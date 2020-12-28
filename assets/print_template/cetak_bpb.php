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
        ?>
    <table>
        <tr>
            <td height="80px" width="80px" valign="top" style="border: solid 0px;">
                <div id="logo"><img src="assets/logo/baru.png"></div>
            </td>
            <td width="190" valign="top">
                PT. SAPTA SARITAMA<br>
                <!-- Jl. Caringin No. 254 A BABAKAN, BANDUNG -->
                <?php echo $data_detail[0]['Alamat'] ?>
                
            </td>
            <td rowspan="3" valign="top">
                <table cellspacing="0" style="font-size: 10px;">
                    <tr>
                        <td width="60" nowrap>No. BPB &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="180" nowrap v-html="NoDokumen"><?php echo $data_detail[0]['NoDokumen'] ?></td>
                    </tr>
                    <tr>
                        <td>No.PO &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="NoPO"><?php echo $data_detail[0]['NoPO'] ?></td>
                    </tr>
                    <tr>
                        <td>No.PR &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="NoPO"><?php echo $data_detail[0]['NoPR'] ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="TglDokumen"><?php echo $data_detail[0]['TglDokumen'] ?></td>
                    </tr>
                    <tr>
                        <td>Cabang &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="Cabang"><?php echo $data_detail[0]['Cabang'] ?></td>
                    </tr>
                    <tr>
                        <td>No SJ &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="NoSJ"><?php echo $data_detail[0]['NoSJ'] ?></td>
                        
                    </tr>
                    <tr>
                        <td>No Invoice &nbsp;</td>
                        <td>:&nbsp;</td>
                        <td v-html="NoInv"><?php echo $data_detail[0]['NoInv'] ?></td>  
                    </tr>
                     <tr>
                        <td>Expedisi(BEX)</td>
                        <td>:&nbsp;</td>
                        <td width="100px"><?php echo $data_detail[0]['NoBEX'] ?></td>  
                    </tr>
                    <!-- <tr>
                        <td>Status Pusat&nbsp;</td>
                        <td>:&nbsp;</td>
                        <td width="100px" nowrap v-html="statusPusat"></td>  
                    </tr> -->
                    
                </table>
            </td>
             <td valign="top">
                    <H5 style="padding-top:0px;">BUKTI PENERIMAAN BARANG</H5>
            </td>
            <!-- <td valign="top" width="150px">Barcode</td> -->
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <td><b>Prinsipal:</b> <?php echo $data_detail[0]['Prinsipal'] ?></td>
            <td>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;</td>
            <td><b>Supplier</b> : <?php echo $data_detail[0]['Supplier'] ?></td>
        </tr>
    </table>
    <table cellspacing="0">
    <thead>
        <tr class="detail atas" align="center">
            <td rowspan="2" width="20" class="awal">No.</td>
            <td rowspan="2" width="80">ID PRODUK</td>
            <td rowspan="2" width="220">NAMA PRODUK</td>
            <td colspan="5">DATA</td>
            <td rowspan="2" width="80">HARGA</td>
            <td rowspan="2" width="80">DISKON</td>
            <td rowspan="2" width="80">Gross</td>
            <td rowspan="2" width="80">Potongan</td>
            <td rowspan="2" width="80">Value</td>
        </tr>
        <tr class="detail" align="center">
            <td width="40">Qty</td>
            <td width="40">Bonus</td>
            <td width="30">Satuan</td>
            <td width="60">Batch</td>
            <td width="80">Exp Date</td>
            <!-- <td width="50">Pusat</td> -->
            <!-- <td width="50">Cabang</td> -->
            <!-- <td width="50">Pusat</td> -->
            <!-- <td width="50">Cabang</td> -->
        </tr>
    </thead>
         <tbody class="report-body">
         <?php foreach ($data_detail as $key => $value) { ?>
            <?php 
            if($value['Qty'] <> 0 or $value['Bonus'] <> 0){
                $t_potongan = $t_potongan + $value['Potongan'];
                $t_gross = $t_gross + $value['Gross'];
                $t_value = $t_value + $value['Value'];
                $t_ppn = $t_ppn + $value['PPN'];
            ?>
            <tr v-for = "(index,datas) in data_detail" class="detail-item"page-break-after: always;" >
                <td v-html='index + 1' class='awal'> <?php echo $key+1 ?></td>
                <td v-html='datas.Produk' style='padding-left:5px;'><?php echo $value['Produk'] ?></td>
                <td v-html='datas.NamaProduk' style='padding-left:2px;'><?php echo $value['NamaProduk'] ?></td>
                <td v-html='datas.Qty | currencyDisplay' style='padding-right:2px;' align='right'><?php echo $value['Qty'] ?></td>
                <td v-html='datas.Bonus | currencyDisplay' style='padding-right:2px;' align='right'><?php echo $value['Bonus'] ?></td>
                <td v-html='datas.Satuan' style='padding-left:2px;'><?php echo $value['Satuan'] ?></td>
                <td v-html='datas.BatchNo' style='padding-left:2px;'><?php echo $value['BatchNo'] ?></td>
                <td v-html='datas.ExpDate' style='padding-left:2px;'><?php echo $value['ExpDate'] ?></td>
                <!-- <td v-html='datas.Harga_Beli_Pst | currencyDisplay' style='padding-right:2px;'  align="right"></td> -->
                <td v-html='datas.HrgBeli | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($value['HrgBeli']) ?></td>
                <!-- <td v-html='datas.Disc_Pst | currencyDisplay' style='padding-right:2px;'  align="right"></td> -->
                <td v-html='datas.Disc | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($value['Disc'],2) ?></td>
                <td v-html='datas.Gross | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($value['Gross']) ?></td>
                <td v-html='datas.Potongan | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($value['Potongan']) ?></td>
                <td v-html='datas.Value | currencyDisplay' style='padding-right:2px;'  align="right"><?php echo number_format($value['Value']) ?></td>
            </tr>
        <?php   
            }
        }
        ?>
    </tbody>
    <!-- <tr class="page-break"> -->
    <!-- <tr v-if="data_detail.length <= 10 ">
        <td style="border-bottom: solid 1px;"  colspan='11'>
            <div style="border-bottom: solid 1px;" ></div>
            <div style="page-break-before:auto"></div>

        </td>
    </tr> -->
    <!-- <tr v-if="data_detail.length > 10 && data_detail.length <= 20">
        <td style="border-bottom: solid 1px;"  colspan='11'>
            <div style="border-bottom: solid 1px;"></div>
            <div style="page-break-before:always"></div>

        </td>
    </tr> -->
    <!-- <tr v-if="data_detail.length > 20 && data_detail.length < 35">
        <td style="border-bottom: solid 1px;"  colspan='11'>
            <div style="border-bottom: solid 1px;"></div>
            <div style="page-break-before:auto"></div>

        </td>
    </tr> -->
    <!-- <tr v-if="data_detail.length >= 35">
        <td style="border-bottom: solid 1px;"  colspan='11'>
            <div style="border-bottom: solid 1px;"></div>
            <div style="page-break-before:always"></div>

        </td>
    </tr> -->
    <!-- <tfoot class="report-footer"> -->
        <tr class="detail atas">
            <td class='awal' colspan="3" valign="top">Jumlah</td>
            <td align='right' v-html='total_qty | currencyDisplay' style='padding-right:2px;'><?php echo $data_detail[0]['total_qty'] ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <!-- <td align='right' v-html='total_Harga_Beli_Pst | currencyDisplay' style='padding-right:2px;'></td> -->
            <td align='right' v-html='total_Harga_Beli | currencyDisplay' style='padding-right:2px;'><?php echo number_format($data_detail[0]['total_Harga_Beli']) ?></td>
            <!-- <td align='right' v-html='total_Disc_Pst | currencyDisplay' style='padding-right:2px;'></td> -->
            <td align='right' v-html='total_Disc | currencyDisplay' style='padding-right:2px;'><?php echo number_format($data_detail[0]['total_Disc']) ?></td>
            <td align='right' v-html='total_Gross | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_gross) ?></td>
            <td align='right' v-html='total_Potongan | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_potongan) ?></td>
            <td id ='total_Value' align='right' v-html='total_Value | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_value) ?></td>
        </tr>
        <tr class="detail atas">
            <td style='padding: 5px;' colspan="10" rowspan='6' valign="top" class="awal">Terbilang : <br> <?php echo terbilang(round($data_detail[0]['total_header'])) ?></td>
            <td></td>
        </tr>
        <tr class="detail">
            <td colspan="2">Ongkos Kirim</td>
            <td><?php echo $data_detail[0]['total_Disc'] ?></td>
        </tr>
        <tr class="detail">
            <td colspan="2">PPN</td>
            <td align='right' v-html='ppn_header | currencyDisplay' style='padding-right:2px;'><?php echo number_format($t_ppn) ?></td>
        </tr>
        <tr class="detail">
            <td colspan="2">PPn BM</td>
            <td></td>
        </tr>
        <tr class="detail">
            <!-- <td colspan="3" rowspan="4" class="awal"></td>
            <td colspan="4" rowspan="4"></td>
            <td colspan="2" rowspan="4"></td> -->
            <td colspan="2">Jumlah Tagihan</td>
            <td align='right' v-html='total_header | currencyDisplay' style='padding-right:2px;'><?php echo number_format($data_detail[0]['total_header']) ?></td>
        </tr>
        <tr class="detail">
            <td colspan="2">Dasar Pengenaan Pajak</td>
            <td></td>
        </tr>
    <!-- </tfoot> -->
    </table>
    <!-- <table width="840px;">
        <tr>
            <td>
                Pembayaran harap di transfer ke rekening {....................} BCA cabang {---------------------} A/C : {-----------};
                Pembayaran dengan cek dan giro dianggap sah setelah diuangkan <br>
                * Sesuai PER. DJP /16/PJ/2014 e-faktur Pajak tidak perlu di cek hard copy. Jika diperlukan e-faktur Pajak dapat
                dicetak sendiri dengan memindai kode QRCODE dikiri bawah dokumen ini.
            </td>
        </tr>
    </table> -->
    <br>
    <table>
        <tr>
            <td align="center" width="250">
                Penerima,<br><br><br><br><br>
                <span style="border-top: solid 1px; width: 200px; display: block;">P. Gudang</span>
            </td>
            <td align="center" width="250">
                Penanggung Jawab,<br><br><br><br><br>
                <span style="border-top: solid 1px; width: 200px; display: block;">Kabag</span>
            </td>
            <td align="center" width="250">
                Menyetujui,<br><br><br><br><br>
                <span style="border-top: solid 1px; width: 200px; display: block;">BM</span>
            </td>
        </tr>
    </table>
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
</body>
</html>