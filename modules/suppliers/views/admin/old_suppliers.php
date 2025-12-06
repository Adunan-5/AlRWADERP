<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>

.checkbox-margin-bottom
{
	margin-bottom:22px !important;
}

</style>


<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php echo form_open(); ?>
				<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">Supplier Settings</h4>
</div>
				<div class="col-md-6">
                <div class="form-group">
                    <label for="name"><?= _l('supplier_name') ?></label>
                    <input type="text" class="form-control" name="name" id="name" value="<?= isset($supplier) ? $supplier->name : '' ?>" required>
                </div>
				</div>
				<div class="col-md-6">
                <div class="form-group">
                    <label for="display_name"><?= _l('supplier_display_name') ?></label>
                    <input type="text" class="form-control" name="display_name" id="display_name" value="<?= isset($supplier) ? $supplier->display_name : '' ?>">
                </div>
				  </div>
				  <div class="col-md-12">
				<div class="form-group">
                    <label for="address"><?= _l('supplier_address') ?></label>
                    <textarea class="form-control" name="address" id="address" value="<?= isset($supplier) ? $supplier->address : '' ?>"></textarea>
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="contact_person"><?= _l('supplier_contact_person') ?></label>
                    <input type="text" class="form-control" name="contact_person" id="contact_person" value="<?= isset($supplier) ? $supplier->contact_person : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="phone"><?= _l('supplier_phone') ?></label>
                    <input type="text" class="form-control" name="phone" id="phone" value="<?= isset($supplier) ? $supplier->phone : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="mobile"><?= _l('supplier_mobile') ?></label>
                    <input type="text" class="form-control" name="mobile" id="mobile" value="<?= isset($supplier) ? $supplier->mobile : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="email"><?= _l('supplier_email') ?></label>
                    <input type="email" class="form-control" name="email" id="email" value="<?= isset($supplier) ? $supplier->email : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="purchase_order_number_prefix"><?= _l('supplier_purchase_order_number_prefix') ?></label>
                    <input type="text" class="form-control" name="purchase_order_number_prefix" id="purchase_order_number_prefix" value="<?= isset($supplier) ? $supplier->purchase_order_number_prefix : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="purchase_order_number_suffix"><?= _l('supplier_purchase_order_number_suffix') ?></label>
                    <input type="text" class="form-control" name="purchase_order_number_suffix" id="purchase_order_number_suffix" value="<?= isset($supplier) ? $supplier->purchase_order_number_suffix : '' ?>">
                </div>
				</div>
			
				
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($supplier) && $supplier->split_timesheet_based_on_invoice_period == 1 ? 'checked' : '';
				?>
                    <input type="checkbox" name="split_timesheet_based_on_invoice_period" id="split_timesheet_based_on_invoice_period" <?=$checked?> value="1">
                <label for="split_timesheet_based_on_invoice_period"><?= _l('supplier_split_timesheet_based_on_invoice_period') ?></label>
				</div>
				</div>
				
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($supplier) && $supplier->split_timesheet_based_on_project == 1 ? 'checked' : '';
				?>
				<input type="checkbox"  name="split_timesheet_based_on_project" id="split_timesheet_based_on_project" <?=$checked?> value="1">
                    <label for="split_timesheet_based_on_project"><?= _l('supplier_split_timesheet_based_on_project') ?></label>
                    
                </div>
				</div>
				
				<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">VAT Settings</h4>
</div>
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($supplier) && $supplier->enable_vat == 1 ? 'checked' : '';
				?>
				<input type="checkbox"  name="enable_vat" id="enable_vat" <?=$checked?> value="1">
                    <label for="enable_vat"><?= _l('supplier_enable_vat') ?></label>
                    
                </div>
				 </div>
				
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($supplier) && $supplier->po_rate_include_vat == 1 ? 'checked' : '';
				?>
				<input type="checkbox" name="po_rate_include_vat" id="po_rate_include_vat" <?= $checked ?> value="1">
                    <label for="po_rate_include_vat"><?= _l('supplier_po_rate_include_vat') ?></label>
                    
                </div>
				 </div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="vat_number"><?= _l('supplier_vat_number') ?></label>
                    <input type="text" class="form-control" name="vat_number" id="vat_number" value="<?= isset($supplier) ? $supplier->vat_number : '' ?>">
                </div>
				 </div>
				 
				 <div class="col-md-6">
					 <div class="form-group" app-field-wrapper="date">
						<label for="date" class="control-label"> <small class="req text-danger">* </small><?= _l('supplier_vat_start_date') ?></label>
							<div class="input-group date">
								<input type="text" id="vat_start_date" name="vat_start_date" class="form-control datepicker" value="<?= isset($supplier) ? $supplier->vat_start_date: '' ?>" autocomplete="off" aria-invalid="false">
								<div class="input-group-addon">
									<i class="fa-regular fa-calendar calendar-icon"></i>
								</div>
							</div>
					</div>
				</div>
				 
				<div class="col-md-6">
				<div class="form-group">
                    <label for="vat_percentage"><?= _l('supplier_vat_percentage') ?></label>
                    <input type="text" class="form-control" name="vat_percentage" id="vat_percentage" value="<?= isset($supplier) ? $supplier->vat_percentage : '' ?>">
                </div>
				 </div>
				 
				<div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary"><?= isset($supplier) ? _l('edit') : _l('add_new_supplier') ?></button>
				</div>
                <?php echo form_close(); ?>
           </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
