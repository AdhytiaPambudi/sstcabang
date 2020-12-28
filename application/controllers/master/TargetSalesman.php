<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TargetSalesman extends CI_Controller {
	var $content;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/Model_targetSalesman');
        $this->load->library('excel');
        $this->load->helper('download');
        $this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
        $this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
    }

    public function TargetSalesman(){
    	// echo "string";
    	if ($this->logged) 
		{
			// $this->content['Cabang'] = $this->Model_targetSalesman->listCabang()->result();
			$Cabang = $this->session->userdata('cabang');
			$this->content['Kode'] = $this->Model_targetSalesman->get_KodeSalesman($Cabang)->result();
			$this->content['Prinsipal'] = $this->Model_targetSalesman->listPrinsipal()->result();
			$this->content['Cabang'] = $Cabang;
			$this->content['getTahun'] =  $this->Model_targetSalesman->getTahun()->result();
			$this->content['bulan'] = date('M');
			$this->content['bulan1'] = date('m');
			$this->content['tahun1'] = date('Y');

			$this->twig->display('master/TargetSalesman.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
    }

    function LapTargetSalesman(){
    	if ($this->logged) 
		{
			// $this->content['Cabang'] = $this->Model_targetSalesman->listCabang()->result();
			$Cabang = $this->session->userdata('cabang');
			// $this->content['Kode'] = $this->Model_targetSalesman->get_KodeSalesman($Cabang)->result();
			$this->content['Prinsipal'] = $this->Model_targetSalesman->LaplistPrinsipal()->result();
			$this->content['Cabang'] = $Cabang;
			$this->content['getTahun'] =  $this->Model_targetSalesman->getTahun()->result();
			$this->content['bulan'] = date('M');
			$this->content['bulan1'] = date('m');
			$this->content['tahun1'] = date('Y');

			$this->twig->display('master/LapTargetSalesman.html', $this->content);
		}
		else 
		{
			redirect("auth/");
		}
    }

    function tanggal_akhir(){
    	$bulan = $_POST['bulan'];
    	echo json_encode(array("tgl" => date('Y-m-t',strtotime(date('Y-'.$bulan.'-d')))));
    }

    public function listDataTargetSalesman(){
    	if ($this->logged) 
		{
			$params = $columns = $totalRecords = $data = array();
			$params = $_REQUEST;
			$query = $this->Model_targetSalesman->listDataTargetSalesman();
			$i=1;
			foreach ($query as $list) {
				$row = array();
				$row[] = $i;
				$row[] = (!empty($list->Cabang) ? $list->Cabang : "");
				$row[] = (!empty($list->KodeSalesman) ? $list->KodeSalesman : "");
				$row[] = (!empty($list->NamaSalesman) ? $list->NamaSalesman : "");
				$row[] = (!empty($list->TipeSalesman) ? $list->TipeSalesman : "");
	 			$row[] = (!empty($list->Mcl) ? $list->Mcl : "");
	 			$row[] = (!empty($list->Tanggal) ? $list->Tanggal : "");
	 			$row[] = (!empty($list->Prinsipal) ? $list->Prinsipal : "");
	 			$row[] = (!empty($list->Target) ? $list->Target : "");
	 			$edit = $delete = '';

	 			if ($list->bulan >= date('m') && $list->tahun >= date('Y')) {
	 				$edit ='<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$list->id."'".')"><i class="fa fa-pencil"></i> Edit</a>';
	 			}

	 			if ($list->bulan >= date('m') && $list->tahun >= date('Y')) {
	 				$delete ='<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$list->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
	 			}

	 			if ($list->statusPusat == 'N') {
	 				$statusPusat = '<a class="btn btn-sm btn-default" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-"></i> Proses</a>';
	 			}else{
	 				$statusPusat = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-check"></i> Proses</a>';
	 			}

	 			$row[] = $edit;
	 			$row[] = $delete;
	 			$row[] = $statusPusat;
			
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
			redirect("auth/");
		}
    }

    public function listDataLapTargetSalesman(){
    	if ($this->logged) 
		{
			$params = $columns = $totalRecords = $data = array();
			$params = $_REQUEST;
			$query = $this->Model_targetSalesman->listDataLapTargetSalesman();
			$i=1;
			foreach ($query as $list) {
				$row = array();
				$row[] = $i;
				$row[] = (!empty($list->Cabang) ? $list->Cabang : "");
				$row[] = (!empty($list->KodeSalesman) ? $list->KodeSalesman : "");
				$row[] = (!empty($list->NamaSalesman) ? $list->NamaSalesman : "");
				$row[] = (!empty($list->TipeSalesman) ? $list->TipeSalesman : "");
	 			$row[] = (!empty($list->Mcl) ? $list->Mcl : "");
	 			$row[] = (!empty($list->Tanggal) ? $list->Tanggal : "");
	 			$row[] = (!empty($list->Prinsipal) ? $list->Prinsipal : "");
	 			$row[] = (!empty($list->Target) ? $list->Target : "");
	 			

	 			/*if ($list->statusPusat == 'N') {
	 				$statusPusat = '<a class="btn btn-sm btn-default" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-"></i> Proses</a>';
	 			}else{
	 				$statusPusat = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-check"></i> Proses</a>';
	 			}

	 			$row[] = $statusPusat;*/
			
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
			redirect("auth/");
		}
    }

    function listDataTargetSalesman_count() { 

       $result = $this->Model_targetSalesman->listDataTargetSalesman_count()->row();
       echo json_encode($result);
   }

   function listDataLapTargetSalesman_count() { 

       $result = $this->Model_targetSalesman->listDataLapTargetSalesman_count()->row();
       echo json_encode($result);
   }

   function listDataLapTargetSalesmanPivot_count() { 

       $result = $this->Model_targetSalesman->listDataLapTargetSalesmanPivot_count()->row();
       echo json_encode($result);
   }

    function listDataKodeSalesman() { 
    	$cabang = $_POST['cabang'];
    	$bulan = $_POST['bulan'];
    	$tahun = $_POST['tahun'];

       $result = $this->Model_targetSalesman->get_KodeSalesman($cabang,$bulan,$tahun)->result();
       echo json_encode($result);
   }

   public function saveDataTargetSalesman(){
		if ( $this->logged) 
		{	
			$params =(object)$this->input->post();
			$valid = $this->Model_targetSalesman->saveDataTargetSalesman($params);
			echo json_encode(array("status" => $valid));
		}
		else 
		{	
			redirect("auth/logout");
		}
    }

    public function deleteDataTargetSalesman($id = NULL){
		if ($this->logged) 
		{
			$this->Model_targetSalesman->deleteTargetSalesman($id);
			echo json_encode(array("status" => TRUE));
		}
		else
		{
			redirect("auth/");
		}
	}

	public function updateDataTargetSalesman(){
		if ( $this->logged) 
		{	
			$params = $this->input->post();	
			$valid = $this->Model_targetSalesman->updateTargetSalesman($params);
			echo json_encode(array("status" => TRUE));
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	public function getDataTargetSalesman($id = NULL)
	{    
		if ( $this->logged) 
		{
			$data = $this->Model_targetSalesman->getDataTargetSalesman($id);
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	}

	function uploadTargetSalesman($id = NULL){
		$valid = $this->Model_targetSalesman->uploadTargetSalesman($id);
		echo json_encode(array("status" => TRUE));
	}

	function uploadTargetSalesman_all() { 

       $result = $this->Model_targetSalesman->uploadTargetSalesman_all();
       echo json_encode(array("status" => TRUE));

   }

   function test() { 

       $result = $this->db->query("call target_salesman(2019,09)");
       print_r($result);

   }

   function LapTargetSalesmanPivot(){
   	if ( $this->logged ) 
        {	
        	$Cabang = $this->session->userdata('cabang');
            $this->content['cabang']= $Cabang;
            $this->content['fieldheader']= $this->Model_targetSalesman->get_field_header($Cabang)->result();
            $this->content['data'] = $this->Model_targetSalesman->listDataLapTargetSalesmanPivot();
            $this->twig->display('master/LapTargetSalesmanPivot.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
   }

   function listDataLapTargetSalesmanPivot(){
   	if ($this->logged) 
		{
			$Cabang = $this->session->userdata('cabang');
			$params = $columns = $totalRecords = $data = array();
			$params = $_REQUEST;
			$query = $this->Model_targetSalesman->listDataLapTargetSalesmanPivot();
			echo"<pre>";
			echo json_encode($query);
			exit();
			$i=1;
			$prinsipal = $this->db->query("SELECT Prinsipal FROM mst_target_salesman_baru WHERE Cabang = '".$Cabang."'
                GROUP BY Prinsipal ORDER BY Prinsipal")->result();

			foreach ($query as $list) {
				$row = array();
				$row[] = $i;
				$row[] = (!empty($list->Cabang) ? $list->Cabang : "");
				$row[] = (!empty($list->KodeSalesman) ? $list->KodeSalesman : "");
				$row[] = (!empty($list->NamaSalesman) ? $list->NamaSalesman : "");
				$row[] = (!empty($list->TipeSalesman) ? $list->TipeSalesman : "");
	 			$row[] = (!empty($list->Mcl) ? $list->Mcl : "");
	 			$row[] = (!empty($list->Tanggal) ? $list->Tanggal : "");
	 			// $row[] = (!empty($list->Prinsipal) ? $list->Prinsipal : "");
	 			
	 			/*$row[] = "ADITAMA";
	 			$row[] = "ALKES-LAIN";
	 			$row[] = "ANDALAN";
	 			$row[] = "ARMOXINDO";
	 			$row[] = "CORONET";*/
	 			

	 			foreach ($prinsipal as $r) {
	 				$row[] = (!empty($r->Prinsipal) ? $r->Prinsipal : "");
	 			}
	 			

	 			/*if ($list->statusPusat == 'N') {
	 				$statusPusat = '<a class="btn btn-sm btn-default" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-"></i> Proses</a>';
	 			}else{
	 				$statusPusat = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Hapus" onclick="upload('."'".$list->id."'".')"><i class="fa fa-check"></i> Proses</a>';
	 			}

	 			$row[] = $statusPusat;*/
			
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
			redirect("auth/");
		}
   } 

   function LapTargetSalesman_Pivot(){
	   	if ( $this->logged ) 
	    {
	    	$Cabang = $this->session->userdata('cabang');
	        $this->content['cabang']= $Cabang;
	        $this->content['getTahun'] =  $this->Model_targetSalesman->getTahun()->result();
	        $this->twig->display('master/LapTargetSalesman_pivot.html', $this->content);
	    }
	    else 
	    {
	        redirect("auth/logout");
	    }
   } 

   public function loadPrinsipal()
	{
		if ( $this->logged ) 
		{
				$data = $this->Model_targetSalesman->listPrinsipal()->result();

			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
	} 

   function getdatasalesman(){
   	if ($this->logged) 
        {
            $list = $this->Model_targetSalesman->getDataTargetSalesman_Pivot();
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
   }

   function getExportTargetSalesman(){
   		if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";
            
            $prinsipal = $this->input->post('prinsipal');
            $tahun = $this->input->post('tahun');
            $bulan = $this->input->post('bulan');
            
           
            $query=$this->Model_targetSalesman->getExportTargetSalesman($prinsipal,$tahun,$bulan)->result();

            if(count($query)>0){
            
                $objPHPExcel = new PHPExcel();
                // Set properties
                $objPHPExcel->getProperties()
                      ->setCreator("Rian Rezky") //creator
                        ->setTitle("Programmer - PT. Sapta Saritama");  //file title
     
                $objset = $objPHPExcel->setActiveSheetIndex(0); //inisiasi set object
                $objget = $objPHPExcel->getActiveSheet();  //inisiasi get object
     
                $objget->setTitle('Sample Sheet'); //sheet title
                //Warna header tabel
                $objget->getStyle("A1:AQ1")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '92d050')
                        ),
                        'font' => array(
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                //table header
                $cols = array("A","B","C","D","E","F","G","H"); 
                $val = array("CABANG","KODE SALESMAN","NAMA SALESMAN","TIPE SALESMAN","MCL","TANGGAL","PRINSIPAL","TARGET");

                for ($a=0;$a<8; $a++) 
                {
                    $objset->setCellValue($cols[$a].'1', $val[$a]);
                     
                    // //Setting lebar cell
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25); 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25); 
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cols[$a].'1')->applyFromArray($style);
                }
                $baris  = 2;
                foreach ($query as $frow){
                    // $objPHPExcel->getActiveSheet()
                    //             ->getStyle('D1:D'.$baris)
                    //             ->getNumberFormat()
                    //             ->setFormatCode('0');
                   //pemanggilan sesuaikan dengan nama kolom tabel
                    $objset->setCellValue("A".$baris, $frow->Cabang); 
                    $objset->setCellValue("B".$baris, $frow->KodeSalesman); 
                    $objset->setCellValue("C".$baris, $frow->NamaSalesman); 
                    $objset->setCellValue("D".$baris, $frow->TipeSalesman); 
                    $objset->setCellValue("E".$baris, $frow->Mcl); 
                    $objset->setCellValue("F".$baris, $frow->Tanggal); 
                    $objset->setCellValue("G".$baris, $frow->Prinsipal); 
                    $objset->setCellValue("H".$baris, $frow->Target); 
                    //Set number value                    
                    $baris++;
                }
                $objPHPExcel->getActiveSheet()->getStyle('H2:H'.$baris)->getNumberFormat()->setFormatCode('0');
                // $objPHPExcel->getActiveSheet()->getStyle('AN2:AN'.$baris)->getNumberFormat()->setFormatCode('0');
                $objPHPExcel->getActiveSheet()->setTitle('Laporan Target Salesman');
                $objPHPExcel->setActiveSheetIndex(0);  
                $filename = urlencode("LapTargetSalesman".date("Y-m-d").".xlsx");  
                // $temp_filename = 'd:/php/'. $filename;   
                // $size = filesize($filename);
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                // header("Content-length: $size");;
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
                header('Cache-Control: max-age=0'); //no cache
                ob_start();
                $objWriter->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();

                $response =  array(
                    'op' => 'ok',
                    'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
                    'filename' => $filename
                );
                echo json_encode($response);
            }else{
                echo "Tidak Ada data pada periode ini";
            }
            // log_message("error",print_r($query,true));
            // if($query){
            //     $this->Model_laporan->getexcel($query);
            // }
            
        }else{   
            redirect("auth/logout");
        }
   }

   function cek_targetSalesman(){
   	if ( $this->logged) 
		{	
			$params = (object)$this->input->post();
			$data = $this->Model_targetSalesman->cek_targetSalesman($params)->result();
			echo json_encode($data);
		}
		else 
		{	
			redirect("auth/logout");
		}
   }
   


}