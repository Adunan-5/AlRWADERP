<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Quotation Header -->
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                                    <?php echo _l('supplier_quotation') . ' - ' . $quotation->quotation_number; ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('equipment_quotation', '', 'edit')) { ?>
                                    <a href="<?php echo admin_url('equipments/quotation/edit/' . $quotation->id); ?>" class="btn btn-default">
                                        <i class="fa-regular fa-pen-to-square"></i> <?php echo _l('edit'); ?>
                                    </a>
                                <?php } ?>

                                <?php if ($quotation->status == 'submitted' || $quotation->status == 'under_review') { ?>
                                    <?php if (has_permission('equipment_quotation', '', 'edit')) { ?>
                                        <button type="button" class="btn btn-success" id="accept-quotation-btn">
                                            <i class="fa fa-check"></i> <?php echo _l('accept_quotation'); ?>
                                        </button>
                                    <?php } ?>
                                <?php } ?>

                                <?php if ($quotation->status == 'accepted') { ?>
                                    <?php if (has_permission('equipment_purchase_orders', '', 'create')) { ?>
                                        <a href="<?php echo admin_url('equipments/quotation/convert_to_po/' . $quotation->id); ?>" class="btn btn-info">
                                            <i class="fa fa-exchange"></i> <?php echo _l('convert_to_po'); ?>
                                        </a>
                                    <?php } ?>
                                <?php } ?>

                                <a href="<?php echo admin_url('equipments/quotation'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_list'); ?>
                                </a>
                            </div>
                        </div>

                        <hr class="hr-panel-heading">

                        <!-- Quotation Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="tw-font-semibold" width="35%"><?php echo _l('quotation_number'); ?>:</td>
                                            <td><?php echo $quotation->quotation_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('quotation_date'); ?>:</td>
                                            <td><?php echo _d($quotation->quotation_date); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('valid_until_date'); ?>:</td>
                                            <td><?php echo $quotation->valid_until_date ? _d($quotation->valid_until_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('rfq_number'); ?>:</td>
                                            <td>
                                                <a href="<?php echo admin_url('equipments/rfq/view/' . $quotation->rfq_id); ?>">
                                                    <?php echo $quotation->rfq_number; ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('project_reference'); ?>:</td>
                                            <td><?php echo $quotation->project_reference ?: '-'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="tw-font-semibold" width="35%"><?php echo _l('supplier'); ?>:</td>
                                            <td><?php echo $quotation->supplier_name; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('email'); ?>:</td>
                                            <td><?php echo $quotation->supplier_email ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('status'); ?>:</td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($quotation->status) {
                                                    case 'draft':
                                                        $status_class = 'default';
                                                        break;
                                                    case 'submitted':
                                                        $status_class = 'info';
                                                        break;
                                                    case 'under_review':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'accepted':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                    case 'expired':
                                                        $status_class = 'default';
                                                        break;
                                                }
                                                ?>
                                                <span class="label label-<?php echo $status_class; ?>">
                                                    <?php echo _l('quotation_status_' . $quotation->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('created_by'); ?>:</td>
                                            <td><?php echo $quotation->created_by_name ?: '-'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if ($quotation->payment_terms || $quotation->delivery_terms || $quotation->notes) { ?>
                        <div class="row tw-mt-4">
                            <?php if ($quotation->payment_terms) { ?>
                            <div class="col-md-6">
                                <h5 class="tw-font-semibold"><?php echo _l('payment_terms'); ?></h5>
                                <p><?php echo nl2br($quotation->payment_terms); ?></p>
                            </div>
                            <?php } ?>
                            <?php if ($quotation->delivery_terms) { ?>
                            <div class="col-md-6">
                                <h5 class="tw-font-semibold"><?php echo _l('delivery_terms'); ?></h5>
                                <p><?php echo nl2br($quotation->delivery_terms); ?></p>
                            </div>
                            <?php } ?>
                            <?php if ($quotation->notes) { ?>
                            <div class="col-md-12 tw-mt-3">
                                <h5 class="tw-font-semibold"><?php echo _l('notes'); ?></h5>
                                <p><?php echo nl2br($quotation->notes); ?></p>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Quotation Items -->
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-mt-0 tw-font-bold"><?php echo _l('quotation_items'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('equipment_quotation', '', 'edit')) { ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#quotation-item-modal">
                                        <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" id="recalculate-totals-btn">
                                        <i class="fa fa-calculator"></i> <?php echo _l('recalculate_totals'); ?>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>

                        <hr class="hr-panel-heading">

                        <div class="table-responsive">
                            <table class="table table-striped" id="quotation-items-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('item_type'); ?></th>
                                        <th><?php echo _l('equipment_description'); ?></th>
                                        <th><?php echo _l('operator_description'); ?></th>
                                        <th class="text-right"><?php echo _l('quantity'); ?></th>
                                        <th><?php echo _l('unit'); ?></th>
                                        <th class="text-right"><?php echo _l('unit_rate'); ?></th>
                                        <th class="text-right"><?php echo _l('line_total'); ?></th>
                                        <th class="text-center"><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items loaded via AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr class="tw-font-semibold">
                                        <td colspan="6" class="text-right"><?php echo _l('subtotal'); ?>:</td>
                                        <td class="text-right" id="quotation-subtotal"><?php echo app_format_money($quotation->subtotal, $quotation->currency); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr class="tw-font-semibold">
                                        <td colspan="6" class="text-right">
                                            <?php echo _l('tax'); ?> (<?php echo number_format($quotation->tax_percentage, 2); ?>%):
                                        </td>
                                        <td class="text-right" id="quotation-tax"><?php echo app_format_money($quotation->tax_amount, $quotation->currency); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr class="tw-font-bold tw-text-lg">
                                        <td colspan="6" class="text-right"><?php echo _l('total_amount'); ?>:</td>
                                        <td class="text-right" id="quotation-total"><?php echo app_format_money($quotation->total_amount, $quotation->currency); ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="quotation-item-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo _l('add_quotation_item'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="quotation-item-form">
                    <input type="hidden" name="quotation_id" value="<?php echo $quotation->id; ?>">
                    <input type="hidden" name="item_id" id="item_id" value="">

                    <div class="form-group">
                        <label><?php echo _l('item_type'); ?> <span class="text-danger">*</span></label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="item_type" value="equipment" checked> <?php echo _l('equipment_only'); ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="item_type" value="operator"> <?php echo _l('operator_only'); ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="item_type" value="equipment_with_operator"> <?php echo _l('equipment_with_operator'); ?>
                            </label>
                        </div>
                    </div>

                    <div id="equipment-fields">
                        <?php echo render_textarea('equipment_description', _l('equipment_description'), '', ['rows' => 3, 'required' => true, 'class' => 'req-equipment']); ?>
                    </div>

                    <div id="operator-fields" style="display: none;">
                        <?php echo render_textarea('operator_description', _l('operator_description'), '', ['rows' => 3, 'class' => 'req-operator']); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?php echo render_input('quantity', _l('quantity'), '1', 'number', ['min' => 1, 'required' => true]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_input('unit', _l('unit'), 'unit', 'text', ['required' => true]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_input('unit_rate', _l('unit_rate'), '0.00', 'number', ['step' => '0.01', 'min' => 0, 'required' => true]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?php echo render_input('standard_hours_per_day', _l('standard_hours_per_day'), '', 'number', ['step' => '0.01', 'min' => 0]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_input('days_per_month', _l('days_per_month'), '', 'number', ['min' => 1]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_input('duration_months', _l('expected_duration_months'), '', 'number', ['min' => 1]); ?>
                        </div>
                    </div>

                    <?php echo render_textarea('notes', _l('notes'), '', ['rows' => 2]); ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="save-item-btn"><?php echo _l('save'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
var quotation_id = <?php echo $quotation->id; ?>;
var currency = '<?php echo $quotation->currency; ?>';

// CSRF token – this is the magic line that kills 419 forever
var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrf_hash       = '<?php echo $this->security->get_csrf_hash(); ?>';
// Helper to always include CSRF in any $.post
function getCsrfData() {
    return {
        [csrf_token_name]: csrf_hash
    };
}

$(function() {
    'use strict';

    // Load items on page load
    loadQuotationItems();

    // Item type change handler
    $('input[name="item_type"]').on('change', function() {
        var itemType = $(this).val();

        if (itemType === 'equipment') {
            $('#equipment-fields').show();
            $('#operator-fields').hide();
            $('#equipment_description').prop('required', true);
            $('#operator_description').prop('required', false);
        } else if (itemType === 'operator') {
            $('#equipment-fields').hide();
            $('#operator-fields').show();
            $('#equipment_description').prop('required', false);
            $('#operator_description').prop('required', true);
        } else if (itemType === 'equipment_with_operator') {
            $('#equipment-fields').show();
            $('#operator-fields').show();
            $('#equipment_description').prop('required', true);
            $('#operator_description').prop('required', true);
        }
    });

    // Reset modal on hide
    $('#quotation-item-modal').on('hidden.bs.modal', function() {
        $('#quotation-item-form')[0].reset();
        $('#item_id').val('');
        $('.modal-title').text('<?php echo _l('add_quotation_item'); ?>');
        $('input[name="item_type"][value="equipment"]').prop('checked', true).trigger('change');
    });

    // Save item (Add or Update)
    $('#save-item-btn').on('click', function(e) {
        e.preventDefault();
        var formData = $('#quotation-item-form').serialize();
        var itemId   = $('#item_id').val();
        var url      = itemId 
            ? admin_url + 'equipments/quotation/update_item/' + itemId 
            : admin_url + 'equipments/quotation/add_item';

        // Add CSRF token
        formData += '&' + csrf_token_name + '=' + csrf_hash;

        $.post(url, formData, function(response) {
            if (response.success) {
                alert_float('success', response.message);
                // Disable unsaved changes warning
                $(window).off("beforeunload");
                $('#quotation-item-modal').modal('hide');
                loadQuotationItems();
                location.reload();
            } else {
                alert_float('danger', response.message);
            }
        }, 'json');
    });

    // Recalculate totals
    $('#recalculate-totals-btn').on('click', function() {
        $.post(admin_url + 'equipments/quotation/recalculate_totals/' + quotation_id, getCsrfData(), function(response) {
            if (response.success) {
                alert_float('success', response.message);
                location.reload();
            }
        }, 'json');
    });

    // Accept quotation
    $('#accept-quotation-btn').on('click', function() {
        if (confirm('<?php echo _l('confirm_accept_quotation'); ?>')) {
            $.post(admin_url + 'equipments/quotation/accept/' + quotation_id, getCsrfData(), function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                }
            }, 'json');
        }
    });
});

// Load quotation items
function loadQuotationItems() {
    $.get(admin_url + 'equipments/quotation/get_items/' + quotation_id, function(response) {
        if (response.success) {
            renderQuotationItems(response.items);
        }
    }, 'json');
}

// Render items in table
function renderQuotationItems(items) {
    var tbody = $('#quotation-items-table tbody');
    tbody.empty();

    if (items.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center text-muted"><?php echo _l('no_quotation_items'); ?></td></tr>');
        return;
    }

    $.each(items, function(index, item) {
        var itemTypeLabel = '';
        switch (item.item_type) {
            case 'equipment':               itemTypeLabel = '<?php echo _l('equipment_only'); ?>'; break;
            case 'operator':                itemTypeLabel = '<?php echo _l('operator_only'); ?>'; break;
            case 'equipment_with_operator': itemTypeLabel = '<?php echo _l('equipment_with_operator'); ?>'; break;
        }

        var row = `<tr>
            <td>${itemTypeLabel}</td>
            <td>${item.equipment_description || '-'}</td>
            <td>${item.operator_description || '-'}</td>
            <td class="text-right">${item.quantity}</td>
            <td>${item.unit}</td>
            <td class="text-right">${formatMoney(item.unit_rate, currency)}</td>
            <td class="text-right tw-font-semibold">${formatMoney(item.line_total, currency)}</td>
            <td class="text-center">
                <?php if (has_permission('equipment_quotation', '', 'edit')) { ?>
                    <a href="#" class="btn btn-default btn-icon edit-item" data-item='${JSON.stringify(item)}'>
                        <i class="fa fa-edit"></i>
                    </a>
                <?php } ?>
                <?php if (has_permission('equipment_quotation', '', 'delete')) { ?>
                    <a href="#" class="btn btn-danger btn-icon delete-item" data-id="${item.id}">
                        <i class="fa fa-remove"></i>
                    </a>
                <?php } ?>
            </td>
        </tr>`;

        tbody.append(row);
    });

    // Edit button
    tbody.off('click', '.edit-item').on('click', '.edit-item', function(e) {
        e.preventDefault();
        var item = $(this).data('item');
        populateItemForm(item);
        $('#quotation-item-modal').modal('show');
    });

    // Delete button
    tbody.off('click', '.delete-item').on('click', '.delete-item', function(e) {
        e.preventDefault();
        var itemId = $(this).data('id');

        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.post(admin_url + 'equipments/quotation/delete_item/' + itemId, getCsrfData(), function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    loadQuotationItems();
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        }
    });
}

// Populate form when editing
function populateItemForm(item) {
    $('#item_id').val(item.id);
    $('.modal-title').text('<?php echo _l('edit_quotation_item'); ?>');
    $('input[name="item_type"][value="' + item.item_type + '"]').prop('checked', true).trigger('change');

    $('#equipment_description').val(item.equipment_description || '');
    $('#operator_description').val(item.operator_description || '');
    $('input[name="quantity"]').val(item.quantity);
    $('input[name="unit"]').val(item.unit);
    $('input[name="unit_rate"]').val(item.unit_rate);
    $('input[name="standard_hours_per_day"]').val(item.standard_hours_per_day || '');
    $('input[name="days_per_month"]').val(item.days_per_month || '');
    $('input[name="duration_months"]').val(item.duration_months || '');
    $('textarea[name="notes"]').val(item.notes || '');
}

// Format money
function formatMoney(amount, curr) {
    return curr + ' ' + parseFloat(amount || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
</script>
