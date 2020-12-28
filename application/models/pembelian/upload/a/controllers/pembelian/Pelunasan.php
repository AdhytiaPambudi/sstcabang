<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pelunasan extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_pelunasan');
		$this->load->model('pembelian/Model_faktur');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function buatpelunasan()
	{	
		if ( $this->logged ) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				// if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'N')){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y' )){
					$this->content['dih'] = $this->Model_pelunasan->listNoDIH();
					$this->content['gl_bank'] = $this->Model_main->gl_bank();
					$this->twig->display('pembelian/buatpelunasan.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getDataDIH()
	{
		if ($this->logged) 
		{
			$nodih = $_POST['nodih'];
			$data = $this->Model_pelunasan->getDataDIH($nodih);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function saveDataPelunasan()
	{          
        if ($this->logged) 
		{	
			$data = (object)$this->input->post();
			$data = json_decode(json_encode($data),true);
			$datas = $data['data_form'];
			$i=0;
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y' )){
					foreach ($datas as $key => $params) 
		        	{
		        		$i++;
		        		$status = $this->Model_pelunasan->saveDataPelunasan($params, $key,$i);
						// $status = $this->Model_pelunasan->prosesDataPelunasan($params->nodih, $params->nofaktur[$key],$i);
						// $status = $this->Model_faktur->prosesDataFaktur($params->nofaktur[$key]);
		        	}
					echo json_encode(array("status" => $status));
				}else{
					$valid=false;
					echo json_encode(array("status" => $valid));
				}
				
			}else if($cektglstok == 0){
				$valid=false;
				echo json_encode(array("status" => $valid));
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function get_gl_bank(){
		$xx = $this->Model_main->gl_bank();
		$output = array(
							"data" => $xx,
					);

		echo json_encode($xx);
	}

	public function get_glbank(){
		$xx = $this->Model_main->gl_bank();
		$output = array(
							"data" => $xx,
					);

		return $xx;
	}

    public function datadih()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datadih.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataDIH($cek = null)
	{	
		if ($this->logged) 
		{
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = [];

			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start=$_REQUEST['start'];

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search=$_REQUEST['search']["value"];


			/*Menghitung total data didalam database*/
			if($cek=='all')
				$total = $this->Model_pelunasan->listDataDIH()->num_rows();
			else
				$total = $this->Model_pelunasan->listDataDIH('','','Open')->num_rows();

			/*Mempersiapkan array tempat kita akan menampung semua data
			yang nantinya akan server kirimkan ke client*/
			$output=array();

			/*Token yang dikrimkan client, akan dikirim balik ke client*/
			$output['draw']=$draw;

			/*
			$output['recordsTotal'] adalah total data sebelum difilter
			$output['recordsFiltered'] adalah total data ketika difilter
			Biasanya kedua duanya bernilai sama, maka kita assignment 
			keduaduanya dengan nilai dari $total
			*/
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " (NoDIH like '%".$search."%' or Penagih like '%".$search."%'or Status like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or TglDIH like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_pelunasan->listDataDIH($bySearch, $byLimit)->result();
			else
				$query=$this->Model_pelunasan->listDataDIH($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listDataDIH($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listDataDIH($bySearch, '', "Open")->num_rows();
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = '<p align="center"><input class="ceklis" type="checkbox" name="list['.$i.']" id="list'.$i.'" value="'.$baris->NoDIH.'" status="'.$baris->Status.'"></p>';
				if ($baris->statusPusat == 'Gagal') {
					$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDIH."'".')"><i class="fa fa-check"></i> Proses</button>';
				}
				else
				{
					$row[] = 'Berhasil';
				}
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDIH."'".','."'".$baris->Status."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoDIH."'".','."'".$baris->Status."'".')"><i class="fa fa-eye"></i> Print</button>';
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
	 			$row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
	 			$row[] = (!empty($baris->Penagih) ? $baris->Penagih : "");
	 			$row[] = (!empty($baris->TglDIH) ? $baris->TglDIH : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = "<p align='right'>".(!empty(number_format((float)$baris->Total,2)) ? number_format((float)$baris->Total,2) : "")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format((float)$baris->Saldo,2)) ? number_format((float)$baris->Saldo,2) : "")."</p>";	
	 			$row[] = "<p align='right'>".(!empty(number_format((float)$baris->Sisa,2)) ? number_format((float)$baris->Sisa,2) : "")."</p>";	
				$data[] = $row;
				$i++;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function listDataDetailDIH()
	{
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];	
			$status 	 = $_POST['status'];	
			$data = $this->Model_pelunasan->dataDetailDIH($no,$status);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function listGiro($pelanggan)
	{
		if ($this->logged) 
		{
			$data = $this->Model_pelunasan->listGiro($pelanggan);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listSSP()
	{
		if ($this->logged) 
		{
			$dih 		 = $_POST['dih'];	
			$nofaktur 	 = $_POST['nofaktur'];
			// $nontpn 	 = $_POST['nontpn'];
			$data = $this->Model_pelunasan->listSSP($dih,$nofaktur);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function listTitipan()
	{
		if ($this->logged) 
		{
			$data = $this->Model_pelunasan->listTitipan();
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}


	public function saveDataGiro()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$status = $this->Model_pelunasan->saveDataGiro($params);
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function saveDataSSP()
	{          
        if ($this->logged) 
		{	
			$params = (object)$this->input->post();
			$status = $this->Model_pelunasan->saveDataSSP($params);
			echo json_encode(array("status" => $status));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function getFakturDIH()
	{
		if ($this->logged) 
		{
			$nodih = $_POST['nodih'];
			$nofaktur = $_POST['nofaktur'];
			$data = $this->Model_pelunasan->getFakturDIH($nodih, $nofaktur);
			echo json_encode($data);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datapelunasan()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datapelunasan.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataPelunasan($tipe = null)
	{	
		if ($this->logged) 
		{

			// $tipe = $this->input->post('tipe');
			// $cek = $tipe;
			
			/*Menagkap semua data yang dikirimkan oleh client*/
            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";

            $draw = "";
            if(isset($_REQUEST['draw']))
            {
	            $draw=$_REQUEST['draw'];            	
            }
			// log_message('error','draw='.print_r($draw,true));
            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            // $length=100;
            $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */

            $start="";
            if (isset($_REQUEST['start'])) {
	            $start=$_REQUEST['start'];
            }
            $search="";
            if (isset($_REQUEST['search']["value"])) {
	            $search=$_REQUEST['search']["value"];
            }


            /*Menghitung total data didalam database*/
            // $total = 0;
            $total = $this->Model_pelunasan->listDataPelunasan('','','',$tipe)->num_rows();
            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (NomorFaktur like '%".$search."%' or NoDIH like '%".$search."%' or Area like '%".$search."%' or KodePelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or KodePenagih like '%".$search."%' or NamaPenagih like '%".$search."%' or KodeSalesman like '%".$search."%' or NamaSalesman like '%".$search."%' or TglFaktur like '%".$search."%' or TglPelunasan like '%".$search."%' or Status like '%".$search."%' or ValueFaktur like '%".$search."%' or Cicilan like '%".$search."%' or SaldoFaktur like '%".$search."%' or Giro like '%".$search."%' or TglPelunasan like '%".$search."%' or TglFaktur like '%".$search."%' or TglGiroCair like '%".$search."%' or bank like '%".$search."%' or status_titipan like '%".$search."%' or no_titipan like '%".$search."%' or status_tambahan like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
            // log_message('error','byLimit='.print_r($byLimit,true));

            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            $query=$this->Model_pelunasan->listDataPelunasan($bySearch, $byLimit, "", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listDataPelunasan($bySearch,'','',$tipe)->num_rows();

            $x = $start;
            $i = 0;
            $countCetak = 1;

			 foreach ($query as $baris) {
		        $x++;
		        $row = array();
		        $row[] = $x;
		        // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NomorFaktur."'".')"><i class="fa fa-eye"></i> Lihat</button>';
		        // $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
		         $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
		         // $row[] = (!empty($baris->UmurLunas) ? $baris->UmurLunas : "");
		         $row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
		         $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		         $row[] = (!empty($baris->NomorFaktur) ? $baris->NomorFaktur : "");
		         $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
		         $f = new DateTime($baris->TglFaktur);
		         $p = new DateTime($baris->TglPelunasan);
		         $s = $f->diff($p);
		         $row[] = (!empty($baris->UmurFaktur) ? $baris->UmurFaktur : $s->days);
		         $row[] = (!empty($baris->KodePenagih) ? $baris->KodePenagih : "");
		         $row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
		         $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
		         $row[] = (!empty($baris->KodeSalesman) ? $baris->KodeSalesman : "");
		          $row[] = (!empty(number_format($baris->ValueFaktur)) ? number_format($baris->ValueFaktur) : 0);
		         // $row[] = (!empty($baris->Cicilan) ? $baris->Cicilan : "");
		         $row[] = (!empty(number_format($baris->SaldoFaktur)) ? number_format($baris->SaldoFaktur) : 0);
		         $row[] = (!empty($baris->TipePelunasan) ? $baris->TipePelunasan : "");
		         $row[] = (!empty($baris->Status) ? $baris->Status : "");
		         if($baris->TipeDokumen == 'Retur' or $baris->TipeDokumen == 'CN'){
		         	if($baris->Status != 'Batal'){
		         		if($baris->ValuePelunasan < 0){
			         		$row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
			         	}else{
			         		$row[] = (!empty(number_format($baris->ValuePelunasan*-1)) ? number_format($baris->ValuePelunasan*-1) : 0);
			         	}	
			         }else{
			         	$row[] = 0;
			         }
		         	
		         }else{
		         	if($baris->Status != 'Batal'){
		         		$row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
		         	}else{
		         		$row[] = 0;
		         	}
		         	
		         }
		         // $row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : "");
		         $row[] = (!empty(number_format($baris->SaldoAkhir)) ? number_format($baris->SaldoAkhir) : 0);
		         $row[] = (!empty($baris->Giro) ? $baris->Giro : "");
		         $row[] = (!empty($baris->TglGiroCair) ? $baris->TglGiroCair : "");
		         $row[] = (!empty(number_format($baris->ValueGiro)) ? number_format($baris->ValueGiro) : 0);
		         $row[] = (!empty(number_format($baris->value_pembulatan)) ? number_format($baris->value_pembulatan) : 0);
		         $row[] = (!empty(number_format($baris->materai)) ? number_format($baris->materai) : 0);
		         $row[] = (!empty(number_format($baris->value_transfer)) ? number_format($baris->value_transfer) : 0);
		         $row[] = (!empty($baris->bank) ? $baris->bank : "");
		         $row[] = (!empty($baris->keterangan) ? $baris->keterangan : "");
		         
		        $data[] = $row;
		        $i++;
		      }
		      $output['data'] = $data;
			// $output = array(
			// 				"data" => $data,
			// 		);
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}


public function dataregpenerimaangiro()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datareggiro.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listregisterpenerimaangiro($tipe = null)
	{	
		if ($this->logged) 
		{

			// $tipe = $this->input->post('tipe');
			// $cek = $tipe;
			
			/*Menagkap semua data yang dikirimkan oleh client*/
            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";

            $draw = "";
            if(isset($_REQUEST['draw']))
            {
	            $draw=$_REQUEST['draw'];            	
            }
			// log_message('error','draw='.print_r($draw,true));
            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            // $length=100;
            $length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */

            $start="";
            if (isset($_REQUEST['start'])) {
	            $start=$_REQUEST['start'];
            }
			            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search="";
            if (isset($_REQUEST['search']["value"])) {
	            $search=$_REQUEST['search']["value"];
            }


            /*Menghitung total data didalam database*/
            // $total = 0;
            $total = $this->Model_pelunasan->listregisterpenerimaangiro('','','',$tipe)->num_rows();
                       /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (NomorFaktur like '%".$search."%' or NoDIH like '%".$search."%' or KodePelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or TglPelunasan like '%".$search."%' or Status like '%".$search."%' or ValueFaktur like '%".$search."%' or Cicilan like '%".$search."%' or SaldoFaktur like '%".$search."%' or Giro like '%".$search."%' or TglPelunasan like '%".$search."%' or TglFaktur like '%".$search."%' or TglGiroCair like '%".$search."%' or bank like '%".$search."%' or status_titipan like '%".$search."%' or no_titipan like '%".$search."%' or status_tambahan like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
            // log_message('error','byLimit='.print_r($byLimit,true));

            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            $query=$this->Model_pelunasan->listregisterpenerimaangiro($bySearch, $byLimit, "", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listregisterpenerimaangiro($bySearch,'','',$tipe)->num_rows();

            $x = $start;
            $i = 0;
            $countCetak = 1;

			 foreach ($query as $baris) {
		        $x++;
		        $row = array();
		        $row[] = $x;
		        $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
		         $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
		         $row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
		         $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
		         $row[] = (!empty($baris->NomorFaktur) ? $baris->NomorFaktur : "");
		         $row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
		         $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
		         $row[] = (!empty($baris->TglJTO) ? $baris->TglJTO : "");
		         $row[] = "<p align='right'>".(!empty(number_format($baris->SaldoFaktur)) ? number_format($baris->SaldoFaktur) : 0)."</p>";
		         $row[] = "<p align='right'>".(!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0)."</p>";
		         $row[] = (!empty($baris->Giro) ? $baris->Giro : "");		         
		        $data[] = $row;
		        $i++;
		      }
		      $output['data'] = $data;
			// $output = array(
			// 				"data" => $data,
			// 		);
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	// public function listDataPelunasan($tipe = null)
	// {	
	// 	if ($this->logged) 
	// 	{
	// 		$data = array();        
	// 		$list = $this->Model_pelunasan->listDataPelunasan($tipe);
	// 		$x = 0;
	// 		$i = 0;
	// 		$countCetak = 1;

	// 		 foreach ($list as $baris) {
	// 	        $x++;
	// 	        $row = array();
	// 	        // $row[] = $x;
	// 	        // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NomorFaktur."'".')"><i class="fa fa-eye"></i> Lihat</button>';
	// 	        // $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
	// 	         $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
	// 	         // $row[] = (!empty($baris->UmurLunas) ? $baris->UmurLunas : "");
	// 	         $row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
	// 	         $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
	// 	         $row[] = (!empty($baris->NomorFaktur) ? $baris->NomorFaktur : "");
	// 	         $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
	// 	         $row[] = (!empty($baris->UmurFaktur) ? $baris->UmurFaktur : "");
	// 	         $row[] = (!empty($baris->KodePenagih) ? $baris->KodePenagih : "");
	// 	         $row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
	// 	         $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
	// 	         $row[] = (!empty($baris->KodeSalesman) ? $baris->KodeSalesman : "");
	// 	         $row[] = (!empty(number_format($baris->ValueFaktur)) ? number_format($baris->ValueFaktur) : 0);
	// 	         // $row[] = (!empty($baris->Cicilan) ? $baris->Cicilan : "");
	// 	         $row[] = (!empty(number_format($baris->SaldoFaktur)) ? number_format($baris->SaldoFaktur) : 0);
	// 	         $row[] = (!empty($baris->TipePelunasan) ? $baris->TipePelunasan : "");
	// 	         $row[] = (!empty($baris->Status) ? $baris->Status : "");
	// 	         if($baris->TipeDokumen == 'Retur' or $baris->TipeDokumen == 'CN'){
	// 	         	if($baris->ValuePelunasan < 0){
	// 	         		$row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
	// 	         	}else{
	// 	         		$row[] = (!empty(number_format($baris->ValuePelunasan*-1)) ? number_format($baris->ValuePelunasan*-1) : 0);
	// 	         	}	
	// 	         }else{
	// 	         	$row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
	// 	         }
	// 	         // $row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : "");
	// 	         $row[] = (!empty(number_format($baris->SaldoAkhir)) ? number_format($baris->SaldoAkhir) : 0);
	// 	         $row[] = (!empty($baris->Giro) ? $baris->Giro : "");
	// 	         $row[] = (!empty($baris->TglGiroCair) ? $baris->TglGiroCair : "");
	// 	         $row[] = (!empty(number_format($baris->ValueGiro)) ? number_format($baris->ValueGiro) : 0);
	// 	         $row[] = (!empty(number_format($baris->value_pembulatan)) ? number_format($baris->value_pembulatan) : 0);
	// 	         $row[] = (!empty(number_format($baris->materai)) ? number_format($baris->materai) : 0);
	// 	         $row[] = (!empty(number_format($baris->value_transfer)) ? number_format($baris->value_transfer) : 0);
	// 	         $row[] = (!empty($baris->bank) ? $baris->bank : "");
		         
	// 	        $data[] = $row;
	// 	        $i++;
	// 	      }
	// 		$output = array(
	// 						"data" => $data,
	// 				);
	// 		//output to json format
	// 		echo json_encode($output);
	// 	}
	// 	else 
	// 	{	
	// 		redirect("auth/logout");
	// 	}
	// }

	public function datagiro()
	{	
		if ( $this->logged ) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y' )){
					$this->twig->display('pembelian/datagiro.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataGiro($cek = null)
	{	
		if ($this->logged) 
		{
			$data = array();   
			$list = $this->Model_pelunasan->listDataGiro();
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if($this->userGroup == 'CabangInkaso' or $this->userGroup == 'KSA' or $this->userGroup == 'Cabang'){
					if ($baris->StatusGiro == "Aktif") {
						if($baris->Giro == "" || $baris->Giro==null || $baris->Giro===false){ 
							$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Kliring" onclick="prosesGiro('."'".$baris->NoGiro."','Kliring'".')" disabled><i class="fa fa-check"></i> Kliring</button> <button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Batal" onclick="prosesGiro('."'".$baris->NoGiro."','Batal'".')" disabled><i class="fa fa-trash"></i> Batal</button>'; 
						}else { 
							$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Kliring" onclick="prosesGiro('."'".$baris->NoGiro."','Kliring'".')"><i class="fa fa-check"></i> Kliring</button> <button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Batal" onclick="prosesGiro('."'".$baris->NoGiro."','Batal'".')"><i class="fa fa-trash"></i> Batal</button>'; 
						}
					}else if($baris->StatusGiro == "Cair") {
						$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="Tolak" onclick="prosesGiro('."'".$baris->NoGiro."','Tolak'".')"><i class="fa fa-trash"></i> Tolak</button> ';
					}else{
						$row[] = '-';
					}
				}else{
					$row[] = '<p style="color:red;">User Tdk Ada Otorisasi</p>';
				}
				$pelunasan = $baris->ValueGiro - $baris->SisaGiro;
				$row[] = (!empty($baris->cabangG) ? $baris->cabangG : "");
	 			$row[] = (!empty($baris->StatusGiro) ? $baris->StatusGiro : "");
	 			$row[] = (!empty($baris->Tolak) ? $baris->Tolak : "");
	 			$row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
	 			$row[] = (!empty($baris->Bank) ? $baris->Bank : "");
	 			$row[] = (!empty($baris->NoGiro) ? $baris->NoGiro : "");
	 			$row[] = (!empty($baris->TglJTO) ? $baris->TglJTO : "");
	 			$row[] = (!empty($baris->TglCair) ? $baris->TglCair : "");
	 			$row[] = "<p align='right'>".(!empty($baris->ValueGiro) ? number_format($baris->ValueGiro) : number_format(0))."</p>";
	 			$row[] = "<p align='right'>".(number_format($pelunasan))."</p>";
	 			$row[] = "<p align='right'>".(!empty($baris->SisaGiro) ? number_format($baris->SisaGiro) : number_format(0))."</p>";
	 			
				$data[] = $row;
				$i++;
			}
			$output = array(
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function prosesGiro()
	{
		if ($this->logged) 
		{
			$tipe = $_POST['tipe'];
			$nodih = $_POST['nodih'];
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y')){
					if ($tipe == "Kliring") {
					$status = $this->Model_pelunasan->prosesGiroKliring($nodih);
					}
					else if ($tipe == "Tolak") {
						$status = $this->Model_pelunasan->prosesGiroTolak($nodih);
					}
					else if ($tipe == "Batal") {
						$status = $this->Model_pelunasan->prosesGiroBatal($nodih);
					}
					echo json_encode(array("status" => $status));
				}else{
					$respon =false;
					echo json_encode(array("status" => $respon));
				}
				
			}else if($cektglstok == 0){
				$respon =false;
				echo json_encode(array("status" => $respon));
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listFakturDIH()
	{	
		if ($this->logged) 
		{
			$data = array();        
			$list = $this->Model_pelunasan->listFakturDIH();
			$x = 0;
			$i = 0;
			$countCetak = 1;
			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				$row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
	 			$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
	 			$row[] = (!empty($baris->Penagih) ? $baris->Penagih : "");
	 			$row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
	 			$row[] = (!empty($baris->TglDIH) ? $baris->TglDIH : "");
	 			$row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
	 			$row[] = (!empty($baris->Total) ? $baris->Total : "");
	 			$row[] = (!empty($baris->Saldo) ? $baris->Saldo : "");	 
	 			$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
	 			$row[] = (!empty($baris->nama) ? $baris->nama : "");			
				$data[] = $row;
				$i++;
			}
			$output = array(
							"data" => $data,
							"total"=>$i
					);
			//output to json format
			// echo json_encode($data);
			return $output;
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function prosesFakturDIH()
	{
		if ($this->logged) 
		{
			$data = (object)$this->input->post();
			$data = json_decode(json_encode($data),true);
			$datas = $data['dataDIH'];
			$i=0;
			foreach ($datas as $key => $params) 
        	{
        		$i++;
        	   	$status = $this->Model_pelunasan->prosesFakturDIH($params, $key,$i);
        	   	echo json_encode(array("status" => $status));
        	}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function buatpelunasanparsial()
	{	
		if ( $this->logged ) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			if($cektglstok == 1){
				if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y')){
					$this->content['datas']=$this->listFakturDIH()["data"];
					$this->content['total']=$this->listFakturDIH()["total"];
					$this->content['data_bank']=$this->get_glbank();
					$this->twig->display('pembelian/buatpelunasanparsial.html', $this->content);
				}else{
					redirect("main");
				}
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function datadihdetail()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datadihdetail.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function listDataDIHdetail($cek = null)
	{	
		if ($this->logged) 
		{
			/*Menagkap semua data yang dikirimkan oleh client*/

			/*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
			server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
			sesuai dengan urutan yang sebenarnya */
			$data = [];

			$bySearch = "";
			$byLimit = "";
			$draw=$_REQUEST['draw'];

			/*Jumlah baris yang akan ditampilkan pada setiap page*/
			$length = "";
			if (isset($_REQUEST['length'])) {
				if($_REQUEST['length'] != -1){
					$length=$_REQUEST['length'];
				}
			}

			/*Offset yang akan digunakan untuk memberitahu database
			dari baris mana data yang harus ditampilkan untuk masing masing page
			*/
			$start=$_REQUEST['start'];

			/*Keyword yang diketikan oleh user pada field pencarian*/
			$search=$_REQUEST['search']["value"];


			/*Menghitung total data didalam database*/
			if($cek=='all')
				$total = $this->Model_pelunasan->listDataDIHdetail()->num_rows();
			else
				$total = $this->Model_pelunasan->listDataDIHdetail('','','Open')->num_rows();

			/*Mempersiapkan array tempat kita akan menampung semua data
			yang nantinya akan server kirimkan ke client*/
			$output=array();

			/*Token yang dikrimkan client, akan dikirim balik ke client*/
			$output['draw']=$draw;

			/*
			$output['recordsTotal'] adalah total data sebelum difilter
			$output['recordsFiltered'] adalah total data ketika difilter
			Biasanya kedua duanya bernilai sama, maka kita assignment 
			keduaduanya dengan nilai dari $total
			*/
			$output['recordsTotal']=$output['recordsFiltered']=$total;

			/*disini nantinya akan memuat data yang akan kita tampilkan 
			pada table client*/
			$output['data']=array();


			/*Jika $search mengandung nilai, berarti user sedang telah 
			memasukan keyword didalam filed pencarian*/
			if($search!=""){
			$bySearch = " and (NoDIH like '%".$search."%' or Penagih like '%".$search."%' or NoFaktur like '%".$search."%' or Status like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or TglDIH like '%".$search."%')";
			}


			/*Lanjutkan pencarian ke database*/
			if($length != 0 || $length != "")
				$byLimit = " LIMIT ".$start.", ".$length;
			/*Urutkan dari alphabet paling terkahir*/
			// $this->db->order_by('name','asc');
			if($cek=='all')
				$query=$this->Model_pelunasan->listDataDIHdetail($bySearch, $byLimit)->result();
			else
				$query=$this->Model_pelunasan->listDataDIHdetail($bySearch, $byLimit, "Open")->result();


			/*Ketika dalam mode pencarian, berarti kita harus
			'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
			yang mengandung keyword tertentu
			*/
			if($search!=""){			
				if($cek=='all')
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listDataDIHdetail($bySearch)->num_rows();
				else
					$output['recordsTotal']=$output['recordsFiltered'] = $this->Model_pelunasan->listDataDIHdetail($bySearch, '', "Open")->num_rows();
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($query as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				// $row[] = '<p align="center"><input class="ceklis" type="checkbox" name="list['.$i.']" id="list'.$i.'" value="'.$baris->NoDIH.'" status="'.$baris->Status.'"></p>';
				// if ($baris->statusPusat == 'Gagal') {
				// 	$row[] = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDIH."'".')"><i class="fa fa-check"></i> Proses</button>';
				// }
				// else
				// {
				// 	$row[] = 'Berhasil';
				// }
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDIH."'".','."'".$baris->Status."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				// $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Print" onclick="cetak('."'".$baris->NoDIH."'".','."'".$baris->Status."'".')"><i class="fa fa-eye"></i> Print</button>';
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
	 			$row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
	 			$row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
	 			$row[] = (!empty($baris->Penagih) ? $baris->Penagih : "");
	 			$row[] = (!empty($baris->TglDIH) ? $baris->TglDIH : "");
	 			$row[] = (!empty($baris->TglDIH) ? $baris->TglFaktur : "");
	 			$row[] = (!empty($baris->Status) ? $baris->Status : "");
	 			$row[] = "<p align='right'>".(!empty(number_format((float)$baris->Total,2)) ? number_format((float)$baris->Total,2) : "")."</p>";
	 			$row[] = "<p align='right'>".(!empty(number_format((float)$baris->Saldo,2)) ? number_format((float)$baris->Saldo,2) : "")."</p>";	
	 			// $row[] = "<p align='right'>".(!empty(number_format((float)$baris->Sisa,2)) ? number_format((float)$baris->Sisa,2) : "")."</p>";	
				$data[] = $row;
				$i++;
			}
			$output['data'] = $data;
			//output to json format
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}
}