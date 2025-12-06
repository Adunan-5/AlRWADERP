<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                            <?php echo isset($po) ? _l('edit_purchase_order') : _l('add_new_purchase_order'); ?>
                        </h4>
                        <hr>

                        <?php echo form_open(admin_url('equipments/purchase_orders/' . (isset($po) ? 'edit/' . $po->id : 'add')), ['id' => 'po-form']); ?>

                        <?php if (isset($source_quotation)) { ?>
                        <!-- Quotation Source Info -->
                        <div class="alert alert-info tw-mb-4">
                            <i class="fa fa-info-circle"></i>
                            <strong><?php echo _l('converting_from_quotation'); ?>:</strong>
                            <a href="<?php echo admin_url('equipments/quotation/view/' . $source_quotation->id); ?>" target="_blank">
                                <?php echo $source_quotation->quotation_number; ?>
                            </a>
                            (<?php echo _l('supplier'); ?>: <?php echo $source_quotation->supplier_name; ?>)
                        </div>
                        <!-- Hidden field to track quotation source -->
                        <input type="hidden" name="quotation_id" value="<?php echo $source_quotation->id; ?>">
                        <?php } ?>

                        <div class="row">
                            <!-- PO Number -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="po_number"><?php echo _l('po_number'); ?></label>
                                    <input type="text" class="form-control" name="po_number" id="po_number"
                                        value="<?php echo isset($po) ? $po->po_number : ''; ?>"
                                        placeholder="<?php echo _l('leave_empty_auto_generate'); ?>">
                                    <small class="text-muted"><?php echo _l('leave_empty_auto_generate'); ?></small>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <?php
                                $selected_status = isset($po) ? $po->status : 'draft';
                                $statuses = [
                                    ['value' => 'draft', 'label' => _l('po_status_draft')],
                                    ['value' => 'sent', 'label' => _l('po_status_sent')],
                                    ['value' => 'confirmed', 'label' => _l('po_status_confirmed')],
                                    ['value' => 'partially_received', 'label' => _l('po_status_partially_received')],
                                    ['value' => 'completed', 'label' => _l('po_status_completed')],
                                    ['value' => 'cancelled', 'label' => _l('po_status_cancelled')],
                                ];
                                echo render_select('status', $statuses, ['value', 'label'], _l('status'), $selected_status, ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Supplier -->
                            <div class="col-md-6">
                                <?php
                                // Pre-fill supplier from quotation if converting
                                if (isset($source_quotation)) {
                                    $selected_supplier = $source_quotation->supplier_id;
                                } elseif (isset($po)) {
                                    $selected_supplier = $po->supplier_id;
                                } else {
                                    $selected_supplier = '';
                                }
                                echo render_select('supplier_id', $suppliers, ['id', 'name'], _l('supplier'), $selected_supplier, ['required' => true]);
                                ?>
                            </div>

                            <!-- Agreement (Optional) -->
                            <div class="col-md-6">
                                <?php
                                $selected_agreement = isset($po) ? $po->agreement_id : '';
                                $agreement_options = [['id' => '', 'agreement_number' => _l('none')]];
                                if (isset($supplier_agreements) && !empty($supplier_agreements)) {
                                    foreach ($supplier_agreements as $agreement) {
                                        // Handle both object and array format
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
                            <!-- PO Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="po_date"><span class="text-danger">*</span> <?php echo _l('po_date'); ?></label>
                                    <input type="text" class="form-control datepicker" name="po_date" id="po_date"
                                        value="<?php echo isset($po) ? _d($po->po_date) : _d(date('Y-m-d')); ?>"
                                        required>
                                </div>
                            </div>

                            <!-- Delivery Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="delivery_date"><?php echo _l('delivery_date'); ?></label>
                                    <input type="text" class="form-control datepicker" name="delivery_date" id="delivery_date"
                                        value="<?php echo isset($po) && $po->delivery_date ? _d($po->delivery_date) : ''; ?>">
                                </div>
                            </div>

                            <!-- Validity Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="validity_date"><?php echo _l('validity_date'); ?></label>
                                    <input type="text" class="form-control datepicker" name="validity_date" id="validity_date"
                                        value="<?php echo isset($po) && $po->validity_date ? _d($po->validity_date) : ''; ?>">
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
                                            value="<?php echo isset($po) ? $po->payment_terms_days : 30; ?>"
                                            min="0" required>
                                        <span class="input-group-addon"><?php echo _l('days'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="col-md-6">
                                <?php
                                $currencies = $this->db->get(db_prefix() . 'currencies')->result_array();
                                $selected_currency = isset($po) ? $po->currency : get_base_currency()->name;
                                echo render_select('currency', $currencies, ['name', 'name'], _l('currency'), $selected_currency, ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="terms_conditions"><?php echo _l('terms_and_conditions'); ?></label>
                                    <textarea class="form-control" name="terms_conditions" id="terms_conditions" rows="3"><?php echo isset($po) ? $po->terms_conditions : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes"><?php echo _l('notes'); ?></label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3"><?php echo isset($po) ? $po->notes : ''; ?></textarea>
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
                                <a href="<?php echo admin_url('equipments/purchase_orders'); ?>" class="btn btn-default">
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

    appValidateForm($('#po-form'), {
        supplier_id: 'required',
        po_date: 'required',
        payment_terms_days: 'required',
        currency: 'required',
        status: 'required'
    });
});
</script>
