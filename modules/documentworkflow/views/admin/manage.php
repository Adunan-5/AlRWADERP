<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .table tbody .btn-info,
.table tbody .btn-danger {
    color: #fff !important;
}
.mbottom20 {
    margin-bottom: 20px !important;
}
</style>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="pull-right mbottom10">
          <?php if (staff_can('create', 'documentworkflow')): ?>
              <a href="<?= admin_url('documentworkflow/editor') ?>" class="btn btn-primary"><?= _l('add_new_document_template') ?></a>
          <?php endif; ?>
        </div>
        <h4 class="bold"><?= _l('document_templates') ?></h4>
        <div class="panel_s">
          <div class="panel-body">
            <!-- Letterhead upload quick form -->
            <h5><?= _l('upload_letterhead') ?></h5>
            <?php echo form_open_multipart(admin_url('documentworkflow/upload_letterhead')); ?>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Letterhead name (e.g. Mohtarifeen)" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <input type="file" name="letterhead_file" accept=".pdf,.png,.jpg,.jpeg" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <button class="btn btn-success" type="submit">Upload</button>
                </div>
              </div>
            <?php echo form_close(); ?>

            <hr />

            <!-- Documents Table -->
             <h5><?= _l('manage_documents') ?></h5>
            <table class="table dt-table" data-order-col="0" data-order-type="asc">
              <thead>
                <tr>
                  <th>#</th>
                  <th><?= _l('document_title') ?></th>
                  <th><?= _l('document_type') ?></th>
                  <th><?= _l('letterheads') ?></th>
                  <th><?= _l('actions') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; foreach ($documents as $d): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= html_escape($d['title']) ?></td>
                  <td><?= html_escape($d['type']) ?></td>
                  <td>
                    <?php
                      $lh = null;
                      foreach ($letterheads as $l) { if ($l['id'] == $d['letterhead_id']) { $lh = $l; break; } }
                      echo $lh ? html_escape($lh['name']) : '-';
                    ?>
                  </td>
                  <td>
                    <?php if (staff_can('edit', 'documentworkflow')): ?>
                      <a href="<?= admin_url('documentworkflow/editor/' . $d['id']) ?>" class="btn btn-sm btn-default"><i class="fa fa-edit"></i> <?= _l('edit') ?></a>
                    <?php endif; ?>

                    <?php if (staff_can('view', 'documentworkflow')): ?>
                      <a href="<?= admin_url('documentworkflow/download/' . $d['id']) ?>" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-download"></i> <?= _l('download') ?></a>
                    <?php endif; ?>
                    
                    <?php if (staff_can('delete', 'documentworkflow')): ?>
                      <a href="<?= admin_url('documentworkflow/delete/' . $d['id']) ?>" class="btn btn-sm btn-danger delete-document"><i class="fa fa-trash"></i> <?= _l('delete') ?></a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <hr />

            <!-- Document Types Management -->
            <div class="pull-right mbottom20">
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#typeModal"><i class="fa fa-plus"></i> <?= _l('add_new_document_type') ?></a>
            </div>
            <h5><?= _l('manage_document_types') ?></h5>
            <table class="table dt-table" data-order-col="0" data-order-type="asc">
              <thead>
                <tr>
                  <th>#</th>
                  <th><?= _l('type_key_name') ?></th>
                  <th><?= _l('type_label') ?></th>
                  <th><?= _l('actions') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; foreach ($types as $t): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= html_escape($t['key_name']) ?></td>
                  <td><?= html_escape($t['label']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-default edit-type" data-id="<?= $t['id'] ?>" data-key-name="<?= html_escape($t['key_name']) ?>" data-label="<?= html_escape($t['label']) ?>" data-toggle="modal" data-target="#typeModal">
                      <i class="fa fa-edit"></i> <?= _l('edit') ?>
                    </button>
                    <a href="<?= admin_url('documentworkflow/delete_type/' . $t['id']) ?>" class="btn btn-sm btn-danger delete-type"><i class="fa fa-trash"></i> <?= _l('delete') ?></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Modal: Add/Edit Document Type -->
            <div class="modal fade" id="typeModal" tabindex="-1" role="dialog" aria-labelledby="typeModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <?php echo form_open(admin_url('documentworkflow/save_type')); ?>
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="typeModalLabel"><?= _l('add_new_document_type') ?></h4>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" id="type_id">
                    <div class="form-group">
                      <label><?= _l('type_key_name') ?></label>
                      <input type="text" name="key_name" id="type_key_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                      <label><?= _l('type_label') ?></label>
                      <input type="text" name="label" id="type_label" class="form-control" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel') ?></button>
                    <?php if (staff_can('create', 'documentworkflow') || staff_can('edit', 'documentworkflow')): ?>
                        <button type="submit" class="btn btn-primary"><?= _l('save') ?></button>
                    <?php endif; ?>
                  </div>
                  <?php echo form_close(); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
$('.delete-document').on('click', function(e){
  e.preventDefault();
  var url = $(this).attr('href');
  Swal.fire({
    title: "<?= _l('are_you_sure') ?>",
    text: "<?= _l('confirm_delete_document') ?>",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "<?= _l('yes_delete') ?>",
    cancelButtonText: "<?= _l('cancel') ?>"
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
});

$('.delete-type').on('click', function(e){
  e.preventDefault();
  var url = $(this).attr('href');
  Swal.fire({
    title: "<?= _l('are_you_sure') ?>",
    text: "<?= _l('confirm_delete_document_type') ?>",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "<?= _l('yes_delete') ?>",
    cancelButtonText: "<?= _l('cancel') ?>"
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
});

$('.edit-type').on('click', function(){
  var id = $(this).data('id');
  var key_name = $(this).data('key-name');
  var label = $(this).data('label');
  $('#type_id').val(id);
  $('#type_key_name').val(key_name);
  $('#type_label').val(label);
  $('#typeModalLabel').text('<?= _l('edit') ?> <?= _l('document_type') ?>');
});

$('[data-target="#typeModal"]').on('click', function(){
  $('#type_id').val('');
  $('#type_key_name').val('');
  $('#type_label').val('');
  $('#typeModalLabel').text('<?= _l('add_new_document_type') ?>');
});
</script>