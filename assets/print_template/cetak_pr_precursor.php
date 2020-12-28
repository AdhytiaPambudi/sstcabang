<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>

    <img src="assets/logo/baru.png" width="50" height="65" style="float:left;">
    <div align="center"><h4>SURAT PESANAN PRECURSOR</h4></div>
    <div align="center">
        Nomor PR: <?php echo $data_detail[0]['No_PR'] ?>  &nbsp;&nbsp;&nbsp; Nomor PO: <?php echo $data_detail[0]['No_PO'] ?>
    </div>
    <div style="clear: both;"></div>
    <br>
    <table style="font-size: 11px; line-height: 12px;">
        <tr>
            <td width="180" height="40" nowrap>Yang bertanda tangan dibawah ini</td>
            <td colspan="2">:</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['Nama_APJ'] ?></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['Alamat_APJ'] ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Apoteker Penanggung Jawab PT. Saptasaritama Cabang <?php echo $data_detail[0]['Cabang'] ?></td>
        </tr>
        <tr>
            <td>SIKA</td>
            <td>:</td>
            <td><?php echo $data_detail[0]['SIKA_Faktur'] ?></td>
        </tr>
        <tr>
            <td width="180" height="40" nowrap>Mengajukan permohonan kepada</td>
            <td colspan="2">:</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>PT. Sapta Sari Tama Pusat.</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>Jl. Caringin 254A Bandung</td>
        </tr>
        <tr>
            <td>Telp</td>
            <td>:</td>
            <td>0226026306 / 0226026310</td>
        </tr>
        <tr>
            <td colspan="3" height="40">Jenis obat yang mengandung Precursor yang dipesan adalah</td>
        </tr>
    </table>
    <table cellspacing="0">
    <thead>
        <tr class="atas" style="font-weight: bold;">
            <td rowspan="2" width="300  " class="kiri bawah">Produk</td>
            <td rowspan="2" width="80  " class="kiri bawah" align="center">Zat</td>
            <td rowspan="2" width="80"class="kiri bawah" align="center">Bentuk</td>
            <td rowspan="2" width="80"class="kiri bawah" align="center">Satuan</td>
            <td rowspan="2" width="60"class="kiri bawah" align="center">Qty</td>
            <td rowspan="2" width="100"class="kiri bawah" align="center">Terbilang</td>
            <td width="60" class="kiri bawah" align="center">Harga Cabang</td>
            <td width="60" class="kiri bawah" align="center">Diskon Cabang</td>
            <td rowspan="3" width="300  "class="kiri bawah kanan">Keterangan</td>
        </tr>
    </thead>
    <tbody class="report-body">
        <?php foreach ($data_detail as $key => $datas) { ?>
        <tr class="detail-item" style="font-size: 11px; page-break-after: always;" >
            <td valign="top" class="kiri"><?php echo $data_detail[$key]['Nama_Produk'] ?></td>
            <td class="kiri"><?php echo $data_detail[$key]['Zat'] ?></td>
            <td class="kiri"><?php echo $data_detail[$key]['Bentuk'] ?></td>
            <td valign="top" align="center" class="kiri"><?php echo $data_detail[$key]['Satuan'] ?></td>
            <td align="center" valign="top"><?php echo number_format($datas['Qty_PR'],2,",",".") ?></td>
            <td valign="top" style="font-size: 8px;"><?php echo ucfirst(terbilang($datas['Qty_PR'])) ?></td>
            <td valign="top" align="right"><?php echo number_format($datas['Harga_Beli_Cab']) ?></td>
            <td valign="top" align="right"><?php echo $datas['Disc_Cab'] ?></td>
            <td style='padding-left:2px;'><?php echo $data_detail[$key]['Keterangan'] ?></td>
        </tr>
    <!-- <tr class="page-break"> -->
            <?php if(count($data_detail) <= 45 ) { ?>
            <tr>
                <td style="border-top: solid 1px;"  colspan='9'>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 45 && count($data_detail) < 50 ) { ?>
            <tr>
                <td style="border-bottom: solid 1px;"  colspan='9'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
            <?php if(count($data_detail) > 85 ) { ?>
            <tr>
                <td style="border-bottom: solid 1px;"  colspan='9'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto;"></div>
                </td>
            </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
    </table>
    <br>
    <p style="font-size: 11px; font-weight: bold">*Setiap pengiriman di Surat Jalan harap mencantumkan Nomor PO dan PR</p>
    <table style="font-size: 11px; line-height: 12px;">
        <tr>
            <td width="180" height="40" nowrap>Mengajukan permohonan kepada</td>
            <td colspan="2">:</td>
        </tr>
        <tr>
            <td>Nama PBF</td>
            <td>:</td>
            <td>PT. Sapta Sari Tama Cabang</td>
        </tr>
        <tr>
            <td>Alamat PBF</td>
            <td>:</td>
            <td v-text="data_detail[0].Alamat"><?php echo $data_detail[0]['Alamat'] ?></td>
        </tr>
        <tr>
            <td>No. Ijin PBF</td>
            <td>:</td>
            <td v-text="data_detail[0].Ijin_PBF"><?php echo $data_detail[0]['Ijin_PBF'] ?></td>
        </tr>
    </table>
    <br><br>
    <table border="0" style="line-height: 11px; font-size: 11px;" align="center">
        <tr>
            <td align="center" valign="top" height="30" width="400" nowrap>
                <br>
                Mengetahui,<br>
                Branch Manager<br><br><br><br><br><br><br>
                <span style="border-top: solid 1px; display: block; width: 150px;"></span>
            </td>
            <td align="center" valign="top" height="80" width="400" nowrap>
                <span v-text="data_detail[0].Cabang"></span><?php echo $data_detail[0]['Cabang'] ?> ,<span v-text="data_detail[0].Tgl_PR | format_tanggal"></span><?php echo format_tanggal($data_detail[0]['Tgl_PR']) ?><br>
                Pemesanan,<br>
                Apoteker Penanggung Jawab<br><br><br><br><br><br><br>
                <span v-text="data_detail[0].SIKA_APJ" style="border-top: solid 1px; display: block; width: 150px; padding: 2px;">SIK. No.:</span>
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
            
            return $tgl_indo;
        }
    ?>
</body>
</html>