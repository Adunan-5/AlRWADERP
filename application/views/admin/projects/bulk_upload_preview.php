<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold">
                            <i class="fa fa-upload"></i> <?= _l('bulk_upload_project_members_preview') ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <!-- Import Summary -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center" style="background-color: #f8f9fa; padding: 20px;">
                                        <h3 class="bold text-primary" style="margin: 0;"><?= $total_rows; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;"><?= _l('total_rows') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center" style="background-color: #d4edda; padding: 20px;">
                                        <h3 class="bold text-success" style="margin: 0;"><?= $total_valid; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;"><?= _l('valid_rows') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center" style="background-color: #f8d7da; padding: 20px;">
                                        <h3 class="bold text-danger" style="margin: 0;"><?= $total_errors; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;"><?= _l('rows_with_errors') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center" style="background-color: #fff3cd; padding: 20px;">
                                        <h3 class="bold text-warning" style="margin: 0;"><?= $total_valid; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;"><?= _l('rows_to_import') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Errors Section -->
                        <?php if (!empty($validation_errors)) { ?>
                            <div class="alert alert-danger mtop20">
                                <h4><i class="fa fa-exclamation-triangle"></i> <?= _l('validation_errors_found') ?></h4>
                                <p><?= _l('validation_errors_description') ?></p>

                                <div class="table-responsive mtop15">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr class="bg-danger text-white">
                                                <th width="80"><?= _l('row_number') ?></th>
                                                <th><?= _l('employee_name') ?></th>
                                                <th><?= _l('iqama_number') ?></th>
                                                <th><?= _l('errors') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($validation_errors as $error) { ?>
                                                <tr>
                                                    <td class="text-center"><strong><?= $error['row']; ?></strong></td>
                                                    <td><?= isset($error['data']['employee_name']) ? e($error['data']['employee_name']) : ''; ?></td>
                                                    <td><?= isset($error['data']['iqama_number']) ? e($error['data']['iqama_number']) : ''; ?></td>
                                                    <td>
                                                        <ul class="list-unstyled" style="margin: 0;">
                                                            <?php foreach ($error['errors'] as $err) { ?>
                                                                <li><i class="fa fa-times text-danger"></i> <?= e($err); ?></li>
                                                            <?php } ?>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- Preview Data Table -->
                        <div class="mtop20">
                            <h4 class="bold"><?= _l('preview_data') ?></h4>
                            <p class="text-muted"><?= _l('preview_data_description') ?></p>

                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered table-hover table-striped" id="preview-table" style="min-width: 100%; white-space: nowrap;">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th width="50">
                                                <input type="checkbox" id="select-all-rows" <?= $total_valid == 0 ? 'disabled' : ''; ?>>
                                            </th>
                                            <th width="60"><?= _l('row') ?></th>
                                            <th><?= _l('employee_name') ?></th>
                                            <th><?= _l('iqama_number') ?></th>
                                            <th><?= _l('skills') ?></th>
                                            <th><?= _l('badge') ?></th>
                                            <th><?= _l('start_date') ?></th>
                                            <th><?= _l('end_date') ?></th>
                                            <th><?= _l('equipment') ?></th>
                                            <th><?= _l('regular_rate') ?></th>
                                            <th><?= _l('overtime_rate') ?></th>
                                            <th><?= _l('rate_type') ?></th>
                                            <th width="100">Record Status</th>
                                            <th width="100"><?= _l('status') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($simulation_data as $row) { ?>
                                            <?php
                                            $hasError = isset($validation_errors[$row['row_number']]);
                                            $isValid = !$hasError;
                                            ?>
                                            <tr class="<?= $hasError ? 'danger' : ''; ?>">
                                                <td class="text-center">
                                                    <?php if ($isValid) { ?>
                                                        <input type="checkbox" class="row-checkbox" value="<?= $row['row_number']; ?>" checked>
                                                    <?php } else { ?>
                                                        <input type="checkbox" disabled>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center"><strong><?= $row['row_number']; ?></strong></td>
                                                <td><?= e($row['employee_name']); ?></td>
                                                <td><?= e($row['iqama_number']); ?></td>
                                                <td><?= e($row['skills']); ?></td>
                                                <td><?= e($row['badge']); ?></td>
                                                <td><?= e($row['start_date']); ?></td>
                                                <td><?= e($row['end_date']); ?></td>
                                                <td><?= e($row['equipment']); ?></td>
                                                <td><?= e($row['regular_rate']); ?></td>
                                                <td><?= e($row['overtime_rate']); ?></td>
                                                <td><?= ucfirst(e($row['rate_type'])); ?></td>
                                                <td class="text-center"><?= isset($row['record_status']) ? $row['record_status'] : '<span class="label label-success">New</span>'; ?></td>
                                                <td class="text-center"><?= $row['status']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mtop30 text-right">
                            <a href="<?= admin_url('projects/view/' . $project_id . '?group=project_members'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> <?= _l('cancel') ?>
                            </a>

                            <?php if ($total_valid > 0) { ?>
                                <div style="display: inline-block;">
                                    <?php if ($total_errors > 0) { ?>
                                        <button type="button" class="btn btn-warning" id="import-valid-only-btn" data-skip-errors="1">
                                            <i class="fa fa-check"></i> <?= _l('import_valid_rows_only') ?> (<?= $total_valid; ?>)
                                        </button>
                                    <?php } ?>

                                    <?php if ($total_errors == 0) { ?>
                                        <button type="button" class="btn btn-success" id="confirm-import-btn" data-skip-errors="0">
                                            <i class="fa fa-check"></i> <?= _l('confirm_and_import') ?> (<?= $total_rows; ?> <?= _l('rows') ?>)
                                        </button>
                                    <?php } ?>
                                </div>

                                <!-- Hidden form for file resubmission -->
                                <?= form_open_multipart(admin_url('projects/bulk_upload_members_process/' . $project_id), ['id' => 'process-import-form', 'style' => 'display: none;']) ?>
                                    <input type="file" name="file_csv" id="file_csv_reupload">
                                    <input type="hidden" name="skip_errors" id="skip_errors_input" value="0">
                                    <input type="hidden" name="confirm_import" value="1">
                                <?= form_close(); ?>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(document).ready(function() {
        // Select All checkbox handler
        $('#select-all-rows').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateImportButtonText();
        });

        // Individual checkbox handler
        $('.row-checkbox').on('change', function() {
            updateImportButtonText();

            // Update select-all checkbox state
            var totalCheckboxes = $('.row-checkbox').length;
            var checkedCheckboxes = $('.row-checkbox:checked').length;
            $('#select-all-rows').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        // Update button text to show selected count
        function updateImportButtonText() {
            var selectedCount = $('.row-checkbox:checked').length;
            var btnText = selectedCount > 0
                ? '<?= _l('import_valid_rows_only') ?> (' + selectedCount + ')'
                : '<?= _l('no_rows_selected') ?>';

            $('#import-valid-only-btn, #confirm-import-btn').html('<i class="fa fa-check"></i> ' + btnText);
            $('#import-valid-only-btn, #confirm-import-btn').prop('disabled', selectedCount === 0);
        }

        // Initial button text update
        updateImportButtonText();

        // Confirm import button handler (use delegated event to ensure it works after DataTables init)
        $(document).on('click', '#confirm-import-btn, #import-valid-only-btn', function(e) {
            e.preventDefault();

            console.log('Import button clicked'); // Debug log

            // Collect selected row numbers
            var selectedRows = [];
            $('.row-checkbox:checked').each(function() {
                selectedRows.push($(this).val());
            });

            if (selectedRows.length === 0) {
                Swal.fire('<?= _l('error') ?>', '<?= _l('please_select_rows_to_import') ?>', 'warning');
                return;
            }

            var skipErrors = $(this).data('skip-errors');
            var totalRows = selectedRows.length;

            Swal.fire({
                title: '<?= _l('confirm_import') ?>',
                text: '<?= _l('confirm_import_message') ?>'.replace('{count}', totalRows),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<?= _l('yes_import') ?>',
                cancelButtonText: '<?= _l('cancel') ?>',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: '<?= _l('processing') ?>...',
                        text: '<?= _l('please_wait') ?>',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Process import using stored temp file
                    $.ajax({
                        url: '<?= admin_url('projects/bulk_upload_members_process/' . $project_id); ?>',
                        type: 'POST',
                        data: {
                            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
                            'skip_errors': skipErrors,
                            'confirm_import': '1',
                            'selected_rows': selectedRows
                        },
                        success: function(response) {
                            Swal.close();
                            window.location.href = '<?= admin_url('projects/view/' . $project_id . '?group=project_members'); ?>';
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: '<?= _l('error') ?>',
                                text: '<?= _l('something_went_wrong') ?>'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
