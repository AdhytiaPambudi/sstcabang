<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
<?php $data = ($_POST["var1"]); ?>

<table cellpadding="3" border="0">
    <tr>
        <td colspan="5" width="350" nowrap>
            <h5>PT. SAPTA SARITAMA</h5>
            <?php echo $data[0]['Cabang'] ?><br>
            <?php echo $data[0]['Alamat'] ?><br>
            Ijin: <?php echo $data[0]['Ijin_PBF'] ?> &nbsp; Telp. &nbsp;<br>
            NPWP: <?php echo $data[0]['NPWPPelanggan'] ?> &nbsp; BKAK: <?php echo $data[0]['Ijin_Alkes'] ?>
        </td>
        <td valign="top">
            FAKTUR
        </td>
        <td colspan="5" width="450">
            <?php echo $data[0]['NamaPelanggan'] ?><br>
            <?php echo $data[0]['AlamatFaktur'] ?></span>
        </td>
    </tr>
    <tr style="border-top: dashed 1px; border-bottom: dashed 1px;">
        <!-- <td colspan="10" height="30"></td> -->
        <td style="border-right: dashed 1px;" align="center">K.DOC</td>
        <td align="center" style="border-right: dashed 1px;">NO. DOC</td>
        <td align="center" style="border-right: dashed 1px;">TANGGAL</td>
        <td align="center" style="border-right: dashed 1px;">NO. ACU</td>
        <td align="center" style="border-right: dashed 1px;">C. BAYAR</td>
        <td align="center" style="border-right: dashed 1px;">TGL. J. TEMPPO</td>
        <td align="center" style="border-right: dashed 1px;">PENJAJA</td>
        <td align="center" style="border-right: dashed 1px;">CASH DISC</td>
        <td align="center" style="border-right: dashed 1px;">DIVISI</td>
        <td align="center">RAYON</td>
    </tr>
    <tr>
        <td align="center">08</td>
        <td align="center"><?php echo $data[0]['NoFaktur'] ?></td>
        <td align="center"><?php echo $data[0]['TglFaktur'] ?></td>
        <td align="center"><?php echo $data[0]['Acu'] ?></td>
        <td align="center"><?php echo $data[0]['CaraBayar'] ?></td>
        <td align="center"><?php echo $data[0]['CaraBayar'] ?></td>
        <td align="center"><?php echo $data[0]['NamaSalesman'] ?></td>
        <td align="right"><?php echo number_format($data[0]['CashDiskon']) ?></td>
        <td align="center">FARMA</td>
        <td align="center"><?php echo $data[0]['Rayon'] ?></td>
    </tr>
    <tr>
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
    </tr>
    <?php foreach ($data as $key => $datas) { ?>
    <tr class="detail-items" style="page-break-after: always;" >
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
                    <td align="right" width="150"><?php echo number_format($datas['Value_detail']) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php } ?>

    <tr v-for="i in ruangkosong">
        <td>&nbsp;</td>
    </tr>
    <!-- <tr v-if="data_detail.length < 3"> 
        <td colspan="6" height="40">&nbsp;</td>
    </tr>
    <tr v-if="data_detail.length < 5"> 
        <td colspan="6" height="60">&nbsp;</td>
    </tr>
    <tr> 
        <td colspan="6" height="10">&nbsp;</td>
    </tr> -->
    <tr>
        <td colspan="7">
            <table width="100%" border="0">
                <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
                    <td align="center" style="border-right: 1px dashed;">TOTAL 1</td>
                    <td align="center" style="border-right: 1px dashed;">POTONGAN</td>
                    <td align="center" style="border-right: 1px dashed;">TOTAL2</td>
                    <td align="center" style="border-right: 1px dashed;">PPN</td>
                    <td align="center" style="border-right: 1px dashed;">B. KIRIM</td>
                </tr>
                <tr>
                    <td align="right" width="100" nowrap><?php echo number_format($datas['Gross']) ?></td>
                    <td align="right" width="100" nowrap><?php echo number_format($datas['Potongan']) ?></td>
                    <td align="right" width="100" nowrap><?php echo number_format($datas['Value']) ?></td>
                    <td align="right" width="100" nowrap><?php echo number_format($datas['Ppn']) ?></td>
                    <td align="right" width="90" nowrap><?php echo number_format($datas['OngkosKirim']) ?></td>
                </tr>
            </table>
        </td>
        <td colspan="3">
            <table width="100%" border="0">
                <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
                    <td align="center" style="border-right: 1px dashed;">MATERAI</td>
                    <td align="center" style="border-right: 1px dashed;">JML. TAGIHAN</td>
                </tr>
                <tr>
                    <td align="right" width="100" nowrap>0</td>
                    <td align="right"><?php echo number_format($datas['Total'])  ?></td>
                </tr>
            </table>
        </td>
         <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
            <td colspan="10">TERBILANG: (RP)<span id ="terbilang_value"></span><?php echo terbilang(round($datas['Total'])) ?></td>
        </tr>
        <tr>
            <td colspan="10">
                <table width="100%" border="0">
                    <tr>
                        <td width="100" nowrap>&nbsp;</td>
                        <td valign="top">
                            Penerima
                        </td>
                        <td align="center" valign="top">
                            TGL:<br>
                            <?php echo $datas['TimeDO'] ?><br>
                            Harap Transfer Ke <br>
                            <?php echo $datas['Bank_Acc'] ?><br>
                        </td>
                        <td width="100"> &nbsp; </td>
                        <td align="center" valign="top">
                            Hormat Kami,
                            <br><br><br><br>
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