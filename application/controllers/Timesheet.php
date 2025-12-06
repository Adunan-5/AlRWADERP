<?php

defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Timesheet extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Timesheet_model', 'timesheets');
    }

    public function upload_excel()
    {
        if (!staff_can('create', 'projects')) {
            access_denied('timesheet_upload');
        }

        $project_id = $this->input->post('project_id');
        $month      = $this->input->post('month'); // format YYYY-MM

        if (!$project_id || !$month) {
            set_alert('danger', 'Project or Month missing.');
            redirect(admin_url('projects'));
        }

        // Upload file
        $dir = FCPATH . 'uploads/timesheets/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $this->load->library('upload', [
            'upload_path'   => $dir,
            'allowed_types' => 'xls|xlsx',
            'max_size'      => 8192,
            'encrypt_name'  => true,
        ]);

        if (!$this->upload->do_upload('timesheet_excel')) {
            set_alert('danger', $this->upload->display_errors());
            redirect(admin_url('projects/view/' . $project_id . '?group=timesheets'));
        }

        $filePath = $this->upload->data('full_path');

        // Parse + Import
        $result = $this->_parse_and_import($filePath, (int)$project_id, $month);

        // Report
        $msg = 'Imported ' . (int)$result['rows_imported'] . ' day-entries for '
             . (int)$result['staff_imported'] . ' staff(s). ';
        if (!empty($result['unmatched'])) {
            $msg .= 'Unmatched staff (' . count($result['unmatched']) . '): ' . implode(', ', $result['unmatched']);
        }
        set_alert('success', $msg);

        redirect(admin_url('projects/view/' . $project_id . '?group=timesheets'));
    }

    /**
     * Core parser for your template:
     * - Row 4 = headers
     * - Row 5+ = data
     * - Date columns from first date-like cell until 'TOTALHRS'
     */
    private function _parse_and_import($filePath, $project_id, $month)
    {
        $rowsImported   = 0;
        $staffImported  = 0;
        $unmatchedNames = [];

        $monthStart     = $month . '-01'; // 'YYYY-MM-01'
        $monthDate      = date('Y-m-01', strtotime($monthStart));
        $nextMonthStart = date('Y-m-01', strtotime($monthDate . ' +1 month'));

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, true); // indexed by column letters

        // 1) Find header row (should be row 4, but we search robustly)
        $headerRow = null;
        for ($r = 1; $r <= 10; $r++) {
            $sample = array_map('strval', $data[$r] ?? []);
            $joined = strtoupper(implode(' ', $sample));
            if (strpos($joined, 'BADGE') !== false && strpos($joined, 'NAME') !== false && strpos($joined, 'IQAMA') !== false) {
                $headerRow = $r;
                break;
            }
        }
        if (!$headerRow) { $headerRow = 4; } // fallback

        // 2) Build a map: column letter -> header label
        $headers = $data[$headerRow];
        $norm = function($s) {
            $s = trim((string)$s);
            $s = preg_replace('/\s+/', ' ', $s);
            return strtoupper($s);
        };

        $colMap = []; // e.g. ['A' => 'SR/NO.', 'B' => 'BADGE NO.', ...]
        foreach ($headers as $col => $label) {
            $colMap[$col] = $norm($label);
        }

        // 3) Find key columns
        $colBadge   = $this->_find_col($colMap, ['BADGE', 'BADGE NO']);
        $colName    = $this->_find_col($colMap, ['NAME']);
        $colIqama   = $this->_find_col($colMap, ['IQAMA']);
        $colTotal   = $this->_find_col($colMap, ['TOTALHRS']);
        $colFAT     = $this->_find_col($colMap, ['FAT']);
        $colDays    = $this->_find_col($colMap, ['DAYS OF PRESENT', 'DAYS PRESENT', 'DAYS']);
        $colPrice   = $this->_find_col($colMap, ['UNIT PRICE SAR', 'UNIT PRICE']);
        $colPayable = $this->_find_col($colMap, ['PAYABLE(PRESENT DAY)', 'PAYABLE']);
        $colRemarks = $this->_find_col($colMap, ['REMARKS']);

        // 4) Detect date columns (between first date-like and TOTALHRS)
        $dateCols = [];
        foreach ($colMap as $col => $label) {
            if ($colTotal && $this->_col_index($col) >= $this->_col_index($colTotal)) {
                continue; // stop at TOTALHRS
            }
            if ($this->_looks_like_day_header($label)) {
                $dateCols[] = $col;
            }
        }
        // Safety: if nothing found, try from column 'L' until TOTALHRS
        if (empty($dateCols) && $colTotal) {
            for ($i = $this->_col_index('L'); $i < $this->_col_index($colTotal); $i++) {
                $col = $this->_col_from_index($i);
                if (isset($colMap[$col]) && $this->_looks_like_day_header($colMap[$col])) {
                    $dateCols[] = $col;
                }
            }
        }

        // 5) Iterate data rows (headerRow+1 .. end)
        $this->db->trans_start();

        for ($r = $headerRow + 1; $r <= count($data); $r++) {
            $row = $data[$r] ?? null;
            if (!$row) continue;

            $nameRaw  = trim((string)($row[$colName]   ?? ''));
            $badgeRaw = trim((string)($row[$colBadge]  ?? ''));
            $iqamaRaw = trim((string)($row[$colIqama]  ?? ''));

            if ($nameRaw === '' && $badgeRaw === '' && $iqamaRaw === '') {
                continue; // empty row
            }

            // Resolve staff_id (adjust to your schema: badge -> tblstaff.code, iqama -> tblstaff.iqama_number)
            $staff_id = $this->_resolve_staff_id($badgeRaw, $iqamaRaw, $nameRaw);

            if (!$staff_id) {
                $unmatchedNames[] = $nameRaw ?: ($badgeRaw ?: $iqamaRaw ?: 'Unknown');
                continue;
            }

            // Summary fields
            $totalHours = $this->_to_float($row[$colTotal]   ?? 0);
            $fat        = trim((string)($row[$colFAT]        ?? ''));
            $daysPres   = (int)($row[$colDays]              ?? 0);
            $unitPrice  = $this->_currency_to_float($row[$colPrice]  ?? 0);
            $payable    = $this->_currency_to_float($row[$colPayable]?? 0);
            $remarks    = trim((string)($row[$colRemarks]    ?? ''));

            // Build day map
            $dayMap = []; // 'YYYY-MM-DD' => hours
            foreach ($dateCols as $col) {
                $headerLabel = $colMap[$col]; // e.g. "01-JUN" or "01/06/2025"
                $dayNum      = $this->_extract_daynum($headerLabel);
                if (!$dayNum) continue;

                $date = date('Y-m-d', strtotime($monthStart . '-' . str_pad($dayNum, 2, '0', STR_PAD_LEFT)));

                $val = $row[$col] ?? '';
                $hours = $this->_hours_from_cell($val); // convert "8", "8.5", "F", "WO" -> 0/float
                if ($hours !== null) {
                    $dayMap[$date] = $hours;
                }
            }

            // Upsert master, replace details
            $summary = [
                'total_hours' => $totalHours,
                'fat'         => $fat,
                'days_present'=> $daysPres,
                'unit_price'  => $unitPrice,
                'payable'     => $payable,
                'remarks'     => $remarks,
            ];
            $timesheet_id = $this->timesheets->upsert_master($staff_id, $project_id, $monthDate, $summary);
            $this->timesheets->replace_details_for_month($timesheet_id, $monthStart, $nextMonthStart, $dayMap);

            $rowsImported += count($dayMap);
            $staffImported++;
        }

        $this->db->trans_complete();

        return [
            'rows_imported'  => $rowsImported,
            'staff_imported' => $staffImported,
            'unmatched'      => array_unique($unmatchedNames),
        ];
    }

    private function _find_col($colMap, array $candidates)
    {
        foreach ($colMap as $col => $label) {
            foreach ($candidates as $want) {
                if (strpos($label, strtoupper($want)) !== false) {
                    return $col;
                }
            }
        }
        return null;
    }

    private function _col_index($col) // A->1, B->2 ...
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num;
    }

    private function _col_from_index($index) // 1->A, 2->B ...
    {
        $str = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $str = chr(65 + $mod) . $str;
            $index = (int)(($index - $mod) / 26);
        }
        return $str;
    }

    // "01-JUN" / "01/06/2025" / "1" -> 1..31
    private function _extract_daynum($label)
    {
        $label = strtoupper(trim($label));
        if (preg_match('/^(\d{1,2})\b/', $label, $m)) {
            $d = (int)$m[1];
            return ($d >= 1 && $d <= 31) ? $d : null;
        }
        return null;
    }

    private function _looks_like_day_header($label)
    {
        $label = strtoupper(trim($label));
        // Matches "01-JUN", "1-JUN", "01/06/2025", "01/06"
        return (bool)preg_match('/^\d{1,2}([\-\/][A-Z]{3}|\-\w+|[\/]\d{1,2}([\/]\d{2,4})?)?$/', $label);
    }

    private function _currency_to_float($v)
    {
        if (is_null($v) || $v === '') return 0.0;
        $s = is_string($v) ? $v : (string)$v;
        $s = strtoupper($s);
        $s = str_replace(['SAR', ',', ' '], '', $s);
        $s = trim($s);
        return is_numeric($s) ? (float)$s : 0.0;
    }

    private function _to_float($v)
    {
        if ($v === null || $v === '') return 0.0;
        return is_numeric($v) ? (float)$v : 0.0;
    }

    private function _hours_from_cell($v)
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float)$v;

        $s = strtoupper(trim((string)$v));
        // treat non-hour codes as 0 hours (adjust if needed)
        $offCodes = ['F','FRI','WO','OFF','H','HOL','N/A','NA','-'];
        if (in_array($s, $offCodes, true)) return 0.0;

        // Try to catch patterns like "8 HRS"
        if (preg_match('/(\d+(\.\d+)?)/', $s, $m)) {
            return (float)$m[1];
        }

        return 0.0;
    }

    // ---- Staff resolution (adjust to your schema) ----
    private function _resolve_staff_id($badge, $iqama, $name)
    {
        // 1) Badge No. -> tblstaff.code  (change if you store badge elsewhere)
        if ($badge !== '') {
            $this->db->where('code', $badge);
            $q = $this->db->get('tblstaff')->row();
            if ($q) return (int)$q->staffid;
        }
        // 2) IQAMA -> tblstaff.iqama_number
        if ($iqama !== '') {
            $this->db->where('iqama_number', $iqama);
            $q = $this->db->get('tblstaff')->row();
            if ($q) return (int)$q->staffid;
        }
        // 3) Name -> try 'name' OR firstname+lastname
        if ($name !== '') {
            // exact match on 'name'
            $this->db->where('name', $name);
            $q = $this->db->get('tblstaff')->row();
            if ($q) return (int)$q->staffid;

            // split into first/last try
            $parts = preg_split('/\s+/', $name);
            if (count($parts) >= 2) {
                $first = $parts[0];
                $last  = $parts[count($parts)-1];
                $this->db->where('firstname', $first);
                $this->db->where('lastname',  $last);
                $q = $this->db->get('tblstaff')->row();
                if ($q) return (int)$q->staffid;
            }
        }
        return null;
    }

    public function save_manual()
    {
        if ($this->input->post()) {
            $staff_id   = $this->input->post('staff_id');
            $project_id = $this->input->post('project_id');
            $month      = $this->input->post('month'); // e.g. 2025-09
            $hours      = $this->input->post('hours'); // array [date => hours]

            if (!$staff_id || !$month || empty($hours)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            // normalize month to first of month
            $month_year = $month . '-01';

            // check if exists → delete old
            $existing = $this->db->get_where(db_prefix().'timesheet_master', [
                'staff_id' => $staff_id,
                'project_id' => $project_id,
                'month_year' => $month_year
            ])->row();

            if ($existing) {
                $this->db->delete(db_prefix().'timesheet_details', ['timesheet_id' => $existing->id]);
                $this->db->delete(db_prefix().'timesheet_master', ['id' => $existing->id]);
            }

            // insert master
            $masterData = [
                'staff_id'    => $staff_id,
                'project_id'  => $project_id,
                'month_year'  => $month_year,
                'total_hours' => array_sum(array_map('floatval', $hours)),
                'days_present'=> count(array_filter($hours, fn($h) => $h !== "" && $h !== null)),
                'created_at'  => date('Y-m-d H:i:s')
            ];
            $this->db->insert(db_prefix().'timesheet_master', $masterData);
            $timesheet_id = $this->db->insert_id();

            // insert details
            foreach ($hours as $date => $h) {
                $this->db->insert(db_prefix().'timesheet_details', [
                    'timesheet_id' => $timesheet_id,
                    'work_date'    => $date,
                    'hours_worked' => $h !== "" ? $h : null
                ]);
            }

            echo json_encode(['success' => true]);
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

}
