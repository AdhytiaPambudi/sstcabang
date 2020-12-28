<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

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
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->cabang = $this->session->userdata('cabang');
		$this->Emailx = $this->session->userdata('email');
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
			$this->content['closing']= 1;
			$this->twig->display('index.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
	}

	public function noDokumen()
	{
		if ($this->logged) 
		{
			$dok = $_POST['dok'];
			$noDok = $this->Model_main->noDokumen($dok);
			echo json_encode($noDok);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listCabang()
	{
		if ($this->logged) 
		{
			$noDok = $this->Model_main->Cabang();
			echo json_encode($noDok);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function produkAll()
	{
		if ($this->logged) 
		{
			$produk = $this->Model_main->Produk();
			echo json_encode($produk);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function produkInStok()
	{
		if ($this->logged) 
		{
			$data = $this->Model_main->ProdukInStok();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function ProdukInkoreksi()
	{
		if ($this->logged) 
		{
			$data = $this->Model_main->ProdukInkoreksi();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function ProdukInMutasi()
	{
		if ($this->logged) 
		{
			$data = $this->Model_main->ProdukInMutasi();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function batchInStok($kode = NULL)
	{
		if ($this->logged) 
		{
			$xpld = explode('~',$kode);
			$kode = $xpld[0];
			$gudang = $xpld[1];
			$data = $this->Model_main->Batch($kode,$gudang);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function batchInKoreksi($kode = NULL)
	{
		if ($this->logged) 
		{
			$xpld = explode('~',$kode);
			$kode = $xpld[0];
			$gudang = urldecode($xpld[1]);
			$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok' from trs_invsum where gudang ='".$gudang."' and tahun ='".date('Y')."' and KodeProduk ='".$kode."' limit 1")->row();
			$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where gudang ='".$gudang."' and tahun ='".date('Y')."' and KodeProduk ='".$kode."' group by KodeProduk")->row();
			if($ceksum->UnitStok == $cekdet->UnitStok){
				$data = $this->Model_main->BatchKoreksi($kode,$gudang);
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
	
	public function allbatchInStok($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_main->AllBatch();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getSatuan($kode=null)
	{
		if ($this->logged) 
		{
			$satuan = $this->Model_main->Produk_uom($kode);
			echo $satuan;
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getPrinsipal()
	{
		if ($this->logged) 
		{
			$prinsipal = $this->Model_main->Prinsipal();
			// log_message('error',print_r($prinsipal,true));
			echo json_encode($prinsipal);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function batchInStokRelokasi($kode = NULL)
  {
    if ($this->logged) 
    {
      	$ceksum = $this->db->query("select ifnull(UnitStok,0) as 'UnitStok' from trs_invsum where gudang ='Baik' and tahun ='".date('Y')."' and KodeProduk ='".$kode."' limit 1")->row();
	  	$cekdet = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where gudang ='Baik' and tahun ='".date('Y')."' and KodeProduk ='".$kode."' group by KodeProduk")->row();
	  	if($ceksum->UnitStok == $cekdet->UnitStok){
			$data = $this->Model_main->Batch($kode,"Baik");
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

  function get_port(){
  	 echo $data = $this->session->userdata('port');
  	 // echo json_encode($data);
  }
  public function getHargaBeli($kode=null)
	{
		if ($this->logged) 
		{
			$valid = $this->Model_main->getHargaBeli($kode);
			echo json_encode($valid);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}
