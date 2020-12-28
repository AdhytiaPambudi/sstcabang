<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_detail = ($_POST["var1"]); ?>
<table border="2" width="790">
	<tr>
		<td colspan="7" align="center"><h4><b>BUKTI KOREKSI BARANG</b></h4></td>
	</tr>
	<tr>
		<td colspan="4" valign="top" style="font-size: 13px;" width="400" nowrap style="padding: 15px;">
			<img src="assets/logo/1489135837049.jpg" width="75" height="100" align="left" alt="Logo Sapta" style="margin: 5px;">
			<p style="font-weight: bold;">PT. SAPTA SARITAMA/<?php echo $data_detail[0]['cabang'] ?></p>
			<p>
				<?php echo $data_detail[0]['Alamat']."<br>";
				echo "Kota ".$data_detail[0]['cabang'] ?>
			</p>
		</td>
		<td colspan="3" width="300" valign="top" nowrap style="padding: 5px;">
			<b>Keterangan :</b> <br>
			<?php echo $data_detail[0]['catatan'] ?>
		</td>
	</tr>
	<tr>
		<th colspan="7">NO. DOK : <?php echo $data_detail[0]['no_koreksi'] ?></th>
	</tr>
	<tr>
		<th style="text-align: center;">K.PROD</th>
		<th style="text-align: center;">NAMA BARANG</th>
		<th style="text-align: center;">STN</th>
		<th style="text-align: center;">TRM</th>
		<th style="text-align: center;">NO. BATCH</th>
		<th style="text-align: center;">HARGA</th>
		<th style="text-align: center;">Sub Total</th>
	</tr>
	<?php foreach ($data_detail as $data) { ?>
	<tr>
		<td style="padding: 1px;"><?php echo $data['produk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['nama_produk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['satuan'] ?></td>
		<td style="padding: 1px;"><?php echo $data['qty'] ?></td>
		<td style="padding: 1px;"><?php echo $data['batch'] ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['harga']) ?></td>
		<td align="right" style="padding: 1px;"><?php echo number_format($data['subtotal']) ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2">Total 1</td>
		<td colspan="5"><?php echo array_sum(array_column($data_detail,'subtotal')); ?></td>
	</tr>
	<tr>
		<td colspan="7">Terbilang: <?php echo terbilang(array_sum(array_column($data_detail,'subtotal'))) ?></td>
	</tr>
	<tr>
		<td colspan="4" align="center" valign="top">
			<b>PEMBUAT</b><br><br><br><br><br>
			<?php echo format_tanggal(date('Y-m-d')) ?>
			/ ______________________
		</td>
		<td colspan="3" align="center" valign="top">
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