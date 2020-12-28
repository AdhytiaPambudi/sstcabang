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
		$this->cabang = $this->session->userdata('cabang');
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
	        $cektglstok = 1;/*$this->Model_main->cek_tglstoktrans();*/
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$this->content['cabang']= $this->Model_main->Cabang();
						$this->twig->display('pembelian/mutasiKoreksi.html', $this->content);
					}else{
						redirect("main");
					}
				}else if($cektglstok == 0){
					redirect("main");
				}
			}else{
				$this->content['cabang']= $this->Model_main->Cabang();
				$this->twig->display('pembelian/mutasiKoreksi.html', $this->content);
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
            $cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1 ){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
					$respon = true;
					foreach ($params->produk as $key => $value) 
		        	{
		        		$produk = explode("~", $params->produk[$key])[0];
						$batchNo = $params->batch[$key];
						$BatchDoc = $params->docstok[$key];
						$banyak = $params->qty[$key];
						$gudang =$params->gudang;
						$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
									from trs_invsum
									where KodeProduk ='".$produk."' and Gudang ='".$gudang."' and Tahun = '".date('Y')."' limit 1 ")->row();
						$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok'
									from trs_invdet
									where KodeProduk ='".$produk."' and Gudang ='".$gudang."' and Tahun = '".date('Y')."' 
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
								 Gudang ='".$gudang."' limit 1")->row();
						
						if($params->qty[$key] < 0){
			              if(($banyak* -1) > $cekstokdetail->UnitStok){
			                $respon = false;
			                break;
			              }
			            }
		        	}
		        	if($respon == true){
						$valid = $this->Model_mutasi->saveData($params);
						echo json_encode(array("status" => $valid));
					}else{
						echo json_encode(array("status" => FALSE));
					}
					
				}else{
					$data=false;
					echo json_encode(array("status" => $data));
				}

			}else if($cektglstok == 0){
				$data=false;
				echo json_encode(array("status" => $data));
			}
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
			$respon = true;
			$params = (object)$this->input->post();
			foreach ($params->produk as $key => $value) 
        	{
        		$produk = explode("~", $params->produk[$key])[0];
				$batchNo = $params->batch[$key];
				$BatchDoc = $params->docstok_awal[$key];
				$banyak = $params->qty[$key];
				$gudang =$params->gudang_awal[$key];
				$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok'
							from trs_invsum
							where KodeProduk ='".$produk."' and Gudang ='".$gudang."' and Tahun = '".date('Y')."' limit 1 ")->row();
				$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok'
							from trs_invdet
							where KodeProduk ='".$produk."' and Gudang ='".$gudang."' and Tahun = '".date('Y')."' 
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
							      Gudang ='".$gudang."' limit 1")->row();
				if($params->qty[$key] < 0){
              if(($banyak* -1) > $cekstokdetail->UnitStok){
                $respon = false;
                break;
              }
            }
        	}
        	if($respon == true){
				$valid = $this->Model_mutasi->saveDataGudang($params);
				echo json_encode(array("status" => $valid));
			}else{
				echo json_encode(array("status" => FALSE));
			}
			
			
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
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->no_koreksi."'".','."'".$key->gudang."'".','."'".$key->produk."'".','."'".$key->batch."'".','."'".$key->NoDocStok."'".','."'".$key->noline."'".')"><i class="fa fa-check"></i> Approve</a>';
				$row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="rejectData('."'".$key->no_koreksi."'".','."'".$key->gudang."'".','."'".$key->produk."'".','."'".$key->batch."'".','."'".$key->NoDocStok."'".','."'".$key->noline."'".')"><i class="fa fa-times"></i> Reject</a>'; 
		
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
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$No = $_POST['No'];
			$gudang = $_POST['gudang'];
			$produk = $_POST['produk'];
			$batch = $_POST['batch'];
			$docstok = $_POST['docstok'];
			$noline = $_POST['noline'];
			$cek = $this->db->query("select month(tanggal) as 'tanggal' from trs_mutasi_koreksi where no_koreksi ='".$No."' limit 1")->row();
			$date = date('Y-m-d');
			$Month = date('m');
			$blnmutasi = $cek->tanggal;
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						if($Month == $blnmutasi){
							$valid = $this->Model_mutasi->apprvMutasiKoreksi($No,$gudang,$produk,$batch,$docstok,$noline);
							echo json_encode(array("status" => $valid));
						}else{
							echo json_encode(array("status" => "bulan"));
						}	
					}else{
						$data=false;
						echo json_encode(array("status" => $data));
					}

				}else if($cektglstok == 0){
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}else{
				if($cektglstok == 1){
					$valid = $this->Model_mutasi->apprvMutasiKoreksi($No,$gudang,$produk,$batch,$docstok,$noline);
					echo json_encode(array("status" => $valid));
				}else if($cektglstok == 0 && date('m') - 1 <= $blnmutasi){
					$valid = $this->Model_mutasi->apprvMutasiKoreksi($No,$gudang,$produk,$batch,$docstok,$noline);
					echo json_encode(array("status" => $valid));
				}else{
					
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}
			
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
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$No = $_POST['No'];
			$gudang = $_POST['gudang'];
			$produk = $_POST['produk'];
			$batch = $_POST['batch'];
			$docstok = $_POST['docstok'];
			$noline = $_POST['noline'];
			$cek = $this->db->query("select month(tanggal) as 'tanggal' from trs_mutasi_koreksi where no_koreksi ='".$No."' limit 1")->row();
			$date = date('Y-m-d');
			$Month = date('m');
			$blnmutasi = $cek->tanggal;
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						if($Month == $blnmutasi){
							$valid = $this->Model_mutasi->rejectMutasiKoreksi($No,$gudang,$produk,$batch,$docstok,$noline);
							echo json_encode(array("status" => $valid));
						}else{
							echo json_encode(array("status" => "bulan"));
						}	
					}else{
						$data=false;
						echo json_encode(array("status" => $data));
					}

				}else if($cektglstok == 0){
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}else{
				$valid = $this->Model_mutasi->rejectMutasiKoreksi($No,$gudang,$produk,$batch,$docstok);
				echo json_encode(array("status" => $valid));
			}
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
			$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok' from trs_invsum where gudang ='".$gudang."' and tahun ='".date('Y')."' and KodeProduk ='".$produk."' limit 1")->row();
			$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where gudang ='".$gudang."' and tahun ='".date('Y')."' and KodeProduk ='".$produk."' group by KodeProduk")->row();
			if($ceksum->UnitStok == $cekdet->UnitStok){
				$data = $this->Model_mutasi->BatchGudang($produk,$gudang);
			}else{
				$data = false;
			}
			
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}
