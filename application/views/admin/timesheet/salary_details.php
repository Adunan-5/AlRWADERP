<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Handsontable CDN for timesheet editing -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.full.min.js"></script>

<div id="wrapper">
    <div class="content">
        <!-- Project and Client Details Header -->
        <div class="project-header">
            <h3>
                <i class="fa fa-briefcase"></i>
                <?= e($project->name); ?>
            </h3>
            <div class="project-info-grid">
                <div class="info-item">
                    <span class="info-label">Client:</span>
                    <span class="info-value">
                        <?php if (isset($project->client_data) && $project->client_data): ?>
                            <?= e($project->client_data->company); ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Project ID:</span>
                    <span class="info-value">#<?= e($project->id); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <?php
                        $status_names = [1 => 'Not Started', 2 => 'In Progress', 3 => 'On Hold', 4 => 'Cancelled', 5 => 'Finished'];
                        $status = isset($status_names[$project->status]) ? $status_names[$project->status] : 'Unknown';
                        $status_colors = [1 => 'default', 2 => 'info', 3 => 'warning', 4 => 'danger', 5 => 'success'];
                        $color = isset($status_colors[$project->status]) ? $status_colors[$project->status] : 'default';
                        ?>
                        <span class="label label-<?= $color ?>"><?= $status ?></span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Salary Month:</span>
                    <span class="info-value">
                        <strong><?= date('F Y', strtotime($month . '-01')) ?></strong>
                    </span>
                </div>
                <?php if (!empty($project->start_date)): ?>
                <div class="info-item">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value"><?= date('d M Y', strtotime($project->start_date)); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($project->deadline)): ?>
                <div class="info-item">
                    <span class="info-label">Deadline:</span>
                    <span class="info-value"><?= date('d M Y', strtotime($project->deadline)); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back to Project Timesheets Button -->
        <div style="margin-bottom: 20px;">
            <a href="<?= admin_url('projects/view/' . $project->id . '?group=project_timesheets') ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Project Timesheets
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h4 class="mb-3"><?= $title ?></h4>
                <div class="table-responsive">
                    <div class="panel_s">
                        <div class="panel-body">
                            <table id="salaryTable" class="table table-striped dt-table" data-order-col="1">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Name</th>
                                        <th>Regular Hours</th>
                                        <th>Regular Pay</th>
                                        <th>OT Hours</th>
                                        <th>OT Pay</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Total</th>
                                        <th>To Pay</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sl = 1; foreach ($assignees as $a): ?>
                                        <tr>
                                            <td><?= $sl++ ?></td>
                                            <td>
                                                <a href="<?= admin_url('staff/member/' . $a['staff_id']) ?>">
                                                    <?= html_escape($a['full_name']) ?>
                                                </a>
                                                <?php if (!empty($a['iqama_number'])): ?>
                                                    <br><small class="text-muted">IQAMA: <?= html_escape($a['iqama_number']) ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($a['staff_type_name']) || !empty($a['employee_id'])): ?>
                                                    <br><small class="text-muted">
                                                        <?php if (!empty($a['staff_type_name'])): ?>
                                                            <?= html_escape($a['staff_type_name']) ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($a['employee_id'])): ?>
                                                            <?= !empty($a['staff_type_name']) ? ' | ' : '' ?>ID: <?= html_escape($a['employee_id']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right"><?= number_format($a['regular_hours'], 2) ?></td>
                                            <td class="text-right"><?= app_format_money($a['regular_pay'], get_base_currency()) ?></td>
                                            <td class="text-right"><?= number_format($a['overtime_hours'], 2) ?></td>
                                            <td class="text-right"><?= app_format_money($a['overtime_pay'], get_base_currency()) ?></td>
                                            <td class="text-right"><?= app_format_money($a['allowances'], get_base_currency()) ?></td>
                                            <td class="text-right"><?= app_format_money($a['deductions'], get_base_currency()) ?></td>
                                            <td class="text-right"><strong><?= app_format_money($a['total'], get_base_currency()) ?></strong></td>
                                            <td class="text-right">
                                                <?php if (false) { // TODO: Check if paid from timesheet_master.paid field ?>
                                                    <span class="badge badge-success">Paid</span>
                                                <?php } else { ?>
                                                    <strong><?= app_format_money($a['to_pay'], get_base_currency()) ?></strong>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-salary-details"
                                                    data-staff-id="<?= $a['staff_id'] ?>"
                                                    data-name="<?= html_escape($a['full_name']) ?>"
                                                    data-iqama="<?= html_escape($a['iqama_number']) ?>"
                                                    data-month="<?= $month ?>"
                                                    data-regular-hours="<?= $a['regular_hours'] ?>"
                                                    data-regular-pay="<?= $a['regular_pay'] ?>"
                                                    data-overtime-hours="<?= $a['overtime_hours'] ?>"
                                                    data-overtime-pay="<?= $a['overtime_pay'] ?>"
                                                    data-allowances="<?= $a['allowances'] ?>"
                                                    data-deductions="<?= $a['deductions'] ?>"
                                                    data-total="<?= $a['total'] ?>">
                                                    View Details
                                                </button>
                                                <button class="btn btn-sm btn-primary edit-timesheet-btn"
                                                    data-staff-id="<?= $a['staff_id'] ?>"
                                                    data-name="<?= html_escape($a['full_name']) ?>"
                                                    data-project-id="<?= $project->id ?>"
                                                    data-month="<?= $month ?>">
                                                    <i class="fa fa-edit"></i> Edit Timesheet
                                                </button>
                                                <a href="<?= admin_url('timesheet/download_payslip/' . $project->id . '/' . $a['staff_id'] . '/' . $month) ?>"
                                                   class="btn btn-sm btn-success" target="_blank">
                                                    <i class="fa fa-file-pdf-o"></i> PaySlip
                                                </a>
                                                <button class="btn btn-sm btn-warning add-deduct-btn"
                                                    data-staff-id="<?= $a['staff_id'] ?>"
                                                    data-name="<?= html_escape($a['full_name']) ?>"
                                                    data-project-id="<?= $project->id ?>"
                                                    data-month="<?= $month ?>">
                                                    Add/Deduct
                                                </button>
                                                <button class="btn btn-sm btn-success make-payment-btn"
                                                    data-staff-id="<?= $a['staff_id'] ?>"
                                                    data-name="<?= html_escape($a['full_name']) ?>"
                                                    data-project-id="<?= $project->id ?>"
                                                    data-month="<?= $month ?>"
                                                    data-balance="<?= $a['to_pay'] ?>">
                                                    <i class="fa fa-money"></i> Make Payment
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Deduct Allowance Modal -->
<div class="modal fade" id="addDeductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Allowance / Deduction</h4>
                <p class="text-muted" id="add-deduct-employee-name" style="margin: 5px 0 0 0;"></p>
            </div>
            <form id="addDeductForm">
                <div class="modal-body">
                    <input type="hidden" id="add-deduct-staff-id" name="staff_id">
                    <input type="hidden" id="add-deduct-current-month" name="current_month">

                    <!-- Type Selection -->
                    <div class="form-group">
                        <label for="adjustment-type">Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="adjustment-type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="allowance">Allowance</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="form-group">
                        <label for="adjustment-date">Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="adjustment-date" name="date" required autocomplete="off">
                    </div>

                    <!-- Project Selection -->
                    <div class="form-group">
                        <label for="adjustment-project">Project <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" id="adjustment-project" name="project_id" data-live-search="true" required>
                            <option value="">Loading projects...</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="adjustment-description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="adjustment-description" name="description" rows="3" placeholder="Enter description" required></textarea>
                    </div>

                    <!-- Amount -->
                    <div class="form-group">
                        <label for="adjustment-amount">Amount (<?= get_base_currency()->symbol ?>) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="adjustment-amount" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Timesheet Modal -->
<div class="modal fade" id="editTimesheetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-edit"></i> Edit Timesheet - <span id="edit-timesheet-employee-name"></span>
                </h4>
                <p class="text-muted" id="edit-timesheet-month" style="margin: 5px 0 0 0;"></p>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" style="margin-bottom: 15px;">
                    <i class="fa fa-info-circle"></i>
                    <strong>Instructions:</strong> Enter regular hours in the first column and overtime hours in the second column for each day. Leave blank for non-working days.
                </div>

                <div id="timesheetGridContainer" style="overflow-x: auto; max-height: 500px;">
                    <div id="singleEmployeeTimesheetGrid"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEmployeeTimesheet">
                    <i class="fa fa-save"></i> Save Timesheet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Salary Details Modal -->
<div class="modal fade" id="salaryDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <span id="modal-employee-name"></span>
                    <span id="modal-employee-iqama"></span>
                </h4>
                <p class="text-muted" id="modal-month" style="margin: 5px 0 0 0;"></p>
            </div>
            <div class="modal-body">
                <div class="salary-details-content">
                    <!-- Basic Salary -->
                    <div class="salary-row">
                        <span class="salary-label">BASIC</span>
                        <span class="salary-value" id="modal-basic"></span>
                    </div>

                    <!-- Hours breakdown -->
                    <div class="salary-row text-muted" style="font-size: 13px; padding-left: 20px;">
                        <span id="modal-hours-breakdown"></span>
                    </div>

                    <!-- Overtime -->
                    <div class="salary-row" id="modal-overtime-row" style="display: none;">
                        <span class="salary-label">OVERTIME</span>
                        <span class="salary-value" id="modal-overtime"></span>
                    </div>

                    <!-- OT Hours breakdown -->
                    <div class="salary-row text-muted" id="modal-overtime-breakdown-row" style="font-size: 13px; padding-left: 20px; display: none;">
                        <span id="modal-overtime-breakdown"></span>
                    </div>

                    <hr style="margin: 10px 0;">

                    <!-- Gross Salary -->
                    <div class="salary-row">
                        <span class="salary-label">GROSS SALARY</span>
                        <span class="salary-value" id="modal-gross"></span>
                    </div>

                    <hr style="margin: 10px 0;">

                    <!-- Allowances Section -->
                    <div id="allowances-section">
                        <div class="salary-row allowance-header">
                            <span class="salary-label">+ ALLOWANCES</span>
                            <span class="salary-value" id="modal-allowances-total"></span>
                        </div>
                        <div id="allowances-breakdown" style="padding-left: 20px; font-size: 13px;">
                            <!-- Allowance items will be inserted here -->
                        </div>
                    </div>

                    <!-- Deductions Section -->
                    <div id="deductions-section">
                        <div class="salary-row deduction-header">
                            <span class="salary-label">- DEDUCTIONS</span>
                            <span class="salary-value" id="modal-deductions-total"></span>
                        </div>
                        <div id="deductions-breakdown" style="padding-left: 20px; font-size: 13px;">
                            <!-- Deduction items will be inserted here -->
                        </div>
                    </div>

                    <hr style="margin: 10px 0;">

                    <!-- Grand Total -->
                    <div class="salary-row" style="font-weight: 600;">
                        <span class="salary-label">GRAND TOTAL</span>
                        <span class="salary-value" id="modal-grand-total"></span>
                    </div>

                    <hr style="margin: 15px 0;">

                    <!-- Payment Details -->
                    <div class="salary-row" style="font-weight: 600; margin-bottom: 10px;">
                        <span class="salary-label">PAYMENT DETAILS</span>
                    </div>

                    <div id="payment-details-list">
                        <!-- Payment records will be inserted here -->
                    </div>

                </div>
            </div>
            <div class="modal-footer" style="text-align: right;">
                <span class="badge badge-success" id="paid-badge" style="font-size: 14px; padding: 8px 15px; display: none;">Paid</span>
            </div>
        </div>
    </div>
</div>

<!-- Make Payment Modal -->
<div class="modal fade" id="makePaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="payment-modal-employee-name"></h4>
                <p class="text-muted" id="payment-modal-amount-payable" style="margin: 5px 0 0 0;"></p>
            </div>
            <form id="makePaymentForm">
                <div class="modal-body">
                    <input type="hidden" id="payment-staff-id" name="staff_id">
                    <input type="hidden" id="payment-project-id" name="project_id">
                    <input type="hidden" id="payment-month" name="month">

                    <!-- Paid From -->
                    <div class="form-group">
                        <label for="paid-from">PAID FROM <span class="text-danger">*</span></label>
                        <select class="form-control" id="paid-from" name="paid_from" required>
                            <option value="">Select payment source</option>
                            <option value="bank">Bank</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <!-- Paid Date -->
                    <div class="form-group">
                        <label for="paid-date">PAID DATE <span class="text-danger">*</span></label>
                        <input type="text" class="form-control datepicker" id="paid-date" name="paid_date" required autocomplete="off">
                    </div>

                    <!-- Amount -->
                    <div class="form-group">
                        <label for="payment-amount">AMOUNT <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="payment-amount" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>

                    <!-- Reference Number -->
                    <div class="form-group">
                        <label for="reference-number">REFERENCE NUMBER</label>
                        <input type="text" class="form-control" id="reference-number" name="reference_number" placeholder="Enter reference number">
                    </div>

                    <!-- Is Bank Transfer -->
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="is-bank-transfer" name="is_bank_transfer" value="1">
                                IS BANK TRANSFER?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Make Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Project/Client Header Section */
.project-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.project-header h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 22px;
    font-weight: 600;
}

.project-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: flex-start;
}

.info-label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
    margin-right: 10px;
}

.info-value {
    color: #212529;
}

@media (max-width: 768px) {
    .project-info-grid {
        grid-template-columns: 1fr;
    }
}

.salary-details-content {
    padding: 10px 0;
}

.salary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.salary-label {
    font-weight: 600;
    color: #555;
    text-transform: uppercase;
}

.salary-value {
    font-weight: 600;
    color: #333;
}

#modal-employee-iqama {
    display: block;
    font-size: 14px;
    color: #888;
    font-weight: normal;
}

#modal-month {
    color: #00b393;
    font-size: 15px;
}

#payment-details-list {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 4px;
    margin-top: 5px;
}

.payment-record {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.payment-record:last-child {
    border-bottom: none;
}

.payment-info {
    flex: 1;
    font-size: 13px;
    color: #555;
}

.payment-amount {
    font-weight: 600;
    margin-right: 10px;
}

.payment-delete {
    color: #d9534f;
    cursor: pointer;
}

/* Allowances and Deductions Styling */
.allowance-header {
    color: #28a745 !important;
}

.allowance-header .salary-label,
.allowance-header .salary-value {
    color: #28a745 !important;
    font-weight: 600;
}

.deduction-header {
    color: #dc3545 !important;
}

.deduction-header .salary-label,
.deduction-header .salary-value {
    color: #dc3545 !important;
    font-weight: 600;
}

.adjustment-item {
    padding: 5px 0;
    color: #666;
}

.adjustment-item-date {
    font-weight: 500;
    color: #333;
}

.adjustment-item-desc {
    color: #777;
}

.adjustment-item-amount {
    font-weight: 500;
}

.allowance-item .adjustment-item-amount {
    color: #28a745;
}

.deduction-item .adjustment-item-amount {
    color: #dc3545;
}

/* Timesheet Grid Styling */
.handsontable th {
    font-weight: bold;
    background-color: #f8f9fa;
}

.handsontable .colHeader {
    white-space: normal !important;
    line-height: 1.2;
    font-size: 11px;
    text-align: center;
    padding: 4px 2px;
}

.handsontable td {
    font-size: 12px;
    padding: 3px 4px !important;
}

.handsontable th.ht__highlight {
    font-size: 11px;
    padding: 3px 5px;
}

#singleEmployeeTimesheetGrid {
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Make date column headers more readable */
.handsontable .htCore thead th {
    font-weight: 600;
}

/* Highlight weekend columns */
.handsontable .weekend-column {
    background-color: #fff3cd !important;
}

/* Total column styling */
.handsontable .total-column {
    background-color: #e7f3ff !important;
    font-weight: bold;
}
</style>

<?php init_tail(); ?>
<script>
$(document).ready(function() {
    // View Salary Details Modal
    $(document).on('click', '.view-salary-details', function() {
        var data = $(this).data();

        // Populate modal header
        $('#modal-employee-name').text(data.name);
        $('#modal-employee-iqama').text('(' + data.iqama + ')');

        // Format month (convert YYYY-MM to Month, YYYY)
        var monthDate = new Date(data.month + '-01');
        var monthName = monthDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        $('#modal-month').text(monthName);

        // Format currency
        var currency = '<?= get_base_currency()->symbol ?>';

        // Calculate basic (regular pay)
        var basic = parseFloat(data.regularPay);
        $('#modal-basic').text(formatMoney(basic, currency));

        // Hours breakdown - show only regular hours for BASIC calculation
        var regularHours = parseFloat(data.regularHours);
        var overtimeHours = parseFloat(data.overtimeHours);
        var rate = regularHours > 0 ? (basic / regularHours).toFixed(2) : '0.00';

        $('#modal-hours-breakdown').text('( ' + regularHours.toFixed(2) + ' HOURS X ' + rate + ' SAR )');

        // Overtime
        var overtime = parseFloat(data.overtimePay);
        if (overtimeHours > 0 && overtime > 0) {
            $('#modal-overtime').text(formatMoney(overtime, currency));
            $('#modal-overtime-row').show();

            var otRate = (overtime / overtimeHours).toFixed(2);
            $('#modal-overtime-breakdown').text('( ' + overtimeHours.toFixed(2) + ' HOURS X ' + otRate + ' SAR )');
            $('#modal-overtime-breakdown-row').show();
        } else {
            $('#modal-overtime-row').hide();
            $('#modal-overtime-breakdown-row').hide();
        }

        // Gross Salary (basic + overtime)
        var gross = basic + overtime;
        $('#modal-gross').text(formatMoney(gross, currency));

        // Allowances and Deductions - will be loaded via AJAX
        var allowances = parseFloat(data.allowances);
        var deductions = parseFloat(data.deductions);
        $('#modal-allowances-total').text(formatMoney(allowances, currency));
        $('#modal-deductions-total').text(formatMoney(deductions, currency));

        // Grand Total (gross + allowances - deductions)
        var grandTotal = gross + allowances - deductions;
        $('#modal-grand-total').text(formatMoney(grandTotal, currency));

        // Load adjustment breakdown via AJAX
        loadAdjustmentBreakdown(data.staffId, <?= $project->id ?>, data.month);

        // Load payment details
        loadPaymentDetails(data.staffId, <?= $project->id ?>, data.month, grandTotal);

        // Show modal
        $('#salaryDetailsModal').modal('show');
    });

    // Helper function to format money
    function formatMoney(amount, currency) {
        return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' ' + currency;
    }

    // Load payment details
    function loadPaymentDetails(staffId, projectId, month, grandTotal) {
        var currency = '<?= get_base_currency()->symbol ?>';

        // Add CSRF token
        var ajaxData = {
            staff_id: staffId,
            project_id: projectId,
            month: month
        };

        if (typeof csrfData !== 'undefined') {
            ajaxData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url: '<?= admin_url('timesheet/get_payments') ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#payment-details-list').html('');

                    if (response.payments && response.payments.length > 0) {
                        $.each(response.payments, function(i, payment) {
                            var paymentHtml = '<div class="payment-record">';
                            paymentHtml += '<div class="payment-info">';
                            paymentHtml += '<strong>PAID ON:</strong> ' + payment.paid_date + ' | ';
                            paymentHtml += '<strong>BY:</strong> ' + payment.paid_by + ' | ';
                            paymentHtml += '<strong>FROM:</strong> ' + payment.paid_from;
                            if (payment.reference_number) {
                                paymentHtml += ' | <strong>REF:</strong> ' + payment.reference_number;
                            }
                            paymentHtml += '</div>';
                            paymentHtml += '<span class="payment-amount">' + formatMoney(payment.amount, currency) + '</span>';
                            paymentHtml += '<i class="fa fa-trash payment-delete" data-payment-id="' + payment.id + '" style="cursor:pointer; margin-left: 10px;"></i>';
                            paymentHtml += '</div>';
                            $('#payment-details-list').append(paymentHtml);
                        });

                        // Show balance if not fully paid
                        var balance = grandTotal - response.total_paid;
                        if (balance > 0.01) {
                            var balanceHtml = '<div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px;">';
                            balanceHtml += '<strong>BALANCE TO BE PAID:</strong> <span style="float:right; color: #856404;">' + formatMoney(balance, currency) + '</span>';
                            balanceHtml += '</div>';
                            $('#payment-details-list').append(balanceHtml);
                        } else if (Math.abs(balance) <= 0.01) {
                            $('#paid-badge').show();
                        }
                    } else {
                        $('#payment-details-list').html('<p class="text-muted" style="margin: 0;">No payment records yet</p>');
                    }
                } else {
                    $('#payment-details-list').html('<p class="text-danger" style="margin: 0;">Failed to load payment details</p>');
                }
            },
            error: function() {
                $('#payment-details-list').html('<p class="text-danger" style="margin: 0;">Failed to load payment details</p>');
            }
        });
    }

    // Load adjustment breakdown
    function loadAdjustmentBreakdown(staffId, projectId, month) {
        var currency = '<?= get_base_currency()->symbol ?>';

        // Add CSRF token
        var ajaxData = {
            staff_id: staffId,
            project_id: projectId,
            month: month
        };

        if (typeof csrfData !== 'undefined') {
            ajaxData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url: '<?= admin_url('timesheet/get_adjustments') ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear previous content
                    $('#allowances-breakdown').html('');
                    $('#deductions-breakdown').html('');

                    // Display allowances
                    if (response.allowances && response.allowances.length > 0) {
                        var allowancesHtml = '';
                        $.each(response.allowances, function(i, item) {
                            allowancesHtml += '<div class="adjustment-item allowance-item">';
                            allowancesHtml += '<div class="salary-row" style="padding: 3px 0;">';
                            allowancesHtml += '<span class="adjustment-item-desc">';
                            allowancesHtml += '<span class="adjustment-item-date">' + item.date + '</span> - ';
                            allowancesHtml += item.description;
                            allowancesHtml += '</span>';
                            allowancesHtml += '<span class="adjustment-item-amount">+' + formatMoney(parseFloat(item.amount), currency) + '</span>';
                            allowancesHtml += '</div>';
                            allowancesHtml += '</div>';
                        });
                        $('#allowances-breakdown').html(allowancesHtml);
                    } else {
                        $('#allowances-breakdown').html('<div class="text-muted" style="padding: 5px 0;">No allowances</div>');
                    }

                    // Display deductions
                    if (response.deductions && response.deductions.length > 0) {
                        var deductionsHtml = '';
                        $.each(response.deductions, function(i, item) {
                            deductionsHtml += '<div class="adjustment-item deduction-item">';
                            deductionsHtml += '<div class="salary-row" style="padding: 3px 0;">';
                            deductionsHtml += '<span class="adjustment-item-desc">';
                            deductionsHtml += '<span class="adjustment-item-date">' + item.date + '</span> - ';
                            deductionsHtml += item.description;
                            deductionsHtml += '</span>';
                            deductionsHtml += '<span class="adjustment-item-amount">-' + formatMoney(parseFloat(item.amount), currency) + '</span>';
                            deductionsHtml += '</div>';
                            deductionsHtml += '</div>';
                        });
                        $('#deductions-breakdown').html(deductionsHtml);
                    } else {
                        $('#deductions-breakdown').html('<div class="text-muted" style="padding: 5px 0;">No deductions</div>');
                    }
                }
            },
            error: function() {
                $('#allowances-breakdown').html('<div class="text-danger" style="padding: 5px 0;">Failed to load breakdown</div>');
                $('#deductions-breakdown').html('<div class="text-danger" style="padding: 5px 0;">Failed to load breakdown</div>');
            }
        });
    }

    // Add/Deduct Button Click Handler
    $(document).on('click', '.add-deduct-btn', function() {
        var staffId = $(this).data('staff-id');
        var staffName = $(this).data('name');
        var projectId = $(this).data('project-id');
        var month = $(this).data('month');

        // Set employee name in modal
        $('#add-deduct-employee-name').text(staffName);

        // Set hidden fields
        $('#add-deduct-staff-id').val(staffId);
        $('#add-deduct-current-month').val(month);

        // Reset form
        $('#addDeductForm')[0].reset();

        // Set today's date as default
        $('#adjustment-date').val('<?= date('Y-m-d') ?>');

        // Load assigned projects for this staff member
        loadAssignedProjects(staffId, projectId);

        // Show modal
        $('#addDeductModal').modal('show');
    });

    // Load assigned projects via AJAX
    function loadAssignedProjects(staffId, currentProjectId) {
        $.ajax({
            url: '<?= admin_url('timesheet/get_assigned_projects') ?>',
            type: 'POST',
            data: { staff_id: staffId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.projects.length > 0) {
                    var options = '<option value="">Select Project</option>';
                    $.each(response.projects, function(i, project) {
                        var selected = (project.id == currentProjectId) ? 'selected' : '';
                        options += '<option value="' + project.id + '" ' + selected + '>' +
                                   project.name + '</option>';
                    });
                    $('#adjustment-project').html(options);

                    // Refresh selectpicker if used
                    if ($.fn.selectpicker) {
                        $('#adjustment-project').selectpicker('refresh');
                    }
                } else {
                    $('#adjustment-project').html('<option value="">No projects assigned</option>');
                    if ($.fn.selectpicker) {
                        $('#adjustment-project').selectpicker('refresh');
                    }
                }
            },
            error: function() {
                alert_float('danger', 'Failed to load projects');
            }
        });
    }

    // Form submission handler
    $('#addDeductForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();

        // Add CSRF token
        if (typeof csrfData !== 'undefined') {
            formData += '&' + csrfData.token_name + '=' + csrfData.hash;
        }

        // Disable button and show loading
        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '<?= admin_url('timesheet/save_adjustment') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', 'Adjustment saved successfully');
                    $('#addDeductModal').modal('hide');

                    // Reload page to show updated calculations
                    location.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to save adjustment');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while saving');
            },
            complete: function() {
                // Re-enable button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Initialize datepicker when modal is shown
    $('#addDeductModal').on('shown.bs.modal', function() {
        // Use the system's init_datepicker function
        init_datepicker($('#adjustment-date'));
    });

    // Edit Timesheet Button Click Handler
    var timesheetHotInstance = null;
    $(document).on('click', '.edit-timesheet-btn', function() {
        var staffId = $(this).data('staff-id');
        var staffName = $(this).data('name');
        var projectId = $(this).data('project-id');
        var month = $(this).data('month');

        // Set modal title
        $('#edit-timesheet-employee-name').text(staffName);
        $('#edit-timesheet-month').text('Month: ' + formatMonthYear(month));

        // Show modal
        $('#editTimesheetModal').modal('show');

        // Load timesheet data
        loadEmployeeTimesheet(staffId, projectId, month);
    });

    // Format month from YYYY-MM to Month, YYYY
    function formatMonthYear(month) {
        var date = new Date(month + '-01');
        return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    }

    // Load employee timesheet data
    function loadEmployeeTimesheet(staffId, projectId, month) {
        var ajaxData = {
            staff_id: staffId,
            project_id: projectId,
            month: month
        };

        if (typeof csrfData !== 'undefined') {
            ajaxData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url: '<?= admin_url('timesheet/get_employee_timesheet') ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderTimesheetGrid(response.data, staffId, projectId, month);
                } else {
                    alert_float('danger', response.message || 'Failed to load timesheet data');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while loading timesheet');
            }
        });
    }

    // Render Handsontable grid
    function renderTimesheetGrid(data, staffId, projectId, month) {
        var container = document.getElementById('singleEmployeeTimesheetGrid');

        // Destroy existing instance if any
        if (timesheetHotInstance) {
            timesheetHotInstance.destroy();
        }

        // Build column headers (dates)
        var dates = data.dates;
        var details = data.details;

        // Store dates in modal for later use
        $('#editTimesheetModal').attr('data-dates', JSON.stringify(dates));

        var columns = [{data: 'type', title: 'Type', readOnly: true, width: 80}];

        // Add one column for each date
        dates.forEach(function(dateStr) {
            var dateObj = new Date(dateStr);
            var dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
            var dayNum = dateObj.getDate();

            columns.push({
                data: dateStr,
                title: dayNum + '\n' + dayName,
                type: 'numeric',
                numericFormat: {pattern: '0.00'},
                width: 50
            });
        });

        // Add Total column
        columns.push({data: 'total', title: 'Total', readOnly: true, className: 'total-column', width: 70});

        // Build data rows - separate rows for Regular and OT
        var regularRow = {type: 'Regular'};
        var overtimeRow = {type: 'Overtime'};

        var totalRegular = 0;
        var totalOvertime = 0;

        dates.forEach(function(dateStr) {
            var regular = details[dateStr] ? (details[dateStr].regular_hours || '') : '';
            var overtime = details[dateStr] ? (details[dateStr].overtime_hours || '') : '';

            regularRow[dateStr] = regular;
            overtimeRow[dateStr] = overtime;

            if (regular) totalRegular += parseFloat(regular);
            if (overtime) totalOvertime += parseFloat(overtime);
        });

        regularRow['total'] = totalRegular.toFixed(2);
        overtimeRow['total'] = totalOvertime.toFixed(2);

        var gridData = [regularRow, overtimeRow];

        // Initialize Handsontable
        timesheetHotInstance = new Handsontable(container, {
            data: gridData,
            columns: columns,
            colHeaders: true,
            rowHeaders: true,
            stretchH: 'none',
            width: '100%',
            height: 'auto',
            licenseKey: 'non-commercial-and-evaluation',
            afterChange: function(changes, source) {
                if (source === 'edit') {
                    calculateTotals();
                }
            },
            cells: function(row, col) {
                var cellProperties = {};
                // Make Type column read-only
                if (col === 0) {
                    cellProperties.readOnly = true;
                }
                return cellProperties;
            }
        });

        // Calculate totals function
        function calculateTotals() {
            var totalRegular = 0;
            var totalOvertime = 0;

            var regularRow = timesheetHotInstance.getDataAtRow(0);
            var overtimeRow = timesheetHotInstance.getDataAtRow(1);

            // Calculate totals for each row (skip first column which is 'Type', and last which is 'Total')
            for (var col = 1; col < columns.length - 1; col++) {
                var regVal = parseFloat(regularRow[col]) || 0;
                var otVal = parseFloat(overtimeRow[col]) || 0;

                totalRegular += regVal;
                totalOvertime += otVal;
            }

            // Update total columns
            timesheetHotInstance.setDataAtCell(0, columns.length - 1, totalRegular.toFixed(2), 'internal');
            timesheetHotInstance.setDataAtCell(1, columns.length - 1, totalOvertime.toFixed(2), 'internal');
        }
    }

    // Save employee timesheet
    $('#saveEmployeeTimesheet').on('click', function() {
        if (!timesheetHotInstance) {
            alert_float('danger', 'No timesheet data to save');
            return;
        }

        var btn = $(this);
        var originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        var regularRow = timesheetHotInstance.getDataAtRow(0);
        var overtimeRow = timesheetHotInstance.getDataAtRow(1);
        var dates = JSON.parse($('#editTimesheetModal').attr('data-dates') || '[]');
        var staffId = parseInt($('#editTimesheetModal').attr('data-staff-id'));
        var projectId = parseInt($('#editTimesheetModal').attr('data-project-id'));
        var month = $('#editTimesheetModal').attr('data-month');

        // Build timesheet data
        var regular = {};
        var overtime = {};

        dates.forEach(function(dateStr, index) {
            // Column index: 0 = Type, 1-31 = dates, last = Total
            // So date columns start at index 1
            var colIndex = index + 1;

            regular[dateStr] = regularRow[colIndex] || '';
            overtime[dateStr] = overtimeRow[colIndex] || '';
        });

        var ajaxData = {
            staff_id: staffId,
            project_id: projectId,
            month: month,
            regular: regular,
            overtime: overtime
        };

        if (typeof csrfData !== 'undefined') {
            ajaxData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url: '<?= admin_url('timesheet/save_manual') ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', 'Timesheet saved successfully');
                    $('#editTimesheetModal').modal('hide');
                    location.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to save timesheet');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while saving');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Store data in modal attributes when opening
    $(document).on('click', '.edit-timesheet-btn', function() {
        var staffId = $(this).data('staff-id');
        var projectId = $(this).data('project-id');
        var month = $(this).data('month');

        $('#editTimesheetModal').attr('data-staff-id', staffId);
        $('#editTimesheetModal').attr('data-project-id', projectId);
        $('#editTimesheetModal').attr('data-month', month);
    });

    // Make Payment Button Click Handler
    $(document).on('click', '.make-payment-btn', function() {
        var staffId = $(this).data('staff-id');
        var staffName = $(this).data('name');
        var projectId = $(this).data('project-id');
        var month = $(this).data('month');
        var balance = $(this).data('balance');

        // Set employee name and amount payable
        $('#payment-modal-employee-name').text(staffName);
        $('#payment-modal-amount-payable').text('AMOUNT PAYABLE: ' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <?= get_base_currency()->symbol ?>');

        // Set hidden fields
        $('#payment-staff-id').val(staffId);
        $('#payment-project-id').val(projectId);
        $('#payment-month').val(month);

        // Set amount to balance
        $('#payment-amount').val(parseFloat(balance).toFixed(2));

        // Reset form
        $('#makePaymentForm')[0].reset();
        $('#payment-staff-id').val(staffId);
        $('#payment-project-id').val(projectId);
        $('#payment-month').val(month);
        $('#payment-amount').val(parseFloat(balance).toFixed(2));

        // Set today's date as default
        $('#paid-date').val('<?= date('Y-m-d') ?>');

        // Show modal
        $('#makePaymentModal').modal('show');
    });

    // Initialize datepicker when modal is shown
    $('#makePaymentModal').on('shown.bs.modal', function() {
        init_datepicker($('#paid-date'));
    });

    // Make Payment Form Submission
    $('#makePaymentForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();

        // Add CSRF token
        if (typeof csrfData !== 'undefined') {
            formData += '&' + csrfData.token_name + '=' + csrfData.hash;
        }

        // Disable button and show loading
        submitBtn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: '<?= admin_url('timesheet/save_payment') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', 'Payment recorded successfully');
                    $('#makePaymentModal').modal('hide');

                    // Reload page to show updated payment
                    location.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to record payment');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while recording payment');
            },
            complete: function() {
                // Re-enable button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Delete payment handler
    $(document).on('click', '.payment-delete', function() {
        if (!confirm('Are you sure you want to delete this payment record?')) {
            return;
        }

        var paymentId = $(this).data('payment-id');
        var deleteBtn = $(this);

        // Add CSRF token
        var ajaxData = { payment_id: paymentId };
        if (typeof csrfData !== 'undefined') {
            ajaxData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url: '<?= admin_url('timesheet/delete_payment') ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', 'Payment deleted successfully');
                    location.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to delete payment');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while deleting payment');
            }
        });
    });
});
</script>