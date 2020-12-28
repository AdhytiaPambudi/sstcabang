<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_main extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    // SUPPLIER
    public function listSupplier()
    {   
        $query = $this->db->query("select * from mst_supplier where id and Supplier like '%id%' group by id")->result();

        return $query;
    }

    // CABANG
    public function Cabang()
    {   
        $query = $this->db->query("select *,CURDATE() as 'server_date' from mst_cabang  order by Cabang asc")->result();

        return $query;
    }
     public function CabangDev()
    {   
        $query = $this->db->query("select *,CURDATE() as 'server_date' from mst_cabang  where Cabang = '".$this->cabang."' order by Cabang asc")->result();

        return $query;
    }

    // GL Bank
    public function gl_bank()
    {   
        $query = $this->db->query("select Bank,`Nama perkiraan` as 'perkiraan',Buku from mst_gl_bank where Cabang = '".$this->cabang."'")->result();
        return $query;
    }

    // PRINSIPAL
    public function Prinsipal()
    {   
        $query = $this->db->query("SELECT Prinsipal, Supplier1 AS Supplier, Supplier2 AS Supplier2 FROM mst_prinsipal WHERE Prinsipal NOT IN 
                    ('KARINDO','LASERIN','ADITAMA','BINTANG KK','NYCOMED','AXION') AND prinsipal NOT LIKE '%MECOSIN%' AND prinsipal NOT LIKE '%CORSA%'  ORDER BY Prinsipal ASC 
                    ")->result();

        return $query;
    }

    // SUPPLIER
    public function Supplier()
    {   
        $query = $this->db->query("select Supplier from mst_supplier order by Supplier asc")->result();

        return $query;
    }

    // PRODUK
    public function Produk()
    {   
        $query = $this->db->query("select Kode_Produk, Produk, Supplier1,Supplier2 from mst_produk order by Produk asc")->result();
        return $query;
    }

    public function ProdukInStok()
    {   
        $query = $this->db->query("SELECT trs_invsum.KodeProduk AS Kode_Produk, mst_produk.Produk AS Produk, KodePrinsipal,trs_invsum.NamaPrinsipal,mst_prinsipal.Supplier1 AS 'Supplier', trs_invsum.UnitCOGS 
                                FROM trs_invsum LEFT JOIN mst_prinsipal ON trs_invsum.`NamaPrinsipal` = mst_prinsipal.`Prinsipal` left join mst_produk on trs_invsum.KodeProduk = mst_produk.Kode_Produk
                                WHERE trs_invsum.Cabang = '".$this->cabang."' AND trs_invsum.Gudang = 'Baik' and unitstok > 0  and trs_invsum.tahun ='".date('Y')."' ORDER BY trs_invsum.NamaProduk ASC")->result();
        return $query;
    }

    public function ProdukInkoreksi()
    {   
        $query = $this->db->query("SELECT distinct trs_invsum.KodeProduk AS Kode_Produk, mst_produk.Produk AS Produk, KodePrinsipal,trs_invsum.NamaPrinsipal,mst_prinsipal.Supplier1 AS 'Supplier'
                                FROM trs_invsum LEFT JOIN mst_prinsipal ON trs_invsum.`NamaPrinsipal` = mst_prinsipal.`Prinsipal` left join mst_produk on trs_invsum.KodeProduk = mst_produk.Kode_Produk
                                WHERE trs_invsum.Cabang = '".$this->cabang."' and trs_invsum.tahun ='".date('Y')."' ORDER BY trs_invsum.NamaProduk ASC")->result();
        return $query;
    }

    public function ProdukInMutasi()
    {   
        $query = $this->db->query("SELECT trs_invsum.KodeProduk AS Kode_Produk, mst_produk.Produk AS Produk, KodePrinsipal,trs_invsum.NamaPrinsipal,mst_prinsipal.Supplier1 AS 'Supplier', trs_invsum.UnitCOGS 
                                FROM trs_invsum LEFT JOIN mst_prinsipal ON trs_invsum.`NamaPrinsipal` = mst_prinsipal.`Prinsipal` left join mst_produk on trs_invsum.KodeProduk = mst_produk.Kode_Produk
                                WHERE trs_invsum.Cabang = '".$this->cabang."' AND trs_invsum.Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."'  ORDER BY trs_invsum.NamaProduk ASC")->result();
        return $query;
    }

    // public function ProdukInStok()
    // {   
    //     $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk, KodePrinsipal, NamaPrinsipal,Supplier1 as 'Supplier', UnitCOGS from trs_invsum join mst_prinsipal on mst_prinsipal.Prinsipal = trs_invsum.KodePrinsipal where trs_invsum.Cabang = '".$this->cabang."' and trs_invsum.Gudang = 'Baik' and trs_invsum.UnitStok > 0 order by trs_invsum.NamaProduk asc")->result();

    //     return $query;
    // }

    // Batch
    public function Batch($kode = NULL,$gudang = "Baik")
    {   
         $query = $this->db->query("select BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."'  and Gudang = '".$gudang."' and UnitStok > 0 and Tahun ='".date('Y')."' order by ExpDate asc")->result();
        return $query;
    }

    public function BatchKoreksi($kode = NULL,$gudang = null)
    {   

         $query = "";
        if($gudang == "" or empty($gudang)){
            $query = $this->db->query("select BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and Tahun ='".date('Y')."'  order by ExpDate asc")->result();
        }else{
            if(substr($gudang,0,5)=='Retur'){
                $gudang = 'Retur Supplier';
            }
            $query = $this->db->query("select BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."'  and Gudang = '".$gudang."' and Tahun ='".date('Y')."' order by ExpDate asc")->result();
        }
        return $query;
    }

    // Batch
    public function AllBatch($kode = NULL)
    {   
        $query = $this->db->query("select Distinct BatchNo, UnitStok, ExpDate,NoDokumen from trs_invdet where Cabang = '".$this->cabang."' and Gudang = 'Baik' and Tahun ='".date('Y')."' order by ExpDate asc")->result();

        return $query;
    }

    // Kode Lama Cabang
    public function kodeLamaCabang()
    {   
        $query = $this->db->query("select Kode_Lama from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
        // $query = $query->Kode_Lama;
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

    // Data Pelanggan
    public function Pelanggan()
    {   
        $query = $this->db->query("select Kode, Pelanggan from mst_pelanggan where Cabang = '".$this->cabang."'")->result();
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

    public function dataarea(){
         $query = $this->db->query("select * from mst_area where Area_Cabang = '".$this->cabang."' order by Area")->result();
    }

    public function datakota(){
         $query = $this->db->query("select * from mst_kota order by Provinsi,Kota asc")->result();
    }

    public function datarayon(){
         $query = $this->db->query("select * from mst_rayon  where Cabang = '".$this->cabang."' order by Cabang,Rayon asc")->result();
    }

    public function datawilayah(){
         $query = $this->db->query("select * from mst_rayon  where Cabang = '".$this->cabang."' order by Cabang,Nama_Wilayah asc")->result();
    }

    public function datatipepelanggan(){
         $query = $this->db->query("select * from mst_tipepelanggan order by Tipe,Nama_Tipe asc")->result();
    }

    public function noDokumen($dok = null)
    {
        $kodeLamaCabang = $this->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->kodeDokumen($dok);
        $counter = $this->counter($dok);
        if ($counter) 
        {
            $no = $counter->Counter + 1;
        }
        else
        {    $no = 1000001;}
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,5,"0",STR_PAD_LEFT);
        return $noDokumen;
    }

    public function noDokumenTemp($dok = null)
    {
        $kodeLamaCabang = $this->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $counter = $this->counter($dok);
        if ($counter) 
        {
            $no = $counter->Counter + 1;
        }
        else
            $no = 1000001;

        $noDokumen = date("y").$kodeLamaCabang.$dok.str_pad($no,5,"0",STR_PAD_LEFT);
        return $noDokumen;
    }

    public function saveNoDokumen($dok=NULL, $no=NULL)
    {
        if ($no == 1000001) {
            $this->db->set("Aplikasi", $dok); 
            $this->db->set("Cabang", $this->cabang); 
            $this->db->set("Counter", $no); 
            $valid =  $this->db->insert("mst_counter");
        }
        else{         
            $this->db->set("Counter", $no);        
            $this->db->where("Aplikasi", $dok);
            $this->db->where("Cabang", $this->cabang); 
            $valid =  $this->db->update("mst_counter");
        }
    }

    public function Produk_uom($kode)
    {   
        $query = $this->db->query("select * from mst_produk where Kode_Produk = '".$kode."'")->row('Satuan');
        return $query;
    }

    public function userlogin(){
        $query = $this->db->query("select sum(userLogged) as 'totaluser' from mst_user")->result();
        return $query;
    }

    public function getdateclosing(){
        $query = $this->db->query("select tgl_stok_closing,tgl_settlement_month,tgl_daily_closing,flag_trans from mst_closing where Cabang ='".$this->cabang."'")->result();
        return $query; 
    }
    public function closing_trans($tgl = NULL){
        $query = $this->db->query("select tgl_closing from mst_closing where Cabang ='".$this->cabang."'")->result();
        $tgl_closing = $query[0]->tgl_closing;
         if(strtotime($tgl_closing) != strtotime($tgl)){
            $result = 0;
         }else{
            $result = 1;
         }
        return $result;
    }

    public function getstokInTrans($produk = NULL)
    {

        $timestamp    = strtotime(date('Y-m-d'));
        $first_date   = date('Y-m-01', $timestamp);
        $end_date     = date('Y-m-d');
        $getmonth     = date('m',$timestamp);
        $query_sawal = $this->db->query("
                                  SELECT KodeProduk,
                                  (case '".$getmonth."' 
                                   when '01' then sum(SAwal01)
                                   when '02' then sum(SAwal02)
                                   when '03' then sum(SAwal03)
                                   when '04' then sum(SAwal04)
                                   when '05' then sum(SAwal05)
                                   when '06' then sum(SAwal06)
                                   when '07' then sum(SAwal07)
                                   when '08' then sum(SAwal08)
                                   when '09' then sum(SAwal09)
                                   when '10' then sum(SAwal10)
                                   when '11' then sum(SAwal11)
                                   when '12' then sum(SAwal12)
                                   else 0 end) AS 'saldo_awal' 
                                  FROM trs_invsum 
                                  WHERE Cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and tahun ='".date('Y')."'
                                  GROUP BY KodeProduk")->result();

        $query_terima = $this->db->query("select sum(ifnull(Qty,0)) AS 'qty'
                                  FROM trs_terima_barang_detail
                                  WHERE trs_terima_barang_detail.Produk = '".$produk ."' and 
                                        TglDokumen between '".$first_date."' and '".$end_date."'")->result();

        $query_koreksi_plus = $this->db->query("select sum(ifnull(qty,0)) AS 'qty'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."' and 
                                        tanggal between '".$first_date."' and '".$end_date."'")->result();

        $query_relokasi_terima = $this->db->query("SELECT sum(ifnull(Qty,0)) AS 'qty'
                                  FROM  trs_relokasi_terima_detail 
                                  WHERE   Qty > 0 AND
                                        Status = 'Terima' and
                                        Produk = '".$produk ."' and 
                                        Tgl_terima between '".$first_date."' and '".$end_date."'")->result();

        $query_retur = $this->db->query("SELECT sum(ifnull(QtyFaktur,0)) AS 'qty'
                                  FROM trs_faktur_detail 
                                  WHERE TipeDokumen ='Retur' AND 
                                        KodeProduk = '".$produk ."' and 
                                        TglFaktur between '".$first_date."' and '".$end_date."'")->result();

        $query_do = $this->db->query("SELECT sum(ifnull(QtyDO,0)) AS 'qty'
                                  FROM trs_delivery_order_sales_detail, trs_delivery_order_sales
                                  WHERE trs_delivery_order_sales.NoDO = trs_delivery_order_sales_detail.NoDo and
                                        trs_delivery_order_sales.Cabang = trs_delivery_order_sales_detail.Cabang and  
                                        trs_delivery_order_sales.Status != 'Batal' and    
                                         trs_delivery_order_sales_detail.KodeProduk = '".$produk ."' and
                                        trs_delivery_order_sales.TglDO between '".$first_date."' and '".$end_date."'")->result();

        $query_relokasi_kirim = $this->db->query("SELECT sum(ifnull(Qty,0)) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail,trs_relokasi_kirim_header
                                  WHERE  trs_relokasi_kirim_header.No_Relokasi =  trs_relokasi_kirim_detail.No_Relokasi and 
                                        trs_relokasi_kirim_header.Cabang =  trs_relokasi_kirim_detail.Cabang and
                                        trs_relokasi_kirim_detail.Qty > 0 AND
                                        trs_relokasi_kirim_header.Status_kiriman = 'Kirim' and
                                        trs_relokasi_kirim_detail.Produk = '".$produk ."' and 
                                        trs_relokasi_kirim_detail.Tgl_kirim between '".$first_date."' and '".$end_date."'")->result();

        $query_koreksi_min = $this->db->query("SELECT sum(ifnull(qty,0)) AS 'qty'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."'and 
                                        tanggal between '".$first_date."' and '".$end_date."'")->result();

        $sawal              = $query_sawal[0]->saldo_awal;
        $terima             = $query_terima[0]->qty;
        $plus               = $query_koreksi_plus[0]->qty;
        $relokasi_terima    = $query_relokasi_terima[0]->qty;
        $retur              = $query_retur[0]->qty;
        $do                 = $query_do[0]->qty;
        $relokasi_kirim     = $query_relokasi_kirim[0]->qty;
        $min                = $query_koreksi_min[0]->qty;

        return $stok = $sawal + $terima + $plus + $relokasi_terima + $retur - $do - $relokasi_kirim - $min;
    }

    public function karyawan()
    {   
        $query = $this->db->query("select Kode, Nama from mst_karyawan where  Cabang = '".$this->cabang."' and Status = 'Aktif' and jabatan not in ('BM','Supervisor')")->result();
        return $query;
    }

    public function cek_tglstoktrans(){
        $query = $this->db->query("select tgl_stok_closing from mst_closing where Cabang ='".$this->cabang."'")->result();
        $daynumber = date('d');
        if($daynumber <= 5){
            $curclose  = date('Y-m-d',strtotime("-10 days"));
        }else if($daynumber > 5 and $daynumber < 15){
            $curclose  = date('Y-m-d',strtotime("-15 days"));
        }else if($daynumber >= 15 and $daynumber < 20){
            $curclose  = date('Y-m-d',strtotime("-21 days"));
        }else if($daynumber >= 20 and $daynumber < 25){
            $curclose  = date('Y-m-d',strtotime("-26 days"));
        }else if($daynumber >= 25){
            $curclose  = date('Y-m-d',strtotime("-32 days"));
        }
        $curclose = date('Y-m-t',strtotime($curclose));
        $tgl_closing = $query[0]->tgl_stok_closing;
         if(strtotime($curclose) != strtotime($tgl_closing)){
            $result = 0;
         }else{
            $result = 1;
         }
        return $result;
    }
    
    public function cek_tglsettlement(){
        $query = $this->db->query("select tgl_settlement_month from mst_closing where Cabang ='".$this->cabang."'")->result();
        $currdate  = date('Y-m-t');
        $tgl_settlement = $query[0]->tgl_settlement_month;
         if(strtotime($currdate) == strtotime($tgl_settlement)){
            $result = 0;
         }else{
            $result = 1;
         }
        return $result;
    }

    public function getcogssummary(){
        $month = date('m');
        $this->db->query("UPDATE trs_invdet
                                SET trs_invdet.`ValueStok` = trs_invdet.`UnitCOGS` * trs_invdet.`UnitStok`
                                WHERE trs_invdet.`UnitStok` > 0;");
        $this->db->query("UPDATE trs_invdet
                                SET trs_invdet.`ValueStok` = 0,
                                    trs_invdet.`UnitStok` = 0
                                WHERE trs_invdet.`UnitStok` <= 0;");
        $this->db->query("UPDATE trs_invsum
                          INNER JOIN (
                          SELECT trs_invdet.`KodeProduk`,
                                 trs_invdet.`Gudang`,
                                 TRUNCATE(SUM(trs_invdet.`ValueStok`),2) AS 'valuestok'
                          FROM   trs_invdet
                          WHERE  trs_invdet.`UnitStok` != 0
                          AND trs_invdet.Tahun = YEAR(CURDATE())
                          GROUP BY trs_invdet.`KodeProduk`,
                                 trs_invdet.`Gudang` ) AS detail
                          ON detail.KodeProduk = trs_invsum.`KodeProduk` AND 
                             detail.Gudang = trs_invsum.`Gudang`
                          SET trs_invsum.`ValueStok` = detail.valuestok
                          WHERE trs_invsum.Tahun = YEAR(CURDATE());");
        $this->db->query("UPDATE trs_invsum
                                SET trs_invsum.`UnitCOGS` = trs_invsum.`ValueStok` /trs_invsum.`UnitStok`
                                WHERE  trs_invsum.Tahun = YEAR(CURDATE());");
        // $this->db->query("UPDATE trs_invsum
        //                     SET trs_invsum.`ValueStok` = trs_invsum.`UnitCOGS` * trs_invsum.`UnitStok`
        //                     WHERE trs_invsum.`UnitStok` > 0;
        //                     ");
        // $this->db->query("UPDATE trs_invsum
        //                     SET trs_invsum.`ValueStok` = 0
        //                     WHERE trs_invsum.`UnitStok` <= 0;
        //                     ");
        // $this->db->query("UPDATE trs_invsum
        //                     SET trs_invsum.`UnitStok` = 0
        //                     WHERE trs_invsum.`UnitStok` <= 0;
        //                     ");
        // $this->db->query("UPDATE trs_invsum
        //                     SET trs_invsum.SAwal".$month." = 0
        //                     WHERE trs_invsum.SAwal".$month." <= 0;
        //                 ");

        // $this->db->query("UPDATE trs_invdet
        //                     SET trs_invdet.`ValueStok` = trs_invdet.`UnitCOGS` * trs_invdet.`UnitStok`
        //                     WHERE trs_invdet.`UnitStok` > 0;
        //                     ");
        // $this->db->query("UPDATE trs_invdet
        //                     SET trs_invdet.`ValueStok` = 0
        //                     WHERE trs_invdet.`UnitStok` <= 0;
        //                     ");
        // $this->db->query("UPDATE trs_invdet
        //                     SET trs_invdet.`UnitStok` = 0
        //                     WHERE trs_invdet.`UnitStok` <= 0;
        //                     ");
        // $this->db->query("UPDATE trs_invdet
        //                     SET trs_invdet.SAwa".$month." = 0
        //                     WHERE trs_invdet.SAwa".$month." <= 0;
        //                 ");
        // $this->db->query("UPDATE trs_invsum
        //                   INNER JOIN (
        //                   SELECT trs_invdet.`KodeProduk`,
        //                          trs_invdet.`Gudang`,
        //                          TRUNCATE(AVG(trs_invdet.`UnitCOGS`),2) AS 'unitcogs'
        //                   FROM   trs_invdet
        //                   WHERE  trs_invdet.`UnitStok` != 0
        //                   AND trs_invdet.Tahun = YEAR(CURDATE())
        //                   GROUP BY trs_invdet.`KodeProduk`,
        //                          trs_invdet.`Gudang` ) AS detail
        //                   ON detail.KodeProduk = trs_invsum.`KodeProduk` AND 
        //                      detail.Gudang = trs_invsum.`Gudang`
        //                   SET trs_invsum.`UnitCOGS` = detail.unitcogs
        //                   WHERE trs_invsum.Tahun = YEAR(CURDATE())
        //                   ;");
      
    }

    public function cek_tglstokdaily(){
        $query = $this->db->query("select tgl_daily_closing,
                                          ifnull(flag_aktif,'N') as 'flag_aktif',
                                          ifnull(flag_trans,'N') as 'flag_trans',
                                          ifnull(flag_stok,'N') as 'flag_stok'
                                    from mst_closing 
                                    where Cabang ='".$this->cabang."' limit 1")->row();
        return $query;
    }
    public function cek_tglstoktranspusat(){
        $this->db2 = $this->load->database('pusat', TRUE);
        if ($this->db2->conn_id == TRUE) {
            $query = $this->db2->query("select tgl_stok_closing from mst_closing where GudangPusat ='Pusat3'")->result();
        }
        $daynumber = date('d');
        if($daynumber <= 5){
            $curclose  = date('Y-m-d',strtotime("-10 days"));
        }else if($daynumber > 5 and $daynumber < 15){
            $curclose  = date('Y-m-d',strtotime("-15 days"));
        }else if($daynumber >= 15 and $daynumber < 20){
            $curclose  = date('Y-m-d',strtotime("-21 days"));
        }else if($daynumber >= 20 and $daynumber < 25){
            $curclose  = date('Y-m-d',strtotime("-26 days"));
        }else if($daynumber >= 25){
            $curclose  = date('Y-m-d',strtotime("-32 days"));
        }
        $curclose = date('Y-m-t',strtotime($curclose));
        $tgl_closing = $query[0]->tgl_stok_closing;
         if(strtotime($curclose) != strtotime($tgl_closing)){
            $result = 0;
         }else{
            $result = 1;
         }
        return $result;
    }
    public function cek_closingactive(){
        $query = $this->db->query("select ifnull(flag_aktif,'') as 'flag_aktif' from mst_closing where cabang ='".$this->cabang."' limit 1")->row();
        return $query;
    }

    public function cek_harga_pusat(){
        $this->db2 = $this->load->database('pusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {

            $query = $this->db2->query("SELECT sum(Hrg_Beli_Pst) Hrg_Beli_Pst ,sum(HNA) HNA ,sum(Hrg_Beli_Cab) Hrg_Beli_Cab,count(Hrg_Beli_Cab) hitung FROM mst_harga")->row();
        }else{
            $query = ["harga" => 0 ,"hitung" =>0];
        }

        return $query;
    }

    public function cek_harga_cabang(){

        $query = $this->db->query("SELECT sum(Hrg_Beli_Pst) Hrg_Beli_Pst ,sum(HNA) HNA ,sum(Hrg_Beli_Cab) Hrg_Beli_Cab,count(Hrg_Beli_Cab) hitung FROM mst_harga")->row();
        return $query;
    }

    function updateHarga(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        $data = $this->db2->query('SELECT * FROM mst_harga')->result();

      $vdoheader="";
      $var_dump="";

      foreach ($data as $r) {
        $cabang           = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
        $Cabang_String    = ($r->Cabang_String == null) ? "null" : "'".$r->Cabang_String."'";
        $Prinsipal        = ($r->Prinsipal == null) ? "null" : "'".$r->Prinsipal."'";
        $Prinsipal_String = ($r->Prinsipal_String == null) ? "null" : "'".$r->Prinsipal_String."'";
        $Produk           = ($r->Produk == null) ? "null" : "'".$r->Produk."'";
        $Produk_String    = ($r->Produk_String == null) ? "null" : "'".str_replace(["'",","], " ", $r->Produk_String)."'";
        $Hrg_Beli_Pst     = ($r->Hrg_Beli_Pst == null) ? "null" : $r->Hrg_Beli_Pst;
        $Hrg_Beli_Cab     = ($r->Hrg_Beli_Cab == null) ? "null" : $r->Hrg_Beli_Cab;
        $HNA              = ($r->HNA == null) ? "null" : $r->HNA;
        $HNA2             = ($r->HNA2 == null) ? "null" : $r->HNA2;
        $Dsc_Cab          = ($r->Dsc_Cab == null) ? "null" : $r->Dsc_Cab;
        $Dsc_Pri          = ($r->Dsc_Pri == null) ? "null" : $r->Dsc_Pri;
        $Dsc_Pst          = ($r->Dsc_Pst == null) ? "null" : $r->Dsc_Pst;
        $Dsc_Beli_Pst     = ($r->Dsc_Beli_Pst == null) ? "null" : $r->Dsc_Beli_Pst;
        $Dsc_Beli_Cab     = ($r->Dsc_Beli_Cab == null) ? "null" : $r->Dsc_Beli_Cab;
        $HK               = ($r->HK == null) ? "null" : "'".$r->HK."'";
        $Group_Harga      = ($r->Group_Harga == null) ? "null" : "'".$r->Group_Harga."'";

        $vdoheader .= "(".$r->id.",".$Prinsipal.",".$Prinsipal_String.",".$Produk.",".$Produk_String.",".$cabang.",".$Cabang_String.",
         ".$Hrg_Beli_Pst.",
         ".$Hrg_Beli_Cab.",
         ".$HNA.",
         ".$HNA2.",
         ".$Dsc_Cab.",
         ".$Dsc_Pri.",
         ".$Dsc_Pst.",
         ".$Dsc_Beli_Pst.",
         ".$Dsc_Beli_Cab.",
         ".$HK.",
         ".$Group_Harga."),";
      }
      $var_dump=rtrim($vdoheader,",");

      $this->db->truncate("mst_harga");
      $valid = $this->db->query("INSERT INTO `mst_harga`
              (`id`,`Prinsipal`,`Prinsipal_String`, `Produk`,`Produk_String`,`Cabang`,`Cabang_String`,`Hrg_Beli_Pst`,`Hrg_Beli_Cab`,
               `HNA`,`HNA2`,`Dsc_Cab`,
               `Dsc_Pri`,`Dsc_Pst`,`Dsc_Beli_Pst`,`Dsc_Beli_Cab`,`HK`,`Group_Harga`)VALUES ".$var_dump);

      $update_flag_pusat = $this->db2->query("UPDATE mst_cabang SET status_flag = 'Berhasil', flag_harga='N',tgl_flag_harga = '".date('Y-m-d H:i:s')."' where Cabang = '".$this->cabang."'");

      $update_flag_harga = $this->db->query("UPDATE mst_cabang SET status_flag = 'Berhasil', flag_harga='N',tgl_flag_harga = '".date('Y-m-d H:i:s')."' where Cabang = '".$this->cabang."'");

      // end update produk
      return $valid;
    }

    function kirim_inv_pusat(){
        $cabang = $this->cabang;
        $this->db2 = $this->load->database('pusat', TRUE);      
        $getyear = date('Y');

        $hapus_sum  = $this->db2->query("DELETE FROM  trs_invsum_all WHERE cabang='$cabang' and tahun = '$getyear'");

        $hapus_det  = $this->db2->query("DELETE FROM  trs_invdet_all  WHERE cabang='$cabang' and tahun = '$getyear'");

        $query = $this->db->query("SELECT * FROM trs_invsum WHERE cabang= '$cabang' AND tahun ='$getyear'")->result();

        $string_sum=$string_detail='';
        $jml_sum = $jml_detail = 0;

        foreach($query as $r) { // loop sum_detail
            $Tahun          = ($r->Tahun == null) ? "null" : "'".$r->Tahun."'";
            $Cabang         = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
            $KodePrinsipal  = ($r->KodePrinsipal == null) ? "null" : "'".$r->KodePrinsipal."'";
            $NamaPrinsipal  = ($r->NamaPrinsipal == null) ? "null" : "'".$r->NamaPrinsipal."'";
            $Pabrik         = ($r->Pabrik == null) ? "null" : "'".$r->Pabrik."'";
            $KodeProduk     = ($r->KodeProduk == null) ? "null" : "'".$r->KodeProduk."'";
            $NamaProduk     = ($r->NamaProduk == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaProduk)."'";
            $UnitStok       = ($r->UnitStok == null) ? "null" : "'".$r->UnitStok."'";
            $ValueStok      = ($r->ValueStok == null) ? "null" : "'".$r->ValueStok."'";
            $Gudang         = ($r->Gudang == null) ? "null" : "'".$r->Gudang."'";
            $Indeks         = ($r->Indeks == null) ? "null" : "'".$r->Indeks."'";
            $UnitCOGS       = ($r->UnitCOGS == null) ? "null" : "'".$r->UnitCOGS."'";
            $HNA            = ($r->HNA == null) ? "null" : "'".$r->HNA."'";
            $SAwal01        = ($r->SAwal01 == null) ? "null" : "'".$r->SAwal01."'";
            $VAwal01        = ($r->VAwal01 == null) ? "null" : "'".$r->VAwal01."'";
            $SAwal02        = ($r->SAwal02 == null) ? "null" : "'".$r->SAwal02."'";
            $VAwal02        = ($r->VAwal02 == null) ? "null" : "'".$r->VAwal02."'";
            $SAwal03        = ($r->SAwal03 == null) ? "null" : "'".$r->SAwal03."'";
            $VAwal03        = ($r->VAwal03 == null) ? "null" : "'".$r->VAwal03."'";
            $SAwal04        = ($r->SAwal04 == null) ? "null" : "'".$r->SAwal04."'";
            $VAwal04        = ($r->VAwal04 == null) ? "null" : "'".$r->VAwal04."'";
            $SAwal05        = ($r->SAwal05 == null) ? "null" : "'".$r->SAwal05."'";
            $VAwal05        = ($r->VAwal05 == null) ? "null" : "'".$r->VAwal05."'";
            $SAwal06        = ($r->SAwal06 == null) ? "null" : "'".$r->SAwal06."'";
            $VAwal06        = ($r->VAwal06 == null) ? "null" : "'".$r->VAwal06."'";
            $SAwal07        = ($r->SAwal07 == null) ? "null" : "'".$r->SAwal07."'";
            $VAwal07        = ($r->VAwal07 == null) ? "null" : "'".$r->VAwal07."'";
            $SAwal08        = ($r->SAwal08 == null) ? "null" : "'".$r->SAwal08."'";
            $VAwal08        = ($r->VAwal08 == null) ? "null" : "'".$r->VAwal08."'";
            $SAwal09        = ($r->SAwal09 == null) ? "null" : "'".$r->SAwal09."'";
            $VAwal09        = ($r->VAwal09 == null) ? "null" : "'".$r->VAwal09."'";
            $SAwal10        = ($r->SAwal10 == null) ? "null" : "'".$r->SAwal10."'";
            $VAwal10        = ($r->VAwal10 == null) ? "null" : "'".$r->VAwal10."'";
            $SAwal11        = ($r->SAwal11 == null) ? "null" : "'".$r->SAwal11."'";
            $VAwal11        = ($r->VAwal11 == null) ? "null" : "'".$r->VAwal11."'";
            $SAwal12        = ($r->SAwal12 == null) ? "null" : "'".$r->SAwal12."'";
            $VAwal12        = ($r->VAwal12 == null) ? "null" : "'".$r->VAwal12."'";
            $LastBuy        = ($r->LastBuy == null) ? "null" : "'".$r->LastBuy."'";
            $LastSales      = ($r->LastSales == null) ? "null" : "'".$r->LastSales."'";
            $AddedUser      = ($r->AddedUser == null) ? "null" : "'".$r->AddedUser."'";
            $AddedTime      = ($r->AddedTime == null) ? "null" : "'".$r->AddedTime."'";
            $ModifiedUser   = ($r->ModifiedUser == null) ? "null" : "'".$r->ModifiedUser."'";
            $ModifiedDate   = ($r->ModifiedDate == null) ? "null" : "'".$r->ModifiedDate."'";


            
            $string_sum .= "(
                ".$Tahun.", ".$Cabang.", ".$KodePrinsipal.", ".$NamaPrinsipal.", ".$Pabrik.", ".$KodeProduk.", ".$NamaProduk.", ".$UnitStok.", ".$ValueStok.", ".$Gudang.", ".$Indeks.", ".$UnitCOGS.", ".$HNA.", ".$SAwal01.", ".$VAwal01.", ".$SAwal02.", ".$VAwal02.", ".$SAwal03.", ".$VAwal03.", ".$SAwal04.", ".$VAwal04.", ".$SAwal05.", ".$VAwal05.", ".$SAwal06.", ".$VAwal06.", ".$SAwal07.", ".$VAwal07.", ".$SAwal08.", ".$VAwal08.", ".$SAwal09.", ".$VAwal09.", ".$SAwal10.", ".$VAwal10.", ".$SAwal11.", ".$VAwal11.", ".$SAwal12.", ".$VAwal12.", ".$LastBuy.", ".$LastSales.", ".$AddedUser.", ".$AddedTime.", ".$ModifiedUser.", ".$ModifiedDate."),";

             $jml_sum++;

        }
             // end insert faktur day

            $query = $this->db->query("SELECT * FROM trs_invdet WHERE cabang= '$cabang' AND tahun ='$getyear'")->result();

            foreach($query as $r) { // loop do_detail
                $Tahun              = ($r->Tahun == null) ? "null" : "'".$r->Tahun."'";
                $Cabang             = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
                $KodePrinsipal      = ($r->KodePrinsipal == null) ? "null" : "'".$r->KodePrinsipal."'";
                $NamaPrinsipal      = ($r->NamaPrinsipal == null) ? "null" : "'".$r->NamaPrinsipal."'";
                $Pabrik             = ($r->Pabrik == null) ? "null" : "'".$r->Pabrik."'";
                $KodeProduk         = ($r->KodeProduk == null) ? "null" : "'".$r->KodeProduk."'";
                $NamaProduk         = ($r->NamaProduk == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaProduk)."'";
                $UnitStok           = ($r->UnitStok == null) ? "null" : "'".$r->UnitStok."'";
                $ValueStok          = ($r->ValueStok == null) ? "null" : "'".$r->ValueStok."'";
                $UnitCOGS           = ($r->UnitCOGS == null) ? "null" : "'".$r->UnitCOGS."'";
                $BatchNo            = ($r->BatchNo == null) ? "null" : "'".str_replace(["'",","], " ", $r->BatchNo)."'";
                $ExpDate            = ($r->ExpDate == null) ? "null" : "'".$r->ExpDate."'";
                $NoDokumen          = ($r->NoDokumen == null) ? "null" : "'".str_replace(["'",","], " ", $r->NoDokumen)."'";
                $Gudang             = ($r->Gudang == null) ? "null" : "'".$r->Gudang."'";
                $TanggalDokumen     = ($r->TanggalDokumen == null) ? "null" : "'".$r->TanggalDokumen."'";
                $SAwa01             = ($r->SAwa01 == null) ? "null" : "'".$r->SAwa01."'";
                $VAwa01             = ($r->VAwa01 == null) ? "null" : "'".$r->VAwa01."'";
                $SAwa02             = ($r->SAwa02 == null) ? "null" : "'".$r->SAwa02."'";
                $VAwa02             = ($r->VAwa02 == null) ? "null" : "'".$r->VAwa02."'";
                $SAwa03             = ($r->SAwa03 == null) ? "null" : "'".$r->SAwa03."'";
                $VAwa03             = ($r->VAwa03 == null) ? "null" : "'".$r->VAwa03."'";
                $SAwa04             = ($r->SAwa04 == null) ? "null" : "'".$r->SAwa04."'";
                $VAwa04             = ($r->VAwa04 == null) ? "null" : "'".$r->VAwa04."'";
                $SAwa05             = ($r->SAwa05 == null) ? "null" : "'".$r->SAwa05."'";
                $VAwa05             = ($r->VAwa05 == null) ? "null" : "'".$r->VAwa05."'";
                $SAwa06             = ($r->SAwa06 == null) ? "null" : "'".$r->SAwa06."'";
                $VAwa06             = ($r->VAwa06 == null) ? "null" : "'".$r->VAwa06."'";
                $SAwa07             = ($r->SAwa07 == null) ? "null" : "'".$r->SAwa07."'";
                $VAwa07             = ($r->VAwa07 == null) ? "null" : "'".$r->VAwa07."'";
                $SAwa08             = ($r->SAwa08 == null) ? "null" : "'".$r->SAwa08."'";
                $VAwa08             = ($r->VAwa08 == null) ? "null" : "'".$r->VAwa08."'";
                $SAwa09             = ($r->SAwa09 == null) ? "null" : "'".$r->SAwa09."'";
                $VAwa09             = ($r->VAwa09 == null) ? "null" : "'".$r->VAwa09."'";
                $SAwa10             = ($r->SAwa10 == null) ? "null" : "'".$r->SAwa10."'";
                $VAwa10             = ($r->VAwa10 == null) ? "null" : "'".$r->VAwa10."'";
                $SAwa11             = ($r->SAwa11 == null) ? "null" : "'".$r->SAwa11."'";
                $VAwa11             = ($r->VAwa11 == null) ? "null" : "'".$r->VAwa11."'";
                $SAWa12             = ($r->SAWa12 == null) ? "null" : "'".$r->SAWa12."'";
                $VAwa12             = ($r->VAwa12 == null) ? "null" : "'".$r->VAwa12."'";
                $Keterangan         = ($r->Keterangan == null) ? "null" : "'".$r->Keterangan."'";
                $LastBuy            = ($r->LastBuy == null) ? "null" : "'".$r->LastBuy."'";
                $LastSales          = ($r->LastSales == null) ? "null" : "'".$r->LastSales."'";
                $AddedUser          = ($r->AddedUser == null) ? "null" : "'".$r->AddedUser."'";
                $AddedTime          = ($r->AddedTime == null) ? "null" : "'".$r->AddedTime."'";
                $ModifiedUser       = ($r->ModifiedUser == null) ? "null" : "'".$r->ModifiedUser."'";
                $ModifiedTime       = ($r->ModifiedTime == null) ? "null" : "'".$r->ModifiedTime."'";
                $nourut             = ($r->nourut == null) ? "null" : "'".$r->nourut."'";


            
            $string_detail .= "(
            ".$Tahun.", ".$Cabang.", ".$KodePrinsipal.", ".$NamaPrinsipal.", ".$Pabrik.", ".$KodeProduk.", ".$NamaProduk.", ".$UnitStok.", ".$ValueStok.", ".$UnitCOGS.", ".$BatchNo.", ".$ExpDate.", ".$NoDokumen.", ".$Gudang.", ".$TanggalDokumen.", ".$SAwa01.", ".$VAwa01.", ".$SAwa02.", ".$VAwa02.", ".$SAwa03.", ".$VAwa03.", ".$SAwa04.", ".$VAwa04.", ".$SAwa05.", ".$VAwa05.", ".$SAwa06.", ".$VAwa06.", ".$SAwa07.", ".$VAwa07.", ".$SAwa08.", ".$VAwa08.", ".$SAwa09.", ".$VAwa09.", ".$SAwa10.", ".$VAwa10.", ".$SAwa11.", ".$VAwa11.", ".$SAWa12.", ".$VAwa12.", ".$Keterangan.", ".$LastBuy.", ".$LastSales.", ".$AddedUser.", ".$AddedTime.", ".$ModifiedUser.", ".$ModifiedTime.", ".$nourut.",".'null'."),";

             $jml_detail++;
        }

        $var_dump=rtrim($string_sum,",");

        $var_dump1=rtrim($string_detail,",");

        $valid = $this->db2->query("INSERT INTO `trs_invsum_all` VALUES ".$var_dump);

        $valid = $this->db2->query("INSERT INTO `trs_invdet_all` VALUES ".$var_dump1);

        if ($valid) {
            $status = ['status'=>true,'jml_sum'=>$jml_sum,'jml_detail'=>$jml_detail];
        }else{
            $status = ['status'=>false];
        }

        return $status;
    }

    function kirim_sales_pusat(){
        $cabang = $this->cabang;
        $this->db2 = $this->load->database('pusat', TRUE);  

        $hapus_do  = $this->db2->query("DELETE FROM  trs_delivery_order_sales_detail_day WHERE cabang='$cabang'");

        $hapus_faktur  = $this->db2->query("DELETE FROM  trs_faktur_detail_day WHERE cabang='$cabang'");

        $query = $this->db->query("SELECT * FROM trs_faktur_detail WHERE YEAR(TglFaktur) = YEAR(CURDATE()) AND MONTH(TglFaktur)= MONTH(CURDATE()) AND TglFaktur < '".date('Y-m-d')."'")->result();

        $string_faktur=$string_do='';
        $jml_faktur = $jml_do = 0;

        foreach($query as $r) { // loop faktur_detail
            $Cabang             = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
            $NoFaktur           = ($r->NoFaktur == null) ? "null" : "'".$r->NoFaktur."'";
            $noline             = ($r->noline == null) ? "null" : "'".$r->noline."'";
            $TglFaktur          = ($r->TglFaktur == null) ? "null" : "'".$r->TglFaktur."'";
            $TimeFaktur         = ($r->TimeFaktur == null) ? "null" : "'".$r->TimeFaktur."'";
            $Pelanggan          = ($r->Pelanggan == null) ? "null" : "'".$r->Pelanggan."'";
            $NamaPelanggan      = ($r->NamaPelanggan == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaPelanggan)."'";
            $AlamatFaktur       = ($r->AlamatFaktur == null) ? "null" : "'".str_replace(["'",","], " ", $r->AlamatFaktur)."'";
            $TipePelanggan      = ($r->TipePelanggan == null) ? "null" : "'".$r->TipePelanggan."'";
            $NamaTipePelanggan  = ($r->NamaTipePelanggan == null) ? "null" : "'".$r->NamaTipePelanggan."'";
            $NPWPPelanggan      = ($r->NPWPPelanggan == null) ? "null" : "'".$r->NPWPPelanggan."'";
            $KategoriPelanggan  = ($r->KategoriPelanggan == null) ? "null" : "'".$r->KategoriPelanggan."'";
            $Acu                = ($r->Acu == null) ? "null" : "'".str_replace(["'",","], " ", $r->Acu)."'";
            $CaraBayar          = ($r->CaraBayar == null) ? "null" : "'".$r->CaraBayar."'";
            $CashDiskon         = ($r->CashDiskon == null) ? "null" : "'".$r->CashDiskon."'";
            $ValueCashDiskon    = ($r->ValueCashDiskon == null) ? "null" : "'".$r->ValueCashDiskon."'";
            $TOP                = ($r->TOP == null) ? "null" : "'".$r->TOP."'";
            $TglJtoFaktur       = ($r->TglJtoFaktur == null) ? "null" : "'".$r->TglJtoFaktur."'";
            $Salesman           = ($r->Salesman == null) ? "null" : "'".$r->Salesman."'";
            $NamaSalesman       = ($r->NamaSalesman == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaSalesman)."'";
            $Rayon              = ($r->Rayon == null) ? "null" : "'".$r->Rayon."'";
            $NamaRayon          = ($r->NamaRayon == null) ? "null" : "'".$r->NamaRayon."'";
            $Status             = ($r->Status == null) ? "null" :  "'".$r->Status. "'";
            $TipeDokumen        = ($r->TipeDokumen == null) ? "null" :  "'".$r->TipeDokumen. "'";
            $KodeProduk         = ($r->KodeProduk == null) ? "null" :  "'".$r->KodeProduk. "'";
            $NamaProduk         = ($r->NamaProduk == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaProduk)."'";
            $UOM                = ($r->UOM == null) ? "null" : "'".$r->UOM."'";
            $Harga              = ($r->Harga == null) ? "null" : "'".$r->Harga."'";
            $QtyDO              = ($r->QtyDO == null) ? "null" : "'".$r->QtyDO."'";
            $BonusDO            = ($r->BonusDO == null) ? "null" : "'".$r->BonusDO."'";
            $QtyFaktur          = ($r->QtyFaktur == null) ? "null" : "'".$r->QtyFaktur."'";
            $BonusFaktur        = ($r->BonusFaktur == null) ? "null" : "'".$r->BonusFaktur."'";
            $ValueBonus         = ($r->ValueBonus == null) ? "null" : "'".$r->ValueBonus."'";
            $DiscCab_onf        = ($r->DiscCab_onf == null) ? "null" : "'".$r->DiscCab_onf."'";
            $ValueDiscCab_onf   = ($r->ValueDiscCab_onf == null) ? "null" : "'".$r->ValueDiscCab_onf."'";
            $DiscCab            = ($r->DiscCab == null) ? "null" : "'".$r->DiscCab."'";
            $ValueDiscCab       = ($r->ValueDiscCab == null) ? "null" : "'".$r->ValueDiscCab."'";
            $DiscCabTot         = ($r->DiscCabTot == null) ? "null" : "'".$r->DiscCabTot."'";
            $ValueDiscCabTotal  = ($r->ValueDiscCabTotal == null) ? "null" : "'".$r->ValueDiscCabTotal."'";
            $DiscPrins1         = ($r->DiscPrins1 == null) ? "null" : "'".$r->DiscPrins1."'";
            $ValueDiscPrins1    = ($r->ValueDiscPrins1 == null) ? "null" : "'".$r->ValueDiscPrins1."'";
            $DiscPrins2         = ($r->DiscPrins2 == null) ? "null" : "'".$r->DiscPrins2."'";
            $ValueDiscPrins2    = ($r->ValueDiscPrins2 == null) ? "null" : "'".$r->ValueDiscPrins2."'";
            $DiscPrinsTot       = ($r->DiscPrinsTot == null) ? "null" : "'".$r->DiscPrinsTot."'";
            $ValueDiscPrinsTotal= ($r->ValueDiscPrinsTotal == null) ? "null" : "'".$r->ValueDiscPrinsTotal."'";
            $DiscTotal          = ($r->DiscTotal == null) ? "null" : "'".$r->DiscTotal."'";
            $ValueDiscTotal     = ($r->ValueDiscTotal == null) ? "null" : "'".$r->ValueDiscTotal."'";
            $Gross              = ($r->Gross == null) ? "null" : "'".$r->Gross."'";
            $Potongan           = ($r->Potongan == null) ? "null" : "'".$r->Potongan."'";
            $Value              = ($r->Value == null) ? "null" : "'".$r->Value."'";
            $Ppn                = ($r->Ppn == null) ? "null" : "'".$r->Ppn."'";
            $LainLain           = ($r->LainLain == null) ? "null" : "'".$r->LainLain."'";
            $Total              = ($r->Total == null) ? "null" : "'".$r->Total."'";
            $Keterangan1        = ($r->Keterangan1 == null) ? "null" : "'".$r->Keterangan1."'";
            $Keterangan2        = ($r->Keterangan2 == null) ? "null" : "'".$r->Keterangan2."'";
            $Barcode            = ($r->Barcode == null) ? "null" : "'".$r->Barcode."'";
            $QrCode             = ($r->QrCode == null) ? "null" : "'".$r->QrCode."'";
            $NoSO               = ($r->NoSO == null) ? "null" : "'".$r->NoSO."'";
            $NoDO               = ($r->NoDO == null) ? "null" : "'".$r->NoDO."'";
            $DiscCabMax         = ($r->DiscCabMax == null) ? "null" : "'".$r->DiscCabMax."'";
            $KetDiscCabMax      = ($r->KetDiscCabMax == null) ? "null" : "'".$r->KetDiscCabMax."'";
            $DiscPrinsMax       = ($r->DiscPrinsMax == null) ? "null" : "'".$r->DiscPrinsMax."'";
            $KetDiscPrinsMax    = ($r->KetDiscPrinsMax == null) ? "null" : "'".$r->KetDiscPrinsMax."'";
            $COGS               = ($r->COGS == null) ? "null" : "'".$r->COGS."'";
            $TotalCOGS          = ($r->TotalCOGS == null) ? "null" : "'".$r->TotalCOGS."'";
            $BatchNo            = ($r->BatchNo == null) ? "null" : "'".$r->BatchNo."'";
            $ExpDate            = ($r->ExpDate == null) ? "null" : "'".$r->ExpDate."'";
            $NoBPB              = ($r->NoBPB == null) ? "null" : "'".$r->NoBPB."'";
            $Prinsipal          = ($r->Prinsipal == null) ? "null" : "'".$r->Prinsipal."'";
            $Prinsipal2         = ($r->Prinsipal2 == null) ? "null" : "'".$r->Prinsipal2."'";
            $Supplier           = ($r->Supplier == null) ? "null" : "'".$r->Supplier."'";
            $Supplier2          = ($r->Supplier2 == null) ? "null" : "'".$r->Supplier2."'";
            $Pabrik             = ($r->Pabrik == null) ? "null" : "'".$r->Pabrik."'";
            $Farmalkes          = ($r->Farmalkes == null) ? "null" : "'".$r->Farmalkes."'";
            $sisa_retur         = ($r->sisa_retur == null) ? "null" : "'".$r->sisa_retur."'";
            $created_at         = ($r->created_at == null) ? "null" : "'".$r->created_at."'";
            $created_by         = ($r->created_by == null) ? "null" : "'".$r->created_by."'";
            $modified_at        = ($r->modified_at == null) ? "null" : "'".$r->modified_at."'";
            $modified_by        = ($r->modified_by == null) ? "null" : "'".$r->modified_by."'";
            $kota               = ($r->kota == null) ? "null" : "'".$r->kota."'";
            $telp               = ($r->telp == null) ? "null" : "'".$r->telp."'";
            $tipe_2             = ($r->tipe_2 == null) ? "null" : "'".$r->tipe_2."'";
            $region             = ($r->region == null) ? "null" : "'".$r->region."'";
            $acu2               = ($r->acu2 == null) ? "null" : "'".$r->acu2."'";
            
            $string_faktur .= "(
             ".$Cabang.", ".$NoFaktur.", ".$noline.", ".$TglFaktur.", ".$TimeFaktur.", ".$Pelanggan.", ".$NamaPelanggan.", ".$AlamatFaktur.", ".$TipePelanggan.", ".$NamaTipePelanggan.", ".$NPWPPelanggan.", ".$KategoriPelanggan.", ".$Acu.", ".$CaraBayar.", ".$CashDiskon.", ".$ValueCashDiskon.", ".$TOP.", ".$TglJtoFaktur.", ".$Salesman.", ".$NamaSalesman.", ".$Rayon.", ".$NamaRayon.", ".$Status.", ".$TipeDokumen.", ".$KodeProduk.", ".$NamaProduk.", ".$UOM.", ".$Harga.", ".$QtyDO.", ".$BonusDO.", ".$QtyFaktur.", ".$BonusFaktur.", ".$ValueBonus.", ".$DiscCab_onf.", ".$ValueDiscCab_onf.", ".$DiscCab.", ".$ValueDiscCab.", ".$DiscCabTot.", ".$ValueDiscCabTotal.", ".$DiscPrins1.", ".$ValueDiscPrins1.", ".$DiscPrins2.", ".$ValueDiscPrins2.", ".$DiscPrinsTot.", ".$ValueDiscPrinsTotal.", ".$DiscTotal.", ".$ValueDiscTotal.", ".$Gross.", ".$Potongan.", ".$Value.", ".$Ppn.", ".$LainLain.", ".$Total.", ".$Keterangan1.", ".$Keterangan2.", ".$Barcode.", ".$QrCode.", ".$NoSO.", ".$NoDO.", ".$DiscCabMax.", ".$KetDiscCabMax.", ".$DiscPrinsMax.", ".$KetDiscPrinsMax.", ".$COGS.", ".$TotalCOGS.", ".$BatchNo.", ".$ExpDate.", ".$NoBPB.", ".$Prinsipal.", ".$Prinsipal2.", ".$Supplier.", ".$Supplier2.", ".$Pabrik.", ".$Farmalkes.", ".$sisa_retur.", ".$created_at.", ".$created_by.", ".$modified_at.", ".$modified_by.", ".$kota.", ".$telp.", ".$tipe_2.", ".$region.", ".$acu2."),";

             $jml_faktur++;

        }
             // end insert faktur day

            $query = $this->db->query("SELECT * FROM trs_delivery_order_sales_detail WHERE YEAR(TglDO) = YEAR(CURDATE()) AND MONTH(TglDO)= MONTH(CURDATE()) AND TglDO < '".date('Y-m-d')."' ")->result();

            foreach($query as $r) { // loop do_detail
                $Cabang             = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
                $NoDO               = ($r->NoDO == null) ? "null" : "'".$r->NoDO."'";
                $TglDO              = ($r->TglDO == null) ? "null" : "'".$r->TglDO."'";
                $TimeDO             = ($r->TimeDO == null) ? "null" : "'".$r->TimeDO."'";
                $noline             = ($r->noline == null) ? "null" : "'".$r->noline."'";
                $Pengirim           = ($r->Pengirim == null) ? "null" : "'".$r->Pengirim."'";
                $NamaPengirim       = ($r->NamaPengirim == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaPengirim)."'";
                $Pelanggan          = ($r->Pelanggan == null) ? "null" : "'".$r->Pelanggan."'";
                $NamaPelanggan      = ($r->NamaPelanggan == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaPelanggan)."'";
                $AlamatKirim        = ($r->AlamatKirim == null) ? "null" : "'".str_replace(["'",","], " ", $r->AlamatKirim)."'";
                $TipePelanggan      = ($r->TipePelanggan == null) ? "null" : "'".$r->TipePelanggan."'";
                $NamaTipePelanggan  = ($r->NamaTipePelanggan == null) ? "null" : "'".$r->NamaTipePelanggan."'";
                $NPWPPelanggan      = ($r->NPWPPelanggan == null) ? "null" : "'".$r->NPWPPelanggan."'";
                $KategoriPelanggan  = ($r->KategoriPelanggan == null) ? "null" : "'".$r->KategoriPelanggan."'";
                $Acu                = ($r->Acu == null) ? "null" : "'".str_replace(["'",","], " ", $r->Acu)."'";
                $CaraBayar          = ($r->CaraBayar == null) ? "null" : "'".$r->CaraBayar."'";
                $CashDiskon         = ($r->CashDiskon == null) ? "null" : "'".$r->CashDiskon."'";
                $ValueCashDiskon    = ($r->ValueCashDiskon == null) ? "null" : "'".$r->ValueCashDiskon."'";
                $TOP                = ($r->TOP == null) ? "null" : "'".$r->TOP."'";
                $TglJtoOrder        = ($r->TglJtoOrder == null) ? "null" : "'".$r->TglJtoOrder."'";
                $Salesman           = ($r->Salesman == null) ? "null" : "'".$r->Salesman."'";
                $NamaSalesman       = ($r->NamaSalesman == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaSalesman)."'";
                $Rayon              = ($r->Rayon == null) ? "null" : "'".$r->Rayon."'";
                $NamaRayon          = ($r->NamaRayon == null) ? "null" : "'".$r->NamaRayon."'";
                $Status             = ($r->Status == null) ? "null" : "'".$r->Status."'";
                $TipeDokumen        = ($r->TipeDokumen == null) ? "null" : "'".$r->TipeDokumen."'";
                $KodeProduk         = ($r->KodeProduk == null) ? "null" : "'".$r->KodeProduk."'";
                $NamaProduk         = ($r->NamaProduk == null) ? "null" : "'".str_replace(["'",","], " ", $r->NamaProduk)."'";
                $UOM                = ($r->UOM == null) ? "null" : "'".$r->UOM."'";
                $Harga              = ($r->Harga == null) ? "null" : "'".$r->Harga."'";
                $QtySO              = ($r->QtySO == null) ? "null" : "'".$r->QtySO."'";
                $BonusSO            = ($r->BonusSO == null) ? "null" : "'".$r->BonusSO."'";
                $QtyDO              = ($r->QtyDO == null) ? "null" : "'".$r->QtyDO."'";
                $BonusDO            = ($r->BonusDO == null) ? "null" : "'".$r->BonusDO."'";
                $ValueBonus         = ($r->ValueBonus == null) ? "null" : "'".$r->ValueBonus."'";
                $DiscCab_onf        = ($r->DiscCab_onf == null) ? "null" : "'".$r->DiscCab_onf."'";
                $ValueDiscCab_onf   = ($r->ValueDiscCab_onf == null) ? "null" : "'".$r->ValueDiscCab_onf."'";
                $DiscCab            = ($r->DiscCab == null) ? "null" : "'".$r->DiscCab."'";
                $ValueDiscCab       = ($r->ValueDiscCab == null) ? "null" : "'".$r->ValueDiscCab."'";
                $DiscCabTot         = ($r->DiscCabTot == null) ? "null" : "'".$r->DiscCabTot."'";
                $ValueDiscCabTotal  = ($r->ValueDiscCabTotal == null) ? "null" : "'".$r->ValueDiscCabTotal."'";
                $DiscPrins1         = ($r->DiscPrins1 == null) ? "null" : "'".$r->DiscPrins1."'";
                $ValueDiscPrins1    = ($r->ValueDiscPrins1 == null) ? "null" : "'".$r->ValueDiscPrins1."'";
                $DiscPrins2         = ($r->DiscPrins2 == null) ? "null" : "'".$r->DiscPrins2."'";
                $ValueDiscPrins2    = ($r->ValueDiscPrins2 == null) ? "null" : "'".$r->ValueDiscPrins2."'";
                $DiscPrinsTot       = ($r->DiscPrinsTot == null) ? "null" : "'".$r->DiscPrinsTot."'";
                $ValueDiscPrinsTotal    = ($r->ValueDiscPrinsTotal == null) ? "null" : "'".$r->ValueDiscPrinsTotal."'";
                $DiscTotal          = ($r->DiscTotal == null) ? "null" : "'".$r->DiscTotal."'";
                $ValueDiscTotal     = ($r->ValueDiscTotal == null) ? "null" : "'".$r->ValueDiscTotal."'";
                $Gross              = ($r->Gross == null) ? "null" : "'".$r->Gross."'";
                $Potongan           = ($r->Potongan == null) ? "null" : "'".$r->Potongan."'";
                $Value              = ($r->Value == null) ? "null" : "'".$r->Value."'";
                $Ppn                = ($r->Ppn == null) ? "null" : "'".$r->Ppn."'";
                $LainLain           = ($r->LainLain == null) ? "null" : "'".$r->LainLain."'";
                $Total              = ($r->Total == null) ? "null" : "'".$r->Total."'";
                $Keterangan1        = ($r->Keterangan1 == null) ? "null" : "'".$r->Keterangan1."'";
                $Keterangan2        = ($r->Keterangan2 == null) ? "null" : "'".$r->Keterangan2."'";
                $Barcode            = ($r->Barcode == null) ? "null" : "'".$r->Barcode."'";
                $QrCode             = ($r->QrCode == null) ? "null" : "'".$r->QrCode."'";
                $NoSO               = ($r->NoSO == null) ? "null" : "'".$r->NoSO."'";
                $NoFaktur           = ($r->NoFaktur == null) ? "null" : "'".$r->NoFaktur."'";
                $DiscCabMax         = ($r->DiscCabMax == null) ? "null" : "'".$r->DiscCabMax."'";
                $KetDiscCabMax      = ($r->KetDiscCabMax == null) ? "null" : "'".$r->KetDiscCabMax."'";
                $DiscPrinsMax       = ($r->DiscPrinsMax == null) ? "null" : "'".$r->DiscPrinsMax."'";
                $KetDiscPrinsMax    = ($r->KetDiscPrinsMax == null) ? "null" : "'".$r->KetDiscPrinsMax."'";
                $COGS               = ($r->COGS == null) ? "null" : "'".$r->COGS."'";
                $TotalCOGS          = ($r->TotalCOGS == null) ? "null" : "'".$r->TotalCOGS."'";
                $BatchNo            = ($r->BatchNo == null) ? "null" : "'".$r->BatchNo."'";
                $ExpDate            = ($r->ExpDate == null) ? "null" : "'".$r->ExpDate."'";
                $TipeFaktur         = ($r->TipeFaktur == null) ? "null" : "'".$r->TipeFaktur."'";
                $NoIDPaket          = ($r->NoIDPaket == null) ? "null" : "'".$r->NoIDPaket."'";
                $KeteranganTender   = ($r->KeteranganTender == null) ? "null" : "'".$r->KeteranganTender."'";
                $NoBPB              = ($r->NoBPB == null) ? "null" : "'".$r->NoBPB."'";
                $flag_closing       = ($r->flag_closing == null) ? "null" : "'".$r->flag_closing."'";
                $picking_list       = ($r->picking_list == null) ? "null" : "'".$r->picking_list."'";
                $BatchNoDok         = ($r->BatchNoDok == null) ? "null" : "'".$r->BatchNoDok."'";
                $BatchTglDok        = ($r->BatchTglDok == null) ? "null" : "'".$r->BatchTglDok."'";
                $Prinsipal          = ($r->Prinsipal == null) ? "null" : "'".$r->Prinsipal."'";
                $Prinsipal2         = ($r->Prinsipal2 == null) ? "null" : "'".$r->Prinsipal2."'";
                $Supplier           = ($r->Supplier == null) ? "null" : "'".$r->Supplier."'";
                $Supplier2          = ($r->Supplier2 == null) ? "null" : "'".$r->Supplier2."'";
                $Pabrik             = ($r->Pabrik == null) ? "null" : "'".$r->Pabrik."'";
                $Farmalkes          = ($r->Farmalkes == null) ? "null" : "'".$r->Farmalkes."'";
                $QtyDO_awal         = ($r->QtyDO_awal == null) ? "null" : "'".$r->QtyDO_awal."'";
                $BonusDO_awal       = ($r->BonusDO_awal == null) ? "null" : "'".$r->BonusDO_awal."'";
                $user_approve       = ($r->user_approve == null) ? "null" : "'".$r->user_approve."'";
                $time_approve       = ($r->time_approve == null) ? "null" : "'".$r->time_approve."'";
                $status_approve     = ($r->status_approve == null) ? "null" : "'".$r->status_approve."'";
                $user_edit          = ($r->user_edit == null) ? "null" : "'".$r->user_edit."'";
                $time_edit          = ($r->time_edit == null) ? "null" : "'".$r->time_edit."'";
                $time_batal         = ($r->time_batal == null) ? "null" : "'".$r->time_batal."'";
                $user_batal         = ($r->user_batal == null) ? "null" : "'".$r->user_batal."'";
                $created_at         = ($r->created_at == null) ? "null" : "'".$r->created_at."'";
                $created_by         = ($r->created_by == null) ? "null" : "'".$r->created_by."'";
                $modified_at        = ($r->modified_at == null) ? "null" : "'".$r->modified_at."'";
                $modified_by        = ($r->modified_by == null) ? "null" : "'".$r->modified_by."'";
                $retur_qtyDO        = ($r->retur_qtyDO == null) ? "null" : "'".$r->retur_qtyDO."'";
                $retur_bonusDO      = ($r->retur_bonusDO == null) ? "null" : "'".$r->retur_bonusDO."'";
                $status_retur       = ($r->status_retur == null) ? "null" : "'".$r->status_retur."'";
                $status_validasi    = ($r->status_validasi == null) ? "null" : "'".$r->status_validasi."'";
                $noretur            = ($r->noretur == null) ? "null" : "'".$r->noretur."'";
                $region             = ($r->region == null) ? "null" : "'".$r->region."'";
                $kota               = ($r->kota == null) ? "null" : "'".$r->kota."'";
                $telp               = ($r->telp == null) ? "null" : "'".$r->telp."'";
                $tipe_2             = ($r->tipe_2 == null) ? "null" : "'".$r->tipe_2."'";
                $acu2               = ($r->acu2 == null) ? "null" : "'".$r->acu2."'";


            
            $string_do .= "(
             ".$Cabang.", ".$NoDO.", ".$TglDO.", ".$TimeDO.", ".$noline.", ".$Pengirim.", ".$NamaPengirim.", ".$Pelanggan.", ".$NamaPelanggan.", ".$AlamatKirim.", ".$TipePelanggan.", ".$NamaTipePelanggan.", ".$NPWPPelanggan.", ".$KategoriPelanggan.", ".$Acu.", ".$CaraBayar.", ".$CashDiskon.", ".$ValueCashDiskon.", ".$TOP.", ".$TglJtoOrder.", ".$Salesman.", ".$NamaSalesman.", ".$Rayon.", ".$NamaRayon.", ".$Status.", ".$TipeDokumen.", ".$KodeProduk.", ".$NamaProduk.", ".$UOM.", ".$Harga.", ".$QtySO.", ".$BonusSO.", ".$QtyDO.", ".$BonusDO.", ".$ValueBonus.", ".$DiscCab_onf.", ".$ValueDiscCab_onf.", ".$DiscCab.", ".$ValueDiscCab.", ".$DiscCabTot.", ".$ValueDiscCabTotal.", ".$DiscPrins1.", ".$ValueDiscPrins1.", ".$DiscPrins2.", ".$ValueDiscPrins2.", ".$DiscPrinsTot.", ".$ValueDiscPrinsTotal.", ".$DiscTotal.", ".$ValueDiscTotal.", ".$Gross.", ".$Potongan.", ".$Value.", ".$Ppn.", ".$LainLain.", ".$Total.", ".$Keterangan1.", ".$Keterangan2.", ".$Barcode.", ".$QrCode.", ".$NoSO.", ".$NoFaktur.", ".$DiscCabMax.", ".$KetDiscCabMax.", ".$DiscPrinsMax.", ".$KetDiscPrinsMax.", ".$COGS.", ".$TotalCOGS.", ".$BatchNo.", ".$ExpDate.", ".$TipeFaktur.", ".$NoIDPaket.", ".$KeteranganTender.", ".$NoBPB.", ".$flag_closing.", ".$picking_list.", ".$BatchNoDok.", ".$BatchTglDok.", ".$Prinsipal.", ".$Prinsipal2.", ".$Supplier.", ".$Supplier2.", ".$Pabrik.", ".$Farmalkes.", ".$QtyDO_awal.", ".$BonusDO_awal.", ".$user_approve.", ".$time_approve.", ".$status_approve.", ".$user_edit.", ".$time_edit.", ".$time_batal.", ".$user_batal.", ".$created_at.", ".$created_by.", ".$modified_at.", ".$modified_by.", ".$retur_qtyDO.", ".$retur_bonusDO.", ".$status_retur.", ".$status_validasi.", ".$noretur.", ".$region.", ".$kota.", ".$telp.", ".$tipe_2.", ".$acu2."),";

             $jml_do++;
        }

        $var_dump=rtrim($string_faktur,",");

        $var_dump1=rtrim($string_do,",");

        $valid = $this->db2->query("INSERT INTO `trs_faktur_detail_day` VALUES ".$var_dump);

        $valid = $this->db2->query("INSERT INTO `trs_delivery_order_sales_detail_day` VALUES ".$var_dump1);

        if ($valid) {
            $status = ['status'=>true,'jml_faktur'=>$jml_faktur,'jml_do'=>$jml_do];
        }else{
            $status = ['status'=>false];
        }

        return $status;
    }

    function cek_flag(){
        $this->db2 = $this->load->database('pusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {

            $query = $this->db2->query("SELECT flag_harga FROM mst_cabang WHERE Cabang = '".$this->cabang."' ")->row("flag_harga");
        }else{
            $query = $this->db->query("SELECT flag_harga FROM mst_cabang WHERE Cabang = '".$this->cabang."' ")->row("flag_harga");
        }

        return $query;
    }

    function updateProduk(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        
      // update produk
        $data = $this->db2->query('SELECT * FROM mst_produk')->result();

        $vproduk="";
        $produk_dump="";

          foreach ($data as $r) {
            $id                 = ($r->id == null) ? "null" : "'".$r->id."'"; 
            $Kode_Produk        = ($r->Kode_Produk == null) ? "null" : "'".$r->Kode_Produk."'";
            $Produk             = ($r->Produk == null) ? "null" : "'".str_replace(["'",","], " ", $r->Produk)."'";
            $Prinsipal          = ($r->Prinsipal == null) ? "null" : "'".$r->Prinsipal."'";
            $Prinsipal2         = ($r->Prinsipal2 == null) ? "null" : "'".$r->Prinsipal2."'";
            $Pabrik             = ($r->Pabrik == null) ? "null" : "'".$r->Pabrik."'";
            $Supplier1          = ($r->Supplier1 == null) ? "null" : "'".$r->Supplier1."'";
            $Supplier2          = ($r->Supplier2 == null) ? "null" : "'".$r->Supplier2."'";
            $Kategori           = ($r->Kategori == null) ? "null" : "'".$r->Kategori."'";
            $Dot                = ($r->Dot == null) ? "null" : "'".$r->Dot."'";
            $Flag_Dot           = ($r->Flag_Dot == null) ? "null" : "'".$r->Flag_Dot."'";
            $Satuan             = ($r->Satuan == null) ? "null" : "'".str_replace(["'",","], " ", $r->Satuan)."'";
            $Bentuk             = ($r->Bentuk == null) ? "null" : "'".$r->Bentuk."'";
            $Kandungan          = ($r->Kandungan == null) ? "null" : "'".str_replace(["'",","], " ", $r->Kandungan)."'";
            $PR_Diawasi         = ($r->PR_Diawasi == null) ? "null" : "'".$r->PR_Diawasi."'";
            $Multi_Segmen       = ($r->Multi_Segmen == null) ? "null" : "'".$r->Multi_Segmen."'";
            $Farmalkes          = ($r->Farmalkes == null) ? "null" : "'".$r->Farmalkes."'";
            $Diskon             = ($r->Diskon == null) ? "null" : "'".$r->Diskon."'";
            $Diskon_Off_Faktur  = ($r->Diskon_Off_Faktur == null) ? "null" : "'".$r->Diskon_Off_Faktur."'";
            $Status             = ($r->Status == null) ? "null" : "'".$r->Status."'";
            $PPH22              = ($r->PPH22 == null) ? "null" : "'".$r->PPH22."'";
            $Paket              = ($r->Paket == null) ? "null" : "'".$r->Paket."'";
            $SDisc              = ($r->SDisc == null) ? "null" : "'".$r->SDisc."'";
            $SReg               = ($r->SReg == null) ? "null" : "'".$r->SReg."'";
            $Kode_Lain          = ($r->Kode_Lain == null) ? "null" : "'".$r->Kode_Lain."'";
            $Tgl_SOBH           = ($r->Tgl_SOBH == null) ? "null" : "'".$r->Tgl_SOBH."'";
            $Produk_Konversi    = ($r->Produk_Konversi == null) ? "null" : "'".$r->Produk_Konversi."'";
            $Kon1               = ($r->Kon1 == null) ? "null" : "'".$r->Kon1."'";
            $Kon2               = ($r->Kon2 == null) ? "null" : "'".$r->Kon2."'";
            $Max_Konv           = ($r->Max_Konv == null) ? "null" : "'".$r->Max_Konv."'";
            $Tipe               = ($r->Tipe == null) ? "null" : "'".$r->Tipe."'";
            $Konsinyasi         = ($r->Konsinyasi == null) ? "null" : "'".$r->Konsinyasi."'";
            $Gol_Warna          = ($r->Gol_Warna == null) ? "null" : "'".$r->Gol_Warna."'";
            $Panjang            = ($r->Panjang == null) ? "null" : "'".$r->Panjang."'";
            $Lebar              = ($r->Lebar == null) ? "null" : "'".$r->Lebar."'";
            $Tinggi             = ($r->Tinggi == null) ? "null" : "'".$r->Tinggi."'";
            $Berat              = ($r->Berat == null) ? "null" : "'".$r->Berat."'";
            $Kode_SMS           = ($r->Kode_SMS == null) ? "null" : "'".$r->Kode_SMS."'";
            $Produk_Non_Beli    = ($r->Produk_Non_Beli == null) ? "null" : "'".$r->Produk_Non_Beli."'";
            $Status_Aktif       = ($r->Status_Aktif == null) ? "null" : "'".$r->Status_Aktif."'";
            $Ijin_Edar          = ($r->Ijin_Edar == null) ? "null" : "'".$r->Ijin_Edar."'";
            $Created_by         = ($r->Created_by == null) ? "null" : "'".$r->Created_by."'";
            $Updated_by         = ($r->Updated_by == null) ? "null" : "'".$r->Updated_by."'";
            $Created_at         = ($r->Created_at == null) ? "null" : "'".$r->Created_at."'";
            $Updated_at         = ($r->Updated_at == null) ? "null" : "'".$r->Updated_at."'";
            $Nama_Lain          = ($r->Nama_Lain == null) ? "null" : "'".str_replace(["'",","], " ", $r->Nama_Lain)."'";
            $DOF                = ($r->DOF == null) ? "null" : "'".$r->DOF."'";
            $TipeHarga          = ($r->TipeHarga == null) ? "null" : "'".$r->TipeHarga."'";
            $TipeR              = ($r->TipeR == null) ? "null" : "'".$r->TipeR."'";
            $Produk_Jadi        = ($r->Produk_Jadi == null) ? "null" : "'".$r->Produk_Jadi."'";


            $vproduk .= "(".$id.", ".$Kode_Produk.", ".$Produk.", ".$Prinsipal.", ".$Prinsipal2.", ".$Pabrik.", ".$Supplier1.", ".$Supplier2.", ".$Kategori.", ".$Dot.", ".$Flag_Dot.", ".$Satuan.", ".$Bentuk.", ".$Kandungan.", ".$PR_Diawasi.", ".$Multi_Segmen.", ".$Farmalkes.", ".$Diskon.", ".$Diskon_Off_Faktur.", ".$Status.", ".$PPH22.", ".$Paket.", ".$SDisc.", ".$SReg.", ".$Kode_Lain.", ".$Tgl_SOBH.", ".$Produk_Konversi.", ".$Kon1.", ".$Kon2.", ".$Max_Konv.", ".$Tipe.", ".$Konsinyasi.", ".$Gol_Warna.", ".$Panjang.", ".$Lebar.", ".$Tinggi.", ".$Berat.", ".$Kode_SMS.", ".$Produk_Non_Beli.", ".$Status_Aktif.", ".$Ijin_Edar.", ".$Created_by.", ".$Updated_by.", ".$Created_at.", ".$Updated_at.", ".$Nama_Lain.", ".$DOF.", ".$TipeHarga.", ".$TipeR.", ".$Produk_Jadi."),";
          }

          $produk_dump=rtrim($vproduk,",");

          $this->db->truncate("mst_produk");
          $valid = $this->db->query("INSERT INTO `mst_produk` VALUES ".$produk_dump);
      // end update produk
      return $valid;
    }

    public function Salesman()
    {   
        $query = $this->db->query("select Kode,Nama from mst_karyawan where Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' ")->result();

        return $query;
    }

    public function getHargaBeli($kode = NULL)
    {        
         $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA, ifnull(Dsc_Cab,0) as 'Dsc_Cab',ifnull(Dsc_Pri,0) as 'Dsc_Pri',ifnull(Dsc_Beli_Pst,0) as 'Dsc_Beli_Pst', ifnull(Dsc_Beli_Cab,0) as 'Dsc_Beli_Cab' from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'"); 

        $num_query = $query->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,ifnull(Dsc_Cab,0) as 'Dsc_Cab',ifnull(Dsc_Pri,0) as 'Dsc_Pri', ifnull(Dsc_Beli_Pst,0) as 'Dsc_Beli_Pst', ifnull(Dsc_Beli_Cab,0) as 'Dsc_Beli_Cab' from mst_harga where Produk ='".$kode."' and  ifnull(Cabang,'')='' limit 1")->row(); 
        }else{
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,ifnull(Dsc_Cab,0) as 'Dsc_Cab',ifnull(Dsc_Pri,0) as 'Dsc_Pri', ifnull(Dsc_Beli_Pst,0) as 'Dsc_Beli_Pst', ifnull(Dsc_Beli_Cab,0) as 'Dsc_Beli_Cab' from mst_harga where Produk ='".$kode."' and  Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        } 
        return $query;
    }
}