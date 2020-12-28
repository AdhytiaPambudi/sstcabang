<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CetakTransaksi extends CI_Controller {

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
		$this->load->model('pembelian/Model_cetakTransaksi');
		$this->load->library('format');
		$this->load->library('mpdf');
		$this->load->library('pdf');
		// $this->load->library('lib_tcpdf');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('user_group');
		$this->cabang = $this->session->userdata('cabang');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);
	}

	// Print usulan beli
	public function printdataUsulan()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataUsulan($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function printdataPR()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataPR($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	// Print PO
	public function printdataPO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataPO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function printdataBPB()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataBPB($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function printdataSO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataSO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function printdataDO()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printDataDO($no);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}
	public function printdataFaktur()
	{
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$TipeDokumen = $_POST['TipeDokumen'];
			$data = $this->Model_cetakTransaksi->printDataFaktur($no,$TipeDokumen);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	public function printdataDIH()
	{
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$status 	 = $_POST['status'];
			$data = $this->Model_cetakTransaksi->printDataDIH($no,$status);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

	function printdatamutasikoreksi(){
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printdatamutasikoreksi($no);
			echo json_encode($data);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	function printdatamutasibatch(){
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printdatamutasibatch($no);
			echo json_encode($data);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	function printdatamutasigudang(){
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printdatamutasigudang($no);
			echo json_encode($data);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	function printdatakiriman(){
		if ($this->logged) 
		{
			$no 		 = $_POST['no'];
			$data = $this->Model_cetakTransaksi->printdatakiriman($no);
			echo json_encode($data);
		}else{  
	      redirect("main/auth/logout");
	    }
	}

	function cetakrelokasikiriman(){
		if ($this->logged) 
		{
			$no 		 = $_GET['no'];
			$data = $this->Model_cetakTransaksi->cetakrelokasikiriman($no);
			echo json_encode($data);
		}else{
	    	redirect("main/auth/logout");
	    }	
	}

	function cetakrelokasiterima(){
		if ($this->logged) 
		{
			$no 		 = $_GET['no'];
			$data = $this->Model_cetakTransaksi->cetakrelokasiterima($no);
			echo json_encode($data);
		}else{
	    	redirect("main/auth/logout");
	    }	
	}

	// function cetak_kas(){
	// 	if ($this->logged) 
	// 	{
	// 		$no = $_POST['no'];
	// 		$data = $this->Model_cetakTransaksi->cetak_kas($no);
	// 		echo json_encode($data);
	// 	}else{
	//     	redirect("main/auth/logout");
	//     }
	// }

	function cetak_kas(){
		if ($this->logged) 
		{
			$no = json_decode($_GET['no']);
			$data['data_detail'] = $this->Model_cetakTransaksi->cetak_kas($no);
			$this->load->view('../../assets/print_template/cetak_bukti_kas', $data);

		}else{
	    	redirect("main/auth/logout");
	    }
	}

	// =====================================================================================
	function print_do_penjualan_mpdf(){
		$params = json_decode($_GET['nodo']);
		
		$data = $this->Model_cetakTransaksi->printDataDO_new($params);

		// $headers = $data['header'];
		$data_detail = $data['detail'];

		$mpdf = $this->mpdf->load();

		foreach ($data['header'] as $key => $headers) {
			
			$explode_salesman = explode(" ", $headers->NamaSalesman);
	        if(count($explode_salesman) > 1){
	            $salesman =  $explode_salesman[0]." ".substr($explode_salesman[1], 1,1).".";
	        }else{
	            $salesman = $explode_salesman[0];
	        }
			$jto = $this->Model_cetakTransaksi->get_jto($headers->CaraBayar);
			
	        if ($jto == "") {
				$top = 0;
				$j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + '.$top.' days'));
			}else{

		        $top = $jto->JTO_Real;
		        $j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + '.$jto->JTO_Real.' days'));
			}

			/*switch ($headers->CaraBayar) {
	            case "COD":
	                $top = 0;
	                $j_tempo = date('Y-m-d', strtotime($headers->TglDO));
	                break;
	            case "Cash":
	                $top = 7;
	                $j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + 7 days'));
	                break;

	            case "Kredit":
	                $top = 21;
	                $j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + 21 days'));
	                break;

	            case "RPO180":
	                $top = 21;
	                $j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + 21 days'));
	                break;

	            case "B2B":
	                $top = 21;
	                $j_tempo = date('Y-m-d', strtotime($headers->TglDO. ' + 21 days'));
	                break;

	            default:
	            	$top = 7;
	                $j_tempo = "";
	        }*/

	        if(preg_match("/RPO/i", $headers->CaraBayar)) {
			  $headers->CaraBayar = "RPO";
			}else if($headers->CaraBayar == "B2B") {
			  $headers->CaraBayar = "";
			}else{
				$headers->CaraBayar = $headers->CaraBayar;
			}

	        $header = '
	        	<table border="0" style="font-family: tahoma; font-size: 10px;">
					<tr>
			            <td width="450" valign="top">
			                <br><br><br>Ijin : '
			                .$headers->Ijin_PBF.'<br>'
			                .$headers->Alamat.'<br>'
            				 .$headers->Bank_Acc.
			            '</td>
			            <td rowspan="2" valign="top" width="170" style="line-height: 13.5px;">
			               	'.$headers->NoDO.'<br>
			                '.$this->format_tanggal($headers->TglDO).'<br>
			                '.$headers->Cabang.'<br>
			                '.$headers->Pengirim.'<br>
			                '.$headers->TipePelanggan.'<br>
			                '.$headers->CaraBayar.'<br>
			                '.$top.' hari<br>
			                '.$j_tempo.'<br>
			            </td>
			            <td rowspan="2" valign="top" width="160" style="line-height: 13px; padding-top:5px;">
			            	<br><br>
		                    No. Acu : '.$headers->Acu.'<br>
		                    Salesman : '.$headers->Salesman.'
			            </td>
			        </tr>
			        <tr>
			            <td valign="top" style="padding-left:72px;line-height: 15px;">'.
			            	$headers->Pelanggan.' - '.$headers->NamaPelanggan.'<br>'.
			            	$headers->AlamatKirim.'<br>'.
			            	$headers->NPWPPelanggan.
			            '</td>
			        </tr>
			    </table>
	        ';

			$html = '
		        <table border="0" style="font-family: tahoma; font-size:11px;">';
		        foreach ($data_detail as $key2 => $detail) {
		        	if($detail->NoDO == $headers->NoDO){
			        	$discab = $detail->DiscCab+$detail->DiscCab_onf > 0 ? " /D".number_format($detail->DiscCab + $detail->DiscCab_onf,2) : "";
			        	$disprin = $detail->DiscPrins1 + $detail->DiscPrins2 > 0 ? " /P".number_format($detail->DiscPrins1 + $detail->DiscPrins2,2) : "";
			        	$discall = (double)$detail->DiscCab_onf + (double)$detail->DiscCab + (double)$detail->DiscPrins1 + (double)$detail->DiscPrins2;
			        	$bonus = $detail->BonusDO > 0 ? " /B".number_format($detail->BonusDO,2) : "";
			        	$x1 = date_format(date_create($detail->ExpDate),"Y-m-d");
			        	$xdate = str_replace("-",".",$x1);
			        	$harga = number_format($detail->Harga);
			        	$gross = number_format($detail->Gross_detail);
			        	$potongan = number_format($detail->Potongan_detail);
			        	$value = number_format($detail->Value_detail);
			        	$html .= '<tr>
			        		<td valign="top" width="60">'.$detail->KodeProduk.'</td>
			        		<td valign="top" width="189">'.$detail->NamaProduk.$discab.$disprin.$bonus.'</td>
			        		<td valign="top" align="center" width="39">'.($detail->QtyDO + $detail->BonusDO).'</td>
			        		<td valign="top" width="40">'.$detail->UOM.'</td>
			        		<td valign="top" width="70">'.$detail->BatchNo.'</td>
			        		<td valign="top" width="58">'.$xdate.'</td>
			        		<td valign="top" align="right" width="58" style="padding-right:2px;">'.$harga.'</td>
			        		<td valign="top" align="center" width="30">'.$discall.'</td>
			        		<td valign="top" align="right" width="80" style="padding-right:4px;">'.$gross.'</td>
			        		<td valign="top" align="right" width="68" style="padding-right:4px;">'.$potongan.'</td>
			        		<td valign="top" align="right" width="82" style="padding-right:4px;">'.$value.'</td>
			        	</tr>';
		        	}
		        }
		        $html .= '</table>';
		       $footer = '
		       		<table border="0" style=" font-family: tahoma; font-size:11px; border-collapse: separate; border-spacing: 4.5px;">
		            	<tr>
		            		<td width="550">&nbsp;</td>
		            		<td width="80" align="right" style="padding-right:4px;">'.number_format($headers->Gross).'</td>
		            		<td width="68" align="right" style="padding-right:4px;">'.number_format($headers->Potongan).'</td>
		            		<td width="82" align="right" style="padding-right:4px;">'.number_format($headers->Value).'</td>
		            	</tr>
		            	<tr>
		            		<td rowspan="2">
		            		'.$this->terbilang($headers->Total).' rupiah
		            		</td>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            	</tr>
		            	<tr>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td align="right" style="padding-right:4px;">'.number_format($headers->Materai).'</td>
		            	</tr>
		            	<tr>
		            		<td rowspan="4" valign="bottom">
		            			<table>
		            				<tr>
		            					<td width="370">
		            						halaman {PAGENO}/{nbpg} - '. date("Y-m-d h:i:s").'
		            					</td>
		            					<td align="center">
					            			'.$headers->Nama_APJ.'<br>
								            '.$headers->SIKA_Faktur.'
		            					</td>
		            				</tr>
		            			</table>
		            		</td>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td align="right" style="padding-right:4px;">'.number_format($headers->ValueCashDiskon).'</td>
		            	</tr>
		            	<tr>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td align="right" style="padding-right:4px;">'.number_format($headers->Value).'</td>
		            	</tr>
		            	<tr>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td align="right" style="padding-right:4px;">'.number_format($headers->Ppn).'</td>
		            	</tr>
		            	<tr>
		            		<td>&nbsp;</td>
		            		<td>&nbsp;</td>
		            		<td align="right" style="padding-right:4px;">'.number_format($headers->Total).'</td>
		            	</tr>
		            	<tr>
		            		<!--<td style="font-size: 8px;" colspan="3">Pembayaran harap di transfer ke Pembayaran dengan cek dan giro dianggap sah setelah diuangkan </td>-->
		            		<td style="font-size: 8px;" colspan="3">&nbsp;</td>
		            	</tr>
		            </table>
		       ';
			// $mpdf->showImageErrors = true;
			// $image_url = getcwd()."/assets/logo/faktur.png";
			$mpdf->SetHTMLHeader($header);
			if($key < count($data['header']) - 1 || $key != 0){
				// $mpdf->AddPage();
				$mpdf->AddPage('', '', 1, '');
			}
			$mpdf->SetHTMLFooter("<p style='font-family:tahoma; font-size:11px;'>&nbsp;&nbsp; halaman {PAGENO}/{nbpg} - ". date("Y-m-d h:i:s")."</p><br><br>");
			$mpdf->WriteHTML($html);
			$mpdf->SetHTMLFooter($footer);
		// $mpdf->SetHTMLHeader('aaaaaaaaaaaaaaaaaaaa');
		}
		// $mpdf->AddPage();
		// $mpdf->AddPage();
		$mpdf->SetJS('this.print();');
		$mpdf->Output();
		$mpdf->SetJS('window.close();');
	}
	// ==================================================================================================

	function print_do_penjualan(){
		$params = $_GET['nodo'];
		$data = $this->Model_cetakTransaksi->printDataDO($params);
		// log_message('error',print_r($data,true));

		$headers = $data['header'];
		$data_detail = $data['detail'];
		$explode_salesman = explode(" ", $headers[0]->NamaSalesman);
        if(count($explode_salesman) > 1){
            $salesman =  $explode_salesman[0]." ".substr($explode_salesman[1], 1,1).".";
        }else{
            $salesman = $explode_salesman[0];
        }
		switch ($headers[0]->CaraBayar) {
            case "Cash":
                $top = 7;
                $j_tempo = date('Y-m-d', strtotime($headers[0]->TglDO. ' + 7 days'));
                break;

            case "Kredit":
                $top = 21;
                $j_tempo = date('Y-m-d', strtotime($headers[0]->TglDO. ' + 21 days'));
                break;

            default:
                $j_tempo = "";
        }
		$html = '
			<html>
			    <head>
			        <style>
			            /** 
			                Set the margins of the page to 0, so the footer and the header
			                can be of the full height and width !
			             **/
			            @page {
			                margin: 0cm 0cm;
			                size: 215.9mm 139.7mm;
			            }

			            /** Define now the real margins of every page in the PDF **/
			            body {
			                margin-top: 45mm;
			                margin-left: 4mm;
			                margin-right: 10mm;
			                margin-bottom: 43mm
			                font-family: sans-serif;
			            }

			            /** Define the header rules **/
			            header {
			                position: fixed;
			                top: 6mm;
			                left: 5mm;
			                right: 0cm;
			                height: 35mm;

			                /** Extra personal styles **/
			                background-color: white;
			                color: #000;
			                text-align: center;
			                line-height: 3mm;
			            }

			            /** Define the footer rules **/
			            footer {
			                position: fixed; 
			                bottom: 0cm;
			                left: 5mm;
			                right: 0cm;
			                height: 43mm;

			                /** Extra personal styles **/
			            }
			            .item {
			            	padding-left:2px;
			            	padding-right:2px;
			            }
			            //footer .page-number:after { content: "Page " counter(page) " of " counter(pages); }
			        </style>
			    </head>
			    <body>
			        <!-- Define header and footer blocks before your content -->
			        <header>
			            <table border="0" cellspacing="0" style="font-family: sans-serif; font-size: 11px;">
				        <tr>
				            <td width="310" valign="top" style="padding-top: 20px; line-height: 4mm;">
				                <!-- PT. SAPTA SARITAMA<br> --><br>
				                '.$headers[0]->Alamat.'<br>
				                '.$headers[0]->Bank_Acc.'
				            </td>
				            <td rowspan="2" valign="top">
				                <table cellspacing="3" style="font-size: 11px; border-collapse: separate; border-spacing: 1.8px;">
				                    <tr>
				                        <td width="120" nowrap>'.$headers[0]->NoDO.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$this->format_tanggal($headers[0]->TglDO).'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$headers[0]->Cabang.'</td>
				                    </tr>
				                    <tr>
				                        <td width="100" nowrap>'.$salesman.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$headers[0]->TipePelanggan.'</td>
				                        
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$headers[0]->CaraBayar.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$top.' hari</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$j_tempo.'</td>
				                    </tr>
				                </table>
				            </td>
				            <td></td>
				        </tr>
				        <tr>
				            <td valign="top" style="padding-left:70px; padding-top: 6px; line-height: 14px;">'.
				            	$headers[0]->Pelanggan.' - '.$headers[0]->NamaPelanggan.'<br>'.
				            	$headers[0]->AlamatKirim.'<br>'.
				            	$headers[0]->NPWPPelanggan.
				            '</td>
				            <td>
				            	<div style="displaye:inline-block; width:180px;" align="right">
				                    No. Acu : '.$headers[0]->Acu.'<br>
				                    Salesman : '.$headers[0]->Salesman.'
				                </div>
				            </td>
				        </tr>
				    </table>
			        </header>

			        <footer>
			            <table border="0" style="font-size:11px; border-collapse: separate; border-spacing: 4px;">
			            	<tr>
			            		<td class="item" width="395">&nbsp;</td>
			            		<td class="item" width="56" align="right">'.number_format($headers[0]->Gross).'</td>
			            		<td class="item" width="49" align="right">'.number_format($headers[0]->Potongan).'</td>
			            		<td class="item" width="55" align="right">'.number_format($headers[0]->Value).'</td>
			            	</tr>
			            	<tr>
			            		<td rowspan="2">
			            		'.$this->terbilang($headers[0]->Total).'
			            		</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers[0]->Materai).'</td>
			            	</tr>
			            	<tr>
			            		<td rowspan="4" align="right" valign="bottom" style="padding-right:5px;">
			            			<table>
			            				<tr>
			            					<td>
			            						
			            					</td>
			            					<td></td>
			            				</tr>
			            			</table>
			            			'.$headers[0]->Nama_APJ.'<br>
			            			'.$headers[0]->SIKA_Faktur.'
			            		</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers[0]->ValueCashDiskon).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers[0]->Value).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers[0]->Ppn).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers[0]->Total).'</td>
			            	</tr>
			            </table> 
			            <p>Pembayaran harap di transfer ke 123456889. Pembayaran dianggap sah apabila sudah diauangkan</p>
			        </footer>

			        <!-- Wrap the content of your PDF inside a main tag -->
			        <main>
			            <table border="0" style="font-size:11px;">';
			            foreach ($data_detail as $key => $detail) {
			            	$discab = $detail->DiscCab > 0 ? " /D".number_format($detail->DiscCab,2) : "";
			            	$disprin = $detail->DiscPrins1 + $detail->DiscPrins2 > 0 ? " /P".number_format($detail->DiscPrins1 + $detail->DiscPrins2,2) : "";
			            	$discall = (double)$detail->DiscCab + (double)$detail->DiscPrins1 + (double)$detail->DiscPrins2;
			            	$bonus = $detail->BonusDO > 0 ? " /B".number_format($detail->BonusDO,2) : "";
			            	$x1 = date_format(date_create($detail->ExpDate),"Y-m-d");
			            	$xdate = str_replace("-",".",$x1);
			            	$harga = number_format($detail->Harga);
			            	$gross = number_format($detail->Gross_detail);
			            	$potongan = number_format($detail->Potongan_detail);
			            	$value = number_format($detail->Value_detail);
			            	$html .= '<tr>
			            		<td class="item" valign="top" width="41;">'.$detail->KodeProduk.'</td>
			            		<td class="item" valign="top" width="137;">'.$detail->NamaProduk.$discab.$disprin.$bonus.'</td>
			            		<td class="item" valign="top" align="center" width="25;">'.($detail->QtyDO + $detail->BonusDO).'</td>
			            		<td class="item" valign="top" width="25;">'.$detail->UOM.'</td>
			            		<td class="item" valign="top" width="48;">'.$detail->BatchNo.'</td>
			            		<td class="item" valign="top" width="42;">'.$xdate.'</td>
			            		<td class="item" valign="top" align="right" width="41;">'.$harga.'</td>
			            		<td class="item" valign="top" align="center" width="17;">'.$discall.'</td>
			            		<td class="item" valign="top" align="right" width="56;">'.$gross.'</td>
			            		<td class="item" valign="top" align="right" width="49;">'.$potongan.'</td>
			            		<td class="item" valign="top" align="right" width="55;">'.$value.'</td>
			            	</tr>';
			            }
			            $html .= '</table>
			        </main>
			    </body>
			</html>
		';
		$dompdf = new Dompdf\Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->loadHtml($html);
		// $dompdf->setPaper('A4', 'landscape');
		// $pdf->setPaper([0, 0, 685.98, 396.85], 'landscape');
		$dompdf->render();
			$fontMetrics = $dompdf->getFontMetrics();
			$canvas = $dompdf->get_canvas();
			$font = $fontMetrics->getFont('Helvetica');
			$canvas->page_text(15, 360, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 8, array(0, 0, 0));
		$dompdf->stream("codexworld",array("Attachment"=>0));
	}

	function print_faktur_penjualan(){
		$params = $_GET['nofaktur'];
		$tipe_dok = $_GET['TipeDokumen'];
		$data = $this->Model_cetakTransaksi->printDataFaktur($params,$tipe_dok);

		$headers = $data[0];
		// log_message('error',print_r($headers,true));
		$data_detail = $data;
		$explode_salesman = explode(" ", $headers->Salesman);
        if(count($explode_salesman) > 1){
            $salesman =  $explode_salesman[0]." ".substr($explode_salesman[1], 1,1).".";
        }else{
            $salesman = $explode_salesman[0];
        }
		switch ($headers->CaraBayar) {
            case "Cash":
                $top = 7;
                $j_tempo = date('Y-m-d', strtotime($headers->TglFaktur. ' + 7 days'));
                break;

            case "Kredit":
                $top = 21;
                $j_tempo = date('Y-m-d', strtotime($headers->TglFaktur. ' + 21 days'));
                break;

            default:
                $j_tempo = "";
        }
		$html = '
			<html>
			    <head>
			        <style>
			            /** 
			                Set the margins of the page to 0, so the footer and the header
			                can be of the full height and width !
			             **/
			            @page {
			                margin: 0cm 0cm;
			                // size: 215.9mm 139.7mm;
			                size: 210mm 139.7mm;
			            }

			            /** Define now the real margins of every page in the PDF **/
			            body {
			                margin-top: 54mm;
			                margin-left: 4.5mm;
			                margin-right: 10mm;
			                margin-bottom: 40mm
			                font-family: sans-serif;
			            }

			            /** Define the header rules **/
			            header {
			                position: fixed;
			                top: 15mm;
			                left: 5mm;
			                right: 10mm;
			                height: 35mm;

			                /** Extra personal styles **/
			                background-color: white;
			                color: #000;
			                text-align: center;
			                line-height: 3mm;
			            }

			            /** Define the footer rules **/
			            footer {
			                position: fixed; 
			                bottom: 0cm;
			                left: 4.5mm;
			                right: 10mm;
			                height: 40mm;

			                /** Extra personal styles **/
			            }
			            .item {
			            	padding-left:2px;
			            	padding-right:2px;
			            }
			        </style>
			    </head>
			    <body>
			        <!-- Define header and footer blocks before your content -->
			        <header>
			            <table border="0" cellspacing="0" style="font-family: sans-serif; font-size: 11px;">
				        <tr>
				            <td width="310" valign="top" style="padding-top: 10px; line-height: 4mm;">
				                <!-- PT. SAPTA SARITAMA<br> --><br>
				                '.$headers->Alamat.' <br>
				                '.$headers->Bank_Acc.'
				            </td>
				            <td rowspan="2" valign="top">
				                <table cellspacing="3" style="font-size: 11px; border-collapse: separate; border-spacing: 1px;">
				                    <tr>
				                        <td width="120" nowrap>'.$headers->NoFaktur.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$this->format_tanggal($headers->TglFaktur).'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$headers->Cabang.'</td>
				                    </tr>
				                    <tr>
				                        <td width="100" nowrap>'.$headers->TipePelanggan.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$headers->CaraBayar.'</td>
				                        
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$top.' hari</td>
				                    </tr>
				                    <tr>
				                        <td nowrap>'.$j_tempo.'</td>
				                    </tr>
				                    <tr>
				                        <td nowrap></td><td nowrap>'.$headers->NoDO.'</td>
				                    </tr>
				                </table>
				            </td>
				        </tr>
				        <tr>
				            <td valign="top" style="padding-left:70px; padding-top: 6px; line-height: 14px;">'.
				            	$headers->Pelanggan.' - '.$headers->NamaPelanggan.'<br>'.
				            	$headers->AlamatFaktur.'<br>'.
				            	$headers->NPWPPelanggan.
				            '</td>
				        </tr>
				    </table>
			        </header>

			        <footer>
			            <table border="0" style="font-size:11px; border-collapse: separate; border-spacing: 4px;">
			            	<tr>
			            		<td class="item" width="387">&nbsp;</td>
			            		<td class="item" width="52" align="right">'.number_format($headers->Gross).'</td>
			            		<td class="item" width="48" align="right">'.number_format($headers->Potongan).'</td>
			            		<td class="item" width="51" align="right">'.number_format($headers->Value).'</td>
			            	</tr>
			            	<tr>
			            		<td rowspan="2">
			            		'.$this->terbilang($headers->Total).'
			            		</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">0</td>
			            	</tr>
			            	<tr>
			            		<td rowspan="4" align="right" valign="bottom" style="padding-right:5px;">
			            			'.$headers->Nama_APJ.'
			            		</td>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers->CashDiskon).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers->Value).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers->Ppn).'</td>
			            	</tr>
			            	<tr>
			            		<td>&nbsp;</td>
			            		<td>&nbsp;</td>
			            		<td class="item" align="right">'.number_format($headers->Total).'</td>
			            	</tr>
			            </table> 
			        </footer>

			        <!-- Wrap the content of your PDF inside a main tag -->
			        <main>
			            <table border="0" style="font-size:11px;">';
			            foreach ($data_detail as $key => $detail) {
			            	$discab = $detail->DiscCab > 0 ? " /D".number_format($detail->DiscCab,2) : "";
			            	$disprin = $detail->DiscPrins1 + $detail->DiscPrins2 > 0 ? " /P".number_format($detail->DiscPrins1 + $detail->DiscPrins2,2) : "";
			            	$discall = (double)$detail->DiscCab + (double)$detail->DiscPrins1 + (double)$detail->DiscPrins2;
			            	$bonus = $detail->BonusFaktur > 0 ? " /B".number_format($detail->BonusFaktur,2) : "";
			            	$x1 = date_format(date_create($detail->ExpDate),"Y-m-d");
			            	$xdate = str_replace("-",".",$x1);
			            	$harga = number_format($detail->Harga);
			            	$gross = number_format($detail->Gross_detail);
			            	$potongan = number_format($detail->Potongan_detail);
			            	$value = number_format($detail->Value_detail);
			            	$html .= '<tr>
			            		<td class="item" valign="top" width="41;">'.$detail->KodeProduk.'</td>
			            		<td class="item" valign="top" width="130;">'.$detail->NamaProduk.$discab.$disprin.$bonus.'</td>
			            		<td class="item" valign="top" align="center" width="25;">'.((int)$detail->QtyFaktur + (int)$detail->BonusFaktur).'</td>
			            		<td class="item" valign="top" width="25;">'.$detail->UOM.'</td>
			            		<td class="item" valign="top" width="48;">'.$detail->BatchNo.'</td>
			            		<td class="item" valign="top" width="42;">'.$xdate.'</td>
			            		<td class="item" valign="top" align="right" width="35;">'.$harga.'</td>
			            		<td class="item" valign="top" align="center" width="17;">'.$discall.'</td>
			            		<td class="item" valign="top" align="right" width="51;">'.$gross.'</td>
			            		<td class="item" valign="top" align="right" width="48;">'.$potongan.'</td>
			            		<td class="item" valign="top" align="right" width="52;">'.$value.'</td>
			            	</tr>';
			            }
			            $html .= '</table>
			        </main>
			    </body>
			</html>
		';
		$dompdf = new Dompdf\Dompdf();
		$dompdf->loadHtml($html);
		// $dompdf->setPaper('A4', 'landscape');
		// $pdf->setPaper([0, 0, 685.98, 396.85], 'landscape');
		$dompdf->render();
			$fontMetrics = $dompdf->getFontMetrics();
			$canvas = $dompdf->get_canvas();
			$font = $fontMetrics->getFont('Helvetica');
			$canvas->page_text(15, 360, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 8, array(0, 0, 0));
		$dompdf->stream("codexworld",array("Attachment"=>0));
	}

	function terbilang($x) {
        $status_bilangan = "";
        if($x < 0){
            $x = $x * -1;
        }

        $x = (float)$x;
        
      $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
      if ($x < 12)
        return " " . $angka[$x];
      elseif ($x < 20)
        return $this->terbilang($x - 10) . " belas";
      elseif ($x < 100)
        return $this->terbilang($x / 10) . " puluh" . $this->terbilang($x % 10);
      elseif ($x < 200)
        return "seratus" . $this->terbilang($x - 100);
      elseif ($x < 1000)
        return $this->terbilang($x / 100) . " ratus" . $this->terbilang($x % 100);
      elseif ($x < 2000)
        return "seribu" . $this->terbilang($x - 1000);
      elseif ($x < 1000000)
        return $this->terbilang($x / 1000) . " ribu" . $this->terbilang($x % 1000);
      elseif ($x < 1000000000)
        return $this->terbilang($x / 1000000) . " juta" . $this->terbilang($x % 1000000);
    }

	function format_tanggal($tanggal, $cetak_hari = false){
        $hari = array ( 1 =>    'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu'
        );
        
        $bulan = array (1 =>    'Januari',
                                'Februari',
                                'Maret',
                                'April',
                                'Mei',
                                'Juni',
                                'Juli',
                                'Agustus',
                                'September',
                                'Oktober',
                                'November',
                                'Desember'
                );
        $split    = explode('-', $tanggal);
        $tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
        
        if ($cetak_hari) {
            $num = date('N', strtotime($tanggal));
            return $hari[$num] . ', ' . $tgl_indo;
        }
        return $tgl_indo;
    }
    public function printdataDOBeli()
	{
		if ($this->logged) 
		{
			$no = $_POST['no'];
			$cabang = $_POST['cabang'];
			$data = $this->Model_cetakTransaksi->printdataDOBeli($no,$cabang);
			echo json_encode($data);
		}
		else 
		{	
			redirect("main/auth/logout");
		}
	}

}