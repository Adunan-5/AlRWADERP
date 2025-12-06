<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<input type="hidden" name="project_id" id="project_id" value="<?= e($project->id); ?>">

<div class="panel_s">
  <div class="panel-body panel-table-full">

    <?php if (staff_can('create', 'projects')) { ?>
      <button class="btn btn-primary pull-right" style="margin-bottom:20px; margin-left: 10px;" id="addMemberBtn">
        <i class="fa fa-plus"></i> <?= _l('add_project_member'); ?>
      </button>
      <button class="btn btn-success pull-right" style="margin-bottom:20px;" id="bulkUploadBtn">
        <i class="fa fa-upload"></i> <?= _l('bulk_upload_members'); ?>
      </button>
      <a href="<?= admin_url('projects/download_members_template/' . $project->id); ?>" class="btn btn-default pull-right" style="margin-bottom:20px; margin-right: 10px;">
        <i class="fa fa-download"></i> <?= _l('download_import_template'); ?>
      </a>
    <?php } ?>

    <?php
    $table_data = [
        _l('staff_member'),
        _l('phone_number'),
        _l('email'),
        _l('badge'),
        _l('regular_rate'),
        _l('overtime_rate'),
        _l('rate_type'),
        _l('options'),
    ];
    render_datatable($table_data, 'project-members');
    ?>

  </div>
</div>
<div class="modal fade" id="assign_member_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <?= form_open(admin_url('projects/assign_member'), ['id' => 'assign-member-form']) ?>
            <div class="modal-header">
                <h4 class="modal-title"><?= _l('add_project_member') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body row">

                <!-- Staff -->
                <div class="form-group col-md-6">
                    <?php
                    $staff_list = $staff; // passed from controller
                    echo render_select('staff_id', $staff_list, ['staffid','name'], 'staff_member');
                    ?>
                </div>

                <!-- Skills -->
                <div class="form-group col-md-6">
                    <?php
                    $skills = get_all_profession_types();
                    echo render_select('skills[]', $skills, ['id','name'], 'skills', null);
                    ?>
                </div>

                <!-- Badge -->
                <div class="form-group col-md-6">
                    <?= render_input('badge', 'badge') ?>
                </div>

                <!-- Start Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('start_date', 'start_date') ?>
                </div>

                <!-- End Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('end_date', 'end_date') ?>
                </div>

                <!-- Equipment -->
                <div class="form-group col-md-6">
                    <?php
                    $equipments = get_all_equipments();
                    echo render_select('equipment', $equipments, ['id','name'], 'equipment');
                    ?>
                </div>

                <!-- Regular Rate -->
                <div class="form-group col-md-6">
                    <?= render_input('regular_rate', 'regular_rate', '', 'number', ['step' => '0.01']) ?>
                </div>

                <!-- Overtime Rate -->
                <div class="form-group col-md-6">
                    <?= render_input('overtime_rate', 'overtime_rate', '', 'number', ['step' => '0.01']) ?>
                </div>

                <!-- Rate Type -->
                <div class="form-group col-md-12">
                    <label for="rate_type"><?= _l('rate_type') ?></label><br/>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" name="rate_type" id="hourly" value="hourly" checked>
                        <label for="hourly"><?= _l('hourly') ?></label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" name="rate_type" id="monthly" value="monthly">
                        <label for="monthly"><?= _l('monthly') ?></label>
                    </div>
                </div>

                <?= form_hidden('project_id', $project->id) ?>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
            </div>

        <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div class="modal fade" id="bulk_upload_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form enctype="multipart/form-data" id="bulk-upload-form">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" id="csrf_token_input">
                <input type="hidden" name="project_id" value="<?= e($project->id); ?>">
                <div class="modal-header">
                    <h4 class="modal-title"><?= _l('bulk_upload_project_members') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><?= _l('bulk_upload_instructions') ?></strong>
                        <ul class="mtop10">
                            <li><?= _l('bulk_upload_instruction_1') ?></li>
                            <li><?= _l('bulk_upload_instruction_2') ?></li>
                            <li><?= _l('bulk_upload_instruction_3') ?></li>
                            <li><?= _l('bulk_upload_instruction_4') ?></li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label for="file_csv"><?= _l('choose_csv_file') ?> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file_csv" id="file_csv" accept=".csv,.xls,.xlsx" required>
                        <small class="text-muted"><?= _l('supported_file_types') ?>: CSV, Excel (.xls, .xlsx)</small>
                        <small class="text-muted tw-block mtop5"><strong>Maximum file size:</strong> <?= ini_get('upload_max_filesize'); ?> (If your file is larger, split it into smaller files)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="bulk-upload-submit-btn">
                        <i class="fa fa-upload"></i> <?= _l('upload_and_preview') ?>
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(function(){
          console.log('Project Members Table Loaded');
          initDataTable('.table-project-members', admin_url + 'projects/members_table/<?= $project->id; ?>', undefined, undefined, undefined, [0,'desc']);
        });

        // Track if we're in edit mode to prevent auto-populate from overwriting data
        var isEditMode = false;

        // Auto-populate staff data from PAY tab (current pay record)
        // ONLY when adding a new member, NOT when editing
        $(document).on('change', 'select[name="staff_id"]', function() {
            var staffId = $(this).val();
            if (!staffId) return;

            // Skip auto-populate if we're editing an existing member
            if (isEditMode) {
                return;
            }

            $.ajax({
                url: admin_url + 'projects/get_staff_details/' + staffId,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        // Populate skills (profession type)
                        var skills = res.professiontype_id ? res.professiontype_id.split(',') : [];
                        $('select[name="skills[]"]').val(skills).change();

                        // Populate badge (use employee code or leave empty)
                        // Badge is typically set manually, so we leave it empty

                        // Populate regular rate (from current PAY record or fallback)
                        $('input[name="regular_rate"]').val(res.basics || '');

                        // Populate overtime rate (from current PAY record or fallback)
                        $('input[name="overtime_rate"]').val(res.ot || '');

                        // Populate rate type (hourly/monthly from current PAY record)
                        if (res.payout_type) {
                            $('input[name="rate_type"][value="' + res.payout_type + '"]').prop('checked', true);
                        }

                        // Note: start_date, end_date, and equipment are NOT auto-populated
                        // as they are project-specific assignments
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching staff details:', error);
                }
            });
        });

        // $(document).on('click', '#assign-member-form button[type="submit"]', function() {
        // $(document).on('submit', '#assign-member-form', function() {
        //     $('#assign_member_modal').modal('hide');
        //     blockArea($('#wrapper'));
    
        //     // simulate unblock after ajax
        //     setTimeout(function(){
        //         unBlockArea($('#wrapper'));
        //     }, 2000);
        // });

        $(document).on('submit', '#assign-member-form', function(e) {
            e.preventDefault(); // prevent normal submit

            var staffId = $('select[name="staff_id"]').val();
    
            // Check if staff already exists in table
            var exists = false;
            $('.table-project-members').find('tr').each(function() {
                if ($(this).data('staffid') == staffId) {
                    exists = true;
                }
            });

            if (exists) {
                $('#assign_member_modal').modal('hide');
                alert_float('warning', 'This employee is already assigned to the project');
                return;
            }

            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();
            $('#assign_member_modal').modal('hide');
            blockArea($('#wrapper'));

            $.post(url, data, function(response) {
                unBlockArea($('#wrapper'));
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (response.success) {
                    $('#assign_member_modal').modal('hide');
                    alert_float('success', response.message || 'Member saved successfully');

                    // Reload the DataTable
                    $('.table-project-members').DataTable().ajax.reload(null, false);
                } else {
                    alert_float('danger', response.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '#addMemberBtn', function() {
          const modal = $('#assign_member_modal');

          // Set flag to allow auto-populate from PAY data
          isEditMode = false;

          // Reset all fields
          modal.find('form')[0].reset();
          modal.find('select').val('').change();
          modal.find('input[name="rate_type"][value="hourly"]').prop('checked', true);

          // Remove edit_id if exists (ensure we're adding, not editing)
          modal.find('input[name="edit_id"]').remove();

          // Reset form action
          modal.find('form').attr('action', '<?= admin_url('projects/assign_member') ?>');

          modal.modal('show');
      });

      $(document).on('click', '.edit-member', function() {
        const modal = $('#assign_member_modal');
        const data = $(this).data();

        console.log(data); // debug

        // Set flag to PREVENT auto-populate from PAY data (we're editing existing data)
        isEditMode = true;

        // Prefill form
        modal.find('form').attr('action', '<?= admin_url('projects/assign_member') ?>');
        modal.find('select[name="staff_id"]').val(data.staffid).change();
        modal.find('select[name="skills[]"]').val((data.skills || '').toString().split(',')).change();
        modal.find('input[name="badge"]').val(data.badge);
        modal.find('input[name="start_date"]').val(data.start_date);
        modal.find('input[name="end_date"]').val(data.end_date);
        modal.find('select[name="equipment"]').val(data.equipment_id).change();
        modal.find('input[name="regular_rate"]').val(data.regular_rate);
        modal.find('input[name="overtime_rate"]').val(data.overtime_rate);
        modal.find('input[name="rate_type"][value="' + data.rate_type + '"]').prop('checked', true);

        // Add hidden field for edit
        modal.find('input[name="edit_id"]').remove();
        modal.find('form').append('<input type="hidden" name="edit_id" value="' + data.id + '">');

        modal.modal('show');
    });

    $(document).on('click', '.remove-member', function() {
        console.log('Remove member clicked');
        const id = $(this).data('id');
        const row = $(this).closest('tr');

        Swal.fire({
            title: '<?= _l('confirm_action_prompt') ?>',
            text: '<?= _l('project_member_delete_confirm') ?>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<?= _l('delete') ?>',
            cancelButtonText: '<?= _l('cancel') ?>',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= admin_url('projects/delete_member') ?>', { id: id })
                    .done(function(response) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        Swal.fire({
                            icon: 'success',
                            title: '<?= _l('project_member_deleted') ?>',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    })
                    .fail(function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '<?= _l('something_went_wrong') ?>'
                        });
                    });
            }
        });
    });


    $('select[name="skills[]"] option[value=""]').remove();
    $('.selectpicker').selectpicker('refresh');

    // Bulk Upload Button Handler
    $(document).on('click', '#bulkUploadBtn', function() {
        // Get fresh CSRF token from page if available
        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfToken = $('input[name="' + csrfName + '"]').first().val();

        if (csrfToken) {
            $('#csrf_token_input').val(csrfToken);
        }

        $('#bulk_upload_modal').modal('show');
    });

    // Bulk Upload Form Submission via AJAX
    $(document).on('submit', '#bulk-upload-form', function(e) {
        e.preventDefault();

        var fileInput = $('#file_csv');
        if (!fileInput.val()) {
            alert_float('warning', '<?= _l('please_select_file') ?>');
            return false;
        }

        var formData = new FormData(this);

        var btn = $('#bulk-upload-submit-btn');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?= _l('processing') ?>...');

        $.ajax({
            url: admin_url + 'projects/bulk_upload_members_preview/<?= $project->id; ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal and redirect to preview page
                    $('#bulk_upload_modal').modal('hide');
                    window.location.href = response.redirect_url;
                } else {
                    Swal.fire('Error', response.message || 'Upload failed', 'error');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to upload file';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    if (xhr.status === 403) {
                        errorMsg = 'Session expired. Please refresh the page and try again.';
                    } else {
                        errorMsg = xhr.statusText || errorMsg;
                    }
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    });
</script>
