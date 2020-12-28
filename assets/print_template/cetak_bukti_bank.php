<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php $data_detail = ($_POST["var1"]); ?>
<?php foreach ($data_detail as $key => $value) { ?>
	<table width="793.7007874" border="0" style="border: solid 1px; border-collapse: separate; border-spacing: 5px;">
		<tr>
			<td align="center">
			<?php 
			if ($value['jenis_trans'] == 'Masuk') {
				echo "BUKTI PENERIMAAN BANK";
			}
			if ($value['jenis_trans'] == 'Keluar') {
				echo "BUKTI PENGELUARAN BANK";
			} 
			?>
			<br><br></td>
		</tr>
		<tr>
			<td align="right" style="border-bottom: solid 1px;">
				<table>
					<tr>
						<td>Tanggal</td>
						<td>:</td>
						<td><?php echo format_tanggal(date_format(date_create($value['Tanggal']),'Y-m-d')) ?></td>
					</tr>
					<tr>
						<td>No Voucher</td>
						<td>:</td>
						<td><?php echo $value['No_Voucher'] ?></td>
					</tr>
					<tr>
						<td>No Id</td>
						<td>:</td>
						<td><?php echo $value['Jurnal_ID'] ?></td>
					</tr>
					<tr>
						<td>Tipe Pembukuan</td>
						<td>:</td>
						<td><?php echo $value['Tipe_Kas'] ?></td>
					</tr>
					<tr>
						<td>Cabang</td>
						<td>:</td>
						<td><?php echo $value['Cabang'] ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td style="border-bottom: solid 1px;">
				<table>
					<tr>
						<td>Nominal</td>
						<td>:</td>
						<td><?php echo $value['Jumlah'] ?></td>
					</tr>
					<tr>
						<td>Kode Transaksi</td>
						<td>:</td>
						<td><?php echo $value['Transaksi'] ?></td>
					</tr>
					<tr>
						<td>Keterangan</td>
						<td>:</td>
						<td><?php echo $value['Keterangan'] ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table>
					<tr>
						<td width="264.566929133333" align="center">
							Kasir
							<br><br><br><br><br><br>
						</td>
						<td width="264.566929133333" align="center">
							Approval
							<br><br><br><br><br><br>
						</td>
						<td width="264.566929133333" align="center">
							Penerima
							<br><br><br><br><br><br>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<footer style="display: block; page-break-after:always;"></footer>
	<br>
<?php } ?>
</body>
</html>

<?php
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