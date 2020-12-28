<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_detail = ($_POST["var1"]); ?>
<table border="2" width="790">
	<tr>
		<td align="center"><h4><b>SURAT JALAN</b></h4></td>
	</tr>
	<tr>
		<td valign="top" style="font-size: 13px;" width="400" nowrap style="padding: 15px;">
			<table>
				<tr>
					<td width="500" style="padding-left: 5px;">
						<img src="assets/logo/1489135837049.jpg" width="75" height="100" align="left" alt="Logo Sapta" style="margin: 5px;" hidden>
						<p style="font-weight: bold;">PT. SAPTA SARITAMA</p>
						<br>
							<?php echo "Cabang : ".$data_detail[0]['Cabang'] ?>
						<br>
							<?php echo "Pengirim : ".$data_detail[0]['Pengirim'] ?>
						<br>
							<?php echo "Tanggal Kirim : ".format_tanggal($data_detail[0]['TglKirim']) ?>
					</td>
					<td valign="top">
						<b>Keterangan :</b> <br>
						<?php echo "Status kiriman: ".$data_detail[0]['StatusKiriman'] ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<th style="padding: 2px;">NO. DOK : <?php echo $data_detail[0]['NoKiriman'] ?></th>
	</tr>
</table>
<table border="2">
	<thead class="report-header">
	<tr>
		<th width="30px" style="text-align: center;">NO.</th>
		<th width="200px" style="text-align: center;">PELANGGAN</th>
		<th width="300" style="text-align: center;">ALAMAT</th>
		<th style="text-align: center;">NO. Faktur</th>
		<th style="text-align: center;">Value Faktur</th>
		<th colspan="2" style="text-align: center;">PARAF & CAP</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($data_detail as $key => $data) { ?>
	<tr>
		<td height="40" style="padding: 1px;" align="right"><?php echo $key + 1 ?>.</td>
		<td style="padding: 1px;"><?php echo $data['KodePelanggan']." - ".$data['Pelanggan'] ?></td>
		<td style="padding: 1px;"><?php echo $data['AlamatKirim'] ?></td>
		<td style="padding: 1px;"><?php echo $data['NoDO'] ?></td>
		<td style="padding: 1px;"><?php echo $data['Total'] ?></td>
		<td width="100px" style="padding: 1px;"><?php echo fmod($key+1,2) == 1 ? ($key+1)."." : "" ?></td>
		<td width="100px" style="padding: 1px;"><?php echo fmod($key+1,2) == 0 ? ($key+1)."." : "" ?></td>
	</tr>
	<?php } ?>
	</tbody>
	<!-- <tr>
		<td colspan="6" align="center" valign="top" style="padding: 1px;">
			<table>
				<tr>
					<td>
						<b>PENGIRIM</b><br><br><br><br><br>

						_________________________
					</td>
					<td>
						<b>PENERIMA</b><br><br><br><br><br>
						_________________________
					</td>
				</tr>
			</table>
		</td>
	</tr> -->
</table>
<footer style="display: block; page-break-after:always;"></footer>
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