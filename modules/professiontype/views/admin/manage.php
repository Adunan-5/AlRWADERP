<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('professiontype', '', 'create')): ?>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#professionTypeModal">
                        <i class="fa-regular fa-plus tw-mr-1"></i> <?= _l('add_new_profession_type') ?>
                    </button>
                <?php endif; ?>
                <h4 class="bold"><?= _l('profession_types') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= _l('profession_type_name') ?></th>
                                    <th><?= _l('profession_type_name_arabic') ?></th>
                                    <th><?= _l('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach ($this->professiontype_model->get() as $type): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= $type['name'] ?></td>
                                        <td><?= $type['name_arabic'] ?></td>
                                        <td>
                                            <?php if (has_permission('professiontype', '', 'edit')): ?>
                                                <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#professionTypeModal" onclick="editProfessionType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($type['name_arabic'], ENT_QUOTES) ?>')">
                                                    <?= _l('edit') ?>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (has_permission('professiontype', '', 'delete')): ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteProfessionType(event, <?= $type['id'] ?>)">
                                                    <?= _l('delete') ?>
                                                </button>
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
    <div class="modal fade" id="professionTypeModal" tabindex="-1" role="dialog" aria-labelledby="professionTypeModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="professionTypeModalLabel"><?= _l('add_new_profession_type') ?></h4>
                </div>
                <?php echo form_open(admin_url('professiontype/save')); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="profession_type_id">
                    <div class="form-group">
                        <label for="name"><?= _l('profession_type_name') ?></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="name_arabic"><?= _l('profession_type_name_arabic') ?></label>
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
function editProfessionType(id, name, name_arabic) {
    $('#professionTypeModalLabel').text('<?= _l('edit') ?> <?= _l('profession_type') ?>');
    $('#profession_type_id').val(id);
    $('#name').val(name);
    $('#name_arabic').val(name_arabic);
}
  function resetProfessionTypeModal() {
    // Change modal title to Add New
    $('#professionTypeModalLabel').text('<?= _l('add_new_profession_type') ?>');

    // Clear the fields
    $('#profession_type_id').val('');
    $('#name').val('');
    $('#name_arabic').val('');
  }

  // When modal closes, reset it
  $('#professionTypeModal').on('hidden.bs.modal', function () {
    resetProfessionTypeModal();
  });

function deleteProfessionType(event, id) {
    event.preventDefault();
    Swal.fire({
        title: "<?= _l('are_you_sure') ?>",
        text: "<?= _l('confirm_delete_profession_type') ?>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "<?= _l('yes_delete') ?>",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= admin_url('professiontype/delete/') ?>" + id;
        }
    });
}
</script>