<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Salesman extends CI_Controller {

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
		$this->load->model('pembelian/Model_order');
		$this->load->model('pembelian/Model_orderSalesman');
		$this->load->model('pembelian/Model_cetakTransaksi');
		$this->load->model('pembelian/Model_kiriman');
		$this->load->library('format');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->user = $this->session->userdata('username');
		$this->cabang = $this->session->userdata('cabang');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"y" => date("y"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function buatordersalesman()
	{	
		if ($this->logged) 
		{
			/*if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='KSA' || $this->userGroup=='Salesman')	
			{	*/			
				$kodeLamaCabang = $this->Model_order->kodeLamaCabang();
				$kodeDokumen = $this->Model_order->kodeDokumen('SO');
				$counter = $this->Model_order->counter('SO');
				if ($counter) 
				{
					$no = $counter->Counter + 1;
		        }
		        else
		        	$no = 1000001;

		        $noOrder = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
		        $this->content['noOrder'] = $noOrder;
		        $this->content['no_Order'] = $this->Model_orderSalesman->NoOrder();
		        $this->content['caraBayar'] = $this->Model_order->caraBayar();
				$this->twig->display('pembelian/buatorder_salesman.html', $this->content);
				//log_message('error',print_r($this->userGroup,true));
			/*}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}	*/		
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getOrderSalesman()
	{
		if ($this->logged) 
		{	
			$no = $_POST['no'];
			$data = $this->Model_orderSalesman->getOrderSalesman($no);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function saveDataOrder_salesman()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_orderSalesman->saveDataSalesOrder_salesman($params);
			$No="";
			// if ($valid['do']) {
			// 	$NoDO = $this->Model_order->saveDataDeliveryOrder($valid['no']);
			// 	$result = $this->Model_kiriman->prosesDataDO($NoDO);
			// 	$No = $NoDO;// $print = $this->Model_cetakTransaksi->printDataDO($NoDO);
			// }
			// $result = $this->Model_order->prosesDataSO($valid['no']);
			
			$output = array(
		          "status" => $valid
		       );
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function datasoSalesman()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datasoSalesman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataSO_Salesman()
	{	
		if ($this->logged) 
		{
			$params = $columns = $totalRecords = $data = array();
			// $params = $_REQUEST;
			$query = $this->Model_orderSalesman->listDataSO_Salesman()->result();

			$i=1;
			foreach ($query as $list) {
				$row = array();
				$NoOrder = "'$list->NoOrder'";
				$row[] = $i;

				$row[] = '<button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Proses" onclick="reject('.$NoOrder.')"><i class="fa fa-trash"></i>Reject</button>';
				$row[] = (!empty($list->Cabang) ? $list->Cabang : "");
				$row[] = (!empty($list->KodeSalesman) ? $list->KodeSalesman : "");
				$row[] = (!empty($list->NamaSalesman) ? $list->NamaSalesman : "");
				$row[] = (!empty($list->NamaPelanggan) ? $list->NamaPelanggan : "");
	 			$row[] = (!empty($list->KodePelanggan) ? $list->KodePelanggan : "");
	 			$row[] = (!empty($list->NoOrder) ? $list->NoOrder : "");
	 			$row[] = (!empty($list->TglOrder) ? $list->TglOrder : "");
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Proses" onclick="detail('.$NoOrder.')"><i class="fa fa-trash"></i>Detail</button>';
			
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

	public function listDataDetail_Salesman()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_orderSalesman->listDataDetail_Salesman($no)->result();;
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	function strpos_arr($haystack, $needle) {
	    if(!is_array($needle)) $needle = array($needle);
	    foreach($needle as $what) {
	        if(($pos = strpos($haystack, $what))!==false) return true; 
	    }
	    return false;
	}

	public function rejectData_Salesman()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$valid = $this->Model_orderSalesman->rejectDataSO_Salesman($no);
			// $valid = $this->Model_orderSalesman->prosesDataSO_Salesman($no);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

}