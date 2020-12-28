<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");

class Model_SP_pelanggan extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
    }

    public function listSP_pelanggan($search = null, $limit = null)
    {
    	/*$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];*/
        $cabang = $this->session->userdata('cabang');
        $query = $this->db->query("SELECT * FROM trs_faktur WHERE Cabang = '$cabang' and Status <> 'Batal'  $search order by NoFaktur DESC $limit ");
        
        return $query;
    
    }

    function detailsp_pelanggan($no){
        $cabang = $this->session->userdata('cabang');
        $query = $this->db->query("SELECT * FROM trs_faktur_detail WHERE Cabang = '$cabang' and NoFaktur = '$no' ");
        
        return $query;

    }

    function headersp_pelanggan($no){
        $cabang = $this->session->userdata('cabang');
        $query = $this->db->query("SELECT * FROM trs_faktur WHERE Cabang = '$cabang' and NoFaktur = '$no'");
        
        return $query;

    }

    function update_sp($data){
        $cabang = $this->session->userdata('cabang');
        $no_sp = explode("||", str_replace("undefined||", "",  substr($data['no_sp'], 0,-2)));
        $no_do = explode("||", str_replace("undefined||", "",  substr($data['no'], 0,-2)));
        
        $no = 0;
        
         foreach($no_sp as $i) {
            $this->db->set("NoSP",$i);
            $this->db->where("Cabang",$cabang);
            $this->db->where("NoFaktur",$no_do[$no]);
            $result = $this->db->update("trs_faktur");
            $no++;
        }
        return $result;

    }

    function update_sp_one($no,$no_sp){
        $cabang = $this->session->userdata('cabang');
        
            $this->db->set("NoSP",$no_sp);
            $this->db->where("Cabang",$cabang);
            $this->db->where("NoFaktur",$no);
            $result = $this->db->update("trs_faktur");
            
        return $result;

    }

    

 }