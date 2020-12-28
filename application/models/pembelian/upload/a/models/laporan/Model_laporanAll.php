<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");

class Model_laporanAll extends CI_Model
{
    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            // $this->load->model('pembelian/Model_order');
            // $this->load->model('Model_main');
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function getdatapiutangBM($start_date=null,$end_date=null,$cabang=null)
    {   
        $month = date('m',strtotime($end_date));
        $year = date('Y',strtotime($end_date));
        $query = $this->db->query("SELECT mst_cabang.`Region1`,
                                          mst_cabang.`Cabang`,
                                         IFNULL(delivery.value,0) AS 'DO',
                                         IFNULL(faktur.value,0) AS 'sales',
                                         m_target.target,
                                         ROUND(((IFNULL(faktur.value,0)/m_target.target)*100),2) AS 'Ach',
                                         piut45.piutang AS 'Piutang45',
                                         piutang.piutang AS 'Piutang',
                                         ROUND((piut45.piutang/piutang.piutang)*100,2) AS 'piut45',
                                         stok.valuestok AS 'valuestok',
                                         ROUND((stok.valuestok/IFNULL(faktur.value,0)),2) AS 'rasio'
                                    FROM mst_cabang LEFT JOIN
                                          (SELECT trs_delivery_order_sales.cabang,
                                            SUM(IFNULL(trs_delivery_order_sales.`Value`,0)) AS 'value'
                                                 FROM trs_delivery_order_sales
                                                 WHERE trs_delivery_order_sales.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."' AND 
                                                       trs_delivery_order_sales.`Status` IN ('Open','Kirim','Terima') and trs_delivery_order_sales.TipeDokumen ='DO'

                                                 GROUP BY trs_delivery_order_sales.cabang ) AS delivery ON delivery.cabang = mst_cabang.`Cabang`
                                              LEFT JOIN
                                              (SELECT trs_faktur.`Cabang`,
                                                SUM(IFNULL(trs_faktur.`Value`,0)) AS 'value'
                                                 FROM trs_faktur
                                                WHERE trs_faktur.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."' AND trs_faktur.TipeDokumen in ('Faktur','Retur')    
                                             GROUP BY trs_faktur.`Cabang`)AS faktur ON faktur.Cabang =  mst_cabang.`Cabang`
                                             LEFT JOIN 
                                             (SELECT mst_target_cabang.`Cabang`,SUM(Target) AS 'target' 
                                               FROM mst_target_cabang 
                                               WHERE  YEAR(mst_target_cabang.`Tgl`) = '".$year."' AND MONTH(mst_target_cabang.`Tgl`) ='".$month."'
                                              GROUP BY mst_target_cabang.`Cabang`) AS m_target ON m_target.Cabang = mst_cabang.`Cabang`
                                             LEFT JOIN 
                                             (SELECT trs_faktur.Cabang,SUM(trs_faktur.`Saldo` + trs_faktur.`saldo_giro`)  AS 'piutang'
                                                FROM trs_faktur
                                               WHERE  DATEDIFF('".$end_date."', trs_faktur.`TglFaktur`) >= 0 AND
                                                      DATEDIFF('".$end_date."', trs_faktur.`TglFaktur`) <= 45 AND 
                                                       (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                                      Status in ( 'Open','OpenDIH','Giro')
                                               GROUP BY trs_faktur.Cabang) AS piut45 ON  piut45.Cabang = mst_cabang.`Cabang`
                                              LEFT JOIN 
                                              (SELECT trs_faktur.Cabang,SUM(trs_faktur.`Saldo` + trs_faktur.`saldo_giro`)  AS 'piutang'
                                                 FROM trs_faktur
                                                WHERE  (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0 and 
                                                      Status in ( 'Open','OpenDIH','Giro')
                                                GROUP BY trs_faktur.Cabang) AS piutang ON  piutang.Cabang = mst_cabang.`Cabang`
                                                LEFT JOIN 
                                              (SELECT trs_invdet.`Cabang`,SUM(trs_invdet.`ValueStok`) AS 'valuestok'
                                               FROM trs_invdet
                                              WHERE trs_invdet.`UnitStok` <> 0 
                                              GROUP BY trs_invdet.`Cabang`) AS stok ON stok.Cabang = mst_cabang.`Cabang`
                                         WHERE mst_cabang.`Cabang` ='".$cabang."';")->result();

        return $query;
    }

    public function getdatasalesharian($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT trs_faktur_detail.`TglFaktur`,
                                          CONCAT(trs_faktur_detail.`Pelanggan`,' - ',trs_faktur_detail.`NamaPelanggan`) AS 'Pelanggan',
                                          trs_faktur_detail.`TipeDokumen`,
                                          trs_faktur_detail.`TipePelanggan`,
                                          trs_faktur_detail.`Rayon`,
                                          (WEEK(trs_faktur_detail.`TglFaktur`, 3) - WEEK(trs_faktur_detail.`TglFaktur` - INTERVAL DAY(trs_faktur_detail.`TglFaktur`)-1 DAY, 3) + 1) as 'week',
                                    CONCAT(trs_faktur_detail.`Salesman`,' - ',mst_karyawan.`Nama`) AS 'Salesman',
                                    mst_produk.`Prinsipal`,
                                    trs_faktur_detail.`Value`
                                FROM trs_faktur_detail 
                                LEFT JOIN mst_karyawan on trs_faktur_detail.Salesman = mst_karyawan.Kode
                                LEFT JOIN `mst_produk` ON trs_faktur_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$start_date."' and '".$end_date."'
                                and IFNULL(trs_faktur_detail.Status,0) not like  '%Batal%'
                                order by trs_faktur_detail.`TglFaktur`,week,mst_produk.`Prinsipal`")->result();

        return $query;
    }

    public function getdataDOharian($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT trs_delivery_order_sales_detail.`TglDO`,
                                          (WEEK(trs_delivery_order_sales_detail.`TglDO`, 3) - WEEK(trs_delivery_order_sales_detail.`TglDO` - INTERVAL DAY(trs_delivery_order_sales_detail.`TglDO`)-1 DAY, 3) + 1) as 'week',
                                    CONCAT(trs_delivery_order_sales_detail.`Salesman`,' - ',mst_karyawan.`Nama`) AS 'Salesman',
                                    CONCAT(trs_delivery_order_sales_detail.`Pelanggan`,' - ',trs_delivery_order_sales_detail.`NamaPelanggan`) AS 'Pelanggan',
                                          trs_delivery_order_sales_detail.`TipeDokumen`,
                                          trs_delivery_order_sales_detail.`TipePelanggan`,
                                          trs_delivery_order_sales_detail.`Rayon`,
                                    mst_produk.`Prinsipal`,
                                    trs_delivery_order_sales_detail.`Value`
                                FROM trs_delivery_order_sales_detail 
                                LEFT JOIN mst_karyawan on trs_delivery_order_sales_detail.Salesman = mst_karyawan.Kode
                                LEFT JOIN `mst_produk` ON trs_delivery_order_sales_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                WHERE trs_delivery_order_sales_detail.`TglDO` BETWEEN '".$start_date."' and '".$end_date."' 
                                        and trs_delivery_order_sales_detail.Status in ('Open','Kirim','Closed','Terima') and ifnull(status_retur,'')='N' and trs_delivery_order_sales_detail.TipeDokumen ='DO'
                                  order by trs_delivery_order_sales_detail.`TglDO`,week,mst_produk.`Prinsipal`")->result();
        return $query;
    }

    public function getdatapiutangpelanggan($cabang=null,$pelanggan=null)
    {   
        // $data = array(); 
        $header = $this->db->query("select mst_pelanggan.Alamat,mst_pelanggan.Nama_Faktur as Pelanggan,mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.saldo,
                                           faktur_pelanggan.Umur_faktur,
                                           (mst_pelanggan.limit_kredit - faktur_pelanggan.saldo) as 'sisa'
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             (case when sum(saldo) = sum(total) then sum(saldo) else (sum(saldo) + sumgiro.totalgiro) end ) as 'saldo',
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(trs_pelunasan_giro_detail.`ValuePelunasan`) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.`Pelanggan`
                                      where (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0) and 
                                        trs_faktur.Status not in ('Usulan','Batal','Reject')
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$pelanggan."'")->result();

        $query = $this->db->query("SELECT DISTINCT trs_faktur.`NoFaktur`,
                                           trs_faktur.`TglFaktur`,
                                           trs_faktur.Acu,
                                           (case when trs_faktur.TipeDokumen ='Faktur' then 'F'
                                                 when trs_faktur.TipeDokumen ='Retur' then 'R'
                                                 when trs_faktur.TipeDokumen ='CN' then 'CN'
                                                 when trs_faktur.TipeDokumen ='DN' then 'DN' 
                                                 else '' end ) as 'TipeDokumen',
                                           trs_faktur.`TglJtoFaktur`,
                                           CONCAT(trs_faktur.`Pelanggan`,' - ',trs_faktur.`NamaPelanggan`) AS 'Pelanggan',
                                           trs_faktur.`AlamatFaktur`,
                                           trs_faktur.`CaraBayar`,
                                           DATEDIFF(curdate(),trs_faktur.`TglFaktur`) AS 'Umur_faktur',
                                           CONCAT(trs_faktur.`Salesman`,' - ',trs_faktur.`NamaSalesman`) AS 'Salesman',
                                           trs_faktur.`Total`,
                                          (case when trs_faktur.`Saldo` = trs_faktur.`Total` then trs_faktur.`Saldo` 
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) != ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + trs_faktur.saldo_giro
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) = ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + ifnull(giro.bayargiro,0) else trs_faktur.`Saldo`
                                                end ) as 'Saldo',
                                           IFNULL(pelunasan.bayar,0) AS 'pelunasan',
                                           giro.giro AS 'Nogiro',
                                           giro.TglGiro AS 'TglGiro',
                                           giro.tglJTO AS 'tglJTO',
                                           ifnull(giro.bayargiro,0) as 'bayargiro'
                                     FROM  trs_faktur LEFT JOIN 
                                        ( SELECT trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`,
                                             SUM(ifnull(trs_pelunasan_detail.`ValuePelunasan`,'')+ifnull(trs_pelunasan_detail.`value_pembulatan`,'')+ifnull(trs_pelunasan_detail.`value_transfer`,'')+ifnull(trs_pelunasan_detail.`materai`,'')) AS 'bayar'
                                          FROM trs_pelunasan_detail 
                                          where  trs_pelunasan_detail.status not in ('Batal','Giro Tolak','GiroBatal') 
                                          GROUP BY trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`) AS pelunasan ON pelunasan.NomorFaktur = trs_faktur.`NoFaktur` AND pelunasan.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                                    trs_pelunasan_giro_detail.Status,
                                           trs_pelunasan_giro_detail.`TglPelunasan` as 'TglGiro',
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            trs_pelunasan_giro_detail.`Giro`,
                                            (ifnull(trs_pelunasan_giro_detail.`ValuePelunasan`,'')+ifnull(trs_pelunasan_giro_detail.`value_pembulatan`,'')+ifnull(trs_pelunasan_giro_detail.`value_transfer`,'')+ifnull(trs_pelunasan_giro_detail.`materai`,'')) AS 'bayargiro',
                                            trs_giro.tglJTO as 'tglJTO'
                                         FROM trs_pelunasan_giro_detail left join 
                                              trs_giro on trs_pelunasan_giro_detail.`Giro` = trs_giro.Nogiro
                                         where trs_pelunasan_giro_detail.Status not in ('Closed','Batal','Giro Tolak','GiroTolak','GiroBatal')
                                          ) AS giro ON giro.NomorFaktur = trs_faktur.`NoFaktur` AND giro.KodePelanggan = trs_faktur.`Pelanggan`             
                                    WHERE (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0)  AND 
                                       trs_faktur.Status not in ('Usulan','Batal','Reject') and       
                                          trs_faktur.`Pelanggan` = '".$pelanggan."'
                                    order by Umur_faktur Desc")->result();
        $row = [];
        foreach ($query as $query) {
            $x = array_search($query->NoFaktur, array_column($row,'NoFaktur'));
            if(strlen($x) == 0){
                array_push($row,
                    [
                    'NoFaktur'      => $query->NoFaktur,
                    'TipeDokumen'   => $query->TipeDokumen,
                    'Acu'           => $query->Acu,
                    'TglFaktur'     => $query->TglFaktur,
                    'TglJtoFaktur'  => $query->TglJtoFaktur,
                    'Alamat'        => $query->AlamatFaktur,
                    'Pelanggan'     => $query->Pelanggan,
                    'CaraBayar'     => $query->CaraBayar,
                    'Umur_faktur'   => $query->Umur_faktur,
                    'Salesman'      => $query->Salesman,
                    'Total'         => $query->Total,
                    'Saldo'         => $query->Saldo,
                    'pelunasan'     => $query->pelunasan,
                    'Giro'          => $query->Nogiro,
                    'TglGiro'       => $query->TglGiro,
                    'tglJTO'        => $query->tglJTO,
                    'value_giro'    => $query->bayargiro,
                    ]);
            }else{
                array_push($row, [
                    'NoFaktur'      => $query->NoFaktur,
                    'TipeDokumen'   => $query->TipeDokumen,
                    'Acu'           => $query->Acu,
                    'TglFaktur'     => $query->TglFaktur,
                    'Alamat'        => $query->AlamatFaktur,
                    'Pelanggan'     => $query->Pelanggan,
                    'CaraBayar'     => $query->CaraBayar,
                    'Umur_faktur'   => $query->Umur_faktur,
                    'Salesman'      => $query->Salesman,
                    'Total'         => '',
                    'Saldo'         => '',
                    'pelunasan'     => '',
                    'Giro'          => $query->Nogiro,
                    'TglGiro'       => $query->TglGiro,
                    'tglJTO'        => $query->tglJTO,
                    'value_giro'    => $query->bayargiro,
                ]);
            }
            // $data[] = $row;          
        }
        $data = array("row" =>$row, "header" =>$header);
        return $data;
    }

    public function Prinsipal2()
    {   
        $query = $this->db->query("select distinct Prinsipal2 from mst_prinsipal order by Prinsipal2 asc")->result();

        return $query;
    }

    public function Prinsipal2bycode($prinsipal=null)
    {   
        $query = $this->db->query("select distinct Prinsipal from mst_prinsipal where prinsipal2 = '".$prinsipal."' order by Prinsipal2 asc")->result();

        return $query;
    }

    
    public function getnilaipl($mon=null,$yer=null,$cabang=null)
    {
      log_message('error',print_r($mon,true));
      log_message('error',print_r($yer,true));
       $p1 = $this->db->query("-- PERSEDIAAN AWAL ----------------------
            SELECT SUM(IFNULL(trs_invsum.`UnitCOGS`,0)* IFNULL(trs_invsum.`SAwal$mon`,0)) AS 'valawal'
            FROM trs_invsum
            WHERE tahun ='$yer';")->row();
       $p2 = $this->db->query("-- Pembelian ----------------------------
            SELECT SUM(IFNULL(trs_terima_barang_detail.`Value`,0)) AS 'Pembelian'
            FROM  trs_terima_barang_detail 
            WHERE STATUS NOT IN ('pending','Batal') AND
                  IFNULL(Tipe,'') != 'BKB' AND 
                  Cabang ='Lampung' AND
                  MONTH(TglDokumen) = MONTH(CURDATE());")->row();
       $p3 = $this->db->query("-- BKB ----------------------------
            SELECT SUM(IFNULL(trs_terima_barang_detail.`Value`,0)) AS 'Pembelian'
            FROM  trs_terima_barang_detail 
            WHERE STATUS NOT IN ('pending','Batal') AND
                  IFNULL(Tipe,'') = 'BKB' AND 
                  Cabang ='Lampung' AND
                  MONTH(TglDokumen) = MONTH(CURDATE());")->row();
       $p4 = $this->db->query(" -- Terima Relokasi ----------------------

            SELECT SUM(IFNULL(trs_relokasi_terima_detail.`Value`,0)) AS 'BPB_Relokasi'
            FROM trs_relokasi_terima_detail
            WHERE STATUS = 'Terima' AND 
                  MONTH(tgl_terima) = MONTH(CURDATE());")->row();
       $p5 = $this->db->query("-- Kirim Relokasi ----------------------

            SELECT SUM(IFNULL(trs_relokasi_kirim_detail.`Value`,0)) AS 'BPB_Relokasi'
            FROM trs_relokasi_kirim_detail
            WHERE STATUS = 'Kirim' AND 
                  MONTH(tgl_kirim) = MONTH(CURDATE());")->row();
       $p6 = $this->db->query("-- PERSEDIAAN Akhir ----------------------
            SELECT SUM(IFNULL(trs_invsum.`UnitCOGS`,0)* IFNULL(trs_invsum.`UnitStok`,0)) AS 'valakhir'
            FROM trs_invsum
            WHERE tahun ='2019';")->row();
       $p7 = $this->db->query("-- Penjualan --------------------------------
            SELECT SUM(IFNULL(trs_faktur.`Value`,0)) AS 'penjualan'
            FROM  trs_faktur
            WHERE STATUS NOT IN ('Usulan','Batal') AND 
                  MONTH(trs_faktur.`TglFaktur`) = MONTH(CURDATE());")->row();
       $p8 = $this->db->query("-- Biaya Gaji -----------------------------------  
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_gaji'
            FROM trs_buku_transaksi
            WHERE Transaksi IN ('Biaya BPJS','Biaya Gaji','Biaya Jamsostek') AND
                  MONTH(Tanggal) = '$mon';")->row();
       $p9 = $this->db->query("-- Biaya Penjualan ---------------------------------
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_penjualan'
            FROM trs_buku_transaksi
            WHERE Transaksi IN (
              'BBM Kirim Barang / Supir',
              'Biaya Administrasi Tender',
              'Biaya BBM BM,SPV,Salesman',
              'Biaya Ekspedisi',
              'Biaya Hotel BM,SPV,Salesman',
              'Biaya Kasus',
              'Biaya Komisi Fee Panel Mitra',
              'Biaya MRK',
              'Biaya Operasional Mitra',
              'Biaya Parkir - Tol BM,SPV,Salesman',
              'Biaya Parkir - Tol Kirim Barang / Supir',
              'Biaya Penghapusan Barang ED',
              'Biaya Penghapusan Piutang',
              'Biaya Promosi Pelanggan/Entertaint Outlet',
              'Biaya Pulsa SPV,BM,DM,SM',
              'Biaya Reward',
              'Biaya transport BM,SPV,Salesman',
              'Biaya Uang Makan BM,SPV,Salesman',
              'Biaya Uang makan kirim barang / Supir'
              ) AND MONTH(Tanggal) = '$mon';")->row();
       $p10 = $this->db->query("-- Biaya Sewa ------------------------------------
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_sewa'
            FROM trs_buku_transaksi
            WHERE Transaksi IN ('Biaya Sewa dibayar dimuka') AND MONTH(Tanggal) = '$mon';")->row();
       $p11 = $this->db->query("-- Biaya Administrasi ---------------------------- 
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_admin'
            FROM trs_buku_transaksi
            WHERE Transaksi IN (
              'Bayar Bunga Pinjaman Bank',
              'Biaya Administrasi Bank',
              'Biaya Alat Tulis Kantor / Supplies',
              'Biaya Fotocopy',
              'Biaya Kirim dokumen',
              'Biaya Percetakan') AND MONTH(Tanggal) = '$mon';")->row();
       $p12 = $this->db->query("-- Biaya Umum -------------------------------------
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_umum'
            FROM trs_buku_transaksi
            WHERE Transaksi IN (
              'Biaya Asuransi Gedung',
              'Biaya Asuransi Kendaraan',
              'Biaya BBM Staff',
              'Biaya Entertainment Karyawan',
              'Biaya Entertainment Principal / Dinas / Konsultan',
              'Biaya Hotel Staff',
              'Biaya HP Cabang',
              'Biaya Internet Cabang',
              'Biaya Iuran dan Retribusi Lainnya',
              'Biaya Legal dan Perizinan',
              'Biaya Listrik Cabang',
              'Biaya Listrik Principal',
              'Biaya Parkir dan Tol Staff',
              'Biaya PDAM',
              'Biaya Perawatan bangunan',
              'Biaya Perawatan inventaris',
              'Biaya Perawatan kendaraan',
              'Biaya Pulkam',
              'Biaya Recruitment',
              'Biaya RTK',
              'Biaya Telpon Cabang',
              'Biaya Training / Seminar',
              'Biaya Transport Staff',
              'Biaya Uang Makan Staff',
              'Pengembalian Pinjaman',
              'Pinjaman Karyawan') AND MONTH(Tanggal) = '$mon';")->row();
       $p13 = $this->db->query("-- Biaya Penyusutan -------------------------------- 
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_penyusutan'
            FROM trs_buku_transaksi
            WHERE Transaksi IN (
              'Biaya Depresiasi Penyusutan Bangunan',
              'Biaya Depresiasi Penyusutan Kendaraan Bermotor',
              'Biaya Depresiasi Penyusutan Peralatan Kantor',
              'Biaya Depresiasi Penyusutan Program Software Komputer') AND MONTH(Tanggal) = '$mon';")->row();
       $p14 = $this->db->query("-- Biaya Pajak -------------------------------------
            SELECT SUM(IFNULL(jumlah,0)) AS 'biaya_pajak'
            FROM trs_buku_transaksi
            WHERE Transaksi IN (
              'Biaya Pajak PBB & Pajak Daerah',
              'Biaya Pajak STNK , KIR KendaRAAN bermotor') AND MONTH(Tanggal) = '$mon';")->row();
  
      

       $output = array('PERSEDIAANAWAL' => $p1,
       'Pembelian' => $p2,
       'BKB' => $p3, 
       'TerimaRelokasi' => $p4,
       'KirimRelokasi' => $p5,
       'PERSEDIAANAkhir' => $p6,
       'Penjualan' => $p7,
       'BiayaGaji' => $p8,
       'BiayaPenjualan' => $p9,
       'BiayaSewa' => $p10,
       'BiayaAdministrasi' => $p11,
       'BiayaUmum' => $p12,
       'BiayaPenyusutan' => $p13,
       'BiayaPajak' => $p14);
        
        return $output;
    }

    public function getdatasalesbyprinsipal($start_date=null,$end_date=null,$cabang=null,$prinsipal=null,$prinsipal2=null)
    { 
        $month = date('m',strtotime($end_date));
        $year = date('Y',strtotime($end_date));

        // log_message('error',$prinsipal);
        // log_message('error',$prinsipal2);

        if($prinsipal == 'All' and $prinsipal2 == 'All'){

            $query = $this->db->query("
                                  SELECT  c.`Cabang`,
                                    a.`Prinsipal`,a.`Prinsipal2`,
                                    IFNULL(b.Target,0) AS 'target',
                                    c.`Region1`, 
                                    IFNULL(dodet.value,0) AS valuedo,
                                    IFNULL(dodet.outlet,0) AS outletdo,
                                    IFNULL(faktur.value,0) AS `value`,
                                    IFNULL(faktur.outlet,0) AS outlet,
                                    (IFNULL(dodet.value,0) + IFNULL(faktur.value,0)) AS totalall,
                                    ROUND(IFNULL(((faktur.value/b.target) * 100),0),2) AS 'persentase',
                                    ROUND(IFNULL((((IFNULL(dodet.value,0) + IFNULL(faktur.value,0))/b.target) * 100),0),2) AS 'persentaseall'
                                  FROM `mst_prinsipal` a
                                  LEFT JOIN mst_target_cabang b ON 
                                            a.`Prinsipal`=b.`Prinsipal` AND b.`Cabang`='".$cabang."'
                                            AND YEAR(b.`Tgl`) = '".$year."' AND MONTH(b.`Tgl`) ='".$month."'
                                  LEFT JOIN `mst_cabang` c ON c.`Cabang` = '".$cabang."'
                                  LEFT JOIN (
                                      SELECT trs_faktur_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_faktur_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_faktur_detail.`Pelanggan`) AS 'outlet'
                                      FROM  trs_faktur_detail
                                      LEFT JOIN `mst_produk` ON trs_faktur_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_faktur_detail.Cabang ='".$cabang."' 
                                        AND IFNULL(trs_faktur_detail.`Status`,'') NOT LIKE 'Batal'                                       
                                        AND trs_faktur_detail.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_faktur_detail.`Cabang`,
                                      mst_produk.`Prinsipal`) AS faktur ON 
                                            faktur.Cabang = '".$cabang."' 
                                            AND faktur.Prinsipal = a.`Prinsipal`
                                  LEFT JOIN (
                                      SELECT trs_delivery_order_sales_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_delivery_order_sales_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_delivery_order_sales_detail.`Pelanggan`) AS 'outlet'
                                      FROM  `trs_delivery_order_sales_detail`
                                      LEFT JOIN `mst_produk` ON trs_delivery_order_sales_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_delivery_order_sales_detail.Cabang ='".$cabang."' 
                                        AND trs_delivery_order_sales_detail.`Status` IN ('Open','Kirim','Terima') and trs_delivery_order_sales_detail.TipeDokumen ='DO'
                                        AND trs_delivery_order_sales_detail.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_delivery_order_sales_detail.`Cabang`,
                                      `mst_produk`.`Prinsipal`) AS dodet ON 
                                            dodet.Cabang ='".$cabang."' 
                                            AND dodet.Prinsipal = a.`Prinsipal`
                                  ORDER BY a.`Prinsipal`,a.`Prinsipal2`                                  
                        ")->result();

        }elseif($prinsipal != 'All' and $prinsipal2 != 'All'){

            $query = $this->db->query("
                                  SELECT  c.`Cabang`,
                                    a.`Prinsipal`,a.`Prinsipal2`,
                                    IFNULL(b.Target,0) AS 'target',
                                    c.`Region1`, 
                                    IFNULL(dodet.value,0) AS valuedo,
                                    IFNULL(dodet.outlet,0) AS outletdo,
                                    IFNULL(faktur.value,0) AS `value`,
                                    IFNULL(faktur.outlet,0) AS outlet,
                                    (IFNULL(dodet.value,0) + IFNULL(faktur.value,0)) AS totalall,
                                    ROUND(IFNULL(((faktur.value/b.target) * 100),0),2) AS 'persentase',
                                    ROUND(IFNULL((((IFNULL(dodet.value,0) + IFNULL(faktur.value,0))/b.target) * 100),0),2) AS 'persentaseall'
                                  FROM `mst_prinsipal` a
                                  LEFT JOIN mst_target_cabang b ON 
                                            a.`Prinsipal`=b.`Prinsipal` AND b.`Cabang`='".$cabang."'
                                            AND YEAR(b.`Tgl`) = '".$year."' AND MONTH(b.`Tgl`) ='".$month."'
                                  LEFT JOIN `mst_cabang` c ON c.`Cabang` = '".$cabang."'
                                  LEFT JOIN (
                                      SELECT trs_faktur_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_faktur_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_faktur_detail.`Pelanggan`) AS 'outlet'
                                      FROM  trs_faktur_detail
                                      LEFT JOIN `mst_produk` ON trs_faktur_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_faktur_detail.Cabang ='".$cabang."' 
                                       AND IFNULL(trs_faktur_detail.`Status`,'') NOT LIKE 'Batal'                           
                                        AND trs_faktur_detail.`Prinsipal` = '".$prinsipal2."' 
                                        AND trs_faktur_detail.`Prinsipal2` ='".$prinsipal."'                                        
                                        AND trs_faktur_detail.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_faktur_detail.`Cabang`,
                                      mst_produk.`Prinsipal`) AS faktur ON 
                                            faktur.Cabang = '".$cabang."' 
                                            AND faktur.Prinsipal = a.`Prinsipal`
                                  LEFT JOIN (
                                      SELECT trs_delivery_order_sales_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_delivery_order_sales_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_delivery_order_sales_detail.`Pelanggan`) AS 'outlet'
                                      FROM  `trs_delivery_order_sales_detail`
                                      LEFT JOIN `mst_produk` ON trs_delivery_order_sales_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_delivery_order_sales_detail.Cabang ='".$cabang."' 
                                        AND trs_delivery_order_sales_detail.`Status` IN ('Open','Kirim','Terima')
                                        and trs_delivery_order_sales.TipeDokumen ='DO' 
                                        AND trs_delivery_order_sales_detail.`Prinsipal` = '".$prinsipal2."' 
                                        AND trs_delivery_order_sales_detail.`Prinsipal2` ='".$prinsipal."'                                        
                                        AND trs_delivery_order_sales_detail.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_delivery_order_sales_detail.`Cabang`,
                                      `mst_produk`.`Prinsipal`) AS dodet ON 
                                            dodet.Cabang ='".$cabang."' 
                                            AND dodet.Prinsipal = a.`Prinsipal`
                                  WHERE a.`Prinsipal` = '".$prinsipal2."' 
                                        AND a.`Prinsipal2` ='".$prinsipal."'                                        
                                  ORDER BY a.`Prinsipal`,a.`Prinsipal2`                                  
                        ")->result();

        }elseif($prinsipal != 'All' and $prinsipal2 == 'All'){

            $query = $this->db->query("
                                  SELECT  c.`Cabang`,
                                    a.`Prinsipal`,a.`Prinsipal2`,
                                    IFNULL(b.Target,0) AS 'target',
                                    c.`Region1`, 
                                    IFNULL(dodet.value,0) AS valuedo,
                                    IFNULL(dodet.outlet,0) AS outletdo,
                                    IFNULL(faktur.value,0) AS `value`,
                                    IFNULL(faktur.outlet,0) AS outlet,
                                    (IFNULL(dodet.value,0) + IFNULL(faktur.value,0)) AS totalall,
                                    ROUND(IFNULL(((faktur.value/b.target) * 100),0),2) AS 'persentase',
                                    ROUND(IFNULL((((IFNULL(dodet.value,0) + IFNULL(faktur.value,0))/b.target) * 100),0),2) AS 'persentaseall'
                                  FROM `mst_prinsipal` a
                                  LEFT JOIN mst_target_cabang b ON 
                                            a.`Prinsipal`=b.`Prinsipal` AND b.`Cabang`='".$cabang."'
                                            AND YEAR(b.`Tgl`) = '".$year."' AND MONTH(b.`Tgl`) ='".$month."'
                                  LEFT JOIN `mst_cabang` c ON c.`Cabang` = '".$cabang."'
                                  LEFT JOIN (
                                      SELECT trs_faktur_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_faktur_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_faktur_detail.`Pelanggan`) AS 'outlet'
                                      FROM  trs_faktur_detail
                                      LEFT JOIN `mst_produk` ON trs_faktur_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_faktur_detail.Cabang ='".$cabang."' 
                                        AND IFNULL(trs_faktur_detail.`Status`,'') NOT LIKE 'Batal'                           
                                        AND trs_faktur_detail.`Prinsipal2` ='".$prinsipal."'                                        
                                        AND trs_faktur_detail.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_faktur_detail.`Cabang`,
                                      mst_produk.`Prinsipal`) AS faktur ON 
                                            faktur.Cabang = '".$cabang."' 
                                            AND faktur.Prinsipal = a.`Prinsipal`
                                  LEFT JOIN (
                                      SELECT trs_delivery_order_sales_detail.`Cabang`, mst_produk.`Prinsipal`,
                                            SUM(trs_delivery_order_sales_detail.`Value`) AS 'value',
                                            COUNT(DISTINCT trs_delivery_order_sales_detail.`Pelanggan`) AS 'outlet'
                                      FROM  `trs_delivery_order_sales_detail`
                                      LEFT JOIN `mst_produk` ON trs_delivery_order_sales_detail.`KodeProduk`= `mst_produk`.`Kode_Produk`
                                      WHERE trs_delivery_order_sales_detail.Cabang ='".$cabang."' 
                                        AND trs_delivery_order_sales_detail.`Status` IN ('Open','Kirim','Terima')
                                        and trs_delivery_order_sales_detail.TipeDokumen ='DO' 
                                        AND trs_delivery_order_sales_detail.`Prinsipal2` ='".$prinsipal."'                                        
                                        AND trs_delivery_order_sales_detail.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."'
                                      GROUP BY trs_delivery_order_sales_detail.`Cabang`,
                                      `mst_produk`.`Prinsipal`) AS dodet ON 
                                            dodet.Cabang ='".$cabang."' 
                                            AND dodet.Prinsipal = a.`Prinsipal`
                                  WHERE a.`Prinsipal2` ='".$prinsipal."'                                        
                                  ORDER BY a.`Prinsipal`,a.`Prinsipal2`                                  
                        ")->result();

        }

        return $query;
    }
    public function getjumlahfaktur($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT salesman.`Region1`,
                                           salesman.`Cabang`,
                                           CONCAT(salesman.`Kode`,'~',salesman.`Nama`) AS 'salesman',
                                           DO.NoDO AS 'DO',
                                           DO.valueDO AS 'valueDO',
                                           faktur.NoFaktur AS 'faktur',
                                           faktur.value AS 'value_faktur',
                                           retur.NoFaktur AS 'retur',
                                           retur.value AS 'value_retur',
                                           (IFNULL(faktur.NoFaktur,0) - IFNULL(retur.NoFaktur,0)) AS 'Total_Faktur',
                                           (IFNULL(faktur.value,0) + IFNULL(retur.value,0)) AS 'Total_Value'
                                    FROM (SELECT mst_cabang.`Region1`,
                                           mst_cabang.`Cabang`,
                                           mst_karyawan.`Kode`,
                                           mst_karyawan.`Nama`
                                           FROM mst_cabang left join mst_karyawan ON mst_karyawan.`Cabang` = mst_cabang.`Cabang`
                                           WHERE mst_karyawan.`Jabatan` ='Salesman' AND 
                                           mst_cabang.`Cabang` ='".$cabang."' AND 
                                           mst_karyawan.`Status`='Aktif' ) AS salesman
                                        LEFT JOIN
                                            (SELECT trs_delivery_order_sales.`Cabang`,
                                                    trs_delivery_order_sales.`Salesman`,
                                                    COUNT(trs_delivery_order_sales.`NoDO`) AS 'NoDO',
                                                    SUM(trs_delivery_order_sales.`Value`) AS 'valueDO'
                                                    FROM  trs_delivery_order_sales
                                                    WHERE trs_delivery_order_sales.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."' AND
                                                          trs_delivery_order_sales.`Status` not in ('Batal','Closed') and trs_delivery_order_sales.TipeDokumen ='DO'
                                                    GROUP BY trs_delivery_order_sales.`Cabang`,
                                                    trs_delivery_order_sales.`Salesman`) AS DO ON DO.Cabang = salesman.cabang AND 
                                                    DO.Salesman = salesman.Kode
                                         LEFT JOIN
                                         (SELECT trs_faktur.Cabang,
                                                 trs_faktur.`Salesman`,
                                                 count(trs_faktur.NoFaktur) AS 'NoFaktur',
                                                 SUM(trs_faktur.`Value`) AS 'value'
                                            FROM trs_faktur
                                          WHERE  trs_faktur.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."' AND
                                           trs_faktur.`TipeDokumen` ='Faktur'
                                          GROUP BY trs_faktur.Cabang,trs_faktur.`Salesman`) AS faktur ON faktur.Cabang = salesman.cabang AND 
                                        faktur.Salesman = salesman.Kode
                                         LEFT JOIN
                                         (SELECT trs_faktur.Cabang,
                                                 trs_faktur.`Salesman`,
                                                 count(trs_faktur.NoFaktur) AS 'NoFaktur',
                                                 SUM(trs_faktur.`Value`) AS 'value'
                                            FROM trs_faktur
                                          WHERE  trs_faktur.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."' AND
                                           trs_faktur.`TipeDokumen` ='Retur'
                                          GROUP BY trs_faktur.Cabang,trs_faktur.`Salesman`) AS retur ON retur.Cabang = salesman.cabang AND 
                                        retur.Salesman = salesman.Kode
                                    ORDER BY salesman.Kode ASC;")->result();
        return $query;
    }

    public function getdatasalesot($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT mst_calendar.list_date,
                                       IFNULL(jmlDO.jml_DO,0) AS 'jml_DO',
                                       IFNULL(jmlFaktur.jml_faktur,0) AS 'jml_faktur',
                                       IFNULL(jmlDO.val_DO,0) AS 'omsetharian_DO',
                                       IFNULL(jmlFaktur.val_faktur,0) AS 'omsetharian_faktur',
                                       IFNULL(otDOharian.jml_ot,0) AS 'otDOharian',
                                       IFNULL(otharian.jml_ot,0) AS 'otfakturharian',
                                       (SELECT SUM(trs_delivery_order_sales.Value) 
                                        FROM trs_delivery_order_sales 
                                             WHERE trs_delivery_order_sales.TglDO BETWEEN '".$start_date."' AND mst_calendar.list_date AND 
                                             trs_delivery_order_sales.Status IN ( 'Open','Kirim','Terima')) AS 'omzetDObulanan',
                                             (SELECT COUNT(DISTINCT trs_delivery_order_sales.Pelanggan) 
                                        FROM trs_delivery_order_sales 
                                        WHERE trs_delivery_order_sales.TglDO BETWEEN '".$start_date."' AND mst_calendar.list_date AND 
                                            trs_delivery_order_sales.Status IN ( 'Open','Kirim','Terima') and trs_delivery_order_sales.TipeDokumen ='DO') AS 'jml_otDObulanan',
                                        (SELECT SUM(trs_faktur.Value) 
                                        FROM trs_faktur 
                                        WHERE trs_faktur.TglFaktur BETWEEN '".$start_date."' AND mst_calendar.list_date AND 
                                          trs_faktur.TipeDokumen ='Faktur') AS 'omzetfakturbulanan',
                                        (SELECT COUNT(DISTINCT trs_faktur.Pelanggan) 
                                                FROM trs_faktur 
                                               WHERE trs_faktur.TglFaktur BETWEEN '".$start_date."' AND mst_calendar.list_date AND 
                                                     trs_faktur.TipeDokumen ='Faktur') AS 'jml_otfakturbulanan',
                                              (IFNULL(lunas_cash.value,0) + IFNULL(lunas_giro.value,0)) AS 'jml_tagihan'
                                FROM mst_calendar 
                                LEFT JOIN
                                     (SELECT trs_delivery_order_sales.`TglDO`,
                                      COUNT(IFNULL(trs_delivery_order_sales.NoDO,0)) AS 'jml_DO',
                                      SUM(IFNULL(trs_delivery_order_sales.`Value`,0)) AS 'val_DO'
                                      FROM trs_delivery_order_sales
                                      WHERE trs_delivery_order_sales.status IN ('Open','Kirim','Terima') and trs_delivery_order_sales.TipeDokumen ='DO'
                                      GROUP BY trs_delivery_order_sales.`TglDO`) AS jmlDO 
                                      ON jmlDO.`TglDO` = mst_calendar.`list_date`
                                LEFT JOIN 
                                     (SELECT trs_faktur.`TglFaktur`,
                                      COUNT(IFNULL(trs_faktur.`NoFaktur`,0)) AS 'jml_faktur',
                                      SUM(IFNULL(trs_faktur.`Value`,0)) AS 'val_faktur'
                                      FROM trs_faktur
                                      WHERE trs_faktur.TipeDokumen ='Faktur'
                                      GROUP BY trs_faktur.`TglFaktur`) AS jmlFaktur
                                      ON jmlFaktur.`TglFaktur` = mst_calendar.`list_date`
                                 LEFT JOIN
                                     (SELECT trs_delivery_order_sales.TglDO,
                                      COUNT(DISTINCT trs_delivery_order_sales.Pelanggan) AS 'jml_ot' 
                                  FROM trs_delivery_order_sales 
                                        WHERE trs_delivery_order_sales.Status IN ( 'Open','Kirim','Terima') and trs_delivery_order_sales.TipeDokumen ='DO'
                                  GROUP BY trs_delivery_order_sales.TglDO) AS otDOharian ON otDOharian.TglDO =  mst_calendar.`list_date`
                                LEFT JOIN
                                     (SELECT trs_faktur.TglFaktur,
                                      COUNT(DISTINCT trs_faktur.Pelanggan) AS 'jml_ot' 
                                  FROM trs_faktur 
                                  WHERE trs_faktur.TipeDokumen ='Faktur'
                                     GROUP BY trs_faktur.TglFaktur) AS otharian ON otharian.TglFaktur = mst_calendar.`list_date`
                                LEFT JOIN
                                     (SELECT trs_pelunasan_detail.TglPelunasan,
                                             SUM((CASE WHEN Tipedokumen IN ('Faktur','DN') THEN IFNULL(trs_pelunasan_detail.ValuePelunasan,0) ELSE IFNULL(trs_pelunasan_detail.ValuePelunasan * -1,0) END)  + IFNULL(trs_pelunasan_detail.value_pembulatan,0) + 
                                             IFNULL(trs_pelunasan_detail.value_transfer,0)+
                                             IFNULL(trs_pelunasan_detail.materai,0)) AS 'value'
                                      FROM trs_pelunasan_detail
                                      WHERE trs_pelunasan_detail.TipePelunasan IN ('Transfer','Cash') AND 
                                             trs_pelunasan_detail.status != 'Batal'
                                      GROUP BY trs_pelunasan_detail.TglPelunasan) AS lunas_cash ON lunas_cash.TglPelunasan = mst_calendar.`list_date`
                                LEFT JOIN
                                     (SELECT trs_pelunasan_detail.TglGiroCair,
                                      SUM((CASE WHEN Tipedokumen IN ('Faktur','DN') THEN IFNULL(trs_pelunasan_detail.ValuePelunasan,0) ELSE IFNULL(trs_pelunasan_detail.ValuePelunasan * -1,0) END)  + IFNULL(trs_pelunasan_detail.value_pembulatan,0) + 
                                             IFNULL(trs_pelunasan_detail.value_transfer,0)+
                                             IFNULL(trs_pelunasan_detail.materai,0)) AS 'value'
                                  FROM trs_pelunasan_detail
                                  WHERE trs_pelunasan_detail.TipePelunasan ='Giro' and 
                                       trs_pelunasan_detail.status = 'GiroCair'
                                  GROUP BY trs_pelunasan_detail.TglGiroCair) AS lunas_giro ON lunas_giro.TglGiroCair = mst_calendar.`list_date`
                                WHERE mst_calendar.`list_date`  BETWEEN  '".$start_date."' AND '".$end_date."';")->result();
        return $query;
    }

    public function getpelunasanperiode($search = null, $limit = null, $tipe=null,$tgl1=null,$tgl2=null)
    {   
        if ($tipe == "All") {
              $query = $this->db->query("SELECT xx.*,b.`acu2`
                      FROM (
                             SELECT a.*
                             FROM trs_pelunasan_detail a 
                             WHERE a.Cabang='".$this->cabang."' AND a.TglPelunasan BETWEEN '".$tgl1."' and '".$tgl2."'  
                             UNION ALL
                            SELECT a.*
                            FROM trs_pelunasan_giro_detail a 
                            WHERE a.Cabang='".$this->cabang."' AND a.TglPelunasan BETWEEN '".$tgl1."' and '".$tgl2."'
                       )xx LEFT JOIN trs_faktur b ON
                       xx.NomorFaktur = b.`NoFaktur` AND 
                       xx.Cabang = b.`Cabang`
                       where xx.Cabang = '".$this->cabang."' $search
                       ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             UNION ALL
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_giro_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
        }elseif ($tipe == "GiroOut") {
                
          $query = $this->db->query("SELECT xx.*,b.`acu2`
                          FROM (
                              SELECT a.*
                              FROM trs_pelunasan_detail a 
                              WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Giro' AND 
                              a.TglGiroCair BETWEEN '".$tgl1."' AND '".$tgl2."'
                              UNION ALL
                              SELECT a.*
                              FROM trs_pelunasan_giro_detail a 
                              WHERE a.Cabang='".$this->cabang."' AND a.Status='Open' AND 
                                    a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                              )xx LEFT JOIN trs_faktur b ON
                           xx.NomorFaktur = b.`NoFaktur` AND 
                           xx.Cabang = b.`Cabang`
                           where xx.Cabang = '".$this->cabang."' $search
                          ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit ");
        }elseif ($tipe == "Giro") {
          $query = $this->db->query("SELECT xx.*,b.`acu2`
                            FROM (
                                 SELECT a.* 
                                 FROM trs_pelunasan_detail a  
                                 WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Giro' AND 
                                       a.TglGiroCair BETWEEN '".$tgl1."' AND '".$tgl2."'
                                 UNION ALL
                                 SELECT a.*
                                 FROM trs_pelunasan_giro_detail a 
                                 WHERE a.Cabang='".$this->cabang."' AND a.Status='Open' AND 
                                       a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                                 )xx LEFT JOIN trs_faktur b ON
                             xx.NomorFaktur = b.`NoFaktur` AND 
                             xx.Cabang = b.`Cabang`
                             where xx.Cabang = '".$this->cabang."' $search
                            ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit");      
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TipePelunasan = 'Giro' and a.TglGiroCair between '".$tgl1."' and '".$tgl2."' $search
                //                             UNION ALL
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_giro_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.Status='Open' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
        }elseif ($tipe == "Cash") {
          $query = $this->db->query("SELECT xx.*,b.`acu2`
                              FROM (
                                  SELECT a.*
                                  FROM trs_pelunasan_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Cash' AND 
                                        a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                                  UNION ALL
                                  SELECT a.* 
                                  FROM trs_pelunasan_giro_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Cash' AND 
                                  a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."')xx 
                                   LEFT JOIN trs_faktur b ON
                                     xx.NomorFaktur = b.`NoFaktur` AND 
                                     xx.Cabang = b.`Cabang`
                               WHERE xx.Cabang = '".$this->cabang."' $search
                               ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TipePelunasan = 'Cash' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             UNION ALL
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_giro_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TipePelunasan = 'Cash' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
        }elseif ($tipe == "Transfer") {
          $query = $this->db->query("SELECT xx.*,b.`acu2`
                              FROM (
                                  SELECT a.*
                                  FROM trs_pelunasan_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Transfer' AND 
                                        a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                                  UNION ALL
                                  SELECT a.* 
                                  FROM trs_pelunasan_giro_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND a.TipePelunasan = 'Transfer' AND 
                                  a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."')xx 
                                   LEFT JOIN trs_faktur b ON
                                     xx.NomorFaktur = b.`NoFaktur` AND 
                                     xx.Cabang = b.`Cabang`
                               WHERE xx.Cabang = '".$this->cabang."' $search
                               ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TipePelunasan = 'Transfer' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."' $search
                //                             UNION ALL
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_giro_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TipePelunasan = 'Transfer' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."'  $search
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
        }else {
          $query = $this->db->query("SELECT xx.*,b.`acu2`
                              FROM (
                                  SELECT a.*
                                  FROM trs_pelunasan_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND 
                                        a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."'
                                  UNION ALL
                                  SELECT a.* 
                                  FROM trs_pelunasan_giro_detail a 
                                  WHERE a.Cabang='".$this->cabang."' AND 
                                  a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."')xx 
                                   LEFT JOIN trs_faktur b ON
                                     xx.NomorFaktur = b.`NoFaktur` AND 
                                     xx.Cabang = b.`Cabang`
                               WHERE xx.Cabang = '".$this->cabang."' $search
                               ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC $limit");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."'
                //                             UNION ALL
                //                             SELECT a.*,b.`acu2` FROM trs_pelunasan_giro_detail a LEFT JOIN trs_faktur b ON a.`NomorFaktur` = b.`NoFaktur` where a.Cabang='".$this->cabang."' and a.TglPelunasan between '".$tgl1."' and '".$tgl2."'
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
        }
        return $query;
    }

//     public function getefaktur($start_date=null,$end_date=null,$cabang=null){
//          $query = $this->db->query("
//                                   SELECT `pajakDO`.`FK` AS `FK`,
//                                         `pajakDO`.`KodeJenisTransaksi` AS `KodeJenisTransaksi`,
//                                         `pajakDO`.`FGPengganti` AS `FGPengganti`,
//                                         `pajakDO`.`NoFaktur` AS `NoFaktur`,
//                                         `pajakDO`.`MasaPajak` AS `MasaPajak`,
//                                         ROUND(`pajakDO`.`TahunPajak`,0) AS `TahunPajak`,
//                                         `pajakDO`.`TanggalFaktur` AS `TanggalFaktur`,
//                                         `pajakDO`.`NPWP` AS `NPWP`,
//                                         `pajakDO`.`Nama` AS `Nama`,
//                                         `pajakDO`.`AlamatLengkap` AS `AlamatLengkap`,
//                                         `pajakDO`.`JumlahDPP` AS `JumlahDPP`,
//                                         `pajakDO`.`JumlahPpn` AS `JumlahPpn`,
//                                         ROUND(`pajakDO`.`JumlahPpnBM`,0) AS `JumlahPpnBM`,
//                                         `pajakDO`.`IDKeteranganTambahan` AS `IDKeteranganTambahan`,
//                                         ROUND(`pajakDO`.`FGUangMuka`,0) AS `FGUangMuka`,
//                                         ROUND(`pajakDO`.`UangMukaDPP`,0) AS `UangMukaDPP`,
//                                         ROUND(`pajakDO`.`UangMukaPpn`,0) AS `UangMukaPpn`,
//                                         ROUND(`pajakDO`.`UangMukaPpnBM`,0) AS `UangMukaPpnBM`,
//                                         `pajakDO`.`Referensi` AS `Referensi`,
//                                         `pajakDO`.`Keterangan` AS `Keterangan`,
//                                         `pajakDO`.`KeteranganTanggal` AS `KeteranganTanggal`,
//                                         `pajakDO`.`KetTipePajak` AS `KetTipePajak` 
//                                       FROM (SELECT 'FK' AS `FK`,
//                                                     (CASE `b`.`Tipe_Pajak` 
//                                                       WHEN 0 THEN '01' 
//                                                       WHEN 1 THEN '01' 
//                                                       WHEN 4 THEN '01' 
//                                                       WHEN 6 THEN '01' 
//                                                       WHEN 2 THEN '02' 
//                                                       WHEN 3 THEN '03' 
//                                                       WHEN 7 THEN '07' 
//                                                       WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END) 
//                                                       WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) 
//                                                       ELSE '01' END) AS `KodeJenisTransaksi`,
//                                                       0 AS `FGPengganti`,
//                                                       `a`.`NoPajak` AS `NoFaktur`,
//                                                       MONTH(`a`.`TglFaktur`) AS `MasaPajak`,
//                                                       YEAR(`a`.`TglFaktur`) AS `TahunPajak`,
//                                                       DATE_FORMAT(`a`.`TglFaktur`,'%d/%m/%Y') AS `TanggalFaktur`,
//                                                       `b`.`NPWP` AS `NPWP`,
//                                                       `b`.`Nama_Pajak` AS `Nama`,
//                                                       `b`.`Alamat_Pajak` AS `AlamatLengkap`,
//                                                       (case when substring_index(`a`.`Value`,'.',-1) < 5 then round(`a`.`Value`,0) else ceil(`a`.`Value`) end) AS `JumlahDPP`,
//                                                       (case when substring_index(`a`.`Ppn`,'.',-1) < 5 then round(`a`.`Ppn`,0) else ceil(`a`.`Ppn`) end) AS `JumlahPpn`,
                                                       
//                                                       0 AS `JumlahPpnBM`,
//                                                       0 AS `IDKeteranganTambahan`,
//                                                       0 AS `FGUangMuka`,
//                                                       0 AS `UangMukaDPP`,
//                                                       0 AS `UangMukaPpn`,
//                                                       0 AS `UangMukaPpnBM`,
//                                                       CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
//                                                       '' AS `Keterangan`,`a`.`TglFaktur` AS `KeteranganTanggal`,
//                                                       '' AS `KetTipePajak` 
//                                                  FROM `trs_faktur` `a` JOIN `sst`.`mst_pelanggan` `b` 
//                                                  WHERE `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang` AND 
//                                                  `a`.`TglFaktur` BETWEEN  '".$start_date."' and '".$end_date."'
//                                       UNION ALL
//                                       SELECT 'OF' AS `FK`,
//                                              `a`.`KodeProduk` AS `KodeJenisTransaksi`,
//                                              `a`.`NamaProduk` AS `FGPengganti`,
//                                             ROUND(`a`.`Harga`,0) AS `NoFaktur`,
//                                              (IFNULL(`a`.`QtyFaktur`,0) + IFNULL(`a`.`BonusFaktur`,0)) AS `MasaPajak`,
//                                              ROUND(`a`.`Gross`,0) AS `TahunPajak`,
//                                              ROUND(`a`.`Potongan`,0) AS `TanggalFaktur`,
//                                              (case when substring_index(`a`.`Value`,'.',-1) < 5 then round(`a`.`Value`,0) else ceil(`a`.`Value`) end) AS `NPWP`,
//                                              (case when substring_index(`a`.`Ppn`,'.',-1) < 5 then round(`a`.`Ppn`,0) else ceil(`a`.`Ppn`) end) AS `Nama`,
//                                              0 AS `AlamatLengkap`,
//                                              0 AS `JumlahDPP`,
//                                              0 AS `JumlahPpn`,
//                                              0 AS `JumlahPpnBM`,
//                                              0 AS `IDKeteranganTambahan`,
//                                              0 AS `FGUangMuka`,
//                                              0 AS `UangMukaDPP`,
//                                              0 AS `UangMukaPpn`,
//                                              0 AS `UangMukaPpnBM`,
//                                              CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
//                                              '' AS `Keterangan`,
//                                              `a`.`TglFaktur` AS `KeteranganTanggal`,
//                                              '' AS `KetTipePajak` 
//                                        FROM `trs_faktur_detail` `a` JOIN `sst`.`mst_pelanggan` `b` 
//                                        WHERE `a`.`Pelanggan` = `b`.`Kode` AND 
//                                              `a`.`Cabang` = `b`.`Cabang`  AND 
//                                              `a`.`TglFaktur` BETWEEN  '".$start_date."' and '".$end_date."'
//                                     UNION ALL
//                                     SELECT 'FK' AS `FK`,
//                                               (CASE `b`.`Tipe_Pajak` 
//                                               WHEN 0 THEN '01' 
//                                               WHEN 1 THEN '01' 
//                                               WHEN 4 THEN '01' 
//                                               WHEN 6 THEN '01' 
//                                               WHEN 2 THEN '02' 
//                                               WHEN 3 THEN '03' 
//                                               WHEN 7 THEN '07' 
//                                               WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END)
//                                               WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' 
//                                               WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) ELSE '01' END) AS `KodeJenisTransaksi`,
//                                               0 AS `FGPengganti`,
//                                               '' AS `NoDO`,
//                                               MONTH(`a`.`TglDO`) AS `MasaPajak`,
//                                               YEAR(`a`.`TglDO`) AS `TahunPajak`,
//                                               DATE_FORMAT(`a`.`TglDO`,'%d/%m/%Y') AS `TanggalFaktur`,
//                                               `b`.`NPWP` AS `NPWP`,
//                                               `b`.`Nama_Pajak` AS `Nama`,
//                                               `b`.`Alamat_Pajak` AS `AlamatLengkap`,
//                                               (case when substring_index(`a`.`Value`,'.',-1) < 5 then round(`a`.`Value`,0) else ceil(`a`.`Value`) end) AS `JumlahDPP`,
//                                               (case when substring_index(`a`.`Ppn`,'.',-1) < 5 then round(`a`.`Ppn`,0) else ceil(`a`.`Ppn`) end) AS `JumlahPpn`,
//                                               0 AS `JumlahPpnBM`,
//                                               0 AS `IDKeteranganTambahan`,
//                                               0 AS `FGUangMuka`,
//                                               0 AS `UangMukaDPP`,
//                                               0 AS `UangMukaPpn`,
//                                               0 AS `UangMukaPpnBM`,
//                                               CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
//                                               '' AS `Keterangan`,
//                                               `a`.`TglDO` AS `KeteranganTanggal`,
//                                               '' AS `KetTipePajak` 
//                                         FROM `trs_delivery_order_sales` `a` JOIN `sst`.`mst_pelanggan` `b`
//                                         WHERE `a`.`Pelanggan` = `b`.`Kode` AND 
//                                               `a`.`Cabang` = `b`.`Cabang` AND 
//                                               `a`.`Status` IN ('Open','Kirim') AND 
//                                               `a`.`TglDO` BETWEEN  '".$start_date."' and '".$end_date."' 
//                                       UNION ALL
//                                         SELECT 'OF' AS `FK`,
//                                               `a`.`KodeProduk` AS `KodeJenisTransaksi`,
//                                               `a`.`NamaProduk` AS `FGPengganti`,
//                                               `a`.`Harga` AS `NoDO`,
//                                               (IFNULL(`a`.`QtyDO`,0) + IFNULL(`a`.`BonusDO`,0)) AS `MasaPajak`,
//                                               ROUND(IFNULL(`a`.`Gross`,0),0) AS `TahunPajak`,
//                                               ROUND(IFNULL(`a`.`Potongan`,0),0) AS `TanggalFaktur`,
//                                               (case when substring_index(`a`.`Value`,'.',-1) < 5 then round(`a`.`Value`,0) else ceil(`a`.`Value`) end) AS `NPWP`,
//                                              (case when substring_index(`a`.`Ppn`,'.',-1) < 5 then round(`a`.`Ppn`,0) else ceil(`a`.`Ppn`) end) AS `Nama`,
//                                               0 AS `AlamatLengkap`,
//                                               0 AS `JumlahDPP`,
//                                               0 AS `JumlahPpn`,
//                                               0 AS `JumlahPpnBM`,
//                                               0 AS `IDKeteranganTambahan`,
//                                               0 AS `FGUangMuka`,
//                                               0 AS `UangMukaDPP`,
//                                               0 AS `UangMukaPpn`,
//                                               0 AS `UangMukaPpnBM`,
//                                               CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
//                                               '' AS `Keterangan`,
//                                               `a`.`TglDO` AS `KeteranganTanggal`,
//                                               '' AS `KetTipePajak` 
//                                         FROM `trs_delivery_order_sales_detail` `a` JOIN `sst`.`mst_pelanggan` `b`
//                                         WHERE `a`.`Pelanggan` = `b`.`Kode`  AND 
//                                               `a`.`Cabang` = `b`.`Cabang` AND 
//                                               `a`.`Status` IN ('Open','Kirim') AND 
//                                               `a`.`TglDO`BETWEEN  '".$start_date."' and '".$end_date."') `pajakDO`
// ORDER BY `pajakDO`.`Referensi`,`pajakDO`.`FK`,`pajakDO`.`MasaPajak`,`pajakDO`.`KodeJenisTransaksi`;

//           ")->result();
//         return $query;
//     }

     public function getefaktur($start_date=null,$end_date=null,$cabang=null){
         $query = $this->db->query("SELECT `pajakDO`.`FK` AS `FK`,
                           `pajakDO`.`KodeJenisTransaksi` AS `KodeJenisTransaksi`,
                           `pajakDO`.`FGPengganti` AS `FGPengganti`,
                           `pajakDO`.`NoFaktur` AS `NoFaktur`,
                           `pajakDO`.`MasaPajak` AS `MasaPajak`,
                           ROUND(`pajakDO`.`TahunPajak`,0) AS `TahunPajak`,
                           `pajakDO`.`TanggalFaktur` AS `TanggalFaktur`,
                           `pajakDO`.`NPWP` AS `NPWP`,
                           `pajakDO`.`Nama` AS `Nama`,
                           `pajakDO`.`AlamatLengkap` AS `AlamatLengkap`,
                           `pajakDO`.`JumlahDPP` AS `JumlahDPP`,
                           `pajakDO`.`JumlahPpn` AS `JumlahPpn`,
                           ROUND(`pajakDO`.`JumlahPpnBM`,0) AS `JumlahPpnBM`,
                           `pajakDO`.`IDKeteranganTambahan` AS `IDKeteranganTambahan`,
                           ROUND(`pajakDO`.`FGUangMuka`,0) AS `FGUangMuka`,
                           ROUND(`pajakDO`.`UangMukaDPP`,0) AS `UangMukaDPP`,
                           ROUND(`pajakDO`.`UangMukaPpn`,0) AS `UangMukaPpn`,
                           ROUND(`pajakDO`.`UangMukaPpnBM`,0) AS `UangMukaPpnBM`,
                           `pajakDO`.`Referensi` AS `Referensi`,
                           `pajakDO`.`Keterangan` AS `Keterangan`,
                           `pajakDO`.`KeteranganTanggal` AS `KeteranganTanggal`,
                           `pajakDO`.`KetTipePajak` AS `KetTipePajak` 
                      FROM (SELECT 'FK' AS `FK`,
                                 (CASE `b`.`Tipe_Pajak` 
                                  WHEN 0 THEN '01' 
                                  WHEN 1 THEN '01' 
                                  WHEN 4 THEN '01' 
                                  WHEN 6 THEN '01' 
                                  WHEN 2 THEN '02' 
                                  WHEN 3 THEN '03' 
                                  WHEN 7 THEN '07' 
                                  WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END) 
                                  WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) 
                                  ELSE '01' END) AS `KodeJenisTransaksi`,
                                 0 AS `FGPengganti`,
                                 `a`.`NoPajak` AS `NoFaktur`,
                                 MONTH(`a`.`TglFaktur`) AS `MasaPajak`,
                                 YEAR(`a`.`TglFaktur`) AS `TahunPajak`,
                                 DATE_FORMAT(`a`.`TglFaktur`,'%d/%m/%Y') AS `TanggalFaktur`,
                                 CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                 `b`.`Nama_Pajak` AS `Nama`,
                                 `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                 IFNULL(a.`Value`,0) AS 'JumlahDPP',
                                 IFNULL(a.`ppn`,0) AS 'JumlahPpn',
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,`a`.`TglFaktur` AS `KeteranganTanggal`,
                                 '' AS `KetTipePajak` 
                            FROM `trs_faktur` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                            WHERE `a`.`TglFaktur` BETWEEN  '".$start_date."' AND '".$end_date."' AND 
                            `a`.`TipeDokumen` ='Faktur'
                            UNION ALL
                            SELECT 'OF' AS `FK`,
                                 `a`.`KodeProduk` AS `KodeJenisTransaksi`,
                                 `a`.`NamaProduk` AS `FGPengganti`,
                                  ROUND(`a`.`Harga`,0) AS `NoFaktur`,
                                  (IFNULL(`a`.`QtyFaktur`,0) + IFNULL(`a`.`BonusFaktur`,0)) AS `MasaPajak`,
                                  ROUND(`a`.`Gross`,0) AS `TahunPajak`,
                                  ROUND(`a`.`Potongan`,0) AS `TanggalFaktur`,
                                  ROUND(`a`.`Value`,0) AS `NPWP`,
                                  ROUND(`a`.`Ppn`,0) AS `Nama`,
                                  0 AS `AlamatLengkap`,
                                  0 AS `JumlahDPP`,
                                  0 AS `JumlahPpn`,
                                  0 AS `JumlahPpnBM`,
                                  0 AS `IDKeteranganTambahan`,
                                  0 AS `FGUangMuka`,
                                  0 AS `UangMukaDPP`,
                                  0 AS `UangMukaPpn`,
                                  0 AS `UangMukaPpnBM`,
                                  CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                  '' AS `Keterangan`,
                                  `a`.`TglFaktur` AS `KeteranganTanggal`,
                                  '' AS `KetTipePajak` 
                            FROM `trs_faktur_detail` `a` LEFT JOIN `sst`.`mst_pelanggan` `b`
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang` 
                            WHERE `a`.`TglFaktur` BETWEEN  '".$start_date."' AND '".$end_date."' AND 
                                  `a`.`TipeDokumen` ='Faktur'
                      UNION ALL
                            SELECT 'FK' AS `FK`,
                                 (CASE `b`.`Tipe_Pajak` 
                                  WHEN 0 THEN '01' 
                                  WHEN 1 THEN '01' 
                                  WHEN 4 THEN '01' 
                                  WHEN 6 THEN '01' 
                                  WHEN 2 THEN '02' 
                                  WHEN 3 THEN '03' 
                                  WHEN 7 THEN '07' 
                                  WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END)
                                  WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' 
                                  WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) ELSE '01' END) AS `KodeJenisTransaksi`,
                                 0 AS `FGPengganti`,
                                 '' AS `NoDO`,
                                 MONTH(`a`.`TglDO`) AS `MasaPajak`,
                                 YEAR(`a`.`TglDO`) AS `TahunPajak`,
                                 DATE_FORMAT(`a`.`TglDO`,'%d/%m/%Y') AS `TanggalFaktur`,
                                 CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                 `b`.`Nama_Pajak` AS `Nama`,
                                 `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                 IFNULL(a.Value,0) AS 'JumlahDPP',
                                 IFNULL(a.ppn,0) AS 'JumlahPpn',
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,
                                 `a`.`TglDO` AS `KeteranganTanggal`,
                                  '' AS `KetTipePajak` 
                            FROM `trs_delivery_order_sales` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                            WHERE `a`.`Status` IN ('Open','Kirim','Terima') AND `a`.TipeDokumen = 'DO' AND 
                                  `a`.`TglDO` BETWEEN  '".$start_date."' AND '".$end_date."'
                            UNION ALL
                            SELECT 'OF' AS `FK`,
                                 `a`.`KodeProduk` AS `KodeJenisTransaksi`,
                                 `a`.`NamaProduk` AS `FGPengganti`,
                                 `a`.`Harga` AS `NoDO`,
                                 (IFNULL(`a`.`QtyDO`,0) + IFNULL(`a`.`BonusDO`,0)) AS `MasaPajak`,
                                 ROUND(IFNULL(`a`.`Gross`,0),0) AS `TahunPajak`,
                                 ROUND(IFNULL(`a`.`Potongan`,0),0) AS `TanggalFaktur`,
                                 ROUND(`a`.`Value`,0) AS `NPWP`,
                                 ROUND(`a`.`Ppn`,0) AS `Nama`,
                                 0 AS `AlamatLengkap`,
                                 0 AS `JumlahDPP`,
                                 0 AS `JumlahPpn`,
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,
                                 `a`.`TglDO` AS `KeteranganTanggal`,
                                 '' AS `KetTipePajak` 
                           FROM `trs_delivery_order_sales_detail` `a` LEFT JOIN `sst`.`mst_pelanggan` `b`
                           ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                           WHERE `a`.`Status` IN ('Open','Kirim','Terima') AND `a`.TipeDokumen = 'DO' AND 
                                 `a`.`TglDO`BETWEEN '".$start_date."' AND '".$end_date."') `pajakDO`
                           ORDER BY `pajakDO`.`Referensi`,`pajakDO`.`FK`,`pajakDO`.`MasaPajak`,`pajakDO`.`KodeJenisTransaksi`;")->result();
        return $query;
    }

    public function getEC($start_date=null,$end_date=null,$cabang=null){
        $query = $this->db->query("
                                  SELECT calendar.list_date,
                                       calendar.`Kode`,
                                       calendar.`Nama`,
                                       CONCAT(calendar.`Kode`,'~',calendar.`Nama`) AS 'salesman',
                                       EC_DO.TglDO,
                                       EC_DO.JumDok,
                                       EC_DO.JumDO,
                                       EC_DO.JumRet,
                                       EC_DO.OTDO,
                                       EC_DO.ECDO,
                                       EC_DO.ValDok,
                                       EC_DO.ValDO,
                                       EC_DO.ValRet,
                                       EC_FAKTUR.TglFaktur AS 'TglFaktur',
                                       SUM(EC_FAKTUR.JumDok) AS 'jumDOkFaktur',
                                       SUM(EC_FAKTUR.JumFak) AS 'jumFaktur',
                                       SUM(EC_FAKTUR.JumRet) AS 'JumRetFaktur',
                                       SUM(EC_FAKTUR.OTFak) AS 'OTFakFaktur',
                                       SUM(EC_FAKTUR.ECFak) AS 'ECFaktur',
                                       SUM(EC_FAKTUR.ValDok) AS 'ValDokFaktur',
                                       SUM(EC_FAKTUR.ValFak) AS 'ValFakFaktur',
                                       SUM(EC_FAKTUR.ValRet) AS 'ValRetFaktur' 
                                 FROM (SELECT mst_calendar.`list_date`,
                                         mst_karyawan.`Kode`,
                                         mst_karyawan.`Nama`,
                                         mst_karyawan.`Cabang`
                                   FROM  mst_calendar left join 
                                         mst_karyawan ON 
                                         mst_calendar.`Cabang` = mst_calendar.`Cabang` 
                                   WHERE mst_karyawan.`Jabatan` = 'Salesman' AND 
                                    mst_karyawan.`Status` ='Aktif') AS calendar
                                 LEFT JOIN 
                                (SELECT `a`.`Cabang`     AS `Cabang`,
                                        `a`.`TglDO`      AS `TglDO`,
                                        `a`.`Salesman`   AS `Salesman`,
                                        COUNT(`a`.`NoFaktur`) AS `JumDok`,
                                        (CASE `a`.`TipeDokumen` WHEN 'DO' THEN COUNT(`a`.`NoFaktur`) ELSE 0 END) AS `JumDO`,
                                        0 AS `JumRet`,
                                        (CASE `a`.`TipeDokumen` WHEN 'DO' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END) AS `OTDO`,
                                        ((CASE `a`.`TipeDokumen` WHEN 'DO' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END) - (CASE `a`.`TipeDokumen` WHEN 'Retur' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END)) AS `ECDO`,
                                        SUM(`a`.`Value`) AS `ValDok`,
                                        (CASE `a`.`TipeDokumen` WHEN 'DO' THEN SUM(`a`.`Value`) ELSE 0 END) AS `ValDO`,
                                        0 AS `ValRet`
                                   FROM `sst`.`trs_delivery_order_sales` `a`
                                   WHERE `a`.`Status` IN('Open','Kirim','Terima','Closed')
                                          AND `a`.`TipeDokumen` IN ('DO') and ifnull(status_retur,'') ='N'
                                   GROUP BY `a`.`Cabang`,`a`.`TglDO`,`a`.`Salesman`,`a`.`TipeDokumen`
                                   ORDER BY `a`.`Cabang`,`a`.`TglDO`,`a`.`Salesman`) AS EC_DO ON EC_DO.Salesman = calendar.`Kode` AND 
                                    EC_DO.Cabang = calendar.`Cabang` AND EC_DO.`TglDO` = calendar.list_date
                                    
                                 LEFT JOIN
                                (
                                  SELECT
                                        `a`.`Cabang`      AS `Cabang`,
                                        `a`.`TglFaktur`   AS `TglFaktur`,
                                        `a`.`Salesman`    AS `Salesman`,
                                        COUNT(`a`.`NoFaktur`) AS `JumDok`,
                                        (CASE `a`.`TipeDokumen` WHEN 'Faktur' THEN COUNT(`a`.`NoFaktur`) ELSE 0 END) AS `JumFak`,
                                        (CASE `a`.`TipeDokumen` WHEN 'Retur' THEN COUNT(`a`.`NoFaktur`) ELSE 0 END) AS `JumRet`,
                                        (CASE `a`.`TipeDokumen` WHEN 'Faktur' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END) AS `OTFak`,
                                        ((CASE `a`.`TipeDokumen` WHEN 'Faktur' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END) - (CASE `a`.`TipeDokumen` WHEN 'Retur' THEN COUNT(DISTINCT `a`.`Pelanggan`) ELSE 0 END)) AS `ECFak`,
                                        SUM(`a`.`Value`)  AS `ValDok`,
                                        (CASE `a`.`TipeDokumen` WHEN 'Faktur' THEN SUM(`a`.`Value`) ELSE 0 END) AS `ValFak`,
                                        (CASE `a`.`TipeDokumen` WHEN 'Retur' THEN SUM(`a`.`Value`) ELSE 0 END) AS `ValRet`
                                      FROM `sst`.`trs_faktur` `a`
                                      WHERE `a`.`Status` != 'Batal'
                                             AND `a`.`TipeDokumen` IN ('Faktur','Retur')
                                      GROUP BY `a`.`Cabang`,`a`.`TglFaktur`,`a`.`Salesman`,`a`.`TipeDokumen`
                                      ORDER BY `a`.`Cabang`,`a`.`TglFaktur`,`a`.`Salesman`
                                  ) AS EC_FAKTUR ON EC_FAKTUR.Salesman = calendar.`Kode` AND 
                                    EC_FAKTUR.Cabang = calendar.`Cabang`  AND EC_FAKTUR.`TglFaktur` = calendar.list_date  
                                 WHERE calendar.list_date BETWEEN '".$start_date."' and '".$end_date."'
                                 GROUP BY calendar.list_date,
                                       calendar.`Kode`,
                                       calendar.`Nama`,
                                       EC_DO.TglDO,
                                       EC_DO.JumDok,
                                       EC_DO.JumDO,
                                       EC_DO.JumRet,
                                       EC_DO.OTDO,
                                       EC_DO.ECDO,
                                       EC_DO.ValDok,
                                       EC_DO.ValDO,
                                       EC_DO.ValRet,
                                       EC_FAKTUR.TglFaktur
                                 ORDER BY calendar.list_date,salesman;")->result();
        return $query;
    }

    public function listfakturAll($cabang=null,$pelanggan=null){
         $query = $this->db->query("select * from trs_delivery_order_sales 
                                    where Cabang = '".$cabang."' and Pelanggan ='".$pelanggan."'")->result();
         return $query;
    }

    public function getkartupiutang($cabang=null,$pelanggan=null,$NoDO=null){
        $query = $this->db->query("SELECT  trs_sales_order.Cabang,
                                           trs_sales_order.`NoSO` AS 'NoDOkumen',
                                           trs_sales_order.`TglSO` AS 'TglDOkumen',
                                           trs_sales_order.`TipeDokumen` AS 'TipeDokumen',
                                           CONCAT(trs_sales_order.`Pelanggan`,' - ',trs_sales_order.`NamaPelanggan`) AS 'Pelanggan',
                                           CONCAT(trs_sales_order.`Salesman`,' - ',trs_sales_order.`NamaSalesman`) AS 'Salesman',
                                           trs_sales_order.`Total` AS 'Total',
                                           trs_sales_order.`Status` AS 'Status'
                                    FROM   trs_sales_order
                                    WHERE  trs_sales_order.`NoDO` = '".$NoDO."' and
                                           trs_sales_order.Cabang = '".$cabang."'

                                    UNION ALL  

                                    SELECT trs_delivery_order_sales.Cabang,
                                           trs_delivery_order_sales.`NoDO` AS 'NoDOkumen',
                                           trs_delivery_order_sales.`TglDO` AS 'TglDOkumen',
                                           trs_delivery_order_sales.`TipeDokumen` AS 'TipeDokumen',
                                           CONCAT(trs_delivery_order_sales.`Pelanggan`,' - ',trs_delivery_order_sales.`NamaPelanggan`) AS 'Pelanggan',
                                           CONCAT(trs_delivery_order_sales.`Salesman`,' - ',trs_delivery_order_sales.`NamaSalesman`) AS 'Salesman',
                                           trs_delivery_order_sales.`Total` AS 'Total',
                                           trs_delivery_order_sales.`Status` AS 'Status'
                                    FROM   trs_delivery_order_sales
                                    WHERE  trs_delivery_order_sales.`NoDO` = '".$NoDO."' and
                                           trs_delivery_order_sales.Cabang = '".$cabang."'

                                    UNION ALL

                                    SELECT trs_kiriman.Cabang,
                                           trs_kiriman.`NoKiriman` AS 'NoDOkumen',
                                           (
                                              CASE 
                                                WHEN trs_kiriman.`StatusKiriman` = 'Open' THEN trs_kiriman.`TglKirim` 
                                                    ELSE trs_kiriman.`TglTerima` 
                                              END 
                                            ) AS 'TglDOkumen',
                                           'Kiriman' AS 'TipeDokumen',
                                           CONCAT(trs_kiriman.`KodePelanggan`,' - ',(SELECT mst_pelanggan.`Pelanggan` FROM mst_pelanggan WHERE mst_pelanggan.`Kode` = trs_kiriman.`KodePelanggan`)) AS 'Pelanggan',
                                           CONCAT(trs_kiriman.`Pengirim`,' - ',trs_kiriman.`NamaPengirim`) AS 'Salesman',
                                           0 AS 'Total',
                                           trs_kiriman.`StatusKiriman` AS 'Status'
                                    FROM   trs_kiriman
                                    WHERE  trs_kiriman.`NoDO` = '".$NoDO."' and
                                           trs_kiriman.Cabang = '".$cabang."'

                                    UNION ALL

                                    SELECT trs_faktur.Cabang,
                                           trs_faktur.`NoFaktur` AS 'NoDOkumen',
                                           trs_faktur.`TglFaktur` AS 'TglDOkumen',
                                           trs_faktur.`TipeDokumen` AS 'TipeDokumen',
                                           CONCAT(trs_faktur.`Pelanggan`,' - ',trs_faktur.`NamaPelanggan`) AS 'Pelanggan',
                                           CONCAT(trs_faktur.`Salesman`,' - ',trs_faktur.`NamaSalesman`) AS 'Salesman',
                                           trs_faktur.`Total` AS 'Total',
                                           trs_faktur.`Status` AS 'Status'
                                    FROM   trs_faktur
                                    WHERE  trs_faktur.`NoDO` = '".$NoDO."' and
                                           trs_faktur.Cabang = '".$cabang."' and
                                           trs_faktur.Status != 'Usulan'

                                    UNION  ALL

                                    SELECT trs_dih.Cabang,
                                           trs_dih.`NoDIH` AS 'NoDOkumen',
                                           trs_dih.`TglDIH` AS 'TglDOkumen',
                                           'DIH' AS 'TipeDokumen',
                                           CONCAT(trs_dih.`Pelanggan`,' - ',(SELECT mst_pelanggan.`Pelanggan` FROM mst_pelanggan WHERE mst_pelanggan.`Kode` = trs_dih.`Pelanggan`)) AS 'Pelanggan',
                                           CONCAT(trs_dih.`Penagih`,' - ',(SELECT CONCAT(mst_karyawan.`Nama`,'/',mst_karyawan.`Jabatan`) FROM mst_karyawan WHERE mst_karyawan.`Kode` = trs_dih.`Penagih`)) AS 'Salesman',
                                           trs_dih.`Saldo` AS 'Total',
                                           trs_dih.`Status` AS 'Status' 
                                    FROM trs_dih
                                    WHERE trs_dih.NoFaktur=(SELECT trs_faktur.`NoFaktur` FROM trs_faktur WHERE trs_faktur.`NoDO` = '".$NoDO."') 
                                    AND trs_dih.Cabang='".$cabang."'

                                    UNION ALL

                                    SELECT trs_pelunasan_detail.Cabang,
                                           trs_pelunasan_detail.`NoDIH` AS 'NoDOkumen',
                                           trs_pelunasan_detail.`TglPelunasan` AS 'TglDOkumen',
                                           trs_pelunasan_detail.`TipePelunasan` AS 'TipeDokumen',
                                           CONCAT(trs_pelunasan_detail.`KodePelanggan`,' - ',trs_pelunasan_detail.`NamaPelanggan`) AS 'Pelanggan',
                                           CONCAT(trs_pelunasan_detail.`KodePenagih`,' - ',trs_pelunasan_detail.`NamaPenagih`) AS 'Salesman',
                                           trs_pelunasan_detail.`ValuePelunasan` AS 'Total',
                                           trs_pelunasan_detail.`Status` AS 'Status'
                                    FROM   trs_pelunasan_detail
                                    WHERE  trs_pelunasan_detail.NomorFaktur = (SELECT trs_faktur.`NoFaktur` FROM trs_faktur WHERE trs_faktur.`NoDO` = '".$NoDO."') and
                                           trs_pelunasan_detail.status != 'Batal' and
                                           trs_pelunasan_detail.Cabang = '".$cabang."'

                                    UNION  ALL
                                    
                                    SELECT trs_pelunasan_giro_detail.Cabang,
                                           trs_pelunasan_giro_detail.`NoDIH` AS 'NoDOkumen',
                                           trs_pelunasan_giro_detail.`TglPelunasan` AS 'TglDOkumen',
                                           trs_pelunasan_giro_detail.`TipePelunasan` AS 'TipeDokumen',
                                           CONCAT(trs_pelunasan_giro_detail.`KodePelanggan`,' - ',trs_pelunasan_giro_detail.`NamaPelanggan`) AS 'Pelanggan',
                                           CONCAT(trs_pelunasan_giro_detail.`KodePenagih`,' - ',trs_pelunasan_giro_detail.`NamaPenagih`) AS 'Salesman',
                                           trs_pelunasan_giro_detail.`ValuePelunasan` AS 'Total',
                                           trs_pelunasan_giro_detail.`Status` AS 'Status'
                                    FROM   trs_pelunasan_giro_detail
                                    WHERE  trs_pelunasan_giro_detail.`Status` = 'Open' AND 
                                           trs_pelunasan_giro_detail.NomorFaktur = (SELECT trs_faktur.`NoFaktur` FROM trs_faktur WHERE trs_faktur.`NoDO` = '".$NoDO."') and
                                           trs_pelunasan_giro_detail.Cabang = '".$cabang."'")->result();
        return $query;
    }
    public function getmutasipiutang($cabang=null,$tgl=null){
        $query = $this->db->query("
                                SELECT mst_cabang.`Cabang`,
                                     trs_delivery_order_sales.`NoDO` AS 'NoDO',
                                     trs_delivery_order_sales.`TglDO` AS 'TglDO',
                                     CONCAT(trs_delivery_order_sales.`Pelanggan`,' - ',trs_delivery_order_sales.`NamaPelanggan`) AS 'Pelanggan',
                                     CONCAT(trs_delivery_order_sales.`Salesman`,' - ',trs_delivery_order_sales.`NamaSalesman`) AS 'Salesman',
                                     trs_delivery_order_sales.`Total` AS 'TotalDO',
                                     trs_delivery_order_sales.`Status` AS 'StatusDO',
                                     dataKiriman.NoKiriman,
                                     dataKiriman.TglKiriman,
                                     dataKiriman.StatusKiriman,
                                     datapiutang.NoFaktur,
                                     datapiutang.TglFaktur,
                                     datapiutang.TipeFaktur,
                                     datapiutang.TotalFaktur,
                                     datapiutang.SaldoFaktur,
                                     datapiutang.StatusFaktur,
                                     datapiutang.NoDIH,
                                     datapiutang.FakturDIH,
                                     datapiutang.TglDIH,
                                     datapiutang.TotalDIH,
                                     datapiutang.StatusDIH,
                                     datapiutang.FakturPelunasan,
                                     datapiutang.TglPelunasan,
                                     datapiutang.TipePelunasan,
                                     datapiutang.ValuePelunasan,
                                     datapiutang.StatusPelunasan,
                                     datapiutang.TglPelunasanGiro,
                                     datapiutang.TipePelunasanGiro,
                                     datapiutang.ValuePelunasanGiro,
                                     datapiutang.StatusGiro,
                                     datapiutang.NoGiro
                              FROM   trs_delivery_order_sales left join 
                                mst_cabang ON mst_cabang.`Cabang` = trs_delivery_order_sales.`Cabang`
                              LEFT JOIN
                              (SELECT trs_kiriman.Cabang,
                                      trs_kiriman.`NoKiriman` AS 'NoKiriman',
                                      trs_kiriman.`NoDO`,
                                      (CASE WHEN trs_kiriman.`StatusKiriman` = 'Open' THEN trs_kiriman.`TglKirim` ELSE trs_kiriman.`TglTerima` END ) AS 'TglKiriman',
                                      trs_kiriman.`StatusKiriman` AS 'StatusKiriman'
                               FROM   trs_kiriman) AS dataKiriman ON dataKiriman.Cabang = trs_delivery_order_sales.Cabang AND 
                                 dataKiriman.NoDO = trs_delivery_order_sales.NoDO    
                              LEFT JOIN
                              (SELECT trs_faktur.Cabang,
                                      trs_faktur.`NoFaktur` AS 'NoFaktur',
                                      trs_faktur.`NoDO` AS 'NoDO',
                                      trs_faktur.`TglFaktur` AS 'TglFaktur',
                                      trs_faktur.`TipeDokumen` AS 'TipeFaktur',
                                      trs_faktur.`Total` AS 'TotalFaktur',
                                      trs_faktur.`Saldo` AS 'SaldoFaktur',
                                      trs_faktur.`Status` AS 'StatusFaktur',
                                      dataDIH.NoDIH,
                                     dataDIH.NoFaktur AS 'FakturDIH',
                                     dataDIH.TglDIH,
                                     dataDIH.TotalDIH,
                                     dataDIH.StatusDIH,
                                     dataPelunasan.FakturPelunasan,
                                     dataPelunasan.TglPelunasan,
                                     dataPelunasan.TipePelunasan,
                                     dataPelunasan.ValuePelunasan,
                                     dataPelunasan.StatusPelunasan,
                                     dataGiro.FakturGiro,
                                     dataGiro.TglPelunasanGiro,
                                     dataGiro.TipePelunasanGiro,
                                     dataGiro.ValuePelunasanGiro,
                                     dataGiro.StatusGiro,
                                     dataGiro.NoGiro
                               FROM   trs_faktur LEFT JOIN
                               (SELECT trs_dih.`Cabang`,
                                trs_dih.`NoDIH`,
                                trs_dih.`NoFaktur`,
                                trs_dih.`TglDIH`,
                                trs_dih.`Status` AS 'StatusDIH' ,
                                trs_dih.`Total` AS 'TotalDIH'
                               FROM   trs_dih ) AS dataDIH ON dataDIH.Cabang = trs_faktur.Cabang AND 
                                 dataDIH.NoFaktur = trs_faktur.NoFaktur  
                              LEFT JOIN
                              (SELECT trs_pelunasan_detail.Cabang,
                                      trs_pelunasan_detail.`NomorFaktur` AS 'FakturPelunasan',
                                      trs_pelunasan_detail.`TglPelunasan` AS 'TglPelunasan',
                                      trs_pelunasan_detail.`TipePelunasan` AS 'TipePelunasan',
                                      trs_pelunasan_detail.`ValuePelunasan` AS 'ValuePelunasan',
                                      trs_pelunasan_detail.`Status` AS 'StatusPelunasan'
                               FROM   trs_pelunasan_detail
                               where trs_pelunasan_detail.status != 'Batal') AS dataPelunasan ON dataPelunasan.Cabang = trs_faktur.Cabang AND 
                                 dataPelunasan.FakturPelunasan = trs_faktur.NoFaktur
                              LEFT JOIN
                              (SELECT trs_pelunasan_giro_detail.Cabang,
                                      trs_pelunasan_giro_detail.`NomorFaktur` AS 'FakturGiro',
                                      trs_pelunasan_giro_detail.`TglPelunasan` AS 'TglPelunasanGiro',
                                      trs_pelunasan_giro_detail.`TipePelunasan` AS 'TipePelunasanGiro',
                                      trs_pelunasan_giro_detail.`ValuePelunasan` AS 'ValuePelunasanGiro',
                                      trs_pelunasan_giro_detail.`Status` AS 'StatusGiro',
                                      trs_pelunasan_giro_detail.giro AS 'NoGiro'
                               FROM   trs_pelunasan_giro_detail
                               WHERE  trs_pelunasan_giro_detail.`Status` != 'Closed') AS dataGiro ON dataGiro.Cabang = trs_faktur.Cabang AND 
                                 dataGiro.FakturGiro = trs_faktur.NoFaktur 
                                where trs_faktur.Status != 'Usulan') AS datapiutang ON datapiutang.Cabang = trs_delivery_order_sales.Cabang AND 
                                 datapiutang.NoDO = trs_delivery_order_sales.NoDO  
                              WHERE  trs_delivery_order_sales.`TglDO` = '".$tgl."' AND
                                     trs_delivery_order_sales.Cabang = '".$cabang."'
                               ORDER BY trs_delivery_order_sales.`TglDO`,trs_delivery_order_sales.`NoDO`
          ")->result();
        return $query;
    }

     public function getmutasiprpoperiode($search = null, $limit = null,$tgl1=null,$tgl2=null)
    {   
        
        $query = $this->db->query("SELECT trs_usulan_beli_header.`Cabang`,
                                           trs_usulan_beli_header.`added_time`,
                                           trs_usulan_beli_header.`No_Usulan`,
                                           trs_usulan_beli_header.`Prinsipal`,
                                           trs_usulan_beli_header.`Status_Usulan`,
                                           prpo.No_PR,
                                           prpo.Time_PR,
                                           prpo.Status_PR,
                                           prpo.No_PO,
                                           prpo.Time_PO,
                                           prpo.Status_PO,
                                           prpo.NoBPB,
                                           prpo.TglBPB,
                                           prpo.StatusBPB,
                                           prpo.NoDOBeli,
                                           prpo.TglDOBeli,
                                           prpo.StatusDOBeli
                                    FROM  trs_usulan_beli_header LEFT JOIN
                                    (
                                    SELECT trs_pembelian_header.`No_Usulan`,
                                           trs_pembelian_header.`Time_PR`,
                                           trs_pembelian_header.`No_PR`,
                                           trs_pembelian_header.`Status_PR`,
                                           po.No_PO,
                                           po.Time_PO,
                                           po.Status_PO,
                                           bpb.NoDokumen AS 'NoBPB',
                                           bpb.TimeDokumen AS 'TglBPB',
                                           bpb.Status AS 'StatusBPB',
                                           dobeli.NoDokumen AS 'NoDOBeli',
                                           dobeli.TimeDokumen AS 'TglDOBeli',
                                           dobeli.Status AS 'StatusDOBeli'
                                    FROM trs_pembelian_header LEFT JOIN 
                                    ( SELECT trs_po_header.`No_PO`,
                                       trs_po_header.`Time_PO`,
                                             trs_po_header.`No_PR`,
                                             trs_po_header.`No_Usulan`,
                                             trs_po_header.`Status_PO`
                                       FROM trs_po_header ) AS po ON po.No_PR = trs_pembelian_header.`No_PR`
                                       LEFT JOIN
                                     (SELECT trs_terima_barang_header.`NoDokumen`,
                                             trs_terima_barang_header.`TimeDokumen`,
                                             trs_terima_barang_header.`NoPR`,
                                             trs_terima_barang_header.`Status`
                                       FROM trs_terima_barang_header ) AS bpb ON bpb.NoPR = trs_pembelian_header.`No_PR`
                                     LEFT JOIN
                                     ( SELECT trs_delivery_order_header.`NoDokumen`,
                                        trs_delivery_order_header.`TimeDokumen`,
                                              trs_delivery_order_header.`NoPR`,
                                              trs_delivery_order_header.`Status`
                                        FROM  trs_delivery_order_header ) AS dobeli ON dobeli.NoPR = trs_pembelian_header.`No_PR`) AS prpo
                                    ON prpo.No_Usulan = trs_usulan_beli_header.`No_Usulan` 
                                  WHERE DATE(trs_usulan_beli_header.`added_time`) BETWEEN '".$tgl1."' AND '".$tgl2."' $search $limit;
                                        ");
      
        return $query;
    }

    public function listDataDetailtransaksi($no = null, $jenis = null)
    {   
      $query ="";
       if($jenis == 'Usulan'){
          $query = $this->db->query("select trs_usulan_beli_header.*,trs_usulan_beli_detail.*,trs_usulan_beli_header.Value_Usulan as 'val_header'
                                    from trs_usulan_beli_header left join trs_usulan_beli_detail on 
                                    trs_usulan_beli_header.No_Usulan = trs_usulan_beli_detail.No_Usulan
                                    where trs_usulan_beli_header.No_Usulan = '".$no."'
                                    ")->result();
       }elseif($jenis == 'pr'){
          $query = $this->db->query("select trs_pembelian_header.*,trs_pembelian_detail.*,trs_pembelian_header.Total_PR as 'total_header'
                                    from trs_pembelian_header left join trs_pembelian_detail on 
                                    trs_pembelian_header.No_PR = trs_pembelian_detail.No_PR
                                    where trs_pembelian_header.No_PR = '".$no."'
                                    ")->result();
       }elseif($jenis == 'po'){
          $query = $this->db->query("select trs_po_header.*,trs_po_detail.*,trs_po_header.Status_PO as 'Status_PO_header'
                                    from trs_po_header left join trs_po_detail on 
                                    trs_po_header.No_PO = trs_po_detail.No_PO
                                    where trs_po_header.No_PO = '".$no."'
                                    ")->result();
       }elseif($jenis == 'bpb'){
          $query = $this->db->query("select trs_terima_barang_header.*,trs_terima_barang_detail.*,trs_terima_barang_header.Status as 'Status_header',trs_terima_barang_header.Value as 'value_header'
                                    from trs_terima_barang_header left join trs_terima_barang_detail on 
                                    trs_terima_barang_header.NoDokumen = trs_terima_barang_detail.NoDokumen
                                    where trs_terima_barang_header.NoDokumen = '".$no."'
                                    ")->result();
       }elseif($jenis == 'dobeli'){
          $query = $this->db->query("select trs_delivery_order_header.*,trs_delivery_order_detail.*,trs_delivery_order_header.Status as 'Status_header',trs_delivery_order_header.Value as 'value_header'
                                    from trs_delivery_order_header left join trs_delivery_order_detail on 
                                    trs_delivery_order_header.NoDokumen = trs_delivery_order_detail.NoDokumen
                                    where trs_delivery_order_header.NoDokumen = '".$no."'
                                    ")->result();
       } 
        return $query;
    }
    public function getomsetsalesharian($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT mst_karyawan.`Kode`,
                                         mst_karyawan.`Nama`,
                                         IFNULL(faktur.omset,0) AS 'omsetfaktur',
                                         IFNULL(komersil.omset,0) AS 'omsetkomersil',
                                         (IFNULL(faktur.omset,0) + IFNULL(komersil.omset,0)) AS 'totalomset'
                                  FROM mst_karyawan LEFT JOIN
                                  (SELECT trs_delivery_order_sales.`Cabang`,
                                    trs_delivery_order_sales.`Salesman`,
                                         SUM(trs_delivery_order_sales.`Value`) AS omset
                                   FROM trs_delivery_order_sales
                                   WHERE trs_delivery_order_sales.`TipeDokumen` ='DO' AND
                                         trs_delivery_order_sales.status IN ('Open','Kirim') AND 
                                         trs_delivery_order_sales.`TglDO` BETWEEN '".$start_date."' AND '".$end_date."' 
                                  GROUP BY trs_delivery_order_sales.`Cabang`,
                                    trs_delivery_order_sales.`Salesman`) AS faktur ON faktur.Salesman = mst_karyawan.`Kode`
                                  LEFT JOIN
                                  (SELECT trs_faktur.`Cabang`,
                                    trs_faktur.`Salesman`,
                                         SUM(trs_faktur.`Value`) AS omset
                                   FROM trs_faktur
                                   WHERE trs_faktur.TipeDokumen IN ('Faktur','Retur','CN','DN') AND
                                         trs_faktur.status NOT IN ('Batal','Usulan') AND 
                                         trs_faktur.`TglFaktur` BETWEEN '".$start_date."' AND '".$end_date."'  
                                  GROUP BY trs_faktur.`Cabang`,
                                    trs_faktur.`Salesman`) AS komersil ON komersil.Salesman = mst_karyawan.`Kode` 
                                  WHERE mst_karyawan.`Status` = 'Aktif' and mst_karyawan.Jabatan ='Salesman' 
                                  HAVING omsetfaktur > 0 OR omsetkomersil > 0;")->result();

        return $query;
    }

    public function getomsetsalesharianprinsipal($start_date=null,$end_date=null,$cabang=null)
    {   
        $query = $this->db->query("SELECT Cabang.Cabang,
                           Cabang.Kode,
                           Cabang.Nama,
                           Cabang.Prinsipal,
                           IFNULL(faktur.omset,0) AS 'omsetfaktur',
                           IFNULL(komersil.omset,0) AS 'omsetkomersil',
                           (IFNULL(faktur.omset,0) + IFNULL(komersil.omset,0)) AS 'totalomset'
                    FROM
                    (SELECT mst_cabang.Cabang,
                           karyawan.Kode,
                           karyawan.Nama,
                           prinsipal.Prinsipal
                    FROM mst_cabang left join 
                    (SELECT mst_karyawan.Cabang,
                           mst_karyawan.Kode,
                           mst_karyawan.Nama 
                      FROM mst_karyawan where mst_karyawan.Jabatan ='Salesman' and mst_karyawan.Status ='Aktif') AS karyawan ON karyawan.Cabang = mst_cabang.Cabang
                    left join 
                    (SELECT mst_prinsipal.Prinsipal,
                           '".$cabang."' AS 'cabang'
                    FROM mst_prinsipal) AS prinsipal ON prinsipal.Cabang = mst_cabang.Cabang
                    WHERE mst_cabang.Cabang ='".$cabang."') AS Cabang 
                    LEFT JOIN 
                    (SELECT trs_delivery_order_sales_detail.Cabang,
                            trs_delivery_order_sales_detail.Salesman,
                            trs_delivery_order_sales_detail.Prinsipal,
                            SUM(trs_delivery_order_sales_detail.Value) AS omset
                    FROM trs_delivery_order_sales_detail
                    WHERE trs_delivery_order_sales_detail.TipeDokumen ='DO' AND
                          IFNULL(trs_delivery_order_sales_detail.status,'') IN ('Open','Kirim') AND 
                           trs_delivery_order_sales_detail.TglDO BETWEEN '".$start_date."' AND '".$end_date."'  
                     GROUP BY trs_delivery_order_sales_detail.Cabang,
                      trs_delivery_order_sales_detail.Salesman,
                            trs_delivery_order_sales_detail.Prinsipal ) AS faktur 
                            ON faktur.Cabang = Cabang.Cabang AND 
                               faktur.Salesman = Cabang.Kode AND 
                               faktur.Prinsipal = Cabang.Prinsipal 
                    LEFT JOIN
                    (SELECT trs_faktur_detail.Cabang,
                            trs_faktur_detail.Salesman,
                            trs_faktur_detail.Prinsipal,
                            SUM(trs_faktur_detail.Value) AS omset
                       FROM trs_faktur_detail
                      WHERE trs_faktur_detail.TipeDokumen IN ('Faktur','Retur','CN','DN') AND
                            IFNULL(trs_faktur_detail.status,'') NOT IN ('Batal','Usulan') AND 
                            trs_faktur_detail.TglFaktur BETWEEN '".$start_date."' AND '".$end_date."'
                     GROUP BY trs_faktur_detail.Cabang,
                             trs_faktur_detail.Salesman,
                             trs_faktur_detail.Prinsipal) AS komersil 
                     ON komersil.Cabang = Cabang.Cabang AND 
                               komersil.Salesman = Cabang.Kode AND 
                               komersil.Prinsipal = Cabang.Prinsipal ;")->result();

        return $query;
    }

    public function geteretur($start_date=null,$end_date=null,$cabang=null){
         $query = $this->db->query("SELECT `pajakDO`.`RK` AS `RK`,
                                     `pajakDO`.`KodeJenisTransaksi` AS `KodeJenisTransaksi`,
                                     `pajakDO`.`FGPengganti` AS `FGPengganti`,
                                     `pajakDO`.`NoFaktur` AS `NoFaktur`,
                                     `pajakDO`.`MasaPajak` AS `MasaPajak`,
                                     ROUND(`pajakDO`.`TahunPajak`,0) AS `TahunPajak`,
                                     `pajakDO`.`TanggalRetur` AS `TanggalRetur`,
                                     '' AS 'AcuFaktur',
                                     `pajakDO`.`TanggalFaktur` AS `TanggalFaktur`,
                                     `pajakDO`.`NPWP` AS `NPWP`,
                                     `pajakDO`.`Nama` AS `Nama`,
                                     `pajakDO`.`AlamatLengkap` AS `AlamatLengkap`,
                                     `pajakDO`.`JumlahDPP` AS `JumlahDPP`,
                                     `pajakDO`.`JumlahPpn` AS `JumlahPpn`,
                                     ROUND(`pajakDO`.`JumlahPpnBM`,0) AS `JumlahPpnBM`,
                                     `pajakDO`.`IDKeteranganTambahan` AS `IDKeteranganTambahan`,
                                     ROUND(`pajakDO`.`FGUangMuka`,0) AS `FGUangMuka`,
                                     ROUND(`pajakDO`.`UangMukaDPP`,0) AS `UangMukaDPP`,
                                     ROUND(`pajakDO`.`UangMukaPpn`,0) AS `UangMukaPpn`,
                                     ROUND(`pajakDO`.`UangMukaPpnBM`,0) AS `UangMukaPpnBM`,
                                     `pajakDO`.`Referensi` AS `Referensi`,
                                     `pajakDO`.`Keterangan` AS `Keterangan`,
                                     `pajakDO`.`KeteranganTanggal` AS `KeteranganTanggal`,
                                     `pajakDO`.`KetTipePajak` AS `KetTipePajak` 
                                FROM (SELECT 'RK' AS `RK`,
                                            (CASE `b`.`Tipe_Pajak` 
                                             WHEN 0 THEN '01' 
                                             WHEN 1 THEN '01' 
                                             WHEN 4 THEN '01' 
                                             WHEN 6 THEN '01' 
                                             WHEN 2 THEN '02' 
                                             WHEN 3 THEN '03' 
                                             WHEN 7 THEN '07' 
                                             WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END) 
                                             WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) 
                                             ELSE '01' END) AS `KodeJenisTransaksi`,
                                            0 AS `FGPengganti`,
                                            `a`.`NoFaktur` AS `NoFaktur`,
                                            MONTH(`a`.`TglFaktur`) AS `MasaPajak`,
                                            YEAR(`a`.`TglFaktur`) AS `TahunPajak`,
                                            DATE_FORMAT(`a`.`TglFaktur`,'%d/%m/%Y') AS `TanggalRetur`,
                                            DATE_FORMAT(`xx`.`TglFaktur`,'%d/%m/%Y') AS `TanggalFaktur`,
                                            CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                            `b`.`Nama_Pajak` AS `Nama`,
                                            `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                            IFNULL(a.`Value`,0) AS 'JumlahDPP',
                                            IFNULL(a.`ppn`,0) AS 'JumlahPpn',
                                            0 AS `JumlahPpnBM`,
                                            0 AS `IDKeteranganTambahan`,
                                            0 AS `FGUangMuka`,
                                            0 AS `UangMukaDPP`,
                                            0 AS `UangMukaPpn`,
                                            0 AS `UangMukaPpnBM`,
                                            CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                            '' AS `Keterangan`,`a`.`TglFaktur` AS `KeteranganTanggal`,
                                            '' AS `KetTipePajak` 
                                     FROM `trs_faktur` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                                     ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                                     LEFT JOIN ( SELECT c.`NoFaktur`,c.`TglFaktur`
                                     FROM trs_faktur c
                                     WHERE c.`TipeDokumen` ='Faktur' ) `xx`
                                     ON a.`Acu` = xx.NoFaktur 
                                     WHERE `a`.`TglFaktur` BETWEEN  '".$start_date."' AND '".$end_date."' AND 
                                           `a`.`TipeDokumen` ='Retur' AND 
                                           `a`.`Status` != 'Batal') `pajakDO`
                               ORDER BY `pajakDO`.`Referensi`,`pajakDO`.`RK`,`pajakDO`.`MasaPajak`,`pajakDO`.`KodeJenisTransaksi`;")->result();
        return $query;
    }

    function getlistfaktur($cabang=null,$tipe=null){
        $query = $this->db->query("select Nofaktur,TglFaktur,TipeDokumen from trs_faktur where TipeDokumen ='".$tipe."' and cabang='".$cabang."' order by TglFaktur Desc")->result();
        return $query;
    }

    public function getefakturbyNo($cabang=null,$NoFaktur=null){
         $query = $this->db->query("SELECT `pajakDO`.`FK` AS `FK`,
                           `pajakDO`.`KodeJenisTransaksi` AS `KodeJenisTransaksi`,
                           `pajakDO`.`FGPengganti` AS `FGPengganti`,
                           `pajakDO`.`NoFaktur` AS `NoFaktur`,
                           `pajakDO`.`MasaPajak` AS `MasaPajak`,
                           ROUND(`pajakDO`.`TahunPajak`,0) AS `TahunPajak`,
                           `pajakDO`.`TanggalFaktur` AS `TanggalFaktur`,
                           `pajakDO`.`NPWP` AS `NPWP`,
                           `pajakDO`.`Nama` AS `Nama`,
                           `pajakDO`.`AlamatLengkap` AS `AlamatLengkap`,
                           `pajakDO`.`JumlahDPP` AS `JumlahDPP`,
                           `pajakDO`.`JumlahPpn` AS `JumlahPpn`,
                           ROUND(`pajakDO`.`JumlahPpnBM`,0) AS `JumlahPpnBM`,
                           `pajakDO`.`IDKeteranganTambahan` AS `IDKeteranganTambahan`,
                           ROUND(`pajakDO`.`FGUangMuka`,0) AS `FGUangMuka`,
                           ROUND(`pajakDO`.`UangMukaDPP`,0) AS `UangMukaDPP`,
                           ROUND(`pajakDO`.`UangMukaPpn`,0) AS `UangMukaPpn`,
                           ROUND(`pajakDO`.`UangMukaPpnBM`,0) AS `UangMukaPpnBM`,
                           `pajakDO`.`Referensi` AS `Referensi`,
                           `pajakDO`.`Keterangan` AS `Keterangan`,
                           `pajakDO`.`KeteranganTanggal` AS `KeteranganTanggal`,
                           `pajakDO`.`KetTipePajak` AS `KetTipePajak` 
                      FROM (SELECT 'FK' AS `FK`,
                                 (CASE `b`.`Tipe_Pajak` 
                                  WHEN 0 THEN '01' 
                                  WHEN 1 THEN '01' 
                                  WHEN 4 THEN '01' 
                                  WHEN 6 THEN '01' 
                                  WHEN 2 THEN '02' 
                                  WHEN 3 THEN '03' 
                                  WHEN 7 THEN '07' 
                                  WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END) 
                                  WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) 
                                  ELSE '01' END) AS `KodeJenisTransaksi`,
                                 0 AS `FGPengganti`,
                                 `a`.`NoPajak` AS `NoFaktur`,
                                 MONTH(`a`.`TglFaktur`) AS `MasaPajak`,
                                 YEAR(`a`.`TglFaktur`) AS `TahunPajak`,
                                 DATE_FORMAT(`a`.`TglFaktur`,'%d/%m/%Y') AS `TanggalFaktur`,
                                 CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                 `b`.`Nama_Pajak` AS `Nama`,
                                 `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                 IFNULL(a.`Value`,0) AS 'JumlahDPP',
                                 IFNULL(a.`ppn`,0) AS 'JumlahPpn',
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,`a`.`TglFaktur` AS `KeteranganTanggal`,
                                 '' AS `KetTipePajak` 
                            FROM `trs_faktur` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                            WHERE `a`.`NoFaktur` = '".$NoFaktur."' AND 
                            `a`.`TipeDokumen` ='Faktur'
                            UNION ALL
                            SELECT 'OF' AS `FK`,
                                 `a`.`KodeProduk` AS `KodeJenisTransaksi`,
                                 `a`.`NamaProduk` AS `FGPengganti`,
                                  ROUND(`a`.`Harga`,0) AS `NoFaktur`,
                                  (IFNULL(`a`.`QtyFaktur`,0) + IFNULL(`a`.`BonusFaktur`,0)) AS `MasaPajak`,
                                  ROUND(`a`.`Gross`,0) AS `TahunPajak`,
                                  ROUND(`a`.`Potongan`,0) AS `TanggalFaktur`,
                                  ROUND(`a`.`Value`,0) AS `NPWP`,
                                  ROUND(`a`.`Ppn`,0) AS `Nama`,
                                  0 AS `AlamatLengkap`,
                                  0 AS `JumlahDPP`,
                                  0 AS `JumlahPpn`,
                                  0 AS `JumlahPpnBM`,
                                  0 AS `IDKeteranganTambahan`,
                                  0 AS `FGUangMuka`,
                                  0 AS `UangMukaDPP`,
                                  0 AS `UangMukaPpn`,
                                  0 AS `UangMukaPpnBM`,
                                  CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                  '' AS `Keterangan`,
                                  `a`.`TglFaktur` AS `KeteranganTanggal`,
                                  '' AS `KetTipePajak` 
                            FROM `trs_faktur_detail` `a` LEFT JOIN `sst`.`mst_pelanggan` `b`
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang` 
                            WHERE `a`.`NoFaktur` = '".$NoFaktur."' AND 
                                  `a`.`TipeDokumen` ='Faktur'
                      UNION ALL
                            SELECT 'FK' AS `FK`,
                                 (CASE `b`.`Tipe_Pajak` 
                                  WHEN 0 THEN '01' 
                                  WHEN 1 THEN '01' 
                                  WHEN 4 THEN '01' 
                                  WHEN 6 THEN '01' 
                                  WHEN 2 THEN '02' 
                                  WHEN 3 THEN '03' 
                                  WHEN 7 THEN '07' 
                                  WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END)
                                  WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' 
                                  WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) ELSE '01' END) AS `KodeJenisTransaksi`,
                                 0 AS `FGPengganti`,
                                 '' AS `NoDO`,
                                 MONTH(`a`.`TglDO`) AS `MasaPajak`,
                                 YEAR(`a`.`TglDO`) AS `TahunPajak`,
                                 DATE_FORMAT(`a`.`TglDO`,'%d/%m/%Y') AS `TanggalFaktur`,
                                 CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                 `b`.`Nama_Pajak` AS `Nama`,
                                 `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                 IFNULL(a.Value,0) AS 'JumlahDPP',
                                 IFNULL(a.ppn,0) AS 'JumlahPpn',
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,
                                 `a`.`TglDO` AS `KeteranganTanggal`,
                                  '' AS `KetTipePajak` 
                            FROM `trs_delivery_order_sales` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                            ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                            WHERE `a`.`Status` IN ('Open','Kirim','Terima') AND `a`.TipeDokumen = 'DO' AND 
                                  `a`.`NoDO` = '".$NoFaktur."'
                            UNION ALL
                            SELECT 'OF' AS `FK`,
                                 `a`.`KodeProduk` AS `KodeJenisTransaksi`,
                                 `a`.`NamaProduk` AS `FGPengganti`,
                                 `a`.`Harga` AS `NoDO`,
                                 (IFNULL(`a`.`QtyDO`,0) + IFNULL(`a`.`BonusDO`,0)) AS `MasaPajak`,
                                 ROUND(IFNULL(`a`.`Gross`,0),0) AS `TahunPajak`,
                                 ROUND(IFNULL(`a`.`Potongan`,0),0) AS `TanggalFaktur`,
                                 ROUND(`a`.`Value`,0) AS `NPWP`,
                                 ROUND(`a`.`Ppn`,0) AS `Nama`,
                                 0 AS `AlamatLengkap`,
                                 0 AS `JumlahDPP`,
                                 0 AS `JumlahPpn`,
                                 0 AS `JumlahPpnBM`,
                                 0 AS `IDKeteranganTambahan`,
                                 0 AS `FGUangMuka`,
                                 0 AS `UangMukaDPP`,
                                 0 AS `UangMukaPpn`,
                                 0 AS `UangMukaPpnBM`,
                                 CONCAT(`a`.`NoDO`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                 '' AS `Keterangan`,
                                 `a`.`TglDO` AS `KeteranganTanggal`,
                                 '' AS `KetTipePajak` 
                           FROM `trs_delivery_order_sales_detail` `a` LEFT JOIN `sst`.`mst_pelanggan` `b`
                           ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                           WHERE `a`.`Status` IN ('Open','Kirim','Terima') AND `a`.TipeDokumen = 'DO' AND 
                                 `a`.`NoDO` = '".$NoFaktur."') `pajakDO`
                           ORDER BY `pajakDO`.`Referensi`,`pajakDO`.`FK`,`pajakDO`.`MasaPajak`,`pajakDO`.`KodeJenisTransaksi`;")->result();
        return $query;
    }

    public function getereturbyNO($cabang=null,$NoFaktur=null){
         $query = $this->db->query("SELECT `pajakDO`.`RK` AS `RK`,
                                     `pajakDO`.`KodeJenisTransaksi` AS `KodeJenisTransaksi`,
                                     `pajakDO`.`FGPengganti` AS `FGPengganti`,
                                     `pajakDO`.`NoFaktur` AS `NoFaktur`,
                                     `pajakDO`.`MasaPajak` AS `MasaPajak`,
                                     ROUND(`pajakDO`.`TahunPajak`,0) AS `TahunPajak`,
                                     `pajakDO`.`TanggalRetur` AS `TanggalRetur`,
                                     '' AS 'AcuFaktur',
                                     `pajakDO`.`TanggalFaktur` AS `TanggalFaktur`,
                                     `pajakDO`.`NPWP` AS `NPWP`,
                                     `pajakDO`.`Nama` AS `Nama`,
                                     `pajakDO`.`AlamatLengkap` AS `AlamatLengkap`,
                                     `pajakDO`.`JumlahDPP` AS `JumlahDPP`,
                                     `pajakDO`.`JumlahPpn` AS `JumlahPpn`,
                                     ROUND(`pajakDO`.`JumlahPpnBM`,0) AS `JumlahPpnBM`,
                                     `pajakDO`.`IDKeteranganTambahan` AS `IDKeteranganTambahan`,
                                     ROUND(`pajakDO`.`FGUangMuka`,0) AS `FGUangMuka`,
                                     ROUND(`pajakDO`.`UangMukaDPP`,0) AS `UangMukaDPP`,
                                     ROUND(`pajakDO`.`UangMukaPpn`,0) AS `UangMukaPpn`,
                                     ROUND(`pajakDO`.`UangMukaPpnBM`,0) AS `UangMukaPpnBM`,
                                     `pajakDO`.`Referensi` AS `Referensi`,
                                     `pajakDO`.`Keterangan` AS `Keterangan`,
                                     `pajakDO`.`KeteranganTanggal` AS `KeteranganTanggal`,
                                     `pajakDO`.`KetTipePajak` AS `KetTipePajak` 
                                FROM (SELECT 'RK' AS `RK`,
                                            (CASE `b`.`Tipe_Pajak` 
                                             WHEN 0 THEN '01' 
                                             WHEN 1 THEN '01' 
                                             WHEN 4 THEN '01' 
                                             WHEN 6 THEN '01' 
                                             WHEN 2 THEN '02' 
                                             WHEN 3 THEN '03' 
                                             WHEN 7 THEN '07' 
                                             WHEN 53 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' ELSE '01' END) 
                                             WHEN 55 THEN (CASE WHEN (`a`.`Total` > 10000000) THEN '03' WHEN (`a`.`Total` > 1000000) THEN '02' ELSE '01' END) 
                                             ELSE '01' END) AS `KodeJenisTransaksi`,
                                            0 AS `FGPengganti`,
                                            `a`.`NoFaktur` AS `NoFaktur`,
                                            MONTH(`a`.`TglFaktur`) AS `MasaPajak`,
                                            YEAR(`a`.`TglFaktur`) AS `TahunPajak`,
                                            DATE_FORMAT(`a`.`TglFaktur`,'%d/%m/%Y') AS `TanggalRetur`,
                                            DATE_FORMAT(`xx`.`TglFaktur`,'%d/%m/%Y') AS `TanggalFaktur`,
                                            CAST(`b`.`NPWP` AS CHAR) AS `NPWP`,
                                            `b`.`Nama_Pajak` AS `Nama`,
                                            `b`.`Alamat_Pajak` AS `AlamatLengkap`,
                                            IFNULL(a.`Value`,0) AS 'JumlahDPP',
                                            IFNULL(a.`ppn`,0) AS 'JumlahPpn',
                                            0 AS `JumlahPpnBM`,
                                            0 AS `IDKeteranganTambahan`,
                                            0 AS `FGUangMuka`,
                                            0 AS `UangMukaDPP`,
                                            0 AS `UangMukaPpn`,
                                            0 AS `UangMukaPpnBM`,
                                            CONCAT(`a`.`NoFaktur`,'-',`a`.`Pelanggan`,'-',`a`.`Cabang`,'-',`a`.`TipeDokumen`) AS `Referensi`,
                                            '' AS `Keterangan`,`a`.`TglFaktur` AS `KeteranganTanggal`,
                                            '' AS `KetTipePajak` 
                                     FROM `trs_faktur` `a` LEFT JOIN `sst`.`mst_pelanggan` `b` 
                                     ON `a`.`Pelanggan` = `b`.`Kode` AND `a`.`Cabang` = `b`.`Cabang`
                                     LEFT JOIN ( SELECT c.`NoFaktur`,c.`TglFaktur`
                                     FROM trs_faktur c
                                     WHERE c.`TipeDokumen` ='Faktur' ) `xx`
                                     ON a.`Acu` = xx.NoFaktur 
                                     WHERE `a`.`NoFaktur` = '".$NoFaktur."' AND 
                                           `a`.`TipeDokumen` ='Retur' AND 
                                           `a`.`Status` != 'Batal') `pajakDO`
                               ORDER BY `pajakDO`.`Referensi`,`pajakDO`.`RK`,`pajakDO`.`MasaPajak`,`pajakDO`.`KodeJenisTransaksi`;")->result();
        return $query;
    }
    public function getbpbfaktur($search = null, $limit = null, $tipe=null,$tgl1=null,$tgl2=null)
    {   
        if ($tipe == "All") {
              $query = $this->db->query("SELECT a.`NoDokumen`,
                               a.`TglDokumen`,
                               a.`Prinsipal`,
                               a.`Produk`,
                               a.`NamaProduk`,
                               a.`Bonus`,
                               a.`BatchNo`,
                               xx.NoDO,
                               xx.TglDO,
                               xx.QtyDO,
                               xx.BonusDO,
                               xx.BatchNo as 'BatchDO',
                               xx.`NoBPB` AS 'BatchDoc'
                        FROM trs_terima_barang_detail a
                        JOIN 
                        ( SELECT b.`NoDO`,
                           b.`TglDO`,
                           CONCAT(b.`Pelanggan`,'~',b.`NamaPelanggan`) AS 'Pelanggan',
                           b.`KodeProduk`,
                           b.`NamaProduk`,
                           b.`QtyDO`,
                           b.`BonusDO`,
                           b.`BatchNo` ,
                           b.`NoBPB`
                          FROM trs_delivery_order_sales_detail b ) xx ON 
                        a.`Produk` = xx.KodeProduk AND 
                        a.`BatchNo` = xx.BatchNo AND 
                        a.`NoDokumen` = xx.NoBPB
                        WHERE SUBSTRING(a.`NoPO`,1,3) ='BPB' AND a.`TglDokumen` between '".$tgl1."' and '".$tgl2."';");
                
        }elseif ($tipe == "Bonus") {  
          $query = $this->db->query("SELECT a.`NoDokumen`,
                                     a.`NoPO`,
                                     a.`TglDokumen`,
                                     a.`Prinsipal`,
                                     a.`Produk`,
                                     a.`NamaProduk`,
                                     a.`Bonus`,
                                     a.`BatchNo`,
                                     xx.NoDO,
                                     xx.TglDO,
                                     xx.TipeDokumen,
                                     xx.Status,
                                     (case when xx.TipeDokumen = 'DO' then xx.QtyDO else (xx.QtyDO * -1) end ) as 'QtyDO',
                                     (case when xx.TipeDokumen = 'DO' then xx.BonusDO else (xx.BonusDO * -1) end ) as 'BonusDO',
                                     xx.BatchNo as 'BatchDO',
                                     xx.`NoBPB` AS 'BatchDoc',
                                     xx.Acu,
                                     xx.Tipe
                              FROM trs_terima_barang_detail a
                              JOIN 
                              ( SELECT b.`NoDO`,
                                 b.`TglDO`,
                                 b.`TipeDokumen`,
                                       b.`Status`,
                                 CONCAT(b.`Pelanggan`,'~',b.`NamaPelanggan`) AS 'Pelanggan',
                                 b.`KodeProduk`,
                                 b.`NamaProduk`,
                                 b.`QtyDO`,
                                 b.`BonusDO`,
                                 b.`BatchNo`,
                                 b.`NoBPB`,
                                 b.`Acu`,
                                 (CASE WHEN b.`TipeDokumen` = 'DO' THEN 'DOFaktur' ELSE 'RDO' END ) AS 'Tipe'
                                FROM trs_delivery_order_sales_detail b ) xx ON 
                              a.`Produk` = xx.KodeProduk AND 
                              a.`BatchNo` = xx.BatchNo AND 
                              a.`NoDokumen` = xx.NoBPB
                              WHERE SUBSTRING(a.`NoPO`,4,3) ='BNS' AND a.`TglDokumen` between '".$tgl1."' and '".$tgl2."' 

                              UNION ALL 

                              SELECT a.`NoDokumen`,
                                     a.`NoPO`,
                                     a.`TglDokumen`,
                                     a.`Prinsipal`,
                                     a.`Produk`,
                                     a.`NamaProduk`,
                                     a.`Bonus`,
                                     a.`BatchNo`,
                                     xx.NoFaktur AS 'NoDO',
                                     xx.TglFaktur AS 'TglDO',
                                     xx.TipeDokumen,
                                     xx.Status,
                                     xx.QtyFaktur AS 'QtyDO',
                                     xx.BonusFaktur AS 'BonusDO',
                                     xx.BatchNo as 'BatchDO',
                                     xx.`NoBPB` AS 'BatchDoc',
                                     xx.Acu,
                                     xx.Tipe
                              FROM trs_terima_barang_detail a
                              JOIN 
                              ( SELECT b.`NoFaktur`,
                                 b.`TglFaktur`,
                                 b.`TipeDokumen`,
                                       b.`Status`,
                                 CONCAT(b.`Pelanggan`,'~',b.`NamaPelanggan`) AS 'Pelanggan',
                                 b.`KodeProduk`,
                                 b.`NamaProduk`,
                                 (b.`QtyFaktur` * -1) AS 'QtyFaktur',
                                 (b.`BonusFaktur` * -1) AS 'BonusFaktur',
                                 b.`BatchNo`,
                                 b.`NoBPB`,
                                 b.`Acu`,
                                 'Retur Faktur' AS 'Tipe'
                                FROM trs_faktur_detail b 
                                WHERE b.`TipeDokumen` ='Retur') xx ON 
                              a.`Produk` = xx.KodeProduk AND 
                              a.`BatchNo` = xx.BatchNo AND 
                              a.`NoDokumen` = xx.NoBPB
                              WHERE SUBSTRING(a.`NoPO`,4,3) ='BNS' AND a.`TglDokumen` between '".$tgl1."' and '".$tgl2."';");
        }
        $nobpb = "";
        $tglbpb = "";
        $prinsipal = "";
        $Produk = "";
        $NamaProduk = "";
        $BonusBPB ="";
        $BatchBPB = "";
        $NoDO = "";
        $TglDO = "";
        $TipeDokumen = "";
        $Status = "";
        $QtyDO = "";
        $BonusDO = "";
        $BatchDO = "";
        $BatchDoc = "";
        $row = [];
        $dataretur = $query->result();
        foreach ($query->result() as $query){
          array_push($row,
                    [
                    'NoDokumen'     => $query->NoDokumen,
                    'NoPO'          => $query->NoPO,
                    'TglDokumen'    => $query->TglDokumen,
                    'Prinsipal'     => $query->Prinsipal,
                    'Produk'        => $query->Produk,
                    'NamaProduk'    => $query->NamaProduk,
                    'Bonus'         => $query->Bonus,
                    'BatchNo'       => $query->BatchNo,
                    'NoDO'          => $query->NoDO,
                    'TglDO'         => $query->TglDO,
                    'TipeDokumen'   => $query->TipeDokumen,
                    'Status'        => $query->Status,
                    'QtyDO'         => $query->QtyDO,
                    'BonusDO'       => $query->BonusDO,
                    'BatchDO'       => $query->BatchDO,
                    'BatchDoc'      => $query->BatchDoc,
                    'Acu'           => $query->Acu,
                    'Tipe'          => $query->Tipe,
                    ]);
        }
        foreach ($dataretur as $list) {
          $nobpb = $list->NoDokumen;
          $NoPO = $list->NoPO;
          $tglbpb = $list->TglDokumen;
          $prinsipal = $list->Prinsipal;
          $Produk = $list->Produk;
          $NamaProduk = $list->NamaProduk;
          $BonusBPB = $list->Bonus;
          $BatchBPB = $list->BatchNo;
          $batchDoc = $list->NoDO;
          if($list->TipeDokumen == 'Retur' and $list->Status == 'Retur' and $list->Tipe == 'RDO' ){
            $cekdata = $this->db->query("select distinct a.* from trs_delivery_order_sales_detail a 
              where a.NoBPB = '".$batchDoc."'");
            if($cekdata->num_rows() > 0){
              foreach ($cekdata->result() as $xx) {
                  $NoDO = $xx->NoDO;
                  $TglDO = $xx->TglDO;
                  $TipeDokumen = $xx->TipeDokumen;
                  $Status = $xx->Status;
                  $QtyDO = $xx->QtyDO;
                  $BonusDO = $xx->BonusDO;
                  $BatchDO = $xx->BatchNo;
                  $Acu = $xx->Acu;
                  $search_items = array('NoDO'=>$NoDO, 'Produk'=>$Produk, 'BatchDO'=>$BatchDO, 'BatchDoc'=>$batchDoc);
                  $res = $this->search($row, $search_items);
                  if(count($res) == 0){
                    array_push($row,
                    [
                      'NoDokumen'     => $nobpb,
                      'NoPO'          => $NoPO,
                      'TglDokumen'    => $tglbpb,
                      'Prinsipal'     => $prinsipal,
                      'Produk'        => $Produk,
                      'NamaProduk'    => $NamaProduk,
                      'Bonus'         => $BonusBPB,
                      'BatchNo'       => $BatchBPB,
                      'NoDO'          => $NoDO,
                      'TglDO'         => $TglDO,
                      'TipeDokumen'   => $TipeDokumen,
                      'Status'        => $Status,
                      'QtyDO'         => $QtyDO,
                      'BonusDO'       => $BonusDO,
                      'BatchDO'       => $BatchDO,
                      'BatchDoc'      => $batchDoc,
                      'Acu'           => $Acu,
                      'Tipe'          => 'DO',
                    ]);
                  }  
              }
            }
          }
        }
        return $row;
    }

    function search($array, $search_list) { 
      $result = array(); 
      foreach ($array as $key => $value) { 
        foreach ($search_list as $k => $v) {  
          if (!isset($value[$k]) || $value[$k] != $v) 
          { 
            continue 2; 
          } 
        } 
        $result[] = $value; 
      } 
      return $result; 
    } 
    public function listdatcogsfaktur($bySearch=null)
    {   
        $query = $this->db->query("select * from
                      (SELECT a.`Cabang`,
                             a.`NoFaktur`,
                             a.`TglFaktur`,
                             a.`TipeDokumen`,
                             a.`Acu`,
                             a.`KodeProduk`,
                             a.`NamaProduk`,
                             a.`BatchNo`,
                             a.`NoBPB`,
                             a.`COGS`,
                             a.`TotalCOGS`
                      FROM trs_faktur_detail a
                      WHERE ifnull(a.`COGS`,'') = 0 or ifnull(a.`COGS`,'') = ''
                      UNION ALL 
                      SELECT b.`Cabang`,
                             b.`NoDO` AS 'NoFaktur',
                             b.`TglDO` AS 'TglFaktur',
                             b.`TipeDokumen`,
                             b.`Acu`,
                             b.`KodeProduk`,
                             b.`NamaProduk`,
                             b.`BatchNo`,
                             b.`NoBPB`,
                             b.`COGS`,
                             b.`TotalCOGS`
                      FROM trs_delivery_order_sales_detail b
                      WHERE ifnull(b.`COGS`,'') = 0 or ifnull(b.`COGS`,'') = '' 
                      ORDER BY `TglFaktur` DESC
                      limit 200 
                      ) cogs
                      where cabang ='".$this->cabang."' $bySearch
                      ;")->result();

        return $query;
    }

    public function ProsesFixDataCOGS($No=null,$tipeDok=null,$Acu=null,$Kode=null,$BatchNo=null,$BatchDoc=null){

      if($tipeDok =='Faktur'){
        if(is_null($BatchDoc) or $BatchDoc == "null"){
          $BatchDoc = "";
        }
        $faktur=$this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and ifnull(NoBPB,'') = '".$BatchDoc."' limit 1 ")->row();
        if($faktur->TotalCOGS != 0){
            $this->db->set("COGS", ($faktur->TotalCOGS) / ($faktur->QtyFaktur + $faktur->BonusFaktur));
            $this->db->where("Nofaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail'); 
        }else{
          if(substr($BatchDoc,4,2) == '12'){
            //cek cogs invdet
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }else{
              //cogs invdet tidak ada
              // mengecek data retur
              $cekretur = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$BatchDoc."' and ifnull(cogs,0) = 0")->result();
              // log_message("error",print_r($cekretur,true));
              foreach ($cekretur as $key) {
                //mengecek acu faktur dari retur
                if(is_null($key->NoBPB) or $key->NoBPB == "null"){
                  $key->NoBPB = "";
                }
                $cekfaktur1 = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$key->Acu."' and KodeProduk='".$key->KodeProduk."' and BatchNo ='".$key->BatchNo."' and ifnull(NoBPB,'') = '".$key->NoBPB."' limit 1 ")->row();
                // jika cogs acu retur ( faktur ) 0
                if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
                  //cek cogs invdet
                  $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$key->KodeProduk."' and NoDokumen ='".$key->NoBPB."' and tahun ='".date('Y')."'")->row();
                  if($cekinvdet->UnitCOGS > 0){
                    // cogs  retur ( faktur ) ada value nya update cogs retur
                    $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
                    $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($key->QtyFaktur + $key->BonusFaktur))* -1);
                    $this->db->where("Nofaktur",$key->NoFaktur);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("NoBPB", $key->NoBPB);
                    $valid = $this->db->update('trs_faktur_detail'); 
                  }
                }else{
                  // jika cogs acu retur ( faktur ) ada value nya update cogs retur
                  $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
                  $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($key->QtyFaktur + $key->BonusFaktur))* -1);
                  $this->db->where("Nofaktur",$key->NoFaktur);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("NoBPB", $key->NoBPB);
                  $valid = $this->db->update('trs_faktur_detail'); 
                  if($valid){
                    //update cogs invdet
                    $this->db->set("UnitCOGS", $cekfaktur1->COGS);
                    $this->db->where("Nodokumen",$key->NoFaktur);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                    //update cogs faktur
                    $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                    $this->db->where("Nofaktur",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    //update cogs do faktur
                    $valid = $this->db->update('trs_faktur_detail'); 
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                    $this->db->where("NoDO",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                  }
                }
              }
            }
            
          }
          elseif(substr($BatchDoc,0,3) == 'BPB' or substr($BatchDoc,0,3) == 'BKB'){
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }else{
              $cekbpb = $this->db->query("select * from trs_terima_barang_detail where nodokumen ='".$BatchDoc."' and Produk ='".$Kode."' and BatchNo ='".$BatchNo."' limit 1")->row();
              //update cogs invdet
              $this->db->set("UnitCOGS", ($cekbpb->Value/$cekbpb->Banyak));
              $this->db->where("Nodokumen",$BatchDoc);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');
              //update cogs faktur
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", ($cekbpb->Value/$cekbpb->Banyak));
              $this->db->set("TotalCOGS", ($cekbpb->Value/$cekbpb->Banyak) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              //update cogs do faktur
              $this->db->set("COGS", ($cekbpb->Value/$cekbpb->Banyak));
              $this->db->set("TotalCOGS", ($cekbpb->Value/$cekbpb->Banyak) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail');
            }
          }
          elseif(substr($BatchDoc,0,5) == 'Saldo' or substr($BatchDoc,0,5) == 'saldo' or substr($BatchDoc,0,5) == 'SAwal'){
            $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            // log_message("error",print_r($fak,true));
            if($fak->TotalCOGS != 0){
              $this->db->set("COGS", ($fak->TotalCOGS) / ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
            }else{
              $cekstokdet =$this->db->query("select AVG(UnitCOGS) as 'UnitCOGS' from trs_invdet where kodeproduk = '".$Kode."' and Tahun ='".date('Y')."' and Gudang ='Baik' group by kodeproduk,Gudang limit 1" )->row();
              $this->db->set("COGS",$cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail');
            }
          }
          elseif(substr($BatchDoc,4,2) == '07'){
            //cek cogs invdet
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoFaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }else{
              //cogs invdet tidak ada
              // mengecek data retur
              $cekretur = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$BatchDoc."'")->result();
              foreach ($cekretur as $key) {
                //mengecek acu faktur dari retur
                $cekfaktur1 = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$key->Acu."' and KodeProduk='".$key->KodeProduk."' and BatchNo ='".$key->BatchNo."' and ifnull(NoBPB,'') = '".$key->NoBPB."' limit 1 ")->row();
                // jika cogs acu retur ( faktur ) 0
                if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
                  //cek cogs invdet
                  $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$key->KodeProduk."' and NoDokumen ='".$key->NoBPB."'")->row();
                  if($cekinvdet->UnitCOGS > 0){
                    // cogs  retur ( faktur ) ada value nya update cogs retur
                    $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
                    $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($key->QtyDO + $key->BonusDO))* -1);
                    $this->db->where("NoDO",$key->NoDO);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("NoBPB", $key->NoBPB);
                    $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                  }
                }else{
                  // jika cogs acu retur ( faktur ) ada value nya update cogs retur
                  $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
                  $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($key->QtyDO + $key->BonusDO))* -1);
                  $this->db->where("NoDO",$key->NoDO);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("NoBPB", $key->NoBPB);
                  $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                  if($valid){
                    //update cogs invdet
                    $this->db->set("UnitCOGS", $cekfaktur1->COGS);
                    $this->db->where("Nodokumen",$key->NoDO);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                    //update cogs faktur
                    $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyDO + $fak->BonusDO));
                    $this->db->where("Nofaktur",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    //update cogs do faktur
                    $valid = $this->db->update('trs_faktur_detail'); 
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyDO + $fak->BonusDO));
                    $this->db->where("NoDO",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                  }
                }
              }
            }  
          }
          elseif(substr($BatchDoc,4,2) == '81'){
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail');
            }else{
              $cekrelo = $this->db->query("select * from trs_relokasi_terima_detail where no_terima ='".$BatchDoc."' and Produk ='".$Kode."' and BatchNo ='".$BatchNo."' limit 1")->row();
              //update cogs invdet
              $this->db->set("UnitCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->where("Nodokumen",$BatchDoc);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');
              //update cogs faktur
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->set("TotalCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus) * ($fak->QtyFaktur + $fak->BonusFaktur)));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              //update cogs do faktur
              $this->db->set("COGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->set("TotalCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus) * ($fak->QtyFaktur + $fak->BonusFaktur)));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail');
            }
          }
          elseif(substr($BatchDoc,4,2) == '08'){
            $cekstokdet =$this->db->query("select UnitCOGS from trs_invdet where kodeproduk = '".$Kode."' and Tahun ='".date('Y')."' and Gudang ='Baik' and Nodokumen = '".$BatchDoc."' limit 1" )->row();
            $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
            $this->db->where("NoFaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail');
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail');
            //update cogs invdet
              $this->db->set("UnitCOGS", ($cekstokdet->UnitCOGS));
              $this->db->where("Nodokumen",$BatchDoc);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');
          }
        }
      }
      elseif($tipeDok =='Retur'){
        if(substr($No,4,2) == '12'){
          //mengecek acu faktur dari retur
          $cekfaktur1 = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$Acu."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and ifnull(NoBPB,'') = '".$BatchDoc."' limit 1 ")->row();

              // jika cogs acu retur ( faktur ) 0
          if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
            //cek cogs invdet
            $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$Kode."' and NoDokumen ='".$No."' and tahun ='".date('Y')."' limit 1")->row();
            if($cekinvdet->UnitCOGS > 0){
            // cogs  retur ( faktur ) ada value nya update cogs retur
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
              $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur))* -1);
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
            }
          }else{
            // jika cogs acu retur ( faktur ) ada value nya update cogs retur
            $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
            $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur))* -1);
            $this->db->where("Nofaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail'); 
            if($valid){
              //update cogs invdet
              $this->db->set("UnitCOGS", $cekfaktur1->COGS);
              $this->db->where("Nodokumen",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');      
            }
          }   
        }
        elseif(substr($No,4,2) == '07'){
          //mengecek acu faktur dari retur
          $cekfaktur1 = $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$Acu."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and ifnull(NoBPB,'') = '".$BatchDoc."' limit 1 ")->row();
              // jika cogs acu retur ( faktur ) 0
          if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
            //cek cogs invdet
            $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$Kode."' and NoDokumen ='".$No."'")->row();
            if($cekinvdet->UnitCOGS > 0){
            // cogs  retur ( faktur ) ada value nya update cogs retur
              $fak= $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
              $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO))* -1);
              $this->db->where("nodo",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }
          }else{
            // jika cogs acu retur ( faktur ) ada value nya update cogs retur
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
            $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($fak->QtyDO + $fak->BonusDO))* -1);
            $this->db->where("nodo",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            if($valid){
              //update cogs invdet
              $this->db->set("UnitCOGS", $cekfaktur1->COGS);
              $this->db->where("Nodokumen",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');      
            }
          }   
        }
      }
      elseif($tipeDok =='DO'){
        if(substr($BatchDoc,4,2) == '12'){
          //cek cogs invdet
          $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
          //cogs invdet ada
          if($cekstokdet->UnitCOGS > 0){
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", $cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
          }else{
            //cogs invdet tidak ada
            // mengecek data retur
            $cekretur = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$BatchDoc."' and ifnull(cogs,0) = 0")->result();
            foreach ($cekretur as $key) {
              //mengecek acu faktur dari retur
              $cekfaktur1 = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$key->Acu."' and KodeProduk='".$key->KodeProduk."' and BatchNo ='".$key->BatchNo."' and ifnull(NoBPB,'') = '".$key->NoBPB."' limit 1 ")->row();
              // jika cogs acu retur ( faktur ) 0
              if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
                //cek cogs invdet
                $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$key->KodeProduk."' and NoDokumen ='".$key->NoBPB."'")->row();
                if($cekinvdet->UnitCOGS > 0){
                  // cogs  retur ( faktur ) ada value nya update cogs retur
                  $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
                  $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($key->QtyFaktur + $key->BonusFaktur))* -1);
                  $this->db->where("Nofaktur",$key->NoFaktur);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("NoBPB", $key->NoBPB);
                  $valid = $this->db->update('trs_faktur_detail'); 
                }
              }else{
                // jika cogs acu retur ( faktur ) ada value nya update cogs retur
                $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
                $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($key->QtyFaktur + $key->BonusFaktur))* -1);
                $this->db->where("Nofaktur",$key->NoFaktur);
                $this->db->where("KodeProduk", $key->KodeProduk);
                $this->db->where("BatchNo", $key->BatchNo);
                $this->db->where("NoBPB", $key->NoBPB);
                $valid = $this->db->update('trs_faktur_detail'); 
                if($valid){
                  //update cogs invdet
                  $this->db->set("UnitCOGS", $cekfaktur1->COGS);
                  $this->db->where("Nodokumen",$key->NoFaktur);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("Gudang", 'Baik');
                  $valid = $this->db->update('trs_invdet');
                  //update cogs faktur
                  $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
                  $this->db->set("COGS", $cekfaktur1->COGS);
                  $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                  $this->db->where("Nofaktur",$No);
                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("BatchNo", $BatchNo);
                  $this->db->where("NoBPB", $BatchDoc);
                  $valid = $this->db->update('trs_faktur_detail'); 
                  //update cogs do faktur
                  $this->db->set("COGS", $cekfaktur1->COGS);
                  $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                  $this->db->where("NoDO",$No);
                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("BatchNo", $BatchNo);
                  $this->db->where("NoBPB", $BatchDoc);
                  $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                }
              }
            }
          }
          
        }
        elseif(substr($BatchDoc,0,3) == 'BPB' or substr($BatchDoc,0,3) == 'BKB'){
          $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
          //cogs invdet ada
          if($cekstokdet->UnitCOGS > 0){
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", $cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
          }else{
            $cekbpb = $this->db->query("select * from trs_terima_barang_detail where nodokumen ='".$BatchDoc."' and Produk ='".$Kode."' and BatchNo ='".$BatchNo."' limit 1")->row();
            //update cogs invdet
            $this->db->set("UnitCOGS", ($cekbpb->Value/$cekbpb->Banyak));
            $this->db->where("Nodokumen",$BatchDoc);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("Gudang", 'Baik');
            $valid = $this->db->update('trs_invdet');
            //update cogs DO
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", ($cekbpb->Value/$cekbpb->Banyak));
            $this->db->set("TotalCOGS", ($cekbpb->Value/$cekbpb->Banyak) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("Nofaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail'); 
            //update cogs do faktur
            $this->db->set("COGS", ($cekbpb->Value/$cekbpb->Banyak));
            $this->db->set("TotalCOGS", ($cekbpb->Value/$cekbpb->Banyak) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail');
          }
        }
        elseif(substr($BatchDoc,0,5) == 'Saldo' or substr($BatchDoc,0,5) == 'saldo' or substr($BatchDoc,0,5) == 'SAwal'){
          $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
          if($fak->TotalCOGS != 0){
            $this->db->set("COGS", ($fak->TotalCOGS) / ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            $this->db->set("COGS", ($fak->TotalCOGS) / ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoFaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail'); 
          }else{
            $cekstokdet =$this->db->query("select AVG(UnitCOGS) as 'UnitCOGS' from trs_invdet where kodeproduk = '".$Kode."' and Tahun ='".date('Y')."' and Gudang ='Baik' group by kodeproduk,Gudang limit 1" )->row();
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail');
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoFaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail');
          }
        }
        elseif(substr($BatchDoc,4,2) == '07'){
          //cek cogs invdet
          $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' limit 1" )->row();
          //cogs invdet ada
          if($cekstokdet->UnitCOGS > 0){
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoFaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS", $cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            $this->db->set("COGS", $cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("Nofaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail'); 
            
          }else{
            //cogs invdet tidak ada
            // mengecek data retur
            $cekretur = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$BatchDoc."'")->result();
            foreach ($cekretur as $key) {
              //mengecek acu faktur dari retur
              $cekfaktur1 = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$key->Acu."' and KodeProduk='".$key->KodeProduk."' and BatchNo ='".$key->BatchNo."' and ifnull(NoBPB,'') = '".$key->NoBPB."' limit 1 ")->row();
              // jika cogs acu retur ( faktur ) 0
              if($cekfaktur1->COGS == 0 or $cekfaktur1->COGS == null or $cekfaktur1->COGS == ""){
                //cek cogs invdet
                $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$key->KodeProduk."' and NoDokumen ='".$key->NoBPB."'")->row();
                if($cekinvdet->UnitCOGS > 0){
                  // cogs  retur ( faktur ) ada value nya update cogs retur
                  $this->db->set("COGS", ($cekinvdet->UnitCOGS)* -1);
                  $this->db->set("TotalCOGS", (($cekinvdet->UnitCOGS) * ($key->QtyDO + $key->BonusDO))* -1);
                  $this->db->where("NoDO",$key->NoDO);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("NoBPB", $key->NoBPB);
                  $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                }
              }else{
                // jika cogs acu retur ( faktur ) ada value nya update cogs retur
                $this->db->set("COGS", ($cekfaktur1->COGS) * -1);
                $this->db->set("TotalCOGS", (($cekfaktur1->COGS) * ($key->QtyDO + $key->BonusDO))* -1);
                $this->db->where("NoDO",$key->NoDO);
                $this->db->where("KodeProduk", $key->KodeProduk);
                $this->db->where("BatchNo", $key->BatchNo);
                $this->db->where("NoBPB", $key->NoBPB);
                $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                if($valid){
                  //update cogs invdet
                  $this->db->set("UnitCOGS", $cekfaktur1->COGS);
                  $this->db->where("Nodokumen",$key->NoDO);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("Gudang", 'Baik');
                  $valid = $this->db->update('trs_invdet');
                  //update cogs faktur
                  $fak= $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
                  $this->db->set("COGS", $cekfaktur1->COGS);
                  $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyDO + $fak->BonusDO));
                  $this->db->where("NoDO",$No);
                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("BatchNo", $BatchNo);
                  $this->db->where("NoBPB", $BatchDoc);
                  $valid = $this->db->update('trs_delivery_order_sales_detail'); 
                  $this->db->set("COGS", $cekfaktur1->COGS);
                  $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyDO + $fak->BonusDO));
                  $this->db->where("Nofaktur",$No);
                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("BatchNo", $BatchNo);
                  $this->db->where("NoBPB", $BatchDoc);
                  //update cogs do faktur
                  $valid = $this->db->update('trs_faktur_detail'); 
                  
                }
              }
            }
          }  
        }
        elseif(substr($BatchDoc,4,2) == '81'){
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' and tahun ='".date('Y')."' limit 1" )->row();
            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail');
              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              
            }else{
              $cekrelo = $this->db->query("select * from trs_relokasi_terima_detail where no_terima ='".$BatchDoc."' and Produk ='".$Kode."' and BatchNo ='".$BatchNo."' limit 1")->row();
              //update cogs invdet
              $this->db->set("UnitCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->where("Nodokumen",$BatchDoc);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');
              //update cogs faktur
              $fak= $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
              $this->db->set("COGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->set("TotalCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus) * ($fak->QtyDO + $fak->BonusDO)));
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 
              //update cogs do faktur
              $this->db->set("COGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus)));
              $this->db->set("TotalCOGS", ($cekrelo->Value/($cekrelo->Qty + $cekrelo->Bonus) * ($fak->QtyDO + $fak->BonusDO)));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail');
            }
        }
        elseif(substr($BatchDoc,4,2) == '08'){
            $cekstokdet =$this->db->query("select UnitCOGS from trs_invdet where kodeproduk = '".$Kode."' and Tahun ='".date('Y')."' and Gudang ='Relokasi' and Nodokumen = '".$BatchDoc."' limit 1" )->row();
            //update cogs invdet
              $this->db->set("UnitCOGS", ($cekstokdet->UnitCOGS));
              $this->db->where("Nodokumen",$BatchDoc);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("Gudang", 'Baik');
              $valid = $this->db->update('trs_invdet');
            $fak= $this->db->query("select * from trs_delivery_order_sales_detail where nodo ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoDO",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_delivery_order_sales_detail');
            $this->db->set("COGS",$cekstokdet->UnitCOGS);
            $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyDO + $fak->BonusDO));
            $this->db->where("NoFaktur",$No);
            $this->db->where("KodeProduk", $Kode);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("NoBPB", $BatchDoc);
            $valid = $this->db->update('trs_faktur_detail');

        }
      }
    }

    public function listdatcogsfakturAll()
    {   
        $query = $this->db->query("select * from
                      (SELECT a.`Cabang`,
                             a.`NoFaktur`,
                             a.`TglFaktur`,
                             a.`TipeDokumen`,
                             a.`Acu`,
                             a.`KodeProduk`,
                             a.`NamaProduk`,
                             a.`BatchNo`,
                             a.`NoBPB`,
                             a.`COGS`,
                             a.`TotalCOGS`
                      FROM trs_faktur_detail a
                      WHERE ifnull(a.`COGS`,'') = 0 or ifnull(a.`COGS`,'') = ''
                      UNION ALL 
                      SELECT b.`Cabang`,
                             b.`NoDO` AS 'NoFaktur',
                             b.`TglDO` AS 'TglFaktur',
                             b.`TipeDokumen`,
                             b.`Acu`,
                             b.`KodeProduk`,
                             b.`NamaProduk`,
                             b.`BatchNo`,
                             b.`NoBPB`,
                             b.`COGS`,
                             b.`TotalCOGS`
                      FROM trs_delivery_order_sales_detail b
                      WHERE ifnull(b.`COGS`,'') = 0 or ifnull(b.`COGS`,'') = ''
                      ORDER BY `TglFaktur` DESC
                      limit 100 ) cogs
                      ")->result();
        return $query;
    }

    public function listlaporanRekapPiutang($tgl1 = null,$tgl2 = null,$pelanggan=null)
    {  
        $var_pelanggan = $where = "";

        
        foreach ($pelanggan as $r => $value) {
            $var_pelanggan .= "'".$pelanggan[$r]."',";
        }
        $pelanggan = rtrim($var_pelanggan,",");

       if(preg_match("/all/i", $pelanggan)) {
            $where = "";
            $where1 = "";
       }else{
           $where = "AND Pelanggan IN ($pelanggan)";
           $where1 = "WHERE Kode IN ($pelanggan)";
       }

       $query = $this->db->query("SELECT  xx.Kode,xx.Pelanggan, IFNULL(SUM(IFNULL(debet_awal,0)) - SUM(IFNULL(kredit_awal,0)),0) AS Saldo_awal,IFNULL(SUM(debet),0) debet,
                IFNULL(SUM(cash),0)cash, IFNULL(SUM(transfer),0)transfer, IFNULL(SUM(giro),0) giro, IFNULL(SUM(retur),0) retur, IFNULL(SUM(CN),0) CN
            FROM mst_pelanggan xx                
            LEFT JOIN 
             (
            SELECT Pelanggan,NamaPelanggan,'Faktur' AS tipe,TglFaktur AS Tgl,NoFaktur,TipeDokumen,'' AS TipePelunasan, 
            CASE WHEN TglFaktur < '".$tgl1."' THEN  
            IFNULL(Total,0) 
            ELSE 0 END AS debet_awal, 0 AS kredit_awal,
            CASE WHEN TglFaktur >= '".$tgl1."' THEN  
            IFNULL(Total,0)
            ELSE
            0 END  AS debet, 0 AS cash , 0 AS transfer, 0 AS giro, 0 AS retur, 0 CN 
            FROM trs_faktur WHERE TglFaktur <= '".$tgl2."' AND 
             (STATUS IN ( 'Open','OpenDIH','Giro') OR (`Saldo` + `saldo_giro`) != 0)  AND 
            STATUS NOT IN ('Usulan','Batal','Reject') 
            $where

             UNION ALL
            SELECT b.`Pelanggan`,a.NamaPelanggan,'pelunasan' AS tipe, a.TglPelunasan AS Tgl,b.NoFaktur, '' AS TipeDokumen, TipePelunasan AS TipePelunasan, 0 AS debet_awal,
            CASE WHEN a.TglPelunasan < '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS kredit_awal,
            0 AS debet, 
            CASE WHEN TipePelunasan = 'Cash' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS cash , 
            CASE WHEN TipePelunasan = 'Transfer' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS transfer, 
            CASE WHEN TipePelunasan = 'Giro' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS giro, 
            CASE WHEN b.TipeDokumen = 'Retur' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS retur, 
            CASE WHEN b.TipeDokumen = 'CN' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS CN 
            FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.nomorfaktur = b.NoFaktur AND a.KodePelanggan = b.Pelanggan
            WHERE a.TglPelunasan <= '".$tgl2."' AND 
             (b.Status IN ( 'Open','OpenDIH','Giro') OR (b.`Saldo` + b.`saldo_giro`) != 0)  AND 
            b.Status NOT IN ('Usulan','Batal','Reject')  AND a.Status <> 'Batal'
            AND IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) <> 0
            $where

            ) z ON xx.Kode = z.Pelanggan $where1 GROUP BY xx.Kode");
        return $query;
    }

    public function getkartupiutangPelanggan_sawal($Kode = NULL,$tgl1 = NULL,$tgl2 = NULL){
      $where = "AND Pelanggan = '".$Kode."' ";

        $query = $this->db->query("SELECT z.Pelanggan, IFNULL(SUM(IFNULL(debet_awal,0)) - SUM(IFNULL(kredit_awal,0)),0) AS Saldo_awal
            FROM 
             (
            SELECT Pelanggan,NamaPelanggan,'Faktur' AS tipe,TglFaktur AS Tgl,NoFaktur,TipeDokumen,'' AS TipePelunasan, 
            CASE WHEN TglFaktur < '".$tgl1."' THEN  
            IFNULL(Total,0) 
            ELSE 0 END AS debet_awal, 0 AS kredit_awal,
            CASE WHEN TglFaktur >= '".$tgl1."' THEN  
            IFNULL(saldo,0) + IFNULL(saldo_giro,0)
            ELSE
            0 END  AS debet, 0 AS cash , 0 AS transfer, 0 AS giro, 0 AS retur, 0 CN 
            FROM trs_faktur WHERE TglFaktur <= '".$tgl1."' AND 
             (STATUS IN ( 'Open','OpenDIH','Giro') OR (`Saldo` + `saldo_giro`) != 0)  AND 
            STATUS NOT IN ('Usulan','Batal','Reject') 
            $where

             UNION ALL
            SELECT b.`Pelanggan`,a.NamaPelanggan,'pelunasan' AS tipe, a.TglPelunasan AS Tgl,b.NoFaktur, '' AS TipeDokumen, TipePelunasan AS TipePelunasan, 0 AS debet_awal,
            CASE WHEN a.TglPelunasan < '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS kredit_awal,
            0 AS debet, 
            CASE WHEN TipePelunasan = 'Cash' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS cash , 
            CASE WHEN TipePelunasan = 'Transfer' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS transfer, 
            CASE WHEN TipePelunasan = 'Giro' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS giro, 
            CASE WHEN b.TipeDokumen = 'Retur' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS retur, 
            CASE WHEN b.TipeDokumen = 'CN' AND a.TglPelunasan >= '".$tgl1."' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS CN 
            FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.nomorfaktur = b.NoFaktur AND a.KodePelanggan = b.Pelanggan
            WHERE a.TglPelunasan <= '".$tgl1."' AND 
             (b.Status IN ( 'Open','OpenDIH','Giro') OR (b.`Saldo` + b.`saldo_giro`) != 0)  AND 
            b.Status NOT IN ('Usulan','Batal','Reject')  AND a.Status <> 'Batal'
            AND IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) <> 0
            $where

            ) z 

                    ")->result();
        return $query;
    }

    public function getkartupiutangPelanggan($Kode = NULL,$tgl1 = NULL,$tgl2 = NULL){
        $query = $this->db->query("
                            SELECT a.`NoDIH` AS 'Nodokumen',
                                   a.`TglPelunasan` AS 'Tgl',
                                   0.00 AS 'debet',
                                   CASE WHEN  IFNULL(a.status,'') = 'Batal' THEN 0 ELSE
                                   (IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')) END AS 'kredit',
                                   'Pelunasan' AS 'Transaksi',
                                    CASE WHEN IFNULL(a.`TipePelunasan`,'') ='Cash' AND IFNULL(a.status,'') != 'Batal' THEN
                                       CONCAT('Pelunasan Faktur : ',a.nomorfaktur,' Tgl : ',b.tglfaktur,' ~ Status : Bayar Cash~',a.Status)
                                        WHEN IFNULL(a.`TipePelunasan`,'') ='Transfer' AND IFNULL(a.status,'') != 'Batal' THEN
                                       CONCAT('Pelunasan Faktur : ',a.nomorfaktur,' Tgl : ',b.tglfaktur,' ~ Status : Bayar transfer~',a.Status)
                                        WHEN IFNULL(a.`TipePelunasan`,'') ='Giro' AND IFNULL(a.status,'') != 'Batal' THEN
                                       CONCAT('Pelunasan Faktur : ',a.nomorfaktur,' Tgl : ',b.tglfaktur ,'~ Status : Bayar Giro Cair~',a.Status)
                                        WHEN  IFNULL(a.status,'') = 'Batal' THEN
                                       CONCAT('Pelunasan Faktur : ',a.nomorfaktur,' Tgl : ',b.tglfaktur,' ~ Status : Batal Bayar~',a.Status)
                                        ELSE '' END
                                    AS 'Keterangan',
                                   a.`TglFaktur` AS 'TglFaktur',
                                   a.`NomorFaktur` AS'nomorfaktur'

                            FROM trs_pelunasan_detail a 
                            JOIN (SELECT NoFaktur,STATUS,saldo,saldo_giro,tglfaktur FROM trs_faktur WHERE (`Saldo` + `saldo_giro`) != 0 ) b
                             ON a.`NomorFaktur` = b.NoFaktur

                            WHERE 
                            -- IFNULL(a.status,'') != 'Batal' AND 
                                 a.KodePelanggan ='".$Kode."' AND
                                 -- IFNULL(a.`TipePelunasan`,'') ='Cash'
                                 a.TglPelunasan BETWEEN '".$tgl1."' AND '".$tgl2."' 
                                 AND (IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')) <> 0

                            UNION ALL
                            SELECT b.`NoFaktur` AS 'Nodokumen',
                                   b.`TglFaktur` AS 'Tgl',
                                   IFNULL(b.`Total`,0) + IFNULL(b.saldo_giro,0) AS 'debit',
                                   '0.00' AS 'kredit',
                                   (CASE WHEN TipeDokumen ='Faktur' THEN 'Faktur'
                                         WHEN TipeDokumen ='Retur' THEN 'Retur'
                                         WHEN TipeDokumen ='CN' THEN 'CN'
                                         WHEN TipeDokumen ='DN' THEN 'DN'
                                         ELSE '' END) AS 'Transaksi',
                                    (CASE WHEN TipeDokumen ='Faktur' THEN CONCAT('Faktur Pelanggan : ',b.Pelanggan,'~',b.NamaPelanggan)
                                         WHEN TipeDokumen ='Retur' THEN CONCAT('Retur Pelanggan : ',b.Pelanggan,'~',b.NamaPelanggan)
                                         WHEN TipeDokumen ='CN' THEN CONCAT('CN Faktur Pelanggan : ',b.Pelanggan,'~',b.NamaPelanggan)
                                         WHEN TipeDokumen ='DN' THEN CONCAT('DN Faktur Pelanggan : ',b.Pelanggan,'~',b.NamaPelanggan)
                                         ELSE '' END) AS 'Keterangan',
                                    b.`TglFaktur` AS 'TglFaktur',
                                    b.`NoFaktur` AS'nomorfaktur'
                            FROM trs_faktur b
                            WHERE b.`Pelanggan` ='".$Kode."' AND (
                              STATUS IN ('Open', 'OpenDIH', 'Giro')
                              OR (b.`Saldo` + b.`saldo_giro`) != 0
                            )
                            AND b.STATUS NOT IN ('Usulan', 'Batal', 'Reject') AND b.tglfaktur BETWEEN '".$tgl1."' AND '".$tgl2."' 
                            ORDER BY Tgl ASC
                    ")->result();
        return $query;
    }

    function listlaporanAgingPiutang($tgl = null ,$jenis = null,$pelanggan = null){
      $var_pelanggan = $where = $where1 = "";

        
        foreach ($pelanggan as $r => $value) {
            $var_pelanggan .= "'".$pelanggan[$r]."',";
        }
        $pelanggan = rtrim($var_pelanggan,",");

       if(preg_match("/all/i", $pelanggan)) {
            $where = "";
            $where1 = "";
       }else{
           $where = "AND a.Pelanggan IN ($pelanggan)";
           $where1 = "WHERE c.Kode IN ($pelanggan)";
       }

       $query = $this->db->query("SELECT c.Kode as Pelanggan,c.Pelanggan as NamaPelanggan,TglFaktur AS tgl,NoFaktur,TglJtoFaktur,umur_jto,IFNULL(SUM(debet - kredit),0) AS saldo_akhir,
                  SUM(IF(umur_jto = 0 , debet - kredit,0)) AS piutang_bjt,
                  SUM(IF(umur_jto BETWEEN 1 AND 30 , debet - kredit,0)) AS piutang_30,
                  SUM(IF(umur_jto BETWEEN 31 AND 45 , debet - kredit,0)) AS piutang_45,
                  SUM(IF(umur_jto BETWEEN 46 AND 60 , debet - kredit,0)) AS piutang_60,
                  SUM(IF(umur_jto BETWEEN 61 AND 90 , debet - kredit,0)) AS piutang_90,
                  SUM(IF(umur_jto > 90 , debet - kredit,0)) AS piutang_90_
                  FROM mst_pelanggan c LEFT JOIN (
                  SELECT
                    a.Pelanggan,
                    a.NoFaktur,
                    a.TglFaktur,
                    a.TglJtoFaktur,
                    CASE
                      WHEN a.TglJtoFaktur < CURDATE()
                      THEN TIMESTAMPDIFF(DAY, DATE (a.TglJtoFaktur), CURDATE())
                      ELSE 0
                    END umur_jto,
                    IFNULL(Total,0) AS debet,
                    IFNULL(b.kredit,0) kredit
                  FROM
                    trs_faktur a LEFT JOIN 
                    (
                    SELECT kodepelanggan,nomorfaktur,SUM(
                    (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(value_transfer,0) + IFNULL(materai,0))) kredit
                    FROM 
                     trs_pelunasan_detail WHERE tglpelunasan <= '".$tgl."' AND STATUS <> 'Batal'  GROUP BY nomorfaktur
                     ) b ON a.nofaktur = b.nomorfaktur
                  WHERE TglFaktur <= '".$tgl."'
                    AND (
                      STATUS IN ('Open', 'OpenDIH', 'Giro')
                      OR (`Saldo` + `saldo_giro`) != 0
                    )
                    AND a.STATUS NOT IN ('Usulan', 'Batal', 'Reject')
                    $where
                    )z ON c.Kode = z.Pelanggan $where1  GROUP BY c.Kode");
       return $query;
    }

    function listlaporanAgingPiutangDetail($tgl = null ,$jenis = null,$pelanggan = null){
      $var_pelanggan = $where = $where1 = "";

        
        foreach ($pelanggan as $r => $value) {
            $var_pelanggan .= "'".$pelanggan[$r]."',";
        }
        $pelanggan = rtrim($var_pelanggan,",");

       if(preg_match("/all/i", $pelanggan)) {
            $where = "";
            $where1 = "";
       }else{
           $where = "AND a.Pelanggan IN ($pelanggan)";
           $where1 = "WHERE c.Kode IN ($pelanggan)";
       }

       $query = $this->db->query("SELECT c.kode AS Pelanggan,c.Pelanggan AS NamaPelanggan,z.Salesman,z.NamaSalesman,TglFaktur ,NoFaktur,TglJtoFaktur,umur_jto,IFNULL(debet - kredit,0) AS saldo_akhir,
                  IF(umur_jto = 0 , debet - kredit,0) AS piutang_bjt,
                  IF(umur_jto BETWEEN 1 AND 30 , debet - kredit,0) AS piutang_30,
                  IF(umur_jto BETWEEN 31 AND 45 , debet - kredit,0) AS piutang_45,
                  IF(umur_jto BETWEEN 46 AND 60 , debet - kredit,0) AS piutang_60,
                  IF(umur_jto BETWEEN 61 AND 90 , debet - kredit,0) AS piutang_90,
                  IF(umur_jto > 90 , debet - kredit,0) AS piutang_90_
                  FROM mst_pelanggan c LEFT JOIN (
                  SELECT
                    a.Pelanggan,
                    a.NamaPelanggan,
                    a.Salesman,
                    a.NamaSalesman,
                    a.NoFaktur,
                    a.TglFaktur,
                    a.TglJtoFaktur,
                    CASE
                      WHEN a.TglJtoFaktur < CURDATE()
                      THEN TIMESTAMPDIFF(DAY, DATE (a.TglJtoFaktur), CURDATE())
                      ELSE 0
                    END umur_jto,
                    CASE WHEN IFNULL(b.kredit,0) <> 0 THEN
                    IFNULL(Total,0)
                    ELSE
                    IFNULL (saldo, 0) + IFNULL (saldo_giro, 0)
                    END
                    AS debet,
                    IFNULL(b.kredit,0) kredit
                  FROM 
                    trs_faktur a 
                    LEFT JOIN 
                    (
                    SELECT kodepelanggan,nomorfaktur,SUM(
                    (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(value_transfer,0) + IFNULL(materai,0))) kredit
                    FROM 
                     trs_pelunasan_detail WHERE tglpelunasan <= '".$tgl."' AND STATUS <> 'Batal'  GROUP BY nomorfaktur
                     ) b ON a.nofaktur = b.nomorfaktur
                  WHERE TglFaktur <= '".$tgl."'
                    AND (
                      STATUS IN ('Open', 'OpenDIH', 'Giro')
                      OR (`Saldo` + `saldo_giro`) != 0
                    )
                    AND a.STATUS NOT IN ('Usulan', 'Batal', 'Reject')
                    $where
                    )z ON c.Kode = z.Pelanggan $where1 ORDER BY c.Kode");
       return $query;
    }

    function listlaporanAgingPiutangSales($tgl = null ,$jenis = null,$salesman = null){
      $var_salesman = $where = $where1 = "";

        
        foreach ($salesman as $r => $value) {
            $var_salesman .= "'".$salesman[$r]."',";
        }
        $salesman = rtrim($var_salesman,",");

       if(preg_match("/all/i", $salesman)) {
            $where = "";
            $where1 = "";
       }else{
           $where = "AND a.salesman IN ($salesman)";
           $where1 = "WHERE c.Kode IN ($salesman)";
       }

       $query = $this->db->query("SELECT Pelanggan,NamaPelanggan,c.Kode AS Salesman,c.Nama AS NamaSalesman,TglFaktur ,NoFaktur,TglJtoFaktur,umur_jto,IFNULL(debet - kredit,0) AS saldo_akhir,
                  IF(umur_jto = 0 , debet - kredit,0) AS piutang_bjt,
                  IF(umur_jto BETWEEN 1 AND 30 , debet - kredit,0) AS piutang_30,
                  IF(umur_jto BETWEEN 31 AND 45 , debet - kredit,0) AS piutang_45,
                  IF(umur_jto BETWEEN 46 AND 60 , debet - kredit,0) AS piutang_60,
                  IF(umur_jto BETWEEN 61 AND 90 , debet - kredit,0) AS piutang_90,
                  IF(umur_jto > 90 , debet - kredit,0) AS piutang_90_
                  FROM mst_karyawan c LEFT JOIN (
                  SELECT
                    a.Pelanggan,
                    a.NamaPelanggan,
                    a.Salesman,
                    a.NamaSalesman,
                    a.NoFaktur,
                    a.TglFaktur,
                    a.TglJtoFaktur,
                    CASE
                      WHEN a.TglJtoFaktur < CURDATE()
                      THEN TIMESTAMPDIFF(DAY, DATE (a.TglJtoFaktur), CURDATE())
                      ELSE 0
                    END umur_jto,
                    IFNULL(Total,0)
                    AS debet,
                    IFNULL(b.kredit,0) kredit
                  FROM 
                    trs_faktur a 
                    LEFT JOIN 
                    (
                    SELECT kodepelanggan,nomorfaktur,SUM(
                    (IFNULL(`ValuePelunasan`,0) + IFNULL(value_pembulatan,0) + IFNULL(value_transfer,0) + IFNULL(materai,0))) kredit
                    FROM 
                     trs_pelunasan_detail WHERE tglpelunasan <= '".$tgl."' AND STATUS <> 'Batal'  GROUP BY nomorfaktur
                     ) b ON a.nofaktur = b.nomorfaktur
                  WHERE TglFaktur <= '".$tgl."'
                    AND (
                      STATUS IN ('Open', 'OpenDIH', 'Giro')
                      OR (`Saldo` + `saldo_giro`) != 0
                    )
                    AND a.STATUS NOT IN ('Usulan', 'Batal', 'Reject')
                    $where
                    )z ON c.Kode = z.Salesman $where1  ORDER BY c.Kode,NoFaktur");
       return $query;
    }
}
