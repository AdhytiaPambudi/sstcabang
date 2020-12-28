<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_releasepr extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
    }

    public function listData($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and No_Usulan = '".$no."'";
        }
        if ($this->session->userdata('userGroup') == 'Apotik') {   
            $query = $this->db->query("select No_Usulan as noUsulan, Prinsipal, Supplier, Value_Usulan, Status_Usulan as status from trs_usulan_beli_header where Status_Usulan = 'Usulan' and Cabang = '".$this->session->userdata('cabang')."' $byID");
        }
        else {   
            $query = $this->db->query("select No_Usulan as noUsulan, Prinsipal, Supplier, Value_Usulan, Status_Usulan as status from trs_usulan_beli_header where Status_Usulan = 'Approval Cabang' and Cabang = '".$this->session->userdata('cabang')."' $byID");
        }

        return $query;
    }

    // public function getData($no = null)
    // {   
    //     $byID = "";
    //     if(!empty($no)){
    //         $byID = "and b.No_Usulan = '".$no."'";
    //     }
    //     $query = $this->db->query("select a.*, b.Value_Usulan as Total, b.Dokumen, b.Dokumen_2, b.Keperluan, b.Nama_Kep, b.Alamat_Kep, b.App_APJ_Cabang, b.App_APJC_Time, b.App_APJC_Alasan, b.App_BM_Status, b.App_BM_Time, b.App_BM_Alasan, b.App_RBM_Status, b.App_RBM_Time, b.App_RBM_Alasan, b.Limit_Beli from trs_usulan_beli_detail a, trs_usulan_beli_header b where b.Status_Usulan = 'Usulan' and b.Cabang = '".$this->session->userdata('cabang')."' and b.`No_Usulan`=a.`No_Usulan` $byID");

    //     return $query;
    // }

    public function releaseData($no = null)
    {
        $this->db2 = $this->load->database('pusat', TRUE);      
        $valid = false;
        $x = 1;
        $piutang = 0;
        // $array_bulan = array(1=>"I","II","III", "IV", "V","VI","VII","VIII","IX","X", "XI","XII");
        // $bulan = $array_bulan[date('n')];

        // $this->db->set("Status_Usulan", 'Approval Cabang');
        // $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
        // $this->db->set("Modified_User", $this->session->userdata('username'));
        // $this->db->where("No_Usulan", $no);
        // $valid = $this->db->update('trs_usulan_beli_detail');
        if ($this->session->userdata('userGroup') == 'Apotik') {   
            if ($this->db2->conn_id == TRUE) {   
                $this->db2->set("Status_Usulan", 'Approval Cabang');
                $this->db2->set("App_APJ_Cabang", 'Approve');            
                $this->db2->set("App_APJC_Time", date('Y-m-d H:i:s'));
                $this->db2->set("modified_time", date('Y-m-d H:i:s'));
                $this->db2->set("modified_user", $this->session->userdata('username'));
                $this->db2->where("No_Usulan", $no);
                $valid = $this->db2->update('trs_usulan_beli_header');
                $this->db->set("StatusPusat", 'Berhasil');
            }
            else{
                $this->db->set("StatusPusat", 'Gagal');            
            }
            $this->db->set("Status_Usulan", 'Approval Cabang');
            $this->db->set("App_APJ_Cabang", 'Approve');            
            $this->db->set("App_APJC_Time", date('Y-m-d H:i:s'));
            $this->db->set("modified_time", date('Y-m-d H:i:s'));
            $this->db->set("modified_user", $this->session->userdata('username'));
            $this->db->where("No_Usulan", $no);
            $valid = $this->db->update('trs_usulan_beli_header');
        }
        else if ($this->session->userdata('userGroup') == 'BM') {   
            if ($this->db2->conn_id == TRUE) {   
                $this->db2->set("Status_Usulan", 'Approval BM');
                $this->db2->set("App_APJ_Cabang", 'Approve');            
                $this->db2->set("App_APJC_Time", date('Y-m-d H:i:s'));
                $this->db2->set("modified_time", date('Y-m-d H:i:s'));
                $this->db2->set("modified_user", $this->session->userdata('username'));
                $this->db2->where("No_Usulan", $no);
                $valid = $this->db2->update('trs_usulan_beli_header');
                $this->db->set("StatusPusat", 'Berhasil');
            }
            else{
                $this->db->set("StatusPusat", 'Gagal');            
            }
            $this->db->set("Status_Usulan", 'Approval BM');
            $this->db->set("App_BM_Status", 'Approve');            
            $this->db->set("App_BM_Time", date('Y-m-d H:i:s'));
            $this->db->set("modified_time", date('Y-m-d H:i:s'));
            $this->db->set("modified_user", $this->session->userdata('username'));
            $this->db->where("No_Usulan", $no);
            $valid = $this->db->update('trs_usulan_beli_header');
        }
        	  
    }  

    public function rejectData($no = NULL, $alasan = NULL)
    {
            $this->db->set("Status_Usulan", 'Tolak');
            $this->db->set("App_APJ_Cabang", 'Tolak');            
            $this->db->set("App_APJC_Time", date('Y-m-d H:i:s')); 
            $this->db->set("App_APJC_Alasan", $alasan);
            $this->db->set("modified_time", date('Y-m-d H:i:s'));
            $this->db->set("modified_user", $this->session->userdata('username'));
            $this->db->where("No_Usulan", $no);
            $valid = $this->db->update('trs_usulan_beli_header');
    }

    public function dataUsulan($no = NULL)
    {
        $query = $this->db->query("select * from trs_usulan_beli_detail where No_Usulan = '".$no."'")->result();

        return $query;
    }  

    public function listDataPRPO()
    {   
        $query = $this->db->query("select Tipe, No_PO as noPO, No_PR as no, No_Usulan as noUsulan, Prinsipal, Supplier, Total_PO, Status_PO as status from trs_po_header where Status_PO in ('Open','OpenGab') and Cabang = '".$this->session->userdata('cabang')."'")->result();

        return $query;
    }

    public function dataPRPO($no = NULL)
    {
        $query = $this->db->query("select * from trs_po_detail where No_PO = '".$no."'")->result();

        return $query;
    }  

    public function updateDataPOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {

            $pr = $this->db2->query("select * from trs_pembelian_detail where Cabang='".$this->session->userdata('cabang')."'")->result();            
            foreach($pr as $r) {
                $cekpr = $this->db->query("select * from trs_pembelian_detail where No_PR = '".$r->No_PR."' and Produk = '".$r->No_PR."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr == 0) {
                    $this->db->insert('trs_pembelian_detail', $r); // insert each row to another table
                }
                else{
                    $this->db->where('Produk', $r->Produk);
                    $this->db->where('No_PR', $r->No_PR);
                    $this->db->update('trs_pembelian_detail', $r);
                }
            }

            $pr2 = $this->db2->query("select * from trs_pembelian_header where Cabang='".$this->session->userdata('cabang')."'")->result();
            foreach($pr2 as $r2) {
                $cekpr2 = $this->db->query("select * from trs_pembelian_header where No_PR = '".$r2->No_PR."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr2 == 0) {
                    $this->db->insert('trs_pembelian_header', $r2); // insert each row to another table
                }
                else{
                    $this->db->where('No_PR', $r2->No_PR);
                    $this->db->update('trs_pembelian_header', $r2);
                }
            }

            // UPDATE DATA PO
            $po = $this->db2->query("select * from trs_po_detail where Cabang='".$this->session->userdata('cabang')."'")->result();            
            foreach($po as $p) {
                $cekpo = $this->db->query("select * from trs_po_detail where No_PO = '".$p->No_PO."' and No_PR = '".$p->No_PR."' and Produk = '".$p->No_PO."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpo == 0) {
                    $this->db->insert('trs_po_detail', $p); // insert each row to another table
                }
                else{
                    $this->db->where('Produk', $p->Produk);
                    $this->db->where('No_PR', $p->No_PR);
                    $this->db->where('No_PO', $p->No_PO);
                    $this->db->update('trs_po_detail', $r);
                }
            }

            $po2 = $this->db2->query("select * from trs_po_header where Cabang='".$this->session->userdata('cabang')."'")->result();
            foreach($po2 as $p2) {
                $cekpo2 = $this->db->query("select * from trs_po_header where No_PO = '".$p2->No_PO."' and No_PR = '".$p2->No_PR."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpo2 == 0) {
                    $this->db->insert('trs_po_header', $p2); // insert each row to another table
                }
                else{
                    $this->db->where('No_PR', $p2->No_PR);
                    $this->db->where('No_PO', $p2->No_PO);
                    $this->db->update('trs_po_header', $p2);
                }
            }

            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }
}