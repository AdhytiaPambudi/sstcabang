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
		  $data->Satuan = $satuan->Satuan;
		  $data->UnitStok = $satuan->UnitStok;
		  $data->uCogs = $satuan->UnitCOGS;
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
				if ($baris->Status == 'Closed' and empty($baris->NoDO)) {
					$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="Proses" onclick="prosesDataDO('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i>Proses</button>';
				}
				else
				{
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "-");
				}
				$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->TimeSO) ? $baris->TimeSO : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			// $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoSO."'".')"><i class="fa fa-eye"></i> Print</button>';
	 			$row[] = (!empty($baris->MonitoringApproval) ? $baris->MonitoringApproval : "");
	 			$row[] = (!empty($baris->MonitoringStatus) ? $baris->MonitoringStatus : "");
	 			if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoSO."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
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
			$data = $this->Model_order->rejectDataSO($no);
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
    public function listDataApprovalDP($cek=null)
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
		$query = $this->Model_order->listDataApprovalDP($search,$byLimit,$cek);

		$total = $this->Model_order->listDataApprovalDP($search, "",$cek)->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		
		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}
		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if ($this->userGroup == 'BM') {
					if($cek != 'all'){
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->No_Usulan."'".', '."'".$row->KodeProduk."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->No_Usulan."'".', '."'".$row->KodeProduk."'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
					}else{
						$statusPusat = "-";
					}
					
				}else{
					$statusPusat = "-";
				}
				$prosespusat = '<a class="btn btn-sm btn-info" title="Proses Pusat" onclick="prosespusat('."'".$row->No_Usulan."'".')" id="prosespusat"><i class="fa fa-eye"></i> Proses Pusat</a>';
				$discprins = $row->disc_prins1 + $row->disc_prins2;
				$output['data'][]=array($nomor_urut,$statusPusat,$row->Cabang,$row->No_Usulan,$row->tanggal,$row->Prinsipal,$row->Tipe_trans,$row->tglmulai,$row->tglakhir,$row->KodeProduk,$row->NamaProduk,$row->status,$row->qty1,$row->qty2,$discprins,$row->KodePelanggan,$row->NamaPelanggan,$row->Approved_BM,$row->Approved_prins,$prosespusat);
				$nomor_urut++;
			}
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","","","","","","","","","");
		}
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
			$valid = $this->Model_order->prosesDataUsulanDiscPrins($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}