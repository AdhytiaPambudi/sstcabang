<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_order extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->cabang = $this->session->userdata('cabang');
    }

    // Pelanggan
    public function Pelanggan()
    {   
        $query = $this->db->query("select Kode, Nama_Faktur, Alamat from mst_pelanggan where Cabang = '".$this->cabang."' AND (Nama_Faktur != '' AND Nama_Faktur IS NOT NULL AND Nama_Faktur != '-') ORDER BY Nama_Faktur, Kode")->result();

        return $query;
    }

    // Salesman
    public function Salesman()
    {   
        $query = $this->db->query("select Kode, Nama, Jabatan from mst_karyawan where Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' group by Kode order by Nama asc")->result();

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
        log_message("error",print_r($tipe,true));
        // INVSUM
        if($tipe == "Merah"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and 
                       flag_dot in ('0','1','2','3','4')
                 order by NamaProduk asc")->result();
            log_message("error",print_r($query,true));
        }elseif($tipe == "Biru"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and 
                       flag_dot in ('0','1','2','3')
                 order by NamaProduk asc")->result();
            log_message("error",print_r($query,true));
        }elseif($tipe == "Hijau"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and 
                       flag_dot in ('0','1','2')
                 order by NamaProduk asc")->result();
            log_message("error",print_r($query,true));
        }elseif($tipe == "Jamu"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and 
                       flag_dot in ('0','1')
                 order by NamaProduk asc")->result();
            log_message("error",print_r($query,true));
        }elseif($tipe == "Non Dot"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and 
                       flag_dot in ('0')
                 order by NamaProduk asc")->result();
            log_message("error",print_r($query,true));
        }
        // $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk from trs_invsum where Cabang = '".$this->cabang."' and Gudang = 'Baik' order by NamaProduk asc")->result();
        // MST PRODUK
        // $query = $this->db->query("select Kode_Produk, Produk from mst_produk order by Produk asc")->result();
        // log_message("error",print_r($query,true));
        return $query;
    }

    // Data Pelanggan
    public function dataPelanggan($kode = NULL)
    {   
        //$query = $this->db->query("select * from mst_pelanggan a , (select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan) b where a.Kode = '".$kode."' and a.Cabang = '".$this->cabang."' limit 1")->row();

        $query = $this->db->query("SELECT * FROM mst_pelanggan a LEFT JOIN (SELECT SUM(saldo) AS saldopiutang,Pelanggan,Cabang FROM trs_faktur WHERE Pelanggan = '".$kode."' AND Cabang = '".$this->cabang."' GROUP BY cabang,pelanggan) b ON a.Kode=b.Pelanggan AND a.`Cabang`=b.Cabang LEFT JOIN (SELECT SUM(total) AS saldoorder,Pelanggan,Cabang FROM trs_sales_order WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS NOT LIKE '%close%' GROUP BY cabang,pelanggan) c ON a.Kode=c.Pelanggan AND a.`Cabang`=c.Cabang LEFT JOIN (SELECT SUM(total) AS saldoDlvorder,Pelanggan,Cabang FROM trs_delivery_order_sales WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS NOT LIKE '%close%' GROUP BY cabang,pelanggan) d ON a.Kode=d.Pelanggan AND a.`Cabang`=d.Cabang WHERE a.Kode = '".$kode."' AND a.Cabang = '".$this->cabang."' LIMIT 1")->row(); 


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

    // Batch No
    public function Batch($kode = NULL)
    {   
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate, NoDokumen as NoBPB from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and UnitStok > 0 and Gudang = 'Baik' order by NoDokumen,ExpDate asc")->result();

        return $query;
    }

    // Harga Produk
    public function getProdukBuatOrder($kode = NULL)
    {   
        $query = $this->db->query("select HNA, 
                                          Dsc_Cab, 
                                          Dsc_Pri 
                                   from mst_harga 
                                   where Produk = '".$kode."' and 
                                        Cabang = (case when ifnull(Cabang,'') != '' then '".$this->cabang."' else '' end) limit 1")->row();
        return $query;
    }

    // Satuan Produk
    public function getSatuanBuatOrder($kode = NULL)
    {   
        $query = $this->db->query("select a.Satuan, b.UnitStok, b.UnitCOGS from mst_produk a, trs_invsum b where a.Kode_Produk = '".$kode."' and a.`Kode_Produk` = b.`KodeProduk` and b.Gudang = 'Baik' limit 1")->row();
        return $query;
    }

    public function saveDataSalesOrder($params = null)
    {
        // // Set no dokumen
        // $NoSO = $this->Model_main->noDokumen('SO');
        // // Update no dokumen
        // $this->Model_main->saveNoDokumen('SO', substr($NoSO, -7));
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('SO');
        $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        $dataPelanggan = $this->dataPelanggan($params->pelanggan);
        $dataSalesman = $this->dataSalesman($params->salesman);   
        $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

        $status = "Closed";
        $statusTOP = "Ok";
        $statusLimit = "Ok";
        $statusDiscCab = "Ok";
        $statusDiscPrins = "Ok";
        $do = true;
        $totalPiutang = $params->total + $params->piutang;
        $xPelanggan = explode("-", $params->pelanggan);
        $Pelanggan = $xPelanggan[0];
        $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
        $data = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status not like '%Lunas%' order by TglFaktur desc")->result();

        foreach ($data as $key) {
            if (strtotime($key->TglFaktur) < strtotime($tglTOP)) {
                $status = "Usulan";
                $statusTOP = "TOP";
                $do = false;
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "TOP");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
                break;
            }
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
        $i=0;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
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

                // tambahan
                $unCogs = $params->cogsval[$key];

                $gross = ($jml  + ($bnscab + $bnsprins)) * $harga;
                $stotCogs = ($jml  + ($bnscab + $bnsprins)) * $unCogs;
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
                
                $potongan = $dscTot + $bnsTot;     
                $value = $gross - $potongan;
                $ppn = $value * 0.1;
                $TotValue = $value + $ppn; 

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
                $this->db->set("TglJtoOrder", "");
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
                $this->db->set("DiscCabMax", $params->dsccab[$key]);
                $this->db->set("KetDiscCabMax", "");
                $this->db->set("DiscPrinsMax", $params->dscprins1[$key]);
                $this->db->set("KetDiscPrinsMax", "");
                $this->db->set("COGS", $params->cogsval[$key]);
                $this->db->set("TotalCOGS", $stotCogs);
                $valid =  $this->db->insert("trs_sales_order_detail");
            }
        }

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
        $this->db->set("CashDiskon", $params->cashdiskon);
        $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
        $this->db->set("TOP", $dataPelanggan->TOP);
        $this->db->set("TglJtoOrder", "");
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
        $this->db->set("Gross", $params->grosir);
        $this->db->set("Potongan", $params->potongan);
        $this->db->set("Value", $params->value);
        $this->db->set("Ppn", $params->ppn);
        $this->db->set("LainLain", "");
        $this->db->set("Materai", $params->materai);
        $this->db->set("OngkosKirim", $params->ongkir);
        $this->db->set("Total", $params->total);
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
        $valid =  $this->db->insert("trs_sales_order");
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

        $NoDO = "";
        $val = true;
        foreach ($detail as $key => $value) 
        {
            $batch = $this->Batch($detail[$key]->KodeProduk);
            log_message("error",print_r($batch));
            if (count($batch) == 0) {
                // $this->db->set('NoDO', $noDokumen);
                $this->db->set('Status', "Usulan");
                $this->db->set('alasan_status', "Stok Barang Kosong");
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');
                log_message("error","no batch");
                $val = false;
                break;

            }
        }
        if ($val == true) {
            $dataPelanggan = $this->dataPelanggan($header->Pelanggan);
            $dataSalesman = $this->dataSalesman($header->Salesman);
            
            if (empty($NoDO)) {
                // // Set no dokumen
                // $NoDO = $this->Model_main->noDokumen('DO');
                // // Update no dokumen
                // $this->Model_main->saveNoDokumen('DO', substr($NoDO, -7));
                //================ Running Number ======================================//
                $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                $kodeDokumen = $this->Model_main->kodeDokumen('DO');
                $data = $this->db->query("select max(right(NoDO,7)) as 'no' from trs_delivery_order_sales where substr(NoDO,5,2) = '06' and Cabang = '".$this->cabang."' and length(NoDO) = 13")->result();
                if(empty($data[0]->no) || $data[0]->no == ""){
                  $lastNumber = 1000001;
                }else {
                  $lastNumber = $data[0]->no + 1;
                }
                $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                //================= end of running number ========================================//
            }

            $totgross = 0;
            $totpotongan = 0;
            $totvalue = 0;
            $totcashdiskon = 0;
            $totppn = 0;
            $summary = 0;
            $totCogs = 0;
            $i=0;
            foreach ($detail as $key => $value) 
            {   
                if (!empty($detail[$key]->KodeProduk)) 
                {
                    $batch = $this->Batch($detail[$key]->KodeProduk);          
                    $QtySO = $detail[$key]->QtySO;
                    foreach ($batch as $kunci => $nilai) {
                        $i++;
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
                        $this->db->set("ValueCashDiskon", $header->ValueCashDiskon);
                        $this->db->set("TOP", $dataPelanggan->TOP);
                        $this->db->set("TglJtoOrder", "");
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
                        // DETAIL PRODUK
                        $this->db->set("KodeProduk", $detail[$key]->KodeProduk);
                        $this->db->set("NamaProduk", $detail[$key]->NamaProduk);
                        $this->db->set("UOM", $detail[$key]->UOM);
                        $this->db->set("Harga", $detail[$key]->Harga);
                        $this->db->set("QtySO", $QtySO);
                        $this->db->set("BonusSO", $detail[$key]->Bonus);
                        $this->db->set("BonusDO", $detail[$key]->Bonus);
                        $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                        $this->db->set("DiscCab", $detail[$key]->DiscCab);
                        // $this->db->set("DiscCab2", $params->dsccab2[$key]);                    
                        $this->db->set("DiscCabTot", "");
                        $this->db->set("ValueDiscCabTotal", "");
                        $this->db->set("DiscPrins1", $detail[$key]->DiscPrins1);
                        $this->db->set("DiscPrins2", $detail[$key]->DiscPrins2);
                        $this->db->set("DiscPrinsTot", "");
                        $this->db->set("ValueDiscPrinsTotal", "");
                        $this->db->set("DiscTotal", "");
                        $this->db->set("ValueDiscTotal", "");
                        $this->db->set("DiscCabMax", $detail[$key]->DiscCabMax);
                        $this->db->set("KetDiscCabMax", "");
                        $this->db->set("DiscPrinsMax", $detail[$key]->DiscPrinsMax);
                        $this->db->set("KetDiscPrinsMax", "");
                        $this->db->set("COGS", $detail[$key]->COGS);
                        $this->db->set("TotalCOGS", $detail[$key]->TotalCOGS);
                        if($detail[$key]->QtySO <= 0){
                            break;
                        }
                        if ($detail[$key]->QtySO  <= $batch[$kunci]->UnitStok) {    
                            $jml = $detail[$key]->QtySO;
                            $harga = $detail[$key]->Harga;
                            $dsccab = $detail[$key]->DiscCab;
                            $dscprins1 = $detail[$key]->DiscPrins1;
                            $dscprins2 = $detail[$key]->DiscPrins2;
                            $bns = $detail[$key]->Bonus;

                            // tambahan
                            $unCogs = $detail[$key]->COGS;

                            $gross = ($jml  + $bns) * $harga;
                            $stotCogs = ($jml  + $bns) * $unCogs;
                            $diskoncab = ($dsccab)/100;

                            $dsccab = ($harga * ($jml)) * (($diskoncab)); 
                            $boncab = ($bns * $harga);

                            $diskonprins1 = ($dscprins1)/100;
                            $diskonprins2 = ($dscprins2)/100;
                            $diskonprins = $diskonprins1 + $diskonprins2;
                            $dscprins1 = ($harga * ($jml)) * (($diskonprins1));
                            $dscprins2 = ($harga * ($jml)) * (($diskonprins2));
                            $dscprins = ($dscprins1) + ($dscprins2);
                            $bonprins = (($bns) * ($harga));

                            $dscTot = $dsccab + $dscprins;
                            $bnsTot = $boncab + $bonprins;
                            
                            $potongan = $dscTot + $bnsTot;     
                            $value = $gross - $potongan;
                            $ppn = $value * 0.1;
                            $TotValue = $value + $ppn; 

                            $totgross = $totgross + $gross;
                            $totpotongan = $totpotongan + $potongan;
                            $totvalue = $totvalue + $value;
                            $totppn = $totppn + $ppn;
                            $summary = $summary + $totppn;
                            $totCogs = $totCogs + $stotCogs;

                            // $gross = $jml * $harga;     
                            // $diskoncab = $dsccab/100;
                            // $diskonprins1 = $dscprins1/100;
                            // $diskonprins2 = $dscprins2/100;
                            // $diskonprins = $diskonprins1 + $diskonprins2;
                            // $dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                            // $dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                            // $dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                            // $dscprins = $dscprins1 + $dscprins2;
                            // $bonus = ( $bns * $harga) - ( $bns * $harga * $diskoncab);
                            // $potongan = $bonus + $dsccab + $dscprins;     
                            // $value = $gross - $potongan;
                            // $ppn = ($value) * ( 10 / 100 );
                            // $TotValue = $value + $ppn; 
                            $this->db->set("ValueDiscCab", $dsccab);
                            $this->db->set("ValueDiscPrins1", $dscprins1);
                            $this->db->set("ValueDiscPrins2", $dscprins2);
                            $this->db->set("QtyDO", $jml);
                            $this->db->set("Gross", $gross);
                            $this->db->set("Potongan", $potongan);
                            $this->db->set("Value", $value);
                            $this->db->set("Ppn", $ppn);
                            $this->db->set("LainLain", "");
                            $this->db->set("Total", $TotValue);
                            $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                            $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                            $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                            $valid =  $this->db->insert("trs_delivery_order_sales_detail");

                            $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                            $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                            $this->db->where('NoSO', $NoSO);
                            $this->db->where('KodeProduk', $detail[$key]->KodeProduk);
                            $this->db->update('trs_sales_order_detail');
                            break;
                        }
                        else{
                            $jml = $batch[$kunci]->UnitStok;
                            $harga = $detail[$key]->Harga;
                            $dsccab = $detail[$key]->DiscCab;
                            $dscprins1 = $detail[$key]->DiscPrins1;
                            $dscprins2 = $detail[$key]->DiscPrins2;
                            $bns = $detail[$key]->Bonus;

                            $gross = $jml * $harga;     
                            $diskoncab = $dsccab/100;
                            $diskonprins = $dscprins2/100;
                            $dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                            $dscprins = ( $harga * $jml ) * ( $diskonprins );
                            $bonus = ( $bns * $harga) - ( $bns * $harga * $diskoncab);
                            $potongan = $bonus + $dsccab + $dscprins;    
                            $value = $gross - $potongan;
                            $ppn = ($value) * ( 10 / 100 );
                            $TotValue = $value + $ppn;

                            $this->db->set("ValueDiscCab", $dsccab);
                            $this->db->set("ValueDiscPrins1", $dscprins1);
                            $this->db->set("ValueDiscPrins2", $dscprins);
                            $this->db->set("QtyDO", $jml);
                            $this->db->set("Gross", $gross);
                            $this->db->set("Potongan", $potongan);
                            $this->db->set("Value", $value);
                            $this->db->set("Ppn", $ppn);
                            $this->db->set("LainLain", "");
                            $this->db->set("Total", $TotValue);
                            $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                            $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                            $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                            $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                            $detail[$key]->QtySO = $detail[$key]->QtySO - $batch[$kunci]->UnitStok;

                            $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                            $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                            $this->db->where('NoSO', $NoSO);
                            $this->db->where('KodeProduk', $detail[$key]->KodeProduk);
                            $this->db->update('trs_sales_order_detail');
                        }
                    }
                }
            }

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
            $this->db->set("TOP", $dataPelanggan->TOP);
            $this->db->set("TglJtoOrder", "");
            $this->db->set("Salesman", $dataSalesman->Kode);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
            $this->db->set("Status", 'Open');
            $this->db->set("TipeDokumen", "DO");
            $this->db->set("Gross", $header->Gross);
            $this->db->set("Potongan", $header->Potongan);
            $this->db->set("Value", $header->Value);
            $this->db->set("Ppn", $header->Ppn);
            $this->db->set("LainLain", "");
            $this->db->set("Materai", $header->Materai);
            $this->db->set("OngkosKirim", $header->OngkosKirim);
            $this->db->set("Total", $header->Total);
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
            $valid =  $this->db->insert("trs_delivery_order_sales");

            $this->db->set('NoDO', $noDokumen);
            $this->db->set('Status', "Usulan");
            $this->db->set('alasan_status', "DO Sudah Di Proses");
            $this->db->where('NoSO', $NoSO);
            $this->db->update('trs_sales_order');

            $this->db->set('NoDO', $noDokumen);
            $this->db->set('Status', "Usulan");
            $this->db->where('NoSO', $NoSO);
            $this->db->update('trs_sales_order_detail');
            if($valid){
                $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'DO' and Cabang = '".$this->cabang."'");
                $this->setStok($noDokumen);
            }
            
        }

        return $noDokumen;
    }

    public function listDataSO($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and Status = '".$status."'";
        }
        $query = $this->db->query("select * from trs_sales_order where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TimeSO DESC,NoSO ASC $limit ");


        return $query;
    }

    public function prosesDataSO($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        $update = $this->db->query("update trs_sales_order set statusPusat = 'Berhasil' where NoSO = '".$no."'");
        $query = $this->db->query("select * from trs_sales_order_detail where NoSO = '".$no."'")->result(); 
        $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row();    
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

            return TRUE;
        }
        else{
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
        $produk = $this->db->query("select KodeProduk, Harga from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO , BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
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
                    $UnitStok = $invsum->UnitStok - $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($detail as $key => $value) {   
            $produk = $detail[$key]->KodeProduk;        
            if (!empty($produk)) {
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $detail[$key]->Gross;
                $UnitStok = $detail[$key]->QtyDO;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', '-')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and ExpDate='".$detail[$key]->ExpDate."' and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
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
                    $this->db->where("ExpDate", $detail[$key]->ExpDate);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                }
            }
        }

    }

    public function updateDataSOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoSO from trs_sales_order where Cabang = '".$this->cabang."'")->result();
                foreach ($nomor as $no) {
                    $query = $this->db2->query("select * from trs_sales_order_detail where NoSO = '".$no->NoSO."'")->result();
                    foreach($query as $r) { // loop over results
                        $this->db->where('KodeProduk', $r->KodeProduk);
                        $this->db->where('NoSO', $no->NoSO);
                        $this->db->update('trs_sales_order_detail', $r); // insert each row to another table
                    }

                    $query2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no->NoSO."'")->row();
                    $this->db->where('NoSO', $no->NoSO);
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
        $this->db->where('NoSO', $NoSO);
        $valid = $this->db->update('trs_sales_order');

        return $valid;
    }
}