<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php //$data_detail = ($_POST["var1"]); ?>
        <?php
        $data_detail = [];
            $t_qty = 0;
            $t_potongan = 0;
            $t_gross = 0;
            $t_value = 0;
            $t_ppn = 0;
            $xt = 0;
            $baris_kosong = 3;
            // $baris_kosong2 = 6;
            $jumlah_baris_data = 0;
            // $jml_baris_standar_halaman1 = 17;
            $jml_baris_standar_halaman1 = 12;
            $jml_baris_standar_halaman2 = 35;
            // $jml_baris_max_halaman1 = 24;
        ?>
    <!-- <table border="1" cellspacing="0" style="font-family: sans-serif; margin-left: 0px;" width="2480"> -->
    <table border="0" cellspacing="0" style="font-family: sans-serif; margin-left: 0px;">
        <tr>
            <td height="80px" width="80px" valign="top" hidden>
                <div id="logo" hidden><img src="assets/logo/1489135837049.jpg"></div>
            </td>
            <td width="300" valign="top">
                <span style="font-size: 20px;">PT. SAPTA SARITAMA</span><br>
                Jl. Caringin No. 254 A BABAKAN, BANDUNG
            </td>
            <td rowspan="2" valign="top">
                <table cellspacing="0">
                    <tr>
                        <td>No.Faktur &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="120"><?php echo $data_detail[0]['NoFaktur'] ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" style="color: #fff;"><?php echo format_tanggal($data_detail[0]['TglFaktur']) ?></td>
                    </tr>
                    <tr>
                        <td>Cabang &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100"><?php echo $data_detail[0]['Cabang'] ?></td>
                    </tr>
                    <tr>
                        <td>Tipe Pelanggan &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="TipePelanggan"></td>
                    </tr>
                    <tr>
                        <td>Cara Bayar &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="CaraBayar"><?php echo $data_detail[0]['CaraBayar'] ?></td>
                        
                    </tr>
                    <tr>
                        <td>TOP &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap>
                            
                        </td>
                    </tr>
                    <tr>
                        <td width="100" nowrap>Tgl Jatuh Tempo &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap></td>
                    </tr>
                    <tr>
                        <td width="100" nowrap>No Pajak &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="NoPajak"><?php echo $data_detail[0]['NoPajak'] ?></td>
                    </tr>
                </table>
            </td>
            <td width="250" valign="top" align="center">
                <h4>Faktur</h4>
                <div id="bcTarget"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td valign="top">Pelanggan</td>
                        <td width="300" style="font-size: 11px;">:</td>
                    </tr>
                    <tr>
                        <td valign="top">Alamat</td>
                        <td>: <?php echo $data_detail[0]['AlamatFaktur'] ?></td>
                    </tr>
                    <tr>
                        <td>NPWP</td>
                        <td>: <?php echo $data_detail[0]['NPWPPelanggan'] ?></td>
                    </tr>
                </table>
            </td>
            <td valign="bottom">
                No. DO : <?php echo $data_detail[0]['NoDO'] ?>
            </td>
        </tr>
    </table>
    <table cellspacing="0" class="report-container report-container" border="0">
        <thead class="report-header">
            <tr class="detail atas" align="center">
                <!-- <td rowspan="2" width="20" class="awal" style="border:solid 1px;">No.</td> -->
                <td rowspan="2" width="60" style="border:solid 1px;">ID</td>
                <td rowspan="2" width="190" style="border:solid 1px;">NAMA PRODUK</td>
                <td colspan="5" style="border:solid 1px;">DATA</td>
                <!-- <td rowspan="2" width="50" style="border:solid 1px;">BNS</td> -->
                <td rowspan="2" width="20" style="border:solid 1px;">DISC<br>(%)</td>
                <td rowspan="2" width="80" style="border:solid 1px;">GROSS</td>
                <td rowspan="2" width="70" style="border:solid 1px;">POT.</td>
                <td rowspan="2" width="80" style="border:solid 1px;">VALUE</td>
            </tr>
            <tr class="detail" align="center">
                <td width="40" style="border:solid 1px;">Qty</td>
                <td width="40" style="border:solid 1px;">Satuan</td>
                <td width="70" style="border:solid 1px;">Batch</td>
                <td width="60" style="border:solid 1px;">Exp Date</td>
                <td width="60" style="border:solid 1px;">Harga</td>
            </tr>
        </thead>
        <tbody class="report-body" id="badan">
        <?php foreach ($data_detail as $key => $detail) { ?>
            <?php if($detail['QtyFaktur'] + $detail['BonusFaktur'] <> 0) {
                $xt = $xt + 1;
                $jumlah_baris_data += 1;
                // ===================================================
                // $baris_kosong = $baris_kosong - 1;
                // $baris_kosong2 = $baris_kosong2 - 1;
               if(strlen($detail['NamaProduk']) > 26 || strlen($detail['UOM']) > 5){
                    $jumlah_baris_data += 1;
                }

                    $t_potongan = $t_potongan + $xdetail['Potongan_detail'];
                    $t_gross = $t_gross + $detail['Gross_detail'];
                    $t_value = $t_value + $detail['Value_detail'];
                    $t_ppn = $t_ppn + $detail['Ppn_detail'];
                ?>
                <tr v-for = "(index,datas) in data_detail" class="detail-item">
                    <!-- <td valign="top" v-html='index + 1' class='awal'><?php echo $xt ?></td> -->
                    <td valign="top" v-html='datas.KodeProduk' class='awal' style='padding-left:5px; font-size:10px;'><?php echo $detail['KodeProduk'] ?></td>
                    <td style='padding-left:2px; font-size: 10px;' valign="top">
                        <span v-text='datas.NamaProduk'></span>
                        <?php 
                            echo $detail['NamaProduk']." ";
                            if($detail['DiscCab'] > 0 ){
                                echo "D".number_format($detail['DiscCab'],2)." ";
                            }
                            if($detail['DiscPrins1'] > 0 || $detail['DiscPrins2'] > 0){
                                echo "/ P".number_format($detail['DiscPrins1'] + $detail['DiscPrins2'],2);
                            }
                         ?>
                    </td>
                    <td valign="top" style='padding-right:2px;' align='center'><?php echo $detail['QtyFaktur'] ?></td>
                    <td valign="top" style='padding-left:2px;'><?php echo $detail['UOM'] ?></td>
                    <td valign="top" style='padding-left:2px;'><?php echo $detail['BatchNo'] ?></td>
                    <td valign="top" align="center" style='padding-left:2px;font-size:10px;'>
                        <?php $x = date_format(date_create($detail['ExpDate']),"Y-m-d");
                        echo str_replace("-",".",$x) ?>
                        
                    </td>
                    <td valign="top" currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Harga']) ?></td>
                    <!-- <td valign="top" style='padding-right:2px;' align="right"><?php echo $detail['BonusFaktur'] ?></td> -->
                    <td valign="top" align="right"><?php echo (double)$detail['DiscCab']+(double)$detail['DiscPrins1']+(double)$detail['DiscPrins2'] ?></td>
                    <td valign="top" v-html='datas.Gross_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Gross_detail']) ?></td>
                    <td valign="top" v-html='datas.Potongan_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Potongan_detail']) ?></td>
                    <td valign="top" v-html='datas.Value_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Value_detail']) ?></td>
                </tr>

            <?php } ?>
        <?php } ?>
                <?php for($i=$jumlah_baris_data; $i < $jml_baris_standar_halaman1; $i++ ){ ?>
                <tr>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <!-- <td colspan="11" style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td> -->
                </tr>
                <tr>
                    
                </tr>
                <?php } ?>
                <?php if($jumlah_baris_data > $jml_baris_standar_halaman1 and $jumlah_baris_data < $jml_baris_standar_halaman2){

                    for($i=$jumlah_baris_data; $i < $jml_baris_standar_halaman2; $i++ ){ ?>
                        <?php if($i == $jml_baris_standar_halaman2-1){ ?> 
                            <!-- <tr>
                                <td colspan="12" style="border-top: solid 1px;">
                                <div align="center">Halaman 1  <span style="float: right;"><?php echo $data_detail[0]['NoFaktur'] ?></span></div>
                                </td>
                            </tr> -->
                        <?php }else{ ?>
                            <tr>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <!-- <td colspan="11" style="border-left: solid 1px; border-right: solid 1px; padding: 0px 2px 0px 2px;">&nbsp;</td> -->
                            </tr>
                        <?php } ?>
                        
                   <?php }
            } ?>
        </tbody>
        <tfoot class="report-footer">
            <tr class="detail atasz" style="font-size: 11px; border-top: solid 1px;" >
                <td class='awal' colspan="8" valign="top" style='padding:2px;'>Jumlah</td>
                <td align='right' v-html='Gross | currencyDisplay' style='padding-right:2px;'>&nbsp;</td>
                <td align='right' v-html='Potongan | currencyDisplay' style='padding-right:2px;'>&nbsp;</td>
                <td align='right' v-html='Value | currencyDisplay' style='padding-right:2px;'>&nbsp;</td>
            </tr>
            <!-- --------------------------------------------------------- -->
            <tr class="detail atas">
                <td style='padding-left:5px; text-transform: capitalize;' colspan="8" rowspan='2' valign="top" class="awal">Terbilang : <br> <span id ="terbilang_value"></span></td>
                <td colspan="2" height="20">Ongkos Kirim</td>
                <td align='right' v-html='OngkosKirim | currencyDisplay' style='padding-right:2px;'></td>
            </tr>
            <tr class="detail">
                <td colspan="2" height="20">Materai</td>
                <td align='right' v-html='Materai | currencyDisplay' style='padding-right:2px;'></td>
            </tr>
            <!-- --------------------------------------------------------- -->
            <tr class="detail">
                <td colspan="2" rowspan="4" class="awal" valign="top">
                    <!-- <div id="qcTarget"></div> -->
                    <!-- <img id="qrImage" src="" style="margin:2px;"> -->
                    <div style="float: left; width: 170px; margin-top: 5px;">
                        <?php 
                            echo '<img src="assets/qrcode.php?text='.$data_detail[0]['NoFaktur'].'" style="border:solid 1px; display:none;" height="50" width="50" />';
                            echo "<br>";
                            echo $data_detail[0]['NoFaktur'];
                        ?>
                    </div>
                    <div style="float: left; top: 0px;">
                        Penerima,
                    </div>
                </td>
                <td colspan="3" rowspan="4" align="center" valign="top"></td>
                <td colspan="3" rowspan="4" align="center" valign="top">Hormat Kami,<br>
                    Pengirim,<br><br><br>
                    <?php echo $data_detail[0]['Nama_APJ'] ?>
                <td colspan="2" height="20">Cash Diskon</td>
                <td align="right"></td>
            </tr>
            <tr class="detail">
                <td colspan="2" height="20">Dasar Pengenaan Pajak</td>
                <td height="20"></td>
            </tr>
            <tr class="detail">
                <td colspan="2" height="20">PPN</td>
                <td align='right' style='padding-right:2px;'></td>
                </td>
            </tr>
            <tr class="detail">
                <td colspan="2" height="20">Jumlah Tagihan</td>
                <td align='right' style='padding-right:2px; font-weight: bold;'></td>
            </tr>
            <tr>
                <td colspan="11" style="font-size: 8px;">
                    Pembayaran harap di transfer ke 
                    <!-- rekening {....................} BCA cabang {---------------------} A/C : {-------------}; -->
                    <span v-text="Bank_Acc"></span>.&nbsp;
                    Pembayaran dengan cek dan giro dianggap sah setelah diuangkan <br>
                    <!-- * Sesuai PER. DJP /16/PJ/2014 e-faktur Pajak tidak perlu di cek hard copy. Jika diperlukan e-faktur Pajak dapat
                    dicetak sendiri dengan memindai kode QRCODE dikiri bawah dokumen ini. -->
                </td>
            </tr>
        </tfoot>
    </table>
    <!-- <table width="100%">
        <tr>
            <td width="250" align="center">
                <br>
                Penerima, <br><br><br><br><br><br>
                <span style="border-top: solid 1px; width: 200px; display: block;"></span>
            </td>
            <td width="250" align="center">
                Hormat Kami,<br>
                Pengirim, <br><br><br><br><br>
                <span v-html="Nama_APJ"></span>
                <span style="border-top: solid 1px; width: 200px; display: block;" v-text="SIKA_Faktur"></span>
            </td>
        </tr>
    </table> -->
    <footer style="display: block; page-break-after:always;"></footer>
    <!-- ============================================================================ -->
    <?php
        function terbilang($x) {
            $status_bilangan = "";
            if($x < 0){
                $x = $x * -1;
            }
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