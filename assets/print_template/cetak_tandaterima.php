<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>
    <?php
        $cabang = $_POST["cabang"];
        $pelanggan = $_POST["pelanggan"];
        $alamat = $_POST["alamat"];
        $max_baris = 10;
    ?>
    <table style="width: 210mm;" border="0">
        <tr>
            <td>
                <table width="100%">
                    <tr style="font-size: 14px;">
                        <td width="100">Pelanggan</td>
                        <td>: <?php echo $pelanggan ?></td>
                    </tr>
                    <tr style="font-size: 14px;">
                        <td>Alamat</td>
                        <td>: <?php echo $alamat ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" style="font-size: 20px; padding: 10px;">TANDA TERIMA</td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">Telah Terima Kwitansi / Faktur Nota dari : PT. SAPTA SARITAMA</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="0">
                    <thead class="report-header">
                    <tr style="border: solid 1px;">
                        <td width="110" align="center" style="padding: 5px; border: solid 1px;">Tanggal Dokumen</td>
                        <td width="110" align="center" style="padding: 5px; border: solid 1px;"">Nomor Dokumen</td>
                        <td width="90" align="center" style="padding: 5px; border: solid 1px;"">Nilai Tagihan</td>
                        <td align="center" style="padding: 5px; border: solid 1px;"">Keterangan</td>
                    </tr>
                    </thead>
                    <?php foreach ($data_detail as $key => $value) { ?>
                        <tr>
                            <td style="padding: 3px; border-left: solid 1px;"><?php echo $value['TglFaktur'] ?></td>
                            <td style="padding: 3px; border-left: solid 1px;""><?php echo $value['NoFaktur'] ?></td>
                            <td align="right" style="padding: 3px; border-left: solid 1px;""><?php echo number_format($value['Total'],2) ?></td>
                            <td style="border-left: solid 1px; border-right: solid 1px;">&nbsp;</td>
                        </tr>
                    <?php } ?>
                    <?php 
                        $jumlah = array_sum(array_column($data_detail,'Saldo'));
                        $sisa_baris = $max_baris - count($data_detail);
                        for ($i=0; $i < $sisa_baris; $i++) { ?>
                    <tr>
                        <td style="border-left: solid 1px;">&nbsp;</td>
                        <td style="border-left: solid 1px;">&nbsp;</td>
                        <td style="border-left: solid 1px;">&nbsp;</td>
                        <td style="border-left: solid 1px; border-right: solid 1px;">&nbsp;</td>
                    </tr> 
                        <?php } ?>
                    <tr>
                        <td style="border: solid 1px; padding: 3px;" colspan="2">J U M L A H</td>
                        <td style="border: solid 1px; padding: 3px;" align="right"><?php echo number_format($jumlah,2) ?></td>
                        <td style="border: solid 1px; padding: 3px; border-right: solid 1px;"></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="center">
                            Pembawa: <br><br><br><br><br>
                            (_____________________________)
                        </td>
                        <td align="center">
                            SST <?php echo explode("-", $cabang)[0]." ". format_tanggal(date("Y-m-d")) ?> <br><br><br><br><br>
                            (_____________________________)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <footer style="display: block; page-break-after:always;"></footer>
    <!-- ============================================================================ -->
    <?php
        function terbilang($x) {
          $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
          if ($x < 12)
            return " " . $angka[$x];
          elseif ($x < 20)
            return terbilang($x - 10) . " belas";
          elseif ($x < 100)
            return terbilang($x / 10) . " puluh" . terbilang($x % 10);
          elseif ($x < 200)
            return "seratus" . terbilang($x - 100);
          elseif ($x < 1000)
            return terbilang($x / 100) . " ratus" . terbilang($x % 100);
          elseif ($x < 2000)
            return "seribu" . terbilang($x - 1000);
          elseif ($x < 1000000)
            return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
          elseif ($x < 1000000000)
            return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
        }
        
        function format_tanggal($tanggal, $cetak_hari = false){
            $hari = array ( 1 =>    'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
            );
            
            $bulan = array (1 =>    'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember'
                    );
            $split    = explode('-', $tanggal);
            $tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
            
            if ($cetak_hari) {
                $num = date('N', strtotime($tanggal));
                return $hari[$num] . ', ' . $tgl_indo;
            }
            return $tgl_indo;
        }
    ?>
    <script type="text/javascript">
         //    var tinggi = document.getElementById('badan').offsetHeight;
            // console.log(tinggi);
         //    if(tinggi>0){
            //     var tinggi_kosong = 0;
            //     if(tinggi<250){
            //      tinggi_kosong =  250 - tinggi;
            //      // console.log(tinggi_kosong);
            //         document.getElementById('kosong').style.height = tinggi_kosong+"px";
            //     }
            //     if(tinggi>250 && tinggi<450){
            //      // console.log('masuk');
            //      tinggi_kosong =  420 - tinggi;
            //      document.getElementById('kosong').style.height = tinggi_kosong+"px";
            //     }
            //     // var tinggi_kosong = 570 - tinggi;
            //     // console.log(tinggi_kosong);
            //     // if(tinggi<250){
            //     // if(tinggi_kosong>0){
            //         // document.getElementById("break").setAttribute("style", "page-break-after:always;");
            //     // }
         //    }
    </script>
</body>
</html>