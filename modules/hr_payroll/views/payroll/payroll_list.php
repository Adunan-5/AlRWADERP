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
                                <h4 class="no-margin">
                                    <i class="fa fa-list-alt"></i> <?php echo _l('hr_payroll_list'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('hrp_employee', '', 'create') || is_admin()) { ?>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#generatePayrollModal">
                                        <i class="fa fa-plus"></i> <?php echo _l('hr_generate_new_payroll'); ?>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <!-- Filters -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_status"><?php echo _l('status'); ?></label>
                                    <select class="form-control selectpicker" id="filter_status" name="filter_status" data-none-selected-text="<?php echo _l('all'); ?>">
                                        <option value=""><?php echo _l('all'); ?></option>
                                        <?php foreach ($statuses as $status_key => $status_label) { ?>
                                            <option value="<?php echo $status_key; ?>"><?php echo $status_label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_company"><?php echo _l('company'); ?></label>
                                    <select class="form-control selectpicker" id="filter_company" name="filter_company" data-none-selected-text="<?php echo _l('all'); ?>">
                                        <option value=""><?php echo _l('all'); ?></option>
                                        <?php foreach ($companies as $company_id => $company_name) { ?>
                                            <option value="<?php echo $company_id; ?>"><?php echo $company_name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_employee_type"><?php echo _l('employee_type'); ?></label>
                                    <select class="form-control selectpicker" id="filter_employee_type" name="filter_employee_type" data-none-selected-text="<?php echo _l('all'); ?>">
                                        <option value=""><?php echo _l('all'); ?></option>
                                        <?php foreach ($employee_types as $type) { ?>
                                            <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_month_from"><?php echo _l('date_from'); ?></label>
                                    <input type="month" class="form-control" id="filter_month_from" name="filter_month_from">
                                </div>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading">
                        <div class="clearfix"></div>

                        <table class="table table-payroll-list">
                            <thead>
                                <tr>
                                    <th><?php echo _l('hr_payroll_number'); ?></th>
                                    <th><?php echo _l('month'); ?></th>
                                    <th><?php echo _l('company'); ?></th>
                                    <th><?php echo _l('employee_type'); ?></th>
                                    <th><?php echo _l('total_employees'); ?></th>
                                    <th><?php echo _l('total_amount'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('created_by'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plus"></i> <?php echo _l('hr_generate_new_payroll'); ?>
                </h4>
            </div>
            <form id="generatePayrollForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payroll_month" class="control-label">
                                    <?php echo _l('month'); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="month" class="form-control" id="payroll_month" name="month" required>
                                <small class="text-muted"><?php echo _l('hr_payroll_select_month_help'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payroll_company" class="control-label">
                                    <?php echo _l('company'); ?>
                                </label>
                                <select class="form-control selectpicker" id="payroll_company" name="company_filter" data-none-selected-text="<?php echo _l('all'); ?>">
                                    <option value=""><?php echo _l('all'); ?></option>
                                    <?php foreach ($companies as $company_id => $company_name) { ?>
                                        <option value="<?php echo $company_id; ?>"><?php echo $company_name; ?></option>
                                    <?php } ?>
                                </select>
                                <small class="text-muted"><?php echo _l('hr_payroll_company_filter_help'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payroll_employee_type" class="control-label">
                                    <?php echo _l('employee_type'); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-control selectpicker" id="payroll_employee_type" name="ownemployee_type_id" required>
                                    <option value=""><?php echo _l('select'); ?></option>
                                    <?php foreach ($employee_types as $type) { ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                    <?php } ?>
                                </select>
                                <small class="text-muted"><?php echo _l('hr_payroll_employee_type_help'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <?php echo _l('hr_payroll_generation_notice'); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info" id="btnGeneratePayroll">
                        <i class="fa fa-refresh"></i> <?php echo _l('hr_generate_payroll'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
var csrfData = {
    token_name: '<?= $this->security->get_csrf_token_name(); ?>',
    hash: '<?= $this->security->get_csrf_hash(); ?>'
};

$(function() {
    'use strict';

    initDataTable('.table-payroll-list', admin_url + 'hr_payroll/get_payrolls_table', [0], [0], {
        status: '[name="filter_status"]',
        company_filter: '[name="filter_company"]',
        ownemployee_type_id: '[name="filter_employee_type"]',
        month_from: '[name="filter_month_from"]'
    });

    // Reload table when filters change
    $('[name="filter_status"], [name="filter_company"], [name="filter_employee_type"], [name="filter_month_from"]').on('change', function() {
        $('.table-payroll-list').DataTable().ajax.reload();
    });

    // Generate Payroll Form Submission
    $('#generatePayrollForm').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#btnGeneratePayroll');
        var originalText = $btn.html();
        var formData = $(this).serializeArray();

        // Add CSRF token
        if (typeof csrfData !== 'undefined') {
            formData.push({
                name: csrfData.token_name,
                value: csrfData.hash
            });
        }

        $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("generating"); ?>...').prop('disabled', true);

        $.ajax({
            url: admin_url + 'hr_payroll/generate_payroll',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#generatePayrollModal').modal('hide');
                    $('#generatePayrollForm')[0].reset();
                    $('.selectpicker').selectpicker('refresh');

                    // Redirect to manage employees page
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        $('.table-payroll-list').DataTable().ajax.reload();
                    }
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Delete Payroll
    window.delete_payroll = function(payroll_id) {
        if (confirm('<?php echo _l("hr_payroll_delete_confirmation"); ?>')) {
            $.ajax({
                url: admin_url + 'hr_payroll/delete_payroll',
                type: 'POST',
                data: { payroll_id: payroll_id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        $('.table-payroll-list').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
                }
            });
        }
    };

    // Initialize selectpicker
    $('.selectpicker').selectpicker();
});
</script>

</body>
</html>
