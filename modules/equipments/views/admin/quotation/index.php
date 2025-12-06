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
                                    <i class="fa fa-file-text-o"></i> <?php echo _l('supplier_quotations'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_quotation', '', 'create')) { ?>
                                    <a href="<?php echo admin_url('equipments/quotation/add'); ?>" class="btn btn-primary">
                                        <i class="fa-regular fa-plus tw-mr-1"></i>
                                        <?php echo _l('add_quotation'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <div class="clearfix"></div>

                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('quotation_number'),
                                _l('quotation_date'),
                                _l('rfq_number'),
                                _l('supplier'),
                                _l('total_amount'),
                                _l('status'),
                                _l('valid_until_date'),
                                _l('options'),
                            ];
                            render_datatable($table_data, 'quotation');
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

    initDataTable('.table-quotation', admin_url + 'equipments/quotation/table', [], [], [], [0, 'desc']);
});
</script>
