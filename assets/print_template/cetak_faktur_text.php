<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data = ($_POST["var1"]); ?>
        <?php
            $data_detail = json_decode($data);
            $t_qty = 0;
            $t_potongan = 0;
            $t_gross = 0;
            $t_value = 0;
            $t_ppn = 0;
            $xt = 0;
            // $baris_kosong = 3;
            // $baris_kosong2 = 6;
            $jumlah_baris_data = 0;
            // $jml_baris_standar_halaman1 = 16;
            $jml_baris_standar_halaman1 = 12;
            $jml_baris_standar_halaman2 = 35;
            // $jml_baris_max_halaman1 = 24;
        ?>
    <!-- <table border="1" cellspacing="0" style="font-family: sans-serif; margin-left: 0px;" width="2480"> -->
    <table border="0" cellspacing="0" style="font-family: sans-serif; margin-left: 0px; margin-top: 46px !important;">
        <tr>
            <td height="80px" width="80px" valign="top" hidden>
                <!-- <div id="logo"><img src="assets/logo/1489135837049.jpg"></div> -->
            </td>
            <td width="300" valign="top">
                <!-- PT. SAPTA SARITAMA --><br><br>
                Jl. Caringin No. 254 A BABAKAN, BANDUNG
            </td>
            <td rowspan="2" valign="top">
                <table cellspacing="0">
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="120" nowrap v-html="NoFaktur"><?php echo $data_detail[0]->NoFaktur ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap v-html="TglFaktur | format_tanggal"><?php echo format_tanggal($data_detail[0]->TglFaktur) ?></td>
                    </tr>
                    <tr>
                        <td> &nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap v-html="Cabang"><?php echo $data_detail[0]->Cabang ?></td>
                    </tr>
                    <tr>
                        <td> &nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap v-html="TipePelanggan"><?php echo $data_detail[0]->TipePelanggan ?></td>
                        
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap v-html="CaraBayar"><?php echo $data_detail[0]->CaraBayar ?></td>
                        
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap>
                            <?php 
                                switch ($data_detail[0]->CaraBayar) {
                                    case "Cash":
                                        $top = 7;
                                        $j_tempo = date('Y-m-d', strtotime($data_detail[0]->TglFaktur. ' + 7 days'));
                                        break;

                                    case "Kredit":
                                        $top = 21;
                                        $j_tempo = date('Y-m-d', strtotime($data_detail[0]->TglFaktur. ' + 21 days'));
                                        break;

                                    default:
                                        // $top = 0;
                                        $j_tempo = "";
                                }
                                // if($data_detail[0]->CaraBayar == "Cash"){
                                //     $top = 7;
                                //     // $j_tempo = $data_detail[0]->TglFaktur + 7;
                                //     $j_tempo = date('Y-m-d', strtotime($data_detail[0]->TglFaktur. ' + 7 days'));
                                // }else{
                                //     $top = 21;
                                //     $j_tempo = date('Y-m-d', strtotime($data_detail[0]->TglFaktur. ' + 21 days'));
                                // }
                                echo $top." hari"
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="100" nowrap>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap><?php echo format_tanggal($j_tempo) ?></td>
                        
                    </tr>
                    <tr>
                        <td width="100" nowrap>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td width="100" nowrap v-html="NoPajak"><?php echo $data_detail[0]->NoPajak ?></td>
                    </tr>
                </table>
            </td>
            <td width="250" valign="top" align="center">
            <h4>&nbsp;</h4>
                <?php 
                    echo '<img alt="Coding Sips" src="assets/barcode.php?text='.$data_detail[0]->NoFaktur.'&print=true" style="display:none;" />';
                 ?>
                <div id="bcTarget"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td width="68" valign="top"></td>
                        <td width="300" style="font-size: 11px;">&nbsp;<?php echo $data_detail[0]->Pelanggan ?> - <?php echo $data_detail[0]->NamaPelanggan ?></td>
                    </tr>
                    <tr>
                        <td valign="top"></td>
                        <td>&nbsp;<?php echo $data_detail[0]->AlamatFaktur ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;<?php echo $data_detail[0]->NPWPPelanggan ?></td>
                    </tr>
                </table>
            </td>
            <td valign="bottom">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;<?php echo $data_detail[0]->NoDO ?>
            </td>
        </tr>
    </table>
    <table cellspacing="0" class="report-container report-container" border="0">
        <thead class="report-headera">
            <tr align="center">
                <!-- <td rowspan="2" width="20" class="awalx" style="border:solid 0px;">No.</td> -->
                <td rowspan="2" width="60" style="border:solid 0px;">&nbsp;</td>
                <td rowspan="2" width="190" style="border:solid 0px;">&nbsp;</td>
                <td colspan="5" style="border:solid 0px;">&nbsp;</td>
                <!-- <td rowspan="2" width="50" style="border:solid 0px;">BNS</td> -->
                <td rowspan="2" width="20" style="border:solid 0px;">&nbsp;<br>&nbsp;</td>
                <td rowspan="2" width="80" style="border:solid 0px;">&nbsp;</td>
                <td rowspan="2" width="70" style="border:solid 0px;">&nbsp;</td>
                <td rowspan="2" width="80" style="border:solid 0px;">&nbsp;</td>
            </tr>
            <tr class="detail" align="center">
                <td width="40" style="border:solid 0px;">&nbsp;</td>
                <td width="40" style="border:solid 0px;">&nbsp;</td>
                <td width="70" style="border:solid 0px;">&nbsp;</td>
                <td width="60" style="border:solid 0px;">&nbsp;</td>
                <td width="60" style="border:solid 0px;">&nbsp;</td>
            </tr>
        </thead>
        <tbody class="report-body" id="badan">
        <?php foreach ($data_detail as $key => $detail) { ?>
            <?php if($detail->QtyFaktur + $detail->BonusFaktur <> 0) {
                $xt = $xt + 1;
                $jumlah_baris_data += 1;
                // ===================================================
                // $baris_kosong = $baris_kosong - 1;
                // $baris_kosong2 = $baris_kosong2 - 1;
               if(strlen($detail->NamaProduk) > 25 || strlen($detail->UOM) > 5 || strlen($detail->KodeProduk) > 7){
                    $jumlah_baris_data += 1;
                }

                    $t_potongan = $t_potongan + $detail->Potongan_detail;
                    $t_gross = $t_gross + $detail->Gross_detail;
                    $t_value = $t_value + $detail->Value_detail;
                    $t_ppn = $t_ppn + $detail->Ppn_detail;
                ?>
                <tr v-for = "(index,datas) in data_detail" class="detail-itema">
                    <!-- <td valign="top" v-html='index + 1' class='awal'><?php echo $xt ?></td> -->
                    <td valign="top" v-html='datas.KodeProduk' class='awalx' style='padding-left:5px; font-size:10px;'><?php echo $detail->KodeProduk ?></td>
                    <td style='padding-left:2px; font-size: 10px;' valign="top">
                        <span v-text='datas.NamaProduk'></span>
                        <?php 
                            echo $detail->NamaProduk." ";
                            if($detail->DiscCab > 0 ){
                                echo "D".number_format($detail->DiscCab,2)." ";
                            }
                            if($detail->DiscPrins1 > 0 || $detail->DiscPrins2 > 0){
                                echo "/ P".number_format($detail->DiscPrins1 + $detail->DiscPrins2,2);
                            }
                            if($detail->BonusFaktur > 0){
                                echo "/ B".number_format($detail->BonusFaktur);
                            }
                         ?>
                    </td>
                    <td valign="top" style='padding-right:2px;' align='center'><?php echo $detail->QtyFaktur ?></td>
                    <td valign="top" style='padding-left:2px;'><?php echo $detail->UOM ?></td>
                    <td valign="top" style='padding-left:2px;'><?php echo $detail->BatchNo ?></td>
                    <td valign="top" align="center" style='padding-left:2px;font-size:10px;'>
                        <?php $x = date_format(date_create($detail->ExpDate),"Y-m-d");
                        echo str_replace("-",".",$x) ?>
                        
                    </td>
                    <td valign="top" currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail->Harga) ?></td>
                    <!-- <td valign="top" style='padding-right:2px;' align="right"><?php echo $detail->BonusFaktur ?></td> -->
                    <td valign="top" align="right"><?php echo (double)$detail->DiscCab+(double)$detail->DiscPrins1+(double)$detail->DiscPrins2 ?></td>
                    <td valign="top" v-html='datas.Gross_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail->Gross_detail) ?></td>
                    <td valign="top" v-html='datas.Potongan_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail->Potongan_detail) ?></td>
                    <td valign="top" v-html='datas.Value_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail->Value_detail) ?></td>
                </tr>

            <?php } ?>
        <?php } ?>
                <?php for($i=$jumlah_baris_data; $i < $jml_baris_standar_halaman1; $i++ ){ ?>
                <tr>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <td style="border-left: solid 0px; border-right: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                    <!-- <td colspan="11" style="border-left: solid 0px; border-right: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td> -->
                </tr>
                <tr>
                    
                </tr>
                <?php } ?>
                <?php if($jumlah_baris_data > $jml_baris_standar_halaman1 and $jumlah_baris_data < $jml_baris_standar_halaman2){

                    for($i=$jumlah_baris_data; $i < $jml_baris_standar_halaman2; $i++ ){ ?>
                        <?php if($i == $jml_baris_standar_halaman2-1){ ?> 
                            <!-- <tr>
                                <td colspan="12" style="border-top: solid 0px;">
                                <div align="center">Halaman 1  <span style="float: right;"><?php echo $data_detail[0]->NoFaktur ?></span></div>
                                </td>
                            </tr> -->
                        <?php }else{ ?>
                            <tr>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <td style="border-left: solid 0px; border-right: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td>
                                <!-- <td colspan="11" style="border-left: solid 0px; border-right: solid 0px; padding: 0px 2px 0px 2px;">&nbsp;</td> -->
                            </tr>
                        <?php } ?>
                        
                   <?php }
            } ?>
        </tbody>
        <tfoot class="report-footer">
            <tr style="font-size: 11px; border-top: solid 0px;" >
                <td class='awalx' colspan="8" valign="top" style='padding:2px;'>&nbsp;</td>
                <td align='right' v-html='Gross | currencyDisplay' style='padding-right:2px;'>
                    <?php echo number_format($t_gross) ?>
                </td>
                <td align='right' v-html='Potongan | currencyDisplay' style='padding-right:2px;'>
                    <?php echo number_format($t_potongan) ?>
                </td>
                <td align='right' v-html='Value | currencyDisplay' style='padding-right:2px;'>
                    <?php echo number_format($t_value) ?>
                </td>
            </tr>
            <tr>
                <td style='padding-left:5px; text-transform: capitalize;' colspan="8" rowspan="2" valign="bottom">
                    <?php echo terbilang(round($detail->Total)).' rupiah' ?>
                </td>
                <td colspan="2" height="20">&nbsp;</td>
                <td align='right' style='padding-right:2px;'>
                    <?php echo number_format($data_detail[0]->OngkosKirim) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="20"></td>
                <td align='right' style='padding-right:2px;'>
                    <?php echo number_format($data_detail[0]->Materai) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" rowspan="4" valign="top">
                    <!-- <div id="qcTarget"></div> -->
                    <!-- <img id="qrImage" src="" style="margin:2px;"> -->
                    <div style="float: left; width: 170px; margin-top: 5px;">
                        <?php 
                            echo '<img src="assets/qrcode.php?text='.$data_detail[0]->NoFaktur.'" style="border:solid 0px;" height="50" width="50" />';
                            echo "<br>";
                            // echo $data_detail[0]['NoFaktur'];
                        ?>
                    </div>
                    <div style="float: left; top: 0px;">

                    </div>
                </td>
                <td colspan="3" rowspan="4" align="center" valign="top"></td>
                <td colspan="3" rowspan="4" align="center" valign="top" style="font-size: 10px;"><br>
                    <br><br><br>
                    <?php echo $data_detail[0]->Nama_APJ ?>
                <td colspan="2" height="20"></td>
                <td align="right" style='padding-right:2px;'><?php echo number_format($data_detail[0]->CashDiskon) ?></td>
            </tr>
            <tr>
                <td colspan="2" height="20">&nbsp;</td>
                <td height="20" align="right" style='padding-right:2px;'><?php echo number_format($data_detail[0]->Value) ?></td>
            </tr>
            <tr>
                <td colspan="2" height="20"></td>
                <td align='right' style='padding-right:2px;'><?php echo number_format($t_ppn) ?></td>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="20"></td>
                <td align='right' style='padding-right:2px; font-weight: bold;'><?php echo number_format($detail->Total) ?></td>
            </tr>
            <tr>
                <td colspan="11" style="font-size: 8px;">
                    <!-- Pembayaran harap di transfer ke  -->
                    <!-- rekening {....................} BCA cabang {---------------------} A/C : {-------------}; -->
                    <!-- <span v-text="Bank_Acc"></span>.&nbsp; -->
                    <!-- Pembayaran dengan cek dan giro dianggap sah setelah diuangkan <br> -->
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
                <span style="border-top: solid 0px; width: 200px; display: block;"></span>
            </td>
            <td width="250" align="center">
                Hormat Kami,<br>
                Pengirim, <br><br><br><br><br>
                <span v-html="Nama_APJ"></span>
                <span style="border-top: solid 0px; width: 200px; display: block;" v-text="SIKA_Faktur"></span>
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
            return " seratus" . terbilang($x - 100);
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