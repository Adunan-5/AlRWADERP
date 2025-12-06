<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                                    <?php echo _l('rfq'); ?> - <?php echo $rfq->rfq_number; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_rfq', '', 'edit')) { ?>
                                    <a href="<?php echo admin_url('equipments/rfq/edit/' . $rfq->id); ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i> <?php echo _l('edit'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('equipments/rfq'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr>

                        <!-- RFQ Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('rfq_details'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('rfq_number'); ?></strong></td>
                                            <td><?php echo $rfq->rfq_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('rfq_date'); ?></strong></td>
                                            <td><?php echo _d($rfq->rfq_date); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('required_by_date'); ?></strong></td>
                                            <td><?php echo $rfq->required_by_date ? _d($rfq->required_by_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('expected_start_date'); ?></strong></td>
                                            <td><?php echo $rfq->expected_start_date ? _d($rfq->expected_start_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('expected_duration_months'); ?></strong></td>
                                            <td><?php echo $rfq->expected_duration_months ? $rfq->expected_duration_months . ' ' . _l('months') : '-'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('additional_information'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('project_reference'); ?></strong></td>
                                            <td><?php echo $rfq->project_reference ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('client'); ?></strong></td>
                                            <td><?php echo $rfq->client_name ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('status'); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_labels = [
                                                    'draft' => '<span class="label label-default">' . _l('rfq_status_draft') . '</span>',
                                                    'sent' => '<span class="label label-info">' . _l('rfq_status_sent') . '</span>',
                                                    'responses_received' => '<span class="label label-primary">' . _l('rfq_status_responses_received') . '</span>',
                                                    'evaluated' => '<span class="label label-warning">' . _l('rfq_status_evaluated') . '</span>',
                                                    'closed' => '<span class="label label-success">' . _l('rfq_status_closed') . '</span>',
                                                    'cancelled' => '<span class="label label-danger">' . _l('rfq_status_cancelled') . '</span>',
                                                ];
                                                echo isset($status_labels[$rfq->status]) ? $status_labels[$rfq->status] : $rfq->status;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('created_by'); ?></strong></td>
                                            <td><?php echo $rfq->created_by_name; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('created_at'); ?></strong></td>
                                            <td><?php echo _dt($rfq->created_at); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <?php if ($rfq->terms_conditions) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('terms_and_conditions'); ?></h4>
                                <div class="well">
                                    <?php echo nl2br(e($rfq->terms_conditions)); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Notes -->
                        <?php if ($rfq->notes) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('notes'); ?></h4>
                                <div class="well">
                                    <?php echo nl2br(e($rfq->notes)); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- RFQ Items Section -->
                        <div class="row tw-mt-6">
                            <div class="col-md-12">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <h4 class="tw-font-semibold tw-mb-0"><?php echo _l('rfq_items'); ?></h4>
                                    <?php if (has_permission('equipment_rfq', '', 'edit')) { ?>
                                        <button type="button" class="btn btn-primary" onclick="openItemModal()">
                                            <i class="fa fa-plus"></i> <?php echo _l('add_rfq_item'); ?>
                                        </button>
                                    <?php } ?>
                                </div>

                                <div id="rfq-items-container">
                                    <div class="text-center tw-py-8">
                                        <i class="fa fa-spinner fa-spin fa-2x tw-text-neutral-400"></i>
                                        <p class="tw-text-neutral-500 tw-mt-2">Loading items...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RFQ Suppliers Section -->
                        <div class="row tw-mt-6">
                            <div class="col-md-12">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <h4 class="tw-font-semibold tw-mb-0"><?php echo _l('rfq_suppliers'); ?></h4>
                                    <?php if (has_permission('equipment_rfq', '', 'edit')) { ?>
                                        <button type="button" class="btn btn-primary" onclick="openSupplierModal()">
                                            <i class="fa fa-plus"></i> <?php echo _l('add_supplier_to_rfq'); ?>
                                        </button>
                                    <?php } ?>
                                </div>

                                <div id="rfq-suppliers-container">
                                    <div class="text-center tw-py-8">
                                        <i class="fa fa-spinner fa-spin fa-2x tw-text-neutral-400"></i>
                                        <p class="tw-text-neutral-500 tw-mt-2">Loading suppliers...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Received Quotations Section -->
                        <?php if (has_permission('equipment_quotation', '', 'view')) { ?>
                        <div class="row tw-mt-6">
                            <div class="col-md-12">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <h4 class="tw-font-semibold tw-mb-0"><?php echo _l('supplier_quotations'); ?></h4>
                                    <div>
                                        <?php if (has_permission('equipment_quotation', '', 'create')) { ?>
                                            <a href="<?php echo admin_url('equipments/quotation/add/' . $rfq->id); ?>" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> <?php echo _l('add_quotation'); ?>
                                            </a>
                                        <?php } ?>
                                        <a href="<?php echo admin_url('equipments/quotation/compare/' . $rfq->id); ?>" class="btn btn-info">
                                            <i class="fa fa-columns"></i> <?php echo _l('compare_quotations'); ?>
                                        </a>
                                    </div>
                                </div>

                                <div id="rfq-quotations-container">
                                    <div class="text-center tw-py-8">
                                        <i class="fa fa-spinner fa-spin fa-2x tw-text-neutral-400"></i>
                                        <p class="tw-text-neutral-500 tw-mt-2">Loading quotations...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Add/Edit RFQ Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_rfq_item'); ?></h4>
            </div>
            <form id="itemForm">
                <div class="modal-body">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="rfq_id" value="<?php echo $rfq->id; ?>">
                    <input type="hidden" name="item_id" id="item_id">

                    <!-- Item Type Selection -->
                    <div class="form-group">
                        <label><span class="text-danger">*</span> <?php echo _l('item_type'); ?></label>
                        <div class="radio radio-primary">
                            <input type="radio" name="item_type" id="item_type_equipment" value="equipment" checked>
                            <label for="item_type_equipment"><?php echo _l('equipment_only'); ?></label>
                        </div>
                        <div class="radio radio-primary">
                            <input type="radio" name="item_type" id="item_type_operator" value="operator">
                            <label for="item_type_operator"><?php echo _l('operator_only'); ?></label>
                        </div>
                        <div class="radio radio-primary">
                            <input type="radio" name="item_type" id="item_type_both" value="equipment_with_operator">
                            <label for="item_type_both"><?php echo _l('equipment_with_operator'); ?></label>
                        </div>
                    </div>
                    <hr>

                    <!-- Equipment Fields -->
                    <div id="equipment-fields">
                        <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('equipment_details'); ?></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $equipments = $this->db->select('id, name, platenumber_code, equipmenttype')
                                    ->get(db_prefix() . 'equipments')->result_array();
                                echo render_select('equipment_id', $equipments, ['id', ['name', 'platenumber_code']], _l('equipment') . ' ' . _l('optional'));
                                ?>
                                <small class="text-muted"><?php echo _l('select_if_equipment_exists'); ?></small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="equipment_description"><span class="text-danger req-equipment">*</span> <?php echo _l('equipment_description'); ?></label>
                                    <textarea class="form-control" name="equipment_description" id="equipment_description" rows="2" placeholder="<?php echo _l('eg_100t_mobile_crane'); ?>"></textarea>
                                    <small class="text-muted"><?php echo _l('enter_equipment_specifications'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operator Fields -->
                    <div id="operator-fields" style="display: none;">
                        <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('operator_details'); ?></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $operators = $this->db->select('id, name, nationality')
                                    ->where('status !=', 'terminated')
                                    ->order_by('name', 'ASC')
                                    ->get(db_prefix() . 'operators')->result_array();
                                echo render_select('operator_id', $operators, ['id', ['name', 'nationality']], _l('operator') . ' ' . _l('optional'));
                                ?>
                                <small class="text-muted"><?php echo _l('select_if_operator_exists'); ?></small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="operator_description"><span class="text-danger req-operator">*</span> <?php echo _l('operator_description'); ?></label>
                                    <textarea class="form-control" name="operator_description" id="operator_description" rows="2" placeholder="<?php echo _l('eg_crane_operator_saudi'); ?>"></textarea>
                                    <small class="text-muted"><?php echo _l('enter_operator_requirements'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity"><span class="text-danger">*</span> <?php echo _l('quantity'); ?></label>
                                <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit"><?php echo _l('unit'); ?></label>
                                <input type="text" class="form-control" name="unit" id="unit" value="unit">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="standard_hours_per_day"><?php echo _l('standard_hours_per_day'); ?></label>
                                <input type="number" class="form-control" name="standard_hours_per_day" id="standard_hours_per_day" value="8" step="0.5" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="days_per_month"><?php echo _l('days_per_month'); ?></label>
                                <input type="number" class="form-control" name="days_per_month" id="days_per_month" value="26" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expected_duration_months"><?php echo _l('expected_duration_months'); ?></label>
                                <input type="number" class="form-control" name="expected_duration_months" id="expected_duration_months" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="item_notes"><?php echo _l('notes'); ?></label>
                        <textarea class="form-control" name="notes" id="item_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_supplier_to_rfq'); ?></h4>
            </div>
            <form id="supplierForm">
                <div class="modal-body">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="rfq_id" value="<?php echo $rfq->id; ?>">
                    <input type="hidden" name="supplier_rfq_id" id="supplier_rfq_id">

                    <?php
                    $suppliers = $this->db->select('id, name, email')
                        ->from(db_prefix() . 'suppliers')
                        ->get()->result_array();
                    echo render_select('supplier_id', $suppliers, ['id', 'name'], _l('supplier'), '', ['required' => true]);
                    ?>

                    <?php echo render_date_input('sent_date', _l('sent_date')); ?>
                    <?php echo render_date_input('response_received_date', _l('response_received_date')); ?>

                    <div class="form-group">
                        <label for="response_status"><?php echo _l('response_status'); ?></label>
                        <select name="response_status" class="form-control selectpicker" required>
                            <option value="pending"><?php echo _l('supplier_response_pending'); ?></option>
                            <option value="quoted"><?php echo _l('supplier_response_quoted'); ?></option>
                            <option value="declined"><?php echo _l('supplier_response_declined'); ?></option>
                            <option value="no_response"><?php echo _l('supplier_response_no_response'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="supplier_notes"><?php echo _l('notes'); ?></label>
                        <textarea class="form-control" name="notes" id="supplier_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    'use strict';

    var rfqId = <?php echo $rfq->id; ?>;

    // Load RFQ items, suppliers, and quotations on page load
    loadRFQItems();
    loadRFQSuppliers();
    <?php if (has_permission('equipment_quotation', '', 'view')) { ?>
    loadRFQQuotations();
    <?php } ?>

    // Item form submission
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var itemId = $('#item_id').val();
        var url = itemId ? admin_url + 'equipments/rfq/update_item/' + itemId : admin_url + 'equipments/rfq/add_item';

        $.post(url, formData, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('#itemModal').modal('hide');
                loadRFQItems();
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    // Supplier form submission
    $('#supplierForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var supplierId = $('#supplier_rfq_id').val();
        var url = supplierId ? admin_url + 'equipments/rfq/update_supplier/' + supplierId : admin_url + 'equipments/rfq/add_supplier';

        $.post(url, formData, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('#supplierModal').modal('hide');
                loadRFQSuppliers();
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    // Load RFQ items
    function loadRFQItems() {
        $.get(admin_url + 'equipments/rfq/get_items/' + rfqId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                renderRFQItems(result.items);
            }
        });
    }

    // Load RFQ suppliers
    function loadRFQSuppliers() {
        $.get(admin_url + 'equipments/rfq/get_suppliers/' + rfqId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                renderRFQSuppliers(result.suppliers);
            }
        });
    }

    // Render RFQ items
    function renderRFQItems(items) {
        var html = '';

        if (items.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo _l('no_rfq_items'); ?></div>';
        } else {
            items.forEach(function(item) {
                var itemTitle = '';
                var itemIcon = '';
                var itemTypeLabel = '';

                if (item.item_type === 'equipment') {
                    itemTitle = item.equipment_description || '<?php echo _l('equipment'); ?>';
                    itemIcon = '<i class="fa fa-wrench tw-mr-2"></i>';
                    itemTypeLabel = '<span class="label label-info"><?php echo _l('equipment_only'); ?></span>';
                } else if (item.item_type === 'operator') {
                    itemTitle = item.operator_description || '<?php echo _l('operator'); ?>';
                    itemIcon = '<i class="fa fa-user tw-mr-2"></i>';
                    itemTypeLabel = '<span class="label label-success"><?php echo _l('operator_only'); ?></span>';
                } else if (item.item_type === 'equipment_with_operator') {
                    itemTitle = item.equipment_description || '<?php echo _l('equipment_with_operator'); ?>';
                    itemIcon = '<i class="fa fa-users tw-mr-2"></i>';
                    itemTypeLabel = '<span class="label label-primary"><?php echo _l('equipment_with_operator'); ?></span>';
                }

                html += '<div class="panel panel-default tw-mb-4">';
                html += '<div class="panel-heading">';
                html += '<div class="tw-flex tw-items-center tw-justify-between">';
                html += '<div><h5 class="tw-mb-0">' + itemIcon + '<strong>' + itemTitle + '</strong></h5>' + itemTypeLabel + '</div>';
                html += '<div class="btn-group">';
                html += '<button class="btn btn-default btn-xs" onclick="editItem(' + item.id + ')"><i class="fa fa-edit"></i></button>';
                html += '<button class="btn btn-danger btn-xs" onclick="deleteItem(' + item.id + ')"><i class="fa fa-trash"></i></button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="panel-body">';

                if (item.item_type !== 'operator' && item.equipment_description) {
                    html += '<div class="tw-mb-3">';
                    html += '<h6 class="tw-font-semibold"><i class="fa fa-wrench tw-mr-2"></i><?php echo _l('equipment_details'); ?></h6>';
                    html += '<p class="tw-text-neutral-600">' + item.equipment_description + '</p>';
                    html += '</div>';
                }

                if (item.item_type !== 'equipment' && item.operator_description) {
                    html += '<div class="tw-mb-3">';
                    html += '<h6 class="tw-font-semibold"><i class="fa fa-user tw-mr-2"></i><?php echo _l('operator_details'); ?></h6>';
                    html += '<p class="tw-text-neutral-600">' + item.operator_description + '</p>';
                    html += '</div>';
                }

                html += '<div class="row tw-mb-3">';
                html += '<div class="col-md-4"><strong><?php echo _l('quantity'); ?>:</strong> ' + item.quantity + ' ' + (item.unit || 'unit') + '</div>';
                html += '<div class="col-md-4"><strong><?php echo _l('standard_hours_per_day'); ?>:</strong> ' + (item.standard_hours_per_day || '-') + '</div>';
                html += '<div class="col-md-4"><strong><?php echo _l('days_per_month'); ?>:</strong> ' + (item.days_per_month || '-') + '</div>';
                html += '</div>';

                if (item.expected_duration_months) {
                    html += '<div class="tw-mb-2"><strong><?php echo _l('expected_duration_months'); ?>:</strong> ' + item.expected_duration_months + '</div>';
                }

                if (item.notes) {
                    html += '<div class="tw-mb-2"><strong><?php echo _l('notes'); ?>:</strong> ' + item.notes + '</div>';
                }

                html += '</div></div>';
            });
        }

        $('#rfq-items-container').html(html);
    }

    // Render RFQ suppliers
    function renderRFQSuppliers(suppliers) {
        var html = '';

        if (suppliers.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo _l('no_suppliers_added'); ?></div>';
        } else {
            html += '<table class="table table-bordered">';
            html += '<thead><tr>';
            html += '<th><?php echo _l('supplier'); ?></th>';
            html += '<th><?php echo _l('sent_date'); ?></th>';
            html += '<th><?php echo _l('response_status'); ?></th>';
            html += '<th><?php echo _l('response_received_date'); ?></th>';
            html += '<th><?php echo _l('options'); ?></th>';
            html += '</tr></thead><tbody>';

            suppliers.forEach(function(supplier) {
                var statusLabels = {
                    'pending': '<span class="label label-default"><?php echo _l('supplier_response_pending'); ?></span>',
                    'quoted': '<span class="label label-success"><?php echo _l('supplier_response_quoted'); ?></span>',
                    'declined': '<span class="label label-danger"><?php echo _l('supplier_response_declined'); ?></span>',
                    'no_response': '<span class="label label-warning"><?php echo _l('supplier_response_no_response'); ?></span>'
                };

                html += '<tr>';
                html += '<td><strong>' + supplier.supplier_name + '</strong><br><small>' + (supplier.supplier_email || '') + '</small></td>';
                html += '<td>' + (supplier.sent_date ? supplier.sent_date : '-') + '</td>';
                html += '<td>' + (statusLabels[supplier.response_status] || supplier.response_status) + '</td>';
                html += '<td>' + (supplier.response_received_date ? supplier.response_received_date : '-') + '</td>';
                html += '<td>';
                html += '<button class="btn btn-default btn-xs" onclick="editSupplier(' + supplier.id + ')"><i class="fa fa-edit"></i></button> ';
                html += '<button class="btn btn-danger btn-xs" onclick="deleteSupplier(' + supplier.id + ')"><i class="fa fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#rfq-suppliers-container').html(html);
    }

    // Load RFQ quotations
    function loadRFQQuotations() {
        // Directly call the endpoint to get quotations for this RFQ
        $.ajax({
            url: admin_url + 'equipments/rfq/get_quotations/' + rfqId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Quotations response:', response);
                if (response && response.success) {
                    renderRFQQuotations(response.quotations || []);
                } else {
                    console.error('Response success flag is false or missing');
                    renderRFQQuotations([]);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading quotations:', status, error);
                console.error('Response:', xhr.responseText);
                renderRFQQuotations([]);
            }
        });
    }

    // Render RFQ quotations
    function renderRFQQuotations(quotations) {
        var html = '';

        if (quotations.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo _l('no_quotations_to_compare'); ?></div>';
        } else {
            html += '<table class="table table-bordered table-hover">';
            html += '<thead><tr>';
            html += '<th><?php echo _l('quotation_number'); ?></th>';
            html += '<th><?php echo _l('supplier'); ?></th>';
            html += '<th><?php echo _l('quotation_date'); ?></th>';
            html += '<th><?php echo _l('total_amount'); ?></th>';
            html += '<th><?php echo _l('status'); ?></th>';
            html += '<th class="text-center"><?php echo _l('actions'); ?></th>';
            html += '</tr></thead><tbody>';

            quotations.forEach(function(quotation) {
                var statusClass = '';
                var statusLabel = '';

                switch (quotation.status) {
                    case 'draft':
                        statusClass = 'default';
                        statusLabel = '<?php echo _l('quotation_status_draft'); ?>';
                        break;
                    case 'submitted':
                        statusClass = 'info';
                        statusLabel = '<?php echo _l('quotation_status_submitted'); ?>';
                        break;
                    case 'under_review':
                        statusClass = 'warning';
                        statusLabel = '<?php echo _l('quotation_status_under_review'); ?>';
                        break;
                    case 'accepted':
                        statusClass = 'success';
                        statusLabel = '<?php echo _l('quotation_status_accepted'); ?>';
                        break;
                    case 'rejected':
                        statusClass = 'danger';
                        statusLabel = '<?php echo _l('quotation_status_rejected'); ?>';
                        break;
                    case 'expired':
                        statusClass = 'default';
                        statusLabel = '<?php echo _l('quotation_status_expired'); ?>';
                        break;
                }

                html += '<tr>';
                html += '<td><a href="' + admin_url + 'equipments/quotation/view/' + quotation.id + '">' + quotation.quotation_number + '</a></td>';
                html += '<td>' + (quotation.supplier_name || '-') + '</td>';
                html += '<td>' + quotation.quotation_date + '</td>';
                html += '<td class="tw-font-semibold">' + quotation.currency + ' ' + parseFloat(quotation.total_amount).toFixed(2) + '</td>';
                html += '<td><span class="label label-' + statusClass + '">' + statusLabel + '</span></td>';
                html += '<td class="text-center">';
                html += '<a href="' + admin_url + 'equipments/quotation/view/' + quotation.id + '" class="btn btn-default btn-xs"><i class="fa fa-eye"></i></a> ';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#rfq-quotations-container').html(html);
    }

    // Global functions for modals
    window.openItemModal = function() {
        $('#itemForm')[0].reset();
        $('#item_id').val('');
        $('#itemModal .modal-title').text('<?php echo _l('add_rfq_item'); ?>');
        $('#itemModal').modal('show');
        $('#itemModal').find('.selectpicker').selectpicker('refresh');
    };

    window.openSupplierModal = function() {
        $('#supplierForm')[0].reset();
        $('#supplier_rfq_id').val('');
        $('#supplierModal .modal-title').text('<?php echo _l('add_supplier_to_rfq'); ?>');
        $('#supplierModal').modal('show');
        $('#supplierModal').find('.selectpicker').selectpicker('refresh');
    };

    window.deleteItem = function(itemId) {
        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/rfq/delete_item/' + itemId, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert_float('success', result.message);
                    loadRFQItems();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    };

    window.deleteSupplier = function(supplierId) {
        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/rfq/delete_supplier/' + supplierId, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert_float('success', result.message);
                    loadRFQSuppliers();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    };

    window.editItem = function(itemId) {
        $.get(admin_url + 'equipments/rfq/get_items/' + rfqId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                var item = result.items.find(i => i.id == itemId);
                if (item) {
                    $('#item_id').val(item.id);
                    $('input[name="item_type"][value="' + item.item_type + '"]').prop('checked', true).trigger('change');
                    $('#equipment_id').val(item.equipment_id || '').selectpicker('refresh');
                    $('#equipment_description').val(item.equipment_description || '');
                    $('#operator_id').val(item.operator_id || '').selectpicker('refresh');
                    $('#operator_description').val(item.operator_description || '');
                    $('#quantity').val(item.quantity);
                    $('#unit').val(item.unit);
                    $('#standard_hours_per_day').val(item.standard_hours_per_day);
                    $('#days_per_month').val(item.days_per_month);
                    $('#expected_duration_months').val(item.expected_duration_months);
                    $('#item_notes').val(item.notes);

                    var titleText = item.equipment_description || item.operator_description || 'Item';
                    $('#itemModal .modal-title').text('<?php echo _l('edit'); ?> - ' + titleText);
                    $('#itemModal').modal('show');
                }
            }
        });
    };

    window.editSupplier = function(supplierId) {
        $.get(admin_url + 'equipments/rfq/get_suppliers/' + rfqId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                var supplier = result.suppliers.find(s => s.id == supplierId);
                if (supplier) {
                    $('#supplier_rfq_id').val(supplier.id);
                    $('#supplierForm select[name="supplier_id"]').val(supplier.supplier_id).selectpicker('refresh');
                    $('#supplierForm input[name="sent_date"]').val(supplier.sent_date || '');
                    $('#supplierForm input[name="response_received_date"]').val(supplier.response_received_date || '');
                    $('#supplierForm select[name="response_status"]').val(supplier.response_status).selectpicker('refresh');
                    $('#supplier_notes').val(supplier.notes);
                    $('#supplierModal .modal-title').text('<?php echo _l('edit'); ?> - ' + supplier.supplier_name);
                    $('#supplierModal').modal('show');
                }
            }
        });
    };

    // Handle item type changes
    $('input[name="item_type"]').on('change', function() {
        var itemType = $(this).val();

        if (itemType === 'equipment') {
            $('#equipment-fields').show();
            $('#operator-fields').hide();
            $('#equipment_description').prop('required', true);
            $('#operator_description').prop('required', false);
            $('.req-equipment').show();
            $('.req-operator').hide();
        } else if (itemType === 'operator') {
            $('#equipment-fields').hide();
            $('#operator-fields').show();
            $('#equipment_description').prop('required', false);
            $('#operator_description').prop('required', true);
            $('.req-equipment').hide();
            $('.req-operator').show();
        } else if (itemType === 'equipment_with_operator') {
            $('#equipment-fields').show();
            $('#operator-fields').show();
            $('#equipment_description').prop('required', true);
            $('#operator_description').prop('required', true);
            $('.req-equipment').show();
            $('.req-operator').show();
        }
    });

    // Auto-populate equipment description
    $('#equipment_id').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        if (selectedText && selectedText !== '') {
            $('#equipment_description').val(selectedText);
        }
    });

    // Auto-populate operator description
    $('#operator_id').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        if (selectedText && selectedText !== '') {
            $('#operator_description').val(selectedText);
        }
    });
});
</script>
