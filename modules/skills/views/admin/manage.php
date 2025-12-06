<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#skillModal"> <i class="fa-regular fa-plus tw-mr-1"></i>
                    <?= _l('add_new_skill') ?>
                </button>
                <h4 class="bold"><?= _l('skills') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= _l('skill_name') ?></th>
                                    <th><?= _l('skill_name_arabic') ?></th>
                                    <th><?= _l('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach ($this->skills_module_model->get() as $skill): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= $skill['name'] ?></td>
                                        <td><?= $skill['name_arabic'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#skillModal" onclick="editSkill(<?= $skill['id'] ?>, '<?= addslashes($skill['name']) ?>', '<?= addslashes($skill['name_arabic']) ?>')">
                                                <?= _l('edit') ?>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSkill(event, <?= $skill['id'] ?>)">
                                                <?= _l('delete') ?>
                                            </button>
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
    <div class="modal fade" id="skillModal" tabindex="-1" role="dialog" aria-labelledby="skillModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="skillModalLabel"><?= _l('add_new_skill') ?></h4>
                </div>
                <?php echo form_open(admin_url('skills/save')); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="skill_id">
                    <div class="form-group">
                        <label for="name"><?= _l('skill_name') ?></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="name_arabic"><?= _l('skill_name_arabic') ?></label>
                        <input type="text" class="form-control" name="name_arabic" id="name_arabic">
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
function editSkill(id, name, name_arabic) {
    $('#skillModalLabel').text('<?= _l('edit') ?> <?= _l('skill') ?>');
    $('#skill_id').val(id);
    $('#name').val(name);
    $('#name_arabic').val(name_arabic);
}
function resetModal() {
    $('#skillModalLabel').text('<?= _l('add_new_skill') ?>');
    $('#skill_id').val('');
    $('#name').val('');
    $('#name_arabic').val('');
}
$('#skillModal').on('hidden.bs.modal', function () {
    resetModal();
});

function deleteSkill(event, id) {
    event.preventDefault();
    Swal.fire({
        title: "<?= _l('are_you_sure') ?>",
        text: "<?= _l('confirm_delete_skill') ?>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "<?= _l('yes_delete') ?>",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= admin_url('skills/delete/') ?>" + id;
        }
    });
}
</script>