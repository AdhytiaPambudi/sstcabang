<style type="text/css">
	table tr td {
		padding: 3px;
	}
</style>
<?php
	$data_detail = json_decode($_POST["var1"]);
	$cabang = $_POST["cabang"];
    $pelanggan = $_POST["pelanggan"];
    $alamat = $_POST["alamat"];
    $total = array_sum(array_column($data_detail, 'Total'));
    $pembayaran = array_sum(array_column($data_detail, 'pelunasan'));
    $nilai_giro = array_sum(array_column($data_detail, 'value_giro'));
    $saldo = array_sum(array_column($data_detail, 'Saldo'));
?>
<!-- 1811111004711 -->
<!-- 1811121000042 -->
<pre>
Kepada
Yth. Bapak/Ibu Pimpinan
<?php
    echo $pelanggan = $_POST["pelanggan"]."<br>";
    echo $alamat = $_POST["alamat"];
?>

Hal : Konfirmasi Piutang Dagang
per <?php echo format_tanggal(date("Y-m-d")) ?>

Dengan Hormat,

Menurut Catatan kami per <?php echo format_tanggal(date("Y-m-d")) ?> 
Saldo tagihan kami ada adalah sebesar RP <?php echo number_format($saldo) ?>

Dimana sesuai dengan sistem pembukuan kami, jumlah tersebut
belum dipotong dengan giro mundur yang belum jatuh tempo per <?php echo format_tanggal(date("Y-m-d")) ?>.

Terlampir adalah perincian mengenai Faktur (kode = "F"), Retur (Kode = "R")
dan giro mundur yang kami terima sampai dengan Tanggal <?php echo format_tanggal(date("Y-m-d")) ?>.

Faktur Tertanggal sampai <?php echo format_tanggal(date("Y-m-d")) ?> adalah faktur yang belum di
selesaikan, bersama ini kami mohon agar dapat diselesaikan.

Kami mohon juga, agar Bapak/Ibu mencocokan daftar piutang dagang terlampir,
bila ada yang tidak cocok agar memberikan penjelasan pada kami.

Atas kerjasama Bapak/Ibu, kami ucapkan banyak terima kasih.





Hormat Kami,




(___________)
</pre>
<footer style="display: block; page-break-after:always;"></footer>
<center>
<p style="font-size: 24px;">PT. SAPTA SARI TAMA</p>
DAFTAR PIUTANG DAGANG<br>
<?php echo $pelanggan ?><br>
<?php echo $alamat ?><br>
<?php echo format_tanggal(date("Y-m-d")) ?><br>
-------------------------------<br>
DAFTAR PIUTANG DAGANG<br><br>
</center>
<table border="1" width="100%" style="font-size: 10px;">
	<thead>
		<tr>
			<td>NOMOR DOK.</td>
			<td>TANGGAL</td>
			<td>TJ. TEMPO</td>
			<td>NILAI DOK.</td>
			<td>PEMBAYARAN</td>
			<td>SISA TAGIHAN</td>
			<td>NOMOR GIRO</td>
			<td>TJT. GIRO</td>
			<td>NILAI GIRO</td>
			<td>LAMA</td>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($data_detail as $key => $value) { ?>
		<tr>
			<td><?php echo ($key+1)." .".$value->TipeDokumen."-".$value->NoFaktur ?></td>
			<td><?php echo $value->TglFaktur ?></td>
			<td><?php echo $value->TglJtoFaktur ?></td>
			<td align="right"><?php echo number_format($value->Total) ?></td>
			<td align="right"><?php echo number_format($value->pelunasan) ?></td>
			<td align="right"><?php echo number_format($value->Saldo) ?></td>
			<td><?php echo $value->Giro ?></td>
			<td><?php echo $value->tglJTO ?></td>
			<td align="right"><?php echo number_format($value->value_giro) ?></td>
			<td><?php echo $value->Umur_faktur ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

Total<br>
Nilai Dokumen = <?php echo number_format($total) ?><br>
Pembayaran = <?php echo number_format($pembayaran) ?><br>
Sisa Tagihan = <?php echo number_format($saldo) ?><br>
Nilai Giro = Sisa Tagihan = <?php echo number_format($nilai_giro) ?><br>
Menyetujui,<br><br><br><br>
(_________________)<br>
Mohon dikembalikan kepada PT Sapta Sari Tama cabang Bandung<br>


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