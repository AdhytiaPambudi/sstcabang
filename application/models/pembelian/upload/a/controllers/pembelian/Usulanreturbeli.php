<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usulanreturbeli extends CI_Controller {

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
		$this->load->model('pembelian/Model_usulanBeli');
		$this->load->model('pembelian/Model_retur_beli');
		$this->load->library('format');
		$this->load->library('upload_file');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->cabang = $this->session->userdata('cabang');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	// public function usulanReturBeli()
	// {	
	// 	if ( $this->logged ) 
	// 	{
	// 		$this->content['cabang']= $this->Model_main->Cabang();
	// 		// $this->content['prinsipal']= $this->Model_main->Prinsipal();
	// 		// $this->content['supplier']= $this->Model_main->Supplier();
	// 		// $this->content['produk']= $this->Model_main->Produk();
	// 		// $this->content['pelanggan']= $this->Model_main->Pelanggan();
	// 		$this->twig->display('pembelian/usulanReturBeli.html', $this->content);
	// 	}
	// 	else 
	// 	{
	// 		redirect("auth/logout");
	// 	}
	// }

	public function usulanReturBeli()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['Prinsipal']= $this->Model_main->Prinsipal();
			// $this->content['supplier']= $this->Model_main->Supplier();
			// $this->content['produk']= $this->Model_main->Produk();
			// $this->content['pelanggan']= $this->Model_main->Pelanggan();
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$this->twig->display('pembelian/usulanReturBeli.html', $this->content);
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

	public function load_prinsipal(){
		$prinsipal = $this->Model_main->Prinsipal();
		echo json_encode($prinsipal);
	}

	public function datareturbeli()
	{	
		if ( $this->logged ) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$this->twig->display('pembelian/datareturbeli.html', $this->content);
					}else{
						redirect("main");
					}
					
				}else if($cektglstok == 0){
					redirect("main");
				}
			}else{
				$this->twig->display('pembelian/datareturbeli.html', $this->content);
			}
		}else 
		{
			redirect("auth/logout");
		}
	}

	public function getstok_retur(){
		$stok = $this->Model_retur_beli->getstok($_POST['produk'], $_POST['batch'], $_POST['batchdoc']);
		echo json_encode($stok);
		// return $stok;
	}

	public function listdatareturbeli($cek = null){
		$data = [];
		$output=[];

		$bySearch = "";
		$byLimit = "";
		$draw = "";
		if (isset($_REQUEST['draw'])) {
			$draw=$_REQUEST['draw'];
		}
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
		$search = "";
			if (isset($_REQUEST['search']['value'])) {
				$search=$_REQUEST['search']['value'];
			}
		if($cek=='all')
			$total = $this->Model_retur_beli->listdatareturbeli('','','')->num_rows();
		else
			$total = $this->Model_retur_beli->listdatareturbeli('','','Open')->num_rows();

		$output['recordsTotal']=$output['recordsFiltered']=$total;
		$output['data']=[];

		if($search!=""){
			$bySearch = " and (No_Retur like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Value_usulan like '%".$search."%' or Status_Usulan like '%".$search."%' or No_BPB like '%".$search."%' or tipe_retur like '%".$search."%' or added_user like '%".$search."%' or Status like '%".$search."%')";
		}

		if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;

		if($cek=='all')
				$query=$this->Model_retur_beli->listdatareturbeli($bySearch,$byLimit,'')->result();
			else
				$query=$this->Model_retur_beli->listdatareturbeli($bySearch, $byLimit, "Open")->result();
		
		if($search!=""){			
			if($cek=='all')
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_retur_beli->listdatareturbeli($bySearch,'','')->num_rows();
			else
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_retur_beli->listdatareturbeli($bySearch,'', "Open")->num_rows();
		}

		$x = 0;
		$i = 0;
		foreach ($query as $baris) {
				$x++;
				$row = array();

				$row[] = $x;
				$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->No_Usulan) ? $baris->No_Usulan : "" ).'" dokumen="kosong"></p>';
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="upload_pusat('."'".$baris->No_Usulan."'".')"><i class="fa fa-check"></i> Proses Pusat</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->No_Usulan."'".','."'".$baris->Prinsipal."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$row[] = (!empty($baris->No_Usulan) ? $baris->No_Usulan : "");
	 			$row[] = (!empty($baris->tanggal) ? $baris->tanggal : "");
	 			$row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
	 			$row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
	 			$row[] = (!empty($baris->Value_Usulan) ? number_format($baris->Value_Usulan) :0);
	 			$row[] = (!empty($baris->Status_Usulan) ? $baris->Status_Usulan : "");
	 			$row[] = (!empty($baris->Dokumen) ? $baris->Dokumen : "-");
	 			if($this->userGroup == 'BM') {
	 				if(empty($baris->App_BM_Status)){
	 					$row[] = '<button type="button" class="btn btn-sm btn-info" href="javascript:void(0)" title="Approve" onclick="Approve_confirm('."'".$baris->No_Usulan."'".')"><i class="glyphicon glyphicon-thumbs-up"></i> Approve</button>';
	 				}else{
	 					$row[] = $baris->App_BM_Status;
	 				}
	 			}else{
	 				$row[] = (!empty($baris->App_BM_Status) ? $baris->App_BM_Status : "Pending");
	 			}
	 			// $row[] = (!empty($baris->App_BM_Time) ? $baris->App_BM_Time : "");
	 			$row[] = (!empty($baris->tipe_retur) ? $baris->tipe_retur : "");
	 			$row[] = (!empty($baris->No_BPB) ? $baris->No_BPB : 0);
	 			$row[] = (!empty($baris->counter_print) ? $baris->counter_print : 0);
	 			$row[] = (!empty($baris->App_BM_Alasan) ? $baris->App_BM_Alasan : "-");
	 			
				$data[] = $row;
				$i++;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);

	}

	public function detail_retur_beli(){
		$output=[];
		$no = $_POST['no'];
		$query=$this->Model_retur_beli->detailreturbeli($no)->result();
		$output['data'] = $query;
		echo json_encode($output);
	}

	public function approval_retur_beli(){
		$this->datareturbeli();
	}

	public function approve_retur_beli(){
		$no = $_POST['no'];
		$status = $_POST['status'];
		$query=$this->Model_retur_beli->approve($no,$status);
		echo json_encode($query);
	}

	public function cetakreturbeli(){
		$no = $_POST['no'];
		$query=$this->Model_retur_beli->cetakreturbeli($no);
		echo json_encode($query);
	}

	public function upload_retur_ke_pusat(){
		$no = $_POST['no'];
		$query=$this->Model_retur_beli->upload_pusat($no);
		echo json_encode($query);
	}

	public function download_retur_beli_pusat(){
		$query=$this->Model_retur_beli->download_retur_beli_pusat();
		echo json_encode($query);
	}

// ======================================================================================================================
	public function usulanBeliPrinsipal()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['prinsipal']= $this->Model_main->Prinsipal();
			$this->content['supplier']= $this->Model_main->Supplier();
			$this->content['produk']= $this->Model_main->Produk();
			$this->content['pelanggan']= $this->Model_main->Pelanggan();
			$this->twig->display('pembelian/usulanBeli_bak.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listProdukUsulanBeli()
	{
		if ( $this->logged ) 
		{
			if ($_POST) 
				$data = $this->Model_usulanBeli->listProduk($_POST['supplier'], $_POST['prinsipal']);

			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function getProdukUsulanBeli($kode = NULL)
	{
		if ( $this->logged ) 
		{  
		  $data = $this->Model_usulanBeli->getProdukUsulanBeli($kode);
		  $harga = $this->Model_usulanBeli->getHargaUsulanBeli($kode);
		  $dStok = $this->Model_usulanBeli->getStokUsulanBeli($kode);
		  $dGIT = $this->Model_usulanBeli->getGITUsulanBeli($kode);
		  $dAMS3 = $this->Model_usulanBeli->getAMS3UsulanBeli($kode);
		  $dLimitP = $this->Model_usulanBeli->getLimitPrins($kode);
		  $dVBeli = $this->Model_usulanBeli->getNilaiBeliUsulanBeli($kode);
		  $HBC = (!empty($harga->Hrg_Beli_Cab) ? $harga->Hrg_Beli_Cab : "");
		  $HBP = (!empty($harga->Hrg_Beli_Pst) ? $harga->Hrg_Beli_Pst : "");
		  $DBC = (!empty($harga->Dsc_Beli_Cab) ? $harga->Dsc_Beli_Cab : "");
		  $DBP = (!empty($harga->Dsc_Beli_Pst) ? $harga->Dsc_Beli_Pst : "");
		  $STK = (!empty($dStok->UnitStok) ? $dStok->UnitStok : "");
		  $GIT = (!empty($dGIT->QtyGIT) ? $dGIT->QtyGIT : "");
		  $AMS3 = (!empty($dAMS3->Unit) ? $dAMS3->Unit : "");
		  $LmtP = (!empty($dLimitP->xlimit) ? $dLimitP->xlimit : "");
		  $vBeli = (!empty($dVBeli->vBeli) ? $dVBeli->vBeli : "");
		  $output = array(
		          "Data" => $data,
		          "GIT" => $GIT,
		          "AMS3" => $AMS3,
		          "LmtP" => $LmtP,
		          "vBeli" => $vBeli,
		          "STK" => $STK,
		          "HBC" => $HBC,
		          "HBP" => $HBP,
		          "DBC" => $DBC,
		          "DBP" => $DBP
		       );
		  echo json_encode($output);
		}
		else 
		{  
		  redirect("auth/logout");
		}
	}

	public function saveDataUsulanBeli()
	{
		if ( $this->logged ) 
		{	
			$params = (object)$this->input->post();
			$dir = 'Prinsipal';
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

            if(!empty($_FILES['Dokumen2']['name']))
            {
            	$time2 = date('Y-m-d_H-i-s');
                $temp2= $_FILES['Dokumen2']['tmp_name'];
                $explode2 = explode(".", $_FILES['Dokumen2']['name']);
                $name2 = $explode2[0].'_'.$time2.".".$explode2[1];
                $target2 = $path.$name2;
                move_uploaded_file($temp2, $target2);
            }

            $valid = $this->Model_usulanBeli->saveData($params, $name1, $name2);

			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function getLimit()
	{
		if ( $this->logged ) 
		{
			if ($_POST) 
			{
				$q = $this->Model_usulanBeli->getLimit($_POST['cab']);
				$q2 = $this->Model_usulanBeli->getTotalPO($_POST['cab']);

				$limit = (!empty($q->limit) ? $q->limit : "0");
				$po = (!empty($q2->po) ? $q2->po : "0");

				$output = array(
							"limit" => $limit,
							"po" => $po
 					);
			echo json_encode($output);
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function dataUsulanBeli()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datausulanbeli.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataUsulanBeli($cek = null)
	{	
		if($cek=='all')
		{
			$list = $this->Model_usulanBeli->listData()->result();
		}else
		{
			$list = $this->Model_usulanBeli->listDataOut()->result();			
		}
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->noUsulan) ? $key->noUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Value_Usulan ) ? $key->Value_Usulan  : "");			
 			$row[] = (!empty($key->status) ? $key->status : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->noUsulan."'".')" id="View"><i class="fa fa-eye"></i> View</a>'; 			
			
			if ($key->statusPusat == 'Gagal') {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$key->noUsulan."'".')"><i class="fa fa-check"></i> Proses</a>';
			}
			else
			{
				$row[] = 'Berhasil';
			}
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function dataDetailUsulan()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_usulanBeli->dataUsulan($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function prosesDataUsulanBeli()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_usulanBeli->prosesData($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function updateDataPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_usulanBeli->updateDataPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function saveusulanretur()
	{
		if ( $this->logged ) 
		{	
			$params = (object)$this->input->post();
			// log_message('error',print_r($params,true));
			// return;
			$dokumen = [];
			$dir = 'Retur';
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
                array_push($dokumen,$name1);
            }

            if(!empty($_FILES['Dokumen2']['name']))
            {
            	$time2 = date('Y-m-d_H-i-s');
                $temp2= $_FILES['Dokumen2']['tmp_name'];
                $explode2 = explode(".", $_FILES['Dokumen2']['name']);
                $name2 = $explode2[0].'_'.$time2.".".$explode2[1];
                $target2 = $path.$name2;
                move_uploaded_file($temp2, $target2);
                array_push($dokumen,$name2);
            }
            $cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$respon = true;
					foreach ($params->Produk as $key => $value){
						$produk = explode("~", $value)[0];
						$BatchNo = explode("~", $params->Batch[$key])[0];
						$BatchDoc = $params->batchdoc[$key];
						$banyak = str_replace( ',', '', $params->Qty[$key]) + str_replace( ',', '', $params->Bonus[$key]);
						$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
							from trs_invsum
							where KodeProduk ='".$produk."' and Gudang ='Baik' and Tahun = '".date('Y')."' limit 1 ")->row();
						$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok'
							from trs_invdet
							where KodeProduk ='".$produk."' and Gudang ='Baik' and Tahun = '".date('Y')."' 
							group by KodeProduk  limit 1 ")->row();
						if($ceksum->UnitStok != $cekdet->UnitStok){
							$respon = false;
							break;
						}
						$cekstokdetail = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
							from trs_invdet 
							where KodeProduk ='".$produk."' and 
							      BatchNo='".$BatchNo."' and 
							      NoDokumen ='".$BatchDoc."' and 
							      Tahun = '".date('Y')."' and
							      Cabang ='".$this->cabang."' and 
							      Gudang ='Baik' limit 1")->row();
						if(($banyak*-1) > $cekstokdetail->UnitStok){
							$respon = false;
							break;
						}
					}
					if($respon == true){
						$respon = $this->Model_retur_beli->savedata($params, $name1, $name2);
					}else{
						$respon = false;
					}
            		echo json_encode($respon);
				}else{
					$respon =false;
					echo json_encode($respon);
				}
            }else if($cektglstok == 0){
            	$respon =false;
				echo json_encode($respon);
			}
            // Upload dokumen ke server pusat
            // if ($status){
            // 	$remoteFile = $remoteFile='/var/www/html/sstcabang/assets/dokumen/bpb/';
            // 	foreach ($dokumen as $value) {
            // 		$this->upload_file->sftp($path.$value,$remoteFile.$value);
            // 	}
            // }
			// echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}
	public function msaveusulanretur()
	{
		if ( $this->logged ) 
		{	
			$params = (object)$this->input->post();
			// log_message('error',print_r($params,true));
			// return;
			$dokumen = [];
			$dir = 'Retur';
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
                array_push($dokumen,$name1);
            }

            if(!empty($_FILES['Dokumen2']['name']))
            {
            	$time2 = date('Y-m-d_H-i-s');
                $temp2= $_FILES['Dokumen2']['tmp_name'];
                $explode2 = explode(".", $_FILES['Dokumen2']['name']);
                $name2 = $explode2[0].'_'.$time2.".".$explode2[1];
                $target2 = $path.$name2;
                move_uploaded_file($temp2, $target2);
                array_push($dokumen,$name2);
            }
			// log_message('error',print_r($params,true));
			// return;

            // $valid = $this->Model_usulanBeli->saveDataByPrinsipal($params, $name1, $name2);
            $cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$respon = $this->Model_retur_beli->msavedata($params, $name1, $name2);
            		echo json_encode($respon);
				}else{
					$respon =false;
					echo json_encode($respon);
				}
            }else if($cektglstok == 0){
            	$respon =false;
				echo json_encode($respon);
			}
            // Upload dokumen ke server pusat
            // if ($status){
            // 	$remoteFile = $remoteFile='/var/www/html/sstcabang/assets/dokumen/bpb/';
            // 	foreach ($dokumen as $value) {
            // 		$this->upload_file->sftp($path.$value,$remoteFile.$value);
            // 	}
            // }
			// echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function uploadData($no){

	}

	public function databkb()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/databkb.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataBKB(){
		$list = $this->Model_retur_beli->listDataBKB()->result();
        $data = array();
        $i = 0;
		foreach ($list as $key) {
			$row = array();
			$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.$key->NoDokumen.'"></p>';
			$row[] = (!empty($key->Tipe) ? $key->Tipe : "");
			$row[] = (!empty($key->Tipe_BKB) ? $key->Tipe_BKB : "");
			$row[] = (!empty($key->NoDokumen) ? $key->NoDokumen : "");
 			$row[] = (!empty($key->NoUsulan) ? $key->NoUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Total) ? $key->Total  : "");
 			$row[] = (!empty($key->Status) ? $key->Status : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->NoDokumen."'".')" id="View"><i class="fa fa-eye"></i> View</a>';
			$row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->NoDokumen."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>';
			if ($key->statusPusat == 'Gagal' | $key->statusPusat == '') {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$key->NoDokumen."'".')"><i class="fa fa-check"></i> Proses</a>';
			}
			else
			{
				$row[] = 'Berhasil';
			}

			$row[] = 'Berhasil';
		
			$data[] = $row;
			$i++;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function listDataBKB2(){
		$list = $this->Model_retur_beli->listDataBKB2()->result();
		echo json_encode($list);
	}

	public function updateDataBKBPusat()
    {
        if ($this->logged) 
        {
            $status = $this->Model_retur_beli->updateDataBKBPusat();
            echo json_encode(array("status" => TRUE, "ket" => $status));
        }
        else 
        {   
            redirect("main/auth/logout");
        }
    }

}