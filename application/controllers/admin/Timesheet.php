<?php

defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Timesheet extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        require_once FCPATH . 'vendor/autoload.php';
        $this->load->model('timesheet_model');
        $this->load->helper('file');
    }

    // public function upload_excel()
    // {
    //     // 1. Upload Config
    //     $config['upload_path']   = FCPATH . 'uploads/timesheets/';
    //     $config['allowed_types'] = 'xls|xlsx';
    //     $config['max_size']      = 5120; // 5MB
    //     $config['file_name']     = time() . '_timesheet';

    //     if (!is_dir($config['upload_path'])) {
    //         mkdir($config['upload_path'], 0777, true);
    //     }

    //     $this->load->library('upload', $config);

    //     // 2. Upload File
    //     if (!$this->upload->do_upload('timesheet_excel')) {
    //         set_alert('danger', $this->upload->display_errors());
    //         redirect($_SERVER['HTTP_REFERER']);
    //     }

    //     $fileData = $this->upload->data();
    //     $filePath = $fileData['full_path'];

    //     try {
    //         // 3. Load Excel
    //         $spreadsheet = IOFactory::load($filePath);
    //         $sheet       = $spreadsheet->getActiveSheet();
    //         $rows        = $sheet->toArray();

    //         // 4. Get form data
    //         $monthYear = $this->input->post('month') . '-01'; // YYYY-MM-01
    //         $projectId = $this->input->post('project_id');

    //         // Excel row indexes
    //         $headerRowIndex    = 3; // Row 4 in Excel
    //         $dayNameRowIndex   = 2; // Row 3 in Excel
    //         $dataStartRowIndex = 4; // Row 5 in Excel

    //         $headers  = $rows[$headerRowIndex];
    //         $dayNames = $rows[$dayNameRowIndex];

    //         // Find first date column dynamically
    //         $firstDateCol = null;
    //         for ($i = 0; $i < count($headers); $i++) {
    //             if (!empty($headers[$i]) && $this->is_excel_date_or_day($headers[$i], $dayNames[$i])) {
    //                 $firstDateCol = $i;
    //                 break;
    //             }
    //         }
    //         if ($firstDateCol === null) {
    //             throw new Exception("Date columns not found in Excel.");
    //         }

    //         $lastDateCol = $firstDateCol;
    //         for ($i = $firstDateCol; $i < count($headers); $i++) {
    //             if (strtolower(trim($headers[$i])) === 'totalhrs') {
    //                 break;
    //             }
    //             $lastDateCol = $i;
    //         }

    //         $totalHrs = null;
    //         for ($i = 0; $i < count($headers); $i++) {
    //             if (strtolower(trim((string)$headers[$i])) === 'totalhrs') {
    //                 $totalHrsCol = $i;
    //                 break;
    //             }
    //         }
    //         if ($totalHrsCol === null) {
    //             throw new Exception("TotalHrs column not found in Excel.");
    //         }

    //         // Loop staff rows
    //         for ($r = $dataStartRowIndex; $r < count($rows); $r++) {
    //             $row = $rows[$r];
    //             if (empty($row[0])) continue; // Skip empty SR/No

    //             $iqamaNo = trim((string)$row[5]);
    //             $staff   = $this->db->get_where(db_prefix().'staff', ['iqama_number' => $iqamaNo])->row();
    //             if (!$staff) continue;

    //             // Summary columns (last 6)
    //             $totalHrs    = $row[$totalHrsCol];
    //             $fat         = $row[$totalHrsCol + 1];
    //             $daysPresent = $row[$totalHrsCol + 2];
    //             $unitPrice   = preg_replace('/[^\d.]/', '', (string)$row[$totalHrsCol + 3]);
    //             $payable     = preg_replace('/[^\d.]/', '', (string)$row[$totalHrsCol + 4]);
    //             $remarks     = $row[$totalHrsCol + 5];

    //             // Insert master record
    //             $timesheetId = $this->timesheet_model->insert_master([
    //                 'staff_id'     => $staff->staffid,
    //                 'project_id'   => $projectId,
    //                 'month_year'   => $monthYear,
    //                 'total_hours'  => is_numeric($totalHrs) ? $totalHrs : (string)$totalHrs, // keep value
    //                 'fat'          => $fat,
    //                 'days_present' => is_numeric($daysPresent) ? $daysPresent : 0,
    //                 'unit_price'   => is_numeric($unitPrice) ? $unitPrice : 0,
    //                 'payable'      => is_numeric($payable) ? $payable : 0,
    //                 'remarks'      => $remarks
    //             ]);

    //             // Daily hours — store text or number as-is
    //             // $lastDateCol = count($row) - 7;
    //             for ($c = $firstDateCol; $c <= $lastDateCol; $c++) {
    //                 $cellValue = trim($row[$c]);
    //                 if ($cellValue === '') continue; // Skip empty cells

    //                 // Get date from header
    //                 $dateValue = $headers[$c];
    //                 if (is_numeric($dateValue)) {
    //                     try {
    //                         $workDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
    //                     } catch (\Exception $e) {
    //                         continue;
    //                     }
    //                 } else {
    //                     $workDate = date('Y-m-d', strtotime($dateValue . '-' . $this->input->post('month')));
    //                 }

    //                 // Insert exactly what’s in the Excel cell (PH, F, number)
    //                 // $this->timesheet_model->insert_detail($timesheetId, $workDate, $cellValue, null);

    //                 // Convert to numeric if possible
    //                 $workedHours = is_numeric($cellValue) ? (float)$cellValue : null;
    //                 $regular = 0;
    //                 $overtime = 0;

    //                 if (!is_null($workedHours)) {
    //                     $standard = (float)$staff->work_hours_per_day;
    //                     $regular  = min($workedHours, $standard);
    //                     $overtime = max(0, $workedHours - $standard);
    //                 }

    //                 // Check if record already exists
    //                 $existing = $this->db->get_where(db_prefix() . 'timesheet_details', [
    //                     'timesheet_id' => $timesheetId,
    //                     'work_date'    => $workDate
    //                 ])->row();

    //                 $data = [
    //                     'timesheet_id'   => $timesheetId,
    //                     'work_date'      => $workDate,
    //                     'regular_hours'  => $regular,
    //                     'overtime_hours' => $overtime,
    //                 ];

    //                 if ($existing) {
    //                     // Update record
    //                     $this->db->where('id', $existing->id)->update(db_prefix() . 'timesheet_details', $data);
    //                 } else {
    //                     // Insert new record
    //                     $this->db->insert(db_prefix() . 'timesheet_details', $data);
    //                 }
    //             }
    //         }

    //         set_alert('success', 'Timesheet imported successfully.');
    //     } catch (Exception $e) {
    //         set_alert('danger', 'Excel read error: ' . $e->getMessage());
    //     }

    //     redirect($_SERVER['HTTP_REFERER']);
    // }

    // public function upload_excel()
    // {
    //     // 1. Upload Config
    //     $config['upload_path']   = FCPATH . 'uploads/timesheets/';
    //     $config['allowed_types'] = 'xls|xlsx';
    //     $config['max_size']      = 5120; // 5MB
    //     $config['file_name']     = time() . '_timesheet';

    //     if (!is_dir($config['upload_path'])) {
    //         mkdir($config['upload_path'], 0777, true);
    //     }

    //     $this->load->library('upload', $config);

    //     // 2. Upload File
    //     if (!$this->upload->do_upload('timesheet_excel')) {
    //         set_alert('danger', $this->upload->display_errors());
    //         redirect($_SERVER['HTTP_REFERER']);
    //     }

    //     $fileData = $this->upload->data();
    //     $filePath = $fileData['full_path'];

    //     try {
    //         // 3. Load Excel
    //         $spreadsheet = IOFactory::load($filePath);
    //         $sheet       = $spreadsheet->getActiveSheet();
    //         $rows        = $sheet->toArray();

    //         // 4. Get form data
    //         $monthYear = $this->input->post('month') . '-01'; // YYYY-MM-01
    //         $projectId = $this->input->post('project_id');

    //         // Excel row indexes
    //         $headerRowIndex    = 3; // Row 4 in Excel
    //         $dayNameRowIndex   = 2; // Row 3 in Excel
    //         $dataStartRowIndex = 4; // Row 5 in Excel

    //         $headers  = $rows[$headerRowIndex];
    //         $dayNames = $rows[$dayNameRowIndex];

    //         // Find first date column dynamically
    //         $firstDateCol = null;
    //         for ($i = 0; $i < count($headers); $i++) {
    //             if (!empty($headers[$i]) && $this->is_excel_date_or_day($headers[$i], $dayNames[$i])) {
    //                 $firstDateCol = $i;
    //                 break;
    //             }
    //         }
    //         if ($firstDateCol === null) {
    //             throw new Exception("Date columns not found in Excel.");
    //         }

    //         $lastDateCol = $firstDateCol;
    //         for ($i = $firstDateCol; $i < count($headers); $i++) {
    //             if (strtolower(trim($headers[$i])) === 'totalhrs') {
    //                 break;
    //             }
    //             $lastDateCol = $i;
    //         }

    //         $totalHrsCol = null;
    //         for ($i = 0; $i < count($headers); $i++) {
    //             if (strtolower(trim((string)$headers[$i])) === 'totalhrs') {
    //                 $totalHrsCol = $i;
    //                 break;
    //             }
    //         }
    //         if ($totalHrsCol === null) {
    //             throw new Exception("TotalHrs column not found in Excel.");
    //         }

    //         // Loop staff rows
    //         for ($r = $dataStartRowIndex; $r < count($rows); $r++) {
    //             $row = $rows[$r];
    //             if (empty($row[0])) continue; // Skip empty SR/No

    //             $iqamaNo = trim((string)$row[5]);
    //             $staff   = $this->db->get_where(db_prefix().'staff', ['iqama_number' => $iqamaNo])->row();
    //             if (!$staff) continue;

    //             // Summary columns (last 6)
    //             $totalHrs    = $row[$totalHrsCol];
    //             $fat         = $row[$totalHrsCol + 1];
    //             $daysPresent = $row[$totalHrsCol + 2];
    //             $unitPrice   = preg_replace('/[^\d.]/', '', (string)$row[$totalHrsCol + 3]);
    //             $payable     = preg_replace('/[^\d.]/', '', (string)$row[$totalHrsCol + 4]);
    //             $remarks     = $row[$totalHrsCol + 5];

    //             // Check for existing master record
    //             $existingMaster = $this->db->get_where(db_prefix() . 'timesheet_master', [
    //                 'staff_id'   => $staff->staffid,
    //                 'project_id' => $projectId,
    //                 'month_year' => $monthYear
    //             ])->row();

    //             $masterData = [
    //                 'total_hours'  => is_numeric($totalHrs) ? $totalHrs : (string)$totalHrs, // keep value
    //                 'fat'          => $fat,
    //                 'days_present' => is_numeric($daysPresent) ? $daysPresent : 0,
    //                 'unit_price'   => is_numeric($unitPrice) ? $unitPrice : 0,
    //                 'payable'      => is_numeric($payable) ? $payable : 0,
    //                 'remarks'      => $remarks
    //             ];

    //             if ($existingMaster) {
    //                 // Update existing master record
    //                 $timesheetId = $existingMaster->id;
    //                 $this->db->where('id', $timesheetId)->update(db_prefix() . 'timesheet_master', $masterData);
    //             } else {
    //                 // Insert new master record
    //                 $masterData['staff_id']   = $staff->staffid;
    //                 $masterData['project_id'] = $projectId;
    //                 $masterData['month_year'] = $monthYear;
    //                 $this->db->insert(db_prefix() . 'timesheet_master', $masterData);
    //                 $timesheetId = $this->db->insert_id();
    //             }

    //             // Daily hours — store text or number as-is
    //             // $lastDateCol = count($row) - 7;
    //             for ($c = $firstDateCol; $c <= $lastDateCol; $c++) {
    //                 $cellValue = trim($row[$c]);
    //                 if ($cellValue === '') continue; // Skip empty cells

    //                 // Get date from header
    //                 $dateValue = $headers[$c];
    //                 if (is_numeric($dateValue)) {
    //                     try {
    //                         $workDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
    //                     } catch (\Exception $e) {
    //                         continue;
    //                     }
    //                 } else {
    //                     $workDate = date('Y-m-d', strtotime($dateValue . '-' . $this->input->post('month')));
    //                 }

    //                 // Insert exactly what’s in the Excel cell (PH, F, number)
    //                 // $this->timesheet_model->insert_detail($timesheetId, $workDate, $cellValue, null);

    //                 // Convert to numeric if possible
    //                 $workedHours = is_numeric($cellValue) ? (float)$cellValue : null;
    //                 $regular = 0;
    //                 $overtime = 0;

    //                 if (!is_null($workedHours)) {
    //                     $standard = (float)$staff->work_hours_per_day;
    //                     $regular  = min($workedHours, $standard);
    //                     $overtime = max(0, $workedHours - $standard);
    //                 }

    //                 // Check if record already exists
    //                 $existing = $this->db->get_where(db_prefix() . 'timesheet_details', [
    //                     'timesheet_id' => $timesheetId,
    //                     'work_date'    => $workDate
    //                 ])->row();

    //                 $data = [
    //                     'timesheet_id'   => $timesheetId,
    //                     'work_date'      => $workDate,
    //                     'regular_hours'  => $regular,
    //                     'overtime_hours' => $overtime,
    //                 ];

    //                 if ($existing) {
    //                     // Update record
    //                     $this->db->where('id', $existing->id)->update(db_prefix() . 'timesheet_details', $data);
    //                 } else {
    //                     // Insert new record
    //                     $this->db->insert(db_prefix() . 'timesheet_details', $data);
    //                 }
    //             }
    //         }

    //         set_alert('success', 'Timesheet imported successfully.');
    //     } catch (Exception $e) {
    //         set_alert('danger', 'Excel read error: ' . $e->getMessage());
    //     }

    //     redirect($_SERVER['HTTP_REFERER']);
    // }

    public function upload_excel()
    {
        $config['upload_path']   = FCPATH . 'uploads/timesheets/';
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size']      = 5120;
        $config['file_name']     = time() . '_timesheet';

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('timesheet_excel')) {
            set_alert('danger', $this->upload->display_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            $projectId = $this->input->post('project_id');
            $monthYear = $this->input->post('month'); // format: YYYY-MM

            // Row 1 is header
            $headers = array_map('trim', $rows[0]);

            // Find column indexes
            $nameCol  = array_search('name', array_map('strtolower', $headers));
            $iqamaCol = array_search('iqama', array_map('strtolower', $headers));

            if ($nameCol === false || $iqamaCol === false) {
                throw new Exception("Missing required headers: Name or Iqama");
            }

            // Determine which columns are days (1–31)
            $dayColumns = [];
            foreach ($headers as $i => $h) {
                if (is_numeric($h) && $h >= 1 && $h <= 31) {
                    $dayColumns[$i] = (int)$h;
                }
            }

            if (empty($dayColumns)) {
                throw new Exception("No day columns (1–31) found in Excel.");
            }

            // Process data rows (from row 2 onwards)
            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                if (empty($row[$iqamaCol])) continue;

                $iqama = trim($row[$iqamaCol]);
                $staff = $this->db->get_where(db_prefix().'staff', ['iqama_number' => $iqama])->row();
                if (!$staff) continue;

                // 1️⃣ Insert or update timesheet master
                $existingMaster = $this->db->get_where(db_prefix().'timesheet_master', [
                    'staff_id'   => $staff->staffid,
                    'project_id' => $projectId,
                    'month_year' => $monthYear . '-01'
                ])->row();

                $masterData = [
                    'staff_id'   => $staff->staffid,
                    'project_id' => $projectId,
                    'month_year' => $monthYear . '-01'
                ];

                if ($existingMaster) {
                    $timesheetId = $existingMaster->id;
                } else {
                    $this->db->insert(db_prefix().'timesheet_master', $masterData);
                    $timesheetId = $this->db->insert_id();
                }

                // 2️⃣ Loop through day columns (1–31)
                foreach ($dayColumns as $colIndex => $day) {
                    $cellValue = trim((string)$row[$colIndex]);
                    if ($cellValue === '') continue;

                    // Dynamically handle month length (28, 29, 30, 31)
                    $year  = date('Y', strtotime($monthYear . '-01'));
                    $month = date('m', strtotime($monthYear . '-01'));
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

                    if ($day > $daysInMonth) continue; // skip invalid days like 30/31 in Feb

                    $workDate = sprintf('%s-%02d', $monthYear, $day);

                    // Calculate hours
                    $workedHours = is_numeric($cellValue) ? (float)$cellValue : null;
                    $regular = 0;
                    $overtime = 0;

                    if (!is_null($workedHours)) {
                        $standard = (float)$staff->work_hours_per_day;
                        $regular  = min($workedHours, $standard);
                        $overtime = max(0, $workedHours - $standard);
                    }

                    // Check existing
                    $existingDetail = $this->db->get_where(db_prefix().'timesheet_details', [
                        'timesheet_id' => $timesheetId,
                        'work_date'    => $workDate
                    ])->row();

                    $detailData = [
                        'timesheet_id'   => $timesheetId,
                        'work_date'      => $workDate,
                        'regular_hours'  => $regular,
                        'overtime_hours' => $overtime,
                    ];

                    if ($existingDetail) {
                        $this->db->where('id', $existingDetail->id)->update(db_prefix().'timesheet_details', $detailData);
                    } else {
                        $this->db->insert(db_prefix().'timesheet_details', $detailData);
                    }
                }
            }

            set_alert('success', 'Timesheet uploaded successfully.');
        } catch (Exception $e) {
            set_alert('danger', 'Excel read error: ' . $e->getMessage());
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    // Preview Excel data before import (AJAX)
    public function preview_excel()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $config['upload_path']   = FCPATH . 'uploads/timesheets/temp/';
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size']      = 5120;
        $config['file_name']     = time() . '_preview';

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('timesheet_excel')) {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];
        $fileName = $fileData['file_name'];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            $projectId = $this->input->post('project_id');
            $monthYear = $this->input->post('month'); // format: YYYY-MM
            $monthDate = $monthYear . '-01';

            // Get project assignees
            $assignedStaffIds = $this->db->select('staff_id')
                ->from(db_prefix() . 'projectassignee')
                ->where('project_id', (int)$projectId)
                ->get()
                ->result_array();
            $assignedStaffIds = array_column($assignedStaffIds, 'staff_id');

            // Row 1 is header
            $headers = array_map('trim', $rows[0]);

            // Find column indexes
            $nameCol  = array_search('name', array_map('strtolower', $headers));
            $iqamaCol = array_search('iqama', array_map('strtolower', $headers));

            if ($nameCol === false || $iqamaCol === false) {
                throw new Exception("Missing required headers: Name or Iqama");
            }

            // Determine which columns are days (1–31)
            $dayColumns = [];
            foreach ($headers as $i => $h) {
                if (is_numeric($h) && $h >= 1 && $h <= 31) {
                    $dayColumns[$i] = (int)$h;
                }
            }

            if (empty($dayColumns)) {
                throw new Exception("No day columns (1–31) found in Excel.");
            }

            // Process data rows for preview
            $previewData = [];
            $staffIdsInExcel = []; // Track which staff are in Excel

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                if (empty($row[$iqamaCol])) continue;

                $name = trim($row[$nameCol]);
                $iqama = trim($row[$iqamaCol]);

                // Find staff
                $staff = $this->db->get_where(db_prefix().'staff', ['iqama_number' => $iqama])->row();

                $status = '';
                $statusClass = '';
                $canImport = true;

                if (!$staff) {
                    $status = 'Employee not found';
                    $statusClass = 'danger';
                    $canImport = false;
                } elseif (!in_array($staff->staffid, $assignedStaffIds)) {
                    $status = 'Not assigned to project';
                    $statusClass = 'warning';
                    $canImport = false;
                } else {
                    // Track this staff as present in Excel
                    $staffIdsInExcel[] = $staff->staffid;

                    // Check if timesheet already exists
                    $existing = $this->db->get_where(db_prefix().'timesheet_master', [
                        'staff_id'   => $staff->staffid,
                        'project_id' => $projectId,
                        'month_year' => $monthDate
                    ])->row();

                    if ($existing) {
                        // Count existing entries
                        $entryCount = $this->db->where('timesheet_id', $existing->id)
                            ->from(db_prefix().'timesheet_details')
                            ->count_all_results();

                        $status = 'Will update (' . $entryCount . ' existing entries)';
                        $statusClass = 'info';
                    } else {
                        $status = 'Ready to import';
                        $statusClass = 'success';
                    }
                }

                // Calculate total hours from Excel
                $totalHours = 0;
                foreach ($dayColumns as $colIndex => $day) {
                    $cellValue = trim((string)$row[$colIndex]);
                    if (is_numeric($cellValue)) {
                        $totalHours += (float)$cellValue;
                    }
                }

                $previewData[] = [
                    'row_index' => $r,
                    'name' => $name,
                    'iqama' => $iqama,
                    'staff_id' => $staff ? $staff->staffid : null,
                    'total_hours' => number_format($totalHours, 2),
                    'status' => $status,
                    'status_class' => $statusClass,
                    'can_import' => $canImport,
                    'in_excel' => true
                ];
            }

            // Find assigned employees missing from Excel
            $missingEmployees = [];
            if (!empty($assignedStaffIds)) {
                $missingStaffIds = array_diff($assignedStaffIds, $staffIdsInExcel);

                if (!empty($missingStaffIds)) {
                    $missingStaff = $this->db->select('staffid, firstname, lastname, iqama_number')
                        ->from(db_prefix() . 'staff')
                        ->where_in('staffid', $missingStaffIds)
                        ->get()
                        ->result_array();

                    foreach ($missingStaff as $staff) {
                        $missingEmployees[] = [
                            'row_index' => null,
                            'name' => $staff['firstname'] . ' ' . $staff['lastname'],
                            'iqama' => $staff['iqama_number'] ?: 'N/A',
                            'staff_id' => $staff['staffid'],
                            'total_hours' => '0.00',
                            'status' => 'Missing from Excel',
                            'status_class' => 'default',
                            'can_import' => false,
                            'in_excel' => false
                        ];
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $previewData,
                'missing_employees' => $missingEmployees,
                'file_name' => $fileName,
                'month' => $monthYear,
                'project_id' => $projectId
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Excel read error: ' . $e->getMessage()
            ]);
        }
    }

    // Process selective import (AJAX)
    public function process_selective_import()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fileName = $this->input->post('file_name');
        $selectedRows = $this->input->post('selected_rows'); // Array of row indexes
        $projectId = $this->input->post('project_id');
        $monthYear = $this->input->post('month');

        if (!$fileName || !$selectedRows || !is_array($selectedRows)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $filePath = FCPATH . 'uploads/timesheets/temp/' . $fileName;

        if (!file_exists($filePath)) {
            echo json_encode(['success' => false, 'message' => 'Upload file not found. Please upload again.']);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            $headers = array_map('trim', $rows[0]);
            $nameCol  = array_search('name', array_map('strtolower', $headers));
            $iqamaCol = array_search('iqama', array_map('strtolower', $headers));

            // Determine day columns
            $dayColumns = [];
            foreach ($headers as $i => $h) {
                if (is_numeric($h) && $h >= 1 && $h <= 31) {
                    $dayColumns[$i] = (int)$h;
                }
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($selectedRows as $rowIndex) {
                $rowIndex = (int)$rowIndex;
                if (!isset($rows[$rowIndex])) {
                    $skipped++;
                    continue;
                }

                $row = $rows[$rowIndex];
                $iqama = trim($row[$iqamaCol]);

                $staff = $this->db->get_where(db_prefix().'staff', ['iqama_number' => $iqama])->row();
                if (!$staff) {
                    $skipped++;
                    $errors[] = "Row " . ($rowIndex + 1) . ": Employee not found";
                    continue;
                }

                // Insert/update master
                $existingMaster = $this->db->get_where(db_prefix().'timesheet_master', [
                    'staff_id'   => $staff->staffid,
                    'project_id' => $projectId,
                    'month_year' => $monthYear . '-01'
                ])->row();

                $masterData = [
                    'staff_id'   => $staff->staffid,
                    'project_id' => $projectId,
                    'month_year' => $monthYear . '-01'
                ];

                if ($existingMaster) {
                    $timesheetId = $existingMaster->id;
                } else {
                    $this->db->insert(db_prefix().'timesheet_master', $masterData);
                    $timesheetId = $this->db->insert_id();
                }

                // Process day columns
                foreach ($dayColumns as $colIndex => $day) {
                    $cellValue = trim((string)$row[$colIndex]);
                    if ($cellValue === '') continue;

                    $year  = date('Y', strtotime($monthYear . '-01'));
                    $month = date('m', strtotime($monthYear . '-01'));
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

                    if ($day > $daysInMonth) continue;

                    $workDate = sprintf('%s-%02d', $monthYear, $day);

                    $workedHours = is_numeric($cellValue) ? (float)$cellValue : null;
                    $regular = 0;
                    $overtime = 0;

                    if (!is_null($workedHours)) {
                        $standard = (float)$staff->work_hours_per_day;
                        $regular  = min($workedHours, $standard);
                        $overtime = max(0, $workedHours - $standard);
                    }

                    $existingDetail = $this->db->get_where(db_prefix().'timesheet_details', [
                        'timesheet_id' => $timesheetId,
                        'work_date'    => $workDate
                    ])->row();

                    $detailData = [
                        'timesheet_id'   => $timesheetId,
                        'work_date'      => $workDate,
                        'regular_hours'  => $regular,
                        'overtime_hours' => $overtime,
                    ];

                    if ($existingDetail) {
                        $this->db->where('id', $existingDetail->id)->update(db_prefix().'timesheet_details', $detailData);
                    } else {
                        $this->db->insert(db_prefix().'timesheet_details', $detailData);
                    }
                }

                $imported++;
            }

            // Delete temp file
            @unlink($filePath);

            echo json_encode([
                'success' => true,
                'message' => "Import completed: {$imported} imported, {$skipped} skipped",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Import error: ' . $e->getMessage()
            ]);
        }
    }

    public function download_import_template()
    {
        $file_path = FCPATH . 'uploads/timesheets/timesheettemplate.xlsx';

        // Check if file exists
        if (!file_exists($file_path)) {
            show_error('Template file not found at: ' . $file_path, 404);
            return;
        }

        // Clean output buffer to prevent corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Set download headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Timesheet_Import_Template.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        // Read the file and output
        readfile($file_path);
        exit;
    }

    private function is_excel_date_or_day($headerValue, $dayNameValue)
    {
        if (is_numeric($headerValue)) return true;
        if (preg_match('/^\d{1,2}[-\/][A-Za-z]{3}$/', $headerValue)) return true;
        $dayNameValue = strtolower(trim((string)$dayNameValue));
        if (in_array(strtolower(trim($dayNameValue)), ['sun','mon','tue','wed','thu','fri','sat'])) return true;
        return false;
    }

    public function list($project_id)
    {
        $month = $this->input->get('month');

        $this->app->get_table_data('timesheet_table', [
            'project_id' => $project_id,
            'month'      => $month
        ]);
    }

    public function get_details($id)
    {
        $response = ['success' => false, 'message' => 'Invalid request'];
        if ($id) {
            $details = $this->timesheet_model->get_details($id);
            if ($details) {
                $response = [
                    'success' => true,
                    'data' => $details
                ];
            } else {
                $response['message'] = 'No details found';
            }
        }
        echo json_encode($response);
        exit;
    }

    public function update_details()
    {
        $updates = $this->input->post('updates');
        $this->load->model('timesheet_model');

        $success = true;
        foreach ($updates as $update) {
            $result = $this->timesheet_model->update_timesheet_detail($update['id'], [
                'hours_worked' => $update['hours_worked']
            ]);
            if (!$result) {
                $success = false;
                break;
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Updated successfully' : 'Some updates failed'
        ]);
    }

    // public function save_manual()
    // {
    //     if ($this->input->post()) {
    //         $staff_id   = $this->input->post('staff_id');
    //         $project_id = $this->input->post('project_id');
    //         $month      = $this->input->post('month'); // e.g. 2025-09
    //         $hours      = $this->input->post('hours'); // array [date => hours]
    //         $fat       = $this->input->post('fat');
    //         $unitPrice = $this->input->post('unit_price');
    //         $payable   = $this->input->post('payable');
    //         $remarks   = $this->input->post('remarks');

    //         if (!$staff_id || !$month || empty($hours)) {
    //             echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    //             return;
    //         }

    //         // normalize month to first of month
    //         $month_year = $month . '-01';

    //         // check if exists → delete old
    //         $existing = $this->db->get_where(db_prefix().'timesheet_master', [
    //             'staff_id' => $staff_id,
    //             'project_id' => $project_id,
    //             'month_year' => $month_year
    //         ])->row();

    //         if ($existing) {
    //             $this->db->delete(db_prefix().'timesheet_details', ['timesheet_id' => $existing->id]);
    //             $this->db->delete(db_prefix().'timesheet_master', ['id' => $existing->id]);
    //         }

    //         // insert master
    //         $masterData = [
    //             'staff_id'    => $staff_id,
    //             'project_id'  => $project_id,
    //             'month_year'  => $month_year,
    //             'total_hours' => array_sum(array_map('floatval', $hours)),
    //             'days_present'=> count(array_filter($hours, fn($h) => $h !== "" && $h !== null)),
    //             'fat'          => $fat,
    //             'unit_price'   => $unitPrice,
    //             'payable'      => $payable,
    //             'remarks'      => $remarks,
    //             'created_at'  => date('Y-m-d H:i:s')
    //         ];
    //         $this->db->insert(db_prefix().'timesheet_master', $masterData);
    //         $timesheet_id = $this->db->insert_id();

    //         // insert details
    //         foreach ($hours as $date => $h) {
    //             $this->db->insert(db_prefix().'timesheet_details', [
    //                 'timesheet_id' => $timesheet_id,
    //                 'work_date'    => $date,
    //                 'hours_worked' => $h !== "" ? $h : null
    //             ]);
    //         }

    //         echo json_encode(['success' => true]);
    //         return;
    //     }
    //     echo json_encode(['success' => false, 'message' => 'Invalid request']);
    // }

    public function save_manual()
    {
        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $staff_id   = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $month      = $this->input->post('month'); // YYYY-MM
        $regular    = $this->input->post('regular') ?: [];
        $overtime   = $this->input->post('overtime') ?: [];
        $fat        = $this->input->post('fat');
        $unitPrice  = $this->input->post('unit_price');
        $payable    = $this->input->post('payable');
        $remarks    = $this->input->post('remarks');

        if (!$staff_id || !$project_id || !$month) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $month_year = $month . '-01';

        // compute totals
        $total_regular = 0;
        $total_overtime = 0;
        $days_present = 0;
        foreach ($regular as $d => $r) {
            $reg = is_numeric($r) ? (float)$r : 0;
            $total_regular += $reg;
            $ot = isset($overtime[$d]) && is_numeric($overtime[$d]) ? (float)$overtime[$d] : 0;
            $total_overtime += $ot;

            if ($reg + $ot > 0) $days_present++;
        }

        $masterData = [
            'staff_id'             => $staff_id,
            'project_id'           => $project_id,
            'month_year'           => $month_year,
            'total_hours'          => $total_regular + $total_overtime,
            'total_regular_hours'  => $total_regular,
            'total_overtime_hours' => $total_overtime,
            'days_present'         => $days_present,
            'fat'                  => $fat,
            'unit_price'           => $unitPrice ?: 0,
            'payable'              => $payable ?: 0,
            'remarks'              => $remarks,
        ];

        $this->load->model('timesheet_model');

        // check if exists
        $existing = $this->db->where([
            'staff_id'   => $staff_id,
            'project_id' => $project_id,
            'month_year' => $month_year
        ])->get(db_prefix().'timesheet_master')->row();

        if ($existing) {
            // update master
            $this->db->where('id', $existing->id)->update(db_prefix().'timesheet_master', $masterData);
            $timesheet_id = $existing->id;
        } else {
            // insert master
            $masterData['created_at'] = date('Y-m-d H:i:s');
            $timesheet_id = $this->timesheet_model->insert_master($masterData);
        }

        // Upsert details
        foreach ($regular as $date => $r) {
            $rt = ($r === '' ? null : $r);
            $ot = isset($overtime[$date]) ? ($overtime[$date] === '' ? null : $overtime[$date]) : null;

            $detail = $this->db->where([
                'timesheet_id' => $timesheet_id,
                'work_date'    => $date
            ])->get(db_prefix().'timesheet_details')->row();

            $detailData = [
                'regular_hours'  => $rt,
                'overtime_hours' => $ot,
            ];

            if ($detail) {
                // update
                $this->db->where('id', $detail->id)
                        ->update(db_prefix().'timesheet_details', $detailData);
            } else {
                // insert
                $this->timesheet_model->insert_detail($timesheet_id, $date, $rt, $ot);
            }
        }

        echo json_encode(['success' => true]);
    }

    // public function save_all()
    // {
    //     if (!$this->input->post()) {
    //         echo json_encode(['success' => false, 'message' => 'Invalid request']);
    //         return;
    //     }

    //     $project_id = $this->input->post('project_id');
    //     $month      = $this->input->post('month'); // YYYY-MM
    //     $timesheets = $this->input->post('timesheets'); // multi staff array

    //     if (!$project_id || !$month || !$timesheets) {
    //         echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    //         return;
    //     }

    //     $month_year = $month . '-01';
    //     $this->load->model('timesheet_model');

    //     $this->db->trans_start(); // Begin transaction

    //     foreach ($timesheets as $staff_id => $rows) {
    //         $regular    = isset($rows['regular']) ? $rows['regular'] : [];
    //         $overtime   = isset($rows['overtime']) ? $rows['overtime'] : [];
    //         $fat        = isset($rows['fat']) ? $rows['fat'] : null;
    //         $unitPrice  = isset($rows['unit_price']) ? $rows['unit_price'] : 0;
    //         $payable    = isset($rows['payable']) ? $rows['payable'] : 0;
    //         $remarks    = isset($rows['remarks']) ? $rows['remarks'] : null;

    //         // compute totals
    //         $total_regular = 0;
    //         $total_overtime = 0;
    //         $days_present = 0;
    //         foreach ($regular as $d => $r) {
    //             $reg = is_numeric($r) ? (float)$r : 0;
    //             $total_regular += $reg;
    //             $ot = isset($overtime[$d]) && is_numeric($overtime[$d]) ? (float)$overtime[$d] : 0;
    //             $total_overtime += $ot;

    //             if ($reg + $ot > 0) $days_present++;
    //         }

    //         $masterData = [
    //             'staff_id'             => $staff_id,
    //             'project_id'           => $project_id,
    //             'month_year'           => $month_year,
    //             'total_hours'          => $total_regular + $total_overtime,
    //             'total_regular_hours'  => $total_regular,
    //             'total_overtime_hours' => $total_overtime,
    //             'days_present'         => $days_present,
    //             'fat'                  => $fat,
    //             'unit_price'           => $unitPrice,
    //             'payable'              => $payable,
    //             'remarks'              => $remarks,
    //         ];

    //         // check if exists
    //         $existing = $this->db->where([
    //             'staff_id'   => $staff_id,
    //             'project_id' => $project_id,
    //             'month_year' => $month_year
    //         ])->get(db_prefix().'timesheet_master')->row();

    //         if ($existing) {
    //             $this->db->where('id', $existing->id)->update(db_prefix().'timesheet_master', $masterData);
    //             $timesheet_id = $existing->id;
    //         } else {
    //             $masterData['created_at'] = date('Y-m-d H:i:s');
    //             $timesheet_id = $this->timesheet_model->insert_master($masterData);
    //         }

    //         // Upsert details
    //         foreach ($regular as $date => $r) {
    //             $rt = ($r === '' ? null : $r);
    //             $ot = isset($overtime[$date]) ? ($overtime[$date] === '' ? null : $overtime[$date]) : null;

    //             $detail = $this->db->where([
    //                 'timesheet_id' => $timesheet_id,
    //                 'work_date'    => $date
    //             ])->get(db_prefix().'timesheet_details')->row();

    //             $detailData = [
    //                 'regular_hours'  => $rt,
    //                 'overtime_hours' => $ot,
    //             ];

    //             if ($detail) {
    //                 $this->db->where('id', $detail->id)->update(db_prefix().'timesheet_details', $detailData);
    //             } else {
    //                 $this->timesheet_model->insert_detail($timesheet_id, $date, $rt, $ot);
    //             }
    //         }

    //         // Upsert tblhrp_employees_value with ot_hours
    //         $month_for_emp = date('Y-m-01', strtotime($month));
    //         $emp_value_data = [
    //             'staff_id' => $staff_id,
    //             'month'    => $month_for_emp,
    //             'ot_hours' => $total_overtime
    //         ];

    //         $existing_emp_value = $this->db->where([
    //             'staff_id' => $staff_id,
    //             'month'    => $month_for_emp
    //         ])->get('tblhrp_employees_value')->row();

    //         if ($existing_emp_value) {
    //             $this->db->where('id', $existing_emp_value->id)
    //                     ->update('tblhrp_employees_value', $emp_value_data);
    //         } else {
    //             $this->db->insert('tblhrp_employees_value', $emp_value_data);
    //         }
    //     }
    //     $this->db->trans_complete(); // Commit transaction

    //     if ($this->db->trans_status() === FALSE) {
    //         echo json_encode(['success' => false, 'message' => 'Failed to save timesheets']);
    //     } else {
    //         echo json_encode(['success' => true, 'message' => 'Timesheets saved successfully']);
    //     }
    // }

    public function save_all()
    {
        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $project_id = $this->input->post('project_id');
        $month      = $this->input->post('month'); // YYYY-MM
        $timesheets = $this->input->post('timesheets'); // multi staff array

        if (!$project_id || !$month || !$timesheets) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $month_year = $month . '-01';
        $month_for_emp = date('Y-m-01', strtotime($month)); // Consistent with payroll
        $rel_type = 'none'; // Or fetch from hrp_get_hr_profile_status() if needed

        $this->load->model('timesheet_model');
        $this->load->model('hr_payroll/hr_payroll_model');

        $this->db->trans_start(); // Begin transaction

        foreach ($timesheets as $staff_id => $rows) {
            $regular    = isset($rows['regular']) ? $rows['regular'] : [];
            $overtime   = isset($rows['overtime']) ? $rows['overtime'] : [];
            $fat        = isset($rows['fat']) ? $rows['fat'] : null;
            $unitPrice  = isset($rows['unit_price']) ? $rows['unit_price'] : 0;
            $payable    = isset($rows['payable']) ? $rows['payable'] : 0;
            $remarks    = isset($rows['remarks']) ? $rows['remarks'] : null;

            // compute totals
            $total_regular = 0;
            $total_overtime = 0;
            $days_present = 0;
            foreach ($regular as $d => $r) {
                $reg = is_numeric($r) ? (float)$r : 0;
                $total_regular += $reg;
                $ot = isset($overtime[$d]) && is_numeric($overtime[$d]) ? (float)$overtime[$d] : 0;
                $total_overtime += $ot;

                if ($reg + $ot > 0) $days_present++;
            }

            $masterData = [
                'staff_id'             => $staff_id,
                'project_id'           => $project_id,
                'month_year'           => $month_year,
                'total_hours'          => $total_regular + $total_overtime,
                'total_regular_hours'  => $total_regular,
                'total_overtime_hours' => $total_overtime,
                'days_present'         => $days_present,
                'fat'                  => $fat,
                'unit_price'           => $unitPrice,
                'payable'              => $payable,
                'remarks'              => $remarks,
            ];

            // check if exists
            $existing = $this->db->where([
                'staff_id'   => $staff_id,
                'project_id' => $project_id,
                'month_year' => $month_year
            ])->get(db_prefix().'timesheet_master')->row();

            if ($existing) {
                $this->db->where('id', $existing->id)->update(db_prefix().'timesheet_master', $masterData);
                $timesheet_id = $existing->id;
            } else {
                $masterData['created_at'] = date('Y-m-d H:i:s');
                $timesheet_id = $this->timesheet_model->insert_master($masterData);
            }

            // Upsert details (unchanged)
            foreach ($regular as $date => $r) {
                $rt = ($r === '' ? null : $r);
                $ot = isset($overtime[$date]) ? ($overtime[$date] === '' ? null : $overtime[$date]) : null;

                $detail = $this->db->where([
                    'timesheet_id' => $timesheet_id,
                    'work_date'    => $date
                ])->get(db_prefix().'timesheet_details')->row();

                $detailData = [
                    'regular_hours'  => $rt,
                    'overtime_hours' => $ot,
                ];

                if ($detail) {
                    $this->db->where('id', $detail->id)->update(db_prefix().'timesheet_details', $detailData);
                } else {
                    $this->timesheet_model->insert_detail($timesheet_id, $date, $rt, $ot);
                }
            }

            // NEW: Load staff info for full population
            $staff_i = $this->hr_payroll_model->get_staff_info($staff_id);

            // NEW: Upsert tblhrp_employees_value with FULL fields (OT + basics/iqama/etc.), MERGING with existing
            $existing_emp_value = $this->db->where([
                'staff_id' => $staff_id,
                'month'    => $month_for_emp
            ])->get('tblhrp_employees_value')->row();

            // Base data (always set)
            $emp_value_data = [
                'ot_hours' => number_format($total_overtime, 2), // Format as string for VARCHAR
                'rel_type' => $rel_type, // NEW: Set rel_type to match payroll
                'basic'    => isset($staff_i->basics) ? (string) $staff_i->basics : '', // From staff
                'employee_id_iqama' => $staff_i->iqama_number ?? '', // From staff
                'employee_account_no_iban' => $staff_i->bank_iban_number ?? '', // From staff
                'bank_code' => $staff_i->bank_swift_code ?? '', // From staff
            ];

            // NEW: Auto-populate GOSI/allowance/deduction from staff (mirrors controller logic)
            $basic_f = floatval(str_replace(',', '', $emp_value_data['basic'] ?? '0'));
            $g_h_f = isset($staff_i->fatallowance) ? floatval(str_replace(',', '', $staff_i->fatallowance)) : 0;
            $g_o_f = isset($staff_i->otherallowance) ? floatval(str_replace(',', '', $staff_i->otherallowance)) : 0;
            $g_d_f = 0; // Default (or from staff if field exists)
            $allow_f = isset($staff_i->siteallowance) ? floatval(str_replace(',', '', $staff_i->siteallowance)) : 0;
            $ded_f = isset($staff_i->advance) ? floatval(str_replace(',', '', $staff_i->advance)) : 0;

            $emp_value_data['gosi_basic_salary'] = $emp_value_data['basic']; // Assume = basic
            $emp_value_data['gosi_housing_allowance'] = number_format($g_h_f, 2);
            $emp_value_data['gosi_other_allowance'] = number_format($g_o_f, 2);
            $emp_value_data['gosi_deduction'] = number_format($g_d_f, 2);
            $emp_value_data['allowance'] = number_format($allow_f, 2);
            $emp_value_data['deduction'] = number_format($ded_f, 2);

            // NEW: Pre-calculate derived fields (mirrors ALWAYS block in controller)
            $ot_r_f = isset($staff_i->ot) ? floatval(str_replace(',', '', $staff_i->ot)) : 0;
            $ot_a_f = $total_overtime * $ot_r_f;
            $total_a_f = $basic_f + $g_h_f + $g_o_f - $g_d_f;
            $full_s_f = $basic_f + $ot_a_f + $allow_f - $ded_f;
            $bal_f = $full_s_f - $basic_f;

            $emp_value_data['ot_rate'] = (string) $ot_r_f;
            $emp_value_data['ot_amount'] = number_format($ot_a_f, 2);
            $emp_value_data['total_amount'] = number_format($total_a_f, 2);
            $emp_value_data['full_salary'] = number_format($full_s_f, 2);
            $emp_value_data['balance'] = number_format($bal_f, 2);

            if ($existing_emp_value) {
                // UPDATE: Preserve other fields (e.g., contracts, rebate), only set these
                $this->db->where('id', $existing_emp_value->id)
                        ->update('tblhrp_employees_value', $emp_value_data);
            } else {
                // INSERT: Full minimal record + rel_type
                $emp_value_data = array_merge($emp_value_data, [
                    'staff_id' => $staff_id,
                    'month'    => $month_for_emp,
                    'id'       => 0, // Auto-increment
                    // Defaults for rest (payroll can override on first edit)
                    'mention' => '',
                    'income_rebate_code' => 'A',
                    'income_tax_rate' => 'A',
                    'probationary_contracts' => '[]',
                    'primary_contracts' => '[]',
                    'probationary_effective' => null,
                    'probationary_expiration' => null,
                    'primary_effective' => null,
                    'primary_expiration' => null,
                ]);
                $this->db->insert('tblhrp_employees_value', $emp_value_data);
            }
        }
        $this->db->trans_complete(); // Commit transaction

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to save timesheets']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Timesheets saved successfully']);
        }
    }


    public function project_grid_data($project_id)
    {
        $project_id = (int)$project_id;
        $month = $this->input->get('month') ?: date('Y-m');

        $this->load->model('timesheet_model');
        $assignees = $this->timesheet_model->get_assignees_by_project($project_id);

        // build dates for the month
        $daysInMonth = (int)date('t', strtotime($month . '-01'));
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = date('Y-m-d', strtotime(sprintf('%s-%02d', $month, $d)));
        }

        $out = [];
        foreach ($assignees as $a) {
            $ts = $this->timesheet_model->get_timesheet_for_staff_month($project_id, $a['staff_id'], $month);
            $out[] = [
                'staff_id' => $a['staff_id'],
                'full_name' => $a['full_name'] ?: ($a['firstname'].' '.$a['lastname']),
                // 'badge' => isset($a['badge']) ? $a['badge'] : '',
                'master' => $ts['master'],
                'details' => $ts['details']
            ];
        }

        echo json_encode([
            'success' => true,
            'month' => $month,
            'dates' => $dates,
            'assignees' => $out
        ]);
        exit;
    }

    /**
     * AJAX: Create empty timesheets for all assignees in a project for a given month.
     */
    public function create_bulk_timesheet()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $project_id = $this->input->post('project_id');
        $month = $this->input->post('month'); // e.g., '2025-09'

        if (!$project_id || !$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            echo json_encode(['success' => false, 'message' => 'Invalid month or project']);
            return;
        }

        $this->load->model('timesheet_model');
        $success = $this->timesheet_model->create_empty_timesheet_for_month($project_id, $month);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Timesheet created for all assignees' : 'Creation failed'
        ]);
    }

    /**
     * Load the edit page for a specific project's month timesheets.
     * URL: /admin/timesheet/edit/{project_id}/{month}
     */
    public function edit($project_id, $month)
    {
        $project_id = (int) $project_id;
        if (!$project_id || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            show_404();
        }

        // Load project data (reuse Perfex's project model if needed)
        $this->load->model('projects_model');
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            show_404();
        }

        $data['project'] = $project;
        $data['month'] = $month;
        $data['title'] = 'Edit Timesheet - ' . $project->name . ' (' . $month . ')';

        $this->load->view('admin/timesheet/edit', $data); // We'll create this view next
    }

    public function get_project_months($project_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $project_id = (int) $project_id;
        $this->load->model('timesheet_model');
        $months = $this->timesheet_model->get_unique_months_by_project($project_id);

        echo json_encode([
            'success' => true,
            'months' => $months
        ]);
    }

    // Download PaySlip PDF
    public function download_payslip($project_id, $staff_id, $month)
    {
        try {
            $project_id = (int) $project_id;
            $staff_id = (int) $staff_id;

        // Get project details
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            set_alert('danger', 'Project not found');
            redirect(admin_url('projects'));
            return;
        }

        // Get staff details
        $staff = $this->db->select('s.name, s.iqama_number, s.bank_name, s.bank_account_number')
                         ->from(db_prefix() . 'staff s')
                         ->where('s.staffid', $staff_id)
                         ->get()
                         ->row();

        if (!$staff) {
            set_alert('danger', 'Employee not found');
            redirect(admin_url('timesheet/salary_details/' . $project_id . '/' . $month));
            return;
        }

        // Get current PAY data
        $pay = $this->db->select('basic_pay, overtime_pay, payout_type')
                       ->from(db_prefix() . 'staffpay')
                       ->where('staff_id', $staff_id)
                       ->order_by('start_date', 'DESC')
                       ->limit(1)
                       ->get()
                       ->row();

        // Check if employee has PAY data
        if (!$pay) {
            set_alert('warning', 'No pay data found for ' . $staff->name . '. Please add pay information in the employee PAY tab first.');
            redirect(admin_url('timesheet/salary_details/' . $project_id . '/' . $month));
            return;
        }

        // Get hours from timesheet
        $month_date = $month . '-01';
        $hours = $this->db->select('
                SUM(CAST(td.regular_hours AS DECIMAL(10,2))) as total_regular,
                SUM(CAST(td.overtime_hours AS DECIMAL(10,2))) as total_overtime
            ')
            ->from(db_prefix() . 'timesheet_details td')
            ->join(db_prefix() . 'timesheet_master tm', 'td.timesheet_id = tm.id')
            ->where('tm.staff_id', $staff_id)
            ->where('tm.project_id', $project_id)
            ->where('tm.month_year', $month_date)
            ->get()
            ->row();

        $regular_hours = (float)($hours->total_regular ?? 0);
        $overtime_hours = (float)($hours->total_overtime ?? 0);
        $total_hours = $regular_hours + $overtime_hours;

        // Calculate salary even if hours are 0 (for monthly employees)
        if ($pay) {
            $basic_pay = (float)$pay->basic_pay;
            $overtime_rate = (float)$pay->overtime_pay;
            $rate_type = $pay->payout_type;

            if ($rate_type == 'hourly') {
                $regular_pay = $regular_hours * $basic_pay;
                $hourly_rate = $basic_pay;
            } else {
                $regular_pay = $basic_pay;
                $hourly_rate = $regular_hours > 0 ? ($basic_pay / $regular_hours) : 0;
            }

            $overtime_pay = $overtime_hours * $overtime_rate;
        } else {
            $regular_pay = 0;
            $overtime_pay = 0;
            $hourly_rate = 0;
        }

        $gross_salary = $regular_pay + $overtime_pay;

        // Get allowances and deductions
        $adjustments = $this->db->select('type, date, description, amount')
            ->from(db_prefix() . 'timesheet_adjustments')
            ->where('staff_id', $staff_id)
            ->where('project_id', $project_id)
            ->where('DATE_FORMAT(date, "%Y-%m") =', $month)
            ->order_by('date', 'ASC')
            ->get()
            ->result_array();

        $allowances = [];
        $deductions = [];
        $total_allowances = 0;
        $total_deductions = 0;

        foreach ($adjustments as $adj) {
            if ($adj['type'] == 'allowance') {
                $allowances[] = $adj;
                $total_allowances += (float)$adj['amount'];
            } else {
                $deductions[] = $adj;
                $total_deductions += (float)$adj['amount'];
            }
        }

        $grand_total = $gross_salary + $total_allowances - $total_deductions;

        // Get payment records
        $payments = $this->db->select('p.*, s.firstname, s.lastname')
            ->from(db_prefix() . 'timesheet_salary_payments p')
            ->join(db_prefix() . 'staff s', 's.staffid = p.paid_by')
            ->where('p.staff_id', $staff_id)
            ->where('p.project_id', $project_id)
            ->where('p.month', $month)
            ->order_by('p.paid_date', 'DESC')
            ->get()
            ->result_array();

        $formatted_payments = [];
        $total_paid = 0;

        foreach ($payments as $payment) {
            $formatted_payments[] = [
                'paid_date' => date('Y-m-d', strtotime($payment['paid_date'])),
                'paid_from' => ucfirst($payment['paid_from']),
                'amount' => (float)$payment['amount'],
                'paid_by' => $payment['firstname'] . ' ' . $payment['lastname'],
            ];
            $total_paid += (float)$payment['amount'];
        }

        $balance_to_pay = $grand_total - $total_paid;
        $is_paid = $balance_to_pay <= 0.01;

        // Convert amount to words
        $this->load->library('App_number_to_word', ['decimal_mark' => 'decimal']);
        $amount_words = ucwords($this->app_number_to_word->convert($grand_total));
        $amount_in_words = 'Salary Total: ' . $amount_words . ' SAR Only';

        // Format period
        $period_start = date('Y-m-d', strtotime($month . '-01'));
        $period_end = date('Y-m-t', strtotime($month . '-01'));
        $period = date('Y-m-d', strtotime($period_start)) . ' - ' . date('Y-m-d', strtotime($period_end)) . ' (' . date('F, Y', strtotime($month . '-01')) . ')';

        // Get job title (profession type)
        $job_title = $this->db->select('professiontype_id')
                             ->from(db_prefix() . 'staff')
                             ->where('staffid', $staff_id)
                             ->get()
                             ->row();

        $job_title_name = '';
        if ($job_title && !empty($job_title->professiontype_id)) {
            // Load helper and use existing function
            $this->load->helper('profession_type');
            $job_title_name = get_profession_type_name($job_title->professiontype_id) ?: '';
        }

        // Prepare payslip data
        $payslip_data = [
            'employee_name' => $staff->name,
            'iqama_number' => $staff->iqama_number,
            'period' => $period,
            'job_title' => $job_title_name,
            'bank_name' => $staff->bank_name ?? '',
            'bank_account' => $staff->bank_account_number ?? '',
            'project_name' => $project->name,
            'basic' => $regular_pay,
            'regular_hours' => $regular_hours,
            'overtime_hours' => $overtime_hours,
            'total_hours' => $total_hours,
            'hourly_rate' => $hourly_rate,
            'gross_salary' => $gross_salary,
            'allowances' => $allowances,
            'total_allowances' => $total_allowances,
            'deductions' => $deductions,
            'total_deductions' => $total_deductions,
            'grand_total' => $grand_total,
            'amount_in_words' => $amount_in_words,
            'payments' => $formatted_payments,
            'total_paid' => $total_paid,
            'balance_to_pay' => $balance_to_pay,
            'is_paid' => $is_paid,
        ];

        // Generate PDF using direct instantiation
        require_once(APPPATH . 'libraries/pdf/Timesheet_payslip_pdf.php');

        $payslip_pdf = new Timesheet_payslip_pdf($payslip_data);
        $payslip_pdf->prepare();

            $filename = slug_it('Salary_Slip_' . $staff->name . '_' . date('F_Y', strtotime($month . '-01'))) . '.pdf';

            $payslip_pdf->Output($filename, 'D');

        } catch (Exception $e) {
            log_message('error', 'PaySlip PDF Generation Error: ' . $e->getMessage());
            set_alert('danger', 'Error generating payslip: ' . $e->getMessage());
            redirect(admin_url('timesheet/salary_details/' . $project_id . '/' . $month));
        }
    }

    // Get assigned projects for a staff member (AJAX)
    public function get_assigned_projects()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');

        if (!$staff_id) {
            echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
            return;
        }

        // Get all projects where this staff member is assigned
        $this->load->model('projects_model');
        $projects = $this->db->select('p.id, p.name')
            ->from(db_prefix() . 'projects p')
            ->join(db_prefix() . 'projectassignee pa', 'pa.project_id = p.id')
            ->where('pa.staff_id', (int)$staff_id)
            ->order_by('p.name', 'ASC')
            ->get()
            ->result_array();

        echo json_encode([
            'success' => true,
            'projects' => $projects
        ]);
    }

    // Save allowance/deduction adjustment (AJAX)
    public function save_adjustment()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $type = $this->input->post('type'); // 'allowance' or 'deduction'
        $date = $this->input->post('date');
        $description = $this->input->post('description');
        $amount = $this->input->post('amount');
        $current_month = $this->input->post('current_month');

        // Validation
        if (!$staff_id || !$project_id || !$type || !$date || !$description || !$amount) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        if (!in_array($type, ['allowance', 'deduction'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid adjustment type']);
            return;
        }

        if (!is_numeric($amount) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Amount must be a positive number']);
            return;
        }

        // Prepare data for insertion
        $data = [
            'staff_id' => (int)$staff_id,
            'project_id' => (int)$project_id,
            'type' => $type,
            'date' => $date,
            'description' => $description,
            'amount' => (float)$amount,
            'month_year' => date('Y-m-01', strtotime($current_month . '-01')),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert into database
        $this->db->insert(db_prefix() . 'timesheet_adjustments', $data);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Adjustment saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save adjustment']);
        }
    }

    // Get adjustment breakdown for a staff member (AJAX)
    public function get_adjustments()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $month = $this->input->post('month'); // YYYY-MM

        if (!$staff_id || !$project_id || !$month) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }

        $month_date = $month . '-01';

        // Get allowances
        $allowances = $this->db->select('id, date, description, amount')
            ->from(db_prefix() . 'timesheet_adjustments')
            ->where('staff_id', (int)$staff_id)
            ->where('project_id', (int)$project_id)
            ->where('month_year', $month_date)
            ->where('type', 'allowance')
            ->order_by('date', 'ASC')
            ->get()
            ->result_array();

        // Get deductions
        $deductions = $this->db->select('id, date, description, amount')
            ->from(db_prefix() . 'timesheet_adjustments')
            ->where('staff_id', (int)$staff_id)
            ->where('project_id', (int)$project_id)
            ->where('month_year', $month_date)
            ->where('type', 'deduction')
            ->order_by('date', 'ASC')
            ->get()
            ->result_array();

        echo json_encode([
            'success' => true,
            'allowances' => $allowances,
            'deductions' => $deductions
        ]);
    }

    // Get single employee timesheet data (AJAX)
    public function get_employee_timesheet()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $month = $this->input->post('month'); // YYYY-MM

        if (!$staff_id || !$project_id || !$month) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }

        $month_date = $month . '-01';

        // Get timesheet master record
        $master = $this->db->get_where(db_prefix() . 'timesheet_master', [
            'project_id' => (int)$project_id,
            'staff_id' => (int)$staff_id,
            'month_year' => $month_date
        ])->row();

        // Get all dates in the month
        $year = (int)date('Y', strtotime($month_date));
        $month_num = (int)date('m', strtotime($month_date));
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);

        $dates = [];
        for ($day = 1; $day <= $num_days; $day++) {
            $dates[] = sprintf('%04d-%02d-%02d', $year, $month_num, $day);
        }

        // Get timesheet details
        $details = [];
        if ($master) {
            $detail_records = $this->db->select('work_date, regular_hours, overtime_hours')
                ->from(db_prefix() . 'timesheet_details')
                ->where('timesheet_id', (int)$master->id)
                ->get()
                ->result_array();

            foreach ($detail_records as $record) {
                $details[$record['work_date']] = [
                    'regular_hours' => $record['regular_hours'],
                    'overtime_hours' => $record['overtime_hours']
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'master' => $master,
                'dates' => $dates,
                'details' => $details
            ]
        ]);
    }

    // NEW: Salary Details page
    public function salary_details($project_id, $month) {
        $project_id = (int) $project_id;
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            show_404();
        }

        // Fetch assignees (exclude Contractor/Supplier type: stafftype_id = 2)
        $assignees = $this->db->select('pa.staff_id, s.staffid as employee_id, s.name as full_name, s.iqama_number, s.stafftype_id, st.name as staff_type_name')
                            ->from(db_prefix() . 'projectassignee pa')
                            ->join(db_prefix() . 'staff s', 'pa.staff_id = s.staffid')
                            ->join(db_prefix() . 'stafftype st', 's.stafftype_id = st.id', 'left')
                            ->where('pa.project_id', $project_id)
                            ->where('s.stafftype_id !=', 2)
                            ->get()
                            ->result_array();

        $month_date = $month . '-01'; // e.g., '2025-10-01'

        foreach ($assignees as &$a) {
            // Get current PAY data (most recent pay record)
            $pay = $this->db->select('basic_pay, overtime_pay, payout_type')
                           ->from(db_prefix() . 'staffpay')
                           ->where('staff_id', $a['staff_id'])
                           ->order_by('start_date', 'DESC')
                           ->limit(1)
                           ->get()
                           ->row();

            // Get regular and overtime hours from timesheet
            $hours = $this->db->select('
                    SUM(CAST(td.regular_hours AS DECIMAL(10,2))) as total_regular,
                    SUM(CAST(td.overtime_hours AS DECIMAL(10,2))) as total_overtime
                ')
                ->from(db_prefix() . 'timesheet_details td')
                ->join(db_prefix() . 'timesheet_master tm', 'td.timesheet_id = tm.id')
                ->where('tm.staff_id', $a['staff_id'])
                ->where('tm.project_id', $project_id)
                ->where('tm.month_year', $month_date)
                ->get()
                ->row();

            $regular_hours = (float)($hours->total_regular ?? 0);
            $overtime_hours = (float)($hours->total_overtime ?? 0);

            // Calculate pay based on PAY data and rate type
            if ($pay) {
                $basic_pay = (float)$pay->basic_pay;
                $overtime_rate = (float)$pay->overtime_pay;
                $rate_type = $pay->payout_type; // 'hourly' or 'monthly'

                if ($rate_type == 'hourly') {
                    // Hourly: regular_hours × regular_rate
                    $a['regular_pay'] = $regular_hours * $basic_pay;
                } else {
                    // Monthly: full basic pay (prorated calculation can be added later)
                    $a['regular_pay'] = $basic_pay;
                }

                // Overtime: always hourly (overtime_hours × overtime_rate)
                $a['overtime_pay'] = $overtime_hours * $overtime_rate;
            } else {
                // Fallback if no PAY data exists
                $a['regular_pay'] = 0;
                $a['overtime_pay'] = 0;
            }

            // Store hours for display
            $a['regular_hours'] = $regular_hours;
            $a['overtime_hours'] = $overtime_hours;

            // Get allowances and deductions from adjustments table
            $allowances_sum = $this->db->select('SUM(amount) as total')
                ->from(db_prefix() . 'timesheet_adjustments')
                ->where('staff_id', $a['staff_id'])
                ->where('project_id', $project_id)
                ->where('month_year', $month_date)
                ->where('type', 'allowance')
                ->get()
                ->row();

            $deductions_sum = $this->db->select('SUM(amount) as total')
                ->from(db_prefix() . 'timesheet_adjustments')
                ->where('staff_id', $a['staff_id'])
                ->where('project_id', $project_id)
                ->where('month_year', $month_date)
                ->where('type', 'deduction')
                ->get()
                ->row();

            $a['allowances'] = (float)($allowances_sum->total ?? 0);
            $a['deductions'] = (float)($deductions_sum->total ?? 0);

            // Total calculation
            $a['total'] = $a['regular_pay'] + $a['overtime_pay'] + $a['allowances'] - $a['deductions'];

            // Get total paid for this staff member
            $total_paid = $this->db->select('SUM(amount) as paid')
                ->from(db_prefix() . 'timesheet_salary_payments')
                ->where('staff_id', $a['staff_id'])
                ->where('project_id', $project->id)
                ->where('month', $month)
                ->get()
                ->row();

            $a['paid'] = $total_paid ? (float)$total_paid->paid : 0;
            $a['to_pay'] = $a['total'] - $a['paid'];
        }

        $data['project'] = $project;
        $data['month'] = $month;
        $data['assignees'] = $assignees;
        $data['title'] = 'Salary Details - ' . date('F Y', strtotime($month . '-01'));
        $this->load->view('admin/timesheet/salary_details', $data);
    }

    // Save payment record (AJAX)
    public function save_payment()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $month = $this->input->post('month');
        $paid_from = $this->input->post('paid_from');
        $paid_date = $this->input->post('paid_date');
        $amount = $this->input->post('amount');
        $reference_number = $this->input->post('reference_number');
        $is_bank_transfer = $this->input->post('is_bank_transfer') ? 1 : 0;

        // Validation
        if (!$staff_id || !$project_id || !$month || !$paid_from || !$paid_date || !$amount) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            return;
        }

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero']);
            return;
        }

        // Insert payment record
        $data = [
            'staff_id' => $staff_id,
            'project_id' => $project_id,
            'month' => $month,
            'paid_from' => $paid_from,
            'paid_date' => $paid_date,
            'amount' => $amount,
            'reference_number' => $reference_number,
            'is_bank_transfer' => $is_bank_transfer,
            'paid_by' => get_staff_user_id(),
        ];

        $result = $this->db->insert(db_prefix() . 'timesheet_salary_payments', $data);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
        }
    }

    // Get payment records for a staff member in a specific month (AJAX)
    public function get_payments()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');
        $project_id = $this->input->post('project_id');
        $month = $this->input->post('month');

        if (!$staff_id || !$project_id || !$month) {
            echo json_encode(['success' => false, 'message' => 'Required parameters missing']);
            return;
        }

        // Get payment records
        $payments = $this->db->select('p.*, s.firstname, s.lastname')
            ->from(db_prefix() . 'timesheet_salary_payments p')
            ->join(db_prefix() . 'staff s', 's.staffid = p.paid_by')
            ->where('p.staff_id', $staff_id)
            ->where('p.project_id', $project_id)
            ->where('p.month', $month)
            ->order_by('p.paid_date', 'DESC')
            ->get()
            ->result_array();

        // Format payment data
        $formatted_payments = [];
        $total_paid = 0;

        foreach ($payments as $payment) {
            $formatted_payments[] = [
                'id' => $payment['id'],
                'paid_date' => date('Y-m-d', strtotime($payment['paid_date'])),
                'paid_from' => ucfirst($payment['paid_from']),
                'amount' => (float)$payment['amount'],
                'reference_number' => $payment['reference_number'],
                'is_bank_transfer' => (bool)$payment['is_bank_transfer'],
                'paid_by' => $payment['firstname'] . ' ' . $payment['lastname'],
            ];
            $total_paid += (float)$payment['amount'];
        }

        echo json_encode([
            'success' => true,
            'payments' => $formatted_payments,
            'total_paid' => $total_paid
        ]);
    }

    // Delete payment record (AJAX)
    public function delete_payment()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $payment_id = $this->input->post('payment_id');

        if (!$payment_id) {
            echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
            return;
        }

        $result = $this->db->where('id', $payment_id)
                          ->delete(db_prefix() . 'timesheet_salary_payments');

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete payment']);
        }
    }
}
