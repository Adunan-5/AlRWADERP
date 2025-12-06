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
                                    <?php echo _l('sales_order'); ?>: <?php echo $order->order_number; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_sales_orders', '', 'edit')): ?>
                                    <a href="<?php echo admin_url('equipments/sales_orders/edit/' . $order->id); ?>" class="btn btn-primary">
                                        <i class="fa fa-edit"></i> <?php echo _l('edit'); ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo admin_url('equipments/sales_orders'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <!-- Order Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-font-bold"><?php echo _l('order_information'); ?></h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%"><?php echo _l('order_number'); ?></th>
                                        <td><?php echo $order->order_number; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo _l('client'); ?></th>
                                        <td>
                                            <a href="<?php echo admin_url('clients/client/' . $order->client_id); ?>">
                                                <?php echo $order->client_company; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php if ($order->project_name): ?>
                                    <tr>
                                        <th><?php echo _l('project'); ?></th>
                                        <td>
                                            <a href="<?php echo admin_url('projects/view/' . $order->project_id); ?>">
                                                <?php echo $order->project_name; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th><?php echo _l('order_date'); ?></th>
                                        <td><?php echo _d($order->order_date); ?></td>
                                    </tr>
                                    <?php if ($order->expected_delivery_date): ?>
                                    <tr>
                                        <th><?php echo _l('expected_delivery_date'); ?></th>
                                        <td><?php echo _d($order->expected_delivery_date); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th><?php echo _l('payment_terms'); ?></th>
                                        <td><?php echo $order->payment_terms_days . ' ' . _l('days'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo _l('currency'); ?></th>
                                        <td><?php echo $order->currency; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="tw-font-bold"><?php echo _l('status_information'); ?></h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%"><?php echo _l('status'); ?></th>
                                        <td>
                                            <?php
                                            $status_labels = [
                                                'draft'                => '<span class="label label-default">' . _l('so_status_draft') . '</span>',
                                                'confirmed'            => '<span class="label label-primary">' . _l('so_status_confirmed') . '</span>',
                                                'partially_fulfilled'  => '<span class="label label-warning">' . _l('so_status_partially_fulfilled') . '</span>',
                                                'fulfilled'            => '<span class="label label-success">' . _l('so_status_fulfilled') . '</span>',
                                                'cancelled'            => '<span class="label label-danger">' . _l('so_status_cancelled') . '</span>',
                                            ];
                                            echo isset($status_labels[$order->status]) ? $status_labels[$order->status] : $order->status;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo _l('fulfillment_status'); ?></th>
                                        <td>
                                            <?php
                                            $fulfillment_labels = [
                                                'pending'      => '<span class="label label-default">' . _l('fulfillment_pending') . '</span>',
                                                'in_progress'  => '<span class="label label-info">' . _l('fulfillment_in_progress') . '</span>',
                                                'completed'    => '<span class="label label-success">' . _l('fulfillment_completed') . '</span>',
                                                'cancelled'    => '<span class="label label-danger">' . _l('fulfillment_cancelled') . '</span>',
                                            ];
                                            echo isset($fulfillment_labels[$order->fulfillment_status]) ? $fulfillment_labels[$order->fulfillment_status] : $order->fulfillment_status;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo _l('created_by'); ?></th>
                                        <td><?php echo $order->firstname . ' ' . $order->lastname; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo _l('created_at'); ?></th>
                                        <td><?php echo _dt($order->created_at); ?></td>
                                    </tr>
                                    <?php if ($order->updated_at): ?>
                                    <tr>
                                        <th><?php echo _l('updated_at'); ?></th>
                                        <td><?php echo _dt($order->updated_at); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order->quotation_id): ?>
                                    <tr>
                                        <th><?php echo _l('quotation'); ?></th>
                                        <td>
                                            <a href="<?php echo admin_url('equipments/quotations/view/' . $order->quotation_id); ?>">
                                                <?php echo _l('view_quotation'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <hr>
                        <h4 class="tw-font-bold"><?php echo _l('order_items'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('equipment'); ?></th>
                                        <th><?php echo _l('operator'); ?></th>
                                        <th class="text-center"><?php echo _l('quantity'); ?></th>
                                        <th class="text-center"><?php echo _l('rental_period_months'); ?></th>
                                        <th class="text-right"><?php echo _l('unit_rate'); ?></th>
                                        <th class="text-right"><?php echo _l('line_total'); ?></th>
                                        <th class="text-center"><?php echo _l('fulfillment'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($order->items)): ?>
                                        <?php foreach ($order->items as $item): ?>
                                            <tr>
                                                <td><?php echo $item->equipment_name; ?></td>
                                                <td><?php echo $item->operator_name ?: '-'; ?></td>
                                                <td class="text-center"><?php echo $item->quantity; ?></td>
                                                <td class="text-center"><?php echo $item->rental_period_months; ?></td>
                                                <td class="text-right"><?php echo app_format_money($item->unit_rate, $order->currency); ?></td>
                                                <td class="text-right"><?php echo app_format_money($item->line_total, $order->currency); ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    $fulfillment_labels = [
                                                        'pending'             => '<span class="label label-default">' . _l('pending') . '</span>',
                                                        'partially_fulfilled' => '<span class="label label-warning">' . _l('partially_fulfilled') . '</span>',
                                                        'fulfilled'           => '<span class="label label-success">' . _l('fulfilled') . '</span>',
                                                    ];
                                                    echo isset($fulfillment_labels[$item->fulfillment_status]) ? $fulfillment_labels[$item->fulfillment_status] : $item->fulfillment_status;
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?php echo _l('no_items_found'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong><?php echo _l('subtotal'); ?>:</strong></td>
                                        <td class="text-right"><strong><?php echo app_format_money($order->subtotal, $order->currency); ?></strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong><?php echo _l('tax'); ?> (<?php echo $order->tax_rate; ?>%):</strong></td>
                                        <td class="text-right"><strong><?php echo app_format_money($order->tax_amount, $order->currency); ?></strong></td>
                                        <td></td>
                                    </tr>
                                    <tr class="tw-bg-neutral-100">
                                        <td colspan="5" class="text-right"><strong><?php echo _l('total'); ?>:</strong></td>
                                        <td class="text-right"><strong><?php echo app_format_money($order->total_amount, $order->currency); ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Fulfillments -->
                        <?php if (!empty($order->fulfillments)): ?>
                        <hr>
                        <h4 class="tw-font-bold"><?php echo _l('order_fulfillments'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('equipment'); ?></th>
                                        <th><?php echo _l('fulfilled_date'); ?></th>
                                        <th><?php echo _l('quantity'); ?></th>
                                        <th><?php echo _l('mobilization'); ?></th>
                                        <th><?php echo _l('agreement'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order->fulfillments as $fulfillment): ?>
                                        <tr>
                                            <td><?php echo $fulfillment->equipment_name; ?></td>
                                            <td><?php echo _d($fulfillment->fulfilled_date); ?></td>
                                            <td><?php echo $fulfillment->fulfilled_quantity; ?></td>
                                            <td>
                                                <?php if ($fulfillment->mobilization_id): ?>
                                                    <a href="<?php echo admin_url('equipments/mobilization/view/' . $fulfillment->mobilization_id); ?>">
                                                        <?php echo _l('view_mobilization'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fulfillment->agreement_id): ?>
                                                    <a href="<?php echo admin_url('equipments/agreements/view/' . $fulfillment->agreement_id); ?>">
                                                        <?php echo _l('view_agreement'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <!-- Terms & Conditions -->
                        <?php if ($order->terms_conditions): ?>
                        <hr>
                        <h4 class="tw-font-bold"><?php echo _l('terms_conditions'); ?></h4>
                        <div class="tw-p-4 tw-bg-neutral-50 tw-rounded">
                            <?php echo nl2br($order->terms_conditions); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Notes -->
                        <?php if ($order->notes): ?>
                        <hr>
                        <h4 class="tw-font-bold"><?php echo _l('notes'); ?></h4>
                        <div class="tw-p-4 tw-bg-neutral-50 tw-rounded">
                            <?php echo nl2br($order->notes); ?>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
