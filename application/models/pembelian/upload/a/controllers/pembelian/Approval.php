<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval extends CI_Controller {

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

	public function dataoutstandingdo()
	{	
		if ( $this->logged ) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			if($cektglstok == 1){
				$DoOpen = $this->Model_closing->getDOopen();
				$DoKirim = $this->Model_closing->getDOKirim();
				$DoTerima = $this->Model_closing->getDOTerima();
				$DoOpen  = $DoOpen[0]->NoDO;
				$DoKirim = $DoKirim[0]->NoDO;
				$DoTerima = $DoTerima[0]->NoDO;
				$this->content['DoOpen'] = $DoOpen;
				$this->content['DoKirim'] = $DoKirim;
				$this->content['DoTerima'] = $DoTerima;
				$this->twig->display('pembelian/dataoutstandingdo.html', $this->content);
			}else if($cektglstok == 0){
				redirect("main");
			}
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datalimit()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datalimit.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	

	public function datatop()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datatop.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datadc()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datadc.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datadp()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datadp.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datalimitTOP()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datalimitTOP.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function dataup()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/dataup.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datalimitbeli()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/approvallimitbeli.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function datakirimrelokasi()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/approvalkirimrelokasi.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataApproval($status = NULL)
	{
		$byLimit = "";
		$byfilter = "";

		if (isset($_REQUEST['filter'])) {
			if($_REQUEST['filter'] != ""){
				$byfilter = " and Status = '".$_REQUEST['filter']."'";
			}
		}

		$draw=$_REQUEST['draw'];
		$start=$_REQUEST['start'];
		$length=$_REQUEST['length'];
		if($_REQUEST['length'] != -1){
			$length=$_REQUEST['length'];
			$byLimit = " LIMIT ".$start.", ".$length;
		}

		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		$query = $this->Model_approval->listDataApproval($search, $length, $start, $status, $byfilter, $byLimit);

		$total = $this->Model_approval->listDataApproval($search, $length, $start, $status, $byfilter, "")->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		
		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}

		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $baris => $row) {
				if ($this->userGroup == 'BM' or $this->userGroup == 'KSA') {
					if ($status == 'Limit') {
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->NoSO."'".', '."'Limit'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->NoSO."'".', '."'Limit'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
						$top_piutang = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="toppiutang('."'".$row->Pelanggan."'".')"><i class="fa fa-eye"></i> TOP Piutang</button>';
					}
					elseif ($status == 'TOP') {
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->NoSO."'".', '."'TOP'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;
						<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->NoSO."'".', '."'TOP'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
						$top_piutang = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="toppiutang('."'".$row->Pelanggan."'".')"><i class="fa fa-eye"></i> TOP Piutang</button>';
					}
					elseif ($status == 'DC') {
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->NoSO."'".', '."'DC'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->NoSO."'".', '."'DC'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
						$top_piutang = '-';
					}
					elseif ($status == 'DP') {
						$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->NoSO."'".', '."'DP'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->NoSO."'".', '."'DP'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
						$top_piutang = '-';
					}else{
						$statusPusat = "-";
						$top_piutang = '-';
					}
				}
				else {
					$statusPusat = "-";
					$top_piutang = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="toppiutang('."'".$row->Pelanggan."'".')"><i class="fa fa-eye"></i> TOP Piutang</button>';
				}
				$detail = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$approval = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="View Approval" onclick="approval('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				if($status == "Limit"){
					$Statusorder = $row->StatusLimit;
				}elseif ($status == 'TOP'){
					$Statusorder = $row->StatusTOP;
				}elseif ($status == 'DC'){
					$Statusorder = $row->StatusDiscCab;
				}elseif ($status == 'DP'){
					$Statusorder = $row->StatusDiscPrins;
				}else{
					$Statusorder = 'Lmt :'. $row->StatusLimit .'-Top :'. $row->StatusTOP .'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins ;
				}
				$output['data'][]=array($nomor_urut,$statusPusat,$detail,$approval,$row->NoSO,$row->TglSO,$row->Pelanggan,$row->NamaPelanggan,$row->Salesman,$Statusorder,$row->Status,$row->Acu,$row->CaraBayar,$row->TOP,$top_piutang,$row->TglJtoOrder,"<p align='right'>".number_format($row->Value)."</p>","<p align='right'>".number_format($row->Total)."</p>");
				$nomor_urut++;
			}
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","","","","","","","","","","");
		}
		echo json_encode($output);
	}

	public function listDoOutstanding()
	{
		$search=$_REQUEST['search']["value"];
		$query = $this->Model_approval->listDataDoOutstanling($search);
		$start=$_REQUEST['start'];
		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$cekdo = $this->db->query("select NoDO from trs_delivery_order_sales where NoSO ='".$row->NoSO."' limit 1")->num_rows();
				if($cekdo < 1){
					$proses = '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$row->NoSO."'".', '."'Limit'".')"><i class="fa fa-check"></i> Proses</button>';

					if ($this->session->userdata('username') == 'cabang' && preg_match("/COGS/i", $row->alasan_status)) {
						$update_cogs = '<button type="button" class="btn btn-sm btn-primary" href="javascript:void(0)" title="Proses" onclick="fix_cogs('."'".$row->NoSO."'".', '."'".$row->Keterangan2."'".')"><i class="fa fa-check"></i> Update COGS</button>';
					}else{
						$update_cogs = '';
					}

					$detail = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
					$output['data'][]=array($nomor_urut,$proses . $update_cogs,$detail,$row->NoSO,$row->TglSO,$row->Status,$row->alasan_status,$row->Pelanggan,$row->Salesman,$row->Rayon,$row->Status,$row->Acu,$row->CaraBayar,$row->ValueCashDiskon,$row->TOP,$row->TglJtoOrder,number_format($row->Gross),number_format($row->Potongan),number_format($row->Value),number_format($row->Ppn),number_format($row->Total));
					$nomor_urut++;
				}else{
					$output['data'][] = array("","","","","","","","","","","","","","","","","","","","","");
				}
			}
		}else{
			$output['data'][] = array("","","","","","","","","","","","","","","","","","","","","");
		}
		echo json_encode($output);
	}

    public function prosesDataApproval($status = NULL)
	{          
        if ($this->logged) 
		{	
			$NoSO = $_POST['No'];
			$tipe = $_POST['tipe'];
			$valid = $this->Model_approval->prosesData($NoSO, $status,$tipe);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

 
    public function prosesDataDoOutstanding()
	{          
        if ($this->logged) 
		{	
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			$NoSO = $_POST['No'];
			$x=[];
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='DO' limit 1")->row();
						$cek = $cek->counter;
						if($cek == '0'){
							$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='DO'");
							$valid = $this->Model_approval->prosesDataDoOutstanding($NoSO);
							$this->db->query("update mst_trans set counter = '0', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='DO'");
							$x = ["status"=>$valid, 'pesan'=>'sukses'];
							// echo json_encode($x);
						}else{
							$valid=false;
							$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
							
						}
					}else{
						$valid=false;
						$x =  ['status'=>$valid, 'pesan'=>'stok','user'=>$this->user];
					}

					
				}else if($cektglstok == 0){
					$valid=true;
					$x =  ['status'=>$valid, 'pesan'=>'stok','user'=>$this->user];
				}
				
				echo json_encode($x);
			}else{
				$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='DO' limit 1")->row();
				if($cek == '0'){
					$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='DO'");
					$valid = $this->Model_approval->prosesDataDoOutstanding($NoSO);
					$this->db->query("update mst_trans set counter = '0', user_trans ='".$this->user."',trans_date='".date('Y-m-d H:i:s')."' where nama_transaksi ='DO'");
					$x = ["status"=>$valid, 'pesan'=>'sukses'];
					// echo json_encode($x);
				}else{
					$valid=false;
					$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
				}
				echo json_encode($x);
			}
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function datacn()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datausulancn.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataUsulanCN($cek = null)
	{	
		if ($this->logged) 
		{
	        $data = array(); 

			if($cek=='all')
			{
				$list = $this->Model_approval->listDataUsulanCN();
			}else
			{
				$list = $this->Model_approval->listDataUsulanCN('Usulan');		
			}
			$x = 0;
			$i = 0;
			$countCetak = 1;

			foreach ($list as $baris) {
				$x++;
				$row = array();
				$row[] = $x;
				if($cek=='all'){
					$row[] = "-";
					$row[] = "-";

				}else{
				if ($this->userGroup == 'BM') {
					$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="Proses" onclick="prosesData('."'".$baris->NoDokumen."'".')"><i class="fa fa-check"></i>Proses</button>';
					$row[] = '<button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="prosesRejectData('."'".$baris->NoDokumen."'".')"><i class="fa fa-close"></i></i>Reject</button>';
				}else{
					$row[] = "-";
					$row[] = "-";
				}
			}
				$row[] = '<button type="button" class="btn btn-sm btn-warning" href="javascript:void(0)" title="View Detail" onclick="view('."'".$baris->NoDokumen."'".')"><i class="glyphicon glyphicon-eye-open"></i>Lihat</button>';
				$row[] = (!empty($baris->Cabang) ? $baris->Cabang : "-");
				$row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
	 			$row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
	 			$row[] = (!empty($baris->tglbuat) ? $baris->tglbuat : "");
	 			if($baris->Approve_BM == "Approve"){
	 				$row[] = (!empty($baris->TanggalCNDN) ? $baris->TanggalCNDN : "");
	 				
	 			}else{
	 				$row[] = "-";
	 			}
	 			$row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan ." ~ " .$baris->NamaPelanggan : "");
	 			// $row[] = (!empty($baris->Faktur) ? $baris->Faktur : "");
	 			$row[] = (!empty($baris->StatusCNDN) ? $baris->StatusCNDN : "");
	 			if($baris->StatusCNDN == 'Usulan'){
	 				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Proses" onclick="prosesDatapusat('."'".$baris->NoDokumen."'".')"><i class="fa fa-check"></i> Proses</a>';
	 			}else{
	 				$row[] = "-";
	 			}
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

	public function updateDataApproveDO()
	{
		if ($this->logged) 
		{
			$status = $this->Model_bpb->updateDataApproveDO();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function detailDataUsulanCN($no = null)
	{	
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_approval->detailDataUsulanCN($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function prosesDataUsulanCN()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			// $NoFaktur = $_POST['faktur'];
			$valid = $this->Model_approval->prosesDataUsulanCN($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function prosesDataRejectUsulanCN()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			// $NoFaktur = $_POST['faktur'];
			$valid = $this->Model_approval->prosesDataRejectUsulanCN($No);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function listDatalimitbeli($cek = null)
    {   
 		$list = $this->Model_approval->listDatalimitbeli($cek)->result();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->Waktu_Usulan) ? $key->Waktu_Usulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = "<p align='right'>".(!empty(number_format($key->Limit )) ? number_format($key->Limit)  : 0)."</p>";			
 			$row[] = "<p align='right'>".(!empty(number_format($key->Limit_Outstanding)) ? number_format($key->Limit_Outstanding) : 0)."</p>";
 			$row[] = "<p align='right'>".(!empty(number_format($key->Limit_Usulan)) ? number_format($key->Limit_Usulan) : 0)."</p>";
 			// $row[] = '<a class="btn btn-sm btn-success" title="view Approval" onclick="approval('."'".$key->Prinsipal."'".','."'".$key->Waktu_Usulan."'".')" id="approve"><i class="fa fa-eye"></i>View</a>'; 
 			if($cek !='all'){
 				// $row[] = 'BM :'. $key->Approve_BM .' || RBM :' .$key->Approve_RBM .' || NSM :'.$key->Approve_NSM.' || Pst :'.$key->Approve_pusat;
	 			if ($this->userGroup == 'BM'){
	 				$row[] = 'BM :' .$key->Approve_BM;
	 				if($key->Approve_BM != ""){
	 					$row[] = '-';
	 				}else{
	 					$row[] = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$key->Prinsipal."'".','."'".$key->Waktu_Usulan."','Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$key->Prinsipal."'".','."'".$key->Waktu_Usulan."','Reject'".'><i class="fa fa-eye"></i> Reject</a>'; 
	 				}
	 			}else if($this->userGroup == 'RBM') {
	 				$row[] = 'RBM :' .$key->Approve_RBM;
	 				if($key->Approve_RBM != ""){
	 					$row[] = '-';
	 				}else{
	 					$row[] = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$key->Prinsipal."'".','."'".$key->Waktu_Usulan."','Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$key->Prinsipal."'".','."'".$key->Waktu_Usulan."','Reject'".'><i class="fa fa-eye"></i> Reject</a>'; 
	 				}
	 				
		 		}else{
		 			$row[] = 'BM :'. $key->Approve_BM .' || RBM :' .$key->Approve_RBM .' || NSM :'.$key->Approve_NSM.' || Pst :'.$key->Approve_pusat;
		 			$row[] = '-';
		 		}
 			}else{
 				$row[] = 'BM :'. $key->Approve_BM .' || RBM :' .$key->Approve_RBM .' || NSM :'.$key->Approve_NSM.' || Pst :'.$key->Approve_pusat;
 				$row[] = '-';
 			}
 			
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
    }

    public function prosesDatalimitbeli()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$status = $_POST['status'];
			$tgl = $_POST['tgl'];
			$valid = $this->Model_approval->prosesDatalimitbeli($No,$status,$tgl);

			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function viewapproval()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_approval->viewapproval($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function viewapprovallimitbeli()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$tgl = $_POST['tgl'];
			$data = $this->Model_approval->viewapprovallimitbeli($no,$tgl);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function updateDataLimitBeliPusat()
	{
		if ($this->logged) 
		{
			// $no = $_POST['no'];
			$data = $this->Model_approval->updateDataLimitBeliPusat();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listDatakirimrelokasi($status = null)
    {   
    	if (empty($status)) {
	 		$list = $this->Model_approval->listDatakirimrelokasi()->result();
    	}else{
    		$list = $this->Model_approval->listDatakirimrelokasiPusat()->result();
    	}
    	
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->Tgl_kirim) ? $key->Tgl_kirim : "");
 			$row[] = (!empty($key->Cabang_Penerima) ? $key->Cabang_Penerima : "");
 			$row[] = (!empty($key->No_Relokasi ) ? $key->No_Relokasi  : "");			
 			$row[] = (!empty($key->Status_kiriman) ? $key->Status_kiriman : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Value) ? $key->Value : "");
 			$row[] = (!empty($key->Biayakirim) ? $key->Biayakirim : "");
 			$row[] = (!empty($key->ValueCR) ? number_format($key->ValueCR,2) : "");
 			$row[] = (!empty($key->App_BM_Status) ? $key->App_BM_Status : "");
 			$row[] = '<a class="btn btn-sm btn-success" title="view Data" onclick="view('."'".$key->No_Relokasi."'".')" id="approve"><i class="fa fa-eye"></i>View</a>'; 
 			$row[] = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$key->No_Relokasi."','Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$key->No_Relokasi."','Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>'; 
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
    }

     public function prosesDatakirimrelokasi($jenis = NULL)
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$status = $_POST['status'];
			$cektglstok = $this->Model_main->cek_tglstoktrans();
			$dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			$cekaktif = $this->Model_main->cek_closingactive();
			$date = date('Y-m-d');
			if($cekaktif->flag_aktif == 'Y'){
				if($cektglstok == 1){
					if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
						$valid = $this->Model_approval->prosesDatakirimrelokasi($No,$status,$jenis);
						echo json_encode(array("status" => $valid));
					}else{
						$data=false;
						echo json_encode(array("status" => $data));
					}
				}else if($cektglstok == 0){
					$data=false;
					echo json_encode(array("status" => $data));
				}
			}else{
				$valid = $this->Model_approval->prosesDatakirimrelokasi($No,$status,$jenis);
				echo json_encode(array("status" => $valid));
			}
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function dataDetailRelokasiKirim()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_relokasi->dataDetailRelokasiKirim($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listDataApproval_UP(){
    	if ($this->logged) 
		{	
			$data = array();
	        $output=array();
	        $bySearch = "";
	        /*Jumlah baris yang akan ditampilkan pada setiap page*/
	        $length=$_REQUEST['length'];
	        $start=$_REQUEST['start'];
	        $search=$_REQUEST['search']["value"];

	        if($search!=""){
	            $bySearch = " and (Kode like '%".$search."%' or Pelanggan like '%".$search."%' or Nama_Faktur like '%".$search."%' or Tipe_Pelanggan like '%".$search."%' or Jenis_Pelanggan like '%".$search."%' or Alamat like '%".$search."%' or Alamat2 like '%".$search."%' or Alamat_Kirim like '%".$search."%' or Alamat_Kirim like '%".$search."%'  or Kota like '%".$search."%')";
	        }

	        $total = $this->Model_approval->listDataApproval_UP()->num_rows();

			$byLimit = " LIMIT ".$start.", ".$length;
	        $no = $_POST['start'];
	        $list=$this->Model_approval->listDataApproval_UP($bySearch, $byLimit)->result();
	        foreach ($list as $pelanggan) {
             $no++;
             $row = array();
             $row[] = $no;
             $row[] = (!empty($pelanggan->Approve_SPV) ? $pelanggan->Approve_SPV : "NULL");
             $row[] = (!empty($pelanggan->Approve_KSA) ? $pelanggan->Approve_KSA : "NULL");
             $row[] = (!empty($pelanggan->Approve_APJ) ? $pelanggan->Approve_APJ : "NULL");
             $row[] = (!empty($pelanggan->Approve_BM) ? $pelanggan->Approve_BM : "NULL");
             $row[] = (!empty($pelanggan->Cabang) ? $pelanggan->Cabang : "NULL");
             $row[] = (!empty($pelanggan->Kode) ? $pelanggan->Kode : "NULL");
             $row[] = (!empty($pelanggan->Pelanggan) ? $pelanggan->Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Alamat) ? $pelanggan->Alamat : "NULL");
             $row[] = (!empty($pelanggan->Kota) ? $pelanggan->Kota : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pelanggan) ? $pelanggan->Tipe_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Grup_Pelanggan) ? $pelanggan->Grup_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pajak) ? $pelanggan->Tipe_Pajak : "NULL");
             $row[] = (!empty($pelanggan->NPWP) ? $pelanggan->NPWP : "NULL");
             $row[] = (!empty($pelanggan->Limit_Kredit) ? $pelanggan->Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->TOP) ? $pelanggan->TOP : "NULL");
             $row[] = (!empty($pelanggan->Salesman) ? $pelanggan->Salesman : "NULL");
             $row[] = (!empty($pelanggan->Area) ? $pelanggan->Area : "NULL");
             $row[] = (!empty($pelanggan->Status_Update) ? $pelanggan->Status_Update : "NULL");
             $row[] = (!empty($pelanggan->Alamat_Pajak) ? $pelanggan->Alamat_Pajak : "NULL");
             $row[] = (!empty($pelanggan->Class) ? $pelanggan->Class : "NULL");
             $row[] = (!empty($pelanggan->Kategori_2) ? $pelanggan->Kategori_2 : "NULL");
             $row[] = (!empty($pelanggan->Kode_Prov) ? $pelanggan->Kode_Prov : "NULL");
             $row[] = (!empty($pelanggan->Kat) ? $pelanggan->Kat : "NULL");
             $row[] = (!empty($pelanggan->No_SIPA) ? $pelanggan->No_SIPA : "NULL");
             $row[] = (!empty($pelanggan->ED_SIPA) ? $pelanggan->ED_SIPA : "NULL");

             $row[] = (!empty($pelanggan->Nama_Faktur) ? $pelanggan->Nama_Faktur : "NULL");
             $row[] = (!empty($pelanggan->Nama_Pajak) ? $pelanggan->Nama_Pajak : "NULL");
             $row[] = (!empty($pelanggan->Cabang_String) ? $pelanggan->Cabang_String : "NULL");
             $row[] = (!empty($pelanggan->Area_String) ? $pelanggan->Area_String : "NULL");
             $row[] = (!empty($pelanggan->Telp) ? $pelanggan->Telp : "NULL");
             $row[] = (!empty($pelanggan->Cara_Bayar) ? $pelanggan->Cara_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Saldo_Piutang) ? $pelanggan->Saldo_Piutang : "NULL");
             $row[] = (!empty($pelanggan->Tipe_2) ? $pelanggan->Tipe_2 : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Harga) ? $pelanggan->Tipe_Harga : "NULL");
             $row[] = (!empty($pelanggan->Aktif) ? $pelanggan->Aktif : "NULL");
             $row[] = (!empty($pelanggan->Rayon_1) ? $pelanggan->Rayon_1 : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Limit) ? $pelanggan->Status_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Usulan_Limit_Kredit) ? $pelanggan->Usulan_Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->History_Update) ? $pelanggan->History_Update : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_Limit) ? $pelanggan->Tgl_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_BM) ? $pelanggan->Approval_Limit_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_Pusat) ? $pelanggan->Approval_Limit_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Top) ? $pelanggan->Status_Usulan_Top : "NULL");
             $row[] = (!empty($pelanggan->Buka_TOP) ? $pelanggan->Buka_TOP : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_TOP) ? $pelanggan->Tgl_Usulan_TOP : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_BM) ? $pelanggan->Approval_TOP_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_Pusat) ? $pelanggan->Approval_TOP_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Riwayat_Bayar) ? $pelanggan->Riwayat_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Dokumen_Limit_TOP) ? $pelanggan->Dokumen_Limit_TOP : "NULL");
             $row[] = (!empty($pelanggan->Wilayah) ? $pelanggan->Wilayah : "NULL");
             $row[] = (!empty($pelanggan->DOW) ? $pelanggan->DOW : "NULL");
             $row[] = (!empty($pelanggan->Week) ? $pelanggan->Week : "NULL");
             $row[] = (!empty($pelanggan->Date) ? $pelanggan->Date : "NULL");
             $row[] = (!empty($pelanggan->Prioritas) ? $pelanggan->Prioritas : "NULL");
             $row[] = (!empty($pelanggan->Prins_Onf) ? $pelanggan->Prins_Onf : "NULL");
             $row[] = (!empty($pelanggan->Cab_Onf) ? $pelanggan->Cab_Onf : "NULL");
             $row[] = (!empty($pelanggan->smsP) ? $pelanggan->smsP : "NULL");
             $row[] = (!empty($pelanggan->Salesman2) ? $pelanggan->Salesman2 : "NULL");

            //add html for action
            $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$pelanggan->id."'".')"><i class="fa fa-pencil"></i> Edit</a>
                   <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$pelanggan->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
        
             $data[] = $row;
         }
			// log_message('error',print_r($ssss,true));
			$output = array(
                        // "draw" => $_POST['draw'],
                        "recordsTotal" => $total,
                        "recordsFiltered" => $total,
                        "data" => $data,
                );
			echo json_encode($output);
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    function prosesApprovalUP(){
    	$no = $_POST['no'];
    	$group = $_POST['group'];
    	$status_approve = $_POST['status'];
    	$fieldname = "";
    	if($this->userGroup == $group){
    		switch ($this->userGroup) {
    			case 'CabangSPV':
    				$fieldname1 = 'Approve_SPV';
    				$fieldname2 = 'Time_SPV';
    				break;
    			case 'KSA':
    				$fieldname1 = 'Approve_KSA';
    				$fieldname2 = 'Time_KSA';
    				break;
    			case 'CabangApoteker':
    				$fieldname1 = 'Approve_APJ';
    				$fieldname2 = 'Time_APJ';
    				break;
    			case 'BM':
    				$fieldname1 = 'Approve_BM';
    				$fieldname2 = 'Time_BM';
    				break;
    			
    			default:
    				# code...
    				break;
    		}
    		$proses=$this->Model_approval->prosesApprovalUP($no,$fieldname1,$fieldname2,$status_approve);
    		$x =  ["status"=>'sukses'];
    		echo json_encode($x);
    	}
    	else{
    		$x =  ["status"=>'gagal', 'pesan'=>'Hanya boleh di Approve oleh user group '.$group];
    		echo json_encode($x);
    		// return "error";
    	}
    }
	public function updateDataSODOPusat()
	{
		if ($this->logged) 
		{
			$cektglstok = $this->Model_main->cek_tglstoktrans();
            $dailystok  = $this->Model_main->cek_tglstokdaily();
			$date = date('Y-m-d');
			$x=[];
			 if($cektglstok == 1){
            	if(($dailystok->tgl_daily_closing == $date) and ($dailystok->flag_trans == 'Y') and ($dailystok->flag_stok == 'Y')){
            		$valid=true;
					$cek = $this->db->query("select counter from mst_trans where nama_transaksi ='DO' limit 1")->row();
					$cek = $cek->counter;
					if($cek == '0'){
						$this->db->query("update mst_trans set counter = '1', user_trans ='".$this->user."',trans_date='".date('Y-m-d  H:i:s')."' where nama_transaksi ='DO'");
						$valid = $this->Model_approval->updateDataSODOPusat();
						$this->db->query("update mst_trans set counter = '0', user_trans ='".$this->user."',trans_date='".date('Y-m-d  H:i:s')."' where nama_transaksi ='DO'");
						$x = ["status"=>$valid['valid'], 'pesan'=>'sukses'];
					}else{
						$valid=false;
						$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
						
					}
            	}else{
					$valid=false;
					$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
					echo json_encode($respon);
				}   
			}else if($cektglstok == 0){
				$valid=false;
				$x =  ['status'=>$valid, 'pesan'=>'gagal','user'=>$this->user];
				echo json_encode($respon);
			}
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function updateDataSOapprovalPusat()
	{
		if ($this->logged) 
		{
			// $status = $_POST['status'];
			$data = $this->Model_approval->updateDataSOapprovalPusat();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataapprovalusulanbeli()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/Approvalusulanbeli.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function prosesApprovalUsulanBeli()
	{          
        if ($this->logged) 
		{	
			$NoUsulan = $_POST['No'];
			$status = $_POST['status'];
			// $cabang = $_POST['cabang'];
			$valid = $this->Model_approval->prosesApprovalUsulanBeli($NoUsulan,$status);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function updateDataApprovalusulanbeli()
	{
		if ($this->logged) 
		{
			$data = $this->Model_approval->updateDataApprovalusulanbeli();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function listApprovalUsulanBeli($status=NULL)
	{
		$draw=$_REQUEST['draw'];
		$length=$_REQUEST['length'];
		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		$status = $_POST['filter'];
		$query = $this->Model_approval->listApprovalUsulanBeli($search, $length, $start,$status);
		$total=$query->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;

		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}
		$nomor_urut=$start+1;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if($status != 'all'){
					if ($this->userGroup == 'Apotik' || $this->userGroup == 'CabangApoteker') {
						if($row->Tipe == 'Rutin'){
							$statusPusat = "-";
							$Statususulan = "-";
						}else{
							if (empty($row->App_APJ_Cabang) or  $row->App_APJ_Cabang == '') {
								$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->No_Usulan."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->No_Usulan."'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
								$Statususulan = "No";
							}else{
								$statusPusat = "-";
								$Statususulan = "Approved";
							}
						}
					}elseif($this->userGroup == 'BM') {
						if (empty($row->App_BM_Status) or $row->App_BM_Status == '') {
							$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->No_Usulan."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->No_Usulan."'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
							$Statususulan = "No";
						}else{
							$statusPusat = "-";
							$Statususulan = "Approved";
						}

					}elseif($this->userGroup == 'RBM') {
						if (empty($row->App_RBM_Status) or  $row->App_RBM_Status== '') {
							$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->No_Usulan."'".','."'Approve'".')" id="approve"><i class="fa fa-eye"></i> Approve</a>&nbsp;&nbsp;<a class="btn btn-sm btn-warning" title="Reject" onclick="prosesData('."'".$row->No_Usulan."'".','."'Reject'".')" id="reject"><i class="fa fa-eye"></i> Reject</a>';
							$Statususulan = "No";
						}else{
							$statusPusat = "-";
							$Statususulan = "Approved";
						}

					}else {
						$statusPusat = "-";
						$Statususulan = 'Apt :'. $row->App_APJ_Cabang .' || BM :'. $row->App_BM_Status .' || RBM :' .$row->App_RBM_Status .' || Apt Pst :'.$row->App_APJ_Pst_status.' || Pst :'.$row->App_pusat_status;
					}

				}else{
					$statusPusat = "-";
					$Statususulan = 'Apt :'. $row->App_APJ_Cabang .' || BM :'. $row->App_BM_Status .' || RBM :' .$row->App_RBM_Status .' || Apt Pst :'.$row->App_APJ_Pst_status.' || Pst :'.$row->App_pusat_status;
				}
				
				$detail = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$row->No_Usulan."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$output['data'][]=array($nomor_urut,$statusPusat,$detail,$row->Cabang,$row->No_Usulan,$row->added_time,$row->Tipe,$row->Prinsipal,$row->Status_Usulan,$Statususulan,number_format($row->Value_Usulan));
				$nomor_urut++;
				
			}			
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","");
		}
		echo json_encode($output);
	}

	public function viewpiutangpelanggan()
	{
		if ($this->logged) 
		{
			$pelanggan = $_POST['pelanggan'];
			$data = $this->Model_approval->viewpiutangpelanggan($pelanggan);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function cetak_top_limit(){
		if ($this->logged) 
		{
			$no_so = $_POST['no'];
			$data = $this->Model_approval->cetak($no_so);
			// log_message('error',print_r($data,true));
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function updateDataCNDNPusat()
	{
		if ($this->logged) 
		{
			// $status = $_POST['status'];
			$data = $this->Model_approval->updateDataCNDNPusat();
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function prosesDataFakturCNDN()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_faktur->prosesDataFakturCNDN($No);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }
    public function listDataApprovalAll($status = NULL)
	{
		$byLimit = "";
		$byfilter = "";
		if (isset($_REQUEST['filter'])) {
			if($_REQUEST['filter'] != ""){
				$byfilter = " and Status = '".$_REQUEST['filter']."'";
			}
		}

		$draw=$_REQUEST['draw'];
		$start=$_REQUEST['start'];
		$length=$_REQUEST['length'];
		if($_REQUEST['length'] != -1){
			$length=$_REQUEST['length'];
			$byLimit = " LIMIT ".$start.", ".$length;
		}

		$start=$_REQUEST['start'];
		$search=$_REQUEST['search']["value"];
		$query = $this->Model_approval->listDataApprovalAll($search, $length, $start, $status, $byfilter, $byLimit);

		$total = $this->Model_approval->listDataApprovalAll($search, $length, $start, $status, $byfilter, "")->num_rows();
		$output=array();
		$output['draw']=$draw;
		$output['recordsTotal']=$output['recordsFiltered']=$total;
		
		if($search!=""){
		$output['recordsTotal']=$output['recordsFiltered']=$query->num_rows();
		}

		$nomor_urut=$start+1;
		$i = 0;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $baris => $row) {
				$sLimit 	= strtoupper($row->StatusLimit);
				$sTOP 		= strtoupper($row->StatusTOP);
				$sDiscCab 	= strtoupper($row->StatusDiscCab);
				$sDiscPrins = strtoupper($row->StatusDiscPrins);
				if ($this->userGroup == 'BM') {
					if(($row->Approve_KSA == 'Y' or $row->Approve_KSA == 'R') and $sLimit == 'LIMIT' and $row->Approve_BM == 'N'){
						$ceklimit1 = '<p align="center" style="background-color:#91a832;color:white"><input type="radio" id="rlimit1['.$i.']" name="Radiolimit['.$i.']" value="Y"/>App
						</p>';
						$ceklimit2 = '<p align="center" style="background-color:#91a832;color:white">
						<input type="radio" id="rlimit2['.$i.']" name="Radiolimit['.$i.']" value="R"/>Reject</p>';
						$ceklimit3 = '<p align="center" style="background-color:#91a832;color:white">
						<input type="radio" id="rlimit3['.$i.']" name="Radiolimit['.$i.']" value="N"/>Batal</p>';	
					}else{
						$ceklimit1 = '-';
						$ceklimit2 = '-';
						$ceklimit3 = '-';
					}			
					if(($row->Approve_TOP_KSA == 'Y' or $row->Approve_TOP_KSA == 'R') and $sTOP == 'TOP' and $row->Approve_TOP_BM == 'N' ){
						$cektop1 = '<p align="center" style="background-color:#32a855;color:white"><input type="radio" id="rtop1['.$i.']" name="RadioTop['.$i.']" value="Y"/>App
						</p>';
						$cektop2 = '<p align="center" style="background-color:#32a855;color:white">
						<input type="radio" id="rtop2['.$i.']" name="RadioTop['.$i.']" value="R"/>Reject</p>';
						$cektop3 = '<p align="center" style="background-color:#32a855;color:white">
						<input type="radio" id="rtop3['.$i.']" name="RadioTop['.$i.']" value="N"/>Batal</p>';
					}else{
						$cektop1 = '-';
						$cektop2 = '-';
						$cektop3 = '-';
					}	
				}else{
					if($sLimit == 'LIMIT'){
						if($row->Approve_KSA == 'N'){
							$ceklimit1 = '<p align="center" style="background-color:#91a832;color:white"><input type="radio" id="rlimit1['.$i.']" name="Radiolimit['.$i.']" value="Y"/>App
							</p>';
							$ceklimit2 = '<p align="center" style="background-color:#91a832;color:white">
							<input type="radio" id="rlimit2['.$i.']" name="Radiolimit['.$i.']" value="R"/>Reject</p>';
							$ceklimit3 = '<p align="center" style="background-color:#91a832;color:white">
							<input type="radio" id="rlimit3['.$i.']" name="Radiolimit['.$i.']" value="N"/>Batal</p>';	
						}else{
							$ceklimit1 = '-';
							$ceklimit2 = '-';
							$ceklimit3 = '-';
						}
						
					}else{
						$ceklimit1 = '-';
						$ceklimit2 = '-';
						$ceklimit3 = '-';
					}
					if($sTOP == 'TOP'){
						if($row->Approve_TOP_KSA == 'N'){
							$cektop1 = '<p align="center" style="background-color:#32a855;color:white"><input type="radio" id="rtop1['.$i.']" name="RadioTop['.$i.']" value="Y"/>App
							</p>';
							$cektop2 = '<p align="center" style="background-color:#32a855;color:white">
							<input type="radio" id="rtop2['.$i.']" name="RadioTop['.$i.']" value="R"/>Reject</p>';
							$cektop3 = '<p align="center" style="background-color:#32a855;color:white">
							<input type="radio" id="rtop3['.$i.']" name="RadioTop['.$i.']" value="N"/>Batal</p>';
						}else{
							$cektop1 = '-';
							$cektop2 = '-';
							$cektop3 = '-';
						}			
					}else{
						$cektop1 = '-';
						$cektop2 = '-';
						$cektop3 = '-';
					}
				}
				if ($this->userGroup == 'BM') {
					if($sDiscPrins == 'DP'){
						if($row->Approve_DiscPrins_BM == 'N'){
							$cekdp1 = '<p align="center" style="background-color:#327fa8;color:white"><input type="radio" id="rdp1['.$i.']" name="RadioDP['.$i.']" value="Y"/>App
							</p>';
							$cekdp2 = '<p align="center" style="background-color:#327fa8;color:white">
								<input type="radio" id="rdp2['.$i.']" name="RadioDP['.$i.']" value="R"/>Reject</p>';
							$cekdp3 = '<p align="center" style="background-color:#327fa8;color:white">
								<input type="radio" id="rdp3['.$i.']" name="RadioDP['.$i.']" value="N"/>Batal</p>';
						}else{
							$cekdp1 = '-';
							$cekdp2 = '-';
							$cekdp3 = '-';
						}	
					}else{
						$cekdp1 = '-';
						$cekdp2 = '-';
						$cekdp3 = '-';
					}
					if($sDiscCab == 'DC'){
						if($row->Approve_DiscCab_BM == 'N'){
							$cekdc1 = '<p align="center" style="background-color:#a83296;color:white"><input type="radio" id="rdc1['.$i.']" name="RadioDC['.$i.']" value="Y"/>App
							</p>';	
							$cekdc2 = '<p align="center" style="background-color:#a83296;color:white">
							<input type="radio" id="rdc2['.$i.']" name="RadioDC['.$i.']" value="R"/>Reject</p>';
							$cekdc3 = '<p align="center" style="background-color:#a83296;color:white">
							<input type="radio" id="rdc3['.$i.']" name="RadioDC['.$i.']" value="N"/>Batal</p>';
						}else{
							$cekdc1 = '-';
							$cekdc2 = '-';
							$cekdc3 = '-';
						}
						
					}else{
						$cekdc1 = '-';
						$cekdc2 = '-';
						$cekdc3 = '-';
					}	
				}else{
					$cekdp1 = '-';
					$cekdp2 = '-';
					$cekdp3 = '-';
					$cekdc1 = '-';
					$cekdc2 = '-';
					$cekdc3 = '-';
				}
				if ($this->userGroup == 'BM' or $this->userGroup == 'KSA') {
					$statusPusat = '<a class="btn btn-sm btn-info" title="Approve" onclick="prosesData('."'".$row->NoSO."'".', '."'LimitTOP'".','."'".$i."'".')" id="approve"><i class="fa fa-eye"></i> Proses</a>';
					$top_piutang = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="toppiutang('."'".$row->Pelanggan."'".')"><i class="fa fa-eye"></i> TOP Piutang</button>';
				}else{
					$statusPusat = "-";
					$top_piutang = '-';
				}

				$detail = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				$approval = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="View Approval" onclick="approval('."'".$row->NoSO."'".')"><i class="fa fa-eye"></i> Lihat</button>';
				if($status == "LimitTOP"){
					if($sLimit == 'LIMIT' and $sTOP == 'TOP' and $sDiscCab == 'DC' and $sDiscPrins == 'DP'){
						$Statusorder = 'Lmt :'. $row->StatusLimit .'-Top :'. $row->StatusTOP .'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'OK' and $sTOP == 'TOP' and $sDiscCab == 'DC' and $sDiscPrins == 'DP'){
						$Statusorder = 'Top :'. $row->StatusTOP .'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'OK' and $sTOP == 'OK' and $sDiscCab == 'DC' and $sDiscPrins == 'DP'){
						$Statusorder = 'DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'OK' and $sTOP == 'OK' and $sDiscCab == 'OK' and $sDiscPrins == 'DP'){
						$Statusorder = 'DP :'.$row->StatusDiscPrins;
				    //=========================================================
					}elseif($sLimit == 'LIMIT' and $sTOP == 'OK' and $sDiscCab == 'DC' and $sDiscPrins == 'DP'){
						$Statusorder = 'Lmt :'. $row->StatusLimit.'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'LIMIT' and $sTOP == 'OK' and $sDiscCab == 'OK' and $sDiscPrins == 'DP'){
						$Statusorder = 'Lmt :'. $row->StatusLimit.'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'LIMIT' and $sTOP == 'OK' and $sDiscCab == 'DC' and $sDiscPrins == 'OK'){
						$Statusorder = 'Lmt :'. $row->StatusLimit .'-DC :' .$row->StatusDiscCab;
					//=========================================================
					}elseif($sLimit == 'LIMIT' and $sTOP == 'TOP' and $sDiscCab == 'OK' and $sDiscPrins == 'DP'){
						$Statusorder = 'Lmt :'. $row->StatusLimit.'-TOP :' .$row->StatusTOP .'-DP :'.$row->StatusDiscPrins;
					}elseif($sLimit == 'LIMIT' and $sTOP == 'TOP' and $sDiscCab == 'OK' and $sDiscPrins == 'OK'){
						$Statusorder = 'Lmt :'. $row->StatusLimit.'-TOP :'.$row->StatusTOP;
					}elseif($sLimit == 'OK' and $sTOP == 'TOP' and $sDiscCab == 'OK' and $sDiscPrins == 'OK'){
						$Statusorder = 'TOP :'. $row->StatusTOP;
					//======================================================
					}elseif($sLimit == 'OK' and $sTOP == 'OK' and $sDiscCab == 'DC' and $sDiscPrins == 'DP'){
						$Statusorder = 'DP :'. $row->StatusDiscPrins.'-DC :'.$row->StatusDiscCab;
					}else{
						$Statusorder = 'Lmt :'. $row->StatusLimit .'-Top :'. $row->StatusTOP .'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins ;
					}
					//=======================================================
				}else{
					$Statusorder = 'Lmt :'. $row->StatusLimit .'-Top :'. $row->StatusTOP .'-DC :' .$row->StatusDiscCab .'-DP :'.$row->StatusDiscPrins ;
				}
				$output['data'][]=array($nomor_urut,$statusPusat,$ceklimit1,$ceklimit2,$ceklimit3,$cektop1,$cektop2,$cektop3,$cekdp1,$cekdp2,$cekdp3,$cekdc1,$cekdc2,$cekdc3,$detail,$top_piutang,$row->NoSO,$row->TglSO,$row->Pelanggan,$row->NamaPelanggan,$row->Salesman,$Statusorder,$row->Status,$row->Acu,$row->CaraBayar,$row->TOP,$approval,$row->TglJtoOrder,"<p align='right'>".number_format($row->Value)."</p>","<p align='right'>".number_format($row->Total)."</p>");
				$nomor_urut++;
				$i++;
			}
		}
		else{
			$output['data'][] = array("","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","");
		}
		echo json_encode($output);
	}
	public function prosesDataApprovalAll($No = NULL)
	{          
        if ($this->logged) 
		{	
			$xpld = explode('~',$No);
			$i = $xpld[0];
			$NoSO = $xpld[1];
			$params = (object)$this->input->post();
			$Radiolimit = (!empty($params->Radiolimit[$i]) ? $params->Radiolimit[$i] : "");
			$RadioTop = (!empty($params->RadioTop[$i]) ? $params->RadioTop[$i] : "");
			$RadioDP = (!empty($params->RadioDP[$i]) ? $params->RadioDP[$i] : "");
			$RadioDC = (!empty($params->RadioDC[$i]) ? $params->RadioDC[$i] : "");
			$valid = $this->Model_approval->prosesDataApprovalAll($NoSO,$Radiolimit,$RadioTop,$RadioDP,$RadioDC);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function fix_cogs()
	{          
        if ($this->logged) 
		{	
			$NoSO = $_POST['No'];
			$Keterangan2 = $_POST['Keterangan2'];
			
			$valid = $this->Model_approval->fix_cogs($NoSO,$Keterangan2);
			echo json_encode($valid);
			
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function datakirimrelokasiPusat()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/approvalkirimrelokasiPusat.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}