<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <!-- <div class="tw-max-w-5xl tw-mx-auto"> -->
        <div>
            <?php if (isset($member)) { ?>
            <?php 
                // $this->load->view('admin/staff/stats');
            ?>
            <div class="member">
                <?= form_hidden('isedit'); ?>
                <?= form_hidden('memberid', $member->staffid); ?>
            </div>
            <?php } ?>
            <div class="clearfix"></div>

            <?php if (isset($member) && total_rows(db_prefix() . 'departments', ['email' => $member->email]) > 0) { ?>
            <div class="alert alert-danger tw-mt-1">
                The staff member email exists also as support department email, according to the docs, the support department email must be unique email in the system, you must change the staff email or the support department email in order all the features to work properly.
            </div>
            <?php } ?>

            <div class="clearfix"></div>

            <h4 class="sticky-name-section tw-font-bold tw-text-lg<?= isset($member) ? ' tw-mt-4' : ''; ?>">
                <?php if (isset($member)) { ?>
                <?= e($member->name); ?>
                <?php if ($member->last_activity && $member->staffid != get_staff_user_id()) { ?>
                <small> -
                    <?= _l('last_active'); ?>:
                    <span class="text-has-action" data-toggle="tooltip" data-title="<?= e(_dt($member->last_activity)); ?>">
                        <?= e(time_ago($member->last_activity)); ?>
                    </span>
                </small>
                <?php } ?>
                <?php } else { ?>
                <?= e($title); ?>
                <?php } ?>
            </h4>

            <?php if (isset($member)) { ?>
            <!-- Quick Info Panel -->
            <?php $this->load->view('admin/staff/quick_info_panel'); ?>
            <?php } ?>

            <?php if (isset($member)) { ?>
            <div class="horizontal-scrollable-tabs sticky-nav-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal nav-tabs-segmented tw-mb-3" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_staff_member" aria-controls="tab_staff_member" role="tab" data-toggle="tab">
                                <?= _l('staff'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_notes" aria-controls="staff_notes" role="tab" data-toggle="tab">
                                <?= _l('staff_add_edit_notes'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_timesheets" aria-controls="staff_timesheets" role="tab" data-toggle="tab">
                                <?= _l('task_timesheets'); ?>
                                & <?= _l('als_reports'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_projects" aria-controls="staff_projects" role="tab" data-toggle="tab">
                                <?= _l('projects'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_vacation" aria-controls="staff_vacation" role="tab" data-toggle="tab">
                                <?= _l('staff_vacation'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_pay" aria-controls="staff_pay" role="tab" data-toggle="tab">
                                <?= _l('staff_pay'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#staff_files" aria-controls="staff_files" role="tab" data-toggle="tab">
                                <?= _l('staff_files'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php } ?>

            <div class="panel_s">
                <div class="panel-body">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="tab_staff_member">
                            <?= form_open_multipart($this->uri->uri_string(), ['class' => 'staff-form', 'autocomplete' => 'off']); ?>
                            <div class="row">
                                <!-- Vertical Tabs Navigation -->
                                <div class="col-md-2">
                                    <ul class="nav nav-tabs nav-tabs-vertical" role="tablist">
                                        <li role="presentation" class="active">
                                            <a href="#tab_basic" aria-controls="tab_basic" role="tab" data-toggle="tab" class="vertical-tab">
                                                Basic
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#tab_contact" aria-controls="tab_contact" role="tab" data-toggle="tab" class="vertical-tab">
                                                Contact
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#tab_bank_details" aria-controls="tab_bank_details" role="tab" data-toggle="tab" class="vertical-tab">
                                                Bank Details
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#tab_documents" aria-controls="tab_documents" role="tab" data-toggle="tab" class="vertical-tab">
                                                Documents
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#tab_other" aria-controls="tab_other" role="tab" data-toggle="tab" class="vertical-tab">
                                                Other
                                            </a>
                                        </li>
                                        <?php if (is_admin()) { ?>
                                        <li role="presentation">
                                            <a href="#tab_permissions" aria-controls="tab_permissions" role="tab" data-toggle="tab" class="vertical-tab">
                                                <?= _l('staff_add_edit_permissions'); ?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <!-- Tab Content -->
                                <div class="col-md-9">
                                    <div class="tab-content">
                                        <!-- Basic Tab -->
                                        <div role="tabpanel" class="tab-pane active" id="tab_basic">
                                            <?php if (is_admin()) { ?>
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="administrator" id="administrator"
                                                    <?= isset($member) && ($member->staffid == get_staff_user_id() || is_admin($member->staffid)) ? 'checked' : ''; ?>>
                                                <label for="administrator"><?= _l('staff_add_edit_administrator'); ?></label>
                                            </div>
                                            <?php } ?>
                                            <div class="is-not-staff<?= isset($member) && $member->admin == 1 ? ' hide' : ''; ?>">
                                                <div class="checkbox checkbox-primary">
                                                    <?php $checked = isset($member) && $member->is_not_staff == 1 ? ' checked' : ''; ?>
                                                    <input type="checkbox" value="1" name="is_not_staff" id="is_not_staff" <?= e($checked); ?>>
                                                    <label for="is_not_staff"><?= _l('is_not_staff_member'); ?></label>
                                                </div>
                                            </div>
                                            <hr />
                                            <?php if ((isset($member) && $member->profile_image == null) || !isset($member)) { ?>
                                            <div class="form-group">
                                                <label for="profile_image" class="profile-image"><?= _l('staff_edit_profile_image'); ?></label>
                                                <input type="file" name="profile_image" class="form-control" id="profile_image">
                                            </div>
                                            <?php } ?>
                                            <?php if (isset($member) && $member->profile_image != null) { ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <?= staff_profile_image($member->staffid, ['img', 'img-responsive', 'staff-profile-image-thumb'], 'thumb'); ?>
                                                    </div>
                                                    <div class="col-md-3 text-right">
                                                        <a href="<?= admin_url('staff/remove_staff_profile_image/' . $member->staffid); ?>"><i class="fa fa-remove"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <div style="display:none;">
                                                <?php $value = (isset($member) ? $member->firstname : ''); ?>
                                                <?php $attrs = (isset($member) ? [] : ['autofocus' => true]); ?>
                                                <?= render_input('firstname', 'staff_add_edit_firstname', $value, 'text', $attrs); ?>
                                                <?php $value = (isset($member) ? $member->lastname : ''); ?>
                                                <?= render_input('lastname', 'staff_add_edit_lastname', $value); ?>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->name : ''); ?>
                                                    <?= render_input('name', 'Name (English)', $value); ?>
                                                    <button type="button" class="btn btn-default btn-sm" id="translate-to-arabic" style="margin-top: -10px; margin-bottom: 10px;">
                                                        <i class="fa fa-language"></i> Translate to Arabic
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->name_arabic : ''); ?>
                                                    <?= render_input('name_arabic', 'Name (Arabic)', $value); ?>
                                                    <button type="button" class="btn btn-default btn-sm" id="translate-to-english" style="margin-top: -10px; margin-bottom: 10px;">
                                                        <i class="fa fa-language"></i> Translate to English
                                                    </button>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->code : ''); ?>
                                                    <?= render_input('code', 'Code', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) && !empty($member->joining_date)) ? $member->joining_date : ''; ?>
                                                    <?= render_date_input('joining_date', 'Joining Date', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) && !empty($member->joining_date_hijri)) ? $member->joining_date_hijri : ''; ?>
                                                    <div class="form-group">
                                                        <label for="joining_date_hijri" class="control-label"><?= _l('Joining Date Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="joining_date_hijri" name="joining_date_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php
                                                    $staff_types = get_all_stafftypes();
                                                    $selected_staff_type = isset($member) ? $member->stafftype_id : '';
                                                    echo render_select(
                                                        'stafftype_id',
                                                        $staff_types,
                                                        ['id', 'name'],
                                                        'Staff Type',
                                                        $selected_staff_type,
                                                        ['data-live-search' => true, 'class' => 'selectpicker', 'id' => 'staff_type_select']
                                                    );
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <div id="supplier_container" style="display: none;">
                                                        <?php
                                                        $suppliers = get_all_suppliers();
                                                        $selected_supplier = isset($member) ? $member->supplier_id : '';
                                                        echo render_select('supplier_id', $suppliers, ['id', 'name'], 'Supplier', $selected_supplier);
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div id="ownemployee_container" style="display: none;">
                                                        <?php
                                                        $ownemployeetypes = get_all_ownemployee_types();
                                                        $selected_ownemployeetype = isset($member) ? $member->ownemployee_id : '';
                                                        echo render_select(
                                                            'ownemployee_id',
                                                            $ownemployeetypes,
                                                            ['id', 'name'],
                                                            'Own Employee Type',
                                                            $selected_ownemployeetype,
                                                            ['data-live-search' => true, 'class' => 'selectpicker', 'id' => 'ownemployee_type_select']
                                                        );
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php
                                                    $company_types = get_all_company_types();
                                                    $selected_company_type = isset($member) ? $member->companytype_id : '';
                                                    echo render_select(
                                                        'companytype_id',
                                                        $company_types,
                                                        ['id', 'name'],
                                                        'Company Type',
                                                        $selected_company_type,
                                                        ['data-live-search' => true, 'class' => 'selectpicker', 'id' => 'company_type_select']
                                                    );
                                                    ?>
                                                </div>

                                                <div class="col-md-6">
                                                    <?php
                                                    $projects = get_all_projects(); // Assume helper exists; else fetch from DB
                                                    $selected_project = isset($member) ? $member->project_id : '';
                                                    echo render_select('project_id', $projects, ['id', 'name'], 'Project', $selected_project, ['data-live-search' => true, 'class' => 'selectpicker']);
                                                    ?>
                                                </div>
                                                <div class="col-md-6" style="display:none;">
                                                    <?php
                                                    $skills = get_all_skills();
                                                    $selected_skills = isset($member) ? explode(',', (string)$member->skills) : [];
                                                    echo render_select(
                                                        'skills[]',
                                                        $skills,
                                                        ['id', 'name'],
                                                        'Skills',
                                                        $selected_skills,
                                                        ['multiple' => true, 'data-live-search' => true, 'class' => 'selectpicker'],
                                                        [],
                                                        '',
                                                        '',
                                                        false
                                                    );
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php
                                                    $countries = get_all_countries();
                                                    $customer_default_country = get_option('customer_default_country');
                                                    $selected = (isset($member) ? $member->country : $customer_default_country);
                                                    echo render_select('country', $countries, ['country_id', ['short_name']], 'staff_nationality', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) && !empty($member->dob)) ? $member->dob : ''; ?>
                                                    <?= render_date_input('dob', 'Date Of Birth', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php
                                                    $status_options = [
                                                        ['id' => 'working', 'name' => 'Working'],
                                                        ['id' => 'standby', 'name' => 'Standby'], 
                                                        ['id' => 'vacation', 'name' => 'Vacation'],
                                                        ['id' => 'exit', 'name' => 'Exit'],
                                                    ];
                                                    $selected_status = isset($member) ? $member->status : 'working';
                                                    echo render_select('status', $status_options, ['id', 'name'], 'Status', $selected_status, ['class' => 'selectpicker', 'data-width' => '100%']);
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->work_hours_per_day : ''); ?>
                                                    <?= render_input('work_hours_per_day', 'Work Hours Per Day', $value, 'number', ['placeholder' => '8.0']); ?>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php if (! isset($member) || is_admin() || ! is_admin() && $member->admin == 0) { ?>
                                                    <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                                                    <input type="text" class="fake-autofill-field" name="fakeusernameremembered"
                                                        value='' tabindex="-1" />
                                                    <input type="password" class="fake-autofill-field" name="fakepasswordremembered"
                                                        value='' tabindex="-1" />
                                                    <div class="clearfix form-group"></div>
                                                    <label for="password"
                                                        class="control-label"><?= _l('staff_add_edit_password'); ?></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control password" name="password"
                                                            autocomplete="off">
                                                        <span class="input-group-addon tw-border-l-0">
                                                            <a href="#password" class="show_password"
                                                                onclick="showPassword('password'); return false;"><i
                                                                    class="fa fa-eye"></i></a>
                                                        </span>
                                                        <span class="input-group-addon">
                                                            <a href="#" class="generate_password"
                                                                onclick="generatePassword(this);return false;"><i
                                                                    class="fa fa-refresh"></i></a>
                                                        </span>
                                                    </div>
                                                    <?php if (isset($member)) { ?>
                                                    <p class="text-muted tw-mt-2">
                                                        <?= _l('staff_add_edit_password_note'); ?>
                                                    </p>
                                                    <?php if ($member->last_password_change != null) { ?>
                                                    <?= _l('staff_add_edit_password_last_changed'); ?>:
                                                    <span class="text-has-action" data-toggle="tooltip"
                                                        data-title="<?= e(_dt($member->last_password_change)); ?>">
                                                        <?= e(time_ago($member->last_password_change)); ?>
                                                    </span>
                                                    <?php }
                                                    } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Contact Tab -->
                                        <div role="tabpanel" class="tab-pane" id="tab_contact">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->email : ''); ?>
                                                    <?= render_input('email', 'staff_add_edit_email', $value, 'email', ['autocomplete' => 'off']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                                                    <?= render_input('phonenumber', 'staff_add_edit_phonenumber', $value); ?>
                                                </div>
                                            </div>
                                            <?php $value = (isset($member) ? $member->address : ''); ?>
                                            <?= render_textarea('address', 'member_address', $value); ?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->emgcontactno : ''); ?>
                                                    <?= render_input('emgcontactno', 'Emergency Contact (HOME)', $value); ?>
                                                </div>
                                            </div>
                                            <div style="display:none;">
                                                <div class="form-group">
                                                    <label for="facebook" class="control-label"><i class="fa-brands fa-facebook-f"></i> <?= _l('staff_add_edit_facebook'); ?></label>
                                                    <input type="text" class="form-control" name="facebook" value="<?php if (isset($member)) { echo e($member->facebook); } ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="linkedin" class="control-label"><i class="fa-brands fa-linkedin-in"></i> <?= _l('staff_add_edit_linkedin'); ?></label>
                                                    <input type="text" class="form-control" name="linkedin" value="<?php if (isset($member)) { echo e($member->linkedin); } ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="skype" class="control-label"><i class="fa-brands fa-skype"></i> <?= _l('staff_add_edit_skype'); ?></label>
                                                    <input type="text" class="form-control" name="skype" value="<?php if (isset($member)) { echo e($member->skype); } ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Bank Details Tab -->
                                        <div role="tabpanel" class="tab-pane" id="tab_bank_details">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->bank_name : ''); ?>
                                                    <?= render_input('bank_name', 'bank_name', $value, 'bank_name', ['autocomplete' => 'off']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->bank_account_number : ''); ?>
                                                    <?= render_input('bank_account_number', 'bank_account_number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->bank_swift_code : ''); ?>
                                                    <?= render_input('bank_swift_code', 'bank_swift_code', $value, 'bank_swift_code', ['autocomplete' => 'off']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->bank_iban_number : ''); ?>
                                                    <?= render_input('bank_iban_number', 'bank_iban_number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->atm_card_number : ''); ?>
                                                    <?= render_input('atm_card_number', 'ATM Card Number', $value, 'atm_card_number', ['autocomplete' => 'off']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->times_issued : ''); ?>
                                                    <?= render_input('times_issued', 'Number of Times Card Issued', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->atm_expiry : ''); ?>
                                                    <?= render_date_input('atm_expiry', 'ATM Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->atm_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="atm_expiry_hijri" class="control-label"><?= _l('ATM Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="atm_expiry_hijri" name="atm_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            $stafftypes = get_all_stafftypes();
                                            $selected_stafftype = isset($member) ? $member->salary_method_id : '';
                                            echo render_select('salary_method_id', '', ['id', 'name'], 'Salary Payment Mode', $selected_stafftype);
                                            ?>
                                        </div>
                                        <!-- Documents Tab -->
                                        <div role="tabpanel" class="tab-pane" id="tab_documents">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->iqama_number : ''); ?>
                                                    <?= render_input('iqama_number', 'IQAMA Number', $value, 'iqama_number', ['autocomplete' => 'off']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->iqama_profession : ''); ?>
                                                    <?= render_input('iqama_profession', 'IQAMA Profession', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->iqama_expiry : ''); ?>
                                                    <?= render_date_input('iqama_expiry', 'IQAMA Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->iqama_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="iqama_expiry_hijri" class="control-label"><?= _l('IQAMA Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="iqama_expiry_hijri" name="iqama_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->ajeer_expiry : ''); ?>
                                                    <?= render_date_input('ajeer_expiry', 'Ajeer Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->ajeer_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="ajeer_expiry_hijri" class="control-label"><?= _l('Ajeer Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="ajeer_expiry_hijri" name="ajeer_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->aramcoid : ''); ?>
                                                    <?= render_input('aramcoid', 'Aramco ID', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) && !empty($member->aramcoidexpiry)) ? $member->aramcoidexpiry : ''; ?>
                                                    <?= render_date_input('aramcoidexpiry', 'Aramco ID Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->visa_number : ''); ?>
                                                    <?= render_input('visa_number', 'Visa Number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->visa_expiry : ''); ?>
                                                    <?= render_date_input('visa_expiry', 'Visa Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->visa_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="visa_expiry_hijri" class="control-label"><?= _l('Visa Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="visa_expiry_hijri" name="visa_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->passport_number : ''); ?>
                                                    <?= render_input('passport_number', 'Passport Number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->passport_expiry : ''); ?>
                                                    <?= render_date_input('passport_expiry', 'Passport Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->insurance_number : ''); ?>
                                                    <?= render_input('insurance_number', 'Insurance Number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->insurance_expiry : ''); ?>
                                                    <?= render_date_input('insurance_expiry', 'Insurance Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->insurance_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="insurance_expiry_hijri" class="control-label"><?= _l('Insurance Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="insurance_expiry_hijri" name="insurance_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->health_card_number : ''); ?>
                                                    <?= render_input('health_card_number', 'Health Card Number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->health_card_expiry : ''); ?>
                                                    <?= render_date_input('health_card_expiry', 'Health Card Expiry', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->health_card_expiry_hijri : ''); ?>
                                                    <div class="form-group">
                                                        <label for="health_card_expiry_hijri" class="control-label"><?= _l('Health Card Expiry Hijri'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" id="health_card_expiry_hijri" name="health_card_expiry_hijri" class="form-control" value="<?= html_escape($value); ?>" readonly>
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Other Tab -->
                                        <div role="tabpanel" class="tab-pane" id="tab_other">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php
                                                    $professiontypes = get_all_profession_types();
                                                    $selected_professiontype = isset($member) ? $member->professiontype_id : '';
                                                    echo render_select('professiontype_id', $professiontypes, ['id', 'name'], 'Profession Type', $selected_professiontype);
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->blood_group : ''); ?>
                                                    <?= render_input('blood_group', 'Blood Group', $value); ?>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->review : ''); ?>
                                                    <?= render_textarea('review', 'Review Notes', $value, ['rows' => 3, 'placeholder' => 'Enter review notes']); ?>
                                                </div>
                                            </div>
                                            <?php /* HIDDEN: Use PAY tab instead for salary information
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->basics : ''); ?>
                                                    <?= render_input('basics', 'Basics', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->ot : ''); ?>
                                                    <?= render_input('ot', 'OT / Hour', $value); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->fatallowance : ''); ?>
                                                    <?= render_input('fatallowance', 'Fat Allowance', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->siteallowance : ''); ?>
                                                    <?= render_input('siteallowance', 'Site Allowance', $value); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->otherallowance : ''); ?>
                                                    <?= render_input('otherallowance', 'Other Allowance', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->advance : ''); ?>
                                                    <?= render_input('advance', 'Advance', $value); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->accomodation : ''); ?>
                                                    <?= render_input('accomodation', 'Accomodation', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) && !empty($member->last_salary_revision_date)) ? $member->last_salary_revision_date : ''; ?>
                                                    <?= render_date_input('last_salary_revision_date', 'Last Salary Revision Date', $value); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <?php $value = (isset($member) ? $member->last_salary_revision_comments : ''); ?>
                                                    <?= render_textarea('last_salary_revision_comments', 'Comments for Last Salary Revision', $value, ['rows' => 2, 'placeholder' => 'Enter comments about the salary revision...']); ?>
                                                </div>
                                            </div>
                                            */ ?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->border_number : ''); ?>
                                                    <?= render_input('border_number', 'Border Number', $value); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->labor_number : ''); ?>
                                                    <?= render_input('labor_number', 'Labor Number', $value); ?>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <div class="form-group mb-0">
                                                        <div class="checkbox">
                                                            <input type="hidden" name="has_gosi" value="0">
                                                            <input type="checkbox" id="has_gosi" name="has_gosi" value="1"
                                                                <?php if (isset($member) && $member->has_gosi == 1) echo 'checked'; ?>>
                                                            <label for="has_gosi">Has GOSI</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->contract_start_date : ''); ?>
                                                    <?= render_date_input('contract_start_date', 'Contract Start Date', $value); ?>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <?php $value = (isset($member) ? $member->contract_period_months : ''); ?>
                                                        <?= render_input('contract_period_months', 'Contract Period (Months)', $value, 'number', ['min' => '0', 'step' => '1', 'placeholder' => '12']); ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php $value = (isset($member) ? $member->contract_end_date : ''); ?>
                                                    <?= render_date_input('contract_end_date', 'Contract End Date', $value); ?>
                                                </div>
                                            </div>
                                            <div style="display:none;">
                                                <div class="form-group">
                                                    <label for="hourly_rate"><?= _l('staff_hourly_rate'); ?></label>
                                                    <div class="input-group">
                                                        <input type="number" name="hourly_rate" value="<?= isset($member) ? $member->hourly_rate : 0; ?>" id="hourly_rate" class="form-control">
                                                        <span class="input-group-addon"><?= e($base_currency->symbol); ?></span>
                                                    </div>
                                                </div>
                                                <?php if (!is_language_disabled()) { ?>
                                                <div class="form-group select-placeholder">
                                                    <label for="default_language" class="control-label"><?= _l('localization_default_language'); ?></label>
                                                    <select name="default_language" data-live-search="true" id="default_language" class="form-control selectpicker" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                                        <option value=""><?= _l('system_default_string'); ?></option>
                                                        <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                                                        <option value="<?= e($availableLanguage); ?>" <?= isset($member) && $member->default_language == $availableLanguage ? 'selected' : ''; ?>>
                                                            <?= e(ucfirst($availableLanguage)); ?>
                                                        </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php } ?>
                                                <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?= _l('staff_email_signature_help'); ?>"></i>
                                                <?php $value = (isset($member) ? $member->email_signature : ''); ?>
                                                <?= render_textarea('email_signature', 'settings_email_signature', $value, ['data-entities-encode' => 'true']); ?>
                                                <div class="form-group select-placeholder">
                                                    <label for="direction"><?= _l('document_direction'); ?></label>
                                                    <select class="selectpicker" data-none-selected-text="<?= _l('system_default_string'); ?>" data-width="100%" name="direction" id="direction">
                                                        <option value="" <?= isset($member) && empty($member->direction) ? 'selected' : ''; ?>></option>
                                                        <option value="ltr" <?= isset($member) && $member->direction == 'ltr' ? 'selected' : ''; ?>>LTR</option>
                                                        <option value="rtl" <?= isset($member) && $member->direction == 'rtl' ? 'selected' : ''; ?>>RTL</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <?php if (count($departments) > 0) { ?>
                                                    <label for="departments"><?= _l('staff_add_edit_departments'); ?></label>
                                                    <?php } ?>
                                                    <?php foreach ($departments as $department) { ?>
                                                    <div class="checkbox checkbox-primary">
                                                        <input type="checkbox" id="dep_<?= e($department['departmentid']); ?>" name="departments[]" value="<?= e($department['departmentid']); ?>" <?= isset($member) && in_array($department['departmentid'], array_column($staff_departments, 'departmentid')) ? 'checked' : ''; ?>>
                                                        <label for="dep_<?= e($department['departmentid']); ?>"><?= e($department['name']); ?></label>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                                <?php $rel_id = (isset($member) ? $member->staffid : false); ?>
                                                <?= render_custom_fields('staff', $rel_id); ?>
                                                <?php if (!isset($member) && is_email_template_active('new-staff-created')) { ?>
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox" name="send_welcome_email" id="send_welcome_email">
                                                    <label for="send_welcome_email"><?= _l('staff_send_welcome_email'); ?></label>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <!-- Permissions Tab -->
                                        <div role="tabpanel" class="tab-pane" id="tab_permissions">
                                            <?php
                                            hooks()->do_action('staff_render_permissions');
                                            $selected = '';
                                            foreach ($roles as $role) {
                                                if (isset($member) && $member->role == $role['roleid']) {
                                                    $selected = $role['roleid'];
                                                    break;
                                                }
                                                if (!isset($member) && get_option('default_staff_role') == $role['roleid']) {
                                                    $selected = $role['roleid'];
                                                }
                                            }
                                            ?>
                                            <?= render_select('role', $roles, ['roleid', 'name'], 'staff_add_edit_role', $selected); ?>
                                            <hr />
                                            <h4 class="tw-mb-4 tw-text-lg tw-font-bold"><?= _l('staff_add_edit_permissions'); ?></h4>
                                            <?php $this->load->view('admin/staff/permissions', [
                                                'funcData' => ['staff_id' => $member->staffid ?? null],
                                                'member' => $member ?? null,
                                            ]); ?>
                                        </div>
                                    </div>
                                    <div class="text-right tw-mt-4">
                                        <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <?= form_close(); ?>
                        </div>
                        <?php if (isset($member)) { ?>
                        <div role="tabpanel" class="tab-pane" id="staff_notes">
                            <div class="tw-text-right">
                                <a href="#" class="btn btn-primary" onclick="slideToggle('.usernote'); return false;"><?= _l('new_note'); ?></a>
                            </div>
                            <div class="mbot15 usernote hide inline-block full-width">
                                <?= form_open(admin_url('misc/add_note/' . $member->staffid . '/staff')); ?>
                                <?= render_textarea('description', 'staff_add_edit_note_description', '', ['rows' => 5]); ?>
                                <button class="btn btn-primary pull-right mbot15"><?= _l('submit'); ?></button>
                                <?= form_close(); ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="mtop15">
                                <table class="table dt-table" data-order-col="2" data-order-type="desc">
                                    <thead>
                                        <tr>
                                            <th width="50%"><?= _l('staff_notes_table_description_heading'); ?></th>
                                            <th><?= _l('staff_notes_table_addedfrom_heading'); ?></th>
                                            <th><?= _l('staff_notes_table_dateadded_heading'); ?></th>
                                            <th><?= _l('options'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_notes as $note) { ?>
                                        <tr>
                                            <td width="50%">
                                                <div data-note-description="<?= e($note['id']); ?>">
                                                    <?= process_text_content_for_display($note['description']); ?>
                                                </div>
                                                <div data-note-edit-textarea="<?= e($note['id']); ?>" class="hide inline-block full-width">
                                                    <textarea name="description" class="form-control" rows="4"><?= clear_textarea_breaks($note['description']); ?></textarea>
                                                    <div class="text-right mtop15">
                                                        <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?= e($note['id']); ?>);return false;"><?= _l('cancel'); ?></button>
                                                        <button type="button" class="btn btn-primary" onclick="edit_note(<?= e($note['id']); ?>);"><?= _l('update_note'); ?></button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= e($note['firstname'] . ' ' . $note['lastname']); ?></td>
                                            <td data-order="<?= e($note['dateadded']); ?>"><?= e(_dt($note['dateadded'])); ?></td>
                                            <td>
                                                <div class="tw-flex tw-items-center tw-space-x-2">
                                                    <?php if ($note['addedfrom'] == get_staff_user_id() || staff_can('delete', 'staff')) { ?>
                                                    <a href="#" onclick="toggle_edit_note(<?= e($note['id']); ?>);return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                                                        <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                    </a>
                                                    <a href="<?= admin_url('misc/delete_note/' . $note['id']); ?>" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                                        <i class="fa-regular fa-trash-can fa-lg"></i>
                                                    </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="staff_timesheets">
                            <!-- <table class="table table-bordered table-striped table-timesheets">
                                <thead>
                                    <tr>
                                        <th><?= _l('Staff'); ?></th>
                                        <th><?= _l('Total Hours'); ?></th>
                                        <th><?= _l('FAT'); ?></th>
                                        <th><?= _l('Days Present'); ?></th>
                                        <th><?= _l('Unit Price'); ?></th>
                                        <th><?= _l('Payable'); ?></th>
                                        <th><?= _l('Remarks'); ?></th>
                                        <th><?= _l('Month'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff_timesheets as $t) { ?>
                                    <tr>
                                        <td><?= e($t['project_name']); ?></td>
                                        <td><?= e($t['total_hours']); ?></td>
                                        <td><?= e($t['fat']); ?></td>
                                        <td><?= e($t['days_present']); ?></td>
                                        <td><?= e(app_format_money($t['unit_price'], $base_currency)); ?></td>
                                        <td><?= e(app_format_money($t['payable'], $base_currency)); ?></td>
                                        <td><?= e($t['remarks']); ?></td>
                                        <td><?= date('F Y', strtotime($t['month_year'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-default view-timesheet" data-id="<?= $t['id']; ?>">
                                                <?= _l('view'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table> -->
                            <div class="row" id="staff_timesheets_container">
                                <div class="col-md-12">
                                    <div id="staff_timesheet_grids" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="staff_projects">
                            <div class="mbot15">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#assign_job_modal">
                                    <i class="fa fa-tasks"></i> Assign New Work Assignment
                                </a>
                            </div>
                            <div class="_filters _hidden_inputs hidden staff_projects_filter">
                                <?= form_hidden('staff_id', $member->staffid); ?>
                            </div>
                            <?php render_datatable([
                                _l('project_name'),
                                _l('skills'),
                                _l('start_date'),
                                _l('end_date'),
                                'Rate Type',
                                _l('options'),
                            ], 'staff-projects'); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="staff_vacation">
                            <div class="mbot15">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#assign_vacation">
                                    <i class="fa fa-umbrella-beach"></i> New Vacation
                                </a>
                            </div>
                            <div class="_filters _hidden_inputs hidden staff_projects_filter">
                                <?= form_hidden('staff_id', $member->staffid); ?>
                            </div>
                            <?php render_datatable([
                                _l('vacation_type'),
                                _l('start_date'),
                                // _l('expected_end_date'),
                                _l('end_date'),
                                _l('comments'),
                                _l('status'),
                                _l('options'),
                            ], 'staff-vacation'); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="staff_pay">
                            <div class="mbot15">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#assign_pay">
                                    <i class="fa fa-money-bill"></i> Add New Payment Information
                                </a>
                            </div>
                            <div class="_filters _hidden_inputs hidden staff_projects_filter">
                                <?= form_hidden('staff_id', $member->staffid); ?>
                            </div>
                            <?php render_datatable([
                                _l('start_date'),
                                _l('payout_type'),
                                _l('basic_pay'),
                                _l('overtime_pay'),
                                _l('food_allowance'),
                                _l('allowance'),
                                _l('fat_allowance'),
                                _l('accomodation_allowance'),
                                _l('mewa'),
                                _l('options'),
                            ], 'staff-pay'); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="staff_files">
                            <div class="mbot15">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#add_file">
                                    <i class="fa fa-file-upload"></i> Add New File
                                </a>
                            </div>
                            <div class="_filters _hidden_inputs hidden staff_projects_filter">
                                <?= form_hidden('staff_id', $member->staffid); ?>
                            </div>
                            <?php render_datatable([
                                _l('document_type'),
                                _l('caption'),
                                _l('file'),
                                _l('uploaded_at'),
                                _l('options'),
                            ], 'staff-files'); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="timesheetDetailsModal" tabindex="-1" role="dialog" aria-labelledby="timesheetDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timesheetDetailsModalLabel">Timesheet Details for <span id="modalStaffName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="timesheetDetailsContent">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Hours Worked</th>
                                </tr>
                            </thead>
                            <tbody id="timesheetDetailsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assign_job_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <?= form_open(admin_url('projects/assign_job'), ['id' => 'assign-job-form']) ?>
                <div class="modal-header">
                <h4 class="modal-title"><?= _l('assign_job_to_staff') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body row">

                <!-- Project (Single select, no default) -->
                <div class="form-group col-md-6">
                    <?php
                    $projects = get_all_projects();
                    echo render_select('project_id', $projects, ['id', 'name'], 'Projects');
                    ?>
                </div>

                <!-- Skills (Multiple select, will be auto-populated from PAY data) -->
                <div class="form-group col-md-6">
                    <?php
                    $skills = get_all_profession_types();
                    echo render_select('skills[]', $skills, ['id', 'name'], 'Skills', [], ['multiple' => true]);
                    ?>
                </div>

                <!-- Badge -->
                <div class="form-group col-md-6">
                    <?= render_input('badge', 'badge') ?>
                </div>

                <!-- Start Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('start_date', 'start_date') ?>
                </div>

                <!-- End Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('end_date', 'end_date') ?>
                </div>

                <!-- Equipment (dropdown from helper) -->
                <div class="form-group col-md-6">
                    <?php
                    $equipments = get_all_equipments();
                    echo render_select('equipment', $equipments, ['id', 'name'], 'Equipment');
                    ?>
                </div>

                <!-- Regular Rate (will be auto-populated from current PAY data) -->
                <div class="form-group col-md-6">
                    <?php
                    echo render_input('regular_rate', 'regular_rate', '', 'number')
                    ?>
                </div>

                <!-- Overtime Rate (will be auto-populated from current PAY data) -->
                <div class="form-group col-md-6">
                    <?php
                    echo render_input('overtime_rate', 'overtime_rate', '', 'number')
                    ?>
                </div>

                <!-- Rate Type -->
                <div class="form-group col-md-12">
                    <label for="rate_type"><?= _l('rate_type') ?></label><br/>
                    <div class="radio radio-primary radio-inline">
                    <input type="radio" name="rate_type" id="hourly" value="hourly" checked>
                    <label for="hourly"><?= _l('hourly') ?></label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                    <input type="radio" name="rate_type" id="monthly" value="monthly">
                    <label for="monthly"><?= _l('monthly') ?></label>
                    </div>
                </div>

                <?= form_hidden('staff_id', $member->staffid) ?>
                </div>

                <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
                </div>

            <?= form_close(); ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assign_vacation" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <?= form_open(admin_url('staff/save_vacation'), ['id' => 'vacation-form']) ?>
                <div class="modal-header">
                    <h4 class="modal-title" id="vacationModalLabel"><?= _l('add_vacation') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body row">
                    <!-- Vacation Type -->
                    <div class="form-group col-md-6">
                        <?php
                        $vacation_types = [
                            ['id' => 'annual', 'name' => 'Annual Leave'],
                            ['id' => 'sick', 'name' => 'Sick Leave'],
                            ['id' => 'unpaid', 'name' => 'Unpaid Leave'],
                        ];
                        echo render_select('vacation_type', $vacation_types, ['id','name'], 'vacation_type');
                        ?>
                    </div>

                    <!-- Start Date -->
                    <div class="form-group col-md-6">
                        <?= render_date_input('start_date', 'start_date') ?>
                    </div>

                    <!-- Expected End Date -->
                    <div class="form-group col-md-6" style="display:none;">
                        <?= render_date_input('expected_end_date', 'expected_end_date') ?>
                    </div>

                    <!-- Actual End Date -->
                    <div class="form-group col-md-6">
                        <?= render_date_input('end_date', 'end_date') ?>
                    </div>

                    <!-- Comments -->
                    <div class="form-group col-md-12">
                        <?= render_textarea('comments', 'comments') ?>
                    </div>

                    <!-- Status -->
                    <div class="form-group col-md-6">
                        <?php
                        $status_options = [
                            ['id' => 'pending', 'name' => 'Pending'],
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'rejected', 'name' => 'Rejected'],
                        ];
                        echo render_select('status', $status_options, ['id','name'], 'status', 'pending');
                        ?>
                    </div>

                    <?= form_hidden('staff_id', $member->staffid) ?>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assign_pay" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <?= form_open(admin_url('staff/save_pay'), ['id' => 'pay-form']) ?>
            <div class="modal-header">
                <h4 class="modal-title" id="payModalTitle"><?= _l('add_pay') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <!-- Row 1 -->
                <div class="row">
                    <div class="col-md-6">
                    <?= render_date_input('start_date', 'Starting From Date') ?>
                    </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label"><?= _l('Payout Type') ?></label><br>
                        <div class="radio radio-primary radio-inline">
                        <input type="radio" name="payout_type" id="payout_monthly" value="monthly" checked>
                        <label for="payout_monthly"><?= _l('Monthly') ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                        <input type="radio" name="payout_type" id="payout_hourly" value="hourly">
                        <label for="payout_hourly"><?= _l('Hourly') ?></label>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row">
                    <div class="col-md-6">
                    <?= render_input('basic_pay', 'Basic Pay Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                    <div class="col-md-6">
                    <?= render_input('overtime_pay', 'Overtime Pay Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="row">
                    <div class="col-md-6">
                    <?= render_input('food_allowance', 'Food Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                    <div class="col-md-6">
                    <?= render_input('allowance', 'Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

                <!-- Row 4 -->
                <div class="row">
                    <div class="col-md-6">
                    <?= render_input('fat_allowance', 'FAT Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                    <div class="col-md-6">
                    <?= render_input('accomodation_allowance', 'Accommodation Allowance Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

                <!-- Row 5 -->
                <div class="row">
                    <div class="col-md-6">
                    <?= render_input('mewa', 'MEWA Amount', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

                <!-- Custom Allowances Section -->
                <div id="custom-allowances-section" style="display:none;">
                    <hr>
                    <h5><?= _l('custom_allowances') ?></h5>
                    <div id="custom-allowances-container"></div>
                </div>

                <!-- GOSI Section -->
                <hr>
                <h5><?= _l('gosi_information') ?></h5>
                <div class="row">
                    <div class="col-md-6">
                    <?= render_input('gosi_basic', 'gosi_basic', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                    <div class="col-md-6">
                    <?= render_input('gosi_housing_allowance', 'gosi_housing_allowance', '', 'number', ['step'=>'0.01','min'=>'0']) ?>
                    </div>
                </div>

                <?= form_hidden('staff_id', $member->staffid) ?>
                <input type="hidden" name="id" id="pay_id">

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
            </div>
            <?= form_close(); ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add_file" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <?= form_open_multipart(admin_url('staff/save_file'), ['id' => 'file-form']) ?>
            <div class="modal-header">
                <h4 class="modal-title" id="fileModalTitle"><?= _l('add_file') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="row">
                <div class="col-md-6">
                    <div class="form-group" id="document-type-wrapper">
                        <label for="document_type_id" class="control-label">
                            <span class="text-danger">*</span> Document Type
                        </label>
                        <select name="document_type_id" id="document_type_id" class="selectpicker form-control" data-live-search="true" data-width="100%" required>
                            <option value="">-- Select Document Type --</option>
                            <?php
                            $this->load->model('document_types_model');
                            $document_types = $this->document_types_model->get();
                            foreach ($document_types as $type) {
                                echo '<option value="' . $type['id'] . '">' . e($type['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <div class="tw-mt-2">
                            <button type="button" class="btn btn-default btn-sm" id="quick-add-document-type">
                                <i class="fa fa-plus"></i> Add New Document Type
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <?= render_input('caption', 'Caption') ?>
                </div>
                </div>
                <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    <label><span class="text-danger">*</span> <?= _l('File') ?></label>
                    <input type="file" name="file" class="form-control" id="file-input">
                    </div>
                </div>
                </div>
                <?= form_hidden('staff_id', $member->staffid) ?>
                <input type="hidden" name="id" id="file_id">
                <input type="hidden" name="document_type" id="document_type_text">
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
            </div>
            <?= form_close(); ?>
            </div>
        </div>
    </div>


    <?php init_tail(); ?>
    <script>

var staffProjectsTable;
$(document).ready(function() {
    var staff_id = $('input[name="staff_id"]').val();
    staffProjectsTable = initDataTable('.table-staff-projects', admin_url + 'staff/staff_projects', undefined, undefined, undefined, [0,'desc'], {staff_id: staff_id});
});
        $(function() {
            initDataTable('.table-staff-vacation', 
                admin_url + 'staff/vacation_table/<?= $member->staffid ?>', 
                [], 
                [], 
                { 'staff_id': '<?= $member->staffid ?>' }
            );
            // Add new vacation
            $('body').on('click', '[data-target="#assign_vacation"]', function() {
                // Reset form
                $('#vacation-form')[0].reset();
                $('#vacation-form input[name="id"]').remove();
                $('#assign_vacation select').val('').change();
                $('#assign_vacation textarea').val('');
                
                // Change modal title
                $('#vacationModalLabel').text('<?= _l('add_vacation') ?>');
            });

            // Edit vacation
            $('body').on('click', '.edit-vacation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                $.post(admin_url + 'staff/get_vacation/' + id, function(response) {
                    var data = JSON.parse(response);

                    // populate modal fields
                    $('#vacation-form input[name="staff_id"]').val(data.staff_id);
                    $('#vacation-form input[name="id"]').remove();
                    $('#vacation-form').append('<input type="hidden" name="id" value="'+data.id+'">');

                    $('#vacation-form select[name="vacation_type"]').val(data.vacation_type).change();
                    $('#vacation-form input[name="start_date"]').val(data.start_date);
                    $('#vacation-form input[name="expected_end_date"]').val(data.expected_end_date);
                    $('#vacation-form input[name="end_date"]').val(data.end_date);
                    $('#vacation-form textarea[name="comments"]').val(data.comments);
                    $('#vacation-form select[name="status"]').val(data.status).change();

                    // Change modal title
                    $('#vacationModalLabel').text('<?= _l('edit_vacation') ?>');

                    $('#assign_vacation').modal('show');
                });
            });

            // Delete vacation
            $('body').on('click', '.delete-vacation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var type = $(this).data('type');

                if (confirm('<?= _l('confirm_delete_vacation') ?>')) {
                    $.ajax({
                        url: admin_url + 'staff/delete_vacation/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert_float('success', response.message);
                                $('.table-staff-vacation').DataTable().ajax.reload();
                            } else {
                                // Show detailed reasons if available
                                if (response.reasons_html) {
                                    var msg = '<strong>' + response.message + '</strong><br><br>' +
                                              '<strong>Reasons:</strong>' + response.reasons_html +
                                              '<br><small>Please contact your administrator if you need to remove this vacation entry.</small>';
                                    alert_float('danger', msg);
                                } else {
                                    alert_float('danger', response.message);
                                }
                            }
                        },
                        error: function() {
                            alert_float('danger', '<?= _l('error_deleting_vacation') ?>');
                        }
                    });
                }
            });
        });

        $(function() {
            initDataTable('.table-staff-pay', 
                admin_url + 'staff/pay_table/<?= $member->staffid ?>', 
                [], 
                [], 
                { 'staff_id': '<?= $member->staffid ?>' }
            );

            // Add Pay
            $('body').on('click', '[data-target="#assign_pay"]', function() {
                $('#pay-form')[0].reset();
                $('#pay-form input[name="id"]').val('');
                $('#payModalTitle').text('<?= _l('add_pay') ?>');

                // Load custom allowances for this employee
                loadCustomAllowances(<?= $member->staffid ?>);
            });

            // Edit Pay
            $('body').on('click', '.edit-pay', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                $.post(admin_url + 'staff/get_pay/' + id, function(response) {
                    var data = JSON.parse(response);

                    $('#pay-form input[name="id"]').val(data.id);
                    $('#pay-form input[name="staff_id"]').val(data.staff_id);

                    $('#pay-form input[name="start_date"]').val(data.start_date);
                    $('#pay-form input[name="payout_type"][value="'+data.payout_type+'"]').prop('checked', true);
                    $('#pay-form input[name="basic_pay"]').val(data.basic_pay);
                    $('#pay-form input[name="overtime_pay"]').val(data.overtime_pay);
                    $('#pay-form input[name="food_allowance"]').val(data.food_allowance);
                    $('#pay-form input[name="allowance"]').val(data.allowance);
                    $('#pay-form input[name="fat_allowance"]').val(data.fat_allowance);
                    $('#pay-form input[name="accomodation_allowance"]').val(data.accomodation_allowance);
                    $('#pay-form input[name="mewa"]').val(data.mewa);
                    $('#pay-form input[name="gosi_basic"]').val(data.gosi_basic || 0);
                    $('#pay-form input[name="gosi_housing_allowance"]').val(data.gosi_housing_allowance || 0);

                    // Load custom allowances and populate
                    loadCustomAllowances(data.staff_id, data.custom_allowances);

                    $('#payModalTitle').text('<?= _l('edit_pay') ?>');
                    $('#assign_pay').modal('show');
                });
            });

            // Load custom allowances function
            window.loadCustomAllowances = function(staff_id, existing_values) {
                $.get(admin_url + 'staff/get_applicable_allowances/' + staff_id, function(response) {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;

                    if (data.allowances && data.allowances.length > 0) {
                        var html = '';
                        var rowOpen = false;

                        data.allowances.forEach(function(allowance, index) {
                            if (index % 2 === 0) {
                                html += '<div class="row">';
                                rowOpen = true;
                            }

                            var value = '';
                            if (existing_values && existing_values[allowance.id]) {
                                value = existing_values[allowance.id];
                            } else if (allowance.default_amount) {
                                value = allowance.default_amount;
                            }

                            html += '<div class="col-md-6">';
                            html += '<div class="form-group">';
                            html += '<label for="custom_allowance_' + allowance.id + '">';
                            if (allowance.is_mandatory == 1) {
                                html += '<span class="text-danger">* </span>';
                            }
                            html += allowance.name;
                            if (allowance.name_arabic) {
                                html += ' (' + allowance.name_arabic + ')';
                            }
                            html += '</label>';
                            html += '<input type="number" class="form-control" ';
                            html += 'name="custom_allowances[' + allowance.id + ']" ';
                            html += 'id="custom_allowance_' + allowance.id + '" ';
                            html += 'step="0.01" min="0" ';
                            if (value) {
                                html += 'value="' + value + '" ';
                            }
                            if (allowance.is_mandatory == 1) {
                                html += 'required ';
                            }
                            html += '>';
                            if (allowance.description) {
                                html += '<small class="text-muted">' + allowance.description + '</small>';
                            }
                            html += '</div>';
                            html += '</div>';

                            if (index % 2 === 1 || index === data.allowances.length - 1) {
                                html += '</div>';
                                rowOpen = false;
                            }
                        });

                        $('#custom-allowances-container').html(html);
                        $('#custom-allowances-section').show();
                    } else {
                        $('#custom-allowances-section').hide();
                    }
                });
            };
        });

        $(function() {
            initDataTable('.table-staff-files',
                admin_url + 'staff/file_table/<?= $member->staffid ?>',
                [],
                [],
                { 'staff_id': '<?= $member->staffid ?>' }
            );

            // Add
            $('body').on('click', '[data-target="#add_file"]', function() {
                $('#file-form')[0].reset();
                $('#file-form input[name="id"]').val('');
                $('#document_type_id').val('').selectpicker('refresh');
                $('#file-input').prop('required', true);
                $('#fileModalTitle').text('<?= _l('add_file') ?>');
            });

            // Edit
            $('body').on('click', '.edit-file', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.post(admin_url + 'staff/get_file/' + id, function(response) {
                    var data = JSON.parse(response);
                    $('#file-form input[name="id"]').val(data.id);
                    $('#file-form input[name="staff_id"]').val(data.staff_id);
                    $('#document_type_id').val(data.document_type_id).selectpicker('refresh');
                    $('#file-form input[name="caption"]').val(data.caption);
                    $('#file-input').prop('required', false); // File not required when editing
                    $('#fileModalTitle').text('<?= _l('edit_file') ?>');
                    $('#add_file').modal('show');
                });
            });

            // Delete Staff File
            window.deleteStaffFile = function(id) {
                if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                    window.location.href = admin_url + 'staff/delete_file/' + id;
                }
            };

            // Quick Add Document Type
            $('#quick-add-document-type').on('click', function() {
                var name = prompt('Enter new document type name:');
                if (name && name.trim() !== '') {
                    $.post(admin_url + 'document_types/quick_add', {
                        name: name.trim()
                    })
                    .done(function(response) {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success) {
                            // Add new option to dropdown
                            var newOption = new Option(data.name, data.id, true, true);
                            $('#document_type_id').append(newOption).selectpicker('refresh');
                            alert_float('success', data.message);
                        } else {
                            alert_float('danger', data.message);
                        }
                    })
                    .fail(function() {
                        alert_float('danger', 'Failed to add document type');
                    });
                }
            });

            // Form submission validation
            $('#file-form').on('submit', function(e) {
                var documentTypeId = $('#document_type_id').val();
                var fileInput = $('#file-input').val();
                var isEdit = $('#file-form input[name="id"]').val() !== '';

                if (!documentTypeId) {
                    e.preventDefault();
                    alert_float('danger', 'Please select a document type');
                    return false;
                }

                if (!isEdit && !fileInput) {
                    e.preventDefault();
                    alert_float('danger', 'Please select a file');
                    return false;
                }

                // Set document_type hidden field for backward compatibility
                var selectedText = $('#document_type_id option:selected').text();
                $('#document_type_text').val(selectedText);
            });
        });

        $(function() {
            function calculateOT() {
                var workHours = parseFloat($('#work_hours_per_day').val());
                var basics = parseFloat($('#basics').val());

                if(!isNaN(workHours) && !isNaN(basics) && workHours > 0) {
                    // Determine divisor
                    var divisor = (workHours == 8) ? 240 : 260;
                    var ot = (basics / divisor) * 1.5;

                    // Update the OT field
                    $('#ot').val(ot.toFixed(2)); // round to 2 decimal places
                }
            }

            // Trigger calculation when either field changes
            $('#work_hours_per_day, #basics').on('input', calculateOT);
        });


        // $(function() {
        //     $('#joining_date').on('change', function() {
        //         const selectedDate = $(this).val();
        //         if (selectedDate !== '') {
        //             $.post(admin_url + 'misc/convert_to_hijri', { date: selectedDate })
        //                 .done(function(response) {
        //                     try {
        //                         let data = JSON.parse(response);
        //                         if (data.hijri) {
        //                             $('#joining_date_hijri').val(data.hijri);
        //                         }
        //                     } catch (e) {
        //                         console.error('Hijri conversion failed:', e);
        //                     }
        //                 });
        //         }
        //     });
        // });

        // $(document).on('click', '#assign-job-form button[type="submit"]', function() {
        //     $('#assign_job_modal').modal('hide');

        //     blockArea($('#wrapper'));
    
        //     // simulate unblock after ajax
        //     setTimeout(function(){
        //         unBlockArea($('#wrapper'));
        //     }, 2000);
        // });

        $(document).on('submit', '#assign-job-form', function(e) {
            e.preventDefault(); // prevent normal page reload

            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();
            $('#assign_job_modal').modal('hide');

            $.post(url, data)
                .done(function(res) {
                    var response = typeof res === 'string' ? JSON.parse(res) : res;

                    if (response.success) {
                        alert_float('success', response.message);
                        $('#assign_job_modal').modal('hide');

                        // Reload the table using the stored instance
                        if (staffProjectsTable) {
                            staffProjectsTable.ajax.reload(null, false);
                        }
                    } else {
                        alert_float('warning', response.message);
                    }
                })
                .fail(function() {
                    alert_float('danger', 'Something went wrong');
                });
        });

        $(document).on('click', '#vacation-form button[type="submit"]', function() {
            $('#assign_vacation').modal('hide');

            blockArea($('#wrapper'));
    
            // simulate unblock after ajax
            setTimeout(function(){
                unBlockArea($('#wrapper'));
            }, 2000);
        });

        // Validate and submit pay form via AJAX
        $(document).on('submit', '#pay-form', function(e) {
            e.preventDefault(); // Prevent normal form submission

            var isValid = true;
            var errorMessages = [];

            // Check all required fields
            $('#pay-form [required]').each(function() {
                var $field = $(this);
                var value = $field.val();
                var fieldName = $field.closest('.form-group').find('label').text().trim();

                if (!value || value.trim() === '') {
                    isValid = false;
                    $field.addClass('has-error');
                    errorMessages.push(fieldName + ' is required');
                } else {
                    $field.removeClass('has-error');
                }
            });

            // If validation fails, show error messages
            if (!isValid) {
                alert_float('danger', 'Please fill in all required fields:\n' + errorMessages.join('\n'));
                return false;
            }

            // Validation passed - submit via AJAX
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $('#assign_pay').modal('hide');
            blockArea($('#wrapper'));

            $.post(url, data)
                .done(function() {
                    alert_float('success', '<?= _l('updated_successfully', _l('pay')) ?>');

                    // Reload the pay table
                    if ($('.table-staff-pay').length > 0) {
                        $('.table-staff-pay').DataTable().ajax.reload(null, false);
                    }

                    unBlockArea($('#wrapper'));
                })
                .fail(function(xhr) {
                    alert_float('danger', 'Error saving pay information. Please try again.');
                    console.error('Save pay error:', xhr);
                    unBlockArea($('#wrapper'));
                });

            return false;
        });

        $(function() {
            // Function to handle date conversion
            function handleDateConversion(gregorianField, hijriField) {
                $(gregorianField).on('change', function() {
                    const selectedDate = $(this).val();
                    if (selectedDate !== '') {
                        $.post(admin_url + 'misc/convert_to_hijri', { date: selectedDate })
                            .done(function(response) {
                                try {
                                    let data = JSON.parse(response);
                                    if (data.hijri) {
                                        $(hijriField).val(data.hijri);
                                    }
                                } catch (e) {
                                    console.error('Hijri conversion failed:', e);
                                }
                            });
                    }
                });
            }

            // Set up all date conversions
            handleDateConversion('#joining_date', '#joining_date_hijri');
            handleDateConversion('#atm_expiry', '#atm_expiry_hijri');
            handleDateConversion('#iqama_expiry', '#iqama_expiry_hijri');
            handleDateConversion('#ajeer_expiry', '#ajeer_expiry_hijri');
            handleDateConversion('#insurance_expiry', '#insurance_expiry_hijri');
            handleDateConversion('#health_card_expiry', '#health_card_expiry_hijri');
            handleDateConversion('#visa_expiry', '#visa_expiry_hijri');

            // Make all hijri fields readonly
            $('[id$="_hijri"]').prop('readonly', true);

            // Translation functionality
            $('#translate-to-arabic').on('click', function() {
                var englishName = $('input[name="name"]').val();

                if (!englishName || englishName.trim() === '') {
                    alert('Please enter an English name first.');
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Translating...');

                $.post(admin_url + 'staff/translate', {
                    text: englishName,
                    target_lang: 'ar'
                })
                .done(function(response) {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('input[name="name_arabic"]').val(data.translated_text);
                        alert_float('success', 'Translation completed successfully');
                    } else {
                        alert_float('danger', data.message || 'Translation failed');
                    }
                })
                .fail(function() {
                    alert_float('danger', 'Translation request failed');
                })
                .always(function() {
                    btn.prop('disabled', false).html(originalText);
                });
            });

            $('#translate-to-english').on('click', function() {
                var arabicName = $('input[name="name_arabic"]').val();

                if (!arabicName || arabicName.trim() === '') {
                    alert('Please enter an Arabic name first.');
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Translating...');

                $.post(admin_url + 'staff/translate', {
                    text: arabicName,
                    target_lang: 'en'
                })
                .done(function(response) {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('input[name="name"]').val(data.translated_text);
                        alert_float('success', 'Translation completed successfully');
                    } else {
                        alert_float('danger', data.message || 'Translation failed');
                    }
                })
                .fail(function() {
                    alert_float('danger', 'Translation request failed');
                })
                .always(function() {
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });

        $(function() {
            $('select[name="role"]').on('change', function() {
                var roleid = $(this).val();
                init_roles_permissions(roleid, true);
            });

            $('input[name="administrator"]').on('change', function() {
                var checked = $(this).prop('checked');
                var isNotStaffMember = $('.is-not-staff');
                if (checked == true) {
                    isNotStaffMember.addClass('hide');
                    $('.roles').find('input').prop('disabled', true).prop('checked', false);
                } else {
                    isNotStaffMember.removeClass('hide');
                    isNotStaffMember.find('input').prop('checked', false);
                    $('.roles').find('.capability').not('[data-not-applicable="true"]').prop('disabled',
                        false)
                }
            });

            $('#is_not_staff').on('change', function() {
                var checked = $(this).prop('checked');
                var row_permission_leads = $('tr[data-name="leads"]');
                if (checked == true) {
                    row_permission_leads.addClass('hide');
                    row_permission_leads.find('input').prop('checked', false);
                } else {
                    row_permission_leads.removeClass('hide');
                }
            });

            init_roles_permissions();

            appValidateForm($('.staff-form'), {
                name: 'required',
                stafftype_id: 'required', // âœ… Staff type is required
                // 'skills[]': {
                //     required: true,
                //     minlength: 1 // âœ… At least one skill must be selected
                // },
                // lastname: 'required',
                // username: 'required',
                // password: {
                //     required: {
                //         depends: function(element) {
                //             return ($('input[name="isedit"]').length == 0) ? true : false
                //         }
                //     }
                // },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: admin_url + "misc/staff_email_exists",
                        type: 'post',
                        data: {
                            email: function() {
                                return $('input[name="email"]').val();
                            },
                            memberid: function() {
                                return $('input[name="memberid"]').val();
                            }
                        }
                    }
                }
            });
        });

        $(document).on('click', '.edit-job, .edit-project', function () {
            const modal = $('#assign_job_modal');
            const data = $(this).data();

            modal.find('form').attr('action', '<?= admin_url('projects/assign_job') ?>');

            modal.find('select[name="project_id"]').val(data.project).change();
            modal.find('select[name="skills[]"]').val(data.skills.toString().split(',')).change();
            modal.find('input[name="badge"]').val(data.badge);
            modal.find('input[name="start_date"]').val(data.start);
            modal.find('input[name="end_date"]').val(data.end);
            modal.find('select[name="equipment"]').val(data.equipment).change();
            modal.find('input[name="regular_rate"]').val(data.regular);
            modal.find('input[name="overtime_rate"]').val(data.overtime);
            modal.find('input[name="rate_type"][value="' + data.type + '"]').prop('checked', true);

            modal.find('input[name="edit_id"]').remove();
            modal.find('form').append('<input type="hidden" name="edit_id" value="' + data.id + '">');

            modal.modal('show');
        });

        // Delete project assignment
        $(document).on('click', '.delete-project-assignment', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var projectName = $(this).data('project');

            if (confirm('<?= _l('confirm_delete_project_assignment') ?>' + ' (' + projectName + ')?')) {
                $.ajax({
                    url: admin_url + 'staff/delete_project_assignment/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            $('.table-staff-projects').DataTable().ajax.reload();
                        } else {
                            // Show detailed reasons if available
                            if (response.reasons_html) {
                                var msg = '<strong>' + response.message + '</strong><br><br>' +
                                          '<strong>Reasons:</strong>' + response.reasons_html +
                                          '<br><small>Please contact your administrator if you need to remove this assignment.</small>';
                                alert_float('danger', msg);
                            } else {
                                alert_float('danger', response.message);
                            }
                        }
                    },
                    error: function() {
                        alert_float('danger', '<?= _l('error_deleting_project_assignment') ?>');
                    }
                });
            }
        });

        $(document).ready(function () {
            if (window.location.hash === "#staff_projects") {
                $('a[href="#staff_projects"]').tab('show');
            }

            // Auto-populate modal fields when opening for new assignment
            $('#assign_job_modal').on('show.bs.modal', function (e) {
                var modal = $(this);

                // Check if this is a new assignment (not an edit)
                // If edit button triggered the modal, skip auto-population
                if ($(e.relatedTarget).hasClass('edit-job') || $(e.relatedTarget).hasClass('edit-project')) {
                    return; // Let the edit handler populate the fields
                }

                // Get staff ID
                var staffId = <?= isset($member) ? $member->staffid : 0 ?>;

                if (!staffId) return;

                // Fetch current pay data via AJAX
                $.ajax({
                    url: '<?= admin_url("staff/get_current_pay/") ?>' + staffId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.error('Error fetching pay data:', data.error);
                            return;
                        }

                        // Populate skills
                        if (data.skills) {
                            var skillsArray = data.skills.toString().split(',');
                            modal.find('select[name="skills[]"]').val(skillsArray).trigger('change');
                        }

                        // Populate regular rate (basic pay)
                        if (data.basic_pay) {
                            modal.find('input[name="regular_rate"]').val(data.basic_pay);
                        }

                        // Populate overtime rate
                        if (data.overtime_pay) {
                            modal.find('input[name="overtime_rate"]').val(data.overtime_pay);
                        }

                        // Populate rate type
                        if (data.payout_type) {
                            modal.find('input[name="rate_type"][value="' + data.payout_type + '"]').prop('checked', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            });

            // Clear modal fields when closed
            $('#assign_job_modal').on('hidden.bs.modal', function () {
                var modal = $(this);
                // Reset form
                modal.find('form')[0].reset();
                // Remove edit ID if exists
                modal.find('input[name="edit_id"]').remove();
                // Reset form action
                modal.find('form').attr('action', '<?= admin_url('projects/assign_job') ?>');
                // Refresh select2/selectpicker
                modal.find('select').trigger('change');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const staffTypeSelect = document.querySelector('select[name="stafftype_id"]');
            const supplierContainer = document.getElementById('supplier_container');

            function toggleSupplierVisibility() {
                if (staffTypeSelect.value === '2') {
                    supplierContainer.style.display = 'block';
                } else {
                    supplierContainer.style.display = 'none';
                }
            }

            // Run on page load to handle pre-selected staff type
            toggleSupplierVisibility();

            // Add event listener for staff type changes
            staffTypeSelect.addEventListener('change', toggleSupplierVisibility);
        });


        document.addEventListener('DOMContentLoaded', function() {
            const staffTypeSelect = document.querySelector('select[name="stafftype_id"]');
            const ownemployeeContainer = document.getElementById('ownemployee_container');
            const $ownEmployeeSelect = $('#ownemployee_type_select');
        
            function toggleOwnemployeeVisibility() {
                if (staffTypeSelect.value === '1') {
                    ownemployeeContainer.style.display = 'block';
        
                    // refresh selectpicker after container becomes visible
                    $ownEmployeeSelect.selectpicker('refresh');
                } else {
                    ownemployeeContainer.style.display = 'none';
                }
            }
        
            // Run on page load to handle pre-selected staff type
            toggleOwnemployeeVisibility();
        
            // Add event listener for staff type changes
            staffTypeSelect.addEventListener('change', toggleOwnemployeeVisibility);
        });

        $('.table-timesheets').on('click', '.view-timesheet', function(e) {
            e.preventDefault();
            var timesheetId = $(this).data('id');
            var staffName = $(this).closest('tr').find('td:first').text();
            var monthYear = $(this).closest('tr').find('td:eq(7)').text();

            $.ajax({
                url: admin_url + 'timesheet/get_details/' + timesheetId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var details = response.data;
                        var html = '';
                        if (details.length > 0) {
                            details.forEach(function(detail) {
                                var date = new Date(detail.work_date);
                                var day = date.toLocaleDateString('en-US', { weekday: 'short' });
                                var daySuffix = ['th', 'st', 'nd', 'rd'][date.getDate() % 10 > 3 ? 0 : (date.getDate() % 100 - date.getDate() % 10 != 10) * date.getDate() % 10];
                                var formattedDate = date.getDate() + (daySuffix || 'th') + ' ' + date.toLocaleDateString('en-US', { month: 'long' }) + ' ' + date.getFullYear() + ' (' + day + ')';
                                var hoursClass = ['A', 'PH', 'F'].includes(detail.regular_hours) ? 'text-danger' : '';
                                html += '<tr>';
                                html += '<td>' + formattedDate + '</td>';
                                html += '<td class="' + hoursClass + '">' + (detail.regular_hours || '') + '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="2">No details available</td></tr>';
                        }
                        $('#timesheetDetailsBody').html(html);
                        $('#modalStaffName').text(staffName + ' - ' + monthYear);
                        $('#timesheetDetailsModal').modal('show');
                    } else {
                        alert('Error loading details: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while loading details.');
                    console.error(error);
                }
            });
        });
    </script>
    <style>
    /* Pay form validation error styling */
    #pay-form .has-error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    #pay-form .has-error:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5);
    }
.modal-content {
    border-radius: 8px;
}
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.modal-title {
    font-size: 1.25rem;
    font-weight: 500;
}
.modal-body .table {
    margin-bottom: 0;
}
.modal-body .table th,
.modal-body .table td {
    text-align: center;
    padding: 8px;
    vertical-align: middle;
}
.text-danger {
    color: #dc3545 !important; /* Red text for A, PH, F */
}
.table-timesheets th,
.table-timesheets td {
    text-align: center;
    padding: 8px;
}
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
    }
    .modal-body .table th,
    .modal-body .table td {
        font-size: 12px;
    }
}

.sticky-name-section {
    position: sticky;
    top: 60px; /* Adjust based on your Perfex header height */
    z-index: 1000; /* Ensure it stays above other content */
    background-color: #fff; /* White background to prevent transparency */
    padding: 10px 0; /* Padding for better spacing */
    margin: 0; /* Remove default margins to align properly */
}

.sticky-nav-tabs {
    position: sticky;
    top: 100px; /* Adjust to account for the name section height and header */
    z-index: 999; /* Slightly lower than name section to avoid overlap issues */
    background-color: #fff; /* White background for visibility */
    padding: 10px 0; /* Consistent padding */
}
</style>

<!-- ===================== Staff Type Modal ===================== -->
<div class="modal fade" id="staffTypeModal" tabindex="-1" role="dialog" aria-labelledby="staffTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staffTypeModalLabel">Add New Staff Type</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="newStaffTypeName" class="form-control" placeholder="Enter staff type name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveStaffTypeBtn">Save</button>
      </div>
    </div>
  </div>
</div>
 
<script>
$(function() {
  var $staffSelect = $('select[name="stafftype_id"]');
 
  // Remove old fake separator
  $staffSelect.find('option').each(function() {
      if ($(this).text() === '---separator---') {
          $(this).remove();
      }
  });
 
  // Ensure separator exists above "Add New"
  if ($staffSelect.find('option[value="separator"]').length === 0) {
      if ($staffSelect.find('option[value="add_new"]').length) {
          $staffSelect.find('option[value="add_new"]').before('<option value="separator" disabled class="dropdown-divider"></option>');
      } else {
          $staffSelect.append('<option value="separator" disabled class="dropdown-divider"></option>');
      }
  }
 
  // Ensure "Add New Staff Type" exists
  if ($staffSelect.find('option[value="add_new"]').length === 0) {
      $staffSelect.append('<option value="add_new">➕ Add New Staff Type</option>');
  }
 
  // Refresh selectpicker
  $staffSelect.selectpicker('refresh');
 
  // Detect "Add New Staff Type"
  $staffSelect.on('changed.bs.select', function (e, clickedIndex) {
      if (typeof clickedIndex === 'undefined') return;
 
      var selectedVal = $(this).find('option').eq(clickedIndex).val();
 
      if (selectedVal === 'add_new') {
          // Unselect it
          $(this).find('option[value="add_new"]').prop('selected', false);
          $staffSelect.selectpicker('refresh');
 
          // Show modal
          $('#staffTypeModal').modal('show');
      }
  });
 
  // Handle Save
  $('#saveStaffTypeBtn').on('click', function() {
      var staffTypeName = $('#newStaffTypeName').val().trim();
      if (!staffTypeName) {
          alert('Please enter a staff type name');
          return;
      }
 
      $.post("<?= admin_url('staff/add_staff_type'); ?>", { name: staffTypeName }, function(resp){
          var data = {};
          try { data = JSON.parse(resp); } catch(e) {}
 
          if (data.success && data.id) {
              // Insert before separator
              var newOption = new Option(data.name, data.id, true, true);
              $staffSelect.find('option[value="separator"]').before(newOption);
 
              // Keep "Add New" at end
              $staffSelect.find('option[value="add_new"]').remove();
              $staffSelect.append('<option value="add_new">➕ Add New Staff Type</option>');
 
              $staffSelect.selectpicker('refresh');
              $('#staffTypeModal').modal('hide');
              $('#newStaffTypeName').val('');
          } else {
              alert(data.message ? data.message : "Could not add staff type.");
          }
      });
  });
});
</script>

<!-- ===================== Skill Modal ===================== -->
<div class="modal fade" id="skillModal" tabindex="-1" role="dialog" aria-labelledby="skillModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="skillModalLabel">Add New Skill</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="newSkillName" class="form-control" placeholder="Enter skill name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveSkillBtn">Save</button>
      </div>
    </div>
  </div>
</div>
 
 
<script>
$(function() {
  var $select = $('select[name="skills[]"]');
 
  // Remove old fake separator option
  $select.find('option').each(function() {
      if ($(this).text() === '---separator---') {
          $(this).remove();
      }
  });
 
  // Ensure separator exists above "Add New Skill"
  if ($select.find('option[value="separator"]').length === 0) {
      if ($select.find('option[value="add_new"]').length) {
          $select.find('option[value="add_new"]').before('<option value="separator" disabled class="dropdown-divider"></option>');
      } else {
          $select.append('<option value="separator" disabled class="dropdown-divider"></option>');
      }
  }
 
  // Ensure "Add New Skill" always exists at end
  if ($select.find('option[value="add_new"]').length === 0) {
      $select.append('<option value="add_new">➕ Add New Skill</option>');
  }
 
  // Refresh selectpicker
  $select.selectpicker('refresh');
 
  // Detect when user selects "Add New Skill"
  $select.on('changed.bs.select', function (e, clickedIndex) {
      if (typeof clickedIndex === 'undefined') return;
 
      var selectedVal = $(this).find('option').eq(clickedIndex).val();
 
      if (selectedVal === 'add_new') {
          // Unselect it
          $(this).find('option[value="add_new"]').prop('selected', false);
          $select.selectpicker('refresh');
 
          // ✅ Show Bootstrap modal instead of prompt
          $('#skillModal').modal('show');
      }
  });
 
  // Handle save button in Skill modal
  $('#saveSkillBtn').on('click', function() {
      var skillName = $('#newSkillName').val().trim();
      if (!skillName) {
          alert('Please enter a skill name');
          return;
      }
 
      $.post("<?= admin_url('staff/add_skill'); ?>", { name: skillName }, function(resp){
          var data = {};
          try { data = JSON.parse(resp); } catch(e) {}
 
          if (data.success && data.id) {
              // Insert before separator
              var newOption = new Option(data.name, data.id, true, true);
              $select.find('option[value="separator"]').before(newOption);
 
              // Always ensure "Add New Skill" stays last
              $select.find('option[value="add_new"]').remove();
              $select.append('<option value="add_new">➕ Add New Skill</option>');
 
              $select.selectpicker('refresh');
              $('#skillModal').modal('hide');
              $('#newSkillName').val('');
          } else {
              alert(data.message ? data.message : "Could not add skill.");
          }
      });
  });
});
</script>

<!-- ===================== Own Employee Type Modal ===================== -->
<div class="modal fade" id="ownEmployeeTypeModal" tabindex="-1" role="dialog" aria-labelledby="ownEmployeeTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ownEmployeeTypeModalLabel">Add New Own Employee Type</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="newOwnEmployeeTypeName" class="form-control" placeholder="Enter type name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveOwnEmployeeTypeBtn">Save</button>
      </div>
    </div>
  </div>
</div>
 
 
<!-- ================= Own Employee Type Script ================= -->
 
 
<script>
$(function() {
    var $select = $('#ownemployee_id'); // ✅ matches PHP ID
    console.log("Dropdown found?", $select.length); // debug check
 
    if ($select.length) {
        // Add separator if not exists
        if ($select.find('option[value="separator"]').length === 0) {
            $select.append('<option value="separator" disabled class="dropdown-divider"></option>');
        }
 
        // Add "Add New" if not exists
        if ($select.find('option[value="add_new"]').length === 0) {
            $select.append('<option value="add_new">➕ Add New Own Employee Type</option>');
        }
 
        $select.selectpicker('refresh');
 
        // Detect selection of "Add New"
        $select.on('changed.bs.select', function (e, clickedIndex) {
            if (typeof clickedIndex === 'undefined') return;
            var selectedVal = $(this).find('option').eq(clickedIndex).val();
 
            if (selectedVal === 'add_new') {
                $(this).find('option[value="add_new"]').prop('selected', false);
                $select.selectpicker('refresh');
                $('#ownEmployeeTypeModal').modal('show');
            }
        });
 
        // Save button handler
        $('#saveOwnEmployeeTypeBtn').on('click', function() {
            var typeName = $('#newOwnEmployeeTypeName').val().trim();
            if (!typeName) {
                alert('Please enter a type name');
                return;
            }
 
            $.post("<?= admin_url('staff/add_ownemployee_type'); ?>", { name: typeName }, function(resp){
                var data = {};
                try { data = JSON.parse(resp); } catch(e) {}
 
                if (data.success && data.id) {
                    var newOption = new Option(data.name, data.id, true, true);
                    $select.find('option[value="separator"]').before(newOption);
 
                    $select.find('option[value="add_new"]').remove();
                    $select.append('<option value="add_new">➕ Add New Own Employee Type</option>');
 
                    $select.selectpicker('refresh');
                    $('#ownEmployeeTypeModal').modal('hide');
                    $('#newOwnEmployeeTypeName').val('');
                } else {
                    alert(data.message ? data.message : "Could not add own employee type.");
                }
            });
        });
    }
});
</script>

<!-- ===================== Company Type Modal ===================== -->
<div class="modal fade" id="companyTypeModal" tabindex="-1" role="dialog" aria-labelledby="companyTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="companyTypeModalLabel">Add New Company Type</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="newCompanyTypeName" class="form-control" placeholder="Enter company type name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveCompanyTypeBtn">Save</button>
      </div>
    </div>
  </div>
</div>
 
<script>
$(function() {
  // ✅ Corrected selector (company_type, NOT company_type[])
  var $select = $('select[name="companytype_id"]');
 
  // Remove old fake separator option
  $select.find('option').each(function() {
      if ($(this).text() === '---separator---') {
          $(this).remove();
      }
  });
 
  // Ensure separator exists above "Add New Company Type"
  if ($select.find('option[value="separator"]').length === 0) {
      if ($select.find('option[value="add_new"]').length) {
          $select.find('option[value="add_new"]').before('<option value="separator" disabled class="dropdown-divider"></option>');
      } else {
          $select.append('<option value="separator" disabled class="dropdown-divider"></option>');
      }
  }
 
  // Ensure "Add New Company Type" always exists at end
  if ($select.find('option[value="add_new"]').length === 0) {
      $select.append('<option value="add_new">➕ Add New Company Type</option>');
  }
 
  // Refresh selectpicker
  $select.selectpicker('refresh');
 
  // Detect when user selects "Add New"
  $select.on('changed.bs.select', function (e, clickedIndex) {
      if (typeof clickedIndex === 'undefined') return;
 
      var selectedVal = $(this).find('option').eq(clickedIndex).val();
 
      if (selectedVal === 'add_new') {
          // Unselect it
          $(this).find('option[value="add_new"]').prop('selected', false);
          $select.selectpicker('refresh');
 
          // ✅ Show Bootstrap modal instead of prompt
          $('#companyTypeModal').modal('show');
      }
  });
 
  // Handle save button in modal
  $('#saveCompanyTypeBtn').on('click', function() {
      var companyTypeName = $('#newCompanyTypeName').val().trim();
      if (!companyTypeName) {
          alert('Please enter a company type name');
          return;
      }
 
      $.post("<?= admin_url('staff/add_company_type'); ?>", { name: companyTypeName }, function(resp){
          var data = {};
          try { data = JSON.parse(resp); } catch(e) {}
 
          if (data.success && data.id) {
              // Insert before separator
              var newOption = new Option(data.name, data.id, true, true);
              $select.find('option[value="separator"]').before(newOption);
 
              // Always ensure "Add New Company Type" stays last
              $select.find('option[value="add_new"]').remove();
              $select.append('<option value="add_new">➕ Add New Company Type</option>');
 
              $select.selectpicker('refresh');
              $('#companyTypeModal').modal('hide');
              $('#newCompanyTypeName').val('');
          } else {
              alert(data.message ? data.message : "Could not add company type.");
          }
      });
  });
});
</script>
 
 
 
 
<style>
  .bootstrap-select .dropdown-menu .dropdown-divider {
    height: 1px !important;
    margin: 8px 0 !important;
    overflow: hidden !important;
    background-color: #e9ecef !important;
  }
</style>
 
 
 
 
<style>
.bootstrap-select .dropdown-menu .dropdown-divider {
    height: 1px !important;
    margin: 6px 0 !important;
    padding: 0 !important;
    background-color: #ccc !important;
    display: block !important;
    width: 100% !important;
    content: "" !important;  /* no text */
}

.nav-tabs-vertical {
    border-right: 1px solid #ddd;
    padding-right: 15px;
}

.nav-tabs-vertical > li {
    display: block; /* Ensures each tab takes its own row */
    margin-bottom: 10px; /* Space between tabs */
    width: 100%; /* Full width for each tab */
}

.nav-tabs-vertical > li > a {
    display: block;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;
    color: #333;
    font-weight: 500;
    text-align: left;
    transition: all 0.3s ease;
}

.nav-tabs-vertical > li.active > a,
.nav-tabs-vertical > li > a:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

.tab-content {
    padding-left: 15px;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .nav-tabs-vertical {
        border-right: none;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }

    .nav-tabs-vertical > li {
        margin-bottom: 8px;
    }
}
 
</style>
    </body>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable@11.1.0/dist/handsontable.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data.min.js"></script>
<script>
$(function() {
    const staffId = "<?php echo $member->staffid; ?>";

    function loadStaffTimesheets() {
        $.getJSON(admin_url + 'staff_timesheet/get_grids/' + staffId, function(grids) {
            const $container = $('#staff_timesheet_grids');
            $container.empty();

            if (!grids.length) {
                $container.append('<div class="alert alert-info text-center">No timesheets found for this staff.</div>');
                return;
            }

            grids.forEach(grid => {
                const gridId = 'grid_' + grid.timesheet_id;
                const gridTitle = `
                    ${grid.project_name ? grid.project_name : 'No Project'} 
                    <small class="text-muted">(${moment(grid.month_year).format('MMMM YYYY')})</small>
                `;

                const $card = $(`
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${gridTitle}</h5>
                            <div>
                                <button class="btn btn-sm btn-primary save-grid" style="margin-bottom:15px;" data-id="${grid.timesheet_id}">
                                    <i class="fa-regular fa-floppy-disk"></i> Save
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="${gridId}" class="hot-container"></div>
                        </div>
                    </div>
                `);

                $container.append($card);

                // Fetch timesheet data for the grid
                $.getJSON(admin_url + 'staff_timesheet/get_details/' + grid.timesheet_id, function(details) {
                    const container = document.getElementById(gridId);

                    // Prepare dates and map
                    const dates = details.map(d => d.work_date);
                    const regularRow = details.map(d => parseFloat(d.regular_hours) || '');
                    const overtimeRow = details.map(d => parseFloat(d.overtime_hours) || '');

                    // Add total column (initially 0, will calculate in afterChange)
                    regularRow.push(0);
                    overtimeRow.push(0);

                    // Prepare column headers
                    const colHeaders = dates.map(d => {
                        const dt = new Date(d + 'T00:00:00');
                        const dayNum = dt.getDate();
                        const weekday = dt.toLocaleDateString('en-US', { weekday: 'short' });
                        const monthName = dt.toLocaleDateString('en-US', { month: 'short' });
                        const suffix = (dayNum===1)?'st':(dayNum===2)?'nd':(dayNum===3)?'rd':'th';
                        return `${dayNum}${suffix} ${monthName} (${weekday})`;
                    });
                    colHeaders.push('Total');

                    const hot = new Handsontable(container, {
                        data: [regularRow, overtimeRow],
                        rowHeaders: ['Regular', 'Overtime'],
                        rowHeaderWidth: 120,
                        colHeaders: colHeaders,
                        stretchH: 'all',
                        contextMenu: true,
                        licenseKey: 'non-commercial-and-evaluation',
                        columns: dates.map(() => ({ type: 'numeric' })).concat({ type: 'numeric', readOnly: true }),
                        height: 120,
                        afterChange: function(changes, source) {
                            if (!changes || source === 'loadData') return;
                            changes.forEach(([row, col]) => {
                                if (col < dates.length) {
                                    let sum = 0;
                                    for (let c = 0; c < dates.length; c++) {
                                        sum += parseFloat(hot.getDataAtCell(row,c)) || 0;
                                    }
                                    hot.setDataAtCell(row, dates.length, sum, 'updateTotal');
                                }
                            });
                        }
                    });

                    // Initialize totals
                    [0,1].forEach(r => {
                        let sum = 0;
                        for (let c=0;c<dates.length;c++){
                            sum += parseFloat(hot.getDataAtCell(r,c)) || 0;
                        }
                        hot.setDataAtCell(r, dates.length, sum, 'updateTotal');
                    });

                    // Save button
                    $card.find('.save-grid').on('click', function() {
                    const hotData = hot.getData(); // 2D array: [ [Regular], [Overtime] ]
                    const dates = hot.getColHeader().slice(0, -1); // remove Total column

                    const rows = dates.map((dateStr, idx) => ({
                        work_date: moment(dateStr, 'D MMM (ddd)').format('YYYY-MM-DD'), // parse date from header
                        regular_hours: hotData[0][idx] !== '' ? parseFloat(hotData[0][idx]) : 0,
                        overtime_hours: hotData[1][idx] !== '' ? parseFloat(hotData[1][idx]) : 0
                    }));

                    const postData = {
                        timesheet_id: grid.timesheet_id,
                        rows: JSON.stringify(rows)
                    };

                    $.post(admin_url + 'staff_timesheet/save_timesheet', postData, function(res) {
                        alert_float('success', 'Timesheet saved successfully.');
                    }).fail(() => {
                        alert_float('danger', 'Error saving timesheet.');
                    });
                });
                });
            });
        });
    }

    // Initialize load
    loadStaffTimesheets();
});
</script>
    </html>