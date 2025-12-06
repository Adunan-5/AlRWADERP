<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

if (!function_exists('update_staff_excel')) {
    /**
     * Create/update one staff row in /uploads/staffdetails/staff_details.xlsx
     *
     * @param int  $staff_id
     * @param bool $is_new (not strictly required, but kept for clarity)
     */
    function update_staff_excel($staff_id, $is_new = false)
    {
        $CI = &get_instance();

        // ---- Paths / constants
        $excelPath = FCPATH . 'uploads/staffdetails/staff_details.xlsx';
        $headingRow = 2; // row 1 is the span/merge row in your sheet

        // ---- 1) Fetch staff row
        $staff = $CI->db->get_where(db_prefix().'staff', ['staffid' => (int)$staff_id])->row_array();
        if (!$staff) return;

        // ---- 2) Resolve names for IDs using your helper functions
        // NOTE: Replace these functions with your actual helpers.
        $companyName      = !empty($staff['companytype_id']) ? get_company_type_name($staff['companytype_id']) : '';
        $ownEmployeeType  = !empty($staff['ownemployee_id']) ? get_ownemployee_type_name($staff['ownemployee_id']) : '';
        $nationalityName  = !empty($staff['country']) ? get_country_name($staff['country']) : '';

        // ---- 3) Date formatting helper
        $fmt = function($value) {
            if (empty($value)) return '';
            // value may be 'YYYY-MM-DD' or datetime; normalize
            $ts = is_numeric($value) ? (int)$value : strtotime($value);
            if (!$ts) return '';
            return date('d-m-Y', $ts); // <---- Excel date format we’ll use
        };

        // ---- 4) Build a map: Excel Heading => Value
        // Keep EXACT order/labels as your sheet row 2.
        $rowData = [
            'Sl.No.'                     => '', // we will fill later as sequence
            'COMPANY NAME'               => $companyName,
            'CLIENT/SITE'                => '', // not kept in DB
            'TYPE OF EMPLOYEE'           => $ownEmployeeType,
            'EMP #'                      => (string)($staff['code'] ?? ''),
            'NAME OF EMPLOYEE'           => (string)($staff['name'] ?? ''),
            'IQAMA NO.'                  => (string)($staff['iqama_number'] ?? ''),
            'HIJRI IQAMA EXP.'           => $fmt($staff['iqama_expiry_hijri'] ?? ''),
            'IQAMA EXP.'                 => $fmt($staff['iqama_expiry'] ?? ''),
            'JOINING DATE'               => $fmt($staff['joining_date'] ?? ''),
            'CONTRACT PERIOD(MONTHS)'    => calc_contract_months($staff['contract_start_date'] ?? null, $staff['contract_end_date'] ?? null),
            'CONTRACT MATURITY DATE'     => $fmt($staff['contract_end_date'] ?? ''),
            'LAST CONTRACT RENEWAL DATE' => '', // not in schema
            'VACATION DUE DATE'          => '', // not in schema
            'QIWA EXPAIRY'               => '', // not in schema
            'PASSPORT NO.'               => (string)($staff['passport_number'] ?? ''),
            'NATIONALITY'                => $nationalityName,
            'PASSPORT EXP.'              => $fmt($staff['passport_expiry'] ?? ''),
            'DOB'                        => $fmt($staff['dob'] ?? ''),
            'VISA NO.'                   => (string)($staff['visa_number'] ?? ''),
            'BORDER NO.'                 => (string)($staff['border_number'] ?? ''),
            'PROFESSION'                 => (string)($staff['iqama_profession'] ?? ''),
            'CONTACT NO.'                => (string)($staff['phonenumber'] ?? ''),
            'HOME ADDRESS'               => (string)($staff['address'] ?? ''),
            'EMERGENCY CONTACT (HOME)'   => (string)($staff['emgcontactno'] ?? ''),
            'MAIL ID'                    => (string)($staff['email'] ?? ''),
            'IBAN/ACCOUNT NO.'           => (string)($staff['bank_iban_number'] ?? $staff['bank_account_number'] ?? ''),
            'BANK NAME'                  => (string)($staff['bank_name'] ?? ''),
            'BASIC'                      => (string)($staff['basics'] ?? ''),
            'OT/HOUR'                    => (string)($staff['ot'] ?? ''),
            'FAT ALLOWANCE'              => (string)($staff['fatallowance'] ?? ''),
            'SITE ALLOWANCE'             => (string)($staff['siteallowance'] ?? ''),
            'OTHER ALLOWANCE'            => (string)($staff['otherallowance'] ?? ''),
            'TOTAL'                      => calc_total_salary(
                                              $staff['basics'] ?? 0,
                                              $staff['fatallowance'] ?? 0,
                                              $staff['siteallowance'] ?? 0,
                                              $staff['otherallowance'] ?? 0
                                           ),
            'ADVANCE'                    => (string)($staff['advance'] ?? ''),
            'LAST SALARY REV DATE'       => '', // not in schema
            'ARAMCO ID#'                 => (string)($staff['aramcoid'] ?? ''),
            'ARAMCO ID EXPIRY'           => $fmt($staff['aramcoidexpiry'] ?? ''),
            'Accomodation'               => (string)($staff['accomodation'] ?? ''),
            'STATUS'                     => ((int)($staff['active'] ?? 1) === 1 ? 'Active' : 'Inactive'),
        ];

        // ---- 5) Open or create spreadsheet
        $spreadsheet = null;
        $sheet = null;

        if (file_exists($excelPath) && filesize($excelPath) > 0) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $spreadsheet = $reader->load($excelPath);
                $sheet = $spreadsheet->getActiveSheet();
            } catch (Throwable $e) {
                // If file is corrupted, recreate
            }
        }

        if (!$spreadsheet) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // Row 1 could be a merged title; leave empty or set your title if you want
            // Set headings on row 2
            $col = 1;
            foreach (array_keys($rowData) as $heading) {
                $sheet->setCellValueByColumnAndRow($col, $headingRow, $heading);
                $col++;
            }
        }

        // ---- 6) Build a "heading => columnIndex" map from row 2
        $headingToCol = [];
        $lastCol = $sheet->getHighestColumn();
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($c = 1; $c <= $lastColIndex; $c++) {
            $h = trim((string)$sheet->getCellByColumnAndRow($c, $headingRow)->getValue());
            if ($h !== '') $headingToCol[$h] = $c;
        }

        // ---- 7) Find the row for this staff by "EMP #"
        $empCol = $headingToCol['EMP #'] ?? null;
        if (!$empCol) return; // headings mismatch; stop

        $targetRow = null;
        $highestRow = $sheet->getHighestRow();
        for ($r = $headingRow + 1; $r <= $highestRow; $r++) {
            $codeInSheet = trim((string)$sheet->getCellByColumnAndRow($empCol, $r)->getValue());
            if ($codeInSheet !== '' && $codeInSheet === (string)$rowData['EMP #']) {
                $targetRow = $r; // update existing row
                break;
            }
        }
        if (!$targetRow) {
            $targetRow = $highestRow >= $headingRow ? $highestRow + 1 : $headingRow + 1;
        }

        // ---- 8) Write values to row
        foreach ($rowData as $heading => $value) {
            if (!isset($headingToCol[$heading])) continue;
            $colIndex = $headingToCol[$heading];
            $sheet->setCellValueExplicitByColumnAndRow($colIndex, $targetRow, $value, DataType::TYPE_STRING);
            
            // Apply bold + font size 14 to this new/updated cell
            $sheet->getStyleByColumnAndRow($colIndex, $targetRow)
                ->getFont()
                ->setBold(true)
                ->setSize(14);
        }

        // Sl.No. => just the running serial (exclude header rows)
        if (isset($headingToCol['Sl.No.'])) {
            $sheet->setCellValueByColumnAndRow($headingToCol['Sl.No.'], $targetRow, ($targetRow - $headingRow));
        }

        // Adjust row height for the whole row (auto height)
        $sheet->getRowDimension($targetRow)->setRowHeight(-1);
        // $sheet->getRowDimension($targetRow)->setRowHeight(25);

        // ---- 9) Save
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelPath);
    }
}

/** Helper: calculate months between dates (rounded down) */
if (!function_exists('calc_contract_months')) {
    function calc_contract_months($start, $end) {
        if (empty($start) || empty($end)) return '';
        $s = is_numeric($start) ? (int)$start : strtotime($start);
        $e = is_numeric($end)   ? (int)$end   : strtotime($end);
        if (!$s || !$e || $e < $s) return '';
        $diff = (int)floor(($e - $s) / (30 * 24 * 60 * 60)); // rough months
        return $diff > 0 ? $diff : '';
    }
}

/** Helper: salary total = basic + all allowances */
if (!function_exists('calc_total_salary')) {
    function calc_total_salary($basic, $fat, $site, $other) {
        $b = floatval($basic);
        $f = floatval($fat);
        $s = floatval($site);
        $o = floatval($other);
        $sum = $b + $f + $s + $o;
        return $sum > 0 ? number_format($sum, 2, '.', '') : '';
    }
}

// function import_staff_excel($filePath)
// {
//     $CI =& get_instance();
//     $spreadsheet = IOFactory::load($filePath);
//     $sheet = $spreadsheet->getActiveSheet();

//     $headingRow = 2; // headers are in row 2
//     $highestRow = $sheet->getHighestRow();

//     // ------------------
//     // Build map: Header -> ColumnIndex
//     // ------------------
//     $headingToCol = [];
//     $lastCol = $sheet->getHighestColumn();
//     $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
//     for ($c = 1; $c <= $lastColIndex; $c++) {
//         $h = trim((string)$sheet->getCellByColumnAndRow($c, $headingRow)->getValue());
//         if ($h !== '') {
//             $headingToCol[$h] = $c;
//         }
//     }

//     // ------------------
//     // Loop rows from row 3
//     // ------------------
//     for ($row = $headingRow + 1; $row <= $highestRow; $row++) {

//         $companyName       = get_cell($sheet, $headingToCol, 'COMPANY NAME', $row);
//         $employeeType      = get_cell($sheet, $headingToCol, 'TYPE OF EMPLOYEE', $row);
//         $empCode           = get_cell($sheet, $headingToCol, 'EMP #', $row);
//         $employeeName      = get_cell($sheet, $headingToCol, 'NAME OF EMPLOYEE', $row);
//         $iqamaNo           = get_cell($sheet, $headingToCol, 'IQAMA NO.', $row);
//         $iqamaHijriExp     = get_cell($sheet, $headingToCol, 'HIJRI IQAMA EXP.', $row);
//         $iqamaExp          = excel_date(get_cell($sheet, $headingToCol, 'IQAMA EXP.', $row));
//         $joiningDate       = excel_date(get_cell($sheet, $headingToCol, 'JOINING DATE', $row));
//         $joiningDateHijri  = $joiningDate ? gregorian_to_hijri($joiningDate) : null;
//         $contractMaturity  = excel_date(get_cell($sheet, $headingToCol, 'CONTRACT MATURITY DATE', $row));
//         $qiwaExpiry        = excel_date(get_cell($sheet, $headingToCol, 'QIWA EXPAIRY', $row));
//         $passportNo        = get_cell($sheet, $headingToCol, 'PASSPORT NO.', $row);
//         $nationality       = normalize_country(get_cell($sheet, $headingToCol, 'NATIONALITY', $row));
//         $passportExp       = excel_date(get_cell($sheet, $headingToCol, 'PASSPORT EXP.', $row));
//         $dob               = excel_date(get_cell($sheet, $headingToCol, 'DOB', $row));
//         $profession        = get_cell($sheet, $headingToCol, 'PROFESSION', $row);
//         $contactNo         = get_cell($sheet, $headingToCol, 'CONTACT NO.', $row);
//         $address           = get_cell($sheet, $headingToCol, 'HOME ADDRESS', $row);
//         $emgContact        = get_cell($sheet, $headingToCol, 'EMERGENCY CONTACT (HOME)', $row);
//         $email             = get_cell($sheet, $headingToCol, 'MAIL ID', $row);
//         $iban              = get_cell($sheet, $headingToCol, 'IBAN/ACCOUNT NO.', $row);
//         $bankName          = get_cell($sheet, $headingToCol, 'BANK NAME', $row);
//         $basic             = get_cell($sheet, $headingToCol, 'BASIC', $row);
//         $ot                = get_cell($sheet, $headingToCol, 'OT RATE', $row);
//         $fatAllowance      = get_cell($sheet, $headingToCol, 'FAT ALLOWANCE', $row);
//         $siteAllowance     = get_cell($sheet, $headingToCol, 'SITE ALLOWANCE', $row);
//         $otherAllowance    = get_cell($sheet, $headingToCol, 'OTHER ALLOWANCE', $row);
//         $advance           = get_cell($sheet, $headingToCol, 'ADVANCE', $row);
//         $lastSalaryRevDate = excel_date(get_cell($sheet, $headingToCol, 'LAST SALARY REV DATE', $row));
//         $aramcoId          = get_cell($sheet, $headingToCol, 'ARAMCO ID#', $row);
//         $aramcoExp         = excel_date(get_cell($sheet, $headingToCol, 'ARAMCO ID EXPIRY', $row));
//         $accomodation      = get_cell($sheet, $headingToCol, 'ACCOMODATION', $row);
//         $visaNo            = get_cell($sheet, $headingToCol, 'VISA NO.', $row);
//         $borderNo          = get_cell($sheet, $headingToCol, 'BORDER NO.', $row);
//         $insuranceExpiry   = excel_date(get_cell($sheet, $headingToCol, 'INSURANCE EXPIRY', $row));
//         // $clientSite        = get_cell($sheet, $headingToCol, 'CLIENT/SITE', $row);

//         // ------------------
//         // Lookup / Create IDs
//         // ------------------
//         $companyId = null;
//         if ($companyName) {
//             $companyId = get_company_type_id_by_name($companyName);
//             if (!$companyId) {
//                 $CI->db->insert('tblcompanytype', ['name' => $companyName]);
//                 $companyId = $CI->db->insert_id();
//             }
//         }

//         $ownEmployeeId = null;
//         if ($employeeType) {
//             $skipTypes = ['not joined', 'na', 'n/a']; // list of values to ignore

//             if (!in_array(strtolower($employeeType), $skipTypes)) {
//                 $ownEmployeeId = get_ownemployee_type_id_by_name($employeeType);
//                 if (!$ownEmployeeId) {
//                     $CI->db->insert('tblownemployeetype', ['name' => $employeeType]);
//                     $ownEmployeeId = $CI->db->insert_id();
//                 }
//             }
//         }

//         $professionId = null;
//         if ($profession) {
//             $professionId = get_profession_type_id_by_name($profession);
//             if (!$professionId) {
//                 $CI->db->insert('tblprofessiontype', ['name' => $profession]);
//                 $professionId = $CI->db->insert_id();
//             }
//         }

//         $countryId = null;
//         if ($nationality) {
//             $countryId = $CI->db->select('country_id')
//                                 ->where('LOWER(short_name)', strtolower($nationality))
//                                 ->get('tblcountries')
//                                 ->row('country_id');
//         }

//         // ------------------
//         // Staff Data
//         // ------------------
//         $staffData = [
//             'companytype_id'    => $companyId,
//             'ownemployee_id'    => $ownEmployeeId,
//             'code'              => $empCode,
//             'name'              => $employeeName,
//             'iqama_number'      => $iqamaNo,
//             'iqama_expiry_hijri'=> $iqamaHijriExp,
//             'iqama_expiry'      => $iqamaExp,
//             'joining_date'      => $joiningDate,
//             'joining_date_hijri'  => $joiningDateHijri,
//             'contract_end_date' => $contractMaturity,
//             'qiwa_expiry'       => $qiwaExpiry,
//             'passport_number'   => $passportNo,
//             'country'           => $countryId,
//             'passport_expiry'   => $passportExp,
//             'dob'               => $dob,
//             'professiontype_id' => $professionId,
//             'phonenumber'       => $contactNo,
//             'address'           => $address,
//             'emgcontactno'      => $emgContact,
//             'email'             => $email,
//             'bank_iban_number'  => $iban,
//             'bank_name'         => $bankName,
//             'basics'            => $basic,
//             'ot'                => $ot,
//             'fatallowance'      => $fatAllowance,
//             'siteallowance'     => $siteAllowance,
//             'otherallowance'    => $otherAllowance,
//             'advance'           => $advance,
//             'last_password_change' => $lastSalaryRevDate, // map to correct field if needed
//             'aramcoid'          => $aramcoId,
//             'aramcoidexpiry'    => $aramcoExp,
//             'accomodation'      => $accomodation,
//             'visa_number'       => $visaNo,
//             'border_number'     => $borderNo,
//             'insurance_expiry'  => $insuranceExpiry,
//             'datecreated'       => date('Y-m-d H:i:s')
//         ];

//         // ------------------
//         // Insert / Update staff
//         // ------------------
//         $staffId = null;
//         if (!empty($empCode)) {
//             $existing = $CI->db->where('code', $empCode)->get('tblstaff')->row();
//             if ($existing) {
//                 $staffId = $existing->staffid;
//                 $CI->db->where('staffid', $staffId)->update('tblstaff', $staffData);
//             } else {
//                 $CI->db->insert('tblstaff', $staffData);
//                 $staffId = $CI->db->insert_id();
//             }
//         }

//         // ------------------
//         // Project / Site Handling
//         // ------------------
//         // if ($staffId && $clientSite) {
//         //     $skipSites = [
//         //         'vacation', 'standby', 'exit', 'not joined', 'na',
//         //         'documents submitted', 'new joinee/standby', 'schedule waiting'
//         //     ];

//         //     if (!in_array(strtolower($clientSite), $skipSites)) {
//         //         $projectId = get_project_id_by_name($clientSite);
//         //         if (!$projectId) {
//         //             // Minimal project creation
//         //             $CI->db->insert('tblprojects', [
//         //                 'name'            => $clientSite,
//         //                 'clientid'        => 1, // set default clientid
//         //                 'status'          => 1,
//         //                 'project_created' => date('Y-m-d'),
//         //                 'addedfrom'       => get_staff_user_id()
//         //             ]);
//         //             $projectId = $CI->db->insert_id();
//         //         }

//         //         // Link staff to project if not already linked
//         //         $exists = $CI->db->where('project_id', $projectId)
//         //                          ->where('staff_id', $staffId)
//         //                          ->get('tblproject_members')
//         //                          ->row();
//         //         if (!$exists) {
//         //             $CI->db->insert('tblproject_members', [
//         //                 'project_id' => $projectId,
//         //                 'staff_id'   => $staffId
//         //             ]);
//         //         }
//         //     }
//         // }
//     }
// }


function import_staff_excel($filePath)
{
    $CI =& get_instance();
    $CI->load->database();

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $headerRow = 1;

    $headers = [
        'SL NO','COMPANY NAME','CLIENT/SITE','TYPE OF EMPLOYEE','EMP #','NAME OF THE EMPLOYEE',
        'IQAMA NO','HIJRI IQAMA EXP','IQAMA EXP','JOINING DATE','CONTRACT PERIOD (MONTHS)',
        'CONTRACT START DATE','CONTRACT MATURITY DATE','LAST CONTRACT RENEWAL DATE',
        'VACATION DUE DATE','REVIEW','QIWA EXPIRY','PASSPORT NO','NATIONALITY',
        'PASSPORT EXP','DOB','PROFESSION','CONTACT NO','HOME ADDRESS',
        'MAIL ID','IBAN/ACCOUNT NO','BANK NAME','BASIC','OT RATE',
        'FAT ALLOWANCE','SITE ALLOWANCE','OTHER ALLOWANCE','TOTAL','ADVANCE',
        'LAST SALARY REV DATE','COMMENTS','ARAMCO ID#','ARAMCO ID EXPIRY',
        'ACCOMODATION','STATUS','VISA NO','BORDER NO','INSURANCE EXPIRY'
    ];

    // Map headers to column indexes
    $colMap = [];
    foreach ($headers as $i => $h) $colMap[$h] = $i + 1;

    $highestRow = $sheet->getHighestRow();

    for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
        $iqamaNo = trim($sheet->getCellByColumnAndRow($colMap['IQAMA NO'], $row)->getValue());
        if (!$iqamaNo) continue; // skip if no IQAMA

        $data = [];
        foreach ($headers as $header) {
            $col = $colMap[$header];
            $val = trim((string)$sheet->getCellByColumnAndRow($col, $row)->getValue());
            $data[$header] = $val;
        }

        // Use your helper functions to get IDs
        $companyId = get_company_type_id_by_name($data['COMPANY NAME']);
        $employeeTypeId = get_ownemployee_type_id_by_name($data['TYPE OF EMPLOYEE']);
        $professionId = get_profession_type_id_by_name($data['PROFESSION']);
        $projectId = get_project_id_by_name($data['CLIENT/SITE']);

        // Country lookup
        $countryId = null;
        if (!empty($data['NATIONALITY'])) {
            $countryId = $CI->db->select('country_id')
                ->where('LOWER(short_name)', strtolower($data['NATIONALITY']))
                ->get(db_prefix() . 'countries')
                ->row('country_id');
        }

        $staffData = [
            'companytype_id' => $companyId,
            'project_id' => $projectId,
            'ownemployee_id' => $employeeTypeId,
            'code' => $data['EMP #'],
            'name' => $data['NAME OF THE EMPLOYEE'],
            'iqama_number' => $data['IQAMA NO'],
            'iqama_expiry_hijri' => $data['HIJRI IQAMA EXP'],
            'iqama_expiry' => excel_date($data['IQAMA EXP']),
            'joining_date' => excel_date($data['JOINING DATE']),
            'contract_period_months' => $data['CONTRACT PERIOD (MONTHS)'],
            'contract_start_date' => excel_date($data['CONTRACT START DATE']),
            'contract_end_date' => excel_date($data['CONTRACT MATURITY DATE']),
            'review' => $data['REVIEW'],
            'qiwa_expiry' => excel_date($data['QIWA EXPIRY']),
            'passport_number' => $data['PASSPORT NO'],
            'country' => $countryId,
            'passport_expiry' => excel_date($data['PASSPORT EXP']),
            'dob' => excel_date($data['DOB']),
            'professiontype_id' => $professionId,
            'phonenumber' => $data['CONTACT NO'],
            'address' => $data['HOME ADDRESS'],
            'email' => $data['MAIL ID'],
            'bank_iban_number' => $data['IBAN/ACCOUNT NO'],
            'bank_name' => $data['BANK NAME'],
            'basics' => $data['BASIC'],
            'ot' => $data['OT RATE'],
            'fatallowance' => $data['FAT ALLOWANCE'],
            'siteallowance' => $data['SITE ALLOWANCE'],
            'otherallowance' => $data['OTHER ALLOWANCE'],
            'advance' => $data['ADVANCE'],
            'last_salary_revision_date' => excel_date($data['LAST SALARY REV DATE']),
            'last_salary_revision_comments' => $data['COMMENTS'],
            'aramcoid' => $data['ARAMCO ID#'],
            'aramcoidexpiry' => excel_date($data['ARAMCO ID EXPIRY']),
            'accomodation' => $data['ACCOMODATION'],
            'status' => strtoupper($data['STATUS']),
            'visa_number' => $data['VISA NO'],
            'border_number' => $data['BORDER NO'],
            'insurance_expiry' => excel_date($data['INSURANCE EXPIRY'])
        ];

        $existing = $CI->db->where('iqama_number', $iqamaNo)->get(db_prefix() . 'staff')->row();
        if ($existing) {
            $CI->db->where('staffid', $existing->staffid)->update(db_prefix() . 'staff', $staffData);
        } else {
            $CI->db->insert(db_prefix() . 'staff', $staffData);
        }
    }
}


/** Safe cell getter */
function get_cell($sheet, $map, $header, $row)
{
    if (!isset($map[$header])) return null;
    $val = trim((string)$sheet->getCellByColumnAndRow($map[$header], $row)->getValue());
    if ($val === '' || in_array(strtolower($val), ['na','n/a'])) return null;
    return $val;
}

/** Convert Excel date to Y-m-d */
function excel_date($value)
{
    if (empty($value) || in_array(strtolower($value), ['na','n/a'])) {
        return null;
    }
    if (is_numeric($value)) {
        return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
    }
    $time = strtotime($value);
    return $time ? date('Y-m-d', $time) : null;
}

/** Normalize nationality */
function normalize_country($value)
{
    if (empty($value) || in_array(strtolower($value), ['na','n/a'])) return null;
    $map = [
        'india' => 'India', 'indian' => 'India',
        'pakistan' => 'Pakistan', 'pakistani' => 'Pakistan',
        'nepal' => 'Nepal', 'nepali' => 'Nepal',
        'srilanka' => 'Sri Lanka', 'sri lanka' => 'Sri Lanka',
        'srilankan' => 'Sri Lanka', 'sri lankan' => 'Sri Lanka',
        'bangladesh' => 'Bangladesh', 'bangladeshi' => 'Bangladesh',
        'filipino' => 'Philippines',
        'uk' => 'United Kingdom',
        'saudi arabia' => 'Saudi Arabia'
    ];
    $key = strtolower(trim($value));
    return $map[$key] ?? $value;
}

if (!function_exists('export_staff_master_data')) {
    function export_staff_master_data()
    {
        $CI =& get_instance();

        $CI->db->select('s.*, 
                         c.name as company_name, 
                         oe.name as employee_type, 
                         co.short_name as nationality,
                         p.name as project_name')
            ->from('tblstaff s')
            ->join('tblcompanytype c', 'c.id = s.companytype_id', 'left')
            ->join('tblownemployeetype oe', 'oe.id = s.ownemployee_id', 'left')
            ->join('tblcountries co', 'co.country_id = s.country', 'left')
            ->join('tblprojects p', 'p.id = s.project_id', 'left');

        $staffList = $CI->db->get()->result();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Excel headers (ALL CAPS)
        $headers = [
            'SL NO', 'COMPANY NAME', 'CLIENT/SITE', 'TYPE OF EMPLOYEE', 'EMP #', 'NAME OF THE EMPLOYEE',
            'IQAMA NO', 'HIJRI IQAMA EXP', 'IQAMA EXP', 'JOINING DATE', 'CONTRACT PERIOD (MONTHS)', 
            'CONTRACT START DATE', 'CONTRACT MATURITY DATE', 'LAST CONTRACT RENEWAL DATE',
            'VACATION DUE DATE', 'REVIEW', 'QIWA EXPIRY', 'PASSPORT NO', 'NATIONALITY', 
            'PASSPORT EXP', 'DOB', 'PROFESSION', 'CONTACT NO', 'HOME ADDRESS', 
            'MAIL ID', 'IBAN/ACCOUNT NO', 'BANK NAME', 'BASIC', 'OT RATE', 
            'FAT ALLOWANCE', 'SITE ALLOWANCE', 'OTHER ALLOWANCE', 'TOTAL', 'ADVANCE',
            'LAST SALARY REV DATE', 'COMMENTS', 'ARAMCO ID#', 'ARAMCO ID EXPIRY',
            'ACCOMODATION', 'STATUS', 'VISA NO', 'BORDER NO', 'INSURANCE EXPIRY'
        ];

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E5E5'] // light gray
            ]
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        $sl = 1;
        foreach ($staffList as $s) {
            // Profession (professiontype_id → tblprofessiontype.name)
            $profession = '';
            if (!empty($s->professiontype_id)) {
                $profession = get_profession_type_name($s->professiontype_id);
            }

            // IBAN fallback
            $accountNo = !empty($s->bank_iban_number) ? $s->bank_iban_number : $s->bank_account_number;

            // Total Salary
            $total = (float)$s->basics + (float)$s->ot + (float)$s->fatallowance + (float)$s->siteallowance + (float)$s->otherallowance;

            // Vacation Due Date (end_date only)
            $vacation_due = $CI->db->select('end_date')
                ->from('tblstaffvacations')
                ->where('staff_id', $s->staffid)
                ->order_by('id', 'DESC')
                ->get()->row();
            $vacation_due_date = $vacation_due ? $vacation_due->end_date : '';

            // Last Contract Renewal Date = vacation end date + 1 day
            $last_renewal_date = '';
            if ($vacation_due && !empty($vacation_due->end_date)) {
                $last_renewal_date = date('Y-m-d', strtotime($vacation_due->end_date . ' +1 day'));
            }

            // Status in UPPERCASE
            $status = !empty($s->status) ? strtoupper($s->status) : '';

            $dataRow = [
                $sl++,
                $s->company_name ?: '',
                $s->project_name ?: '',
                $s->employee_type ?: '',
                $s->code ?: '',
                $s->name ?: '',
                $s->iqama_number ?: '',
                $s->iqama_expiry_hijri ?: '',
                $s->iqama_expiry ?: '',
                $s->joining_date ?: '',
                $s->contract_period_months ?: '', // mapped directly
                $s->contract_start_date ?: '',
                $s->contract_end_date ?: '',
                $last_renewal_date ?: '',
                $vacation_due_date ?: '',
                $s->review ?: '',
                $s->qiwa_expiry ?: '',
                $s->passport_number ?: '',
                $s->nationality ?: '',
                $s->passport_expiry ?: '',
                $s->dob ?: '',
                $profession ?: '',
                $s->phonenumber ?: '',
                $s->address ?: '',
                $s->email ?: '',
                $accountNo ?: '',
                $s->bank_name ?: '',
                $s->basics ?: '',
                $s->ot ?: '',
                $s->fatallowance ?: '',
                $s->siteallowance ?: '',
                $s->otherallowance ?: '',
                $total ?: '',
                $s->advance ?: '',
                $s->last_salary_revision_date ?: '',
                $s->last_salary_revision_comments ?: '',
                $s->aramcoid ?: '',
                $s->aramcoidexpiry ?: '',
                $s->accomodation ?: '',
                $status,
                $s->visa_number ?: '',
                $s->border_number ?: '',
                $s->insurance_expiry ?: ''
            ];

            $col = 1;
            foreach ($dataRow as $key => $val) {
                // Force text for specific columns
                if (in_array($headers[$key], ['EMP #', 'IQAMA NO', 'PASSPORT NO', 'IBAN/ACCOUNT NO', 'VISA NO', 'BORDER NO'])) {
                    $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueByColumnAndRow($col, $row, $val);
                }
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // Dynamic file name: Master Data + date
        $filename = 'Master Data ' . date('jS M Y') . '.xlsx';

        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

/**
 * Parse Excel file and return preview data
 *
 * @param string $filePath Path to Excel file
 * @return array Array of preview data with action indicators
 */
if (!function_exists('parse_staff_excel_for_preview')) {
    function parse_staff_excel_for_preview($filePath)
    {
        $CI =& get_instance();
        $CI->load->database();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headerRow = 1;

        $headers = [
            'SL NO','COMPANY NAME','CLIENT/SITE','TYPE OF EMPLOYEE','EMP #','NAME OF THE EMPLOYEE',
            'IQAMA NO','HIJRI IQAMA EXP','IQAMA EXP','JOINING DATE','CONTRACT PERIOD (MONTHS)',
            'CONTRACT START DATE','CONTRACT MATURITY DATE','LAST CONTRACT RENEWAL DATE',
            'VACATION DUE DATE','REVIEW','QIWA EXPIRY','PASSPORT NO','NATIONALITY',
            'PASSPORT EXP','DOB','PROFESSION','CONTACT NO','HOME ADDRESS',
            'MAIL ID','IBAN/ACCOUNT NO','BANK NAME','BASIC','OT RATE',
            'FAT ALLOWANCE','SITE ALLOWANCE','OTHER ALLOWANCE','TOTAL','ADVANCE',
            'LAST SALARY REV DATE','COMMENTS','ARAMCO ID#','ARAMCO ID EXPIRY',
            'ACCOMODATION','STATUS','VISA NO','BORDER NO','INSURANCE EXPIRY'
        ];

        // Map headers to column indexes
        $colMap = [];
        foreach ($headers as $i => $h) {
            $colMap[$h] = $i + 1;
        }

        $highestRow = $sheet->getHighestRow();
        $preview_data = [];

        // Track identifiers to detect duplicates within the file
        $seenIqama = [];
        $seenPassport = [];
        $seenEmpCode = [];

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            // Collect data from Excel first
            $data = [];
            foreach ($headers as $header) {
                $col = $colMap[$header];
                $val = trim((string)$sheet->getCellByColumnAndRow($col, $row)->getValue());
                $data[$header] = $val;
            }

            $iqamaNo = $data['IQAMA NO'];
            $passportNo = $data['PASSPORT NO'];
            $empCode = $data['EMP #'];

            // Skip row only if ALL identifiers are empty
            if (empty($iqamaNo) && empty($passportNo) && empty($empCode)) {
                continue;
            }

            // Check for duplicates within the Excel file
            $duplicateErrors = [];
            $isDuplicate = false;

            // Check IQAMA duplicate in file
            if (!empty($iqamaNo)) {
                if (isset($seenIqama[$iqamaNo])) {
                    $duplicateErrors[] = 'IQAMA "' . $iqamaNo . '" already seen in row ' . $seenIqama[$iqamaNo];
                    $isDuplicate = true;
                } else {
                    $seenIqama[$iqamaNo] = $row;
                }
            }

            // Check Passport duplicate in file
            if (!empty($passportNo)) {
                if (isset($seenPassport[$passportNo])) {
                    $duplicateErrors[] = 'Passport "' . $passportNo . '" already seen in row ' . $seenPassport[$passportNo];
                    $isDuplicate = true;
                } else {
                    $seenPassport[$passportNo] = $row;
                }
            }

            // Check Employee Code duplicate in file
            if (!empty($empCode)) {
                if (isset($seenEmpCode[$empCode])) {
                    $duplicateErrors[] = 'Employee Code "' . $empCode . '" already seen in row ' . $seenEmpCode[$empCode];
                    $isDuplicate = true;
                } else {
                    $seenEmpCode[$empCode] = $row;
                }
            }

            // Check if this employee already exists in database (by IQAMA, Passport, or Employee Code)
            $existing = null;
            $existingBy = null;

            if (!empty($iqamaNo)) {
                $existing = $CI->db->where('iqama_number', $iqamaNo)->get(db_prefix() . 'staff')->row();
                if ($existing) $existingBy = 'IQAMA';
            }

            if (!$existing && !empty($passportNo)) {
                $existing = $CI->db->where('passport_number', $passportNo)->get(db_prefix() . 'staff')->row();
                if ($existing) $existingBy = 'Passport';
            }

            if (!$existing && !empty($empCode)) {
                $existing = $CI->db->where('code', $empCode)->get(db_prefix() . 'staff')->row();
                if ($existing) $existingBy = 'Employee Code';
            }

            // Determine action
            $action = 'insert';
            if ($isDuplicate) {
                $action = 'duplicate';
            } elseif ($existing) {
                $action = 'update';
            }

            // Build preview row
            $preview_data[] = [
                'row_number' => $row,
                'action' => $action,
                'existing_id' => $existing ? $existing->staffid : null,
                'existing_by' => $existingBy,
                'code' => $data['EMP #'],
                'name' => $data['NAME OF THE EMPLOYEE'],
                'iqama_number' => $data['IQAMA NO'],
                'passport_number' => $data['PASSPORT NO'],
                'company_name' => $data['COMPANY NAME'],
                'employee_type' => $data['TYPE OF EMPLOYEE'],
                'nationality' => $data['NATIONALITY'],
                'email' => $data['MAIL ID'],
                'phonenumber' => $data['CONTACT NO'],
                'basics' => $data['BASIC'],
                'status' => strtoupper($data['STATUS']),
                'duplicate_errors' => $duplicateErrors,
                'raw_data' => $data // Store raw data for processing
            ];
        }

        return $preview_data;
    }
}

/**
 * Import only selected rows from Excel file
 *
 * @param string $filePath Path to Excel file
 * @param array $selectedRows Array of row indices to import
 * @return array Result with success and error counts
 */
if (!function_exists('import_staff_excel_selected_rows')) {
    function import_staff_excel_selected_rows($filePath, $selectedRows)
    {
        $CI =& get_instance();
        $CI->load->database();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headerRow = 1;

        $headers = [
            'SL NO','COMPANY NAME','CLIENT/SITE','TYPE OF EMPLOYEE','EMP #','NAME OF THE EMPLOYEE',
            'IQAMA NO','HIJRI IQAMA EXP','IQAMA EXP','JOINING DATE','CONTRACT PERIOD (MONTHS)',
            'CONTRACT START DATE','CONTRACT MATURITY DATE','LAST CONTRACT RENEWAL DATE',
            'VACATION DUE DATE','REVIEW','QIWA EXPIRY','PASSPORT NO','NATIONALITY',
            'PASSPORT EXP','DOB','PROFESSION','CONTACT NO','HOME ADDRESS',
            'MAIL ID','IBAN/ACCOUNT NO','BANK NAME','BASIC','OT RATE',
            'FAT ALLOWANCE','SITE ALLOWANCE','OTHER ALLOWANCE','TOTAL','ADVANCE',
            'LAST SALARY REV DATE','COMMENTS','ARAMCO ID#','ARAMCO ID EXPIRY',
            'ACCOMODATION','STATUS','VISA NO','BORDER NO','INSURANCE EXPIRY'
        ];

        // Map headers to column indexes
        $colMap = [];
        foreach ($headers as $i => $h) {
            $colMap[$h] = $i + 1;
        }

        $highestRow = $sheet->getHighestRow();
        $success_count = 0;
        $error_count = 0;
        $currentIndex = 0;

        // Convert selectedRows to associative array for faster lookup
        $selectedRowsMap = array_flip($selectedRows);

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            // Collect data from Excel first
            $data = [];
            foreach ($headers as $header) {
                $col = $colMap[$header];
                $val = trim((string)$sheet->getCellByColumnAndRow($col, $row)->getValue());
                $data[$header] = $val;
            }

            $iqamaNo = $data['IQAMA NO'];
            $passportNo = $data['PASSPORT NO'];
            $empCode = $data['EMP #'];

            // Skip row only if ALL identifiers are empty
            if (empty($iqamaNo) && empty($passportNo) && empty($empCode)) {
                continue;
            }

            // Check if this row index should be processed
            if (!isset($selectedRowsMap[$currentIndex])) {
                $currentIndex++;
                continue;
            }

            try {
                // Use helper functions to get/create IDs
                $companyId = get_company_type_id_by_name($data['COMPANY NAME']);
                $employeeTypeId = get_ownemployee_type_id_by_name($data['TYPE OF EMPLOYEE']);
                $professionId = get_profession_type_id_by_name($data['PROFESSION']);
                $projectId = get_project_id_by_name($data['CLIENT/SITE']);

                // Country lookup
                $countryId = null;
                if (!empty($data['NATIONALITY'])) {
                    $countryId = $CI->db->select('country_id')
                        ->where('LOWER(short_name)', strtolower($data['NATIONALITY']))
                        ->get(db_prefix() . 'countries')
                        ->row('country_id');
                }

                // Build staff data array
                $staffData = [
                    'companytype_id' => $companyId,
                    'project_id' => $projectId,
                    'ownemployee_id' => $employeeTypeId,
                    'code' => $data['EMP #'],
                    'name' => $data['NAME OF THE EMPLOYEE'],
                    'iqama_number' => $data['IQAMA NO'],
                    'iqama_expiry_hijri' => $data['HIJRI IQAMA EXP'],
                    'iqama_expiry' => excel_date($data['IQAMA EXP']),
                    'joining_date' => excel_date($data['JOINING DATE']),
                    'contract_period_months' => $data['CONTRACT PERIOD (MONTHS)'],
                    'contract_start_date' => excel_date($data['CONTRACT START DATE']),
                    'contract_end_date' => excel_date($data['CONTRACT MATURITY DATE']),
                    'review' => $data['REVIEW'],
                    'qiwa_expiry' => excel_date($data['QIWA EXPIRY']),
                    'passport_number' => $data['PASSPORT NO'],
                    'country' => $countryId,
                    'passport_expiry' => excel_date($data['PASSPORT EXP']),
                    'dob' => excel_date($data['DOB']),
                    'professiontype_id' => $professionId,
                    'phonenumber' => $data['CONTACT NO'],
                    'address' => $data['HOME ADDRESS'],
                    'email' => $data['MAIL ID'],
                    'bank_iban_number' => $data['IBAN/ACCOUNT NO'],
                    'bank_name' => $data['BANK NAME'],
                    'basics' => $data['BASIC'],
                    'ot' => $data['OT RATE'],
                    'fatallowance' => $data['FAT ALLOWANCE'],
                    'siteallowance' => $data['SITE ALLOWANCE'],
                    'otherallowance' => $data['OTHER ALLOWANCE'],
                    'advance' => $data['ADVANCE'],
                    'last_salary_revision_date' => excel_date($data['LAST SALARY REV DATE']),
                    'last_salary_revision_comments' => $data['COMMENTS'],
                    'aramcoid' => $data['ARAMCO ID#'],
                    'aramcoidexpiry' => excel_date($data['ARAMCO ID EXPIRY']),
                    'accomodation' => $data['ACCOMODATION'],
                    'status' => strtoupper($data['STATUS']),
                    'visa_number' => $data['VISA NO'],
                    'border_number' => $data['BORDER NO'],
                    'insurance_expiry' => excel_date($data['INSURANCE EXPIRY'])
                ];

                // Check if record exists and insert/update accordingly
                $existing = $CI->db->where('iqama_number', $iqamaNo)
                                   ->get(db_prefix() . 'staff')
                                   ->row();

                if ($existing) {
                    $CI->db->where('staffid', $existing->staffid)
                           ->update(db_prefix() . 'staff', $staffData);
                } else {
                    $CI->db->insert(db_prefix() . 'staff', $staffData);
                }

                $success_count++;

            } catch (Exception $e) {
                $error_count++;
                log_message('error', 'Error importing row ' . $row . ': ' . $e->getMessage());
            }

            $currentIndex++;
        }

        return [
            'success' => $success_count,
            'errors' => $error_count
        ];
    }
}