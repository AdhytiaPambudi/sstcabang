<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
        @font-face {
            font-family: "fake-receipt";
            src: url('assets/fonts/fake_receipt.ttf');
        }

    </style>
</head>
<body>
<?php $data = ($_POST["var1"]);
$headers = $data['header'];
$details = $data['detail'];
?>
<?php foreach ($headers as $key => $header) { ?>
<?php $total_baris = 9;
	$jml_baris = 0;
?>
	<table cellpadding="3" border="0" style="font-family: Tahoma; font-size: 10px; margin-top: 20px;">
	    <tr>
	        <td colspan="5" width="350" nowrap>
	            <h5>PT. SAPTA SARITAMA</h5>
	            <span v-text="header.Cabang"></span><?php echo $header['Cabang'] ?><br>
	            <span v-text="header.AlamatCabang"><?php echo $header['Alamat'] ?></span><br>
	            Ijin: <span v-text="header.Ijin_PBF"></span><?php echo $header['Ijin_PBF'] ?> &nbsp; Telp. &nbsp;<br>
	            NPWP: <span v-text="header.NPWP"></span><?php echo $header['NPWP'] ?>&nbsp; BKAK: <span v-text="header.Ijin_Alkes"><?php echo $header['Ijin_Alkes'] ?></span>
	        </td>
	        <td colspan="2" valign="top">
	            <!-- DELIVERY ORDER (DO) -->
	            <?php
	            	if ($header['TipeDokumen'] == 'DO') {
	            		echo "<h4>FAKTUR</h4>";
	            	}else{
	            		if($header['status_approve'] == 'Approve'){
	            			echo "<h4>RETUR</h4>";
	            		}else{
	            			echo "<h4>USULAN RETUR</h4>";
	            		}
	            	}
	            ?>
	            
	        </td>
	        <td colspan="4" width="450" align="center">
	        	<?php echo $header['Pelanggan'] ?><br>
	            <span v-html="header.NamaPelanggan"></span><?php echo $header['NamaPelanggan'] ?><br>
	            <span v-html="header.AlamatKirim"><?php echo $header['AlamatKirim'] ?></span>
	        </td>
	    </tr>
	</table>
	<table>
		<thead>
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
	        <td align="center"><span v-text="NoDO"></span><?php echo $header['NoDO'] ?></td>
	        <td align="center"><span v-text="TglDO"></span><?php echo $header['TglDO'] ?></td>
	        <td align="center"><span v-text="Acu"></span><?php echo $header['Acu'] ?></td>
	        <td align="center"><span v-text="CaraBayar"></span><?php echo $header['CaraBayar'] ?></td>
	        <td align="center">
	        	<?php 
                    if($headers[0]['CaraBayar'] == "Cash"){
                        $top = 7;
                        // $j_tempo = $headers['TglFaktur'] + 7;
                        $j_tempo = date('Y-m-d', strtotime($headers[0]['TglDO']. ' + 7 days'));
                    }else if($headers[0]['CaraBayar'] == "COD"){
                        $top = 0;
                        $j_tempo = date('Y-m-d', strtotime($headers[0]['TglDO']));
                    }else{
                        $top = 21;
                        $j_tempo = date('Y-m-d', strtotime($headers[0]['TglDO']. ' + 21 days'));
                    }
                    echo $top." hari"
                ?>
	        </td>
	        <td align="center"><span v-text="Salesman"></span><?php echo $header['Salesman'] ?></td>
	        <td align="right"><span v-text="CashDiskon"></span><?php echo $header['CashDiskon'] ?></td>
	        <td align="center">FARMA</td>
	        <td align="center"><span v-text="Rayon"></span><?php echo $header['Rayon'] ?></td>
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
	    </thead>
	    <?php foreach ($details as $key => $detail) { ?>
	    	<?php if($detail['NoDO'] == $header['NoDO']) { ?>
	    	<?php $total_baris = $total_baris - 1; ?>
			    <tr v-for = "(index,datas) in data_detail" class="detail-items" style="page-break-after: always;" >
			        <td><span v-text="datas.KodeProduk"></span><?php echo $detail['KodeProduk'] ?></td>
			        <td colspan="4">
			            <span v-text='datas.NamaProduk'></span><?php echo $detail['NamaProduk'] ?>
			            <?php 
                    	if($detail['DiscCab'] > 0){
                    		echo "D".number_format($detail['DiscCab'],2)," ";
                    	}
                    	if($detail['DiscCab'] > 0 && $detail['DiscPrins1'] + $detail['DiscPrins2'] > 0){
                    		echo "/";
                    	}
                    	if($detail['DiscPrins1'] > 0 || $detail['DiscPrins2'] > 0){
                    		echo "P".number_format($detail['DiscPrins1'] + $detail['DiscPrins2'], 2)."";	
                    	}
                    	if($detail['BonusDO'] > 0){
                    		echo "B/".number_format($detail['BonusDO']);	
                    	}
                    	?>
			        </td>
			        <td align="center"><?php echo $detail['BatchNo'] ?>/<?php echo $detail['ExpDate'] ?></td>
			        <td align="right"><span v-text="parseFloat(datas.QtyDO) + parseFloat(datas.BonusDO)"><?php echo $detail['QtyDO'] + $detail['BonusDO'] ?></td>
			        <td colspan="3">
			            <table>
			                <tr>
			                    <td align="right" width="150"><span v-text="datas.Harga | currencyDisplay"><?php echo number_format($detail['Harga']) ?></td>
			                    <td align="right" width="150"><span v-text="datas.Value_detail | currencyDisplay"><?php echo number_format($detail['Value_detail']) ?></td>
			                </tr>
			            </table>
			        </td>
			    </tr>
	    	<?php } ?>	
	    <?php } ?>
			    <?php for ($i=0; $i < $total_baris; $i++) {  ?>
			    	<tr>
			    		<td>&nbsp;</td>
			    	</tr>
			    <?php } ?>	
			    <?php
			    if($total_baris < 0 ){
			    	for ($i=0; $i < 2; $i++){
			    	echo "<tr>";
			    		echo "<td>&nbsp;</td>";
			    	echo "</tr>";
			     	}
			    }
			    ?>
	    <!-- <tr v-for="i in ruangkosong">
	        <td>&nbsp;</td>
	    </tr> -->
	    <!-- <tr v-if="data_detail.length < 3"> 
	        <td colspan="6" height="40">&nbsp;</td>
	    </tr>
	    <tr v-if="data_detail.length < 5"> 
	        <td colspan="6" height="60">&nbsp;</td>
	    </tr>
	    <tr> 
	        <td colspan="6" height="10">&nbsp;</td>
	    </tr> -->
	    <tfoot>
	    <tr>
	        <td colspan="6">
	            <table width="100%" border="0">
	                <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
	                    <td align="center" style="border-right: 1px dashed;">TOTAL 1</td>
	                    <td align="center" style="border-right: 1px dashed;">POTONGAN</td>
	                    <td align="center" style="border-right: 1px dashed;">TOTAL2</td>
	                    <td align="center" style="border-right: 1px dashed;">PPN</td>
	                    <td align="center" style="border-right: 1px dashed;">B. KIRIM</td>
	                </tr>
	                <tr>
	                    <td align="right" width="100" nowrap><span v-text="Gross | currencyDisplay"></span><?php echo number_format($header['Gross']) ?></td>
	                    <td align="right" width="100" nowrap><span v-text="Potongan | currencyDisplay"></span><?php echo number_format($header['Potongan']) ?></td>
	                    <td align="right" width="100" nowrap><span v-text="Value | currencyDisplay"></span><?php echo number_format($header['Value']) ?></td>
	                    <td align="right" width="100" nowrap><span v-text="Ppn | currencyDisplay"></span><?php echo number_format($header['Ppn']) ?></td>
	                    <td align="right" width="90" nowrap><span v-text="OngkosKirim | currencyDisplay"></span><?php echo number_format($header['OngkosKirim']) ?></td>
	                </tr>
	            </table>
	        </td>
	        <td colspan="4">
	            <table width="100%" border="0">
	                <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
	                    <td width="80" align="center" style="border-right: 1px dashed;">MATERAI</td>
	                    <td width="120" align="center" style="border-right: 1px dashed;">JML. TAGIHAN</td>
	                </tr>
	                <tr>
	                    <td align="right" width="100">0</td>
	                    <td align="right"><span v-text="Total | currencyDisplay"></span><?php echo number_format($header['Total']) ?></td>
	                </tr>
	            </table>
	        </td>
	    </tr>
        <tr style="border-top: 1px dashed; border-bottom: 1px dashed;">
            <td colspan="10">TERBILANG: (RP)<span id ="terbilang_value"></span><?php echo terbilang(round($header['Total'])) ?></td>
        </tr>
        </tfoot>
	</table>
	<table width="100%">
		<tr>
            <td>
                <table width="100%" border="0">
                    <tr>
                        <td width="100" nowrap>&nbsp;</td>
                        <td valign="top">
                            Penerima
                        </td>
                        <td align="center" valign="top">
                            TGL:<br>
                            <span v-text="TimeDO"></span><?php echo $header['TimeDO'] ?><br>
                            Harap Transfer Ke <br>
                            <span v-text="Bank_Acc"></span><?php echo $header['Bank_Acc'] ?><br>
                        </td>
                        <td width="100"> &nbsp; </td>
                        <td align="center" valign="top">
                            Hormat Kami,
                            <br><br><br><br>
                            <span v-text="Nama_APJ"></span><?php echo $header['Nama_APJ'] ?><br>
                            <span v-text="SIKA_Faktur"><?php echo $header['SIKA_Faktur'] ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>
	<footer style="display: block; page-break-after:always;"></footer>
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
    ?>
</body>
</html>