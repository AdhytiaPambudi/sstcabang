<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventori extends CI_Controller {

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
		$this->load->model('pembelian/Model_inventori');
		$this->load->library('format');
		$this->logs = $this->session->all_userdata();
		$this->user = $this->session->userdata('username');
        $this->cabang = $this->session->userdata('cabang');
        $this->group = $this->session->userdata('userGroup');
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function kartustok()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datakartustok.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function kartustokdaily()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datakartustokdaily.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function mutasistok()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datamutasistok.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function sobh()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datasobh.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function uploadsobh()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			// $this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/Uploadsobh.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function sobb()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datasobb.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function uploadsobb()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			// $this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/Uploadsobb.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datainvsum()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datainvsum.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataInvSum($cek = null)
	{	
		if ($this->logged) 
		{
			// ----------------------------------------limit----------------------------------
	        $byLimit = "";
	        $length = "";
				if (isset($_REQUEST['length'])) {
					if($_REQUEST['length'] != -1){
						$length=$_REQUEST['length'];
					}
				}

			$start = "";
				if (isset($_REQUEST['start'])) {
					$start=$_REQUEST['start'];
				}

			if($length != 0 || $length != "")
					$byLimit = " LIMIT ".$start.", ".$length;
		// -------------------------------------------limit-------------------------------

		$bySearch = "";
			if($cek=='sum')
			{
				$list = $this->Model_inventori->listDataInvSum($bySearch,$byLimit);
			}else
			{
				$list = $this->Model_inventori->listDataInvSum2($bySearch,$byLimit);			
			}
	        $data = array(); 
			
			$x = 0;
			$i = 0;
			foreach ($list as $baris) {
				$x++;
				if($baris->UnitStok == ""){
					$unit_stok = 0;
				}
				else{
					$unit_stok = $baris->UnitStok; 
				}
				if($baris->Rata2jual == ""){
					$rata2jual = 0;
				}
				else{
					$rata2jual = $baris->Rata2jual; 
				}
				if($rata2jual == 0){
					$umurStok = 0;
				}else{
					$umurStok = number_format(($unit_stok / $rata2jual),2);
				}
				if($cek=='sum')
				{
					$gudang = "All";
				}else{
					$gudang = $baris->Gudang;
				}
				
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->Tahun) ? $baris->Tahun : "");
	 			$row[] = (!empty($baris->KodePrinsipal) ? $baris->KodePrinsipal : "");
	 			$row[] = (!empty($baris->NamaPrinsipal) ? $baris->NamaPrinsipal : "");
	 			$row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
	 			$row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
	 			$row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->UnitStok)) ? number_format($baris->UnitStok) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->ValueStok)) ? number_format($baris->ValueStok) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->qtyfakturtigabulan)) ? number_format($baris->qtyfakturtigabulan) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->qtyfakturduabulan)) ? number_format($baris->qtyfakturduabulan) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->qtyfaktursatubulan)) ? number_format($baris->qtyfaktursatubulan) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->Rata2jual)) ? number_format($baris->Rata2jual) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->QtyGIT)) ? number_format($baris->QtyGIT) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->rasio)) ? number_format($baris->rasio) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->SL)) ? number_format($baris->SL) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->umur_stok)) ? number_format($baris->umur_stok) : "0")."</p>";
	 			$row[] = (!empty($gudang) ? $gudang : "");
	 			
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->Indeks)) ? number_format($baris->Indeks) : "0")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->UnitCOGS)) ? number_format($baris->UnitCOGS) : 0)."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->HNA)) ? number_format($baris->HNA)."</p>" : 0);
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->Pesan)) ? number_format($baris->Pesan)."</p>" : 0);
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
			redirect("auth/logout");
		}
	}

	public function datainvdet()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datainvdet.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataInvDet($cek = null)
	{	
		if ($this->logged) 
		{
			// ----------------------------------------limit----------------------------------
	        $byLimit = "";
	        $length = "";
				if (isset($_REQUEST['length'])) {
					if($_REQUEST['length'] != -1){
						$length=$_REQUEST['length'];
					}
				}

			$start = "";
				if (isset($_REQUEST['start'])) {
					$start=$_REQUEST['start'];
				}

			if($length != 0 || $length != "")
					$byLimit = " LIMIT ".$start.", ".$length;
		// -------------------------------------------limit-------------------------------

		$bySearch = "";
	        $data = array(); 
			$list = $this->Model_inventori->listDataInvDet($bySearch,$byLimit);
			$x = 0;
			$i = 0;

			foreach ($list as $baris) {
				$x++;
				if($baris->UnitStok == ""){
					$unit_stok = 0;
				}
				else{
					$unit_stok = $baris->UnitStok; 
				}
				// if($baris->Rata2jual == ""){
				// 	$rata2jual = 0;
				// }
				// else{
				// 	$rata2jual = $baris->Rata2jual; 
				// }
				// if($unit_stok >= 0 and $rata2jual == 0){
				// 	$umurStok = 0;
				// }else{
				// 	$umurStok = number_format(($unit_stok / $rata2jual),2);
				// }

				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->Tahun) ? $baris->Tahun : "");
	 			$row[] = (!empty($baris->KodePrinsipal) ? $baris->KodePrinsipal : "");
	 			$row[] = (!empty($baris->NamaPrinsipal) ? $baris->NamaPrinsipal : "");
	 			$row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
	 			$row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
	 			$row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->UnitStok)) ? number_format($baris->UnitStok) : "")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->ValueStok)) ? number_format($baris->ValueStok) : "")."</p>";
	 			$row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
	 			$row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
	 			// $row[] = (!empty($baris->TanggalDokumen) ? $baris->TanggalDokumen : "");
	 			// $row[] = (!empty($baris->Rata2jual) ? $baris->Rata2jual : "0");
	 			// $row[] = (!empty($umurStok) ? $umurStok : "0");
	 			$row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
	 			$row[] = (!empty($baris->Gudang) ? $baris->Gudang : "");
	 			$row[] = "<p align='right'>".(!empty(number_format($baris->UnitCOGS)) ? number_format($baris->UnitCOGS) : 0)."</p>";
	 			
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
			redirect("auth/logout");
		}
	}

	public function datainvhis()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datainvhis.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataInvHis($cek = null)
	{	
		if ($this->logged) 
		{
	        $data = array(); 
			$list = $this->Model_inventori->listDataInvHis();
			$x = 0;
			$i = 0;

			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->Tahun) ? $baris->Tahun : "");
	 			$row[] = (!empty($baris->KodePrinsipal) ? $baris->KodePrinsipal : "");
	 			$row[] = (!empty($baris->NamaPrinsipal) ? $baris->NamaPrinsipal : "");
	 			$row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
	 			$row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
	 			$row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
	 			$row[] = (!empty($baris->UnitStok) ? $baris->UnitStok : "");
	 			$row[] = (!empty($baris->ValueStok) ? $baris->ValueStok : "");
	 			$row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
	 			$row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
	 			$row[] = (!empty($baris->Gudang) ? $baris->Gudang : "");
	 			$row[] = (!empty($baris->Tipe) ? $baris->Tipe : 0);
	 			$row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
	 			$row[] = (!empty($baris->Keterangan) ? $baris->Keterangan : "");
	 			
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
			redirect("auth/logout");
		}
	}

	public function settlement()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/settlement.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataSettlement($cek = null)
	{	
		if ($this->logged) 
		{
	        $data = array(); 
			$list = $this->Model_inventori->listDataSettlement();
			$x = 0;
			$i = 0;

			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->tanggal) ? $baris->tanggal : "");
				$row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
	 			$row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
	 			$row[] = (!empty($baris->SAwal) ? $baris->SAwal : "");
	 			$row[] = (!empty($baris->BPB) ? $baris->BPB : "");
	 			$row[] = (!empty($baris->relokasi_terima) ? $baris->relokasi_terima : "");
	 			$row[] = (!empty($baris->adjustment_plus) ? $baris->adjustment_plus : "");
	 			$row[] = (!empty($baris->Retur) ? $baris->Retur : "");
	 			$row[] = (!empty($baris->DOFaktur) ? $baris->DOFaktur : "");
	 			$row[] = (!empty($baris->relokasi_kirim) ? $baris->relokasi_kirim : "");
	 			$row[] = (!empty($baris->adjustment_min) ? $baris->adjustment_min : "");
	 			$row[] = (!empty($baris->SAkhir) ? $baris->SAkhir : "");
	 			$row[] = (!empty($baris->UnitStok) ? $baris->UnitStok : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = (!empty($baris->Cabang) ? $baris->Cabang : ""); 			
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
			redirect("auth/logout");
		}
	}

	// public function setSettlement()
	// {          
 //        if ($this->logged) 
	// 	{	
	// 		$params = (object)$this->input->post();
	// 		$params = $params->grid;
	// 		$params = json_decode($params,true);
	// 		$i=0;
	// 		foreach ($params as $list) {
	// 			$status = $list["status"];
	// 			if($status == "Selisih"){
	// 				$i++;
	// 				break;
	// 			}
	// 		}
	// 		if($i == 0){
	// 			$valid = $this->Model_inventori->setSettlement($params);
	// 			echo json_encode(array("status" => $valid));
	// 		}else{
	// 			echo json_encode(array("status" => false));
	// 		}
			
	// 	}
	// 	else 
	// 	{	
	// 		redirect("auth/logout");
	// 	}
 //    }

	public function setSettlement()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$params = $params->grid;
			$params = json_decode($params,true);
			$i=0;
			foreach ($params as $list) {
				$status = $list["status"];
				if($status == "selisih"){
					$i++;
					break;
				}
			}
			if($i == 0){
				$valid = $this->Model_inventori->setSettlement($params,'daily');
				echo json_encode(array("status" => $valid));
			}else{
				echo json_encode(array("status" => false));
			}
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function setSettlement_bulan()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$params = $params->grid;
			$params = json_decode($params,true);
			$i=0;
			foreach ($params as $list) {
				$status = $list["status"];
				if($status == "selisih"){
					$i++;
					break;
				}
			}
			if($i == 0){
				$valid = $this->Model_inventori->setSettlement($params,'bulanan');
				echo json_encode(array("status" => $valid));
			}else{
				echo json_encode(array("status" => false));
			}
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function settlementKas()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/settlementkas.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataSettlementKas($cek = null)
	{	
		if ($this->logged) 
		{
	        $data = array(); 
			$list = $this->Model_inventori->listDataSettlementKas();
			$x = 0;
			$i = 0;

			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
				$row[] = (!empty($baris->tanggal) ? $baris->tanggal : "");
				$row[] = (!empty($baris->TipeTransaksi) ? $baris->TipeTransaksi : "");
	 			$row[] = (!empty($baris->Transaksi) ? $baris->Transaksi : "");
	 			$row[] = (!empty($baris->NoDIH_kasbank) ? $baris->NoDIH_kasbank : "");
	 			$row[] = (!empty($baris->NoDIH_pelunasan) ? $baris->NoDIH_pelunasan : "");
	 			$row[] = (!empty($baris->saldo_KasBank) ? $baris->saldo_KasBank : "");
	 			$row[] = (!empty($baris->saldo_pelunasan) ? $baris->saldo_pelunasan : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			
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
			redirect("auth/logout");
		}
	}

	public function setSettlementKas()
	{          
        if ($this->logged) 
		{	
			$valid = $this->Model_inventori->setSettlementKas();

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function getkartustok()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
			$produk = $_POST['produk'];
			$expld = explode("~", $produk);
	        $Kode = $expld[0];
	        $Nama = $expld[1];
	        $timestamp    = strtotime($tglDoc);
			$first_date = date('Y-m-01 00:00:00', $timestamp);
			$end_date 	= date('Y-m-d 23:59:59', $timestamp);
			$getmonth 	= date('m', $timestamp);
			$getyear 	= date('Y', $timestamp);
			$sawal = $this->Model_inventori->saldoawal($Kode,$getmonth,$getyear);
			$list = $this->Model_inventori->getkartustok($first_date,$end_date,$Kode);
			$return['Result'] = $list;
    		$return['s_awal'] = $sawal;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getkartustokdaily()
    {
    	if ($this->logged) 
		{
			// log_message("error",print_r($_POST['tgldoc'],true));

			$tglDoc = $_POST['tgldoc'];
			$produk = $_POST['produk'];
			$expld = explode("~", $produk);
	        $Kode = $expld[0];
	        $Nama = $expld[1];
	        $timestamp  = strtotime($tglDoc);
	        $dateawal  = date('Y-m-01', $timestamp);
	        $curr_date  = date("Y-m-d");
			$first_date = date('Y-m-01 00:00:00', $timestamp);
			$before_day = date('Y-m-d 23:59:59',(strtotime ( '-1 day' , $timestamp)));
			$end_date 	= date('Y-m-d 23:59:59', $timestamp);
			$getmonth 	= date('m', $timestamp);
			if($dateawal != $curr_date){
				$sawal = $this->Model_inventori->saldoawaldaily($Kode,$getmonth,$first_date,$before_day);
			}else{
				$sawal = $this->Model_inventori->saldoawal($Kode,$getmonth);
			}
			$list = $this->Model_inventori->getkartustokdaily(date("Y-m-d",$timestamp),$Kode);
			$return['Result'] = $list;
    		$return['s_awal'] = $sawal;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

  //    public function getstokdetail()
  //   {
  //   	if ($this->logged) 
		// {
		// 	$produk = $_POST['produk'];
		// 	$expld = explode("~", $produk);
	 //        $Kode = $expld[0];
	 //        $Nama = $expld[1];
		// 	$list = $this->Model_inventori->getstokdetail($Kode);
		// 	$return['Result'] = $list;
  //   		echo json_encode($return);
		
		// }
		// else{
		// 	redirect("auth/logout");
		// }	
  //   } 
     public function getstokdetail()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['produk'];
			$expld = explode("~", $produk);
	        $Kode = $expld[0];
	        $Nama = $expld[1];
			$list = $this->Model_inventori->getstokdetail($Kode);
			$data = array();
			log_message("error",print_r($this->userGroup,true));
			foreach ($list as $key) {
				$row = array();
	 			$row[] = (!empty($key->Kode_Produk) ? $key->Kode_Produk : "");
	 			$row[] = (!empty($key->Produk) ? $key->Produk : "");
	 			$row[] = (!empty($key->Satuan ) ? $key->Satuan  : "");			
	 			$row[] = (!empty($key->gudang_summary) ? $key->gudang_summary : "");
	 			$row[] = "<p align='right'>".(!empty($key->Stok_summary) ? number_format($key->Stok_summary) : 0)."</p>";
	 			if($this->userGroup == 'Cabang'){
	 				$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="summary('."'".$key->Kode_Produk."'".','."'".$key->gudang_summary."'".')" id="summary"><i class="fa fa-eye"></i>Cek Stok Summary</a>';
	 			}else{
	 				$row[] = '-';
	 			}
	 			
	 			$row[] = (!empty($key->gudang_detail) ? $key->gudang_detail : "");
	 			$row[] = "<p align='right'>".(!empty($key->Stok_detail) ? number_format($key->Stok_detail) : 0)."</p>";
	 			if($this->userGroup == 'Cabang'){
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="detail('."'".$key->Kode_Produk."'".','."'".$key->gudang_detail."'".')" id="detail"><i class="fa fa-eye"></i>Cek Stok Detail</a>';
		 		}else{
	 				$row[] = '-';
	 			}
				$data[] = $row;
			}

			$output = array(
                        "data" => $data,
				);
		echo json_encode($output);
			
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getsobh()
    {
    	if ($this->logged) 
		{
			$list = $this->Model_inventori->getsobh();
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function importsobh()
    {
    	if ($this->logged) 
		{
			$params = (object)$this->input->post();
			$dir = 'excel';
			if (!file_exists(getcwd().'/assets/dokumen/'.$dir)) {
			    mkdir(getcwd().'/assets/dokumen/'.$dir, 0777, true);
			}
			$path = getcwd().'/assets/dokumen/'.$dir.'/';
			$name1 = "";
			$name2 = "";

			if(!empty($_FILES['Dokumen']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }
			$list = $this->Model_inventori->uploadsobh($name1);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getsobb()
    {
    	if ($this->logged) 
		{
			$list = $this->Model_inventori->getsobb();
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function importsobb()
    {
    	if ($this->logged) 
		{
			$params = (object)$this->input->post();
			$dir = 'excel';
			if (!file_exists(getcwd().'/assets/dokumen/'.$dir)) {
			    mkdir(getcwd().'/assets/dokumen/'.$dir, 0777, true);
			}
			$path = getcwd().'/assets/dokumen/'.$dir.'/';
			$name1 = "";
			$name2 = "";

			if(!empty($_FILES['Dokumen']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }
			$list = $this->Model_inventori->uploadsobb($name1);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getmutasistok()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
	        $timestamp    = strtotime($tglDoc);
			$first_date = date('Y-m-01 00:00:00', $timestamp);
			$end_date 	= date('Y-m-d 23:59:59', $timestamp);
			$getmonth 	= date('m', $timestamp);
			$nextmonth  = date('m',strtotime('+1 month', $timestamp ));
			$list = $this->Model_inventori->getmutasistok($first_date,$end_date,$getmonth,$nextmonth);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function datasobhperiode()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			// $this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datasobhperiode.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

    public function getdatasobh()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
			$cabang = $_POST['cabang'];
	        $timestamp    = strtotime($tglDoc);
			$transdate 	= date('Y-m-d', $timestamp);
			$list = $this->Model_inventori->getdatasobh($transdate,$cabang);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function datasobbperiode()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			// $this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datasobbperiode.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

    public function getdatasobb()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
			$cabang = $_POST['cabang'];
	        $timestamp    = strtotime($tglDoc);
	        $year 	= date('Y', $timestamp);
			$month 	= date('m', $timestamp);
			$list = $this->Model_inventori->getdatasobb($year,$month,$cabang);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

     public function getstokdetailbatch()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['produk'];
			$expld = explode("~", $produk);
	        $Kode = $expld[0];
	        $Nama = $expld[1];
	        $BatchNo = $_POST['batch'];
	        $BatchDoc = $_POST['batchdoc'];
			$list = $this->Model_inventori->getstokdetailbatch($Kode,$BatchNo);
			$return['Result'] = $list;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function kartustokbatch()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datakartustokbatch.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getkartustokbatch()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
			$produk = $_POST['produk'];
			$expld = explode("~", $produk);
	        $Kode = $expld[0];
	        $Nama = $expld[1];
	        $BatchNo = $_POST['batch'];
	        $BatchDoc = $_POST['batchdoc'];
	        $timestamp    = strtotime($tglDoc);
			$first_date = date('Y-m-01 00:00:00', $timestamp);
			$end_date 	= date('Y-m-d 23:59:59', $timestamp);
			$getmonth 	= date('m', $timestamp);
			$sawal = $this->Model_inventori->saldoawalbatch($Kode,$getmonth,$BatchNo);
			$list = $this->Model_inventori->getkartustokbatch($first_date,$end_date,$Kode,$BatchNo);
			$return['Result'] = $list;
    		$return['s_awal'] = $sawal;
    		echo json_encode($return);
		
		}
		else{
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
			log_message("error",print_r($query,true));
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

	public function liststoksummary()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['kode'];
	        $gudang = $_POST['gudang'];
			$list = $this->Model_inventori->liststoksummary($produk,$gudang);
    		echo json_encode($list);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function updatestoksummary()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['KodeProduk'];
	        $gudang = $_POST['Gudang'];
	        $UnitStok = $_POST['UnitStok'];
			$valid = $this->Model_inventori->updatestoksummary($produk,$gudang,$UnitStok);
    		echo json_encode($valid);
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function liststokdetail()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['kode'];
	        $gudang = $_POST['gudang'];
			$list = $this->Model_inventori->liststokdetail($produk,$gudang);
    		echo json_encode($list);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function updatestokdetail()
    {
    	if ($this->logged) 
		{
			$produk = $_POST['KodeProduk'];
	        $gudang = $_POST['Gudang'];
	        $UnitStok = $_POST['UnitStok'];
	        $BatchNo = $_POST['BatchNo'];
	        $BatchDoc = $_POST['BatchDoc'];
			$valid = $this->Model_inventori->updatestokdetail($produk,$gudang,$UnitStok,$BatchNo,$BatchDoc);
    		echo json_encode($valid);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getclosingstokdaily()
    {
    	if ($this->logged) 
		{
			$first_date = date('Y-m-01');
			$end_date 	= date('Y-m-d');
			$getmonth 	= date('m');
			$list = $this->Model_inventori->getclosingstokdaily($first_date,$end_date,$getmonth);	
	        // $this->Model_inventori->federate_this_week();
	        // // $this->Model_inventori->federate_this_month();
	        // // $this->Model_inventori->federate_two_month();
	        $this->Model_inventori->send_message_tele("SEND PT, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTelePT();
	        $this->Model_inventori->send_message_tele("SEND PU, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTelePU();
	        $this->Model_inventori->send_message_tele("SEND STOK, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTeleStok();
	        $this->Model_inventori->send_message_tele("SEND KAS/BANK, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTeleKasBank();
			
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function generatestok()
    {
    	if ($this->logged) 
		{
			$params = (object)$this->input->post();
			// log_message("error",print_r($params,true));
			$valid = $this->Model_inventori->generatestok($params);	
			echo json_encode($valid);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getclosingstokbulanan()
    {
    	if ($this->logged) 
		{
			$date = date('Y-m-01',strtotime("-10 days"));
            $first_date = date('Y-m-01', strtotime($date));
            $end_date = date('Y-m-t', strtotime($date));
			$getmonth 	= date('m',strtotime($date));
			$getyear = date('Y',strtotime($date));
			$list = $this->Model_inventori->getclosingstokbulanan($first_date,$end_date,$getmonth,$getyear);	
	        // $this->Model_inventori->federate_this_week();
	        // $this->Model_inventori->federate_this_month();
	        // $this->Model_inventori->federate_two_month();
	        $this->Model_inventori->send_message_tele("SEND PT, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTelePT();
	        $this->Model_inventori->send_message_tele("SEND PU, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTelePU();
	        $this->Model_inventori->send_message_tele("SEND STOK, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTeleStok();
	        $this->Model_inventori->send_message_tele("SEND KAS/BANK, CABANG ".$this->cabang." ");
	        $this->Model_inventori->getTeleKasBank();
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function generatestokbulanan()
    {
    	if ($this->logged) 
		{
			$params = (object)$this->input->post();
			$date = date('Y-m-01',strtotime("-10 days"));
            $first_date = date('Y-m-01', strtotime($date));
            $end_date = date('Y-m-t', strtotime($date));
			$getmonth 	= date('m',strtotime($date));
			$getyear = date('Y',strtotime($date));
			$valid = $this->Model_inventori->generatestokbulanan($params,$first_date,$end_date,$getmonth,$getyear);	
			echo json_encode($valid);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getrevisistok()
    {
    	if ($this->logged) 
		{
			$params = (object)$this->input->post();
			$params = $this->input->post();
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";
            $bydateD     = "";

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tipe   = $params['tipe'];
            $tgl1   = $params['tgl1'];

            if($length == -1){
                $bylimit    = "";
            }else{
                $bylimit    = " LIMIT ".$start.", ".$length;
            }

            if($tgl1){
                $date = $tgl1;
            }else{
            	$date = date('Y-m-d');
            }
            // $date = date('Y-m-01',strtotime("-10 days"));
            $first_date = date('Y-m-01', strtotime($date));
            $end_date = date('Y-m-d', strtotime($date));
			$getmonth 	= date('m',strtotime($date));
			$getyear = date('Y',strtotime($date));
			$enddate = date('Y-m-t', strtotime($date));
			$datesaldo = date('Y-m-d',strtotime($enddate,'+2 days'));
			$yearsaldo = date('Y',strtotime($datesaldo));
			$monthsaldo = date('m',strtotime($datesaldo));
			$valid = $this->Model_inventori->getrevisistok($params,$first_date,$end_date,$getmonth,$getyear);	
			echo json_encode($valid);
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function getclosingdoheader()
    {
    	if ($this->logged) 
		{
			$status = $_POST['status'];
			// log_message("error",print_r($status,true));
			if($status == "daily"){
				$first_date = date('Y-m-01');
				$end_date 	= date('Y-m-d');
				$getmonth 	= date('m');
			}else{
				$tgl  = date('Y-m-d',strtotime("-7 days"));
				$first_date  = date('Y-m-01',strtotime($tgl));
				$end_date  = date('Y-m-t',strtotime($tgl));
				$getmonth 	= date('m');
			}	
			$list = $this->Model_inventori->getselisihdoheader($first_date,$end_date,$getmonth);			
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function getclosingdodetail()
    {
    	if ($this->logged) 
		{
			$status = $_POST['status'];
			// log_message("error",print_r($status,true));
			if($status == "daily"){
				$first_date = date('Y-m-01');
				$end_date 	= date('Y-m-d');
				$getmonth 	= date('m');
			}else{
				$tgl  = date('Y-m-d',strtotime("-7 days"));
				$first_date  = date('Y-m-01',strtotime($tgl));
				$end_date  = date('Y-m-t',strtotime($tgl));
				$getmonth 	= date('m');
			}
			$list = $this->Model_inventori->getselisihdodetail($first_date,$end_date,$getmonth);
			
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function getclosingdokirim()
    {
    	if ($this->logged) 
		{
			$status = $_POST['status'];
			// log_message("error",print_r($status,true));
			if($status == "daily"){
				$first_date = date('Y-m-01');
				$end_date 	= date('Y-m-d');
				$getmonth 	= date('m');
			}else{
				$tgl  = date('Y-m-d',strtotime("-7 days"));
				$first_date  = date('Y-m-01',strtotime($tgl));
				$end_date  = date('Y-m-t',strtotime($tgl));
				$getmonth 	= date('m');
			}
			$list = $this->Model_inventori->getselisihdokirim($first_date,$end_date,$getmonth);
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function getclosingcndn()
    {
    	if ($this->logged) 
		{
			$status = $_POST['status'];
			// log_message("error",print_r($status,true));
			if($status == "daily"){
				$first_date = date('Y-m-01');
				$end_date 	= date('Y-m-d');
				$getmonth 	= date('m');
			}else{
				$tgl  = date('Y-m-d',strtotime("-7 days"));
				$first_date  = date('Y-m-01',strtotime($tgl));
				$end_date  = date('Y-m-t',strtotime($tgl));
				$getmonth 	= date('m');
			}
			$list = $this->Model_inventori->getselisihcndn($first_date,$end_date,$getmonth);
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }
    public function getclosingvallunas()
    {
    	if ($this->logged) 
		{
			$status = $_POST['status'];
			log_message("error",print_r($status,true));
			if($status == "daily"){
				$getmonth 	= date('m');
				$daynumber = date('d');
			    if($daynumber <= 3){
			        $tgl1  = date('Y-m-01');
			        $tgl2  = date('Y-m-d');
			    }else{
			        $dayofweek = date('w');
			      	if($dayofweek == 1){
			      		$tgl1  = date('Y-m-d',strtotime("-5 days"));
			      	}else{
			      		$tgl1  = date('Y-m-d',strtotime("-3 days"));
			      	}
			        $tgl2  = date('Y-m-d');
			    }
			}else{
				$tgl  = date('Y-m-d',strtotime("-7 days"));
				$tgl1  = date('Y-m-t',$tgl);
				$tgl2  = date('Y-m-d',strtotime("-3 days",strtotime($tgl1)));
			}
			
		$list = $this->Model_inventori->getselisihvallunas($tgl1,$tgl2,$getmonth);
			
			echo json_encode($list);
		}
		else{
			redirect("auth/logout");
		}	
    }
}