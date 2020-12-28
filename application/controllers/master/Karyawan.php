<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karyawan extends CI_Controller {
	var $content;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/Model_karyawan');
        $this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
        $this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
    }

    public function Karyawan(){
    	// echo "string";
    	if ($this->logged) 
		{
			// $this->content['Cabang'] = $this->Model_karyawan->listCabang()->result();
			$Cabang = $this->session->userdata('cabang');
			$this->content['Rayon'] = $this->Model_karyawan->get_rayon($Cabang)->result();
			// $this->content['Prinsipal'] = $this->Model_karyawan->listPrinsipal()->result();
			$this->content['Cabang'] = $Cabang;
			$this->twig->display('master/Karyawan.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
    }

    public function listDataKaryawan(){
    	if ($this->logged) 
		{
			$params = $columns = $totalRecords = $data = array();
			$params = $_REQUEST;
			$query = $this->Model_karyawan->listDataKaryawan();
			$i=1;
			foreach ($query as $list) {
				$row = array();
				$row[] = $i;
				$row[] = (!empty($list->Cabang) ? $list->Cabang : "");
				$row[] = (!empty($list->Kode) ? $list->Kode : "");
				$row[] = (!empty($list->Nama) ? $list->Nama : "");
				$row[] = (!empty($list->Jabatan) ? $list->Jabatan : "");
	 			$row[] = (!empty($list->Tipe_Salesman) ? $list->Tipe_Salesman : "");
	 			$row[] = (!empty($list->Rayon_Salesman) ? $list->Rayon_Salesman : "");
	 			$row[] = (!empty($list->Supervisor) ? $list->Supervisor : "");
	 			$row[] = (!empty($list->Status) ? $list->Status : "");
	 			$row[] = (!empty($list->Menagih) ? $list->Menagih : "");
	 			$row[] = (!empty($list->Mengirim) ? $list->Mengirim : "");
	 			$row[] = (!empty($list->Jenis) ? $list->Jenis : "");
	 			$row[] = (!empty($list->Jabatan) ? $list->Jabatan : "");
	 			$row[] = (!empty($list->MCL) ? $list->MCL : "");
	 			$row[] = (!empty($list->email) ? $list->email : "");
	 			$edit = $delete = '';

	 			// if ($list->bulan == date('m') && $list->tahun == date('Y')) {
	 				$edit ='<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$list->id."'".')"><i class="fa fa-pencil"></i> Edit</a>';
	 			// }

	 			// if ($list->bulan == date('m') && $list->tahun == date('Y')) {
	 				$delete ='<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$list->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
	 			// }

	 			if ($list->statusPusat == 'N') {
	 				$statusPusat = '<a class="btn btn-sm btn-default" href="javascript:void(0)" title="Upload ke Pusat" onclick="upload('."'".$list->id."'".')"><i class="fa fa-"></i> Proses</a>';
	 			}else{
	 				$statusPusat = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Upload ke Pusat" onclick="upload('."'".$list->id."'".')"><i class="fa fa-check"></i> Proses</a>';
	 			}

	 			$row[] = $edit;
	 			$row[] = $statusPusat;
			
				$data[] = $row;

				$i++;
			}

			$output = array(
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/");
		}
    }

    function listDataKaryawan_count() { 

       $result = $this->Model_karyawan->listDataKaryawan_count()->row();
       echo json_encode($result);
   }

    function listDataKodeSalesman() { 
    	$cabang = $this->uri->segment(2);
       $result = $this->Model_karyawan->get_KodeSalesman($cabang)->result();
       echo json_encode($result);
   }

   public function saveDataKaryawan(){
		if ( $this->logged) 
		{	
			$params = $this->input->post();	
			$valid = $this->Model_karyawan->saveDataKaryawan($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function deleteDataKaryawan($id = NULL){
		if ($this->logged) 
		{
			$this->Model_karyawan->deleteKaryawan($id);
			echo json_encode(array("status" => TRUE));
		}
		else
		{
			redirect("auth/");
		}
	}

	public function updateDataKaryawan(){
		if ( $this->logged) 
		{	
			$params = $this->input->post();	
			$valid = $this->Model_karyawan->updateKaryawan($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function getDataKaryawan($id = NULL)
	{    
		if ( $this->logged) 
		{
			$data = $this->Model_karyawan->getDataKaryawan($id);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	function uploadKaryawan($id = NULL){
		$valid = $this->Model_karyawan->uploadKaryawan($id);
		echo json_encode(array("status" => TRUE));
	}

	function uploadKaryawan_all() { 

       $result = $this->Model_karyawan->uploadKaryawan_all();
       echo json_encode(array("status" => TRUE));

   }
   


}