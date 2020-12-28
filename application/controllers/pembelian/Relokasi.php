<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Relokasi extends CI_Controller {

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
		$this->load->model('pembelian/Model_relokasi');
		$this->load->library('format');
		$this->load->library('upload_file');
		$this->logs = $this->session->all_userdata();
		$this->cabang = $this->session->userdata('cabang');
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

// START USULAN RELOKASI
	public function buatrelokasi()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Relokasi Cabang');
	        $this->content['no'] = $no;
			$this->content['cabang']= $this->Model_main->Cabang();
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$this->twig->display('pembelian/buatrelokasi.html', $this->content);
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

	public function dataterimarelokasi()
	{	
		if ( $this->logged) 
		{
			// $no = $this->Model_main->noDokumen('Relokasi Cabang');
	  //       $this->content['no'] = $no;
	  		$cektglstok = $this->Model_main->cek_tglstoktrans();
	  		$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
	  		if($cektglstok == 1){
	  			if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
	  				$this->content['cabang']= $this->Model_main->Cabang();
					$this->twig->display('pembelian/dataterimarelokasi.html', $this->content);
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

	public function getDetailUsulanRelokasi()
	{
		if ($this->logged) 
		{
			$kode = $_POST['kode'];
			$data = $this->Model_relokasi->getDetailUsulanRelokasi($kode);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function saveUsulanRelokasi()
	{
		if ( $this->logged ) 
		{	
			$valid = false;
			$params = (object)$this->input->post();

			$dir = 'Relokasi';
			if (!file_exists(getcwd().'/assets/dokumen/'.$dir)) {
			    mkdir(getcwd().'/assets/dokumen/'.$dir, 0777, true);
			}
			$path = getcwd().'/assets/dokumen/'.$dir.'/';
			$remoteFile='/var/www/html/sstcabang/assets/dokumen/'.$dir.'/';
			$name1 = "";
			$name2 = "";

			if(!empty($_FILES['Dokumen1']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen1']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen1']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }
            $respon = true;
            $cektglstok = $this->Model_main->cek_tglstoktrans();
            $dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					foreach ($params->kode_produk as $key => $value){
						$produk = $params->kode_produk[$key];
						$batchNo = explode("~", $params->batch[$key])[0];
						$BatchDoc = $params->BatchDoc[$key];
						$banyak = $params->Qty[$key] + $this->format->number_unformat($params->Bonus[$key]);
						$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
							from trs_invsum
							where KodeProduk ='".$produk."' and Gudang ='Baik' and Tahun = '".date('Y')."' limit 1 ")->row();
						$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok'
							from trs_invdet
							where KodeProduk ='".$produk."' and Gudang ='Baik' and Tahun = '".date('Y')."' 
							group by KodeProduk limit 1 ")->row();
						if($ceksum->UnitStok != $cekdet->UnitStok){
							$respon = false;
							break;
						}
						$cekstokdetail = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
							from trs_invdet 
							where KodeProduk ='".$produk."' and 
							      BatchNo='".$batchNo."' and 
							      NoDokumen ='".$BatchDoc."' and 
							      Tahun = '".date('Y')."' and
							      Cabang ='".$this->cabang."' and 
							      Gudang ='Baik' limit 1")->row();
						if($banyak > $cekstokdetail->UnitStok){
							$respon = false;
							break;
						}
					}
					if($respon == true){
						$no = $this->Model_relokasi->saveData($params, $name1);
						if($no){
							echo json_encode(array("status" => TRUE));
						}else{
							echo json_encode(array("status" => FALSE));
						}
					}else{
						echo json_encode(array("status" => FALSE));
					}
					
				}else{
					$respon =false;
					echo json_encode(array("status" =>$respon));
				}
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode($respon);
			}
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function relokasiusulan()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/datarelokasiusulan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	
// FINISH USULAN RELOKASI

// START KIRIMAN RELOKASI
	public function buatkirimanrelokasi()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Relokasi Cabang');
	        $this->content['no'] = $no;
			$this->twig->display('pembelian/buatkirimanrelokasi.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function cabangRelokasi()
	{
		if ($this->logged) 
		{
			$data = $this->Model_relokasi->cabangRelokasi();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function produkRelokasi()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$produk = $this->Model_relokasi->produkRelokasi($no);
			echo json_encode($produk);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getDetailProdukRelokasi()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$kode = $_POST['kode'];
			$produk = $this->Model_relokasi->getDetailProdukRelokasi($no, $kode);
			echo json_encode($produk);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataTerimaRelokasi($cek = null)
	{	
		$list = $this->Model_relokasi->listDataTerimaRelokasi();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->Cabang_Pengirim) ? $key->Cabang_Pengirim : "");
 			$row[] = (!empty($key->Cabang_Penerima) ? $key->Cabang_Penerima : "");
 			$row[] = (!empty($key->No_Terima) ? $key->No_Terima : "");
 			$row[] = (!empty($key->No_Relokasi) ? $key->No_Relokasi : "");
 			$row[] = (!empty($key->No_DO) ? $key->No_DO : "");
 			$row[] = (!empty($key->Tgl_kirim) ? $key->Tgl_kirim : "");
 			$row[] = (!empty($key->Status_kiriman) ? $key->Status_kiriman : "");
 			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="View" onclick="view('."'".$key->No_Terima."'".')" id="View"><i class="fa fa-eye"></i>&nbsp;&nbsp;View Detail&nbsp;&nbsp;</a>';
 			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Cetak" onclick="cetak('."'".$key->No_Terima."'".')" id="View"><i class="fa fa-eye"></i>&nbsp;&nbsp;Print&nbsp;&nbsp;</a>'; 			
			if ($key->Status_kiriman == 'Pending') {
				//add html for action	
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->No_Terima."'".','."'Approve'".')"><i class="fa fa-check"></i> Approve</a>
				<a class="btn btn-sm btn-warning" href="javascript:void(0)" title="Reject" onclick="RejectPenerima('."'".$key->No_Terima."'".')"><i class="fa fa-check"></i> Reject</a>';
				// $row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="approveData('."'".$key->No_Terima."'".','."'Reject'".')"><i class="fa fa-times"></i> Reject</a>'; 				
				
			}
			else
			{
				$row[] = '-';
				$row[] = '-';
				$row[] = '-';
			}

		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function updateDataRelokasiDOPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->updateDataRelokasiDOPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataDetailRelokasiKirim()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_relokasi->dataDetailRelokasiKirim($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataDetailRelokasiTerima()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_relokasi->dataDetailRelokasiTerima($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}



	public function EditDataRelokasiterima()
	{
		if ( $this->logged ) 
		{	
			$valid = false;
			$params = (object)$this->input->post();
			$no_terima = $params->no_terima;
			$db = $this->db->query("select * from trs_relokasi_terima_header where no_terima = '".$no_terima."' and Status_kiriman = 'Terima'");
			if($db->num_rows() > 0){
				$valid = false;
			}else{
				$data_detail = $params->data_detail;
            	$valid = $this->Model_relokasi->EditDataRelokasiterima($params,$data_detail);
			}
			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function datarelokasikiriman()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/datarelokasikiriman.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function load_datarelokasikiriman()
	{	
		$data = array();
        $bySearch = "";
        $byLimit = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_relokasi->load_datarelokasikiriman()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (No_Relokasi like '%".$search."%' or Tgl_kirim like '%".$search."%' or Cabang_Pengirim like '%".$search."%' or Cabang_Penerima like '%".$search."%' or Prinsipal like '%".$search."%' or Status_kiriman like '%".$search."%')";
        }
        if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

		$datas = $this->Model_relokasi->load_datarelokasikiriman($bySearch, $byLimit)->result();
		$data = array();
		$no = $_POST['start'];
		foreach ($datas as $relokasi) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = '<a style="cursor:pointer" title="Cek Status Pusat" onclick="cek('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')" id="cekpusat">'.$relokasi->No_Relokasi.'</a>';
			// $row[] = (!empty($relokasi->No_Relokasi) ? $relokasi->No_Relokasi : "");
			$row[] = (!empty($relokasi->Tgl_kirim) ? $relokasi->Tgl_kirim : "");
			$row[] = (!empty($relokasi->Cabang_Pengirim) ? $relokasi->Cabang_Pengirim : "");
			$row[] = (!empty($relokasi->Cabang_Penerima) ? $relokasi->Cabang_Penerima : "");
			$row[] = (!empty($relokasi->Prinsipal) ? $relokasi->Prinsipal : "");
			$row[] = (!empty($relokasi->Gross) ? $relokasi->Gross : "");
			$row[] = (!empty($relokasi->Status_kiriman) ? $relokasi->Status_kiriman : "");
			$row[] = '<span class="btn btn-primary" onclick="view_detail('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Detail &nbsp;&nbsp;</span>';
			$row[] = '<span class="btn btn-primary" onclick="cetak('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Print &nbsp;&nbsp;</span>';
			if ($relokasi->Dokumen_2 == 'Gagal' | $relokasi->Dokumen_2 == '') {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')"><i class="fa fa-check"></i> Proses</a>';
			}
			else
			{
				$row[] = 'Berhasil';
			}
			// $row[] = "a";

			$data[] = $row;
		}	
		$output = array(
					"draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "recordsFiltered" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "data" => $data
                );

		// log_message('error',print_r($output,true));
        echo json_encode($output);
	}

	public function approvalData()
	{          
        if ($this->logged) 
		{	
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$No = $_POST['No'];
					$tipe = $_POST['tipe'];
					$valid = $this->Model_relokasi->act($tipe, $No);
					echo json_encode(array("status" => $valid));
				}else{
					$respon =false;
					echo json_encode(array("status" => $respon));
				}
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode($respon);
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function RejectPenerima()
	{          
        if ($this->logged) 
		{	
			/*$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$No = $_POST['No'];
					$tipe = $_POST['tipe'];*/
					$No = $_POST['No'];
					$valid = $this->Model_relokasi->RejectPenerima( $No);
					echo json_encode(array("status" => $valid));
				/*}else{
					$respon =false;
					echo json_encode(array("status" => $respon));
				}
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode($respon);
			}*/
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }
    
    public function updatedataRelokasipusat(){
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->updatedataRelokasipusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function prosesDataRelokasi()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$cabang = $_POST['cabang'];
			$valid = $this->Model_relokasi->prosesDataRelokasi($No,$cabang);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

     public function ApprovedataRelokasipusatReject(){
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->ApprovedataRelokasipusatReject();
			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

    public function updatedataRelokasipusatReject(){
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->updatedataRelokasipusatReject();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function datarelokasireject()
	{	
		if ( $this->logged) 
		{
			// $no = $this->Model_main->noDokumen('Relokasi Cabang');
	  //       $this->content['no'] = $no;
	  		$cektglstok = $this->Model_main->cek_tglstoktrans();
	  		$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
	  		if($cektglstok == 1){
	  			if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
	  				$this->content['cabang']= $this->Model_main->Cabang();
					$this->twig->display('pembelian/datarejectrelokasi.html', $this->content);
				}else{
					// redirect("main");
					$this->twig->display('pembelian/datarejectrelokasi.html', $this->content);
				}
				
			}else if($cektglstok == 0){
				$this->twig->display('pembelian/datarejectrelokasi.html', $this->content);
				// redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function load_datarelokasireject()
	{	
		$data = array();
        $bySearch = "";
        $byLimit = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_relokasi->load_datarelokasireject()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (No_Relokasi like '%".$search."%' or Tgl_kirim like '%".$search."%' or Cabang_Pengirim like '%".$search."%' or Cabang_Penerima like '%".$search."%' or Prinsipal like '%".$search."%' or Status_kiriman like '%".$search."%')";
        }
        if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

		$datas = $this->Model_relokasi->load_datarelokasireject($bySearch, $byLimit)->result();
		$data = array();
		$no = $_POST['start'];
		foreach ($datas as $relokasi) {
			$no++;

			if ($relokasi->Status_kiriman == 'RejectPusat' OR $relokasi->Status_kiriman == 'RejectCabPenerima' ) {
				$buttonapp = '<span class="btn btn-primary" onclick="approve('."'".$relokasi->No_Relokasi."'".')">&nbsp;&nbsp; Approve &nbsp;&nbsp;</span>';
			}else{
				$buttonapp = "-";
			}

			$row = array();
			$row[] = $no;
			$row[] = '<a style="cursor:pointer" title="Cek Status Pusat" onclick="cek('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')" id="cekpusat">'.$relokasi->No_Relokasi.'</a>';
			// $row[] = (!empty($relokasi->No_Relokasi) ? $relokasi->No_Relokasi : "");
			$row[] = (!empty($relokasi->Tgl_kirim) ? $relokasi->Tgl_kirim : "");
			$row[] = (!empty($relokasi->Cabang_Pengirim) ? $relokasi->Cabang_Pengirim : "");
			$row[] = (!empty($relokasi->Cabang_Penerima) ? $relokasi->Cabang_Penerima : "");
			$row[] = (!empty($relokasi->Prinsipal) ? $relokasi->Prinsipal : "");
			$row[] = (!empty($relokasi->Gross) ? $relokasi->Gross : "");
			$row[] = (!empty($relokasi->Status_kiriman) ? $relokasi->Status_kiriman : "");
			$row[] = '<span class="btn btn-primary" onclick="view_detail('."'".$relokasi->No_Relokasi."'".')">&nbsp;&nbsp; Detail &nbsp;&nbsp;</span>';
			$row[] = $buttonapp;
			if ($relokasi->Dokumen_2 == 'Gagal' | $relokasi->Dokumen_2 == '') {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')"><i class="fa fa-check"></i> Proses</a>';
			}
			else
			{
				$row[] = 'Berhasil';
			}
			// $row[] = "a";

			$data[] = $row;
		}	
		$output = array(
					"draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "recordsFiltered" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "data" => $data
                );

		// log_message('error',print_r($output,true));
        echo json_encode($output);
	}

	public function datarelokasikirimanPusat()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/datarelokasikirimanPusat.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function load_datarelokasikirimanPusat()
	{	
		$data = array();
        $bySearch = "";
        $byLimit = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_relokasi->load_datarelokasikirimanPusat()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (No_Relokasi like '%".$search."%' or Tgl_kirim like '%".$search."%' or Cabang_Pengirim like '%".$search."%' or Cabang_Penerima like '%".$search."%' or Prinsipal like '%".$search."%' or Status_kiriman like '%".$search."%')";
        }
        if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

		$datas = $this->Model_relokasi->load_datarelokasikirimanPusat($bySearch, $byLimit)->result();
		$data = array();
		$no = $_POST['start'];
		foreach ($datas as $relokasi) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = '<a style="cursor:pointer" title="Cek Status Pusat" onclick="cek('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')" id="cekpusat">'.$relokasi->No_Relokasi.'</a>';
			// $row[] = (!empty($relokasi->No_Relokasi) ? $relokasi->No_Relokasi : "");
			$row[] = (!empty($relokasi->Tgl_kirim) ? $relokasi->Tgl_kirim : "");
			$row[] = (!empty($relokasi->Cabang_Pengirim) ? $relokasi->Cabang_Pengirim : "");
			$row[] = (!empty($relokasi->Cabang_Penerima) ? $relokasi->Cabang_Penerima : "");
			$row[] = (!empty($relokasi->Prinsipal) ? $relokasi->Prinsipal : "");
			$row[] = (!empty($relokasi->Gross) ? $relokasi->Gross : "");
			$row[] = (!empty($relokasi->Status_kiriman) ? $relokasi->Status_kiriman : "");
			$row[] = (!empty($relokasi->Keterangan) ? $relokasi->Keterangan : "");
			$row[] = '<span class="btn btn-primary" onclick="view_detail('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Detail &nbsp;&nbsp;</span>';
			$row[] = '<span class="btn btn-primary" onclick="cetak('.$relokasi->No_Relokasi.')">&nbsp;&nbsp; Print &nbsp;&nbsp;</span>';
			if ($relokasi->Dokumen_2 == 'Gagal' | $relokasi->Dokumen_2 == '') {/*
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$relokasi->No_Relokasi."'".','."'".$relokasi->Cabang."'".')"><i class="fa fa-check"></i> Proses</a>';*/
				$row[] = '-';
			}
			else
			{
				$row[] = 'Berhasil';
			}
			// $row[] = "a";

			$data[] = $row;
		}	
		$output = array(
					"draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "recordsFiltered" => $this->Model_relokasi->load_datarelokasikiriman()->num_rows(),
                    "data" => $data
                );

		// log_message('error',print_r($output,true));
        echo json_encode($output);
	}

	
    public function updatedataRelokasiKirimanPusat(){
		if ($this->logged) 
		{
			$status = $this->Model_relokasi->updatedataRelokasiKirimanPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
}