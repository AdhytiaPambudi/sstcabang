<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");
class Model_orderSalesman extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }

    // Pelanggan
    public function Pelanggan()
    {   
        $query = $this->db->query("select * from mst_pelanggan where Cabang = '".$this->cabang."' AND ifnull(Aktif,'') in ('Y','') AND (Nama_Faktur != '' AND Nama_Faktur IS NOT NULL AND Nama_Faktur != '-') ORDER BY Nama_Faktur, Kode")->result();

        return $query;
    }

    public function NoOrder()
    {   
        $query = $this->db->query("SELECT NoOrder,KodePelanggan, CONCAT(c.Kode , '-',c.Nama_Faktur,'-',c.`Alamat`,'-',c.`Cara_Bayar`,'-',c.`flag_ppn`,'-',c.`Tipe_2`) AS NamaPelanggan,KodeSalesman,
                        CONCAT(KodeSalesman,'-',NamaSalesman,'-',Jabatan) AS NamaSalesman
                         FROM trs_sales_order_salesman a left join mst_karyawan b ON b.Kode = a.KodeSalesman 
             left join mst_pelanggan c ON c.Kode = a.`KodePelanggan`
             WHERE c.`Cabang` = '".$this->cabang."' AND (a.`Status` IS NULL OR a.`Status` not in ('Closed','Reject'))
                         GROUP BY NoOrder")->result();
        // $query = $this->db->query("select No_PO, No_PR, Cabang from trs_po_header where flag_cabang = 'Y'")->result();
        return $query;
    }

    public function getOrderSalesman($no = NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {
            // $byNo = "where NoOrder = '".$no."'";            
            $query = $this->db->query("SELECT a.*,b.HNA,b.`Cabang`,c.`UnitStok`,CONCAT(a.KodeProduk,'~',a.NamaProduk) AS Produk1 FROM trs_sales_order_salesman a left join mst_harga b ON a.`KodeProduk` = b.`Produk` 
                left join trs_invsum c ON a.`KodeProduk` = c.`KodeProduk` AND a.`Cabang` = c.`Cabang`
                WHERE NoOrder = '$no' AND c.`Gudang` = 'Baik' AND c.`Tahun` = YEAR(CURDATE()) AND 
                CASE 
                  WHEN b.`Cabang` IS NULL THEN
                   b.`Cabang` IS NULL
                  ELSE
                  b.`Cabang` = '".$this->cabang."'
                END ")->result();
            // $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();
        }

        return $query;
    }


    // Data Salesman
    public function dataSalesman($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_karyawan where Kode = '".$kode."' and Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' limit 1")->row();

        return $query;
    }

    public function listapproval($pelanggan = null)
    {   
        // tambahan kriteria, hanyaFaktur dan Retur Saja untuk perhitungan TOP Limit
        // ACC Pa Kuntoro
        // UJ 07Agustus2019
        $query = $this->db->query("SELECT Pelanggan,
                                              (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                                             DATEDIFF(CURDATE(),trs_faktur.TglFaktur) AS 'Umur_faktur'
                                        FROM trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.KodePelanggan,
                                            SUM(trs_pelunasan_giro_detail.ValuePelunasan) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         WHERE STATUS = 'Open' 
                                         GROUP BY trs_pelunasan_giro_detail.KodePelanggan) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.Pelanggan
                                        WHERE ((LEFT(trs_faktur.Status,5) NOT IN ('Lunas','Batal') AND IFNULL(trs_faktur.Status,'') NOT LIKE '%je%') OR trs_faktur.Saldo!= 0)
                                        AND Pelanggan = '$pelanggan' 
                                            AND (trs_faktur.TipeDokumen IN ('Faktur','Retur') OR trs_faktur.NoFaktur LIKE 'INV%' OR trs_faktur.NoFaktur LIKE 'RTF%')
                                         GROUP BY Pelanggan;")->row();

        return $query;
    }

    // Data Pelanggan
    public function dataPelangganNew($kode = NULL)
    {   
        $query = $this->db->query("SELECT * FROM mst_pelanggan  WHERE Kode = '".$kode."' AND Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

    public function saveDataSalesOrder_salesman($params = null)
    {
       
        $dataPelanggan = $this->dataPelangganNew($params->pelanggan);
        $dataSalesman = $this->dataSalesman($params->salesman);   
        $dataTOPLimit = $this->listapproval($params->pelanggan);
        $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

        $status = "Usulan";
        $statusTOP = "Ok";
        $statusLimit = "Ok";
        $statusDiscCab = "Ok";
        $statusDiscPrins = "Ok";
        $do = true;
        $totalPiutang = $params->total + $params->piutang;
        $xPelanggan = explode("-", $params->pelanggan);
        $Pelanggan = $xPelanggan[0];
        $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
        $datax = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status in ('Open','OpenDIH') and Saldo != 0 order by TglFaktur desc")->result();

        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('SO');
        $year = date('Y');
        $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13 and YEAR(tglso) ='".$year."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//

        
        
        if ($dataTOPLimit->Umur_faktur > $params->top) {
            $status = "Usulan";
            $statusTOP = "TOP";
            $do = false;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("Dokumen", "Sales Order");
            $this->db->set("NoDokumen", $noDokumen);
            $this->db->set("Status", "TOP");
            $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
            $valid =  $this->db->insert("trs_approval");
            // break;
        }

        if ($params->limit < $totalPiutang) {
            $status = "Usulan";
            $statusLimit = "Limit";
            $do = false;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("Dokumen", "Sales Order");
            $this->db->set("NoDokumen", $noDokumen);
            $this->db->set("Status", "Limit");
            $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
            $valid =  $this->db->insert("trs_approval");
        } 

        foreach ($params->produk as $key => $value)
        {
            $dscprins = $params->dscprins1[$key] + $params->dscprins2[$key];
            if ($params->maksdsccab[$key] < $params->dsccab[$key]) {
                $status = "Usulan";
                $statusDiscCab = "DC";
                $do = false;
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "DC");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
                break;
            }

            if($params->Prinsipal[$key] == 'NUTRINDO' || substr($params->Prinsipal[$key],0,5) == 'CORSA' || $params->Prinsipal[$key] == 'MERSI' || $params->Prinsipal[$key] == 'ALTA WRAP'){
                    // $status = "Usulan";
                    // $statusDiscPrins = "Ok";
                    // $do = false;
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Dokumen", "Sales Order");
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("Status", "Ok");
                    $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_approval");
            }else{
                if ($params->maksdscprins[$key] < $dscprins) {
                    $status = "Usulan";
                    $statusDiscPrins = "DP";
                    $do = false;
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Dokumen", "Sales Order");
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("Status", "DP");
                    $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_approval");
                    break;
                }
            }
        } 

        $carabayar = $params->carabayar;
        if($carabayar == "Cash"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+7 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "Kredit"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+21 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO60"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+60 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO75"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+75 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO90"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+90 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO120"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+120 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO150"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+150 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO180"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+180 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }
        $xbid = explode("-", $params->acu2);
        $bid = $xbid[0];
        //save header  
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("NoSO", $noDokumen);
        $this->db->set("TglSO", $params->tgl);
        $this->db->set("TimeSO", date('Y-m-d H:i:s'));
        $this->db->set("Pelanggan", $params->pelanggan);
        $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
        $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
        $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
        $this->db->set("NamaTipePelanggan", "");
        $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
        $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
        $this->db->set("Acu", $params->acu);
        $this->db->set("acu2", $bid);
        $this->db->set("CaraBayar", $params->carabayar);
        $this->db->set("ppn_pelanggan", $params->flag_ppn);
        $this->db->set("CashDiskon", $params->cashdiskon);
        $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
        $this->db->set("TOP", $dataPelanggan->TOP);
        $this->db->set("TglJtoOrder", $tgljto);
        $this->db->set("Salesman", $dataSalesman->Kode);
        $this->db->set("NamaSalesman", $dataSalesman->Nama);
        $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
        $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
        $this->db->set("Status", $status);
        $this->db->set("StatusTOP", $statusTOP);
        $this->db->set("StatusLimit", $statusLimit);
        $this->db->set("StatusDiscCab", $statusDiscCab);
        $this->db->set("StatusDiscPrins", $statusDiscPrins);
        $this->db->set("TipeDokumen", "SO");
        $this->db->set("Gross", round($params->grosir,0));
        $this->db->set("Potongan", round($params->potongan,0));
        $this->db->set("Value", round($params->value,0));
        $this->db->set("Ppn", round($params->ppn,0));
        $this->db->set("LainLain", "");
        $this->db->set("Materai", $params->materai);
        $this->db->set("OngkosKirim", $params->ongkir);
        $this->db->set("Total", round($params->total,0));
        $this->db->set("Keterangan1", "");
        $this->db->set("Keterangan2", "");
        $this->db->set("Barcode", "");
        $this->db->set("QrCode", "");
        $this->db->set("NoDo", "");
        $this->db->set("NoFaktur", "");
        $this->db->set("NoSP", $params->nosp);
        $this->db->set("TipeFaktur", $params->tipefaktur);
        $this->db->set("NoIDPaket", $params->idpaket);
        $this->db->set("KeteranganTender", $params->kettender);
        $this->db->set("statusPusat", "Gagal");
        $this->db->set("created_at", date('Y-m-d H:i:s'));
        $this->db->set("created_by", $this->session->userdata('username'));
        $valid =  $this->db->insert("trs_sales_order"); 
        $i=0;
        $xx = $this->db->query("select Pelanggan from trs_sales_order where NoSO = '".$noDokumen."' limit 1")->row();
        $headerPelanggan = $xx->Pelanggan;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                if($headerPelanggan == $params->pelanggan){
                  $i++;
                  $expld = explode("~", $params->produk[$key]);
                  $KodeProduk = $expld[0];
                  $NamaProduk = $expld[1];

                  $jml = $params->jumlah[$key];
                  $harga = $params->harga[$key];
                  $dsccab = $params->dsccab[$key];
                  $dscprins1 = $params->dscprins1[$key];
                  $dscprins2 = $params->dscprins2[$key];
                  $bnscab = $params->bnscab[$key];
                  $bnsprins = $params->bnsprins[$key];
                  $statusppn = $params->flag_ppn;

                  // tambahan
                  $unCogs = $params->cogsval[$key];

                  $gross = round(($jml  + ($bnscab + $bnsprins)) * $harga,0);
                  $stotCogs = round(($jml  + ($bnscab + $bnsprins)) * $unCogs,0);
                  $diskoncab = ($dsccab)/100;

                  $dsccab = ($harga * ($jml)) * (($diskoncab)); 
                  $boncab = ($bnscab * $harga);

                  $diskonprins1 = ($dscprins1)/100;
                  $diskonprins2 = ($dscprins2)/100;
                  $diskonprins = $diskonprins1 + $diskonprins2;
                  $dscprins1 = ($harga * ($jml)) * (($diskonprins1));
                  $dscprins2 = ($harga * ($jml)) * (($diskonprins2));
                  $dscprins = ($dscprins1) + ($dscprins2);
                  $bonprins = (($bnsprins) * ($harga));

                  $dscTot = $dsccab + $dscprins;
                  $bnsTot = $boncab + $bonprins;
                  
                  $potongan = round($dscTot + $bnsTot,0);     
                  $value = round($gross - $potongan,0);
                  if($statusppn == 'Y'){
                      $ppn = round($value * 0.1,0);
                  }else{
                      $ppn = 0;
                  }
                  
                  $TotValue = round($value + $ppn,0); 

                  $totgross = $totgross + $gross;
                  $totpotongan = $totpotongan + $potongan;
                  $totvalue = $totvalue + $value;
                  $totppn = $totppn + $ppn;
                  $summary = $summary + $totppn;
                  $totCogs = $totCogs + $stotCogs;

                  // update salesman (Rian)
                  $this->db->set("Status", "Closed");
                  $this->db->set("NoSO", $noDokumen);
                  $this->db->where('NoOrder', $params->no_Order);
                  $this->db->update('trs_sales_order_salesman');

                  $this->db->set("Cabang", $this->cabang);
                  $this->db->set("NoSO", $noDokumen);
                  $this->db->set("noline", $i);
                  $this->db->set("TglSO", $params->tgl);
                  $this->db->set("TimeSO", date('Y-m-d H:i:s'));
                  $this->db->set("Pelanggan", $params->pelanggan);
                  $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                  $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
                  $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                  $this->db->set("NamaTipePelanggan", "");
                  $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                  $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                  $this->db->set("Acu", $params->acu);
                  $this->db->set("acu2", $bid);
                  $this->db->set("CaraBayar", $params->carabayar);
                  $this->db->set("CashDiskon", $params->cashdiskon);
                  $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
                  $this->db->set("TOP", $dataPelanggan->TOP);
                  $this->db->set("TglJtoOrder", $tgljto );
                  $this->db->set("Salesman", $dataSalesman->Kode);
                  $this->db->set("NamaSalesman", $dataSalesman->Nama);
                  $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                  $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                  $this->db->set("Status", $status);
                  $this->db->set("TipeDokumen", "SO");
                  $this->db->set("Gross", $gross);
                  $this->db->set("Potongan", $potongan);
                  $this->db->set("Value", $value);
                  $this->db->set("Ppn", $ppn);
                  $this->db->set("LainLain", "");
                  $this->db->set("Total", $TotValue);
                  $this->db->set("Keterangan1", "");
                  $this->db->set("Keterangan2", "");
                  $this->db->set("Barcode", "");
                  $this->db->set("QrCode", "");
                  $this->db->set("NoDO", "");
                  $this->db->set("NoFaktur", "");
                  // DETAIL PRODUK
                  $this->db->set("KodeProduk", $KodeProduk);
                  $this->db->set("NamaProduk", $NamaProduk);
                  $this->db->set("UOM", $params->uom[$key]);
                  $this->db->set("Harga", $params->harga[$key]);
                  $this->db->set("QtySO", $params->jumlah[$key]);
                  $this->db->set("Bonus", $params->bnscab[$key] + $params->bnsprins[$key]);
                  $this->db->set("ValueBonus", $params->valuebnscab[$key] + $params->valuebnsprins[$key]);
                  $this->db->set("DiscCab", $params->dsccab[$key]);
                  $this->db->set("ValueDiscCab", $params->valuedsccab[$key]);
                  // $this->db->set("DiscCab2", $params->dsccab2[$key]);
                  // $this->db->set("ValueDiscCab2", $params->valuedsccab2[$key]);
                  $this->db->set("DiscCabTot", "");
                  $this->db->set("ValueDiscCabTotal", "");
                  $this->db->set("DiscPrins1", $params->dscprins1[$key]);
                  $this->db->set("ValueDiscPrins1", $params->valuedscprins1[$key]);
                  $this->db->set("DiscPrins2", $params->dscprins2[$key]);
                  $this->db->set("ValueDiscPrins2", $params->valuedscprins2[$key]);
                  $this->db->set("DiscPrinsTot", "");
                  $this->db->set("ValueDiscPrinsTotal", "");
                  $this->db->set("DiscTotal", "");
                  $this->db->set("ValueDiscTotal", "");
                  $this->db->set("DiscCabMax", $params->maksdsccab[$key]);
                  // $this->db->set("KetDiscCabMax", $params->KetDiscCabMax[$key]);
                  $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
                  // $this->db->set("KetDiscPrinsMax", $params->KetDiscPrinsMax[$key]);
                  $this->db->set("COGS", $params->cogsval[$key]);
                  $this->db->set("TotalCOGS", $stotCogs);
                  $this->db->set("created_at", date('Y-m-d H:i:s'));
                  $this->db->set("created_by", $this->session->userdata('username'));
                  $this->db->set("NoUsulanDisc", $params->NoUsulanDisc[$key]);
                  $this->db->set("TipeUsulanDisc", $params->disprins_trans[$key]);
                  $valid =  $this->db->insert("trs_sales_order_detail");
                  if($valid){
                       if(!empty($params->NoUsulanDisc[$key])){
                            $this->db->query("update mst_usulan_disc_prinsipal  
                                          SET status ='Terpakai',
                                              NoSO = '".$noDokumen."'
                                          WHERE No_Usulan ='".$params->NoUsulanDisc[$key]."' and 
                                        KodeProduk = '".$KodeProduk."'");
                       }
                      $this->db->query("update trs_sales_order_detail a, mst_produk b 
                                  SET a.Prinsipal=b.Prinsipal,
                                    a.Prinsipal2=b.Prinsipal2,
                                    a.Supplier=b.Supplier1,
                                    a.Supplier2=b.Supplier2,
                                    a.Pabrik =b.Pabrik,
                                    a.Farmalkes=b.Farmalkes
                                  WHERE a.KodeProduk=b.Kode_Produk and 
                                        a.KodeProduk = '".$KodeProduk."' and a.NoSO = '".$noDokumen."'");
                  }

                }
                
            }
        }
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'SO' and Cabang = '".$this->cabang."'");
            $this->db->query("update mst_usulan_edsipa set Status_Usulan = 'Terpakai',NoSO ='".$noDokumen."' where Status_Usulan = 'Disetujui' and Kode_Pelanggan = '".$params->pelanggan."' and Cabang = '".$this->cabang."'");  
        }
        $callback['no'] = $noDokumen;
        $callback['do'] = $do;

        return $callback;
    }

    public function listDataDetail_Salesman($no)
    {   
          $query = $this->db->query(        
                "SELECT * FROM trs_sales_order_salesman WHERE  NoOrder ='$no'
                        ");
       
        return $query;
    }

    public function listDataSO_Salesman()
    {   
          $query = $this->db->query(        
                "SELECT * FROM trs_sales_order_salesman WHERE  (`Status` NOT IN ('Closed','Reject') OR `Status` IS NULL)
                        GROUP BY NoOrder");
       
        return $query;
    }

    public function rejectDataSO_Salesman($NoSO = NULL)
    {
        $this->db->set("Status", "Reject");
        $this->db->set("ModifTime", date('Y-m-d H:i:s'));
        // $this->db->set("modified_by", $this->session->userdata('username'));
        $this->db->where('NoOrder', $NoSO);
        $valid = $this->db->update('trs_sales_order_salesman');


        return $valid;
    }

    public function prosesDataSO_Salesman($no = NULL,$do=null)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        
        $query = $this->db->query("select * from trs_sales_order_detail where NoSO = '".$no."'")->result(); 
        $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row(); 
        $status = $query2->Status;    
        if ($this->db2->conn_id == TRUE) {
            $cek = $this->db2->query("select * from trs_sales_order_detail where NoSO = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_sales_order_detail', $r); // insert each row to another table
                }
            }
            else{
                
                foreach($query as $r) { // loop over results
                    $this->db2->where('KodeProduk', $r->KodeProduk);
                    $this->db2->where('NoSO', $no);
                    $this->db2->update('trs_sales_order_detail', $r);
                }
            }
            $cek2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_sales_order', $query2); // insert each row to another table
            }
            else{
                
                $this->db2->where('NoSO', $no);
                $this->db2->update('trs_sales_order', $query2);
            }
            $update = $this->db->query("update trs_sales_order set statusPusat = 'Berhasil' where NoSO = '".$no."'");
            return TRUE;
        }
        else{
            $this->db->query("update trs_sales_order set statusPusat = 'Gagal' where NoSO = '".$no."'");
            return FALSE;
        }
    }

}