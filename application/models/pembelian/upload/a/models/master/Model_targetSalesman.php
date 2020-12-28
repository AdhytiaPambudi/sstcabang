<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");

class Model_targetSalesman extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
    }

    public function listDataTargetSalesman()
    {
    	$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT *,MONTH(Tanggal) bulan,YEAR(Tanggal) tahun FROM mst_target_salesman_baru where  MONTH(Tanggal) = '$bulan' and year(Tanggal) = '$tahun' and Cabang = '$cabang' order by id ASC ")->result(); 

        return $query;
    }

    public function listDataLapTargetSalesman()
    {
       $tahun = $_POST['tahun'];
       $bulan = $_POST['bulan'];
       $prinsipal = $_POST['prinsipal'];

        $whereprinsipal = $wherebulan = $wheretahun ="";

        if ($bulan != "-") {
            $wheretahun = "AND  MONTH(Tanggal) = '$bulan'";
        }

        if ($tahun != "-") {
            $wheretahun =" and year(Tanggal) = '$tahun'";
        }

        if ($prinsipal != "-") {
            $whereprinsipal =" and Prinsipal = '$prinsipal'";
        }
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT *,MONTH(Tanggal) bulan,YEAR(Tanggal) tahun FROM mst_target_salesman_baru where  Cabang = '$cabang' $whereprinsipal $wherebulan $wheretahun order by id ASC ")->result(); 

        return $query;
    }

    public function getDataTargetSalesman_Pivot()
    {
       $tahun = $_POST['tahun'];
       $bulan = $_POST['bulan'];
       $cabang = $_POST['cabang'];

        $whereprinsipal = $wherebulan = $wheretahun ="";

        if ($bulan != "-") {
            $wherebulan = "AND  MONTH(Tanggal) = '$bulan'";
        }

        if ($tahun != "-") {
            $wheretahun =" and year(Tanggal) = '$tahun'";
        }

       
        // $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT *,Target as Value FROM mst_target_salesman_baru where  Cabang = '$cabang'  $wherebulan $wheretahun order by id ASC ")->result(); 

        return $query;
    }

    function get_field_header($cabang){
        $query = $this->db->query("SELECT Prinsipal FROM mst_target_salesman_baru WHERE Cabang = '$cabang'
                GROUP BY Prinsipal ORDER BY Prinsipal");
        return $query;
    }

    public function listDataLapTargetSalesmanPivot()
    {
      /* $tahun = $_POST['tahun'];
       $bulan = $_POST['bulan'];

        $whereprinsipal = $wherebulan = $wheretahun ="";

        if ($bulan != "-") {
            $wheretahun = "AND  MONTH(Tanggal) = '$bulan'";
        }

        if ($tahun != "-") {
            $wheretahun =" and year(Tanggal) = '$tahun'";
        }*/

        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("CALL target_salesman(2019,07,'".$cabang."') ")->result(); 

        return $query;
    }

    public function listDataLapTargetSalesmanPivot_count()
    {
        $tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];

         $wherebulan = $wheretahun ="";

        if ($bulan != "-") {
            $wheretahun = "AND  MONTH(Tanggal) = '$bulan'";
        }

        if ($tahun != "-") {
            $wheretahun =" and year(Tanggal) = '$tahun'";
        }
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("CALL target_salesman(2019,07,'".$cabang."') "); 

        return $query;
    }

    public function listDataTargetSalesman_count()
    {
    	$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT sum(Target) total FROM mst_target_salesman_baru where  MONTH(Tanggal) = '$bulan' and year(Tanggal) = '$tahun' and Cabang = '$cabang' order by id ASC "); 

        return $query;
    }

    public function listDataLapTargetSalesman_count()
    {
        $tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];

         $wherebulan = $wheretahun ="";

        if ($bulan != "-") {
            $wheretahun = "AND  MONTH(Tanggal) = '$bulan'";
        }

        if ($tahun != "-") {
            $wheretahun =" and year(Tanggal) = '$tahun'";
        }
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT sum(Target) total FROM mst_target_salesman_baru where  Cabang = '$cabang' $wherebulan $wheretahun order by id ASC "); 

        return $query;
    }

    function listCabang(){
    	$query = $this->db->query("SELECT Cabang FROM mst_target_salesman WHERE Cabang IS NOT NULL GROUP BY Cabang"); 

        return $query;	
    }

    function listPrinsipal(){
    	$query = $this->db->query("SELECT Prinsipal FROM mst_prinsipal"); 

        return $query;	
    }

    function LaplistPrinsipal(){
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT Prinsipal FROM mst_target_salesman_baru WHERE Cabang = '$cabang' GROUP BY Prinsipal"); 

        return $query;  
    }

    function get_KodeSalesman($Cabang/*,$bulan,$tahun*/){
    	/*$query = "SELECT a.Kode,a.Tanggal,b.Tipe_Salesman, b.Nama, b.MCL FROM mst_target_salesman a JOIN mst_karyawan b
					ON a.Kode = b.Kode WHERE a.Cabang = '$Cabang' AND YEAR(Tanggal) = '$tahun' AND MONTH(Tanggal) = '$bulan'";*/
        $query = "SELECT Kode,Nama,Tipe_Salesman,MCL FROM `mst_karyawan` where Jabatan = 'Salesman' and `Status` = 'Aktif' and Cabang = '$Cabang'";
        return $this->db->query($query);

    }

    public function saveDataTargetSalesman($params = NULL){
        // print_r($params->Cabang);
        $valid = false;
        foreach ($params->prinsipal as $key => $value) 
        {
                $this->db->set("Cabang", $params->Cabang);
                $this->db->set("KodeSalesman", $params->KodeSalesman);
                $this->db->set("NamaSalesman", $params->NamaSalesman);
                $this->db->set("TipeSalesman", $params->TipeSalesman);
                $this->db->set("Mcl", $params->Mcl);
                $this->db->set("Tanggal", $params->tgl);
                $this->db->set("Prinsipal", $params->prinsipal[$key]);
                $this->db->set("Target", $params->target[$key]);
                $this->db->set("created_by", $this->session->userdata('username'));
                $valid = $this->db->insert('mst_target_salesman_baru');
                /*$this->db->set("cabang", $this->cabang);
                $this->db->set("tanggal", date("Y-m-d"));
                $this->db->set("time_trans", date("Y-m-d H:i:s"));
                $this->db->set("No_Usulan", $noDokumen);
                $this->db->set("Tipe_trans", $params->tipe_trans);
                $this->db->set("Prinsipal", $kodePrins);
                $this->db->set("Prinsipal2", $namaPrins);
                $this->db->set("Supplier", $params->Supplier);
                $this->db->set("KodeProduk", $produk);
                $this->db->set("NamaProduk", $namaProduk);
                $this->db->set("tglmulai", $params->tgl1);
                $this->db->set("tglakhir",$params->tgl2);
                $this->db->set("qty1", $params->qty1[$key]);
                $this->db->set("qty2", $params->qty2[$key]);
                $this->db->set("disc_prins1", $params->dscprins1[$key]);
                $this->db->set("disc_prins2", $params->dscprins2[$key]);
                $this->db->set("disc_cab", $params->dsccab1[$key]);
                $this->db->set("KodePelanggan", $kodePelanggan);
                $this->db->set("NamaPelanggan", $namaPelanggan);
                $this->db->set("status", "Open");
                $this->db->set("status_pelanggan", $params->jmlpelanggan);
                $this->db->set("created_by", $this->session->userdata('username'));
                $this->db->set("created_at", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("mst_usulan_disc_prinsipal");
                if($valid){
                    $this->prosesDataUsulanDiscPrins($noDokumen,$produk);
                }*/
            
        }
       

        return $valid;
    }

    function save_lama($params = NULL){
    $valid = false;
        $this->db->set("Cabang", $params['Cabang']);
        $this->db->set("KodeSalesman", $params['KodeSalesman']);
        $this->db->set("NamaSalesman", $params['NamaSalesman']);
        $this->db->set("TipeSalesman", $params['TipeSalesman']);
        $this->db->set("Mcl", $params['Mcl']);
        $this->db->set("Tanggal", $params['tgl']);
        $this->db->set("Prinsipal", $params['Prinsipal']);
        $this->db->set("Target", $params['Target']);
        $this->db->set("created_by", $this->session->userdata('username'));
        $valid = $this->db->insert('mst_target_salesman_baru');

        return $valid;
    
    }

     public function updateTargetSalesman($params = NULL){
        
        $valid = false;
        // $this->db->set("Prinsipal", $params['Prinsipal']);
        $this->db->set("Target", $params['Target']);
        $this->db->set("updated_at", date('Y-m-d H:i:s'));
        $this->db->set("updated_by", $this->session->userdata('username'));
        $this->db->set("statusPusat", 'N');
        $this->db->where('id', $params['id']);
        $valid = $this->db->update('mst_target_salesman_baru');

        return $valid;
    }

    public function deleteTargetSalesman($id = NULL){
        $query = $this->db->query("delete FROM mst_target_salesman_baru where id =".$id); 

        return $query;
    }

     public function getDataTargetSalesman($id = NULL){
        $query = $this->db->query("select *,MONTH(Tanggal) bulan,YEAR(Tanggal) tahun FROM mst_target_salesman_baru where id =".$id)->row(); 

        return $query;
    }
    
    function getTahun(){
        $query = $this->db->query("SELECT distinct year(Tanggal) tahun from mst_target_salesman_baru ORDER BY tahun DESC ");

         return $query;
    }

    public function uploadTargetSalesman($id = NULL){
    	$this->db1 = $this->load->database('pusat', TRUE);
    	
    	$data = $this->db->query("select * FROM mst_target_salesman_baru where id ='$id'")->row(); 

    	$cek = $this->db1->query("SELECT * FROM mst_target_salesman_baru WHERE Cabang = '$data->Cabang' and KodeSalesman
    		= '$data->KodeSalesman' and Tanggal = '$data->Tanggal' and Prinsipal = '$data->Prinsipal' ");

    	if ($cek->num_rows() > 0) {
    		$query = $this->db1->query("DELETE FROM mst_target_salesman_baru where Cabang = '$data->Cabang' and KodeSalesman
    		= '$data->KodeSalesman' and Tanggal = '$data->Tanggal' and Prinsipal = '$data->Prinsipal'"); 
    	}
        $valid = false;
        $this->db1->set("Cabang", $data->Cabang);
        $this->db1->set("KodeSalesman", $data->KodeSalesman);
        $this->db1->set("NamaSalesman", $data->NamaSalesman);
        $this->db1->set("TipeSalesman", $data->TipeSalesman);
        $this->db1->set("Mcl", $data->Mcl);
        $this->db1->set("Tanggal", $data->Tanggal);
        $this->db1->set("Prinsipal", $data->Prinsipal);
        $this->db1->set("Target", $data->Target);
        $this->db1->set("statusPusat", 'Y');
        $this->db1->set("created_at", $data->created_at);
        $this->db1->set("created_by", $data->created_by);
        $this->db1->set("updated_at", $data->updated_at);
        $this->db1->set("updated_by", $data->updated_by);
        $this->db1->set("posting_at", date('Y-m-d H:i:s'));
        $this->db1->set("posting_by", $this->session->userdata('username'));
        $valid = $this->db1->insert('mst_target_salesman_baru');

        $this->db->set("statusPusat", 'Y');
        $this->db->set("posting_at", date('Y-m-d H:i:s'));
        $this->db->set("posting_by", $this->session->userdata('username'));
        $this->db->where('id', $id);
        $this->db->update('mst_target_salesman_baru');

        return $valid;
    }

    public function uploadTargetSalesman_all(){
    	$this->db1 = $this->load->database('pusat', TRUE);
    	
    	$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];
        $cabang = $this->session->userdata('cabang');

    	$data = $this->db->query("SELECT * FROM mst_target_salesman_baru where  MONTH(Tanggal) = '$bulan' and year(Tanggal) = '$tahun' and Cabang = '$cabang'")->result(); 

	     $valid = false;
    	foreach ($data as $data) {
    		# code...
	    	$cek = $this->db1->query("SELECT * FROM mst_target_salesman_baru WHERE Cabang = '$data->Cabang' and KodeSalesman
	    		= '$data->KodeSalesman' and Tanggal = '$data->Tanggal' and Prinsipal = '$data->Prinsipal' ");

	    	if ($cek->num_rows() > 0) {
	    		$query = $this->db1->query("DELETE FROM mst_target_salesman_baru where Cabang = '$data->Cabang' and KodeSalesman
	    		= '$data->KodeSalesman' and Tanggal = '$data->Tanggal' and Prinsipal = '$data->Prinsipal' "); 
	    	}

	        $this->db1->set("Cabang", $data->Cabang);
	        $this->db1->set("KodeSalesman", $data->KodeSalesman);
	        $this->db1->set("NamaSalesman", $data->NamaSalesman);
	        $this->db1->set("TipeSalesman", $data->TipeSalesman);
	        $this->db1->set("Mcl", $data->Mcl);
	        $this->db1->set("Tanggal", $data->Tanggal);
	        $this->db1->set("Prinsipal", $data->Prinsipal);
	        $this->db1->set("Target", $data->Target);
	        $this->db1->set("statusPusat", 'Y');
	        $this->db1->set("created_at", $data->created_at);
	        $this->db1->set("created_by", $data->created_by);
	        $this->db1->set("updated_at", $data->updated_at);
	        $this->db1->set("updated_by", $data->updated_by);
            $this->db1->set("posting_at", date('Y-m-d H:i:s'));
            $this->db1->set("posting_by", $this->session->userdata('username'));
	        $valid = $this->db1->insert('mst_target_salesman_baru');

	        $this->db->set("statusPusat", 'Y');
            $this->db->set("posting_at", date('Y-m-d H:i:s'));
            $this->db->set("posting_by", $this->session->userdata('username'));
	        $this->db->where('id', $data->id);
	        $this->db->update('mst_target_salesman_baru');
    	}/*
    	echo "SELECT * FROM mst_target_salesman_baru WHERE Cabang = '$data->Cabang' and KodeSalesman
	    		= '$data->KodeSalesman' and Tanggal = '$data->Tanggal' and Prinsipal = '$data->Prinsipal' ";
    	exit();*/
        return $valid;
    }

    function getExportTargetSalesman($prinsipal,$tahun,$bulan){
        $cabang = $this->session->userdata('cabang');
        $whereprinsipal = $wheretahun = $wherebulan = "";

        if ($prinsipal != "-") {
            $whereprinsipal = " AND Prinsipal = '$prinsipal'";
        }

        if ($tahun != "-") {
            $wheretahun = " AND year(Tanggal) = '$tahun'";
        }

        if ($bulan != "-") {
            $wherebulan = " AND MONTH(Tanggal) = '$bulan'";
        }

         return $this->db->query("SELECT * FROM mst_target_salesman_baru where  Cabang = '$cabang' $whereprinsipal  $wheretahun  $wherebulan order by tanggal");
    }

    function cek_targetSalesman($params = NULL){

        $query =  $this->db->query("SELECT Prinsipal FROM mst_target_salesman_baru where  Cabang = '$params->cabang' AND KodeSalesman = '$params->KodeSalesman' AND Tanggal = '$params->tgl'");

         return $query;
    }
    

 }