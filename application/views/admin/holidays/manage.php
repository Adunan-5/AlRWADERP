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
                                    <i class="fa fa-calendar"></i> <?php echo _l('holidays'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-info" onclick="add_holiday()">
                                    <i class="fa fa-plus"></i> <?php echo _l('new_holiday'); ?>
                                </button>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <!-- DataTable -->
                        <div class="clearfix"></div>
                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                _l('holiday_label'),
                                _l('date'),
                                _l('description'),
                                _l('options')
                            ];

                            render_datatable($table_data, 'holidays');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><i class="fa fa-plus"></i> <?php echo _l('add_new', _l('holiday')); ?></span>
                    <span class="edit-title hide"><i class="fa fa-edit"></i> <?php echo _l('edit', _l('holiday')); ?></span>
                </h4>
            </div>
            <form id="holidayForm">
                <input type="hidden" name="id" id="holiday_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="label" class="control-label">
                            <?php echo _l('holiday_label'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="label" name="label" required>
                    </div>

                    <div class="form-group">
                        <label for="holiday_date" class="control-label">
                            <?php echo _l('date'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="holiday_date" name="holiday_date" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="control-label">
                            <?php echo _l('description'); ?>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
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

    // Initialize DataTable
    initDataTable('.table-holidays', admin_url + 'holidays/table', [3], [3], [], [1, 'asc']);

    // Form submission
    $('#holidayForm').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');

        // Prevent double submission
        if ($submitBtn.prop('disabled')) {
            return false;
        }

        $submitBtn.prop('disabled', true);

        var formData = $(this).serializeArray();
        formData.push({
            name: csrfData.token_name,
            value: csrfData.hash
        });

        $.ajax({
            url: admin_url + 'holidays/save',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#holidayModal').modal('hide');
                    $('#holidayForm')[0].reset();
                    // Reload table instead of full page reload
                    $('.table-holidays').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message);
                    $submitBtn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
                $submitBtn.prop('disabled', false);
            }
        });
    });
});

// Add new holiday
function add_holiday() {
    $('#holiday_id').val('');
    $('#holidayForm')[0].reset();
    $('#holidayForm button[type="submit"]').prop('disabled', false);
    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
    $('#holidayModal').modal('show');
}

// Edit holiday
function edit_holiday(id) {
    $.get(admin_url + 'holidays/get/' + id, function(holiday) {
        $('#holiday_id').val(holiday.id);
        $('#label').val(holiday.label);
        $('#holiday_date').val(holiday.holiday_date);
        $('#description').val(holiday.description);
        $('#holidayForm button[type="submit"]').prop('disabled', false);
        $('.add-title').addClass('hide');
        $('.edit-title').removeClass('hide');
        $('#holidayModal').modal('show');
    }, 'json');
}
</script>

</body>
</html>
