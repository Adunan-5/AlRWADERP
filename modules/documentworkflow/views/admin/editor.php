<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <h4 class="bold"><?= $title ?></h4>
        <div class="panel_s">
          <div class="panel-body">
            <?php echo form_open(admin_url('documentworkflow/save')); ?>
              <input type="hidden" name="id" id="doc_id" value="<?= isset($document) ? $document->id : '' ?>">
              
              <div class="form-group">
                <label><?= _l('document_title') ?></label>
                <input type="text" name="title" id="doc_title" class="form-control" value="<?= isset($document) ? html_escape($document->title) : '' ?>" required>
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label><?= _l('document_type') ?></label>
                  <select name="type" id="doc_type" class="form-control">
                    <option value=""><?= _l('select_document_type') ?></option>
                    <?php foreach($types as $t): ?>
                      <option value="<?= html_escape($t['key_name']) ?>" <?= isset($document) && $document->type == $t['key_name'] ? 'selected' : '' ?>><?= html_escape($t['label']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label><?= _l('select_letterhead') ?></label>
                  <select name="letterhead_id" id="doc_letterhead" class="form-control">
                    <option value=""><?= _l('no_letterheads_found') ?></option>
                    <?php foreach ($letterheads as $l): ?>
                      <option value="<?= $l['id'] ?>" data-file="<?= base_url($l['file']) ?>" <?= isset($document) && $document->letterhead_id == $l['id'] ? 'selected' : '' ?>><?= html_escape($l['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-muted">Choose letterhead — content preview will show it as background in editor</small>
                </div>
              </div>

              <div class="form-group">
                <label><?= _l('document_content') ?></label>
                <textarea id="doc_content" name="content" rows="12" class="form-control"><?= isset($document) ? $document->content : '' ?></textarea>
              </div>

              <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= _l('save') ?></button>
                <a href="<?= admin_url('documentworkflow') ?>" class="btn btn-default"><?= _l('cancel') ?></a>
              </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script src="<?= base_url('assets/plugins/tinymce/tinymce.min.js'); ?>"></script>
<script>
var editorInitialized = false;
function initEditor(initialBgUrl) {
  if (editorInitialized) {
    var ed = tinymce.get('doc_content');
    if (ed && initialBgUrl) {
      ed.getBody().style.backgroundImage = "url('" + initialBgUrl + "')";
      ed.getBody().style.backgroundSize = "cover";
      ed.getBody().style.backgroundRepeat = "no-repeat";
    }
    setCursorPosition(ed);
    return;
  }

  tinymce.init({
    selector: '#doc_content',
    height: 600,
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
        if (!editor.getContent()) {
          editor.setContent('<div style="padding-top: 150px;"></div>');
        }
        setCursorPosition(editor);
      });
    }
  });

  editorInitialized = true;
}

function setCursorPosition(editor) {
  if (editor) {
    editor.focus();
    editor.selection.select(editor.getBody(), true);
    editor.selection.collapse(true);
  }
}

initEditor('<?= isset($document) ? $document->letterhead_file_url : '' ?>');

$('#doc_letterhead').on('change', function(){
  var selectedFile = $(this).find(':selected').data('file') || '';
  var ed = tinymce.get('doc_content');
  if (ed) {
    if (selectedFile) {
      ed.getBody().style.backgroundImage = "url('" + selectedFile + "')";
      ed.getBody().style.backgroundSize = "cover";
      ed.getBody().style.backgroundRepeat = "no-repeat";
    } else {
      ed.getBody().style.backgroundImage = '';
    }
    setCursorPosition(ed);
  }
});
</script>