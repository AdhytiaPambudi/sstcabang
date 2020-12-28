<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_approval');
		$this->load->model('pembelian/Model_order');
		$this->load->model('pembelian/Model_kiriman');
		$this->load->model('pembelian/Model_relokasi');
		$this->load->model('pembelian/Model_closing');
		$this->load->model('pembelian/Model_faktur');
		$this->logs = $this->session->all_userdata();
		$this->user = $this->session->userdata('username');
        $this->cabang = $this->session->userdata('cabang');
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function test(){
		$this->twig->display('index_.html', $this->content);
    }

    public function test1(){
    	
    }

    public function test2(){

    }


}