<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prinsipal_limit extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_prinsipal_limit');
		$this->load->library('format');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
			"Cabang" => $this->session->userdata('Cabang'),
		);
		$this->user = $this->session->userdata('username');
        $this->cabang = $this->session->userdata('cabang');
	}

	public function usulanlimitbeliprinsipal()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['prinsipal']= $this->Model_main->Prinsipal();
			$this->twig->display('pembelian/buat_usulan_prinsipal_limit.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datalimitbeliprinsipal()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/data_prinsipal_limit.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getprinsipalLimit()
	{
		if ( $this->logged ) 
		{
			if ($_POST) 
			{
				$q = $this->Model_prinsipal_limit->getLimit($_POST['prinsipal'],$this->cabang);
				$q2 = $this->Model_prinsipal_limit->getLimitOutstanding($_POST['prinsipal'],$this->cabang);

				$limit = (!empty($q[0]->Limit_Beli) ? $q[0]->Limit_Beli : "0");
				$outstanding = (!empty($q2[0]->po) ? $q2[0]->po : "0");

				$output = array(
							"limit" => $limit,
							"outstanding" => $outstanding
 					);
			echo json_encode($output);
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
	}


	public function listdataPrinsipalLimitbeli()
    {  
    	$data = "";

		$bySearch = "";
		$byLimit = "";
		$draw=$_REQUEST['draw'];

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
		$length=$_REQUEST['length'];

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
		$start=$_REQUEST['start'];
			/*Keyword yang diketikan oleh user pada field pencarian*/
		$search=$_REQUEST['search']["value"];
		if($search!=""){
			$bySearch = " and (Prinsipal like '%".$search."%' )";
			}

        $list = $this->Model_prinsipal_limit->listPrinsipalLimitbeli($bySearch);
        $data = array();
        $x = $_POST['start'];
		foreach ($list as $prinsipal_limit) {
			$x++;
			$row = array();
			$row[] = $x;
			$row[] = (!empty($prinsipal_limit->Cabang) ? $prinsipal_limit->Cabang : "NULL");
			$row[] = (!empty($prinsipal_limit->Bulan) ? $prinsipal_limit->Bulan : "NULL");
 			$row[] = (!empty($prinsipal_limit->Prinsipal) ? $prinsipal_limit->Prinsipal : "NULL");
 			$row[] = "<p align='right'>".(!empty($prinsipal_limit->Limit_Beli) ? number_format($prinsipal_limit->Limit_Beli) : "0")."</p>";
 			$row[] = "<p align='right'>".(!empty($prinsipal_limit->Limit_Outstanding) ? number_format($prinsipal_limit->Limit_Outstanding) : "0")."</p>";
 			$row[] = "<p align='right'>".(!empty($prinsipal_limit->Sisa_Limit)? number_format($prinsipal_limit->Sisa_Limit) : "0")."</p>";
 			// $row[] = (!empty($prinsipal_limit->Waktu_Approv) ? $prinsipal_limit->Waktu_Approv : "NULL");
			
			//add html for action
			// $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$prinsipal_limit->id."'".')"><i class="fa fa-pencil"></i> Edit</a>
			// 	  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$prinsipal_limit->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
       
    }

    public function listCabangPrinsipalLimit()
    {   
        if ( $this->logged ) 
        {
            $data = $this->Model_prinsipal_limit->listCabang();
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

	public function listDataPrinsipalLimit()
	{	
		$data = "";

		$bySearch = "";
		$byLimit = "";
		$draw=$_REQUEST['draw'];

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
		$length=$_REQUEST['length'];

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
		// $start=$_REQUEST['start'];
			/*Keyword yang diketikan oleh user pada field pencarian*/
		$search=$_REQUEST['search']["value"];
		if($search!=""){
			$bySearch = " and (Prinsipal like '%".$search."%' )";
			}

		$list = $this->Model_prinsipal_limit->getlistdatalimit($bySearch);
        $data = array();
        $x = $_POST['start'];
		foreach ($list as $prinsipal_limit) {
			$x++;
			$row = array();
			$row[] = $x;
 			$row[] = (!empty($prinsipal_limit->Cabang) ? $prinsipal_limit->Cabang : "NULL");
 			$row[] = (!empty($prinsipal_limit->Prinsipal) ? $prinsipal_limit->Prinsipal : "NULL");
 			$row[] = (!empty($prinsipal_limit->Limit) ? $prinsipal_limit->Limit : "0");
 			$row[] = (!empty($prinsipal_limit->Limit_Outstanding) ? $prinsipal_limit->Limit_Outstanding : "0");
 			$row[] = (!empty($prinsipal_limit->Limit_Usulan)? $prinsipal_limit->Limit_Usulan : "0");
 			$row[] = (!empty($prinsipal_limit->Keterangan_Usulan) ? $prinsipal_limit->Keterangan_Usulan : "NULL");
 			$row[] = (!empty($prinsipal_limit->Waktu_Usulan) ? $prinsipal_limit->Waktu_Usulan : "NULL");
 			// $row[] = (!empty($prinsipal_limit->Waktu_Approv) ? $prinsipal_limit->Waktu_Approv : "NULL");
			if($prinsipal_limit->Approve_BM == 'Approve' or $prinsipal_limit->Approve_RBM == 'Approve' or $prinsipal_limit->Approve_NSM == 'Approve' or $prinsipal_limit->Approve_pusat == 'Approve'){
				$row[] = "-";
			}if($prinsipal_limit->Approve_BM == 'Reject' or $prinsipal_limit->Approve_RBM == 'Reject' or $prinsipal_limit->Approve_NSM == 'Reject' or $prinsipal_limit->Approve_pusat == 'Reject'){
				$row[] = "-";
			}else{
				$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$prinsipal_limit->id."'".')"><i class="fa fa-pencil"></i> Edit</a>
				  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$prinsipal_limit->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
			}
			//add html for action
			
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function addDataPrinsipalLimit()
	{
		if ( $this->logged ) 
		{	
			//$this->_validate();
			$params = (object)$this->input->post();
			$data = $this->Model_prinsipal_limit->savePrinsipalLimit($params);
			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function updateDataPrinsipalLimit()
	{
		if ( $this->logged  ) 
		{	
			//$this->_validate();
			$params = (object)$this->input->post();
			$valid = $this->Model_prinsipal_limit->updatePrinsipalLimit($params);
			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function deleteDataPrinsipalLimit($id = NULL)
	{
		$this->Model_prinsipal_limit->deletePrinsipalLimit($id);
		echo json_encode(array("status" => TRUE));
	}

	public function getDataPrinsipalLimit($id = NULL)
	{    
		if ( $this->logged ) 
		{
			$valid = $this->Model_prinsipal_limit->getdataById($id); 
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	// private function _validate()
 //    {
 //        $data = array();
 //        $data['error_string'] = array();
 //        $data['inputerror'] = array();
 //        $data['status'] = TRUE;
 
 //        if($this->input->post('Limit') == '')
 //        {
 //            $data['inputerror'][] = 'Limit';
 //            $data['error_string'][] = 'Limit is required';
 //            $data['status'] = FALSE;
 //        }

 //        if($this->input->post('Limit_outstanding') == '')
 //        {
 //            $data['inputerror'][] = 'Limit_outstanding';
 //            $data['error_string'][] = 'Limit Outstanding is required';
 //            $data['status'] = FALSE;
 //        }
 
 //        if($this->input->post('Limit_usulan') == '')
 //        {
 //           $data['inputerror'][] = 'Limit_usulan';
 //           $data['error_string'][] = 'Limit Usulan is required ';
 //           $data['status'] = FALSE;
 //        }

 //        if($this->input->post('Keterangan_usulan') == '')
 //        {
 //           $data['inputerror'][] = 'Keterangan_usulan';
 //           $data['error_string'][] = 'Keterangan Usulan is required ';
 //           $data['status'] = FALSE;
 //        }

 //        if($this->input->post('Waktu_usulan') == '')
 //        {
 //            $data['inputerror'][] = 'Waktu_usulan';
 //            $data['error_string'][] = 'Date of Waktu Usulan is required';
 //            $data['status'] = FALSE;
 //        }

 //        if($this->input->post('Waktu_approv') == '')
 //        {
 //            $data['inputerror'][] = 'Waktu_approv';
 //            $data['error_string'][] = 'Date of Waktu Approv is required';
 //            $data['status'] = FALSE;
 //        }
 
 //        if($data['status'] === FALSE)
 //        {
 //            echo json_encode($data);
 //            exit();
 //        }
 //    }
}
