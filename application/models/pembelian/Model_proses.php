<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_proses extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }

    public function listDataDOPending($search = NULL, $length = NULL, $start = NULL)
    {
        $query = $this->db->query("select trs_sales_order.*,trs_delivery_order_sales.`NoSO` AS NOSOd from trs_sales_order LEFT JOIN `trs_delivery_order_sales`  ON trs_sales_order.`NoSO`=`trs_delivery_order_sales`.`NoSO` where (trs_sales_order.NoSO like '%".$search."%' or trs_sales_order.NoDO like '%".$search."%' or trs_sales_order.TglSO like '%".$search."%' or trs_sales_order.Pelanggan like '%".$search."%') and trs_sales_order.NoDO is not null and (trs_sales_order.NoFaktur is null or trs_sales_order.NoFaktur = '') HAVING NOSOd IS NULL");
        
        return $query;
    }

    public function prosesData($No = NULL, $status = NULL)
    {
        $cek = $this->db->query("select * from trs_sales_order where NoSO = '".$No."'")->row();  
        $byStatus = "";
        $statusLimit = "";
        $statusTOP = "";
        $statusDC = "";
        $statusDP = "";
        $statusApprove = "";
        if ($status == "Limit") {
            if ($this->group == "BM") {
                $statusLimit = "Ok";
                $statusApprove = "Limit Approval BM";                
                if ($cek->StatusTOP == "Ok" and $cek->StatusDiscCab == "Ok" and $cek->StatusDiscPrins == "Ok") {
                    $byStatus = ", Status = 'Closed'";
                }
            }
            $update = $this->db->query("update trs_sales_order set StatusLimit = '".$statusLimit."', statusPusat = 'Gagal' $byStatus where NoSO = '".$No."'");
        }
        elseif ($status == "TOP") {
            if ($this->group == "KSA") {
                $statusTOP = "TOP Approval KSA";
                $statusApprove = "TOP Approval KSA";
            }
            elseif ($this->group == "BM") {
                $statusTOP = "Ok";
                $statusApprove = "TOP Approval BM";                
                if ($cek->StatusLimit == "Ok" and $cek->StatusDiscCab == "Ok" and $cek->StatusDiscPrins == "Ok") {
                    $byStatus = ", Status = 'Closed'";
                }
            }

            $update = $this->db->query("update trs_sales_order set StatusTOP = '".$statusTOP."',statusPusat = 'Gagal'$byStatus where NoSO = '".$No."'");            
        }
        elseif ($status == "DC") {
            if ($this->group == "BM") {
                $statusDC = "Ok";
                $statusApprove = "DC Approval BM";                
                if ($cek->StatusTOP == "Ok" and $cek->StatusLimit == "Ok" and $cek->StatusDiscPrins == "Ok") {
                    $byStatus = ", Status = 'Closed'";
                }
            }
            $update = $this->db->query("update trs_sales_order set StatusDiscCab = '".$statusDC."', statusPusat = 'Gagal' $byStatus where NoSO = '".$No."'");
        }
        elseif ($status == "DP") {
            if ($this->group == "BM") {
                $statusDP = "Ok";
                $statusApprove = "DP Approval BM";                
                if ($cek->StatusTOP == "Ok" and $cek->StatusDiscCab == "Ok" and $cek->StatusLimit == "Ok") {
                    $byStatus = ", Status = 'Closed'";
                }
            }
            $update = $this->db->query("update trs_sales_order set StatusDiscPrins = '".$statusDP."', statusPusat = 'Gagal' $byStatus where NoSO = '".$No."'");
        }

        $this->db->set("Cabang", $this->cabang);
        $this->db->set("Dokumen", "Sales Order");
        $this->db->set("NoDokumen", $No);
        $this->db->set("Status", $statusApprove);
        $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        $valid =  $this->db->insert("trs_approval");

        return $update;
    }

    public function listDataUsulanCN($status='')
    {
        $byStatus = '';
        if (!empty($status)) {
            $byStatus = "and Status = '".$status."' and NoUsulan is not null";
        }

        $query = $this->db->query("select * from trs_faktur_cndn where Cabang = '".$this->cabang."' $byStatus")->result();
        
        return $query;
    }

    public function prosesDataUsulanCN($No = NULL)
    {
        $this->db->set("Status", 'CNOK');
        $this->db->set("StatusCNDN", 'Disetujui');
        $this->db->set("updated_by", $this->user);
        $this->db->set("updated_at", date("Y-m-d H:i:s"));
        $this->db->where("NoDokumen", $No);
        $valid =  $this->db->update("trs_faktur_cndn");
        return $valid;
    }
}