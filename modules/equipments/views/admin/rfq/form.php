<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                            <?php echo isset($rfq) ? _l('edit_rfq') : _l('add_rfq'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open(isset($rfq) ? admin_url('equipments/rfq/edit/' . $rfq->id) : admin_url('equipments/rfq/add')); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rfq_number"><?php echo _l('rfq_number'); ?></label>
                                    <input type="text" class="form-control" name="rfq_number"
                                           value="<?php echo isset($rfq) ? $rfq->rfq_number : ''; ?>"
                                           placeholder="<?php echo _l('auto_generated'); ?>"
                                           <?php echo isset($rfq) ? 'readonly' : ''; ?>>
                                    <small class="text-muted"><?php echo _l('leave_empty_for_auto_generation'); ?></small>
                                </div>

                                <?php
                                $rfq_date = isset($rfq) ? _d($rfq->rfq_date) : date('Y-m-d');
                                echo render_date_input('rfq_date', _l('rfq_date'), $rfq_date, ['required' => true]);
                                ?>

                                <?php
                                $required_by = isset($rfq) && $rfq->required_by_date ? _d($rfq->required_by_date) : '';
                                echo render_date_input('required_by_date', _l('required_by_date'), $required_by);
                                ?>

                                <?php
                                $expected_start = isset($rfq) && $rfq->expected_start_date ? _d($rfq->expected_start_date) : '';
                                echo render_date_input('expected_start_date', _l('expected_start_date'), $expected_start);
                                ?>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_reference"><?php echo _l('project_reference'); ?></label>
                                    <input type="text" class="form-control" name="project_reference"
                                           value="<?php echo isset($rfq) ? $rfq->project_reference : ''; ?>">
                                </div>

                                <?php
                                $selected_client = isset($rfq) ? $rfq->client_id : '';
                                echo render_select('client_id', $clients, ['userid', 'company'], _l('client') . ' (' . _l('optional') . ')', $selected_client);
                                ?>

                                <div class="form-group">
                                    <label for="expected_duration_months"><?php echo _l('expected_duration_months'); ?></label>
                                    <input type="number" class="form-control" name="expected_duration_months"
                                           value="<?php echo isset($rfq) ? $rfq->expected_duration_months : ''; ?>"
                                           min="1" step="1">
                                </div>

                                <div class="form-group">
                                    <label for="status"><?php echo _l('status'); ?></label>
                                    <select name="status" class="form-control selectpicker" required>
                                        <option value="draft" <?php echo isset($rfq) && $rfq->status == 'draft' ? 'selected' : ''; ?>><?php echo _l('rfq_status_draft'); ?></option>
                                        <option value="sent" <?php echo isset($rfq) && $rfq->status == 'sent' ? 'selected' : ''; ?>><?php echo _l('rfq_status_sent'); ?></option>
                                        <option value="responses_received" <?php echo isset($rfq) && $rfq->status == 'responses_received' ? 'selected' : ''; ?>><?php echo _l('rfq_status_responses_received'); ?></option>
                                        <option value="evaluated" <?php echo isset($rfq) && $rfq->status == 'evaluated' ? 'selected' : ''; ?>><?php echo _l('rfq_status_evaluated'); ?></option>
                                        <option value="closed" <?php echo isset($rfq) && $rfq->status == 'closed' ? 'selected' : ''; ?>><?php echo _l('rfq_status_closed'); ?></option>
                                        <option value="cancelled" <?php echo isset($rfq) && $rfq->status == 'cancelled' ? 'selected' : ''; ?>><?php echo _l('rfq_status_cancelled'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="terms_conditions"><?php echo _l('terms_and_conditions'); ?></label>
                                    <textarea class="form-control" name="terms_conditions" rows="4"><?php echo isset($rfq) ? $rfq->terms_conditions : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes"><?php echo _l('notes'); ?></label>
                                    <textarea class="form-control" name="notes" rows="4"><?php echo isset($rfq) ? $rfq->notes : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                            <a href="<?php echo admin_url('equipments/rfq'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    'use strict';

    appValidateForm($('form'), {
        rfq_date: 'required',
        status: 'required'
    });
});
</script>
