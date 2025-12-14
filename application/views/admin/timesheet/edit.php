<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Your original CSS/Handsontable CDN here -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.full.min.js"></script>

<style>
.timesheet-wrapper { margin-top: 20px; }
.top-controls { display: flex; flex-direction: row; gap: 20px; margin-bottom: 20px; }
.timesheet-controls { flex: 1; }
.timesheet-toolbar { display: flex; align-items: center; margin-bottom: 15px; }
.timesheet-grid-full #timesheetGrid { overflow: auto; border: 1px solid #ddd; border-radius: 6px; }
.handsontable th { font-weight: bold; }
.label-strong { font-weight: 600; }
#masterFields { margin-bottom:10px; display:flex; gap:10px; }

/* New styles for proper button alignment */
.toolbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.toolbar-left, .toolbar-right {
    display: flex;
    align-items: center;
}

.month-label {
    margin-right: 20px;
    font-weight: 600;
}

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

/* Compact column headers with line breaks */
.handsontable .colHeader {
    white-space: normal !important;
    line-height: 1.2;
    font-size: 9px;
    text-align: center;
    padding: 2px 1px;
    min-width: 35px;
}

/* Compact table cells */
.handsontable td {
    font-size: 11px;
    padding: 2px 3px !important;
}

/* Compact row headers */
.handsontable th.ht__highlight {
    font-size: 11px;
    padding: 2px 4px;
}

/* Reduce overall table width */
.handsontable col {
    width: 40px !important;
}

/* Make Total column slightly wider */
.handsontable col:last-child {
    width: 50px !important;
}

/* Tighter grid wrapper */
.assignee-grid-wrapper {
    margin-bottom: 25px !important;
}

.assignee-grid-wrapper h5 {
    font-size: 14px;
    margin-bottom: 8px;
}

/* Additional optimizations for ultra-compact layout */
.handsontable table {
    font-size: 11px;
}

.handsontable .htCore td,
.handsontable .htCore th {
    height: 22px;
}

/* Reduce row header width further */
.ht_clone_left .handsontable table th {
    width: 70px !important;
    font-size: 10px;
}

/* Ensure grid doesn't overflow */
.timesheet-grid-full {
    overflow-x: auto;
    max-width: 100%;
}

#timesheetGrid {
    max-width: 100%;
}
</style>

<div id="wrapper">
    <div class="content">
        <div class="timesheet-wrapper">
            <h4 class="tw-mb-4 tw-mt-0">
                <i class="fa fa-clock-o"></i> Timesheet
            </h4>

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
                        <span class="info-label">Timesheet Month:</span>
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

            <div class="top-controls">
                <div class="timesheet-controls">
                    <!-- Main toolbar with left and right sections -->
                    <div class="toolbar-container">
                        <!-- Left side: Month label + Save Timesheet -->
                        <div class="toolbar-left">
                            <label class="month-label mb-0">Month: <?= date('F Y', strtotime($month . '-01')) ?></label>
                            <button id="saveGrid" class="btn btn-primary">Save Timesheet</button>
                        </div>

                        <!-- Right side: Salary Details button -->
                        <div class="toolbar-right">
                            <a href="<?= admin_url('timesheet/salary_details/' . $project->id . '/' . $month) ?>" 
                               class="btn btn-info">Salary Details</a>
                        </div>
                    </div>

                    <div id="masterFields" style="display:none;">
                        <input type="text" id="fat" placeholder="FAT" class="form-control" style="width:120px;">
                        <input type="number" id="unit_price" placeholder="Unit Price" class="form-control" style="width:140px;">
                        <input type="number" id="payable" placeholder="Payable" class="form-control" style="width:140px;">
                        <input type="text" id="remarks" placeholder="Remarks" class="form-control">
                    </div>
                </div>
            </div>

            <div class="timesheet-grid-full">
                <div id="timesheetGrid"></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
var admin_url = '<?= admin_url(); ?>';
var csrfData = { token_name: '<?= $this->security->get_csrf_token_name(); ?>', hash: '<?= $this->security->get_csrf_hash(); ?>' };

// Wait for jQuery and DOM
$(document).ready(function() {
    var projectId = <?= (int)$project->id ?>;
    var month = '<?= $month ?>';
    var hotInstances = {};
    var currentDates = [];
    var holidayDates = [];

    function loadData() {
        $.get(admin_url + 'timesheet/project_grid_data/' + projectId, { month: month }, function (res) {
            if (!res.success) {
                alert('Error loading timesheets');
                return;
            }
            currentDates = res.dates;
            holidayDates = res.holiday_dates || [];
            renderAllAssigneeGrids(res.assignees, month);
        }, 'json');
    }

    // Copy your original renderAllAssigneeGrids() function here exactly
    function renderAllAssigneeGrids(assignees, month) {
        var container = document.getElementById('timesheetGrid');
        container.innerHTML = '';

        hotInstances = {};

        assignees.forEach(function(a) {
            var wrapper = document.createElement('div');
            wrapper.classList.add('assignee-grid-wrapper');
            wrapper.style.marginBottom = "40px";

            var dtMonth = new Date(month + '-01T00:00:00'); 
            var formattedMonth = dtMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            var heading = document.createElement('h5');
            var iqamaInfo = a.iqama_number ? ` (${a.iqama_number})` : '';
            var hoursInfo = a.work_hours_per_day ? ` - ${a.work_hours_per_day} hrs/day` : ' - <span style="color: #e74c3c;">work hours per day is missing</span>';
            heading.innerHTML = `<span class="label-strong">${a.full_name}${iqamaInfo}${hoursInfo}</span> — <span class="label-strong">${formattedMonth}</span>`;
            wrapper.appendChild(heading);

            var gridDiv = document.createElement('div');
            gridDiv.id = 'grid_' + a.staff_id;
            wrapper.appendChild(gridDiv);
            container.appendChild(wrapper);

            var regularRow = [], overtimeRow = [];
            currentDates.forEach(function(date) {
                var d = (a.details && a.details[date]) || {};
                regularRow.push(d.regular_hours !== undefined ? d.regular_hours : '');
                overtimeRow.push(d.overtime_hours !== undefined ? d.overtime_hours : '');
            });
            regularRow.push(0); overtimeRow.push(0);

            var colHeaders = currentDates.map(function(d) {
                var dt = new Date(d + 'T00:00:00');
                var dayNum = dt.getDate();
                var weekday = dt.toLocaleDateString('en-US', { weekday: 'short' });
                // Super compact: just day number and weekday initial
                // e.g., "1<br>M" for "1st Monday"
                return `${dayNum}<br>${weekday.charAt(0)}`;
            });
            colHeaders.push('Total');

            var hot = new Handsontable(gridDiv, {
                data: [regularRow, overtimeRow],
                rowHeaders: ['Regular', 'Overtime'],
                rowHeaderWidth: 80,
                colHeaders: colHeaders,
                stretchH: 'none', // Changed from 'all' to 'none' for compact layout
                contextMenu: true,
                licenseKey: 'non-commercial-and-evaluation',
                columns: currentDates.map(function(){ return { type: 'numeric', width: 40 }; })
                        .concat({ type: 'numeric', readOnly: true, width: 55 }),
                colWidths: currentDates.map(function(){ return 40; }).concat([55]),
                height: 100,
                width: '100%',
                copyPaste: true,
                allowPaste: true,
                pasteMode: 'overwrite',
                fillHandle: {
                    direction: 'horizontal',
                    autoInsertRow: false
                },
                afterChange: function(changes, source) {
                    if (source === 'loadData' || source === 'updateTotal' || source === 'autoOverflow') return;
                    if (!changes) return;

                    var maxRegularHours = parseFloat(a.work_hours_per_day) || 0;

                    changes.forEach(function([row, col, oldVal, newVal]) {
                        if (col < currentDates.length) {
                            // Only apply overflow logic to Regular row (row 0)
                            if (row === 0) {
                                var enteredHours = parseFloat(newVal) || 0;

                                // Check if the date is a Friday or holiday
                                var dateString = currentDates[col];
                                var currentDate = new Date(dateString + 'T00:00:00');
                                var isFriday = currentDate.getDay() === 5; // Friday = 5
                                var isHoliday = holidayDates.indexOf(dateString) !== -1;

                                // If Friday or holiday, move all hours to overtime
                                if ((isFriday || isHoliday) && enteredHours > 0) {
                                    // Set regular to 0
                                    hot.setDataAtCell(0, col, 0, 'autoOverflow');

                                    // Set overtime to all entered hours
                                    hot.setDataAtCell(1, col, enteredHours, 'autoOverflow');
                                } else if (maxRegularHours > 0 && enteredHours > maxRegularHours) {
                                    // Normal overflow logic for non-Friday/non-holiday
                                    var overflow = enteredHours - maxRegularHours;

                                    // Update regular hours to max
                                    hot.setDataAtCell(0, col, maxRegularHours, 'autoOverflow');

                                    // Set overtime to overflow amount (not add to existing)
                                    hot.setDataAtCell(1, col, overflow, 'autoOverflow');
                                }
                            }

                            // Recalculate totals for both rows
                            for (var r = 0; r < 2; r++) {
                                var sum = 0;
                                for (var c=0; c<currentDates.length; c++) {
                                    sum += parseFloat(hot.getDataAtCell(r,c)) || 0;
                                }
                                hot.setDataAtCell(r, currentDates.length, sum, 'updateTotal');
                            }
                        }
                    });
                }
            });

            for (var r=0; r<2; r++) {
                var sum=0;
                for (var c=0; c<currentDates.length; c++) {
                    sum += parseFloat(hot.getDataAtCell(r,c)) || 0;
                }
                hot.setDataAtCell(r, currentDates.length, sum, 'updateTotal');
            }

            hotInstances[a.staff_id] = hot;
        });
    }

    // Copy your original save_all click handler, but fix month to '<?= $month ?>'
    $('#saveGrid').on('click', function() {
        var $gridContainer = $('#timesheetGrid');
        var fd = new FormData();
        fd.append('project_id', projectId);
        fd.append('month', month); // Fixed

        Object.keys(hotInstances).forEach(function(staffId) {
            var hot = hotInstances[staffId];
            var data = hot.getData();
            var regularRow = data[0] || [];
            var overtimeRow = data[1] || [];

            currentDates.forEach(function(date, idx) {
                fd.append(`timesheets[${staffId}][regular][${date}]`, regularRow[idx] || '');
                fd.append(`timesheets[${staffId}][overtime][${date}]`, overtimeRow[idx] || '');
            });

            // Add master fields per staff (for simplicity, same for all; you can make per-staff later)
            fd.append(`timesheets[${staffId}][fat]`, $('#fat').val() || '');
            fd.append(`timesheets[${staffId}][unit_price]`, $('#unit_price').val() || '');
            fd.append(`timesheets[${staffId}][payable]`, $('#payable').val() || '');
            fd.append(`timesheets[${staffId}][remarks]`, $('#remarks').val() || '');
        });

        fd.append(csrfData.token_name, csrfData.hash);

        blockArea($gridContainer);

        $.ajax({
            url: admin_url + 'timesheet/save_all',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire('Saved','Timesheet saved successfully','success');
                    loadData(); // Reload to update totals
                } else {
                    Swal.fire('Error', res.message || 'Save failed','error');
                }
            },
            error: function() {
                Swal.fire('Error','Server error','error');
            },
            complete: function() {
                unblockArea($gridContainer);
            }
        });
    });

    loadData(); // Initial load
});
</script>