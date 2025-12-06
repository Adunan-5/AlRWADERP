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
                                    <i class="fa fa-file-text-o"></i> <?php echo _l('equipment_quotations'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_quotations', '', 'create')) { ?>
                                    <a href="<?php echo admin_url('equipments/quotations/add'); ?>" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('add_new_quotation'); ?>
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
                                _l('quotation_number'),
                                _l('client'),
                                _l('quotation_date'),
                                _l('validity_date'),
                                _l('payment_terms'),
                                _l('status'),
                                _l('options')
                            ];

                            render_datatable($table_data, 'quotations');
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

    // Initialize Quotations DataTable
    initDataTable('.table-quotations', '<?php echo admin_url('equipments/quotations/table'); ?>', [6], [6], [], [2, 'desc']);
});

function deleteQuotation(id) {
    if (confirm('<?php echo _l('confirm_delete'); ?>')) {
        $.post(admin_url + 'equipments/quotations/delete/' + id, function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert_float('success', result.message);
                $('.table-quotations').DataTable().ajax.reload();
            } else {
                alert_float('danger', result.message);
            }
        });
    }
}
</script>
