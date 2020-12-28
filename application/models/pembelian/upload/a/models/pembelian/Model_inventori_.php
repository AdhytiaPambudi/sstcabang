<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");
class Model_inventori extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->model('Model_main');
            $this->load->library('excel');
            $this->load->helper('download');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

     public function listDataInvSum($bySearch=null,$byLimit=null)
    {   
        if(date('t') != '31'){
          $tigabulan = date('Y-m-01', strtotime('-3 months'));
          $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
          $duabulan = date('Y-m-01', strtotime('-2 months'));
          $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
          $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
          $satubulan = date('Y-m-t', strtotime('-1 months'));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }else{
            $daynumber = date('d');
            if($daynumber <= 5){
                $satu  = date('Y-m-01',strtotime("-10 days"));
                $dua  = date('Y-m-01',strtotime("-36 days"));
                $tiga  = date('Y-m-01',strtotime("-70 days"));
            }else if($daynumber > 5 and $daynumber < 15){
                $satu  = date('Y-m-01',strtotime("-15 days"));
                $dua  = date('Y-m-01',strtotime("-46 days"));
                $tiga  = date('Y-m-01',strtotime("-77 days"));
            }else if($daynumber >= 15 and $daynumber < 20){
                $satu  = date('Y-m-01',strtotime("-21 days"));
                $dua  = date('Y-m-01',strtotime("-52 days"));
                $tiga  = date('Y-m-01',strtotime("-83 days"));
                // $curclose  = date('Y-m-d',strtotime("-21 days"));
            }else if($daynumber >= 20 and $daynumber < 25){
                $satu  = date('Y-m-01',strtotime("-26 days"));
                $dua  = date('Y-m-01',strtotime("-57 days"));
                $tiga  = date('Y-m-01',strtotime("-88 days"));
                // $curclose  = date('Y-m-d',strtotime("-26 days"));
            }else if($daynumber >= 25){
                $satu  = date('Y-m-01',strtotime("-32 days"));
                $dua  = date('Y-m-01',strtotime("-63 days"));
                $tiga  = date('Y-m-01',strtotime("-94 days"));
                // $curclose  = date('Y-m-d',strtotime("-32 days"));
            }
          $tigabulan = date('Y-m-01', strtotime($tiga));
          $tigabulan_akhir = date('Y-m-t', strtotime($tiga));

          $duabulan = date('Y-m-01', strtotime($dua));
          $duabulan_akhir = date('Y-m-t', strtotime($dua));

          $satubulan_awal = date('Y-m-01', strtotime($satu));
          $satubulan = date('Y-m-t', strtotime($satu));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }
        $this->Model_main->getcogssummary();
        $query = $this->db->query("SELECT DISTINCT trs_invsum.`Tahun`,
                                           trs_invsum.`Cabang`,
                                           trs_invsum.`KodePrinsipal`,
                                           trs_invsum.`NamaPrinsipal`,
                                           trs_invsum.`Pabrik`,
                                           trs_invsum.`KodeProduk`,
                                           trs_invsum.`NamaProduk`,
                                           IFNULL(trs_invsum.`UnitStok`,0) AS 'UnitStok',
                                           IFNULL(trs_invsum.`ValueStok`,0) AS 'ValueStok',
                                           trs_invsum.`Gudang`,
                                           ROUND(IFNULL(trs_invsum.`Indeks`,''),2) AS 'Indeks',
                                           trs_invsum.`UnitCOGS`,
                                           trs_invsum.`HNA`,
                                           (case when trs_invsum.`Gudang` = 'Baik' then QtyGIT else 0 end) as 'QtyGIT' ,
                                           (case when trs_invsum.`Gudang` = Qtyjualtigabulan then 0 else 0 end) as 'qtyfakturtigabulan' ,
                                           (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualtigabulan else 0 end) as 'qtyfakturtigabulan' ,
                                           (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualduabulan else 0 end) as 'qtyfakturduabulan' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualsatubulan else 0 end) as 'qtyfaktursatubulan' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then QtyAvgjual else 0 end) as 'Rata2jual' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((UnitStok+IFNULL(QtyGIT,0))/IFNULL(QtyAvgjual,0)),2)) else 0 end) AS 'rasio',
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((UnitStok/IFNULL(QtyAvgjual,0))*30),2)) else 0 end)  AS 'umur_stok',
                                           (case when trs_invsum.`Gudang` = 'Baik' then ((0.75 + ((SELECT Ltime FROM mst_cabang WHERE Cabang = '".$this->cabang."')/30))) else 0 end) AS 'SL',
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((IFNULL((0.75 + ((SELECT Ltime FROM mst_cabang WHERE Cabang = '".$this->cabang."')/30)),0) - IFNULL(((UnitStok+IFNULL(QtyGIT,0))/IFNULL(QtyAvgjual,0)),0)) * IFNULL(QtyAvgjual,0)),0))  else 0 end)  AS 'Pesan'
                                      FROM trs_invsum 
                                      LEFT JOIN
                                     (SELECT sgit.produk,SUM(IFNULL(sgit.QtyGIT,0)) AS QtyGIT 
                                       FROM ( SELECT produk,(CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 THEN IFNULL(Sisa_PO,'') ELSE (IFNULL(Qty_PO,'') + IFNULL(Bonus,'')) END) AS QtyGIT 
                                              FROM `trs_po_detail` left join mst_prinsipal
                                              on trs_po_detail.Prinsipal = mst_prinsipal.Prinsipal
                                              WHERE cabang='".$this->cabang."' AND 
                                                    ifnull(statusGIT,'') = 'Open' AND 
                                                    (case when mst_prinsipal.umur_GIT = '0' then trs_po_detail.`Tgl_PO` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '1' then trs_po_detail.`Tgl_PO` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '2' then trs_po_detail.`Tgl_PO` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' else
                                                      trs_po_detail.`Tgl_PO` <= '".$bulanini_akhir."'  end)
                                      UNION ALL
                                    SELECT Produk, Banyak AS QtyGIT
                                    FROM trs_delivery_order_detail left join mst_prinsipal
                                    ON trs_delivery_order_detail.`Prinsipal` = mst_prinsipal.Prinsipal
                                    WHERE trs_delivery_order_detail.`Cabang` = '".$this->cabang."' AND 
                                          trs_delivery_order_detail.`Status` = 'Open' AND 
                                          LEFT(trs_delivery_order_detail.NoPO,2) ='SP' AND 
                                          (CASE WHEN mst_prinsipal.umur_GIT = '0' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '1' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '2' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' ELSE
                                                trs_delivery_order_detail.`TglDokumen` <= '".$bulanini_akhir."' END) ) AS sgit
                                       GROUP BY sgit.produk) AS GIT ON GIT.produk = trs_invsum.KodeProduk
                                      LEFT JOIN
                                      (select * from mst_data_ams3 ) AS ams3 
                                         ON ams3.Produk = trs_invsum.KodeProduk
                                     WHERE trs_invsum.Cabang ='".$this->cabang."' AND 
                                           trs_invsum.gudang='Baik' and tahun = '".date('Y')."'
                                     HAVING (UnitStok) != 0 OR (IFNULL(QtyGIT,0)) > 0 OR (IFNULL(Rata2jual,0)) > 0
                                    ORDER BY trs_invsum.`NamaProduk` ASC $byLimit; ")->result();

        return $query;
    }

    public function listDataInvSum2($bySearch=null,$byLimit=null)
    {   
        if(date('t') != '31'){
          $tigabulan = date('Y-m-01', strtotime('-3 months'));
          $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
          $duabulan = date('Y-m-01', strtotime('-2 months'));
          $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
          $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
          $satubulan = date('Y-m-t', strtotime('-1 months'));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }else{
            $daynumber = date('d');
            if($daynumber <= 5){
                $satu  = date('Y-m-01',strtotime("-10 days"));
                $dua  = date('Y-m-01',strtotime("-36 days"));
                $tiga  = date('Y-m-01',strtotime("-70 days"));
            }else if($daynumber > 5 and $daynumber < 15){
                $satu  = date('Y-m-01',strtotime("-15 days"));
                $dua  = date('Y-m-01',strtotime("-46 days"));
                $tiga  = date('Y-m-01',strtotime("-77 days"));
            }else if($daynumber >= 15 and $daynumber < 20){
                $satu  = date('Y-m-01',strtotime("-21 days"));
                $dua  = date('Y-m-01',strtotime("-52 days"));
                $tiga  = date('Y-m-01',strtotime("-83 days"));
                // $curclose  = date('Y-m-d',strtotime("-21 days"));
            }else if($daynumber >= 20 and $daynumber < 25){
                $satu  = date('Y-m-01',strtotime("-26 days"));
                $dua  = date('Y-m-01',strtotime("-57 days"));
                $tiga  = date('Y-m-01',strtotime("-88 days"));
                // $curclose  = date('Y-m-d',strtotime("-26 days"));
            }else if($daynumber >= 25){
                $satu  = date('Y-m-01',strtotime("-32 days"));
                $dua  = date('Y-m-01',strtotime("-63 days"));
                $tiga  = date('Y-m-01',strtotime("-94 days"));
                // $curclose  = date('Y-m-d',strtotime("-32 days"));
            }
          $tigabulan = date('Y-m-01', strtotime($tiga));
          $tigabulan_akhir = date('Y-m-t', strtotime($tiga));

          $duabulan = date('Y-m-01', strtotime($dua));
          $duabulan_akhir = date('Y-m-t', strtotime($dua));

          $satubulan_awal = date('Y-m-01', strtotime($satu));
          $satubulan = date('Y-m-t', strtotime($satu));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }
        $this->Model_main->getcogssummary();
        $query = $this->db->query("SELECT DISTINCT trs_invsum.`Tahun`,
                                           trs_invsum.`Cabang`,
                                           trs_invsum.`KodePrinsipal`,
                                           trs_invsum.`NamaPrinsipal`,
                                           trs_invsum.`Pabrik`,
                                           trs_invsum.`KodeProduk`,
                                           trs_invsum.`NamaProduk`,
                                           IFNULL(trs_invsum.`UnitStok`,0) AS 'UnitStok',
                                           IFNULL(trs_invsum.`ValueStok`,0) AS 'ValueStok',
                                           trs_invsum.`Gudang`,
                                           ROUND(IFNULL(trs_invsum.`Indeks`,''),2) AS 'Indeks',
                                           trs_invsum.`UnitCOGS`,
                                           trs_invsum.`HNA`,
                                           (case when trs_invsum.`Gudang` = 'Baik' then QtyGIT else 0 end) as 'QtyGIT' ,
                                           (case when trs_invsum.`Gudang` = Qtyjualtigabulan then 0 else 0 end) as 'qtyfakturtigabulan' ,
                                           (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualtigabulan else 0 end) as 'qtyfakturtigabulan' ,
                                           (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualduabulan else 0 end) as 'qtyfakturduabulan' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then Qtyjualsatubulan else 0 end) as 'qtyfaktursatubulan' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then QtyAvgjual else 0 end) as 'Rata2jual' ,
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((UnitStok+IFNULL(QtyGIT,0))/IFNULL(QtyAvgjual,0)),2)) else 0 end) AS 'rasio',
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((UnitStok/IFNULL(QtyAvgjual,0))*30),2)) else 0 end)  AS 'umur_stok',
                                           (case when trs_invsum.`Gudang` = 'Baik' then ((0.75 + ((SELECT Ltime FROM mst_cabang WHERE Cabang = '".$this->cabang."')/30))) else 0 end) AS 'SL',
                                          (case when trs_invsum.`Gudang` = 'Baik' then (ROUND(((IFNULL((0.75 + ((SELECT Ltime FROM mst_cabang WHERE Cabang = '".$this->cabang."')/30)),0) - IFNULL(((UnitStok+IFNULL(QtyGIT,0))/IFNULL(QtyAvgjual,0)),0)) * IFNULL(QtyAvgjual,0)),0))  else 0 end)  AS 'Pesan'
                                      FROM trs_invsum 
                                      LEFT JOIN
                                     (SELECT sgit.produk,SUM(IFNULL(sgit.QtyGIT,0)) AS QtyGIT 
                                       FROM ( SELECT produk,(CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 THEN IFNULL(Sisa_PO,'') ELSE (IFNULL(Qty_PO,'') + IFNULL(Bonus,'')) END) AS QtyGIT 
                                              FROM `trs_po_detail` left join mst_prinsipal
                                              on trs_po_detail.Prinsipal = mst_prinsipal.Prinsipal
                                              WHERE cabang='".$this->cabang."' AND 
                                                    ifnull(statusGIT,'') = 'Open' AND 
                                                    (case when mst_prinsipal.umur_GIT = '0' then trs_po_detail.`Tgl_PO` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '1' then trs_po_detail.`Tgl_PO` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '2' then trs_po_detail.`Tgl_PO` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' else
                                                      trs_po_detail.`Tgl_PO` <= '".$bulanini_akhir."'  end) 
                                        UNION ALL
                                    SELECT Produk, Banyak AS QtyGIT
                                    FROM trs_delivery_order_detail left join mst_prinsipal
                                    ON trs_delivery_order_detail.`Prinsipal` = mst_prinsipal.Prinsipal
                                    WHERE trs_delivery_order_detail.`Cabang` = '".$this->cabang."' AND 
                                          trs_delivery_order_detail.`Status` = 'Open' AND 
                                          LEFT(trs_delivery_order_detail.NoPO,2) ='SP' AND 
                                          (CASE WHEN mst_prinsipal.umur_GIT = '0' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '1' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '2' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' ELSE
                                                trs_delivery_order_detail.`TglDokumen` <= '".$bulanini_akhir."' END)) AS sgit
                                       GROUP BY sgit.produk) AS GIT ON GIT.produk = trs_invsum.KodeProduk
                                      LEFT JOIN
                                       (select * from mst_data_ams3 ) AS ams3 
                                         ON ams3.Produk = trs_invsum.KodeProduk
                                     WHERE trs_invsum.Cabang ='".$this->cabang."' and tahun ='".date('Y')."'
                                     HAVING (UnitStok) != 0 OR (IFNULL(QtyGIT,0)) > 0 OR (IFNULL(Rata2jual,0)) > 0
                                    ORDER BY trs_invsum.`NamaProduk` ASC $byLimit; ")->result();

        return $query;
    }

    public function listDataInvDet($bySearch=null,$byLimit=null)
    {   
        $this->Model_main->getcogssummary();
        $query = $this->db->query("select trs_invdet.`Tahun`,
                                          trs_invdet.`Cabang`,
                                          trs_invdet.`KodePrinsipal`,
                                          trs_invdet.`NamaPrinsipal`,
                                          trs_invdet.`Pabrik`,
                                          trs_invdet.`KodeProduk`,
                                          trs_invdet.`NamaProduk`,
                                          IFNULL(trs_invdet.`UnitStok`,0) as 'UnitStok',
                                          IFNULL(trs_invdet.`ValueStok`,0) as 'ValueStok',
                                          trs_invdet.`BatchNo`,
                                          trs_invdet.`ExpDate`,
                                          trs_invdet.`Gudang`,
                                          trs_invdet.`UnitCOGS`,
                                          trs_invdet.`NoDokumen`
                                    from trs_invdet 
                                    where trs_invdet.Cabang = '".$this->cabang."' and tahun ='".date('Y')."' and
                                         IFNULL(trs_invdet.UnitStok,0) <> 0 
                                    order by trs_invdet.`NamaProduk` ASC $byLimit")->result();

        return $query;
    }

    public function listDataInvHis()
    {   
        $query = $this->db->query("select * from trs_invhis where Cabang = '".$this->cabang."'")->result();

        return $query;
    }

    public function listDataSettlement()
    {   
        $query = $this->db->query("select * from trs_settlement_stok_day where Cabang = '".$this->cabang."' and Status = 'Ok'")->result();

        return $query;
    }


    

    // public function setSettlement($params = null)
    // {
    //     $list = $params;
    //     $date = date('d');
    //     $lastdate = date('t');
    //     $cek = $this->db->query("
    //                               select count(KodeProduk) as 'KodeProduk' from trs_settlement_stok_day where tanggal = CURDATE()")->result();
    //     if($cek[0]->KodeProduk > 0){
    //       $message = "data_ada";
    //       $valid   = true;
    //     }else{
    //       foreach ($list as $status) {
    //         $stok   = $status["qty_stok"];
    //         $sakhir = $status["saldo_akhir"];
    //         if($stok != $sakhir){
    //           $message = "no_balance";
    //           $valid = true;
    //           break; 
    //         }
    //       }
    //       foreach ($list as $key) 
    //       { 
             
    //         if($date != $lastdate ){
    //           $this->db->set("Cabang", $this->cabang);
    //           $this->db->set("KodeProduk", $key["Kode_Produk"]);
    //           $this->db->set("NamaProduk", $key["Produk"]);
    //           $this->db->set("satuan", $key["Satuan"]);
    //           $this->db->set("SAwal", $key["saldo_awal"]);
    //           $this->db->set("BPB", $key["qty_terima"]);
    //           $this->db->set("DOFaktur", $key["qty_do"]);  
    //           $this->db->set("Retur", $key["qty_retur"]);
    //           $this->db->set("adjustment_plus", $key["qty_plus"]);
    //           $this->db->set("adjustment_min", $key["qty_min"]);
    //           $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
    //           $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
    //           $this->db->set("SAkhir", $key["saldo_akhir"]);
    //           $this->db->set("UnitStok", $key["qty_stok"]);
    //           $this->db->set("Status", "ok");
    //           $this->db->set("tanggal", date('Y-m-d'));
    //           $this->db->set("time_settlement", date('Y-m-d H:i:s'));
    //           $this->db->set("create_by", $this->session->userdata('username'));
    //           $this->db->set("create_date",  date('Y-m-d H:i:s'));
    //           $valid = $this->db->insert('trs_settlement_stok_day'); 
    //           $message = "sukses_day";
    //         }else{
    //           $this->db->set("Cabang", $this->cabang);
    //           $this->db->set("KodeProduk", $key["Kode_Produk"]);
    //           $this->db->set("NamaProduk", $key["Produk"]);
    //           $this->db->set("satuan", $key["Satuan"]);
    //           $this->db->set("SAwal", $key["saldo_awal"]);
    //           $this->db->set("BPB", $key["qty_terima"]);
    //           $this->db->set("DOFaktur", $key["qty_do"]);  
    //           $this->db->set("Retur", $key["qty_retur"]);
    //           $this->db->set("adjustment_plus", $key["qty_plus"]);
    //           $this->db->set("adjustment_min", $key["qty_min"]);
    //           $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
    //           $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
    //           $this->db->set("SAkhir", $key["saldo_akhir"]);
    //           $this->db->set("UnitStok", $key["qty_stok"]);
    //           $this->db->set("Status", "ok");
    //           $this->db->set("tanggal", date('Y-m-d'));
    //           $this->db->set("time_settlement", date('Y-m-d H:i:s'));
    //           $this->db->set("create_by", $this->session->userdata('username'));
    //           $this->db->set("create_date",  date('Y-m-d H:i:s'));
    //           $valid = $this->db->insert('trs_settlement_stok_day'); 

    //           $this->db->set("Cabang", $this->cabang);
    //           $this->db->set("tahun", date('Y'));
    //           $this->db->set("bulan", date('m'));
    //           $this->db->set("KodeProduk", $key["Kode_Produk"]);
    //           $this->db->set("NamaProduk", $key["Produk"]);
    //           $this->db->set("satuan", $key["Satuan"]);
    //           $this->db->set("SAwal", $key["saldo_awal"]);
    //           $this->db->set("BPB", $key["qty_terima"]);
    //           $this->db->set("DOFaktur", $key["qty_do"]);  
    //           $this->db->set("Retur", $key["qty_retur"]);
    //           $this->db->set("adjustment_plus", $key["qty_plus"]);
    //           $this->db->set("adjustment_min", $key["qty_min"]);
    //           $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
    //           $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
    //           $this->db->set("SAkhir", $key["saldo_akhir"]);
    //           $this->db->set("UnitStok", $key["qty_stok"]);
    //           $this->db->set("Status", "ok");
    //           $this->db->set("tanggal", date('Y-m-d'));
    //           $this->db->set("time_settlement", date('Y-m-d H:i:s'));
    //           $this->db->set("create_by", $this->session->userdata('username'));
    //           $this->db->set("create_date",  date('Y-m-d H:i:s'));
    //           $valid = $this->db->insert('trs_settlement_stok_month'); 
    //           $message = "sukses_month";
    //         }
    //       }

    //     }
    //     $data = ["update" =>$valid,"message"=>$message];
    //     return $data;
    // }
    public function setSettlement($params = null,$tipe= null)
    {
        $valid=TRUE;
        $list = $params;
        $date = date('d');
        $lastdate = date('t');
        if($tipe == 'daily'){
            $cek = $this->db->query("
                                  select count(ifnull(KodeProduk,'')) as 'KodeProduk' from trs_settlement_stok_day where tanggal = CURDATE()")->result();
            if($cek[0]->KodeProduk > 0){
              $message = "data_ada";
              $valid   =FALSE;
            }else{
              foreach ($list as $status) {
                $stok   = $status["qty_stok"];
                $sakhir = $status["saldo_akhir"];
                if($stok != $sakhir){
                  $message = "no_balance";
                  $valid = FALSE;
                  break; 
                }
              }
            }

            if($valid==TRUE){
              foreach ($list as $key) 
              { 
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodeProduk", $key["Kode_Produk"]);
                $this->db->set("NamaProduk", $key["Produk"]);
                $this->db->set("satuan", $key["Satuan"]);
                $this->db->set("SAwal", $key["saldo_awal"]);
                $this->db->set("BPB", $key["qty_terima"]);
                $this->db->set("DOFaktur", $key["qty_do"]);  
                $this->db->set("Retur", $key["qty_retur"]);
                $this->db->set("adjustment_plus", $key["qty_plus"]);
                $this->db->set("adjustment_min", $key["qty_min"]);
                $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
                $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
                $this->db->set("SAkhir", $key["saldo_akhir"]);
                $this->db->set("UnitStok", $key["qty_stok"]);
                $this->db->set("Status", "ok");
                $this->db->set("tanggal", date('Y-m-d'));
                $this->db->set("time_settlement", date('Y-m-d H:i:s'));
                $this->db->set("create_by", $this->session->userdata('username'));
                $this->db->set("create_date",  date('Y-m-d H:i:s'));
                $valid = $this->db->insert('trs_settlement_stok_day'); 
                $message = "sukses_day";

              }
            }
        }else{
            $cek = $this->db->query("
                                  select count(ifnull(KodeProduk,'')) as 'KodeProduk' from trs_settlement_stok_month where bulan = MONTH(CURDATE()) and tahun = YEAR(CURDATE()) ")->result();
            if($cek[0]->KodeProduk > 0){
              $message = "data_ada";
              $valid   =FALSE;
            }else{
              foreach ($list as $status) {
                $stok   = $status["qty_stok"];
                $sakhir = $status["saldo_akhir"];
                if($stok != $sakhir){
                  $message = "no_balance";
                  $valid = FALSE;
                  break; 
                }
              }
            }
            if($valid==TRUE){
              foreach ($list as $key) 
              { 
                // $this->db->set("Cabang", $this->cabang);
                // $this->db->set("KodeProduk", $key["Kode_Produk"]);
                // $this->db->set("NamaProduk", $key["Produk"]);
                // $this->db->set("satuan", $key["Satuan"]);
                // $this->db->set("SAwal", $key["saldo_awal"]);
                // $this->db->set("BPB", $key["qty_terima"]);
                // $this->db->set("DOFaktur", $key["qty_do"]);  
                // $this->db->set("Retur", $key["qty_retur"]);
                // $this->db->set("adjustment_plus", $key["qty_plus"]);
                // $this->db->set("adjustment_min", $key["qty_min"]);
                // $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
                // $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
                // $this->db->set("SAkhir", $key["saldo_akhir"]);
                // $this->db->set("UnitStok", $key["qty_stok"]);
                // $this->db->set("Status", "ok");
                // $this->db->set("tanggal", date('Y-m-d'));
                // $this->db->set("time_settlement", date('Y-m-d H:i:s'));
                // $this->db->set("create_by", $this->session->userdata('username'));
                // $this->db->set("create_date",  date('Y-m-d H:i:s'));
                // $valid = $this->db->insert('trs_settlement_stok_day'); 

                $this->db->set("Cabang", $this->cabang);
                $this->db->set("tahun", date('Y'));
                $this->db->set("bulan", date('m'));
                $this->db->set("KodeProduk", $key["Kode_Produk"]);
                $this->db->set("NamaProduk", $key["Produk"]);
                $this->db->set("satuan", $key["Satuan"]);
                $this->db->set("SAwal", $key["saldo_awal"]);
                $this->db->set("BPB", $key["qty_terima"]);
                $this->db->set("DOFaktur", $key["qty_do"]);  
                $this->db->set("Retur", $key["qty_retur"]);
                $this->db->set("adjustment_plus", $key["qty_plus"]);
                $this->db->set("adjustment_min", $key["qty_min"]);
                $this->db->set("relokasi_terima", $key["qty_relokasi_terima"]);
                $this->db->set("relokasi_kirim", $key["qty_relokasi_kirim"]);
                $this->db->set("SAkhir", $key["saldo_akhir"]);
                $this->db->set("UnitStok", $key["qty_stok"]);
                $this->db->set("Status", "ok");
                $this->db->set("tanggal", date('Y-m-d'));
                $this->db->set("time_settlement", date('Y-m-d H:i:s'));
                $this->db->set("create_by", $this->session->userdata('username'));
                $this->db->set("create_date",  date('Y-m-d H:i:s'));
                $valid = $this->db->insert('trs_settlement_stok_month'); 

                $this->db->set("tgl_settlement_month", date('Y-m-t'));
                $this->db->set("time_settlement_month", date('Y-m-d H:i:s'));
                $valid = $this->db->update('mst_closing'); 

                $message = "sukses_month";

              }
            }
        }
        $data = ["update" =>$valid,"message"=>$message];
        return $data;
    }

    public function listDataSettlementKas()
    {   
        $query = $this->db->query("select * from trs_settlement_kasbank_day where Cabang = '".$this->cabang."' and Status = 'Ok' and tanggal = CURDATE()")->result();

        return $query;
    }

    // public function setSettlementKas()
    // {
    //     $this->db->query("truncate table temp_settlement_kas_bank");
    //     $query = $this->db->query("insert into temp_settlement_kas_bank 
    //         select a.TipeTransaksi, a.Transaksi, a.KasBank, a.Pelunasan, case when a.KasBank != a.Pelunasan then 'Selisih' end as status, (a.KasBank - a.Pelunasan) as Selisih, a.Cabang, a.Bulan, a.Tahun
    //         from
    //         (select x.TipeTransaksi, x.Transaksi, x.KasBank, y.Pelunasan, x.Cabang, x.Bulan, x.Tahun
    //         from
    //           (
    //           (select 
    //             a.Kategori as TipeTransaksi,
    //             a.Transaksi,
    //             sum(a.Jumlah) as KasBank,
    //             a.Cabang,
    //             month(a.Tanggal) as Bulan,
    //             year(a.Tanggal) as Tahun 
    //           from
    //             trs_buku_transaksi a
    //           where a.Transaksi in (
    //               'Transfer Pelanggan',
    //               'Pencairan Giro',
    //               'Penerimaan Piutang PID Tunai'
    //             ) 
    //           group by a.Cabang,
    //             a.Transaksi,
    //             Tahun,
    //             Bulan) as x
    //           join 
    //             (select 
    //               b.Cabang,
    //               year(b.TglPelunasan) as Tahun,
    //               month(b.TglPelunasan) as Bulan,
    //               sum(b.ValuePelunasan) as Pelunasan,
    //               case
    //                 when b.TipePelunasan = 'Transfer' then 'Transfer Pelanggan'
    //                 when b.TipePelunasan = 'Giro' then 'Pencairan Giro'
    //                 when b.TipePelunasan = 'Cash' then 'Penerimaan Piutang PID Tunai'
    //               end as Transaksi
    //             from
    //               trs_pelunasan_detail b
    //             where b.Status not in ('GiroTolak', 'GiroBatal')
    //             group by b.Cabang,
    //             Transaksi,
    //             Tahun,
    //             Bulan) as y    
    //           )
    //          where
    //          x.Transaksi = y.Transaksi
    //          ) as a");

    //     $query = $this->db->query("insert into temp_settlement_kas_bank 
    //         select a.TipeTransaksi, a.Transaksi, a.KasBank, a.Pelunasan, case when a.KasBank != a.Pelunasan then 'Selisih' end as status, (a.KasBank - a.Pelunasan) as Selisih, a.Cabang, a.Bulan, a.Tahun
    //         from
    //         (select x.TipeTransaksi, x.Transaksi, x.KasBank, y.Pelunasan, x.Cabang, x.Bulan, x.Tahun
    //         from
    //           (
    //           (select 
    //             a.Kategori as TipeTransaksi,
    //             a.Transaksi,
    //             sum(a.Jumlah) as KasBank,
    //             a.Cabang,
    //             month(a.Tanggal) as Bulan,
    //             year(a.Tanggal) as Tahun 
    //           from
    //             trs_buku_transaksi a
    //           where a.Transaksi = 'Penolakan Giro' 
    //           group by a.Cabang,
    //             a.Transaksi,
    //             Tahun,
    //             Bulan) as x
    //           join 
    //             (select 
    //               b.Cabang,
    //               year(b.TglPelunasan) as Tahun,
    //               month(b.TglPelunasan) as Bulan,
    //               sum(b.ValuePelunasan) as Pelunasan,
    //               'Penolakan Giro' as Transaksi 
    //             from
    //               trs_pelunasan_detail b
    //             where b.Status = 'GiroTolak' 
    //             group by b.Cabang,
    //             Transaksi,
    //             Tahun,
    //             Bulan) as y    
    //           )
    //          where
    //          x.Transaksi = y.Transaksi
    //          ) as a");

    //     return $query;
    // }

    public function saldoawal($produk = NULL,$getmonth = NULL,$getyear = NULL){
        $query = $this->db->query("
                                  SELECT KodeProduk AS Kode_Produk, NamaProduk AS Produk,
                                  (case '".$getmonth."' 
                                   when '01' then sum(SAwal01)
                                   when '02' then sum(SAwal02)
                                   when '03' then sum(SAwal03)
                                   when '04' then sum(SAwal04)
                                   when '05' then sum(SAwal05)
                                   when '06' then sum(SAwal06)
                                   when '07' then sum(SAwal07)
                                   when '08' then sum(SAwal08)
                                   when '09' then sum(SAwal09)
                                   when '10' then sum(SAwal10)
                                   when '11' then sum(SAwal11)
                                   when '12' then sum(SAwal12)
                                   else 0 end) AS 'saldo_awal' 
                                  FROM trs_invsum 
                                  WHERE Cabang = '".$this->cabang."' and KodeProduk = '".$produk."' and Tahun ='".$getyear."'
                                  GROUP BY KodeProduk
                                  ORDER BY KodeProduk ASC;

                    ")->result();
        return $query;
    }

    public function saldoawaldaily($produk = NULL,$getmonth = NULL,$first_date=NULL,$before_day=NULL){
      $sawal = $this->db->query("SELECT KodeProduk,
                                          (CASE '".$getmonth."' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '".$this->cabang."' and KodeProduk = '".$produk."'
                                     GROUP BY KodeProduk")->result();
       $terima = $this->db->query("SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          Status not in ('pending') and
                                          ifnull(Tipe,'') != 'BKB' and 
                                          TglDokumen BETWEEN '".$first_date."' AND '".$before_day."' and Produk = '".$produk."' GROUP BY Produk")->result();
       $returbeli = $this->db->query("SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus) * -1,0) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          ifnull(Tipe,'') = 'BKB' and 
                                          TglDokumen BETWEEN '".$first_date."' AND '".$before_day."' and Produk = '".$produk."' GROUP BY Produk")->result();
      $Rterima = $this->db->query("SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                    FROM trs_relokasi_terima_detail
                                   WHERE  Cabang = '".$this->cabang."' AND 
                                          Status = 'Terima' and
                                          Tgl_terima BETWEEN '".$first_date."' AND '".$before_day."' and Produk = '".$produk."' GROUP BY Produk")->result();
      $kor_plus = $this->db->query("SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '".$this->cabang."' AND
                                           status = 'Approve' and 
                                           IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$before_day."' and produk = '".$produk."'
                                   GROUP BY produk")->result();

      $retur = $this->db->query("SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          Cabang = '".$this->cabang."' AND
                                          TglFaktur BETWEEN '".$first_date."' AND '".$before_day."' and KodeProduk = '".$produk."'
                                    GROUP BY KodeProduk")->result();

      $DO = $this->db->query("SELECT KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'                            
                                       FROM trs_delivery_order_sales_detail join trs_delivery_order_sales on 
                                            trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO
                                      WHERE  trs_delivery_order_sales.Cabang = '".$this->cabang."' AND
                                             trs_delivery_order_sales.Status != 'Batal' and
                                             trs_delivery_order_sales.TglDO BETWEEN '".$first_date."' AND '".$before_day."' and KodeProduk = '".$produk."'
                                   GROUP BY KodeProduk")->result();

      $Rkirim = $this->db->query("SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                    FROM trs_relokasi_kirim_detail join trs_relokasi_kirim_header 
                                    on  trs_relokasi_kirim_detail.No_Relokasi = trs_relokasi_kirim_header.No_Relokasi and 
                                        trs_relokasi_kirim_detail.Cabang = trs_relokasi_kirim_header.Cabang
                                   WHERE  trs_relokasi_kirim_header.Cabang = '".$this->cabang."' AND 
                                          trs_relokasi_kirim_header.Status_kiriman = 'Kirim' and
                                          trs_relokasi_kirim_header.Tgl_kirim BETWEEN '".$first_date."' AND '".$before_day."' and Produk = '".$produk."'
                                    GROUP BY Produk")->result();

      $kor_min = $this->db->query("SELECT produk,
                                            IFNULL(SUM(Qty),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                                        status = 'Approve' and 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$before_day."' and produk = '".$produk."'
                                  GROUP BY produk ")->result();

      $sawal    = (!empty($sawal[0]->saldo_awal) ? $sawal[0]->saldo_awal :0); 
      $terima   = (!empty($terima[0]->qty) ? $terima[0]->qty :0);
      $returbeli   = (!empty($returbeli[0]->qty) ? $returbeli[0]->qty :0);  
      $Rterima  = (!empty($Rterima[0]->qty) ? $Rterima[0]->qty :0);
      $kor_plus = (!empty($kor_plus[0]->qty) ? $kor_plus[0]->qty :0);
      $retur    = (!empty($retur[0]->qty) ? $retur[0]->qty :0);
      $DO       = (!empty($DO[0]->qty) ? $DO[0]->qty :0);
      $Rkirim   = (!empty($Rkirim[0]->qty) ? $Rkirim[0]->qty :0);
      $kor_min  = (!empty($kor_min[0]->qty) ? $kor_min[0]->qty :0);

      $query = $sawal + $terima + $kor_plus + $retur - $DO - $Rkirim - $kor_min - $returbeli;
      return $query;
    }

    public function getstokdetail($produk = NULL){
        $query = $this->db->query("
                                 SELECT mst_produk.Kode_Produk,
                                         mst_produk.Produk,
                                         mst_produk.Satuan,
                                         gudang.Gudang AS 'gudang_summary',
                                         gudang.UnitStok AS 'Stok_summary',
                                         gudang.gudang_detail AS 'gudang_detail',
                                         gudang.stok_detail AS 'Stok_detail'
                                  FROM mst_produk LEFT JOIN (
                                  SELECT trs_invsum.KodeProduk,
                                         trs_invsum.Gudang,
                                         trs_invsum.UnitStok AS 'UnitStok',
                                         IFNULL(detail.Gudang,'') AS 'gudang_detail',
                                         IFNULL(detail.UnitStok,0)  AS 'stok_detail'
                                   FROM trs_invsum LEFT JOIN
                                  (SELECT trs_invdet.KodeProduk,
                                                trs_invdet.Gudang,
                                                SUM(trs_invdet.UnitStok) AS 'UnitStok'
                                           FROM trs_invdet
                                           where Tahun ='".date('Y')."'
                                         GROUP BY trs_invdet.KodeProduk,
                                                trs_invdet.Gudang) AS detail ON
                                         detail.KodeProduk = trs_invsum.`KodeProduk` AND 
                                         detail.Gudang =trs_invsum.`Gudang` 
                                  where Tahun ='".date('Y')."') AS gudang ON
                                         gudang.KodeProduk = mst_produk.`Kode_Produk`
                                  WHERE mst_produk.`Kode_Produk` = '".$produk."';

                    ")->result();
        return $query;
    }

    public function getkartustok($first_date = NULL,$end_date = NULL, $produk = NULL)
    {
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen as 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          (ifnull(Qty,'') + ifnull(Bonus,'')) AS 'qty_in',
                                          0 AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  WHERE trs_terima_barang_detail.Produk = '".$produk ."' and 
                                        Status not in ('pending','Batal') and
                                        ifnull(Tipe,'') not in  ('BKB','Tolakan') and
                                        TglDokumen between '".$first_date."' and '".$end_date."'
                                  
                                  UNION ALL

                                  SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen as 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          0 AS 'qty_in',
                                          (((ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  where   ifnull(Tipe,'') = 'BKB' and Produk = '".$produk ."' and 
                                        TglDokumen between '".$first_date."' and '".$end_date."'
                                  
                                  UNION ALL
                                  SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Tolakan Retur ',Prinsipal,' - ',Supplier,' ~ Qty : ', (ifnull(Qty,'') + ifnull(Bonus,''))*-1) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen as 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          0 AS 'qty_in',
                                          0 AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  where   ifnull(Tipe,'') = 'Tolakan' and Produk = '".$produk ."' and 
                                        TglDokumen between '".$first_date."' and '".$end_date."'

                                  UNION ALL
                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Usulan Adjusment (+) : ', catatan) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
                                         trs_mutasi_koreksi.created_at as 'TimeDoc',
                                         trs_mutasi_koreksi.produk AS 'Kode' ,
                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         qty AS 'qty_in',
                                         0 AS 'qty_out',
                                         batch AS 'BatchNo',
                                         exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status IN ('Open','Approve') and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."' and 
                                        CASE WHEN trs_mutasi_koreksi.`Status` = 'Open' THEN
                                          tanggal
                                       ELSE
                                          IFNULL(DATE(tgl_approve),tanggal)
                                       END between '".$first_date."' and '".$end_date."'
                                  UNION ALL
                                  SELECT NoDokumen AS 'NoDoc', 
                                         CONCAT('Mutasi Gudang dari Gudang : ', gudang_awal,' Ke Gudang : ',gudang_akhir,' ~ QTY = ',qty) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
                                         trs_mutasi_gudang.create_date as 'TimeDoc',
                                         trs_mutasi_gudang.produk AS 'Kode' ,
                                         trs_mutasi_gudang.namaproduk AS 'NamaProduk' ,
                                         '' AS 'Unit',
                                         0 AS 'qty_in',
                                         0 AS 'qty_out',
                                         batchno_awal AS 'BatchNo',
                                         expdate_awal AS 'ExpDate'
                                  FROM  trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_gudang.status IN ('Open','Approve') and
                                    trs_mutasi_gudang.`produk` = '".$produk ."' and 
                                        tanggal between '".$first_date."' and '".$end_date."'
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Adjusment (+) : QTY = ',qty,' ', catatan) AS 'Dokumen',
                                         IFNULL(DATE(tgl_approve),tanggal) AS 'TglDoc',
                                         trs_mutasi_koreksi.created_at as 'TimeDoc',
                                         trs_mutasi_koreksi.produk AS 'Kode' ,
                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         0 AS 'qty_out',
                                         batch AS 'BatchNo',
                                         exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."' and 
                                        IFNULL(DATE(tgl_approve),tanggal) between '".$first_date."' and '".$end_date."'
                                  UNION ALL

                                  SELECT No_Terima AS 'NoDoc', 
                                         CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
                                         Tgl_terima AS 'TglDoc',
                                         Time_terima as 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         (Qty+Bonus) AS 'qty_in',
                                         0 AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_terima_detail 
                                  WHERE   (Qty > 0 or Bonus > 0) AND
                                        Status = 'Terima' and
                                        Produk = '".$produk ."' and 
                                        Tgl_terima between '".$first_date."' and '".$end_date."'
                                    
                                  UNION ALL 

                                  SELECT NoFaktur AS 'NoDoc',
                                        CONCAT('Terima dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
                                        TglFaktur AS 'TglDoc',
                                        TimeFaktur as 'TimeDoc',
                                        KodeProduk AS 'Kode', 
                                        NamaProduk AS 'NamaProduk',
                                        UOM AS 'Unit',
                                        (QtyFaktur+BonusFaktur) AS 'qty_in',
                                        0 AS 'qty_out',
                                        BatchNo,
                                        ExpDate
                                  FROM trs_faktur_detail 
                                  WHERE TipeDokumen ='Retur' AND 
                                        ifnull(status,'') != 'Batal' and
                                        KodeProduk = '".$produk ."' and 
                                        TglFaktur between '".$first_date."' and '".$end_date."'

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
                                  WHERE KodeProduk = '".$produk ."'  
                                    AND TglDO BETWEEN '".$first_date."' and '".$end_date."' and tipedokumen ='DO'
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
                                  WHERE KodeProduk = '".$produk ."' 
                                    AND Status = 'Batal' 
                                    AND date(ifnull(time_batal,'')) BETWEEN '".$first_date."' and '".$end_date."'
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
                                  WHERE KodeProduk = '".$produk ."' and 
                                        TipeDokumen ='Retur' and Status = 'Retur' 
                                    AND TglDO BETWEEN '".$first_date."' and '".$end_date."'
                                  UNION ALL

                                  SELECT trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 
                                        CASE WHEN Status = 'ApproveLog' THEN
                                         CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_detail.Cabang_Penerima)
                                        ELSE
                                          CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_detail.Cabang_Penerima)
                                        END
                                         AS 'Dokumen',
                                         CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END AS 'TglDoc',
                                         CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Time_kirim ELSE trs_relokasi_kirim_detail.App_BM_Time END  'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         CASE WHEN Status = 'ApproveLog' THEN
                                         0
                                         ELSE
                                         (Qty+Bonus)
                                         END AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima','ApproveLog') and
                                        trs_relokasi_kirim_detail.Produk = '".$produk ."' and 
                                          CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                                         between '".$first_date."' and '".$end_date."' 

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
                                  WHERE trs_relokasi_kirim_detail.Status = 'Reject' and
                                        trs_relokasi_kirim_detail.Produk = '".$produk ."' and 
                                        trs_relokasi_kirim_detail.Tgl_reject between '".$first_date."' and '".$end_date."'

                                  UNION ALL

                                  SELECT trs_usulan_retur_beli_header.No_Usulan AS 'NoDoc', 
                                        CONCAT('Usulan Retur Beli : QTY = ',((trs_usulan_retur_beli_detail.Qty+trs_usulan_retur_beli_detail.Bonus) * -1),'  ~ Ke : ',trs_usulan_retur_beli_header.Prinsipal) AS 'Dokumen',
                                        trs_usulan_retur_beli_header.Tanggal AS 'TglDoc',
                                        trs_usulan_retur_beli_header.added_time as 'TimeDoc',
                                        trs_usulan_retur_beli_detail.Produk AS 'Kode' ,
                                        trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk' ,
                                        trs_usulan_retur_beli_detail.Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        0 AS 'qty_out',
                                        trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo',
                                        trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate'
                                  FROM  trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                                  WHERE ifnull(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' and
                                    trs_usulan_retur_beli_detail.Produk = '".$produk ."'and 
                                        trs_usulan_retur_beli_header.tanggal between '".$first_date."' and '".$end_date."'
                                  
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                        CONCAT('Usulan Adjusment (-): QTY = ',(qty * -1),' ',catatan) AS 'Dokumen',
                                        tanggal AS 'TglDoc',
                                        trs_mutasi_koreksi.created_at as 'TimeDoc',
                                        trs_mutasi_koreksi.produk AS 'Kode' ,
                                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                        Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        0 AS 'qty_out',
                                        batch AS 'BatchNo',
                                        exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status IN ('Open','Approve') and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."'and 
                                        CASE WHEN trs_mutasi_koreksi.`Status` = 'Open' THEN
                                            tanggal
                                         ELSE
                                            IFNULL(DATE(tgl_approve),tanggal)
                                         END between '".$first_date."' and '".$end_date."'
                                        
                                  UNION ALL

                                  SELECT no_koreksi AS 'NoDoc', 
                                        CONCAT('Adjusment (-): ',catatan) AS 'Dokumen',
                                        IFNULL(DATE(tgl_approve),tanggal) AS 'TglDoc',
                                        trs_mutasi_koreksi.created_at as 'TimeDoc',
                                        trs_mutasi_koreksi.produk AS 'Kode' ,
                                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                        Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        (qty * -1) AS 'qty_out',
                                        batch AS 'BatchNo',
                                        exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."'and 
                                        IFNULL(DATE(tgl_approve),tanggal) between '".$first_date."' and '".$end_date."'
                                  
                                  UNION ALL 

                                  SELECT nodokumen AS 'NoDoc', 
                                        CONCAT('Mutasi Batch Dari Batch ',batchno_awal,' ke Batch ',batchno_akhir,' : QTY = ',qty) AS 'Dokumen',
                                        DATE(tanggal) AS 'TglDoc',
                                        create_date AS 'TimeDoc',
                                        trs_mutasi_batch.produk AS 'Kode' ,
                                        namaproduk AS 'NamaProduk' ,
                                        '' AS 'Unit',
                                        0 AS 'qty_in',
                                        0 AS 'qty_out',
                                        batchno_akhir AS 'BatchNo',
                                        expdate_akhir AS 'ExpDate'
                                  FROM  trs_mutasi_batch INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_batch.`produk`
                                  WHERE    trs_mutasi_batch.status = 'Approve' AND
                                    trs_mutasi_batch.`produk` = '".$produk ."'AND 
                                        DATE(tanggal) BETWEEN '".$first_date."' and '".$end_date."'
                                        
                                  ORDER BY TglDoc,TimeDoc,NoDoc ASC;")->result();
        return $query;
    }

public function getmutasistok($first_date = NULL,$end_date = NULL,$getmonth = NULL,$nextmonth=null)
{
      $year = date('Y',strtotime($first_date));
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT  DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                                         trs_invsum.NamaProduk AS Produk,
                                         CONCAT(trs_invsum.KodeProduk ,' - ',trs_invsum.NamaProduk) AS 'NamaProduk',
                                         -- mst_produk.Satuan,
                                         '' AS Satuan,
                                         IFNULL(s_awal.saldo_awal,0) AS 'saldo_awal',
                                         IFNULL(s_awalnext.saldo_awal,0) AS 'saldo_awalnext',
                                         IFNULL(terima.qty,0) AS 'qty_terima',
                                         IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                                         IFNULL(koreksi_plus.qty,0) AS 'qty_plus',
                                         IFNULL(retur.qty,0) AS 'qty_retur',
                                         IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                                         IFNULL(Delivery.qty,0) AS 'qty_do',
                                         IFNULL(returDelivery.qty,0) AS 'qty_returdo',
                                         IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                                         IFNULL(koreksi_min.qty,0) AS 'qty_min',
                                         IFNULL(stok.qty,0) AS 'qty_stok',
                                         IFNULL(stokdetail.qty,0) AS 'qty_stokdetail',
                                         IFNULL(stok_detail.Status_stok_detail,'') AS 'Status_stok_detail',
                                        (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
                                   FROM trs_invsum LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '".$nextmonth."' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '".$this->cabang."' and Tahun ='".date('Y')."'
                                     GROUP BY KodeProduk ) AS s_awalnext ON s_awalnext.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '".$getmonth."' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '".$this->cabang."' and Tahun ='".$year."'
                                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          SUM(ifnull(Qty,'') + ifnull(Bonus,'')) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          Status not in ('pending','Batal') and 
                                          ifnull(tipe,'') not in  ('BKB','Tolakan')
                                    GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          ifnull(Tipe,'') ='BKB'
                                    GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(qty),0) AS 'qty' FROM (
                                      SELECT  Produk,
                                            IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_terima_detail
                                     WHERE  Cabang = '".$this->cabang."' AND 
                                            Status = 'Terima' and
                                            Tgl_terima BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY Produk

                                      UNION ALL

                                      SELECT  Produk,
                                            IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_kirim_detail
                                     WHERE  Cabang_Pengirim = '".$this->cabang."' AND 
                                            STATUS = 'Reject' AND
                                            tgl_reject BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY Produk
                                    )zz GROUP BY zz.Produk

                                    ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                   ( SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '".$this->cabang."' AND
                                   CASE WHEN `Status` = 'Open' THEN
                                      tanggal
                                   ELSE
                                      IFNULL(DATE(tgl_approve),tanggal)
                                   END BETWEEN '".$first_date."' AND '".$end_date."' and status in ('Approve','Open')
                                   GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          ifnull(status,'') != 'Batal' and
                                          Cabang = '".$this->cabang."' AND
                                          TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' 
                                    GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status != 'Batal' and tipedokumen ='DO' 
                                    AND TglDO BETWEEN '".$first_date."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status = 'Retur' and tipedokumen ='Retur' 
                                    AND TglDO BETWEEN '".$first_date."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (
                                    SELECT Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status in ('Kirim','Reject','RejectPusat','RejectCabPenerima') and
                                        trs_relokasi_kirim_detail.Cabang_Pengirim = '".$this->cabang."' and 
                                        CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                                         between '".$first_date."' and '".$end_date."'
                                        GROUP BY Produk

                                

                                  ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                    (SELECT produk,
                                            IFNULL(SUM(Qty * -1),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                                        status = 'Approve' and 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."'
                                  GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invsum
                                   where Tahun ='".$year."'
                                   GROUP BY KodeProduk) as stok on stok.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invdet
                                   where Tahun ='".$year."'
                                   GROUP BY KodeProduk) as stokdetail on stokdetail.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         (CASE COUNT(UnitStok)
                                          WHEN 0 THEN 'Ok'
                                          ELSE 'Minus' END ) AS 'Status_stok_detail'
                                  FROM trs_invdet
                                  WHERE IFNULL(UnitStok,0) < 0 and Tahun ='".$year."'
                                  group by KodeProduk) as stok_detail on stok_detail.KodeProduk = trs_invsum.KodeProduk
                                ORDER BY trs_invsum.KodeProduk ASC")->result();
        return $query;

}
// public function getkartustokdaily($datetrans = NULL, $produk = NULL)
//     {
//        $query = $this->db->query("SELECT NoDokumen AS 'NoDoc',
//                                           CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
//                                           TglDokumen AS 'TglDoc',
//                                           TimeDokumen as 'TimeDoc',
//                                           Produk AS 'Kode',
//                                           NamaProduk AS 'NamaProduk',
//                                           Satuan AS 'Unit',
//                                           (Qty+Bonus) AS 'qty_in',
//                                           0 AS 'qty_out',
//                                           BatchNo,
//                                           ExpDate
//                                   FROM trs_terima_barang_detail
//                                   WHERE trs_terima_barang_detail.Produk = '".$produk ."' and 
//                                         Status not in ('pending') and
//                                         Tipe != 'BKB' and
//                                         TglDokumen between '".$first_date."' and '".$end_date."'
                                  
//                                   UNION

//                                   SELECT NoDokumen AS 'NoDoc',
//                                           CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
//                                           TglDokumen AS 'TglDoc',
//                                           TimeDokumen as 'TimeDoc',
//                                           Produk AS 'Kode',
//                                           NamaProduk AS 'NamaProduk',
//                                           Satuan AS 'Unit',
//                                           0 AS 'qty_in',
//                                           ((Qty+Bonus)*-1) AS 'qty_out',
//                                           BatchNo,
//                                           ExpDate
//                                   FROM trs_terima_barang_detail
//                                         Tipe = 'BKB' and
//                                         TglDokumen between '".$first_date."' and '".$end_date."'

//                                   UNION

//                                   SELECT no_koreksi AS 'NoDoc', 
//                                          CONCAT('Adjusment : ', catatan) AS 'Dokumen',
//                                          tanggal AS 'TglDoc',
//                                          trs_mutasi_koreksi.created_at as 'TimeDoc',
//                                          trs_mutasi_koreksi.produk AS 'Kode' ,
//                                          trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
//                                          Satuan AS 'Unit',
//                                          qty AS 'qty_in',
//                                          0 AS 'qty_out',
//                                          batch AS 'BatchNo',
//                                          exp_date AS 'ExpDate'
//                                   FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
//                                   WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' and
//                                     trs_mutasi_koreksi.`produk` = '".$produk ."' and 
//                                         tanggal = '".$datetrans."'
//                                   UNION

//                                   SELECT No_Terima AS 'NoDoc', 
//                                          CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
//                                          Tgl_terima AS 'TglDoc',
//                                          Time_terima as 'TimeDoc',
//                                          Produk AS 'Kode' ,
//                                          NamaProduk AS 'NamaProduk' ,
//                                          Satuan AS 'Unit',
//                                          (Qty+Bonus) AS 'qty_in',
//                                          0 AS 'qty_out',
//                                          BatchNo AS 'BatchNo',
//                                          ExpDate AS 'ExpDate'
//                                   FROM  trs_relokasi_terima_detail 
//                                   WHERE   Qty > 0 AND
//                                         Status = 'Terima' and
//                                         Produk = '".$produk ."' and 
//                                         Tgl_terima = '".$datetrans."'
                                    
//                                   UNION 

//                                   SELECT NoFaktur AS 'NoDoc',
//                                         CONCAT('Terima dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
//                                         TglFaktur AS 'TglDoc',
//                                         TimeFaktur as 'TimeDoc',
//                                         KodeProduk AS 'Kode', 
//                                         NamaProduk AS 'NamaProduk',
//                                         UOM AS 'Unit',
//                                         (QtyFaktur+BonusFaktur) AS 'qty_in',
//                                         0 AS 'qty_out',
//                                         BatchNo,
//                                         ExpDate
//                                   FROM trs_faktur_detail 
//                                   WHERE TipeDokumen ='Retur' AND 
//                                         KodeProduk = '".$produk ."' and 
//                                         TglFaktur = '".$datetrans."'

//                                   UNION

//                                  SELECT trs_delivery_order_sales.NoDO AS 'NoDoc',
//                                         CONCAT('Kirim ke ',trs_delivery_order_sales.Pelanggan, ' - ' ,trs_delivery_order_sales.NamaPelanggan) AS 'Dokumen',  
//                                         trs_delivery_order_sales.TglDO AS 'TglDoc',
//                                         trs_delivery_order_sales.TimeDO as 'TimeDoc',
//                                         KodeProduk AS 'Kode',
//                                         NamaProduk AS 'NamaProduk',
//                                         UOM AS 'Unit',
//                                         0 AS 'qty_in', 
//                                         (QtyDO+BonusDO) AS 'qty_out',
//                                         BatchNo,ExpDate
//                                   FROM trs_delivery_order_sales_detail join trs_delivery_order_sales on 
//                                        trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO
//                                   WHERE trs_delivery_order_sales_detail.KodeProduk = '".$produk ."' and 
//                                         trs_delivery_order_sales.Status != 'Batal' and
//                                         trs_delivery_order_sales.TglDO = '".$datetrans."'

//                                   UNION

//                                   SELECT trs_relokasi_kirim_header.No_Relokasi AS 'NoDoc', 
//                                          CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_header.Cabang_Penerima) AS 'Dokumen',
//                                          trs_relokasi_kirim_header.Tgl_kirim AS 'TglDoc',
//                                          trs_relokasi_kirim_header.Time_kirim 'TimeDoc',
//                                          Produk AS 'Kode' ,
//                                          NamaProduk AS 'NamaProduk' ,
//                                          Satuan AS 'Unit',
//                                          0 AS 'qty_in',
//                                          (Qty+Bonus) AS 'qty_out',
//                                          BatchNo AS 'BatchNo',
//                                          ExpDate AS 'ExpDate'
//                                   FROM  trs_relokasi_kirim_detail,trs_relokasi_kirim_header
//                                   WHERE  trs_relokasi_kirim_header.No_Relokasi =  trs_relokasi_kirim_detail.No_Relokasi and 
//                                         trs_relokasi_kirim_header.Cabang =  trs_relokasi_kirim_detail.Cabang and
//                                         trs_relokasi_kirim_detail.Qty > 0 AND
//                                         trs_relokasi_kirim_header.Status_kiriman = 'Kirim' and
//                                         trs_relokasi_kirim_detail.Produk = '".$produk ."' and 
//                                         trs_relokasi_kirim_detail.Tgl_kirim = '".$datetrans."'

//                                   UNION

//                                   SELECT no_koreksi AS 'NoDoc', 
//                                         CONCAT('Adjusment : ',catatan) AS 'Dokumen',
//                                         tanggal AS 'TglDoc',
//                                         trs_mutasi_koreksi.created_at as 'TimeDoc',
//                                         trs_mutasi_koreksi.produk AS 'Kode' ,
//                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
//                                         Satuan AS 'Unit',
//                                         0 AS 'qty_in',
//                                         qty AS 'qty_out',
//                                         batch AS 'BatchNo',
//                                         exp_date AS 'ExpDate'
//                                   FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
//                                   WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' and
//                                     trs_mutasi_koreksi.`produk` = '".$produk ."'and 
//                                         tanggal = '".$datetrans."'
//                                   ORDER BY TglDoc,TimeDoc,NoDoc ASC;")->result();
//         return $query;
//     }
public function getkartustokdaily($datetrans = NULL, $produk = NULL)
    {
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen as 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          (Qty+Bonus) AS 'qty_in',
                                          0 AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                  WHERE trs_terima_barang_detail.Produk = '".$produk ."' and 
                                        Status not in ('pending','Batal') and
                                        Tipe != 'BKB' and
                                        TglDokumen between '".$first_date."' and '".$end_date."'
                                  
                                  UNION

                                  SELECT NoDokumen AS 'NoDoc',
                                          CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                          TglDokumen AS 'TglDoc',
                                          TimeDokumen as 'TimeDoc',
                                          Produk AS 'Kode',
                                          NamaProduk AS 'NamaProduk',
                                          Satuan AS 'Unit',
                                          0 AS 'qty_in',
                                          ((Qty+Bonus)*-1) AS 'qty_out',
                                          BatchNo,
                                          ExpDate
                                  FROM trs_terima_barang_detail
                                        Tipe = 'BKB' and
                                        TglDokumen between '".$first_date."' and '".$end_date."'

                                  UNION

                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Adjusment : ', catatan) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
                                         trs_mutasi_koreksi.created_at as 'TimeDoc',
                                         trs_mutasi_koreksi.produk AS 'Kode' ,
                                         trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         qty AS 'qty_in',
                                         0 AS 'qty_out',
                                         batch AS 'BatchNo',
                                         exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."' and 
                                        tanggal = '".$datetrans."'
                                  UNION

                                  SELECT No_Terima AS 'NoDoc', 
                                         CONCAT('Relokasi Dari Cabang  : ', Cabang_Pengirim) AS 'Dokumen',
                                         Tgl_terima AS 'TglDoc',
                                         Time_terima as 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         (Qty+Bonus) AS 'qty_in',
                                         0 AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_terima_detail 
                                  WHERE   Qty > 0 AND
                                        Status = 'Terima' and
                                        Produk = '".$produk ."' and 
                                        Tgl_terima = '".$datetrans."'
                                    
                                  UNION 

                                  SELECT NoFaktur AS 'NoDoc',
                                        CONCAT('Terima dari ',Pelanggan, ' - ' ,NamaPelanggan) AS 'Dokumen',
                                        TglFaktur AS 'TglDoc',
                                        TimeFaktur as 'TimeDoc',
                                        KodeProduk AS 'Kode', 
                                        NamaProduk AS 'NamaProduk',
                                        UOM AS 'Unit',
                                        (QtyFaktur+BonusFaktur) AS 'qty_in',
                                        0 AS 'qty_out',
                                        BatchNo,
                                        ExpDate
                                  FROM trs_faktur_detail 
                                  WHERE TipeDokumen ='Retur' AND 
                                        KodeProduk = '".$produk ."' and 
                                        TglFaktur = '".$datetrans."'

                                  UNION

                                 SELECT trs_delivery_order_sales.NoDO AS 'NoDoc',
                                        CONCAT('Kirim ke ',trs_delivery_order_sales.Pelanggan, ' - ' ,trs_delivery_order_sales.NamaPelanggan) AS 'Dokumen',  
                                        trs_delivery_order_sales.TglDO AS 'TglDoc',
                                        trs_delivery_order_sales.TimeDO as 'TimeDoc',
                                        KodeProduk AS 'Kode',
                                        NamaProduk AS 'NamaProduk',
                                        UOM AS 'Unit',
                                        0 AS 'qty_in', 
                                        (QtyDO+BonusDO) AS 'qty_out',
                                        BatchNo,ExpDate
                                  FROM trs_delivery_order_sales_detail join trs_delivery_order_sales on 
                                       trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO
                                  WHERE trs_delivery_order_sales_detail.KodeProduk = '".$produk ."' and 
                                        trs_delivery_order_sales.Status != 'Batal' and
                                        trs_delivery_order_sales.TglDO = '".$datetrans."'

                                  UNION

                                  SELECT trs_relokasi_kirim_header.No_Relokasi AS 'NoDoc', 
                                         CONCAT('Relokasi ke Cabang  : ', trs_relokasi_kirim_header.Cabang_Penerima) AS 'Dokumen',
                                         trs_relokasi_kirim_header.Tgl_kirim AS 'TglDoc',
                                         trs_relokasi_kirim_header.Time_kirim 'TimeDoc',
                                         Produk AS 'Kode' ,
                                         NamaProduk AS 'NamaProduk' ,
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         (Qty+Bonus) AS 'qty_out',
                                         BatchNo AS 'BatchNo',
                                         ExpDate AS 'ExpDate'
                                  FROM  trs_relokasi_kirim_detail,trs_relokasi_kirim_header
                                  WHERE  trs_relokasi_kirim_header.No_Relokasi =  trs_relokasi_kirim_detail.No_Relokasi and 
                                        trs_relokasi_kirim_header.Cabang =  trs_relokasi_kirim_detail.Cabang and
                                        trs_relokasi_kirim_detail.Qty > 0 AND
                                        trs_relokasi_kirim_header.Status_kiriman = 'Kirim' and
                                        trs_relokasi_kirim_detail.Produk = '".$produk ."' and 
                                        trs_relokasi_kirim_detail.Tgl_kirim = '".$datetrans."'

                                  UNION

                                  SELECT no_koreksi AS 'NoDoc', 
                                        CONCAT('Adjusment : ',catatan) AS 'Dokumen',
                                        tanggal AS 'TglDoc',
                                        trs_mutasi_koreksi.created_at as 'TimeDoc',
                                        trs_mutasi_koreksi.produk AS 'Kode' ,
                                        trs_mutasi_koreksi.nama_produk AS 'NamaProduk' ,
                                        Satuan AS 'Unit',
                                        0 AS 'qty_in',
                                        qty AS 'qty_out',
                                        batch AS 'BatchNo',
                                        exp_date AS 'ExpDate'
                                  FROM  trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk`
                                  WHERE   qty < 0 AND trs_mutasi_koreksi.status = 'Approve' and
                                    trs_mutasi_koreksi.`produk` = '".$produk ."'and 
                                        tanggal = '".$datetrans."'
                                  ORDER BY TglDoc,TimeDoc,NoDoc ASC;")->result();
        return $query;
    }


    public function getsobh(){
        $cab = $this->db->query("select Kode from mst_cabang where Cabang = '".$this->cabang."' limit 1 ")->row();
        $cab = $cab->Kode;
        $day_of_week = date('N', strtotime(date('Y-m-d')));
        // if($day_of_week == 1){
        //   $transdate = date('Y-m-d', strtotime('-2 days'));
        // }else{
        //   $transdate = date('Y-m-d', strtotime('-1 days'));
        // }
        $lastdate = $this->db->query("select tglDO from trs_delivery_order_sales where Cabang = '".$this->cabang."' and tglDO < '".date('Y-m-d')."' order by tglDO DESC limit 1 ")->row();
        $transdate = $lastdate->tglDO;
        $sobh = 'SOBH/'.$cab.'/'.date('Y-m-d H:i:s');
        $this->Model_main->getcogssummary();
        $lasttrans = $this->db->query("SELECT distinct trs_invsum.KodeProduk AS Kode_Produk,
                                        IFNULL(terima.qty,0) AS 'qty_terima',
                                         IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                                         IFNULL(koreksi_plus.qty,0) AS 'qty_plus',
                                         IFNULL(retur.qty,0) AS 'qty_retur',
                                         IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                                         IFNULL(Delivery.qty,0) AS 'qty_do',
                                         IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                                         IFNULL(koreksi_min.qty,0) AS 'qty_min'
                                   FROM trs_invsum LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen =  '".$transdate."' and 
                                          Status not in ('pending','Batal') and 
                                          ifnull(tipe,'') not in  ('BKB','Tolakan')
                                    GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus)*-1,0) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen =  '".$transdate."' and 
                                          Tipe = 'BKB'
                                    GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                    FROM trs_relokasi_terima_detail
                                   WHERE  Cabang = '".$this->cabang."' AND 
                                          Status = 'Terima' and
                                          Tgl_terima =  '".$transdate."'
                                    GROUP BY Produk) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                   ( SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '".$this->cabang."' AND
                                           status = 'Approve' and 
                                           tanggal =  '".$transdate."'
                                   GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          Cabang = '".$this->cabang."' AND
                                          TglFaktur =  '".$transdate."'
                                    GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (SELECT KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status != 'Batal' 
                                    AND TglDO =  '".$transdate."'
                                    GROUP BY KodeProduk) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (SELECT Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status = 'Kirim' and
                                        trs_relokasi_kirim_detail.Cabang = '".$this->cabang."' and 
                                        trs_relokasi_kirim_detail.Tgl_kirim =  '".$transdate."'
                                        GROUP BY Produk) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                    (SELECT produk,
                                            IFNULL(SUM(Qty),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                                        status = 'Approve' and 
                                        tanggal =  '".$transdate."'
                                  GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
                                  where trs_invsum.tahun ='".date('Y')."'
                                  HAVING (qty_terima > 0) or (qty_do > 0)  or (qty_returbeli > 0) or (qty_relokasi_terima > 0) 
                                        OR (qty_plus > 0) OR (qty_retur > 0) or (qty_relokasi_kirim > 0) 
                                          OR (qty_min > 0)
                               ")->result();
        $data = array(); 
        $this->db->query("delete from trs_sobh where Cabang= '".$this->cabang."' and TglSOBH = '".date('Y-m-d')."'");
        foreach ($lasttrans as $trans) {
            $kode = $trans->Kode_Produk;
            $query = $this->db->query("select distinct Tahun,
                                          '".$sobh."' as 'NoSOBH',
                                          '".date('Y-m-d')."' as 'TglSOBH',
                                          '".date('Y-m-d H:i:s')."' as 'TimeSOBH',
                                          Cabang,
                                          KodePrinsipal,
                                          NamaPrinsipal,
                                          Pabrik,
                                          KodeProduk,
                                          NamaProduk,
                                          UnitStok,
                                          ValueStok,
                                          Gudang,
                                          0 as UnitOpname,
                                          0 as selisih,
                                          '' as catatan,
                                          '' as status
                                  from trs_invsum
                                  where KodeProduk ='".$kode."' and tahun ='".date('Y')."'
                                  order by NamaPrinsipal,NamaProduk ASC")->result();
            foreach ($query as $key => $value) {
              $this->db->set("Cabang", $query[$key]->Cabang);
              $this->db->set("Tahun",$query[$key]->Tahun);
              $this->db->set("NoSOBH", $query[$key]->NoSOBH);
              $this->db->set("TglSOBH", $query[$key]->TglSOBH);
              $this->db->set("TimeSOBH", $query[$key]->TimeSOBH);
              $this->db->set("NamaPrinsipal", $query[$key]->NamaPrinsipal);
              $this->db->set("KodeProduk", $query[$key]->KodeProduk);
              $this->db->set("NamaProduk", $query[$key]->NamaProduk);
              $this->db->set("UnitStok", $query[$key]->UnitStok);
              $this->db->set("ValueStok", $query[$key]->ValueStok);
              $this->db->set("Gudang", $query[$key]->Gudang);
              $this->db->set("UnitOpname", $query[$key]->UnitOpname);
              $this->db->set("selisih", $query[$key]->selisih);
              $this->db->set("catatan", $query[$key]->catatan);
              $this->db->set("status", $query[$key]->status);
              $this->db->set("AddedUser", $this->user);
              $this->db->set("AddedTime", date('Y-m-d H:i:s'));
              $this->db->insert("trs_sobh");

              $row = array();
                $row = ['Tahun' => $query[$key]->Tahun,
                       'Cabang' => $query[$key]->Cabang,
                      'NoSOBH'       => $query[$key]->NoSOBH,
                      'TglSOBH'      => $query[$key]->TglSOBH,
                      'TimeSOBH'     => $query[$key]->TimeSOBH,
                      'NamaPrinsipal'  => $query[$key]->NamaPrinsipal,
                      'KodeProduk'   => $query[$key]->KodeProduk,
                      'NamaProduk'    => $query[$key]->NamaProduk,
                      'UnitStok'      => $query[$key]->UnitStok,
                      'ValueStok'     => $query[$key]->ValueStok,
                      'Gudang'     => $query[$key]->Gudang,
                      'UnitOpname'    => $query[$key]->UnitOpname,
                      'selisih'      => $query[$key]->selisih,
                      'catatan'      => $query[$key]->catatan,
                      'status'       => $query[$key]->status,
                      ];
              
                $data[] = $row;              
            }
        }
        return $data;

    }

    public function getsobb(){
        $cab = $this->db->query("select Kode from mst_cabang where Cabang = '".$this->cabang."' limit 1 ")->row();
        $cab = $cab->Kode;
        $month = date('m');
        $sobb = 'SOBB/'.$cab.'/'.date('Y-m-d H:i:s');
        $this->db->query("delete from trs_sobb where Cabang= '".$this->cabang."' and Bulan = '".date('m')."' and Tahun = '".date('Y')."'");
        $this->Model_main->getcogssummary();
        $list = $this->db->query("select distinct Tahun,
                                          '".$month."' as Bulan,
                                          '".$sobb."' as 'NoSOBB',
                                          '".date('Y-m-d')."' as 'TglSOBB',
                                          '".date('Y-m-d H:i:s')."' as 'TimeSOBB',
                                          Cabang,
                                          KodePrinsipal,
                                          NamaPrinsipal,
                                          Pabrik,
                                          KodeProduk,
                                          NamaProduk,
                                          UnitStok,
                                          ValueStok,
                                          Gudang,
                                          UnitCOGS,
                                          0 as UnitOpname,
                                          0 as selisih,
                                          '' as catatan,
                                          '' as status
                                  from trs_invsum where tahun ='".date('Y')."'
                                  order by NamaPrinsipal,NamaProduk ASC")->result();
        foreach ($list as $query) {
              $this->db->set("Cabang", $query->Cabang);
              $this->db->set("Tahun",$query->Tahun);
              $this->db->set("Bulan",$query->Bulan);
              $this->db->set("NoSOBB", $query->NoSOBB);
              $this->db->set("TglSOBB", $query->TglSOBB);
              $this->db->set("TimeSOBB", $query->TimeSOBB);
              $this->db->set("NamaPrinsipal", $query->NamaPrinsipal);
              $this->db->set("KodeProduk", $query->KodeProduk);
              $this->db->set("NamaProduk", $query->NamaProduk);
              $this->db->set("UnitStok", $query->UnitStok);
              $this->db->set("UnitCOGS", $query->UnitCOGS);
              $this->db->set("ValueStok", $query->ValueStok);
              $this->db->set("Gudang", $query->Gudang);
              $this->db->set("UnitOpname", $query->UnitOpname);
              $this->db->set("selisih", $query->selisih);
              $this->db->set("catatan", $query->catatan);
              $this->db->set("status", $query->status);
              $this->db->set("AddedUser", $this->user);
              $this->db->set("AddedTime", date('Y-m-d H:i:s'));
              $this->db->insert("trs_sobb");
            }
        return $list;
    }

    public function uploadsobh($name=null){
      $file_path = "/var/www/html/sstcabang/assets/dokumen/excel/$name";   // for linux
      $noSOBH = "";
      // $file_path = "C:/xampp/htdocs/sstcabang/assets/dokumen/excel/$name";
      $FileType = pathinfo($file_path, PATHINFO_EXTENSION);
      $inputFileType = PHPExcel_IOFactory::identify($file_path);
      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objPHPExcel = $objReader->load($file_path);
      foreach($objPHPExcel->getWorksheetIterator() as $worksheet)
       {
          $highestRow = $worksheet->getHighestRow();
          $highestColumn = $worksheet->getHighestColumn();
          for($row=4; $row<=$highestRow; $row++)
          {
             $cabang = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
             $noSOBH = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
             $TglSOBH = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
             $TimeSOBH = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
             $Prinsipal = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
             $KodeProduk = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
             $NamaProduk = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
             $gudang = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
             $UnitStok = $worksheet->getCellByColumnAndRow(9,$row)->getValue();
             $UnitOpname = $worksheet->getCellByColumnAndRow(10, $row)->getCalculatedValue();
             $selisih = $worksheet->getCellByColumnAndRow(11, $row)->getCalculatedValue();
             $catatan = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
             $status = $worksheet->getCellByColumnAndRow(13, $row)->getValue();

             $UnitStok = str_replace(',','',$UnitStok);
             $UnitOpname = str_replace(',','',$UnitOpname);
             $selisih = str_replace(',','',$selisih);
             $this->db->set("UnitOpname", $UnitOpname);
             $this->db->set("selisih", $selisih);
             $this->db->set("catatan", $catatan);
             $this->db->set("file_upload", $file_path);
             $this->db->set("status_upload", 'Y');
             $this->db->where("Cabang", trim($cabang));
             $this->db->where("NoSOBH", trim($noSOBH));
             $this->db->where("KodeProduk", trim($KodeProduk));
             $this->db->where("Gudang", trim($gudang));
             $valid= $this->db->update("trs_sobh");
          }
       }
       if($valid){
          $query = $this->db->query("select * from trs_sobh where noSOBH = '".$noSOBH."'")->result();
       }
       return $query;
       
    }

    public function uploadsobb($name=null){
      // $file_path = "/var/www/html/sstcabang/assets/dokumen/excel/$name";   // for linux
      $noSOBB = "";
      $file_path = "C:/xampp/htdocs/sstcabang/assets/dokumen/excel/$name";
      $FileType = pathinfo($file_path, PATHINFO_EXTENSION);
      $inputFileType = PHPExcel_IOFactory::identify($file_path);
      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objPHPExcel = $objReader->load($file_path);
      foreach($objPHPExcel->getWorksheetIterator() as $worksheet)
       {
          $highestRow = $worksheet->getHighestRow();
          $highestColumn = $worksheet->getHighestColumn();
          for($row=4; $row<=$highestRow; $row++)
          {
             $cabang = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
             $noSOBB = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
             $TglSOBB = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
             $TimeSOBB = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
             $Prinsipal = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
             $KodeProduk = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
             $NamaProduk = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
             $gudang = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
             $UnitStok = $worksheet->getCellByColumnAndRow(9,$row)->getValue();
             $UnitCOGS = $worksheet->getCellByColumnAndRow(10,$row)->getValue();
             $UnitOpname = $worksheet->getCellByColumnAndRow(11, $row)->getCalculatedValue();
             $selisih = $worksheet->getCellByColumnAndRow(12, $row)->getCalculatedValue();
             $catatan = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
             $status = $worksheet->getCellByColumnAndRow(14, $row)->getValue();

             $UnitStok = str_replace(',','',$UnitStok);
             $UnitCOGS = str_replace(',','',$UnitCOGS);
             $UnitOpname = str_replace(',','',$UnitOpname);
             $selisih = str_replace(',','',$selisih);

             $this->db->set("UnitOpname", $UnitOpname);
             $this->db->set("selisih", $selisih);
             $this->db->set("catatan", $catatan);
             $this->db->set("file_upload", $file_path);
             $this->db->set("status_upload", 'Y');
             $this->db->where("Cabang", trim($cabang));
             $this->db->where("NoSOBB", trim($noSOBB));
             $this->db->where("KodeProduk", trim($KodeProduk));
             $this->db->where("Gudang", trim($gudang));
             $valid= $this->db->update("trs_sobb");
          }
       }
       if($valid){
          $query = $this->db->query("select * from trs_sobb where noSOBB = '".$noSOBB."'")->result();
       }
       return $query;
       
    }

    public function getdatasobh($transdate=null,$cabang=null){
        $query = $this->db->query("select * from trs_sobh where TglSOBH = '".$transdate."' and Cabang ='".$this->cabang."'")->result();
       return $query;
       
    }

    public function getdatasobb($year=null,$month=null,$cabang=null){
        $query = $this->db->query("select * from trs_sobb where Tahun = '".$year."' and Bulan = '".$month."' and Cabang ='".$this->cabang."'")->result();
       return $query;
       
    }

    public function saldoawalbatch($produk = NULL,$getmonth = NULL,$BatchNo = NULL){
        $query = $this->db->query("
                                  SELECT KodeProduk AS Kode_Produk, NamaProduk AS Produk,
                                       (CASE '10' 
                                        WHEN '01' THEN sum(SAwa01)
                                        WHEN '02' THEN sum(SAwa02)
                                        WHEN '03' THEN sum(SAwa03)
                                        WHEN '04' THEN sum(SAwa04)
                                        WHEN '05' THEN sum(SAwa05)
                                        WHEN '06' THEN sum(SAwa06)
                                        WHEN '07' THEN sum(SAwa07)
                                        WHEN '08' THEN sum(SAwa08)
                                        WHEN '09' THEN sum(SAwa09)
                                        WHEN '10' THEN sum(SAwa10)
                                        WHEN '11' THEN sum(SAwa11)
                                        WHEN '12' THEN sum(SAwa12)
                                        ELSE 0 END) AS 'saldo_awal' 
                                  FROM trs_invdet 
                                  WHERE Cabang = '".$this->cabang."' and KodeProduk = '".$produk."'and 
                                        trs_invdet.`BatchNo` = '".$BatchNo."'
                                  group by KodeProduk,NamaProduk
                                  ORDER BY NamaProduk ASC;
                    ")->result();
   
        return $query;
    }

     public function getstokdetailbatch($produk = NULL,$BatchNo = NULL){
        $query = $this->db->query("
                                 SELECT mst_produk.Kode_Produk,
                                         mst_produk.Produk,
                                         mst_produk.Satuan,
                                         gudang.Gudang AS 'gudang_detail',
                                         gudang.UnitStok AS 'Stok_detail'
                                  FROM mst_produk LEFT JOIN
                                  (SELECT trs_invdet.KodeProduk,
                                          trs_invdet.Gudang,
                                          sum(trs_invdet.UnitStok) AS 'UnitStok'
                                    FROM trs_invdet
                                    where trs_invdet.`BatchNo` = '".$BatchNo."'
                                    group by trs_invdet.KodeProduk,
                                          trs_invdet.Gudang ) AS gudang ON
                                         gudang.KodeProduk = mst_produk.`Kode_Produk`
                                  WHERE mst_produk.`Kode_Produk` = '".$produk."';

                    ")->result();
        return $query;
    }

     public function getkartustokbatch($first_date = NULL,$end_date = NULL, $produk = NULL,$BatchNo = NULL)
    {
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT NoDokumen AS 'NoDoc',
                                         CONCAT('Terima dari ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                         TglDokumen AS 'TglDoc',
                                         TimeDokumen AS 'TimeDoc',
                                         Produk AS 'Kode',
                                         NamaProduk AS 'NamaProduk',
                                         Satuan AS 'Unit',
                                         (Qty+Bonus) AS 'qty_in',
                                         0 AS 'qty_out',
                                         BatchNo,
                                         ExpDate
                                   FROM trs_terima_barang_detail
                                   WHERE trs_terima_barang_detail.Produk = '".$produk ."' AND 
                                         STATUS NOT IN ('pending','Batal') AND
                                         Tipe != 'BKB' AND
                                         BatchNo = '".$BatchNo."' AND
                                         TglDokumen BETWEEN '".$first_date."' and '".$end_date."'
                                                                    
                                  UNION

                                  SELECT NoDokumen AS 'NoDoc',
                                         CONCAT('Retur Ke ',Prinsipal,' - ',Supplier) AS 'Dokumen',
                                         TglDokumen AS 'TglDoc',
                                         TimeDokumen AS 'TimeDoc',
                                         Produk AS 'Kode',
                                         NamaProduk AS 'NamaProduk',
                                         Satuan AS 'Unit',
                                         0 AS 'qty_in',
                                         ((Qty+Bonus)*-1) AS 'qty_out',
                                         BatchNo,
                                         ExpDate
                                   FROM trs_terima_barang_detail
                                   WHERE   Tipe = 'BKB' AND Produk = '".$produk ."' AND 
                                         BatchNo = '".$BatchNo."' AND
                                         TglDokumen BETWEEN '".$first_date."' and '".$end_date."'

                                  UNION
                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Adjusment : ', catatan) AS 'Dokumen',
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
                                  WHERE   qty > 0 AND trs_mutasi_koreksi.status = 'Approve' AND
                                        trs_mutasi_koreksi.batch = '".$BatchNo."' AND
                                        trs_mutasi_koreksi.`produk` = '".$produk ."' AND 
                                        tanggal BETWEEN '".$first_date."' and '".$end_date."'
                                  UNION
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
                                   WHERE   Qty > 0 AND
                                         STATUS = 'Terima' AND
                                         Produk = '".$produk ."' AND
                                         BatchNo = '".$BatchNo."' AND
                                         Tgl_terima BETWEEN '".$first_date."' and '".$end_date."'  
                                  UNION 
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
                                        KodeProduk = '".$produk ."' AND 
                                        BatchNo = '".$BatchNo."' AND
                                        TglFaktur BETWEEN '".$first_date."' and '".$end_date."'
                                  UNION
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
                                  WHERE KodeProduk = '".$produk ."' AND
                                        BatchNo = '".$BatchNo."' AND
                                        TglDO BETWEEN '".$first_date."' and '".$end_date."'
                                  UNION
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
                                  WHERE KodeProduk = '".$produk ."' AND 
                                        STATUS = 'Batal' AND
                                        BatchNo = '".$BatchNo."' AND
                                        DATE(IFNULL(time_batal,'')) BETWEEN '".$first_date."' and '".$end_date."'

                                  UNION

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
                                   WHERE trs_relokasi_kirim_detail.Status = 'Kirim' AND
                                         trs_relokasi_kirim_detail.Produk = '".$produk ."' AND 
                                         trs_relokasi_kirim_detail.BatchNo = '".$BatchNo."' AND
                                         trs_relokasi_kirim_detail.Tgl_kirim BETWEEN '".$first_date."' and '".$end_date."'

                                  UNION
                                  SELECT no_koreksi AS 'NoDoc', 
                                         CONCAT('Adjusment : ',catatan) AS 'Dokumen',
                                         tanggal AS 'TglDoc',
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
                                        trs_mutasi_koreksi.batch = '".$BatchNo."' AND
                                        trs_mutasi_koreksi.`produk` = '".$produk ."' AND 
                                        tanggal BETWEEN '".$first_date."' and '".$end_date."'
                                  ORDER BY TglDoc,TimeDoc,NoDoc ASC;")->result();
        return $query;
    }
    public function liststoksummary($kode=null,$gudang=null,$getyear=null)
    {   
        $query = $this->db->query("select * from trs_invsum where KodeProduk = '".$kode."' and gudang = '".$gudang."' and Tahun = '".$getyear."'")->result();

        return $query;
    }

    public function updatestoksummary($kode=null,$gudang=null,$UnitStok=null,$getyear=null)
    {   
        $query = $this->db->query("update trs_invsum
                                   set UnitStok = '".$UnitStok."',
                                       ValueStok = '".$UnitStok."' * UnitCOGS
                                   where KodeProduk = '".$kode."' and 
                                        Gudang = '".$gudang."' and 
                                    Tahun = '".$getyear."'");
        return $query;
    }
    public function liststokdetail($kode=null,$gudang=null,$getyear=null)
    {   
        $query = $this->db->query("select * from trs_invdet where KodeProduk = '".$kode."' and gudang = '".$gudang."' and Tahun = '".$getyear."'")->result();

        return $query;
    }

    public function updatestokdetail($kode=null,$gudang=null,$UnitStok=null,$BatchNo=null,$BatchDoc=null,$getyear=null)
    {   
        $query = $this->db->query("update trs_invdet
                                   set UnitStok = '".$UnitStok."',
                                       ValueStok = '".$UnitStok."' * UnitCOGS
                                   where KodeProduk = '".$kode."' and 
                                        Gudang = '".$gudang."' and 
                                        BatchNo ='".$BatchNo."' and 
                                        NoDokumen ='".$BatchDoc."' and 
                                    Tahun = '".$getyear."'");
        return $query;
    }

    public function getclosingstokdaily($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                                         trs_invsum.NamaProduk AS Produk,
                                         CONCAT(trs_invsum.KodeProduk ,' - ',trs_invsum.NamaProduk) AS 'NamaProduk',
                                         -- mst_produk.Satuan,
                                         '' AS Satuan,
                                         IFNULL(s_awal.saldo_awal,0) AS 'saldo_awal',
                                         IFNULL(terima.qty,0) AS 'qty_terima',
                                         IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                                         IFNULL(koreksi_plus.qty,0) AS 'qty_plus',
                                         IFNULL(retur.qty,0) AS 'qty_retur',
                                         IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                                         IFNULL(Delivery.qty,0) AS 'qty_do',
                                         IFNULL(returDelivery.qty,0) AS 'qty_returdo',
                                         IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                                         IFNULL(koreksi_min.qty,0) AS 'qty_min',
                                         IFNULL(stok.qty,0) AS 'qty_stok',
                                         IFNULL(stokdetail.qty,0) AS 'qty_stokdetail',
                                         IFNULL(stok_detail.Status_stok_detail,'') AS 'Status_stok_detail',
                                        (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
                                   FROM trs_invsum LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '".$getmonth."' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '".$this->cabang."' and Tahun ='".date('Y')."'
                                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          SUM(ifnull(Qty,'')+ifnull(Bonus,'')) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          Status not in ('pending','Batal') and 
                                          ifnull(tipe,'') not in  ('BKB','Tolakan')
                                    GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          ifnull(Tipe,'') ='BKB'
                                    GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(qty),0) AS 'qty' FROM (
                                      SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                        FROM trs_relokasi_terima_detail
                                       WHERE  Cabang = '".$this->cabang."' AND 
                                              Status = 'Terima' and
                                              Tgl_terima BETWEEN '".$first_date."' AND '".$end_date."'
                                        GROUP BY Produk

                                         UNION ALL

                                        SELECT  Produk,
                                              IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                        FROM trs_relokasi_kirim_detail
                                       WHERE  Cabang_Pengirim = '".$this->cabang."' AND 
                                              Status = 'Reject' and
                                              tgl_reject BETWEEN '".$first_date."' AND '".$end_date."'
                                        GROUP BY Produk
                                      )zz GROUP BY zz.Produk

                                    ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                   ( SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '".$this->cabang."' AND
                                   CASE WHEN `Status` = 'Open' THEN
                                      tanggal
                                   ELSE
                                      IFNULL(DATE(tgl_approve),tanggal)
                                   END BETWEEN '".$first_date."' AND '".$end_date."' and status IN ('Approve','Open')
                                   GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          ifnull(status,'') != 'Batal' and
                                          Cabang = '".$this->cabang."' AND
                                          TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' 
                                    GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status != 'Batal' and tipedokumen ='DO' 
                                    AND TglDO BETWEEN '".$first_date  ."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status = 'Retur' and tipedokumen ='Retur' 
                                    AND TglDO BETWEEN '".$first_date  ."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (
                                    SELECT Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') and
                                        trs_relokasi_kirim_detail.Cabang_Pengirim = '".$this->cabang."' and 
                                        CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                                        between '".$first_date."' and '".$end_date."'
                                        GROUP BY Produk

                                  ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                    (SELECT produk,
                                            IFNULL(SUM(Qty * -1),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                                        status = 'Approve' and 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."'
                                  GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invsum
                                   where Tahun ='".date('Y')."'
                                   GROUP BY KodeProduk) as stok on stok.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invdet
                                   where Tahun ='".date('Y')."'
                                   GROUP BY KodeProduk) as stokdetail on stokdetail.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         (CASE COUNT(UnitStok)
                                          WHEN 0 THEN 'Ok'
                                          ELSE 'Minus' END ) AS 'Status_stok_detail'
                                  FROM trs_invdet
                                  WHERE IFNULL(UnitStok,0) < 0 and Tahun ='".date('Y')."'
                                  group by KodeProduk) as stok_detail on stok_detail.KodeProduk = trs_invsum.KodeProduk
                                  where trs_invsum.tahun ='".date('Y')."'
                                  HAVING ((saldo_akhir != qty_stok) or (saldo_akhir < 0) or (qty_stokdetail != saldo_akhir))
                                ORDER BY trs_invsum.KodeProduk ASC
                                ")->result();
        return $query;

         // HAVING (saldo_awal > 0 ) OR (qty_terima > 0) 
         //                                OR (qty_plus > 0) OR (qty_retur > 0) 
         //                                OR (qty_do > 0) OR (qty_min > 0) OR (qty_stok > 0)

    }
    public function generatestok($params = NULL)
    {
      $first_date = date('Y-m-01');
      $end_date = date('Y-m-d');
      $month = date('m');
      $this->Model_main->getcogssummary();
      foreach ($params->Kode_Produk as $key => $value) 
      {
        $query = $this->db->query("
            SELECT DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                   trs_invsum.NamaProduk AS Produk,
                   IFNULL(s_awal.saldo_awal,0) AS 'saldo_awal',
                   IFNULL(s_awalbaik.saldo_awal,0) AS 's_awalbaik',
                   IFNULL(s_awalretursup.saldo_awal,0) AS 's_awalretursup',
                   IFNULL(s_awalrelokasi.saldo_awal,0) AS 's_awalrelokasi',
                   IFNULL(s_awalrelokasiout.saldo_awal,0) AS 's_awalrelokasiout',
                   IFNULL(s_awalkoreksi.saldo_awal,0) AS 's_awalkoreksi',
                   IFNULL(s_awallain.saldo_awal,0) AS 's_awallain',
                   IFNULL(terima.qty,0) AS 'qty_terima',
                   IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                   IFNULL(korbaik_plus.qty,0) AS 'qty_korbaik_plus',
                   IFNULL(kor_retsup_plus.qty,0) AS 'qty_kor_retsuplier_plus',
                   IFNULL(kor_relokasi_plus.qty,0) AS 'qty_kor_relokasi_plus',
                   IFNULL(koreksi_plus.qty,0) AS 'qty_koreksi_plus',
                   IFNULL(retur.qty,0) AS 'qty_retur',
                   IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                   IFNULL(usulanreturbeli.qty,0) AS 'qty_usulanreturbeli',
                   IFNULL(tolakanretur.qty,0) AS 'qty_tolakanretur',
                   IFNULL(Delivery.qty,0) AS 'qty_do',
                   IFNULL(returDelivery.qty,0) AS 'qty_returdo',
                   IFNULL(usulanrelokasi_kirim.qty,0) AS 'qty_usulanrelokasi_kirim',
                   IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                   IFNULL(korbaik_min.qty,0) AS 'qty_korbaik_min',
                   IFNULL(kor_rel_min.qty,0) AS 'qty_kor_relokasi_min',
                   IFNULL(kor_retsup_min.qty,0) AS 'qty_kor_retsupplier_min',
                   IFNULL(koreksi_min.qty,0)  AS 'qty_koreksi_min',
                   IFNULL(allusulankoreksi.qty,0)  AS 'qty_allusulankoreksi',
                   IFNULL(usulankoreksibaik.qty,0)  AS 'qty_usulankoreksibaik',
                   IFNULL(usulankoreksiRelokasi.qty,0)  AS 'qty_usulankoreksiRelokasi',
                   IFNULL(usulankoreksisupplier.qty,0)  AS 'qty_usulankoreksisupplier',
                   IFNULL(MutasiGudangBaikIn.qty,0)  AS 'qty_MutasiGudangBaikIn',
                   IFNULL(MutasiGudangKoreksiIn.qty,0)  AS 'qty_MutasiGudangKoreksiIn',
                   IFNULL(MutasiGudangLainIn.qty,0)  AS 'qty_MutasiGudangLainIn',
                   IFNULL(MutasiGudangReturIn.qty,0)  AS 'qty_MutasiGudangReturIn',
                   IFNULL(MutasiGudangRelokasiIn.qty,0)  AS 'qty_MutasiGudangRelokasiIn',
                   IFNULL(MutasiGudangBaikOut.qty,0)  AS 'qty_MutasiGudangBaikOut',
                   IFNULL(MutasiGudangKoreksiOut.qty,0)  AS 'qty_MutasiGudangKoreksiOut',
                   IFNULL(MutasiGudangLainOut.qty,0)  AS 'qty_MutasiGudangLainOut',
                   IFNULL(MutasiGudangReturOut.qty,0)  AS 'qty_MutasiGudangReturOut',
                   IFNULL(MutasiGudangRelokasiOut.qty,0)  AS 'qty_MutasiGudangRelokasiOut',
                   IFNULL(stok.qty,0) AS 'qty_stok',
                   IFNULL(stokdetail.qty,0) AS 'qty_stokdetail',
                   IFNULL(stok_detail.Status_stok_detail,'') AS 'Status_stok_detail',
                   (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
             FROM trs_invsum LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."' 
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Baik'
                     GROUP BY KodeProduk ) AS s_awalbaik ON s_awalbaik.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Retur Supplier'
                     GROUP BY KodeProduk ) AS s_awalretursup ON s_awalretursup.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Relokasi'
                     GROUP BY KodeProduk ) AS s_awalrelokasi ON s_awalrelokasi.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Relokasi Out'
                     GROUP BY KodeProduk ) AS s_awalrelokasiout ON s_awalrelokasiout.KodeProduk = trs_invsum.KodeProduk
               LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."' 
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Koreksi'
                     GROUP BY KodeProduk ) AS s_awalkoreksi ON s_awalrelokasi.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Lain-lain'
                     GROUP BY KodeProduk ) AS s_awallain ON s_awallain.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                 (SELECT  Produk,
                          SUM(ifnull(Qty,'')+ifnull(Bonus,'')) AS 'qty'      
                     FROM trs_terima_barang_detail
                    WHERE  Cabang = '".$this->cabang."' AND
                           TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                           STATUS NOT IN ('pending','Batal') AND 
                           IFNULL(tipe,'') not in  ('BKB','Tolakan') AND
                           Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT  Produk,
                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                    FROM trs_terima_barang_detail
                  WHERE  Cabang = '".$this->cabang."' AND
                         TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                         IFNULL(Tipe,'') ='BKB' AND
                         Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT Produk ,
                         IFNULL(SUM(qty + bonus),0) AS 'qty'
                   FROM  trs_usulan_retur_beli_header INNER JOIN 
                         trs_usulan_retur_beli_detail 
                   ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                   WHERE IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' AND
                       trs_usulan_retur_beli_detail.Produk = '".$params->Kode_Produk[$key]."'AND 
                       trs_usulan_retur_beli_header.tanggal BETWEEN '".$first_date."' AND '".$end_date."'
                   GROUP BY Produk) AS usulanreturbeli ON usulanreturbeli.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT  Produk,
                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                    FROM trs_terima_barang_detail
                  WHERE  Cabang = '".$this->cabang."' AND
                         TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                         IFNULL(Tipe,'') ='Tolakan' AND
                         Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS tolakanretur ON tolakanretur.Produk =trs_invsum.KodeProduk 
             LEFT OUTER JOIN
                  (SELECT  Produk,
                      IFNULL(SUM(qty),0) AS 'qty' FROM (
                        SELECT  Produk,
                         IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                        FROM trs_relokasi_terima_detail
                        WHERE  Cabang = '".$this->cabang."' AND 
                        STATUS = 'Terima' AND
                               Tgl_terima BETWEEN '".$first_date."' AND '".$end_date."' AND
                               Produk = '".$params->Kode_Produk[$key]."'
                       GROUP BY Produk

                        UNION ALL
                       
                       SELECT  Produk,
                             IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                        FROM trs_relokasi_kirim_detail
                        WHERE  Cabang_Pengirim = '".$this->cabang."' AND 
                        STATUS = 'Reject' AND
                               Tgl_kirim BETWEEN '".$first_date."' AND '".$end_date."' AND
                               Produk =  '".$params->Kode_Produk[$key]."'
                       GROUP BY Produk
                     )zz GROUP BY zz.Produk
                   ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          CASE WHEN `Status` = 'Open' THEN
                            tanggal
                         ELSE
                            IFNULL(DATE(tgl_approve),tanggal)
                         END BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS  IN ('Approve','Open') AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          CASE WHEN `Status` = 'Open' THEN
                            tanggal
                         ELSE
                            IFNULL(DATE(tgl_approve),tanggal)
                         END BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS IN ('Approve','Open') AND
                          gudang = 'Baik' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS korbaik_plus ON korbaik_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          tanggal BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS NOT IN ('Tolak') AND
                          gudang = 'Retur Supplier' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS kor_retsup_plus ON kor_retsup_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          tanggal BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS NOT IN ('Tolak') AND
                          gudang = 'Relokasi Out' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS kor_relokasi_plus ON kor_relokasi_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                    (SELECT KodeProduk, 
                            IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                       FROM trs_faktur_detail 
                      WHERE TipeDokumen ='Retur' AND 
                            ifnull(status,'') != 'Batal' and
                            Cabang = '".$this->cabang."' AND
                            TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND
                            KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                     (SELECT  KodeProduk,
                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                     FROM trs_delivery_order_sales_detail 
                     WHERE Cabang = '".$this->cabang."' AND STATUS != 'Batal' AND tipedokumen ='DO' 
                           AND TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                      GROUP BY KodeProduk
                      ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                   (SELECT  KodeProduk,
                            IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                    FROM trs_delivery_order_sales_detail 
                    WHERE Cabang = '".$this->cabang."' AND STATUS = 'Retur' AND tipedokumen ='Retur' 
                           AND TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND
                          KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk
                    ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT Produk,
                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                    FROM  trs_relokasi_kirim_detail
                    WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') AND
                          trs_relokasi_kirim_detail.Cabang_Pengirim = '".$this->cabang."' AND 
                            CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                          BETWEEN '".$first_date."' AND '".$end_date."' AND
                          trs_relokasi_kirim_detail.Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY Produk
                   ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT Produk,
                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                    FROM  trs_relokasi_kirim_detail
                    WHERE trs_relokasi_kirim_detail.Status = 'Open' AND
                          trs_relokasi_kirim_detail.Cabang = '".$this->cabang."' AND 
                          trs_relokasi_kirim_detail.Tgl_kirim BETWEEN '".$first_date."' AND '".$end_date."' AND
                          trs_relokasi_kirim_detail.Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY Produk
                   ) AS usulanrelokasi_kirim ON usulanrelokasi_kirim.Produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Retur Supplier'
                   GROUP BY produk ) AS kor_retsup_min ON kor_retsup_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Baik'
                   GROUP BY produk ) AS korbaik_min ON korbaik_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Relokasi Out'
                   GROUP BY produk ) AS kor_rel_min ON kor_rel_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."'
                   GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS allusulankoreksi ON allusulankoreksi.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Baik' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksibaik ON usulankoreksibaik.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Relokasi' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksiRelokasi ON usulankoreksiRelokasi.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Retur Supplier' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksisupplier ON usulankoreksisupplier.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Baik' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangBaikIn 
                  ON MutasiGudangBaikIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Koreksi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangKoreksiIn
              ON MutasiGudangKoreksiIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN      
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Lain-lain' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangLainIn
              ON MutasiGudangLainIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN    
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Retur Supplier' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."' 
            GROUP BY produk) AS MutasiGudangReturIn
              ON MutasiGudangReturIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN      
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Relokasi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangRelokasiIn
              ON MutasiGudangRelokasiIn.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Baik' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangBaikOut
               ON MutasiGudangBaikOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Koreksi' AND
                 produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangKoreksiOut
               ON MutasiGudangKoreksiOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Lain-lain' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangLainOut 
               ON MutasiGudangLainOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Retur Supplier' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangReturOut
               ON MutasiGudangReturOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Relokasi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangRelokasiOut 
               ON MutasiGudangRelokasiOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT KodeProduk,
                          SUM(IFNULL(UnitStok,0)) AS 'qty'
                     FROM trs_invsum
                     WHERE Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS stok ON stok.KodeProduk = trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                   (SELECT KodeProduk,
                        SUM(IFNULL(UnitStok,0)) AS 'qty'
                     FROM trs_invdet
                    WHERE Tahun ='".date('Y')."' AND
                          KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS stokdetail ON stokdetail.KodeProduk = trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                    (SELECT KodeProduk,
                           (CASE COUNT(UnitStok)
                             WHEN 0 THEN 'Ok'
                             ELSE 'Minus' END ) AS 'Status_stok_detail'
                      FROM trs_invdet
                      WHERE IFNULL(UnitStok,0) < 0 AND Tahun ='".date('Y')."' AND
                            KodeProduk = '".$params->Kode_Produk[$key]."'
                      GROUP BY KodeProduk) AS stok_detail ON stok_detail.KodeProduk = trs_invsum.KodeProduk
             WHERE trs_invsum.KodeProduk = '".$params->Kode_Produk[$key]."' and 
                  trs_invsum.tahun ='".date('Y')."';
          ")->row();
        $saldoawal = $query->saldo_awal;
        $s_awalbaik = $query->s_awalbaik;
        $s_awalretursup = $query->s_awalretursup;
        $s_awalrelokasi = $query->s_awalrelokasi;
        $s_awalrelokasiout = $query->s_awalrelokasiout;
        $s_awalkoreksi = $query->s_awalkoreksi;
        $s_awallain = $query->s_awallain;
        $totalsaldo = $query->saldo_akhir;
        $usulanretursupplier = ($query->qty_usulanreturbeli * -1);
        $tolakanretursupplier = $query->qty_tolakanretur ;
        $retursupplier = $query->qty_returbeli;
        $bpb = $query->qty_terima;
        $relokasi_terima = $query->qty_relokasi_terima;
        $koreksiplus = $query->qty_koreksi_plus;
        $qty_korbaik_plus = $query->qty_korbaik_plus;
        $qty_kor_retsuplier_plus = $query->qty_kor_retsuplier_plus;
        $qty_kor_relokasi_plus = $query->qty_kor_relokasi_plus;
        $returbeli = $query->qty_returbeli;
        $returjual = $query->qty_retur;
        $DOkirim = $query->qty_do;
        $DORetur = $query->qty_returdo;
        $usulanrelokasi_kirim = $query->qty_usulanrelokasi_kirim;
        $relokasi_kirim = $query->qty_relokasi_kirim;
        $koreksimin = $query->qty_koreksi_min;
        $qty_korbaik_min = $query->qty_korbaik_min;
        $qty_kor_relokasi_min = $query->qty_kor_relokasi_min;
        $qty_kor_retsupplier_min = $query->qty_kor_retsupplier_min;
        $qty_allusulankoreksi = $query->qty_allusulankoreksi;
        $qty_usulankoreksibaik = $query->qty_usulankoreksibaik;
        $qty_usulankoreksiRelokasi = $query->qty_usulankoreksiRelokasi;
        $qty_usulankoreksisupplier = $query->qty_usulankoreksisupplier;
        $MutasiGudangBaikIn = $query->qty_MutasiGudangBaikIn;
        $MutasiGudangKoreksiIn = $query->qty_MutasiGudangKoreksiIn;
        $MutasiGudangLainIn = $query->qty_MutasiGudangLainIn;
        $MutasiGudangReturIn = $query->qty_MutasiGudangReturIn;
        $MutasiGudangRelokasiIn = $query->qty_MutasiGudangRelokasiIn;
        $MutasiGudangBaikOut = $query->qty_MutasiGudangBaikOut;
        $MutasiGudangKoreksiOut = $query->qty_MutasiGudangKoreksiOut;
        $MutasiGudangLainOut = $query->qty_MutasiGudangLainOut;
        $MutasiGudangReturOut = $query->qty_MutasiGudangReturOut;
        $MutasiGudangRelokasiOut = $query->qty_MutasiGudangRelokasiOut;
        
        $saldobaik = ($s_awalbaik + $bpb + $relokasi_terima + $qty_korbaik_plus + $returjual + $DORetur + $MutasiGudangBaikIn)  - ($usulanretursupplier  + $usulanrelokasi_kirim + $qty_korbaik_min + $DOkirim + $usulanrelokasi_kirim + $relokasi_kirim + $MutasiGudangBaikOut) + $qty_usulankoreksibaik + $tolakanretursupplier;
        $saldorelokasi = $s_awalrelokasi + $MutasiGudangRelokasiIn - $usulanrelokasi_kirim + $qty_usulankoreksiRelokasi - $MutasiGudangRelokasiOut;
        $saldoretursupp = ($s_awalretursup + $usulanretursupplier + $MutasiGudangReturIn) - $returbeli - $qty_kor_retsupplier_min + $qty_usulankoreksisupplier - $tolakanretursupplier - $MutasiGudangReturOut;
        $saldokoreksi = $s_awalkoreksi + $qty_allusulankoreksi + $MutasiGudangKoreksiIn - $MutasiGudangKoreksiOut;
        $saldolain2 = $s_awallain + $MutasiGudangLainIn - $MutasiGudangLainOut;
        $qbaik = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Baik'")->row();
        $this->db->query("update trs_invsum 
                          set unitstok = '".$saldobaik."'
                          where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Baik'
                          ");

        $qrelokasi = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Relokasi'")->num_rows();
        if($qrelokasi > 0){
          $this->db->query("update trs_invsum 
                          set unitstok = '".$saldorelokasi."'
                          where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Relokasi'
                          ");
        }else{
          $value = $qbaik->UnitCOGS * $saldorelokasi;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".date('Y')."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldorelokasi."','".$value."','Relokasi','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qsupplier = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Retur Supplier'")->num_rows();
        if($qsupplier > 0){
          $this->db->query("update trs_invsum 
                          set unitstok = '".$saldoretursupp."'
                          where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Retur Supplier'
                          ");
        }else{
          $value = $qbaik->UnitCOGS * $saldoretursupp;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".date('Y')."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldoretursupp."','".$value."','Retur Supplier','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qkoreksi = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Koreksi'")->num_rows();
        if($qkoreksi > 0){
          $this->db->query("update trs_invsum 
                            set unitstok = '".$saldokoreksi."'
                            where tahun = '".date('Y')."' and 
                                  KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                  gudang = 'Koreksi'
                            ");
        }else{
          $value = $qbaik->UnitCOGS * $saldokoreksi;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".date('Y')."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldokoreksi."','".$value."','Koreksi','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qlain = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Lain-lain'")->num_rows();
        if($qlain > 0){
          $this->db->query("update trs_invsum 
                            set unitstok = '".$saldolain2."'
                            where tahun = '".date('Y')."' and 
                                  KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                  gudang = 'Lain-lain'
                            ");
        }else{
          $value = $qbaik->UnitCOGS * $saldolain2;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".date('Y')."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldolain2."','".$value."','Lain-lain','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
      }
      $valid =true;
      return $valid;
    }


    public function getclosingstokbulanan($first_date = NULL,$end_date = NULL,$getmonth = NULL,$getyear=NULL)
    {
      $this->Model_main->getcogssummary();
       $query = $this->db->query("SELECT DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                                         trs_invsum.NamaProduk AS Produk,
                                         CONCAT(trs_invsum.KodeProduk ,' - ',trs_invsum.NamaProduk) AS 'NamaProduk',
                                         -- mst_produk.Satuan,
                                         '' AS Satuan,
                                         IFNULL(s_awal.saldo_awal,0) AS 'saldo_awal',
                                         IFNULL(terima.qty,0) AS 'qty_terima',
                                         IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                                         IFNULL(koreksi_plus.qty,0) AS 'qty_plus',
                                         IFNULL(retur.qty,0) AS 'qty_retur',
                                         IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                                         IFNULL(Delivery.qty,0) AS 'qty_do',
                                         IFNULL(returDelivery.qty,0) AS 'qty_returdo',
                                         IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                                         IFNULL(koreksi_min.qty,0) AS 'qty_min',
                                         IFNULL(stok.qty,0) AS 'qty_stok',
                                         IFNULL(stokdetail.qty,0) AS 'qty_stokdetail',
                                         IFNULL(stok_detail.Status_stok_detail,'') AS 'Status_stok_detail',
                                        (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
                                   FROM trs_invsum LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '".$getmonth."' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '".$this->cabang."' and Tahun ='".$getyear."'
                                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          SUM(ifnull(Qty,'')+ifnull(Bonus,'')) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          Status not in ('pending','Batal') and 
                                          ifnull(tipe,'') not in ('BKB','Tolakan')
                                    GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                         ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '".$this->cabang."' AND
                                          TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' and 
                                          ifnull(Tipe,'') ='BKB'
                                    GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(qty),0) AS 'qty' FROM (
                                      SELECT  Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_terima_detail
                                     WHERE  Cabang = '".$this->cabang."' AND 
                                            Status = 'Terima' and
                                            Tgl_terima BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY Produk

                                      UNION ALL

                                      SELECT  Produk,
                                            IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_kirim_detail
                                     WHERE  Cabang_Pengirim = '".$this->cabang."' AND 
                                            Status = 'Reject' and
                                            tgl_reject BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY Produk
                                     )zz GROUP BY zz.Produk
                                    ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                   ( SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '".$this->cabang."' AND
                                   CASE WHEN `Status` = 'Open' THEN
                                      tanggal
                                   ELSE
                                      IFNULL(DATE(tgl_approve),tanggal)
                                   END BETWEEN '".$first_date."' AND '".$end_date."' and status IN ('Approve','Open')
                                   GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          ifnull(status,'') != 'Batal' and
                                          Cabang = '".$this->cabang."' AND
                                          TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' 
                                    GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status != 'Batal' and tipedokumen ='DO' 
                                    AND TglDO BETWEEN '".$first_date  ."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '".$this->cabang."' AND Status = 'Retur' and tipedokumen ='Retur' 
                                    AND TglDO BETWEEN '".$first_date  ."' and '".$end_date."'
                                    GROUP BY KodeProduk
                                  ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (
                                    SELECT Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') and
                                        trs_relokasi_kirim_detail.Cabang_Pengirim = '".$this->cabang."' and 
                                        CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                                        between '".$first_date."' and '".$end_date."'
                                        GROUP BY Produk

                                  ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                    (SELECT produk,
                                            IFNULL(SUM(Qty * -1),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                                        status = 'Approve' and 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."'
                                  GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invsum
                                   where Tahun ='".$getyear."'
                                   GROUP BY KodeProduk) as stok on stok.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         sum(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invdet
                                   where Tahun ='".$getyear."'
                                   GROUP BY KodeProduk) as stokdetail on stokdetail.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         (CASE COUNT(UnitStok)
                                          WHEN 0 THEN 'Ok'
                                          ELSE 'Minus' END ) AS 'Status_stok_detail'
                                  FROM trs_invdet
                                  WHERE IFNULL(UnitStok,0) < 0 and Tahun ='".$getyear."'
                                  group by KodeProduk) as stok_detail on stok_detail.KodeProduk = trs_invsum.KodeProduk
                                  where trs_invsum.tahun ='".$getyear."'
                                  HAVING ((saldo_akhir != qty_stok) or (saldo_akhir < 0) or (qty_stokdetail != saldo_akhir))
                                ORDER BY trs_invsum.KodeProduk ASC
                                ")->result();
        return $query;

         // HAVING (saldo_awal > 0 ) OR (qty_terima > 0) 
         //                                OR (qty_plus > 0) OR (qty_retur > 0) 
         //                                OR (qty_do > 0) OR (qty_min > 0) OR (qty_stok > 0)

    }

    public function generatestokbulanan($params = NULL,$first_date= NULL,$end_date= NULL,$getmonth= NULL,$getyear= NULL)
    {
      $this->Model_main->getcogssummary();
      foreach ($params->Kode_Produk as $key => $value) 
      {
        $query = $this->db->query("
            SELECT DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                   trs_invsum.NamaProduk AS Produk,
                   IFNULL(s_awal.saldo_awal,0) AS 'saldo_awal',
                   IFNULL(s_awalbaik.saldo_awal,0) AS 's_awalbaik',
                   IFNULL(s_awalretursup.saldo_awal,0) AS 's_awalretursup',
                   IFNULL(s_awalrelokasi.saldo_awal,0) AS 's_awalrelokasi',
                   IFNULL(s_awalrelokasiout.saldo_awal,0) AS 's_awalrelokasiout',
                   IFNULL(s_awalkoreksi.saldo_awal,0) AS 's_awalkoreksi',
                   IFNULL(s_awallain.saldo_awal,0) AS 's_awallain',
                   IFNULL(terima.qty,0) AS 'qty_terima',
                   IFNULL(relokasi_terima.qty,0) AS 'qty_relokasi_terima',
                   IFNULL(korbaik_plus.qty,0) AS 'qty_korbaik_plus',
                   IFNULL(kor_retsup_plus.qty,0) AS 'qty_kor_retsuplier_plus',
                   IFNULL(kor_relokasi_plus.qty,0) AS 'qty_kor_relokasi_plus',
                   IFNULL(koreksi_plus.qty,0) AS 'qty_koreksi_plus',
                   IFNULL(retur.qty,0) AS 'qty_retur',
                   IFNULL(returbeli.qty,0) AS 'qty_returbeli',
                   IFNULL(usulanreturbeli.qty,0) AS 'qty_usulanreturbeli',
                   IFNULL(tolakanretur.qty,0) AS 'qty_tolakanretur',
                   IFNULL(Delivery.qty,0) AS 'qty_do',
                   IFNULL(returDelivery.qty,0) AS 'qty_returdo',
                   IFNULL(usulanrelokasi_kirim.qty,0) AS 'qty_usulanrelokasi_kirim',
                   IFNULL(relokasi_kirim.qty,0) AS 'qty_relokasi_kirim',
                   IFNULL(korbaik_min.qty,0) AS 'qty_korbaik_min',
                   IFNULL(kor_rel_min.qty,0) AS 'qty_kor_relokasi_min',
                   IFNULL(kor_retsup_min.qty,0) AS 'qty_kor_retsupplier_min',
                   IFNULL(koreksi_min.qty,0)  AS 'qty_koreksi_min',
                   IFNULL(allusulankoreksi.qty,0)  AS 'qty_allusulankoreksi',
                   IFNULL(usulankoreksibaik.qty,0)  AS 'qty_usulankoreksibaik',
                   IFNULL(usulankoreksiRelokasi.qty,0)  AS 'qty_usulankoreksiRelokasi',
                   IFNULL(usulankoreksisupplier.qty,0)  AS 'qty_usulankoreksisupplier',
                   IFNULL(MutasiGudangBaikIn.qty,0)  AS 'qty_MutasiGudangBaikIn',
                   IFNULL(MutasiGudangKoreksiIn.qty,0)  AS 'qty_MutasiGudangKoreksiIn',
                   IFNULL(MutasiGudangLainIn.qty,0)  AS 'qty_MutasiGudangLainIn',
                   IFNULL(MutasiGudangReturIn.qty,0)  AS 'qty_MutasiGudangReturIn',
                   IFNULL(MutasiGudangRelokasiIn.qty,0)  AS 'qty_MutasiGudangRelokasiIn',
                   IFNULL(MutasiGudangBaikOut.qty,0)  AS 'qty_MutasiGudangBaikOut',
                   IFNULL(MutasiGudangKoreksiOut.qty,0)  AS 'qty_MutasiGudangKoreksiOut',
                   IFNULL(MutasiGudangLainOut.qty,0)  AS 'qty_MutasiGudangLainOut',
                   IFNULL(MutasiGudangReturOut.qty,0)  AS 'qty_MutasiGudangReturOut',
                   IFNULL(MutasiGudangRelokasiOut.qty,0)  AS 'qty_MutasiGudangRelokasiOut',
                   IFNULL(stok.qty,0) AS 'qty_stok',
                   IFNULL(stokdetail.qty,0) AS 'qty_stokdetail',
                   IFNULL(stok_detail.Status_stok_detail,'') AS 'Status_stok_detail',
                   (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
             FROM trs_invsum LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."' 
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Baik'
                     GROUP BY KodeProduk ) AS s_awalbaik ON s_awalbaik.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."' 
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Retur Supplier'
                     GROUP BY KodeProduk ) AS s_awalretursup ON s_awalretursup.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Relokasi'
                     GROUP BY KodeProduk ) AS s_awalrelokasi ON s_awalrelokasi.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Relokasi Out'
                     GROUP BY KodeProduk ) AS s_awalrelokasiout ON s_awalrelokasiout.KodeProduk = trs_invsum.KodeProduk
               LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".$getmonth."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Koreksi'
                     GROUP BY KodeProduk ) AS s_awalkoreksi ON s_awalrelokasi.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                  (SELECT KodeProduk,
                         (CASE '".date('m')."'  
                          WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                          WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                          WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                          WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                          WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                          WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                          WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                          WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                          WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                          WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                          WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                          WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                          ELSE 0 END) AS 'saldo_awal' 
                     FROM trs_invsum 
                     WHERE Cabang = '".$this->cabang."' AND Tahun ='".date('Y')."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."' and gudang = 'Lain-lain'
                     GROUP BY KodeProduk ) AS s_awallain ON s_awallain.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                 (SELECT  Produk,
                          SUM(ifnull(Qty,'')+ifnull(Bonus,'')) AS 'qty'      
                     FROM trs_terima_barang_detail
                    WHERE  Cabang = '".$this->cabang."' AND
                           TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                           STATUS NOT IN ('pending','Batal') AND 
                           IFNULL(tipe,'') not in ('BKB','Tolakan') AND
                           Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT  Produk,
                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                    FROM trs_terima_barang_detail
                  WHERE  Cabang = '".$this->cabang."' AND
                         TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                         IFNULL(Tipe,'') ='BKB' AND
                         Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT  Produk,
                          ((SUM(ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty'       
                    FROM trs_terima_barang_detail
                  WHERE  Cabang = '".$this->cabang."' AND
                         TglDokumen BETWEEN '".$first_date."' AND '".$end_date."' AND 
                         IFNULL(Tipe,'') ='Tolakan' AND
                         Produk = '".$params->Kode_Produk[$key]."'
                  GROUP BY Produk) AS tolakanretur ON tolakanretur.Produk =trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                 (SELECT Produk ,
                         IFNULL(SUM(qty + bonus),0) AS 'qty'
                   FROM  trs_usulan_retur_beli_header INNER JOIN 
                         trs_usulan_retur_beli_detail 
                   ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan 
                   WHERE IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'') <> 'R' AND
                       trs_usulan_retur_beli_detail.Produk = '".$params->Kode_Produk[$key]."'AND 
                       trs_usulan_retur_beli_header.tanggal BETWEEN '".$first_date."' AND '".$end_date."'
                   GROUP BY Produk) AS usulanreturbeli ON usulanreturbeli.Produk =trs_invsum.KodeProduk 
             LEFT OUTER JOIN
                  (SELECT  Produk,
                          IFNULL(SUM(qty),0) AS 'qty' FROM (
                        SELECT  Produk,
                           IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                        FROM trs_relokasi_terima_detail
                        WHERE  Cabang = '".$this->cabang."' AND 
                        STATUS = 'Terima' AND
                               Tgl_terima BETWEEN '".$first_date."' AND '".$end_date."' AND
                               Produk = '".$params->Kode_Produk[$key]."'
                       GROUP BY Produk

                       UNION ALL

                       SELECT  Produk,
                             IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                        FROM trs_relokasi_kirim_detail
                        WHERE  Cabang_Pengirim = '".$this->cabang."' AND 
                        STATUS = 'Reject' AND
                               Tgl_kirim BETWEEN '".$first_date."' AND '".$end_date."' AND
                               Produk = '".$params->Kode_Produk[$key]."'
                       GROUP BY Produk
                     )zz GROUP BY zz.Produk
                   ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          CASE WHEN `Status` = 'Open' THEN
                            tanggal
                         ELSE
                            IFNULL(DATE(tgl_approve),tanggal)
                         END BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS IN ('Approve','Open') AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          CASE WHEN `Status` = 'Open' THEN
                              tanggal
                           ELSE
                              IFNULL(DATE(tgl_approve),tanggal)
                           END BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS IN ('Approve','Open') AND
                          gudang = 'Baik' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS korbaik_plus ON korbaik_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          tanggal BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS NOT IN ('Tolak') AND
                          gudang = 'Retur Supplier' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS kor_retsup_plus ON kor_retsup_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                   ( SELECT produk,
                         IFNULL(SUM(Qty),0) AS 'qty'    
                     FROM  trs_mutasi_koreksi 
                     WHERE  qty > 0 AND Cabang = '".$this->cabang."' AND
                          tanggal BETWEEN '".$first_date."' AND '".$end_date."' AND STATUS NOT IN ('Tolak') AND
                          gudang = 'Relokasi Out' AND Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY produk ) AS kor_relokasi_plus ON kor_relokasi_plus.Produk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                    (SELECT KodeProduk, 
                            IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                       FROM trs_faktur_detail 
                      WHERE TipeDokumen ='Retur' AND 
                            ifnull(status,'') != 'Batal' and
                            Cabang = '".$this->cabang."' AND
                            TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND
                            KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
              LEFT OUTER JOIN
                     (SELECT  KodeProduk,
                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                     FROM trs_delivery_order_sales_detail 
                     WHERE Cabang = '".$this->cabang."' AND STATUS != 'Batal' AND tipedokumen ='DO' 
                           AND TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                      GROUP BY KodeProduk
                      ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
             LEFT OUTER JOIN
                   (SELECT  KodeProduk,
                            IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                    FROM trs_delivery_order_sales_detail 
                    WHERE Cabang = '".$this->cabang."' AND STATUS = 'Retur' AND tipedokumen ='Retur' 
                           AND TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND
                          KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk
                    ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT Produk,
                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                    FROM  trs_relokasi_kirim_detail
                    WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') AND
                          trs_relokasi_kirim_detail.Cabang_Pengirim = '".$this->cabang."' AND 
                            CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                          BETWEEN '".$first_date."' AND '".$end_date."' AND
                          trs_relokasi_kirim_detail.Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY Produk
                   ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT Produk,
                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                    FROM  trs_relokasi_kirim_detail
                    WHERE trs_relokasi_kirim_detail.Status = 'Open' AND
                          trs_relokasi_kirim_detail.Cabang = '".$this->cabang."' AND 
                          trs_relokasi_kirim_detail.Tgl_kirim BETWEEN '".$first_date."' AND '".$end_date."' AND
                          trs_relokasi_kirim_detail.Produk = '".$params->Kode_Produk[$key]."'
                     GROUP BY Produk
                   ) AS usulanrelokasi_kirim ON usulanrelokasi_kirim.Produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Retur Supplier'
                   GROUP BY produk ) AS kor_retsup_min ON kor_retsup_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Baik'
                   GROUP BY produk ) AS korbaik_min ON korbaik_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."' AND gudang ='Relokasi Out'
                   GROUP BY produk ) AS kor_rel_min ON kor_rel_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT produk,
                         IFNULL(SUM(Qty * -1),0) AS 'qty' 
                   FROM  trs_mutasi_koreksi
                   WHERE   qty < 0 AND  Cabang = '".$this->cabang."' AND
                          STATUS = 'Approve' AND 
                          IFNULL(DATE(tgl_approve),tanggal) BETWEEN '".$first_date."' AND '".$end_date."' AND
                          produk = '".$params->Kode_Produk[$key]."'
                   GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS allusulankoreksi ON allusulankoreksi.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Baik' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksibaik ON usulankoreksibaik.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Relokasi' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksiRelokasi ON usulankoreksiRelokasi.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                  (SELECT trs_mutasi_koreksi.produk,
                         SUM(CASE WHEN IFNULL(qty,'') < 0 THEN ( IFNULL(qty,'') * -1) ELSE IFNULL(qty,'') END) AS 'qty'
                   FROM  trs_mutasi_koreksi 
                   WHERE trs_mutasi_koreksi.status = 'Open' AND trs_mutasi_koreksi.gudang ='Retur Supplier' and
                         trs_mutasi_koreksi.`produk` = '".$params->Kode_Produk[$key]."' AND 
                         tanggal BETWEEN '".$first_date."' AND '".$end_date."') AS usulankoreksisupplier ON usulankoreksisupplier.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Baik' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangBaikIn 
                  ON MutasiGudangBaikIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Koreksi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangKoreksiIn
              ON MutasiGudangKoreksiIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN      
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Lain-lain' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangLainIn
              ON MutasiGudangLainIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN    
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Retur Supplier' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."' 
            GROUP BY produk) AS MutasiGudangReturIn
              ON MutasiGudangReturIn.produk = trs_invsum.KodeProduk;
            LEFT OUTER JOIN      
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Relokasi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangRelokasiIn
              ON MutasiGudangRelokasiIn.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Baik' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangBaikOut
               ON MutasiGudangBaikOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Koreksi' AND
                 produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangKoreksiOut
               ON MutasiGudangKoreksiOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Lain-lain' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangLainOut 
               ON MutasiGudangLainOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_awal = 'Retur Supplier' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangReturOut
               ON MutasiGudangReturOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN   
            (SELECT produk,
              IFNULL(SUM(qty),0) AS 'qty'
            FROM trs_mutasi_gudang
            WHERE Cabang = '".$this->cabang."' AND 
                  gudang_akhir = 'Relokasi' AND
                  produk ='".$params->Kode_Produk[$key]."' AND 
                  tanggal BETWEEN '".$first_date."' AND '".$end_date."'
            GROUP BY produk) AS MutasiGudangRelokasiOut 
               ON MutasiGudangRelokasiOut.produk = trs_invsum.KodeProduk
            LEFT OUTER JOIN
                   (SELECT KodeProduk,
                          SUM(IFNULL(UnitStok,0)) AS 'qty'
                     FROM trs_invsum
                     WHERE Tahun ='".$getyear."' AND
                           KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS stok ON stok.KodeProduk = trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                   (SELECT KodeProduk,
                        SUM(IFNULL(UnitStok,0)) AS 'qty'
                     FROM trs_invdet
                    WHERE Tahun ='".$getyear."' AND
                          KodeProduk = '".$params->Kode_Produk[$key]."'
                     GROUP BY KodeProduk) AS stokdetail ON stokdetail.KodeProduk = trs_invsum.KodeProduk 
            LEFT OUTER JOIN
                    (SELECT KodeProduk,
                           (CASE COUNT(UnitStok)
                             WHEN 0 THEN 'Ok'
                             ELSE 'Minus' END ) AS 'Status_stok_detail'
                      FROM trs_invdet
                      WHERE IFNULL(UnitStok,0) < 0 AND Tahun ='".$getyear."' AND
                            KodeProduk = '".$params->Kode_Produk[$key]."'
                      GROUP BY KodeProduk) AS stok_detail ON stok_detail.KodeProduk = trs_invsum.KodeProduk
             WHERE trs_invsum.KodeProduk = '".$params->Kode_Produk[$key]."' and trs_invsum.tahun ='".$getyear."';
          ")->row();
        $saldoawal = $query->saldo_awal;
        $s_awalbaik = $query->s_awalbaik;
        $s_awalretursup = $query->s_awalretursup;
        $s_awalrelokasi = $query->s_awalrelokasi;
        $s_awalrelokasiout = $query->s_awalrelokasiout;
        $s_awalkoreksi = $query->s_awalkoreksi;
        $s_awallain = $query->s_awallain;
        $totalsaldo = $query->saldo_akhir;
        $usulanretursupplier = ($query->qty_usulanreturbeli * -1);
        $retursupplier = $query->qty_returbeli;
        $tolakanretur = $query->qty_tolakanretur;
        $bpb = $query->qty_terima;
        $relokasi_terima = $query->qty_relokasi_terima;
        $koreksiplus = $query->qty_koreksi_plus;
        $qty_korbaik_plus = $query->qty_korbaik_plus;
        $qty_kor_retsuplier_plus = $query->qty_kor_retsuplier_plus;
        $qty_kor_relokasi_plus = $query->qty_kor_relokasi_plus;
        $returbeli = $query->qty_returbeli;
        $returjual = $query->qty_retur;
        $DOkirim = $query->qty_do;
        $DORetur = $query->qty_returdo;
        $usulanrelokasi_kirim = $query->qty_usulanrelokasi_kirim;
        $relokasi_kirim = $query->qty_relokasi_kirim;
        $koreksimin = $query->qty_koreksi_min;
        $qty_korbaik_min = $query->qty_korbaik_min;
        $qty_kor_relokasi_min = $query->qty_kor_relokasi_min;
        $qty_kor_retsupplier_min = $query->qty_kor_retsupplier_min;
        $qty_allusulankoreksi = $query->qty_allusulankoreksi;
        $qty_usulankoreksibaik = $query->qty_usulankoreksibaik;
        $qty_usulankoreksiRelokasi = $query->qty_usulankoreksiRelokasi;
        $qty_usulankoreksisupplier = $query->qty_usulankoreksisupplier;
        $MutasiGudangBaikIn = $query->qty_MutasiGudangBaikIn;
        $MutasiGudangKoreksiIn = $query->qty_MutasiGudangKoreksiIn;
        $MutasiGudangLainIn = $query->qty_MutasiGudangLainIn;
        $MutasiGudangReturIn = $query->qty_MutasiGudangReturIn;
        $MutasiGudangRelokasiIn = $query->qty_MutasiGudangRelokasiIn;
        $MutasiGudangBaikOut = $query->qty_MutasiGudangBaikOut;
        $MutasiGudangKoreksiOut = $query->qty_MutasiGudangKoreksiOut;
        $MutasiGudangLainOut = $query->qty_MutasiGudangLainOut;
        $MutasiGudangReturOut = $query->qty_MutasiGudangReturOut;
        $MutasiGudangRelokasiOut = $query->qty_MutasiGudangRelokasiOut;
        $saldobaik = ($s_awalbaik + $bpb + $relokasi_terima + $qty_korbaik_plus + $returjual + $DORetur + $MutasiGudangBaikIn)  - ($usulanretursupplier  + $usulanrelokasi_kirim + $qty_korbaik_min + $DOkirim + $usulanrelokasi_kirim + $relokasi_kirim + $MutasiGudangBaikOut) + $qty_usulankoreksibaik + $tolakanretur;
        $saldorelokasi = $s_awalrelokasi + $MutasiGudangRelokasiIn - $usulanrelokasi_kirim + $qty_usulankoreksiRelokasi - $MutasiGudangRelokasiOut;
        $saldoretursupp = ($s_awalretursup + $usulanretursupplier + $MutasiGudangReturIn) - $returbeli - $qty_kor_retsupplier_min + $qty_usulankoreksisupplier - $tolakanretursupplier - $MutasiGudangReturOut;
        $saldokoreksi = $s_awalkoreksi + $qty_allusulankoreksi + $MutasiGudangKoreksiIn - $MutasiGudangKoreksiOut;
        $saldolain2 = $s_awallain + $MutasiGudangLainIn - $MutasiGudangLainOut;
        $qbaik = $this->db->query("select * from trs_invsum where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Baik'")->row();
        $this->db->query("update trs_invsum 
                          set unitstok = '".$saldobaik."'
                          where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Baik'
                          ");

        $qrelokasi = $this->db->query("select * from trs_invsum where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Relokasi'")->num_rows();
        if($qrelokasi > 0){
          $this->db->query("update trs_invsum 
                          set unitstok = '".$saldorelokasi."'
                          where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Relokasi'
                          ");
        }else{
          $value = $qbaik->UnitCOGS * $saldorelokasi;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".$getyear."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldorelokasi."','".$value."','Relokasi','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qsupplier = $this->db->query("select * from trs_invsum where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Retur Supplier'")->num_rows();
        if($qsupplier > 0){
          $this->db->query("update trs_invsum 
                          set unitstok = '".$saldoretursupp."'
                          where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Retur Supplier'
                          ");
        }else{
          $value = $qbaik->UnitCOGS * $saldoretursupp;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".$getyear."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldoretursupp."','".$value."','Retur Supplier','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qkoreksi = $this->db->query("select * from trs_invsum where tahun = '".$getyear."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Koreksi'")->num_rows();
        if($qkoreksi > 0){
          $this->db->query("update trs_invsum 
                            set unitstok = '".$saldokoreksi."'
                            where tahun = '".$getyear."' and 
                                  KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                  gudang = 'Koreksi'
                            ");
        }else{
          $value = $qbaik->UnitCOGS * $saldokoreksi;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".$getyear."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldokoreksi."','".$value."','Koreksi','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        $qlain = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' and 
                                KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                gudang = 'Lain-lain'")->num_rows();
        if($qlain > 0){
          $this->db->query("update trs_invsum 
                            set unitstok = '".$saldolain2."'
                            where tahun = '".date('Y')."' and 
                                  KodeProduk = '".$params->Kode_Produk[$key]."' and 
                                  gudang = 'Lain-lain'
                            ");
        }else{
          $value = $qbaik->UnitCOGS * $saldolain2;
          $this->db->query("
                  INSERT INTO `trs_invsum` (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                  `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,
                  `UnitCOGS`,`HNA`,`AddedUser`,`AddedTime`)VALUES('".date('Y')."','".$this->cabang."','".$qbaik->KodePrinsipal."','".$qbaik->NamaPrinsipal."','".$qbaik->Pabrik."','".$params->Kode_Produk[$key]."',
                    '".$qbaik->NamaProduk."','".$saldolain2."','".$value."','Lain-lain','1','".$qbaik->UnitCOGS."','".$qbaik->HNA."',
                    '".$this->use."','".date('Y-m-d_H-i-s')."')");
        }
        
      }
      $valid =true;
      return $valid;
    }


    public function jumlahdata()
    {

      $this->dbfed = $this->load->database('fedtemp', TRUE);
      $this->db3 = $this->load->database('pusat', TRUE); 


        // $this->db3 = $this->load->database('real', TRUE);  
        // $query = $this->db3->query("select count(Produk) as 'produk' from mst_produk")->row();
        // $mst_produk = $query->produk;

        // $query = $this->db3->query("select count(Pelanggan) as 'Pelanggan' from mst_pelanggan")->row();
        // $mst_pelanggan = $query->Pelanggan;

        // $query = $this->db3->query("select count(Nama) as 'Nama' from mst_karyawan")->row();
        // $mst_karyawan = $query->Nama;

        // $query = $this->db3->query("select max(id) as 'harga' from mst_harga")->row();
        // $mst_harga = $query->harga;

        // $query = $this->db3->query("select max(id) as 'prinsipal' from mst_prinsipal")->row();
        // $mst_prinsipal = $query->prinsipal;

        // $query = $this->db3->query("select max(id) as 'cabang' from mst_cabang")->row();
        // $mst_cabang = $query->cabang;

        // $query = $this->db3->query("select max(id) as 'ams3' from mst_data_ams3")->row();
        // $mst_data_ams3 = $query->ams3;

        // $query = $this->db3->query("select max(id) as 'disc_jual' from mst_diskon_jual")->row();
        // $mst_diskon_jual = $query->disc_jual;

        // $query = $this->db3->query("select count(bank) as 'bank' from mst_gl_bank")->row();
        // $mst_gl_bank = $query->bank;

        // $query = $this->db3->query("select count(`Kode Perkiraan`) as 'Perkiraan' from mst_gl_coa")->row();
        // $mst_gl_coa = $query->Perkiraan;

        // $query = $this->db3->query("select max(id) as 'NamaTransaksi' from mst_gl_transaksi_kat")->row();
        // $mst_gl_transaksi_kat = $query->NamaTransaksi;

        // $query = $this->db3->query("select count(NoFaktur) as 'NoFaktur' from trs_faktur")->row();
        // $trs_faktur = $query->NoFaktur;

        // $query = $this->db3->query("select count(NoDokumen) as 'NoDokumen' from trs_faktur_cndn")->row();
        // $cndn = $query->NoDokumen;

        // $query = $this->db3->query("select count(NoDO) as 'NoDO' from trs_delivery_order_sales")->row();
        // $trs_do = $query->NoDO;

        // $query = $this->db3->query("select count(*) as 'invsum' from trs_invsum")->row();
        // $trs_invsum = $query->invsum;

        // $query = $this->db3->query("select count(*) as 'invdet' from trs_invdet")->row();
        // $trs_invdet = $query->invdet;

        // $query = $this->db3->query("select count(*) as 'invhis' from trs_invhis")->row();
        // $trs_invhis = $query->invhis;

        // $query = $this->db3->query("select count(*) as 'dih' from trs_dih")->row();
        // $trs_dih = $query->dih;

        // $query = $this->db3->query("select count(*) as 'lunas' from trs_pelunasan_detail")->row();
        // $trs_pelunasan_detail = $query->lunas;

        // $query = $this->db3->query("select count(*) as 'giro' from trs_pelunasan_giro_detail")->row();
        // $trs_pelunasan_giro_detail = $query->giro;

        // $query = $this->db3->query("select count(*) as 'po' from trs_po_header")->row();
        // $trs_po_header = $query->po;

        // $query = $this->db3->query("select count(*) as 'terima' from trs_terima_barang_header")->row();
        // $trs_terima_barang_header = $query->terima;


        // $data = array(
        //     "mst_produk" => $mst_produk,
        //     "mst_pelanggan" => $mst_pelanggan,
        //     "mst_karyawan" => $mst_karyawan,
        //     "mst_harga" => $mst_harga,
        //     "mst_cabang" => $mst_cabang,
        //     "mst_prinsipal" => $mst_prinsipal,
        //     "mst_data_ams3" => $mst_data_ams3,
        //     "mst_diskon_jual" => $mst_diskon_jual,
        //     "mst_gl_bank" => $mst_gl_bank,
        //     "mst_gl_coa" => $mst_gl_coa,
        //     "mst_gl_transaksi_kat" => $mst_gl_transaksi_kat,
        //     "trs_faktur" => $trs_faktur,
        //     "cndn" => $cndn,
        //     "trs_do" => $trs_do,
        //     "trs_invsum" => $trs_invsum,
        //     "trs_invdet" => $trs_invdet,
        //     "trs_invhis" => $trs_invhis,
        //     "trs_dih" => $trs_dih,
        //     "trs_pelunasan_detail" => $trs_pelunasan_detail,
        //     "trs_pelunasan_giro_detail" => $trs_pelunasan_giro_detail,
        //     "trs_po_header" => $trs_po_header,
        //     "trs_terima_barang_header" => $trs_terima_barang_header,
        // );

        // return $data;

        // $this->db->query("SELECT count(*) as jumDOH FROM sst.trs_delivery_order_sales WHERE 
        //     (CASE WHEN IFNULL(modified_at,'') != '' 
        //       THEN YEARWEEK(DATE(modified_at), 1) = YEARWEEK(CURDATE(), 1) 
        //       ELSE YEARWEEK(DATE(TimeDO), 1) = YEARWEEK(CURDATE(), 1)
        //     END)")->row();
        // $DOH = $query->jumDOH;

        $this->db->query("INSERT INTO `mst_backuodb` ('".$this->cabang."','Jumalh DO Cabang ',date('Y-m-d_H-i-s'),date('Y-m-d_H-i-s'));");

      if ($this->dbfed->conn_id == TRUE)
      {

        $this->db->query("INSERT INTO `mst_backuodb` ('".$this->cabang."','Berhasil koneksi Temporary Cabang',date('Y-m-d_H-i-s'),date('Y-m-d_H-i-s'));");


      }else
      {

        $this->db->query("INSERT INTO `mst_backuodb` ('".$this->cabang."','Gagal koneksi Temporary Cabang',date('Y-m-d_H-i-s'),date('Y-m-d_H-i-s'));");
      }


      if ($this->db3->conn_id == TRUE)
      {

        $this->db->query("INSERT INTO `mst_backuodb` ('".$this->cabang."','Berhasil koneksi Temporary Pusat',date('Y-m-d_H-i-s'),date('Y-m-d_H-i-s'));");
      }else
      {
        $this->db->query("INSERT INTO `mst_backuodb` ('".$this->cabang."','Gagal koneksi Temporary Pusat',date('Y-m-d_H-i-s'),date('Y-m-d_H-i-s'));");
      }



    }


    public function federate_this_days()
    {
      // KONEKSII CABANNGGGGG
       $dbhost = 'localhost:3306';
       $dbuser = 'sapta';
       $dbpass = 'Sapta254*x';
       $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

       if(!$conn ){
          log_message("error","GAGAL KONEKSI CABANG");
          $this->send_message_tele("PROSES UPLOAD GAGAL KONEKSI CABANG ");
       }else
       {
          log_message("error","BERHASIL KONEKSI CABANG");    
          $this->send_message_tele("PROSES CREATE TEMP DATABASE CABANG ");
       // DROP AND CREATE FED DB 
           $buatdb = mysqli_query($conn,"DROP DATABASE sst_fed_temp");
           $buatdb = mysqli_query($conn,"CREATE DATABASE sst_fed_temp");
           if($buatdb){
                  log_message("error","Create database success");
                  //create

                  $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
                  while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];}

                  $this->send_message_tele("PROSES CREATE TABLE TEMP CABANG DAN TEMP PUSAT ");
                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create mst_karyawan : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   ");                    
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create mst_pelanggan : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_buku_giro : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_buku_kasbon: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_buku_transaksi: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_delivery_order_sales_detail : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_delivery_order_sales : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_faktur_cndn : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_faktur_detail : " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_faktur: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error createe trs_giro: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_invdet: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_invsum: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_kiriman: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_pelunasan_detail: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error trs_pelunasan_detail_ssp: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_pelunasan_giro_detail: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_po_detail: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error createtrs_po_header: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_sales_order_detail: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error create trs_sales_order: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error create trs_terima_barang_detail: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; "))
                  {
                      $this->send_message_tele("Error create trs_terima_barang_header: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; ");
                  }
                  

                  // $this->send_message_tele("FINISH CREATE TEMP CABANG DAN TEMP PUSAT ");


                  //DO
                  $this->send_message_tele("PROSES INSERT DATA TEMP CABANG DAN PUSAT ");

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
                  

                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." SELECT * FROM sst.trs_delivery_order_sales WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);"))
                  {
                      $this->send_message_tele("Error trs_delivery_order_sales_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." SELECT * FROM sst.trs_delivery_order_sales WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);");
                  }

                  //DO Detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END ) ;"))
                  {
                      $this->send_message_tele("Error trs_delivery_order_sales_detail_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);");
                  }

                  //Kiriman
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_kiriman_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." SELECT * FROM sst.trs_kiriman WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW())END ELSE CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);"))
                  {
                      $this->send_message_tele("Error trs_kiriman_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." SELECT * FROM sst.trs_kiriman WHERE (CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW())END ELSE CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);");
                  }

                  //Faktur
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." SELECT * FROM sst.trs_faktur WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END) ;"))
                  {
                      $this->send_message_tele("Error trs_faktur_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." SELECT * FROM sst.trs_faktur WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END);");
                  }

                  //Faktur Detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail JOIN ( SELECT Cabang, NoFaktur, modified_at, TimeFaktur FROM sst.trs_faktur) AS trs_faktur ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang WHERE (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' THEN YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) END);"))
                  {
                      $this->send_message_tele("Error trs_faktur_detail_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail JOIN (SELECT Cabang, NoFaktur, modified_at, TimeFaktur FROM sst.trs_faktur) AS trs_faktur ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang WHERE (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' THEN YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) END);");
                  }

                  //cndn
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." SELECT * FROM sst.trs_faktur_cndn WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );"))
                  {
                      $this->send_message_tele("Error trs_faktur_cndn_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." SELECT * FROM sst.trs_faktur_cndn WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(updated_at,'') != '' THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );");
                  }

                  //pelunasan detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_detail WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );"))
                  {
                      $this->send_message_tele("Error trs_pelunasan_detail_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_detail WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END )");
                  }

                  //pelunasan giro detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_giro_detail WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );"))
                  {
                      $this->send_message_tele("Error trs_pelunasan_giro_detail_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_giro_detail WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );");
                  }

                  //giro
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_giro_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." SELECT * FROM sst.trs_giro WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );"))
                  {
                      $this->send_message_tele("Error trs_giro_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." SELECT * FROM sst.trs_giro WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(modified_at,'') != '' THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END );");
                  }

                  // bukutransaksi
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." SELECT * FROM sst.trs_buku_transaksi WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(Modified_Time,'') != '' THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(Modified_Time,'') != '' THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END )  ;"))
                  {
                      $this->send_message_tele("Error trs_buku_transaksi_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." SELECT * FROM sst.trs_buku_transaksi WHERE ( CASE WHEN DATE_FORMAT(NOW(),'%w') IN (0,1) THEN CASE WHEN IFNULL(Modified_Time,'') != '' THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) END ELSE CASE WHEN IFNULL(Modified_Time,'') != '' THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) END END ) ;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invsum_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;"))
                  {
                      $this->send_message_tele("Error trs_invsum_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invdet_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;"))
                  {
                      $this->send_message_tele("Error trs_invdet_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_pelanggan_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;"))
                  {
                      $this->send_message_tele("Error mst_pelanggan_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_karyawan_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;"))
                  {
                      $this->send_message_tele("Error mst_karyawan_fed_: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;");
                  }

                  // $this->send_message_tele("FINISH INSERT DATA TEMP CABANG DAN TEMP PUSAT ");

            }else{
                log_message("error","Create Failed");
                $this->send_message_tele("GAGAL CREATE TEMP DATABASE CABANG ");
                // $valid=FALSE;
            }              
       }
      mysqli_close($conn);


      // KONEKKSIII PUSATTTT
       $dbhostpst = '119.235.19.138:3306';
       $dbuserpst = 'sapta';
       $dbpasspst = 'Sapta254*x';
       $connpst = mysqli_connect($dbhostpst, $dbuserpst, $dbpasspst);
       

       if(!$connpst ){
          log_message("error","GAGAL KONEKSI PUSAT");
          $this->send_message_tele("GAGAL KONEKSI DATABASE PUSAT ");
       }else{

          log_message("error","BERHASIL KONEKSI PUSAT");
         $this->send_message_tele("PROSES UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

            

          if (!mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur"))
            {
                $this->send_message_tele("Error description on pst trs_delivery_order_sales: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales_detail SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,status_validasi = t_order.status_validasi,noretur = t_order.noretur;"))
            {
                $this->send_message_tele("Error description on pst trs_delivery_order_sales_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales_detail SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,status_validasi = t_order.status_validasi,noretur = t_order.noretur;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
            }



            if (!mysqli_query($connpst,"INSERT INTO sst.trs_kiriman SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;"))
            {
                $this->send_message_tele("Error description on pst trs_kiriman: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_kiriman SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab.";");
            }


            if (mysqli_query($connpst,"INSERT INTO sst.trs_faktur SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                "))
            {
                $this->send_message_tele("Error description on pst trs_faktur: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                ");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_faktur_detail SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;"))
            {
                $this->send_message_tele("Error description on pst trs_faktur_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur_detail SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_faktur_cndn SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;"))
            {
                $this->send_message_tele("Error description on pst trs_faktur_cndn: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur_cndn SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab.";");
            }    


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_detail SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;"))
            {
                $this->send_message_tele("Error description on pst trs_pelunasan_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_detail SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_giro_detail SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;"))
            {
                $this->send_message_tele("Error description on pst trs_pelunasan_giro_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_giro_detail SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_giro SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;"))
            {
                $this->send_message_tele("Error description on pst trs_giro: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_giro SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_giro_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_buku_transaksi_all SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,Jurnal_ID=t_gl.Jurnal_ID;"))
            {
                $this->send_message_tele("Error description on pst trs_buku_transaksi_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_buku_transaksi_all SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,Jurnal_ID=t_gl.Jurnal_ID;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab.";");
            }
    
            mysqli_query($connpst,"DELETE FROM sst.trs_invsum_all where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.trs_invsum_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description on pst trs_invsum_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";");
            }


            mysqli_query($connpst,"DELETE FROM sst.trs_invdet_all where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.trs_invdet_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.trs_invdet_all(Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut) SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description on pst trs_invdet_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_invdet_all(Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut) SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";");
            }
                
            mysqli_query($connpst,"DELETE FROM sst.mst_pelanggan where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.mst_pelanggan WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description on pst mst_pelanggan: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";");
            }

       $this->send_message_tele("FINISH UPDATE DATA TEMP PUSAT KE REAL PUSAT ");
       mysqli_close($connpst);
        }

    }


    public function federate_this_week()
    {
      // KONEKSII CABANNGGGGG
       $dbhost = 'localhost:3306';
       $dbuser = 'sapta';
       $dbpass = 'Sapta254*x';
       $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

       if(!$conn ){
          log_message("error","GAGAL KONEKSI CABANG");
          $this->send_message_tele("PROSES UPLOAD GAGAL KONEKSI CABANG ");
       }else
       {
          log_message("error","BERHASIL KONEKSI CABANG");    
          $this->send_message_tele("PROSES CREATE TEMP DATABASE CABANG ");
       // DROP AND CREATE FED DB 
           $buatdb = mysqli_query($conn,"DROP DATABASE sst_fed_temp");
           $buatdb = mysqli_query($conn,"CREATE DATABASE sst_fed_temp");
           if($buatdb){
                  log_message("error","Create database success");
                  //create

                  $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
                  while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];}

                  $this->send_message_tele("PROSES CREATE TABLE TEMP CABANG DAN TEMP PUSAT ");
                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   ");                    
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  ");
                  }


                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   ");
                  }

                  if (!mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; "))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab."  ENGINE=FEDERATED COLLATE = latin1_swedish_ci CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; ");
                  }
                  

                  $this->send_message_tele("FINISH CREATE TEMP CABANG DAN TEM PUSAT ");


                  //DO
                  $this->send_message_tele("PROSES INSERT DATA TEMP CABANG DAN PUSAT ");

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
                  

                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." SELECT * FROM sst.trs_delivery_order_sales WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN YEARWEEK(DATE(modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeDO), 1) = YEARWEEK(CURDATE(), 1)END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." SELECT * FROM sst.trs_delivery_order_sales WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN YEARWEEK(DATE(modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeDO), 1) = YEARWEEK(CURDATE(), 1)END);");
                  }

                  //DO Detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang WHERE (CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN YEARWEEK(DATE(trs_delivery_order_sales.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_delivery_order_sales.TimeDO), 1) = YEARWEEK(CURDATE(), 1) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang WHERE (CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' THEN YEARWEEK(DATE(trs_delivery_order_sales.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_delivery_order_sales.TimeDO), 1) = YEARWEEK(CURDATE(), 1) END);");
                  }

                  //Kiriman
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_kiriman_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." SELECT * FROM sst.trs_kiriman WHERE (CASE WHEN IFNULL(updated_at,'') != '' THEN YEARWEEK(DATE(updated_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeKirim), 1) = YEARWEEK(CURDATE(), 1) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." SELECT * FROM sst.trs_kiriman WHERE (CASE WHEN IFNULL(updated_at,'') != '' THEN YEARWEEK(DATE(updated_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeKirim), 1) = YEARWEEK(CURDATE(), 1) END);");
                  }

                  //Faktur
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." SELECT * FROM sst.trs_faktur WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN YEARWEEK(DATE(modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeFaktur), 1) = YEARWEEK(CURDATE(), 1) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." SELECT * FROM sst.trs_faktur WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN YEARWEEK(DATE(modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(TimeFaktur), 1) = YEARWEEK(CURDATE(), 1) END);");
                  }

                  //Faktur Detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail JOIN (SELECT Cabang,NoFaktur,modified_at,TimeFaktur FROM sst.trs_faktur) AS trs_faktur ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang WHERE (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' THEN YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail JOIN (SELECT Cabang,NoFaktur,modified_at,TimeFaktur FROM sst.trs_faktur) AS trs_faktur ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang WHERE (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' THEN YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) ELSE YEARWEEK(DATE(trs_faktur.modified_at), 1) = YEARWEEK(CURDATE(), 1) END);");
                  }

                  //cndn
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." SELECT * FROM sst.trs_faktur_cndn WHERE (CASE WHEN IFNULL(updated_at,'') != '' THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(created_at)),MONTH(DATE(created_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." SELECT * FROM sst.trs_faktur_cndn WHERE (CASE WHEN IFNULL(updated_at,'') != '' THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(created_at)),MONTH(DATE(created_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);");
                  }

                  //pelunasan detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_detail WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_detail WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);");
                  }

                  //pelunasan giro detail
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_giro_detail WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." SELECT * FROM sst.trs_pelunasan_giro_detail WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);");
                  }

                  //giro
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_giro_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." SELECT * FROM sst.trs_giro WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." SELECT * FROM sst.trs_giro WHERE (CASE WHEN IFNULL(modified_at,'') != '' THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);");
                  }

                  // bukutransaksi
                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." SELECT * FROM sst.trs_buku_transaksi WHERE (CASE WHEN IFNULL(Modified_Time,'') != '' THEN CONCAT(YEAR(DATE(Modified_Time)),MONTH(DATE(Modified_Time))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(Tanggal)),MONTH(DATE(Tanggal))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." SELECT * FROM sst.trs_buku_transaksi WHERE (CASE WHEN IFNULL(Modified_Time,'') != '' THEN CONCAT(YEAR(DATE(Modified_Time)),MONTH(DATE(Modified_Time))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) ELSE CONCAT(YEAR(DATE(Tanggal)),MONTH(DATE(Tanggal))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) END);");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invsum_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invdet_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_pelanggan_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;");
                  }

                  mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_karyawan_fed_".$kkdcab.";");
                  if (!mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;"))
                  {
                      $this->send_message_tele("Error description: " . mysqli_error($conn));
                  }else
                  {
                      mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;");
                  }

                  $this->send_message_tele("FINISH INSERT DATA TEMP CABANG DAN TEMP PUSAT ");

            }else{
                log_message("error","Create Failed");
                $this->send_message_tele("GAGAL CREATE TEMP DATABASE CABANG ");
                // $valid=FALSE;
            }              
       }
      mysqli_close($conn);


      // KONEKKSIII PUSATTTT
       $dbhostpst = '119.235.19.138:3306';
       $dbuserpst = 'sapta';
       $dbpasspst = 'Sapta254*x';
       $connpst = mysqli_connect($dbhostpst, $dbuserpst, $dbpasspst);
       

       if(!$connpst ){
          log_message("error","GAGAL KONEKSI PUSAT");
          $this->send_message_tele("GAGAL KONEKSI DATABASE PUSAT ");
       }else{

          log_message("error","BERHASIL KONEKSI PUSAT");
         $this->send_message_tele("PROSES UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

            

          if (!mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur"))
            {
                $this->send_message_tele("Error description on pst trs_delivery_order_sales: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales_detail SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,status_validasi = t_order.status_validasi,noretur = t_order.noretur;"))
            {
                $this->send_message_tele("Error description on pst trs_delivery_order_sales_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_delivery_order_sales_detail SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,time_batal = t_order.time_batal,user_batal= t_order.user_batal,modified_at = t_order.modified_at, modified_by = t_order.modified_by,Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,status_validasi = t_order.status_validasi,noretur = t_order.noretur;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
            }



            if (!mysqli_query($connpst,"INSERT INTO sst.trs_kiriman SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;"))
            {
                $this->send_message_tele("Error description on pst trs_kiriman: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_kiriman SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab.";");
            }


            if (mysqli_query($connpst,"INSERT INTO sst.trs_faktur SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                "))
            {
                $this->send_message_tele("Error description on pst trs_faktur: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                ");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_faktur_detail SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;"))
            {
                $this->send_message_tele("Error description trs_faktur_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur_detail SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_faktur_cndn SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;"))
            {
                $this->send_message_tele("Error description trs_faktur_cndn: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_faktur_cndn SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab.";");
            }    


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_detail SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;"))
            {
                $this->send_message_tele("Error description trs_pelunasan_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_detail SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_giro_detail SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;"))
            {
                $this->send_message_tele("Error description trs_pelunasan_giro_detail: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_pelunasan_giro_detail SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_giro SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;"))
            {
                $this->send_message_tele("Error description trs_giro: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_giro SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_giro_fed_".$kkdcab.";");
            }


            if (!mysqli_query($connpst,"INSERT INTO sst.trs_buku_transaksi_all SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,Jurnal_ID=t_gl.Jurnal_ID;"))
            {
                $this->send_message_tele("Error description trs_buku_transaksi_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_buku_transaksi_all SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,Jurnal_ID=t_gl.Jurnal_ID;");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab.";");
            }
    
            mysqli_query($connpst,"DELETE FROM sst.trs_invsum_all where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.trs_invsum_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description trs_invsum_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";");
            }


            mysqli_query($connpst,"DELETE FROM sst.trs_invdet_all where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.trs_invdet_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.trs_invdet_all(Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut) SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description trs_invdet_all: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.trs_invdet_all(Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut) SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";");
            }
                
            mysqli_query($connpst,"DELETE FROM sst.mst_pelanggan where ifnull(cabang,'')='';");
            mysqli_query($connpst,"DELETE FROM sst.mst_pelanggan WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.") > 0;");
            if (!mysqli_query($connpst,"INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";"))
            {
                $this->send_message_tele("Error description mst_pelanggan: " . mysqli_error($connpst));
            }else
            {
                mysqli_query($connpst,"INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";");
                mysqli_query($connpst,"DELETE FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";");
            }

       $this->send_message_tele("FINISH UPDATE DATA TEMP PUSAT KE REAL PUSAT ");
       mysqli_close($connpst);
        }

    }

    public function federate_this_month()
    {

      // KONEKSII CABANNGGGGG
       $dbhost = 'localhost:3306';
       $dbuser = 'sapta';
       $dbpass = 'Sapta254*x';
       $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

       if(!$conn ){
          log_message("error","GAGAL KONEKSI CABANG");
          $this->send_message_tele("PROSES UPLOAD GAGAL KONEKSI CABANG ");
       }else
       {
          log_message("error","BERHASIL KONEKSI CABANG");    
          $this->send_message_tele("PROSES CREATE TEMP DATABASE CABANG ");
          // DROP AND CREATE FED DB 
           $buatdb = mysqli_query($conn,"DROP DATABASE sst_fed_temp");
           $buatdb = mysqli_query($conn,"CREATE DATABASE sst_fed_temp");
           if($buatdb){
                  log_message("error","Create database success");
                  //create

                  $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
                  while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];}

                  $this->send_message_tele("PROSES CREATE TABLE TEMP CABANG DAN TEMP PUSAT ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  "); 
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  "); 
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; ");
                  $this->send_message_tele("FINISH CREATE TEMP CABANG DAN TEM PUSAT ");

                //DO
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_delivery_order_sales WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(TimeDO)),MONTH(DATE(TimeDO))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //DO Detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." 
                    SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail 
                      JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales 
                      ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO 
                        AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang
                      WHERE 
                      (CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(trs_delivery_order_sales.modified_at)),MONTH(DATE(trs_delivery_order_sales.modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(trs_delivery_order_sales.TimeDO)),MONTH(DATE(trs_delivery_order_sales.TimeDO))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //Kiriman
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_kiriman_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_kiriman 
                      WHERE 
                      (CASE WHEN IFNULL(updated_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(TimeKirim)),MONTH(DATE(TimeKirim))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //Faktur
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_faktur 
                      WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(TimeFaktur)),MONTH(DATE(TimeFaktur))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //Faktur Detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." 
                    SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail 
                      JOIN (SELECT Cabang,NoFaktur,modified_at,TimeFaktur FROM sst.trs_faktur) AS trs_faktur 
                        ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang
                      WHERE 
                      (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(trs_faktur.modified_at)),MONTH(DATE(trs_faktur.modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(trs_faktur.TimeFaktur)),MONTH(DATE(trs_faktur.TimeFaktur))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //cndn
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_faktur_cndn 
                      WHERE 
                      (CASE WHEN IFNULL(updated_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(created_at)),MONTH(DATE(created_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //pelunasan detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_pelunasan_detail 
                      WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //pelunasan giro detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_pelunasan_giro_detail 
                      WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                //giro
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_giro_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_giro 
                      WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                        THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                // bukutransaksi
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab."
                    SELECT * FROM sst.trs_buku_transaksi 
                      WHERE 
                      (CASE WHEN IFNULL(Modified_Time,'') != '' 
                        THEN CONCAT(YEAR(DATE(Modified_Time)),MONTH(DATE(Modified_Time))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                        ELSE CONCAT(YEAR(DATE(Tanggal)),MONTH(DATE(Tanggal))) = CONCAT(YEAR(CURDATE()),MONTH(CURDATE())) 
                      END);
                  ");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invsum_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invdet_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_pelanggan_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_karyawan_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;");

                $this->send_message_tele("FINISH INSERT DATA TEMP CABANG DAN TEMP PUSAT ");

            }else{
                log_message("error","Create Failed");
                $this->send_message_tele("GAGAL CREATE TEMP DATABASE CABANG ");
                // $valid=FALSE;
            }              
       }

      mysqli_close($conn);

      // KONEKKSIII PUSATTTT
       $dbhostpst = '119.235.19.138:3306';
       $dbuserpst = 'sapta';
       $dbpasspst = 'Sapta254*x';
       $connpst = mysqli_connect($dbhost, $dbuser, $dbpass);
       

       if(!$connpst ){
          log_message("error","GAGAL KONEKSI PUSAT");
          $this->send_message_tele("GAGAL KONEKSI DATABASE PUSAT ");
       }else{

          log_message("error","BERHASIL KONEKSI PUSAT");
         $this->send_message_tele("PROSES UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

            
            mysqli_query($connpst,"
                INSERT INTO sst.trs_delivery_order_sales
                            SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order
                            ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
                            time_batal = t_order.time_batal,user_batal= t_order.user_batal,
                            modified_at = t_order.modified_at, modified_by = t_order.modified_by,
                            Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,
                            user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,
                            user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur ;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_delivery_order_sales_detail
                              SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order
                              ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
                              time_batal = t_order.time_batal,user_batal= t_order.user_batal,
                              modified_at = t_order.modified_at, modified_by = t_order.modified_by,
                              Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,
                              retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,
                              status_validasi = t_order.status_validasi,noretur = t_order.noretur;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";      
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_kiriman
                            SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim
                            ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,
                            TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,
                            updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab."; 
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur
                                SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur
                                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,
                                StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,
                                umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,
                                TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,
                                modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,
                                nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur_detail
                                SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur
                                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur_cndn
                         SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur
                         ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,
                         DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,
                         Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,
                         Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,
                         Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                  
                INSERT INTO sst.trs_pelunasan_detail
                        SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas
                        ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
                        Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
                        SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,
                        modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
                        bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
                        materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
                        No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                 
                INSERT INTO sst.trs_pelunasan_giro_detail
                            SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas
                            ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
                            Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
                            SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,
                            modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
                            bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
                            materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
                            No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_giro
                        SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro
                        ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,
                        TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_giro_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                            
                INSERT INTO sst.trs_buku_transaksi_all
                      SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl
                      ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,
                      Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,
                      Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,
                      Jurnal_ID=t_gl.Jurnal_ID;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invsum_all where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invsum_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invdet_all where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invdet_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_invdet_all  
                  (Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,
                    Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,
                    SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,
                    LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut)
                SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.mst_pelanggan where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.mst_pelanggan WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";
                ");
        }          
       $this->send_message_tele("FINISH UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

      mysqli_close($connpst);
    

    }

    public function federate_two_month()
    {

      // KONEKSII CABANNGGGGG
       $dbhost = 'localhost:3306';
       $dbuser = 'sapta';
       $dbpass = 'Sapta254*x';
       $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

       if(!$conn ){
          log_message("error","GAGAL KONEKSI CABANG");
          $this->send_message_tele("PROSES UPLOAD GAGAL KONEKSI CABANG ");
       }else
       {
          log_message("error","BERHASIL KONEKSI CABANG");    
          $this->send_message_tele("PROSES CREATE TEMP DATABASE CABANG ");
          // DROP AND CREATE FED DB 
           $buatdb = mysqli_query($conn,"DROP DATABASE sst_fed_temp");
           $buatdb = mysqli_query($conn,"CREATE DATABASE sst_fed_temp");
           if($buatdb){
                  log_message("error","Create database success");
                  //create

                  $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
                  while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];}

                  $this->send_message_tele("PROSES CREATE TABLE TEMP CABANG DAN TEMP PUSAT ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;  "); 
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;  "); 
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   ");
                  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_".$kkdcab." 
                    ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
                    CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_".$kkdcab."' 
                    COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; ");
                  $this->send_message_tele("FINISH CREATE TEMP CABANG DAN TEM PUSAT ");

                //DO
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_delivery_order_sales WHERE 
                      (CASE WHEN IFNULL(modified_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(TimeDO)),MONTH(DATE(TimeDO))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                      END);
                  ");

                //DO Detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." 
                    SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail 
                      JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales 
                      ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO 
                        AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang
                      WHERE 
                        (CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' 
                            THEN CONCAT(YEAR(DATE(trs_delivery_order_sales.modified_at)),MONTH(DATE(trs_delivery_order_sales.modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                            ELSE CONCAT(YEAR(DATE(trs_delivery_order_sales.TimeDO)),MONTH(DATE(trs_delivery_order_sales.TimeDO))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //Kiriman
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_kiriman_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_kiriman_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_kiriman 
                      WHERE 
                        (CASE WHEN IFNULL(updated_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(TimeKirim)),MONTH(DATE(TimeKirim))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //Faktur
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_faktur 
                      WHERE 
                        (CASE WHEN IFNULL(modified_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(TimeFaktur)),MONTH(DATE(TimeFaktur))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //Faktur Detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_detail_fed_".$kkdcab." 
                    SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail 
                      JOIN (SELECT Cabang,NoFaktur,modified_at,TimeFaktur FROM sst.trs_faktur) AS trs_faktur 
                        ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang
                      WHERE 
                        (CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' 
                            THEN CONCAT(YEAR(DATE(trs_faktur.modified_at)),MONTH(DATE(trs_faktur.modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                            ELSE CONCAT(YEAR(DATE(trs_faktur.TimeFaktur)),MONTH(DATE(trs_faktur.TimeFaktur))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //cndn
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_faktur_cndn 
                      WHERE 
                        (CASE WHEN IFNULL(updated_at,'') != '' 
                            THEN CONCAT(YEAR(DATE(updated_at)),MONTH(DATE(updated_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                            ELSE CONCAT(YEAR(DATE(created_at)),MONTH(DATE(created_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //pelunasan detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_pelunasan_detail 
                      WHERE 
                        (CASE WHEN IFNULL(modified_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //pelunasan giro detail
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_pelunasan_giro_detail 
                      WHERE 
                        (CASE WHEN IFNULL(modified_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                //giro
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_giro_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_giro_fed_".$kkdcab." 
                    SELECT * FROM sst.trs_giro 
                      WHERE 
                        (CASE WHEN IFNULL(modified_at,'') != '' 
                          THEN CONCAT(YEAR(DATE(modified_at)),MONTH(DATE(modified_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(create_at)),MONTH(DATE(create_at))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                // bukutransaksi
                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab.";");
                mysqli_query($conn,"
                    INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_".$kkdcab."
                    SELECT * FROM sst.trs_buku_transaksi 
                      WHERE 
                        (CASE WHEN IFNULL(Modified_Time,'') != '' 
                          THEN CONCAT(YEAR(DATE(Modified_Time)),MONTH(DATE(Modified_Time))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                          ELSE CONCAT(YEAR(DATE(Tanggal)),MONTH(DATE(Tanggal))) IN (CONCAT(YEAR(CURDATE()),MONTH(CURDATE())),CONCAT(YEAR(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)),MONTH(DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)))) 
                        END);
                  ");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invsum_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invsum_fed_".$kkdcab." SELECT * FROM sst.trs_invsum;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.trs_invdet_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.trs_invdet_fed_".$kkdcab." SELECT * FROM sst.trs_invdet;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_pelanggan_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_pelanggan_fed_".$kkdcab." SELECT * FROM sst.mst_pelanggan;");

                mysqli_query($conn,"DELETE FROM sst_fed_temp.mst_karyawan_fed_".$kkdcab.";");
                mysqli_query($conn,"INSERT INTO sst_fed_temp.mst_karyawan_fed_".$kkdcab." SELECT * FROM sst.mst_karyawan;");

                $this->send_message_tele("FINISH INSERT DATA TEMP CABANG DAN TEMP PUSAT ");

            }else{
                log_message("error","Create Failed");
                $this->send_message_tele("GAGAL CREATE TEMP DATABASE CABANG ");
                // $valid=FALSE;
            }              
       }

      mysqli_close($conn);

      // KONEKKSIII PUSATTTT
       $dbhostpst = '119.235.19.138:3306';
       $dbuserpst = 'sapta';
       $dbpasspst = 'Sapta254*x';
       $connpst = mysqli_connect($dbhost, $dbuser, $dbpass);
       

       if(!$connpst ){
          log_message("error","GAGAL KONEKSI PUSAT");
          $this->send_message_tele("GAGAL KONEKSI DATABASE PUSAT ");
       }else{

          log_message("error","BERHASIL KONEKSI PUSAT");
         $this->send_message_tele("PROSES UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

            
            mysqli_query($connpst,"
                INSERT INTO sst.trs_delivery_order_sales
                            SELECT * FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab." t_order
                            ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
                            time_batal = t_order.time_batal,user_batal= t_order.user_batal,
                            modified_at = t_order.modified_at, modified_by = t_order.modified_by,
                            Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,status_retur= t_order.status_retur,
                            user_retur= t_order.user_retur,time_retur= t_order.time_retur,status_validasi= t_order.status_validasi,
                            user_validasi= t_order.user_validasi,time_validasi= t_order.time_validasi,noretur= t_order.noretur ;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_delivery_order_sales_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_delivery_order_sales_detail
                              SELECT * FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab." t_order
                              ON DUPLICATE KEY UPDATE STATUS = t_order.Status,NoFaktur = t_order.NoFaktur,
                              time_batal = t_order.time_batal,user_batal= t_order.user_batal,
                              modified_at = t_order.modified_at, modified_by = t_order.modified_by,
                              Pengirim = t_order.Pengirim, NamaPengirim = t_order.Pengirim,retur_qtyDO = t_order.retur_qtyDO,
                              retur_bonusDO = t_order.retur_bonusDO,status_retur = t_order.status_retur,
                              status_validasi = t_order.status_validasi,noretur = t_order.noretur;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_delivery_order_sales_detail_fed_".$kkdcab.";      
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_kiriman
                            SELECT * FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab." t_kirim
                            ON DUPLICATE KEY UPDATE StatusKiriman = t_kirim.StatusKiriman,StatusDO = t_kirim.StatusDO,
                            TglTerima = t_kirim.TglTerima,TimeTerima= t_kirim.TimeTerima,Alasan = t_kirim.Alasan,
                            updated_at = t_kirim.updated_at, updated_by = t_kirim.updated_by;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_kiriman_fed_".$kkdcab."; 
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur
                                SELECT * FROM sst_federate_temp.trs_faktur_fed_".$kkdcab." t_faktur
                                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Saldo = t_faktur.Saldo,
                                StatusInkaso = t_faktur.StatusInkaso,TimeInkaso =t_faktur.TimeInkaso,
                                umur_faktur = t_faktur.umur_faktur,umur_pelunasan = t_faktur.umur_pelunasan,
                                TglPelunasan = t_faktur.TglPelunasan, saldo_giro = t_faktur.saldo_giro,
                                modified_at = t_faktur.modified_at, modified_by = t_faktur.modified_by,counter_print = t_faktur.counter_print,
                                nodih = t_faktur.nodih,tgldih = t_faktur.nodih;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur_detail
                                SELECT * FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab." t_faktur
                                ON DUPLICATE KEY UPDATE STATUS = t_faktur.Status,Total = t_faktur.Total;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_faktur_cndn
                         SELECT * FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab." t_faktur
                         ON DUPLICATE KEY UPDATE Jumlah= t_faktur.Jumlah, Perhitungan = t_faktur.Perhitungan,
                         DasarPerhitungan = t_faktur.DasarPerhitungan,Persen = t_faktur.Persen,
                         Rupiah = t_faktur.Rupiah,DscCab = t_faktur.DscCab,
                         Banyak = t_faktur.Banyak,ValueDscCab = t_faktur.ValueDscCab,
                         Total = t_faktur.Total,umur_pelunasan = t_faktur.umur_pelunasan;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_faktur_cndn_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                  
                INSERT INTO sst.trs_pelunasan_detail
                        SELECT * FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab." t_lunas
                        ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
                        Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
                        SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,TglGiroCair = t_lunas.TglGiroCair,
                        modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
                        bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
                        materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
                        No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_pelunasan_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                 
                INSERT INTO sst.trs_pelunasan_giro_detail
                            SELECT * FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab." t_lunas
                            ON DUPLICATE KEY UPDATE STATUS = t_lunas.Status,SaldoFaktur = t_lunas.SaldoFaktur,
                            Cicilan = t_lunas.Cicilan,TipePelunasan = t_lunas.TipePelunasan, ValuePelunasan = t_lunas.ValuePelunasan,
                            SaldoAkhir = t_lunas.SaldoAkhir,Giro = t_lunas.Giro,ValueGiro = t_lunas.ValueGiro,TglGiroCair = t_lunas.TglGiroCair,
                            modified_at = t_lunas.modified_at ,modified_by = t_lunas.modified_by,
                            bank = t_lunas.bank,value_pembulatan = t_lunas.value_pembulatan,value_transfer = t_lunas.value_transfer,
                            materai = t_lunas.materai,ValueGiro= t_lunas.ValueGiro,bank= t_lunas.bank,status_titipan= t_lunas.status_titipan,
                            No_Titipan= t_lunas.No_Titipan,NoNTPN= t_lunas.NoNTPN;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_pelunasan_giro_detail_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_giro
                        SELECT * FROM sst_federate_temp.trs_giro_fed_".$kkdcab." t_giro
                        ON DUPLICATE KEY UPDATE StatusGiro = t_giro.StatusGiro,Tolak = t_giro.Tolak,
                        TglCair = t_giro.TglCair ,SisaGiro = t_giro.SisaGiro,modified_at = t_giro.modified_at,modified_by = t_giro.modified_by;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_giro_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"                            
                INSERT INTO sst.trs_buku_transaksi_all
                      SELECT * FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab." t_gl
                      ON DUPLICATE KEY UPDATE Tanggal = t_gl.Tanggal,Transaksi = t_gl.Transaksi,
                      Keterangan = t_gl.Keterangan ,Jumlah = t_gl.Jumlah,Saldo_Awal = t_gl.Saldo_Awal,
                      Debit = t_gl.Debit, Kredit=t_gl.Kredit,Saldo_Akhir=t_gl.Saldo_Akhir,
                      Jurnal_ID=t_gl.Jurnal_ID;
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_buku_transaksi_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invsum_all where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invsum_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_invsum_all SELECT * FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_invsum_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invdet_all where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.trs_invdet_all WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.trs_invdet_all  
                  (Tahun,Cabang,KodePrinsipal,NamaPrinsipal,Pabrik,KodeProduk,NamaProduk,UnitStok,ValueStok,UnitCOGS,BatchNo,ExpDate,NoDokumen,
                    Gudang,TanggalDokumen,SAwa01,VAwa01,SAwa02,VAwa02,SAwa03,VAwa03,SAwa04,VAwa04,SAwa05,VAwa05,SAwa06,VAwa06,SAwa07,VAwa07,
                    SAwa08,VAwa08,SAwa09,VAwa09,SAwa10,VAwa10,SAwa11,VAwa11,SAWa12,VAwa12,Keterangan,
                    LastBuy,LastSales,AddedUser,AddedTime,ModifiedUser,ModifiedTime,nourut)
                SELECT * FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.trs_invdet_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.mst_pelanggan where ifnull(cabang,'')='';
                ");
            mysqli_query($connpst,"
                DELETE FROM sst.mst_pelanggan WHERE Cabang='".$this->cabang."' AND (SELECT COUNT(*) FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.") > 0;
                ");
            mysqli_query($connpst,"
                INSERT INTO sst.mst_pelanggan SELECT * FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";
                ");
            mysqli_query($connpst,"
                DELETE FROM sst_federate_temp.mst_pelanggan_fed_".$kkdcab.";
                ");
        }          
       $this->send_message_tele("FINISH UPDATE DATA TEMP PUSAT KE REAL PUSAT ");

      mysqli_close($connpst);
    

    }
 

    public function getClientIPMe() {
            $ipaddress = '';
            if (getenv('HTTP_CLIENT_IP'))
                $ipaddress = getenv('HTTP_CLIENT_IP');
            else if(getenv('HTTP_X_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            else if(getenv('HTTP_X_FORWARDED'))
                $ipaddress = getenv('HTTP_X_FORWARDED');
            else if(getenv('HTTP_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
            else if(getenv('HTTP_FORWARDED'))
               $ipaddress = getenv('HTTP_FORWARDED');
            else if(getenv('REMOTE_ADDR'))
                $ipaddress = getenv('REMOTE_ADDR');
            else
                $ipaddress = 'UNKNOWN';
            return $ipaddress;
    }

    public function send_message_tele($kirimpesan = null,$idtele = null)
    {
        $getMyIP = $this->getClientIPMe();
        $bDatax = $kirimpesan;
        $tgl = date('ymd');
        $hostname = 'localhost';
        $username = 'sapta';
        $password = 'Sapta254*x';
        $db_name = 'sst';

        $conntel = new mysqli($hostname, $username, $password, $db_name);

        if($conntel->connect_error)
        {
         die('Connection Failed '.$conntel->connect_error);
         $connMes =  ' Connection Failed '.$conntel->connect_error;
        }
        else 
        {
         //echo 'Connected Successfully<br>';
         $connMes =  ' Connected Successfully';
        }

        $sqlmst = "
                  SELECT b.Cabang,b.Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1";

        $resultmst = mysqli_query($conntel, $sqlmst);

        if (mysqli_num_rows($resultmst) > 0) {
            while($rowmst = mysqli_fetch_assoc($resultmst)) {
                $cab = $rowmst["Cabang"];
                $kdcab = $rowmst["Kode"];
            }
        } else {
                $cab = "Gagal";
                $kdcab = "GGLTRK".$kdcab;
        }

        // id telegram
        // nootiflap = -341949406 // -341949406
        // laporanpusat = -1001331931445
        // laporanstok = -1001380319718
        // diskusiIT = -171688895
        // me = 138684434

        // date_default_timezone_set("Asia/Calcutta");
        $pesan = date("Y-m-d H:i:s");
        $botToken="583561269:AAFHWZSvVBGl-DFnhPOmMw6o0dtmaE7Qh04"; //** remeber the token, this is token of th bot

        $str = 'Copyright © TeamIT "SST".';

        // $pesantambahan = htmlspecialchars($str, ENT_QUOTES); // Will only convert double quotes
        // $pesantambahan = htmlspecialchars($str, ENT_COMPAT); // Converts double and single quotes
        $pesantambahan = htmlspecialchars($str, ENT_NOQUOTES); // Does not convert any quotes

        $pesandefault = 
        $bDatax.'
        '.$pesan."
        My IP is ".$getMyIP.$connMes.'
        Cabang '.$cab.' Kode '.$kdcab.'
        '.$pesantambahan
        ;


          $website="https://api.telegram.org/bot".$botToken;

          if (empty($idtele)) {
            $chatId=-341949406;  //** ===>>>NOTE: this chatId MUST be the chat_id of a person/group, NOT another bot chatId !!!**
          }else{
            $chatId=$idtele;  //** ===>>>NOTE: this chatId MUST be the chat_id of a person/group, NOT another bot chatId !!!**
          }

          $params=['chat_id'=>$chatId,'text'=> $pesandefault,];

          $ch = curl_init($website . '/sendMessage');
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          $result = curl_exec($ch);
          curl_close($ch);

        mysqli_close($conntel);

    }


    public function getTelePT($tglDate = NULL)
    {
        $bDate = $tglDate;

        // log_message("error","Create Failed");
        // $this->send_message_tele("error disini cuyy ".substr($bDate,0,4) , 138684434 );
        //Aslinya
        /*if (empty($bDate)) {
          //echo "Tanggal Default";
          $getDateQ = "DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())";
          $tgl = date('ymd');
        }else
        {
          $getDate = "'".substr($bDate,0,4)."-".substr($bDate,4,2)."-".cal_days_in_month(CAL_GREGORIAN, substr($bDate,4,2), substr($bDate,0,4))."'";
          $getDateQ = "DATE_ADD(LAST_DAY(DATE_SUB($getDate, INTERVAL 1 MONTH)), INTERVAL 1 DAY) AND DATE($getDate)";
          $tgl = date("'".substr($bDate,0,4)."-".substr($bDate,4,2)."-".cal_days_in_month(CAL_GREGORIAN, substr($bDate,4,2), substr($bDate,0,4))."'");
        }*/


        if (empty($bDate)) {
          //echo "Tanggal Default";
          $cekDate = date('d');
          if ($cekDate == 1 || $cekDate == 2 || $cekDate == 3 || $cekDate == 4 || $cekDate == 5) {
            $getDateQ = "DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())";
          }else
          {            
            $getDateQ = "DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())";
          }
          
          $tgl = date('ymd');
        }else
        {
          $getDate = "'".substr($bDate,0,4)."-".substr($bDate,5,2)."-".cal_days_in_month(CAL_GREGORIAN, substr($bDate,5,2), substr($bDate,0,4))."'";
          $getDateQ = "DATE_ADD(LAST_DAY(DATE_SUB($getDate, INTERVAL 1 MONTH)), INTERVAL 1 DAY) AND DATE($getDate)";
          $tgl = date("'".substr($bDate,0,4)."-".substr($bDate,5,2)."-".cal_days_in_month(CAL_GREGORIAN, substr($bDate,5,2), substr($bDate,0,4))."'");
        }

        // $this->send_message_tele("error kedua cuyy ".$bDate , 138684434 );
        // $this->send_message_tele("error ketiga cuyy ".$getDateQ , 138684434 );
        // $this->send_message_tele("error keenpat cuyy ".$tgl , 138684434 );

        $hostname = 'localhost';
        $username = 'sapta';
        $password = 'Sapta254*x';
        $db_name = 'sst';

        $conn = new mysqli($hostname, $username, $password, $db_name);

        if($conn->connect_error)
        {
         die('Connection Failed '.$conn->connect_error);
        }
        else 
        {
         //echo 'Connected Successfully<br>';
        }


        $result = array();

        $sqlmst = "
                  SELECT b.Cabang,b.Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1";

        $resultmst = mysqli_query($conn, $sqlmst);

        if (mysqli_num_rows($resultmst) > 0) {
            while($rowmst = mysqli_fetch_assoc($resultmst)) {
                $cab = $rowmst["Cabang"];
                $kdcab = $rowmst["Kode"];
            }
        } else {
                $cab = "Gagal";
                $kdcab = "GGLTRK".$kdcab;
        }


        $sql = "
                SELECT * FROM 
                (SELECT mst_cabang.Region1 AS 'WIL',
                       trs_faktur_detail.Cabang AS 'NM_CABANG',
                       IFNULL(trs_faktur_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                       trs_faktur_detail.NoFaktur AS 'NODOKJDI',
                       IFNULL(trs_faktur_detail.Acu,'') AS 'NO_ACU',
                       trs_faktur_detail.Pelanggan AS 'KODE_PELANGGAN',
                       trs_faktur_detail.kota AS 'KODE_KOTA',
                       trs_faktur_detail.TipePelanggan AS 'KODE_TYPE',
                       '' AS KODE_LANG,
                       trs_faktur_detail.NamaPelanggan AS 'NAMA_LANG',
                       trs_faktur_detail.AlamatFaktur AS 'ALAMAT',
                       trs_faktur_detail.Prinsipal AS 'JUDUL',
                       trs_faktur_detail.KodeProduk AS 'KODEPROD',
                       trs_faktur_detail.NamaProduk AS 'NAMAPROD',
                       CASE trs_faktur_detail.TipeDokumen 
                       WHEN 'Retur'
                              THEN ROUND(trs_faktur_detail.`QtyFaktur`,0) *-1 
                              ELSE ROUND(trs_faktur_detail.`QtyFaktur`,0) 
                       END AS 'UNIT',
                       CASE trs_faktur_detail.TipeDokumen 
                       WHEN 'Retur' 
                              THEN ROUND(trs_faktur_detail.`BonusFaktur`,0) *-1 
                              ELSE ROUND(trs_faktur_detail.`BonusFaktur`,0) 
                       END AS 'PRINS',
                       CASE trs_faktur_detail.TipeDokumen 
                       WHEN 'Retur' 
                              THEN ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))*-1 
                              ELSE ABS((IFNULL(QtyFaktur,0) + IFNULL(BonusFaktur,0)))
                       END AS 'BANYAK',
                       IFNULL(trs_faktur_detail.Harga,0) AS 'HARGA',
                       (IFNULL(trs_faktur_detail.DiscCab,0.0) + IFNULL(trs_faktur_detail.DiscCab_onf,0.0)) AS 'PRSNXTRA',
                       (CASE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                       WHEN 0 THEN IFNULL(trs_faktur_detail.DiscPrinsTot,0.0)
                       ELSE (IFNULL(trs_faktur_detail.DiscPrins1,0.0) + IFNULL(trs_faktur_detail.DiscPrins2,0.0))
                       END) AS 'PRINPXTRA',
                       IFNULL(trs_faktur_detail.Gross,0.0) AS 'TOT1',
                       IFNULL(trs_faktur_detail.Value,0.0) AS 'NILJU',
                       IFNULL(trs_faktur_detail.PPN,0.0) AS PPN,
                       IFNULL(trs_faktur_detail.TotalCOGS,0.0) AS 'COGS',
                       trs_faktur_detail.Salesman AS 'KODESALES',
                       DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
                       IFNULL(DATE(trs_faktur_detail.ExpDate),'') AS 'TGLEXP',
                       IFNULL(trs_faktur_detail.BatchNo,'') AS 'BATCH',
                       trs_faktur_detail.Cabang AS 'Area',
                       trs_faktur_detail.Telp AS TELP,
                       trs_faktur_detail.Rayon AS RAYON,
                       trs_faktur_detail.Tipe_2 AS PANEL,
                       IFNULL(trs_faktur_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                       IFNULL(trs_faktur_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                       trs_faktur_detail.CashDiskon,
                       IFNULL(trs_faktur_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                       trs_faktur_detail.KetDiscCabMax,
                       IFNULL(trs_faktur_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                       trs_faktur_detail.KetDiscPrinsMax,
                       trs_faktur_detail.NoDO,
                       trs_faktur.Status,
                       trs_faktur_detail.TipeDokumen,
                       'F' AS Tipe,
                       trs_faktur_detail.acu2 as ACU2,
                       trs_faktur_detail.NoBPB
                FROM trs_faktur_detail JOIN trs_faktur ON 
                     trs_faktur.NoFaktur = trs_faktur_detail.NoFaktur AND 
                     trs_faktur.Cabang = trs_faktur_detail.Cabang
                     LEFT JOIN (SELECT Cabang,Kode,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                FROM mst_pelanggan) AS mst_pelanggan 
                                ON trs_faktur_detail.Pelanggan=mst_pelanggan.Kode AND 
                                   trs_faktur_detail.Cabang = mst_pelanggan.Cabang
                     LEFT JOIN (SELECT Region1,Cabang 
                        FROM mst_cabang) AS mst_cabang 
                                ON trs_faktur_detail.Cabang=mst_cabang.Cabang
                                WHERE trs_faktur.Status NOT LIKE '%Batal%'
                                AND trs_faktur.TglFaktur BETWEEN $getDateQ
                                                  
                UNION ALL
                SELECT mst_cabang.Region1 AS 'WIL',
                       a.Cabang AS 'NM_CABANG',
                       IFNULL(trs_faktur.CaraBayar,'Kredit') AS 'KODE_JUAL',
                       NoDokumen AS 'NODOKJDI',
                       IFNULL(trs_faktur.Acu,'') AS 'NO_ACU',
                       a.Pelanggan AS 'KODE_PELANGGAN',
                       mst_pelanggan.Kota AS 'KODE_KOTA',
                       mst_pelanggan.Tipe_Pelanggan AS 'KODE_TYPE',
                       '' AS KODE_LANG,
                       mst_pelanggan.Pelanggan AS 'NAMA_LANG',
                       mst_pelanggan.Alamat AS 'ALAMAT',
                       IFNULL(mst_produk.Prinsipal,'') AS 'JUDUL',
                       a.KodeProduk AS 'KODEPROD',
                       mst_produk.Produk AS 'NAMAPROD',
                       CASE trs_faktur.TipeDokumen 
                       WHEN 'CN'
                              THEN ABS(`Banyak`) *-1 
                              ELSE ABS(`Banyak`) 
                       END AS 'UNIT',
                       0 AS 'PRINS',
                       CASE trs_faktur.TipeDokumen 
                       WHEN 'CN' 
                              THEN ABS((IFNULL(Banyak,0) ))*-1 
                              ELSE ABS((IFNULL(Banyak,0) ))
                       END AS 'BANYAK',
                       0 AS 'HARGA',
                       IFNULL(DscCab,0.0) AS 'PRSNXTRA',
                       0 AS 'PRINPXTRA',
                       0 AS 'TOT1',
                       IFNULL(a.Jumlah,0) AS 'NILJU',
                       0 AS PPN,
                       0 AS 'COGS',
                       trs_faktur.Salesman AS 'KODESALES',
                       DATE(TglFaktur) AS 'TGLDOK',
                       '' AS 'TGLEXP',
                       '' AS 'BATCH',
                       a.Cabang AS 'Area',
                       mst_pelanggan.Telp AS TELP,
                       mst_pelanggan.Rayon_1 AS RAYON,
                       mst_pelanggan.Tipe_2 AS PANEL,
                       '' AS DiscPrins1,
                       '' AS DiscPrins2,
                       '' AS CashDiskon,
                       '' AS DiscCabMax,
                       '' AS KetDiscCabMax,
                       '' AS DiscPrinsMax,
                       '' AS KetDiscPrinsMax,
                       trs_faktur.NoDO,
                       trs_faktur.Status,
                       trs_faktur.TipeDokumen,
                       'CD' AS Tipe,
                       '' as ACU2,
                       '' as NoBPB
                FROM trs_faktur_cndn a,mst_produk,trs_faktur
                     LEFT JOIN (SELECT Kode,Kota,Cabang,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                                 FROM mst_pelanggan) AS mst_pelanggan 
                                 ON trs_faktur.Pelanggan=mst_pelanggan.Kode AND 
                                   trs_faktur.Cabang = mst_pelanggan.Cabang
                     LEFT JOIN (SELECT Region1,Cabang 
                        FROM mst_cabang) AS mst_cabang 
                                ON trs_faktur.Cabang=mst_cabang.Cabang
                                WHERE  a.Status IN ('CNOK','DNOK')
                                AND a.NoDokumen=trs_faktur.NoFaktur
                                AND a.Cabang=trs_faktur.Cabang
                                AND a.KodeProduk = mst_produk.Kode_Produk
                                AND TanggalCNDN BETWEEN $getDateQ
                UNION ALL
                SELECT mst_cabang.Region1 AS 'WIL',
                       trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
                       IFNULL(trs_delivery_order_sales_detail.CaraBayar,'Kredit') AS 'KODE_JUAL',
                       trs_delivery_order_sales_detail.NoDO AS 'NODOKJDI',
                       IFNULL(trs_delivery_order_sales_detail.Acu,'') AS 'NO_ACU',
                       trs_delivery_order_sales_detail.Pelanggan AS 'KODE_PELANGGAN',
                       mst_pelanggan.Kota AS 'KODE_KOTA',
                       IFNULL(trs_delivery_order_sales_detail.TipePelanggan,mst_pelanggan.Tipe_Pelanggan) AS 'KODE_TYPE',
                       '' AS KODE_LANG,
                       IFNULL(trs_delivery_order_sales_detail.NamaPelanggan,mst_pelanggan.Pelanggan) AS 'NAMA_LANG',
                       IFNULL(trs_delivery_order_sales_detail.AlamatKirim,mst_pelanggan.Alamat) AS 'ALAMAT',
                       Prinsipal AS 'JUDUL',
                       KodeProduk AS 'KODEPROD',
                       NamaProduk AS 'NAMAPROD',
                       CASE trs_delivery_order_sales_detail.Status 
                       WHEN 'Retur' 
                              THEN ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) *-1 
                              ELSE ROUND(trs_delivery_order_sales_detail.`QtyDO`,0) 
                       END AS 'UNIT',
                       CASE trs_delivery_order_sales_detail.Status 
                       WHEN 'Retur' 
                              THEN ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) *-1 
                              ELSE ROUND(trs_delivery_order_sales_detail.`BonusDO`,0) 
                       END AS 'PRINS',
                       CASE trs_delivery_order_sales_detail.Status 
                       WHEN 'Retur' 
                              THEN ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))*-1 
                              ELSE ABS((IFNULL(QtyDO,0) + IFNULL(BonusDO,0)))
                       END AS 'BANYAK',
                       IFNULL(Harga,0) AS 'HARGA',
                       (IFNULL(DiscCab,0) + IFNULL(DiscCab_onf,0)) AS 'PRSNXTRA',
                       (CASE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                        WHEN 0 THEN IFNULL(DiscPrinsTot,0.0)
                        ELSE (IFNULL(DiscPrins1,0.0) + IFNULL(DiscPrins2,0.0))
                        END) AS 'PRINPXTRA',
                        IFNULL(Gross,0.0) AS 'TOT1',
                        IFNULL(VALUE,0.0) AS 'NILJU',
                        IFNULL(PPN,0.0) AS PPN,
                        IFNULL(TotalCOGS,0.0) AS 'COGS',
                        Salesman AS 'KODESALES',
                        DATE(TglDO) AS 'TGLDOK',
                        IFNULL(DATE(ExpDate),'') AS 'TGLEXP',
                        IFNULL(BatchNo,'') AS 'BATCH',
                        trs_delivery_order_sales_detail.Cabang AS 'Area',
                        mst_pelanggan.Telp AS TELP,
                        mst_pelanggan.Rayon_1 AS RAYON,
                        mst_pelanggan.Tipe_2 AS PANEL,
                        IFNULL(trs_delivery_order_sales_detail.DiscPrins1,0.0) AS 'DiscPrins1',
                        IFNULL(trs_delivery_order_sales_detail.DiscPrins2,0.0) AS 'DiscPrins2',
                        IFNULL(trs_delivery_order_sales_detail.CashDiskon,0.0) AS 'CashDiskon',
                        IFNULL(trs_delivery_order_sales_detail.DiscCabMax,0.0) AS 'DiscCabMax',
                        trs_delivery_order_sales_detail.KetDiscCabMax,
                        IFNULL(trs_delivery_order_sales_detail.DiscPrinsMax,0.0) AS 'DiscPrinsMax',
                        trs_delivery_order_sales_detail.KetDiscPrinsMax,
                        trs_delivery_order_sales_detail.NoDO,
                        trs_delivery_order_sales_detail.Status,
                        'DO' AS 'TipeDokumen',
                        'D' AS Tipe,
                        trs_delivery_order_sales_detail.acu2 as ACU2,
                        trs_delivery_order_sales_detail.NoBPB
                   FROM trs_delivery_order_sales_detail
                    LEFT JOIN (SELECT Kode,Cabang,Kota,Tipe_Pelanggan,Pelanggan,Alamat,Tipe_2,Telp,Rayon_1
                               FROM mst_pelanggan ) AS mst_pelanggan 
                               ON trs_delivery_order_sales_detail.Pelanggan=mst_pelanggan.Kode AND 
                                   trs_delivery_order_sales_detail.Cabang = mst_pelanggan.Cabang
                    LEFT JOIN (SELECT Region1,Cabang 
                        FROM mst_cabang ) AS mst_cabang 
                               ON trs_delivery_order_sales_detail.Cabang=mst_cabang.Cabang
                 WHERE  ((STATUS IN ('Open','Kirim','Terima','Retur')) OR (STATUS ='Closed' AND IFNULL(status_retur,'') ='Y'))
                       AND TglDO BETWEEN $getDateQ
                       ) AS LapPT
                WHERE LapPT.NM_CABANG LIKE '%%'  
                 ORDER BY LapPT.TGLDOK,LapPT.KODE_PELANGGAN,LapPT.NAMA_LANG,LapPT.NODOKJDI
        ";

        //echo $sql;

        $result_sql = mysqli_query($conn, $sql);
        while ($rows = mysqli_fetch_assoc($result_sql))
        {
         $result[] = $rows;
        }

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();


        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Umar Junaedi")
                                     ->setLastModifiedBy("Umar Junaedi")
                                     ->setTitle("LAPORAN PUSAT SST")
                                     ->setSubject("Umar Junaedi")
                                     ->setDescription("LAPORAN PUSAT SST")
                                     ->setKeywords("LAPORAN PUSAT SST")
                                     ->setCategory("LAPORAN PUSAT SST");


        // Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        // Merge Columns for showing 'Student's Data' start---------------
        $objPHPExcel->setActiveSheetIndex(0)
         ->mergeCells('A1:AS1');

        $objPHPExcel->getActiveSheet()
         ->getCell('A1')
         ->setValue("DATA PT");

        $objPHPExcel->getActiveSheet()
         ->getStyle('AS')
         ->getAlignment()
         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1:AS1')
         ->getFill()
         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
         ->getStartColor()
         ->setARGB('FF3399'); //FF3399 33F0FF F28A8C        

        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'WIL');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'NM_CABANG');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', 'KODE_JUAL');
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'NODOKJDI');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', 'NO_ACU');
        $objPHPExcel->getActiveSheet()->setCellValue('F2', 'KODE_PELANGGAN');
        $objPHPExcel->getActiveSheet()->setCellValue('G2', 'KODE_KOTA');
        $objPHPExcel->getActiveSheet()->setCellValue('H2', 'KODE_TYPE');
        $objPHPExcel->getActiveSheet()->setCellValue('I2', 'KODE_LANG');
        $objPHPExcel->getActiveSheet()->setCellValue('J2', 'NAMA_LANG');
        $objPHPExcel->getActiveSheet()->setCellValue('K2', 'ALAMAT');
        $objPHPExcel->getActiveSheet()->setCellValue('L2', 'JUDUL');
        $objPHPExcel->getActiveSheet()->setCellValue('M2', 'KODEPROD');
        $objPHPExcel->getActiveSheet()->setCellValue('N2', 'NAMAPROD');
        $objPHPExcel->getActiveSheet()->setCellValue('O2', 'UNIT');
        $objPHPExcel->getActiveSheet()->setCellValue('P2', 'PRINS');
        $objPHPExcel->getActiveSheet()->setCellValue('Q2', 'BANYAK');
        $objPHPExcel->getActiveSheet()->setCellValue('R2', 'HARGA');
        $objPHPExcel->getActiveSheet()->setCellValue('S2', 'PRSNXTRA');
        $objPHPExcel->getActiveSheet()->setCellValue('T2', 'PRINPXTRA');
        $objPHPExcel->getActiveSheet()->setCellValue('U2', 'TOT1');
        $objPHPExcel->getActiveSheet()->setCellValue('V2', 'NILJU');
        $objPHPExcel->getActiveSheet()->setCellValue('W2', 'PPN');
        $objPHPExcel->getActiveSheet()->setCellValue('X2', 'COGS');
        $objPHPExcel->getActiveSheet()->setCellValue('Y2', 'KODESALES');
        $objPHPExcel->getActiveSheet()->setCellValue('Z2', 'TGLDOK');
        $objPHPExcel->getActiveSheet()->setCellValue('AA2', 'TGLEXP');
        $objPHPExcel->getActiveSheet()->setCellValue('AB2', 'BATCH');
        $objPHPExcel->getActiveSheet()->setCellValue('AC2', 'AREA');
        $objPHPExcel->getActiveSheet()->setCellValue('AD2', 'TELP');
        $objPHPExcel->getActiveSheet()->setCellValue('AE2', 'RAYON');
        $objPHPExcel->getActiveSheet()->setCellValue('AF2', 'PANEL');
        $objPHPExcel->getActiveSheet()->setCellValue('AG2', 'DiscPrins1');
        $objPHPExcel->getActiveSheet()->setCellValue('AH2', 'DiscPrins2');
        $objPHPExcel->getActiveSheet()->setCellValue('AI2', 'CashDiskon');
        $objPHPExcel->getActiveSheet()->setCellValue('AJ2', 'DiscCabMax');
        $objPHPExcel->getActiveSheet()->setCellValue('AK2', 'KetDiscCabMax');
        $objPHPExcel->getActiveSheet()->setCellValue('AL2', 'DiscPrinsMax');
        $objPHPExcel->getActiveSheet()->setCellValue('AM2', 'KetDiscPrinsMax');
        $objPHPExcel->getActiveSheet()->setCellValue('AN2', 'NoDO');
        $objPHPExcel->getActiveSheet()->setCellValue('AO2', 'STATUS');
        $objPHPExcel->getActiveSheet()->setCellValue('AP2', 'TipeDokumen');
        $objPHPExcel->getActiveSheet()->setCellValue('AQ2', 'TIPE');
        $objPHPExcel->getActiveSheet()->setCellValue('AR2', 'ACU2'); 
        $objPHPExcel->getActiveSheet()->setCellValue('AS2', 'NoBPB'); 
        //44

         $styleArray = array(
         'font' => array('bold' => true,'color' => array('rgb' => '3333FF'),'size' => 11,'name' => 'Verdana'),
         'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '33F0FF')) 
         );

        $objPHPExcel->getActiveSheet()->getStyle('A2:AS2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2:AS2')->getAlignment()->setWrapText(false); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AS')->setAutoSize(true);
        //$objPHPExcel->getStyle('A1')->getNumberFormat()->setFormatCode('#,##0.00');
        //$objPHPExcel->getActiveSheet()->setCellValueExplicit('A1', $val,PHPExcel_Cell_DataType::TYPE_STRING);



        $rowCount = 3;
        foreach($result as $key => $values) 
        {
         //start of printing column names as names of MySQL fields 
         $column = 'A';
         foreach($values as $value) 
         {
         // echo $value.'<br>';
         // echo $column.$rowCount.'<br>';
          $namakolomInt = array("R","S","T","U","V","W","X","AG","AH","AI","AJ","AL");

           if (in_array($column, $namakolomInt)) {
             $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
             $objPHPExcel->getActiveSheet()->getStyle($column.$rowCount)->getNumberFormat()->setFormatCode('#,##0.00');
           }else
           {
             $objPHPExcel->getActiveSheet()->setCellValueExplicit($column.$rowCount, $value,PHPExcel_Cell_DataType::TYPE_STRING);
            // $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
           }

             // $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);


           $column++; 
         } 
         $rowCount++;
        }


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');


        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        // id telegram
        // nootiflap = -341949406 // -341949406
        // laporanpusat = -1001331931445
        // laporanstok = -1001380319718
        // diskusiIT = -171688895

        $telegramID = -1001331931445;
        $telegramFile = '/var/www/html/sstcabang/assets/dokumen/excel/'.$tgl.'_PT_'.$kdcab.'_bysystem.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // $objWriter->save('php://output');
        $objWriter->save(str_replace(__FILE__,$telegramFile,__FILE__));


        $zip = new ZipArchive;
        if ($zip->open('test_new.zip', ZipArchive::CREATE) === TRUE)
        {
            // Add files to the zip file
            $zip->addFile('test.txt');
            $zip->addFile('test.pdf');
         
            // Add random.txt file to zip and rename it to newfile.txt
            $zip->addFile('random.txt', 'newfile.txt');
         
            // Add a file new.txt file to zip using the text specified
            $zip->addFromString('new.txt', 'text to be added to the new.txt file');
         
            // All files are added, so close the zip file.
            $zip->close();
        }


        // definisi ID Telegram    
        $bot_url3    = "https://api.telegram.org/bot583561269:AAFHWZSvVBGl-DFnhPOmMw6o0dtmaE7Qh04/sendDocument?chat_id=".$telegramID ;
        $post_fields7 = array('chat_id' => $telegramID, 'document' => new CURLFile(realpath($telegramFile)));
        $ch7 = curl_init(); 
        curl_setopt($ch7, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch7, CURLOPT_URL, $bot_url3); 
        curl_setopt($ch7, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch7, CURLOPT_POSTFIELDS, $post_fields7); 
        $output7 = curl_exec($ch7);
            
        $conn->close(); 
        // exit;
    }

    public function getTelePU()
    {
        $hostname = 'localhost';
        $username = 'sapta';
        $password = 'Sapta254*x';
        $db_name = 'sst';

        $conn = new mysqli($hostname, $username, $password, $db_name);

        if($conn->connect_error)
        {
         die('Connection Failed '.$conn->connect_error);
        }
        else 
        {
         //echo 'Connected Successfully<br>';
        }

        $result = array();

        $sqlmst = "
                  SELECT b.Cabang,b.Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1";

        $resultmst = mysqli_query($conn, $sqlmst);

        if (mysqli_num_rows($resultmst) > 0) {
            while($rowmst = mysqli_fetch_assoc($resultmst)) {
                $cab = $rowmst["Cabang"];
                $kdcab = $rowmst["Kode"];
            }
        } else {
                $cab = "Gagal";
                $kdcab = "GGLTRK".$kdcab;
        }


        $sql = "
                SELECT * FROM (
                    SELECT
                          '' AS REGION,
                          Cabang AS CABANG,
                          Cabang AS 'AREA',
                          Pelanggan AS 'INDEX',
                          CONCAT(Pelanggan,NoFaktur,TglFaktur) AS COMBO,
                          TipeDokumen AS KDDOKJDI,
                          NoFaktur AS NODOKJDI,
                          TglFaktur AS TGLFAK,
                          TOP,
                          NODO,
                          nodih,
                          tgldih,
                          StatusInkaso,
                          CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END AS UMUR,
                          CASE 
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            BETWEEN 0 AND 30 THEN '0-30'
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            BETWEEN 31 AND 45 THEN '31-45'
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            BETWEEN 46 AND 60 THEN '46-60'
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            BETWEEN 61 AND 90 THEN '61-90'
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            BETWEEN 91 AND 150 THEN '91-150'
                            WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                            > 150 THEN '>150'
                            ELSE DATEDIFF(DATE(NOW()),TglFaktur)
                          END AS KATEGORI,
                          CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN
                          CASE 
                            WHEN Salesman='RG2' THEN 'RAGU-RAGU' 
                                WHEN CaraBayar LIKE '%RPO%' THEN 
                                    CASE 
                                        WHEN (CaraBayar = 'RPO' OR CaraBayar LIKE '%60%') THEN 
                                            CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                        WHEN CaraBayar LIKE '%75%' THEN 
                                            CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                        WHEN CaraBayar LIKE '%90%' THEN 
                                        CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                        WHEN CaraBayar LIKE '%120%' THEN 
                                        CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                        WHEN CaraBayar LIKE '%150%' THEN 
                                        CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                        WHEN CaraBayar LIKE '%180%' THEN 
                                        CASE WHEN 
                                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                    ELSE 'CEK CARA BAYAR' 
                                    END
                                WHEN CaraBayar LIKE '%Cash%' THEN 
                                CASE WHEN 
                                        (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                        >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                WHEN CaraBayar LIKE '%Kredit%' THEN 
                                CASE WHEN 
                                        (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                        >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                            ELSE IF( (IFNULL(saldo,0) + IFNULL(saldo_giro,0))= 0 , 'LUNAS' , UCASE(TipeDokumen)) 
                          END 
                          ELSE 'LUNAS' END  
                          AS KATEGORI2,
                          Salesman AS KODESALES,
                          NamaPelanggan AS NAMA_LANG,
                          AlamatFaktur AS ALAMAT_LANG,
                          Total AS NILDOK,
                          (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) AS SALDO,
                          CASE 
                            WHEN TipePelanggan IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                            WHEN TipePelanggan IN ('DK','DPKM') THEN 'DINKES'
                            WHEN TipePelanggan IN ('IN') THEN 'INSTANSI'
                            WHEN TipePelanggan IN ('RSSA') THEN 'RS SWASTA'
                            WHEN TipePelanggan IN ('RSUD') THEN 'RSUD'
                            ELSE 'CEK TIPE PELANGGAN'
                            END AS 'KATEGORI3'
                            ,
                          '' AS JUDUL,
                          '' AS SPESIAL,
                          Status AS 'STATUS',  
                          IFNULL(CaraBayar,'') AS CARA_BAYAR,
                           CASE WHEN Saldo !=0 
                            THEN
                                CASE WHEN
                                    (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                    > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                            ELSE 'LUNAS'
                           END AS KETERANGAN_JATUH_TEMPO,
                           IFNULL(trs_faktur.acu2,'') AS ACU2,
                           alasan_jto AS ALASAN
                    FROM trs_faktur 
                    WHERE IFNULL(STATUS,'') IN ('Giro','Open','OpenDIH')  )a   
                    ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC 
        ";
        $result_sql = mysqli_query($conn, $sql);
        while ($rows = mysqli_fetch_assoc($result_sql))
        {
         $result[] = $rows;
        }

        // echo "<pre>";
        // print_r($result);

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();


        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Umar Junaedi")
                                     ->setLastModifiedBy("Umar Junaedi")
                                     ->setTitle("LAPORAN PUSAT SST")
                                     ->setSubject("Umar Junaedi")
                                     ->setDescription("LAPORAN PUSAT SST")
                                     ->setKeywords("LAPORAN PUSAT SST")
                                     ->setCategory("LAPORAN PUSAT SST");


        // Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        // Merge Columns for showing 'Student's Data' start---------------
        $objPHPExcel->setActiveSheetIndex(0)
         ->mergeCells('A1:AB1');

        $objPHPExcel->getActiveSheet()
         ->getCell('A1')
         ->setValue("DATA PU");

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1')
         ->getAlignment()
         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1:AB1')
         ->getFill()
         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
         ->getStartColor()
         ->setARGB('FF3399'); //FF3399 33F0FF F28A8C
         
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'REGION'); 
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'CABANG'); 
        $objPHPExcel->getActiveSheet()->setCellValue('C2', 'AREA'); 
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'INDEX'); 
        $objPHPExcel->getActiveSheet()->setCellValue('E2', 'COMBO'); 
        $objPHPExcel->getActiveSheet()->setCellValue('F2', 'KDDOKJDI'); 
        $objPHPExcel->getActiveSheet()->setCellValue('G2', 'NODOKJDI'); 
        $objPHPExcel->getActiveSheet()->setCellValue('H2', 'TGLFAK'); 
        $objPHPExcel->getActiveSheet()->setCellValue('I2', 'TOP'); 
        $objPHPExcel->getActiveSheet()->setCellValue('J2', 'NODO'); 
        $objPHPExcel->getActiveSheet()->setCellValue('K2', 'NODIH'); 
        $objPHPExcel->getActiveSheet()->setCellValue('L2', 'TGLDIH'); 
        $objPHPExcel->getActiveSheet()->setCellValue('M2', 'STATUSINKASO'); 
        $objPHPExcel->getActiveSheet()->setCellValue('N2', 'UMUR'); 
        $objPHPExcel->getActiveSheet()->setCellValue('O2', 'KATEGORI'); 
        $objPHPExcel->getActiveSheet()->setCellValue('P2', 'KATEGORI2'); 
        $objPHPExcel->getActiveSheet()->setCellValue('Q2', 'KODESALES'); 
        $objPHPExcel->getActiveSheet()->setCellValue('R2', 'NAMA_LANG'); 
        $objPHPExcel->getActiveSheet()->setCellValue('S2', 'ALAMAT_LANG'); 
        $objPHPExcel->getActiveSheet()->setCellValue('T2', 'NILDOK'); 
        $objPHPExcel->getActiveSheet()->setCellValue('U2', 'SALDO'); 
        $objPHPExcel->getActiveSheet()->setCellValue('V2', 'KATEGORI3'); 
        $objPHPExcel->getActiveSheet()->setCellValue('W2', 'JUDUL'); 
        $objPHPExcel->getActiveSheet()->setCellValue('X2', 'SPESIAL'); 
        $objPHPExcel->getActiveSheet()->setCellValue('Y2', 'STATUS'); 
        $objPHPExcel->getActiveSheet()->setCellValue('Z2', 'CARA_BAYAR'); 
        $objPHPExcel->getActiveSheet()->setCellValue('AA2', 'KETERANGAN_JATUH_TEMPO'); 
        $objPHPExcel->getActiveSheet()->setCellValue('AB2', 'ACU2'); 
        $objPHPExcel->getActiveSheet()->setCellValue('AB2', 'ALASAN'); 

         $styleArray = array(
         'font' => array('bold' => true,'color' => array('rgb' => '3333FF'),'size' => 11,'name' => 'Verdana'),
         'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '33F0FF')) 
         );

        $objPHPExcel->getActiveSheet()->getStyle('A2:AB2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2:AB2')->getAlignment()->setWrapText(false); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
        //$objPHPExcel->getStyle('A1')->getNumberFormat()->setFormatCode('#,##0.00');
        //$objPHPExcel->getActiveSheet()->setCellValueExplicit('A1', $val,PHPExcel_Cell_DataType::TYPE_STRING);

        $rowCount = 3;
        foreach($result as $key => $values) 
        {
         //start of printing column names as names of MySQL fields 
         $column = 'A';
         foreach($values as $value) 
         {
         // echo $value.'<br>';
         // echo $column.$rowCount.'<br>';
          $namakolomInt = array("I", "N", "T", "U");

           if (in_array($column, $namakolomInt)) {
             $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
             $objPHPExcel->getActiveSheet()->getStyle($column.$rowCount)->getNumberFormat()->setFormatCode('#,##0.00');
           }else
           {
             $objPHPExcel->getActiveSheet()->setCellValueExplicit($column.$rowCount, $value,PHPExcel_Cell_DataType::TYPE_STRING);
            // $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
           }

           $column++; 
         } 
         $rowCount++;
        }



        $tgl = date('ymd');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');


        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        // id telegram
        // nootiflap = -341949406 // -341949406
        // laporanpusat = -1001331931445
        // laporanstok = -1001380319718
        // diskusiIT = -171688895

        $telegramID = -1001331931445;
        $telegramFile = '/var/www/html/sstcabang/assets/dokumen/excel/'.$tgl.'_PU_'.$kdcab.'_bysystem.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // $objWriter->save('php://output');
        $objWriter->save(str_replace(__FILE__,$telegramFile,__FILE__));

        // definisi ID Telegram    
        $bot_url3    = "https://api.telegram.org/bot583561269:AAFHWZSvVBGl-DFnhPOmMw6o0dtmaE7Qh04/sendDocument?chat_id=".$telegramID ;
        $post_fields7 = array('chat_id' => $telegramID,'document' => new CURLFile(realpath($telegramFile)));

        $ch7 = curl_init(); 
        curl_setopt($ch7, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch7, CURLOPT_URL, $bot_url3); 
        curl_setopt($ch7, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch7, CURLOPT_POSTFIELDS, $post_fields7); 
        $output7 = curl_exec($ch7);
            
        $conn->close(); 
        // exit;    

    }

    public function getTeleStok()
    {
        $hostname = 'localhost';
        $username = 'sapta';
        $password = 'Sapta254*x';
        $db_name = 'sst';

        $conn = new mysqli($hostname, $username, $password, $db_name);

        if($conn->connect_error)
        {
         die('Connection Failed '.$conn->connect_error);
        }
        else 
        {
         //echo 'Connected Successfully<br>';
        }

        $result = array();

        $sqlmst = "
                  SELECT b.Cabang,b.Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1";

        $resultmst = mysqli_query($conn, $sqlmst);

        if (mysqli_num_rows($resultmst) > 0) {
            while($rowmst = mysqli_fetch_assoc($resultmst)) {
                $cab = $rowmst["Cabang"];
                $kdcab = $rowmst["Kode"];
            }
        } else {
                $cab = "Gagal";
                $kdcab = "GGLTRK".$kdcab;
        }


        $sql = "
                SELECT DISTINCT trs_invsum.`Tahun`,
                   trs_invsum.`Cabang`,
                   mst_produk.`Prinsipal`,
                   mst_produk.`Pabrik`,
                   trs_invsum.`KodeProduk`,
                   trs_invsum.`NamaProduk`,
                   IFNULL(trs_invsum.`UnitStok`,0) AS 'UnitStok',
                   IFNULL(trs_invsum.`ValueStok`,0) AS 'ValueStok',
                   trs_invsum.`Gudang`,
                   ROUND(IFNULL(trs_invsum.`Indeks`,''),2) AS 'Indeks',
                   trs_invsum.`UnitCOGS`,
                   trs_invsum.`HNA`
                FROM sst.trs_invsum, sst.mst_produk 
                WHERE tahun = '".date('Y')."' AND trs_invsum.`KodeProduk`= mst_produk.`Kode_Produk`
                AND IFNULL(trs_invsum.`UnitStok`,0) !=0
                ORDER BY mst_produk.`Prinsipal` ASC, trs_invsum.`NamaProduk` ASC, trs_invsum.Gudang ASC
        ";
        $result_sql = mysqli_query($conn, $sql);
        while ($rows = mysqli_fetch_assoc($result_sql))
        {
         $result[] = $rows;
        }

        // echo "<pre>";
        // print_r($result);

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();


        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Umar Junaedi")
                                     ->setLastModifiedBy("Umar Junaedi")
                                     ->setTitle("LAPORAN PUSAT SST")
                                     ->setSubject("Umar Junaedi")
                                     ->setDescription("LAPORAN PUSAT SST")
                                     ->setKeywords("LAPORAN PUSAT SST")
                                     ->setCategory("LAPORAN PUSAT SST");


        // Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        // Merge Columns for showing 'Student's Data' start---------------
        $objPHPExcel->setActiveSheetIndex(0)
         ->mergeCells('A1:L1');

        $objPHPExcel->getActiveSheet()
         ->getCell('A1')
         ->setValue("Data Stok");

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1')
         ->getAlignment()
         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1:L1')
         ->getFill()
         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
         ->getStartColor()
         ->setARGB('FF3399'); //FF3399 33F0FF F28A8C
         

        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'TAHUN'); 
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'CABANG'); 
        $objPHPExcel->getActiveSheet()->setCellValue('C2', 'PRINSIPAL'); 
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'PABRIK'); 
        $objPHPExcel->getActiveSheet()->setCellValue('E2', 'KODE PRODUK'); 
        $objPHPExcel->getActiveSheet()->setCellValue('F2', 'NAMA PRODUK'); 
        $objPHPExcel->getActiveSheet()->setCellValue('G2', 'UNIT'); 
        $objPHPExcel->getActiveSheet()->setCellValue('H2', 'VALUE'); 
        $objPHPExcel->getActiveSheet()->setCellValue('I2', 'GUDANG'); 
        $objPHPExcel->getActiveSheet()->setCellValue('J2', 'INDEKS'); 
        $objPHPExcel->getActiveSheet()->setCellValue('K2', 'UNIT COGS'); 
        $objPHPExcel->getActiveSheet()->setCellValue('L2', 'TOTAL HNA'); 


         $styleArray = array(
         'font' => array('bold' => true,'color' => array('rgb' => '3333FF'),'size' => 11,'name' => 'Verdana'),
         'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '33F0FF')) 
         );

        $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getAlignment()->setWrapText(false); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);



        $rowCount = 3;
        foreach($result as $key => $values) 
        {
         //start of printing column names as names of MySQL fields 
         $column = 'A';
         foreach($values as $value) 
         {
         // echo $value.'<br>';
         // echo $column.$rowCount.'<br>';
         
         $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
         $column++; 
         } 
         $rowCount++;
        }



        $tgl = date('ymd');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');


        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        // id telegram
        // nootiflap = -341949406 // -341949406
        // laporanpusat = -1001331931445
        // laporanstok = -1001380319718
        // diskusiIT = -171688895

        $telegramID = -1001380319718;
        $telegramFile = '/var/www/html/sstcabang/assets/dokumen/excel/'.$tgl.'_STOK_'.$kdcab.'_bysystem.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // $objWriter->save('php://output');
        $objWriter->save(str_replace(__FILE__,$telegramFile,__FILE__));

        // definisi ID Telegram    
        $bot_url3    = "https://api.telegram.org/bot583561269:AAFHWZSvVBGl-DFnhPOmMw6o0dtmaE7Qh04/sendDocument?chat_id=".$telegramID ;
        $post_fields7 = array('chat_id' => $telegramID,'document' => new CURLFile(realpath($telegramFile)));

        $ch7 = curl_init(); 
        curl_setopt($ch7, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch7, CURLOPT_URL, $bot_url3); 
        curl_setopt($ch7, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch7, CURLOPT_POSTFIELDS, $post_fields7); 
        $output7 = curl_exec($ch7);
            
        $conn->close(); 
        // exit;
    }

    Public function getTeleKasBank()
    {

        $hostname = 'localhost';
        $username = 'sapta';
        $password = 'Sapta254*x';
        $db_name = 'sst';

        $conn = new mysqli($hostname, $username, $password, $db_name);

        if($conn->connect_error)
        {
         die('Connection Failed '.$conn->connect_error);
        }
        else 
        {
         //echo 'Connected Successfully<br>';
        }

        $result = array();

        $sqlmst = "
                  SELECT b.Cabang,b.Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1";

        $resultmst = mysqli_query($conn, $sqlmst);

        if (mysqli_num_rows($resultmst) > 0) {
            while($rowmst = mysqli_fetch_assoc($resultmst)) {
                $cab = $rowmst["Cabang"];
                $kdcab = $rowmst["Kode"];
            }
        } else {
                $cab = "Gagal";
                $kdcab = "GGLTRK".$kdcab;
        }



        $sql = "
        SELECT 
            Cabang,
            No_Voucher,
            Buku,
            CASE WHEN BUKU = 'Bank'  THEN bank_trans ELSE Tipe_Kas END AS tipebuku,
            DATE(Tanggal) AS Tanggal,
            jurnal_ID,
            kategori_2,
            transaksi,
            DR,
            CR,
            jenis_trans,
            Jumlah,
            Saldo_Awal,
            Debit,
            Kredit,
            Saldo_Akhir,
            IFNULL(NoGiro,'') AS NoGiro,
            Keterangan AS ket,
            IFNULL(Ket_2,'') AS ket1,
            CASE WHEN Ket_2 = 'Bank'
              THEN IFNULL(Bank_Ref,'')
              ELSE IFNULL(CONCAT(Kode_Karyawan,' - ',Karyawan,' - ',Bank_Ref),'') 
            END AS ket2
        FROM trs_buku_transaksi 
        WHERE IFNULL(Kategori_2,'') <> ''  
        AND Tanggal BETWEEN DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())
        ORDER BY Cabang,Kategori,tipebuku,Tipe_Kas,Tanggal,Jurnal_ID 
          ";



        $result_sql = mysqli_query($conn, $sql);
        while ($rows = mysqli_fetch_assoc($result_sql))
        {
         $result[] = $rows;
        }

        // echo "<pre>";
        // print_r($result);

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();


        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Umar Junaedi")
                                     ->setLastModifiedBy("Umar Junaedi")
                                     ->setTitle("LAPORAN PUSAT SST")
                                     ->setSubject("Umar Junaedi")
                                     ->setDescription("LAPORAN PUSAT SST")
                                     ->setKeywords("LAPORAN PUSAT SST")
                                     ->setCategory("LAPORAN PUSAT SST");

        // Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        // Merge Columns for showing 'Student's Data' start---------------
        $objPHPExcel->setActiveSheetIndex(0)
         ->mergeCells('A1:T1');

        $objPHPExcel->getActiveSheet()
         ->getCell('A1')
         ->setValue("DATA KAS");

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1')
         ->getAlignment()
         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()
         ->getStyle('A1:T1')
         ->getFill()
         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
         ->getStartColor()
         ->setARGB('FF3399'); //FF3399 33F0FF F28A8C
        
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Cabang');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Voucher');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Buku');
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'Tipe Buku');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', 'Tanggal');
        $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ID');
        $objPHPExcel->getActiveSheet()->setCellValue('G2', 'Kategori');
        $objPHPExcel->getActiveSheet()->setCellValue('H2', 'Transaksi');
        $objPHPExcel->getActiveSheet()->setCellValue('I2', 'DR');
        $objPHPExcel->getActiveSheet()->setCellValue('J2', 'CR');
        $objPHPExcel->getActiveSheet()->setCellValue('K2', 'Jenis Trans');
        $objPHPExcel->getActiveSheet()->setCellValue('L2', 'Jumlah');
        $objPHPExcel->getActiveSheet()->setCellValue('M2', 'Saldo Awal');
        $objPHPExcel->getActiveSheet()->setCellValue('N2', 'Debit');
        $objPHPExcel->getActiveSheet()->setCellValue('O2', 'Kredit');
        $objPHPExcel->getActiveSheet()->setCellValue('P2', 'Saldo Akhir');
        $objPHPExcel->getActiveSheet()->setCellValue('Q2', 'No Giro');
        $objPHPExcel->getActiveSheet()->setCellValue('R2', 'Keterangan');
        $objPHPExcel->getActiveSheet()->setCellValue('S2', 'Keterangan 1');
        $objPHPExcel->getActiveSheet()->setCellValue('T2', 'Keterangan 2');


         $styleArray = array(
         'font' => array('bold' => true,'color' => array('rgb' => '3333FF'),'size' => 11,'name' => 'Verdana'),
         'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '33F0FF')) 
         );

        $objPHPExcel->getActiveSheet()->getStyle('A2:T2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2:T2')->getAlignment()->setWrapText(false); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        //$objPHPExcel->getStyle('A1')->getNumberFormat()->setFormatCode('#,##0.00');
        //$objPHPExcel->getActiveSheet()->setCellValueExplicit('A1', $val,PHPExcel_Cell_DataType::TYPE_STRING);


        $rowCount = 3;
        foreach($result as $key => $values) 
        {
         //start of printing column names as names of MySQL fields 
         $column = 'A';
         foreach($values as $value) 
         {
         // echo $value.'<br>';
         // echo $column.$rowCount.'<br>';
          $namakolomInt = array("L", "M", "N", "O", "P");

           if (in_array($column, $namakolomInt)) {
             $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
             $objPHPExcel->getActiveSheet()->getStyle($column.$rowCount)->getNumberFormat()->setFormatCode('#,##0.00');
           }else
           {
             $objPHPExcel->getActiveSheet()->setCellValueExplicit($column.$rowCount, $value,PHPExcel_Cell_DataType::TYPE_STRING);
            // $objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $value);
           }

           $column++; 
         } 
         $rowCount++;
        }


        $tgl = date('ymd');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');


        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        // id telegram
        // nootiflap = -341949406 // -341949406
        // laporanpusat = -1001331931445
        // laporanstok = -1001380319718
        // diskusiIT = -171688895
        // diskusiKASBANK = -329069905

        $idTele = -329069905;
        $telegramFile = '/var/www/html/sstcabang/assets/dokumen/excel/'.$tgl.'_KAS_BANK_'.$kdcab.'_bysystem.xlsx';


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // $objWriter->save('php://output');
        $objWriter->save(str_replace(__FILE__,$telegramFile,__FILE__));


        // definisi ID Telegram    
        $bot_url3    = "https://api.telegram.org/bot583561269:AAFHWZSvVBGl-DFnhPOmMw6o0dtmaE7Qh04/sendDocument?chat_id=".$idTele ;


        $post_fields7 = array('chat_id' => $idTele, 'document' => new CURLFile(realpath($telegramFile)));

        $ch7 = curl_init(); 
        curl_setopt($ch7, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));

        curl_setopt($ch7, CURLOPT_URL, $bot_url3); 
        curl_setopt($ch7, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch7, CURLOPT_POSTFIELDS, $post_fields7); 
        $output7 = curl_exec($ch7);
        $conn->close();   
    }

    public function getselisihdata($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      $this->Model_main->getcogssummary();
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $doheader = $this->db->query("SELECT a.`Cabang`,
                             a.NoDO,SUM(a.`Total`) AS 'Total',
                             b.`NoFaktur`,
                             IFNULL(b.`Total`,0) AS 'TotalB'
                       FROM `trs_delivery_order_sales` a
                       LEFT JOIN
                       (
                         SELECT a.`Cabang`,
                                a.`NoFaktur`,
                                SUM(a.`Total`) AS 'Total'
                          FROM `trs_faktur` a
                         WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND 
                               a.`TipeDokumen` IN ('Faktur')
                          GROUP BY a.`Cabang`,
                                a.`NoFaktur`)b 
                      ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                      WHERE a.TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND 
                          IFNULL(a.status_retur,'N')='N' AND 
                           a.`Status` ='Closed' 
                      GROUP BY a.`Cabang`,a.NoDO
                      HAVING Total != TotalB;")->num_rows();
      $dodetail = $this->db->query("SELECT a.`Cabang`,
                             a.NoDO,SUM(a.`Total`) AS 'Total',
                             b.`NoFaktur`,
                             IFNULL(b.`Total`,0) AS 'TotalB'
                       FROM `trs_delivery_order_sales_detail` a
                       LEFT JOIN
                       (
                         SELECT a.`Cabang`,
                                a.`NoFaktur`,
                                SUM(a.`Total`) AS 'Total'
                          FROM `trs_faktur_detail` a
                         WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND 
                               a.`TipeDokumen` IN ('Faktur')
                          GROUP BY a.`Cabang`,
                                a.`NoFaktur`)b 
                      ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                      WHERE a.TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND 
                          IFNULL(a.status_retur,'N')='N' AND 
                           a.`Status` ='Closed' 
                      GROUP BY a.`Cabang`,a.NoDO
                      HAVING Total != TotalB;")->num_rows();
      $cndn = $this->db->query("SELECT a.`Cabang`,
                             a.NoFaktur,
                             a.`Total`,
                             dd.`Total`
                       FROM `trs_faktur` a
                       LEFT JOIN
                       (
                          SELECT a.`Cabang`,a.NoDokumen,
                                 SUM(a.`Jumlah`) AS Total
                          FROM `trs_faktur_cndn` a
                          WHERE a.TanggalCNDN BETWEEN '".$first_date."' AND '".$end_date."'
                          GROUP BY a.`Cabang`,a.`NoDokumen`) dd 
                       ON a.`Cabang`=dd.`Cabang` AND 
                          a.`NoFaktur`= dd.`NoDokumen`
                      WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."'  AND 
                            a.`TipeDokumen` IN ('CN','DN') AND 
                           (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;")->num_rows();
      $dokirim = $this->db->query("SELECT Cabang,NoDO,
                                    TglDO,'header' AS 'status' 
                                    FROM trs_delivery_order_sales
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                          IFNULL(status_retur,'N')='N' AND  
                                          nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim') AND 
                                          tgldo BETWEEN '".$first_date."' AND '".$end_date."' 
                                    UNION ALL
                                    SELECT DISTINCT Cabang,NoDO,TglDO,'detail' AS 'status' 
                                    FROM trs_delivery_order_sales_detail
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                      IFNULL(status_retur,'N')='N' AND  
                                      nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim') AND 
                                      tgldo BETWEEN '".$first_date."' AND '".$end_date."' ;")->num_rows();

      $vallunas = $this->db->query("SELECT a.NoFaktur,
                                    a.Value AS  valuefaktur,a.TglFaktur,a.TipeDokumen ,
                              a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,
                              (CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                              WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                            ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                            FROM trs_faktur a
                            LEFT JOIN (
                              SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                                SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                                CASE 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  ELSE (IFNULL(ValuePelunasan,0) )
                                  END AS ValuePelunasanx
                                 
                                FROM trs_pelunasan_detail 
                                   WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                UNION ALL
                                SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                                CASE 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  ELSE (IFNULL(ValuePelunasan,0) )
                                  END AS ValuePelunasanx
                                FROM trs_pelunasan_giro_detail 
                                   WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                )xdat GROUP BY NomorFaktur
                            )pel ON a.NoFaktur=pel.NomorFaktur
                             WHERE a.NoFaktur IN (
                              SELECT DISTINCT NomorFaktur FROM(
                              SELECT NomorFaktur
                              FROM trs_pelunasan_detail 
                                 WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                 AND TglPelunasan between '".$tgl1."' and '".$tgl2."'
                              UNION ALL
                              SELECT NomorFaktur
                              FROM trs_pelunasan_giro_detail 
                                 WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                 AND TglPelunasan between '".$tgl1."' and '".$tgl2."'
                                 )xx
                            ) 
                            HAVING selisih != 0;")->num_rows();
      $arr_query=[
        "doheader" =>$doheader,
        "dodetail" =>$dodetail,
        "cndn" =>$cndn,
        "dokirim" =>$dokirim,
        "vallunas" =>$vallunas,
      ];
      return $arr_query;

    }

    public function getselisihdoheader($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      // $this->Model_main->getcogssummary();
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $arr_query = $this->db->query("SELECT a.`Cabang`,
                             a.NoDO,SUM(a.`Total`) AS 'Total',
                             b.`NoFaktur`,
                             IFNULL(b.`Total`,0) AS 'TotalB'
                       FROM `trs_delivery_order_sales` a
                       LEFT JOIN
                       (
                         SELECT a.`Cabang`,
                                a.`NoFaktur`,
                                SUM(a.`Total`) AS 'Total'
                          FROM `trs_faktur` a
                         WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND 
                               a.`TipeDokumen` IN ('Faktur')
                          GROUP BY a.`Cabang`,
                                a.`NoFaktur`)b 
                      ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                      WHERE a.TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND 
                          IFNULL(a.status_retur,'N')='N' AND 
                           a.`Status` ='Closed' 
                      GROUP BY a.`Cabang`,a.NoDO
                      HAVING Total != TotalB;")->num_rows();
      // $arr_query=[
      //   "doheader" =>$doheader,
      //   "dodetail" =>$dodetail,
      //   "cndn" =>$cndn,
      //   "dokirim" =>$dokirim,
      //   "vallunas" =>$vallunas,
      // ];
      return $arr_query;

    }
    public function getselisihdodetail($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      // $this->Model_main->getcogssummary();
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      
      $arr_query = $this->db->query("SELECT a.`Cabang`,
                             a.NoDO,SUM(a.`Total`) AS 'Total',
                             b.`NoFaktur`,
                             IFNULL(b.`Total`,0) AS 'TotalB'
                       FROM `trs_delivery_order_sales_detail` a
                       LEFT JOIN
                       (
                         SELECT a.`Cabang`,
                                a.`NoFaktur`,
                                SUM(a.`Total`) AS 'Total'
                          FROM `trs_faktur_detail` a
                         WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."' AND 
                               a.`TipeDokumen` IN ('Faktur')
                          GROUP BY a.`Cabang`,
                                a.`NoFaktur`)b 
                      ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                      WHERE a.TglDO BETWEEN '".$first_date."' AND '".$end_date."' AND 
                          IFNULL(a.status_retur,'N')='N' AND 
                           a.`Status` ='Closed' 
                      GROUP BY a.`Cabang`,a.NoDO
                      HAVING Total != TotalB;")->num_rows();
      
      // $arr_query=[
      //   "doheader" =>$doheader,
      //   "dodetail" =>$dodetail,
      //   "cndn" =>$cndn,
      //   "dokirim" =>$dokirim,
      //   "vallunas" =>$vallunas,
      // ];
      return $arr_query;

    }
    public function getselisihdokirim($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      // $this->Model_main->getcogssummary();
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $arr_query = $this->db->query("SELECT Cabang,NoDO,
                                    TglDO,'header' AS 'status' 
                                    FROM trs_delivery_order_sales
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                          IFNULL(status_retur,'N')='N' AND  
                                          nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim' and TglTerima between '".$first_date."' AND '".$end_date."') AND 
                                          tgldo BETWEEN '".$first_date."' AND '".$end_date."' 
                                    UNION ALL
                                    SELECT DISTINCT Cabang,NoDO,TglDO,'detail' AS 'status' 
                                    FROM trs_delivery_order_sales_detail
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                      IFNULL(status_retur,'N')='N' AND  
                                      nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim' and TglTerima between '".$first_date."' AND '".$end_date."') AND 
                                      tgldo BETWEEN '".$first_date."' AND '".$end_date."' ;")->num_rows();

      
      // $arr_query=[
      //   "doheader" =>$doheader,
      //   "dodetail" =>$dodetail,
      //   "cndn" =>$cndn,
      //   "dokirim" =>$dokirim,
      //   "vallunas" =>$vallunas,
      // ];
      return $arr_query;

    }
    public function getselisihcndn($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      // $this->Model_main->getcogssummary();
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      
      $arr_query = $this->db->query("SELECT a.`Cabang`,
                             a.NoFaktur,
                             a.`Total`,
                             dd.`Total`
                       FROM `trs_faktur` a
                       LEFT JOIN
                       (
                          SELECT a.`Cabang`,a.NoDokumen,
                                 SUM(a.`Jumlah`) AS Total
                          FROM `trs_faktur_cndn` a
                          WHERE a.TanggalCNDN BETWEEN '".$first_date."' AND '".$end_date."'
                          GROUP BY a.`Cabang`,a.`NoDokumen`) dd 
                       ON a.`Cabang`=dd.`Cabang` AND 
                          a.`NoFaktur`= dd.`NoDokumen`
                      WHERE a.TglFaktur BETWEEN '".$first_date."' AND '".$end_date."'  AND 
                            a.`TipeDokumen` IN ('CN','DN') AND 
                           (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;")->num_rows();
      
      // $arr_query=[
      //   "doheader" =>$doheader,
      //   "dodetail" =>$dodetail,
      //   "cndn" =>$cndn,
      //   "dokirim" =>$dokirim,
      //   "vallunas" =>$vallunas,
      // ];
      return $arr_query;

    }
    public function getselisihvallunas($tgl1 = NULL,$tgl2 = NULL,$getmonth = NULL)
    {
      // $this->Model_main->getcogssummary();
      // $daynumber = date('d');
      // if($daynumber <= 3){
      //   $tgl1  = date('Y-m-01');
      //   $tgl2  = date('Y-m-d');
      // }else{
      //   $tgl1  = date('Y-m-d',strtotime("-3 days"));
      //   $tgl2  = date('Y-m-d');
      // }
      
      $arr_query = $this->db->query("SELECT a.NoFaktur,
                                    a.Value AS  valuefaktur,a.TglFaktur,a.TipeDokumen ,
                              a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,
                              (CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                              WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                            ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                            FROM trs_faktur a
                            LEFT JOIN (
                              SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                                SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                                CASE 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  ELSE (IFNULL(ValuePelunasan,0) )
                                  END AS ValuePelunasanx
                                 
                                FROM trs_pelunasan_detail 
                                   WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                UNION ALL
                                SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                                CASE 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                  WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                  ELSE (IFNULL(ValuePelunasan,0) )
                                  END AS ValuePelunasanx
                                FROM trs_pelunasan_giro_detail 
                                   WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                )xdat GROUP BY NomorFaktur
                            )pel ON a.NoFaktur=pel.NomorFaktur
                             WHERE a.NoFaktur IN (
                              SELECT DISTINCT NomorFaktur FROM(
                              SELECT NomorFaktur
                              FROM trs_pelunasan_detail 
                                 WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                 AND TglPelunasan between '".$tgl1."' and '".$tgl2."'
                              UNION ALL
                              SELECT NomorFaktur
                              FROM trs_pelunasan_giro_detail 
                                 WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                 AND TglPelunasan between '".$tgl1."' and '".$tgl2."'
                                 )xx
                            ) 
                            HAVING selisih != 0;")->num_rows();
      return $arr_query;
    }
    public function getselisihbpbDO($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $query=$this->db->query("SELECT a.`Cabang`,
                                       a.`NoDokumen`,
                                       a.`NoDO`,
                                       COUNT(a.`NoDokumen`) AS 'jmlproduk',
                                       SUM(a.`Banyak`) AS 'totalQty',
                                       DObeli.NoDokumen AS 'DOBeli',
                                       DObeli.jmlproduk AS 'jmlprodukDO',
                                       DObeli.totalQty AS 'totalQtyDO',
                                       (CASE WHEN COUNT(a.`Produk`) != DObeli.jmlproduk THEN 'selisih' ELSE 'ok' END) AS 'statusJml',
                                       (CASE WHEN SUM(a.`Banyak`) != DObeli.totalQty THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                FROM trs_terima_barang_detail a
                                LEFT JOIN (
                                  SELECT b.`Cabang`,
                                    b.`NoDokumen`,
                                    COUNT(b.`Produk`) AS 'jmlproduk',
                                    SUM(b.`Banyak`) AS 'totalQty'
                                  FROM trs_delivery_order_detail b
                                  WHERE b.`Status` NOT IN ('Batal','Reject')
                                        GROUP BY b.`Cabang`,b.`NoDokumen`) AS DObeli ON 
                                a.`Cabang` = DObeli.Cabang AND 
                                a.`NoDO` =  DObeli.NoDokumen
                                WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                       a.`Status` NOT IN ('Batal','Reject') AND 
                                      a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`
                                HAVING (IFNULL(jmlproduk,0) != IFNULL(jmlprodukDO,0)) AND (IFNULL(totalQty,0) != IFNULL(totalQtyDO,0));")->num_rows();
      if($query > 0){
        $arr_query = $this->db->query("SELECT Cabang,
                                             NoDokumen,
                                             flag_suratjalan,
                                             VALUE,
                                             Total,
                                             DokDetail,
                                             ValueDetail,
                                             TotalDetail
                                      FROM (SELECT a.`Cabang`,
                                             a.`NoDokumen`,
                                             a.`flag_suratjalan`,
                                             SUM(a.`Value`) AS 'Value',
                                             SUM(a.`Total`) AS 'Total',
                                             detail.NoDokumen AS 'DokDetail',
                                             detail.Value AS 'ValueDetail',
                                             detail.Total AS 'TotalDetail',
                                             (CASE WHEN SUM(a.`Value`) != detail.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                             (CASE WHEN SUM(a.`Total`) != detail.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                      FROM trs_terima_barang_header a
                                      JOIN (
                                        SELECT b.`Cabang`,
                                          b.`NoDokumen`,
                                          SUM(b.`Value`) AS 'Value',
                                          SUM(b.`Total`) AS 'Total'
                                        FROM trs_terima_barang_detail b
                                        WHERE b.`Status` NOT IN ('Batal','Reject')
                                              GROUP BY b.`Cabang`,b.`NoDokumen`) AS detail ON 
                                      a.`Cabang` = detail.Cabang AND 
                                      a.`NoDokumen` =  detail.NoDokumen
                                      WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                             a.`Status` NOT IN ('Batal','Reject') AND 
                                            a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY a.`Cabang`,a.`NoDokumen`, a.`flag_suratjalan`
                                      UNION ALL
                                      SELECT a.`Cabang`,
                                             a.`NoDokumen`,
                                             '' AS 'flag_suratjalan',
                                             SUM(a.`Value`) AS 'Value',
                                             SUM(a.`Total`) AS 'Total',
                                             detail.NoDokumen AS 'DokDetail',
                                             detail.Value AS 'ValueDetail',
                                             detail.Total AS 'TotalDetail',
                                             (CASE WHEN SUM(a.`Value`) != detail.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                             (CASE WHEN SUM(a.`Total`) != detail.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                      FROM trs_delivery_order_header a
                                      JOIN (
                                        SELECT b.`Cabang`,
                                          b.`NoDokumen`,
                                          SUM(b.`Value`) AS 'Value',
                                          SUM(b.`Total`) AS 'Total'
                                        FROM trs_delivery_order_detail b
                                        WHERE b.`Status` NOT IN ('Batal','Reject')
                                              GROUP BY b.`Cabang`,b.`NoDokumen`) AS detail ON 
                                      a.`Cabang` = detail.Cabang AND 
                                      a.`NoDokumen` =  detail.NoDokumen
                                      WHERE IFNULL(a.tipe,'') != 'Relokasi' AND
                                            IFNULL(a.No_Relokasi,'') = '' AND
                                             a.`Status` NOT IN ('Batal','Reject') AND 
                                            a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY a.`Cabang`,a.`NoDokumen`) AS BPBDO
                                      HAVING (IFNULL(Total,0) != IFNULL(TotalDetail,0))")->num_rows();
      }else{
        $arr_query = $this->db->query("SELECT a.`Cabang`,
                                           a.`NoDokumen`,
                                           a.`NoDO`,
                                           a.`flag_suratjalan`,
                                           SUM(a.`Value`) AS 'Value',
                                           SUM(a.`Total`) AS 'Total',
                                           DObeli.NoDokumen AS 'DOBeli',
                                           DObeli.Value AS 'ValueDO',
                                           DObeli.Total AS 'TotalDO'
                                    FROM trs_terima_barang_header a
                                    LEFT JOIN (
                                      SELECT b.`Cabang`,
                                        b.`NoDokumen`,
                                        SUM(b.`Value`) AS 'Value',
                                        SUM(b.`Total`) AS 'Total'
                                      FROM trs_delivery_order_header b
                                      WHERE b.`Status` NOT IN ('Batal','Reject')
                                            GROUP BY b.`Cabang`,b.`NoDokumen`) AS DObeli ON 
                                    a.`Cabang` = DObeli.Cabang AND 
                                    a.`NoDO` =  DObeli.NoDokumen
                                    WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                           a.`Status` NOT IN ('Batal','Reject') AND 
                                          a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                    GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`, a.`flag_suratjalan`
                                    HAVING (IFNULL(Total,0) != IFNULL(TotalDO,0));")->num_rows();
      }
      
      return $arr_query;

    }
    public function getselisihbpbDODetail($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $query=$this->db->query("SELECT a.`Cabang`,
                                       a.`NoDokumen`,
                                       a.`NoDO`,
                                       COUNT(a.`NoDokumen`) AS 'jmlproduk',
                                       SUM(a.`Banyak`) AS 'totalQty',
                                       DObeli.NoDokumen AS 'DOBeli',
                                       DObeli.jmlproduk AS 'jmlprodukDO',
                                       DObeli.totalQty AS 'totalQtyDO',
                                       (CASE WHEN COUNT(a.`Produk`) != DObeli.jmlproduk THEN 'selisih' ELSE 'ok' END) AS 'statusJml',
                                       (CASE WHEN SUM(a.`Banyak`) != DObeli.totalQty THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                FROM trs_terima_barang_detail a
                                LEFT JOIN (
                                  SELECT b.`Cabang`,
                                    b.`NoDokumen`,
                                    COUNT(b.`Produk`) AS 'jmlproduk',
                                    SUM(b.`Banyak`) AS 'totalQty'
                                  FROM trs_delivery_order_detail b
                                  WHERE b.`Status` NOT IN ('Batal','Reject')
                                        GROUP BY b.`Cabang`,b.`NoDokumen`) AS DObeli ON 
                                a.`Cabang` = DObeli.Cabang AND 
                                a.`NoDO` =  DObeli.NoDokumen
                                WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                       a.`Status` NOT IN ('Batal','Reject') AND 
                                      a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`
                                HAVING (IFNULL(jmlproduk,0) != IFNULL(jmlprodukDO,0)) AND (IFNULL(totalQty,0) != IFNULL(totalQtyDO,0));")->num_rows();
      if($query > 0){
        $arr_query = $this->db->query("SELECT Cabang,
                                             NoDokumen,
                                             flag_suratjalan,
                                             VALUE,
                                             Total,
                                             DokDetail,
                                             ValueDetail,
                                             TotalDetail
                                      FROM (SELECT a.`Cabang`,
                                             a.`NoDokumen`,
                                             a.`flag_suratjalan`,
                                             SUM(a.`Value`) AS 'Value',
                                             SUM(a.`Total`) AS 'Total',
                                             detail.NoDokumen AS 'DokDetail',
                                             detail.Value AS 'ValueDetail',
                                             detail.Total AS 'TotalDetail',
                                             (CASE WHEN SUM(a.`Value`) != detail.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                             (CASE WHEN SUM(a.`Total`) != detail.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                      FROM trs_terima_barang_header a
                                      JOIN (
                                        SELECT b.`Cabang`,
                                          b.`NoDokumen`,
                                          SUM(b.`Value`) AS 'Value',
                                          SUM(b.`Total`) AS 'Total'
                                        FROM trs_terima_barang_detail b
                                        WHERE b.`Status` NOT IN ('Batal','Reject')
                                              GROUP BY b.`Cabang`,b.`NoDokumen`) AS detail ON 
                                      a.`Cabang` = detail.Cabang AND 
                                      a.`NoDokumen` =  detail.NoDokumen
                                      WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                             a.`Status` NOT IN ('Batal','Reject') AND 
                                            a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY a.`Cabang`,a.`NoDokumen`, a.`flag_suratjalan`
                                      UNION ALL
                                      SELECT a.`Cabang`,
                                             a.`NoDokumen`,
                                             '' AS 'flag_suratjalan',
                                             SUM(a.`Value`) AS 'Value',
                                             SUM(a.`Total`) AS 'Total',
                                             detail.NoDokumen AS 'DokDetail',
                                             detail.Value AS 'ValueDetail',
                                             detail.Total AS 'TotalDetail',
                                             (CASE WHEN SUM(a.`Value`) != detail.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                             (CASE WHEN SUM(a.`Total`) != detail.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                      FROM trs_delivery_order_header a
                                      JOIN (
                                        SELECT b.`Cabang`,
                                          b.`NoDokumen`,
                                          SUM(b.`Value`) AS 'Value',
                                          SUM(b.`Total`) AS 'Total'
                                        FROM trs_delivery_order_detail b
                                        WHERE b.`Status` NOT IN ('Batal','Reject')
                                              GROUP BY b.`Cabang`,b.`NoDokumen`) AS detail ON 
                                      a.`Cabang` = detail.Cabang AND 
                                      a.`NoDokumen` =  detail.NoDokumen
                                      WHERE IFNULL(a.tipe,'') != 'Relokasi' AND
                                            IFNULL(a.No_Relokasi,'') = '' AND
                                             a.`Status` NOT IN ('Batal','Reject') AND 
                                            a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                      GROUP BY a.`Cabang`,a.`NoDokumen`) AS BPBDO
                                      HAVING (IFNULL(Total,0) != IFNULL(TotalDetail,0)) AND (IFNULL(VALUE,0) != IFNULL(ValueDetail,0))")->num_rows();
      }else{
        $arr_query = $this->db->query("SELECT a.`Cabang`,
                                         a.`NoDokumen`,
                                         a.`NoDO`,
                                         SUM(a.`Value`) AS 'Value',
                                         SUM(a.`Total`) AS 'Total',
                                         DObeli.NoDokumen AS 'DOBeli',
                                         DObeli.Value AS 'ValueDO',
                                         DObeli.Total AS 'TotalDO',
                                         (CASE WHEN SUM(a.`Value`) != DObeli.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                         (CASE WHEN SUM(a.`Total`) != DObeli.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                  FROM trs_terima_barang_detail a
                                  LEFT JOIN (
                                    SELECT b.`Cabang`,
                                      b.`NoDokumen`,
                                      SUM(b.`Value`) AS 'Value',
                                      SUM(b.`Total`) AS 'Total'
                                    FROM trs_delivery_order_detail b
                                    WHERE b.`Status` NOT IN ('Batal','Reject')
                                          GROUP BY b.`Cabang`,b.`NoDokumen`) AS DObeli ON 
                                  a.`Cabang` = DObeli.Cabang AND 
                                  a.`NoDO` =  DObeli.NoDokumen
                                  WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                         a.`Status` NOT IN ('Batal','Reject') AND 
                                        a.`TglDokumen` BETWEEN '".$first_date."' AND '".$end_date."'
                                  GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`
                                  HAVING (IFNULL(Total,0) != IFNULL(TotalDO,0)) and (IFNULL(Value,0) != IFNULL(ValueDO,0));")->num_rows();
      }
      return $arr_query;
    }
    public function getselisihdofaktur($first_date = NULL,$end_date = NULL,$getmonth = NULL)
    {
      $daynumber = date('d');
      if($daynumber <= 3){
        $tgl1  = date('Y-m-01');
        $tgl2  = date('Y-m-d');
      }else{
        $tgl1  = date('Y-m-d',strtotime("-3 days"));
        $tgl2  = date('Y-m-d');
      }
      $query=$this->db->query("SELECT a.`Cabang`,
                                           a.NoDO,
                                           a.`Total`,
                                           ifnull(dd.`Total`,0) as 'TotalD'
                                    FROM `trs_delivery_order_sales` a
                                    LEFT JOIN
                                    (
                                        SELECT a.`Cabang`,a.NoDO,
                                               SUM(a.`Total`) AS Total
                                        FROM `trs_delivery_order_sales_detail` a
                                        WHERE TglDO between '".$first_date."' and '".$end_date."'
                                        GROUP BY a.`Cabang`,a.`NoDO`) dd 
                                    ON a.`Cabang`=dd.`Cabang` AND a.`NoDO`= dd.`NoDO`
                                    WHERE TglDO between '".$first_date."' and '".$end_date."' and a.status not in ('Batal','Reject') and
                                        IFNULL(a.`Total`,0) != IFNULL(dd.`Total`,0);")->num_rows();
      
      return $query;

    }

    function reproses_kartustok_sawal($gudang = NULL,$first_date = NULL,$end_date = NULL,$Kode = NULL,$getmonth = NULL,$getyear = NULL,$tipe = NULL){
      $valid = true;
        // $saldo_awal = $this->saldoawal($Kode,$getmonth,$getyear);
      $saldo_awal = 0;
        if ($gudang == "Baik") {
              $valid = $this->reproses_kartustok_baik_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal);
        }elseif ($gudang == "Koreksi") {
              $valid = $this->reproses_kartustok_koreksi_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal);
        }elseif ($gudang == "Lain-lain") {
              // $valid = $this->reproses_kartustok_lain_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal);
              $valid = ["status" =>false,"message"=>" Tidak ada transaksi di gudang ini"];
        }elseif ($gudang == "Relokasi") {
              $valid = $this->reproses_kartustok_relokasi_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal);
        }elseif ($gudang == "Retur Supplier") {

              $valid = $this->reproses_kartustok_retur_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$tipe);
        }else{
              $valid = ["status" =>false,"message"=>" Tidak ada transaksi di gudang ini","addjs" => 'N'];
        }
     

     return $valid;   

    }

    function reproses_kartustok_baik_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal){
      $valid = true;

      $cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode' ");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {
            $selisih = $cek_sawal->row("stok_sum") - $cek_sawal->row("stok_detail");

            if ($selisih > 0) {
              //menambah saldo awal detail salah satu no dokumen
              //get detail yg melebihi selisih

              $get_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='$gudang' AND tahun='$getyear' AND KodeProduk='$Kode' Order BY ExpDate DESC  LIMIT 1 "); 

              if ($get_detail->num_rows() > 0) {
                 $stok_unit = $get_detail->row("SAwa".$getmonth) + $selisih;


                  for ($i=$getmonth; $i <= date('m'); $i++) { 
                      if (strlen($i) == 1) {
                        $i = "0".$i;
                      }
                      
                    $this->db->set("SAwa".$i, $stok_unit);
                    $this->db->set("VAwa".$i, $stok_unit. " * UnitCOGS",FALSE);
                  }

                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("Gudang", $gudang);
                  $this->db->where("BatchNo", $get_detail->row("BatchNo"));
                  $this->db->where("NoDokumen", $get_detail->row("NoDokumen"));
                  $this->db->where("Tahun", $getyear);
                  $valid = $this->db->update('trs_invdet');

                 
                 $valid = true; $message="Berhasil";
                return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 

              }else{
                $valid = false; $message="Data Selisih Detail Tidak Ditemukan";
                return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
              } 
            }else{
              // mengurangi saldo awal detail salah saty no dokumen
              $selisih = $selisih * -1;
              $get_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='$gudang' AND tahun='$getyear' AND KodeProduk='$Kode' and SAwa$getmonth >= $selisih LIMIT 1 "); 

              if ($get_detail->num_rows() > 0) {
                 $stok_unit = $get_detail->row("SAwa".$getmonth) - $selisih ;
                 // $stok_unit = 0 ;

                 for ($i=$getmonth; $i <= date('m'); $i++) { 
                      if (strlen($i) == 1) {
                        $i = "0".$i;
                      }
                      
                    $this->db->set("SAwa".$i, $stok_unit);
                    $this->db->set("VAwa".$i, "(SAwa".$i.") * UnitCOGS",FALSE);
                  }

                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("Gudang", $gudang);
                  $this->db->where("BatchNo", $get_detail->row("BatchNo"));
                  $this->db->where("NoDokumen", $get_detail->row("NoDokumen"));
                  $this->db->where("Tahun", $getyear);
                  $valid = $this->db->update('trs_invdet');

                
                 
                 $valid = true; $message="Berhasil";
                return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 

              }else{
                $valid = false; $message="Data Selisih Detail Tidak Ditemukan";
                return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
              } 
            }
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message]; 
      } 
    }

    function reproses_kartustok_koreksi_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal){

      $valid = true;

      $cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode'");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {
              $cek_selisih = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE Produk = '$Kode' AND  DATE_FORMAT(tanggal,'%Y-%m') < '$getyear-$getmonth' AND STATUS ='Open'");

              $sum_seharusnya = 0;
              if ($cek_selisih->num_rows() > 0 ) {
                foreach ($cek_selisih->result_array() as $j) {
                  $selisih = $j['qty'];

                  if ($selisih < 0 ) {
                    $selisih = $selisih * -1;
                  }

                  $sum_seharusnya += $selisih;

                  $data_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='$gudang' AND KodeProduk='$Kode' AND tahun='$getyear' AND BatchNo = '".$j['batch']."' AND NoDokumen = '".$j['NoDocStok']."'");

                  if ($data_detail->num_rows() > 0 ) {

                    for ($i=$getmonth; $i <= date('m'); $i++) { 
                        if (strlen($i) == 1) {
                          $i = "0".$i;
                        }
                        
                      $this->db->set("SAwa".$i, $selisih);
                      $this->db->set("VAwa".$i, $selisih." * UnitCOGS",FALSE);
                    }

                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("BatchNo", $j["batch"]);
                    $this->db->where("NoDokumen", $j["NoDocStok"]);
                    $this->db->where("ExpDate", $j["exp_date"]);
                    $this->db->where("Tahun", $getyear);
                    $valid = $this->db->update('trs_invdet');

                  }

                   
                }

                if ($sum_seharusnya != $cek_sawal->row("stok_sum")) {
                  $selisih = $sum_seharusnya - $cek_sawal->row("stok_sum");

                  $valid = false; $message="Adjustment ".$selisih;
                        return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'Y',"qty" => $selisih];
                }else{
                  $valid = true; $message="Berhasil";
                  return $data = ["status" =>$valid,"message"=>$message];  
                }

                
              }else{
                $selisih = 0;

                for ($i=$getmonth; $i <= date('m'); $i++) { 
                    if (strlen($i) == 1) {
                      $i = "0".$i;
                    }
                    
                  $this->db->set("SAwa".$i, $selisih);
                  $this->db->set("VAwa".$i, $selisih." * UnitCOGS",FALSE);
                }

                $this->db->where("KodeProduk", $Kode);
                $this->db->where("Gudang", $gudang);
                $this->db->where("Tahun", $getyear);
                $valid = $this->db->update('trs_invdet');

                $valid = true; $message="Berhasil";
                return $data = ["status" =>$valid,"message"=>$message]; 
              }
          }

      }
      
    }

    function reproses_kartustok_relokasi_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal){
      $valid = true;

      $cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode' ");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {


              // update Saldo awal detail 0
              $data_detail = $this->db->query("SELECT * FROM trs_invdet where KodeProduk = '$Kode' AND gudang = '$gudang' AND tahun='$getyear'");


              if ($data_detail->num_rows() > 0) {
                foreach ($data_detail->result_array() as $j) {
                  $stok_unit = 0;

                  for ($i=$getmonth; $i <= date('m'); $i++) { 
                      if (strlen($i) == 1) {
                        $i = "0".$i;
                      }
                      
                    $this->db->set("SAwa".$i, $stok_unit);
                    $this->db->set("VAwa".$i, $stok_unit." * UnitCOGS",FALSE);
                  }

                  $this->db->where("KodeProduk", $Kode);
                  $this->db->where("Gudang", $gudang);
                  $this->db->where("BatchNo", $j["BatchNo"]);
                  $this->db->where("NoDokumen", $j["NoDokumen"]);
                  $this->db->where("Tahun", $getyear);
                  $valid = $this->db->update('trs_invdet');
                  
                }

                
              }

              $get_detail = $this->db->query("SELECT * FROM `trs_relokasi_kirim_detail` WHERE Produk ='$Kode' AND STATUS ='Open' AND DATE_FORMAT(Tgl_kirim,'%Y-%m') < '$getmonth-$getyear'"); 

              if ($get_detail->num_rows() > 0) { // ada stok gantung 
                $stok_gantung= 0;
                foreach ($get_detail->result_array() as $j) {
                  $stok_gantung += $j['Qty'] + $j['Bonus'];
                }
                
                 $selisih = $stok_gantung;
                 
                 if ($cek_sawal->row("stok_sum") > $selisih) {
                    $selisih_detail = "";
                  }else{
                    $selisih_detail = "0";
                  }
              }else{ // tidak ada stok gantung

                $selisih = $cek_sawal->row("stok_sum") * -1;
                $selisih_detail = "0";

                $valid = false; $message="Adjustment ".$selisih;
                        return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'Y',"qty" => $selisih];
              } 
               
           
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message]; 
      }    
    }

    function reproses_kartustok_retur_sawal($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$tipe){

      $valid = true;
      $cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode'");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {

            $cek_selisih = $this->db->query("
                             SELECT
                              zz.*, v.SAwa$getmonth, IF ((Qty * - 1) - (Qty_terima * - 1) = SAwa$getmonth, 'Sama', 'Beda') cek, SAwa$getmonth - ((Qty * - 1) - (Qty_terima * - 1)) selisih, ((Qty * - 1) - (Qty_terima * - 1)) seharusnya FROM (SELECT No_Usulan, Produk, ExpDate, NoDokumen, IF (jml_terima > 1, SUM(Qty + Bonus) / jml_terima, SUM(Qty + Bonus) ) Qty, BatchNo, IF ( DATE_FORMAT (TglDokumen, '%Y-%m') < '$getyear-$getmonth', IF (jml_terima = jml_usulan, SUM(Qty + Bonus) / jml_terima, SUM(Qty_terima + Bonus_terima) ),0) Qty_terima, jml_terima, jml_usulan FROM (SELECT a.No_Usulan, a.Produk, a.ExpDate, a.Qty, a.Bonus, a.BatchNo, b.NoDokumen, IF(DATE_FORMAT (b.TglDokumen, '%Y-%m') < '$getyear-$getmonth', IF (b.qty < a.qty, 0, IFNULL (b.Qty, 0)),0) Qty_terima,IFNULL (b.Bonus, 0) Bonus_terima,  (SELECT COUNT(c.Produk) FROM trs_terima_barang_detail c WHERE a.No_Usulan = c.NoUsulan AND a.Produk = c.Produk AND a.BatchNo = c.BatchNo AND a.ExpDate = c.ExpDate AND a.Produk = c.Produk AND c.Tipe = 'BKB') jml_terima, (SELECT COUNT(d.Produk) FROM trs_usulan_retur_beli_detail d WHERE a.No_Usulan = d.No_Usulan AND a.Produk = d.Produk AND a.BatchNo = d.BatchNo AND a.ExpDate = d.ExpDate AND a.Produk = d.Produk) jml_usulan, b.TglDokumen FROM trs_usulan_retur_beli_detail a JOIN trs_usulan_retur_beli_header e ON a.No_Usulan = e.No_Usulan LEFT JOIN trs_terima_barang_detail b ON a.No_Usulan = b.NoUsulan AND a.Produk = b.Produk AND a.BatchNo = b.BatchNo AND a.ExpDate = b.ExpDate AND b.Tipe = 'BKB' WHERE a.Produk = '$Kode'  AND DATE_FORMAT (e.Tanggal, '%Y-%m') < '$getyear-$getmonth') z GROUP BY No_Usulan, BatchNo, ExpDate) zz LEFT JOIN trs_invdet v ON v.KodeProduk = zz.Produk AND v.`BatchNo` = zz.BatchNo AND v.`NoDokumen` = zz.No_Usulan AND v.`ExpDate` = zz.ExpDate AND v.tahun = '$getyear'AND v.gudang = '$gudang'");

            $sum_seharusnya = 0;

            if ($cek_selisih->num_rows() > 0) {
              $string_NoDoc = $string_batch = "";

              foreach ($cek_selisih->result_array() as $j) {
                $string_NoDoc .= "'".$j["No_Usulan"]."',"; 
                $string_batch .= "'".$j["BatchNo"]."',";   
                $sum_seharusnya +=  $j["seharusnya"];

                if ($j["cek"] == "Beda") {
                  $cek_detail = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '".$j["BatchNo"]."' AND NoDokumen = '".$j["No_Usulan"]."' AND ExpDate = '".$j["ExpDate"]."'");

                  if ($cek_detail->num_rows() == 0) {
                    $valid = false; $message=" BatchNo = ".$j["BatchNo"]." NoDokumen = ".$j["No_Usulan"]."  ExpDate = ".$j["ExpDate"]." , tidak ada di inventory detail";
                    return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
                    break;
                  }

                  if ($j["seharusnya"] < 0 ) {
                    $valid = false; $message="Stok tidak boleh minus setelah reproses BatchNo = ".$j["BatchNo"]." NoDokumen = ".$j["No_Usulan"]."  ExpDate = ".$j["ExpDate"]." , stok = ".number_format($j["seharusnya"]);
                    return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
                    break;
                  }

                  $string_NoDoc .= "'".$j["No_Usulan"]."',"; 
                  $string_batch .= "'".$j["BatchNo"]."',";  

                    for ($i=$getmonth; $i <= date('m'); $i++) { 
                        if (strlen($i) == 1) {
                          $i = "0".$i;
                        }
                        
                      $this->db->set("SAwa".$i, $j["seharusnya"]);
                      $this->db->set("VAwa".$i, $j["seharusnya"]." * UnitCOGS",FALSE);
                    }

                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("Gudang", $gudang);
                    $this->db->where("BatchNo", $j["BatchNo"]);
                    $this->db->where("NoDokumen", $j["No_Usulan"]);
                    $this->db->where("ExpDate", $j["ExpDate"]);
                    $this->db->where("Tahun", $getyear);
                    $valid = $this->db->update('trs_invdet');


                  
                }
              }

              $string_batch =  substr($string_batch, 0,-1);
              $string_NoDoc =  substr($string_NoDoc, 0,-1);

              // mengembalikan saldo awal detail yg tidak ada dalam transaksi menjadi 0
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and NoDokumen NOT IN ($string_NoDoc) ");

                if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                      $stok_unit = 0;

                        for ($i=$getmonth; $i <= date('m'); $i++) { 
                            if (strlen($i) == 1) {
                              $i = "0".$i;
                            }
                            
                          $this->db->set("SAwa".$i, $stok_unit);
                          $this->db->set("VAwa".$i, $stok_unit." * UnitCOGS",FALSE);
                        }

                        $this->db->where("KodeProduk", $Kode);
                        $this->db->where("Gudang", $gudang);
                        $this->db->where("BatchNo", $jj["BatchNo"]);
                        $this->db->where("NoDokumen", $jj["NoDokumen"]);
                        $this->db->where("ExpDate", $jj["ExpDate"]);
                        $this->db->where("Tahun", $getyear);
                        $valid = $this->db->update('trs_invdet');

                    } 
                }

                $message = "Berhasil";

                // echo $sum_seharusnya. " , ".$cek_sawal->row("stok_sum");
                if ($sum_seharusnya != $cek_sawal->row("stok_sum")) {
                  $selisih =  $sum_seharusnya - $cek_sawal->row("stok_sum");
                  
                  if ($selisih < 0 ) {
                    $selisih_a = $selisih * -1;
                  }else{
                    $selisih_a = $selisih;  
                  }

                  if ($cek_sawal->row("stok_detail") >= $sum_seharusnya) {
                    $selisih_detail = "0";
                  }else{
                    $selisih_detail = "";
                  }
                  $valid = false; $message="Adjustment ".$selisih;
                        return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'Y',"qty" => $selisih];

                }

                $valid = true; $message="Berhasil";
                return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N'];

            }else{

              /*$valid = false; $message="Transaksi tidak di temukan";
              return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; */

              // mengembalikan saldo awal detail yg tidak ada dalam transaksi menjadi 0
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'");

                if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                      $stok_unit = 0;

                      for ($i=$getmonth; $i <= date('m'); $i++) { 
                          if (strlen($i) == 1) {
                            $i = "0".$i;
                          }
                          
                        $this->db->set("SAwa".$i, $stok_unit);
                        $this->db->set("VAwa".$i, $stok_unit." * UnitCOGS",FALSE);
                      }

                      $this->db->where("KodeProduk", $Kode);
                      $this->db->where("Gudang", $gudang);
                      $this->db->where("BatchNo", $jj["BatchNo"]);
                      $this->db->where("NoDokumen", $jj["NoDokumen"]);
                      $this->db->where("ExpDate", $jj["ExpDate"]);
                      $this->db->where("Tahun", $getyear);
                      $valid = $this->db->update('trs_invdet');
                      
                    }
                }

                $message = "Transaksi Tidak Ditemukan";
            }

            $valid = true; 
            return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message,"addjs" => 'N']; 
      }
     
    }

    function reproses_kartustok($gudang = NULL,$first_date = NULL,$end_date = NULL,$Kode = NULL,$getmonth = NULL,$getyear = NULL){
      $valid = true;
        // $saldo_awal = $this->saldoawal($Kode,$getmonth,$getyear);
      $saldo_awal = 0;
      $saldo_akhir = $this->cek_saldo_akhir($Kode);
      $cektglstok = $this->Model_main->cek_tglstoktrans();

      if($cektglstok == 0){
          $date = date('Y-m-d',strtotime("-10 days"));
          $end_date = date('Y-m-t', strtotime($date));
          $first_date = date('Y-m-01', strtotime($date));
      }
        
        if ($gudang == "Baik") {
              $valid = $this->reproses_kartustok_baik($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }elseif ($gudang == "Koreksi") {
              $valid = $this->reproses_kartustok_koreksi($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }elseif ($gudang == "Lain-lain") {
              $valid = $this->reproses_kartustok_lain($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }elseif ($gudang == "Relokasi") {
              $valid = $this->reproses_kartustok_relokasi($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }elseif ($gudang == "Retur Supplier") {
              $valid = $this->reproses_kartustok_retur($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }elseif ($gudang == "all"){
              $this->reproses_kartustok_baik("Baik",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
              $this->reproses_kartustok_koreksi("Koreksi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
              $this->reproses_kartustok_lain("Lain-lain",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
              $this->reproses_kartustok_relokasi("Relokasi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
              $valid = $this->reproses_kartustok_retur("Retur Supplier",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);

              $cek_inv = $this->db->query("SELECT SUM(UnitStok) inv_sum,(SELECT SUM(UnitStok) FROM trs_invdet WHERE KodeProduk = a.`KodeProduk` AND tahun = a.tahun ) inv_det FROM trs_invsum a WHERE kodeProduk='$Kode' AND tahun ='$getyear'")->row();

              if ($cek_inv->inv_sum != $cek_inv->inv_det) {
                $valid = ["status" =>false,"message"=>" Total Unit Stok summary dan detail tidak sama"];
              }else if ($saldo_akhir != $cek_inv->inv_sum){
                $valid = ["status" =>false,"message"=>" Total Unit Stok dan saldo akhir tidak sama"];
              }else{
                $valid = ["status" =>true,"message"=>" Berhasil"];
              }
        }else{
              $valid = ["status" =>false,"message"=>" Tidak ada transaksi di gudang ini"];
        }
     

     return $valid;   

    }

    function reproses_kartustok_baik($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir){
      $valid = true;

      /*$cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode' ");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {
            $selisih = $cek_sawal->row("stok_sum") - $cek_sawal->row("stok_detail");

            if ($selisih > 0) {
              //menambah saldo awal detail salah satu no dokumen
              //get detail yg melebihi selisih

              $get_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='$gudang' AND tahun='$getyear' AND KodeProduk='$Kode' Order BY ExpDate DESC  LIMIT 1 "); 

              if ($get_detail->num_rows() > 0) {
                 $stok_unit = $get_detail->row("SAwa".$getmonth) + $selisih;

                 $valid = $this->db->query("UPDATE trs_invdet
                                       set UnitStok = ".$stok_unit.",
                                           ValueStok = ".$stok_unit." * UnitCOGS,
                                           SAwa".$getmonth." = $stok_unit,
                                           VAwa".$getmonth." = ".$stok_unit." * UnitCOGS
                                       where KodeProduk = '".$Kode."' and 
                                            Gudang = '".$gudang."' and 
                                            BatchNo ='".$get_detail->row("BatchNo")."' and 
                                            NoDokumen ='".$get_detail->row("NoDokumen")."' and 
                                        Tahun = '".$getyear."' ");

              }else{
                $valid = false; $message="Data Selisih Detail Tidak Ditemukan";
                return $data = ["status" =>$valid,"message"=>$message]; 
              } 
            }else{
              // mengurangi saldo awal detail salah saty no dokumen
              $selisih = $selisih * -1;
              $get_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='Baik' AND tahun='$getyear' AND KodeProduk='$Kode' and SAwa$getmonth >= $selisih LIMIT 1 "); 

              if ($get_detail->num_rows() > 0) {
                 $stok_unit = $get_detail->row("SAwa".$getmonth) - $selisih ;

                 $valid = $this->db->query("UPDATE trs_invdet
                                       set UnitStok = ".$stok_unit.",
                                           ValueStok = ".$stok_unit." * UnitCOGS,
                                           SAwa".$getmonth." = $stok_unit,
                                           VAwa".$getmonth." = ".$stok_unit." * UnitCOGS
                                       where KodeProduk = '".$Kode."' and 
                                            Gudang = '".$gudang."' and 
                                            BatchNo ='".$get_detail->row("BatchNo")."' and 
                                            NoDokumen ='".$get_detail->row("NoDokumen")."' and 
                                        Tahun = '".$getyear."'");

              }else{
                $valid = false; $message="Data Selisih Detail Tidak Ditemukan";
                return $data = ["status" =>$valid,"message"=>$message]; 
              } 
            }
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message]; 
      }*/

        $data = $this->db->query("SELECT
                            NoDoc, Kode, NamaProduk, SUM(qty_in) qty_in, SUM(qty_out) qty_out, BatchNo, DATE_FORMAT(ExpDate,'%Y-%m-%d')ExpDate, 'Baik' AS Gudang
                          FROM
                            (
                            SELECT
                              '1' as No,NoDokumen AS 'NoDoc', CONCAT( 'Terima dari ', Prinsipal, ' - ', Supplier ) AS 'Dokumen', TglDokumen AS 'TglDoc', TimeDokumen AS 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', ( IFNULL( Qty, '' ) + IFNULL( Bonus, '' ) ) AS 'qty_in', 0 AS 'qty_out', BatchNo, ExpDate FROM trs_terima_barang_detail WHERE trs_terima_barang_detail.Produk = '$Kode'AND STATUS NOT IN ( 'pending', 'Batal' ) AND IFNULL( Tipe, '' ) NOT IN ( 'BKB', 'Tolakan' ) AND TglDokumen BETWEEN '$first_date'AND '$end_date' 
                            UNION ALL
                            SELECT 
                              '2' AS NO, trs_usulan_retur_beli_detail.BatchDoc AS 'NoDoc', CONCAT ('Usulan Retur Beli : QTY = ', ((trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus ) * - 1 ), '  ~ Ke : ', trs_usulan_retur_beli_header.Prinsipal ) AS 'Dokumen', trs_usulan_retur_beli_header.Tanggal AS 'TglDoc', trs_usulan_retur_beli_header.added_time AS 'TimeDoc', trs_usulan_retur_beli_detail.Produk AS 'Kode', trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk', trs_usulan_retur_beli_detail.Satuan AS 'Unit', 
                                CASE WHEN IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'Y') = 'R' THEN (trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus ) * - 1  ELSE 0 END AS 'qty_in', CASE WHEN IFNULL(trs_usulan_retur_beli_header.App_BM_Status,'Y') = 'Y' THEN (trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus ) * - 1  ELSE 0 END AS 'qty_out', trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo', trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate'FROM trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan WHERE trs_usulan_retur_beli_header.Status_Usulan IN ('Open','Closed') AND trs_usulan_retur_beli_detail.Produk = '$Kode'AND trs_usulan_retur_beli_header.tanggal BETWEEN '$first_date'AND '$end_date'
                            UNION ALL

                            SELECT
                              '3' AS NO, BatchDoc AS 'NoDoc', 'Relokasi (Kirim,Batal,Reject) 'AS 'Dokumen', tgl_reject AS 'TglDoc', Time_kirim 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', 0 AS 'qty_in', CASE WHEN STATUS IN ('Reject', 'Open','Kirim','RejectCabPenerima','RejectPusat') THEN (Qty + Bonus) ELSE 0 END AS 'qty_out', BatchNo AS 'BatchNo', ExpDate AS 'ExpDate'FROM trs_relokasi_kirim_detail WHERE STATUS IN ('Kirim', 'Batal', 'Reject', 'Open','RejectCabPenerima','RejectPusat') AND Produk = '$Kode'AND Tgl_kirim BETWEEN '$first_date'AND '$end_date'

                            UNION ALL

                            SELECT
                              '3.1' AS NO, BatchDoc AS 'NoDoc', 'Relokasi (Reject) 'AS 'Dokumen', tgl_reject AS 'TglDoc', Time_kirim 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit',  (Qty + Bonus)  AS 'qty_in', 0 AS 'qty_out', BatchNo AS 'BatchNo', ExpDate AS 'ExpDate'FROM trs_relokasi_kirim_detail WHERE STATUS IN ('Reject') AND Produk = '$Kode'AND Tgl_reject BETWEEN '$first_date'AND '$end_date'

                            UNION ALL

                            SELECT
                              '4' AS NO, NoBpb AS 'NoDoc', 'Delivery Order (DO,RDO,Batal)' AS 'Dokumen', TglDO AS 'TglDoc', TimeDO AS 'TimeDoc', KodeProduk AS 'Kode', NamaProduk AS 'NamaProduk', UOM AS 'Unit', CASE WHEN (STATUS = 'Retur' AND tipedokumen = 'Retur') THEN (QtyDO + BonusDO)  WHEN (STATUS = 'Batal') THEN (QtyDO + BonusDO) ELSE 0 END AS 'qty_in', CASE WHEN (tipedokumen = 'DO') THEN (QtyDO + BonusDO) ELSE 0 END AS 'qty_out', BatchNo, ExpDate FROM trs_delivery_order_sales_detail WHERE KodeProduk = '$Kode'AND TglDO BETWEEN '$first_date'AND '$end_date'AND tipedokumen IN ('DO','Retur')

                            UNION ALL

                            SELECT
                              '5' AS NO,No_Terima AS 'NoDoc', CONCAT ('Relokasi Dari Cabang  : ', Cabang_Pengirim ) AS 'Dokumen', Tgl_terima AS 'TglDoc', Time_terima AS 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', (Qty + Bonus) AS 'qty_in', 0 AS 'qty_out', BatchNo AS 'BatchNo', ExpDate AS 'ExpDate'FROM trs_relokasi_terima_detail WHERE (Qty > 0 OR Bonus > 0) AND STATUS = 'Terima' AND Produk ='$Kode'  AND Tgl_terima BETWEEN '$first_date'AND '$end_date'

                            UNION ALL

                            SELECT
                              '6' AS NO,NoFaktur AS 'NoDoc', CONCAT ('Terima dari ', Pelanggan, ' - ', NamaPelanggan ) AS 'Dokumen', TglFaktur AS 'TglDoc', TimeFaktur AS 'TimeDoc', KodeProduk AS 'Kode', NamaProduk AS 'NamaProduk', UOM AS 'Unit', CASE WHEN (IFNULL (STATUS, '') <> 'Batal') THEN (QtyFaktur + BonusFaktur) ELSE 0 END AS 'qty_in', CASE WHEN (IFNULL (STATUS, '') = 'Batal') THEN (QtyFaktur + BonusFaktur) ELSE 0 END AS 'qty_out', BatchNo, ExpDate FROM trs_faktur_detail WHERE TipeDokumen = 'Retur'AND KodeProduk = '$Kode'AND TglFaktur BETWEEN '$first_date'AND '$end_date'

                            UNION ALL

                             SELECT
                                '7' AS NO, NoDocStok AS 'NoDoc', CONCAT ('Usulan Adjusment : ', qty, ' ', catatan ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_koreksi.created_at AS 'TimeDoc', trs_mutasi_koreksi.produk AS 'Kode', trs_mutasi_koreksi.nama_produk AS 'NamaProduk', Satuan AS 'Unit', CASE WHEN trs_mutasi_koreksi.STATUS = 'Approve'THEN IF(qty<0,0,qty) ELSE 0 END AS 'qty_in', CASE WHEN  trs_mutasi_koreksi.STATUS IN ('Open','Approve')THEN IF(qty<0,qty * -1,0) ELSE 0 END AS 'qty_out', batch AS 'BatchNo', exp_date AS 'ExpDate'FROM trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk` WHERE trs_mutasi_koreksi.STATUS IN ('Approve','Open') AND gudang = 'Baik'AND trs_mutasi_koreksi.`produk` = '$Kode'AND tanggal BETWEEN '$first_date 00:00:00'AND '$end_date 23:59:59'

                            UNION ALL

                            SELECT
                              '8' as No,NoDocStok_awal AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal AS 'BatchNo', expdate_awal AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_awal = 'Baik'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                            UNION ALL
                            SELECT
                              '9' as No,NoDocStok_akhir AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_akhir, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir AS 'BatchNo', expdate_akhir AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_akhir = 'Baik'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 

                            UNION ALL
                            SELECT
                              '10' as No,NoDocStok_awal AS 'NoDoc', 'Mutasi Batch awal '+batchno_awal AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal, expdate_awal AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Baik' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                            UNION ALL
                            SELECT
                              '11' as No,NoDocStok_akhir AS 'NoDoc', 'Mutasi Batch akhir '+batchno_akhir AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir, expdate_akhir AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Baik' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                            UNION ALL
                            SELECT 
                            '12' AS NO, NoDokumen AS 'NoDoc', CONCAT ('Tolakan Retur ', Prinsipal, ' - ', Supplier, ' ~ Qty : ', (IFNULL (Qty, '') + IFNULL (Bonus, '')) * - 1 ) AS 'Dokumen', TglDokumen AS 'TglDoc', TimeDokumen AS 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', (IFNULL (Qty, '') + IFNULL (Bonus, '')) * - 1 AS 'qty_in', 0 AS 'qty_out', BatchNo, ExpDate FROM trs_terima_barang_detail WHERE IFNULL (Tipe, '') = 'Tolakan'AND Produk = '$Kode'AND TglDokumen BETWEEN '$first_date'AND '$end_date'

                          
                            ) z GROUP BY BatchNo ,NoDoc,ExpDate");
          $sum_qty = 0;

          if ($data->num_rows() == 0) {
            // Mengembalikan ke stok awal
            $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' ");

            if ($valid == true && $batch_detail_a->num_rows() > 0) {

              foreach ($batch_detail_a->result_array() as $jj) {
                $stok_unit = $jj['SAwa'.$getmonth];
                $valid = $this->db->query("update trs_invdet
                               set UnitStok = ".$stok_unit.",
                                   ValueStok = '".$stok_unit."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                    BatchNo ='".$jj["BatchNo"]."' and 
                                    NoDokumen ='".$jj["NoDokumen"]."' and 
                                    ExpDate ='".$jj["ExpDate"]."' and 
                                Tahun = '".$getyear."' ");
                
                $sum_qty += $stok_unit; // unit stok summary
              }
            }else{
              $valid = false; $message="Tidak ada data detail di gudang $gudang";
              return $data = ["status" =>$valid,"message"=>$message]; 
            }

            $valid = true;
            $message="Berhasil";
          }else{   
            $valid = true;
            $message = "Berhasil";
            
            $string_batch= "";
            $string_NoDoc= "";
             foreach ($data->result() as $r) {

              //select inv det
              $data_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' and ExpDate = '$r->ExpDate' ");

              if ($data_detail->num_rows() > 0 ) {
                  $string_batch .= "'$r->BatchNo',";
                  $string_NoDoc .= "'$r->NoDoc',";

                  $batch_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' and ExpDate = '$r->ExpDate'");
                   
                  if ($batch_detail->num_rows() > 0) {
                    $valid = true;
                      foreach ($batch_detail->result_array() as $kk) {
                        if ($kk['SAwa'.$getmonth] != "0"  ) {
                          if ($kk['SAwa'.$getmonth] == "") {
                            $kk['SAwa'.$getmonth] = 0;
                          }

                          $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                        }else{
                          $stok_unit = ($r->qty_in - $r->qty_out);
                        }
                        
                          if ($stok_unit < 0) {
                            $switch = $this->switch_sawal_detail($data,$getyear,$getmonth,$stok_unit,$Kode,$gudang,$r->BatchNo,$r->NoDoc,$r->ExpDate);
                            
                            
                                                        
                            if ($switch == 0) {
                              $detail = $this->db->query("SELECT * FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' and UnitStok > 0 LIMIT 1");

                              if ($detail->num_rows() == 0) {
                                $switch_detail = $this->switch_inv_detail($getyear,$stok_unit,$Kode,$gudang,$r->BatchNo,$r->NoDoc,$r->ExpDate);

                                $valid = false;
                                 $message = "Stok tidak boleh minus setelah reproses , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate;
                                  
                                  $data = ["status" =>$valid,"message"=>$message];
                                  return $data;

                                  
                                  break;
                              }
                              
                            }
                            $stok_unit = 0;
                            
                          }

                      }

                      if ($valid) {
                        foreach ($batch_detail->result_array() as $kk) {
                          if ($kk['SAwa'.$getmonth] != "0"  ) {
                            if ($kk['SAwa'.$getmonth] == "") {
                              $kk['SAwa'.$getmonth] = 0;
                            }

                            $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                          }else{
                            $stok_unit = ($r->qty_in - $r->qty_out);
                          }

                          if ($stok_unit < 0) {
                            $stok_unit = 0;
                          }

                           $valid = $this->db->query("UPDATE trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = ".$stok_unit." * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$kk["BatchNo"]."' and 
                                              NoDokumen ='".$kk["NoDokumen"]."' and 
                                              ExpDate ='".$kk["ExpDate"]."' and 
                                          Tahun = '".$getyear."' ");
                          

                          $sum_qty += $stok_unit; // unit stok summary
                        }
                      }
                  }else{
                    $stok_unit =  ($r->qty_in - $r->qty_out);
                    if ($stok_unit < 0) {
                      $valid = false;
                      $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                      $data = ["status" =>$valid,"message"=>$message];
                            return $data;
                      break;
                    }

                    $sum_qty += $stok_unit; // unit stok summary

                    $generate = $this->generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$r->BatchNo,$r->NoDoc);

                    //get detail
                    if ($generate != "1") {
                      $valid = false;
                      $message = "Data detail digudang ".$gudang." tidak ditemukan";
                      break;

                    }
                    
                  }

                 
              }else{
                 $valid = false;
                  $message = "Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." Tidak Ada dalam inventory Detail";

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;
              }

            }

              $string_batch =  substr($string_batch, 0,-1);
              $string_NoDoc =  substr($string_NoDoc, 0,-1);

              // NoDOc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch)  and NoDokumen NOT IN ($string_NoDoc) ");

              if ($valid == true && $batch_detail_a->num_rows() > 0) {

                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];
                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");
                  
                  $sum_qty += $stok_unit; // unit stok summary
                }
              }

             // batc lain yang tidak ada dalam transaksi
            
            
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and BatchNo NOT IN ($string_batch) ");

              if ($valid == true && $batch_detail_a->num_rows() > 0 ) {
                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];
                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");
                  
                  $sum_qty += $stok_unit; // unit stok summary
                }
              }

        }

        // update stok summary
        $stok_sum = $sum_qty;

        if ($stok_sum < 0 ) {
          $valid = false;
          $message = "Stok Summary minus = ".$stok_sum;
        }

        if ($valid == true) {
            $valid = $this->db->query("update trs_invsum
                               set UnitStok = '".$stok_sum."',
                                   ValueStok = '".$stok_sum."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                Tahun = '".$getyear."'");
          $stok_sum = $this->db->query("SELECT SUM(UnitStok) UnitStok FROM trs_invsum WHERE KodeProduk ='$Kode' AND tahun ='$getyear'")->row("UnitStok");

          if ($stok_sum != $saldo_akhir) {
            $selisih = $saldo_akhir - $stok_sum;
            $message = "Gudang ".$gudang." Berhasil, tapi masih ada selisih stok = ".$selisih." di gudang lain";
          }
        }
      
      $data = ["status" =>$valid,"message"=>$message];
      return $data;
    }

    function reproses_kartustok_koreksi($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir){

      $valid = true;

      /*$cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode'");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {
              $cek_selisih = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE Produk = '$Kode' AND  DATE_FORMAT(tanggal,'%Y-%m') < '".date('Y-m')."' AND STATUS ='Open'");

              if ($cek_selisih->num_rows() > 0 ) {
                foreach ($cek_selisih->result_array() as $j) {
                  $selisih = $j['qty'];

                  if ($selisih < 0 ) {
                    $selisih = $selisih * -1;
                  }

                  $data_detail = $this->db->query("SELECT * FROM trs_invdet WHERE gudang='$gudang' AND KodeProduk='$Kode' AND tahun='$getyear' AND BatchNo = '".$j['batch']."' AND NoDokumen = '".$j['NoDocStok']."'");

                  if ($data_detail->num_rows() > 0 ) {

                    $this->db->query("UPDATE trs_invdet
                                       set UnitStok = ".$selisih.",
                                           ValueStok = ".$selisih." * UnitCOGS,
                                           SAwa$getmonth = ".$selisih." ,
                                           VAwa$getmonth = ".$selisih." * UnitCOGS
                                       where KodeProduk = '".$Kode."' and 
                                            Gudang = '".$gudang."' and 
                                            BatchNo ='".$j["batch"]."' and 
                                            NoDokumen ='".$j["NoDocStok"]."' and 
                                            ExpDate ='".$j["exp_date"]."' and 
                                        Tahun = '".$getyear."' ");
                  }
                }
              }else{
                $selisih = 0;

                $this->db->query("UPDATE trs_invdet
                     set UnitStok = ".$selisih.",
                         ValueStok = ".$selisih." * UnitCOGS,
                         SAwa$getmonth = ".$selisih." ,
                         VAwa$getmonth = ".$selisih." * UnitCOGS
                     where 
                          KodeProduk = '".$Kode."' and 
                          Gudang = '".$gudang."' and 
                          Tahun = '".$getyear."' ");
              }
          }

      }*/

        $data = $this->db->query("SELECT
                                  NoDoc, Kode, NamaProduk, SUM(qty_in) qty_in, SUM(qty_out) qty_out, BatchNo, DATE_FORMAT(ExpDate,'%Y-%m-%d')ExpDate, 'Koreksi' AS Gudang
                                  FROM
                                  (
                                    SELECT
                                      '1' AS NO, NoDocStok AS 'NoDoc', CONCAT ('Usulan Adjusment : ', catatan) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_koreksi.created_at AS 'TimeDoc', trs_mutasi_koreksi.produk AS 'Kode', trs_mutasi_koreksi.nama_produk AS 'NamaProduk', Satuan AS 'Unit', IF(trs_mutasi_koreksi.gudang <> 'Koreksi',IF (qty < 0 , qty * - 1, qty),0) AS 'qty_in', CASE WHEN trs_mutasi_koreksi.STATUS = 'Approve'THEN IF(qty < 0 ,qty * -1, qty) ELSE 0 END AS 'qty_out', batch AS 'BatchNo', exp_date AS 'ExpDate'FROM trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk` WHERE  trs_mutasi_koreksi.STATUS IN ('Open', 'Approve') AND trs_mutasi_koreksi.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '3' as No,NoDocStok_awal AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal AS 'BatchNo', expdate_awal AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_awal = 'Koreksi'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                                  UNION ALL
                                  SELECT
                                    '4' as No,NoDocStok_akhir AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir AS 'BatchNo', expdate_akhir AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_akhir = 'Koreksi'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'

                                UNION ALL
                                SELECT
                                  '5' as No,NoDocStok_awal AS 'NoDoc', 'Mutasi Batch awal '+batchno_awal AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal, expdate_awal AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Koreksi' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                UNION ALL
                                SELECT
                                  '6' as No,NoDocStok_akhir AS 'NoDoc', 'Mutasi Batch akhir '+batchno_akhir AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir, expdate_akhir AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Koreksi' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                ) z GROUP BY BatchNo,NoDoc,ExpDate ");
          $sum_qty = 0;

          if ($data->num_rows() == 0) {
            /*$valid = false;
            $message = "Data tidak ditemukan";*/

            // Mengembalikan ke stok awal
            $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' ");

            if ($valid == true && $batch_detail_a->num_rows() > 0) {

              foreach ($batch_detail_a->result_array() as $jj) {
                $stok_unit = $jj['SAwa'.$getmonth];
                $valid = $this->db->query("update trs_invdet
                               set UnitStok = ".$stok_unit.",
                                   ValueStok = '".$stok_unit."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                    BatchNo ='".$jj["BatchNo"]."' and 
                                    NoDokumen ='".$jj["NoDokumen"]."' and 
                                    ExpDate ='".$jj["ExpDate"]."' and 
                                Tahun = '".$getyear."' ");
                
                $sum_qty += $stok_unit; // unit stok summary
              }
            }

            $message="Berhasil";
          }else{   
              $valid = true;
              $message = "Berhasil";
              $string_batch = "";
              $string_NoDoc= "";
             foreach ($data->result() as $r) {
              $batch_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' and ExpDate = '$r->ExpDate'");

              if ($batch_detail->num_rows() > 0) {
                $string_batch .= "'$r->BatchNo',";
                $string_NoDoc .= "'$r->NoDoc',";
                   
                      foreach ($batch_detail->result_array() as $kk) {
                        if ($kk['SAwa'.$getmonth] != "0" or $kk['SAwa'.$getmonth] != "") {
                          $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                        }else{
                          $stok_unit =  ($r->qty_in - $r->qty_out);
                        }
                        // echo $stok_unit .",";
                        if ($stok_unit < 0) {
                          $valid = false;
                          $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                          $data = ["status" =>$valid,"message"=>$message];
                          return $data;

                          break;
                        }
                      }

                      if ($valid) {
                        foreach ($batch_detail->result_array() as $kk) {
                          if ($kk['SAwa'.$getmonth] != "0"  ) {
                            if ($kk['SAwa'.$getmonth] == "") {
                              $kk['SAwa'.$getmonth] = 0;
                            }

                            $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                          }else{
                            $stok_unit = ($r->qty_in - $r->qty_out);
                          }

                          if ($stok_unit < 0) {
                            $stok_unit = 0;
                          }

                           $valid = $this->db->query("UPDATE trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = ".$stok_unit." * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$kk["BatchNo"]."' and 
                                              NoDokumen ='".$kk["NoDokumen"]."' and 
                                              ExpDate ='".$kk["ExpDate"]."' and 
                                          Tahun = '".$getyear."' ");
                          

                          $sum_qty += $stok_unit; // unit stok summary
                        }
                      }

              }else{
                $stok_unit =  ($r->qty_in - $r->qty_out);
                if ($stok_unit < 0) {
                  $valid = false;
                  $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;

                  break;
                }

                $sum_qty += $stok_unit; // unit stok summary

                $generate = $this->generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$r->BatchNo,$r->NoDoc);

                //get detail
                if ($generate != "1") {
                  $valid = false;
                  $message = "Data detail digudang ".$gudang." tidak ditemukan";

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;
                  break;
                }
                
              }
            }

            $string_batch =  substr($string_batch, 0,-1);
            $string_NoDoc =  substr($string_NoDoc, 0,-1);

            // NoDOc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch)  and NoDokumen NOT IN ($string_NoDoc) ");

              if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                    $stok_unit = $jj['SAwa'.$getmonth];

                    if ($stok_unit < 0) {
                      $stok_unit = 0;
                    }
                    $valid = $this->db->query("update trs_invdet
                                   set UnitStok = ".$stok_unit.",
                                       ValueStok = '".$stok_unit."' * UnitCOGS
                                   where KodeProduk = '".$Kode."' and 
                                        Gudang = '".$gudang."' and 
                                        BatchNo ='".$jj["BatchNo"]."' and 
                                        NoDokumen ='".$jj["NoDokumen"]."' and 
                                        ExpDate ='".$jj["ExpDate"]."' and 
                                    Tahun = '".$getyear."' ");

                    $sum_qty += $stok_unit; // unit stok summary
                  }
              }

             // batc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and BatchNo NOT IN ($string_batch)");

              if ($valid == true && $batch_detail_a->num_rows() > 0 ) {
                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];
                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");

                  $sum_qty += $stok_unit; // unit stok summary
                }
              }

            
        }

        // update stok summary
            $stok_sum = $sum_qty;

            if ($stok_sum < 0 ) {
              $valid = false;
              $message = "Stok Summary minus = ".$stok_sum;
            }

            if ($valid == true ) {
              
              $valid = $this->db->query("update trs_invsum
                                     set UnitStok = '".$stok_sum."',
                                         ValueStok = '".$stok_sum."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                      Tahun = '".$getyear."'");

              $stok_sum = $this->db->query("SELECT SUM(UnitStok) UnitStok FROM trs_invsum WHERE KodeProduk ='$Kode' AND tahun ='$getyear'")->row("UnitStok");

              if ($stok_sum != $saldo_akhir) {
                $selisih = $saldo_akhir - $stok_sum;
                $message = "Gudang ".$gudang." Berhasil, tapi masih ada selisih stok = ".$selisih." di gudang lain";
              }
            }

      $data = ["status" =>$valid,"message"=>$message];
      return $data;
    }

    function reproses_kartustok_lain($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir){
      $valid = true;

        $data = $this->db->query("SELECT
                                    NoDoc, Kode, NamaProduk, SUM(qty_in) qty_in, SUM(qty_out) qty_out, BatchNo, DATE_FORMAT(ExpDate,'%Y-%m-%d')ExpDate, 'Lain-lain' AS Gudang
                                  FROM
                                    (
                                    SELECT
                                      '1' as No ,NoDocStok_awal AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal AS 'BatchNo', expdate_awal AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_awal = 'Lain-lain' AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                                    UNION ALL
                                    SELECT
                                      '2' as No,NoDocStok_akhir AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_akhir, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir AS 'BatchNo', expdate_akhir AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_akhir = 'Lain-lain' AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'

                                    UNION ALL
                                    SELECT
                                      '3' as No,NoDocStok AS 'NoDoc', CONCAT( 'Usulan Adjusment : ', qty,' ', catatan ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_koreksi.created_at AS 'TimeDoc', trs_mutasi_koreksi.produk AS 'Kode', trs_mutasi_koreksi.nama_produk AS 'NamaProduk', Satuan AS 'Unit', CASE WHEN qty < 0 THEN 0 ELSE qty END AS 'qty_in', CASE WHEN qty < 0 THEN qty * -1 ELSE 0 END AS 'qty_out', batch AS 'BatchNo', exp_date AS 'ExpDate'FROM trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk` WHERE  trs_mutasi_koreksi.STATUS IN ('Approve' ) AND gudang='Lain - lain' AND trs_mutasi_koreksi.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'

                                    UNION ALL
                                    SELECT
                                      '4' as No,NoDocStok_awal AS 'NoDoc', 'Mutasi Batch awal '+batchno_awal AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal, expdate_awal AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Lain - lain' AND Produk = '$Kode' AND create_date BETWEEN '$first_date'AND '$end_date'
                                    UNION ALL
                                    SELECT
                                      '5' as No,NoDocStok_akhir AS 'NoDoc', 'Mutasi Batch akhir '+batchno_akhir AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir, expdate_akhir AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Lain - lain' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                    ) z GROUP BY BatchNo,NoDoc,ExpDate");
          $sum_qty = 0;

          if ($data->num_rows() == 0) {
            // Mengembalikan ke stok awal
            $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' ");

            if ($valid == true && $batch_detail_a->num_rows() > 0) {

              foreach ($batch_detail_a->result_array() as $jj) {
                $stok_unit = $jj['SAwa'.$getmonth];
                $valid = $this->db->query("update trs_invdet
                               set UnitStok = ".$stok_unit.",
                                   ValueStok = '".$stok_unit."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                    BatchNo ='".$jj["BatchNo"]."' and 
                                    NoDokumen ='".$jj["NoDokumen"]."' and 
                                    ExpDate ='".$jj["ExpDate"]."' and 
                                Tahun = '".$getyear."' ");
                
                $sum_qty += $stok_unit; // unit stok summary
              }
            }else{
              $valid = false; $message="Tidak ada data detail di gudang $gudang";
              return $data = ["status" =>$valid,"message"=>$message]; 
            }

            $valid = true;
            $message="Berhasil";

          }else{ 
              $valid = true;
              $message = "Berhasil" ;  
              $sum_qty = 0;
              $string_batch = "";
              $string_NoDoc = "";

             foreach ($data->result() as $r) {
              $batch_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' and ExpDate = '$r->ExpDate'");

              if ($batch_detail->num_rows() > 0) {
                $string_batch .= "'$r->BatchNo',";
                $string_NoDoc .= "'$r->NoDoc',";
                   
                      foreach ($batch_detail->result_array() as $kk) {
                        if ($kk['SAwa'.$getmonth] != "0" or $kk['SAwa'.$getmonth] != "") {
                          $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                        }else{
                          $stok_unit =  ($r->qty_in - $r->qty_out);
                        }
                        // echo $stok_unit .",";
                        if ($stok_unit < 0) {
                          $valid = false;
                          $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                          $data = ["status" =>$valid,"message"=>$message];
                          return $data;

                          break;
                        }
                      }  

                      if ($valid) {
                        foreach ($batch_detail->result_array() as $kk) {
                          if ($kk['SAwa'.$getmonth] != "0"  ) {
                            if ($kk['SAwa'.$getmonth] == "") {
                              $kk['SAwa'.$getmonth] = 0;
                            }

                            $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                          }else{
                            $stok_unit = ($r->qty_in - $r->qty_out);
                          }

                          if ($stok_unit < 0) {
                            $stok_unit = 0;
                          }

                           $valid = $this->db->query("UPDATE trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = ".$stok_unit." * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$kk["BatchNo"]."' and 
                                              NoDokumen ='".$kk["NoDokumen"]."' and 
                                              ExpDate ='".$kk["ExpDate"]."' and 
                                          Tahun = '".$getyear."' ");
                          

                          $sum_qty += $stok_unit; // unit stok summary
                        }
                      }

              }else{
                $stok_unit =  ($r->qty_in - $r->qty_out);
                if ($stok_unit < 0) {
                  $valid = false;
                  $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;
                  break;
                }

                $sum_qty += $stok_unit; // unit stok summary

                $generate = $this->generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$r->BatchNo,$r->NoDoc);

                //get detail
                if ($generate != "1") {
                  $valid = false;
                  $message = "Data detail digudang ".$gudang." tidak ditemukan";

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;

                  break;
                }
                
              }
            }


            $string_batch =  substr($string_batch, 0,-1);
            $string_NoDoc =  substr($string_NoDoc, 0,-1);

             // NoDOc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch)  and NoDokumen NOT IN ($string_NoDoc) ");

              if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                    $stok_unit = $jj['SAwa'.$getmonth];

                    if ($stok_unit < 0) {
                      $stok_unit = 0;
                    }

                    $valid = $this->db->query("update trs_invdet
                                   set UnitStok = ".$stok_unit.",
                                       ValueStok = '".$stok_unit."' * UnitCOGS
                                   where KodeProduk = '".$Kode."' and 
                                        Gudang = '".$gudang."' and 
                                        BatchNo ='".$jj["BatchNo"]."' and 
                                        NoDokumen ='".$jj["NoDokumen"]."' and 
                                        ExpDate ='".$jj["ExpDate"]."' and 
                                    Tahun = '".$getyear."' ");

                    $sum_qty += $stok_unit; // unit stok summary
                  }
            }

             // batc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' and SAwa$getmonth <> UnitStok and BatchNo NOT IN ($string_batch)");

              if ($valid == true && $batch_detail_a->num_rows() > 0 ) {
                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];

                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");

                  $sum_qty += $stok_unit; // unit stok summary
                }
              }

           
        }

        // update stok summary
        $stok_sum = $sum_qty;

        if ($stok_sum < 0 ) {
          $valid = false;
          $message = "Stok Summary minus = ".$stok_sum;
        }

        if ($valid == true) {
            $valid = $this->db->query("update trs_invsum
                               set UnitStok = '".$stok_sum."',
                                   ValueStok = '".$stok_sum."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                Tahun = '".$getyear."'");
          $stok_sum = $this->db->query("SELECT SUM(UnitStok) UnitStok FROM trs_invsum WHERE KodeProduk ='$Kode' AND tahun ='$getyear'")->row("UnitStok");

          if ($stok_sum != $saldo_akhir) {
            $selisih = $saldo_akhir - $stok_sum;
            $message = "Gudang ".$gudang." Berhasil, tapi masih ada selisih stok = ".$selisih." di gudang lain";
          }
        }

      $data = ["status" =>$valid,"message"=>$message];
      return $data;
    }

    function reproses_kartustok_relokasi($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir){
      $valid = true;

      /*$cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode' ");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") AND $cek_sawal->row("UnitStok") != $cek_sawal->row("UnitStok_detail") AND $cek_sawal->row("UnitStok") != $cek_sawal->row("stok_detail")) {

           

              $get_detail = $this->db->query("SELECT * FROM `trs_relokasi_kirim_detail` WHERE Produk ='$Kode' AND STATUS ='Open' AND DATE_FORMAT(Tgl_kirim,'%Y-%m') < '".date('Y-m')."'"); 

              // update Saldo awal detail 0
              $data_detail = $this->db->query("SELECT * FROM trs_invdet where KodeProduk = '$Kode' AND gudang = '$gudang' AND tahun='$getyear'");


              if ($data_detail->num_rows() > 0) {
                foreach ($data_detail->result_array() as $j) {
                  $stok_unit = 0;

                  $this->db->query("UPDATE trs_invdet
                                     set UnitStok = ".$stok_unit.",
                                         ValueStok = ".$stok_unit." * UnitCOGS,
                                         SAwa".$getmonth." = ".$stok_unit.",
                                         VAwa".$getmonth." = ".$stok_unit." * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                          BatchNo ='".$j["BatchNo"]."' and 
                                          NoDokumen ='".$j["NoDokumen"]."' and 
                                      Tahun = '".$getyear."' ");
                }
              }

              if ($get_detail->num_rows() > 0) { // ada stok gantung 
                $stok_gantung= 0;
                foreach ($get_detail->result_array() as $j) {
                  $stok_gantung += $j['Qty'] + $j['Bonus'];
                }
                
                 $selisih = $stok_gantung;
                 
                 if ($cek_sawal->row("stok_sum") > $selisih) {
                    $selisih_detail = "";
                  }else{
                    $selisih_detail = "0";
                  }
              }else{ // tidak ada stok gantung

                $selisih = $cek_sawal->row("stok_sum") * -1;
                $selisih_detail = "0";
              } 


              // cek generate adjustment bulan ini
              $cek_mutasi = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE Produk = '$Kode' 
                            AND gudang = '$gudang' AND batch = 'NoBatch' AND DATE_FORMAT(tanggal,'%Y-%m') = '".date('Y-m')."'")->num_rows();

                  if ($cek_mutasi == 0) {
                    $this->generate_addjustment_by_reproses($gudang,$Kode,$selisih,$getyear,$getmonth,$selisih_detail,"");
                  }
           
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message]; 
      }*/

        $data = $this->db->query("SELECT
                                    NoDoc, Kode, NamaProduk, SUM(qty_in) qty_in, SUM(qty_out) qty_out, BatchNo, DATE_FORMAT(ExpDate,'%Y-%m-%d')ExpDate, 'Relokasi' AS Gudang
                                  FROM
                                    (
                                    
                                    SELECT
                                      '2' AS No,NoDocStok_awal AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal AS 'BatchNo', expdate_awal AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_awal = 'Relokasi'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                                    UNION ALL
                                    SELECT
                                      '3' AS No,NoDocStok_akhir AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir AS 'BatchNo', expdate_akhir AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_akhir = 'Relokasi'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                                    UNION ALL
                                    SELECT
                                      '4' as No,trs_relokasi_kirim_detail.No_Relokasi AS 'NoDoc', 'Relokasi (Open,Batal,Kirim) ' AS 'Dokumen', trs_relokasi_kirim_detail.Tgl_kirim AS 'TglDoc', trs_relokasi_kirim_detail.Time_kirim 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', CASE WHEN (STATUS  IN ('Open','Kirim','Reject')) THEN (Qty + Bonus) ELSE 0 END AS 'qty_in', CASE WHEN (STATUS IN ('Batal','Kirim','Reject','RejectCabPenerima','RejectPusat')) THEN (Qty + Bonus) ELSE 0 END AS 'qty_out', BatchNo AS 'BatchNo', ExpDate AS 'ExpDate'FROM trs_relokasi_kirim_detail WHERE trs_relokasi_kirim_detail.STATUS IN ( 'Open','Batal','Kirim','Reject','RejectCabPenerima','RejectPusat','ApproveLog' ) AND trs_relokasi_kirim_detail.Produk = '$Kode'AND trs_relokasi_kirim_detail.Tgl_kirim BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '5' as No,NoDocStok AS 'NoDoc', CONCAT( 'Usulan Adjusment : ',qty, ' ', catatan ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_koreksi.created_at AS 'TimeDoc', trs_mutasi_koreksi.produk AS 'Kode', trs_mutasi_koreksi.nama_produk AS 'NamaProduk', Satuan AS 'Unit', CASE WHEN qty < 0 THEN 0 ELSE qty END AS 'qty_in', CASE WHEN qty < 0 THEN qty * -1 ELSE 0 END AS 'qty_out', batch AS 'BatchNo', exp_date AS 'ExpDate'FROM trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk` WHERE  trs_mutasi_koreksi.STATUS IN ('Approve' ) AND gudang='Relokasi' AND trs_mutasi_koreksi.`produk` = '$Kode' AND trs_mutasi_koreksi.catatan <> 'Addjustment By System' AND tanggal BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '6' as No,NoDocStok_awal AS 'NoDoc', 'Mutasi Batch awal '+batchno_awal AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal, expdate_awal AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Relokasi' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '7' as No,NoDocStok_akhir AS 'NoDoc', 'Mutasi Batch akhir '+batchno_akhir AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir, expdate_akhir AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Relokasi' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                    ) z GROUP BY BatchNo,NoDoc,ExpDate ");
          $sum_qty = 0;
          if ($data->num_rows() == 0) {
            /*$valid = false;
            $message = "Data tidak ditemukan";*/

            // Mengembalikan ke stok awal
            $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' ");

            if ($valid == true && $batch_detail_a->num_rows() > 0) {

              foreach ($batch_detail_a->result_array() as $jj) {
                $stok_unit = $jj['SAwa'.$getmonth];

                if ($stok_unit < 0) {
                  $stok_unit = 0;
                }

                $valid = $this->db->query("update trs_invdet
                               set UnitStok = ".$stok_unit.",
                                   ValueStok = '".$stok_unit."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                    BatchNo ='".$jj["BatchNo"]."' and 
                                    NoDokumen ='".$jj["NoDokumen"]."' and 
                                    ExpDate ='".$jj["ExpDate"]."' and 
                                Tahun = '".$getyear."' ");
                
                $sum_qty += $stok_unit; // unit stok summary
              }
            }

            $message="Berhasil";
          }else{   
            $valid = true;
              $message = "Berhasil" ;
              
              $string_batch ="'a',";
              $string_NoDoc ="'a',";
             foreach ($data->result() as $r) {
              $batch_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' ");

              if ($batch_detail->num_rows() > 0) {
                $string_batch .= "'$r->BatchNo',";
                $string_NoDoc .= "'$r->NoDoc',";
                   
                      foreach ($batch_detail->result_array() as $kk) {
                        if ($kk['SAwa'.$getmonth] != "0" or $kk['SAwa'.$getmonth] != "") {
                          $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                        }else{
                          $stok_unit =  ($r->qty_in - $r->qty_out);
                        }
                        // echo $stok_unit .",";
                        if ($stok_unit < 0) {
                          $valid = false;
                          $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                          $data = ["status" =>$valid,"message"=>$message];
                          return $data;

                          break;
                        }

                      }

                      if ($valid) {
                        foreach ($batch_detail->result_array() as $kk) {
                          if ($kk['SAwa'.$getmonth] != "0"  ) {
                            if ($kk['SAwa'.$getmonth] == "") {
                              $kk['SAwa'.$getmonth] = 0;
                            }

                            $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                          }else{
                            $stok_unit = ($r->qty_in - $r->qty_out);
                          }

                          if ($stok_unit < 0) {
                            $stok_unit = 0;
                          }

                           $valid = $this->db->query("UPDATE trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = ".$stok_unit." * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$kk["BatchNo"]."' and 
                                              NoDokumen ='".$kk["NoDokumen"]."' and 
                                              ExpDate ='".$kk["ExpDate"]."' and 
                                          Tahun = '".$getyear."' ");
                          

                          $sum_qty += $stok_unit; // unit stok summary
                        }
                      }

              }else{
                $stok_unit =  ($r->qty_in - $r->qty_out);
                if ($stok_unit < 0) {
                  $valid = false;
                  $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;
                  break;
                }

                $sum_qty += $stok_unit; // unit stok summary

                $generate = $this->generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$r->BatchNo,$r->NoDoc);

                //get detail
                if ($generate != "1") {
                  $valid = false;
                  $message = "Data detail digudang ".$gudang." tidak ditemukan";

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;

                  break;
                }
                
              }
            }

            $string_batch =  substr($string_batch, 0,-1);
            $string_NoDoc =  substr($string_NoDoc, 0,-1);

             // NoDOc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch)  and NoDokumen NOT IN ($string_NoDoc) ");

              if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                    $stok_unit = $jj['SAwa'.$getmonth];

                    if ($stok_unit < 0) {
                      $stok_unit = 0;
                    }

                    $valid = $this->db->query("update trs_invdet
                                   set UnitStok = ".$stok_unit.",
                                       ValueStok = '".$stok_unit."' * UnitCOGS
                                   where KodeProduk = '".$Kode."' and 
                                        Gudang = '".$gudang."' and 
                                        BatchNo ='".$jj["BatchNo"]."' and 
                                        NoDokumen ='".$jj["NoDokumen"]."' and 
                                        ExpDate ='".$jj["ExpDate"]."' and 
                                    Tahun = '".$getyear."' ");

                    $sum_qty += $stok_unit; // unit stok summary
                  }
            }

             // batc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' and SAwa$getmonth <> UnitStok and BatchNo NOT IN ($string_batch)");

              if ($valid == true && $batch_detail_a->num_rows() > 0 ) {
                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];

                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");

                 $sum_qty += $stok_unit; // unit stok summary
                }
              }
            
        }

        // update stok summary
            $stok_sum = $sum_qty;

             if ($stok_sum < 0 ) {
                $valid = false;
                $message = "Stok Summary minus = ".$stok_sum;
              }

            if ($valid == true ) {
              
              $valid = $this->db->query("update trs_invsum
                                     set UnitStok = '".$stok_sum."',
                                         ValueStok = '".$stok_sum."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                      Tahun = '".$getyear."'");

              $stok_sum = $this->db->query("SELECT SUM(UnitStok) UnitStok FROM trs_invsum WHERE KodeProduk ='$Kode' AND tahun ='$getyear'")->row("UnitStok");

              if ($stok_sum != $saldo_akhir) {
                $selisih = $saldo_akhir - $stok_sum;
                $message = "Gudang ".$gudang." Berhasil, tapi masih ada selisih stok = ".$selisih." di gudang lain";
              }
            }
      $data = ["status" =>$valid,"message"=>$message];
      return $data;
    }

    function reproses_kartustok_retur($gudang,$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir){

      $valid = true;

      /*$cek_sawal = $this->db->query("SELECT 
                                      KodeProduk,tahun,gudang,SAwal$getmonth as stok_sum,(SELECT SUM(SAwa$getmonth) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) AS stok_detail,UnitStok,
                                    (SELECT SUM(UnitStok) FROM trs_invdet b WHERE b.gudang = a.gudang AND b.tahun = a.tahun AND b.KodeProduk = a.KodeProduk) 
                                  AS UnitStok_detail FROM trs_invsum a WHERE a.tahun = '$getyear' AND a.gudang ='$gudang' AND a.KodeProduk = '$Kode' ");
      if($cek_sawal->num_rows() > 0 ){
          if ($cek_sawal->row("stok_sum") != $cek_sawal->row("stok_detail") ) {
            $cek_selisih = $this->db->query("
                    SELECT
                      zz.*, v.SAwa$getmonth, IF ((Qty * - 1) - (Qty_terima * - 1) = SAwa$getmonth, 'Sama', 'Beda') cek, SAwa$getmonth - ((Qty * - 1) - (Qty_terima * - 1)) selisih, ((Qty * - 1) - (Qty_terima * - 1)) seharusnya FROM (SELECT No_Usulan, Produk, ExpDate, NoDokumen, IF(jml_terima > 1, SUM(Qty + Bonus)/jml_terima,SUM(Qty + Bonus)) Qty, BatchNo, IF(jml_terima = jml_usulan,SUM(Qty + Bonus)/jml_terima, SUM(Qty_terima + Bonus_terima))  Qty_terima, jml_terima,jml_usulan FROM (SELECT a.No_Usulan, a.Produk, a.ExpDate, a.Qty, a.Bonus, a.BatchNo, b.NoDokumen, IF(b.qty < a.qty ,0,IFNULL(b.Qty, 0)) Qty_terima, IFNULL (b.Bonus, 0) Bonus_terima, (SELECT COUNT(c.Produk) FROM  trs_terima_barang_detail c WHERE a.No_Usulan = c.NoUsulan AND a.Produk = c.Produk AND a.BatchNo = c.BatchNo AND a.ExpDate = c.ExpDate AND a.Produk = c.Produk ) jml_terima,
                        (SELECT COUNT(d.Produk) FROM  trs_usulan_retur_beli_detail d WHERE a.No_Usulan = d.No_Usulan AND a.Produk = d.Produk AND a.BatchNo = d.BatchNo AND a.ExpDate = d.ExpDate AND a.Produk = d.Produk ) jml_usulan FROM trs_usulan_retur_beli_detail a JOIN trs_usulan_retur_beli_header e ON a.No_Usulan = e.No_Usulan LEFT JOIN trs_terima_barang_detail b ON a.No_Usulan = b.NoUsulan AND a.Produk = b.Produk AND a.BatchNo = b.BatchNo AND a.ExpDate = b.ExpDate WHERE a.Produk = '$Kode' AND DATE_FORMAT(e.Tanggal,'%Y-%m') < '".date('Y-m')."' ) z GROUP BY No_Usulan, BatchNo, ExpDate) zz LEFT JOIN trs_invdet v ON v.KodeProduk = zz.Produk AND v.`BatchNo` = zz.BatchNo AND v.`NoDokumen` = zz.No_Usulan AND v.`ExpDate` = zz.ExpDate AND v.tahun = '$getyear'AND v.gudang = '$gudang'");

            $sum_seharusnya = 0;

            if ($cek_selisih->num_rows() > 0) {
              $string_NoDoc = $string_batch = "";

              foreach ($cek_selisih->result_array() as $j) {
                $string_NoDoc .= "'".$j["No_Usulan"]."',"; 
                $string_batch .= "'".$j["BatchNo"]."',";   
                $sum_seharusnya +=  $j["seharusnya"];

                if ($j["cek"] == "Beda") {
                  $cek_detail = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '".$j["BatchNo"]."' AND NoDokumen = '".$j["No_Usulan"]."' AND ExpDate = '".$j["ExpDate"]."'");

                  if ($cek_detail->num_rows() == 0) {
                    $valid = false; $message=" BatchNo = ".$j["BatchNo"]." NoDokumen = ".$j["No_Usulan"]."  ExpDate = ".$j["ExpDate"]." , tidak ada di inventory detail";
                    return $data = ["status" =>$valid,"message"=>$message]; 
                    break;
                  }

                  $string_NoDoc .= "'".$j["No_Usulan"]."',"; 
                  $string_batch .= "'".$j["BatchNo"]."',";  

                  $this->db->query("update trs_invdet
                                 set UnitStok = ".$j["seharusnya"].",
                                     ValueStok = '".$j["seharusnya"]."' * UnitCOGS,
                                     SAwa".$getmonth." = '".$j["seharusnya"]."',
                                     VAwa".$getmonth." = '".$j["seharusnya"]."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$j["BatchNo"]."' and 
                                      NoDokumen ='".$j["No_Usulan"]."' and 
                                      ExpDate ='".$j["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");
                }
              }

              $string_batch =  substr($string_batch, 0,-1);
              $string_NoDoc =  substr($string_NoDoc, 0,-1);

              // mengembalikan saldo awal detail yg tidak ada dalam transaksi menjadi 0
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and NoDokumen NOT IN ($string_NoDoc)  ");

                if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                      $stok_unit = 0;
                       $this->db->query("update trs_invdet
                                     set UnitStok = ".$stok_unit.",
                                         ValueStok = '".$stok_unit."' * UnitCOGS,
                                     SAwa".$getmonth." = '".$stok_unit."',
                                     VAwa".$getmonth." = '".$stok_unit."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                          BatchNo ='".$jj["BatchNo"]."' and 
                                          NoDokumen ='".$jj["NoDokumen"]."' and 
                                          ExpDate ='".$jj["ExpDate"]."' and 
                                      Tahun = '".$getyear."' ");
                      
                    } 
                }

                if ($sum_seharusnya != $cek_sawal->row("stok_sum") or $sum_seharusnya != $cek_sawal->row("UnitStok")) {
                  $selisih =  $sum_seharusnya - $cek_sawal->row("stok_sum");
                  
                  if ($selisih < 0 ) {
                    $selisih_a = $selisih * -1;
                  }else{
                    $selisih_a = $selisih;  
                  }

                  if ($cek_sawal->row("stok_detail") >= $sum_seharusnya) {
                    $selisih_detail = "0";
                  }else{
                    $selisih_detail = "";
                  }

               
                  $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and SAwa$getmonth >= $selisih_a ORDER BY UnitStok DESC limit 1  ");

                  if ($batch_detail_a->num_rows() > 0) {
                    # code...
                    $stok_unit = $batch_detail_a->row("UnitStok") - $selisih_a;
                    $this->db->query("update trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = '".$stok_unit."' * UnitCOGS,
                                         SAwa".$getmonth." = '".$stok_unit."',
                                         VAwa".$getmonth." = '".$stok_unit."' * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$batch_detail_a->row("BatchNo")."' and 
                                              NoDokumen ='".$batch_detail_a->row("NoDokumen")."' and 
                                              ExpDate ='".$batch_detail_a->row("ExpDate")."' and 
                                          Tahun = '".$getyear."' ");
                  }

                  // cek generate adjustment bulan ini
                  $cek_mutasi = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE Produk = '$Kode' 
                              AND gudang = '$gudang' AND batch = 'NoBatch' AND DATE_FORMAT(tanggal,'%Y-%m') = '".date('Y-m')."'")->num_rows();

                    if ($cek_mutasi == 0) {
                      $this->generate_addjustment_by_reproses($gudang,$Kode,$selisih,$getyear,$getmonth,$selisih_detail,"");
                    }
                }

            }else{
              // mengembalikan saldo awal detail yg tidak ada dalam transaksi menjadi 0
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'");

                if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                      $stok_unit = 0;
                       $this->db->query("update trs_invdet
                                     set UnitStok = ".$stok_unit.",
                                         ValueStok = '".$stok_unit."' * UnitCOGS,
                                     SAwa".$getmonth." = '".$stok_unit."',
                                     VAwa".$getmonth." = '".$stok_unit."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                          BatchNo ='".$jj["BatchNo"]."' and 
                                          NoDokumen ='".$jj["NoDokumen"]."' and 
                                          ExpDate ='".$jj["ExpDate"]."' and 
                                      Tahun = '".$getyear."' ");
                      
                    }
                }
            }
          }
      }else{
        $valid = false; $message="Data Saldo Awal Tidak Ditemukan";
         return $data = ["status" =>$valid,"message"=>$message]; 
      }*/
        $data = $this->db->query("SELECT
                                    NoDoc, Kode, NamaProduk, SUM(qty_in) qty_in, SUM(qty_out) qty_out, BatchNo, DATE_FORMAT(ExpDate,'%Y-%m-%d')ExpDate, 'Retur Supplier' AS Gudang
                                  FROM
                                    (
                                    SELECT
                                      '1' as No,trs_usulan_retur_beli_header.No_Usulan AS 'NoDoc', CONCAT( 'Usulan Retur Beli : QTY = ', ( ( trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus ) * - 1 ), '  ~ Ke : ', trs_usulan_retur_beli_header.Prinsipal ) AS 'Dokumen', trs_usulan_retur_beli_header.Tanggal AS 'TglDoc', trs_usulan_retur_beli_header.added_time AS 'TimeDoc', trs_usulan_retur_beli_detail.Produk AS 'Kode', trs_usulan_retur_beli_detail.Nama_produk AS 'NamaProduk', trs_usulan_retur_beli_detail.Satuan AS 'Unit', 
                                      CASE WHEN (IFNULL( trs_usulan_retur_beli_header.App_BM_Status, 'Y' ) = 'Y') THEN (trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus) * - 1 ELSE 0 END AS 'qty_in', 
                                      CASE WHEN (IFNULL( trs_usulan_retur_beli_header.App_BM_Status, 'Y' ) = 'R') THEN (trs_usulan_retur_beli_detail.Qty + trs_usulan_retur_beli_detail.Bonus) * - 1 ELSE 0 END AS 'qty_out', trs_usulan_retur_beli_detail.BatchNo AS 'BatchNo', trs_usulan_retur_beli_detail.ExpDate AS 'ExpDate'FROM trs_usulan_retur_beli_header INNER JOIN trs_usulan_retur_beli_detail ON trs_usulan_retur_beli_header.No_Usulan = trs_usulan_retur_beli_detail.No_Usulan WHERE IFNULL( trs_usulan_retur_beli_header.App_BM_Status, 'Y' ) IN ('Y','R') AND trs_usulan_retur_beli_detail.Produk = '$Kode'AND trs_usulan_retur_beli_header.tanggal BETWEEN '$first_date'AND '$end_date' 
                                    UNION ALL
                                    SELECT 
                                        '2' as No,NoUsulan AS 'NoDoc', 'Mengurangi stok gudang retur supplier ' AS 'Dokumen', TglDokumen AS 'TglDoc', TimeDokumen as 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', 0 AS 'qty_in', (((ifnull(Qty,'') + ifnull(Bonus,'')))*-1) AS 'qty_out', BatchNo, ExpDate FROM trs_terima_barang_detail where   ifnull(Tipe,'') = 'BKB' and Produk = '$Kode' and TglDokumen between '$first_date' and '$end_date'
    
                                    UNION ALL 
                                    SELECT
                                      '3' as No,NoDocStok_awal AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal AS 'BatchNo', expdate_awal AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ( 'Open', 'Approve' ) AND gudang_awal = 'Retur Supplier'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date' 
                                    UNION ALL
                                    SELECT
                                      '4' as No,NoDocStok_akhir AS 'NoDoc', CONCAT( 'Mutasi Gudang dari Gudang : ', gudang_awal, ' Ke Gudang : ', gudang_akhir, ' ~ QTY = ', qty ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_gudang.create_date AS 'TimeDoc', trs_mutasi_gudang.produk AS 'Kode', trs_mutasi_gudang.namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir AS 'BatchNo', expdate_akhir AS 'ExpDate'FROM trs_mutasi_gudang INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_gudang.`produk` WHERE qty > 0 AND trs_mutasi_gudang.STATUS IN ('Approve' ) AND gudang_akhir = 'Retur Supplier'AND trs_mutasi_gudang.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '5' as No,NoDocStok AS 'NoDoc', CONCAT( 'Usulan Adjusment : ',qty, ' ', catatan ) AS 'Dokumen', tanggal AS 'TglDoc', trs_mutasi_koreksi.created_at AS 'TimeDoc', trs_mutasi_koreksi.produk AS 'Kode', trs_mutasi_koreksi.nama_produk AS 'NamaProduk', Satuan AS 'Unit', CASE WHEN qty < 0 THEN 0 ELSE qty END AS 'qty_in', CASE WHEN qty < 0 THEN qty * -1 ELSE 0 END AS 'qty_out', batch AS 'BatchNo', exp_date AS 'ExpDate'FROM trs_mutasi_koreksi INNER JOIN mst_produk ON mst_produk.`Kode_Produk` = trs_mutasi_koreksi.`produk` WHERE  trs_mutasi_koreksi.STATUS IN ('Approve' ) AND gudang='$gudang' and trs_mutasi_koreksi.catatan <> 'Addjustment By System' AND trs_mutasi_koreksi.`produk` = '$Kode'AND tanggal BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '6' as No,NoDocStok_awal AS 'NoDoc', 'Mutasi Batch awal '+batchno_awal AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', 0 AS 'qty_in', qty AS 'qty_out', batchno_awal, expdate_awal AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Retur Supplier' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'
                                  UNION ALL
                                  SELECT
                                    '7' as No,NoDocStok_akhir AS 'NoDoc', 'Mutasi Batch akhir '+batchno_akhir AS 'Dokumen', Tanggal AS 'TglDoc', create_date AS 'TimeDoc', produk AS 'Kode', namaproduk AS 'NamaProduk', '' AS 'Unit', qty AS 'qty_in', 0 AS 'qty_out', batchno_akhir, expdate_akhir AS ExpDate FROM trs_mutasi_batch WHERE STATUS = 'Approve' AND gudang='Retur Supplier' AND Produk = '$Kode'AND create_date BETWEEN '$first_date'AND '$end_date'

                                  UNION ALL
                                  SELECT 
                                  '8' AS NO, NoDokumen AS 'NoDoc', CONCAT ('Tolakan Retur ', Prinsipal, ' - ', Supplier, ' ~ Qty : ', (IFNULL (Qty, '') + IFNULL (Bonus, '')) * - 1 ) AS 'Dokumen', TglDokumen AS 'TglDoc', TimeDokumen AS 'TimeDoc', Produk AS 'Kode', NamaProduk AS 'NamaProduk', Satuan AS 'Unit', 0 AS 'qty_in', (IFNULL (Qty, '') + IFNULL (Bonus, '')) * - 1 AS 'qty_out', BatchNo, ExpDate FROM trs_terima_barang_detail WHERE IFNULL (Tipe, '') = 'Tolakan'AND Produk = '$Kode'AND TglDokumen BETWEEN '$first_date'AND '$end_date'

                                    ) z GROUP BY BatchNo,NoDoc,ExpDate");
          $sum_qty = 0;
          if ($data->num_rows() == 0) {
            // Mengembalikan ke stok awal
            $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' ");

            if ($valid == true && $batch_detail_a->num_rows() > 0) {

              foreach ($batch_detail_a->result_array() as $jj) {
                $stok_unit = $jj['SAwa'.$getmonth];
                $valid = $this->db->query("update trs_invdet
                               set UnitStok = ".$stok_unit.",
                                   ValueStok = '".$stok_unit."' * UnitCOGS
                               where KodeProduk = '".$Kode."' and 
                                    Gudang = '".$gudang."' and 
                                    BatchNo ='".$jj["BatchNo"]."' and 
                                    NoDokumen ='".$jj["NoDokumen"]."' and
                                    ExpDate ='".$jj["ExpDate"]."' and 
                                Tahun = '".$getyear."' ");
                
                $sum_qty += $stok_unit; // unit stok summary
              }
            }

            // echo $sum_qty;
            $message="Berhasil";
          }else{  
            $valid = true;
              $message = "Berhasil" ;
              
              $string_batch = "";
              $string_NoDoc = "";
             foreach ($data->result() as $r) {
              $batch_detail = $this->db->query("SELECT BatchNo, UnitStok,SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo = '$r->BatchNo' and NoDokumen = '$r->NoDoc' and ExpDate = '$r->ExpDate'");

                $string_batch .= "'$r->BatchNo',";
                $string_NoDoc .= "'$r->NoDoc',";

              if ($batch_detail->num_rows() > 0) {
                   
                      foreach ($batch_detail->result_array() as $kk) {
                        if ($kk['SAwa'.$getmonth] != "0" or $kk['SAwa'.$getmonth] != "") {
                          $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                        }else{
                          $stok_unit =  ($r->qty_in - $r->qty_out);
                        }
                        // echo $stok_unit .",";
                        if ($stok_unit < 0) {
                          $valid = false;
                          $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                          $data = ["status" =>$valid,"message"=>$message];
                          return $data;

                          break;
                        }

                      }

                      if ($valid) {
                        foreach ($batch_detail->result_array() as $kk) {
                          if ($kk['SAwa'.$getmonth] != "0"  ) {
                            if ($kk['SAwa'.$getmonth] == "") {
                              $kk['SAwa'.$getmonth] = 0;
                            }

                            $stok_unit = $kk['SAwa'.$getmonth] + ($r->qty_in - $r->qty_out);
                          }else{
                            $stok_unit = ($r->qty_in - $r->qty_out);
                          }

                          if ($stok_unit < 0) {
                            $stok_unit = 0;
                          }

                           $valid = $this->db->query("UPDATE trs_invdet
                                         set UnitStok = ".$stok_unit.",
                                             ValueStok = ".$stok_unit." * UnitCOGS
                                         where KodeProduk = '".$Kode."' and 
                                              Gudang = '".$gudang."' and 
                                              BatchNo ='".$kk["BatchNo"]."' and 
                                              NoDokumen ='".$kk["NoDokumen"]."' and 
                                              ExpDate ='".$kk["ExpDate"]."' and 
                                          Tahun = '".$getyear."' ");
                          

                          $sum_qty += $stok_unit; // unit stok summary
                        }
                      }

              }else{
                $stok_unit =  ($r->qty_in - $r->qty_out);
                if ($stok_unit < 0) {
                  $valid = false;
                  $message = "Stok tidak boleh minus , Produk ".$Kode ." , Batch = ". $r->BatchNo ." , NoDokumen = ". $r->NoDoc ." , ExpDate = ". $r->ExpDate ." , stok = ".$stok_unit;

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;

                  break;
                }

                $sum_qty += $stok_unit; // unit stok summary

                $generate = $this->generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$r->BatchNo,$r->NoDoc);

                //get detail
                if ($generate != "1") {
                  $valid = false;
                  $message = "Data detail digudang ".$gudang." tidak ditemukan";

                  $data = ["status" =>$valid,"message"=>$message];
                  return $data;
                  
                  break;
                }
                
              }
            }

            $string_batch =  substr($string_batch, 0,-1);
            $string_NoDoc =  substr($string_NoDoc, 0,-1);

             // NoDOc lain yang tidak ada dalam transaksi
                $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch) and NoDokumen NOT IN ($string_NoDoc) ");
                if ($valid == true && $batch_detail_a->num_rows() > 0) {

                  foreach ($batch_detail_a->result_array() as $jj) {
                      $stok_unit = $jj['SAwa'.$getmonth];

                      if ($stok_unit < 0) {
                        $stok_unit = 0;
                      }
                      $valid = $this->db->query("update trs_invdet
                                     set UnitStok = ".$stok_unit.",
                                         ValueStok = '".$stok_unit."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                          BatchNo ='".$jj["BatchNo"]."' and 
                                          NoDokumen ='".$jj["NoDokumen"]."' and 
                                          ExpDate ='".$jj["ExpDate"]."' and 
                                      Tahun = '".$getyear."' ");
                      
                      $sum_qty += $stok_unit; // unit stok summary
                    }
                }

             // batc lain yang tidak ada dalam transaksi
              $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and BatchNo NOT IN ($string_batch)");

              if ($valid == true && $batch_detail_a->num_rows() > 0 ) {
                foreach ($batch_detail_a->result_array() as $jj) {
                  $stok_unit = $jj['SAwa'.$getmonth];

                  if ($stok_unit < 0) {
                    $stok_unit = 0;
                  }

                  $valid = $this->db->query("update trs_invdet
                                 set UnitStok = ".$stok_unit.",
                                     ValueStok = '".$stok_unit."' * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$jj["BatchNo"]."' and 
                                      NoDokumen ='".$jj["NoDokumen"]."' and 
                                      ExpDate ='".$jj["ExpDate"]."' and 
                                  Tahun = '".$getyear."' ");

                  $sum_qty += $stok_unit; // unit stok summary
                }
              }

            
        }

        // update stok summary
            $stok_sum = $sum_qty;

            if ($stok_sum < 0 ) {
              $valid = false;
              $message = "Stok Summary minus = ".$stok_sum;

            }


            if ($valid == true ) {
              
              $valid = $this->db->query("update trs_invsum
                                     set UnitStok = '".$stok_sum."',
                                         ValueStok = '".$stok_sum."' * UnitCOGS
                                     where KodeProduk = '".$Kode."' and 
                                          Gudang = '".$gudang."' and 
                                      Tahun = '".$getyear."'");

              $stok_sum = $this->db->query("SELECT SUM(UnitStok) UnitStok FROM trs_invsum WHERE KodeProduk ='$Kode' AND tahun ='$getyear' ")->row("UnitStok");

              if ($stok_sum != $saldo_akhir) {
                $selisih = $saldo_akhir - $stok_sum;
                $message = "Gudang ".$gudang." Berhasil, tapi masih ada selisih stok = ".$selisih." di gudang lain";
              }
            }



      $data = ["status" =>$valid,"message"=>$message];
      return $data;
    }

    function switch_sawal_detail($data,$getyear,$getmonth,$selisih,$Kode,$gudang,$BatchNo,$NoDoc,$ExpDate){
      if ($selisih < 0) {
        $selisih = $selisih * -1;
      }
      $status = 0;
      $string_NoDoc = $string_batch = "'a',";
      $text ="";
      foreach ($data->result() as $r) {

        $string_NoDoc .= "'".$r->NoDoc."',"; 
        $string_batch .= "'".$r->BatchNo."',";

        $cek_detail = $this->db->query("
          SELECT * FROM trs_invdet WHERE tahun ='$getyear' and gudang = '".$gudang."' and KodeProduk = '".$r->Kode."' 
          and BatchNo = '".$r->BatchNo."' and NoDokumen = '".$r->NoDoc."' and ExpDate = '".$r->ExpDate."' and 
          (SAwa$getmonth - (IF(".$r->qty_in." - ".$r->qty_out." > 0 , ".$r->qty_in." - ".$r->qty_out.", (".$r->qty_in." - ".$r->qty_out.") * -1)  )) >= $selisih LIMIT 1");

        if ($cek_detail->num_rows() > 0 ) {
          $unitstok = $cek_detail->row("SAwa".$getmonth) - $selisih;

          //switch saldo awal detail
          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = ".$unitstok." ,
                                     VAwa$getmonth = ".$unitstok." * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$cek_detail->row("BatchNo")."' and 
                                      NoDokumen ='".$cek_detail->row("NoDokumen")."' and  
                                      ExpDate ='".$cek_detail->row("ExpDate")."' and 
                                  Tahun = '".$getyear."'");

          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") ,
                                     VAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$BatchNo."' and 
                                      NoDokumen ='".$NoDoc."' and  
                                      ExpDate ='".$ExpDate."' and 
                                  Tahun = '".$getyear."'");
          $status += 1;
        }else{
          $status += 0;
        }

      }

      $string_batch =  substr($string_batch, 0,-1);
      $string_NoDoc =  substr($string_NoDoc, 0,-1);

      if ($status == 0) {
        $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang' AND BatchNo IN ($string_batch) and NoDokumen NOT IN ($string_NoDoc) and 
          (SAwa$getmonth - (IF(".$r->qty_in." - ".$r->qty_out." > 0 , ".$r->qty_in." - ".$r->qty_out.", (".$r->qty_in." - ".$r->qty_out.") * -1)  )) >= $selisih LIMIT 1");

        if ($batch_detail_a->num_rows() > 0 ) {
          $unitstok = $batch_detail_a->row("SAwa".$getmonth) - $selisih;

          //switch saldo awal detail
          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = ".$unitstok." ,
                                     VAwa$getmonth = ".$unitstok." * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$batch_detail_a->row("BatchNo")."' and 
                                      NoDokumen ='".$batch_detail_a->row("NoDokumen")."' and  
                                      ExpDate ='".$batch_detail_a->row("ExpDate")."' and 
                                  Tahun = '".$getyear."'");

          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") ,
                                     VAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$BatchNo."' and 
                                      NoDokumen ='".$NoDoc."' and  
                                      ExpDate ='".$ExpDate."' and 
                                  Tahun = '".$getyear."'");

          $status += 1;
        }else{
          $status += 0;
        }
      }

      if ($status == 0) {
        $batch_detail_a = $this->db->query("SELECT BatchNo, UnitStok,ifnull(SAwa$getmonth,0) SAwa$getmonth,UnitCOGS,NoDokumen,ExpDate FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'  and BatchNo NOT IN ($string_batch) and 
          (SAwa$getmonth - (IF(".$r->qty_in." - ".$r->qty_out." > 0 , ".$r->qty_in." - ".$r->qty_out.", ".$r->qty_in." - ".$r->qty_out." * -1)  )) >= $selisih LIMIT 1");

        if ($batch_detail_a->num_rows() > 0 ) {
          $unitstok = $batch_detail_a->row("SAwa".$getmonth) - $selisih;

          //switch saldo awal detail
          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = ".$unitstok." ,
                                     VAwa$getmonth = ".$unitstok." * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$batch_detail_a->row("BatchNo")."' and 
                                      NoDokumen ='".$batch_detail_a->row("NoDokumen")."' and  
                                      ExpDate ='".$batch_detail_a->row("ExpDate")."' and 
                                  Tahun = '".$getyear."'");

          $this->db->query("UPDATE trs_invdet
                                 set 
                                     SAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") ,
                                     VAwa$getmonth = IF(SAwa$getmonth IS NULL, 0 + ".$selisih.",SAwa$getmonth+".$selisih.") * UnitCOGS
                                 where KodeProduk = '".$Kode."' and 
                                      Gudang = '".$gudang."' and 
                                      BatchNo ='".$BatchNo."' and 
                                      NoDokumen ='".$NoDoc."' and  
                                      ExpDate ='".$ExpDate."' and 
                                  Tahun = '".$getyear."'");

          $status += 1;
        }else{
          $status += 0;
        }
      }

      if ($status == 0) {
        return 0;
      }else{
        return 1;
      }

    }

    function generate_detail_by_reproses($Kode,$getyear,$gudang,$stok_unit,$BatchNo,$NoDoc){
      $get_all_detail = $this->db->query("SELECT * FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'");
      if ($get_all_detail->num_rows() > 0) {
        $no = $cogs = 0;
        $ed_terakhir = $KodePrinsipal = $NamaPrinsipal = $Pabrik = $NamaProduk ="";
        foreach ($get_all_detail->result() as $kk) {
          $no++;
          $cogs += $kk->UnitCOGS;
          $ed_terakhir = $kk->ExpDate; // get ed terakhir
          $KodePrinsipal = $kk->KodePrinsipal;
          $NamaPrinsipal = $kk->NamaPrinsipal;
          $Pabrik = $kk->Pabrik;
          $NamaProduk = $kk->NamaProduk;
        }

        $UnitCOGS = $cogs / $no; // menghitung rata2 cogs

        $this->db->set("Tahun", date('Y'));
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("KodePrinsipal", $KodePrinsipal);
        $this->db->set("NamaPrinsipal", $NamaPrinsipal);
        $this->db->set("Pabrik", $Pabrik);
        $this->db->set("KodeProduk", $Kode);
        $this->db->set("NamaProduk", $NamaProduk);
        $this->db->set("UnitStok", $stok_unit);
        $this->db->set("ValueStok", $stok_unit * $UnitCOGS);
        $this->db->set("BatchNo", $BatchNo);
        $this->db->set("NoDokumen", $NoDoc);
        $this->db->set("ExpDate", $ed_terakhir);
        $this->db->set("Gudang", $gudang);
        $this->db->set("UnitCOGS", $UnitCOGS);
        $this->db->set("Keterangan", "Generate By ReProses Kartu Stok");
        $valid = $this->db->insert('trs_invdet');
        if ($valid) {
          $status = "1";
        }else{
          $status = "0";
        }
      }else{
        $status = "2";
      }

      return $status;
    }

    function generate_addjustment_by_reproses($gudang,$Kode,$stok_unit,$getyear,$getmonth,$selisih_detail){

      //================ Running Number ======================================//
      $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
      $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
      $kodeDokumen = $this->Model_main->kodeDokumen('Mutasi Koreksi');
      $data = $this->db->query("select max(right(no_koreksi,7)) as 'no' from trs_mutasi_koreksi where Cabang = '".$this->cabang."' and length(no_koreksi) = 13 and YEAR(tanggal) ='".date('Y')."'")->result();
      if(empty($data[0]->no) || $data[0]->no == "" ){
        $lastNumber = 1000001;
      }else {
        $lastNumber = $data[0]->no + 1;
      }
      $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
      //================= end of running number ========================================//

      $get_all_detail = $this->db->query("SELECT * FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'");
      if ($get_all_detail->num_rows() > 0) {
        $no = $cogs = 0;
        $ed_terakhir = $KodePrinsipal = $NamaPrinsipal = $Pabrik = $NamaProduk ="";
        foreach ($get_all_detail->result() as $kk) {
          $no++;
          $cogs += $kk->UnitCOGS;
          $ed_terakhir = $kk->ExpDate; // get ed terakhir
          $KodePrinsipal = $kk->KodePrinsipal;
          $NamaPrinsipal = $kk->NamaPrinsipal;
          $Pabrik = $kk->Pabrik;
          $NamaProduk = $kk->NamaProduk;
        }

        $UnitCOGS = $cogs / $no; // menghitung rata2 cogs

        $this->db->set("cabang", $this->cabang);
        $this->db->set("tanggal", date("Y-m-d"));
        $this->db->set("no_koreksi", $noDokumen);
        $this->db->set("noline", 0);
        $this->db->set("catatan", "Addjustment By System");
        $this->db->set("tipe_koreksi", "Stok");
        $this->db->set("gudang", $gudang);
        $this->db->set("produk", $Kode);
        $this->db->set("nama_produk", $NamaProduk);
        $this->db->set("qty", $stok_unit);
        $this->db->set("harga", $UnitCOGS);
        $this->db->set("value", $stok_unit * $UnitCOGS);
        $this->db->set("batch_detail", "NoBatch");
        $this->db->set("batch", "NoBatch");
        $this->db->set("exp_date", (date('Y')+1).date('-m-d'));
        $this->db->set("NoDocStok", "GenerateAdjs".date('Ymd'));
        $this->db->set("stok", $stok_unit);
        $this->db->set("status", "Approve");
        $this->db->set("created_by", $this->user);
        $this->db->set("created_at", date("Y-m-d H:i:s"));
        $this->db->set("Approve_BM", "Approve");
        $this->db->set("date_BM", date("Y-m-d"));
        $valid =  $this->db->insert("trs_mutasi_koreksi");

        $get_sum = $this->db->query("SELECT * FROM trs_invsum WHERE KodeProduk = '$Kode' AND tahun ='$getyear' AND gudang ='$gudang'")->row();
        $valuestok_baik = ($get_sum->UnitStok + $stok_unit) * $get_sum->UnitCOGS;

        $this->db->set("UnitStok", ($get_sum->UnitStok + $stok_unit));
        $this->db->set("ValueStok", $valuestok_baik);
        $this->db->where("Tahun", date('Y'));
        $this->db->where("Cabang", $this->cabang);
        $this->db->where("KodeProduk", $Kode);
        $this->db->where("Gudang", $gudang);
        $valid = $this->db->update('trs_invsum');

        if ($stok_unit <= 0 ) {
          $stok_unit = $stok_unit *-1;
        }

        if ($selisih_detail == "0") {
          $stok_unit = 0;
        }
        $this->db->set("Tahun", date('Y'));
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("KodePrinsipal", $KodePrinsipal);
        $this->db->set("NamaPrinsipal", $NamaPrinsipal);
        $this->db->set("Pabrik", $Pabrik);
        $this->db->set("KodeProduk", $Kode);
        $this->db->set("NamaProduk", $NamaProduk);
        $this->db->set("UnitStok", $stok_unit);
        $this->db->set("ValueStok", $stok_unit * $UnitCOGS);
        $this->db->set("BatchNo", "NoBatch");
        $this->db->set("NoDokumen", "GenerateAdjs".date('Ymd'));
        $this->db->set("ExpDate", (date('Y')+1).date('-m-d'));
        $this->db->set("Gudang", $gudang);
        $this->db->set("UnitCOGS", $UnitCOGS);
        $this->db->set("SAwa".$getmonth, $stok_unit);
        $this->db->set("VAwa".$getmonth, $stok_unit * $UnitCOGS);
        $valid = $this->db->insert('trs_invdet');

        if ($valid) {
          $status = "1";
        }else{
          $status = "0";
        }
      }else{
        $status = "2";
      }

      return $status;
      
    }

    function reproses_stokdetail($status=NULL,$first_date=NULL,$end_date=NULL,$Kode=NULL,$getmonth=NULL,$getyear=NULL,$saldoakhir=NULL,$stokdetail=NULL){
      $valid = false;
      $saldo_akhir = $this->cek_saldo_akhir($Kode);
      
      if($saldoakhir == 0){
        $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."'");
        $valid = ["status" =>$query,"message"=>"Ok"];
      }else{
        $sumbaik = $this->db->query("SELECT a.KodeProduk,
                                    a.Gudang,
                                    ifnull(a.UnitStok,0) AS 'UnitStok'
                                  from trs_invsum a
                                  where a.KodeProduk = '".$Kode."' and  
                                         a.Tahun = '".$getyear."' and
                                         a.gudang = 'Baik' limit 1
                                         ")->row();
        $sumKoreksi = $this->db->query("SELECT a.KodeProduk,
                                    a.Gudang,
                                    ifnull(a.UnitStok,0) AS 'UnitStok'
                                  from trs_invsum a
                                  where a.KodeProduk = '".$Kode."' and  
                                         a.Tahun = '".$getyear."' and
                                         a.gudang = 'Koreksi' limit 1
                                         ")->row();
        $sumlain = $this->db->query("SELECT a.KodeProduk,
                                    a.Gudang,
                                    ifnull(a.UnitStok,0) AS 'UnitStok'
                                  from trs_invsum a
                                  where a.KodeProduk = '".$Kode."' and  
                                         a.Tahun = '".$getyear."' and
                                         a.gudang = 'Lain-lain' limit 1
                                         ")->row();
        $sumRelokasi = $this->db->query("SELECT a.KodeProduk,
                                    a.Gudang,
                                    ifnull(a.UnitStok,0) AS 'UnitStok'
                                  from trs_invsum a
                                  where a.KodeProduk = '".$Kode."' and  
                                         a.Tahun = '".$getyear."' and
                                         a.gudang = 'Relokasi' limit 1
                                         ")->row();
        $sumsupp = $this->db->query("SELECT a.KodeProduk,
                                    a.Gudang,
                                    ifnull(a.UnitStok,0) AS 'UnitStok'
                                  from trs_invsum a
                                  where a.KodeProduk = '".$Kode."' and  
                                         a.Tahun = '".$getyear."' and
                                         a.gudang = 'Retur Supplier' limit 1
                                         ")->row();
        $saldo_awal = $this->saldoawal($Kode,$getmonth,$getyear);
        if($sumbaik != ""){
          $sbaik = $sumbaik->UnitStok;
        }else{
          $sbaik = 0;
        }

        if($sbaik == 0){
          $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."' and 
                                  Gudang ='Baik'");
          $valid = ["status" =>$query,"message"=>"Ok"];
        }else{
          // $valid = $this->reproses_stokdetail_baik("Baik",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal);
          $valid = $this->reproses_kartustok_baik("Baik",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
          
        }
        if($sumKoreksi != ""){
          $sKoreksi = $sumKoreksi->UnitStok;
        }else{
          $sKoreksi = 0;
        }
        if($sKoreksi == 0){
          $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."' and 
                                  Gudang ='Koreksi'");
          $valid = ["status" =>$query,"message"=>"Ok"];
        }else{
          $valid = $this->reproses_kartustok_koreksi("Koreksi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }
        
        if($sumlain != ""){
          $slain = $sumlain->UnitStok;
        }else{
          $slain = 0;
        }
        if($slain == 0){
          $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."' and 
                                  Gudang ='Lain-lain'");
          $valid = ["status" =>$query,"message"=>"Ok"];
        }else{
          $valid = $this->reproses_kartustok_lain("Lain-lain",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }

        if($sumRelokasi != ""){
          $sRelokasi = $sumRelokasi->UnitStok;
        }else{
          $sRelokasi = 0;
        }
        if($sRelokasi == 0){
          $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."' and 
                                  Gudang ='Relokasi'");
          $valid = ["status" =>$query,"message"=>"Ok"];
        }else{
          $valid = $this->reproses_kartustok_relokasi("Relokasi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }

        if($sumsupp != ""){
          $ssupp = $sumsupp->UnitStok;
        }else{
          $ssupp = 0;
        }
        if($ssupp == 0){
          $query = $this->db->query("update trs_invdet
                              set UnitStok = 0,
                                  ValueStok = 0
                              where KodeProduk = '".$Kode."' and  
                                  Tahun = '".$getyear."' and 
                                  Gudang ='Retur Supplier'");
          $valid = ["status" =>$query,"message"=>"Ok"];
        }else{
          $valid = $this->reproses_kartustok_retur("Retur Supplier",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
        }
      }
     return $valid;   
    }

    function reproses_stokdetail_new($status=NULL,$first_date=NULL,$end_date=NULL,$Kode=NULL,$getmonth=NULL,$getyear=NULL,$saldo_akhir=NULL,$stokdetail=NULL){
      $valid = true;
        // $saldo_awal = $this->saldoawal($Kode,$getmonth,$getyear);
      $saldo_awal = 0;
        
       
      $this->reproses_kartustok_baik("Baik",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
      $this->reproses_kartustok_koreksi("Koreksi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
      $this->reproses_kartustok_lain("Lain-lain",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
      $this->reproses_kartustok_relokasi("Relokasi",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);
      $valid = $this->reproses_kartustok_retur("Retur Supplier",$first_date,$end_date,$Kode,$getmonth,$getyear,$saldo_awal,$saldo_akhir);

      $cek_inv = $this->db->query("SELECT SUM(UnitStok) inv_sum,(SELECT SUM(UnitStok) FROM trs_invdet WHERE KodeProduk = a.`KodeProduk` AND tahun = a.tahun ) inv_det FROM trs_invsum a WHERE kodeProduk='$Kode' AND tahun ='$getyear'")->row();

      if ($cek_inv->inv_sum != $cek_inv->inv_det) {
        $valid = ["status" =>false,"message"=>" Total Unit Stok summary dan detail tidak sama"];
      }else if ($saldo_akhir != $cek_inv->inv_sum){
        $valid = ["status" =>false,"message"=>" Total Unit Stok dan saldo akhir tidak sama"];
      }else{
        $valid = ["status" =>true,"message"=>" Berhasil"];
      }
       
    }
    public function getSAwalstokdetail($getmonth=null,$getyear=null,$tipe=null,$produk=null){
      if($tipe == 'All'){
        $query = $this->db->query("
                            SELECT a.`Cabang`,
                                   a.`Tahun`,
                                   (case '".$getmonth."' 
                                    when '01' then '$getmonth~Januari'
                                    when '02' then '$getmonth~Februari'
                                    when '03' then '$getmonth~Maret'
                                    when '04' then '$getmonth~April'
                                    when '05' then '$getmonth~Mei'
                                    when '06' then '$getmonth~Juni'
                                    when '07' then '$getmonth~Juli'
                                    when '08' then '$getmonth~Agustus'
                                    when '09' then '$getmonth~September'
                                    when '10' then '$getmonth~Oktober'
                                    when '11' then '$getmonth~November'
                                    when '12' then '$getmonth~Desember'
                                    else '~' end) as 'Bulan',
                                   a.`KodeProduk`,
                                   a.`NamaProduk`,
                                   a.`Gudang`,
                                   IFNULL(a.SAwal$getmonth,0) AS 'SAwalSum',
                                   det.Gudang AS 'GudangDetail',
                                   IFNULL(det.SAwal,0) AS 'Sawaldetail',
                                   HNA AS 'HNA'
                            FROM trs_invsum a 
                            LEFT JOIN 
                            ( SELECT b.`Cabang`,b.`Tahun`,
                               b.`KodeProduk`,
                               b.`Gudang`,
                               SUM(IFNULL(b.SAwa$getmonth,0)) AS 'SAwal'
                              FROM trs_invdet b
                              where b.tahun = '".$getyear."'
                              GROUP BY b.`Cabang`,b.`Tahun`,b.`KodeProduk`,b.`Gudang`) det ON 
                            a.Cabang = det.Cabang and
                            a.`Tahun` = det.Tahun and 
                            a.`KodeProduk` = det.KodeProduk and 
                            a.`Gudang` = det.Gudang
                            where a.tahun = '".$getyear."'
                            having ifnull(SAwalSum,0) != Sawaldetail
                    ")->result();
      }else{
        $kode = explode('~',$produk)[0];
        $query = $this->db->query("
                            SELECT a.`Cabang`,
                                   a.`Tahun`,
                                   (case '".$getmonth."' 
                                    when '01' then '$getmonth~Januari'
                                    when '02' then '$getmonth~Februari'
                                    when '03' then '$getmonth~Maret'
                                    when '04' then '$getmonth~April'
                                    when '05' then '$getmonth~Mei'
                                    when '06' then '$getmonth~Juni'
                                    when '07' then '$getmonth~Juli'
                                    when '08' then '$getmonth~Agustus'
                                    when '09' then '$getmonth~September'
                                    when '10' then '$getmonth~Oktober'
                                    when '11' then '$getmonth~November'
                                    when '12' then '$getmonth~Desember'
                                    else '~' end) as 'Bulan',
                                   a.`KodeProduk`,
                                   a.`NamaProduk`,
                                   a.`Gudang`,
                                   IFNULL(a.SAwal$getmonth,0) AS 'SAwalSum',
                                   det.Gudang AS 'GudangDetail',
                                   IFNULL(det.SAwal,0) AS 'Sawaldetail'
                            FROM trs_invsum a 
                            LEFT JOIN 
                            ( SELECT b.`Cabang`,b.`Tahun`,
                               b.`KodeProduk`,
                               b.`Gudang`,
                               SUM(IFNULL(b.SAwa$getmonth,0)) AS 'SAwal'
                              FROM trs_invdet b
                              where b.KodeProduk = '".$kode."' and 
                                    b.tahun = '".$getyear."'
                              GROUP BY b.`Cabang`,b.`Tahun`,b.`KodeProduk`,b.`Gudang`) det ON 
                            a.Cabang = det.Cabang and
                            a.`Tahun` = det.Tahun and 
                            a.`KodeProduk` = det.KodeProduk and 
                            a.`Gudang` = det.Gudang
                            where a.tahun = '".$getyear."' and 
                                  a.KodeProduk = '".$kode."'
                    ")->result();
      }
        
      return $query;
    }
    public function listQtySAwalDetail($kode=null,$gudang=null,$Cabang=null,$Tahun=null,$Bulan=null)
    {   
        $sum = $this->db->query("select Tahun,Cabang,CONCAT(KodeProduk,'~',NamaProduk) AS 'Produk',Gudang,ifnull(SAwal$Bulan,0) as 'SAwalsum' from trs_invsum where KodeProduk = '".$kode."' and gudang = '".$gudang."' and Tahun = '".$Tahun."' and cabang ='".$Cabang."' limit 1")->row();
        $det = $this->db->query("select Tahun,Cabang,KodeProduk,NamaProduk as 'NamaProduk',Gudang,BatchNo,ExpDate,Gudang,NoDokumen,ifnull(SAwa$Bulan,0) as 'SAwaldetail' from trs_invdet where KodeProduk = '".$kode."' and gudang = '".$gudang."' and Tahun = '".$Tahun."' and cabang ='".$Cabang."'")->result();

        $arr_query =array(
          'SAwalsum' =>$sum,
          'SAwaldet' => $det
        );

        return $arr_query;
    }

    public function updateSAwalDetail($produk=null,$gudang=null,$SAwalupdate=null,$BatchNo=null,$BatchDoc=null,$Cabang=null,$Tahun=null,$Bulan=null)
    {   
        $query = $this->db->query("update trs_invdet
                                   set SAwa$Bulan = '".$SAwalupdate."',
                                       VAwa$Bulan = '".$SAwalupdate."' * UnitCOGS
                                   where KodeProduk = '".$produk."' and 
                                        Gudang = '".$gudang."' and 
                                        BatchNo ='".$BatchNo."' and 
                                        NoDokumen ='".$BatchDoc."' and 
                                    Tahun = '".$Tahun."' and Cabang ='".$Cabang."'");
        return $query;
    }

    function saveMutasiKoreksi_sawal($params = null){
      $tgl = $params->tgl;  
      $getmonth = substr($tgl,5,2);

      $a= date('Y-m-t', strtotime($tgl));
      $bulan_lalu = date('Y-m-d', strtotime('-1 month', strtotime($a)));
      // echo substr($bulan_lalu, 0,4);
      // exit();
       //================ Running Number ======================================//
      $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
      $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
      $kodeDokumen = $this->Model_main->kodeDokumen('Mutasi Koreksi');
      $data = $this->db->query("select max(right(no_koreksi,7)) as 'no' from trs_mutasi_koreksi where Cabang = '".$this->cabang."' and length(no_koreksi) = 13 and YEAR(tanggal) ='".date('Y')."'")->result();
      if(empty($data[0]->no) || $data[0]->no == "" ){
        $lastNumber = 1000001;
      }else {
        $lastNumber = $data[0]->no + 1;
      }
      $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
      //================= end of running number ========================================//
      $b = explode("~", $params->produk1);
      $Kode = $b[0];
      $NamaProduk = $b[1];
      $get_all_detail = $this->db->query("SELECT * FROM trs_invdet WHERE KodeProduk = '$Kode' AND tahun ='".date('Y')."' AND gudang ='$params->gudang'");
      if ($get_all_detail->num_rows() > 0) {
        $no = $cogs = 0;
        $ed_terakhir = $KodePrinsipal = $NamaPrinsipal = $Pabrik = $NamaProduk ="";
        foreach ($get_all_detail->result() as $kk) {
          $no++;
          $cogs += $kk->UnitCOGS;
          $ed_terakhir = $kk->ExpDate; // get ed terakhir
          $KodePrinsipal = $kk->KodePrinsipal;
          $NamaPrinsipal = $kk->NamaPrinsipal;
          $Pabrik = $kk->Pabrik;
          $NamaProduk = $kk->NamaProduk;
        }

        $UnitCOGS = $cogs / $no; // menghitung rata2 cogs

        $this->db->set("cabang", $this->cabang);

        $this->db->set("tanggal", $bulan_lalu);
        $this->db->set("no_koreksi", $noDokumen);
        $this->db->set("noline", 0);
        $this->db->set("catatan", $params->catatan);
        $this->db->set("tipe_koreksi", "Stok");
        $this->db->set("gudang", $params->gudang);
        $this->db->set("produk", $Kode);
        $this->db->set("nama_produk", $NamaProduk);
        $this->db->set("qty", $params->qty);
        $this->db->set("harga", $params->harga);
        $this->db->set("value", $params->value);
        $this->db->set("batch_detail", "NoBatch");
        $this->db->set("batch", $params->batch);
        $this->db->set("exp_date", $params->expdate);
        $this->db->set("NoDocStok", $params->docstok);
        $this->db->set("status", "Approve");
        $this->db->set("created_by", $this->user);
        $this->db->set("created_at", date("Y-m-d H:i:s"));
        $this->db->set("Approve_BM", "Approve");
        $this->db->set("date_BM", $bulan_lalu);
        $valid =  $this->db->insert("trs_mutasi_koreksi");

        if (!$valid) {
          $valid = false; $message="Kesalahan Pada saat addjustment";
          return $data = ["status" =>$valid,"message"=>$message]; 
        }

        $get_sum = $this->db->query("SELECT * FROM trs_invsum WHERE KodeProduk = '$Kode' AND tahun ='".date('Y')."' AND gudang ='".$params->gudang."'")->row();
        // $valuestok_baik = ($get_sum->UnitStok + $stok_unit) * $get_sum->UnitCOGS;

        // =================================
        if ($getmonth == "01") {
          $this->db->set("SAwal12", "SAwal12"."+".$params->qty,FALSE);
          $this->db->set("VAwal12", "(SAwal12"."+".$params->qty.") * UnitCOGS",FALSE);
          $this->db->set("UnitStok", "UnitStok+".$params->qty,FALSE);
          $this->db->set("ValueStok", "(ValueStok+".$params->qty.") * UnitCOGS",FALSE);
          $this->db->where("Tahun", date('Y') - 1);
          $this->db->where("Cabang", $this->cabang);
          $this->db->where("KodeProduk", $Kode);
          $this->db->where("Gudang", $params->gudang);
          $valid = $this->db->update('trs_invsum');
        }

        // =================================
      
        for ($i=$getmonth; $i <= date('m'); $i++) { 
            if (strlen($i) == 1) {
              $i = "0".$i;
            }
            
          $this->db->set("SAwal".$i, "SAwal".$i."+".$params->qty,FALSE);
          $this->db->set("VAwal".$i, "(SAwal".$i."+".$params->qty.") * UnitCOGS",FALSE);
        }
        $this->db->set("UnitStok", "UnitStok+".$params->qty,FALSE);
        $this->db->set("ValueStok", "(ValueStok+".$params->qty.") * UnitCOGS",FALSE);
        $this->db->where("Tahun", date('Y'));
        $this->db->where("Cabang", $this->cabang);
        $this->db->where("KodeProduk", $Kode);
        $this->db->where("Gudang", $params->gudang);
        $valid = $this->db->update('trs_invsum');

        if (!$valid) {
          $valid = false; $message="Kesalahan Pada saat Update Sawal Summary";
          return $data = ["status" =>$valid,"message"=>$message]; 
        }

        if ($params->qty <= 0 ) {
          $stok_unit = 0;
        }else{
          $stok_unit = $params->qty;
        }

        // =================================
        if ($getmonth == "01") {
          $this->db->set("SAwa12", "SAwa12"."+".$params->qty,FALSE);
          $this->db->set("VAwa12", "(SAwa12"."+".$params->qty.") * UnitCOGS",FALSE);
          $this->db->set("UnitStok", "UnitStok+".$params->qty,FALSE);
          $this->db->set("ValueStok", "(ValueStok+".$params->qty.") * UnitCOGS",FALSE);
          $this->db->set("Tahun", date('Y') - 1);
          $this->db->set("Cabang", $this->cabang);
          $this->db->set("KodePrinsipal", $KodePrinsipal);
          $this->db->set("NamaPrinsipal", $NamaPrinsipal);
          $this->db->set("Pabrik", $Pabrik);
          $this->db->set("KodeProduk", $Kode);
          $this->db->set("NamaProduk", $NamaProduk);
          $this->db->set("BatchNo", "NoBatch");
          $this->db->set("UnitCOGS", $UnitCOGS);
          $this->db->set("UnitStok", $params->qty);
          $this->db->set("ValueStok", $params->qty * $UnitCOGS);
          $this->db->set("NoDokumen", $params->docstok);
          $this->db->set("ExpDate", $params->expdate);
          $this->db->set("Gudang", $params->gudang);

          $this->db->where("Tahun", date('Y') - 1);
          $this->db->where("Cabang", $this->cabang);
          $this->db->where("KodeProduk", $Kode);
          $this->db->where("Gudang", $params->gudang);
          $valid = $this->db->insert('trs_invdet');
        }

        // =================================

        $this->db->set("Tahun", date('Y'));
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("KodePrinsipal", $KodePrinsipal);
        $this->db->set("NamaPrinsipal", $NamaPrinsipal);
        $this->db->set("Pabrik", $Pabrik);
        $this->db->set("KodeProduk", $Kode);
        $this->db->set("NamaProduk", $NamaProduk);
        $this->db->set("BatchNo", "NoBatch");
        $this->db->set("UnitCOGS", $UnitCOGS);
        $this->db->set("UnitStok", $params->qty);
        $this->db->set("ValueStok", $params->qty * $UnitCOGS);
        $this->db->set("NoDokumen", $params->docstok);
        $this->db->set("ExpDate", $params->expdate);
        $this->db->set("Gudang", $params->gudang);
        for ($i=$getmonth; $i <= date('m'); $i++) { 
            if (strlen($i) == 1) {
              $i = "0".$i;
            }
            
          $this->db->set("SAwa".$i, "SAwa".$i."+".$params->qty,FALSE);
          $this->db->set("VAwa".$i, "(SAwa".$i."+".$params->qty.") * UnitCOGS",FALSE);
        }
        $valid = $this->db->insert('trs_invdet');

        if (!$valid) {
          $valid = false; $message="Kesalahan Pada Insert Detail";
          return $data = ["status" =>$valid,"message"=>$message]; 
        }else{
          $valid = true; $message="Berhasil";
          return $data = ["status" =>$valid,"message"=>$message]; 
        }
      }else{
        $valid = false; $message="Data Detail Tidak Ditemukan";
        return $data = ["status" =>$valid,"message"=>$message]; 
      }
    }

    function cek_saldo_akhir($produk){
      $cektglstok = $this->Model_main->cek_tglstoktrans();
      if($cektglstok == 1){
          $hari_ini = date("Y-m-d");
          $first_date = date('Y-m-01', strtotime($hari_ini));
          $end_date = date('Y-m-t', strtotime($hari_ini));
      }else if($cektglstok == 0){
          $hari_ini = date('Y-m-d',strtotime("-10 days"));
          $first_date = date('Y-m-01', strtotime($hari_ini));
          $end_date = date('Y-m-t', strtotime($hari_ini));
      }

        $getmonth = date('m', strtotime($hari_ini));

        $query = $this->db->query("SELECT SUM(qty_in) - SUM(qty_out) AS saldo_akhir FROM 
                                (
                                SELECT 'Saldo Awal' AS 'NoDoc','Saldo Awal' AS 'Dokumen',NULL TglDoc, NULL TimeDoc,KodeProduk AS Kode ,NamaProduk NamaProduk,
                                '' Unit,SUM(SAwal$getmonth) AS 'qty_in',0 AS 'qty_out', '' AS BatchNo,NULL ExpDate
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
