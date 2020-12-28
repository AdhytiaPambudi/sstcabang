<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_mutasi extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->model('Model_main');
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }

    public function getHargaProduk($kode = null)
    {   
        $query = $this->db->query("select HNA from mst_harga where Produk = '".$kode."' limit 1")->row();

        return $query;
    }

    public function saveData($params = null)
    {
        $valid = false;
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Mutasi Koreksi');
        $data = $this->db->query("select max(right(no_koreksi,7)) as 'no' from trs_mutasi_koreksi where Cabang = '".$this->cabang."' and length(no_koreksi) = 13 and YEAR(tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == "" ){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        $i=0;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                $val=true;
                $valid=false;
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $namaProduk = $expld[1];
                if($params->batch[$key] == ""){
                    $val=false;
                }
                if($params->docstok[$key] == ""){
                    $val=false;
                }
                if($params->batch[$key] != "" and $params->docstok[$key] != ""){
                    if($params->qty[$key] < 0){
                        $cekbatch = $this->db->query("select KodeProduk,sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where KodeProduk ='".$produk."' and Gudang='".$params->gudang."' and tahun ='".date('Y')."' and BatchNo='".$params->batch[$key]."' and NoDokumen ='".$params->docstok[$key]."'")->row();
                        $qtybatch = $params->qty[$key] * -1;
                        if($qtybatch > $cekbatch->UnitStok){
                            $val=false;
                        }
                    }   
                } 
                if($val==true){
                    $i++;
                    $this->db->set("cabang", $params->cabang);
                    $this->db->set("tanggal", date("Y-m-d"));
                    $this->db->set("no_koreksi", $noDokumen);
                    $this->db->set("noline", $i);
                    $this->db->set("catatan", $params->catatan);
                    $this->db->set("tipe_koreksi", $params->tipe);
                    $this->db->set("gudang", $params->gudang);
                    $this->db->set("produk", $produk);
                    $this->db->set("nama_produk", $namaProduk);
                    $this->db->set("qty", $params->qty[$key]);
                    $this->db->set("harga", $params->harga[$key]);
                    $this->db->set("value", $params->qty[$key] * $params->harga[$key]);
                    $this->db->set("batch_detail", $params->batchdetail[$key]);
                    $this->db->set("batch", $params->batch[$key]);
                    $this->db->set("exp_date", $params->expdate[$key]);
                    $this->db->set("NoDocStok", $params->docstok[$key]);
                    $this->db->set("stok", $params->stok[$key]);
                    $this->db->set("status", "open");
                    $this->db->set("created_by", $this->user);
                    $this->db->set("created_at", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_mutasi_koreksi");

                    $this->setStok('', 'Koreksi', $params->cabang,$produk, $params->qty[$key], $params->harga[$key], $params->batch[$key], $params->expdate[$key],$params->docstok[$key],$params->gudang);
                }
                
            }
        }
            
        // Update no dokumen
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Mutasi Koreksi' and Cabang = '".$this->cabang."'");
        }
            
        return $valid;
    }

    public function saveDataBatch($params = null)
    {
        $valid = false;
        // $no = "";
        // $no = $this->Model_main->noDokumen('Mutasi Batch');
         //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Mutasi Batch');
        $data = $this->db->query("select max(right(nodokumen,7)) as 'no' from trs_mutasi_batch where Cabang = '".$this->cabang."' and length(nodokumen) = 13 and YEAR(tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == "" ){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $namaProduk = $expld[1]; 

                $this->db->set("cabang", $params->cabang);
                $this->db->set("tanggal", date("Y-m-d"));
                $this->db->set("nodokumen", $noDokumen);
                $this->db->set("produk", $produk);
                $this->db->set("namaproduk", $namaProduk);
                $this->db->set("qty", $params->qty[$key]);
                $this->db->set("gudang", $params->gudang);
                $this->db->set("batchno_awal", $params->batch[$key]);
                $this->db->set("expdate_awal", $params->expdate[$key]);
                $this->db->set("batchno_akhir", $params->newbatch[$key]);
                $this->db->set("expdate_akhir", $params->expdate_akhir[$key]);
                $this->db->set("stok_awal", $params->stok[$key]);
                $this->db->set("stok_akhir", $params->stok[$key] + $params->qty[$key]);
                $this->db->set("NoDocStok_awal", $params->docstok_awal[$key]);
                if(empty($params->docstok_akhir[$key])){
                    $this->db->set("NoDocStok_akhir",$noDokumen);
                }else{
                    $this->db->set("NoDocStok_akhir", $params->docstok_akhir[$key]);
                }
                $this->db->set("status", "Approve");
                $this->db->set("created_by", $this->user);
                $this->db->set("create_date", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_mutasi_batch");
                $this->setStokbatch($noDokumen,$params->gudang, $params->cabang, $produk, $params->qty[$key], $params->batch[$key], $params->expdate[$key],$params->newbatch[$key], $params->expdate_akhir[$key],$params->stok[$key],$params->docstok_awal[$key],$params->docstok_akhir[$key]);
            }
        }
            
        // Update no dokumen
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Mutasi Batch' and Cabang = '".$this->cabang."'");
        }
            
        return $valid;
    }

    public function saveDataGudang($params = null)
    {
        $valid = false;
        // $no = "";
        // $no = $this->Model_main->noDokumen('Mutasi Gudang');
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Mutasi Gudang');
        $data = $this->db->query("select max(right(nodokumen,7)) as 'no' from trs_mutasi_gudang where Cabang = '".$this->cabang."' and length(nodokumen) = 13 and YEAR(tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == "" ){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $namaProduk = $expld[1]; 

                $this->db->set("cabang", $params->cabang);
                $this->db->set("tanggal", date("Y-m-d"));
                $this->db->set("nodokumen", $noDokumen);
                $this->db->set("produk", $produk);
                $this->db->set("namaproduk", $namaProduk);
                $this->db->set("qty", $params->qty[$key]);
                $this->db->set("gudang_awal", $params->gudang_awal[$key]);
                $this->db->set("gudang_akhir", $params->gudang_akhir[$key]);
                $this->db->set("batchno_awal", $params->batch[$key]);
                $this->db->set("expdate_awal", $params->expdate[$key]);
                $this->db->set("batchno_akhir", $params->newbatch[$key]);
                $this->db->set("expdate_akhir", $params->expdate_akhir[$key]);
                $this->db->set("stok_gudang_awal", $params->stok[$key]);
                $this->db->set("stok_gudang_akhir", $params->stok[$key] + $params->qty[$key]);
                $this->db->set("NoDocStok_awal", $params->docstok_awal[$key]);
                if(empty($params->docstok_akhir[$key])){
                    $this->db->set("NoDocStok_akhir", $noDokumen);
                }else{
                    $this->db->set("NoDocStok_akhir", $params->docstok_akhir[$key]);
                }
                $this->db->set("status", "Approve");
                $this->db->set("create_by", $this->user);
                $this->db->set("create_date", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_mutasi_gudang");

                $this->setStokgudang($noDokumen,$params->gudang_awal[$key], $params->gudang_akhir[$key],$params->cabang, $produk, $params->qty[$key], $params->batch[$key], $params->expdate[$key],$params->newbatch[$key], $params->expdate_akhir[$key],$params->stok[$key],$params->docstok_awal[$key],$params->docstok_akhir[$key]);
            }
        }
            
       // Update no dokumen
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Mutasi Gudang' and Cabang = '".$this->cabang."'");
        }
            
        return $valid;
    }


    private function setStok($no=null, $gudang=null, $cabang=null, $produk=null, $qty=null, $harga=null, $batch=null, $expdate=null,$docstok=NULL,$gudangasal)
    {
        if (!empty($no)) {
            // $dt = $this->db->query("select * from trs_mutasi_koreksi where no_koreksi = '".$no."'")->row();
            $sum = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1")->row();
            $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."'  and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
            $sum_baik = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' limit 1")->row();
            $det_baik = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and   Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
            // $harga = $dt->harga;
            // $qty = $dt->qty;
        }
        else{
            $sum = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1")->row();
            $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and   Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();

            $sum_baik = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' limit 1")->row();
            $det_baik = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and   Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
        }
        // UPDATE GUDANG KOREKSI SETELAH APPROVAL
        if(!empty($no)){
                $sum = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = 'Koreksi' and Tahun = '".date('Y')."' limit 1")->row();
                $qty_kor = $qty;
                if($qty < 0){
                    $qty_kor = -1 * $qty;
                }
                $qtysum = $sum->UnitStok - $qty_kor;
                $valuestok = $qtysum * $sum->UnitCOGS;

                $this->db->set("UnitStok", $qtysum);
                $this->db->set("ValueStok", $valuestok);
                $this->db->where("Cabang", $sum->Cabang);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", $sum->KodeProduk);
                $this->db->where("Gudang", 'Koreksi');
                $valid = $this->db->update('trs_invsum');
                if($qty > 0){
                    $qtysum_baik = $sum_baik->UnitStok + $qty;
                    $valuestok_baik = $qtysum_baik * $sum_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $sum_baik->Cabang);
                    $this->db->where("KodeProduk", $sum_baik->KodeProduk);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invsum');
                }
        }else{
            $qty_sum = $qty;
            if (!empty($sum)) {
            //update gudang baik
                if($qty < 0){
                    $qtysum_baik = $sum_baik->UnitStok + $qty;
                    $valuestok_baik = $qtysum_baik * $sum_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $sum_baik->Cabang);
                    $this->db->where("KodeProduk", $sum_baik->KodeProduk);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invsum');
                    $qty_sum = -1 * $qty;

                }
                $qtysum = $sum->UnitStok + $qty_sum;
                $valuestok = $qtysum * $sum->UnitCOGS;

                //update gudang koreksi
                $this->db->set("UnitStok", $qtysum);
                $this->db->set("ValueStok", $valuestok);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("Cabang", $sum->Cabang);
                $this->db->where("KodeProduk", $sum->KodeProduk);
                $this->db->where("Gudang", $gudang);
                $valid = $this->db->update('trs_invsum');
            }
            else{
                if($qty < 0){
                    $qtysum_baik = $sum_baik->UnitStok + $qty;
                    $valuestok_baik = $qtysum_baik * $sum_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $sum_baik->Cabang);
                    $this->db->where("KodeProduk", $sum_baik->KodeProduk);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invsum');
                    $qty_sum = -1 * $qty;
                }
                $sum = $this->db->query("select * from trs_invsum where cabang = '".$cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' limit 1")->row();

                $unitcogs = (!empty($harga))? $harga : $sum->UnitCOGS;
                $valuestok = $qty_sum * $unitcogs;

                // INSERT INVSUM
                $this->db->set("Tahun", $sum->Tahun);
                $this->db->set("Cabang", $sum->Cabang);
                $this->db->set("KodePrinsipal", $sum->KodePrinsipal);
                $this->db->set("NamaPrinsipal", $sum->NamaPrinsipal);
                $this->db->set("Pabrik", $sum->Pabrik);
                $this->db->set("KodeProduk", $sum->KodeProduk);
                $this->db->set("NamaProduk", $sum->NamaProduk);
                $this->db->set("UnitStok", $qty_sum);
                $this->db->set("ValueStok", $valuestok);
                $this->db->set("Gudang", $gudang);
                $this->db->set("Indeks", $sum->Indeks);
                $this->db->set("UnitCOGS", $unitcogs);
                $valid = $this->db->insert('trs_invsum');
            }
        }
        
        if(!empty($no)){                
                $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."'  and  Gudang = 'Koreksi' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
                $qty_kor = $qty;
                if($qty < 0){
                    $qty_kor = -1 * $qty;
                }
                $qtydet = $det->UnitStok - $qty_kor;
                $valuestok = $qtydet * $det->UnitCOGS;

                $this->db->set("UnitStok", $qtydet);
                $this->db->set("ValueStok", $valuestok);
                $this->db->where("Cabang", $this->cabang);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", $produk);
                $this->db->where("NoDokumen", $docstok);
                $this->db->where("BatchNo", $batch);
                $this->db->where("ExpDate", $expdate);
                $this->db->where("Gudang", 'Koreksi');
                $valid = $this->db->update('trs_invdet');

                if($qty > 0){
                    $qtydet_baik = $det_baik->UnitStok + $qty;
                    $valuestok_baik = $qtydet_baik * $det_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtydet_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invdet');
                }
        }
        else{
             $qty_det = $qty;
            if (!empty($det)) {
                 if($qty < 0){
                    $qtydet_baik = $det_baik->UnitStok + $qty;
                    $valuestok_baik = $qtydet_baik * $det_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtydet_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invdet');
                    $qty_det = -1 * $qty;
                }
                $qtydet = $det->UnitStok + $qty_det;
                $valuestok = $qtydet * $det->UnitCOGS;

                $this->db->set("UnitStok", $qtydet);
                $this->db->set("ValueStok", $valuestok);
                $this->db->where("Cabang", $this->cabang);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", $produk);
                $this->db->where("NoDokumen", $docstok);
                $this->db->where("BatchNo", $batch);
                $this->db->where("Gudang", $gudang);
                $valid = $this->db->update('trs_invdet');

                // UPDATE GUDANG KOREKSI SETELAH APPROVAL 
            }
            else{     
                if($qty < 0){
                    $qtydet_baik = $det_baik->UnitStok + $qty;
                    $valuestok_baik = $qtydet_baik * $det_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtydet_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invdet');
                    $qty_det = -1 * $qty;
                }       
                $det = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and  Gudang = '".$gudangasal."' and ExpDate = '".$expdate."'  and Tahun = '".date('Y')."' limit 1")->row();            
                $unitcogs = (!empty($harga))? $harga : $det->UnitCOGS;
                $valuestok = $qty_det * $unitcogs;
                // INSERT INVDET
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", $det->KodePrinsipal);
                $this->db->set("NamaPrinsipal", $det->NamaPrinsipal);
                $this->db->set("Pabrik", $det->Pabrik);
                $this->db->set("KodeProduk", $produk);
                $this->db->set("NamaProduk", $det->NamaProduk);
                $this->db->set("UnitStok", $qty_det);
                $this->db->set("ValueStok", $valuestok);
                $this->db->set("BatchNo", $batch);
                $this->db->set("NoDokumen", $docstok);
                $this->db->set("ExpDate", $expdate);
                $this->db->set("Gudang", $gudang);
                $this->db->set("UnitCOGS", $unitcogs);
                $valid = $this->db->insert('trs_invdet');
            }
        }
        
    }

     private function setBackStok($no=null, $gudang=null, $cabang=null, $produk=null, $qty=null, $harga=null, $batch=null, $expdate=null,$docstok=NULL,$gudangasal)
    {
        if (!empty($no)) {
            // $dt = $this->db->query("select * from trs_mutasi_koreksi where no_koreksi = '".$no."'")->row();
            $sum = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1")->row();
            $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."'  and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
            $sum_baik = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' limit 1")->row();
            $det_baik = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and   Gudang = '".$gudangasal."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
            // $harga = $dt->harga;
            // $qty = $dt->qty;
        }
        if(!empty($no)){
                $sum = $this->db->query("select * from trs_invsum where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and  Gudang = 'Koreksi' and Tahun = '".date('Y')."' limit 1")->row();
                $qty_kor = $qty;
                if($qty < 0){
                    $qty_kor = -1 * $qty;
                    $qtysum = $sum->UnitStok - $qty_kor;
                    $valuestok = $qtysum * $sum->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", 'Koreksi');
                    $valid = $this->db->update('trs_invsum');

                    $qtysum_baik = $sum_baik->UnitStok + $qty_kor;
                    $valuestok_baik = $qtysum_baik * $sum_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invsum');
                }elseif($qty > 0){
                    $qtysum = $sum->UnitStok - $qty;
                    $valuestok = $qtysum * $sum->UnitCOGS;
                    $this->db->set("UnitStok", $qtysum);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $sum_baik->Cabang);
                    $this->db->where("KodeProduk", $sum_baik->KodeProduk);
                    $this->db->where("Gudang", 'Koreksi');
                    $valid = $this->db->update('trs_invsum');
                }
        }
        
        if(!empty($no)){                
                $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."'  and  Gudang = 'Koreksi' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok."' limit 1")->row();
                $qty_kor = $qty;
                if($qty < 0){
                    $qty_kor = -1 * $qty;
                    $qtydet = $det->UnitStok - $qty_kor;
                    $valuestok = $qtydet * $det->UnitCOGS;

                    $this->db->set("UnitStok", $qtydet);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("ExpDate", $expdate);
                    $this->db->where("Gudang", 'Koreksi');
                    $valid = $this->db->update('trs_invdet');

                    $qtydet_baik = $det_baik->UnitStok + $qty_kor;
                    $valuestok_baik = $qtydet_baik * $det_baik->UnitCOGS;
                    $this->db->set("UnitStok", $qtydet_baik);
                    $this->db->set("ValueStok", $valuestok_baik);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("Gudang", $gudangasal);
                    $valid = $this->db->update('trs_invdet');
                }elseif($qty > 0){
                    $qtydet = $det->UnitStok - $qty;
                    $valuestok = $qtydet * $det->UnitCOGS;
                    $this->db->set("UnitStok", $qtydet);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("NoDokumen", $docstok);
                    $this->db->where("BatchNo", $batch);
                    $this->db->where("Gudang", 'Koreksi');
                    $valid = $this->db->update('trs_invdet');
                }
        }
        
    }

     private function setStokbatch($no=null, $gudang=null, $cabang=null, $produk=null, $qty=null, $batch=null, $expdate=null,$newbatch=null,$expdate_akhir=null,$stok=null,$docstok1=NULL,$docstok2=NULL)
    {
        if (!empty($no)) {
            // $dt = $this->db->query("select * from trs_mutasi_batch where nodokumen = '".$no."'")->row();
            $sum = $this->db->query("select * from trs_invsum where cabang = '".$cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1")->row();
            $det_awal = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and ExpDate='".$expdate."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok1."' limit 1")->row();
            $det_akhir = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$newbatch."' and ExpDate='".$expdate_akhir."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok2."' limit 1")->row();
            // $harga = $dt->harga;
            // $qty = $dt->qty;
        }
        // else{
        //     $sum = $this->db->query("select * from trs_invsum where cabang = '".$cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1")->row();
        //     $det_awal = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and ExpDate='".$expdate."' and  Gudang = '".$gudang."' and NoDokumen = '".$docstok1."' and Tahun = '".date('Y')."' limit 1")->row();
        //      $det_akhir = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$newbatch."' and ExpDate='".$expdate_akhir."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok2."' limit 1")->row();
        // }
       
        if (!empty($det_akhir)) {
            $qtydet_awal = $det_awal->UnitStok - $qty;
            $qtydet_ahir = $det_akhir->UnitStok + $qty;
            $valuestok_awal = $qtydet_awal * $det_awal->UnitCOGS;
            $valuestok_akhir = $qtydet_ahir * $det_akhir->UnitCOGS;

            $this->db->set("UnitStok", $qtydet_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("NoDokumen", $docstok1);
            $this->db->where("BatchNo", $batch);
            $this->db->where("ExpDate", $expdate);
            $this->db->where("Gudang", $gudang);
            $valid = $this->db->update('trs_invdet');

            $this->db->set("UnitStok", $qtydet_ahir);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("BatchNo", $newbatch);
            $this->db->where("ExpDate", $expdate_akhir);
            $this->db->where("NoDokumen", $docstok2);
            $this->db->where("Gudang", $gudang);
            $valid = $this->db->update('trs_invdet');

        }
        else{  
            $det_awal = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."'  and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok1."' limit 1")->row(); 
            $qtydet_awal = $det_awal->UnitStok - $qty;
            $unitcogs_awal = (!empty($harga))? $harga : $det_awal->UnitCOGS;
            $valuestok_awal = $qtydet_awal * $unitcogs_awal;

            $unitcogs_akhir = $unitcogs_awal;
            $valuestok_akhir =$unitcogs_akhir * $qty;
            // INSERT INVDET
            $this->db->set("UnitStok", $qtydet_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->set("UnitCOGS", $unitcogs_awal);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("BatchNo", $batch);
            $this->db->where("NoDokumen", $docstok1);
            $this->db->where("Gudang", $gudang);
            $valid = $this->db->update('trs_invdet');

            $this->db->set("Tahun", date('Y'));
            $this->db->set("Cabang", $cabang);
            $this->db->set("KodePrinsipal", $det_awal->KodePrinsipal);
            $this->db->set("NamaPrinsipal", $det_awal->NamaPrinsipal);
            $this->db->set("Pabrik", $det_awal->Pabrik);
            $this->db->set("KodeProduk", $produk);
            $this->db->set("NamaProduk", $det_awal->NamaProduk);
            $this->db->set("UnitStok", $qty);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->set("BatchNo", $newbatch);
            $this->db->set("ExpDate", $expdate_akhir);
            if(empty($docstok2)){
                $this->db->set("NoDokumen", $no);
            }else{
                $this->db->set("NoDokumen", $docstok2);
            }
            $this->db->set("Gudang", $gudang);
            $this->db->set("UnitCOGS", $unitcogs_akhir);
            $valid = $this->db->insert('trs_invdet');
        }
    }

    private function setStokgudang($no=null, $gudang_awal=null, $gudang_akhir=null, $cabang=null, $produk=null, $qty=null, $batch=null, $expdate=null,$newbatch=null,$expdate_akhir=null,$stok=null,$docstok1=NULL,$docstok2=NULL)
    {
        if (!empty($no)) {
            // $dt = $this->db->query("select * from trs_mutasi_gudang where nodokumen = '".$no."'")->row();
            $sum_awal = $this->db->query("select * from trs_invsum where cabang = '".$cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang_awal."' and Tahun = '".date('Y')."'  limit 1")->row();
            $sum_akhir = $this->db->query("select * from trs_invsum where cabang = '".$cabang."' and KodeProduk = '".$produk."' and  Gudang = '".$gudang_akhir."' and Tahun = '".date('Y')."' limit 1")->row();
            $det_awal = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and ExpDate='".$expdate."' and  Gudang = '".$gudang_awal."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok1."' limit 1")->row();
            $det_akhir = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$newbatch."' and ExpDate='".$expdate_akhir."'  and  Gudang = '".$gudang_akhir."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok2."' limit 1")->row();
            // $harga = $dt->harga;
            // $qty = $dt->qty;
        }

        if (!empty($sum_akhir)) {
            $qtysum_awal = $sum_awal->UnitStok - $qty;
            $valuestok_awal = $qtysum_awal * $sum_awal->UnitCOGS;

            $qtysum_akhir = $sum_akhir->UnitStok + $qty;
            $valuestok_akhir = $qtysum_akhir * $sum_akhir->UnitCOGS;

            $this->db->set("UnitStok", $qtysum_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("Gudang", $gudang_awal);
            $valid = $this->db->update('trs_invsum');

            $this->db->set("UnitStok", $qtysum_akhir);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("Gudang", $gudang_akhir);
            $valid = $this->db->update('trs_invsum');

            // UPDATE GUDANG KOREKSI SETELAH APPROVAL
            // if(!empty($no)){
            //     $sum = $this->db->query("select * from trs_invsum where cabang = '".$dt->cabang."' and KodeProduk = '".$dt->produk."' and  Gudang = 'Koreksi' limit 1")->row();
            //     $qtysum = $sum->UnitStok - $qty;
            //     $valuestok = $qtysum * $sum->UnitCOGS;

            //     $this->db->set("UnitStok", $qtysum);
            //     $this->db->set("ValueStok", $valuestok);
            //     $this->db->where("Cabang", $sum->Cabang);
            //     $this->db->where("KodeProduk", $sum->KodeProduk);
            //     $this->db->where("Gudang", 'Koreksi');
            //     $valid = $this->db->update('trs_invsum');
            // }
        }
        else{
            $qtysum_awal = $sum_awal->UnitStok - $qty;
            $valuestok_awal = $qtysum_awal * $sum_awal->UnitCOGS;

            $qtysum_akhir =  $qty;
            $valuestok_akhir = $qtysum_akhir * $sum_awal->UnitCOGS;

            $this->db->set("UnitStok", $qtysum_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("Gudang", $gudang_awal);
            $valid = $this->db->update('trs_invsum');

            // INSERT INVSUM
            $this->db->set("Tahun", date('Y'));
            $this->db->set("Cabang", $cabang);
            $this->db->set("KodePrinsipal", $sum_awal->KodePrinsipal);
            $this->db->set("NamaPrinsipal", $sum_awal->NamaPrinsipal);
            $this->db->set("Pabrik", $sum_awal->Pabrik);
            $this->db->set("KodeProduk", $produk);
            $this->db->set("NamaProduk", $sum_awal->NamaProduk);
            $this->db->set("UnitStok", $qtysum_akhir);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->set("Gudang", $gudang_akhir);
            $this->db->set("Indeks", $sum_awal->Indeks);
            $this->db->set("UnitCOGS", $sum_awal->UnitCOGS);
            $valid = $this->db->insert('trs_invsum');
        }
       
        if (!empty($det_akhir)) {
            $qtydet_awal = $det_awal->UnitStok - $qty;
            $qtydet_ahir = $det_akhir->UnitStok + $qty;
            $valuestok_awal = $qtydet_awal * $det_awal->UnitCOGS;
            $valuestok_akhir = $qtydet_ahir * $det_akhir->UnitCOGS;

            $this->db->set("UnitStok", $qtydet_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->where("Cabang", $cabang);
            $this->db->where("KodeProduk", $produk);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("BatchNo", $batch);
            $this->db->where("NoDokumen", $docstok1);
            $this->db->where("Gudang", $gudang_awal);
            $valid = $this->db->update('trs_invdet');

            $this->db->set("UnitStok", $qtydet_ahir);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->where("Cabang", $cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("BatchNo", $newbatch);
            $this->db->where("Gudang", $gudang_akhir);
            $this->db->where("NoDokumen", $docstok2);
            $valid = $this->db->update('trs_invdet');

        }
        else{            
            $det_awal = $this->db->query("select * from trs_invdet where cabang = '".$cabang."' and KodeProduk = '".$produk."' and BatchNo = '".$batch."' and  Gudang = '".$gudang_awal."' and Tahun = '".date('Y')."' and NoDokumen = '".$docstok1."' limit 1")->row();   
            $qtydet_awal = $det_awal->UnitStok - $qty;
            $unitcogs_awal = $det_awal->UnitCOGS;
            $valuestok_awal = $qtydet_awal * $unitcogs_awal;

            $unitcogs_akhir = $unitcogs_awal;
            $valuestok_akhir =$unitcogs_akhir * $qty;
            // INSERT INVDET
            $this->db->set("UnitStok", $qtydet_awal);
            $this->db->set("ValueStok", $valuestok_awal);
            $this->db->set("UnitCOGS", $unitcogs_awal);
            $this->db->where("Cabang", $det_awal->Cabang);
            $this->db->where("Tahun", date('Y'));
            $this->db->where("KodeProduk", $produk);
            $this->db->where("BatchNo", $batch);
            $this->db->where("ExpDate", $expdate);
            $this->db->where("Gudang", $gudang_awal);
            $this->db->where("NoDokumen", $docstok1);
            $valid = $this->db->update('trs_invdet');

            $this->db->set("Tahun", $det_awal->Tahun);
            $this->db->set("Cabang", $det_awal->Cabang);
            $this->db->set("KodePrinsipal", $det_awal->KodePrinsipal);
            $this->db->set("NamaPrinsipal", $det_awal->NamaPrinsipal);
            $this->db->set("Pabrik", $det_awal->Pabrik);
            $this->db->set("KodeProduk", $produk);
            $this->db->set("NamaProduk", $det_awal->NamaProduk);
            $this->db->set("UnitStok", $qty);
            $this->db->set("ValueStok", $valuestok_akhir);
            $this->db->set("BatchNo", $newbatch);
            $this->db->set("ExpDate", $expdate_akhir);
            $this->db->set("Gudang", $gudang_akhir);
            if(empty($docstok2)){
                $this->db->set("NoDokumen", $no);
            }else{
                $this->db->set("NoDokumen", $docstok2);
            }
            $this->db->set("UnitCOGS", $unitcogs_akhir);
            $valid = $this->db->insert('trs_invdet');
        }
    }

    public function listDataApprvMutasiKoreksi()
    {   
        $query = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE cabang = '".$this->cabang."' AND STATUS = 'open' AND YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) >= MONTH(CURDATE()) - 1;")->result();

        return $query;
    }

    public function apprvMutasiKoreksi($no = null,$gudang=NULL,$produk=null,$batch=null,$docstok=null,$noline=null)
    {
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $tgl = date('Y-m-d H:i:s');
        }else if($cektglstok == 0){
            $date = date('Y-m-d',strtotime("-10 days"));
            $tgl = date('Y-m-t H:i:s', strtotime($date));
        }
       if ($this->group == "BM") {
            $this->db->set("status", 'Approve');
            $this->db->set("Approve_BM", 'Approve');
            $this->db->set("date_BM",date('Y-m-d'));
            $this->db->set("user_BM", $this->session->userdata('username'));
            $this->db->set("updated_at", date('Y-m-d H:i:s'));
            $this->db->set("tgl_approve", $tgl);
            $this->db->set("updated_by", $this->session->userdata('username'));
            $this->db->where("no_koreksi", $no);
            $this->db->where("produk", $produk);
            $this->db->where("batch", $batch);
            $this->db->where("NoDocStok", $docstok);
            $this->db->where("gudang", $gudang);
            $this->db->where("noline", $noline);
            $valid = $this->db->update('trs_mutasi_koreksi');
            $query = $this->db->query("select * from trs_mutasi_koreksi where cabang = '".$this->cabang."' and no_koreksi = '".$no."' and produk ='".$produk."' and batch = '".$batch."' and NoDocStok = '".$docstok."' and gudang = '".$gudang."' and noline = '".$noline."' limit 1")->row();
             $this->setStok($no, $gudang, $this->cabang,$produk, $query->qty, $query->harga, $batch, $query->exp_date,$docstok,$gudang);   
        }
        return $valid;
    }  

    public function rejectMutasiKoreksi($no = null,$gudang=NULL,$produk=null,$batch=null,$docstok=null,$noline=null)
    {
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $tgl = date('Y-m-d H:i:s');
        }else if($cektglstok == 0){
            $date = date('Y-m-d',strtotime("-10 days"));
            $tgl = date('Y-m-t H:i:s', strtotime($date));
        }
        if ($this->group == "BM") {
            $this->db->set("status", 'Tolak');
            $this->db->set("Approve_BM", 'Reject');
            $this->db->set("date_BM",date('Y-m-d'));
            $this->db->set("user_BM", $this->session->userdata('username'));
            $this->db->set("updated_at", date('Y-m-d H:i:s'));
            $this->db->set("tgl_approve", $tgl);
            $this->db->set("updated_by", $this->session->userdata('username'));
            $this->db->where("no_koreksi", $no);
            $this->db->where("produk", $produk);
            $this->db->where("batch", $batch);
            $this->db->where("NoDocStok", $docstok);
            $this->db->where("gudang", $gudang);
            $this->db->where("noline", $noline);
            $valid = $this->db->update('trs_mutasi_koreksi');
            $query = $this->db->query("select * from trs_mutasi_koreksi where cabang = '".$this->cabang."' and no_koreksi = '".$no."' and produk ='".$produk."' and batch = '".$batch."' and NoDocStok = '".$docstok."' and gudang = '".$gudang."' and noline = '".$noline."' limit 1")->row();
             $this->setBackStok($no, $gudang, $this->cabang,$produk, $query->qty, $query->harga, $batch, $query->exp_date,$docstok,$gudang);
            
        }

        return $valid;
    }
    
    public function listDataMutasiKoreksi(){
        $query = $this->db->query("select distinct cabang,no_koreksi,status,tanggal from trs_mutasi_koreksi where cabang = '".$this->cabang."' order by tanggal desc ")->result();
        $query = json_decode(json_encode($query),true);
        return $query;
    }

    public function listDataMutasiBatch(){
        $query = $this->db->query("select distinct cabang,nodokumen,status,tanggal from trs_mutasi_batch where cabang = '".$this->cabang."' order by tanggal desc")->result();
        $query = json_decode(json_encode($query),true);
        return $query;
    }

    public function listDataMutasiGudang(){
        $query = $this->db->query("select distinct cabang,nodokumen,status,tanggal from trs_mutasi_gudang where cabang = '".$this->cabang."' order by tanggal desc")->result();
        $query = json_decode(json_encode($query),true);
        return $query;
    }
    public function BatchGudang($kode = NULL,$gudang = null)
    {   
        
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."'  and Gudang = '".$gudang."' order by ExpDate asc")->result();
        return $query;
    }
}