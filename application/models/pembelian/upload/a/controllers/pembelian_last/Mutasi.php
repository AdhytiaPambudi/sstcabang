<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mutasi extends CI_Controller {

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
		$this->load->model('pembelian/Model_mutasi');
		$this->load->library('format');
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

// START MUTASI KOREKSI
	public function mutasikoreksi()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Mutasi Koreksi');
	        $this->content['no'] = $no;
	        $cektglstok = $this->Model_main->cek_tglstoktrans();
			// $cektglsettlement = $this->Model_main->cek_tglsettlement();
			if($cektglstok == 1){
				$this->content['cabang']= $this->Model_main->Cabang();
				$this->twig->display('pembelian/mutasiKoreksi.html', $this->content);
			}else if($cektglstok == 0){
					redirect("main");
				}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getHargaProduk($kode = NULL)
	{
		if ( $this->logged ) 
		{  
		  $data = $this->Model_mutasi->getHargaProduk($kode);
		  echo json_encode($data);
		}
		else 
		{  
		  redirect("auth/logout");
		}
	}

	public function saveMutasiKoreksi()
	{
		if ( $this->logged ) 
		{
			$valid = false;
			$params = (object)$this->input->post();
			$dir = 'MutasiKoreksi';
			if (!file_exists(getcwd().'/assets/dokumen/'.$dir)) {
			    mkdir(getcwd().'/assets/dokumen/'.$dir, 0777, true);
			}
			$path = getcwd().'/assets/dokumen/'.$dir.'/';
			// $remoteFile='/var/www/html/sstcabang/assets/dokumen/'.$dir.'/';
			$name1 = "";
			// ====== Proses Upload FIle ======
			if(!empty($_FILES['Dokumen']['name']))
            {
            	$time1 = date('Y-m-d_H-i-s');
                $temp1= $_FILES['Dokumen']['tmp_name'];
                $explode1 = explode(".", $_FILES['Dokumen']['name']);
                $name1 = $explode1[0].'_'.$time1.".".$explode1[1];
                $target1 = $path.$name1;
                move_uploaded_file($temp1, $target1);
            }

            $valid = $this->Model_mutasi->saveData($params);
            // if ($no) 
            // 	$valid = $this->Model_relokasi->prosesData($no);

			echo json_encode(array("status" => $valid));

		}
		else 
		{
			redirect("auth/");
		}
	}

	public function saveMutasiBatch()
	{
		if ( $this->logged )
		{
			$valid = false;
			$params = (object)$this->input->post();
            $valid = $this->Model_mutasi->saveDataBatch($params);
			echo json_encode(array("status" => $valid));

		}
		else
		{
			redirect("auth/");
		}
	}

	public function saveMutasiGudang()
	{
		if ( $this->logged )
		{
			$valid = false;
			$params = (object)$this->input->post();
            $valid = $this->Model_mutasi->saveDataGudang($params);
			echo json_encode(array("status" => $valid));
			
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function approvalMutasiKoreksi()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/apprvMutasiKoreksi.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataApprvMutasiKoreksi()
	{	
		$list = $this->Model_mutasi->listDataApprvMutasiKoreksi();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->cabang) ? $key->cabang : "");
 			$row[] = (!empty($key->tanggal) ? $key->tanggal : "");
 			$row[] = (!empty($key->no_koreksi) ? $key->no_koreksi : "");
 			$row[] = (!empty($key->catatan ) ? $key->catatan  : "");
 			$row[] = (!empty($key->dokumen ) ? $key->dokumen  : "");
 			$row[] = (!empty($key->produk ) ? $key->produk.'~'.$key->nama_produk  : "");
 			$row[] = (!empty($key->qty ) ? $key->qty  : 0);
 			$row[] = (!empty($key->harga ) ? $key->harga  : "");
 			$row[] = (!empty($key->value ) ? $key->value  : "");
 			$row[] = (!empty($key->gudang ) ? $key->gudang  : "");
 			$row[] = (!empty($key->batch_detail ) ? $key->batch_detail  : "");
 			$row[] = (!empty($key->batch ) ? $key->batch  : "");
 			$row[] = (!empty($key->exp_date ) ? $key->exp_date  : "");
 			$row[] = (!empty($key->stok ) ? $key->stok  : "");
 			$row[] = (!empty($key->status ) ? $key->status  : "");			
				//add html for action	
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->no_koreksi."'".','."'".$key->gudang."'".','."'".$key->produk."'".','."'".$key->batch."'".','."'".$key->NoDocStok."'".')"><i class="fa fa-check"></i> Approve</a>';
				$row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="rejectData('."'".$key->no_koreksi."'".','."'".$key->gudang."'".','."'".$key->produk."'".','."'".$key->batch."'".','."'".$key->NoDocStok."'".')"><i class="fa fa-times"></i> Reject</a>'; 
		
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function apprvMutasiKoreksi()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$gudang = $_POST['gudang'];
			$produk = $_POST['produk'];
			$batch = $_POST['batch'];
			$docstok = $_POST['docstok'];
			$valid = $this->Model_mutasi->apprvMutasiKoreksi($No,$gudang,$produk,$batch,$docstok);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

	public function rejectMutasiKoreksi()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_mutasi->rejectMutasiKoreksi($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }


// START MUTASI BATCH
	public function mutasibatch()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Mutasi Batch');
	        $this->content['no'] = $no;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/mutasibatch.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}



// START MUTASI GUDANG
	public function mutasigudang()
	{	
		if ( $this->logged) 
		{
			$no = $this->Model_main->noDokumen('Mutasi Gudang');
	        $this->content['no'] = $no;
			$this->content['cabang']= $this->Model_main->Cabang();
			$this->twig->display('pembelian/mutasigudang.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datamutasikoreksi(){
		if ( $this->logged){
			$this->content['data'] = $this->Model_mutasi->listDataMutasiKoreksi();
			$this->content['cabang'] = $this->Model_main->Cabang();
			$this->twig->display('pembelian/datamutasikoreksi.html', $this->content);
		}else{  
	      redirect("main/auth/logout");
	    }
		
	}

	public function datamutasibatch(){
		if ( $this->logged){
			$this->content['data'] = $this->Model_mutasi->listDataMutasiBatch();
			$this->content['cabang'] = $this->Model_main->Cabang();
			$this->twig->display('pembelian/datamutasibatch.html', $this->content);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	public function datamutasigudang(){
		if ( $this->logged){
			$this->content['data'] = $this->Model_mutasi->listDataMutasiGudang();
			$this->content['cabang'] = $this->Model_main->Cabang();
			$this->twig->display('pembelian/datamutasigudang.html', $this->content);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	public function batchInGudang()
	{
		if ($this->logged) 
		{
			$produk = $_POST['produk'];
			$gudang = $_POST['gudang'];
			$data = $this->Model_mutasi->BatchGudang($produk,$gudang);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}
