<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                            <?php echo isset($quotation) ? _l('edit_quotation') : _l('add_quotation'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open(isset($quotation) ? admin_url('equipments/quotation/edit/' . $quotation->id) : admin_url('equipments/quotation/add')); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quotation_number"><?php echo _l('quotation_number'); ?></label>
                                    <input type="text" class="form-control" name="quotation_number"
                                           value="<?php echo isset($quotation) ? $quotation->quotation_number : ''; ?>"
                                           placeholder="<?php echo _l('auto_generated'); ?>"
                                           <?php echo isset($quotation) ? 'readonly' : ''; ?>>
                                    <small class="text-muted"><?php echo _l('leave_empty_for_auto_generation'); ?></small>
                                </div>

                                <?php
                                $quotation_date = isset($quotation) ? _d($quotation->quotation_date) : date('Y-m-d');
                                echo render_date_input('quotation_date', _l('quotation_date'), $quotation_date, ['required' => true]);
                                ?>

                                <?php
                                $valid_until = isset($quotation) && $quotation->valid_until_date ? _d($quotation->valid_until_date) : '';
                                echo render_date_input('valid_until_date', _l('valid_until_date'), $valid_until);
                                ?>

                                <?php
                                $selected_rfq = isset($quotation) ? $quotation->rfq_id : (isset($selected_rfq) ? $selected_rfq : '');
                                $rfq_options = [];
                                foreach ($rfqs as $rfq) {
                                    $rfq_options[] = [
                                        'id' => $rfq['id'],
                                        'label' => $rfq['rfq_number'] . ($rfq['project_reference'] ? ' - ' . $rfq['project_reference'] : '')
                                    ];
                                }
                                echo render_select('rfq_id', $rfq_options, ['id', 'label'], _l('rfq'), $selected_rfq, ['required' => true], [], '', '', isset($quotation));
                                ?>

                                <?php
                                $selected_supplier = isset($quotation) ? $quotation->supplier_id : '';
                                echo render_select('supplier_id', $suppliers, ['id', 'name'], _l('supplier'), $selected_supplier, ['required' => true]);
                                ?>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?php echo _l('status'); ?></label>
                                    <select name="status" class="form-control selectpicker" required>
                                        <option value="draft" <?php echo isset($quotation) && $quotation->status == 'draft' ? 'selected' : ''; ?>><?php echo _l('quotation_status_draft'); ?></option>
                                        <option value="submitted" <?php echo isset($quotation) && $quotation->status == 'submitted' ? 'selected' : ''; ?>><?php echo _l('quotation_status_submitted'); ?></option>
                                        <option value="under_review" <?php echo isset($quotation) && $quotation->status == 'under_review' ? 'selected' : ''; ?>><?php echo _l('quotation_status_under_review'); ?></option>
                                        <option value="accepted" <?php echo isset($quotation) && $quotation->status == 'accepted' ? 'selected' : ''; ?>><?php echo _l('quotation_status_accepted'); ?></option>
                                        <option value="rejected" <?php echo isset($quotation) && $quotation->status == 'rejected' ? 'selected' : ''; ?>><?php echo _l('quotation_status_rejected'); ?></option>
                                        <option value="expired" <?php echo isset($quotation) && $quotation->status == 'expired' ? 'selected' : ''; ?>><?php echo _l('quotation_status_expired'); ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="currency"><?php echo _l('currency'); ?></label>
                                    <select name="currency" class="form-control selectpicker" required>
                                        <option value="SAR" <?php echo isset($quotation) && $quotation->currency == 'SAR' ? 'selected' : 'selected'; ?>>SAR (<?php echo _l('saudi_riyal'); ?>)</option>
                                        <option value="USD" <?php echo isset($quotation) && $quotation->currency == 'USD' ? 'selected' : ''; ?>>USD (<?php echo _l('us_dollar'); ?>)</option>
                                        <option value="EUR" <?php echo isset($quotation) && $quotation->currency == 'EUR' ? 'selected' : ''; ?>>EUR (<?php echo _l('euro'); ?>)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="tax_percentage"><?php echo _l('tax_percentage'); ?></label>
                                    <input type="number" class="form-control" name="tax_percentage"
                                           value="<?php echo isset($quotation) ? $quotation->tax_percentage : '15.00'; ?>"
                                           min="0" max="100" step="0.01">
                                    <small class="text-muted"><?php echo _l('vat_15_percent_default'); ?></small>
                                </div>

                                <?php if (!isset($quotation)) { ?>
                                <div class="form-group">
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="copy_rfq_items" id="copy_rfq_items" value="1" checked>
                                        <label for="copy_rfq_items">
                                            <?php echo _l('copy_rfq_items_to_quotation'); ?>
                                        </label>
                                    </div>
                                    <small class="text-muted"><?php echo _l('copy_rfq_items_help'); ?></small>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="payment_terms"><?php echo _l('payment_terms'); ?></label>
                                    <textarea class="form-control" name="payment_terms" rows="3"><?php echo isset($quotation) ? $quotation->payment_terms : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="delivery_terms"><?php echo _l('delivery_terms'); ?></label>
                                    <textarea class="form-control" name="delivery_terms" rows="3"><?php echo isset($quotation) ? $quotation->delivery_terms : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes"><?php echo _l('notes'); ?></label>
                                    <textarea class="form-control" name="notes" rows="3"><?php echo isset($quotation) ? $quotation->notes : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                            <a href="<?php echo admin_url('equipments/quotation'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
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
        quotation_date: 'required',
        rfq_id: 'required',
        supplier_id: 'required',
        status: 'required',
        currency: 'required'
    });
});
</script>
