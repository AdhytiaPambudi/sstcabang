<!DOCTYPE html>
<html>
<head>
	<title>Cetak</title>
</head>
<body>
<?php $data = ($_POST["var1"]);
	$header = $data['header'];
	$detail = $data['detail'];
	$baris = 0;
	$total_baris = 15;
	$total_baris_max = 21;
 ?>
<div style="line-height: normal; border: solid 1px; font-size: 10px;">
	<table cellpadding="3" border="0" width="100%">
		<tr>
			<?php if(substr($header['NoFaktur'],4,1) == 'C'){ ?>
				<td colspan="2" align="center" style="padding: 3px; font-weight: bold;">DN CABANG</td>
			<?php }else{ ?>
				<td colspan="2" align="center" style="padding: 3px; font-weight: bold;">DN PUSAT</td>
			<?php }?>
		</tr>
		</tr>
	    <tr>
	        <td width="450" valign="top">
	        	<div id="logo" style="width: 80px; float: left;">
	        		<img src="assets/logo/1489135837049.jpg" width="75" height="95">
	        	</div>
	        	<div style="padding-top: 0px;">
	            	<span style="font-size: 12px; font-weight: bold;">PT. SAPTA SARITAMA</span><br>
	            	<?php echo $header['Cabang'] ?><br>
	            	<?php echo $header['Alamat'] ?><br>
	        	</div>	
	            Ijin: <?php echo $header['Ijin_PBF'] ?> &nbsp; Telp. &nbsp;<br>
	            NPWP: <?php echo $header['NPWP'] ?> &nbsp; BKAK: <?php echo $header['Ijin_Alkes'] ?>
	        </td>
	        <td valign="top">
	            <table style="border-collapse: separate; border-spacing: 5px;">	
	            	<tr>
	            		<td valign="top">Kepada</td>
	            		<td valign="top">:</td>
	            		<td valign="top">
	            			PT. SAPTA SARITAMA/<?php echo $header['Kode'] ?><br>
	            			<?php echo $header['Alamat'] ?>
	            		</td>
	            	</tr>
	            	<tr>
	            		<td>NPWP</td>
	            		<td>:</td>
	            		<td><?php echo $header['NPWP'] ?></td>
	            	</tr>
	            </table>
	        </td>
	    </tr>
	    </table>
	    <table>
	    <tr style="border-top: solid 1px; border-bottom: solid 1px;">
	        <!-- <td colspan="10" height="30"></td> -->
	        <td width="160" align="center" style="border-right: solid 1px;">NO. DOC</td>
	        <td width="100" align="center" style="border-right: solid 1px;">TANGGAL</td>
	        <td width="160" align="center" style="border-right: solid 1px;"">NO. ACU</td>
	        <td width="100" align="center" style="border-right: solid 1px;">C. BAYAR</td>
	        <td width="100" align="center" style="border-right: solid 1px;">TGL. J. TEMPPO</td>
	        <td width="100" align="center" style="border-right: solid 1px;">PENJAJA</td>
	        <td width="100" align="center" style="border-right: solid 1px;">DIVISI</td>
	    </tr>
	    <tr style="height: 20px; border-bottom: solid 1px;">
	        <td align="center" style="border-right: solid 1px;" align="center"><?php echo $header['NoFaktur'] ?></td>
	        <td align="center" style="border-right: solid 1px;" align="center"><?php echo $header['TglFaktur'] ?></td>
	        <td align="center" style="border-right: solid 1px;" align="center"><?php echo $header['NoAcuDokumen'] ?></td>
	        <td align="center" style="border-right: solid 1px;" align="center"></td>
	        <td align="center" style="border-right: solid 1px;" align="center"></td>
	        <td align="center" style="border-right: solid 1px;" align="right"></td>
	        <td align="center" style="border-right: solid 1px;" align="center">FARMA</td>
	    </tr>
	    </table>
	    <table>
	    <tr style="border-bottom: solid 1px;">
	        <td width="80" align="center" style="border-right: solid 1px;" colspan="2">K. PROD</td>
	        <td width="315" align="center" style="border-right: solid 1px;" colspan="2">NAMA BARANG</td>
	        <td width="200" align="center" style="border-right: solid 1px;">HARGA</td>
	         <td width="200" align="center" style="border-right: solid 1px;" colspan="3">TOTAL</td>
	    </tr>
	    <?php foreach ($detail as $key => $datas) { ?>
	    	<?php 
	    		$baris += 1;
	    		if(count($datas['Produk'])>30){
	    			$baris += 1;
	    		}
	    	?>
	    <tr class="detail-items">
	        <td style="padding-left: 3px; border-right: solid 1px;" colspan="2"><?php echo $datas['KodeProduk'] ?></td>
	        <td style="padding-left: 3px; border-right: solid 1px;" colspan="2">
	            <?php echo $datas['Produk'] ?>
	        </td>
	        <?php if(substr($header['NoFaktur'],4,1) == 'C'){ ?>
		        <td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;"><?php echo number_format($datas['HargaBeliCab']) ?></td>
		        <td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;" colspan="3"><?php echo number_format($datas['JumlahCab']) ?></td>
		    <?php }else{ ?>
				<td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;"><?php echo number_format($datas['HargaBeliPst']) ?></td>
		        <td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;" colspan="3"><?php echo number_format($datas['JumlahPst']) ?></td>
			<?php }?>          
	    </tr>
	    <?php } ?>

	    <?php for($i=0; $i < ($total_baris-$baris); $i++){ ?>
	    <tr>
	        <td style="border-right: solid 1px;" colspan="2">&nbsp;</td>
	        <td style="border-right: solid 1px;" colspan="2">&nbsp;</td>
	        <td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;">&nbsp;</td>
	        <td align="right" width="200" style="padding-right: 3px; border-right: solid 1px;" colspan="3">&nbsp;</td>
	    </tr>
	    <?php } ?>
    </table>
    <table>
        <tr style="border-top: 1px solid; border-bottom: 1px solid;">
            <td width="110" align="center" style="border-right: 1px solid;">TOTAL 1</td>
            <td width="110" align="center" style="border-right: 1px solid;">PPH22</td>
            <td width="110" align="center" style="border-right: 1px solid;">TOTAL2</td>
            <td width="110" align="center" style="border-right: 1px solid;">PPN</td>
            <td width="110" align="center" style="border-right: 1px solid;">B. KIRIM</td>
            <td width="110" align="center" style="border-right: 1px solid;">MATERAI</td>
            <td width="110" align="center" style="border-right: 1px solid;">JML. TAGIHAN</td>
        </tr>
        <tr style="border: solid 1px;">
            <td align="right" style="border-right: 1px solid; padding-right: 2px;"><?php echo number_format($header['Value']) ?></td>
            <td align="right" style="border-right: 1px solid; padding-right: 2px;"><?php echo number_format($header['pph22']) ?></td>
            <td align="right" style="border-right: 1px solid; padding-right: 2px;"><?php echo number_format($header['Value']) ?></td>
            <td align="right" style="border-right: 1px solid; padding-right: 2px;">0</td>
            <td align="right" style="border-right: 1px solid; padding-right: 2px;"></td>
            <td align="right" style="border-right: 1px solid; padding-right: 2px;">0</td>
            <td align="right" style="padding-right: 2px;"><?php echo number_format($header['Total'])  ?></td>
        </tr>
		<tr style="border-top: 1px solid; border-bottom: 1px solid;">
			<td colspan="7" style="padding: 2px;">TERBILANG: (RP)<span id ="terbilang_value"></span><?php echo terbilang($header['Total']) ?></td>
		</tr>
    </table>
    <table>
        <tr>
            <td width="200" valign="top" style="padding: 2px;">
                PENERIMA
            </td>
            <td width="200" align="center" valign="top" style="border-right: solid 1px;">
                TGL:<br>
                <?php echo $datas['TglFaktur'] ?><br>
            </td>
            <td align="center" valign="top" style="padding: 2px;">
                PENGIRIM
                <br><br><br><br>
            </td>
        </tr>
	</table>
</div>
<footer style="display: block; page-break-after:always;"></footer>
    <!-- ============================================================================ -->
    <?php
        function terbilang($nilai) {
        	$x = abs($nilai);
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
    <script type="text/javascript">
         //    var tinggi = document.getElementById('badan').offsetHeight;
	        // console.log(tinggi);
         //    if(tinggi>0){
	        //     var tinggi_kosong = 0;
	        //     if(tinggi<250){
	        //     	tinggi_kosong =	 250 - tinggi;
	        //     	// console.log(tinggi_kosong);
	        //         document.getElementById('kosong').style.height = tinggi_kosong+"px";
	        //     }
	        //     if(tinggi>250 && tinggi<450){
	        //     	// console.log('masuk');
	        //     	tinggi_kosong =	 420 - tinggi;
	        //     	document.getElementById('kosong').style.height = tinggi_kosong+"px";
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