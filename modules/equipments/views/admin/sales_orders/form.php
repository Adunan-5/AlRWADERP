<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                            <?php echo isset($order) ? _l('edit_sales_order') : _l('add_new_sales_order'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open(admin_url('equipments/sales_orders/' . (isset($order) ? 'edit/' . $order->id : 'add')), ['id' => 'sales-order-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Client -->
                                <?php
                                $selected_client = isset($order) ? $order->client_id : (isset($quotation) ? $quotation->client_id : '');
                                echo render_select('client_id', $clients, ['userid', ['company']], 'client', $selected_client, ['required' => true]);
                                ?>
                            </div>

                            <div class="col-md-6">
                                <!-- Project (Optional) -->
                                <div id="project-select-wrapper">
                                    <?php
                                    $selected_project = isset($order) ? $order->project_id : '';
                                    echo render_select('project_id', [], ['id', 'name'], 'project', $selected_project);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <!-- Order Date -->
                                <?php
                                $order_date = isset($order) ? $order->order_date : date('Y-m-d');
                                echo render_date_input('order_date', 'order_date', $order_date, ['required' => true]);
                                ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Expected Delivery Date -->
                                <?php
                                $delivery_date = isset($order) ? $order->expected_delivery_date : '';
                                echo render_date_input('expected_delivery_date', 'expected_delivery_date', $delivery_date);
                                ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Payment Terms -->
                                <?php
                                $payment_terms = isset($order) ? $order->payment_terms_days : 30;
                                echo render_input('payment_terms_days', 'payment_terms_days', $payment_terms, 'number', ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <!-- Currency -->
                                <?php
                                $selected_currency = isset($order) ? $order->currency : 'SAR';
                                $currencies = [
                                    ['value' => 'SAR', 'label' => 'SAR'],
                                    ['value' => 'USD', 'label' => 'USD'],
                                    ['value' => 'EUR', 'label' => 'EUR'],
                                ];
                                echo render_select('currency', $currencies, ['value', 'label'], 'currency', $selected_currency, ['required' => true]);
                                ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Tax Rate -->
                                <?php
                                $tax_rate = isset($order) ? $order->tax_rate : 15.00;
                                echo render_input('tax_rate', 'tax_rate', $tax_rate, 'number', ['step' => '0.01', 'required' => true]);
                                ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Status -->
                                <?php
                                $selected_status = isset($order) ? $order->status : 'draft';
                                $statuses = [
                                    ['value' => 'draft', 'label' => _l('so_status_draft')],
                                    ['value' => 'confirmed', 'label' => _l('so_status_confirmed')],
                                    ['value' => 'cancelled', 'label' => _l('so_status_cancelled')],
                                ];
                                echo render_select('status', $statuses, ['value', 'label'], 'status', $selected_status, ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <hr>
                        <h4 class="tw-font-bold"><?php echo _l('order_items'); ?></h4>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="order-items-table">
                                <thead>
                                    <tr>
                                        <th width="30%"><?php echo _l('equipment'); ?></th>
                                        <th width="15%"><?php echo _l('operator'); ?></th>
                                        <th width="10%"><?php echo _l('quantity'); ?></th>
                                        <th width="12%"><?php echo _l('rental_period_months'); ?></th>
                                        <th width="12%"><?php echo _l('unit_rate'); ?></th>
                                        <th width="15%"><?php echo _l('line_total'); ?></th>
                                        <th width="6%"><?php echo _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="order-items-body">
                                    <?php if (isset($order) && !empty($order->items)): ?>
                                        <?php foreach ($order->items as $index => $item): ?>
                                            <tr class="order-item-row">
                                                <td>
                                                    <?php echo render_select('equipment_id[]', $equipments, ['id', 'name'], '', $item->equipment_id, ['class' => 'selectpicker equipment-select', 'data-live-search' => 'true', 'required' => true], [], '', '', false); ?>
                                                </td>
                                                <td>
                                                    <select name="operator_id[]" class="form-control operator-select">
                                                        <option value=""><?php echo _l('none'); ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="quantity[]" class="form-control quantity-input" value="<?php echo $item->quantity; ?>" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="rental_period_months[]" class="form-control rental-period-input" value="<?php echo $item->rental_period_months; ?>" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="unit_rate[]" class="form-control unit-rate-input" value="<?php echo $item->unit_rate; ?>" step="0.01" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="line_total[]" class="form-control line-total-input" value="<?php echo $item->line_total; ?>" step="0.01" readonly>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-xs remove-item"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" class="btn btn-info btn-sm" id="add-item-btn">
                                                <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong><?php echo _l('subtotal'); ?>:</strong></td>
                                        <td><input type="number" id="subtotal" class="form-control" step="0.01" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong><?php echo _l('tax'); ?> (<span id="tax-rate-display">15</span>%):</strong></td>
                                        <td><input type="number" id="tax-amount" class="form-control" step="0.01" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong><?php echo _l('total'); ?>:</strong></td>
                                        <td><input type="number" id="total-amount" class="form-control" step="0.01" readonly></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $terms = isset($order) ? $order->terms_conditions : (isset($quotation) ? $quotation->terms_conditions : '');
                                echo render_textarea('terms_conditions', 'terms_conditions', $terms, ['rows' => 4]);
                                ?>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $notes = isset($order) ? $order->notes : (isset($quotation) ? $quotation->notes : '');
                                echo render_textarea('notes', 'notes', $notes, ['rows' => 3]);
                                ?>
                            </div>
                        </div>

                        <!-- Quotation ID (hidden) -->
                        <?php if (isset($quotation)): ?>
                            <input type="hidden" name="quotation_id" value="<?php echo $quotation->id; ?>">
                        <?php endif; ?>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <button type="submit" class="btn btn-primary pull-right">
                                    <?php echo _l('submit'); ?>
                                </button>
                                <a href="<?php echo admin_url('equipments/sales_orders'); ?>" class="btn btn-default pull-right" style="margin-right: 10px;">
                                    <?php echo _l('cancel'); ?>
                                </a>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    'use strict';

    var equipments = <?php echo json_encode($equipments); ?>;
    var itemRowTemplate = `
        <tr class="order-item-row">
            <td>
                <?php echo render_select('equipment_id[]', $equipments, ['id', 'name'], '', '', ['class' => 'selectpicker equipment-select', 'data-live-search' => 'true', 'required' => true], [], '', '', false); ?>
            </td>
            <td>
                <select name="operator_id[]" class="form-control operator-select">
                    <option value=""><?php echo _l('none'); ?></option>
                </select>
            </td>
            <td>
                <input type="number" name="quantity[]" class="form-control quantity-input" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="rental_period_months[]" class="form-control rental-period-input" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="unit_rate[]" class="form-control unit-rate-input" value="0" step="0.01" min="0" required>
            </td>
            <td>
                <input type="number" name="line_total[]" class="form-control line-total-input" value="0" step="0.01" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-xs remove-item"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;

    // Add item row
    $('#add-item-btn').on('click', function() {
        $('#order-items-body').append(itemRowTemplate);
        init_selectpicker();
        calculateTotals();
    });

    // Remove item row
    $('body').on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Calculate line total on input change
    $('body').on('input', '.quantity-input, .rental-period-input, .unit-rate-input', function() {
        var row = $(this).closest('tr');
        var quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        var rental_period = parseFloat(row.find('.rental-period-input').val()) || 0;
        var unit_rate = parseFloat(row.find('.unit-rate-input').val()) || 0;
        var line_total = quantity * rental_period * unit_rate;
        row.find('.line-total-input').val(line_total.toFixed(2));
        calculateTotals();
    });

    // Calculate totals
    function calculateTotals() {
        var subtotal = 0;
        $('.line-total-input').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });

        var tax_rate = parseFloat($('#tax_rate').val()) || 0;
        var tax_amount = (subtotal * tax_rate) / 100;
        var total = subtotal + tax_amount;

        $('#subtotal').val(subtotal.toFixed(2));
        $('#tax-amount').val(tax_amount.toFixed(2));
        $('#total-amount').val(total.toFixed(2));
        $('#tax-rate-display').text(tax_rate);
    }

    // Tax rate change
    $('#tax_rate').on('input', calculateTotals);

    // Load projects when client changes
    $('select[name="client_id"]').on('change', function() {
        var client_id = $(this).val();
        if (client_id) {
            $.get(admin_url + 'projects/get_client_projects/' + client_id, function(response) {
                var projects = JSON.parse(response);
                var options = '<option value=""><?php echo _l("none"); ?></option>';
                projects.forEach(function(project) {
                    options += '<option value="' + project.id + '">' + project.name + '</option>';
                });
                $('select[name="project_id"]').html(options).selectpicker('refresh');
            });
        }
    });

    // Initialize calculations on page load
    calculateTotals();

    // Trigger client change if editing
    <?php if (isset($order) && $order->client_id): ?>
        $('select[name="client_id"]').trigger('change');
    <?php endif; ?>
});
</script>
