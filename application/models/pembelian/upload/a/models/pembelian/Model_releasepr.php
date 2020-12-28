<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");
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
            $query = $this->db->query("select No_Usulan as noUsulan, Prinsipal, Supplier, Value_Usulan, Status_Usulan as status from trs_usulan_beli_header where Status_Usulan = 'Usulan' and Cabang = '".$this->session->userdata('cabang')."' $byID");
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

    public function listDataPR()
    {   
        // $query = $this->db->query("select * from trs_pembelian_header where  Cabang = '".$this->session->userdata('cabang')."' order by Tgl_PR DESC")->result();
        $query = $this->db->query("SELECT trs_pembelian_header.No_PR,
                                       trs_pembelian_header.Tipe,
                                       trs_pembelian_header.Tgl_PR,
                                       trs_pembelian_header.No_Usulan,
                                       trs_pembelian_header.Prinsipal,
                                       trs_pembelian_header.Supplier,
                                       trs_pembelian_header.Supplier_2,
                                       trs_pembelian_header.Total_PR,
                                       trs_pembelian_header.Status_PR
                                FROM trs_pembelian_header
                                WHERE trs_pembelian_header.Cabang ='".$this->session->userdata('cabang')."'
                                UNION ALL

                                SELECT trs_pembelian_pusat_header.NoDokumen AS 'No_PR',
                                       trs_pembelian_pusat_header.Tipe AS 'Tipe',
                                       trs_pembelian_pusat_header.TglDokumen AS 'Tgl_PR',
                                       trs_pembelian_pusat_header.NoUsulan AS 'No_Usulan',
                                       trs_pembelian_pusat_header.Prinsipal AS 'prinsipal',
                                       trs_pembelian_pusat_header.Supplier AS 'Supplier',
                                       '' AS 'Supplier_2',
                                       trs_pembelian_pusat_header.Total AS 'Total_PR',
                                       trs_pembelian_pusat_header.Status AS 'Status_PR'
                                FROM trs_pembelian_pusat_header
                                WHERE trs_pembelian_pusat_header.Cabang ='".$this->session->userdata('cabang')."'
                                ORDER BY Tgl_PR DESC")->result();
        return $query;
    }
    public function listDataPRPO()
    {   
        $query = $this->db->query("select Tipe, Tgl_PO,No_PO as noPO, No_PR as no, No_Usulan as noUsulan, Prinsipal, Supplier, Total_PO, Status_PO as status,flag_suratjalan from trs_po_header where Status_PO in ('Open','OpenGab','Closed') and Cabang = '".$this->session->userdata('cabang')."' order by Tgl_PO DESC,Prinsipal,NO_PO ASC")->result();

        return $query;
    }


    public function DataPR($no = NULL)
    {   
        $query = $this->db->query("select * from trs_pembelian_detail where No_PR = '".$no."'")->result();

        return $query;
    }

    public function dataPRPO($no = NULL)
    {
        $query = $this->db->query("select * from trs_po_detail where No_PO = '".$no."'")->result();

        return $query;
    }  

    public function updateDataPRPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $daynumber = date('d');
            if($daynumber <= 5){
                $curclose  = date('Y-m-d',strtotime("-10 days"));
            }else if($daynumber > 5 and $daynumber < 15){
                $curclose  = date('Y-m-d',strtotime("-15 days"));
            }else if($daynumber >= 15 and $daynumber < 20){
                $curclose  = date('Y-m-d',strtotime("-21 days"));
            }else if($daynumber >= 20 and $daynumber < 25){
                $curclose  = date('Y-m-d',strtotime("-26 days"));
            }else if($daynumber >= 25){
                $curclose  = date('Y-m-d',strtotime("-32 days"));
            }
            $satubulan_awal = date('Y-m-01',strtotime($curclose));
            $pr2 = $this->db2->query("select * from trs_pembelian_header where Cabang='".$this->session->userdata('cabang')."' and tgl_PR between '".$satubulan_awal."' and '".date('Y-m-d')."'")->result();
            // $sp2 = $this->db2->query("select * from trs_pembelian_pusat_header where Cabang='".$this->session->userdata('cabang')."' and TglDokumen between '".$satubulan_awal."' and '".date('Y-m-d')."'")->result();
            foreach($pr2 as $r2) {
                $cekpr2 = $this->db->query("select * from trs_pembelian_header where No_PR = '".$r2->No_PR."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr2 == 0) {
                    $this->db->insert('trs_pembelian_header', $r2); // insert each row to another table
                }
                // else{
                //     $this->db->where('No_PR', $r2->No_PR);
                //     $this->db->update('trs_pembelian_header', $r2);
                // }
            }
            $pr = $this->db2->query("select * from trs_pembelian_detail where Cabang='".$this->session->userdata('cabang')."' and tgl_PR between '".$satubulan_awal."' and '".date('Y-m-d')."' ")->result();  
            // $sp = $this->db2->query("select * from trs_pembelian_pusat_detail where Cabang='".$this->session->userdata('cabang')."' and TglDokumen between '".$satubulan_awal."' and '".date('Y-m-d')."' ")->result();        
            foreach($pr as $r) {
                $cekpr = $this->db->query("select * from trs_pembelian_detail where No_PR = '".$r->No_PR."' and Produk = '".$r->Produk."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr == 0) {
                    $this->db->insert('trs_pembelian_detail', $r); // insert each row to another table
                }
                // else{
                //     $this->db->where('Produk', $r->Produk);
                //     $this->db->where('No_PR', $r->No_PR);
                //     $this->db->update('trs_pembelian_detail', $r);
                // }
            }
            // foreach($sp as $p) {
            //     $ceksp = $this->db->query("select * from trs_pembelian_pusat_detail where NoDokumen = '".$p->NoDokumen."' and Produk = '".$p->Produk."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
            //     if ($ceksp == 0) {
            //         $this->db->insert('trs_pembelian_pusat_detail', $p); // insert each row to another table
            //     }
            //     else{
            //         $this->db->where('Produk', $p->Produk);
            //         $this->db->where('NoDokumen', $p->NoDokumen);
            //         $this->db->update('trs_pembelian_pusat_detail', $p);
            //     }
            // }

            
            // foreach($sp2 as $p2) {
            //     $ceksp2 = $this->db->query("select * from trs_pembelian_pusat_header where NoDokumen = '".$p2->NoDokumen."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
            //     if ($ceksp2 == 0) {
            //         $this->db->insert('trs_pembelian_pusat_header', $p2); // insert each row to another table
            //     }
            //     else{
            //         $this->db->where('NoDokumen', $p2->NoDokumen);
            //         $this->db->update('trs_pembelian_pusat_header', $p2);
            //     }
            // }
           
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

   public function updateDataPOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            // $daynumber = date('d');
            // if($daynumber <= 5){
            //     $curclose  = date('Y-m-d',strtotime("-10 days"));
            //     $satubulan_awal = date('Y-m-01',strtotime($curclose));
            // }else if($daynumber > 5 and $daynumber < 15){
            //     $curclose  = date('Y-m-d',strtotime("-15 days"));
            //     $satubulan_awal = date('Y-m-01',strtotime($curclose));
            // }else if($daynumber >= 15 and $daynumber < 20){
            //     $curclose  = date('Y-m-d',strtotime("-21 days"));
            //     $satubulan_awal = date('Y-m-01',strtotime($curclose));
            // }else if($daynumber >= 20 and $daynumber < 25){
            //     $curclose  = date('Y-m-d',strtotime("-26 days"));
            //     $satubulan_awal = date('Y-m-d',strtotime($curclose));
            // }else if($daynumber >= 25){
            //     $curclose  = date('Y-m-d',strtotime("-32 days"));
            //     $satubulan_awal = date('Y-m-d',strtotime($curclose));
            // }
            $satubulan_awal  = date('Y-m-d',strtotime("-32 days"));
            // $satubulan_awal = date('Y-m-d',strtotime($curclose));
            

            
            // UPDATE USULAN SURAT JALAN PUSAT
            $sp = $this->db2->query("select * from trs_pembelian_pusat_detail where Cabang='".$this->session->userdata('cabang')."' and TglDokumen between '".$satubulan_awal."' and '".date('Y-m-d')."' and status = 'Closed' ")->result();     
            $sp2 = $this->db2->query("select * from trs_pembelian_pusat_header where Cabang='".$this->session->userdata('cabang')."' and TglDokumen between '".$satubulan_awal."' and '".date('Y-m-d')."' and status = 'Closed' ")->result();
            foreach($sp as $p) {
                $ceksp = $this->db->query("select * from trs_pembelian_pusat_detail where NoDokumen = '".$p->NoDokumen."' and Produk = '".$p->Produk."' and Cabang='".$this->session->userdata('cabang')."' and Status ='Closed'")->num_rows();
                if ($ceksp == 0) {
                    $this->db->insert('trs_pembelian_pusat_detail', $p); // insert each row to another table
                }
                else{
                    $this->db->where('Produk', $p->Produk);
                    $this->db->where('NoDokumen', $p->NoDokumen);
                    $this->db->update('trs_pembelian_pusat_detail', $p);
                }
            }
            foreach($sp2 as $p2) {
                $ceksp2 = $this->db->query("select * from trs_pembelian_pusat_header where NoDokumen = '".$p2->NoDokumen."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($ceksp2 == 0) {
                    $this->db->insert('trs_pembelian_pusat_header', $p2); // insert each row to another table
                }
                else{
                    $this->db->where('NoDokumen', $p2->NoDokumen);
                    $this->db->update('trs_pembelian_pusat_header', $p2);
                }
            }
            // UPDATE DATA PO
            $po2 = $this->db2->query("select * from trs_po_header where Cabang='".$this->session->userdata('cabang')."' and tgl_po between '".$satubulan_awal."' and '".date('Y-m-d')."'")->result();
            foreach($po2 as $p2) {
                $cekpo2 = $this->db->query("select * from trs_po_header where No_PO = '".$p2->No_PO."'  and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpo2 == 0) {
                    $this->db->insert('trs_po_header', $p2); 
                }
                else{
                    $this->db->set("Status_PO", $p2->Status_PO);
                    $this->db->set("flag_suratjalan", $p2->flag_suratjalan);
                    $this->db->where('No_PR', $p2->No_PR);
                    $this->db->where('No_PO', $p2->No_PO);
                    $this->db->where('Status_PO', 'Open');
                    $this->db->update('trs_po_header');
                }
                $po = $this->db2->query("select * from trs_po_detail where Cabang='".$this->session->userdata('cabang')."' and No_PO ='".$p2->No_PO."'")->result();
                foreach($po as $p) {
                    $cekpo = $this->db->query("select * from trs_po_detail where No_PO = '".$p->No_PO."' and No_PR = '".$p->No_PR."' and Produk = '".$p->Produk."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                    if ($cekpo == 0) {
                        $this->db->insert('trs_po_detail', $p); // insert each row to another table
                    }
                    else{
                        $this->db->where('Produk', $p->Produk);
                        $this->db->where('No_PR', $p->No_PR);
                        $this->db->where('No_PO', $p->No_PO);
                        $this->db->where("Status_PO", 'Open');
                        $this->db->update('trs_po_detail', $p);
                    }
                }
            }
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }
    public function listDataPRPOClosed()
    {   
        $query = $this->db->query("SELECT DISTINCT trs_po_header.Tipe, 
                                          trs_po_header.Tgl_PO,
                                          trs_po_header.No_PO AS noPO, 
                                          trs_po_header.No_PR AS NO, 
                                          trs_po_header.No_Usulan AS noUsulan, 
                                          trs_po_header.Prinsipal, 
                                          trs_po_header.Supplier, 
                                          trs_po_header.Total_PO, 
                                          trs_po_header.Status_PO AS STATUS 
                                    FROM trs_po_header JOIN trs_po_detail ON 
                                     trs_po_header.`No_PO` = trs_po_detail.`No_PO` AND 
                                     trs_po_header.`Cabang` = trs_po_detail.`Cabang`
                                    WHERE LEFT(trs_po_header.No_PO,2) ='PO' AND (trs_po_header.Status_PO IN ('Open','OpenGab') OR IFNULL(trs_po_detail.`statusGIT`,'') = 'Open') AND
                                    trs_po_header.Cabang = '".$this->session->userdata('cabang')."'
                                    ORDER BY trs_po_header.Tgl_PO DESC,trs_po_header.Prinsipal,trs_po_header.NO_PO ASC;")->result();
        return $query;
    }

    public function closedataPO($no = NULL)
    {
        $this->db->set("Status_PO", 'Closed');
        $this->db->set("modified_time", date('Y-m-d H:i:s'));
        $this->db->set("modified_user", $this->session->userdata('username'));
        $this->db->where("No_PO", $no);
        $valid = $this->db->update('trs_po_header');

        $this->db->query("update trs_po_detail
                        set Status_PO='Closed',
                            StatusGIT ='Closed'
                        where No_PO ='".$no."'");

        // $this->db->set("Status_PO", 'Closed');
        // $this->db->set("modified_time", date('Y-m-d H:i:s'));
        // $this->db->set("modified_user", $this->session->userdata('username'));
        // $this->db->where("No_PO", $no);
        // $valid = $this->db->update('trs_po_detail');

        if($valid){
            $this->db2 = $this->load->database('pusat', TRUE);      
            if ($this->db2->conn_id == TRUE) {
                $this->db2->set("Status_PO", 'Closed');
                $this->db2->set("modified_time", date('Y-m-d H:i:s'));
                $this->db2->set("modified_user", $this->session->userdata('username'));
                $this->db2->where("No_PO", $no);
                $this->db2->update('trs_po_header');

                $this->db->query("update trs_po_detail
                                   set Status_PO='Closed',
                                       StatusGIT ='Closed'
                                   where No_PO ='".$no."' and cabang ='".$this->session->userdata('cabang')."'");
            }
        }
        return $valid;
    }
    public function updateDataPOPusat1($nopo=null)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            // UPDATE USULAN SURAT JALAN PUSAT
            $sp = $this->db2->query("select * from trs_pembelian_pusat_detail where Cabang='".$this->session->userdata('cabang')."' and nodokumen = '".$nopo."' and status = 'Closed' ")->result();     
            $sp2 = $this->db2->query("select * from trs_pembelian_pusat_header where Cabang='".$this->session->userdata('cabang')."' and nodokumen = '".$nopo."' and status = 'Closed' ")->result();
            foreach($sp as $p) {
                $ceksp = $this->db->query("select * from trs_pembelian_pusat_detail where NoDokumen = '".$p->NoDokumen."' and Produk = '".$p->Produk."' and Cabang='".$this->session->userdata('cabang')."' and Status ='Closed'")->num_rows();
                if ($ceksp == 0) {
                    $this->db->insert('trs_pembelian_pusat_detail', $p); // insert each row to another table
                }
                else{
                    $this->db->where('Produk', $p->Produk);
                    $this->db->where('NoDokumen', $p->NoDokumen);
                    $this->db->update('trs_pembelian_pusat_detail', $p);
                }
            }
            foreach($sp2 as $p2) {
                $ceksp2 = $this->db->query("select * from trs_pembelian_pusat_header where NoDokumen = '".$p2->NoDokumen."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($ceksp2 == 0) {
                    $this->db->insert('trs_pembelian_pusat_header', $p2); // insert each row to another table
                }
                else{
                    $this->db->where('NoDokumen', $p2->NoDokumen);
                    $this->db->update('trs_pembelian_pusat_header', $p2);
                }
            }
            // UPDATE DATA PO
            $po = $this->db2->query("select * from trs_po_detail where Cabang='".$this->session->userdata('cabang')."' and no_po = '".$nopo."'")->result();            
            foreach($po as $p) {
                $cekpo = $this->db->query("select * from trs_po_detail where No_PO = '".$nopo."' and No_PR = '".$p->No_PR."' and Produk = '".$p->Produk."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpo == 0) {
                    $this->db->insert('trs_po_detail', $p); // insert each row to another table
                }
                else{
                    $this->db->where('Produk', $p->Produk);
                    $this->db->where('No_PR', $p->No_PR);
                    $this->db->where('No_PO', $p->No_PO);
                    $this->db->where("Status_PO", 'Open');
                    $this->db->update('trs_po_detail', $p);
                }
            }

            $po2 = $this->db2->query("select * from trs_po_header where Cabang='".$this->session->userdata('cabang')."' and no_po = '".$nopo."'")->result();
            foreach($po2 as $p2) {
                $cekpo2 = $this->db->query("select * from trs_po_header where No_PO = '".$nopo."'  and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpo2 == 0) {
                    $this->db->insert('trs_po_header', $p2); // insert each row to another table
                }
                else{
                    $this->db->set("Status_PO", $p2->Status_PO);
                    $this->db->set("flag_suratjalan", $p2->flag_suratjalan);
                    $this->db->where('No_PR', $p2->No_PR);
                    $this->db->where('No_PO', $p2->No_PO);
                    $this->db->where('Status_PO', 'Open');
                    $this->db->update('trs_po_header');
                }
            }
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }
}