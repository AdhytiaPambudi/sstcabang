<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_bpp extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->model('Model_main');
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }

    public function PR()
    {   
        $query = $this->db->query("select No_PR, Cabang from(select No_PR, Cabang from trs_po_header where Status_PO='open' union all select NoPR as No_PR, Cabang from trs_terima_barang_detail where QtyPO > Qty) t group by No_PR")->result();

        return $query;
    }

    public function getDO($cab = NULL)
    {
        $byCabang = "";
        $query = "";
        if (!empty($cab)) {
            $byCabang = "and Cabang = '".$cab."'";            
            $query = $this->db->query("select NoDokumen from trs_delivery_order_header where Status != 'Closed' and NoBPB is not null ".$byCabang." group by NoDokumen ORDER BY RIGHT(NoDokumen,5)")->result();
        }

        return $query;
    } 

    public function getBPBCab()
    {
        $byCabang = "";
        $query = "";
        if (!empty($cab)) {
            // $byCabang = "and Cabang = '".$cab."'";            
            $query = $this->db->query("select * from trs_terima_barang_cabang_header where Status = 'Open' ".$byCabang." and flag_suratjalan='N'")->result();
        }

        return $query;
    } 

    public function getPO($cab = NULL)
    {
        $byCabang = "";
        $query = "";
        if (!empty($cab)) {
            $byCabang = "and Cabang = '".$cab."'";            
            $query = $this->db->query("select No_PO from trs_po_header where Status_PO Not In ('Closed','BPP') ".$byCabang." and flag_suratjalan='Y' group by No_PO ORDER BY RIGHT(No_PO,5)")->result();
        }

        return $query;
    } 

    public function getDataDO($no = NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {
            $byNo = " and trs_delivery_order_detail.NoDokumen = '".$no."'";            
            $query = $this->db->query("select trs_delivery_order_detail.*,trs_delivery_order_header.BiayaKirim from trs_delivery_order_detail, trs_delivery_order_header where trs_delivery_order_detail.NoDokumen = trs_delivery_order_header.NoDokumen".$byNo)->result();
        }

        return $query;
    }   

    public function listDataBPP()
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select * from trs_terima_barang_header where Cabang ='Pusat' order by TimeDokumen DESC,NoDokumen ASC");

        return $query;
    } 

    public function listDataBPB()
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select * from trs_terima_barang_header where Cabang !='Pusat' order by TimeDokumen DESC,NoDokumen ASC");

        return $query;
    } 
    public function dataBPP($cabang=null,$no = NULL)
    {
        if (!empty($no)) {         
            $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."' and Cabang='".$cabang."'")->result();
        }
        return $query;
    } 
    
    public function getDataPO($no = NULL,$cabang=NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {   
            $exp = explode('/',$no);
            if($exp[1] != 'PST'){
                $query = $this->db->query("
                            SELECT 
                                    trs_po_detail.id,trs_po_detail.Cabang,
                                    mst_produk.PPH22,
                                    CASE mst_produk.PPH22 WHEN 'Y' THEN (0.003 * (IFNULL(trs_po_detail.Value_PO,0)+(IFNULL(trs_po_detail.Value_PO,0) * 0.1))) ELSE 0 END AS PPH22Val,
                                    trs_po_detail.Prinsipal,trs_po_detail.Supplier,trs_po_detail.Kategori,
                                    trs_po_detail.Produk,trs_po_detail.Nama_Produk,trs_po_detail.Satuan,
                                    trs_po_detail.Keterangan,trs_po_detail.Penjelasan,
                                    trs_po_detail.No_Usulan,trs_po_detail.No_PR,
                                    trs_po_detail.Avg,trs_po_detail.Indeks,
                                    trs_po_detail.Stok,trs_po_detail.GIT,
                                    CASE 
                                    WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty,0) END
                                    AS Qty,
                                    IFNULL(trs_po_detail.Disc_Deal,0) AS Disc_Deal,
                                    IFNULL(trs_po_detail.Disc2,0) AS Disc2,
                                    IFNULL(trs_po_detail.Bonus,0) AS Bonus,
                                    IFNULL(trs_po_detail.Disc_Cab,0) AS Disc_Cab,
                                    IFNULL(trs_po_detail.Harga_Beli_Cab,0) AS Harga_Beli_Cab,
                                    IFNULL(trs_po_detail.Harga_Deal,0) AS Harga_Deal,
                                    IFNULL(trs_po_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_po_detail.HPC,0) AS HPC,
                                    IFNULL(trs_po_detail.HPC1,0) AS HPC1,
                                    IFNULL(trs_po_detail.No_PO,0) AS No_PO,
                                    IFNULL(trs_po_detail.Tgl_PO,0) AS Tgl_PO,
                                    IFNULL(trs_po_detail.Time_PO,0) AS Time_PO,
                                    IFNULL(trs_po_detail.Status_PO,0) AS Status_PO,
                                    CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty_PO,0)
                                    end AS Qty_PO,
                                    IFNULL(trs_po_detail.Sisa_PO,0) AS Sisa_PO,
                                    IFNULL(trs_po_detail.Disc_Pst,0) AS Disc_Pst,
                                    IFNULL(trs_po_detail.Harga_Beli_Pst,0) AS Harga_Beli_Pst,
                                    IFNULL(trs_po_detail.Pot_Pst,0) AS Pot_Pst,
                                    IFNULL(trs_po_detail.Value_PO,0) AS Value_PO,
                                    (IFNULL(trs_po_detail.Value_PO,0) * 0.1) AS Value_PPNPO,
                                    IFNULL(trs_po_detail.HPP,0) AS HPP,
                                    IFNULL(trs_po_detail.HPP1,0) AS HPP1,
                                    IFNULL(trs_po_detail.Counter_PO,0) AS Counter_PO,
                                    IFNULL(trs_po_detail.Qty,0) + IFNULL(trs_po_detail.Bonus,0) AS banyak,
                                    '0' AS BiayaKirim, 
                                    IFNULL(trs_po_header.No_Usulan,0) AS No_Usulan,
                                    IFNULL(trs_pembelian_detail.Value_PR,0) AS Value_PR, 
                                    IFNULL(trs_pembelian_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_pembelian_detail.Value_PR,0) AS Value_PR,
                                    (IFNULL(trs_pembelian_detail.Value_PR,0)*0.1) AS Value_PPNPR
                            FROM trs_po_detail, trs_po_header ,trs_pembelian_detail, mst_produk,trs_pembelian_header
                            WHERE trs_po_detail.No_PO = trs_po_header.No_PO AND 
                                  trs_po_detail.Cabang = trs_po_header.Cabang AND
                                  trs_pembelian_header.No_PR=trs_pembelian_detail.No_PR and 
                                  trs_pembelian_header.Cabang=trs_pembelian_detail.Cabang and 
                                   trs_po_header.No_PR=trs_pembelian_header.No_PR and 
                                    trs_po_detail.No_PR=trs_pembelian_detail.No_PR and 
                                    trs_po_detail.Cabang=trs_pembelian_detail.Cabang and 
                                    trs_po_detail.Produk=trs_pembelian_detail.Produk AND 
                                    trs_po_detail.Produk=mst_produk.Kode_Produk and 
                                    trs_po_header.flag_suratjalan = 'Y' and 
                                 trs_po_detail.Status_PO = 'Open' AND trs_po_header.No_PO =  '".$no."' and trs_po_header.Cabang ='".$cabang."'")->result();
            }else{
                $query = $this->db->query("
                            SELECT 
                                    trs_po_detail.id,trs_po_detail.Cabang,
                                    mst_produk.PPH22,
                                    CASE mst_produk.PPH22 WHEN 'Y' THEN (0.003 * (IFNULL(trs_po_detail.Value_PO,0)+(IFNULL(trs_po_detail.Value_PO,0) * 0.1))) ELSE 0 END AS PPH22Val,
                                    trs_po_detail.Prinsipal,trs_po_detail.Supplier,trs_po_detail.Kategori,
                                    trs_po_detail.Produk,trs_po_detail.Nama_Produk,trs_po_detail.Satuan,
                                    trs_po_detail.Keterangan,trs_po_detail.Penjelasan,
                                    trs_po_detail.No_Usulan,trs_po_detail.No_PR,
                                    trs_po_detail.Avg,trs_po_detail.Indeks,
                                    trs_po_detail.Stok,trs_po_detail.GIT,
                                    CASE 
                                    WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty,0) END
                                    AS Qty,
                                    IFNULL(trs_po_detail.Disc_Deal,0) AS Disc_Deal,
                                    IFNULL(trs_po_detail.Disc2,0) AS Disc2,
                                    IFNULL(trs_po_detail.Bonus,0) AS Bonus,
                                    IFNULL(trs_po_detail.Disc_Cab,0) AS Disc_Cab,
                                    IFNULL(trs_po_detail.Harga_Beli_Cab,0) AS Harga_Beli_Cab,
                                    IFNULL(trs_po_detail.Harga_Deal,0) AS Harga_Deal,
                                    IFNULL(trs_po_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_po_detail.HPC,0) AS HPC,
                                    IFNULL(trs_po_detail.HPC1,0) AS HPC1,
                                    IFNULL(trs_po_detail.No_PO,0) AS No_PO,
                                    IFNULL(trs_po_detail.Tgl_PO,0) AS Tgl_PO,
                                    IFNULL(trs_po_detail.Time_PO,0) AS Time_PO,
                                    IFNULL(trs_po_detail.Status_PO,0) AS Status_PO,
                                    CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty_PO,0)
                                    end AS Qty_PO,
                                    IFNULL(trs_po_detail.Sisa_PO,0) AS Sisa_PO,
                                    IFNULL(trs_po_detail.Disc_Pst,0) AS Disc_Pst,
                                    IFNULL(trs_po_detail.Harga_Beli_Pst,0) AS Harga_Beli_Pst,
                                    IFNULL(trs_po_detail.Pot_Pst,0) AS Pot_Pst,
                                    IFNULL(trs_po_detail.Value_PO,0) AS Value_PO,
                                    (IFNULL(trs_po_detail.Value_PO,0) * 0.1) AS Value_PPNPO,
                                    IFNULL(trs_po_detail.HPP,0) AS HPP,
                                    IFNULL(trs_po_detail.HPP1,0) AS HPP1,
                                    IFNULL(trs_po_detail.Counter_PO,0) AS Counter_PO,
                                    IFNULL(trs_po_detail.Qty,0) + IFNULL(trs_po_detail.Bonus,0) AS banyak,
                                    '0' AS BiayaKirim, 
                                    IFNULL(trs_po_header.No_Usulan,0) AS No_Usulan,
                                    IFNULL(trs_pembelian_pusat_detail.Value_PR,0) AS Value_PR, 
                                    IFNULL(trs_pembelian_pusat_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_pembelian_pusat_detail.Value_PR,0) AS Value_PR,
                                    (IFNULL(trs_pembelian_pusat_detail.Value_PR,0)*0.1) AS Value_PPNPR
                            FROM trs_po_detail, trs_po_header ,trs_pembelian_pusat_detail, mst_produk
                            where  trs_po_detail.No_PO = trs_po_header.No_PO AND 
                                  trs_po_detail.Cabang = trs_po_header.Cabang AND
                                    trs_po_detail.No_PR=trs_pembelian_pusat_detail.NoPR and 
                                    trs_po_detail.Cabang=trs_pembelian_pusat_detail.Cabang and 
                                    trs_po_detail.Produk=trs_pembelian_pusat_detail.Produk AND 
                                    trs_po_detail.Produk=mst_produk.Kode_Produk and 
                                    trs_po_header.flag_suratjalan = 'Y' and
                                   trs_po_detail.Status_PO = 'Open' AND trs_po_detail.No_PO =  '".$no."' and trs_po_header.Cabang ='".$cabang."'")->result();
            }    
            
        }
        return $query;
    }  

     public function listDataBPBCabang($search = null, $limit = null, $status = null)
    {   
        // $byStatus = "";
        if (!empty($status)) {
            // $byStatus = " Status_PO in ('Open','OpenGab')";
            $query = $this->db->query("select * from trs_terima_barang_cabang_header 
                                       where flag_suratjalan ='N' and 
                                            Status = '".$status."' $search 
                                        order by TglDokumen DESC,TimeDokumen ASC $limit");
        }else{
            // $byStatus = " Status_PO in ('Open','OpenGab','Closed')";
            $query = $this->db->query("select * from trs_terima_barang_cabang_header 
                                       where flag_suratjalan ='N' $search 
                                        order by TglDokumen DESC,TimeDokumen ASC $limit");
        }
        return $query;
    }

     public function getDataBPBCab($no = NULL,$cabang=NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no) and !empty($cabang)) {   
            $query = $this->db->query("
                            SELECT mst_produk.PPH22,
                                    CASE mst_produk.PPH22 WHEN 'Y' THEN (0.003 * (IFNULL(trs_terima_barang_cabang_detail.Value,0)+(IFNULL(trs_terima_barang_cabang_detail.Value,0) * 0.1))) ELSE 0 END AS PPH22Val,
                                    trs_terima_barang_cabang_detail.*,
                                    trs_terima_barang_cabang_header.*
                            FROM trs_terima_barang_cabang_detail, trs_terima_barang_cabang_header,mst_produk
                            WHERE trs_terima_barang_cabang_header.NoDokumen = trs_terima_barang_cabang_detail.NoDokumen AND 
                                  trs_terima_barang_cabang_header.Cabang = trs_terima_barang_cabang_detail.Cabang AND 
                                  trs_terima_barang_cabang_detail.Produk=mst_produk.Kode_Produk and 
                                  trs_terima_barang_cabang_header.flag_suratjalan = 'N' and 
                                  trs_terima_barang_cabang_header.Status = 'Pending' AND
                                  trs_terima_barang_cabang_header.NoDokumen =  '".$no."' and 
                                  trs_terima_barang_cabang_header.Cabang ='".$cabang."'")->result();
        }
        if (!empty($no) and empty($cabang)) {   
            $query = $this->db->query("
                            SELECT mst_produk.PPH22,
                                    CASE mst_produk.PPH22 WHEN 'Y' THEN (0.003 * (IFNULL(trs_terima_barang_cabang_detail.Value,0)+(IFNULL(trs_terima_barang_cabang_detail.Value,0) * 0.1))) ELSE 0 END AS PPH22Val,
                                    trs_terima_barang_cabang_detail.*,
                                    trs_terima_barang_cabang_header.*
                            FROM trs_terima_barang_cabang_detail, trs_terima_barang_cabang_header,mst_produk
                            WHERE trs_terima_barang_cabang_header.NoDokumen = trs_terima_barang_cabang_detail.NoDokumen AND 
                                  trs_terima_barang_cabang_header.Cabang = trs_terima_barang_cabang_detail.Cabang AND 
                                  trs_terima_barang_cabang_detail.Produk=mst_produk.Kode_Produk and 
                                  trs_terima_barang_cabang_header.flag_suratjalan = 'N' and 
                                  trs_terima_barang_cabang_header.Status = 'Pending' AND
                                  trs_terima_barang_cabang_header.NoDokumen =  '".$no."'")->result();
        }
        return $query;
    }  

    public function getDataProdukPO($no = NULL, $kode = NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {        
            $query = $this->db->query("
                            SELECT 
                                    trs_po_detail.id,trs_po_detail.Cabang,
                                    mst_produk.PPH22,
                                    CASE mst_produk.PPH22 WHEN 'Y' THEN (0.003 * (IFNULL(trs_po_detail.Value_PO,0)+(IFNULL(trs_po_detail.Value_PO,0) * 0.1))) ELSE 0 END AS PPH22Val,
                                    trs_po_detail.Prinsipal,trs_po_detail.Supplier,trs_po_detail.Kategori,
                                    trs_po_detail.Produk,trs_po_detail.Nama_Produk,trs_po_detail.Satuan,
                                    trs_po_detail.Keterangan,trs_po_detail.Penjelasan,
                                    trs_po_detail.No_Usulan,trs_po_detail.No_PR,
                                    trs_po_detail.Avg,trs_po_detail.Indeks,
                                    trs_po_detail.Stok,trs_po_detail.GIT,
                                    CASE 
                                    WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty,0) END
                                    AS Qty,
                                    IFNULL(trs_po_detail.Disc_Deal,0) AS Disc_Deal,
                                    IFNULL(trs_po_detail.Disc2,0) AS Disc2,
                                    IFNULL(trs_po_detail.Bonus,0) AS Bonus,
                                    IFNULL(trs_po_detail.Disc_Cab,0) AS Disc_Cab,
                                    IFNULL(trs_po_detail.Harga_Beli_Cab,0) AS Harga_Beli_Cab,
                                    IFNULL(trs_po_detail.Harga_Deal,0) AS Harga_Deal,
                                    IFNULL(trs_po_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_po_detail.HPC,0) AS HPC,
                                    IFNULL(trs_po_detail.HPC1,0) AS HPC1,
                                    IFNULL(trs_po_detail.No_PO,0) AS No_PO,
                                    IFNULL(trs_po_detail.Tgl_PO,0) AS Tgl_PO,
                                    IFNULL(trs_po_detail.Time_PO,0) AS Time_PO,
                                    IFNULL(trs_po_detail.Status_PO,0) AS Status_PO,
                                    CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 
                                    THEN IFNULL(trs_po_detail.Sisa_PO,0) 
                                    ELSE IFNULL(trs_po_detail.Qty_PO,0)
                                    end AS Qty_PO,
                                    IFNULL(trs_po_detail.Sisa_PO,0) AS Sisa_PO,
                                    IFNULL(trs_po_detail.Disc_Pst,0) AS Disc_Pst,
                                    IFNULL(trs_po_detail.Harga_Beli_Pst,0) AS Harga_Beli_Pst,
                                    IFNULL(trs_po_detail.Pot_Pst,0) AS Pot_Pst,
                                    IFNULL(trs_po_detail.Value_PO,0) AS Value_PO,
                                    (IFNULL(trs_po_detail.Value_PO,0) * 0.1) AS Value_PPNPO,
                                    IFNULL(trs_po_detail.HPP,0) AS HPP,
                                    IFNULL(trs_po_detail.HPP1,0) AS HPP1,
                                    IFNULL(trs_po_detail.Counter_PO,0) AS Counter_PO,
                                    IFNULL(trs_po_detail.Qty,0) + IFNULL(trs_po_detail.Bonus,0) AS banyak,
                                    '0' AS BiayaKirim, 
                                    IFNULL(trs_po_header.No_Usulan,0) AS No_Usulan,
                                    IFNULL(trs_pembelian_pusat_detail.Value_PR,0) AS Value_PR, 
                                    IFNULL(trs_pembelian_pusat_detail.Potongan_Cab,0) AS Potongan_Cab,
                                    IFNULL(trs_pembelian_pusat_detail.Value_PR,0) AS Value_PR,
                                    (IFNULL(trs_pembelian_pusat_detail.Value_PR,0)*0.1) AS Value_PPNPR
                            FROM trs_po_detail, trs_po_header ,trs_pembelian_pusat_detail, mst_produk
                            WHERE trs_po_detail.No_PO = trs_po_header.No_PO AND trs_po_detail.No_PR=trs_pembelian_pusat_detail.NoPR 
                                AND trs_po_detail.No_PO = '$no'
                                AND trs_po_detail.Produk= '$kode'
                                AND trs_po_detail.Produk=trs_pembelian_pusat_detail.Produk 
                                AND trs_po_detail.Produk=mst_produk.Kode_Produk 
                        ")->result();
        }

        return $query;
    }  

    public function getCounterBPP($cab = NULL)
    {
        $query = $this->db->query("select (Counter + 1) as Counter from mst_counter where Aplikasi = 'BPP' and Cabang = '".$cab."' limit 1")->row();
        if(empty($query[0]->Counter)){
            $query = 1000001;
        }
        return $query;
    } 
    public function getCounterBPB($cab = NULL)
    {
        $query = $this->db->query("select (Counter + 1) as Counter from mst_counter where Aplikasi = 'BPB' and Cabang = '".$cab."' limit 1")->row();

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
        $cekOpen = 0;
        $totpph22 = 0;
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $expld = explode("/", $params->nousulan);
        $Tipe = "";
        //================ Running Number ======================================//
        if($expld[1] == 'JO'){
            $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$expld[2]."' limit 1")->row();
        }
        else{
            $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$expld[1]."' limit 1")->row();
        }
        $cab = $this->db->query("select Cabang from mst_cabang where Kode = '".$expld[0]."' limit 1")->row();
        $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$params->cabang."' and length(NoDokumen) > 23 and LEFT(NoDokumen,3) = 'BPP'")->result();

        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            // $lastNumber = explode("/", $data[0]->no);
            // $lastNumber = end($lastNumber) + 1;
            $lastNumber = ($data[0]->no) + 1;
        }
        $nomorbpp = 'BPP/'.$expld[0].'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;
        //================= end of running number ========================================//
        //================ Running Number ======================================//
        // $cab = $this->db->query("select Cabang from mst_cabang where Kode = '".$expld[0]."' limit 1")->row();
        // $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$expld[1]."' limit 1")->row();
        // $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$params->cabang."' and length(NoDokumen) > 23 and LEFT(NoDokumen,3) = 'BPB'")->result();
        // if(empty($data[0]->no) || $data[0]->no == ""){
        //     $lastNumber = 1000001;
        // }else {
        //     // $lastNumber = explode("/", $data[0]->no);
        //     // $lastNumber = end($lastNumber) + 1;
        //     $lastNumber = ($data[0]->no) + 1;
        // }
        // $nomorbpb = 'BPB/'.$expld[0].'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;
        //================= end of running number ========================================//
        // if($params->tipe == 'BPP'){
        //     $NoDoc = $nomorbpp;
        // }else{
        //     $NoDoc = $nomorbpb;
        // }
        $NoDoc = $nomorbpp;
            $i=0;
            foreach ($params->produk as $key => $value) 
            {
                if (!empty($params->produk[$key])) {
                        $i++;
                        $expld = explode("~", $params->produk[$key]);
                        $Produk = $expld[0];
                        $NamaProduk = $expld[1];
                        $expDD= date("Y-m-d", strtotime($params->expdate[$key]));
                        $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$Produk."' limit 1")->row(); 
                        $banyak = $params->qtyterima[$key] + $params->bonus[$key];

                        $this->db->set("Cabang", $params->cabang);
                        $this->db->set("Prinsipal", $params->prinsipal);
                        $this->db->set("Supplier", $params->supplier);
                        $this->db->set("Pabrik", $qproduk->Pabrik);
                        $this->db->set("NoUsulan", $params->nousulan);
                        $this->db->set("NoPR", $params->nopr);
                        $this->db->set("NoPO", $params->nopo);
                        $this->db->set("NoDO", '');
                        $this->db->set("Tipe", "BPP");
                        $this->db->set("NoDokumen", $NoDoc);
                        $this->db->set("kategori", $params->kategori[$key]);
                        $this->db->set("TglDokumen", date('Y-m-d'));
                        $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                        $this->db->set("Status", $params->status);
                        $this->db->set("NoSJ", $params->nosj);
                        $this->db->set("NOBEX", $params->nobex);
                        $this->db->set("NoInv", $params->noinvoice);
                        $this->db->set("noline", $i);
                        $this->db->set("Produk", $Produk);
                        $this->db->set("NamaProduk", $NamaProduk);
                        $this->db->set("Satuan", $params->satuan[$key]);
                        $this->db->set("QtyPO", $params->banyakx[$key]);
                        $this->db->set("Qty", $params->qtyterima[$key]);
                        $this->db->set("Bonus", $params->bonus[$key]);
                        $this->db->set("Banyak", $banyak);
                        $this->db->set("Disc", $params->diskon[$key]);
                        $this->db->set("HrgBeli", $params->hargabeli[$key]);
                        $this->db->set("BatchNo", strtoupper($params->batchno[$key]));
                        $this->db->set("ExpDate", $expDD);
                        $this->db->set("HPC", $params->hpc[$key]);
                        $this->db->set("HPC1", $params->hpcawal[$key]);
                        $this->db->set("Disc_Pst", $params->diskonpusat[$key]);
                        $this->db->set("Harga_Beli_Pst", $params->hargabelipusat[$key]);
                        $this->db->set("HPP", $params->hpp[$key]);
                        $this->db->set("pph22", $params->pph22pst[$key]);
                        
                        $gross = ($params->qtyterima[$key]+$params->bonus[$key])* $params->hargabeli[$key];
                        $diskon = $gross * ( $params->diskon[$key] / 100 );
                        $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
                        $value = $gross - $potongan;
                        $ppn = $value * ( 10 / 100 );
                        $total = $value + $ppn;

                        $totpph22 = $totpph22 + $params->pph22pst[$key];
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
                        $valid = $this->db->insert('trs_terima_barang_detail'); 
                    }
                // if($params->sisaPO[$key]>0){$this->db->set("Status_PO", 'Open'); $cekOpen++;}else{$this->db->set("Status_PO", 'BPP');}
                $terima = $this->db->query("select Produk,sum(Banyak) as banyak from trs_terima_barang_detail where NoPO = '".$params->nopo."' and Produk = '".$Produk."' limit 1")->row();
                $sumqty = $terima->banyak;
                if($sumqty >= $params->banyakx[$key]){
                    $this->db->set("Status_PO", 'BPP'); 
                }else{
                    $this->db->set("Status_PO", 'Open');
                    $cekOpen++;
                }

                $this->db->set("Sisa_PO", ($params->banyakx[$key]- $sumqty));
                $this->db->where("Cabang", $params->cabang);
                $this->db->where("No_PO", $params->nopo);
                $this->db->where("Produk", $Produk);
                $valid = $this->db->update('trs_po_detail');
                
            }
            if (!empty($params->do)) {
                $ID_Paket = $this->db->query("select NoIDPaket, Pelanggan from trs_delivery_order_header where NoDokumen = '".$params->do."' limit 1")->row();
                $this->db->set("NoIDPaket", $ID_Paket->NoIDPaket);
                $this->db->set("Pelanggan", $ID_Paket->Pelanggan);
            }

                $totBiaya = intval($params->biayakirim) + $totTotal;
                $this->db->set("Cabang", $params->cabang);
                $this->db->set("Prinsipal", $params->prinsipal);
                $this->db->set("Supplier", $params->supplier);
                $this->db->set("NoUsulan", $params->nousulan);
                $this->db->set("NoPR", $params->nopr);
                $this->db->set("NoPO", $params->nopo);
                $this->db->set("NoDO", '');
                $this->db->set("Tipe", "BPP");
                $this->db->set("NoDokumen", $NoDoc);
                $this->db->set("TglDokumen", date('Y-m-d'));
                $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                $this->db->set("Status", 'Open');
                $this->db->set("Attach1", $name1);
                $this->db->set("NoSJ", $params->nosj);
                $this->db->set("NoBEX", $params->nobex);
                $this->db->set("NoInv", $params->noinvoice);
                $this->db->set("Gross", $totGross);
                $this->db->set("Potongan", $totPotongan);
                $this->db->set("Value", $value);
                $this->db->set("PPN", $totPPN);
                $this->db->set("Total", $totTotal);
                $this->db->set("TotalBiaya", $totBiaya);
                $this->db->set("pph22", $totpph22);
                $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                $this->db->set("UserAdd", $this->session->userdata('username'));
                $valid = $this->db->insert('trs_terima_barang_header'); 
                if ($valid) {                        
                    $this->db->query("update mst_counter set Counter = ".$lastNumber." where Aplikasi = 'BPP' and Cabang = '".$params->cabang."'");
                }

            
            if (!empty($params->do)) {
                $this->db->set("Status", 'Closed');
                $this->db->where("NoDokumen", $params->do);
                $valid = $this->db->update('trs_delivery_order_detail');

                $this->db->set("Status", 'Closed');
                $this->db->set("TimeEdit", date('Y-m-d H:i:s'));            
                $this->db->set("UserEdit", $this->session->userdata('username'));
                $this->db->where("NoDokumen", $params->do);
                $valid = $this->db->update('trs_delivery_order_header');
            }
            else{
                $this->db->set("Modified_Time", date('Y-m-d H:i:s'));            
                $this->db->set("User_PO", $this->session->userdata('username'));
                $this->db->where("No_PO", $params->nopo);
                $valid = $this->db->update('trs_po_detail');

                $this->db->set("modified_time", date('Y-m-d H:i:s'));            
                $this->db->set("modified_user", $this->session->userdata('username'));
                // $this->db->set("Status_PO", 'Closed');
                if($cekOpen>0){$this->db->set("Status_PO", 'Open');}else{$this->db->set("Status_PO", 'BPP');}
                $this->db->where("No_PO", $params->nopo);
                $valid = $this->db->update('trs_po_header');
            }

            return $NoDoc;
        // } JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
    }

    public function setStok($params = NULL, $no = NULL)
    {      

        // $prdct = $params->produk;
        $prins = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."'")->row();
        
            foreach ($params->produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $params->produk[$kunci];

                if (!empty($product1)) {
                    $split = explode("~", $product1);
                    $product = $split[0];
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product."'")->row();
                    $cogsx = 0;
                    $summary = 0;
                    $qtybyk = $params->qtyterima[$kunci] + $params->bonus[$kunci];
                    $cogsx = ($params->valuebpp[$kunci]) / ($params->banyakx[$kunci]);                        
                    $summary = $qtybyk;
                    $valuestok = $summary * $cogsx;
                    $UnitCOGS = $cogsx;
                   
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product."' and Cabang='Pusat' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();
                        $UnitStok = $invsum->UnitStok + $summary;
                        $valuestok = $UnitStok * $cogsx;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok." where KodeProduk='".$product."' and Cabang='Pusat' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                        $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$product."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', ".$UnitCOGS.", '0.000')"); 
                    }
                }
            }

            // save inventori history
            foreach ($params->produk as $key => $value) {        
                if (!empty($params->produk[$key])) {
                    $expld = explode("~", $params->produk[$key]);
                    $produk = $expld[0];
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                    $valuestok = $params->valuebpp[$key];
                    $UnitStok = $qtybyk;
                        // $this->db->query("update trs_invsum set UnitStok = ".$UnitStok." where KodeProduk='".$data->Produk."' and Cabang='".$data->Cabang."' and Gudang='Baik' limit 1");

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', 'Pusat', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$params->qtyterima[$key]."', '".$valuestok."', '".strtoupper($params->batchno[$key])."', '".$params->expdate[$key]."', 'Baik', 'BPP', '".$no."', '-')");
                }
            }

            // save inventori detail
            foreach ($params->produk as $key => $value) {      
                if (!empty($params->produk[$key])) {
                    $expld = explode("~", $params->produk[$key]);
                    $produk = $expld[0];
                    $NamaProduk = $expld[1];
                    $expDD= date("Y-m-d", strtotime($params->expdate[$key]));

                    // $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$params->cabang."' and Gudang='Baik' limit 1")->row();
                    if(empty($params->expdate[$key]))
                    {
                        $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='Pusat' and BatchNo='".strtoupper($params->batchno[$key])."' and ExpDate='0000-00-00' and Gudang='Baik' and NoDokumen='".$no."' and Tahun='".date('Y')."' limit 1");
                    }else
                    {
                        $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='Pusat' and BatchNo='".strtoupper($params->batchno[$key])."' and ExpDate='".$expDD."' and NoDokumen='".$no."' and Gudang='Baik' and Tahun='".date('Y')."' limit 1");                    
                    }
                    $dt = $invdet->row();
                    if ($invdet->num_rows() > 0) {
                        $valid = false;
                    }else{
                        // $stok = $dt->UnitStok;
                        $UnitStok =  $params->qtyterima[$key] + $params->bonus[$key];
                        $cogs = ($params->valuebpp[$key]) / ($params->banyakx[$key]);
                        $ValueStok = $cogs * $UnitStok;
                        //pusat
                        $expDD= date("Y-m-d", strtotime($params->expdate[$key]));
                        $this->db->set("Tahun", date('Y'));
                        $this->db->set("KodePrinsipal", $prins->Kode_Counter);
                        $this->db->set("NamaPrinsipal", $params->prinsipal);
                        $this->db->set("Pabrik", $prod->Pabrik);
                        $this->db->set("UnitStok", $UnitStok);
                        $this->db->set("ValueStok", $ValueStok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->set("KodeProduk", $produk);
                        $this->db->set("NamaProduk", $NamaProduk);
                        $this->db->set("Cabang", 'Pusat');
                        $this->db->set("Gudang", 'Baik');
                        $this->db->set("NoDokumen", $no);
                        $this->db->set("TanggalDokumen", date('Y-m-d'));
                        $this->db->set("BatchNo", strtoupper($params->batchno[$key]));
                        $this->db->set("ExpDate", $params->expdate[$key]);
                        $this->db->set("UnitCOGS",$params->hpc[$key]);
                        $valid = $this->db->insert('trs_invdet');
                    }
                }
            }
        }

    public function createbppdaricabang($param){

        // $bpbheader = $this->db->query("select * from trs_terima_barang_cabang_header where NoDokumen ='".$NoBPB."' and Cabang = '".$cabang."'")->result(); 
        //  $bpbdetail = $this->db->query("select * from trs_terima_barang_cabang_detail where NoDokumen ='".$NoBPB."' and Cabang = '".$cabang."'")->result();
        $cabang = $param->cabang;
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $prinsipal = $param->prinsipal;
        //================ Running Number ======================================//
        $cab = $this->db->query("select Kode from mst_cabang where Cabang = '".$cabang."' limit 1")->row();
        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$prinsipal."' limit 1")->row();
        $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$cabang."' and length(NoDokumen) > 23 and LEFT(NoDokumen,3) = 'BPP'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = ($data[0]->no) + 1;
        }
        $nomorbpp = 'BPP/'.$cab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;
        
        // save data header
        $this->db->set("Cabang", $param->cabang);
        $this->db->set("Prinsipal", $param->prinsipal);
        $this->db->set("NamaPrinsipal", $param->prinsipal);
        $this->db->set("Supplier", $param->supplier);
        $this->db->set("NamaSupplier", $param->supplier);
        $this->db->set("NoUsulan", $param->nousulan);
        $this->db->set("NoPR", $param->nopr);
        $this->db->set("NoPO", $param->nopo);
        $this->db->set("Tipe", "BPB");
        $this->db->set("NoDokumen", $nomorbpp);
        $this->db->set("NoAcuDokumen", $param->bpb);
        $this->db->set("TglDokumen", date('Y-m-d'));
        $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
        $this->db->set("Status", $param->status);
        $this->db->set("Attach1", $param->Attach1[0]);
        $this->db->set("Attach2", $param->Attach2[0]);
        $this->db->set("NoSJ", $param->NoSJ[0]);
        $this->db->set("NoBEX", $param->nobex);
        $this->db->set("NoInv", $param->noinvoice);
        $this->db->set("Keterangan", $param->keterangan);
        $this->db->set("Penjelasan", $param->penjelasan[0]);
        $this->db->set("Gross", $param->total2);
        $this->db->set("Potongan", $param->potongan);
        $this->db->set("Value", $param->total2 + $param->potongan); 
        $this->db->set("PPN", $param->ppn);
        $this->db->set("Total", $param->totalvalue);
        $this->db->set("TotalBiaya", $param->totalbiaya[0]);
        $this->db->set("Counter", $lastNumber);
        $this->db->set("UserAdd", $this->session->userdata('username'));
        $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
        $this->db->set("statusPusat", $param->statusPusat[0]);
        $this->db->set("NoIDPaket", $param->NoIDPaket[0]);
        $this->db->set("Pelanggan", $param->Pelanggan[0]);
        $this->db->set("flag_closing", $param->flag_closing[0]);
        $this->db->set("NoDO", $param->nodo[0]);
        $this->db->set("flag_suratjalan", $param->flag_suratjalan[0]);
        $this->db->set("no_suratjalan", $param->no_suratjalan[0]);
        $valid = $this->db->insert('trs_terima_barang_header');
        if ($valid) {                        
            $this->db->query("update mst_counter set Counter = ".$lastNumber." where Aplikasi = 'BPP' and Cabang = '".$cabang."'");
        }

        foreach ($param->produk as $key =>$detail) {
              $this->db->set("Cabang", $param->cabang);
              $this->db->set("NoDokumen", $nomorbpp);
              $this->db->set("noline", $param->noline[$key]);
              $this->db->set("Prinsipal", $param->prinsipal);
              $this->db->set("NamaPrinsipal", $param->prinsipal);
              $this->db->set("Supplier", $param->supplier);
              $this->db->set("NamaSupplier", $param->supplier);
              $this->db->set("Pabrik", $param->pabrik[$key]);
              $this->db->set("NoUsulan", $param->nousulan);
              $this->db->set("NoPR", $param->nopr);
              $this->db->set("NoPO", $param->nopo);
              $this->db->set("NoDO", $param->nodo[$key]);
              $this->db->set("Tipe", "BPB");
              $this->db->set("NoAcuDokumen", $param->bpb);
              $this->db->set("TglDokumen", date('Y-m-d'));
              $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
              $this->db->set("Status", $param->status);
              $this->db->set("NoSJ", $param->NoSJ[0]);
              $this->db->set("NoBEX", $param->nobex);
              $this->db->set("NoInv", $param->noinvoice);
              $this->db->set("Produk", explode('~',$detail)[0]);
              $this->db->set("NamaProduk", explode('~',$detail)[1]);
              $this->db->set("Satuan", $param->satuan[$key]);
              $this->db->set("Keterangan", $param->keterangan);
              $this->db->set("Penjelasan", $param->penjelasan[$key]);
              $this->db->set("QtyPO", $param->qtypesan[$key]);
              $this->db->set("Qty", $param->qtyterima[$key]);
              $this->db->set("Bonus", $param->bonus[$key]);
              $this->db->set("Banyak", $param->banyak[$key]);
              $this->db->set("Disc", $param->diskon[$key]);
              // $this->db->set("DiscT", $param->discT);
              $this->db->set("HrgBeli", $param->hargabeli[$key]);
              $this->db->set("BatchNo", $param->batchno[$key]);
              $this->db->set("ExpDate", $param->expdate[$key]);
              $this->db->set("HPC", $param->hpc[$key]);
              $this->db->set("HPC1", $param->hpcawal[$key]);
              $this->db->set("Gross", $param->gross[$key]);
              $this->db->set("Potongan", $param->potongan[$key]);
              $this->db->set("Value", $param->gross[$key] - $param->potongan[$key]);
              $this->db->set("PPN", $param->ppncabang[$key]);
              $this->db->set("Total", $param->valuebpb[$key]);
              $this->db->set("Disc_Pst", $param->diskonpusat[$key]);
              $this->db->set("Harga_Beli_Pst", $param->hargabelipusat[$key]);
              $this->db->set("HPP", $param->hpp[$key]);
              $this->db->set("Counter", $lastNumber);
              $this->db->set("UserAdd", $this->session->userdata('username'));
              $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
              // $this->db->set("QtyAwal", $param->qtyawal[$key]);
              // $this->db->set("BonusAwal", $param->bonusawal[$key]);
              // $this->db->set("HrgBeliAwal", $param->hrgbeliawal);
              // $this->db->set("DiscAwal", $param->discawal);
              // $this->db->set("DiscTAwal", $param->disctawal);
              // $this->db->set("GrossAwal", $param->grossawal);
              // $this->db->set("PotonganAwal", $param->potonganawal);
              // $this->db->set("ValueAwal", $param->valueawal);
              // $this->db->set("PPNAwal", $param->ppnawal);
              // $this->db->set("TotalAwal", $param->totalawal);
              $this->db->set("flag_closing", $param->flag_closing[$key]);
              // $this->db->set("kategori", $param->kategori);   
              $valid = $this->db->insert('trs_terima_barang_detail');  
        }
        

        //=== buat DO bro ==========================

        $bppheader = $this->db->query("select * from trs_terima_barang_header where NoDokumen ='".$nomorbpp."' and Cabang = '".$cabang."'")->result(); 
         $bppdetail = $this->db->query("select * from trs_terima_barang_detail where NoDokumen ='".$nomorbpp."' and Cabang = '".$cabang."'")->result();

                //================ Running Number ======================================//
        $dataDO = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_delivery_order_header where Cabang = '".$cabang."' and length(NoDokumen) > 23")->result();
        if(empty($dataDO[0]->no) || $dataDO[0]->no == ""){
            $lastNumberDO = 1000001;
        }else {
            $lastNumberDO = ($dataDO[0]->no) + 1;
        }
        $nomordo = 'DO/'.$cab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumberDO;
        //================= end of running number ========================================//
        $i=0;
        $totGross = 0;
        $totPotongan = 0;
        $totValue = 0;
        $totPPN = 0;
        $totTotal = 0;
            foreach ($bppdetail as $detail) 
            {
                    $i++;
                    $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$detail->Produk."' limit 1")->row(); 
                    $this->db->set("Cabang", $detail->Cabang);
                    $this->db->set("Prinsipal", $detail->Prinsipal);
                    $this->db->set("Supplier", $detail->Supplier);
                    $this->db->set("Pabrik", $qproduk->Pabrik);
                    $this->db->set("NoUsulan", $detail->NoUsulan);
                    $this->db->set("NoPR", $detail->NoPR);
                    $this->db->set("NoPO", $detail->NoPO);
                    $this->db->set("NoBPB", $detail->NoAcuDokumen);
                    $this->db->set("Tipe", "BPB");
                    $this->db->set("NoDokumen", $nomordo);
                    $this->db->set("NoAcuDokumen", $detail->NoDokumen);
                    $this->db->set("TglDokumen", date('Y-m-d'));
                    $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("Status", 'Closed');
                    $this->db->set("NoSJ", $detail->NoSJ);
                    $this->db->set("NOBEX", $detail->NoBEX);
                    $this->db->set("NoInv", $detail->NoInv);
                    $this->db->set("noline", $detail->noline);
                    $this->db->set("Produk", $detail->Produk);
                    $this->db->set("NamaProduk", $detail->NamaProduk);
                    $this->db->set("Satuan", $detail->Satuan);
                    $this->db->set("QtyPO", $detail->QtyPO);
                    $this->db->set("Qty", $detail->Qty);
                    $this->db->set("Bonus", $detail->Bonus);
                    $this->db->set("Banyak", $detail->Banyak);
                    $this->db->set("Disc", $detail->Disc);
                    $this->db->set("HrgBeli", $detail->HrgBeli);
                    $this->db->set("BatchNo", $detail->BatchNo);
                    $this->db->set("ExpDate", $detail->ExpDate);
                    $this->db->set("HPC", $detail->HPC);
                    $this->db->set("HPC1", $detail->HPC1);
                    $this->db->set("Disc_Pst", $detail->Disc_Pst);
                    $this->db->set("Harga_Beli_Pst", $detail->Harga_Beli_Pst);
                    $this->db->set("HPP", $detail->HPP);
                    
                    if($detail->HrgBeli>0){
                        $gross = ($detail->Qty + $detail->Bonus) * $detail->HrgBeli;
                        $diskon = $gross * ( $detail->Disc / 100 );
                        $potongan = ( $detail->Bonus * $detail->HrgBeli ) + $diskon;
                    }else{
                        $gross = ($detail->Qty + $detail->Bonus)  * $detail->HPC;
                        $diskon = $gross * ( $detail->Disc / 100 );
                        $potongan = ( $detail->Bonus * $detail->HPC ) + $diskon;
                    }
                    $value = $gross - $potongan;
                    $ppn = $value * ( 10 / 100 );
                    $total = $value + $ppn;

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
                    $valid = $this->db->insert('trs_delivery_order_detail'); 
            }
            foreach ($bppheader as $header) {
                if ($header->Tipe == "BPB") {
                    $ID_Paket = $this->db->query("select NoIDPaket, Pelanggan from trs_terima_barang_header where NoDokumen = '".$nomorbpp."' limit 1")->row();
                    $this->db->set("NoIDPaket", $ID_Paket->NoIDPaket);
                    $this->db->set("Pelanggan", $ID_Paket->Pelanggan);
                }
                // $totBiaya = intval($header->biayakirim) + $totTotal;
                $this->db->set("Cabang", $header->Cabang);
                $this->db->set("Prinsipal", $header->Prinsipal);
                $this->db->set("Supplier", $header->Supplier);
                $this->db->set("NoUsulan", $header->NoUsulan);
                $this->db->set("NoPR", $header->NoPR);
                $this->db->set("NoPO", $header->NoPO);
                $this->db->set("NoBPB", $header->NoAcuDokumen);
                $this->db->set("Tipe", $header->Tipe);
                $this->db->set("NoDokumen", $nomordo);
                $this->db->set("NoAcuDokumen", $header->NoDokumen);
                $this->db->set("TglDokumen", date('Y-m-d'));
                $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                $this->db->set("Status", 'Open');
                $this->db->set("Attach1", $header->Attach1);
                $this->db->set("NoSJ", $header->NoSJ);
                $this->db->set("NoBEX", $header->NoBEX);
                $this->db->set("NoInv", $header->NoInv);
                $this->db->set("Gross", $totGross);
                $this->db->set("Potongan", $totPotongan);
                $this->db->set("Value", $value);
                $this->db->set("PPN", $totPPN);
                // $this->db->set("BiayaKirim", $header->biayakirim);
                $this->db->set("Total", $totTotal);
                $this->db->set("TotalBiaya", $header->TotalBiaya);
                $this->db->set("no_suratjalan", $header->NoSJ);
                $this->db->set("flag_suratjalan", 'N');
                $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                $this->db->set("UserAdd", $this->session->userdata('username'));
                $valid = $this->db->insert('trs_delivery_order_header'); 

                if ($valid) {                        
                    $this->db->query("update mst_counter set Counter = ".$lastNumber." where Aplikasi = 'DO' and Cabang = '".$header->Cabang."'");
                }
            }

            $this->db->set("Status", 'Closed');
            $this->db->where("NoDokumen", $nomorbpp);
            $valid = $this->db->update("trs_terima_barang_header");

            $this->db->set("Status", 'Closed');
            $this->db->where("NoDokumen", $bppheader[0]->NoAcuDokumen);
            $valid = $this->db->update("trs_terima_barang_cabang_header");

            $this->db->set("Status", 'Closed');
            $this->db->set("TimeEdit", date('Y-m-d H:i:s'));            
            $this->db->set("UserEdit", $this->session->userdata('username'));
            $this->db->where("NoDokumen", $nomorbpp);
            $valid = $this->db->update("trs_terima_barang_detail");

            $this->db->set("Status", 'Closed');
            $this->db->set("TimeEdit", date('Y-m-d H:i:s'));            
            $this->db->set("UserEdit", $this->session->userdata('username'));
            $this->db->where("NoDokumen", $bppheader[0]->NoAcuDokumen);
            $valid = $this->db->update("trs_terima_barang_cabang_detail");
        // $this->db->set("flag_suratjalan", 'N'); 
        // $this->db->where("No_PO", $NoPO); 
        // $this->db->where("No_PR", $NoPR);
        // $this->db->where("Cabang", $cabang);
        // $this->db->where("Status_PO !=", "Closed");
        // $valid = $this->db->update('trs_po_header'); 

        return $valid;
    }

    // }
}