<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");
class Model_order extends CI_Model {

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

    // Salesman
    public function Salesman()
    {   
        $query = $this->db->query("select Kode, Nama, Jabatan from mst_karyawan where Cabang = '".$this->cabang."'  and Status = 'Aktif' and jabatan='Salesman' group by Kode order by Nama asc")->result();

        return $query;
    }

    // Kode Lama Cabang
    public function kodeLamaCabang()
    {   
        $query = $this->db->query("select Kode_Lama from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
        $query = $query->Kode_Lama;
        return $query;
    }

    // Kode Dokumen
    public function kodeDokumen($dok = NULL)
    {   
        $query = $this->db->query("select Kode from mst_kode_dokumen where Dokumen = '".$dok."' limit 1")->row();
        $query = (!empty($query->Kode))?$query->Kode:'';
        return $query;
    }

    // Counter
    public function counter($apl = NULL)
    {   
        $query = $this->db->query("select Counter from mst_counter where Aplikasi = '".$apl."' and Cabang = '".$this->cabang."' limit 1")->row();
        return $query;
    }

    //Cara Bayar
    public function caraBayar()
    {   
        $query = $this->db->query("select * from mst_cara_bayar")->result();
        return $query;
    }

    // Produk
    public function Produk()
    {   
        // INVSUM
        $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk from trs_invsum where Cabang = '".$this->cabang."' and Gudang = 'Baik' order by NamaProduk asc")->result();
        // MST PRODUK
        // $query = $this->db->query("select Kode_Produk, Produk from mst_produk order by Produk asc")->result();

        return $query;
    }

    // Produk
    public function listProdukorder($tipe=NULL)
    {   
        $tipe = $this->db->query("select distinct Dot from mst_tipepelanggan where Tipe='".$tipe."' LIMIT 1")->result();
        $tipe = $tipe[0]->Dot;
        // INVSUM
        if($tipe == "Merah"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2','3','4')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Biru"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2','3')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Hijau"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Jamu"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Non Dot"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0')
                 order by NamaProduk asc")->result();
        }
        // $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk from trs_invsum where Cabang = '".$this->cabang."' and Gudang = 'Baik' order by NamaProduk asc")->result();
        // MST PRODUK
        // $query = $this->db->query("select Kode_Produk, Produk from mst_produk order by Produk asc")->result();
        // log_message("error",print_r($query,true));
        return $query;
    }

    // Data Pelanggan
    public function dataPelangganNew($kode = NULL)
    {   
        $query = $this->db->query("SELECT * FROM mst_pelanggan  WHERE Kode = '".$kode."' AND Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }
    
    public function dataPelanggan($kode = NULL)
    {   
        //$query = $this->db->query("select * from mst_pelanggan a , (select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan) b where a.Kode = '".$kode."' and a.Cabang = '".$this->cabang."' limit 1")->row();
        $query = $this->db->query("SELECT * FROM mst_pelanggan a LEFT JOIN (SELECT SUM(saldo) AS saldopiutang,Pelanggan,Cabang FROM trs_faktur WHERE Pelanggan = '".$kode."' AND Cabang = '".$this->cabang."' GROUP BY cabang,pelanggan) b ON a.Kode=b.Pelanggan AND a.`Cabang`=b.Cabang LEFT JOIN (SELECT SUM(total) AS saldoorder,Pelanggan,Cabang FROM trs_sales_order WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS ='Usulan' GROUP BY cabang,pelanggan) c ON a.Kode=c.Pelanggan AND a.`Cabang`=c.Cabang LEFT JOIN (SELECT SUM(total) AS saldoDlvorder,Pelanggan,Cabang FROM trs_delivery_order_sales WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS IN ('Open','Kirim') GROUP BY cabang,pelanggan) d ON a.Kode=d.Pelanggan AND a.`Cabang`=d.Cabang WHERE a.Kode = '".$kode."' AND a.Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

    // Data Piutang
    public function dataPelanggangPiutang($kode = NULL)
    {   
        $query = $this->db->query("select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan limit 1")->row();

        return $query;
    }

    // Data Salesman
    public function dataSalesman($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_karyawan where Kode = '".$kode."' and Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' limit 1")->row();

        return $query;
    }

    public function AllBatch($kode = NULL)
    {   
        $query = $this->db->query("select sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and Gudang = 'Baik' and Tahun ='".date('Y')."' limit 1")->result();

        return $query;
    }

    // Batch No
    public function Batch($kode = NULL)
    {   
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate, NoDokumen as NoBPB,UnitCOGS as 'COGS' from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and UnitStok > 0 and Gudang = 'Baik' and Tahun ='".date('Y')."' order by NoDokumen,ExpDate asc")->result();

        return $query;
    }

    // Harga Produk
    public function getProdukBuatOrder($kode = NULL)
    {   
        // $query = $this->db->query("select HNA, 
        //                                   Dsc_Cab, 
        //                                   Dsc_Pri 
        //                            from mst_harga 
        //                            where Produk = '".$kode."' and 
        //                                 Cabang = (case when ifnull(Cabang,'') != '' then '".$this->cabang."' else '' end) limit 1")->row();
        $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Prinsipal, Dsc_Cab,Dsc_Pri,Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'"); 
        $num_query = $query->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab,Prinsipal, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  ifnull(Cabang,'')='' limit 1")->row(); 
        }else{
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab,Prinsipal, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        } 
        return $query;
    }

    // Satuan Produk
    public function getprodukorder($kode = NULL,$NODO=NULL)
    {   
        $query = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$NODO."' and KodeProduk='".$kode."'")->row();
        return $query;
    }

    // Satuan Produk
    public function getSatuanBuatOrder($kode = NULL)
    {   
        $query = $this->db->query("select a.Satuan, b.UnitStok, b.UnitCOGS from mst_produk a, trs_invsum b where a.Kode_Produk = '".$kode."' and a.`Kode_Produk` = b.`KodeProduk` and b.Gudang = 'Baik' and b.tahun='".date('Y')."' limit 1")->row();
        return $query;
    }

    // public function saveDataSalesOrder($params = null)
    // {
    //     // // Set no dokumen
    //     // $NoSO = $this->Model_main->noDokumen('SO');
    //     // // Update no dokumen
    //     // $this->Model_main->saveNoDokumen('SO', substr($NoSO, -7));
       
    //     $dataPelanggan = $this->dataPelanggan($params->pelanggan);
    //     $dataSalesman = $this->dataSalesman($params->salesman);   
    //     $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

    //     $status = "Usulan";
    //     $statusTOP = "Ok";
    //     $statusLimit = "Ok";
    //     $statusDiscCab = "Ok";
    //     $statusDiscPrins = "Ok";
    //     $do = true;
    //     $totalPiutang = $params->total + $params->piutang;
    //     $xPelanggan = explode("-", $params->pelanggan);
    //     $Pelanggan = $xPelanggan[0];
    //     $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
    //     $datax = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status in ('Open','OpenDIH') and Saldo != 0 order by TglFaktur desc")->result();

    //      //================ Running Number ======================================//
    //     $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
    //     $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
    //     $kodeDokumen = $this->Model_main->kodeDokumen('SO');
    //     $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13")->result();
    //     if(empty($data[0]->no) || $data[0]->no == ""){
    //       $lastNumber = 1000001;
    //     }else {
    //       $lastNumber = $data[0]->no + 1;
    //     }
    //     $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
    //     //================= end of running number ========================================//

    //     foreach ($datax as $key) {
    //         if (strtotime($key->TglFaktur) < strtotime($tglTOP)) {
    //             $status = "Usulan";
    //             $statusTOP = "TOP";
    //             $do = false;
    //             $this->db->set("Cabang", $this->cabang);
    //             $this->db->set("Dokumen", "Sales Order");
    //             $this->db->set("NoDokumen", $noDokumen);
    //             $this->db->set("Status", "TOP");
    //             $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
    //             $valid =  $this->db->insert("trs_approval");
    //             break;
    //         }
    //     }

    //     if ($params->limit < $totalPiutang) {
    //         $status = "Usulan";
    //         $statusLimit = "Limit";
    //         $do = false;
    //         $this->db->set("Cabang", $this->cabang);
    //         $this->db->set("Dokumen", "Sales Order");
    //         $this->db->set("NoDokumen", $noDokumen);
    //         $this->db->set("Status", "Limit");
    //         $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
    //         $valid =  $this->db->insert("trs_approval");
    //     } 

    //     foreach ($params->produk as $key => $value)
    //     {
    //         $dscprins = $params->dscprins1[$key] + $params->dscprins2[$key];
    //         if ($params->maksdsccab[$key] < $params->dsccab[$key]) {
    //             $status = "Usulan";
    //             $statusDiscCab = "DC";
    //             $do = false;
    //             $this->db->set("Cabang", $this->cabang);
    //             $this->db->set("Dokumen", "Sales Order");
    //             $this->db->set("NoDokumen", $noDokumen);
    //             $this->db->set("Status", "DC");
    //             $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
    //             $valid =  $this->db->insert("trs_approval");
    //             break;
    //         }

    //         if ($params->maksdscprins[$key] < $dscprins) {
    //             $status = "Usulan";
    //             $statusDiscPrins = "DP";
    //             $do = false;
    //             $this->db->set("Cabang", $this->cabang);
    //             $this->db->set("Dokumen", "Sales Order");
    //             $this->db->set("NoDokumen", $noDokumen);
    //             $this->db->set("Status", "DP");
    //             $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
    //             $valid =  $this->db->insert("trs_approval");
    //             break;
    //         }
    //     } 

    //     $carabayar = $params->carabayar;
    //     if($carabayar == "Cash"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+7 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
    //     }elseif($carabayar == "Kredit"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+21 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
    //     }elseif($carabayar == "RPO60"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+60 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
    //     }elseif($carabayar == "RPO75"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+75 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
    //     }elseif($carabayar == "RPO90"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+90 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
    //     }elseif($carabayar == "RPO120"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+120 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
    //     }elseif($carabayar == "RPO150"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+150 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
    //     }elseif($carabayar == "RPO180"){
    //         $curr_date = date('Y-m-d H:i:s');
    //         $curr_date = strtotime($curr_date);
    //         $tgl_jto   = strtotime("+180 day", $curr_date);
    //         $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
    //     }
    //     //save header  
    //     $this->db->set("Cabang", $this->cabang);
    //     $this->db->set("NoSO", $noDokumen);
    //     $this->db->set("TglSO", $params->tgl);
    //     $this->db->set("TimeSO", date('Y-m-d H:i:s'));
    //     $this->db->set("Pelanggan", $params->pelanggan);
    //     $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
    //     $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
    //     $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
    //     $this->db->set("NamaTipePelanggan", "");
    //     $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
    //     $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
    //     $this->db->set("Acu", $params->acu);
    //     $this->db->set("CaraBayar", $params->carabayar);
    //     $this->db->set("ppn_pelanggan", $params->flag_ppn);
    //     $this->db->set("CashDiskon", $params->cashdiskon);
    //     $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
    //     $this->db->set("TOP", $dataPelanggan->TOP);
    //     $this->db->set("TglJtoOrder", $tgljto);
    //     $this->db->set("Salesman", $dataSalesman->Kode);
    //     $this->db->set("NamaSalesman", $dataSalesman->Nama);
    //     $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
    //     $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
    //     $this->db->set("Status", $status);
    //     $this->db->set("StatusTOP", $statusTOP);
    //     $this->db->set("StatusLimit", $statusLimit);
    //     $this->db->set("StatusDiscCab", $statusDiscCab);
    //     $this->db->set("StatusDiscPrins", $statusDiscPrins);
    //     $this->db->set("TipeDokumen", "SO");
    //     $this->db->set("Gross", round($params->grosir,0));
    //     $this->db->set("Potongan", round($params->potongan,0));
    //     $this->db->set("Value", round($params->value,0));
    //     $this->db->set("Ppn", round($params->ppn,0));
    //     $this->db->set("LainLain", "");
    //     $this->db->set("Materai", $params->materai);
    //     $this->db->set("OngkosKirim", $params->ongkir);
    //     $this->db->set("Total", round($params->total,0));
    //     $this->db->set("Keterangan1", "");
    //     $this->db->set("Keterangan2", "");
    //     $this->db->set("Barcode", "");
    //     $this->db->set("QrCode", "");
    //     $this->db->set("NoDo", "");
    //     $this->db->set("NoFaktur", "");
    //     $this->db->set("NoSP", $params->nosp);
    //     $this->db->set("TipeFaktur", $params->tipefaktur);
    //     $this->db->set("NoIDPaket", $params->idpaket);
    //     $this->db->set("KeteranganTender", $params->kettender);
    //     $this->db->set("statusPusat", "Gagal");
    //     $valid =  $this->db->insert("trs_sales_order"); 
    //     $i=0;
    //     foreach ($params->produk as $key => $value) 
    //     {
    //         if (!empty($params->produk[$key])) 
    //         {
    //             $i++;
    //             $expld = explode("~", $params->produk[$key]);
    //             $KodeProduk = $expld[0];
    //             $NamaProduk = $expld[1];

    //             $jml = $params->jumlah[$key];
    //             $harga = $params->harga[$key];
    //             $dsccab = $params->dsccab[$key];
    //             $dscprins1 = $params->dscprins1[$key];
    //             $dscprins2 = $params->dscprins2[$key];
    //             $bnscab = $params->bnscab[$key];
    //             $bnsprins = $params->bnsprins[$key];
    //             $statusppn = $params->flag_ppn;

    //             // tambahan
    //             $unCogs = $params->cogsval[$key];

    //             $gross = round(($jml  + ($bnscab + $bnsprins)) * $harga,0);
    //             $stotCogs = round(($jml  + ($bnscab + $bnsprins)) * $unCogs,0);
    //             $diskoncab = ($dsccab)/100;

    //             $dsccab = ($harga * ($jml)) * (($diskoncab)); 
    //             $boncab = ($bnscab * $harga);

    //             $diskonprins1 = ($dscprins1)/100;
    //             $diskonprins2 = ($dscprins2)/100;
    //             $diskonprins = $diskonprins1 + $diskonprins2;
    //             $dscprins1 = ($harga * ($jml)) * (($diskonprins1));
    //             $dscprins2 = ($harga * ($jml)) * (($diskonprins2));
    //             $dscprins = ($dscprins1) + ($dscprins2);
    //             $bonprins = (($bnsprins) * ($harga));

    //             $dscTot = $dsccab + $dscprins;
    //             $bnsTot = $boncab + $bonprins;
                
    //             $potongan = round($dscTot + $bnsTot,0);     
    //             $value = round($gross - $potongan,0);
    //             if($statusppn == 'Y'){
    //                 $ppn = round($value * 0.1,0);
    //             }else{
    //                 $ppn = 0;
    //             }
                
    //             $TotValue = round($value + $ppn,0); 

    //             $totgross = $totgross + $gross;
    //             $totpotongan = $totpotongan + $potongan;
    //             $totvalue = $totvalue + $value;
    //             $totppn = $totppn + $ppn;
    //             $summary = $summary + $totppn;
    //             $totCogs = $totCogs + $stotCogs;


    //             // end tambahan

    //             //$gross = $jml * $harga;     
    //             //$diskoncab = $dsccab/100;
    //             //$diskonprins1 = $dscprins1/100;
    //             //$diskonprins2 = $dscprins2/100;
    //             //$diskonprins = $diskonprins1 + $diskonprins2;
    //             //$dsccab = ( $harga * $jml ) * ( $diskoncab ); 
    //             //$dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
    //             //$dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
    //             //$dscprins = $dscprins1 + $dscprins2;
    //             //$boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
    //             //$bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
    //             //$potongan = $bonprins + $boncab + $dsccab + $dscprins;     
    //             //$value = $gross - $potongan;
    //             //$ppn = ($value) * ( 10 / 100 );
    //             //$TotValue = $value + $ppn; 

    //             $this->db->set("Cabang", $this->cabang);
    //             $this->db->set("NoSO", $noDokumen);
    //             $this->db->set("noline", $i);
    //             $this->db->set("TglSO", $params->tgl);
    //             $this->db->set("TimeSO", date('Y-m-d H:i:s'));
    //             $this->db->set("Pelanggan", $params->pelanggan);
    //             $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
    //             $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
    //             $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
    //             $this->db->set("NamaTipePelanggan", "");
    //             $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
    //             $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
    //             $this->db->set("Acu", $params->acu);
    //             $this->db->set("CaraBayar", $params->carabayar);
    //             $this->db->set("CashDiskon", $params->cashdiskon);
    //             $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
    //             $this->db->set("TOP", $dataPelanggan->TOP);
    //             $this->db->set("TglJtoOrder", $tgljto );
    //             $this->db->set("Salesman", $dataSalesman->Kode);
    //             $this->db->set("NamaSalesman", $dataSalesman->Nama);
    //             $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
    //             $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
    //             $this->db->set("Status", $status);
    //             $this->db->set("TipeDokumen", "SO");
    //             $this->db->set("Gross", $gross);
    //             $this->db->set("Potongan", $potongan);
    //             $this->db->set("Value", $value);
    //             $this->db->set("Ppn", $ppn);
    //             $this->db->set("LainLain", "");
    //             $this->db->set("Total", $TotValue);
    //             $this->db->set("Keterangan1", "");
    //             $this->db->set("Keterangan2", "");
    //             $this->db->set("Barcode", "");
    //             $this->db->set("QrCode", "");
    //             $this->db->set("NoDO", "");
    //             $this->db->set("NoFaktur", "");
    //             // DETAIL PRODUK
    //             $this->db->set("KodeProduk", $KodeProduk);
    //             $this->db->set("NamaProduk", $NamaProduk);
    //             $this->db->set("UOM", $params->uom[$key]);
    //             $this->db->set("Harga", $params->harga[$key]);
    //             $this->db->set("QtySO", $params->jumlah[$key]);
    //             $this->db->set("Bonus", $params->bnscab[$key] + $params->bnsprins[$key]);
    //             $this->db->set("ValueBonus", $params->valuebnscab[$key] + $params->valuebnsprins[$key]);
    //             $this->db->set("DiscCab", $params->dsccab[$key]);
    //             $this->db->set("ValueDiscCab", $params->valuedsccab[$key]);
    //             // $this->db->set("DiscCab2", $params->dsccab2[$key]);
    //             // $this->db->set("ValueDiscCab2", $params->valuedsccab2[$key]);
    //             $this->db->set("DiscCabTot", "");
    //             $this->db->set("ValueDiscCabTotal", "");
    //             $this->db->set("DiscPrins1", $params->dscprins1[$key]);
    //             $this->db->set("ValueDiscPrins1", $params->valuedscprins1[$key]);
    //             $this->db->set("DiscPrins2", $params->dscprins2[$key]);
    //             $this->db->set("ValueDiscPrins2", $params->valuedscprins2[$key]);
    //             $this->db->set("DiscPrinsTot", "");
    //             $this->db->set("ValueDiscPrinsTotal", "");
    //             $this->db->set("DiscTotal", "");
    //             $this->db->set("ValueDiscTotal", "");
    //             $this->db->set("DiscCabMax", $params->maksdsccab[$key]);
    //             $this->db->set("KetDiscCabMax", "");
    //             $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
    //             $this->db->set("KetDiscPrinsMax", "");
    //             $this->db->set("COGS", $params->cogsval[$key]);
    //             $this->db->set("TotalCOGS", $stotCogs);
    //             $valid =  $this->db->insert("trs_sales_order_detail");
    //             if($valid){
    //                 $this->db->query("update trs_sales_order_detail a, mst_produk b 
    //                             SET a.Prinsipal=b.Prinsipal,
    //                               a.Prinsipal2=b.Prinsipal2,
    //                               a.Supplier=b.Supplier1,
    //                               a.Supplier2=b.Supplier2,
    //                               a.Pabrik =b.Pabrik,
    //                               a.Farmalkes=b.Farmalkes
    //                             WHERE a.KodeProduk=b.Kode_Produk and 
    //                                   a.KodeProduk = '".$KodeProduk."' and a.NoSO = '".$noDokumen."'");
    //             }
    //         }
    //     }
    //     if($valid){
    //         $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'SO' and Cabang = '".$this->cabang."'");
    //     }
    //     $callback['no'] = $noDokumen;
    //     $callback['do'] = $do;

    //     return $callback;
    // }
    public function listapproval($pelanggan = null)
    {
        $query = $this->db->query("select mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.saldo,
                                           faktur_pelanggan.Umur_faktur
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(trs_pelunasan_giro_detail.`ValuePelunasan`) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.`Pelanggan`
                                      where (LEFT(trs_faktur.Status,5) != 'Lunas' OR trs_faktur.`Saldo`!= 0)
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$pelanggan."'")->row();

        return $query;
    }

    public function saveDataSalesOrder($params = null)
    {
        // // Set no dokumen
        // $NoSO = $this->Model_main->noDokumen('SO');
        // // Update no dokumen
        // $this->Model_main->saveNoDokumen('SO', substr($NoSO, -7));
       
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

        // foreach ($datax as $key) {
        //     if (strtotime($key->TglFaktur) < strtotime($tglTOP)) {
        //         $status = "Usulan";
        //         $statusTOP = "TOP";
        //         $do = false;
        //         $this->db->set("Cabang", $this->cabang);
        //         $this->db->set("Dokumen", "Sales Order");
        //         $this->db->set("NoDokumen", $noDokumen);
        //         $this->db->set("Status", "TOP");
        //         $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        //         $valid =  $this->db->insert("trs_approval");
        //         break;
        //     }
        // }
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


                  // end tambahan

                  //$gross = $jml * $harga;     
                  //$diskoncab = $dsccab/100;
                  //$diskonprins1 = $dscprins1/100;
                  //$diskonprins2 = $dscprins2/100;
                  //$diskonprins = $diskonprins1 + $diskonprins2;
                  //$dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                  //$dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                  //$dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                  //$dscprins = $dscprins1 + $dscprins2;
                  //$boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
                  //$bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
                  //$potongan = $bonprins + $boncab + $dsccab + $dscprins;     
                  //$value = $gross - $potongan;
                  //$ppn = ($value) * ( 10 / 100 );
                  //$TotValue = $value + $ppn; 

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
                  $this->db->set("KetDiscCabMax", "");
                  $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
                  $this->db->set("KetDiscPrinsMax", "");
                  $this->db->set("COGS", $params->cogsval[$key]);
                  $this->db->set("TotalCOGS", $stotCogs);
                  $this->db->set("created_at", date('Y-m-d H:i:s'));
                  $this->db->set("created_by", $this->session->userdata('username'));
                  $valid =  $this->db->insert("trs_sales_order_detail");
                  if($valid){
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
        }
        $callback['no'] = $noDokumen;
        $callback['do'] = $do;

        return $callback;
    }

    public function saveUlangDataSalesOrder($params = null)
    {
        // // Set no dokumen
        // $NoSO = $this->Model_main->noDokumen('SO');
        // // Update no dokumen
        // $this->Model_main->saveNoDokumen('SO', substr($NoSO, -7));
        
        $xpldsales = explode("~", $params->salesman);
        $xpldpelanggan = explode("~", $params->pelanggan);
        $kodesales = $xpldsales[0];
        $kodepelanggan = $xpldpelanggan[0];

        $dataPelanggan = $this->dataPelangganNew($kodepelanggan);
        $dataSalesman = $this->dataSalesman($kodesales);   
        $dataTOPLimit = $this->listapproval($kodepelanggan);
        $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

        $status = "Usulan";
        $statusTOP = "Ok";
        $statusLimit = "Ok";
        $statusDiscCab = "Ok";
        $statusDiscPrins = "Ok";
        $do = true;
        $totalPiutang = $params->total + $params->piutang;
        $xPelanggan = explode("~", $params->pelanggan);
        $Pelanggan = $xPelanggan[0];
        $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
        $datax = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status in ('Open','OpenDIH') and Saldo != 0 order by TglFaktur desc")->result();

        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('SO');
        $year = date('Y');
        $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13 and YEAR(tglSO) ='".$year."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//

        // foreach ($datax as $key) {
        //     if (strtotime($key->TglFaktur) < strtotime($tglTOP)) {
        //         $status = "Usulan";
        //         $statusTOP = "TOP";
        //         $do = false;
        //         $this->db->set("Cabang", $this->cabang);
        //         $this->db->set("Dokumen", "Sales Order");
        //         $this->db->set("NoDokumen", $noDokumen);
        //         $this->db->set("Status", "TOP");
        //         $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        //         $valid =  $this->db->insert("trs_approval");
        //         break;
        //     }
        // }
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
        //save header  
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("NoSO", $noDokumen);
        $this->db->set("TglSO", $params->tgl);
        $this->db->set("TimeSO", date('Y-m-d H:i:s'));
        $this->db->set("Pelanggan", $kodepelanggan);
        $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
        $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
        $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
        $this->db->set("NamaTipePelanggan", "");
        $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
        $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
        $this->db->set("Acu", $params->acu);
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
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key]) and ($params->jumlah[$key] > 0 or ($params->bnscab[$key] + $params->bnsprins[$key]) > 0)) 
            {
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


                // end tambahan

                //$gross = $jml * $harga;     
                //$diskoncab = $dsccab/100;
                //$diskonprins1 = $dscprins1/100;
                //$diskonprins2 = $dscprins2/100;
                //$diskonprins = $diskonprins1 + $diskonprins2;
                //$dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                //$dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                //$dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                //$dscprins = $dscprins1 + $dscprins2;
                //$boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
                //$bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
                //$potongan = $bonprins + $boncab + $dsccab + $dscprins;     
                //$value = $gross - $potongan;
                //$ppn = ($value) * ( 10 / 100 );
                //$TotValue = $value + $ppn; 

                $this->db->set("Cabang", $this->cabang);
                $this->db->set("NoSO", $noDokumen);
                $this->db->set("noline", $i);
                $this->db->set("TglSO", $params->tgl);
                $this->db->set("TimeSO", date('Y-m-d H:i:s'));
                $this->db->set("Pelanggan", $kodepelanggan);
                $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
                $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                $this->db->set("NamaTipePelanggan", "");
                $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                $this->db->set("Acu", $params->acu);
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
                $this->db->set("KetDiscCabMax", "");
                $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
                $this->db->set("KetDiscPrinsMax", "");
                $this->db->set("COGS", $params->cogsval[$key]);
                $this->db->set("TotalCOGS", $stotCogs);
                $this->db->set("created_at", date('Y-m-d H:i:s'));
                $this->db->set("created_by", $this->session->userdata('username'));
                $valid =  $this->db->insert("trs_sales_order_detail");
                if($valid){
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
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'SO' and Cabang = '".$this->cabang."'");
        }
        $callback['no'] = $noDokumen;
        $callback['do'] = $do;

        return $callback;
    }

    public function saveDataDeliveryOrder($NoSO = null, $NoDO = NULL)
   {    
        $header = $this->db->query("select * from trs_sales_order where NoSO = '".$NoSO."'")->row();
        $detail = $this->db->query("select * from trs_sales_order_detail where NoSO = '".$NoSO."'")->result();
        $noDokumen ='';
        $NoDO = "";
        $val = true;
        foreach ($detail as $key => $value) 
        {
            $AllBatch = $this->AllBatch($detail[$key]->KodeProduk);
            $qty_so     = $detail[$key]->QtySO;
            $bonus_so   = $detail[$key]->Bonus;
            if(($qty_so + $bonus_so) >  $AllBatch[0]->UnitStok){
                $this->db->set('Status', "Usulan");
                $alasan = "Stok Produk ".$detail[$key]->KodeProduk."~".$detail[$key]->NamaProduk." Tidak Terpenuhi";
                $this->db->set('alasan_status', $alasan);
                $this->db->set('flag_nostok', 'Y');
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');
                log_message("error","stok tidak terpenuhi");
                $val = false;
                $noDokumen='';
                break;
            }
        }
        if ($val == true) {
        	$year = date('Y');
            $dataPelanggan = $this->dataPelangganNew($header->Pelanggan);
            $dataSalesman = $this->dataSalesman($header->Salesman);
            if (empty($NoDO)) {
                //================ Running Number ======================================//
                $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                $kodeDokumen = $this->Model_main->kodeDokumen('DO');
                $data = $this->db->query("select max(right(NoDO,7)) as 'no' from trs_delivery_order_sales where substr(NoDO,5,2) = '06' and Cabang = '".$this->cabang."' and length(NoDO) = 13 and YEAR(TglDO) ='".$year."'")->result();
                if(empty($data[0]->no) || $data[0]->no == ""){
                  $lastNumber = 1000001;
                }else {
                  $lastNumber = $data[0]->no + 1;
                }
                $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                //================= end of running number ========================================//
            }
            $carabayar = $header->CaraBayar;
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

            $totgross = 0;
            $totpotongan = 0;
            $totalvalue = 0;
            $totcashdiskon = 0;
            $totppn = 0;
            $summary = 0;
            $totCogs = 0;
            $i=0;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("NoDO", $noDokumen);
            $this->db->set("TglDO", date('Y-m-d'));
            $this->db->set("TimeDO", date('Y-m-d H:i:s'));
            $this->db->set("Pengirim", '');
            $this->db->set("NamaPengirim", '');
            $this->db->set("Pelanggan", $header->Pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("AlamatKirim", $dataPelanggan->Alamat);
            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
            $this->db->set("NamaTipePelanggan", "");
            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
            $this->db->set("Acu", $header->Acu);
            $this->db->set("CaraBayar", $header->CaraBayar);
            $this->db->set("CashDiskon", $header->CashDiskon);
            $this->db->set("ValueCashDiskon", $header->ValueCashDiskon);
            $this->db->set("ppn_pelanggan", $header->ppn_pelanggan);
            $this->db->set("TOP", $dataPelanggan->TOP);
            $this->db->set("TglJtoOrder", $tgljto);
            $this->db->set("Salesman", $dataSalesman->Kode);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
            $this->db->set("Status", 'Open');
            $this->db->set("TipeDokumen", "DO");
            $this->db->set("Gross", 0);
            $this->db->set("Potongan", 0);
            $this->db->set("Value",0);
            $this->db->set("Ppn", 0);
            $this->db->set("LainLain",0);
            $this->db->set("Materai", 0);
            $this->db->set("OngkosKirim", 0);
            $this->db->set("Total", 0);
            $this->db->set("Keterangan1", "");
            $this->db->set("Keterangan2", "");
            $this->db->set("Barcode", "");
            $this->db->set("QrCode", "");
            $this->db->set("NoSO", $NoSO);
            $this->db->set("NoFaktur", "");
            $this->db->set("NoSP", $header->NoSP);
            $this->db->set("TipeFaktur", $header->TipeFaktur);
            $this->db->set("NoIDPaket", $header->NoIDPaket);
            $this->db->set("KeteranganTender", $header->KeteranganTender);
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("created_at", date('Y-m-d H:i:s'));
            $this->db->set("created_by", $this->session->userdata('username'));
            $valid =  $this->db->insert("trs_delivery_order_sales");
            $xx = $this->db->query("select Pelanggan from trs_delivery_order_sales where NoDO = '".$noDokumen."' limit 1")->row();
            $headerPelanggan = $xx->Pelanggan; 
            foreach ($detail as $key => $value) 
            {  
                if($headerPelanggan == $header->Pelanggan){ 
                    if (!empty($detail[$key]->KodeProduk)) 
                    {
                        $batch = $this->Batch($detail[$key]->KodeProduk);        
                        $QtySO = $detail[$key]->QtySO;
                        $bonusSOawal = $detail[$key]->Bonus;
                        foreach ($batch as $kunci => $nilai) {
                            $i++; 
                            $jumlah = $detail[$key]->QtySO + $detail[$key]->Bonus;
                            if($jumlah <= 0){
                                log_message("error","kosong");
                                break;
                            }
                            // $cek = $this->cekStok($detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah); 
                            $this->db->set("Cabang", $this->cabang);
                            $this->db->set("NoDO", $noDokumen);
                            $this->db->set("noline", $i);
                            $this->db->set("TglDO", date('Y-m-d'));
                            $this->db->set("TimeDO", date('Y-m-d H:i:s'));
                            $this->db->set("Pengirim", '');
                            $this->db->set("NamaPengirim", '');
                            $this->db->set("Pelanggan", $header->Pelanggan);
                            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                            $this->db->set("AlamatKirim", $dataPelanggan->Alamat);
                            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                            $this->db->set("NamaTipePelanggan", "");
                            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                            $this->db->set("Acu", $header->Acu);
                            $this->db->set("CaraBayar", $header->CaraBayar);
                            $this->db->set("CashDiskon", $header->CashDiskon);
                            $this->db->set("TOP", $dataPelanggan->TOP);
                            $this->db->set("TglJtoOrder", $tgljto);
                            $this->db->set("Salesman", $dataSalesman->Kode);
                            $this->db->set("NamaSalesman", $dataSalesman->Nama);
                            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                            $this->db->set("Status", "Open");
                            $this->db->set("TipeDokumen", "DO");
                            $this->db->set("Keterangan1", "");
                            $this->db->set("Keterangan2", "");
                            $this->db->set("Barcode", "");
                            $this->db->set("QrCode", "");
                            $this->db->set("NoSO", $NoSO);
                            $this->db->set("NoFaktur", "");
                            $this->db->set("KodeProduk", $detail[$key]->KodeProduk);
                            $this->db->set("NamaProduk", $detail[$key]->NamaProduk);
                            $this->db->set("UOM", $detail[$key]->UOM);
                            $this->db->set("Harga", $detail[$key]->Harga);
                            $this->db->set("QtySO", $QtySO);
                            $this->db->set("BonusSO", $bonusSOawal);
                            // $this->db->set("BonusDO", $detail[$key]->Bonus);
                            // $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                            $this->db->set("DiscCab", $detail[$key]->DiscCab);                  
                            $this->db->set("DiscCabTot", $detail[$key]->DiscCab);
                            // $this->db->set("ValueDiscCabTotal", $detail[$key]->DiscCab);
                            $this->db->set("DiscPrins1", $detail[$key]->DiscPrins1);
                            $this->db->set("DiscPrins2", $detail[$key]->DiscPrins2);
                            $this->db->set("DiscPrinsTot", $detail[$key]->DiscPrins1 + $detail[$key]->DiscPrins2);
                            // $this->db->set("ValueDiscPrinsTotal", "");
                            // $this->db->set("DiscTotal", "");
                            // $this->db->set("ValueDiscTotal", "");
                            $this->db->set("DiscCabMax", $detail[$key]->DiscCabMax);
                            $this->db->set("KetDiscCabMax", "");
                            $this->db->set("DiscPrinsMax", $detail[$key]->DiscPrinsMax);
                            $this->db->set("KetDiscPrinsMax", "");
                            $this->db->set("created_at", date('Y-m-d H:i:s'));
                            $this->db->set("created_by", $this->session->userdata('username'));
                            // $this->db->set("COGS", $detail[$key]->COGS);
                            // $this->db->set("TotalCOGS", $detail[$key]->TotalCOGS);
                            if (($detail[$key]->QtySO + $detail[$key]->Bonus ) <= $batch[$kunci]->UnitStok) {     
                                $jml = $detail[$key]->QtySO;
                                $harga = $detail[$key]->Harga;
                                $dsccab = $detail[$key]->DiscCab;
                                $dscprins1 = $detail[$key]->DiscPrins1;
                                $dscprins2 = $detail[$key]->DiscPrins2;
                                $bns = $detail[$key]->Bonus;
                                $CashDiskon = $header->CashDiskon;
                                $statusppn = $header->ppn_pelanggan;

                                // tambahan
                                if($batch[$kunci]->COGS < 0){
                                   $unCogs =  -1 * $batch[$kunci]->COGS;
                                }else{
                                   $unCogs = $batch[$kunci]->COGS; 
                                }

                                $gross = round((($jml  + $bns) * $harga),0);
                                $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                $diskoncab = ($dsccab)/100;

                                $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                $boncabval = ($bns * $harga);

                                $diskonprins1 = ($dscprins1)/100;
                                $diskonprins2 = ($dscprins2)/100;
                                $diskonprins = $diskonprins1 + $diskonprins2;
                                $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                $dscprins = ($dscprins1val) + ($dscprins2val);
                                // $bonprinsval = (($bns) * ($harga));

                                $dscTot = $dsccabval + $dscprins;
                                $bnsTot = $boncabval;

                                $valdisctotal = $dsccabval+$dscprins1val+$dscprins2val;
                                $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                
                                $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);     
                                $value = round(($gross - $potongan),0);
                                if($statusppn=='Y'){
                                    $ppn = round(($value * 0.1),0);
                                }else{
                                    $ppn = 0;
                                }
                                
                                $TotValue = round(($value + $ppn),0); 
                                // $TotAll = 

                                $totgross = $totgross + $gross;
                                $totpotongan = $totpotongan + $potongan;
                                $totalvalue = $totalvalue + $value;
                                $totppn = $totppn + $ppn;
                                $summary = $summary + $TotValue;
                                $totCogs = $totCogs + $stotCogs;

                                $this->db->set("ValueDiscCab", $dsccabval);
                                $this->db->set("ValueDiscCabTotal", $dsccabval);
                                $this->db->set("ValueDiscPrins1", $dscprins1val);
                                $this->db->set("ValueDiscPrins2", $dscprins2val);
                                $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                $this->db->set("DiscTotal", $dsccab+$dscprins1+$dscprins2);
                                $this->db->set("ValueDiscTotal", $dsccabval+$dscprins1val+$dscprins2val);
                                $this->db->set("ValueCashDiskon", $valcashdiskon);
                                $this->db->set("QtyDO", $jml);
                                $this->db->set("BonusDO", $detail[$key]->Bonus);
                                $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                                $this->db->set("Gross", $gross);
                                $this->db->set("Potongan", $potongan);
                                $this->db->set("Value", $value);
                                $this->db->set("Ppn", $ppn);
                                $this->db->set("LainLain", "");
                                $this->db->set("Total", $TotValue);
                                $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                $this->db->set("COGS", $unCogs);
                                $this->db->set("TotalCOGS", $stotCogs);
                                $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                if($valid){
                                    $jumlah = $detail[$key]->QtySO + $detail[$key]->Bonus;
                                    $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                    $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                                        SET a.Prinsipal=b.Prinsipal,
                                                          a.Prinsipal2=b.Prinsipal2,
                                                          a.Supplier=b.Supplier1,
                                                          a.Supplier2=b.Supplier2,
                                                          a.Pabrik =b.Pabrik,
                                                          a.Farmalkes=b.Farmalkes
                                                        WHERE 
                                                          a.KodeProduk=b.Kode_Produk and 
                                                          a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                }
                                
                                break;
                            }
                            if((($detail[$key]->QtySO + $detail[$key]->Bonus ) > $batch[$kunci]->UnitStok) and ($batch[$kunci]->UnitStok < $detail[$key]->Bonus)){

                                $jml = $batch[$kunci]->UnitStok;
                                $harga = $detail[$key]->Harga;
                                $dsccab = $detail[$key]->DiscCab;
                                $dscprins1 = $detail[$key]->DiscPrins1;
                                $dscprins2 = $detail[$key]->DiscPrins2;
                                $bns = 0;
                                $CashDiskon = $header->CashDiskon;
                                $statusppn = $header->ppn_pelanggan;

                                // tambahan
                                if($batch[$kunci]->COGS < 0){
                                   $unCogs =  -1 * $batch[$kunci]->COGS;
                                }else{
                                   $unCogs = $batch[$kunci]->COGS; 
                                }

                                $gross = round((($jml  + $bns) * $harga),0);
                                $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                $diskoncab = ($dsccab)/100;

                                $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                $boncabval = ($bns * $harga);

                                $diskonprins1 = ($dscprins1)/100;
                                $diskonprins2 = ($dscprins2)/100;
                                $diskonprins = $diskonprins1 + $diskonprins2;
                                $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                $dscprins = ($dscprins1val) + ($dscprins2val);
                                // $bonprinsval = (($bns) * ($harga));

                                $dscTot = $dsccabval + $dscprins;
                                $bnsTot = $boncabval;

                                $valdisctotal = $dsccabval+$dscprins1val+$dscprins2val;
                                $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                
                                $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);      
                                $value = round(($gross - $potongan),0);
                                if($statusppn=='Y'){
                                    $ppn = round(($value * 0.1),0);
                                }else{
                                    $ppn = 0;
                                }
                                $TotValue = round(($value + $ppn),0); 

                                $totgross = $totgross + $gross;
                                $totpotongan = $totpotongan + $potongan ;
                                $totalvalue = $totalvalue + $value;
                                $totppn = $totppn + $ppn;
                                $summary = $summary + $TotValue;
                                $totCogs = $totCogs + $stotCogs;

                                $this->db->set("ValueDiscCab", $dsccabval);
                                $this->db->set("ValueDiscCabTotal", $dsccabval);
                                $this->db->set("ValueDiscPrins1", $dscprins1val);
                                $this->db->set("ValueDiscPrins2", $dscprins2val);
                                $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                $this->db->set("DiscTotal", $dsccab+$dscprins1+$dscprins2);
                                $this->db->set("ValueDiscTotal", $dsccabval+$dscprins1val+$dscprins2val);
                                $this->db->set("ValueCashDiskon", $valcashdiskon);
                                $this->db->set("QtyDO", $jml);
                                $this->db->set("Gross", $gross);
                                $this->db->set("BonusDO", 0);
                                $this->db->set("ValueBonus", 0);
                                $this->db->set("Potongan", $potongan);
                                $this->db->set("Value", $value);
                                $this->db->set("Ppn", $ppn);
                                $this->db->set("LainLain", "");
                                $this->db->set("Total", $TotValue);
                                $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                $this->db->set("COGS", $unCogs);
                                $this->db->set("TotalCOGS", $stotCogs);
                                $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                $detail[$key]->QtySO = ($detail[$key]->QtySO) - $batch[$kunci]->UnitStok;
                                if($valid){
                                    $jumlah = $batch[$kunci]->UnitStok + $detail[$key]->Bonus;
                                    $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                    $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                                        SET a.Prinsipal=b.Prinsipal,
                                                          a.Prinsipal2=b.Prinsipal2,
                                                          a.Supplier=b.Supplier1,
                                                          a.Supplier2=b.Supplier2,
                                                          a.Pabrik =b.Pabrik,
                                                          a.Farmalkes=b.Farmalkes
                                                        WHERE 
                                                          a.KodeProduk=b.Kode_Produk and 
                                                          a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                }
                            }
                            else{
                                $jml = $batch[$kunci]->UnitStok - $detail[$key]->Bonus;
                                $harga = $detail[$key]->Harga;
                                $dsccab = $detail[$key]->DiscCab;
                                $dscprins1 = $detail[$key]->DiscPrins1;
                                $dscprins2 = $detail[$key]->DiscPrins2;
                                $bns = $detail[$key]->Bonus;
                                $CashDiskon = $header->CashDiskon;
                                $statusppn = $header->ppn_pelanggan;
                                // tambahan
                                
                                if($batch[$kunci]->COGS < 0){
                                   $unCogs =  -1 * $batch[$kunci]->COGS;
                                }else{
                                   $unCogs = $batch[$kunci]->COGS; 
                                }

                                $gross = round((($jml  + $bns) * $harga),0);
                                $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                $diskoncab = ($dsccab)/100;

                                $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                $boncabval = ($bns * $harga);

                                $diskonprins1 = ($dscprins1)/100;
                                $diskonprins2 = ($dscprins2)/100;
                                $diskonprins = $diskonprins1 + $diskonprins2;
                                $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                $dscprins = ($dscprins1val) + ($dscprins2val);
                                // $bonprinsval = (($bns) * ($harga));

                                $dscTot = $dsccabval + $dscprins;
                                $bnsTot = $boncabval;

                                $valdisctotal = $dsccabval+$dscprins1val+$dscprins2val;
                                $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                
                                $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);      
                                $value = round(($gross - $potongan),0);
                                if($statusppn=='Y'){
                                    $ppn = round(($value * 0.1),0);
                                }else{
                                    $ppn = 0;
                                }
                                $TotValue = round(($value + $ppn),0);   

                                $totgross = $totgross + $gross;
                                $totpotongan = $totpotongan + $potongan;
                                $totalvalue = $totalvalue + $value;
                                $totppn = $totppn + $ppn;
                                $summary = $summary + $TotValue;
                                $totCogs = $totCogs + $stotCogs;

                                $this->db->set("ValueDiscCab", $dsccabval);
                                $this->db->set("ValueDiscCabTotal", $dsccabval);
                                $this->db->set("ValueDiscPrins1", $dscprins1val);
                                $this->db->set("ValueDiscPrins2", $dscprins2val);
                                $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                $this->db->set("DiscTotal", $dsccab+$dscprins1+$dscprins2);
                                $this->db->set("ValueDiscTotal", $dsccabval+$dscprins1val+$dscprins2val);
                                $this->db->set("ValueCashDiskon", $valcashdiskon);
                                $this->db->set("QtyDO", $jml);
                                $this->db->set("BonusDO", $detail[$key]->Bonus);
                                $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                                $this->db->set("Gross", $gross);
                                $this->db->set("Potongan", $potongan);
                                $this->db->set("Value", $value);
                                $this->db->set("Ppn", $ppn);
                                $this->db->set("LainLain", "");
                                $this->db->set("Total", $TotValue);
                                $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                $this->db->set("COGS", $unCogs);
                                $this->db->set("TotalCOGS", $stotCogs);
                                $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                $detail[$key]->QtySO = ($detail[$key]->QtySO + $detail[$key]->Bonus ) - $batch[$kunci]->UnitStok;
                                $detail[$key]->Bonus = 0;
                                $detail[$key]->ValueBonus = 0;
                                if($valid){
                                    $jumlah = ($batch[$kunci]->UnitStok - $detail[$key]->Bonus) + $detail[$key]->Bonus;
                                    $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                    $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                                        SET a.Prinsipal=b.Prinsipal,
                                                          a.Prinsipal2=b.Prinsipal2,
                                                          a.Supplier=b.Supplier1,
                                                          a.Supplier2=b.Supplier2,
                                                          a.Pabrik =b.Pabrik,
                                                          a.Farmalkes=b.Farmalkes
                                                        WHERE 
                                                          a.KodeProduk=b.Kode_Produk and 
                                                          a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                }
                            }
                        }
                    }
                }
            }

            $valid = $this->db->query("update trs_delivery_order_sales 
                                        set  Gross = '".$totgross."', 
                                            Potongan = '".$totpotongan."',
                                            Value = '".$totalvalue."',
                                            Ppn = '".$totppn."',
                                            LainLain = '".$header->LainLain."',
                                            Materai = '".$header->Materai."',
                                            OngkosKirim = '".$header->OngkosKirim."',
                                            Total = '".$summary."'
                                        WHERE Cabang = '".$this->cabang."' and 
                                              NoDO = '".$noDokumen."'");
            
            // $this->db->set("Gross", $totgross);
            // $this->db->set("Potongan", $totpotongan);
            // $this->db->set("Value", $totalvalue);
            // $this->db->set("Ppn", $totppn);
            // $this->db->set("LainLain", $header->LainLain);
            // $this->db->set("Materai", $header->Materai);
            // $this->db->set("OngkosKirim", $header->OngkosKirim);
            // $this->db->set("Total", $summary);
            // $this->db->where("Cabang", $this->cabang);
            // $this->db->where("NoDO", $noDokumen);
            // $valid =  $this->db->update("trs_delivery_order_sales");
            if($valid){
                // $this->setStok($noDokumen);
                $this->db->set('NoDO', $noDokumen);
                $this->db->set('Status', "Closed");
                $this->db->set('alasan_status', "DO Sudah Di Proses");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->session->userdata('username'));
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');

                $this->db->set('NoDO', $noDokumen);
                $this->db->set('Status', "Closed");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->session->userdata('username'));
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order_detail');
                if($valid){
                    $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'DO' and Cabang = '".$this->cabang."'");
                   
                }
            }
        }
        return $noDokumen;
    }

    public function cekDataDO($noDokumen=NULL){
        $rollback = false;
                $query = $this->db->query("SELECT DISTINCT trs_delivery_order_sales_detail.KodeProduk,
                                            QtySO,BonusSO,sumdo.QtyDO,sumdo.BonusDO 
                                            FROM trs_delivery_order_sales_detail LEFT JOIN
                                            (SELECT KodeProduk,SUM(QtyDO) AS 'QtyDO', SUM(BonusDO) AS 'BonusDO'  
                                             from trs_delivery_order_sales_detail where NoDO ='".$noDokumen."'
                                             group by KodeProduk) as sumdo on sumdo.KodeProduk = trs_delivery_order_sales_detail.KodeProduk
                                            where trs_delivery_order_sales_detail.NoDO ='".$noDokumen."'")->result();
                foreach ($query as $cek) {
                    $cekprod = $cek->KodeProduk;
                    $cekqtyso = $cek->QtySO;
                    $cekbonusso = $cek->BonusSO;
                    $cekqtydo = $cek->QtyDO;
                    $cekbonusdo = $cek->BonusDO;
                    if(($cekqtyso + $cekbonusso) != ($cekqtydo + $cekbonusdo)){
                        $rollback = true;
                        break;
                    }
                }

                $query2 =$this->db->query("select NODO from trs_delivery_order_sales where NODO = '".$noDokumen."' and Gross=0 limit 1")->num_rows();
                $query3 =$this->db->query("select NODO from trs_delivery_order_sales where NODO = '".$noDokumen."' and NODO in (select NODO from trs_delivery_order_sales_detail where NODO = '".$noDokumen."') ")->num_rows();

                if($query2 > 0 or $query3 <= 0){
                    $rollback = true;
                }
                if($rollback == true){
                    $this->db->set("Status", "Batal");
                    $this->db->set('Keterangan1', "DO Batal By System");
                    $this->db->where("NoDO", $noDokumen);
                    $valid = $this->db->update('trs_delivery_order_sales'); 

                    $this->db->set("Status", "Batal");
                    $this->db->set('time_batal', date('Y-m-d H:i:s'));
                    $this->db->where("NoDO", $noDokumen);
                    $valid = $this->db->update('trs_delivery_order_sales_detail');

                    $this->db->set('NoDO', '');
                    $this->db->set('Status', "Usulan");
                    $this->db->set('alasan_status', "DO Batal By System");
                    $this->db->where('NoDO', $noDokumen);
                    $valid = $this->db->update('trs_sales_order');

                    $this->db->set('NoDO', '');
                    $this->db->set('Status', "Usulan");
                    $this->db->where('NoDO', $noDokumen);
                    $valid = $this->db->update('trs_sales_order_detail');

                    if($valid){
                        $this->backStok($noDokumen);
                    }
                }
    }

    // public function listDataSO($search = null, $limit = null, $status = null)
    // {   
    //     $byStatus = "";
    //     if (!empty($status)) {
    //         $byStatus = " and Status = '".$status."'";
    //     }

    //     // $query = $this->db->query("select * from trs_sales_order where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TimeSO DESC,NoSO ASC $limit ");
    //     $query = $this->db->query(        
    //             "SELECT a.NoDO, 
    //                     trs_sales_order.NoSO,
    //                     TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
    //                     Acu,Salesman,`CaraBayar`,`Status`,
    //                     TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,
    //                     -- Monitoring
    //                     CONCAT(
    //                         CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
    //                         CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
    //                         CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
    //                         CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
    //                         ) AS MonitoringApproval,
    //                     CONCAT(
    //                             -- Limit
    //                             CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
    //                             CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
    //                             -- TOP
    //                             CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Diskon Cabang
    //                             CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Diskon Prinsipal
    //                             CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Status Stok
    //                             IFNULL(alasan_status,'')  
    //                         ) AS MonitoringStatus                           
    //                 FROM trs_sales_order 
    //                 LEFT JOIN (SELECT NoDO,NoSO FROM `trs_delivery_order_sales` WHERE Cabang='".$this->cabang."') a ON trs_sales_order.`NoSO`=a.NoSO 
    //                 WHERE Cabang = '".$this->cabang."' $byStatus $search
    //                 ORDER BY TimeSO DESC,NoSO ASC $limit ");


    //     return $query;
    // }

     public function listDataSO($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and Status = '".$status."'";
        }

        // $query = $this->db->query("select * from trs_sales_order where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TimeSO DESC,NoSO ASC $limit ");
        if($status != 'Usulan'){
          $query = $this->db->query(        
                "SELECT trs_sales_order.NoDO, 
                        trs_sales_order.NoSO,
                        TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
                        Acu,Salesman,`CaraBayar`,`Status`,
                        TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,
                        -- Monitoring
                        CONCAT(
                            CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
                            CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
                            CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
                            CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
                            ) AS MonitoringApproval,
                        CONCAT(
                                -- Limit
                                CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
                                CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
                                CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
                                -- TOP
                                CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Cabang
                                CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Prinsipal
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
                                -- Status Stok
                                IFNULL(alasan_status,'')  
                            ) AS MonitoringStatus                           
                    FROM trs_sales_order 
                    WHERE Cabang = '".$this->cabang."' $search
                    ORDER BY TimeSO DESC,NoSO ASC $limit ");
        }else{
          $query = $this->db->query(        
                "SELECT a.NoDO, 
                        trs_sales_order.NoSO,
                        TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
                        Acu,Salesman,`CaraBayar`,`Status`,
                        TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,
                        -- Monitoring
                        CONCAT(
                            CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
                            CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
                            CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
                            CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
                            ) AS MonitoringApproval,
                        CONCAT(
                                -- Limit
                                CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
                                CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
                                CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
                                -- TOP
                                CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Cabang
                                CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Prinsipal
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
                                -- Status Stok
                                IFNULL(alasan_status,'')  
                            ) AS MonitoringStatus                           
                    FROM trs_sales_order 
                    LEFT JOIN (SELECT NoDO,NoSO FROM `trs_delivery_order_sales` WHERE Cabang='".$this->cabang."') a ON trs_sales_order.`NoSO`=a.NoSO 
                    WHERE Cabang = '".$this->cabang."' $byStatus $search
                    ORDER BY TimeSO DESC,NoSO ASC $limit ");
        }
        return $query;
    }

    public function prosesDataSO($no = NULL,$do=null)
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
                // // foreach($query as $r) { // loop over results
                //    if($status == 'Reject'){
                //     $this->db2->set('Status', "Reject");
                //   }elseif($status == 'Closed') {
                //     $this->db2->set('Status', "Closed");
                //     $this->db2->set('NoDO', $do);
                //   }else{
                //     $this->db2->set('Status', $status);
                //   }
                //     $this->db2->where('NoSO', $no);
                //     $this->db2->update('trs_sales_order_detail');
                // // }
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
                // if($status == 'Reject'){
                //     $this->db2->set('Status', "Reject");
                //   }elseif($status == 'Closed') {
                //     $this->db2->set('Status', "Closed");
                //     $this->db2->set('NoDO', $do);
                //   }else{
                //     $this->db2->set('Status', $status);
                //   }
                // $this->db2->where('NoSO', $no);
                // $this->db2->update('trs_sales_order');
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

    public function dataSO($no = NULL)
    {
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and trs_sales_order.NoSO = '".$no."'";
        }
        $query = $this->db->query("select trs_sales_order.StatusTOP,trs_sales_order.StatusLimit,trs_sales_order.StatusDiscCab,trs_sales_order.StatusDiscPrins,trs_sales_order_detail.* from trs_sales_order_detail join trs_sales_order on  trs_sales_order.NoSO = trs_sales_order_detail.NoSO and  trs_sales_order.Cabang = trs_sales_order_detail.Cabang where trs_sales_order.Cabang = '".$this->cabang."' $byNo ")->result();

        return $query;
    }

    public function setStok($NoDO = NULL)
    {  
        $status=true;            
        $produk = $this->db->query("select KodeProduk, Harga from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $data = $this->db->query("select KodeProduk, QtyDO , BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,TotalCOGS,COGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'");
        $numdata=$data->num_rows();
        $detail=$data->result();
        if($numdata <=0){
            $status=false;
        }
        if($status==true){
                foreach ($produk as $kunci => $nilai) {
                    $summary = 0;
                    $product1 = $produk[$kunci]->KodeProduk;
                    $harga = $produk[$kunci]->Harga;
                    if (!empty($product1)) {
                        $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                        foreach ($detail as $key => $value) {
                            $product2 = $detail[$key]->KodeProduk;
                            if ($product1 == $product2) {
                                $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                                $valuestok = $detail[$key]->TotalCOGS;
                            }
                        }
                        $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                        if ($invsum->num_rows() > 0) {
                            $invsum = $invsum->row();  
                            $UnitStok = $invsum->UnitStok - $summary;
                            $valuestok = $UnitStok * $invsum->UnitCOGS;
                            $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                        }
                        // else{
                        //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                        // }
                    }
                }

                // save inventori history
                foreach ($detail as $key => $value) {   
                    $produk = $detail[$key]->KodeProduk;        
                    if (!empty($produk)) {
                        $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                        $valuestok = $detail[$key]->Gross;
                        $UnitStok = $detail[$key]->QtyDO + $detail[$key]->BonusDO;

                        $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', '-')");
                    }
                }

                // save inventori detail
                foreach ($detail as $key => $value) { 
                    $produk = $detail[$key]->KodeProduk;  
                    if (!empty($produk)) {
                        $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                        $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                        $dt = $invdet->row();
                        if ($invdet->num_rows() > 0) {
                            $stok = $dt->UnitStok;
                            $UnitStok = $stok - ($detail[$key]->QtyDO + $detail[$key]->BonusDO );
                            $cogs = $dt->UnitCOGS;
                            $ValueStok = $invsum->UnitCOGS * $UnitStok;
                            $this->db->set("UnitStok", $UnitStok);
                            $this->db->set("ValueStok", $ValueStok);
                            $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                            $this->db->set("ModifiedUser", $this->session->userdata('username'));
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang", $this->cabang);
                            $this->db->where("NoDokumen", $detail[$key]->NoBPB);
                            $this->db->where("BatchNo", $detail[$key]->BatchNo);
                            $this->db->where("Gudang", 'Baik');
                            $valid = $this->db->update('trs_invdet');
                            $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                            if($invdet->num_rows <= 0){
                                $this->db->set("ValueStok", 0);
                                $this->db->where("KodeProduk", $produk);
                                $this->db->where("Gudang", 'Baik');
                                $this->db->where("Tahun", date('Y'));
                                $this->db->where("Cabang",$this->cabang);
                                $valid = $this->db->update('trs_invsum');
                            }else{
                                $invdet = $invdet->row();
                                $this->db->set("ValueStok", $invdet->sumval);
                                // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                                $this->db->where("KodeProduk", $produk);
                                $this->db->where("Gudang", 'Baik');
                                $this->db->where("Tahun", date('Y'));
                                $this->db->where("Cabang",$this->cabang);
                                $valid = $this->db->update('trs_invsum');
                            }
                        }
                    }
                }

        }
        

    }

     public function updateDataSOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoSO from trs_sales_order where Cabang = '".$this->cabang."' and Status ='Usulan'")->result();
                foreach ($nomor as $no) {
                    $query = $this->db2->query("select * from trs_sales_order_detail where NoSO = '".$no->NoSO."' and Status ='Usulan' ")->result();
                    foreach($query as $r) { // loop over results
                        $this->db->where('KodeProduk', $r->KodeProduk);
                        $this->db->where('Status', 'Usulan');
                        $this->db->where('NoSO', $no->NoSO);
                        $this->db->update('trs_sales_order_detail', $r); // insert each row to another table
                    }

                    $query2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no->NoSO."' and Status ='Usulan'")->row();
                    $this->db->where('NoSO', $no->NoSO);
                    $this->db->where('Status', 'Usulan');
                    $this->db->where('statusPusat', 'Berhasil');
                    $this->db->update('trs_sales_order', $query2); // insert each row to another table
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function rejectDataSO($NoSO = NULL)
    {
        $this->db->set("Status", "Reject");
        $this->db->set("statusPusat", "Gagal");
        $this->db->set("alasan_status", "SO Batal");
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->session->userdata('username'));
        $this->db->where('NoSO', $NoSO);
        $valid = $this->db->update('trs_sales_order');

        $this->db->set("Status", "Reject");
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->session->userdata('username'));
        // $this->db->set("statusPusat", "Gagal");
        // $this->db->set("alasan_status", "SO Batal");
        $this->db->where('NoSO', $NoSO);
        $valid = $this->db->update('trs_sales_order_detail');

        return $valid;
    }

    public function backStok($NoDO = NULL)
    {    
        $status=true;          
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $data = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'");
        $numdata = $data->num_rows();
        $detail = $data->result();
        if($numdata <= 0){
            $status=false;
        }
        if($status==true){
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->KodeProduk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    foreach ($detail as $key => $value) {
                        $product2 = $detail[$key]->KodeProduk;
                        if ($product1 == $product2) {
                            $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                            $valuestok = $detail[$key]->Gross;
                        }
                    }
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok + $summary;
                        $valuestok = $invsum->ValueStok + $detail[$key]->TotalCOGS;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    }
                    // else{
                    //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                    // }
                }
            }

            // save inventori history
            foreach ($detail as $key => $value) {   
                $produk = $detail[$key]->KodeProduk;        
                if (!empty($produk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                    $valuestok = $detail[$key]->TotalCOGS;
                    $UnitStok = $detail[$key]->QtyDO+$detail[$key]->BonusDO;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
                }
            }

            // save inventori detail
            foreach ($detail as $key => $value) { 
                $produk = $detail[$key]->KodeProduk;  
                if (!empty($produk)) {
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                    $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                    $dt = $invdet->row();
                    if ($invdet->num_rows() > 0) {
                        $stok = $dt->UnitStok;
                        $UnitStok = $stok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                        $ValueStok = $dt->ValueStok + $detail[$key]->TotalCOGS;

                        $this->db->set("UnitStok", $UnitStok);
                        $this->db->set("ValueStok", $ValueStok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("KodeProduk", $produk);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Gudang", "Baik");
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("NoDokumen", $detail[$key]->NoBPB);
                        $this->db->where("BatchNo", $detail[$key]->BatchNo);
                        $valid = $this->db->update('trs_invdet');
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db->set("ValueStok", 0);
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Gudang", 'Baik');
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }else{
                            $invdet = $invdet->row();
                            $this->db->set("ValueStok", $invdet->sumval);
                            $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Gudang", 'Baik');
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }
                    }
                    // else{
                    //     $valuestok = ($detail[$key]->QtyDO  + $detail[$key]->BonusDO) * $detail[$key]->Harga;                
                    //     $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$detail[$key]->QtyDO + $detail[$key]->BonusDO."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$detail[$key]->Harga."','".$detail[$key]->NoBPB."')");
                    // }
                }
            }
        }
    }

    public function cekStok($produk=null,$Batch=null,$batchDoc=null,$qty=null)
    {
        $result = TRUE;
        $s = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$Batch."' and NoDokumen='".$batchDoc."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
        $stok = $s->UnitStok;
        // if ($qty > $stok) {
        //     $result = FALSE;
        // }
        return $stok;
    }
    public function updateStok($nodo=null,$produk=null,$Batch=null,$batchDoc=null,$qty=null,$harga=null,$gross=null,$expdate=null)
    {  
        $summary = 0;
        if (!empty($produk)) {
            $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
            $summary = $summary + $qty;
            $valuestok = $gross;
            $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
            if ($invsum->num_rows() > 0) {
                $invsum = $invsum->row();  
                $UnitStok = $invsum->UnitStok - $summary;
                if($UnitStok == 0){
                    $valuestok = 0;
                }else{
                    $valuestok = $invsum->ValueStok - $gross;
                }
                $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
            }
                        
        }      
        if(!empty($produk)) {
            $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
            $valuestok = $gross;
            $UnitStok = $qty;

            $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$Batch."', '".$expdate."', 'Baik', 'DO', '".$nodo."', '-')");
        }
        // save inventori detail
        if (!empty($produk)) {
            $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
            $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$Batch."'  and Tahun = '".date('Y')."' and NoDokumen='".$batchDoc."' and Gudang='Baik' limit 1");
            $dt = $invdet->row();
            if ($invdet->num_rows() > 0) {
                $stok = $dt->UnitStok;
                $UnitStok = $stok - $qty;
                $cogs = $dt->UnitCOGS;
                if($UnitStok == 0){
                    $ValueStok = 0;
                }else{
                    $ValueStok = $invsum->ValueStok - $gross;
                }
                $this->db->set("UnitStok", $UnitStok);
                $this->db->set("ValueStok", $ValueStok);
                $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->where("KodeProduk", $produk);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("Cabang", $this->cabang);
                $this->db->where("NoDokumen", $batchDoc);
                $this->db->where("BatchNo", $Batch);
                $this->db->where("Gudang", 'Baik');
                $valid = $this->db->update('trs_invdet');
                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $this->db->set("ValueStok", $invdet->sumval);
                    // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }
            }
        }            
    }

    public function listsoulang()
    {   
        $query = $this->db->query("select NoSO,TglSO,Pelanggan from trs_sales_order where Cabang = '".$this->cabang."'  and alasan_status like '%Terpenuhi%' and NoSO NOT IN (select Acu from trs_sales_order where Cabang = '".$this->cabang."') order by NoSO asc")->result();

        return $query;
    }
    public function getdataorder($noso=null)
    {   
        $query = $this->db->query("select trs_sales_order.ppn_pelanggan,trs_sales_order_detail.* from trs_sales_order 
                                    join trs_sales_order_detail
                                    on trs_sales_order.Cabang = trs_sales_order_detail.Cabang and 
                                       trs_sales_order.NoSO = trs_sales_order_detail.NoSO
                                     where trs_sales_order.Cabang = '".$this->cabang."' and 
                                           trs_sales_order.NoSO = '".$noso."'  and 
                                           (trs_sales_order.alasan_status like '%Terpenuhi%' or ifnull(Approve_BM,'N') = 'R' or  ifnull(Approve_TOP_BM,'N') = 'R' or ifnull(Approve_DiscCab_BM,'N') = 'R' or ifnull(Approve_DiscPrins_BM,'N') = 'R')
                                    order by trs_sales_order.NoSO asc")->result();
        return $query;
    }

    public function datapiutangPelanggan($kode = NULL)
    {   
        $header = $this->db->query("select mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.Umur_faktur
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur 
                                      where (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0) and 
                                        trs_faktur.Status != 'Usulan'
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$kode."'")->result();

        $query = $this->db->query("SELECT DISTINCT trs_faktur.`NoFaktur`,
                                           trs_faktur.`TglFaktur`,
                                           (case when trs_faktur.TipeDokumen ='Faktur' then 'F'
                                                 when trs_faktur.TipeDokumen ='Retur' then 'R'
                                                 when trs_faktur.TipeDokumen ='CN' then 'CN'
                                                 when trs_faktur.TipeDokumen ='DN' then 'DN' 
                                                 else '' end ) as 'TipeDokumen',
                                           trs_faktur.`TglJtoFaktur`,
                                           CONCAT(trs_faktur.`Pelanggan`,' - ',trs_faktur.`NamaPelanggan`) AS 'Pelanggan',
                                           trs_faktur.`AlamatFaktur`,
                                           trs_faktur.`CaraBayar`,
                                           DATEDIFF(curdate(),trs_faktur.`TglFaktur`) AS 'Umur_faktur',
                                           trs_faktur.`Total`,
                                          (case when trs_faktur.`Saldo` = trs_faktur.`Total` then trs_faktur.`Saldo` 
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) != ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + trs_faktur.saldo_giro
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) = ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + ifnull(giro.bayargiro,0) else trs_faktur.`Saldo`
                                                end ) as 'Saldo',
                                           IFNULL(pelunasan.bayar,0) AS 'pelunasan',
                                           giro.giro AS 'Nogiro',
                                           giro.TglGiro AS 'TglGiro',
                                           giro.tglJTO AS 'tglJTO',
                                           ifnull(giro.bayargiro,0) as 'bayargiro'
                                     FROM  trs_faktur LEFT JOIN 
                                        ( SELECT trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`,
                                             SUM(trs_pelunasan_detail.`ValuePelunasan`) AS 'bayar'
                                          FROM trs_pelunasan_detail
                                          where  trs_pelunasan_detail.status != 'Batal'
                                          GROUP BY trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`) AS pelunasan ON pelunasan.NomorFaktur = trs_faktur.`NoFaktur` AND pelunasan.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                                    trs_pelunasan_giro_detail.Status,
                                           trs_pelunasan_giro_detail.`TglPelunasan` as 'TglGiro',
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            trs_pelunasan_giro_detail.`Giro`,
                                            trs_pelunasan_giro_detail.`ValuePelunasan` AS 'bayargiro',
                                            trs_giro.tglJTO as 'tglJTO'
                                         FROM trs_pelunasan_giro_detail left join 
                                              trs_giro on trs_pelunasan_giro_detail.`Giro` = trs_giro.Nogiro
                                         where trs_pelunasan_giro_detail.Status != 'Closed'
                                          ) AS giro ON giro.NomorFaktur = trs_faktur.`NoFaktur` AND giro.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(ifnull(trs_pelunasan_giro_detail.`ValuePelunasan`,0)) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON sumgiro.NomorFaktur = trs_faktur.`NoFaktur` AND sumgiro.KodePelanggan = trs_faktur.`Pelanggan` 
                                    WHERE (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0)  AND 
                                       trs_faktur.Status != 'Usulan' and
                                       trs_faktur.`Pelanggan` = '".$kode."'
                                    order by Umur_faktur Desc")->result();
        $data = array("query" =>$query, "header" =>$header);

        return $data;
    }
    public function saveDataUsulanDiskonPrins($params = null)
    {
        $valid = false;
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Usulan Diskon Prins');
        $data = $this->db->query("select max(right(No_Usulan,7)) as 'no' from mst_usulan_disc_prinsipal where Cabang = '".$this->cabang."' and YEAR(tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == "" ){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        $xpldprins = explode("~", $params->prinsipal);
        $kodePrins = $xpldprins[0];
        $namaPrins = $xpldprins[1];

        if($params->jmlpelanggan == 'All'){
            $kodePelanggan = '';
            $namaPelanggan = '';
        }else{
            $xpldPelanggan = explode("-", $params->pelangganMask);
            $kodePelanggan = $xpldPelanggan[0];
            $namaPelanggan = $xpldPelanggan[1];
        }

        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $namaProduk = $expld[1]; 

                $this->db->set("cabang", $this->cabang);
                $this->db->set("tanggal", date("Y-m-d"));
                $this->db->set("time_trans", date("Y-m-d H:i:s"));
                $this->db->set("No_Usulan", $noDokumen);
                $this->db->set("Tipe_trans", $params->tipe_trans);
                $this->db->set("Prinsipal", $kodePrins);
                $this->db->set("Prinsipal2", $namaPrins);
                $this->db->set("Supplier", $params->Supplier);
                $this->db->set("KodeProduk", $produk);
                $this->db->set("NamaProduk", $namaProduk);
                $this->db->set("tglmulai", $params->tgl1);
                $this->db->set("tglakhir",$params->tgl2);
                $this->db->set("qty1", $params->qty1[$key]);
                $this->db->set("qty2", $params->qty2[$key]);
                $this->db->set("disc_prins1", $params->dscprins1[$key]);
                $this->db->set("disc_prins2", $params->dscprins2[$key]);
                $this->db->set("KodePelanggan", $kodePelanggan);
                $this->db->set("NamaPelanggan", $namaPelanggan);
                $this->db->set("status", "Open");
                $this->db->set("status_pelanggan", $params->jmlpelanggan);
                $this->db->set("created_by", $this->session->userdata('username'));
                $this->db->set("created_at", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("mst_usulan_disc_prinsipal");
                if($valid){
                    $this->prosesDataUsulanDiscPrins($noDokumen,$produk);
                }
            }
        } 
        return $valid;
    }
    public function prosesDataUsulanDiscPrins($no = NULL,$produk=NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        
        $query = $this->db->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->result(); 
        $query2 = $this->db->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->row(); 
        // $status = $query2->Status;    
        if ($this->db2->conn_id == TRUE) {
            $cek = $this->db2->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->num_rows();
            if ($cek == 0) {
                $this->db2->insert('mst_usulan_disc_prinsipal', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('No_Usulan', $no);
                $this->db2->update('mst_usulan_disc_prinsipal', $query2);
            }
            $update = $this->db->query("update mst_usulan_disc_prinsipal set statusPusat = 'Berhasil' where No_Usulan = '".$no."'");
            return TRUE;
        }
        else{
            $this->db->query("update mst_usulan_disc_prinsipal set statusPusat = 'Gagal' where No_Usulan = '".$no."'");
            return FALSE;
        }
    }

    public function listDataApprovalDP($search=null,$byLimit=null,$cek=null)
    {
        $byStatus = "";
        
        if($cek == 'all'){
            $byStatus = " and ifnull(Status,'') in ('Open','Terpakai')";
        }else{
            if($this->group == "BM"){
            $byStatus = " and ifnull(Approved_BM,'') = '' and ifnull(Status,'') != 'Terpakai'";
            }else{
                $byStatus = " and ifnull(Status,'') in ('Open','Terpakai')";
            }
        }
        
         $query = $this->db->query("select * from mst_usulan_disc_prinsipal where (No_Usulan like '%".$search."%' or tanggal like '%".$search."%' or Prinsipal like '%".$search."%') $byStatus $byLimit");

        return $query;
    }
    public function prosesDataApprovalDP($NoUsulan=null,$KodeProduk=null,$status=null)
    {
       $this->db2 = $this->load->database('pusat', TRUE); 
        if ($this->group == "BM") {
            if($status =='Approve'){
                $approve_DiscPrins  = "Y";
            }else if($status =='Reject'){
                $approve_DiscPrins  = "R";
            }
            $date_approve_DiscPrins = date('Y-m-d H:i:s');
            $user_DiscPrins = $this->session->userdata('username');
            $update = $this->db->query("update mst_usulan_disc_prinsipal 
                                            set status = 'Open', 
                                                statusPusat = 'Berhasil',
                                                Approved_BM = '".$approve_DiscPrins."', 
                                                DateApprove_BM = '".$date_approve_DiscPrins."',
                                                modified_at = '".date('Y-m-d H:i:s')."',
                                                modified_by = '".$user_DiscPrins."',
                                                user_BM = '".$user_DiscPrins."' 
                                        where No_Usulan = '".$NoUsulan."' and 
                                              KodeProduk = '".$KodeProduk."' and 
                                              Cabang ='".$this->cabang."'"); 
                if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update mst_usulan_disc_prinsipal 
                                            set status = 'Open', 
                                                statusPusat = 'Berhasil',
                                                Approved_BM = '".$approve_DiscPrins."', 
                                                DateApprove_BM = '".$date_approve_DiscPrins."',
                                                modified_at = '".date('Y-m-d H:i:s')."',
                                                modified_by = '".$user_DiscPrins."',
                                                user_BM = '".$user_DiscPrins."' 
                                        where No_Usulan = '".$NoUsulan."' and 
                                              KodeProduk = '".$KodeProduk."' and 
                                              Cabang ='".$this->cabang."'");
                    } 
                $message = "Sukses";                 
            }else{
                $update = false;
                $message = "no_Approve";
            }      
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function getProdukDiscPrins($kode = NULL)
    {   
        $num = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPF' and ifnull(status,'')='Open' and Cabang ='".$this->session->userdata('cabang')."'"); 
        $num_query = $num->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPL' and curdate() >= tglmulai and curdate() <= tglakhir and Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        }else{
            $query = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPF' and ifnull(status,'')='Open' and Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        } 
        return $query;
    }

    public function updateDataDiscPrinsPusat(){

        $this->db2 = $this->load->database('pusat', TRUE);   
    
        $disc = $this->db->query("select * from mst_usulan_disc_prinsipal where ifnull(Approved_prins,'') ='' and 
            (case when Tipe_trans = 'DPF' then ifnull(status,'') ='Open' else ifnull(status,'') in ('Open','Terpakai') end) ")->result();
        $No = "";
        if ($this->db2->conn_id == TRUE) {
            foreach ($disc as $list) {
               $No = $list->No_usulan;
               $KodeProduk = $list->KodeProduk;
               $disprins_pusat = $this->db2->query("select * from mst_usulan_disc_prinsipal where No_usulan = '".$list->No_usulan."' and Cabang ='".$this->cabang."' and KodeProduk ='".$KodeProduk."'");
               $num_prins  = $disprins_pusat->num_rows();
               $data_prins = $disprins_pusat->result();
               if($num_prins > 0){
                    $this->db->set('Status', 'Open');
                    $this->db->set('Approved_prins', $data_prins[0]->Approved_prins);
                    $this->db->set('DateApprove_prins', $data_prins[0]->DateApprove_prins);
                    $this->db->set('user_prins', $data_prins[0]->user_prins);
                    $this->db->where('No_usulan',$list->NoDokumen);
                    $this->db->where('KodeProduk',$list->KodeProduk);
                    $this->db->where('Cabang',$this->cabang);
                    $this->db->where('Status','Open');
                    $this->db->update('mst_usulan_disc_prinsipal'); 
               }
            }
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }

    }
}