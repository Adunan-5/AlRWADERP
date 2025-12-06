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
                                    <i class="fa fa-briefcase"></i> <?php echo _l('equipment_sales_orders'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_sales_orders', '', 'create')) { ?>
                                    <a href="<?php echo admin_url('equipments/sales_orders/add'); ?>" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('add_new_sales_order'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <!-- DataTable -->
                        <div class="clearfix"></div>
                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('order_number'),
                                _l('client'),
                                _l('order_date'),
                                _l('expected_delivery_date'),
                                _l('total_amount'),
                                _l('status'),
                                _l('fulfillment_status'),
                                _l('options')
                            ];

                            render_datatable($table_data, 'sales-orders');
                            ?>
                        </div>
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

    // Initialize Sales Orders DataTable
    initDataTable('.table-sales-orders', '<?php echo admin_url('equipments/sales_orders/table'); ?>', [7], [7], [], [2, 'desc']);

    // Delete sales order handler
    $('body').on('click', '.delete-sales-order', function(e) {
        e.preventDefault();
        var orderId = $(this).data('id');

        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.ajax({
                url: admin_url + 'equipments/sales_orders/delete/' + orderId,
                type: 'POST',
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert_float('success', result.message || '<?php echo _l('deleted', _l('sales_order')); ?>');
                        $('.table-sales-orders').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', result.message || '<?php echo _l('problem_deleting', _l('sales_order')); ?>');
                    }
                },
                error: function() {
                    alert_float('danger', '<?php echo _l('problem_deleting', _l('sales_order')); ?>');
                }
            });
        }
    });
});
</script>
