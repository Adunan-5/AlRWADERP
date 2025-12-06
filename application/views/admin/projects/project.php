<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>

.checkbox-margin-top
{
	margin-top:22px !important;
}

</style>


<div id="wrapper">
    <div class="content">
        <?= form_open($this->uri->uri_string(), ['id' => 'project_form']); ?>

        <!-- <div class="tw-max-w-4xl tw-mx-auto"> -->
        <div class="tw-mx-auto">
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                <?= e($title); ?>
            </h4>
            <div class="panel_s">
                <div class="panel-body">
<div class="row">
<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">General Settings</h4>
</div>
</div>


                    <div class="horizontal-scrollable-tabs panel-full-width-tabs" style="display:none;">
                        <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                        <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                        <div class="horizontal-tabs">
                            <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#tab_project" aria-controls="tab_project" role="tab" data-toggle="tab">
                                        <?= _l('project'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_settings" aria-controls="tab_settings" role="tab" data-toggle="tab">
                                        <?= _l('project_settings'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content tw-mt-3">
                        <div role="tabpanel" class="tab-pane active" id="tab_project">


                            <?php
                        $disable_type_edit = '';
if (isset($project)) {
    if ($project->billing_type != 1) {
        if (total_rows(db_prefix() . 'tasks', ['rel_id' => $project->id, 'rel_type' => 'project', 'billable' => 1, 'billed' => 1]) > 0) {
            $disable_type_edit = 'disabled';
        }
    }
}
?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->name : ''); ?>
                                <?= render_input('name', 'project_name', $value); ?>
                            </div>
                           
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->arabicname : ''); ?>
                                    <?= render_input('arabicname', 'project_name_arabic', $value); ?>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->code : ''); ?>
                                <?= render_input('code', 'project_code', $value); ?>
                            </div>



                                <div class="col-md-6 checkbox-margin-top">
                                                <div class="checkbox checkbox-margin-bottom">
                                                    <?php
                                                    $checked = '';
                                                    $checked = isset($project) && $project->manpower_project == 1 ? 'checked' : '';
                                                    ?>
                                                        <input type="checkbox" name="manpower_project" id="manpower_project" <?=$checked?> value="1">
                                                    <label for="manpower_project"><?= _l('project_manpower_project') ?></label>
                                                </div>
                                 </div>
                        </div>



                                <div class="row"> 
                                                <div class="col-md-6">
                                                        <?php
                                                            // If you’re in edit mode
                                                            $allowances = isset($project) ? $project->allowances  : '';
                                                            ?>
                                                            <div class="form-group contact-direction-option">
                                                            <label for="allowances "><?= _l('project_allowances ') ?></label>
                                                            <select class="selectpicker"
                                                                    data-none-selected-text="System Default"
                                                                    data-width="100%"
                                                                    name="allowances"
                                                                    id="allowances">
                                                                <option value="" <?= $allowances == '' ? 'selected' : '' ?>></option>
                                                                <option value="MEWA" <?= $allowances == 'MEWA' ? 'selected' : '' ?>>MEWA</option>
                                                            
                                                            </select>
                                                            </div>

                                                </div>

                                            <div class="col-md-6">
                                                        <?php
                                                            // If you’re in edit mode
                                                            $monthly_invoicing_period_end_day = isset($project) ? $project->monthly_invoicing_period_end_day : '';
                                                            ?>
                                                            <div class="form-group contact-direction-option">
                                                            <label for="monthly_invoicing_period_end_day"><?= _l('project_monthly_invoicing_period_end_day') ?></label>
                                                            <select class="selectpicker"
                                                                    data-none-selected-text="System Default"
                                                                    data-width="100%"
                                                                    name="monthly_invoicing_period_end_day"
                                                                    id="monthly_invoicing_period_end_day">
                                                                <option value="" <?= $monthly_invoicing_period_end_day == '' ? 'selected' : '' ?>></option>
                                                                <option value="End_of_month" <?= $monthly_invoicing_period_end_day == 'End_of_month' ? 'selected' : '' ?>>End Of Month</option>
                                                                <option value="01" <?= $monthly_invoicing_period_end_day == '01' ? 'selected' : '' ?>>01</option>
                                                                <option value="02" <?= $monthly_invoicing_period_end_day == '02' ? 'selected' : '' ?>>02</option>
                                                                <option value="03" <?= $monthly_invoicing_period_end_day == '03' ? 'selected' : '' ?>>03</option>
                                                                <option value="04" <?= $monthly_invoicing_period_end_day == '04' ? 'selected' : '' ?>>04</option>
                                                                <option value="05" <?= $monthly_invoicing_period_end_day == '05' ? 'selected' : '' ?>>05</option>
                                                                <option value="06" <?= $monthly_invoicing_period_end_day == '06' ? 'selected' : '' ?>>06</option>
                                                                <option value="07" <?= $monthly_invoicing_period_end_day == '07' ? 'selected' : '' ?>>07</option>
                                                                <option value="08" <?= $monthly_invoicing_period_end_day == '08' ? 'selected' : '' ?>>08</option>
                                                                <option value="09" <?= $monthly_invoicing_period_end_day == '09' ? 'selected' : '' ?>>09</option>
                                                                <option value="10" <?= $monthly_invoicing_period_end_day == '10' ? 'selected' : '' ?>>10</option>
                                                                <option value="11" <?= $monthly_invoicing_period_end_day == '11' ? 'selected' : '' ?>>11</option>
                                                                <option value="12" <?= $monthly_invoicing_period_end_day == '12' ? 'selected' : '' ?>>12</option>
                                                                <option value="13" <?= $monthly_invoicing_period_end_day == '13' ? 'selected' : '' ?>>13</option>
                                                                <option value="14" <?= $monthly_invoicing_period_end_day == '14' ? 'selected' : '' ?>>14</option>
                                                                <option value="15" <?= $monthly_invoicing_period_end_day == '15' ? 'selected' : '' ?>>15</option>
                                                                <option value="16" <?= $monthly_invoicing_period_end_day == '16' ? 'selected' : '' ?>>16</option>
                                                                <option value="17" <?= $monthly_invoicing_period_end_day == '17' ? 'selected' : '' ?>>17</option>
                                                                <option value="18" <?= $monthly_invoicing_period_end_day == '18' ? 'selected' : '' ?>>18</option>
                                                                <option value="19" <?= $monthly_invoicing_period_end_day == '19' ? 'selected' : '' ?>>19</option>
                                                                <option value="20" <?= $monthly_invoicing_period_end_day == '20' ? 'selected' : '' ?>>20</option>
                                                                <option value="21" <?= $monthly_invoicing_period_end_day == '21' ? 'selected' : '' ?>>21</option>
                                                                <option value="22" <?= $monthly_invoicing_period_end_day == '22' ? 'selected' : '' ?>>22</option>
                                                                <option value="23" <?= $monthly_invoicing_period_end_day == '23' ? 'selected' : '' ?>>23</option>
                                                                <option value="24" <?= $monthly_invoicing_period_end_day == '24' ? 'selected' : '' ?>>24</option>
                                                                <option value="25" <?= $monthly_invoicing_period_end_day == '25' ? 'selected' : '' ?>>25</option>
                                                                <option value="26" <?= $monthly_invoicing_period_end_day == '26' ? 'selected' : '' ?>>26</option>
                                                                <option value="27" <?= $monthly_invoicing_period_end_day == '27' ? 'selected' : '' ?>>27</option>
                                                                <option value="28" <?= $monthly_invoicing_period_end_day == '28' ? 'selected' : '' ?>>28</option>
                                                                <option value="29" <?= $monthly_invoicing_period_end_day == '29' ? 'selected' : '' ?>>29</option>
                                                            
                                                            </select>
                                                            </div>
                                                </div>
                                </div>





                            <div class="row">
                                <div class="col-md-6">
                                    <?php $value = (isset($project) ? _d($project->start_date) : _d(date('Y-m-d'))); ?>
                                    <?= render_date_input('start_date', 'project_start_date', $value); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php $value = (isset($project) ? _d($project->deadline) : ''); ?>
                                    <?= render_date_input('deadline', 'project_end_date', $value); ?>
                                </div>
                            </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->po_number : ''); ?>
                                <?= render_input('po_number', 'project_po_number', $value); ?>
                            </div>


                             <div class="col-md-6 checkbox-margin-top">
                                                <div class="checkbox checkbox-margin-bottom">
                                                    <?php
                                                    $checked = '';
                                                    $checked = isset($project) && $project->reserve_a_series_of_invoice_number_for_this_project == 1 ? 'checked' : '';
                                                    ?>
                                                        <input type="checkbox" name="reserve_a_series_of_invoice_number_for_this_project" id="reserve_a_series_of_invoice_number_for_this_project" <?=$checked?> value="1">
                                                    <label for="reserve_a_series_of_invoice_number_for_this_project"><?= _l('project_reserve_a_series_of_invoice_number_for_this_project') ?></label>
                                                </div>
                                 </div>
                        </div>

                         <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->invoice_due_in_days : ''); ?>
                                <?= render_input('invoice_due_in_days', 'project_invoice_due_in_days', $value); ?>
                            </div>

                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->invoice_number_prefix : ''); ?>
                                <?= render_input('invoice_number_prefix', 'project_invoice_number_prefix', $value); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->regular_hours_label : ''); ?>
                                <?= render_input('regular_hours_label', 'project_regular_hours_label', $value); ?>
                            </div>

                            <div class="col-md-6">
                                <?php $value = (isset($project) ? $project->overtime_label : ''); ?>
                                <?= render_input('overtime_label', 'project_overtime_label', $value); ?>
                            </div>
                        </div>


                         <div class="row">
                                <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="clientid" class="control-label"><?= _l('project_customer'); ?></label>
                                            <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                                class="ajax-search"
                                                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                                <?php $selected = (isset($project) ? $project->clientid : '');
                                                    if ($selected == '') {
                                                        $selected = ($customer_id ?? '');
                                                    }
                                                    if ($selected != '') {
                                                        $rel_data = get_relation_data('customer', $selected);
                                                        $rel_val  = get_relation_values($rel_data, 'customer');
                                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                                    } ?>
                                            </select>
                                        </div>
                                </div>


                                <div class="col-md-6" style="display:none;">
                                    <div class="form-group select-placeholder">
                                        <label
                                            for="billing_type"><?= _l('project_billing_type'); ?></label>
                                        <div class="clearfix"></div>
                                        <select name="billing_type" class="selectpicker" id="billing_type"
                                            data-width="100%"
                                            <?= $disable_type_edit; ?>
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <option value=""></option>
                                            <option value="1" <?php if (isset($project) && $project->billing_type == 1) {
                                                    echo 'selected';
                                                } ?>><?= _l('project_billing_type_fixed_cost'); ?></option>

                                                <option value="2" <?php if (isset($project) && $project->billing_type == 2) {
                                                    echo 'selected';
                                                } ?>><?= _l('project_billing_type_project_hours'); ?></option>

                                                <option value="3"
                                                    data-subtext="<?= _l('project_billing_type_project_task_hours_hourly_rate'); ?>"
                                                    <?php if ((isset($project) && $project->billing_type == 3) || !isset($project)) {
                                                        echo 'selected';
                                                    } ?>><?= _l('project_billing_type_project_task_hours'); ?>
                                                </option>



                                        </select>
                                        <?php if ($disable_type_edit != '') {
                                            echo '<p class="text-danger tw-mt-1">' . _l('cant_change_billing_type_billed_tasks_found') . '</p>';
                                        } ?>
                                    </div>
                                </div>
                        
                            </div>

                            <div class="form-group" style="display:none;">
                                <div class="checkbox">
                                    <input type="checkbox" <?php if ((isset($project) && $project->progress_from_tasks == 1) || ! isset($project)) {
                                        echo 'checked';
                                    } ?> name="progress_from_tasks" id="progress_from_tasks">
                                    <label
                                        for="progress_from_tasks"><?= _l('calculate_progress_through_tasks'); ?></label>
                                </div>
                            </div>
                        
                     
                        <!-- <?php
                    if (isset($project) && $project->progress_from_tasks == 1) {
                        $value = $this->projects_model->calc_progress_by_tasks($project->id);
                    } elseif (isset($project) && $project->progress_from_tasks == 0) {
                        $value = $project->progress;
                    } else {
                        $value = 0;
                    }
?>
                            <label
                                for=""><?= _l('project_progress'); ?>
                                <span
                                    class="label_progress"><?= e($value); ?>%</span></label>
                            <?= form_hidden('progress', $value); ?> -->

                      
                            <div class="project_progress_slider project_progress_slider_horizontal mbot15" style="display:none;"></div>
                           
                              
                                
                                
                                <div class="col-md-6" style="display:none;">
                                    <div class="form-group select-placeholder">
                                        <label
                                            for="status"><?= _l('project_status'); ?></label>
                                        <div class="clearfix"></div>
                                        <select name="status" id="status" class="selectpicker" data-width="100%"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <?php foreach ($statuses as $status) { ?>
                                            <option
                                                value="<?= e($status['id']); ?>"
                                                <?php if (! isset($project) && $status['id'] == 2 || (isset($project) && $project->status == $status['id'])) {
                                                    echo 'selected';
                                                } ?>><?= e($status['name']); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                         
                            <?php if (isset($project) && project_has_recurring_tasks($project->id)) { ?>
                            <div class="alert alert-warning recurring-tasks-notice hide"></div>
                            <?php } ?>
                            <?php if (is_email_template_active('project-finished-to-customer')) { ?>
                            <div class="form-group project_marked_as_finished hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="project_marked_as_finished_email_to_contacts"
                                        id="project_marked_as_finished_email_to_contacts">
                                    <label
                                        for="project_marked_as_finished_email_to_contacts"><?= _l('project_marked_as_finished_to_contacts'); ?></label>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if (isset($project)) { ?>
                            <div class="form-group mark_all_tasks_as_completed hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="mark_all_tasks_as_completed"
                                        id="mark_all_tasks_as_completed">
                                    <label
                                        for="mark_all_tasks_as_completed"><?= _l('project_mark_all_tasks_as_completed'); ?></label>
                                </div>
                            </div>
                            <div class="notify_project_members_status_change hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="notify_project_members_status_change"
                                        id="notify_project_members_status_change">
                                    <label
                                        for="notify_project_members_status_change"><?= _l('notify_project_members_status_change'); ?></label>
                                </div>
                                <hr />
                            </div>
                            <?php } ?>
                            <?php
                    $input_field_hide_class_total_cost = '';
if (! isset($project)) {
    if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 1 || ! $auto_select_billing_type) {
        $input_field_hide_class_total_cost = 'hide';
    }
} elseif (isset($project) && $project->billing_type != 1) {
    $input_field_hide_class_total_cost = 'hide';
}
?>
                            <div id="project_cost"
                                class="<?= e($input_field_hide_class_total_cost); ?>">
                                <?php $value = (isset($project) ? $project->project_cost : ''); ?>
                                <?= render_input('project_cost', 'project_total_cost', $value, 'number'); ?>
                            </div>
                            <?php
$input_field_hide_class_rate_per_hour = '';
if (! isset($project)) {
    if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 2 || ! $auto_select_billing_type) {
        $input_field_hide_class_rate_per_hour = 'hide';
    }
} elseif (isset($project) && $project->billing_type != 2) {
    $input_field_hide_class_rate_per_hour = 'hide';
}
?>
                            <div id="project_rate_per_hour"
                                class="<?= e($input_field_hide_class_rate_per_hour); ?>">
                                <?php $value = (isset($project) ? $project->project_rate_per_hour : ''); ?>
                                <?php
    $input_disable = [];
if ($disable_type_edit != '') {
    $input_disable['disabled'] = true;
}
?>
                                <?= render_input('project_rate_per_hour', 'project_rate_per_hour', $value, 'number', $input_disable); ?>
                            </div>
                            
                                <div class="col-md-6" style="display:none;">
                                    <?= render_input('estimated_hours', 'estimated_hours', isset($project) ? $project->estimated_hours : '', 'number'); ?>
                                </div>
                                <?php 
                                /*

<div class="row">

                                        <div class="col-md-6">
                                            <?php
                                                        $selected = [];
                                                        // 1. If editing existing project
                                                        if (isset($project_members)) {
                                                            foreach ($project_members as $member) {
                                                                array_push($selected, $member['staff_id']);
                                                            }
                                                        // 2. If creating new project and staff_id passed via URL
                                                        } elseif ($this->input->get('staff_id')) {
                                                            array_push($selected, $this->input->get('staff_id'));
                                                        // 3. Default to logged-in user
                                                        } else {
                                                            array_push($selected, get_staff_user_id());
                                                        }
                                                        echo render_select('project_members[]', $staff, ['staffid', 'name'], 'project_members', $selected, ['multiple' => true, 'data-actions-box' => true], [], '', '', false);
                                                        ?>
                                        </div>
                                    </div>
                                    */ ?>

                            <?php if (isset($project) && $project->date_finished != null && $project->status == 4) { ?>
                            <?= render_datetime_input('date_finished', 'project_completed_date', _dt($project->date_finished)); ?>
                            <?php } ?>
                            <div class="form-group" style="display:none">
                                <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                                    <?= _l('tags'); ?></label>
                                <input type="text" class="tagsinput" id="tags" name="tags"
                                    value="<?= isset($project) ? prep_tags_input(get_tags_in($project->id, 'project')) : ''; ?>"
                                    data-role="tagsinput">
                            </div>
                            <!-- <?php $rel_id_custom_field = (isset($project) ? $project->id : false); ?>
                            <?= render_custom_fields('projects', $rel_id_custom_field); ?>
                            <p class="bold">
                                <?= _l('project_description'); ?>
                            </p> -->
                            <!-- <?php $contents = '';
if (isset($project)) {
    $contents = $project->description;
} ?>
                            <?= render_textarea('description', '', $contents, [], [], '', 'tinymce'); ?>

                            <?php if (isset($estimate)) {?>
                            <hr class="hr-panel-separator" />
                            <h5 class="font-medium">
                                <?= _l('estimate_items_convert_to_tasks') ?>
                            </h5>
                            <input type="hidden" name="estimate_id"
                                value="<?= $estimate->id ?>">
                            <div class="row">
                                <?php foreach ($estimate->items as $item) { ?>
                                <div class="col-md-8 border-right">
                                    <div class="checkbox mbot15">
                                        <input type="checkbox" name="items[]"
                                            value="<?= $item['id'] ?>"
                                            checked
                                            id="item-<?= $item['id'] ?>">
                                        <label
                                            for="item-<?= $item['id'] ?>">
                                            <h5 class="no-mbot no-mtop text-uppercase">
                                                <?= $item['description'] ?>
                                            </h5>
                                            <span
                                                class="text-muted"><?= $item['long_description'] ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div data-toggle="tooltip"
                                        title="<?= _l('task_single_assignees_select_title'); ?>">
                                        <?= render_select('items_assignee[]', $staff, ['staffid', ['firstname', 'lastname']], '', get_staff_user_id(), ['data-actions-box' => true], [], '', '', false); ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <?php } ?> -->









                            <!-- <hr class="hr-panel-separator" /> -->



<div class="col-md-12">
 <hr class="hr-panel-separator" />
                                </div>

<div class="col-md-12">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">VAT Settings</h4>
</div>
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($project) && $project->enable_vat == 1 ? 'checked' : '';
				?>
				<input type="checkbox"  name="enable_vat" id="enable_vat" <?=$checked?> value="1">
                    <label for="enable_vat"><?= _l('project_enable_vat') ?></label>
                    
                </div>
				 </div>
				
				<div class="col-md-6">
				<div class="checkbox checkbox-margin-bottom">
				<?php
				$checked = '';
				$checked = isset($project) && $project->po_rate_include_vat == 1 ? 'checked' : '';
				?>
				<input type="checkbox" name="po_rate_include_vat" id="po_rate_include_vat" <?= $checked ?> value="1">
                    <label for="po_rate_include_vat"><?= _l('project_po_rate_include_vat') ?></label>
                    
                </div>
				 </div>
				
				<div class="col-md-6">
				<div class="form-group">
                    <label for="vat_number"><?= _l('project_vat_number') ?></label>
                    <input type="text" class="form-control" name="vat_number" id="vat_number" value="<?= isset($project) ? $project->vat_number : '' ?>">
                </div>
				 </div>
				 
				 <div class="col-md-6">
					 <div class="form-group" app-field-wrapper="date">
						<label for="date" class="control-label"><?= _l('project_vat_start_date') ?></label>
							<div class="input-group date">
								<input type="text" id="vat_start_date" name="vat_start_date" class="form-control datepicker" value="<?= isset($project) ? $project->vat_start_date: '' ?>" autocomplete="off" aria-invalid="false">
								<div class="input-group-addon">
									<i class="fa-regular fa-calendar calendar-icon"></i>
								</div>
							</div>
					</div>
				</div>

                <div class="col-md-6">
				<div class="form-group">
                    <label for="show_arabic_numbers"><?= _l('project_show_arabic_numbers') ?></label>
                    <input type="text" class="form-control" name="show_arabic_numbers" id="show_arabic_numbers" value="<?= isset($project) ? $project->show_arabic_numbers : '' ?>">
                </div>
				 </div>

                 <div class="col-md-6">
				<div class="form-group">
                    <label for="show_arabic_numbers_to_words"><?= _l('project_show_arabic_numbers_to_words') ?></label>
                    <input type="text" class="form-control" name="show_arabic_numbers_to_words" id="show_arabic_numbers_to_words" value="<?= isset($project) ? $project->show_arabic_numbers_to_words : '' ?>">
                </div>
				 </div>
				 
				<div class="col-md-6">
				<div class="form-group">
                    <label for="vat_percentage"><?= _l('project_vat_percentage') ?></label>
                    <input type="text" class="form-control" name="vat_percentage" id="vat_percentage" value="<?= isset($project) ? $project->vat_percentage : '' ?>">
                </div>
				 </div>















                            <!-- <?php if (is_email_template_active('assigned-to-project')) { ?>
                            <div class="checkbox checkbox-primary tw-mb-0">
                                <input type="checkbox" name="send_created_email" id="send_created_email">
                                <label
                                    for="send_created_email"><?= _l('project_send_created_email'); ?></label>
                            </div>
                            <?php } ?> -->
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_settings">
                            <div id="project-settings-area">
                                <div class="form-group select-placeholder">
                                    <label for="contact_notification" class="control-label">
                                        <span class="text-danger">*</span>
                                        <?= _l('projects_send_contact_notification'); ?>
                                    </label>
                                    <select name="contact_notification" id="contact_notification"
                                        class="form-control selectpicker"
                                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                                        required>
                                        <?php
                    $options = [
                        ['id' => 1, 'name' => _l('project_send_all_contacts_with_notifications_enabled')],
                        ['id' => 2, 'name' => _l('project_send_specific_contacts_with_notification')],
                        ['id' => 0, 'name' => _l('project_do_not_send_contacts_notifications')],
                    ];

foreach ($options as $option) { ?>
                                        <option
                                            value="<?= e($option['id']); ?>"
                                            <?php if ((isset($project) && $project->contact_notification == $option['id'])) {
                                                echo ' selected';
                                            } ?>><?= e($option['name']); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <!-- hide class -->
                                <div class="form-group select-placeholder <?= (isset($project) && $project->contact_notification == 2) ? '' : 'hide' ?>"
                                    id="notify_contacts_wrapper">
                                    <label for="notify_contacts" class="control-label"><span
                                            class="text-danger">*</span>
                                        <?= _l('project_contacts_to_notify') ?></label>
                                    <select name="notify_contacts[]" data-id="notify_contacts" id="notify_contacts"
                                        class="ajax-search" data-width="100%" data-live-search="true"
                                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                                        multiple>
                                        <?php
                                            $notify_contact_ids = isset($project) ? unserialize($project->notify_contacts) : [];

foreach ($notify_contact_ids as $contact_id) {
    $rel_data = get_relation_data('contact', $contact_id);
    $rel_val  = get_relation_values($rel_data, 'contact');
    echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
}
?>
                                    </select>
                                </div>
                                <?php foreach ($settings as $setting) {
                                    $checked = ' checked';
                                    if (isset($project)) {
                                        if ($project->settings->{$setting} == 0) {
                                            $checked = '';
                                        }
                                    } else {
                                        foreach ($last_project_settings as $last_setting) {
                                            if ($setting == $last_setting['name']) {
                                                // hide_tasks_on_main_tasks_table is not applied on most used settings to prevent confusions
                                                if ($last_setting['value'] == 0 || $last_setting['name'] == 'hide_tasks_on_main_tasks_table') {
                                                    $checked = '';
                                                }
                                            }
                                        }
                                        if (count($last_project_settings) == 0 && $setting == 'hide_tasks_on_main_tasks_table') {
                                            $checked = '';
                                        }
                                    } ?>
                                <?php if ($setting != 'available_features') { ?>
                                <div class="checkbox">
                                    <input type="checkbox"
                                        name="settings[<?= e($setting); ?>]"
                                        <?= e($checked); ?>
                                    id="<?= e($setting); ?>">
                                    <label for="<?= e($setting); ?>">
                                        <?php if ($setting == 'hide_tasks_on_main_tasks_table') { ?>
                                        <?= _l('hide_tasks_on_main_tasks_table'); ?>
                                        <?php } else { ?>
                                        <?= e(_l('project_allow_client_to', _l('project_setting_' . $setting))); ?>
                                        <?php } ?>
                                    </label>
                                </div>
                                <?php } else { ?>
                                <div class="form-group mtop15 select-placeholder project-available-features">
                                    <label
                                        for="available_features"><?= _l('visible_tabs'); ?></label>
                                    <select
                                        name="settings[<?= e($setting); ?>][]"
                                        id="<?= e($setting); ?>"
                                        multiple="true" class="selectpicker" id="available_features" data-width="100%"
                                        data-actions-box="true" data-hide-disabled="true">
                                        <?php foreach (get_project_tabs_admin() as $tab) {
                                            $selected = '';
                                            if (isset($tab['collapse'])) { ?>
                                        <optgroup
                                            label="<?= e($tab['name']); ?>">
                                            <?php foreach ($tab['children'] as $tab_dropdown) {
                                                $selected = '';
                                                if (isset($project) && (
                                                    (isset($project->settings->available_features[$tab_dropdown['slug']])
                                                                && $project->settings->available_features[$tab_dropdown['slug']] == 1)
                                                            || ! isset($project->settings->available_features[$tab_dropdown['slug']])
                                                )) {
                                                    $selected = ' selected';
                                                } elseif (! isset($project) && count($last_project_settings) > 0) {
                                                    foreach ($last_project_settings as $last_project_setting) {
                                                        if ($last_project_setting['name'] == $setting) {
                                                            if (isset($last_project_setting['value'][$tab_dropdown['slug']])
                                                                    && $last_project_setting['value'][$tab_dropdown['slug']] == 1) {
                                                                $selected = ' selected';
                                                            }
                                                        }
                                                    }
                                                } elseif (! isset($project)) {
                                                    $selected = ' selected';
                                                } ?>
                                            <option
                                                value="<?= e($tab_dropdown['slug']); ?>"
                                                <?= e($selected); ?><?php if (isset($tab_dropdown['linked_to_customer_option']) && is_array($tab_dropdown['linked_to_customer_option']) && count($tab_dropdown['linked_to_customer_option']) > 0) { ?>
                                                data-linked-customer-option="<?= implode(',', $tab_dropdown['linked_to_customer_option']); ?>"
                                                <?php } ?>><?= e($tab_dropdown['name']); ?>
                                            </option>
                                            <?php
                                            } ?>
                                        </optgroup>
                                        <?php } else {
                                            if (isset($project) && (
                                                (isset($project->settings->available_features[$tab['slug']])
                             && $project->settings->available_features[$tab['slug']] == 1)
                            || ! isset($project->settings->available_features[$tab['slug']])
                                            )) {
                                                $selected = ' selected';
                                            } elseif (! isset($project) && count($last_project_settings) > 0) {
                                                foreach ($last_project_settings as $last_project_setting) {
                                                    if ($last_project_setting['name'] == $setting) {
                                                        if (isset($last_project_setting['value'][$tab['slug']])
                                    && $last_project_setting['value'][$tab['slug']] == 1) {
                                                            $selected = ' selected';
                                                        }
                                                    }
                                                }
                                            } elseif (! isset($project)) {
                                                $selected = ' selected';
                                            } ?>
                                        <option
                                            value="<?= e($tab['slug']); ?>"
                                            <?php if ($tab['slug'] == 'project_overview') {
                                                echo ' disabled selected';
                                            } ?>
                                            <?= e($selected); ?>
                                            <?php if (isset($tab['linked_to_customer_option']) && is_array($tab['linked_to_customer_option']) && count($tab['linked_to_customer_option']) > 0) { ?>
                                            data-linked-customer-option="<?= implode(',', $tab['linked_to_customer_option']); ?>"
                                            <?php } ?>>
                                            <?= e($tab['name']); ?>
                                        </option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php } ?>
                                <hr class="tw-my-3 -tw-mx-8" />
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" data-form="#project_form" class="btn btn-primary" autocomplete="off"
                        data-loading-text="<?= _l('wait_text'); ?>">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    <?php if (isset($project)) { ?>
    var original_project_status = '<?= e($project->status); ?>';
    <?php } ?>

    $(function() {

        $contacts_select = $('#notify_contacts'),
            $contacts_wrapper = $('#notify_contacts_wrapper'),
            $clientSelect = $('#clientid'),
            $contact_notification_select = $('#contact_notification');

        init_ajax_search('contacts', $contacts_select, {
            rel_id: $contacts_select.val(),
            type: 'contacts',
            extra: {
                client_id: function() {
                    return $clientSelect.val();
                }
            }
        });

        if ($clientSelect.val() == '') {
            $contacts_select.prop('disabled', true);
            $contacts_select.selectpicker('refresh');
        } else {
            $contacts_select.siblings().find('input[type="search"]').val(' ').trigger('keyup');
        }

        $clientSelect.on('changed.bs.select', function() {
            if ($clientSelect.selectpicker('val') == '') {
                $contacts_select.prop('disabled', true);
            } else {
                $contacts_select.siblings().find('input[type="search"]').val(' ').trigger('keyup');
                $contacts_select.prop('disabled', false);
            }
            deselect_ajax_search($contacts_select[0]);
            $contacts_select.find('option').remove();
            $contacts_select.selectpicker('refresh');
        });

        $contact_notification_select.on('changed.bs.select', function() {
            if ($contact_notification_select.selectpicker('val') == 2) {
                $contacts_select.siblings().find('input[type="search"]').val(' ').trigger('keyup');
                $contacts_wrapper.removeClass('hide');
            } else {
                $contacts_wrapper.addClass('hide');
                deselect_ajax_search($contacts_select[0]);
            }
        });

        $('select[name="billing_type"]').on('change', function() {
            var type = $(this).val();
            if (type == 1) {
                $('#project_cost').removeClass('hide');
                $('#project_rate_per_hour').addClass('hide');
            } else if (type == 2) {
                $('#project_cost').addClass('hide');
                $('#project_rate_per_hour').removeClass('hide');
            } else {
                $('#project_cost').addClass('hide');
                $('#project_rate_per_hour').addClass('hide');
            }
        });

        appValidateForm($('form'), {
            name: 'required',
    //        clientid: 'required',
            start_date: 'required',
            vat_start_date: 'required',
            // billing_type: 'required',
            'notify_contacts[]': {
                required: {
                    depends: function() {
                        return !$contacts_wrapper.hasClass('hide');
                    }
                }
            },
        });

        $('select[name="status"]').on('change', function() {
            var status = $(this).val();
            var mark_all_tasks_completed = $('.mark_all_tasks_as_completed');
            var notify_project_members_status_change = $('.notify_project_members_status_change');
            mark_all_tasks_completed.removeClass('hide');
            if (typeof(original_project_status) != 'undefined') {
                if (original_project_status != status) {

                    mark_all_tasks_completed.removeClass('hide');
                    notify_project_members_status_change.removeClass('hide');

                    if (status == 4 || status == 5 || status == 3) {
                        $('.recurring-tasks-notice').removeClass('hide');
                        var notice =
                            "<?= _l('project_changing_status_recurring_tasks_notice'); ?>";
                        notice = notice.replace('{0}', $(this).find('option[value="' + status + '"]')
                            .text()
                            .trim());
                        $('.recurring-tasks-notice').html(notice);
                        $('.recurring-tasks-notice').append(
                            '<input type="hidden" name="cancel_recurring_tasks" value="true">');
                        mark_all_tasks_completed.find('input').prop('checked', true);
                    } else {
                        $('.recurring-tasks-notice').html('').addClass('hide');
                        mark_all_tasks_completed.find('input').prop('checked', false);
                    }
                } else {
                    mark_all_tasks_completed.addClass('hide');
                    mark_all_tasks_completed.find('input').prop('checked', false);
                    notify_project_members_status_change.addClass('hide');
                    $('.recurring-tasks-notice').html('').addClass('hide');
                }
            }

            if (status == 4) {
                $('.project_marked_as_finished').removeClass('hide');
            } else {
                $('.project_marked_as_finished').addClass('hide');
                $('.project_marked_as_finished').prop('checked', false);
            }
        });

        $('form').on('submit', function() {
            $('select[name="billing_type"]').prop('disabled', false);
            $('#available_features,#available_features option').prop('disabled', false);
            $('input[name="project_rate_per_hour"]').prop('disabled', false);
        });

        var progress_input = $('input[name="progress"]');
        var progress_from_tasks = $('#progress_from_tasks');
        var progress = progress_input.val();

        $('.project_progress_slider').slider({
            min: 0,
            max: 100,
            value: progress,
            disabled: progress_from_tasks.prop('checked'),
            slide: function(event, ui) {
                progress_input.val(ui.value);
                $('.label_progress').html(ui.value + '%');
            }
        });

        progress_from_tasks.on('change', function() {
            var _checked = $(this).prop('checked');
            $('.project_progress_slider').slider({
                disabled: _checked
            });
        });

        $('#project-settings-area input').on('change', function() {
            if ($(this).attr('id') == 'view_tasks' && $(this).prop('checked') == false) {
                $('#create_tasks').prop('checked', false).prop('disabled', true);
                $('#edit_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_comments').prop('checked', false).prop('disabled', true);
                $('#comment_on_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_attachments').prop('checked', false).prop('disabled', true);
                $('#view_task_checklist_items').prop('checked', false).prop('disabled', true);
                $('#upload_on_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_total_logged_time').prop('checked', false).prop('disabled', true);
            } else if ($(this).attr('id') == 'view_tasks' && $(this).prop('checked') == true) {
                $('#create_tasks').prop('disabled', false);
                $('#edit_tasks').prop('disabled', false);
                $('#view_task_comments').prop('disabled', false);
                $('#comment_on_tasks').prop('disabled', false);
                $('#view_task_attachments').prop('disabled', false);
                $('#view_task_checklist_items').prop('disabled', false);
                $('#upload_on_tasks').prop('disabled', false);
                $('#view_task_total_logged_time').prop('disabled', false);
            }
        });

        // Auto adjust customer permissions based on selected project visible tabs
        // Eq Project creator disable TASKS tab, then this function will auto turn off customer project option Allow customer to view tasks

        $('#available_features').on('change', function() {
            $("#available_features option").each(function() {
                if ($(this).data('linked-customer-option') && !$(this).is(':selected')) {
                    var opts = $(this).data('linked-customer-option').split(',');
                    for (var i = 0; i < opts.length; i++) {
                        var project_option = $('#' + opts[i]);
                        project_option.prop('checked', false);
                        if (opts[i] == 'view_tasks') {
                            project_option.trigger('change');
                        }
                    }
                }
            });
        });
        $("#view_tasks").trigger('change');
        <?php if (! isset($project)) { ?>
        $('#available_features').trigger('change');
        <?php } ?>
    });
</script>
</body>

</html>