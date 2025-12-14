<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * hr payroll controler
 */
class hr_payroll extends AdminController {

	public function __construct() {
		parent::__construct();
		$this->load->model('hr_payroll_model');
		hooks()->do_action('hr_payroll_init'); 
	}

	/**
	 * setting
	 * @return view
	 */
	public function setting() {
		if (!has_permission('hrp_setting', '', 'view') && !has_permission('hrp_setting', '', 'edit') && !is_admin() && !has_permission('hrp_setting', '', 'create')) {
			access_denied('hrp_settting');
		}

		$data['group'] = $this->input->get('group');
        $data['unit_tab'] = $this->input->get('tab');

		$data['title'] = _l('setting');

		$data['tab'][] = 'income_tax_rates';
		$data['tab'][] = 'income_tax_rebates';
		if (hr_payroll_get_status_modules('hr_profile') && (get_hr_payroll_option('integrated_hrprofile') == 1)) {
			$data['tab'][] = 'hr_records_earnings_list';
		} else {
			$data['tab'][] = 'earnings_list';
		}
		$data['tab'][] = 'salary_deductions_list';
		$data['tab'][] = 'insurance_list';
		$data['tab'][] = 'payroll_columns';
		$data['tab'][] = 'pdf_payslip_template';
		$data['tab'][] = 'data_integration';
        $data['tab'][] = 'currency_rates';

		if (is_admin()) {
			$data['tab'][] = 'permissions';
			$data['tab'][] = 'reset_data';
		}

		if ($data['group'] == '') {
			$data['group'] = 'payroll_columns';
			$data['payroll_column_value'] = $this->hr_payroll_model->get_hrp_payroll_columns();
			$data['order_display_in_paylip'] = $this->hr_payroll_model->count_payroll_column();
		} elseif ($data['group'] == 'payroll_columns') {
			$data['payroll_column_value'] = $this->hr_payroll_model->get_hrp_payroll_columns();
			$data['order_display_in_paylip'] = $this->hr_payroll_model->count_payroll_column();

		} elseif ($data['group'] == 'income_tax_rates') {
			$data['title'] = _l('income_tax_rates');
			$data['income_tax_rates'] = json_encode($this->hr_payroll_model->get_income_tax_rate());
		} elseif ($data['group'] == 'income_tax_rebates') {
			$data['title'] = _l('income_tax_rebates');
			$data['income_tax_rebates'] = json_encode($this->hr_payroll_model->get_income_tax_rebates());
		} elseif ($data['group'] == 'earnings_list') {

			$earnings_value = [];
			$earnings_value[] = [
				'id' => 'monthly',
				'label' => _l('monthly'),
			];
			$earnings_value[] = [
				'id' => 'annual',
				'label' => _l('annual'),
			];

			$data['title'] = _l('earnings_list');
			$data['basis_value'] = $earnings_value;
			$data['earnings_list'] = json_encode($this->hr_payroll_model->get_earnings_list());
		} elseif ($data['group'] == 'salary_deductions_list') {
			$earn_inclusion_value = [];
			$earn_inclusion_value[] = [
				'id' => 'fullvalue',
				'label' => _l('fullvalue'),
			];
			$earn_inclusion_value[] = [
				'id' => 'taxable',
				'label' => _l('taxable'),
			];

			$basis_value = [];
			$basis_value[] = [
				'id' => 'gross',
				'label' => _l('gross'),
			];
			$basis_value[] = [
				'id' => 'fixed_amount',
				'label' => _l('fixed_amount'),
			];

			if (hr_payroll_get_status_modules('hr_profile') && (get_hr_payroll_option('integrated_hrprofile') == 1)) {
				$earnings_list = $this->hr_payroll_model->hr_records_get_earnings_list();

				foreach ($earnings_list as $value) {
					switch ($value['rel_type']) {
						case 'salary':
						
						$basis_value[] = [
							'id' => 'st_'.$value['rel_id'],
							'label' => $value['description'],
						];
						break;

						case 'allowance':
						$basis_value[] = [
							'id' => 'al_'.$value['rel_id'],
							'label' => $value['description'],
						];
						
						break;

						default:
						# code...
						break;
					}

				}


			} else {
				$earnings_list = $this->hr_payroll_model->get_earnings_list();

				foreach ($earnings_list as $value) {
					$basis_value[] = [
						'id' => 'earning_'.$value['id'],
						'label' => $value['description'],
					];
				}
			}

			$data['title'] = _l('salary_deductions_list');
			$data['basis_value'] = $basis_value;
			$data['earn_inclusion'] = $earn_inclusion_value;
			$data['salary_deductions_list'] = json_encode($this->hr_payroll_model->get_salary_deductions_list());

		} elseif ($data['group'] == 'insurance_list') {
			$basis_value = [];
			$basis_value[] = [
				'id' => 'gross',
				'label' => _l('gross'),
			];
			$basis_value[] = [
				'id' => 'fixed_amount',
				'label' => _l('fixed_amount'),
			];

			$data['title'] = _l('insurance_list');
			$data['basis_value'] = $basis_value;
			$data['insurance_list'] = json_encode($this->hr_payroll_model->get_insurance_list());

		} elseif ($data['group'] == 'company_contributions_list') {
			$earn_inclusion_value = [];
			$earn_inclusion_value[] = [
				'id' => 'fullvalue',
				'label' => _l('fullvalue'),
			];
			$earn_inclusion_value[] = [
				'id' => 'taxable',
				'label' => _l('taxable'),
			];
			$earn_inclusion_value[] = [
				'id' => 'none',
				'label' => _l('none'),
			];

			$data['title'] = _l('company_contributions_list');
			$data['earn_inclusion'] = $earn_inclusion_value;
			$data['company_contributions_list'] = json_encode($this->hr_payroll_model->get_company_contributions_list());
		} elseif ($data['group'] == 'data_integration') {
			$data['hr_profile_active'] = hr_payroll_get_status_modules('hr_profile');
			$data['timesheets_active'] = hr_payroll_get_status_modules('timesheets');
			$data['commissions_active'] = hr_payroll_get_status_modules('commission');

			$hr_profile_title = '';
			$timesheets_title = '';
			//title
			if ($data['hr_profile_active'] == false) {
				$hr_profile_title = _l('active_hr_profile_to_integration');
			} else {
				$hr_profile_title = _l('hr_profile_integration_data');
			}

			if ($data['timesheets_active'] == false) {
				$timesheets_title = _l('active_timesheets_to_integration');
			} else {
				$timesheets_title = _l('timesheets_to_integration');
			}

			if ($data['commissions_active'] == false) {
				$commissions_title = _l('active_commissions_to_integration');
			} else {
				$commissions_title = _l('commissions_to_integration');
			}

			$data['hr_profile_title'] = $hr_profile_title;
			$data['timesheets_title'] = $timesheets_title;
			$data['commissions_title'] = $commissions_title;

			//get data each type
			$get_attendance_type = $this->hr_payroll_model->setting_get_attendance_type();

			$data['actual_workday_type'] = $get_attendance_type['actual_workday'];
			$data['paid_leave_type'] = $get_attendance_type['paid_leave'];
			$data['unpaid_leave_type'] = $get_attendance_type['unpaid_leave'];
			$data['get_customize_payslip_columns'] = $this->hr_payroll_model->get_customize_payslip_columns();

		} elseif ($data['group'] == 'hr_records_earnings_list') {
			$earnings_value = [];
			$earnings_value[] = [
				'id' => 'monthly',
				'label' => _l('monthly'),
			];
			$earnings_value[] = [
				'id' => 'annual',
				'label' => _l('annual'),
			];

			$data['title'] = _l('earnings_list');
			$data['basis_value'] = $earnings_value;
			$data['earnings_list_hr_records'] = json_encode($this->hr_payroll_model->hr_records_get_earnings_list());
		} elseif($data['group'] == 'pdf_payslip_template'){
			$data['pdf_payslip_templates'] = $this->hr_payroll_model->get_pdf_payslip_template();

		} elseif($data['group'] == 'currency_rates'){
            $this->load->model('currencies_model');
            $this->hr_payroll_model->check_auto_create_currency_rate();

            $data['currencies'] = $this->currencies_model->get();
            if($data['unit_tab'] == ''){
                $data['unit_tab'] = 'general';
            }
        }

		$data['tabs']['view'] = 'includes/' . $data['group'];

		$this->load->view('includes/manage_setting', $data);
	}

	/**
	 * setting incometax rates
	 * @return [type]
	 */
	public function setting_incometax_rates() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_income_tax_rates($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=income_tax_rates'));

			}

		}
	}

	/**
	 * setting incometax rebates
	 * @return [type]
	 */
	public function setting_incometax_rebates() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_income_tax_rebates($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=income_tax_rebates'));

			}

		}
	}

	/**
	 * setting earnings list
	 * @return [type]
	 */
	public function setting_earnings_list() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_earnings_list($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=earnings_list'));

			}

		}
	}

	/**
	 * setting salary deductions list
	 * @return [type]
	 */
	public function setting_salary_deductions_list() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_salary_deductions_list($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=salary_deductions_list'));

			}

		}
	}

	/**
	 * setting insurance list
	 * @return [type]
	 */
	public function setting_insurance_list() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_insurance_list($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=insurance_list'));

			}

		}
	}

	/**
	 * setting company contributions list
	 * @return [type]
	 */
	public function setting_company_contributions_list() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->update_company_contributions_list($data);
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('warning', _l('hrp_updated_failed'));
				}

				redirect(admin_url('hr_payroll/setting?group=company_contributions_list'));

			}

		}
	}

	/**
	 * data integration
	 * @return [type]
	 */
	public function data_integration() {
		if (!is_admin()) {
			access_denied('hr_payroll');
		}

		$data = $this->input->post();

		$mess = $this->hr_payroll_model->update_data_integration($data);
		if ($mess) {
			set_alert('success', _l('hrp_updated_successfully'));

		} else {
			set_alert('warning', _l('hrp_updated_failed'));
		}

		redirect(admin_url('hr_payroll/setting?group=data_integration'));

	}

	/**
	 * timesheet integration type change
	 * @return [type]
	 */
	public function timesheet_integration_type_change() {
		if ($this->input->post()) {
			$data = $this->input->post();

			$results = $this->hr_payroll_model->get_timesheet_type_for_setting($data);

			echo json_encode([
				'actual_workday_v' => $results['actual_workday'],
				'paid_leave_v' => $results['paid_leave'],
				'unpaid_leave_v' => $results['unpaid_leave'],
			]);
			die;
		}
	}

	/**
	 * setting earnings list hr records
	 * @return [type]
	 */
	public function setting_earnings_list_hr_records() {
		if ($this->input->post()) {

			$data = $this->input->post();
			if (!$this->input->post('id')) {

				$mess = $this->hr_payroll_model->earnings_list_synchronization($data);
				set_alert('success', _l('hrp_successful_data_synchronization'));
				if ($mess) {
					set_alert('success', _l('hrp_updated_successfully'));

				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}

				redirect(admin_url('hr_payroll/setting?group=hr_records_earnings_list'));
			}
		}
	}

	/**
	 * hr payroll permission table
	 * @return [type]
	 */
	public function hr_payroll_permission_table() {
		if ($this->input->is_ajax_request()) {

			$select = [
				'staffid',
				'name as full_name',
				'name', //for role name
				'email',
				'phonenumber',
			];
			$where = [];
			$where[] = 'AND ' . db_prefix() . 'staff.admin != 1';

			$arr_staff_id = hr_payroll_get_staff_id_hr_permissions();

			if (count($arr_staff_id) > 0) {
				$where[] = 'AND ' . db_prefix() . 'staff.staffid IN (' . implode(', ', $arr_staff_id) . ')';
			} else {
				$where[] = 'AND ' . db_prefix() . 'staff.staffid IN ("")';
			}

			$aColumns = $select;
			$sIndexColumn = 'staffid';
			$sTable = db_prefix() . 'staff';
			$join = ['LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role'];

			$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'roles.name as role_name', db_prefix() . 'staff.role']);

			$output = $result['output'];
			$rResult = $result['rResult'];

			$not_hide = '';

			foreach ($rResult as $aRow) {
				$row = [];

				$row[] = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['full_name'] . '</a>';

				$row[] = $aRow['role_name'];
				$row[] = $aRow['email'];
				$row[] = $aRow['phonenumber'];

				$options = '';

				if (has_permission('hrm_setting', '', 'edit')) {
					$options = icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
						'title' => _l('hr_edit'),
						'onclick' => 'hr_payroll_permissions_update(' . $aRow['staffid'] . ', ' . $aRow['role'] . ', ' . $not_hide . '); return false;',
					]);
				}

				if (has_permission('hrm_setting', '', 'delete')) {
					$options .= icon_btn('hr_payroll/delete_hr_payroll_permission/' . $aRow['staffid'], 'fa fa-remove', 'btn-danger _delete', ['title' => _l('delete')]);
				}

				$row[] = $options;

				$output['aaData'][] = $row;
			}

			echo json_encode($output);
			die();
		}
	}

	/**
	 * permission modal
	 * @return [type]
	 */
	public function permission_modal() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->load->model('staff_model');

		if ($this->input->post('slug') === 'update') {
			$staff_id = $this->input->post('staff_id');
			$role_id = $this->input->post('role_id');

			$data = ['funcData' => ['staff_id' => isset($staff_id) ? $staff_id : null]];

			if (isset($staff_id)) {
				$data['member'] = $this->staff_model->get($staff_id);
			}

			$data['roles_value'] = $this->roles_model->get();
			$data['staffs'] = hr_payroll_get_staff_id_dont_permissions();
			$add_new = $this->input->post('add_new');

			if ($add_new == ' hide') {
				$data['add_new'] = ' hide';
				$data['display_staff'] = '';
			} else {
				$data['add_new'] = '';
				$data['display_staff'] = ' hide';
			}

			$this->load->view('includes/permission_modal', $data);
		}
	}

	/**
	 * hr payroll update permissions
	 * @param  string $id
	 * @return [type]
	 */
	public function hr_payroll_update_permissions($id = '') {
		if (!is_admin()) {
			access_denied('hr_payroll');
		}
		$data = $this->input->post();

		if (!isset($id) || $id == '') {
			$id = $data['staff_id'];
		}

		if (isset($id) && $id != '') {

			$data = hooks()->apply_filters('before_update_staff_member', $data, $id);

			if (is_admin()) {
				if (isset($data['administrator'])) {
					$data['admin'] = 1;
					unset($data['administrator']);
				} else {
					if ($id != get_staff_user_id()) {
						if ($id == 1) {
							return [
								'cant_remove_main_admin' => true,
							];
						}
					} else {
						return [
							'cant_remove_yourself_from_admin' => true,
						];
					}
					$data['admin'] = 0;
				}
			}

			$this->db->where('staffid', $id);
			$this->db->update(db_prefix() . 'staff', [
				'role' => $data['role'],
			]);

			$response = $this->staff_model->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $data['permissions']), $id);
		} else {
			$this->load->model('roles_model');

			$role_id = $data['role'];
			unset($data['role']);
			unset($data['staff_id']);

			$data['update_staff_permissions'] = true;

			$response = $this->roles_model->update($data, $role_id);
		}

		if (is_array($response)) {
			if (isset($response['cant_remove_main_admin'])) {
				set_alert('warning', _l('staff_cant_remove_main_admin'));
			} elseif (isset($response['cant_remove_yourself_from_admin'])) {
				set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
			}
		} elseif ($response == true) {
			set_alert('success', _l('updated_successfully', _l('staff_member')));
		}
		redirect(admin_url('hr_payroll/setting?group=permissions'));

	}

	/**
	 * staff id changed
	 * @param  [type] $staff_id
	 * @return [type]
	 */
	public function staff_id_changed($staff_id) {
		$role_id = '';
		$status = 'false';
		$r_permission = [];

		$staff = $this->staff_model->get($staff_id);

		if ($staff) {
			if (count($staff->permissions) > 0) {
				foreach ($staff->permissions as $permission) {
					$r_permission[$permission['feature']][] = $permission['capability'];
				}
			}

			$role_id = $staff->role;
			$status = 'true';

		}

		if (count($r_permission) > 0) {
			$data = ['role_id' => $role_id, 'status' => $status, 'permission' => 'true', 'r_permission' => $r_permission];
		} else {
			$data = ['role_id' => $role_id, 'status' => $status, 'permission' => 'false', 'r_permission' => $r_permission];
		}

		echo json_encode($data);
		die;
	}

	/**
	 * delete hr payroll permission
	 * @param  [type] $id
	 * @return [type]
	 */
	public function delete_hr_payroll_permission($id) {
		if (!is_admin()) {
			access_denied('hr_profile');
		}

		$response = $this->hr_payroll_model->delete_hr_payroll_permission($id);

		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('hr_is_referenced', _l('department_lowercase')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted', _l('hr_department')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('department_lowercase')));
		}
		redirect(admin_url('hr_payroll/setting?group=permissions'));

	}

	/**
	 * manage employees
	 * @return [type]
	 */
	// public function manage_employees() {
	// 	if (!has_permission('hrp_employee', '', 'view') && !has_permission('hrp_employee', '', 'view_own') && !is_admin()) {
	// 		access_denied('hrp_employee');
	// 	}

	// 	$company_filter = $this->input->get('company'); // either "mahiroon" or "mohtarifeen"

	// 	$company_name = '';
	// 	if($company_filter == 'mohtarifeen') {
	// 		$company_name = 'Mohtarifeen';
	// 	} else if($company_filter == 'mahiroon') {
	// 		$company_name = 'Mahiroon';
	// 	}

	// 	$data['company_name'] = $company_name;
	// 	$this->load->model('staff_model');
	// 	$this->load->model('departments_model');

	// 	$rel_type = hrp_get_hr_profile_status();

	// 	//get current month
	// 	$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
	// 	$employees_data = $this->hr_payroll_model->get_employees_data($current_month, $rel_type);
	// 	$employees_value = [];
	// 	foreach ($employees_data as $key => $value) {
	// 		$employees_value[$value['staff_id'] . '_' . $value['month']] = $value;
	// 	}
	// 	//get employee data for the first
	// 	$format_employees_value = $this->hr_payroll_model->get_format_employees_data($rel_type);

	// 	//load staff
	// 	if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
	// 		//View own
	// 		$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
	// 	} else {
	// 		//admin or view global
	// 		$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
	// 	}

	// 	// Exclude staff with staffid 1 and 3
	// 	$staffs = array_values(array_filter($staffs, function ($staff) {
	// 		return !in_array((int)$staff['staffid'], [1, 3]);
	// 	}));

	// 	/* ✅ Filter by company_id */
	// 	if ($company_filter == 'mohtarifeen') {
	// 		// Show only Mohtarifeen (company_id = 2)
	// 		$staffs = array_values(array_filter($staffs, function ($staff) {
	// 			return isset($staff['companytype_id']) && (int)$staff['companytype_id'] === 2;
	// 		}));
	// 	} elseif ($company_filter == 'mahiroon') {
	// 		// Show Mahiroon (all except companytype_id = 2)
	// 		$staffs = array_values(array_filter($staffs, function ($staff) {
	// 			return isset($staff['companytype_id']) && (int)$staff['companytype_id'] !== 2;
	// 		}));
	// 	}

	// 	//get current month

	// 	$data_object_kpi = [];

	// 	foreach ($staffs as $staff_key => $staff_value) {
	// 		/*check value from database*/
	// 		$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

	// 		$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
	// 		if ($staff_i) {

	// 			if ($rel_type == 'hr_records') {
	// 				$data_object_kpi[$staff_key]['employee_number'] = $staff_i->staff_identifi;
	// 			} else {
	// 				$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
	// 			}

	// 			$data_object_kpi[$staff_key]['employee_name'] = $staff_i->name;

	// 			$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

	// 			$list_department = '';
	// 			if (count($arr_department) > 0) {

	// 				foreach ($arr_department as $key => $department) {
	// 					$department_value = $this->departments_model->get($department);

	// 					if ($department_value) {
	// 						if (new_strlen($list_department) != 0) {
	// 							$list_department .= ', ' . $department_value->name;
	// 						} else {
	// 							$list_department .= $department_value->name;
	// 						}
	// 					}
	// 				}
	// 			}

	// 			$data_object_kpi[$staff_key]['department_name'] = $list_department;

	// 		} else {
	// 			$data_object_kpi[$staff_key]['employee_number'] = '';
	// 			$data_object_kpi[$staff_key]['employee_name'] = '';
	// 			$data_object_kpi[$staff_key]['department_name'] = '';
	// 		}

	// 		if ($rel_type == 'hr_records') {
	// 			$data_object_kpi[$staff_key]['job_title'] = $staff_value['position_name'];
	// 			$data_object_kpi[$staff_key]['income_tax_number'] = $staff_value['Personal_tax_code'];
	// 			$data_object_kpi[$staff_key]['residential_address'] = $staff_value['resident'];
	// 			$data_object_kpi[$staff_key]['bank_name'] = $staff_value['issue_bank'];
	// 			$data_object_kpi[$staff_key]['account_number'] = $staff_value['account_number'];
	// 			$data_object_kpi[$staff_key]['epf_no'] = $staff_value['epf_no'];
	// 			$data_object_kpi[$staff_key]['social_security_no'] = $staff_value['social_security_no'];
	// 		} else {
	// 			if (isset($employees_value[$staff_value['staffid'] . '_' . $current_month])) {
	// 			// 	$data_object_kpi[$staff_key]['job_title'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['job_title'];
	// 			// 	$data_object_kpi[$staff_key]['income_tax_number'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['income_tax_number'];
	// 			// 	$data_object_kpi[$staff_key]['residential_address'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['residential_address'];
	// 			// 	$data_object_kpi[$staff_key]['bank_name'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['bank_name'];
	// 			// 	$data_object_kpi[$staff_key]['account_number'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['account_number'];
	// 			// 	$data_object_kpi[$staff_key]['epf_no'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['epf_no'];
	// 			// 	$data_object_kpi[$staff_key]['social_security_no'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['social_security_no'];

	// 				$data_object_kpi[$staff_key]['employee_id_iqama'] = !empty($employees_value[$staff_value['staffid'] . '_' . $current_month]['employee_id_iqama'])
	// 																? $employees_value[$staff_value['staffid'] . '_' . $current_month]['employee_id_iqama']
	// 																: $staff_value['iqama_number'];
	// 				$data_object_kpi[$staff_key]['employee_account_no_iban'] = !empty($employees_value[$staff_value['staffid'] . '_' . $current_month]['employee_account_no_iban'])
	// 																? $employees_value[$staff_value['staffid'] . '_' . $current_month]['employee_account_no_iban']
	// 																: $staff_value['bank_iban_number'];
	// 				$data_object_kpi[$staff_key]['bank_code'] = !empty($employees_value[$staff_value['staffid'] . '_' . $current_month]['bank_code']) ? $employees_value[$staff_value['staffid'] . '_' . $current_month]['bank_code'] : $staff_value['bank_swift_code'];
	// 				$data_object_kpi[$staff_key]['gosi_basic_salary'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['gosi_basic_salary'];
	// 				$data_object_kpi[$staff_key]['gosi_housing_allowance'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['gosi_housing_allowance'];
	// 				$data_object_kpi[$staff_key]['gosi_other_allowance'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['gosi_other_allowance'];
	// 				$data_object_kpi[$staff_key]['gosi_deduction'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['gosi_deduction'];
	// 				$data_object_kpi[$staff_key]['total_amount'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['total_amount'];
	// 				$data_object_kpi[$staff_key]['balance'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['balance'];
	// 				$data_object_kpi[$staff_key]['full_salary'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['full_salary'];
	// 				// $data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['basic'];
	// 				$data_object_kpi[$staff_key]['basic'] = isset($staff_i->basics) ? (string) $staff_i->basics : '';  // Cast to string, fallback empty
	// 				// $data_object_kpi[$staff_key]['ot_hours'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_hours'];
	// 				$ot_hours_val = $this->hr_payroll_model->get_total_ot_hours($staff_value['staffid'], $current_month);
    //     			$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
	// 				// $data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_rate'];
	// 				$data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_rate'] ?? (isset($staff_i->ot) ? (string) $staff_i->ot : '');
	// 				// $data_object_kpi[$staff_key]['ot_amount'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_amount'];
	// 				$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));  // Parse rate, ignore commas
    //     			$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 				$data_object_kpi[$staff_key]['allowance'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['allowance'];
	// 				$data_object_kpi[$staff_key]['deduction'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['deduction'];
	// 				$data_object_kpi[$staff_key]['mention'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['mention'];

	// 			} else {
	// 				// $data_object_kpi[$staff_key]['job_title'] = '';
	// 				// $data_object_kpi[$staff_key]['income_tax_number'] = '';
	// 				// $data_object_kpi[$staff_key]['residential_address'] = '';
	// 				// $data_object_kpi[$staff_key]['bank_name'] = '';
	// 				// $data_object_kpi[$staff_key]['account_number'] = '';
	// 				// $data_object_kpi[$staff_key]['epf_no'] = '';
	// 				// $data_object_kpi[$staff_key]['social_security_no'] = '';

	// 				$data_object_kpi[$staff_key]['employee_id_iqama'] = '';
	// 				$data_object_kpi[$staff_key]['employee_account_no_iban'] = '';
	// 				$data_object_kpi[$staff_key]['bank_code'] = '';
	// 				$data_object_kpi[$staff_key]['gosi_basic_salary'] = '';
	// 				$data_object_kpi[$staff_key]['gosi_housing_allowance'] = '';
	// 				$data_object_kpi[$staff_key]['gosi_other_allowance'] = '';
	// 				$data_object_kpi[$staff_key]['gosi_deduction'] = '';
	// 				$data_object_kpi[$staff_key]['total_amount'] = '';
	// 				$data_object_kpi[$staff_key]['balance'] = '';
	// 				$data_object_kpi[$staff_key]['full_salary'] = '';
	// 				// $data_object_kpi[$staff_key]['basic'] = '';
	// 				// $data_object_kpi[$staff_key]['ot_hours'] = '';
	// 				// $data_object_kpi[$staff_key]['ot_rate'] = '';
	// 				// $data_object_kpi[$staff_key]['ot_amount'] = '';
	// 				$data_object_kpi[$staff_key]['basic'] = isset($staff_i->basics) ? (string) $staff_i->basics : '';

	// 				$ot_hours_val = $this->hr_payroll_model->get_total_ot_hours($staff_value['staffid'], $current_month);
	// 				$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);

	// 				// NEW: Auto-populate ot_rate from staff
	// 				$data_object_kpi[$staff_key]['ot_rate'] = isset($staff_i->ot) ? (string) $staff_i->ot : '0';  // Default to '0' if missing

	// 				// NEW: Calculate ot_amount
	// 				$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
	// 				$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 				$data_object_kpi[$staff_key]['allowance'] = '';
	// 				$data_object_kpi[$staff_key]['deduction'] = '';
	// 				$data_object_kpi[$staff_key]['mention'] = '';
	// 			}
	// 		}

	// 		if (isset($employees_value[$staff_value['staffid'] . '_' . $current_month])) {

	// 			$data_object_kpi[$staff_key]['income_rebate_code'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['income_rebate_code'];
	// 			$data_object_kpi[$staff_key]['income_tax_rate'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['income_tax_rate'];

	// 			// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 			if (isset($employees_value[$staff_value['staffid'] . '_' . $current_month]['contract_value'])) {

	// 				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $employees_value[$staff_value['staffid'] . '_' . $current_month]['contract_value']);
	// 			} else {
	// 				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);
	// 			}

	// 			$data_object_kpi[$staff_key]['probationary_effective'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['probationary_effective'];
	// 			$data_object_kpi[$staff_key]['probationary_expiration'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['probationary_expiration'];
	// 			$data_object_kpi[$staff_key]['primary_effective'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['primary_effective'];
	// 			$data_object_kpi[$staff_key]['primary_expiration'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['primary_expiration'];

	// 			$data_object_kpi[$staff_key]['id'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['id'];


	// 		} else {
	// 			$data_object_kpi[$staff_key]['income_rebate_code'] = 'A';
	// 			$data_object_kpi[$staff_key]['income_tax_rate'] = 'A';

	// 			// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 			$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);

	// 			$data_object_kpi[$staff_key]['probationary_effective'] = '';
	// 			$data_object_kpi[$staff_key]['probationary_expiration'] = '';
	// 			$data_object_kpi[$staff_key]['primary_effective'] = '';
	// 			$data_object_kpi[$staff_key]['primary_expiration'] = '';

	// 			$data_object_kpi[$staff_key]['id'] = 0;

	// 		}

	// 		$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
	// 	}
	// 	//check is add new or update data
	// 	if (count($employees_value) > 0) {
	// 		$data['button_name'] = _l('hrp_update');
	// 	} else {
	// 		$data['button_name'] = _l('submit');
	// 	}

	// 	$data['departments'] = $this->departments_model->get();
	// 	$data['roles'] = $this->roles_model->get();
	// 	$data['staffs'] = $staffs;

	// 	$data['body_value'] = json_encode($data_object_kpi);
	// 	$data['columns'] = json_encode($format_employees_value['column_format']);
	// 	$data['col_header'] = json_encode($format_employees_value['header']);

	// 	$this->load->view('employees/employees_manage', $data);
	// }

	/**
	 * employees filter
	 * @return [type]
	 */
	// public function employees_filter() {
	// 	$this->load->model('departments_model');
	// 	$data = $this->input->post();

	// 	$rel_type = hrp_get_hr_profile_status();

	// 	$months_filter = $data['month'];
	// 	$department = isset($data['department']) ? $data['department'] : '';
	// 	$staff = '';
	// 	if (isset($data['staff'])) {
	// 		$staff = $data['staff'];
	// 	}
	// 	$role_attendance = '';
	// 	if (isset($data['role_attendance'])) {
	// 		$role_attendance = $data['role_attendance'];
	// 	}

	// 	$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

	// 	//get current month
	// 	$month_filter = date('Y-m', strtotime($data['month']));
	// 	$month_filter_db = date('Y-m-d', strtotime($data['month'])); 

	// 	$employees_data = $this->hr_payroll_model->get_employees_data($month_filter_db, $rel_type);
	// 	$employees_value = [];
	// 	foreach ($employees_data as $key => $value) {
	// 		// Normalize DB month
	// 		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value['month'])) {
	// 			// Case like 2025-08-01 → convert to Y-m
	// 			$normalized_month = date('Y-m', strtotime($value['month']));
	// 		} elseif (preg_match('/^\d{2}$/', $value['month'])) {
	// 			// Case like 07 → assume current year + month
	// 			$normalized_month = date('Y') . '-' . $value['month'];
	// 		} else {
	// 			// Fallback, store as-is
	// 			$normalized_month = $value['month'];
	// 		}

	// 		$employees_value[$value['staff_id'] . '_' . $normalized_month] = $value;
	// 	}

	// 	//get employee data for the first
	// 	$format_employees_value = $this->hr_payroll_model->get_format_employees_data($rel_type);

	// 	// data return
	// 	$data_object_kpi = [];
	// 	$index_data_object = 0;
	// 	if ($newquerystring != '') {

	// 		//load deparment by manager
	// 		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
	// 			//View own
	// 			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
	// 		} else {
	// 			//admin or view global
	// 			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
	// 		}

	// 		$data_object_kpi = [];

	// 		foreach ($staffs as $staff_key => $staff_value) {
	// 			/*check value from database*/
	// 			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

	// 			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
	// 			if ($staff_i) {

	// 				if ($rel_type == 'hr_records') {
	// 					$data_object_kpi[$staff_key]['employee_number'] = $staff_i->staff_identifi;
	// 				} else {
	// 					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
	// 				}

	// 				$data_object_kpi[$staff_key]['employee_name'] = $staff_i->name;

	// 				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

	// 				$list_department = '';
	// 				if (count($arr_department) > 0) {

	// 					foreach ($arr_department as $key => $department) {
	// 						$department_value = $this->departments_model->get($department);

	// 						if ($department_value) {
	// 							if (new_strlen($list_department) != 0) {
	// 								$list_department .= ', ' . $department_value->name;
	// 							} else {
	// 								$list_department .= $department_value->name;
	// 							}
	// 						}
	// 					}
	// 				}

	// 				$data_object_kpi[$staff_key]['department_name'] = $list_department;

	// 			} else {
	// 				$data_object_kpi[$staff_key]['employee_number'] = '';
	// 				$data_object_kpi[$staff_key]['employee_name'] = '';
	// 				$data_object_kpi[$staff_key]['department_name'] = '';
	// 			}

	// 			if ($rel_type == 'hr_records') {
	// 				$data_object_kpi[$staff_key]['job_title'] = $staff_value['position_name'];
	// 				$data_object_kpi[$staff_key]['income_tax_number'] = $staff_value['Personal_tax_code'];
	// 				$data_object_kpi[$staff_key]['residential_address'] = $staff_value['resident'];
	// 				$data_object_kpi[$staff_key]['bank_name'] = $staff_value['issue_bank'];
	// 				$data_object_kpi[$staff_key]['account_number'] = $staff_value['account_number'];
	// 				$data_object_kpi[$staff_key]['epf_no'] = $staff_value['epf_no'];
	// 				$data_object_kpi[$staff_key]['social_security_no'] = $staff_value['social_security_no'];
	// 			} else {
	// 				if (isset($employees_value[$staff_value['staffid'] . '_' . $month_filter])) {
	// 					$data_object_kpi[$staff_key]['employee_id_iqama'] = !empty($employees_value[$staff_value['staffid'] . '_' . $month_filter]['employee_id_iqama'])
	// 																? $employees_value[$staff_value['staffid'] . '_' . $month_filter]['employee_id_iqama']
	// 																: $staff_value['iqama_number'];
	// 					$data_object_kpi[$staff_key]['employee_account_no_iban'] = !empty($employees_value[$staff_value['staffid'] . '_' . $month_filter]['employee_account_no_iban'])
	// 																	? $employees_value[$staff_value['staffid'] . '_' . $month_filter]['employee_account_no_iban']
	// 																	: $staff_value['bank_iban_number'];
	// 					$data_object_kpi[$staff_key]['bank_code'] = !empty($employees_value[$staff_value['staffid'] . '_' . $month_filter]['bank_code']) ? $employees_value[$staff_value['staffid'] . '_' . $month_filter]['bank_code'] : $staff_value['bank_swift_code'];
	// 					$data_object_kpi[$staff_key]['gosi_basic_salary'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['gosi_basic_salary'];
	// 					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['gosi_housing_allowance'];
	// 					$data_object_kpi[$staff_key]['gosi_other_allowance'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['gosi_other_allowance'];
	// 					$data_object_kpi[$staff_key]['gosi_deduction'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['gosi_deduction'];
	// 					$data_object_kpi[$staff_key]['total_amount'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['total_amount'];
	// 					$data_object_kpi[$staff_key]['balance'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['balance'];
	// 					$data_object_kpi[$staff_key]['full_salary'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['full_salary'];
	// 					// $data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['basic'];
	// 					$data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['basic'];
	// 					// $data_object_kpi[$staff_key]['ot_hours'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_hours'];
	// 					$ot_hours_val = $this->hr_payroll_model->get_total_ot_hours($staff_value['staffid'], $month_filter_db);
    //     				$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
	// 					// $data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_rate'];
	// 					$data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_rate'] ?? (isset($staff_i->ot) ? (string) $staff_i->ot : '');
	// 					// $data_object_kpi[$staff_key]['ot_amount'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_amount'];
	// 					$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
    //     				$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 					$data_object_kpi[$staff_key]['allowance'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['allowance'];
	// 					$data_object_kpi[$staff_key]['deduction'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['deduction'];
	// 					$data_object_kpi[$staff_key]['mention'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['mention'];

	// 				} else {
	// 					$data_object_kpi[$staff_key]['employee_id_iqama'] = $staff_value['iqama_number'] ?? '';
    // 					$data_object_kpi[$staff_key]['employee_account_no_iban'] = $staff_value['bank_iban_number'] ?? '';
	// 					$data_object_kpi[$staff_key]['bank_code'] = $staff_value['bank_swift_code'] ?? '';
	// 					$data_object_kpi[$staff_key]['gosi_basic_salary'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_other_allowance'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_deduction'] = '';
	// 					$data_object_kpi[$staff_key]['total_amount'] = '';
	// 					$data_object_kpi[$staff_key]['balance'] = '';
	// 					$data_object_kpi[$staff_key]['full_salary'] = '';
	// 					// $data_object_kpi[$staff_key]['basic'] = '';
	// 					$data_object_kpi[$staff_key]['basic'] = isset($staff_i->basics) ? (string) $staff_i->basics : '';
	// 					// $data_object_kpi[$staff_key]['ot_hours'] = '';
	// 					$ot_hours = $this->hr_payroll_model->get_total_ot_hours($staff_value['staffid'], $month_filter_db);  // $month_filter_db is first-of-month
    //     				$ot_hours_val = $this->hr_payroll_model->get_total_ot_hours($staff_value['staffid'], $month_filter_db);
    //     				$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
	// 					// $data_object_kpi[$staff_key]['ot_rate'] = '';
	// 					// $data_object_kpi[$staff_key]['ot_amount'] = '';
	// 					$data_object_kpi[$staff_key]['ot_rate'] = isset($staff_i->ot) ? (string) $staff_i->ot : '0';

    //     				// NEW: Calculate ot_amount
	// 					$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
	// 					$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 					$data_object_kpi[$staff_key]['allowance'] = '';
	// 					$data_object_kpi[$staff_key]['deduction'] = '';
	// 					$data_object_kpi[$staff_key]['mention'] = '';
	// 				}
	// 			}

	// 			if (isset($employees_value[$staff_value['staffid'] . '_' . $month_filter])) {

	// 				$data_object_kpi[$staff_key]['income_rebate_code'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['income_rebate_code'];
	// 				$data_object_kpi[$staff_key]['income_tax_rate'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['income_tax_rate'];

	// 				$data_object_kpi[$staff_key]['probationary_effective'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['probationary_effective'];
	// 				$data_object_kpi[$staff_key]['probationary_expiration'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['probationary_expiration'];
	// 				$data_object_kpi[$staff_key]['primary_effective'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['primary_effective'];
	// 				$data_object_kpi[$staff_key]['primary_expiration'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['primary_expiration'];

	// 				// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 				if (isset($employees_value[$staff_value['staffid'] . '_' . $month_filter]['contract_value'])) {

	// 					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $employees_value[$staff_value['staffid'] . '_' . $month_filter]['contract_value']);
	// 				} else {
	// 					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);
	// 				}

	// 				$data_object_kpi[$staff_key]['id'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['id'];


	// 			} else {
	// 				$data_object_kpi[$staff_key]['income_rebate_code'] = 'A';
	// 				$data_object_kpi[$staff_key]['income_tax_rate'] = 'A';

	// 				// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);

	// 				$data_object_kpi[$staff_key]['id'] = 0;

	// 			}

	// 			$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
	// 		}

	// 	}

	// 	//check is add new or update data
	// 	if (count($employees_value) > 0) {
	// 		$button_name = _l('hrp_update');
	// 	} else {
	// 		$button_name = _l('submit');
	// 	}

	// 	echo json_encode([
	// 		'data_object' => $data_object_kpi,
	// 		'button_name' => $button_name,
	// 	]);
	// 	die;
	// }

	public function manage_employees() {
		if (!has_permission('hrp_employee', '', 'view') && !has_permission('hrp_employee', '', 'view_own') && !is_admin()) {
			access_denied('hrp_employee');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_hr_profile_status();

		// Check if this is for a specific payroll
		$payroll_id = $this->input->get('payroll_id');
		$payroll = null;
		$is_payroll_mode = false;

		if ($payroll_id) {
			// Load payroll details
			$payroll = $this->hr_payroll_model->get_payroll_details($payroll_id);

			if (!$payroll) {
				show_error('Payroll not found', 404);
				return;
			}

			$is_payroll_mode = true;
			$current_month = $payroll->month;
			$current_month_key = date('Y-m', strtotime($current_month));
			$company_filter = $payroll->company_filter; // Company ID
			$company_name = $payroll->company_name ?? '';
			$ownemployee_type_id = $payroll->ownemployee_type_id;

			// Get employees for this specific payroll
			$employees_data = $this->hr_payroll_model->get_payroll_employees($payroll_id);

			$data['payroll'] = $payroll;
		} else {
			// Legacy mode: use GET parameters
			$company_filter = $this->input->get('company'); // either "mahiroon" or "mohtarifeen"
			$company_name = '';
			if($company_filter == 'mohtarifeen') {
				$company_name = 'Mohtarifeen';
			} else if($company_filter == 'mahiroon') {
				$company_name = 'Mahiroon';
			}

			//get current month
			$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
			$current_month_key = date('Y-m', strtotime($current_month)); // For normalized key: YYYY-MM
			$employees_data = $this->hr_payroll_model->get_employees_data($current_month, $rel_type);
		}

		$data['company_name'] = $company_name;
		$data['is_payroll_mode'] = $is_payroll_mode;

		// Build employees_value array
		$employees_value = [];
		foreach ($employees_data as $key => $value) {
			// Normalize DB month
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value['month'])) {
				// Case like 2025-08-01 → convert to Y-m
				$normalized_month = date('Y-m', strtotime($value['month']));
			} elseif (preg_match('/^\d{2}$/', $value['month'])) {
				// Case like 07 → assume current year + month
				$normalized_month = date('Y') . '-' . str_pad($value['month'], 2, '0', STR_PAD_LEFT);
			} else {
				// Fallback, store as-is
				$normalized_month = $value['month'];
			}

			$employees_value[$value['staff_id'] . '_' . $normalized_month] = $value;
		}
		//get employee data for the first
		$format_employees_value = $this->hr_payroll_model->get_format_employees_data($rel_type);

		//load staff
		if ($is_payroll_mode) {
			// In payroll mode, load only employees that belong to this payroll
			$payroll_staff_ids = array_column($employees_data, 'staff_id');

			// DEBUG: Log payroll staff IDs
			log_activity('Payroll Mode - Found ' . count($employees_data) . ' employees in payroll_id=' . $payroll_id);
			log_activity('Payroll Mode - Staff IDs: ' . implode(', ', $payroll_staff_ids));

			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
			}

			log_activity('Payroll Mode - Before filter: ' . count($staffs) . ' total staff');

			// Filter to only show staff IDs that are in this payroll
			$staffs = array_values(array_filter($staffs, function ($staff) use ($payroll_staff_ids) {
				return in_array($staff['staffid'], $payroll_staff_ids);
			}));

			log_activity('Payroll Mode - After filter: ' . count($staffs) . ' filtered staff');
		} else {
			// Legacy mode: load and filter staff as before
			// Get permanent employee type ID
			$this->load->helper('ownemployee_type');
			$permanent_type_id = get_ownemployee_type_id_by_name('Permanent');

			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
			}

			// Exclude staff with staffid 1 and 3
			$staffs = array_values(array_filter($staffs, function ($staff) {
				return !in_array((int)$staff['staffid'], [1, 3]);
			}));

			// Filter to only show permanent employees (exclude contract, temporary, etc.)
			if ($permanent_type_id) {
				$staffs = array_values(array_filter($staffs, function ($staff) use ($permanent_type_id) {
					return isset($staff['ownemployee_id']) && (int)$staff['ownemployee_id'] === $permanent_type_id;
				}));
			}

			/* ✅ Filter by company_id */
			if ($company_filter == 'mohtarifeen') {
				// Show only Mohtarifeen (company_id = 2)
				$staffs = array_values(array_filter($staffs, function ($staff) {
					return isset($staff['companytype_id']) && (int)$staff['companytype_id'] === 2;
				}));
			} elseif ($company_filter == 'mahiroon') {
				// Show Mahiroon (all except companytype_id = 2)
				$staffs = array_values(array_filter($staffs, function ($staff) {
					return isset($staff['companytype_id']) && (int)$staff['companytype_id'] !== 2;
				}));
			}
		}

		//get current month

		$data_object_kpi = [];

		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];
			$data_object_kpi[$staff_key]['select'] = false; // Initialize checkbox as unchecked

			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['employee_number'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi[$staff_key]['employee_name'] = $staff_i->name;

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$data_object_kpi[$staff_key]['department_name'] = $list_department;

			} else {
				$data_object_kpi[$staff_key]['employee_number'] = '';
				$data_object_kpi[$staff_key]['employee_name'] = '';
				$data_object_kpi[$staff_key]['department_name'] = '';
			}

			if ($rel_type == 'hr_records') {
				$data_object_kpi[$staff_key]['job_title'] = $staff_value['position_name'];
				$data_object_kpi[$staff_key]['income_tax_number'] = $staff_value['Personal_tax_code'];
				$data_object_kpi[$staff_key]['residential_address'] = $staff_value['resident'];
				$data_object_kpi[$staff_key]['bank_name'] = $staff_value['issue_bank'];
				$data_object_kpi[$staff_key]['account_number'] = $staff_value['account_number'];
				$data_object_kpi[$staff_key]['epf_no'] = $staff_value['epf_no'];
				$data_object_kpi[$staff_key]['social_security_no'] = $staff_value['social_security_no'];
			} else {
				$db_key = $staff_value['staffid'] . '_' . $current_month_key;
				$has_db_record = isset($employees_value[$db_key]);

				if ($has_db_record) {
					// 	$data_object_kpi[$staff_key]['job_title'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['job_title'];
					// 	$data_object_kpi[$staff_key]['income_tax_number'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['income_tax_number'];
					// 	$data_object_kpi[$staff_key]['residential_address'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['residential_address'];
					// 	$data_object_kpi[$staff_key]['bank_name'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['bank_name'];
					// 	$data_object_kpi[$staff_key]['account_number'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['account_number'];
					// 	$data_object_kpi[$staff_key]['epf_no'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['epf_no'];
					// 	$data_object_kpi[$staff_key]['social_security_no'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['social_security_no'];

					$data_object_kpi[$staff_key]['employee_id_iqama'] = !empty($employees_value[$db_key]['employee_id_iqama'])
																	? $employees_value[$db_key]['employee_id_iqama']
																	: $staff_value['iqama_number'];
					$data_object_kpi[$staff_key]['employee_account_no_iban'] = !empty($employees_value[$db_key]['employee_account_no_iban'])
																	? $employees_value[$db_key]['employee_account_no_iban']
																	: $staff_value['bank_iban_number'];
					$data_object_kpi[$staff_key]['bank_code'] = !empty($employees_value[$db_key]['bank_code']) ? $employees_value[$db_key]['bank_code'] : $staff_value['bank_swift_code'];
					$data_object_kpi[$staff_key]['gosi_basic_salary'] = $employees_value[$db_key]['gosi_basic_salary'];
					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = $employees_value[$db_key]['gosi_housing_allowance'];
					$data_object_kpi[$staff_key]['gosi_other_allowance'] = $employees_value[$db_key]['gosi_other_allowance'];
					$data_object_kpi[$staff_key]['gosi_deduction'] = $employees_value[$db_key]['gosi_deduction'];
					$data_object_kpi[$staff_key]['total_amount'] = $employees_value[$db_key]['total_amount'];
					$data_object_kpi[$staff_key]['balance'] = $employees_value[$db_key]['balance'];
					$data_object_kpi[$staff_key]['full_salary'] = $employees_value[$db_key]['full_salary'];
					// $data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['basic'];
					$data_object_kpi[$staff_key]['basic'] = $employees_value[$db_key]['basic'];
					// $data_object_kpi[$staff_key]['ot_hours'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_hours'];
					$ot_hours_val = floatval($employees_value[$db_key]['ot_hours'] ?? 0);
					$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
					// $data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_rate'];
					$data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$db_key]['ot_rate'] ?? (isset($staff_i->ot) ? (string) $staff_i->ot : '');
					// $data_object_kpi[$staff_key]['ot_amount'] = $employees_value[$staff_value['staffid'] . '_' . $current_month]['ot_amount'];
					$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));  // Parse rate, ignore commas
					$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
					$data_object_kpi[$staff_key]['allowance'] = $employees_value[$db_key]['allowance'];

					// ALWAYS get real-time additions/deductions from tblhrp_adjustments (not stored values)
					$hrp_additions = $this->hr_payroll_model->get_additions_for_month($staff_value['staffid'], $current_month);
					$hrp_deductions = $this->hr_payroll_model->get_deductions_for_month($staff_value['staffid'], $current_month);
					$data_object_kpi[$staff_key]['additions'] = number_format($hrp_additions, 2);
					$data_object_kpi[$staff_key]['deduction'] = number_format($hrp_deductions, 2);
					$data_object_kpi[$staff_key]['mention'] = $employees_value[$db_key]['mention'];
					$data_object_kpi[$staff_key]['payroll_month'] = isset($employees_value[$db_key]['payroll_month']) ? $employees_value[$db_key]['payroll_month'] : date('F Y', strtotime($current_month));
					$data_object_kpi[$staff_key]['comment_1'] = isset($employees_value[$db_key]['comment_1']) ? $employees_value[$db_key]['comment_1'] : '';
					$data_object_kpi[$staff_key]['comment_2'] = isset($employees_value[$db_key]['comment_2']) ? $employees_value[$db_key]['comment_2'] : '';
					$data_object_kpi[$staff_key]['comment_3'] = isset($employees_value[$db_key]['comment_3']) ? $employees_value[$db_key]['comment_3'] : '';

				} else {
					// No saved payroll data - get defaults from tblstaffpay
					$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_value['staffid'], $current_month);

					// Get additions/deductions from tblhrp_adjustments (separate from allowances)
					$hrp_additions = $this->hr_payroll_model->get_additions_for_month($staff_value['staffid'], $current_month);
					$hrp_deductions = $this->hr_payroll_model->get_deductions_for_month($staff_value['staffid'], $current_month);

					$data_object_kpi[$staff_key]['employee_id_iqama'] = $staff_value['iqama_number'] ?? '';
					$data_object_kpi[$staff_key]['employee_account_no_iban'] = $staff_value['bank_iban_number'] ?? '';
					$data_object_kpi[$staff_key]['bank_code'] = $staff_value['bank_swift_code'] ?? '';

					if ($staff_pay) {
						// Use tblstaffpay data
						$basic_pay = floatval($staff_pay->basic_pay ?? 0);
						$overtime_rate = floatval($staff_pay->overtime_pay ?? 0);
						$food_allowance = floatval($staff_pay->food_allowance ?? 0);
						$allowance = floatval($staff_pay->allowance ?? 0);
						$fat_allowance = floatval($staff_pay->fat_allowance ?? 0);
						$accomodation_allowance = floatval($staff_pay->accomodation_allowance ?? 0);
						$mewa = floatval($staff_pay->mewa ?? 0);

						// GOSI fields - read from database (can be different from basic_pay/accommodation)
						$gosi_basic = floatval($staff_pay->gosi_basic ?? 0);
						$gosi_housing = floatval($staff_pay->gosi_housing_allowance ?? 0);

						$data_object_kpi[$staff_key]['gosi_basic_salary'] = number_format($gosi_basic, 2);
						$data_object_kpi[$staff_key]['gosi_housing_allowance'] = number_format($gosi_housing, 2);

						// ✅ Calculate gosi_other_allowance including MEWA and custom allowances
						$gosi_other = $this->hr_payroll_model->calculate_gosi_other_allowance($staff_value['staffid'], $current_month);
						$data_object_kpi[$staff_key]['gosi_other_allowance'] = number_format($gosi_other, 2);

						// ✅ gosi_deduction is now an open field (not automatically MEWA)
						$data_object_kpi[$staff_key]['gosi_deduction'] = '0.00';  // Default empty

						// Basic salary and OT
						$data_object_kpi[$staff_key]['basic'] = number_format($basic_pay, 2);
						$data_object_kpi[$staff_key]['ot_hours'] = '0.00';
						$data_object_kpi[$staff_key]['ot_rate'] = number_format($overtime_rate, 2);
						$data_object_kpi[$staff_key]['ot_amount'] = '0.00';

						// ✅ Calculate total allowance including MEWA and custom allowances
						$total_allowance = $this->hr_payroll_model->calculate_total_allowance($staff_value['staffid'], $current_month);
						$data_object_kpi[$staff_key]['allowance'] = number_format($total_allowance, 2);

						// Additions from tblhrp_adjustments (separate column)
						$data_object_kpi[$staff_key]['additions'] = number_format($hrp_additions, 2);

						// Deductions from tblhrp_adjustments
						$data_object_kpi[$staff_key]['deduction'] = number_format($hrp_deductions, 2);

						// ✅ Calculate GOSI total amount (GOSI Basic + GOSI Housing + Other - Deduction)
						// Note: gosi_deduction is now 0.00 by default (not MEWA)
						$total_amount = $gosi_basic + $gosi_housing + $gosi_other - 0;  // gosi_deduction = 0

						// Calculate full salary: Basic + OT Amount + Allowance + Additions - Deductions
						$full_salary = $basic_pay + 0 + $total_allowance + $hrp_additions - $hrp_deductions;  // OT is 0 for new records

						// Calculate balance: Full Salary - Total Amount
						$balance = $full_salary - $total_amount;

						$data_object_kpi[$staff_key]['total_amount'] = number_format($total_amount, 2);
						$data_object_kpi[$staff_key]['full_salary'] = number_format($full_salary, 2);
						$data_object_kpi[$staff_key]['balance'] = number_format($balance, 2);
					} else {
						// Fallback: no pay data found
						$data_object_kpi[$staff_key]['gosi_basic_salary'] = '';
						$data_object_kpi[$staff_key]['gosi_housing_allowance'] = '';
						$data_object_kpi[$staff_key]['gosi_other_allowance'] = '';
						$data_object_kpi[$staff_key]['gosi_deduction'] = '';
						$data_object_kpi[$staff_key]['total_amount'] = '';
						$data_object_kpi[$staff_key]['balance'] = '';
						$data_object_kpi[$staff_key]['full_salary'] = '';
						$data_object_kpi[$staff_key]['basic'] = '';
						$data_object_kpi[$staff_key]['ot_hours'] = '0.00';
						$data_object_kpi[$staff_key]['ot_rate'] = '0.00';
						$data_object_kpi[$staff_key]['ot_amount'] = '0.00';
						$data_object_kpi[$staff_key]['allowance'] = '';
						$data_object_kpi[$staff_key]['additions'] = number_format($hrp_additions, 2);
						$data_object_kpi[$staff_key]['deduction'] = '';
					}

					$data_object_kpi[$staff_key]['mention'] = '';
					$data_object_kpi[$staff_key]['payroll_month'] = date('F Y', strtotime($current_month));
					$data_object_kpi[$staff_key]['comment_1'] = '';
					$data_object_kpi[$staff_key]['comment_2'] = '';
					$data_object_kpi[$staff_key]['comment_3'] = '';

				}
			}

			// ALWAYS: Calculate derived fields after setting inputs (for both DB and default cases)
			$basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['basic'] ?? '0'));
			$g_basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_basic_salary'] ?? '0'));
			$g_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_housing_allowance'] ?? '0'));
			$g_o_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_other_allowance'] ?? '0'));
			$g_d_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_deduction'] ?? '0'));
			$allow_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['allowance'] ?? '0'));
			$additions_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['additions'] ?? '0'));
			$ded_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['deduction'] ?? '0'));
			$ot_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_hours'] ?? '0'));
			$ot_r_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate'] ?? '0'));
			$ot_a_f = $ot_h_f * $ot_r_f;
			$total_a_f = $g_basic_f + $g_h_f + $g_o_f - $g_d_f;

			// Calculate full salary: Basic + OT Amount + Allowance + Additions - Deductions
			$full_s_f = $basic_f + $ot_a_f + $allow_f + $additions_f - $ded_f;
			$bal_f = $full_s_f - $total_a_f;

			$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_a_f, 2);
			$data_object_kpi[$staff_key]['total_amount'] = number_format($total_a_f, 2);
			$data_object_kpi[$staff_key]['full_salary'] = number_format($full_s_f, 2);
			$data_object_kpi[$staff_key]['balance'] = number_format($bal_f, 2);

			if ($has_db_record) {

				$data_object_kpi[$staff_key]['income_rebate_code'] = $employees_value[$db_key]['income_rebate_code'];
				$data_object_kpi[$staff_key]['income_tax_rate'] = $employees_value[$db_key]['income_tax_rate'];

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				if (isset($employees_value[$db_key]['contract_value'])) {

					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $employees_value[$db_key]['contract_value']);
				} else {
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);
				}

				$data_object_kpi[$staff_key]['probationary_effective'] = $employees_value[$db_key]['probationary_effective'];
				$data_object_kpi[$staff_key]['probationary_expiration'] = $employees_value[$db_key]['probationary_expiration'];
				$data_object_kpi[$staff_key]['primary_effective'] = $employees_value[$db_key]['primary_effective'];
				$data_object_kpi[$staff_key]['primary_expiration'] = $employees_value[$db_key]['primary_expiration'];

				$data_object_kpi[$staff_key]['id'] = $employees_value[$db_key]['id'];


			} else {
				$data_object_kpi[$staff_key]['income_rebate_code'] = 'A';
				$data_object_kpi[$staff_key]['income_tax_rate'] = 'A';

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);

				$data_object_kpi[$staff_key]['probationary_effective'] = '';
				$data_object_kpi[$staff_key]['probationary_expiration'] = '';
				$data_object_kpi[$staff_key]['primary_effective'] = '';
				$data_object_kpi[$staff_key]['primary_expiration'] = '';

				$data_object_kpi[$staff_key]['id'] = 0;

			}

			$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
		}
		//check is add new or update data
		if (count($employees_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($format_employees_value['column_format']);
		$data['col_header'] = json_encode($format_employees_value['header']);

		$this->load->view('employees/employees_manage', $data);
	}

	/**
	 * employees filter
	 * @return [type]
	 */
	// public function employees_filter() {
	// 	$this->load->model('departments_model');
	// 	$data = $this->input->post();

	// 	$company_filter = isset($data['company']) ? $data['company'] : ''; // either "mahiroon" or "mohtarifeen"

	// 	$rel_type = hrp_get_hr_profile_status();

	// 	$months_filter = $data['month'];
	// 	$department = isset($data['department']) ? $data['department'] : '';
	// 	$staff = '';
	// 	if (isset($data['staff'])) {
	// 		$staff = $data['staff'];
	// 	}
	// 	$role_attendance = '';
	// 	if (isset($data['role_attendance'])) {
	// 		$role_attendance = $data['role_attendance'];
	// 	}

	// 	$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

	// 	//get current month
	// 	$month_filter = date('Y-m', strtotime($data['month']));
	// 	$month_filter_db = date('Y-m-d', strtotime($data['month'])); 

	// 	$employees_data = $this->hr_payroll_model->get_employees_data($month_filter_db, $rel_type);
	// 	$employees_value = [];
	// 	foreach ($employees_data as $key => $value) {
	// 		// Normalize DB month
	// 		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value['month'])) {
	// 			// Case like 2025-08-01 → convert to Y-m
	// 			$normalized_month = date('Y-m', strtotime($value['month']));
	// 		} elseif (preg_match('/^\d{2}$/', $value['month'])) {
	// 			// Case like 07 → assume current year + month
	// 			$normalized_month = date('Y') . '-' . str_pad($value['month'], 2, '0', STR_PAD_LEFT);
	// 		} else {
	// 			// Fallback, store as-is
	// 			$normalized_month = $value['month'];
	// 		}

	// 		$employees_value[$value['staff_id'] . '_' . $normalized_month] = $value;
	// 	}

	// 	//get employee data for the first
	// 	$format_employees_value = $this->hr_payroll_model->get_format_employees_data($rel_type);

	// 	// data return
	// 	$data_object_kpi = [];
	// 	$index_data_object = 0;
	// 	if ($newquerystring != '') {

	// 		//load deparment by manager
	// 		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
	// 			//View own
	// 			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
	// 		} else {
	// 			//admin or view global
	// 			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
	// 		}

	// 		// Exclude staff with staffid 1 and 3
	// 		$staffs = array_values(array_filter($staffs, function ($staff) {
	// 			return !in_array((int)$staff['staffid'], [1, 3]);
	// 		}));

	// 		/* ✅ Filter by company_id */
	// 		if ($company_filter == 'mohtarifeen') {
	// 			// Show only Mohtarifeen (company_id = 2)
	// 			$staffs = array_values(array_filter($staffs, function ($staff) {
	// 				return isset($staff['companytype_id']) && (int)$staff['companytype_id'] === 2;
	// 			}));
	// 		} elseif ($company_filter == 'mahiroon') {
	// 			// Show Mahiroon (all except companytype_id = 2)
	// 			$staffs = array_values(array_filter($staffs, function ($staff) {
	// 				return isset($staff['companytype_id']) && (int)$staff['companytype_id'] !== 2;
	// 			}));
	// 		}

	// 		$data_object_kpi = [];

	// 		foreach ($staffs as $staff_key => $staff_value) {
	// 			/*check value from database*/
	// 			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

	// 			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
	// 			if ($staff_i) {

	// 				if ($rel_type == 'hr_records') {
	// 					$data_object_kpi[$staff_key]['employee_number'] = $staff_i->staff_identifi;
	// 				} else {
	// 					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
	// 				}

	// 				$data_object_kpi[$staff_key]['employee_name'] = $staff_i->name;

	// 				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

	// 				$list_department = '';
	// 				if (count($arr_department) > 0) {

	// 					foreach ($arr_department as $key => $department) {
	// 						$department_value = $this->departments_model->get($department);

	// 						if ($department_value) {
	// 							if (new_strlen($list_department) != 0) {
	// 								$list_department .= ', ' . $department_value->name;
	// 							} else {
	// 								$list_department .= $department_value->name;
	// 							}
	// 						}
	// 					}
	// 				}

	// 				$data_object_kpi[$staff_key]['department_name'] = $list_department;

	// 			} else {
	// 				$data_object_kpi[$staff_key]['employee_number'] = '';
	// 				$data_object_kpi[$staff_key]['employee_name'] = '';
	// 				$data_object_kpi[$staff_key]['department_name'] = '';
	// 			}

	// 			if ($rel_type == 'hr_records') {
	// 				$data_object_kpi[$staff_key]['job_title'] = $staff_value['position_name'];
	// 				$data_object_kpi[$staff_key]['income_tax_number'] = $staff_value['Personal_tax_code'];
	// 				$data_object_kpi[$staff_key]['residential_address'] = $staff_value['resident'];
	// 				$data_object_kpi[$staff_key]['bank_name'] = $staff_value['issue_bank'];
	// 				$data_object_kpi[$staff_key]['account_number'] = $staff_value['account_number'];
	// 				$data_object_kpi[$staff_key]['epf_no'] = $staff_value['epf_no'];
	// 				$data_object_kpi[$staff_key]['social_security_no'] = $staff_value['social_security_no'];
	// 			} else {
	// 				$db_key = $staff_value['staffid'] . '_' . $month_filter;
	// 				$has_db_record = isset($employees_value[$db_key]);

	// 				if ($has_db_record) {
	// 					$data_object_kpi[$staff_key]['employee_id_iqama'] = !empty($employees_value[$db_key]['employee_id_iqama'])
	// 																	? $employees_value[$db_key]['employee_id_iqama']
	// 																	: $staff_value['iqama_number'];
	// 					$data_object_kpi[$staff_key]['employee_account_no_iban'] = !empty($employees_value[$db_key]['employee_account_no_iban'])
	// 																	? $employees_value[$db_key]['employee_account_no_iban']
	// 																	: $staff_value['bank_iban_number'];
	// 					$data_object_kpi[$staff_key]['bank_code'] = !empty($employees_value[$db_key]['bank_code']) ? $employees_value[$db_key]['bank_code'] : $staff_value['bank_swift_code'];
	// 					$data_object_kpi[$staff_key]['gosi_basic_salary'] = $employees_value[$db_key]['gosi_basic_salary'];
	// 					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = $employees_value[$db_key]['gosi_housing_allowance'];
	// 					$data_object_kpi[$staff_key]['gosi_other_allowance'] = $employees_value[$db_key]['gosi_other_allowance'];
	// 					$data_object_kpi[$staff_key]['gosi_deduction'] = $employees_value[$db_key]['gosi_deduction'];
	// 					$data_object_kpi[$staff_key]['total_amount'] = $employees_value[$db_key]['total_amount'];
	// 					$data_object_kpi[$staff_key]['balance'] = $employees_value[$db_key]['balance'];
	// 					$data_object_kpi[$staff_key]['full_salary'] = $employees_value[$db_key]['full_salary'];
	// 					// $data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['basic'];
	// 					$data_object_kpi[$staff_key]['basic'] = $employees_value[$db_key]['basic'];
	// 					// $data_object_kpi[$staff_key]['ot_hours'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_hours'];
	// 					$ot_hours_val = floatval($employees_value[$db_key]['ot_hours'] ?? 0);
	// 					$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
	// 					// $data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_rate'];
	// 					$data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$db_key]['ot_rate'] ?? (isset($staff_i->ot) ? (string) $staff_i->ot : '');
	// 					// $data_object_kpi[$staff_key]['ot_amount'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_amount'];
	// 					$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
	// 					$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 					$data_object_kpi[$staff_key]['allowance'] = $employees_value[$db_key]['allowance'];
	// 					$data_object_kpi[$staff_key]['deduction'] = $employees_value[$db_key]['deduction'];
	// 					$data_object_kpi[$staff_key]['mention'] = $employees_value[$db_key]['mention'];

	// 				} else {
	// 					$data_object_kpi[$staff_key]['employee_id_iqama'] = $staff_value['iqama_number'] ?? '';
	// 					$data_object_kpi[$staff_key]['employee_account_no_iban'] = $staff_value['bank_iban_number'] ?? '';
	// 					$data_object_kpi[$staff_key]['bank_code'] = $staff_value['bank_swift_code'] ?? '';
	// 					$data_object_kpi[$staff_key]['gosi_basic_salary'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_other_allowance'] = '';
	// 					$data_object_kpi[$staff_key]['gosi_deduction'] = '';
	// 					$data_object_kpi[$staff_key]['total_amount'] = '';
	// 					$data_object_kpi[$staff_key]['balance'] = '';
	// 					$data_object_kpi[$staff_key]['full_salary'] = '';
	// 					// $data_object_kpi[$staff_key]['basic'] = '';
	// 					$data_object_kpi[$staff_key]['basic'] = isset($staff_i->basics) ? (string) $staff_i->basics : '';
	// 					// $data_object_kpi[$staff_key]['ot_hours'] = '';
	// 					$ot_hours_val = 0;
	// 					$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
	// 					// $data_object_kpi[$staff_key]['ot_rate'] = '';
	// 					// $data_object_kpi[$staff_key]['ot_amount'] = '';
	// 					$data_object_kpi[$staff_key]['ot_rate'] = isset($staff_i->ot) ? (string) $staff_i->ot : '0';

	// 					// NEW: Calculate ot_amount
	// 					$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
	// 					$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
	// 					$data_object_kpi[$staff_key]['allowance'] = '';
	// 					$data_object_kpi[$staff_key]['deduction'] = '';
	// 					$data_object_kpi[$staff_key]['mention'] = '';

	// 					// NEW: Auto-populate GOSI and allowance/deduction fields from staff table
	// 					$basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['basic'] ?? '0'));
	// 					$g_h_f = isset($staff_i->fatallowance) ? floatval(str_replace(',', '', $staff_i->fatallowance)) : 0;
	// 					$g_o_f = isset($staff_i->otherallowance) ? floatval(str_replace(',', '', $staff_i->otherallowance)) : 0;
	// 					$g_d_f = 0;
	// 					$allow_f = isset($staff_i->siteallowance) ? floatval(str_replace(',', '', $staff_i->siteallowance)) : 0;
	// 					$ded_f = isset($staff_i->advance) ? floatval(str_replace(',', '', $staff_i->advance)) : 0;

	// 					$data_object_kpi[$staff_key]['gosi_basic_salary'] = $data_object_kpi[$staff_key]['basic']; // Assume GOSI basic = staff basic
	// 					$data_object_kpi[$staff_key]['gosi_housing_allowance'] = number_format($g_h_f, 2);
	// 					$data_object_kpi[$staff_key]['gosi_other_allowance'] = number_format($g_o_f, 2);
	// 					$data_object_kpi[$staff_key]['gosi_deduction'] = number_format($g_d_f, 2);
	// 					$data_object_kpi[$staff_key]['allowance'] = number_format($allow_f, 2);
	// 					$data_object_kpi[$staff_key]['deduction'] = number_format($ded_f, 2);
	// 				}
	// 			}

	// 			// ALWAYS: Calculate derived fields after setting inputs (for both DB and default cases)
	// 			$basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['basic'] ?? '0'));
	// 			$g_basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_basic_salary'] ?? '0'));
	// 			$g_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_housing_allowance'] ?? '0'));
	// 			$g_o_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_other_allowance'] ?? '0'));
	// 			$g_d_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_deduction'] ?? '0'));
	// 			$allow_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['allowance'] ?? '0'));
	// 			$ded_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['deduction'] ?? '0'));
	// 			$ot_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_hours'] ?? '0'));
	// 			$ot_r_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate'] ?? '0'));
	// 			$ot_a_f = $ot_h_f * $ot_r_f;
	// 			$total_a_f = $g_basic_f + $g_h_f + $g_o_f - $g_d_f;
	// 			$full_s_f = $basic_f + $ot_a_f + $allow_f - $ded_f;
	// 			$bal_f = $full_s_f - $basic_f;

	// 			// NEW: Fetch net adjustment and apply to full_salary (deductions reduce, additions increase)
	// 			$net_adjustment = $this->hr_payroll_model->get_net_adjustment($staff_value['staffid'], $month_filter_db);
	// 			$full_s_f += $net_adjustment; // Net: positive for additions, negative for deductions
	// 			$bal_f += $net_adjustment;

	// 			$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_a_f, 2);
	// 			$data_object_kpi[$staff_key]['total_amount'] = number_format($total_a_f, 2);
	// 			$data_object_kpi[$staff_key]['full_salary'] = number_format($full_s_f, 2);
	// 			$data_object_kpi[$staff_key]['balance'] = number_format($bal_f, 2);

	// 			if ($has_db_record) {

	// 				$data_object_kpi[$staff_key]['income_rebate_code'] = $employees_value[$db_key]['income_rebate_code'];
	// 				$data_object_kpi[$staff_key]['income_tax_rate'] = $employees_value[$db_key]['income_tax_rate'];

	// 				$data_object_kpi[$staff_key]['probationary_effective'] = $employees_value[$db_key]['probationary_effective'];
	// 				$data_object_kpi[$staff_key]['probationary_expiration'] = $employees_value[$db_key]['probationary_expiration'];
	// 				$data_object_kpi[$staff_key]['primary_effective'] = $employees_value[$db_key]['primary_effective'];
	// 				$data_object_kpi[$staff_key]['primary_expiration'] = $employees_value[$db_key]['primary_expiration'];

	// 				// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 				if (isset($employees_value[$db_key]['contract_value'])) {

	// 					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $employees_value[$db_key]['contract_value']);
	// 				} else {
	// 					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);
	// 				}

	// 				$data_object_kpi[$staff_key]['id'] = $employees_value[$db_key]['id'];


	// 			} else {
	// 				$data_object_kpi[$staff_key]['income_rebate_code'] = 'A';
	// 				$data_object_kpi[$staff_key]['income_tax_rate'] = 'A';

	// 				// array merge: staff information + earning list (probationary contract) + earning list (formal)
	// 				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);

	// 				$data_object_kpi[$staff_key]['id'] = 0;

	// 			}

	// 			$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
	// 		}

	// 	}

	// 	//check is add new or update data
	// 	if (count($employees_value) > 0) {
	// 		$button_name = _l('hrp_update');
	// 	} else {
	// 		$button_name = _l('submit');
	// 	}

	// 	echo json_encode([
	// 		'data_object' => $data_object_kpi,
	// 		'button_name' => $button_name,
	// 	]);
	// 	die;
	// }

	public function employees_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$company_filter = isset($data['company']) ? $data['company'] : '';
		$payroll_id = isset($data['payroll_id']) ? $data['payroll_id'] : null;

		$rel_type = hrp_get_hr_profile_status();

		$months_filter = $data['month'];
		$department = isset($data['department']) ? $data['department'] : '';
		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$role_attendance = '';
		if (isset($data['role_attendance'])) {
			$role_attendance = $data['role_attendance'];
		}

		$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

		//get current month
		$month_filter = date('Y-m', strtotime($data['month']));
		$month_filter_db = date('Y-m-d', strtotime($data['month']));

		// Load employees data - if payroll_id provided, only load that payroll's employees
		if ($payroll_id) {
			$employees_data = $this->hr_payroll_model->get_payroll_employees($payroll_id);
		} else {
			$employees_data = $this->hr_payroll_model->get_employees_data($month_filter_db, $rel_type);
		}
		$employees_value = [];
		foreach ($employees_data as $key => $value) {
			// Normalize DB month
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value['month'])) {
				// Case like 2025-08-01 → convert to Y-m
				$normalized_month = date('Y-m', strtotime($value['month']));
			} elseif (preg_match('/^\d{2}$/', $value['month'])) {
				// Case like 07 → assume current year + month
				$normalized_month = date('Y') . '-' . str_pad($value['month'], 2, '0', STR_PAD_LEFT);
			} else {
				// Fallback, store as-is
				$normalized_month = $value['month'];
			}

			$employees_value[$value['staff_id'] . '_' . $normalized_month] = $value;
		}

		//get employee data for the first
		$format_employees_value = $this->hr_payroll_model->get_format_employees_data($rel_type);

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load deparment by manager
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			// Exclude staff with staffid 1 and 3
			$staffs = array_values(array_filter($staffs, function ($staff) {
				return !in_array((int)$staff['staffid'], [1, 3]);
			}));

			/* ✅ Filter by company_id */
			if ($company_filter == 'mohtarifeen') {
				// Show only Mohtarifeen (company_id = 2)
				$staffs = array_values(array_filter($staffs, function ($staff) {
					return isset($staff['companytype_id']) && (int)$staff['companytype_id'] === 2;
				}));
			} elseif ($company_filter == 'mahiroon') {
				// Show Mahiroon (all except companytype_id = 2)
				$staffs = array_values(array_filter($staffs, function ($staff) {
					return isset($staff['companytype_id']) && (int)$staff['companytype_id'] !== 2;
				}));
			}

			/* ✅ CRITICAL: Filter by payroll_id if in payroll mode */
			if ($payroll_id && !empty($employees_data)) {
				$payroll_staff_ids = array_column($employees_data, 'staff_id');
				$staffs = array_values(array_filter($staffs, function ($staff) use ($payroll_staff_ids) {
					return in_array($staff['staffid'], $payroll_staff_ids);
				}));
			}

			$data_object_kpi = [];

			foreach ($staffs as $staff_key => $staff_value) {
				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
				if ($staff_i) {

					if ($rel_type == 'hr_records') {
						$data_object_kpi[$staff_key]['employee_number'] = $staff_i->staff_identifi;
					} else {
						$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
					}

					$data_object_kpi[$staff_key]['employee_name'] = $staff_i->name;

					$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

					$list_department = '';
					if (count($arr_department) > 0) {

						foreach ($arr_department as $key => $department) {
							$department_value = $this->departments_model->get($department);

							if ($department_value) {
								if (new_strlen($list_department) != 0) {
									$list_department .= ', ' . $department_value->name;
								} else {
									$list_department .= $department_value->name;
								}
							}
						}
					}

					$data_object_kpi[$staff_key]['department_name'] = $list_department;

				} else {
					$data_object_kpi[$staff_key]['employee_number'] = '';
					$data_object_kpi[$staff_key]['employee_name'] = '';
					$data_object_kpi[$staff_key]['department_name'] = '';
				}

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['job_title'] = $staff_value['position_name'];
					$data_object_kpi[$staff_key]['income_tax_number'] = $staff_value['Personal_tax_code'];
					$data_object_kpi[$staff_key]['residential_address'] = $staff_value['resident'];
					$data_object_kpi[$staff_key]['bank_name'] = $staff_value['issue_bank'];
					$data_object_kpi[$staff_key]['account_number'] = $staff_value['account_number'];
					$data_object_kpi[$staff_key]['epf_no'] = $staff_value['epf_no'];
					$data_object_kpi[$staff_key]['social_security_no'] = $staff_value['social_security_no'];
				} else {
					$db_key = $staff_value['staffid'] . '_' . $month_filter;
					$has_db_record = isset($employees_value[$db_key]);

					if ($has_db_record) {
						$data_object_kpi[$staff_key]['employee_id_iqama'] = !empty($employees_value[$db_key]['employee_id_iqama'])
																		? $employees_value[$db_key]['employee_id_iqama']
																		: $staff_value['iqama_number'];
						$data_object_kpi[$staff_key]['employee_account_no_iban'] = !empty($employees_value[$db_key]['employee_account_no_iban'])
																		? $employees_value[$db_key]['employee_account_no_iban']
																		: $staff_value['bank_iban_number'];
						$data_object_kpi[$staff_key]['bank_code'] = !empty($employees_value[$db_key]['bank_code']) ? $employees_value[$db_key]['bank_code'] : $staff_value['bank_swift_code'];
						$data_object_kpi[$staff_key]['gosi_basic_salary'] = $employees_value[$db_key]['gosi_basic_salary'];
						$data_object_kpi[$staff_key]['gosi_housing_allowance'] = $employees_value[$db_key]['gosi_housing_allowance'];
						$data_object_kpi[$staff_key]['gosi_other_allowance'] = $employees_value[$db_key]['gosi_other_allowance'];
						$data_object_kpi[$staff_key]['gosi_deduction'] = $employees_value[$db_key]['gosi_deduction'];
						$data_object_kpi[$staff_key]['total_amount'] = $employees_value[$db_key]['total_amount'];
						$data_object_kpi[$staff_key]['balance'] = $employees_value[$db_key]['balance'];
						$data_object_kpi[$staff_key]['full_salary'] = $employees_value[$db_key]['full_salary'];
						// $data_object_kpi[$staff_key]['basic'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['basic'];
						$data_object_kpi[$staff_key]['basic'] = $employees_value[$db_key]['basic'];
						// $data_object_kpi[$staff_key]['ot_hours'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_hours'];
						$ot_hours_val = floatval($employees_value[$db_key]['ot_hours'] ?? 0);
						$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
						// $data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_rate'];
						$data_object_kpi[$staff_key]['ot_rate'] = $employees_value[$db_key]['ot_rate'] ?? (isset($staff_i->ot) ? (string) $staff_i->ot : '');
						// $data_object_kpi[$staff_key]['ot_amount'] = $employees_value[$staff_value['staffid'] . '_' . $month_filter]['ot_amount'];
						$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
						$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
						$data_object_kpi[$staff_key]['allowance'] = $employees_value[$db_key]['allowance'];
						$data_object_kpi[$staff_key]['deduction'] = $employees_value[$db_key]['deduction'];
						$data_object_kpi[$staff_key]['mention'] = $employees_value[$db_key]['mention'];

					} else {
						$data_object_kpi[$staff_key]['employee_id_iqama'] = $staff_value['iqama_number'] ?? '';
						$data_object_kpi[$staff_key]['employee_account_no_iban'] = $staff_value['bank_iban_number'] ?? '';
						$data_object_kpi[$staff_key]['bank_code'] = $staff_value['bank_swift_code'] ?? '';
						$data_object_kpi[$staff_key]['gosi_basic_salary'] = '';
						$data_object_kpi[$staff_key]['gosi_housing_allowance'] = '';
						$data_object_kpi[$staff_key]['gosi_other_allowance'] = '';
						$data_object_kpi[$staff_key]['gosi_deduction'] = '';
						$data_object_kpi[$staff_key]['total_amount'] = '';
						$data_object_kpi[$staff_key]['balance'] = '';
						$data_object_kpi[$staff_key]['full_salary'] = '';
						// $data_object_kpi[$staff_key]['basic'] = '';
						$data_object_kpi[$staff_key]['basic'] = isset($staff_i->basics) ? (string) $staff_i->basics : '';
						// $data_object_kpi[$staff_key]['ot_hours'] = '';
						$ot_hours_val = 0;
						$data_object_kpi[$staff_key]['ot_hours'] = number_format($ot_hours_val, 2);
						// $data_object_kpi[$staff_key]['ot_rate'] = '';
						// $data_object_kpi[$staff_key]['ot_amount'] = '';
						$data_object_kpi[$staff_key]['ot_rate'] = isset($staff_i->ot) ? (string) $staff_i->ot : '0';

						// NEW: Calculate ot_amount
						$ot_amount_val = $ot_hours_val * floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate']));
						$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_amount_val, 2);
						$data_object_kpi[$staff_key]['allowance'] = '';
						$data_object_kpi[$staff_key]['deduction'] = '';
						$data_object_kpi[$staff_key]['mention'] = '';

						// NEW: Auto-populate GOSI and allowance/deduction fields from staff table
						$basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['basic'] ?? '0'));
						$g_h_f = isset($staff_i->fatallowance) ? floatval(str_replace(',', '', $staff_i->fatallowance)) : 0;
						$g_o_f = isset($staff_i->otherallowance) ? floatval(str_replace(',', '', $staff_i->otherallowance)) : 0;
						$g_d_f = 0;
						$allow_f = isset($staff_i->siteallowance) ? floatval(str_replace(',', '', $staff_i->siteallowance)) : 0;
						$ded_f = isset($staff_i->advance) ? floatval(str_replace(',', '', $staff_i->advance)) : 0;

						$data_object_kpi[$staff_key]['gosi_basic_salary'] = $data_object_kpi[$staff_key]['basic']; // Assume GOSI basic = staff basic
						$data_object_kpi[$staff_key]['gosi_housing_allowance'] = number_format($g_h_f, 2);
						$data_object_kpi[$staff_key]['gosi_other_allowance'] = number_format($g_o_f, 2);
						$data_object_kpi[$staff_key]['gosi_deduction'] = number_format($g_d_f, 2);
						$data_object_kpi[$staff_key]['allowance'] = number_format($allow_f, 2);
						$data_object_kpi[$staff_key]['deduction'] = number_format($ded_f, 2);
					}
				}

				// ALWAYS: Calculate derived fields after setting inputs (for both DB and default cases)
				$basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['basic'] ?? '0'));
				$g_basic_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_basic_salary'] ?? '0'));
				$g_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_housing_allowance'] ?? '0'));
				$g_o_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_other_allowance'] ?? '0'));
				$g_d_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['gosi_deduction'] ?? '0'));
				$allow_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['allowance'] ?? '0'));
				$ded_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['deduction'] ?? '0'));
				$ot_h_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_hours'] ?? '0'));
				$ot_r_f = floatval(str_replace(',', '', $data_object_kpi[$staff_key]['ot_rate'] ?? '0'));
				$ot_a_f = $ot_h_f * $ot_r_f;
				$total_a_f = $g_basic_f + $g_h_f + $g_o_f - $g_d_f;
				$full_s_f = $basic_f + $ot_a_f + $allow_f - $ded_f;
				$bal_f = $full_s_f - $basic_f;

				// NEW: Fetch net adjustment and apply to full_salary (deductions reduce, additions increase)
				$net_adjustment = $this->hr_payroll_model->get_net_adjustment($staff_value['staffid'], $month_filter_db);
				$full_s_f += $net_adjustment; // Net: positive for additions, negative for deductions
				$bal_f += $net_adjustment;

				$data_object_kpi[$staff_key]['ot_amount'] = number_format($ot_a_f, 2);
				$data_object_kpi[$staff_key]['total_amount'] = number_format($total_a_f, 2);
				$data_object_kpi[$staff_key]['full_salary'] = number_format($full_s_f, 2);
				$data_object_kpi[$staff_key]['balance'] = number_format($bal_f, 2);

				if ($has_db_record) {

					$data_object_kpi[$staff_key]['income_rebate_code'] = $employees_value[$db_key]['income_rebate_code'];
					$data_object_kpi[$staff_key]['income_tax_rate'] = $employees_value[$db_key]['income_tax_rate'];

					$data_object_kpi[$staff_key]['probationary_effective'] = $employees_value[$db_key]['probationary_effective'];
					$data_object_kpi[$staff_key]['probationary_expiration'] = $employees_value[$db_key]['probationary_expiration'];
					$data_object_kpi[$staff_key]['primary_effective'] = $employees_value[$db_key]['primary_effective'];
					$data_object_kpi[$staff_key]['primary_expiration'] = $employees_value[$db_key]['primary_expiration'];

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					if (isset($employees_value[$db_key]['contract_value'])) {

						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $employees_value[$db_key]['contract_value']);
					} else {
						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);
					}

					$data_object_kpi[$staff_key]['id'] = $employees_value[$db_key]['id'];


				} else {
					$data_object_kpi[$staff_key]['income_rebate_code'] = 'A';
					$data_object_kpi[$staff_key]['income_tax_rate'] = 'A';

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_employees_value['probationary'], $format_employees_value['formal']);

					$data_object_kpi[$staff_key]['id'] = 0;

				}

				$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
			}

		}

		//check is add new or update data
		if (count($employees_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * add manage employees
	 */
	// public function add_manage_employees() {
	// 	if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
	// 		access_denied('hrp_employee');
	// 	}

	// 	if ($this->input->post()) {
	// 		$data = $this->input->post();
	// 		if ($data['hrp_employees_rel_type'] == 'synchronization') {
	// 			//synchronization
	// 			$success = $this->hr_payroll_model->employees_synchronization($data);
	// 		} elseif ($data['hrp_employees_rel_type'] == 'update') {
	// 			// update data
	// 			$success = $this->hr_payroll_model->employees_update($data);
	// 		} else {
	// 			$success = false;
	// 		}

	// 		if ($success) {
	// 			set_alert('success', _l('updated_successfully'));
	// 		} else {
	// 			set_alert('success', _l('updated_successfully'));
	// 		}

	// 		redirect(admin_url('hr_payroll/manage_employees'));
	// 	}

	// }

	public function add_manage_employees() {
		if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			echo json_encode([
				'success' => false,
				'message' => 'Access denied',
				'csrf_hash' => $this->security->get_csrf_hash()
			]);
			return;
		}

		if ($this->input->post()) {
			try {
				$data = $this->input->post();

				log_message('debug', 'add_manage_employees POST data: ' . print_r($data, true));

				if (!isset($data['hrp_employees_rel_type'])) {
					throw new Exception('Missing hrp_employees_rel_type');
				}

				if ($data['hrp_employees_rel_type'] == 'synchronization') {
					$success = $this->hr_payroll_model->employees_synchronization($data);
				} elseif ($data['hrp_employees_rel_type'] == 'update') {
					$success = $this->hr_payroll_model->employees_update($data);
					// ✅ Treat as success even if update didn't affect any rows
					if (!empty($data['hrp_employees_value'])) {
						$success = true;
					}
				} else {
					$success = false;
				}

				echo json_encode([
					'success' => $success,
					'type' => $data['hrp_employees_rel_type'],
					'message' => $success ? _l('updated_successfully') : _l('something_went_wrong'),
					'csrf_hash' => $this->security->get_csrf_hash()
				]);

			} catch (Exception $e) {
				log_message('error', 'add_manage_employees error: ' . $e->getMessage());
				echo json_encode([
					'success' => false,
					'message' => 'Error: ' . $e->getMessage(),
					'csrf_hash' => $this->security->get_csrf_hash()
				]);
			}
			return;
		}

		echo json_encode([
			'success' => false,
			'message' => 'No data posted',
			'csrf_hash' => $this->security->get_csrf_hash()
		]);
	}

	/**
	 * Export payroll data to bank transfer file format
	 * @return file download (Excel)
	 */
	public function export_payroll_bank_file() {
		if (!has_permission('hrp_employee', '', 'view') && !is_admin()) {
			echo json_encode(['success' => false, 'message' => 'Access denied']);
			return;
		}

		if ($this->input->post()) {
			$staff_ids = $this->input->post('staff_ids');
			$month = $this->input->post('month');
			$company = $this->input->post('company');

			if (empty($staff_ids) || !is_array($staff_ids)) {
				echo json_encode(['success' => false, 'message' => 'No employees selected']);
				return;
			}

			// Load WPS settings model
			$this->load->model('wps_settings_model');

			// Get company ID from filter (Mahiroon=1, Mohtarifeen=2)
			$company_id = null;
			if ($company == 'mahiroon') {
				$company_id = 1;
			} elseif ($company == 'mohtarifeen') {
				$company_id = 2;
			}

			// Get WPS settings for this company
			$wps_settings = $this->wps_settings_model->get($company_id);

			// Get export data from model
			$export_data = $this->hr_payroll_model->get_payroll_export_data($staff_ids, $month, $company);

			if (empty($export_data)) {
				echo json_encode(['success' => false, 'message' => 'No data found for selected employees']);
				return;
			}

			// Load PhpSpreadsheet library
			require_once(APPPATH . 'vendor/autoload.php');

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			// === ROW 1: WPS Header Row ===
			$sheet->setCellValue('A1', 'Type');
			$sheet->setCellValue('B1', 'Customer Name');
			$sheet->setCellValue('C1', 'Agreement Code');
			$sheet->setCellValue('D1', 'Funding Account');
			$sheet->setCellValue('E1', 'Branch No');
			$sheet->setCellValue('F1', 'Credit Date (DDMMYYYY)');
			$sheet->setCellValue('G1', 'Mins of Lab Establish Id');
			$sheet->setCellValue('H1', 'ECR ID');
			$sheet->setCellValue('I1', 'Bank Code');
			$sheet->setCellValue('J1', 'Currency');
			$sheet->setCellValue('K1', 'Batch');
			$sheet->setCellValue('L1', 'File Reference');

			// === ROW 2: WPS Settings Values ===
			$credit_date = '';
			if ($wps_settings) {
				$credit_date_format = $wps_settings->credit_date_format ?? 'DDMMYYYY';
				if ($credit_date_format == 'DDMMYYYY') {
					$credit_date = date('dmY');
				} elseif ($credit_date_format == 'DD/MM/YYYY') {
					$credit_date = date('d/m/Y');
				} else {
					$credit_date = date('Y-m-d');
				}

				$sheet->setCellValue('A2', $wps_settings->type ?? '111');
				$sheet->setCellValue('B2', $wps_settings->customer_name ?? '');
				$sheet->setCellValue('C2', $wps_settings->agreement_code ?? '');
				$sheet->setCellValue('D2', $wps_settings->funding_account ?? '');
				$sheet->setCellValue('E2', $wps_settings->branch_no ?? '');
				$sheet->setCellValue('F2', $credit_date);
				$sheet->setCellValue('G2', $wps_settings->mins_lab_establish_id ?? '');
				$sheet->setCellValue('H2', $wps_settings->ecr_id ?? '');
				$sheet->setCellValue('I2', $wps_settings->bank_code ?? 'RIBL');
				$sheet->setCellValue('J2', $wps_settings->currency ?? 'SAR');
				$sheet->setCellValue('K2', $wps_settings->batch ?? '');
				$sheet->setCellValue('L2', $wps_settings->file_reference ?? '');
			} else {
				// Default values if no WPS settings found
				$sheet->setCellValue('A2', '111');
				$sheet->setCellValue('I2', 'RIBL');
				$sheet->setCellValue('J2', 'SAR');
				$sheet->setCellValue('F2', date('dmY'));
			}

			// === ROW 3: Employee Detail Header ===
			$sheet->setCellValue('A3', 'SN');
			$sheet->setCellValue('B3', 'Beneficiary Ref');
			$sheet->setCellValue('C3', 'Employee Name');
			$sheet->setCellValue('D3', 'Account Number');
			$sheet->setCellValue('E3', 'Bank code');
			$sheet->setCellValue('F3', 'Net Amount');
			$sheet->setCellValue('G3', 'Basic Salary');
			$sheet->setCellValue('H3', 'Housing Allowance');
			$sheet->setCellValue('I3', 'Other earning');
			$sheet->setCellValue('J3', 'Deductions');
			$sheet->setCellValue('K3', 'Address');
			$sheet->setCellValue('L3', 'Currency');
			$sheet->setCellValue('M3', 'Status');
			$sheet->setCellValue('N3', 'Payment Desc');
			$sheet->setCellValue('O3', 'Payment Ref');

			// === ROW 4+: Employee Data ===
			$row = 4; // Start from row 4
			$sn = 1;
			foreach ($export_data as $employee) {
				$payment_desc = $wps_settings ? ($wps_settings->payment_desc ?? 'Salary for ' . date('F Y', strtotime($month))) : 'Salary for ' . date('F Y', strtotime($month));
				$payment_ref = $wps_settings ? ($wps_settings->payment_ref ?? '') : '';

				$sheet->setCellValue('A' . $row, str_pad($sn, 4, '0', STR_PAD_LEFT)); // SN with leading zeros
				$sheet->setCellValue('B' . $row, $employee['employee_id_iqama'] ?? ''); // Beneficiary Ref (Iqama number)
				$sheet->setCellValue('C' . $row, $employee['employee_name']); // Employee Name
				$sheet->setCellValue('D' . $row, $employee['employee_account_no_iban']); // Account Number/IBAN
				$sheet->setCellValue('E' . $row, $employee['bank_code'] ?? ''); // Bank Code
				$sheet->setCellValue('F' . $row, number_format($employee['net_amount'], 2, '.', '')); // Net Amount
				$sheet->setCellValue('G' . $row, number_format($employee['basic_salary'], 2, '.', '')); // Basic Salary
				$sheet->setCellValue('H' . $row, number_format($employee['housing_allowance'], 2, '.', '')); // Housing Allowance
				$sheet->setCellValue('I' . $row, number_format($employee['other_earnings'], 2, '.', '')); // Other earning
				$sheet->setCellValue('J' . $row, number_format($employee['deductions'], 2, '.', '')); // Deductions
				$sheet->setCellValue('K' . $row, $employee['address'] ?? ''); // Address
				$sheet->setCellValue('L' . $row, 'SAR'); // Currency
				$sheet->setCellValue('M' . $row, ''); // Status (empty)
				$sheet->setCellValue('N' . $row, $payment_desc); // Payment Description
				$sheet->setCellValue('O' . $row, $payment_ref); // Payment Reference

				$row++;
				$sn++;
			}

			// Auto-size columns
			foreach (range('A', 'O') as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			}

			// Generate filename
			$company_name = $company ? ucfirst($company) . '_' : '';
			$filename = 'WPS_Export_' . $company_name . date('Y_m', strtotime($month)) . '.xlsx';

			// Set headers for file download
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			// Write file to output
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
			$writer->save('php://output');
			exit;
		}

		echo json_encode(['success' => false, 'message' => 'Invalid request']);
	}

	/**
	 * Export payroll data to Excel (all columns except checkbox and payslip)
	 */
	public function export_payroll_excel() {
		if (!has_permission('hrp_employee', '', 'view') && !is_admin()) {
			echo json_encode(['success' => false, 'message' => 'Access denied']);
			return;
		}

		if ($this->input->post()) {
			$staff_ids = $this->input->post('staff_ids');
			$month = $this->input->post('month');
			$payroll_id = $this->input->post('payroll_id');

			if (empty($staff_ids) || !is_array($staff_ids)) {
				echo json_encode(['success' => false, 'message' => 'No employees selected']);
				return;
			}

			// Get employees data
			$this->load->model('staff_model');

			// Normalize month to YYYY-MM format
			$month_formatted = date('Y-m', strtotime($month));
			$month_db = date('Y-m-d', strtotime($month . '-01'));

			// Get payroll data from database
			$employees_data = [];
			foreach ($staff_ids as $staff_id) {
				$employee = $this->hr_payroll_model->get_employees_data_by_staff($staff_id, $month_db);
				if ($employee) {
					$employees_data[] = $employee;
				}
			}

			if (empty($employees_data)) {
				echo json_encode(['success' => false, 'message' => 'No data found for selected employees']);
				return;
			}

			// Load PhpSpreadsheet library
			require_once(APPPATH . 'vendor/autoload.php');

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			// Define column headers (excluding 'select' checkbox and 'payslip' button)
			$headers = [
				'Employee Number',
				'Employee Name',
				'Employee ID/Iqama',
				'Account No/IBAN',
				'Bank Code',
				'GOSI Basic Salary',
				'GOSI Housing Allowance',
				'GOSI Other Allowance',
				'GOSI Deduction',
				'Total Amount',
				'Balance',
				'Full Salary',
				'Basic',
				'OT Hours',
				'OT Rate',
				'OT Amount',
				'Allowance',
				'Addition',
				'Deduction',
				'Mention',
				'Month',
				'Comment 1',
				'Comment 2',
				'Comment 3'
			];

			// Set header row
			$col = 'A';
			foreach ($headers as $header) {
				$sheet->setCellValue($col . '1', $header);
				$col++;
			}

			// Style header row
			$lastCol = chr(ord('A') + count($headers) - 1);
			$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
				'font' => ['bold' => true],
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'CCCCCC']
				]
			]);

			// Fill data rows
			$row = 2;
			foreach ($employees_data as $employee) {
				$staff_info = $this->hr_payroll_model->get_staff_info($employee['staff_id']);
				$staff_name = $staff_info ? $staff_info->name : '';

				// Get employee number
				$employee_number = $this->hr_payroll_model->hrp_format_code('RWAD', $employee['staff_id'], 5);

				// Get additions/deductions from adjustments
				$hrp_additions = $this->hr_payroll_model->get_additions_for_month($employee['staff_id'], $month_db);
				$hrp_deductions = $this->hr_payroll_model->get_deductions_for_month($employee['staff_id'], $month_db);

				$sheet->setCellValue('A' . $row, $employee_number);
				$sheet->setCellValue('B' . $row, $staff_name);
				$sheet->setCellValue('C' . $row, $employee['employee_id_iqama'] ?? '');
				$sheet->setCellValue('D' . $row, $employee['employee_account_no_iban'] ?? '');
				$sheet->setCellValue('E' . $row, $employee['bank_code'] ?? '');
				$sheet->setCellValue('F' . $row, $employee['gosi_basic_salary'] ?? '');
				$sheet->setCellValue('G' . $row, $employee['gosi_housing_allowance'] ?? '');
				$sheet->setCellValue('H' . $row, $employee['gosi_other_allowance'] ?? '');
				$sheet->setCellValue('I' . $row, $employee['gosi_deduction'] ?? '');
				$sheet->setCellValue('J' . $row, $employee['total_amount'] ?? '');
				$sheet->setCellValue('K' . $row, $employee['balance'] ?? '');
				$sheet->setCellValue('L' . $row, $employee['full_salary'] ?? '');
				$sheet->setCellValue('M' . $row, $employee['basic'] ?? '');
				$sheet->setCellValue('N' . $row, $employee['ot_hours'] ?? '0.00');
				$sheet->setCellValue('O' . $row, $employee['ot_rate'] ?? '0.00');
				$sheet->setCellValue('P' . $row, $employee['ot_amount'] ?? '0.00');
				$sheet->setCellValue('Q' . $row, $employee['allowance'] ?? '');
				$sheet->setCellValue('R' . $row, number_format($hrp_additions, 2));
				$sheet->setCellValue('S' . $row, number_format($hrp_deductions, 2));
				$sheet->setCellValue('T' . $row, $employee['mention'] ?? '');
				$sheet->setCellValue('U' . $row, $employee['payroll_month'] ?? date('F Y', strtotime($month_db)));
				$sheet->setCellValue('V' . $row, $employee['comment_1'] ?? '');
				$sheet->setCellValue('W' . $row, $employee['comment_2'] ?? '');
				$sheet->setCellValue('X' . $row, $employee['comment_3'] ?? '');

				$row++;
			}

			// Apply column background colors (matching the grid colors)
			$totalRows = $row - 1; // Last row number (excluding header)

			// Pale green for GOSI columns (F-J: GOSI Basic Salary to Total Amount)
			$sheet->getStyle('F2:J' . $totalRows)->applyFromArray([
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'D4EDDA']
				]
			]);

			// Pale orange for Balance column (K)
			$sheet->getStyle('K2:K' . $totalRows)->applyFromArray([
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'FFE5D0']
				]
			]);

			// Pale blue for payslip columns (L-S: Full Salary to Deduction)
			$sheet->getStyle('L2:S' . $totalRows)->applyFromArray([
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'D1ECF1']
				]
			]);

			// Pale amber/yellow for reference/comment columns (T-X: Mention, Month, Comment 1-3)
			$sheet->getStyle('T2:X' . $totalRows)->applyFromArray([
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'FFF3CD']
				]
			]);

			// Auto-size columns
			foreach (range('A', 'X') as $columnID) {
				$sheet->getColumnDimension($columnID)->setAutoSize(true);
			}

			// Generate filename
			$filename = 'Payroll_Data_' . date('Y_m', strtotime($month)) . '_' . date('YmdHis') . '.xlsx';

			// Set headers for file download
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			// Write file to output
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
			$writer->save('php://output');
			exit;
		}

		echo json_encode(['success' => false, 'message' => 'Invalid request']);
	}


	/**
	 * render filter query
	 * @param  [type] $data_month
	 * @param  [type] $data_staff
	 * @param  [type] $data_department
	 * @param  [type] $data_role_attendance
	 * @return [type]
	 */
	public function render_filter_query($data_month, $data_staff, $data_department, $data_role_attendance) {

		$months_filter = $data_month;
		$querystring = ' active=1';
		$department = $data_department;

		$staff = '';
		if (isset($data_staff)) {
			$staff = $data_staff;
		}
		$staff_querystring = '';
		$department_querystring = '';
		$role_querystring = '';

		if ($department != '') {
			$arrdepartment = $this->staff_model->get('', 'staffid in (select tblstaff_departments.staffid from tblstaff_departments where departmentid = ' . $department . ')');
			$temp = '';
			foreach ($arrdepartment as $value) {
				$temp = $temp . $value['staffid'] . ',';
			}
			$temp = rtrim($temp, ",");
			$department_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if ($staff != '') {
			$temp = '';
			$araylengh = count($staff);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $staff[$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$staff_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if (isset($data_role_attendance) && $data_role_attendance != '') {
			$temp = '';
			$araylengh = count($data_role_attendance);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $data_role_attendance[$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$role_querystring = 'FIND_IN_SET(role, "' . $temp . '")';
		}

		$arrQuery = array($staff_querystring, $department_querystring, $querystring, $role_querystring);

		$newquerystring = '';
		foreach ($arrQuery as $string) {
			if ($string != '') {
				$newquerystring = $newquerystring . $string . ' AND ';
			}
		}

		$newquerystring = rtrim($newquerystring, "AND ");
		if ($newquerystring == '') {
			$newquerystring = [];
		}

		return $newquerystring;
	}

	/**
	 * manage attendance
	 * @return [type]
	 */
	public function manage_attendance() {
		if (!has_permission('hrp_attendance', '', 'view') && !has_permission('hrp_attendance', '', 'view_own') && !is_admin()) {
			access_denied('hrp_attendance');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_timesheets_status();

		//get current month
		$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));

		//get day header in month
		$days_header_in_month = $this->hr_payroll_model->get_day_header_in_month($current_month, $rel_type);

		$attendances = $this->hr_payroll_model->get_hrp_attendance($current_month);
		$attendances_value = [];

		foreach ($attendances as $key => $value) {
			$attendances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//load deparment by manager
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];

		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/

			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if (isset($staff_i->staff_identifi)) {
					$data_object_kpi[$staff_key]['hr_code'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi[$staff_key]['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi[$staff_key]['staff_name'] = $staff_i->name;

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object_kpi[$staff_key]['staff_departments'] = $list_department;

			} else {
				$data_object_kpi[$staff_key]['hr_code'] = '';
				$data_object_kpi[$staff_key]['staff_name'] = '';
				$data_object_kpi[$staff_key]['staff_departments'] = '';

			}

			if (isset($attendances_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi[$staff_key]['standard_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['standard_workday'];
				$data_object_kpi[$staff_key]['actual_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['actual_workday'];
				$data_object_kpi[$staff_key]['actual_workday_probation'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['actual_workday_probation'];
				$data_object_kpi[$staff_key]['paid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['paid_leave'];
				$data_object_kpi[$staff_key]['unpaid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['unpaid_leave'];
				$data_object_kpi[$staff_key]['id'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['id'];

				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $attendances_value[$staff_value['staffid'] . '_' . $current_month]);

			} else {
				$data_object_kpi[$staff_key]['standard_workday'] = get_hr_payroll_option('standard_working_time');
				$data_object_kpi[$staff_key]['actual_workday_probation'] = 0;
				$data_object_kpi[$staff_key]['actual_workday'] = 0;
				$data_object_kpi[$staff_key]['paid_leave'] = 0;
				$data_object_kpi[$staff_key]['unpaid_leave'] = 0;
				$data_object_kpi[$staff_key]['id'] = 0;
				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $days_header_in_month['days_header']);

			}
			$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
			$data_object_kpi[$staff_key]['month'] = $current_month;
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

		}

		//check is add new or update data
		if (count($attendances_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;
		$data['data_object_kpi'] = $data_object_kpi;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($days_header_in_month['columns_type']);
		$data['col_header'] = json_encode($days_header_in_month['headers']);

		$this->load->view('attendances/attendance_manage', $data);
	}

	/**
	 * add attendance
	 */
	public function add_attendance() {
		if (!has_permission('hrp_attendance', '', 'create') && !has_permission('hrp_attendance', '', 'edit') && !is_admin()) {
			access_denied('hrp_attendance');
		}

		if ($this->input->post()) {
			$data = $this->input->post();
			if (isset($data)) {

				if ($data['hrp_attendance_rel_type'] == 'update') {
					$success = $this->hr_payroll_model->add_update_attendance($data);
				} elseif ($data['hrp_attendance_rel_type'] == 'synchronization') {
					$success = $this->hr_payroll_model->synchronization_attendance($data);
				} else {
					$success = false;
				}

				if ($success) {
					set_alert('success', _l('hrp_updated_successfully'));
				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}
				redirect(admin_url('hr_payroll/manage_attendance'));
			}

		}
	}

	/**
	 * import xlsx employees
	 * @return [type]
	 */
	public function import_xlsx_employees() {
		if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			access_denied('hrp_employee');
		}

		$this->load->model('staff_model');
		$data_staff = $this->staff_model->get(get_staff_user_id());
		/*get language active*/
		if ($data_staff) {
			if ($data_staff->default_language != '') {
				$data['active_language'] = $data_staff->default_language;
			} else {
				$data['active_language'] = get_option('active_language');
			}

		} else {
			$data['active_language'] = get_option('active_language');
		}

		$this->load->view('hr_payroll/employees/import_employees', $data);
	}

	/**
	 * create employees sample file
	 * @return [type]
	 */
	public function create_employees_sample_file()
    {
        if (!has_permission('hrp_employee', '', 'view') && !has_permission('hrp_employee', '', 'view_own') && !is_admin()) {
            access_denied('hrp_employee');
        }

        $month = $this->input->post('month_employees');
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define headers matching the template
        $headers = [
            'Employee Id/Iqama',
            'Employee Account No/IBAN',
            'Employee Name',
            'Bank Code',
            'Basic Salary',
            'Housing Allowance',
            'Other Allowance',
            'GOSI Deduction',
            'Total Amount',
            'Balance',
            'Full Salary',
            'Basic',
            'Ot Hours',
            'Ot Rate',
            'Ot Amount',
            'Allowance',
            'Deduction',
            '', // Empty header
            'Mention'
        ];

        // Set headers
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }

        // Save file
        $temp_dir = FCPATH . 'uploads/payslips/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        $filename = 'employee_sample_' . str_replace('-', '', $month) . '_' . get_staff_user_id() . '.xlsx';
        $file_path = $temp_dir . $filename;

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($file_path);

        if (file_exists($file_path)) {
            echo json_encode([
                'success' => true,
                'filename' => 'uploads/payslips/' . $filename,
                'message' => _l('create_attendance_file_success')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('create_attendance_file_false')
            ]);
        }
    }

	/**
	 * import employees excel
	 * @return [type]
	 */
	public function import_employees_excel()
    {
        if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
            access_denied('hrp_employee');
        }

        $file = $_FILES['file_csv']['tmp_name'];
        $month = $this->input->post('month_employees') ?? null;

		// Log input data
        log_message('debug', 'Import Employees: File=' . ($file ?: 'null') . ', Month=' . ($month ?: 'null'));

        if (!$file || !$month) {
            echo json_encode(['message' => _l('invalid_data'), 'total_row_success' => 0, 'total_row_false' => 0, 'total_rows' => 0]);
            return;
        }

		// Convert month to YYYY-MM-01 format
        $month = date('Y-m-01', strtotime($month . '-01'));

        // Load Excel file
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        $headers = array_shift($data); // First row is headers

		// Replace nulls with empty string and trim spaces
		$headers = array_map(function($h) { return is_null($h) ? '' : trim($h); }, $headers);

		// Only consider headers up to 'Mention'
		$mentionIndex = array_search('Mention', $headers);
		if ($mentionIndex === false) {
			echo json_encode([
				'message' => _l('invalid_excel_headers'),
				'total_row_success' => 0,
				'total_row_false' => 0,
				'total_rows' => 0
			]);
			return;
		}
		$headers = array_slice($headers, 0, $mentionIndex + 1);

		log_message('debug', 'Cleaned Excel Headers: ' . json_encode($headers));

        // Expected headers from template
        $expected_headers = [
            'Employee Id/Iqama',
            'Employee Account No/IBAN',
            'Employee Name',
            'Bank Code',
            'Basic Salary',
            'Housing Allowance',
            'Other Allowance',
            'GOSI Deduction',
            'Total Amount',
            'Balance',
            'Full Salary',
            'Basic',
            'Ot Hours',
            'Ot Rate',
            'Ot Amount',
            'Allowance',
            'Deduction',
            '', // Empty header
            'Mention'
        ];

        // Validate headers
        if (count(array_intersect($headers, $expected_headers)) !== count($expected_headers)) {
			log_message('error', 'Header mismatch: Expected=' . json_encode($expected_headers) . ', Found=' . json_encode($headers));
            echo json_encode([
                'message' => _l('invalid_excel_headers'),
                'total_row_success' => 0,
                'total_row_false' => 0,
                'total_rows' => 0
            ]);
            return;
        }

        // Map Excel headers to database fields
        $field_mapping = [
            'Employee Id/Iqama' => 'employee_id_iqama',
            'Employee Account No/IBAN' => 'employee_account_no_iban',
            // 'Employee Name' => 'employee_name',
            'Bank Code' => 'bank_code',
            'Basic Salary' => 'gosi_basic_salary',
            'Housing Allowance' => 'gosi_housing_allowance',
            'Other Allowance' => 'gosi_other_allowance',
            'GOSI Deduction' => 'gosi_deduction',
            'Total Amount' => 'total_amount',
            'Balance' => 'balance',
            'Full Salary' => 'full_salary',
            'Basic' => 'basic',
            'Ot Hours' => 'ot_hours',
            'Ot Rate' => 'ot_rate',
            'Ot Amount' => 'ot_amount',
            'Allowance' => 'allowance',
            'Deduction' => 'deduction',
            'Mention' => 'mention'
        ];

        $total_rows = 0;
        $total_row_success = 0;
        $total_row_false = 0;
        // Initialize error rows with headers + Issue column
		$error_rows = $headers;
		$error_rows[] = 'Issue';
		$error_rows = [$error_rows];
        $arr_insert = [];
        $arr_update = [];

        foreach ($data as $row_index => $row) {
            $total_rows++;
            $employee = ['month' => $month, 'rel_type' => hrp_get_hr_profile_status()];

            // Map row data to fields
            foreach ($headers as $col_index => $header) {
                if ($header === '') continue; // Skip empty header
                if (isset($field_mapping[$header])) {
                    $employee[$field_mapping[$header]] = $row[$col_index] ?? '';
                }
            }

			$issue = ''; // Track issue for error file

            // Find staff_id by matching employee_id_iqama with tblstaff.iqama_number
            if (!empty($employee['employee_id_iqama'])) {
                $this->db->where('iqama_number', $employee['employee_id_iqama']);
                $staff = $this->db->get(db_prefix() . 'staff')->row_array();
                if ($staff) {
                    $employee['staff_id'] = $staff['staffid'];
                    // $employee['employee_name'] = $staff['firstname'] . ' ' . $staff['lastname'];
                } else {
					log_message('error', 'No staff found for iqama_number: ' . $employee['employee_id_iqama']);
					$issue = 'No matching staff for Employee Id/Iqama';
                    // $error_rows[] = $row;
                    // $total_row_false++;
                    // continue;
                }
            } else {
				log_message('error', 'Empty employee_id_iqama in row ' . ($row_index + 2));
				$issue = 'Employee Id/Iqama is required';
                // $error_rows[] = $row;
                // $total_row_false++;
                // continue;
            }

			// If there is an issue, add to error file and skip insertion
			if ($issue !== '') {
				$clean_row = array_slice($row, 0, $mentionIndex + 1);
				$clean_row[] = $issue;
				$error_rows[] = $clean_row;
				$total_row_false++;
				continue;
			}

            // Recalculate computed fields
            $employee['gosi_basic_salary'] = (float)($employee['gosi_basic_salary'] ?? 0);
            $employee['gosi_housing_allowance'] = (float)($employee['gosi_housing_allowance'] ?? 0);
            $employee['gosi_other_allowance'] = (float)($employee['gosi_other_allowance'] ?? 0);
            $employee['gosi_deduction'] = (float)($employee['gosi_deduction'] ?? 0);
            $employee['basic'] = (float)($employee['basic'] ?? 0);
            $employee['ot_hours'] = (float)($employee['ot_hours'] ?? 0);
            $employee['ot_rate'] = (float)($employee['ot_rate'] ?? 0);
            $employee['allowance'] = (float)($employee['allowance'] ?? 0);
            $employee['deduction'] = (float)($employee['deduction'] ?? 0);

            $employee['total_amount'] = $employee['gosi_basic_salary'] + $employee['gosi_housing_allowance'] + $employee['gosi_other_allowance'] - $employee['gosi_deduction'];
            $employee['ot_amount'] = $employee['ot_hours'] * $employee['ot_rate'];
            $employee['full_salary'] = $employee['basic'] + $employee['ot_amount'] + $employee['allowance'] - $employee['deduction'];
            $employee['balance'] = $employee['full_salary'] - $employee['basic'];

            // Format numeric fields
            $numeric_fields = ['gosi_basic_salary', 'gosi_housing_allowance', 'gosi_other_allowance', 'gosi_deduction', 'total_amount', 'balance', 'full_salary', 'basic', 'ot_hours', 'ot_rate', 'ot_amount', 'allowance', 'deduction'];
            foreach ($numeric_fields as $field) {
                $employee[$field] = number_format($employee[$field], 2, '.', '');
            }

            // Check if record exists
            $this->db->where(['staff_id' => $employee['staff_id'], 'month' => $employee['month']]);
            $existing = $this->db->get(db_prefix() . 'hrp_employees_value')->row_array();
            if ($existing) {
                $employee['id'] = $existing['id'];
                $arr_update[] = $employee;
            } else {
                $arr_insert[] = $employee;
            }
        }

        // Insert or update batch
        $affectedRows = 0;
        if (count($arr_insert) > 0) {
            $affectedRows += $this->db->insert_batch(db_prefix() . 'hrp_employees_value', $arr_insert);
        }
        if (count($arr_update) > 0) {
            $affectedRows += $this->db->update_batch(db_prefix() . 'hrp_employees_value', $arr_update, 'id');
        }

        $total_row_success = $affectedRows;

        // Generate error file if needed
        $filename = '';
        if ($total_row_false > 0) {
            $error_spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $error_sheet = $error_spreadsheet->getActiveSheet();
            $error_sheet->fromArray($error_rows);
            $filename = 'error_import_' . str_replace('-', '', $month) . '_' . get_staff_user_id() . '.xlsx';
            $error_file_path = FCPATH . 'uploads/payslips/' . $filename;
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($error_spreadsheet);
            $writer->save($error_file_path);
        }

		if ($total_row_success > 0) {
			$message = _l('import_processed');
		} else if ($total_row_false > 0) {
			$message = _l('import_failed'); 
		} else {
			$message = _l('no_data_to_import');
		}

        echo json_encode([
            // 'message' => $total_rows > 0 ? _l('import_processed') : _l('no_data_to_import'),
			'message' => $message,
            'total_row_success' => $total_row_success,
            'total_row_false' => $total_row_false,
            'total_rows' => $total_rows,
            'site_url' => site_url(),
            'staff_id' => get_staff_user_id(),
            'filename' => $filename ? 'uploads/payslips/' . $filename : ''
        ]);
    }

	/**
	 * attendance filter
	 * @return [type]
	 */
	public function attendance_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_timesheets_status();

		$months_filter = $data['month'];

		$querystring = ' active=1';
		$department = $data['department'];

		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$staff_querystring = '';
		$department_querystring = '';
		$role_querystring = '';

		if ($department != '') {
			$arrdepartment = $this->staff_model->get('', 'staffid in (select tblstaff_departments.staffid from tblstaff_departments where departmentid = ' . $department . ')');
			$temp = '';
			foreach ($arrdepartment as $value) {
				$temp = $temp . $value['staffid'] . ',';
			}
			$temp = rtrim($temp, ",");
			$department_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if ($staff != '') {
			$temp = '';
			$araylengh = count($staff);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $staff[$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$staff_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if (isset($data['role_attendance'])) {
			$temp = '';
			$araylengh = count($data['role_attendance']);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $data['role_attendance'][$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$role_querystring = 'FIND_IN_SET(role, "' . $temp . '")';
		}

		$arrQuery = array($staff_querystring, $department_querystring, $querystring, $role_querystring);

		$newquerystring = '';
		foreach ($arrQuery as $string) {
			if ($string != '') {
				$newquerystring = $newquerystring . $string . ' AND ';
			}
		}

		$newquerystring = rtrim($newquerystring, "AND ");
		if ($newquerystring == '') {
			$newquerystring = [];
		}

		$month_filter = date('Y-m-d', strtotime($data['month'] . '-01'));
		//get day header in month
		$days_header_in_month = $this->hr_payroll_model->get_day_header_in_month($month_filter, $rel_type);

		$attendances = $this->hr_payroll_model->get_hrp_attendance($month_filter);
		$attendances_value = [];
		foreach ($attendances as $key => $value) {
			$attendances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			foreach ($staffs as $staff_key => $staff_value) {

				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
				if ($staff_i) {

					if (isset($staff_i->staff_identifi)) {
						$data_object_kpi[$staff_key]['hr_code'] = $staff_i->staff_identifi;
					} else {
						$data_object_kpi[$staff_key]['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
					}

					$data_object_kpi[$staff_key]['staff_name'] = $staff_i->name;

					$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

					$list_department = '';
					if (count($arr_department) > 0) {

						foreach ($arr_department as $key => $department) {
							$department_value = $this->departments_model->get($department);

							if ($department_value) {
								if (new_strlen($list_department) != 0) {
									$list_department .= ', ' . $department_value->name;
								} else {
									$list_department .= $department_value->name;
								}
							}

						}
					}

					$data_object_kpi[$staff_key]['staff_departments'] = $list_department;

				} else {
					$data_object_kpi[$staff_key]['hr_code'] = '';
					$data_object_kpi[$staff_key]['staff_name'] = '';
					$data_object_kpi[$staff_key]['staff_departments'] = '';

				}

				if (isset($attendances_value[$staff_value['staffid'] . '_' . $month_filter])) {

					$data_object_kpi[$staff_key]['standard_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['standard_workday'];
					$data_object_kpi[$staff_key]['overtime_hours'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['overtime_hours'] ?? 0;
					$data_object_kpi[$staff_key]['actual_workday_probation'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['actual_workday_probation'];
					$data_object_kpi[$staff_key]['actual_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['actual_workday'];
					$data_object_kpi[$staff_key]['paid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['paid_leave'];
					$data_object_kpi[$staff_key]['unpaid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['unpaid_leave'];
					$data_object_kpi[$staff_key]['id'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['id'];
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $attendances_value[$staff_value['staffid'] . '_' . $month_filter]);

				} else {
					$data_object_kpi[$staff_key]['standard_workday'] = get_hr_payroll_option('standard_working_time');
					$data_object_kpi[$staff_key]['overtime_hours'] = 0;
					$data_object_kpi[$staff_key]['actual_workday_probation'] = 0;
					$data_object_kpi[$staff_key]['actual_workday'] = 0;
					$data_object_kpi[$staff_key]['paid_leave'] = 0;
					$data_object_kpi[$staff_key]['unpaid_leave'] = 0;
					$data_object_kpi[$staff_key]['id'] = 0;
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $days_header_in_month['days_header']);

				}

				$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
				$data_object_kpi[$staff_key]['month'] = $month_filter;

			}

		}

		//check is add new or update data
		if (count($attendances_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'columns' => $days_header_in_month['columns_type'],
			'col_header' => $days_header_in_month['headers'],
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * import xlsx attendance
	 * @return [type]
	 */
	public function import_xlsx_attendance() {
		$this->load->model('staff_model');
		$data_staff = $this->staff_model->get(get_staff_user_id());
		/*get language active*/
		if ($data_staff) {
			if ($data_staff->default_language != '') {
				$data['active_language'] = $data_staff->default_language;
			} else {
				$data['active_language'] = get_option('active_language');
			}

		} else {
			$data['active_language'] = get_option('active_language');
		}

		$this->load->view('hr_payroll/attendances/import_attendance', $data);
	}

	/**
	 * create attendance sample file
	 * @return [type]
	 */
	public function create_attendance_sample_file() {
		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$month_attendance = $this->input->post('month_attendance');

		if (!class_exists('XLSXReader_fin')) {
			require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXReader/XLSXReader.php';
		}
		require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXWriter/xlsxwriter.class.php';

		$this->delete_error_file_day_before('1', HR_PAYROLL_CREATE_ATTENDANCE_SAMPLE);

		$rel_type = hrp_get_timesheets_status();
		//get attendance data
		$current_month = date('Y-m-d', strtotime($month_attendance . '-01'));
		//get day header in month
		$days_header_in_month = $this->hr_payroll_model->get_day_header_in_month($current_month, $rel_type);
		$header_key = array_merge($days_header_in_month['staff_key'], $days_header_in_month['days_key'], $days_header_in_month['attendance_key']);

		$attendances = $this->hr_payroll_model->get_hrp_attendance($current_month);
		$attendances_value = [];
		foreach ($attendances as $key => $value) {
			$attendances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		//Writer file
		$writer_header = [];
		$widths = [];
		foreach ($days_header_in_month['headers'] as $value) {
			$writer_header[$value] = 'string';
			$widths[] = 30;
		}

		$writer = new XLSXWriter();

		$col_style1 = [0, 1, 2, 3, 4, 5, 6];
		$style1 = ['widths' => $widths, 'fill' => '#ff9800', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13];

		$writer->writeSheetHeader_v2('Sheet1', $writer_header, $col_options = ['widths' => $widths, 'fill' => '#03a9f46b', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13],
			$col_style1, $style1);

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			$data_object_kpi = [];
			$staffid = 0;
			$hr_code = '';
			$id = 0;
			$staff_name = '';
			$staff_departments = '';
			$actual_workday_probation = 0;
			$actual_workday = 0;
			$paid_leave = 0;
			$unpaid_leave = 0;
			$standard_workday = 0;

			/*check value from database*/
			$staffid = $staff_value['staffid'];

			/*check value from database*/
			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if (isset($staff_i->staff_identifi)) {
					$data_object_kpi['hr_code'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi['staff_name'] = $staff_i->name;

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object_kpi['staff_departments'] = $list_department;

			} else {
				$data_object_kpi['hr_code'] = '';
				$data_object_kpi['staff_name'] = '';
				$data_object_kpi['staff_departments'] = '';

			}

			if (isset($attendances_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi['standard_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['standard_workday'];
				$data_object_kpi['actual_workday_probation'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['actual_workday_probation'];
				$data_object_kpi['actual_workday'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['actual_workday'];
				$data_object_kpi['paid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['paid_leave'];
				$data_object_kpi['unpaid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['unpaid_leave'];
				$data_object_kpi['id'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['id'];

				$data_object_kpi = array_merge($data_object_kpi, $attendances_value[$staff_value['staffid'] . '_' . $current_month]);

			} else {
				$data_object_kpi['standard_workday'] = get_hr_payroll_option('standard_working_time');
				$data_object_kpi['actual_workday_probation'] = 0;
				$data_object_kpi['actual_workday'] = 0;
				$data_object_kpi['paid_leave'] = 0;
				$data_object_kpi['unpaid_leave'] = 0;
				$data_object_kpi['id'] = 0;
				$data_object_kpi = array_merge($data_object_kpi, $days_header_in_month['days_header']);

			}
			$data_object_kpi['rel_type'] = $rel_type;
			$data_object_kpi['month'] = $current_month;
			$data_object_kpi['staff_id'] = $staff_value['staffid'];

			if ($staff_key == 0) {
				$writer->writeSheetRow('Sheet1', $header_key);
			}

			$get_values_for_keys = $this->get_values_for_keys($data_object_kpi, $header_key);
			$writer->writeSheetRow('Sheet1', $get_values_for_keys);

		}

		$filename = 'attendance_sample_file' . get_staff_user_id() . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
		$writer->writeToFile(new_str_replace($filename, HR_PAYROLL_CREATE_ATTENDANCE_SAMPLE . $filename, $filename));

		echo json_encode([
			'success' => true,
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'filename' => HR_PAYROLL_CREATE_ATTENDANCE_SAMPLE . $filename,
		]);

	}

	/**
	 * get values for keys
	 * @param  [type] $mapping
	 * @param  [type] $keys
	 * @return [type]
	 */
	function get_values_for_keys($mapping, $keys) {
		foreach ($keys as $key) {
			$output_arr[] = $mapping[$key];
		}
		return $output_arr;
	}

	/**
	 * import attendance excel
	 * @return [type]
	 */
	public function import_attendance_excel() {
		if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			access_denied('hrp_employee');
		}

		if (!class_exists('XLSXReader_fin')) {
			require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXReader/XLSXReader.php';
		}
		require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXWriter/xlsxwriter.class.php';

		$filename = '';
		if ($this->input->post()) {
			if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {

				$this->delete_error_file_day_before();
				$rel_type = hrp_get_timesheets_status();

				// Get the temp file path
				$tmpFilePath = $_FILES['file_csv']['tmp_name'];
				// Make sure we have a filepath
				if (!empty($tmpFilePath) && $tmpFilePath != '') {
					$rows = [];
					$arr_insert = [];

					$tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

					if (!file_exists(TEMP_FOLDER)) {
						mkdir(TEMP_FOLDER, 0755);
					}

					if (!file_exists($tmpDir)) {
						mkdir($tmpDir, 0755);
					}

					// Setup our new file path
					$newFilePath = $tmpDir . $_FILES['file_csv']['name'];

					if (move_uploaded_file($tmpFilePath, $newFilePath)) {
						//Writer file
						$writer_header = array(
							_l('staffid') => 'string',
							_l('id') => 'string',
							_l('hr_code') => 'string',
							_l('staff_name') => 'string',
							_l('department') => 'string',
							_l('integration_actual_workday') => 'string',
							_l('integration_paid_leave') => 'string',
							_l('integration_unpaid_leave') => 'string',
							_l('standard_working_time_of_month') => 'string',
							_l('month') => 'string',
							_l('error') => 'string',
						);

						$writer = new XLSXWriter();
						$writer->writeSheetHeader('Sheet1', $writer_header, $col_options = ['widths' => [40, 40, 40, 50, 40, 40, 40, 40, 50, 50]]);

						//Reader file
						$xlsx = new XLSXReader_fin($newFilePath);
						$sheetNames = $xlsx->getSheetNames();
						$data = $xlsx->getSheetData($sheetNames[1]);

						$arr_header = [];

						$arr_header['staff_id'] = 0;
						$arr_header['id'] = 1;
						$arr_header['hr_code'] = 2;
						$arr_header['staff_name'] = 3;
						$arr_header['staff_departments'] = 4;
						$arr_header['actual_workday'] = 5;
						$arr_header['paid_leave'] = 6;
						$arr_header['unpaid_leave'] = 7;
						$arr_header['standard_workday'] = 8;
						$arr_header['month'] = 9;

						$total_rows = 0;
						$total_row_false = 0;

						$column_key = $data[1];
						for ($row = 1; $row < count($data); $row++) {

							$total_rows++;

							$rd = array();
							$flag = 0;
							$flag2 = 0;

							$string_error = '';
							$flag_position_group;
							$flag_department = null;

							$flag_staff_id = 0;

							if (($flag == 1) || $flag2 == 1) {
								//write error file
								$writer->writeSheetRow('Sheet1', [
									$value_staffid,
									$value_dependent_name,
									$value_relationship,
									$value_dependent_bir,
									$value_dependent_iden,
									$value_reason,
									$value_start_month,
									$value_end_month,
									$value_status,
									$string_error,
								]);

								$total_row_false++;
							}

							if ($flag == 0 && $flag2 == 0) {

								$rd = array_combine($column_key, $data[$row]);
								unset($rd['employee_number']);
								unset($rd['employee_name']);
								unset($rd['department_name']);
								unset($rd['hr_code']);
								unset($rd['staff_name']);
								unset($rd['staff_departments']);

								$rows[] = $rd;
								array_push($arr_insert, $rd);

							}

						}

						//insert batch
						if (count($arr_insert) > 0) {
							$this->hr_payroll_model->import_attendance_data($arr_insert);
						}

						$total_rows = $total_rows;
						$total_row_success = isset($rows) ? count($rows) : 0;
						$dataerror = '';
						$message = 'Not enought rows for importing';

						if ($total_row_false != 0) {
							$filename = 'Import_attendance_error_' . get_staff_user_id() . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
							$writer->writeToFile(new_str_replace($filename, HR_PAYROLL_ERROR . $filename, $filename));
						}

					}
				}
			}
		}

		if (file_exists($newFilePath)) {
			@unlink($newFilePath);
		}

		echo json_encode([
			'message' => $message,
			'total_row_success' => $total_row_success,
			'total_row_false' => $total_row_false,
			'total_rows' => $total_rows,
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'filename' => HR_PAYROLL_ERROR . $filename,
		]);
	}

	/**
	 * attendance calculation
	 * @return [type]
	 */
	public function attendance_calculation() {
		if (!has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			access_denied('hrp_employee');
		}

		$data = $this->input->post();
		$this->hr_payroll_model->attendance_calculation($data);
		$message = _l('updated_successfully');
		echo json_encode([
			'message' => $message,
		]);
	}

	/**
	 * manage deductions
	 * @return [type]
	 */
	public function manage_deductions() {
		if (!has_permission('hrp_deduction', '', 'view') && !has_permission('hrp_deduction', '', 'view_own') && !is_admin()) {
			access_denied('hrp_deduction');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_hr_profile_status();

		//get current month
		$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
		$deductions_data = $this->hr_payroll_model->get_deductions_data($current_month);
		$deductions_value = [];
		if (count($deductions_data) > 0) {
			foreach ($deductions_data as $key => $value) {
				$deductions_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		//get deduction data for the first
		$format_deduction_value = $this->hr_payroll_model->get_format_deduction_data();

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

			if ($rel_type == 'hr_records') {
				$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
			} else {
				$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
			}

			$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

			$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

			$list_department = '';
			if (count($arr_department) > 0) {

				foreach ($arr_department as $key => $department) {
					$department_value = $this->departments_model->get($department);

					if ($department_value) {
						if (new_strlen($list_department) != 0) {
							$list_department .= ', ' . $department_value->name;
						} else {
							$list_department .= $department_value->name;
						}
					}
				}
			}

			$data_object_kpi[$staff_key]['department_name'] = $list_department;

			if (isset($deductions_value[$staff_value['staffid'] . '_' . $current_month])) {

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				if (isset($deductions_value[$staff_value['staffid'] . '_' . $current_month]['deduction_value'])) {

					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $deductions_value[$staff_value['staffid'] . '_' . $current_month]['deduction_value']);
				} else {
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_deduction_value['array_deduction']);
				}

				$data_object_kpi[$staff_key]['id'] = $deductions_value[$staff_value['staffid'] . '_' . $current_month]['id'];

			} else {

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_deduction_value['array_deduction']);

				$data_object_kpi[$staff_key]['id'] = 0;

			}
			$data_object_kpi[$staff_key]['month'] = $current_month;

		}

		//check is add new or update data
		if (count($deductions_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($format_deduction_value['column_format']);
		$data['col_header'] = json_encode($format_deduction_value['header']);

		$this->load->view('deductions/deductions_manage', $data);
	}

	/**
	 * add manage deductions
	 */
	public function add_manage_deductions() {
		if (!has_permission('hrp_deduction', '', 'create') && !has_permission('hrp_deduction', '', 'edit') && !is_admin()) {
			access_denied('hrp_deduction');
		}

		if ($this->input->post()) {
			$data = $this->input->post();

			if ($data['hrp_deductions_rel_type'] == 'update') {
				// update data
				$success = $this->hr_payroll_model->deductions_update($data);
			} else {
				$success = false;
			}

			if ($success) {
				set_alert('success', _l('updated_successfully'));
			} else {
				set_alert('success', _l('hrp_updated_successfully'));
			}

			redirect(admin_url('hr_payroll/manage_deductions'));
		}

	}

	/**
	 * deductions filter
	 * @return [type]
	 */
	public function deductions_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_hr_profile_status();

		$months_filter = $data['month'];
		$department = $data['department'];
		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$role_attendance = '';
		if (isset($data['role_attendance'])) {
			$role_attendance = $data['role_attendance'];
		}

		$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);
		//get current month
		$month_filter = date('Y-m-d', strtotime($data['month'] . '-01'));
		$deductions_data = $this->hr_payroll_model->get_deductions_data($month_filter);
		$deductions_value = [];
		foreach ($deductions_data as $key => $value) {
			$deductions_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//get employee data for the first
		$format_deduction_value = $this->hr_payroll_model->get_format_deduction_data();

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			$data_object_kpi = [];

			foreach ($staffs as $staff_key => $staff_value) {
				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
				} else {
					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
				}

				$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$data_object_kpi[$staff_key]['department_name'] = $list_department;

				if (isset($deductions_value[$staff_value['staffid'] . '_' . $month_filter])) {

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					if (isset($deductions_value[$staff_value['staffid'] . '_' . $month_filter]['deduction_value'])) {

						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $deductions_value[$staff_value['staffid'] . '_' . $month_filter]['deduction_value']);
					} else {
						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_deduction_value['array_deduction']);
					}

					$data_object_kpi[$staff_key]['id'] = $deductions_value[$staff_value['staffid'] . '_' . $month_filter]['id'];

				} else {

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_deduction_value['array_deduction']);

					$data_object_kpi[$staff_key]['id'] = 0;

				}
				$data_object_kpi[$staff_key]['month'] = $month_filter;
			}

		}

		//check is add new or update data
		if (count($deductions_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * manage commissions
	 * @return [type]
	 */
	public function manage_commissions() {
		if (!has_permission('hrp_commission', '', 'view') && !has_permission('hrp_commission', '', 'view_own') && !is_admin()) {
			access_denied('hrp_commission');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_hr_profile_status();
		$commission_type = hrp_get_commission_status();

		//get current month
		$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
		$commissions_data = $this->hr_payroll_model->get_commissions_data($current_month);
		$commissions_value = [];
		if (count($commissions_data) > 0) {
			foreach ($commissions_data as $key => $value) {
				$commissions_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		//get deduction data for the first
		$format_commission_value = $this->hr_payroll_model->get_format_commission_data();

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

			if ($rel_type == 'hr_records') {
				$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
			} else {
				$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
			}

			$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

			$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

			$list_department = '';
			if (count($arr_department) > 0) {

				foreach ($arr_department as $key => $department) {
					$department_value = $this->departments_model->get($department);

					if ($department_value) {
						if (new_strlen($list_department) != 0) {
							$list_department .= ', ' . $department_value->name;
						} else {
							$list_department .= $department_value->name;
						}
					}
				}
			}

			$data_object_kpi[$staff_key]['department_name'] = $list_department;

			if (isset($commissions_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi[$staff_key]['commission_amount'] = $commissions_value[$staff_value['staffid'] . '_' . $current_month]['commission_amount'];
				$data_object_kpi[$staff_key]['id'] = $commissions_value[$staff_value['staffid'] . '_' . $current_month]['id'];

			} else {

				$data_object_kpi[$staff_key]['commission_amount'] = 0;
				$data_object_kpi[$staff_key]['id'] = 0;

			}
			$data_object_kpi[$staff_key]['month'] = $current_month;
			$data_object_kpi[$staff_key]['rel_type'] = $commission_type;

		}

		//check is add new or update data
		if (count($commissions_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($format_commission_value['column_format']);
		$data['col_header'] = json_encode($format_commission_value['headers']);

		$this->load->view('commissions/commissions_manage', $data);
	}

	/**
	 * add manage commissions
	 */
	public function add_manage_commissions() {
		if (!has_permission('hrp_commission', '', 'create') && !has_permission('hrp_commission', '', 'edit') && !is_admin()) {
			access_denied('hrp_commission');
		}

		if ($this->input->post()) {
			$data = $this->input->post();

			if ($data['hrp_commissions_rel_type'] == 'update') {
				// update data
				$success = $this->hr_payroll_model->commissions_update($data);
			} elseif ($data['hrp_commissions_rel_type'] == 'synchronization') {
				//synchronization
				$success = $this->hr_payroll_model->commissions_synchronization($data);

			} else {
				$success = false;
			}

			if ($success) {
				set_alert('success', _l('updated_successfully'));
			} else {
				set_alert('success', _l('hrp_updated_successfully'));
			}

			redirect(admin_url('hr_payroll/manage_commissions'));
		}

	}

	/**
	 * commissions filter
	 * @return [type]
	 */
	public function commissions_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_hr_profile_status();
		$commission_type = hrp_get_commission_status();

		$months_filter = $data['month'];
		$department = $data['department'];
		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$role_attendance = '';
		if (isset($data['role_attendance'])) {
			$role_attendance = $data['role_attendance'];
		}

		$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

		//get current month
		$month_filter = date('Y-m-d', strtotime($data['month'] . '-01'));
		$commissions_data = $this->hr_payroll_model->get_commissions_data($month_filter);
		$commissions_value = [];
		if (count($commissions_data) > 0) {
			foreach ($commissions_data as $key => $value) {
				$commissions_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			$data_object_kpi = [];

			foreach ($staffs as $staff_key => $staff_value) {
				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
				} else {
					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
				}

				$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$data_object_kpi[$staff_key]['department_name'] = $list_department;

				if (isset($commissions_value[$staff_value['staffid'] . '_' . $month_filter])) {

					$data_object_kpi[$staff_key]['commission_amount'] = $commissions_value[$staff_value['staffid'] . '_' . $month_filter]['commission_amount'];
					$data_object_kpi[$staff_key]['id'] = $commissions_value[$staff_value['staffid'] . '_' . $month_filter]['id'];

				} else {

					$data_object_kpi[$staff_key]['commission_amount'] = 0;
					$data_object_kpi[$staff_key]['id'] = 0;

				}
				$data_object_kpi[$staff_key]['month'] = $month_filter;
				$data_object_kpi[$staff_key]['rel_type'] = $commission_type;
			}

		}

		//check is add new or update data
		if (count($commissions_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * [import_xlsx_commissions
	 * @return [type]
	 */
	public function import_xlsx_commissions() {
		$this->load->model('staff_model');
		$data_staff = $this->staff_model->get(get_staff_user_id());
		/*get language active*/
		if ($data_staff) {
			if ($data_staff->default_language != '') {
				$data['active_language'] = $data_staff->default_language;
			} else {
				$data['active_language'] = get_option('active_language');
			}

		} else {
			$data['active_language'] = get_option('active_language');
		}

		$this->load->view('hr_payroll/commissions/import_commissions', $data);
	}

	/**
	 * create commissions sample file
	 * @return [type]
	 */
	public function create_commissions_sample_file() {
		if (!has_permission('hrp_commission', '', 'create') && !has_permission('hrp_commission', '', 'edit') && !is_admin()) {
			access_denied('hrp_commission');

		}
		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$month_commission = $this->input->post('month_commissions');

		if (!class_exists('XLSXReader_fin')) {
			require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXReader/XLSXReader.php';
		}
		require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXWriter/xlsxwriter.class.php';

		$this->delete_error_file_day_before('1', HR_PAYROLL_CREATE_COMMISSIONS_SAMPLE);

		$rel_type = hrp_get_commission_status();
		//get commission data
		$current_month = date('Y-m-d', strtotime($month_commission . '-01'));
		//get day header in month
		$format_commission_data = $this->hr_payroll_model->get_format_commission_data($current_month, $rel_type);
		$header_key = $format_commission_data['staff_information'];

		$commissions = $this->hr_payroll_model->get_commissions_data($current_month);
		$commissions_value = [];
		foreach ($commissions as $key => $value) {
			$commissions_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		//Writer file
		$writer_header = [];
		$widths = [];
		foreach ($format_commission_data['headers'] as $value) {
			$writer_header[$value] = 'string';
			$widths[] = 30;
		}

		$writer = new XLSXWriter();

		$col_style1 = [0, 1, 2, 3, 4, 5, 6];
		$style1 = ['widths' => $widths, 'fill' => '#ff9800', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13];

		$writer->writeSheetHeader_v2('Sheet1', $writer_header, $col_options = ['widths' => $widths, 'fill' => '#03a9f46b', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13],
			$col_style1, $style1);

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			$staffid = 0;
			$id = 0;
			$staff_name = '';
			$staff_departments = '';
			$commissions_amount = 0;

			/*check value from database*/
			$staffid = $staff_value['staffid'];

			/*check value from database*/
			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if (isset($staff_i->staff_identifi)) {
					$data_object_kpi['employee_number'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi['employee_name'] = $staff_i->name;

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object_kpi['department_name'] = $list_department;

			} else {
				$data_object_kpi['employee_number'] = '';
				$data_object_kpi['employee_name'] = '';
				$data_object_kpi['department_name'] = '';

			}

			if (isset($commissions_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi['commission_amount'] = $commissions_value[$staff_value['staffid'] . '_' . $current_month]['commission_amount'];
				$data_object_kpi['id'] = $commissions_value[$staff_value['staffid'] . '_' . $current_month]['id'];

			} else {
				$data_object_kpi['commission_amount'] = 0;
				$data_object_kpi['id'] = 0;

			}
			$data_object_kpi['rel_type'] = $rel_type;
			$data_object_kpi['month'] = $current_month;
			$data_object_kpi['staff_id'] = $staff_value['staffid'];

			if ($staff_key == 0) {
				$writer->writeSheetRow('Sheet1', $header_key);
			}
			$get_values_for_keys = $this->get_values_for_keys($data_object_kpi, $header_key);

			$writer->writeSheetRow('Sheet1', $get_values_for_keys);

		}

		$filename = 'commission_sample_file' . get_staff_user_id() . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
		$writer->writeToFile(new_str_replace($filename, HR_PAYROLL_CREATE_COMMISSIONS_SAMPLE . $filename, $filename));

		echo json_encode([
			'success' => true,
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'filename' => HR_PAYROLL_CREATE_COMMISSIONS_SAMPLE . $filename,
		]);

	}

	/**
	 * import commissions excel
	 * @return [type]
	 */
	public function import_commissions_excel() {
		if (!has_permission('hrp_commission', '', 'create') && !has_permission('hrp_commission', '', 'edit') && !is_admin()) {
			access_denied('hrp_commission');
		}

		if (!class_exists('XLSXReader_fin')) {
			require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXReader/XLSXReader.php';
		}
		require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXWriter/xlsxwriter.class.php';

		$filename = '';
		if ($this->input->post()) {
			if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {

				$this->delete_error_file_day_before();
				$rel_type = hrp_get_timesheets_status();

				// Get the temp file path
				$tmpFilePath = $_FILES['file_csv']['tmp_name'];
				// Make sure we have a filepath
				if (!empty($tmpFilePath) && $tmpFilePath != '') {
					$rows = [];
					$arr_insert = [];

					$tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

					if (!file_exists(TEMP_FOLDER)) {
						mkdir(TEMP_FOLDER, 0755);
					}

					if (!file_exists($tmpDir)) {
						mkdir($tmpDir, 0755);
					}

					// Setup our new file path
					$newFilePath = $tmpDir . $_FILES['file_csv']['name'];

					if (move_uploaded_file($tmpFilePath, $newFilePath)) {
						//Writer file
						$writer_header = array(
							_l('staffid') => 'string',
							_l('id') => 'string',
							_l('hr_code') => 'string',
							_l('staff_name') => 'string',
							_l('department') => 'string',
							_l('integration_actual_workday') => 'string',
							_l('integration_paid_leave') => 'string',
							_l('integration_unpaid_leave') => 'string',
							_l('standard_working_time_of_month') => 'string',
							_l('month') => 'string',
							_l('error') => 'string',
						);

						$writer = new XLSXWriter();
						$writer->writeSheetHeader('Sheet1', $writer_header, $col_options = ['widths' => [40, 40, 40, 50, 40, 40, 40, 40, 50, 50]]);

						//Reader file
						$xlsx = new XLSXReader_fin($newFilePath);
						$sheetNames = $xlsx->getSheetNames();
						$data = $xlsx->getSheetData($sheetNames[1]);

						$arr_header = [];

						$arr_header['staff_id'] = 0;
						$arr_header['id'] = 1;
						$arr_header['hr_code'] = 2;
						$arr_header['staff_name'] = 3;
						$arr_header['staff_departments'] = 4;
						$arr_header['actual_workday'] = 5;
						$arr_header['paid_leave'] = 6;
						$arr_header['unpaid_leave'] = 7;
						$arr_header['standard_workday'] = 8;
						$arr_header['month'] = 9;

						$total_rows = 0;
						$total_row_false = 0;

						$column_key = $data[1];
						for ($row = 2; $row < count($data); $row++) {

							$total_rows++;

							$rd = array();
							$flag = 0;
							$flag2 = 0;

							$string_error = '';
							$flag_position_group;
							$flag_department = null;

							$flag_staff_id = 0;

							if (($flag == 1) || $flag2 == 1) {
								//write error file
								$writer->writeSheetRow('Sheet1', [
									$value_staffid,
									$value_dependent_name,
									$value_relationship,
									$value_dependent_bir,
									$value_dependent_iden,
									$value_reason,
									$value_start_month,
									$value_end_month,
									$value_status,
									$string_error,
								]);

								$total_row_false++;
							}

							if ($flag == 0 && $flag2 == 0) {

								$rd = array_combine($column_key, $data[$row]);
								unset($rd['employee_number']);
								unset($rd['employee_name']);
								unset($rd['department_name']);
								unset($rd['hr_code']);
								unset($rd['staff_name']);
								unset($rd['staff_departments']);

								$rows[] = $rd;
								array_push($arr_insert, $rd);

							}

						}

						//insert batch
						if (count($arr_insert) > 0) {
							$this->hr_payroll_model->import_commissions_data($arr_insert);
						}

						$total_rows = $total_rows;
						$total_row_success = isset($rows) ? count($rows) : 0;
						$dataerror = '';
						$message = 'Not enought rows for importing';

						if ($total_row_false != 0) {
							$filename = 'Import_commissions_error_' . get_staff_user_id() . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
							$writer->writeToFile(new_str_replace($filename, HR_PAYROLL_ERROR . $filename, $filename));
						}

					}
				}
			}
		}

		if (file_exists($newFilePath)) {
			@unlink($newFilePath);
		}

		echo json_encode([
			'message' => $message,
			'total_row_success' => $total_row_success,
			'total_row_false' => $total_row_false,
			'total_rows' => $total_rows,
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'filename' => HR_PAYROLL_ERROR . $filename,
		]);
	}

	/**
	 * manage income taxs
	 * @return [type]
	 */
	public function income_taxs_manage() {
		if (!has_permission('hrp_income_tax', '', 'view') && !has_permission('hrp_income_tax', '', 'view_own') && !is_admin()) {
			access_denied('hrp_income_tax');
		}
		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_hr_profile_status();

		//get current month
		$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
		$income_taxs_data = $this->hr_payroll_model->get_income_tax_data($current_month);
		$income_taxs_value = [];
		if (count($income_taxs_data) > 0) {
			foreach ($income_taxs_data as $key => $value) {
				$income_taxs_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		//get tax for year
		$total_income_tax_in_year = $this->hr_payroll_model->get_total_income_tax_in_year($current_month);
		$tax_in_year = [];
		foreach ($total_income_tax_in_year as $t_key => $t_value) {
			$tax_in_year[$t_value['staff_id']] = $t_value;
		}

		//get deduction data for the first
		$format_income_tax_value = $this->hr_payroll_model->get_format_income_tax_data();

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

			if ($rel_type == 'hr_records') {
				$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
			} else {
				$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
			}

			$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

			$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

			$list_department = '';
			if (count($arr_department) > 0) {

				foreach ($arr_department as $key => $department) {
					$department_value = $this->departments_model->get($department);

					if ($department_value) {
						if (new_strlen($list_department) != 0) {
							$list_department .= ', ' . $department_value->name;
						} else {
							$list_department .= $department_value->name;
						}
					}
				}
			}

			$data_object_kpi[$staff_key]['department_name'] = $list_department;

			if (isset($income_taxs_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi[$staff_key]['income_tax'] = $income_taxs_value[$staff_value['staffid'] . '_' . $current_month]['income_tax'];
				$data_object_kpi[$staff_key]['id'] = $income_taxs_value[$staff_value['staffid'] . '_' . $current_month]['id'];

			} else {

				$data_object_kpi[$staff_key]['income_tax'] = 0;
				$data_object_kpi[$staff_key]['id'] = 0;

			}
			$data_object_kpi[$staff_key]['month'] = $current_month;

			if (isset($tax_in_year[$staff_value['staffid']])) {
				$data_object_kpi[$staff_key]['tax_for_year'] = $tax_in_year[$staff_value['staffid']]['tax_for_year'];
			} else {
				$data_object_kpi[$staff_key]['tax_for_year'] = 0;
			}
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($format_income_tax_value['column_format']);
		$data['col_header'] = json_encode($format_income_tax_value['headers']);

		$this->load->view('income_tax/income_tax_manage', $data);
	}

	/**
	 * income taxs filter
	 * @return [type]
	 */
	public function income_taxs_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_hr_profile_status();
		$commission_type = hrp_get_commission_status();

		$months_filter = $data['month'];
		$department = $data['department'];
		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$role_attendance = '';
		if (isset($data['role_attendance'])) {
			$role_attendance = $data['role_attendance'];
		}

		$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

		//get current month
		$current_month = date('Y-m-d', strtotime($data['month'] . '-01'));
		$income_taxs_data = $this->hr_payroll_model->get_income_tax_data($current_month);
		$income_taxs_value = [];
		if (count($income_taxs_data) > 0) {
			foreach ($income_taxs_data as $key => $value) {
				$income_taxs_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		//get tax for year
		$total_income_tax_in_year = $this->hr_payroll_model->get_total_income_tax_in_year($current_month);
		$tax_in_year = [];
		foreach ($total_income_tax_in_year as $t_key => $t_value) {
			$tax_in_year[$t_value['staff_id']] = $t_value;
		}

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			$data_object_kpi = [];

			foreach ($staffs as $staff_key => $staff_value) {
				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
				} else {
					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
				}

				$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$data_object_kpi[$staff_key]['department_name'] = $list_department;

				if (isset($income_taxs_value[$staff_value['staffid'] . '_' . $current_month])) {

					$data_object_kpi[$staff_key]['income_tax'] = $income_taxs_value[$staff_value['staffid'] . '_' . $current_month]['income_tax'];
					$data_object_kpi[$staff_key]['id'] = $income_taxs_value[$staff_value['staffid'] . '_' . $current_month]['id'];

				} else {

					$data_object_kpi[$staff_key]['income_tax'] = 0;
					$data_object_kpi[$staff_key]['id'] = 0;

				}
				$data_object_kpi[$staff_key]['month'] = $current_month;
				if (isset($tax_in_year[$staff_value['staffid']])) {
					$data_object_kpi[$staff_key]['tax_for_year'] = $tax_in_year[$staff_value['staffid']]['tax_for_year'];
				} else {
					$data_object_kpi[$staff_key]['tax_for_year'] = 0;
				}
			}

		}

		echo json_encode([
			'data_object' => $data_object_kpi,
		]);
		die;
	}

	/**
	 * manage insurances
	 * @return [type]
	 */
	public function manage_insurances() {
		if (!has_permission('hrp_insurrance', '', 'view') && !has_permission('hrp_insurrance', '', 'view_own') && !is_admin()) {
			access_denied('hrp_insurrance');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_hr_profile_status();

		//get current month
		$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
		$insurances_data = $this->hr_payroll_model->get_insurances_data($current_month);
		$insurances_value = [];
		if (count($insurances_data) > 0) {
			foreach ($insurances_data as $key => $value) {
				$insurances_value[$value['staff_id'] . '_' . $value['month']] = $value;
			}
		}

		//get insurance data for the first
		$format_insurance_value = $this->hr_payroll_model->get_format_insurance_data();

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];
		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

			if ($rel_type == 'hr_records') {
				$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
			} else {
				$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
			}

			$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

			$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

			$list_department = '';
			if (count($arr_department) > 0) {

				foreach ($arr_department as $key => $department) {
					$department_value = $this->departments_model->get($department);

					if ($department_value) {
						if (new_strlen($list_department) != 0) {
							$list_department .= ', ' . $department_value->name;
						} else {
							$list_department .= $department_value->name;
						}
					}
				}
			}

			$data_object_kpi[$staff_key]['department_name'] = $list_department;

			if (isset($insurances_value[$staff_value['staffid'] . '_' . $current_month])) {

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				if (isset($insurances_value[$staff_value['staffid'] . '_' . $current_month]['insurance_value'])) {
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $insurances_value[$staff_value['staffid'] . '_' . $current_month]['insurance_value']);
				} else {
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_insurance_value['array_insurance']);
				}

				$data_object_kpi[$staff_key]['id'] = $insurances_value[$staff_value['staffid'] . '_' . $current_month]['id'];

			} else {

				// array merge: staff information + earning list (probationary contract) + earning list (formal)
				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_insurance_value['array_insurance']);

				$data_object_kpi[$staff_key]['id'] = 0;

			}
			$data_object_kpi[$staff_key]['month'] = $current_month;

		}

		//check is add new or update data
		if (count($insurances_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($format_insurance_value['column_format']);
		$data['col_header'] = json_encode($format_insurance_value['header']);

		$this->load->view('insurances/insurances_manage', $data);
	}

	/**
	 * add manage insurances
	 */
	public function add_manage_insurances() {
		if (!has_permission('hrp_insurrance', '', 'create') && !has_permission('hrp_insurrance', '', 'edit') && !is_admin()) {
			access_denied('hrp_insurrance');
		}

		if ($this->input->post()) {
			$data = $this->input->post();

			if ($data['hrp_insurances_rel_type'] == 'update') {
				// update data
				$success = $this->hr_payroll_model->insurances_update($data);
			} else {
				$success = false;
			}

			if ($success) {
				set_alert('success', _l('updated_successfully'));
			} else {
				set_alert('success', _l('hrp_updated_successfully'));
			}

			redirect(admin_url('hr_payroll/manage_insurances'));
		}

	}

	/**
	 * insurances filter
	 * @return [type]
	 */
	public function insurances_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_hr_profile_status();

		$months_filter = $data['month'];
		$department = $data['department'];
		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$role_attendance = '';
		if (isset($data['role_attendance'])) {
			$role_attendance = $data['role_attendance'];
		}

		$newquerystring = $this->render_filter_query($months_filter, $staff, $department, $role_attendance);

		//get current month
		$month_filter = date('Y-m-d', strtotime($data['month'] . '-01'));
		$insurances_data = $this->hr_payroll_model->get_insurances_data($month_filter);
		$insurances_value = [];
		foreach ($insurances_data as $key => $value) {
			$insurances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//get employee data for the first
		$format_insurance_value = $this->hr_payroll_model->get_format_insurance_data();

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			$data_object_kpi = [];

			foreach ($staffs as $staff_key => $staff_value) {
				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				if ($rel_type == 'hr_records') {
					$data_object_kpi[$staff_key]['employee_number'] = $staff_value['staff_identifi'];
				} else {
					$data_object_kpi[$staff_key]['employee_number'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_value['staffid'], 5);
				}

				$data_object_kpi[$staff_key]['employee_name'] = $staff_value['name'];

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$data_object_kpi[$staff_key]['department_name'] = $list_department;

				if (isset($insurances_value[$staff_value['staffid'] . '_' . $month_filter])) {

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					if (isset($insurances_value[$staff_value['staffid'] . '_' . $month_filter]['insurance_value'])) {
						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $insurances_value[$staff_value['staffid'] . '_' . $month_filter]['insurance_value']);
					} else {
						$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_insurance_value['array_insurance']);
					}

					$data_object_kpi[$staff_key]['id'] = $insurances_value[$staff_value['staffid'] . '_' . $month_filter]['id'];

				} else {

					// array merge: staff information + earning list (probationary contract) + earning list (formal)
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $format_insurance_value['array_insurance']);

					$data_object_kpi[$staff_key]['id'] = 0;

				}
				$data_object_kpi[$staff_key]['month'] = $month_filter;
			}

		}

		//check is add new or update data
		if (count($insurances_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * delete_error file day before
	 * @return [type]
	 */
	public function delete_error_file_day_before($before_day = '', $folder_name = '') {
		if ($before_day != '') {
			$day = $before_day;
		} else {
			$day = '7';
		}

		if ($folder_name != '') {
			$folder = $folder_name;
		} else {
			$folder = HR_PAYROLL_ERROR;
		}

		//Delete old file before 7 day
		$date = date_create(date('Y-m-d H:i:s'));
		date_sub($date, date_interval_create_from_date_string($day . " days"));
		$before_7_day = strtotime(date_format($date, "Y-m-d H:i:s"));

		foreach (glob($folder . '*') as $file) {

			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					$file_name_arr = new_explode("_", $filename);
					$date_create_file = array_pop($file_name_arr);
					$date_create_file = new_str_replace('.xlsx', '', $date_create_file);

					if ((float) $date_create_file <= (float) $before_7_day) {
						unlink($folder . $filename);
					}
				}
			}
		}
		return true;
	}

	/**
	 * payslip manage
	 * @param  string $id
	 * @return [type]
	 */
	public function payslip_manage($id = '') {
		if (!has_permission('hrp_payslip', '', 'view') && !has_permission('hrp_payslip', '', 'view_own') && !is_admin()) {
			access_denied('hrp_payslip');
		}
		$data['internal_id'] = $id;
		$data['title'] = _l('hr_pay_slips');
		$data['staffs'] = $this->staff_model->get();
        $base_currency = get_base_currency();
        $base_currency_id = 0;
        if ($base_currency) {
        	$base_currency_id = $base_currency->id;
        }
        $data['base_currency_id'] = $base_currency_id;
        $data['currencies'] = $this->currencies_model->get();

		$this->load->view('payslips/payslip_manage', $data);
	}

	/**
	 * payslip table
	 * @return table
	 */
	public function payslip_table() {
		$this->app->get_table_data(module_views_path('hr_payroll', 'payslips/payslip_table'));
	}

	/**
	 * delete payslip
	 * @param  [type] $id
	 * @return [type]
	 */
	public function delete_payslip($id) {
		if (!is_admin() && !has_permission('hrp_payslip', '', 'delete')) {
			access_denied('hrp_payslip');
		}
		if (!$id) {
			redirect(admin_url('hr_payroll/payslip_manage'));
		}

		$response = $this->hr_payroll_model->delete_payslip($id);
		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('is_referenced', _l('payslip_template')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted', _l('payslip_template')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('payslip_template')));
		}
		redirect(admin_url('hr_payroll/payslip_manage'));
	}

	/**
	 * payslip manage
	 * @param  string $id
	 * @return [type]
	 */
	public function payslip_templates_manage($id = '') {
		if (!has_permission('hrp_payslip_template', '', 'view') && !has_permission('hrp_payslip_template', '', 'view_own') && !is_admin()) {
			access_denied('hrp_payslip_template');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$data['staffs'] = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		$data['internal_id'] = $id;

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();

		$data['title'] = _l('payslip_template');
		$this->load->view('payslip_templates/payslip_template_manage', $data);
	}

	/**
	 * payslip table
	 * @return table
	 */
	public function payslip_template_table() {
		$this->app->get_table_data(module_views_path('hr_payroll', 'payslip_templates/payslip_template_table'));
	}

	/**
	 * get column key html add
	 * @return [type]
	 */
	public function get_payroll_column_method_html_add() {
		$method_option = $this->hr_payroll_model->get_list_payroll_column_method(['id' => '']);
		$order_display = $this->hr_payroll_model->count_payroll_column();

		echo json_encode([
			'method_option' => $method_option['method_option'],
			'order_display' => $order_display,

		]);
	}

	/**
	 * get payroll column function name html
	 * @return [type]
	 */
	public function get_payroll_column_function_name_html() {
		$method_option = $this->hr_payroll_model->get_list_payroll_column_function_name(['function_name' => '']);

		echo json_encode([
			'method_option' => $method_option['method_option'],

		]);
	}

	/**
	 * payroll column
	 * @return [type]
	 */
	public function payroll_column() {
		if ($this->input->post()) {
			$data = $this->input->post();
			if (!$this->input->post('id')) {

				if (!is_admin() && !has_permission('hrp_setting', '', 'create')) {
					access_denied('hr_payroll');
				}

				$add = $this->hr_payroll_model->add_payroll_column($data);
				if ($add) {
					$message = _l('added_successfully', _l('payroll_column'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/setting?group=payroll_columns'));
			} else {

				if (!is_admin() && !has_permission('hrp_setting', '', 'edit')) {
					access_denied('hr_payroll');
				}

				$id = $data['id'];
				unset($data['id']);
				$success = $this->hr_payroll_model->update_payroll_column($data, $id);
				if ($success == true) {
					$message = _l('updated_successfully', _l('payroll_column'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/setting?group=payroll_columns'));
			}

		}
	}

	/**
	 * get payroll column
	 * @param  [type] $id
	 * @return [type]
	 */
	public function get_payroll_column($id) {
		//get data
		$payroll_column = $this->hr_payroll_model->get_hrp_payroll_columns($id);
		//get taking method html
		if ($payroll_column) {
			$method_option = $this->hr_payroll_model->get_list_payroll_column_method(['taking_method' => $payroll_column->taking_method]);
		} else {
			$method_option = $this->hr_payroll_model->get_list_payroll_column_method(['taking_method' => '']);
		}
		//get function name html
		if ($payroll_column) {
			$function_name = $this->hr_payroll_model->get_list_payroll_column_function_name(['function_name' => $payroll_column->function_name]);
		} else {
			$function_name = $this->hr_payroll_model->get_list_payroll_column_function_name(['function_name' => '']);
		}

		echo json_encode([
			'payroll_column' => $payroll_column,
			'method_option' => $method_option,
			'function_name' => $function_name,
		]);
		die;

	}

	/**
	 * delete payroll column setting
	 * @param  string $id
	 * @return [type]
	 */
	public function delete_payroll_column_setting($id = '') {
		if (!is_admin() && !has_permission('hrp_setting', '', 'delete')) {
			access_denied('hr_payroll');
		}
		if (!$id) {
			redirect(admin_url('hr_payroll/setting?group=payroll_columns'));
		}

		$response = $this->hr_payroll_model->delete_payroll_column($id);
		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('is_referenced', _l('payslip_template')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted', _l('payslip_template')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('payslip_template')));
		}
		redirect(admin_url('hr_payroll/setting?group=payroll_columns'));
	}

	/**
	 * get payslip template
	 * @param  string $id
	 * @return [type]
	 */
	public function get_payslip_template($id = '') {
		$payslip_template_data = '';
		if (isset($id) && $id != '') {
			$payslip_template = $this->hr_payroll_model->get_hrp_payslip_templates($id);
			// update
			$payslip_template_selected = $this->hr_payroll_model->get_payslip_template_selected_html($payslip_template->payslip_id_copy);
			$payslip_column_selected = $this->hr_payroll_model->get_payslip_column_html($payslip_template->payslip_columns);
			$payslip_template_data = $payslip_template;

		} else {
			// create
			$payslip_template_selected = $this->hr_payroll_model->get_payslip_template_selected_html('');
			$payslip_column_selected = $this->hr_payroll_model->get_payslip_column_html('');
		}

		echo json_encode([
			'payslip_template_selected' => $payslip_template_selected,
			'payslip_column_selected' => $payslip_column_selected,
			'payslip_template_data' => $payslip_template_data,
		]);
		die;

	}

	/**
	 * payslip template
	 * @return [type]
	 */
	public function payslip_template() {
		if (!has_permission('hrp_payslip_template', '', 'create') && !has_permission('hrp_payslip_template', '', 'edit') && !is_admin()) {
			access_denied('hrp_payslip_template');
		}

		if ($this->input->post()) {
			$data = $this->input->post();

			if (!$this->input->post('id')) {

				if (!is_admin() && !has_permission('hrp_payslip_template', '', 'create')) {
					access_denied('hrp_payslip_template');
				}

				$insert_id = $this->hr_payroll_model->add_payslip_template($data);
				if ($insert_id) {
					$this->hr_payroll_model->add_payslip_templates_detail_first($insert_id);

					$message = _l('added_successfully', _l('payroll_column'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/view_payslip_templates_detail/' . $insert_id));
			} else {

				if (!is_admin() && !has_permission('hrp_payslip_template', '', 'edit')) {
					access_denied('hrp_payslip_template');
				}

				$id = $data['id'];
				unset($data['id']);

				$edit_payslip_column = false;
				if (isset($data['edit_payslip_column']) && $data['edit_payslip_column'] == 'true') {
					$edit_payslip_column = true;
					unset($data['edit_payslip_column']);
				}

				$check_update_detail = false;
				$check_update_detail = $this->hr_payroll_model->check_update_payslip_template_detail($data, $id);
				$success = $this->hr_payroll_model->update_payslip_template($data, $id);

				if ($success == true || $success) {
					if ($check_update_detail['status']) {
						$this->hr_payroll_model->update_payslip_templates_detail_first($check_update_detail['old_column_formular'], $id);
					}

					$message = _l('updated_successfully', _l('payroll_column'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/view_payslip_templates_detail/' . $id));
			}

		}
	}

	/**
	 * delete payslip template
	 * @param  [type] $id
	 * @return [type]
	 */
	public function delete_payslip_template($id) {
		if (!is_admin() && !has_permission('hrp_payslip_template', '', 'delete')) {
			access_denied('hr_payroll');
		}
		if (!$id) {
			redirect(admin_url('hr_payroll/payslip_templates_manage'));
		}

		$response = $this->hr_payroll_model->delete_payslip_template($id);
		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('is_referenced', _l('payslip_template')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted', _l('payslip_template')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('payslip_template')));
		}
		redirect(admin_url('hr_payroll/payslip_templates_manage'));
	}

	/**
	 * view payslip templates detail, add or edit
	 * @param [type] $parent_id
	 * @param string $id
	 */
	public function view_payslip_templates_detail($id = "") {

		$data_form = $this->input->post();
		if ($this->input->post()) {
			$data = $this->input->post();
			if(isset($data['csrf_token_name'])){
				unset($data['csrf_token_name']);
			}
			
			if (!is_admin() && !has_permission('hrp_payslip_template', '', 'edit') && !has_permission('hrp_payslip_template', '', 'create')) {
				$message = _l('access_denied');
				echo json_encode(['danger' => false, 'message' => $message]);
				die;
			}

			$id = $data['id'];
			unset($data['id']);
			$success = $this->hr_payroll_model->update_payslip_templates_detail($data, $id);

			if ($success == true) {
				$message = _l('payslip_template') . ' ' . _l('updated_successfully');
				$file_excel = $this->hr_payroll_model->get_hrp_payslip_templates($id);
				echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->templates_name]);
				die;
			} else {
				$message = _l('payslip_template') . ' ' . _l('updated_successfully');
				echo json_encode(['success' => true, 'message' => $message]);
				die;
			}

		}

		if ($id != '') {
			$data['id'] = $id;
			$data['file_excel'] = $this->hr_payroll_model->get_hrp_payslip_templates($data['id']);
			$data['data_form'] = $data['file_excel']->payslip_template_data;

		}
		if (has_permission('hrp_payslip_template', '', 'create') || has_permission('hrp_payslip_template', '', 'edit')) {

			$permission_actions = '<button id="luckysheet_info_detail_save" class="BTNSS btn btn-info luckysheet_info_detail_save pull-right">Save</button><a id="luckysheet_info_detail_export" class="btn btn-info luckysheet_info_detail_export pull-right"> Download</a><a href="' . admin_url() . 'hr_payroll/payslip_templates_manage' . '" class="btn mright5 btn-default pull-right" >Back</a>';
		} else {
			$permission_actions = '<a id="luckysheet_info_detail_export" class="btn btn-info luckysheet_info_detail_export pull-right"> Download</a><a href="' . admin_url() . 'hr_payroll/payslip_templates_manage' . '" class="btn mright5 btn-default pull-right" >Back</a>';
		}

		$data['permission_actions'] = $permission_actions;

		$data['title'] = _l('view_payslip_templates_detail');

		$this->load->view('payslip_templates/add_payslip_template', $data);

	}

	/**
	 * view payslip
	 * @param  string $id
	 * @return [type]
	 */
	public function view_payslip_detail($id = "") {

		if (!is_admin() && !has_permission('hrp_payslip', '', 'view')) {
			access_denied('view_payslip');
		}

		$data_form = $this->input->post();

		if ($this->input->post()) {
			$data = $this->input->post();

			if (!is_admin() && !has_permission('hrp_payslip', '', 'edit') && !has_permission('hrp_payslip', '', 'create')) {
				$message = _l('access_denied');
				echo json_encode(['danger' => false, 'message' => $message]);
				die;
			}
			$id = $data['id'];
			unset($data['id']);
			$success = $this->hr_payroll_model->update_payslip($data, $id);
			if ($success == true) {
				$message = _l('payslip_template') . ' ' . _l('updated_successfully');
				echo json_encode(['success' => true, 'message' => $message]);
				die;
			} else {
				$message = _l('payslip_template') . ' ' . _l('updated_failed');
				echo json_encode(['success' => false, 'message' => $message]);
				die;
			}

		}

		if ($id != '') {
			$data['id'] = $id;
			$payslip = $this->hr_payroll_model->get_hrp_payslip($data['id']);

			$data['payslip'] = $payslip;

			$path = HR_PAYROLL_PAYSLIP_FILE . $payslip->file_name;
			if(!file_exists($path)){
				set_alert('warning', _l('hrp_The_physical_file_has_been_deleted'));
				redirect(admin_url('hr_payroll/payslip_manage'));
			}
			$mystring = file_get_contents($path, true);

			//$data['data_form'] = replace_spreadsheet_value($mystring);
			$data['data_form'] = $mystring;

		}

		if (has_permission('hrp_payslip', '', 'create') || has_permission('hrp_payslip', '', 'edit')) {
			$permission_actions = '<button id="save_data" class="btn mright5 btn-primary pull-right luckysheet_info_detail_save" >Save</button><a href="#" class="btn mright5 btn-success pull-right payslip_download hide" >Download</a><button  class="btn mright5 btn-info pull-right luckysheet_info_detail_exports ">Create file</button><button id="payslip_close" class="btn mright5 btn-warning pull-right luckysheet_info_detail_payslip_close" >Payslip closing</button><a href="' . admin_url() . 'hr_payroll/payslip_manage' . '" class="btn mright5 btn-default pull-right" >Back</a>';
		} else {
			$permission_actions = '<a href="#" class="btn mright5 btn-success pull-right payslip_download hide" >Download</a><button  class="btn mright5 btn-info pull-right luckysheet_info_detail_exports ">Create file</button><a href="' . admin_url() . 'hr_payroll/payslip_manage' . '" class="btn mright5 btn-default pull-right" >Back</a>';
		}
		$data['permission_actions'] = $permission_actions;

		$data['title'] = _l('payslip_detail');

		$this->load->view('payslips/payslip', $data);

	}

	/**
	 * view payslip detail v2
	 * @param  string $id
	 * @return [type]
	 */
	public function view_payslip_detail_v2($id = "") {
		if (!is_admin() && !has_permission('hrp_payslip', '', 'view_own')) {
			access_denied('view_payslip');
		}

		$data_form = $this->input->post();

		if ($this->input->post()) {
			$data = $this->input->post();

			if (!is_admin() && !has_permission('hrp_payslip', '', 'edit') && !has_permission('hrp_payslip', '', 'create')) {
				$message = _l('access_denied');
				echo json_encode(['danger' => false, 'message' => $message]);
				die;
			}
			$id = $data['id'];
			unset($data['id']);
			$success = $this->hr_payroll_model->update_payslip($data, $id);
			if ($success == true) {
				$message = _l('payslip_template') . ' ' . _l('updated_successfully');
				echo json_encode(['success' => true, 'message' => $message]);
				die;
			} else {
				$message = _l('payslip_template') . ' ' . _l('updated_failed');
				echo json_encode(['success' => false, 'message' => $message]);
				die;
			}

		}

		if ($id != '') {

			$data['id'] = $id;
			$payslip = $this->hr_payroll_model->get_hrp_payslip($data['id']);

			$data['payslip'] = $payslip;

			$path = HR_PAYROLL_PAYSLIP_FILE . $payslip->file_name;
			if(!file_exists($path)){
				set_alert('warning', _l('hrp_The_physical_file_has_been_deleted'));
				redirect(admin_url('hr_payroll/payslip_manage'));
			}
			$mystring = file_get_contents($path, true);

			//remove employees not under management
			$mystring = $this->hr_payroll_model->remove_employees_not_under_management_on_payslip($mystring);

			//$data['data_form'] = replace_spreadsheet_value($mystring);
			$data['data_form'] = $mystring;

		}

		$permission_actions = '<a href="#" class="btn mright5 btn-success pull-right payslip_download hide" >Download</a><button  class="btn mright5 btn-info pull-right luckysheet_info_detail_exports ">Create file</button><a href="' . admin_url() . 'hr_payroll/payslip_manage' . '" class="btn mright5 btn-default pull-right" >Back</a>';
		$data['permission_actions'] = $permission_actions;

		$data['title'] = _l('view_payslip');

		$this->load->view('payslips/payslip_view_own', $data);

	}

	/**
	 * manage bonus
	 * @return [type]
	 */
	public function manage_bonus() {
		if (!has_permission('hrp_bonus_kpi', '', 'view') && !has_permission('hrp_bonus_kpi', '', 'view_own') && !is_admin()) {
			access_denied('hrp_bonus_kpi');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		/*bonus commodity fill*/
		//get current month
		$current_month = date('Y-m');

		/*bonus commodity fill*/

		/*bonus Kpi*/
		//get current month

		//load staff
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];
		$bonus_status = true;

		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/
			$data_object_kpi[$staff_key]['staffid'] = $staff_value['staffid'];

			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if (isset($staff_i->staff_identifi)) {
					$data_object_kpi[$staff_key]['hr_code'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi[$staff_key]['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi[$staff_key]['staff_name'] = $staff_i->name;

				$data_object_kpi[$staff_key]['job_position'] = '';

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object_kpi[$staff_key]['staff_departments'] = $list_department;

			} else {
				$data_object_kpi[$staff_key]['hr_code'] = '';
				$data_object_kpi[$staff_key]['staff_name'] = '';
				$data_object_kpi[$staff_key]['job_position'] = $staff_value['staffid'];
				$data_object_kpi[$staff_key]['staff_departments'] = '';

			}

			//get_data from hrm_allowance_commodity_fill
			$bonus_kpi = $this->hr_payroll_model->get_bonus_by_month($staff_value['staffid'], $current_month);
			if ($bonus_kpi) {

				$data_object_kpi[$staff_key]['bonus_kpi'] = $bonus_kpi->bonus_kpi;

			} else {
				$data_object_kpi[$staff_key]['bonus_kpi'] = 0;
				$bonus_status = false;
			}

		}

		/*bonus Kpi*/
		//check is add new or update data
		if ($bonus_status == true) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['staffs_li'] = $this->staff_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;
		$data['data_object_kpi'] = $data_object_kpi;

		$this->load->view('bonus/bonus_kpi', $data);
	}

	/**
	 * add bonus kpi
	 * @return redirect
	 */
	public function add_bonus_kpi() {
		if (!has_permission('hrp_bonus_kpi', '', 'view') && !has_permission('hrp_bonus_kpi', '', 'edit') && !is_admin()) {
			access_denied('hrp_bonus_kpi');
		}
		if ($this->input->post()) {
			$data = $this->input->post();

			if (isset($data)) {

				$success = $this->hr_payroll_model->add_bonus_kpi($data);

				if ($success) {
					set_alert('success', _l('hrp_updated_successfully'));
				} else {
					set_alert('success', _l('hrp_updated_successfully'));
				}
				redirect(admin_url('hr_payroll/manage_bonus'));
			}

		}
	}

	/**
	 * bonus kpi filter
	 * @return array
	 */
	public function bonus_kpi_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$months_filter = $data['month'];
		$year = date('Y', strtotime(($data['month'] . '-01')));
		$g_month = date('m', strtotime(($data['month'] . '-01')));

		$querystring = ' active=1';

		$department = $data['department'];

		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$staff_querystring = '';
		$department_querystring = '';
		$month_year_querystring = '';
		$month = date('m');
		$month_year = date('Y');
		$cmonth = date('m');
		$cyear = date('Y');

		if ($year != '') {
			$month_new = (string) $g_month;
			if (new_strlen($month_new) == 1) {
				$month_new = '0' . $month_new;
			}
			$month = $month_new;
			$month_year = (int) $year;

		}

		if ($department != '') {
			$arrdepartment = $this->staff_model->get('', 'staffid in (select tblstaff_departments.staffid from tblstaff_departments where departmentid = ' . $department . ')');
			$temp = '';
			foreach ($arrdepartment as $value) {
				$temp = $temp . $value['staffid'] . ',';
			}
			$temp = rtrim($temp, ",");
			$department_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if ($staff != '') {
			$temp = '';
			$araylengh = count($staff);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $staff[$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$staff_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		$arrQuery = array($staff_querystring, $department_querystring, $month_year_querystring, $querystring);

		$newquerystring = '';
		foreach ($arrQuery as $string) {
			if ($string != '') {
				$newquerystring = $newquerystring . $string . ' AND ';
			}
		}

		$newquerystring = rtrim($newquerystring, "AND ");
		if ($newquerystring == '') {
			$newquerystring = [];
		}

		// data return
		$data_object = [];
		$index_data_object = 0;
		$bonus_status = true;

		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			foreach ($staffs as $staffs_key => $staff_value) {

				$bonus_value = $this->hr_payroll_model->get_bonus_by_month($staff_value['staffid'], $months_filter);

				if ($bonus_value) {

					$data_object[$index_data_object]['staffid'] = $staff_value['staffid'];

					$data_object[$index_data_object]['hr_code'] = $staff_value['staff_identifi'];
					$data_object[$index_data_object]['staff_name'] = $staff_value['full_name'];

					$data_object[$index_data_object]['job_position'] = '';

					$data_object[$index_data_object]['bonus_kpi'] = $bonus_value->bonus_kpi;

				} else {
					$data_object[$index_data_object]['staffid'] = $staff_value['staffid'];

					$data_object[$index_data_object]['hr_code'] = $staff_value['staff_identifi'];
					$data_object[$index_data_object]['staff_name'] = $staff_value['full_name'];

					$data_object[$index_data_object]['job_position'] = '';

					$data_object[$index_data_object]['bonus_kpi'] = 0;

					$bonus_status = false;

				}

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_value['staffid'], true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object[$index_data_object]['staff_departments'] = $list_department;

				$index_data_object++;

			}

		}

		//check is add new or update data
		if ($bonus_status == true) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object,
			'button_name' => $button_name,
		]);
		die;
	}

	/**
	 * payslip
	 * @param  string $value
	 * @return [type]
	 */
	public function payslip($value = '') {
		if ($this->input->post()) {
			$data = $this->input->post();

			if (!$this->input->post('id')) {

				if (!is_admin() && !has_permission('hrp_payslip', '', 'create')) {
					access_denied('hrp_payslip');
				}

				$insert_id = $this->hr_payroll_model->add_payslip($data);
				if ($insert_id) {
					$message = _l('added_successfully', _l('hrp_payslip'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/payslip_manage'));

			}

		}
	}

	/**
	 * payslip closing
	 * @return [type]
	 */
	public function payslip_closing() {
		if (!has_permission('hrp_payslip', '', 'edit') && !is_admin()) {
			$message = _l('access_denied');
			echo json_encode(['danger' => false, 'message' => $message]);
			die;
		}
		if ($this->input->post()) {
			$data = $this->input->post();
			if(isset($data['csrf_token_name'])){
				unset($data['csrf_token_name']);
			}
			$hrp_payslip = $this->hr_payroll_model->get_hrp_payslip($data['id']);

			if ($hrp_payslip) {
				$payslip_checked = $this->hr_payroll_model->payslip_checked($hrp_payslip->payslip_month, $hrp_payslip->payslip_template_id, true);
				// if ($payslip_checked) {

					$result = $this->hr_payroll_model->payslip_close($data);
					if ($result == true) {
						$message = _l('hrp_updated_successfully');
						$status = true;
					} else {
						$message = _l('hrp_updated_failed');
						$status = false;
					}
				// } else {
				// 	$status = false;
				// 	$message = _l('payslip_for_the_month_of');
				// }

			} else {
				$message = _l('hrp_updated_failed');
				$status = false;
			}

			echo json_encode([
				'message' => $message,
				'status' => $status,
			]);
		}
	}

	/**
	 * payslip update status
	 * @param  [type] $id
	 * @return [type]
	 */
	public function payslip_update_status($id) {
		if (!is_admin() && !has_permission('hrp_payslip', '', 'udpate')) {
			access_denied('hrp_payslip');
		}

		$result = $this->hr_payroll_model->update_payslip_status($id, 'payslip_opening');
		if ($result) {
			set_alert('success', _l('hrp_updated_successfully'));
		} else {
			set_alert('warning', _l('hrp_updated_failed'));
		}
		redirect(admin_url('hr_payroll/payslip_manage'));
	}

	/**
	 * table staff payslip
	 * @return [type]
	 */
	public function table_staff_payslip() {
		$this->app->get_table_data(module_views_path('hr_payroll', 'employee_payslip/table_staff_payslip'));
	}

	/**
	 * view staff payslip modal
	 * @return [type]
	 */
	public function view_staff_payslip_modal() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$this->load->model('departments_model');

		if ($this->input->post('slug') === 'view') {
			$payslip_detail_id = $this->input->post('payslip_detail_id');

			$data['payslip_detail'] = $this->hr_payroll_model->get_payslip_detail($payslip_detail_id);

			$arr_department = $this->hr_payroll_model->get_staff_departments($data['payslip_detail']->staff_id, true);
			$list_department = '';
			if (count($arr_department) > 0) {

				foreach ($arr_department as $key => $department) {
					$department_value = $this->departments_model->get($department);

					if ($department_value) {
						if (new_strlen($list_department) != 0) {
							$list_department .= ', ' . $department_value->name;
						} else {
							$list_department .= $department_value->name;
						}
					}
				}
			}

			$employee = $this->hr_payroll_model->get_employees_data($data['payslip_detail']->month, '', ' staff_id = ' . $data['payslip_detail']->staff_id);

			$data['payslip'] = $this->hr_payroll_model->get_hrp_payslip($data['payslip_detail']->payslip_id);
			if($data['payslip'] && is_null($data['payslip']->to_currency_name)){
				$base_currency = get_base_currency();
				$base_currency_id = 0;
				if ($base_currency) {
					$data['payslip']->to_currency_name = $base_currency->name;
				}
			}

			$data['employee'] = count($employee) > 0 ? $employee[0] : [];
			$data['list_department'] = $list_department;

			$this->load->view('employee_payslip/staff_payslip_modal_view', $data);
		}
	}

	/**
	 * reports
	 * @return [type]
	 */
	public function reports() {
		if (!has_permission('hrp_report', '', 'view') && !is_admin()) {
			access_denied('reports');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$data['mysqlVersion'] = $this->db->query('SELECT VERSION() as version')->row();
		$data['sqlMode'] = $this->db->query('SELECT @@sql_mode as mode')->row();
		// $data['position']     = $this->hr_payroll_model->get_job_position();
		$data['staff'] = $this->staff_model->get();
		$data['department'] = $this->departments_model->get();
		$data['title'] = _l('hr_reports');

		$this->load->view('reports/manage_reports', $data);
	}

	/**
	 * payslip report
	 * @return [type]
	 */
	public function payslip_report() {
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$months_report = $this->input->post('months_filter');
				$position_filter = $this->input->post('position_filter');
				$department_filter = $this->input->post('department_filter');
				$staff_filter = $this->input->post('staff_filter');

				if ($months_report == 'this_month') {
					$from_date = date('Y-m-01');
					$to_date = date('Y-m-t');
				}
				if ($months_report == '1') {
					$from_date = date('Y-m-01', strtotime('first day of last month'));
					$to_date = date('Y-m-t', strtotime('last day of last month'));
				}
				if ($months_report == 'this_year') {
					$from_date = date('Y-m-d', strtotime(date('Y-01-01')));
					$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
				}
				if ($months_report == 'last_year') {
					$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
					$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
				}

				if ($months_report == '3') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');
				}
				if ($months_report == '6') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == '12') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == 'custom') {
					$from_date = to_sql_date($this->input->post('report_from'));
					$to_date = to_sql_date($this->input->post('report_to'));
				}

				$select = [
					'month',
					'pay_slip_number',
					'employee_name',
					'gross_pay',
					'total_deductions',
					'income_tax_paye',
					'it_rebate_value',
					'commission_amount',
					'bonus_kpi',
					'total_insurance',
					'net_pay',
					'total_cost',
				];
				$query = '';

				if (isset($from_date) && isset($to_date)) {

					$query = ' month >= \'' . $from_date . '\' and month <= \'' . $to_date . '\' and ';
				} else {
					$query = '';
				}

				if (isset($staff_filter)) {
					$staffid_list = implode(',', $staff_filter);
					$query .= db_prefix() . 'hrp_payslip_details.staff_id in (' . $staffid_list . ') and ';
				}
				if (isset($department_filter)) {
					$department_list = implode(',', $department_filter);
					$query .= db_prefix() . 'hrp_payslip_details.staff_id in (SELECT staffid FROM ' . db_prefix() . 'staff_departments where departmentid in (' . $department_list . ')) and ';
				}

				$query .= db_prefix() . 'hrp_payslips.payslip_status = "payslip_closing" and ';

				$total_query = '';
				if (($query) && ($query != '')) {
					$total_query = rtrim($query, ' and');
					$total_query = ' where ' . $total_query;
				}

				$where = [$total_query];

				$aColumns = $select;
				$sIndexColumn = 'id';
				$sTable = db_prefix() . 'hrp_payslip_details';
				$join = [
					'LEFT JOIN ' . db_prefix() . 'hrp_payslips ON ' . db_prefix() . 'hrp_payslip_details.payslip_id = ' . db_prefix() . 'hrp_payslips.id',
				];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'hrp_payslip_details.id',
					db_prefix() . 'hrp_payslip_details.month',
					'payment_run_date',
					db_prefix() . 'hrp_payslip_details.payslip_id',

				]);

				$output = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$payslip = $this->hr_payroll_model->get_hrp_payslip($aRow['payslip_id']);
					if($payslip && is_null($payslip->to_currency_name)){
						$base_currency = get_base_currency();
						$base_currency_id = 0;
						if ($base_currency) {
							$payslip->to_currency_name = $base_currency->name;
						}
					}

					$row = [];

					$row[] = $aRow['id'];
					$row[] = date('Y-m', strtotime($aRow['month']));
					$row[] = $aRow['payment_run_date'];
					$row[] = $aRow['pay_slip_number'];
					$row[] = $aRow['employee_name'];
					$row[] = currency_converter_value($aRow['gross_pay'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['total_deductions'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['income_tax_paye'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['it_rebate_value'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['commission_amount'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['bonus_kpi'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['total_insurance'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['net_pay'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);
					$row[] = currency_converter_value($aRow['total_cost'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * income summary report
	 * @return [type]
	 */
	public function income_summary_report() {
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('departments_model');

				$months_report = $this->input->post('months_filter');
				$position_filter = $this->input->post('position_filter');
				$department_filter = $this->input->post('department_filter');
				$staff_filter = $this->input->post('staff_filter');

				if ($months_report == 'this_month') {
					$from_date = date('Y-m-01');
					$to_date = date('Y-m-t');
				}
				if ($months_report == '1') {
					$from_date = date('Y-m-01', strtotime('first day of last month'));
					$to_date = date('Y-m-t', strtotime('last day of last month'));
				}
				if ($months_report == 'this_year') {
					$from_date = date('Y-m-d', strtotime(date('Y-01-01')));
					$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
				}
				if ($months_report == 'last_year') {
					$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
					$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
				}

				if ($months_report == '3') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');
				}
				if ($months_report == '6') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == '12') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == 'custom') {
					$from_date = to_sql_date($this->input->post('report_from'));
					$to_date = to_sql_date($this->input->post('report_to'));
				}

				$select = [
					'staffid',

				];
				$query = '';
				$staff_query = '';

				if (isset($from_date) && isset($to_date)) {

					$staff_query = ' month >= \'' . $from_date . '\' and month <= \'' . $to_date . '\' and ';
				} else {
					$staff_query = '';
				}

				if (isset($staff_filter)) {
					$staffid_list = implode(',', $staff_filter);
					$query .= db_prefix() . 'staff.staffid in (' . $staffid_list . ') and ';

					$staff_query .= db_prefix() . 'hrp_payslip_details.staff_id in (' . $staffid_list . ') and ';
				}

				if (isset($department_filter)) {
					$department_list = implode(',', $department_filter);
					$query .= db_prefix() . 'staff.staffid in (SELECT staffid FROM ' . db_prefix() . 'staff_departments where departmentid in (' . $department_list . ')) and ';

					$staff_query .= db_prefix() . 'hrp_payslip_details.staff_id in (SELECT staffid FROM ' . db_prefix() . 'staff_departments where departmentid in (' . $department_list . ')) and ';
				}

				$query .= db_prefix() . 'staff.active = "1" and ';

				$total_query = '';
				$staff_query_trim = '';
				if (($query) && ($query != '')) {
					$total_query = rtrim($query, ' and');
					$total_query = ' where ' . $total_query;
				}

				if (($staff_query) && ($staff_query != '')) {
					$staff_query_trim = rtrim($staff_query, ' and');

				}
				$where = [$total_query];

				$aColumns = $select;
				$sIndexColumn = 'staffid';
				$sTable = db_prefix() . 'staff';
				$join = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['name']);

				$output = $result['output'];
				$rResult = $result['rResult'];
				$rel_type = hrp_get_hr_profile_status();
				$staff_income = $this->hr_payroll_model->get_income_summary_report($staff_query_trim);

				$base_currency = get_base_currency();
				$base_currency_name = '';
				if ($base_currency) {
					$base_currency_name = $base_currency->name;
				}

				$staffs_data = [];
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
				foreach ($staffs as $value) {
					$staffs_data[$value['staffid']] = $value;
				}

				$temp = 0;
				foreach ($rResult as $staff_key => $aRow) {
					$row = [];

					$arr_department = $this->hr_payroll_model->get_staff_departments($aRow['staffid'], true);

					$list_department = '';
					if (count($arr_department) > 0) {

						foreach ($arr_department as $key => $department) {
							$department_value = $this->departments_model->get($department);

							if ($department_value) {
								if (new_strlen($list_department) != 0) {
									$list_department .= ', ' . $department_value->name;
								} else {
									$list_department .= $department_value->name;
								}
							}
						}
					}

					$data_object_kpi[$staff_key]['department_name'] = $list_department;

					if ($rel_type == 'hr_records') {
						if (isset($staffs_data[$aRow['staffid']])) {
							$row[] = $staffs_data[$aRow['staffid']]['staff_identifi'];
						} else {
							$row[] = '';
						}
					} else {
						$row[] = $this->hr_payroll_model->hrp_format_code('RWAD', $aRow['staffid'], 5);
					}

					$row[] = $aRow['name'];

					$row[] = $list_department;

					if (isset($staff_income[$aRow['staffid']]['01'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['01'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['02'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['02'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['03'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['03'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['04'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['04'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['05'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['05'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['06'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['06'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['07'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['07'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['08'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['08'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['09'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['09'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['10'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['10'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['11'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['11'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if (isset($staff_income[$aRow['staffid']]['12'])) {
						$row[] = app_format_money($staff_income[$aRow['staffid']]['12'], $base_currency_name);
						$temp++;
					} else {
						$row[] = 0;
					}

					if ($temp != 0) {
						if (isset($staff_income[$aRow['staffid']]['average_income'])) {

							$row[] = app_format_money($staff_income[$aRow['staffid']]['average_income'] / $temp, $base_currency_name);
						} else {
							$row[] = 0;
						}
					} else {
						$row[] = 0;
					}

					$temp = 0;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();

			}
		}
	}

	/**
	 * insurance cost summary report
	 * @return [type]
	 */
	public function insurance_cost_summary_report() {
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('departments_model');

				$months_report = $this->input->post('months_filter');
				$position_filter = $this->input->post('position_filter');
				$department_filter = $this->input->post('department_filter');
				$staff_filter = $this->input->post('staff_filter');

				if ($months_report == 'this_month') {
					$from_date = date('Y-m-01');
					$to_date = date('Y-m-t');
				}
				if ($months_report == '1') {
					$from_date = date('Y-m-01', strtotime('first day of last month'));
					$to_date = date('Y-m-t', strtotime('last day of last month'));
				}
				if ($months_report == 'this_year') {
					$from_date = date('Y-m-d', strtotime(date('Y-01-01')));
					$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
				}
				if ($months_report == 'last_year') {
					$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
					$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
				}

				if ($months_report == '3') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');
				}
				if ($months_report == '6') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == '12') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == 'custom') {
					$from_date = to_sql_date($this->input->post('report_from'));
					$to_date = to_sql_date($this->input->post('report_to'));
				}

				$select = [
					'departmentid',

				];
				$query = '';
				$staff_query = '';

				if (isset($from_date) && isset($to_date)) {

					$staff_query = ' month >= \'' . $from_date . '\' and month <= \'' . $to_date . '\' and ';
				} else {
					$staff_query = '';
				}

				if (isset($staff_filter)) {
					$staffid_list = implode(',', $staff_filter);
					if(1==2){
						$query .= db_prefix() . 'staff.staffid in (' . $staffid_list . ') and ';
					}

					$staff_query .= db_prefix() . 'hrp_payslip_details.staff_id in (' . $staffid_list . ') and ';
				}

				if (isset($department_filter)) {
					$department_list = implode(',', $department_filter);
					$query .= db_prefix() . 'departments.departmentid in  (' . $department_list . ') and ';

					$staff_query .= db_prefix() . 'hrp_payslip_details.staff_id in (SELECT staffid FROM ' . db_prefix() . 'staff_departments where departmentid in (' . $department_list . ')) and ';
				}

				$total_query = '';
				$staff_query_trim = '';
				if (($query) && ($query != '')) {
					$total_query = rtrim($query, ' and');
					$total_query = ' where ' . $total_query;
				}

				if (($staff_query) && ($staff_query != '')) {
					$staff_query_trim = rtrim($staff_query, ' and');

				}

				$where = [$total_query];

				$aColumns = $select;
				$sIndexColumn = 'departmentid';
				$sTable = db_prefix() . 'departments';
				$join = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['name']);

				$output = $result['output'];
				$rResult = $result['rResult'];
				$rel_type = hrp_get_hr_profile_status();

				$staff_insurance = $this->hr_payroll_model->get_insurance_summary_report($staff_query_trim);
				$base_currency = get_base_currency();
				$base_currency_name = '';
				if ($base_currency) {
					$base_currency_name = $base_currency->name;
				}

				$temp_insurance = 0;
				foreach ($rResult as $der_key => $aRow) {
					$row = [];

					$row[] = $aRow['name'];

					$staff_ids = $this->hr_payroll_model->get_staff_in_deparment($aRow['departmentid']);

					foreach ($staff_ids as $key => $value) {
						if (isset($staff_insurance[$value])) {
							$temp_insurance += $staff_insurance[$value];
						}
					}

					$row[] = app_format_money($temp_insurance, $base_currency_name);

					$temp_insurance = 0;

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();

			}
		}
	}

	/**
	 * payslip chart
	 * @return [type]
	 */
	public function payslip_chart() {
		if ($this->input->is_ajax_request()) {

			$months_report = $this->input->post('months_filter');
			$staff_id = $this->input->post('staff_id');
			$filter_by_year = '';

			$filter_by_year .= 'date_format(month, "%Y") = ' . $months_report;

			echo json_encode($this->hr_payroll_model->payslip_chart($filter_by_year, $staff_id));
		}
	}

	/**
	 * department payslip chart
	 * @return [type]
	 */
	public function department_payslip_chart() {
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$months_report = $this->input->post('months_filter');
				$department_filter = $this->input->post('department_filter');

				$from_date = date('Y-m-d', strtotime('1997-01-01'));
				$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
				if ($months_report == 'this_month') {

					$from_date = date('Y-m-01');
					$to_date = date('Y-m-t');
				}
				if ($months_report == '1') {
					$from_date = date('Y-m-01', strtotime('first day of last month'));
					$to_date = date('Y-m-t', strtotime('last day of last month'));

				}
				if ($months_report == 'this_year') {
					$from_date = date('Y-m-d', strtotime(date('Y-01-01')));
					$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
				}
				if ($months_report == 'last_year') {
					$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
					$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
				}

				if ($months_report == '3') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == '6') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == '12') {
					$months_report--;
					$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
					$to_date = date('Y-m-t');

				}
				if ($months_report == 'custom') {
					$from_date = to_sql_date($this->input->post('report_from'));
					$to_date = to_sql_date($this->input->post('report_to'));
				}

				$id_department = '';
				if (isset($department_filter)) {
					$id_department = implode(',', $department_filter);
				}
				$circle_mode = false;
				$list_diploma = array(
					"ps_total_insurance",
					"ps_income_tax_paye",
					"ps_total_deductions",
					"ps_net_pay",
				);
				$list_result = array();
				$list_data_department = [];

				$staff_payslip = $this->hr_payroll_model->get_department_payslip_chart($from_date, $to_date);
				$base_currency = get_base_currency();

				$current_name = '';
				if ($base_currency) {
					$current_name .= $base_currency->name;
				}

				echo json_encode([
					'department' => $staff_payslip['department_name'],
					'data_result' => $staff_payslip['list_result'],
					'circle_mode' => $circle_mode,
					'current_name' => $current_name,
				]);
				die;
			}
		}
	}

	/**
	 * payslip template checked
	 * @return [type]
	 */
	public function payslip_template_checked() {
		$data = $this->input->post();
		if ($this->input->is_ajax_request()) {
			$payslip_template_checked = $this->hr_payroll_model->payslip_template_checked($data);

			if ($payslip_template_checked === true) {
				$status = true;
			} else {
				$status = false;
			}

			echo json_encode([
				'status' => $status,
				'staff_name' => $payslip_template_checked,
			]);
		}
	}

	/**
	 * payslip checked
	 * @return [type]
	 */
	public function payslip_checked() {
		$data = $this->input->post();
		if ($this->input->is_ajax_request()) {
			$payslip_checked = $this->hr_payroll_model->payslip_checked($data['payslip_month'], $data['payslip_template_id']);

			if ($payslip_checked) {
				$status = true;
				$message = '';
			} else {
				$status = false;
				$message = _l('payslip_for_the_month_of');
			}
			$status = true;

			echo json_encode([
				'status' => $status,
				'message' => $message,
			]);
		}
	}

	/**
	 * create payslip file
	 * @return [type]
	 */
	public function create_payslip_file() {

		$data = $this->input->post();
		$get_data = $this->hr_payroll_model->payslip_download($data);
		if ($get_data) {

			if (!class_exists('XLSXReader_fin')) {
				require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXReader/XLSXReader.php';
			}
			require_once module_dir_path(HR_PAYROLL_MODULE_NAME) . '/assets/plugins/XLSXWriter/xlsxwriter.class.php';

			$this->delete_error_file_day_before('1', HR_PAYROLL_CREATE_PAYSLIP_EXCEL);

			$payroll_system_columns_dont_format = payroll_system_columns_dont_format();

			//Writer file
			$writer_header = [];
			$widths = [];
			$col_style1 = [];

			$payroll_column_key = $get_data['payroll_column_key'];
			foreach ($get_data['payroll_header'] as $key => $value) {
				if (!in_array($payroll_column_key[$key], $payroll_system_columns_dont_format)) {

					$writer_header[$value] = '#,##0.00';
				} else {
					$writer_header[$value] = 'string';

				}
				$widths[] = 30;
				$col_style1[] = $key;
			}

			$writer = new XLSXWriter();

			$style1 = ['widths' => $widths, 'fill' => '#ff9800', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13];

			$writer->writeSheetHeader_v2('Sheet1', $writer_header, $col_options = ['widths' => $widths, 'fill' => '#03a9f46b', 'font-style' => 'bold', 'color' => '#0a0a0a', 'border' => 'left,right,top,bottom', 'border-color' => '#0a0a0a', 'font-size' => 13],
				$col_style1, $style1);

			$data_object_kpi = [];
			$writer->writeSheetRow('Sheet1', $get_data['payroll_header']);

			foreach ($get_data['payslip_detail'] as $data_key => $payslip_detail) {

				$writer->writeSheetRow('Sheet1', array_values($payslip_detail));

			}

			$filename = 'Payslip_' . date('Y-m', strtotime($get_data['month'])) . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
			$writer->writeToFile(new_str_replace($filename, HR_PAYROLL_CREATE_PAYSLIP_EXCEL . $filename, $filename));

			echo json_encode([
				'success' => true,
				'message' => _l('create_a_payslip_for_successful_download'),
				'site_url' => site_url(),
				'staff_id' => get_staff_user_id(),
				'filename' => HR_PAYROLL_CREATE_PAYSLIP_EXCEL . $filename,
			]);
			die;
		}

		echo json_encode([
			'success' => false,
			'message' => _l('an_error_occurred_while_creating_a_payslip_to_download'),
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'filename' => HR_PAYROLL_CREATE_PAYSLIP_EXCEL,
		]);
		die;

	}

	/**
	 *employees copy
	 * @return [type]
	 */
	public function employees_copy() {
		if (!has_permission('hrp_employee', '', 'create') && !has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			access_denied('hrp_employee');
		}

		if ($this->input->post()) {
			$data = $this->input->post();
			$results = $this->hr_payroll_model->employees_copy($data);

			if ($results) {
				$message = _l('updated_successfully');
			} else {
				$message = _l('hrp_updated_failed');
			}

			echo json_encode([
				'message' => $results['message'],
				'status' => $results['status'],
			]);
		}

	}

	/**
	 * reset data
	 * @return [type]
	 */
	public function reset_data() {

		if (!is_admin()) {
			access_denied('hr_payroll');
		}
		//delete hrp_employees_value
		$this->db->truncate(db_prefix() . 'hrp_employees_value');

		//delete hrp_employees_timesheets
		$this->db->truncate(db_prefix() . 'hrp_employees_timesheets');

		//delete hrp_commissions
		$this->db->truncate(db_prefix() . 'hrp_commissions');

		//delete hrp_salary_deductions
		$this->db->truncate(db_prefix() . 'hrp_salary_deductions');

		//delete hrp_bonus_kpi
		$this->db->truncate(db_prefix() . 'hrp_bonus_kpi');

		//delete hrp_staff_insurances
		$this->db->truncate(db_prefix() . 'hrp_staff_insurances');

		//delete hrp_payslips
		$this->db->truncate(db_prefix() . 'hrp_payslips');

		//delete hrp_payslip_details
		$this->db->truncate(db_prefix() . 'hrp_payslip_details');

		//delete attendance_sample_file
		foreach (glob('modules/hr_payroll/uploads/attendance_sample_file/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/attendance_sample_file/' . $filename);
				}
			}

		}

		foreach (glob('modules/hr_payroll/uploads/commissions_sample_file/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/commissions_sample_file/' . $filename);
				}
			}

		}

		foreach (glob('modules/hr_payroll/uploads/employees_sample_file/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/employees_sample_file/' . $filename);
				}
			}

		}

		foreach (glob('modules/hr_payroll/uploads/file_error_response/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/file_error_response/' . $filename);
				}
			}

		}

		foreach (glob('modules/hr_payroll/uploads/payslip/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/payslip/' . $filename);
				}
			}

		}

		foreach (glob('modules/hr_payroll/uploads/payslip_excel_file/' . '*') as $file) {
			$file_arr = new_explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					unlink('modules/hr_payroll/uploads/payslip_excel_file/' . $filename);
				}
			}

		}

		set_alert('success', _l('reset_data_successful'));
		redirect(admin_url('hr_payroll/setting?group=reset_data'));

	}

	/**
	 * employee export pdf
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function employee_export_pdf($id) {
		if (!$id) {
			show_404();
		}

		$this->db->where('id', $id);
		$hrp_payslip_details = $this->db->get(db_prefix() . 'hrp_payslip_details')->result_array();

		$data = [];
		$data['payslip_detail'] = $hrp_payslip_details[0];

		$arr_department = $this->hr_payroll_model->get_staff_departments($data['payslip_detail']['staff_id'], true);
		$list_department = '';
		if (count($arr_department) > 0) {

			foreach ($arr_department as $key => $department) {
				$this->load->model('departments_model');

				$department_value = $this->departments_model->get($department);

				if ($department_value) {
					if (new_strlen($list_department) != 0) {
						$list_department .= ', ' . $department_value->name;
					} else {
						$list_department .= $department_value->name;
					}
				}
			}
		}

		$employee = $this->hr_payroll_model->get_employees_data($data['payslip_detail']['month'], '', ' staff_id = ' . $data['payslip_detail']['staff_id']);
		$data['employee'] = count($employee) > 0 ? $employee[0] : [];
		$data['list_department'] = $list_department;
		$data['payslip'] = $this->hr_payroll_model->get_hrp_payslip($data['payslip_detail']['payslip_id']);
		if($data['payslip'] && is_null($data['payslip']->to_currency_name)){
			$base_currency = get_base_currency();
			$base_currency_id = 0;
			if ($base_currency) {
				$data['payslip']->to_currency_name = $base_currency->name;
			}
		}

		$html = $this->load->view('hr_payroll/employee_payslip/export_employee_payslip', $data, true);
		$html .= '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/export_employee_pdf.css') . '"  rel="stylesheet" type="text/css" />';


		try {
			$pdf = $this->hr_payroll_model->employee_export_pdf($html);

		} catch (Exception $e) {
			echo new_html_entity_decode($e->getMessage());
			die;
		}

		$type = 'D';

		if ($this->input->get('output_type')) {
			$type = $this->input->get('output_type');
		}

		if ($this->input->get('print')) {
			$type = 'I';
		}

		$pdf->Output($data['payslip_detail']['employee_number'].'_'.date('m-Y', strtotime($data['payslip_detail']['month'])).'_'.strtotime(date('Y-m-d H:i:s')).'.pdf', $type);
	}

	/**
	 * payslip manage export pdf
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function payslip_manage_export_pdf($id)
	{
		if (!$id) {
			show_404();
		}

		$data = $this->input->post();

		//delete sub folder STOCK_EXPORT
		foreach(glob(HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP . '*') as $file) { 
			$file_arr = new_explode("/",$file);
			$filename = array_pop($file_arr);

			if(file_exists($file)) {
				if ($filename != 'index.html') {
					unlink(HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP.$filename);
				}
			}
		}

		$payslip = $this->hr_payroll_model->get_hrp_payslip($id);
		$payslip_details = $this->hr_payroll_model->get_payslip_detail_by_payslip_id($id);

		$has_pdf_template = false;

		foreach ($payslip_details as $payslip_detail) {
			$check_payslip_has_pdf_template = check_payslip_has_pdf_template($payslip_detail['payslip_id']);
			if(is_numeric($check_payslip_has_pdf_template) && is_numeric($check_payslip_has_pdf_template) && $check_payslip_has_pdf_template != 0 ){
				$has_pdf_template = true;
			}

			if($has_pdf_template){
				$payslip = $this->hr_payroll_model->hr_payroll_get_payslip_pdf_only_for_pdf($payslip_detail['id']);

				try {
					$pdf = hr_payroll_payslip_pdf($payslip);
				} catch (Exception $e) {
					echo $e->getMessage();
					die;
				}
			}else{
				$data = [];
				$data['payslip_detail'] = $payslip_detail;

				$arr_department = $this->hr_payroll_model->get_staff_departments($payslip_detail['staff_id'], true);
				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$this->load->model('departments_model');

						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}
					}
				}

				$employee = $this->hr_payroll_model->get_employees_data($payslip_detail['month'], '', ' staff_id = ' . $payslip_detail['staff_id']);
				$data['employee'] = count($employee) > 0 ? $employee[0] : [];
				$data['list_department'] = $list_department;

				$data['payslip'] = $this->hr_payroll_model->get_hrp_payslip($data['payslip_detail']['payslip_id']);
				if($data['payslip'] && is_null($data['payslip']->to_currency_name)){
					$base_currency = get_base_currency();
					$base_currency_id = 0;
					if ($base_currency) {
						$data['payslip']->to_currency_name = $base_currency->name;
					}
				}
		
				$html = $this->load->view('hr_payroll/employee_payslip/export_employee_payslip', $data, true);
				$html .= '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/export_employee_pdf.css') . '"  rel="stylesheet" type="text/css" />';


				try {
					$pdf = $this->hr_payroll_model->employee_export_pdf($html);

				} catch (Exception $e) {
					echo new_html_entity_decode($e->getMessage());
					die;
				}
			}

			if(!is_null($payslip_detail['employee_number']) && $payslip_detail['employee_number'] != ''  && $payslip_detail['employee_number'] != 0 ){
				$employee_name = $payslip_detail['employee_number'];
			}else{
				$employee_name = slug_it($payslip_detail['employee_name'] ?? '', ['separator' => '_']);
			}

			$this->re_save_to_dir($pdf, $employee_name .'_'.date('m-Y', strtotime($payslip_detail['month'])) . '.pdf');
		}

		$this->load->library('zip');

        //get list file
		foreach(glob(HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP . '*') as $file) { 
			$file_arr = new_explode("/",$file);
			$filename = array_pop($file_arr);

			$this->zip->read_file(HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP. $filename);
		}

		$this->zip->download($payslip->payslip_name .'_'. date('m-Y', strtotime($payslip->payslip_month)). '.zip');
		$this->zip->clear_data();
	}

	/**
	 * re save to dir
	 * @param  [type] $pdf       
	 * @param  [type] $file_name 
	 * @return [type]            
	 */
	private function re_save_to_dir($pdf, $file_name)
	{
		$dir = HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP;

		$dir .= $file_name;

		$pdf->Output($dir, 'F');
	}

	/**
	 * manage attendance timesheet leaves
	 * @return [type] 
	 */
	public function manage_attendance_timesheet_leaves($month = '') {
		if (!has_permission('hrp_attendance', '', 'view') && !has_permission('hrp_attendance', '', 'view_own') && !is_admin()) {
			access_denied('hrp_attendance');
		}

		$this->load->model('staff_model');
		$this->load->model('departments_model');

		$rel_type = hrp_get_timesheets_status();

		//get current month
		if(strlen($month) > 0){
			$current_month = date('Y-m-d', strtotime($month . '-01'));
			$data['current_month'] = date('Y-m-d', strtotime($month . '-01'));
		}else{
			$current_month = date('Y-m-d', strtotime(date('Y-m') . '-01'));
			$data['current_month'] = date('Y-m-d', strtotime(date('Y-m') . '-01'));
		}

		//get day header in month
		$hrp_timesheet_leave_data_sample = hrp_timesheet_leave_data_sample();
		$days_header_in_month = $this->hr_payroll_model->get_day_header_in_month($current_month, $rel_type, false);

		$attendances = $this->hr_payroll_model->get_hrp_attendance_timesheet_leave($current_month);
		$attendances_value = [];
		$cell_background = [];
		$cell_background_data = [];

		foreach ($attendances as $key => $value) {
			$dt_cell_bg = [];
			foreach ($value as $hearder_key => $cell_value) {

				if($hearder_key != 'id' && $hearder_key != 'staff_id' && $hearder_key != 'month' && $hearder_key != 'paid_leave' && $hearder_key != 'unpaid_leave' && $hearder_key != 'rel_type'){
					if(strlen($cell_value) > 0){
						if(preg_match('/^PL:/', $cell_value) && strlen($cell_value) < 7){
							$dt_cell_bg[$hearder_key] = '#0c0';

						}elseif(preg_match('/^UPL:/', $cell_value) && strlen($cell_value) < 7){
							$dt_cell_bg[$hearder_key] = '#c00';

						}elseif(preg_match('/PL:/', $cell_value) && preg_match('/UPL:/', $cell_value)){
							$dt_cell_bg[$hearder_key] = '#FF9800';
						}else{
							$dt_cell_bg[$hearder_key] = '#fff';
						}
					}else{
						$dt_cell_bg[$hearder_key] = '#fff';
					}
				}elseif($hearder_key == 'paid_leave' && (float)$cell_value > 0){
					$dt_cell_bg[$hearder_key] = '#0c0';
				}elseif($hearder_key == 'unpaid_leave' && (float)$cell_value > 0){
					$dt_cell_bg[$hearder_key] = '#c00';
				}else{
					$dt_cell_bg[$hearder_key] = '#fff';
				}
			}
			$cell_background[$value['staff_id'] . '_' . $value['month']] = $dt_cell_bg;
			$attendances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		//load deparment by manager
		if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
			//View own
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission());
		} else {
			//admin or view global
			$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object();
		}

		$data_object_kpi = [];

		foreach ($staffs as $staff_key => $staff_value) {
			/*check value from database*/

			$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
			if ($staff_i) {

				if (isset($staff_i->staff_identifi)) {
					$data_object_kpi[$staff_key]['hr_code'] = $staff_i->staff_identifi;
				} else {
					$data_object_kpi[$staff_key]['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
				}

				$data_object_kpi[$staff_key]['staff_name'] = $staff_i->name;

				$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

				$list_department = '';
				if (count($arr_department) > 0) {

					foreach ($arr_department as $key => $department) {
						$department_value = $this->departments_model->get($department);

						if ($department_value) {
							if (new_strlen($list_department) != 0) {
								$list_department .= ', ' . $department_value->name;
							} else {
								$list_department .= $department_value->name;
							}
						}

					}
				}

				$data_object_kpi[$staff_key]['staff_departments'] = $list_department;

			} else {
				$data_object_kpi[$staff_key]['hr_code'] = '';
				$data_object_kpi[$staff_key]['staff_name'] = '';
				$data_object_kpi[$staff_key]['staff_departments'] = '';

			}

			if (isset($attendances_value[$staff_value['staffid'] . '_' . $current_month])) {

				$data_object_kpi[$staff_key]['paid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['paid_leave'];
				$data_object_kpi[$staff_key]['unpaid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['unpaid_leave'];
				$data_object_kpi[$staff_key]['id'] = $attendances_value[$staff_value['staffid'] . '_' . $current_month]['id'];

				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $attendances_value[$staff_value['staffid'] . '_' . $current_month]);

				$cell_background_data[] = $cell_background[$staff_value['staffid'] . '_' . $current_month];
			} else {
			
				$data_object_kpi[$staff_key]['paid_leave'] = 0;
				$data_object_kpi[$staff_key]['unpaid_leave'] = 0;
				$data_object_kpi[$staff_key]['id'] = 0;
				$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $days_header_in_month['days_header']);
				$cell_background_data[] = $hrp_timesheet_leave_data_sample;

			}
			$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
			$data_object_kpi[$staff_key]['month'] = $current_month;
			$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

		}

		//check is add new or update data
		if (count($attendances_value) > 0) {
			$data['button_name'] = _l('hrp_update');
		} else {
			$data['button_name'] = _l('submit');
		}

		$data['departments'] = $this->departments_model->get();
		$data['roles'] = $this->roles_model->get();
		$data['staffs'] = $staffs;
		$data['data_object_kpi'] = $data_object_kpi;

		$data['body_value'] = json_encode($data_object_kpi);
		$data['columns'] = json_encode($days_header_in_month['columns_type']);
		$data['col_header'] = json_encode($days_header_in_month['headers']);
		$data['cell_background'] = json_encode($cell_background_data);

		$this->load->view('timesheet_leaves/timesheet_leave_manage', $data);
	}

	/**
	 * add timesheets leave
	 */
	public function add_timesheets_leave() {
		if (!has_permission('hrp_attendance', '', 'create') && !has_permission('hrp_attendance', '', 'edit') && !is_admin()) {
			access_denied('hrp_attendance');
		}

		if ($this->input->post()) {
			$data = $this->input->post();
			if (isset($data)) {

				if ($data['hrp_attendance_rel_type'] == 'update') {
					$success = $this->hr_payroll_model->add_update_attendance_timesheets_leave($data);
				} elseif ($data['hrp_attendance_rel_type'] == 'synchronization') {
					$success = $this->hr_payroll_model->synchronization_attendance($data);
				} else {
					$success = false;
				}

				if ($success) {
					set_alert('success', _l('hrp_updated_successfully'));
				}
				if(isset($data['attendance_fill_month'])){
					redirect(admin_url('hr_payroll/manage_attendance_timesheet_leaves/'.$data['attendance_fill_month']));
				}else{
					redirect(admin_url('hr_payroll/manage_attendance_timesheet_leaves'));
				}
			}

		}
	}

	/**
	 * timesheets leave filter
	 * @return [type] 
	 */
	public function timesheets_leave_filter() {
		$this->load->model('departments_model');
		$data = $this->input->post();

		$rel_type = hrp_get_timesheets_status();

		$months_filter = $data['month'];

		$querystring = ' active=1';
		$department = $data['department'];

		$staff = '';
		if (isset($data['staff'])) {
			$staff = $data['staff'];
		}
		$staff_querystring = '';
		$department_querystring = '';
		$role_querystring = '';

		if ($department != '') {
			$arrdepartment = $this->staff_model->get('', 'staffid in (select tblstaff_departments.staffid from tblstaff_departments where departmentid = ' . $department . ')');
			$temp = '';
			foreach ($arrdepartment as $value) {
				$temp = $temp . $value['staffid'] . ',';
			}
			$temp = rtrim($temp, ",");
			$department_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if ($staff != '') {
			$temp = '';
			$araylengh = count($staff);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $staff[$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$staff_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if (isset($data['role_attendance'])) {
			$temp = '';
			$araylengh = count($data['role_attendance']);
			for ($i = 0; $i < $araylengh; $i++) {
				$temp = $temp . $data['role_attendance'][$i];
				if ($i != $araylengh - 1) {
					$temp = $temp . ',';
				}
			}
			$role_querystring = 'FIND_IN_SET(role, "' . $temp . '")';
		}

		$arrQuery = array($staff_querystring, $department_querystring, $querystring, $role_querystring);

		$newquerystring = '';
		foreach ($arrQuery as $string) {
			if ($string != '') {
				$newquerystring = $newquerystring . $string . ' AND ';
			}
		}

		$newquerystring = rtrim($newquerystring, "AND ");
		if ($newquerystring == '') {
			$newquerystring = [];
		}

		$hrp_timesheet_leave_data_sample = hrp_timesheet_leave_data_sample();
		$month_filter = date('Y-m-d', strtotime($data['month'] . '-01'));
		//get day header in month
		$days_header_in_month = $this->hr_payroll_model->get_day_header_in_month($month_filter, $rel_type, false);

		$attendances = $this->hr_payroll_model->get_hrp_attendance_timesheet_leave($month_filter);
		$attendances_value = [];
		$cell_background = [];
		$cell_background_data = [];

		foreach ($attendances as $key => $value) {
			$dt_cell_bg = [];
			foreach ($value as $hearder_key => $cell_value) {

				if($hearder_key != 'id' && $hearder_key != 'staff_id' && $hearder_key != 'month' && $hearder_key != 'paid_leave' && $hearder_key != 'unpaid_leave' && $hearder_key != 'rel_type'){
					if(strlen($cell_value) > 0){
						if(preg_match('/^PL:/', $cell_value) && strlen($cell_value) < 7){
							$dt_cell_bg[$hearder_key] = '#0c0';

						}elseif(preg_match('/^UPL:/', $cell_value) && strlen($cell_value) < 7){
							$dt_cell_bg[$hearder_key] = '#c00';

						}elseif(preg_match('/PL:/', $cell_value) && preg_match('/UPL:/', $cell_value)){
							$dt_cell_bg[$hearder_key] = '#FF9800';
						}else{
							$dt_cell_bg[$hearder_key] = '#fff';
						}
					}else{
						$dt_cell_bg[$hearder_key] = '#fff';
					}
				}else{
					$dt_cell_bg[$hearder_key] = '#fff';
				}

			}
			$cell_background[$value['staff_id'] . '_' . $value['month']] = $dt_cell_bg;
			$attendances_value[$value['staff_id'] . '_' . $value['month']] = $value;
		}

		// data return
		$data_object_kpi = [];
		$index_data_object = 0;
		if ($newquerystring != '') {

			//load staff
			if (!is_admin() && !has_permission('hrp_employee', '', 'view')) {
				//View own
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object(get_staffid_by_permission($newquerystring));
			} else {
				//admin or view global
				$staffs = $this->hr_payroll_model->get_staff_timekeeping_applicable_object($newquerystring);
			}

			foreach ($staffs as $staff_key => $staff_value) {

				/*check value from database*/
				$data_object_kpi[$staff_key]['staff_id'] = $staff_value['staffid'];

				$staff_i = $this->hr_payroll_model->get_staff_info($staff_value['staffid']);
				if ($staff_i) {

					if (isset($staff_i->staff_identifi)) {
						$data_object_kpi[$staff_key]['hr_code'] = $staff_i->staff_identifi;
					} else {
						$data_object_kpi[$staff_key]['hr_code'] = $this->hr_payroll_model->hrp_format_code('RWAD', $staff_i->staffid, 5);
					}

					$data_object_kpi[$staff_key]['staff_name'] = $staff_i->name;

					$arr_department = $this->hr_payroll_model->get_staff_departments($staff_i->staffid, true);

					$list_department = '';
					if (count($arr_department) > 0) {

						foreach ($arr_department as $key => $department) {
							$department_value = $this->departments_model->get($department);

							if ($department_value) {
								if (new_strlen($list_department) != 0) {
									$list_department .= ', ' . $department_value->name;
								} else {
									$list_department .= $department_value->name;
								}
							}

						}
					}

					$data_object_kpi[$staff_key]['staff_departments'] = $list_department;

				} else {
					$data_object_kpi[$staff_key]['hr_code'] = '';
					$data_object_kpi[$staff_key]['staff_name'] = '';
					$data_object_kpi[$staff_key]['staff_departments'] = '';

				}

				if (isset($attendances_value[$staff_value['staffid'] . '_' . $month_filter])) {

					
					$data_object_kpi[$staff_key]['paid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['paid_leave'];
					$data_object_kpi[$staff_key]['unpaid_leave'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['unpaid_leave'];
					$data_object_kpi[$staff_key]['id'] = $attendances_value[$staff_value['staffid'] . '_' . $month_filter]['id'];
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $attendances_value[$staff_value['staffid'] . '_' . $month_filter]);
					$cell_background_data[] = $cell_background[$staff_value['staffid'] . '_' . $month_filter];


				} else {
					
					$data_object_kpi[$staff_key]['paid_leave'] = 0;
					$data_object_kpi[$staff_key]['unpaid_leave'] = 0;
					$data_object_kpi[$staff_key]['id'] = 0;
					$data_object_kpi[$staff_key] = array_merge($data_object_kpi[$staff_key], $days_header_in_month['days_header']);
					$cell_background_data[] = $hrp_timesheet_leave_data_sample;

				}

				$data_object_kpi[$staff_key]['rel_type'] = $rel_type;
				$data_object_kpi[$staff_key]['month'] = $month_filter;

			}

		}

		//check is add new or update data
		if (count($attendances_value) > 0) {
			$button_name = _l('hrp_update');
		} else {
			$button_name = _l('submit');
		}

		echo json_encode([
			'data_object' => $data_object_kpi,
			'columns' => $days_header_in_month['columns_type'],
			'col_header' => $days_header_in_month['headers'],
			'button_name' => $button_name,
			'cell_background' => $cell_background_data

		]);
		die;
	}

	/**
	 * timesheet leave calculation
	 * @return [type] 
	 */
	public function timesheet_leave_calculation()
	{
		if (!has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			access_denied('hrp_employee');
		}

		$data = $this->input->post();
		$this->hr_payroll_model->timesheet_leave_calculation($data);
		$message = _l('updated_successfully');
		echo json_encode([
			'message' => $message,
		]);
	}

	/**
	 * payslip pdf template
	 * @param  string $id 
	 * @return [type]     
	 */
	public function payslip_pdf_template($id = '') {

		if ($this->input->post()) {
			$message = '';
			$data = $this->input->post();

			$data['content'] = $this->input->post('mce_0', false);

			if (isset($data['mce_0'])) {
				unset($data['mce_0']);

			}

			if ($id == '') {
				$id = $this->hr_payroll_model->add_pdf_payslip_template($data);

				if ($id) {
					$message = _l('added_successfully', _l('pdf_payslip_template'));
					set_alert('success', $message);
				} else {
					$message = _l('added_failed', _l('pdf_payslip_template'));
					set_alert('warning', $message);
				}

				redirect(admin_url('hr_payroll/payslip_pdf_template/'.$id));
			} else {

				$success = $this->hr_payroll_model->update_pdf_payslip_template($data, $id);

				if ($success) {
					$message = _l('updated_successfully', _l('pdf_payslip_template'));
					set_alert('success', $message);

				}

				redirect(admin_url('hr_payroll/payslip_pdf_template/'.$id));
			}

		}
		$data = [];

		if ($id == '') {
			//add
			$title = _l('add_pdf_payslip_template');
			$data['title'] = $title;

		} else {
			//update
			$title = _l('update_pdf_payslip_template');
			$data['title'] = $title;
			$data['pdf_payslip_template'] = $this->hr_payroll_model->get_pdf_payslip_template($id);
		}

		$data['payslip_templates'] = $this->hr_payroll_model->get_hrp_payslip_templates();
		$data['pdf_payslip_merge_fields'] = $this->app_merge_fields->get_flat('hr_payslip', ['other'], '{email_signature}');

		$this->load->view('includes/pdf_payslip_template_detail', $data);

	}

	/**
	 * delete payslip pdf template
	 * @param  [type] $id
	 * @return [type]    
	 */
	public function delete_payslip_pdf_template_($id) {
		if (!$id) {
			redirect(admin_url('hr_payroll/setting?group=pdf_payslip_template'));
		}
		$response = $this->hr_payroll_model->delete_pdf_payslip_template($id);
		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('hr_is_referenced', _l('pdf_payslip_template')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted', _l('pdf_payslip_template')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('pdf_payslip_template')));
		}
		redirect(admin_url('hr_payroll/setting?group=pdf_payslip_template'));
	}

	/**
	 * save pdf payslip data
	 * @return [type] 
	 */
	public function save_pdf_payslip_data()
	{
		if (!has_permission('hrp_setting', '', 'edit')) {
			header('HTTP/1.0 400 Bad error');
			echo json_encode([
				'success' => false,
				'message' => _l('access_denied'),
			]);
			die;
		}

		$success = false;
		$message = '';

		$this->db->where('id_contract', $this->input->post('contract_id'));
		$this->db->update(db_prefix() . 'hr_staff_contract', [
			'content' => html_purify($this->input->post('content', false)),
		]);

		$success = $this->db->affected_rows() > 0;
		$message = _l('updated_successfully', _l('contract'));

		echo json_encode([
			'success' => $success,
			'message' => $message,
		]);
	}

	/**
	 * get pdf payslip template
	 * @param  string $pdf_payslip_template_id 
	 * @return [type]                          
	 */
	public function get_pdf_payslip_template($id = '', $payslip_template_id = '') {
		
		$base_currency_id = 0;
		$payslip_template_data = '';
		$payslip_name = '';
		$from_currency_id = 0;
		$from_currency_name = '';
		$from_currency_rate = '';
		$to_currency_id = 0;
		$to_currency_name = '';
		$to_currency_rate = '';

		$base_currency = get_base_currency();
        $base_currency_id = 0;
        if ($base_currency) {
        	$base_currency_id = $base_currency->id;
        }

		if (isset($id) && $id != '' && $id != 0) {

			// update
			$get_hrp_payslip = $this->hr_payroll_model->get_hrp_payslip($id);
			$pdf_payslip_template_id = '';
			if($get_hrp_payslip){
				$pdf_payslip_template_id = $get_hrp_payslip->pdf_template_id;
				$payslip_name = $get_hrp_payslip->payslip_name;
				$from_currency_id = $get_hrp_payslip->from_currency_id;
				$from_currency_name = $get_hrp_payslip->from_currency_name;
				$from_currency_rate = $get_hrp_payslip->from_currency_rate;
				$to_currency_id = $get_hrp_payslip->to_currency_id;
				$to_currency_name = $get_hrp_payslip->to_currency_name;
				$to_currency_rate = $get_hrp_payslip->to_currency_rate;

			}

			$pdf_payslip_template_selected = $this->hr_payroll_model->get_pdf_payslip_template_selected_html($payslip_template_id, $pdf_payslip_template_id);

		} else {
			// create
			$pdf_payslip_template_selected = $this->hr_payroll_model->get_pdf_payslip_template_selected_html($payslip_template_id, '');
		}

		echo json_encode([
			'pdf_payslip_template_selected' => $pdf_payslip_template_selected,
			'payslip_name' => $payslip_name,
			'from_currency_id' => $from_currency_id,
			'from_currency_name' => $from_currency_name,
			'from_currency_rate' => $from_currency_rate,
			'to_currency_id' => $to_currency_id,
			'to_currency_name' => $to_currency_name,
			'to_currency_rate' => $to_currency_rate,
			'base_currency_id' => $base_currency_id,
		]);
		die;
	}

	/**
	 * edit payslip
	 * @return [type] 
	 */
	public function edit_payslip() {
		if ($this->input->post()) {
			$data = $this->input->post();
			if (isset($data['id'])) {

				if (!is_admin() && !has_permission('hrp_payslip', '', 'edit')) {
					access_denied('hrp_payslip');
				}
				$update_data = [];

				$payslip = $this->hr_payroll_model->get_hrp_payslip($data['id']);
				$old_currency_id = $payslip->to_currency_id;
				if($old_currency_id != $data['to_currency_id']){
					$get_currency_rate = $this->hr_payroll_model->get_currency_rate_infor($data['to_currency_id']);
					$update_data['from_currency_name'] = $get_currency_rate['base_currency_name'];
					$update_data['from_currency_rate'] = $get_currency_rate['base_currency_rate'];
					$update_data['to_currency_name'] = $get_currency_rate['to_currency_name'];
					$update_data['to_currency_rate'] = $get_currency_rate['currency_rate'];
					$update_data['to_currency_id'] = $data['to_currency_id'];
					$update_data['from_currency_id'] = $data['from_currency_id'];
				}

				$update_data['payslip_name'] = $data['payslip_name'];
				$update_data['pdf_template_id'] = $data['pdf_template_id'];

				$this->db->where('id', $data['id']);
				$this->db->update(db_prefix().'hrp_payslips', $update_data);
				if($this->db->affected_rows() > 0){
					$message = _l('added_successfully', _l('hrp_payslip'));
					set_alert('success', $message);
				}
				redirect(admin_url('hr_payroll/payslip_manage'));

			}
		}
	}

	/**
	 * new employee export pdf
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function new_employee_export_pdf($id) {

		if (!$id) {
			show_404();
		}

		$payslip = $this->hr_payroll_model->hr_payroll_get_payslip_pdf_only_for_pdf($id);

		try {
			$pdf = hr_payroll_payslip_pdf($payslip);
		} catch (Exception $e) {
			echo $e->getMessage();
			die;
		}

		$type = 'D';
		if ($this->input->get('output_type')) {
			$type = $this->input->get('output_type');
		}

		if ($this->input->get('print')) {
			$type = 'I';
		}
		$pdf->Output(slug_it($payslip->payslip_number ?? '') . '.pdf', $type);
	}

	/**
     * currency rate table
     * @return [type] 
     */
    public function currency_rate_table(){
        $this->app->get_table_data(module_views_path('hr_payroll', 'includes/currencies/currency_rate_table'));
    }

    /**
     * update automatic conversion
     */
    public function update_setting_currency_rate(){
        $data = $this->input->post();
        $success = $this->hr_payroll_model->update_setting_currency_rate($data);
        if($success == true){
            $message = _l('updated_successfully', _l('setting'));
            set_alert('success', $message);
        }
        redirect(admin_url('hr_payroll/setting?group=currency_rates'));
    }

    /**
     * Gets all currency rate online.
     */
    public function get_all_currency_rate_online()
    {
        $result = $this->hr_payroll_model->get_all_currency_rate_online();
        if($result){
            set_alert('success', _l('updated_successfully', _l('hrp_currency_rates')));
        }
        else{
            set_alert('warning', _l('no_data_changes', _l('hrp_currency_rates')));                  
        }

        redirect(admin_url('hr_payroll/setting?group=currency_rates'));
    }

    /**
     * update currency rate
     * @return [type] 
     */
    public function update_currency_rate($id)
    {
        if($this->input->post()){
            $data = $this->input->post();

            $result =  $this->hr_payroll_model->update_currency_rate($data, $id);
            if($result){
                set_alert('success', _l('updated_successfully', _l('hrp_currency_rates')));
            }
            else{
                set_alert('warning', _l('no_data_changes', _l('hrp_currency_rates')));                  
            }
        }

        redirect(admin_url('hr_payroll/setting?group=currency_rates'));
    }

    /**
     * Gets the currency rate online.
     *
     * @param        $id     The identifier
     */
    public function get_currency_rate_online($id)
    {
            $result =  $this->hr_payroll_model->get_currency_rate_online($id);
            echo json_encode(['value' => $result]);
            die;
    }


    /**
     * delete currency
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_currency_rate($id){
        if($id != ''){
            $result =  $this->hr_payroll_model->delete_currency_rate($id);
            if($result){
                set_alert('success', _l('deleted_successfully', _l('hrp_currency_rates')));
            }
            else{
                set_alert('danger', _l('deleted_failure', _l('hrp_currency_rates')));                   
            }
        }
        redirect(admin_url('hr_payroll/setting?group=currency_rates'));
    }

    /**
     * currency rate modal
     * @return [type] 
     */
    public function currency_rate_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id=$this->input->post('id');

        $data=[];
        $data['currency_rate'] = $this->hr_payroll_model->get_currency_rate($id);

        $this->load->view('includes/currencies/currency_rate_modal', $data);
    }

    /**
     * currency rate table
     * @return [type] 
     */
    public function currency_rate_logs_table(){
        $this->app->get_table_data(module_views_path('hr_payroll', 'includes/currencies/currency_rate_logs_table'));
    }

    /**
     * get currency rate
     * @param  [type] $currency_id 
     * @return [type]              
     */
	public function get_currency_rate($currency_id){
        $get_currency_rate = $this->hr_payroll_model->get_currency_rate_infor($currency_id);

        $currency_rate = $get_currency_rate['currency_rate'];
        $convert_str = $get_currency_rate['convert_str'];
        $currency_name = $get_currency_rate['currency_name'];

        echo json_encode([
            'currency_rate' => hrp_app_format_number($currency_rate),
            'convert_str' => $convert_str,
            'currency_name' => $currency_name,
        ]);

    }

	public function generate_payslip($staff_id, $month, $return = false)
	{
		$this->load->helper('url');
		$this->load->model('staff_model'); // Model for staff data
		$this->load->model('projects_model'); // Model for projects data
		$this->load->model('hr_payroll_model'); // Model for employee value data

		// Initialize PDF
		$pdf = new MYPDF();
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->AddPage();
		$pdf->SetFont('dejavusans', '', 10);

		// Global border
		$pdf->Rect(5, 5, 200, 287);

		// Logo
		$pdf->Image(FCPATH.'uploads/company/logo.png', 90, 10, 25);

		// Company name
		$pdf->Ln(30);
		$pdf->SetFont('dejavusans', 'B', 14);
		$pdf->Cell(0, 0, 'AL RWAD AL MOHTARIFEEN GEN. CONT. CO.', 0, 1, 'C');
		$pdf->Ln(5);
		$pdf->Line(5, $pdf->GetY(), 205, $pdf->GetY());

		// Salary Slip title
		$pdf->Ln(5);
		$pdf->SetFont('dejavusans', 'B', 12);
		$pdf->Cell(0, 0, 'Salary Slip', 0, 1, 'C');
		$pdf->Ln(5);
		$pdf->SetFont('dejavusans', '', 10);

		// Fetch employee data from tblstaff
		$employee = $this->db->get_where('tblstaff', ['staffid' => $staff_id])->row_array();
		if (!$employee) {
			show_error('Employee not found', 404);
		}

		// Convert month (YYYY-MM) to YYYY-MM-01 for database query
		$month_date = DateTime::createFromFormat('Y-m', $month);
		$month_date_start = DateTime::createFromFormat('Y-m-d', $month . '-01');
		if (!$month_date) {
			show_error('Invalid month format. Please use YYYY-MM.', 400);
		}
		$db_month = $month_date->format('Y-m-01');

		// Fetch employee value data from tblhrp_employees_value
		$employee_value = $this->db->get_where('tblhrp_employees_value', [
			'staff_id' => $staff_id,
			'month' => $db_month
		])->row_array();
		if (!$employee_value) {
			show_error('Payslip data not found for the specified month', 404);
		}

		// Fetch all payments with staff details
		$this->db->select('p.*, s.firstname, s.lastname');
		$this->db->from(db_prefix() . 'hrp_payments as p');
		$this->db->join(db_prefix() . 'staff as s', 's.staffid = p.created_by', 'left');
		$this->db->where('p.staff_id', $staff_id);
		$this->db->where('p.month', $db_month);
		$this->db->where('p.status', 'paid');
		$this->db->order_by('p.paid_date', 'ASC');
		$payments = $this->db->get()->result_array();

		$total_paid_amount = 0;
		foreach ($payments as $p) {
			$total_paid_amount += floatval($p['amount']);
		}

		$is_paid = count($payments) > 0;
		$paid_amount = $total_paid_amount;

		// Fetch project data from tblprojectassignee and tblprojects
		$this->db->select('tblprojects.name as project_name');
		$this->db->from('tblprojectassignee');
		$this->db->join('tblprojects', 'tblprojects.id = tblprojectassignee.project_id');
		$this->db->where('tblprojectassignee.staff_id', $staff_id);
		$project = $this->db->get()->row_array();
		$project_name = $project ? $project['project_name'] : 'N/A';

		// Format the period for display (e.g., '2025-09-01 - 2025-09-30 (September, 2025)')
		$month_end = (clone $month_date_start)->modify('last day of this month');
		// $period = $month_date_start->format('Y-m-d') . ' - ' . $month_end->format('Y-m-d') . ' (' . $month_date_start->format('F, Y') . ')';
		$period = $month_date_start->format('F Y');

		$job_title = get_profession_type_name($employee['professiontype_id'] ?? null);

		$bank_account_display = 'N/A';
		if (!empty($employee['bank_iban_number'])) {
			$bank_account_display = $employee['bank_iban_number'];
		} elseif (!empty($employee['bank_account_number'])) {
			$bank_account_display = $employee['bank_account_number'];
		}

		// Employee info
		$infoRows = [
			['Month:', $period, 'Job Title:', $job_title ?? 'N/A'],
			['Name:', $employee['name'] ?? 'N/A', 'Iqama #:', $employee['iqama_number'] ?? 'N/A'],
			['Bank:', $employee['bank_name'] ?? 'N/A', 'Bank A/C #:', $bank_account_display],
			['Project:', $project_name, '', ''],
		];

		foreach ($infoRows as $row) {
			// Default column widths
			$widths = [30, 65, 30, 65];

			// Special case: if 3rd and 4th columns are empty → merge into one big cell
			if ($row[2] === '' && $row[3] === '') {
				$widths = [30, 160]; // 30 for label, 160 for value
				$row = [$row[0], $row[1]]; // shrink to 2 cells
			}

			// Calculate max height
			$heights = [];
			for ($i = 0; $i < count($row); $i++) {
				$heights[] = $pdf->getNumLines($row[$i], $widths[$i]) * 6;
			}
			$rowHeight = max($heights);

			// Save X/Y
			$x = $pdf->GetX();
			$y = $pdf->GetY();

			for ($i = 0; $i < count($row); $i++) {
				$pdf->MultiCell(
					$widths[$i], $rowHeight, $row[$i], 1, 'L', 0, 0,
					$x, $y, true, 0, false, true, $rowHeight, 'M', true
				);
				$x += $widths[$i];
			}
			$pdf->Ln($rowHeight);
		}

		// Payslip info
		$pdf->Ln(5);
		$pdf->SetFont('dejavusans', 'B', 12);
		$pdf->Cell(0, 10, 'Payslip Information', 0, 1, 'C');
		$pdf->SetFont('dejavusans', '', 10);

		// Calculate gross salary from datatable (tblhrp_employees_value)
		$basic = floatval($employee_value['basic'] ?? 0);
		$ot_hours = floatval($employee_value['ot_hours'] ?? 0);
		$ot_rate  = floatval($employee_value['ot_rate'] ?? 0);
		$ot_amount = floatval($employee_value['ot_amount'] ?? 0);
		$gross_salary = $basic + $ot_amount;
		$allowance = floatval($employee_value['allowance'] ?? 0);
		$additions = floatval($employee_value['additions'] ?? 0);
		$deduction = floatval($employee_value['deduction'] ?? 0);

		// --- Get adjustments from tblhrp_adjustments for DISPLAY breakdown only ---
		// NOTE: allowance/deduction in tblhrp_employees_value are already SUMS of these adjustments
		$adjustments = $this->db->get_where('tblhrp_adjustments', [
			'staff_id' => $staff_id,
			'month'    => $db_month
		])->result_array();

		$adj_add_total = 0;
		$adj_ded_total = 0;
		$adj_add_rows  = [];
		$adj_ded_rows  = [];

		foreach ($adjustments as $adj) {
			if ($adj['type'] === 'addition') {
				$adj_add_total += floatval($adj['amount']);
				$adj_add_rows[] = [$adj['description'] ?? 'Addition', number_format($adj['amount'], 2) . ' SAR'];
			} elseif ($adj['type'] === 'deduction') {
				$adj_ded_total += floatval($adj['amount']);
				$adj_ded_rows[] = [$adj['description'] ?? 'Deduction', '-' . number_format($adj['amount'], 2) . ' SAR'];
			}
		}

		// Calculate net salary using real-time adjustments (NOT stored database values)
		$full_salary = $gross_salary + $allowance + $adj_add_total;
		$net_salary = $full_salary - $adj_ded_total;

		// Days in month for daily salary calculation
		$days_in_month = (int)$month_date->format('t');

		// Calculate working period and proration for payslip display
		$start_day = 1;
		$end_day = $days_in_month;
		$working_days = $days_in_month;
		$full_monthly_basic = $basic; // Default to current basic

		// Get staff pay to check for proration
		$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, $db_month);

		if ($staff_pay && isset($staff_pay->start_date)) {
			$start_date = $staff_pay->start_date;
			$month_start = date('Y-m-01', strtotime($db_month));
			$month_end = date('Y-m-t', strtotime($db_month));

			// If start_date is within this payroll month, calculate working period
			if (strtotime($start_date) >= strtotime($month_start) && strtotime($start_date) <= strtotime($month_end)) {
				$start_day = (int)date('j', strtotime($start_date));
				$working_days = $days_in_month - $start_day + 1;
				$full_monthly_basic = floatval($staff_pay->basic_pay ?? 0);
			} else {
				$full_monthly_basic = floatval($staff_pay->basic_pay ?? $basic);
			}
		}

		$daily_salary = $days_in_month > 0 ? $full_monthly_basic / $days_in_month : 0;

		// --- Build allowance breakdown for PDF ---
		$allowance_breakdown_items = [];
		$allowance_breakdown_total = 0;
		if ($staff_pay) {
			// Fixed allowances
			if (isset($staff_pay->food_allowance) && $staff_pay->food_allowance > 0) {
				$allowance_breakdown_items[] = ['Food Allowance', floatval($staff_pay->food_allowance)];
			}
			if (isset($staff_pay->allowance) && $staff_pay->allowance > 0) {
				$allowance_breakdown_items[] = ['General Allowance', floatval($staff_pay->allowance)];
			}
			if (isset($staff_pay->fat_allowance) && $staff_pay->fat_allowance > 0) {
				$allowance_breakdown_items[] = ['FAT Allowance', floatval($staff_pay->fat_allowance)];
			}
			if (isset($staff_pay->accomodation_allowance) && $staff_pay->accomodation_allowance > 0) {
				$allowance_breakdown_items[] = ['Accommodation Allowance', floatval($staff_pay->accomodation_allowance)];
			}
			if (isset($staff_pay->mewa) && $staff_pay->mewa > 0) {
				$allowance_breakdown_items[] = ['MEWA', floatval($staff_pay->mewa)];
			}

			// Get custom allowances
			$custom_allowances = $this->hr_payroll_model->get_employee_applicable_allowances($staff_id);
			if (!empty($custom_allowances) && isset($staff_pay->id)) {
				foreach ($custom_allowances as $ca) {
					$this->db->select('amount');
					$this->db->from(db_prefix() . 'staff_pay_allowances');
					$this->db->where('staff_pay_id', $staff_pay->id);
					$this->db->where('allowance_type_id', $ca['id']);
					$custom_amount = $this->db->get()->row();

					if ($custom_amount && $custom_amount->amount > 0) {
						$allowance_breakdown_items[] = [$ca['name'], floatval($custom_amount->amount)];
					} elseif (!empty($ca['default_amount']) && $ca['default_amount'] > 0) {
						$allowance_breakdown_items[] = [$ca['name'], floatval($ca['default_amount'])];
					}
				}
			}

			// Calculate breakdown total
			foreach ($allowance_breakdown_items as $item) {
				$allowance_breakdown_total += $item[1];
			}
		}

		// Convert net salary to words
		$amount_in_words = $this->number_to_words($net_salary) . ' Only';

		$rows = [
			['Basic', number_format($basic, 2) . ' SAR'],
			['', "Dates $start_day..$end_day ($working_days days) — Monthly " . number_format($full_monthly_basic, 2) . " SAR — Daily " . number_format($daily_salary, 2) . " SAR"],
			['Overtime', number_format($ot_amount, 2) . ' SAR ('. $working_days . ' days)'],
			['', "($ot_hours Hours x " . number_format($ot_rate, 2) . " SAR)"],
			['Gross Salary', number_format($gross_salary, 2) . ' SAR'],
		];

		// --- Allowance (show breakdown) ---
		if ($allowance > 0 || count($allowance_breakdown_items) > 0) {
			$rows[] = ['Allowance', '']; // section header
			if (count($allowance_breakdown_items) > 0) {
				foreach ($allowance_breakdown_items as $item) {
					$rows[] = ['   → ' . $item[0], number_format($item[1], 2) . ' SAR'];
				}
			}
			$rows[] = ['Total Allowance', number_format($allowance, 2) . ' SAR'];
		}

		// --- Show additions from adjustments table (with breakdown) ---
		if ($adj_add_total > 0 || (count($adj_add_rows) > 0)) {
			$rows[] = ['Additions', '']; // section header
			if (count($adj_add_rows) > 0) {
				foreach ($adj_add_rows as $ar) {
					$rows[] = ['   → ' . $ar[0], $ar[1]];
				}
			}
			$rows[] = ['Total Additions', number_format($adj_add_total, 2) . ' SAR'];
		}

		// Full salary row
		$rows[] = ['Full Salary', number_format($full_salary, 2) . ' SAR'];

		// --- Show breakdown of deductions from adjustments table ---
		if ($adj_ded_total > 0 && count($adj_ded_rows) > 0) {
			$rows[] = ['Deductions', '']; // section header
			foreach ($adj_ded_rows as $dr) {
				$rows[] = ['   → ' . $dr[0], $dr[1]];
			}
			$rows[] = ['Total Deductions', '-' . number_format($adj_ded_total, 2) . ' SAR'];
		}

		// Net salary (Grand Total)
		$rows[] = ['Net Salary', number_format($net_salary, 2) . ' SAR'];

		// Payment details section
		if ($is_paid && count($payments) > 0) {
			$rows[] = ['Payments Made', '']; // section header
			foreach ($payments as $pmt) {
				$paid_by = trim($pmt['firstname'] . ' ' . $pmt['lastname']);
				if (empty($paid_by)) {
					$paid_by = 'N/A';
				}
				$payment_date = date('d-M-Y', strtotime($pmt['paid_date']));
				$payment_amount = number_format($pmt['amount'], 2) . ' SAR';
				$label = '   Paid on ' . $payment_date . ' by ' . $paid_by;
				$rows[] = [$label, $payment_amount, 'PAID', true];
			}
			$rows[] = ['Total Paid', number_format($paid_amount, 2) . ' SAR'];
		}

		// Balance to be paid
		$balance = max(0, $net_salary - $paid_amount);
		if ($balance > 0) {
			$rows[] = ['Balance To Be Paid', number_format($balance, 2) . ' SAR', 'UNPAID', true];
		} elseif ($is_paid) {
			$rows[] = ['Balance To Be Paid', '0.00 SAR (Fully Paid)', 'PAID', true];
		}

		// Amount in words
		$rows[] = ['Amount in words', $amount_in_words];

		foreach ($rows as $row) {
			$col1Width = 70;
			$col2Width = 120;

			if (isset($row[3]) && $row[3] === true) { // Row with badge (PAID or UNPAID)
				$rowHeight = 8;
				$x = $pdf->GetX();
				$y = $pdf->GetY();

				// Label
				$pdf->SetFont('dejavusans', 'B', 10);
				$pdf->MultiCell($col1Width, $rowHeight, $row[0], 0, 'L', 0, 0, $x, $y, true);

				// Value (amount + badge inline)
				$pdf->SetFont('dejavusans', '', 10);

				// Amount
				$amountWidth = 50;
				$pdf->MultiCell($amountWidth, $rowHeight, $row[1], 0, 'L', 0, 0, $x + $col1Width, $y, true);

				// Badge (PAID or UNPAID)
				$badgeText = isset($row[2]) ? $row[2] : 'PAID';
				$badgeWidth  = ($badgeText === 'UNPAID') ? 15 : 10;
				$badgeHeight = 5;

				// Set color based on badge type
				if ($badgeText === 'PAID') {
					$pdf->SetFillColor(144, 238, 144); // light green
				} else {
					$pdf->SetFillColor(255, 200, 200); // light red
				}

				$pdf->Rect($x + $col1Width + $amountWidth + 2, $y, $badgeWidth, $badgeHeight, 'F');
				$pdf->SetFont('dejavusans', 'B', 8);
				$pdf->SetXY($x + $col1Width + $amountWidth + 2, $y);
				$pdf->Cell($badgeWidth, $badgeHeight, $badgeText, 0, 0, 'C');

				$pdf->Ln($rowHeight);
			} else {
				// Regular rows
				$rowHeight = max(
					$pdf->getNumLines($row[0], $col1Width) * 6,
					$pdf->getNumLines(strip_tags($row[1]), $col2Width) * 6
				);
				$x = $pdf->GetX();
				$y = $pdf->GetY();

				$pdf->SetFont('dejavusans', 'B', 10);
				$pdf->MultiCell($col1Width, $rowHeight, $row[0], 0, 'L', 0, 0, $x, $y, true);
				$pdf->SetFont('dejavusans', '', 10);
				$pdf->MultiCell($col2Width, $rowHeight, $row[1], 0, 'L', 0, 1, $x + $col1Width, $y, true);
			}
		}

		// Footer
		$pdf->Ln(15);
		$pdf->SetFont('dejavusans', 'B', 10);

		$footer = ['Employee Name', 'Prepared By', 'Approved By'];
		$widths = [63, 63, 64];

		$x = $pdf->GetX();
		$y = $pdf->GetY();
		foreach ($footer as $i => $txt) {
			$pdf->MultiCell($widths[$i], 7, $txt, 1, 'C', 0, 0, $x, $y, true, 0, false, true, 7, 'M', true);
			$x += $widths[$i];
		}
		$pdf->Ln(7);

		$footerVals = [$employee['name'] ?? 'N/A', '', ''];
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		foreach ($footerVals as $i => $txt) {
			$pdf->MultiCell($widths[$i], 15, $txt, 1, 'C', 0, 0, $x, $y, true, 0, false, true, 15, 'M', true);
			$x += $widths[$i];
		}
		$pdf->Ln(15);

		// Prepare filename
		$emp_code  = $employee['code'] ?? 'EMPCODE';
		$emp_name  = preg_replace('/\s+/', '', $employee['name'] ?? 'N/A'); // remove spaces from name
		$iqama_no  = $employee['iqama_number'] ?? 'IQAMA';
		$month_str = $month_date->format('FY'); // e.g. September2025

		$filename = "{$emp_code}_{$emp_name}_{$iqama_no}_Payslip_{$month_str}.pdf";

		if ($return) {
			// return as string (no output to browser)
			return [
				'pdf'      => $pdf->Output($filename, 'S'),
				'filename' => $filename,
			];
		} else {
			// output directly to browser
			$pdf->Output($filename, "I");
		}
	}

	private function number_to_words($number)
	{
		if (!is_numeric($number)) {
			return 'invalid';
		}

		if ($number < 0) {
			return 'negative ' . $this->number_to_words(abs($number));
		}

		// Split into integer and decimal parts
		$parts = explode('.', number_format($number, 2, '.', ''));
		$riyal_part = (int)$parts[0];
		$halala_part = isset($parts[1]) ? (int)$parts[1] : 0;

		// Convert riyal part
		$riyal_words = $this->convert_number_to_words($riyal_part);
		$result = ucfirst($riyal_words) . ' Riyal';
		if ($riyal_part != 1) {
			$result .= 's';
		}

		// Add halala part if present
		if ($halala_part > 0) {
			$halala_words = $this->convert_number_to_words($halala_part);
			$result .= ' and ' . ucfirst($halala_words) . ' Halala';
			if ($halala_part != 1) {
				$result .= 's';
			}
		}

		return $result;
	}

	private function convert_number_to_words($number)
	{
		$hyphen = '-';
		$conjunction = ' and ';
		$separator = ', ';
		$dictionary = [
			0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
			6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
			11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
			16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
			20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty',
			70 => 'seventy', 80 => 'eighty', 90 => 'ninety', 100 => 'hundred',
			1000 => 'thousand', 1000000 => 'million'
		];

		if ($number < 21) {
			return $dictionary[$number];
		}

		if ($number < 100) {
			$tens = ((int)($number / 10)) * 10;
			$units = $number % 10;
			$string = $dictionary[$tens];
			if ($units) {
				$string .= $hyphen . $dictionary[$units];
			}
			return $string;
		}

		if ($number < 1000) {
			$hundreds = (int)($number / 100);
			$remainder = $number % 100;
			$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
			if ($remainder) {
				$string .= $conjunction . $this->convert_number_to_words($remainder);
			}
			return $string;
		}

		if ($number < 1000000) {
			$thousands = (int)($number / 1000);
			$remainder = $number % 1000;
			$string = $this->convert_number_to_words($thousands) . ' ' . $dictionary[1000];
			if ($remainder) {
				$string .= $separator . $this->convert_number_to_words($remainder);
			}
			return $string;
		}

		$millions = (int)($number / 1000000);
		$remainder = $number % 1000000;
		$string = $this->convert_number_to_words($millions) . ' ' . $dictionary[1000000];
		if ($remainder) {
			$string .= $separator . $this->convert_number_to_words($remainder);
		}
		return $string;
	}

	public function get_preview($staff_id, $month)
	{
		// normalize month to YYYY-MM-01
		$month_date = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month = $month_date->format('Y-m-01');

		// fetch staff + payroll row
		$staff = $this->db->get_where('tblstaff', ['staffid' => $staff_id])->row_array();

		if (!$staff) {
			echo json_encode(['status' => 'error', 'message' => 'Staff not found']); exit;
		}

		// Try to get payroll data for the month
		$val = $this->db->get_where('tblhrp_employees_value', [
			'staff_id' => $staff_id,
			'month'    => $db_month
		])->row_array();

		// If no payroll data exists, get defaults from tblstaffpay
		if (!$val) {
			// Load model if not loaded
			$this->load->model('hr_payroll/hr_payroll_model');

			// Get staff pay data for the month
			$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, $db_month);

			// Get project additions/deductions
			$project_additions = $this->hr_payroll_model->get_project_additions_for_month($staff_id, $db_month);
			$project_deductions = $this->hr_payroll_model->get_project_deductions_for_month($staff_id, $db_month);

			if ($staff_pay) {
				$basic_pay = floatval($staff_pay->basic_pay ?? 0);
				$overtime_rate = floatval($staff_pay->overtime_pay ?? 0);
				$food_allowance = floatval($staff_pay->food_allowance ?? 0);
				$allowance = floatval($staff_pay->allowance ?? 0);
				$fat_allowance = floatval($staff_pay->fat_allowance ?? 0);
				$accomodation_allowance = floatval($staff_pay->accomodation_allowance ?? 0);
				$mewa = floatval($staff_pay->mewa ?? 0);

				// Calculate total allowances and deductions
				$total_allowance = $allowance + $food_allowance + $accomodation_allowance + $fat_allowance + $project_additions;
				$total_deduction = $mewa + $project_deductions;

				$val = [
					'staff_id' => $staff_id,
					'month' => $db_month,
					'basic' => $basic_pay,
					'ot_hours' => 0,
					'ot_rate' => $overtime_rate,
					'ot_amount' => 0,
					'allowance' => $total_allowance,
					'deduction' => $total_deduction,
				];
			} else {
				// Ultimate fallback: no pay data at all
				$val = [
					'staff_id' => $staff_id,
					'month' => $db_month,
					'basic' => 0,
					'ot_hours' => 0,
					'ot_rate' => 0,
					'ot_amount' => 0,
					'allowance' => $project_additions,
					'deduction' => $project_deductions,
				];
			}
		}

		// --- Adjustments (list + sums) from tblhrp_adjustments ---
		// NOTE: The allowance/deduction in tblhrp_employees_value are SUMS of these adjustments
		// So we only use them for DISPLAY breakdown, not for calculation (to avoid double-counting)
		$adjustments = $this->db->where(['staff_id' => $staff_id, 'month' => $db_month])
			->get('tblhrp_adjustments')->result_array();

		$sumAdd = 0; $sumDed = 0;
		$addRows = ''; $dedRows = '';

		foreach ($adjustments as $adj) {
			if ($adj['type'] === 'addition') {
				$sumAdd += (float)$adj['amount'];
				$addRows .= '<tr><td>→ '.htmlspecialchars($adj['description'] ?: 'Addition').'</td><td>'.number_format($adj['amount'],2).' SAR</td></tr>';
			}
			if ($adj['type'] === 'deduction') {
				$sumDed += (float)$adj['amount'];
				$dedRows .= '<tr><td>→ '.htmlspecialchars($adj['description'] ?: 'Deduction').'</td><td>-'.number_format($adj['amount'],2).' SAR</td></tr>';
			}
		}

		// --- Payments --- Fetch all with staff details
		$this->db->select('p.*, s.firstname, s.lastname');
		$this->db->from(db_prefix() . 'hrp_payments as p');
		$this->db->join(db_prefix() . 'staff as s', 's.staffid = p.created_by', 'left');
		$this->db->where('p.staff_id', $staff_id);
		$this->db->where('p.month', $db_month);
		$this->db->where('p.status', 'paid');
		$this->db->order_by('p.paid_date', 'ASC');
		$payments = $this->db->get()->result_array();

		$total_paid = 0;
		foreach ($payments as $p) {
			$total_paid += floatval($p['amount']);
		}

		$is_paid = count($payments) > 0;

		// --- Basic / OT / Gross / Net ---
		// $basic = (float)($val['basic'] ?? 0);
		// $ot_amount = (float)($val['ot_amount'] ?? 0);
		// $days_in_month = (int)date('t', strtotime($db_month));
		// $daily = $days_in_month ? $basic / $days_in_month : 0;
		// $gross = $basic + $ot_amount;
		// $net = $gross + $sumAdd - $sumDed;

		$basic    = floatval($val['basic'] ?? 0);
		$ot_hours = floatval($val['ot_hours'] ?? 0);
		$ot_rate  = floatval($val['ot_rate'] ?? 0);

		// prefer stored ot_amount but recalc if not present
		$ot_amount = isset($val['ot_amount']) && $val['ot_amount'] !== ''
			? floatval($val['ot_amount'])
			: ($ot_hours * $ot_rate);

		$allowance = floatval($val['allowance'] ?? 0);
		$additions = floatval($val['additions'] ?? 0);
		$deduction = floatval($val['deduction'] ?? 0);

		$days_in_month = (int) date('t', strtotime($db_month));

		// Calculate working period and daily rate based on proration
		$start_day = 1;
		$end_day = $days_in_month;
		$working_days = $days_in_month;
		$full_monthly_basic = $basic; // Default to current basic
		$daily = 0;

		// Get staff pay to check for proration
		if (!isset($staff_pay)) {
			$this->load->model('hr_payroll/hr_payroll_model');
			$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, $db_month);
		}

		// Debug logging
		log_activity("Payslip Preview Debug - Staff: $staff_id, Month: $db_month, Basic: $basic");
		if ($staff_pay) {
			log_activity("Payslip Preview Debug - Staff Pay Found: start_date=" . ($staff_pay->start_date ?? 'NULL') . ", basic_pay=" . ($staff_pay->basic_pay ?? 'NULL'));
		} else {
			log_activity("Payslip Preview Debug - No Staff Pay Found");
		}

		if ($staff_pay && isset($staff_pay->start_date)) {
			$start_date = $staff_pay->start_date;
			$month_start = date('Y-m-01', strtotime($db_month));
			$month_end = date('Y-m-t', strtotime($db_month));

			log_activity("Payslip Preview Debug - Date Check: start_date=$start_date, month_start=$month_start, month_end=$month_end");

			// If start_date is within this payroll month, calculate working period
			if (strtotime($start_date) >= strtotime($month_start) && strtotime($start_date) <= strtotime($month_end)) {
				$start_day = (int)date('j', strtotime($start_date));
				$working_days = $days_in_month - $start_day + 1;

				// Get full monthly basic (before proration)
				$full_monthly_basic = floatval($staff_pay->basic_pay ?? 0);
				$daily = $days_in_month ? $full_monthly_basic / $days_in_month : 0;

				log_activity("Payslip Preview Debug - PRORATED: start_day=$start_day, working_days=$working_days, full_monthly=$full_monthly_basic, daily=$daily");
			} else {
				// Full month - get full monthly basic
				$full_monthly_basic = floatval($staff_pay->basic_pay ?? $basic);
				$daily = $days_in_month ? $full_monthly_basic / $days_in_month : 0;

				log_activity("Payslip Preview Debug - FULL MONTH: full_monthly=$full_monthly_basic, daily=$daily");
			}
		} else {
			$daily = $days_in_month ? $basic / $days_in_month : 0;
			log_activity("Payslip Preview Debug - FALLBACK: Using basic for daily calculation, daily=$daily");
		}

		$gross = $basic + $ot_amount;                           // Basic + OT
		$full_salary = $gross + $allowance + $sumAdd;           // Add allowance and additions from adjustments table (real-time)
		$net = $full_salary - $sumDed;                          // Subtract deductions from adjustments table (real-time)

		// --- Get allowance breakdown ---
		$allowance_rows = '';
		$allowance_breakdown_total = 0;
		if ($staff_pay) {
			$breakdown_items = [];

			// Fixed allowances
			if (isset($staff_pay->food_allowance) && $staff_pay->food_allowance > 0) {
				$breakdown_items[] = ['Food Allowance', floatval($staff_pay->food_allowance)];
			}
			if (isset($staff_pay->allowance) && $staff_pay->allowance > 0) {
				$breakdown_items[] = ['General Allowance', floatval($staff_pay->allowance)];
			}
			if (isset($staff_pay->fat_allowance) && $staff_pay->fat_allowance > 0) {
				$breakdown_items[] = ['FAT Allowance', floatval($staff_pay->fat_allowance)];
			}
			if (isset($staff_pay->accomodation_allowance) && $staff_pay->accomodation_allowance > 0) {
				$breakdown_items[] = ['Accommodation Allowance', floatval($staff_pay->accomodation_allowance)];
			}
			if (isset($staff_pay->mewa) && $staff_pay->mewa > 0) {
				$breakdown_items[] = ['MEWA', floatval($staff_pay->mewa)];
			}

			// Get custom allowances
			$custom_allowances = $this->hr_payroll_model->get_employee_applicable_allowances($staff_id);
			if (!empty($custom_allowances) && isset($staff_pay->id)) {
				foreach ($custom_allowances as $ca) {
					$this->db->select('amount');
					$this->db->from(db_prefix() . 'staff_pay_allowances');
					$this->db->where('staff_pay_id', $staff_pay->id);
					$this->db->where('allowance_type_id', $ca['id']);
					$custom_amount = $this->db->get()->row();

					if ($custom_amount && $custom_amount->amount > 0) {
						$breakdown_items[] = [$ca['name'], floatval($custom_amount->amount)];
					} elseif (!empty($ca['default_amount']) && $ca['default_amount'] > 0) {
						$breakdown_items[] = [$ca['name'], floatval($ca['default_amount'])];
					}
				}
			}

			// Build HTML rows
			foreach ($breakdown_items as $item) {
				$allowance_rows .= '<tr><td>→ ' . htmlspecialchars($item[0]) . '</td><td>' . number_format($item[1], 2) . ' SAR</td></tr>';
				$allowance_breakdown_total += $item[1];
			}
		}

		// --- Build HTML preview ---
		ob_start(); ?>
		<div class="payslip-preview">
			<h5><?=htmlspecialchars($staff['name'] ?? ($staff['firstname'].' '.$staff['lastname']))?> — <?=date('F Y', strtotime($db_month))?></h5>
			<table class="table table-sm">
				<tr><td><strong>Basic</strong></td><td><?=number_format($basic,2)?> SAR</td></tr>
				<tr><td colspan="2">Dates <?= $start_day ?>..<?= $end_day ?> (<?= $working_days ?> days) — Monthly <?= number_format($full_monthly_basic,2) ?> SAR — Daily <?= number_format($daily,2) ?> SAR</td></tr>

				<tr><td><strong>Overtime</strong></td><td><?=number_format($ot_amount,2)?> SAR</td></tr>
				<tr><td colspan="2">(<?=number_format($ot_hours,2)?> Hours x <?=number_format($ot_rate,2)?> SAR)</td></tr>

				<tr><td><strong>Gross Salary (Basic + OT)</strong></td><td><?=number_format($gross,2)?> SAR</td></tr>

				<?php if ($allowance > 0 || $allowance_rows): ?>
					<tr class="table-primary"><td colspan="2"><strong>Allowance</strong></td></tr>
					<?php if ($allowance_rows): ?>
						<?= $allowance_rows ?>
					<?php endif; ?>
					<tr><td><strong>Total Allowance</strong></td><td><?=number_format($allowance,2)?> SAR</td></tr>
				<?php endif; ?>

				<?php if ($sumAdd > 0 || $addRows): ?>
					<tr class="table-success"><td colspan="2"><strong>Additions</strong></td></tr>
					<?php if ($addRows): ?>
						<?= $addRows ?>
					<?php endif; ?>
					<tr><td><strong>Total Additions</strong></td><td><?=number_format($sumAdd,2)?> SAR</td></tr>
				<?php endif; ?>

				<tr><td><strong>Full Salary</strong></td><td><?=number_format($full_salary,2)?> SAR</td></tr>

				<?php if ($dedRows): ?>
					<tr class="table-danger"><td colspan="2"><strong>Deductions</strong></td></tr>
					<?= $dedRows ?>
					<tr><td><strong>Total Deductions</strong></td><td>-<?=number_format($sumDed,2)?> SAR</td></tr>
				<?php endif; ?>

				<tr style="background:#f5f5f5"><td><strong>Grand Total</strong></td><td><strong><?=number_format($net,2)?> SAR</strong></td></tr>

				<?php if ($is_paid && count($payments) > 0): ?>
					<tr class="table-info"><td colspan="2"><strong>Payments Made</strong></td></tr>
					<?php foreach ($payments as $pmt): ?>
						<?php
						$paid_by = trim($pmt['firstname'] . ' ' . $pmt['lastname']);
						if (empty($paid_by)) $paid_by = 'N/A';
						$payment_date = date('d-M-Y', strtotime($pmt['paid_date']));
						?>
						<tr>
							<td>→ Paid on <?= $payment_date ?> by <?= htmlspecialchars($paid_by) ?></td>
							<td>
								<?= number_format($pmt['amount'], 2) ?> SAR
								<span class="badge" style="background-color:#d4edda;color:#155724;border:1px solid #c3e6cb;">PAID</span>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr><td><strong>Total Paid</strong></td><td><strong><?=number_format($total_paid,2)?> SAR</strong></td></tr>
				<?php endif; ?>

				<?php
				$balance = max(0, $net - $total_paid);
				?>
				<tr>
					<td><strong>Balance To Be Paid</strong></td>
					<td>
						<strong><?=number_format($balance,2)?> SAR</strong>
						<?php if ($balance == 0 && $is_paid): ?>
							<span class="badge" style="background-color:#d4edda;color:#155724;border:1px solid #c3e6cb;">Fully Paid</span>
						<?php elseif ($balance > 0): ?>
							<span class="badge" style="background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb;">UNPAID</span>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<div class="text-right">
				<a href="<?= admin_url("hr_payroll/generate_payslip/{$staff_id}/{$month}") ?>" target="_blank" class="btn btn-primary">Download Payslip</a>
				<button class="btn btn-secondary" id="openAdjustmentModalPreview" data-staff="<?=$staff_id?>" data-month="<?=$month?>">Add / Deduct</button>
				<button class="btn btn-success" id="openPaymentModalPreview" data-staff="<?=$staff_id?>" data-month="<?=$month?>">Make Payment</button>
				<button class="btn btn-info" id="sendPayslipEmail" data-staff="<?=$staff_id?>" data-month="<?=$month?>">Email Payslip</button>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		echo json_encode(['status'=>'ok','html'=>$html]); exit;
	}

	public function add_adjustment()
	{
		$this->load->helper('url');
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month'); // expect YYYY-MM or YYYY-MM-01
		$type = $this->input->post('type'); // addition / deduction
		$date = $this->input->post('date');
		$project_id = $this->input->post('project_id') ?: NULL;
		$description = $this->input->post('description');
		$amount = (float)$this->input->post('amount');

		// normalize month
		$monthDate = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month = $monthDate->format('Y-m-01');

		$ins = [
			'staff_id'=>$staff_id,
			'month'=>$db_month,
			'type'=>$type,
			'date'=>$date,
			'project_id'=>$project_id,
			'description'=>$description,
			'amount'=>$amount,
			'created_by'=>get_staff_user_id()
		];
		$this->db->insert('tblhrp_adjustments', $ins);
		echo json_encode(['status'=>'ok','id'=>$this->db->insert_id(), 'csrf_hash' => $this->security->get_csrf_hash()]);
	}

	public function make_payment()
	{
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month');
		$method = $this->input->post('method'); // bank | cash
		$paid_date = $this->input->post('paid_date');
		$amount = (float)$this->input->post('amount');
		$reference = $this->input->post('reference');

		$monthDate = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month = $monthDate->format('Y-m-01');

		$ins = [
			'staff_id'=>$staff_id,
			'month'=>$db_month,
			'amount'=>$amount,
			'method'=>$method,
			'paid_date'=>$paid_date,
			'reference'=>$reference,
			'status'=>'paid',
			'created_by'=>get_staff_user_id()
		];
		$this->db->insert('tblhrp_payments', $ins);
		echo json_encode(['status'=>'ok','payment_id'=>$this->db->insert_id(), 'csrf_hash' => $this->security->get_csrf_hash()]);
	}
	
	public function edit_adjustment()
	{
		$this->load->helper('url');
		$id = $this->input->post('id');
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month');
		$type = $this->input->post('type'); // addition / deduction
		$date = $this->input->post('date');
		$project_id = $this->input->post('project_id') ?: NULL;
		$description = $this->input->post('description');
		$amount = (float)$this->input->post('amount');

		// Validate inputs
		if (!$id || !$staff_id || !$month || !$type || !$date || !$amount) {
			echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
			return;
		}

		// Normalize month
		$monthDate = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month = $monthDate->format('Y-m-01');

		// Check if adjustment exists and belongs to staff/month
		$adjustment = $this->db->get_where('tblhrp_adjustments', [
			'id' => $id,
			'staff_id' => $staff_id,
			'month' => $db_month
		])->row_array();

		if (!$adjustment) {
			echo json_encode(['status' => 'error', 'message' => 'Adjustment not found']);
			return;
		}

		// Update adjustment
		$update = [
			'type' => $type,
			'date' => $date,
			'project_id' => $project_id,
			'description' => $description,
			'amount' => $amount,
			'created_by' => get_staff_user_id()
		];
		$this->db->where('id', $id);
		$this->db->update('tblhrp_adjustments', $update);

		if ($this->db->affected_rows() > 0) {
			echo json_encode(['status' => 'ok', 'message' => 'Adjustment updated', 'csrf_hash' => $this->security->get_csrf_hash()]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'No changes made']);
		}
	}

	public function delete_adjustment()
	{
		$this->load->helper('url');
		$id = $this->input->post('id');
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month');

		// Validate inputs
		if (!$id || !$staff_id || !$month) {
			echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
			return;
		}

		// Normalize month
		$monthDate = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month = $monthDate->format('Y-m-01');

		// Check if adjustment exists
		$adjustment = $this->db->get_where('tblhrp_adjustments', [
			'id' => $id,
			'staff_id' => $staff_id,
			'month' => $db_month
		])->row_array();

		if (!$adjustment) {
			echo json_encode(['status' => 'error', 'message' => 'Adjustment not found']);
			return;
		}

		// Delete adjustment
		$this->db->where('id', $id);
		$this->db->delete('tblhrp_adjustments');

		if ($this->db->affected_rows() > 0) {
			echo json_encode(['status' => 'ok', 'message' => 'Adjustment deleted', 'csrf_hash' => $this->security->get_csrf_hash()]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to delete adjustment']);
		}
	}

	public function get_staff_projects($staff_id)
    {
        $rows = $this->db->select('p.id, p.name')
            ->from('tblprojectassignee pa')
            ->join('tblprojects p', 'p.id = pa.project_id')
            ->where('pa.staff_id', $staff_id)
            ->get()->result_array();
        echo json_encode($rows);
    }

	public function send_payslip_email()
	{
		$staff_id = $this->input->post('staff_id');
		$month    = $this->input->post('month');

		if (!$staff_id || !$month) {
			echo json_encode(['status' => 'error', 'message' => 'Invalid request']); exit;
		}

		// Normalize month
		$month_date = DateTime::createFromFormat('Y-m', $month) ?: new DateTime($month);
		$db_month   = $month_date->format('Y-m-01');

		// Fetch staff
		$staff = $this->db->get_where('tblstaff', ['staffid' => $staff_id])->row();
		if (!$staff || empty($staff->email)) {
			echo json_encode(['status' => 'error', 'message' => 'Staff email not found']); exit;
		}

		// Generate PDF (reuse your generate_payslip function)
		$this->load->model('hr_payroll_model');
		// Generate PDF
		$result = $this->generate_payslip($staff_id, $month, true);
		$pdf      = $result['pdf'];
		$filename = $result['filename'];

		// Save temp file
		$filepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
		file_put_contents($filepath, $pdf);

		// Send email
		$this->load->library('email');
		$this->email->from(get_option('smtp_email'), get_option('companyname'));
		$this->email->to($staff->email);
		$this->email->subject('Your Payslip for '.date('F Y', strtotime($db_month)));
		$this->email->message(
			'Dear '.$staff->name.',<br><br>'.
			'Please find attached your payslip for '.date('F Y', strtotime($db_month)).
			'.<br><br>Regards,<br>'.get_option('companyname')
		);
		$this->email->attach($filepath);

		$result = $this->email->send();

		if ($result) {
			echo json_encode(['status'=>'ok']);
		} else {
			echo json_encode([
				'status'=>'error',
				'message'=>$this->email->print_debugger(['headers'])
			]);
		}

		// Cleanup
		@unlink($filepath);
		exit;
	}

	/**
	 * ========================================
	 * PAYROLL GENERATION SYSTEM METHODS
	 * ========================================
	 */

	/**
	 * Payroll List - Display all payrolls with filters
	 * @return view
	 */
	public function payroll_list()
	{
		if (!has_permission('hrp_employee', '', 'view') && !is_admin()) {
			access_denied('hrp_employee');
		}

		$data['title'] = _l('hr_payroll_list');

		// Get filters for dropdowns
		$data['statuses'] = [
			'draft' => _l('hr_payroll_status_draft'),
			'ready_for_review' => _l('hr_payroll_status_ready_for_review'),
			'awaiting_approval' => _l('hr_payroll_status_awaiting_approval'),
			'submitted' => _l('hr_payroll_status_submitted'),
			'completed' => _l('hr_payroll_status_completed'),
			'cancelled' => _l('hr_payroll_status_cancelled'),
		];

		// Load companies from database
		$this->db->select('id, name');
		$this->db->from(db_prefix() . 'companytype');
		$this->db->where_in('id', [1, 2]); // Only Mahiroon and Mohtarifeen
		$this->db->order_by('name', 'ASC');
		$company_types = $this->db->get()->result_array();

		$data['companies'] = [];
		foreach ($company_types as $company) {
			$data['companies'][$company['id']] = $company['name'];
		}

		$data['employee_types'] = get_all_ownemployee_types();

		$this->load->view('payroll/payroll_list', $data);
	}

	/**
	 * Get payrolls data for DataTable (AJAX)
	 * @return json
	 */
	public function get_payrolls_table()
	{
		if (!has_permission('hrp_employee', '', 'view') && !is_admin()) {
			ajax_access_denied();
		}

		// Get filters from request
		$filters = [];
		if ($this->input->post('status')) {
			$filters['status'] = $this->input->post('status');
		}
		if ($this->input->post('company_filter')) {
			$filters['company_filter'] = $this->input->post('company_filter');
		}
		if ($this->input->post('ownemployee_type_id')) {
			$filters['ownemployee_type_id'] = $this->input->post('ownemployee_type_id');
		}
		if ($this->input->post('month_from')) {
			$filters['month_from'] = $this->input->post('month_from');
		}
		if ($this->input->post('month_to')) {
			$filters['month_to'] = $this->input->post('month_to');
		}

		$payrolls = $this->hr_payroll_model->get_payrolls($filters);

		// Get total count (without filters) for recordsTotal
		$total_payrolls = $this->hr_payroll_model->get_payrolls([]);

		$output = [];
		foreach ($payrolls as $payroll) {
			$row = [];

			// Payroll Number
			$row[] = '<a href="' . admin_url('hr_payroll/manage_employees?payroll_id=' . $payroll['id']) . '" class="font-weight-bold">' . $payroll['payroll_number'] . '</a>';

			// Month
			$row[] = date('F Y', strtotime($payroll['month']));

			// Company - handle both old string format and new ID format
			if (!empty($payroll['company_name'])) {
				$company_label = $payroll['company_name'];
			} elseif (!empty($payroll['company_filter'])) {
				// Fallback for old string format
				$company_label = ucfirst($payroll['company_filter']);
			} else {
				$company_label = 'All';
			}
			$row[] = $company_label;

			// Employee Type
			$row[] = $payroll['ownemployee_type_name'] ?? '-';

			// Total Employees
			$row[] = $payroll['total_employees'];

			// Total Amount
			$row[] = number_format($payroll['total_amount'], 2);

			// Status badge
			$status_colors = [
				'draft' => 'secondary',
				'ready_for_review' => 'info',
				'awaiting_approval' => 'warning',
				'submitted' => 'primary',
				'completed' => 'success',
				'cancelled' => 'danger',
			];
			$color = $status_colors[$payroll['status']] ?? 'secondary';
			$status_text = str_replace('_', ' ', ucwords($payroll['status'], '_'));
			$row[] = '<span class="badge badge-' . $color . '">' . $status_text . '</span>';

			// Created By & Date
			$row[] = $payroll['created_by_name'] . '<br><small class="text-muted">' . date('Y-m-d H:i', strtotime($payroll['created_date'])) . '</small>';

			// Actions
			$actions = '';
			$actions .= '<a href="' . admin_url('hr_payroll/manage_employees?payroll_id=' . $payroll['id']) . '" class="btn btn-sm btn-default" title="' . _l('view') . '"><i class="fa fa-eye"></i></a> ';

			if (($payroll['status'] == 'draft' || $payroll['status'] == 'cancelled') && (has_permission('hrp_employee', '', 'delete') || is_admin())) {
				$actions .= '<a href="#" onclick="delete_payroll(' . $payroll['id'] . '); return false;" class="btn btn-sm btn-danger" title="' . _l('delete') . '"><i class="fa fa-trash"></i></a>';
			}

			$row[] = $actions;

			$output[] = $row;
		}

		// DataTables server-side processing expects these fields
		echo json_encode([
			'draw' => intval($this->input->post('draw')),
			'recordsTotal' => count($total_payrolls),
			'recordsFiltered' => count($payrolls),
			'data' => $output
		]);
	}

	/**
	 * Generate new payroll (AJAX or Form)
	 * @return json/redirect
	 */
	public function generate_payroll()
	{
		if (!has_permission('hrp_employee', '', 'create') && !is_admin()) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => false, 'message' => _l('access_denied')]);
				return;
			}
			access_denied('hrp_employee');
		}

		$month = $this->input->post('month');
		$company_filter = $this->input->post('company_filter');
		$ownemployee_type_id = $this->input->post('ownemployee_type_id');

		// Validate inputs
		if (empty($month) || empty($ownemployee_type_id)) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_missing_required_fields')
			]);
			return;
		}

		// Format month to YYYY-MM-01
		$month_formatted = date('Y-m-01', strtotime($month . '-01'));

		$rel_type = hrp_get_hr_profile_status();

		// Create payroll header
		$payroll_data = [
			'month' => $month_formatted,
			'company_filter' => $company_filter,
			'ownemployee_type_id' => $ownemployee_type_id,
			'created_by' => get_staff_user_id(),
			'rel_type' => $rel_type,
		];

		$payroll_id = $this->hr_payroll_model->create_payroll($payroll_data);

		if ($payroll_id === false) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_already_exists')
			]);
			return;
		}

		// Log payroll generation attempt
		log_activity('Generating payroll for month=' . $month_formatted . ', company=' . $company_filter . ', employee_type=' . $ownemployee_type_id);

		// Generate employees for this payroll
		$employees_count = $this->hr_payroll_model->generate_payroll_employees(
			$payroll_id,
			$month_formatted,
			$company_filter,
			$ownemployee_type_id,
			$rel_type
		);

		if ($employees_count > 0) {
			log_activity('Payroll generated successfully: payroll_id=' . $payroll_id . ', employees=' . $employees_count);

			echo json_encode([
				'success' => true,
				'message' => sprintf(_l('hr_payroll_generated_successfully'), $employees_count),
				'payroll_id' => $payroll_id,
				'employees_count' => $employees_count,
				'redirect' => admin_url('hr_payroll/manage_employees?payroll_id=' . $payroll_id)
			]);
		} else {
			log_activity('Payroll generation failed - no employees found. Deleting payroll_id=' . $payroll_id);

			// Delete payroll if no employees found
			$this->hr_payroll_model->delete_payroll($payroll_id);

			echo json_encode([
				'success' => false,
				'message' => 'No eligible employees found for the selected criteria. Please check: 1) Employee Type exists and has active employees, 2) Employees have valid staffpay records, 3) Company filter matches employee records. Check activity log for details.'
			]);
		}
	}

	/**
	 * Change payroll status
	 * @param int $payroll_id
	 * @param string $new_status
	 * @return json
	 */
	public function change_payroll_status($payroll_id = null, $new_status = null)
	{
		// Handle both POST and URL parameters
		if (!$payroll_id) {
			$payroll_id = $this->input->post('payroll_id');
		}
		if (!$new_status) {
			$new_status = $this->input->post('new_status');
		}

		if (!$payroll_id || !$new_status) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_missing_parameters')
			]);
			return;
		}

		// Get payroll
		$payroll = $this->hr_payroll_model->get_payroll($payroll_id);

		if (!$payroll) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_not_found')
			]);
			return;
		}

		// Validate state transition
		if (!$this->hr_payroll_model->validate_status_transition($payroll->status, $new_status)) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_invalid_status_transition')
			]);
			return;
		}

		// Permission check based on transition
		$permission_check = true;
		$update_field = '';

		switch ($new_status) {
			case 'ready_for_review':
				$permission_check = has_permission('hrp_employee', '', 'edit') || is_admin();
				$update_field = 'reviewed';
				break;
			case 'awaiting_approval':
				// Assuming HR Manager has 'approve' permission
				$permission_check = has_permission('hrp_employee', '', 'edit') || is_admin();
				$update_field = 'approved';
				break;
			case 'submitted':
				// Assuming Finance/Admin has 'edit' permission or is admin
				$permission_check = has_permission('hrp_employee', '', 'edit') || is_admin();
				$update_field = 'submitted';
				break;
			case 'completed':
				$permission_check = is_admin();
				$update_field = 'completed';
				break;
			case 'draft':
				// Allow going back to draft from review
				$permission_check = has_permission('hrp_employee', '', 'edit') || is_admin();
				$update_field = 'reviewed';
				break;
		}

		if (!$permission_check) {
			echo json_encode([
				'success' => false,
				'message' => _l('access_denied')
			]);
			return;
		}

		// Update status
		$update_data = [
			'payroll_id' => $payroll_id,
			'status' => $new_status,
		];

		// Add timestamp fields
		if ($update_field && $new_status != 'draft') {
			$update_data[$update_field . '_by'] = get_staff_user_id();
			$update_data[$update_field . '_date'] = date('Y-m-d H:i:s');
		}

		// Special handling for completed status
		if ($new_status == 'completed') {
			$update_data['completed_date'] = date('Y-m-d H:i:s');
		}

		$updated = $this->hr_payroll_model->update_payroll_status($update_data);

		if ($updated) {
			// Log status change
			$this->hr_payroll_model->log_status_change([
				'payroll_id' => $payroll_id,
				'from_status' => $payroll->status,
				'to_status' => $new_status,
				'changed_by' => get_staff_user_id(),
			]);

			echo json_encode([
				'success' => true,
				'message' => _l('hr_payroll_status_updated_successfully')
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('something_went_wrong')
			]);
		}
	}

	/**
	 * Delete payroll (only draft status)
	 * @param int $payroll_id
	 * @return json
	 */
	public function delete_payroll($payroll_id = null)
	{
		if (!$payroll_id) {
			$payroll_id = $this->input->post('payroll_id');
		}

		if (!has_permission('hrp_employee', '', 'delete') && !is_admin()) {
			echo json_encode([
				'success' => false,
				'message' => _l('access_denied')
			]);
			return;
		}

		$deleted = $this->hr_payroll_model->delete_payroll($payroll_id);

		if ($deleted) {
			echo json_encode([
				'success' => true,
				'message' => _l('hr_payroll_deleted_successfully')
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_cannot_delete')
			]);
		}
	}

	/**
	 * Recalculate payroll data for selected employees
	 * Re-fetches staff pay and timesheet data and updates the payroll
	 * @return json
	 */
	public function recalculate_payroll_employees()
	{
		$payroll_id = $this->input->post('payroll_id');
		$staff_ids = $this->input->post('staff_ids');

		if (!$payroll_id || !$staff_ids || !is_array($staff_ids)) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_missing_required_fields')
			]);
			return;
		}

		// Check permissions
		if (!has_permission('hrp_employee', '', 'edit') && !is_admin()) {
			echo json_encode([
				'success' => false,
				'message' => _l('access_denied')
			]);
			return;
		}

		// Get payroll details
		$payroll = $this->hr_payroll_model->get_payroll_details($payroll_id);

		if (!$payroll) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_payroll_not_found')
			]);
			return;
		}

		// Only allow recalculation in draft or ready_for_review status
		if (!in_array($payroll->status, ['draft', 'ready_for_review'])) {
			echo json_encode([
				'success' => false,
				'message' => _l('hr_cannot_recalculate_payroll_status')
			]);
			return;
		}

		// Recalculate for each selected employee
		$updated_count = 0;
		$failed_count = 0;
		$month = date('Y-m-d', strtotime($payroll->month));

		// Calculate month boundaries for proration
		$month_start = date('Y-m-01', strtotime($month));
		$month_end = date('Y-m-t', strtotime($month));
		$total_days_in_month = (int)date('t', strtotime($month));

		foreach ($staff_ids as $staff_id) {
			try {
				// Get fresh staff pay data
				$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, $month);

				// Calculate proration factor based on employee start date
				$proration_factor = 0; // Default: zero (no pay record)
				$start_date = $staff_pay->start_date ?? null;

				if ($start_date) {
					// Check if start_date is AFTER the payroll month (future employee)
					if (strtotime($start_date) > strtotime($month_end)) {
						// Employee hasn't started yet - show zero amounts
						$proration_factor = 0;
						log_activity("Recalculate - Staff {$staff_id}: Future start date {$start_date} (after {$month_end}), showing zero amounts");
					}
					// Check if start_date is within this payroll month (mid-month starter)
					elseif (strtotime($start_date) >= strtotime($month_start) && strtotime($start_date) <= strtotime($month_end)) {
						$start_day = (int)date('j', strtotime($start_date));
						$working_days = $total_days_in_month - $start_day + 1;
						$proration_factor = $working_days / $total_days_in_month;

						log_activity("Recalculate Proration - Staff {$staff_id}: Started {$start_date}, Working {$working_days}/{$total_days_in_month} days, Factor: " . round($proration_factor, 4));
					}
					// Otherwise, employee started before this month - use full month (factor = 1.0)
					else {
						$proration_factor = 1.0;
					}
				} else {
					// No pay record found - show zero amounts
					$failed_count++;
					log_activity("Recalculate: No pay data found for staff_id={$staff_id}");
					continue;
				}

				// Get additions/deductions from tblhrp_adjustments table
				$hrp_additions = $this->hr_payroll_model->get_additions_for_month($staff_id, $month);
				$hrp_deductions = $this->hr_payroll_model->get_deductions_for_month($staff_id, $month);

				// Get existing record to preserve OT hours and editable fields
				$existing = $this->hr_payroll_model->get_employee_payroll_record($payroll_id, $staff_id);
				$ot_hours = $existing ? floatval($existing->ot_hours ?? 0) : 0;

				// Get basic pay with proration
				$basic_pay = floatval($staff_pay->basic_pay ?? 0) * $proration_factor;

				// Calculate GOSI totals from staff_pay with corrected mappings and apply proration
				$gosi_basic = floatval($staff_pay->gosi_basic ?? $staff_pay->basic_pay ?? 0) * $proration_factor;
				$gosi_housing = floatval($staff_pay->gosi_housing_allowance ?? $staff_pay->accomodation_allowance ?? 0) * $proration_factor;
				$gosi_other = $this->hr_payroll_model->calculate_gosi_other_allowance($staff_id, $month, $proration_factor);
				$gosi_deduction = 0;  // ✅ MEWA is now an allowance, not a deduction

				// Calculate overtime with prorated rate
				$ot_rate = floatval($staff_pay->overtime_pay ?? 0);
				$ot_amount = $ot_hours * $ot_rate;

				// Calculate total allowance (for payslip display)
				$total_allowance = $this->hr_payroll_model->calculate_total_allowance($staff_id, $month, $proration_factor);

				// Calculate GOSI total amount
				$total_amount = $gosi_basic + $gosi_housing + $gosi_other - $gosi_deduction;

				// Calculate full salary: Basic Pay + OT Amount + Allowance + Additions - Deductions
				$full_salary = $basic_pay + $ot_amount + $total_allowance + $hrp_additions - $hrp_deductions;

				// Balance is the difference between full salary and GOSI total
				$balance = $full_salary - $total_amount;

				// Prepare update data with corrected calculations
				$update_data = [
					'gosi_basic_salary' => number_format($gosi_basic, 2, '.', ''),
					'gosi_housing_allowance' => number_format($gosi_housing, 2, '.', ''),
					'gosi_other_allowance' => number_format($gosi_other, 2, '.', ''),
					'gosi_deduction' => number_format($gosi_deduction, 2, '.', ''),
					'basic' => number_format($basic_pay, 2, '.', ''),
					'ot_rate' => number_format($ot_rate, 2, '.', ''),
					'ot_amount' => number_format($ot_amount, 2, '.', ''),
					'allowance' => number_format($total_allowance, 2, '.', ''),  // ✅ All allowances including custom, prorated
					'additions' => number_format($hrp_additions, 2, '.', ''),  // HRP additions from tblhrp_adjustments
					'deduction' => number_format($hrp_deductions, 2, '.', ''),  // Only HRP deductions from tblhrp_adjustments
					'total_amount' => number_format($total_amount, 2, '.', ''),
					'full_salary' => number_format($full_salary, 2, '.', ''),
					'balance' => number_format($balance, 2, '.', ''),
				];

				log_activity("Recalculate - Staff {$staff_id}: Basic Pay={$basic_pay}, GOSI Basic={$gosi_basic}, Housing={$gosi_housing}, Other={$gosi_other}, Allowance={$total_allowance}, Full Salary={$full_salary}");

				// Update the employee record
				$updated = $this->hr_payroll_model->update_employee_payroll_record($payroll_id, $staff_id, $update_data);

				if ($updated) {
					$updated_count++;
					log_activity("Recalculate - Staff {$staff_id}: Successfully updated (count: {$updated_count})");
				} else {
					$failed_count++;
					log_activity("Recalculate - Staff {$staff_id}: Update FAILED (failed count: {$failed_count})");
				}

			} catch (Exception $e) {
				$failed_count++;
				log_activity("Recalculate error for staff_id={$staff_id}: " . $e->getMessage());
			}
		}

		// Update payroll totals
		$this->hr_payroll_model->update_payroll_totals($payroll_id);

		// Debug: Log final counts
		log_activity("Recalculate Final - Total staff_ids: " . count($staff_ids) . ", Updated: {$updated_count}, Failed: {$failed_count}");

		if ($updated_count > 0) {
			log_activity("Recalculated payroll data for {$updated_count} employees in payroll_id={$payroll_id}");

			// Build message manually to avoid sprintf issues
			if ($failed_count > 0) {
				$message = "Recalculated {$updated_count} employees successfully. {$failed_count} employees failed.";
			} else {
				$message = "Successfully recalculated pay data for {$updated_count} employee" . ($updated_count != 1 ? 's' : '');
			}

			echo json_encode([
				'success' => true,
				'message' => $message,
				'updated_count' => (int)$updated_count,
				'failed_count' => (int)$failed_count,
				'debug_total_ids' => count($staff_ids)
			]);
		} else {
			log_activity("Recalculate Failed - No employees updated. Total IDs: " . count($staff_ids));

			echo json_encode([
				'success' => false,
				'message' => _l('hr_recalculate_failed') . ' (No employees were updated. Processed: ' . count($staff_ids) . ', Failed: ' . $failed_count . ')',
				'updated_count' => $updated_count,
				'failed_count' => $failed_count,
				'debug_total_ids' => count($staff_ids)
			]);
		}
	}

	/**
	 * Get payroll status log
	 * @param int $payroll_id
	 * @return json
	 */
	public function get_payroll_status_log($payroll_id)
	{
		if (!has_permission('hrp_employee', '', 'view') && !is_admin()) {
			ajax_access_denied();
		}

		$logs = $this->hr_payroll_model->get_payroll_status_log($payroll_id);

		echo json_encode([
			'success' => true,
			'logs' => $logs
		]);
	}

	/**
	 * Get pay modal data for editing staff pay information
	 * Returns staff details, pay information, and applicable custom allowances
	 */
	public function get_pay_modal_data()
	{
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month');

		if (!$staff_id) {
			echo json_encode(['error' => 'Invalid staff ID']);
			return;
		}

		$this->load->model('staff_model');

		$staff = $this->staff_model->get($staff_id);

		if (!$staff) {
			echo json_encode(['error' => 'Staff not found']);
			return;
		}

		$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, date('Y-m-d', strtotime($month)));

		// Get custom allowances applicable to this employee
		$custom_allowances = $this->hr_payroll_model->get_employee_applicable_allowances($staff_id);

		// Get current amounts for custom allowances
		$custom_amounts = [];
		if ($staff_pay) {
			foreach ($custom_allowances as $allowance) {
				$this->db->select('amount');
				$this->db->from(db_prefix() . 'staff_pay_allowances');
				$this->db->where('staff_pay_id', $staff_pay->id);
				$this->db->where('allowance_type_id', $allowance['id']);
				$result = $this->db->get()->row();

				$custom_amounts[$allowance['id']] = $result ? floatval($result->amount) : floatval($allowance['default_amount'] ?? 0);
			}
		} else {
			// No pay record yet - use default amounts
			foreach ($custom_allowances as $allowance) {
				$custom_amounts[$allowance['id']] = floatval($allowance['default_amount'] ?? 0);
			}
		}

		$data = [
			'staff' => $staff,
			'staff_pay' => $staff_pay,
			'custom_allowances' => $custom_allowances,
			'custom_amounts' => $custom_amounts,
			'month' => $month,
			'csrf_hash' => $this->security->get_csrf_hash()
		];

		echo json_encode($data);
	}

	/**
	 * Update pay information from modal
	 * Updates tblstaffpay and tblstaff_pay_allowances
	 */
	public function update_pay_from_modal()
	{
		try {
			$staff_id = $this->input->post('staff_id');
			$month = $this->input->post('month');

			if (!$staff_id) {
				echo json_encode(['status' => 'error', 'message' => 'Invalid staff ID', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}

			$this->load->model('staff_pay_model');

			// Get or create staff pay record
			$month_date = date('Y-m-d', strtotime($month));
			$staff_pay = $this->hr_payroll_model->get_staff_pay_for_month($staff_id, $month_date);

			$basic_pay = floatval($this->input->post('basic_pay') ?? 0);
			$accomodation = floatval($this->input->post('accomodation_allowance') ?? 0);

			// Get GOSI values from form (can be different from basic_pay and accommodation)
			$gosi_basic = $this->input->post('gosi_basic');
			$gosi_housing = $this->input->post('gosi_housing_allowance');

			// Use posted values if provided, otherwise fallback to basic_pay/accommodation
			$gosi_basic = ($gosi_basic !== null && $gosi_basic !== '') ? floatval($gosi_basic) : $basic_pay;
			$gosi_housing = ($gosi_housing !== null && $gosi_housing !== '') ? floatval($gosi_housing) : $accomodation;

			$pay_data = [
				'staff_id' => $staff_id,
				'start_date' => $month_date,
				'payout_type' => $this->input->post('payout_type') ?? 'monthly',
				'basic_pay' => $basic_pay,
				'overtime_pay' => floatval($this->input->post('overtime_pay') ?? 0),
				'food_allowance' => floatval($this->input->post('food_allowance') ?? 0),
				'allowance' => floatval($this->input->post('allowance') ?? 0),
				'fat_allowance' => floatval($this->input->post('fat_allowance') ?? 0),
				'accomodation_allowance' => $accomodation,
				'mewa' => floatval($this->input->post('mewa') ?? 0),
				// GOSI fields (can be different from basic_pay and accommodation)
				'gosi_basic' => $gosi_basic,
				'gosi_housing_allowance' => $gosi_housing,
			];

			log_activity('update_pay_from_modal - Staff ID: ' . $staff_id . ', GOSI Basic: ' . $gosi_basic . ', GOSI Housing: ' . $gosi_housing);

			// Check if we're updating from a payroll view
			$payroll_id = $this->input->post('payroll_id');

			$pay_id = null;
			$success = false;

			if ($staff_pay) {
				// Update existing record
				$success = $this->staff_pay_model->update($staff_pay->id, $pay_data);
				$pay_id = $staff_pay->id;

				if (!$success) {
					log_message('error', 'Failed to update staff pay. Staff ID: ' . $staff_id . ', Pay ID: ' . $staff_pay->id);
					echo json_encode([
						'status' => 'error',
						'message' => 'Failed to update pay information. Database error: ' . $this->db->error()['message'],
						'csrf_hash' => $this->security->get_csrf_hash()
					]);
					return;
				}
			} else {
				// Create new record
				$pay_id = $this->staff_pay_model->add($pay_data);

				if (!$pay_id) {
					log_message('error', 'Failed to create staff pay. Staff ID: ' . $staff_id . ', Error: ' . $this->db->error()['message']);
					echo json_encode([
						'status' => 'error',
						'message' => 'Failed to create pay information. Database error: ' . $this->db->error()['message'],
						'csrf_hash' => $this->security->get_csrf_hash()
					]);
					return;
				}
			}

			// Verify pay_id is valid before proceeding
			if (!$pay_id) {
				log_message('error', 'Invalid pay_id after save operation. Staff ID: ' . $staff_id);
				echo json_encode([
					'status' => 'error',
					'message' => 'Invalid pay record ID',
					'csrf_hash' => $this->security->get_csrf_hash()
				]);
				return;
			}

			// Update custom allowances
			$custom_allowances = $this->input->post('custom_allowances') ?? [];

			// Delete existing custom allowances
			$this->db->where('staff_pay_id', $pay_id);
			$this->db->delete(db_prefix() . 'staff_pay_allowances');

			// Insert new custom allowances (only if they have values)
			if (!empty($custom_allowances)) {
				foreach ($custom_allowances as $allowance_id => $amount) {
					// Convert to float and check if greater than 0
					$amount_float = floatval($amount);
					if ($amount_float > 0) {
						$insert_result = $this->db->insert(db_prefix() . 'staff_pay_allowances', [
							'staff_pay_id' => $pay_id,
							'allowance_type_id' => $allowance_id,
							'amount' => $amount_float
						]);

						if (!$insert_result) {
							log_message('error', 'Failed to insert custom allowance. Pay ID: ' . $pay_id . ', Allowance ID: ' . $allowance_id);
						}
					}
				}
			}

			// If updating from payroll view, also update the payroll snapshot
			if ($payroll_id) {
				// Calculate GOSI other allowance (includes MEWA and custom allowances)
				$gosi_other = $this->hr_payroll_model->calculate_gosi_other_allowance($staff_id, $month_date);

				// Calculate total allowance for payslip side
				$total_allowance = $this->hr_payroll_model->calculate_total_allowance($staff_id, $month_date);

				// Calculate GOSI total
				$gosi_total = $gosi_basic + $gosi_housing + $gosi_other - 0;

				// Update the payroll snapshot
				$this->db->where('payroll_id', $payroll_id);
				$this->db->where('staff_id', $staff_id);
				$this->db->update(db_prefix() . 'hrp_employees_value', [
					'gosi_basic_salary' => $gosi_basic,
					'gosi_housing_allowance' => $gosi_housing,
					'gosi_other_allowance' => $gosi_other,
					'gosi_deduction' => 0,
					'total_amount' => $gosi_total,
					'basic' => $basic_pay,
					'allowance' => $total_allowance,
				]);

				log_activity('Updated payroll snapshot - Payroll ID: ' . $payroll_id . ', Staff ID: ' . $staff_id);
			}

			echo json_encode([
				'status' => 'ok',
				'message' => 'Pay information updated successfully',
				'payroll_updated' => $payroll_id ? true : false,
				'csrf_hash' => $this->security->get_csrf_hash()
			]);

		} catch (Exception $e) {
			log_message('error', 'Exception in update_pay_from_modal: ' . $e->getMessage());
			echo json_encode([
				'status' => 'error',
				'message' => 'An error occurred: ' . $e->getMessage(),
				'csrf_hash' => $this->security->get_csrf_hash()
			]);
		}
	}

	/**
	 * WPS Settings - Manage WPS (Wage Protection System) export settings
	 * @return view
	 */
	public function wps_settings()
	{
		if (!has_permission('hrp_setting', '', 'view') && !has_permission('hrp_setting', '', 'edit') && !is_admin()) {
			access_denied('hrp_setting');
		}

		$this->load->model('wps_settings_model');

		// Get all companies for dropdown - query directly from tblcompanytype
		$data['companies'] = $this->db->select('id, name')->get('tblcompanytype')->result_array();

		// Get WPS settings for all companies
		$data['wps_settings'] = $this->wps_settings_model->get_all();

		$data['title'] = 'WPS Export Settings';
		$this->load->view('wps_settings/manage', $data);
	}

	/**
	 * Save WPS settings via AJAX
	 * @return json
	 */
	public function save_wps_settings()
	{
		if (!has_permission('hrp_setting', '', 'edit') && !is_admin()) {
			echo json_encode([
				'success' => false,
				'message' => _l('access_denied')
			]);
			return;
		}

		$this->load->model('wps_settings_model');

		$company_id = $this->input->post('company_id');
		if ($company_id === '' || $company_id === 'null') {
			$company_id = null;
		}

		$data = [
			'type' => $this->input->post('type'),
			'customer_name' => $this->input->post('customer_name'),
			'agreement_code' => $this->input->post('agreement_code'),
			'funding_account' => $this->input->post('funding_account'),
			'branch_no' => $this->input->post('branch_no'),
			'credit_date_format' => $this->input->post('credit_date_format'),
			'mins_lab_establish_id' => $this->input->post('mins_lab_establish_id'),
			'ecr_id' => $this->input->post('ecr_id'),
			'bank_code' => $this->input->post('bank_code'),
			'currency' => $this->input->post('currency'),
			'batch' => $this->input->post('batch'),
			'file_reference' => $this->input->post('file_reference'),
			'payment_desc' => $this->input->post('payment_desc'),
			'payment_ref' => $this->input->post('payment_ref'),
		];

		$success = $this->wps_settings_model->save($data, $company_id);

		echo json_encode([
			'success' => $success,
			'message' => $success ? 'WPS settings saved successfully' : 'Failed to save WPS settings'
		]);
	}

	/**
	 * Get WPS settings for a specific company via AJAX
	 * @return json
	 */
	public function get_wps_settings()
	{
		$this->load->model('wps_settings_model');

		$company_id = $this->input->get('company_id');
		if ($company_id === '' || $company_id === 'null') {
			$company_id = null;
		}

		$settings = $this->wps_settings_model->get($company_id);

		echo json_encode([
			'success' => true,
			'data' => $settings
		]);
	}

//End file
}

require_once(APPPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    public function CellFitScale($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        // get string width
        $str_width = $this->GetStringWidth($txt);
        if ($str_width == 0) {
            $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
            return;
        }
        $ratio = ($w - 2) / $str_width; // -2 padding
        if ($ratio < 1) {
            $this->SetFont('', '', $this->FontSizePt * $ratio); // shrink font
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        $this->SetFont('', '', 10); // reset font
    }
	
}