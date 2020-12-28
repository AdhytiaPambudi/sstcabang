<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proses extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_proses');
		$this->load->model('pembelian/Model_order');
		$this->load->model('pembelian/Model_kiriman');
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

	public function dataDOPending()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datadopending.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataDOPending()
	{
		$draw=$_REQUEST['draw'];
		$length=$_REQUEST['length'];
		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		$query = $this->Model_proses->listDataDOPending($search, $length, $start);

		$total=$query->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		

		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}

		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$statusPusat = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$row->NoSO."'".', '."'".$row->NoDO."'".')"><i class="fa fa-check"></i> Proses</button>';
				$detail = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';

				$output['data'][]=array($nomor_urut,$statusPusat,$detail,$row->NoSO,$row->NoDO,$row->TglSO,$row->Pelanggan,$row->Salesman,$row->Rayon,$row->Status,$row->Acu,$row->CaraBayar,$row->ValueCashDiskon,$row->TOP,$row->TglJtoOrder,$row->Gross,$row->Potongan,$row->Value,$row->Ppn,$row->Total);
				$nomor_urut++;
			}
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","","","","","","","","","");
		}
		echo json_encode($output);
	}

	public function prosesDataDOPending($status = NULL)
	{          
        if ($this->logged) 
		{	
			$valid = true;
			$NoSO = $_POST['NoSO'];
			$NoDO = $_POST['NoDO'];
			$cek = $this->db->query("select * from trs_delivery_order_sales where NoSO = '".$NoSO."' and NoDO = '".$NoDO."'")->num_rows();

			if ($cek == 0) {
				$NoDO = $this->Model_order->saveDataDeliveryOrder($NoSO, $NoDO);
				$valid = $this->Model_kiriman->prosesDataDO($NoDO);
			}

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}