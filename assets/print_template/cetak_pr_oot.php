<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>

    <table width="100%">
        <tr>
            <td valign="top"><img src="assets/logo/baru.png" width="50" height="65"></td>
            <td align="right" valign="top">
                <h5>Kepada Yth :<br>
                PT. SAPTASARITAMA PUSAT<br>
                BANDUNG</h5>
            </td>
        </tr>
    </table>
    <div style="font-weight: bold; font-size: 20px; line-height: 18px;" align="center">
        SURAT PESANAN OBAT-OBAT<br>
        TERTENTU
    </div>
    <table>
        <tr>
            <td>Harap dikirim ke Cabang :</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['Cabang'] ?></td>
        </tr>
        <tr>
            <td>Nomor PR</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['No_PR'] ?></td>
        </tr>
        <tr>
            <td>Tanggal PR</td>
            <td>:</td>
            <td><?php echo format_tanggal($data_detail[0]['Tgl_PR']) ?></td>
        </tr>
    </table>
    <table cellspacing="0">
    <thead>
        <tr class="atas" style="font-weight: bold; border-top: solid 1px;">
            <td rowspan="2" width="350  " class="kiri bawah">Produk</td>
            <td rowspan="2" width="80"class="kiri bawah" align="center">Qty</td>
            <td rowspan="2" width="100"class="kiri bawah" align="center">Satuan</td>
            <td width="60" class="kiri bawah" align="center">Harga Cabang</td>
            <td width="60" class="kiri bawah" align="center">Diskon Cabang</td>
            <td rowspan="3" width="350  "class="kiri bawah kanan">Keterangan</td>
        </tr>
    </thead>
    <tbody class="report-body">
        <?php foreach ($data_detail as $key => $datas) { ?>
        <tr v-for = "(index,datas) in data_detail" class="detail-item" style="font-size: 11px; page-break-after: always;" >
            <td class="kiri"><?php echo $datas['Nama_Produk'] ?></td>
            <td align="center"><?php echo number_format($datas['Qty_PR'],0,",",".") ?></td>
            <td align="center"><?php echo $datas['Satuan'] ?></td>
            <td align="right"><?php echo number_format($datas['Harga_Beli_Cab']) ?></td>
            <td align="right"><?php echo $datas['Disc_Cab'] ?></td>
            <td style='padding-left:2px;'><?php echo $datas['Keterangan'] ?></td>
        </tr>
    <!-- <tr class="page-break"> -->
            <?php if(count($data_detail) <= 45 ) { ?>
            <tr>
                <td style="border-top: solid 1px;"  colspan='7'>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 45 && count($data_detail) < 50 ) { ?>
            <tr>
                <td style="border-bottom: solid 1px;"  colspan='7'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 85 ) { ?>
            <tr>
                <td style="border-bottom: solid 1px;"  colspan='7'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
    </table>
    <br>
    <p style="font-size: 11px; font-weight: bold;">*Setiap pengiriman di Surat Jalan harap mencantumkan Nomor PO dan PR</p>
    <table border="0" style="line-height: 11px; font-size: 12px;" align="center">
        <tr>
            <td align="center" valign="top" width="200" nowrap>
                Penerima<br>Pesanan,
            </td>
            <td align="center" valign="top" height="80" width="300" nowrap>
                Pemesanan,<br>
                Apoteker
            </td>
            <td align="center" valign="top" height="30" width="200" nowrap>
                Mengetahui,<br>
                Business Manager
            </td>
            <td align="center" valign="top" height="30" width="200" nowrap>
                Mengetahui,<br>
                Regional Business Manager
            </td>
        </tr>
        <tr>
            <td valign="bottom" align="center">
                ____________________<br>
                Nama
            </td>
            <td valign="bottom" align="center">
                <span style="border-bottom: solid 0.2px;"><?php echo $data_detail[0]['Nama_APJ'] ?></span><br>
                SIK. No.: <?php echo $data_detail[0]['SIKA_APJ'] ?>
            </td>
            <td valign="bottom" align="center">
                ____________________<br>
                Nama
            </td>
            <td valign="bottom" align="center">
                ____________________<br>
                Nama
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
</body>
</html>