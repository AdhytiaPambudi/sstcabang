<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");

class Model_karyawan extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
    }

    public function listDataKaryawan()
    {
    	/*$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];*/
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT * FROM mst_karyawan WHERE Cabang = '$cabang' order by id ASC ")->result(); 

        return $query;
    }

 /*   public function listDataKaryawan_count()
    {
    	$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];
        $cabang = $this->session->userdata('cabang');

        $query = $this->db->query("SELECT sum(Target) total FROM mst_karyawan where  MONTH(Tanggal) = '$bulan' and year(Tanggal) = '$tahun' and Cabang = '$cabang' order by id ASC "); 

        return $query;
    }*/

    function listCabang(){
    	$query = $this->db->query("SELECT Cabang FROM mst_target_salesman WHERE Cabang IS NOT NULL GROUP BY Cabang"); 

        return $query;	
    }


    function get_rayon($Cabang){
    	$query = "SELECT Kode FROM mst_rayon WHERE Cabang = '$Cabang' order by Kode";
        return $this->db->query($query);

    }

    public function saveDataKaryawan($params = NULL){

        $valid = false;
        $cabang = $params['Cabang'];
        $kode = $params['Kode'];
        $jabatan = $params['Jabatan'];

        $chek= $this->db->query("SELECT Cabang, Kode FROM mst_karyawan where Cabang= '$cabang' AND Kode = '$kode' and Jabatan = '$jabatan'");

        if ($chek->num_rows() > 0) {
            return false;
        }else{



            $this->db->set("Cabang", $params['Cabang']);
            $this->db->set("Kode", $params['Kode']);
            $this->db->set("Nama", $params['Nama']);
            $this->db->set("Jabatan", $params['Jabatan']);
            $this->db->set("Tipe_Salesman", $params['Tipe_Salesman']);
            $this->db->set("Rayon_Salesman", $params['Rayon_Salesman']);
            $this->db->set("Supervisor", $params['Supervisor']);
            $this->db->set("Status", $params['Status']);
            $this->db->set("Menagih", $params['Menagih']);
            $this->db->set("Mengirim", $params['Mengirim']);
            $this->db->set("Jenis", $params['Jenis']);
            $this->db->set("MCL", $params['Mcl']);

            $this->db->set("Created_by", $this->session->userdata('username'));
            $valid = $this->db->insert('mst_karyawan');

            return true;
        }
    }

     public function updateKaryawan($params = NULL){
        $valid = false;
        // $this->db->set("Prinsipal", $params['Prinsipal']);
        $this->db->set("Nama", $params['Nama']);
        $this->db->set("Jabatan", $params['Jabatan']);
        // $this->db->set("Tipe_Salesman", $params['Tipe_Salesman']);
        $this->db->set("Rayon_Salesman", $params['Rayon_Salesman']);
        $this->db->set("Supervisor", $params['Supervisor']);
        $this->db->set("Status", $params['Status']);
        $this->db->set("Menagih", $params['Menagih']);
        $this->db->set("Mengirim", $params['Mengirim']);
        $this->db->set("Jenis", $params['Jenis']);
        $this->db->set("Email", $params['Email']);
        $this->db->set("Updated_at", date('Y-m-d H:i:s'));
        $this->db->set("Updated_by", $this->session->userdata('username'));
        $this->db->set("statusPusat", 'N');
        $this->db->where('id', $params['id']);
        
        $valid = $this->db->update('mst_karyawan');

        return $valid;
    }

    public function deleteKaryawan($id = NULL){
        $query = $this->db->query("delete FROM mst_karyawan where id =".$id); 

        return $query;
    }

     public function getDataKaryawan($id = NULL){
        $query = $this->db->query("SELECT * FROM mst_karyawan where id =".$id)->row(); 

        return $query;
    }


    public function uploadKaryawan($id = NULL){
    	$this->db1 = $this->load->database('pusat', TRUE);
    	
    	$data = $this->db->query("select * FROM mst_karyawan where id ='$id'")->row(); 

    	$cek = $this->db1->query("SELECT * FROM mst_karyawan WHERE Cabang = '$data->Cabang' and Kode
    		= '$data->Kode' and Jabatan = '$data->Jabatan' ");

    	if ($cek->num_rows() > 0) {
    		$query = $this->db1->query("DELETE FROM mst_karyawan where Cabang = '$data->Cabang' and Kode
    		= '$data->Kode' and Jabatan = '$data->Jabatan'"); 
    	}
        $valid = false;

        $this->db1->set("Cabang", $data->Cabang);
        $this->db1->set("Kode", $data->Kode);
        $this->db1->set("Nama", $data->Nama);
        $this->db1->set("Jabatan", $data->Jabatan);
        $this->db1->set("Tipe_Salesman", $data->Tipe_Salesman);
        $this->db1->set("Rayon_Salesman", $data->Rayon_Salesman);
        $this->db1->set("Supervisor", $data->Supervisor);
        $this->db1->set("Status", $data->Status);
        $this->db1->set("Menagih", $data->Menagih);
        $this->db1->set("Mengirim", $data->Mengirim);
        $this->db1->set("Jenis", $data->Jenis);
        $this->db1->set("MCL", $data->MCL);
        $this->db1->set("Created_at", $data->Created_at);
        $this->db1->set("Created_by", $data->Created_by);
        $this->db1->set("Updated_at", $data->Updated_at);
        $this->db1->set("Updated_by", $data->Updated_by);
        $this->db1->set("posting_at", date('Y-m-d H:i:s'));
        $this->db1->set("posting_by", $this->session->userdata('username'));
        $valid = $this->db1->insert('mst_karyawan');

        $this->db->set("statusPusat", 'Y');
        $this->db->where('id', $id);
        $this->db->update('mst_karyawan');

        return $valid;
    }

    public function uploadKaryawan_all(){
    	$this->db1 = $this->load->database('pusat', TRUE);
        
        $data = $this->db->query("select * FROM mst_karyawan ")->result(); 

        
        $valid = false;

        foreach ($data as $data) {
            $cek = $this->db1->query("SELECT * FROM mst_karyawan WHERE Cabang = '$data->Cabang' and Kode
                = '$data->Kode' and Jabatan = '$data->Jabatan' ");

            if ($cek->num_rows() > 0) {
                $query = $this->db1->query("DELETE FROM mst_karyawan where Cabang = '$data->Cabang' and Kode
                = '$data->Kode' and Jabatan = '$data->Jabatan'"); 
            }
            # code...
            $this->db1->set("Cabang", $data->Cabang);
            $this->db1->set("Kode", $data->Kode);
            $this->db1->set("Nama", $data->Nama);
            $this->db1->set("Jabatan", $data->Jabatan);
            $this->db1->set("Tipe_Salesman", $data->Tipe_Salesman);
            $this->db1->set("Rayon_Salesman", $data->Rayon_Salesman);
            $this->db1->set("Supervisor", $data->Supervisor);
            $this->db1->set("Status", $data->Status);
            $this->db1->set("Menagih", $data->Menagih);
            $this->db1->set("Mengirim", $data->Mengirim);
            $this->db1->set("Jenis", $data->Jenis);
            $this->db1->set("MCL", $data->MCL);
            $this->db1->set("Created_at", $data->Created_at);
            $this->db1->set("Created_by", $data->Created_by);
            $this->db1->set("Updated_at", $data->Updated_at);
            $this->db1->set("Updated_by", $data->Updated_by);
            $this->db1->set("posting_at", date('Y-m-d H:i:s'));
            $this->db1->set("posting_by", $this->session->userdata('username'));
            $valid = $this->db1->insert('mst_karyawan');

            $this->db->set("statusPusat", 'Y');
            $this->db->where('id', $data->id);
            $this->db->update('mst_karyawan');
        }


        return $valid;
    }
    

 }