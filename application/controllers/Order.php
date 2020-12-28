<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller {

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
		$this->load->model('pembelian/Model_order');
		$this->load->library('format');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"y" => date("y"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function buatorder()
	{	

		if ($this->logged) 
		{				
			$kodeLamaCabang = $this->Model_order->kodeLamaCabang();
			$kodeDokumen = $this->Model_order->kodeDokumen('SO');
			$counter = $this->Model_order->counter('SO');
			if ($counter) 
			{
				$no = $counter->Counter + 1;
	        }
	        else
	        	$no = 1;

	        $noOrder = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
	        $this->content['noOrder'] = $noOrder;
	        $this->content['caraBayar'] = $this->Model_order->caraBayar();
			$this->twig->display('pembelian/buatorder.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listPelanggan()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Pelanggan();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function dataPelanggan($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->dataPelanggan($kode);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function listSales()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Salesman();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listProduk()
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Produk();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getBatch($kode = NULL)
	{
		if ($this->logged) 
		{
			$data = $this->Model_order->Batch($kode);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}