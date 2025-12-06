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
          <a href="<?= admin_url('dw_template/editor') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?= _l('add_new_dw_document_template') ?></a>
        </div>
        <h4 class="bold"><?= _l('dw_document_templates') ?></h4>
        <div class="panel_s">
          <div class="panel-body">
            <!-- Letterhead upload quick form -->
            <h5><?= _l('upload_letterhead') ?></h5>
            <?php echo form_open_multipart(admin_url('dw_template/upload_letterhead')); ?>
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
                  <td><?= html_escape($d['type_label']) ?></td>
                  <td>
                    <?php
                      $lh = null;
                      foreach ($letterheads as $l) { if ($l['id'] == $d['letterhead_id']) { $lh = $l; break; } }
                      echo $lh ? html_escape($lh['name']) : '-';
                    ?>
                  </td>
                  <td>
                    <a href="<?= admin_url('dw_template/editor/' . $d['id']) ?>" class="btn btn-sm btn-default"><i class="fa fa-edit"></i> <?= _l('edit') ?></a>
                    <a href="<?= admin_url('dw_template/download/' . $d['id']) ?>" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-download"></i> <?= _l('download') ?></a>
                    <a href="<?= admin_url('dw_template/delete/' . $d['id']) ?>" class="btn btn-sm btn-danger delete-document"><i class="fa fa-trash"></i> <?= _l('delete') ?></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <hr />

            <!-- Document Types Management -->
            <div class="pull-right mbottom20">
              <a href="#" class="btn btn-primary add-type-btn" data-toggle="modal" data-target="#typeModal"><i class="fa fa-plus"></i> <?= _l('add_new_dw_document_type') ?></a>
            </div>
            <h5><?= _l('manage_dw_document_types') ?></h5>
            <table class="table dt-table" data-order-col="0" data-order-type="asc">
              <thead>
                <tr>
                  <th>#</th>
                  <th><?= _l('type_key_name') ?></th>
                  <th><?= _l('type_label') ?></th>
                  <th><?= _l('default_letterhead') ?></th>
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
                    <?php
                      $lh = null;
                      foreach ($letterheads as $l) { if ($l['id'] == $t['default_letterhead_id']) { $lh = $l; break; } }
                      echo $lh ? html_escape($lh['name']) : '-';
                    ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-default edit-type" data-id="<?= $t['id'] ?>" data-key-name="<?= html_escape($t['key_name']) ?>" data-label="<?= html_escape($t['label']) ?>" data-template="<?= htmlspecialchars($t['template_content'], ENT_QUOTES) ?>" data-letterhead="<?= $t['default_letterhead_id'] ?>" data-toggle="modal" data-target="#typeModal">
                      <i class="fa fa-edit"></i> <?= _l('edit') ?>
                    </button>
                    <a href="<?= admin_url('dw_template/delete_type/' . $t['id']) ?>" class="btn btn-sm btn-danger delete-type"><i class="fa fa-trash"></i> <?= _l('delete') ?></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Modal: Add/Edit Document Type -->
            <div class="modal fade" id="typeModal" tabindex="-1" role="dialog" aria-labelledby="typeModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <?php echo form_open(admin_url('dw_template/save_type')); ?>
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="typeModalLabel"><?= _l('add_new_dw_document_type') ?></h4>
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
                    <div class="form-group">
                      <label><?= _l('default_letterhead') ?></label>
                      <select name="default_letterhead_id" id="type_letterhead" class="form-control">
                        <option value=""><?= _l('no_letterheads_found') ?></option>
                        <?php foreach ($letterheads as $l): ?>
                          <option value="<?= $l['id'] ?>" data-file="<?= base_url($l['file']) ?>"><?= html_escape($l['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label><?= _l('template_content') ?></label>
                      <textarea id="type_template_content" name="template_content" rows="10" class="form-control"></textarea>
                      <small class="text-muted">Define template with placeholders like {PLACE}, {DATE}, {NAME}. Use TinyMCE for formatting (e.g., right-align date, center text).</small>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= _l('save') ?></button>
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
<script src="<?= base_url('assets/plugins/tinymce/tinymce.min.js'); ?>"></script>
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

var typeEditorInitialized = false;
var editTypeData = {}; // Store edit data temporarily

function initTypeEditor(initialBgUrl) {
  if (typeEditorInitialized) {
    var ed = tinymce.get('type_template_content');
    if (ed && initialBgUrl) {
      ed.getBody().style.backgroundImage = "url('" + initialBgUrl + "')";
      ed.getBody().style.backgroundSize = "cover";
      ed.getBody().style.backgroundRepeat = "no-repeat";
    }
    return;
  }
  tinymce.init({
    selector: '#type_template_content',
    height: 300,
    menubar: true,
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code insertdatetime media table directionality',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist outdent indent | table | removeformat | code',
    content_style: "body{font-family: Arial, Helvetica, sans-serif; padding: 150px 50px 60px 50px;}",
    setup: function(editor) {
      editor.on('init', function() {
        if (initialBgUrl) {
          editor.getBody().style.backgroundImage = "url('" + initialBgUrl + "')";
          editor.getBody().style.backgroundSize = "cover";
          editor.getBody().style.backgroundRepeat = "no-repeat";
        }
      });
    }
  });
  typeEditorInitialized = true;
}

// Handle modal show event to populate fields after DOM is ready
$('#typeModal').on('show.bs.modal', function (e) {
  var button = $(e.relatedTarget); // Button that triggered the modal
  var isEdit = button.hasClass('edit-type');
  if (isEdit) {
    var id = button.data('id');
    var key_name = button.data('key-name');
    var label = button.data('label');
    var template = button.data('template');
    var letterhead = button.data('letterhead');
    editTypeData = { id: id, key_name: key_name, label: label, template: template, letterhead: letterhead };
    $('#type_id').val(id);
    $('#type_key_name').val(key_name);
    $('#type_label').val(label);
    $('#type_letterhead').val(letterhead);
    $('#typeModalLabel').text('<?= _l('edit') ?> <?= _l('dw_document_type') ?>');
    // Initialize editor first
    var letterhead_file = $('#type_letterhead option[value="' + letterhead + '"]').data('file') || '';
    initTypeEditor(letterhead_file);
    // Set content after a short delay to ensure editor is ready
    setTimeout(function() {
      var ed = tinymce.get('type_template_content');
      if (ed) {
        ed.setContent(template || '');
      }
    }, 500);
  } else {
    // Add new
    $('#type_id').val('');
    $('#type_key_name').val('');
    $('#type_label').val('');
    $('#type_letterhead').val('');
    editTypeData = {};
    $('#typeModalLabel').text('<?= _l('add_new_dw_document_type') ?>');
    initTypeEditor('');
    setTimeout(function() {
      var ed = tinymce.get('type_template_content');
      if (ed) {
        ed.setContent('');
      }
    }, 500);
  }
});

$('#type_letterhead').on('change', function(){
  var selectedFile = $(this).find(':selected').data('file') || '';
  var ed = tinymce.get('type_template_content');
  if (ed) {
    if (selectedFile) {
      ed.getBody().style.backgroundImage = "url('" + selectedFile + "')";
      ed.getBody().style.backgroundSize = "cover";
      ed.getBody().style.backgroundRepeat = "no-repeat";
    } else {
      ed.getBody().style.backgroundImage = '';
    }
  }
});
</script>