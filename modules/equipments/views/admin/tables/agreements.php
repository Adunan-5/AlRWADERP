<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'agreement_number',
    'agreement_type',
    'party_id',
    'start_date',
    'end_date',
    'payment_terms_days',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_agreements';

// We still need to select id for row actions, but it's not a displayed column
$additionalSelect = ['id'];

// Additional filtering for tabs
$additionalWhere = [];

// Filter by type if specified
if (isset($_POST['type'])) {
    $type = $_POST['type'];
    if ($type !== 'all') {
        $additionalWhere[] = 'AND agreement_type = "' . $this->db->escape_str($type) . '"';
    }
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $additionalWhere, $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        // Agreement Number column with link
        if ($aColumns[$i] == 'agreement_number') {
            $agreement_url = admin_url('equipments/agreements/view/' . $aRow['id']);
            $_data = '<a href="' . $agreement_url . '" class="tw-font-medium">' . e($_data) . '</a>';
        }
        // Agreement Type column with badges
        elseif ($aColumns[$i] == 'agreement_type') {
            if ($_data == 'supplier') {
                $_data = '<span class="label label-primary"><i class="fa fa-building-o"></i> ' . _l('supplier') . '</span>';
            } else {
                $_data = '<span class="label label-success"><i class="fa fa-user-o"></i> ' . _l('client') . '</span>';
            }
        }
        // Party Name column - resolve based on agreement type
        elseif ($aColumns[$i] == 'party_id') {
            // Load party name based on agreement type
            $CI =& get_instance();
            if ($aRow['agreement_type'] == 'supplier') {
                $CI->db->select('name');
                $CI->db->where('id', $_data);
                $party = $CI->db->get(db_prefix() . 'suppliers')->row();

                if ($party) {
                    $party_url = admin_url('suppliers/supplier/' . $_data);
                    $_data = '<a href="' . $party_url . '">' . e($party->name) . '</a>';
                } else {
                    $_data = '-';
                }
            } else {
                $CI->db->select('company');
                $CI->db->where('userid', $_data);
                $party = $CI->db->get(db_prefix() . 'clients')->row();

                if ($party) {
                    $party_url = admin_url('clients/client/' . $_data);
                    $_data = '<a href="' . $party_url . '">' . e($party->company) . '</a>';
                } else {
                    $_data = '-';
                }
            }
        }
        // Date columns
        elseif ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'end_date') {
            $_data = !empty($_data) ? _d($_data) : '-';
        }
        // Payment Terms column
        elseif ($aColumns[$i] == 'payment_terms_days') {
            $_data = !empty($_data) ? $_data . ' ' . _l('days') : '-';
        }
        // Status column with badges
        elseif ($aColumns[$i] == 'status') {
            $status_labels = [
                'draft' => '<span class="label label-default">' . _l('draft') . '</span>',
                'active' => '<span class="label label-success">' . _l('active') . '</span>',
                'expired' => '<span class="label label-warning">' . _l('expired') . '</span>',
                'terminated' => '<span class="label label-danger">' . _l('terminated') . '</span>',
                'completed' => '<span class="label label-info">' . _l('completed') . '</span>',
            ];
            $_data = isset($status_labels[$_data]) ? $status_labels[$_data] : $_data;
        }

        $row[] = $_data;
    }

    // Add Options column with action buttons
    $options = '<div class="tw-flex tw-items-center tw-gap-2">';

    if (has_permission('equipment_agreements', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/agreements/view/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('view') . '">';
        $options .= '<i class="fa fa-eye"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_agreements', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/agreements/edit/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('edit') . '">';
        $options .= '<i class="fa fa-edit"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_agreements', '', 'delete')) {
        $options .= '<a href="#" class="btn btn-danger btn-xs delete-agreement" data-id="' . $aRow['id'] . '" title="' . _l('delete') . '">';
        $options .= '<i class="fa fa-trash"></i>';
        $options .= '</a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
