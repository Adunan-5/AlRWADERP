<?php
header('Content-Type: application/json; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    's.name as staff_name',    // 0
    's.phonenumber',           // 1
    's.email',                 // 2
    'pa.badge',                // 3
    'pa.regular_rate',         // 4
    'pa.overtime_rate',        // 5
    'pa.rate_type',            // 6
    's.iqama_number',          // 7
];

// main index
$sIndexColumn = 'pa.id';
$sTable       = db_prefix().'projectassignee pa';

$join = [
    'JOIN ' . db_prefix() . 'staff s ON s.staffid = pa.staff_id',
    'LEFT JOIN ' . db_prefix() . 'stafftype st ON st.id = s.stafftype_id',
    'LEFT JOIN ' . db_prefix() . 'suppliers sup ON sup.id = s.supplier_id',
];

$where = [];

if (isset($params['project_id']) && (int)$params['project_id'] > 0) {
    $where[] = 'AND pa.project_id = ' . (int)$params['project_id'];
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [
        'pa.id',
        's.staffid',
        'pa.skills',
        'pa.start_date',
        'pa.end_date',
        'pa.equipment_id',
        'st.name as staff_type_name',
        's.stafftype_id',
        'sup.name as supplier_name',
    ] // extra fields
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Staff name link with IQAMA number and employee type underneath
    $staffNameHtml = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . e($aRow['staff_name']) . '</a>';

    // Add IQAMA number if exists
    if (!empty($aRow['iqama_number'])) {
        $staffNameHtml .= '<br><small class="text-muted">IQAMA: ' . e($aRow['iqama_number']) . '</small>';
    }

    // Add employee type if exists
    if (!empty($aRow['staff_type_name'])) {
        $staffNameHtml .= '<br><small class="text-muted">' . e($aRow['staff_type_name']);

        // If staff type is Contractor/Supplier (ID = 2) and supplier exists, add supplier name
        if ($aRow['stafftype_id'] == 2 && !empty($aRow['supplier_name'])) {
            $staffNameHtml .= ' - ' . e($aRow['supplier_name']);
        }

        $staffNameHtml .= '</small>';
    }

    $row[] = $staffNameHtml;

    // Phone
    $row[] = e($aRow['phonenumber']);
    // Email
    $row[] = e($aRow['email']);

    // Badge
    $row[] = e($aRow['badge']);
    // Regular Rate
    $row[] = app_format_money($aRow['regular_rate'], get_base_currency());
    // Overtime Rate
    $row[] = app_format_money($aRow['overtime_rate'], get_base_currency());
    // Rate Type
    $row[] = ucfirst($aRow['rate_type']);

    // Options column with *all needed data*
    $row[] = '
    <button type="button" 
        class="btn btn-sm btn-default edit-member" 
        data-id="' . $aRow['id'] . '" 
        data-staffid="' . $aRow['staffid'] . '" 
        data-name="' . e($aRow['staff_name']) . '" 
        data-skills="' . e($aRow['skills'] ?? '') . '" 
        data-phonenumber="' . e($aRow['phonenumber'] ?? '') . '" 
        data-start_date="' . e($aRow['start_date'] ?? '') . '"
        data-end_date="' . e($aRow['end_date'] ?? '') . '"
        data-equipment_id="' . e($aRow['equipment_id'] ?? '') . '"
        data-email="' . e($aRow['email'] ?? '') . '" 
        data-badge="' . e($aRow['badge'] ?? '') . '"  
        data-regular_rate="' . e($aRow['regular_rate'] ?? '') . '" 
        data-overtime_rate="' . e($aRow['overtime_rate'] ?? '') . '" 
        data-rate_type="' . e($aRow['rate_type'] ?? '') . '">
        Edit
    </button>
    <button type="button" 
        class="btn btn-sm btn-danger remove-member" 
        data-id="' . $aRow['id'] . '" 
        data-name="' . e($aRow['staff_name']) . '">
        Remove
    </button>
    ';

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;
