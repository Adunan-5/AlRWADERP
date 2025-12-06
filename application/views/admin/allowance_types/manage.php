<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#allowance_type_modal" onclick="openAllowanceModal()">
                                <i class="fa fa-plus"></i> <?= _l('add_new_allowance_type') ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading">

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th><?= _l('id') ?></th>
                                        <th><?= _l('allowance_type_name') ?></th>
                                        <th><?= _l('allowance_type_name_arabic') ?></th>
                                        <th><?= _l('description') ?></th>
                                        <th><?= _l('is_active') ?></th>
                                        <th><?= _l('sort_order') ?></th>
                                        <th><?= _l('usage_count') ?></th>
                                        <th><?= _l('assignments') ?></th>
                                        <th><?= _l('options') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($allowances)): ?>
                                        <?php foreach ($allowances as $allowance): ?>
                                            <tr>
                                                <td><?= $allowance['id'] ?></td>
                                                <td><strong><?= e($allowance['name']) ?></strong></td>
                                                <td><?= e($allowance['name_arabic']) ?></td>
                                                <td><?= e($allowance['description']) ?></td>
                                                <td>
                                                    <?php if ($allowance['is_active'] == 1): ?>
                                                        <span class="label label-success"><?= _l('active') ?></span>
                                                    <?php else: ?>
                                                        <span class="label label-default"><?= _l('inactive') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $allowance['sort_order'] ?></td>
                                                <td>
                                                    <span class="badge"><?= $allowance['usage_count'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge"><?= $allowance['assignment_count'] ?></span>
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="editAllowance(<?= $allowance['id'] ?>)" title="<?= _l('edit') ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-info btn-icon" onclick="manageAssignments(<?= $allowance['id'] ?>, '<?= e($allowance['name']) ?>')" title="<?= _l('assign_to_employee_types') ?>">
                                                        <i class="fa fa-link"></i>
                                                    </a>
                                                    <?php if ($allowance['usage_count'] == 0 && $allowance['assignment_count'] == 0): ?>
                                                        <a href="#" class="btn btn-danger btn-icon" onclick="deleteAllowance(<?= $allowance['id'] ?>, '<?= e($allowance['name']) ?>')" title="<?= _l('delete') ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="#" class="btn btn-default btn-icon disabled" title="<?= _l('allowance_type_in_use') ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center"><?= _l('no_records_found') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Allowance Type Modal -->
<div class="modal fade" id="allowance_type_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= form_open(admin_url('allowance_types/save'), ['id' => 'allowance-form']) ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="allowanceModalTitle"><?= _l('add_new_allowance_type') ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="allowance_id">

                <?= render_input('name', 'allowance_type_name', '', 'text', ['required' => true]) ?>
                <?= render_input('name_arabic', 'allowance_type_name_arabic') ?>
                <?= render_textarea('description', 'allowance_description', '', ['rows' => 3]) ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('sort_order', 'sort_order', '0', 'number', ['min' => '0']) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active"><?= _l('is_active') ?></label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1"><?= _l('active') ?></option>
                                <option value="0"><?= _l('inactive') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Manage Assignments Modal -->
<div class="modal fade" id="assignments_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="assignmentModalTitle"><?= _l('assign_to_employee_types') ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assignment_allowance_id">

                <!-- Add Assignment Form -->
                <div class="panel panel-primary">
                    <div class="panel-heading"><?= _l('add_new_assignment') ?></div>
                    <div class="panel-body">
                        <?= form_open('#', ['id' => 'assignment-form']) ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?= _l('employee_type') ?></label>
                                    <select name="employee_type" id="assignment_employee_type" class="form-control" required>
                                        <option value="">-- <?= _l('select') ?> --</option>
                                        <option value="staff_type"><?= _l('staff_type') ?></option>
                                        <option value="company_type"><?= _l('company_type') ?></option>
                                        <option value="profession_type"><?= _l('profession_type') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label id="type_label"><?= _l('select_type') ?></label>
                                    <select name="employee_type_id" id="assignment_employee_type_id" class="form-control selectpicker" data-live-search="true" required>
                                        <option value="">-- <?= _l('select') ?> --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <?= render_input('default_amount', 'default_amount', '', 'number', ['step' => '0.01', 'min' => '0']) ?>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="is_mandatory" id="assignment_is_mandatory" value="1">
                                    <label for="assignment_is_mandatory"><?= _l('is_mandatory') ?></label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success"><?= _l('add') ?></button>
                        <?= form_close() ?>
                    </div>
                </div>

                <!-- Existing Assignments List -->
                <div class="panel panel-default">
                    <div class="panel-heading"><?= _l('existing_assignments') ?></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="assignments_table">
                                <thead>
                                    <tr>
                                        <th><?= _l('employee_type') ?></th>
                                        <th><?= _l('type_name') ?></th>
                                        <th><?= _l('default_amount') ?></th>
                                        <th><?= _l('is_mandatory') ?></th>
                                        <th><?= _l('options') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="assignments_list">
                                    <tr>
                                        <td colspan="5" class="text-center"><?= _l('loading') ?>...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
// Staff types, company types and profession types data
var staffTypes = <?= json_encode($staff_types) ?>;
var companyTypes = <?= json_encode($company_types) ?>;
var professionTypes = <?= json_encode($profession_types) ?>;

// Open modal for new allowance
function openAllowanceModal() {
    $('#allowance-form')[0].reset();
    $('#allowance_id').val('');
    $('#allowanceModalTitle').text('<?= _l('add_new_allowance_type') ?>');
    $('#is_active').val('1');
}

// Edit allowance
function editAllowance(id) {
    $.get(admin_url + 'allowance_types/get/' + id, function(response) {
        var data = typeof response === 'string' ? JSON.parse(response) : response;

        $('#allowance_id').val(data.id);
        $('#allowance-form input[name="name"]').val(data.name);
        $('#allowance-form input[name="name_arabic"]').val(data.name_arabic);
        $('#allowance-form textarea[name="description"]').val(data.description);
        $('#allowance-form input[name="sort_order"]').val(data.sort_order);
        $('#allowance-form select[name="is_active"]').val(data.is_active);

        $('#allowanceModalTitle').text('<?= _l('edit_allowance_type') ?>');
        $('#allowance_type_modal').modal('show');
    });
}

// Delete allowance
function deleteAllowance(id, name) {
    if (confirm('<?= _l('confirm_delete') ?> "' + name + '"?')) {
        $.post(admin_url + 'allowance_types/delete/' + id, function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            alert_float(data.success ? 'success' : 'danger', data.message);
            if (data.success) {
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        });
    }
}

// Save allowance form
$('#allowance-form').on('submit', function(e) {
    e.preventDefault();

    $.post($(this).attr('action'), $(this).serialize(), function(response) {
        var data = typeof response === 'string' ? JSON.parse(response) : response;
        alert_float(data.success ? 'success' : 'danger', data.message);

        if (data.success) {
            $('#allowance_type_modal').modal('hide');
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
    });
});

// Manage assignments
function manageAssignments(allowanceId, allowanceName) {
    $('#assignment_allowance_id').val(allowanceId);
    $('#assignmentModalTitle').text('<?= _l('assign_to_employee_types') ?> - ' + allowanceName);
    $('#assignment-form')[0].reset();
    $('.selectpicker').selectpicker('val', '');
    loadAssignments(allowanceId);
    $('#assignments_modal').modal('show');
}

// Load assignments for an allowance
function loadAssignments(allowanceId) {
    $.get(admin_url + 'allowance_types/get_assignments/' + allowanceId, function(response) {
        var assignments = typeof response === 'string' ? JSON.parse(response) : response;
        var html = '';

        if (assignments.length > 0) {
            assignments.forEach(function(assignment) {
                var typeLabel = '';
                if (assignment.employee_type == 'staff_type') {
                    typeLabel = '<?= _l('staff_type') ?>';
                } else if (assignment.employee_type == 'company_type') {
                    typeLabel = '<?= _l('company_type') ?>';
                } else {
                    typeLabel = '<?= _l('profession_type') ?>';
                }
                var typeName = assignment.type_name + (assignment.type_name_arabic ? ' (' + assignment.type_name_arabic + ')' : '');
                var mandatory = assignment.is_mandatory == 1 ? '<span class="label label-danger"><?= _l('yes') ?></span>' : '<span class="label label-default"><?= _l('no') ?></span>';
                var defaultAmount = assignment.default_amount ? assignment.default_amount : '-';

                html += '<tr>';
                html += '<td>' + typeLabel + '</td>';
                html += '<td>' + typeName + '</td>';
                html += '<td>' + defaultAmount + '</td>';
                html += '<td>' + mandatory + '</td>';
                html += '<td><button class="btn btn-danger btn-sm" onclick="deleteAssignment(' + assignment.id + ')"><i class="fa fa-trash"></i></button></td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="5" class="text-center"><?= _l('no_records_found') ?></td></tr>';
        }

        $('#assignments_list').html(html);
    });
}

// Change employee type dropdown
$('#assignment_employee_type').on('change', function() {
    var type = $(this).val();
    var options = '<option value="">-- <?= _l('select') ?> --</option>';

    if (type == 'staff_type') {
        $('#type_label').text('<?= _l('staff_type') ?>');
        staffTypes.forEach(function(st) {
            options += '<option value="' + st.id + '">' + st.name + '</option>';
        });
    } else if (type == 'company_type') {
        $('#type_label').text('<?= _l('company_type') ?>');
        companyTypes.forEach(function(ct) {
            options += '<option value="' + ct.id + '">' + ct.name + (ct.name_arabic ? ' (' + ct.name_arabic + ')' : '') + '</option>';
        });
    } else if (type == 'profession_type') {
        $('#type_label').text('<?= _l('profession_type') ?>');
        professionTypes.forEach(function(pt) {
            options += '<option value="' + pt.id + '">' + pt.name + (pt.name_arabic ? ' (' + pt.name_arabic + ')' : '') + '</option>';
        });
    }

    $('#assignment_employee_type_id').html(options).selectpicker('refresh');
});

// Submit assignment form
$('#assignment-form').on('submit', function(e) {
    e.preventDefault();

    var allowanceId = $('#assignment_allowance_id').val();
    var formData = {
        allowance_type_id: allowanceId,
        employee_type: $('#assignment_employee_type').val(),
        employee_type_id: $('#assignment_employee_type_id').val(),
        default_amount: $('input[name="default_amount"]').val(),
        is_mandatory: $('#assignment_is_mandatory').is(':checked') ? 1 : 0
    };

    $.post(admin_url + 'allowance_types/save_assignment', formData, function(response) {
        var data = typeof response === 'string' ? JSON.parse(response) : response;
        alert_float(data.success ? 'success' : 'danger', data.message);

        if (data.success) {
            $('#assignment-form')[0].reset();
            $('.selectpicker').selectpicker('val', '');
            loadAssignments(allowanceId);
        }
    });
});

// Delete assignment
function deleteAssignment(id) {
    if (confirm('<?= _l('confirm_delete') ?>?')) {
        $.post(admin_url + 'allowance_types/delete_assignment/' + id, function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            alert_float(data.success ? 'success' : 'danger', data.message);

            if (data.success) {
                var allowanceId = $('#assignment_allowance_id').val();
                loadAssignments(allowanceId);
            }
        });
    }
}
</script>
</body>
</html>
