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
        // $pelanggan = explode("-", $_POST["pelanggan"])[1];
        $alamat = $_POST["alamat"];
        $jumlah = array_sum(array_column($data_detail,'Saldo'));
    ?>
    <table style="width: 210mm; border-collapse: separate; border-spacing: 5px;" border="0">
        <tr>
            <td align="center" colspan="3" style="padding: 20px;">
                <h4>K W I T A N S I</h4>
            </td>
        </tr>
        <tr>
            <td width="120">No.</td>
            <td>:</td>
            <td></td>
        </tr>
        <tr>
            <td>Sudah Diterima Dari</td>
            <td>:</td>
            <td><?php echo $pelanggan; ?></td>
        </tr>
        <tr>
            <td valign="top">Banyaknya Uang</td>
            <td valign="top">:</td>
            <td style="text-transform: capitalize;"><?php echo terbilang($jumlah) ?> Rupiah</td>
        </tr>
        <tr>
            <td valign="top">Untuk Pembayaran</td>
            <td valign="top">:</td>
            <td>PT. SAPTA SARITAMA <br>
                    <table>
                    <?php foreach ($data_detail as $key => $value) { ?>
                        <tr>
                            <td>Nomor Faktur</td>
                            <td>: <?php echo $value['NoFaktur'] ?>&nbsp;&nbsp;</td>
                            <td>Tanggal Faktur</td>
                            <td>: <?php echo $value['TglFaktur'] ?>&nbsp;&nbsp;</td>
                            <td>Rp. <?php echo number_format($value['Saldo']) ?></td>
                        </tr>
                    <?php } ?>
                    </table>
            </td>
        </tr>
    </table>
    <br><br><br>
    <table style="width: 210mm; border="0">
        <tr>
            <td valign="bottom" width="50%"> Jumlah Rp. <?php echo number_format($jumlah) ?> Rupiah</td>
            <td align="center" width="50%">
                SST <?php echo explode("-", $cabang)[0]." ". format_tanggal(date("Y-m-d")) ?>
                <br><br><br><br><br>
                (____________________________________)
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
            return terbilang($x - 10) . " belas ";
          elseif ($x < 100)
            return terbilang($x / 10) . " puluh " . terbilang($x % 10);
          elseif ($x < 200)
            return "seratus" . terbilang($x - 100);
          elseif ($x < 1000)
            return terbilang($x / 100) . " ratus " . terbilang($x % 100);
          elseif ($x < 2000)
            return "seribu" . terbilang($x - 1000);
          elseif ($x < 1000000)
            return terbilang($x / 1000) . " ribu " . terbilang($x % 1000);
          elseif ($x < 1000000000)
            return terbilang($x / 1000000) . " juta " . terbilang($x % 1000000);
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