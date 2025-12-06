<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('companytype', '', 'create')): ?>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#companyTypeModal">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?= _l('add_new_company_type') ?>
                    </button>
                <?php endif; ?>
                <h4 class="bold"><?= _l('company_types') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= _l('company_type_name') ?></th>
                                    <th><?= _l('company_type_name_arabic') ?></th>
                                    <th><?= _l('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach ($this->companytype_model->get() as $type): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= $type['name'] ?></td>
                                        <td><?= $type['name_arabic'] ?></td>
                                        <td>
                                            <?php if (has_permission('companytype', '', 'edit')): ?>
                                                <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#companyTypeModal"
                                                    onclick="editCompanyType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($type['name_arabic'], ENT_QUOTES) ?>')">
                                                    <?= _l('edit') ?>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (has_permission('companytype', '', 'delete')): ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCompanyType(event, <?= $type['id'] ?>)">
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
    <div class="modal fade" id="companyTypeModal" tabindex="-1" role="dialog" aria-labelledby="companyTypeModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="companyTypeModalLabel"><?= _l('add_new_company_type') ?></h4>
                </div>
                <?php echo form_open(admin_url('companytype/save')); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="company_type_id">
                    <div class="form-group">
                        <label for="name"><?= _l('company_type_name') ?></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="name_arabic"><?= _l('company_type_name_arabic') ?></label>
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
function editCompanyType(id, name, name_arabic) {
    $('#companyTypeModalLabel').text('<?= _l('edit') ?> <?= _l('company_type') ?>');
    $('#company_type_id').val(id);
    $('#name').val(name);
    $('#name_arabic').val(name_arabic);
}
  function resetCompanyTypeModal() {
    // Change modal title to Add New
    $('#companyTypeModalLabel').text('<?= _l('add_new_company_type') ?>');

    // Clear the fields
    $('#company_type_id').val('');
    $('#name').val('');
    $('#name_arabic').val('');
  }

  // When modal closes, reset it
  $('#companyTypeModal').on('hidden.bs.modal', function () {
    resetCompanyTypeModal();
  });

function deleteCompanyType(event, id) {
    event.preventDefault();
    Swal.fire({
        title: "<?= _l('are_you_sure') ?>",
        text: "<?= _l('confirm_delete_company_type') ?>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "<?= _l('yes_delete') ?>",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= admin_url('companytype/delete/') ?>" + id;
        }
    });
}
</script>