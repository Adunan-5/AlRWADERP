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
                                    <?php echo _l('quotation'); ?> - <?php echo $quotation->quotation_number; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if ($quotation->status == 'accepted' && empty($quotation->sales_order_id)) { ?>
                                    <?php if (has_permission('equipment_sales_orders', '', 'create')) { ?>
                                        <a href="<?php echo admin_url('equipments/sales_orders/create_from_quotation/' . $quotation->id); ?>" class="btn btn-success" onclick="return confirm('<?php echo _l('convert_quotation_confirm'); ?>')">
                                            <i class="fa fa-exchange"></i> <?php echo _l('convert_to_sales_order'); ?>
                                        </a>
                                    <?php } ?>
                                <?php } ?>

                                <?php if (has_permission('equipment_quotations', '', 'edit')) { ?>
                                    <a href="<?php echo admin_url('equipments/quotations/edit/' . $quotation->id); ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i> <?php echo _l('edit'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('equipments/quotations'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr>

                        <!-- PO Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('quotation_details'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('quotation_number'); ?></strong></td>
                                            <td><?php echo $quotation->quotation_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('client'); ?></strong></td>
                                            <td><?php echo $client ? $client->company : '-'; ?></td>
                                        </tr>
                                        <?php if (isset($agreement)) { ?>
                                        <tr>
                                            <td><strong><?php echo _l('agreement'); ?></strong></td>
                                            <td>
                                                <a href="<?php echo admin_url('equipments/agreements/view/' . $agreement->id); ?>">
                                                    <?php echo $agreement->agreement_number; ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><strong><?php echo _l('quotation_date'); ?></strong></td>
                                            <td><?php echo _d($quotation->quotation_date); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('validity_date'); ?></strong></td>
                                            <td><?php echo $quotation->validity_date ? _d($quotation->validity_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('validity_date'); ?></strong></td>
                                            <td><?php echo $quotation->validity_date ? _d($quotation->validity_date) : '-'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('payment_terms'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('payment_terms'); ?></strong></td>
                                            <td><?php echo $quotation->payment_terms_days . ' ' . _l('days'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('currency'); ?></strong></td>
                                            <td><?php echo $quotation->currency; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('status'); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_labels = [
                                                    'draft' => '<span class="label label-default">' . _l('quotation_status_draft') . '</span>',
                                                    'sent' => '<span class="label label-info">' . _l('quotation_status_sent') . '</span>',
                                                    'confirmed' => '<span class="label label-primary">' . _l('quotation_status_confirmed') . '</span>',
                                                    'partially_received' => '<span class="label label-warning">' . _l('quotation_status_partially_received') . '</span>',
                                                    'completed' => '<span class="label label-success">' . _l('quotation_status_completed') . '</span>',
                                                    'cancelled' => '<span class="label label-danger">' . _l('quotation_status_cancelled') . '</span>',
                                                ];
                                                echo isset($status_labels[$quotation->status]) ? $status_labels[$quotation->status] : $quotation->status;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('created_at'); ?></strong></td>
                                            <td><?php echo _dt($quotation->created_at); ?></td>
                                        </tr>
                                        <?php if ($quotation->updated_at) { ?>
                                        <tr>
                                            <td><strong><?php echo _l('last_updated'); ?></strong></td>
                                            <td><?php echo _dt($quotation->updated_at); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <?php if ($quotation->terms_conditions) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('terms_and_conditions'); ?></h4>
                                <div class="well">
                                    <?php echo nl2br(e($quotation->terms_conditions)); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Notes -->
                        <?php if ($quotation->notes) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('notes'); ?></h4>
                                <div class="well">
                                    <?php echo nl2br(e($quotation->notes)); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Equipment Items Section -->
                        <div class="row tw-mt-6">
                            <div class="col-md-12">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <h4 class="tw-font-semibold tw-mb-0"><?php echo _l('equipment_items'); ?></h4>
                                    <?php if (has_permission('equipment_quotations', '', 'edit')) { ?>
                                        <button type="button" class="btn btn-primary" onclick="openItemModal()">
                                            <i class="fa fa-plus"></i> <?php echo _l('add_equipment_item'); ?>
                                        </button>
                                    <?php } ?>
                                </div>

                                <div id="po-items-container">
                                    <div class="text-center tw-py-8">
                                        <i class="fa fa-spinner fa-spin fa-2x tw-text-neutral-400"></i>
                                        <p class="tw-text-neutral-500 tw-mt-2">Loading items...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charges Section -->
                        <div class="row tw-mt-6">
                            <div class="col-md-12">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <h4 class="tw-font-semibold tw-mb-0"><?php echo _l('charges'); ?></h4>
                                    <?php if (has_permission('equipment_quotations', '', 'edit')) { ?>
                                        <button type="button" class="btn btn-primary" onclick="openChargeModal()">
                                            <i class="fa fa-plus"></i> <?php echo _l('add_charge'); ?>
                                        </button>
                                    <?php } ?>
                                </div>

                                <div id="po-charges-container">
                                    <div class="text-center tw-py-8">
                                        <i class="fa fa-spinner fa-spin fa-2x tw-text-neutral-400"></i>
                                        <p class="tw-text-neutral-500 tw-mt-2">Loading charges...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Add/Edit Equipment Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_equipment_item'); ?></h4>
            </div>
            <form id="itemForm">
                <div class="modal-body">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="quotation_id" value="<?php echo $quotation->id; ?>">
                    <input type="hidden" name="item_id" id="item_id">

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $equipments = $this->db->select('id, name, platenumber_code, equipmenttype')
                                ->get(db_prefix() . 'equipments')->result_array();
                            echo render_select('equipment_id', $equipments, ['id', ['name', 'platenumber_code']], _l('equipment'));
                            ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipment_description"><span class="text-danger">*</span> <?php echo _l('equipment_description'); ?></label>
                                <input type="text" class="form-control" name="equipment_description" id="equipment_description" required>
                            </div>
                        </div>
                    </div>

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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="days_per_month"><?php echo _l('days_per_month'); ?></label>
                                <input type="number" class="form-control" name="days_per_month" id="days_per_month" value="26" min="0">
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

<!-- Add/Edit Pricing Tier Modal -->
<div class="modal fade" id="tierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_pricing_tier'); ?></h4>
            </div>
            <form id="tierForm">
                <div class="modal-body">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="quotation_item_id" id="tier_quotation_item_id">
                    <input type="hidden" name="tier_id" id="tier_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from_month"><span class="text-danger">*</span> <?php echo _l('from_month'); ?></label>
                                <input type="number" class="form-control" name="from_month" id="from_month" required min="1" placeholder="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to_month"><?php echo _l('to_month'); ?></label>
                                <input type="number" class="form-control" name="to_month" id="to_month" min="1" placeholder="12">
                                <small class="text-muted">Leave empty for indefinite</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="monthly_rate"><span class="text-danger">*</span> <?php echo _l('monthly_rate'); ?> (<?php echo $quotation->currency; ?>)</label>
                        <input type="number" class="form-control" name="monthly_rate" id="monthly_rate" required step="0.01" min="0" placeholder="55000.00">
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

<!-- Add/Edit Charge Modal -->
<div class="modal fade" id="chargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_charge'); ?></h4>
            </div>
            <form id="chargeForm">
                <div class="modal-body">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="quotation_id" value="<?php echo $quotation->id; ?>">
                    <input type="hidden" name="charge_id" id="charge_id">
                    <input type="hidden" name="charge_name" id="charge_name_hidden">
                    <input type="hidden" name="status" value="pending">

                    <div class="form-group">
                        <label for="charge_type"><span class="text-danger">*</span> <?php echo _l('charge_type'); ?></label>
                        <select class="form-control selectpicker" name="charge_type" id="charge_type" required>
                            <option value="mobilization"><?php echo _l('mobilization'); ?></option>
                            <option value="demobilization"><?php echo _l('demobilization'); ?></option>
                            <option value="setup"><?php echo _l('setup'); ?></option>
                            <option value="teardown"><?php echo _l('teardown'); ?></option>
                            <option value="transportation"><?php echo _l('transportation'); ?></option>
                            <option value="other"><?php echo _l('other'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="charge_amount"><span class="text-danger">*</span> <?php echo _l('amount'); ?> (<?php echo $quotation->currency; ?>)</label>
                        <input type="number" class="form-control" name="amount" id="charge_amount" required step="0.01" min="0" placeholder="20000.00">
                    </div>

                    <div class="form-group">
                        <label for="charge_notes"><?php echo _l('notes'); ?></label>
                        <textarea class="form-control" name="notes" id="charge_notes" rows="2"></textarea>
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

    var quotationId = <?php echo $quotation->id; ?>;
    var currency = '<?php echo $quotation->currency; ?>';

    // Load PO items and charges on page load
    loadQuotationItems();
    loadQuotationCharges();

    // Auto-populate charge name from charge type
    $('#charge_type').on('change', function() {
        var chargeType = $(this).val();
        var chargeName = $(this).find('option:selected').text();
        $('#charge_name_hidden').val(chargeName);
    });

    // Item form submission
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var itemId = $('#item_id').val();
        var url = itemId ? admin_url + 'equipments/quotations/update_item/' + itemId : admin_url + 'equipments/quotations/add_item';

        $.post(url, formData, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('#itemModal').modal('hide');
                loadQuotationItems();
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    // Tier form submission
    $('#tierForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var tierId = $('#tier_id').val();
        var url = tierId ? admin_url + 'equipments/quotations/update_tier/' + tierId : admin_url + 'equipments/quotations/add_tier';

        $.post(url, formData, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('#tierModal').modal('hide');
                loadQuotationItems();
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    // Charge form submission
    $('#chargeForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var chargeId = $('#charge_id').val();
        var url = chargeId ? admin_url + 'equipments/quotations/update_charge/' + chargeId : admin_url + 'equipments/quotations/add_charge';

        $.post(url, formData, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('#chargeModal').modal('hide');
                loadQuotationCharges();
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    // Load PO items
    function loadQuotationItems() {
        $.get(admin_url + 'equipments/quotations/get_items/' + quotationId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                renderQuotationItems(result.items);
            }
        });
    }

    // Load PO charges
    function loadQuotationCharges() {
        $.get(admin_url + 'equipments/quotations/get_charges/' + quotationId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                renderQuotationCharges(result.charges);
            }
        });
    }

    // Render PO items
    function renderQuotationItems(items) {
        var html = '';

        if (items.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo _l('no_equipment_added'); ?></div>';
        } else {
            items.forEach(function(item) {
                html += '<div class="panel panel-default tw-mb-4">';
                html += '<div class="panel-heading">';
                html += '<div class="tw-flex tw-items-center tw-justify-between">';
                html += '<h5 class="tw-mb-0"><strong>' + item.equipment_description + '</strong></h5>';
                html += '<div class="btn-group">';
                html += '<button class="btn btn-default btn-xs" onclick="openTierModal(' + item.id + ')"><i class="fa fa-plus"></i> <?php echo _l('add_pricing_tier'); ?></button>';
                html += '<button class="btn btn-default btn-xs" onclick="editItem(' + item.id + ')"><i class="fa fa-edit"></i></button>';
                html += '<button class="btn btn-danger btn-xs" onclick="deleteItem(' + item.id + ')"><i class="fa fa-trash"></i></button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="panel-body">';

                // Item details
                html += '<div class="row tw-mb-3">';
                html += '<div class="col-md-4"><strong><?php echo _l('quantity'); ?>:</strong> ' + item.quantity + ' ' + (item.unit || 'unit') + '</div>';
                html += '<div class="col-md-4"><strong><?php echo _l('standard_hours_per_day'); ?>:</strong> ' + (item.standard_hours_per_day || '-') + '</div>';
                html += '<div class="col-md-4"><strong><?php echo _l('days_per_month'); ?>:</strong> ' + (item.days_per_month || '-') + '</div>';
                html += '</div>';

                // Pricing tiers
                html += '<h6 class="tw-font-semibold"><?php echo _l('pricing_tiers'); ?></h6>';
                if (item.tiers && item.tiers.length > 0) {
                    html += '<table class="table table-bordered table-sm">';
                    html += '<thead><tr>';
                    html += '<th><?php echo _l('from_month'); ?></th>';
                    html += '<th><?php echo _l('to_month'); ?></th>';
                    html += '<th><?php echo _l('monthly_rate'); ?></th>';
                    html += '<th><?php echo _l('options'); ?></th>';
                    html += '</tr></thead><tbody>';
                    item.tiers.forEach(function(tier) {
                        html += '<tr>';
                        html += '<td>Month ' + tier.from_month + '</td>';
                        html += '<td>' + (tier.to_month ? 'Month ' + tier.to_month : '∞ (Indefinite)') + '</td>';
                        html += '<td>' + formatMoney(tier.monthly_rate) + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-default btn-xs" onclick="editTier(' + tier.id + ', ' + item.id + ')"><i class="fa fa-edit"></i></button> ';
                        html += '<button class="btn btn-danger btn-xs" onclick="deleteTier(' + tier.id + ')"><i class="fa fa-trash"></i></button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<p class="text-muted"><i><?php echo _l('no_pricing_tiers'); ?></i></p>';
                }

                html += '</div></div>';
            });
        }

        $('#po-items-container').html(html);
    }

    // Render PO charges
    function renderQuotationCharges(charges) {
        var html = '';

        if (charges.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo _l('no_charges'); ?></div>';
        } else {
            html += '<table class="table table-bordered">';
            html += '<thead><tr>';
            html += '<th><?php echo _l('charge_type'); ?></th>';
            html += '<th><?php echo _l('amount'); ?></th>';
            html += '<th><?php echo _l('notes'); ?></th>';
            html += '<th><?php echo _l('options'); ?></th>';
            html += '</tr></thead><tbody>';

            charges.forEach(function(charge) {
                html += '<tr>';
                html += '<td><span class="label label-primary">' + charge.charge_name + '</span></td>';
                html += '<td><strong>' + formatMoney(charge.amount) + '</strong></td>';
                html += '<td>' + (charge.notes || '-') + '</td>';
                html += '<td>';
                html += '<button class="btn btn-default btn-xs" onclick="editCharge(' + charge.id + ')"><i class="fa fa-edit"></i></button> ';
                html += '<button class="btn btn-danger btn-xs" onclick="deleteCharge(' + charge.id + ')"><i class="fa fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#po-charges-container').html(html);
    }

    // Helper function to format money
    function formatMoney(amount) {
        return currency + ' ' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Global functions for modals
    window.openItemModal = function() {
        $('#itemForm')[0].reset();
        $('#item_id').val('');
        $('#itemModal .modal-title').text('<?php echo _l('add_equipment_item'); ?>');
        $('#itemModal').modal('show');
        $('#itemModal').find('.selectpicker').selectpicker('refresh');
    };

    window.openTierModal = function(quotationItemId) {
        $('#tierForm')[0].reset();
        $('#tier_id').val('');
        $('#tier_quotation_item_id').val(quotationItemId);
        $('#tierModal .modal-title').text('<?php echo _l('add_pricing_tier'); ?>');
        $('#tierModal').modal('show');
    };

    window.openChargeModal = function() {
        $('#chargeForm')[0].reset();
        $('#charge_id').val('');
        $('#chargeModal .modal-title').text('<?php echo _l('add_charge'); ?>');
        // Set default charge name
        $('#charge_name_hidden').val('<?php echo _l('mobilization'); ?>');
        $('#chargeModal').modal('show');
        $('#chargeModal').find('.selectpicker').selectpicker('refresh');
    };

    window.deleteItem = function(itemId) {
        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/quotations/delete_item/' + itemId, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert_float('success', result.message);
                    loadQuotationItems();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    };

    window.deleteTier = function(tierId) {
        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/quotations/delete_tier/' + tierId, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert_float('success', result.message);
                    loadQuotationItems();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    };

    window.deleteCharge = function(chargeId) {
        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/quotations/delete_charge/' + chargeId, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert_float('success', result.message);
                    loadQuotationCharges();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    };

    // Edit functions
    window.editItem = function(itemId) {
        // Get item data from the loaded items
        $.get(admin_url + 'equipments/quotations/get_items/' + quotationId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                var item = result.items.find(i => i.id == itemId);
                if (item) {
                    $('#item_id').val(item.id);
                    $('#equipment_id').val(item.equipment_id).selectpicker('refresh');
                    $('#equipment_description').val(item.equipment_description);
                    $('#quantity').val(item.quantity);
                    $('#unit').val(item.unit);
                    $('#standard_hours_per_day').val(item.standard_hours_per_day);
                    $('#days_per_month').val(item.days_per_month);
                    $('#item_notes').val(item.notes);
                    $('#itemModal .modal-title').text('<?php echo _l('edit'); ?> - ' + item.equipment_description);
                    $('#itemModal').modal('show');
                }
            }
        });
    };

    window.editTier = function(tierId, poItemId) {
        // Get tier data from the loaded items
        $.get(admin_url + 'equipments/quotations/get_items/' + quotationId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                var item = result.items.find(i => i.id == poItemId);
                if (item && item.tiers) {
                    var tier = item.tiers.find(t => t.id == tierId);
                    if (tier) {
                        $('#tier_id').val(tier.id);
                        $('#tier_quotation_item_id').val(tier.quotation_item_id);
                        $('#from_month').val(tier.from_month);
                        $('#to_month').val(tier.to_month || '');
                        $('#monthly_rate').val(tier.monthly_rate);
                        $('#tierModal .modal-title').text('<?php echo _l('edit'); ?> - <?php echo _l('pricing_tier'); ?>');
                        $('#tierModal').modal('show');
                    }
                }
            }
        });
    };

    window.editCharge = function(chargeId) {
        // Get charge data from the loaded charges
        $.get(admin_url + 'equipments/quotations/get_charges/' + quotationId, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                var charge = result.charges.find(c => c.id == chargeId);
                if (charge) {
                    $('#charge_id').val(charge.id);
                    $('#charge_type').val(charge.charge_type).selectpicker('refresh');
                    $('#charge_name_hidden').val(charge.charge_name);
                    $('#charge_amount').val(charge.amount);
                    $('#charge_notes').val(charge.notes);
                    $('#chargeModal .modal-title').text('<?php echo _l('edit'); ?> - ' + charge.charge_name);
                    $('#chargeModal').modal('show');
                }
            }
        });
    };

    // Auto-populate equipment description when equipment is selected
    $('#equipment_id').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        if (selectedText && selectedText !== '') {
            $('#equipment_description').val(selectedText);
        }
    });
});
</script>
