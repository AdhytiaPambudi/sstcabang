<!DOCTYPE html>
<html>
<head>
	<title>Cetak</title>
</head>
<body>
<?php $data = ($_POST["var1"]);
	$header = $data['header'];
	$detail = $data['detail'];
	$baris_kosong = 21;
	$baris_kosong2 = 32;
 ?>
<div style="line-height: normal;">
	<table>
		<tr>
			<td width="350">
				PT. SAPTA SARITAMA<br>
				SARANA UNTUK MENCAAI CITA-CITA BERSAMA<br>
				PEDAGANG BESAR FARMASI Izin PBF<br>
				No.HK.07.01/V/054/14<br>
				PUSAT: Jl. Caringin No. 254 A, BANDUNG<br>
	            Telp.:0226026306 / 0226026310 - FAX : 0226026306
			</td>
			<td>
				<table>
					<tr>
						<td>KEPADA TYH.</td>
						<td>:</td>
						<td><?php echo $header['Supplier'] ?></td>
					</tr>
					<tr>
						<td>DARI CABANG</td>
						<td>:</td>
						<td><?php echo $header['Cabang'] ?></td>
					</tr>
					<tr>
						<td>NO. DOKUMEN</td>
						<td>:</td>
						<td><?php echo $header['No_Usulan'] ?></td>
					</tr>
					<tr>
						<td>TANGGAL</td>
						<td>:</td>
						<td><?php echo format_tanggal($header['tanggal']) ?></td>
					</tr>
					<tr>
						<td>NO. ACU</td>
						<td>:</td>
						<td><?php echo $header['No_BPB'] ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border="0" width="600px;">
		<thead>
			<tr style="border:solid 1px;">
				<td width="80" style="border-left: solid 1px; padding: 2px">K. Prod</td>
				<td width="210" style="border-left: solid 1px padding: 2px;">Nama Produk</td>
				<td width="70" style="border-left: solid 1px; padding: 2px;">Batch</td>
				<td width="70" style="border-left: solid 1px; padding: 2px;">Expired</td>
				<td width="50" style="border-left: solid 1px; padding: 2px;">Unit</td>
				<td width="230" style="border: 1px solid; padding: 2px;" >Alasan</td>
			</tr>
		</thead>
		<tbody id="badan">
		<?php foreach ($detail as $key => $item) {
			$baris_kosong = $baris_kosong - 1;
			$baris_kosong2 = $baris_kosong2 - 1;
			if(strlen($item['Nama_Produk']) > 30){
				$baris_kosong = $baris_kosong - 1;
				$baris_kosong2 = $baris_kosong2 - 1;
			}
		 ?>
			<tr>
				<td style="border-left: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['Produk'] ?></td>
				<td style="border-left: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['Nama_Produk'] ?></td>
				<td style="border-left: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['BatchNo'] ?></td>
				<td style="border-left: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['ExpDate'] ?></td>
				<td style="border-left: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['Qty'] ?></td>
				<td style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;"><?php echo $item['Keterangan'] ?></td>
			</tr>
		<?php } ?>
		</tbody>
		<?php for($i=1; $i < $baris_kosong; $i++ ){ ?>
		<tr>
            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
            <td style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
        </tr>
        <?php } ?>
        <?php if($baris_kosong < count($detail)){
        	for($si=1; $si < $baris_kosong2; $si++ ){ ?>
				<tr>
		            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		            <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		            <td style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
		        </tr>
		    <?php } ?>
        <?php } ?>
		<tr>
			<td colspan="6" style="border-bottom: solid 1px;"></td>
		</tr>
		<tr>
			<td colspan="6">
				<table style="border-collapse: separate; border-spacing: 40px 10px;">
					<tr>
						<td align="center">Pengirim<br><br><br><br><br>(___________________)</td>
						<td align="center">Menyetujui<br><br><br><br><br>(___________________)</td>
						<td align="center">Menyetujui<br><br><br><br><br>(___________________)</td>
						<td align="center">Expedisi<br><br><br><br><br>(___________________)</td>
						<td align="center">Penerima<br><br><br><br><br>(___________________)</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="6">
				*Perhatian
				1. Supplier/Pabrik penerima barang, mohon untuk memberikan bukti penerimaan barang ke :
					cabang pengirim, dan
					PT. SAPTA SARITAMA Pusat di nomor 0226026306 / 0226026310 atau email ke logistik2@saptasaritama.co.id
				2. Bila dalam 15(lima belas) hari, tidak ada pemberitahuan dari pihak penerima, maka kami anggap barang sudah diterima di Supplier /Pabrik
				3. Bila dalam 1 bukan tidak ada penggantian barang, maka kami akan mengusulkan untuk diperhitungkan/dipotongkan di tagihan supplier.
			</td>
		</tr>
		<tfoot>
			<tr>
				<td colspan="6" align="center">
					<div>**<?php echo "No Retur: ".$header['No_Retur'] ?>**</div>
					<div style="float: right;">Tgl Cetak : <?php echo date("d-m-Y") ?></div>
					</td>
			</tr>
		</tfoot>
	</table>
</div>

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