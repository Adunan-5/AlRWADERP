<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('Timesheet Content'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <p><strong>Extracted Text:</strong></p>
                        <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto;">
                            <?php echo htmlentities($extracted_text); ?>
                        </pre>
                        <a href="<?php echo admin_url('timesheet/upload_pdf'); ?>" class="btn btn-default">Upload Another</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>