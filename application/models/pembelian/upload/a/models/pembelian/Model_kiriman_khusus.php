<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_kiriman_khusus extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->load->model('pembelian/Model_orderKhusus');
            $this->load->model('pembelian/Model_main');
    }

    public function listKirimanKhusus($search = NULL, $limit = NULL)
    {   
        /*$query = $this->db->query("select * from trs_kiriman where Cabang = '".$this->cabang."' and StatusKiriman in ('Open','Pending') $search group by NoKiriman $limit");*/

        $query = $this->db->query("SELECT a.*,b.Keterangan2 FROM trs_kiriman a left join trs_delivery_order_sales b ON a.NoDO = b.NoDO  where a.Cabang = '".$this->cabang."' and Keterangan2 = 'Bidan' and  StatusKiriman in ('Open','Pending') $search group by NoKiriman $limit");

        return $query;
    }

    public function saveTerimaKirimanKhusus($params = NULL)
    {   
        
        
        $NoFaktur = "";
        
        
        foreach ($params->NoDO as $key => $value) 
        {   
            // echo $params->NoDO[$key];exit();
            // START SAVE TABEL KIRIMAN
            $this->db->set("StatusKiriman", 'Closed');
            $this->db->set("StatusDO", $params->Status[$key]);
            $this->db->set("Alasan", $params->Alasan[$key]);
            $this->db->set("TglTerima", date("Y-m-d"));
            $this->db->set("TimeTerima", date("Y-m-d H:i:s")); 
            $this->db->set("updated_by", $this->session->userdata('username'));
            $this->db->set("updated_at", date("Y-m-d H:i:s"));
            $this->db->where("NoKiriman", $params->NoKiriman);
            $this->db->where("NoDO", $params->NoDO[$key]);
            $valid = $this->db->update("trs_kiriman");
            // END SAVE TABEL KIRIMAN

            // START UPDATE STATUS DO
            if ($params->Status[$key] == "Terkirim") {   
            // echo "string";  
                $nofak = $this->db->query("select ifnull(trs_faktur.NoDO,'') as 'NoDO' from trs_faktur
                                          where ifnull(trs_faktur.NoDO,'') = '".$params->NoDO[$key]."' and Keterangan2 = 'Bidan' and TipeDokumen ='Faktur' limit 1")->num_rows();       
                if($nofak == 0){
                        // $detail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->result();
                        $header = $this->db->query("select * from trs_delivery_order_sales where NoDO = '".$params->NoDO[$key]."' and Keterangan2 = 'Bidan' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'")->row();

                        /*$detail = $this->db->query("SELECT NoDO,SUM(QtyDO + BonusDO) qty ,KodeProduk FROM trs_delivery_order_sales_detail WHERE NoDO = '".$params->NoDO[$key]."' AND Keterangan2 = 'Bidan' AND STATUS NOT IN ('Closed','Batal') AND IFNULL(status_retur,'')='N' GROUP BY KodeProduk");
                        
                        foreach ($detail->result() as $r) {

                           if ($this->cek_saldo_akhir($r->KodeProduk) - $r->qty < 0 ) {

                             return $valid = ["status" =>false,"message"=> $r->KodeProduk . " minus",'pesan'=>'sukses'];
                           }
                        }*/

                        $jto = $this->db->query("select JTO_Real from mst_cara_bayar where Cara_Bayar = '".$header->CaraBayar."'")->row();
                        $tglJTO = date('Y-m-d', strtotime('+'.$jto->JTO_Real.' days', strtotime(date('Y-m-d')))); 

                        if ($header->CaraBayar == 'Cash') {
                          $status_faktur = 'LunasTransfer';
                          $total_faktur = '0';
                        }else{
                          $status_faktur = 'Open';
                          $total_faktur = '`Total`';
                        }

                        $valid = $this->db->query("INSERT INTO `trs_faktur` (
                                          `Cabang`,`NoFaktur`,
                                          `TglFaktur`,`TimeFaktur`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatFaktur`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          `TipePajak`,`NoPajak`,
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,
                                          `Saldo`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`NoSP`,
                                          `statusPusat`,`TipeFaktur`,
                                          `NoIDPaket`,`KeteranganTender`,
                                          `ppn_pelanggan`,
                                          `created_at`,`created_by`,`acu2`,StatusInkaso
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `TglDO`,'".date('Y-m-d H:i:s')."',
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatKirim`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          '','',
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,'".$tglJTO."',
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          '".$status_faktur."','Faktur',
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,".$total_faktur.",
                                          `Keterangan1`,`Keterangan2`,
                                          `Barcode`,`QrCode`,
                                          `NoSO`,`NoDO`,
                                          `NoSP`,`statusPusat`,
                                          `TipeFaktur`,`NoIDPaket`,
                                          `KeteranganTender`,`ppn_pelanggan`,
                                          '".date('Y-m-d H:i:s')."','".$this->user."',`acu2`,
                                          'Ok'

                                        FROM `trs_delivery_order_sales`
                                        where NoDo = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N';
                                        ");

                        $query = $this->db->query("INSERT INTO `trs_faktur_detail` (
                                          `Cabang`,`NoFaktur`,
                                          `noline`,`TglFaktur`,
                                          `TimeFaktur`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatFaktur`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyFaktur`,`BonusFaktur`,
                                          `ValueBonus`,`DiscCab`,`DiscCab_onf`,
                                          `ValueDiscCab`,`ValueDiscCab_onf`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,
                                          `sisa_retur`,`created_at`,
                                          `created_by`,`kota`,
                                          `telp`,`tipe_2`,
                                          `region`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `noline`,`TglDO`,
                                          '".date('Y-m-d H:i:s')."',`Pelanggan`,
                                          `NamaPelanggan`,`AlamatKirim`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,'".$tglJTO."',
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyDO`,`BonusDO`,
                                          `ValueBonus`,`DiscCab`,`DiscCab_onf`,
                                          `ValueDiscCab`,`ValueDiscCab_onf`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,0,
                                          '".date('Y-m-d H:i:s')."',
                                          '".$this->user."',`kota`,
                                          `telp`,`tipe_2`,`region`,`acu2`
                                        FROM `trs_delivery_order_sales_detail`
                                        where NoDo = '".$params->NoDO[$key]."' and Status not in ('Closed','Batal') and ifnull(status_retur,'')='N'");

                      
                       
                    $cekheader = $this->db->query("select * from trs_faktur where nofaktur ='".$params->NoDO[$key]."'")->num_rows();
                    $cekdetail = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$params->NoDO[$key]."'")->num_rows();
                    // echo $params->NoDO[$key];

                    if($cekheader > 0 and $cekdetail > 0){
                      
                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            $this->db->where('NoSO', $header->NoSO);
                            $this->db->update('trs_sales_order'); 

                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("Status", "Closed");
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            // $this->db->set("status_validasi", "Y");
                            $this->db->set("time_validasi", date('Y-m-d H:i:s'));
                            $this->db->set("user_validasi", $this->user);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update('trs_delivery_order_sales');

                            $this->db->set('NoFaktur', $header->NoDO);
                            $this->db->set("Status", "Closed");
                            // $this->db->set("status_validasi", "Y");
                            $this->db->set("modified_at", date('Y-m-d H:i:s'));
                            $this->db->set("modified_by", $this->user);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update('trs_delivery_order_sales_detail');

                            //insert ke DIH
                            $kodeLamaCabang = $this->Model_orderKhusus->kodeLamaCabang();
                            $kodeDokumen = $this->Model_orderKhusus->kodeDokumen('DIH');
                            $counter = $this->Model_orderKhusus->counter('DIH');
                            
                            $noDIH = "";
                            $dih = "";
                               
                                    $num_data = 0;
                                    
                                    
                                        $query = $this->db->query("select * from trs_faktur where NoFaktur = '".$header->NoDO."'  limit 1");
                                        $data = $query->row();
                                        $num_data = $query->num_rows();
                                        if($num_data > 0){
                                            $salesman = $data->Salesman;
                                            $saldo = $data->Saldo;
                                            $tgl = $data->TglFaktur; 
                                        }  
                                    // }
                                    // if($num_data > 0){
                                        $query2 = $this->db->query("select NoFaktur from trs_dih where NoFaktur = '".$header->NoDO."' limit 1");
                                        // $listdih = $query->row();
                                        $num_dih = $query2->num_rows();
                                        if($num_dih == 0){
                                            if($noDIH == ""){
                                                $year = date('Y');
                                                $doc = $this->db->query("select max(right(NoDIH,7)) as 'no' from trs_dih where  length(NoDIH)= 13  and Cabang = '".$this->cabang."' and YEAR(TglDIH) ='".$year."'")->result();
                                                if(empty($doc[0]->no) || $doc[0]->no == ""){
                                                        $lastNumber = 1000001;
                                                }else {
                                                    $lastNumber = $doc[0]->no + 1;
                                                }


                                                $cek_dih_hari_ini = $this->db->query("SELECT NoDIH from trs_dih WHERE Keterangan = 'Khusus' and TglDIH = CURDATE()");

                                                if ($cek_dih_hari_ini->num_rows() > 0 ) {
                                                  $noDIH = $cek_dih_hari_ini->row("NoDIH");
                                                }else{
                                                  $noDIH = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                                                }
                                            }

                                            if ($header->CaraBayar == 'Cash') {
                                              $this->db->set("Cabang", $this->cabang);
                                              $this->db->set("NoDIH", $noDIH);
                                              $this->db->set("NoFaktur", $header->NoDO);
                                              $this->db->set("acu2", $header->acu2);
                                              $this->db->set("TipeDokumen", "Faktur");
                                              $this->db->set("Penagih", $salesman);
                                              $this->db->set("Pelanggan", $data->Pelanggan);
                                              $this->db->set("Salesman", $salesman);
                                              $this->db->set("Status", "Closed");
                                              $this->db->set("Total", $data->Total);
                                              $this->db->set("Saldo", 0);
                                              $this->db->set("Saldo_faktur", $data->Total);
                                              $this->db->set("TglDIH", date("Y-m-d"));
                                              $this->db->set("TglFaktur", $tgl);
                                              $this->db->set("created_by", $this->user);
                                              $this->db->set("created_at", date("Y-m-d H:i:s"));
                                              $this->db->set("Keterangan", "Khusus");
                                              $valid = $this->db->insert('trs_dih');
                                            }

                                        }  
                            
                                    // insert trs_penulasan_detail
                                    $header->NoDO;
                                    
                                    $dtFaktur = $this->db->query("select * from trs_faktur where NoFaktur = '".$header->NoDO."'")->row();
                                    // print_r($dtFaktur);
  
                                    $UmurPelunasan = 0;
                                    $UmurFaktur = 0;
                                    $tgl = date("Y-m-d");
                                    
                                     $UmurPelunasan = floor((strtotime(date("Y-m-d"))-strtotime($dtFaktur->TglFaktur))/(60 * 60 * 24));
                                        
                                        $UmurFaktur = floor((strtotime($tgl)-strtotime($dtFaktur->TglFaktur))/(60 * 60 * 24)); 
                                                    // floor((strtotime($tgl)-strtotime($cek->TglPelunasan))/(60 * 60 * 24));
                                      
                                        if ($header->CaraBayar == 'Cash') {
                                          $this->db->set("UmurLunas", $UmurPelunasan);
                                          $this->db->set("Cabang", $this->cabang);
                                          $this->db->set("TglPelunasan", date("Y-m-d"));
                                          $this->db->set("NoDIH", $noDIH);
                                          $this->db->set("acu2", $dtFaktur->acu2);
                                          $this->db->set("KodePenagih", $dtFaktur->Salesman);
                                          $this->db->set("NamaPenagih", $dtFaktur->NamaSalesman);
                                          $this->db->set("NomorFaktur", $dtFaktur->NoFaktur);
                                          $this->db->set("TglFaktur", $dtFaktur->TglFaktur);
                                          $this->db->set("UmurFaktur", $UmurFaktur);
                                          $this->db->set("KodePelanggan", $dtFaktur->Pelanggan);
                                          $this->db->set("NamaPelanggan", $dtFaktur->NamaPelanggan);
                                          $this->db->set("KodeSalesman", $dtFaktur->Salesman);
                                          $this->db->set("NamaSalesman", $dtFaktur->NamaSalesman);
                                          $this->db->set("ValueFaktur", $data->Total);
                                          $this->db->set("SaldoFaktur", $data->Total);
                                          $this->db->set("ValuePelunasan", $data->Total);
                                          $this->db->set("TipePelunasan", "Transfer");
                                          $this->db->set("TipeDokumen", "Faktur");
                                          $this->db->set("bank", "00");
                                          $this->db->set("SaldoAkhir", 0);
                                          $this->db->set("status", 'Bayar Full');
                                          // $this->db->set("status_DIH", 'Full');
                                          $this->db->set("create_by", $this->user);
                                          $this->db->set("create_at", date("Y-m-d H:i:s"));
                                          $this->db->set("keterangan", "Khusus");
                                          $valid = $this->db->insert("trs_pelunasan_detail"); 
                                        }
                                         
                                     
                                        if ($header->CaraBayar != 'Cash') {
                                          $noDIH = "";
                                        }

                                        $this->db->set("statusPusat", "Gagal");
                                        $this->db->set("umur_pelunasan", $UmurPelunasan);
                                        $this->db->set("umur_faktur", $UmurFaktur);
                                        $this->db->set("nodih", $noDIH);
                                        $this->db->set("tgldih", date('Y-m-d'));
                                        $this->db->set("TglPelunasan", date('Y-m-d'));
                                        $this->db->set("modified_at", date('Y-m-d H:i:s'));
                                        $this->db->set("modified_by", $this->user);
                                        $this->db->where("NoFaktur", $dtFaktur->NoFaktur);
                                        $this->db->update("trs_faktur");

                                    if ($header->CaraBayar == 'Cash') {

                                      $this->db->set("Status", 'Closed');
                                      $this->db->set("updated_by", $this->user);
                                      $this->db->set("updated_at", date("Y-m-d H:i:s"));
                                      $this->db->where("NoFaktur", $dtFaktur->NoFaktur);
                                      $this->db->where("NoDIH", $noDIH);
                                      $this->db->update("trs_dih");
                                    }

                                    $query = $this->db->query("SELECT NoOrder FROM trs_sales_order_detail_bidan WHERE NoDo = '".$params->NoDO[$key]."'");

                                    if ($query->num_rows() > 0) {
                                        $no_order = $query->row("NoOrder");
                                        $this->Model_orderKhusus->data_api_update("method=setstatusfaktur&status=Faktur&noorder=".$no_order."&cabang=".$this->cabang."&faktur=".$dtFaktur->NoFaktur); //UPDATE API
                                    }
                                    
                                    // end insert trs_penulasan_detail
                                
                    }else{
                            $delheader = $this->db->query("delete from trs_faktur where  NoFaktur = '".$params->NoDO[$key]."'");
                            $deldetail = $this->db->query("delete from trs_faktur_detail where NoFaktur ='".$params->NoDO[$key]."'");

                            $this->db->set("StatusKiriman", 'Open');
                            $this->db->set("StatusDO", "Kirim");
                            $this->db->set("updated_by", $this->session->userdata('username'));
                            $this->db->set("updated_at", date("Y-m-d H:i:s"));
                            $this->db->where("NoKiriman", $params->NoKiriman);
                            $this->db->where("NoDO", $params->NoDO[$key]);
                            $valid = $this->db->update("trs_kiriman");

                    }    
                }else{
                    $valid =FALSE;
                }
            }
            elseif ($params->Status[$key] == "Kembali") {
              
                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales'); 

                $this->db->set("Status", "Open");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }elseif($params->Status[$key] == "Pending"){
              
                $this->db->set("StatusKiriman", 'Open');
                $this->db->set("StatusDO", $params->Status[$key]);
                $this->db->set("Alasan", $params->Alasan[$key]);
                // $this->db->set("TglTerima", date("Y-m-d"));
                // $this->db->set("TimeTerima", date("Y-m-d H:i:s")); 
                $this->db->set("updated_by", $this->session->userdata('username'));
                $this->db->set("updated_at", date("Y-m-d H:i:s"));
                $this->db->where("NoKiriman", $params->NoKiriman);
                $this->db->where("NoDO", $params->NoDO[$key]);
                $valid = $this->db->update("trs_kiriman");
            }
            // END UPDATE STATUS DO            
        }  
        // exit();
        return $valid;
    }

    public function getDOKirimKhusus(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TipeDokumen='DO' and Keterangan2 = 'Bidan' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        return $query;
    }

    public function getDOTerimaKhusus(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Terima' and tipedokumen='DO' and Keterangan2 = 'Bidan' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();

        return $query;
    }

    public function getDOopenKhusus(){
      
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Open' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
         $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Open' and tipedokumen='DO' and Keterangan2 ='Bidan' AND TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        return $query;
    }

     public function getTerimaKiriman($no = null)
    {
        $noDO = "";
        $krm = $this->db->query("select NoDO from trs_kiriman where NoKiriman = '".$no."' and StatusKiriman in ('Open','Pending')")->result();
        foreach ($krm as $kirim) {            
            $noDO .= "'".$kirim->NoDO."',";
        }
        $noDO = substr($noDO, 0, -1);
        $query = $this->db->query("select * from trs_delivery_order_sales where NoDO in (".$noDO.") and Keterangan2 = 'Bidan' order by NoDO asc")->result();
        return $query;
    }

    public function listDataDOKhusus($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        $query = "";

        if (!empty($status)) {
            if($status != 'All'){
                $byStatus = " and trs_delivery_order_sales.Status in ('".$status."')";
                $query = $this->db->query("select distinct trs_delivery_order_sales.*,'' as NoKiriman from trs_delivery_order_sales  where trs_delivery_order_sales.Cabang = '".$this->cabang."' and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' and trs_delivery_order_sales.Gross > 0 AND trs_delivery_order_sales.Keterangan2 = 'Bidan' $byStatus $search order by TimeDO DESC $limit");
            }else{
                $query = $this->db->query("select distinct trs_delivery_order_sales.*,'' as NoKiriman from trs_delivery_order_sales  where trs_delivery_order_sales.Cabang = '".$this->cabang."' and trs_delivery_order_sales.Gross > 0  and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' AND trs_delivery_order_sales.Keterangan2 = 'Bidan' $search order by TimeDO DESC $limit");
            }

        }else{
            $byStatus = " and Status <> 'Batal'";
            $query = $this->db->query("select distinct trs_delivery_order_sales.*,trs_kiriman.NoKiriman from trs_delivery_order_sales left join trs_kiriman on trs_kiriman.NoDO = trs_delivery_order_sales.NoDO where trs_delivery_order_sales.Cabang = '".$this->cabang."' and ifnull(trs_delivery_order_sales.status_retur,'') = 'N' and trs_delivery_order_sales.Gross > 0 AND trs_delivery_order_sales.Keterangan2 = 'Bidan' $byStatus $search order by TimeDO DESC $limit");

        }
        return $query;
    }

    public function saveKiriman_khusus($NoDO = NULL, $NoKiriman = NULL, $pengirim = NULL)
    {   
        $query = $this->db->query("select * FROM trs_delivery_order_sales WHERE NoDO = '".$NoDO."' and Keterangan2 = 'Bidan' and Status = 'Open' limit 1");
        $valid = true;
        $data = $query->row();
        $num_data = $query->num_rows();
        if($num_data > 0){
            $query2 = $this->db->query("select * from trs_kiriman where NoDO = '".$NoDO."' and StatusDO != 'Kembali' limit 1");
            $kirim = $query2->num_rows();
            if($kirim < 1){
                $expld = explode("-", $pengirim);
                $Kode = $expld[0];
                $Nama = $expld[1];
                if($num_data > 0){
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("KodePelanggan", $data->Pelanggan);
                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("NoKiriman", $NoKiriman);        
                    $this->db->set("StatusKiriman", 'Open');
                    $this->db->set("StatusDO", 'Kirim');
                    $this->db->set("NoDO", $NoDO);
                    $this->db->set("TglKirim", date("Y-m-d"));
                    $this->db->set("TimeKirim", date("Y-m-d H:i:s")); 
                    $this->db->set("created_by", $this->user);
                    $this->db->set("created_at", date("Y-m-d H:i:s"));
                    $this->db->set("Keterangan", "Khusus");
                    $valid = $this->db->insert('trs_kiriman');

                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("Status", "Kirim");
                    $this->db->set("modified_at", date('Y-m-d H:i:s'));
                    $this->db->set("modified_by", $this->user);
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales');        

                    $this->db->set("Status", "Kirim");
                    $this->db->set("Pengirim", $Kode);
                    $this->db->set("NamaPengirim", $Nama);
                    $this->db->set("modified_at", date('Y-m-d H:i:s'));
                    $this->db->set("modified_by", $this->user);
                    $this->db->where("NoDO", $NoDO);
                    $valid = $this->db->update('trs_delivery_order_sales_detail');
                }
            }

        }  
        return $valid;
    }

    function cek_saldo_akhir($produk){
        $hari_ini = date("Y-m-d");
        $first_date = date('Y-m-01', strtotime($hari_ini));
        $end_date = date('Y-m-t', strtotime($hari_ini));

        $query = $this->db->query("SELECT SUM(qty_in) - SUM(qty_out) AS saldo_akhir FROM 
                                (
                                SELECT 'Saldo Awal' AS 'NoDoc','Saldo Awal' AS 'Dokumen',NULL TglDoc, NULL TimeDoc,KodeProduk AS Kode ,NamaProduk NamaProduk,
                                '' Unit,SUM(SAwal04) AS 'qty_in',0 AS 'qty_out', '' AS BatchNo,NULL ExpDate
                                FROM trs_invsum 
                                WHERE Cabang = '".$this->cabang."' AND KodeProduk = '$produk' AND Tahun ='".date('Y')."'
                                GROUP BY KodeProduk

                                UNION ALL
                                SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen AS 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          (IFNULL(Qty,'') + IFNULL(Bonus,'')) AS 'qty_in',
                                          0 AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  WHERE trs_terima_barang_detail.Produk = '$produk' AND 
                                        STATUS NOT IN ('pending','Batal') AND
                                        IFNULL(Tipe,'') NOT IN  ('BKB','Tolakan') AND
                                        TglDokumen BETWEEN '$first_date' AND '$end_date'
                                  
                                  UNION ALL

                                  SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen AS 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          0 AS 'qty_in',
                                          (((IFNULL(Qty,'') + IFNULL(Bonus,'')))*-1) AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  WHERE   IFNULL(Tipe,'') = 'BKB' AND Produk = '$produk' AND 
                                        TglDokumen BETWEEN '$first_date' AND '$end_date'
                                  
                                  UNION ALL
                                  SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Tolakan Retur ',Prinsipal,' - ',Supplier,' ~ Qty : ', (IFNULL(Qty,'') + IFNULL(Bonus,''))*-1) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen AS 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          0 AS 'qty_in',
                                          0 AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  WHERE   IFNULL(Tipe,'') = 'Tolakan' AND Produk = '$produk' AND 
                                        TglDokumen BETWEEN '$first_date' AND '$end_date'

                                  UNION ALL
                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Usulan Adjusment (+) : ', catatan) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
                                         trs_mutasi_koreksi.created_at AS 'TimeDoc',
                                         trs_mutasi_koreksi.produk AS 'Kode' ,
                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         qty AS 'qty_in',
                                         0 AS 'qty_out',
                                         batch AS 'BatchNo',
                                         exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status IN ('Open','Approve') AND
                                    trs_mutasi_koreksi.`produk` = '$produk' AND 
                                        CASE WHEN trs_mutasi_koreksi.`Status` = 'Open' THEN
                                          tanggal
                                       ELSE
                                          IFNULL(DATE(tgl_approve),tanggal)
                                       END BETWEEN '$first_date' AND '$end_date'
                                  UNION ALL
                                  SELECT NoDokumen AS 'NoDoc', 
                                         CONCAT('Mutasi Gudang dari Gudang : ', gudang_awal,' Ke Gudang : ',gudang_akhir,' ~ QTY = ',qty) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
                                         trs_mutasi_gudang.create_date AS 'TimeDoc',
                                         trs_mutasi_gudang.produk AS 'Kode' ,
                                         trs_mutasi_gudang.namaproduk AS 'NamaProduk' ,
                                         '' AS 'Unit',
                                         0 AS 'qty_in',
                                         0 AS 'qty_out',
                                         batchno_awal AS 'BatchNo',
                                         expdate_awal AS 'ExpDate'
                                  FROM  trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_gudang.status IN ('Open','Approve') AND
                                    trs_mutasi_gudang.`produk` = '$produk' AND 
                                        tanggal BETWEEN '$first_date' AND '$end_date'
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Adjusment (+) : QTY = ',qty,' ', catatan) AS 'Dokumen',
                                         IFNULL(DATE(tgl_approve),tanggal) AS 'TglDoc',
                                         trs_mutasi_koreksi.created_at AS 'TimeDoc',
                                         trs_mutasi_koreksi.produk AS 'Kode' ,
                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         0 AS 'qty_out',
                                         batch AS 'BatchNo',
                                         exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                                    trs_mutasi_koreksi.`produk` = '$produk' AND 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '$first_date' AND '$end_date'
                                  UNION ALL

                                  SELECT No_Terima AS 'NoDoc', 
                                         CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
                                         Tgl_terima AS 'TglDoc',
                                         Time_terima AS 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         (Qty+Bonus) AS 'qty_in',
                                         0 AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_terima_detail 
                                  WHERE   (Qty > 0 OR Bonus > 0) AND
                                        STATUS = 'Terima' AND
                                        Produk = '$produk' AND 
                                        Tgl_terima BETWEEN '$first_date' AND '$end_date'
                                    
                                  UNION ALL 

                                  SELECT NoFaktur AS 'NoDoc',
                                        CONCAT('Terima dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
                                        TglFaktur AS 'TglDoc',
                                        TimeFaktur AS 'TimeDoc',
                                        KodeProduk AS 'Kode', 
                                        NamaProduk AS 'NamaProduk',
                                        UOM AS 'Unit',
                                        (QtyFaktur+BonusFaktur) AS 'qty_in',
                                        0 AS 'qty_out',
                                        BatchNo,
                                        ExpDate
                                  FROM trs_faktur_detail 
                                  WHERE TipeDokumen ='Retur' AND 
                                        IFNULL(STATUS,'') != 'Batal' AND
                                        KodeProduk = '$produk' AND 
                                        TglFaktur BETWEEN '$first_date' AND '$end_date'

                                  UNION ALL


                                  SELECT NoDO AS 'NoDoc',
                                    CONCAT('Kirim ke ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                                    TglDO AS 'TglDoc',
                                    TimeDO AS 'TimeDoc',
                                    KodeProduk AS 'Kode',
                                    NamaProduk AS 'NamaProduk',
                                    UOM AS 'Unit',
                                    0 AS 'qty_in', 
                                    (QtyDO+BonusDO) AS 'qty_out',
                                    BatchNo,ExpDate
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE KodeProduk = '$produk'  
                                    AND TglDO BETWEEN '$first_date' AND '$end_date' AND tipedokumen ='DO'
                                  UNION ALL
                                  SELECT NoDO AS 'NoDoc',
                                    CONCAT('Batal Kirim ke ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                                    TglDO AS 'TglDoc',
                                    TimeDO AS 'TimeDoc',
                                    KodeProduk AS 'Kode',
                                    NamaProduk AS 'NamaProduk',
                                    UOM AS 'Unit',
                                    (QtyDO+BonusDO) AS 'qty_in', 
                                    0 AS 'qty_out',
                                    BatchNo,ExpDate
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE KodeProduk = '$produk' 
                                    AND STATUS = 'Batal' 
                                    AND DATE(IFNULL(time_batal,'')) BETWEEN '$first_date' AND '$end_date'
                                  UNION ALL
                                  SELECT NoDO AS 'NoDoc',
                                    CONCAT('Retur Kirim dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',  
                                    TglDO AS 'TglDoc',
                                    TimeDO AS 'TimeDoc',
                                    KodeProduk AS 'Kode',
                                    NamaProduk AS 'NamaProduk',
                                    UOM AS 'Unit',
                                    (QtyDO+BonusDO) AS 'qty_in', 
                                    0 AS 'qty_out',
                                    BatchNo,ExpDate
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE KodeProduk = '$produk' AND 
                                        TipeDokumen ='Retur' AND STATUS = 'Retur' 
                                    AND TglDO BETWEEN '$first_date' AND '$end_date'
                                  UNION ALL

                                  SELECT trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 
                                         CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_detail.Cabang_Penerima) AS 'Dokumen',
                                         trs_relokasi_kirim_detail.Tgl_kirim AS 'TglDoc',
                                         trs_relokasi_kirim_detail.Time_kirim 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         (Qty+Bonus) AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') AND
                                        trs_relokasi_kirim_detail.Produk = '$produk' AND 
                                          trs_relokasi_kirim_detail.Tgl_kirim 
                                         BETWEEN '$first_date' AND '$end_date' 

                                  UNION ALL

                                  SELECT trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 
                                         CASE WHEN Keterangan_reject LIKE '%Penerima%' THEN
                                          'Relokasi Reject Cabang Penerima'
                                         ELSE 
                                           'Relokasi Reject Pusat'
                                         END AS 'Dokumen',
                                         trs_relokasi_kirim_detail.tgl_reject AS 'TglDoc',
                                         trs_relokasi_kirim_detail.Time_kirim 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         (Qty+Bonus) AS 'qty_in',
                                         0 AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status = 'Reject' AND
                                        trs_relokasi_kirim_detail.Produk = '$produk' AND 
                                        trs_relokasi_kirim_detail.Tgl_reject BETWEEN '$first_date' AND '$end_date'

                                  UNION ALL

                                  SELECT trs_usulan_retur_beli_header.No_Usulan AS 'NoDoc', 
                                        CONCAT('Usulan Retur Beli : QTY = ',((trs_usulan_retur_beli_detail.Qty+trs_usulan_retur_beli_detail.Bonus) * -1),'  ~ Ke : ',trs_usulan_retur_beli_header.Prinsipal) AS 'Dokumen',
                                        trs_usulan_retur_beli_header.Tanggal AS 'TglDoc',
                                        trs_usulan_retur_beli_header.added_time AS 'TimeDoc',
                                        trs_usulan_retur_beli_detail.Produk AS 'Kode' ,
                                        trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk' ,
                                        trs_usulan_retur_beli_detail.Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        0 AS 'qty_out',
                                        trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo',
                                        trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate'
                                  FROM  trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                                  WHERE IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' AND
                                    trs_usulan_retur_beli_detail.Produk = '$produk'AND 
                                        trs_usulan_retur_beli_header.tanggal BETWEEN '$first_date' AND '$end_date'
                                  
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                        CONCAT('Usulan Adjusment (-): QTY = ',(qty * -1),' ',catatan) AS 'Dokumen',
                                        tanggal AS 'TglDoc',
                                        trs_mutasi_koreksi.created_at AS 'TimeDoc',
                                        trs_mutasi_koreksi.produk AS 'Kode' ,
                                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                        Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        0 AS 'qty_out',
                                        batch AS 'BatchNo',
                                        exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status IN ('Open','Approve') AND
                                    trs_mutasi_koreksi.`produk` = '$produk'AND 
                                        CASE WHEN trs_mutasi_koreksi.`Status` = 'Open' THEN
                                            tanggal
                                         ELSE
                                            IFNULL(DATE(tgl_approve),tanggal)
                                         END BETWEEN '$first_date' AND '$end_date'
                                        
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                        CONCAT('Adjusment (-): ',catatan) AS 'Dokumen',
                                        IFNULL(DATE(tgl_approve),tanggal) AS 'TglDoc',
                                        trs_mutasi_koreksi.created_at AS 'TimeDoc',
                                        trs_mutasi_koreksi.produk AS 'Kode' ,
                                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                        Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        (qty * -1) AS 'qty_out',
                                        batch AS 'BatchNo',
                                        exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                                    trs_mutasi_koreksi.`produk` = '$produk'AND 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '$first_date' AND '$end_date'
                                  

                                  ORDER BY TglDoc,TimeDoc,NoDoc ASC
                    ) zz")->row();
        return $query->saldo_akhir;
    }

}