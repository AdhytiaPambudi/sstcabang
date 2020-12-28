<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kiriman extends CI_Controller {

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
		$this->load->model('pembelian/Model_kiriman');
		$this->load->model('pembelian/Model_order');
		$this->load->model('pembelian/Model_faktur');
		$this->load->model('pembelian/Model_closing');
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

	public function buatkiriman()
	{	
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/buatkiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function buatpickinglist()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/picking_list.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataDO($cek = null)
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
			
			$draw="";
			if(isset($_REQUEST['draw'])){
				$draw=$_REQUEST['draw'];
			}

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length = $_REQUEST['length'];
				}
			}

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start="";
			if(isset($_REQUEST['start'])){
				$draw=$_REQUEST['start'];
			}

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search="";
			if(isset($_REQUEST['search']["value"])){
				$draw=$_REQUEST['search']["value"];
			}


			/*Menghitung total data didalam database*/
			if($cek=='all')
				$total = $this->Model_kiriman->listDataDO()->num_rows();
			else
				$total = $this->Model_kiriman->listDataDO('','','Open')->num_rows();

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
			$bySearch = " and (NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or Pengirim like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoOrder like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Open")->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->Status == "Open" || $baris->Status == "Kembali") {
					// $row[] = '<button type="button" class="btn-sm btn-info" onclick="showModal('.$baris->NoDO.', '."'edit'".')">Edit</button>';
					// $row[] = '-';
					$row[] = '<button type="button" class="btn-sm btn-warning" href="javascript:void(0)" title="Lihat" onclick="batal('."'".$baris->NoDO."'".')">Retur</button>';
					$row[] = '<p align="center"><input type="checkbox" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoDO) ? $baris->NoDO : "").'"></p>';
				}
				else{
					$row[] = '<button type="button" class="btn-sm btn-info" onclick="showModal('.$baris->NoDO.', '."'view'".')">View</button>';
					$row[] = "-";
					$row[] = '<p align="center"><input type="checkbox" name="exp" id="exp" value="" disabled=""></p>';
				}

				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			// $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? $baris->ValueCashDiskon : "");
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoOrder) ? $baris->TglJtoOrder : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			
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

	public function listDataPicking($cek = null)
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
				$total = $this->Model_kiriman->listDataPicking()->num_rows();
			else
				$total = $this->Model_kiriman->listDataPicking('','','Open')->num_rows();

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
			$bySearch = " and (trs_delivery_order_sales.NoDO like '%".$search."%' or trs_delivery_order_sales.TglDO like '%".$search."%' or  trs_delivery_order_sales.Pengirim like '%".$search."%' or trs_delivery_order_sales.Pelanggan like '%".$search."%' or trs_delivery_order_sales.NamaPelanggan like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_kiriman->listDataPicking($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman->listDataPicking($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataPicking($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataPicking($bySearch, '', "Open")->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->Status == "Open" and $baris->picking_list == "") {
					$row[] = '<p align="center"><input type="checkbox" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoDO) ? $baris->NoDO : "").'"></p><p align="center"><input type="hidden" name="noline['.$i.']" id="noline'.$i.'" value="'.(!empty($baris->noline) ? $baris->noline : "").'"></p>';
					// $row[] = '';
				}else{
					$row[] = '<p align="center"><input type="checkbox" name="exp" id="exp" value="" disabled=""></p>';
				}
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = '<p align="center"><input type="text" style="width:100px" readonly="" class="form-control"  name="KodeProduk['.$i.']" id="KodeProduk'.$i.'" value="'.(!empty($baris->KodeProduk) ? $baris->KodeProduk : "").'"></p>';
	 			$row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
	 			$row[] = (!empty($baris->QtyDO) ? $baris->QtyDO : 0);
	 			$row[] = (!empty($baris->UOM) ? $baris->UOM : "");
	 			$row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");	 
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->AlamatKirim) ? $baris->AlamatKirim : "");			
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

	public function updatepickinglist()
	{          
        if ($this->logged) 
		{	
			$status = false;
			$params = json_decode(json_encode((object)$this->input->post()),true);
			$list = $params["list"];
			$noline = $params["noline"];
			$kode = $params["KodeProduk"];
			$i=-1;
			foreach ($noline as $key) {
				$i++;
				if(!empty($list[$i])){
					$x=$i;
					$noDO = $list[$x];
					$line = $noline[$x];
					$prod = $kode[$x];
					$status = $this->Model_kiriman->updatepickinglist($noDO,$line,$prod);
				}
				
			}
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

	public function saveKiriman()
	{          
        if ($this->logged) 
		{	
			$status = false;
			if (isset($_POST['list'])) 
			{
				$list = $_POST['list'];
				$pengirim = $_POST['pengirim'];
				// $kodeLamaCabang = $this->Model_order->kodeLamaCabang();
				// $kodeDokumen = $this->Model_order->kodeDokumen('Kiriman');
				//================ Running Number ======================================//
		        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
		        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
		        $kodeDokumen = $this->Model_main->kodeDokumen('Kiriman');
		        $data = $this->db->query("select max(right(NoKiriman,7)) as 'no' from trs_kiriman where  Cabang = '".$this->cabang."' and length(NoKiriman) = 13")->result();
		        if(empty($data[0]->no) || $data[0]->no == ""){
		          $lastNumber = 1000001;
		        }else {
		          $lastNumber = $data[0]->no + 1;
		        }
		        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;

				foreach ($list as $key => $value) {
					$NoDO = $this->Model_kiriman->saveKiriman($list[$key], $noDokumen, $pengirim);
					// $status = $this->Model_kiriman->prosesDataDO($list[$key]);			
				}
				// $status = $this->Model_kiriman->prosesDataKiriman($noDokumen);
			}
			echo json_encode(array("status" => $noDokumen, ));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function terimaKiriman()
	{
		if ($this->logged) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/terimakiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listTerimaKiriman()
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
			$length=$_REQUEST['length'];

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start=$_REQUEST['start'];

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search=$_REQUEST['search']["value"];


			/*Menghitung total data didalam database*/
			$total = $this->Model_kiriman->listKiriman()->num_rows();

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
			$bySearch = " and (NoKiriman like '%".$search."%' or TglKirim like '%".$search."%' or StatusKiriman like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
				$query=$this->Model_kiriman->listKiriman($bySearch, $byLimit)->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listKiriman($bySearch)->num_rows();
			}

			$x = 0;
			foreach ($query as $key) {
				$x++;
				$row = array();
	 			$row[] = $x;
	 			$row[] = (!empty($key->NoKiriman) ? $key->NoKiriman : "");
	 			$row[] = (!empty($key->TglKirim) ? $key->TglKirim : "");
	 			// $row[] = (!empty($key->NoDO) ? $key->NoDO : "");
	 			$row[] = (!empty($key->StatusKiriman) ? $key->StatusKiriman : "");
				$row[] = '<button type="button" class="btn btn-sm btn-warning" title="Terima" onclick="terima('."'".$key->NoKiriman."'".')" id="Terima"><i class="fa fa-check"></i> Terima</button>'; 
				// $row[] = '<a class="btn btn-sm btn-warning" href="javascript:void(0)" title="Terima" disabled><i class="fa fa-check"></i> Terima</a>';
			
				$data[] = $row;
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

	public function formTerimaKiriman()
	{
		if ($this->logged) 
		{
			if (isset($_POST['no'])) 
			{
				$no = $_POST['no'];
				$data = $this->Model_kiriman->getTerimaKiriman($no);
				$this->content['kode'] = $no;
				$this->content['data'] = $data; 
				$this->twig->display('pembelian/formterimakiriman.html', $this->content);
			}
			else{
				redirect("terimakiriman");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveTerimaKiriman()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$x=[];
			$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='Kiriman' limit 1")->row();
			$cek = $cek->counter;
			if($cek == '0'){
				$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='Kiriman'");
				$valid = $this->Model_kiriman->saveTerimaKiriman($params);
				// if($NoFaktur){
				// 	$valid=true;
				// }
				$this->db->query("update mst_trans set counter = '0', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='Kiriman'");
				$x = ["status"=>$valid, 'pesan'=>'sukses'];
			}else{
				$valid=false;
				$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
				
			}
			echo json_encode($x);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

	public function getDataDO()
	{
		if ($this->logged) 
		{
			$noDO = $_POST['noDO'];
			$data = $this->Model_kiriman->getDataDO($noDO);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function updateDataDO()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$result = $this->Model_kiriman->cekStok($params);
			if ($result == true) {
				$NoDO = $this->Model_kiriman->updateDataDO($params);
				$status = $this->Model_kiriman->prosesDataDO($NoDO);
				echo json_encode(array("status" => true));
			}
			else{
				echo json_encode(array("status" => false));	
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function datado()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datado.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDO($cek = null)
	{	
		if ($this->logged) 
		{
			$data = "";
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			if($cek=='All')
				{
					$total = $this->Model_kiriman->listDataDO()->num_rows();
				}
			elseif($cek=='Open')
				{
					$total = $this->Model_kiriman->listDataDO('','','Open')->num_rows();
				}
			elseif($cek=='Closed')
				{
					$total = $this->Model_kiriman->listDataDO('','','Closed')->num_rows();
				}
			elseif($cek=='Kirim')
				{
					$total = $this->Model_kiriman->listDataDO('','','Kirim')->num_rows();
				}
			elseif($cek=='Batal')
				{
					$total = $this->Model_kiriman->listDataDO('','','Batal')->num_rows();
				}
			else{
				$total = $this->Model_kiriman->listDataDO('','','Open')->num_rows();
			}
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
			$bySearch = " and (trs_delivery_order_sales.NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or trs_delivery_order_sales.Pengirim like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoOrder like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');

			if($cek=='All')
				{
					$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit)->result();
				}
			elseif($cek=='Open')
				{
					$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Open")->result();
				}
			elseif($cek=='Closed')
				{
					$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Closed")->result();
				}
			elseif($cek=='Kirim')
				{
					$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Kirim")->result();
				}
			elseif($cek=='Batal')
				{
					$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Batal")->result();
				}
			else{
				$query=$this->Model_kiriman->listDataDO($bySearch, $byLimit, "Open")->result();
			}
			

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
				if($cek=='All')
					{
						$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch)->num_rows();
					}
				elseif($cek=='Open')
					{
						$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Open")->num_rows();
					}
				elseif($cek=='Closed')
					{
						$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Closed")->num_rows();
					}
				elseif($cek=='Kirim')
					{
						$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Kirim")->num_rows();
					}
				elseif($cek=='Batal')
					{
						$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Batal")->num_rows();
					}	
				else
				{
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO($bySearch, '', "Open")->num_rows();
				}	
				
			}



			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDO."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoDO) ? $baris->NoDO : "").'"></p>';
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Print</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default empat" href="javascript:void(0)" title="Print 4x" onclick="cetak('."'".$baris->NoDO."'".','."'copy'".')"><i class="fa fa-eye"></i> Print 4x</button>';
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? $baris->ValueCashDiskon : "");
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoOrder) ? $baris->TglJtoOrder : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			
				$data[] = $row;
				$i++;
			}$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function prosesDataDO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_kiriman->prosesDataDO($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function datakiriman()
	{	
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/datakiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataKiriman($cek = null)
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
				$total = $this->Model_kiriman->listDataKiriman()->num_rows();
			else
				$total = $this->Model_kiriman->listDataKiriman('','','Open')->num_rows();

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
			$bySearch = " and (KodePelanggan like '%".$search."%' or NoKiriman like '%".$search."%' or StatusKiriman like '%".$search."%' or NoDO like '%".$search."%' or StatusDO like '%".$search."%' or Alasan like '%".$search."%' or TimeKirim like '%".$search."%' or TimeTerima like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_kiriman->listDataKiriman($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman->listDataKiriman($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataKiriman($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataKiriman($bySearch, '', "Open")->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = "";
				$row[] = $x;
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoKiriman."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			$row[] = (!empty($baris->StatusKiriman) ? $baris->StatusKiriman : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->NamaPengirim) ? $baris->NamaPengirim : "");
	 			$row[] = (!empty($baris->TglKirim) ? $baris->TglKirim : "");
	 			$row[] = (!empty($baris->TglTerima) ? $baris->TglTerima : "");
	 			$row[] = '<span class="btn btn-primary" onclick="view_detail('.$baris->NoKiriman.')">&nbsp;&nbsp;Detail&nbsp;&nbsp;</span>';
	 			$row[] = '<span class="btn btn-primary" onclick="cetak('.$baris->NoKiriman.')">&nbsp;&nbsp;Print&nbsp;&nbsp;</span>';
	 			
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

	public function prosesDataKiriman()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_kiriman->prosesDataKiriman($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listDataDetailDO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_kiriman->dataDO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function updateDataDOSalesPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_kiriman->updateDataDOPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function updateDataKirimanPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_kiriman->updateDataKirimanPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function listPengirim()
	{
		if ($this->logged) 
		{
			$data = $this->Model_kiriman->Pengirim();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listBatch()
	{
		if ($this->logged) 
		{
			$kode = $_POST['kode'];
			$data = $this->Model_kiriman->getBatch($kode);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	//  public function prosesbatalDataDo()
	// {
	// 	if ($this->logged) 
	// 	{
	// 		$NoDO = $_POST['no'];
	// 		$data = $this->Model_kiriman->prosesbatalDataDo($NoDO);
	// 		echo json_encode($data);
	// 	}
	// 	else 
	// 	{	
	// 		redirect("main/auth/logout");
	// 	}
	// }

	// public function prosesbatalDataDo()
	// {
	// 	if ($this->logged) 
	// 	{
	// 		$cektglstok = $this->Model_main->cek_tglstoktrans();
	// 		$cektglsettlement = $this->Model_main->cek_tglsettlement();
	// 		if($cektglstok == 1 and $cektglsettlement == 1 ){
	// 			$NoDO = $_POST['no'];
	// 			$data = $this->Model_kiriman->prosesbatalDataDo($NoDO);
	// 			echo json_encode($data);
	// 		}else if(($cektglstok == 1 and $cektglsettlement == 0) or ($cektglstok == 0)){
	// 			$data=false;
	// 			echo json_encode($data);
	// 		}
	// 	}
	// 	else 
	// 	{	
	// 		redirect("main/auth/logout");
	// 	}
	// }
	public function prosesbatalDataDo()
	{
		if ($this->logged) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$cektglsettlement = $this->Model_main->cek_tglsettlement();
			if($cektglstok == 1 and $cektglsettlement == 1 ){
				$NoDO = $_POST['no'];
				$bulan = date('m');
				$cekDO = $this->db->query("select month(tgldo) as 'bulando' from trs_delivery_order_sales where NoDO = '".$NoDO."'")->row();
				if($bulan == $cekDO->bulando){
					$data = $this->Model_kiriman->prosesbatalDataDo($NoDO);
					echo json_encode($data);
				}else{
					$data=false;
					echo json_encode($data);
				}
				
			}else if(($cektglstok == 1 and $cektglsettlement == 0) or ($cektglstok == 0)){
				$data=false;
				echo json_encode($data);
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listDO2($cek = null)
	{	
		if ($this->logged) 
		{
			$params = $this->input->post();
	        $tipe = $this->input->post('tipe');
	        $byperiode = "";

	        if(isset($params['tgl1']) && isset($params['tgl2'])){
		        $tgl1 = $this->input->post('tgl1');
		        $tgl2 = $this->input->post('tgl2');
	        	$byperiode = " and TglDO between '".$tgl1."' and '".$tgl2."' ";
	        }

			$data = "";
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			
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
			$total = $this->Model_kiriman->listDataDO2()->num_rows();
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " and (trs_delivery_order_sales.NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or trs_delivery_order_sales.Pengirim like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoOrder like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			$query=$this->Model_kiriman->listDataDO2($bySearch, $byLimit,$byperiode)->result();		

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataDO2($bySearch,'',$byperiode)->num_rows();
				
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDO."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				if($baris->Status == 'Batal' or $baris->Gross == 0){
					$row[] = '-';
				}else{
					$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoDO) ? $baris->NoDO : "").'"></p>';
				}
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Print</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default empat" href="javascript:void(0)" title="Print 4x" onclick="cetak('."'".$baris->NoDO."'".','."'copy'".')"><i class="fa fa-eye"></i> Print 4x</button>';
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? $baris->ValueCashDiskon : "");
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoOrder) ? $baris->TglJtoOrder : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);
	 			$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			
				$data[] = $row;
				$i++;
			}$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function validasiDO()
	{	
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/validasido.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listvalidasiDO($cek = null)
	{	
		if ($this->logged) 
		{
			$params = $this->input->post();
	        $tipe = $this->input->post('tipe');
	        $byperiode = "";

	        // if(isset($params['tgl1']) && isset($params['tgl2'])){
		       //  $tgl1 = $this->input->post('tgl1');
		       //  $tgl2 = $this->input->post('tgl2');
	        // 	$byperiode = " and TglDO between '".$tgl1."' and '".$tgl2."' ";
	        // }

			$data = [];
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			
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
			$total = $this->Model_kiriman->listDatavalidasiDO()->num_rows();
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " and (trs_delivery_order_sales.NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or trs_delivery_order_sales.Pengirim like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoOrder like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			$query=$this->Model_kiriman->listDatavalidasiDO($bySearch, $byLimit)->result();		

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDatavalidasiDO($bySearch,'')->num_rows();
				
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$x.']" id="list'.$x.'" value="'.(!empty($baris->NoDO) ? $baris->NoDO : "").'" onchange="centang('.$x.')"></p>';
				$row[] = '<button type="button" id="btn-validasi'.$x.'" class="btn btn-sm btn-success" href="javascript:void(0)" title="Validasi" onclick="Validasi('."'".$baris->NoDO."'".')"><i class="fa fa-check"></i> Validasi</button>';
				$row[] = '<button type="button" id="btn-retur'.$x.'" class="btn btn-sm btn-info" href="javascript:void(0)" title="Retur Faktur" onclick="retur('."'".$baris->NoDO."'".')"><i class="fa fa-check"></i> Retur</button>';
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? $baris->ValueCashDiskon : "");
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoOrder) ? $baris->TglJtoOrder : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);	 			
				$data[] = $row;
				$i++;
			}$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}
	public function prosesValidasiDO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_kiriman->prosesValidasiDO($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function prosesReturDO()
	{          
        if ($this->logged) 
		{	
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$NoDO = $_POST['No'];
						$bulan = date('m');
						$cekDO = $this->db->query("select month(tgldo) as 'bulando' from trs_delivery_order_sales where NoDO = '".$NoDO."'")->row();
						if($bulan == $cekDO->bulando){
							$No = $_POST['No'];
							$cekAcu = $this->db->query("SELECT ifnull(Acu,'') as 'Acu' 
									FROM trs_delivery_order_sales WHERE tipedokumen = 'Retur' 
									and Acu = '".$NoDO."'")->num_rows();
							if($cekAcu < 1){
								$data = $this->Model_kiriman->prosesReturDO($No);
							}else{
								$data =false;
							}
							echo json_encode(array("status" => $data));
							// echo json_encode($data);
						}else{
							$data=false;
							echo json_encode($data);
						}
					}else{
						$data=false;
						echo json_encode(array("status" => $data));
					}
				
				}else if($cektglstok == 0){
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}else{
				$No = $_POST['No'];
				$cekAcu = $this->db->query("SELECT ifnull(Acu,'') as 'Acu' 
									FROM trs_delivery_order_sales WHERE tipedokumen = 'Retur' and Acu = '".$No."'")->num_rows();
				if($cekAcu < 1){
					$data = $this->Model_kiriman->prosesReturDO($No);
				}else{
					$data =false;
				}
				echo json_encode(array("status" => $data));
			}	
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function approvalreturDO()
	{          
        if ( $this->logged ) 
		{
			$this->twig->display('pembelian/approvalReturDOPelanggan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
    }

    public function listdatareturDO($cek = null)
	{	
		if ($this->logged) 
		{
			$params = $this->input->post();
	        $tipe = $this->input->post('tipe');
	        $byperiode = "";

	        // if(isset($params['tgl1']) && isset($params['tgl2'])){
		       //  $tgl1 = $this->input->post('tgl1');
		       //  $tgl2 = $this->input->post('tgl2');
	        // 	$byperiode = " and TglDO between '".$tgl1."' and '".$tgl2."' ";
	        // }

			$data = "";
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			
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
			$total = $this->Model_kiriman->listDatareturDO()->num_rows();
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " and (trs_delivery_order_sales.NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or trs_delivery_order_sales.Pengirim like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoOrder like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			$query=$this->Model_kiriman->listDatareturDO($bySearch, $byLimit)->result();		

			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDatareturDO($bySearch,'')->num_rows();
				
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if ($this->userGroup == 'BM') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approve('."'".$baris->NoDO."'".','."'".$baris->Acu."'".')"><i class="fa fa-check"></i>Approve</button>';
					$row[] = '<button type="button" class="btn btn-sm btn-info" href="javascript:void(0)" title="Reject" onclick="reject('."'".$baris->NoDO."'".','."'".$baris->Acu."'".')"><i class="fa fa-check"></i> Reject</button>';
				}else{
					$row[] = '-';
					$row[] = '-';
				}
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
	 			$row[] = (!empty($baris->Acu) ? $baris->Acu : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
	 			$row[] = (!empty($baris->ValueCashDiskon) ? $baris->ValueCashDiskon : "");
	 			$row[] = (!empty($baris->TOP) ? $baris->TOP : "");
	 			$row[] = (!empty($baris->TglJtoOrder) ? $baris->TglJtoOrder : "");
	 			$row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
	 			$row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
	 			$row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
	 			$row[] = (!empty($baris->Ppn) ? number_format($baris->Ppn) : 0);
	 			$row[] = (!empty($baris->Total) ? number_format($baris->Total) : 0);	 			
				$data[] = $row;
				$i++;
			}$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function approvereturDO()
	{          
        if ($this->logged) 
		{	
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			$No = $_POST['No'];
			$Acu = $_POST['Acu'];
			$status = $_POST['status'];
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$bulan = date('m');
						
						$cekDO = $this->db->query("select month(tgldo) as 'bulando' from trs_delivery_order_sales where NoDO = '".$No."'")->row();
						if($bulan == $cekDO->bulando){
							$valid = $this->Model_kiriman->approvereturDO($No,$Acu,$status);
							echo json_encode(array("status" => $valid));
						}else{
							$valid=false;
							echo json_encode(array("status" => $valid));
						}
					}else{
						$valid=false;
						echo json_encode(array("status" => $valid));
					}
				}else{
					$valid=false;
					echo json_encode(array("status" => $valid));
				}
			}else{
				$valid = $this->Model_kiriman->approvereturDO($No,$Acu,$status);
				echo json_encode(array("status" => $valid));
			}
						
		}
		else 
		{	
			redirect("auth/logout");
		}
					
    }
    public function prosesTerimaDO()
	{          
        if ($this->logged) 
		{	
			$NoKiriman = $_POST['NoKiriman'];
			$NoDO = $_POST['NoDO'];
			$status = $_POST['status'];
			$alasan = $_POST['alasan'];
			$valid = $this->Model_kiriman->prosesTerimaDO($NoKiriman,$NoDO,$status,$alasan);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function datakirimandetail()
	{	
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/datakirimandetail.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataKirimandetail($cek = null)
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
				$total = $this->Model_kiriman->listDataKirimandetail()->num_rows();
			else
				$total = $this->Model_kiriman->listDataKirimandetail('','','Open')->num_rows();

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
			$bySearch = " and (KodePelanggan like '%".$search."%' or NoKiriman like '%".$search."%' or StatusKiriman like '%".$search."%' or NoDO like '%".$search."%' or KodePelanggan like '%".$search."%'or NoDO like '%".$search."%' or StatusDO like '%".$search."%' or Alasan like '%".$search."%' or TimeKirim like '%".$search."%' or TimeTerima like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_kiriman->listDataKirimandetail($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman->listDataKirimandetail($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataKirimandetail($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataKirimandetail($bySearch, '', "Open")->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			$row[] = (!empty($baris->StatusKiriman) ? $baris->StatusKiriman : "");
	 			$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->StatusDO) ? $baris->StatusDO : "");
	 			$row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->NamaPengirim) ? $baris->NamaPengirim : "");
	 			$row[] = (!empty($baris->TglKirim) ? $baris->TglKirim : "");
	 			$row[] = (!empty($baris->TglTerima) ? $baris->TglTerima : "");	 			
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
	public function datacekkiriman()
	{	
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			$DoTerima = $this->Model_closing->getDOTerima();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/datacekkiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listcekDataKiriman($cek = null)
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
				$total = $this->Model_kiriman->listcekDataKiriman()->num_rows();
			else
				$total = $this->Model_kiriman->listcekDataKiriman('','')->num_rows();

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
			$bySearch = " and (KodePelanggan like '%".$search."%' or NoKiriman like '%".$search."%' or StatusKiriman like '%".$search."%' or NoDO like '%".$search."%' or KodePelanggan like '%".$search."%'or NoDO like '%".$search."%' or StatusDO like '%".$search."%' or Alasan like '%".$search."%' or TimeKirim like '%".$search."%' or TimeTerima like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_kiriman->listcekDataKiriman($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman->listcekDataKiriman($bySearch, $byLimit)->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listcekDataKiriman($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listcekDataKiriman($bySearch, '')->num_rows();
			}

			$x = 0;
			$i = 0;
			$countCetak = 1;
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
	 			$row[] = (!empty($baris->StatusKiriman) ? $baris->StatusKiriman : "");
	 			$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
	 			$row[] = (!empty($baris->StatusDO) ? $baris->StatusDO : "");
	 			$row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
	 			$row[] = (!empty($baris->Pengirim) ? $baris->Pengirim : "");
	 			$row[] = (!empty($baris->NamaPengirim) ? $baris->NamaPengirim : "");
	 			$row[] = (!empty($baris->TglKirim) ? $baris->TglKirim : "");
	 			$row[] = (!empty($baris->TglTerima) ? $baris->TglTerima : "");	
	 			if($baris->StatusKiriman == 'Closed' and $baris->StatusDO == 'Terkirim'){
	 				if($baris->header > 0 and $baris->detail > 0){
	 					$row[] = 'Ok';
	 					$row[] = 'Ok';
	 					$row[] = '-';
	 				}elseif($baris->header > 0 and $baris->detail < 1){
	 					$row[] = 'Ok';
	 					$row[] = 'Not Ok';
	 					$row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="prosesfaktur('."'".$baris->NoDO."'".','."'Ok'".','."'Tidak'".')">Proses</button>';
	 				}elseif($baris->header < 1 and $baris->detail < 1){
	 					$row[] = 'Not Ok';
	 					$row[] = 'Not Ok';
	 					$row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="prosesfaktur('."'".$baris->NoDO."'".','."'Tidak'".','."'Tidak'".')">Proses</button>';
	 				}
	 			}else{
	 				$row[] = '-';
	 				$row[] = '-';
	 				$row[] = '-';
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

	public function prosesUlangKiriman()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$fakturheader = $_POST['fakturheader'];
			$fakturdetail = $_POST['fakturdetail'];
			$x=[];
			$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='Kiriman' limit 1")->row();
			$cek = $cek->counter;
			if($cek == '0'){
				$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='Kiriman'");
				$valid = $this->Model_kiriman->prosesTerimaUlangDO($No,$fakturheader,$fakturdetail);
				$this->db->query("update mst_trans set counter = '0', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='Kiriman'");
				$x = ["status"=>$valid, 'pesan'=>'sukses'];
			}else{
				$valid=false;
				$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
				
			}
			echo json_encode($x);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function validasiDOFaktur()
	{	
		if ( $this->logged ) 
		{
			// $DoOpen = $this->Model_closing->getDOopen();
			// $DoKirim = $this->Model_closing->getDOKirim();
			// $DoTerima = $this->Model_closing->getDOTerima();
			// $getDOHeaderDetail = $this->Model_kiriman->getDOHeaderDetail();
			// $getFakturHeaderDetail = $this->Model_kiriman->getFakturHeaderDetail();
			// $getCNDN = $this->Model_kiriman->getCNDN();
			// $getDOFakturHeader = $this->Model_kiriman->getDOFakturHeader();
			// $getDOFakturDetail = $this->Model_kiriman->getDOFakturDetail();
			// $DoOpen  = $DoOpen[0]->NoDO;
			// $DoKirim = $DoKirim[0]->NoDO;
			// $DoTerima = $DoTerima[0]->NoDO;
			// $this->content['DoOpen'] = $DoOpen;
			// $this->content['DoKirim'] = $DoKirim;
			// $this->content['DoTerima'] = $DoTerima;
			// $this->content['getDOHeaderDetail'] = $getDOHeaderDetail;
			// $this->content['getFakturHeaderDetail'] = $getFakturHeaderDetail;
			// $this->content['getCNDN'] = $getCNDN;
			// $this->content['getDOFakturHeader'] = $getDOFakturHeader;
			// $this->content['getDOFakturDetail'] = $getDOFakturDetail;
			$this->twig->display('pembelian/validasiDOFaktur.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listvalidasiDOFaktur($cek = null)
	{	
		if ($this->logged) 
		{
			$data = "";
			$params = $this->input->post();
            $byperiode = "";
            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            if(isset($params['tgl2'])){
                $tgl2 = $this->input->post('tgl2');
            }
            if($tgl1 == "" or $tgl2 == ""){
            	$tgl1 = date('Y-m-01');
            	$tgl2 = date('Y-m-d');
            }
            $tipe = $this->input->post('tipe');
			$query=$this->Model_kiriman->listvalidasiDOFaktur($tgl1,$tgl2,$tipe)->result();
			$x = 0;
			$i = 0;
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if($tipe =='do'){
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->TotalA) ? $baris->TotalA : "");
		 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
		 			// $row[] = '-';
		 			// $row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoDO."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>Fix Data</a>';
				}
				elseif($tipe =='faktur'){
					$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
		 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->TotalA) ? $baris->TotalA : "");
		 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
		 			// $row[] = '-';
		 			// $row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoFaktur."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			if($baris->Total != "" or $baris->Total != 0){
		 				$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoFaktur."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>Fix Data</a>';
		 			}else{
		 				$row[] = "-";
		 			}
		 			
				}
				elseif($tipe =='cndn'){
					$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
		 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
		 			$row[] = (!empty($baris->Total_detail) ? $baris->Total_detail : "");
		 			// $row[] = '-';
		 			// $row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoFaktur."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoFaktur."'".','."'".$tipe."'".'><i class="fa fa-eye"></i>Fix Data</a>';
				}
				elseif($tipe =='header' or $tipe =='detail'){
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
		 			$row[] = (!empty($baris->TotalB) ? $baris->TotalB : "");
		 			// $row[] = '-';
		 			// $row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoDO."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>Fix Data</a>';
				}elseif($tipe =='dokirim'){
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
		 			$row[] = (!empty($baris->TotalB) ? $baris->TotalB : "");
		 			// $row[] = '-';
		 			// $row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoDO."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".','."'".$baris->Status."'".')"><i class="fa fa-eye"></i>Fix Data</a>';
				}elseif($tipe =='saldo'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
		 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty(number_format($baris->valuefaktur)) ? number_format($baris->valuefaktur) : 0);
		 			$row[] = (!empty(number_format($baris->Total)) ? number_format($baris->Total) : 0);
		 			$row[] = (!empty(number_format($baris->Saldo)) ? number_format($baris->Saldo) : 0);
		 			$row[] = (!empty(number_format($baris->saldo_giro)) ? number_format($baris->saldo_giro) : 0);
		 			$row[] = (!empty(number_format($baris->ValuePelunasanx)) ? number_format($baris->ValuePelunasanx) : 0);
		 			$row[] = (!empty(number_format($baris->saldoHt)) ? ($baris->saldoHt) : 0);
		 			$row[] = (!empty(number_format($baris->selisih)) ? number_format($baris->selisih) : 0);
		 			$saldo = $baris->Saldo + $baris->saldo_giro;
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoFaktur."'".','."'".$tipe."'".','."'".$saldo."'".','."'".$baris->ValuePelunasanx."'".','."'".$baris->TipeDokumen."'".','."'".$baris->selisih."'".','."'".$baris->Total."'".','."'Lunas'".')"><i class="fa fa-eye"></i>Fix Data</a>';
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

	public function prosesfixfaktur()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$tipe = $_POST['tipe'];
			$status = $_POST['status'];
			if($tipe=='do'){
				$valid = $this->Model_kiriman->prosesfixdo($No);
			}
			elseif($tipe=='faktur'){
				$valid = $this->Model_kiriman->prosesfixfaktur($No);
			}
			elseif($tipe=='header'){
				$valid = $this->Model_kiriman->prosesfixfakturheder($No);
			}
			elseif($tipe=='detail'){
				$valid = $this->Model_kiriman->prosesfixfakturdetail($No);
			}
			elseif($tipe=='saldo'){
				$valid = $this->Model_kiriman->prosesfixsaldo($No);
			}
			elseif($tipe=='cndn'){
				$valid = $this->Model_kiriman->prosesfixcndn($No);
			}elseif($tipe=='dokirim'){
				$valid = $this->Model_kiriman->prosesfixdokirim($No,$status);
			}
			
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function RegisterFaktur()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/RegisterFaktur.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listRegisterFaktur()
	{	
		if ($this->logged) 
		{
			$data = "";
			$params = $this->input->post();
            $byperiode = "";
            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            if(isset($params['tgl2'])){
                $tgl2 = $this->input->post('tgl2');
            }
            if($tgl1 == "" or $tgl2 == ""){
            	$tgl1 = date('Y-m-01');
            	$tgl2 = date('Y-m-d');
            }
			$data = "";
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
			
			/*Mempersiapkan array tempat kita akan menampung semua data
			yang nantinya akan server kirimkan ke client*/
			$output=array();

			/*Token yang dikrimkan client, akan dikirim balik ke client*/
			$output['draw']=$draw;
			/*Menghitung total data didalam database*/
			$total = $this->Model_kiriman->listRegisterFaktur("","",$tgl1,$tgl2)->num_rows();

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
			$bySearch = " and (NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or TimeDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or  Status like '%".$search."%' or status_validasi like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			$query=$this->Model_kiriman->listRegisterFaktur($bySearch, $byLimit,$tgl1,$tgl2)->result();

			if($search!=""){			
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listRegisterFaktur($bySearch,"",$tgl1,$tgl2)->num_rows();
			}
			$x = 0;
			$i = 0;
			$now = date('Y-m-d');
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$dateDO = date("Y-m-d",strtotime($baris->TglDO));
				$diff = floor((strtotime(date("Y-m-d"))-strtotime($baris->TglDO))/(60 * 60 * 24));
				$row[] = '<a class="btn btn-sm btn-success" title="Register Faktur Pelanggan" onclick="fix('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i>Faktur Pelanggan Lengkap</a>';
				if($diff > 7){
					$row[] = "<p style='color:red'>".(!empty($baris->NoDO) ? $baris->NoDO : "").'</p>';
					$row[] = "<p style='color:red'>".(!empty($baris->TglDO) ? $baris->TglDO : "").'</p>';
					$row[] = "<p style='color:red'>".(!empty($baris->TimeDO) ? $baris->TimeDO : "").'</p>';
		 			// $row[] = "<p style='color:red'>".(!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "").'</p>';
		 			$row[] = "<p style='color:red'>".(!empty($baris->NoSO) ? $baris->NoSO : "").'</p>';
		 			$row[] = "<p style='color:red'>".(!empty($baris->Pelanggan) ? $baris->Pelanggan : "").'</p>';
		 			$row[] = "<p style='color:red'>".(!empty($baris->Status) ? $baris->Status : "").'</p>';
		 			$row[] = "<p style='color:red'>".(!empty($baris->status_validasi) ? $baris->status_validasi : "").'</p>';
				}else{
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
					$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
					$row[] = (!empty($baris->TimeDO) ? $baris->TimeDO : "");
		 			// $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
		 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
		 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 			$row[] = (!empty($baris->status_validasi) ? $baris->status_validasi : "");
				}
		 		$row[] = '<a class="btn btn-sm btn-success" title="View Detail" onclick="view('."'".$baris->NoDO."'".')"><i class="fa fa-eye"></i>View Detail</a>';
				// <p style='color:red'>
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

	public function ProsesRegisterFaktur()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_kiriman->ProsesRegisterFaktur($No);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function DataRegisterFaktur()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/DataRegisterFaktur.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function listDataRegisterFaktur()
	{	
		if ($this->logged) 
		{
			$data = "";
			$params = $this->input->post();
            $byperiode = "";
            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            if(isset($params['tgl2'])){
                $tgl2 = $this->input->post('tgl2');
            }
            if($tgl1 == "" or $tgl2 == ""){
            	$tgl1 = date('Y-m-01');
            	$tgl2 = date('Y-m-d');
            }
			$data = "";
			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];
			$length=$_REQUEST['length'];
			$start=$_REQUEST['start'];
			$search=$_REQUEST['search']["value"];
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
			
			/*Mempersiapkan array tempat kita akan menampung semua data
			yang nantinya akan server kirimkan ke client*/
			$output=array();

			/*Token yang dikrimkan client, akan dikirim balik ke client*/
			$output['draw']=$draw;
			/*Menghitung total data didalam database*/
			$total = $this->Model_kiriman->listDataRegisterFaktur("","",$tgl1,$tgl2)->num_rows();

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
			$bySearch = " and (NoDO like '%".$search."%' or TglDO like '%".$search."%' or NoSO like '%".$search."%' or TimeDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or  Status like '%".$search."%' or status_validasi like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			$query=$this->Model_kiriman->listDataRegisterFaktur($bySearch, $byLimit,$tgl1,$tgl2)->result();

			if($search!=""){			
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman->listDataRegisterFaktur($bySearch,"",$tgl1,$tgl2)->num_rows();
			}
			$x = 0;
			$i = 0;
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
				$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
				$row[] = (!empty($baris->TimeDO) ? $baris->TimeDO : "");
		 		$row[] = (!empty($baris->NoSO) ? $baris->NoSO : "");
		 		$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
		 		$row[] = (!empty($baris->Status) ? $baris->Status : "");
		 		if($baris->status_validasi == 'Y'){
		 			$row[] = "Faktur Pelanggan Lengkap";
		 		}else{
		 			$row[] = "Faktur Pelanggan Belum Teregister";
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
	public function PresentaseRegisterFaktur()
	{          
        if ($this->logged) 
		{	
			$tgl1 = $_POST['tgl1'];
			$tgl2 = $_POST['tgl2'];
			$valid = $this->Model_kiriman->PresentaseRegisterFaktur($tgl1,$tgl2);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}