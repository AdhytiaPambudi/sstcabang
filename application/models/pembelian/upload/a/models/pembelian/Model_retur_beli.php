<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_retur_beli extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->model('Model_main');
            $this->cabang = $this->session->userdata('cabang');
    }

    // ========================================= GET STOK =====================================================
    public function getstok($kode = null, $batch=null,$batchdoc=null){
        $stok = $this->db->query("select * from trs_invdet where KodeProduk = '".$kode."' and BatchNo = '".$batch."' and Gudang = 'Baik' and NoDokumen ='".$batchdoc."' and tahun='".date('Y')."'")->row();
        return $stok;

    }
    
    // ========================================= SAVE DATA =====================================================
    public function savedata($params = null, $name1 = null, $name2 = null){
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $prinsipal = explode("~", $params->Prinsipal)[0];
        //get kode konter prinsipal
        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$prinsipal."' limit 1")->row();
        // if(!$kd){
        //     $respon = [
        //         "status" => "gagal",
        //         "pesan" => "Cek Kode konter prinsipal"
        //     ];
        //     return $respon;
        // }
        //get max number dokumen
        $max_number = $this->db->query("select max(RIGHT(No_Usulan,7)) as 'no' from trs_usulan_retur_beli_header where Cabang = '".$this->cabang."'")->result();
        if(empty($max_number[0]->no) || $max_number[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = ($max_number[0]->no) + 1;
        }

        //get kode cab
        $kdCab = $this->db->query("select Kode from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
        //create no dokumen
        $No_Usulan = 'RTR/'.$kdCab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;

        // save data header
        $this->db->trans_begin();
        $this->db->set("Cabang",$this->cabang);
        $this->db->set("No_Usulan",$No_Usulan);
        $this->db->set("Tanggal",date('Y-m-d'));
        $this->db->set("time_retur",date('Y-m-d H:i:s'));
        $this->db->set("Prinsipal",$prinsipal);
        $this->db->set("Supplier",$params->Supplier);
        $this->db->set("Value_Usulan",str_replace( ',', '', $params->TotalNET));
        $this->db->set("Status_Usulan","Open");
        $this->db->set("Dokumen",$name1);
        $this->db->set("Dokumen_2",$name2);
        $this->db->set("No_BPB",$params->BPB);
        $this->db->set("added_time",date('Y-m-d H:i:s'));
        $this->db->set("added_user",$this->session->userdata('username'));
        $this->db->set("statusPusat","Gagal");
        $this->db->set("flag_closing","N");
        $this->db->set("counter_print",0);
        $this->db->set("tipe_retur",$params->tipe_retur);
        $this->db->insert('trs_usulan_retur_beli_header');

        //Save data detail
        foreach ($params->Produk as $key => $value){
            // if (strpos($params->Batch[$key], '~') === true) {
                $batch = explode("~", $params->Batch[$key])[0];
            // }else{
            //     $batch = $params->Batch[$key];
            // }
            $this->db->set("Cabang",$this->cabang);
            $this->db->set("No_Usulan",$No_Usulan);
            $this->db->set("noline",$key+1);
            $this->db->set("Status_Usulan","Open");
            $this->db->set("Tanggal",date('Y-m-d'));
            $this->db->set("time_retur",date('Y-m-d H:i:s'));
            $this->db->set("Produk",explode("~", $value)[0]);
            $this->db->set("Nama_Produk",explode("~", $value)[1]);
            $this->db->set("Satuan",$params->Satuan[$key]);
            $this->db->set("Keterangan",$params->Keterangan[$key]);
            $this->db->set("Qty",str_replace( ',', '', $params->Qty[$key]));
            $banyak = str_replace( ',', '', $params->Qty[$key]) + str_replace( ',', '', $params->Bonus[$key]);
            $this->db->set("BatchNo",$batch);
            $this->db->set("BatchDoc",$params->batchdoc[$key]);
            $this->db->set("Qty_Rec",str_replace( ',', '', $params->QtyRec[$key]));
            $this->db->set("ExpDate",$params->ExpDate[$key]);
            $this->db->set("Disc",str_replace( ',', '', $params->Diskon[$key]));
            $this->db->set("Bonus",str_replace( ',', '', $params->Bonus[$key]));
            $this->db->set("Disc_Cab",str_replace( ',', '', $params->DiscCab[$key]));
            $this->db->set("Harga_Beli_Cab",str_replace( ',', '', $params->HPC[$key]));
            $this->db->set("Harga_Deal",str_replace( ',', '', $params->HargaDeal[$key]));
            $this->db->set("Value_Usulan",str_replace( ',', '', $params->Value[$key]));
            $this->db->set("Value",str_replace( ',', '', $params->Value[$key]));
            $this->db->set("UnitCOGS",str_replace( ',', '', $params->UnitCOGS[$key]));
            $this->db->set("TotalCOGS",str_replace( ',', '', $params->TotalCOGS[$key]));
            $this->db->set("HPC1",str_replace( ',', '', $params->HPC1[$key]));
            $this->db->set("Gross",str_replace( ',', '', $params->Gross[$key]));
            $this->db->set("Potongan",str_replace( ',', '', $params->Potongan[$key]));
            $this->db->set("PPN",str_replace( ',', '', $params->PPN[$key]));
            $this->db->set("Total",str_replace( ',', '', $params->Total[$key]));
            $this->db->set("Disc_Pst",str_replace( ',', '', $params->Disc_Pst[$key]));
            $this->db->set("Harga_Beli_Pst",str_replace( ',', '', $params->Harga_Beli_Pst[$key]));
            $this->db->set("HPP",str_replace( ',', '', $params->HPP[$key]));
            $this->db->set("UserAdd",$this->session->userdata('username'));
            $this->db->set("TimeAdd",date('Y-m-d H:i:s'));
            $this->db->set("sisa_qty",$banyak);
            $this->db->insert('trs_usulan_retur_beli_detail');
        }

        // commit transaction
        if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $respon = [
                        "status" => "Gagal",
                        "pesan" => "Gagal simpan ke database local"
                ];
        }else{
                $this->db->trans_commit();
                $this->setStok($params,$No_Usulan);
                $respon = [
                        "status" => "sukses",
                        "pesan" => "Berhasil Disimpan ".$No_Usulan
                ];
        }

        return $respon;
    }

    // ========================================= SET STOK =====================================================
    public function setStok($params = NULL,$No_Usulan=null)
    {
        // return;
        foreach ($params->Produk as $key => $value) {
            // if (strpos($params->Batch[$key], '~') === true) {
                // $batch = split($params->Batch[$key], "~")[0];
                $batch = explode("~", $params->Batch[$key])[0];
            // }else{
            //     $batch = $params->Batch[$key];
            // }
            $prinsipal = explode("~", $params->Prinsipal)[0];
            // $batch = explode("~", $batch)[0];
            $nama_prinsipal = explode("~", $params->Prinsipal)[1];
            $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$prinsipal."' limit 1")->row();
            $qty = str_replace( ',', '', $params->Qty[$key]);
            $bonus = str_replace( ',', '', $params->Bonus[$key]);
            $hpc = str_replace( ',', '', $params->HPC[$key]);
            $unitCOGS = str_replace( ',', '', $params->UnitCOGS[$key]);

            if ($unitCOGS == 0) {
                $unitCOGS = 1;
            }
            
            $banyak = ($qty + $bonus) * -1 ;
            $valuestok = $banyak * $unitCOGS;
            $sisa_qty = str_replace( ',', '', $params->QtyRec[$key]) - $banyak;
            $value_sisa_qty = $sisa_qty * $unitCOGS;
            //cek detail
            $cekinvdet = $this->db->query("select * from trs_invdet 
                where KodeProduk='".explode("~", $value)[0]."' and Cabang='".$this->cabang."' 
                and Gudang='Retur Supplier' and Tahun = '".date('Y')."' and BatchNo='".$batch."' and NoDokumen='".$No_Usulan."' limit 1");
            if($cekinvdet->num_rows() > 0){
                //update where same stok
                $this->db->set("UnitStok", "UnitStok+".$banyak,FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->where("NoDokumen", $No_Usulan);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->update("trs_invdet");
            }else{
                //insert
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", $kd->Kode_Counter);
                $this->db->set("NamaPrinsipal", $prinsipal);
                $this->db->set("Pabrik", $params->Supplier);
                $this->db->set("KodeProduk", explode("~", $value)[0]);
                $this->db->set("NamaProduk", explode("~", $value)[1]);
                $this->db->set("BatchNo", $batch);
                $this->db->set("ExpDate", $params->ExpDate[$key]);
                $this->db->set("UnitStok", $banyak);
                $this->db->set("Gudang", 'Retur Supplier');
                $this->db->set("TanggalDokumen", date('Y-m-d'));
                $this->db->set("UnitCOGS", $unitCOGS);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->set("NoDokumen", $No_Usulan);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->insert("trs_invdet");
            }
                // kurangi gudang baik
                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->set("ModifiedTime", date('Y-m-d h:i:s'));
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("BatchNo", $batch);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("NoDokumen", $params->batchdoc[$key]);
                $this->db->update("trs_invdet");
                
            // ==================== save inventori Sumary ==============
            $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".explode("~", $value)[0]."' and Cabang='".$this->cabang."' and Gudang='Retur Supplier' and Tahun = '".date('Y')."' limit 1");
            $invsum_baik = $this->db->query("select * from trs_invsum where KodeProduk='".explode("~", $value)[0]."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");

            $invsum_baik = $invsum_baik->row();
            $sisa_stok_baik = $invsum_baik->UnitStok - $banyak;
            $sisa_value_stok_baik = $sisa_stok_baik * $unitCOGS;

            if ($invsum->num_rows() > 0) {
                $invsum = $invsum->row();
                // $UnitStok = $invsum->UnitStok + $banyak;
                // $valuestok2 = $UnitStok * $unitCOGS;
                // Tambah stok retur
                $this->db->set("UnitStok", "UnitStok+".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*unitCOGS", FALSE);
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->set("ModifiedDate", date('Y-m-d h:i:s'));
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->update("trs_invsum");
                // kurangi stok baik
                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*unitCOGS", FALSE);
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->set("ModifiedDate", date('Y-m-d h:i:s'));
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("Gudang", 'Baik');
                $this->db->update("trs_invsum");
            }
            else{
                //insert gudang retur supplier
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", $kd->Kode_Counter);
                $this->db->set("NamaPrinsipal", $prinsipal);
                $this->db->set("Pabrik", $params->Supplier);
                $this->db->set("KodeProduk", explode("~", $value)[0]);
                $this->db->set("NamaProduk", explode("~", $value)[1]);
                $this->db->set("UnitStok", $banyak);
                $this->db->set("Gudang", 'Retur Supplier');
                $this->db->set("indeks", '0.000');
                $this->db->set("ValueStok", $valuestok);
                $this->db->set("UnitCOGS", $unitCOGS);
                $this->db->set("HNA", '0.000');
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->insert("trs_invsum");
                //kurng stok baik
                $this->db->set("UnitStok", "UnitStok-".$banyak,FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS",FALSE);
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->set("ModifiedDate", date('Y-m-d h:i:s'));
                $this->db->where("Tahun", date('Y'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("Gudang", 'Baik');
                $this->db->update("trs_invsum");
            }

            // ==================== save inventori history ==============
            $this->db->set("Tahun", date('Y'));
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("KodePrinsipal", $kd->Kode_Counter);
            $this->db->set("NamaPrinsipal", $prinsipal);
            $this->db->set("Pabrik", $params->Supplier);
            $this->db->set("KodeProduk", explode("~", $value)[0]);
            $this->db->set("NamaProduk", explode("~", $value)[1]);
            $this->db->set("BatchNo", $batch);
            $this->db->set("ExpDate", $params->ExpDate[$key]);
            $this->db->set("Tipe", "BPB");
            $this->db->set("UnitStok", $banyak);
            $this->db->set("Gudang", 'Retur Supplier');
            $this->db->set("ValueStok", $valuestok);
            $this->db->set("NoDokumen", $No_Usulan);
            $this->db->set("Keterangan", "-");
            $this->db->set("AddedUser", $this->session->userdata('username'));
            $this->db->set("AddedTime", date('Y-m-d h:i:s'));
            $this->db->insert("trs_invhis");
        }
         return;
    }

    // ========================================= LIST DATA =====================================================
    function listdatareturbeli($search = null, $limit = null, $status = null){
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " where Status_Usulan = '".$status."'";
            $query = $this->db->query("SELECT *, DATE_FORMAT(added_time, '%Y-%m-%d') as tanggal FROM trs_usulan_retur_beli_header $byStatus $search and ifnull(App_BM_Status,'') = '' order by Tanggal DESC, No_Usulan ASC $limit");
        }else{
            $query = $this->db->query("SELECT *, DATE_FORMAT(added_time, '%Y-%m-%d') as tanggal FROM trs_usulan_retur_beli_header $byStatus $search  order by Tanggal DESC, No_Usulan ASC $limit");
        }
        return $query;
    }

    function detailreturbeli($no = null){
        $query = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail where No_Usulan='".$no."'");
        return $query;
    }

    // ========================================= APPROVE =====================================================
    function approve($no = null, $status = null){
        $this->db->set("App_BM_Status", $status);
        $this->db->set("App_BM_Time", date("Y-m-d H:i:s"));
        $this->db->where("No_Usulan", $no);
        $this->db->update("trs_usulan_retur_beli_header");

        if($status == 'Y'){
            $status = $this->upload_pusat($no);
            return $status;
        }else{
            $this->reverse_stok($no,"reject");
            return "Data Berhasil Di Reject";
        }

    }

    // ========================================= CETAK =====================================================
    function cetakreturbeli($no = null){
        $header = $this->db->query("SELECT *, DATE_FORMAT(added_time, '%Y-%m-%d') as tanggal FROM trs_usulan_retur_beli_header where No_Usulan='".$no."'")->row();
        $detail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail where No_Usulan='".$no."'")->result();
        $output = [
            "header" => $header,
            "detail" => $detail
        ];
        return $output;
    }

    // ========================================= UPDATE PUSAT =====================================================
    function upload_pusat($no){
        $data_header = $this->db->query("SELECT * FROM trs_usulan_retur_beli_header WHERE No_Usulan='".$no."' and App_BM_Status = 'Y'")->row();
        $data_detail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$no."'")->result();

        if(count($data_header)>0){
            $respon = "";
            $this->db2 = $this->load->database('pusat', TRUE);
            if ($this->db2->conn_id == TRUE) {
                $respon = $this->db2->insert('trs_usulan_retur_beli_header', $data_header); 
                foreach ($data_detail as $key => $detail) {
                    $this->db2->insert('trs_usulan_retur_beli_detail', $detail);
                }
                if($respon){
                    $this->db->set("statusPusat", 'Berhasil');
                    $this->db->where("No_Usulan", $no);
                    $this->db->update("trs_usulan_retur_beli_header");
                    return "Upload ke pusat sukses";
                }else{
                    return "Upload ke pusat gagal atau terjadi duplikasi data";
                }
            }
        }else{
            return "Data belum di approve BM";
        }
    }

    // ====================================== DOWNLOAD RETUR DAN UPDATE STOK =====================================
    function download_retur_beli_pusat(){
        $bkb_pusat_header = [];
        $bkb_pusat_detail = [];
        $no_bkb_pusat = [];
        $retur_cabang = [];
        $updated_data = [];
        $updated_data_retur = [];
        $respon = [
            "status" => "Gagal",
            "pesan" => "Proses Gagal"
        ];

        // tarik data dari pusat header dan detail
        $this->db2 = $this->load->database('pusat', TRUE);
        if ($this->db2->conn_id == TRUE) {
            $bkb_pusat_header = $this->db2->query("SELECT * FROM trs_usulan_retur_beli_header WHERE Cabang = '".$this->cabang."' AND tipe_retur='BKB' AND Status_Usulan='Open'")->result();
            if(count($bkb_pusat_header)>0){
                $no_bkb_pusat = array_column($bkb_pusat_header, 'No_Usulan');
            }
            if(count($no_bkb_pusat)>0){
                $list = "'". implode("', '", $no_bkb_pusat) ."'";
                $bkb_pusat_detail = $this->db2->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan IN(".$list.")")->result();
            }
        }
        // insert di trs terima barang cabang
        $this->db->trans_begin();
        foreach ($bkb_pusat_header as $key => $header) {
            // periksa duplikasi di cabang
            $cek_duplikasi = $this->db->query("SELECT * from trs_terima_barang_header where Cabang = '".$header->Cabang."' and NoAcuDokumen='".$header->No_Usulan."'")->result();
            if(count($cek_duplikasi)==1){
                $tgl = date('d');
                $bulan = date('m');
                $tahun = date('y');
                $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$header->Prinsipal."' limit 1")->row();
                $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$header->Cabang."' and length(NoDokumen) > 23")->result();
                $kdCab = $this->db->query("select Kode from mst_cabang where Cabang = '".$header->Cabang."' limit 1")->row();

                if(empty($data[0]->no) || $data[0]->no == ""){
                    $lastNumber = 1000001;
                }else {
                    $lastNumber = ($data[0]->no) + 1;
                }

                $nomorbpb = 'BPB/'.$kdCab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;
                
                // $save_header = $this->db->query("INSERT INTO trs_terima_barang_header (Cabang,Prinsipal,Supplier,Tipe,NoDokumen,NoAcuDokumen,TglDokumen,TimeDokumen,Status,Attach1,Attach2,Value,PPN,Total,UserAdd,TimeAdd,flag_closing)
                // VALUES('".$header->Cabang."','".$header->Prinsipal."','".$header->Supplier."','Retur Supplier','".$nomorbpb."','".$header->No_Usulan."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."','Close','".$header->Dokumen."','".$header->Dokumen_2."','".($header->Value_Usulan*-1)."','0','".($header->Value_Usulan*-1)."','".$this->session->userdata('username')."','".date('Y-m-d H:i:s')."','Y')");

                $this->db->set("Cabang",$header->Cabang);
                $this->db->set("Prinsipal",$header->Prinsipal);
                $this->db->set("Supplier",$header->Supplier);
                $this->db->set("Tipe","Retur Supplier");
                $this->db->set("NoDokumen",$nomorbpb);
                $this->db->set("NoAcuDokumen",$header->No_Usulan);
                $this->db->set("TglDokumen",date('Y-m-d'));
                $this->db->set("TimeDokumen",date('Y-m-d H:i:s'));
                $this->db->set("Status","Close");
                $this->db->set("Attach1",$header->Dokumen);
                $this->db->set("Attach2",$header->Dokumen_2);
                $this->db->set("Value",($header->Value_Usulan*-1));
                $this->db->set("PPN",0);
                $this->db->set("Total",($header->Value_Usulan*-1));
                $this->db->set("UserAdd",$this->session->userdata('username'));
                $this->db->set("TimeAdd",date('Y-m-d H:i:s'));
                $this->db->set("flag_closing",'Y');
                $save_header = $this->db->insert("trs_terima_barang_header");


                if($save_header){
                    $filter = $header->No_Usulan;
                    $new_array = array_filter($bkb_pusat_detail, function($var) use ($filter){
                        return ($var->No_Usulan == $filter);
                    });

                    foreach ($new_array as $key => $detail) {
                        $this->db->set("Cabang",$detail->Cabang);
                        $this->db->set("NoDokumen",$nomorbpb);
                        $this->db->set("noline",$key+1);
                        $this->db->set("Prinsipal",$header->Prinsipal);
                        $this->db->set("NamaPrinsipal",$header->Prinsipal);
                        $this->db->set("Supplier",$header->Supplier);
                        $this->db->set("NoUsulan",$header->Supplier);
                        $this->db->set("NamaSupplier",$header->Supplier);
                        $this->db->set("Tipe","Retur Suplier");
                        $this->db->set("NoAcuDokumen",$header->No_Acu);
                        $this->db->set("TglDokumen",$header->added_time);
                        $this->db->set("TimeDokumen",$header->added_time);
                        $this->db->set("Status","Closed");
                        $this->db->set("Produk",$detail->Produk);
                        $this->db->set("NamaProduk",$detail->Nama_Produk);
                        $this->db->set("Satuan",$detail->Satuan);
                        $this->db->set("QtyPO",$detail->Qty);
                        $this->db->set("Qty",$detail->Qty);
                        $this->db->set("Bonus",$detail->Bonus);
                        $this->db->set("Banyak",$detail->Qty + $detail->Bonus);
                        $this->db->set("Disc",$detail->Disc);
                        $this->db->set("HrgBeli",$detail->Harga_Beli_Cab);
                        $this->db->set("BatchNo",$detail->BatchNo);
                        // $this->db->set("ExpDate",$detail->ExpDate);
                        $this->db->set("HPC",$detail->Harga_Beli_Cab);
                        // $this->db->set("HPC1",);
                        $this->db->set("Gross",$detail->Value_Usulan);
                        $this->db->set("Potongan","Potongan");
                        $this->db->set("Value",$detail->Value_Usulan);
                        $this->db->set("PPN",$detail->Value_Usulan*0.1);
                        $this->db->set("Total",$detail->Value_Usulan);
                        $this->db->set("HPP",$detail->HPP);
                        $this->db->set("UserAdd",$this->session->userdata('username'));
                        $this->db->set("TimeAdd",date('Y-m-d H:i:s'));
                        // $this->db->insert("trs_terima_barang_detail");
                        
                        // Noretur yang berhasil di update di tampung dalam updated_data untuk update stok
                        array_push($updated_data, $header->No_Usulan);
                        array_push($updated_data_retur, $header->No_Acu);
                    } //end if foreach
                } //end if save_header
            }
        }

        // update stok nya
        $reverse_stok = $this->reverse_stok($updated_data,"BKB");
        //update Status_Usulan BKB pusat supaya tidak di download lagi
        if ($this->db2->conn_id == TRUE) {
            $this->db2->set("Status_Usulan","Close");
            $this->db2->where_in("No_Usulan",$updated_data);
            $this->db2->update("trs_usulan_retur_beli_header");
        }
        //update data retur menjadi close
        $this->db->set("Status_Usulan","Close");
        $this->db->where_in("No_Usulan",$updated_data_retur);
        $this->db->update("trs_usulan_retur_beli_header");

        // cek status transaksi
        if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $respon = [
                        "status" => "gagal",
                        "pesan" => "Gagal simpan ke database local"
                ];
        }else{
                $this->db->trans_commit();
                $respon = [
                        "status" => "sukses",
                        "pesan" => "Download Pusat sukses",
                ];
        }

    } //end function

    // ========================================= REVERSE STOK =====================================================
    // function reverse_stok($no=null, $trigger = null){
    //     switch ($trigger) {
    //         case 'reject':
    //                 $data_header = $this->db->query("SELECT * FROM trs_usulan_retur_beli_header WHERE No_Usulan='".$no."'")->row();
    //                 $data_detail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$no."'")->result();
    //                 $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$data_header->Prinsipal."' limit 1")->row();

    //                 foreach ($data_detail as $key => $detail) {
    //                     $banyak = $detail->Qty + $detail->Bonus;
    //                     // gudang baik bertambah
    //                     if (strpos($detail->BatchNo, '~') === false) {
    //                         $batch = $detail->BatchNo;    
    //                     }else{
    //                        $batch = explode("~", $detail->BatchNo)[0]; 
    //                     }
    //                      //stok baik updet  tambah 
    //                     $this->db->set("UnitStok", "UnitStok+".ABS($banyak), FALSE);
    //                     $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                     $this->db->where("KodeProduk", $detail->Produk);
    //                     $this->db->where("BatchNo", $detail->BatchNo);
    //                     $this->db->where("Gudang", 'Baik');
    //                     $this->db->where("NoDokumen", $detail->BatchDoc);
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->update("trs_invdet");
    //                     // gudang retur berkurang
    //                     $this->db->set("UnitStok", "UnitStok-".ABS($banyak), FALSE);
    //                     $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                     $this->db->where("KodeProduk", $detail->Produk);
    //                     $this->db->where("BatchNo", $detail->BatchNo);
    //                     $this->db->where("Gudang", 'Retur Supplier');
    //                     $this->db->where("NoDokumen", $detail->No_Usulan);
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->update("trs_invdet");

    //                     // gudang retur berkurang di summary
    //                     $this->db->set("UnitStok", "UnitStok-".ABS($banyak), FALSE);
    //                     $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                     $this->db->where("KodeProduk", $detail->Produk);
    //                     $this->db->where("Gudang", 'Retur Supplier');
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->update("trs_invsum");
    //                     // gudang baik bertambah di summary
    //                     $this->db->set("UnitStok", "UnitStok+".ABS($banyak), FALSE);
    //                     $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                     $this->db->where("KodeProduk", $detail->Produk);
    //                     $this->db->where("Gudang", 'Baik');
    //                     $this->db->where("Tahun", date('Y'));
    //                     $this->db->update("trs_invsum");

    //                     // catat di history
    //                     $this->db->set("Tahun", date('Y'));
    //                     $this->db->set("Cabang",$detail->Cabang);
    //                     $this->db->set("KodePrinsipal",$kd->Kode_Counter);
    //                     $this->db->set("NamaPrinsipal",$data_header->Prinsipal);
    //                     $this->db->set("Pabrik",$data_header->Prinsipal);
    //                     $this->db->set("KodeProduk",$detail->Produk);
    //                     $this->db->set("NamaProduk",$detail->Nama_Produk);
    //                     $this->db->set("UnitStok",$detail->Qty);
    //                     $this->db->set("ValueStok",$detail->Value_Usulan);
    //                     $this->db->set("BatchNo",$batch);
    //                     $this->db->set("ExpDate",$detail->ExpDate);
    //                     $this->db->set("Gudang","Baik");
    //                     $this->db->set("Tipe","Retur");
    //                     $this->db->set("NoDokumen",$data_header->No_Usulan);
    //                     $this->db->set("Keterangan",$detail->Keterangan);
    //                     $this->db->set("AddedUser", $this->session->userdata('username'));
    //                     $this->db->set("AddedTime", date('Y-m-d H:i:s'));
    //                     $this->db->insert("trs_invhis");
    //                 }
    //             break;
    //             // ===============================================
    //         case 'BKB':
    //             $data_detail = $no;
                                    
    //             for ($i=0; $i < count($data_detail); $i++) { 
    //                 // log_message("error",print_r($i,true));
    //                 // catat di history
    //                 $banyak = $data_detail[$i]->Banyak * -1;
    //                 $ValueStok = ($data_detail[$i]->Value / $banyak) * -1;
    //                 $gross = $data_detail[$i]->Gross * -1;
    //                 // if (strpos($detail->BatchNo, '~') === false) {
    //                 //     $batch = $detail->BatchNo;    
    //                 // }else{
    //                 //     $batch = explode("~", $data_detail[$i]->BatchNo)[0]; 
    //                 // }
    //                 $this->db->set("Tahun", date('Y'));
    //                 $this->db->set("Cabang",$data_detail[$i]->Cabang);
    //                 $this->db->set("KodePrinsipal","");
    //                 $this->db->set("NamaPrinsipal",$data_detail[$i]->Prinsipal);
    //                 $this->db->set("Pabrik",$data_detail[$i]->Prinsipal);
    //                 $this->db->set("KodeProduk",$data_detail[$i]->Produk);
    //                 $this->db->set("NamaProduk",$data_detail[$i]->NamaProduk);
    //                 $this->db->set("UnitStok",$banyak);
    //                 $this->db->set("ValueStok",$gross);
    //                 $this->db->set("BatchNo",$data_detail[$i]->BatchNo);
    //                 $this->db->set("ExpDate",$data_detail[$i]->ExpDate);
    //                 $this->db->set("Gudang","Retur Supplier");
    //                 $this->db->set("Tipe","Retur");
    //                 $this->db->set("NoDokumen",$data_detail[$i]->NoDokumen);
    //                 $this->db->set("Keterangan",$data_detail[$i]->Keterangan);
    //                 $this->db->set("AddedUser", $this->session->userdata('username'));
    //                 $this->db->set("AddedTime", date('Y-m-d H:i:s'));
    //                 $this->db->insert("trs_invhis");

    //                 // // gudang retur berkurang
    //                 // log_message("error",print_r($data_detail[$i]->Produk,true));
    //                 // log_message("error",print_r($data_detail[$i]->BatchNo,true));
    //                 // log_message("error",print_r($data_detail[$i]->NoUsulan,true));
    //                 // log_message("error",print_r($i,true));
    //                 $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
    //                 $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                 $this->db->where("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->where("BatchNo", $data_detail[$i]->BatchNo);
    //                 $this->db->where("Gudang", 'Retur Supplier');
    //                 $this->db->where("NoDokumen", $data_detail[$i]->NoUsulan);
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->update("trs_invdet");

    //                 // gudang retur berkurang di summary
    //                 $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
    //                 $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                 $this->db->where("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->where("Gudang", 'Retur Supplier');
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->update("trs_invsum");
    //             }
    //             break;

    //         case 'Tolakan':
    //             $data_detail = $no;
                                    
    //             for ($i=0; $i < count($data_detail); $i++) { 
    //                 // log_message("error",print_r($i,true));
    //                 // catat di history
    //                 $banyak = $data_detail[$i]->Banyak * -1;
    //                 $ValueStok = ($data_detail[$i]->Value / $banyak) * -1;
    //                 $gross = $data_detail[$i]->Gross * -1;
    //                 $this->db->set("Tahun", date('Y'));
    //                 $this->db->set("Cabang",$data_detail[$i]->Cabang);
    //                 $this->db->set("KodePrinsipal","");
    //                 $this->db->set("NamaPrinsipal",$data_detail[$i]->Prinsipal);
    //                 $this->db->set("Pabrik",$data_detail[$i]->Prinsipal);
    //                 $this->db->set("KodeProduk",$data_detail[$i]->Produk);
    //                 $this->db->set("NamaProduk",$data_detail[$i]->NamaProduk);
    //                 $this->db->set("UnitStok",$banyak);
    //                 $this->db->set("ValueStok",$gross);
    //                 $this->db->set("BatchNo",$data_detail[$i]->BatchNo);
    //                 $this->db->set("ExpDate",$data_detail[$i]->ExpDate);
    //                 $this->db->set("Gudang","Retur Supplier");
    //                 $this->db->set("Tipe","Retur");
    //                 $this->db->set("NoDokumen",$data_detail[$i]->NoDokumen);
    //                 $this->db->set("Keterangan",$data_detail[$i]->Keterangan);
    //                 $this->db->set("AddedUser", $this->session->userdata('username'));
    //                 $this->db->set("AddedTime", date('Y-m-d H:i:s'));
    //                 $this->db->insert("trs_invhis");

    //                 // // gudang retur berkurang

    //                 $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
    //                 $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                 $this->db->where("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->where("BatchNo", $data_detail[$i]->BatchNo);
    //                 $this->db->where("Gudang", 'Retur Supplier');
    //                 $this->db->where("NoDokumen", $data_detail[$i]->NoUsulan);
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->update("trs_invdet");

    //                 // gudang retur berkurang di summary
    //                 $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
    //                 $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                 $this->db->where("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->where("Gudang", 'Retur Supplier');
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->update("trs_invsum");

    //                 // // gudang Baik detail Bertambah

    //                 $this->db->set("Tahun", date('Y'));
    //                 $this->db->set("Cabang", $this->cabang);
    //                 $this->db->set("KodePrinsipal", '');
    //                 $this->db->set("NamaPrinsipal", $data_detail[$i]->Prinsipal);
    //                 $this->db->set("Pabrik", $data_detail[$i]->Prinsipal);
    //                 $this->db->set("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->set("NamaProduk", $data_detail[$i]->NamaProduk);
    //                 $this->db->set("BatchNo", $data_detail[$i]->BatchNo);
    //                 $this->db->set("ExpDate", $data_detail[$i]->ExpDate);
    //                 $this->db->set("UnitStok", $banyak);
    //                 $this->db->set("Gudang", 'Baik');
    //                 $this->db->set("TanggalDokumen", date('Y-m-d'));
    //                 $this->db->set("UnitCOGS", $ValueStok);
    //                 $this->db->set("ValueStok", $ValueStok*$banyak, FALSE);
    //                 $this->db->set("NoDokumen", $data_detail[$i]->NoDokumen);
    //                 $this->db->set("AddedUser", $this->session->userdata('username'));
    //                 $this->db->set("AddedTime", date('Y-m-d h:i:s'));
    //                 $this->db->insert("trs_invdet");

    //                 // gudang Baik Bertambah di summary
    //                 $this->db->set("UnitStok", "UnitStok+".$banyak, FALSE);
    //                 $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
    //                 $this->db->where("KodeProduk", $data_detail[$i]->Produk);
    //                 $this->db->where("Gudang", 'Baik');
    //                 $this->db->where("Tahun", date('Y'));
    //                 $this->db->update("trs_invsum");
    //             }
    //             break;

    //         default:
    //             # code...
    //             break;
    //     }
    // }
    function reverse_stok($no=null, $trigger = null){
        if($trigger == 'reject'){
            $data_header = $this->db->query("SELECT * FROM trs_usulan_retur_beli_header WHERE No_Usulan='".$no."'")->row();
            $data_detail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$no."'")->result();
            $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$data_header->Prinsipal."' limit 1")->row();

            foreach ($data_detail as $key => $detail) {
                $banyak = $detail->Qty + $detail->Bonus;
                        // gudang baik bertambah
                if (strpos($detail->BatchNo, '~') === false) {
                    $batch = $detail->BatchNo;    
                }else{
                    $batch = explode("~", $detail->BatchNo)[0]; 
                }
                         //stok baik updet  tambah 
                $this->db->set("UnitStok", "UnitStok+".ABS($banyak), FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $detail->Produk);
                $this->db->where("BatchNo", $detail->BatchNo);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("NoDokumen", $detail->BatchDoc);
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invdet");
                        // gudang retur berkurang
                $this->db->set("UnitStok", "UnitStok-".ABS($banyak), FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $detail->Produk);
                $this->db->where("BatchNo", $detail->BatchNo);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("NoDokumen", $detail->No_Usulan);
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invdet");

                // gudang retur berkurang di summary
                $this->db->set("UnitStok", "UnitStok-".ABS($banyak), FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $detail->Produk);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");
                // gudang baik bertambah di summary
                $this->db->set("UnitStok", "UnitStok+".ABS($banyak), FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $detail->Produk);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");

                // catat di history
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang",$detail->Cabang);
                $this->db->set("KodePrinsipal",$kd->Kode_Counter);
                $this->db->set("NamaPrinsipal",$data_header->Prinsipal);
                $this->db->set("Pabrik",$data_header->Prinsipal);
                $this->db->set("KodeProduk",$detail->Produk);
                $this->db->set("NamaProduk",$detail->Nama_Produk);
                $this->db->set("UnitStok",$detail->Qty);
                $this->db->set("ValueStok",$detail->Value_Usulan);
                $this->db->set("BatchNo",$batch);
                $this->db->set("ExpDate",$detail->ExpDate);
                $this->db->set("Gudang","Baik");
                $this->db->set("Tipe","Retur");
                $this->db->set("NoDokumen",$data_header->No_Usulan);
                $this->db->set("Keterangan",$detail->Keterangan);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d H:i:s'));
                $this->db->insert("trs_invhis");
            }  
        }elseif($trigger == 'BKB'){
            $data_detail = $no;
            $NoUsulan = "";
            $NoBKB = "";                    
            for ($i=0; $i < count($data_detail); $i++) { 
                // catat di history
                $banyak = $data_detail[$i]->Banyak * -1;
                $ValueStok = ($data_detail[$i]->Value / $banyak) * -1;
                $gross = $data_detail[$i]->Gross * -1;
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang",$data_detail[$i]->Cabang);
                $this->db->set("KodePrinsipal","");
                $this->db->set("NamaPrinsipal",$data_detail[$i]->Prinsipal);
                $this->db->set("Pabrik",$data_detail[$i]->Prinsipal);
                $this->db->set("KodeProduk",$data_detail[$i]->Produk);
                $this->db->set("NamaProduk",$data_detail[$i]->NamaProduk);
                $this->db->set("UnitStok",$banyak);
                $this->db->set("ValueStok",$gross);
                $this->db->set("BatchNo",$data_detail[$i]->BatchNo);
                $this->db->set("ExpDate",$data_detail[$i]->ExpDate);
                $this->db->set("Gudang","Retur Supplier");
                $this->db->set("Tipe","Retur");
                $this->db->set("NoDokumen",$data_detail[$i]->NoDokumen);
                $this->db->set("Keterangan",$data_detail[$i]->Keterangan);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d H:i:s'));
                $this->db->insert("trs_invhis");

                // // gudang retur berkurang
                $NoUsulan = $data_detail[$i]->NoUsulan;
                $NoBKB = $data_detail[$i]->NoDokumen;
                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $data_detail[$i]->Produk);
                $this->db->where("BatchNo", $data_detail[$i]->BatchNo);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("NoDokumen", $data_detail[$i]->NoUsulan);
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invdet");

                // gudang retur berkurang di summary
                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $data_detail[$i]->Produk);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");
                $returdetail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$data_detail[$i]->NoUsulan."' and Produk = '".$data_detail[$i]->Produk."' and BatchNo='".$data_detail[$i]->BatchNo."' limit 1")->row();
                $qtyusulan   = $returdetail->Qty * -1;
                $Bonususulan = $returdetail->Bonus * -1;
                $bykusulan   = $qtyusulan + $Bonususulan;
                if($banyak == $bykusulan){
                    $this->db->query("update trs_usulan_retur_beli_detail 
                         set Status_Usulan = 'Closed'
                         where No_Usulan = '".$data_detail[$i]->NoUsulan."' and 
                               Produk = '".$data_detail[$i]->Produk."' and 
                               BatchNo ='".$data_detail[$i]->BatchNo."'");
                }else{
                    $this->db->query("update trs_usulan_retur_beli_detail 
                         set Status_Usulan = 'Open'
                         where No_Usulan = '".$data_detail[$i]->NoUsulan."' and 
                               Produk = '".$data_detail[$i]->Produk."' and 
                               BatchNo ='".$data_detail[$i]->BatchNo."'");
                }
                
            }
            $detusulan = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$NoUsulan."' and Status_Usulan ='Open'")->num_rows();
            if($detusulan > 0){
                $this->db->query("update trs_usulan_retur_beli_header 
                         set Status_Usulan = 'Open',
                              No_Acu ='".$NoBKB."'
                         where No_Usulan = '".$NoUsulan."'");
            }else{
                $this->db->query("update trs_usulan_retur_beli_header 
                         set Status_Usulan = 'Closed',
                              No_Acu ='".$NoBKB."'
                         where No_Usulan = '".$NoUsulan."'");
            }

        }elseif($trigger == 'Tolakan'){
            $data_detail = $no;
            $NoUsulan = "";
            $NoBKB = "";                     
            for ($i=0; $i < count($data_detail); $i++) { 
                // catat di history
                $banyak = $data_detail[$i]->Banyak * -1;
                $ValueStok = ($data_detail[$i]->Value / $banyak) * -1;
                $gross = $data_detail[$i]->Gross * -1;
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang",$data_detail[$i]->Cabang);
                $this->db->set("KodePrinsipal","");
                $this->db->set("NamaPrinsipal",$data_detail[$i]->Prinsipal);
                $this->db->set("Pabrik",$data_detail[$i]->Prinsipal);
                $this->db->set("KodeProduk",$data_detail[$i]->Produk);
                $this->db->set("NamaProduk",$data_detail[$i]->NamaProduk);
                $this->db->set("UnitStok",$banyak);
                $this->db->set("ValueStok",$gross);
                $this->db->set("BatchNo",$data_detail[$i]->BatchNo);
                $this->db->set("ExpDate",$data_detail[$i]->ExpDate);
                $this->db->set("Gudang","Retur Supplier");
                $this->db->set("Tipe","Retur");
                $this->db->set("NoDokumen",$data_detail[$i]->NoDokumen);
                $this->db->set("Keterangan",$data_detail[$i]->Keterangan);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d H:i:s'));
                $this->db->insert("trs_invhis");

                // // gudang retur berkurang

                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $data_detail[$i]->Produk);
                $this->db->where("BatchNo", $data_detail[$i]->BatchNo);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("NoDokumen", $data_detail[$i]->NoUsulan);
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invdet");

                // gudang retur berkurang di summary
                $NoUsulan = $data_detail[$i]->NoUsulan;
                $NoBKB = $data_detail[$i]->NoDokumen;
                $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $data_detail[$i]->Produk);
                $this->db->where("Gudang", 'Retur Supplier');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");

                // // gudang Baik detail Bertambah

                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", '');
                $this->db->set("NamaPrinsipal", $data_detail[$i]->Prinsipal);
                $this->db->set("Pabrik", $data_detail[$i]->Prinsipal);
                $this->db->set("KodeProduk", $data_detail[$i]->Produk);
                $this->db->set("NamaProduk", $data_detail[$i]->NamaProduk);
                $this->db->set("BatchNo", $data_detail[$i]->BatchNo);
                $this->db->set("ExpDate", $data_detail[$i]->ExpDate);
                $this->db->set("UnitStok", $banyak);
                $this->db->set("Gudang", 'Baik');
                $this->db->set("TanggalDokumen", date('Y-m-d'));
                $this->db->set("UnitCOGS", $ValueStok);
                $this->db->set("ValueStok", $ValueStok*$banyak, FALSE);
                $this->db->set("NoDokumen", $data_detail[$i]->NoDokumen);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->insert("trs_invdet");

                // gudang Baik Bertambah di summary
                $this->db->set("UnitStok", "UnitStok+".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->where("KodeProduk", $data_detail[$i]->Produk);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");
                $returdetail = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$data_detail[$i]->NoUsulan."' and Produk = '".$data_detail[$i]->Produk."' and BatchNo='".$data_detail[$i]->BatchNo."' limit 1")->row();
                $qtyusulan   = $returdetail->Qty * -1;
                $Bonususulan = $returdetail->Bonus * -1;
                $bykusulan   = $qtyusulan + $Bonususulan;
                if($banyak == $bykusulan){
                    $this->db->query("update trs_usulan_retur_beli_detail 
                         set Status_Usulan = 'Closed'
                         where No_Usulan = '".$data_detail[$i]->NoUsulan."' and 
                               Produk = '".$data_detail[$i]->Produk."' and 
                               BatchNo ='".$data_detail[$i]->BatchNo."'");
                }else{
                    $this->db->query("update trs_usulan_retur_beli_detail 
                         set Status_Usulan = 'Open'
                         where No_Usulan = '".$data_detail[$i]->NoUsulan."' and 
                               Produk = '".$data_detail[$i]->Produk."' and 
                               BatchNo ='".$data_detail[$i]->BatchNo."'");
                }
            }
            $detusulan = $this->db->query("SELECT * FROM trs_usulan_retur_beli_detail WHERE No_Usulan='".$NoUsulan."' and Status_Usulan ='Open'")->num_rows();
            if($detusulan > 0){
                $this->db->query("update trs_usulan_retur_beli_header 
                         set Status_Usulan = 'Open',
                             No_Acu ='".$NoBKB."'
                         where No_Usulan = '".$NoUsulan."'");
            }else{
                $this->db->query("update trs_usulan_retur_beli_header 
                         set Status_Usulan = 'Closed',
                             No_Acu ='".$NoBKB."'
                         where No_Usulan = '".$NoUsulan."'");
            }

        }
    }
    
    public function listDataBKB($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select *
                                from trs_terima_barang_header where Cabang = '".$this->session->userdata('cabang')."' 
                                and Tipe in  ('BKB','Tolakan') $byID");

        return $query;
    }

    public function listDataBKB2($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select *
                                from trs_terima_barang_header where Cabang = '".$this->session->userdata('cabang')."' 
                                and Tipe in  ('BKB','Tolakan') and Tipe_BKB = 'Barang' and Status = 'Open' $byID");

        return $query;
    }


    public function updateDataBKBPusat()
    {   
        $data_update = [];
        $this->db2 = $this->load->database('pusat', TRUE);  
        // $this->db2->trans_begin();
        $NoDoc = '';
        $TipeBKB ='';
        if ($this->db2->conn_id == TRUE) {
                $data = $this->db2->query("select * from trs_terima_barang_cabang_header where Cabang = '".$this->cabang."' and Tipe in ('BKB','Tolakan') and Status='Open'")->result();
                foreach ($data as $dt) { //loop header
                    // header

                    $cek_header = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$dt->NoDokumen."'")->num_rows();
                    if ($cek_header == 0) {
                        $this->db->insert('trs_terima_barang_header', $dt); // insert each row to another table
                        if($dt->Tipe_BKB == 'CN' or $dt->Tipe_BKB == 'Tolakan'){
                            $this->db->query('update trs_terima_barang_header set Status = "Close" where  NoDokumen = "'.$dt->NoDokumen.'"');
                        }
                        $TipeBKB =$dt->Tipe_BKB;
                        $this->db->query('update trs_terima_barang_header set TglDokumen = "'.date('Y-m-d').'" where  NoDokumen = "'.$dt->NoDokumen.'"');
                        // detail
                        $detail = $this->db2->query("select * from trs_terima_barang_cabang_detail where NoDokumen = '".$dt->NoDokumen."'")->result();
                        
                        foreach($detail as $r) { // loop over results
                            array_push($data_update, $r);
                            $cek = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$r->NoDokumen."' and Produk = '".$r->Produk."' and BatchNo ='".$r->BatchNo."' and noline ='".$r->noline."'")->num_rows();
                                if ($cek == 0) {
                                    $test = $this->db->insert('trs_terima_barang_detail', $r);
                                    
                                }else{
                                    $this->db->set("Qty", "Qty+".$r->Qty, FALSE);
                                    $this->db->set("Bonus", "Bonus+".$r->Bonus, FALSE);
                                    $this->db->set("Banyak", "Banyak+".$r->Banyak, FALSE);
                                    $this->db->set("Disc", "Disc+".$r->Disc, FALSE);
                                    $this->db->set("DiscT", "DiscT+".$r->DiscT, FALSE);
                                    $this->db->set("HPC", "HPC+".$r->HPC, FALSE);
                                    $this->db->set("HPC1", "HPC1+".$r->HPC1, FALSE);
                                    $this->db->set("Gross", "Gross+".$r->Gross, FALSE);
                                    $this->db->set("Potongan", "Potongan+".$r->Potongan, FALSE);
                                    $this->db->set("Value", "Value+".$r->Value, FALSE);
                                    $this->db->set("PPN", "PPN+".$r->PPN, FALSE);
                                    $this->db->set("Total", "Total+".$r->Total, FALSE);
                                    $this->db->set("Disc_Pst", "Disc_Pst+".$r->Disc_Pst, FALSE);
                                    $this->db->set("HPP", "HPP+".$r->HPP, FALSE);
                                    $this->db->where("NoDokumen",$r->NoDokumen);
                                    $this->db->where("Produk",$r->Produk);
                                    $this->db->update("trs_terima_barang_detail");

                                }
                        }// loop over results
                        $this->db->query('update trs_terima_barang_detail set TglDokumen = "'.date('Y-m-d').'" where  NoDokumen = "'.$dt->NoDokumen.'"');
                    }
                    $NoDoc = $dt->NoDokumen;
                    if(count($data_update) > 0){
                        $this->db2->set("Status","Close");
                        $this->db2->where("NoDokumen",$dt->NoDokumen);
                        $this->db2->update("trs_terima_barang_cabang_header");
                        
                        $this->db2->set("Status","Close");
                        $this->db2->where("NoDokumen",$dt->NoDokumen);
                        $this->db2->update("trs_terima_barang_cabang_detail");

                        if($dt->Tipe_BKB == 'Barang'){
                            $this->db->set("Status","Open");
                            $this->db->where("NoDokumen",$dt->NoDokumen);
                            $this->db->update("trs_terima_barang_header");
                            
                            $this->db->set("Status","Open");
                            $this->db->where("NoDokumen",$dt->NoDokumen);
                            $this->db->update("trs_terima_barang_detail");
                        }
                        if($dt->Tipe_BKB == 'Barang' or $dt->Tipe_BKB == 'CN'){
                            $reverse_stok = $this->reverse_stok($data_update,"BKB");
                        }elseif($dt->Tipe_BKB == 'Tolakan'){
                            $reverse_stok = $this->reverse_stok($data_update,"Tolakan");
                        }
                       
                    }
                }//loop header
                
                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            // $this->db2->trans_rollback();
            return 'GAGAL';
        }
    }

}