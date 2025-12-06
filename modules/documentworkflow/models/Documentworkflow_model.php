<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Documentworkflow_model extends App_Model
{
    protected $documents_table = 'tbl_hr_documents';
    protected $letterheads_table = 'tbl_letterheads';
    protected $types_table = 'tbl_document_types';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_documents()
    {
        return $this->db->order_by('created_at', 'DESC')->get($this->documents_table)->result_array();
    }

    public function get_document($id)
    {
        return $this->db->where('id', $id)->get($this->documents_table)->row();
    }

    public function add_document($data)
    {
        $data['created_by'] = get_staff_user_id();
        $this->db->insert($this->documents_table, $data);
        return $this->db->insert_id();
    }

    public function update_document($id, $data)
    {
        $this->db->where('id', $id)->update($this->documents_table, $data);
        return $this->db->affected_rows();
    }

    public function delete_document($id)
    {
        $doc = $this->get_document($id);
        if ($doc && !empty($doc->pdf_file) && file_exists(FCPATH . $doc->pdf_file)) {
            @unlink(FCPATH . $doc->pdf_file);
        }
        $this->db->where('id', $id)->delete($this->documents_table);
        return $this->db->affected_rows();
    }

    public function get_letterheads()
    {
        return $this->db->order_by('id', 'desc')->get($this->letterheads_table)->result_array();
    }

    public function get_types()
    {
        return $this->db->order_by('label', 'asc')->get($this->types_table)->result_array();
    }

    public function add_type($data)
    {
        $this->db->insert($this->types_table, $data);
        return $this->db->insert_id();
    }

    public function update_type($id, $data)
    {
        $this->db->where('id', $id)->update($this->types_table, $data);
        return $this->db->affected_rows();
    }

    public function delete_type($id)
    {
        $this->db->where('id', $id)->delete($this->types_table);
        return $this->db->affected_rows();
    }

    public function generate_pdf($document_id)
    {
        $doc = $this->get_document($document_id);
        if (!$doc) {
            return false;
        }

        $letterhead = $this->db->where('id', $doc->letterhead_id)->get($this->letterheads_table)->row();
        $letterhead_path = $letterhead ? FCPATH . $letterhead->file : '';

        $pdf = new LetterheadPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setLetterhead($letterhead_path);

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();

        if (!empty($letterhead_path) && file_exists($letterhead_path)) {
            $ext = strtolower(pathinfo($letterhead_path, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $size = @getimagesize($letterhead_path);
                if ($size !== false) {
                    $img_w = $size[0];
                    $img_h = $size[1];
                    $page_w = 210;
                    $page_h = 297;
                    $scale = min($page_w / $img_w, $page_h / $img_h);
                    $w = $img_w * $scale;
                    $h = $img_h * $scale;
                    $x = ($page_w - $w) / 2;
                    $y = ($page_h - $h) / 2;
                    $pdf->Image($letterhead_path, $x, $y, $w, $h, '', '', '', false, 300, '', false, 0);
                } else {
                    log_message('warning', 'Could not get image size; stretching to full page');
                    $pdf->Image($letterhead_path, 0, 0, 210, 297, '', '', '', false, 300, '', false, 0);
                }
            } elseif ($ext === 'pdf') {
                try {
                    $pagecount = $pdf->setSourceFile($letterhead_path);
                    if ($pagecount > 0) {
                        $tpl = $pdf->importPage(1);
                        $pdf->useTemplate($tpl, 0, 0, 210, 297, true);
                    } else {
                        log_message('warning', 'PDF letterhead has no pages');
                    }
                } catch (Exception $e) {
                    log_message('error', 'PDF letterhead import failed: ' . $e->getMessage());
                }
            }
        } else {
            log_message('debug', 'No letterhead for PDF or file not found: ' . $letterhead_path);
        }

        $pdf->SetY(15);
        $leftMargin = 15;
        $rightMargin = 20;
        $topMargin = 45;
        $bottomMargin = 16;

        $pdf->SetMargins($leftMargin, $topMargin, $rightMargin);
        $pdf->SetAutoPageBreak(true, $bottomMargin);

        $html = '<div style="padding-left: '.$leftMargin.'mm; padding-right: '.$rightMargin.'mm; line-height:1.5;">'
            . $doc->content .
            '</div>';

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->writeHTML($html, true, false, true, false, '');

        $folder = 'uploads/documents/' . date('Y') . '/' . date('m') . '/';
        if (!is_dir(FCPATH . $folder)) {
            mkdir(FCPATH . $folder, 0755, true);
        }

        $fileName = $folder . time() . '_' . url_title($doc->title, '_', true) . '.pdf';
        $output_path = FCPATH . $fileName;
        $pdf->Output($output_path, 'F');

        $this->db->where('id', $document_id)->update($this->documents_table, [
            'pdf_file' => $fileName,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $fileName;
    }

    public function upload_letterhead()
    {
        if (empty($_FILES['letterhead_file']['name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        $config['upload_path'] = FCPATH . 'uploads/letterheads/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        $config['allowed_types'] = 'pdf|png|jpg|jpeg';
        $config['max_size'] = 20480;
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('letterhead_file')) {
            return ['success' => false, 'message' => $this->upload->display_errors()];
        }

        $f = $this->upload->data();
        $file_path = 'uploads/letterheads/' . $f['file_name'];
        $name = $this->input->post('name') ? $this->input->post('name') : $f['raw_name'];

        $this->db->insert($this->letterheads_table, [
            'name' => $name,
            'file' => $file_path,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return ['success' => true, 'file' => $file_path, 'id' => $this->db->insert_id()];
    }
}

class LetterheadPDF extends TCPDF {
    protected $letterheadPath;

    public function setLetterhead($path) {
        $this->letterheadPath = $path;
    }

    public function Header() {
        if (!empty($this->letterheadPath) && file_exists($this->letterheadPath)) {
            $ext = strtolower(pathinfo($this->letterheadPath, PATHINFO_EXTENSION));
            $bMargin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);

            if (in_array($ext, ['png','jpg','jpeg'])) {
                $this->Image($this->letterheadPath, 0, 0, 210, 297, '', '', '', false, 300, '', false, 0, false, false, 0);
            } elseif ($ext === 'pdf') {
                try {
                    $pagecount = $this->setSourceFile($this->letterheadPath);
                    if ($pagecount > 0) {
                        $tpl = $this->importPage(1);
                        $this->useTemplate($tpl, 0, 0, 210, 297, true);
                    }
                } catch (Exception $e) {
                    log_message('error', 'PDF letterhead import failed: ' . $e->getMessage());
                }
            }

            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
        }
    }
}