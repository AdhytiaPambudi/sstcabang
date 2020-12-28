<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Faktur extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_faktur');
		$this->load->model('pembelian/Model_kiriman');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->cabang = $this->session->userdata('cabang');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function datafaktur()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datafaktur.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datafaktur2()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datafaktur2.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataFaktur($cek = null)
	{	
		if ($this->logged) 
		{
	        $params = $this->input->post();
	        $tipe = $this->input->post('tipe');
	        $byperiode = "";

	        if(isset($params['tgl1']) && isset($params['tgl2'])){
		        $tgl1 = $this->input->post('tgl1');
		        $tgl2 = $this->input->post('tgl2');
	        	$byperiode = " and TglFaktur between '".$tgl1."' and '".$tgl2."' ";
	        }
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = "";

			$bySearch = "";
			$byLimit = "";
			$draw = "";
			if (isset($_REQUEST['draw'])) {
				$draw=$_REQUEST['draw'];
			}

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
			
			// $length = (isset($_REQUEST['length']) != -1) ? $_REQUEST['length'] : "";

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start = "";
			if (isset($_REQUEST['start'])) {
				$start=$_REQUEST['start'];
			}

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search = "";
			if (isset($_REQUEST['search']['value'])) {
				$search=$_REQUEST['search']['value'];
			}


			/*Menghitung total data didalam database*/
			//if($cek=='all')
			//	$total = $this->Model_faktur->listData('','','',$tipe)->num_rows();
			//else
			//	$total = $this->Model_faktur->listData('','','Open',$tipe)->num_rows();

			if($cek=='all')
				$total = $this->Model_faktur->listDataAll('','','',$tipe, $byperiode)->num_rows();
			else
				$total = $this->Model_faktur->listDataAll('','','Open',$tipe, $byperiode)->num_rows();

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
			$bySearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or StatusInkaso like '%".$search."%' or TipeDokumen like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			//if($cek=='all')
			//	$query=$this->Model_faktur->listData($bySearch,$byLimit,'',$tipe)->result();
			//else
			//	$query=$this->Model_faktur->listData($bySearch, $byLimit, "Open", $tipe)->result();

			if($cek=='all')
				$query=$this->Model_faktur->listDataAll($bySearch,$byLimit,'',$tipe, $byperiode)->result();
			else
				$query=$this->Model_faktur->listDataAll($bySearch, $byLimit, "Open", $tipe, $byperiode)->result();

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			//if($search!=""){			
			//	if($cek=='all')
			//		$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'','',$tipe)->num_rows();
			//	else
			//		$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'', "Open", $tipe)->num_rows();
			//}

			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listDataAll($bySearch,'','',$tipe, $byperiode)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listDataAll($bySearch,'', "Open", $tipe, $byperiode)->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoFaktur."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				if (empty($baris->StatusInkaso)) {
					$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "" ).'" dokumen="'.$baris->TipeDokumen.'"></p>';
				}
				else{
					$row[] = '<p align="center"><input type="checkbox" name="exp" id="exp" value="" disabled=""></p>';
				}
				// $row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "" ).'" dokumen="'.$baris->TipeDokumen.'"></p>';
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Print</button>';
				$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
	 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
	 			$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? number_format($baris->ValueCashDiskon) : 0);
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			$row[] = (!empty($baris->Saldo) ? number_format($baris->Saldo) : 0);
	 			$row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "-");
	 			$row[] = (!empty($baris->nodih) ? $baris->nodih : "");
	 			$row[] = (!empty($baris->tgldih) ? $baris->tgldih : "");
	 			$row[] = (!empty($baris->alasan_jto) ? $baris->alasan_jto : "");
	 			
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

	public function prosesDataFaktur()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_faktur->prosesDataFaktur($No);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listDataDetailFaktur()
	{
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];	
			$TipeDokumen = $_POST['TipeDokumen'];
			// $TipeDokumen = isset($_POST['tipe']) ? $_POST['tipe'] : "";
			$data = $this->Model_faktur->dataFaktur($no,$TipeDokumen);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}
	public function listDataDetailFakturCek()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];	
			$cndn = $_POST['cndn'];	
			$TipeDokumen = 'Faktur';		
			$cek = $this->Model_faktur->cekDataCNDN($no);
			$data = "";
			if ($cek > 0){
				$data = 'duplikat';
			}
			else{			
				$data = $this->Model_faktur->dataFakturCN($no,$TipeDokumen);
				foreach ($data as $item) {
					$kode = $item->KodeProduk;
					$cekjual = $this->Model_faktur->cekjualcndn($kode);
					if($cekjual[0]->flag_jual_cndn == 'N' && $cndn == 'CN'){
						$data = 'nojual';
						break;
					}
				}
			}
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function updateDataFakturPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_faktur->updateDataFakturPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function buatInkaso()
	{          
        if ($this->logged) 
		{	
			$status = false;
			if (isset($_POST['list'])) 
			{
				$list = $_POST['list'];
				foreach ($list as $key => $value) {
					$status = $this->Model_faktur->buatInkaso($list[$key]);
					// $status = $this->Model_faktur->prosesDataFaktur($list[$key]);			
				}
			}
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function buatdih()
	{	
		if ( $this->logged ) 
		{
			if(isset($_POST)){
				$this->content['date1'] = $this->input->post('date1');
				$this->content['date2'] = $this->input->post('date2');
				$this->content['pelanggan'] = $this->input->post('pelanggan');
				$this->content['salesman'] = $this->input->post('salesman');
			}

			// if($this->logs['user_group']=='Admin' || $this->logs['user_group']=='Cabang' || $this->logs['user_group']=='CabangInkaso')
			// {
				$this->twig->display('pembelian/buatdih.html', $this->content);
			// }else
			// {
				// redirect("main");
			// }

		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataInkaso()
	{	
		if ($this->logged) 
		{
			$date1 = (!empty($_POST['date1']) ? $_POST['date1'] : "");
			$date2 = (!empty($_POST['date2']) ? $_POST['date2'] : "");
			$pelanggan = (!empty($_POST['pelanggan']) ? $_POST['pelanggan'] : "");
			$salesman = (!empty($_POST['salesman']) ? $_POST['salesman'] : "");

			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = [];

			$bySearch = "";
			$byLimit = "";
			$draw = "";
			if (isset($_REQUEST['draw'])) {
				$draw=$_REQUEST['draw'];
			}

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start = "";
			if (isset($_REQUEST['start'])) {
				$start=$_REQUEST['start'];
			}

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search = "";
			if (isset($_REQUEST['search']['value'])) {
				$draw=$_REQUEST['search']['value'];
			}


			/*Menghitung total data didalam database*/
			$total = $this->Model_faktur->listDataInkaso('','',$date1, $date2, $pelanggan, $salesman)->num_rows();

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
			$bySearch = " where NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or StatusInkaso like '%".$search."%'";
			}

			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;

			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
				$query=$this->Model_faktur->listDataInkaso($bySearch, $byLimit, $date1, $date2, $pelanggan, $salesman)->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listDataInkaso($bySearch, '', $date1, $date2, $pelanggan, $salesman)->num_rows();
			}
			
			$x = $start;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$giroGantung = $this->Model_faktur->listGiroGantung($baris->NoFaktur);
				$totalGiro = (!empty($giroGantung->Total) ? $giroGantung->Total : 0);
				if ($baris->TipeDokumen == "Faktur") {
					if ($baris->Saldo > $totalGiro) {
						$x++;
						$row = array();
						$row[] = $x;
						$row[] = '<p align="center"><input type="checkbox" name="list['.$i.']" id="list'.$x.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "").'~'.$baris->dok.'" onchange="ceklis(this)"></p>';
						// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
						$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
			 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
			 			// $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
			 			// $row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
			 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
			 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
			 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
			 			// $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
			 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
			 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
			 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
			 			$row[] = (!empty($baris->Acu2) ? $baris->Acu2 : "");
			 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
			 			// $row[] = (!empty($baris->ValueCashDiskon) ? number_format($baris->ValueCashDiskon) : 0);
			 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
			 			$row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
			 			// $row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
			 			// $row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
			 			// $row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
			 			// $row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
			 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
			 			$row[] = (!empty($baris->Saldo) ? number_format($baris->Saldo) : 0);
			 			// $row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "-");
			 			
						$data[] = $row;
						$i++;
					}
				}
				else{
					$x++;
						$row = array();
						$row[] = $x;
						$row[] = '<p align="center"><input type="checkbox" name="list['.$i.']" id="list'.$x.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "").'~'.$baris->dok.'" onchange="ceklis(this)"></p>';
						// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".')"><i class="fa fa-eye"></i> Lihat</button>';
						$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
			 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
			 			// $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
			 			// $row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
			 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
			 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
			 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
			 			// $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
			 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
			 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
			 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
			 			$row[] = (!empty($baris->Acu2) ? $baris->Acu2 : "");
			 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
			 			// $row[] = (!empty($baris->ValueCashDiskon) ? number_format($baris->ValueCashDiskon) : 0);
			 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
			 			$row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
			 			// $row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
			 			// $row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
			 			// $row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
			 			// $row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
			 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
			 			$row[] = (!empty($baris->Saldo) ? number_format($baris->Saldo) : 0);
			 			// $row[] = '-';
			 			
						$data[] = $row;
						$i++;
				}

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

	public function saveDataDIH()
	{          
        if ($this->logged) 
		{	
			$list = array_column($_POST['list'], 'value');
			$status = false;
			if (isset($_POST['list'])) 
			{
					// $noDIH = "18241710188";
					$valid = $this->Model_faktur->saveDataDIH($list, $_POST['penagih']);
					if($valid){
						$status = true;
					}
					// $status = $this->Model_faktur->prosesDataDIH($noDIH);			
			}
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function prosesDataDIH()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_faktur->prosesDataDIH($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listPenagih()
	{
		if ($this->logged) 
		{
			$data = $this->Model_faktur->Penagih();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function buatretur()
	{	
		
		if ( $this->logged ) 
		{
			$kodeLamaCabang = $this->Model_main->kodeLamaCabang();
			$kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
			$kodeDokumen = $this->Model_main->kodeDokumen('Retur');
			$counter = $this->Model_main->counter('Retur');
			if ($counter) 
			{
				$no = $counter->Counter + 1;
	        }
	        else
	        {
	        	$no = 1000001;
	        }
	        $cektglstok = $this->Model_main->cek_tglstoktrans();
	        $dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$retur = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
			        $this->content['retur'] = $retur;
					$this->twig->display('pembelian/buatretur.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listPelangganRetur()
	{
		if ($this->logged) 
		{
			$data = $this->Model_faktur->listPelangganRetur();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function cekPelangganRetur($kode = null)
	{
		if ($this->logged) 
		{
			$data = $this->Model_faktur->cekPelangganRetur($kode);

			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listFaktur($kode = null)
	{
		if ($this->logged) 
		{
			$data = $this->Model_faktur->listFaktur($kode);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveDataRetur()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			// *** cek folder ***
			if (!file_exists(getcwd().'/assets/dokumen/retur')) {
			    mkdir(getcwd().'/assets/dokumen/retur', 0777, true);
			}
			$path = getcwd().'/assets/dokumen/retur/';
			$name1 = "";

			if(!empty($_FILES['file']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['file']['tmp_name'];
                $explode1 = explode(".", $_FILES['file']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }
            $cektglstok = $this->Model_main->cek_tglstoktrans();
            $dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
            if($cektglstok == 1){
            	if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
            		$valid = $this->Model_faktur->saveDataRetur($params,$name1);
					echo json_encode(array("status" => $valid));
				}else{
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}else if($cektglstok == 0){
				$data=false;
				echo json_encode(array("status" => $data));
				// echo json_encode($data);
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listProdukAll()
	{
		if ($this->logged) 
		{
			$data = $this->Model_main->Produk();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function prosesReqRetur()
	{          
        if ($this->logged) 
		{	
			$kode = $_POST['kode'];
			$status = $this->Model_faktur->prosesReqRetur($kode);
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function buatcndn()
	{	
		if ( $this->logged ) 
		{
			$no = $this->Model_main->noDokumen("CN");
	        $this->content['no'] = $no;
			$this->twig->display('pembelian/buatcndn.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveDataCNDN()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_faktur->saveDataCNDN($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

 //    public function updateDataPelangganPusat()
	// {
	// 	if ($this->logged) 
	// 	{
	// 		$status = $this->Model_faktur->updateDataPelangganPusat();
	// 		echo json_encode(array("status" => TRUE, "ket" => $status));
	// 	}
	// 	else 
	// 	{	
	// 		redirect("auth/logout");
	// 	}
	// }

	public function listReqPelanggan()
	{	
		if ($this->logged) 
		{
	        $data = array();  
			$list = $this->Model_faktur->listReqPelanggan();
			$x = 0;
			$status = "";
			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->Kode) ? $baris->Kode : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			if ($baris->Status_Retur == "ya")
	 				$status = "Approve";
	 			elseif ($baris->Status_Retur == "request")
	 				$status = "Request";
	 			$row[] = $status;
	 			
				$data[] = $row;
			}
			$output = array(
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function dataretur()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/dataretur.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datacndn()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datacndn.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataCNDN($cek = null)
	{	
		if ($this->logged) 
		{
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = [];

			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start=$_REQUEST['start'];

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search=$_REQUEST['search']["value"];


			/*Menghitung total data didalam database*/
			if($cek=='all')
				$total = $this->Model_faktur->listDataCNDN()->num_rows();
			else
				$total = $this->Model_faktur->listDataCNDN('','','CNOK')->num_rows();

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
			$bySearch = " and (NoDokumen like '%".$search."%' or TanggalCNDN like '%".$search."%' or Faktur like '%".$search."%' or KodeProduk like '%".$search."%' or Pelanggan like '%".$search."%'  or Prinsipal like '%".$search."%' or Produk like '%".$search."%' or StatusCNDN like '%".$search."%' or Status like '%".$search."%' or Perhitungan like '%".$search."%' or DasarPerhitungan like '%".$search."%' or Persen like '%".$search."%' or Rupiah like '%".$search."%' or Jumlah like '%".$search."%' or Banyak like '%".$search."%' or Total like '%".$search."%' )";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_faktur->listDataCNDN($bySearch, $byLimit)->result();
			else
				$query=$this->Model_faktur->listDataCNDN($bySearch, $byLimit, "CNOK")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listDataCNDN($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listDataCNDN($bySearch, '', "CNOK")->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				// if ($baris->statusPusat == 'Gagal') {
				// 	$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDokumen."'".')"><i class="fa fa-check"></i> Proses</button>';
				// }
				// else
				// {
				// 	$row[] = 'Berhasil';
				// }
				// if (empty($baris->StatusInkaso)) {
				// 	$row[] = '<p align="center"><input type="checkbox" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoDokumen : "").'"></p>';
				// }
				// else{
				// 	$row[] = '<p align="center"><input type="checkbox" name="exp" id="exp" value="" disabled=""></p>';
				// }
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
				$row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
	 			$row[] = (!empty($baris->TanggalCNDN) ? $baris->TanggalCNDN : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Faktur) ? $baris->Faktur : "");
	 			$row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
	 			// $row[] = (!empty($baris->Supplier1) ? $baris->Supplier1 : "");
	 			$row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
	 			$row[] = (!empty($baris->Produk) ? $baris->Produk : "");
	 			$row[] = (!empty(number_format($baris->DasarPerhitungan)) ? number_format($baris->DasarPerhitungan) : 0);
	 			$row[] = (!empty($baris->Persen) ? $baris->Persen : "");
	 			$row[] = (!empty($baris->Rupiah) ? $baris->Rupiah : "");
	 			// $row[] = (!empty($baris->Jumlah) ? $baris->Jumlah : "");
	 			$row[] = (!empty($baris->Banyak) ? $baris->Banyak : "");
	 			$row[] = (!empty($baris->DscCab) ? $baris->DscCab : "");
	 			$row[] = (!empty($baris->ValueDscCab) ? number_format($baris->ValueDscCab) : 0);
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			// $row[] = (!empty($baris->StatusCNDN) ? $baris->StatusCNDN : 0);
	 			// $row[] = (!empty($baris->NoUsulan) ? $baris->NoUsulan :"");	 			
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

	public function listfakturexcel(){
		if ($this->logged) 
		{
			$query=$this->Model_faktur->listDataAll('','LIMIT 0, 10','Open','')->result();
			echo json_encode($query);
			// return $query;
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function buatdih2()
	{	
		if ( $this->logged ) 
		{
			if(isset($_POST)){
				$this->content['date1'] = $this->input->post('date1');
				$this->content['date2'] = $this->input->post('date2');
				$this->content['pelanggan'] = $this->input->post('pelanggan');
				$this->content['salesman'] = $this->input->post('salesman');
			}

			// if($this->logs['user_group']=='Admin' || $this->logs['user_group']=='Cabang' || $this->logs['user_group']=='CabangInkaso')
			// {
				$this->twig->display('pembelian/buatdih2.html', $this->content);
			// }else
			// {
				// redirect("main");
			// }

		}
		else 
		{
			redirect("auth/logout");
		}
	}
	
	public function listDataInkaso2()
	{
		if ($this->logged) 
		{
			$data = $this->Model_faktur->listDataInkaso2();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function getListFakturDIH()
	{
		if ($this->logged) 
		{
			$nofaktur = $_POST['nofaktur'];
			$data = $this->Model_faktur->getListFakturDIH($nofaktur);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveDataDIH2()
	{          
        if ($this->logged) 
		{	
			$valid=true;
			$params = (object)$this->input->post();
			$nodih = $this->Model_faktur->saveDataDIH2($params);
			if(empty($nodih)){
				$valid = false;
			}else{
				$valid = true;
			}
			echo json_encode(array("status" => $valid,"nodih" =>$nodih));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listprintfaktur2()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/listprintfaktur2.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

    public function listPrintDataFaktur($cek = null)
	{	
		if ($this->logged) 
		{
	        $params = $this->input->post();
	        $tipe = $this->input->post('tipe');
	        $byperiode = "";

	        if(isset($params['tgl1']) && isset($params['tgl2'])){
		        $tgl1 = $this->input->post('tgl1');
		        $tgl2 = $this->input->post('tgl2');
	        	$byperiode = " and TglFaktur between '".$tgl1."' and '".$tgl2."' ";
	        }
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = "";

			$bySearch = "";
			$byLimit = "";
			$draw = "";
			if (isset($_REQUEST['draw'])) {
				$draw=$_REQUEST['draw'];
			}

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
			
			// $length = (isset($_REQUEST['length']) != -1) ? $_REQUEST['length'] : "";

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start = "";
			if (isset($_REQUEST['start'])) {
				$start=$_REQUEST['start'];
			}

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search = "";
			if (isset($_REQUEST['search']['value'])) {
				$search=$_REQUEST['search']['value'];
			}


			/*Menghitung total data didalam database*/
			//if($cek=='all')
			//	$total = $this->Model_faktur->listData('','','',$tipe)->num_rows();
			//else
			//	$total = $this->Model_faktur->listData('','','Open',$tipe)->num_rows();

			if($cek=='all')
				$total = $this->Model_faktur->listPrintDataAll('','','',$tipe, $byperiode)->num_rows();
			else
				$total = $this->Model_faktur->listPrintDataAll('','','Open',$tipe, $byperiode)->num_rows();

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
			$bySearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or StatusInkaso like '%".$search."%' or TipeDokumen like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			//if($cek=='all')
			//	$query=$this->Model_faktur->listData($bySearch,$byLimit,'',$tipe)->result();
			//else
			//	$query=$this->Model_faktur->listData($bySearch, $byLimit, "Open", $tipe)->result();

			if($cek=='all')
				$query=$this->Model_faktur->listPrintDataAll($bySearch,$byLimit,'',$tipe, $byperiode)->result();
			else
				$query=$this->Model_faktur->listPrintDataAll($bySearch, $byLimit, "Open", $tipe, $byperiode)->result();

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			//if($search!=""){			
			//	if($cek=='all')
			//		$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'','',$tipe)->num_rows();
			//	else
			//		$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'', "Open", $tipe)->num_rows();
			//}

			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listPrintDataAll($bySearch,'','',$tipe, $byperiode)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listPrintDataAll($bySearch,'', "Open", $tipe, $byperiode)->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoFaktur."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				if (empty($baris->StatusInkaso)) {
					$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "" ).'" dokumen="'.$baris->TipeDokumen.'"></p>';
				}
				else{
					$row[] = '<p align="center"><input type="checkbox" name="exp" id="exp" value="" disabled=""></p>';
				}
				// $row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "" ).'" dokumen="'.$baris->TipeDokumen.'"></p>';
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Print</button>';
				$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
	 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
	 			$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? number_format($baris->ValueCashDiskon) : 0);
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			$row[] = (!empty($baris->Saldo) ? number_format($baris->Saldo) : 0);
	 			$row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "-");
	 			
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
	public function listDetailFaktur()
	{
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];	
			$TipeDokumen = $_POST['TipeDokumen'];
			// $TipeDokumen = isset($_POST['tipe']) ? $_POST['tipe'] : "";
			$data = $this->Model_faktur->listDetailFaktur($no,$TipeDokumen);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}
	
}