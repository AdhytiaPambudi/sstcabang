<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2020-11-24 14:00:50 --> Severity: Warning --> session_destroy(): Trying to destroy uninitialized session D:\xampp70\htdocs\sstcabang\application\controllers\Auth.php 72
ERROR - 2020-11-24 14:03:53 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '-1' at line 24 - Invalid query: 
                            SELECT * FROM 
                            (SELECT a.`Cabang_Pengirim`,a.`Cabang_Penerima`,
                                   a.`Tgl_kirim`,'' AS 'Tgl_terima',
                                   'Relokasi Kirim' AS 'Jenis',a.`No_Relokasi`,
                                   '' AS 'No_Terima',a.`Status_kiriman`,
                                   a.`Gross`,a.`Potongan`,a.`Value`,a.`Ppn`,a.`Total`,
                                   a.`Keterangan`
                            FROM trs_relokasi_kirim_header a
                            UNION ALL
                            SELECT b.`Cabang_Pengirim`,b.`Cabang_Penerima`,
                                   '' AS Tgl_kirim,b.`Tgl_terima` AS 'Tgl_terima',
                                   'Relokasi Terima' AS 'Jenis',
                                   b.`No_Relokasi` AS 'No_Relokasi',
                                   b.`No_Terima` AS 'No_Terima',
                                   b.`Status_kiriman`,
                                   b.`Gross`,b.`Potongan`,b.`Value`,b.`Ppn`,b.`Total`,
                                   '' AS 'Keterangan'
                            FROM trs_relokasi_terima_header b ) relokasi
                            WHERE (CASE WHEN Jenis = 'Relokasi Terima' THEN 
                                   Tgl_terima between '2020-10-01' and '2020-11-24' ELSE
                                   Tgl_kirim between '2020-10-01' and '2020-11-24' END )
                                   
                            ORDER BY Tgl_terima DESC,No_Terima ASC, 
                                   Tgl_kirim DESC,No_Relokasi ASC  LIMIT 0, -1
ERROR - 2020-11-24 14:06:16 --> 404 Page Not Found: /index
ERROR - 2020-11-24 14:06:16 --> 404 Page Not Found: /index
ERROR - 2020-11-24 14:06:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '-1' at line 24 - Invalid query: 
                            SELECT * FROM 
                            (SELECT a.`Cabang_Pengirim`,a.`Cabang_Penerima`,
                                   a.`Tgl_kirim`,'' AS 'Tgl_terima',
                                   'Relokasi Kirim' AS 'Jenis',a.`No_Relokasi`,
                                   '' AS 'No_Terima',a.`Status_kiriman`,
                                   a.`Gross`,a.`Potongan`,a.`Value`,a.`Ppn`,a.`Total`,
                                   a.`Keterangan`
                            FROM trs_relokasi_kirim_header a
                            UNION ALL
                            SELECT b.`Cabang_Pengirim`,b.`Cabang_Penerima`,
                                   '' AS Tgl_kirim,b.`Tgl_terima` AS 'Tgl_terima',
                                   'Relokasi Terima' AS 'Jenis',
                                   b.`No_Relokasi` AS 'No_Relokasi',
                                   b.`No_Terima` AS 'No_Terima',
                                   b.`Status_kiriman`,
                                   b.`Gross`,b.`Potongan`,b.`Value`,b.`Ppn`,b.`Total`,
                                   '' AS 'Keterangan'
                            FROM trs_relokasi_terima_header b ) relokasi
                            WHERE (CASE WHEN Jenis = 'Relokasi Terima' THEN 
                                   Tgl_terima between '2020-08-01' and '2020-11-24' ELSE
                                   Tgl_kirim between '2020-08-01' and '2020-11-24' END )
                                   
                            ORDER BY Tgl_terima DESC,No_Terima ASC, 
                                   Tgl_kirim DESC,No_Relokasi ASC  LIMIT 0, -1
ERROR - 2020-11-24 14:08:30 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 24 - Invalid query: 
                            SELECT * FROM 
                            (SELECT a.`Cabang_Pengirim`,a.`Cabang_Penerima`,
                                   a.`Tgl_kirim`,'' AS 'Tgl_terima',
                                   'Relokasi Kirim' AS 'Jenis',a.`No_Relokasi`,
                                   '' AS 'No_Terima',a.`Status_kiriman`,
                                   a.`Gross`,a.`Potongan`,a.`Value`,a.`Ppn`,a.`Total`,
                                   a.`Keterangan`
                            FROM trs_relokasi_kirim_header a
                            UNION ALL
                            SELECT b.`Cabang_Pengirim`,b.`Cabang_Penerima`,
                                   '' AS Tgl_kirim,b.`Tgl_terima` AS 'Tgl_terima',
                                   'Relokasi Terima' AS 'Jenis',
                                   b.`No_Relokasi` AS 'No_Relokasi',
                                   b.`No_Terima` AS 'No_Terima',
                                   b.`Status_kiriman`,
                                   b.`Gross`,b.`Potongan`,b.`Value`,b.`Ppn`,b.`Total`,
                                   '' AS 'Keterangan'
                            FROM trs_relokasi_terima_header b ) relokasi
                            WHERE (CASE WHEN Jenis = 'Relokasi Terima' THEN 
                                   Tgl_terima between '2020-08-01' and '2020-11-24' ELSE
                                   Tgl_kirim between '2020-08-01' and '2020-11-24' END )
                                   
                            ORDER BY Tgl_terima DESC,No_Terima ASC, 
                                   Tgl_kirim DESC,No_Relokasi ASC  LIMIT 0, 
ERROR - 2020-11-24 14:26:42 --> 404 Page Not Found: /index
ERROR - 2020-11-24 14:26:42 --> 404 Page Not Found: /index
ERROR - 2020-11-24 14:27:41 --> Severity: error --> Exception: syntax error, unexpected ',', expecting ')' D:\xampp70\htdocs\sstcabang\application\controllers\laporan\CLaporan.php 3929
ERROR - 2020-11-24 14:28:00 --> Severity: error --> Exception: syntax error, unexpected ',', expecting ')' D:\xampp70\htdocs\sstcabang\application\controllers\laporan\CLaporan.php 3929
ERROR - 2020-11-24 14:32:44 --> 404 Page Not Found: /index
ERROR - 2020-11-24 14:32:45 --> 404 Page Not Found: /index
