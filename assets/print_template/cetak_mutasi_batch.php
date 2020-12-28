<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_detail = ($_POST["var1"]); ?>
<table border="2" width="790">
	<tr>
		<td colspan="10" align="center"><h4><b>BUKTI KOREKSI BATCH</b></h4></td>
	</tr>
	<tr>
		<td colspan="5" valign="top" style="font-size: 13px;" width="400" nowrap style="padding: 15px;">
			<img src="assets/logo/1489135837049.jpg" width="75" height="100" align="left" alt="Logo Sapta" style="margin: 5px;">
			<p style="font-weight: bold;">PT. SAPTA SARITAMA/<?php echo $data_detail[0]['cabang'] ?></p>
			<p>
				<?php echo $data_detail[0]['Alamat']."<br>";
				echo "Kota ".$data_detail[0]['cabang'] ?>
			</p>
		</td>
		<td colspan="5" width="300" valign="top" nowrap style="padding: 5px;">
			<b>Keterangan :</b> <br>
			<?php echo $data_detail[0]['keterangan'] ?>
		</td>
	</tr>
	<tr>
		<th colspan="10">NO. DOK : <?php echo $data_detail[0]['nodokumen'] ?></th>
	</tr>
	<tr>
		<th rowspan="2" style="text-align: center;">K.PROD</th>
		<th rowspan="2" style="text-align: center;">NAMA BARANG</th>
		<th rowspan="2" style="text-align: center;">STN</th>
		<th rowspan="2" style="text-align: center;">GUDANG</th>
		<th rowspan="2" style="text-align: center;">QTY<br>MUTASI</th>
		<th colspan="2" style="text-align: center;">AWAL</th>
		<th colspan="2" style="text-align: center;">AKHIR</th>
	</tr>
	<tr>
		<th style="text-align: center;">No.Batch</th>
		<th style="text-align: center;">Tgl Exp</th>
		<th style="text-align: center;">No.Batch</th>
		<th style="text-align: center;">Tgl Exp</th>
	</tr>
	<?php foreach ($data_detail as $data) { ?>
	<tr>
		<td style="padding: 1px;"><?php echo $data['produk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['namaproduk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['satuan'] ?></td>
		<td align="center" style="padding: 1px;"><?php echo $data['gudang'] ?></td>
		<td style="padding: 1px;" align="center"><?php echo $data['qty'] ?></td>
		<td style="padding: 1px;"><?php echo $data['batchno_awal'] ?></td>
		<td style="padding: 1px;"><?php echo $data['expdate_awal'] ?></td>
		<td style="padding: 1px;"><?php echo $data['batchno_akhir'] ?></td>
		<td style="padding: 1px;"><?php echo $data['expdate_akhir'] ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2" style="padding: 1px;">Total 1</td>
		<td colspan="8" style="padding: 1px;"><?php echo array_sum(array_column($data_detail,'subtotal')); ?></td>
	</tr>
	<tr>
		<td colspan="10" style="padding: 1px;">Terbilang: <?php echo terbilang(array_sum(array_column($data_detail,'subtotal'))) ?></td>
	</tr>
	<tr>
		<td colspan="5" align="center" valign="top" style="padding: 1px;">
			<b>PEMBUAT</b><br><br><br><br><br>
			<?php echo format_tanggal(date('Y-m-d')) ?>
			/ ______________________
		</td>
		<td colspan="5" align="center" valign="top" style="padding: 1px;">
			<b>PENERIMA</b><br><br><br><br><br>
			_________________________
		</td>
	</tr>
</table>

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