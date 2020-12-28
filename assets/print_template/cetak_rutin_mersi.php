<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>

    <table  border="0" cellspacing="0" >
        <tr>
            <td height="70px" width="280" valign="top" style="line-height: 12px;" nowrap>
                <img src="assets/logo/1489135837049.jpg" width="50" height="65" style="float:left;">
                <font size="1">
                PT. SAPTA SARITAMA<br>
                SARANA MENCAPAI CITA-CITA BERSAMA<br>
                Pusat : Jl. Caringin No.254A, BANDUNG 40223<br>
                Telp  : (022) 6026306 Fax (022) 6026309<br>
                Email : logistik@saptasaritama.com</font>
            </td>
            <td valign="bottom" width="170" align="center"><h4>SURAT PESANAN</h4></td>
            <td align="right" valign="top" width="280" nowrap>
                    Kepada Yth : <br>
                    <?php echo $data_detail[0]['Supplier'] ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 8px;">No. Ijin PBF / NO. PAK : <?php echo $data_detail[0]['Ijin_PBF'] ?></td>
        </tr>
    </table>
    <br>
    <table style="font-size: 11px; line-height: 12px;">
        <tr>
            <td width="140" nowrap>Harap dikirim ke cabang</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['Cabang'] ?></td>
        </tr>
        <tr>
            <td>Nomor PO</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['No_PO'] ?></td>
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
        <tr>
            <td>ID Paket</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['NoIDPaket'] ?></td>
        </tr>
        <tr>
            <td>Akhir Kontrak</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['-'] ?></td>
        </tr>
    </table>
    <table cellspacing="0">
    <thead>
        <tr class="atas" style="font-weight: bold;">
            <td width="80  " class="kiri bawah">Kode</td>
            <td width="300  " class="kiri bawah">Produk</td>
            <td width="100" class="kiri bawah" align="center">Bentuk Satuan</td>
            <td width="100" class="kiri bawah" align="center">Satuan</td>
            <td width="60" class="kiri bawah" align="center">Qty</td>
            <td width="60" class="kiri bawah" align="center">Harga Cabang</td>
            <td width="60" class="kiri bawah" align="center">Diskon Cabang</td>
            <td width="300  "class="kiri kanan bawah">Terbilang</td>
        </tr>
    </thead>
    <tbody class="report-body">
    	<?php foreach ($data_detail as $key => $datas) { ?>
        <tr class="detail-item" style="font-size: 11px; page-break-after: always;" >
            <td class="kiri"><?php echo $datas['Produk'] ?></td>
            <td class="kiri"><?php echo $datas['Nama_Produk'] ?></td>
            <td align="center"><?php echo $datas['bentuk'] ?></td>
            <td align="center"><?php echo $datas['Satuan'] ?></td>
            <td align="right"><?php echo number_format($datas['Harga_Beli_Cab']) ?></td>
            <td align="right"><?php echo $datas['Disc_Cab'] ?></td>
            <td align="center"><?php echo number_format($datas['Qty_PO'],2,",",".") ?></td>
            <td style='padding-left:2px;'><?php echo ucfirst(terbilang($datas['Qty_PO'])) ?></td>
        </tr>
    	<!-- <tr class="page-break"> -->
            <?php if(count($data_detail) <= 45 ) { ?>
       		<tr>
                <td style="border-top: solid 1px;"  colspan='8'>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 45 && count($data_detail) < 50 ) { ?>
       		<tr>
                <td style="border-bottom: solid 1px;"  colspan='8'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 85 ) { ?>
       		<tr>
                <td style="border-bottom: solid 1px;"  colspan='8'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
    </table>
    <p style="font-size: 11px;">
    Alamat Pengiriman = <?php echo $data_detail[0]['gudang_kirim'] ?><br>
    <?php echo $data_detail[0]['alamat_gudang_kirim'] ?>
    </p>
    <p style="font-size: 11px;">*Setiap pengiriman di Surat Jalan harap mencantumkan Nomor PO dan PR</p>
    <table border="0" style="line-height: 11px; font-size: 11px;" align="center">
        <tr>
            <td align="center" valign="top" width="200" nowrap>
                Penerima Pesanan,<br>
                Penanggung Jawab
            </td>
            <td align="center" valign="top" height="80" width="300" nowrap>
                Pemesanan,<br>
                Apoteker Penanggung Jawab
            </td>
            <td align="center" valign="top" height="30" width="200" nowrap>
                Mengetahui,<br>
                Penanggung Jawab
            </td>
        </tr>
        <tr style="line-height: 14px;">
            <td valign="bottom" align="center">
                ____________________<br>
                Nama
            </td>
            <td valign="bottom" align="center">
                <span style="border-bottom: solid 1px;"><?php echo $data_detail[0]['Nama_APJ'] ?></span><br>
                SIK. No.: <?php echo $data_detail[0]['SIKA_APJ'] ?>
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

        function format_tanggal($tanggal,$cetak_hari=false){
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