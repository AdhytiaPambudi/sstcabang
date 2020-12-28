<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2020-12-08 09:27:38 --> Severity: Warning --> session_destroy(): Trying to destroy uninitialized session D:\xampp70\htdocs\sstcabang\application\controllers\Auth.php 72
ERROR - 2020-12-08 09:32:04 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:32:04 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:32:06 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:32:06 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:32:31 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:32:32 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:07 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '1select * from trs_faktur where Cabang = 'Mataram' and TipeDokumen in ('Faktur',' at line 1 - Invalid query: 1select * from trs_faktur where Cabang = 'Mataram' and TipeDokumen in ('Faktur','Retur','CN','DN') and Status not in ('Usulan','Batal')    order by TimeFaktur DESC, NoFaktur ASC 
ERROR - 2020-12-08 09:36:10 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:10 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:11 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:11 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:11 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '1select * from trs_faktur where Cabang = 'Mataram' and TipeDokumen in ('Faktur',' at line 1 - Invalid query: 1select * from trs_faktur where Cabang = 'Mataram' and TipeDokumen in ('Faktur','Retur','CN','DN') and Status not in ('Usulan','Batal')    order by TimeFaktur DESC, NoFaktur ASC 
ERROR - 2020-12-08 09:36:38 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:36:38 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:37:05 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:37:05 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:38:20 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:38:21 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:39:27 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:39:27 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:41:19 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:41:19 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:41:30 --> Severity: Warning --> Invalid argument supplied for foreach() D:\xampp70\htdocs\sstcabang\application\models\laporan\Model_laporanAll.php 2960
ERROR - 2020-12-08 09:41:30 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near ')

             UNION ALL
            SELECT b.`Pelanggan`,a.NamaPelanggan,'pelu' at line 17 - Invalid query: SELECT  xx.Kode,xx.Pelanggan, IFNULL(SUM(IFNULL(debet_awal,0)) - SUM(IFNULL(kredit_awal,0)),0) AS Saldo_awal,IFNULL(SUM(debet),0) debet,
                IFNULL(SUM(cash),0)cash, IFNULL(SUM(transfer),0)transfer, IFNULL(SUM(giro),0) giro, IFNULL(SUM(retur),0) retur, IFNULL(SUM(CN),0) CN
            FROM mst_pelanggan xx                
            LEFT JOIN 
             (
            SELECT Pelanggan,NamaPelanggan,'Faktur' AS tipe,TglFaktur AS Tgl,NoFaktur,TipeDokumen,'' AS TipePelunasan, 
            CASE WHEN TglFaktur < '' THEN  
            IFNULL(Total,0) 
            ELSE 0 END AS debet_awal, 0 AS kredit_awal,
            CASE WHEN TglFaktur >= '' THEN  
            IFNULL(Total,0)
            ELSE
            0 END  AS debet, 0 AS cash , 0 AS transfer, 0 AS giro, 0 AS retur, 0 CN 
            FROM trs_faktur WHERE TglFaktur <= '' AND 
             (STATUS IN ( 'Open','OpenDIH','Giro') OR (`Saldo` + `saldo_giro`) != 0)  AND 
            STATUS NOT IN ('Usulan','Batal','Reject') 
            AND Pelanggan IN ()

             UNION ALL
            SELECT b.`Pelanggan`,a.NamaPelanggan,'pelunasan' AS tipe, a.TglPelunasan AS Tgl,b.NoFaktur, '' AS TipeDokumen, TipePelunasan AS TipePelunasan, 0 AS debet_awal,
            CASE WHEN a.TglPelunasan < '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS kredit_awal,
            0 AS debet, 
            CASE WHEN TipePelunasan = 'Cash' AND a.TglPelunasan >= '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS cash , 
            CASE WHEN TipePelunasan = 'Transfer' AND a.TglPelunasan >= '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS transfer, 
            CASE WHEN TipePelunasan = 'Giro' AND a.TglPelunasan >= '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS giro, 
            CASE WHEN b.TipeDokumen = 'Retur' AND a.TglPelunasan >= '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS retur, 
            CASE WHEN b.TipeDokumen = 'CN' AND a.TglPelunasan >= '' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS CN 
            FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.nomorfaktur = b.NoFaktur AND a.KodePelanggan = b.Pelanggan
            WHERE a.TglPelunasan <= '' AND 
             (b.Status IN ( 'Open','OpenDIH','Giro') OR (b.`Saldo` + b.`saldo_giro`) != 0)  AND 
            b.Status NOT IN ('Usulan','Batal','Reject')  AND a.Status <> 'Batal'
            AND IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) <> 0
            AND Pelanggan IN ()

            ) z ON xx.Kode = z.Pelanggan WHERE Kode IN () GROUP BY xx.Kode
ERROR - 2020-12-08 09:49:05 --> 404 Page Not Found: /index
ERROR - 2020-12-08 09:49:06 --> 404 Page Not Found: /index
ERROR - 2020-12-08 11:17:22 --> 404 Page Not Found: /index
ERROR - 2020-12-08 11:17:22 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:04:35 --> Severity: Warning --> session_destroy(): Trying to destroy uninitialized session D:\xampp70\htdocs\sstcabang\application\controllers\Auth.php 72
ERROR - 2020-12-08 13:04:36 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:04:46 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:04:46 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:05:35 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:05:35 --> 404 Page Not Found: /index
ERROR - 2020-12-08 13:05:57 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '1SELECT  xx.Kode,xx.Pelanggan, IFNULL(SUM(IFNULL(debet_awal,0)) - SUM(IFNULL(kre' at line 1 - Invalid query: 1SELECT  xx.Kode,xx.Pelanggan, IFNULL(SUM(IFNULL(debet_awal,0)) - SUM(IFNULL(kredit_awal,0)),0) AS Saldo_awal,IFNULL(SUM(debet),0) debet,
                IFNULL(SUM(cash),0)cash, IFNULL(SUM(transfer),0)transfer, IFNULL(SUM(giro),0) giro, IFNULL(SUM(retur),0) retur, IFNULL(SUM(CN),0) CN
            FROM mst_pelanggan xx                
            LEFT JOIN 
             (
            SELECT Pelanggan,NamaPelanggan,'Faktur' AS tipe,TglFaktur AS Tgl,NoFaktur,TipeDokumen,'' AS TipePelunasan, 
            CASE WHEN TglFaktur < '2020-09-01' THEN  
            IFNULL(Total,0) 
            ELSE 0 END AS debet_awal, 0 AS kredit_awal,
            CASE WHEN TglFaktur >= '2020-09-01' THEN  
            IFNULL(Total,0)
            ELSE
            0 END  AS debet, 0 AS cash , 0 AS transfer, 0 AS giro, 0 AS retur, 0 CN 
            FROM trs_faktur WHERE TglFaktur <= '2020-09-30' AND 
             -- (STATUS IN ( 'Open','OpenDIH','Giro') OR (`Saldo` + `saldo_giro`) != 0)  AND 
            STATUS NOT IN ('Usulan','Batal','Reject') 
            AND Pelanggan IN ('ASKINDINAS')

             UNION ALL
            SELECT b.`Pelanggan`,a.NamaPelanggan,'pelunasan' AS tipe, a.TglPelunasan AS Tgl,b.NoFaktur, '' AS TipeDokumen, TipePelunasan AS TipePelunasan, 0 AS debet_awal,
            CASE WHEN a.TglPelunasan < '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS kredit_awal,
            0 AS debet, 
            CASE WHEN TipePelunasan = 'Cash' AND a.TglPelunasan >= '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS cash , 
            CASE WHEN TipePelunasan = 'Transfer' AND a.TglPelunasan >= '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS transfer, 
            CASE WHEN TipePelunasan = 'Giro' AND a.TglPelunasan >= '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END AS giro, 
            CASE WHEN b.TipeDokumen = 'Retur' AND a.TglPelunasan >= '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS retur, 
            CASE WHEN b.TipeDokumen = 'CN' AND a.TglPelunasan >= '2020-09-01' THEN 
            IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) 
            ELSE 0 END
             AS CN 
            FROM trs_pelunasan_detail a LEFT JOIN trs_faktur b ON a.nomorfaktur = b.NoFaktur AND a.KodePelanggan = b.Pelanggan
            WHERE a.TglPelunasan <= '2020-09-30' AND 
            -- (b.Status IN ( 'Open','OpenDIH','Giro') OR (b.`Saldo` + b.`saldo_giro`) != 0)  AND 
            b.Status NOT IN ('Usulan','Batal','Reject')  AND a.Status <> 'Batal'
            AND IFNULL((IFNULL(a.`ValuePelunasan`,'') + IFNULL(a.value_pembulatan,'') + IFNULL(a.value_transfer,'') + IFNULL(a.materai,'')),0) <> 0
            AND Pelanggan IN ('ASKINDINAS')

            ) z ON xx.Kode = z.Pelanggan WHERE Kode IN ('ASKINDINAS') GROUP BY xx.Kode
ERROR - 2020-12-08 16:31:59 --> Severity: Warning --> session_destroy(): Trying to destroy uninitialized session D:\xampp70\htdocs\sstcabang\application\controllers\Auth.php 72
