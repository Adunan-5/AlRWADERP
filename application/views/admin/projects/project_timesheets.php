<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ---- CARD STYLING ---- */
.card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px 20px; /* spacing inside the card */
    transition: all 0.3s ease-in-out;
}
/* .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
} */
.card h5 {
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}
.card .btn {
    margin: 0 5px;
}

/* ---- LAYOUT SPACING ---- */
#uploadExcelForm {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
    margin-bottom: 25px; /* spacing below upload form */
}
#addTimesheetBtn {
    margin-top: 10px;
    margin-bottom: 25px;
}
#timesheetCards .col-md-12 {
    margin-bottom: 20px; /* space between cards */
}

/* ---- BUTTON ALIGNMENT ---- */
.card .d-flex {
    gap: 10px;
}

/* ---- RESPONSIVE TWEAK ---- */
@media (max-width: 767px) {
    #uploadExcelForm {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
<a href="<?= admin_url('timesheet/download_import_template'); ?>" 
   class="btn btn-success" 
   style="float:right; margin-left: 5px;">
   <i class="far fa-file-excel"></i> Download Timesheet Template
</a>
<div class="row mb-4">
    <div class="col-md-12">
        <form id="uploadExcelForm" enctype="multipart/form-data">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="project_id" id="upload_project_id" value="<?= e($project->id); ?>">

            <div>
                <label class="control-label">Month</label>
                <input type="month" name="month" id="upload_month" class="form-control" required>
            </div>
            <div>
                <label class="control-label">Timesheet Excel</label>
                <input type="file" name="timesheet_excel" id="timesheet_excel_file" class="form-control" accept=".xls,.xlsx" required>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" id="previewExcelBtn">
                    <i class="fa fa-eye"></i> Preview & Import
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <button id="addTimesheetBtn" class="btn btn-success">Add Timesheet</button>
    </div>
</div>

<!-- Modal for New Timesheet -->
<div class="modal fade" id="newTimesheetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New Timesheet</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="newTimesheetForm">
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="<?= e($project->id); ?>">
                    <div class="form-group">
                        <label>Month and Year</label>
                        <input type="month" name="month" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Timesheet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Import Modal -->
<div class="modal fade" id="previewImportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1400px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-eye"></i> Preview Timesheet Import
                </h4>
                <p class="text-muted" style="margin: 5px 0 0 0;">
                    <span id="preview-month-display"></span> |
                    <span id="preview-total-rows">0</span> employees found
                </p>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Instructions:</strong> Review the data below and select which employees to import. Only employees with status "Ready to import" or "Will update" can be imported.
                </div>

                <!-- Selection Controls -->
                <div class="tw-mb-4" style="margin-bottom: 15px;">
                    <label style="margin-right: 15px;">
                        <input type="checkbox" id="selectAllImportable">
                        <strong>Select all importable rows</strong>
                    </label>
                    <span id="selectedCount" class="badge badge-primary" style="font-size: 13px; padding: 5px 10px;">
                        0 selected
                    </span>
                </div>

                <!-- Employees in Excel -->
                <h4 style="margin-top: 0; margin-bottom: 10px;">
                    <i class="fa fa-file-excel-o text-success"></i> Employees in Excel Sheet
                </h4>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
                    <table class="table table-striped table-bordered" id="previewTable">
                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllCheckbox" disabled>
                                </th>
                                <th width="50">#</th>
                                <th>Employee Name</th>
                                <th>IQAMA</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Rows will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <!-- Missing Employees Section -->
                <div id="missingEmployeesSection" style="display: none;">
                    <h4 style="margin-bottom: 10px;">
                        <i class="fa fa-exclamation-triangle text-warning"></i> Missing from Excel
                        <small class="text-muted">(Assigned to project but not in Excel file)</small>
                    </h4>
                    <div class="alert alert-warning" style="margin-bottom: 10px;">
                        <i class="fa fa-info-circle"></i> The following employees are assigned to this project but are <strong>not found</strong> in the uploaded Excel file.
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped table-bordered">
                            <thead style="position: sticky; top: 0; background: #fcf8e3; z-index: 10;">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Employee Name</th>
                                    <th>IQAMA</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="missingEmployeesTableBody">
                                <!-- Missing employees will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <input type="hidden" id="preview_file_name">
                <input type="hidden" id="preview_project_id">
                <input type="hidden" id="preview_month">
            </div>
            <div class="modal-footer">
                <div style="flex: 1; text-align: left;">
                    <span class="text-muted" style="font-size: 11px;">
                        <strong>Legend:</strong> &nbsp;
                        <i class="fa fa-check-circle text-success"></i> Ready to import &nbsp;
                        <i class="fa fa-info-circle text-info"></i> Will update &nbsp;
                        <i class="fa fa-exclamation-triangle text-warning"></i> Not assigned &nbsp;
                        <i class="fa fa-times-circle text-danger"></i> Not found &nbsp;
                        <i class="fa fa-minus-circle" style="color: #777;"></i> Missing from Excel
                    </span>
                </div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="proceedImportBtn" disabled>
                    <i class="fa fa-upload"></i> Import Selected (<span id="importBtnCount">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Result Modal -->
<div class="modal fade" id="importResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-check-circle text-success"></i> Import Completed
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <h4 id="result-summary"></h4>
                </div>

                <div id="result-details">
                    <p><strong>Imported:</strong> <span id="result-imported" class="text-success"></span></p>
                    <p><strong>Skipped:</strong> <span id="result-skipped" class="text-warning"></span></p>
                </div>

                <div id="result-errors" style="display: none;">
                    <h5>Errors:</h5>
                    <ul id="result-errors-list" class="text-danger"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="refreshTimesheetsBtn">
                    <i class="fa fa-refresh"></i> Refresh Timesheets
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Timesheet Cards -->
<div id="timesheetCards" class="row tw-mt-4 tw-mb-4"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function() {
        var projectId = <?= (int)$project->id ?>;

        // Load cards on page load
        loadTimesheetCards();

        // Add Timesheet Modal
        $('#addTimesheetBtn').click(function() {
            $('#newTimesheetModal').modal('show');
        });

        // Create Timesheet AJAX
        $('#newTimesheetForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

            $.ajax({
                url: admin_url + 'timesheet/create_bulk_timesheet',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Success', res.message, 'success');
                        $('#newTimesheetModal').modal('hide');
                        loadTimesheetCards(); // Refresh cards
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'An error occurred while creating the timesheet.', 'error');
                }
            });
        });

        function loadTimesheetCards() {
            $.get(admin_url + 'timesheet/get_project_months/' + projectId, function(res) {
                if (res.success) {
                    var html = '';
                    res.months.forEach(function(m) {
                        html += `
                            <div class="col-md-12">
                                <div class="card shadow-sm text-center">
                                    <h5>${m.formatted}</h5>
                                    <div class="d-flex justify-content-center mt-3">
                                        <a href="<?= admin_url('timesheet/edit/') ?>${projectId}/${m.month_year.substring(0,7)}" class="btn btn-primary">Timesheet</a>
                                        <button class="btn btn-info">Invoice</button>
                                        <a href="<?= admin_url('timesheet/salary_details/') ?>${projectId}/${m.month_year.substring(0,7)}" class="btn btn-secondary">Salary Details</a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#timesheetCards').html(html || '<div class="col-md-12 text-center p-4"><p class="mb-0">No timesheets yet. Create one above.</p></div>');
                }
            }, 'json');
        }

        // ===== PREVIEW & IMPORT FUNCTIONALITY =====

        // Handle Excel upload preview
        $('#uploadExcelForm').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            var btn = $('#previewExcelBtn');
            var originalText = btn.html();

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: admin_url + 'timesheet/preview_excel',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showPreviewModal(response);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to process Excel file', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Show preview modal with data
        function showPreviewModal(response) {
            var data = response.data;
            var missingEmployees = response.missing_employees || [];
            var month = response.month;
            var fileName = response.file_name;
            var projectId = response.project_id;

            // Set header info
            $('#preview-month-display').text(formatMonthYear(month));
            $('#preview-total-rows').text(data.length);

            // Store hidden data
            $('#preview_file_name').val(fileName);
            $('#preview_project_id').val(projectId);
            $('#preview_month').val(month);

            // Build table rows for employees in Excel
            var tbody = $('#previewTableBody');
            tbody.empty();

            $.each(data, function(index, row) {
                var statusBadge = getStatusBadge(row.status_class, row.status);
                var checkbox = row.can_import ?
                    '<input type="checkbox" class="row-checkbox" data-row-index="' + row.row_index + '">' :
                    '<input type="checkbox" disabled>';

                var tr = $('<tr></tr>');
                if (!row.can_import) {
                    tr.addClass('bg-light');
                }

                tr.append('<td>' + checkbox + '</td>');
                tr.append('<td>' + (index + 1) + '</td>');
                tr.append('<td>' + row.name + '</td>');
                tr.append('<td>' + row.iqama + '</td>');
                tr.append('<td>' + row.total_hours + '</td>');
                tr.append('<td>' + statusBadge + '</td>');

                tbody.append(tr);
            });

            // Build missing employees table
            if (missingEmployees.length > 0) {
                var missingTbody = $('#missingEmployeesTableBody');
                missingTbody.empty();

                $.each(missingEmployees, function(index, employee) {
                    var statusBadge = getStatusBadge(employee.status_class, employee.status);

                    var tr = $('<tr></tr>');
                    tr.addClass('warning'); // Highlight missing rows

                    tr.append('<td>' + (index + 1) + '</td>');
                    tr.append('<td>' + employee.name + '</td>');
                    tr.append('<td>' + employee.iqama + '</td>');
                    tr.append('<td>' + statusBadge + '</td>');

                    missingTbody.append(tr);
                });

                $('#missingEmployeesSection').show();
            } else {
                $('#missingEmployeesSection').hide();
            }

            // Enable select all if there are importable rows
            var importableCount = data.filter(r => r.can_import).length;
            if (importableCount > 0) {
                $('#selectAllCheckbox').prop('disabled', false);
                $('#selectAllImportable').prop('disabled', false);
            }

            updateSelectedCount();

            // Show modal
            $('#previewImportModal').modal('show');
        }

        // Format month YYYY-MM to Month, YYYY
        function formatMonthYear(month) {
            var date = new Date(month + '-01');
            return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        }

        // Get status badge HTML
        function getStatusBadge(statusClass, statusText) {
            var iconMap = {
                'success': 'fa-check-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle',
                'danger': 'fa-times-circle',
                'default': 'fa-minus-circle'
            };

            var icon = iconMap[statusClass] || 'fa-question-circle';
            return '<span class="label label-' + statusClass + '"><i class="fa ' + icon + '"></i> ' + statusText + '</span>';
        }

        // Handle row checkbox change
        $(document).on('change', '.row-checkbox', function() {
            updateSelectedCount();
        });

        // Select all importable checkbox
        $('#selectAllImportable').change(function() {
            var isChecked = $(this).is(':checked');
            $('.row-checkbox:not(:disabled)').prop('checked', isChecked);
            updateSelectedCount();
        });

        // Select all checkbox in header
        $('#selectAllCheckbox').change(function() {
            var isChecked = $(this).is(':checked');
            $('.row-checkbox:not(:disabled)').prop('checked', isChecked);
            updateSelectedCount();
        });

        // Update selected count and button state
        function updateSelectedCount() {
            var selectedCount = $('.row-checkbox:checked').length;
            $('#selectedCount').text(selectedCount + ' selected');
            $('#importBtnCount').text(selectedCount);

            if (selectedCount > 0) {
                $('#proceedImportBtn').prop('disabled', false);
            } else {
                $('#proceedImportBtn').prop('disabled', true);
            }

            // Update select all checkbox state
            var total = $('.row-checkbox:not(:disabled)').length;
            var checked = $('.row-checkbox:checked').length;
            $('#selectAllCheckbox').prop('checked', checked === total && total > 0);
            $('#selectAllImportable').prop('checked', checked === total && total > 0);
        }

        // Proceed with import
        $('#proceedImportBtn').click(function() {
            var selectedRows = [];
            $('.row-checkbox:checked').each(function() {
                selectedRows.push($(this).data('row-index'));
            });

            if (selectedRows.length === 0) {
                Swal.fire('Warning', 'Please select at least one row to import', 'warning');
                return;
            }

            var btn = $(this);
            var originalText = btn.html();

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');

            var ajaxData = {
                file_name: $('#preview_file_name').val(),
                project_id: $('#preview_project_id').val(),
                month: $('#preview_month').val(),
                selected_rows: selectedRows
            };

            if (typeof csrfData !== 'undefined') {
                ajaxData[csrfData.token_name] = csrfData.hash;
            }

            $.ajax({
                url: admin_url + 'timesheet/process_selective_import',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#previewImportModal').modal('hide');
                        showResultModal(response);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to import timesheet data', 'error');
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Show result modal
        function showResultModal(response) {
            $('#result-summary').text(response.message);
            $('#result-imported').text(response.imported + ' employees');
            $('#result-skipped').text(response.skipped + ' employees');

            if (response.errors && response.errors.length > 0) {
                var errorList = $('#result-errors-list');
                errorList.empty();
                $.each(response.errors, function(i, error) {
                    errorList.append('<li>' + error + '</li>');
                });
                $('#result-errors').show();
            } else {
                $('#result-errors').hide();
            }

            $('#importResultModal').modal('show');

            // Reset upload form
            $('#uploadExcelForm')[0].reset();
        }

        // Refresh timesheets button
        $('#refreshTimesheetsBtn').click(function() {
            $('#importResultModal').modal('hide');
            loadTimesheetCards();
        });

        // Reset modal on close
        $('#previewImportModal').on('hidden.bs.modal', function() {
            $('#previewTableBody').empty();
            $('#selectAllCheckbox').prop('checked', false).prop('disabled', true);
            $('#selectAllImportable').prop('checked', false).prop('disabled', true);
            $('#proceedImportBtn').prop('disabled', true);
            updateSelectedCount();
        });
    });
});
</script>
