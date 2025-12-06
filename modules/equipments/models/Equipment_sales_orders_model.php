<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_sales_orders_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get sales order by ID
     */
    public function get($id)
    {
        $this->db->select('so.*, c.company as client_company, p.name as project_name, s.firstname, s.lastname');
        $this->db->from(db_prefix() . 'equipment_sales_orders so');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = so.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = so.project_id', 'left');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = so.created_by', 'left');
        $this->db->where('so.id', $id);

        $order = $this->db->get()->row();

        if ($order) {
            $order->items = $this->get_order_items($id);
            $order->fulfillments = $this->get_order_fulfillments($id);
        }

        return $order;
    }

    /**
     * Get all sales orders
     */
    public function get_all($where = [])
    {
        $this->db->select('so.*, c.company as client_company');
        $this->db->from(db_prefix() . 'equipment_sales_orders so');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = so.client_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('so.order_date', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get order items
     */
    public function get_order_items($order_id)
    {
        $this->db->select('soi.*, e.name as equipment_name, e.serial_number, o.name as operator_name');
        $this->db->from(db_prefix() . 'equipment_sales_order_items soi');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = soi.equipment_id', 'left');
        $this->db->join(db_prefix() . 'operators o', 'o.id = soi.operator_id', 'left');
        $this->db->where('soi.sales_order_id', $order_id);

        return $this->db->get()->result();
    }

    /**
     * Get order fulfillments
     */
    public function get_order_fulfillments($order_id)
    {
        $this->db->select('sof.*, soi.equipment_id, e.name as equipment_name');
        $this->db->from(db_prefix() . 'equipment_sales_order_fulfillments sof');
        $this->db->join(db_prefix() . 'equipment_sales_order_items soi', 'soi.id = sof.sales_order_item_id', 'left');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = soi.equipment_id', 'left');
        $this->db->where('sof.sales_order_id', $order_id);

        return $this->db->get()->result();
    }

    /**
     * Add new sales order
     */
    public function add($data)
    {
        $data['order_number'] = $this->generate_order_number();
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        // Calculate totals
        if (isset($data['items']) && is_array($data['items'])) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = ($subtotal * $data['tax_rate']) / 100;
            $data['total_amount'] = $subtotal + $data['tax_amount'];
        }

        $items = isset($data['items']) ? $data['items'] : [];
        unset($data['items']);

        $this->db->insert(db_prefix() . 'equipment_sales_orders', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id && !empty($items)) {
            foreach ($items as $item) {
                $item['sales_order_id'] = $insert_id;
                $this->db->insert(db_prefix() . 'equipment_sales_order_items', $item);
            }
        }

        // Update quotation if linked
        if ($insert_id && !empty($data['quotation_id'])) {
            $this->db->where('id', $data['quotation_id']);
            $this->db->update(db_prefix() . 'equipment_client_quotations', [
                'sales_order_id' => $insert_id
            ]);
        }

        if ($insert_id) {
            log_activity('Sales Order Created [ID: ' . $insert_id . ', Number: ' . $data['order_number'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update sales order
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Recalculate totals if items provided
        if (isset($data['items']) && is_array($data['items'])) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = ($subtotal * $data['tax_rate']) / 100;
            $data['total_amount'] = $subtotal + $data['tax_amount'];

            $items = $data['items'];
            unset($data['items']);

            // Delete existing items
            $this->db->where('sales_order_id', $id);
            $this->db->delete(db_prefix() . 'equipment_sales_order_items');

            // Insert updated items
            foreach ($items as $item) {
                $item['sales_order_id'] = $id;
                $this->db->insert(db_prefix() . 'equipment_sales_order_items', $item);
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_sales_orders', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Sales Order Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete sales order
     */
    public function delete($id)
    {
        $order = $this->get($id);
        if (!$order) {
            return false;
        }

        // Check if order has fulfillments
        if (!empty($order->fulfillments)) {
            return false; // Cannot delete fulfilled orders
        }

        // Delete items
        $this->db->where('sales_order_id', $id);
        $this->db->delete(db_prefix() . 'equipment_sales_order_items');

        // Delete order
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'equipment_sales_orders');

        if ($this->db->affected_rows() > 0) {
            log_activity('Sales Order Deleted [ID: ' . $id . ', Number: ' . $order->order_number . ']');
            return true;
        }

        return false;
    }

    /**
     * Generate unique order number
     */
    public function generate_order_number()
    {
        $prefix = 'SO-' . date('Y');

        $this->db->select('order_number');
        $this->db->from(db_prefix() . 'equipment_sales_orders');
        $this->db->like('order_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        $result = $this->db->get()->row();

        if ($result) {
            $last_number = intval(substr($result->order_number, -5));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        return $prefix . '-' . str_pad($new_number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create sales order from quotation
     */
    public function create_from_quotation($quotation_id)
    {
        $this->load->model('equipments/equipment_quotations_model');
        $quotation = $this->equipment_quotations_model->get($quotation_id);

        if (!$quotation || $quotation->status != 'accepted') {
            return false;
        }

        // Check if already has sales order
        if (!empty($quotation->sales_order_id)) {
            return $quotation->sales_order_id;
        }

        $order_data = [
            'quotation_id' => $quotation_id,
            'client_id' => $quotation->client_id,
            'order_date' => date('Y-m-d'),
            'payment_terms_days' => $quotation->payment_terms_days,
            'currency' => $quotation->currency,
            'tax_rate' => 15.00,
            'status' => 'confirmed',
            'terms_conditions' => $quotation->terms_conditions,
            'notes' => $quotation->notes,
        ];

        // Add items from quotation
        if (!empty($quotation->items)) {
            $items = [];
            foreach ($quotation->items as $item) {
                $items[] = [
                    'equipment_id' => $item->equipment_id,
                    'quantity' => $item->quantity,
                    'rental_period_months' => $item->to_month ? ($item->to_month - $item->from_month + 1) : 1,
                    'unit_rate' => $item->rate,
                    'line_total' => $item->rate * $item->quantity,
                ];
            }
            $order_data['items'] = $items;
        }

        return $this->add($order_data);
    }

    /**
     * Update fulfillment status based on mobilizations
     */
    public function update_fulfillment_status($order_id)
    {
        $items = $this->get_order_items($order_id);

        $total_items = 0;
        $fulfilled_items = 0;

        foreach ($items as $item) {
            $total_items++;
            if ($item->fulfillment_status == 'fulfilled') {
                $fulfilled_items++;
            }
        }

        if ($fulfilled_items == 0) {
            $fulfillment_status = 'pending';
        } elseif ($fulfilled_items < $total_items) {
            $fulfillment_status = 'in_progress';
        } else {
            $fulfillment_status = 'completed';
        }

        $this->db->where('id', $order_id);
        $this->db->update(db_prefix() . 'equipment_sales_orders', [
            'fulfillment_status' => $fulfillment_status,
            'status' => $fulfillment_status == 'completed' ? 'fulfilled' : 'confirmed'
        ]);
    }
}
