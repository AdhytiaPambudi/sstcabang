<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>
        <?php
            // $t_qty = 0;
            // $t_potongan = 0;
            // $t_gross = 0;
            // $t_value = 0;
            // $t_ppn = 0;
        $total = array_sum(array_column($data_detail, 'Jumlah'))
        ?>
    <h4>PT SAPTASARITAMA * <span v-html="TipeDokumen"></span><?php echo $data_detail[0]['TipeDokumen'] ?> *</h4>
    <div class="style2" style="height: 5px;"></div>
    <table class="header" cellspacing="0">
        <tr>
            <td width="80">No. Dokumen </td>
            <td>:</td>
            <td width="170" v-html="NoFaktur" style='padding-left:2px;'><?php echo $data_detail[0]['NoFaktur'] ?></td>
            <td width="80">Kode Sales</td>
            <td>:</td>
            <td width="170"><?php echo $data_detail[0]['Salesman'] ?></td>
            <td rowspan="4" width="190">
                <?php 
                    echo '<img alt="Coding Sips" src="assets/barcode.php?text='.$data_detail[0]['NoFaktur'].'&print=true" />';
                ?>
                Kepada Yth. <span v-html="NamaPelanggan"></span> <?php echo $data_detail[0]['NamaPelanggan'] ?><br>
                <span v-html="AlamatFaktur"></span>
            </td>
        </tr>
        <tr>
            <td>Tgl. Dokumen</td>
            <td>:</td>
            <td v-html="TglFaktur | format_tanggal" style='padding-left:2px;'><?php echo format_tanggal($data_detail[0]['TglFaktur']) ?></td>
            <td>Rayon </td>
            <td>:</td>
            <td v-html="Cabang" style='padding-left:2px;'><?php echo $data_detail[0]['Cabang'] ?></td>
        </tr>
        <tr>
            <td>Lama Bayar </td>
            <td>:</td>
            <td v-text="TOP + ' hari'" style='padding-left:2px;'><?php echo $data_detail[0]['TOP']." hari" ?></td>
            <td>Tgl j. Tempo </td>
            <td>:</td>
            <td v-html="TglFaktur | j_tempo" style='padding-left:2px;'>
                <?php 
                    $j_tempo = date('Y-m-d', strtotime($data_detail[0]['TglFaktur']. ' + '.$data_detail[0]['TOP'].' days'));
                    echo format_tanggal($j_tempo);
                ?>
            </td>
        </tr>
        <tr>
            <td>No. Acuan</td>
            <td>:</td>
            <td v-html="Acu" style='padding-left:2px;'><?php echo $data_detail[0]['Acu'] ?></td>
            <td>Kode Outlet</td>
            <td>:</td>
            <td v-html="Pelanggan" style='padding-left:2px;'><?php echo $data_detail[0]['Pelanggan'] ?></td>
        </tr>
    </table>
    <div class="style1"></div>
    <table border="0" cellspacing="0" cellpadding="1">
        <tr align="center">
            <td width="80" class="atas kiri bawah">Kode</td>
            <td width="300" class="atas kiri bawah">Nama Barang</td>
            <td width="90" class="atas kiri bawah">Nilai</td>
            <td width="90" class="atas kiri bawah">S-Harga</td>
            <td width="80" class="atas kiri bawah">%Prc</td>
            <td width="100" class="atas kiri kanan bawah">Jumlah</td>
        </tr>
        <tr>
            <td colspan="6" class="kiri kanan bawah">KAMI KREDITIR REKENING SDR</td>
        </tr>
        <?php foreach ($data_detail as $key => $detail) { ?>
        <?php 
            $t_potongan = $t_potongan + $detail['Potongan_detail'];
            $t_gross = $t_gross + $detail['Gross_detail'];
            $t_value = $t_value + $detail['Value_detail'];
            $t_ppn = $t_ppn + $detail['Ppn_detail'];
        ?>
        <tr v-for="detail_data in data_detail">
            <td class="kiri"><span v-html="detail_data.KodeProduk"></span><?php echo $detail['KodeProduk'] ?></td>
            <td class="kiri"><span v-html="detail_data.Produk"><?php echo $detail['Produk'] ?></td>
            <td class="kiri" align="right"><span v-html="detail_data.Value |currencyDisplay"><?php echo number_format($detail['Total_detail']) ?></td>
            <td class="kiri" align="right"><span v-html="detail_data.DasarPerhitungan | currencyDisplay"><?php echo number_format($detail['DasarPerhitungan']) ?></td>
            <td class="kiri" align="right"><span v-html="detail_data.Persen"><?php echo number_format($detail['Persen']) ?></td>
            <td class="kiri kanan" align="right"><span v-html="detail_data.Jumlah | currencyDisplay"><?php echo number_format($detail['Jumlah']) ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="2" align="center" class="atas bawah kiri">Total Potongan</td>
            <td colspan="4" align="center" class="atas bawah kanan kiri">Nilai Dokumen</td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="kiri bawah"><span v-html="total_Harga"><?php echo number_format($total) ?></td>
            <td colspan="4" align="center" class="kiri kanan bawah"><span v-html="total_Harga"><?php echo number_format($total) ?></td>
        </tr>
    </table>
    <br>
    <?php 
        echo "Terbilang: <i style='text-transform: capitalize;'>". terbilang($data_detail[0]['Total']*-1)." Rupiah</i>";
        // echo $data_detail[0]['Total'];
    ?> 
    <br><br>
    <table>
        <tr align="center">
            <td width="300px">
                T a n d a &nbsp; T e r i m a<br><br><br><br>
                <span style="display: block; width: 150px; border-top: solid 1px;"></span>
            </td>
            <td width="300px">
                H o r m a t &nbsp; K a m i<br><br><br><br>
                <span style="display: block; width: 150px; border-top: solid 1px;"></span>
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