<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usulanbeli extends CI_Controller {

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
		$this->load->model('pembelian/Model_closing');
		$this->load->library('format');
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

	public function usulanBeli()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['prinsipal']= $this->Model_main->Prinsipal();
			$this->content['supplier']= $this->Model_main->Supplier();
			$this->content['produk']= $this->Model_main->Produk();
			$this->content['pelanggan']= $this->Model_main->Pelanggan();
			$this->twig->display('pembelian/usulanBeli.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

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

	public function usulanBeliBonusPrinsipal()
	{	
		if ( $this->logged ) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->content['prinsipal']= $this->Model_main->Prinsipal();
			$this->content['supplier']= $this->Model_main->Supplier();
			$this->content['produk']= $this->Model_main->Produk();
			$this->content['pelanggan']= $this->Model_main->Pelanggan();
			$this->twig->display('pembelian/usulanBeliBonus_bak.html', $this->content);
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

	public function getProdukUsulanBeli($kode =NULL)
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
		  $AMS3 = (!empty($dAMS3->avgjual) ? $dAMS3->avgjual : "");
		  $LmtP = (!empty($dLimitP->xlimit) ? $dLimitP->xlimit : "");
		  $vBeli = (!empty($dVBeli->vBeli) ? $dVBeli->vBeli : "");
		  $DSC = (!empty($infdet->Disc) ? $infdet->Disc : "");
		  $QTYPO = (!empty($infdet->QTYPO) ? $infdet->QTYPO : "");
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
		          "DBP" => $DBP,
		       );
		  echo json_encode($output);
		}
		else 
		{  
		  redirect("auth/logout");
		}
	}

	// public function getProdukUsulanBelix()
	// {
	// 	if ( $this->logged ) 
	// 	{  
	// 		// $kode = $_GET['kode'];
	// 		$kode = $_POST['kode'];
	// 		$nodo = $_POST['nodo'];
	// 	  $data = $this->Model_usulanBeli->getProdukUsulanBeli($kode);
	// 	  $infdet = $this->Model_usulanBeli->getitemusulaninfo($kode,$nodo);
	// 	  $harga = $this->Model_usulanBeli->getHargaUsulanBeli($kode);
	// 	  $dStok = $this->Model_usulanBeli->getStokUsulanBeli($kode);
	// 	  $dGIT = $this->Model_usulanBeli->getGITUsulanBeli($kode);
	// 	  $dAMS3 = $this->Model_usulanBeli->getAMS3UsulanBeli($kode);
	// 	  $dLimitP = $this->Model_usulanBeli->getLimitPrins($kode);
	// 	  $dVBeli = $this->Model_usulanBeli->getNilaiBeliUsulanBeli($kode);
	// 	  $HBC = (!empty($harga->Hrg_Beli_Cab) ? $harga->Hrg_Beli_Cab : "");
	// 	  $HBP = (!empty($harga->Hrg_Beli_Pst) ? $harga->Hrg_Beli_Pst : "");
	// 	  $DBC = (!empty($harga->Dsc_Beli_Cab) ? $harga->Dsc_Beli_Cab : "");
	// 	  $DBP = (!empty($harga->Dsc_Beli_Pst) ? $harga->Dsc_Beli_Pst : "");
	// 	  $STK = (!empty($dStok->UnitStok) ? $dStok->UnitStok : "");
	// 	  $GIT = (!empty($dGIT->QtyGIT) ? $dGIT->QtyGIT : "");
	// 	  $AMS3 = (!empty($dAMS3->avgjual) ? $dAMS3->avgjual : "");
	// 	  $LmtP = (!empty($dLimitP->xlimit) ? $dLimitP->xlimit : "");
	// 	  $vBeli = (!empty($dVBeli->vBeli) ? $dVBeli->vBeli : "");
	// 	  $DSC = (!empty($infdet->Disc) ? $infdet->Disc : "");
	// 	  $QTYPO = (!empty($infdet->QTYPO) ? $infdet->QTYPO : "");
	// 	  $output = array(
	// 	          "Data" => $data,
	// 	          "GIT" => $GIT,
	// 	          "AMS3" => $AMS3,
	// 	          "LmtP" => $LmtP,
	// 	          "vBeli" => $vBeli,
	// 	          "STK" => $STK,
	// 	          "HBC" => $HBC,
	// 	          "HBP" => $HBP,
	// 	          "DBC" => $DBC,
	// 	          "DBP" => $DBP,
	// 	          "DSC" => $DSC,
	// 	          "QTYPO" => $QTYPO
	// 	       );
	// 	  echo json_encode($output);
	// 	}
	// 	else 
	// 	{  
	// 	  redirect("auth/logout");
	// 	}
	// }

	public function getprinsipallimit()
	{
		if ( $this->logged ) 
		{ 
		  $ext = $this->input->post("Prinsipal");
		  $exp = explode("~",$ext);
		  $prinsipal =$exp[0];
		  $dLimitP = $this->Model_usulanBeli->getLimitPrinsbyprins($prinsipal);
		  $dVBeli = $this->Model_usulanBeli->getNilaiBeliUsulanBelibyPrins($prinsipal);
		  $LmtP = (!empty($dLimitP['lprins']->xlimit) ? $dLimitP['lprins']->xlimit : 0);
		  $LmtC = (!empty($dLimitP['lcab']->xlimit) ? $dLimitP['lcab']->xlimit : 0);
		  $vBeliP = (!empty($dVBeli['vBeliP']->vBeli) ? $dVBeli['vBeliP']->vBeli : "");
		  $vBeliC = (!empty($dVBeli['vBeliC']->vBeli) ? $dVBeli['vBeliC']->vBeli : "");
		  $output = array(
		          "LmtP" => $LmtP,
		          "LmtC" => $LmtC,
		          "vBeliP" => $vBeliP,
		          "vBeliC" => $vBeliC,
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
			$row[] = '<a style="cursor:pointer" title="Cek Approval" onclick="cek('."'".$key->noUsulan."'".')" id="cekpusat">'.$key->noUsulan.'</a>';
 			$row[] = (!empty($key->Tgl) ? $key->Tgl : "");
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
 			$row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->noUsulan."'".','."'".$key->Prinsipal."'".','."'".$key->Supplier."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>'; 			
		
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

	public function listProdukOrderMonitor()
	{
		if ($this->logged) 
		{
			$prinsipal = $_POST['prinsipal'];
			$ltime  = $_POST['ltime'];
			$data = $this->Model_usulanBeli->listProdukOrderMonitor($prinsipal,$ltime);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function printdataUsulan()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_usulanBeli->printDataUsulan($no);
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

	public function saveDataUsulanBeliPrinsipal()
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

            $valid = $this->Model_usulanBeli->saveDataByPrinsipal($params, $name1, $name2);

			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function saveDataUsulanBeliBonusPrinsipal()
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

            $valid = $this->Model_usulanBeli->saveDataUsulanBeliBonusPrinsipal($params, $name1, $name2);

			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	  // ==================== DATA DO PUSAT ===================
	public function datadopusat(){
		if ($this->logged) 
		{
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/datadopusat.html', $this->content);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function load_datadopusat(){	
		$data = array();
        $bySearch = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length=$_REQUEST['length'];
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_usulanBeli->load_datadopusat()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (NoDokumen like '%".$search."%' or TglDokumen like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%')";
        }
        $byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

		$datas = $this->Model_usulanBeli->load_datadopusat($bySearch, $byLimit)->result();
		$data = array();
		$no = $_POST['start'];
		foreach ($datas as $item) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = (!empty($item->TglDokumen) ? $item->TglDokumen : "");
			$row[] = (!empty($item->NoDokumen) ? $item->NoDokumen : "");
			$row[] = (!empty($item->Prinsipal) ? $item->Prinsipal : "");
			$row[] = (!empty($item->Supplier) ? $item->Supplier : "");
			$row[] = (!empty($item->Gross) ? number_format($item->Gross) : "");
			$row[] = (!empty($item->Potongan) ? number_format($item->Potongan) : "");
			$row[] = (!empty($item->Value) ? number_format($item->Value) : "");
			$row[] = (!empty($item->PPN) ? number_format($item->PPN) : "");
			$row[] = (!empty($item->BiayaKirim) ? number_format($item->BiayaKirim) : "");
			$row[] = (!empty($item->Total) ? number_format($item->Total) : "");
			$row[] = (!empty($item->TotalBiaya) ? number_format($item->TotalBiaya) : "");
			$row[] = (!empty($item->Status) ? $item->Status : "");
			$row[] = '<span class="btn btn-primary" onclick="view_detail('."'".$item->NoDokumen."'".')">&nbsp;&nbsp; Detail &nbsp;&nbsp;</span>';
			$row[] = '<span class="btn btn-primary" onclick="cetak('."'".$item->NoDokumen."'".','."'".$this->cabang."'".')">&nbsp;&nbsp; Print &nbsp;&nbsp;</span>';
			$row[] = '<span class="btn btn-success" title="Closed" onclick="closed('."'".$item->Cabang."'".','."'".$item->NoDokumen."'".','."'Closed'".')" id="closebpp">Close</span>';
			$row[] = '<span class="btn btn-success" title="Batal" onclick="batal('."'".$item->Cabang."'".','."'".$item->NoDokumen."'".','."'Batal'".')" id="closebpp">Batal</span>'; 
			$row[] = '<span class="btn btn-warning" title="Value" onclick="valDO('."'".$item->Cabang."'".','."'".$item->NoDokumen."'".','."'valdo'".')" id="closebpp">Update</span>'; 
			$data[] = $row;
		}	
		$output = array(
					"draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_usulanBeli->load_datadopusat()->num_rows(),
                    "recordsFiltered" => $this->Model_usulanBeli->load_datadopusat()->num_rows(),
                    "data" => $data
                );

        echo json_encode($output);
	}

	public function detaildopusat(){
		$no = $_GET['no'];
		$datas = $this->Model_usulanBeli->detaildopusat($no);
		echo json_encode($datas);
	}

	// public function updatedataDOBelipusat(){
	// 	if ($this->logged) 
	// 	{
	// 		$status = $this->Model_usulanBeli->updatedataDOpusat();
	// 		echo json_encode(array("status" => TRUE, "ket" => $status));
	// 	}
	// 	else 
	// 	{	
	// 		redirect("main/auth/logout");
	// 	}
	// }

	public function updatedataDOBelipusat(){
		if ($this->logged) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1 ){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y')){
					$status = $this->Model_usulanBeli->updatedataDOpusat();
					echo json_encode(array("status" => TRUE, "ket" => $status));
				}else{
					echo json_encode(array("status" => FALSE, "ket" => 'stok'));
				}
			}else if($cektglstok == 0){
				echo json_encode(array("status" => FALSE, "ket" => 'stok'));
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function viewusulanbelipusat()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_usulanBeli->viewusulanbelipusat($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listOustandinglimit()
	{
		if ($this->logged) 
		{
			$prinsipal = $_POST['prinsipal'];
			$data = $this->Model_usulanBeli->listOustandinglimit($prinsipal);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function updateAms3()
	{
		if ($this->logged) 
		{
			$valid = $this->Model_closing->Data_AMS3();
			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function closedataDO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['no'];
			$cabang = $_POST['cabang'];
			$status = $_POST['status'];
			$cekDO = $this->db->query("select * from trs_delivery_order_header
                        where NoDokumen ='".$No."' and Cabang ='".$cabang."' limit 1")->row();
			$NoPO = $cekDO->NoPO;
			if($status == 'Batal'){
				$cekbrg = $this->db->query("select * from trs_terima_barang_header
                        where NoPO ='".$NoPO."' and Cabang ='".$cabang."'")->num_rows();
				if($cekbrg > 0){
					echo json_encode(array("status" => FALSE));
				}else{
					$valid = $this->Model_usulanBeli->closedataDO($No,$cabang,$status);
					echo json_encode(array("status" => TRUE));
				}
			}elseif($status == 'Closed'){
				$valid = $this->Model_usulanBeli->closedataDO($No,$cabang,$status);
				echo json_encode(array("status" => TRUE));
			}elseif($status == 'valdo'){
				$valid = $this->Model_usulanBeli->UpdateValdataDO($No,$cabang,$status);
				echo json_encode(array("status" => TRUE));
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }
}