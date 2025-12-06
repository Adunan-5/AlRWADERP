<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons tw-mb-2 sm:tw-mb-4">
                            <?php if (has_permission('equipment_rfq', '', 'create')) { ?>
                                <a href="<?php echo admin_url('equipments/rfq/add'); ?>" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_rfq'); ?>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="clearfix"></div>

                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('rfq_number'),
                                _l('rfq_date'),
                                _l('required_by_date'),
                                _l('project_reference'),
                                _l('status'),
                                _l('rfq_items'),
                                _l('rfq_suppliers'),
                                _l('created_by'),
                                _l('options'),
                            ];
                            render_datatable($table_data, 'rfq');
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

    initDataTable('.table-rfq', admin_url + 'equipments/rfq/table', [], [], [], [0, 'desc']);
});
</script>
