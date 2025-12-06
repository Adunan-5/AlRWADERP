<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-mb-4">
                            <i class="fa fa-file-excel"></i> Import Staff - Preview
                        </h4>
                        <p class="text-muted">Review the data below and select which records you want to import. Records with existing IQAMA numbers, passport numbers, or employee codes will be updated.</p>

                        <?php
                        // Calculate summary counts
                        $newCount = 0;
                        $updateCount = 0;
                        $duplicateCount = 0;
                        foreach ($preview_data as $row) {
                            if ($row['action'] == 'insert') $newCount++;
                            elseif ($row['action'] == 'update') $updateCount++;
                            elseif ($row['action'] == 'duplicate') $duplicateCount++;
                        }
                        ?>

                        <!-- Summary Cards -->
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-3">
                                <div class="panel_s" style="margin-bottom: 0;">
                                    <div class="panel-body text-center" style="background-color: #d4edda; padding: 15px;">
                                        <h3 class="bold text-success" style="margin: 0;"><?= $newCount; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;">New Records</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s" style="margin-bottom: 0;">
                                    <div class="panel-body text-center" style="background-color: #d1ecf1; padding: 15px;">
                                        <h3 class="bold text-info" style="margin: 0;"><?= $updateCount; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;">Updates</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s" style="margin-bottom: 0;">
                                    <div class="panel-body text-center" style="background-color: #f8d7da; padding: 15px;">
                                        <h3 class="bold text-danger" style="margin: 0;"><?= $duplicateCount; ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;">Duplicates</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s" style="margin-bottom: 0;">
                                    <div class="panel-body text-center" style="background-color: #f8f9fa; padding: 15px;">
                                        <h3 class="bold text-primary" style="margin: 0;"><?= count($preview_data); ?></h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;">Total Rows</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($duplicateCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> <?= $duplicateCount ?> duplicate record(s) found in the file. Duplicate records cannot be imported and are disabled below.
                        </div>
                        <?php endif; ?>

                        <hr>

                        <?= form_open(admin_url('staff/process_import'), ['id' => 'import-process-form']) ?>

                        <div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
                            <div>
                                <button type="button" id="select-all" class="btn btn-default btn-sm">
                                    <i class="fa fa-check-square"></i> Select All Valid
                                </button>
                                <button type="button" id="deselect-all" class="btn btn-default btn-sm">
                                    <i class="fa fa-square"></i> Deselect All
                                </button>
                                <button type="button" id="select-new" class="btn btn-success btn-sm">
                                    <i class="fa fa-plus-square"></i> Select New Only
                                </button>
                                <button type="button" id="select-update" class="btn btn-info btn-sm">
                                    <i class="fa fa-edit"></i> Select Updates Only
                                </button>
                            </div>
                            <div>
                                <span class="tw-font-semibold">Total Rows: </span>
                                <span id="total-count" class="badge badge-default"><?= count($preview_data) ?></span>
                                <span class="tw-font-semibold tw-ml-3">Selected: </span>
                                <span id="selected-count" class="badge badge-primary">0</span>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" id="preview-table">
                                <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            <input type="checkbox" id="checkbox-all">
                                        </th>
                                        <th style="width: 80px;">Action</th>
                                        <th>EMP #</th>
                                        <th>Name</th>
                                        <th>IQAMA No</th>
                                        <th>Company</th>
                                        <th>Employee Type</th>
                                        <th>Nationality</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Basic Salary</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preview_data as $index => $row): ?>
                                    <?php
                                    $isDuplicate = ($row['action'] == 'duplicate');
                                    $isUpdate = ($row['action'] == 'update');
                                    $rowClass = $isDuplicate ? 'danger' : '';
                                    ?>
                                    <tr class="preview-row <?= $rowClass ?>" data-action="<?= $row['action'] ?>">
                                        <td class="text-center">
                                            <?php if ($isDuplicate): ?>
                                                <input type="checkbox" disabled title="Duplicate records cannot be imported">
                                            <?php else: ?>
                                                <input type="checkbox"
                                                       name="selected_rows[]"
                                                       value="<?= $index ?>"
                                                       class="row-checkbox">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['action'] == 'insert'): ?>
                                                <span class="label label-success">NEW</span>
                                            <?php elseif ($row['action'] == 'update'): ?>
                                                <span class="label label-info">UPDATE</span>
                                                <?php if (!empty($row['existing_by'])): ?>
                                                    <br><small class="text-muted" style="font-size: 9px;">via <?= htmlspecialchars($row['existing_by']) ?></small>
                                                <?php endif; ?>
                                            <?php elseif ($row['action'] == 'duplicate'): ?>
                                                <span class="label label-danger">DUPLICATE</span>
                                                <?php if (!empty($row['duplicate_errors'])): ?>
                                                    <br><i class="fa fa-info-circle text-danger"
                                                           style="cursor: help;"
                                                           title="<?= htmlspecialchars(implode('; ', $row['duplicate_errors'])) ?>"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['code']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['name']) ?>
                                            <?php if ($isDuplicate && !empty($row['duplicate_errors'])): ?>
                                                <div class="text-danger" style="font-size: 11px; margin-top: 3px;">
                                                    <?php foreach ($row['duplicate_errors'] as $error): ?>
                                                        <div><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($row['iqama_number']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                                        <td><?= htmlspecialchars($row['employee_type']) ?></td>
                                        <td><?= htmlspecialchars($row['nationality']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phonenumber']) ?></td>
                                        <td><?= htmlspecialchars($row['basics']) ?></td>
                                        <td><?= htmlspecialchars($row['status']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="tw-flex tw-justify-between tw-items-center">
                            <a href="<?= admin_url('staff') ?>" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="process-import-btn">
                                <i class="fa fa-check"></i> Import Selected Records
                            </button>
                        </div>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    // Update selected count
    function updateSelectedCount() {
        var count = $('.row-checkbox:checked').length;
        $('#selected-count').text(count);

        // Disable submit button if no rows selected
        if (count === 0) {
            $('#process-import-btn').prop('disabled', true);
        } else {
            $('#process-import-btn').prop('disabled', false);
        }
    }

    // Master checkbox - only affects non-duplicate rows
    $('#checkbox-all').on('change', function() {
        $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
        updateSelectedCount();
    });

    // Individual checkboxes
    $('.row-checkbox').on('change', function() {
        var totalCheckboxes = $('.row-checkbox:not(:disabled)').length;
        var checkedCheckboxes = $('.row-checkbox:not(:disabled):checked').length;

        $('#checkbox-all').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateSelectedCount();
    });

    // Select All Valid button - only selects non-duplicate rows
    $('#select-all').on('click', function() {
        $('.row-checkbox:not(:disabled)').prop('checked', true);
        $('#checkbox-all').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect All button
    $('#deselect-all').on('click', function() {
        $('.row-checkbox').prop('checked', false);
        $('#checkbox-all').prop('checked', false);
        updateSelectedCount();
    });

    // Select New Only
    $('#select-new').on('click', function() {
        $('.row-checkbox').prop('checked', false);
        $('.preview-row[data-action="insert"] .row-checkbox').prop('checked', true);
        $('#checkbox-all').prop('checked', false);
        updateSelectedCount();
    });

    // Select Updates Only
    $('#select-update').on('click', function() {
        $('.row-checkbox').prop('checked', false);
        $('.preview-row[data-action="update"] .row-checkbox').prop('checked', true);
        $('#checkbox-all').prop('checked', false);
        updateSelectedCount();
    });

    // Form submission validation
    $('#import-process-form').on('submit', function(e) {
        var selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount === 0) {
            alert('Please select at least one record to import.');
            e.preventDefault();
            return false;
        }

        if (!confirm('Are you sure you want to import ' + selectedCount + ' selected record(s)?')) {
            e.preventDefault();
            return false;
        }

        // Show loading indicator
        $('#process-import-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    });

    // Initial count update
    updateSelectedCount();
});
</script>

<style>
#preview-table thead th {
    background-color: #f5f5f5;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}

.preview-row:hover {
    background-color: #f9f9f9;
}

/* Duplicate row styling */
.preview-row.danger {
    background-color: #f8d7da !important;
}

.preview-row.danger:hover {
    background-color: #f5c6cb !important;
}

.preview-row.danger td {
    color: #721c24;
}

.table-responsive {
    border: 1px solid #ddd;
    border-radius: 4px;
}

#preview-table {
    margin-bottom: 0;
}

.label {
    font-size: 11px;
    padding: 3px 8px;
}

/* Ensure disabled checkboxes are visible */
input[type="checkbox"]:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Action column styling */
.label-info {
    background-color: #5bc0de;
}

.label-danger {
    background-color: #d9534f;
}
</style>
</body>
</html>
