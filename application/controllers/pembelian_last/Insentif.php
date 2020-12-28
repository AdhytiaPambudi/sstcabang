<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Insentif extends CI_Controller {

	var $content;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_main');
		$this->load->model('pembelian/Model_insentif');
		$this->load->model('pembelian/Model_main');
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

	public function insentif()
	{
		if ($this->logged) 
		{
            $this->content['cabang'] = $this->Model_main->Cabang();
            $this->content['time'] = date('Y-m-d H:i:s');
			$this->content['salesman'] = $this->Model_insentif->dataLaporanInsentifSalesman();
			$this->twig->display('pembelian/insentif.html', $this->content);
		}
		else 
		{
			redirect("auth/logout");
		}
	}

	public function getSalesman()
    {
        if ($this->logged) 
        {
            $cabang = $_POST['cabang'];
			$data = $this->Model_insentif->dataLaporanInsentifSalesman($cabang);
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

	public function selisihHari()
	{
		$tgl = $_POST['tgl'];

		//definis tgl awal dan tgl akhir
		$tglAwal = date('Y-m-01', strtotime($tgl));
		$tglAkhir = date('Y-m-t', strtotime($tgl));

	    // list tanggal merah selain hari minggu
	    //$tglLibur = Array("2013-01-04", "2013-01-05", "2013-01-17");
		$tglLibur = Array('');

	    // memecah string tanggal awal untuk mendapatkan
	    // tanggal, bulan, tahun
	    $pecah1 = explode("-", $tglAwal);
	    $date1 = $pecah1[2];
	    $month1 = $pecah1[1];
	    $year1 = $pecah1[0];

	    $libur1 = $libur2 = $libur3 = $jumH= 0;

	    // mencari total Jumlah hari dalan bulan berjalan
	    $selisih = cal_days_in_month(CAL_GREGORIAN, $month1, $year1);;

	    // proses menghitung tanggal merah dan hari minggu
	    // di antara tanggal awal dan akhir
	    for($i=1; $i<=$selisih; $i++)
	    {
	        // menentukan tanggal pada hari ke-i dari tanggal awal
	        $tanggal = mktime(0, 0, 0, $month1, $date1+$i, $year1);
	        $tglstr = date("Y-m-d", $tanggal);

	        // menghitung jumlah tanggal pada hari ke-i
	        // yang masuk dalam daftar tanggal merah selain minggu
	        if (in_array($tglstr, $tglLibur))
	        {
	           $libur1++;
	        }

	        // menghitung jumlah tanggal pada hari ke-i
	        // yang merupakan hari minggu
	        if ((date("N", $tanggal) == 7))
	        {
	           $libur2++;
	        }

	        // menghitung jumlah tanggal pada hari ke-i
	        // yang merupakan hari minggu
	        if ((date("N", $tanggal) == 6))
	        {
	           $libur3++;
	        }
	    }

	    // menghitung selisih hari yang bukan tanggal merah dan hari minggu
	    return $selisih-$libur1-$libur2-($libur3*0.5);
	}

	public function dataLaporanInsentifSalesman()
	{
		if ($this->logged) 
		{
			$targetTotal = 0;
			$tgl = $_POST['tgl'];
			$kode = $_POST['kode'];
			$cabang = $_POST['cabang'];

	        $startdate = date('Y-m-01', strtotime($tgl));
	        $enddate = date('Y-m-t', strtotime($tgl));

			$datexy = explode("-", $tgl);
			$yearxy = $datexy[0];
			$monthxy = $datexy[1];

	        $jHarixy = cal_days_in_month(CAL_GREGORIAN, $monthxy, $yearxy);



			$arrTipe = array('Reg','Alkes','Institusi','OTC','RS','Ekat','Apotik','Mix', 'PBF', 'COMBO', 'Combo', 'ALK', 'Alkes');
			$sales = $this->Model_insentif->salesInsentif($tgl, $kode, $cabang);
			$tipe = $this->Model_insentif->salesTipeSalesman($kode, $cabang); // Jumlah Hari

			$detSalesmanDat = '';
			//$detSalesmanDat = $kode.', '.$tipe->tipeSalesman;

			$komSalesTot = 0;
			if((!empty($sales->Persen)? $sales->Persen : 0) >= 90.00){
				$komSalesTot = (!empty($sales->KomisiSalesTotal)? $sales->KomisiSalesTotal : 0);
			}
			else{
				$komSalesTot = 0;
			}

			// Cek EC
			$hari = 23.0;
			$tglec = (!empty($this->Model_insentif->salesTanggalEfektifCall($tgl, $kode, $cabang))? $this->Model_insentif->salesTanggalEfektifCall($tgl, $kode, $cabang) : 0);
			$ec = $harix= $harixs = 0;

			if($tglec == null)
			{
				$ec = 0;
			}else
			{
				foreach ($tglec as $key => $value) {
					//Hitung jumlah faktuk - retur per hari
					$count = $this->Model_insentif->salesCountPelanggan($tglec[$key]->TglFaktur, $kode, $cabang); 
					$ec = $ec + $count; 
					$nHari = date('D', strtotime($tglec[$key]->TglFaktur));
					if($nHari !='Sat')
					{
						$harix = $harix + 1;
					}else
					{
						$harixs = $harixs + 1;
					}
					
				}				
			}


			// jumlah hari menggunakan fungsi
			if($jHarixy != 0) {$hari = $this->selisihHari($tgl);}

			// Jika Jumlah hari kerja, sejumlah hari dalam sebulan di kurangi sabtu dan minggu kemudian ditambah minggu 1/2 hari
			//if($jHarixy != 0) {$hari = $jHarixy - ($harixs * 2) + (0.5 * $harixs);}			
			// Jika hari kerja berdasarkan jumlah data penjualannya di kurangi sabtu dan minggu kemudinan ditambah minggu 1/2 hari
			//if($harix != 0) {$hari = $harix - ($harixs * 2) + (0.5 * $harixs);}
			// target call per tipesalesman
			$call = $this->Model_insentif->salesCall($tipe->tipeSalesman);
			$ectarget = ($hari * $call);
			if($ectarget == 0) { $batas = 0;} else { $batas = ($ec / $ectarget);}
			$echari = ($ec / $hari);
			$ecpersen = ($batas * 100);

			// IPT
			$jumIPT = $this->Model_insentif->jumlahIPT($tgl, $kode, $cabang);

			$iptDet = (!empty($jumIPT->JumD)? $jumIPT->JumD : 0);
			$iptHed = (!empty($jumIPT->JumH)? $jumIPT->JumH : 0);

			if($iptHed==0)
			{
				$iptPci = 0;
			}else
			{
				$iptPci = $iptDet/$iptHed;
			}

			if (in_array($tipe->tipeSalesman, $arrTipe, true)) {
				$insentif = $this->Model_insentif->salesInsentifCall($batas, 'RegEC');
				$komEC = $this->Model_insentif->komisiEC($batas, 'RegEC18', '1801');
				$komIPT = $this->Model_insentif->targetIPT($iptPci, '18');
				$komROT = $this->Model_insentif->insentifROT($tgl, $kode , $cabang , '2018');
				$komRotVal = (!empty($komROT->Insentif)? $komROT ->Insentif : 0);
				$komnoo = 0;
			}
			else {
				$insentif = $this->Model_insentif->salesInsentifCall($batas, 'NonRegEC');
				$komEC = $this->Model_insentif->komisiEC($batas, 'NonReg18', '1801');
				$komIPT = 0;
				$komROT = $this->Model_insentif->insentifROT($tgl, $kode , $cabang , '2018');
				$komRotVal = 0;
				$noo = $this->Model_insentif->salesNOO($tgl, $kode, $cabang);
				$komnoo = $noo->total;
		}


			// Tagihan
			$tagihan = $this->Model_insentif->salesTagihan($tgl, $kode);
			if (!empty($sales)) {
				$salesman = $sales->KodeSalesman."-".$sales->NamaSalesman;
			}

			$komTagihan = $this->Model_insentif->komisiTaghan($tgl , $kode , $cabang , $kode);
			$komPen = $this->Model_insentif->komisiTaghanPenalti($tgl, $kode, $cabang);

			//Target pe Prinsipal
			$trgtpriAdi = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Aditama Raya');
			$trgtpriAlkes = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Alkes-lain');
			$trgtpriAlta = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Altamed');
			$trgtpriAndalan = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Andalan');
			$trgtpriArmoxindo = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Armoxindo');
			$trgtpriAxion = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Axion');
			$trgtpriBintangKK = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Bintang KK');
			$trgtpriCalumika = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Calumika');
			$trgtpriCendo = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Cendo');
			$trgtpriCoronet = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Coronet');
			$trgtpriCorsa = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Corsa');
			$trgtpriDipa = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Dipa');
			$trgtpriDipaOTC = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Dipa.OTC');
			$trgtpriEKAT = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'E-KAT');
			$trgtpriErella = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Erella');
			$trgtpriErlimpexNReg = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Erlimpex N.Reg');
			$trgtpriErlimpexReg = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Erlimpex Reg');
			$trgtpriEscolab = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Escolab');
			$trgtpriFhOTC = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Fahrenheit.OTC');
			$trgtpriFirstmed = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Firstmed');
			$trgtpriGmp = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Global');
			$trgtpriHermed = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Hermed');
			$trgtpriHoli = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Holi');
			$trgtpriHufa = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Hufa');
			$trgtpriItra = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Itrasal');
			$trgtpriKarindo = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Karindo');
			$trgtpriLainlain = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Lain-lain');
			$trgtpriLas = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Laserin');
			$trgtpriMecNR = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Mecosin N.Reg');
			$trgtpriMecOTC = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Mecosin OTC');
			$trgtpriMecReg = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Mecosin Reg.');
			$trgtpriMersi = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Mersi');
			$trgtpriNova = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Nova');
			$trgtpriNutrindo = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Nutrindo');
			$trgtpriPyridam = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Pyridam');
			$trgtprisdm_ = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'SDM');
			$trgtpriSdmEth = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'SDM ETHICAL');
			$trgtpriSdmOTC = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'SDM OTC');
			$trgtpriSdmOth = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'SDM OTHERS');
			$trgtpriSdmPck = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'SDM PEACOCK');
			$trgtpriSeles = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Seles');
			$trgtpriSolasNR = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Solas N.Reg');
			$trgtpriSolasReg = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Solas Reg');
			$trgtpriSpartaX = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Sparta-X');
			$trgtpriSunthi = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Sunthi');
			$trgtpriSutraFiesta = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Sutra Fiesta');
			$trgtpriTpNREG = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Tp.NREG');
			$trgtpriTpOBH = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Tp.OBH');
			$trgtpriTpReg = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Tp.Reg');
			$trgtpriTrifa = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Trifa');
			$trgtpriZenith = $this->Model_insentif->salesTargetPrisipal($kode, $cabang, $tgl, 'Zenith');

			$tottrgtpri = 	 (!empty($trgtpriAdi->TargetP)? $trgtpriAdi->TargetP : 0) + (!empty($trgtpriAlta->TargetP)? $trgtpriAlta->TargetP : 0) + (!empty($trgtpriAlkes->TargetP)? $trgtpriAlkes->TargetP : 0) + (!empty($trgtpriAndalan ->TargetP)? $trgtpriAndalan ->TargetP : 0) + (!empty($trgtpriArmoxindo ->TargetP)? $trgtpriArmoxindo ->TargetP : 0) + (!empty($trgtpriAxion ->TargetP)? $trgtpriAxion ->TargetP : 0) + (!empty($trgtpriBintangKK ->TargetP)? $trgtpriBintangKK ->TargetP : 0) + (!empty($trgtpriCalumika ->TargetP)? $trgtpriCalumika ->TargetP : 0) + (!empty($trgtpriCendo ->TargetP)? $trgtpriCendo ->TargetP : 0) + (!empty($trgtpriCoronet ->TargetP)? $trgtpriCoronet ->TargetP : 0) + (!empty($trgtpriCorsa ->TargetP)? $trgtpriCorsa ->TargetP : 0) + (!empty($trgtpriDipa ->TargetP)? $trgtpriDipa ->TargetP : 0) + (!empty($trgtpriDipaOTC ->TargetP)? $trgtpriDipaOTC ->TargetP : 0) + (!empty($trgtpriEKAT ->TargetP)? $trgtpriEKAT ->TargetP : 0) + (!empty($trgtpriErella ->TargetP)? $trgtpriErella ->TargetP : 0) + (!empty($trgtpriErlimpexNReg ->TargetP)? $trgtpriErlimpexNReg ->TargetP : 0) + (!empty($trgtpriErlimpexReg ->TargetP)? $trgtpriErlimpexReg ->TargetP : 0) + (!empty($trgtpriEscolab ->TargetP)? $trgtpriEscolab ->TargetP : 0) + (!empty($trgtpriFhOTC ->TargetP)? $trgtpriFhOTC ->TargetP : 0) + (!empty($trgtpriFirstmed ->TargetP)? $trgtpriFirstmed ->TargetP : 0) + (!empty($trgtpriGmp ->TargetP)? $trgtpriGmp ->TargetP : 0) + (!empty($trgtpriHermed ->TargetP)? $trgtpriHermed ->TargetP : 0) + (!empty($trgtpriHoli ->TargetP)? $trgtpriHoli ->TargetP : 0) + (!empty($trgtpriHufa ->TargetP)? $trgtpriHufa ->TargetP : 0) + (!empty($trgtpriItra ->TargetP)? $trgtpriItra ->TargetP : 0) + (!empty($trgtpriKarindo ->TargetP)? $trgtpriKarindo ->TargetP : 0) + (!empty($trgtpriLainlain ->TargetP)? $trgtpriLainlain ->TargetP : 0) + (!empty($trgtpriLas ->TargetP)? $trgtpriLas ->TargetP : 0) + (!empty($trgtpriMecNR ->TargetP)? $trgtpriMecNR ->TargetP : 0) + (!empty($trgtpriMecOTC ->TargetP)? $trgtpriMecOTC ->TargetP : 0) + (!empty($trgtpriMecReg ->TargetP)? $trgtpriMecReg ->TargetP : 0) + (!empty($trgtpriMersi ->TargetP)? $trgtpriMersi ->TargetP : 0) + (!empty($trgtpriNutrindo ->TargetP)? $trgtpriNutrindo ->TargetP : 0) + (!empty($trgtpriNova ->TargetP)? $trgtpriNova ->TargetP : 0) + (!empty($trgtpriPyridam ->TargetP)? $trgtpriPyridam ->TargetP : 0) + (!empty($trgtprisdm_ ->TargetP)? $trgtprisdm_ ->TargetP : 0) + (!empty($trgtpriSdmEth ->TargetP)? $trgtpriSdmEth ->TargetP : 0) + (!empty($trgtpriSdmOTC ->TargetP)? $trgtpriSdmOTC ->TargetP : 0) + (!empty($trgtpriSdmOth ->TargetP)? $trgtpriSdmOth ->TargetP : 0) + (!empty($trgtpriSdmPck ->TargetP)? $trgtpriSdmPck ->TargetP : 0) + (!empty($trgtpriSeles ->TargetP)? $trgtpriSeles ->TargetP : 0) + (!empty($trgtpriSolasNR ->TargetP)? $trgtpriSolasNR ->TargetP : 0) + (!empty($trgtpriSolasReg ->TargetP)? $trgtpriSolasReg ->TargetP : 0) + (!empty($trgtpriSpartaX ->TargetP)? $trgtpriSpartaX ->TargetP : 0) + (!empty($trgtpriSunthi ->TargetP)? $trgtpriSunthi ->TargetP : 0) + (!empty($trgtpriSutraFiesta ->TargetP)? $trgtpriSutraFiesta ->TargetP : 0) + (!empty($trgtpriTpNREG ->TargetP)? $trgtpriTpNREG ->TargetP : 0) + (!empty($trgtpriTpOBH ->TargetP)? $trgtpriTpOBH ->TargetP : 0) + (!empty($trgtpriTpReg ->TargetP)? $trgtpriTpReg ->TargetP : 0) + (!empty($trgtpriTrifa ->TargetP)? $trgtpriTrifa ->TargetP : 0) + (!empty($trgtpriZenith ->TargetP)? $trgtpriZenith ->TargetP : 0);

			$slspriAdi = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Aditama Raya');
			$slspriAlkes = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Alkes-lain');
			$slspriAlta = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Altamed');
			$slspriAndalan = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Andalan');
			$slspriArmoxindo = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Armoxindo');
			$slspriAxion = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Axion');
			$slspriBintangKK = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Bintang KK');
			$slspriCalumika = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Calumika');
			$slspriCendo = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Cendo');
			$slspriCoronet = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Coronet');
			$slspriCorsa = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Corsa');
			$slspriDipa = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Dipa');
			$slspriDipaOTC = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Dipa.OTC');
			$slspriEKAT = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'E-KAT');
			$slspriErella = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Erella');
			$slspriErlimpexNReg = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Erlimpex N.Reg');
			$slspriErlimpexReg = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Erlimpex Reg');
			$slspriEscolab = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Escolab');
			$slspriFhOTC = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Fahrenheit.OTC');
			$slspriFirstmed = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Firstmed');
			$slspriGmp = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Global');
			$slspriHermed = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Hermed');
			$slspriHoli = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Holi');
			$slspriHufa = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Hufa');
			$slspriItra = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Itrasal');
			$slspriKarindo = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Karindo');
			$slspriLainlain = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Lain-lain');
			$slspriLas = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Laserin');
			$slspriMecNR = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Mecosin N.Reg');
			$slspriMecOTC = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Mecosin OTC');
			$slspriMecReg = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Mecosin Reg.');
			$slspriMersi = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Mersi');
			$slspriNova = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Nova');
			$slspriNutrindo = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Nutrindo');
			$slspriPyridam = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Pyridam');
			$slsprisdm_ = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'SDM');
			$slspriSdmEth = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'SDM ETHICAL');
			$slspriSdmOTC = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'SDM OTC');
			$slspriSdmOth = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'SDM OTHERS');
			$slspriSdmPck = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'SDM PEACOCK');
			$slspriSeles = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Seles');
			$slspriSolasNR = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Solas N.Reg');
			$slspriSolasReg = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Solas Reg');
			$slspriSpartaX = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Sparta-X');
			$slspriSunthi = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Sunthi');
			$slspriSutraFiesta = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Sutra Fiesta');
			$slspriTpNREG = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Tp.NREG');
			$slspriTpOBH = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Tp.OBH');
			$slspriTpReg = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Tp.Reg');
			$slspriTrifa = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Trifa');
			$slspriZenith = $this->Model_insentif->salesPrisipal2($kode, $cabang, $tgl, 'Zenith');


			// cek penjualan
			if((!empty($trgtpriAdi->TargetP)? $trgtpriAdi->TargetP : 0)==0){$cekAdi = 0;}else{$cekAdi = ((!empty($slspriAdi ->valSalesP)? $slspriAdi ->valSalesP : 0) / (!empty($trgtpriAdi->TargetP)? $trgtpriAdi->TargetP : 0)) * 100;}
			if((!empty($trgtpriAlta->TargetP)? $trgtpriAlta->TargetP : 0)==0){$cekAlta =0;}else{$cekAlta =((!empty($slspriAlta->valSalesP)? $slspriAlta->valSalesP : 0) / (!empty($trgtpriAlta->TargetP)? $trgtpriAlta->TargetP : 0))*100;}
			if((!empty($trgtpriAlkes->TargetP)? $trgtpriAlkes->TargetP : 0)==0){$cekAlkes =0;}else{$cekAlkes =((!empty($slspriAlkes->valSalesP)? $slspriAlkes->valSalesP : 0) / (!empty($trgtpriAlkes->TargetP)? $trgtpriAlkes->TargetP : 0))*100;}
			if((!empty($trgtpriAndalan ->TargetP)? $trgtpriAndalan ->TargetP : 0)==0){$cekAndalan =0;}else{$cekAndalan =((!empty($slspriAndalan ->valSalesP)? $slspriAndalan ->valSalesP : 0) / (!empty($trgtpriAndalan ->TargetP)? $trgtpriAndalan ->TargetP : 0))*100;}
			if((!empty($trgtpriArmoxindo ->TargetP)? $trgtpriArmoxindo ->TargetP : 0)==0){$cekArmoxindo =0;}else{$cekArmoxindo =((!empty($slspriArmoxindo ->valSalesP)? $slspriArmoxindo ->valSalesP : 0) / (!empty($trgtpriArmoxindo ->TargetP)? $trgtpriArmoxindo ->TargetP : 0))*100;}
			if((!empty($trgtpriAxion ->TargetP)? $trgtpriAxion ->TargetP : 0)==0){$cekAxion =0;}else{$cekAxion =((!empty($slspriAxion ->valSalesP)? $slspriAxion ->valSalesP : 0) / (!empty($trgtpriAxion ->TargetP)? $trgtpriAxion ->TargetP : 0))*100;}
			if((!empty($trgtpriBintangKK ->TargetP)? $trgtpriBintangKK ->TargetP : 0)==0){$cekBintangKK =0;}else{$cekBintangKK =((!empty($slspriBintangKK ->valSalesP)? $slspriBintangKK ->valSalesP : 0) / (!empty($trgtpriBintangKK ->TargetP)? $trgtpriBintangKK ->TargetP : 0))*100;}
			if((!empty($trgtpriCalumika ->TargetP)? $trgtpriCalumika ->TargetP : 0)==0){$cekCalumika =0;}else{$cekCalumika =((!empty($slspriCalumika ->valSalesP)? $slspriCalumika ->valSalesP : 0) / (!empty($trgtpriCalumika ->TargetP)? $trgtpriCalumika ->TargetP : 0))*100;}
			if((!empty($trgtpriCendo ->TargetP)? $trgtpriCendo ->TargetP : 0)==0){$cekCendo =0;}else{$cekCendo =((!empty($slspriCendo ->valSalesP)? $slspriCendo ->valSalesP : 0) / (!empty($trgtpriCendo ->TargetP)? $trgtpriCendo ->TargetP : 0))*100;}
			if((!empty($trgtpriCoronet ->TargetP)? $trgtpriCoronet ->TargetP : 0)==0){$cekCoronet =0;}else{$cekCoronet =((!empty($slspriCoronet ->valSalesP)? $slspriCoronet ->valSalesP : 0) / (!empty($trgtpriCoronet ->TargetP)? $trgtpriCoronet ->TargetP : 0))*100;}
			if((!empty($trgtpriCorsa ->TargetP)? $trgtpriCorsa ->TargetP : 0)==0){$cekCorsa =0;}else{$cekCorsa =((!empty($slspriCorsa ->valSalesP)? $slspriCorsa ->valSalesP : 0) / (!empty($trgtpriCorsa ->TargetP)? $trgtpriCorsa ->TargetP : 0))*100;}
			if((!empty($trgtpriDipa ->TargetP)? $trgtpriDipa ->TargetP : 0)==0){$cekDipa =0;}else{$cekDipa =((!empty($slspriDipa ->valSalesP)? $slspriDipa ->valSalesP : 0) / (!empty($trgtpriDipa ->TargetP)? $trgtpriDipa ->TargetP : 0))*100;}
			if((!empty($trgtpriDipaOTC ->TargetP)? $trgtpriDipaOTC ->TargetP : 0)==0){$cekDipaOTC =0;}else{$cekDipaOTC =((!empty($slspriDipaOTC ->valSalesP)? $slspriDipaOTC ->valSalesP : 0) / (!empty($trgtpriDipaOTC ->TargetP)? $trgtpriDipaOTC ->TargetP : 0))*100;}
			if((!empty($trgtpriEKAT ->TargetP)? $trgtpriEKAT ->TargetP : 0)==0){$cekEKAT =0;}else{$cekEKAT =((!empty($slspriEKAT ->valSalesP)? $slspriEKAT ->valSalesP : 0) / (!empty($trgtpriEKAT ->TargetP)? $trgtpriEKAT ->TargetP : 0))*100;}
			if((!empty($trgtpriErella ->TargetP)? $trgtpriErella ->TargetP : 0)==0){$cekErella =0;}else{$cekErella =((!empty($slspriErella ->valSalesP)? $slspriErella ->valSalesP : 0) / (!empty($trgtpriErella ->TargetP)? $trgtpriErella ->TargetP : 0))*100;}
			if((!empty($trgtpriErlimpexNReg ->TargetP)? $trgtpriErlimpexNReg ->TargetP : 0)==0){$cekErlimpexNReg =0;}else{$cekErlimpexNReg =((!empty($slspriErlimpexNReg ->valSalesP)? $slspriErlimpexNReg ->valSalesP : 0) / (!empty($trgtpriErlimpexNReg ->TargetP)? $trgtpriErlimpexNReg ->TargetP : 0))*100;}
			if((!empty($trgtpriErlimpexReg ->TargetP)? $trgtpriErlimpexReg ->TargetP : 0)==0){$cekErlimpexReg =0;}else{$cekErlimpexReg =((!empty($slspriErlimpexReg ->valSalesP)? $slspriErlimpexReg ->valSalesP : 0) / (!empty($trgtpriErlimpexReg ->TargetP)? $trgtpriErlimpexReg ->TargetP : 0))*100;}
			if((!empty($trgtpriEscolab ->TargetP)? $trgtpriEscolab ->TargetP : 0)==0){$cekEscolab =0;}else{$cekEscolab =((!empty($slspriEscolab ->valSalesP)? $slspriEscolab ->valSalesP : 0) / (!empty($trgtpriEscolab ->TargetP)? $trgtpriEscolab ->TargetP : 0))*100;}
			if((!empty($trgtpriFhOTC ->TargetP)? $trgtpriFhOTC ->TargetP : 0)==0){$cekFhOTC =0;}else{$cekFhOTC =((!empty($slspriFhOTC ->valSalesP)? $slspriFhOTC ->valSalesP : 0) / (!empty($trgtpriFhOTC ->TargetP)? $trgtpriFhOTC ->TargetP : 0))*100;}
			if((!empty($trgtpriFirstmed ->TargetP)? $trgtpriFirstmed ->TargetP : 0)==0){$cekFirstmed =0;}else{$cekFirstmed =((!empty($slspriFirstmed ->valSalesP)? $slspriFirstmed ->valSalesP : 0) / (!empty($trgtpriFirstmed ->TargetP)? $trgtpriFirstmed ->TargetP : 0))*100;}
			if((!empty($trgtpriGmp ->TargetP)? $trgtpriGmp ->TargetP : 0)==0){$cekGmp =0;}else{$cekGmp =((!empty($slspriGmp ->valSalesP)? $slspriGmp ->valSalesP : 0) / (!empty($trgtpriGmp ->TargetP)? $trgtpriGmp ->TargetP : 0))*100;}
			if((!empty($trgtpriHermed ->TargetP)? $trgtpriHermed ->TargetP : 0)==0){$cekHermed =0;}else{$cekHermed =((!empty($slspriHermed ->valSalesP)? $slspriHermed ->valSalesP : 0) / (!empty($trgtpriHermed ->TargetP)? $trgtpriHermed ->TargetP : 0))*100;}
			if((!empty($trgtpriHoli ->TargetP)? $trgtpriHoli ->TargetP : 0)==0){$cekHoli =0;}else{$cekHoli =((!empty($slspriHoli ->valSalesP)? $slspriHoli ->valSalesP : 0) / (!empty($trgtpriHoli ->TargetP)? $trgtpriHoli ->TargetP : 0))*100;}
			if((!empty($trgtpriHufa ->TargetP)? $trgtpriHufa ->TargetP : 0)==0){$cekHufa =0;}else{$cekHufa =((!empty($slspriHufa ->valSalesP)? $slspriHufa ->valSalesP : 0) / (!empty($trgtpriHufa ->TargetP)? $trgtpriHufa ->TargetP : 0))*100;}
			if((!empty($trgtpriItra ->TargetP)? $trgtpriItra ->TargetP : 0)==0){$cekItra =0;}else{$cekItra =((!empty($slspriItra ->valSalesP)? $slspriItra ->valSalesP : 0) / (!empty($trgtpriItra ->TargetP)? $trgtpriItra ->TargetP : 0))*100;}
			if((!empty($trgtpriKarindo ->TargetP)? $trgtpriKarindo ->TargetP : 0)==0){$cekKarindo =0;}else{$cekKarindo =((!empty($slspriKarindo ->valSalesP)? $slspriKarindo ->valSalesP : 0) / (!empty($trgtpriKarindo ->TargetP)? $trgtpriKarindo ->TargetP : 0))*100;}
			if((!empty($trgtpriLainlain ->TargetP)? $trgtpriLainlain ->TargetP : 0)==0){$cekLainlain =0;}else{$cekLainlain =((!empty($slspriLainlain ->valSalesP)? $slspriLainlain ->valSalesP : 0) / (!empty($trgtpriLainlain ->TargetP)? $trgtpriLainlain ->TargetP : 0))*100;}
			if((!empty($trgtpriLas ->TargetP)? $trgtpriLas ->TargetP : 0)==0){$cekLas =0;}else{$cekLas =((!empty($slspriLas ->valSalesP)? $slspriLas ->valSalesP : 0) / (!empty($trgtpriLas ->TargetP)? $trgtpriLas ->TargetP : 0))*100;}
			if((!empty($trgtpriMecNR ->TargetP)? $trgtpriMecNR ->TargetP : 0)==0){$cekMecNR =0;}else{$cekMecNR =((!empty($slspriMecNR ->valSalesP)? $slspriMecNR ->valSalesP : 0) / (!empty($trgtpriMecNR ->TargetP)? $trgtpriMecNR ->TargetP : 0))*100;}
			if((!empty($trgtpriMecOTC ->TargetP)? $trgtpriMecOTC ->TargetP : 0)==0){$cekMecOTC =0;}else{$cekMecOTC =((!empty($slspriMecOTC ->valSalesP)? $slspriMecOTC ->valSalesP : 0) / (!empty($trgtpriMecOTC ->TargetP)? $trgtpriMecOTC ->TargetP : 0))*100;}
			if((!empty($trgtpriMecReg ->TargetP)? $trgtpriMecReg ->TargetP : 0)==0){$cekMecReg =0;}else{$cekMecReg =((!empty($slspriMecReg ->valSalesP)? $slspriMecReg ->valSalesP : 0) / (!empty($trgtpriMecReg ->TargetP)? $trgtpriMecReg ->TargetP : 0))*100;}
			if((!empty($trgtpriMersi ->TargetP)? $trgtpriMersi ->TargetP : 0)==0){$cekMersi =0;}else{$cekMersi =((!empty($slspriMersi ->valSalesP)? $slspriMersi ->valSalesP : 0) / (!empty($trgtpriMersi ->TargetP)? $trgtpriMersi ->TargetP : 0))*100;}
			if((!empty($trgtpriNutrindo ->TargetP)? $trgtpriNutrindo ->TargetP : 0)==0){$cekNutrindo =0;}else{$cekNutrindo =((!empty($slspriNutrindo ->valSalesP)? $slspriNutrindo ->valSalesP : 0) / (!empty($trgtpriNutrindo ->TargetP)? $trgtpriNutrindo ->TargetP : 0))*100;}
			if((!empty($trgtpriNova ->TargetP)? $trgtpriNova ->TargetP : 0)==0){$cekNova =0;}else{$cekNova =((!empty($slspriNova ->valSalesP)? $slspriNova ->valSalesP : 0) / (!empty($trgtpriNova ->TargetP)? $trgtpriNova ->TargetP : 0))*100;}
			if((!empty($trgtpriPyridam ->TargetP)? $trgtpriPyridam ->TargetP : 0)==0){$cekPyridam =0;}else{$cekPyridam =((!empty($slspriPyridam ->valSalesP)? $slspriPyridam ->valSalesP : 0) / (!empty($trgtpriPyridam ->TargetP)? $trgtpriPyridam ->TargetP : 0))*100;}
			if((!empty($trgtprisdm_ ->TargetP)? $trgtprisdm_ ->TargetP : 0)==0){$ceksdm_ =0;}else{$ceksdm_ =((!empty($slsprisdm_ ->valSalesP)? $slsprisdm_ ->valSalesP : 0) / (!empty($trgtprisdm_ ->TargetP)? $trgtprisdm_ ->TargetP : 0))*100;}
			if((!empty($trgtpriSdmEth ->TargetP)? $trgtpriSdmEth ->TargetP : 0)==0){$cekSdmEth =0;}else{$cekSdmEth =((!empty($slspriSdmEth ->valSalesP)? $slspriSdmEth ->valSalesP : 0) / (!empty($trgtpriSdmEth ->TargetP)? $trgtpriSdmEth ->TargetP : 0))*100;}
			if((!empty($trgtpriSdmOTC ->TargetP)? $trgtpriSdmOTC ->TargetP : 0)==0){$cekSdmOTC =0;}else{$cekSdmOTC =((!empty($slspriSdmOTC ->valSalesP)? $slspriSdmOTC ->valSalesP : 0) / (!empty($trgtpriSdmOTC ->TargetP)? $trgtpriSdmOTC ->TargetP : 0))*100;}
			if((!empty($trgtpriSdmOth ->TargetP)? $trgtpriSdmOth ->TargetP : 0)==0){$cekSdmOth =0;}else{$cekSdmOth =((!empty($slspriSdmOth ->valSalesP)? $slspriSdmOth ->valSalesP : 0) / (!empty($trgtpriSdmOth ->TargetP)? $trgtpriSdmOth ->TargetP : 0))*100;}
			if((!empty($trgtpriSdmPck ->TargetP)? $trgtpriSdmPck ->TargetP : 0)==0){$cekSdmPck =0;}else{$cekSdmPck =((!empty($slspriSdmPck ->valSalesP)? $slspriSdmPck ->valSalesP : 0) / (!empty($trgtpriSdmPck ->TargetP)? $trgtpriSdmPck ->TargetP : 0))*100;}
			if((!empty($trgtpriSeles ->TargetP)? $trgtpriSeles ->TargetP : 0)==0){$cekSeles =0;}else{$cekSeles =((!empty($slspriSeles ->valSalesP)? $slspriSeles ->valSalesP : 0) / (!empty($trgtpriSeles ->TargetP)? $trgtpriSeles ->TargetP : 0))*100;}
			if((!empty($trgtpriSolasNR ->TargetP)? $trgtpriSolasNR ->TargetP : 0)==0){$cekSolasNR =0;}else{$cekSolasNR =((!empty($slspriSolasNR ->valSalesP)? $slspriSolasNR ->valSalesP : 0) / (!empty($trgtpriSolasNR ->TargetP)? $trgtpriSolasNR ->TargetP : 0))*100;}
			if((!empty($trgtpriSolasReg ->TargetP)? $trgtpriSolasReg ->TargetP : 0)==0){$cekSolasReg =0;}else{$cekSolasReg =((!empty($slspriSolasReg ->valSalesP)? $slspriSolasReg ->valSalesP : 0) / (!empty($trgtpriSolasReg ->TargetP)? $trgtpriSolasReg ->TargetP : 0))*100;}
			if((!empty($trgtpriSpartaX ->TargetP)? $trgtpriSpartaX ->TargetP : 0)==0){$cekSpartaX =0;}else{$cekSpartaX =((!empty($slspriSpartaX ->valSalesP)? $slspriSpartaX ->valSalesP : 0) / (!empty($trgtpriSpartaX ->TargetP)? $trgtpriSpartaX ->TargetP : 0))*100;}
			if((!empty($trgtpriSunthi ->TargetP)? $trgtpriSunthi ->TargetP : 0)==0){$cekSunthi =0;}else{$cekSunthi =((!empty($slspriSunthi ->valSalesP)? $slspriSunthi ->valSalesP : 0) / (!empty($trgtpriSunthi ->TargetP)? $trgtpriSunthi ->TargetP : 0))*100;}
			if((!empty($trgtpriSutraFiesta ->TargetP)? $trgtpriSutraFiesta ->TargetP : 0)==0){$cekSutraFiesta =0;}else{$cekSutraFiesta =((!empty($slspriSutraFiesta ->valSalesP)? $slspriSutraFiesta ->valSalesP : 0) / (!empty($trgtpriSutraFiesta ->TargetP)? $trgtpriSutraFiesta ->TargetP : 0))*100;}
			if((!empty($trgtpriTpNREG ->TargetP)? $trgtpriTpNREG ->TargetP : 0)==0){$cekTpNREG =0;}else{$cekTpNREG =((!empty($slspriTpNREG ->valSalesP)? $slspriTpNREG ->valSalesP : 0) / (!empty($trgtpriTpNREG ->TargetP)? $trgtpriTpNREG ->TargetP : 0))*100;}
			if((!empty($trgtpriTpOBH ->TargetP)? $trgtpriTpOBH ->TargetP : 0)==0){$cekTpOBH =0;}else{$cekTpOBH =((!empty($slspriTpOBH ->valSalesP)? $slspriTpOBH ->valSalesP : 0) / (!empty($trgtpriTpOBH ->TargetP)? $trgtpriTpOBH ->TargetP : 0))*100;}
			if((!empty($trgtpriTpReg ->TargetP)? $trgtpriTpReg ->TargetP : 0)==0){$cekTpReg =0;}else{$cekTpReg =((!empty($slspriTpReg ->valSalesP)? $slspriTpReg ->valSalesP : 0) / (!empty($trgtpriTpReg ->TargetP)? $trgtpriTpReg ->TargetP : 0))*100;}
			if((!empty($trgtpriTrifa ->TargetP)? $trgtpriTrifa ->TargetP : 0)==0){$cekTrifa =0;}else{$cekTrifa =((!empty($slspriTrifa ->valSalesP)? $slspriTrifa ->valSalesP : 0) / (!empty($trgtpriTrifa ->TargetP)? $trgtpriTrifa ->TargetP : 0))*100;}
			if((!empty($trgtpriZenith ->TargetP)? $trgtpriZenith ->TargetP : 0)==0){$cekZenith =0;}else{$cekZenith =((!empty($slspriZenith ->valSalesP)? $slspriZenith ->valSalesP : 0) / (!empty($trgtpriZenith ->TargetP)? $trgtpriZenith ->TargetP : 0))*100;}

			// cek saleh harus lebih besar dari 100%
			if (in_array($tipe->tipeSalesman, $arrTipe, true)) {
				if ($cekAdi>=100) { $komAdi = (!empty($slspriAdi ->KomSalesDisk)? $slspriAdi ->KomSalesDisk : 0); }else { $komAdi = 0; }
				if($cekAlta >=100){$komAlta =(!empty($slspriAlta->KomSalesDisk)? $slspriAlta->KomSalesDisk : 0);}else{$komAlta =0;}
				if($cekAlkes >=100){$komAlkes =(!empty($slspriAlkes->KomSalesDisk)? $slspriAlkes->KomSalesDisk : 0);}else{$komAlkes =0;}
				if($cekAndalan >=100){$komAndalan =(!empty($slspriAndalan ->KomSalesDisk)? $slspriAndalan ->KomSalesDisk : 0);}else{$komAndalan =0;}
				if($cekArmoxindo >=100){$komArmoxindo =(!empty($slspriArmoxindo ->KomSalesDisk)? $slspriArmoxindo ->KomSalesDisk : 0);}else{$komArmoxindo =0;}
				if($cekAxion >=100){$komAxion =(!empty($slspriAxion ->KomSalesDisk)? $slspriAxion ->KomSalesDisk : 0);}else{$komAxion =0;}
				if($cekBintangKK >=100){$komBintangKK =(!empty($slspriBintangKK ->KomSalesDisk)? $slspriBintangKK ->KomSalesDisk : 0);}else{$komBintangKK =0;}
				if($cekCalumika >=100){$komCalumika =(!empty($slspriCalumika ->KomSalesDisk)? $slspriCalumika ->KomSalesDisk : 0);}else{$komCalumika =0;}
				if($cekCendo >=100){$komCendo =(!empty($slspriCendo ->KomSalesDisk)? $slspriCendo ->KomSalesDisk : 0);}else{$komCendo =0;}
				if($cekCoronet >=100){$komCoronet =(!empty($slspriCoronet ->KomSalesDisk)? $slspriCoronet ->KomSalesDisk : 0);}else{$komCoronet =0;}
				if($cekCorsa >=100){$komCorsa =(!empty($slspriCorsa ->KomSalesDisk)? $slspriCorsa ->KomSalesDisk : 0);}else{$komCorsa =0;}
				if($cekDipa >=100){$komDipa =(!empty($slspriDipa ->KomSalesDisk)? $slspriDipa ->KomSalesDisk : 0);}else{$komDipa =0;}
				if($cekDipaOTC >=100){$komDipaOTC =(!empty($slspriDipaOTC ->KomSalesDisk)? $slspriDipaOTC ->KomSalesDisk : 0);}else{$komDipaOTC =0;}
				if($cekEKAT >=100){$komEKAT =(!empty($slspriEKAT ->KomSalesDisk)? $slspriEKAT ->KomSalesDisk : 0);}else{$komEKAT =0;}
				if($cekErella >=100){$komErella =(!empty($slspriErella ->KomSalesDisk)? $slspriErella ->KomSalesDisk : 0);}else{$komErella =0;}
				if($cekErlimpexNReg >=100){$komErlimpexNReg =(!empty($slspriErlimpexNReg ->KomSalesDisk)? $slspriErlimpexNReg ->KomSalesDisk : 0);}else{$komErlimpexNReg =0;}
				if($cekErlimpexReg >=100){$komErlimpexReg =(!empty($slspriErlimpexReg ->KomSalesDisk)? $slspriErlimpexReg ->KomSalesDisk : 0);}else{$komErlimpexReg =0;}
				if($cekEscolab >=100){$komEscolab =(!empty($slspriEscolab ->KomSalesDisk)? $slspriEscolab ->KomSalesDisk : 0);}else{$komEscolab =0;}
				if($cekFhOTC >=100){$komFhOTC =(!empty($slspriFhOTC ->KomSalesDisk)? $slspriFhOTC ->KomSalesDisk : 0);}else{$komFhOTC =0;}
				if($cekFirstmed >=100){$komFirstmed =(!empty($slspriFirstmed ->KomSalesDisk)? $slspriFirstmed ->KomSalesDisk : 0);}else{$komFirstmed =0;}
				if($cekGmp >=100){$komGmp =(!empty($slspriGmp ->KomSalesDisk)? $slspriGmp ->KomSalesDisk : 0);}else{$komGmp =0;}
				if($cekHermed >=100){$komHermed =(!empty($slspriHermed ->KomSalesDisk)? $slspriHermed ->KomSalesDisk : 0);}else{$komHermed =0;}
				if($cekHoli >=100){$komHoli =(!empty($slspriHoli ->KomSalesDisk)? $slspriHoli ->KomSalesDisk : 0);}else{$komHoli =0;}
				if($cekHufa >=100){$komHufa =(!empty($slspriHufa ->KomSalesDisk)? $slspriHufa ->KomSalesDisk : 0);}else{$komHufa =0;}
				if($cekItra >=100){$komItra =(!empty($slspriItra ->KomSalesDisk)? $slspriItra ->KomSalesDisk : 0);}else{$komItra =0;}
				if($cekKarindo >=100){$komKarindo =(!empty($slspriKarindo ->KomSalesDisk)? $slspriKarindo ->KomSalesDisk : 0);}else{$komKarindo =0;}
				if($cekLainlain >=100){$komLainlain =(!empty($slspriLainlain ->KomSalesDisk)? $slspriLainlain ->KomSalesDisk : 0);}else{$komLainlain =0;}
				if($cekLas >=100){$komLas =(!empty($slspriLas ->KomSalesDisk)? $slspriLas ->KomSalesDisk : 0);}else{$komLas =0;}
				if($cekMecNR >=100){$komMecNR =(!empty($slspriMecNR ->KomSalesDisk)? $slspriMecNR ->KomSalesDisk : 0);}else{$komMecNR =0;}
				if($cekMecOTC >=100){$komMecOTC =(!empty($slspriMecOTC ->KomSalesDisk)? $slspriMecOTC ->KomSalesDisk : 0);}else{$komMecOTC =0;}
				if($cekMecReg >=100){$komMecReg =(!empty($slspriMecReg ->KomSalesDisk)? $slspriMecReg ->KomSalesDisk : 0);}else{$komMecReg =0;}
				if($cekMersi >=100){$komMersi =(!empty($slspriMersi ->KomSalesDisk)? $slspriMersi ->KomSalesDisk : 0);}else{$komMersi =0;}
				if($cekNutrindo >=100){$komNutrindo =(!empty($slspriNutrindo ->KomSalesDisk)? $slspriNutrindo ->KomSalesDisk : 0);}else{$komNutrindo =0;}
				if($cekNova >=100){$komNova =(!empty($slspriNova ->KomSalesDisk)? $slspriNova ->KomSalesDisk : 0);}else{$komNova =0;}
				if($cekPyridam >=100){$komPyridam =(!empty($slspriPyridam ->KomSalesDisk)? $slspriPyridam ->KomSalesDisk : 0);}else{$komPyridam =0;}
				if($ceksdm_ >=100){$komsdm_ =(!empty($slsprisdm_ ->KomSalesDisk)? $slsprisdm_ ->KomSalesDisk : 0);}else{$komsdm_ =0;}
				if($cekSdmEth >=100){$komSdmEth =(!empty($slspriSdmEth ->KomSalesDisk)? $slspriSdmEth ->KomSalesDisk : 0);}else{$komSdmEth =0;}
				if($cekSdmOTC >=100){$komSdmOTC =(!empty($slspriSdmOTC ->KomSalesDisk)? $slspriSdmOTC ->KomSalesDisk : 0);}else{$komSdmOTC =0;}
				if($cekSdmOth >=100){$komSdmOth =(!empty($slspriSdmOth ->KomSalesDisk)? $slspriSdmOth ->KomSalesDisk : 0);}else{$komSdmOth =0;}
				if($cekSdmPck >=100){$komSdmPck =(!empty($slspriSdmPck ->KomSalesDisk)? $slspriSdmPck ->KomSalesDisk : 0);}else{$komSdmPck =0;}
				if($cekSeles >=100){$komSeles =(!empty($slspriSeles ->KomSalesDisk)? $slspriSeles ->KomSalesDisk : 0);}else{$komSeles =0;}
				if($cekSolasNR >=100){$komSolasNR =(!empty($slspriSolasNR ->KomSalesDisk)? $slspriSolasNR ->KomSalesDisk : 0);}else{$komSolasNR =0;}
				if($cekSolasReg >=100){$komSolasReg =(!empty($slspriSolasReg ->KomSalesDisk)? $slspriSolasReg ->KomSalesDisk : 0);}else{$komSolasReg =0;}
				if($cekSpartaX >=100){$komSpartaX =(!empty($slspriSpartaX ->KomSalesDisk)? $slspriSpartaX ->KomSalesDisk : 0);}else{$komSpartaX =0;}
				if($cekSunthi >=100){$komSunthi =(!empty($slspriSunthi ->KomSalesDisk)? $slspriSunthi ->KomSalesDisk : 0);}else{$komSunthi =0;}
				if($cekSutraFiesta >=100){$komSutraFiesta =(!empty($slspriSutraFiesta ->KomSalesDisk)? $slspriSutraFiesta ->KomSalesDisk : 0);}else{$komSutraFiesta =0;}
				if($cekTpNREG >=100){$komTpNREG =(!empty($slspriTpNREG ->KomSalesDisk)? $slspriTpNREG ->KomSalesDisk : 0);}else{$komTpNREG =0;}
				if($cekTpOBH >=100){$komTpOBH =(!empty($slspriTpOBH ->KomSalesDisk)? $slspriTpOBH ->KomSalesDisk : 0);}else{$komTpOBH =0;}
				if($cekTpReg >=100){$komTpReg =(!empty($slspriTpReg ->KomSalesDisk)? $slspriTpReg ->KomSalesDisk : 0);}else{$komTpReg =0;}
				if($cekTrifa >=100){$komTrifa =(!empty($slspriTrifa ->KomSalesDisk)? $slspriTrifa ->KomSalesDisk : 0);}else{$komTrifa =0;}
				if($cekZenith >=100){$komZenith =(!empty($slspriZenith ->KomSalesDisk)? $slspriZenith ->KomSalesDisk : 0);}else{$komZenith =0;}
			}else
			{
				$komAdi = $komAlta = $komAlkes = $komAndalan = $komArmoxindo = $komAxion = $komBintangKK = $komCalumika = $komCendo = $komCoronet = $komCorsa = $komDipa = $komDipaOTC = $komEKAT = $komErella = $komErlimpexNReg = $komErlimpexReg = $komEscolab = $komFhOTC = $komFirstmed = $komGmp = $komHermed = $komHoli = $komHufa = $komItra = $komKarindo = $komLainlain = $komLas = $komMecNR = $komMecOTC = $komMecReg = $komMersi = $komNutrindo = $komNova = $komPyridam = $komsdm_ = $komSdmEth = $komSdmOTC = $komSdmOth = $komSdmPck = $komSeles = $komSolasNR = $komSolasReg = $komSpartaX = $komSunthi = $komSutraFiesta = $komTpNREG = $komTpOBH = $komTpReg = $komTrifa = $komZenith = 0;
			}
			$komAlkes = $komLainlain = $komEKAT = 0 ;
			$lokCab = (!empty($this->Model_insentif->getCabangLok($cabang))? $this->Model_insentif->getCabangLok($cabang) : '');
			if( $lokCab == 'Jawa' or  $lokCab == '' or  $lokCab == '' )
				$komArmoxindo = $komTrifa = 0;


			$totPriSaleskom = $komAdi +$komAlta +$komAlkes +$komAndalan +$komArmoxindo +$komAxion +$komBintangKK +$komCalumika +$komCendo +$komCoronet +$komCorsa +$komDipa +$komDipaOTC +$komEKAT +$komErella +$komErlimpexNReg +$komErlimpexReg +$komEscolab +$komFhOTC +$komFirstmed +$komGmp +$komHermed +$komHoli +$komHufa +$komItra +$komKarindo +$komLainlain +$komLas +$komMecNR +$komMecOTC +$komMecReg +$komMersi +$komNutrindo +$komNova +$komPyridam +$komsdm_ +$komSdmEth +$komSdmOTC +$komSdmOth +$komSdmPck +$komSeles +$komSolasNR +$komSolasReg +$komSpartaX +$komSunthi +$komSutraFiesta +$komTpNREG +$komTpOBH +$komTpReg +$komTrifa +$komZenith;

			$output = array(
							"detSalesman" => $detSalesmanDat,
							"tipesalesmanD" => $tipe->tipeSalesman,
							"sales" => (!empty($sales->PencapaianSales)? $sales->PencapaianSales : 0),
							"target" => (!empty($sales->Target)? $sales->Target : 0),
							"persenKS" => (!empty($sales->Persen)? $sales->Persen : 0),
							"valKS" => $komSalesTot,
							"valtrgtprisipal" => $tottrgtpri,
							"targetAdi" => (!empty($trgtpriAdi->TargetP)? $trgtpriAdi->TargetP : 0),
							"targetAlta" => (!empty($trgtpriAlta->TargetP)? $trgtpriAlta->TargetP : 0),
							"targetAlkes" => (!empty($trgtpriAlkes->TargetP)? $trgtpriAlkes->TargetP : 0),
							"targetAndalan" => (!empty($trgtpriAndalan ->TargetP)? $trgtpriAndalan ->TargetP : 0),
							"targetArmoxindo" => (!empty($trgtpriArmoxindo ->TargetP)? $trgtpriArmoxindo ->TargetP : 0),
							"targetAxion" => (!empty($trgtpriAxion ->TargetP)? $trgtpriAxion ->TargetP : 0),
							"targetBintangKK" => (!empty($trgtpriBintangKK ->TargetP)? $trgtpriBintangKK ->TargetP : 0),
							"targetCalumika" => (!empty($trgtpriCalumika ->TargetP)? $trgtpriCalumika ->TargetP : 0),
							"targetCendo" => (!empty($trgtpriCendo ->TargetP)? $trgtpriCendo ->TargetP : 0),
							"targetCoronet" => (!empty($trgtpriCoronet ->TargetP)? $trgtpriCoronet ->TargetP : 0),
							"targetCorsa" => (!empty($trgtpriCorsa ->TargetP)? $trgtpriCorsa ->TargetP : 0),
							"targetDipa" => (!empty($trgtpriDipa ->TargetP)? $trgtpriDipa ->TargetP : 0),
							"targetDipaOTC" => (!empty($trgtpriDipaOTC ->TargetP)? $trgtpriDipaOTC ->TargetP : 0),
							"targetEKAT" => (!empty($trgtpriEKAT ->TargetP)? $trgtpriEKAT ->TargetP : 0),
							"targetErella" => (!empty($trgtpriErella ->TargetP)? $trgtpriErella ->TargetP : 0),
							"targetErlimpexNReg" => (!empty($trgtpriErlimpexNReg ->TargetP)? $trgtpriErlimpexNReg ->TargetP : 0),
							"targetErlimpexReg" => (!empty($trgtpriErlimpexReg ->TargetP)? $trgtpriErlimpexReg ->TargetP : 0),
							"targetEscolab" => (!empty($trgtpriEscolab ->TargetP)? $trgtpriEscolab ->TargetP : 0),
							"targetFhOTC" => (!empty($trgtpriFhOTC ->TargetP)? $trgtpriFhOTC ->TargetP : 0),
							"targetFirstmed" => (!empty($trgtpriFirstmed ->TargetP)? $trgtpriFirstmed ->TargetP : 0),
							"targetGmp" => (!empty($trgtpriGmp ->TargetP)? $trgtpriGmp ->TargetP : 0),
							"targetHermed" => (!empty($trgtpriHermed ->TargetP)? $trgtpriHermed ->TargetP : 0),
							"targetHoli" => (!empty($trgtpriHoli ->TargetP)? $trgtpriHoli ->TargetP : 0),
							"targetHufa" => (!empty($trgtpriHufa ->TargetP)? $trgtpriHufa ->TargetP : 0),
							"targetItra" => (!empty($trgtpriItra ->TargetP)? $trgtpriItra ->TargetP : 0),
							"targetKarindo" => (!empty($trgtpriKarindo ->TargetP)? $trgtpriKarindo ->TargetP : 0),
							"targetLainlain" => (!empty($trgtpriLainlain ->TargetP)? $trgtpriLainlain ->TargetP : 0),
							"targetLas" => (!empty($trgtpriLas ->TargetP)? $trgtpriLas ->TargetP : 0),
							"targetMecNR" => (!empty($trgtpriMecNR ->TargetP)? $trgtpriMecNR ->TargetP : 0),
							"targetMecOTC" => (!empty($trgtpriMecOTC ->TargetP)? $trgtpriMecOTC ->TargetP : 0),
							"targetMecReg" => (!empty($trgtpriMecReg ->TargetP)? $trgtpriMecReg ->TargetP : 0),
							"targetMersi" => (!empty($trgtpriMersi ->TargetP)? $trgtpriMersi ->TargetP : 0),
							"targetNutrindo" => (!empty($trgtpriNutrindo ->TargetP)? $trgtpriNutrindo ->TargetP : 0),
							"targetNova" => (!empty($trgtpriNova ->TargetP)? $trgtpriNova ->TargetP : 0),
							"targetPyridam" => (!empty($trgtpriPyridam ->TargetP)? $trgtpriPyridam ->TargetP : 0),
							"targetsdm_" => (!empty($trgtprisdm_ ->TargetP)? $trgtprisdm_ ->TargetP : 0),
							"targetSdmEth" => (!empty($trgtpriSdmEth ->TargetP)? $trgtpriSdmEth ->TargetP : 0),
							"targetSdmOTC" => (!empty($trgtpriSdmOTC ->TargetP)? $trgtpriSdmOTC ->TargetP : 0),
							"targetSdmOth" => (!empty($trgtpriSdmOth ->TargetP)? $trgtpriSdmOth ->TargetP : 0),
							"targetSdmPck" => (!empty($trgtpriSdmPck ->TargetP)? $trgtpriSdmPck ->TargetP : 0),
							"targetSeles" => (!empty($trgtpriSeles ->TargetP)? $trgtpriSeles ->TargetP : 0),
							"targetSolasNR" => (!empty($trgtpriSolasNR ->TargetP)? $trgtpriSolasNR ->TargetP : 0),
							"targetSolasReg" => (!empty($trgtpriSolasReg ->TargetP)? $trgtpriSolasReg ->TargetP : 0),
							"targetSpartaX" => (!empty($trgtpriSpartaX ->TargetP)? $trgtpriSpartaX ->TargetP : 0),
							"targetSunthi" => (!empty($trgtpriSunthi ->TargetP)? $trgtpriSunthi ->TargetP : 0),
							"targetSutraFiesta" => (!empty($trgtpriSutraFiesta ->TargetP)? $trgtpriSutraFiesta ->TargetP : 0),
							"targetTpNREG" => (!empty($trgtpriTpNREG ->TargetP)? $trgtpriTpNREG ->TargetP : 0),
							"targetTpOBH" => (!empty($trgtpriTpOBH ->TargetP)? $trgtpriTpOBH ->TargetP : 0),
							"targetTpReg" => (!empty($trgtpriTpReg ->TargetP)? $trgtpriTpReg ->TargetP : 0),
							"targetTrifa" => (!empty($trgtpriTrifa ->TargetP)? $trgtpriTrifa ->TargetP : 0),
							"targetZenith" => (!empty($trgtpriZenith ->TargetP)? $trgtpriZenith ->TargetP : 0),
							"SalesAdi" => (!empty($slspriAdi ->valSalesP)? $slspriAdi ->valSalesP : 0),
							"SalesAlta" => (!empty($slspriAlta->valSalesP)? $slspriAlta->valSalesP : 0),
							"SalesAlkes" => (!empty($slspriAlkes->valSalesP)? $slspriAlkes->valSalesP : 0),
							"SalesAndalan" => (!empty($slspriAndalan ->valSalesP)? $slspriAndalan ->valSalesP : 0),
							"SalesArmoxindo" => (!empty($slspriArmoxindo ->valSalesP)? $slspriArmoxindo ->valSalesP : 0),
							"SalesAxion" => (!empty($slspriAxion ->valSalesP)? $slspriAxion ->valSalesP : 0),
							"SalesBintangKK" => (!empty($slspriBintangKK ->valSalesP)? $slspriBintangKK ->valSalesP : 0),
							"SalesCalumika" => (!empty($slspriCalumika ->valSalesP)? $slspriCalumika ->valSalesP : 0),
							"SalesCendo" => (!empty($slspriCendo ->valSalesP)? $slspriCendo ->valSalesP : 0),
							"SalesCoronet" => (!empty($slspriCoronet ->valSalesP)? $slspriCoronet ->valSalesP : 0),
							"SalesCorsa" => (!empty($slspriCorsa ->valSalesP)? $slspriCorsa ->valSalesP : 0),
							"SalesDipa" => (!empty($slspriDipa ->valSalesP)? $slspriDipa ->valSalesP : 0),
							"SalesDipaOTC" => (!empty($slspriDipaOTC ->valSalesP)? $slspriDipaOTC ->valSalesP : 0),
							"SalesEKAT" => (!empty($slspriEKAT ->valSalesP)? $slspriEKAT ->valSalesP : 0),
							"SalesErella" => (!empty($slspriErella ->valSalesP)? $slspriErella ->valSalesP : 0),
							"SalesErlimpexNReg" => (!empty($slspriErlimpexNReg ->valSalesP)? $slspriErlimpexNReg ->valSalesP : 0),
							"SalesErlimpexReg" => (!empty($slspriErlimpexReg ->valSalesP)? $slspriErlimpexReg ->valSalesP : 0),
							"SalesEscolab" => (!empty($slspriEscolab ->valSalesP)? $slspriEscolab ->valSalesP : 0),
							"SalesFhOTC" => (!empty($slspriFhOTC ->valSalesP)? $slspriFhOTC ->valSalesP : 0),
							"SalesFirstmed" => (!empty($slspriFirstmed ->valSalesP)? $slspriFirstmed ->valSalesP : 0),
							"SalesGmp" => (!empty($slspriGmp ->valSalesP)? $slspriGmp ->valSalesP : 0),
							"SalesHermed" => (!empty($slspriHermed ->valSalesP)? $slspriHermed ->valSalesP : 0),
							"SalesHoli" => (!empty($slspriHoli ->valSalesP)? $slspriHoli ->valSalesP : 0),
							"SalesHufa" => (!empty($slspriHufa ->valSalesP)? $slspriHufa ->valSalesP : 0),
							"SalesItra" => (!empty($slspriItra ->valSalesP)? $slspriItra ->valSalesP : 0),
							"SalesKarindo" => (!empty($slspriKarindo ->valSalesP)? $slspriKarindo ->valSalesP : 0),
							"SalesLainlain" => (!empty($slspriLainlain ->valSalesP)? $slspriLainlain ->valSalesP : 0),
							"SalesLas" => (!empty($slspriLas ->valSalesP)? $slspriLas ->valSalesP : 0),
							"SalesMecNR" => (!empty($slspriMecNR ->valSalesP)? $slspriMecNR ->valSalesP : 0),
							"SalesMecOTC" => (!empty($slspriMecOTC ->valSalesP)? $slspriMecOTC ->valSalesP : 0),
							"SalesMecReg" => (!empty($slspriMecReg ->valSalesP)? $slspriMecReg ->valSalesP : 0),
							"SalesMersi" => (!empty($slspriMersi ->valSalesP)? $slspriMersi ->valSalesP : 0),
							"SalesNutrindo" => (!empty($slspriNutrindo ->valSalesP)? $slspriNutrindo ->valSalesP : 0),
							"SalesNova" => (!empty($slspriNova ->valSalesP)? $slspriNova ->valSalesP : 0),
							"SalesPyridam" => (!empty($slspriPyridam ->valSalesP)? $slspriPyridam ->valSalesP : 0),
							"Salessdm_" => (!empty($slsprisdm_ ->valSalesP)? $slsprisdm_ ->valSalesP : 0),
							"SalesSdmEth" => (!empty($slspriSdmEth ->valSalesP)? $slspriSdmEth ->valSalesP : 0),
							"SalesSdmOTC" => (!empty($slspriSdmOTC ->valSalesP)? $slspriSdmOTC ->valSalesP : 0),
							"SalesSdmOth" => (!empty($slspriSdmOth ->valSalesP)? $slspriSdmOth ->valSalesP : 0),
							"SalesSdmPck" => (!empty($slspriSdmPck ->valSalesP)? $slspriSdmPck ->valSalesP : 0),
							"SalesSeles" => (!empty($slspriSeles ->valSalesP)? $slspriSeles ->valSalesP : 0),
							"SalesSolasNR" => (!empty($slspriSolasNR ->valSalesP)? $slspriSolasNR ->valSalesP : 0),
							"SalesSolasReg" => (!empty($slspriSolasReg ->valSalesP)? $slspriSolasReg ->valSalesP : 0),
							"SalesSpartaX" => (!empty($slspriSpartaX ->valSalesP)? $slspriSpartaX ->valSalesP : 0),
							"SalesSunthi" => (!empty($slspriSunthi ->valSalesP)? $slspriSunthi ->valSalesP : 0),
							"SalesSutraFiesta" => (!empty($slspriSutraFiesta ->valSalesP)? $slspriSutraFiesta ->valSalesP : 0),
							"SalesTpNREG" => (!empty($slspriTpNREG ->valSalesP)? $slspriTpNREG ->valSalesP : 0),
							"SalesTpOBH" => (!empty($slspriTpOBH ->valSalesP)? $slspriTpOBH ->valSalesP : 0),
							"SalesTpReg" => (!empty($slspriTpReg ->valSalesP)? $slspriTpReg ->valSalesP : 0),
							"SalesTrifa" => (!empty($slspriTrifa ->valSalesP)? $slspriTrifa ->valSalesP : 0),
							"SalesZenith" => (!empty($slspriZenith ->valSalesP)? $slspriZenith ->valSalesP : 0),
							"dSalesAdi" => (!empty($slspriAdi ->valSalesDisk)? $slspriAdi ->valSalesDisk : 0),
							"dSalesAlta" => (!empty($slspriAlta->valSalesDisk)? $slspriAlta->valSalesDisk : 0),
							"dSalesAlkes" => (!empty($slspriAlkes->valSalesDisk)? $slspriAlkes->valSalesDisk : 0),
							"dSalesAndalan" => (!empty($slspriAndalan ->valSalesDisk)? $slspriAndalan ->valSalesDisk : 0),
							"dSalesArmoxindo" => (!empty($slspriArmoxindo ->valSalesDisk)? $slspriArmoxindo ->valSalesDisk : 0),
							"dSalesAxion" => (!empty($slspriAxion ->valSalesDisk)? $slspriAxion ->valSalesDisk : 0),
							"dSalesBintangKK" => (!empty($slspriBintangKK ->valSalesDisk)? $slspriBintangKK ->valSalesDisk : 0),
							"dSalesCalumika" => (!empty($slspriCalumika ->valSalesDisk)? $slspriCalumika ->valSalesDisk : 0),
							"dSalesCendo" => (!empty($slspriCendo ->valSalesDisk)? $slspriCendo ->valSalesDisk : 0),
							"dSalesCoronet" => (!empty($slspriCoronet ->valSalesDisk)? $slspriCoronet ->valSalesDisk : 0),
							"dSalesCorsa" => (!empty($slspriCorsa ->valSalesDisk)? $slspriCorsa ->valSalesDisk : 0),
							"dSalesDipa" => (!empty($slspriDipa ->valSalesDisk)? $slspriDipa ->valSalesDisk : 0),
							"dSalesDipaOTC" => (!empty($slspriDipaOTC ->valSalesDisk)? $slspriDipaOTC ->valSalesDisk : 0),
							"dSalesEKAT" => (!empty($slspriEKAT ->valSalesDisk)? $slspriEKAT ->valSalesDisk : 0),
							"dSalesErella" => (!empty($slspriErella ->valSalesDisk)? $slspriErella ->valSalesDisk : 0),
							"dSalesErlimpexNReg" => (!empty($slspriErlimpexNReg ->valSalesDisk)? $slspriErlimpexNReg ->valSalesDisk : 0),
							"dSalesErlimpexReg" => (!empty($slspriErlimpexReg ->valSalesDisk)? $slspriErlimpexReg ->valSalesDisk : 0),
							"dSalesEscolab" => (!empty($slspriEscolab ->valSalesDisk)? $slspriEscolab ->valSalesDisk : 0),
							"dSalesFhOTC" => (!empty($slspriFhOTC ->valSalesDisk)? $slspriFhOTC ->valSalesDisk : 0),
							"dSalesFirstmed" => (!empty($slspriFirstmed ->valSalesDisk)? $slspriFirstmed ->valSalesDisk : 0),
							"dSalesGmp" => (!empty($slspriGmp ->valSalesDisk)? $slspriGmp ->valSalesDisk : 0),
							"dSalesHermed" => (!empty($slspriHermed ->valSalesDisk)? $slspriHermed ->valSalesDisk : 0),
							"dSalesHoli" => (!empty($slspriHoli ->valSalesDisk)? $slspriHoli ->valSalesDisk : 0),
							"dSalesHufa" => (!empty($slspriHufa ->valSalesDisk)? $slspriHufa ->valSalesDisk : 0),
							"dSalesItra" => (!empty($slspriItra ->valSalesDisk)? $slspriItra ->valSalesDisk : 0),
							"dSalesKarindo" => (!empty($slspriKarindo ->valSalesDisk)? $slspriKarindo ->valSalesDisk : 0),
							"dSalesLainlain" => (!empty($slspriLainlain ->valSalesDisk)? $slspriLainlain ->valSalesDisk : 0),
							"dSalesLas" => (!empty($slspriLas ->valSalesDisk)? $slspriLas ->valSalesDisk : 0),
							"dSalesMecNR" => (!empty($slspriMecNR ->valSalesDisk)? $slspriMecNR ->valSalesDisk : 0),
							"dSalesMecOTC" => (!empty($slspriMecOTC ->valSalesDisk)? $slspriMecOTC ->valSalesDisk : 0),
							"dSalesMecReg" => (!empty($slspriMecReg ->valSalesDisk)? $slspriMecReg ->valSalesDisk : 0),
							"dSalesMersi" => (!empty($slspriMersi ->valSalesDisk)? $slspriMersi ->valSalesDisk : 0),
							"dSalesNutrindo" => (!empty($slspriNutrindo ->valSalesDisk)? $slspriNutrindo ->valSalesDisk : 0),
							"dSalesNova" => (!empty($slspriNova ->valSalesDisk)? $slspriNova ->valSalesDisk : 0),
							"dSalesPyridam" => (!empty($slspriPyridam ->valSalesDisk)? $slspriPyridam ->valSalesDisk : 0),
							"dSalessdm_" => (!empty($slsprisdm_ ->valSalesDisk)? $slsprisdm_ ->valSalesDisk : 0),
							"dSalesSdmEth" => (!empty($slspriSdmEth ->valSalesDisk)? $slspriSdmEth ->valSalesDisk : 0),
							"dSalesSdmOTC" => (!empty($slspriSdmOTC ->valSalesDisk)? $slspriSdmOTC ->valSalesDisk : 0),
							"dSalesSdmOth" => (!empty($slspriSdmOth ->valSalesDisk)? $slspriSdmOth ->valSalesDisk : 0),
							"dSalesSdmPck" => (!empty($slspriSdmPck ->valSalesDisk)? $slspriSdmPck ->valSalesDisk : 0),
							"dSalesSeles" => (!empty($slspriSeles ->valSalesDisk)? $slspriSeles ->valSalesDisk : 0),
							"dSalesSolasNR" => (!empty($slspriSolasNR ->valSalesDisk)? $slspriSolasNR ->valSalesDisk : 0),
							"dSalesSolasReg" => (!empty($slspriSolasReg ->valSalesDisk)? $slspriSolasReg ->valSalesDisk : 0),
							"dSalesSpartaX" => (!empty($slspriSpartaX ->valSalesDisk)? $slspriSpartaX ->valSalesDisk : 0),
							"dSalesSunthi" => (!empty($slspriSunthi ->valSalesDisk)? $slspriSunthi ->valSalesDisk : 0),
							"dSalesSutraFiesta" => (!empty($slspriSutraFiesta ->valSalesDisk)? $slspriSutraFiesta ->valSalesDisk : 0),
							"dSalesTpNREG" => (!empty($slspriTpNREG ->valSalesDisk)? $slspriTpNREG ->valSalesDisk : 0),
							"dSalesTpOBH" => (!empty($slspriTpOBH ->valSalesDisk)? $slspriTpOBH ->valSalesDisk : 0),
							"dSalesTpReg" => (!empty($slspriTpReg ->valSalesDisk)? $slspriTpReg ->valSalesDisk : 0),
							"dSalesTrifa" => (!empty($slspriTrifa ->valSalesDisk)? $slspriTrifa ->valSalesDisk : 0),
							"dSalesZenith" => (!empty($slspriZenith ->valSalesDisk)? $slspriZenith ->valSalesDisk : 0),
							"komPriSalestot"=> $totPriSaleskom,
							"komSalesAdi" => $komAdi,
							"komSalesAlta"=> $komAlta,
							"komSalesAlkes"=> $komAlkes,
							"komSalesAndalan"=> $komAndalan,
							"komSalesArmoxindo"=> $komArmoxindo,
							"komSalesAxion"=> $komAxion,
							"komSalesBintangKK"=> $komBintangKK,
							"komSalesCalumika"=> $komCalumika,
							"komSalesCendo"=> $komCendo,
							"komSalesCoronet"=> $komCoronet,
							"komSalesCorsa"=> $komCorsa,
							"komSalesDipa"=> $komDipa,
							"komSalesDipaOTC"=> $komDipaOTC,
							"komSalesEKAT"=> $komEKAT,
							"komSalesErella"=> $komErella,
							"komSalesErlimpexNReg"=> $komErlimpexNReg,
							"komSalesErlimpexReg"=> $komErlimpexReg,
							"komSalesEscolab"=> $komEscolab,
							"komSalesFhOTC"=> $komFhOTC,
							"komSalesFirstmed"=> $komFirstmed,
							"komSalesGmp"=> $komGmp,
							"komSalesHermed"=> $komHermed,
							"komSalesHoli"=> $komHoli,
							"komSalesHufa"=> $komHufa,
							"komSalesItra"=> $komItra,
							"komSalesKarindo"=> $komKarindo,
							"komSalesLainlain"=> $komLainlain,
							"komSalesLas"=> $komLas,
							"komSalesMecNR"=> $komMecNR,
							"komSalesMecOTC"=> $komMecOTC,
							"komSalesMecReg"=> $komMecReg,
							"komSalesMersi"=> $komMersi,
							"komSalesNutrindo"=> $komNutrindo,
							"komSalesNova"=> $komNova,
							"komSalesPyridam"=> $komPyridam,
							"komSalessdm_"=> $komsdm_,
							"komSalesSdmEth"=> $komSdmEth,
							"komSalesSdmOTC"=> $komSdmOTC,
							"komSalesSdmOth"=> $komSdmOth,
							"komSalesSdmPck"=> $komSdmPck,
							"komSalesSeles"=> $komSeles,
							"komSalesSolasNR"=> $komSolasNR,
							"komSalesSolasReg"=> $komSolasReg,
							"komSalesSpartaX"=> $komSpartaX,
							"komSalesSunthi"=> $komSunthi,
							"komSalesSutraFiesta"=> $komSutraFiesta,
							"komSalesTpNREG"=> $komTpNREG,
							"komSalesTpOBH"=> $komTpOBH,
							"komSalesTpReg"=> $komTpReg,
							"komSalesTrifa"=> $komTrifa,
							"komSalesZenith"=> $komZenith,
							"perkomAdi" => $cekAdi,
							"perkomAlta"=> $cekAlta,
							"perkomAlkes"=> $cekAlkes,
							"perkomAndalan"=> $cekAndalan,
							"perkomArmoxindo"=> $cekArmoxindo,
							"perkomAxion"=> $cekAxion,
							"perkomBintangKK"=> $cekBintangKK,
							"perkomCalumika"=> $cekCalumika,
							"perkomCendo"=> $cekCendo,
							"perkomCoronet"=> $cekCoronet,
							"perkomCorsa"=> $cekCorsa,
							"perkomDipa"=> $cekDipa,
							"perkomDipaOTC"=> $cekDipaOTC,
							"perkomEKAT"=> $cekEKAT,
							"perkomErella"=> $cekErella,
							"perkomErlimpexNReg"=> $cekErlimpexNReg,
							"perkomErlimpexReg"=> $cekErlimpexReg,
							"perkomEscolab"=> $cekEscolab,
							"perkomFhOTC"=> $cekFhOTC,
							"perkomFirstmed"=> $cekFirstmed,
							"perkomGmp"=> $cekGmp,
							"perkomHermed"=> $cekHermed,
							"perkomHoli"=> $cekHoli,
							"perkomHufa"=> $cekHufa,
							"perkomItra"=> $cekItra,
							"perkomKarindo"=> $cekKarindo,
							"perkomLainlain"=> $cekLainlain,
							"perkomLas"=> $cekLas,
							"perkomMecNR"=> $cekMecNR,
							"perkomMecOTC"=> $cekMecOTC,
							"perkomMecReg"=> $cekMecReg,
							"perkomMersi"=> $cekMersi,
							"perkomNutrindo"=> $cekNutrindo,
							"perkomNova"=> $cekNova,
							"perkomPyridam"=> $cekPyridam,
							"perkomsdm_"=> $ceksdm_,
							"perkomSdmEth"=> $cekSdmEth,
							"perkomSdmOTC"=> $cekSdmOTC,
							"perkomSdmOth"=> $cekSdmOth,
							"perkomSdmPck"=> $cekSdmPck,
							"perkomSeles"=> $cekSeles,
							"perkomSolasNR"=> $cekSolasNR,
							"perkomSolasReg"=> $cekSolasReg,
							"perkomSpartaX"=> $cekSpartaX,
							"perkomSunthi"=> $cekSunthi,
							"perkomSutraFiesta"=> $cekSutraFiesta,
							"perkomTpNREG"=> $cekTpNREG,
							"perkomTpOBH"=> $cekTpOBH,
							"perkomTpReg"=> $cekTpReg,
							"perkomTrifa"=> $cekTrifa,
							"perkomZenith"=> $cekZenith,
							"ecpersen" => $ecpersen,
							"ectot" => $ec,
							"eccall" => $call,
							"ectarget" => $ectarget,
							"echari" => $hari,
							"ecinsentif" => (!empty($komEC->Insentif)? $komEC->Insentif : 0),
							"noo" => $komnoo,
							"iptDet" => $iptDet,
							"iptHed" => $iptHed,
							"iptVal" => $iptPci,
							"iptKom" => $komIPT,
							"rotMCL" => (!empty($komROT->MCL)? $komROT ->MCL : 0),
							"rotJPel" => (!empty($komROT->JumPel)? $komROT ->JumPel : 0),
							"rotTrgt" => (!empty($komROT->TargetOT)? $komROT ->TargetOT : 0),
							"rotPci" => (!empty($komROT->pciOT)? $komROT ->pciOT : 0) * 100,
							"rotKom" => $komRotVal,
							"tagihanA" => (!empty($komTagihan->vp30s)? $komTagihan ->vp30s : 0),
							"tagihanB" => (!empty($komTagihan->vp45s)? $komTagihan ->vp45s : 0),
							"tagihanC" => (!empty($komTagihan->vp60s)? $komTagihan ->vp60s : 0),
							"tagihanD" => (!empty($komPen->penalti)? $komPen ->penalti : 0),
							"tagihanA1" => (!empty($komTagihan->vp30t)? $komTagihan ->vp30t : 0),
							"tagihanB1" => (!empty($komTagihan->vp45t)? $komTagihan ->vp45t : 0),
							"tagihanC1" => (!empty($komTagihan->vp60t)? $komTagihan ->vp60t : 0),
							"tagihanD1" => (!empty($komTagihan->vpb60t)? $komTagihan ->vpb60t : 0),
					);
			//output to json format
			echo json_encode($output);
		}
		else 
		{
			redirect("auth/logout");
		}
	}
}