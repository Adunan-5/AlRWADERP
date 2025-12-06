<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php echo form_open_multipart($this->uri->uri_string()); ?>

				<div class="col-md-12">
					<div class="alert alert-info tw-mb-4">
						<i class="fa fa-info-circle"></i>
						<strong>File Upload Limits:</strong> Maximum 10MB per file. Accepted formats: PDF, JPG, PNG.
					</div>
				</div>

				<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">Basic Information</h4>
</div>
				<div class="col-md-6">
                <div class="form-group">
                    <label for="name"><?= _l('equipment_name') ?></label>
                    <input type="text" class="form-control" name="name" id="name" value="<?= isset($equipment) ? $equipment->name : '' ?>" required>
                </div>
				</div>
				<div class="col-md-6">
                <div class="form-group">
                    <label for="platenumber_code"><?= _l('equipment_platenumber_code') ?></label>
                    <input type="text" class="form-control" name="platenumber_code" id="platenumber_code" value="<?= isset($equipment) ? $equipment->platenumber_code : '' ?>">
                </div>
				  </div>

				<div class="col-md-6">
					 <div class="form-group" app-field-wrapper="date">
						<label for="date" class="control-label"> <small class="req text-danger">* </small><?= _l('equipment_available_from_date') ?></label>
							<div class="input-group date">
								<input type="text" id="available_from_date" name="available_from_date" class="form-control datepicker" value="<?= isset($equipment) ? $equipment->available_from_date: '' ?>" autocomplete="off" aria-invalid="false">
								<div class="input-group-addon">
									<i class="fa-regular fa-calendar calendar-icon"></i>
								</div>
							</div>
					</div>
				</div>

				<div class="col-md-6">
						<?php
  $ownership_type = isset($equipment) ? $equipment->ownership_type : '';
?>
<div class="form-group contact-direction-option">
  <label for="ownership_type"><?= _l('equipment_ownership_type') ?></label>
  <select class="selectpicker"
          data-none-selected-text="Select Ownership Type"
          data-width="100%"
          name="ownership_type"
          id="ownership_type">
    <option value="Own" <?= $ownership_type == 'Own' ? 'selected' : '' ?>>Own</option>
    <option value="Supplier Rented" <?= $ownership_type == 'Supplier Rented' ? 'selected' : '' ?>>Supplier Rented</option>
  </select>
</div>

				</div>

                <!-- Supplier Dropdown (shows when Supplier Rented is selected) -->
                <div class="col-md-6" id="supplier-field" <?php echo (isset($equipment) && $equipment->ownership_type == 'Own') || !isset($equipment) ? 'style="display:none;"' : ''; ?>>
                    <?php
                    $suppliers = $this->db->get(db_prefix() . 'suppliers')->result_array();
                    $supplier_options = [];
                    foreach ($suppliers as $supplier) {
                        $supplier_options[] = [
                            'value' => $supplier['id'],
                            'label' => $supplier['name']
                        ];
                    }
                    $selected_supplier = isset($equipment) && !empty($equipment->supplier_id) ? $equipment->supplier_id : '';
                    echo render_select('supplier_id', $supplier_options, ['value', ['label']], 'supplier', $selected_supplier);
                    ?>
                </div>


									<?php
      //                                  $equipment_type = get_all_equipment_types(); 
      //                                  $selected_equipment_type = (isset($member) ? explode(',', $member->equipment_type) : []);
      //                                  echo render_select('equipmenttype[]', $equipment_type, ['id', 'name'], 'Equipment Type', $selected_equipment_type, ['multiple' => true]);
                                    ?>
					<div class="col-md-6">
					

							<?php
$equipment_types = get_all_equipment_types();
$selected_equipment_type = isset($equipment) && !empty($equipment->equipmenttype)
  ? array_map('intval', explode(',', $equipment->equipmenttype))
  : [];

echo render_select(
  'equipmenttype[]',
  $equipment_types,
  ['id', 'name'],
  'Equipment Type',
  $selected_equipment_type,
  ['multiple' => true]
);

?>

					</div>



<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">Contact Information</h4>
</div>

<div class="col-md-6">
										<?php $countries = get_all_countries();
                                        $customer_default_country                = get_option('customer_default_country');
                                        $selected                                = (isset($member) ? $member->country : $customer_default_country);
                                        echo render_select('country', $countries, ['country_id', ['short_name']], 'staff_nationality', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                                    ?>
</div>




				<div class="col-md-6">
    <div class="form-group">
        <label for="address"><?= _l('equipment_address') ?></label>
        <textarea class="form-control" name="address" id="address"><?= isset($equipment) ? $equipment->address : '' ?></textarea>
    </div>
</div>

				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="phone"><?= _l('equipment_phone') ?></label>
                    <input type="text" class="form-control" name="phone" id="phone" value="<?= isset($equipment) ? $equipment->phone : '' ?>">
                </div>
				</div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="email"><?= _l('equipment_email') ?></label>
                    <input type="email" class="form-control" name="email" id="email" value="<?= isset($equipment) ? $equipment->email : '' ?>">
                </div>
				</div>




<!-- Documents Section -->
<div class="col-md-12">
    <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700"><?php echo _l('equipment_documents'); ?></h4>
    <p class="text-muted tw-mb-4"><small><?php echo _l('document_types_from_master'); ?></small></p>
</div>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php if (!empty($equipment_document_types)): ?>
                <?php foreach ($equipment_document_types as $doc_type): ?>
                    <div class="equipment-document-section tw-mb-6 tw-pb-4 tw-border-b last:tw-border-0">
                        <h5 class="tw-font-semibold tw-mb-3">
                            <?php echo $doc_type['name']; ?>
                            <?php if (!empty($doc_type['name_arabic'])): ?>
                                <span class="text-muted"> - <?php echo $doc_type['name_arabic']; ?></span>
                            <?php endif; ?>
                            <?php if ($doc_type['is_mandatory']): ?>
                                <span class="label label-danger">Mandatory</span>
                            <?php endif; ?>
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $field_name = 'doc_number_' . $doc_type['id'];
                                $existing_value = '';
                                if (isset($equipment_documents)) {
                                    foreach ($equipment_documents as $doc) {
                                        if ($doc['document_type_id'] == $doc_type['id'] && $doc['document_number']) {
                                            $existing_value = $doc['document_number'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <input type="text"
                                       name="<?php echo $field_name; ?>"
                                       class="form-control"
                                       placeholder="<?php echo _l('document_number'); ?>"
                                       value="<?php echo $existing_value; ?>"
                                       <?php echo $doc_type['is_mandatory'] ? 'required' : ''; ?>>
                            </div>

                            <?php if ($doc_type['requires_expiry']): ?>
                                <div class="col-md-6">
                                    <?php
                                    $expiry_field_name = 'doc_expiry_' . $doc_type['id'];
                                    $existing_expiry = '';
                                    if (isset($equipment_documents)) {
                                        foreach ($equipment_documents as $doc) {
                                            if ($doc['document_type_id'] == $doc_type['id'] && $doc['expiry_date']) {
                                                $existing_expiry = _d($doc['expiry_date']);
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="input-group date">
                                        <input type="text"
                                               name="<?php echo $expiry_field_name; ?>"
                                               class="form-control datepicker"
                                               placeholder="<?php echo _l('expiry_date'); ?>"
                                               value="<?php echo $existing_expiry; ?>"
                                               autocomplete="off"
                                               <?php echo $doc_type['is_mandatory'] ? 'required' : ''; ?>>
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                    <?php if ($doc_type['reminder_days'] > 0): ?>
                                        <small class="text-muted">
                                            <i class="fa fa-bell-o"></i>
                                            <?php echo sprintf(_l('reminder_before_days'), $doc_type['reminder_days']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- File Upload Section -->
                        <div class="row tw-mt-3">
                            <div class="col-md-12">
                                <?php
                                // Check if file exists for this document
                                $existing_file = '';
                                $existing_file_name = '';
                                $doc_id = '';
                                if (isset($equipment_documents)) {
                                    foreach ($equipment_documents as $doc) {
                                        if ($doc['document_type_id'] == $doc_type['id']) {
                                            $existing_file = $doc['file_path'];
                                            $existing_file_name = $doc['file_name'];
                                            $doc_id = $doc['id'];
                                            break;
                                        }
                                    }
                                }
                                ?>

                                <?php if ($existing_file && file_exists(FCPATH . $existing_file)): ?>
                                    <div class="tw-flex tw-items-center tw-justify-between tw-bg-gray-50 tw-p-2 tw-rounded tw-mb-2">
                                        <div class="tw-flex tw-items-center">
                                            <i class="fa fa-file-text-o tw-mr-2 tw-text-blue-600"></i>
                                            <span class="tw-text-sm"><?php echo $existing_file_name; ?></span>
                                        </div>
                                        <div>
                                            <a href="<?php echo admin_url('equipments/download_equipment_document/' . $doc_id); ?>"
                                               class="btn btn-xs btn-info"
                                               title="<?php echo _l('download'); ?>">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <a href="<?php echo admin_url('equipments/delete_equipment_document/' . $doc_id); ?>"
                                               class="btn btn-xs btn-danger _delete"
                                               title="<?php echo _l('delete'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label class="control-label">
                                        <i class="fa fa-upload"></i>
                                        <?php echo _l('upload_file'); ?>
                                        <?php if (!$existing_file): ?>
                                            <small class="text-muted">(PDF, JPG, PNG - Max 10MB)</small>
                                        <?php else: ?>
                                            <small class="text-muted"><?php echo _l('replace_file'); ?> (Max 10MB)</small>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file"
                                           name="doc_file_<?php echo $doc_type['id']; ?>"
                                           class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted"><?php echo _l('no_document_types_found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
			
				 
				<div class="col-md-12 text-right">
                <a href="<?php echo admin_url('equipments'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                <button type="submit" class="btn btn-primary"><?= isset($equipment) ? _l('update_equipment') : _l('submit') ?></button>
				</div>
                <?php echo form_close(); ?>
           </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    'use strict';

    // Show/hide supplier field based on ownership type
    $('#ownership_type').on('change', function() {
        if ($(this).val() == 'Supplier Rented') {
            $('#supplier-field').slideDown();
            $('#supplier-field select').prop('required', true);
        } else {
            $('#supplier-field').slideUp();
            $('#supplier-field select').prop('required', false).val('');
        }
    });

    // Trigger on page load
    $('#ownership_type').trigger('change');

    // Validate file sizes before form submission
    $('form').on('submit', function(e) {
        var maxSize = 10 * 1024 * 1024; // 10MB in bytes
        var hasError = false;
        var errorFiles = [];

        $('input[type="file"]').each(function() {
            if (this.files && this.files[0]) {
                var fileSize = this.files[0].size;
                var fileName = this.files[0].name;

                if (fileSize > maxSize) {
                    hasError = true;
                    errorFiles.push(fileName + ' (' + (fileSize / 1024 / 1024).toFixed(2) + 'MB)');
                }
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('The following files exceed the 10MB limit:\n\n' + errorFiles.join('\n') + '\n\nPlease reduce file sizes or upload documents one at a time.');
            return false;
        }
    });
});
</script>