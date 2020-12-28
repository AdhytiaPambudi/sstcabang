<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends CI_Controller {

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
		$this->load->model('pembelian/Model_retur');
		$this->load->library('format');
		$this->load->library('excel');
		$this->load->library('owner');
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

	public function approvalReturPelanggan()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/approvalReturPelanggan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataReturPelanggan()
	{	
		$list = $this->Model_retur->listData();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
 			$row[] = (!empty($key->Kode) ? $key->Kode : "");
 			$row[] = (!empty($key->Pelanggan) ? $key->Pelanggan : "");
 			$row[] = (!empty($key->Alamat ) ? $key->Alamat  : "");	
				//add html for action	
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->Kode."'".')"><i class="fa fa-check"></i> Approve</a>';
				$row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="reject('."'".$key->Kode."'".')"><i class="fa fa-times"></i> Reject</a>'; 
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function approveDataReturPelanggan()
	{          
        if ($this->logged) 
		{	
			$Kode = $_POST['Kode'];
			$valid = $this->Model_retur->prosesData($Kode, 'Approve');

			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

	public function rejectDataReturPelanggan()
	{          
        if ($this->logged) 
		{	
			$Kode = $_POST['Kode'];
			$valid = $this->Model_retur->prosesData($Kode, 'Reject');

			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}