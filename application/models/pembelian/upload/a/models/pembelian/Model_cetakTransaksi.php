<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_cetakTransaksi extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code    
            $this->cabang = $this->session->userdata('cabang');  
    }

    public function printDataUsulan($no = NULL)
    {
        $query = $this->db->query("
                                select  trs_usulan_beli_header.Cabang,
                                        trs_usulan_beli_header.Prinsipal,
                                        trs_usulan_beli_header.Supplier,
                                        trs_usulan_beli_header.No_Usulan,
                                        date(trs_usulan_beli_header.added_time) as 'added_time',
                                        trs_usulan_beli_header.Value_Usulan as 'value_usulan_header',
                                        trs_usulan_beli_header.Status_Usulan,
                                        (case when trs_usulan_beli_header.statusPusat = 'Gagal' then 'Gagal' else 'Berhasil' end) as 'statusPusat',
                                        trs_usulan_beli_detail.Produk,
                                        trs_usulan_beli_detail.Nama_Produk,
                                        trs_usulan_beli_detail.Satuan,
                                        trs_usulan_beli_detail.Qty,
                                        trs_usulan_beli_detail.Disc_Cab,
                                        trs_usulan_beli_detail.Disc_Deal,
                                        trs_usulan_beli_detail.Bonus,
                                        trs_usulan_beli_detail.Harga_Beli_Cab,
                                        trs_usulan_beli_detail.Harga_Deal,
                                        trs_usulan_beli_detail.Value_Usulan
                                from  trs_usulan_beli_header join trs_usulan_beli_detail
                                on    trs_usulan_beli_header.Cabang = trs_usulan_beli_detail.cabang 
                                      and trs_usulan_beli_header.No_Usulan = trs_usulan_beli_detail.No_Usulan 
                                where  trs_usulan_beli_header.No_Usulan ='".$no."'")->result();
        return $query;
    }  

     public function printDataPR($no = NULL)
    {
        $query = $this->db->query("
                                SELECT trs_pembelian_header.No_PR,
       trs_pembelian_header.No_Usulan,
       trs_pembelian_header.Tgl_PR,
       trs_pembelian_header.NoIDPaket,
       trs_pembelian_header.Tipe,
       trs_pembelian_header.Cabang,
       trs_pembelian_header.Prinsipal,
       trs_pembelian_header.Supplier,
       trs_pembelian_header.Total_PR,
       trs_pembelian_header.Status_PR,
       trs_pembelian_detail.Kategori,
       trs_pembelian_detail.Produk,
       trs_pembelian_detail.Nama_Produk,
       trs_pembelian_detail.Qty_PR,
       trs_pembelian_detail.Satuan,
       trs_pembelian_detail.Disc_Cab,
       trs_pembelian_detail.Harga_Beli_Cab,
       trs_pembelian_detail.Keterangan,
       mst_cabang.Alamat,
       mst_cabang.Kota,
       mst_cabang.Bank_Acc,
       mst_cabang.User,
       mst_cabang.Ijin_PBF,
       mst_cabang.NPWP,
       mst_cabang.Nama_APJ,
       mst_cabang.Alamat_APJ,
       mst_cabang.SIKA_APJ,
       mst_cabang.TTD_APJ,
       mst_cabang.Apoteker_Faktur,
       mst_cabang.SIKA_Faktur,
       mst_cabang.gudang_kirim,
       mst_cabang.alamat_gudang_kirim,
       mst_produk.Supplier2 AS 'Supplier_2',
       mst_produk.bentuk,
       mst_produk.Kandungan AS 'Zat',
       mst_prinsipal.pic_user,
      (CASE WHEN trs_pembelian_header.Prinsipal IN ('ANDALAN','SUTRA FIESTA') THEN 2 ELSE 1 END) AS total_supplier
FROM   trs_pembelian_header LEFT JOIN trs_pembelian_detail ON 
       trs_pembelian_header.No_PR = trs_pembelian_detail.No_PR
LEFT JOIN mst_cabang ON 
       trs_pembelian_header.Cabang = mst_cabang.Cabang 
LEFT JOIN mst_produk ON
       trs_pembelian_detail.Produk = mst_produk.kode_produk
LEFT JOIN mst_prinsipal ON
       trs_pembelian_header.Prinsipal = mst_prinsipal.Prinsipal
WHERE  trs_pembelian_header.No_PR = '".$no."'

UNION ALL

SELECT trs_pembelian_pusat_header.NoDokumen AS 'No_PR',
       trs_pembelian_pusat_header.NoUsulan AS 'No_Usulan',
       trs_pembelian_pusat_header.TglDokumen AS 'Tgl_PR',
       trs_pembelian_pusat_header.NoIDPaket,
       trs_pembelian_pusat_header.Tipe,
       trs_pembelian_pusat_header.Cabang,
       trs_pembelian_pusat_header.Prinsipal,
       trs_pembelian_pusat_header.Supplier,
       trs_pembelian_pusat_header.Total AS 'Total_PR',
       trs_pembelian_pusat_header.Status AS 'Status_PR',
       trs_pembelian_pusat_detail.Kategori,
       trs_pembelian_pusat_detail.Produk,
       trs_pembelian_pusat_detail.NamaProduk AS 'Nama_Produk',
       trs_pembelian_pusat_detail.Banyak AS 'Qty_PR',
       trs_pembelian_pusat_detail.Satuan,
       trs_pembelian_pusat_detail.Disc_Cab,
       trs_pembelian_pusat_detail.Harga_Beli_Cab,
       trs_pembelian_pusat_detail.Keterangan,
       mst_cabang.Alamat,
       mst_cabang.Kota,
       mst_cabang.Bank_Acc,
       mst_cabang.User,
       mst_cabang.Ijin_PBF,
       mst_cabang.NPWP,
       mst_cabang.Nama_APJ,
       mst_cabang.Alamat_APJ,
       mst_cabang.SIKA_APJ,
       mst_cabang.TTD_APJ,
       mst_cabang.Apoteker_Faktur,
       mst_cabang.SIKA_Faktur,
       mst_cabang.gudang_kirim,
       mst_cabang.alamat_gudang_kirim,
       mst_produk.Supplier2 AS 'Supplier_2',
       mst_produk.bentuk,
       mst_produk.Kandungan AS 'Zat',
       mst_prinsipal.pic_user,
      (CASE WHEN trs_pembelian_pusat_header.Prinsipal IN ('ANDALAN','SUTRA FIESTA') THEN 2 ELSE 1 END) AS total_supplier
FROM   trs_pembelian_pusat_header LEFT JOIN trs_pembelian_pusat_detail ON 
       trs_pembelian_pusat_header.NoDokumen = trs_pembelian_pusat_detail.NoDokumen
LEFT JOIN mst_cabang ON 
       trs_pembelian_pusat_header.Cabang = mst_cabang.Cabang 
LEFT JOIN mst_produk ON
       trs_pembelian_pusat_detail.Produk = mst_produk.kode_produk
LEFT JOIN mst_prinsipal ON
       trs_pembelian_pusat_header.Prinsipal = mst_prinsipal.Prinsipal
WHERE  trs_pembelian_pusat_header.NoDokumen = '".$no."'")->result();
        $prinsipal = $query[0]->Prinsipal;
        $tipe = $query[0]->Tipe;
        if($tipe == 'Rutin'){
          if($prinsipal == "MERSI"){
             $xhtml = 'cetak_pr_rutin_mersi.php';
          }else{
             $xhtml = 'cetak_pr_rutin_reguler.php';
          }
        }elseif($tipe == 'Precursor'){
            $xhtml = 'cetak_pr_precursor.php';
        }elseif($tipe == 'Psikotropika'){
            $xhtml = 'cetak_pr_psikotropika.php';
        }elseif($tipe == 'OOT'){
            $xhtml = 'cetak_pr_oot.php';
        }
        $arr_query = [
                      'xhtml' => $xhtml,
                      'query' => $query
                    ];
        return $arr_query;
    }  
    public function printDataPO($no = NULL)
    {
        $query = $this->db->query("
                                select trs_po_header.No_PO,
                                       trs_po_header.No_PR,
                                       trs_pembelian_header.Tgl_PR,
                                       trs_po_header.NoIDPaket,
                                       trs_po_header.Tgl_PO,
                                       trs_po_header.Tipe,
                                       trs_po_header.Cabang,
                                       trs_po_header.Prinsipal,
                                       trs_po_header.Supplier,
                                       trs_po_header.Total_PO,
                                       trs_po_header.Status_PO,
                                       trs_po_header.Akhir_Kontrak,
                                       trs_po_detail.Kategori,
                                       trs_po_detail.Produk,
                                       trs_po_detail.Nama_Produk,
                                       trs_po_detail.Qty as 'Qty_PR',
                                       trs_po_detail.Qty_PO,
                                       trs_po_detail.Keterangan,
                                       trs_po_detail.Satuan,
                                       mst_cabang.Alamat,
                                       mst_cabang.Kota,
                                       mst_cabang.Bank_Acc,
                                       mst_cabang.User,
                                       mst_cabang.Ijin_PBF,
                                       mst_cabang.NPWP,
                                       mst_cabang.Nama_APJ,
                                       mst_cabang.Alamat_APJ,
                                       mst_cabang.SIKA_APJ,
                                       mst_cabang.TTD_APJ,
                                       mst_cabang.Apoteker_Faktur,
                                       mst_cabang.SIKA_Faktur,
                                       mst_cabang.gudang_kirim,
                                       mst_cabang.alamat_gudang_kirim,
                                       mst_produk.Supplier2 as 'Supplier_2',
                                       mst_produk.bentuk,
                                       '' as 'Zat',
                                       mst_prinsipal.pic_user,
                                       (case when trs_po_header.Prinsipal IN ('ANDALAN','SUTRA FIESTA') and ifnull(mst_produk.Supplier2,'') <> '' then 2 else 1 end) as total_supplier
                                from   trs_po_header left join trs_po_detail on 
                                       trs_po_header.No_PO = trs_po_detail.No_PO
                                       left join trs_pembelian_header on
                                       trs_po_header.No_PR = trs_pembelian_header.No_PR
                                       left join mst_cabang on 
                                       trs_po_header.Cabang = mst_cabang.Cabang 
                                       left join mst_produk on
                                       trs_po_detail.Produk = mst_produk.kode_produk
                                       left join mst_prinsipal on
                                       trs_po_header.Prinsipal = mst_prinsipal.Prinsipal
                                where  trs_po_header.No_PO = '".$no."'")->result();
        $prinsipal = $query[0]->Prinsipal;
        $tipe = $query[0]->Tipe;
        if($tipe == 'Rutin'){
          if($prinsipal == "MERSI"){
             $xhtml = 'cetak_rutin_mersi.php';
          }else{
             $xhtml = 'cetak_rutin_reguler.php';
          }
        }elseif($tipe == 'Precursor'){
            $xhtml = 'cetak_precursor.php';
        }elseif($tipe == 'Psikotropika'){
            $xhtml = 'cetak_psikotropika.php';
        }elseif($tipe == 'OOT'){
            $xhtml = 'cetak_oot.php';
        }
        $arr_query = [
                      'xhtml' => $xhtml,
                      'query' => $query
                    ];
        return $arr_query;
    }  

    public function printDataBPB($no = NULL)
    {
        $query = $this->db->query("
                                select trs_terima_barang_header.NoDokumen,
                                       trs_terima_barang_header.NoPO,
                                       trs_terima_barang_header.NoPR,
                                       trs_terima_barang_header.Cabang,
                                       trs_terima_barang_header.Prinsipal,
                                       trs_terima_barang_header.Supplier,
                                       trs_terima_barang_header.TglDokumen,
                                       trs_terima_barang_header.NoSJ,
                                       trs_terima_barang_header.NoInv,
                                       trs_terima_barang_header.NoBEX,
                                       trs_terima_barang_header.Status,
                                       trs_terima_barang_header.statusPusat,
                                       trs_terima_barang_header.total as 'total_header',
                                       trs_terima_barang_detail.Produk,
                                       trs_terima_barang_detail.NamaProduk,
                                       trs_terima_barang_detail.Qty,
                                       trs_terima_barang_detail.Bonus,
                                       trs_terima_barang_detail.Satuan,
                                       trs_terima_barang_detail.BatchNo,
                                       trs_terima_barang_detail.Disc,
                                       trs_terima_barang_detail.ExpDate,
                                       trs_terima_barang_detail.Disc_Pst,
                                       trs_terima_barang_detail.Harga_Beli_Pst,
                                       trs_terima_barang_detail.HrgBeli,
                                       trs_terima_barang_detail.HPP,
                                       trs_terima_barang_detail.HPC,
                                       trs_terima_barang_detail.Gross,
                                       trs_terima_barang_detail.Potongan,
                                       trs_terima_barang_detail.PPN,
                                       trs_terima_barang_detail.Value,
                                       trs_terima_barang_detail.Total as 'total_detail',
                                       b.Alamat
                                from   trs_terima_barang_header left join 
                                       trs_terima_barang_detail on 
                                       trs_terima_barang_header.NoDokumen = trs_terima_barang_detail.NoDokumen
                                       join mst_cabang b on b.Cabang = '".$this->cabang."'
                                where  trs_terima_barang_header.NoDokumen = '".$no."'")->result();
        return $query;
    }

    public function printDataSO($no = NULL)
    {
        $query = $this->db->query("
                                select trs_sales_order.Cabang,
                                       trs_sales_order.NoSo,
                                       trs_sales_order.TglSo,
                                       trs_sales_order.Pelanggan,
                                       trs_sales_order.NamaPelanggan,
                                       trs_sales_order.AlamatPelanggan,
                                       trs_sales_order.TipePelanggan,
                                       trs_sales_order.NPWPPelanggan,
                                       trs_sales_order.CaraBayar,
                                       trs_sales_order.TOP,
                                       trs_sales_order.Status,
                                       trs_sales_order.statusPusat,
                                       trs_sales_order.TipeFaktur,
                                       trs_sales_order.Gross,
                                       trs_sales_order.Value,
                                       trs_sales_order.Potongan,
                                       trs_sales_order.Ppn,
                                       trs_sales_order.Materai,
                                       trs_sales_order.OngkosKirim,
                                       trs_sales_order.Total,
                                       trs_sales_order_detail.KodeProduk,
                                       trs_sales_order_detail.NamaProduk,
                                       trs_sales_order_detail.UOM,
                                       trs_sales_order_detail.QtySO,
                                       trs_sales_order_detail.Harga,
                                       trs_sales_order_detail.Bonus,
                                       trs_sales_order_detail.ValueBonus,
                                       trs_sales_order_detail.DiscCab,
                                       trs_sales_order_detail.ValueDiscCab,
                                       trs_sales_order_detail.DiscPrins1,
                                       trs_sales_order_detail.ValueDiscPrins1,
                                       trs_sales_order_detail.DiscPrins2,
                                       trs_sales_order_detail.ValueDiscPrins2,
                                       trs_sales_order_detail.Gross as 'Gross_detail',
                                       trs_sales_order_detail.Value as 'Value_detail',
                                       trs_sales_order_detail.Potongan as 'Potongan_detail',
                                       trs_sales_order_detail.Ppn as 'Ppn_detail',
                                       trs_sales_order_detail.Total as 'Total_detail'
                                  from   trs_sales_order left join trs_sales_order_detail on 
                                       trs_sales_order.NoSo = trs_sales_order_detail.NoSo
                                  where  trs_sales_order.NoSo = '".$no."'")->result();
        return $query;
    }

    public function printDataDO($no = NULL)
    {
        // $array = join("','",$no
      // where  trs_delivery_order_sales.NoDO in ('$array')")->result();
      
        $header = $this->db->query("
                                select trs_delivery_order_sales.Cabang,
                                       trs_delivery_order_sales.TimeDO,
                                       trs_delivery_order_sales.NoDO,
                                       trs_delivery_order_sales.TglDO,
                                       trs_delivery_order_sales.TipeDokumen,
                                       trs_delivery_order_sales.status_approve,
                                       trs_delivery_order_sales.Acu,
                                       trs_delivery_order_sales.Rayon,
                                       trs_delivery_order_sales.NoSO,
                                       trs_delivery_order_sales.CashDiskon,
                                       trs_delivery_order_sales.ValueCashDiskon,
                                       trs_delivery_order_sales.Pengirim,
                                       trs_delivery_order_sales.NamaPengirim,
                                       trs_delivery_order_sales.Pelanggan,
                                       trs_delivery_order_sales.NamaPelanggan,
                                       trs_delivery_order_sales.AlamatKirim,
                                       trs_delivery_order_sales.TipePelanggan,
                                       trs_delivery_order_sales.NPWPPelanggan,
                                       trs_delivery_order_sales.CaraBayar,
                                       trs_delivery_order_sales.TOP,
                                       trs_delivery_order_sales.TglJtoOrder,
                                       trs_delivery_order_sales.Status,
                                       trs_delivery_order_sales.statusPusat,
                                       trs_delivery_order_sales.TipeFaktur,
                                       trs_delivery_order_sales.Gross,
                                       trs_delivery_order_sales.Value,
                                       trs_delivery_order_sales.Potongan,
                                       trs_delivery_order_sales.Ppn,
                                       trs_delivery_order_sales.Materai,
                                       trs_delivery_order_sales.OngkosKirim,
                                       trs_delivery_order_sales.Salesman,
                                       trs_delivery_order_sales.NamaSalesman,
                                       trs_delivery_order_sales.Total,
                                       mst_cabang.NPWP,
                                       mst_cabang.Ijin_PBF,
                                       mst_cabang.Ijin_Alkes,
                                       mst_cabang.Alamat,
                                       mst_cabang.Nama_APJ,
                                       mst_cabang.SIKA_Faktur,
                                       mst_cabang.Bank_Acc
                                  from   trs_delivery_order_sales 
                                       left join mst_cabang on mst_cabang.Cabang = trs_delivery_order_sales.Cabang
                                  where  trs_delivery_order_sales.NoDO = '".$no."'")->result();

        $detail = $this->db->query("select trs_delivery_order_sales_detail.NoDO,
                                       trs_delivery_order_sales_detail.KodeProduk,
                                       trs_delivery_order_sales_detail.NamaProduk,
                                       trs_delivery_order_sales_detail.UOM,
                                       trs_delivery_order_sales_detail.BatchNo,
                                       trs_delivery_order_sales_detail.ExpDate,
                                       trs_delivery_order_sales_detail.QtyDO,
                                       trs_delivery_order_sales_detail.BonusDO,
                                       trs_delivery_order_sales_detail.ValueBonus,
                                       trs_delivery_order_sales_detail.Harga,
                                       trs_delivery_order_sales_detail.DiscCab,
                                       trs_delivery_order_sales_detail.ValueDiscCab,
                                       trs_delivery_order_sales_detail.DiscPrins1,
                                       trs_delivery_order_sales_detail.ValueDiscPrins1,
                                       trs_delivery_order_sales_detail.DiscPrins2,
                                       trs_delivery_order_sales_detail.ValueDiscPrins2,
                                       trs_delivery_order_sales_detail.Gross as 'Gross_detail',
                                       trs_delivery_order_sales_detail.Value as 'Value_detail',
                                       trs_delivery_order_sales_detail.Potongan as 'Potongan_detail',
                                       trs_delivery_order_sales_detail.Ppn as 'Ppn_detail',
                                       trs_delivery_order_sales_detail.Total as 'Total_detail'
                                from   trs_delivery_order_sales_detail
                                where  trs_delivery_order_sales_detail.NoDO = '".$no."'")->result();
        $query = [
          "header" => $header,
          "detail" => $detail
        ];
        return $query;
    }

    public function printDataDO_new($no = NULL)
    {
      $array = join("','",$no);
      
        $header = $this->db->query("
                                select trs_delivery_order_sales.Cabang,
                                       trs_delivery_order_sales.TimeDO,
                                       trs_delivery_order_sales.NoDO,
                                       trs_delivery_order_sales.TglDO,
                                       trs_delivery_order_sales.Acu,
                                       trs_delivery_order_sales.Rayon,
                                       trs_delivery_order_sales.NoSO,
                                       trs_delivery_order_sales.CashDiskon,
                                       trs_delivery_order_sales.ValueCashDiskon,
                                       trs_delivery_order_sales.Pengirim,
                                       trs_delivery_order_sales.NamaPengirim,
                                       trs_delivery_order_sales.Pelanggan,
                                       trs_delivery_order_sales.NamaPelanggan,
                                       trs_delivery_order_sales.AlamatKirim,
                                       trs_delivery_order_sales.TipePelanggan,
                                       trs_delivery_order_sales.NPWPPelanggan,
                                       trs_delivery_order_sales.CaraBayar,
                                       trs_delivery_order_sales.TOP,
                                       trs_delivery_order_sales.TglJtoOrder,
                                       trs_delivery_order_sales.Status,
                                       trs_delivery_order_sales.statusPusat,
                                       trs_delivery_order_sales.TipeFaktur,
                                       trs_delivery_order_sales.Gross,
                                       trs_delivery_order_sales.Value,
                                       trs_delivery_order_sales.Potongan,
                                       trs_delivery_order_sales.Ppn,
                                       trs_delivery_order_sales.Materai,
                                       trs_delivery_order_sales.OngkosKirim,
                                       trs_delivery_order_sales.Salesman,
                                       trs_delivery_order_sales.NamaSalesman,
                                       trs_delivery_order_sales.Total,
                                       mst_cabang.NPWP,
                                       mst_cabang.Ijin_PBF,
                                       mst_cabang.Ijin_Alkes,
                                       mst_cabang.Alamat,
                                       mst_cabang.Nama_APJ,
                                       mst_cabang.SIKA_APJ,
                                       mst_cabang.SIKA_Faktur,
                                       mst_cabang.Bank_Acc
                                  from   trs_delivery_order_sales 
                                       left join mst_cabang on mst_cabang.Cabang = trs_delivery_order_sales.Cabang
                                  where  trs_delivery_order_sales.NoDO IN ('".$array."')")->result();

        $detail = $this->db->query("select trs_delivery_order_sales_detail.NoDO,
                                       trs_delivery_order_sales_detail.KodeProduk,
                                       trs_delivery_order_sales_detail.NamaProduk,
                                       trs_delivery_order_sales_detail.UOM,
                                       trs_delivery_order_sales_detail.BatchNo,
                                       trs_delivery_order_sales_detail.ExpDate,
                                       trs_delivery_order_sales_detail.QtyDO,
                                       trs_delivery_order_sales_detail.BonusDO,
                                       trs_delivery_order_sales_detail.ValueBonus,
                                       trs_delivery_order_sales_detail.Harga,
                                       trs_delivery_order_sales_detail.DiscCab_onf,
                                       trs_delivery_order_sales_detail.ValueDiscCab_onf,
                                       trs_delivery_order_sales_detail.DiscCab,
                                       trs_delivery_order_sales_detail.ValueDiscCab,
                                       trs_delivery_order_sales_detail.DiscPrins1,
                                       trs_delivery_order_sales_detail.ValueDiscPrins1,
                                       trs_delivery_order_sales_detail.DiscPrins2,
                                       trs_delivery_order_sales_detail.ValueDiscPrins2,
                                       trs_delivery_order_sales_detail.Gross as 'Gross_detail',
                                       trs_delivery_order_sales_detail.Value as 'Value_detail',
                                       trs_delivery_order_sales_detail.Potongan as 'Potongan_detail',
                                       trs_delivery_order_sales_detail.Ppn as 'Ppn_detail',
                                       trs_delivery_order_sales_detail.Total as 'Total_detail'
                                from   trs_delivery_order_sales_detail
                                where  trs_delivery_order_sales_detail.NoDO IN ('".$array."')")->result();
        $query = [
          "header" => $header,
          "detail" => $detail
        ];
        return $query;
    }

    public function printDataFaktur($no = NULL,$TipeDokumen = NULL)
    {
        if($TipeDokumen == 'Faktur' || $TipeDokumen == 'Retur'){
            $query = $this->db->query("
                                select trs_faktur.Cabang,
                                       trs_faktur.NoFaktur,
                                       trs_faktur.NoPajak,
                                       trs_faktur.Acu,
                                       trs_faktur.Rayon,
                                       trs_faktur.TglFaktur,
                                       trs_faktur.TimeFaktur,
                                       trs_faktur.Pelanggan,
                                       trs_faktur.NamaPelanggan,
                                       trs_faktur.AlamatFaktur,
                                       trs_faktur.TipePelanggan,
                                       trs_faktur.NPWPPelanggan,
                                       trs_faktur.CaraBayar,
                                       trs_faktur.CashDiskon,
                                       trs_faktur.TOP,
                                       trs_faktur.TglJtoFaktur,
                                       trs_faktur.NamaSalesman,
                                       trs_faktur.Status,
                                       trs_faktur.statusPusat,
                                       trs_faktur.TipeDokumen,
                                       trs_faktur.TipeFaktur,
                                       trs_faktur.Gross,
                                       trs_faktur.Value,
                                       trs_faktur.Potongan,
                                       trs_faktur.Ppn,
                                       trs_faktur.Materai,
                                       trs_faktur.OngkosKirim,
                                       trs_faktur.Total,
                                       trs_faktur.Salesman,
                                       trs_faktur.NoDO,
                                       trs_faktur_detail.KodeProduk,
                                       trs_faktur_detail.NamaProduk,
                                       trs_faktur_detail.UOM,
                                       trs_faktur_detail.BatchNo,
                                       trs_faktur_detail.ExpDate,
                                       trs_faktur_detail.QtyFaktur,
                                       trs_faktur_detail.BonusFaktur,
                                       trs_faktur_detail.ValueBonus,
                                       trs_faktur_detail.Harga,
                                       trs_faktur_detail.DiscCab,
                                       trs_faktur_detail.ValueDiscCab,
                                       trs_faktur_detail.DiscPrins1,
                                       trs_faktur_detail.ValueDiscPrins1,
                                       trs_faktur_detail.DiscPrins2,
                                       trs_faktur_detail.ValueDiscPrins2,
                                       trs_faktur_detail.Gross as 'Gross_detail',
                                       trs_faktur_detail.Value as 'Value_detail',
                                       trs_faktur_detail.Potongan as 'Potongan_detail',
                                       trs_faktur_detail.Ppn as 'Ppn_detail',
                                       trs_faktur_detail.Total as 'Total_detail',
                                       mst_cabang.NPWP,
                                       mst_cabang.Ijin_PBF,
                                       mst_cabang.Ijin_Alkes,
                                       mst_cabang.Alamat,
                                       mst_cabang.Nama_APJ,
                                       mst_cabang.SIKA_Faktur,
                                       mst_cabang.Bank_Acc
                                  from   trs_faktur left join trs_faktur_detail on 
                                       trs_faktur.NoFaktur = trs_faktur_detail.NoFaktur
                                       left join mst_cabang on mst_cabang.Cabang = trs_faktur.Cabang
                                  where  trs_faktur.NoFaktur = '".$no."'")->result();
        }else{
          $query = $this->db->query("
                                select trs_faktur.Cabang,
                                       trs_faktur.NoFaktur,
                                       trs_faktur.TglFaktur,
                                       trs_faktur.TimeFaktur,
                                       trs_faktur.Acu,
                                       trs_faktur.Rayon,
                                       trs_faktur.Pelanggan,
                                       trs_faktur.NamaPelanggan,
                                       trs_faktur.AlamatFaktur,
                                       trs_faktur.TipePelanggan,
                                       trs_faktur.NPWPPelanggan,
                                       trs_faktur.CaraBayar,
                                       trs_faktur.CashDiskon,
                                       trs_faktur.TOP,
                                       trs_faktur.Status,
                                       trs_faktur.statusPusat,
                                       trs_faktur.TipeDokumen,
                                       trs_faktur.TipeFaktur,
                                       trs_faktur.Gross,
                                       trs_faktur.Value,
                                       trs_faktur.Potongan,
                                       trs_faktur.Total,
                                       trs_faktur.Salesman,
                                       trs_faktur_cndn.NoDokumen,
                                       trs_faktur_cndn.Faktur,
                                       trs_faktur_cndn.KodeProduk,
                                       trs_faktur_cndn.Produk,
                                       trs_faktur_cndn.DasarPerhitungan,
                                       trs_faktur_cndn.Persen,
                                       trs_faktur_cndn.Rupiah,
                                       trs_faktur_cndn.Jumlah,
                                       trs_faktur_cndn.DscCab,
                                       trs_faktur_cndn.ValueDscCab,
                                       trs_faktur_cndn.Total as 'Total_detail',mst_cabang.Alamat,
                                       mst_cabang.NPWP,
                                       mst_cabang.Ijin_PBF,
                                       mst_cabang.Ijin_Alkes,
                                       mst_cabang.Alamat,
                                       mst_cabang.Nama_APJ,
                                       mst_cabang.SIKA_Faktur,
                                       mst_cabang.Bank_Acc
                                  from   trs_faktur left join trs_faktur_cndn on 
                                       trs_faktur.NoFaktur = trs_faktur_cndn.NoDokumen
                                       left join mst_cabang on mst_cabang.Cabang = trs_faktur.Cabang
                                  where  trs_faktur.NoFaktur = '".$no."'")->result();
        }

        if($query){
          $this->db->query("update trs_faktur 
                            set counter_print = (ifnull(counter_print,0) + 1)
                            where ifnull(counter_print,0) < 1 and NoFaktur ='".$no."'");
        }
        
        return $query;
    }
    public function printDataDIH($no = NULL,$status = NULL)
    {
        $query = $this->db->query("
            SELECT distinct trs_dih.*,
              mst_pelanggan.Pelanggan as nama_pelanggan,ifnull(trs_dih.Total - trs_dih.Saldo,0) as 'Sisa',
              mst_karyawan.Nama as nama_penagih,
              IFNULL(trs_pelunasan_detail.UmurFaktur,DATEDIFF(trs_dih.TglDIH,trs_dih.TglFaktur)) as UmurFaktur,
              trs_pelunasan_detail.SaldoFaktur as SaldoFaktur,
              trs_pelunasan_detail.ValuePelunasan as ValuePelunasan,
              trs_pelunasan_detail.TipeDokumen as TipeDokumen,
              trs_pelunasan_giro_detail.Giro as Giro
            from trs_dih
            left join mst_pelanggan on mst_pelanggan.Kode = trs_dih.Pelanggan 
            left join mst_karyawan on mst_karyawan.Kode = trs_dih.Penagih
            left join trs_pelunasan_detail on trs_pelunasan_detail.NoDIH = trs_dih.NoDIH 
              and trs_pelunasan_detail.NomorFaktur = trs_dih.NoFaktur
            left join trs_pelunasan_giro_detail on trs_pelunasan_giro_detail.NoDIH = trs_dih.NoDIH 
              and trs_pelunasan_giro_detail.NomorFaktur = trs_dih.NoFaktur
            where trs_dih.NoDIH = '".$no."' and trs_dih.status = '".$status."' order by date(trs_dih.created_at),No ASC
          ")->result();
        return $query;
    }

    public function printdatamutasikoreksi($no){
      $query = $this->db->query("
                                select  trs_mutasi_koreksi.cabang,
                                        mst_cabang.Alamat,
                                        trs_mutasi_koreksi.no_koreksi,
                                        trs_mutasi_koreksi.tanggal,
                                        trs_mutasi_koreksi.catatan,
                                        trs_mutasi_koreksi.produk,
                                        trs_mutasi_koreksi.nama_produk,
                                        trs_mutasi_koreksi.qty,
                                        mst_produk.satuan,
                                        trs_mutasi_koreksi.harga,
                                        trs_mutasi_koreksi.qty * trs_mutasi_koreksi.harga as subtotal,
                                        trs_mutasi_koreksi.value,
                                        trs_mutasi_koreksi.batch,
                                        trs_mutasi_koreksi.status
                                from  trs_mutasi_koreksi 
                                left join mst_cabang on mst_cabang.Cabang = trs_mutasi_koreksi.Cabang
                                left join mst_produk on mst_produk.Kode_Produk = trs_mutasi_koreksi.produk
                                where  trs_mutasi_koreksi.no_koreksi ='".$no."'")->result();
      return $query;
    }

    public function printdatamutasibatch($no){
      $query = $this->db->query("
                                select trs_mutasi_batch.*,
                                mst_cabang.Alamat,
                                mst_produk.satuan
                                from  trs_mutasi_batch
                                left join mst_cabang on mst_cabang.Cabang = trs_mutasi_batch.cabang
                                left join mst_produk on mst_produk.Kode_Produk = trs_mutasi_batch.produk
                                where trs_mutasi_batch.NoDokumen ='".$no."'")->result();
      return $query;
    }

    public function printdatamutasigudang($no){
      $query = $this->db->query("
                                select trs_mutasi_gudang.*,
                                mst_cabang.Alamat,
                                mst_produk.satuan
                                from  trs_mutasi_gudang
                                left join mst_cabang on mst_cabang.Cabang = trs_mutasi_gudang.cabang
                                left join mst_produk on mst_produk.Kode_Produk = trs_mutasi_gudang.produk
                                where trs_mutasi_gudang.NoDokumen ='".$no."'")->result();
      return $query;
    }

    public function printdatakiriman($no){
      /*$query = $this->db->query("select trs_kiriman.*, mst_pelanggan.Pelanggan, mst_pelanggan.Alamat_Kirim from trs_kiriman
                                  left join mst_pelanggan on mst_pelanggan.Kode = trs_kiriman.KodePelanggan
                                where trs_kiriman.NoKiriman = '".$no."'")->result();*/

      $query = $this->db->query("SELECT trs_kiriman.*, b.NamaPelanggan as Pelanggan , b.AlamatKirim as Alamat_Kirim,format(b.Total,2) Total FROM trs_kiriman
                                  LEFT JOIN trs_delivery_order_sales b ON trs_kiriman.NoDO = b.NoDO
                                WHERE trs_kiriman.NoKiriman = '".$no."' order by b.NamaPelanggan")->result();
      return $query;
    }

    public function cetakrelokasikiriman($no){
      $header = $this->db->query("select a.*,ifnull(a.Biayakirim,0) as 'Biayakirimx',ifnull(a.ValueCR,0) as 'ValueCRx' from trs_relokasi_kirim_header a where (Cabang = '".$this->cabang."'  or Cabang = 'Pusat') and
                                No_Relokasi = '".$no."'")->result();

      $alamat = $this->db->query("select * from mst_cabang where Cabang in('".$header[0]->Cabang_Pengirim."','".$header[0]->Cabang_Penerima."')")->result();

      $detail = $this->db->query("select * from trs_relokasi_kirim_detail where (Cabang = '".$this->cabang."'  or Cabang = 'Pusat') and
                                No_Relokasi = '".$no."'")->result();

      $output = [
        'header' => $header,
        'detail' => $detail,
        'alamat' => $alamat
      ];
      return $output;
    }

    public function cetakrelokasiterima($no){
      $header = $this->db->query("select * from trs_relokasi_terima_header where No_Terima = '".$no."'")->result();

      $alamat = $this->db->query("select * from mst_cabang where Cabang in('".$header[0]->Cabang_Pengirim."','".$header[0]->Cabang_Penerima."')")->result();

      $detail = $this->db->query("select * from trs_relokasi_terima_detail where No_Terima = '".$no."'")->result();

      $output = [
        'header' => $header,
        'detail' => $detail,
        'alamat' => $alamat
      ];
      return $output;
    }

    public function cetak_kas($no){
      $array = join("','",$no);
      $query = $this->db->query("select * from trs_buku_transaksi where Jurnal_ID IN ('".$array."') and IFNULL(Kategori_2,'') <> '' and Cabang = '".$this->cabang."'")->result();
      return $query;
    }

    public function printdataDOBeli($no = NULL,$cabang=null)
    {
        $query = $this->db->query("
                                select trs_delivery_order_header.NoDokumen,
                                       trs_delivery_order_header.NoPO,
                                       trs_delivery_order_header.Cabang,
                                       trs_delivery_order_header.Prinsipal,
                                       trs_delivery_order_header.Supplier,
                                       trs_delivery_order_header.TglDokumen,
                                       trs_delivery_order_header.NoSJ,
                                       trs_delivery_order_header.NoInv,
                                       trs_delivery_order_header.Status,
                                       trs_delivery_order_header.PPN as 'ppn_header',
                                       trs_delivery_order_header.total as 'total_header',
                                       trs_delivery_order_detail.Produk,
                                       trs_delivery_order_detail.NamaProduk,
                                       trs_delivery_order_detail.Qty,
                                       trs_delivery_order_detail.Bonus,
                                       trs_delivery_order_detail.Satuan,
                                       trs_delivery_order_detail.BatchNo,
                                       trs_delivery_order_detail.Disc,
                                       trs_delivery_order_detail.HrgBeli,
                                       trs_delivery_order_detail.HPP,
                                       trs_delivery_order_detail.HPC,
                                       trs_delivery_order_detail.Gross,
                                       trs_delivery_order_detail.Potongan,
                                       trs_delivery_order_detail.PPN,
                                       trs_delivery_order_detail.Value,
                                       trs_delivery_order_detail.Total as 'total_detail'
                                from   trs_delivery_order_header join 
                                       trs_delivery_order_detail on 
                                       trs_delivery_order_header.NoDokumen = trs_delivery_order_detail.NoDokumen and 
                                       trs_delivery_order_header.Cabang = trs_delivery_order_detail.Cabang
                                where  trs_delivery_order_header.NoDokumen = '".$no."' and 
                                       trs_delivery_order_header.Cabang = '".$cabang."' and
                                       (trs_delivery_order_detail.Qty <> 0 or trs_delivery_order_detail.Bonus <> 0)")->result();
        return $query;
    }

    
    public function get_jto($CaraBayar = NULL)
    {
        
        $query = $this->db->get_where('mst_cara_bayar', array('Cara_Bayar' => $CaraBayar))->row();
        return $query;
    }

   
}