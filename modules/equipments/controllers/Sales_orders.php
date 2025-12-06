<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_orders extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipments/equipment_sales_orders_model');
        $this->load->model('equipments/equipments_model');
        $this->load->language('equipments/equipments');
    }

    /**
     * List all sales orders
     */
    public function index()
    {
        if (!has_permission('equipment_sales_orders', '', 'view')) {
            access_denied('Equipment Sales Orders');
        }

        $data['title'] = _l('equipment_sales_orders');
        $this->load->view('admin/sales_orders/manage', $data);
    }

    /**
     * Get sales orders for DataTables
     */
    public function table()
    {
        if (!has_permission('equipment_sales_orders', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/sales_orders'));
    }

    /**
     * Add new sales order
     */
    public function add()
    {
        if (!has_permission('equipment_sales_orders', '', 'create')) {
            access_denied('Equipment Sales Orders');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Process items
            if (isset($data['equipment_id']) && is_array($data['equipment_id'])) {
                $items = [];
                foreach ($data['equipment_id'] as $key => $equipment_id) {
                    $quantity = $data['quantity'][$key];
                    $unit_rate = $data['unit_rate'][$key];
                    $rental_period = $data['rental_period_months'][$key];

                    $items[] = [
                        'equipment_id' => $equipment_id,
                        'operator_id' => !empty($data['operator_id'][$key]) ? $data['operator_id'][$key] : null,
                        'quantity' => $quantity,
                        'rental_period_months' => $rental_period,
                        'unit_rate' => $unit_rate,
                        'line_total' => $quantity * $unit_rate * $rental_period,
                        'fulfillment_status' => 'pending',
                        'fulfilled_quantity' => 0,
                    ];
                }
                $data['items'] = $items;
            }

            $insert_id = $this->equipment_sales_orders_model->add($data);

            if ($insert_id) {
                set_alert('success', _l('added_successfully', _l('sales_order')));
                redirect(admin_url('equipments/sales_orders/view/' . $insert_id));
            } else {
                set_alert('danger', _l('problem_adding', _l('sales_order')));
            }
        }

        $data['title'] = _l('add_new_sales_order');
        $data['clients'] = $this->clients_model->get();
        $data['equipments'] = $this->equipments_model->get();
        $data['quotation_id'] = $this->input->get('quotation_id');

        if ($data['quotation_id']) {
            $this->load->model('equipments/equipment_quotations_model');
            $data['quotation'] = $this->equipment_quotations_model->get($data['quotation_id']);
        }

        $this->load->view('admin/sales_orders/form', $data);
    }

    /**
     * Edit sales order
     */
    public function edit($id)
    {
        if (!has_permission('equipment_sales_orders', '', 'edit')) {
            access_denied('Equipment Sales Orders');
        }

        $data['order'] = $this->equipment_sales_orders_model->get($id);

        if (!$data['order']) {
            show_404();
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();

            // Process items
            if (isset($post_data['equipment_id']) && is_array($post_data['equipment_id'])) {
                $items = [];
                foreach ($post_data['equipment_id'] as $key => $equipment_id) {
                    $quantity = $post_data['quantity'][$key];
                    $unit_rate = $post_data['unit_rate'][$key];
                    $rental_period = $post_data['rental_period_months'][$key];

                    $items[] = [
                        'equipment_id' => $equipment_id,
                        'operator_id' => !empty($post_data['operator_id'][$key]) ? $post_data['operator_id'][$key] : null,
                        'quantity' => $quantity,
                        'rental_period_months' => $rental_period,
                        'unit_rate' => $unit_rate,
                        'line_total' => $quantity * $unit_rate * $rental_period,
                    ];
                }
                $post_data['items'] = $items;
            }

            $success = $this->equipment_sales_orders_model->update($id, $post_data);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('sales_order')));
                redirect(admin_url('equipments/sales_orders/view/' . $id));
            } else {
                set_alert('danger', _l('problem_updating', _l('sales_order')));
            }
        }

        $data['title'] = _l('edit_sales_order');
        $data['clients'] = $this->clients_model->get();
        $data['equipments'] = $this->equipments_model->get();

        $this->load->view('admin/sales_orders/form', $data);
    }

    /**
     * View sales order details
     */
    public function view($id)
    {
        if (!has_permission('equipment_sales_orders', '', 'view')) {
            access_denied('Equipment Sales Orders');
        }

        $data['order'] = $this->equipment_sales_orders_model->get($id);

        if (!$data['order']) {
            show_404();
        }

        $data['title'] = _l('sales_order') . ' - ' . $data['order']->order_number;

        $this->load->view('admin/sales_orders/view', $data);
    }

    /**
     * Delete sales order
     */
    public function delete($id)
    {
        if (!has_permission('equipment_sales_orders', '', 'delete')) {
            ajax_access_denied();
        }

        $success = $this->equipment_sales_orders_model->delete($id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('sales_order'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('sales_order'))]);
        }
    }

    /**
     * Create sales order from quotation
     */
    public function create_from_quotation($quotation_id)
    {
        if (!has_permission('equipment_sales_orders', '', 'create')) {
            access_denied('Equipment Sales Orders');
        }

        $order_id = $this->equipment_sales_orders_model->create_from_quotation($quotation_id);

        if ($order_id) {
            set_alert('success', _l('sales_order_created_from_quotation'));
            redirect(admin_url('equipments/sales_orders/view/' . $order_id));
        } else {
            set_alert('danger', _l('problem_creating_sales_order'));
            redirect(admin_url('equipments/quotations/view/' . $quotation_id));
        }
    }

    /**
     * Change order status
     */
    public function change_status($id)
    {
        if (!has_permission('equipment_sales_orders', '', 'edit')) {
            ajax_access_denied();
        }

        $status = $this->input->post('status');

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_sales_orders', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => _l('status_updated_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_updating_status')]);
        }
    }
}
