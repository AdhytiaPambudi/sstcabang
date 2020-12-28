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
			$this->content['user']= $this->username;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['totaluser']= $this->Model_main->userlogin();
			$this->content['dateclosing']= $this->Model_main->getdateclosing();
			$this->content['setstok'] = $setstok;
			$this->content['setkasbank'] = $setkasbank;
			$this->twig->display('pembelian/closingharian.html', $this->content);
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
			$DoTerima = $this->Model_closing->getDOTerima();
			$tglclosing = $this->Model_main->getdateclosing();
			$DoOpen  = $DoOpen[0]->NoDO;
			$DoKirim = $DoKirim[0]->NoDO;
			$DoTerima = $DoTerima[0]->NoDO;
			$this->content['user']= $this->username;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['totaluser']= $this->Model_main->userlogin();
			$this->content['dateclosing']= $tglclosing[0]->tgl_stok_closing;
			// $this->content['datesettlement']= $tglclosing[0]->tgl_settlement_month;
			// $this->content['setstok'] = $setstok;
			// $this->content['setkasbank'] = $setkasbank;
			$this->content['cektglstok'] = $cektglstok;
			// $this->content['cektglsettlement'] = $cektglsettlement;
			$this->content['DoOpen'] = $DoOpen;
			$this->content['DoKirim'] = $DoKirim;
			$this->content['DoTerima'] = $DoTerima;
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
			$valid = $this->Model_closing->setClosingdaily();
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
}