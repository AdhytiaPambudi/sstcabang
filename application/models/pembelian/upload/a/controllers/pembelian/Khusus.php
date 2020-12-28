<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Khusus extends CI_Controller {

	var $content;
	var $API ="";
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
		$this->load->model('pembelian/Model_orderKhusus');
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

		$this->load->library('curl');
		$this->API="http://localhost/sstcabang/index.php";
	}

	function testKhusus(){
		$ch 	= curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL 			=> "http://192.168.1.22/bidanapi/apipusat/grobak?method=getsalestotal",
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_ENCODING 		=> "",
				CURLOPT_MAXREDIRS 		=> 10,
				CURLOPT_TIMEOUT 		=> 30,
				CURLOPT_CUSTOMREQUEST 	=> "GET",
				// CURLOPT_POSTFIELDS 		=> "method=getsalestotal",
				CURLOPT_HTTPHEADER 		=> array(
											"cache-control: no-cache",
											"content-type: application/x-www-form-urlencoded",
											"Accept: application/json"
				)
			));


			$result = curl_exec($ch);
			curl_close($ch);
			return $result;

		// printf("<pre>%s</pre>",print_r(json_decode($result),TRUE));
	}

	function tampilbidan(){
		$data = json_decode($this->testbidan(),TRUE);

		 //print_r($data['output']);

		$myData = $data['output'];

		foreach ($data['output'] as $r) {
			echo $r['id']."<br>";
		}

	}

	public function buatorderKhusus()
	{	
		if ($this->logged) 
		{		
				$kodeLamaCabang = $this->Model_orderKhusus->kodeLamaCabang();
				$kodeDokumen = $this->Model_orderKhusus->kodeDokumen('SO');
				$counter = $this->Model_orderKhusus->counter('SO');

				if ($counter) 
				{
					$no = $counter->Counter + 1;
		        }
		        else
		        {
		        	$no = 1000001;
		        }
		        
		        $data_api = json_decode($this->Model_orderKhusus->NoOrder(),TRUE);

				$query = $data_api['output'];

		        $noOrder = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
		        $this->content['noOrder'] = $noOrder;
		        $this->content['no_Order'] = $query;
		        $this->content['caraBayar'] = $this->Model_orderKhusus->caraBayar();
				$this->twig->display('pembelian/buatorder_Khusus.html', $this->content);
				
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getOrderKhusus()
	{
		if ($this->logged) 
		{	
			$no = $_POST['no'];
			$KodePelanggan = $_POST['KodePelanggan'];
			$data = json_decode($this->Model_orderKhusus->getOrderKhusus($no),TRUE);
			$free_panel = $this->Model_orderKhusus->get_free_panel($KodePelanggan)->row();
			$prins1 = $free_panel->Prins_Onf;
			$Cab_onf = $free_panel->Cab_onf;


			$data = $data['output'];
			
			echo json_encode( array('data' => $data,"prins1" => $prins1,"Cab_onf" => $Cab_onf ));
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function cek_getOrderKhusus()
	{
		if ($this->logged) 
		{	
			$no = $_GET['no'];
			
			$params = "method=getsalesdetail&noorder=".$no."&cabang=Bekasi";

			$ch     = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL             => "http://api.sst.biz.id/bidanapi/apipusat/grobak?".$params,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 30,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                                            "cache-control: no-cache",
                                            "content-type: application/x-www-form-urlencoded",
                                            "Accept: application/json"
                )
            ));


            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data,TRUE);
			$data = $data['output'];

			print_r($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function saveDataOrder_Khusus()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_orderKhusus->saveDataSalesOrder_Khusus($params);
			$No="";
			
			
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

    public function datasoKhusus()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datasoKhusus.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataSO_Khusus()
	{	
		if ($this->logged) 
		{
			$params = $columns = $totalRecords = $data = array();

			$data_api = json_decode($this->Model_orderKhusus->listDataSO_Khusus(),TRUE);

			$query = $data_api['output'];
			// print_r($query);

			$i=1;
			// echo count($query);
			if (count($query)>1) {
				foreach ($query as $list) {
					$row = array();
					$NoOrder = "'$list[NoOrder]'";
					$row[] = $i;

					$row[] = '<button type="button" disabled="true" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="reject('.$NoOrder.')"><i class="fa fa-trash"></i>Reject</button>';
					$row[] = (!empty($list['Cabang']) ? $list['Cabang'] : "");
					$row[] = (!empty($list['NamaPelanggan']) ? $list['NamaPelanggan'] : "");
		 			$row[] = (!empty($list['KodePelanggan']) ? $list['KodePelanggan'] : "");
		 			$row[] = (!empty($list['NoOrder']) ? $list['NoOrder'] : "");
		 			$row[] = (!empty($list['OrderDate']) ? $list['OrderDate'] : "");
		 			$row[] = (!empty($list['Status']) ? $list['Status'] : "");
		 			$row[] = (!empty($list['NoKiriman']) ? $list['NoKiriman'] : "");
		 			$row[] = (!empty($list['ResiKiriman']) ? $list['ResiKiriman'] : "");
		 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Detail" onclick="detail('.$NoOrder.')"><i class="fa fa-trash"></i>Detail</button>';
				
					$data[] = $row;

					$i++;
				}
			}else{
			}
				$data[] = array("","","","","","","","","","","");
			

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

	

	public function listDataDetail_Khusus()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];

			$data_api = json_decode($this->Model_orderKhusus->listDataDetail_Khusus($no),TRUE);

			$data = $data_api['output'];
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

	public function rejectData_Mitra()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$valid = $this->Model_orderKhusus->rejectDataSO_Mitra($no);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	
}