<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('employeetype', '', 'create')): ?>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#employeeTypeModal">
                        <i class="fa-regular fa-plus tw-mr-1"></i> <?= _l('add_new_employee_type') ?>
                    </button>
                <?php endif; ?>
                <h4 class="bold"><?= _l('employee_types') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= _l('employee_type_name') ?></th>
                                    <th><?= _l('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach ($this->employeetype_model->get() as $type): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= $type['name'] ?></td>
                                        <td>
                                            <?php if (!in_array($type['id'], [1, 2, 3])): ?>
                                                <?php if (has_permission('employeetype', '', 'edit')): ?>
                                                    <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#employeeTypeModal" onclick="editEmployeeType(<?= $type['id'] ?>, '<?= addslashes($type['name']) ?>')">
                                                        <?= _l('edit') ?>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (has_permission('employeetype', '', 'delete')): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteEmployeeType(event, <?= $type['id'] ?>)">
                                                        <?= _l('delete') ?>
                                                    </button>
                                                <?php endif; ?>

                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit -->
    <div class="modal fade" id="employeeTypeModal" tabindex="-1" role="dialog" aria-labelledby="employeeTypeModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="employeeTypeModalLabel"><?= _l('add_new_employee_type') ?></h4>
                </div>
                <?php echo form_open(admin_url('employeetype/save')); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="employee_type_id">
                    <div class="form-group">
                        <label for="name"><?= _l('employee_type_name') ?></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?= _l('save') ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
function editEmployeeType(id, name) {
    $('#employeeTypeModalLabel').text('<?= _l('edit') ?> <?= _l('employee_type') ?>');
    $('#employee_type_id').val(id);
    $('#name').val(name);
}
function resetModal() {
    $('#employeeTypeModalLabel').text('<?= _l('add_new_employee_type') ?>');
    $('#employee_type_id').val('');
    $('#name').val('');
}
$('#employeeTypeModal').on('hidden.bs.modal', function () {
    resetModal();
});

function deleteEmployeeType(event, id) {
    event.preventDefault();
    Swal.fire({
        title: "<?= _l('are_you_sure') ?>",
        text: "<?= _l('confirm_delete_employee_type') ?>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "<?= _l('yes_delete') ?>",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= admin_url('employeetype/delete/') ?>" + id;
        }
    });
}
</script>