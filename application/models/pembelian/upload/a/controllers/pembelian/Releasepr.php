<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Releasepr extends CI_Controller {

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
		$this->load->model('pembelian/Model_releasepr');
		$this->load->model('pembelian/Model_bpb');
		$this->load->library('format');
		$this->load->library('export');
		$this->load->library('owner');
		$this->load->library('excel');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	public function approval()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/releasepr.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataReleasePR()
	{	
		$list = $this->Model_releasepr->listData()->result();
        $data = array();
		foreach ($list as $key) {
			$row = array();
 			$row[] = (!empty($key->noUsulan) ? $key->noUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Value_Usulan ) ? $key->Value_Usulan  : "");			
 			$row[] = (!empty($key->status) ? $key->status : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->noUsulan."'".')" id="View"><i class="fa fa-eye"></i> View</a>'; 			
			$row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->noUsulan."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>'; 
			if ($key->status != 'Closed') {
				//add html for action	
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approveData('."'".$key->noUsulan."'".')"><i class="fa fa-check"></i> Approve</a>';
				$row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Reject" onclick="rejectData('."'".$key->noUsulan."'".')"><i class="fa fa-times"></i> Reject</a>'; 
			}
			else
			{
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

	public function releaseDataPR()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$valid = $this->Model_releasepr->releaseData($No);

			echo json_encode(array("status" => True));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function dataUsulan()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_releasepr->dataUsulan($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function rejectDataPR()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['No'];
			$Alasan = $_POST['Alasan'];
			$valid = $this->Model_releasepr->rejectData($No, $Alasan);

			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }

    public function dataprpo()
	{	
		if ( $this->logged ) 
		{
			$this->content['pr']= $this->Model_bpb->PRPO_Pusat();
			$this->twig->display('pembelian/dataprpo.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
	public function datapr()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/datapr.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataPR()
	{	
		$list = $this->Model_releasepr->listDataPR();
        $data = array();
        $i = 0;
		foreach ($list as $key) {
			$row = array();
			$row[] = '<p align="center"><input type="checkbox" class="ceklis" name="list['.$i.']" id="list'.$i.'" value="'.$key->No_PR.'"></p>';
			$row[] = (!empty($key->Tipe) ? $key->Tipe : "");
			$row[] = (!empty($key->No_PR) ? $key->No_PR : "");
			$row[] = (!empty($key->Tgl_PR) ? $key->Tgl_PR : "");
 			$row[] = (!empty($key->No_Usulan) ? $key->No_Usulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Supplier_2) ? $key->Supplier_2 : "");
 			$row[] = (!empty($key->Total_PR) ? $key->Total_PR  : "");			
 			$row[] = (!empty($key->Status_PR) ? $key->Status_PR : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->No_PR."'".')" id="View"><i class="fa fa-eye"></i> View</a>';
			$row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->No_PR."'".','."'".$key->Tipe."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>'; 
			$data[] = $row;
			$i++;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function listDataPRPO()
	{	
		$list = $this->Model_releasepr->listDataPRPO();
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = (!empty($key->Tipe) ? $key->Tipe : "");
			$row[] = (!empty($key->noPO) ? $key->noPO : "");
			$row[] = (!empty($key->Tgl_PO) ? $key->Tgl_PO : "");
			$row[] = (!empty($key->no) ? $key->no : "");
 			$row[] = (!empty($key->noUsulan) ? $key->noUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Total_PO) ? $key->Total_PO  : "");			
 			$row[] = (!empty($key->status) ? $key->status : "");
 			$row[] = (!empty($key->flag_suratjalan) ? $key->flag_suratjalan : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->noPO."'".')" id="View"><i class="fa fa-eye"></i> View</a>';
			$row[] = '<a class="btn btn-sm btn-default" title="Print" onclick="cetak('."'".$key->noPO."'".','."'".$key->Tipe."'".')" id="cetak"><i class="fa fa-eye"></i> Print</a>'; 
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function dataDetailPR()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_releasepr->dataPR($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function dataDetailPRPO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_releasepr->dataPRPO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function updateDataPOPusat()
	{
		if ($this->logged) 
		{  
			$tipe = $_POST['tipe'];
			if($tipe=='all'){
				$nopo="";
				$status = $this->Model_releasepr->updateDataPOPusat();
			}else{
				$nopo=$_POST['nopo'];
				$status = $this->Model_releasepr->updateDataPOPusat1($nopo);
			}
			
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function updateDataPRPusat()
	{
		if ($this->logged) 
		{
			$status = $this->Model_releasepr->updateDataPRPusat();
			echo json_encode(array("status" => TRUE, "ket" => $status));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function dataprpoclosed()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/dataprpoClosed.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function listDataPRPOClosed()
	{	
		$list = $this->Model_releasepr->listDataPRPOClosed();
        $data = array();
		foreach ($list as $key) {
			$row = array();
			$row[] = (!empty($key->Tipe) ? $key->Tipe : "");
			$row[] = (!empty($key->noPO) ? $key->noPO : "");
			$row[] = (!empty($key->Tgl_PO) ? $key->Tgl_PO : "");
			$row[] = (!empty($key->no) ? $key->no : "");
 			$row[] = (!empty($key->noUsulan) ? $key->noUsulan : "");
 			$row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
 			$row[] = (!empty($key->Supplier) ? $key->Supplier : "");
 			$row[] = (!empty($key->Total_PO) ? $key->Total_PO  : "");			
 			$row[] = (!empty($key->status) ? $key->status : "");
 			$row[] = '<a class="btn btn-sm btn-default" title="View" onclick="view('."'".$key->noPO."'".')" id="View"><i class="fa fa-eye"></i> View</a>';
			$row[] = '<a class="btn btn-sm btn-info" title="Close PO" onclick="closed('."'".$key->noPO."'".','."'".$key->Tipe."'".')" id="closed"><i class="fa fa-eye"></i> Close PO</a>'; 
			$data[] = $row;
		}

		$output = array(
                        "data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function closedataPO()
	{          
        if ($this->logged) 
		{	
			$No = $_POST['no'];
			$valid = $this->Model_releasepr->closedataPO($No);

			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("main/auth/logout");
		}
    }
}






// PROSES MELALUI EMAIL

  //   public function emailSend($name)
  //   {
  //   	$valid = $this->sendEmail('noreply@saptasaritama.co.id','ini subjek','ini isi pesan',$name);
  //   }

  //   private function sendEmail($email,$subject,$message,$name)
  //   {   
		// $i = 0;
		// $filePath = 'C:/Users/Public/Documents/UsulanBeli/'.$name; 				

		// $config = Array(
		// 	'protocol' => 'smtp',
		// 	'smtp_host' => 'mail.saptasaritama.co.id',
		// 	'smtp_port' => 25,
		// 	'smtp_user' => 'noreplay_bdg@saptasaritama.co.id', 
		// 	'smtp_pass' => 'sst777bdg', 
		// 	'mailtype' => 'html',
		// 	'charset' => 'iso-8859-1',
		// 	'wordwrap' => TRUE
		// );

		// while (!file_exists($filePath) && $i < 60)
		// {
		// 	$i++;
		// 	sleep(1);	
		// }

		// $this->load->library('email');
		// $this->email->initialize($config);
		// $this->email->set_newline("\r\n");
		// $this->email->from('noreplay_bdg@saptasaritama.co.id');
		// $this->email->to($email);
		// $this->email->subject($subject);
		// $this->email->message($message);
		// $this->email->attach($filePath);

		// if($this->email->send())
		// {
		// 	return true;
		// }
		// else
		// {
		// 	return show_error($this->email->print_debugger());
		// }
  //   }

  //   private function toExcel($data)
  //   {
  //   	// Instantiate a new PHPExcel object 
  //   	if (!file_exists('C:/Users/Public/Documents/UsulanBeli')) {
		// 	    mkdir('C:/Users/Public/Documents/UsulanBeli', 0777, true);
		// 	}
		// $objPHPExcel = new PHPExcel();  
		// // Set the active Excel worksheet to sheet 0 
		// $objPHPExcel->setActiveSheetIndex(0);  
		// // Initialise the Excel row number 
		// $rowCount = 1;  

		// //start of printing column names as names of MySQL fields  
		// $column = 'A';
		// $list = $data->list_fields();
		// for ($i = 0; $i < $data->num_fields(); $i++)  
		// {
		//     $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $list[$i]);
		//     $column++;
		// }
		// //end of adding column names  

		// //start while loop to get data  
		// $rowCount = 2;  
		// $dt = $data->result_array();
		// foreach ($dt as $key => $value) 
		// {
		//     $column = 'A';
		//     for($j=0; $j<$data->num_fields();$j++)  
		//     {  
		//         if(!isset($dt[$key][$list[$j]]))  
		//             $value = NULL;  
		//         elseif ($dt[$key][$list[$j]] != "")  
		//             $value = strip_tags($dt[$key][$list[$j]]);  
		//         else  
		//             $value = "";  

		//         $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
		//         $column++;
		//     }  
		//     $rowCount++;
		// } 

		// $name = str_replace("/", "-", $dt[$key]['No_Usulan']);
		// $name = str_replace(":", "-", $name);
		// $name = $name.'.xls';

		// // Redirect output to a client’s web browser (Excel5) 
		// header('Content-Type: application/vnd.ms-excel'); 
		// header('Content-Disposition: attachment;filename="'.$name); 
		// header('Cache-Control: max-age=0'); 
		// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
		// $objWriter->save('C:/Users/Public/Documents/UsulanBeli/'.$name);
		// return $name;
  //   }