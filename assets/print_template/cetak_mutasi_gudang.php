<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_detail = ($_POST["var1"]); ?>
<table border="2" width="790">
	<tr>
		<td colspan="12" align="center"><h4><b>BUKTI KOREKSI GUDANG</b></h4></td>
	</tr>
	<tr>
		<td colspan="6" valign="top" style="font-size: 13px;" width="400" nowrap style="padding: 15px;">
			<img src="assets/logo/1489135837049.jpg" width="75" height="100" align="left" alt="Logo Sapta" style="margin: 5px;">
			<p style="font-weight: bold;">PT. SAPTA SARITAMA/<?php echo $data_detail[0]['cabang'] ?></p>
			<p>
				<?php echo $data_detail[0]['Alamat']."<br>";
				echo "Kota ".$data_detail[0]['cabang'] ?>
			</p>
		</td>
		<td colspan="6" width="300" valign="top" nowrap style="padding: 5px;">
			<b>Keterangan :</b> <br>
			<?php echo $data_detail[0]['keterangan'] ?>
		</td>
	</tr>
	<tr>
		<th colspan="12">NO. DOK : <?php echo $data_detail[0]['nodokumen']."<br> Tanggal : ". format_tanggal($data_detail[0]['tanggal']) ?></th>
	</tr>
	<tr>
		<th rowspan="2" style="text-align: center;">K.PROD</th>
		<th rowspan="2" style="text-align: center;">NAMA BARANG</th>
		<th rowspan="2" style="text-align: center;">JUMLAH</th>
		<th colspan="3" style="text-align: center;">GUDANG AWAL</th>
		<th colspan="3" style="text-align: center;">GUDANG AKHIR</th>
		<th rowspan="2" style="text-align: center;">KETERANGAN</th>
	</tr>
	<tr>
		<th style="text-align: center;">Gudang</th>
		<th style="text-align: center;">Batch</th>
		<th style="text-align: center;">Tgl.Expire</th>
		<!-- <th style="text-align: center;">Stok Awal</th> -->
		<th style="text-align: center;">Gudang</th>
		<th style="text-align: center;">Batch</th>
		<th style="text-align: center;">Tgl.Expire</th>
		<!-- <th style="text-align: center;">Stok Awal</th> -->
	</tr>
	<?php foreach ($data_detail as $data) { ?>
	<tr>
		<td style="padding: 1px;"><?php echo $data['produk'] ?></td>
		<td style="padding: 1px;"><?php echo $data['namaproduk'] ?></td>
		<td align="center" style="padding: 1px;"><?php echo $data['qty'] ?></td>
		<td style="padding: 1px;"><?php echo $data['gudang_awal'] ?></td>
		<td style="padding: 1px;"><?php echo $data['batchno_awal'] ?></td>
		<td style="padding: 1px;"><?php echo $data['expdate_awal'] ?></td>
		<td style="padding: 1px;"><?php echo $data['gudang_akhir'] ?></td>
		<td style="padding: 1px;"><?php echo $data['batchno_akhir'] ?></td>
		<td style="padding: 1px;"><?php echo $data['expdate_akhir'] ?></td>
		<td style="padding: 1px;"><?php echo $data['keterangan'] ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="12">
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