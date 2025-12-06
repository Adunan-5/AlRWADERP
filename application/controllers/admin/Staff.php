<?php

defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * @property-read Authentication_model $authentication_model
 * @property-read Staff_model $staff_model
 */
class Staff extends AdminController
{
    /* List all staff members */
    public function index()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff');
        }
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title']         = _l('staff_members');
        $this->load->view('admin/staff/manage', $data);
    }

    /* Add new staff member or edit existing */
    // public function member($id = '')
    // {
    //     if (staff_cant('view', 'staff')) {
    //         access_denied('staff');
    //     }
    //     hooks()->do_action('staff_member_edit_view_profile', $id);

    //     $this->load->model('departments_model');
    //     if ($this->input->post()) {
    //         $data = $this->input->post();
    //         $dateFields = [
    //             'joining_date',
    //             'joining_date_hijri',
    //             'atm_expiry',
    //             'atm_expiry_hijri',
    //             'iqama_expiry',
    //             'iqama_expiry_hijri',
    //             'ajeer_expiry',
    //             'ajeer_expiry_hijri',
    //             'contract_start_date',
    //             'contract_end_date',
    //             'insurance_expiry',
    //             'insurance_expiry_hijri',
    //             'health_card_expiry',
    //             'health_card_expiry_hijri',
    //             'visa_expiry',
    //             'visa_expiry_hijri',
    //             'passport_expiry',
    //             'qiwa_expiry',
    //             'aramcoidexpiry',
    //             'dob', // if you want DOB also as real date, else keep as string
    //         ];

    //         foreach ($dateFields as $field) {
    //             if (isset($data[$field]) && $data[$field] === '') {
    //                 $data[$field] = null;
    //             }
    //         }
    //         if (isset($data['skills']) && is_array($data['skills'])) {
    //             $data['skills'] = implode(',', $data['skills']);
    //         }
    //         // Don't do XSS clean here.
    //         $data['email_signature'] = $this->input->post('email_signature', false);
    //         $data['email_signature'] = html_entity_decode($data['email_signature']);

    //         if ($data['email_signature'] == strip_tags($data['email_signature'])) {
    //             // not contains HTML, add break lines
    //             $data['email_signature'] = nl2br_save_html($data['email_signature']);
    //         }

    //         $data['password'] = $this->input->post('password', false);

    //         if ($id == '') {
    //             if (staff_cant('create', 'staff')) {
    //                 access_denied('staff');
    //             }
    //             $id = $this->staff_model->add($data);
    //             if ($id) {
    //                 handle_staff_profile_image_upload($id);
    //                 update_staff_excel($id, true);
    //                 set_alert('success', _l('added_successfully', _l('staff_member')));
    //                 redirect(admin_url('staff/member/' . $id));
    //             }
    //         } else {
    //             if (staff_cant('edit', 'staff')) {
    //                 access_denied('staff');
    //             }
    //             handle_staff_profile_image_upload($id);
    //             $response = $this->staff_model->update($data, $id);
    //             if (is_array($response)) {
    //                 if (isset($response['cant_remove_main_admin'])) {
    //                     set_alert('warning', _l('staff_cant_remove_main_admin'));
    //                 } elseif (isset($response['cant_remove_yourself_from_admin'])) {
    //                     set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
    //                 }
    //             } elseif ($response == true) {
    //                 update_staff_excel($id, false);
    //                 set_alert('success', _l('updated_successfully', _l('staff_member')));
    //             }
    //             redirect(admin_url('staff/member/' . $id));
    //         }
    //     }
    //     if ($id == '') {
    //         $title = _l('add_new', _l('staff_member'));
    //     } else {
    //         $member = $this->staff_model->get($id);
    //         if (!$member) {
    //             blank_page('Staff Member Not Found', 'danger');
    //         }
    //         $data['member']            = $member;
    //         $title                     = $member->name;
    //         $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

    //         $ts_filter_data = [];
    //         if ($this->input->get('filter')) {
    //             if ($this->input->get('range') != 'period') {
    //                 $ts_filter_data[$this->input->get('range')] = true;
    //             } else {
    //                 $ts_filter_data['period-from'] = $this->input->get('period-from');
    //                 $ts_filter_data['period-to']   = $this->input->get('period-to');
    //             }
    //         } else {
    //             $ts_filter_data['this_month'] = true;
    //         }

    //         // $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
    //         // $data['timesheets']  = $data['logged_time']['timesheets'];

    //         $this->load->model('timesheet_model');
    //         $data['staff_timesheets'] = $this->timesheet_model->get_by_staff($id);
    //     }
    //     $this->load->model('currencies_model');
    //     $data['base_currency'] = $this->currencies_model->get_base_currency();
    //     $data['roles']         = $this->roles_model->get();
    //     $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
    //     $data['departments']   = $this->departments_model->get();
    //     $data['title']         = $title;

    //     $this->load->view('admin/staff/member', $data);
    // }

    public function member($id = '')
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Enhanced date handling - handle both empty strings and '0000-00-00'
            $dateFields = [
                'joining_date',
                'joining_date_hijri',
                'atm_expiry',
                'atm_expiry_hijri',
                'iqama_expiry',
                'iqama_expiry_hijri',
                'ajeer_expiry',
                'ajeer_expiry_hijri',
                'contract_start_date',
                'contract_end_date',
                'insurance_expiry',
                'insurance_expiry_hijri',
                'health_card_expiry',
                'health_card_expiry_hijri',
                'visa_expiry',
                'visa_expiry_hijri',
                'passport_expiry',
                'qiwa_expiry',
                'aramcoidexpiry',
                'dob', // if you want DOB also as real date, else keep as string
                'last_salary_revision_date', // NEW FIELD
            ];

            foreach ($dateFields as $field) {
                if (isset($data[$field])) {
                    // Handle empty string, null, or '0000-00-00'
                    if ($data[$field] === '' || $data[$field] === null || $data[$field] === '0000-00-00') {
                        $data[$field] = null;
                    }
                }
            }

            // Handle new fields
            $newFields = [
                'status',
                'project_id', 
                'work_hours_per_day',
                'review',
                'last_salary_revision_comments',
                'contract_period_months',
            ];

            foreach ($newFields as $field) {
                if (isset($data[$field])) {
                    // Handle empty values for new fields
                    if ($data[$field] === '' || $data[$field] === null) {
                        $data[$field] = null;
                    }
                    // For work_hours_per_day, convert to float if numeric
                    if ($field === 'work_hours_per_day' && is_numeric($data[$field])) {
                        $data[$field] = (float) $data[$field];
                    }
                    // For contract_period_months, ensure it's integer
                    if ($field === 'contract_period_months' && is_numeric($data[$field])) {
                        $data[$field] = (int) $data[$field];
                    }
                }
            }

            // Handle skills array
            if (isset($data['skills']) && is_array($data['skills'])) {
                $data['skills'] = implode(',', $data['skills']);
            }

            // Handle email signature
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $data['password'] = $this->input->post('password', false);

            if ($id == '') {
                if (staff_cant('create', 'staff')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);
                if ($id) {
                    handle_staff_profile_image_upload($id);
                    // update_staff_excel($id, true);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('staff/member/' . $id));
                }
            } else {
                if (staff_cant('edit', 'staff')) {
                    access_denied('staff');
                }
                handle_staff_profile_image_upload($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    // update_staff_excel($id, false);
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                redirect(admin_url('staff/member/' . $id));
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('staff_member'));
        } else {
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member']            = $member;
            $title                     = $member->name;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to']   = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $this->load->model('timesheet_model');
            $data['staff_timesheets'] = $this->timesheet_model->get_by_staff($id);

            // Fetch additional data for quick info panel
            // Get company type name
            if (!empty($member->companytype_id)) {
                $company_type = $this->db->where('id', $member->companytype_id)->get(db_prefix() . 'companytype')->row();
                $data['company_type_name'] = $company_type ? $company_type->name : '';
            } else {
                $data['company_type_name'] = '';
            }

            // Get profession type name
            if (!empty($member->professiontype_id)) {
                $profession_type = $this->db->where('id', $member->professiontype_id)->get(db_prefix() . 'professiontype')->row();
                $data['profession_type_name'] = $profession_type ? $profession_type->name : '';
            } else {
                $data['profession_type_name'] = '';
            }

            // Get project name
            if (!empty($member->project_id)) {
                $this->load->model('projects_model');
                $project = $this->projects_model->get($member->project_id);
                $data['project_name'] = $project ? $project->name : '';
            } else {
                $data['project_name'] = '';
            }

            // Get current basic pay
            $this->load->model('staff_pay_model');
            $current_pay = $this->db
                ->where('staff_id', $id)
                ->order_by('start_date', 'DESC')
                ->limit(1)
                ->get(db_prefix() . 'staffpay')
                ->row();
            $data['current_basic_pay'] = $current_pay ? $current_pay->basic_pay : '';

            // Calculate age from DOB
            $data['age'] = '';
            if (!empty($member->dob)) {
                try {
                    $dob = new DateTime($member->dob);
                    $now = new DateTime();
                    $age_diff = $now->diff($dob);
                    $data['age'] = $age_diff->y;
                } catch (Exception $e) {
                    $data['age'] = '';
                }
            }
        }

        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['roles']         = $this->roles_model->get();
        $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
        $data['departments']   = $this->departments_model->get();
        $data['title']         = $title;

        $this->load->view('admin/staff/member', $data);
    }

    /* Get role permission for specific role id */
    public function role_changed($id)
    {
        if (staff_cant('view', 'staff')) {
            ajax_access_denied('staff');
        }

        echo json_encode($this->roles_model->get($id)->permissions);
    }

    public function save_dashboard_widgets_order()
    {
        hooks()->do_action('before_save_dashboard_widgets_order');

        $post_data = $this->input->post();
        foreach ($post_data as $container => $widgets) {
            if ($widgets == 'empty') {
                $post_data[$container] = [];
            }
        }
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', serialize($post_data));
    }

    public function save_dashboard_widgets_visibility()
    {
        hooks()->do_action('before_save_dashboard_widgets_visibility');

        $post_data = $this->input->post();
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', serialize($post_data['widgets']));
    }

    public function reset_dashboard()
    {
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', null);
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', null);

        redirect(admin_url());
    }

    public function save_hidden_table_columns()
    {
        hooks()->do_action('before_save_hidden_table_columns');
        $data   = $this->input->post();
        $id     = $data['id'];
        $hidden = isset($data['hidden']) ? $data['hidden'] : [];
        update_staff_meta(get_staff_user_id(), 'hidden-columns-' . $id, json_encode($hidden));
    }

    public function change_language($lang = '')
    {
        hooks()->do_action('before_staff_change_language', $lang);

        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'staff', ['default_language' => $lang]);
        
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function timesheets()
    {
        $data['view_all'] = false;
        if (staff_can('view-timesheets', 'reports') && $this->input->get('view') == 'all') {
            $data['staff_members_with_timesheets'] = $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . 'taskstimers WHERE staff_id !=' . get_staff_user_id())->result_array();
            $data['view_all']                      = true;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_timesheets', ['view_all' => $data['view_all']]);
        }

        if ($data['view_all'] == false) {
            unset($data['view_all']);
        }

        $data['logged_time'] = $this->staff_model->get_logged_time_data(get_staff_user_id());
        $data['title']       = '';
        $this->load->view('admin/staff/timesheets', $data);
    }

    public function delete()
    {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }

        if (staff_can('delete',  'staff')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }

        redirect(admin_url('staff'));
    }

    /* When staff edit his profile */
    public function edit_profile()
    {
        hooks()->do_action('edit_logged_in_staff_profile');

        if ($this->input->post()) {
            handle_staff_profile_image_upload();
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $success = $this->staff_model->update_profile($data, get_staff_user_id());

            if ($success) {
                set_alert('success', _l('staff_profile_updated'));
            }

            redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
        }
        $member = $this->staff_model->get(get_staff_user_id());
        $this->load->model('departments_model');
        $data['member']            = $member;
        $data['departments']       = $this->departments_model->get();
        $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);
        $data['title']             = $member->firstname . ' ' . $member->lastname;
        $this->load->view('admin/staff/profile', $data);
    }

    /* Remove staff profile image / ajax */
    public function remove_staff_profile_image($id = '')
    {
        $staff_id = get_staff_user_id();
        if (is_numeric($id) && (staff_can('create',  'staff') || staff_can('edit',  'staff'))) {
            $staff_id = $id;
        }
        hooks()->do_action('before_remove_staff_profile_image');
        $member = $this->staff_model->get($staff_id);
        if (file_exists(get_upload_path_by_type('staff') . $staff_id)) {
            delete_dir(get_upload_path_by_type('staff') . $staff_id);
        }
        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix() . 'staff', [
            'profile_image' => null,
        ]);

        if (!is_numeric($id)) {
            redirect(admin_url('staff/edit_profile/' . $staff_id));
        } else {
            redirect(admin_url('staff/member/' . $staff_id));
        }
    }

    /* When staff change his password */
    public function change_password_profile()
    {
        if ($this->input->post()) {
            $response = $this->staff_model->change_password($this->input->post(null, false), get_staff_user_id());
            if (is_array($response) && isset($response[0]['passwordnotmatch'])) {
                set_alert('danger', _l('staff_old_password_incorrect'));
            } else {
                if ($response == true) {
                    set_alert('success', _l('staff_password_changed'));
                } else {
                    set_alert('warning', _l('staff_problem_changing_password'));
                }
            }
            redirect(admin_url('staff/edit_profile'));
        }
    }

    /* View public profile. If id passed view profile by staff id else current user*/
    public function profile($id = '')
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }

        hooks()->do_action('staff_profile_access', $id);

        $data['logged_time'] = $this->staff_model->get_logged_time_data($id);
        $data['staff_p']     = $this->staff_model->get($id);

        if (!$data['staff_p']) {
            blank_page('Staff Member Not Found', 'danger');
        }

        $this->load->model('departments_model');
        $data['staff_departments'] = $this->departments_model->get_staff_departments($data['staff_p']->staffid);
        $data['departments']       = $this->departments_model->get();

        // Load your custom timesheets
        $this->load->model('timesheet_model');
        $data['staff_timesheets'] = $this->timesheet_model->get_by_staff($id);
        $data['base_currency'] = get_base_currency();

        $data['title']             = _l('staff_profile_string') . ' - ' . $data['staff_p']->firstname . ' ' . $data['staff_p']->lastname;
        // notifications
        $total_notifications = total_rows(db_prefix() . 'notifications', [
            'touserid' => get_staff_user_id(),
        ]);
        $data['total_pages'] = ceil($total_notifications / $this->misc_model->get_notifications_limit());
        $this->load->view('admin/staff/myprofile', $data);
    }

    /* Change status to staff active or inactive / ajax */
    public function change_staff_status($id, $status)
    {
        if (staff_can('edit',  'staff')) {
            if ($this->input->is_ajax_request()) {
                $this->staff_model->change_staff_status($id, $status);
            }
        }
    }

    /* Logged in staff notifications*/
    public function notifications()
    {
        $this->load->model('misc_model');
        if ($this->input->post()) {
            $page   = $this->input->post('page');
            $offset = ($page * $this->misc_model->get_notifications_limit());
            $this->db->limit($this->misc_model->get_notifications_limit(), $offset);
            $this->db->where('touserid', get_staff_user_id());
            $this->db->order_by('date', 'desc');
            $notifications = $this->db->get(db_prefix() . 'notifications')->result_array();
            $i             = 0;
            foreach ($notifications as $notification) {
                if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0) || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                    if ($notification['fromuserid'] != 0) {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('staff/profile/' . $notification['fromuserid']) . '">' . staff_profile_image($notification['fromuserid'], [
                            'staff-profile-image-small',
                            'img-circle',
                            'pull-left',
                        ]) . '</a>';
                    } else {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('clients/client/' . $notification['fromclientid']) . '">
                    <img class="client-profile-image-small img-circle pull-left" src="' . contact_profile_image_url($notification['fromclientid']) . '"></a>';
                    }
                } else {
                    $notifications[$i]['profile_image'] = '';
                    $notifications[$i]['full_name']     = '';
                }
                $additional_data = '';
                if (!empty($notification['additional_data'])) {
                    $additional_data = unserialize($notification['additional_data']);
                    $x               = 0;
                    foreach ($additional_data as $data) {
                        if (strpos($data, '<lang>') !== false) {
                            $lang = get_string_between($data, '<lang>', '</lang>');
                            $temp = _l($lang);
                            if (strpos($temp, 'project_status_') !== false) {
                                $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                                $temp   = $status['name'];
                            }
                            $additional_data[$x] = $temp;
                        }
                        $x++;
                    }
                }
                $notifications[$i]['description'] = _l($notification['description'], $additional_data);
                $notifications[$i]['date']        = time_ago($notification['date']);
                $notifications[$i]['full_date']   = _dt($notification['date']);
                $i++;
            } //$notifications as $notification
            echo json_encode($notifications);
            die;
        }
    }

    public function update_two_factor()
    {
        $fail_reason = _l('set_two_factor_authentication_failed');
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('two_factor_auth', _l('two_factor_auth'), 'required');

            if ($this->input->post('two_factor_auth') == 'google') {
                $this->form_validation->set_rules('google_auth_code', _l('google_authentication_code'), 'required');
            }

            if ($this->form_validation->run() !== false) {
                $two_factor_auth_mode = $this->input->post('two_factor_auth');
                $id = get_staff_user_id();
                if ($two_factor_auth_mode == 'google') {
                    $this->load->model('Authentication_model');
                    $secret = $this->input->post('secret');
                    $success = $this->authentication_model->set_google_two_factor($secret);
                    $fail_reason = _l('set_google_two_factor_authentication_failed');
                } elseif ($two_factor_auth_mode == 'email') {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 1]);
                } else {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 0]);
                }
                if ($success) {
                    set_alert('success', _l('set_two_factor_authentication_successful'));
                    redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
                }
            }
        }
        set_alert('danger', $fail_reason);
        redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
    }

    public function verify_google_two_factor()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->load->model('authentication_model');
            $is_success = $this->authentication_model->is_google_two_factor_code_valid($data['code'],$data['secret']);
            $result = [];

            header('Content-Type: application/json');
            if ($is_success) {
                $result['status'] = 'success';
                $result['message'] = _l('google_2fa_code_valid');;

                echo json_encode($result);
                die;
            }

            $result['status'] = 'failed';
            $result['message'] = _l('google_2fa_code_invalid');;

            echo json_encode($result);
            die;
        }
    }

    public function save_completed_checklist_visibility()
    {
        hooks()->do_action('before_save_completed_checklist_visibility');

        $post_data = $this->input->post();
        if (is_numeric($post_data['task_id'])) {
            update_staff_meta(get_staff_user_id(), 'task-hide-completed-items-'. $post_data['task_id'], $post_data['hideCompleted']);
        }
    }

    public function import_excel()
    {
        if (staff_cant('create', 'staff')) {
            access_denied('staff');
        }

        $this->load->helper('staff_excel');

        if (isset($_FILES['import_file']['name']) && $_FILES['import_file']['name'] != '') {
            $allowed_extensions = ['xls', 'xlsx'];
            $file_extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                set_alert('danger', 'Invalid file type. Please upload an Excel file (.xls or .xlsx).');
                redirect(admin_url('staff'));
                return;
            }

            // Create uploads/temp directory if it doesn't exist
            $temp_dir = FCPATH . 'uploads/temp/';
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }

            // Generate unique filename
            $temp_filename = 'staff_import_' . time() . '_' . uniqid() . '.' . $file_extension;
            $temp_path = $temp_dir . $temp_filename;

            // Move uploaded file to temp directory
            if (move_uploaded_file($_FILES['import_file']['tmp_name'], $temp_path)) {
                try {
                    // Parse the Excel file for preview
                    $preview_data = parse_staff_excel_for_preview($temp_path);

                    if (empty($preview_data)) {
                        unlink($temp_path); // Delete temp file
                        set_alert('warning', 'No valid data found in the Excel file.');
                        redirect(admin_url('staff'));
                        return;
                    }

                    // Store temp filename in session for later processing
                    $this->session->set_userdata('import_temp_file', $temp_filename);

                    // Load preview view
                    $data['preview_data'] = $preview_data;
                    $data['title'] = 'Import Staff - Preview';
                    $this->load->view('admin/staff/import_preview', $data);

                } catch (Exception $e) {
                    if (file_exists($temp_path)) {
                        unlink($temp_path); // Delete temp file on error
                    }
                    log_message('error', 'Excel Import Error: ' . $e->getMessage());
                    set_alert('danger', 'Error while parsing Excel file: ' . $e->getMessage());
                    redirect(admin_url('staff'));
                }
            } else {
                set_alert('danger', 'Failed to upload file. Please try again.');
                redirect(admin_url('staff'));
            }
        } else {
            set_alert('warning', 'Please select a file to upload.');
            redirect(admin_url('staff'));
        }
    }

    public function process_import()
    {
        if (staff_cant('create', 'staff')) {
            access_denied('staff');
        }

        $this->load->helper('staff_excel');

        // Get temp filename from session
        $temp_filename = $this->session->userdata('import_temp_file');

        if (!$temp_filename) {
            set_alert('danger', 'Import session expired. Please upload the file again.');
            redirect(admin_url('staff'));
            return;
        }

        $temp_path = FCPATH . 'uploads/temp/' . $temp_filename;

        if (!file_exists($temp_path)) {
            set_alert('danger', 'Import file not found. Please upload the file again.');
            $this->session->unset_userdata('import_temp_file');
            redirect(admin_url('staff'));
            return;
        }

        // Get selected row indices from POST
        $selected_rows = $this->input->post('selected_rows');

        if (empty($selected_rows)) {
            set_alert('warning', 'No rows selected for import.');
            redirect(admin_url('staff/import_excel_preview'));
            return;
        }

        try {
            // Process only selected rows
            $result = import_staff_excel_selected_rows($temp_path, $selected_rows);

            // Delete temp file
            unlink($temp_path);
            $this->session->unset_userdata('import_temp_file');

            // Show results
            $success_count = $result['success'];
            $error_count = $result['errors'];
            $total_selected = count($selected_rows);

            if ($error_count > 0) {
                set_alert('warning', "Import completed with some issues. Successfully imported: {$success_count}, Errors: {$error_count} out of {$total_selected} selected rows.");
            } else {
                set_alert('success', "Successfully imported {$success_count} staff records.");
            }

        } catch (Exception $e) {
            if (file_exists($temp_path)) {
                unlink($temp_path);
            }
            $this->session->unset_userdata('import_temp_file');
            log_message('error', 'Excel Import Process Error: ' . $e->getMessage());
            set_alert('danger', 'Error while processing import: ' . $e->getMessage());
        }

        redirect(admin_url('staff'));
    }

    public function save_vacation()
    {
        $this->load->model('staff_vacation_model');

        if ($this->input->post()) {
            $data = $this->input->post();

            if (isset($data['id']) && $data['id']) {
                // Update
                $success = $this->staff_vacation_model->update($data['id'], $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('vacation')));
                } else {
                    set_alert('danger', _l('problem_updating', _l('vacation')));
                }
            } else {
                // Insert
                if ($this->staff_vacation_model->add($data)) {
                    set_alert('success', _l('added_successfully', _l('vacation')));
                } else {
                    set_alert('danger', _l('problem_adding', _l('vacation')));
                }
            }

            redirect(admin_url('staff/member/' . $data['staff_id'] . '?tab=staff_vacation'));
        }
    }

    public function vacation_table($staff_id)
    {
        if (!is_numeric($staff_id)) {
            ajax_access_denied();
        }

        $this->app->get_table_data('staff_vacation', [
            'staff_id' => $staff_id
        ]);
    }

    public function get_vacation($id)
    {
        $this->load->model('staff_vacation_model');
        $vacation = $this->staff_vacation_model->get($id);

        echo json_encode($vacation);
    }

    public function delete_vacation($id)
    {
        if (!is_numeric($id)) {
            ajax_access_denied();
        }

        $this->load->model('staff_vacation_model');

        // Get vacation details before deletion
        $vacation = $this->staff_vacation_model->get($id);

        if (!$vacation) {
            echo json_encode([
                'success' => false,
                'message' => _l('vacation_not_found')
            ]);
            return;
        }

        // Check if user has permission to delete (staff member's own vacation or admin)
        if (!is_admin() && $vacation->staff_id != get_staff_user_id()) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied')
            ]);
            return;
        }

        // Check for dependencies before allowing deletion
        $dependency_check = $this->staff_vacation_model->check_dependencies($id);

        if (!$dependency_check['can_delete']) {
            $reasons_html = '<ul>';
            foreach ($dependency_check['reasons'] as $reason) {
                $reasons_html .= '<li>' . $reason . '</li>';
            }
            $reasons_html .= '</ul>';

            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete this vacation entry',
                'reasons' => $dependency_check['reasons'],
                'reasons_html' => $reasons_html
            ]);
            return;
        }

        $success = $this->staff_vacation_model->delete($id);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => _l('deleted', _l('vacation'))
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('problem_deleting', _l('vacation'))
            ]);
        }
    }

    public function staff_projects()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_projects');
        }
    }

    public function delete_project_assignment($id)
    {
        if (!is_numeric($id)) {
            ajax_access_denied();
        }

        $this->load->model('projectassignee_model');

        // Get project assignment details before deletion
        $assignment = $this->projectassignee_model->get($id);

        if (!$assignment) {
            echo json_encode([
                'success' => false,
                'message' => _l('project_assignment_not_found')
            ]);
            return;
        }

        // Check if user has permission to delete (staff member's own assignment or admin)
        if (!is_admin() && $assignment->staff_id != get_staff_user_id()) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied')
            ]);
            return;
        }

        // Check for dependencies before allowing deletion
        $dependency_check = $this->projectassignee_model->check_dependencies($id);

        if (!$dependency_check['can_delete']) {
            $reasons_html = '<ul>';
            foreach ($dependency_check['reasons'] as $reason) {
                $reasons_html .= '<li>' . $reason . '</li>';
            }
            $reasons_html .= '</ul>';

            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete this project assignment',
                'reasons' => $dependency_check['reasons'],
                'reasons_html' => $reasons_html
            ]);
            return;
        }

        // No dependencies found, safe to delete
        $success = $this->projectassignee_model->delete($id);

        if ($success) {
            log_activity('Project Assignment Deleted [ID: ' . $id . ', Staff ID: ' . $assignment->staff_id . ', Project ID: ' . $assignment->project_id . ']');
            echo json_encode([
                'success' => true,
                'message' => _l('deleted', _l('project_assignment'))
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('problem_deleting', _l('project_assignment'))
            ]);
        }
    }

    public function save_pay()
    {
        $this->load->model('staff_pay_model');

        if ($this->input->post()) {
            $data = $this->input->post();
            $is_ajax = $this->input->is_ajax_request();

            // Log the incoming data for debugging
            log_activity('Save Pay - Incoming Data: ' . json_encode($data));

            // Clean up custom allowances - convert empty values to 0
            if (isset($data['custom_allowances']) && is_array($data['custom_allowances'])) {
                foreach ($data['custom_allowances'] as $key => $value) {
                    // Convert empty strings or null to 0
                    if (empty($value) && $value !== '0' && $value !== 0) {
                        $data['custom_allowances'][$key] = 0;
                    } else {
                        $data['custom_allowances'][$key] = floatval($value);
                    }
                }
                log_activity('Save Pay - Cleaned Custom Allowances: ' . json_encode($data['custom_allowances']));
            }

            if (isset($data['id']) && $data['id']) {
                $success = $this->staff_pay_model->update($data['id'], $data);

                // Log the result
                if ($success) {
                    log_activity('Save Pay - Update Success for Pay ID: ' . $data['id']);

                    if ($is_ajax) {
                        echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('pay'))]);
                        return;
                    }
                    set_alert('success', _l('updated_successfully', _l('pay')));
                } else {
                    $db_error = $this->db->error();
                    log_activity('Save Pay - Update Failed for Pay ID: ' . $data['id'] . ' - Error: ' . json_encode($db_error));

                    if ($is_ajax) {
                        echo json_encode(['success' => false, 'message' => _l('problem_updating', _l('pay')) . ' - ' . $db_error['message']]);
                        return;
                    }
                    set_alert('danger', _l('problem_updating', _l('pay')) . ' - ' . $db_error['message']);
                }
            } else {
                $pay_id = $this->staff_pay_model->add($data);

                if ($pay_id) {
                    log_activity('Save Pay - Add Success. New Pay ID: ' . $pay_id);

                    if ($is_ajax) {
                        echo json_encode(['success' => true, 'message' => _l('added_successfully', _l('pay'))]);
                        return;
                    }
                    set_alert('success', _l('added_successfully', _l('pay')));
                } else {
                    $db_error = $this->db->error();
                    log_activity('Save Pay - Add Failed - Error: ' . json_encode($db_error));

                    if ($is_ajax) {
                        echo json_encode(['success' => false, 'message' => _l('problem_adding', _l('pay')) . ' - ' . $db_error['message']]);
                        return;
                    }
                    set_alert('danger', _l('problem_adding', _l('pay')) . ' - ' . $db_error['message']);
                }
            }

            if (!$is_ajax) {
                redirect(admin_url('staff/member/' . $data['staff_id'] . '?tab=staff_pay'));
            }
        }
    }

    public function pay_table($staff_id)
    {
        if (!is_numeric($staff_id)) {
            ajax_access_denied();
        }

        $this->app->get_table_data('staff_pay', [
            'staff_id' => $staff_id
        ]);
    }

    public function get_pay($id)
    {
        $this->load->model('staff_pay_model');
        $pay = $this->staff_pay_model->get_with_allowances($id);

        echo json_encode($pay);
    }

    public function get_current_pay($staff_id)
    {
        if (!is_numeric($staff_id)) {
            echo json_encode(['error' => 'Invalid staff ID']);
            return;
        }

        $this->load->model('staff_pay_model');
        $this->load->model('staff_model');

        // Get the most recent pay record based on start_date
        $current_pay = $this->db
            ->where('staff_id', $staff_id)
            ->order_by('start_date', 'DESC')
            ->limit(1)
            ->get(db_prefix() . 'staffpay')
            ->row();

        // Get staff member info for skills
        $staff = $this->staff_model->get($staff_id);

        $response = [
            'has_pay' => !empty($current_pay),
            'basic_pay' => $current_pay ? $current_pay->basic_pay : '',
            'overtime_pay' => $current_pay ? $current_pay->overtime_pay : '',
            'payout_type' => $current_pay ? $current_pay->payout_type : 'hourly',
            'skills' => $staff ? $staff->professiontype_id : ''
        ];

        echo json_encode($response);
    }

    /**
     * Get applicable allowances for a staff member based on their employee type
     * Uses OR logic - returns allowances assigned to ANY of: Staff Type, Company Type, or Profession Type
     * @param int $staff_id
     */
    public function get_applicable_allowances($staff_id)
    {
        if (!is_numeric($staff_id)) {
            echo json_encode(['error' => 'Invalid staff ID']);
            return;
        }

        $this->load->model('staff_model');
        $this->load->model('allowance_assignments_model');

        $staff = $this->staff_model->get($staff_id);

        if (!$staff) {
            echo json_encode(['error' => 'Staff not found']);
            return;
        }

        $allowances = [];
        $allowance_ids = []; // Track IDs to avoid duplicates

        // Priority 1: Get allowances assigned to this employee's staff type
        if (!empty($staff->stafftype_id)) {
            $staff_type_allowances = $this->allowance_assignments_model
                ->get_by_employee_type('staff_type', $staff->stafftype_id);
            foreach ($staff_type_allowances as $allowance) {
                if (!in_array($allowance['id'], $allowance_ids)) {
                    $allowances[] = $allowance;
                    $allowance_ids[] = $allowance['id'];
                }
            }
        }

        // Priority 2: Get allowances assigned to this employee's company type
        if (!empty($staff->companytype_id)) {
            $company_allowances = $this->allowance_assignments_model
                ->get_by_employee_type('company_type', $staff->companytype_id);
            foreach ($company_allowances as $allowance) {
                if (!in_array($allowance['id'], $allowance_ids)) {
                    $allowances[] = $allowance;
                    $allowance_ids[] = $allowance['id'];
                }
            }
        }

        // Priority 3: Get allowances assigned to this employee's profession type
        if (!empty($staff->professiontype_id)) {
            $profession_allowances = $this->allowance_assignments_model
                ->get_by_employee_type('profession_type', $staff->professiontype_id);
            foreach ($profession_allowances as $allowance) {
                if (!in_array($allowance['id'], $allowance_ids)) {
                    $allowances[] = $allowance;
                    $allowance_ids[] = $allowance['id'];
                }
            }
        }

        echo json_encode(['success' => true, 'allowances' => $allowances]);
    }

    public function save_file()
    {
        $this->load->model('staff_file_model');

        if ($this->input->post()) {
            $data = $this->input->post();
            $staff_id = $data['staff_id'];

            // Handle upload
            if (!empty($_FILES['file']['name'])) {
                $upload_path = FCPATH . 'uploads/staff_files/' . $staff_id . '/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = '*';
                $config['encrypt_name']  = FALSE;

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('file')) {
                    set_alert('danger', $this->upload->display_errors());
                    redirect(admin_url('staff/member/' . $staff_id . '?tab=staff_files'));
                } else {
                    $upload_data = $this->upload->data();
                    $data['file_name'] = $upload_data['orig_name']; // keep original uploaded filename
                    $data['file_path'] = 'uploads/staff_files/' . $staff_id . '/' . $upload_data['file_name']; // physical path
                }
            }

            if (isset($data['id']) && $data['id']) {
                $success = $this->staff_file_model->update($data['id'], $data);
                set_alert($success ? 'success' : 'danger',
                    $success ? _l('updated_successfully', _l('file')) : _l('problem_updating', _l('file'))
                );
            } else {
                if ($this->staff_file_model->add($data)) {
                    set_alert('success', _l('added_successfully', _l('file')));
                } else {
                    set_alert('danger', _l('problem_adding', _l('file')));
                }
            }

            redirect(admin_url('staff/member/' . $staff_id . '?tab=staff_files'));
        }
    }

    public function file_table($staff_id)
    {
        if (!is_numeric($staff_id)) {
            ajax_access_denied();
        }

        $this->app->get_table_data('staff_files', [
            'staff_id' => $staff_id
        ]);
    }

    public function get_file($id)
    {
        $this->load->model('staff_file_model');
        $file = $this->staff_file_model->get($id);
        echo json_encode($file);
    }

    public function download_file($id)
    {
        $this->load->model('staff_file_model');
        $file = $this->staff_file_model->get($id);

        if ($file && file_exists(FCPATH . $file->file_path)) {
            // Get staff details
            $this->load->model('staff_model');
            $staff = $this->staff_model->get($file->staff_id);

            // Build custom filename: EMPCODE_NAME_IQAMA_DOCUMENTTYPE
            $emp_code   = !empty($staff->code) ? $staff->code : 'NOCODE';
            $full_name  = !empty($staff->name) ? $staff->name : 'NONAME';
            $iqama      = !empty($staff->iqama_number) ? $staff->iqama_number : 'NOIQAMA';
            $doc_type   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $file->document_type);

            $download_name = $emp_code . '_' . $full_name . '_' . $iqama . '_' . $doc_type;

            // Preserve original file extension
            $ext = pathinfo($file->file_name, PATHINFO_EXTENSION);
            if ($ext) {
                $download_name .= '.' . $ext;
            }

            // Load file contents
            $data = file_get_contents(FCPATH . $file->file_path);

            // Force browser to download with our custom filename
            $this->load->helper('download');
            force_download($download_name, $data);
        } else {
            set_alert('danger', 'File not found');
            if ($file) {
                redirect(admin_url('staff/member/' . $file->staff_id . '?tab=staff_files'));
            } else {
                redirect(admin_url('staff'));
            }
        }
    }

    public function delete_file($id)
    {
        if (!is_admin() && !has_permission('staff', '', 'delete')) {
            access_denied('Staff Files');
        }

        $this->load->model('staff_file_model');
        $file = $this->staff_file_model->get($id);

        if (!$file) {
            set_alert('danger', 'File not found');
            redirect(admin_url('staff'));
            return;
        }

        $staff_id = $file->staff_id;

        if ($this->staff_file_model->delete($id)) {
            set_alert('success', 'File deleted successfully');
        } else {
            set_alert('danger', 'Failed to delete file');
        }

        redirect(admin_url('staff/member/' . $staff_id . '?tab=staff_files'));
    }

    public function add_skill()
    {
        $name = $this->input->post('name');
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'No skill name provided']);
            return;
        }
    
        $CI = &get_instance();
    
        // Check if skill already exists
        $exists = $CI->db->where('name', $name)->get(db_prefix().'skills')->row();
        if ($exists) {
            echo json_encode(['success' => true, 'id' => $exists->id, 'name' => $exists->name]);
            return;
        }
    
        // Insert new skill
        $CI->db->insert(db_prefix().'skills', ['name' => $name]);
        $id = $CI->db->insert_id();
    
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    }
    
    
    public function add_company_type()
    {
        $name = trim($this->input->post('name', true));
    
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'No company type name provided']);
            return;
        }
    
        // If exists, return the existing id
        $exists = $this->db->where('name', $name)->get(db_prefix() . 'companytype')->row();
        if ($exists) {
            echo json_encode(['success' => true, 'id' => (int)$exists->id, 'name' => $exists->name]);
            return;
        }
    
        // Insert
        $this->db->insert(db_prefix() . 'companytype', ['name' => $name]);
        $id = (int)$this->db->insert_id();
    
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    }
    
    
    
    public function add_ownemployee_type()
    {
        $name = trim($this->input->post('name', true));
    
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'No Own Employee Type name provided']);
            return;
        }
    
        // If exists, return the existing id
        $exists = $this->db->where('name', $name)->get(db_prefix() . 'ownemployeetype')->row();
        if ($exists) {
            echo json_encode(['success' => true, 'id' => (int)$exists->id, 'name' => $exists->name]);
            return;
        }
    
        // Insert
        $this->db->insert(db_prefix() . 'ownemployeetype', ['name' => $name]);
        $id = (int)$this->db->insert_id();
    
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    }

    public function add_staff_type()
    {
        $name = trim($this->input->post('name', true));
    
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'No Staff Type name provided']);
            return;
        }
    
        // If exists, return the existing id
        $exists = $this->db->where('name', $name)->get(db_prefix() . 'stafftype')->row();
        if ($exists) {
            echo json_encode(['success' => true, 'id' => (int)$exists->id, 'name' => $exists->name]);
            return;
        }
    
        // Insert
        $this->db->insert(db_prefix() . 'stafftype', ['name' => $name]);
        $id = (int)$this->db->insert_id();
    
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    }

    public function export_master_data()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }

        $this->load->helper('staff_excel'); // we’ll put logic in helper for reuse
        export_staff_master_data();
    }

    public function download_import_template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Excel headers (ALL CAPS, same as export)
        $headers = [
            'SL NO', 'COMPANY NAME', 'CLIENT/SITE', 'TYPE OF EMPLOYEE', 'EMP #', 'NAME OF THE EMPLOYEE',
            'IQAMA NO', 'HIJRI IQAMA EXP', 'IQAMA EXP', 'JOINING DATE', 'CONTRACT PERIOD (MONTHS)',
            'CONTRACT START DATE', 'CONTRACT MATURITY DATE', 'LAST CONTRACT RENEWAL DATE',
            'VACATION DUE DATE', 'REVIEW', 'QIWA EXPIRY', 'PASSPORT NO', 'NATIONALITY',
            'PASSPORT EXP', 'DOB', 'PROFESSION', 'CONTACT NO', 'HOME ADDRESS',
            'MAIL ID', 'IBAN/ACCOUNT NO', 'BANK NAME', 'BASIC', 'OT RATE',
            'FAT ALLOWANCE', 'SITE ALLOWANCE', 'OTHER ALLOWANCE', 'TOTAL', 'ADVANCE',
            'LAST SALARY REV DATE', 'COMMENTS', 'ARAMCO ID#', 'ARAMCO ID EXPIRY',
            'ACCOMODATION', 'STATUS', 'VISA NO', 'BORDER NO', 'INSURANCE EXPIRY'
        ];

        // Style header row (bold + light gray fill)
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E5E5'] // light gray
            ]
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray($headerStyle);
            $col++;
        }

        // Auto-size columns
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $filename = 'Staff Import Template.xlsx';

        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Translate text using Google Translate API
     */
    public function translate()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $text = $this->input->post('text');
        $target_lang = $this->input->post('target_lang'); // 'ar' for Arabic, 'en' for English

        if (empty($text)) {
            echo json_encode(['success' => false, 'message' => 'No text provided']);
            return;
        }

        try {
            // Use Google Translate API via HTTP
            $translated = $this->translate_text($text, $target_lang);

            echo json_encode([
                'success' => true,
                'translated_text' => $translated
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Translation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Helper function to translate text using Google Translate
     */
    private function translate_text($text, $target_lang)
    {
        // Using Google Translate's public API endpoint
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=' . $target_lang . '&dt=t&q=' . urlencode($text);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception($error);
        }

        $result = json_decode($response, true);

        if (isset($result[0][0][0])) {
            return $result[0][0][0];
        }

        throw new Exception('Unable to parse translation response');
    }

    /**
     * Convert Gregorian date to Hijri date
     */
    public function convert_to_hijri()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $gregorian_date = $this->input->post('date');

        if (empty($gregorian_date)) {
            echo json_encode(['success' => false, 'message' => 'No date provided']);
            return;
        }

        try {
            $this->load->helper('hijri_date');
            $hijri_date = gregorian_to_hijri($gregorian_date);

            echo json_encode([
                'success' => true,
                'hijri_date' => $hijri_date
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Date conversion failed: ' . $e->getMessage()
            ]);
        }
    }

}
