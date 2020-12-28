<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body style="font-family: tahoma;">
    <?php $data_detail = ($_POST["var1"]); ?>
        <?php
            $total_sisa = 0;
            $total_faktur = 0;
            $total_tunai = 0;
        ?>
    <font size="3" face="Tahoma" >
        
    <table border="0">
        <tr>
            <td width="270px" valign="top">
                PT. SST <span> Cabang  :</span><?php echo $data_detail[0]['Cabang'] ?>
            </td>
            <td width="270" valign="top" align="center" valign="top">
                Daftar Inkaso Harian <br>
                Tanggal :<?php echo format_tanggal($data_detail[0]['TglDIH']) ?> <br>
                No.DIH : <?php echo $data_detail[0]['NoDIH'] ?><br>
            </td>
            <td width="270" align="center">Penagih : <?php echo $data_detail[0]['Penagih']."-".$data_detail[0]['nama_penagih'] ?></td>
        </tr>
        <tr>
            <td align="left">User Buat: <span v-html="created_by"></span><?php echo $data_detail[0]['created_by'] ?></td>
            <td></td>
            <td align="right"></span></td>
        </tr>
    </table>
    </font>
    <table border="1">
        <thead class="report-header">
            <tr align="center" font-size='14px' height='50'>
                <!-- <td width="20">No.</td> -->
                <td width="90">Tgl</td>
                <td width="100">Nomor</td>
                <td width="80">Sisa Tagihan</td>
                <td width="190">Pelanggan</td>
                <!-- <td width="100">Salesman</td> -->
                <td width="80">Nilai Faktur</td>
                <td>Umur Faktur</td>
                <!-- <td width="80">Saldo DIH</td> -->
                <td width="80">Tunai</td>
                <td width="80">Giro Mundur</td>
                <td width="80">Catatan</td>
            </tr>
        </thead>
        <tbody class="report-body">
        <?php foreach ($data_detail as $key => $detail) { 
                $total_sisa = $total_sisa+$detail['Saldo'];
                $total_faktur = $total_faktur+$detail['Total'];
                if($detail['TipeDokumen'] == "Faktur"){
                    $total_tunai = $total_tunai+$detail['ValuePelunasan'];
                }else{
                    $total_tunai = $total_tunai-$detail['ValuePelunasan'];
                }
            ?>
            <tr style="font-size: 12px; page-break-after: always;height:30px" >
                <!-- <td v-html='index + 1'> {{ index }}</td> -->
                <td style='padding-left:2px;'><?php echo $detail['TglFaktur'] ?></td>
                <td style='padding-left:5px;'><?php echo $detail['NoFaktur'] ?></td>
                <td style='padding-right:2px;'  align="right"><?php echo number_format((float)$detail['Saldo'],2) ?></td>
                <td style='padding-left:2px;'><?php echo $detail['nama_pelanggan'] ?></td>
                <!-- <td v-html='datas.Salesman' style='padding-left:2px;'></td> -->
                <td style='padding-right:2px;' align='right'><?php echo number_format((float)$detail['Total'],2) ?></td>
                <td style='padding-right:2px;' align='right'>
                    <?php 
                        if(!empty($detail['UmurFaktur'])){
                            echo $detail['UmurFaktur'];
                        }else{
                            $date1=date_create($detail['TglFaktur']);
                            $date2=date_create();
                            $diff=date_diff($date1,$date2);
                            echo $diff->format("%a");
                            // echo $diff->format("%R%a Hari");
                        }

                    ?>        
                </td>
                <td style='padding-right:2px;' align="right">
                </td>
                <td></td>
                <td></td>
            </tr>
        <?php } ?>
        </tbody>
         <!-- <tr class="page-break"> -->
        <!-- <tr v-if="data_detail.length <= 10 ">
            <td style="border-bottom: solid 1px;"  colspan='11'>
                <div style="border-bottom: solid 1px;" ></div>
                <div style="page-break-before:auto"></div>

            </td>
        </tr> -->
        <!-- <tr v-if="data_detail.length > 10 && data_detail.length <= 20">
            <td style="border-bottom: solid 1px;"  colspan='11'>
                <div style="border-bottom: solid 1px;"></div>
                <div style="page-break-before:always"></div>

            </td>
        </tr>
        <tr v-if="data_detail.length > 20 && data_detail.length < 40">
            <td style="border-bottom: solid 1px;"  colspan='11'>
                <div style="border-bottom: solid 1px;"></div>
                <div style="page-break-before:auto"></div>

            </td>
        </tr> -->
        <!-- <tr v-if="data_detail.length >= 40">
            <td style="border-bottom: solid 1px;"  colspan='11'>
                <div style="border-bottom: solid 1px;"></div>
                <div style="page-break-before:always"></div>

            </td>
        </tr> -->
        <tr>
                <td colspan="2">Total</td>
                <td style='padding-right:2px;' align="right"><?php echo number_format((float)$total_sisa, 2) ?></td>
                <td></td>
                <td style='padding-right:2px;' align="right"><?php echo number_format((float)$total_faktur, 2) ?></td>
                <td></td>
                <td style='padding-right:2px;' align="right"><?php echo number_format((float)$total_tunai, 2) ?></td>
                <td></td>
                <td></td>
            </tr>
        <!-- <tfoot class="report-footer"> -->
        <!-- </tfoot> -->
    </table>
    <table cellpadding="2" style="font-size: 10px;" border="0">
        <tr style="font-size:12px;">
            <td width="300" valign="top" style="padding: 2px;">
                Telah diterima sebanyak <?php echo count($data_detail) ?> faktur tsb diatas untuk ditagih<br>
                Nilai : Rp.<?php echo number_format((float)$total_sisa,2) ?>,-
            </td>
            <td width="300" valign="top" style="padding: 2px;">
                Telah kembali <span>.....</span> faktur yang belum tertagih<br>
                Nilai : Rp. <span>.....</span>
            </td>
            <td width="300" valign="top" style="padding: 2px;">
                Telah terima hasil penagihan <span>.....</span> faktur <br>
                Tunai : RP.<span>.....</span><br>
                Jumlah Giro : <span>.....</span><br>
                Nilai : <span>.....</span>
            </td>
            <td width="300" align="right" valign="top" style="padding: 2px;">
                Telah di Cross cek
            </td>
        </tr>
    </table>
    <table border="0">
        <tr style="font-size:12px;">
            <td align="center" valign="top" width="200">
                Yang Menyetor<br>
                P E N A G I H<br><br><br>
                (_________________)
            </td>
            <td align="center" valign="top" width="200">
                Yang menerima<br>
                I N K A S O<br><br><br>
                (_________________)
            </td>
            <td align="center" valign="top" width="200">
                Kasir<br><br><br><br>
                (_________________)
            </td>
            <td align="center" valign="top" width="200">
                Penyetor<br><br><br><br>
                (_________________)
            </td>
            <td align="center" valign="top" width="200">
                Mengetahui,<br>
                B M &nbsp; & &nbsp; K S A<br><br><br>
                (_________________)
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