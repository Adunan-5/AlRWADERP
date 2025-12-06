<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <div class="_buttons tw-mb-2 sm:tw-mb-4">
                            <?php if (has_permission('equipments', '', 'create')) { ?>
                                <a href="<?php echo admin_url('equipments/add'); ?>" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_new_equipment'); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('equipment_id'),
                                _l('equipment_name'),
                                _l('equipment_platenumber_code'),
                                _l('equipment_type'),
                                _l('equipment_ownership_type'),
                                _l('equipment_phone'),
                                _l('equipment_email'),
                                _l('equipment_available_from_date')
                            ];

                            render_datatable($table_data, 'equipments');
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
$(function(){
    'use strict';

    var EquipmentsServerParams = {};
    initDataTable('.table-equipments', '<?php echo admin_url('equipments/table_equipments'); ?>', [7], [7], EquipmentsServerParams, [0, 'desc']);
});
</script>
