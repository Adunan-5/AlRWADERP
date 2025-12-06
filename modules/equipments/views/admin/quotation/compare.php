<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                                    <?php echo _l('compare_quotations'); ?>
                                </h4>
                                <p class="text-muted">
                                    <?php echo _l('rfq'); ?>: <strong><?php echo $rfq->rfq_number; ?></strong>
                                    <?php if ($rfq->project_reference) { ?>
                                        | <?php echo _l('project_reference'); ?>: <strong><?php echo $rfq->project_reference; ?></strong>
                                    <?php } ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('equipments/rfq/view/' . $rfq->id); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_rfq'); ?>
                                </a>
                            </div>
                        </div>

                        <hr class="hr-panel-heading">

                        <?php if (empty($quotations)) { ?>
                            <div class="alert alert-info">
                                <?php echo _l('no_quotations_to_compare'); ?>
                            </div>
                        <?php } else { ?>

                            <!-- Summary Comparison Table -->
                            <div class="table-responsive">
                                <h5 class="tw-font-semibold"><?php echo _l('quotation_summary_comparison'); ?></h5>
                                <table class="table table-bordered table-striped">
                                    <thead class="tw-bg-gray-100">
                                        <tr>
                                            <th width="20%"><?php echo _l('criteria'); ?></th>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <th class="text-center">
                                                    <?php echo $quotation['supplier_name']; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $quotation['quotation_number']; ?></small>
                                                </th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('quotation_date'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center"><?php echo _d($quotation['quotation_date']); ?></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('valid_until_date'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center">
                                                    <?php echo $quotation['valid_until_date'] ? _d($quotation['valid_until_date']) : '-'; ?>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('status'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center">
                                                    <?php
                                                    $status_class = '';
                                                    switch ($quotation['status']) {
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
                                                    }
                                                    ?>
                                                    <span class="label label-<?php echo $status_class; ?>">
                                                        <?php echo _l('quotation_status_' . $quotation['status']); ?>
                                                    </span>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <tr class="tw-bg-yellow-50">
                                            <td class="tw-font-bold"><?php echo _l('subtotal'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center tw-font-semibold">
                                                    <?php echo app_format_money($quotation['subtotal'], $quotation['currency']); ?>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('tax'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center">
                                                    <?php echo app_format_money($quotation['tax_amount'], $quotation['currency']); ?>
                                                    <small class="text-muted">(<?php echo number_format($quotation['tax_percentage'], 2); ?>%)</small>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <tr class="tw-bg-green-50">
                                            <td class="tw-font-bold tw-text-lg"><?php echo _l('total_amount'); ?></td>
                                            <?php
                                            $lowest_total = min(array_column($quotations, 'total_amount'));
                                            foreach ($quotations as $quotation) {
                                                $is_lowest = $quotation['total_amount'] == $lowest_total;
                                            ?>
                                                <td class="text-center tw-font-bold tw-text-lg <?php echo $is_lowest ? 'tw-text-green-600' : ''; ?>">
                                                    <?php echo app_format_money($quotation['total_amount'], $quotation['currency']); ?>
                                                    <?php if ($is_lowest) { ?>
                                                        <br><span class="label label-success"><?php echo _l('lowest_price'); ?></span>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <?php if (has_permission('equipment_quotation', '', 'edit')) { ?>
                                        <tr>
                                            <td class="tw-font-semibold"><?php echo _l('actions'); ?></td>
                                            <?php foreach ($quotations as $quotation) { ?>
                                                <td class="text-center">
                                                    <?php if ($quotation['status'] == 'accepted') { ?>
                                                        <span class="label label-success">
                                                            <i class="fa fa-check"></i> <?php echo _l('accepted'); ?>
                                                        </span>
                                                    <?php } elseif ($quotation['status'] == 'rejected') { ?>
                                                        <span class="label label-danger">
                                                            <i class="fa fa-times"></i> <?php echo _l('rejected'); ?>
                                                        </span>
                                                    <?php } else { ?>
                                                        <button type="button" class="btn btn-success btn-sm accept-quotation"
                                                                data-id="<?php echo $quotation['id']; ?>"
                                                                data-supplier="<?php echo $quotation['supplier_name']; ?>">
                                                            <i class="fa fa-check"></i> <?php echo _l('accept'); ?>
                                                        </button>
                                                    <?php } ?>
                                                    <br><br>
                                                    <a href="<?php echo admin_url('equipments/quotation/view/' . $quotation['id']); ?>"
                                                       class="btn btn-default btn-sm">
                                                        <i class="fa fa-eye"></i> <?php echo _l('view_details'); ?>
                                                    </a>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Detailed Item Comparison -->
                            <div class="tw-mt-8">
                                <h5 class="tw-font-semibold"><?php echo _l('detailed_item_comparison'); ?></h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="tw-bg-gray-100">
                                            <tr>
                                                <th width="15%"><?php echo _l('item_description'); ?></th>
                                                <th width="8%"><?php echo _l('item_type'); ?></th>
                                                <th width="8%" class="text-center"><?php echo _l('qty'); ?></th>
                                                <?php foreach ($quotations as $quotation) { ?>
                                                    <th class="text-center" colspan="2">
                                                        <?php echo $quotation['supplier_name']; ?>
                                                    </th>
                                                <?php } ?>
                                            </tr>
                                            <tr>
                                                <th colspan="3"></th>
                                                <?php foreach ($quotations as $quotation) { ?>
                                                    <th class="text-right"><?php echo _l('unit_rate'); ?></th>
                                                    <th class="text-right"><?php echo _l('total'); ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Get all unique items from all quotations
                                            $all_items = [];
                                            foreach ($quotations as $quotation) {
                                                foreach ($quotation['items'] as $item) {
                                                    $key = $item['equipment_description'] . '|' . $item['operator_description'];
                                                    if (!isset($all_items[$key])) {
                                                        $all_items[$key] = [
                                                            'equipment_description' => $item['equipment_description'],
                                                            'operator_description' => $item['operator_description'],
                                                            'item_type' => $item['item_type'],
                                                            'quantity' => $item['quantity'],
                                                            'unit' => $item['unit'],
                                                        ];
                                                    }
                                                }
                                            }

                                            foreach ($all_items as $key => $base_item) {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($base_item['equipment_description']) { ?>
                                                            <strong><?php echo _l('equipment'); ?>:</strong> <?php echo $base_item['equipment_description']; ?>
                                                            <br>
                                                        <?php } ?>
                                                        <?php if ($base_item['operator_description']) { ?>
                                                            <strong><?php echo _l('operator'); ?>:</strong> <?php echo $base_item['operator_description']; ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo _l('item_type_' . $base_item['item_type']); ?></td>
                                                    <td class="text-center"><?php echo $base_item['quantity'] . ' ' . $base_item['unit']; ?></td>

                                                    <?php foreach ($quotations as $quotation) {
                                                        $matching_item = null;
                                                        foreach ($quotation['items'] as $item) {
                                                            $item_key = $item['equipment_description'] . '|' . $item['operator_description'];
                                                            if ($item_key === $key) {
                                                                $matching_item = $item;
                                                                break;
                                                            }
                                                        }

                                                        if ($matching_item) {
                                                    ?>
                                                            <td class="text-right"><?php echo app_format_money($matching_item['unit_rate'], $quotation['currency']); ?></td>
                                                            <td class="text-right tw-font-semibold"><?php echo app_format_money($matching_item['line_total'], $quotation['currency']); ?></td>
                                                        <?php } else { ?>
                                                            <td class="text-center text-muted" colspan="2">-</td>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
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

<script>
$(function() {
    'use strict';

    // Accept quotation handler
    $('.accept-quotation').on('click', function() {
        var quotationId = $(this).data('id');
        var supplierName = $(this).data('supplier');

        if (confirm('<?php echo _l('confirm_accept_quotation_from'); ?> ' + supplierName + '?\n\n<?php echo _l('other_quotations_will_be_rejected'); ?>')) {
            $.post(admin_url + 'equipments/quotation/accept/' + quotationId, function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        }
    });
});
</script>
