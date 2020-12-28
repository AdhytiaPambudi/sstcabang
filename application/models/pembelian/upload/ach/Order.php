<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller {

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

	public function buatorder()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='KSA' || $this->userGroup=='Salesman')	
			{				
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
		        $this->content['caraBayar'] = $this->Model_order->caraBayar();
		        $this->content['flag_harga'] = $this->Model_main->cek_flag();
				$this->twig->display('pembelian/buatorder.html', $this->content);
				//log_message('error',print_r($this->userGroup,true));
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listPelanggan()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Pelanggan();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listPelanggan_all()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Pelanggan_all();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function dataPelanggan($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->dataPelanggan($kode);
			$detail = $this->Model_order->datapiutangPelanggan($kode);
			$output = array(
		          "data" => $data,
		          "detail" => $detail
		       );
			echo json_encode($output);
			die();
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function dataPelanggan_detail($kode = NULL)
	{
		if ($this->logged) 
		{
			$detail = $this->Model_order->datapiutangPelanggan_detail($kode);
			$output = array(
		          "detail" => $detail
		       );
			echo json_encode($output);
			die();
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function listSales()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Salesman();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listProduk()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Produk();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listProdukorder()
	{
		if ($this->logged) 
		{
			$tipe = $_POST['tipe'];
			$data = $this->Model_order->listProdukorder($tipe);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getBatch($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Batch($kode);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getProdukBuatOrder()
	{
		if ( $this->logged ) 
		{ 
		  $kode = $_POST['Kode'];
		  $Kodepel = $_POST['Kodepel'];
		  $data = $this->Model_order->getProdukBuatOrder($kode);
		  $satuan = $this->Model_order->getSatuanBuatOrder($kode);
		  $discprinsNew = $this->Model_order->getProdukDiscPrins($kode,$Kodepel);

		  $get_onf = $this->Model_order->get_onf($Kodepel);

		  
		  
		  // log_message("error",print_r($kode,true));
		  // log_message("error",print_r($data,true));
		  if($data != ""){

		  	if ($get_onf->num_rows() > 0) {
			  	$data->Cab_onf = $get_onf->row("Cab_Onf");
			  	$data->Prins_onf = $get_onf->row("Prins_Onf");
			  	$data->Total_onf = $get_onf->row("Prins_Onf") + $get_onf->row("Cab_Onf");
			}else{
			  	$data->Cab_onf = 0;
			  	$data->Prins_onf = 0;
			  	$data->Total_onf = 0;
			}

		  	if($satuan != ""){
			  	$data->Satuan = (!empty($satuan->Satuan) ? $satuan->Satuan : "");
			  	$data->UnitStok = (!empty($satuan->UnitStok) ? $satuan->UnitStok : 0);
			  	$data->uCogs = (!empty($satuan->UnitCOGS) ? $satuan->UnitCOGS : 0);
			  }else{
			  	$data->Satuan = "";
			  	$data->UnitStok = 0;
			  	$data->uCogs = 0;
			  }
			  if(empty($discprinsNew->No_Usulan)){
				  $data->NoUsulanDisc = "";
				  $data->Tipe_trans = "";
				  $data->qty1 = 0;
				  $data->qty2 = 0;
				  $data->disc_prins1 = 0;
				  $data->disc_prins2 = 0;
				  $data->status_pelanggan = "";
				  $data->KodePelanggan = "";
			  }else{
			  	  $data->NoUsulanDisc = $discprinsNew->No_Usulan;
				  $data->Tipe_trans = $discprinsNew->Tipe_trans;
				  $data->qty1 = $discprinsNew->qty1;
				  $data->qty2 = $discprinsNew->qty2;
				  $data->disc_prins1 = $discprinsNew->disc_prins1;
				  $data->disc_prins2 = $discprinsNew->disc_prins2;
				  $data->status_pelanggan = $discprinsNew->status_pelanggan;
				  $data->KodePelanggan = $discprinsNew->KodePelanggan;
			  }
			  
			  //diskon cabang khusus >> UJ 20190716
			  $disccabsNew = $this->Model_order->getProdukDiscCabs($kode,$Kodepel);
			  if(!empty($disccabsNew->NamaDiskon)){
			  	  $data->NoUsulanDiscCab = $disccabsNew->NamaDiskon;
				  $data->Dsc_Cab = $disccabsNew->DiscCab;
				  $data->HNA = $disccabsNew->HNA;
				  $data->Tipe_trans = "DCK";
				  $data->KodePelanggandc = $disccabsNew->KodePelanggan;
			  }
			}else{
				$data="";
			}
		  echo json_encode($data);
		}
		else 
		{  
		  redirect("auth/logout");
		}
	}

	public function getProdukNambahOrder()
	{
		if ( $this->logged ) 
		{  
		  $NODO = $_POST['NoDO'];
		  $kode = $_POST['kode'];
		  $data = $this->Model_order->getprodukorder($kode,$NODO);
		  echo json_encode($data);
		}
		else 
		{  
		  redirect("auth/logout");
		}
	}

	public function saveDataOrder()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_order->saveDataSalesOrder($params);
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

    public function dataso()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/dataso.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataSO($cek = null)
	{	
		if ($this->logged) 
		{
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
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


			/*Menghitung total data didalam database*/
			if($cek=='all')
				$total = $this->Model_order->listDataSO('','','')->num_rows();
			else
				$total = $this->Model_order->listDataSO('','','Usulan')->num_rows();

			/*Mempersiapkan array tempat kita akan menampung semua data
			yang nantinya akan server kirimkan ke client*/
			$output=array();

			/*Token yang dikrimkan client, akan dikirim balik ke client*/
			$output['draw']=$draw;

			/*
			$output['recordsTotal'] adalah total data sebelum difilter
			$output['recordsFiltered'] adalah total data ketika difilter
			Biasanya kedua duanya bernilai sama, maka kita assignment 
			keduaduanya dengan nilai dari $total
			*/
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " and (trs_sales_order.NoSO like '%".$search."%' or TglSO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Salesman like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_order->listDataSO($bySearch, $byLimit , '')->result();
			else
				$query=$this->Model_order->listDataSO($bySearch, $byLimit, 'Usulan')->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_order->listDataSO($bySearch,'','')->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_order->listDataSO($bySearch, '', 'Usulan')->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				
				if (($baris->alasan_status == 'DO Batal By System' or empty($baris->NoDO)) and $baris->Status != "Reject") {
					$row[] = '<button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Proses" onclick="rejectData('."'".$baris->NoSO."'".')"><i class="fa fa-trash"></i>Reject</button>';
				}
				else
				{
					if ($baris->Status == "Reject") {
						$row[] = "<p style='color:red'>REJECTED</p>";	
					}
					else{
						$row[] = "-";
					}
				}
				$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="Proses" onclick="closeData('."'".$baris->NoSO."'".')"><i class="fa fa-trash"></i>Close</button>';
				if ($baris->Status == 'Closed' and empty($baris->NoDO)) {
					$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="Proses" onclick="prosesDataDO('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i>Proses</button>';
				}
				else
				{
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "-");
				}
				$row[] = '<a style="cursor:pointer" title="Update Approval" onclick="updateapproval('."'".$baris->NoSO."'".')" id="cekpusat">'.$baris->NoSO.'</a>';
				// $row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->TimeSO) ? $baris->TimeSO : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			// $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = '<a style="cursor:pointer" title="Update Status Piutang" onclick="prosesulangtop('."'".$baris->NoSO."'".','."'Limit'".')" id="cekpusat">'.(!empty(number_format($baris->limit_piutang_bm)) ? number_format($baris->limit_piutang_bm) : "").'</a>';
	 			$row[] = '<a style="cursor:pointer" title="Update Status TOP" onclick="prosesulangtop('."'".$baris->NoSO."'".','."'TOP'".')" id="cekpusat">'.$baris->umur_piutang_bm.'</a>';
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cek('."'".$baris->NoSO."'".')"><i class="fa fa-eye"></i> Cek</button>';
	 			$row[] = (!empty($baris->MonitoringApproval) ? $baris->MonitoringApproval : "");
	 			$row[] = (!empty($baris->MonitoringStatus) ? $baris->MonitoringStatus : "");
	 			if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				if($this->userGroup == 'Cabang' or $this->userGroup == 'KSA' or $this->userGroup == 'BM'){
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesUlangDataSO('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i> Proses</button>';
				}else{
					$row[] = "-";
				}
					
					if($this->strpos_arr($baris->MonitoringApproval, array('TOP', 'Limit'))){

						$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="proseslt('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i> Proses</button>';
					}else{
						$row[] = '';
					}

				$data[] = $row;
				$i++;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);
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

	public function prosesDataSO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_order->prosesDataSO($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

	public function prosesDataLT()
	{          
               
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_order->prosesDataLT($No);
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

    public function listDataDetailSO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_order->dataSO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function updateDataSOPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_order->updateDataSOPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function rejectDataSO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$status = $_POST['status'];
			$data = $this->Model_order->rejectDataSO($no,$status);
			$valid = $this->Model_order->prosesDataSO($no);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function prosesDO($status = NULL)
	{          
        if ($this->logged) 
		{	
			$NoSO = $_POST['No'];
			$NoDO = $this->Model_order->saveDataDeliveryOrder($NoSO);
			$result = $this->Model_kiriman->prosesDataDO($NoDO);

			echo json_encode(array("status" => $result));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function buatorderulang()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP')	
			{				
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
		        $this->content['caraBayar'] = $this->Model_order->caraBayar();
				$this->twig->display('pembelian/buatorder_ulang.html', $this->content);
				//log_message('error',print_r($this->userGroup,true));
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listsoulang()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->listsoulang();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getdataorder()
	{
		if ($this->logged) 
		{
			$noso = $_POST['noso'];
			$data = $this->Model_order->getdataorder($noso);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	 public function saveUlangDataOrder()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_order->saveUlangDataSalesOrder($params);
			$No="";
			// if ($valid['do']) {
			// 	$NoDO = $this->Model_order->saveDataDeliveryOrder($valid['no']);
			// 	$result = $this->Model_kiriman->prosesDataDO($NoDO);
			// 	$No = $NoDO;// $print = $this->Model_cetakTransaksi->printDataDO($NoDO);
			// }
			$result = $this->Model_order->prosesDataSO($valid['no']);
			
			$output = array(
		          "status" => $result
		       );
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function buatusulandiskonprinsipal()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP')	
			{				
		        $this->content['cabang']= $this->Model_main->Cabang();
				$this->content['prinsipal']= $this->Model_main->Prinsipal();
				$this->content['supplier']= $this->Model_main->Supplier();
				$this->content['produk']= $this->Model_main->Produk();
				$this->content['pelanggan']= $this->Model_main->Pelanggan();
				$this->twig->display('pembelian/buatusulandiskonprinsipal.html', $this->content);
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function saveDataUsulanDiskonPrins()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_order->saveDataUsulanDiskonPrins($params);
			$No="";
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
    public function dataapprovaldiscprins()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='BM')	
			{				
				$this->twig->display('pembelian/dataapprovaldiscprins.html', $this->content);
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}
    public function listDataApprovalDP()
	{
		$params = (object)$this->input->post();
		$keyword = $params->keyword;
		$data = "";
		$bySearch = "";
		$bySearch1 = "";
		$byLimit = "";
		$draw=$_REQUEST['draw'];
		$length=$_REQUEST['length'];
		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		$output=array();
		$output['draw']=$draw;
		// log_message('error',print_r($keyword,true));
		$total = $this->Model_order->listDataApprovalDP($bySearch, "")->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		$output['data']=array();
		if($keyword['s_cabang'] != "" or $keyword['s_usulan'] != "" or $keyword['s_tanggal'] != "" or $keyword['s_prinsipal'] != "" or $keyword['s_tipe'] != "" or $keyword['s_mulai'] != "" or $keyword['s_akhir'] != "" or $keyword['s_kode'] != "" or $keyword['s_nama'] != "" or $keyword['s_status'] != "" or $keyword['s_qty1'] != "" or $keyword['s_qty2'] != "" or $keyword['s_discprins'] != "" or $keyword['s_kodepel'] != "" or $keyword['s_namapel'] != ""){
				// log_message('error','xxxx');
				$bySearch = " and (Cabang like '%".$keyword['s_cabang']."%' and No_Usulan like '%".$keyword['s_usulan']."%' and tanggal like '%".$keyword['s_tanggal']."%' and Prinsipal like '%".$keyword['s_prinsipal']."%' and Tipe_trans like '%".$keyword['s_tipe']."%' and tglmulai like '%".$keyword['s_mulai']."%' and tglakhir like '%".$keyword['s_akhir']."%' and KodeProduk like '%".$keyword['s_kode']."%' and NamaProduk like '%".$keyword['s_nama']."%' and Status like '%".$keyword['s_status']."%' and qty1 like '%".$keyword['s_qty1']."%' and qty2 like '%".$keyword['s_qty2']."%' and disc_prins2 like '%".$keyword['s_discprins']."%' and KodePelanggan like '%".$keyword['s_kodepel']."%' and NamaPelanggan like '%".$keyword['s_namapel']."%')";

				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_order->listDataApprovalDP($bySearch)->num_rows();
		}

		if($_REQUEST['length'] != -1){
			$length=$_REQUEST['length'];
			$byLimit = " LIMIT ".$start.", ".$length;
		}
		$query = $this->Model_order->listDataApprovalDP($bySearch,$byLimit)->result();
		$nomor_urut=$start+1;
		// if ($total  > 0) {
			foreach ($query as $row) {
				if ($this->userGroup == 'BM') {
					// if($cek != 'all'){
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->No_Usulan."'".', '."'".$row->KodeProduk."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->No_Usulan."'".', '."'".$row->KodeProduk."'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
					// }else{
					// 	$statusPusat = "-";
					// }
					
				}else{
					$statusPusat = "-";
				}
				$prosespusat = '<a class="btn btn-sm btn-info" title="Proses Pusat" onclick="prosespusat('."'".$row->No_Usulan."'".', '."'".$row->KodeProduk."'".')" id="prosespusat"><i class="fa fa-eye"></i> Proses Pusat</a>';
				$discprins = $row->disc_prins1 + $row->disc_prins2;
				$output['data'][]=array($nomor_urut,$statusPusat,$row->Cabang,$row->No_Usulan,$row->tanggal,$row->Prinsipal,$row->Tipe_trans,$row->tglmulai,$row->tglakhir,$row->KodeProduk,$row->NamaProduk,$row->status,$row->qty1,$row->qty2,$discprins,$row->KodePelanggan,$row->NamaPelanggan,$row->Approved_BM,$row->Approved_prins,$prosespusat);
				$nomor_urut++;
			}
		// }
		// else{
		// 	$output['data'][] = array("","","","","","","","","","","","","","","","","","","","");
		// }
		echo json_encode($output);
	}
	public function prosesDataApprovalDP()
	{          
        if ($this->logged) 
		{	
			$NoUsulan = $_POST['No'];
			$KodeProduk = $_POST['KodeProduk'];
			$status = $_POST['status'];
			$valid = $this->Model_order->prosesDataApprovalDP($NoUsulan,$KodeProduk,$status);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function updateDataDiscPrinsPusat()
	{
		if ($this->logged) 
		{
			// $status = $_POST['status'];
			$data = $this->Model_order->updateDataDiscPrinsPusat();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function datausulandiskonprinsipal()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='BM')	
			{				
				$this->twig->display('pembelian/datadiscprins.html', $this->content);
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function prosesDataUsulanDiscPrins()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$Produk = $_POST['produk'];
			$valid = $this->Model_order->prosesDataUsulanDiscPrins($No,$Produk );

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function prosesUlangDataSO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_order->prosesUlangDataSO($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function viewapprovalpusat()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_order->viewapproval($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	
	public function UnlockTrans()
	{
		if ($this->logged) 
		{
			$status = $_POST['status'];
			if($status =='DO'){
				$this->db->query("update mst_trans 
										   set counter=0,
										       user_trans ='".$this->user."',
										       trans_date ='".date('Y-m-d H:i:s')."'
									 where Nama_transaksi='DO'");
			}elseif($status =='Kiriman'){
				$this->db->query("update mst_trans set counter=0,
										       user_trans ='".$this->user."',
										       trans_date ='".date('Y-m-d H:i:s')."' 
										    where Nama_transaksi='Kiriman'");
			}elseif($status =='Closing'){
				$this->db->query("update mst_closing
                                    	set flag_stok ='Y'");
			}
			echo json_encode(array("status" => true));
		}else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function updateDataSOapprovalbyno()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_order->updateDataSOapprovalbyno($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function listPelanggan2()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Pelanggan2();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function dataretail()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/dataretail.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listretail()
	{
		if ($this->logged) 
		{
			$list = $this->Model_order->dataretail()->result();
         	$data = array();
         	$no = $_POST['start'];
			foreach ($list as $baris) {
				$no++;
				$row = array();
				$row[] = $no;

	 			$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
	 			$row[] = (!empty($baris->Kode) ? $baris->Kode : "");
	 			$row[] = (!empty($baris->Kode) ? $baris->addedTime: "");
	 			$row[] = (!empty($baris->Kode) ? $baris->ModifiedTime : "");
				$data[] = $row;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}else 
		{
			redirect("auth/logout");
		}
	}

	public function updateDataretail()
	{
		if ($this->logged) 
		{
			$status = $this->Model_order->updateDataretail();
			$valid=true;
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}
	public function cekEDSIPA($Kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->cekEDSIPA($Kode);
			$cekapprove = $this->Model_order->cekAproveEDSIPA($Kode);
			$output = array(
		          "dataedsipa" => $data,
		          "dataaprove" => $cekapprove
		       );
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function saveUsulanEDSIPA()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_order->saveDataUsulanEDSIAPA($params);
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

    public function dataUsulanEDSIPA()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='BM'||$this->userGroup=='CabangApoteker')	
			{				
				$this->twig->display('pembelian/datausulanbukaEDSIPA.html', $this->content);
			}else
			{
				redirect("main");
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataUsulanEDSIPA($cek=null)
	{
		$byLimit = "";
		$byfilter = "";

		if (isset($_REQUEST['filter'])) {
			if($_REQUEST['filter'] != ""){
				$byfilter = " and Status = '".$_REQUEST['filter']."'";
			}
		}

		$draw=$_REQUEST['draw'];
		$start=$_REQUEST['start'];
		$length=$_REQUEST['length'];
		if($_REQUEST['length'] != -1){
			$length=$_REQUEST['length'];
			$byLimit = " LIMIT ".$start.", ".$length;
		}

		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		
		$query = $this->Model_order->listDataUsulanEDSIPA($search,$byLimit,$cek);

		$total = $this->Model_order->listDataUsulanEDSIPA($search, "",$cek)->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		
		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}
		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if ($this->userGroup == 'CabangApoteker') {
					// $row[] = (!empty($baris->TimeSO) ? $baris->TimeSO : "");
					if(!empty($row->Approved_APTCAB)){
						$statausAprove = '-';
					}else{
						$statausAprove = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesaprove('."'".$row->No_Usulan."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesaprove('."'".$row->No_Usulan."'".', '."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
				
					}
					
				}else if ($this->userGroup == 'PusatApotik'){
					if(!empty($row->Approved_APTPST)){
						$statausAprove = '-';
					}else{
						$statausAprove = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesaprove('."'".$row->No_Usulan."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesaprove('."'".$row->No_Usulan."'".', '."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
				
					}
				}else{
					$statausAprove = '-';
				}
				if($row->Status_Pusat != "Berhasil"){
					$prosespusat = '<a class="btn btn-sm btn-info" title="Proses Pusat" onclick="prosespusat('."'".$row->No_Usulan."'".')" id="prosespusat"><i class="fa fa-eye"></i> Proses Pusat</a>';
				}else{$prosespusat =$row->Status_Pusat;}
				$kirimulang = '<a class="btn btn-sm btn-info" title="Proses Pusat" onclick="prosespusat('."'".$row->No_Usulan."'".')" id="prosespusat"><i class="fa fa-eye"></i> Proses</a>';
				
				$output['data'][]=array($nomor_urut,$statausAprove,$row->Cabang,$row->No_Usulan,$row->Tanggal,$row->Kode_Pelanggan,$row->Pelanggan,$row->Status_Usulan,$row->NoSO,$row->Approved_APTCAB,$row->Approved_APTPST,$prosespusat,$kirimulang);
				$nomor_urut++;
			}
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","","");
		}
		echo json_encode($output);
	}

	public function prosesDataApprovalEDSIPA()
	{          
        if ($this->logged) 
		{	
			$NoUsulan = $_POST['No'];
			$status = $_POST['status'];
			$valid = $this->Model_order->prosesDataApprovalEDSIPA($NoUsulan,$status);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }


    public function updateDataUsulanEDSIPA()
	{
		if ($this->logged) 
		{
			// $status = $_POST['status'];
			$data = $this->Model_order->updateDataUsulanEDSIPA();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function prosesDataUsulanEDSIPA()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_order->prosesDataUsulanEDSIPA($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function RestartTrans()
	{
		if ($this->logged) 
		{
			if($this->userGroup == 'KSA' or $this->userGroup == 'Cabang'){
				$valid = $this->Model_order->RestartTrans();
			}else{
				$valid="KSA";
			}
			
			echo json_encode($valid);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function cekstokorder()
	{
		if ($this->logged) 
		{
			$produk = $_POST['produk'];
			$data = $this->Model_order->cekstokorder($produk);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function prosesulangtop()
	{
		if ($this->logged) 
		{
			$noso = $_POST['no'];
			$status = $_POST['status'];
			log_message("error",print_r($noso,true));
        	log_message("error",print_r($status,true));
			$data = $this->Model_order->prosesulangtop($noso,$status);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	
	public function dataPelanggan_acu2($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->dataPelanggan_acu2($kode);
			$detail = $this->Model_order->datapiutangPelanggan_acu2($kode);
			$output = array(
		          "data" => $data,
		          "detail" => $detail
		       );
			echo json_encode($output);
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
			$KodePelanggan = $_POST['KodePelanggan'];
			$data = json_decode($this->Model_order->getOrderSalesman($no),TRUE);
			$free_panel = $this->Model_order->get_onf($KodePelanggan)->row();
			// echo json_encode($free_panel);
			$prins1 = $free_panel->Prins_Onf;
			$Cab_onf = $free_panel->Cab_Onf;


			$data = $data['output'];
			
			echo json_encode( array('data' => $data,"prins1" => $prins1,"Cab_onf" => $Cab_onf ));
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function buatorderSPSalesman()
	{	
		if ($this->logged) 
		{
			if($this->userGroup=='Admin' || $this->userGroup=='Cabang' || $this->userGroup=='CabangEDP' || $this->userGroup=='KSA' || $this->userGroup=='Salesman')	
			{				
				$kodeLamaCabang = $this->Model_order->kodeLamaCabang();
				$kodeDokumen = $this->Model_order->kodeDokumen('SO');
				$counter = $this->Model_order->counter('SO');
				if ($counter) 
				{
					$no = $counter->Counter + 1;
		        }
		        else
		        	$no = 1000001;

		        $data_api = json_decode($this->Model_order->NoOrder(),TRUE);

				$query = $data_api['output'];

		        $noOrder = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
		        $this->content['noOrder'] = $noOrder;
		        $this->content['no_Order'] = $query;
		        $this->content['caraBayar'] = $this->Model_order->caraBayar();
				$this->twig->display('pembelian/buatorderSPSalesman.html', $this->content);
				//log_message('error',print_r($this->userGroup,true));
			}else
			{
				redirect("main");
				//log_message('error','xxxx');
			}			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listPelangganSP()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->PelangganSP();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}
