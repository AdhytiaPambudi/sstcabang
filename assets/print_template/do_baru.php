<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!-- <script src="assets/js/jquery.min.js"></script> -->
	<!-- <script src="assets/js/qrcode.js"></script> -->
    <!-- <script src="assets/js/jquery-barcode.min.js"></script> -->
    <!-- <style type="text/css">
        @font-face {
            font-family: "fake-receipt";
            src: url('/assets/fonts/fake_receipt.ttf');
        }

    </style> -->
</head>
<body>
<?php $data = ($_POST["var1"]);
$headers = $data['header'];
$details = $data['detail'];
?>
<?php foreach ($headers as $key => $header) { ?>
<?php $total_baris=15; $nomor=0; ?>
    <!-- <table border="0" cellspacing="0" bgcolor="white" style="font-family: 'fake-receipt'"> -->
	<table border="0" cellspacing="0" bgcolor="white" style="font-size: 9px;">
                <tr>
                    <td valign="top">
                        <div id="logo"><img src="assets/logo/1489135837049.jpg"></div>
                    </td>
                    <td width="250px" valign="top" style="font-size: 12px;">
                        PT. SAPTA SARITAMA<br>
                        Jl. Caringin No. 254 A BABAKAN,<br>BANDUNG
                    </td>
                    <td rowspan="2" valign="top">
                        <table cellspacing="0" border="0">
                            <tr>
                                <td width="100">No.DO </td>
                                <td>:</td>
                                <td><?php echo $header['NoDo'] ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal </td>
                                <td>:</td>
                                <td width="100px" nowrap v-html="TglDO"><?php echo format_tanggal($header['TglDO']).format_tanggal($header['TglFaktur']) ?></td>
                            </tr>
                            <tr>
                                <td>Cabang </td>
                                <td>:</td>
                                <td width="100px" nowrap v-html="Cabang"><?php echo $header['Cabang'] ?></td>
                            </tr>
                            <tr>
                                <td>Pengirim </td>
                                <td>:</td>
                                <td width="100px" nowrap v-html="NamaPengirim"><?php echo $header['NamaPengirim'] ?></td>
                            </tr>
                            <tr>
                                <td>Tipe Pelanggan </td>
                                <td>:</td>
                                <td width="100px" nowrap v-html="TipePelanggan"><?php echo $header['TipePelanggan'] ?></td>
                                
                            </tr>
                            <tr>
                                <td>Cara Bayar </td>
                                <td>:</td>
                                <td width="100px" nowrap v-html="CaraBayar"><?php echo $header['CaraBayar'] ?></td>
                                
                            </tr>
                            <tr>
                                <td>TOP </td>
                                <td>:</td>
                                <td width="100px" nowrap>
                                <?php 
                                    if($header[0]['CaraBayar'] == "Cash"){
                                        $top = 7;
                                        // $j_tempo = $data_detail[0]['TglFaktur'] + 7;
                                        $j_tempo = date('Y-m-d', strtotime($header[0]['TglDO']. ' + 7 days'));
                                    }else{
                                        $top = 21;
                                        $j_tempo = date('Y-m-d', strtotime($header[0]['TglDO']. ' + 21 days'));
                                    }
                                    echo $top." hari"
                                 ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tgl Jatuh Tempo</td>
                                <td>:</td>
                                <td width="100" nowrap><?php echo format_tanggal($j_tempo) ?></td>
                                
                            </tr>
                        </table>
                    </td>
                    <td rowspan="2" valign="top" align="center" rowspan="2">
                        <H5 v-text="salinan"></H5>
                        <H4>Delivery Order</H4>
                        <?php 
                        echo '<img alt="Coding Sips" src="assets/barcode.php?text='.$header['NoDo'].'&print=true" />';
                        ?>
                    </td>
                    <!-- <td valign="top" width="150px">Barcode</td> -->
                </tr>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>Pelanggan</td>
                                <td>: <?php echo $header['Pelanggan'] ?> - <?php echo $header['NamaPelanggan'] ?></td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>: <?php echo $header['AlamatKirim'] ?><?php echo $header['AlamatFaktur'] ?></td>
                            </tr>
                            <tr>
                                <td>NPWP</td>
                                <td>: <span v-html='NPWPPelanggan'><?php echo $header['NPWPPelanggan'] ?></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>
            </table>
            
            <table cellspacing="0" class="report-container" style="font-size: 9px;">
            <thead class="report-header">
                <tr class="detail atas" align="center">
                    <td rowspan="2" width="20" class="awal">No.</td>
                    <td rowspan="2" width="80">ID PRODUK</td>
                    <td rowspan="2" width="220">NAMA PRODUK</td>
                    <td colspan="5">DATA</td>
                    <td colspan="2" width="80">BONUS</td>
                    <td rowspan="2" width="40">DISC(%)</td>
                    <td rowspan="2" width="80">GROSS</td>
                    <td rowspan="2" width="80">POTONGAN</td>
                    <td rowspan="2" width="80">VALUE</td>
                </tr>
                <tr class="detail" align="center">
                    <td width="40">Qty</td>
                    <td width="30">Satuan</td>
                    <td width="30">Batch</td>
                    <td width="70">Exp Date</td>
                    <td width="50">Harga</td>
                    <td width="30">Qty</td>
                    <td width="30">Value</td>
                </tr>
            </thead>
            <tbody class="report-body">
           	<?php foreach ($details as $key => $detail) { ?>
	    	<?php if($detail['NoDO'] == $header['NoDO']) { ?>
	    	<?php $total_baris = $total_baris - 1; $nomor = $nomor + 1 ?>
                <tr class="detail-item"page-break-after: always;" >
                    <td v-html='index + 1' class='awal' style="text-align: center"> <?php echo $nomor ?></td>
                    <td v-html='datas.KodeProduk' style='padding-left:5px;'><?php echo $detail['KodeProduk'] ?></td>
                    <td style='padding-left:2px;'>
                        <span v-text='datas.NamaProduk'></span><?php echo $detail['NamaProduk'] ?>
	                    	<?php 
	                    	if($detail['DiscCab'] > 0){
	                    		echo "D/".number_format($detail['DiscCab'],s);
	                    	}
	                    	if($detail['ValueDiscPrins1'] > 0){
	                    		echo "P/".number_format($detail['DiscPrins1'] + $detail['DiscPrins1'],2);	
	                    	}
                            if($detail['BonusFaktur'] > 0){
                                echo "/ B".$detail['BonusFaktur'];
                            }
	                    	?>
                    </td>
                    <td style='padding-right:5px;' align='center'> </span><?php echo $detail['QtyFaktur'] + $detail['BonusFaktur'] ?></td>
                    <td style='padding-left:5px;'><?php echo $detail['UOM'] ?></td>
                    <td style='padding-left:5px;'><?php echo $detail['BatchNo'] ?></td>
                    <td style='padding-left:5px;' align="center">
                        <?php $x = date_format(date_create($detail['ExpDate']),"Y-m-d");
                        echo str_replace("-",".",$x) ?>
                    </td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['Harga']) ?></td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['Bonus']) ?></td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['ValueBonus']) ?></td>
                    <!-- <td style='padding-right:5px;' align="right"><?php echo number_format($detail['ValueDiscCab']) ?></td> -->
                    <td style='padding-right:5px;' align="right"><?php echo (double)$detail['DiscCab']+(double)$detail['DiscPrins1']+(double)$detail['DiscPrins2'] ?></td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['Gross_detail']) ?></td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['Potongan_detail']) ?></td>
                    <td style='padding-right:5px;' align="right"><?php echo number_format($detail['Value_detail']) ?></td>
                </tr>
            <?php } ?>	
	    	<?php } ?>
	    	<?php for ($i=0; $i < $total_baris; $i++) {  ?>
		    	<tr>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px;">&nbsp;</td>
		    		<td style="border-left: solid 1px; border-right: solid 1px;">&nbsp;</td>
		    	</tr>
		    <?php } ?>
            </tbody>
             <!-- <tr class="page-break"> -->
            <!-- <tr v-if="data_detail.length <= 10 ">
                <td style="border-bottom: solid 1px;"  colspan='11'>
                    <div style="border-bottom: solid 1px;" ></div>
                    <div style="page-break-before:auto"></div>

                </td>
            </tr>
            <tr v-if="data_detail.length > 10 && data_detail.length <= 20">
                <td style="border-bottom: solid 1px;"  colspan='11'>
                    <div style="border-bottom: solid 1px;"></div>
                    <div style="page-break-before:always"></div>

                </td>
            </tr>
            <tr v-if="data_detail.length > 20 && data_detail.length < 35">
                <td style="border-bottom: solid 1px;"  colspan='11'>
                    <div style="border-bottom: solid 1px;"></div>
                    <div style="page-break-before:auto"></div>

                </td>
            </tr>
            <tr v-if="data_detail.length >= 35">
                <td style="border-bottom: solid 1px;"  colspan='11'>
                    <div style="border-bottom: solid 1px;"></div>
                    <div style="page-break-before:always"></div>

                </td>
            </tr> -->
            <!-- <tfoot class="report-footer"> -->
                <tr class="detail atas">
                    <td class='awal' colspan="3" valign="top">Jumlah</td>
                    <td align='right' v-html='total_qty | currencyDisplay' style='padding-right:2px;'></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td align='right' v-html='total_Harga | currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='total_Bonus | currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='total_ValueBonus | currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='total_ValueDiscCab + total_ValueDiscPrins1 + total_ValueDiscPrins2| currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='Gross | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['Gross']) ?></td>
                    <td align='right' v-html='Potongan | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['Potongan']) ?></td>
                    <td align='right' v-html='Value | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['Value']) ?></td>
                </tr>
                <tr class="detail atas">
                    <td style='padding-left:5px;' colspan="11" rowspan='2' valign="top" class="awal">Terbilang : <br> <span id ="terbilang_value"></span><?php echo terbilang($header['Total']) ?></td>
                    <td colspan="2" style="padding-left: 2px;">Ongkir</td>
                    <td align='right' v-html='OngkosKirim | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['OngkosKirim']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="2" style="padding-left: 2px;">Materai</td>
                    <td align='right' v-html='Materai | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['Materai']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="3" rowspan="3" class="awal" align="center" valign="top">Penerima,<br><br><br><br></td>
                    <td colspan="4" rowspan="3"></td>
                    <td colspan="4" rowspan="3" align="center" valign="top">Pengirim,<br><br><br><br></td>
                    <td colspan="2" style="padding-left: 2px;">PPN</td>
                    <td align='right' v-html='Ppn | currencyDisplay' style='padding-right:2px;'><?php echo number_format($header['Ppn']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="2" style="padding-left: 2px;">Jumlah Tagihan</td>
                    <td align='right' style='padding-right:2px; font-weight: bold;'><?php echo number_format($header['Total']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="2" style="padding-left: 2px;">Dasar Pengenaan Pajak</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="14" style="font-size: 8px;">
                        Pembayaran harap di transfer ke 
                        <!-- rekening {....................} BCA cabang {---------------------} A/C : {-------------}; -->
                        <span v-text="Bank_Acc"></span><?php echo $header['Bank_Acc'] ?>.&nbsp;
                        Pembayaran dengan cek dan giro dianggap sah setelah diuangkan <br>
                        * Sesuai PER. DJP /16/PJ/2014 e-faktur Pajak tidak perlu di cek hard copy. Jika diperlukan e-faktur Pajak dapat
                        dicetak sendiri dengan memindai kode QRCODE dikiri bawah dokumen ini.
                    </td>
                </tr>
            <!-- </tfoot> -->
            </table>
            <!-- <table width="100%">
                <tr>
                    <td width="250" align="center">
                        <br>
                        Penerima, <br><br><br><br><br><br>
                        <span style="border-top: solid 1px; width: 200px; display: block;"></span>
                    </td>
                    <td width="250" align="center">
                        Hormat Kami,<br>
                        Pengirim, <br><br><br><br><br>
                        <span v-html="Nama_APJ"></span>
                        <span style="border-top: solid 1px; width: 200px; display: block;" v-text="SIKA_Faktur"></span>
                    </td>
                </tr>
            </table> -->
<?php } ?>
<?php
        function terbilang($x) {
            $status_bilangan = "";
            if($x < 0){
                $x = $x * -1;
            }
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