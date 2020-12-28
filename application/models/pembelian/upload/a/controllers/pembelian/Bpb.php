<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bpb extends CI_Controller {

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
		$this->load->model('pembelian/Model_bpb');
		$this->load->library('format');
		$this->load->library('upload_file');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	// public function buatbpb()
	// {	
	// 	if ( $this->logged) 
	// 	{
	// 		if($this->logs['userGroup']=='Admin' || $this->logs['userGroup']=='Cabang' || $this->logs['userGroup']=='CabangLogistik')
	// 		{
	// 			$this->content['cabang']= $this->Model_main->Cabang();
	// 			$this->content['prinsipal']= $this->Model_main->Prinsipal();
	// 			$this->content['supplier']= $this->Model_main->Supplier();
	// 			$this->content['produk']= $this->Model_main->Produk();
	// 			$this->content['pr']= $this->Model_bpb->PR();
	// 			$this->twig->display('pembelian/buatbpb.html', $this->content);
	// 		}else
	// 		{
	// 			redirect("main");
	// 		}
	// 	}
	// 	else 
	// 	{
	// 		redirect("auth/logout");
	// 	}
	// }

	public function buatbpb()
	{	
		if ( $this->logged) 
		{
			if($this->logs['userGroup']=='Admin' || $this->logs['userGroup']=='Cabang' || $this->logs['userGroup']=='CabangLogistik')
			{
				$daynumber = date('d');
				if($daynumber <= 5){
					$cektglstokpusat = $this->Model_main->cek_tglstoktranspusat();
				}else{
					$cektglstokpusat = 1;
				}
				$cektglstok = $this->Model_main->cek_tglstoktrans();
				$dailystok  = $this->Model_main->cek_tglstokdaily();
				$date = date('Y-m-d');
				if($cektglstok == 1 and $cektglstokpusat == 1 ){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$this->content['cabang']= $this->Model_main->Cabang();
						$this->content['prinsipal']= $this->Model_main->Prinsipal();
						$this->content['supplier']= $this->Model_main->Supplier();
						$this->content['produk']= $this->Model_main->Produk();
						$this->content['pr']= $this->Model_bpb->PR();
						$this->twig->display('pembelian/buatbpb.html', $this->content);
					}else{
						redirect("main");
					}
				}else if($cektglstok == 0){
					redirect("main");
				}
			}else
			{
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	// public function buatbpb_cabang()
	// {	
	// 	if ( $this->logged) 
	// 	{
	// 		if($this->logs['userGroup']=='Admin' || $this->logs['userGroup']=='Cabang' || $this->logs['userGroup']=='CabangLogistik')
	// 		{
	// 			$this->content['cabang']= $this->Model_main->Cabang();
	// 			$this->content['prinsipal']= $this->Model_main->Prinsipal();
	// 			$this->content['supplier']= $this->Model_main->Supplier();
	// 			$this->content['produk']= $this->Model_main->Produk();
	// 			$this->content['pr']= $this->Model_bpb->PRPO_cabang();
	// 			$this->twig->display('pembelian/buatbpb_cabang.html', $this->content);
	// 		}else
	// 		{
	// 			redirect("main");
	// 		}
	// 	}
	// 	else 
	// 	{
	// 		redirect("auth/logout");
	// 	}
	// }

	public function buatbpb_cabang()
	{	
		if ( $this->logged) 
		{
			if($this->logs['userGroup']=='Admin' || $this->logs['userGroup']=='Cabang' || $this->logs['userGroup']=='CabangLogistik')
			{
				$cektglstok = $this->Model_main->cek_tglstoktrans();
				$dailystok  = $this->Model_main->cek_tglstokdaily();
				$date = date('Y-m-d');
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$this->content['cabang']= $this->Model_main->Cabang();
						$this->content['prinsipal']= $this->Model_main->Prinsipal();
						$this->content['supplier']= $this->Model_main->Supplier();
						$this->content['produk']= $this->Model_main->Produk();
						$this->content['pr']= $this->Model_bpb->PRPO_cabang();
						$this->twig->display('pembelian/buatbpb_cabang.html', $this->content);
					}else{
						redirect("main");
					}
				}else if($cektglstok == 0){
					redirect("main");
				}
			}else
			{
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getprpo()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_bpb->getPRPO($no);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function getprpo_cabang()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_bpb->getPRPO_cabang($no);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function getCounterBPB()
	{
		if ($this->logged) 
		{
			$cab = $_POST['cab'];
			$data = $this->Model_bpb->getCounterBPB($cab);
			$tgl = date('dmy');
			
			// echo json_encode($data);
			echo json_encode(array("counter" => $data, "tgl" =>$tgl));
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function getbpbpr()
	{
		if ($this->logged) 
		{	
			$no = $_POST['no'];
			$data = $this->Model_bpb->getBPBPR($no);
			
			echo json_encode($data);
		}
		else 
		{
			redirect("main/auth/logout");
		}
	}

	public function saveDataBPB()
	{
		if ( $this->logged ) 
		{	
			$params = (object)$this->input->post();
			if (!file_exists(getcwd().'/assets/dokumen/bpb')) {
			    mkdir(getcwd().'/assets/dokumen/bpb', 0777, true);
			}
			$path = getcwd().'/assets/dokumen/bpb/';
			$name1 = "";
			$name2 = "";
			$dokumen = [];

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
            		$no = $this->Model_bpb->saveData($params, $name1, $name2);
		            if($no){
		            	$valid = $this->Model_bpb->setStok($params, $no);
		            	$status = $this->Model_bpb->prosesData($no);
		            	$respon =true;
		            }else{
		            	$respon =false;
		            }
		            if ($status){
		            	$remoteFile = $remoteFile='/var/www/html/sstpusat/assets/dokumen/bpb/';
		            	foreach ($dokumen as $value) {
		            		$this->upload_file->sftp($path.$value,$remoteFile.$value);
		            	}
		            }
		            echo json_encode(array("status" => $respon,"nobpb" =>$no));
				}else{
					$respon =false;
					echo json_encode($respon);
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

	public function saveDataBPBCabang()
	{
		if ( $this->logged ) 
		{	
			$params = (object)$this->input->post();
			if (!file_exists(getcwd().'/assets/dokumen/bpb')) {
			    mkdir(getcwd().'/assets/dokumen/bpb', 0777, true);
			}
			$path = getcwd().'/assets/dokumen/bpb/';
			$name1 = "";
			$name2 = "";
			$dokumen = [];

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
					$no = $this->Model_bpb->saveDataBPBCabang($params, $name1, $name2);
		            $status = $this->Model_bpb->prosesDataCabang($no);
		            if ($status){
		            	$remoteFile = $remoteFile='/var/www/html/sstcabang/assets/dokumen/bpb/';
		            	foreach ($dokumen as $value) {
		            		$this->upload_file->sftp($path.$value,$remoteFile.$value);
		            	}
		            }
					echo json_encode(array("status" => $status,"nobpb" =>$no));
				}else{
					$respon =false;
					echo json_encode($respon);
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

	public function dataBPB()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/databpb.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataBPB()
	{	
		$list = $this->Model_bpb->listData()->result();
        $data = array();
        $i = 0;
		foreach ($list as $key) {
			$row = array();
			$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.$key->NoDokumen.'"></p>';
			$row[] = '<a style="cursor:pointer" title="Cek Status Pusat" onclick="cek('."'".$key->NoDokumen."'".')" id="cekpusat">'.$key->NoDokumen.'</a>';
			// $row[] = (!empty($key->NoDokumen) ? $key->NoDokumen : "");
			$row[] = (!empty($key->TglDokumen) ? $key->TglDokumen : "");
			$row[] = (!empty($key->NoPR) ? $key->NoPR : "");
			$row[] = (!empty($key->NoPO) ? $key->NoPO : "");
 			// $row[] = (!empty($key->NoUsulan) ? $key->NoUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->NoSJ) ? $key->NoSJ : "");
 			$row[] = (!empty($key->NoBEX) ? $key->NoBEX : "");
 			$row[] = (!empty($key->Total) ? number_format($key->Total,2)  : "");			
 			$row[] = (!empty($key->Status) ? $key->Status : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->NoDokumen."'".')" id="View"><i class="fa fa-eye"></i> View</a>'; 			
			// $row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->NoDokumen."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>'; 
			if($key->flag_suratjalan == 'N'){
				$row[] = 'Cabang';
			}else{
				$row[] = 'Dropping';
			}
			
			if ($key->statusPusat == 'Gagal' | $key->statusPusat == '') {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$key->NoDokumen."'".')"><i class="fa fa-check"></i> Proses</a>';
			}
			else
			{
				$row[] = 'Berhasil';
			}
		
			$data[] = $row;
			$i++;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}	

	public function dataDetailBPB()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_bpb->dataBPB($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function prosesDataBPB()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_bpb->prosesData($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function prosesDataBPBCabang()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_bpb->prosesDataCabang($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function updateDataBPBPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_bpb->updateDataBPBPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listNoBPB(){
		$valid = $this->Model_bpb->listNoBPB();
		echo json_encode($valid);
	}

	public function buatbpb_retur(){
		$cektglstok = $this->Model_main->cek_tglstoktrans();
		$dailystok  = $this->Model_main->cek_tglstokdaily();
		$date = date('Y-m-d');
		if($cektglstok == 1){
			if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
				$this->content['cabang']= $this->Model_main->Cabang();
				$this->twig->display('pembelian/buatbpb_retur.html', $this->content);
			}else{
				redirect("main");
			}
		}else if($cektglstok == 0){
			redirect("main");
		}
	}

	public function dataDetailBKB(){
		$params = (object)$this->input->post();
		$no = $params->no;
		$query = $this->Model_bpb->dataDetailBKB($no);
		echo json_encode($query);
	}

	public function saveDataTerimaRetur(){
		$cektglstok = $this->Model_main->cek_tglstoktrans();
		$dailystok  = $this->Model_main->cek_tglstokdaily();
		$date = date('Y-m-d');
		if($cektglstok == 1){
			if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
				$params = (object)$this->input->post();
				$query = $this->Model_bpb->saveDataTerimaRetur($params);
				echo json_encode($query);
			}else{
				$respon =false;
				echo json_encode($respon);
			}
		}else if($cektglstok == 0){
			$respon =false;
			echo json_encode($respon);
		}
	}
	public function viewbpppusat()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_bpb->viewbpppusat($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function revisi_bpb()
	{	
		if ( $this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['bpbnumber']= $this->Model_bpb->GetBPBNumber();
			$this->twig->display('pembelian/revisi_bpb.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function get_bpb_revisi(){
		if ( $this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_bpb->load_bpb_revisi($no);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function update_bpb(){
		if ( $this->logged) 
		{
			$params = (object)$this->input->post();
			$data = $this->Model_bpb->update_bpb($params);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function datacndnbelicabang(){
		if ( $this->logged){
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/datacndnbelicabang.html', $this->content);
		}else{
			redirect("main/auth/logout");
		}
	}
	public function list_data_cndnBelicabang($cek = null){
		$data = [];
		$output=[];

		$bySearch = "";
		$byLimit = "";
		$draw = "";
		if (isset($_REQUEST['draw'])) {
			$draw=$_REQUEST['draw'];
		}
		$length = "";
        if(isset($_REQUEST['length'])) {
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
			$total = $this->Model_bpb->list_data_cndnBelicabang('','','')->num_rows();
		else
			$total = $this->Model_bpb->list_data_cndnBelicabang('','','Open')->num_rows();

		$output['recordsTotal']=$output['recordsFiltered']=$total;
		$output['data']=[];

		if($search!=""){
			$bySearch = " and (NoFaktur like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Cabang like '%".$search."%' or TipeDokumen like '%".$search."%' or TipeFaktur like '%".$search."%' or NoAcuDokumen like '%".$search."%' or Value like '%".$search."%' or Status like '%".$search."%')";
		}

		if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;

		if($cek=='all')
				$query=$this->Model_bpb->list_data_cndnBelicabang($bySearch,$byLimit,'')->result();
			else
				$query=$this->Model_bpb->list_data_cndnBelicabang($bySearch, $byLimit, "Open")->result();
		
		if($search!=""){			
			if($cek=='all')
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_bpb->list_data_cndnBelicabang($bySearch,'','')->num_rows();
			else
				$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_bpb->list_data_cndnBelicabang($bySearch,'', "Open")->num_rows();
		}

		$x = 0;
		$i = 0;
		foreach ($query as $baris) {
				$x++;
				$row = array();

				$row[] = $x;
				$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.(!empty($baris->NoFaktur) ? $baris->NoFaktur : "" ).'"></p>';
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
				$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
	 			$row[] = (!empty($baris->tanggal) ? $baris->tanggal : "");
	 			$row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
	 			$row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
	 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen :"");
	 			$row[] = (!empty($baris->TipeFaktur) ? $baris->TipeFaktur : "");
	 			$row[] = (!empty($baris->NoAcuDokumen) ? $baris->NoAcuDokumen : "-");
	 			$row[] = (!empty($baris->Value) ? $baris->Value : 0);
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->Prinsipal."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$data[] = $row;
				$i++;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);

	}

	public function detail_cndnbeli_cabang(){
		$output=[];
		$no = $_POST['no'];
		$query=$this->Model_bpb->detail_cndnbeli_cabang($no)->result();
		$output['data'] = $query;
		echo json_encode($output);
	}

	public function cetakcndnbelicabang(){
		$no = $_POST['no'];
		$query=$this->Model_bpb->cetakcndnbelicabang($no);
		echo json_encode($query);
	}

	public function updateDatacndnbeliPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_bpb->updateDatacndnbeliPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
}