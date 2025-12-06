<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">

						<div class="row mb-5">
							<div class="col-md-12">
								<?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
									<div class="tw-flex tw-justify-between tw-items-center">
										<div>
											<h4 class="no-margin">
												<?php echo _l('hrp_payroll') . ': ' . $payroll->payroll_number; ?>
											</h4>
											<small class="text-muted"><?php echo _l('hrp_employees') . ' - ' . $company_name; ?></small>
										</div>
										<div>
											<?php
											$status_colors = [
												'draft' => 'default',
												'ready_for_review' => 'info',
												'awaiting_approval' => 'warning',
												'submitted' => 'primary',
												'completed' => 'success',
												'cancelled' => 'danger',
											];
											$color = $status_colors[$payroll->status] ?? 'default';
											$status_text = str_replace('_', ' ', ucwords($payroll->status, '_'));
											?>
											<span class="label label-<?php echo $color; ?> tw-text-lg">
												<?php echo $status_text; ?>
											</span>
										</div>
									</div>
								<?php else: ?>
									<h4 class="no-margin"><?php echo _l('hrp_employees') . ' - ' . $company_name; ?></h4>
								<?php endif; ?>
							</div>
						</div>

						<?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
							<div class="row">
								<div class="col-md-12">
									<div class="alert alert-info">
										<div class="row">
											<div class="col-md-3">
												<strong><?php echo _l('month'); ?>:</strong><br>
												<?php echo date('F Y', strtotime($payroll->month)); ?>
											</div>
											<div class="col-md-3">
												<strong><?php echo _l('company'); ?>:</strong><br>
												<?php echo $payroll->company_name ?? _l('all'); ?>
											</div>
											<div class="col-md-3">
												<strong><?php echo _l('employee_type'); ?>:</strong><br>
												<?php echo $payroll->ownemployee_type_name ?? '-'; ?>
											</div>
											<div class="col-md-3">
												<strong><?php echo _l('total_employees'); ?>:</strong><br>
												<?php echo $payroll->total_employees; ?>
												&nbsp;&nbsp;|&nbsp;&nbsp;
												<strong><?php echo _l('total_amount'); ?>:</strong>
												<?php echo number_format($payroll->total_amount, 2); ?>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Status Transition Buttons -->
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-default">
										<div class="panel-body">
											<div class="tw-flex tw-justify-between tw-items-center">
												<div>
													<strong><?php echo _l('hr_payroll_workflow'); ?>:</strong>
													<span class="text-muted"><?php echo _l('hr_payroll_workflow_help'); ?></span>
												</div>
												<div class="btn-group">
													<?php if ($payroll->status == 'draft'): ?>
														<button type="button" class="btn btn-info payroll-status-btn" data-status="ready_for_review" data-payroll-id="<?php echo $payroll->id; ?>">
															<i class="fa fa-arrow-right"></i> <?php echo _l('hr_mark_ready_for_review'); ?>
														</button>
													<?php elseif ($payroll->status == 'ready_for_review'): ?>
														<button type="button" class="btn btn-default payroll-status-btn" data-status="draft" data-payroll-id="<?php echo $payroll->id; ?>">
															<i class="fa fa-arrow-left"></i> <?php echo _l('hr_back_to_draft'); ?>
														</button>
														<?php if (has_permission('hrp_employee', '', 'edit') || is_admin()): ?>
															<button type="button" class="btn btn-warning payroll-status-btn" data-status="awaiting_approval" data-payroll-id="<?php echo $payroll->id; ?>">
																<i class="fa fa-arrow-right"></i> <?php echo _l('hr_send_for_approval'); ?>
															</button>
														<?php endif; ?>
													<?php elseif ($payroll->status == 'awaiting_approval'): ?>
														<button type="button" class="btn btn-default payroll-status-btn" data-status="ready_for_review" data-payroll-id="<?php echo $payroll->id; ?>">
															<i class="fa fa-arrow-left"></i> <?php echo _l('hr_back_to_review'); ?>
														</button>
														<?php if (has_permission('hrp_employee', '', 'edit') || is_admin()): ?>
															<button type="button" class="btn btn-primary payroll-status-btn" data-status="submitted" data-payroll-id="<?php echo $payroll->id; ?>">
																<i class="fa fa-check"></i> <?php echo _l('hr_approve_and_submit'); ?>
															</button>
														<?php endif; ?>
													<?php elseif ($payroll->status == 'submitted'): ?>
														<?php if (has_permission('hrp_employee', '', 'edit') || is_admin()): ?>
															<button type="button" class="btn btn-success payroll-status-btn" data-status="completed" data-payroll-id="<?php echo $payroll->id; ?>">
																<i class="fa fa-check-circle"></i> <?php echo _l('hr_mark_as_completed'); ?>
															</button>
														<?php endif; ?>
													<?php elseif ($payroll->status == 'completed'): ?>
														<span class="label label-success">
															<i class="fa fa-check-circle"></i> <?php echo _l('hr_payroll_completed'); ?>
														</span>
													<?php endif; ?>

													<?php if ($payroll->status != 'completed' && $payroll->status != 'cancelled'): ?>
														<?php if (has_permission('hrp_employee', '', 'delete') || is_admin()): ?>
															<button type="button" class="btn btn-danger payroll-status-btn" data-status="cancelled" data-payroll-id="<?php echo $payroll->id; ?>" data-confirm="true">
																<i class="fa fa-times"></i> <?php echo _l('hr_cancel_payroll'); ?>
															</button>
														<?php endif; ?>
													<?php endif; ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<br>
						<div class="row mb-4">
							<div class="col-md-12">
								<!-- filter -->
								<div class="row filter_by">

									<div class="col-md-2">
										<?php
										$month_value = (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll))
											? date('Y-m', strtotime($payroll->month))
											: date('Y-m');
										$month_readonly = (isset($is_payroll_mode) && $is_payroll_mode) ? ['readonly' => true] : [];
										echo render_input('month_employees','month', $month_value, 'month', $month_readonly);
										?>
									</div>

									<!-- <div class="col-md-3 leads-filter-column pull-left">
										<?php echo render_select('department_employees',$departments,array('departmentid', 'name'),'department',''); ?>
									</div>

									<div class="col-md-3 leads-filter-column pull-left">
										<div class="form-group">
											<label for="role_employees" class="control-label"><?php echo _l('role'); ?></label>
											<select name="role_employees[]" class="form-control selectpicker" multiple="true" id="role_employees" data-actions-box="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true"> 
												<?php foreach ($roles as $key => $role) { ?>
													<option value="<?php echo new_html_entity_decode($role['roleid']); ?>" ><?php  echo new_html_entity_decode($role['name']); ?></option>
												<?php } ?>
											</select>
										</div>
									</div> -->

									<div class="col-md-3 leads-filter-column pull-left">

										<div class="form-group">
											<label for="staff_employees" class="control-label"><?php echo _l('staff'); ?></label>
											<select name="staff_employees[]" class="form-control selectpicker" multiple="true" id="staff_employees" data-actions-box="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true"> 
												<?php foreach ($staffs as $key => $staff) { ?>

													<option value="<?php echo new_html_entity_decode($staff['staffid']); ?>" ><?php  echo new_html_entity_decode($staff['name']); ?></option>
												<?php } ?>
											</select>
										</div>

									</div>

								</div>
								<!-- filter -->
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-12">
								<hr class="hr-color">
							</div>
						</div>

						<?php echo form_open(admin_url('hr_payroll/add_manage_employees'),array('id'=>'add_manage_employees')); ?>
						
						<div class="col-md-12">
							<small><?php echo _l('handsontable_scroll_horizontally') ?></small>
							<div class="pull-right">
								<label>
									<input type="checkbox" id="select_all_employees" />
									<strong><?php echo _l('Select All'); ?></strong>
								</label>
							</div>
						</div>
						<div id="total_insurance_histtory" class="col-md-12">
							<div class="row">
								<div id="hrp_employees_value_wrapper"
    								style="width:100%; overflow:hidden; -webkit-overflow-scrolling:touch;">
								<div id="hrp_employees_value" class="hot handsontable htColumnHeaders"></div>
								</div>

								<?php echo form_hidden('hrp_employees_value'); ?>
								<?php echo form_hidden('month', date('m-Y')); ?>
								<?php echo form_hidden('employees_fill_month'); ?>
								<?php echo form_hidden('department_employees_filter'); ?>
								<?php echo form_hidden('staff_employees_filter'); ?>
								<?php echo form_hidden('role_employees_filter'); ?>
								<?php echo form_hidden('hrp_employees_rel_type'); ?>
								<?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
									<?php echo form_hidden('payroll_id', $payroll->id); ?>
								<?php endif; ?>
							</div>
						</div>

						<div class="col-md-12">
							<div class="modal-footer">
								<?php if(has_permission('hrp_employee', '', 'create') || has_permission('hrp_employee', '', 'edit')){ ?>
									<button type="button" class="btn btn-info pull-right save_manage_employees mleft5 "><?php echo new_html_entity_decode($button_name); ?></button>
									<?php if(hrp_get_hr_profile_status() == 'hr_records'){ ?>

										<a href="#"class="btn btn-info pull-right display-block hrp_employees_synchronization" data-toggle="tooltip" title="<?php echo _l('synchronized_employees_title'); ?>"><?php echo _l('hrp_synchronized'); ?><i class=" pull-right fa fa-question-circle i_tooltip" ></i></a>
									<?php } ?>

									<!-- <a href="#" class=" btn mright5 btn-primary pull-right hrp_employees_copy" data-toggle="tooltip" title="<?php echo _l('copy_from_last_month'); ?>">
										<?php echo _l('hrp_copy'); ?>
									</a> -->
									<?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll) && in_array($payroll->status, ['draft', 'ready_for_review'])): ?>
										<button type="button" class="btn btn-warning pull-right mright5 recalculate_selected_employees" data-toggle="tooltip" title="<?php echo _l('hr_recalculate_selected_help'); ?>">
											<i class="fa fa-refresh"></i> <?php echo _l('hr_recalculate_selected'); ?>
										</button>
									<?php endif; ?>
									<button type="button" class="btn btn-success pull-right mright5 export_selected_employees" data-toggle="tooltip" title="<?php echo _l('Export WPS file for selected employees'); ?>">
										<i class="fa fa-download"></i> Export WPS
									</button>
									<button type="button" class="btn btn-info pull-right mright5 export_payroll_excel" data-toggle="tooltip" title="<?php echo _l('Export payroll data to Excel'); ?>">
										<i class="fa fa-file-excel-o"></i> Export Excel
									</button>
									<?php /* Temporarily hidden - Import Excel button
									<a href="<?php echo admin_url('hr_payroll/import_xlsx_employees'); ?>" class=" btn mright5 btn-default pull-right">
										<?php echo _l('hrp_import_excel'); ?>
									</a>
									*/ ?>
								<?php } ?>

							</div>
						</div>
					</div>
				</div>
			</div>

			<?php echo form_close(); ?>

		</div>

	</div>
</div>


</div>
</div>
</div>

<!-- Preview Modal -->
<div id="payslipPreviewModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
   <div class="modal-content">
     <div class="modal-header"><h5 class="modal-title">Payslip Preview</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
     <div class="modal-body" id="payslipPreviewBody"></div>
   </div>
  </div>
</div>

<!-- Adjustment Modal (Addition / Deduction) -->
<div id="adjustmentModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
   <div class="modal-content">
     <div class="modal-header"><h5 class="modal-title">Add Adjustment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
     <div class="modal-body">
       <form id="adjustmentForm">
         <input type="hidden" name="staff_id" id="adj_staff_id">
         <input type="hidden" name="month" id="adj_month">
         <div class="form-group">
           <label>Type</label>
           <select name="type" class="form-control" id="adj_type">
             <option value="addition">Addition</option>
             <option value="deduction">Deduction</option>
           </select>
         </div>
         <div class="form-group">
           <label>Date</label>
           <input type="date" name="date" class="form-control" required>
         </div>
         <div class="form-group">
           <label>Project</label>
           <select name="project_id" id="adj_project_id" class="form-control">
             <!-- <option value="">-- Project (optional) --</option> -->
           </select>
         </div>
         <div class="form-group">
           <label>Description</label>
           <input type="text" name="description" class="form-control">
         </div>
         <div class="form-group">
           <label>Amount</label>
           <input type="number" step="0.01" name="amount" class="form-control" required>
         </div>
         <div class="text-right">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="submit" class="btn btn-primary">Add</button>
         </div>
       </form>
     </div>
   </div>
  </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
   <div class="modal-content">
     <div class="modal-header"><h5 class="modal-title">Make Payment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
     <div class="modal-body">
       <form id="paymentForm">
         <input type="hidden" name="staff_id" id="pay_staff_id">
         <input type="hidden" name="month" id="pay_month">
         <div class="form-group">
           <label>Amount payable</label>
           <input type="number" step="0.01" name="amount" id="pay_amount" class="form-control" required>
         </div>
         <div class="form-group">
           <label>Paid From</label>
           <select name="method" class="form-control">
             <option value="bank">Bank</option>
             <option value="cash">Cash</option>
           </select>
         </div>
         <div class="form-group">
           <label>Paid Date</label>
           <input type="date" name="paid_date" class="form-control" required>
         </div>
         <div class="form-group">
           <label>Reference</label>
           <input type="text" name="reference" class="form-control">
         </div>
         <div class="text-right">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="submit" class="btn btn-success">Make Payment</button>
         </div>
       </form>
     </div>
   </div>
  </div>
</div>

<!-- Include Pay Information Modal -->
<?php require 'modules/hr_payroll/views/modals/pay_information_modal.php'; ?>

<?php init_tail(); ?>
<?php require 'modules/hr_payroll/assets/js/manage_employees/manage_employees_js.php'; ?>

<style>
	#hrp_employees_value_wrapper {
		max-width: 100vw;         /* full screen width */
		/*  max-height: 80vh;          allow vertical scrolling on mobile */
		touch-action: pan-x pan-y; /* allow finger swipe in both directions */
	}
	#hrp_employees_value {
		min-width: 1200px; /* force horizontal scroll if many columns */
	}

	/* Color coding for payroll grid columns */
	.bg-pale-green {
		background-color: #d4edda !important; /* Pale green for GOSI columns */
	}
	.bg-pale-orange {
		background-color: #ffe5d0 !important; /* Pale orange for balance */
	}
	.bg-pale-blue {
		background-color: #d1ecf1 !important; /* Pale blue for payslip columns */
	}
	.bg-pale-amber {
		background-color: #fff3cd !important; /* Pale amber/yellow for reference/comment columns */
	}
</style>

</body>
</html>
