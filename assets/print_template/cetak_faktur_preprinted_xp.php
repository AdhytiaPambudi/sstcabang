<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style type="text/css">
        @font-face {
            font-family: "fake-receipt";
            src: url('/assets/fonts/fake_receipt.ttf');
        }

    </style>
</head>
<body>
<?php $data = ($_POST["var1"]);
        $xt = 0;
        $jumlah_baris_data = 0;
        $jml_baris_standar_halaman1 = 21;
        $jml_baris_max_halaman1 = 21;
 ?>

<table cellpadding="3" border="0" style="margin-left: 5px; margin-right: 7px; font-family: 'fake-receipt'; line-height: 12px;">
    <tr style="height: 85px;">
        <td colspan="5" valign="top">
            <span style="display: inline-block; margin-top: 17px;"><?php echo $data[0]['Cabang'] ?></span><br>
            <?php echo $data[0]['Alamat'] ?><br>
            Ijin: <?php echo $data[0]['Ijin_PBF'] ?> &nbsp; Telp. &nbsp;<br>
        </td>
        <td colspan="5" valign="top">
            <span style="margin-left: 100px; margin-top: 10px; display: inline-block;"><?php echo $data[0]['NamaPelanggan'] ?></span><br>
            <span style="margin-left: 100px;"><?php echo $data[0]['AlamatFaktur'] ?></span><br>
            <span style="margin-left: 70px; display: inline-block; margin-top: 5px;"><?php echo $data[0]['NPWPPelanggan'] ?></span>
        </td>
    </tr>
    <tr style="height:36px;" align="top">
        <td width="88" align="center" valign="top">08</td>
        <td width="82" align="center" valign="top"><?php echo substr($data[0]['NoFaktur'], 8)  ?></td>
        <td width="85" align="center" valign="top" style="font-size: 9px;"><?php echo $data[0]['TglFaktur'] ?></td>
        <td width="85" align="center" valign="top" style="font-size: 9px;"><?php echo $data[0]['Acu'] ?></td>
        <td width="82" align="center" valign="top"><?php echo $data[0]['CaraBayar'] ?></td>
        <td width="80" align="center" valign="top" style="font-size: 9px;"><?php echo $data[0]['TglFaktur'] ?></td>
        <td width="80" align="center" valign="top"><?php echo $data[0]['NamaSalesman'] ?></td>
        <td width="80" align="right" valign="top"><?php echo number_format($data[0]['CashDiskon']) ?></td>
        <td width="80" align="center" valign="top">FARMA</td>
        <td width="80" align="center" valign="top"><?php echo $data[0]['Rayon'] ?></td>
    </tr>
    <!-- <tr>
        <td height="10"></td>
    </tr>
    <tr style="border-top: dashed 1px; border-bottom: dashed 1px;">
        <td align="center" style="border-right: dashed 1px;">K. PROD</td>
        <td align="center" colspan="4" style="border-right: dashed 1px;">NAMA BARANG</td>
        <td align="center" style="border-right: dashed 1px;">NO. BATCH</td>
        <td align="center" style="border-right: dashed 1px;">UNIT</td>
        <td colspan="3">
            <table>
                <tr>
                    <td align="center" style="border-right: dashed 1px;" width="150">HARGA</td>
                    <td align="center" width="150">TOTAL</td>
                </tr>
            </table>
        </td>
    </tr> -->
    <?php foreach ($data as $key => $datas) {
        if($datas['QtyFaktur'] + $datas['BonusFaktur'] <> 0) {
            $xt = $xt + 1;
            $jumlah_baris_data = $jumlah_baris_data + 1;
            if(strlen($datas['NamaProduk']) > 40){
                $jumlah_baris_data = $jumlah_baris_data + 1;
            } ?>
            <tr class="detail-items"">
                <td><?php echo $datas['KodeProduk'] ?></td>
                <td colspan="4">
                    <?php echo $datas['NamaProduk'] ?>
                    <?php if($datas['DiscCab'] > 0){
                        echo "D/". $data['DiscCab'];
                    }
                    if($datas['DiscPrins1']+$datas['DiscPrins2'] > 0){
                        echo "P/". $datas['DiscPrins1']+$datas['DiscPrins2'];
                    }
                    ?>
                </td>
                <td align="center"><?php echo $datas['BatchNo'] ?></td>
                <td align="right"><?php echo $datas['QtyFaktur'] + $datas['BonusFaktur'] ?></td>
                <td colspan="3">
                    <table>
                        <tr>
                            <td align="right" width="150"><?php echo number_format($datas['Harga']) ?></td>
                            <td align="right" width="145"><?php echo number_format($datas['Value_detail']) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php }
    } ?>
    <?php for($i=$jumlah_baris_data; $i < $jml_baris_standar_halaman1; $i++ ){ ?>
        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="7">
            <table width="100%" border="0">
                <!-- <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
                    <td align="center" style="border-right: 1px dashed;">TOTAL 1</td>
                    <td align="center" style="border-right: 1px dashed;">POTONGAN</td>
                    <td align="center" style="border-right: 1px dashed;">TOTAL2</td>
                    <td align="center" style="border-right: 1px dashed;">PPN</td>
                    <td align="center" style="border-right: 1px dashed;">B. KIRIM</td>
                </tr> -->
                <tr>
                    <td align="right" width="95"><?php echo number_format($datas['Gross']) ?></td>
                    <td align="right" width="104"><?php echo number_format($datas['Potongan']) ?></td>
                    <td align="right" width="105"><?php echo number_format($datas['Value']) ?></td>
                    <td align="right" width="105"><?php echo number_format($datas['Ppn']) ?></td>
                    <td align="right" width="90"><?php echo number_format($datas['OngkosKirim']) ?></td>
                </tr>
            </table>
        </td>
        <td colspan="3">
            <table width="100%" border="0">
                <!-- <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
                    <td align="center" style="border-right: 1px dashed;">MATERAI</td>
                    <td align="center" style="border-right: 1px dashed;">JML. TAGIHAN</td>
                </tr> -->
                <tr>
                    <td align="right" width="100" nowrap>0</td>
                    <td align="right"><?php echo number_format($datas['Total'])  ?></td>
                </tr>
            </table>
        </td>
         <tr style="height: 42px;">
            <td colspan="10" valign="top">
                <span style="margin-left: 140px; display: inline-block; margin-top: 12px; font-size: 9px;"></span><?php echo terbilang(round($datas['Total'])) ?></td>
        </tr>
        <tr>
            <td colspan="10">
                <table width="100%" border="0">
                    <tr>
                        <td width="100" nowrap>&nbsp;</td>
                        <td align="center" valign="top">
                            <?php echo $datas['TimeDO'] ?><br>
                            Harap Transfer Ke <br>
                            <?php echo $datas['Bank_Acc'] ?><br>
                        </td>
                        <td width="100"> &nbsp; </td>
                        <td align="center" valign="top">
                            <?php echo $datas['Nama_APJ'] ?><br>
                            <?php echo $datas['SIKA_Faktur'] ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tr>
</table>
<!-- =============================================================================================================== -->
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