<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Relokasi extends CI_Controller {

	var $content;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_relokasi');
		$this->load->library('format');
		$this->load->library('upload_file');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

// START USULAN RELOKASI
	public function buatrelokasi()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Relokasi Cabang');
	        $this->content['no'] = $no;
			$this->content['cabang']= $this->Model_main->Cabang();
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			if($cektglstok == 1){
				$this->twig->display('pembelian/buatrelokasi.html', $this->content);
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function dataterimarelokasi()
	{	
		if ( $this->logged) 
		{
			// $no = $this->Model_main->noDokumen('Relokasi Cabang');
	  //       $this->content['no'] = $no;
	  		$cektglstok = $this->Model_main->cek_tglstoktrans();
	  		if($cektglstok == 1){
				$this->content['cabang']= $this->Model_main->Cabang();
				$this->twig->display('pembelian/dataterimarelokasi.html', $this->content);
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getDetailUsulanRelokasi()
	{
		if ($this->logged) 
		{
			$kode = $_POST['kode'];
			$data = $this->Model_relokasi->getDetailUsulanRelokasi($kode);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function saveUsulanRelokasi()
	{
		if ( $this->logged ) 
		{	
			$valid = false;
			$params = (object)$this->input->post();

			$dir = 'Relokasi';
			if (!file_exists(getcwd().'/assets/dokumen/'.$dir)) {
			    mkdir(getcwd().'/assets/dokumen/'.$dir, 0777, true);
			}
			$path = getcwd().'/assets/dokumen/'.$dir.'/';
			$remoteFile='/var/www/html/sstcabang/assets/dokumen/'.$dir.'/';
			$name1 = "";
			$name2 = "";

			if(!empty($_FILES['Dokumen1']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen1']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen1']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }

			if(!empty($_FILES['Dokumen2']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen2']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen2']['name']);
                $name2 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name2;
                move_uploaded_file($temp1, $target1);
            }
            $cektglstok = $this->Model_main->cek_tglstoktrans();
			if($cektglstok == 1){
	            $no = $this->Model_relokasi->saveData($params, $name1,$name2);
				if($no){
					echo json_encode(array("status" => TRUE));
				}else{
					echo json_encode(array("status" => FALSE));
				}
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode($respon);
			}
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function relokasiusulan()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/datarelokasiusulan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	
// FINISH USULAN RELOKASI

// START KIRIMAN RELOKASI
	public function buatkirimanrelokasi()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Relokasi Cabang');
	        $this->content['no'] = $no;
			$this->twig->display('pembelian/buatkirimanrelokasi.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function cabangRelokasi()
	{
		if ($this->logged) 
		{
			$data = $this->Model_relokasi->cabangRelokasi();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function produkRelokasi()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$produk = $this->Model_relokasi->produkRelokasi($no);
			echo json_encode($produk);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getDetailProdukRelokasi()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$kode = $_POST['kode'];
			$produk = $this->Model_relokasi->getDetailProdukRelokasi($no, $kode);
			echo json_encode($produk);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataTerimaRelokasi($cek = null)
	{	
		$list = $this->Model_relokasi->listDataTerimaRelokasi();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->Cabang_Pengirim) ? $key->Cabang_Pengirim : "");
 			$row[] = (!empty($key->Cabang_Penerima) ? $key->Cabang_Penerima : "");
 			$row[] = (!empty($key->No_Terima) ? $key->No_Terima : "");
 			$row[] = (!empty($key->No_Relokasi) ? $key->No_Relokasi : "");
 			$row[] = (!empty($key->No_DO) ? $key->No_DO : "");
 			$row[] = (!empty($key->Tgl_kirim) ? $key->Tgl_kirim : "");
 			$row[] = (!empty($key->Status_kiriman) ? $key->Status_kiriman : "");
 			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="View" onclick="view('."'".$key->No_Terima."'".')" id="View"><i class="fa fa-eye"></i>&nbsp;&nbsp;View Detail&nbsp;&nbsp;</a>';
 			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Cetak" onclick="cetak('."'".$key->No_Terima."'".')" id="View"><i class="fa fa-eye"></i>&nbsp;&nbsp;Print&nbsp;&nbsp;</a>'; 			
			if ($key->Status_kiriman == 'Pending') {
				//add html for action	
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->No_Terima."'".','."'Approve'".')"><i class="fa fa-check"></i> Approve</a>';
				// $row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="approveData('."'".$key->No_Terima."'".','."'Reject'".')"><i class="fa fa-times"></i> Reject</a>'; 				
				
			}
			else
			{
				$row[] = '-';
				$row[] = '-';
				$row[] = '-';
			}

		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function updateDataRelokasiDOPusat()
	{
		if ($this->logged) 
		{
			log_message("error","mestinya kesini");
			$status = $this->Model_relokasi->updateDataRelokasiDOPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataDetailRelokasiKirim()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_relokasi->dataDetailRelokasiKirim($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataDetailRelokasiTerima()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_relokasi->dataDetailRelokasiTerima($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}



	public function EditDataRelokasiterima()
	{
		if ( $this->logged ) 
		{	
			$valid = false;
			$params = (object)$this->input->post();
			$no_terima = $params->no_terima;
			$db = $this->db->query("select * from trs_relokasi_terima_header where no_terima = '".$no_terima."' and Status_kiriman = 'Terima'");
			if($db->num_rows() > 0){
				$valid = false;
			}else{
				$data_detail = $params->data_detail;
            	$valid = $this->Model_relokasi->EditDataRelokasiterima($params,$data_detail);
			}
			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function datarelokasikiriman()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/datarelokasikiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function load_datarelokasikiriman()
	{	
		$data = array();
        $bySearch = "";
        $byLimit = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_relokasi->load_datarelokasikiriman()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (No_Relokasi like '%".$search."%' or Tgl_kirim like '%".$search."%' or Cabang_Pengirim like '%".$search."%' or Cabang_Penerima like '%".$search."%' or Prinsipal like '%".$search."%' or Status_kiriman like '%".$search."%')";
        }
        if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

		$datas = $this->Model_relokasi->load_datarelokasikiriman($bySearch, $byLimit)->result();
		$data = array();
		$no = $_POST['start'];
		foreach ($datas as $relokasi) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = (!empty($relokasi->No_Relokasi) ? $relokasi->No_Relokasi : "");
			$row[] = (!empty($relokasi->Tgl_kirim) ? $relokasi->Tgl_kirim : "");
			$row[] = (!empty($relokasi->Cabang_Pengirim) ? $relokasi->Cabang_Pengirim : "");
			$row[] = (!empty($relokasi->Cabang_Penerima) ? $relokasi->Cabang_Penerima : "");
			$row[] = (!empty($relokasi->Prinsipal) ? $relokasi->Prinsipal : "");
			$row[] = (!empty($relokasi->Gross) ? $relokasi->Gross : "");
			$row[] = (!empty($relokasi->Status_kiriman) ? $relokasi->Status_kiriman : "");
			$row[] = '<span class="btn btn-primary" onclick="view_detail('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Detail &nbsp;&nbsp;</span>';
			$row[] = '<span class="btn btn-primary" onclick="cetak('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Print &nbsp;&nbsp;</span>';
			
			$row[] = "a";

			$data[] = $row;
		}	
		$output = array(
					"draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "recordsFiltered" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "data" => $data
                );

		// log_message('error',print_r($output,true));
        echo json_encode($output);
	}

	public function approvalData()
	{          
        if ($this->logged) 
		{	
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			if($cektglstok == 1){
				$No = $_POST['No'];
				$tipe = $_POST['tipe'];
				$valid = $this->Model_relokasi->act($tipe, $No);

				echo json_encode(array("status" => $valid));
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode($respon);
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }
    public function updatedataRelokasipusat(){
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->updatedataRelokasipusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
}