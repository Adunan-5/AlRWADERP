<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                            <?php echo isset($quotation) ? _l('edit_quotation') : _l('add_new_quotation'); ?>
                        </h4>
                        <hr>

                        <?php echo form_open(admin_url('equipments/quotations/' . (isset($quotation) ? 'edit/' . $quotation->id : 'add')), ['id' => 'quotation-form']); ?>

                        <div class="row">
                            <!-- Quotation Number -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quotation_number"><?php echo _l('quotation_number'); ?></label>
                                    <input type="text" class="form-control" name="quotation_number" id="quotation_number"
                                        value="<?php echo isset($quotation) ? $quotation->quotation_number : ''; ?>"
                                        placeholder="<?php echo _l('leave_empty_auto_generate'); ?>">
                                    <small class="text-muted"><?php echo _l('leave_empty_auto_generate'); ?></small>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <?php
                                $selected_status = isset($quotation) ? $quotation->status : 'draft';
                                $statuses = [
                                    ['value' => 'draft', 'label' => _l('quotation_status_draft')],
                                    ['value' => 'sent', 'label' => _l('quotation_status_sent')],
                                    ['value' => 'accepted', 'label' => _l('quotation_status_accepted')],
                                    ['value' => 'rejected', 'label' => _l('quotation_status_rejected')],
                                    ['value' => 'expired', 'label' => _l('quotation_status_expired')],
                                ];
                                echo render_select('status', $statuses, ['value', 'label'], _l('status'), $selected_status, ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Client -->
                            <div class="col-md-6">
                                <?php
                                $selected_client = isset($quotation) ? $quotation->client_id : '';
                                echo render_select('client_id', $clients, ['userid', 'company'], _l('client'), $selected_client, ['required' => true]);
                                ?>
                            </div>

                            <!-- Agreement (Optional) -->
                            <div class="col-md-6">
                                <?php
                                $selected_agreement = isset($quotation) ? $quotation->agreement_id : '';
                                $agreement_options = [['id' => '', 'agreement_number' => _l('none')]];
                                if (isset($client_agreements) && !empty($client_agreements)) {
                                    foreach ($client_agreements as $agreement) {
                                        $agr_id = is_object($agreement) ? $agreement->id : $agreement['id'];
                                        $agr_number = is_object($agreement) ? $agreement->agreement_number : $agreement['agreement_number'];
                                        $agreement_options[] = ['id' => $agr_id, 'agreement_number' => $agr_number];
                                    }
                                }
                                echo render_select('agreement_id', $agreement_options, ['id', 'agreement_number'], _l('agreement') . ' (' . _l('optional') . ')', $selected_agreement);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quotation Date -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quotation_date"><span class="text-danger">*</span> <?php echo _l('quotation_date'); ?></label>
                                    <input type="text" class="form-control datepicker" name="quotation_date" id="quotation_date"
                                        value="<?php echo isset($quotation) ? _d($quotation->quotation_date) : _d(date('Y-m-d')); ?>"
                                        required>
                                </div>
                            </div>

                            <!-- Validity Date -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="validity_date"><?php echo _l('validity_date'); ?></label>
                                    <input type="text" class="form-control datepicker" name="validity_date" id="validity_date"
                                        value="<?php echo isset($quotation) && $quotation->validity_date ? _d($quotation->validity_date) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Payment Terms -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_terms_days"><span class="text-danger">*</span> <?php echo _l('payment_terms'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="payment_terms_days" id="payment_terms_days"
                                            value="<?php echo isset($quotation) ? $quotation->payment_terms_days : 30; ?>"
                                            min="0" required>
                                        <span class="input-group-addon"><?php echo _l('days'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="col-md-6">
                                <?php
                                $currencies = $this->db->get(db_prefix() . 'currencies')->result_array();
                                $selected_currency = isset($quotation) ? $quotation->currency : get_base_currency()->name;
                                echo render_select('currency', $currencies, ['name', 'name'], _l('currency'), $selected_currency, ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="terms_conditions"><?php echo _l('terms_and_conditions'); ?></label>
                                    <textarea class="form-control" name="terms_conditions" id="terms_conditions" rows="3"><?php echo isset($quotation) ? $quotation->terms_conditions : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes"><?php echo _l('notes'); ?></label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3"><?php echo isset($quotation) ? $quotation->notes : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> <?php echo _l('submit'); ?>
                                </button>
                                <a href="<?php echo admin_url('equipments/quotations'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
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

    appValidateForm($('#quotation-form'), {
        client_id: 'required',
        quotation_date: 'required',
        payment_terms_days: 'required',
        currency: 'required',
        status: 'required'
    });
});
</script>
