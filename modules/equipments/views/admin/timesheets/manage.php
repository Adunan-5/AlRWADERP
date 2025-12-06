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
                            <?php if (has_permission('equipment_timesheets', '', 'create')) { ?>
                                <a href="#" class="btn btn-primary">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('add_new_timesheet'); ?>
                                </a>
                                <a href="#" class="btn btn-success">
                                    <i class="fa fa-file-excel-o tw-mr-1"></i>
                                    <?php echo _l('import_from_excel'); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="alert alert-success">
                            <i class="fa fa-calendar-check-o"></i>
                            <strong>⭐ <?php echo _l('equipment_timesheets'); ?> Module</strong> - Priority Feature!
                            <p>This is the CORE billing feature matching your screenshot format.</p>
                            <p><strong>Database Tables:</strong></p>
                            <ul class="tw-mb-0">
                                <li><?php echo db_prefix(); ?>equipment_timesheet (master)</li>
                                <li><?php echo db_prefix(); ?>equipment_timesheet_details (daily hours)</li>
                            </ul>
                            <p class="tw-mt-2"><strong>Status:</strong> ✅ Controller created | ⏳ View under construction | ⏳ Model pending</p>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">📅 Equipment Timesheets (Coming Soon)</h4>
                            </div>
                            <div class="panel-body">
                                <p><strong>This will match your screenshot format exactly:</strong></p>
                                <ul>
                                    <li>✓ Monthly timesheet grid (Day 1-30/31)</li>
                                    <li>✓ Equipment description (e.g., "BUS 30 SEATER W/ DRIVER")</li>
                                    <li>✓ Driver name, Plate number, Location</li>
                                    <li>✓ Daily hours entry (Actual Hours column)</li>
                                    <li>✓ Total hours calculation</li>
                                    <li>✓ Rate per hour</li>
                                    <li>✓ Deduction support</li>
                                    <li>✓ Payable amount calculation</li>
                                    <li>✓ 3-tier approval workflow:
                                        <ul>
                                            <li>Prepared by (Business Coordinator)</li>
                                            <li>Verified by (Marketing Manager)</li>
                                            <li>Approved by (General Manager)</li>
                                        </ul>
                                    </li>
                                    <li>✓ Excel import/export</li>
                                    <li>✓ Invoice generation</li>
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
