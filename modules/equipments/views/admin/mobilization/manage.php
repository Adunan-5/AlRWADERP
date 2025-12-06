<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <div class="_buttons tw-mb-2 sm:tw-mb-4">
                            <?php if (has_permission('equipment_mobilization', '', 'create')) { ?>
                                <a href="#" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_new_mobilization'); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="alert alert-info">
                            <i class="fa fa-location-arrow"></i>
                            <strong><?php echo _l('mobilization'); ?> Module</strong> - Awaiting confirmation from equipments division, for the rest of the work-flow.
                            <p>This page will manage equipment deployments to client sites.</p>
                            <p><strong>Database Table:</strong> <?php echo db_prefix(); ?>equipment_mobilization</p>
                            <p><strong>Status:</strong> ✅ Controller created | ⏳ View Pending | ⏳ Model pending</p>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">📍 Equipment Mobilization (Coming Soon)</h4>
                            </div>
                            <div class="panel-body">
                                <p>Features in development:</p>
                                <ul>
                                    <li>✓ Equipment deployment tracking</li>
                                    <li>✓ Client/project linkage</li>
                                    <li>✓ Mobilization/demobilization dates</li>
                                    <li>✓ Rate management (hourly/daily/monthly)</li>
                                    <li>✓ Operator assignment</li>
                                    <li>✓ Location tracking</li>
                                    <li>✓ Status workflow</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>