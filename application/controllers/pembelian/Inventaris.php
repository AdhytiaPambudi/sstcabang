<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventaris extends CI_Controller {

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
        $this->load->model('pembelian/Model_inventaris');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function entry_data_inventaris()
	{	
		if ( $this->logged) 
		{
			$this->twig->display('pembelian/entry_data_inventaris.html', $this->content);
		}
		else
		{
			redirect("auth/");
		}
	}

	public function save_data_inventaris(){
		$datapost =  $_POST['detail'];
        $valid = $this->Model_inventaris->saveData($datapost);
        // return $valid;
        echo json_encode($valid); 
	}
    
    // ============================================================================

    public function data_inventaris()
    {   
        if ( $this->logged)
        {
            $this->twig->display('pembelian/data_inventaris.html', $this->content);
        }
        else 
        {
            redirect("auth/");
        }
    }

    public function load_datainventaris()
    {   
        $data = array();
        $bySearch = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length=$_REQUEST['length'];
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        $total = $this->Model_inventaris->load_datainventaris()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " where (cabang like '%".$search."%' or scabang like '%".$search."%' or kode like '%".$search."%' or jenis like '%".$search."%' or barang like '%".$search."%' or merk like '%".$search."%' or tipe like '%".$search."%' or lokasi like '%".$search."%' or ruang like '%".$search."%' or pemegang like '%".$search."%' or kondisi like '%".$search."%' or barcode like '%".$search."%')";
        }
        $byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];

        $datas = $this->Model_inventaris->load_datainventaris($bySearch, $byLimit)->result();
        // log_message('error',print_r($datas,true));
        $data = array();
        // $no = $_POST['start'];
        $no = $start;
        $i = 0;
        foreach ($datas as $value) {
            $no++;
            $row = array();
            // $row[] = $no;
            $row[] = '<p align="center"><input type="checkbox" produk="'.$value->barang.'" name="'.$value->barang.'" id="list'.$no.'" value="'.(!empty($value->barcode) ? $value->barcode : "").'" onchange="ceklis(this)"></p>';
            $row[] = (!empty($value->id) ? $value->id : "");
            $row[] = (!empty($value->cabang) ? $value->cabang : "");
            $row[] = (!empty($value->kode) ? $value->kode : "");
            $row[] = (!empty($value->jenis) ? $value->jenis : "");
            $row[] = (!empty($value->barang) ? $value->barang : "");
            $row[] = (!empty($value->merk) ? $value->merk : "");
            $row[] = (!empty($value->tipe) ? $value->tipe : "");
            $row[] = (!empty($value->tgl_beli) ? $value->tgl_beli : "");
            $row[] = (!empty($value->lokasi) ? $value->lokasi : "");
            $row[] = (!empty($value->ruang) ? $value->ruang : "");
            $row[] = (!empty($value->pemegang) ? $value->pemegang : "");
            $row[] = (!empty($value->kondisi) ? $value->kondisi : "");
            $row[] = (!empty($value->barcode) ? $value->barcode : "");
            $row[] = (!empty($value->keterangan) ? $value->keterangan : "");
            $row[] = '<button onclick="change(event,this)" id='.$value->barcode.' hidden><span class="glyphicon glyphicon-edit" title="Edit"></button></span><button onclick="remove(event,this)" id='.$value->barcode.'><span class="glyphicon glyphicon-trash" title="hapus"></span></button>';
            $row[] = "a";

            $data[] = $row;

            $i++;
        }   
        $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $this->Model_inventaris->load_datainventaris($bySearch)->num_rows(),
                    "recordsFiltered" => $this->Model_inventaris->load_datainventaris($bySearch)->num_rows(),
                    "data" => $data
                );
        echo json_encode($output); 
    }

    public function remove_data_inventaris(){
        $data = $_POST['no'];
        $datas = $this->Model_inventaris->remove_data_inventaris($data);
        return;
    }

    public function cetak_inventaris(){
        $data = $_GET['no'];
        $datas = $this->Model_inventaris->cetak_inventaris($data);
        return $datas;
    }
}
