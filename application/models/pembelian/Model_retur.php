<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_retur extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->cabang = $this->session->userdata('cabang');
            $this->logs = $this->session->all_userdata();
            $this->logged = $this->session->userdata('userLogged');
            $this->userGroup = $this->session->userdata('userGroup');
            $this->content = array(
                "base_url" => base_url(),
                "tgl" => date("Y-m-d"),
                "logs" => $this->session->all_userdata(),
                "logged" => $this->session->userdata('userLogged'),
            );
    }

      public function listData()
    {   
        $query = $this->db->query("select Cabang, Kode, Pelanggan, Alamat from mst_pelanggan where Status_Retur = 'request' and Kode <> ''")->result();

        return $query;
    }

    public function prosesData($Kode = null, $act = null)
    {
        $valid = false;
        if ($act == 'Approve')
            $status = 'ya'; 
        else
            $status = null;
        if($this->userGroup == "BM"){
             $this->db->set("Status_Retur", $status); 
            $this->db->where("Kode", $Kode);
            $valid = $this->db->update('mst_pelanggan');
                $this->db2 = $this->load->database('pusat', TRUE);      
                if ($this->db2->conn_id == TRUE) {
                    $this->db2->set("Status_Retur", $status);
                    $this->db2->where("Cabang", $this->cabang);
                    $this->db2->where("Kode", $Kode);        
                    $valid = $this->db2->update("mst_pelanggan");
                }
        }
        return $valid;
    }   
}