<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons tw-mb-2 sm:tw-mb-4">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#document-type-modal" onclick="resetForm()">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?= _l('add_new'); ?> <?= _l('document_type'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>

                        <table class="table dt-table table-document-types" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?= _l('id'); ?></th>
                                    <th><?= _l('name'); ?></th>
                                    <th><?= _l('description'); ?></th>
                                    <th><?= _l('usage_count'); ?></th>
                                    <th><?= _l('created_at'); ?></th>
                                    <th><?= _l('created_by'); ?></th>
                                    <th><?= _l('modified_at'); ?></th>
                                    <th><?= _l('modified_by'); ?></th>
                                    <th><?= _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->document_types_model->get() as $type):
                                    // Calculate usage count
                                    $usage_count = $this->db->where('document_type_id', $type['id'])
                                                           ->count_all_results(db_prefix() . 'staff_files');
                                ?>
                                    <tr>
                                        <td><?= $type['id']; ?></td>
                                        <td><?= e($type['name']); ?></td>
                                        <td><?= e($type['description']); ?></td>
                                        <td><?= $usage_count; ?></td>
                                        <td><?= $type['created_at'] ? _dt($type['created_at']) : '-'; ?></td>
                                        <td><?= $type['created_by'] ? get_staff_full_name($type['created_by']) : '-'; ?></td>
                                        <td><?= $type['modified_at'] ? _dt($type['modified_at']) : '-'; ?></td>
                                        <td><?= $type['modified_by'] ? get_staff_full_name($type['modified_by']) : '-'; ?></td>
                                        <td>
                                            <div class="tw-flex tw-items-center tw-space-x-2">
                                                <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 edit-document-type"
                                                   data-id="<?= $type['id']; ?>"
                                                   data-name="<?= htmlspecialchars($type['name'], ENT_QUOTES); ?>"
                                                   data-description="<?= htmlspecialchars($type['description'], ENT_QUOTES); ?>">
                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                </a>

                                                <?php if ($type['is_system'] == 0): ?>
                                                    <?php if ($usage_count == 0): ?>
                                                        <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700" onclick="deleteDocumentType(<?= $type['id']; ?>); return false;">
                                                            <i class="fa-regular fa-trash-can fa-lg"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="tw-text-neutral-300" title="Cannot delete - in use">
                                                            <i class="fa-regular fa-trash-can fa-lg"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="tw-text-neutral-300" title="System type - cannot delete">
                                                        <i class="fa-regular fa-trash-can fa-lg"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="document-type-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="edit-title hide"><?= _l('edit'); ?> <?= _l('document_type'); ?></span>
                    <span class="add-title"><?= _l('add_new'); ?> <?= _l('document_type'); ?></span>
                </h4>
            </div>
            <?= form_open(admin_url('document_types/save')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" id="document-type-id">
                        <?= render_input('name', 'document_type_name', '', 'text', ['required' => true]); ?>
                        <?= render_textarea('description', 'description', '', ['rows' => 3]); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    // Edit document type
    $('body').on('click', '.edit-document-type', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');

        $('#document-type-id').val(id);
        $('input[name="name"]').val(name);
        $('textarea[name="description"]').val(description);

        $('.add-title').addClass('hide');
        $('.edit-title').removeClass('hide');

        $('#document-type-modal').modal('show');
    });

    // Reset modal when closed
    $('#document-type-modal').on('hidden.bs.modal', function () {
        resetForm();
    });
});

// Delete document type
function deleteDocumentType(id) {
    if (confirm('<?= _l('confirm_delete'); ?>')) {
        window.location.href = admin_url + 'document_types/delete/' + id;
    }
}

function resetForm() {
    $('#document-type-id').val('');
    $('input[name="name"]').val('');
    $('textarea[name="description"]').val('');
    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}
</script>
