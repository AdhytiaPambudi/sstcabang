<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kiriman_khusus extends CI_Controller {

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
		$this->load->model('pembelian/Model_kiriman_khusus');
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

	function buatkirimanKhusus()
	{	
		// echo "string"; exit();
		if ( $this->logged ) 
		{
			$DoOpen = $this->Model_kiriman_khusus->getDOopenKhusus();
			$DoKirim = $this->Model_kiriman_khusus->getDOKirimKhusus();
			$DoTerima = $this->Model_kiriman_khusus->getDOTerimaKhusus();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/buatkirimanKhusus.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function terimaKirimanKhusus()
	{
		if ($this->logged) 
		{
			$DoOpen = $this->Model_kiriman_khusus->getDOopenKhusus();
			$DoKirim = $this->Model_kiriman_khusus->getDOKirimKhusus();
			$DoTerima = $this->Model_kiriman_khusus->getDOTerimaKhusus();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
			$this->twig->display('pembelian/terimakirimanKhusus.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveTerimaKirimanKhusus()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$x=[];
			$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='Kiriman' limit 1")->row();
			$cek = $cek->counter;
			if($cek == '0'){
				$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='Kiriman'");
				$valid = $this->Model_kiriman_khusus->saveTerimaKirimanKhusus($params);
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


	public function listDataDOKhusus($cek = null)
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
				$total = $this->Model_kiriman_khusus->listDataDOKhusus()->num_rows();
			else
				$total = $this->Model_kiriman_khusus->listDataDOKhusus('','','Open')->num_rows();

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
				$query=$this->Model_kiriman_khusus->listDataDOKhusus($bySearch, $byLimit)->result();
			else
				$query=$this->Model_kiriman_khusus->listDataDOKhusus($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman_khusus->listDataDOKhusus($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman_khusus->listDataDOKhusus($bySearch, '', "Open")->num_rows();
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

	public function formTerimaKirimanKhusus()
	{
		if ($this->logged) 
		{
			if (isset($_POST['no'])) 
			{
				$no = $_POST['no'];
				$data = $this->Model_kiriman_khusus->getTerimaKiriman($no);
				$this->content['kode'] = $no;
				$this->content['data'] = $data; 
				$this->twig->display('pembelian/formterimakirimanKhusus.html', $this->content);
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

	public function listTerimaKirimanKhusus()
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
			$total = $this->Model_kiriman_khusus->listKirimanKhusus()->num_rows();

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
				$query=$this->Model_kiriman_khusus->listKirimanKhusus($bySearch, $byLimit)->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){	
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_kiriman_khusus->listKirimanKhusus($bySearch)->num_rows();
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

	public function saveKirimanKhusus()
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
					$NoDO = $this->Model_kiriman_khusus->saveKiriman_khusus($list[$key], $noDokumen, $pengirim);
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

}