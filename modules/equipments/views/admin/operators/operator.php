<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <?php echo isset($operator) ? _l('edit') . ' ' . _l('operator') : _l('add_new_operator'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'operator-form']); ?>

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('basic_information'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <?php echo render_input('name', 'operator_name', isset($operator) ? $operator->name : '', 'text', ['required' => true]); ?>

                                        <?php echo render_input('name_arabic', 'operator_name_arabic', isset($operator) ? $operator->name_arabic : '', 'text', ['dir' => 'rtl']); ?>

                                        <?php
                                        $selected_country = isset($operator) ? $operator->nationality : '';
                                        echo render_select('nationality', $countries, ['country_id', ['short_name']], 'nationality', $selected_country, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                                        ?>

                                        <?php
                                        $operator_types = [
                                            ['value' => 'own', 'label' => _l('own_operator')],
                                            ['value' => 'hired', 'label' => _l('hired_operator')]
                                        ];
                                        $selected_type = isset($operator) ? $operator->operator_type : 'own';
                                        echo render_select('operator_type', $operator_types, ['value', ['label']], 'operator_type', $selected_type, ['required' => true], [], '', '', false);
                                        ?>

                                        <div id="supplier-field" <?php echo (isset($operator) && $operator->operator_type == 'own') ? 'style="display:none;"' : ''; ?>>
                                            <?php
                                            $supplier_options = [];
                                            if (isset($suppliers)) {
                                                foreach ($suppliers as $supplier) {
                                                    $supplier_options[] = [
                                                        'value' => $supplier['id'],
                                                        'label' => $supplier['name']
                                                    ];
                                                }
                                            }
                                            $selected_supplier = isset($operator) ? $operator->supplier_id : '';
                                            echo render_select('supplier_id', $supplier_options, ['value', ['label']], 'supplier', $selected_supplier);
                                            ?>
                                        </div>

                                        <div class="form-group">
                                            <div class="checkbox">
                                                <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo (isset($operator) && $operator->is_active) || !isset($operator) ? 'checked' : ''; ?>>
                                                <label for="is_active"><?php echo _l('active'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Information -->
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('document_information'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <p class="text-muted"><?php echo _l('document_types_from_master'); ?></p>

                                        <?php if (!empty($document_types)): ?>
                                            <?php foreach ($document_types as $doc_type): ?>
                                                <div class="form-group document-type-group" data-doc-type-id="<?php echo $doc_type['id']; ?>">
                                                    <label class="control-label">
                                                        <?php echo $doc_type['name']; ?>
                                                        <?php if ($doc_type['name_arabic']): ?>
                                                            <span dir="rtl" class="text-muted">(<?php echo $doc_type['name_arabic']; ?>)</span>
                                                        <?php endif; ?>
                                                        <?php if ($doc_type['is_mandatory']): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?>
                                                    </label>

                                                    <div class="row">
                                                        <div class="col-md-<?php echo $doc_type['requires_expiry'] ? '6' : '12'; ?>">
                                                            <?php
                                                            $field_name = 'doc_number_' . $doc_type['id'];
                                                            $existing_value = '';
                                                            if (isset($operator_documents)) {
                                                                foreach ($operator_documents as $doc) {
                                                                    if ($doc['document_type_id'] == $doc_type['id']) {
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
                                                                if (isset($operator_documents)) {
                                                                    foreach ($operator_documents as $doc) {
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
                                                            if (isset($operator_documents)) {
                                                                foreach ($operator_documents as $doc) {
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
                                                                        <a href="<?php echo admin_url('equipments/download_operator_document/' . $doc_id); ?>"
                                                                           class="btn btn-xs btn-info"
                                                                           title="<?php echo _l('download'); ?>">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                        <a href="<?php echo admin_url('equipments/delete_operator_document/' . $doc_id); ?>"
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
                                                                        <small class="text-muted">(PDF, JPG, PNG - Max 5MB)</small>
                                                                    <?php else: ?>
                                                                        <small class="text-muted"><?php echo _l('replace_file'); ?></small>
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
                        </div>

                        <div class="row">
                            <!-- Skills & Remarks -->
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('skills_and_remarks'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <?php echo render_textarea('skills', 'skills', isset($operator) ? $operator->skills : '', ['rows' => 3]); ?>

                                        <?php echo render_textarea('remarks', 'remarks', isset($operator) ? $operator->remarks : '', ['rows' => 3]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('equipments/operators'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo _l('submit'); ?>
                            </button>
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
$(function(){
    'use strict';

    // Show/hide supplier field based on operator type
    $('#operator_type').on('change', function() {
        if ($(this).val() == 'hired') {
            $('#supplier-field').slideDown();
            $('#supplier-field select').prop('required', true);
        } else {
            $('#supplier-field').slideUp();
            $('#supplier-field select').prop('required', false).val('');
        }
    });

    // Trigger on page load
    $('#operator_type').trigger('change');

    // Form validation
    appValidateForm($('#operator-form'), {
        name: 'required',
        operator_type: 'required'
    });
});
</script>
