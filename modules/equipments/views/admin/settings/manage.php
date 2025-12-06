<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <i class="fa fa-cog tw-mr-1"></i>
                            <?php echo _l('equipment_settings'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Settings Module</strong> - Coming Soon!
                            <p>Manage document types and system configurations.</p>
                            <p><strong>Status:</strong> ✅ Controller created | ⏳ View under construction</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">🔧 Equipment Document Types</h4>
                                    </div>
                                    <div class="panel-body">
                                        <p><strong>Table:</strong> <?php echo db_prefix(); ?>equipment_document_types</p>
                                        <p>Default types installed:</p>
                                        <ul>
                                            <li>Insurance (30-day reminder)</li>
                                            <li>MVPI - Motor Vehicle Periodic Inspection</li>
                                            <li>TAMM Paper</li>
                                            <li>Istimara - Vehicle Registration</li>
                                            <li>Operation Manual</li>
                                            <li>Warranty Certificate</li>
                                        </ul>
                                        <a href="#" class="btn btn-default btn-sm">Manage Document Types</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">👥 Operator Document Types</h4>
                                    </div>
                                    <div class="panel-body">
                                        <p><strong>Table:</strong> <?php echo db_prefix(); ?>operator_document_types</p>
                                        <p>Default types installed:</p>
                                        <ul>
                                            <li>Iqama - Residence Permit (30-day reminder)</li>
                                            <li>Muqueen - Profession</li>
                                            <li>Driving License</li>
                                            <li>Passport (60-day reminder)</li>
                                            <li>Medical Certificate (90-day reminder)</li>
                                            <li>Training Certificate</li>
                                            <li>Police Clearance</li>
                                        </ul>
                                        <a href="#" class="btn btn-default btn-sm">Manage Document Types</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">⚙️ Additional Settings (Future)</h4>
                            </div>
                            <div class="panel-body">
                                <ul>
                                    <li>Timesheet number prefix configuration</li>
                                    <li>Default hourly rates</li>
                                    <li>Email notification templates</li>
                                    <li>Document expiry reminder intervals</li>
                                    <li>Approval workflow roles</li>
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
