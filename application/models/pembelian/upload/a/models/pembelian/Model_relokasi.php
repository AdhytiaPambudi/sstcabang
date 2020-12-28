<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_relokasi extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function getDetailUsulanRelokasi($kode = null)
    {     
        $hpc = $this->db->query("select UnitCOGS as cogs from trs_invsum where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' limit 1")->row();

        $harga = $this->db->query("select Hrg_Beli_Cab as harga, Dsc_Beli_Cab as diskon from mst_harga where Produk = '".$kode."' and Cabang = '".$this->cabang."' limit 1")->row();

        $data['hpc'] = (!empty($hpc->cogs) ? $hpc->cogs : 0);
        $data['harga'] = (!empty($harga->harga) ? $harga->harga : 0);
        $data['diskon'] = (!empty($harga->diskon) ? $harga->diskon : 0);

        return $data;
    }

 public function saveData($params = null,$name1 = null)
    {
        $valid = false;
        $no = "";
        $no = $this->Model_main->noDokumen('Relokasi Cabang');
        $totalgross = 0;
        $totalharga = 0;
        $totaldisc = 0;
        $totalpotongan = 0;
        $totalbonus = 0;
        $totalall = 0;
        $disc = 0;
        $values = 0;
        $ppn = 0;
        foreach ($params->kode_produk as $key => $value){
            $totalgross += (float)$this->format->number_unformat($params->Gross[$key]);
            $totalharga += (float)$this->format->number_unformat($params->Harga[$key]);
            $totaldisc += (float)$this->format->number_unformat($params->Disc[$key]);
            $totalpotongan += (float)$this->format->number_unformat($params->T_potongan[$key]);
            $disc += (float)$this->format->number_unformat($params->Disc[$key]);
            $values += (float)$this->format->number_unformat($params->Value[$key]);
            $ppn += (float)$this->format->number_unformat($params->PPN[$key]);
            $totalbonus += (float)$this->format->number_unformat($params->Bonus[$key]);
            $totalall += (float)$this->format->number_unformat($params->Total[$key]);
        }

        // try{
            //================ Running Number ======================================//
            $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
            $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
            $kodeDokumen = $this->Model_main->kodeDokumen('Relokasi Cabang');
            $data = $this->db->query("select max(right(No_Relokasi,7)) as 'no' from trs_relokasi_kirim_header where Cabang = '".$this->cabang."'")->result();
            if(empty($data[0]->no)){
                $lastNumber = 1000001;
            }else {
                $lastNumber = $data[0]->no + 1;
            }
            $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
            //================= end of running number ========================================//
            // $this->db->trans_begin();
            $this->db->set("Cabang", $params->cabang1);
            $this->db->set("No_Relokasi", $noDokumen);
            $this->db->set("Kategori", "Rutin");
            $this->db->set("Cabang_Pengirim", $params->cabang1);
            $this->db->set("Cabang_Penerima", $params->cabang2);
            $this->db->set("Prinsipal", $params->Nama_Prinsipal[$key]);
            $this->db->set("Tgl_kirim", $params->tgl_kirim);
            $this->db->set("Status_kiriman", "Open");
            $this->db->set("Dokumen", $name1);
            // $this->db->set("Dokumen_2", $name2);
            $this->db->set("Gross", $totalgross);
            $this->db->set("Potongan", $totalpotongan);
            $this->db->set("Disc", $totaldisc);
            $this->db->set("Value", $values);
            $this->db->set("Ppn", ($values*10)/100);
            $this->db->set("Total", $totalall);
            $this->db->set("Biayakirim", $params->biayakirim);
            $this->db->set("ValueCR", $params->valCR);
            $this->db->set("Keterangan", $params->keterangan);
            $this->db->set("added_user", $this->user);
            $this->db->insert("trs_relokasi_kirim_header");
            $i=0;
            foreach ($params->kode_produk as $key => $value) 
            {
                $i++;
                $batchNo = explode("~", $params->batch[$key]);
                $batchNo = $batchNo[0];
                $this->db->set("Cabang", $params->cabang1);
                $this->db->set("No_Relokasi", $noDokumen);
                $this->db->set("Kategori", "Rutin");
                $this->db->set("Cabang_Pengirim", $params->cabang1);
                $this->db->set("Cabang_Penerima", $params->cabang2);
                $this->db->set("Prinsipal", $params->Nama_Prinsipal[$key]);
                $this->db->set("Supplier", $params->Supplier[$key]);
                $this->db->set("Tgl_kirim", $params->tgl_kirim);
                $this->db->set("Status", "Open");
                $this->db->set("noline", $i);
                $this->db->set("Produk", $params->kode_produk[$key]);
                $this->db->set("NamaProduk", $params->namaproduk[$key]);
                $this->db->set("Satuan", $params->Satuan[$key]);
                $this->db->set("Qty", $params->Qty[$key]);
                $this->db->set("Bonus", $this->format->number_unformat($params->Bonus[$key]));
                $this->db->set("Disc", $this->format->number_unformat($params->Disc[$key]));
                $this->db->set("Harga", $this->format->number_unformat($params->Harga[$key]));
                $this->db->set("Gross", $this->format->number_unformat($params->Gross[$key]));
                $this->db->set("Potongan", $this->format->number_unformat($params->T_potongan[$key]));
                $this->db->set("Value", $this->format->number_unformat($params->Value[$key]));
                $this->db->set("PPN", $this->format->number_unformat($params->PPN[$key]));
                $this->db->set("Total", $this->format->number_unformat($params->Total[$key]));
                $this->db->set("BatchNo", $batchNo);
                $this->db->set("ExpDate", $params->ExpDate[$key]);
                if(empty($params->BatchDoc[$key]) or $params->BatchDoc[$key] == ""){
                    $batchDoc = 'xxx';
                }else{
                    $batchDoc = $params->BatchDoc[$key];
                }
                $this->db->set("BatchDoc", $params->BatchDoc[$key]);
                $this->db->set("Added_User", $this->user);

                $this->db->insert("trs_relokasi_kirim_detail");
            }
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Relokasi Cabang' and Cabang = '".$this->cabang."'");
            $this->setStokCabangOut($noDokumen,'Baik');
            $this->setStokCabangIn($noDokumen,'Relokasi');
            
            // if ($this->db->trans_status() === FALSE)
            // {
            //         $this->db->trans_rollback();
            // }
            // else
            // {
            //         $this->db->trans_commit();
            // }

            return $no;
        // }
        // catch (Exception $e) {
        //   $this->db->trans_rollback();
        //   log_message('error', sprintf('%s : %s : DB transaction failed. Error no: %s, Error msg:%s, Last query: %s', __CLASS__, __FUNCTION__, $e->getCode(), $e->getMessage(), print_r($this->main_db->last_query(), TRUE)));
        // } 
    }

    public function prosesData($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_relokasi_usulan_header set statusPusat = 'Berhasil' where No_Usulan = '".$no."'");

            $query = $this->db->query("select * from trs_relokasi_usulan_detail where NoUsulan = '".$no."'")->result();
            $cek = $this->db2->query("select * from trs_relokasi_usulan_detail where NoUsulan = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_relokasi_usulan_detail', $r); // insert each row to another table
                }
            }
            else{
                foreach($query as $r) { // loop over results
                    $this->db2->where('Produk', $r->Produk);
                    $this->db2->where('NoUsulan', $no);
                    $this->db2->update('trs_relokasi_usulan_detail', $r);
                }
            }

            $query2 = $this->db->query("select * from trs_relokasi_usulan_header where No_Usulan = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_relokasi_usulan_header where No_Usulan = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_relokasi_usulan_header', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('No_Usulan', $no);
                $this->db2->update('trs_relokasi_usulan_header', $query2);
            }

            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_relokasi_usulan_header set statusPusat = 'Gagal' where No_Usulan = '".$no."'");
            return FALSE;
        }
    }
   


    // USULAN RELOKASI APPROVAL PUSAT
    public function cabangRelokasi()
    {   
        $query = $this->db->query("select No_Usulan, Cabang_Usulan from trs_relokasi_usulan_header where Cabang_Pengirim = '".$this->cabang."' and Status_Usulan = 'Approv Pst' order by Cabang_Pengirim, No_Usulan asc")->result();

        return $query;
    }

    public function produkRelokasi($no = null)
    {   
        $query = $this->db->query("select Produk, NamaProduk, Qty from trs_relokasi_usulan_detail where NoUsulan = '".$no."'")->result();

        return $query;
    }

    public function getDetailProdukRelokasi($no = null, $kode = null)
    {   
        $query = $this->db->query("select Qty, HPC from trs_relokasi_usulan_detail where NoUsulan = '".$no."' and Produk = '".$kode."'")->result();

        return $query;
    }

    public function load_datarelokasikiriman($search=null, $limit=null, $status=null){
        $header = $this->db->query("select * from trs_relokasi_kirim_header where Cabang='".$this->cabang."' $search order by Tgl_kirim DESC, No_Relokasi ASC $limit");
        return $header;
    }

    public function listDataTerimaRelokasi()
    {   
        $query = $this->db->query("select * from trs_relokasi_terima_header order by Tgl_terima DESC, No_Terima ASC")->result();

        return $query;
    }

    public function updateDataRelokasiDOPusat()
    {   
        $cabang = $this->cabang;
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
                $data = $this->db2->query("select * from trs_delivery_order_header where Cabang_penerima = '".$this->cabang."' and Status = 'Open' and Tipe='Relokasi'")->result();
                $NoDO = (!empty($data[0]->NoDokumen) ? $data[0]->NoDokumen : "");
                foreach ($data as $dt) {
                    // $no = "";
                    // $no = $this->Model_main->noDokumen('Relokasi Cabang');
                   

                    $query = $this->db2->query("select * from trs_delivery_order_detail where NoDokumen = '".$dt->NoDokumen."'")->result();
                    $i = 0 ;
                    // $cek = $this->db->query("select * from trs_relokasi_terima_header where No_DO = '".$dt->NoDokumen."'")->num_rows();
                    $cek = $this->db->query("select * from trs_relokasi_terima_header where No_Relokasi = '".$dt->No_Relokasi."'")->num_rows();
                    if ($cek == 0) {
                        //================ Running Number ======================================//
                        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                        $kodeDokumen = $this->Model_main->kodeDokumen('Terima Relokasi');
                        $data = $this->db->query("select max(right(No_Terima,7)) as 'no' from trs_relokasi_terima_header where Cabang = '".$this->cabang."'")->result();
                        if(empty($data[0]->no)){
                          $lastNumber = 1000001;
                        }else {
                          $lastNumber = $data[0]->no + 1;
                        }
                        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                        //================= end of running number ========================================//
                        //
                        $cabang_pengirim = (!empty($dt->Cabang_pengirim) ? $dt->Cabang_pengirim : "");
                        $cabang_penerima = (!empty($dt->Cabang_penerima) ? $dt->Cabang_penerima : "");
                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("Cabang_Penerima", $cabang_penerima);
                        $this->db->set("Cabang_Pengirim", $cabang_pengirim);
                        $this->db->set("No_Terima", $noDokumen);
                        $this->db->set("Tgl_terima", date('Y-m-d'));
                        $this->db->set("Time_terima", date('Y-m-d h:m:s'));
                        $this->db->set("Kategori", 'Rutin');
                        $this->db->set("No_DO", $dt->NoDokumen);
                        $this->db->set("Status_kiriman", 'Pending');
                        $this->db->set("No_Relokasi", $dt->No_Relokasi);
                        $this->db->set("Tgl_kirim", $dt->TglDokumen);
                        $this->db->set("Time_kirim", $dt->TimeDokumen);
                        $this->db->set("Gross", $dt->Gross);
                        $this->db->set("Value", $dt->Value);
                        $this->db->set("Potongan", $dt->Potongan);
                        $this->db->set("Disc", $dt->Disc);
                        $this->db->set("Ppn", $dt->PPN);
                        $this->db->set("Total", $dt->Total);
                        $this->db->set("Added_Time", date('Y-m-d h:m:s'));
                        $this->db->set("Added_User", $this->user);
                        $valid =  $this->db->insert("trs_relokasi_terima_header");
                        foreach($query as $r) { 
                            $cek = $this->db->query("select * from trs_relokasi_terima_detail where No_Relokasi = '".$r->No_Relokasi."' and Produk = '".$r->Produk."' and BatchNo = '".$r->BatchNo."' and noline='".$r->noline."'")->num_rows();
                                if ($cek == 0) {
                                    $i++;
                                    $this->db->set("Cabang", $this->cabang);
                                    $this->db->set("Cabang_Penerima", $cabang_penerima);
                                    $this->db->set("Cabang_Pengirim", $cabang_pengirim);
                                    $this->db->set("No_Terima", $noDokumen);
                                    $this->db->set("Tgl_terima", date('Y-m-d'));
                                    $this->db->set("Time_terima", date('Y-m-d h:m:s'));
                                    $this->db->set("Kategori", 'Rutin');
                                    $this->db->set("noline", $i);
                                    $this->db->set("Produk", $r->Produk);
                                    $this->db->set("NamaProduk", $r->NamaProduk);
                                    $this->db->set("Satuan", $r->Satuan);
                                    $this->db->set("Keterangan", $r->Keterangan);
                                    $this->db->set("Penjelasan", $r->Penjelasan);
                                    $this->db->set("No_Relokasi", $r->No_Relokasi);
                                    $this->db->set("Tgl_kirim", $r->TglDokumen);
                                    $this->db->set("Time_kirim", $r->TimeDokumen);
                                    $this->db->set("Status", 'Pending');
                                    $this->db->set("Qty_kirim", $r->Qty+$r->Bonus);
                                    $this->db->set("Qty_terima", $r->Qty+$r->Bonus);
                                    $this->db->set("Qty", $r->Qty);
                                    $this->db->set("Bonus", $r->Bonus);
                                    $this->db->set("Harga", $r->HrgBeli);
                                    $this->db->set("Gross", $r->Gross);
                                    $this->db->set("Value", $r->Value);
                                    $this->db->set("Disc", $r->Disc);
                                    $this->db->set("Potongan", $r->Potongan);
                                    $this->db->set("Ppn", $r->PPN);
                                    $this->db->set("Total", $r->Total);
                                    $this->db->set("HPC", $r->HPC);
                                    $this->db->set("BatchNo", $r->BatchNo);
                                    $this->db->set("ExpDate", $r->ExpDate);
                                    $this->db->set("BatchDoc", $r->NoDokumen);
                                    $this->db->set("Added_Time", date('Y-m-d h:m:s'));
                                    $this->db->set("Added_User", $this->user);
                                    $valid =  $this->db->insert("trs_relokasi_terima_detail");
                                }
                                else{
                                    $this->db->set("Cabang", $this->cabang);
                                    $this->db->set("Cabang_Penerima", $cabang_penerima);
                                    $this->db->set("Cabang_Pengirim", $cabang_pengirim);
                                    $this->db->set("Kategori", 'Rutin');
                                    $this->db->set("Produk", $r->Produk);
                                    $this->db->set("NamaProduk", $r->NamaProduk);
                                    $this->db->set("Satuan", $r->Satuan);
                                    $this->db->set("Keterangan", $r->Keterangan);
                                    $this->db->set("Penjelasan", $r->Penjelasan);
                                    $this->db->set("No_Relokasi", $r->No_Relokasi);
                                    $this->db->set("Tgl_kirim", $r->TglDokumen);
                                    $this->db->set("Time_kirim", $r->TimeDokumen);
                                    $this->db->set("Status", 'Pending');
                                    $this->db->set("Qty_kirim", $r->Qty+$r->Bonus);
                                    $this->db->set("Qty_terima", $r->Qty+$r->Bonus);
                                    $this->db->set("Qty", $r->Qty);
                                    $this->db->set("Bonus", $r->Bonus);
                                    $this->db->set("Harga", $r->HrgBeli);
                                    $this->db->set("Gross", $r->Gross);
                                    $this->db->set("Value", $r->Value);
                                    $this->db->set("Ppn", $r->PPN);
                                    $this->db->set("Potongan", $r->Potongan);
                                    $this->db->set("Disc", $r->Disc);
                                    $this->db->set("Total", $r->Total);
                                    $this->db->set("HPC", $r->HPC);
                                    $this->db->set("BatchNo", $r->BatchNo);
                                    $this->db->set("ExpDate", $r->ExpDate);
                                    $this->db->set("BatchDoc", $r->NoDokumen);
                                    $this->db->set("Added_Time", date('Y-m-d h:m:s'));
                                    $this->db->set("Added_User", $this->user);
                                    $this->db->where('Produk', $r->Produk);
                                    $this->db->where('No_Relokasi', $r->No_Relokasi);
                                    $valid =  $this->db->update("trs_relokasi_terima_detail");
                                }
                        } 
                        if($valid){
                            $this->db2->query("update trs_delivery_order_header set Status = 'Closed' where NoDokumen = '".$dt->NoDokumen."' and Cabang = '".$cabang_pengirim."'");
                            $this->db2->query("update trs_delivery_order_detail set Status = 'Closed' where NoDokumen = '".$dt->NoDokumen."' and Cabang = '".$cabang_pengirim."'");
                            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Relokasi Cabang' and Cabang = '".$cabang_pengirim."'");
                            // $this->setStokOut($NoDO,'Git');
                            // $this->setStokIn($noDokumen,'Relokasi');
                            
                        }
                            
                    }
                    // else{
                    //     // $this->db->where('No_Usulan', $dt->No_Usulan);
                    //     // $this->db->update('trs_relokasi_usulan_header', $dt);
                    // }    
                }
               
                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    //==== nambah  stok ke gudang relokasi cabang =======////

    public function setStokIn($No = NULL, $gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$No."' where Status ='terima' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                foreach ($produk as $key => $value) {
                    $product2 = $produk[$key]->Produk;
                    if ($product1 == $product2) {
                        $summary = $summary + $produk[$key]->Qty+$produk[$key]->Bonus;
                        $valuestok = $produk[$key]->Gross;
                    }
                }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");  
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
               $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty+$produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$kunci]->Cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk;  
            if (!empty($kodeproduk)) {
                $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                    $UnitStok = $produk[$key]->Qty+$produk[$key]->Bonus;
                    $valuestok = $UnitStok * $produk[$key]->Value;  
                    $unitcogs = $valuestok / $UnitStok;
                    // log_message("error",print_r($unitcogs,true));
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $produk[$key]->Cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    // $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
                
                 $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                 $invdet = $this->db->query("select KodeProduk,sum(ValueStok) as 'valuestok' from trs_invdet where KodeProduk ='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' and UnitStok != 0 group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$produk[$key]->Cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $ValueStok = $invdet->valuestok;
                    $unitcogs = $ValueStok / $invsum->UnitStok;
                    $this->db->query("update trs_invsum set ValueStok = ".$valuestok.", UnitCOGS = '".$unitcogs."' where KodeProduk='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }        
            }
        }

    }

    //==== Edit  stok ke gudang relokasi cabang =======////

    public function setBackStokIn($No = NULL, $gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                foreach ($produk as $key => $value) {
                    $product2 = $produk[$key]->Produk;
                    if ($product1 == $product2) {
                        $summary = $summary + $produk[$key]->Qty_terima + $produk[$key]->Bonus;
                        $valuestok = $produk[$key]->Gross;
                    }
                }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");  
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
               $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty_terima + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$kunci]->Cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk;  
            if (!empty($kodeproduk)) {
                $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$kodeproduk."' and Cabang='".$this->cabang."' and BatchNo='".$produk[$key]->BatchNo."' and NoDokumen='".$No."' and Gudang = '".$gudang."' and Tahun = '".date('Y')."' limit 1"); 
                if($invdet->num_rows() > 0){
                    $det = $invdet->row();
                    $unitcogs = $det->UnitCOGS;
                    $qtystok = $produk[$key]->Qty_terima + $produk[$key]->Bonus;
                    $valuestok = $qtystok * $unitcogs;    
                    
                    $this->db->set("UnitStok", $qtystok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $produk[$key]->Cabang);
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $valid = $this->db->update('trs_invdet');
                }
                else{
                    $unitcogs = $produk[$key]->Harga;
                    $UnitStok = $produk[$key]->Qty_terima + $produk[$key]->Bonus;
                    $valuestok = $UnitStok * $unitcogs;    
                    
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $produk[$key]->Cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", $No);
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
                } 
                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $this->db->set("ValueStok", $invdet->sumval);
                    $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }
                    
            }
        }

    }

    //==== kurangi  stok ke gudang relokasi pusat =======////
     public function setStokOut($No = NULL, $gudang = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $produk = $this->db2->query("select * from trs_delivery_order_detail where NoDokumen = '".$No."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->HrgBeli;
                if (!empty($product1)) {
                   $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                    foreach ($produk as $key => $value) {
                        $product2 = $produk[$key]->Produk;
                        if ($product1 == $product2) {
                            $summary = $summary + $produk[$key]->Qty+$produk[$key]->Bonus;
                            $valuestok = $produk[$key]->Gross;
                        }
                    }
                    $invsum = $this->db2->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."'  limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db2->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                       $this->db2->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;      
                if (!empty($kodeproduk)) {
                   $prod2 = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty+$produk[$key]->Bonus;

                     $this->db2->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', 'Pusat','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk;  
                if (!empty($kodeproduk)) {
                      $det = $this->db2->query("select * from trs_invdet where cabang = 'Pusat' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen='".$No."' limit 1")->row(); 
                        $unitcogs = $det->UnitCOGS;    
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty+$produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs;
                        $this->db2->set("UnitStok", $qtystok);
                        $this->db2->set("ValueStok", $valuestok);
                        $this->db2->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db2->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("Cabang", 'Pusat');
                        $this->db2->where("KodeProduk", $kodeproduk);
                        $this->db2->where("NoDokumen", $No);
                        $this->db2->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db2->where("Gudang", $gudang);
                        $valid = $this->db2->update('trs_invdet');

                        $invdet = $this->db2->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='Pusat'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db2->set("ValueStok", 0);;
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang", $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');

                        }else{
                            $invdet = $invdet->row();
                            $this->db2->set("ValueStok", $invdet->sumval);
                            $this->db2->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang", $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');
                        }
                }
            }

        }
    }

    //==== Edit  stok ke gudang relokasi pusat =======////
     public function setBackStokOut($No = NULL, $gudang = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $produk = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$No."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                   $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                    // foreach ($produk as $key => $value) {
                    //     $product2 = $produk[$key]->Produk;
                    //     if ($product1 == $product2) {
                            $summary = $summary + $produk[$kunci]->Qty_terima;
                            $valuestok = $produk[$kunci]->Gross;
                    //     }
                    // }
                    $invsum = $this->db2->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."'  limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db2->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                    }
                    // else{
                    //    $this->db2->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                    // }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;      
                if (!empty($kodeproduk)) {
                   $prod2 = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty_terima;

                     $this->db2->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', 'Pusat','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk; 
                $BatchDoc = $produk[$key]->BatchDoc; 
                if (!empty($kodeproduk)) {
                      $det = $this->db2->query("select * from trs_invdet where cabang = 'Pusat' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen='".$BatchDoc."' limit 1")->row(); 
                        $unitcogs = $det->UnitCOGS;   
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty_terima);
                        $valuestok = $qtystok * $unitcogs; 
                        $this->db2->set("UnitStok", $qtystok);
                        $this->db2->set("ValueStok", $valuestok);
                        $this->db2->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db2->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("Cabang", 'Pusat');
                        $this->db2->where("KodeProduk", $kodeproduk);
                        $this->db2->where("NoDokumen", $BatchDoc);
                        $this->db2->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db2->where("Gudang", $gudang);
                        $valid = $this->db2->update('trs_invdet');

                        $invdet = $this->db2->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='Pusat'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db2->set("ValueStok", 0);;
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang", $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');

                        }else{
                            $invdet = $invdet->row();
                            $this->db2->set("ValueStok", $invdet->sumval);
                            $this->db2->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang",  $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');
                        }
                    // }
                }
            }

        }
    }

    public function dataDetailRelokasiKirim($no = null)
    {   
        $query = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$no."'")->result();

        return $query;
    }

    public function dataDetailRelokasiTerima($no = null)
    {   
        $query = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$no."'")->result();

        return $query;
    }

     public function EditDataRelokasiterima($params = null,$data_detail = NULL)
    {
        $valid = false;
        $no = "";
        $no = $params->no_terima;
        $data_detail = json_decode(json_encode($data_detail),true);
        foreach ($data_detail as $key) 
        {
            $Produk          = $key["Produk"];
            $Cabang_Pengirim = $key["Cabang_Pengirim"];
            $Cabang_Penerima = $key["Cabang_Penerima"];
            $NamaProduk      = $key["NamaProduk"];
            $qty_kirim       = $key["qty_kirim"];
            $qty_terima      = $key["qty_terima"];
            $qty             = $key["qty"];
            $harga           = $key["harga"];
            $harga           = str_replace(",","", $harga);
            $gross           = $key["gross"];
            $hpc             = $key["hpc"];
            $disc            = $key["disc"];
            $potongan        = $key["potongan"];
            $val             = $key["val"];
            $Total          = $key["Total"];
            if (!empty($Produk)) 
            {
                
                $this->db->set("Qty_terima", $qty_terima);
                $this->db->set("Qty", $qty);
                $this->db->set("Harga", $harga);
                $this->db->set("Gross", $gross);
                $this->db->set("Value", $val);
                // $this->db->set("Ppn", $ppn);
                $this->db->set("Total", $Total);
                $this->db->set("HPC", $hpc);      
                $this->db->set("Modified_User", $this->user);
                $this->db->set("Modified_Time", date("Y-m-d H:i:s"));
                $this->db->where("Produk", $Produk);
                $this->db->where("No_Terima", $no);
                $valid =  $this->db->update("trs_relokasi_terima_detail");   
               
            }
        }
        $this->db->set("Gross", $params->gross_total);
        $this->db->set("Value", $params->value_total);
        $this->db->set("Disc", $params->disc_total);
        $this->db->set("Potongan", $params->potongan_total);
        // $this->db->set("Ppn", $params->ppn_total);
        $this->db->set("Total", $params->summary_total);
        $this->db->set("modified_user", $this->user);
        $this->db->set("modified_time", date("Y-m-d H:i:s"));
        $this->db->where("No_Terima", $no);
        $valid =  $this->db->update("trs_relokasi_terima_header"); 
        if($valid){
            // $this->setBackStokOut($no,'Git');
            $this->setBackStokIn($no,'Relokasi');                 
        }  

        return $valid;
    }

    public function act($type = null, $no = null)
    {
        $valid = false;
        if ($type == "Approve")
            $status = "Approv Pst";
        elseif($type == "Reject")
            $status = "Riject Pst";

        if ($this->session->userdata('userGroup') == 'CabangLogistik') {
            if($type == "Approve"){
                $No_Relokasi = $this->db->query("SELECT No_Relokasi FROM trs_relokasi_terima_header WHERE No_Terima = '".$no."' ")->row('No_Relokasi');

                $cek = $this->db->query("SELECT * FROM trs_relokasi_terima_header WHERE No_Relokasi = '".$No_Relokasi."' AND Status_kiriman = 'Terima' ")->num_rows();

                if ($cek > 0 ) {
                    $data = ["update" =>true,"message"=>"Sudah"];
                    return $data;
                }else{

                    $this->db->set("Status", 'Terima');
                    $this->db->set("Tgl_terima", date('Y-m-d'));
                    $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
                    $this->db->set("Modified_User", $this->user);
                    $this->db->where("No_Terima", $no);
                    $valid = $this->db->update('trs_relokasi_terima_detail');

                    $this->db->set("Status_kiriman", "Terima");
                    $this->db->set("Tgl_terima", date('Y-m-d'));
                    $this->db->set("modified_time", date('Y-m-d H:i:s'));
                    $this->db->set("modified_user", $this->user);
                    $this->db->where("No_Terima", $no);
                    $valid = $this->db->update('trs_relokasi_terima_header');
                    $this->setStokbaik($no,'Baik');
                    // $this->setBackStokOut($no,'Git');
                    $message = "approve";
                    $this->db2 = $this->load->database('pusat', TRUE); 
                    if ($this->db2->conn_id == TRUE) {
                        $DO = $this->db->query("select No_DO from trs_relokasi_terima_header
                            where No_Terima ='".$no."' limit 1")->row();
                        $this->db->set('Status', 'Closed');
                        $this->db->where('NoDokumen', $DO->No_DO);
                        $this->db->update('trs_delivery_order_header');

                        $this->db->set('Status', 'Closed');
                        $this->db->where('NoDokumen', $DO->No_DO);
                        $this->db->update('trs_delivery_order_detail');

                        $this->db2->set('Status', 'Closed');
                        $this->db2->where('NoDokumen', $DO->No_DO);
                        $this->db2->update('trs_delivery_order_header');

                        $this->db2->set('Status', 'Closed');
                        $this->db2->where('NoDokumen', $DO->No_DO);
                        $this->db2->update('trs_delivery_order_detail');
                    }
                }

            }
            elseif($type == "Reject")
            {
                $this->db->set("Status", 'Batal');
                $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
                $this->db->set("Modified_User", $this->user);
                $this->db->where("No_Terima", $no);
                $valid = $this->db->update('trs_relokasi_terima_detail');

                $this->db->set("Status_kiriman", "Batal");
                $this->db->set("modified_time", date('Y-m-d H:i:s'));
                $this->db->set("modified_user", $this->user);
                $this->db->where("No_Terima", $no);
                $valid = $this->db->update('trs_relokasi_terima_header');
                // $valid   = "true";
                $message = "reject";
            }
            $data = ["update" =>$valid,"message"=>$message];
            return $data; 
            
        }else{
            $data = ["update" =>true,"message"=>"no"];
            return $data;
        }  

    }

    //==== nambah  stok ke gudang baik cabang =======////
    public function setStokbaik($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$No."'")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
               $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                        $valuestok = $produk[$kunci]->Gross;
                    // }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                   $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");     
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$kunci]->Cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk;  
            if (!empty($kodeproduk)) {
              $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $cek = $this->db->query("select * from trs_invdet where KodeProduk ='".$kodeproduk."' and BatchNo ='".$produk[$key]->BatchNo."' and NoDokumen='".$No."' and gudang='".$gudang."' and Tahun='".date('Y')."'")->num_rows();
                if($cek == 0){
                    $UnitStok = $produk[$key]->Qty+$produk[$key]->Bonus;
                    $valuestok = $produk[$key]->Value;  
                    $unitcogs = $valuestok / $UnitStok;
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $produk[$key]->Cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
                }else{
                    $cek2 = $this->db->query("select * from trs_invdet where KodeProduk ='".$kodeproduk."' and BatchNo ='".$produk[$key]->BatchNo."' and NoDokumen='".$No."' and gudang='".$gudang."' and Tahun='".date('Y')."'")->row();
                    $UnitStok = $cek2->UnitStok + $produk[$key]->Qty+$produk[$key]->Bonus;
                    $valuestok = $cek2->UnitCOGS * $UnitStok;  
                    // $unitcogs = $valuestok / $UnitStok;
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $produk[$key]->Cabang);
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $valid = $this->db->update('trs_invdet');
                } 
                    
                
                 $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                 $invdet = $this->db->query("select KodeProduk,sum(ValueStok) as 'valuestok',sum(unitstok) as 'sumunit' from trs_invdet where KodeProduk ='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' and UnitStok != 0 group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$produk[$key]->Cabang);
                        $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $ValueStok = $invdet->valuestok;
                    $unitcogs = $ValueStok / $invdet->sumunit;
                    $this->db->query("update trs_invsum set ValueStok = ".$valuestok.", UnitCOGS = '".$unitcogs."' where KodeProduk='".$kodeproduk."' and Cabang='".$produk[$key]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }
            }
        }

    }

    //==== kurangi stok ke gudang relokasi cabang =======////
     public function setStokrelokasi($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                        $valuestok = $produk[$kunci]->Gross;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok - $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
               $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$kunci]->Cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk;  
            if (!empty($kodeproduk)) {
                $det = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen='".$No."' limit 1")->row(); 
                    $unitcogs = $det->UnitCOGS;
                    $qtystok = $det->UnitStok - ($produk[$key]->Qty + $produk[$key]->Bonus);
                    $valuestok = $qtystok * $unitcogs; 
                    $this->db->set("UnitStok", $qtystok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $produk[$key]->Cabang);
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $valid = $this->db->update('trs_invdet');

                    $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }

            }
        }

    } 

    public function setStokCabangIn($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$kunci]->Cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk; 
            $BatchDoc = $produk[$key]->BatchDoc;  
            if (!empty($kodeproduk)) {
                $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();    
                $det = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 
                $relo = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$No."' limit 1"); 
                if ($relo->num_rows() <= 0) {
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;
                    $valuestok = ($produk[$key]->Qty + $produk[$key]->Bonus) * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $produk[$key]->Cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
                }else{
                    $unitrelo = $relo->row(); 
                    $UnitStok = $unitrelo->UnitStok + ($produk[$key]->Qty + $produk[$key]->Bonus);
                    $valuestok = $UnitStok * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("Tahun", date('Y'));
                    $valid = $this->db->insert('trs_invdet');
                }

                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and cabang = '".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');

                    }
                // }
            }
        }

    }

    //==== kurangi  stok ke gudang relokasi  =======////
    public function setStokCabangOut($No = NULL, $gudang = NULL)
    {   
 
            $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    // foreach ($produk as $key => $value) {
                    //     $product2 = $produk[$key]->Produk;
                    //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Gross;
                    //     }
                    // }
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                        $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;
                $BatchDoc = $produk[$key]->BatchDoc;      
                if (!empty($kodeproduk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$key]->Cabang."', '".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk; 
                $BatchDoc = $produk[$key]->BatchDoc; 
                if (!empty($kodeproduk)) {
                     $det = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 

                        $unitcogs = $det->UnitCOGS;   
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs; 
                        $this->db->set("UnitStok", $qtystok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $produk[$key]->Cabang);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("NoDokumen", $BatchDoc);
                        $this->db->where("BatchNo", $produk[$key]->BatchNo);
                        // $this->db->where("ExpDate", $produk[$key]->ExpDate);
                        $this->db->where("Gudang", $gudang);
                        $valid = $this->db->update('trs_invdet');
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and cabang = '".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');

                    }

                }
            } 
    }

    public function updatedataRelokasipusat(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {

            $doheader = $this->db2->query("select * from trs_relokasi_kirim_header where Status_kiriman = 'Batal' and Cabang='".$this->session->userdata('cabang')."'")->result();
            foreach($doheader as $r) {
               
                    $cekdo = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$r->No_Relokasi."' and Cabang='".$this->session->userdata('cabang')."' and Status_kiriman ='Batal'")->num_rows();
                    // ==== BACA DETAIL ====
                    $dodetail = $this->db2->query("select * from trs_relokasi_kirim_detail where Cabang='".$this->session->userdata('cabang')."' and No_Relokasi = '".$r->No_Relokasi."'")->result();
                    if($cekdo==0){
                        $this->db->set('Status_kiriman', 'Batal');
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_header");

                        $this->db->set('Status', 'Batal');
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_detail");
                        $this->setStok($r->No_Relokasi);
                        $this->setStokreject($r->No_Relokasi,$this->session->userdata('cabang'),'Relokasi');
                    }

            }

             $data = $this->db2->query("select * from trs_relokasi_kirim_header where ifnull(Keterangan2,'') = 'Edit Disc' and Cabang='".$this->session->userdata('cabang')."'");

             if ($data->num_rows() > 0) {
                foreach ($data->result() as $r) {
                    $this->db->replace("trs_relokasi_kirim_header", $r);

                    $this->db2->set('Keterangan2', 'Edit Disc Berhasil');
                    $this->db2->where('No_Relokasi', $r->No_Relokasi);
                    $this->db2->where('Cabang', $this->session->userdata('cabang'));
                    $this->db2->update("trs_relokasi_kirim_header");

                    $this->db->set('Status_kiriman', 'Kirim');
                    $this->db->where('No_Relokasi', $r->No_Relokasi);
                    $this->db->where('Cabang', $this->session->userdata('cabang'));
                    $this->db->update("trs_relokasi_kirim_header");

                    $data_detail = $this->db2->query("select * from trs_relokasi_kirim_detail where Cabang='".$this->session->userdata('cabang')."' and No_Relokasi = '".$r->No_Relokasi."'")->result();

                    foreach ($data_detail as $dt) {
                        $this->db->replace("trs_relokasi_kirim_detail", $dt);

                        $this->db->set('Status', 'Kirim');
                        $this->db->where('No_Relokasi', $dt->No_Relokasi);
                        $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_detail");
                    }
                }    
             }


            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

    public function setStok($No = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    foreach ($produk as $key => $value) {
                        $product2 = $produk[$key]->Produk;
                        if ($product1 == $product2) {
                            $summary = $summary + $produk[$key]->Qty + $produk[$key]->Bonus;
                            $valuestok = $produk[$key]->Gross;
                        }
                    }
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok + $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$produk[$kunci]->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                        $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$produk[$kunci]->Cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;
                $BatchDoc = $produk[$key]->BatchDoc;      
                if (!empty($kodeproduk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Value;
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$produk[$key]->Cabang."', '".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', 'Baik', 'Relokasi', '".$produk[$key]->BatchDoc."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk; 
                $BatchDoc = $produk[$key]->BatchDoc; 
                if (!empty($kodeproduk)) {
                     $det = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 

                        $unitcogs = $det->UnitCOGS;   
                        $qtystok = $det->UnitStok + ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs; 
                        $this->db->set("UnitStok", $qtystok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $produk[$key]->Cabang);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("NoDokumen", $BatchDoc);
                        $this->db->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db->where("Gudang", 'Baik');
                        $valid = $this->db->update('trs_invdet');

                    $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and cabang ='".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", $invdet->sumval/$invdet->sumunit);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }
                }
            } 

    }

     public function setStokreject($No = NULL, $cabang= NULL, $gudang = NULL)
    {   
         $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $produk = $this->db2->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' and Cabang = '".$cabang."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                    foreach ($produk as $key => $value) {
                        $product2 = $produk[$key]->Produk;
                        if ($product1 == $product2) {
                            $summary = $summary + $produk[$key]->Qty + $produk[$key]->Bonus;
                            $valuestok = $produk[$key]->Gross;
                        }
                    }
                    $invsum = $this->db2->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db2->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='Pusat' and Tahun = '".date('Y')."' and Gudang='".$gudang."' limit 1");
                    }
                    else{
                        $this->db2->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");    
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;   
                $BatchDoc = $produk[$key]->BatchDoc;   
                if (!empty($kodeproduk)) {
                    $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                   $this->db2->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', 'Pusat','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk; 
                $BatchDoc = $produk[$key]->BatchDoc; 
                if (!empty($kodeproduk)) {
                     $det = $this->db2->query("select * from trs_invdet where cabang = 'Pusat' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and BatchNo = '".$produk[$key]->BatchNo."' and NoDokumen='".$No."' limit 1")->row(); 
                        $unitcogs = $det->UnitCOGS;    
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $produk[$key]->Value;
                        $this->db2->set("UnitStok", $qtystok);
                        $this->db2->set("ValueStok", $valuestok);
                        $this->db2->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db2->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("Cabang", 'Pusat');
                        $this->db2->where("KodeProduk", $kodeproduk);
                        $this->db2->where("NoDokumen", $No);
                        $this->db2->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db2->where("Gudang", $gudang);
                        $valid = $this->db2->update('trs_invdet');

                        $invdet = $this->db2->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='Pusat'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db2->set("ValueStok", 0);;
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang", $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');

                        }else{
                            $invdet = $invdet->row();
                            $this->db2->set("ValueStok", $invdet->sumval);
                            $this->db2->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db2->where("KodeProduk", $kodeproduk);
                            $this->db2->where("Gudang", $gudang);
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",'Pusat');
                            $valid = $this->db2->update('trs_invsum');
                        }
                    // }
                }
            } 

        }
            
    }

    public function prosesDataRelokasi($no = NULL,$cabang= NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_relokasi_kirim_header set Dokumen_2 = 'Berhasil' where No_Relokasi = '".$no."' and Cabang ='".$cabang."'");

            $query = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$no."' and Cabang ='".$cabang."'")->result();
            $cek = $this->db2->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$no."' and Cabang ='".$cabang."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_relokasi_kirim_detail', $r); 
                }
            }
            // else{
            //     foreach($query as $r) { // loop over results
            //         $this->db2->where('Cabang', $cabang);
            //         $this->db2->where('noline', $r->noline);
            //         $this->db2->where('BatchNo', $r->BatchNo);
            //         $this->db2->where('Produk', $r->Produk);
            //         $this->db2->where('No_Relokasi', $no);
            //         $this->db2->update('trs_relokasi_kirim_detail', $r);
            //     }
            // }
// return;
            $query2 = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$no."' and Cabang ='".$cabang."'")->row();
            $cek2 = $this->db2->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$no."' and Cabang ='".$cabang."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_relokasi_kirim_header', $query2); // insert each row to another table
            }
            // else{
            //     $this->db2->where('Cabang', $cabang);
            //     $this->db2->where('No_Relokasi', $no);
            //     $this->db2->update('trs_relokasi_kirim_header', $query2);
            // }
            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_relokasi_kirim_header set Dokumen_2 = 'Gagal' where No_Relokasi = '".$no."' and Cabang ='".$cabang."'");
            return FALSE;
        }
    }

     //==== Tambah  stok ke gudang Baik dari reject relokasi  =======////
    public function setStokCabangIn_ByReject($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk; 
            $BatchDoc = $produk[$key]->BatchDoc;  
            if (!empty($kodeproduk)) {
                $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();    
                $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 
                $relo = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$No."' limit 1"); 

                // if ($relo->num_rows() <= 0) {
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;
                    $valuestok = ($produk[$key]->Qty + $produk[$key]->Bonus) * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
               /* }else{
                    $unitrelo = $relo->row(); 
                    $UnitStok = $unitrelo->UnitStok + ($produk[$key]->Qty + $produk[$key]->Bonus);
                    $valuestok = $UnitStok * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("Tahun", date('Y'));
                    $valid = $this->db->insert('trs_invdet');
                }*/

                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and cabang = '".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');

                    }
                // }
            }
        }

    }

    public function updatedataRelokasipusatReject(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {

            $doheader = $this->db2->query("select * from trs_relokasi_kirim_header where Status_kiriman in ('RejectPusat','RejectCabPenerima') and Cabang='".$this->session->userdata('cabang')."'")->result();
            foreach($doheader as $r) {
               
                    $cekdo = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$r->No_Relokasi."' and Cabang='".$this->session->userdata('cabang')."' and Status_kiriman ='Batal'")->num_rows();
                    // ==== BACA DETAIL ====
                    $dodetail = $this->db2->query("select * from trs_relokasi_kirim_detail where Cabang='".$this->session->userdata('cabang')."' and No_Relokasi = '".$r->No_Relokasi."'")->result();
                    if($cekdo==0){
                        $this->db->set('Status_kiriman', $r->Status_kiriman);
                        $this->db->set('Keterangan', $r->Keterangan);
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        $this->db->where('Status_kiriman <>', 'Reject');
                        $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_header");

                        $this->db->set('Status', $r->Status_kiriman);
                        $this->db->set('Keterangan_reject', $r->Keterangan);
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        $this->db->where('Status <>', 'Reject');
                        $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_detail");
                        /*$this->setStok($r->No_Relokasi);
                        $this->setStokreject($r->No_Relokasi,$this->session->userdata('cabang'),'Relokasi');*/
                    }

            }
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

    
    public function load_datarelokasireject($search=null, $limit=null, $status=null){
        $header = $this->db->query("select * from trs_relokasi_kirim_header where (Cabang='".$this->cabang."' or Cabang_Pengirim
            ='".$this->cabang."') and status_kiriman in ('RejectPusat','Reject','RejectCabPenerima') $search order by Tgl_kirim DESC, No_Relokasi ASC $limit");
        return $header;
    }

    public function RejectPenerima( $no = null)
    {
        $valid = false;
      
            $status = "RejectCabPenerima";

        if ($this->session->userdata('userGroup') == 'CabangLogistik') {
            // if($type == "Approve"){
               
                $message = "RejectCabPenerima";
                $this->db2 = $this->load->database('pusat', TRUE); 
                if ($this->db2->conn_id == TRUE) {
                    $DO = $this->db->query("select No_Relokasi from trs_relokasi_terima_header
                        where No_Terima ='".$no."' limit 1")->row();

                    $this->db->set('Status_kiriman', $status);
                    $this->db->set('Keterangan', "Reject Cabang Penerima");
                    $this->db->where('No_Relokasi', $DO->No_Relokasi);
                    $this->db->update('trs_relokasi_terima_header');

                    $this->db->set('Status', $status);
                    $this->db->set('Keterangan', "Reject Cabang Penerima");
                    $this->db->where('No_Relokasi', $DO->No_Relokasi);
                    $this->db->update('trs_relokasi_terima_detail');

                    $this->db2->set('Status_kiriman', $status);
                    $this->db2->set('Keterangan', "Reject Cabang Penerima");
                    $this->db2->set('tgl_reject', date('Y-m-d'));
                    $this->db2->where('No_Relokasi', $DO->No_Relokasi);
                    $this->db2->update('trs_relokasi_kirim_header');

                    $this->db2->set('Status', $status);
                    $this->db2->set('Keterangan_reject', "Reject Cabang Penerima");
                    $this->db2->set('tgl_reject', date('Y-m-d'));
                    $this->db2->where('No_Relokasi', $DO->No_Relokasi);
                    $this->db2->update('trs_relokasi_kirim_detail');

                    $data = ["update" =>$valid,"message"=>$message];
                    return $data; 
                }else{
                    $data = ["update" =>false,"message"=>"tidak terhubung ke pusat"];
                    return $data; 
                }

            // }
           
            
        }else{
            $data = ["update" =>true,"message"=>"no"];
            return $data;
        }  

    }

    public function ApprovedataRelokasipusatReject()
     {

        $no_relokasi = $_POST['no_relokasi'];
        


            $doheader = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '$no_relokasi' and (Cabang='".$this->session->userdata('cabang')."' or Cabang_pengirim='".$this->session->userdata('cabang')."')")->result();

            foreach($doheader as $r) {
               
                    $cekdo = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$r->No_Relokasi."' and (Cabang='".$this->session->userdata('cabang')."' or Cabang_pengirim='".$this->session->userdata('cabang')."') and Status_kiriman ='Batal'")->num_rows();
                    // ==== BACA DETAIL ====
                    $dodetail = $this->db->query("select * from trs_relokasi_kirim_detail where (Cabang='".$this->session->userdata('cabang')."' or Cabang_pengirim='".$this->session->userdata('cabang')."') and No_Relokasi = '".$r->No_Relokasi."'")->result();
                    if($cekdo==0){
                        $this->db->set('Status_kiriman', 'Reject');
                        $this->db->set('tgl_reject', date('Y-m-d'));
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        // $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_header");

                        $this->db->set('Status', 'Reject');
                        $this->db->set('tgl_reject', date('Y-m-d'));
                        $this->db->where('No_Relokasi', $r->No_Relokasi);
                        // $this->db->where('Cabang', $this->session->userdata('cabang'));
                        $this->db->update("trs_relokasi_kirim_detail");


                            //update ke pusat
                          $this->db2 = $this->load->database('pusat', TRUE); 

                         if ($this->db2->conn_id == TRUE) {
                            $this->db2->set('Status_kiriman', 'Reject');
                            $this->db2->set('tgl_reject', date('Y-m-d'));
                            $this->db2->where('No_Relokasi', $r->No_Relokasi);
                            // $this->db2->where('Cabang', $this->session->userdata('cabang'));
                            $this->db2->update("trs_relokasi_kirim_header");

                            $this->db2->set('Status', 'Reject');
                            $this->db2->set('tgl_reject', date('Y-m-d'));
                            $this->db2->where('No_Relokasi', $r->No_Relokasi);
                            // $this->db2->where('Cabang', $this->session->userdata('cabang'));
                            $this->db2->update("trs_relokasi_kirim_detail");
                         }

                        /*$this->setStok($r->No_Relokasi);
                        $this->setStokreject($r->No_Relokasi,$this->session->userdata('cabang'),'Relokasi');*/
                        $this->setStokCabangIn_ByReject($r->No_Relokasi,"Baik");
                    }

            }
      
    }

    public function updatedataRelokasiKirimanPusat(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {

            $reloheader = $this->db2->query("SELECT * from trs_relokasi_kirim_header where ifnull(GudangPusat,'') <> '' and Cabang_Pengirim='".$this->session->userdata('cabang')."' and Status_kiriman = 'ApproveLog'  ")->result();

            $stok = $Keterangan = "";
            foreach($reloheader as $r) {
               
                    $cekdo = $this->db->query("SELECT * from trs_relokasi_kirim_header where No_Relokasi = '".$r->No_Relokasi."' and Cabang_Pengirim='".$this->session->userdata('cabang')."' and ifnull(GudangPusat,'') <> '' ")->num_rows();
                    
                    if($cekdo==0){
                        $this->db->insert("trs_relokasi_kirim_header",$r);

                        

                        $relodetail = $this->db2->query("SELECT * from trs_relokasi_kirim_detail where No_Relokasi = '".$r->No_Relokasi."' and Cabang_Pengirim='".$this->session->userdata('cabang')."' and ifnull(GudangPusat,'') <> '' ");

                        if ($relodetail->num_rows() > 0) {
                            foreach ($relodetail->result() as $k) {

                                $this->db->insert("trs_relokasi_kirim_detail",$k);
                            }

                            foreach ($relodetail->result() as $k) {
                                $cek_stok = $this->db->query("SELECT * FROM trs_invdet WHERE Kodeproduk = '".$k->Produk."' AND BatchNo ='".$k->BatchNo."' AND NoDokumen ='".$k->BatchDoc."' AND ExpDate = '".$k->ExpDate."' AND tahun = year(curdate()) AND gudang = 'Baik' AND UnitStok >= ".$k->Qty." ")->num_rows();

                                if ($cek_stok > 0 ) {
                                    $stok = 'Stok Tersedia';
                                    $Keterangan = '';
                                }else{
                                    $stok = 'Stok Kosong';
                                    log_message("error",$k->Produk . " stok tidak terpenuhi, Relokasi Pusat");
                                    $Keterangan = $k->Produk . " stok tidak terpenuhi";
                                    break;
                                }

                            }
                            //
                            foreach ($relodetail->result() as $k) {
                                if ($stok == 'Stok Kosong') {

                                    //update pusat
                                    $this->db2->set("Status_kiriman", 'Batal');
                                    $this->db2->set("Modified_Time", $k->Modified_Time);
                                    $this->db2->set("Modified_User", $k->Modified_User);
                                    $this->db2->set("Keterangan", $Keterangan);
                                    $this->db2->where('No_Relokasi', $r->No_Relokasi);
                                    $this->db2->update("trs_relokasi_kirim_header");

                                    $this->db2->set("Status", 'Batal');
                                    $this->db2->set("Modified_Time", $k->Modified_Time);
                                    $this->db2->set("Modified_User", $k->Modified_User);
                                    $this->db2->set("Keterangan", $Keterangan);
                                    $this->db2->where('No_Relokasi', $r->No_Relokasi);
                                    $this->db2->update("trs_relokasi_kirim_detail");

                                    $this->db->set("Status_kiriman", 'Batal');
                                    $this->db->set("Modified_Time", $k->Modified_Time);
                                    $this->db->set("Keterangan", $Keterangan);
                                    $this->db->where('No_Relokasi', $r->No_Relokasi);
                                    $this->db->update("trs_relokasi_kirim_header");

                                    $this->db->set("Status", 'Batal');
                                    $this->db->set("Modified_Time", $k->Modified_Time);
                                    $this->db->set("Keterangan", $Keterangan);
                                    $this->db->where('No_Relokasi', $r->No_Relokasi);
                                    $this->db->update("trs_relokasi_kirim_detail");
                                }

                                
                                # code...
                            }

                            
                            if ($stok != 'Stok Kosong') {
                                /*$GetCabang = $this->db->query("SELECT GetCabang from trs_relokasi_kirim_header where No_Relokasi = '".$r->No_Relokasi."' and Cabang_Pengirim='".$this->session->userdata('cabang')."' and ifnull(GudangPusat,'') <> '' ")->row('GetCabang');

                                if ($GetCabang == 'N') {*/
                                    $this->setStokCabangOutReloPusat($r->No_Relokasi,'Baik');
                                    $this->setStokCabangInReloPusat($r->No_Relokasi,'Relokasi');
                                // }
                            }  
                        }

                       

                    }else{
                       //
                        
                    }

                    //END update status tarik cabang
            }
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

    //==== kurangi  stok ke gudang relokasi  =======////
    public function setStokCabangOutReloPusat($No = NULL, $gudang = NULL)
    {   
 
            $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."'")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    // foreach ($produk as $key => $value) {
                    //     $product2 = $produk[$key]->Produk;
                    //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Gross;
                    //     }
                    // }
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                        $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;
                $BatchDoc = $produk[$key]->BatchDoc;      
                if (!empty($kodeproduk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // update inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk; 
                $BatchDoc = $produk[$key]->BatchDoc; 
                if (!empty($kodeproduk)) {
                     $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 

                        $unitcogs = $det->UnitCOGS;   
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs; 
                        $this->db->set("UnitStok", $qtystok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("NoDokumen", $BatchDoc);
                        $this->db->where("BatchNo", $produk[$key]->BatchNo);
                        // $this->db->where("ExpDate", $produk[$key]->ExpDate);
                        $this->db->where("Gudang", $gudang);
                        $valid = $this->db->update('trs_invdet');
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and cabang = '".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');

                    }

                }
            } 
    }

    public function setStokCabangInReloPusat($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");   
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
        foreach ($produk as $key => $value) { 
            $kodeproduk = $produk[$key]->Produk; 
            $BatchDoc = $produk[$key]->BatchDoc;  
            if (!empty($kodeproduk)) {
                $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();    
                $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 
                $relo = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$No."' limit 1");

                if ($relo->num_rows() <= 0) {
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;
                    $valuestok = ($produk[$key]->Qty + $produk[$key]->Bonus) * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("KodePrinsipal", $prod_det->Kode);
                    $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                    $this->db->set("Pabrik", $prod_det->Pabrik);
                    $this->db->set("KodeProduk", $kodeproduk);
                    $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("NoDokumen", $No);
                    $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("BatchNo", $produk[$key]->BatchNo);
                    $this->db->set("ExpDate", $produk[$key]->ExpDate);
                    $this->db->set("Gudang", $gudang);
                    $valid = $this->db->insert('trs_invdet');
                }else{
                    $unitrelo = $relo->row(); 
                    $UnitStok = $unitrelo->UnitStok + ($produk[$key]->Qty + $produk[$key]->Bonus);
                    $valuestok = $UnitStok * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;  
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", $unitcogs);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $kodeproduk);
                    $this->db->where("NoDokumen", $No);
                    $this->db->where("BatchNo", $produk[$key]->BatchNo);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("Tahun", date('Y'));
                    $valid = $this->db->update('trs_invdet');
                }

                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and cabang = '".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');

                    }
                // }
            }
        }

    }

    public function load_datarelokasikirimanPusat($search=null, $limit=null, $status=null){
        $header = $this->db->query("select * from trs_relokasi_kirim_header where Cabang_Pengirim='".$this->cabang."' AND ifnull(GudangPusat,'') <> '' $search order by Tgl_kirim DESC, No_Relokasi ASC $limit");
        return $header;
    }
}