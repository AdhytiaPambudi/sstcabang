<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GPSCall extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('laporan/Model_GPSCall');
    }

    public function g_stok()
    {
        $data = $this->Model_GPSCall->get_stok();
        echo json_encode($data);
    }

    public function g_pelanggan()
    {
        $data = $this->Model_GPSCall->get_pelanggan();
        echo json_encode($data);
    }

    public function g_rute_salesman()
    {
        $kode_sales = $_GET['mKodeSalesman'];
        $data = $this->Model_GPSCall->get_rute_salesman($kode_sales);
        echo json_encode($data);
    }

    public function g_piutang()
    {
        $data = $this->Model_GPSCall->get_piutang();
        echo json_encode($data);

    }
}