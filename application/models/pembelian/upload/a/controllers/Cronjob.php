<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cronjob extends CI_Controller {

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
		$this->load->model('Model_cronjob');
	}

	function index(){
		$this->Model_cronjob->clear_history();
		
		$this->Model_cronjob->update_trs_buku_giro();
		$this->Model_cronjob->update_trs_buku_kasbon();
		$this->Model_cronjob->update_trs_buku_titipan();
		$this->Model_cronjob->update_trs_buku_transaksi();
		$this->Model_cronjob->update_trs_giro();
		$this->Model_cronjob->update_trs_mutasi_koreksi();
		$this->Model_cronjob->update_trs_pelunasan_detail();
		$this->Model_cronjob->update_trs_pelunasan_giro_detail();
		$this->Model_cronjob->update_trs_relokasi_kirim_header();
		$this->Model_cronjob->update_trs_relokasi_kirim_detail();
		$this->Model_cronjob->update_trs_relokasi_terima_header();
		$this->Model_cronjob->update_trs_relokasi_terima_detail();
		$this->Model_cronjob->update_trs_terima_barang_header();
		$this->Model_cronjob->update_trs_terima_barang_detail();
		$this->Model_cronjob->update_trs_kiriman();
		$this->Model_cronjob->update_trs_invsum();
		$this->Model_cronjob->update_trs_invdet();
		$this->Model_cronjob->update_trs_delivery_order_sales();
		$this->Model_cronjob->update_trs_delivery_order_sales_detail();
		$this->Model_cronjob->update_trs_faktur();
		$this->Model_cronjob->update_trs_faktur_detail();
		$this->Model_cronjob->update_trs_faktur_cndn();
		$this->Model_cronjob->update_trs_invday_sum();
		$this->Model_cronjob->update_trs_invday_det();
		/*$this->Model_cronjob->update_trs_faktur_day();
		$this->Model_cronjob->update_trs_do_day();*/
	}

	function invday(){
		
		$this->Model_cronjob->update_trs_invday_sum();
		$this->Model_cronjob->update_trs_invday_det();
	}

	function salesday(){
		
		$this->Model_cronjob->update_trs_faktur_day();
		$this->Model_cronjob->update_trs_do_day();
	}

}