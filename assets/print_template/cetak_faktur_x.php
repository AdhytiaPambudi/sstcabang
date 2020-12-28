<!DOCTYPE html>
<html>
<head>
    <title>Printing</title>
</head>
<body>
    <?php $data_detail = ($_POST["var1"]); ?>
        <?php
            $t_qty = 0;
            $t_potongan = 0;
            $t_gross = 0;
            $t_value = 0;
            $t_ppn = 0;
            $xt = 1;
        ?>
    <table border="0" cellspacing="0">
        <tr>
            <td height="80px" width="80px" valign="top">
                <div id="logo"><img src="assets/logo/1489135837049.jpg"></div>
            </td>
            <td width="250" valign="top">
                PT. SAPTA SARITAMA<br>
                Jl. Caringin No. 254 A BABAKAN, BANDUNG
            </td>
            <td rowspan="3" valign="top">
                <table cellspacing="0">
                    <tr>
                        <td>No.Faktur &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="120" nowrap v-html="NoFaktur"><?php echo $data_detail[0]['NoFaktur'] ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="TglFaktur | format_tanggal"><?php echo format_tanggal($data_detail[0]['TglFaktur']) ?></td>
                    </tr>
                    <tr>
                        <td>Cabang &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="Cabang"><?php echo $data_detail[0]['Cabang'] ?></td>
                    </tr>
                    <tr>
                        <td>Tipe Pelanggan &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="TipePelanggan"><?php echo $data_detail[0]['TipePelanggan'] ?></td>
                        
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
                            <?php 
                                if($data_detail[0]['CaraBayar'] == "Cash"){
                                    $top = 7;
                                    // $j_tempo = $data_detail[0]['TglFaktur'] + 7;
                                    $j_tempo = date('Y-m-d', strtotime($data_detail[0]['TglFaktur']. ' + 7 days'));
                                }else{
                                    $top = 21;
                                    $j_tempo = date('Y-m-d', strtotime($data_detail[0]['TglFaktur']. ' + 21 days'));
                                }
                                echo $top." hari"
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="100" nowrap>Tgl Jatuh Tempo &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="Jatuh_Tempo | format_tanggal"><?php echo format_tanggal($j_tempo) ?></td>
                        
                    </tr>
                    <tr>
                        <td width="100" nowrap>No Pajak &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100" nowrap v-html="NoPajak"><?php $data_detail[0]['NoPajak'] ?></td>
                    </tr>
                </table>
            </td>
            <td valign="top" align="center">
                <?php 
                    echo "<H5>".$data_detail[0]['TipeDokumen']."</H5>";
                    echo '<img alt="Coding Sips" src="assets/barcode.php?text='.$data_detail[0]['NoFaktur'].'&print=true" />';
                 ?>
                <div id="bcTarget"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td>Pelanggan</td>
                        <td>: <span v-html='Pelanggan'></span> - <span v-html='NamaPelanggan'></span></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: <span v-html='AlamatFaktur'></td>
                    </tr>
                    <tr>
                        <td>NPWP</td>
                        <td>: <span v-html='NPWPPelanggan'></td>
                    </tr>
                </table>
            </td>
            <td>
                No DO : <?php echo $data_detail[0]['NoDo'] ?>
            </td>
        </tr>
    </table>
    <table cellspacing="0" class="report-container" border="0" style="page-break-inside:auto;">
    <thead class="report-header">
        <tr class="detail atas" align="center">
            <td rowspan="2" width="20" class="awal" style="border:solid 1px;">No.</td>
            <td rowspan="2" width="60" style="border:solid 1px;">ID</td>
            <td rowspan="2" width="220" style="border:solid 1px;">NAMA PRODUK</td>
            <td colspan="5" style="border:solid 1px;">DATA</td>
            <td rowspan="2" width="60" style="border:solid 1px;">BONUS</td>
            <td rowspan="2" width="80" style="border:solid 1px;">DISKON</td>
            <td rowspan="2" width="80" style="border:solid 1px;">GROSS</td>
            <td rowspan="2" width="80" style="border:solid 1px;">POTONGAN</td>
            <td rowspan="2" width="80" style="border:solid 1px;">VALUE</td>
        </tr>
        <tr class="detail" align="center">
            <td width="40" style="border:solid 1px;">Qty</td>
            <td width="30" style="border:solid 1px;">Satuan</td>
            <td width="30" style="border:solid 1px;">Batch</td>
            <td width="100" style="border:solid 1px;">Exp Date</td>
            <td width="50" style="border:solid 1px;">Harga</td>
        </tr>
    </thead>
    <tbody class="report-body" id="badan">
    <?php foreach ($data_detail as $key => $detail) { ?>
        <?php if($detail['QtyFaktur'] + $detail['BonusFaktur'] > 0) { ?>
            <?php 
                $t_potongan = $t_potongan + $detail['Potongan_detail'];
                $t_gross = $t_gross + $detail['Gross_detail'];
                $t_value = $t_value + $detail['Value_detail'];
                $t_ppn = $t_ppn + $detail['Ppn_detail'];
            ?>
            <tr v-for = "(index,datas) in data_detail" class="detail-item" style="page-break-after: always;" >
                <td valign="top" v-html='index + 1' class='awal'><?php echo $xt ?></td>
                <td valign="top" v-html='datas.KodeProduk' style='padding-left:5px;'><?php echo $detail['KodeProduk'] ?></td>
                <td style='padding-left:2px;'>
                    <span v-text='datas.NamaProduk'></span>
                    <?php 
                        echo $detail['NamaProduk']." ";
                        if($detail['DiscCab'] > 0 ){
                            echo "D/".number_format($detail['DiscCab'])." ";
                        }
                        if($detail['DiscPrins1'] > 0 || $detail['DiscPrins2'] > 0){
                            echo "P/".number_format($detail['DiscPrins1'] + $detail['DiscPrins2']);
                        }
                     ?>
                </td>
                <td v-html='datas.QtyFaktur | currencyDisplay' style='padding-right:2px;' align='right'><?php echo $detail['QtyFaktur'] ?></td>
                <td v-html='datas.UOM' style='padding-left:2px;'><?php echo $detail['UOM'] ?></td>
                <td v-html='datas.BatchNo' style='padding-left:2px;'><?php echo $detail['BatchNo'] ?></td>
                <td v-html='datas.ExpDate' style='padding-left:2px;font-size:11px'><?php echo $detail['ExpDate'] ?></td>
                <td v-html='datas.Harga | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Harga']) ?></td>
                <td v-html='datas.BonusFaktur | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo $detail['BonusFaktur'] ?></td>
                 <td v-html='datas.DiscCab + datas.DiscPrins1 + datas.DiscPrins2 | currencyDisplay' style='padding-right:2px;'  align="right">
                     <?php echo $detail['DiscCab']+$detail['DiscPrins1']+$detail['DiscPrins2'] ?>
                 </td>
                <td v-html='datas.Gross_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Gross_detail']) ?></td>
                <td v-html='datas.Potongan_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Potongan_detail']) ?></td>
                <td v-html='datas.Value_detail | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($detail['Value_detail']) ?></td>
            </tr>
            <?php if($xt == 13){ ?>
            <!-- <tr>
                <td colspan="13" style="border-bottom: solid 1px;"></td>
            </tr> -->
            <tr>
                <td colspan='13' style="border-bottom: solid 1px;">
                    <div></div>
                    <div id="content">
                        <div id='break'></div>
                    </div>
                </td>
            </tr>
            <?php } ?>
        <?php 
        $xt++;
        } ?>
    <?php } ?>
    </tbody>
    <!-- <tr v-if="data_detail.length <= 10 ">
        <td style="border-bottom: solid 1px;" colspan='13'>
            
        </td>
    </tr>
    <tr v-if="data_detail.length > 10 && data_detail.length <= 20">
        <td style="border-bottom: solid 1px;"  colspan='13'>
            <div style="border-bottom: solid 1px;"></div>
            <div style="page-break-before:always"></div>

        </td>
    </tr>
    <tr v-if="data_detail.length > 20 && data_detail.length < 35">
        <td style="border-bottom: solid 1px;"  colspan='13'>
            <div style="border-bottom: solid 1px;"></div>
            <div style="page-break-before:auto"></div>

        </td>
    </tr> -->
    <!--< tr>
        <td style="border-bottom: solid 1px;"  colspan='13'>
            <div style="border-bottom: solid 1px;"></div>
            <div id='break' style="page-break-before:always"></div>

        </td>
    </tr> -->
    <!-- <tr>
        <td colspan='13'>
            <div style="border-bottom: solid 1px;"></div>
            <div id='break'></div>
        </td>
    </tr> -->
        <!-- <tfoot class="report-footer"> -->
                <tr class="detail atas" style="font-size: 11px;" >
                    <td class='awal' colspan="3" valign="top">Jumlah</td>
                    <td align='right' v-html='total_qty | currencyDisplay' style='padding-right:2px;'></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td><!-- <td align='right' v-html='total_Harga | currencyDisplay' style='padding-right:2px;'></td> -->
                    <td align='right' v-html='total_ValueBonus | currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='total_ValueDiscCab + total_ValueDiscPrins1 + total_ValueDiscPrins2| currencyDisplay' style='padding-right:2px;'></td>
                    <td align='right' v-html='Gross | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_gross) ?></td>
                    <td align='right' v-html='Potongan | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_potongan) ?></td>
                    <td align='right' v-html='Value | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_value) ?></td>
                </tr>
                
                <tr class="detail atas">
                    <td style='padding-left:5px;' colspan="10" rowspan='2' valign="top" class="awal">Terbilang : <br> <span id ="terbilang_value"></span><?php echo terbilang(round($detail['Total'])).' rupiah' ?></td>
                    <td colspan="2">Ongkos Kirim</td>
                    <td align='right' v-html='OngkosKirim | currencyDisplay' style='padding-right:2px;'><?php echo number_format($detail[0]['OngkosKirim']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="2">Materai</td>
                    <td align='right' v-html='Materai | currencyDisplay' style='padding-right:2px;'><?php echo number_format($detail[0]['Materai']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="3" rowspan="5" class="awal">
                        <div id="qcTarget"></div>
                        <img id="qrImage" src="" style="margin:2px;">
                        <?php 
                            echo '<img src="assets/qrcode.php?text='.$data_detail[0]['NoFaktur'].'" style="border:solid 1px;" />';
                        ?>
                    </td>
                    <td colspan="4" rowspan="5" align="center">Penerima, <br><br><br><br><br></td>
                    <td colspan="3" rowspan="5" align="center">Hormat Kami,<br>
                        Pengirim, <br><br><br><br>
                        <?php echo $data_detail[0]['Nama_APJ'] ?>
                    <td colspan="2">Cash Diskon</td>
                    <td align="right"><?php echo number_format($data_detail[0]['CashDiskon']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="2">PPN</td>
                    <td align='right' v-html='Ppn | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_ppn) ?></td>
                    </td>
                </tr>
                <tr class="detail">
                    <td colspan="2">Jumlah Tagihan</td>
                    <td align='right' v-html='Total | currencyDisplay' style='padding-right:2px;'><?php echo number_format($detail['Total']) ?></td>
                </tr>
                <tr class="detail">
                    <td colspan="3">Dasar Pengenaan Pajak</td>
                </tr>
                <tr class="detail">
                    <td colspan="3"> &nbsp;</td>
                </tr>
                <tr>
                    <td colspan="13">
                        Pembayaran harap di transfer ke 
                        <!-- rekening {....................} BCA cabang {---------------------} A/C : {-------------}; -->
                        <span v-text="Bank_Acc"></span>.&nbsp;
                        Pembayaran dengan cek dan giro dianggap sah setelah diuangkan <br>
                        * Sesuai PER. DJP /16/PJ/2014 e-faktur Pajak tidak perlu di cek hard copy. Jika diperlukan e-faktur Pajak dapat
                        dicetak sendiri dengan memindai kode QRCODE dikiri bawah dokumen ini.
                    </td>
                </tr>
        <tfoot style="border:white">
            <tr style="border:white">
                <td colspan="13" align="center"" style="border:white">
                Halaman
                <div id='break2'></div>
                <div style="float: right"><?php echo "No Faktur: ".$data_detail[0]['NoFaktur'] ?></div>
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
    <script type="text/javascript">
            var tinggi = document.getElementById('badan').offsetHeight;
            console.log(tinggi);
            if(806>tinggi>350){
                document.getElementById("break").setAttribute("style", "page-break-before:always;");
            }
    </script>
</body>
</html>