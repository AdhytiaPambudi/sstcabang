<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_bpb extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function PR()
    {   
        // $query = $this->db->query("select No_PR, Cabang from(select No_PR, Cabang from trs_po_header where Status_PO='open' union all select NoPR as No_PR, Cabang from trs_terima_barang_detail where QtyPO > Qty and Cabang ='".$this->session->userdata('cabang')."') t group by No_PR")->result();

        $query = $this->db->query("select NoPR as No_PR, Cabang from trs_delivery_order_header where Status='open' group by No_PR")->result();

        return $query;
    }

    public function getPRPO($no = NULL)
    {   
        // $query = $this->db->query("select a.*, b.No_Usulan, b.Tipe from trs_po_detail a, trs_po_header b where a.No_PR = b.No_PR and b.No_PR = '".$no."' and a.Status_PO = 'Open'")->result();

        $byNo = "";
        $query = "";
        if (!empty($no)) {
            $byNo = "where NoPR = '".$no."'";        
            $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();
        }

        return $query;
    } 

    public function getBPBPR($no = NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {
            $byNo = "where NoPR = '".$no."'";            
            $query = $this->db->query("select * from trs_terima_barang_detail ".$byNo)->result();
            // $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();
        }

        return $query;
    } 
    public function dataBPB($no = NULL)
    {
        if (!empty($no)) {         
            $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();
        }
        return $query;
    } 

    public function getCounterBPB($cab = NULL)
    {
        $c = $this->db->query("select Counter from mst_counter where Aplikasi = 'BPB' and Cabang = '".$cab."' limit 1");
        if ($c->num_rows() == 0) {
            $query = 1000001;
        }
        else{
            $t = $c->row();
            $query = intval($t->Counter) + 1;
        }

        return $query;
    } 

    public function saveData($params = null, $name1 = null, $name2 = null)
    {
        $valid = false;
        $x = 1;
        $piutang = 0;
        $totGross = 0;
        $totPotongan = 0;
        $totValue = 0;
        $totPPN = 0;
        $totTotal = 0;
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $expld = explode("/", $params->nousulan);
        $statusHeader = "Closed";
        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."' limit 1")->row();
        $c = $this->db->query("select Counter from mst_counter where Aplikasi = 'BPB' and Cabang = '".$params->cabang."' limit 1"); 
        if ($c->num_rows() == 0) {
            $this->db->query("insert into mst_counter (Aplikasi, Cabang, Counter) values ('BPB', '".$params->cabang."', 1000000)");
            $Counter = 1000001;
        }
        else{                            
            $r = $c->row();
            $Counter = intval($r->Counter) + 1;
        }
        $nomorbpb = 'BPB/'.$expld[0].'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$Counter;

            foreach ($params->produk as $key => $value) 
            {
                // if ($params->Kategori[$key] == $distinct[$kunci]) {  JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                if (!empty($params->produk[$key]) and $params->qtyterima[$key] > 0) {
                    $expld = explode("~", $params->produk[$key]);
                    $Produk = $expld[0];
                    $NamaProduk = $expld[1];

                    $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$Produk."' limit 1")->row(); 
                    $banyak = $params->qtyterima[$key] + $params->bonus[$key];

                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Prinsipal", $params->prinsipal);
                    // $this->db->set("NamaPrinsipal", $);
                    $this->db->set("Supplier", $params->supplier);
                    // $this->db->set("NamaSupplier", $);
                    $this->db->set("Pabrik", $qproduk->Pabrik);
                    $this->db->set("NoUsulan", $params->nousulan);
                    $this->db->set("NoPR", $params->pr);
                    $this->db->set("NoPO", $params->nopo);
                    $this->db->set("Tipe", $params->tipe);
                    $this->db->set("NoDokumen", $nomorbpb);
                    // $this->db->set("NoAcuDokumen", $);
                    $this->db->set("TglDokumen", date('Y-m-d'));
                    $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("Status", $params->status);
                    $this->db->set("NoSJ", $params->nosj);
                    $this->db->set("NOBEX", $params->nobex);
                    $this->db->set("NoInv", $params->noinvoice);
                    $this->db->set("Produk", $Produk);
                    $this->db->set("NamaProduk", $NamaProduk);
                    $this->db->set("Satuan", $params->satuan[$key]);
                    $this->db->set("Keterangan", $params->keterangan);
                    // $this->db->set("Penjelasan", $);
                    $this->db->set("QtyPO", $params->qtypesan[$key]);
                    $this->db->set("Qty", $params->qtyterima[$key]);
                    $this->db->set("Bonus", $params->bonus[$key]);
                    $this->db->set("Banyak", $banyak);
                    $this->db->set("Disc", $params->diskon[$key]);
                    // $this->db->set("DiscT", $);
                    $this->db->set("HrgBeli", $params->hargabeli[$key]);
                    $this->db->set("BatchNo", $params->batchno[$key]);
                    $this->db->set("ExpDate", $params->expdate[$key]);
                    $this->db->set("HPC", $params->hpc[$key]);
                    $this->db->set("HPC1", $params->hpcawal[$key]);
                    $this->db->set("Harga_Beli_Pst", $params->hargabelipusat[$key]);
                    $this->db->set("HPP", $params->hpp[$key]);
                    $this->db->set("Disc_Pst", $params->diskonpusat[$key]);
                    
                    $gross = $params->qtyterima[$key] * $params->hargabeli[$key];
                    $diskon = $gross * ( $params->diskon[$key] / 100 );
                    $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
                    $value = $gross - $diskon;
                    $ppn = $value * ( 10 / 100 );
                    $total = $gross + $ppn - $diskon;

                    $totGross = $totGross + $gross;
                    $totPotongan = $totPotongan + $potongan;
                    $totValue = $totValue + $value;
                    $totPPN = $totPPN + $ppn;
                    $totTotal = $totTotal + $total;

                    $this->db->set("Gross", $gross);
                    $this->db->set("Potongan", $potongan);
                    $this->db->set("Value", $value);
                    $this->db->set("PPN", $ppn);
                    $this->db->set("Total", $total);
                    // $this->db->set("Counter", $);

                    $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                    $this->db->set("UserAdd", $this->session->userdata('username'));

                    $result = substr($params->nopo, 0, 2);
                    if ($result == "PO") {   

                        $this->db->set("Status", $params->status);
                    }
                    elseif ($result == "DO") {

                        $this->db->set("Status", "Closed");
                    }
                    $valid = $this->db->insert('trs_terima_barang_detail'); 

                    if ($result == "PO") {

                        $this->db->set("Status_PO", 'Closed');
                        $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
                        $this->db->where("Produk", $Produk);
                        $this->db->where("No_PO", $params->nopo);
                        $valid = $this->db->update('trs_po_detail');

                    }
                    elseif ($result == "DO") {

                        $this->db->set("Status", 'Closed');
                        $this->db->where("Produk", $Produk);
                        $this->db->where("NoDokumen", $params->nopo);
                        $valid = $this->db->update('trs_delivery_order_detail');

                    }
                }
                else{
                    $statusHeader = "Open";
                }
            }

            $ID_Paket = $this->db->query("select NoIDPaket, Pelanggan from trs_delivery_order_header where NoDokumen = '".$params->nopo."' limit 1")->row();
            $this->db->set("Cabang", $params->cabang);
            $this->db->set("Prinsipal", $params->prinsipal);
            // $this->db->set("NamaPrinsipal", $);
            $this->db->set("Supplier", $params->supplier);
            // $this->db->set("NamaSupplier", $);
            $this->db->set("NoUsulan", $params->nousulan);
            $this->db->set("NoPR", $params->pr);
            $this->db->set("NoPO", $params->nopo);
            $this->db->set("Tipe", $params->tipe);
            $this->db->set("NoDokumen", $nomorbpb);
            // $this->db->set("NoAcuDokumen", $);
            $this->db->set("TglDokumen", date('Y-m-d'));
            $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
            $this->db->set("Attach1", $name1);
            $this->db->set("Attach2", $name2);
            $this->db->set("NoSJ", $params->nosj);
            $this->db->set("NoBEX", $params->nobex);
            $this->db->set("NoInv", $params->noinvoice);
            $this->db->set("Keterangan", $params->keterangan);
            // $this->db->set("Penjelasan", $);
            $this->db->set("Gross", $totGross);
            $this->db->set("Potongan", $totPotongan);
            $this->db->set("Value", $value);
            $this->db->set("PPN", $totPPN);
            $this->db->set("Total", $totTotal);
            $this->db->set("NoIDPaket", $ID_Paket->NoIDPaket);
            $this->db->set("Pelanggan", $ID_Paket->Pelanggan);

            $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
            $this->db->set("UserAdd", $this->session->userdata('username'));
            $result = substr($params->nopo, 0, 2);
                    if ($result == "PO") {                        
                        $this->db->set("Status", "Open");
                    }
                    elseif ($result == "DO") {
                        $this->db->set("Status", "Closed");
                    }
            $valid = $this->db->insert('trs_terima_barang_header'); 

            if ($valid) {                        
                $this->db->query("update mst_counter set Counter = ".$Counter." where Aplikasi = 'BPB' and Cabang = '".$params->cabang."'");
            }

            if ($result == "PO") {                  

                        $this->db->set("Status_PO", $statusHeader);
                        $this->db->set("Modified_Time", date('Y-m-d H:i:s'));            
                        $this->db->set("Modified_User", $this->session->userdata('username'));
                        $this->db->where("No_PO", $params->nopo);
                        $valid = $this->db->update('trs_po_header');
            }
            elseif ($result == "DO") {

                $this->db->set("Status", $statusHeader);
                $this->db->set("TimeEdit", date('Y-m-d H:i:s'));            
                $this->db->set("UserEdit", $this->session->userdata('username'));
                $this->db->where("NoDokumen", $params->nopo);
                $valid = $this->db->update('trs_delivery_order_header');
            }
            
            return $nomorbpb;
        // } JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
    }

    public function setStok($params = NULL, $no = NULL)
    {       
        $prdct = array_unique($params->produk);
        $summary = 0;
        $prins = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."'")->row();        
        foreach ($prdct as $kunci => $nilai) {
            if (!empty($prdct[$kunci])) {
                $split = explode("~", $prdct[$kunci]);
                $product = $split[0];
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product."'")->row();
                foreach ($params->produk as $key => $value) {
                    $expld = explode("~", $params->produk[$key]);
                    $produk = $expld[0];
                    if ($produk == $product) {
                        $summary = $params->qtyterima[$key];
                        $valuestok = $params->valuebpb[$key];
                        $UnitCOGS = $params->hpc[$key];
                    }
                }

                // Update Gudang Git
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product."' and Cabang='Pusat' and Gudang='Git' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();
                    $UnitStok = $invsum->UnitStok - $summary;
                    $valuestok2 = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok2." where KodeProduk='".$product."' and Cabang='Pusat' and Gudang='Git' limit 1");
                }
                // else{                    
                //     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$product."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Git', '0.000', '".$UnitCOGS."', '0.000')");   
                // }

                // Update Gudang Baik
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product."' and Cabang='".$this->cabang."' and Gudang='Baik' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok2 = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.",  ValueStok = ".$valuestok2." where KodeProduk='".$product."' and Cabang='".$this->cabang."' and Gudang='Baik' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$product."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$UnitCOGS."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($params->produk as $key => $value) {            
            if (!empty($params->produk[$key])) {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $params->qtyterima[$key] * $params->hpc[$key];
                $UnitStok = $params->qtyterima[$key];
                    // $this->db->query("update trs_invsum set UnitStok = ".$UnitStok." where KodeProduk='".$data->Produk."' and Cabang='".$this->cabang."' and Gudang='Baik' limit 1");

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$params->qtyterima[$key]."', '".$valuestok."', '".$params->batchno[$key]."', '".$params->expdate[$key]."', 'Baik', 'BPB', '".$no."', '-')");
            }
        }

        // save inventori detail
        foreach ($params->produk as $key => $value) {         
            if (!empty($params->produk[$key])) {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                // $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$params->cabang."' and Gudang='Baik' limit 1")->row();

                // Update Gudang Git
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='Pusat' and BatchNo='".$params->batchno[$key]."' and ExpDate='".$params->expdate[$key]."' and Gudang = 'Git' limit 1");
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $stok = $dt->UnitStok;
                    $UnitStok = $stok - $params->qtyterima[$key];
                    $cogs = $dt->UnitCOGS;
                    $ValueStok = $dt->UnitCOGS * $UnitStok;

                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $ValueStok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Cabang", 'Pusat');
                    $this->db->where("Gudang", 'Git');
                    $this->db->where("BatchNo", $params->batchno[$key]);
                    $this->db->where("ExpDate", $params->expdate[$key]);
                    $valid = $this->db->update('trs_invdet');
                }
                // else{
                //     $valuestok = $params->qtyterima[$key] * $params->hpc[$key];                
                //     $this->db->query("insert into trs_invdet (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS) values ('".date('Y')."', 'Pusat', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$params->qtyterima[$key]."', '".$valuestok."', '".$params->batchno[$key]."', '".$params->expdate[$key]."', 'Git', '".$params->hpc[$key]."')");
                // }


                // Update Gudang Baik
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$params->batchno[$key]."' and ExpDate='".$params->expdate[$key]."' and Gudang = 'Baik' limit 1");
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $stok = $dt->UnitStok;
                    $UnitStok = $stok + $params->qtyterima[$key];
                    $cogs = $dt->UnitCOGS;
                    $ValueStok = $dt->UnitCOGS * $UnitStok;

                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $ValueStok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Cabang",  $this->cabang);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("BatchNo", $params->batchno[$key]);
                    $this->db->where("ExpDate", $params->expdate[$key]);
                    $valid = $this->db->update('trs_invdet');
                }
                else{
                    $valuestok = $params->qtyterima[$key] * $params->hpc[$key];                
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS) values ('".date('Y')."', '".$this->cabang."', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$params->qtyterima[$key]."', '".$valuestok."', '".$params->batchno[$key]."', '".$params->expdate[$key]."', 'Baik', '".$params->hpc[$key]."')");
                }                
            }
        }

    }

    public function prosesData($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Berhasil' where NoDokumen = '".$no."'");

            $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();
            $cek = $this->db2->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_terima_barang_detail', $r); // insert each row to another table
                }
            }
            else{
                foreach($query as $r) { // loop over results
                    $this->db2->where('Produk', $r->Produk);
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_detail', $r);
                }
            }

            $query2 = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_terima_barang_header', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('NoDokumen', $no);
                $this->db2->update('trs_terima_barang_header', $query2);
            }

            // Update Status PO
            $this->db2->set('Status_PO', 'Closed');
            $this->db2->where('No_PO', $query2->NoPO);
            $this->db2->update('trs_po_header');

            $this->db2->set('Status_PO', 'Closed');
            $this->db2->where('No_PO', $query2->NoPO);
            $this->db2->update('trs_po_detail');


            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Gagal' where NoDokumen = '".$no."'");
            return FALSE;
        }
    }

    public function listData($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select Tipe, NoDokumen, NoPO, NoPR, NoUsulan, Prinsipal, Supplier, Total, Status, statusPusat from trs_terima_barang_header where Cabang = '".$this->session->userdata('cabang')."' $byID");

        return $query;
    }

    public function updateDataBPBPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  

        if ($this->db2->conn_id == TRUE) {
                $data = $this->db2->query("select * from trs_terima_barang_header where Cabang = '".$this->cabang."'")->result();
                foreach ($data as $dt) {
                    $query = $this->db2->query("select * from trs_terima_barang_detail where NoDokumen = '".$dt->NoDokumen."'")->result();
                    foreach($query as $r) { // loop over results
                            $cek = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$r->NoDokumen."' and Produk = '".$r->Produk."'")->num_rows();
                            if ($cek == 0) {
                                $test = $this->db->insert('trs_terima_barang_detail', $r);
                            }
                            else{
                                $this->db->where('Produk', $r->Produk);
                                $this->db->where('NoDokumen', $r->NoDokumen);
                                $this->db->update('trs_terima_barang_detail', $r);
                            }
                    }

                    $cek = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$r->NoDokumen."'")->num_rows();
                    if ($cek == 0) {
                        $this->db->insert('trs_terima_barang_header', $dt); // insert each row to another table
                    }
                    else{
                        $this->db->where('NoDokumen', $dt->NoDokumen);
                        $this->db->update('trs_terima_barang_header', $dt);
                    }
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }
}