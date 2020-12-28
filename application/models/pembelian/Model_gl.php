<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_gl extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->cabang = $this->session->userdata('cabang');
            $this->user = $this->session->userdata('username');
    }

    public function getSaldoAwal($jenis=null)
    {
        $query = $this->db->query("select Saldo_Akhir as saldo from trs_buku_transaksi where Kategori = '".$jenis."' order by Jurnal_ID desc limit 1")->row();

        return $query;
    }

    public function getSaldoAwalBank($bank=null)
    {
        
        $query = $this->db->query("select ifnull(Saldo_Akhir,0) as saldo from trs_buku_transaksi where Kategori = 'Bank' and bank_trans = '".$bank."' order by Jurnal_ID desc limit 1")->row();
        return $query;
    }
    public function getSaldoAwalKas($tipekas=null)
    {
        
        $query = $this->db->query("select ifnull(Saldo_Akhir,0) as saldo from trs_buku_transaksi where Kategori = 'Kas' and Tipe_Kas = '".$tipekas."' order by Jurnal_ID desc limit 1")->row();
        return $query;
    }

    public function listGLTransaksi($tipe = null, $jenis = null,$tipekas=null)
    {   
        if($tipe == 'KAS'){
            if($jenis == 'Masuk' && $tipekas == 'Kecil'){
                $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and KasKMasuk ='Y' ")->result();
            }elseif($jenis == 'Masuk' && $tipekas == 'Besar'){
                $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and KasBMasuk ='Y' ")->result();
            }elseif($jenis == 'Keluar' && $tipekas == 'Kecil'){
                $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and KasKKeluar ='Y' ")->result();
            }elseif($jenis == 'Keluar' && $tipekas == 'Besar'){
                $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and KasBKeluar ='Y' ")->result();
            }
        }elseif($tipe == 'BANK'){
           if($jenis == 'Masuk'){
                 $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and BankMasuk ='Y' ")->result();
           }elseif($jenis == 'Keluar'){
                $query = $this->db->query("select NamaTransaksi from mst_gl_transaksi_kat where Tipe in ('A','C') and BankKeluar ='Y' ")->result();
           }
        }
        return $query;
    }

    public function listGLKatagori($tipe = null, $jenis = null)
    {   
        $byTipe = "";
       
        $query = $this->db->query("select distinct Kategori2 from mst_gl_transaksi_kat ")->result();

        return $query;
    }

    public function listGLBank()
    {   
        $query = $this->db->query("select `Nama Perkiraan` as Bank from mst_gl_bank where Cabang = '".$this->cabang."'")->result();

        return $query;
    }

    public function listGLKaryawan()
    {   
        $query = $this->db->query("select Nama, Kode,Jabatan from mst_karyawan where Cabang = '".$this->cabang."'")->result();

        return $query;
    }

    public function listGLGiro($tipegiro=NULL)
    {   
        $status ="";
        if($tipegiro == ""){
           $status =""; 
        }
        if($tipegiro == 'cair' || $tipegiro == 'batal'){
           $status = "and (StatusGiro = 'Terima') or (StatusGiro = 'Tolak' and ifnull(max_tolak,0) < 3 ) ";
        }elseif($tipegiro == 'tolak'){
            $status = "and StatusGiro = 'Cair'";
        }
        $query = $this->db->query("select * from trs_buku_giro where Cabang = '".$this->cabang."' $status")->result();
        return $query;
    }

    public function getlistNokontra($karyawan=NULL)
    {   
        $query = $this->db->query("select * from trs_buku_kasbon where Cabang = '".$this->cabang."' and Status ='open' and KodeKaryawan ='".$karyawan."'")->result();
        return $query;
    }

    public function detailGLTransaksi($trans,$jenis)
    {
        // $expld = explode("~", $trans);
        // $kode = $expld[0];
        // $nama = $expld[1];

        $query = $this->db->query("select * from mst_gl_transaksi_kat where NamaTransaksi = '".$trans."' and Kategori1 = '".$jenis."' limit 1")->result();

        return $query;
    }

    public function getJurnalID($jenis=null)
    {
        $query = $this->db->query("select max(Counter) as maks from trs_buku_transaksi where Kategori = '".$jenis."'")->row();

        return $query->maks;
    }

    // public function saveGLTransaksi($params=null)
    // {
    //     //================ Running Number ======================================//
    //     $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
    //     $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
    //     $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = '".$params->kategori."' and length(No_Voucher)> 13")->result();
    //     if(empty($data[0]->no) || $data[0]->no == ""){
    //       $lastNumber = 1000001;
    //     }else {
    //         // if($params->kategori == 'KAS'){
    //         // $lastNumber = $data[0]->no + 1;
    //         // }else{
    //            $lastNumber = $data[0]->no + 1; 
    //         // }
          
    //     }

    //     $noDokumen = date("y").$kodeLamaCabang.$params->kategori.$lastNumber;

    //     //================= end of running number ========================================//
    //     $counter = $this->getJurnalID($params->kategori);
    //     if (!empty($counter))
    //         $no = $counter;
    //     else
    //         $no = 1000000;  

    //     // $saldo = $this->getSaldoAwal($params->kategori);
    //     if($params->kategori == 'KAS'){
    //         $saldo = $this->getSaldoAwalKas($params->tipe_kas);
    //     }else{
    //         $saldo = $this->getSaldoAwalBank($params->bank_trans);
    //     }
    //     $saldoakhir = (!empty($saldo->saldo)) ? $saldo->saldo:0;
    //     foreach ($params->transaksi as $key => $value) {
    //         $no++;
    //         $expld_tipe = explode("~", $params->tipe[$key]);
    //         $kat6 = $expld_tipe[0];
    //         $kat7 = $expld_tipe[1];
    //         $saldoawal = $saldoakhir;
    //         //==== buat jurnal id ======
    //         $dtjurnal = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = '".$params->kategori."'")->result();
    //         if(empty($dtjurnal[0]->no) || $dtjurnal[0]->no == ""){
    //           $lastjurnal = 1000001;
    //         }else {
    //            $lastjurnal = $dtjurnal[0]->no + 1;          
    //         }

    //         $jurnalid = $params->kategori."/".$lastjurnal;
    //         //========================
    //         if ($params->jtransaksi == "Masuk") {
    //             $saldoakhir = $saldoakhir + $params->jumlah[$key];
    //             $this->db->set("Debit", $params->jumlah[$key]); 
    //             $this->db->set("Kredit", 0); 
    //             $this->db->set("Add", 1); 
    //         }
    //         else{
    //             $saldoakhir = $saldoakhir - $params->jumlah[$key];
    //             $this->db->set("Kredit", $params->jumlah[$key]); 
    //             $this->db->set("Debit", 0); 
    //             $this->db->set("Add", -1); 
    //         }

    //         if ($kat6 == 'Bank') { 
    //             if($params->kategori == 'KAS'){
    //                 $this->db->set("Tipe_Kas", $params->tipe_kas);
    //                 $this->db->set("bank_trans", "");
    //             }else{
    //                  $this->db->set("Bank_Ref", $params->bank[$key]);
    //                  $this->db->set("bank_trans", $params->bank_trans);
    //             }
    //         }
    //         elseif ($kat6 == 'Karyawan') {
    //             $expld2 = explode("~", $params->karyawan[$key]);
    //             $kd = $expld2[0];
    //             $nama = $expld2[1];
    //             $Jabatan = $expld2[2];
    //             if(empty($params->bank[$key]) || $params->bank[$key] == ""){
    //                 $params->bank[$key] = "";
    //             }
    //             $this->db->set("Karyawan", $nama); 
    //             $this->db->set("Kode_Karyawan", $kd); 
    //             $this->db->set("Ket_4", $Jabatan);
    //             $this->db->set("NoDIH", $params->NoDIH[$key]); 
    //             if($params->kategori == 'KAS'){
    //                 $this->db->set("Tipe_Kas", $params->tipe_kas);
    //                 $this->db->set("bank_trans", "");
    //             }else{
    //                  $this->db->set("Bank_Ref", $params->bank[$key]);
    //                  $this->db->set("bank_trans", $params->bank_trans);
    //             }

    //             if($kat7== 'Titipan'){
    //                 $kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
    //                 $kodeLamaCabang = $kodeLamaCabang->KodeGL;
    //                 $kodeDokumen = $this->Model_main->kodeDokumen('Titipan');
    //                 $data = $this->db->query("select max(right(NoTitipan,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."'")->result();
    //                 if(empty($data[0]->no) || $data[0]->no == ""){
    //                     $titipanNumber = 1000001;
    //                 }else {
    //                     $titipanNumber = ($data[0]->no) + 1; 
    //                 }
    //                 $NoTitipan = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$titipanNumber;
    //                 $this->db->set("NoTitipan", $NoTitipan);
    //             }
    //             if($params->transaksi[$key] =='Penambahan Kasbon' || $params->transaksi[$key] =='Realisasi Kasbon'){
    //                 $kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
    //                 $kodeLamaCabang = $kodeLamaCabang->KodeGL;
    //                 $kodeDokumen = $this->Model_main->kodeDokumen('KasBon');
    //                 $data = $this->db->query("SELECT MAX(RIGHT(NoKasbon,7)) AS no FROM trs_buku_kasbon WHERE Cabang ='".$this->cabang."'")->result();
    //                 if(empty($data[0]->no) || $data[0]->no == ""){
    //                     $kontraNumber = 1000001;
    //                 }else {
    //                     $kontraNumber = ($data[0]->no) + 1; 
    //                 }
    //                 $NoKontra = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$kontraNumber;
    //                 $this->db->set("NoKontrabon", $NoKontra);
    //             }
    //         }elseif($kat6 == 'Giro'){
    //             $this->db->set("bank_trans", $params->bank_trans);
    //             $giro = explode("~", $params->giro[$key]);
    //             $this->db->set("NoGiro", $giro[0].'~'.$giro[3]); 
    //         }elseif($kat6 == 'PPH23'){
    //             $this->db->set("tipe_pph", $params->pph23[$key]);
    //             if($params->kategori == 'KAS'){
    //                 $this->db->set("Tipe_Kas", $params->tipe_kas);
    //                 $this->db->set("bank_trans", "");
    //             }else{
    //                  $this->db->set("Bank_Ref", $params->bank[$key]);
    //                  $this->db->set("bank_trans", $params->bank_trans);
    //             }
    //         }else{
    //             if($params->kategori == 'KAS'){
    //                 $this->db->set("Tipe_Kas", $params->tipe_kas);
    //                 $this->db->set("bank_trans", "");
    //             }else{
    //                  $this->db->set("Bank_Ref", $params->bank[$key]);
    //                  $this->db->set("bank_trans", $params->bank_trans);
    //             }
    //         }

    //         /*if($params->transaksi[$key] == 'SSP'){
    //             if ($params->jtransaksi == "Masuk") {
    //                 $saldoakhir = $saldo;
    //                 $this->db->set("Debit", 0); 
    //                 $this->db->set("Kredit", 0); 
    //                 $this->db->set("Add", 1); 
    //             }
    //             else{
    //                 $saldoakhir = $saldo;
    //                 $this->db->set("Kredit", 0); 
    //                 $this->db->set("Debit", 0); 
    //                 $this->db->set("Add", -1); 
    //             }

    //         }*/
    //         $this->db->set("Buku", $params->kategori);
    //         $this->db->set("Cabang", $params->cabang);
    //         $this->db->set("Jurnal_ID", $jurnalid); 
    //         $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
    //         $this->db->set("Kategori", $params->kategori); 
    //         $this->db->set("Kategori_2", $params->katagori[$key]); 
    //         $this->db->set("Kategori3", $params->kategori3[$key]); 
    //         $this->db->set("Transaksi", $params->transaksi[$key]); 
    //         // $this->db->set("Kode_Transaksi", $kode); 
    //         $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
    //         $this->db->set("Ket_2", $kat6); 
    //         $this->db->set("Ket_3", $kat7); 
    //         $this->db->set("Jumlah", $params->jumlah[$key]); 
    //         $this->db->set("Saldo_Awal", $saldoawal); 
    //         $this->db->set("Saldo_Akhir", $saldoakhir); 
    //         $this->db->set("Status",$params->Status[$key]); 
    //         $this->db->set("CR", $params->cr[$key]);
    //         $this->db->set("DR", $params->dr[$key]); 
    //         $this->db->set("Bulan", date("Ym")); 
    //         $this->db->set("Counter", $lastNumber); 
    //         $this->db->set("No_Voucher", $noDokumen); 
    //         $this->db->set("jenis_trans", $params->jtransaksi);
    //         $this->db->set("Added_User", $this->user); 
    //         $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
    //         $valid =  $this->db->insert("trs_buku_transaksi");
    //         if($valid){
    //             if($kat6 == 'Bank'){
    //                     //================ Running Number ======================================//
    //                     $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
    //                     $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
    //                     $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'Bank' and length(No_Voucher)> 13")->result();
    //                     if(empty($data[0]->no) || $data[0]->no == ""){
    //                       $lastNumber = 1000001;
    //                     }else {
    //                         // if($params->kategori == 'KAS'){
    //                         // $lastNumber = substr($data[0]->no,7) + 1;
    //                         // }else{
    //                            $lastNumber = $data[0]->no + 1; 
    //                         // }
                          
    //                     }

    //                     $noDocbanding = date("y").$kodeLamaCabang.'Bank'.$lastNumber;
    //                     //==buat jurnal id==
    //                     $dtjurnalbanding = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'Bank'")->result();
    //                     if(empty($dtjurnalbanding[0]->no) || $dtjurnalbanding[0]->no == ""){
    //                       $lastjurnalbanding = 1000001;
    //                     }else {
    //                        $lastjurnalbanding = $dtjurnalbanding[0]->no + 1;          
    //                     }

    //                     // $jurnalidbanding = $params->kategori."/".$lastjurnalbanding;
    //                     //================= end of running number ========================================//
    //                     $counter = $this->getJurnalID($params->kategori);
    //                     if (!empty($counter))
    //                         {$no = $counter;}
    //                     else
    //                         {$no = 1000000;} 
    //                     $saldo = $this->getSaldoAwalBank($params->bank[$key]);
    //                     $saldoakhir = (!empty($saldo->saldo)) ? $saldo->saldo:0;
    //                     // foreach ($params->transaksi as $key => $value) {
    //                         // $no++;
    //                         $expld_tipe = explode("~", $params->tipe[$key]);
    //                         $kat6 = $expld_tipe[0];
    //                         $kat7 = $expld_tipe[1];
    //                         $saldoawal = $saldoakhir;

    //                         if ($params->jtransaksi != "Masuk") {
    //                             $saldoakhir = $saldoakhir + $params->jumlah[$key];
    //                             $this->db->set("Debit", $params->jumlah[$key]); 
    //                             $this->db->set("Kredit", 0); 
    //                             $this->db->set("Add", 1); 
    //                             $this->db->set("jenis_trans", "Masuk");
    //                         }
    //                         else{
    //                             $saldoakhir = $saldoakhir - $params->jumlah[$key];
    //                             $this->db->set("Kredit", $params->jumlah[$key]); 
    //                             $this->db->set("Debit", 0); 
    //                             $this->db->set("Add", -1); 
    //                             $this->db->set("jenis_trans", "Keluar");
    //                         }

    //                         if ($kat6 == 'Bank') {
    //                             if($params->kategori == 'KAS'){
    //                                 $this->db->set("Bank_Ref", $params->bank[$key]); 
    //                             }else{
    //                                 $this->db->set("Bank_Ref", $params->bank_trans); 
    //                             }
    //                         }
    //                         $this->db->set("Buku", "Bank");
    //                         $this->db->set("Cabang", $params->cabang);
    //                         $this->db->set("Jurnal_ID", 'Bank/'.$lastjurnalbanding); 
    //                         $this->db->set("Jurnal_Acu", $jurnalid); 
    //                         $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
    //                         $this->db->set("Kategori", "Bank"); 
    //                         $this->db->set("bank_trans", $params->bank[$key]);
    //                         $this->db->set("Kategori_2", $params->katagori[$key]); 
    //                         $this->db->set("Kategori3", $params->kategori3[$key]); 
    //                         $this->db->set("Transaksi", $params->transaksi[$key]); 
    //                         $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
    //                         $this->db->set("Ket_2", $kat6); 
    //                         $this->db->set("Ket_3", $kat7); 
    //                         $this->db->set("Jumlah", $params->jumlah[$key]); 
    //                         $this->db->set("Saldo_Awal", $saldoawal); 
    //                         $this->db->set("Saldo_Akhir", $saldoakhir); 
    //                         $this->db->set("Status",$params->Status[$key]); 
    //                         $this->db->set("CR", $params->cr[$key]);
    //                         $this->db->set("DR", $params->dr[$key]); 
    //                         $this->db->set("Bulan", date("Ym")); 
    //                         $this->db->set("Counter", $lastjurnalbanding); 
    //                         $this->db->set("No_Voucher", $noDocbanding); 
    //                         $this->db->set("Added_User", $this->user); 
    //                         $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
    //                         $valid =  $this->db->insert("trs_buku_transaksi");
    //                         if($valid){
    //                                 $this->db->set("Jurnal_Acu", 'Bank/'.$lastjurnalbanding);
    //                                 $this->db->where("Jurnal_ID", $jurnalid);
    //                                 $valid =  $this->db->update("trs_buku_transaksi");
    //                         }
    //                     // }
    //             }elseif($kat6 == 'Giro'){
    //                 $giro = explode("~", $params->giro[$key]);
    //                 $nogiro = $giro[0];
    //                 $bankgiro = $giro[3];
    //                 $dtgiro=$this->db->query("select ifnull(max_tolak,0) as 'max_tolak' from trs_buku_giro where NoGiro ='".$nogiro."' and Bank='".$bankgiro."'")->row();
    //                 $max = $dtgiro->max_tolak;
    //                 if($params->transaksi[$key] == 'Pencairan Giro'){
    //                     $this->db->set("StatusGiro","Cair");
    //                     $this->db->set("TglCairGiro",date('Y-m-d'));
    //                 }elseif($params->transaksi[$key] == 'Tolakan Giro'){
    //                     $this->db->set("StatusGiro","Tolak");
    //                     $this->db->set("max_tolak",$max + 1);
    //                     $this->db->set("TglTolakGiro",date('Y-m-d'));
    //                 }elseif($params->transaksi[$key] == 'Pembatalan Giro'){
    //                     $this->db->set("StatusGiro","Batal");
    //                     $this->db->set("TglTolakGiro",date('Y-m-d'));
    //                 }

    //                 $this->db->set("NoVoucher", $noDokumen);
    //                 $this->db->set("JurnalID", $jurnalid);
    //                 $this->db->set("Transaksi", $params->transaksi[$key]);
    //                 $this->db->set("modified_by", $this->user);
    //                 $this->db->set("modified_at", date("Y-m-d H:i:s"));
    //                 $this->db->where("Cabang", $params->cabang);
    //                 $this->db->where("NoGiro", $nogiro);
    //                 $this->db->where("Bank", $bankgiro);
    //                 $valid =  $this->db->update("trs_buku_giro");
    //             }elseif($kat6 == 'Kas')
    //             {
    //                 $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
    //                 $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
    //                 $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'KAS' and length(No_Voucher)> 13")->result();
    //                 if(empty($data[0]->no) || $data[0]->no == ""){
    //                     $lastNumber = 1000001;
    //                 }else {
    //                     $lastNumber = $data[0]->no + 1;   
    //                 }

    //                 $noDocbanding = date("y").$kodeLamaCabang.'KAS'.$lastNumber;
    //                 $dtjurnalbanding = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'KAS'")->result();
    //                 if(empty($dtjurnalbanding[0]->no) || $dtjurnalbanding[0]->no == ""){
    //                     $lastjurnalbanding = 1000001;
    //                 }else {
    //                     $lastjurnalbanding = $dtjurnalbanding[0]->no + 1;          
    //                 }
    //                 $jurnalidbanding = "KAS/".$lastjurnalbanding;
    //                 $saldo = $this->getSaldoAwalKas($kat7);
                    
    //                 $saldoakhir = (!empty($saldo->saldo)) ? $saldo->saldo:0;
    //                 $saldoawal = $saldoakhir;
    //                 if ($params->jtransaksi != "Masuk") {
    //                     $saldoakhir = $saldoakhir + $params->jumlah[$key];
    //                     $this->db->set("Debit", $params->jumlah[$key]); 
    //                     $this->db->set("Kredit", 0); 
    //                     $this->db->set("Add", 1); 
    //                     $this->db->set("jenis_trans", "Masuk");
    //                 }
    //                 else{
    //                     $saldoakhir = $saldoakhir - $params->jumlah[$key];
    //                     $this->db->set("Kredit", $params->jumlah[$key]); 
    //                     $this->db->set("Debit", 0); 
    //                     $this->db->set("Add", -1); 
    //                     $this->db->set("jenis_trans", "Keluar");
    //                 }
    //                 if($params->transaksi[$key] == 'SSP'){
    //                     if ($params->jtransaksi != "Masuk") {
    //                         $saldoakhir = $saldo;
    //                         $this->db->set("Debit", 0); 
    //                         $this->db->set("Kredit", 0); 
    //                         $this->db->set("Add", 1); 
    //                         $this->db->set("jenis_trans", "Masuk");
    //                     }
    //                     else{
    //                         $saldoakhir = $saldo;
    //                         $this->db->set("Kredit", 0); 
    //                         $this->db->set("Debit", 0); 
    //                         $this->db->set("Add", -1); 
    //                         $this->db->set("jenis_trans", "Keluar");
    //                     }

    //                 }
    //                 $this->db->set("Tipe_Kas",$kat7);
    //                 $this->db->set("bank_trans", "");
    //                 $this->db->set("Buku", 'KAS');
    //                 $this->db->set("Cabang", $params->cabang);
    //                 $this->db->set("Jurnal_ID", $jurnalidbanding); 
    //                 $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
    //                 $this->db->set("Kategori",'KAS'); 
    //                 $this->db->set("Kategori_2", $params->katagori[$key]); 
    //                 $this->db->set("Kategori3", $params->kategori3[$key]); 
    //                 $this->db->set("Transaksi", $params->transaksi[$key]); 
    //                     // $this->db->set("Kode_Transaksi", $kode); 
    //                 $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
    //                 $this->db->set("Ket_2", $kat6); 
    //                 $this->db->set("Ket_3", $kat7); 
    //                 $this->db->set("Jumlah", $params->jumlah[$key]); 
    //                 $this->db->set("Saldo_Awal", $saldoawal); 
    //                 $this->db->set("Saldo_Akhir", $saldoakhir); 
    //                 $this->db->set("Status",$params->Status[$key]); 
    //                 $this->db->set("CR", $params->cr[$key]);
    //                 $this->db->set("DR", $params->dr[$key]); 
    //                 $this->db->set("Bulan", date("Ym")); 
    //                 $this->db->set("Counter", $lastjurnalbanding); 
    //                 $this->db->set("No_Voucher", $noDocbanding); 
    //                 $this->db->set("Added_User", $this->user); 
    //                 $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
    //                 $valid =  $this->db->insert("trs_buku_transaksi");
    //                 if($valid){
    //                     $this->db->set("Jurnal_Acu", $kat6.'/'.$lastjurnalbanding);
    //                     $this->db->where("Jurnal_ID", $jurnalid);
    //                     $valid =  $this->db->update("trs_buku_transaksi");
    //                 }
                        
    //             }

    //             if($kat7 == 'Titipan'){
    //                 $this->db->set("Cabang", $params->cabang);
    //                 $this->db->set("Tanggal", date("Y-m-d")); 
    //                 $this->db->set("Time", date("Y-m-d H:i:s")); 
    //                 $this->db->set("NoTitipan", $NoTitipan); 
    //                 $this->db->set("ValueTitipan", $params->jumlah[$key]); 
    //                 $this->db->set("Saldo", $params->jumlah[$key]); 
    //                 $this->db->set("Status", "Aktif");
    //                 $this->db->set("Create_by", $this->user); 
    //                 $this->db->set("Create_at", date("Y-m-d H:i:s")); 
    //                 $valid =  $this->db->insert("trs_buku_titipan");
    //             }
    //             if($params->transaksi[$key] =='Penambahan Kasbon'){
    //                 $expld2 = explode("~", $params->karyawan[$key]);
    //                 $kd = $expld2[0];
    //                 $nama = $expld2[1];
    //                 $Jabatan = $expld2[2];
    //                 $this->db->set("NamaKaryawan", $nama); 
    //                 $this->db->set("KodeKaryawan", $kd); 
    //                 // $this->db->set("Ket_4", $Jabatan);
    //                 $this->db->set("Cabang", $params->cabang);
    //                 $this->db->set("Tanggal", date("Y-m-d")); 
    //                 $this->db->set("TimeTransaksi", date("Y-m-d H:i:s")); 
    //                 $this->db->set("NoKasbon", $NoKontra); 
    //                 $this->db->set("ValueKasbon",$params->jumlah[$key]); 
    //                 $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
    //                 $this->db->set("Status", "Open");
    //                 $this->db->set("NoVoucher", $noDokumen);
    //                 $this->db->set("JurnalID", $jurnalid);
    //                 $this->db->set("Transaksi", $params->transaksi[$key]);
    //                 $this->db->set("Create_by", $this->user); 
    //                 $this->db->set("Create_at", date("Y-m-d H:i:s")); 
    //                 $valid =  $this->db->insert("trs_buku_kasbon");
    //             }
    //             if($params->transaksi[$key] =='Realisasi Kasbon'){
    //                 $expld2 = explode("~", $params->karyawan[$key]);
    //                 $expld = explode("~", $params->nokontra[$key]);
    //                 $kd = $expld2[0];
    //                 $nama = $expld2[1];
    //                 $Jabatan = $expld2[2];
    //                 $NoKasbon = $expld[0];
    //                 $this->db->set("NamaKaryawan", $nama); 
    //                 $this->db->set("KodeKaryawan", $kd); 
    //                 // $this->db->set("Ket_4", $Jabatan);
    //                 $this->db->set("Cabang", $params->cabang);
    //                 $this->db->set("Tanggal", date("Y-m-d")); 
    //                 $this->db->set("TimeTransaksi", date("Y-m-d H:i:s")); 
    //                 $this->db->set("NoKasbon", $NoKontra); 
    //                 $this->db->set("ValueRealisasi",$params->jumlah[$key]); 
    //                 $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
    //                 $this->db->set("Status", "Closed");
    //                 $this->db->set("NoVoucher", $noDokumen);
    //                 $this->db->set("JurnalID", $jurnalid);
    //                 $this->db->set("Transaksi", $params->transaksi[$key]);
    //                 $this->db->set("NoKasbon_Asal", $NoKasbon);
    //                 $this->db->set("Create_by", $this->user); 
    //                 $this->db->set("Create_at", date("Y-m-d H:i:s")); 
    //                 $valid =  $this->db->insert("trs_buku_kasbon");

    //                 $this->db->set("TanggalClosed", date("Y-m-d"));
    //                 $this->db->set("TimeClosed", date("Y-m-d H:i:s"));
    //                 $this->db->set("Status", "Closed"); 
    //                 $this->db->where("Cabang", $params->cabang);
    //                 $this->db->where("NoKasbon", $NoKasbon); 
    //                 $valid =  $this->db->update("trs_buku_kasbon");
    //             }

    //         }
    //     }
    //     if($valid){
    //          $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = '".$params->kategori."' and Cabang = '".$this->cabang."'");
    //     }
    //     $no = substr($noDokumen, -7);
    //     // $this->Model_main->saveNoDokumen($params->kategori, $no);
    //     return $valid;
    // }
    public function saveGLTransaksi($params=null)
    {
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = '".$params->kategori."' and length(No_Voucher)> 13")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
               $lastNumber = $data[0]->no + 1; 
        
        }

        $noDokumen = date("y").$kodeLamaCabang.$params->kategori.$lastNumber;

        //================= end of running number ========================================//
        $counter = $this->getJurnalID($params->kategori);
        if (!empty($counter))
            $no = $counter;
        else
            $no = 1000000;  

        // $saldo = $this->getSaldoAwal($params->kategori);
        if($params->kategori == 'KAS'){
            $saldo = $this->getSaldoAwalKas($params->tipe_kas);
        }else{
            $saldo = $this->getSaldoAwalBank($params->bank_trans);
        }
        $saldoakhir = (!empty($saldo->saldo)) ? $saldo->saldo:0;
        foreach ($params->transaksi as $key => $value) {
            $no++;
            $expld_tipe = explode("~", $params->tipe[$key]);
            $kat6 = $expld_tipe[0];
            $kat7 = $expld_tipe[1];
            $saldoawal = $saldoakhir;
            //==== buat jurnal id ======
            $dtjurnal = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = '".$params->kategori."'")->result();
            if(empty($dtjurnal[0]->no) || $dtjurnal[0]->no == ""){
              $lastjurnal = 1000001;
            }else {
               $lastjurnal = $dtjurnal[0]->no + 1;          
            }

            $jurnalid = $params->kategori."/".$lastjurnal;
            //========================
            if ($params->jtransaksi == "Masuk") {
                $saldoakhir = $saldoakhir + $params->jumlah[$key];
                $this->db->set("Debit", $params->jumlah[$key]); 
                $this->db->set("Kredit", 0); 
                $this->db->set("Add", 1); 
            }
            else{
                $saldoakhir = $saldoakhir - $params->jumlah[$key];
                $this->db->set("Kredit", $params->jumlah[$key]); 
                $this->db->set("Debit", 0); 
                $this->db->set("Add", -1); 
            }

            if ($kat6 == 'Bank') { 
                if($params->kategori == 'KAS'){
                    $this->db->set("Tipe_Kas", $params->tipe_kas);
                    $this->db->set("bank_trans", "");
                }else{
                     $this->db->set("Bank_Ref", $params->bank[$key]);
                     $this->db->set("bank_trans", $params->bank_trans);
                }
            }
            elseif ($kat6 == 'Karyawan') {
                $expld2 = explode("~", $params->karyawan[$key]);
                $kd = $expld2[0];
                $nama = $expld2[1];
                $Jabatan = $expld2[2];
                if(empty($params->bank[$key]) || $params->bank[$key] == ""){
                    $params->bank[$key] = "";
                }
                $this->db->set("Karyawan", $nama); 
                $this->db->set("Kode_Karyawan", $kd); 
                $this->db->set("Ket_4", $Jabatan);
                $this->db->set("NoDIH", $params->NoDIH[$key]); 
                if($params->kategori == 'KAS'){
                    $this->db->set("Tipe_Kas", $params->tipe_kas);
                    $this->db->set("bank_trans", "");
                }else{
                     $this->db->set("Bank_Ref", $params->bank[$key]);
                     $this->db->set("bank_trans", $params->bank_trans);
                }

                if($kat7== 'Titipan'){
                    $kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
                    $kodeLamaCabang = $kodeLamaCabang->KodeGL;
                    $kodeDokumen = $this->Model_main->kodeDokumen('Titipan');
                    $data = $this->db->query("select max(right(NoTitipan,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."'")->result();
                    if(empty($data[0]->no) || $data[0]->no == ""){
                        $titipanNumber = 1000001;
                    }else {
                        $titipanNumber = ($data[0]->no) + 1; 
                    }
                    $NoTitipan = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$titipanNumber;
                    $this->db->set("NoTitipan", $NoTitipan);
                }
                if($params->transaksi[$key] =='Penambahan Kasbon' || $params->transaksi[$key] =='Realisasi Kasbon'){
                    $kodeLamaCabang = $this->db->query("select KodeGL from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
                    $kodeLamaCabang = $kodeLamaCabang->KodeGL;
                    $kodeDokumen = $this->Model_main->kodeDokumen('KasBon');
                    $data = $this->db->query("SELECT MAX(RIGHT(NoKasbon,7)) AS no FROM trs_buku_kasbon WHERE Cabang ='".$this->cabang."'")->result();
                    if(empty($data[0]->no) || $data[0]->no == ""){
                        $kontraNumber = 1000001;
                    }else {
                        $kontraNumber = ($data[0]->no) + 1; 
                    }
                    $NoKontra = date("y").date("m").$kodeLamaCabang.$kodeDokumen.$kontraNumber;
                    $this->db->set("NoKontrabon", $NoKontra);
                }
            }elseif($kat6 == 'Giro'){
                $this->db->set("bank_trans", $params->bank_trans);
                $giro = explode("~", $params->giro[$key]);
                $this->db->set("NoGiro", $giro[0].'~'.$giro[3]); 
            }elseif($kat6 == 'PPH23'){
                $this->db->set("tipe_pph", $params->pph23[$key]);
                if($params->kategori == 'KAS'){
                    $this->db->set("Tipe_Kas", $params->tipe_kas);
                    $this->db->set("bank_trans", "");
                }else{
                     $this->db->set("Bank_Ref", $params->bank[$key]);
                     $this->db->set("bank_trans", $params->bank_trans);
                }
            }else{
                if($params->kategori == 'KAS'){
                    $this->db->set("Tipe_Kas", $params->tipe_kas);
                    $this->db->set("bank_trans", "");
                }else{
                     $this->db->set("Bank_Ref", $params->bank[$key]);
                     $this->db->set("bank_trans", $params->bank_trans);
                }
            }

            /*if($params->transaksi[$key] == 'SSP'){
                if ($params->jtransaksi == "Masuk") {
                    $saldoakhir = $saldo;
                    $this->db->set("Debit", 0); 
                    $this->db->set("Kredit", 0); 
                    $this->db->set("Add", 1); 
                }
                else{
                    $saldoakhir = $saldo;
                    $this->db->set("Kredit", 0); 
                    $this->db->set("Debit", 0); 
                    $this->db->set("Add", -1); 
                }

            }*/
            $this->db->set("Buku", $params->kategori);
            $this->db->set("Cabang", $params->cabang);
            $this->db->set("Jurnal_ID", $jurnalid); 
            $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
            $this->db->set("Kategori", $params->kategori); 
            $this->db->set("Kategori_2", $params->katagori[$key]); 
            $this->db->set("Kategori3", $params->kategori3[$key]); 
            $this->db->set("Transaksi", $params->transaksi[$key]); 
            // $this->db->set("Kode_Transaksi", $kode); 
            $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
            $this->db->set("Ket_2", $kat6); 
            $this->db->set("Ket_3", $kat7); 
            $this->db->set("Jumlah", $params->jumlah[$key]); 
            $this->db->set("Saldo_Awal", $saldoawal); 
            $this->db->set("Saldo_Akhir", $saldoakhir); 
            $this->db->set("Status",$params->Status[$key]); 
            $this->db->set("CR", $params->cr[$key]);
            $this->db->set("DR", $params->dr[$key]); 
            $this->db->set("Bulan", date("Ym")); 
            $this->db->set("Counter", $lastNumber); 
            $this->db->set("No_Voucher", $noDokumen); 
            $this->db->set("jenis_trans", $params->jtransaksi);
            $this->db->set("Added_User", $this->user); 
            $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
            $valid =  $this->db->insert("trs_buku_transaksi");
            if($valid){
                if($kat6 == 'Bank'){
                        //================ Running Number ======================================//
                        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                        $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'Bank' and length(No_Voucher)> 13")->result();
                        if(empty($data[0]->no) || $data[0]->no == ""){
                          $lastNumber = 1000001;
                        }else {
                            // if($params->kategori == 'KAS'){
                            // $lastNumber = substr($data[0]->no,7) + 1;
                            // }else{
                               $lastNumber = $data[0]->no + 1; 
                            // }
                          
                        }

                        $noDocbanding = date("y").$kodeLamaCabang.'Bank'.$lastNumber;
                        //==buat jurnal id==
                        $dtjurnalbanding = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'Bank'")->result();
                        if(empty($dtjurnalbanding[0]->no) || $dtjurnalbanding[0]->no == ""){
                          $lastjurnalbanding = 1000001;
                        }else {
                           $lastjurnalbanding = $dtjurnalbanding[0]->no + 1;          
                        }

                        // $jurnalidbanding = $params->kategori."/".$lastjurnalbanding;
                        //================= end of running number ========================================//
                        $counter = $this->getJurnalID($params->kategori);
                        if (!empty($counter))
                            {$no = $counter;}
                        else
                            {$no = 1000000;} 
                        $saldox = $this->getSaldoAwalBank($params->bank[$key]);
                        $saldoakhirx = (!empty($saldox->saldo)) ? $saldox->saldo:0;
                        // foreach ($params->transaksi as $key => $value) {
                            // $no++;
                            $expld_tipe = explode("~", $params->tipe[$key]);
                            $kat6 = $expld_tipe[0];
                            $kat7 = $expld_tipe[1];
                            $saldoawalx = $saldoakhirx;

                            if ($params->jtransaksi != "Masuk") {
                                $saldoakhirx = $saldoakhirx + $params->jumlah[$key];
                                $this->db->set("Debit", $params->jumlah[$key]); 
                                $this->db->set("Kredit", 0); 
                                $this->db->set("Add", 1); 
                                $this->db->set("jenis_trans", "Masuk");
                            }
                            else{
                                $saldoakhirx = $saldoakhirx - $params->jumlah[$key];
                                $this->db->set("Kredit", $params->jumlah[$key]); 
                                $this->db->set("Debit", 0); 
                                $this->db->set("Add", -1); 
                                $this->db->set("jenis_trans", "Keluar");
                            }

                            if ($kat6 == 'Bank') {
                                if($params->kategori == 'KAS'){
                                    $this->db->set("Bank_Ref", $params->bank[$key]); 
                                }else{
                                    $this->db->set("Bank_Ref", $params->bank_trans); 
                                }
                            }
                            $this->db->set("Buku", "Bank");
                            $this->db->set("Cabang", $params->cabang);
                            $this->db->set("Jurnal_ID", 'Bank/'.$lastjurnalbanding); 
                            $this->db->set("Jurnal_Acu", $jurnalid); 
                            $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
                            $this->db->set("Kategori", "Bank"); 
                            $this->db->set("bank_trans", $params->bank[$key]);
                            $this->db->set("Kategori_2", $params->katagori[$key]); 
                            $this->db->set("Kategori3", $params->kategori3[$key]); 
                            $this->db->set("Transaksi", $params->transaksi[$key]); 
                            $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
                            $this->db->set("Ket_2", $kat6); 
                            $this->db->set("Ket_3", $kat7); 
                            $this->db->set("Jumlah", $params->jumlah[$key]); 
                            $this->db->set("Saldo_Awal", $saldoawalx); 
                            $this->db->set("Saldo_Akhir", $saldoakhirx); 
                            $this->db->set("Status",$params->Status[$key]); 
                            $this->db->set("CR", $params->cr[$key]);
                            $this->db->set("DR", $params->dr[$key]); 
                            $this->db->set("Bulan", date("Ym")); 
                            $this->db->set("Counter", $lastjurnalbanding); 
                            $this->db->set("No_Voucher", $noDocbanding); 
                            $this->db->set("Added_User", $this->user); 
                            $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
                            $valid =  $this->db->insert("trs_buku_transaksi");
                            if($valid){
                                    $this->db->set("Jurnal_Acu", 'Bank/'.$lastjurnalbanding);
                                    $this->db->where("Jurnal_ID", $jurnalid);
                                    $valid =  $this->db->update("trs_buku_transaksi");
                            }
                        // }
                }elseif($kat6 == 'Giro'){
                    $giro = explode("~", $params->giro[$key]);
                    $nogiro = $giro[0];
                    $bankgiro = $giro[3];
                    $dtgiro=$this->db->query("select ifnull(max_tolak,0) as 'max_tolak' from trs_buku_giro where NoGiro ='".$nogiro."' and Bank='".$bankgiro."'")->row();
                    $max = $dtgiro->max_tolak;
                    if($params->transaksi[$key] == 'Pencairan Giro'){
                        $this->db->set("StatusGiro","Cair");
                        $this->db->set("TglCairGiro",date('Y-m-d'));
                    }elseif($params->transaksi[$key] == 'Tolakan Giro'){
                        $this->db->set("StatusGiro","Tolak");
                        $this->db->set("max_tolak",$max + 1);
                        $this->db->set("TglTolakGiro",date('Y-m-d'));
                    }elseif($params->transaksi[$key] == 'Pembatalan Giro'){
                        $this->db->set("StatusGiro","Batal");
                        $this->db->set("TglTolakGiro",date('Y-m-d'));
                    }

                    $this->db->set("NoVoucher", $noDokumen);
                    $this->db->set("JurnalID", $jurnalid);
                    $this->db->set("Transaksi", $params->transaksi[$key]);
                    $this->db->set("modified_by", $this->user);
                    $this->db->set("modified_at", date("Y-m-d H:i:s"));
                    $this->db->where("Cabang", $params->cabang);
                    $this->db->where("NoGiro", $nogiro);
                    $this->db->where("Bank", $bankgiro);
                    $valid =  $this->db->update("trs_buku_giro");
                }elseif($kat6 == 'Kas')
                {
                    $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                    $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                    $data = $this->db->query("select max(right(No_Voucher,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'KAS' and length(No_Voucher)> 13")->result();
                    if(empty($data[0]->no) || $data[0]->no == ""){
                        $lastNumber = 1000001;
                    }else {
                        $lastNumber = $data[0]->no + 1;   
                    }

                    $noDocbanding = date("y").$kodeLamaCabang.'KAS'.$lastNumber;
                    $dtjurnalbanding = $this->db->query("select max(right(Jurnal_ID,7)) as 'no' from trs_buku_transaksi where Cabang = '".$this->cabang."' and Kategori = 'KAS'")->result();
                    if(empty($dtjurnalbanding[0]->no) || $dtjurnalbanding[0]->no == ""){
                        $lastjurnalbanding = 1000001;
                    }else {
                        $lastjurnalbanding = $dtjurnalbanding[0]->no + 1;          
                    }
                    $jurnalidbanding = "KAS/".$lastjurnalbanding;
                    $saldoz = $this->getSaldoAwalKas($kat7);
                    
                    $saldoakhirz = (!empty($saldoz->saldo)) ? $saldoz->saldo:0;
                    $saldoawalz = $saldoakhirz;
                    if ($params->jtransaksi != "Masuk") {
                        $saldoakhirz = $saldoakhirz + $params->jumlah[$key];
                        $this->db->set("Debit", $params->jumlah[$key]); 
                        $this->db->set("Kredit", 0); 
                        $this->db->set("Add", 1); 
                        $this->db->set("jenis_trans", "Masuk");
                    }
                    else{
                        $saldoakhirz = $saldoakhirz - $params->jumlah[$key];
                        $this->db->set("Kredit", $params->jumlah[$key]); 
                        $this->db->set("Debit", 0); 
                        $this->db->set("Add", -1); 
                        $this->db->set("jenis_trans", "Keluar");
                    }
                    if($params->transaksi[$key] == 'SSP'){
                        if ($params->jtransaksi != "Masuk") {
                            $saldoakhirz = $saldoz;
                            $this->db->set("Debit", 0); 
                            $this->db->set("Kredit", 0); 
                            $this->db->set("Add", 1); 
                            $this->db->set("jenis_trans", "Masuk");
                        }
                        else{
                            $saldoakhirz = $saldoz;
                            $this->db->set("Kredit", 0); 
                            $this->db->set("Debit", 0); 
                            $this->db->set("Add", -1); 
                            $this->db->set("jenis_trans", "Keluar");
                        }

                    }
                    $this->db->set("Tipe_Kas",$kat7);
                    $this->db->set("bank_trans", "");
                    $this->db->set("Buku", 'KAS');
                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Jurnal_ID", $jurnalidbanding); 
                    $this->db->set("Tanggal", date("Y-m-d H:i:s")); 
                    $this->db->set("Kategori",'KAS'); 
                    $this->db->set("Kategori_2", $params->katagori[$key]); 
                    $this->db->set("Kategori3", $params->kategori3[$key]); 
                    $this->db->set("Transaksi", $params->transaksi[$key]); 
                        // $this->db->set("Kode_Transaksi", $kode); 
                    $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
                    $this->db->set("Ket_2", $kat6); 
                    $this->db->set("Ket_3", $kat7); 
                    $this->db->set("Jumlah", $params->jumlah[$key]); 
                    $this->db->set("Saldo_Awal", $saldoawalz); 
                    $this->db->set("Saldo_Akhir", $saldoakhirz); 
                    $this->db->set("Status",$params->Status[$key]); 
                    $this->db->set("CR", $params->cr[$key]);
                    $this->db->set("DR", $params->dr[$key]); 
                    $this->db->set("Bulan", date("Ym")); 
                    $this->db->set("Counter", $lastjurnalbanding); 
                    $this->db->set("No_Voucher", $noDocbanding); 
                    $this->db->set("Added_User", $this->user); 
                    $this->db->set("Added_Time", date("Y-m-d H:i:s")); 
                    $valid =  $this->db->insert("trs_buku_transaksi");
                    if($valid){
                        $this->db->set("Jurnal_Acu", $kat6.'/'.$lastjurnalbanding);
                        $this->db->where("Jurnal_ID", $jurnalid);
                        $valid =  $this->db->update("trs_buku_transaksi");
                    }
                        
                }

                if($kat7 == 'Titipan'){
                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Tanggal", date("Y-m-d")); 
                    $this->db->set("Time", date("Y-m-d H:i:s")); 
                    $this->db->set("NoTitipan", $NoTitipan); 
                    $this->db->set("ValueTitipan", $params->jumlah[$key]); 
                    $this->db->set("Saldo", $params->jumlah[$key]); 
                    $this->db->set("Status", "Aktif");
                    $this->db->set("Create_by", $this->user); 
                    $this->db->set("Create_at", date("Y-m-d H:i:s")); 
                    $valid =  $this->db->insert("trs_buku_titipan");
                }
                if($params->transaksi[$key] =='Penambahan Kasbon'){
                    $expld2 = explode("~", $params->karyawan[$key]);
                    $kd = $expld2[0];
                    $nama = $expld2[1];
                    $Jabatan = $expld2[2];
                    $this->db->set("NamaKaryawan", $nama); 
                    $this->db->set("KodeKaryawan", $kd); 
                    // $this->db->set("Ket_4", $Jabatan);
                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Tanggal", date("Y-m-d")); 
                    $this->db->set("TimeTransaksi", date("Y-m-d H:i:s")); 
                    $this->db->set("NoKasbon", $NoKontra); 
                    $this->db->set("ValueKasbon",$params->jumlah[$key]); 
                    $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
                    $this->db->set("Status", "Open");
                    $this->db->set("NoVoucher", $noDokumen);
                    $this->db->set("JurnalID", $jurnalid);
                    $this->db->set("Transaksi", $params->transaksi[$key]);
                    $this->db->set("Create_by", $this->user); 
                    $this->db->set("Create_at", date("Y-m-d H:i:s")); 
                    $valid =  $this->db->insert("trs_buku_kasbon");
                }
                if($params->transaksi[$key] =='Realisasi Kasbon'){
                    $expld2 = explode("~", $params->karyawan[$key]);
                    $expld = explode("~", $params->nokontra[$key]);
                    $kd = $expld2[0];
                    $nama = $expld2[1];
                    $Jabatan = $expld2[2];
                    $NoKasbon = $expld[0];
                    $this->db->set("NamaKaryawan", $nama); 
                    $this->db->set("KodeKaryawan", $kd); 
                    // $this->db->set("Ket_4", $Jabatan);
                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Tanggal", date("Y-m-d")); 
                    $this->db->set("TimeTransaksi", date("Y-m-d H:i:s")); 
                    $this->db->set("NoKasbon", $NoKontra); 
                    $this->db->set("ValueRealisasi",$params->jumlah[$key]); 
                    $this->db->set("Keterangan", strtoupper($params->keterangan[$key])); 
                    $this->db->set("Status", "Closed");
                    $this->db->set("NoVoucher", $noDokumen);
                    $this->db->set("JurnalID", $jurnalid);
                    $this->db->set("Transaksi", $params->transaksi[$key]);
                    $this->db->set("NoKasbon_Asal", $NoKasbon);
                    $this->db->set("Create_by", $this->user); 
                    $this->db->set("Create_at", date("Y-m-d H:i:s")); 
                    $valid =  $this->db->insert("trs_buku_kasbon");

                    $this->db->set("TanggalClosed", date("Y-m-d"));
                    $this->db->set("TimeClosed", date("Y-m-d H:i:s"));
                    $this->db->set("Status", "Closed"); 
                    $this->db->where("Cabang", $params->cabang);
                    $this->db->where("NoKasbon", $NoKasbon); 
                    $valid =  $this->db->update("trs_buku_kasbon");
                }

            }
        }
        if($valid){
             $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = '".$params->kategori."' and Cabang = '".$this->cabang."'");
        }
        $no = substr($noDokumen, -7);
        // $this->Model_main->saveNoDokumen($params->kategori, $no);
        return $valid;
    }

    public function listDataTransaksi($Search = NULL, $tipe=null, $kat=null,$periode=null, $limit=null)
    {   
        if($kat == 'KAS'){
            if($tipe == 'All'){
                $query = $this->db->query("select * from trs_buku_transaksi where Kategori = '".$kat."' and IFNULL(Kategori_2,'') <> '' and Cabang = '".$this->cabang."' $Search $periode $limit")->result();
            }elseif($tipe == 'Kecil'){
                $query = $this->db->query("select * from trs_buku_transaksi where Kategori = '".$kat."' and IFNULL(Kategori_2,'') <> '' and Tipe_Kas = 'Kecil' and Cabang = '".$this->cabang."' $Search $periode $limit")->result();
            }elseif($tipe == 'Besar'){
                $query = $this->db->query("select * from trs_buku_transaksi where Kategori = '".$kat."' and IFNULL(Kategori_2,'') <> '' and Tipe_Kas = 'Besar' and Cabang = '".$this->cabang."' $Search $periode $limit")->result();
            }
        }elseif($kat == 'Bank'){
           if($tipe == 'All'){
                $query = $this->db->query("select * from trs_buku_transaksi where Kategori = '".$kat."' and IFNULL(Kategori_2,'') <> '' and Cabang = '".$this->cabang."' $Search $periode $limit")->result();
            }else{
                $query = $this->db->query("select * from trs_buku_transaksi where Kategori = '".$kat."' and IFNULL(Kategori_2,'') <> '' and bank_trans ='".$tipe."' and Cabang = '".$this->cabang."' $Search $periode $limit")->result();
            }
        }
        return $query;
    }

    public function getNoDIH($karyawan = NULL,$bank = NULL, $transaksi = NULL,$jenis=null)
    {   
        
        if($transaksi == 'Pelunasan Faktur  ( Piutang Dagang )' and $jenis == 'KAS'){
            $tipe = 'Cash';
            $query = $this->db->query("select distinct NoDIH from trs_pelunasan_detail where KodePenagih = '".$karyawan."' and  Cabang = '".$this->cabang."' and TipePelunasan in ('Cash','Transfer')")->result();
        }else if($transaksi == 'Pelunasan Faktur  ( Piutang Dagang )' and $jenis == 'Bank'){
            $tipe = 'Transfer';
            $query = $this->db->query("select distinct NoDIH from trs_pelunasan_detail where KodePenagih = '".$karyawan."' and Cabang = '".$this->cabang."' and TipePelunasan in ('Cash','Transfer')")->result();
        }else if($transaksi == 'Pencairan Giro'){
            $tipe = 'Giro';
            $query = $this->db->query("select distinct NoDIH from trs_pelunasan_detail where Giro = '".$bank."' and Cabang = '".$this->cabang."' and TipePelunasan='".$tipe."'")->result();
        }
        return $query;
    }

    public function getmutasikasbank($timestamp = NULL,$date = NULL)
    {
       $query = $this->db->query("
                            SELECT DATE(Tanggal) AS 'tgl_trans',
                                   concat('KAS ','~ ',Transaksi) as 'Transaksi',
                                   trs_buku_transaksi.NoDIH AS 'DIH_KasBank',
                                   t_dih.NoDIH AS 'DIH_Pelunasan',
                                   IFNULL(Jumlah,0) AS 'saldo_KasBank',
                                   IFNULL(t_dih.value_pelunasan,0) AS 'saldo_dih',
                                   (CASE WHEN IFNULL(Jumlah,0) != IFNULL(t_dih.value_pelunasan,0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                             FROM  trs_buku_transaksi LEFT JOIN 
                             ( SELECT NoDIH,
                                  TglPelunasan,
                                   SUM(valuePelunasan) AS 'value_pelunasan'
                                FROM trs_pelunasan_detail
                                WHERE TipePelunasan = 'Cash' 
                                GROUP BY NoDIH,TglPelunasan) AS t_dih ON 
                                t_dih.NoDIH = trs_buku_transaksi.NoDIH AND 
                                t_dih.TglPelunasan = DATE(trs_buku_transaksi.Tanggal)
                             WHERE Transaksi = 'Pelunasan Faktur  ( Piutang Dagang )' AND
                                    Kategori= 'KAS' and DATE(Tanggal) = '".$date."'
                             
                             UNION 
                             
                             SELECT  TglPelunasan AS 'tgl_trans',
                                'KAS ~ Pelunasan Faktur  ( Piutang Dagang )'  AS 'Transaksi',
                                '' AS 'DIH_KasBank',
                                trs_pelunasan_detail.NoDIH AS 'DIH_Pelunasan',
                                IFNULL(t_kasir.saldo_KasBank,0) AS 'saldo_KasBank',
                                IFNULL(SUM(valuePelunasan),0) AS 'saldo_dih',
                                (CASE WHEN IFNULL(t_kasir.saldo_KasBank,0) != IFNULL(SUM(valuePelunasan),0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                                FROM trs_pelunasan_detail LEFT JOIN (
                                SELECT DATE(Tanggal) AS 'tgl_trans',
                                    Transaksi,
                                    Kode_Karyawan AS 'doc_ref',
                                    trs_buku_transaksi.NoDIH AS 'NoDIH',
                                    IFNULL(Jumlah,0) AS 'saldo_KasBank'
                                FROM trs_buku_transaksi 
                                where transaksi = 'Pelunasan Faktur  ( Piutang Dagang )' and Kategori = 'KAS') AS t_kasir ON 
                                t_kasir.NoDIH = trs_pelunasan_detail.`NoDIH` AND
                                t_kasir.tgl_trans  = trs_pelunasan_detail.TglPelunasan      
                                WHERE TipePelunasan = 'Cash' AND 
                                TglPelunasan = '".$date."' AND 
                                trs_pelunasan_detail.NoDIH NOT IN  (
                                SELECT IFNULL(trs_buku_transaksi.NoDIH,'') AS 'NoDIH' FROM trs_buku_transaksi
                                WHERE DATE(tanggal) = '".$date."' AND transaksi = 'Pelunasan Faktur  ( Piutang Dagang )' and Kategori = 'KAS')
                                GROUP BY trs_pelunasan_detail.NoDIH,TglPelunasan,t_kasir.saldo_KasBank

                             UNION 
                                
                                SELECT DATE(Tanggal) AS 'tgl_trans',
                                   concat('Transfer ','~ ',Transaksi) as 'Transaksi',
                                   trs_buku_transaksi.NoDIH AS 'DIH_KasBank',
                                   t_dih.NoDIH AS 'DIH_Pelunasan',
                                   IFNULL(Jumlah,0) AS 'saldo_KasBank',
                                   IFNULL(t_dih.value_pelunasan,0) AS 'saldo_dih',
                                   (CASE WHEN IFNULL(Jumlah,0) != IFNULL(t_dih.value_pelunasan,0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                             FROM  trs_buku_transaksi LEFT JOIN 
                             ( SELECT NoDIH,
                                  TglPelunasan,
                                   SUM(valuePelunasan) AS 'value_pelunasan'
                                FROM trs_pelunasan_detail
                                WHERE TipePelunasan = 'Transfer' 
                                GROUP BY NoDIH,TglPelunasan) AS t_dih ON 
                                t_dih.NoDIH = trs_buku_transaksi.NoDIH AND 
                                t_dih.TglPelunasan = DATE(trs_buku_transaksi.Tanggal)
                             WHERE Transaksi = 'Pelunasan Faktur  ( Piutang Dagang )' and Kategori = 'Bank' and
                                   DATE(Tanggal) = '".$date."'

                            UNION 
                             
                             SELECT  TglPelunasan AS 'tgl_trans',
                                'Transfer ~ Pelunasan Faktur  ( Piutang Dagang )'  AS 'Transaksi',
                                '' AS 'DIH_KasBank',
                                trs_pelunasan_detail.NoDIH AS 'DIH_Pelunasan',
                                IFNULL(t_kasir.saldo_KasBank,0) AS 'saldo_KasBank',
                                IFNULL(SUM(valuePelunasan),0) AS 'saldo_dih',
                                (CASE WHEN IFNULL(t_kasir.saldo_KasBank,0) != IFNULL(SUM(valuePelunasan),0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                                FROM trs_pelunasan_detail LEFT JOIN (
                                SELECT DATE(Tanggal) AS 'tgl_trans',
                                    Transaksi,
                                    Kode_Karyawan AS 'doc_ref',
                                    trs_buku_transaksi.NoDIH AS 'NoDIH',
                                    IFNULL(Jumlah,0) AS 'saldo_KasBank'
                                FROM trs_buku_transaksi ) AS t_kasir ON 
                                t_kasir.NoDIH = trs_pelunasan_detail.`NoDIH` AND
                                t_kasir.tgl_trans  = trs_pelunasan_detail.TglPelunasan      
                                WHERE TipePelunasan = 'Transfer' AND 
                                TglPelunasan = '".$date."' AND 
                                trs_pelunasan_detail.NoDIH NOT IN  (
                                SELECT IFNULL(trs_buku_transaksi.NoDIH,'') AS 'NoDIH' FROM trs_buku_transaksi
                                WHERE DATE(tanggal) = '".$date."' AND Transaksi = 'Pelunasan Faktur  ( Piutang Dagang )' and Kategori = 'Bank' )
                                GROUP BY trs_pelunasan_detail.NoDIH,TglPelunasan,t_kasir.saldo_KasBank 

                             UNION 
                                
                                SELECT DATE(Tanggal) AS 'tgl_trans',
                                   'Pencairan Giro' AS Transaksi,
                                   trs_buku_transaksi.NoGiro AS 'DIH_KasBank',
                                   CONCAT(giro.NoGiro,'~',giro.Bank) AS 'DIH_Pelunasan',
                                   SUM(CASE WHEN Transaksi = 'Pencairan Giro' THEN jumlah WHEN Transaksi = 'Tolakan Giro' THEN jumlah * -1 ELSE 0 END ) AS 'saldo_KasBank',
                                   giro.ValueGiro AS 'saldo_dih',
                                   (CASE WHEN (SUM(CASE WHEN Transaksi = 'Pencairan Giro' THEN jumlah WHEN Transaksi = 'Tolakan Giro' THEN jumlah * -1 ELSE 0 END )) != IFNULL(giro.ValueGiro,0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                             FROM  trs_buku_transaksi
                             LEFT JOIN (
                                SELECT * FROM trs_giro
                                WHERE statusgiro ='Cair' AND 
                                      SisaGiro = 0 ) AS giro ON giro.NoGiro = SUBSTRING_INDEX(trs_buku_transaksi.NoGiro,'~',1) AND 
                                giro.TglCair = DATE(trs_buku_transaksi.`Tanggal`)
                             WHERE Transaksi IN ('Pencairan Giro','Tolakan Giro') AND 
                                   DATE(Tanggal) = '".$date."'
                             GROUP BY DATE(Tanggal),DIH_KasBank

                             UNION 

                             SELECT trs_giro.`TglCair` as 'tgl_trans',
                            'Pencairan Giro'  AS 'Transaksi',
                            '' AS 'DIH_KasBank',
                            CONCAT(trs_giro.NoGiro,'~',trs_giro.Bank) AS 'DIH_Pelunasan',
                            IFNULL(bank_giro.saldo_KasBank,0) AS 'saldo_KasBank',
                            IFNULL(trs_giro.`ValueGiro`,0) AS 'saldo_dih',
                            (CASE WHEN IFNULL(bank_giro.saldo_KasBank,0) != IFNULL(trs_giro.`ValueGiro`,0) THEN 'selisih' ELSE 'Ok' END ) AS 'status'
                         FROM trs_giro LEFT JOIN 
                            ( SELECT DATE(Tanggal) AS 'tgl_trans',
                                trs_buku_transaksi.NoGiro,
                                SUM(CASE WHEN Transaksi = 'Pencairan Giro' THEN jumlah WHEN Transaksi = 'Tolakan Giro' THEN jumlah * -1 ELSE 0 END ) AS 'saldo_KasBank'
                             FROM trs_buku_transaksi
                             WHERE  Transaksi IN ('Pencairan Giro','Tolakan Giro','Pembatalan Giro') 
                             GROUP BY DATE(Tanggal),trs_buku_transaksi.NoGiro) AS bank_giro ON SUBSTRING_INDEX(bank_giro.NoGiro,'~',1) = trs_giro.`NoGiro` AND 
                                bank_giro.tgl_trans = trs_giro.`TglCair`
                        WHERE statusgiro ='Cair' AND 
                              SisaGiro = 0 AND 
                              trs_giro.`TglCair` = '".$date."' AND trs_giro.`NoGiro` NOT IN (
                              SELECT IFNULL(SUBSTRING_INDEX(trs_buku_transaksi.NoGiro,'~',1),'') AS 'NoDIH' FROM trs_buku_transaksi
                                                        WHERE DATE(tanggal) = '".$date."' AND transaksi IN ('Pencairan Giro','Tolakan Giro') );
                             
                             
                             ")->result();
        return $query;

    }

    public function setMutasiKas($params = null)
    {
        // log_message("error",print_r($params,true));
        $list = $params;
        $date = date('d');
        $lastdate = date('t');
        $cek = $this->db->query("
                                  select count(tanggal) as 'tgl' from trs_settlement_kasbank_day where tanggal = CURDATE()
                              ")->result();
        if($cek[0]->tgl > 0){
          $message = "data_ada";
          $valid   = true;
        }
        else{
          $i=0;
          foreach ($list as $status) {
            $saldo_KasBank   = $status["saldo_KasBank"];
            $saldo_dih       = $status["saldo_dih"];
            $status          = $status["status"];
            if($status == 'selisih'){
                $message = "no_balance";
                $valid = true;
                break; 
            }
          }
          foreach ($list as $key) 
          { 
            
            if($key["Transaksi"] == 'Penerimaan Piutang Dagang'){
              $tipe = 'KAS';
            }else{
              $tipe = 'BANK';
            }
            $i++;
            if($date != $lastdate ){
              $this->db->set("Cabang", $this->cabang);
              // $this->db->set("tanggal", $key["tgl_trans"]);
              $this->db->set("noline", $i);
              $this->db->set("TipeTransaksi", $tipe);
              $this->db->set("Transaksi", $key["Transaksi"]);
              $this->db->set("NoDIH_kasbank", $key["DIH_KasBank"]);
              $this->db->set("NoDIH_pelunasan", $key["DIH_Pelunasan"]);
              $this->db->set("saldo_KasBank", $key["saldo_KasBank"]);
              $this->db->set("saldo_pelunasan", $key["saldo_dih"]);  
              $this->db->set("Status", "Ok");
              $this->db->set("tanggal", date('Y-m-d'));
              $this->db->set("time_settlement", date('Y-m-d H:i:s'));
              $this->db->set("create_by", $this->session->userdata('username'));
              $this->db->set("create_date",  date('Y-m-d H:i:s'));
              $valid = $this->db->insert('trs_settlement_kasbank_day'); 
              $message = "sukses_day";
            }else{
              $this->db->set("Cabang", $this->cabang);
              $this->db->set("noline", $i);
              $this->db->set("TipeTransaksi", $tipe);
              $this->db->set("Transaksi", $key["Transaksi"]);
              $this->db->set("NoDIH_kasbank", $key["DIH_KasBank"]);
              $this->db->set("NoDIH_pelunasan", $key["DIH_Pelunasan"]);
              $this->db->set("saldo_KasBank", $key["saldo_KasBank"]);
              $this->db->set("saldo_pelunasan", $key["saldo_dih"]);
              $this->db->set("Status", "Ok");
              $this->db->set("tanggal", date('Y-m-d'));
              $this->db->set("time_settlement", date('Y-m-d H:i:s'));
              $this->db->set("create_by", $this->session->userdata('username'));
              $this->db->set("create_date",  date('Y-m-d H:i:s'));
              $valid = $this->db->insert('trs_settlement_kasbank_day'); 

              $this->db->set("Cabang", $this->cabang);
              $this->db->set("tahun", date('Y'));
              $this->db->set("bulan", date('m'));
              $this->db->set("noline", $i);
              $this->db->set("TipeTransaksi", $tipe);
              $this->db->set("Transaksi", $key["Transaksi"]);
              $this->db->set("NoDIH_kasbank", $key["DIH_KasBank"]);
              $this->db->set("NoDIH_pelunasan", $key["DIH_Pelunasan"]);
              $this->db->set("saldo_KasBank", $key["saldo_KasBank"]);
              $this->db->set("saldo_pelunasan", $key["saldo_dih"]);
              $this->db->set("Status", "Ok");
              $this->db->set("tanggal", date('Y-m-d'));
              $this->db->set("time_settlement", date('Y-m-d H:i:s'));
              $this->db->set("create_by", $this->session->userdata('username'));
              $this->db->set("create_date",  date('Y-m-d H:i:s'));
              $valid = $this->db->insert('trs_settlement_kasbank_month'); 
              $message = "sukses_month";
            }
          }

        }
        
        $data = ["update" =>$valid,"message"=>$message];
        return $data;
    }

    public function saveDataBukuGiro($params = null)
    {
        foreach ($params->nogiro as $key => $value) 
        {
              $pelanggan = explode("~",$params->pelanggan[$key]);
              $kodePelanggan = $pelanggan[0];
              $namaPelanggan = $pelanggan[1];

              $karyawan = explode("~",$params->karyawan[$key]);
              $kodeKaryawan = $karyawan[0];
              $namaKaryawan = $karyawan[1];
              $this->db->set("Cabang", $this->cabang);
              $this->db->set("Tanggal", date('Y-m-d'));
              $this->db->set("TimeGiro", date('Y-m-d H:i:s'));
              $this->db->set("TglJTOGiro",$params->jtogiro[$key]);
              $this->db->set("Bank", $params->bank[$key]);
              $this->db->set("KodePelanggan", $kodePelanggan);
              $this->db->set("NamaPelanggan", $namaPelanggan);
              $this->db->set("KodeKaryawan", $kodeKaryawan);
              $this->db->set("NamaKaryawan", $namaKaryawan);
              $this->db->set("NoGiro", $params->nogiro[$key]);
              $this->db->set("StatusGiro", "Terima");
              $this->db->set("ValueGiro",$params->valuegiro[$key]);
              $this->db->set("create_by", $this->session->userdata('username'));
              $this->db->set("create_at",  date('Y-m-d H:i:s'));
              $valid = $this->db->insert('trs_buku_giro'); 

        }
        return $valid;
    }

    public function listDataBukuGiro($Search=null,$limit = NULL)
    {   
        $query = $this->db->query("select * from trs_buku_giro where Cabang = '".$this->cabang."' $Search order by Tanggal DESC,NoGiro ASC $limit")->result();
        return $query;
    }

    public function listDataBukuKasbon($Search=null,$limit = NULL)
    {   
        $query = $this->db->query("select * from trs_buku_kasbon where Cabang = '".$this->cabang."' AND Status NOT LIKE '%Batal%' $Search order by Tanggal DESC,NoKasbon ASC $limit")->result();
        return $query;
    }
    public function saveopnamekas($params = null)
    {
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("TglOpname", date('Y-m-d',strtotime($params->tanggal)));
        $this->db->set("TipeKas", $params->tipe_kas);
        $this->db->set("seratusribu", $params->seratusribu);
        $this->db->set("valseratusribu", $params->valseratusribu);
        $this->db->set("limapuluhribu", $params->limapuluhribu);
        $this->db->set("vallimapuluhribu", $params->vallimapuluhribu);
        $this->db->set("duapuluhribu", $params->duapuluhribu);
        $this->db->set("valduapuluhribu", $params->valduapuluhribu);
        $this->db->set("sepuluhribu", $params->sepuluhribu);
        $this->db->set("valsepuluhribu", $params->valsepuluhribu);
        $this->db->set("limaribu", $params->limaribu);
        $this->db->set("vallimaribu", $params->vallimaribu);
        $this->db->set("duaribu", $params->duaribu);
        $this->db->set("valduaribu", $params->valduaribu);
        $this->db->set("seribu", $params->seribu);
        $this->db->set("valseribu", $params->valseribu);
        $this->db->set("seribulogam", $params->seribulogam);
        $this->db->set("valseribulogam", $params->valseribulogam);
        $this->db->set("limaratus", $params->limaratus);
        $this->db->set("vallimaratus", $params->vallimaratus);
        $this->db->set("duaratus", $params->duaratus);
        $this->db->set("valduaratus", $params->valduaratus);
        $this->db->set("seratus", $params->seratus);
        $this->db->set("valseratus", $params->valseratus);
        $this->db->set("saldoawal", $params->saldoawalmask);
        $this->db->set("status", "Opname Berhasil");
        $this->db->set("selisih", $params->saldoakhirmask);
        $this->db->set("totaluangkertas", $params->totaluangkertasmask);
        $this->db->set("totaluanglogam", $params->totaluanglogammask);
        $valid = $this->db->insert('trs_opname_kas');
        return $valid;
    }

    public function listDataOpnameKas($tgl=null)
    {   
        $query = $this->db->query("select * from trs_opname_kas where TglOpname = '".$tgl."' and Cabang = '".$this->cabang."'")->result();
        return $query;
    }

     public function getclosingkaskecil($cabang=null,$tgl=null)
    {   
        $transdate = date('Y-m-d', strtotime('-1 days'));
        // $query = $this->db->query("SELECT DISTINCT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Tipe_Kas`,
        //                                        IFNULL(sawal_kecil.Saldo_Akhir,0) AS 'Saldo_awal',
        //                                        IFNULL(terima_kecil.saldo,0) AS 'saldo_masuk',
        //                                        IFNULL(keluar_kecil.saldo,0) AS 'saldo_keluar',
        //                                        (IFNULL(sawal_kecil.Saldo_Akhir,0) + IFNULL(terima_kecil.saldo,0) - IFNULL(keluar_kecil.saldo,0)) AS 'Saldo_akhir',
        //                                        IFNULL(opnamekas.saldo,0) AS 'saldo_opname',
        //                                        IFNULL(kasbon.saldo,0) AS 'saldo_kasbon',
        //                                        IFNULL(bank_kecil.Saldo_Akhir,0) AS 'saldo_bankkecil'
        //                                 FROM   trs_buku_transaksi 
        //                                 LEFT JOIN
        //                                 (SELECT trs_buku_transaksi.`Tanggal`,
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     trs_buku_transaksi.`Saldo_Akhir` 
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
        //                                       DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
        //                                 ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_kecil ON 
        //                                           sawal_kecil.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas`
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
        //                                         trs_buku_transaksi.`jenis_trans` = 'Masuk'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
        //                                     trs_buku_transaksi.`Tipe_Kas`) AS terima_kecil ON 
        //                                           terima_kecil.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas` AND 
        //                                           terima_kecil.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
        //                                         trs_buku_transaksi.`jenis_trans` = 'Keluar'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
        //                                     trs_buku_transaksi.`Tipe_Kas`) AS keluar_kecil ON 
        //                                           keluar_kecil.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas` AND 
        //                                           keluar_kecil.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN 
        //                                 (SELECT trs_opname_kas.`TglOpname`,
        //                                        trs_opname_kas.`TipeKas`,
        //                                        (IFNULL(trs_opname_kas.`totaluangkertas`,0) + IFNULL(trs_opname_kas.`totaluanglogam`,0)) AS 'saldo'
        //                                 FROM trs_opname_kas
        //                                 WHERE trs_opname_kas.`TipeKas` ='Kecil') AS opnamekas ON opnamekas.TipeKas = trs_buku_transaksi.`Tipe_Kas` AND 
        //                                           opnamekas.TglOpname = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN 
        //                                 (SELECT trs_buku_kasbon.`Cabang`,
        //                                        SUM(trs_buku_kasbon.`ValueKasbon`) AS 'saldo'
        //                                 FROM trs_buku_kasbon
        //                                 WHERE trs_buku_kasbon.`Status` ='Open'
        //                                 GROUP BY trs_buku_kasbon.`Cabang`) AS kasbon ON kasbon.Cabang = trs_buku_transaksi.`Cabang`
        //                                 LEFT JOIN
        //                                 (SELECT trs_buku_transaksi.`Tanggal`,
        //                                     trs_buku_transaksi.`Saldo_Akhir` 
        //                                   FROM trs_buku_transaksi join mst_gl_bank on 
        //                                        trs_buku_transaksi.bank_trans = mst_gl_bank.`Nama Perkiraan`
        //                                   WHERE mst_gl_bank.`Bank Ref` = '2'
        //                                 ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS bank_kecil ON 
        //                                           bank_kecil.Tanggal <= DATE(trs_buku_transaksi.`Tanggal`)
        //                                 WHERE DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                       trs_buku_transaksi.`Tipe_Kas` = 'Kecil';")->result();
        $query = $this->db->query("SELECT DISTINCT mst_calendar.`list_date` AS 'tanggal',
                                           sawal_kecil.Tipe_Kas,
                                           IFNULL(sawal_kecil.Saldo_Akhir,0) AS 'Saldo_awal',
                                           IFNULL(terima_kecil.saldo,0) AS 'saldo_masuk',
                                           IFNULL(keluar_kecil.saldo,0) AS 'saldo_keluar',
                                           (IFNULL(sawal_kecil.Saldo_Akhir,0) + IFNULL(terima_kecil.saldo,0) - IFNULL(keluar_kecil.saldo,0)) AS 'Saldo_akhir',
                                           IFNULL(opnamekas.saldo,0) AS 'saldo_opname',
                                           IFNULL(kasbon.saldo,0) AS 'saldo_kasbon',
                                           IFNULL(bank_kecil.Saldo_Akhir,0) AS 'saldo_bankkecil'
                                     FROM  mst_calendar 
                                     LEFT JOIN
                                           (SELECT trs_buku_transaksi.`Cabang`,
                                               trs_buku_transaksi.`Tanggal`,
                                                   trs_buku_transaksi.`Tipe_Kas`,
                                                   trs_buku_transaksi.`Saldo_Akhir` 
                                              FROM trs_buku_transaksi
                                             WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
                                                   DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
                                              ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_kecil ON 
                                            sawal_kecil.Cabang = mst_calendar.`Cabang`
                                     LEFT JOIN
                                           (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                   trs_buku_transaksi.`Tipe_Kas`,
                                                   SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                              FROM trs_buku_transaksi
                                             WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
                                                   trs_buku_transaksi.`jenis_trans` = 'Masuk'
                                            GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                                   trs_buku_transaksi.`Tipe_Kas`) AS terima_kecil ON 
                                                   terima_kecil.tanggal = mst_calendar.`list_date`
                                    LEFT JOIN
                                            (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                    trs_buku_transaksi.`Tipe_Kas`,
                                                    SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                               FROM trs_buku_transaksi
                                              WHERE trs_buku_transaksi.`Tipe_Kas` = 'Kecil' AND 
                                                    trs_buku_transaksi.`jenis_trans` = 'Keluar'
                                              GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                                    trs_buku_transaksi.`Tipe_Kas`) AS keluar_kecil ON 
                                                    keluar_kecil.tanggal = mst_calendar.`list_date`
                                     LEFT JOIN 
                                            (SELECT trs_opname_kas.`TglOpname`,
                                                    trs_opname_kas.`TipeKas`,
                                                    (IFNULL(trs_opname_kas.`totaluangkertas`,0) + IFNULL(trs_opname_kas.`totaluanglogam`,0)) AS 'saldo'
                                             FROM trs_opname_kas
                                             WHERE trs_opname_kas.`TipeKas` ='Kecil') AS opnamekas ON 
                                             opnamekas.TglOpname = mst_calendar.`list_date`
                                     LEFT JOIN 
                                            (SELECT trs_buku_kasbon.`Cabang`,
                                                   SUM(trs_buku_kasbon.`ValueKasbon`) AS 'saldo'
                                              FROM trs_buku_kasbon
                                              WHERE trs_buku_kasbon.`Status` ='Open'
                                              GROUP BY trs_buku_kasbon.`Cabang`) AS kasbon ON kasbon.Cabang = mst_calendar.`Cabang`
                                     LEFT JOIN
                                            (SELECT trs_buku_transaksi.`Tanggal`,
                                                    trs_buku_transaksi.`Saldo_Akhir` 
                                              FROM trs_buku_transaksi JOIN mst_gl_bank ON 
                                                   trs_buku_transaksi.bank_trans = mst_gl_bank.`Nama Perkiraan`
                                              WHERE mst_gl_bank.`Bank Ref` = '2'
                                               ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS bank_kecil ON 
                                              bank_kecil.Tanggal <= mst_calendar.`list_date`
                                        WHERE mst_calendar.`list_date` = '".$tgl."' ;")->result();
        return $query;
    }
   public function getkasbankmasuk($cabang=null,$tgl=null)
    { 
        
        $query = $this->db->query("SELECT mst_calendar.`list_date` AS 'tanggal',
                                            mst_calendar.`Cabang`,
                                            saldo_kasbesar.saldokas AS 'saldo_kasbesar',
                                            saldo_bank.saldobank AS 'saldo_bank',
                                            (IFNULL(saldo_kasbesar.saldokas,0) + IFNULL(saldo_bank.saldobank,0)) AS 'kasmasuk'
                                     FROM   mst_calendar 
                                     LEFT JOIN
                                          (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                  SUM(case when trs_buku_transaksi.Transaksi = 'CN' then (trs_buku_transaksi.`Jumlah` * -1) else trs_buku_transaksi.`Jumlah` end) AS 'saldokas' 
                                           FROM trs_buku_transaksi
                                           WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar'  AND 
                                                 trs_buku_transaksi.Transaksi in ('Pelunasan Faktur  ( Piutang Dagang )','CN')
                                           GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS saldo_kasbesar ON 
                                                 saldo_kasbesar.tanggal = mst_calendar.`list_date`
                                     LEFT JOIN
                                          (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                  SUM(case when trs_buku_transaksi.Transaksi = 'CN' then (trs_buku_transaksi.`Jumlah` * -1) else trs_buku_transaksi.`Jumlah` end) AS 'saldobank' 
                                             FROM trs_buku_transaksi
                                            WHERE trs_buku_transaksi.`Buku` = 'Bank' AND trs_buku_transaksi.`Ket_2` != 'Giro' AND
                                                  trs_buku_transaksi.Transaksi in ('Pelunasan Faktur  ( Piutang Dagang )','CN')
                                            GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS saldo_bank ON 
                                                  saldo_bank.tanggal = mst_calendar.`list_date`     
                                    WHERE mst_calendar.`list_date` = '".$tgl."' AND 
                                          mst_calendar.`Cabang` = '".$cabang."';")->result();
        return $query;
    }
    public function getpelunasan($cabang=null,$tgl=null)
    { 
        $query = $this->db->query("SELECT DISTINCT trs_pelunasan_detail.`TglPelunasan`,
                                    IFNULL(tunai.saldo_tunai,0) AS 'saldo_tunai',
                                    IFNULL(transfer.saldo_transfer,0) AS 'saldo_transfer',
                                    IFNULL(giro.saldo_giro,0) AS 'saldo_giro'
                                FROM   trs_pelunasan_detail LEFT JOIN
                                ( SELECT trs_pelunasan_detail.`TglPelunasan`,
                                     SUM(CASE WHEN trs_pelunasan_detail.`TipeDokumen` IN ('Faktur','DN') THEN (IFNULL(trs_pelunasan_detail.`ValuePelunasan`,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) ELSE (IFNULL(trs_pelunasan_detail.`ValuePelunasan` * -1,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) END ) AS 'saldo_tunai' 
                                  FROM trs_pelunasan_detail
                                  WHERE trs_pelunasan_detail.`TipePelunasan` = 'Cash' and 
                                        trs_pelunasan_detail.Status !='Batal'
                                  GROUP BY trs_pelunasan_detail.`TglPelunasan` ) AS tunai ON 
                                  tunai.TglPelunasan = trs_pelunasan_detail.`TglPelunasan`
                                  LEFT JOIN
                                ( SELECT trs_pelunasan_detail.`TglPelunasan`,
                                     SUM(CASE WHEN trs_pelunasan_detail.`TipeDokumen` IN ('Faktur','DN') THEN (IFNULL(trs_pelunasan_detail.`ValuePelunasan`,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) ELSE (IFNULL(trs_pelunasan_detail.`ValuePelunasan` * -1,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) END ) AS 'saldo_transfer' 
                                  FROM trs_pelunasan_detail
                                  WHERE trs_pelunasan_detail.`TipePelunasan` = 'Transfer'
                                         and trs_pelunasan_detail.Status !='Batal'
                                  GROUP BY trs_pelunasan_detail.`TglPelunasan` ) AS transfer ON 
                                  transfer.TglPelunasan = trs_pelunasan_detail.`TglPelunasan`
                                  LEFT JOIN
                                ( SELECT trs_pelunasan_detail.`TglGiroCair`,
                                     SUM(CASE WHEN trs_pelunasan_detail.`TipeDokumen` IN ('Faktur','DN') THEN (IFNULL(trs_pelunasan_detail.`ValuePelunasan`,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) ELSE (IFNULL(trs_pelunasan_detail.`ValuePelunasan` * -1,0) +  IFNULL(trs_pelunasan_detail.`value_pembulatan`,0) + IFNULL(trs_pelunasan_detail.`value_transfer`,0) + IFNULL(trs_pelunasan_detail.`materai`,0)) END ) AS 'saldo_giro' 
                                  FROM trs_pelunasan_detail
                                  WHERE trs_pelunasan_detail.`TipePelunasan` = 'Giro'  and 
                                        trs_pelunasan_detail.Status not in ('Batal','GiroTolak')
                                  GROUP BY trs_pelunasan_detail.`TglGiroCair` ) AS giro ON 
                                  giro.TglGiroCair = trs_pelunasan_detail.`TglPelunasan`
                                WHERE trs_pelunasan_detail.`TglPelunasan` = '".$tgl."' and 
                                trs_pelunasan_detail.Cabang ='".$cabang."' ")->result();
        return $query;
    }
    public function getgirocair($cabang=null,$tgl=null)
    { 
        $query = $this->db->query("SELECT DISTINCT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                           trs_buku_transaksi.`Cabang`,
                                           SUM(CASE WHEN trs_buku_transaksi.Transaksi = 'Pencairan Giro' THEN IFNULL(trs_buku_transaksi.`Jumlah`,0) ELSE IFNULL(trs_buku_transaksi.`Jumlah` * -1,0) END ) AS 'saldogiro' 
                                    FROM   trs_buku_transaksi 
                                     WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
                                           trs_buku_transaksi.Transaksi IN ('Pencairan Giro') AND 
                                           DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
                                           trs_buku_transaksi.`Cabang` = '".$cabang."'
                                    GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                           trs_buku_transaksi.`Cabang`;")->result();
        return $query;
    }
    
    public function getgiro($cabang=null,$tgl=null)
    { 
        $query = $this->db->query("SELECT  trs_pelunasan_giro_detail.`Cabang`,
                                        trs_pelunasan_giro_detail.`TglPelunasan` AS 'tanggal',
                                           SUM(IFNULL(trs_pelunasan_giro_detail.`ValuePelunasan`,'')) AS 'ValueGiro'
                                    FROM trs_pelunasan_giro_detail
                                    WHERE trs_pelunasan_giro_detail.`Status` = 'Open' AND 
                                          trs_pelunasan_giro_detail.`Cabang` = '".$cabang."' and 
                                          trs_pelunasan_giro_detail.`TglPelunasan` ='".$tgl."'
                                    GROUP BY trs_pelunasan_giro_detail.`Cabang`,
                                        trs_pelunasan_giro_detail.`TglPelunasan`")->result();
        return $query;
    }
    
    public function getglgiro($cabang=null,$tgl=null)
    { 
        $query = $this->db->query("SELECT DISTINCT trs_buku_giro.`Tanggal` AS 'tanggal',
                                           trs_buku_giro.`Cabang`,
                                           SUM(ifnull(trs_buku_giro.`ValueGiro`,0)) AS 'ValueGiro' 
                                    FROM   trs_buku_giro 
                                    WHERE  trs_buku_giro.`Tanggal` = '".$tgl."' and 
                                          trs_buku_giro.`Cabang` = '".$cabang."' and 
                                          ifnull(trs_buku_giro.StatusGiro,'') = 'Terima'
                                      GROUP BY trs_buku_giro.`Tanggal`,
                                           trs_buku_giro.`Cabang`")->result();
        return $query;
    }

    public function getclosingkasbesar($cabang=null,$tgl=null)
    {   
        $transdate = date('Y-m-d', strtotime('-1 days'));
        // $query = $this->db->query("SELECT DISTINCT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Tipe_Kas`,
        //                                        IFNULL(sawal_besar.Saldo_Akhir,0) AS 'Saldo_awal',
        //                                        IFNULL(terima_besar.saldo,0) AS 'saldo_masuk',
        //                                        IFNULL(setoranbank.saldo,0) AS 'saldo_keluar',
        //                                        IFNULL(sakhir.Saldo_Akhir,0) AS 'saldo_akhir'
        //                                 FROM   trs_buku_transaksi 
        //                                 LEFT JOIN
        //                                 (SELECT trs_buku_transaksi.`Tanggal`,
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     trs_buku_transaksi.`Saldo_Akhir` 
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
        //                                       DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
        //                                 ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_besar ON 
        //                                           sawal_besar.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas`
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
        //                                         trs_buku_transaksi.`jenis_trans` = 'Masuk'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
        //                                     trs_buku_transaksi.`Tipe_Kas`) AS terima_besar ON 
        //                                           terima_besar.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas` AND 
        //                                           terima_besar.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     trs_buku_transaksi.`Tipe_Kas`,
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
        //                                         trs_buku_transaksi.Buku ='Kas' and 
        //                                         trs_buku_transaksi.Transaksi ='Setoran ke Bank Besar'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
        //                                     trs_buku_transaksi.`Tipe_Kas`) AS setoranbank ON 
        //                                           setoranbank.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas` AND 
        //                                           setoranbank.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN
        //                                 (SELECT trs_buku_transaksi.`Tanggal`,
        //                                    trs_buku_transaksi.`Tipe_Kas`,
        //                                     trs_buku_transaksi.`Saldo_Akhir` 
        //                                   FROM trs_buku_transaksi 
        //                                   WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
        //                                        DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                         trs_buku_transaksi.Buku ='Kas'
        //                                 ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sakhir ON 
        //                                           sakhir.Tipe_Kas = trs_buku_transaksi.`Tipe_Kas`
        //                                 WHERE DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                        trs_buku_transaksi.Buku ='Kas' and 
        //                                       trs_buku_transaksi.`Tipe_Kas` = 'Besar';")->result();
        $query = $this->db->query("SELECT DISTINCT mst_calendar.`list_date` AS 'tanggal',
                                           sawal_besar.Tipe_Kas,
                                           IFNULL(sawal_besar.Saldo_Akhir,0) AS 'Saldo_awal',
                                           IFNULL(terima_besar.saldo,0) AS 'saldo_masuk',
                                           IFNULL(setoranbank.saldo,0) AS 'saldo_keluar',
                                           IFNULL(sakhir.Saldo_Akhir,0) AS 'saldo_akhir'
                                    FROM   mst_calendar 
                                    LEFT JOIN
                                         (SELECT trs_buku_transaksi.`Cabang`,
                                                 trs_buku_transaksi.`Tanggal`,
                                                 trs_buku_transaksi.`Tipe_Kas`,
                                                 trs_buku_transaksi.`Saldo_Akhir` 
                                            FROM trs_buku_transaksi
                                            WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
                                                 DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
                                            ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_besar ON 
                                            sawal_besar.Cabang = mst_calendar.`Cabang`
                                    LEFT JOIN
                                          (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                  trs_buku_transaksi.`Tipe_Kas`,
                                                  SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                             FROM trs_buku_transaksi
                                            WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
                                                  trs_buku_transaksi.`jenis_trans` = 'Masuk'
                                             GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                                trs_buku_transaksi.`Tipe_Kas`) AS terima_besar ON 
                                             terima_besar.tanggal = mst_calendar.`list_date`
                                     LEFT JOIN
                                           (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                                   trs_buku_transaksi.`Tipe_Kas`,
                                                   SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                              FROM trs_buku_transaksi
                                              WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
                                                   trs_buku_transaksi.Buku ='Kas' AND 
                                                   trs_buku_transaksi.Transaksi ='Setoran ke Bank Besar'
                                              GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                                   trs_buku_transaksi.`Tipe_Kas`) AS setoranbank ON 
                                                   setoranbank.tanggal = mst_calendar.`list_date`
                                      LEFT JOIN
                                              (SELECT trs_buku_transaksi.`Cabang`,
                                              trs_buku_transaksi.`Tanggal`,
                                                      trs_buku_transaksi.`Tipe_Kas`,
                                                       trs_buku_transaksi.`Saldo_Akhir` 
                                                 FROM trs_buku_transaksi 
                                                WHERE trs_buku_transaksi.`Tipe_Kas` = 'Besar' AND 
                                                      DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
                                                      trs_buku_transaksi.Buku ='Kas'
                                                ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sakhir ON 
                                               sakhir.Cabang = mst_calendar.`Cabang`
                                    WHERE mst_calendar.`list_date` = '".$tgl."' ;")->result();
        return $query;
    }

    public function getclosingbank($cabang=null,$tgl=null)
    {   
        $transdate = date('Y-m-d', strtotime('-1 days'));
        // $query = $this->db->query("SELECT DISTINCT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Buku`,
        //                                        IFNULL(sawal_bca.saldo,0) AS 'Saldo_awal',
        //                                        IFNULL(setorankas.saldo,0) AS 'setorankas',
        //                                        IFNULL(kliringgiro.saldo,0) AS 'kliringgiro',
        //                                        IFNULL(ats.saldo,0) AS 'ats',
        //                                        IFNULL(sakhir.Saldo_Akhir,0) AS 'Saldo_akhir'
        //                                 FROM   trs_buku_transaksi 
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Buku`as 'Buku',
        //                                        trs_buku_transaksi.`Saldo_Akhir` AS 'saldo'
        //                                   FROM trs_buku_transaksi 
        //                                  WHERE trs_buku_transaksi.`Buku` ='Bank' AND 
        //                                        trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811' AND 
        //                                        DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
        //                                  ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_bca ON 
        //                                           sawal_bca.`Buku` = trs_buku_transaksi.`Buku`
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
        //                                         trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811' AND  
        //                                         trs_buku_transaksi.Transaksi ='Setoran ke Bank Besar'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS setorankas ON 
        //                                           setorankas.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Buku`,
        //                                        SUM(CASE WHEN trs_buku_transaksi.Transaksi = 'Pencairan Giro' THEN IFNULL(trs_buku_transaksi.`Jumlah`,0) WHEN  trs_buku_transaksi.Transaksi = 'Tolakan Giro' THEN IFNULL(trs_buku_transaksi.`Jumlah` * -1,0) END ) AS 'saldo' 
        //                                     FROM   trs_buku_transaksi 
        //                                      WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
        //                                            trs_buku_transaksi.Transaksi IN ('Pencairan Giro','Tolakan Giro') AND 
        //                                            DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                            trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811' 
        //                                     GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
        //                                            trs_buku_transaksi.`Cabang`) AS kliringgiro ON 
        //                                           kliringgiro.Buku = trs_buku_transaksi.`Buku` AND 
        //                                           kliringgiro.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                 LEFT JOIN 
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                     SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
        //                                   FROM trs_buku_transaksi
        //                                   WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
        //                                         trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811' AND  
        //                                         trs_buku_transaksi.Transaksi ='Bayar ke pusat ( auto transfer )'
        //                                   GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS ats ON 
        //                                           setorankas.tanggal = DATE(trs_buku_transaksi.`Tanggal`)
        //                                  LEFT JOIN
        //                                 (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
        //                                        trs_buku_transaksi.`Buku`, 
        //                                     trs_buku_transaksi.`Saldo_Akhir` 
        //                                   FROM trs_buku_transaksi 
        //                                   WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
        //                                         DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                         trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811'
        //                                 ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sakhir ON 
        //                                           sakhir.Buku = trs_buku_transaksi.`Buku`
        //                                 WHERE DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' AND 
        //                                       trs_buku_transaksi.`Buku` = 'Bank' and 
        //                                       trs_buku_transaksi.`bank_trans` = 'Bank BCA 1573788811';")->result();
        $query = $this->db->query("SELECT DISTINCT mst_calendar.`list_date` AS 'tanggal',
                                       sawal_bca.`Buku`,
                                       IFNULL(sawal_bca.saldo,0) AS 'Saldo_awal',
                                       IFNULL(setorankas.saldo,0) AS 'setorankas',
                                       IFNULL(kliringgiro.saldo,0) AS 'kliringgiro',
                                       IFNULL(ats.saldo,0) AS 'ats',
                                       IFNULL(sakhir.Saldo_Akhir,0) AS 'Saldo_akhir'
                                 FROM   mst_calendar 
                                 LEFT JOIN
                                       (SELECT trs_buku_transaksi.`Cabang`,
                                               DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                               trs_buku_transaksi.`Buku`AS 'Buku',
                                               trs_buku_transaksi.`Saldo_Akhir` AS 'saldo'
                                          FROM trs_buku_transaksi JOIN 
                                          (SELECT Cabang,`Nama Perkiraan` AS 'bank' 
                                           FROM mst_gl_bank WHERE mst_gl_bank.`Bank Ref` = '1' ) AS gl_bank
                                               ON  trs_buku_transaksi.`Cabang` = gl_bank.Cabang 
                                         WHERE trs_buku_transaksi.`Buku` ='Bank' AND 
                                               trs_buku_transaksi.`bank_trans` = gl_bank.bank AND
                                               DATE(trs_buku_transaksi.`Tanggal`) < '".$tgl."'
                                        ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sawal_bca ON 
                                        sawal_bca.`Cabang` = mst_calendar.`Cabang`
                                 LEFT JOIN
                                      (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                              SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                        FROM trs_buku_transaksi JOIN (SELECT Cabang,`Nama Perkiraan` AS 'bank' FROM mst_gl_bank WHERE mst_gl_bank.`Bank Ref` = '1' ) AS  gl_bank
                                               ON  trs_buku_transaksi.`Cabang` = gl_bank.`Cabang`
                                       WHERE trs_buku_transaksi.`Buku` = 'Bank' AND  
                                              trs_buku_transaksi.`bank_trans` = gl_bank.bank AND 
                                             trs_buku_transaksi.Transaksi ='Setoran ke Bank Besar'
                                       GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS setorankas ON 
                                             setorankas.tanggal = mst_calendar.`list_date`
                                LEFT JOIN
                                     (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                             trs_buku_transaksi.`Buku`,
                                             SUM(CASE WHEN trs_buku_transaksi.Transaksi = 'Pencairan Giro' THEN IFNULL(trs_buku_transaksi.`Jumlah`,0) WHEN  trs_buku_transaksi.Transaksi = 'Tolakan Giro' THEN IFNULL(trs_buku_transaksi.`Jumlah` * -1,0) END ) AS 'saldo' 
                                      FROM   trs_buku_transaksi JOIN (SELECT Cabang,`Nama Perkiraan` AS 'bank' FROM mst_gl_bank WHERE mst_gl_bank.`Bank Ref` = '1' ) AS  gl_bank
                                               ON  trs_buku_transaksi.`Cabang` = gl_bank.`Cabang`
                                      WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
                                            trs_buku_transaksi.Transaksi IN ('Pencairan Giro','Tolakan Giro') AND 
                                            trs_buku_transaksi.`bank_trans` = gl_bank.bank AND
                                            DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."'
                                       GROUP BY DATE(trs_buku_transaksi.`Tanggal`),
                                             trs_buku_transaksi.`Cabang`) AS kliringgiro ON 
                                             kliringgiro.tanggal = mst_calendar.`list_date`
                                LEFT JOIN 
                                     (SELECT DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                            SUM(trs_buku_transaksi.`Jumlah`) AS 'saldo'
                                       FROM trs_buku_transaksi JOIN (SELECT Cabang,`Nama Perkiraan` AS 'bank' FROM mst_gl_bank WHERE mst_gl_bank.`Bank Ref` = '1' ) AS  gl_bank
                                               ON  trs_buku_transaksi.`Cabang` = gl_bank.`Cabang`
                                      WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
                                            trs_buku_transaksi.`bank_trans` = gl_bank.bank AND
                                            trs_buku_transaksi.Transaksi ='Bayar ke pusat ( auto transfer )'
                                     GROUP BY DATE(trs_buku_transaksi.`Tanggal`)) AS ats ON 
                                            ats.tanggal = mst_calendar.`list_date`
                                 LEFT JOIN
                                     (SELECT trs_buku_transaksi.`Cabang`,  
                                        DATE(trs_buku_transaksi.`Tanggal`) AS 'tanggal',
                                            trs_buku_transaksi.`Buku`, 
                                            trs_buku_transaksi.`Saldo_Akhir` 
                                       FROM trs_buku_transaksi JOIN (SELECT Cabang,`Nama Perkiraan` AS 'bank' FROM mst_gl_bank WHERE mst_gl_bank.`Bank Ref` = '1' ) AS  gl_bank
                                               ON  trs_buku_transaksi.`Cabang` = gl_bank.`Cabang`
                                      WHERE trs_buku_transaksi.`Buku` = 'Bank' AND 
                                            trs_buku_transaksi.`bank_trans` = gl_bank.bank AND
                                            DATE(trs_buku_transaksi.`Tanggal`) = '".$tgl."' 
                                      ORDER BY trs_buku_transaksi.`Jurnal_ID` DESC LIMIT 1) AS sakhir ON 
                                            sakhir.Cabang = mst_calendar.`Cabang`
                                 WHERE mst_calendar.`list_date` = '".$tgl."';")->result();
        return $query;
    }

    public function listdetailOpnameKaskecil($tgl=null)
    {   
        $query = $this->db->query("select * from trs_opname_kas where TglOpname = '".$tgl."' and Cabang = '".$this->cabang."' and Tipekas = 'Kecil'")->result();
        return $query;
    }
    public function listdetailOpnameKasbesar($tgl=null)
    {   
        $query = $this->db->query("select * from trs_opname_kas where TglOpname = '".$tgl."' and Cabang = '".$this->cabang."' and Tipekas = 'Besar'")->result();
        return $query;
    }
    public function getSaldoAwalOpnameKas($tipekas=null,$tgl=null)
    {
        
        $query = $this->db->query("select ifnull(Saldo_Akhir,0) as saldo from trs_buku_transaksi where Kategori = 'Kas' and Tipe_Kas = '".$tipekas."' and date(Tanggal)='".$tgl."' order by Jurnal_ID desc limit 1")->row();
        return $query;
    }
    public function listDataBukuTitipan()
    {   
        $query = $this->db->query("select * from trs_buku_titipan where Cabang = '".$this->cabang."' AND Status NOT LIKE '%Batal%' order by NoTitipan ASC")->result();
        return $query;
    }

    public function listDataTransaksiKasBank($Search = NULL,$periode=null, $limit=null)
    {   
        $query = $this->db->query("select * from trs_buku_transaksi where  Cabang = '".$this->cabang."' $Search $periode order by date(Tanggal),No_Voucher,Jurnal_ID ASC $limit")->result();
            
        return $query;
    }
    public function getSaldoAwalMutasiBank($bank=null,$tgl=null)
    {
        
        $query = $this->db->query("select ifnull(Saldo_Akhir,0) as saldo from trs_buku_transaksi where Kategori = 'Bank' and bank_trans = '".$bank."' and date(Tanggal) < '".$tgl."' order by Jurnal_ID desc limit 1")->row();
        return $query;
    }
    public function getSaldoAwalMutasiKas($tipekas=null,$tgl=null)
    {     
        $query = $this->db->query("select ifnull(Saldo_Akhir,0) as saldo from trs_buku_transaksi where Kategori = 'Kas' and Tipe_Kas = '".$tipekas."' and date(Tanggal) < '".$tgl."' order by Jurnal_ID desc limit 1")->row();
        return $query;
    }
    public function getdatamutasikas($bydate,$tipekas=null)
    {     
        $query = $this->db->query("SELECT DATE(a.`Tanggal`) AS 'Tanggal',
                                     a.`No_Voucher`,
                                     a.`Jurnal_ID`,
                                     a.`Kategori`,
                                     a.`Tipe_Kas`,
                                     a.`Transaksi`,
                                     a.`Keterangan`,
                                     IFNULL(a.`Debit`,0) AS 'Debet',
                                     0 AS 'Kredit'
                                FROM trs_buku_transaksi a
                                WHERE IFNULL(a.`jenis_trans`,'') = 'Masuk' AND 
                                     DATE(a.`Tanggal`) $bydate AND 
                                     a.`Kategori` ='Kas' AND a.`Tipe_Kas` ='".$tipekas."'
                                UNION ALL
                                SELECT DATE(b.`Tanggal`) AS 'Tanggal',
                                     b.`No_Voucher`,
                                     b.`Jurnal_ID`,
                                     b.`Kategori`,
                                     b.`Tipe_Kas`,
                                     b.`Transaksi`,
                                     b.`Keterangan`,
                                     0 AS 'Debet',
                                     IFNULL(b.`Kredit`,0) AS 'Kredit'
                                FROM trs_buku_transaksi b
                                WHERE IFNULL(b.`jenis_trans`,'') = 'Keluar' AND 
                                     DATE(b.`Tanggal`) $bydate AND 
                                     b.`Kategori` ='Kas' AND b.`Tipe_Kas` ='".$tipekas."'
                                ORDER BY Tanggal,Jurnal_ID;")->result();
        return $query;
    }
    public function getdatamutasibank($bydate,$bank=null)
    {     
        $query = $this->db->query("SELECT DATE(a.`Tanggal`) AS 'Tanggal',
                                     a.`No_Voucher`,
                                     a.`Jurnal_ID`,
                                     a.`Kategori`,
                                     a.`Keterangan`,
                                     a.`Transaksi`,
                                     IFNULL(a.`Debit`,0) AS 'Debet',
                                     0 AS 'Kredit'
                                FROM trs_buku_transaksi a
                                WHERE IFNULL(a.`jenis_trans`,'') = 'Masuk' AND 
                                     DATE(a.`Tanggal`) $bydate AND 
                                     a.`Kategori` ='Bank' AND a.`bank_trans` ='".$bank."'
                                UNION ALL
                                SELECT DATE(b.`Tanggal`) AS 'Tanggal',
                                     b.`No_Voucher`,
                                     b.`Jurnal_ID`,
                                     b.`Kategori`,
                                     b.`Keterangan`,
                                     b.`Transaksi`,
                                     0 AS 'Debet',
                                     IFNULL(b.`Kredit`,0) AS 'Kredit'
                                FROM trs_buku_transaksi b
                                WHERE IFNULL(b.`jenis_trans`,'') = 'Keluar' AND 
                                     DATE(b.`Tanggal`) $bydate AND 
                                     b.`Kategori` ='Bank' AND b.`bank_trans` ='".$bank."'
                                ORDER BY Tanggal,Jurnal_ID;")->result();
        return $query;
    }
    public function listGLMutasiBank()
    {   
        $query = $this->db->query("select `Nama Perkiraan` as Bank,Buku from mst_gl_bank where Cabang = '".$this->cabang."'")->result();

        return $query;
    }
}