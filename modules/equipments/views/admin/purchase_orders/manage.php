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
                                    <i class="fa fa-file-text-o"></i> <?php echo _l('equipment_purchase_orders'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_purchase_orders', '', 'create')) { ?>
                                    <a href="<?php echo admin_url('equipments/purchase_orders/add'); ?>" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('add_new_purchase_order'); ?>
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
                                _l('po_number'),
                                _l('supplier'),
                                _l('po_date'),
                                _l('delivery_date'),
                                _l('payment_terms'),
                                _l('status'),
                                _l('options')
                            ];

                            render_datatable($table_data, 'purchase-orders');
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

    // Initialize Purchase Orders DataTable
    initDataTable('.table-purchase-orders', '<?php echo admin_url('equipments/purchase_orders/table'); ?>', [6], [6], [], [2, 'desc']);

    // Delete purchase order handler
    $('body').on('click', '.delete-po', function(e) {
        e.preventDefault();
        var poId = $(this).data('id');

        if (confirm('<?php echo _l('confirm_delete'); ?>')) {
            $.ajax({
                url: admin_url + 'equipments/purchase_orders/delete/' + poId,
                type: 'POST',
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert_float('success', result.message || '<?php echo _l('deleted', _l('purchase_order')); ?>');
                        $('.table-purchase-orders').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', result.message || '<?php echo _l('problem_deleting', _l('purchase_order')); ?>');
                    }
                },
                error: function() {
                    alert_float('danger', '<?php echo _l('problem_deleting', _l('purchase_order')); ?>');
                }
            });
        }
    });
});
</script>
