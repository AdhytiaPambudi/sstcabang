<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");

class Model_laporan extends CI_Model
{
    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            // $this->load->model('pembelian/Model_order');
            $this->load->model('Model_main');
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function listDataPiutangAll($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                            select trs_faktur.* from trs_faktur
                                where (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                    Status in ( 'Open','OpenDIH','Giro') and 
                                    TipeDokumen in ('Faktur','Retur','CN','DN') $byStatus $search order by TimeFaktur DESC $limit");
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            select trs_faktur.* from trs_faktur 
                                where trs_faktur.Cabang = '".$this->cabang."' and (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                    Status in ( 'Open','OpenDIH','Giro') and 
                                    TipeDokumen in ('Faktur','Retur','CN','DN') $byStatus $search order by TimeFaktur DESC $limit");     
                                }else{
            $query = $this->db->query("
                            select trs_faktur.* from trs_faktur
                                where trs_faktur.Cabang = '".$this->cabang."'  and 
                                    Status != 'Usulan' and 
                                    TipeDokumen in ('Faktur','Retur','CN','DN') $byStatus $search order by TimeFaktur DESC $limit"); 
                                }       
        }

        return $query;
    }

    public function listDataPDUDOAll($search = null, $limit = null, $status = null, $tipe = null, $bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan,AlamatFaktur, IFNULL(QtyFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar` FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') $byStatus $search $bydate order by TimeFaktur DESC $limit"
                            );
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglDO, Pelanggan, NamaPelanggan,AlamatKirim, IFNULL(QtyDO,0) AS Banyak, IFNULL(`BonusDO`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar` FROM `trs_delivery_order_sales_detail` WHERE TipeDokumen IN ('DO') AND status in ('Open','Kirim','Terima' ) AND Cabang = '".$this->cabang."' AND TglDO>='$startdate' $byStatus $search $bydate order by TimeDO DESC $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglDO, Pelanggan, NamaPelanggan,AlamatKirim IFNULL(QtyDO,0) AS Banyak, IFNULL(`BonusDO`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar` FROM `trs_delivery_order_sales_detail` WHERE TipeDokumen IN ('DO') AND status in ('Open','Kirim','Terima' ) AND Cabang = '".$this->cabang."' $byStatus $search $bydate order by TimeDO DESC $limit"
                                ); 
                }       
        }

        return $query;
    }


    public function listDataPDUAll($search = null, $limit = null, $status = null, $tipe = null, $bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Prinsipal`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, AlamatFaktur, IFNULL(QtyFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar`,BatchNo,DATE(ExpDate) AS ExpDate  FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') $byStatus $search $bydate order by TimeFaktur DESC $limit"
                            );
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Prinsipal`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, AlamatFaktur, IFNULL(QtyFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar`,BatchNo,DATE(ExpDate) AS ExpDate  FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') AND Cabang = '".$this->cabang."' AND TglFaktur>='$startdate' $byStatus $search $bydate order by TimeFaktur DESC $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT `Cabang`,`Prinsipal2`,`Prinsipal`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, AlamatFaktur, IFNULL(QtyFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar`,BatchNo,DATE(ExpDate) AS ExpDate  FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') AND Cabang = '".$this->cabang."' $byStatus $search $bydate order by TimeFaktur DESC $limit"
                                ); 
                }   
        }

        return $query;
    }

    
    public function listDataPDUFDOAll($search = null, $limit = null, $status = null, $tipe = null, $bydate = null, $bydated = null,$search1=null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            // $query = $this->db->query("
            //                 SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, IFNULL(QtyFaktur+BonusFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar` FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') $byStatus $search $bydate order by TimeFaktur DESC $limit"
            //                 );
        }else
        {

            $query = $this->db->query("
                            SELECT * FROM (
                                SELECT `Cabang`,`Prinsipal2`,`Prinsipal`,`Pabrik`,`KodeProduk`, NamaProduk, 
                                    IFNULL(NoDO,'') AS NoDO, IFNULL(NoFaktur,'') AS NoFaktur, IFNULL(Acu,'') AS Acu, 
                                    TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, 
                                    IFNULL(QtyFaktur,0) AS Banyak, 
                                    IFNULL(`BonusFaktur`,0) AS Bonus, 
                                    IFNULL(`Gross`,0.0) AS Gross, 
                                    IFNULL(`Potongan`,0.0) AS Potongan, 
                                    IFNULL(`Value`,0.0) AS Value, 
                                    IFNULL(`Ppn`,0.0) AS Ppn, 
                                    IFNULL(`Total`,0.0) AS Total, Salesman,
                                    IFNULL(DiscCab,0.0) AS DiscCab, 
                                    (case when (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) = 0.0 then IFNULL(DiscPrinsTot,0.0) else (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) end ) AS DiscPri, `CaraBayar` , TimeFaktur AS TimeDok
                                FROM `trs_faktur_detail` 
                                WHERE TipeDokumen IN ('Faktur','Retur') 
                                    AND Cabang = '".$this->cabang."' $byStatus $search $bydate 

                                UNION ALL

                                SELECT `Cabang`,`Prinsipal2`,`Prinsipal`,`Pabrik`,`KodeProduk`, NamaProduk, 
                                    IFNULL(NoDO,'') AS NoDO, IFNULL(NoFaktur,'') AS NoFaktur, IFNULL(Acu,'') AS Acu, 
                                    TipeDokumen, TglDO, Pelanggan, NamaPelanggan, 
                                    IFNULL(QtyDO,0) AS Banyak, 
                                    IFNULL(`BonusDO`,0) AS Bonus, 
                                    IFNULL(`Gross`,0.0) AS Gross, 
                                    IFNULL(`Potongan`,0.0) AS Potongan, 
                                    IFNULL(`Value`,0.0) AS Value, 
                                    IFNULL(`Ppn`,0.0) AS Ppn, 
                                    IFNULL(`Total`,0.0) AS Total, Salesman,
                                    IFNULL(DiscCab,0.0) AS DiscCab, 
                                    (case when (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) = 0.0 then IFNULL(DiscPrinsTot,0.0) else (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) end ) AS DiscPri, `CaraBayar` , TimeDO AS TimeDok
                                FROM `trs_delivery_order_sales_detail` 
                                WHERE ((STATUS IN ('Open','Kirim','Terima','Retur')) or (status ='Closed' and ifnull(status_retur,'') ='Y'))
                                    AND Cabang = '".$this->cabang."' $byStatus $search1 $bydated

                                )xx ORDER BY NoDO DESC,TimeDok ASC  $limit "
                                );     
        }

        return $query;
    }

    public function listDataBPBDetailAll($search = null, $limit = null, $status = null, $tipe = null,$bydate=null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }
        $query = $this->db->query("
                            SELECT Cabang,Prinsipal,pp.Prinsipal2,Supplier,Pabrik,Produk,NamaProduk,
                                    Satuan,Keterangan,Penjelasan,NoPR,NoPO,NoDokumen,TglDokumen,TimeDokumen,
                                    Status,NoSJ,NoBex,NoInv,Banyak,Qty,Bonus,HrgBeli,Disc,DiscT,BatchNo,ExpDate,
                                    Gross,Potongan,VALUE,PPN,Total,NoDO
                                FROM trs_terima_barang_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_terima_barang_detail.Produk 
                                WHERE Cabang = '".$this->cabang."' $bydate $search $byStatus ORDER BY  TimeDokumen DESC, NoDokumen ASC $limit"
                                ); 
        return $query;
    }
    
    public function listDataUsulanBeliDetailAll($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                        SELECT Cabang,Prinsipal,Supplier,Kategori,Produk,Nama_Produk,
                                    Satuan,Keterangan,Penjelasan,No_Usulan,Tgl_Usulan,Time_Usulan,Status_Usulan,
                                    Qty,Bonus,Harga_Beli_Cab,Harga_Deal,
                                    Disc2,Disc_Deal,
                                    ((IFNULL(Qty,0) + IFNULL(Bonus,0)) * IFNULL(Harga_Beli_Cab,0)) AS Gross,
                                    Potongan_Cab,Value_Usulan,(0.1 * Value_Usulan)AS PPN,((0.1 * Value_Usulan)+Value_Usulan) AS Total, 
                                    Qty_Rec,AVG,Indeks,Stok,GIT
                        FROM `trs_usulan_beli_detail` ORDER BY  Time_Usulan DESC, No_Usulan ASC $limit
                        ");
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT Cabang,Prinsipal,Supplier,Kategori,Produk,Nama_Produk,
                                    Satuan,Keterangan,Penjelasan,No_Usulan,Tgl_Usulan,Time_Usulan,Status_Usulan,
                                    Qty,Bonus,Harga_Beli_Cab,Harga_Deal,
                                    Disc2,Disc_Deal,
                                    ((IFNULL(Qty,0) + IFNULL(Bonus,0)) * IFNULL(Harga_Beli_Cab,0)) AS Gross,
                                    Potongan_Cab,Value_Usulan,(0.1 * Value_Usulan)AS PPN,((0.1 * Value_Usulan)+Value_Usulan) AS Total, 
                                    Qty_Rec,AVG,Indeks,Stok,GIT
                            FROM `trs_usulan_beli_detail` 
                            WHERE Cabang = '".$this->cabang."' $byStatus ORDER BY  Time_Usulan DESC, No_Usulan ASC $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT Cabang,Prinsipal,Supplier,Kategori,Produk,Nama_Produk,
                                    Satuan,Keterangan,Penjelasan,No_Usulan,Tgl_Usulan,Time_Usulan,Status_Usulan,
                                    Qty,Bonus,Harga_Beli_Cab,Harga_Deal,
                                    Disc2,Disc_Deal,
                                    ((IFNULL(Qty,0) + IFNULL(Bonus,0)) * IFNULL(Harga_Beli_Cab,0)) AS Gross,
                                    Potongan_Cab,Value_Usulan,(0.1 * Value_Usulan)AS PPN,((0.1 * Value_Usulan)+Value_Usulan) AS Total, 
                                    Qty_Rec,AVG,Indeks,Stok,GIT
                            FROM `trs_usulan_beli_detail` 
                            WHERE Cabang = '".$this->cabang."' $byStatus ORDER BY  Time_Usulan DESC, No_Usulan ASC $limit"
                                ); 
                }       
        }

        return $query;
    }




    public function listDatacndnHq($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                            SELECT Cabang,NoDokumen,Faktur,TipeDokumen, Pelanggan,NamaPelanggan,TanggalCNDN,SUM(Total) AS Total FROM `trs_faktur_cndn` WHERE TipeDokumen IN ('CN','DN') $search GROUP BY Cabang, NoDokumen, Faktur, TipeDokumen, Pelanggan, NamaPelanggan, TanggalCNDN ORDER BY TanggalCNDN DESC, NoDokumen DESC $limit
                            ");
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT Cabang,NoDokumen,Faktur,TipeDokumen,Pelanggan,NamaPelanggan,TanggalCNDN,SUM(Total) AS Total FROM `trs_faktur_cndn` WHERE TipeDokumen IN ('CN','DN') AND Cabang = '".$this->cabang."' $search GROUP BY Cabang, NoDokumen, Faktur, TipeDokumen, Pelanggan, NamaPelanggan, TanggalCNDN ORDER BY TanggalCNDN DESC, NoDokumen DESC $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT Cabang,NoDokumen,Faktur,TipeDokumen,Pelanggan,NamaPelanggan,TanggalCNDN,SUM(Total) AS Total FROM `trs_faktur_cndn` WHERE TipeDokumen IN ('CN','DN') AND Cabang = '".$this->cabang."' $search GROUP BY Cabang, NoDokumen, Faktur, TipeDokumen, Pelanggan, NamaPelanggan, TanggalCNDN ORDER BY TanggalCNDN DESC, NoDokumen DESC $limit"
                                ); 
                }       
        }

        return $query;
    }


    public function listDataPelangganM($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                            SELECT a.*,b.SaldoP FROM `mst_pelanggan` a LEFT JOIN (SELECT Cabang,Pelanggan,IFNULL(SUM(Saldo),0) AS SaldoP FROM trs_faktur GROUP BY Cabang,Pelanggan)b ON a.Cabang=b.Cabang AND a.Kode=b.Pelanggan ORDER BY b.SaldoP DESC, a.Kode $search $limit
                            ");
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT a.*,b.SaldoP FROM `mst_pelanggan` a LEFT JOIN (SELECT Cabang,Pelanggan,IFNULL(SUM(Saldo),0) AS SaldoP FROM trs_faktur WHERE Cabang = '".$this->cabang."' GROUP BY Cabang,Pelanggan)b ON a.Cabang=b.Cabang AND a.Kode=b.Pelanggan $search ORDER BY b.SaldoP DESC, a.Kode $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT a.*,b.SaldoP FROM `mst_pelanggan` a LEFT JOIN (SELECT Cabang,Pelanggan,IFNULL(SUM(Saldo),0) AS SaldoP FROM trs_faktur WHERE Cabang = '".$this->cabang."' GROUP BY Cabang,Pelanggan)b ON a.Cabang=b.Cabang AND a.Kode=b.Pelanggan $search ORDER BY b.SaldoP DESC, a.Kode $limit"
                                ); 
                }       
        }

        return $query;
    }


public function getstoksalesman($search = null, $limit = null, $tipe=null,$tgl1=null,$tgl2=null)
    {   
        // if ($tipe == "All") {
        //         $query = $this->db->query("SELECT * FROM `v_dStokSalesman` WHERE Cabang = '".$this->cabang."' $search $limit");
        // }else {
        //         $query = $this->db->query("SELECT * FROM `v_dStokSalesman` WHERE Cabang = '".$this->cabang."' AND NamaPrinsipal ='".$tipe."' $search $limit");
        // }

        if ($tipe == "All") {
                $query = $this->db->query("SELECT 
                                          trs_invsum.NamaPrinsipal, trs_invsum.KodeProduk, 
                                          trs_invsum.NamaProduk, trs_invsum.UnitStok, 
                                          CASE WHEN a.Cabang='".$this->cabang."' 
                                          THEN a.HNA 
                                          ELSE b.HNA
                                          END AS HNA
                                          
                                        FROM trs_invsum 
                                          LEFT JOIN mst_harga a ON a.Produk = trs_invsum.KodeProduk AND a.cabang='".$this->cabang."'
                                          LEFT JOIN mst_harga b ON b.Produk = trs_invsum.KodeProduk AND IFNULL(b.cabang,'')=''
                                            WHERE trs_invsum.tahun = year(curdate()) AND trs_invsum.UnitStok > 0 AND trs_invsum.Gudang = 'Baik' AND trs_invsum.Cabang = '".$this->cabang."' $search ORDER BY trs_invsum.NamaPrinsipal, trs_invsum.NamaProduk $limit");
        }else {
                $query = $this->db->query("SELECT 
                                          trs_invsum.NamaPrinsipal, trs_invsum.KodeProduk, 
                                          trs_invsum.NamaProduk, trs_invsum.UnitStok, 
                                          CASE WHEN a.Cabang='".$this->cabang."' 
                                          THEN a.HNA 
                                          ELSE b.HNA
                                          END AS HNA
                                          
                                        FROM trs_invsum 
                                          LEFT JOIN mst_harga a ON a.Produk = trs_invsum.KodeProduk AND a.cabang='".$this->cabang."'
                                          LEFT JOIN mst_harga b ON b.Produk = trs_invsum.KodeProduk AND IFNULL(b.cabang,'')=''
                                            WHERE trs_invsum.tahun = year(curdate()) AND trs_invsum.UnitStok > 0 AND trs_invsum.Gudang = 'Baik' AND trs_invsum.Cabang = '".$this->cabang."' AND trs_invsum.NamaPrinsipal ='".$tipe."' $search ORDER BY trs_invsum.NamaPrinsipal, trs_invsum.NamaProduk $limit");
        }
        // log_message('error',print_r($query->result(),true));
        return $query;
    }


public function getstokprisipal()
    {   
                // $query = $this->db->query("SELECT distinct NamaPrinsipal FROM `v_dStokSalesman` WHERE Cabang = '".$this->cabang."' order by NamaPrinsipal")->result();

                $this->db->select('Prinsipal as NamaPrinsipal');
                $this->db->order_by('NamaPrinsipal','ASC');
                $query = $this->db->get('mst_prinsipal');

        return $query->result();
    }



public function getagingpiut($search = null, $limit = null, $tipe=null, $jenis=null)
    {   

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                                Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    ");
        }else
        {
            if($tipe == "All"){
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                        ");     
            }
            elseif ($tipe == "7L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END <= 7
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "7")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 7 AND 30
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "30")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 30 AND 45
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "45")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 45 AND 60
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "60")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 60 AND 90
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "90")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 90 AND 120
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "120")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 120 AND 150
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "150L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END >= 150
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            else
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
        }

        // log_message('error',print_r($query->result(),true));
        return $query;
    }

public function getagingpiutF($search = null, $limit = null, $tipe=null, $jenis=null)
    {   

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    ");
        }else
        {
            if($tipe == "All"){
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND(trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro') 
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                        ");     
            }
            elseif ($tipe == "7L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END <= 7
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "7")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND(trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 7 AND 30
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "30")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 30 AND 45
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "45")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 45 AND 60
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "60")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 60 AND 90
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "90")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 90 AND 120
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "120")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END between 120 AND 150
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "150L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END >= 150
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            else
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
        }

        // log_message('error',print_r($query->result(),true));
        return $query;
    }

public function getagingpiutL($search = null, $limit = null, $tipe=null, $jenis=null)
    {   

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE(trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    ");
        }else
        {
            if($tipe == "All"){
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                        ");     
            }
            elseif ($tipe == "7L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) <= 7
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "7")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 7 AND 30
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "30")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 30 AND 45
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "45")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 45 AND 60
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "60")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 60 AND 90
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "90")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 90 AND 120
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            elseif ($tipe == "120")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) between 120 AND 150
                                        $search
                                        ORDER BY CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END DESC $limit
                                    "); 
            }       
            elseif ($tipe == "150L")
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') 
                                            AND DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) >= 150
                                        $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
            else
            {
            $query = $this->db->query("
                                        SELECT trs_faktur.*,
                                            DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) AS umurL,
                                            CASE WHEN trs_faktur.`Status` LIKE '%Lunas%' 
                                                THEN DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`)
                                                ELSE DATEDIFF(NOW(),trs_faktur.`TglFaktur`) END AS umurF
                                        FROM trs_faktur 
                                        WHERE trs_faktur.Cabang = '".$this->cabang."' AND (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                            Status in ( 'Open','OpenDIH','Giro')
                                            AND TipeDokumen IN ('Faktur','Retur','CN','DN') $search
                                        ORDER BY DATEDIFF(IFNULL(trs_faktur.`TglPelunasan`,NOW()),trs_faktur.`TglFaktur`) DESC $limit
                                    "); 
            }       
        }

        // log_message('error',print_r($query->result(),true));
        return $query;
    }

 public function getpudat($search = null, $limit = null, $tipe=null, $jenis=null, $kat1=null)
    {   

        if ($tipe == "All") {
            $tipeD = "";
        }elseif ($tipe == "7"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 0 AND 30)";
        }elseif ($tipe == "30"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 31 AND 45)";
        }elseif ($tipe == "45"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 46 AND 60)";
        }elseif ($tipe == "60"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 61 AND 90)";
        }elseif ($tipe == "90"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 91 AND 120)";
        }elseif ($tipe == "120"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 121 AND 150)";
        }elseif ($tipe == "150"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 151 AND 180)";
        }elseif ($tipe == "180"){
            $tipeD = "AND (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END > 180)";
        }else{
            $tipeD = "";
        }


        if (empty($jenis)) {
            $tipeDok = "";
        }elseif($jenis=='All'){
            $tipeDok = "";
        }else{
            $tipeDok = "AND TipeDokumen = '".$jenis."' ";
        }

        if(empty($kat1))
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
        }
        elseif ($kat1=='All') 
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";
        }
        elseif ($kat1=='SLunas') 
        {
            $kat1Dat = "WHERE KATEGORI2 LIKE '%LUNAS%'";                
        }elseif ($kat1=='BLunas') 
        {
            $kat1Dat = "WHERE KATEGORI2 NOT LIKE '%LUNAS%'";                
        }
        elseif ($kat1=='RPOSLunas') 
        {
            $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO JATUH TEMPO%'  )";                
        }elseif ($kat1=='RPOBLunas') 
        {
            $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO BELUM JATUH TEMPO%'  )";                
        }else
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
        }

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("SELECT * FROM (
                                    SELECT
                                          '' AS REGION,
                                          `Cabang` AS CABANG,
                                          `Cabang` AS 'AREA',
                                          `Pelanggan` AS 'INDEX',
                                          CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
                                          `TipeDokumen` AS KDDOKJDI,
                                          `NoFaktur` AS NODOKJDI,
                                          `TglFaktur` AS TGLFAK,
                                          `TOP`,
                                          `NoDO`,
                                          DATEDIFF (CURDATE(), `TglFaktur`) AS UMUR,
                                          CASE 
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 0 AND 30 THEN '0-30'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 31 AND 45 THEN '31-45'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 46 AND 60 THEN '46-60'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 61 AND 90 THEN '61-90'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 91 AND 150 THEN '91-150'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            > 150 THEN '>150'
                                            ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
                                          END AS KATEGORI,
                                          CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN
                                          CASE 
                                            WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
                                                WHEN `CaraBayar` LIKE '%RPO%' THEN 
                                                    CASE 
                                                        WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%75%' THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%90%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%120%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%150%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%180%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                    ELSE 'CEK CARA BAYAR' 
                                                    END
                                                WHEN `CaraBayar` LIKE '%Cash%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                                WHEN `CaraBayar` LIKE '%Kredit%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE IF( (ifnull(saldo,0) + ifnull(saldo_giro,0))= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
                                          END 
                                          ELSE 'LUNAS' END  
                                          AS KATEGORI2,
                                          `Salesman` AS KODESALES,
                                          NamaPelanggan AS NAMA_LANG,
                                          `AlamatFaktur` AS ALAMAT_LANG,
                                          Total AS NILDOK,
                                          (ifnull(saldo,0) + ifnull(saldo_giro,0)) AS SALDO,
                                          CASE 
                                            WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                                            WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
                                            WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
                                            WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
                                            WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
                                            ELSE 'CEK TIPE PELANGGAN'
                                            END AS 'KATEGORI3'
                                            ,
                                          '' AS JUDUL,
                                          '' AS SPESIAL,
                                          `Status` AS 'STATUS',  
                                          IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
                                           CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) !=0 
                                            THEN
                                                CASE WHEN
                                                    (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                    > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE 'LUNAS'
                                           END AS KETERANGAN_JATUH_TEMPO,
                                           alasan_jto
                                    FROM `trs_faktur` WHERE Cabang != '' AND IFNULL(Status,'') in ('Giro','Open','OpenDIH') $tipeD $tipeDok)a $kat1Dat $search  
                                    ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC $limit
                                    ");
        }else
        {
            $query = $this->db->query(" SELECT * FROM (
                                    SELECT
                                          '' AS REGION,
                                          `Cabang` AS CABANG,
                                          `Cabang` AS 'AREA',
                                          `Pelanggan` AS 'INDEX',
                                          CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
                                          `TipeDokumen` AS KDDOKJDI,
                                          `NoFaktur` AS NODOKJDI,
                                          `TglFaktur` AS TGLFAK,
                                          `TOP`,
                                          `NODO`,
                                          `nodih`,
                                          `tgldih`,
                                          `StatusInkaso`,
                                          CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END AS UMUR,
                                          CASE 
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 0 AND 30 THEN '0-30'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 31 AND 45 THEN '31-45'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 46 AND 60 THEN '46-60'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 61 AND 90 THEN '61-90'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 91 AND 150 THEN '91-150'
                                            WHEN (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            > 150 THEN '>150'
                                            ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
                                          END AS KATEGORI,
                                          CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN
                                          CASE 
                                            WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
                                                WHEN `CaraBayar` LIKE '%RPO%' THEN 
                                                    CASE 
                                                        WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%75%' THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%90%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%120%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%150%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%180%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                    ELSE 'CEK CARA BAYAR' 
                                                    END
                                                WHEN `CaraBayar` LIKE '%Cash%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                                WHEN `CaraBayar` LIKE '%Kredit%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE IF( (ifnull(saldo,0) + ifnull(saldo_giro,0))= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
                                          END 
                                          ELSE 'LUNAS' END  
                                          AS KATEGORI2,
                                          `Salesman` AS KODESALES,
                                          NamaPelanggan AS NAMA_LANG,
                                          `AlamatFaktur` AS ALAMAT_LANG,
                                          Total AS NILDOK,
                                          (ifnull(saldo,0) + ifnull(saldo_giro,0)) AS SALDO,
                                          CASE 
                                            WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                                            WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
                                            WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
                                            WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
                                            WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
                                            ELSE 'CEK TIPE PELANGGAN'
                                            END AS 'KATEGORI3'
                                            ,
                                          '' AS JUDUL,
                                          '' AS SPESIAL,
                                          `Status` AS 'STATUS',  
                                          IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
                                           CASE WHEN Saldo !=0 
                                            THEN
                                                CASE WHEN
                                                    (CASE WHEN (ifnull(saldo,0) + ifnull(saldo_giro,0)) != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                    > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE 'LUNAS'
                                           END AS KETERANGAN_JATUH_TEMPO,
                                           alasan_jto
                                    FROM `trs_faktur` WHERE Cabang = '".$this->cabang."'  AND IFNULL(Status,'') in ('Giro','Open','OpenDIH')  $tipeD $tipeDok)a $kat1Dat $search  
                                    ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC $limit
                                        ");     
        }

        // log_message('error','tipe :'.$tipe);
        // log_message('error','tipeD :'.$tipeD);
        // log_message('error','jenis :'.$jenis);
        // log_message('error','tipedok :'.$tipeDok);
        // log_message('error','kat1 :'.$kat1);
        // log_message('error','kat1Dat :'.$kat1Dat);
        return $query;
    }

// public function getpudat($search = null, $limit = null, $tipe=null, $jenis=null, $kat1=null)
//     {   

//         if ($tipe == "All") {
//             $tipeD = "";
//         }elseif ($tipe == "7"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 0 AND 30)";
//         }elseif ($tipe == "30"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 31 AND 45)";
//         }elseif ($tipe == "45"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 46 AND 60)";
//         }elseif ($tipe == "60"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 61 AND 90)";
//         }elseif ($tipe == "90"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 91 AND 120)";
//         }elseif ($tipe == "120"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 121 AND 150)";
//         }elseif ($tipe == "150"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 151 AND 180)";
//         }elseif ($tipe == "180"){
//             $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END > 180)";
//         }else{
//             $tipeD = "";
//         }


//         if (empty($jenis)) {
//             $tipeDok = "";
//         }elseif($jenis=='All'){
//             $tipeDok = "";
//         }else{
//             $tipeDok = "AND TipeDokumen = '".$jenis."' ";
//         }

//         if(empty($kat1))
//         {
//             $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
//         }
//         elseif ($kat1=='All') 
//         {
//             $kat1Dat = "WHERE KATEGORI2 != 'CEK'";
//         }
//         elseif ($kat1=='SLunas') 
//         {
//             $kat1Dat = "WHERE KATEGORI2 LIKE '%LUNAS%'";                
//         }elseif ($kat1=='BLunas') 
//         {
//             $kat1Dat = "WHERE KATEGORI2 NOT LIKE '%LUNAS%'";                
//         }
//         elseif ($kat1=='RPOSLunas') 
//         {
//             $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO JATUH TEMPO%'  )";                
//         }elseif ($kat1=='RPOBLunas') 
//         {
//             $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO BELUM JATUH TEMPO%'  )";                
//         }else
//         {
//             $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
//         }

//         if($this->cabang == "Pusat")
//         {
//             $query = $this->db->query("SELECT * FROM (
//                                     SELECT
//                                           '' AS REGION,
//                                           `Cabang` AS CABANG,
//                                           `Cabang` AS 'AREA',
//                                           `Pelanggan` AS 'INDEX',
//                                           CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
//                                           `TipeDokumen` AS KDDOKJDI,
//                                           `NoFaktur` AS NODOKJDI,
//                                           `TglFaktur` AS TGLFAK,
//                                           `TOP`,
//                                           CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END AS UMUR,
//                                           CASE 
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 0 AND 30 THEN '0-30'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 31 AND 45 THEN '31-45'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 46 AND 60 THEN '46-60'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 61 AND 90 THEN '61-90'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 91 AND 150 THEN '91-150'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             > 150 THEN '>150'
//                                             ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
//                                           END AS KATEGORI,
//                                           CASE WHEN SALDO != 0 THEN
//                                           CASE 
//                                             WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
//                                                 WHEN `CaraBayar` LIKE '%RPO%' THEN 
//                                                     CASE 
//                                                         WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
//                                                             CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%75%' THEN 
//                                                             CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%90%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%120%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%150%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%180%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
//                                                     ELSE 'CEK CARA BAYAR' 
//                                                     END
//                                                 WHEN `CaraBayar` LIKE '%Cash%' THEN 
//                                                 CASE WHEN 
//                                                         (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                         >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                                 WHEN `CaraBayar` LIKE '%Kredit%' THEN 
//                                                 CASE WHEN 
//                                                         (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                         >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                             ELSE IF( `Saldo`= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
//                                           END 
//                                           ELSE 'LUNAS' END  
//                                           AS KATEGORI2,
//                                           `Salesman` AS KODESALES,
//                                           NamaPelanggan AS NAMA_LANG,
//                                           `AlamatFaktur` AS ALAMAT_LANG,
//                                           Total AS NILDOK,
//                                           Saldo AS SALDO,
//                                           CASE 
//                                             WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
//                                             WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
//                                             WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
//                                             WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
//                                             WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
//                                             ELSE 'CEK TIPE PELANGGAN'
//                                             END AS 'KATEGORI3'
//                                             ,
//                                           '' AS JUDUL,
//                                           '' AS SPESIAL,
//                                           `Status` AS 'STATUS',  
//                                           IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
//                                            CASE WHEN Saldo !=0 
//                                             THEN
//                                                 CASE WHEN
//                                                     (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                     > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                             ELSE 'LUNAS'
//                                            END AS KETERANGAN_JATUH_TEMPO
//                                     FROM `trs_faktur` WHERE Cabang != '' AND IFNULL(Status,'') in ('Giro','Open','OpenDIH') $tipeD $tipeDok)a $kat1Dat $search  
//                                     ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC $limit
//                                     ");
//         }else
//         {
//             $query = $this->db->query(" SELECT * FROM (
//                                     SELECT
//                                           '' AS REGION,
//                                           `Cabang` AS CABANG,
//                                           `Cabang` AS 'AREA',
//                                           `Pelanggan` AS 'INDEX',
//                                           CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
//                                           `TipeDokumen` AS KDDOKJDI,
//                                           `NoFaktur` AS NODOKJDI,
//                                           `TglFaktur` AS TGLFAK,
//                                           `TOP`,
//                                           CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END AS UMUR,
//                                           CASE 
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 0 AND 30 THEN '0-30'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 31 AND 45 THEN '31-45'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 46 AND 60 THEN '46-60'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 61 AND 90 THEN '61-90'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             BETWEEN 91 AND 150 THEN '91-150'
//                                             WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
//                                             > 150 THEN '>150'
//                                             ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
//                                           END AS KATEGORI,
//                                           CASE WHEN SALDO != 0 THEN
//                                           CASE 
//                                             WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
//                                                 WHEN `CaraBayar` LIKE '%RPO%' THEN 
//                                                     CASE 
//                                                         WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
//                                                             CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%75%' THEN 
//                                                             CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%90%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%120%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%150%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
//                                                         WHEN `CaraBayar` LIKE '%180%' THEN 
//                                                         CASE WHEN 
//                                                                 (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                                 >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
//                                                     ELSE 'CEK CARA BAYAR' 
//                                                     END
//                                                 WHEN `CaraBayar` LIKE '%Cash%' THEN 
//                                                 CASE WHEN 
//                                                         (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                         >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                                 WHEN `CaraBayar` LIKE '%Kredit%' THEN 
//                                                 CASE WHEN 
//                                                         (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                         >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                             ELSE IF( `Saldo`= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
//                                           END 
//                                           ELSE 'LUNAS' END  
//                                           AS KATEGORI2,
//                                           `Salesman` AS KODESALES,
//                                           NamaPelanggan AS NAMA_LANG,
//                                           `AlamatFaktur` AS ALAMAT_LANG,
//                                           Total AS NILDOK,
//                                           Saldo AS SALDO,
//                                           CASE 
//                                             WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
//                                             WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
//                                             WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
//                                             WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
//                                             WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
//                                             ELSE 'CEK TIPE PELANGGAN'
//                                             END AS 'KATEGORI3'
//                                             ,
//                                           '' AS JUDUL,
//                                           '' AS SPESIAL,
//                                           `Status` AS 'STATUS',  
//                                           IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
//                                            CASE WHEN Saldo !=0 
//                                             THEN
//                                                 CASE WHEN
//                                                     (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
//                                                     > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
//                                             ELSE 'LUNAS'
//                                            END AS KETERANGAN_JATUH_TEMPO
//                                     FROM `trs_faktur` WHERE Cabang = '".$this->cabang."'  AND IFNULL(Status,'') in ('Giro','Open','OpenDIH')  $tipeD $tipeDok)a $kat1Dat $search  
//                                     ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC $limit
//                                         ");     
//         }

//         // log_message('error','tipe :'.$tipe);
//         // log_message('error','tipeD :'.$tipeD);
//         // log_message('error','jenis :'.$jenis);
//         // log_message('error','tipedok :'.$tipeDok);
//         // log_message('error','kat1 :'.$kat1);
//         // log_message('error','kat1Dat :'.$kat1Dat);
//         return $query;
//     }



    public function listlaporanPT($search = null, $limit = null,$bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));
        $query = $this->db->query("
                                    SELECT * FROM 
                                    (SELECT mst_cabang.Region1 AS 'WIL',
                                           trs_faktur_detail.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                                           IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                                           IFNULL(trs_faktur_detail.Acu2,'') AS 'NO_ACU2',
                                           trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           trs_faktur_detail.Prinsipal AS 'JUDUL',
                                           trs_faktur_detail.KodeProduk AS 'KODEPROD',
                                           trs_faktur_detail.NamaProduk AS 'NAMAPROD',                                           
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur'
                                                  THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                                                  ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                                           END AS 'UNIT',
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                                                  ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                                           END AS 'PRINS',
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur' 
                                                  THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                                                  ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                                           END AS 'BANYAK',
                                           IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                                           (IFNULL(trs_faktur_detail.DiscCab,0.0) + IFNULL(trs_faktur_detail.DiscCab_onf,0.0) ) AS 'PRSNXTRA',
                                           (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                           WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                           ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                                           END) AS 'PRINPXTRA',
                                           IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                                           IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                                           IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                                           IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                                           trs_faktur_detail.Salesman AS 'KODESALES',
                                           DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                                           IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                                           IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                                           trs_faktur_detail.Cabang AS 'Area',
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           ifnull(trs_faktur_detail.DiscPrins1,0.0) as 'DiscPrins1',
                                           ifnull(trs_faktur_detail.DiscPrins2,0.0) as 'DiscPrins2',
                                           trs_faktur_detail.CashDiskon,
                                           ifnull(trs_faktur_detail.DiscCabMax,0.0) as 'DiscCabMax',
                                           trs_faktur_detail.KetDiscCabMax,
                                           ifnull(trs_faktur_detail.DiscPrinsMax,0.0) as 'DiscPrinsMax',
                                           trs_faktur_detail.KetDiscPrinsMax,
                                           trs_faktur_detail.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'F' AS Tipe,
                                           NoBPB
                                    FROM trs_faktur_detail JOIN trs_faktur ON 
                                         trs_faktur.`NoFaktur` = trs_faktur_detail.`NoFaktur` AND 
                                         trs_faktur.`Cabang` = trs_faktur_detail.`Cabang`
                                         LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                    FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                    ON trs_faktur_detail.Pelanggan=mst_pelanggan.Kode
                                         LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                    ON trs_faktur_detail.Cabang=mst_cabang.Cabang
                                                    WHERE trs_faktur_detail.Cabang='".$this->cabang."' AND trs_faktur.Status NOT LIKE '%Batal%'
                                                    AND MONTH(trs_faktur.TglFaktur) IN ('".$month."') 
                                                    AND YEAR(trs_faktur.TglFaktur)='".$year."'
                                                                      
                                    UNION ALL
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           a.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           NoDokumen AS 'NODOKJDI',
                                           IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                                           IFNULL(trs_faktur.Acu2,'') AS 'NO_ACU2',
                                           a.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                                           a.KodeProduk AS 'KODEPROD',
                                           mst_produk.Produk AS 'NAMAPROD',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN'
                                                  THEN ABS(`Banyak`) *-1 
                                                  ELSE ABS(`Banyak`) 
                                           END AS 'UNIT',
                                           0 AS 'PRINS',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN' 
                                                  THEN ABS((IFNULL(Banyak,0) ))*-1 
                                                  ELSE ABS((IFNULL(Banyak,0) ))
                                           END AS 'BANYAK',
                                           0 AS 'HARGA',
                                           (IFNULL(a.DscCab,0.0)) AS 'PRSNXTRA',
                                           0 AS 'PRINPXTRA',
                                           0 AS 'TOT1',
                                           IFNULL(a.Jumlah,0) AS 'NILJU',
                                           0 AS PPN,
                                           0 AS 'COGS',
                                           trs_faktur.Salesman AS 'KODESALES',
                                           DATE(TglFaktur) AS 'TGLDOK',
                                           '' AS 'TGLEXP',
                                           '' AS 'BATCH',
                                           a.Cabang AS 'Area',
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           '' AS DiscPrins1,
                                           '' AS DiscPrins2,
                                           '' AS CashDiskon,
                                           '' AS DiscCabMax,
                                           '' AS KetDiscCabMax,
                                           '' AS DiscPrinsMax,
                                           '' AS KetDiscPrinsMax,
                                           trs_faktur.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'CD' AS Tipe,
                                           '' as NoBPB
                                    FROM trs_faktur_cndn a,mst_produk,trs_faktur
                                         LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                     FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                     ON trs_faktur.Pelanggan=mst_pelanggan.Kode
                                         LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                    ON trs_faktur.Cabang=mst_cabang.Cabang
                                                    WHERE a.Cabang='".$this->cabang."' AND a.Status IN ('CNOK','DNOK')
                                                    AND a.NoDokumen=trs_faktur.NoFaktur
                                                    AND a.KodeProduk = mst_produk.Kode_Produk
                                                    AND MONTH(TanggalCNDN)IN ('".$month."') 
                                                    AND YEAR(TanggalCNDN)='".$year."'
                                    UNION ALL
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           trs_delivery_order_sales_detail.`NoDO` AS 'NODOKJDI',
                                           IFNULL(trs_delivery_order_sales_detail.`Acu`,'') AS 'NO_ACU',
                                           IFNULL(trs_delivery_order_sales_detail.`Acu2`,'') AS 'NO_ACU2',
                                           trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           Prinsipal AS 'JUDUL',
                                           KodeProduk AS 'KODEPROD',
                                           NamaProduk AS 'NAMAPROD',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                                                  ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                                           END AS 'UNIT',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                                                  ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                                           END AS 'PRINS',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                                                  ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                                           END AS 'BANYAK',
                                           IFNULL(Harga,0) AS 'HARGA',
                                           (IFNULL(DiscCab,0.0) + IFNULL(DiscCab_onf,0.0)) AS 'PRSNXTRA',
                                           (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                            WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                            ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                            END) AS 'PRINPXTRA',
                                            IFNULL(Gross,0.0) AS 'TOT1',
                                            IFNULL(VALUE,0.0) AS 'NILJU',
                                            IFNULL(PPN,0.0) AS PPN,
                                            IFNULL(TotalCOGS,0.0) AS 'COGS',
                                            Salesman AS 'KODESALES',
                                            DATE(TglDO) AS 'TGLDOK',
                                            IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                                            IFNULL(BatchNo,'') AS 'BATCH',
                                            trs_delivery_order_sales_detail.Cabang AS 'Area',
                                            mst_pelanggan.Telp AS TELP,
                                            mst_pelanggan.Rayon_1 AS RAYON,
                                            mst_pelanggan.Tipe_2 AS PANEL,
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) as 'DiscPrins1',
                                            ifnull(trs_delivery_order_sales_detail.DiscPrins2,0.0) as 'DiscPrins2',
                                            ifnull(trs_delivery_order_sales_detail.CashDiskon,0.0) as 'CashDiskon',
                                            ifnull(trs_delivery_order_sales_detail.DiscCabMax,0.0) as 'DiscCabMax',
                                            trs_delivery_order_sales_detail.KetDiscCabMax,
                                            ifnull(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) as 'DiscPrinsMax',
                                            trs_delivery_order_sales_detail.KetDiscPrinsMax,
                                            trs_delivery_order_sales_detail.NoDO,
                                            trs_delivery_order_sales_detail.Status,
                                            'DO' as 'TipeDokumen',
                                            'D' AS Tipe,
                                            NoBPB
                                       FROM trs_delivery_order_sales_detail
                                        LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                   FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                   ON trs_delivery_order_sales_detail.Pelanggan=mst_pelanggan.Kode
                                        LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                   ON trs_delivery_order_sales_detail.Cabang=mst_cabang.Cabang
                                     WHERE trs_delivery_order_sales_detail.Cabang='".$this->cabang."' 
                                           AND ((STATUS IN ('Open','Kirim','Terima','Retur')) or (status ='Closed' and ifnull(status_retur,'') ='Y'))
                                           AND MONTH(TglDO) IN ('".$month."') 
                                           AND YEAR(TglDO)='".$year."') AS LapPT
                                    where LapPT.NM_CABANG = '".$this->cabang."' $search 
                                     ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,LapPT.NAMA_LANG,LapPT.NODOKJDI $limit;

                                    "); 
        return $query;
    }

    public function listDataPDUFDOAll2($bydate = null, $bydated = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            // $query = $this->db->query("
            //                 SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, IFNULL(NoDO,'') AS NoDO, NoFaktur, IFNULL(Acu,'') AS Acu, TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, IFNULL(QtyFaktur+BonusFaktur,0) AS Banyak, IFNULL(`BonusFaktur`,0) AS Bonus, IFNULL(`Gross`,0.0) AS Gross, IFNULL(`Potongan`,0.0) AS Potongan, IFNULL(`Value`,0.0) AS Value, IFNULL(`Ppn`,0.0) AS Ppn, IFNULL(`Total`,0.0) AS Total, Salesman,IFNULL(DiscCab,0.0) AS DiscCab, IFNULL(DiscPrins1 + DiscPrins2,0.0) AS DiscPri, `CaraBayar` FROM `trs_faktur_detail` WHERE TipeDokumen IN ('Faktur','Retur') $byStatus $search $bydate order by TimeFaktur DESC $limit"
            //                 );
        }else
        {

            $query = $this->db->query("
                            SELECT * FROM (
                                SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, 
                                    IFNULL(NoDO,'') AS NoDO, IFNULL(NoFaktur,'') AS NoFaktur, IFNULL(Acu,'') AS Acu, 
                                    TipeDokumen, TglFaktur, Pelanggan, NamaPelanggan, 
                                    IFNULL(QtyFaktur,0) AS Banyak, 
                                    IFNULL(`BonusFaktur`,0) AS Bonus, 
                                    IFNULL(`Gross`,0.0) AS Gross, 
                                    IFNULL(`Potongan`,0.0) AS Potongan, 
                                    IFNULL(`Value`,0.0) AS Value, 
                                    IFNULL(`Ppn`,0.0) AS Ppn, 
                                    IFNULL(`Total`,0.0) AS Total, Salesman,
                                    IFNULL(DiscCab,0.0) AS DiscCab, 
                                    (case when (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) = 0.0 then IFNULL(DiscPrinsTot,0.0) else (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) end ) AS DiscPri, `CaraBayar` , TimeFaktur AS TimeDok
                                FROM `trs_faktur_detail` 
                                WHERE TipeDokumen IN ('Faktur','Retur') 
                                    AND Cabang = '".$this->cabang."' $bydate 

                                UNION ALL

                                SELECT `Cabang`,`Prinsipal2`,`Pabrik`,`KodeProduk`, NamaProduk, 
                                    IFNULL(NoDO,'') AS NoDO, IFNULL(NoFaktur,'') AS NoFaktur, IFNULL(Acu,'') AS Acu, 
                                    TipeDokumen, TglDO, Pelanggan, NamaPelanggan, 
                                    IFNULL(QtyDO,0) AS Banyak, 
                                    IFNULL(`BonusDO`,0) AS Bonus, 
                                    IFNULL(`Gross`,0.0) AS Gross, 
                                    IFNULL(`Potongan`,0.0) AS Potongan, 
                                    IFNULL(`Value`,0.0) AS Value, 
                                    IFNULL(`Ppn`,0.0) AS Ppn, 
                                    IFNULL(`Total`,0.0) AS Total, Salesman,
                                    IFNULL(DiscCab,0.0) AS DiscCab, 
                                    (case when (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) = 0.0 then IFNULL(DiscPrinsTot,0.0) else (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0)) end ) AS DiscPri, `CaraBayar` , TimeDO AS TimeDok
                                FROM `trs_delivery_order_sales_detail` 
                                WHERE ((STATUS IN ('Open','Kirim','Terima','Retur')) or (status ='Closed' and ifnull(status_retur,'') ='Y'))
                                    AND Cabang = '".$this->cabang."' $bydated

                                )xx ORDER BY NoDO DESC,TimeDok ASC "
                                );     
        }
        // log_message("error",print_r($query,true));
        return $query;
    }

    public function listDataDobeliDetailAll($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("
                                SELECT Cabang,Prinsipal,pp.Prinsipal2,Supplier,Pabrik,Produk,NamaProduk,
                                    Satuan,Keterangan,Penjelasan,NoPR,NoPO,NoDokumen,TglDokumen,TimeDokumen,
                                    Status,NoSJ,NoBex,NoInv,Banyak,Qty,Bonus,HrgBeli,Disc,DiscT,BatchNo,ExpDate,
                                    Gross,Potongan,VALUE,PPN,Total,NoBPB,NoBPP,NoAcuDokumen
                                FROM trs_delivery_order_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_delivery_order_detail.Produk
                                ORDER BY  TimeDokumen DESC, NoDokumen ASC $limit"
                            );
        }else
        {
            if($status == "Open"){
            $query = $this->db->query("
                            SELECT Cabang,Prinsipal,pp.Prinsipal2,Supplier,Pabrik,Produk,NamaProduk,
                                    Satuan,Keterangan,Penjelasan,NoPR,NoPO,NoDokumen,TglDokumen,TimeDokumen,
                                    Status,NoSJ,NoBex,NoInv,Banyak,Qty,Bonus,HrgBeli,Disc,DiscT,BatchNo,ExpDate,
                                    Gross,Potongan,VALUE,PPN,Total,NoBPB,NoBPP,NoAcuDokumen
                                FROM trs_delivery_order_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_delivery_order_detail.Produk 
                                WHERE Cabang = '".$this->cabang."' $search $byStatus ORDER BY  TimeDokumen DESC, NoDokumen ASC $limit"
                                );     
                 }else{
            $query = $this->db->query("
                            SELECT Cabang,Prinsipal,pp.Prinsipal2,Supplier,Pabrik,Produk,NamaProduk,
                                    Satuan,Keterangan,Penjelasan,NoPR,NoPO,NoDokumen,TglDokumen,TimeDokumen,
                                    Status,NoSJ,NoBex,NoInv,Banyak,Qty,Bonus,HrgBeli,Disc,DiscT,BatchNo,ExpDate,
                                    Gross,Potongan,VALUE,PPN,Total,NoBPB,NoBPP
                                FROM trs_delivery_order_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_delivery_order_detail.Produk 
                                WHERE Cabang = '".$this->cabang."' $search $byStatus ORDER BY  TimeDokumen DESC, NoDokumen ASC $limit"
                                ); 
                }       
        }

        return $query;
    }
    public function getexport($tipe=null, $jenis=null, $kat1=null)
    {   

        if ($tipe == "All") {
            $tipeD = "";
        }elseif ($tipe == "7"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 0 AND 30)";
        }elseif ($tipe == "30"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 31 AND 45)";
        }elseif ($tipe == "45"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 46 AND 60)";
        }elseif ($tipe == "60"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 61 AND 90)";
        }elseif ($tipe == "90"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 91 AND 120)";
        }elseif ($tipe == "120"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 121 AND 150)";
        }elseif ($tipe == "150"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END BETWEEN 151 AND 180)";
        }elseif ($tipe == "180"){
            $tipeD = "AND (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END > 180)";
        }else{
            $tipeD = "";
        }


        if (empty($jenis)) {
            $tipeDok = "";
        }elseif($jenis=='All'){
            $tipeDok = "";
        }else{
            $tipeDok = "AND TipeDokumen = '".$jenis."' ";
        }

        if(empty($kat1))
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
        }
        elseif ($kat1=='All') 
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";
        }
        elseif ($kat1=='SLunas') 
        {
            $kat1Dat = "WHERE KATEGORI2 LIKE '%LUNAS%'";                
        }elseif ($kat1=='BLunas') 
        {
            $kat1Dat = "WHERE KATEGORI2 NOT LIKE '%LUNAS%'";                
        }
        elseif ($kat1=='RPOSLunas') 
        {
            $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO JATUH TEMPO%'  )";                
        }elseif ($kat1=='RPOBLunas') 
        {
            $kat1Dat = "WHERE ( KATEGORI2 LIKE '%RPO BELUM JATUH TEMPO%'  )";                
        }else
        {
            $kat1Dat = "WHERE KATEGORI2 != 'CEK'";            
        }

        if($this->cabang == "Pusat")
        {
            $query = $this->db->query("SELECT * FROM (
                                    SELECT
                                          '' AS REGION,
                                          `Cabang` AS CABANG,
                                          `Cabang` AS 'AREA',
                                          `Pelanggan` AS 'INDEX',
                                          CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
                                          `TipeDokumen` AS KDDOKJDI,
                                          `NoFaktur` AS NODOKJDI,
                                          `TglFaktur` AS TGLFAK,
                                          `TOP`,
                                          `NODO`,
                                          CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END AS UMUR,
                                          CASE 
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 0 AND 30 THEN '0-30'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 31 AND 45 THEN '31-45'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 46 AND 60 THEN '46-60'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 61 AND 90 THEN '61-90'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 91 AND 150 THEN '91-150'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            > 150 THEN '>150'
                                            ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
                                          END AS KATEGORI,
                                          CASE WHEN SALDO != 0 THEN
                                          CASE 
                                            WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
                                                WHEN `CaraBayar` LIKE '%RPO%' THEN 
                                                    CASE 
                                                        WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%75%' THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%90%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%120%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%150%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%180%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                    ELSE 'CEK CARA BAYAR' 
                                                    END
                                                WHEN `CaraBayar` LIKE '%Cash%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                                WHEN `CaraBayar` LIKE '%Kredit%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE IF( `Saldo`= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
                                          END 
                                          ELSE 'LUNAS' END  
                                          AS KATEGORI2,
                                          `Salesman` AS KODESALES,
                                          NamaPelanggan AS NAMA_LANG,
                                          `AlamatFaktur` AS ALAMAT_LANG,
                                          Total AS NILDOK,
                                          Saldo AS SALDO,
                                          CASE 
                                            WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                                            WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
                                            WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
                                            WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
                                            WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
                                            ELSE 'CEK TIPE PELANGGAN'
                                            END AS 'KATEGORI3'
                                            ,
                                          '' AS JUDUL,
                                          '' AS SPESIAL,
                                          `Status` AS 'STATUS',  
                                          IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
                                           CASE WHEN Saldo !=0 
                                            THEN
                                                CASE WHEN
                                                    (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                    > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE 'LUNAS'
                                           END AS KETERANGAN_JATUH_TEMPO
                                    FROM `trs_faktur` WHERE Cabang != '' $tipeD $tipeDok)a $kat1Dat 
                                    ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC 
                                    ");
        }else
        {
            $query = $this->db->query(" SELECT * FROM (
                                    SELECT
                                          '' AS REGION,
                                          `Cabang` AS CABANG,
                                          `Cabang` AS 'AREA',
                                          `Pelanggan` AS 'INDEX',
                                          CONCAT(`Pelanggan`,`NoFaktur`,`TglFaktur`) AS COMBO,
                                          `TipeDokumen` AS KDDOKJDI,
                                          `NoFaktur` AS NODOKJDI,
                                          `TglFaktur` AS TGLFAK,
                                          `TOP`,
                                          `NODO`,
                                          `nodih`,
                                          `tgldih`,
                                          CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END AS UMUR,
                                          CASE 
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 0 AND 30 THEN '0-30'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 31 AND 45 THEN '31-45'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 46 AND 60 THEN '46-60'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 61 AND 90 THEN '61-90'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            BETWEEN 91 AND 150 THEN '91-150'
                                            WHEN (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END)
                                            > 150 THEN '>150'
                                            ELSE DATEDIFF(DATE(NOW()),`TglFaktur`)
                                          END AS KATEGORI,
                                          CASE WHEN SALDO != 0 THEN
                                          CASE 
                                            WHEN `Salesman`='RG2' THEN 'RAGU-RAGU' 
                                                WHEN `CaraBayar` LIKE '%RPO%' THEN 
                                                    CASE 
                                                        WHEN (`CaraBayar` = 'RPO' OR `CaraBayar` LIKE '%60%') THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%75%' THEN 
                                                            CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%90%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%120%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%150%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                        WHEN `CaraBayar` LIKE '%180%' THEN 
                                                        CASE WHEN 
                                                                (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                                >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                    ELSE 'CEK CARA BAYAR' 
                                                    END
                                                WHEN `CaraBayar` LIKE '%Cash%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                                WHEN `CaraBayar` LIKE '%Kredit%' THEN 
                                                CASE WHEN 
                                                        (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                        >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE IF( `Saldo`= 0 , 'LUNAS' , UCASE(`TipeDokumen`)) 
                                          END 
                                          ELSE 'LUNAS' END  
                                          AS KATEGORI2,
                                          `Salesman` AS KODESALES,
                                          NamaPelanggan AS NAMA_LANG,
                                          `AlamatFaktur` AS ALAMAT_LANG,
                                          Total AS NILDOK,
                                          Saldo AS SALDO,
                                          CASE 
                                            WHEN `TipePelanggan` IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                                            WHEN `TipePelanggan` IN ('DK','DPKM') THEN 'DINKES'
                                            WHEN `TipePelanggan` IN ('IN') THEN 'INSTANSI'
                                            WHEN `TipePelanggan` IN ('RSSA') THEN 'RS SWASTA'
                                            WHEN `TipePelanggan` IN ('RSUD') THEN 'RSUD'
                                            ELSE 'CEK TIPE PELANGGAN'
                                            END AS 'KATEGORI3'
                                            ,
                                          '' AS JUDUL,
                                          '' AS SPESIAL,
                                          `Status` AS 'STATUS',  
                                          IFNULL(`CaraBayar`,'') AS CARA_BAYAR,
                                           CASE WHEN Saldo !=0 
                                            THEN
                                                CASE WHEN
                                                    (CASE WHEN `Saldo` != 0 THEN CASE WHEN (`TglPelunasan`='' OR `TglPelunasan` IS NULL ) THEN DATEDIFF(DATE(NOW()),`TglFaktur`) ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END ELSE DATEDIFF(IFNULL(`TglPelunasan`,DATE(NOW())),`TglFaktur`) END )
                                                    > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                            ELSE 'LUNAS'
                                           END AS KETERANGAN_JATUH_TEMPO
                                    FROM `trs_faktur` WHERE Cabang = '".$this->cabang."' $tipeD $tipeDok)a $kat1Dat 
                                    ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC 
                                        ");     
        }
        return $query;
    }

    public function getexportPT($bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));
        $query = $this->db->query("
                                    SELECT * FROM 
                                    (SELECT mst_cabang.Region1 AS 'WIL',
                                           trs_faktur_detail.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                                           IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                                           IFNULL(trs_faktur_detail.Acu2,'') AS 'NO_ACU2',
                                           trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           trs_faktur_detail.Prinsipal AS 'JUDUL',
                                           trs_faktur_detail.KodeProduk AS 'KODEPROD',
                                           trs_faktur_detail.NamaProduk AS 'NAMAPROD',                                           
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur'
                                                  THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                                                  ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                                           END AS 'UNIT',
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                                                  ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                                           END AS 'PRINS',
                                           CASE trs_faktur_detail.TipeDokumen 
                                           WHEN 'Retur' 
                                                  THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                                                  ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                                           END AS 'BANYAK',
                                           IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                                           (IFNULL(trs_faktur_detail.DiscCab,0.0) + IFNULL(trs_faktur_detail.DiscCab_onf,0.0)) AS 'PRSNXTRA',
                                           (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                           WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                           ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                                           END) AS 'PRINPXTRA',
                                           IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                                           IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                                           IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                                           IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                                           trs_faktur_detail.Salesman AS 'KODESALES',
                                           DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                                           IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                                           IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                                           trs_faktur_detail.Cabang AS 'Area',
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           ifnull(trs_faktur_detail.DiscPrins1,0.0) as 'DiscPrins1',
                                           ifnull(trs_faktur_detail.DiscPrins2,0.0) as 'DiscPrins2',
                                           trs_faktur_detail.CashDiskon,
                                           ifnull(trs_faktur_detail.DiscCabMax,0.0) as 'DiscCabMax',
                                           trs_faktur_detail.KetDiscCabMax,
                                           ifnull(trs_faktur_detail.DiscPrinsMax,0.0) as 'DiscPrinsMax',
                                           trs_faktur_detail.KetDiscPrinsMax,
                                           trs_faktur_detail.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'F' AS Tipe,
                                           NoBPB 
                                    FROM trs_faktur_detail JOIN trs_faktur ON 
                                         trs_faktur.`NoFaktur` = trs_faktur_detail.`NoFaktur` AND 
                                         trs_faktur.`Cabang` = trs_faktur_detail.`Cabang`
                                         LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                    FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                    ON trs_faktur_detail.Pelanggan=mst_pelanggan.Kode
                                         LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                    ON trs_faktur_detail.Cabang=mst_cabang.Cabang
                                                    WHERE trs_faktur_detail.Cabang='".$this->cabang."' AND trs_faktur.Status NOT LIKE '%Batal%'
                                                    AND MONTH(trs_faktur.TglFaktur) IN ('".$month."') 
                                                    AND YEAR(trs_faktur.TglFaktur)='".$year."'
                                                                      
                                    UNION ALL
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           a.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           NoDokumen AS 'NODOKJDI',
                                           IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                                           IFNULL(trs_faktur.Acu2,'') AS 'NO_ACU2',
                                           a.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                                           a.KodeProduk AS 'KODEPROD',
                                           mst_produk.Produk AS 'NAMAPROD',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN'
                                                  THEN ABS(`Banyak`) *-1 
                                                  ELSE ABS(`Banyak`) 
                                           END AS 'UNIT',
                                           0 AS 'PRINS',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN' 
                                                  THEN ABS((IFNULL(Banyak,0) ))*-1 
                                                  ELSE ABS((IFNULL(Banyak,0) ))
                                           END AS 'BANYAK',
                                           0 AS 'HARGA',
                                           IFNULL(DscCab,0.0) AS 'PRSNXTRA',
                                           0 AS 'PRINPXTRA',
                                           0 AS 'TOT1',
                                           IFNULL(a.Jumlah,0) AS 'NILJU',
                                           0 AS PPN,
                                           0 AS 'COGS',
                                           trs_faktur.Salesman AS 'KODESALES',
                                           DATE(TglFaktur) AS 'TGLDOK',
                                           '' AS 'TGLEXP',
                                           '' AS 'BATCH',
                                           a.Cabang AS 'Area',
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           '' AS DiscPrins1,
                                           '' AS DiscPrins2,
                                           '' AS CashDiskon,
                                           '' AS DiscCabMax,
                                           '' AS KetDiscCabMax,
                                           '' AS DiscPrinsMax,
                                           '' AS KetDiscPrinsMax,
                                           trs_faktur.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'CD' AS Tipe,
                                           '' as NoBPB 
                                    FROM trs_faktur_cndn a,mst_produk,trs_faktur
                                         LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                     FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                     ON trs_faktur.Pelanggan=mst_pelanggan.Kode
                                         LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                    ON trs_faktur.Cabang=mst_cabang.Cabang
                                                    WHERE a.Cabang='".$this->cabang."' AND a.Status IN ('CNOK','DNOK')
                                                    AND a.NoDokumen=trs_faktur.NoFaktur
                                                    AND a.KodeProduk = mst_produk.Kode_Produk
                                                    AND MONTH(TanggalCNDN)IN ('".$month."') 
                                                    AND YEAR(TanggalCNDN)='".$year."'
                                    UNION ALL
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           trs_delivery_order_sales_detail.`NoDO` AS 'NODOKJDI',
                                           IFNULL(trs_delivery_order_sales_detail.`Acu`,'') AS 'NO_ACU',
                                           IFNULL(trs_delivery_order_sales_detail.`Acu2`,'') AS 'NO_ACU2',
                                           trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           Prinsipal AS 'JUDUL',
                                           KodeProduk AS 'KODEPROD',
                                           NamaProduk AS 'NAMAPROD',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                                                  ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                                           END AS 'UNIT',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                                                  ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                                           END AS 'PRINS',
                                           CASE trs_delivery_order_sales_detail.Status 
                                           WHEN 'Retur' 
                                                  THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                                                  ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                                           END AS 'BANYAK',
                                           IFNULL(Harga,0) AS 'HARGA',
                                           (IFNULL(DiscCab,0.0) + IFNULL(DiscCab,0.0)) AS 'PRSNXTRA',
                                           (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                            WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                            ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                            END) AS 'PRINPXTRA',
                                            IFNULL(Gross,0.0) AS 'TOT1',
                                            IFNULL(VALUE,0.0) AS 'NILJU',
                                            IFNULL(PPN,0.0) AS PPN,
                                            IFNULL(TotalCOGS,0.0) AS 'COGS',
                                            Salesman AS 'KODESALES',
                                            DATE(TglDO) AS 'TGLDOK',
                                            IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                                            IFNULL(BatchNo,'') AS 'BATCH',
                                            trs_delivery_order_sales_detail.Cabang AS 'Area',
                                            mst_pelanggan.Telp AS TELP,
                                            mst_pelanggan.Rayon_1 AS RAYON,
                                            mst_pelanggan.Tipe_2 AS PANEL,
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) as 'DiscPrins1',
                                            ifnull(trs_delivery_order_sales_detail.DiscPrins2,0.0) as 'DiscPrins2',
                                            ifnull(trs_delivery_order_sales_detail.CashDiskon,0.0) as 'CashDiskon',
                                            ifnull(trs_delivery_order_sales_detail.DiscCabMax,0.0) as 'DiscCabMax',
                                            trs_delivery_order_sales_detail.KetDiscCabMax,
                                            ifnull(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) as 'DiscPrinsMax',
                                            trs_delivery_order_sales_detail.KetDiscPrinsMax,
                                            trs_delivery_order_sales_detail.NoDO,
                                            trs_delivery_order_sales_detail.Status,
                                            'DO' as 'TipeDokumen',
                                            'D' AS Tipe,
                                             NoBPB 
                                       FROM trs_delivery_order_sales_detail
                                        LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                   FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                                   ON trs_delivery_order_sales_detail.Pelanggan=mst_pelanggan.Kode
                                        LEFT JOIN (SELECT Region1,Cabang 
                                            FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                                   ON trs_delivery_order_sales_detail.Cabang=mst_cabang.Cabang
                                     WHERE trs_delivery_order_sales_detail.Cabang='".$this->cabang."' 
                                           AND ((STATUS IN ('Open','Kirim','Terima','Retur')) or (status ='Closed' and ifnull(status_retur,'') ='Y'))
                                           AND MONTH(TglDO) IN ('".$month."') 
                                           AND YEAR(TglDO)='".$year."') AS LapPT
                                    where LapPT.NM_CABANG = '".$this->cabang."'
                                     ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,LapPT.NAMA_LANG,LapPT.NODOKJDI;

                                    "); 
        return $query;
    }



    public function listDataPODetailAll($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

       
        if($status == "Open"){
            $query = $this->db->query("
                            SELECT trs_po_detail.*,pp.Prinsipal2 FROM trs_po_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_po_detail.Produk 
                                WHERE Cabang = '".$this->cabang."' $search $byStatus ORDER BY  Time_PO DESC, No_PO ASC $limit"
                                );     
        }else{
            $query = $this->db->query("
                            SELECT trs_po_detail.*,pp.Prinsipal2
                                FROM trs_po_detail
                                LEFT JOIN (SELECT Kode_Produk,Prinsipal2 FROM mst_produk )pp ON pp.Kode_Produk=trs_po_detail.Produk 
                                WHERE Cabang = '".$this->cabang."' $search $byStatus ORDER BY  Time_PO DESC, No_PO ASC $limit"
                                ); 
                    
        }

        return $query;
    }

    public function listHargaCabang($search = null,$limit = null)
    {   

        $query = $this->db->query("SELECT Prinsipal,Produk,Produk_String,Cabang,HNA,HNA2,Dsc_Cab,Dsc_Pri,Hrg_Beli_Cab,Dsc_Beli_Cab FROM mst_harga WHERE IFNULL(Cabang,'') IN ('','".$this->cabang."') AND IFNULL(Produk,'') != '' $search ORDER BY Prinsipal,Produk_String $limit");
        return $query;

    }

    public function listestokawals($bydate = null,$kode = null)
    {

        $cabang = $this->cabang;
        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));

        $query = $this->db->query("
                        SELECT Tahun,Cabang,KodeProduk,NamaProduk,SUM(UnitStok) AS unitS,SUM(SAwal".$month.") AS unitA 
                                FROM `trs_invsum` 
                                WHERE KodeProduk='$kode' AND Tahun='$year' AND Cabang='$cabang'
                                GROUP BY Tahun,Cabang,KodeProduk
            ");


        // log_message('error','tipe :'.$tipe);
        // log_message('error','tipeD :'.$tipeD);
        // log_message('error','jenis :'.$jenis);
        // log_message('error','tipedok :'.$tipeDok);
        // log_message('error','kat1 :'.$kat1);
        // log_message('error','kat1Dat :'.$kat1Dat);
         // log_message('error',print_r($query->result(),true));


        return $query;
    }


    public function listenapza($search = null, $limit = null,$bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $cabang = $this->cabang;
        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));

        $query =$this->db->query("
                    SELECT 
                        xxx.NamaProduk AS NamaProduk,xxx.TglDoc AS Tanggal,'' AS SaldoAwal,xxx.BatchNo AS BatchAwal,
                        CASE WHEN xxx.tipedata IN ('IN') THEN xxx.NoDoc ELSE '' END AS DokumenMasuk,
                        CASE WHEN xxx.tipedata IN ('IN') THEN xxx.Dokumen ELSE '' END AS Sumber,
                        xxx.qty_in AS JumlahMasuk,
                        CASE WHEN xxx.tipedata IN ('IN') THEN xxx.BatchNo ELSE '' END AS BatchMasuk,
                        CASE WHEN xxx.tipedata IN ('OUT') THEN xxx.NoDoc ELSE '' END AS DokumenKeluar,
                        CASE WHEN xxx.tipedata IN ('OUT') THEN xxx.Dokumen ELSE '' END AS Tujuan,
                        xxx.qty_out AS JumlahKeluar,
                        CASE WHEN xxx.tipedata IN ('OUT') THEN xxx.BatchNo ELSE '' END AS BatchKeluar,
                        '' AS SaldoAkhir,xxx.BatchNo AS BatchAkhir, DATE(ExpDate) AS expD,  
                        yyy.`Kategori`,yyy.`Kandungan`,yyy.`Bentuk`,yyy.`Satuan`,yyy.Farmalkes, 
                        xxx.TglDoc,xxx.TimeDoc,xxx.NoDoc,xxx.Kode FROM (
                      SELECT 'IN' AS tipedata,'BPB' AS trans, NoDokumen AS 'NoDoc',
                          CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                          TglDokumen AS 'TglDoc',
                          TimeDokumen AS 'TimeDoc',
                          Produk AS 'Kode',
                          NamaProduk AS 'NamaProduk',
                          Satuan AS 'Unit',
                          (IFNULL(Qty,'') + IFNULL(Bonus,'')) AS 'qty_in',
                          0 AS 'qty_out',
                          BatchNo,
                          ExpDate
                      FROM trs_terima_barang_detail
                      WHERE 
                        STATUS NOT IN ('pending','Batal') AND
                        IFNULL(Tipe,'') != 'BKB' AND
                        MONTH(TglDokumen)=$month AND 
                        YEAR(TglDokumen)=$year 
                      
                      UNION ALL

                      SELECT 'OUT' AS tipedata,'BKB' AS trans, NoDokumen AS 'NoDoc',
                          CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                          TglDokumen AS 'TglDoc',
                          TimeDokumen AS 'TimeDoc',
                          Produk AS 'Kode',
                          NamaProduk AS 'NamaProduk',
                          Satuan AS 'Unit',
                          0 AS 'qty_in',
                          (((IFNULL(Qty,'') + IFNULL(Bonus,'')))*-1) AS 'qty_out',
                          BatchNo,
                          ExpDate
                      FROM trs_terima_barang_detail
                      WHERE   IFNULL(Tipe,'') = 'BKB' AND 
                        MONTH(TglDokumen)=$month AND 
                        YEAR(TglDokumen)=$year 

                      UNION ALL
                      
                      SELECT 'IN' AS tipedata,'KORIN' AS trans, no_koreksi AS 'NoDoc', 
                         CONCAT('Usulan Adjusment (+) : ', catatan) AS 'Dokumen',
                         tanggal AS 'TglDoc',
                         trs_mutasi_koreksi.created_at AS 'TimeDoc',
                         trs_mutasi_koreksi.produk AS 'Kode' ,
                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                         Satuan AS 'Unit',
                         qty AS 'qty_in',
                         0 AS 'qty_out',
                         batch AS 'BatchNo',
                         exp_date AS 'ExpDate'
                      FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                      WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Open' AND
                        MONTH(tanggal)=$month AND 
                        YEAR(tanggal)=$year 

                      UNION ALL

                      SELECT 'IN' AS tipedata,'KORIN' AS trans, no_koreksi AS 'NoDoc', 
                         CONCAT('Adjusment (+) : ', catatan) AS 'Dokumen',
                         tanggal AS 'TglDoc',
                         trs_mutasi_koreksi.created_at AS 'TimeDoc',
                         trs_mutasi_koreksi.produk AS 'Kode' ,
                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                         Satuan AS 'Unit',
                         qty AS 'qty_in',
                         0 AS 'qty_out',
                         batch AS 'BatchNo',
                         exp_date AS 'ExpDate'
                      FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                      WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                        MONTH(tanggal)=$month AND 
                        YEAR(tanggal)=$year 


                      UNION ALL

                      SELECT 'IN' AS tipedata,'RLKIN' AS trans, No_Terima AS 'NoDoc', 
                         CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
                         Tgl_terima AS 'TglDoc',
                         Time_terima AS 'TimeDoc',
                         Produk AS 'Kode' ,
                         NamaProduk AS 'NamaProduk' ,
                         Satuan AS 'Unit',
                         (Qty+Bonus) AS 'qty_in',
                         0 AS 'qty_out',
                         BatchNo AS 'BatchNo',
                         ExpDate AS 'ExpDate'
                      FROM  trs_relokasi_terima_detail 
                      WHERE   (Qty > 0 OR Bonus > 0) AND
                        STATUS = 'Terima' AND
                        MONTH(Tgl_terima)=$month AND 
                        YEAR(Tgl_terima)=$year 
                        
                      UNION ALL 

                      SELECT 'IN' AS tipedata,'RTF' AS trans, NoFaktur AS 'NoDoc',
                        CONCAT('Terima dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
                        TglFaktur AS 'TglDoc',
                        TimeFaktur AS 'TimeDoc',
                        KodeProduk AS 'Kode', 
                        NamaProduk AS 'NamaProduk',
                        UOM AS 'Unit',
                        (QtyFaktur+BonusFaktur) AS 'qty_in',
                        0 AS 'qty_out',
                        BatchNo,
                        ExpDate
                      FROM trs_faktur_detail 
                      WHERE TipeDokumen ='Retur' AND 
                        IFNULL(STATUS,'') != 'Batal' AND
                        MONTH(TglFaktur)=$month AND 
                        YEAR(TglFaktur)=$year 

                      UNION ALL


                      SELECT 'OUT' AS tipedata,'INV' AS trans, NoDO AS 'NoDoc',
                        CONCAT('Kirim ke ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                        TglDO AS 'TglDoc',
                        TimeDO AS 'TimeDoc',
                        KodeProduk AS 'Kode',
                        NamaProduk AS 'NamaProduk',
                        UOM AS 'Unit',
                        0 AS 'qty_in', 
                        (QtyDO+BonusDO) AS 'qty_out',
                        BatchNo,ExpDate
                      FROM trs_delivery_order_sales_detail 
                      WHERE 
                        tipedokumen ='DO' AND
                        MONTH(TglDO)=$month AND 
                        YEAR(TglDO)=$year 


                      UNION ALL
                      
                      SELECT 'IN' AS tipedata,'RTF' AS trans, NoDO AS 'NoDoc',
                        CONCAT('Batal Kirim ke ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                        TglDO AS 'TglDoc',
                        TimeDO AS 'TimeDoc',
                        KodeProduk AS 'Kode',
                        NamaProduk AS 'NamaProduk',
                        UOM AS 'Unit',
                        (QtyDO+BonusDO) AS 'qty_in', 
                        0 AS 'qty_out',
                        BatchNo,ExpDate
                      FROM trs_delivery_order_sales_detail 
                      WHERE 
                        STATUS = 'Batal' AND 
                        MONTH(DATE(IFNULL(time_batal,'')))=$month AND 
                        YEAR(DATE(IFNULL(time_batal,'')))=$year 
                        
                      UNION ALL
                      
                      SELECT 'IN' AS tipedata,'RTF' AS trans, NoDO AS 'NoDoc',
                        CONCAT('Retur Kirim dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                        TglDO AS 'TglDoc',
                        TimeDO AS 'TimeDoc',
                        KodeProduk AS 'Kode',
                        NamaProduk AS 'NamaProduk',
                        UOM AS 'Unit',
                        (QtyDO+BonusDO) AS 'qty_in', 
                        0 AS 'qty_out',
                        BatchNo,ExpDate
                      FROM trs_delivery_order_sales_detail 
                      WHERE 
                        TipeDokumen ='Retur' AND STATUS = 'Retur' AND
                        MONTH(TglDO)=$month AND 
                        YEAR(TglDO)=$year 


                      UNION ALL

                      SELECT 'OUT' AS tipedata,'RLKOUT' AS trans, trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 
                         CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_detail.Cabang_Penerima) AS 'Dokumen',
                         trs_relokasi_kirim_detail.Tgl_kirim AS 'TglDoc',
                         trs_relokasi_kirim_detail.Time_kirim 'TimeDoc',
                         Produk AS 'Kode' ,
                         NamaProduk AS 'NamaProduk' ,
                         Satuan AS 'Unit',
                         0 AS 'qty_in',
                         (Qty+Bonus) AS 'qty_out',
                         BatchNo AS 'BatchNo',
                         ExpDate AS 'ExpDate'
                      FROM  trs_relokasi_kirim_detail
                      WHERE trs_relokasi_kirim_detail.Status = 'Kirim' AND
                        MONTH(trs_relokasi_kirim_detail.Tgl_kirim)=$month AND 
                        YEAR(trs_relokasi_kirim_detail.Tgl_kirim)=$year 
                        
                      UNION ALL

                      SELECT 'OUT' AS tipedata,'BKB' AS trans, trs_usulan_retur_beli_header.No_Usulan AS 'NoDoc', 
                        CONCAT('Usulan Retur Beli : QTY = ',((trs_usulan_retur_beli_detail.Qty+trs_usulan_retur_beli_detail.Bonus) * -1),'  ~ Ke : ',trs_usulan_retur_beli_header.Prinsipal) AS 'Dokumen',
                        trs_usulan_retur_beli_header.Tanggal AS 'TglDoc',
                        trs_usulan_retur_beli_header.added_time AS 'TimeDoc',
                        trs_usulan_retur_beli_detail.Produk AS 'Kode' ,
                        trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk' ,
                        trs_usulan_retur_beli_detail.Satuan AS 'Unit',
                        0 AS 'qty_in',
                        0 AS 'qty_out',
                        trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo',
                        trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate'
                      FROM  trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                      WHERE IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' AND
                        MONTH(trs_usulan_retur_beli_header.tanggal)=$month AND 
                        YEAR(trs_usulan_retur_beli_header.tanggal)=$year 
                      
                      UNION ALL

                      SELECT 'OUT' AS tipedata,'KOROUT' AS trans, no_koreksi AS 'NoDoc', 
                        CONCAT('Adjusment (-): ',catatan) AS 'Dokumen',
                        tanggal AS 'TglDoc',
                        trs_mutasi_koreksi.created_at AS 'TimeDoc',
                        trs_mutasi_koreksi.produk AS 'Kode' ,
                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                        Satuan AS 'Unit',
                        0 AS 'qty_in',
                        (qty * -1) AS 'qty_out',
                        batch AS 'BatchNo',
                        exp_date AS 'ExpDate'
                      FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                      WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                        MONTH(tanggal)=$month AND 
                        YEAR(tanggal)=$year 


                      UNION ALL

                      SELECT 'OUT' AS tipedata,'KOROUT' AS trans, no_koreksi AS 'NoDoc', 
                        CONCAT('Usulan Adjusment (-): QTY = ',(qty * -1),catatan) AS 'Dokumen',
                        tanggal AS 'TglDoc',
                        trs_mutasi_koreksi.created_at AS 'TimeDoc',
                        trs_mutasi_koreksi.produk AS 'Kode' ,
                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                        Satuan AS 'Unit',
                        0 AS 'qty_in',
                        0 AS 'qty_out',
                        batch AS 'BatchNo',
                        exp_date AS 'ExpDate'
                      FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                      WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Open' AND
                        MONTH(tanggal)=$month AND 
                        YEAR(tanggal)=$year 
                      ORDER BY TglDoc,TimeDoc,NoDoc ASC
                    )xxx   
                      LEFT JOIN mst_produk AS yyy ON xxx.Kode=yyy.`Kode_Produk`
                      WHERE yyy.`Kategori` IN ('Psikotropika','OOT','Precursor')
                      ORDER BY yyy.`Kategori`,xxx.Kode,xxx.TglDoc,xxx.TimeDoc,xxx.NoDoc
                      $limit
                      


            ");


        // log_message('error','tipe :'.$tipe);
        // log_message('error','tipeD :'.$tipeD);
        // log_message('error','jenis :'.$jenis);
        // log_message('error','tipedok :'.$tipeDok);
        // log_message('error','kat1 :'.$kat1);
        // log_message('error','kat1Dat :'.$kat1Dat);
         // log_message('error',print_r($query->result(),true));


        return $query;
    }


    public function listtriwulan($search = null, $limit = null,$bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $cabang = $this->cabang;
        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));


        $query =$this->db->query("

                    SELECT 
                        pp.Farmalkes AS NIE,pp.Produk AS NamaProduk,pp.Satuan AS Kemasan,
                        0 AS StokAwal,
                        SUM(RRET) AS RRET,
                        SUM(RS) AS RS,
                        SUM(AP) AS AP,
                        SUM(PBF) AS PBF,
                        SUM(PEM) AS PEM,
                        SUM(PKM) AS PKM,
                        SUM(KL) AS KL,
                        SUM(SW) AS SW,
                        SUM(PE) AS PE,
                        (SUM(MPBF)+SUM(KLA)+SUM(MLA)) AS MasukPBF,
                        SUM(MRET) AS keluarRet,
                        pp.Bentuk,pp.Kandungan,
                        pp.Kategori, HNA AS HJD,'' AS Keterangan,pp.Kode_Produk AS Kode,HNA

                    FROM (

                    SELECT 
                    'IN' AS tipedata,'BPB' AS trans, 
                    NoDokumen AS 'NoDoc',
                    CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                    TglDokumen AS 'TglDoc',
                    TimeDokumen AS 'TimeDoc',
                    Produk AS 'Kode',
                    NamaProduk AS 'NamaProduk',
                    Satuan AS 'Unit',
                    (IFNULL(Qty,'') + IFNULL(Bonus,'')) AS 'qty_in',
                    0 AS 'qty_out',
                    BatchNo,
                    ExpDate,
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    SUM((IFNULL(Qty,'') + IFNULL(Bonus,''))) AS MPBF,
                    CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM trs_terima_barang_detail
                    WHERE 
                    STATUS NOT IN ('pending','Batal') AND
                    IFNULL(Tipe,'') != 'BKB' AND
                    TglDokumen BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY Produk

                    UNION ALL

                    SELECT 'OUT' AS tipedata,'BKB' AS trans, NoDokumen AS 'NoDoc',
                    CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                    TglDokumen AS 'TglDoc',
                    TimeDokumen AS 'TimeDoc',
                    Produk AS 'Kode',
                    NamaProduk AS 'NamaProduk',
                    Satuan AS 'Unit',
                    0 AS 'qty_in',
                    ((ABS(IFNULL(Qty,'') + IFNULL(Bonus,'')))*-1) AS 'qty_out',
                    BatchNo,
                    ExpDate,
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    SUM((ABS(IFNULL(Qty,'') + IFNULL(Bonus,'')))) AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM trs_terima_barang_detail
                    WHERE   IFNULL(Tipe,'') = 'BKB' AND 
                    TglDokumen BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_terima_barang_detail.Produk

                    UNION ALL

                    SELECT 'IN' AS tipedata,'KORIN' AS trans, no_koreksi AS 'NoDoc', 
                    CONCAT('Usulan Adjusment (+) : ', catatan) AS 'Dokumen',
                    tanggal AS 'TglDoc',
                    trs_mutasi_koreksi.created_at AS 'TimeDoc',
                    trs_mutasi_koreksi.produk AS 'Kode' ,
                    trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    qty AS 'qty_in',
                    0 AS 'qty_out',
                    batch AS 'BatchNo',
                    exp_date AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET,  
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    SUM(IFNULL(qty,0)) AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                    WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Open' AND
                    tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_mutasi_koreksi.produk

                    UNION ALL

                    SELECT 'IN' AS tipedata,'KORIN' AS trans, no_koreksi AS 'NoDoc', 
                    CONCAT('Adjusment (+) : ', catatan) AS 'Dokumen',
                    tanggal AS 'TglDoc',
                    trs_mutasi_koreksi.created_at AS 'TimeDoc',
                    trs_mutasi_koreksi.produk AS 'Kode' ,
                    trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    qty AS 'qty_in',
                    0 AS 'qty_out',
                    batch AS 'BatchNo',
                    exp_date AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET,  
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    SUM(IFNULL(qty,0)) AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                    WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                    tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_mutasi_koreksi.produk

                    UNION ALL

                    SELECT 'IN' AS tipedata,'RLKIN' AS trans, No_Terima AS 'NoDoc', 
                    CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
                    Tgl_terima AS 'TglDoc',
                    Time_terima AS 'TimeDoc',
                    Produk AS 'Kode' ,
                    NamaProduk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    (Qty+Bonus) AS 'qty_in',
                    0 AS 'qty_out',
                    BatchNo AS 'BatchNo',
                    ExpDate AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    SUM(IFNULL(Qty,0)+IFNULL(Bonus,0)) AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM  trs_relokasi_terima_detail 
                    WHERE   (Qty > 0 OR Bonus > 0) AND
                    STATUS = 'Terima' AND
                    Tgl_terima BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY Produk

                    UNION ALL 

                    SELECT 'IN' AS tipedata,'RTF' AS trans, NoFaktur AS 'NoDoc',
                    CONCAT('Terima dari ',a.Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
                    TglFaktur AS 'TglDoc',
                    TimeFaktur AS 'TimeDoc',
                    KodeProduk AS 'Kode', 
                    NamaProduk AS 'NamaProduk',
                    UOM AS 'Unit',
                    ABS(IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0)) AS 'qty_in',
                    0 AS 'qty_out',
                    BatchNo,
                    ExpDate,
                    b.`Tipe_Pelanggan` AS tipeP,
                    SUM(ABS(IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    a.Harga AS HNA

                    FROM trs_faktur_detail a
                    LEFT JOIN mst_pelanggan b ON a.`Pelanggan`=b.`Kode`
                    WHERE TipeDokumen ='Retur' AND 
                    IFNULL(STATUS,'') != 'Batal' AND
                    TglFaktur BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY KodeProduk

                    UNION ALL


                    SELECT 'OUT' AS tipedata,'INV' AS trans, NoDO AS 'NoDoc',
                    CONCAT('Kirim ke ',a.Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                    TglDO AS 'TglDoc',
                    TimeDO AS 'TimeDoc',
                    KodeProduk AS 'Kode',
                    NamaProduk AS 'NamaProduk',
                    UOM AS 'Unit',
                    0 AS 'qty_in', 
                    ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0)) AS 'qty_out',
                    BatchNo,ExpDate,
                    b.`Tipe_Pelanggan` AS tipeP,
                    0 AS RRET, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('RS','RSUD','RSSA') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS RS, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('AP','APGR','APMT','APSG') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS AP, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('PBAK','PBF') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PBF, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('DK','IN') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PEM, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('DPKM') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PKM, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('KL','KL1') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS KL, 
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan` IN ('KLAD','KLAB','KLAL','MLOK','MKKT','MNAS','PT') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS SW,
                    IFNULL(SUM(CASE WHEN b.`Tipe_Pelanggan`  IN ('TK','TK','TKOS','XTO') THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    a.Harga AS HNA
                    FROM trs_delivery_order_sales_detail a
                    LEFT JOIN mst_pelanggan b ON a.`Pelanggan`=b.`Kode`
                    WHERE 
                    TglDO BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH)) AND tipedokumen ='DO'
                    GROUP BY KodeProduk

                    UNION ALL

                    SELECT 'IN' AS tipedata,'RTF' AS trans, NoDO AS 'NoDoc',
                    CONCAT('Batal Kirim ke ',a.Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                    TglDO AS 'TglDoc',
                    TimeDO AS 'TimeDoc',
                    KodeProduk AS 'Kode',
                    NamaProduk AS 'NamaProduk',
                    UOM AS 'Unit',
                    ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0)) AS 'qty_in', 
                    0 AS 'qty_out',
                    BatchNo,ExpDate,
                    b.`Tipe_Pelanggan` AS tipeP,
                    SUM(ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    a.Harga AS HNA
                    FROM trs_delivery_order_sales_detail a
                    LEFT JOIN mst_pelanggan b ON a.`Pelanggan`=b.`Kode`
                    WHERE 
                    STATUS = 'Batal' 
                    AND DATE(IFNULL(time_batal,'')) BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY KodeProduk

                    UNION ALL

                    SELECT 'IN' AS tipedata,'RTF' AS trans, NoDO AS 'NoDoc',
                    CONCAT('Retur Kirim dari ',a.Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                    TglDO AS 'TglDoc',
                    TimeDO AS 'TimeDoc',
                    KodeProduk AS 'Kode',
                    NamaProduk AS 'NamaProduk',
                    UOM AS 'Unit',
                    SUM(ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) AS 'qty_in', 
                    0 AS 'qty_out',
                    BatchNo,ExpDate,
                    b.`Tipe_Pelanggan` AS tipeP,
                    IFNULL(ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0)),0) AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    a.Harga AS HNA
                    FROM trs_delivery_order_sales_detail a
                    LEFT JOIN mst_pelanggan b ON a.`Pelanggan`=b.`Kode`
                    WHERE 
                    TipeDokumen ='Retur' AND STATUS = 'Retur' 
                    AND TglDO BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY KodeProduk

                    UNION ALL

                    SELECT 'OUT' AS tipedata,'RLKOUT' AS trans, trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 
                    CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_detail.Cabang_Penerima) AS 'Dokumen',
                    trs_relokasi_kirim_detail.Tgl_kirim AS 'TglDoc',
                    trs_relokasi_kirim_detail.Time_kirim 'TimeDoc',
                    Produk AS 'Kode' ,
                    NamaProduk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    0 AS 'qty_in',
                    (Qty+Bonus) AS 'qty_out',
                    BatchNo AS 'BatchNo',
                    ExpDate AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    SUM(IFNULL(ABS(IFNULL(Qty,0)+IFNULL(Bonus,0)),0)) AS KLA,
                    0 AS HNA
                    FROM  trs_relokasi_kirim_detail
                    WHERE trs_relokasi_kirim_detail.Status = 'Kirim' AND
                    trs_relokasi_kirim_detail.Tgl_kirim BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY Produk

                    UNION ALL

                    SELECT 'OUT' AS tipedata,'BKB' AS trans, trs_usulan_retur_beli_header.No_Usulan AS 'NoDoc', 
                    CONCAT('Usulan Retur Beli : QTY = ',((trs_usulan_retur_beli_detail.Qty+trs_usulan_retur_beli_detail.Bonus) * -1),'  ~ Ke : ',trs_usulan_retur_beli_header.Prinsipal) AS 'Dokumen',
                    trs_usulan_retur_beli_header.Tanggal AS 'TglDoc',
                    trs_usulan_retur_beli_header.added_time AS 'TimeDoc',
                    trs_usulan_retur_beli_detail.Produk AS 'Kode' ,
                    trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk' ,
                    trs_usulan_retur_beli_detail.Satuan AS 'Unit',
                    0 AS 'qty_in',
                    0 AS 'qty_out',
                    trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo',
                    trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM  trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                    WHERE IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' AND
                    trs_usulan_retur_beli_header.tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_usulan_retur_beli_detail.Produk 

                    UNION ALL

                    SELECT 'OUT' AS tipedata,'KOROUT' AS trans, no_koreksi AS 'NoDoc', 
                    CONCAT('Adjusment (-): ',catatan) AS 'Dokumen',
                    tanggal AS 'TglDoc',
                    trs_mutasi_koreksi.created_at AS 'TimeDoc',
                    trs_mutasi_koreksi.produk AS 'Kode' ,
                    trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    0 AS 'qty_in',
                    (qty * -1) AS 'qty_out',
                    batch AS 'BatchNo',
                    exp_date AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    SUM(ABS(qty)) AS KLA,
                    0 AS HNA
                    FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                    WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                    tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_mutasi_koreksi.produk             
                     
                    UNION ALL

                    SELECT 'OUT' AS tipedata,'KOROUT' AS trans, no_koreksi AS 'NoDoc', 
                    CONCAT('Usulan Adjusment (-): QTY = ',(qty * -1),catatan) AS 'Dokumen',
                    tanggal AS 'TglDoc',
                    trs_mutasi_koreksi.created_at AS 'TimeDoc',
                    trs_mutasi_koreksi.produk AS 'Kode' ,
                    trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                    Satuan AS 'Unit',
                    0 AS 'qty_in',
                    0 AS 'qty_out',
                    batch AS 'BatchNo',
                    exp_date AS 'ExpDate',
                    '' AS tipeP,
                    0 AS RRET, 
                    0 AS RS, 
                    0 AS AP, 
                    0 AS PBF, 
                    0 AS PEM, 
                    0 AS PKM, 
                    0 AS KL, 
                    0 AS SW,
                    0 AS PE,
                    0 AS MPBF,
                    '' AS KMPBF,
                    0 AS MRET,
                    0 AS MLA,
                    0 AS KLA,
                    0 AS HNA
                    FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                    WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Open' AND
                    tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
                    INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
                    AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
                    GROUP BY trs_mutasi_koreksi.produk
                    )ss 
                    LEFT JOIN mst_produk pp ON ss.Kode=pp.`Kode_Produk`
                    GROUP BY Kode
                    ORDER BY pp.Farmalkes DESC, pp.Produk ASC,ss.Kode ASC
                    $limit


            ");

        // $queryxxx =$this->db->query("
        //                  SELECT 
        //                     Farmalkes AS NIE,
        //                     Produk AS NamaProduk,
        //                     Satuan AS Kemasan,
        //                     stk AS StokAwal,
        //                     'Pusat' AS SumberPemasukan,
        //                     ''  AS Keterangan,
        //                     0 AS MasukPabrik,
        //                     masuk AS MasukPBF,
        //                     keluarRet AS ReturPabrik,
        //                     0 AS Lainnya,
        //                     RS,
        //                     AP,
        //                     PBF,
        //                     PEM AS SaranaPemerintah,
        //                     PKM AS Puskesmas,
        //                     KL AS Klinik,
        //                     PE AS TokoObat,
        //                     RRET AS Retur,
        //                     EP AS Lainnya,
        //                     '' AS Keterangan,
        //                     Harga AS HJD,
        //                     Kode
                            
        //                 FROM

        //                 (

        //                     (SELECT 
        //                         xx.`Kode_Produk` AS Kode,xx.`Produk`,xx.`Farmalkes`,xx.`Satuan`,
        //                         IFNULL(xx.`Kandungan`,'') AS Sediaan,IFNULL(xx.`Bentuk`,'') AS Bentuk,
        //                     --  IFNULL(zz.Supplier,'') AS Supplier,
        //                         IFNULL(yy.stok,0) AS stk,
        //                         IFNULL(zz.masuk,0) AS masuk,
        //                         IFNULL(zzr.keluarRet,0) AS keluarRet,
        //                         IFNULL(xyz.RRET,0) AS RRET,
        //                         IFNULL(xyz.RS,0) AS RS,
        //                         IFNULL(xyz.AP,0) AS AP,
        //                         IFNULL(xyz.PBF,0) AS PBF,
        //                         IFNULL(xyz.PEM,0) AS PEM,
        //                         IFNULL(xyz.SW,0) AS SW, 
        //                         IFNULL(xyz.PKM,0) AS PKM,
        //                         IFNULL(xyz.KL,0) AS KL,
        //                         IFNULL(xyz.PE,0) AS PE,
        //                         IFNULL(xyz.EP,0) AS EP,
        //                         xyz.Harga       
                                 
        //                     FROM `mst_produk` xx

        //                     LEFT JOIN(
        //                         SELECT  Cabang,KodeProduk AS Produk,IFNULL(SUM(SAwal01),0) AS stok FROM `trs_invsum` WHERE `Cabang`='$cabang' AND Tahun='$year' GROUP BY `Cabang`,`KodeProduk`
        //                     ) yy ON xx.`Kode_Produk`=yy.Produk

        //                     LEFT JOIN(

        //                         SELECT Cabang,Produk,SUM(masuk) AS masuk, expd,batch FROM (
        //                         SELECT Cabang,Produk ,SUM((IFNULL(`Qty`,0) + IFNULL(`Bonus`,0))) AS masuk, 'BPB' AS 'ket',
        //                             DATE(`ExpDate`) AS expd ,`BatchNo` AS batch
        //                         FROM `trs_terima_barang_detail` WHERE `Status` IN ('Close','Closed')
        //                             AND Cabang='$cabang' AND TglDokumen BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`

        //                         UNION ALL

        //                         SELECT 
        //                             Cabang,Produk,SUM((IFNULL(`Qty`,0) + IFNULL(`Bonus`,0))) AS masuk, 'RLK' AS 'ket',
        //                             DATE(`ExpDate`) AS expd ,`BatchNo` AS batch
        //                         FROM `trs_relokasi_terima_detail`
        //                         WHERE Cabang='$cabang' AND Tgl_terima BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`

        //                         UNION ALL

        //                         SELECT Cabang,produk,qty AS masuk,'KOR' AS 'ket',
        //                             DATE(exp_date) AS expd,batch
        //                         FROM `trs_mutasi_koreksi` WHERE qty>0 AND `status`='Approve' 
        //                         AND Cabang='$cabang' AND tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`
                                    
        //                         )msk GROUP BY `Cabang`,`Produk`     
                                
        //                     )zz ON  xx.`Kode_Produk`=zz.Produk AND yy.Produk=zz.Produk AND yy.Cabang=zz.Cabang


        //                     LEFT JOIN(


        //                         SELECT Cabang,Produk,SUM(keluarRet) AS keluarRet, expd,batch FROM (
        //                         SELECT Cabang,Produk ,SUM((IFNULL(`Qty`,0) + IFNULL(`Bonus`,0))) AS keluarRet, 'BPB' AS 'ket',
        //                             DATE(`ExpDate`) AS expd ,`BatchNo` AS batch
        //                         FROM `trs_terima_barang_detail` WHERE `Status` IN ('Close','Closed') AND LEFT(IFNULL(NoDokumen,''),3) = 'BKB'
        //                             AND Cabang='$cabang' AND TglDokumen BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`

        //                         UNION ALL

        //                         SELECT 
        //                             Cabang,Produk,SUM((IFNULL(`Qty`,0) + IFNULL(`Bonus`,0))) AS keluarRet, 'RLK' AS 'ket',
        //                             DATE(`ExpDate`) AS expd ,`BatchNo` AS batch
        //                         FROM `trs_relokasi_kirim_detail`
        //                         WHERE Cabang='$cabang' AND Tgl_kirim BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`

        //                         UNION ALL

        //                         SELECT Cabang,produk,qty AS keluarRet,'KOR' AS 'ket',
        //                             DATE(exp_date) AS expd,batch
        //                         FROM `trs_mutasi_koreksi` WHERE qty<0 AND `status`='Approve' 
        //                         AND Cabang='$cabang' AND tanggal BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),
        //                                                 INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                 AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))
        //                             GROUP BY `Cabang`,`Produk`
                                    
        //                         )msk GROUP BY `Cabang`,`Produk`     
                                
        //                     )zzr ON  xx.`Kode_Produk`=zz.Produk AND yy.Produk=zz.Produk AND yy.Cabang=zz.Cabang



        //                     LEFT JOIN(

        //                             SELECT `Cabang`,`NamaProduk`,`Harga`, Produk,expd,batch,Pelanggan, 
        //                                 SUM(RRET) AS RRET, SUM(RS) AS RS, SUM(AP) AS AP, SUM(PBF) AS PBF, SUM(PEM) AS PEM, SUM(PKM) AS PKM, SUM(KL) AS KL, SUM(SW) AS SW,
        //                                 SUM(PE) AS PE, SUM(EP) AS EP, KodePelangganF,KodePelangganR
        //                             FROM (
        //                             SELECT  a.`Cabang`,a.`NamaProduk`,a.`Harga`,a.`KodeProduk` AS Produk,DATE(a.`ExpDate`) AS expd, a.`BatchNo` AS batch,a.Pelanggan,
        //                                 IFNULL(SUM(CASE WHEN a.`TipeDokumen`='Retur' THEN ABS(IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0)) ELSE 0 END),0) AS RRET, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('RS','RSUD','RSSA') AND b.`Prinsipal` != 'E-Kat' AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS RS, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('AP','APGR','APMT','APSG') AND b.`Prinsipal` != 'E-Kat' AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS AP, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('PBAK','PBF')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS PBF, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('DK','IN')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS PEM, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('DPKM')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS PKM, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('KL','KL1')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS KL, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('KLAD','KLAB','KLAL','MLOK','MKKT','MNAS','PT')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS SW,
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan`  IN ('TK','TK','TKOS','XTO')  AND b.`Prinsipal` != 'E-Kat'  AND a.`TipeDokumen`='Faktur' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS PE,
        //                                 IFNULL(SUM(CASE WHEN a.`Prinsipal` = 'E-Kat' THEN ((IFNULL(a.QtyFaktur,0)+IFNULL(a.BonusFaktur,0))) ELSE 0 END),0) AS EP,
        //                                 CASE WHEN a.TipeDokumen = 'Faktur' THEN CONCAT('(',a.Pelanggan,'), ',a.NamaPelanggan,' (FK)') ELSE '' END AS KodePelangganF,
        //                                 CASE WHEN a.TipeDokumen = 'Retur' THEN CONCAT('(',a.Pelanggan,'), ',a.NamaPelanggan,' (FM)') ELSE '' END AS KodePelangganR
        //                             FROM trs_faktur_detail a, mst_produk b
        //                             WHERE a.`TipeDokumen` IN ('Retur')
        //                                 AND a.`Cabang`='$cabang'
        //                                 AND  a.`KodeProduk`=b.`Kode_Produk`
        //                                 AND (a.`TglFaktur` BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                     AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))) 
        //                             GROUP BY a.`Cabang`,a.`KodeProduk`

        //                             UNION ALL


        //                             SELECT  a.`Cabang`,a.`NamaProduk`,a.`Harga`,a.`KodeProduk` AS Produk,DATE(a.`ExpDate`) AS expd, a.`BatchNo` AS batch,a.Pelanggan,
        //                                 IFNULL(SUM(CASE WHEN a.`Status`='Retur' THEN ABS(IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0)) ELSE 0 END),0) AS RRET, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('RS','RSUD','RSSA') AND b.`Prinsipal` != 'E-Kat' AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS RS, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('AP','APGR','APMT','APSG') AND b.`Prinsipal` != 'E-Kat' AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS AP, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('PBAK','PBF')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PBF, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('DK','IN')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PEM, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('DPKM')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PKM, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('KL','KL1')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS KL, 
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan` IN ('KLAD','KLAB','KLAL','MLOK','MKKT','MNAS','PT')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS SW,
        //                                 IFNULL(SUM(CASE WHEN a.`TipePelanggan`  IN ('TK','TK','TKOS','XTO')  AND b.`Prinsipal` != 'E-Kat'  AND (a.Status IN ('Open','Kirim','Terima')) THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS PE,
        //                                 IFNULL(SUM(CASE WHEN a.`Prinsipal` = 'E-Kat' THEN ((IFNULL(a.QtyDO,0)+IFNULL(a.BonusDO,0))) ELSE 0 END),0) AS EP,
        //                                 CASE WHEN ( a.`TipeDokumen` IN ('DO') AND ((a.Status IN ('Open','Kirim','Terima')) OR (a.Status ='Closed' AND IFNULL(a.status_retur,'') IN ('Y','R')))) 
        //                                     THEN CONCAT('(',a.Pelanggan,'), ',a.NamaPelanggan,' (DK)') ELSE '' END AS KodePelangganF,
        //                                 CASE WHEN ( a.`TipeDokumen` IN ('Retur') AND ((a.Status IN ('Retur')) OR (a.Status ='Closed' AND IFNULL(a.status_retur,'') IN ('Y','R')))) 
        //                                     THEN CONCAT('(',a.Pelanggan,'), ',a.NamaPelanggan,' (DM)') ELSE '' END AS KodePelangganR
        //                             FROM trs_delivery_order_sales_detail a, mst_produk b
        //                             WHERE a.`TipeDokumen` IN ('DO','Retur') AND ((a.Status IN ('Open','Kirim','Terima','Retur')) OR (a.Status ='Closed' AND IFNULL(a.status_retur,'') IN ('Y','R')))
        //                                 AND a.`Cabang`='$cabang'
        //                                 AND  a.`KodeProduk`=b.`Kode_Produk`
        //                                 AND (a.`TglDO` BETWEEN DATE_SUB(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)),INTERVAL DAY(LAST_DAY(DATE_ADD('$bydate', INTERVAL 0 MONTH)))-1 DAY) 
        //                                     AND LAST_DAY(DATE_ADD('$bydate', INTERVAL 2 MONTH))) 
        //                             GROUP BY a.`Cabang`,a.`KodeProduk`

        //                             )dSales GROUP BY `Cabang`,`Produk`


        //                     )xyz
        //                     ON xx.`Kode_Produk`=xyz.Produk AND yy.Produk=xyz.Produk AND zz.Produk=xyz.Produk AND yy.Cabang=xyz.Cabang AND zz.Cabang=xyz.Cabang
        //                     -- where ifnull(xx.`Farmalkes`,'') != ''
        //                     WHERE yy.stok IS NOT NULL
        //                          AND zz.masuk IS NOT NULL
        //                          AND xyz.RS IS NOT NULL
        //                          AND xyz.AP IS NOT NULL
        //                          AND xyz.PBF IS NOT NULL
        //                          AND xyz.PEM IS NOT NULL
        //                          AND xyz.SW IS NOT NULL
        //                     ORDER BY xx.`Farmalkes` DESC,xx.Produk
        //                     )

        //                 )a
        //               $limit 
                      


        //     ");


        // log_message('error','tipe :'.$tipe);
        // log_message('error','tipeD :'.$tipeD);
        // log_message('error','jenis :'.$jenis);
        // log_message('error','tipedok :'.$tipeDok);
        // log_message('error','kat1 :'.$kat1);
        // log_message('error','kat1Dat :'.$kat1Dat);
         // log_message('error',print_r($query->result(),true));


        return $query;
    }

    public function listsalesbypelangganbyprinsipal($search = null, $limit = null, $status = null, $tipe = null, $bydate = null, $bydated = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d')))); //kurang tanggal sebanyak 1 bulan
        $startdate = date('Y-m-01',strtotime($enddatemin));

        $query = $this->db->query("
                            SELECT trs_delivery_order_sales_detail.`NoDO` AS 'NoDokumen',
                                   CONCAT(trs_delivery_order_sales_detail.`Prinsipal`,' => ',trs_delivery_order_sales_detail.`Pelanggan`,'~',trs_delivery_order_sales_detail.`NamaPelanggan`) AS 'Prins',
                                   trs_delivery_order_sales_detail.`Cabang`,
                                   trs_delivery_order_sales_detail.`TglDO` AS 'Tgl',
                                   trs_delivery_order_sales_detail.`Prinsipal`,
                                   trs_delivery_order_sales_detail.`NamaPelanggan`,
                                   trs_delivery_order_sales_detail.`Salesman`,
                                   trs_delivery_order_sales_detail.`KodeProduk`,
                                   trs_delivery_order_sales_detail.`NamaProduk`,
                                   trs_delivery_order_sales_detail.`QtyDO` AS 'Qty',
                                   trs_delivery_order_sales_detail.`BonusDO` AS 'Bonus',
                                   trs_delivery_order_sales_detail.`Gross`,
                                   trs_delivery_order_sales_detail.`Potongan`,
                                   trs_delivery_order_sales_detail.`Value`,
                                   trs_delivery_order_sales_detail.TipeDokumen
                            FROM trs_delivery_order_sales_detail
                            WHERE trs_delivery_order_sales_detail.TipeDokumen = 'DO' AND 
                                  trs_delivery_order_sales_detail.status IN ( 'Open','Kirim') AND
                                  trs_delivery_order_sales_detail.Cabang = '".$this->cabang."'  $byStatus $search $bydated
                            UNION ALL   
                            SELECT trs_faktur_detail.`NoFaktur` AS 'NoDokumen',
                                   CONCAT(trs_faktur_detail.`Prinsipal`,' => ',trs_faktur_detail.`Pelanggan`,'~',trs_faktur_detail.`NamaPelanggan`) AS 'Prins',
                                   trs_faktur_detail.`Cabang`,
                                   trs_faktur_detail.`TglFaktur` AS 'Tgl',
                                   trs_faktur_detail.`Prinsipal`,
                                   trs_faktur_detail.`NamaPelanggan`,
                                   trs_faktur_detail.`Salesman`,
                                   trs_faktur_detail.`KodeProduk`,
                                   trs_faktur_detail.`NamaProduk`,
                                   trs_faktur_detail.`QtyFaktur` AS 'Qty',
                                   trs_faktur_detail.`BonusFaktur` AS 'Bonus',
                                   trs_faktur_detail.`Gross`,
                                   trs_faktur_detail.`Potongan`,
                                   trs_faktur_detail.`Value`,
                                   trs_faktur_detail.TipeDokumen
                            FROM trs_faktur_detail
                            WHERE trs_faktur_detail.TipeDokumen IN ('Faktur','Retur') AND 
                                  trs_faktur_detail.status != 'Batal' AND
                                  trs_faktur_detail.Cabang = '".$this->cabang."' $byStatus $search $bydate 
                            ORDER BY Tgl,Prinsipal,NamaPelanggan ASC $limit ; "
                                );     
        return $query;
    }

    public function listlaporanPTNew($search = null, $limit = null,$bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));
        $query = $this->db->query("
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           LapPT.NM_CABANG,
                                           LapPT.KODE_JUAL,
                                           LapPT.NODOKJDI,
                                           LapPT.NO_ACU,
                                           LapPT.KODE_PELANGGAN,
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           LapPT.JUDUL,
                                           LapPT.KODEPROD,
                                           LapPT.NAMAPROD,
                                           LapPT.UNIT,
                                           LapPT.PRINS,
                                           LapPT.BANYAK,
                                           LapPT.HARGA,
                                           LapPT.PRSNXTRA,
                                           LapPT.PRINPXTRA,
                                           LapPT.NILAI_PRINPXTRA,
                                           LapPT.TOT1,
                                           LapPT.NILJU,
                                           LapPT.PPN,
                                           LapPT.COGS,
                                           LapPT.KODESALES,
                                           LapPT.TGLDOK,
                                           LapPT.TGLEXP,
                                           LapPT.BATCH,
                                           LapPT.Area,
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           LapPT.Area,
                                           LapPT.DiscCab1,
                                           LapPT.DiscCab2,
                                           LapPT.DiscPrins1,
                                           LapPT.DiscPrins2,
                                           LapPT.CashDiskon,
                                           LapPT.DiscCabMax,
                                           LapPT.KetDiscCabMax,
                                           LapPT.DiscPrinsMax,
                                           LapPT.KetDiscPrinsMax,
                                           LapPT.NoDO,
                                           LapPT.Status,
                                           LapPT.TipeDokumen,
                                           LapPT.Tipe,
                                           LapPT.acu2,
                                           LapPT.NoBPB
                                    FROM 
                                      (SELECT 
                                              trs_faktur_detail.Cabang AS 'NM_CABANG',
                                              IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                              trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                                              IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                                              trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                                              trs_faktur_detail.Prinsipal AS 'JUDUL',
                                              trs_faktur_detail.KodeProduk AS 'KODEPROD',
                                              trs_faktur_detail.NamaProduk AS 'NAMAPROD',                                           
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur'
                                                   THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                                              END AS 'UNIT',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                                              END AS 'PRINS',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                                                   ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                                              END AS 'BANYAK',
                                              IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                                              IFNULL(trs_faktur_detail.DiscCab,0.0) + IFNULL(trs_faktur_detail.DiscCab_onf,0.0) AS 'PRSNXTRA',
                                              (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                               WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                               ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                                               END) AS 'PRINPXTRA',
                                              IFNULL(trs_faktur_detail.ValueDiscPrinsTotal,0.0) AS 'NILAI_PRINPXTRA',
                                              IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                                              IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                                              IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                                              IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                                              trs_faktur_detail.Salesman AS 'KODESALES',
                                              DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                                              IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                                              IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                                              trs_faktur_detail.Cabang AS 'Area',
                                              IFNULL(trs_faktur_detail.DiscCab_onf,0.0) AS 'DiscCab1',
                                              IFNULL(trs_faktur_detail.DiscCab,0.0) AS 'DiscCab2',
                                              IFNULL(trs_faktur_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                              IFNULL(trs_faktur_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                              trs_faktur_detail.CashDiskon,
                                              IFNULL(trs_faktur_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                              trs_faktur_detail.KetDiscCabMax,
                                              IFNULL(trs_faktur_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                              trs_faktur_detail.KetDiscPrinsMax,
                                              trs_faktur_detail.NoDO,
                                              trs_faktur.Status,
                                              trs_faktur.TipeDokumen,                                           
                                              'F' AS Tipe,
                                              trs_faktur_detail.acu2,
                                              trs_faktur_detail.NoBPB
                                          FROM trs_faktur_detail JOIN trs_faktur ON 
                                              trs_faktur.`NoFaktur` = trs_faktur_detail.`NoFaktur` AND 
                                              trs_faktur.`Cabang` = trs_faktur_detail.`Cabang`
                                          WHERE trs_faktur_detail.Cabang='".$this->cabang."' AND trs_faktur.Status NOT LIKE '%Batal%'
                                                AND MONTH(trs_faktur.TglFaktur) IN ('".$month."') 
                                                AND YEAR(trs_faktur.TglFaktur)='".$year."'                                                                    
                                    UNION ALL
                                    SELECT 
                                           a.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           NoDokumen AS 'NODOKJDI',
                                           IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                                           a.Pelanggan AS 'KODE_PELANGGAN',
                                           IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                                           a.KodeProduk AS 'KODEPROD',
                                           mst_produk.Produk AS 'NAMAPROD',
                                           0 AS 'UNIT',
                                           0 AS 'PRINS',
                                           0 AS 'BANYAK',
                                           0 AS 'HARGA',
                                           IFNULL(DscCab,0.0) AS 'PRSNXTRA',
                                           0 AS 'PRINPXTRA',
                                           0 AS 'NILAI_PRINPXTRA',
                                           0 AS 'TOT1',
                                           IFNULL(a.Jumlah,0) AS 'NILJU',
                                           0 AS PPN,
                                           0 AS 'COGS',
                                           trs_faktur.Salesman AS 'KODESALES',
                                           DATE(TglFaktur) AS 'TGLDOK',
                                           '' AS 'TGLEXP',
                                           '' AS 'BATCH',
                                           a.Cabang AS 'Area',
                                           '' AS DiscCab1,
                                           IFNULL(DscCab,0.0) AS DiscCab2,
                                           '' AS DiscPrins1,
                                           '' AS DiscPrins2,
                                           '' AS CashDiskon,
                                           '' AS DiscCabMax,
                                           '' AS KetDiscCabMax,
                                           '' AS DiscPrinsMax,
                                           '' AS KetDiscPrinsMax,
                                           trs_faktur.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'CD' AS Tipe,
                                           '' AS acu2,
                                           '' AS NoBPB
                                      FROM trs_faktur_cndn a,mst_produk,trs_faktur
                                      WHERE a.Cabang='".$this->cabang."' AND a.Status IN ('CNOK','DNOK')
                                            AND a.NoDokumen=trs_faktur.NoFaktur
                                            AND a.KodeProduk = mst_produk.Kode_Produk
                                            AND MONTH(TanggalCNDN)IN ('".$month."') 
                                            AND YEAR(TanggalCNDN)='".$year."' 
                                            
                                     UNION ALL
                                     SELECT 
                                            trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                                            IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                            trs_delivery_order_sales_detail.`NoDO` AS 'NODOKJDI',
                                            IFNULL(trs_delivery_order_sales_detail.`Acu`,'') AS 'NO_ACU',
                                            trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                                            Prinsipal AS 'JUDUL',
                                            KodeProduk AS 'KODEPROD',
                                        NamaProduk AS 'NAMAPROD',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                                               END AS 'UNIT',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                                               END AS 'PRINS',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                           THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                                               ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                                               END AS 'BANYAK',
                                            IFNULL(Harga,0) AS 'HARGA',
                                            IFNULL(DiscCab,0.0) + IFNULL(DiscCab_onf,0.0) AS 'PRSNXTRA',
                                            (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                             ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             END) AS 'PRINPXTRA',
                                             IFNULL(ValueDiscPrinsTotal,0.0)  AS 'NILAI_PRINPXTRA',
                                            IFNULL(Gross,0.0) AS 'TOT1',
                                            IFNULL(VALUE,0.0) AS 'NILJU',
                                            IFNULL(PPN,0.0) AS PPN,
                                            IFNULL(TotalCOGS,0.0) AS 'COGS',
                                            Salesman AS 'KODESALES',
                                            DATE(TglDO) AS 'TGLDOK',
                                            IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                                            IFNULL(BatchNo,'') AS 'BATCH',
                                            trs_delivery_order_sales_detail.Cabang AS 'Area',
                                            IFNULL(trs_delivery_order_sales_detail.DiscCab_onf,0.0) AS 'DiscCab1',
                                            IFNULL(trs_delivery_order_sales_detail.DiscCab,0.0) AS 'DiscCab2',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                            IFNULL(trs_delivery_order_sales_detail.CashDiskon,0.0) AS 'CashDiskon',
                                            IFNULL(trs_delivery_order_sales_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                            trs_delivery_order_sales_detail.KetDiscCabMax,
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                            trs_delivery_order_sales_detail.KetDiscPrinsMax,
                                            trs_delivery_order_sales_detail.NoDO,
                                            trs_delivery_order_sales_detail.Status,
                                            'DO' AS 'TipeDokumen',
                                            'D' AS Tipe,
                                            trs_delivery_order_sales_detail.acu2,
                                            trs_delivery_order_sales_detail.NoBPB
                                       FROM trs_delivery_order_sales_detail
                                     WHERE trs_delivery_order_sales_detail.Cabang='".$this->cabang."' 
                                           AND ((STATUS IN ('Open','Kirim','Terima','Retur')) OR (STATUS ='Closed' AND IFNULL(status_retur,'') ='Y'))
                                           AND MONTH(TglDO) IN ('".$month."') 
                                           AND YEAR(TglDO)='".$year."') AS LapPT 
                                     LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                 FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                               ON LapPT.KODE_PELANGGAN=mst_pelanggan.Kode
                                     LEFT JOIN (SELECT Region1,Cabang 
                                                 FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                               ON LapPT.NM_CABANG=mst_cabang.Cabang
                                     WHERE LapPT.NM_CABANG = '".$this->cabang."' 
                                     ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,mst_pelanggan.Pelanggan,LapPT.NODOKJDI
                                    "); 
        $row = [];
        foreach ($query->result() as $query){
            $prinsipal = $query->JUDUL;
            $prinsipal1 = $prinsipal;
            $NoBatchDoc = "";
            $NilaiKlaim = 0;
            if($prinsipal != 'ANDALAN'){
                $NoBatchDoc = $query->NoBPB;
            }else{
                if(substr($query->NoBPB,0,3) == 'BPB'){
                   $NoBatchDoc = $query->NoBPB; 
               }else{
                   if(substr($query->NoBPB,4,2) == '81'){
                    //relokasi
                        $NoBatchDoc = $query->NoBPB; 
                   }elseif(substr($query->NoBPB,4,2) == '12'){
                    //retur faktur
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$query->NoBPB."' limit 1");
                        // $NoBatchDoc = $cek->NoBPB;
                        if ($cek->num_rows() > 0) {
                            $cek = $cek->row();
                            if(substr($cek->NoBPB,0,3)== 'BPB'){
                                $NoBatchDoc = $cek->NoBPB;
                            }
                            elseif(substr($cek->NoBPB,4,2) == '12'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '07'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '19'){
                                 $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '20'){
                                $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                                $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                            }
                        }else{
                            $NoBatchDoc = '';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '07'){
                    //retur DO
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$query->NoBPB."' limit 1");

                        if ($cek->num_rows() > 0 ) {
                            $cek = $cek->row();

                            if(substr($cek->NoBPB,0,3)== 'BPB'){
                                $NoBatchDoc = $cek->NoBPB;
                            }
                            elseif(substr($cek->NoBPB,4,2) == '12'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '07'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '19'){
                                 $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '20'){
                                $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                                $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                            }
                            
                        }else{
                            $NoBatchDoc = "";
                        }
                        
                   }elseif(substr($query->NoBPB,4,2) == '19'){
                    //mutasi Koreksi
                        $cek = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                        
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoDocStok;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '20'){
                    //mutasi batch
                        $cek = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$query->NoBPB."' limit 1")->row();
                        
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoDocStok;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,0,5) == 'Saldo'){
                        $NoBatchDoc = $query->NoBPB.' ~ Stok Zoho';
                   }
               }
            }

            $Produk = $this->db->query("SELECT Produk FROM mst_produk WHERE Kode_Produk = '".$query->KODEPROD."' LIMIT 1")->row("Produk");
            if($prinsipal == 'LAIN-LAIN'){
               $prinsipal1 == 'GLOBAL HEALTH'; 
            }
            $cekprinsinfo = $this->db->query("select * from mst_prinsipal_info where prinsipal ='".$prinsipal1."'" );
            if($cekprinsinfo->num_rows() < 1){
                $NilaiKlaim = 0;
            }else{
                $prinsinfo = $cekprinsinfo->row();
                if($prinsinfo->Prinsipal == 'ARMOXINDO' or $prinsinfo->Prinsipal == 'DIPA REG' or $prinsinfo->Prinsipal == 'ESCOLAB'){
                    $NilaiKlaim = $query->NILAI_PRINPXTRA * $prinsinfo->Nominal_klaim;
                }else{
                    $NilaiKlaim = $query->NILAI_PRINPXTRA;
                }
                if($prinsinfo->Prinsipal == 'GLOBAL HEALTH'){
                    if($prinsinfo->Pabrik == 'LD' or $prinsinfo->Pabrik == 'MLF' or $prinsinfo->Pabrik == 'GHP'){
                       $NilaiKlaim = $query->NILAI_PRINPXTRA; 
                   }else{
                       $NilaiKlaim = 0;
                   }
                }
            }

          array_push($row,
                    [
                        'WIL'           => $query->WIL,
                        'NM_CABANG'     =>$query->NM_CABANG,
                        'KODE_JUAL'     =>$query->KODE_JUAL,
                        'NODOKJDI'      =>$query->NODOKJDI,
                        'NO_ACU'        =>$query->NO_ACU,
                        'KODE_PELANGGAN' =>$query->KODE_PELANGGAN,
                        'KODE_KOTA'     =>$query->KODE_KOTA,
                        'KODE_TYPE'     =>$query->KODE_TYPE,
                        'KODE_LANG'     =>$query->KODE_LANG,
                        'NAMA_LANG'     =>$query->NAMA_LANG,
                        'ALAMAT'        =>$query->ALAMAT,
                        'JUDUL'         =>$query->JUDUL,
                        'KODEPROD'      =>$query->KODEPROD,
                        // 'NAMAPROD'      =>$query->NAMAPROD,
                        'NAMAPROD'      =>$Produk,
                        'UNIT'          =>$query->UNIT,
                        'PRINS'         =>$query->PRINS,
                        'BANYAK'        =>$query->BANYAK,
                        'HARGA'         =>$query->HARGA,
                        'PRSNXTRA'      =>$query->PRSNXTRA,
                        'PRINPXTRA'     =>$query->PRINPXTRA,
                        'NILAI_PRINPXTRA'     =>$query->NILAI_PRINPXTRA,
                        'TOT1'          =>$query->TOT1,
                        'NILJU'         =>$query->NILJU,
                        'PPN'           =>$query->PPN,
                        'COGS'          =>$query->COGS,
                        'KODESALES'     =>$query->KODESALES,
                        'TGLDOK'        =>$query->TGLDOK,
                        'TGLEXP'        =>$query->TGLEXP,
                        'BATCH'         =>$query->BATCH,
                        'Area'          =>$query->Area,
                        'TELP'          =>$query->TELP,
                        'RAYON'         =>$query->RAYON,
                        'PANEL'         =>$query->PANEL,
                        'Area'          =>$query->Area,
                        'DiscCab1'      =>$query->DiscCab1,
                        'DiscCab2'      =>$query->DiscCab2,
                        'DiscPrins1'    =>$query->DiscPrins1,
                        'DiscPrins2'    =>$query->DiscPrins2,
                        'CashDiskon'    =>$query->CashDiskon,
                        'DiscCabMax'    =>$query->DiscCabMax,
                        'KetDiscCabMax' =>$query->KetDiscCabMax,
                        'DiscPrinsMax'  =>$query->DiscPrinsMax,
                        'KetDiscPrinsMax' =>$query->KetDiscPrinsMax,
                        'NoDO'          =>$query->NoDO,
                        'Status'        =>$query->Status,
                        'TipeDokumen'   =>$query->TipeDokumen,
                        'Tipe'          =>$query->Tipe,
                        'acu2'          =>$query->acu2,
                        'NoBPB'         =>$query->NoBPB,
                        'NoDocStok'     =>$NoBatchDoc,
                        'NilaiKlaimDiscPrins' => $NilaiKlaim
                    ]);
        }
        // log_message("error",print_r($row,true));
        return $row;
    }
    public function listlaporanPTByPrins($search = null, $limit = null,$bydate = null,$prinsipal=null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));
        $query = $this->db->query("
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           LapPT.NM_CABANG,
                                           LapPT.KODE_JUAL,
                                           LapPT.NODOKJDI,
                                           LapPT.NO_ACU,
                                           LapPT.KODE_PELANGGAN,
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           LapPT.JUDUL,
                                           LapPT.KODEPROD,
                                           LapPT.NAMAPROD,
                                           LapPT.UNIT,
                                           LapPT.PRINS,
                                           LapPT.BANYAK,
                                           LapPT.HARGA,
                                           LapPT.PRSNXTRA,
                                           LapPT.PRINPXTRA,
                                           LapPT.TOT1,
                                           LapPT.NILJU,
                                           LapPT.PPN,
                                           LapPT.COGS,
                                           LapPT.KODESALES,
                                           LapPT.TGLDOK,
                                           LapPT.TGLEXP,
                                           LapPT.BATCH,
                                           LapPT.Area,
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           LapPT.Area,
                                           LapPT.DiscPrins1,
                                           LapPT.DiscPrins2,
                                           LapPT.CashDiskon,
                                           LapPT.DiscCabMax,
                                           LapPT.KetDiscCabMax,
                                           LapPT.DiscPrinsMax,
                                           LapPT.KetDiscPrinsMax,
                                           LapPT.NoDO,
                                           LapPT.Status,
                                           LapPT.TipeDokumen,
                                           LapPT.Tipe,
                                           LapPT.acu2,
                                           LapPT.NoBPB
                                    FROM 
                                      (SELECT 
                                              trs_faktur_detail.Cabang AS 'NM_CABANG',
                                              IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                              trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                                              IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                                              trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                                              trs_faktur_detail.Prinsipal AS 'JUDUL',
                                              trs_faktur_detail.KodeProduk AS 'KODEPROD',
                                              trs_faktur_detail.NamaProduk AS 'NAMAPROD',                                           
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur'
                                                   THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                                              END AS 'UNIT',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                                              END AS 'PRINS',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                                                   ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                                              END AS 'BANYAK',
                                              IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                                              IFNULL(trs_faktur_detail.DiscCab,0.0) AS 'PRSNXTRA',
                                              (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                               WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                               ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                                               END) AS 'PRINPXTRA',
                                              IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                                              IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                                              IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                                              IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                                              trs_faktur_detail.Salesman AS 'KODESALES',
                                              DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                                              IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                                              IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                                              trs_faktur_detail.Cabang AS 'Area',
                                              IFNULL(trs_faktur_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                              IFNULL(trs_faktur_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                              trs_faktur_detail.CashDiskon,
                                              IFNULL(trs_faktur_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                              trs_faktur_detail.KetDiscCabMax,
                                              IFNULL(trs_faktur_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                              trs_faktur_detail.KetDiscPrinsMax,
                                              trs_faktur_detail.NoDO,
                                              trs_faktur.Status,
                                              trs_faktur.TipeDokumen,                                           
                                              'F' AS Tipe,
                                              trs_faktur_detail.acu2,
                                              trs_faktur_detail.NoBPB
                                          FROM trs_faktur_detail JOIN trs_faktur ON 
                                              trs_faktur.`NoFaktur` = trs_faktur_detail.`NoFaktur` AND 
                                              trs_faktur.`Cabang` = trs_faktur_detail.`Cabang`
                                          WHERE trs_faktur_detail.Cabang='".$this->cabang."' AND trs_faktur.Status NOT LIKE '%Batal%'
                                                AND MONTH(trs_faktur.TglFaktur) IN ('".$month."') 
                                                AND YEAR(trs_faktur.TglFaktur)='".$year."' and  
                                                trs_faktur_detail.Prinsipal = '".$prinsipal."'                                                                  
                                    UNION ALL
                                    SELECT 
                                           a.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           NoDokumen AS 'NODOKJDI',
                                           IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                                           a.Pelanggan AS 'KODE_PELANGGAN',
                                           IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                                           a.KodeProduk AS 'KODEPROD',
                                           mst_produk.Produk AS 'NAMAPROD',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN'
                                           THEN ABS(`Banyak`) *-1 
                                               ELSE ABS(`Banyak`) 
                                           END AS 'UNIT',
                                           0 AS 'PRINS',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN' 
                                               THEN ABS((IFNULL(Banyak,0) ))*-1 
                                               ELSE ABS((IFNULL(Banyak,0) ))
                                           END AS 'BANYAK',
                                           0 AS 'HARGA',
                                           IFNULL(DscCab,0.0) AS 'PRSNXTRA',
                                           0 AS 'PRINPXTRA',
                                           0 AS 'TOT1',
                                           IFNULL(a.Jumlah,0) AS 'NILJU',
                                           0 AS PPN,
                                           0 AS 'COGS',
                                           trs_faktur.Salesman AS 'KODESALES',
                                           DATE(TglFaktur) AS 'TGLDOK',
                                           '' AS 'TGLEXP',
                                           '' AS 'BATCH',
                                           a.Cabang AS 'Area',
                                           '' AS DiscPrins1,
                                           '' AS DiscPrins2,
                                           '' AS CashDiskon,
                                           '' AS DiscCabMax,
                                           '' AS KetDiscCabMax,
                                           '' AS DiscPrinsMax,
                                           '' AS KetDiscPrinsMax,
                                           trs_faktur.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'CD' AS Tipe,
                                           '' AS acu2,
                                           '' AS NoBPB
                                      FROM trs_faktur_cndn a,mst_produk,trs_faktur
                                      WHERE a.Cabang='".$this->cabang."' AND a.Status IN ('CNOK','DNOK')
                                            AND a.NoDokumen=trs_faktur.NoFaktur
                                            AND a.KodeProduk = mst_produk.Kode_Produk
                                            AND MONTH(TanggalCNDN)IN ('".$month."') 
                                            AND YEAR(TanggalCNDN)='".$year."' and 
                                            mst_produk.Prinsipal = '".$prinsipal."' 
                                            
                                     UNION ALL
                                     SELECT 
                                            trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                                            IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                            trs_delivery_order_sales_detail.`NoDO` AS 'NODOKJDI',
                                            IFNULL(trs_delivery_order_sales_detail.`Acu`,'') AS 'NO_ACU',
                                            trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                                            Prinsipal AS 'JUDUL',
                                            KodeProduk AS 'KODEPROD',
                                        NamaProduk AS 'NAMAPROD',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                                               END AS 'UNIT',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                                               END AS 'PRINS',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                           THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                                               ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                                               END AS 'BANYAK',
                                            IFNULL(Harga,0) AS 'HARGA',
                                            IFNULL(DiscCab,0.0) AS 'PRSNXTRA',
                                            (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                             ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             END) AS 'PRINPXTRA',
                                            IFNULL(Gross,0.0) AS 'TOT1',
                                            IFNULL(VALUE,0.0) AS 'NILJU',
                                            IFNULL(PPN,0.0) AS PPN,
                                            IFNULL(TotalCOGS,0.0) AS 'COGS',
                                            Salesman AS 'KODESALES',
                                            DATE(TglDO) AS 'TGLDOK',
                                            IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                                            IFNULL(BatchNo,'') AS 'BATCH',
                                            trs_delivery_order_sales_detail.Cabang AS 'Area',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                            IFNULL(trs_delivery_order_sales_detail.CashDiskon,0.0) AS 'CashDiskon',
                                            IFNULL(trs_delivery_order_sales_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                            trs_delivery_order_sales_detail.KetDiscCabMax,
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                            trs_delivery_order_sales_detail.KetDiscPrinsMax,
                                            trs_delivery_order_sales_detail.NoDO,
                                            trs_delivery_order_sales_detail.Status,
                                            'DO' AS 'TipeDokumen',
                                            'D' AS Tipe,
                                            trs_delivery_order_sales_detail.acu2,
                                            trs_delivery_order_sales_detail.NoBPB
                                       FROM trs_delivery_order_sales_detail
                                     WHERE trs_delivery_order_sales_detail.Cabang='".$this->cabang."' 
                                           AND ((STATUS IN ('Open','Kirim','Terima','Retur')) OR (STATUS ='Closed' AND IFNULL(status_retur,'') ='Y'))
                                           AND MONTH(TglDO) IN ('".$month."') 
                                           AND YEAR(TglDO)='".$year."' and 
                                           Prinsipal ='".$prinsipal."') AS LapPT 
                                     LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                 FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                               ON LapPT.KODE_PELANGGAN=mst_pelanggan.Kode
                                     LEFT JOIN (SELECT Region1,Cabang 
                                                 FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                               ON LapPT.NM_CABANG=mst_cabang.Cabang
                                     WHERE LapPT.NM_CABANG = '".$this->cabang."' 
                                     ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,mst_pelanggan.Pelanggan,LapPT.NODOKJDI
                                    "); 
        $row = [];
        foreach ($query->result() as $query){
            $prinsipal = $query->JUDUL;
            if($prinsipal != 'ANDALAN'){
                $NoBatchDoc = $query->NoBPB;
            }else{
                if(substr($query->NoBPB,0,3) == 'BPB'){
                   $NoBatchDoc = $query->NoBPB; 
               }else{
                   if(substr($query->NoBPB,4,2) == '81'){
                    //relokasi
                        $NoBatchDoc = $query->NoBPB; 
                   }elseif(substr($query->NoBPB,4,2) == '12'){
                    //retur faktur
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$query->NoBPB."' limit 1")->row();
                        // $NoBatchDoc = $cek->NoBPB;
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoBPB;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '07'){
                    //retur DO
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$query->NoBPB."' limit 1")->row();
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoBPB;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '19'){
                    //mutasi Koreksi
                        $cek = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                        
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoDocStok;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '20'){
                    //mutasi batch
                        $cek = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$query->NoBPB."' limit 1")->row();
                        
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoDocStok;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '19'){
                             $cek1 = $this->db->query("select ifnull(NoDocStok,'') as 'NoBPB' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '20'){
                            $cek1 = $this->db->query("select ifnull(NoDocStok_akhir,'') as 'NoBPB' from trs_mutasi_batch where produk = '".$query->KODEPROD."' and batchno_akhir = '".$query->BATCH."' and nodokumen = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,0,5) == 'Saldo'){
                        $NoBatchDoc = $query->NoBPB.' ~ Stok Zoho';
                   }
               }
            }
          array_push($row,
                    [
                        'WIL'           => $query->WIL,
                        'NM_CABANG'     =>$query->NM_CABANG,
                        'KODE_JUAL'     =>$query->KODE_JUAL,
                        'NODOKJDI'      =>$query->NODOKJDI,
                        'NO_ACU'        =>$query->NO_ACU,
                        'KODE_PELANGGAN' =>$query->KODE_PELANGGAN,
                        'KODE_KOTA'     =>$query->KODE_KOTA,
                        'KODE_TYPE'     =>$query->KODE_TYPE,
                        'KODE_LANG'     =>$query->KODE_LANG,
                        'NAMA_LANG'     =>$query->NAMA_LANG,
                        'ALAMAT'        =>$query->ALAMAT,
                        'JUDUL'         =>$query->JUDUL,
                        'KODEPROD'      =>$query->KODEPROD,
                        'NAMAPROD'      =>$query->NAMAPROD,
                        'UNIT'          =>$query->UNIT,
                        'PRINS'         =>$query->PRINS,
                        'BANYAK'        =>$query->BANYAK,
                        'HARGA'         =>$query->HARGA,
                        'PRSNXTRA'      =>$query->PRSNXTRA,
                        'PRINPXTRA'     =>$query->PRINPXTRA,
                        'TOT1'          =>$query->TOT1,
                        'NILJU'         =>$query->NILJU,
                        'PPN'           =>$query->PPN,
                        'COGS'          =>$query->COGS,
                        'KODESALES'     =>$query->KODESALES,
                        'TGLDOK'        =>$query->TGLDOK,
                        'TGLEXP'        =>$query->TGLEXP,
                        'BATCH'         =>$query->BATCH,
                        'Area'          =>$query->Area,
                        'TELP'          =>$query->TELP,
                        'RAYON'         =>$query->RAYON,
                        'PANEL'         =>$query->PANEL,
                        'Area'          =>$query->Area,
                        'DiscPrins1'    =>$query->DiscPrins1,
                        'DiscPrins2'    =>$query->DiscPrins2,
                        'CashDiskon'    =>$query->CashDiskon,
                        'DiscCabMax'    =>$query->DiscCabMax,
                        'KetDiscCabMax' =>$query->KetDiscCabMax,
                        'DiscPrinsMax'  =>$query->DiscPrinsMax,
                        'KetDiscPrinsMax' =>$query->KetDiscPrinsMax,
                        'NoDO'          =>$query->NoDO,
                        'Status'        =>$query->Status,
                        'TipeDokumen'   =>$query->TipeDokumen,
                        'Tipe'          =>$query->Tipe,
                        'acu2'          =>$query->acu2,
                        'NoBPB'         =>$query->NoBPB,
                        'NoDocStok'     =>$NoBatchDoc,
                    ]);
        }
        // log_message("error",print_r($row,true));
        return $row;
    }
    public function getexportPTNew($bydate = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = "";
        }

        $year = date('Y', strtotime($bydate)); 
        $month = date('m',strtotime($bydate));
        $query = $this->db->query("
                                    SELECT mst_cabang.Region1 AS 'WIL',
                                           LapPT.NM_CABANG,
                                           LapPT.KODE_JUAL,
                                           LapPT.NODOKJDI,
                                           LapPT.NO_ACU,
                                           LapPT.KODE_PELANGGAN,
                                           mst_pelanggan.Kota AS 'KODE_KOTA',
                                           mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                                           IFNULL(mst_pelanggan.Kode,'') AS 'KODE_LANG',
                                           mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                                           mst_pelanggan.Alamat AS 'ALAMAT',
                                           LapPT.JUDUL,
                                           LapPT.KODEPROD,
                                           LapPT.NAMAPROD,
                                           LapPT.UNIT,
                                           LapPT.PRINS,
                                           LapPT.BANYAK,
                                           LapPT.HARGA,
                                           LapPT.PRSNXTRA,
                                           LapPT.PRINPXTRA,
                                           LapPT.NILAI_PRINPXTRA,
                                           LapPT.TOT1,
                                           LapPT.NILJU,
                                           LapPT.PPN,
                                           LapPT.COGS,
                                           LapPT.KODESALES,
                                           LapPT.TGLDOK,
                                           LapPT.TGLEXP,
                                           LapPT.BATCH,
                                           LapPT.Area,
                                           mst_pelanggan.Telp AS TELP,
                                           mst_pelanggan.Rayon_1 AS RAYON,
                                           mst_pelanggan.Tipe_2 AS PANEL,
                                           LapPT.Area,
                                           LapPT.DiscPrins1,
                                           LapPT.DiscPrins2,
                                           LapPT.CashDiskon,
                                           LapPT.DiscCabMax,
                                           LapPT.KetDiscCabMax,
                                           LapPT.DiscPrinsMax,
                                           LapPT.KetDiscPrinsMax,
                                           LapPT.NoDO,
                                           LapPT.Status,
                                           LapPT.TipeDokumen,
                                           LapPT.Tipe,
                                           LapPT.acu2,
                                           LapPT.NoBPB
                                    FROM 
                                      (SELECT 
                                              trs_faktur_detail.Cabang AS 'NM_CABANG',
                                              IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                              trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                                              IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                                              trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                                              trs_faktur_detail.Prinsipal AS 'JUDUL',
                                              trs_faktur_detail.KodeProduk AS 'KODEPROD',
                                              trs_faktur_detail.NamaProduk AS 'NAMAPROD',                                           
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur'
                                                   THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                                              END AS 'UNIT',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                                                   ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                                              END AS 'PRINS',
                                              CASE trs_faktur_detail.TipeDokumen 
                                              WHEN 'Retur' 
                                                   THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                                                   ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                                              END AS 'BANYAK',
                                              IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                                              IFNULL(trs_faktur_detail.DiscCab,0.0) + IFNULL(trs_faktur_detail.DiscCab_onf,0.0) AS 'PRSNXTRA',
                                              (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                               WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                               ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                                               END) AS 'PRINPXTRA',
                                              IFNULL(trs_faktur_detail.ValueDiscPrinsTotal,0.0) AS 'NILAI_PRINPXTRA',
                                              IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                                              IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                                              IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                                              IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                                              trs_faktur_detail.Salesman AS 'KODESALES',
                                              DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                                              IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                                              IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                                              trs_faktur_detail.Cabang AS 'Area',
                                              IFNULL(trs_faktur_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                              IFNULL(trs_faktur_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                              trs_faktur_detail.CashDiskon,
                                              IFNULL(trs_faktur_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                              trs_faktur_detail.KetDiscCabMax,
                                              IFNULL(trs_faktur_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                              trs_faktur_detail.KetDiscPrinsMax,
                                              trs_faktur_detail.NoDO,
                                              trs_faktur.Status,
                                              trs_faktur.TipeDokumen,                                           
                                              'F' AS Tipe,
                                              trs_faktur_detail.acu2,
                                              trs_faktur_detail.NoBPB
                                          FROM trs_faktur_detail JOIN trs_faktur ON 
                                              trs_faktur.`NoFaktur` = trs_faktur_detail.`NoFaktur` AND 
                                              trs_faktur.`Cabang` = trs_faktur_detail.`Cabang`
                                          WHERE trs_faktur_detail.Cabang='".$this->cabang."' AND trs_faktur.Status NOT LIKE '%Batal%'
                                                AND MONTH(trs_faktur.TglFaktur) IN ('".$month."') 
                                                AND YEAR(trs_faktur.TglFaktur)='".$year."'                                                                    
                                    UNION ALL
                                    SELECT 
                                           a.Cabang AS 'NM_CABANG',
                                           IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                           NoDokumen AS 'NODOKJDI',
                                           IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                                           a.Pelanggan AS 'KODE_PELANGGAN',
                                           IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                                           a.KodeProduk AS 'KODEPROD',
                                           mst_produk.Produk AS 'NAMAPROD',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN'
                                           THEN ABS(`Banyak`) *-1 
                                               ELSE ABS(`Banyak`) 
                                           END AS 'UNIT',
                                           0 AS 'PRINS',
                                           CASE trs_faktur.TipeDokumen 
                                           WHEN 'CN' 
                                               THEN ABS((IFNULL(Banyak,0) ))*-1 
                                               ELSE ABS((IFNULL(Banyak,0) ))
                                           END AS 'BANYAK',
                                           0 AS 'HARGA',
                                           IFNULL(DscCab,0.0) AS 'PRSNXTRA',
                                           0 AS 'PRINPXTRA',
                                           0 AS 'NILAI_PRINPXTRA',
                                           0 AS 'TOT1',
                                           IFNULL(a.Jumlah,0) AS 'NILJU',
                                           0 AS PPN,
                                           0 AS 'COGS',
                                           trs_faktur.Salesman AS 'KODESALES',
                                           DATE(TglFaktur) AS 'TGLDOK',
                                           '' AS 'TGLEXP',
                                           '' AS 'BATCH',
                                           a.Cabang AS 'Area',
                                           '' AS DiscPrins1,
                                           '' AS DiscPrins2,
                                           '' AS CashDiskon,
                                           '' AS DiscCabMax,
                                           '' AS KetDiscCabMax,
                                           '' AS DiscPrinsMax,
                                           '' AS KetDiscPrinsMax,
                                           trs_faktur.NoDO,
                                           trs_faktur.Status,
                                           trs_faktur.TipeDokumen,
                                           'CD' AS Tipe,
                                           '' AS acu2,
                                           '' AS NoBPB
                                      FROM trs_faktur_cndn a,mst_produk,trs_faktur
                                      WHERE a.Cabang='".$this->cabang."' AND a.Status IN ('CNOK','DNOK')
                                            AND a.NoDokumen=trs_faktur.NoFaktur
                                            AND a.KodeProduk = mst_produk.Kode_Produk
                                            AND MONTH(TanggalCNDN)IN ('".$month."') 
                                            AND YEAR(TanggalCNDN)='".$year."' 
                                            
                                     UNION ALL
                                     SELECT 
                                            trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                                            IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                                            trs_delivery_order_sales_detail.`NoDO` AS 'NODOKJDI',
                                            IFNULL(trs_delivery_order_sales_detail.`Acu`,'') AS 'NO_ACU',
                                            trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                                            Prinsipal AS 'JUDUL',
                                            KodeProduk AS 'KODEPROD',
                                        NamaProduk AS 'NAMAPROD',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                                               END AS 'UNIT',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                               THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                                               ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                                               END AS 'PRINS',
                                            CASE trs_delivery_order_sales_detail.Status 
                                            WHEN 'Retur' 
                                           THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                                               ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                                               END AS 'BANYAK',
                                            IFNULL(Harga,0) AS 'HARGA',
                                            IFNULL(DiscCab,0.0) + IFNULL(DiscCab_onf,0.0) AS 'PRSNXTRA',
                                            (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                                             ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                                             END) AS 'PRINPXTRA',
                                             IFNULL(ValueDiscPrinsTotal,0.0)  AS 'NILAI_PRINPXTRA',
                                            IFNULL(Gross,0.0) AS 'TOT1',
                                            IFNULL(VALUE,0.0) AS 'NILJU',
                                            IFNULL(PPN,0.0) AS PPN,
                                            IFNULL(TotalCOGS,0.0) AS 'COGS',
                                            Salesman AS 'KODESALES',
                                            DATE(TglDO) AS 'TGLDOK',
                                            IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                                            IFNULL(BatchNo,'') AS 'BATCH',
                                            trs_delivery_order_sales_detail.Cabang AS 'Area',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                                            IFNULL(trs_delivery_order_sales_detail.CashDiskon,0.0) AS 'CashDiskon',
                                            IFNULL(trs_delivery_order_sales_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                                            trs_delivery_order_sales_detail.KetDiscCabMax,
                                            IFNULL(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                                            trs_delivery_order_sales_detail.KetDiscPrinsMax,
                                            trs_delivery_order_sales_detail.NoDO,
                                            trs_delivery_order_sales_detail.Status,
                                            'DO' AS 'TipeDokumen',
                                            'D' AS Tipe,
                                            trs_delivery_order_sales_detail.acu2,
                                            trs_delivery_order_sales_detail.NoBPB
                                       FROM trs_delivery_order_sales_detail
                                     WHERE trs_delivery_order_sales_detail.Cabang='".$this->cabang."' 
                                           AND ((STATUS IN ('Open','Kirim','Terima','Retur')) OR (STATUS ='Closed' AND IFNULL(status_retur,'') ='Y'))
                                           AND MONTH(TglDO) IN ('".$month."') 
                                           AND YEAR(TglDO)='".$year."') AS LapPT 
                                     LEFT JOIN (SELECT Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                                 FROM mst_pelanggan WHERE Cabang='".$this->cabang."') AS mst_pelanggan 
                                               ON LapPT.KODE_PELANGGAN=mst_pelanggan.Kode
                                     LEFT JOIN (SELECT Region1,Cabang 
                                                 FROM mst_cabang WHERE Cabang='".$this->cabang."') AS mst_cabang 
                                               ON LapPT.NM_CABANG=mst_cabang.Cabang
                                     WHERE LapPT.NM_CABANG = '".$this->cabang."' 
                                     ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,mst_pelanggan.Pelanggan,LapPT.NODOKJDI
                                    "); 
        $row = [];
        foreach ($query->result() as $query){
            $prinsipal = $query->JUDUL;
            if($prinsipal != 'ANDALAN'){
                $NoBatchDoc = $query->NoBPB;
            }else{
                if(substr($query->NoBPB,0,3) == 'BPB'){
                   $NoBatchDoc = $query->NoBPB; 
               }else{
                   if(substr($query->NoBPB,4,2) == '81'){
                    //relokasi
                        $NoBatchDoc = $query->NoBPB; 
                   }elseif(substr($query->NoBPB,4,2) == '12'){
                    //retur faktur
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$query->NoBPB."' limit 1");
                        // $NoBatchDoc = $cek->NoBPB;
                        if ($cek->num_rows() > 0) {
                            $cek = $cek->row();
                            if(substr($cek->NoBPB,0,3)== 'BPB'){
                                $NoBatchDoc = $cek->NoBPB;
                            }
                            elseif(substr($cek->NoBPB,4,2) == '12'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,4,2) == '07'){
                                $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                                $NoBatchDoc = $cek1->NoBPB;
                            }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                                $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                            }
                        }else{
                            $NoBatchDoc = "";
                        }
                   }elseif(substr($query->NoBPB,4,2) == '07'){
                    //retur DO
                        $cek = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$query->NoBPB."' limit 1")->row();
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoBPB;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,4,2) == '19'){
                    //mutasi Koreksi
                        $cek = $this->db->query("select ifnull(NoDocStok,'') as 'NoDocStok' from trs_mutasi_koreksi where produk = '".$query->KODEPROD."' and batch = '".$query->BATCH."' and no_koreksi = '".$query->NoBPB."' limit 1")->row();
                        
                        if(substr($cek->NoBPB,0,3)== 'BPB'){
                            $NoBatchDoc = $cek->NoDocStok;
                        }
                        elseif(substr($cek->NoBPB,4,2) == '12'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_faktur_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoFaktur = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,4,2) == '07'){
                            $cek1 = $this->db->query("select ifnull(NoBPB,'') as 'NoBPB' from trs_delivery_order_sales_detail where KodeProduk = '".$query->KODEPROD."' and BatchNo = '".$query->BATCH."' and NoDO = '".$cek->NoBPB."' limit 1")->row();
                            $NoBatchDoc = $cek1->NoBPB;
                        }elseif(substr($cek->NoBPB,0,5) == 'Saldo'){
                            $NoBatchDoc = $cek->NoBPB.' ~ Stok Zoho';
                        }
                   }elseif(substr($query->NoBPB,0,5) == 'Saldo'){
                        $NoBatchDoc = $query->NoBPB.' ~ Stok Zoho';
                   }
               }
            }

            $Produk = $this->db->query("SELECT Produk FROM mst_produk WHERE Kode_Produk = '".$query->KODEPROD."' LIMIT 1")->row("Produk");
            
          array_push($row,
                    [
                        'WIL'           => $query->WIL,
                        'NM_CABANG'     =>$query->NM_CABANG,
                        'KODE_JUAL'     =>$query->KODE_JUAL,
                        'NODOKJDI'      =>$query->NODOKJDI,
                        'NO_ACU'        =>$query->NO_ACU,
                        'KODE_PELANGGAN' =>$query->KODE_PELANGGAN,
                        'KODE_KOTA'     =>$query->KODE_KOTA,
                        'KODE_TYPE'     =>$query->KODE_TYPE,
                        'KODE_LANG'     =>$query->KODE_LANG,
                        'NAMA_LANG'     =>$query->NAMA_LANG,
                        'ALAMAT'        =>$query->ALAMAT,
                        'JUDUL'         =>$query->JUDUL,
                        'KODEPROD'      =>$query->KODEPROD,
                        // 'NAMAPROD'      =>$query->NAMAPROD,
                        'NAMAPROD'      =>$Produk,
                        'UNIT'          =>$query->UNIT,
                        'PRINS'         =>$query->PRINS,
                        'BANYAK'        =>$query->BANYAK,
                        'HARGA'         =>$query->HARGA,
                        'PRSNXTRA'      =>$query->PRSNXTRA,
                        'PRINPXTRA'     =>$query->PRINPXTRA,
                        'NILAI_PRINPXTRA'     =>$query->NILAI_PRINPXTRA,
                        'TOT1'          =>$query->TOT1,
                        'NILJU'         =>$query->NILJU,
                        'PPN'           =>$query->PPN,
                        'COGS'          =>$query->COGS,
                        'KODESALES'     =>$query->KODESALES,
                        'TGLDOK'        =>$query->TGLDOK,
                        'TGLEXP'        =>$query->TGLEXP,
                        'BATCH'         =>$query->BATCH,
                        'Area'          =>$query->Area,
                        'TELP'          =>$query->TELP,
                        'RAYON'         =>$query->RAYON,
                        'PANEL'         =>$query->PANEL,
                        'Area'          =>$query->Area,
                        'DiscPrins1'    =>$query->DiscPrins1,
                        'DiscPrins2'    =>$query->DiscPrins2,
                        'CashDiskon'    =>$query->CashDiskon,
                        'DiscCabMax'    =>$query->DiscCabMax,
                        'KetDiscCabMax' =>$query->KetDiscCabMax,
                        'DiscPrinsMax'  =>$query->DiscPrinsMax,
                        'KetDiscPrinsMax' =>$query->KetDiscPrinsMax,
                        'NoDO'          =>$query->NoDO,
                        'Status'        =>$query->Status,
                        'TipeDokumen'   =>$query->TipeDokumen,
                        'Tipe'          =>$query->Tipe,
                        'acu2'          =>$query->acu2,
                        'NoBPB'         =>$query->NoBPB,
                        'NoDocStok'     =>$NoBatchDoc,
                    ]);
        }
        return $row;
    }

    function list_invday($bySearch=null,$byLimit=null,$cek=null,$tgl=null){
        $where = "";
        
        if ($cek != '') {
            $where = "AND SAwa$tgl > 0 ";
        }
        $data = $this->db->query("  SELECT * FROM trs_invday_det WHERE Tahun = YEAR(CURDATE()) AND MONTH(CURDATE()) $where $bySearch ORDER BY KodeProduk $byLimit");

        return $data;
    }
}
