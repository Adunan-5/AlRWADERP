<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Pay Information Modal -->
<div id="payInfoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?= form_open('#', ['id' => 'payInfoForm']) ?>
            <div class="modal-header">
                <h4 class="modal-title" id="payModalTitle">Edit Pay Information</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="staff_id" id="pay_staff_id">
                <input type="hidden" name="month" id="pay_month">

                <!-- Employee Info (Read-only) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" id="pay_employee_name" readonly style="background-color: #f5f5f5;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee Number</label>
                            <input type="text" class="form-control" id="pay_employee_number" readonly style="background-color: #f5f5f5;">
                        </div>
                    </div>
                </div>

                <!-- Row 1: Date and Payout Type (Read-only) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Starting From Date</label>
                            <input type="text" name="start_date" class="form-control" readonly style="background-color: #f5f5f5;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Payout Type</label><br>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="payout_type" id="payout_monthly" value="monthly" checked disabled>
                                <label for="payout_monthly">Monthly</label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="payout_type" id="payout_hourly" value="hourly" disabled>
                                <label for="payout_hourly">Hourly</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Basic Pay and Overtime -->
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('basic_pay', 'Basic Pay Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_basic_pay']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_input('overtime_pay', 'Overtime Pay Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_overtime_pay']) ?>
                    </div>
                </div>

                <!-- Row 3: Food and General Allowance -->
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('food_allowance', 'Food Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_food_allowance']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_input('allowance', 'Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_allowance']) ?>
                    </div>
                </div>

                <!-- Row 4: FAT and Accommodation -->
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('fat_allowance', 'FAT Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_fat_allowance']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_input('accomodation_allowance', 'Accommodation Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_accomodation_allowance']) ?>
                    </div>
                </div>

                <!-- Row 5: MEWA -->
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('mewa', 'MEWA Amount', '', 'number', ['step'=>'0.01','min'=>'0', 'id'=>'pay_mewa']) ?>
                        <small class="text-success">✓ MEWA is an allowance (included in GOSI Other)</small>
                    </div>
                </div>

                <!-- Custom Allowances Section -->
                <div id="custom-allowances-section" style="display:none;">
                    <hr>
                    <h5>Custom Allowances <small class="text-muted">(Assigned to this employee)</small></h5>
                    <div id="custom-allowances-container"></div>
                </div>

                <!-- GOSI Information Section -->
                <hr>
                <h5>GOSI Information <small class="text-muted">(Can differ from actual pay amounts)</small></h5>
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('gosi_basic', 'GOSI Basic', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_input('gosi_housing_allowance', 'GOSI Housing Allowance', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="savePayInfo">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
