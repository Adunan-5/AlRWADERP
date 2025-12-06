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
                            <?php if (has_permission('equipment_agreements', '', 'create')) { ?>
                                <a href="<?php echo admin_url('equipments/agreements/add'); ?>" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_new_agreement'); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>

                        <!-- Filter tabs -->
                        <ul class="nav nav-tabs tw-mb-4" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#all_agreements" aria-controls="all_agreements" role="tab" data-toggle="tab">
                                    <?php echo _l('all_agreements'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#supplier_agreements" aria-controls="supplier_agreements" role="tab" data-toggle="tab">
                                    <?php echo _l('supplier_agreements'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#client_agreements" aria-controls="client_agreements" role="tab" data-toggle="tab">
                                    <?php echo _l('client_agreements'); ?>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content">
                            <!-- All Agreements -->
                            <div role="tabpanel" class="tab-pane active" id="all_agreements">
                                <div class="panel-table-full">
                                    <?php
                                    $table_data = [
                                        _l('agreement_number'),
                                        _l('agreement_type'),
                                        _l('party'),
                                        _l('start_date'),
                                        _l('end_date'),
                                        _l('payment_terms'),
                                        _l('status'),
                                        _l('options')
                                    ];

                                    render_datatable($table_data, 'agreements-all');
                                    ?>
                                </div>
                            </div>

                            <!-- Supplier Agreements -->
                            <div role="tabpanel" class="tab-pane" id="supplier_agreements">
                                <div class="panel-table-full">
                                    <?php render_datatable($table_data, 'agreements-supplier'); ?>
                                </div>
                            </div>

                            <!-- Client Agreements -->
                            <div role="tabpanel" class="tab-pane" id="client_agreements">
                                <div class="panel-table-full">
                                    <?php render_datatable($table_data, 'agreements-client'); ?>
                                </div>
                            </div>
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

    // Initialize DataTables for all three tabs
    var AgreementsServerParams = {
        'type': 'all'
    };

    var SupplierAgreementsServerParams = {
        'type': 'supplier'
    };

    var ClientAgreementsServerParams = {
        'type': 'client'
    };

    // All agreements table
    initDataTable('.table-agreements-all', '<?php echo admin_url('equipments/agreements/table'); ?>', [7], [], AgreementsServerParams, [0, 'desc']);

    // Supplier agreements table
    initDataTable('.table-agreements-supplier', '<?php echo admin_url('equipments/agreements/table'); ?>', [7], [], SupplierAgreementsServerParams, [0, 'desc']);

    // Client agreements table
    initDataTable('.table-agreements-client', '<?php echo admin_url('equipments/agreements/table'); ?>', [7], [], ClientAgreementsServerParams, [0, 'desc']);

    // Delete agreement handler
    $('body').on('click', '.delete-agreement', function(e) {
        e.preventDefault();
        var agreementId = $(this).data('id');

        if (confirm('<?php echo _l('confirm_delete_agreement'); ?>')) {
            $.ajax({
                url: admin_url + 'equipments/agreements/delete/' + agreementId,
                type: 'POST',
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert_float('success', result.message || '<?php echo _l('deleted', _l('agreement')); ?>');
                        $('.table-agreements-all').DataTable().ajax.reload();
                        $('.table-agreements-supplier').DataTable().ajax.reload();
                        $('.table-agreements-client').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', result.message || '<?php echo _l('problem_deleting', _l('agreement')); ?>');
                    }
                },
                error: function() {
                    alert_float('danger', '<?php echo _l('problem_deleting', _l('agreement')); ?>');
                }
            });
        }
    });
});
</script>
