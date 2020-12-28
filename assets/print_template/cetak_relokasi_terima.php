<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_header = ($_POST["var1"]["header"]); ?>
<?php $data_detail = ($_POST["var1"]["detail"]); ?>
<?php $alamat = ($_POST["var1"]["alamat"]); ?>
<table border="2" width="790">
	<tr>
		<td colspan="13" align="center"><h4><b>BUKTI RELOKASI TERIMA CABANG</b></h4></td>
	</tr>
	<tr>
		<td colspan="7" valign="top" style="font-size: 13px;" width="400" nowrap style="padding: 15px;">
			<img src="assets/logo/1489135837049.jpg" width="75" height="100" align="left" alt="Logo Sapta" style="margin: 5px;">
			<p style="font-weight: bold;">PT. SAPTA SARITAMA <br><?php echo $data_header[0]['Cabang_Pengirim'] ?></p>
			<p>
				<?php 
				$key = array_search($data_header[0]['Cabang_Pengirim'], array_column($alamat, 'Cabang'));
				echo $alamat[$key]['Alamat'];
				echo "<br>";
				echo "Kota ".$alamat[$key]['Kota']."<br>Telp: ".$alamat[$key]['Telp']; ?>
			</p>
		</td>
		<td colspan="6" width="300" valign="top" nowrap style="padding: 5px;" valign="top">
			<h4><b>Relokasi Ke :</b></h4>
			<?php 
			$key2 = array_search($data_header[0]['Cabang_Penerima'], array_column($alamat, 'Cabang'));
			echo "<b>".$data_header[0]['Cabang_Penerima']."</b>";
			echo "<br>".$alamat[$key2]['Alamat'];
			echo $alamat[$key2]['Kota']."<br>Telp: ".$alamat[$key2]['Telp'];
			?>
		</td>
	</tr>
	<tr>
		<th colspan="13" height="40" valign="top">Keterangan:</th>
	</tr>
	<tr>
		<th colspan="13">NO. DOK : <?php echo $data_detail[0]['No_Relokasi']."<br> Tanggal : ". format_tanggal($data_detail[0]['Tgl_kirim']) ?></th>
	</tr>
<!-- </table> -->
<!-- <table border="2"> -->
	<tr style="font-size: 9px;">
		<th style="min-width:20px">No</th>
        <th style="min-width:80px">Produk</th>
        <th style="min-width:40px">Batch</th>
        <th style="min-width:35px">Exp Date</th>
        <th style="min-width:25px">Qty</th>
        <th style="min-width:40px">Satuan</th>
        <th style="min-width:60px">Prinsipal</th>
        <th style="min-width:40px">Bonus</th>
        <th style="min-width:40px">Disc</th>
        <th style="min-width:40px">Potongan</th>
        <th style="min-width:40px">Gross</th>
        <th style="min-width:40px">Harga</th>
        <th style="min-width:50px">Total</th>
	</tr>
	<?php foreach ($data_detail as $key => $data) { ?>
	<tr style="font-size: 9px;">
		<td style="padding: 1px;"><?php echo $key+1 ?></td>
		<td style="padding: 1px;"><?php echo $data['Produk']."-".$data['NamaProduk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['BatchNo'] ?></td>
		<td style="padding: 1px;"><?php echo $data['ExpDate'] ?></td>
		<td align="center" style="padding: 1px;"><?php echo $data['Qty'] ?></td>
		<td style="padding: 1px;"><?php echo $data['Satuan'] ?></td>
		<td style="padding: 1px;"><?php echo $data['Prinsipal'] ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Bonus']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Disc']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Potongan']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Gross']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Harga']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['Total']) ?></td>
	</tr>
	<?php } ?>
	<tr style="font-size: 9px;">
		<td colspan="12" align="right"><b>Total</b></td>
		<td align="right"><?php echo number_format($data_header[0]['Total']) ?></td>
	</tr>
	<tr>
		<td colspan="13">
			<table style="margin-top: 10px; margin-bottom: 10px;">
				<tr style="font-weight: bold;">
					<td width="200" height="90" align="center" valign="top" nowrap>Pengirim</td>
					<td width="200" height="90" align="center" valign="top" nowrap>Penanggung Jawab</td>
					<td width="200" height="90" align="center" valign="top" nowrap>Penerima</td>
					<td width="200" height="90" align="center" valign="top" nowrap>Penanggung Jawab</td>
				</tr>
				<tr>
					<td align="center">(________________________)</td>
					<td align="center">(________________________)</td>
					<td align="center">(________________________)</td>
					<td align="center">(________________________)</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>
			
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