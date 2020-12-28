<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<h3>Laporan Limit dan TOP</h3>
<?php 
    $data_detail = ($_POST["var1"]);
    $tipe = $_POST["tipe"];
    // print_r($data_detail);
    $value = array_sum(array_column($data_detail,'Value'));
    $total = array_sum(array_column($data_detail,'Total'));
    $limit = $total - $value
 ?>
<h5><?php echo $data_detail['Pelanggan']." - ".$data_detail['NamaPelanggan'] ?></h5>
<p><?php echo $data_detail['TglSO'] ?>&nbsp; / &nbsp; SO. : <?php echo $data_detail['NoSO'] ?></p>
<table>
    <tr>
        <td width="130">Usulan</td>
        <td width="10">:</td>
        <td><?php echo $tipe ?></td>
    </tr>
    <tr>
        <td>Acu</td>
        <td>:</td>
        <td><?php echo $data_detail['Acu'] ?></td>
    </tr>
    <tr>
        <td>Limit Kredit</td>
        <td>:</td>
        <td><?php echo number_format($data_detail['Limit_Kredit']) ?></td>
    </tr>
    <tr>
        <td>Usulan Limit</td>
        <td>:</td>
        <td><?php echo number_format($limit) ?></td>
    </tr>
    <tr>
        <td>TOP Awal</td>
        <td>:</td>
        <td><?php echo $data_detail['TOP'] ?> hari</td>
    </tr>
    <tr>
        <td>U. Buka TOP</td>
        <td>:</td>
        <td><?php echo "" ?></td>
    </tr>
    <tr>
        <td>U. TOP Baru</td>
        <td>:</td>
        <td><?php echo "" ?></td>
    </tr>
    <tr>
        <td>Janji Bayar</td>
        <td>:</td>
        <td><?php echo "" ?></td>
    </tr>
    <tr>
        <td>Alasan K. Terlabat</td>
        <td>:</td>
        <td><?php echo "" ?></td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td><?php echo "" ?></td>
    </tr>
    <tr>
        <td valign="top">Finance Manager</td>
        <td valign="top">:</td>
        <td>
            Approve / Pending / Tolak <br>
            Alasan : _______________________________<br>
            ______________________________________
        </td>
    </tr>
</table>
<br><br>
<table>
    <tr>
        <td>(___________________)</td>
    </tr>
</table>
<footer style="display: block; page-break-after:always;"></footer>
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