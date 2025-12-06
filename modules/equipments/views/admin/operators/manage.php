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
                            <?php if (has_permission('operators', '', 'create')) { ?>
                                <a href="<?php echo admin_url('equipments/operator'); ?>" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_new_operator'); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('operator_id'),
                                _l('operator_name'),
                                _l('operator_name_arabic'),
                                _l('nationality'),
                                _l('iqama_number'),
                                _l('license_number'),
                                _l('operator_type'),
                                _l('supplier'),
                                _l('status')
                            ];

                            render_datatable($table_data, 'operators');
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

    var OperatorsServerParams = {};

    var ajaxUrl = '<?php echo admin_url('equipments/table_operators'); ?>';
    console.log('DataTable AJAX URL:', ajaxUrl);
    console.log('Table selector:', '.table-operators');
    console.log('Table exists:', $('.table-operators').length);

    // Test the endpoint first
    $.get(ajaxUrl + '?draw=1&start=0&length=10', function(data) {
        console.log('AJAX Test Response:', data);
        console.log('Response type:', typeof data);
        if (typeof data === 'string') {
            console.error('Response is string, not object. Trying to parse...');
            try {
                var parsed = JSON.parse(data);
                console.log('Parsed successfully:', parsed);
            } catch(e) {
                console.error('JSON parse failed:', e);
                console.error('First 500 chars:', data.substring(0, 500));
            }
        }
    }).fail(function(xhr, status, error) {
        console.error('AJAX failed:', status, error);
        console.error('Response text:', xhr.responseText.substring(0, 500));
    });

    initDataTable('.table-operators', ajaxUrl, [8], [8], OperatorsServerParams, [0, 'desc']);

    // Handle status toggle
    $('body').on('change', '.onoffswitch input[type="checkbox"]', function() {
        var $switch = $(this);
        var checked = $switch.prop('checked');
        var id = $switch.data('id');
        var url = $switch.data('switch-url');

        $.post(url, {
            id: id,
            status: checked ? 1 : 0
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
            } else {
                alert_float('danger', response.message);
                // Revert the switch
                $switch.prop('checked', !checked);
            }
        });
    });
});
</script>
