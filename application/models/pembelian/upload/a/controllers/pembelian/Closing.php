<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Closing extends CI_Controller {

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
		$this->load->model('pembelian/Model_closing');
		$this->load->library('format');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->username = $this->session->userdata('username');
		$this->userGroup = $this->session->userdata('user_group');
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

	public function closingharian()
	{	
		if ( $this->logged ) 
		{
			$setstok 	=  $this->Model_closing->getsettlementstokday();
			$setkasbank =  $this->Model_closing->getsettlementkasbankday();
			$setstok 	= $setstok[0]->count;
			$setkasbank = $setkasbank[0]->count;
			if($setstok == 0){
				$setstok = 'Belum';
			}else if($setstok > 0){
				$setstok = 'OK';
			}
			if($setkasbank == 0){
				$setkasbank = 'Belum';
			}else if($setkasbank > 0){
				$setkasbank = 'OK';
			}
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			if($cektglstok == 1 ){
				$this->content['user']= $this->username;
				$this->content['cabang']= $this->Model_main->Cabang();
				$this->content['totaluser']= $this->Model_main->userlogin();
				$this->content['dateclosing']= $this->Model_main->getdateclosing();
				$this->content['setstok'] = $setstok;
				$this->content['setkasbank'] = $setkasbank;
				$this->twig->display('pembelian/closingharian.html', $this->content);
			}else if($cektglstok == 0){
				redirect("main");
			}
			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function closingbulanan()
	{	
		if ( $this->logged ) 
		{
			
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			// $cektglsettlement = $this->Model_main->cek_tglsettlement();
			if($cektglstok == 0){
				$cektglstok = 'Belum';
			}else if($cektglstok == 1){
				$cektglstok = 'OK';
			}
			// if($cektglsettlement == 0){
			// 	$cektglsettlement = 'Not OK';
			// }else if($cektglsettlement == 1){
			// 	$cektglsettlement = 'OK';
			// }
			$DoOpen = $this->Model_closing->getDOopen();
			$DoKirim = $this->Model_closing->getDOKirim();
			// $DoTerima = $this->Model_closing->getDOTerima();
			$AppAdjustment = $this->Model_closing->AppAdjustment();
			$AppRelokasi = $this->Model_closing->AppRelokasi();
			$AppReturBeli = $this->Model_closing->AppReturBeli();
			$AppRDO = $this->Model_closing->AppRDO();
			$tglclosing = $this->Model_main->getdateclosing();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$AppAdjustment = $AppAdjustment[0]->NoKoreksi;
			$AppRelokasi = $AppRelokasi[0]->NoRelokasi;
			$AppReturBeli = $AppReturBeli[0]->UsulanRetur;
			$AppRDO = $AppRDO[0]->NoDO;
			// $DoTerima = $DoTerima[0]->NoDO;
			$this->content['user']= $this->username;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['totaluser']= $this->Model_main->userlogin();
			$this->content['dateclosing']= $tglclosing[0]->tgl_stok_closing;
			$this->content['cektglstok'] = $cektglstok;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			// $this->content['DoTerima'] = $DoTerima;
			$this->content['AppAdjustment'] = $AppAdjustment;
			$this->content['AppRelokasi'] = $AppRelokasi;
			$this->content['AppReturBeli'] = $AppReturBeli;
			$this->content['AppRDO'] = $AppRDO;
			$this->twig->display('pembelian/closingbulanan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}


	// public function closingbulanan()
	// {	
	// 	if ( $this->logged ) 
	// 	{
	// 		$setstok 	=  $this->Model_closing->getsettlementstokmonth();
	// 		$setkasbank =  $this->Model_closing->getsettlementkasbankmonth();
	// 		$setstok 	= $setstok[0]->count;
	// 		$setkasbank = $setkasbank[0]->count;
	// 		if($setstok == 0){
	// 			$setstok = 'Belum';
	// 		}else if($setstok > 0){
	// 			$setstok = 'OK';
	// 		}
	// 		if($setkasbank == 0){
	// 			$setkasbank = 'Belum';
	// 		}else if($setkasbank > 0){
	// 			$setkasbank = 'OK';
	// 		}
	// 		$this->content['user']= $this->username;
	// 		$this->content['cabang']= $this->Model_main->Cabang();
	// 		$this->content['totaluser']= $this->Model_main->userlogin();
	// 		$this->content['dateclosing']= $this->Model_main->getdateclosing();
	// 		$this->content['setstok'] = $setstok;
	// 		$this->content['setkasbank'] = $setkasbank;
	// 		$this->twig->display('pembelian/closingbulanan.html', $this->content);
	// 	}
	// 	else 
	// 	{
	// 		redirect("auth/logout");
	// 	}
	// }

	

	public function setClosing()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			// $params = $params->grid;
			$valid = $this->Model_closing->setClosing();
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function setClosingBulan()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			// $params = $params->grid;
			$valid = $this->Model_closing->setClosingBulan();
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

   public function db_download()
	{	
		if ( $this->logged ) 
		{
			$this->content['user']= $this->username;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['totaluser']= $this->Model_main->userlogin();
			$db = $this->cek_db();
			if($db==true){
				$this->twig->display('pembelian/db_download.html', $this->content);
			}else{
				$this->content['closing']= 1;
				$this->twig->display('index.html', $this->content);
			}
			
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function getdownload()
	{          
        if ($this->logged) 
		{	
			$valid=false;
			$backup = $this->Model_closing->getbackup();
			if($backup==true){
				$valid=$this->Model_closing->getdownload();
				$data = $this->Model_closing->table_num();
				echo json_encode(array("status" => $valid,"row" =>$data));
			}else{
				$valid=false;
				echo json_encode(array("status" => $valid));
			}
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function cek_db(){
    	$dbhost = 'localhost:3306';
        $dbuser = 'sapta';
        $dbpass = 'Sapta254*x';
        $mysql_database = "sst";
    	// ====== cek database lokal ============ 
        $con = mysqli_connect($dbhost, $dbuser, $dbpass);
        if($db  = mysqli_query($con, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'sst'")){
            $row_db = mysqli_num_rows($db);
            if($row_db > 0)
            {
                $dbase=true;
            }else{
                $dbase=false;
                $valid=true;
            }
        }
        if($dbase==true){
            $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$mysql_database);
            // $data_count = mysqli_query($con,"SELECT count(Kode_Produk) FROM mst_produk");
            if ($result = mysqli_query($conn, "SELECT Kode_Produk,Produk FROM mst_produk")) {
                /* determine number of rows result set */
                $row_cnt = mysqli_num_rows($result);
                if($row_cnt > 10000)
                {
                    $valid=false;
                }else{
                    $valid=true;
                }
                mysqli_free_result($result);
            }else{
                log_message("error","belum ada database ");
                $valid=true;
            }
            mysqli_close($conn);
        }

        return $valid;
    }

    public function uploaddatacabang()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/uploaddatacabang.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getuploadcabang()
	{          
        if ($this->logged) 
		{	
			$valid=false;
			$params = $this->input->post();
            if(isset($params['tgl1']) && isset($params['tgl2'])){
                $tgl1 = $this->input->post('tgl1');
                $tgl2 = $this->input->post('tgl2');
            }
			$valid = $this->Model_closing->getuploadcabang($tgl1,$tgl2);
			echo json_encode(array("status" => $valid));
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function setClosingdaily()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$tipe = $_POST['tipe'];
			$status = $_POST['status'];
			$valid = $this->Model_closing->setClosingdaily($tipe,$status);
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function FixDOFakturClosing()
	{	
		if ( $this->logged ) 
		{
			$status = $_POST['status'];
			$tipe = $_POST['tipe'];
			$this->content['status'] = $status;
			$this->content['tipe'] = $tipe;
			if($status == "doheader" or $status == "dodetail"){
				$this->twig->display('pembelian/fixdoheader.html', $this->content);
			}elseif($status == "dokirim"){
				$this->twig->display('pembelian/fixdokirim.html', $this->content);
			}elseif($status == "cndn"){
				$this->twig->display('pembelian/fixcndn.html', $this->content);
			}elseif($status == "vallunas"){
				$this->twig->display('pembelian/fixpelunasan.html', $this->content);
			}elseif($status == "doopen"){
				$this->twig->display('pembelian/fixdoOpen.html', $this->content);
			}elseif($status == "doclosed"){
				$this->twig->display('pembelian/fixdoClosed.html', $this->content);
			}elseif($status == "bpb" or $status == "bpbdetail"){
				$this->twig->display('pembelian/fixbpbheader.html', $this->content);
			}elseif($status == "dofaktur"){
				$this->twig->display('pembelian/fixdofaktur.html', $this->content);
			}
			
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listFixDOFaktur()
	{	
		if ($this->logged) 
		{
			$data = "";
			$params = $this->input->post();
            $byperiode = "";
            $tipe = $this->input->post('tipe');
            $jenis = $this->input->post('jenis');
			$query=$this->Model_closing->listFixDOFaktur($tipe,$jenis)->result();
			$x = 0;
			$i = 0;
			//doheader,dodetail,dokirim,cndn,vallunas
			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if($tipe =='doheader' or $tipe =='dodetail'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty(number_format($baris->Total)) ? ($baris->Total) : "");
		 			$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
		 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		 			$row[] = (!empty(number_format($baris->TotalB)) ? number_format($baris->TotalB) : "");
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".','."'DO'".')"><i class="fa fa-eye"></i>Fix Data</a>';
				}
				elseif($tipe =='dokirim'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->status) ? $baris->status : "");
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".','."'".$baris->status."'".')"><i class="fa fa-eye"></i>Fix Data</a>';
		 			
		 			
				}
				elseif($tipe =='cndn'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
		 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		 			$row[] = (!empty(number_format($baris->Total)) ? number_format($baris->Total) : "");
		 			$row[] = (!empty(number_format($baris->Total_detail)) ? number_format($baris->Total_detail) : "");
		 			$row[] = '-';
		 			$row[] = '<a class="btn btn-sm btn-info" title="Fix Stok" onclick="view('."'".$baris->NoFaktur."'".','."'".$tipe."'".')"><i class="fa fa-eye"></i>View</a>';
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoFaktur."'".','."'".$tipe."'".','."'CNDN'".'><i class="fa fa-eye"></i>Fix Data</a>';
				}
				elseif($tipe =='vallunas'){
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
				}elseif($tipe =='doopen' or $tipe =='doclosed'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->status) ? $baris->status : "");
		 			$row[] = (!empty($baris->status_retur) ? $baris->status_retur : "");
		 			$row[] = (!empty($baris->NoKiriman) ? $baris->NoKiriman : "");
		 			$row[] = (!empty($baris->StatusKiriman) ? $baris->StatusKiriman : "");
		 			$row[] = (!empty($baris->StatusDO) ? $baris->StatusDO : "");
		 			// $row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".','."'".$baris->status."'".')"><i class="fa fa-eye"></i>Fix Data</a>';	
		 			$row[] = "Fix manual";
				}elseif($tipe =='bpb' or $tipe =='bpbdetail'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
		 			$row[] = (!empty($baris->TglDokumen) ? $baris->TglDokumen : "");
		 			$row[] = (!empty(number_format($baris->Value)) ? ($baris->Value) : "");
		 			$row[] = (!empty(number_format($baris->Total)) ? ($baris->Total) : "");
		 			$row[] = (!empty($baris->DOBeli) ? $baris->DOBeli : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty(number_format($baris->ValueDO)) ? number_format($baris->ValueDO) : "");
		 			$row[] = (!empty(number_format($baris->TotalDO)) ? number_format($baris->TotalDO) : "");
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDokumen."'".','."'".$baris->DOBeli."'".','."'".$tipe."'".','."'bpb'".')"><i class="fa fa-eye"></i>Fix Data</a>';
				}elseif($tipe =='dofaktur'){
					$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
					$row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
		 			$row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
		 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		 			$row[] = (!empty($baris->status) ? $baris->status : "");
		 			$row[] = (!empty(number_format($baris->Total)) ? ($baris->Total) : "");
		 			$row[] = (!empty(number_format($baris->TotalD)) ? ($baris->TotalD) : "");
		 			$row[] = "Fix Manual";
		 			$row[] = '<a class="btn btn-sm btn-success" title="Fix Stok" onclick="fix('."'".$baris->NoDO."'".','."'".$tipe."'".','."'dofaktur'".')"><i class="fa fa-eye"></i>Fix Data</a>';
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

	public function prosesfixDoClosing()
	{          
        if ($this->logged) 
		{	
			//doheader,dodetail,dokirim,cndn,vallunas
			$No = $_POST['No'];
			$NoDO = "";
			$tipe = $_POST['tipe'];
			$status = $_POST['status'];
			if($tipe=='doheader'){
				$valid = $this->Model_closing->prosesfixfakturheder($No);
			}
			elseif($tipe=='dodetail'){
				$valid = $this->Model_closing->prosesfixfakturdetail($No);
			}
			elseif($tipe=='vallunas'){
				$valid = $this->Model_closing->prosesfixsaldo($No);
			}
			elseif($tipe=='cndn'){
				$valid = $this->Model_closing->prosesfixcndn($No);
			}elseif($tipe=='dokirim'){
				$valid = $this->Model_closing->prosesfixdokirim($No,$status);
			}elseif($tipe=='bpb'){
				$NoDO = $_POST['NoDO'];
				$valid = $this->Model_closing->prosesfixbpbheader($No,$NoDO,$status);
			}elseif($tipe=='bpbdetail'){
				$NoDO = $_POST['NoDO'];
				$valid = $this->Model_closing->prosesfixbpbdetail($No,$NoDO,$status);
			}elseif($tipe=='dofaktur'){
				$NoDO = $_POST['NoDO'];
				$valid = $this->Model_closing->prosesfixdofaktur($NoDO,$status);
			}
			
			echo json_encode($valid);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function BypassClosing(){
    	if ($this->logged) 
		{
	        $query = $this->db->query("Update mst_closing
	        							set tgl_daily_closing ='".date('Y-m-d')."',
	        							flag_trans = 'Y'");
	        echo json_encode(array("status" => $query));
	    }else 
		{	
			redirect("auth/logout");
		}
    } 
    public function setKirimdatadaily()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$tipe = $_POST['tipe'];
			$status = $_POST['status'];
			if($tipe == "monthly"){
				$valid = $this->Model_closing->setKirimdatamonthly($tipe,$status);
			}else{
				$valid = $this->Model_closing->setKirimdatadaily($tipe,$status);
			}
			echo json_encode(array("status" => true));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function setcreatetablepu()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$tipe = $_POST['tipe'];
			$status = $_POST['status'];
			$valid = $this->Model_closing->setcreatetablepu($tipe,$status);
			echo json_encode(array("status" => true));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}