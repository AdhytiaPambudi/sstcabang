<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Gl extends CI_Controller {

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
		$this->load->model('pembelian/Model_gl');
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

	public function index()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('index.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function buatKas()
	{	
		if ( $this->logged) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y' )){
					$no = $this->Model_main->noDokumenTemp("KAS");
					$saldo = $this->Model_gl->getSaldoAwal("KAS");
			        $this->content['no'] = $no;
			        $this->content['saldo'] = (!empty($saldo->saldo)) ? $saldo->saldo:0;
					$this->twig->display('pembelian/buatKas.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function buatbukugiro()
	{	
		if ( $this->logged) 
		{
			$this->content['karyawan']= $this->Model_main->karyawan();
	        $this->content['pelanggan']= $this->Model_main->Pelanggan();
	        $this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/BuatBukuGiro.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function listGLTransaksi()
	{	
		if ($this->logged) 
		{
			$jenis = $this->input->post('jenis');
			$kat = $this->input->post('kat');
			$tipekas = $this->input->post('tipe');
			$data = $this->Model_gl->listGLTransaksi($kat, $jenis,$tipekas);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}

	public function listGLKatagori()
	{	
		if ($this->logged) 
		{
			$jenis = $this->input->post('jenis');
			$kat = $this->input->post('kat');
			
			$data = $this->Model_gl->listGLKatagori($kat, $jenis);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}

	public function detailGLTransaksi()
	{	
		if ($this->logged) 
		{
			$trans = $this->input->post('trans');
			// $tipe = $this->input->post('tipe');
			$jenis = $this->input->post('jenis');
			$data = $this->Model_gl->detailGLTransaksi($trans,$jenis);
			// $counter = $this->Model_gl->getJurnalID($jenis);
			// if (!empty($counter))
			// 	$no = $counter + 1;
			// else
			// 	$no = 1000001;	
			// $jurnalID = $jenis.'/'.$no;
			// $output = array(
			// 				"DR" => $data->DR,
			// 				"CR" => $data->CR,
			// 				"ID" => $jurnalID,
			// 			);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}

	public function getNoDIH()
	{	
		if ($this->logged) 
		{
			$karyawan = $this->input->post('karyawan');
			$transaksi = $this->input->post('transaksi');
			$jenis = $this->input->post('jenis');
			$data = $this->Model_gl->getNoDIH($karyawan,'',$transaksi,$jenis);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}
	public function getNoDIHByBank()
	{	
		if ($this->logged) 
		{
			$bank = $this->input->post('Bank_Ref');
			$transaksi = $this->input->post('transaksi');
			$jenis = $this->input->post('jenis');
			$data = $this->Model_gl->getNoDIH('',$bank,$transaksi,$jenis);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}
	
	public function listGLBank()
	{	
		if ($this->logged) 
		{
			$data = $this->Model_gl->listGLBank();
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}	

	public function listGLKaryawan()
	{	
		if ($this->logged) 
		{
			$data = $this->Model_gl->listGLKaryawan();
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}

	public function listGLGiro()
	{	
		if ($this->logged) 
		{
			$tipegiro = $this->input->post('tipegiro');
			$data = $this->Model_gl->listGLGiro($tipegiro);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}


	public function saveGLTransaksi()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_gl->saveGLTransaksi($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/");
		}
    }	

    public function dataKas()
	{	
		if ( $this->logged) 
		{
			$this->content["kat"] = "KAS";
			$this->twig->display('pembelian/dataTransaksi.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function dataKasPeriode()
	{	
		if ( $this->logged) 
		{
			$this->content["kat"] = "KAS";
			$this->twig->display('pembelian/dataTransaksi2.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function dataBank()
	{	
		if ( $this->logged) 
		{
			$this->content["kat"] = "Bank";
			$this->content['bank'] =$this->Model_gl->listGLBank();
			$this->twig->display('pembelian/dataTransaksibank.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function dataBank2()
	{	
		if ( $this->logged) 
		{
			$this->content["kat"] = "Bank";
			$this->content['bank'] =$this->Model_gl->listGLBank();
			$this->twig->display('pembelian/dataTransaksibank2.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function mutasikasBank()
	{	
		if ( $this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['produk']= $this->Model_main->Produk();
			$this->twig->display('pembelian/datamutasikasbank.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function listDataTransaksi($tipe=NULL)
	{	
		$params = $this->input->post();
		$kat = $this->input->post('kat');
		$byperiode = "";

		if(isset($params['tgl1']) && isset($params['tgl2'])){
	        $tgl1 = $this->input->post('tgl1');
	        $tgl2 = $this->input->post('tgl2');
        	$byperiode = " and Tanggal between '".$tgl1." 00:00:00' and '".$tgl2." 23:59:59' ";
        }

        if(isset($params['tipe'])){
        	$tipe = $params['tipe'];
        }

        if(isset($params['bank_trans'])){
        	$tipe = $params['bank_trans'];
        }

        $tipe = str_replace("%20", " ", $tipe);

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
		$search = $_REQUEST['search']["value"];

		if($search!=""){
            $bySearch = " and (No_Voucher like '%".$search."%' or Buku like '%".$search."%' or Tipe_Kas like '%".$search."%' or bank_trans like '%".$search."%' or Tanggal like '%".$search."%' or Jurnal_ID like '%".$search."%' or Kategori3 like '%".$search."%' or Transaksi like '%".$search."%' or DR like '%".$search."%' or CR like '%".$search."%' or jenis_trans like '%".$search."%' or Jumlah like '%".$search."%' or Saldo_Awal like '%".$search."%' or Debit like '%".$search."%' or Kredit like '%".$search."%' or Saldo_Akhir like '%".$search."%' or Keterangan like '%".$search."%' or Cabang like '%".$search."%' or Ket_2 like '%".$search."%' or Bank_Ref like '%".$search."%' or Karyawan like '%".$search."%')";
            }
		
		$list = $this->Model_gl->listDataTransaksi($bySearch,$tipe,$kat,$byperiode,$byLimit);
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = "";
			$row[] = "";
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->No_Voucher) ? $key->No_Voucher : "");
			$row[] = (!empty($key->Buku) ? $key->Buku : "");
			if($kat=='KAS'){
				$row[] = (!empty($key->Tipe_Kas) ? $key->Tipe_Kas : "");
			}else{
				$row[] = (!empty($key->bank_trans) ? $key->bank_trans : "");
			}
			$row[] = (!empty($key->Tanggal) ? date("Y-m-d",strtotime($key->Tanggal)) : "");
			$row[] = (!empty($key->Jurnal_ID) ? $key->Jurnal_ID : "");
			$row[] = (!empty($key->Kategori3) ? $key->Kategori3 : "");
 			$row[] = (!empty($key->Transaksi) ? $key->Transaksi : "");
 			$row[] = (!empty($key->DR) ? $key->DR : "");
 			$row[] = (!empty($key->CR) ? $key->CR : "");
 			$row[] = (!empty($key->jenis_trans) ? $key->jenis_trans : "");
 			$row[] = "<p align='right'>".(!empty($key->Jumlah) ? number_format($key->Jumlah) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Saldo_Awal) ? number_format($key->Saldo_Awal) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Debit) ? number_format($key->Debit) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Kredit) ? number_format($key->Kredit) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Saldo_Akhir) ? number_format($key->Saldo_Akhir) : 0)."</p>"; 		
 			$row[] = (!empty($key->NoGiro) ? $key->NoGiro  : "");
 			$row[] = (!empty($key->Keterangan) ? $key->Keterangan  : "");			
 			$row[] = (!empty($key->Ket_2) ? $key->Ket_2 : "");
 			if ($key->Ket_2 == "Bank") 
 				$row[] = (!empty($key->Bank_Ref) ? $key->Bank_Ref  : "");
 			elseif($key->Ket_2 == "Karyawan") 
 				$row[] = (!empty($key->Karyawan) ? $key->Karyawan.'~'.$key->Kode_Karyawan.'~'.$key->Ket_4  : "");
 			else
 				$row[] = "-";
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}	

	public function buatBank()
	{	
		if ( $this->logged) 
		{
			
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y' )){
					$no = $this->Model_main->noDokumenTemp("Bank");
			        $this->content['no'] = $no;
			        $this->content['saldo'] = (!empty($saldo->saldo)) ? $saldo->saldo:0;
			        $this->content['bank'] =$this->Model_gl->listGLBank();
					$this->twig->display('pembelian/buatBank.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function getmutasikasbank()
    {
    	if ($this->logged) 
		{
			$tglDoc = $_POST['tgldoc'];
	        $timestamp    = strtotime($tglDoc);
			// $date = date('Y-m-01 00:00:00', $timestamp);
			$date 	= date('Y-m-d', $timestamp);
			$getmonth 	= date('m', $timestamp);
			// $sawal = $this->Model_inventori->saldoawal($Kode,$getmonth);
			$list = $this->Model_gl->getmutasikasbank($timestamp,$date);
			$return['Result'] = $list;
    		// $return['s_awal'] = $sawal;
    		echo json_encode($return);
		
		}
		else{
			redirect("auth/logout");
		}	
    }

    public function setMutasiKas()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$params = $params->grid;
			$params = json_decode($params,true);
			$i=0;
			foreach ($params as $list) {
				$status = $list["status"];
				if($status == "Selisih"){
					$i++;
					break;
				}
			}
			if($i == 0){
				$valid = $this->Model_gl->setMutasiKas($params);
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
    public function getsaldoawalbank()
	{          
        if ($this->logged) 
		{	
			$banktrans = (object)$this->input->post();
			$banktrans = $banktrans->bank_trans;
			// log_message("error",print_r($banktrans,true));
			$valid = $this->Model_gl->getSaldoAwalBank($banktrans);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/");
		}
    }	
    public function getsaldoawalkas()
	{          
        if ($this->logged) 
		{	
			$tipe = (object)$this->input->post();
			$tipekas = $tipe->tipe_kas;
			// log_message("error",print_r($banktrans,true));
			$valid = $this->Model_gl->getSaldoAwalKas($tipekas);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/");
		}
    }	

    public function getNoTitipan()
	{          
        if ($this->logged) 
		{	
			$kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
            $kodeLamaCabang = $kodeLamaCabang->KodeGL;
            $kodeDokumen = $this->Model_main->kodeDokumen('Titipan');
            $data = $this->db->query("select max(right(NoTitipan,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."'")->result();
            if(empty($data[0]->no) || $data[0]->no == ""){
                $titipanNumber = 1000001;
            }else {
                $titipanNumber = ($data[0]->no) + 1; 
            }
            $NoTitipan = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$titipanNumber;
			echo json_encode($NoTitipan);
		}
		else 
		{	
			redirect("auth/");
		}
    }

    public function getNokontra()
	{          
        if ($this->logged) 
		{	
			$kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
            $kodeLamaCabang = $kodeLamaCabang->KodeGL;
            $kodeDokumen = $this->Model_main->kodeDokumen('KasBon');
            $data = $this->db->query("SELECT MAX(RIGHT(NoKasbon,7)) AS no FROM trs_buku_kasbon WHERE Cabang ='".$this->cabang."'")->result();
            if(empty($data[0]->no) || $data[0]->no == ""){
                $kontraNumber = 1000001;
            }else {
                $kontraNumber = ($data[0]->no) + 1; 
            }
            $NoKontra = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$kontraNumber;
			echo json_encode($NoKontra);
		}
		else 
		{	
			redirect("auth/");
		}
    }

    public function saveDataBukuGiro()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_gl->saveDataBukuGiro($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/");
		}
    }

    public function databukugiro()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/databukugiro.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

    public function listDataBukuGiro()
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
		$search = $_REQUEST['search']["value"];

		if($search!=""){
            $bySearch = " and (Cabang like '%".$search."%' or NoGiro like '%".$search."%' or ValueGiro like '%".$search."%' or Tanggal like '%".$search."%' or TglJTOGiro like '%".$search."%' or Bank like '%".$search."%' or KodePelanggan like '%".$search."%' or KodeKaryawan like '%".$search."%' or StatusGiro like '%".$search."%' or TglCairGiro like '%".$search."%' or NoVoucher like '%".$search."%' or Transaksi like '%".$search."%' or JurnalID like '%".$search."%' )";
            }
		
		$list = $this->Model_gl->listDataBukuGiro($bySearch,$byLimit);
        $data = array();
		foreach ($list as $key) {
			$row = array();
			if ($key->StatusGiro == "Cair" or $key->StatusGiro == "Tolak") {
				$row[] = '<button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Batal" onclick="prosesGiro('."'".$key->NoGiro."','Batal'".')" disabled><i class="fa fa-trash"></i> Batal</button>'; 
			}else { 
				$row[] = '<button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Batal" onclick="prosesGiro('."'".$key->NoGiro."','Batal'".')"><i class="fa fa-trash"></i> Batal</button>'; 
			}
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->NoGiro) ? $key->NoGiro : "");
			$row[] = (!empty($key->ValueGiro) ? $key->ValueGiro : 0);
			$row[] = (!empty($key->Tanggal) ? $key->Tanggal : "");
			$row[] = (!empty($key->TglJTOGiro) ? $key->TglJTOGiro : "");
 			$row[] = (!empty($key->Bank) ? $key->Bank : "");
 			$row[] = (!empty($key->KodePelanggan) ? $key->KodePelanggan.'~'.$key->NamaPelanggan : "");
 			$row[] = (!empty($key->KodeKaryawan) ? $key->KodeKaryawan.'~'.$key->NamaKaryawan  : "");
 			$row[] = (!empty($key->StatusGiro) ? $key->StatusGiro  : "");			
 			$row[] = (!empty($key->TglCairGiro) ? $key->TglCairGiro : "");
 			$row[] = (!empty($key->NoVoucher) ? $key->NoVoucher : "");
 			$row[] = (!empty($key->Transaksi) ? $key->Transaksi : "");
 			$row[] = (!empty($key->JurnalID) ? $key->JurnalID : "");	
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}		
	public function getlistNokontra()
	{	
		if ($this->logged) 
		{
			$karyawan = $this->input->post('karyawan');
			log_message("error",print_r($karyawan,true));
			$data = $this->Model_gl->getlistNokontra($karyawan);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/");
		}
	}	

	public function databukukasbon()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/databukukasbon.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}
	public function listDataBukuKasbon()
	{	
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
		$bySearch = "";
		$search = $_REQUEST['search']["value"];

		if($search!=""){
            $bySearch = " and (Cabang like '%".$search."%' or NoKasbon like '%".$search."%' or ValueKasbon like '%".$search."%' or Tanggal like '%".$search."%' or KodeKaryawan like '%".$search."%' or NamaKaryawan like '%".$search."%' or NoVoucher like '%".$search."%' or JurnalID like '%".$search."%' or Transaksi like '%".$search."%' or Status like '%".$search."%' or ValueRealisasi like '%".$search."%' or NoKasbon_Asal like '%".$search."%' or Keterangan like '%".$search."%' )";
            }
		$list = $this->Model_gl->listDataBukuKasbon($bySearch,$byLimit);
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->NoKasbon) ? '.'.$key->NoKasbon : "");
			$row[] = (!empty($key->Tanggal) ? $key->Tanggal : "");
			$row[] = (!empty($key->KodeKaryawan) ? $key->KodeKaryawan.'~'.$key->NamaKaryawan  : "");
			$row[] = (!empty($key->Status) ? $key->Status  : "");			
 			$row[] = "<p align='right'>".(!empty($key->ValueKasbon) ? number_format($key->ValueKasbon) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->ValueRealisasi) ? number_format($key->ValueRealisasi) : 0)."</p>";
 			$row[] = (!empty($key->NoKasbon_Asal) ? '.'.$key->NoKasbon_Asal  : "");
 			$row[] = (!empty($key->NoVoucher) ? $key->NoVoucher : "");
 			$row[] = (!empty($key->Transaksi) ? $key->Transaksi : "");
 			$row[] = (!empty($key->JurnalID) ? $key->JurnalID : "");
 			$row[] = (!empty($key->Keterangan) ? $key->Keterangan : "");	
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}	
	public function buatkasopname()
	{	
		if ( $this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/buatkasopname.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}
	public function saveopnamekas()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$valid = $this->Model_gl->saveopnamekas($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/");
		}
    }

    public function dataOpnameKas()
	{	
		if ( $this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/dataOpnamekas.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}	

	public function listDataOpnameKas()
	{	
		$tgl = $this->input->post('tgl');
		$tgldoc = date('Y-m-d',strtotime($tgl));
		$list = $this->Model_gl->listDataOpnameKas($tgldoc);
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$nilaiopname = ($key->totaluangkertas+$key->totaluanglogam);
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->TglOpname) ? date("Y-m-d",strtotime($key->TglOpname)) : "");
			$row[] = (!empty($key->TipeKas) ? $key->TipeKas : "");
			if($key->selisih != 0){
				$row[] = "Selisih";
			}else{
				$row[] = (!empty($key->status) ? $key->status : "");
			}
			$row[] = "<p align='right'>".(!empty(number_format($key->saldoawal)) ? number_format($key->saldoawal) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty(number_format($nilaiopname)) ? number_format($nilaiopname) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty(number_format($key->selisih)) ? number_format($key->selisih) : 0)."</p>";
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);

	}

	public function buatclosingkas()
	{	
		if ( $this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/closinghariankas.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function getclosingkas()
	{	
		if ($this->logged) 
		{
			$cabang = $this->input->post('cabang');
			$tipekas = $this->input->post('tipekas');
			$tanggal = $this->input->post('tanggal');
			$timestamp    = strtotime($tanggal);
			$date 	= date('Y-m-d', $timestamp);
			$kaskecil = $this->Model_gl->getclosingkaskecil($cabang,$date);
			log_message("error",print_r($kaskecil,true));
			$kasbankmasuk = $this->Model_gl->getkasbankmasuk($cabang,$date);
			$giromasuk = $this->Model_gl->getgirocair($cabang,$date);
			$pelunasan = $this->Model_gl->getpelunasan($cabang,$date);
			$totalgiro = $this->Model_gl->getgiro($cabang,$date);
			$totalglgiro = $this->Model_gl->getglgiro($cabang,$date);
			$kasbesar = $this->Model_gl->getclosingkasbesar($cabang,$date);
			$saldobank = $this->Model_gl->getclosingbank($cabang,$date);
			$output = array(
		          "kaskecil" => $kaskecil,
		          "kasbankmasuk" => $kasbankmasuk,
		          "pelunasan" => $pelunasan,
		          "giromasuk" => $giromasuk,
		          "totalgiro" => $totalgiro,
		          "totalglgiro" => $totalglgiro,
		          "kasbesar" => $kasbesar,
		          "saldobank" => $saldobank
		          );
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/");
		}
	}
	public function listdetailOpnameKas()
	{	
		$tgl = $this->input->post('tgl');
		$tgldoc = date('Y-m-d',strtotime($tgl));
		$kecil = $this->Model_gl->listdetailOpnameKaskecil($tgldoc);
        $besar = $this->Model_gl->listdetailOpnameKasbesar($tgldoc);
		$output = array(
                        "kecil" => $kecil,
                        "besar" => $besar,
				);
		//output to json format
		echo json_encode($output);
	}	

	public function getsaldoawalopnamekas()
	{          
        if ($this->logged) 
		{	
			$tipe = (object)$this->input->post();
			$tipekas = $tipe->tipe_kas;
			$tgl = $tipe->tgl;
			$valid = $this->Model_gl->getSaldoAwalOpnameKas($tipekas,$tgl);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/");
		}
    }	
    public function databukutitipan()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/databukutitipan.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

    public function listDataBukuTitipan()
	{	
		$list = $this->Model_gl->listDataBukuTitipan();
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->NoTitipan) ? $key->NoTitipan : "");
			$row[] = (!empty($key->Tanggal) ? $key->Tanggal : "");
			// $row[] = (!empty($key->KodeKaryawan) ? $key->KodeKaryawan.'~'.$key->NamaKaryawan  : "");
			$row[] = (!empty($key->Status) ? $key->Status  : "");			
 			$row[] = "<p align='right'>".(!empty($key->ValueTitipan) ? number_format($key->ValueTitipan) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Saldo) ? number_format($key->Saldo) : 0)."</p>";
 			// $row[] = (!empty($key->NoKasbon_Asal) ? $key->NoKasbon_Asal  : "");
 			// $row[] = (!empty($key->NoVoucher) ? $key->NoVoucher : "");
 			// $row[] = (!empty($key->Transaksi) ? $key->Transaksi : "");
 			// $row[] = (!empty($key->JurnalID) ? $key->JurnalID : "");
 			// $row[] = (!empty($key->Keterangan) ? $key->Keterangan : "");	
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}	

	public function dataTransaksiKasBank()
	{	
		if ( $this->logged) 
		{
			$this->content["kat"] = "Bank";
			$this->content['bank'] =$this->Model_gl->listGLBank();
			$this->twig->display('pembelian/dataTransaksiKasBank.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}
	public function listDataTransaksiKasBank($tipe=NULL)
	{	
		$params = $this->input->post();
		$kat = $this->input->post('kat');
		$byperiode = "";

		if(isset($params['tgl1']) && isset($params['tgl2'])){
	        $tgl1 = $this->input->post('tgl1');
	        $tgl2 = $this->input->post('tgl2');
        	$byperiode = " and Tanggal between '".$tgl1." 00:00:00' and '".$tgl2." 23:59:59' ";
        }

        // if(isset($params['tipe'])){
        // 	$tipe = $params['tipe'];
        // }

        // if(isset($params['bank_trans'])){
        // 	$tipe = $params['bank_trans'];
        // }

        // $tipe = str_replace("%20", " ", $tipe);

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
		$search = $_REQUEST['search']["value"];

		if($search!=""){
            $bySearch = " and (No_Voucher like '%".$search."%' or Buku like '%".$search."%' or Tipe_Kas like '%".$search."%' or bank_trans like '%".$search."%' or Tanggal like '%".$search."%' or Jurnal_ID like '%".$search."%' or Kategori3 like '%".$search."%' or Transaksi like '%".$search."%' or DR like '%".$search."%' or CR like '%".$search."%' or jenis_trans like '%".$search."%' or Jumlah like '%".$search."%' or Saldo_Awal like '%".$search."%' or Debit like '%".$search."%' or Kredit like '%".$search."%' or Saldo_Akhir like '%".$search."%' or Keterangan like '%".$search."%' or Cabang like '%".$search."%' or Ket_2 like '%".$search."%' or Bank_Ref like '%".$search."%' or Karyawan like '%".$search."%')";
            }
		
		$list = $this->Model_gl->listDataTransaksiKasBank($bySearch,$byperiode,$byLimit);
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = "";
			$row[] = "";
			$row[] = (!empty($key->Cabang) ? $key->Cabang : "");
			$row[] = (!empty($key->No_Voucher) ? $key->No_Voucher : "");
			$row[] = (!empty($key->Buku) ? $key->Buku : "");
			if($key->Buku=='KAS'){
				$row[] = (!empty($key->Tipe_Kas) ? $key->Tipe_Kas : "");
			}else{
				$row[] = (!empty($key->bank_trans) ? $key->bank_trans : "");
			}
			$row[] = (!empty($key->Tanggal) ? date("Y-m-d",strtotime($key->Tanggal)) : "");
			$row[] = (!empty($key->Jurnal_ID) ? $key->Jurnal_ID : "");
			$row[] = (!empty($key->Kategori3) ? $key->Kategori3 : "");
 			$row[] = (!empty($key->Transaksi) ? $key->Transaksi : "");
 			$row[] = (!empty($key->DR) ? $key->DR : "");
 			$row[] = (!empty($key->CR) ? $key->CR : "");
 			$row[] = (!empty($key->jenis_trans) ? $key->jenis_trans : "");
 			$row[] = "<p align='right'>".(!empty($key->Jumlah) ? number_format($key->Jumlah) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Saldo_Awal) ? number_format($key->Saldo_Awal) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Debit) ? number_format($key->Debit) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Kredit) ? number_format($key->Kredit) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty($key->Saldo_Akhir) ? number_format($key->Saldo_Akhir) : 0)."</p>"; 		
 			$row[] = (!empty($key->NoGiro) ? $key->NoGiro  : "");
 			$row[] = (!empty($key->Keterangan) ? $key->Keterangan  : "");			
 			$row[] = (!empty($key->Ket_2) ? $key->Ket_2 : "");
 			if ($key->Ket_2 == "Bank") 
 				$row[] = (!empty($key->Bank_Ref) ? $key->Bank_Ref  : "");
 			elseif($key->Ket_2 == "Karyawan") 
 				$row[] = (!empty($key->Karyawan) ? $key->Karyawan.'~'.$key->Kode_Karyawan.'~'.$key->Ket_4  : "");
 			else
 				$row[] = "-";
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function prosesDataTransaksi()
	{          
        if ($this->logged) 
		{	
			$Nogiro = $this->input->post('No');
			$valid = $this->db->query("update trs_buku_giro
				                       set StatusGiro ='Batal'
				                       where Nogiro ='".$Nogiro."' and 
				                            ifnull(StatusGiro,'') not in ('Cair','Tolak')");
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/");
		}
    }
    public function datamutasikas()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('pembelian/datamutasikas.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 
    public function getdatamutasikas()
    {
        if ($this->logged) 
        {
            $tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $cabang = $_POST['cabang'];
            $tipekas = $_POST['tipekas'];
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $sawal = $this->Model_gl->getSaldoAwalMutasiKas($tipekas,$tgl1);		
            $list = $this->Model_gl->getdatamutasikas($bydate,$tipekas);
            $return['Result'] = $list;
    		$return['s_awal'] = $sawal;
            echo json_encode($return);
        }
        else{
            redirect("auth/logout");
        }   
    }
    public function datamutasibank()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content["kat"]   = "Bank";
			$this->content['bank']  = $this->Model_gl->listGLMutasiBank();
            $this->twig->display('pembelian/datamutasibank.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 
    public function getdatamutasibank()
    {
        if ($this->logged) 
        {
            $tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $cabang = $_POST['cabang'];
            $bank_trans = $_POST['bank_trans'];
            log_message("error",print_r($bank_trans,true));
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $sawal = $this->Model_gl->getSaldoAwalMutasiBank($bank_trans,$tgl1);		
            $list = $this->Model_gl->getdatamutasibank($bydate,$bank_trans);
            $return['Result'] = $list;
    		$return['s_awal'] = $sawal;
            echo json_encode($return);
        }
        else{
            redirect("auth/logout");
        }   
    }
    function SaldoAwalMutasiBank(){
    	if ($this->logged) 
        {
        	$tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $cabang = $_POST['cabang'];
            $bank_trans = $_POST['bank_trans'];
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $sawal = $this->Model_gl->getSaldoAwalMutasiBank($bank_trans,$tgl1);
            echo json_encode($sawal);
        }else{
            redirect("auth/logout");
        } 
    }
    function SaldoAwalMutasiKas(){
    	if ($this->logged) 
        {
        	$tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $cabang = $_POST['cabang'];
            $tipekas = $_POST['tipekas'];
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $sawal = $this->Model_gl->getSaldoAwalMutasiKas($tipekas,$tgl1);
            echo json_encode($sawal);
        }else{
            redirect("auth/logout");
        } 
    }
}
