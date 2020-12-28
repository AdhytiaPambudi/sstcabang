<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
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
        $query = "";

        if (!empty($status)) {
            if($status != 'All'){
                $byStatus = " and trs_delivery_order_sales.Status in ('".$status."')";
                $query = $this->db->query("select distinct trs_delivery_order_sales.*,'' as NoKiriman from trs_delivery_order_sales  where trs_delivery_order_sales.Cabang = '".$this->cabang."' and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' and trs_delivery_order_sales.Gross > 0 AND IFNULL(trs_delivery_order_sales.Keterangan2,'') <> 'Bidan' $byStatus $search order by TimeDO DESC $limit");
            }else{
                $query = $this->db->query("select distinct trs_delivery_order_sales.*,'' as NoKiriman from trs_delivery_order_sales  where trs_delivery_order_sales.Cabang = '".$this->cabang."' and trs_delivery_order_sales.Gross > 0  and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' AND IFNULL(trs_delivery_order_sales.Keterangan2,'') <> 'Bidan' $search order by TimeDO DESC $limit");
            }

        }else{
            $byStatus = " and Status <> 'Batal'";
            $query = $this->db->query("select distinct trs_delivery_order_sales.*,trs_kiriman.NoKiriman from trs_delivery_order_sales left join trs_kiriman on trs_kiriman.NoDO = trs_delivery_order_sales.NoDO where trs_delivery_order_sales.Cabang = '".$this->cabang."' and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' and trs_delivery_order_sales.Gross > 0 AND IFNULL(trs_delivery_order_sales.Keterangan2,'') <> 'Bidan' $byStatus $search order by TimeDO DESC $limit");

        }

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
                                  from trs_delivery_order_sales left join trs_delivery_order_sales_detail on 
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
        $query = $this->db->query("select distinct NoKiriman,StatusKiriman,TglKirim,TglTerima,statusPusat,Pengirim,NamaPengirim from trs_kiriman where Cabang = '".$this->cabang."' $byStatus $search  Order by TimeKirim DESC $limit");

        return $query;
    }

    public function saveKiriman($NoDO = NULL, $NoKiriman = NULL, $pengirim = NULL)
    {   
        $query = $this->db->query("select * FROM trs_delivery_order_sales WHERE NoDO = '".$NoDO."' and IFNULL(Keterangan2,'') <> 'Bidan' and Status = 'Open' limit 1");
        $valid = true;
        $data = $query->row();
        $num_data = $query->num_rows();
        if($num_data > 0){
            $query2 = $this->db->query("select * from trs_kiriman where NoDO = '".$NoDO."' and StatusDO != 'Kembali' limit 1");
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
                    $this->db->set("modified_at", date('Y-m-d H:i:s'));
                    $this->db->set("modified_by", $this->user);
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales');        

                    $this->db->set("Status", "Kirim");
                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("modified_at", date('Y-m-d H:i:s'));
                    $this->db->set("modified_by", $this->user);
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales_detail');
                }
            }

        }  
        return $valid;
    }

    public function listKiriman($search = NULL, $limit = NULL)
    {   
        /*$query = $this->db->query("select * from trs_kiriman where Cabang = '".$this->cabang."' and StatusKiriman in ('Open','Pending') $search group by NoKiriman $limit");*/

        $query = $this->db->query("select a.*,b.Keterangan2 FROM trs_kiriman a left join trs_delivery_order_sales b ON a.NoDO = b.NoDO  where a.Cabang = '".$this->cabang."' and IFNULL(Keterangan2,'') <> 'Bidan' and  StatusKiriman in ('Open','Pending') $search group by NoKiriman $limit");

        return $query;
    }

    public function getTerimaKiriman($no = null)
    {
        $noDO = "";
        $krm = $this->db->query("select NoDO from trs_kiriman where NoKiriman = '".$no."' and StatusKiriman in ('Open','Pending')")->result();
        foreach ($krm as $kirim) {            
            $noDO .= "'".$kirim->NoDO."',";
        }
        $noDO = substr($noDO, 0, -1);
        $query = $this->db->query("select * from trs_delivery_order_sales where NoDO in (".$noDO.") and IFNULL(Keterangan2,'') <> 'Bidan' order by NoDO asc")->result();
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
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where Cabang = '".$this->cabang."' and KodeProduk = '".$kode."' and Gudang = 'Baik' and UnitStok > 0")->result();
        return $query;
    }

    public function updateDataDO($params = null)
    {
        $status=false;
        // $h = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$params->noDO."' limit 1")->row();
        $this->backStok($params->noDO);

        $del = $this->db->query("delete from trs_delivery_order_sales_detail where NoDO = '".$params->noDO."'");
        $num_do = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$params->noDO."' limit 1")->num_rows();
        if($num_do <= 0){
            $status = true;
        }

        $dataPelanggan = $this->dataPelanggan($params->pelanggan);
        $dataSalesman = $this->dataSalesman($params->salesman);        
        $NoDO = $params->noDO;
        $NoSO = $params->noSO;
        $totgross = 0;
        $totpotongan = 0;
        $totalvalue = 0;
        $totcashdiskon = 0;
        $totppn = 0;
        $summary = 0;
        $totCogs = 0;
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
        if($status==true){
            foreach ($params->idx as $key => $value) 
            {
                if (!empty($params->produk[$key])) 
                {    
                    $expld = explode("~", $params->produk[$key]);
                    $KodeProduk = $expld[0];
                    $NamaProduk = $expld[1];
                    $batch = $this->Batch($KodeProduk,$params->batch[$key],$params->NoBPB[$key]);
                    
                    $jml = $params->jumlah[$key];
                    $harga = $params->harga[$key];
                    $dsccab = $params->dsccab[$key];
                    $dscprins1 = $params->dscprins1[$key];
                    $dscprins2 = $params->dscprins2[$key];
                    $bns = $params->bnscab[$key];
                    $CashDiskon = $params->cashdiskon[$key];

                                // tambahan
                    $unCogs = $batch[0]->COGS;

                    $gross = ($jml  + $bns) * $harga;
                    $stotCogs = ($jml  + $bns) * $unCogs;
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
                                
                    $potongan = $dscTot + $bnsTot + $valcashdiskon;     
                    $value = $gross - $potongan;
                    $ppn = $value * 0.1;
                    $TotValue = $value + $ppn; 

                    $totgross = $totgross + $gross;
                    $totpotongan = $totpotongan + $potongan;
                    $totalvalue = $totalvalue + $value;
                    $totppn = $totppn + $ppn;
                    $summary = $summary + $TotValue;
                    $totCogs = $totCogs + $stotCogs;
                    
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("NoDO", $NoDO);
                    $this->db->set("noline", $params->idx[$key]);
                    $this->db->set("TglDO", $params->tgl);
                    $this->db->set("TimeDO", date('Y-m-d H:i:s'));
                    $this->db->set("Pengirim", '');
                    $this->db->set("NamaPengirim", '');
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
                    $this->db->set("KodeProduk", $KodeProduk);
                    $this->db->set("NamaProduk", $NamaProduk);
                    $this->db->set("UOM", $params->uom[$key]);
                    $this->db->set("Harga", $params->harga[$key]);
                    $this->db->set("QtySO", $jml);
                    $this->db->set("BonusSO", $params->bnscab[$key]);
                    $this->db->set("DiscCab", $params->dsccab[$key]);                  
                    $this->db->set("DiscCabTot", $params->dsccab[$key]);
                    $this->db->set("DiscPrins1", $params->dscprins1[$key]);
                    $this->db->set("DiscPrins2", $params->dscprins2[$key]);
                    $this->db->set("DiscPrinsTot", $params->dscprins1[$key] + $params->dscprins2[$key]);
                    $this->db->set("DiscCabMax", $params->DiscCabMax[$key]);
                    $this->db->set("KetDiscCabMax", "");
                    $this->db->set("DiscPrinsMax", $params->DiscPrinsMax[$key]);
                    $this->db->set("KetDiscPrinsMax", "");
                    $this->db->set("ValueDiscCab", $dsccabval);
                    $this->db->set("ValueDiscCabTotal", $dsccabval);
                    $this->db->set("ValueDiscPrins1", $dscprins1val);
                    $this->db->set("ValueDiscPrins2", $dscprins2val);
                    $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                    $this->db->set("DiscTotal", $dsccab+$dscprins1+$dscprins2);
                    $this->db->set("ValueDiscTotal", $dsccabval+$dscprins1val+$dscprins2val);
                    $this->db->set("ValueCashDiskon", $valcashdiskon);
                    $this->db->set("QtyDO", $jml);
                    $this->db->set("BonusDO", $bns);
                    $this->db->set("ValueBonus", $boncabval);
                    $this->db->set("Gross", $gross);
                    $this->db->set("Potongan", $potongan);
                    $this->db->set("Value", $value);
                    $this->db->set("Ppn", $ppn);
                    $this->db->set("LainLain", "");
                    $this->db->set("Total", $TotValue);
                    $this->db->set("BatchNo", $params->batch[$key]);
                    $this->db->set("ExpDate", $batch[0]->ExpDate);
                    $this->db->set("COGS", $unCogs);
                    $this->db->set("TotalCOGS", $stotCogs);
                    $this->db->set("NoBPB", $params->NoBPB[$key]);
                    $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                    if($valid){
                        $this->db->set("BatchNo", $batch[0]->ExpDate);
                        $this->db->set("NoBPB", $params->NoBPB[$key]);
                        $this->db->where('NoSO', $NoSO);
                        $this->db->where('KodeProduk', $KodeProduk);
                        $this->db->update('trs_sales_order_detail');

                        $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                            SET a.Prinsipal=b.Prinsipal,
                                                a.Prinsipal2=b.Prinsipal2,
                                                a.Supplier=b.Supplier1,
                                                a.Supplier2=b.Supplier2,
                                                a.Pabrik =b.Pabrik,
                                                a.Farmalkes=b.Farmalkes
                                            WHERE 
                                                a.KodeProduk=b.Kode_Produk and 
                                                a.KodeProduk = '".$KodeProduk."'");
                    // }
                    
                    }
                }
            }

            $this->db->set("statusPusat", "Gagal");
            $this->db->set("TglJtoOrder", $tgljto);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update("trs_delivery_order_sales");

        }
        $this->setStok($NoDO);

        return $NoDO;
    }      
    public function saveTerimaKiriman($params = NULL)
    {   
        $NoFaktur = "";
        $message ='';

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
                $nofak = $this->db->query("select ifnull(trs_faktur.NoDO,'') as 'NoDO' from trs_faktur
                                          where ifnull(trs_faktur.NoDO,'') = '".$params->NoDO[$key]."' and IFNULL(Keterangan2,'') <> 'Bidan' and TipeDokumen ='Faktur' limit 1")->num_rows();            
                if($nofak == 0){
                        // $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->result();
                        $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$params->NoDO[$key]."' and IFNULL(Keterangan2,'') <> 'Bidan' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->row();
                        // $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                        // $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 


                        $valid = $this->db->query("INSERT INTO `trs_faktur` (
                                          `Cabang`,`NoFaktur`,
                                          `TglFaktur`,`TimeFaktur`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatFaktur`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          `TipePajak`,`NoPajak`,
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,
                                          `Saldo`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`NoSP`,
                                          `statusPusat`,`TipeFaktur`,
                                          `NoIDPaket`,`KeteranganTender`,
                                          `ppn_pelanggan`,
                                          `created_at`,`created_by`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `TglDO`,'".date('Y-m-d H:i:s')."',
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatKirim`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          '','',
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoOrder`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,`Total`,
                                          `Keterangan1`,`Keterangan2`,
                                          `Barcode`,`QrCode`,
                                          `NoSO`,`NoDO`,
                                          `NoSP`,`statusPusat`,
                                          `TipeFaktur`,`NoIDPaket`,
                                          `KeteranganTender`,`ppn_pelanggan`,
                                          '".date('Y-m-d H:i:s')."','".$this->user."',`acu2`
                                        FROM `trs_delivery_order_sales`
                                        where NoDo = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N';
                                        ");

                        $query = $this->db->query("INSERT INTO `trs_faktur_detail` (
                                          `Cabang`,`NoFaktur`,
                                          `noline`,`TglFaktur`,
                                          `TimeFaktur`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatFaktur`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyFaktur`,`BonusFaktur`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCab_onf`,
                                          `ValueDiscCab_onf`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,
                                          `sisa_retur`,`created_at`,
                                          `created_by`,`kota`,
                                          `telp`,`tipe_2`,
                                          `region`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `noline`,`TglDO`,
                                          '".date('Y-m-d H:i:s')."',`Pelanggan`,
                                          `NamaPelanggan`,`AlamatKirim`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,'TglJtoOrder',
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyDO`,`BonusDO`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCab_onf`,
                                          `ValueDiscCab_onf`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,0,
                                          '".date('Y-m-d H:i:s')."',
                                          '".$this->user."',`kota`,
                                          `telp`,`tipe_2`,`region`,`acu2`
                                        FROM `trs_delivery_order_sales_detail`
                                        where NoDo = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'");
                    
                        
                        // $this->db->set('NoFaktur', $header->NoDO);
                        // $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        // $this->db->set("modified_by", $this->user);
                        // $this->db->where('NoSO', $header->NoSO);
                        // $this->db->update('trs_sales_order'); 

                        // $this->db->set('NoFaktur', $header->NoDO);
                        // $this->db->set("Status", "Closed");
                        // $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        // $this->db->set("modified_by", $this->user);
                        // $this->db->set("status_validasi", "Y");
                        // $this->db->set("time_validasi", date('Y-m-d H:i:s'));
                        // $this->db->set("user_validasi", $this->user);
                        // $this->db->where("NoDO", $params->NoDO[$key]);
                        // $valid = $this->db->update('trs_delivery_order_sales');

                        // $this->db->set('NoFaktur', $header->NoDO);
                        // $this->db->set("Status", "Closed");
                        // $this->db->set("status_validasi", "Y");
                        // $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        // $this->db->set("modified_by", $this->user);
                        // $this->db->where("NoDO", $params->NoDO[$key]);
                        // $valid = $this->db->update('trs_delivery_order_sales_detail');   
                        // if($valid){
                        //     $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Faktur' and Cabang = '".$this->cabang."'");
                        // }
                    $cekheader = $this->db->query("select * from trs_faktur where nofaktur ='".$params->NoDO[$key]."'")->num_rows();
                    $cekdetail = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$params->NoDO[$key]."'")->num_rows();
                    if($cekheader > 0 and $cekdetail > 0){
                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            $this->db->where('NoSO', $header->NoSO);
                            $this->db->update('trs_sales_order'); 

                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("Status", "Closed");
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            // $this->db->set("status_validasi", "Y");
                            $this->db->set("time_validasi", date('Y-m-d H:i:s'));
                            $this->db->set("user_validasi", $this->user);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update('trs_delivery_order_sales');

                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("Status", "Closed");
                            // $this->db->set("status_validasi", "Y");
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update('trs_delivery_order_sales_detail');
                            

                            if ($header->Keterangan2 == 'Salesman') {

                                 $this->Model_order->data_api("method=setnoerp&cabang=".$this->cabang."&nosp=".$header->NoSP."&noerp=".$header->NoDO."&tglerp=".date('Y-m-d'));

                                 $query = $this->Model_order->data_api("method=setnoerp&cabang=".$this->cabang."&nosp=".$header->NoSP."&noerp=".$header->NoDO."&tglerp=".date('Y-m-d'));
                                $data = json_decode($query);

                                if ($data->message != 'Berhasil') {
                                    $delheader = $this->db->query("delete from trs_faktur where  NoFaktur = '".$params->NoDO[$key]."'");
                                    $deldetail = $this->db->query("delete from trs_faktur_detail where NoFaktur ='".$params->NoDO[$key]."'");

                                    $this->db->set("Status", "Kirim");
                                    $this->db->where("NoDO", $params->NoDO[$key]);
                                    $this->db->update('trs_delivery_order_sales');

                                    $this->db->set("Status", "Kirim");
                                    $this->db->where("NoDO", $params->NoDO[$key]);
                                    $this->db->update('trs_delivery_order_sales_detail');

                                    $this->db->set("StatusKiriman", 'Open');
                                    $this->db->set("StatusDO", "Kirim");
                                    $this->db->set("updated_by", $this->session->userdata('username'));
                                    $this->db->set("updated_at", date("Y-m-d H:i:s"));
                                    $this->db->where("NoKiriman", $params->NoKiriman);
                                    $this->db->where("NoDO", $params->NoDO[$key]);
                                    $this->db->update("trs_kiriman");
                                    
                                    $this->db->set("Keterangan", 'GagalUpdate salesman');
                                    $this->db->where("NoKiriman", $params->NoKiriman);
                                    $this->db->where("NoDO", $params->NoDO[$key]);
                                    $this->db->update("trs_kiriman");

                                    log_message("error","GagalUpdate salesman ".$params->NoDO[$key]);

                                    $message .= $params->NoDO[$key].',';

                                    
                                }else{

                                    if ($header->Keterangan2 == 'Salesman') {
                                        $this->db->set("Keterangan", '');
                                        $this->db->where("NoKiriman", $params->NoKiriman);
                                        $this->db->where("NoDO", $params->NoDO[$key]);
                                        $this->db->update("trs_kiriman");
                                    }
                                }

                                
                            }

                    }else{
                            $delheader = $this->db->query("delete from trs_faktur where  NoFaktur = '".$params->NoDO[$key]."'");
                            $deldetail = $this->db->query("delete from trs_faktur_detail where NoFaktur ='".$params->NoDO[$key]."'");

                            $this->db->set("StatusKiriman", 'Open');
                            $this->db->set("StatusDO", "Kirim");
                            $this->db->set("updated_by", $this->session->userdata('username'));
                            $this->db->set("updated_at", date("Y-m-d H:i:s"));
                            $this->db->where("NoKiriman", $params->NoKiriman);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update("trs_kiriman");

                    }    
                }else{
                    $valid = FALSE;
                }

            }
            elseif ($params->Status[$key] == "Kembali") {
                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales'); 

                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales_detail');
                

            }elseif($params->Status[$key] == "Pending"){
                $this->db->set("StatusKiriman", 'Open');
                $this->db->set("StatusDO", $params->Status[$key]);
                $this->db->set("Alasan", $params->Alasan[$key]);
                // $this->db->set("TglTerima", date("Y-m-d"));
                // $this->db->set("TimeTerima", date("Y-m-d H:i:s")); 
                $this->db->set("updated_by", $this->session->userdata('username'));
                $this->db->set("updated_at", date("Y-m-d H:i:s"));
                $this->db->where("NoKiriman", $params->NoKiriman);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update("trs_kiriman");

            }
            // END UPDATE STATUS DO            
        } 
         
        $valid =["status" =>$valid,"message"=>$message];
        return $valid;
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

    // public function updateDataDOPusat()
    // {   
    //     $this->db2 = $this->load->database('pusat', TRUE);      
    //     if ($this->db2->conn_id == TRUE) {
    //             $nomor = $this->db2->query("select NoDO from trs_delivery_order_sales where Cabang = '".$this->cabang."' and Tipe = 'Relokasi'")->result();
    //              $nomor_lokal = $this->db->query("select NoDO from trs_delivery_order_sales where Cabang = '".$this->cabang."'")->result();
    //              foreach ($nomor_lokal as $no_lokal) {
    //                  if(!$no_lokal){
    //                     foreach ($nomor as $no) {
    //                         $query = $this->db2->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no->NoDO."'")->result();
    //                         foreach($query as $r) { // loop over results
    //                             $this->db->insert('trs_delivery_order_sales_detail', $r); // insert each row to another table
    //                         }

    //                         $query2 = $this->db2->query("select * from trs_delivery_order_sales where NoDO = '".$no->NoDO."'")->row();
    //                         $this->db->insert('trs_delivery_order_sales', $query2); // insert each row to another table
    //                     }

    //                  }else{
    //                     foreach ($nomor as $no) {
    //                         $query = $this->db2->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no->NoDO."'")->result();
    //                         foreach($query as $r) { // loop over results
    //                             $this->db->where('BatchNo', $r->BatchNo);
    //                             $this->db->where('KodeProduk', $r->KodeProduk);
    //                             $this->db->where('NoDO', $no->NoDO);
    //                             $this->db->update('trs_delivery_order_sales_detail', $r); // insert each row to another table
    //                         }

    //                         $query2 = $this->db2->query("select * from trs_delivery_order_sales where NoDO = '".$no->NoDO."'")->row();
    //                         $this->db->where('NoDO', $no->NoDO);
    //                         $this->db->where('statusPusat', 'Berhasil');
    //                         $this->db->update('trs_delivery_order_sales', $query2); // insert each row to another table
    //                     }

    //                  }
    //              }
                

    //             return 'BERHASIL';
    //         // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
    //     }
    //     else{
    //         return 'GAGAL';
    //     }
    // }

    public function updateDataKirimanPusat()
    {   
        // $this->db2 = $this->load->database('pusat', TRUE);      
        // if ($this->db2->conn_id == TRUE) {
        //         $nomor = $this->db2->query("select NoKiriman, NoDO from trs_kiriman where Cabang = '".$this->cabang."'")->result();
        //         foreach ($nomor as $no) {
        //             $query2 = $this->db2->query("select * from trs_kiriman where NoKiriman = '".$no->NoKiriman."' and NoDO = '".$no->NoDO."'")->row();
        //             $this->db->where('NoDO', $no->NoDO);
        //             $this->db->where('NoKiriman', $no->NoKiriman);
        //             $this->db->set('statusPusat', 'Berhasil');
        //             $this->db->update('trs_kiriman', $query2); // insert each row to another table
        //         }

        //         return 'BERHASIL';
        //     // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        // }
        // else{
        //     return 'GAGAL';
        // }
    }

    public function prosesbatalDataDo($NoDO=NULL){
        $this->db->set("Status", "Batal");
        $this->db->set("time_batal",date('Y-m-d H:i:s'));
        $this->db->set("user_batal",$this->session->userdata('username'));
        $this->db->set('Keterangan1', "DO Batal By User");
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->user);
        $this->db->where("NoDO", $NoDO);
        $valid = $this->db->update('trs_delivery_order_sales'); 

        $this->db->set("Status", "Batal");
        $this->db->set("time_batal",date('Y-m-d H:i:s'));
        $this->db->set("user_batal",$this->session->userdata('username'));
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->user);
        $this->db->where("NoDO", $NoDO);
        $valid = $this->db->update('trs_delivery_order_sales_detail'); 
        if($valid){
            $valid = $this->backStok($NoDO);
        }

        // $this->db->set('NoDO', '');
        // $this->db->set('Status', "Usulan");
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->user);
        $this->db->set('alasan_status', "DO Batal By User");
        $this->db->where('NoDO', $NoDO);
        $valid = $this->db->update('trs_sales_order');

        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->user);
        $this->db->where('NoDO', $NoDO);
        $valid = $this->db->update('trs_sales_order_detail'); 
        
        return $valid;
    }

    public function setStok($NoDO = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,TotalCOGS,COGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
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
                        if($detail[$key]->TotalCOGS < 0){
                            $valuestok = -1 * $detail[$key]->TotalCOGS;
                        }else{
                            $valuestok = $detail[$key]->TotalCOGS;
                        }
                    }
                }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok - $summary;
                    if($detail[$key]->TotalCOGS < 0){
                        $valuestok = $invsum->ValueStok - (-1 * $detail[$key]->TotalCOGS);
                    }else{
                        $valuestok = $invsum->ValueStok - $detail[$key]->TotalCOGS;
                    }
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }
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
                    $UnitStok = $dt->UnitStok - ($detail[$key]->QtyDO + $detail[$key]->BonusDO);
                    if($detail[$key]->TotalCOGS < 0){
                        $valuestok = $dt->ValueStok - (-1 * $detail[$key]->TotalCOGS);
                    }else{
                        $valuestok = $dt->ValueStok - $detail[$key]->TotalCOGS;
                    }

                    if($UnitStok == 0){
                        $valuestok = 0;
                    }

                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
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

    // public function backStokOLD($NoDO = NULL)
    // {              
    //     $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
    //     $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,COGS,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
    //     foreach ($produk as $kunci => $nilai) {
    //         $summary = 0;
    //         $product1 = $produk[$kunci]->KodeProduk;
    //         $harga = $produk[$kunci]->Harga;
    //         if (!empty($product1)) {
    //             $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
    //             foreach ($detail as $key => $value) {
    //                 $product2 = $detail[$key]->KodeProduk;
    //                 if ($product1 == $product2) {
    //                     $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
    //                     if($detail[$key]->TotalCOGS < 0){
    //                         $valuestok = -1 * $detail[$key]->TotalCOGS;
    //                     }else{
    //                         $valuestok = $detail[$key]->TotalCOGS;
    //                     }
                        
    //                 }
    //             }
    //             $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
    //             if ($invsum->num_rows() > 0) {
    //                 $invsum = $invsum->row();  
    //                 $UnitStok = $invsum->UnitStok + $summary;
    //                 if($detail[$key]->TotalCOGS < 0){
    //                     $valuestok = $invsum->ValueStok + (-1 * $detail[$key]->TotalCOGS);
    //                 }else{
    //                     $valuestok = $invsum->ValueStok + $detail[$key]->TotalCOGS;
    //                 }

    //                 if($UnitStok == 0){
    //                     $valuestok = 0;
    //                 }
                    
    //                 $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
    //             }
    //             // else{
    //             //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
    //             // }
    //         }
    //     }

    //     // save inventori history
    //     foreach ($detail as $key => $value) {   
    //         $produk = $detail[$key]->KodeProduk;        
    //         if (!empty($produk)) {
    //             $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
    //             $valuestok = $detail[$key]->TotalCOGS;
    //             $UnitStok = $detail[$key]->QtyDO+$detail[$key]->BonusDO;

    //             $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
    //         }
    //     }

    //     // save inventori detail
    //     foreach ($detail as $key => $value) { 
    //         $produk = $detail[$key]->KodeProduk;  
    //         if (!empty($produk)) {
    //             $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
    //             $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
    //             $dt = $invdet->row();
    //             if ($invdet->num_rows() > 0) {
    //                 $stok = $dt->UnitStok;
    //                 $UnitStok = $stok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
    //                 if($detail[$key]->TotalCOGS < 0){
    //                     $valuestok = $dt->ValueStok + (-1 * $detail[$key]->TotalCOGS);
    //                 }else{
    //                     $valuestok = $dt->ValueStok + $detail[$key]->TotalCOGS;
    //                 }

    //                 if($UnitStok == 0){
    //                     $valuestok = 0;
    //                 }

    //                 $this->db->set("UnitStok", $UnitStok);
    //                 $this->db->set("ValueStok", $valuestok);
    //                 $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
    //                 $this->db->set("ModifiedUser", $this->session->userdata('username'));
    //                 $this->db->where("KodeProduk", $produk);
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->where("Gudang", "Baik");
    //                 $this->db->where("Cabang", $this->cabang);
    //                 $this->db->where("NoDokumen", $detail[$key]->NoBPB);
    //                 $this->db->where("BatchNo", $detail[$key]->BatchNo);
    //                 $valid = $this->db->update('trs_invdet');

    //                 $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
    //                                         from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
    //                                         group by KodeProduk limit 1");
    //                 if($invdet->num_rows() <= 0){
    //                     $this->db->set("ValueStok", 0);
    //                     $this->db->where("KodeProduk", $produk);
    //                     $this->db->where("Gudang", 'Baik');
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->where("Cabang",$this->cabang);
    //                     $valid = $this->db->update('trs_invsum');
    //                 }else{
    //                     $invdet = $invdet->row();
    //                     $this->db->set("ValueStok", $invdet->sumval);
    //                     // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
    //                     $this->db->where("KodeProduk", $produk);
    //                     $this->db->where("Gudang", 'Baik');
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->where("Cabang",$this->cabang);
    //                     $valid = $this->db->update('trs_invsum');
    //                 }
    //             }
    //             // else{
    //             //     $valuestok = ($detail[$key]->QtyDO  + $detail[$key]->BonusDO) * $detail[$key]->Harga;                
    //             //     $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$detail[$key]->QtyDO + $detail[$key]->BonusDO."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$detail[$key]->Harga."','".$detail[$key]->NoBPB."')");
    //             // }
    //         }
    //     }

    // }

    public function backStok($NoDO = null, $Acu = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtyDO  from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."' ")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo,Value, ExpDate,NoBPB,COGS,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."'")->result();
        foreach ($detail as $kunci => $nilai) {
            $summary = 0;
            $product1 = $detail[$kunci]->KodeProduk;
            $harga = $detail[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$product1."'")->row();
                // foreach ($detail as $key => $value) {
                //     $product2 = $detail[$key]->KodeProduk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $detail[$kunci]->QtyDO + $detail[$kunci]->BonusDO;
                        $valuestok = $detail[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    if($detail[$kunci]->TotalCOGS < 0){
                        $valuestok = $invsum->ValueStok - ($detail[$kunci]->TotalCOGS);
                    }else{
                        $valuestok = $invsum->ValueStok + ($detail[$kunci]->TotalCOGS);
                    }
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }
                    $UnitCOGS = $valuestok / $UnitStok;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok.",UnitCOGS ='".$UnitCOGS."' where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
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
                $UnitStok = $detail[$key]->QtyDO + $detail[$key]->BonusDO;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and Tahun = '".date('Y')."' and Gudang='Baik' and NoDokumen = '".$NoDO."' limit 1");
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$produk."'")->row();
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $UnitStok  = $dt->UnitStok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("NoDokumen", $NoDO);
                    $this->db->where("BatchNo", $detail[$key]->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                }else{
                    $UnitStok  = $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik,KodePrinsipal,NamaPrinsipal, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."','".$prod->Prinsipal."','".$prod->Prinsipal."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$UnitCOGS."','".$NoDO."')");
                }
                    

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

    public function backStok_double($NoDO = null, $Acu = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtyDO  from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."' ")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo,Value, ExpDate,NoBPB,COGS,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."'")->result();

        

        // save inventori history
        foreach ($detail as $key => $value) {   
            $produk = $detail[$key]->KodeProduk;        
            if (!empty($produk)) {
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $detail[$key]->TotalCOGS;
                $UnitStok = $detail[$key]->QtyDO + $detail[$key]->BonusDO;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and Tahun = '".date('Y')."' and Gudang='Baik' and NoDokumen = '".$NoDO."' limit 1");
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$produk."'")->row();
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $UnitStok  = $dt->UnitStok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("NoDokumen", $NoDO);
                    $this->db->where("BatchNo", $detail[$key]->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                }else{
                    $UnitStok  = $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik,KodePrinsipal,NamaPrinsipal, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."','".$prod->Prinsipal."','".$prod->Prinsipal."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$UnitCOGS."','".$NoDO."')");
                }
                    

                
            }
        }


         // ===== untuk summary ===== //
            /*$produk_sum = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' GROUP BY KodeProduk")->result();*/
            $data_sum = $this->db->query("select KodeProduk, sum(QtyDO) QtyDO, sum(BonusDO) BonusDO, Harga,Value,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' GROUP BY KodeProduk");

            $numdata_sum = $data_sum->num_rows();
            $detail_sum = $data_sum->result();

            foreach ($detail_sum as $kunci => $nilai) {
              $summary = 0;
              $product1 = $detail_sum[$kunci]->KodeProduk;
              $harga = $detail_sum[$kunci]->Harga;

              if (!empty($product1)) {
                  $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$product1."'")->row();
                          $summary = $summary + $detail_sum[$kunci]->QtyDO + $detail_sum[$kunci]->BonusDO;
                          $valuestok = $detail_sum[$kunci]->Value;
                  $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");

                  if ($invsum->num_rows() > 0) {
                      $invsum = $invsum->row();  
                      $UnitStok = $invsum->UnitStok + $summary;

                      if($detail_sum[$kunci]->TotalCOGS < 0){
                          $valuestok = $invsum->ValueStok - ($detail_sum[$kunci]->TotalCOGS);
                      }else{
                          $valuestok = $invsum->ValueStok + ($detail_sum[$kunci]->TotalCOGS);
                      }
                      if($UnitStok == 0){
                          $valuestok = 0;
                      }
                      $UnitCOGS = $valuestok / $UnitStok;
                      $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok.",UnitCOGS ='".$UnitCOGS."' where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                  }

                  $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$product1."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");

                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $product1);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $product1);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }
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
                $s = $this->db->query("select * from trs_invdet where KodeProduk='".$KodeProduk."' and Cabang='".$this->cabang."' and BatchNo='".$params->batch[$key]."' and ExpDate='".$params->expdate[$key]."' and NoDokumen='".$params->NoBPB[$key]."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();

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

    public function Batch($kode = NULL,$BatchNo=NULL,$NoDokumen=null)
    {   
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate, NoDokumen as NoBPB,UnitCOGS as 'COGS' from trs_invdet where KodeProduk = '".$kode."' and BatchNo = '".$BatchNo."' and NoDokumen = '".$NoDokumen."' and  Cabang = '".$this->cabang."' and UnitStok > 0 and Gudang = 'Baik' and Tahun ='".date('Y')."' order by NoDokumen,ExpDate asc")->result();

        return $query;
    }

    public function listDataDO2($search = null, $limit = null, $periode = null)
    {   
        $query = $this->db->query("select  trs_delivery_order_sales.* from trs_delivery_order_sales  
                                    where trs_delivery_order_sales.Cabang = '".$this->cabang."'
                                    $periode  $search order by TimeDO DESC $limit");
        
        return $query;
    }
    public function listDatavalidasiDO($search = null, $limit = null)
    {   
        $query = $this->db->query("select  trs_delivery_order_sales.* from trs_delivery_order_sales  
            where trs_delivery_order_sales.Cabang = '".$this->cabang."' and trs_delivery_order_sales.Gross > 0 and 
                  trs_delivery_order_sales.Status ='Terima' and 
                  ifnull(trs_delivery_order_sales.nofaktur,'') = '' and ifnull(status_validasi,'') ='N'
          $search order by TglDO,NoDO ASC $limit");
        
        return $query;
    }

    // public function prosesValidasiDO($no = NULL)
    // {   
    //     $this->db->set("status_validasi", "Y");
    //     $this->db->set("time_validasi", date('Y-m-d H:i:s'));
    //     $this->db->set("user_validasi", $this->user);
    //     $this->db->where("NoDO", $no);
    //     $valid = $this->db->update('trs_delivery_order_sales');

    //     $this->db->set("status_validasi", "Y");
    //     $this->db->where("NoDO", $no);
    //     $valid = $this->db->update('trs_delivery_order_sales_detail'); 

    //     $nofak = $this->db->query("select ifnull(trs_faktur.NoDO,'') as 'NoDO' from trs_faktur
    //                                       where ifnull(trs_faktur.NoDO,'') = '".$no."' and TipeDokumen ='Faktur' limit 1")->num_rows();            
    //     if($nofak == 0){
    //         $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$no."' and Status = 'Terima' and ifnull(status_validasi,'') ='Y'")->result();
    //         $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$no."' and Status = 'Terima' and ifnull(status_validasi,'') ='Y'")->row();
    //         $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
    //         $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 
    //         $this->db->set("Cabang", $this->cabang);
    //         $this->db->set("NoFaktur",$no);
    //         $this->db->set("TglFaktur", $header->TglDO);
    //         $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
    //         $this->db->set("Pelanggan", $header->Pelanggan);
    //         $this->db->set("NamaPelanggan", $header->NamaPelanggan);
    //         $this->db->set("AlamatFaktur", $header->AlamatKirim);
    //         $this->db->set("TipePelanggan", $header->TipePelanggan);
    //         $this->db->set("NamaTipePelanggan", $header->NamaTipePelanggan);
    //         $this->db->set("NPWPPelanggan", $header->NPWPPelanggan);
    //         $this->db->set("ppn_pelanggan", $header->ppn_pelanggan);
    //         $this->db->set("NoPajak", "");
    //         $this->db->set("TipePajak", "");
    //         $this->db->set("KategoriPelanggan", $header->KategoriPelanggan);
    //         $this->db->set("Acu", $header->Acu);
    //         $this->db->set("CaraBayar", $header->CaraBayar);
    //         $this->db->set("CashDiskon", $header->CashDiskon);
    //         $this->db->set("ValueCashDiskon", $header->ValueCashDiskon);
    //         $this->db->set("TOP", $header->TOP);
    //         $this->db->set("TglJtoFaktur", $tglJTO);
    //         $this->db->set("Salesman", $header->Salesman);
    //         $this->db->set("NamaSalesman", $header->NamaSalesman);
    //         $this->db->set("Rayon", $header->Rayon);
    //         $this->db->set("NamaRayon", $header->NamaRayon);
    //         $this->db->set("Status", "Open");
    //         $this->db->set("TipeDokumen", "Faktur");
    //         $this->db->set("Gross", $header->Gross);
    //         $this->db->set("Potongan", $header->Potongan);
    //         $this->db->set("Value", $header->Value);
    //         $this->db->set("Ppn", $header->Ppn);
    //         $this->db->set("LainLain", $header->LainLain);
    //         $this->db->set("Materai", $header->Materai);
    //         $this->db->set("OngkosKirim", $header->OngkosKirim);
    //         $this->db->set("Total", $header->Total);
    //         $this->db->set("Saldo", $header->Total);
    //         $this->db->set("Keterangan1", $header->Keterangan1);
    //         $this->db->set("Keterangan2", $header->Keterangan2);
    //         $this->db->set("Barcode", $header->Barcode);
    //         $this->db->set("QrCode", $header->QrCode);
    //         $this->db->set("NoSO", $header->NoSO);
    //         $this->db->set("NoDO", $header->NoDO);
    //         $this->db->set("NoSP", $header->NoSP);
    //         $this->db->set("TipeFaktur", $header->TipeFaktur);
    //         $this->db->set("NoIDPaket", $header->NoIDPaket);
    //         $this->db->set("KeteranganTender", $header->KeteranganTender);
    //         $this->db->set("statusPusat", "Gagal");
    //         $this->db->set("created_at", date('Y-m-d H:i:s'));
    //         $this->db->set("created_by", $this->session->userdata('username'));
    //         $valid =  $this->db->insert("trs_faktur");
    //         $i=0;
    //         $xx = $this->db->query("select Pelanggan from trs_faktur where NoFaktur = '".$no."' limit 1")->row();
    //         $headerPelanggan = $xx->Pelanggan;
    //         foreach ($detail as $row) {
    //             if($headerPelanggan == $row->Pelanggan){
    //                 $i++;
    //                 $this->db->set("Cabang", $this->cabang);
    //                 $this->db->set("NoFaktur", $no);
    //                 $this->db->set("noline", $i);
    //                 $this->db->set("TglFaktur", $header->TglDO);
    //                 $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
    //                 $this->db->set("Pelanggan", $row->Pelanggan);
    //                 $this->db->set("NamaPelanggan", $row->NamaPelanggan);
    //                 $this->db->set("AlamatFaktur", $row->AlamatKirim);
    //                 $this->db->set("TipePelanggan", $row->TipePelanggan);
    //                 $this->db->set("NamaTipePelanggan", $row->NamaTipePelanggan);
    //                 $this->db->set("NPWPPelanggan", $row->NPWPPelanggan);
    //                 $this->db->set("KategoriPelanggan", $row->KategoriPelanggan);
    //                 $this->db->set("Acu", $row->Acu);
    //                 $this->db->set("CaraBayar", $row->CaraBayar);
    //                 $this->db->set("CashDiskon", $row->CashDiskon);
    //                 $this->db->set("ValueCashDiskon", $row->ValueCashDiskon);
    //                 $this->db->set("TOP", $row->TOP);
    //                 $this->db->set("TglJtoFaktur", date('Y-m-d'));
    //                 $this->db->set("Salesman", $row->Salesman);
    //                 $this->db->set("NamaSalesman", $row->NamaSalesman);
    //                 $this->db->set("Rayon", $row->Rayon);
    //                 $this->db->set("NamaRayon", $row->NamaRayon);
    //                 $this->db->set("Status", "Open");
    //                 $this->db->set("TipeDokumen", "Faktur");
    //                 $this->db->set("Gross", $row->Gross);
    //                 $this->db->set("Potongan", $row->Potongan);
    //                 $this->db->set("Value", $row->Value);
    //                 $this->db->set("Ppn", $row->Ppn);
    //                 $this->db->set("LainLain", $row->LainLain);
    //                 $this->db->set("Total", $row->Total);
    //                 $this->db->set("Keterangan1", $row->Keterangan1);
    //                 $this->db->set("Keterangan2", $row->Keterangan2);
    //                 $this->db->set("Barcode", $row->Barcode);
    //                 $this->db->set("QrCode", $row->QrCode);
    //                 $this->db->set("NoSO", $row->NoSO);
    //                 $this->db->set("NoDO", $row->NoDO);
    //                 $this->db->set("KodeProduk", $row->KodeProduk);
    //                 $this->db->set("NamaProduk", $row->NamaProduk);
    //                 $this->db->set("UOM", $row->UOM);
    //                 $this->db->set("Harga", $row->Harga);
    //                 $this->db->set("QtyDO", $row->QtyDO);
    //                 $this->db->set("BonusDO", $row->BonusDO);                
    //                 $this->db->set("QtyFaktur", $row->QtyDO);
    //                 $this->db->set("BonusFaktur", $row->BonusDO);
    //                 $this->db->set("ValueBonus", $row->ValueBonus);
    //                 $this->db->set("DiscCab", $row->DiscCab);
    //                 $this->db->set("ValueDiscCab", $row->ValueDiscCab);
    //                 $this->db->set("DiscCabTot", $row->DiscCabTot);
    //                 $this->db->set("ValueDiscCabTotal", $row->ValueDiscCabTotal);
    //                 $this->db->set("DiscPrins1", $row->DiscPrins1);
    //                 $this->db->set("ValueDiscPrins1", $row->ValueDiscPrins1);
    //                 $this->db->set("DiscPrins2", $row->DiscPrins2);
    //                 $this->db->set("ValueDiscPrins2", $row->ValueDiscPrins2);
    //                 $this->db->set("DiscPrinsTot", $row->DiscPrinsTot);
    //                 $this->db->set("ValueDiscPrinsTotal", $row->ValueDiscPrinsTotal);
    //                 $this->db->set("DiscTotal", $row->DiscTotal);
    //                 $this->db->set("ValueDiscTotal", $row->ValueDiscTotal);
    //                 $this->db->set("DiscCabMax", $row->DiscCabMax);
    //                 $this->db->set("KetDiscCabMax", $row->KetDiscCabMax);
    //                 $this->db->set("DiscPrinsMax", $row->DiscPrinsMax);
    //                 $this->db->set("KetDiscPrinsMax", $row->KetDiscPrinsMax);
    //                 $this->db->set("COGS", $row->COGS);
    //                 $this->db->set("TotalCOGS", $row->TotalCOGS);
    //                 $this->db->set("BatchNo", $row->BatchNo);
    //                 $this->db->set("ExpDate", $row->ExpDate);
    //                 $this->db->set("NoBPB", $row->NoBPB);
    //                 $this->db->set("created_at", date('Y-m-d H:i:s'));
    //                 $this->db->set("created_by", $this->session->userdata('username'));
    //                 $valid =  $this->db->insert("trs_faktur_detail");
    //                 if($valid){
    //                     $this->db->query("update trs_faktur_detail a, mst_produk b 
    //                                     SET a.Prinsipal=b.Prinsipal,
    //                                         a.Prinsipal2=b.Prinsipal2,
    //                                         a.Supplier=b.Supplier1,
    //                                         a.Supplier2=b.Supplier2,
    //                                         a.Pabrik =b.Pabrik,
    //                                         a.Farmalkes=b.Farmalkes
    //                                     WHERE a.KodeProduk=b.Kode_Produk and 
    //                                         a.KodeProduk = '".$row->KodeProduk."' and 
    //                                         a.NoFaktur ='".$no."'");
    //                 }

    //             }
                                
    //         }
    //             $this->db->set('NoFaktur', $no);
    //             $this->db->set("modified_at", date('Y-m-d H:i:s'));
    //             $this->db->set("modified_by", $this->user);
    //             $this->db->where('NoSO', $header->NoSO);
    //             $valid = $this->db->update('trs_sales_order'); 

    //             $this->db->set("nofaktur", $no);
    //             $this->db->set("Status", "Closed");
    //             $this->db->set("modified_at", date('Y-m-d H:i:s'));
    //             $this->db->set("modified_by", $this->user);
    //             $this->db->where("NoDO", $no);
    //             $valid = $this->db->update('trs_delivery_order_sales');

    //             $this->db->set("nofaktur", $no);
    //             $this->db->set("Status", "Closed");
    //             $this->db->set("modified_at", date('Y-m-d H:i:s'));
    //             $this->db->set("modified_by", $this->user);
    //             $this->db->where("NoDO", $no);
    //             $valid = $this->db->update('trs_delivery_order_sales_detail');   
    //     }
    //     return $valid;
    // }


    public function prosesReturDO($no = NULL)
    {   
        $x = $this->db->query("select nofaktur from trs_faktur where nodo ='".$no."'")->num_rows();
        if($x < 1){
            //================ Running Number ======================================//
            $year = date('Y');
            $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
            $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
            $kodeDokumen = $this->Model_main->kodeDokumen('Retur DO');
            $data = $this->db->query("select max(right(NoDO,7)) as 'no' from trs_delivery_order_sales where substr(NoDO,5,2) = '07' and Cabang = '".$this->cabang."' and length(NoDO) = 13 and YEAR(TglDO) ='".$year."' and TipeDokumen ='Retur'")->result();
            if(empty($data[0]->no) || $data[0]->no == ""){
                $lastNumber = 1000001;
            }else {
                $lastNumber = $data[0]->no + 1;
            }
            $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
            //================= end of running number ========================================//
            $valid = $this->db->query("INSERT INTO `trs_delivery_order_sales`
                                        (`Cabang`,`NoDO`,`TglDO`,`TimeDO`,`Pengirim`,`NamaPengirim`,`Pelanggan`,
                                        `NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,`NamaTipePelanggan`,
                                        `NPWPPelanggan`,`KategoriPelanggan`,`Acu`,`Acu2`,`CaraBayar`,`CashDiskon`,
                                        `ValueCashDiskon`,`TOP`,`TglJtoOrder`,`Salesman`,`NamaSalesman`,
                                        `Rayon`,`NamaRayon`,`Status`,`TipeDokumen`,`Gross`,`Potongan`,
                                        `Value`,`Ppn`,`LainLain`,`Materai`,`OngkosKirim`,`Total`,`Keterangan1`,
                                        `Keterangan2`,`Barcode`,`QrCode`,`NoSO`,`NoFaktur`,`NoSP`,`statusPusat`,
                                        `TipeFaktur`,`NoIDPaket`,`KeteranganTender`,`flag_closing`,`ppn_pelanggan`,
                                        `user_approve`,`time_approve`,`status_approve`,`user_edit`,`time_edit`,
                                        `time_batal`,`user_batal`,`created_at`,`created_by`,`modified_at`,
                                        `modified_by`,`status_retur`,`user_retur`,`time_retur`)
                                        SELECT `Cabang`,'".$noDokumen."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."',`Pengirim`,`NamaPengirim`,
                                                `Pelanggan`,`NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,
                                                `NamaTipePelanggan`,`NPWPPelanggan`,`KategoriPelanggan`,'".$no."',
                                                `Acu2`,`CaraBayar`,`CashDiskon`,(`ValueCashDiskon` * -1),`TOP`,`TglJtoOrder`,
                                                `Salesman`,`NamaSalesman`,`Rayon`,`NamaRayon`,'Usulan Retur','Retur',
                                                (`Gross` * -1),(`Potongan` * -1),(`Value` * -1),(`Ppn` * -1),(`LainLain` * -1),(`Materai` * -1),(`OngkosKirim` * -1),
                                                (`Total` * -1),`Keterangan1`,`Keterangan2`,`Barcode`,`QrCode`,`NoSO`,`NoFaktur`,
                                                `NoSP`,`statusPusat`,`TipeFaktur`,`NoIDPaket`,`KeteranganTender`,
                                                `flag_closing`,`ppn_pelanggan`,`user_approve`,`time_approve`,`status_approve`,
                                                `user_edit`,`time_edit`,`time_batal`,`user_batal`,'".date('Y-m-d H:i:s')."','".$this->session->userdata('username')."',
                                                '','','','',''
                                        FROM `trs_delivery_order_sales`
                                        where NODO ='".$no."'"); 
            $valid = $this->db->query("INSERT INTO `trs_delivery_order_sales_detail`
                                                (`Cabang`,`NoDO`,`TglDO`,`TimeDO`,`noline`,`Pengirim`,`NamaPengirim`,
                                                `Pelanggan`,`NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,`NamaTipePelanggan`,
                                                `NPWPPelanggan`,`KategoriPelanggan`,Acu,Acu2,`CaraBayar`,`CashDiskon`,
                                                `ValueCashDiskon`,`TOP`,`TglJtoOrder`,`Salesman`,`NamaSalesman`,`Rayon`,
                                                `NamaRayon`,`Status`,`TipeDokumen`,`KodeProduk`,`NamaProduk`,`UOM`,`Harga`,
                                                `QtySO`,`BonusSO`,`QtyDO`,`BonusDO`,`ValueBonus`,`DiscCab`,`ValueDiscCab`,
                                                `DiscCab_onf`,`ValueDiscCab_onf`,
                                                `DiscCabTot`,`ValueDiscCabTotal`,`DiscPrins1`,`ValueDiscPrins1`,`DiscPrins2`,
                                                `ValueDiscPrins2`,`DiscPrinsTot`,`ValueDiscPrinsTotal`,`DiscTotal`,
                                                `ValueDiscTotal`,`Gross`,`Potongan`,`Value`,`Ppn`,`LainLain`,`Total`,
                                                `Keterangan1`,`Keterangan2`,`Barcode`,`QrCode`,`NoSO`,`NoFaktur`,`DiscCabMax`,
                                                `KetDiscCabMax`,`DiscPrinsMax`,`KetDiscPrinsMax`,`COGS`,`TotalCOGS`,
                                                `BatchNo`,`ExpDate`,`TipeFaktur`,`NoIDPaket`,`KeteranganTender`,`NoBPB`,
                                                `flag_closing`,`picking_list`,`BatchNoDok`,`BatchTglDok`,`Prinsipal`,
                                                `Prinsipal2`,`Supplier`,`Supplier2`,`Pabrik`,`Farmalkes`,`QtyDO_awal`,
                                                `BonusDO_awal`,`created_at`,`created_by`,`status_retur`)
                                        SELECT `Cabang`,'".$noDokumen."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."',`noline`,`Pengirim`,`NamaPengirim`,
                                                `Pelanggan`,`NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,`NamaTipePelanggan`,
                                                `NPWPPelanggan`,`KategoriPelanggan`,'".$no."',`Acu2`,`CaraBayar`,`CashDiskon`,
                                                `ValueCashDiskon`,`TOP`,`TglJtoOrder`,`Salesman`,`NamaSalesman`,`Rayon`,
                                                `NamaRayon`,'Usulan Retur','Retur',`KodeProduk`,`NamaProduk`,`UOM`,
                                                `Harga`,`QtySO`,`BonusSO`,`QtyDO`,`BonusDO`,(`ValueBonus` * -1),`DiscCab`,
                                                (`ValueDiscCab` * -1),
                                                `DiscCab_onf`,(`ValueDiscCab_onf` * -1),`DiscCabTot`,(`ValueDiscCabTotal` * -1),`DiscPrins1`,
                                                (`ValueDiscPrins1` * -1),`DiscPrins2`,(`ValueDiscPrins2` * -1),`DiscPrinsTot`,
                                                (`ValueDiscPrinsTotal` * -1),`DiscTotal`,(`ValueDiscTotal` * -1),(`Gross` * -1),(`Potongan` * -1),
                                                (`Value` * -1),(`Ppn` * -1),(`LainLain` * -1),(`Total` * -1),`Keterangan1`,`Keterangan2`,`Barcode`,
                                                `QrCode`,`NoSO`,`NoFaktur`,`DiscCabMax`,`KetDiscCabMax`,`DiscPrinsMax`,
                                                `KetDiscPrinsMax`,(`COGS` * -1),(`TotalCOGS` * -1),`BatchNo`,`ExpDate`,`TipeFaktur`,
                                                `NoIDPaket`,`KeteranganTender`,`NoBPB`,`flag_closing`,`picking_list`,
                                                `BatchNoDok`,`BatchTglDok`,`Prinsipal`,`Prinsipal2`,`Supplier`,`Supplier2`,
                                                `Pabrik`,`Farmalkes`,`QtyDO_awal`,`BonusDO_awal`,'".date('Y-m-d H:i:s')."','".$this->session->userdata('username')."','Y'
                                        FROM `trs_delivery_order_sales_detail`
                                        where NODO ='".$no."'"); 

            if($valid){
                $valid = $this->db->query("update trs_delivery_order_sales 
                                            set Status = 'Open', 
                                                status_retur = 'Y',
                                                noretur = '".$noDokumen."',
                                                user_retur='".$this->session->userdata('username')."',
                                                time_retur='".date('Y-m-d H:i:s')."'
                                            where nodo ='".$no."'");
                $valid = $this->db->query("update trs_delivery_order_sales_detail 
                                            set Status = 'Open',
                                                status_retur = 'Y',
                                                noretur = '".$noDokumen."',
                                                retur_qtyDO = QtyDO,
                                                retur_bonusDO = BonusDO
                                            where nodo ='".$no."'");
            }
        }else{
            $valid=false;
        }
        return $valid;
    }

    public function listDatareturDO($search = null, $limit = null)
    {   
        if ($this->userGroup == 'BM' or $this->userGroup == 'KSA') {
            $query = $this->db->query("select  trs_delivery_order_sales.* from trs_delivery_order_sales  
                where trs_delivery_order_sales.Cabang = '".$this->cabang."'  and 
                      trs_delivery_order_sales.TipeDokumen ='Retur' and Status ='Usulan Retur' and ifnull(user_approve,'')=''
              $search order by TimeDO DESC $limit");
        }else{
            $query = $this->db->query("select  trs_delivery_order_sales.* from trs_delivery_order_sales  
                where trs_delivery_order_sales.Cabang = '".$this->cabang."'  and 
                      trs_delivery_order_sales.TipeDokumen ='Retur' 
              $search order by TimeDO DESC $limit");
        }
        return $query;
    }

    public function approvereturDO($NoDO=NULL,$Acu=NULL,$status=NULL){
        if($status == 'Approve'){
            $this->db->set("Status", "Retur");
            $this->db->set("status_retur", "Retur");
            $this->db->set("status_approve", "Approve");
            $this->db->set("time_approve",date('Y-m-d H:i:s'));
            $this->db->set("user_approve",$this->session->userdata('username'));
            $this->db->set('Keterangan1', "DO Retur By User");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update('trs_delivery_order_sales'); 

            $this->db->set("Status", "Retur");
            $this->db->set("status_retur", "Retur");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            if($valid){
                
                $cek_produk_double = $this->db->query("select KodeProduk,COUNT(KodeProduk)jumlah from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."' AND (QtySO = QtyDO AND BonusSO = BonusDO) AND Keterangan2 = 'Bidan' GROUP BY KodeProduk
                            HAVING COUNT(KodeProduk) > 1")->num_rows();

                if ($cek_produk_double > 0) {
                  $valid = $this->backStok_double($NoDO,$Acu);
                }else{
                  $valid = $this->backStok($NoDO,$Acu);
                }

                $valid = $this->db->query("update trs_delivery_order_sales 
                                            set Status = 'Closed', 
                                                status_retur = 'Y'
                                            where NoDO ='".$Acu."'");
                $valid = $this->db->query("update trs_delivery_order_sales_detail 
                                            set Status = 'Closed',
                                                status_retur = 'Y'
                                            where NoDO ='".$Acu."'");
            }

            // $this->db->set('NoDO', '');
            // $this->db->set('Status', "Usulan");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->set('alasan_status', "DO Retur By User");
            $this->db->where('NoDO', $NoDO);
            $valid = $this->db->update('trs_sales_order');

            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where('NoDO', $NoDO);
            $valid = $this->db->update('trs_sales_order_detail'); 
        }else{
            $this->db->set("Status", "Reject");
            $this->db->set("status_retur", "R");
            $this->db->set("status_approve", "Reject");
            $this->db->set("time_approve",date('Y-m-d H:i:s'));
            $this->db->set("user_approve",$this->session->userdata('username'));
            $this->db->set('Keterangan1', "DO Retur By User");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update('trs_delivery_order_sales'); 

            $this->db->set("Status", "Reject");
            $this->db->set("status_retur", "R");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update('trs_delivery_order_sales_detail');
            if($valid){
                $valid = $this->db->query("update trs_delivery_order_sales 
                                            set Status = 'Open', 
                                                status_retur = 'N',
                                                user_retur='',
                                                time_retur=''
                                            where NoDO ='".$Acu."'");
                $valid = $this->db->query("update trs_delivery_order_sales_detail 
                                            set Status = 'Open',
                                                status_retur = 'N',
                                                retur_qtyDO = 0,
                                                retur_bonusDO = 0
                                            where NoDO ='".$Acu."'");
                $valid = $this->db->query("update trs_kiriman 
                                            set StatusDO = 'Kembali'
                                            where NoDO ='".$Acu."'");
            }
        }
        
        return $valid;
    }

    public function prosesTerimaDO($NoKiriman = NULL,$NoDO = NULL,$status = NULL,$alasan = NULL)
    {   
        // START SAVE TABEL KIRIMAN
            $this->db->set("StatusKiriman", 'Closed');
            $this->db->set("StatusDO", $status);
            $this->db->set("Alasan", $alasan);
            $this->db->set("TglTerima", date("Y-m-d"));
            $this->db->set("TimeTerima", date("Y-m-d H:i:s")); 
            $this->db->set("updated_by", $this->session->userdata('username'));
            $this->db->set("updated_at", date("Y-m-d H:i:s"));
            $this->db->where("NoKiriman", $NoKiriman);
            $this->db->where("NoDO", $NoDO);
            $valid = $this->db->update("trs_kiriman");
            // END SAVE TABEL KIRIMAN

            // START UPDATE STATUS DO
            if ($status == "Terkirim") {     
                $nofak = $this->db->query("select ifnull(trs_faktur.NoDO,'') as 'NoDO' from trs_faktur
                                          where ifnull(trs_faktur.NoDO,'') = '".$NoDO."' and TipeDokumen ='Faktur' limit 1")->num_rows();            
                if($nofak == 0){
                        $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->result();
                        $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$NoDO."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->row();
                        $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                        $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 

                        //================ Running Number ======================================//
                        // $year = date('Y');
                        // $thn = substr($year,2,2);
                        // $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                        // $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                        // $kodeDokumen = $this->Model_main->kodeDokumen('Faktur');
                        // $data = $this->db->query("select max(right(NoFaktur,7)) as 'no' from trs_faktur where TipeDokumen='Faktur'  and length(NoFaktur)= 13 and Cabang = '".$this->cabang."' and YEAR(TimeFaktur) ='".$year."' and left(NoFaktur,2) = '".$thn."'")->result();
                        // if(empty($data[0]->no) || $data[0]->no == ""){
                        //   $lastNumber = 1000001;
                        // }else {
                        //   $lastNumber = $data[0]->no + 1;
                        // }
                        // $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                        //================= end of running number ========================================//

                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("NoFaktur", $header->NoDO);
                        $this->db->set("TglFaktur", $header->TglDO);
                        $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
                        $this->db->set("Pelanggan", $header->Pelanggan);
                        $this->db->set("NamaPelanggan", $header->NamaPelanggan);
                        $this->db->set("AlamatFaktur", $header->AlamatKirim);
                        $this->db->set("TipePelanggan", $header->TipePelanggan);
                        $this->db->set("NamaTipePelanggan", $header->NamaTipePelanggan);
                        $this->db->set("NPWPPelanggan", $header->NPWPPelanggan);
                        $this->db->set("ppn_pelanggan", $header->ppn_pelanggan);
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
                        $this->db->set("created_at", date('Y-m-d H:i:s'));
                        $this->db->set("created_by", $this->session->userdata('username'));
                        $valid =  $this->db->insert("trs_faktur");
                        $i=0;
                        $xx = $this->db->query("select Pelanggan from trs_faktur where NoFaktur = '".$header->NoDO."' limit 1")->row();
                        $headerPelanggan = $xx->Pelanggan;
                        foreach ($detail as $row) {
                            if($headerPelanggan == $row->Pelanggan){
                                $i++;
                                $this->db->set("Cabang", $this->cabang);
                                $this->db->set("NoFaktur", $header->NoDO);
                                $this->db->set("noline", $i);
                                $this->db->set("TglFaktur", $header->TglDO);
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
                                $this->db->set("created_at", date('Y-m-d H:i:s'));
                                $this->db->set("created_by", $this->session->userdata('username'));
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
                                                        a.KodeProduk = '".$row->KodeProduk."' and 
                                                        a.NoFaktur ='".$header->NoDO."'");
                                }

                            }
                                
                        }

                        $this->db->set('NoFaktur', $header->NoDO);
                        $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        $this->db->set("modified_by", $this->user);
                        $this->db->where('NoSO', $header->NoSO);
                        $this->db->update('trs_sales_order'); 

                        $this->db->set('NoFaktur', $header->NoDO);
                        $this->db->set("Status", "Closed");
                        $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        $this->db->set("modified_by", $this->user);
                        $this->db->set("status_validasi", "Y");
                        $this->db->set("time_validasi", date('Y-m-d H:i:s'));
                        $this->db->set("user_validasi", $this->user);
                        $this->db->where("NoDO", $params->NoDO[$key]);
                        $valid = $this->db->update('trs_delivery_order_sales');

                        $this->db->set('NoFaktur', $header->NoDO);
                        $this->db->set("Status", "Closed");
                        $this->db->set("status_validasi", "Y");
                        $this->db->set("modified_at", date('Y-m-d H:i:s'));
                        $this->db->set("modified_by", $this->user);
                        $this->db->where("NoDO", $params->NoDO[$key]);
                        $valid = $this->db->update('trs_delivery_order_sales_detail');   
                        // if($valid){
                        //     $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Faktur' and Cabang = '".$this->cabang."'");
                        // }
                }else{
                    $valid =FALSE;
                }
            }
            elseif ($status == "Kembali") {
                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $NoDO);
                $valid = $this->db->update('trs_delivery_order_sales'); 

                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $NoDO);
                $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }
        return $valid;
    }
    public function listDataKirimandetail($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and StatusKiriman = '".$status."'";
            $query = $this->db->query("select * from trs_kiriman where Cabang = '".$this->cabang."' $byStatus $search  Order by TimeKirim DESC $limit");
        }else{
            $query = $this->db->query("select * from trs_kiriman where Cabang = '".$this->cabang."' $byStatus $search  Order by TimeKirim DESC $limit");
        }
        

        return $query;
    }

    public function listcekDataKiriman($search = null, $limit = null)
    {   
        $query = $this->db->query("SELECT trs_kiriman.*,
                                            fakheader.faktur AS header,
                                            fakdetail.faktur AS 'detail'
                                        FROM trs_kiriman
                                        LEFT JOIN ( SELECT nodo,COUNT(IFNULL(nodo,'')) AS faktur FROM trs_faktur GROUP BY nodo ) AS fakheader 
                                        ON trs_kiriman.`NoDO` = fakheader.nodo
                                        LEFT JOIN ( SELECT nodo,COUNT(IFNULL(nodo,'')) AS faktur FROM trs_faktur_detail GROUP BY nodo ) AS fakdetail 
                                        ON trs_kiriman.`NoDO` = fakdetail.nodo
                                        WHERE Cabang ='".$this->cabang."' AND StatusKiriman = 'Closed' AND StatusDO ='Terkirim' AND (ifnull(fakheader.faktur,0) < 1 OR ifnull(fakdetail.faktur,0) < 1 )    $search  Order by TimeKirim DESC $limit");
        return $query;
    }
    public function prosesTerimaUlangDO($NoDO = NULL,$fakturheader = NULL,$fakturdetail = NULL)
    {   
         
                if($fakturheader == "Tidak"){
                        $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and ((status not in ('Batal','Reject')) or (status = 'Closed' and ifnull(status_retur,'')='N'))")->result();
                        $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$NoDO."'  and ((status not in ('Batal','Reject')) or  (status = 'Closed' and ifnull(status_retur,'')='N'))")->row();
                        $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                        $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 

                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("NoFaktur", $header->NoDO);
                        $this->db->set("TglFaktur", $header->TglDO);
                        $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
                        $this->db->set("Pelanggan", $header->Pelanggan);
                        $this->db->set("NamaPelanggan", $header->NamaPelanggan);
                        $this->db->set("AlamatFaktur", $header->AlamatKirim);
                        $this->db->set("TipePelanggan", $header->TipePelanggan);
                        $this->db->set("NamaTipePelanggan", $header->NamaTipePelanggan);
                        $this->db->set("NPWPPelanggan", $header->NPWPPelanggan);
                        $this->db->set("ppn_pelanggan", $header->ppn_pelanggan);
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
                        $this->db->set("created_at", date('Y-m-d H:i:s'));
                        $this->db->set("created_by", $this->session->userdata('username'));
                        $valid =  $this->db->insert("trs_faktur");
                        $i=0;
                        $xx = $this->db->query("select Pelanggan from trs_faktur where NoFaktur = '".$header->NoDO."' limit 1")->row();
                        $headerPelanggan = $xx->Pelanggan;
                        foreach ($detail as $row) {
                            if($headerPelanggan == $row->Pelanggan){
                                $i++;
                                $this->db->set("Cabang", $this->cabang);
                                $this->db->set("NoFaktur", $header->NoDO);
                                $this->db->set("noline", $i);
                                $this->db->set("TglFaktur", $header->TglDO);
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
                                $this->db->set("created_at", date('Y-m-d H:i:s'));
                                $this->db->set("created_by", $this->session->userdata('username'));
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
                                                        a.KodeProduk = '".$row->KodeProduk."' and 
                                                        a.NoFaktur ='".$header->NoDO."'");
                                }

                            }
                                
                        }
  
                        // if($valid){
                        //     $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Faktur' and Cabang = '".$this->cabang."'");
                        // }
                }else{
                    $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and ((status not in ('Batal','Reject')) or (status = 'Closed' and ifnull(status_retur,'')='N'))")->result();
                        $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$NoDO."'  and ((status not in ('Batal','Reject')) or  (status = 'Closed' and ifnull(status_retur,'')='N'))")->row();
                        $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                        $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 
                        $i=0;
                        $xx = $this->db->query("select Pelanggan from trs_faktur where NoFaktur = '".$header->NoDO."' limit 1")->row();
                        $headerPelanggan = $xx->Pelanggan;
                        foreach ($detail as $row) {
                            if($headerPelanggan == $row->Pelanggan){
                                $i++;
                                $this->db->set("Cabang", $this->cabang);
                                $this->db->set("NoFaktur", $header->NoDO);
                                $this->db->set("noline", $i);
                                $this->db->set("TglFaktur", $header->TglDO);
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
                                $this->db->set("created_at", date('Y-m-d H:i:s'));
                                $this->db->set("created_by", $this->session->userdata('username'));
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
                                                        a.KodeProduk = '".$row->KodeProduk."' and 
                                                        a.NoFaktur ='".$header->NoDO."'");
                                }

                            }
                                
                        }
                        
                }
            
        return $valid;
    }

    public function listvalidasiDOFaktur($tgl1=null,$tgl2=null,$tipe=null){
        if($tipe=='do'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`TglDO`,
                                            a.NoDO,a.`Status`,       
                                            a.TipeDokumen,a.`Gross`,
                                            a.`Potongan`,a.`Value`,
                                            a.`Ppn`,a.`Total` as 'TotalA',
                                            dd.`Status`,dd.TipeDokumen,
                                            dd.`Gross`,dd.`Potongan`,
                                            dd.`Value`,dd.`Ppn`,dd.`Total`
                                    FROM `trs_delivery_order_sales` a
                                    LEFT JOIN
                                    (
                                        SELECT a.`Cabang`,a.NoDO,
                                               a.`Status`,a.TipeDokumen,
                                                SUM(a.`Gross`) AS Gross,
                                                SUM(a.`Potongan`) AS Potongan,
                                                SUM(a.`Value`) AS `value`,
                                                SUM(a.`Ppn`) AS Ppn,
                                                SUM(a.`Total`) AS Total
                                        FROM `trs_delivery_order_sales_detail` a
                                        WHERE TglDO between '".$tgl1."' and '".$tgl2."'
                                        GROUP BY a.`Cabang`,a.`NoDO`) dd 
                                    ON a.`Cabang`=dd.`Cabang` AND a.`NoDO`= dd.`NoDO`
                                    WHERE TglDO between '".$tgl1."' and '".$tgl2."' and
                                        a.status not in ('Batal','Reject') and  
                                        IFNULL(a.`Total`,0) != IFNULL(dd.`Total`,0);");
        }elseif($tipe=='faktur'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`TglFaktur`,
                                              a.NoFaktur,a.`Status`,
                                              a.TipeDokumen,a.`Gross`,
                                              a.`Potongan`,a.`Value`,
                                              a.`Ppn`,a.`Total` as 'TotalA',
                                              dd.`Status` as 'StatusA',dd.TipeDokumen as 'TipeDokumenA',
                                              dd.`Gross`,dd.`Potongan`,
                                              dd.`Value`,dd.`Ppn`,
                                              dd.`Total`
                                       FROM `trs_faktur` a
                                       LEFT JOIN
                                       (
                                        SELECT a.`Cabang`,a.NoFaktur,
                                               a.`Status`,a.TipeDokumen,
                                               SUM(a.`Gross`) AS Gross,
                                               SUM(a.`Potongan`) AS Potongan,
                                               SUM(a.`Value`) AS `value`,
                                               SUM(a.`Ppn`) AS Ppn,
                                               SUM(a.`Total`) AS Total
                                        FROM `trs_faktur_detail` a
                                        WHERE a.TglFaktur between '".$tgl1."' and '".$tgl2."' 
                                        GROUP BY a.`Cabang`,a.`NoFaktur`) dd 
                                        ON a.`Cabang`=dd.`Cabang` AND a.`NoFaktur`= dd.`NoFaktur`
                                        WHERE a.TglFaktur between '".$tgl1."' and '".$tgl2."' AND 
                                              a.`TipeDokumen` IN ('Faktur','Retur') and 
                                              a.status not in ('Batal','Reject') and 
                                        (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 0;");
        }
        elseif($tipe=='cndn'){
            $query = $this->db->query("SELECT a.`Cabang`,
                                           a.NoFaktur,
                                           a.TglFaktur,
                                           a.TipeDokumen,
                                           a.Status,
                                           a.`Total`,
                                           dd.`Total` as 'Total_detail'
                                     FROM `trs_faktur` a
                                     LEFT JOIN
                                     (
                                        SELECT a.`Cabang`,a.NoDokumen,
                                               SUM(a.`Jumlah`) AS Total
                                        FROM `trs_faktur_cndn` a
                                        WHERE a.TanggalCNDN BETWEEN '".$tgl1."' AND '".$tgl2."' 
                                        GROUP BY a.`Cabang`,a.`NoDokumen`) dd 
                                     ON a.`Cabang`=dd.`Cabang` AND 
                                        a.`NoFaktur`= dd.`NoDokumen`
                                    WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."'  AND 
                                          a.`TipeDokumen` IN ('CN','DN') AND 
                                         (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;");
        }elseif($tipe=='header'){
            $query = $this->db->query("SELECT a.`Cabang`,a.NoDO,
                                        a.TglDO,a.TipeDokumen,
                                           a.Status,
                                           SUM(a.`Total`) AS 'Total',
                                           b.`NoFaktur`,
                                           b.TglFaktur,
                                           IFNULL(b.`Total`,0) AS 'TotalB'
                                    FROM `trs_delivery_order_sales` a
                                    LEFT JOIN
                                    (
                                       SELECT a.`Cabang`,
                                              a.`NoFaktur`,
                                              a.`TglFaktur`,
                                              SUM(a.`Total`) AS 'Total'
                                        FROM `trs_faktur` a
                                        WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                              a.`TipeDokumen` IN ('Faktur')
                                       GROUP BY a.`Cabang`,
                                              a.`NoFaktur`,a.`TglFaktur`)b 
                                    ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                                    WHERE a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                          IFNULL(a.status_retur,'N')='N' AND 
                                          a.`Status` ='Closed' 
                                    GROUP BY a.`Cabang`,a.NoDO,a.TglDO,
                                            a.TipeDokumen,a.Status
                                    HAVING Total != TotalB;");
        }
        elseif($tipe=='detail'){
            $query = $this->db->query("SELECT a.`Cabang`,a.NoDO,a.TglDO,
                                        a.TglDO,a.TipeDokumen,a.Status,
                                        SUM(a.`Total`) AS 'Total',
                                        b.`NoFaktur`,
                                        IFNULL(b.`Total`,0) AS 'TotalB'
                                     FROM `trs_delivery_order_sales_detail` a
                                     LEFT JOIN
                                     (
                                       SELECT a.`Cabang`,
                                              a.`NoFaktur`,
                                              a.`TglFaktur`,
                                              SUM(a.`Total`) AS 'Total'
                                        FROM `trs_faktur_detail` a
                                       WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                             a.`TipeDokumen` IN ('Faktur')
                                        GROUP BY a.`Cabang`,
                                              a.`NoFaktur`,a.`TglFaktur`)b 
                                    ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                                    WHERE a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                        IFNULL(a.status_retur,'N')='N' AND 
                                         a.`Status` ='Closed' 
                                    GROUP BY a.`Cabang`,a.NoDO,a.TglDO,
                                            a.TglDO,a.TipeDokumen,a.Status
                                    HAVING Total != TotalB;");
        }elseif($tipe=='dokirim'){
            $query = $this->db->query("SELECT Cabang,NoDO,
                                    TglDO,'header' AS 'Status' 
                                    FROM trs_delivery_order_sales
                                    WHERE STATUS IN ('Kirim','Open') AND
                                          IFNULL(status_retur,'N')='N' AND  
                                          nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim') AND 
                                          tgldo BETWEEN '".$tgl1."' AND '".$tgl2."' 
                                    UNION ALL
                                    SELECT DISTINCT Cabang,NoDO,TglDO,'detail' AS 'status' 
                                    FROM trs_delivery_order_sales_detail
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                      IFNULL(status_retur,'N')='N' AND  
                                      nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim') AND 
                                      tgldo BETWEEN '".$tgl1."' AND '".$tgl2."' ;");
        }elseif($tipe=='doopen'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`NoDO`,a.`TglDO`,
                                   a.`Status`,a.`status_retur`,
                                   b.NoKiriman,b.StatusKiriman,b.StatusDO
                            FROM trs_delivery_order_sales a 
                            LEFT JOIN 
                            ( SELECT b.`NoKiriman`,b.`NoDO`,b.`TglKirim`,b.`TglTerima`,b.`StatusKiriman`,b.`StatusDO` 
                            FROM trs_kiriman b
                            where b.TglKirim BETWEEN '".$tgl1."' AND '".$tgl2."' ) b
                            ON a.`NoDO` = b.NoDO
                            WHERE IFNULL(a.Status,'') = 'Open' AND a.tipedokumen='DO' 
                            AND a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."'");
        }elseif($tipe=='doclosed'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`NoDO`,a.`TglDO`,
                                   a.`Status`,a.`status_retur`,
                                   b.NoKiriman,b.StatusKiriman,b.StatusDO
                            FROM trs_delivery_order_sales a 
                            LEFT JOIN 
                            ( SELECT b.`NoKiriman`,b.`NoDO`,b.`TglKirim`,b.`TglTerima`,b.`StatusKiriman`,b.`StatusDO` 
                            FROM trs_kiriman b
                            where b.TglKirim BETWEEN '".$tgl1."' AND '".$tgl2."' ) b
                            ON a.`NoDO` = b.NoDO
                            WHERE IFNULL(a.Status,'') = 'Kirim' AND a.tipedokumen='DO' 
                            AND a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."';");
        }elseif($tipe=='saldo'){
            $query = $this->db->query("SELECT a.Cabang,a.NoFaktur,a.Value AS  valuefaktur,a.TglFaktur,a.TipeDokumen ,
                a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,
                (CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                FROM trs_faktur a
                LEFT JOIN (
                  SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                    SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                    CASE 
                      WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                      WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      ELSE (IFNULL(ValuePelunasan,0) )
                      END AS ValuePelunasanx
                     
                    FROM trs_pelunasan_detail 
                       WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                    UNION ALL
                    SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                    CASE 
                      WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                      WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      ELSE (IFNULL(ValuePelunasan,0) )
                      END AS ValuePelunasanx
                    FROM trs_pelunasan_giro_detail 
                       WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                    )xdat GROUP BY NomorFaktur
                )pel ON a.NoFaktur=pel.NomorFaktur
                 WHERE a.NoFaktur IN (
                  SELECT DISTINCT NomorFaktur FROM(
                  SELECT NomorFaktur
                  FROM trs_pelunasan_detail 
                     WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                     AND TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                  UNION ALL
                  SELECT NomorFaktur
                  FROM trs_pelunasan_giro_detail 
                     WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                     AND TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                     )xx
                ) 
                HAVING selisih != 0;");
        }
        return $query;
    }

    public function getDOHeaderDetail(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
         $query = $this->db->query("SELECT a.`Cabang`,
                                           a.NoDO,
                                           a.`Total`,
                                           dd.`Total`
                                    FROM `trs_delivery_order_sales` a
                                    LEFT JOIN
                                    (
                                        SELECT a.`Cabang`,a.NoDO,
                                               SUM(a.`Total`) AS Total
                                        FROM `trs_delivery_order_sales_detail` a
                                        WHERE TglDO between '".$satubulan_awal."' and '".$satubulan."'
                                        GROUP BY a.`Cabang`,a.`NoDO`) dd 
                                    ON a.`Cabang`=dd.`Cabang` AND a.`NoDO`= dd.`NoDO`
                                    WHERE TglDO between '".$satubulan_awal."' and '".$satubulan."' and a.status not in ('Batal','Reject') and
                                        IFNULL(a.`Total`,0) != IFNULL(dd.`Total`,0);")->num_rows();
        return $query;
    }

    public function getFakturHeaderDetail(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
         $query = $this->db->query("SELECT a.`Cabang`,
                                              a.NoFaktur,
                                              a.`Total`,
                                              dd.`Total`
                                       FROM `trs_faktur` a
                                       LEFT JOIN
                                       (
                                        SELECT a.`Cabang`,a.NoFaktur,
                                               SUM(a.`Total`) AS Total
                                        FROM `trs_faktur_detail` a
                                        WHERE a.TglFaktur between '".$satubulan_awal."' and '".$satubulan."' 
                                        GROUP BY a.`Cabang`,a.`NoFaktur`) dd 
                                        ON a.`Cabang`=dd.`Cabang` AND a.`NoFaktur`= dd.`NoFaktur`
                                    WHERE a.TglFaktur between '".$satubulan_awal."' and '".$satubulan."' and 
                                              a.`TipeDokumen` IN ('Faktur','Retur') and
                                              a.status not in ('Batal','Reject') and 
                                        (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;")->num_rows();
        return $query;
    }

    public function getCNDN(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
         $query = $this->db->query("SELECT a.`Cabang`,
                                           a.NoFaktur,
                                           a.`Total`,
                                            dd.`Total`
                                    FROM `trs_faktur` a
                                    LEFT JOIN
                                    (
                                        SELECT a.`Cabang`,a.NoDokumen,
                                               SUM(a.`Jumlah`) AS Total
                                        FROM `trs_faktur_cndn` a
                                        WHERE a.TanggalCNDN between '".$satubulan_awal."' and '".$satubulan."'
                                        GROUP BY a.`Cabang`,a.`NoDokumen`) dd 
                                        ON a.`Cabang`=dd.`Cabang` AND 
                                           a.`NoFaktur`= dd.`NoDokumen`
                                    WHERE a.TglFaktur between '".$satubulan_awal."' and '".$satubulan."' AND 
                                          a.`TipeDokumen` IN ('CN','DN') and 
                                          (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;")->num_rows();
        return $query;
    }

    public function getsaldopiutang(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("SELECT a.`Cabang`,a.`TglFaktur`,
                                       a.NoFaktur,a.`Status`,
                                       a.TipeDokumen,a.`Total`,
                                       IFNULL(a.`Saldo`,0) AS 'saldo',
                                       IFNULL(a.`saldo_giro`,0) AS 'saldo_giro',
                                       (IFNULL(a.`Saldo`,0) + IFNULL(a.`saldo_giro`,0)) AS 'saldoAll',
                                       IFNULL(ValuePelunasan,0) AS ValuePelunasan,
                                       IFNULL(ValuePelunasanGiro,0) AS ValuePelunasanGiro,
                                       ((IFNULL(a.`Total`,0)) - (IFNULL(ValuePelunasan,0)+ IFNULL(ValuePelunasanGiro,0))) AS sel, 
                                       xT
                                FROM `trs_faktur` a
                                      LEFT JOIN
                                      (
                                        SELECT Cabang,NomorFaktur,
                                               TipePelunasan,`Status`,
                                               TipeDokumen,
                                               SUM(`ValuePelunasan`) AS ValuePelunasan, 
                                               SUM(`ValuePelunasanGiro`) AS ValuePelunasanGiro, 
                                               xT 
                                         FROM (
                                              SELECT Cabang,NomorFaktur,
                                                      TipePelunasan,`Status`,
                                                      TipeDokumen,
                                                      CASE WHEN  TipeDokumen IN ('Retur','CN') 
                                                      THEN (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(`materai`,0)) * -1
                                                      ELSE (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(`materai`,0)) 
                                                      END AS `ValuePelunasanGiro` , 
                                                      0 AS 'ValuePelunasan',
                                                      'G' AS xT
                                                FROM `trs_pelunasan_giro_detail` 
                                                WHERE `status` IN ('Open') AND 
                                                      (( `TglPelunasan` BETWEEN '".$satubulan_awal."' and '".$satubulan."') OR ((`TglGiroCair`BETWEEN '".$satubulan_awal."' and '".$satubulan."')) )
                                              UNION ALL
                                              SELECT Cabang,NomorFaktur,
                                                     TipePelunasan,`Status`,
                                                     TipeDokumen,
                                                     0 AS 'ValuePelunasanGiro',
                                                     CASE WHEN  TipeDokumen IN ('Retur','CN')
                                                     THEN (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(`materai`,0)) * -1
                                                     ELSE (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(`materai`,0)) 
                                                     END AS `ValuePelunasan`, 
                                                     'B' AS xT
                                               FROM `trs_pelunasan_detail` 
                                               WHERE `status` IN ('Bayar Full','BayarFull','Cicilan','GiroCair','Giro Cair')  AND 
                                                     ((`TglPelunasan` BETWEEN '".$satubulan_awal."' and '".$satubulan."') OR ((`TglGiroCair` BETWEEN '".$satubulan_awal."' and '".$satubulan."')))                
                                                )xx GROUP BY Cabang,NomorFaktur) pel ON a.`NoFaktur`=pel.NomorFaktur 
                                         WHERE a.TglFaktur BETWEEN '".$satubulan_awal."' and '".$satubulan."'
                                        AND (((IFNULL(a.`Total`,0) - IFNULL(ValuePelunasan,0)) != IFNULL(a.`saldo_giro`,0)) OR ( a.`Status` = 'Open' AND (IFNULL(a.`Saldo`,0) + IFNULL(a.`saldo_giro`,0)) = 0 ))
                                        AND a.`Status` NOT IN ('Reject','Batal')
                                         HAVING sel != saldoAll;")->num_rows();
        return $query;
    }

    public function getDOFakturHeader(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
         $query = $this->db->query("SELECT a.`Cabang`,a.NoDO,a.`Total`,
                                            b.NoDO,b.`NoFaktur`,
                                            IFNULL(b.`Total`,0) AS 'TotalB'
                                    FROM `trs_delivery_order_sales` a
                                    LEFT JOIN
                                        (
                                            SELECT a.`Cabang`,
                                                a.NoDO,a.`NoFaktur`,
                                                a.`Total` 
                                            FROM `trs_faktur` a
                                            WHERE a.TglFaktur between '".$satubulan_awal."' and '".$satubulan."' AND 
                                                a.`TipeDokumen` IN ('Faktur'))b 
                                            ON a.`NoDO`=b.NoDO and a.`Cabang`=b.Cabang
                                    WHERE a.TglDO between '".$satubulan_awal."' and '".$satubulan."' AND 
                                        IFNULL(a.status_retur,'N')='N' and 
                                         a.`Status` ='Closed' and 
                                     a.`Total` != IFNULL(b.`Total`,0);")->num_rows();
        return $query;
    }

    public function getDOFakturDetail(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("SELECT a.`Cabang`,
                                        a.NoDO,
                                        SUM(a.`Total`) AS 'TotalA',
                                        b.`NoFaktur`,
                                        IFNULL(b.`Total`,0) AS 'TotalB'
                                    FROM `trs_delivery_order_sales_detail` a
                                    LEFT JOIN
                                    (
                                        SELECT a.`Cabang`,
                                            a.NoDO,a.`NoFaktur`,
                                            SUM(a.`Total`) AS 'Total' 
                                        FROM `trs_faktur_detail` a
                                        WHERE a.TglFaktur between '".$satubulan_awal."' and '".$satubulan."' AND 
                                                      a.`TipeDokumen` IN ('Faktur')
                                        GROUP BY a.`Cabang`,
                                            a.NoDO,a.`NoFaktur`)b 
                                                      ON a.`NoDO`=b.NoDO
                                    WHERE a.TglDO between '".$satubulan_awal."' and '".$satubulan."' and IFNULL(a.status_retur,'N')='N' AND a.`Status` ='Closed' 
                                    GROUP BY a.`Cabang`,
                                        a.NoDO,b.`NoFaktur`,
                                        b.`Total`
                                     HAVING TotalA != TotalB;")->num_rows();

        return $query;
    }
public function prosesfixdo($no = null)
    {   
        $query = $this->db->query("UPDATE trs_delivery_order_sales header
                                            left join ( SELECT NoDO,SUM(Gross) AS 'Gross',
                                                     SUM(Potongan) AS 'Potongan',
                                                     SUM(VALUE) AS 'VALUE',
                                                     SUM(Ppn) AS 'Ppn',
                                                     SUM(LainLain) AS 'LainLain',
                                                     SUM(Total) AS 'Total'
                                                FROM trs_delivery_order_sales_detail
                                                GROUP BY Nodo ) AS detail
                                            ON header.`NoDO` = detail.NoDO
                                            SET header.`Gross` = detail.Gross,
                                                header.Potongan = detail.Potongan,
                                                header.VALUE = detail.VALUE,
                                                header.Ppn = detail.Ppn,
                                                header.LainLain = detail.LainLain,
                                                header.Total = detail.Total
                                            WHERE header.`NoDO` = '".$no."';");

        return $query;
    }
    public function prosesfixfaktur($no = null)
    {   
        $query = $this->db->query("UPDATE trs_faktur header
                                            left join ( SELECT nofaktur,SUM(Gross) AS 'Gross',
                                                     SUM(Potongan) AS 'Potongan',
                                                     SUM(VALUE) AS 'VALUE',
                                                     SUM(Ppn) AS 'Ppn',
                                                     SUM(LainLain) AS 'LainLain',
                                                     SUM(Total) AS 'Total'
                                                FROM trs_faktur_detail
                                                GROUP BY nofaktur ) AS detail
                                            ON header.`nofaktur` = detail.nofaktur
                                            SET header.`Gross` = detail.Gross,
                                                header.Potongan = detail.Potongan,
                                                header.VALUE = detail.VALUE,
                                                header.Ppn = detail.Ppn,
                                                header.LainLain = detail.LainLain,
                                                header.Total = detail.Total,
                                                header.Saldo = detail.Total
                                            WHERE header.`nofaktur` = '".$no."' and 
                                            header.Status in ('Open','OpenDIH','Usulan') and 
                                            header.Total = header.Saldo;");

        return $query;
    }
    public function prosesfixfakturheder($no = null)
    {   
        $cek =$this->db->query("select * from trs_faktur where nofaktur ='".$no."'")->num_rows();
        $query ="";
        if($cek > 0){
            $query = $this->db->query("UPDATE trs_faktur faktur
                                            left join ( SELECT *
                                                FROM trs_delivery_order_sales ) AS do
                                            ON faktur.`nofaktur` = do.NoDO
                                            SET faktur.`Gross` = do.Gross,
                                                faktur.Potongan = do.Potongan,
                                                faktur.VALUE = do.VALUE,
                                                faktur.Ppn = do.Ppn,
                                                faktur.LainLain = do.LainLain,
                                                faktur.Total = do.Total,
                                                faktur.Saldo = do.Total
                                            WHERE faktur.`nofaktur` = '".$no."' and 
                                                 faktur.Status in ('Open','OpenDIH','Usulan') and 
                                            faktur.Total = header.Saldo;");
        }else{
            $query = $this->db->query("INSERT INTO `trs_faktur` (
                                          `Cabang`,`NoFaktur`,
                                          `TglFaktur`,`TimeFaktur`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatFaktur`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          `TipePajak`,`NoPajak`,
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,
                                          `Saldo`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`NoSP`,
                                          `statusPusat`,`TipeFaktur`,
                                          `NoIDPaket`,`KeteranganTender`,
                                          `ppn_pelanggan`,
                                          `created_at`,`created_by`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `TglDO`,`TimeDO`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatKirim`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          '','',
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoOrder`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,`Total`,
                                          `Keterangan1`,`Keterangan2`,
                                          `Barcode`,`QrCode`,
                                          `NoSO`,`NoDO`,
                                          `NoSP`,`statusPusat`,
                                          `TipeFaktur`,`NoIDPaket`,
                                          `KeteranganTender`,`ppn_pelanggan`,
                                          '".date('Y-m-d H:i:s')."','".$this->user."',`acu2`
                                        FROM `trs_delivery_order_sales`
                                        where NoDo = '".$no."' and IFNULL(trs_delivery_order_sales.status_retur,'N')='N' AND trs_delivery_order_sales.`Status` ='Closed'  ;
                                        ");
        $this->prosesfixfakturdetail($no);
        }
        return $query;
    }

    public function prosesfixfakturdetail($no = null)
    {   
        $query =false;
        $cekheader =$this->db->query("select * from trs_faktur where nofaktur ='".$no."'")->num_rows();
        if($cekheader > 0){
            $cek =$this->db->query("select * from trs_faktur_detail where nofaktur ='".$no."'")->num_rows();
            if($cek > 0){
                $del= $this->db->query("delete from trs_faktur_detail where nofaktur ='".$no."'");
            }
            $query = $this->db->query("INSERT INTO `trs_faktur_detail` (
                                          `Cabang`,`NoFaktur`,
                                          `noline`,`TglFaktur`,
                                          `TimeFaktur`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatFaktur`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyFaktur`,`BonusFaktur`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,
                                          `sisa_retur`,`created_at`,
                                          `created_by`,`kota`,
                                          `telp`,`tipe_2`,
                                          `region`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `noline`,`TglDO`,
                                          `TimeDO`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatKirim`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoOrder`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyDO`,`BonusDO`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,0,
                                          '".date('Y-m-d H:i:s')."',
                                          '".$this->user."',`kota`,
                                          `telp`,`tipe_2`,`region`,`acu2`
                                        FROM `trs_delivery_order_sales_detail`
                                        where NoDo = '".$no."' and IFNULL(trs_delivery_order_sales_detail.status_retur,'N')='N' AND trs_delivery_order_sales_detail.`Status` ='Closed'");
        }
        
        return $query;
    }
    public function prosesfixcndn($no = null)
    {   
        $query = $this->db->query("UPDATE trs_faktur header
                                    left join ( SELECT NoDokumen,
                                                SUM(`Jumlah`) as 'jumlah'
                                            FROM trs_faktur_cndn
                                            GROUP BY NoDokumen ) AS cn
                                    ON header.`nofaktur` = cn.NoDokumen
                                    SET header.Potongan = cn.jumlah,
                                        header.VALUE = cn.jumlah,
                                        header.Total = cn.jumlah,
                                        header.Saldo = cn.jumlah
                                    WHERE header.`nofaktur` = '".$no."';");
        return $query;
    }

    public function prosesfixsaldo($no = null)
    {   
        $query = $this->db->query("SELECT a.Cabang,a.NoFaktur,
                                    a.Value AS 'valuefaktur',a.TglFaktur,a.TipeDokumen ,a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,(CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                                     WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                                    ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                                    FROM trs_faktur a
                                    LEFT JOIN (
                                      SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                                        SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                                        CASE 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          ELSE (IFNULL(ValuePelunasan,0) )
                                          END AS ValuePelunasanx
                                         
                                        FROM trs_pelunasan_detail 
                                           WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                        UNION ALL
                                        SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                                        CASE 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          ELSE (IFNULL(ValuePelunasan,0) )
                                          END AS ValuePelunasanx
                                        FROM trs_pelunasan_giro_detail 
                                           WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                        )xdat GROUP BY NomorFaktur
                                    )pel ON a.NoFaktur=pel.NomorFaktur
                                     WHERE a.NoFaktur = '".$no."'; ")->row();
        $total = $query->Total;
        $saldo = $query->Saldo;
        $saldogiro = $query->saldo_giro;
        $valuepelunasan = $query->ValuePelunasanx;
        $saldoakhir = $query->saldoHt;
        $selisih = $query->selisih; 

        if(($total == $valuepelunasan) and ($selisih != 0)){
            $cekdih = $this->db->query("SELECT * 
                                    FROM trs_pelunasan_detail 
                                    WHERE nomorfaktur ='".$no."' AND SaldoAkhir = 0")->row();
            if($cekdih->TipePelunasan == 'Cash'){
                $status = "LunasCash";
            }elseif($cekdih->TipePelunasan == 'Transfer'){
                $status = "LunasTransfer";
            }elseif($cekdih->TipePelunasan == 'Giro'){
                $status = "LunasGiro";
            }
            $output = $this->db->query("update trs_faktur 
                                        set status ='".$status."',
                                            Saldo = 0 
                                        where nofaktur = '".$no."'");
        }
        if($total != $valuepelunasan){
            $output = $this->db->query("update trs_faktur 
                                        set Saldo = '".$saldoakhir."',
                                            Status = 'Open' 
                                        where nofaktur = '".$no."'");
        }
        return $output;
    }

    public function prosesfixdokirim($no = null,$status=NULL)
    {   
        $query =false;
        if($status == "header"){
            $query = $this->db->query("UPDATE trs_delivery_order_sales a
                set Status ='Closed'
                where nodo ='".$no."' and IFNULL(a.status_retur,'N')='N';");
        }elseif($status == "detail"){
            $query = $this->db->query("UPDATE trs_delivery_order_sales_detail a
                set Status ='Closed'
                where nodo ='".$no."' and IFNULL(a.status_retur,'N')='N';");
        }
        return $query;
    }
    public function listRegisterFaktur($bySearch=null,$byLimit=null,$tgl1=null,$tgl2=null){
        $query = $this->db->query("SELECT a.`Cabang`,a.`TglDO`,
                                            a.NoDO,
                                            a.NoSO,
                                            a.`Status`,       
                                            a.TipeDokumen,
                                            a.TimeDO,
                                            a.Pelanggan,
                                            a.NamaPelanggan,
                                            concat(a.Pelanggan,'~',a.NamaPelanggan) as 'Pelanggan',
                                            a.status_validasi
                                    FROM `trs_delivery_order_sales` a
                                    WHERE TglDO between '".$tgl1."' and '".$tgl2."' and a.TipeDokumen ='DO' and 
                                        a.status not in ('Batal','Reject') and  
                                        ifnull(a.status_validasi,'N') ='N' $bySearch 
                                    order by a.`TglDO`,a.NoDO ASC $byLimit;");
        
        return $query;
    }

    public function ProsesRegisterFaktur($no = null)
    {   
        $this->db->set("status_validasi", "Y");
        $this->db->set("time_validasi", date('Y-m-d H:i:s'));
        $this->db->set("user_validasi", $this->user);
        $this->db->where("NoDO", $no);
        $valid = $this->db->update('trs_delivery_order_sales');
        $this->db->set("status_validasi", "Y");
        $this->db->set("modified_at", date('Y-m-d H:i:s'));
        $this->db->set("modified_by", $this->user);
        $this->db->where("NoDO", $no);
        $valid = $this->db->update('trs_delivery_order_sales_detail'); 
        return $valid;
    }
    public function listDataRegisterFaktur($bySearch=null,$byLimit=null,$tgl1=null,$tgl2=null){
        $query = $this->db->query("SELECT a.`Cabang`,a.`TglDO`,
                                            a.NoDO,
                                            a.NoSO,
                                            a.`Status`,       
                                            a.TipeDokumen,
                                            a.TimeDO,
                                            a.Pelanggan,
                                            a.NamaPelanggan,
                                            concat(a.Pelanggan,'~',a.NamaPelanggan) as 'Pelanggan',
                                            a.status_validasi
                                    FROM `trs_delivery_order_sales` a
                                    WHERE TglDO between '".$tgl1."' and '".$tgl2."' and a.TipeDokumen ='DO' and 
                                        a.status not in ('Batal','Reject') and  
                                        ifnull(a.status_validasi,'N') ='Y' $bySearch 
                                    order by a.`TglDO`,a.NoDO ASC $byLimit;");
        
        return $query;
    }
    public function PresentaseRegisterFaktur($tgl1=null,$tgl2=null){
        $blmsp = $this->db->query("select count(NoDO) as 'blmsp'
                                    from trs_delivery_order_sales 
                                    where TglDO between '".$tgl1."' and '".$tgl2."' and ifnull(status_validasi,'') ='N' and TipeDokumen ='DO'")->row();
        $sdhsp = $this->db->query("select count(NoDO) as 'sdhsp'
                                    from trs_delivery_order_sales 
                                    where TglDO between '".$tgl1."' and '".$tgl2."' and ifnull(status_validasi,'') ='Y' and TipeDokumen ='DO'")->row();
        $tfaktur = $this->db->query("select count(NoDO) as 'totfaktur'
                                    from trs_delivery_order_sales 
                                    where TglDO between '".$tgl1."' and '".$tgl2."'  and TipeDokumen ='DO'")->row();
        $blmsppersen = round((($blmsp->blmsp/$tfaktur->totfaktur) * 100),2) .' %';
        $sdhsppersen = round((($sdhsp->sdhsp/$tfaktur->totfaktur) * 100),2) .' %';
        $totfaktur = $tfaktur->totfaktur;
        $arr_query = array(
              "blmsp" => $blmsppersen,
              "sdhsp" => $sdhsppersen,
              "tfaktur" => $totfaktur,
           );

        return $arr_query;
    }

}