<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SP_Pelanggan extends CI_Controller {
	var $content;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/Model_SP_pelanggan');
        $this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
        $this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
    }

    function Register_SP_Pelanggan(){ // rian
		if ( $this->logged ) 
		{
			$this->content['tgl'] = date('Y-m-d');
			$this->content['tgl2'] = date('2019-01-01');
			$this->content['tgl_ini'] = date('Y-m-d');
			$this->content['tgl_plus'] = date("Y-m-d",strtotime("+7 day"));

			$this->twig->display('pembelian/buatsp_pelanggan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

   

    public function listSP_pelanggan(){
    	if ($this->logged) 
		{
			$data = array();

	        $draw = $_REQUEST['draw'];
	        $tgl1 = $_POST['tgl1'];
	        $tgl2 = $_POST['tgl2'];
	        $cek = $_POST['cek'];

	        /*Jumlah baris yang akan ditampilkan pada setiap page*/
	        $length = $_REQUEST['length'];

	    
	        $start = $_REQUEST['start'];

	        /*Keyword yang diketikan oleh user pada field pencarian*/
	        $search = $_REQUEST['search']["value"];


	        $this->db->where('status <>', 'batal');
	        $this->db->where('TipeDokumen', 'Faktur');
	        $this->db->where("TglFaktur BETWEEN '$tgl1' AND '$tgl2'");
	         if ($cek != "semua") {
	        	$this->db->where("( NoSP='' OR NoSP is null )");
	        }
	        $total = $this->db->count_all_results("trs_faktur");

	        /*Mempersiapkan array tempat kita akan menampung semua data
	        yang nantinya akan server kirimkan ke client*/
	        $output = array();

	        /*Token yang dikrimkan client, akan dikirim balik ke client*/
	        $output['draw'] = $draw;

	      
	        $output['recordsTotal'] = $output['recordsFiltered'] = $total;

	        /*disini nantinya akan memuat data yang akan kita tampilkan 
	        pada table client*/
	        $output['data'] = array();


	        /*Jika $search mengandung nilai, berarti user sedang telah 
	        memasukan keyword didalam filed pencarian*/
	        if ($search != "") {
	            $this->db->like("NoFaktur", $search);
	            // $this->db->or_like("TglFaktur", $search);
	            // $this->db->or_like("NamaPelanggan", $search);
	        }


	        /*Lanjutkan pencarian ke database*/
	        if ($length != -1) {
	        	$this->db->limit($length, $start);
	        }
	        /*Urutkan dari alphabet paling terkahir*/
	        $this->db->order_by('NoFaktur', 'DESC');
	        $this->db->where('status <>', 'batal');
	        $this->db->where('TipeDokumen', 'Faktur');
	        $this->db->where("TglFaktur BETWEEN '$tgl1' AND '$tgl2'");
	        if ($cek != "semua") {
	        	$this->db->where("( NoSP='' OR NoSP is null )");
	        }

	        $query = $this->db->get('trs_faktur');


	        if ($search != "") {
	            $this->db->like("NoFaktur", $search);
	            // $this->db->or_like("TglFaktur", $search);
	            // $this->db->or_like("NamaPelanggan", $search);
	            $this->db->where("TglFaktur BETWEEN '$tgl1' AND '$tgl2'");
	            $this->db->where('status <>', 'batal');
	            $this->db->where('TipeDokumen', 'Faktur');
	             if ($cek != "semua") {
		        	$this->db->where("( NoSP='' OR NoSP is null )");
		        }
	            $jum = $this->db->get('trs_faktur');
	            $output['recordsTotal'] = $output['recordsFiltered'] = $jum->num_rows();
	        }


	        $i = $start + 1;
	        foreach ($query->result_array() as $r) {
	        	if ($r['NoSP'] == '' || $r['NoSP'] == null) {
	 				$status = '<button class="btn btn-sm btn-warning" href="javascript:void(0)" title="SP Pelanggan Lengkap" onclick="proses_one('."'".$r['NoFaktur']."'".')">Faktur Lengkap</button>';
	 				// $status = "-";
	 				$disabled="";
	 				$checkbox = '<input type="checkbox" class="aa form-control" onchange="ceklis(this)"  name="cekfaktur" id="checkbox-'.$i.'" value="'.$r['NoFaktur'].'"> ';

	 			}else{
	 				 $status='Lengkap';
	 				 $disabled="disabled";
	 				 $checkbox = '';
	 			}

	 			$warna=$title= "";			

	 			if ($this->beda_waktu(date($r['TglFaktur'],strtotime("+7 day")), date('Y-m-d'), '%d') > 7) {
	 				$warna = "red";
	 				$title = "Tanggal Faktur Lewat dari 7 hari";
	 			}

	 			if ($r['Acu'] == "") {
	 				$value= $r['NoSP'];
	 			}else{
	 				$value = $r['Acu'];
	 			}

	            $output['data'][] = array(

	                $i,
	                $r['Cabang'],

	                $checkbox,

					'<label style="color:'.$warna.'" title="'.$title.'">'.$r['NoFaktur'].'</label>' ,
					$r['TglFaktur'],
					$r['NamaPelanggan'],
					'<input type="text" id="txt-sp-'.$r['NoFaktur'].'" '.$disabled.' value="'.$value.'" class="form-control">',
					$r['Acu'],
					'<button class="btn  btn-default" href="javascript:void(0)" title="Detail" onclick="detail('."'".$r['NoFaktur']."'".')"> Detail</button>',
					$status

	                
	            );
	            $i++;
	        }

	        echo json_encode($output);

			
		}
		else 
		{	
			redirect("auth/");
		}
    }

    function beda_waktu($date1, $date2, $format = false) {
		$diff = date_diff( date_create($date1), date_create($date2) );
		if ($format)
			return $diff->format($format);
		
		return array('y' => $diff->y,
					'm' => $diff->m,
					'd' => $diff->d,
					'h' => $diff->h,
					'i' => $diff->i,
					's' => $diff->s
				);
	}

    function detailsp_pelanggan() { 
    	$no = $_POST['no'];

       $result = $this->Model_SP_pelanggan->detailsp_pelanggan($no)->result();
       echo json_encode(
       					array('header' => $this->Model_SP_pelanggan->headersp_pelanggan($no)->row(),
       						  'detail' => $this->Model_SP_pelanggan->detailsp_pelanggan($no)->result())
   						);
   }

    public function prosesSP_pelanggan_one(){
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$no_sp = $_POST['no_sp'];
			$result = $this->Model_SP_pelanggan->update_sp_one($no,$no_sp);
			echo json_encode(array("status" => $result));
		}
		else
		{
			redirect("auth/");
		}
	}

	public function prosesSP_pelanggan(){
		if ($this->logged) 
		{
			$data = $this->input->post();
			// print_r($data);
			// exit();
			$result = $this->Model_SP_pelanggan->update_sp($data);
			echo json_encode(array("status" => $result));
		}
		else
		{
			redirect("auth/");
		}
	}

	
   


}