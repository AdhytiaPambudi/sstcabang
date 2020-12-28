<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_kiriman extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->load->model('pembelian/Model_order');
    }

    public function listDataDO($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and Status in ('Open','Kembali')";
        }
        $query = $this->db->query("select trs_delivery_order_sales.*, trs_kiriman.NoKiriman as NoKiriman from trs_delivery_order_sales left join trs_kiriman on trs_kiriman.NoDO = trs_delivery_order_sales.NoDO where trs_delivery_order_sales.Cabang = '".$this->cabang."' $byStatus $search order by TimeDO DESC $limit");

        return $query;
    }

    public function listDataPicking($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and trs_delivery_order_sales.Status = '".$status."' and IFNULL(trs_delivery_order_sales_detail.picking_list,'') = ''";
        }
        // $query = $this->db->query("select * from trs_delivery_order_sales where Cabang = '".$this->cabang."' $byStatus $search order by TimeDO DESC $limit ");
        $query = $this->db->query("
                                select trs_delivery_order_sales.Cabang,
                                       trs_delivery_order_sales.NoDO,
                                       trs_delivery_order_sales.TglDO,
                                       trs_delivery_order_sales.NamaPengirim,
                                       trs_delivery_order_sales.Pelanggan,
                                       trs_delivery_order_sales.NamaPelanggan,
                                       trs_delivery_order_sales.AlamatKirim,
                                       trs_delivery_order_sales.Status,
                                       trs_delivery_order_sales_detail.noline,
                                       trs_delivery_order_sales_detail.KodeProduk,
                                       trs_delivery_order_sales_detail.NamaProduk,
                                       (ifnull(trs_delivery_order_sales_detail.QtyDO,0) + ifnull(trs_delivery_order_sales_detail.BonusDO,0)) as 'QtyDO',
                                       trs_delivery_order_sales_detail.UOM,
                                       trs_delivery_order_sales_detail.BatchNo,
                                       trs_delivery_order_sales_detail.picking_list
                                  from trs_delivery_order_sales join trs_delivery_order_sales_detail on 
                                       trs_delivery_order_sales.NoDO = trs_delivery_order_sales_detail.NoDO and 
                                       trs_delivery_order_sales.Cabang = trs_delivery_order_sales_detail.Cabang 
                                  where trs_delivery_order_sales.Cabang = '".$this->cabang."' $byStatus $search order by trs_delivery_order_sales.TimeDO DESC $limit
                                  ");

        return $query;
    }

    public function updatepickinglist($noDO=null,$line=null,$kode=null){
        $this->db->set("picking_list", 'Y');
        $this->db->where("NoDO", $noDO);
        $this->db->where("noline", $line);
        $this->db->where("KodeProduk", $kode);
        $valid = $this->db->update('trs_delivery_order_sales_detail'); 
        return $valid;
    }

    public function Pengirim()
    {   
        $query = $this->db->query("select Kode, Nama, Jabatan from mst_karyawan where Cabang = '".$this->cabang."' and Status = 'Aktif' and Mengirim = 'Ya'")->result();

        return $query;
    }

    public function listDataKiriman($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and StatusKiriman = '".$status."'";
        }
        $query = $this->db->query("select distinct NoKiriman,StatusKiriman,TglKirim,TglTerima,statusPusat from trs_kiriman where Cabang = '".$this->cabang."' $byStatus $search  Order by TimeKirim DESC $limit");

        return $query;
    }

    public function saveKiriman($NoDO = NULL, $NoKiriman = NULL, $pengirim = NULL)
    {   
        $query = $this->db->query("select * FROM trs_delivery_order_sales WHERE NoDO = '".$NoDO."' and Status = 'Open' limit 1");
        $valid = true;
        $data = $query->row();
        $num_data = $query->num_rows();
        if($num_data > 0){
            $query2 = $this->db->query("select * from trs_kiriman where NoDO = '".$NoDO."' and StatusKiriman != 'Kembali' limit 1");
            $kirim = $query2->num_rows();
            if($kirim < 1){
                $expld = explode("-", $pengirim);
                $Kode = $expld[0];
                $Nama = $expld[1];
                if($num_data > 0){
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("KodePelanggan", $data->Pelanggan);
                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("NoKiriman", $NoKiriman);        
                    $this->db->set("StatusKiriman", 'Open');
                    $this->db->set("StatusDO", 'Kirim');
                    $this->db->set("NoDO", $NoDO);
                    $this->db->set("TglKirim", date("Y-m-d"));
                    $this->db->set("TimeKirim", date("Y-m-d H:i:s")); 
                    $this->db->set("created_by", $this->user);
                    $this->db->set("created_at", date("Y-m-d H:i:s"));
                    $valid = $this->db->insert('trs_kiriman');

                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("Status", "Kirim");
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales');        

                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales_detail');
                }
            }

        }  
        return $valid;
    }

    public function listKiriman($search = NULL, $limit = NULL)
    {   
        $query = $this->db->query("select * from trs_kiriman where Cabang = '".$this->cabang."' and StatusKiriman = 'Open' $search group by NoKiriman $limit");

        return $query;
    }

    public function getTerimaKiriman($no = null)
    {
        $noDO = "";
        $krm = $this->db->query("select NoDO from trs_kiriman where NoKiriman = '".$no."' and StatusKiriman = 'Open'")->result();
        foreach ($krm as $kirim) {            
            $noDO .= "'".$kirim->NoDO."',";
        }
        $noDO = substr($noDO, 0, -1);
        $query = $this->db->query("select * from trs_delivery_order_sales where NoDO in (".$noDO.") order by NoDO asc")->result();
        return $query;
    }

    public function getDataDO($noDO = null)
    {   
        $query = $this->db->query("select a.*, b.UnitStok 
                                    from trs_delivery_order_sales_detail a, trs_invdet b 
                                    where a.NoDO = '".$noDO."' and 
                                          a.BatchNo = b.BatchNo and 
                                          a.ExpDate = b.ExpDate and 
                                          a.NoBPB = b.NoDokumen and
                                          a.KodeProduk = b.KodeProduk and 
                                          b.Gudang = 'Baik' and 
                                          b.Cabang = '".$this->cabang."'")->result();

        return $query;
    }

    // Data Pelanggan
    public function dataPelanggan($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_pelanggan where Kode = '".$kode."' limit 1")->row();

        return $query;
    }

    // Data Salesman
    public function dataSalesman($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_karyawan where Kode = '".$kode."' and Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' limit 1")->row();

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

    public function getBatch($kode = null)
    {   
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate from trs_invdet where Cabang = '".$this->cabang."' and KodeProduk = '".$kode."' and Gudang = 'Baik'")->result();
        return $query;
    }

    public function updateDataDO($params = null)
    {
        $h = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$params->noDO."' limit 1")->row();
        $this->backStok($params->noDO);

        $dataPelanggan = $this->dataPelanggan($params->pelanggan);
        $dataSalesman = $this->dataSalesman($params->salesman);        
        $NoDO = $params->noDO;
        $NoSO = $params->noSO;
        $totgross = 0;
        $totpotongan = 0;
        $totvalue = 0;
        $totcashdiskon = 0;
        $totppn = 0;
        $summary = 0;

        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {    
                $expld = explode("-", $params->produk[$key]);
                $KodeProduk = $expld[0];
                $NamaProduk = $expld[1];

                $jml = $params->jumlah[$key];
                $harga = $params->harga[$key];
                $dsccab = $params->dsccab[$key];
                $dscprins1 = $params->dscprins1[$key];
                $dscprins2 = $params->dscprins2[$key];
                $bnscab = $params->bnscab[$key];
                $bnsprins = $params->bnsprins[$key];

                $gross = $jml * $harga;     
                $diskoncab = $dsccab/100;
                $diskonprins1 = $dscprins1/100;
                $diskonprins2 = $dscprins2/100;
                $diskonprins = $diskonprins1 + $diskonprins2;
                $dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                $dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                $dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                $dscprins = $dscprins1 + $dscprins2;
                $boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
                $bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
                $potongan = $bonprins + $boncab + $dsccab + $dscprins;     
                $value = $gross - $potongan;
                $ppn = ($value) * ( 10 / 100 );
                $TotValue = $value + $ppn; 

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
                $this->db->set("NoSO", $NoSO);
                $this->db->set("NoFaktur", "");
                // DETAIL PRODUK
                $this->db->set("NamaProduk", $NamaProduk);
                $this->db->set("UOM", $params->uom[$key]);
                $this->db->set("Harga", $params->harga[$key]);
                $this->db->set("BonusSO", $params->bnscab[$key]);                  
                $this->db->set("QtyDO", $params->jumlah[$key]);
                $this->db->set("BonusDO", $params->bnscab[$key]);
                $this->db->set("ValueBonus", "");
                $this->db->set("DiscCab", $params->dsccab[$key]);
                $this->db->set("ValueDiscCab", $params->valuedsccab[$key]);
                $this->db->set("DiscCabTot", "");
                $this->db->set("ValueDiscCabTotal", "");
                $this->db->set("DiscPrins1", $params->dscprins1[$key]);
                $this->db->set("ValueDiscPrins1", $params->valuedscprins1[$key]);
                $this->db->set("DiscPrins2", $params->dscprins2[$key]);
                $this->db->set("ValueDiscPrins2", $params->valuedscprins2[$key]);
                $this->db->set("Pengirim", $h->Pengirim);
                $this->db->set("NamaPengirim", $h->NamaPengirim);

                $cek = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and KodeProduk = '".$KodeProduk."' and BatchNo = '".$params->batch[$key]."'")->num_rows();
                if (!empty($params->batchMask[$key])) {
                    $this->db->set("QtySO", $params->qtyso[$key]);
                    $this->db->set("BatchNo", $params->batch[$key]);
                    $this->db->set("ExpDate", $params->expdate[$key]);
                    $this->db->where("NoDO", $NoDO);
                    $this->db->where("KodeProduk", $KodeProduk);
                    $this->db->where("BatchNo", $params->batchMask[$key]);
                    $valid =  $this->db->update("trs_delivery_order_sales_detail");
                }
                else{           
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("TglDO", $params->tgl);
                    $this->db->set("TimeDO", date('Y-m-d H:i:s'));
                    $this->db->set("Pelanggan", $params->pelanggan);
                    $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                    $this->db->set("AlamatKirim", $dataPelanggan->Alamat);
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
                    $this->db->set("Status", "Open");
                    $this->db->set("TipeDokumen", "DO");
                    $this->db->set("QtySO", $jml);     
                    $this->db->set("NoDO", $NoDO);
                    $this->db->set("KodeProduk", $KodeProduk);
                    $this->db->set("BatchNo", $params->batch[$key]);
                    $this->db->set("ExpDate", date('Y-m-d'));
                    $this->db->set("DiscCabMax", $params->dsccab[$key]);
                    $this->db->set("DiscPrinsMax", $params->dscprins1[$key]);
                    $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                }
            }
        }

        $this->db->set("statusPusat", "Gagal");
        $this->db->set("Gross", $params->grosir);
        $this->db->set("Potongan", $params->potongan);
        $this->db->set("Value", $params->value);
        $this->db->set("Ppn", $params->ppn);
        $this->db->set("Materai", $params->materai);
        $this->db->set("Total", $params->total);
        $this->db->where("NoDO", $NoDO);
        $valid =  $this->db->update("trs_delivery_order_sales");

        $this->setStok($NoDO);

        return $NoDO;
    }

    public function saveTerimaKiriman($params = NULL)
    {   
        $NoFaktur = "";
        foreach ($params->NoDO as $key => $value) 
        {   
            // START SAVE TABEL KIRIMAN
            $this->db->set("StatusKiriman", 'Closed');
            $this->db->set("StatusDO", $params->Status[$key]);
            $this->db->set("Alasan", $params->Alasan[$key]);
            $this->db->set("TglTerima", date("Y-m-d"));
            $this->db->set("TimeTerima", date("Y-m-d H:i:s")); 
            $this->db->set("updated_by", $this->session->userdata('username'));
            $this->db->set("updated_at", date("Y-m-d H:i:s"));
            $this->db->where("NoKiriman", $params->NoKiriman);
            $this->db->where("NoDO", $params->NoDO[$key]);
            $valid = $this->db->update("trs_kiriman");
            // END SAVE TABEL KIRIMAN

            // START UPDATE STATUS DO
            if ($params->Status[$key] == "Terkirim") {
                // $kodeLamaCabang = $this->kodeLamaCabang();
                // $kodeDokumen = $this->kodeDokumen('Faktur');
                // $counter = $this->counter('Faktur');
                // if ($counter) 
                // {
                //     $no = $counter->Counter + 1;
                //     $this->db->query("update mst_counter set Counter = ".$no." where Aplikasi = 'Faktur' and Cabang = '".$this->cabang."' limit 1");
                // }
                // else
                // {
                //     $no = 1000001;
                //     $this->db->query("insert into mst_counter (`Aplikasi`, `Cabang`, `Counter`) values ('Faktur', '".$this->cabang."', ".$no.")");
                // }

                // $NoFaktur = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
                //================ Running Number ======================================//
                $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                $kodeDokumen = $this->Model_main->kodeDokumen('Faktur');
                $data = $this->db->query("select max(right(NoFaktur,7)) as 'no' from trs_faktur where TipeDokumen='Faktur'  and length(NoFaktur)= 13 and Cabang = '".$this->cabang."'")->result();
                if(empty($data[0]->no) || $data[0]->no == ""){
                  $lastNumber = 1000001;
                }else {
                  $lastNumber = $data[0]->no + 1;
                }
                $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                // $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($lastNumber,5,"0",STR_PAD_LEFT);
                //================= end of running number ========================================//
                $this->db->set('NoFaktur', $noDokumen);
                $this->db->set("Status", "Close");
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales');                 

                // START SAVE TABEL FAKTUR
                $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$params->NoDO[$key]."'")->result();
                $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$params->NoDO[$key]."'")->row();
                $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 

                $this->db->set('NoFaktur', $noDokumen);
                $this->db->where('NoSO', $header->NoSO);
                $this->db->update('trs_sales_order'); 

                // Start Header
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("NoFaktur", $noDokumen);
                $this->db->set("TglFaktur", date('Y-m-d'));
                $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
                $this->db->set("Pelanggan", $header->Pelanggan);
                $this->db->set("NamaPelanggan", $header->NamaPelanggan);
                $this->db->set("AlamatFaktur", $header->AlamatKirim);
                $this->db->set("TipePelanggan", $header->TipePelanggan);
                $this->db->set("NamaTipePelanggan", $header->NamaTipePelanggan);
                $this->db->set("NPWPPelanggan", $header->NPWPPelanggan);
                $this->db->set("NoPajak", "");
                $this->db->set("TipePajak", "");
                $this->db->set("KategoriPelanggan", $header->KategoriPelanggan);
                $this->db->set("Acu", $header->Acu);
                $this->db->set("CaraBayar", $header->CaraBayar);
                $this->db->set("CashDiskon", $header->CashDiskon);
                $this->db->set("ValueCashDiskon", $header->ValueCashDiskon);
                $this->db->set("TOP", $header->TOP);
                $this->db->set("TglJtoFaktur", $tglJTO);
                $this->db->set("Salesman", $header->Salesman);
                $this->db->set("NamaSalesman", $header->NamaSalesman);
                $this->db->set("Rayon", $header->Rayon);
                $this->db->set("NamaRayon", $header->NamaRayon);
                $this->db->set("Status", "Open");
                $this->db->set("TipeDokumen", "Faktur");
                $this->db->set("Gross", $header->Gross);
                $this->db->set("Potongan", $header->Potongan);
                $this->db->set("Value", $header->Value);
                $this->db->set("Ppn", $header->Ppn);
                $this->db->set("LainLain", $header->LainLain);
                $this->db->set("Materai", $header->Materai);
                $this->db->set("OngkosKirim", $header->OngkosKirim);
                $this->db->set("Total", $header->Total);
                $this->db->set("Saldo", $header->Total);
                $this->db->set("Keterangan1", $header->Keterangan1);
                $this->db->set("Keterangan2", $header->Keterangan2);
                $this->db->set("Barcode", $header->Barcode);
                $this->db->set("QrCode", $header->QrCode);
                $this->db->set("NoSO", $header->NoSO);
                $this->db->set("NoDO", $header->NoDO);
                $this->db->set("NoSP", $header->NoSP);
                $this->db->set("TipeFaktur", $header->TipeFaktur);
                $this->db->set("NoIDPaket", $header->NoIDPaket);
                $this->db->set("KeteranganTender", $header->KeteranganTender);
                $this->db->set("statusPusat", "Gagal");
                $valid =  $this->db->insert("trs_faktur");
                // End Header
                $i=0;
                foreach ($detail as $row) {
                    // Start Detail
                    $i++;
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("NoFaktur", $noDokumen);
                    $this->db->set("noline", $i);
                    $this->db->set("TglFaktur", date('Y-m-d'));
                    $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
                    $this->db->set("Pelanggan", $row->Pelanggan);
                    $this->db->set("NamaPelanggan", $row->NamaPelanggan);
                    $this->db->set("AlamatFaktur", $row->AlamatKirim);
                    $this->db->set("TipePelanggan", $row->TipePelanggan);
                    $this->db->set("NamaTipePelanggan", $row->NamaTipePelanggan);
                    $this->db->set("NPWPPelanggan", $row->NPWPPelanggan);
                    $this->db->set("KategoriPelanggan", $row->KategoriPelanggan);
                    $this->db->set("Acu", $row->Acu);
                    $this->db->set("CaraBayar", $row->CaraBayar);
                    $this->db->set("CashDiskon", $row->CashDiskon);
                    $this->db->set("ValueCashDiskon", $row->ValueCashDiskon);
                    $this->db->set("TOP", $row->TOP);
                    $this->db->set("TglJtoFaktur", date('Y-m-d'));
                    $this->db->set("Salesman", $row->Salesman);
                    $this->db->set("NamaSalesman", $row->NamaSalesman);
                    $this->db->set("Rayon", $row->Rayon);
                    $this->db->set("NamaRayon", $row->NamaRayon);
                    $this->db->set("Status", "Open");
                    $this->db->set("TipeDokumen", "Faktur");
                    $this->db->set("Gross", $row->Gross);
                    $this->db->set("Potongan", $row->Potongan);
                    $this->db->set("Value", $row->Value);
                    $this->db->set("Ppn", $row->Ppn);
                    $this->db->set("LainLain", $row->LainLain);
                    $this->db->set("Total", $row->Total);
                    $this->db->set("Keterangan1", $row->Keterangan1);
                    $this->db->set("Keterangan2", $row->Keterangan2);
                    $this->db->set("Barcode", $row->Barcode);
                    $this->db->set("QrCode", $row->QrCode);
                    $this->db->set("NoSO", $row->NoSO);
                    $this->db->set("NoDO", $row->NoDO);
                    $this->db->set("KodeProduk", $row->KodeProduk);
                    $this->db->set("NamaProduk", $row->NamaProduk);
                    $this->db->set("UOM", $row->UOM);
                    $this->db->set("Harga", $row->Harga);
                    $this->db->set("QtyDO", $row->QtyDO);
                    $this->db->set("BonusDO", $row->BonusDO);                
                    $this->db->set("QtyFaktur", $row->QtyDO);
                    $this->db->set("BonusFaktur", $row->BonusDO);
                    $this->db->set("ValueBonus", $row->ValueBonus);
                    $this->db->set("DiscCab", $row->DiscCab);
                    $this->db->set("ValueDiscCab", $row->ValueDiscCab);
                    $this->db->set("DiscCabTot", $row->DiscCabTot);
                    $this->db->set("ValueDiscCabTotal", $row->ValueDiscCabTotal);
                    $this->db->set("DiscPrins1", $row->DiscPrins1);
                    $this->db->set("ValueDiscPrins1", $row->ValueDiscPrins1);
                    $this->db->set("DiscPrins2", $row->DiscPrins2);
                    $this->db->set("ValueDiscPrins2", $row->ValueDiscPrins2);
                    $this->db->set("DiscPrinsTot", $row->DiscPrinsTot);
                    $this->db->set("ValueDiscPrinsTotal", $row->ValueDiscPrinsTotal);
                    $this->db->set("DiscTotal", $row->DiscTotal);
                    $this->db->set("ValueDiscTotal", $row->ValueDiscTotal);
                    $this->db->set("DiscCabMax", $row->DiscCabMax);
                    $this->db->set("KetDiscCabMax", $row->KetDiscCabMax);
                    $this->db->set("DiscPrinsMax", $row->DiscPrinsMax);
                    $this->db->set("KetDiscPrinsMax", $row->KetDiscPrinsMax);
                    $this->db->set("COGS", $row->COGS);
                    $this->db->set("TotalCOGS", $row->TotalCOGS);
                    $this->db->set("BatchNo", $row->BatchNo);
                    $this->db->set("ExpDate", $row->ExpDate);
                    $this->db->set("NoBPB", $row->NoBPB);
                    $valid =  $this->db->insert("trs_faktur_detail");
                    if($valid){
                        $this->db->query("update trs_faktur_detail a, mst_produk b 
                                            SET a.Prinsipal=b.Prinsipal,
                                              a.Prinsipal2=b.Prinsipal2,
                                              a.Supplier=b.Supplier1,
                                              a.Supplier2=b.Supplier2,
                                              a.Pabrik =b.Pabrik,
                                              a.Farmalkes=b.Farmalkes
                                            WHERE a.KodeProduk=b.Kode_Produk and 
                                            a.KodeProduk = '".$row->KodeProduk."'");
                    }
                    // End Detail
                }
                if($valid){
                $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Faktur' and Cabang = '".$this->cabang."'");
            }
                // END SAVE TABEL FAKTUR
            }
            elseif ($params->Status[$key] == "Kembali") {
                $this->db->set("Status", "Open");
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales'); 
            }
            // END UPDATE STATUS DO            
        }  

        return $NoFaktur;
    }

    public function prosesDataDO($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);     
        $update = $this->db->query("update trs_delivery_order_sales set statusPusat = 'Berhasil' where NoDO = '".$no."'");
        $query = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no."'")->result(); 
        $query2 = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$no."'")->row();
        if ($this->db2->conn_id == TRUE) {
            foreach($query as $r) { // loop over results                
                $cek = $this->db2->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no."' and KodeProduk = '".$r->KodeProduk."' and BatchNo = '".$r->BatchNo."'")->num_rows();
                if ($cek == 0) {
                    $this->db2->insert('trs_delivery_order_sales_detail', $r); // insert each row to another table
                }
                else{
                    $this->db2->where('BatchNo', $r->BatchNo);
                    $this->db2->where('KodeProduk', $r->KodeProduk);
                    $this->db2->where('NoDO', $no);
                    $this->db2->update('trs_delivery_order_sales_detail', $r);
                }
            }
            
            $cek2 = $this->db2->query("select * from trs_delivery_order_sales where NoDO = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_delivery_order_sales', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('NoDO', $no);
                $this->db2->update('trs_delivery_order_sales', $query2);
            }

            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function prosesDataKiriman($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);   
        $update = $this->db->query("update trs_kiriman set statusPusat = 'Berhasil' where NoKiriman = '".$no."'");
        $query = $this->db->query("select * from trs_kiriman where NoKiriman = '".$no."'")->result();   
        
        foreach($query as $r) { // loop over results  
                if ($this->db2->conn_id == TRUE) {              
                    $cek = $this->db2->query("select * from trs_kiriman where NoKiriman = '".$no."' and NoDO = '".$r->NoDO."'")->num_rows();
                    if ($cek == 0) {
                        $this->db2->insert('trs_kiriman', $r); // insert each row to another table
                    }
                    else{
                        $this->db2->where('NoDO', $r->NoDO);
                        $this->db2->where('NoKiriman', $no);
                        $this->db2->update('trs_kiriman', $r);
                    }
                     return TRUE;
                }else{
                return FALSE;
                } 
        }
        
    }

    public function dataDO($no = NULL)
    {
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and NoDO = '".$no."'";
        }
        $query = $this->db->query("select * from trs_delivery_order_sales_detail where Cabang = '".$this->cabang."' $byNo ")->result();

        return $query;
    }

    public function updateDataDOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoDO from trs_delivery_order_sales where Cabang = '".$this->cabang."'")->result();
                 $nomor_lokal = $this->db->query("select NoDO from trs_delivery_order_sales where Cabang = '".$this->cabang."'")->result();
                 foreach ($nomor_lokal as $no_lokal) {
                     if(!$no_lokal){
                        foreach ($nomor as $no) {
                            $query = $this->db2->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no->NoDO."'")->result();
                            foreach($query as $r) { // loop over results
                                $this->db->insert('trs_delivery_order_sales_detail', $r); // insert each row to another table
                            }

                            $query2 = $this->db2->query("select * from trs_delivery_order_sales where NoDO = '".$no->NoDO."'")->row();
                            $this->db->insert('trs_delivery_order_sales', $query2); // insert each row to another table
                        }

                     }else{
                        foreach ($nomor as $no) {
                            $query = $this->db2->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no->NoDO."'")->result();
                            foreach($query as $r) { // loop over results
                                $this->db->where('BatchNo', $r->BatchNo);
                                $this->db->where('KodeProduk', $r->KodeProduk);
                                $this->db->where('NoDO', $no->NoDO);
                                $this->db->update('trs_delivery_order_sales_detail', $r); // insert each row to another table
                            }

                            $query2 = $this->db2->query("select * from trs_delivery_order_sales where NoDO = '".$no->NoDO."'")->row();
                            $this->db->where('NoDO', $no->NoDO);
                            $this->db->where('statusPusat', 'Berhasil');
                            $this->db->update('trs_delivery_order_sales', $query2); // insert each row to another table
                        }

                     }
                 }
                

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function updateDataKirimanPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoKiriman, NoDO from trs_kiriman where Cabang = '".$this->cabang."'")->result();
                foreach ($nomor as $no) {
                    $query2 = $this->db2->query("select * from trs_kiriman where NoKiriman = '".$no->NoKiriman."' and NoDO = '".$no->NoDO."'")->row();
                    $this->db->where('NoDO', $no->NoDO);
                    $this->db->where('NoKiriman', $no->NoKiriman);
                    $this->db->where('statusPusat', 'Berhasil');
                    $this->db->update('trs_kiriman', $query2); // insert each row to another table
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function prosesbatalDataDo($NoDO=NULL){
        $this->db->set("Status", "Batal");
        $this->db->where("NoDO", $NoDO);
        $valid = $this->db->update('trs_delivery_order_sales'); 
        if($valid){
            $valid = $this->backStok($NoDO);
        }
        return $valid;
    }

    public function setStok($NoDO = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
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
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and ExpDate='".$detail[$key]->ExpDate."' and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $UnitStok = $dt->UnitStok - ($detail[$key]->QtyDO  + $detail[$key]->BonusDO);
                    $ValueStok = $invsum->UnitCOGS * $UnitStok;

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
                    $this->db->where("ExpDate", $detail[$key]->ExpDate);
                    $valid = $this->db->update('trs_invdet');
                }
            }
        }

    }

    public function backStok($NoDO = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
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

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
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
                    $UnitStok = $stok + $detail[$key]->QtyDO  + $detail[$key]->BonusDO;
                    $ValueStok = $invsum->UnitCOGS * $UnitStok;

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
                    $this->db->where("ExpDate", $detail[$key]->ExpDate);
                    $valid = $this->db->update('trs_invdet');
                }
                // else{
                //     $valuestok = ($detail[$key]->QtyDO  + $detail[$key]->BonusDO) * $detail[$key]->Harga;                
                //     $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$detail[$key]->QtyDO + $detail[$key]->BonusDO."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$detail[$key]->Harga."','".$detail[$key]->NoBPB."')");
                // }
            }
        }

    }

    public function cekStok($params = NULL)
    {
        $result = TRUE;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {    
                $expld = explode("~", $params->produk[$key]);
                $KodeProduk = $expld[0];
                $NamaProduk = $expld[1];

                $s = $this->db->query("select * from trs_invdet where KodeProduk='".$KodeProduk."' and Cabang='".$this->cabang."' and BatchNo='".$params->batch[$key]."' and ExpDate='".$params->expdate[$key]."' and NoDokumen='".$params[$key]->BatchDoc."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();

                $qty = $params->jumlah[$key];
                $qty2 = $params->jumlahmask[$key];
                $stok = $s->UnitStok;
                $stok2 = $stok + $qty2;
                if ($qty > $stok2) {
                    $result = FALSE;
                }

            }
        }
        return $result;
    }
}