mysql -h119.235.19.138 -usapta -pSapta254*x -e"
use sst;

INSERT INTO sst.trs_delivery_order_sales
            SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_mkr t_order
            ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
            time_batal = t_order.time_batal,user_batal= t_order.user_batal,
            modified_at = t_order.modified_at, modified_by = t_order.modified_by,
            Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,
            user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,
            user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur ;
DELETE FROM sst_federate_temp.trs_delivery_order_sales_fed_mkr;


INSERT INTO sst.trs_delivery_order_sales_detail
              SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_mkr t_order
              ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
              time_batal = t_order.time_batal,user_batal= t_order.user_batal,
              modified_at = t_order.modified_at, modified_by = t_order.modified_by,
              Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,
              retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,
              status_validasi = t_order.status_validasi,noretur = t_order.noretur;
DELETE FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_mkr;      


INSERT INTO sst.trs_kiriman
            SELECT * FROM sst_federate_temp.trs_kiriman_fed_mkr t_kirim
            ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,
            TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,
            updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;
DELETE FROM sst_federate_temp.trs_kiriman_fed_mkr; 


INSERT INTO sst.trs_faktur
                SELECT * FROM sst_federate_temp.trs_faktur_fed_mkr t_faktur
                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,
                StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,
                umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,
                TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,
                modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,
                nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
DELETE FROM sst_federate_temp.trs_faktur_fed_mkr;

INSERT INTO sst.trs_faktur_detail
                SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_mkr t_faktur
                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;
DELETE FROM sst_federate_temp.trs_faktur_detail_fed_mkr;

INSERT INTO sst.trs_faktur_cndn
         SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_mkr t_faktur
         ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,
         DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,
         Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,
         Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,
         Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;
DELETE FROM sst_federate_temp.trs_faktur_cndn_fed_mkr;
         

INSERT INTO sst.trs_pelunasan_detail
        SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_mkr t_lunas
        ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
        Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
        SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,
        modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
        bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
        materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
        No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
DELETE FROM sst_federate_temp.trs_pelunasan_detail_fed_mkr;
       

INSERT INTO sst.trs_pelunasan_giro_detail
            SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_mkr t_lunas
            ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
            Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
            SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,
            modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
            bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
            materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
            No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
DELETE FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_mkr;


INSERT INTO sst.trs_giro
        SELECT * FROM sst_federate_temp.trs_giro_fed_mkr t_giro
        ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,
        TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;
DELETE FROM sst_federate_temp.trs_giro_fed_mkr;
                   

INSERT INTO sst.trs_buku_transaksi_all
      SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_mkr t_gl
      ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,
      Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,
      Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,
      Jurnal_ID=t_gl.Jurnal_ID;
DELETE FROM sst_federate_temp.trs_buku_transaksi_fed_mkr;


DELETE FROM sst.trs_invsum_all where ifnull(cabang,'')='';
DELETE FROM sst.trs_invsum_all WHERE Cabang='makasar' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invsum_fed_mkr) > 0;
INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_mkr;
DELETE FROM sst_federate_temp.trs_invsum_fed_mkr;

DELETE FROM sst.trs_invdet_all where ifnull(cabang,'')='';
DELETE FROM sst.trs_invdet_all WHERE Cabang='makasar' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invdet_fed_mkr) > 0;
INSERT INTO sst.trs_invdet_all  
  (Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,
    Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,
    SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,
    LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut)
SELECT Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,
    Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,
    SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,
    LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut 
FROM sst_federate_temp.trs_invdet_fed_mkr;
DELETE FROM sst_federate_temp.trs_invdet_fed_mkr;

DELETE FROM sst.mst_pelanggan where ifnull(cabang,'')='';
DELETE FROM sst.mst_pelanggan WHERE Cabang='makasar' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_pelanggan_fed_mkr) > 0;
INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_mkr;
DELETE FROM sst_federate_temp.mst_pelanggan_fed_mkr;

DELETE FROM sst.mst_karyawan where ifnull(cabang,'')='';
DELETE FROM sst.mst_karyawan WHERE Cabang='makasar' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_karyawan_fed_mkr) > 0;
INSERT INTO sst.mst_karyawan SELECT * FROM sst_federate_temp.mst_karyawan_fed_mkr;
DELETE FROM sst_federate_temp.mst_karyawan_fed_mkr;

UPDATE 
  sst.trs_delivery_order_sales_detail a 
LEFT JOIN sst.mst_pelanggan b ON a.Cabang=b.Cabang AND a.Pelanggan=b.Kode AND b.Cabang='makasar'
SET
a.kota=b.Kota,
a.telp=b.Telp,
a.tipe_2=b.Tipe_2 
WHERE MONTH(a.TglDO)=MONTH(CURDATE()) AND YEAR(a.TglDO)=YEAR(CURDATE()) AND a.Kota IS NULL
AND a.Cabang='makasar' ;


UPDATE 
  sst.trs_faktur_detail a 
LEFT JOIN sst.mst_pelanggan b ON a.Cabang=b.Cabang AND a.Pelanggan=b.Kode AND b.Cabang='makasar'
SET
a.kota=b.Kota,
a.telp=b.Telp,
a.tipe_2=b.Tipe_2 
WHERE MONTH(a.TglFaktur)=MONTH(CURDATE()) AND YEAR(a.TglFaktur)=YEAR(CURDATE()) AND a.Kota IS NULL
AND a.Cabang='makasar' ;


DELETE FROM sst.trs_delivery_order_sales_detail_month where cabang = 'Makasar';
INSERT INTO sst.trs_delivery_order_sales_detail_month
            SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_month_fed_mkr;


DELETE FROM sst.trs_faktur_detail_month where cabang = 'Makasar';
INSERT INTO sst.trs_faktur_detail_month
            SELECT * FROM sst_federate_temp.trs_faktur_detail_month_fed_mkr;



 "
  wget http://119.235.19.142/fedcab/salestele.php