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
		$data = $this->db->query("SELECT KodeProduk,NoDokumen FROM trs_invdet WHERE tahun ='2020' AND gudang='Baik' AND UnitCogs = 0  ")->result();

      foreach ($data as $r) {

        $data1 = $this->db->query("SELECT COGS,acu FROM trs_faktur_detail WHERE NoFaktur = '".$r->NoDokumen."' AND KodeProduk ='".$r->KodeProduk."' ")->row();

        if ($data1->COGS < 0 ) {
        	$cogs = $data1->COGS * -1;
        	$this->db->query("UPDATE trs_invdet SET UnitCOGS = $cogs WHERE tahun='2020' and gudang = 'Baik' And KodeProduk = '".$r->KodeProduk."' and NoDokumen = '".$r->NoDokumen."'");
        }else{
        	$data1 = $this->db->query("SELECT COGS,acu FROM trs_faktur_detail WHERE NoFaktur = '".$data1->acu."' AND KodeProduk ='".$r->KodeProduk."' ")->row();

        	if ($data1->COGS < 0 ) {
        		$cogs = $data1->COGS * -1;
        	}else{
        		$cogs = $data1->COGS ;
        	}
        	$this->db->query("UPDATE trs_invdet SET UnitCOGS = $cogs WHERE tahun='2020' and gudang = 'Baik' And KodeProduk = '".$r->KodeProduk."' and NoDokumen = '".$r->NoDokumen."'");
        }

          
      }
    }

    public function test1(){

    }

    public function test2(){

    }


}