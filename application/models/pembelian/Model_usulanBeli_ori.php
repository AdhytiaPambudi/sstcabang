<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_usulanBeli extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->model('Model_main');
            $this->cabang = $this->session->userdata('cabang');
    }

    // PRODUK
    public function listProduk($supplier = NULL, $prinsipal = NULL)
    {   
        $query = $this->db->query("select Kode_Produk, Produk from mst_produk where Prinsipal = '".$prinsipal."' and  ( status is null or status in ('','Y')) ")->result();

        return $query;
    }

    public function getProdukUsulanBeli($kode = NULL)
    {
        $query = $this->db->query("select Produk, Satuan, Kategori from mst_produk where Kode_Produk='".$kode."'")->row(); 

        return $query;
    }

    // public function getitemusulaninfo($produk = NULL,$no = NULL)
    // {
    //     $query = $this->db->query("SELECT QTYPO,Disc FROM trs_delivery_order_detail WHERE NoDokumen = '".$no."' and Produk= '".$produk."'  AND Status = 'Open' limit 1")->row(); 

    //     return $query;
    // }

    public function getStokUsulanBeli($kode = NULL)
    {
        $query = $this->db->query("select KodeProduk,UnitStok,ValueStok from trs_invsum where KodeProduk='".$kode."' and Cabang ='".$this->session->userdata('cabang')."' and gudang='Baik' and tahun='".date('Y')."' Limit 1")->row(); 

        return $query;
    }

    public function getAMS3UsulanBeli($kode = NULL)
    {
       // if(date('t') != '31'){
       //    $tigabulan = date('Y-m-01', strtotime('-3 months'));
       //    $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
       //    $duabulan = date('Y-m-01', strtotime('-2 months'));
       //    $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
       //    $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
       //    $satubulan = date('Y-m-t', strtotime('-1 months'));
       //    $bulanini = date('Y-m-01');
       //    $bulanini_akhir = date('Y-m-t');
       //  }else{
       //    $tiga = date('Y-m-01',strtotime("-70 days"));
       //    $tigabulan = date('Y-m-01', strtotime($tiga));
       //    $tigabulan_akhir = date('Y-m-t', strtotime($tiga));
       //    $dua = date('Y-m-01',strtotime("-35 days"));
       //    $duabulan = date('Y-m-01', strtotime($dua));
       //    $duabulan_akhir = date('Y-m-t', strtotime($dua));
       //    $satu = date('Y-m-01',strtotime("-10 days"));
       //    $satubulan_awal = date('Y-m-01', strtotime($satu));
       //    $satubulan = date('Y-m-t', strtotime($satu));
       //    $bulanini = date('Y-m-01');
       //    $bulanini_akhir = date('Y-m-t');
       //  }
       //  // $query = $this->db->query("select KodeProduk,round((sum(ifnull(QtyFaktur,'') + ifnull(BonusFaktur,''))/3)) as avgjual
       //  //                             from trs_faktur_detail
       //  //                             where TglFaktur between '".$tigabulan."' and '".$satubulan."' and 
       //  //                                   KodeProduk = '".$kode."'
       //  //                             group by KodeProduk")->row(); 

       //  $query = $this->db->query("SELECT trs_invsum.`Cabang`,
       //                                        trs_invsum.`KodeProduk`,
       //                                        IFNULL(tigabulan.`qtyfaktur`,0) AS 'qtyfakturtigabulan',
       //                                        IFNULL(duabulan.`qtyfaktur`,0) AS 'qtyfakturduabulan',
       //                                        IFNULL(satubulan.`qtyfaktur`,0) AS 'qtyfaktursatubulan',
       //                                        (CASE WHEN IFNULL(tigabulan.`qtyfaktur`,0) = 0 AND IFNULL(duabulan.`qtyfaktur`,0) = 0 THEN IFNULL(satubulan.`qtyfaktur`,0)
       //                                              WHEN IFNULL(tigabulan.`qtyfaktur`,0) = 0 AND IFNULL(duabulan.`qtyfaktur`,0) > 0 THEN (IFNULL(satubulan.`qtyfaktur`,0) + IFNULL(duabulan.`qtyfaktur`,0))/2
       //                                              WHEN IFNULL(tigabulan.`qtyfaktur`,0) > 0 THEN (IFNULL(satubulan.`qtyfaktur`,0) + IFNULL(duabulan.`qtyfaktur`,0) + IFNULL(tigabulan.`qtyfaktur`,0))/3
       //                                              ELSE 0 END ) AS 'avgjual'
       //                                    FROM trs_invsum 
       //                                    LEFT JOIN  
       //                                    (SELECT trs_faktur_detail.`KodeProduk`,
       //                                            SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
       //                                                ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
       //                                       FROM trs_faktur_detail
       //                                       WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$tigabulan."' AND '".$tigabulan_akhir."' and trs_faktur_detail.`KodeProduk` ='".$kode."'
       //                                       GROUP BY trs_faktur_detail.`KodeProduk`) AS tigabulan ON tigabulan.KodeProduk = trs_invsum.`KodeProduk` 
       //                                    LEFT JOIN
       //                                    (SELECT trs_faktur_detail.`KodeProduk`,
       //                                            SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
       //                                  ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
       //                                       FROM trs_faktur_detail
       //                                      WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$duabulan."' AND '".$duabulan_akhir."' and trs_faktur_detail.`KodeProduk` ='".$kode."'
       //                                      GROUP BY trs_faktur_detail.`KodeProduk`) AS duabulan ON duabulan.KodeProduk = trs_invsum.`KodeProduk`
       //                                    LEFT JOIN
       //                                   (SELECT trs_faktur_detail.`KodeProduk`,
       //                                           SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
       //                                           ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
       //                                      FROM trs_faktur_detail
       //                                     WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$satubulan_awal."' AND '".$satubulan."' and trs_faktur_detail.`KodeProduk` ='".$kode."'
       //                                   GROUP BY trs_faktur_detail.`KodeProduk`) AS satubulan ON satubulan.KodeProduk = trs_invsum.`KodeProduk`
       //                                   WHERE trs_invsum.KodeProduk = '".$kode."' and 
       //                                         trs_invsum.tahun ='".date('Y')."'")->row(); 
        $query = $this->db->query("select ifnull(QtyAvgjual,0) as 'avgjual' from mst_data_ams3 where Cabang ='".$this->cabang."' and Produk ='".$kode."' limit 1")->row();
        return $query;
    }

    public function getGITUsulanBeli($kode = NULL)
    {
        if(date('t') != '31'){
          $tigabulan = date('Y-m-01', strtotime('-3 months'));
          $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
          $duabulan = date('Y-m-01', strtotime('-2 months'));
          $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
          $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
          $satubulan = date('Y-m-t', strtotime('-1 months'));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d');
          // $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
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
          $bulanini_akhir = date('Y-m-d');
          // $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }
        // $query = $this->db->query("select SUM(QtyGIT) AS QtyGIT 
        //     FROM (  select (SUM(Qty) + SUM(Bonus)) AS QtyGIT FROM `trs_po_detail` WHERE cabang='".$this->session->userdata('cabang')."' AND produk='".$kode."' AND status_po NOT IN ('Closed','Tolak') and Tgl_PO between '".$duabulan."' and '".$satubulan."' GROUP BY cabang,Produk)a")->row();

         $query = $this->db->query("select SUM(QtyGIT) AS QtyGIT 
                                       FROM ( SELECT produk,(CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 THEN IFNULL(Sisa_PO,'') ELSE (IFNULL(Qty_PO,'') + IFNULL(Bonus,'')) END) AS QtyGIT 
                                              FROM `trs_po_detail` left join mst_prinsipal
                                              on trs_po_detail.Prinsipal = mst_prinsipal.Prinsipal
                                              WHERE cabang='".$this->cabang."' AND 
                                                    ifnull(statusGIT,'') = 'Open' AND  
                                                    (case when mst_prinsipal.umur_GIT = '0' then trs_po_detail.`Tgl_PO` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '1' then trs_po_detail.`Tgl_PO` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                                     when mst_prinsipal.umur_GIT = '2' then trs_po_detail.`Tgl_PO` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' else
                                                      trs_po_detail.`Tgl_PO` <= '".$bulanini_akhir."'  end) and produk='".$kode."'
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
                                                trs_delivery_order_detail.`TglDokumen` <= '".$bulanini_akhir."' END) AND produk='".$kode."'
                                    ) AS sgit limit 1
                                       ")->row();
        return $query;
    }
    public function getNilaiBeliUsulanBeli($kode = NULL)
    {
      // $query = $this->db->query("SELECT SUM(vBeli) AS vBeli FROM (SELECT (SUM(Value_Usulan)) AS vBeli FROM `trs_usulan_beli_detail` WHERE cabang='".$this->session->userdata('cabang')."' AND Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND status_usulan NOT IN ('Tolak','Closed') AND MONTH(Tgl_Usulan) IN (".date('m').") AND YEAR(Tgl_Usulan) = ".date('Y')." GROUP BY cabang,Prinsipal UNION SELECT (SUM(Value_PR)) AS vBeli FROM `trs_pembelian_detail` WHERE cabang='".$this->session->userdata('cabang')."' AND  Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND status_pr NOT IN ('Tolak','Closed') AND MONTH(Tgl_PR) IN (".date('m').") AND YEAR(Tgl_PR) = ".date('Y')." GROUP BY cabang,Prinsipal UNION SELECT (SUM(Value_PO)) AS vBeli FROM `trs_po_detail` WHERE cabang='".$this->session->userdata('cabang')."' AND  Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND status_po NOT IN ('Tolak','Closed') AND MONTH(Tgl_PO) IN (".date('m').") AND YEAR(Tgl_PO) = ".date('Y')." GROUP BY cabang,Prinsipal)a")->row(); 
        $query = $this->db->query("SELECT SUM(vBeli) AS vBeli 
                                  FROM (SELECT (SUM(Value_Usulan)) AS vBeli 
                                         FROM `trs_usulan_beli_detail` 
                                         WHERE cabang='".$this->session->userdata('cabang')."' AND 
                                               Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND 
                                               trs_usulan_beli_detail.`Produk` ='".$kode."' AND 
                                               (status_usulan NOT IN ('Tolak','Closed','Batal') and 
                                             trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header)) AND 
                                               MONTH(Tgl_Usulan) IN (".date('m').") AND 
                                               YEAR(Tgl_Usulan) = ".date('Y')." 
                                               GROUP BY cabang,Prinsipal 
                                        UNION 
                                        SELECT (SUM(Value_PR)) AS vBeli 
                                        FROM `trs_pembelian_detail` 
                                        WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                            Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND 
                                            trs_pembelian_detail.`Produk`  = '".$kode."' AND
                                            (status_pr NOT IN ('Tolak','Closed','Batal') and 
                                                  trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header)) AND 
                                                 MONTH(Tgl_PR) IN (".date('m').") AND 
                                            MONTH(Tgl_PR) IN (".date('m').") AND 
                                            YEAR(Tgl_PR) = ".date('Y')." 
                                            GROUP BY cabang,Prinsipal 
                                        UNION 
                                        SELECT (SUM(Value_PO)) AS vBeli 
                                        FROM `trs_po_detail` 
                                        WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                              Prinsipal IN (SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') AND 
                                              trs_po_detail.`Produk` = '".$kode."' AND
                                              status_po NOT IN ('Tolak','Batal','Closed') AND 
                                              MONTH(Tgl_PO) IN (".date('m').") AND 
                                              YEAR(Tgl_PO) = ".date('Y')." 
                                        GROUP BY cabang,Prinsipal)a")->row(); 
        return $query;
    }

    public function getNilaiBeliUsulanBelibyPrins($prinsipal = NULL)
    {
       $query = $this->db->query("SELECT SUM(vBeli) AS vBeli
                                    FROM (SELECT (SUM(Value_Usulan)) AS vBeli 
                                          FROM `trs_usulan_beli_detail` 
                                          WHERE cabang='".$this->session->userdata('cabang')."' AND 
                                                Prinsipal IN ('".$prinsipal."') AND 
                                                (status_usulan NOT IN ('Tolak','Closed') and
                                             trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header)) AND 
                                                MONTH(Tgl_Usulan) IN (".date('m').") AND 
                                                YEAR(Tgl_Usulan) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                          UNION 
                                          SELECT (SUM(Value_PR)) AS vBeli 
                                           FROM `trs_pembelian_detail` 
                                           WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                                 Prinsipal IN ('".$prinsipal."') AND
                                                 (status_pr NOT IN ('Tolak','Closed','Batal') and 
                                                  trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header)) AND 
                                                 MONTH(Tgl_PR) IN (".date('m').") AND 
                                                 YEAR(Tgl_PR) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                         UNION 
                                         SELECT (SUM(Value_PO)) AS vBeli 
                                         FROM `trs_po_detail` 
                                         WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                           Prinsipal IN ('".$prinsipal."') AND 
                                           status_po NOT IN ('Tolak','Batal','Closed') AND 
                                           MONTH(Tgl_PO) IN (".date('m').") AND 
                                           YEAR(Tgl_PO) = ".date('Y')." 
                                         GROUP BY cabang,Prinsipal)a 
                                    ")->row(); 
        return $query;
    }

    public function getHargaUsulanBeli($kode = NULL)
    {        
        // $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'")->row(); 
        // if ($query == null) {
        //     $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang in (null,'')  ")->row(); 
        // }   

         $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA, Dsc_Cab,Dsc_Pri,Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'"); 
        $num_query = $query->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  ifnull(Cabang,'')='' limit 1")->row(); 
        }else{
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        } 

        return $query;
    }

    public function saveData($params = null, $name1 = null, $name2 = null)
    {        
            // +++++++++++++++++ Transfer file ke server via sftp  ++++++++++++++++++
            $dokumen = array();
            if(!empty($name1)){
                array_push($dokumen, $name1);
            }
            if(!empty($name2)){
                array_push($dokumen, $name2);
            }
            // $status_dokumen = false;

            foreach($dokumen as $doc){
                $localFile = getcwd().'/assets/dokumen/Prinsipal/'.$doc;
                $remoteFile='/var/www/html/sstpusat/assets/dokumen/Prinsipal/'.$doc;
                $d = $this->upload_file->sftp($localFile,$remoteFile);
            }
            // +++++++++++++++++akhir Transfer file ke server via sftp  ++++++++++++++++++

            $this->db2 = $this->load->database('pusat', TRUE);      
            $valid = false;
            $x = 1;
            $piutang = 0;
            $temp = [];
            $split = explode("-", $params->Cabang);
            $Cabang = $split[0];            

            $distinct = array_unique($params->Kategori);
            $distinct2 = array_unique($params->Prinsipal);
            foreach ($distinct2 as $kunci2 => $nilai2)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            { 
                $split2 = explode("~", $distinct2[$kunci2]);
                $Prinspl = $split2[0];

                foreach ($distinct as $kunci => $nilai)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                {                                                                    
                     
                    $total = 0;
                    $gross = 0;
                    $potongan = 0;
                    $ppn = 0;
                    $summary = 0;
                    foreach ($params->Produk as $key => $value) 
                    {
                        if ($params->Kategori[$key] == $distinct[$kunci] && $params->Prinsipal[$key] == $distinct2[$kunci2]) {  //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                            $expld = explode("~", $params->Produk[$key]);
                            $Produk = $expld[0];
                            $Nama_Produk = $expld[1];                              
                            $total = $total + $params->Value[$key];
                            $gross = $gross + $params->gross[$key];
                            $potongan = $potongan + $params->PotonganCab[$key];
                            $ppn = $ppn + $params->ppn[$key]; 
                            $summary = $summary + $params->total[$key];                               

                            $cek = $params->Prinsipal[$key].' - '.$params->Kategori[$key];
                            if (in_array($cek, $temp) == false) {                           
                            
                                $temp[$key] = $cek;
                                $noUsulan = $split[1]."/".$Prinspl."/".date('Y-m-d/H:i:s');
                                sleep(1);
                                if ($this->db2->conn_id == TRUE) {  
                                // START PROSES SAVE DATA PUSAT
                                    $this->db2->set("Tipe", $params->Kategori[$key]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                                    $this->db2->set("Cabang", $Cabang);
                                    $this->db2->set("Prinsipal", $Prinspl);
                                    $this->db2->set("Supplier", $params->Supplier[$key]);
                                    $this->db2->set("No_Usulan", $noUsulan);
                                    $this->db2->set("Value_Usulan", $total);
                                    $this->db2->set("gross", $gross);
                                    $this->db2->set("potongan", $potongan);
                                    $this->db2->set("ppn", $ppn);
                                    $this->db2->set("total", $summary);
                                    $this->db2->set("Status_Usulan", "Usulan");  
                                    $this->db2->set("Dokumen", $name1);
                                    $this->db2->set("Dokumen_2", $name2);
                                    $this->db2->set("Keperluan", $params->Keperluan);
                                    $this->db2->set("Nama_Kep", $params->NamaKep);
                                    $this->db2->set("Alamat_Kep", $params->AlamatKep);
                                    $this->db2->set("Limit_Beli", $params->SisaLimit);
                                    $this->db2->set("NoIDPaket", $params->idpaket);
                                    $this->db2->set("Pelanggan", $params->pelanggan);
                                    $this->db2->set("added_time", date('Y-m-d H:i:s'));
                                    $this->db2->set("added_user", $this->session->userdata('username'));
                                    $valid = $this->db2->insert('trs_usulan_beli_header'); 
                                // END PROSES SAVE DATA PUSAT
                                    $this->db->set("statusPusat", 'Berhasil'); //STATUS SAVE DATA KE SERVER PUSAT
                                }
                                else{
                                    $this->db->set("statusPusat", 'Gagal'); //STATUS SAVE DATA KE SERVER PUSAT  
                                }

                                $this->db->set("Tipe", $distinct[$kunci]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI                
                                $this->db->set("Cabang", $Cabang);
                                $this->db->set("Prinsipal", $Prinspl);
                                $this->db->set("Supplier", $params->Supplier[$key]);
                                $this->db->set("No_Usulan", $noUsulan);
                                $this->db->set("Value_Usulan", $total);
                                $this->db->set("gross", $gross);
                                $this->db->set("potongan", $potongan);
                                $this->db->set("ppn", $ppn);
                                $this->db->set("total", $summary);
                                $this->db->set("Status_Usulan", "Usulan");  
                                $this->db->set("Dokumen", $name1);
                                $this->db->set("Dokumen_2", $name2);
                                $this->db->set("Keperluan", $params->Keperluan);
                                $this->db->set("Nama_Kep", $params->NamaKep);
                                $this->db->set("Alamat_Kep", $params->AlamatKep);
                                $this->db->set("Limit_Beli", $params->SisaLimit);
                                $this->db->set("NoIDPaket", $params->idpaket);
                                $this->db->set("Pelanggan", $params->pelanggan);
                                $this->db->set("added_time", date('Y-m-d H:i:s'));
                                $this->db->set("added_user", $this->session->userdata('username'));
                                $valid = $this->db->insert('trs_usulan_beli_header');   
                            }
                            else{
                                if ($this->db2->conn_id == TRUE) {  
                                    $update = $this->db2->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");
                                }
                                $update = $this->db->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");                                
                            }

                            if ($this->db2->conn_id == TRUE) {        
                            // START PROSES SAVE DATA PUSAT
                                $this->db2->set("Cabang", $Cabang);
                                $this->db2->set("noline", $key+1);
                                $this->db2->set("Prinsipal", $Prinspl);
                                $this->db2->set("Supplier", $params->Supplier[$key]);
                                $this->db2->set("Kategori", $params->Kategori[$key]);
                                $this->db2->set("Produk", $Produk);
                                $this->db2->set("Nama_Produk", $Nama_Produk);
                                $this->db2->set("Satuan", $params->Satuan[$key]);
                                $this->db2->set("Keterangan", $params->Keterangan[$key]);
                                $this->db2->set("Penjelasan", $params->KetInternal[$key]);
                                $this->db2->set("No_Usulan", $noUsulan);
                                $this->db2->set("Value_Usulan", $params->Value[$key]);
                                // $this->db2->set("Status_PR", $params->Status);  
                                // $this->db2->set("Qty_PR", $params->Qty[$key]); 
                                $this->db2->set("Qty_Rec", $params->QtyRec[$key]); 
                                $this->db2->set("Avg", $params->Avg[$key]);
                                $this->db2->set("Indeks", $params->Indeks[$key]);
                                $this->db2->set("Stok", $params->Stok[$key]);
                                $this->db2->set("GIT", $params->GIT[$key]);
                                $this->db2->set("Disc_Deal", $params->Diskon[$key]);
                                $this->db2->set("Disc2", $params->Diskon[$key]);
                                $this->db2->set("Bonus", $params->Bonus[$key]);
                                $this->db2->set("Disc_Cab", $params->DiscCab[$key]);
                                $this->db2->set("Harga_Beli_Cab", $params->HBC[$key]);
                                $this->db2->set("Harga_Deal", $params->HargaDeal[$key]);            
                                $this->db2->set("HPC", $params->HPC[$key]);
                                $this->db2->set("Disc_Pst", $params->DscPst[$key]);
                                $this->db2->set("Harga_Beli_Pst", $params->HBP[$key]);
                                $this->db2->set("HPP", $params->HPP[$key]);

                                // Tambahan Usulan
                                $this->db2->set("gross", $params->gross[$key]);
                                $this->db2->set("ppn", $params->ppn[$key]);
                                $this->db2->set("total", $params->total[$key]);
                                $this->db2->set("val_disc", $params->val_disc[$key]);
                                $this->db2->set("val_bonus", $params->val_bonus[$key]);
                                $this->db2->set("Potongan_Cab", $params->PotonganCab[$key]); 
                                $this->db2->set("Qty", $params->Qty[$key]); 
                                $this->db2->set("Counter_Usulan", 0);
                                $this->db2->set("User_Usulan", $this->session->userdata('username'));
                                $this->db2->set("Tgl_Usulan", date('Y-m-d'));
                                $this->db2->set("Time_Usulan", date('Y-m-d H:i:s'));
                                $this->db2->set("Status_Usulan", "Usulan");
                                $this->db2->set("noline", $key+1);  
                                // end tambahan Usulan

                                $this->db2->set("Added_Time", date('Y-m-d H:i:s'));
                                $this->db2->set("Added_User", $this->session->userdata('username'));
                                $valid = $this->db2->insert('trs_usulan_beli_detail');
                            // END PROSES SAVE DATA PUSAT 
                            }
                            $this->db->set("Cabang", $Cabang);
                            $this->db->set("noline", $key);
                            $this->db->set("Prinsipal", $Prinspl);
                            $this->db->set("Supplier", $params->Supplier[$key]);
                            $this->db->set("Kategori", $params->Kategori[$key]);
                            $this->db->set("Produk", $Produk);
                            $this->db->set("Nama_Produk", $Nama_Produk);
                            $this->db->set("Satuan", $params->Satuan[$key]);
                            $this->db->set("Keterangan", $params->Keterangan[$key]);
                            $this->db->set("Penjelasan", $params->KetInternal[$key]);
                            $this->db->set("No_Usulan", $noUsulan);
                            $this->db->set("Value_Usulan", $params->Value[$key]);
                            // $this->db->set("Status_PR", $params->Status);  
                            // $this->db->set("Qty_PR", $params->Qty[$key]); 
                            $this->db->set("Qty_Rec", $params->QtyRec[$key]); 
                            $this->db->set("Avg", $params->Avg[$key]);
                            $this->db->set("Indeks", $params->Indeks[$key]);
                            $this->db->set("Stok", $params->Stok[$key]);
                            $this->db->set("GIT", $params->GIT[$key]);
                            $this->db->set("Disc_Deal", $params->Diskon[$key]);
                            $this->db->set("Disc2", $params->Diskon[$key]);
                            $this->db->set("Bonus", $params->Bonus[$key]);
                            $this->db->set("Disc_Cab", $params->DiscCab[$key]);
                            $this->db->set("Harga_Beli_Cab", $params->HBC[$key]);
                            $this->db->set("Harga_Deal", $params->HargaDeal[$key]);            
                            $this->db->set("HPC", $params->HPC[$key]);
                            $this->db->set("Disc_Pst", $params->DscPst[$key]);
                            $this->db->set("Harga_Beli_Pst", $params->HBP[$key]);
                            $this->db->set("HPP", $params->HPP[$key]);

                            // Tambahan Usulan
                            $this->db->set("gross", $params->gross[$key]);
                            $this->db->set("ppn", $params->ppn[$key]);
                            $this->db->set("total", $params->total[$key]);
                            $this->db->set("val_disc", $params->val_disc[$key]);
                            $this->db->set("val_bonus", $params->val_bonus[$key]);
                            $this->db->set("Potongan_Cab", $params->PotonganCab[$key]); 
                            $this->db->set("Qty", $params->Qty[$key]); 
                            $this->db->set("Counter_Usulan", 0);
                            $this->db->set("User_Usulan", $this->session->userdata('username'));
                            $this->db->set("Tgl_Usulan", date('Y-m-d'));
                            $this->db->set("Time_Usulan", date('Y-m-d H:i:s'));
                            $this->db->set("Status_Usulan", "Usulan");
                            $this->db->set("noline", $key+1);
                            // end tambahan Usulan

                            $this->db->set("Added_Time", date('Y-m-d H:i:s'));
                            $this->db->set("Added_User", $this->session->userdata('username'));
                            $valid = $this->db->insert('trs_usulan_beli_detail');    
                        }
                    } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI  
                    
                } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            }
        
        return true;
    }

    public function getLimit($cab = NULL)
    {
        $query = $this->db->query("select Limit_Beli as `limit` from mst_limit_pembelian where Cabang ='".$cab."' limit 1")->row(); 

        return $query;
    }

    public function getLimitPrins($kode = NULL)
    {
        $bln = date('m');
        $query = $this->db->query("select Limit_Beli as xlimit from mst_limit_pembelian where Cabang ='".$this->session->userdata('cabang')."' and MONTH(Bulan)=".date('m')." AND YEAR(Bulan)=".date('Y')." and SUBSTRING_INDEX(Prinsipal,'~',1)=(SELECT Prinsipal FROM mst_produk WHERE Kode_Produk='".$kode."') limit 1")->row(); 
        return $query;
    }

    public function getLimitPrinsbyprins($prinsipal = NULL)
    {
        $bln = date('m');
        $thn = date("Y");
        $query = $this->db->query("select Limit_Beli as xlimit from mst_limit_pembelian where Cabang ='".$this->session->userdata('cabang')."' and Prinsipal ='".$prinsipal."' and MONTH(Bulan)='".$bln."' and YEAR(Bulan)='".$thn."' limit 1")->row(); 
        return $query;
    }

    public function getTotalPO($cab = NULL)
    {
        $query = $this->db->query("select sum(Value_PO) as po from trs_po_detail where Cabang = '".$cab."' and Status_PO !='Batal' group by Cabang")->row(); 

        return $query;
    }

    public function listData($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and No_Usulan = '".$no."'";
        }
        $query = $this->db->query("select No_Usulan as noUsulan, date(added_time) as 'Tgl',Prinsipal, Supplier, Value_Usulan, Status_Usulan as status, statusPusat from trs_usulan_beli_header where Cabang = '".$this->session->userdata('cabang')."' $byID order by added_time");

        return $query;
    }

    public function listDataOut($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and No_Usulan = '".$no."'";
        }
        $query = $this->db->query("select No_Usulan as noUsulan, date(added_time) as 'Tgl',Prinsipal, Supplier, Value_Usulan, Status_Usulan as status, statusPusat from trs_usulan_beli_header where Cabang = '".$this->session->userdata('cabang')."' $byID and Status_Usulan not in ('Tolak','Closed') order by added_time Desc");

        return $query;
    }

    public function dataUsulan($no = NULL)
    {
        $query = $this->db->query("SELECT trs_usulan_beli_detail.* ,
                                           App_APJ_Cabang,
                                           user_apj_cabang,
                                           App_APJC_Time,
                                           App_BM_Status,
                                           user_bm,
                                           App_BM_Time,
                                           App_RBM_Status,
                                           user_rbm,
                                           App_RBM_Time,
                                           App_APJ_Pst_status,
                                           user_APJ_pusat,
                                           App_APJ_Pst_Time,
                                           App_pusat_status,
                                           user_App_pusat,
                                           App_pusat_Time
                                    FROM trs_usulan_beli_detail left join trs_usulan_beli_header
                                    ON   trs_usulan_beli_detail.`Cabang` = trs_usulan_beli_header.`Cabang` AND 
                                         trs_usulan_beli_detail.`No_Usulan` = trs_usulan_beli_header.`No_Usulan` 
                                    where trs_usulan_beli_header.No_Usulan = '".$no."' and trs_usulan_beli_header.Cabang = '".$this->session->userdata('cabang')."'")->result();

        return $query;
    }  

    public function listProdukOrderMonitor($prinsipal = NULL,$ltime=null)
    {
        if(date('t') != '31'){
          $tigabulan = date('Y-m-01', strtotime('-3 months'));
          $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
          $duabulan = date('Y-m-01', strtotime('-2 months'));
          $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
          $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
          $satubulan = date('Y-m-t', strtotime('-1 months'));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d');
          // $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
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
          $bulanini_akhir = date('Y-m-d');
          // $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }
        $query = $this->db->query("select distinct mst_produk.*,
                                           UnitStok,
                                           ValueStok,
                                           QtyGIT,
                                           round(ifnull(avgjual,0),2) as 'avgjual',
                                           round(((UnitStok+ifnull(QtyGIT,0))/ifnull(avgjual,0)),2) as 'rasio',
                                           round(((UnitStok/avgjual)*30),2) as 'umur_stok',
                                           round(0.75 + ('".$ltime."'/30),2) as 'SL',
                                           round(((0.75 + ('".$ltime."'/30)) - ((UnitStok+ifnull(QtyGIT,0))/ifnull(avgjual,0))) * ifnull(avgjual,0),2) as 'pesan'
                                    from mst_produk 
                                    left join 
                                    (select KodeProduk,
                                            ifnull(UnitStok,0) as 'UnitStok',
                                            ValueStok 
                                    from trs_invsum 
                                    where Cabang ='".$this->session->userdata('cabang')."' and tahun='".date('Y')."' and
                                          gudang='Baik') as stok on stok.KodeProduk = mst_produk.Kode_Produk  
                                    left join
                                    (SELECT sgit.produk,SUM(IFNULL(sgit.QtyGIT,0)) AS QtyGIT 
                                       FROM ( SELECT produk,(CASE WHEN IFNULL(trs_po_detail.Sisa_PO,0) > 0 THEN IFNULL(Sisa_PO,'') ELSE (IFNULL(Qty_PO,'') + IFNULL(Bonus,'')) END) AS QtyGIT 
                                              FROM `trs_po_detail` left join mst_prinsipal
                                              on trs_po_detail.Prinsipal = mst_prinsipal.Prinsipal
                                              WHERE cabang='".$this->cabang."' AND trs_po_detail.Prinsipal ='".$prinsipal."' AND 
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
                                          trs_delivery_order_detail.Prinsipal ='".$prinsipal."' AND 
                                          (CASE WHEN mst_prinsipal.umur_GIT = '0' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$bulanini."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '1' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$satubulan_awal."' AND '".$bulanini_akhir."'
                                           WHEN mst_prinsipal.umur_GIT = '2' THEN trs_delivery_order_detail.`TglDokumen` BETWEEN '".$duabulan."' AND '".$bulanini_akhir."' ELSE
                                                trs_delivery_order_detail.`TglDokumen` <= '".$bulanini_akhir."' END)) AS sgit
                                       GROUP BY sgit.produk) as GIT on GIT.produk = mst_produk.Kode_Produk 
                                    left join
                                    (select Produk,ifnull(QtyAvgjual,0) as 'avgjual' from mst_data_ams3 where Prinsipal = '".$prinsipal."') AS ams3 on ams3.Produk = mst_produk.Kode_Produk
                                    where mst_produk.prinsipal = '".$prinsipal."'
                                    having (UnitStok) > 0 or (QtyGIT) > 0 or (avgjual) > 0
                                    order by pesan Desc,mst_produk.Produk Asc")->result();

        return $query;
    }     

    public function prosesData($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_usulan_beli_header set statusPusat = 'Berhasil' where No_Usulan = '".$no."'");

            $query = $this->db->query("select * from trs_usulan_beli_detail where No_Usulan = '".$no."'")->result();
            $cek = $this->db2->query("select * from trs_usulan_beli_detail where No_Usulan = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_usulan_beli_detail', $r); // insert each row to another table
                }
            }
            else{
                foreach($query as $r) { // loop over results
                    $this->db2->where('Produk', $r->Produk);
                    $this->db2->where('No_Usulan', $no);
                    $this->db2->where('Status_Usulan', 'Usulan');
                    $this->db2->update('trs_usulan_beli_detail', $r);
                }
            }

            $query2 = $this->db->query("select * from trs_usulan_beli_header where No_Usulan = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_usulan_beli_header where No_Usulan = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_usulan_beli_header', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('No_Usulan', $no);
                $this->db2->where('Status_Usulan', 'Usulan');
                $this->db2->update('trs_usulan_beli_header', $query2);
            }

            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    public function updateDataPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
        // JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
            // $cek = $this->db->query("select No_Usulan from trs_usulan_beli_header where statusPusat = 'Gagal' and Cabang = '".$this->session->userdata('cabang')."'")->num_rows();
            // if ($cek > 0) {
            //     return 'CEK';
            // }
            // else{
                $nomor = $this->db2->query("select No_Usulan from trs_usulan_beli_header where Cabang = '".$this->session->userdata('cabang')."' and Status_Usulan not in ('Usulan')")->result();
                foreach ($nomor as $no) {
                    // $query = $this->db2->query("select * from trs_usulan_beli_detail where No_Usulan = '".$no->No_Usulan."'")->result();
                    // foreach($query as $r) { // loop over results
                    //     $this->db->set('Status_Usulan', $r->No_Usulan);
                    //     $this->db->where('No_Usulan', $no->No_Usulan);
                    //     $this->db->update('trs_usulan_beli_detail', $r); // insert each row to another table
                    // }
                    $query2 = $this->db2->query("select * from trs_usulan_beli_header where No_Usulan = '".$no->No_Usulan."' and Status_Usulan ='Closed' limit 1")->row();
                    $this->db->set('Status_Usulan', $query2->Status_Usulan);
                    $this->db->where('No_Usulan', $no->No_Usulan);
                    $this->db->where('statusPusat', 'Berhasil');
                    $this->db->update('trs_usulan_beli_header'); // insert each row to another table
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function saveDataByPrinsipal($params = null, $name1 = null, $name2 = null)
    {
        // +++++++++++++++++ Transfer file ke server via sftp  ++++++++++++++++++
            $dokumen = array();
            if(!empty($name1)){
                array_push($dokumen, $name1);
            }
            if(!empty($name2)){
                array_push($dokumen, $name2);
            }
            // $status_dokumen = false;

            foreach($dokumen as $doc){
                $localFile = getcwd().'/assets/dokumen/Prinsipal/'.$doc;
                $remoteFile='/var/www/html/sstpusat/assets/dokumen/Prinsipal/'.$doc;
                $d = $this->upload_file->sftp($localFile,$remoteFile);
            }
            // +++++++++++++++++akhir Transfer file ke server via sftp  ++++++++++++++++++
            
        $this->db2 = $this->load->database('pusat', TRUE);      
        $valid = false;
        $x = 1;
        $piutang = 0;
        $temp = [];
        $split = explode("-", $params->Cabang);
        $Cabang = $split[0];
        $split2 = explode("~", $params->Prinsipal);
        $Prinspl = $split2[0];
        $total = 0;
        $gross = 0;
        $potongan = 0;
        $ppn = 0;
        $summary = 0;
        $distinct = array_unique($params->Kategori);
            // $distinct2 = array_unique($params->Prinsipal);
            // foreach ($distinct2 as $kunci2 => $nilai2)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            // { 
            //     $split2 = explode("~", $distinct2[$kunci2]);
            //     $Prinspl = $split2[0];
                foreach ($distinct as $kunci => $nilai)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                {                                                                    
                     
                    $total = 0;
                    foreach ($params->Produk as $key => $value) 
                    {
                        if ($params->Kategori[$key] == $distinct[$kunci]) {  //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                            $expld = explode("~", $params->Produk[$key]);
                            $Produk = $expld[0];
                            $Nama_Produk = $expld[1];                              
                            $total = $total + $params->Value[$key];  
                            $gross = $gross + $params->gross[$key];
                            $potongan = $potongan + $params->PotonganCab[$key];
                            $ppn = $ppn + $params->ppn[$key]; 
                            $summary = $summary + $params->total[$key];                             

                            $cek = $params->Prinsipal.' - '.$params->Kategori[$key];
                            if (in_array($cek, $temp) == false) {                           
                            
                                $temp[$key] = $cek;
                                $noUsulan = $split[1]."/".$Prinspl."/".date('Y-m-d/H:i:s');
                                sleep(1);
                                if ($this->db2->conn_id == TRUE) {  
                                // START PROSES SAVE DATA PUSAT
                                    $this->db2->set("Tipe", $params->Kategori[$key]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                                    $this->db2->set("Cabang", $Cabang);
                                    $this->db2->set("Prinsipal", $Prinspl);
                                    $this->db2->set("Supplier", $params->Supplier);
                                    $this->db2->set("No_Usulan", $noUsulan);
                                    $this->db2->set("Value_Usulan", $total);
                                    $this->db2->set("gross", $gross);
                                    $this->db2->set("potongan", $potongan);
                                    $this->db2->set("ppn", $ppn);
                                    $this->db2->set("total", $summary);
                                    $this->db2->set("Status_Usulan", "Usulan");  
                                    $this->db2->set("Dokumen", $name1);
                                    $this->db2->set("Dokumen_2", $name2);
                                    $this->db2->set("Keperluan", $params->Keperluan);
                                    $this->db2->set("Nama_Kep", $params->NamaKep);
                                    $this->db2->set("Alamat_Kep", $params->AlamatKep);
                                    $this->db2->set("Limit_Beli", $params->SisaLimit);
                                    $this->db2->set("NoIDPaket", $params->idpaket);
                                    $this->db2->set("Pelanggan", $params->pelanggan);
                                    $this->db2->set("added_time", date('Y-m-d H:i:s'));
                                    $this->db2->set("added_user", $this->session->userdata('username'));
                                    $valid = $this->db2->insert('trs_usulan_beli_header'); 
                                // END PROSES SAVE DATA PUSAT
                                    $this->db->set("statusPusat", 'Berhasil'); //STATUS SAVE DATA KE SERVER PUSAT
                                }
                                else{
                                    $this->db->set("statusPusat", 'Gagal'); //STATUS SAVE DATA KE SERVER PUSAT  
                                }

                                $this->db->set("Tipe", $distinct[$kunci]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI                
                                $this->db->set("Cabang", $Cabang);
                                $this->db->set("Prinsipal", $Prinspl);
                                $this->db->set("Supplier", $params->Supplier);
                                $this->db->set("No_Usulan", $noUsulan);
                                $this->db->set("Value_Usulan", $total);
                                $this->db->set("gross", $gross);
                                $this->db->set("potongan", $potongan);
                                $this->db->set("ppn", $ppn);
                                $this->db->set("total", $summary);
                                $this->db->set("Status_Usulan", "Usulan");  
                                $this->db->set("Dokumen", $name1);
                                $this->db->set("Dokumen_2", $name2);
                                $this->db->set("Keperluan", $params->Keperluan);
                                $this->db->set("Nama_Kep", $params->NamaKep);
                                $this->db->set("Alamat_Kep", $params->AlamatKep);
                                $this->db->set("Limit_Beli", $params->SisaLimit);
                                $this->db->set("NoIDPaket", $params->idpaket);
                                $this->db->set("Pelanggan", $params->pelanggan);
                                $this->db->set("added_time", date('Y-m-d H:i:s'));
                                $this->db->set("added_user", $this->session->userdata('username'));
                                $valid = $this->db->insert('trs_usulan_beli_header');   
                            }
                            else{
                                if ($this->db2->conn_id == TRUE) {  
                                    $update = $this->db2->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");
                                }
                                $update = $this->db->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");                              
                            }


                            if ($this->db2->conn_id == TRUE) {        
                            // START PROSES SAVE DATA PUSAT
                                $this->db2->set("Cabang", $Cabang);
                                $this->db2->set("noline", $key+1);
                                $this->db2->set("Prinsipal", $Prinspl);
                                $this->db2->set("Supplier", $params->Supplier);
                                $this->db2->set("Kategori", $params->Kategori[$key]);
                                $this->db2->set("Produk", $Produk);
                                $this->db2->set("Nama_Produk", $Nama_Produk);
                                $this->db2->set("Satuan", $params->Satuan[$key]);
                                $this->db2->set("Keterangan", $params->Keterangan[$key]);
                                $this->db2->set("Penjelasan", $params->KetInternal[$key]);
                                $this->db2->set("No_Usulan", $noUsulan);
                                $this->db2->set("Value_Usulan", $params->Value[$key]);
                                // $this->db2->set("Status_PR", $params->Status);  
                                // $this->db2->set("Qty_PR", $params->Qty[$key]); 
                                $this->db2->set("Qty_Rec", $params->QtyRec[$key]); 
                                $this->db2->set("Avg", $params->Avg[$key]);
                                $this->db2->set("Indeks", $params->Indeks[$key]);
                                $this->db2->set("Stok", $params->Stok[$key]);
                                $this->db2->set("GIT", $params->GIT[$key]);
                                $this->db2->set("Disc_Deal", $params->Diskon[$key]);
                                $this->db2->set("Disc2", $params->Diskon[$key]);
                                $this->db2->set("Bonus", $params->Bonus[$key]);
                                $this->db2->set("Disc_Cab", $params->DiscCab[$key]);
                                $this->db2->set("Harga_Beli_Cab", $params->HBC[$key]);
                                $this->db2->set("Harga_Deal", $params->HargaDeal[$key]);            
                                $this->db2->set("HPC", $params->HPC[$key]);
                                $this->db2->set("Disc_Pst", $params->DscPst[$key]);
                                $this->db2->set("Harga_Beli_Pst", $params->HBP[$key]);
                                $this->db2->set("HPP", $params->HPP[$key]);

                                // Tambahan Usulan
                                $this->db2->set("gross", $params->gross[$key]);
                                $this->db2->set("ppn", $params->ppn[$key]);
                                $this->db2->set("total", $params->total[$key]);
                                $this->db2->set("val_disc", $params->val_disc[$key]);
                                $this->db2->set("val_bonus", $params->val_bonus[$key]);
                                $this->db2->set("Potongan_Cab", $params->PotonganCab[$key]); 
                                $this->db2->set("Qty", $params->Qty[$key]); 
                                $this->db2->set("Counter_Usulan", 0);
                                $this->db2->set("User_Usulan", $this->session->userdata('username'));
                                $this->db2->set("Tgl_Usulan", date('Y-m-d'));
                                $this->db2->set("Time_Usulan", date('Y-m-d H:i:s'));
                                $this->db2->set("Status_Usulan", "Usulan");
                                $this->db2->set("noline", $key+1);  
                                // end tambahan Usulan

                                $this->db2->set("Added_Time", date('Y-m-d H:i:s'));
                                $this->db2->set("Added_User", $this->session->userdata('username'));
                                $valid = $this->db2->insert('trs_usulan_beli_detail');
                            // END PROSES SAVE DATA PUSAT 
                            }
                            $this->db->set("Cabang", $Cabang);
                            $this->db->set("noline", $key);
                            $this->db->set("Prinsipal", $Prinspl);
                            $this->db->set("Supplier", $params->Supplier);
                            $this->db->set("Kategori", $params->Kategori[$key]);
                            $this->db->set("Produk", $Produk);
                            $this->db->set("Nama_Produk", $Nama_Produk);
                            $this->db->set("Satuan", $params->Satuan[$key]);
                            $this->db->set("Keterangan", $params->Keterangan[$key]);
                            $this->db->set("Penjelasan", $params->KetInternal[$key]);
                            $this->db->set("No_Usulan", $noUsulan);
                            $this->db->set("Value_Usulan", $params->Value[$key]);
                            // $this->db->set("Status_PR", $params->Status);  
                            // $this->db->set("Qty_PR", $params->Qty[$key]); 
                            $this->db->set("Qty_Rec", $params->QtyRec[$key]); 
                            $this->db->set("Avg", $params->Avg[$key]);
                            $this->db->set("Indeks", $params->Indeks[$key]);
                            $this->db->set("Stok", $params->Stok[$key]);
                            $this->db->set("GIT", $params->GIT[$key]);
                            $this->db->set("Disc_Deal", $params->Diskon[$key]);
                            $this->db->set("Disc2", $params->Diskon[$key]);
                            $this->db->set("Bonus", $params->Bonus[$key]);
                            $this->db->set("Disc_Cab", $params->DiscCab[$key]);
                            $this->db->set("Harga_Beli_Cab", $params->HBC[$key]);
                            $this->db->set("Harga_Deal", $params->HargaDeal[$key]);            
                            $this->db->set("HPC", $params->HPC[$key]);
                            $this->db->set("Disc_Pst", $params->DscPst[$key]);
                            $this->db->set("Harga_Beli_Pst", $params->HBP[$key]);
                            $this->db->set("HPP", $params->HPP[$key]);

                            // Tambahan Usulan
                            $this->db->set("gross", $params->gross[$key]);
                            $this->db->set("ppn", $params->ppn[$key]);
                            $this->db->set("total", $params->total[$key]);
                            $this->db->set("val_disc", $params->val_disc[$key]);
                            $this->db->set("val_bonus", $params->val_bonus[$key]);
                            $this->db->set("Potongan_Cab", $params->PotonganCab[$key]); 
                            $this->db->set("Qty", $params->Qty[$key]); 
                            $this->db->set("Counter_Usulan", 0);
                            $this->db->set("User_Usulan", $this->session->userdata('username'));
                            $this->db->set("Tgl_Usulan", date('Y-m-d'));
                            $this->db->set("Time_Usulan", date('Y-m-d H:i:s'));
                            $this->db->set("Status_Usulan", "Usulan");
                            $this->db->set("noline", $key+1);
                            // end tambahan Usulan

                            $this->db->set("Added_Time", date('Y-m-d H:i:s'));
                            $this->db->set("Added_User", $this->session->userdata('username'));
                            $valid = $this->db->insert('trs_usulan_beli_detail');    
                        }
                    } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI  
                    
                } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            // }
        
        return true;
    } 

    public function saveDataUsulanBeliBonusPrinsipal($params = null, $name1 = null, $name2 = null)
    {
        // +++++++++++++++++ Transfer file ke server via sftp  ++++++++++++++++++
            $dokumen = array();
            if(!empty($name1)){
                array_push($dokumen, $name1);
            }
            if(!empty($name2)){
                array_push($dokumen, $name2);
            }
            // $status_dokumen = false;

            foreach($dokumen as $doc){
                $localFile = getcwd().'/assets/dokumen/Prinsipal/'.$doc;
                $remoteFile='/var/www/html/sstpusat/assets/dokumen/Prinsipal/'.$doc;
                $d = $this->upload_file->sftp($localFile,$remoteFile);
            }
            // +++++++++++++++++akhir Transfer file ke server via sftp  ++++++++++++++++++
            
        $this->db2 = $this->load->database('pusat', TRUE);      
        $valid = false;
        $x = 1;
        $piutang = 0;
        $temp = [];
        $split = explode("-", $params->Cabang);
        $Cabang = $split[0];
        $split2 = explode("~", $params->Prinsipal);
        $Prinspl = $split2[0];
        
        $distinct = array_unique($params->Kategori);
            // $distinct2 = array_unique($params->Prinsipal);
            // foreach ($distinct2 as $kunci2 => $nilai2)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            // { 
            //     $split2 = explode("~", $distinct2[$kunci2]);
            //     $Prinspl = $split2[0];
                foreach ($distinct as $kunci => $nilai)              //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                {                                                                    
                     
                    $total = 0;
                    $gross = 0;
                    $potongan = 0;
                    $ppn = 0;
                    $summary = 0;
                    foreach ($params->Produk as $key => $value) 
                    {
                        if ($params->Kategori[$key] == $distinct[$kunci]) {  //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                            $expld = explode("~", $params->Produk[$key]);
                            $Produk = $expld[0];
                            $Nama_Produk = $expld[1];                                                             
                            $total = $total + $params->Value[$key];  
                            $gross = $gross + $params->gross[$key];
                            $potongan = $potongan + $params->PotonganCab[$key];
                            $ppn = $ppn + $params->ppn[$key]; 
                            $summary = $summary + $params->total[$key]; 
                            $cek = $params->Prinsipal.' - '.$params->Kategori[$key];
                            if (in_array($cek, $temp) == false) {                           
                            
                                $temp[$key] = $cek;
                                $noUsulan = $split[1]."/BNS/".$Prinspl."/".date('Y-m-d/H:i:s');
                                sleep(1);
                                if ($this->db2->conn_id == TRUE) {  
                                // START PROSES SAVE DATA PUSAT
                                    $this->db2->set("Tipe", $params->Kategori[$key]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                                    $this->db2->set("Cabang", $Cabang);
                                    $this->db2->set("Prinsipal", $Prinspl);
                                    $this->db2->set("Supplier", $params->Supplier);
                                    $this->db2->set("No_Usulan", $noUsulan);
                                    $this->db2->set("Value_Usulan", $total);
                                    $this->db2->set("gross", $gross);
                                    $this->db2->set("potongan", $potongan);
                                    $this->db2->set("ppn", $ppn);
                                    $this->db2->set("total", $summary);
                                    $this->db2->set("Status_Usulan", "Usulan");  
                                    $this->db2->set("Dokumen", $name1);
                                    $this->db2->set("Dokumen_2", $name2);
                                    $this->db2->set("Keperluan", $params->Keperluan);
                                    $this->db2->set("Nama_Kep", $params->NamaKep);
                                    $this->db2->set("Alamat_Kep", $params->AlamatKep);
                                    $this->db2->set("Limit_Beli", $params->SisaLimit);
                                    $this->db2->set("NoIDPaket", $params->idpaket);
                                    $this->db2->set("Pelanggan", $params->pelanggan);
                                    $this->db2->set("added_time", date('Y-m-d H:i:s'));
                                    $this->db2->set("added_user", $this->session->userdata('username'));
                                    $valid = $this->db2->insert('trs_usulan_beli_header'); 
                                // END PROSES SAVE DATA PUSAT
                                    $this->db->set("statusPusat", 'Berhasil'); //STATUS SAVE DATA KE SERVER PUSAT
                                }
                                else{
                                    $this->db->set("statusPusat", 'Gagal'); //STATUS SAVE DATA KE SERVER PUSAT  
                                }

                                $this->db->set("Tipe", $distinct[$kunci]); //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI                
                                $this->db->set("Cabang", $Cabang);
                                $this->db->set("Prinsipal", $Prinspl);
                                $this->db->set("Supplier", $params->Supplier);
                                $this->db->set("No_Usulan", $noUsulan);
                                $this->db->set("Value_Usulan", $total);
                                $this->db->set("gross", $gross);
                                $this->db->set("potongan", $potongan);
                                $this->db->set("ppn", $ppn);
                                $this->db->set("total", $summary);
                                $this->db->set("Status_Usulan", "Usulan");  
                                $this->db->set("Dokumen", $name1);
                                $this->db->set("Dokumen_2", $name2);
                                $this->db->set("Keperluan", $params->Keperluan);
                                $this->db->set("Nama_Kep", $params->NamaKep);
                                $this->db->set("Alamat_Kep", $params->AlamatKep);
                                $this->db->set("Limit_Beli", $params->SisaLimit);
                                $this->db->set("NoIDPaket", $params->idpaket);
                                $this->db->set("Pelanggan", $params->pelanggan);
                                $this->db->set("added_time", date('Y-m-d H:i:s'));
                                $this->db->set("added_user", $this->session->userdata('username'));
                                $valid = $this->db->insert('trs_usulan_beli_header');   
                            }
                            else{
                                if ($this->db2->conn_id == TRUE) {  
                                    $update = $this->db2->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");
                                }
                                $update = $this->db->query("update trs_usulan_beli_header set Value_Usulan = Value_Usulan+".$params->Value[$key].",gross = gross+".$params->gross[$key].",total = total+".$params->total[$key].",potongan = potongan+".$params->PotonganCab[$key].",ppn = ppn+".$params->ppn[$key]." where No_Usulan = '".$noUsulan."'");                                
                            }


                            if ($this->db2->conn_id == TRUE) {        
                            // START PROSES SAVE DATA PUSAT
                                $this->db2->set("Cabang", $Cabang);
                                $this->db2->set("noline", $key+1);
                                $this->db2->set("Prinsipal", $Prinspl);
                                $this->db2->set("Supplier", $params->Supplier);
                                $this->db2->set("Kategori", $params->Kategori[$key]);
                                $this->db2->set("Produk", $Produk);
                                $this->db2->set("Nama_Produk", $Nama_Produk);
                                $this->db2->set("Satuan", $params->Satuan[$key]);
                                $this->db2->set("Keterangan", $params->Keterangan[$key]);
                                $this->db2->set("Penjelasan", $params->KetInternal[$key]);
                                $this->db2->set("No_Usulan", $noUsulan);
                                $this->db2->set("Value_Usulan", $params->Value[$key]);
                                // $this->db2->set("Status_PR", $params->Status);  
                                // $this->db2->set("Qty_PR", $params->Qty[$key]); 
                                $this->db2->set("Qty_Rec", $params->QtyRec[$key]); 
                                $this->db2->set("Avg", $params->Avg[$key]);
                                $this->db2->set("Indeks", $params->Indeks[$key]);
                                $this->db2->set("Stok", $params->Stok[$key]);
                                $this->db2->set("GIT", $params->GIT[$key]);
                                $this->db2->set("Disc_Deal", $params->Diskon[$key]);
                                $this->db2->set("Disc2", $params->Diskon[$key]);
                                $this->db2->set("Bonus", $params->Bonus[$key]);
                                $this->db2->set("Disc_Cab", $params->DiscCab[$key]);
                                $this->db2->set("Harga_Beli_Cab", $params->HBC[$key]);
                                $this->db2->set("Harga_Deal", $params->HargaDeal[$key]);            
                                $this->db2->set("HPC", $params->HPC[$key]);
                                $this->db2->set("Disc_Pst", $params->DscPst[$key]);
                                $this->db2->set("Harga_Beli_Pst", $params->HBP[$key]);
                                $this->db2->set("HPP", $params->HPP[$key]);

                                // Tambahan Usulan
                                $this->db2->set("gross", $params->gross[$key]);
                                $this->db2->set("ppn", $params->ppn[$key]);
                                $this->db2->set("total", $params->total[$key]);
                                $this->db2->set("val_disc", $params->val_disc[$key]);
                                $this->db2->set("val_bonus", $params->val_bonus[$key]);
                                $this->db2->set("Potongan_Cab", $params->PotonganCab[$key]); 
                                $this->db2->set("Qty", $params->Qty[$key]); 
                                $this->db2->set("Counter_Usulan", 0);
                                $this->db2->set("User_Usulan", $this->session->userdata('username'));
                                $this->db2->set("Tgl_Usulan", date('Y-m-d'));
                                $this->db2->set("Time_Usulan", date('Y-m-d H:i:s'));
                                $this->db2->set("Status_Usulan", "Usulan");
                                $this->db2->set("noline", $key+1);  
                                // end tambahan Usulan

                                $this->db2->set("Added_Time", date('Y-m-d H:i:s'));
                                $this->db2->set("Added_User", $this->session->userdata('username'));
                                $valid = $this->db2->insert('trs_usulan_beli_detail');
                            // END PROSES SAVE DATA PUSAT 
                            }
                            $this->db->set("Cabang", $Cabang);
                            $this->db->set("noline", $key);
                            $this->db->set("Prinsipal", $Prinspl);
                            $this->db->set("Supplier", $params->Supplier);
                            $this->db->set("Kategori", $params->Kategori[$key]);
                            $this->db->set("Produk", $Produk);
                            $this->db->set("Nama_Produk", $Nama_Produk);
                            $this->db->set("Satuan", $params->Satuan[$key]);
                            $this->db->set("Keterangan", $params->Keterangan[$key]);
                            $this->db->set("Penjelasan", $params->KetInternal[$key]);
                            $this->db->set("No_Usulan", $noUsulan);
                            $this->db->set("Value_Usulan", $params->Value[$key]);
                            // $this->db->set("Status_PR", $params->Status);  
                            // $this->db->set("Qty_PR", $params->Qty[$key]); 
                            $this->db->set("Qty_Rec", $params->QtyRec[$key]); 
                            $this->db->set("Avg", $params->Avg[$key]);
                            $this->db->set("Indeks", $params->Indeks[$key]);
                            $this->db->set("Stok", $params->Stok[$key]);
                            $this->db->set("GIT", $params->GIT[$key]);
                            $this->db->set("Disc_Deal", $params->Diskon[$key]);
                            $this->db->set("Disc2", $params->Diskon[$key]);
                            $this->db->set("Bonus", $params->Bonus[$key]);
                            $this->db->set("Disc_Cab", $params->DiscCab[$key]);
                            $this->db->set("Harga_Beli_Cab", $params->HBC[$key]);
                            $this->db->set("Harga_Deal", $params->HargaDeal[$key]);            
                            $this->db->set("HPC", $params->HPC[$key]);
                            $this->db->set("Disc_Pst", $params->DscPst[$key]);
                            $this->db->set("Harga_Beli_Pst", $params->HBP[$key]);
                            $this->db->set("HPP", $params->HPP[$key]);

                            // Tambahan Usulan
                            $this->db->set("gross", $params->gross[$key]);
                            $this->db->set("ppn", $params->ppn[$key]);
                            $this->db->set("total", $params->total[$key]);
                            $this->db->set("val_disc", $params->val_disc[$key]);
                            $this->db->set("val_bonus", $params->val_bonus[$key]);
                            $this->db->set("Potongan_Cab", $params->PotonganCab[$key]); 
                            $this->db->set("Qty", $params->Qty[$key]); 
                            $this->db->set("Counter_Usulan", 0);
                            $this->db->set("User_Usulan", $this->session->userdata('username'));
                            $this->db->set("Tgl_Usulan", date('Y-m-d'));
                            $this->db->set("Time_Usulan", date('Y-m-d H:i:s'));
                            $this->db->set("Status_Usulan", "Usulan");
                            $this->db->set("noline", $key+1);
                            // end tambahan Usulan

                            $this->db->set("Added_Time", date('Y-m-d H:i:s'));
                            $this->db->set("Added_User", $this->session->userdata('username'));
                            $valid = $this->db->insert('trs_usulan_beli_detail');    
                        }
                    } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI  
                    
                } //JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
            // }
        
        return true;
    } 

    //Data DO pusat
    public function load_datadopusat($search=null, $limit=null, $status=null){
        $header = $this->db->query("select * from trs_delivery_order_header where Cabang='".$this->cabang."' $search $limit");
        return $header;
    }
    public function detaildopusat($no){
        $header = $this->db->query("select * from trs_delivery_order_header where NoDokumen='".$no."'")->result();
        $detail = $this->db->query("select * from trs_delivery_order_detail where NoDokumen='".$no."'")->result();
        $data = [
            "header" => $header,
            "detail" => $detail
        ];
        return $data;
    }

    public function updatedataDOpusat(){
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $doheader = $this->db2->query("select * from trs_delivery_order_header where Cabang='".$this->cabang."' and Status= 'Open'")->result();
            foreach($doheader as $r) {
                $cekpo = $this->db->query("select * from trs_po_header where No_PO = '".$r->NoPO."' and Cabang='".$this->cabang."'")->num_rows();
                // if ($cekpo > 0) {
                    $cekdo = $this->db->query("select * from trs_delivery_order_header where NoDokumen = '".$r->NoDokumen."' and Cabang='".$this->cabang."'")->num_rows();
                    // ==== BACA DETAIL ====
                    $dodetail = $this->db2->query("select * from trs_delivery_order_detail where Cabang='".$this->cabang."' and NoDokumen = '".$r->NoDokumen."'")->result();
                    if($cekdo==0){
                        $this->db->insert('trs_delivery_order_header', $r); 
                        $NoBPB = (!empty($r->NoBPB) ? $r->NoBPB : "");
                        if($NoBPB != "" and $r->flag_suratjalan == "N"){
                          $cekbpb = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$r->NoBPB."' and Cabang='".$this->cabang."' and status ='Pending' limit 1")->num_rows();
                          if($cekbpb > 0 ){
                            $this->db->set("Gross", $r->Gross);
                            $this->db->set("Potongan", $r->Potongan);
                            $this->db->set("Value", $r->Value);
                            $this->db->set("PPN", $r->PPN);
                            $this->db->set("Total", $r->Total);
                            $this->db->set("TimeEdit", date('Y-m-d H:i:s'));
                            $this->db->set("UserEdit", $this->session->userdata('username'));
                            $this->db->set("Status", "Close");
                            $this->db->set("TglDokumen", date('Y-m-d'));
                            $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                            $this->db->where("Cabang", $this->cabang);
                            $this->db->where("NoDokumen", $r->NoBPB);
                            $this->db->update('trs_terima_barang_header');
                          } 
                        }
                        foreach($dodetail as $r2) {
                            $this->db->insert('trs_delivery_order_detail', $r2);
                            if($r->flag_suratjalan == "N" and $cekbpb > 0){
                              $this->db->set("Qty", $r2->Qty);
                              $this->db->set("Bonus", $r2->Bonus);
                              $this->db->set("Banyak", $r2->Banyak);
                              $this->db->set("Disc", $r2->Disc);
                              $this->db->set("HrgBeli", $r2->HrgBeli);
                              $this->db->set("ExpDate", $r2->ExpDate);
                              $this->db->set("HPC", $r2->HPC);
                              $this->db->set("HPC1", $r2->HPC1);
                              $this->db->set("Harga_Beli_Pst", $r2->Harga_Beli_Pst);
                              $this->db->set("HPP", $r2->HPP);
                              $this->db->set("Disc_Pst", $r2->Disc_Pst);
                              $this->db->set("Gross", $r2->Gross);
                              $this->db->set("Potongan", $r2->Potongan);
                              $this->db->set("Value", $r2->Value);
                              $this->db->set("PPN", $r2->PPN);
                              $this->db->set("Total", $r2->Total);
                              $this->db->set("TimeEdit", date('Y-m-d H:i:s'));
                              $this->db->set("UserEdit", $this->session->userdata('username'));
                              $this->db->set("Status", "Close");
                              $this->db->set("TglDokumen", date('Y-m-d'));
                              $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                              $this->db->where("Cabang", $this->cabang);
                              $this->db->where("NoDokumen",$r->NoBPB);
                              $this->db->where("Produk", $r2->Produk);
                              $this->db->where("BatchNo", $r2->BatchNo);
                              $this->db->update('trs_terima_barang_detail');
                              $this->setStok($r2);
                              // Update DO pusat
                              $this->db2->set("Status", "Close");
                              $this->db2->where("NoDokumen", $r->NoDokumen);
                              $this->db2->update("trs_delivery_order_detail");
                              //update do_cabang
                              $this->db->set("Status", "Close");
                              $this->db->where("NoDokumen", $r->NoDokumen);
                              $this->db->update("trs_delivery_order_detail");
                              
                            }
                        }
                        // // Update Status BPB Cabang
                        // $this->db->set("Status", "Close");
                        // $this->db->set("TglDokumen", date('Y-m-d'));
                        // $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                        // $this->db->where("NoDokumen", $r->NoBPB);
                        // $this->db->update("trs_terima_barang_header");
                        // // Update Status BPB Cabang detail
                        // $this->db->set("Status", "Close");
                        // $this->db->set("TglDokumen", date('Y-m-d'));
                        // $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                        // $this->db->where("NoDokumen", $r->NoBPB);
                        // $this->db->update("trs_terima_barang_detail");
                        
                        /// Update DO pusat
                        $this->db2->set("Status", "Close");
                        $this->db2->where("NoDokumen", $r->NoDokumen);
                        $this->db2->update("trs_delivery_order_header");
                        //update do_cabang
                        $this->db->set("Status", "Close");
                        $this->db->where("NoDokumen", $r->NoDokumen);
                        $this->db->update("trs_delivery_order_header");
                    }
                // }
            }
            return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

    public function setStok($params = NULL)
    {
        // log_message('error',print_r($params,true));
        // return;
        $Kode_Counter = explode("/", $params->NoDokumen);
        $valuestok = $params->Qty * $params->HPC;
        $jml_qty = $params->Banyak;
        // ============================== Update Gudang Baik ============================
        $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$params->Produk."' and Cabang='".$params->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
        if ($invsum->num_rows() > 0) {
            $invsum = $invsum->row();
            $UnitStok = $invsum->UnitStok + $jml_qty;
            $valuestok2 = $UnitStok * $params->HPC;
            $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.",  ValueStok = ".$valuestok2." where KodeProduk='".$params->Produk."' and Cabang='".$params->Cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
        }
        else{
            $this->db->set("Tahun", date('Y'));
            $this->db->set("Cabang", $params->Cabang);
            $this->db->set("KodePrinsipal", $Kode_Counter[2]);
            $this->db->set("NamaPrinsipal", $params->Prinsipal);
            $this->db->set("Pabrik", $params->Pabrik);
            $this->db->set("KodeProduk", $params->Produk);
            $this->db->set("NamaProduk", $params->NamaProduk);
            $this->db->set("UnitStok", $jml_qty);
            $this->db->set("ValueStok", $valuestok);
            $this->db->set("Gudang", 'Baik');
            $this->db->set("indeks", '0.000');
            $this->db->set("UnitCOGS", $params->HPC);
            $this->db->set("HNA", '0.000');
            $this->db->insert('trs_invsum');
            // $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$params->Cabang."', '".$Kode_Counter[2]."', '".$params->Prinsipal."', '".$params->Pabrik."', '".$params->Produk."', '".$params->NamaProduk."', '".$jml_qty."', '".$valuestok."', 'Baik', '0.000', '".$params->HPC."', '0.000')");

        }
        // ==================== save inventori history ==============
        $this->db->set("Tahun", date('Y'));
        $this->db->set("Cabang",$params->Cabang);
        $this->db->set("KodePrinsipal",$Kode_Counter[2]);
        $this->db->set("NamaPrinsipal",$params->Prinsipal);
        $this->db->set("Pabrik",$params->Pabrik);
        $this->db->set("KodeProduk",$params->Produk);
        $this->db->set("NamaProduk",$params->NamaProduk);
        $this->db->set("UnitStok",$jml_qty);
        $this->db->set("ValueStok",$valuestok);
        $this->db->set("BatchNo",$params->BatchNo);
        $this->db->set("ExpDate",$params->ExpDate);
        $this->db->set("Gudang",'Baik');
        $this->db->set("Tipe",'BPB');
        $this->db->set("NoDokumen",$params->NoBPB);
        $this->db->set("Keterangan",'-');
        $this->db->insert('trs_invhis');

        // $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$params->Cabang."', '".$Kode_Counter[2]."', '".$params->Prinsipal."', '".$params->Pabrik."', '".$params->Produk."', '".$params->NamaProduk."', '".$jml_qty."', '".$valuestok."', '".$params->BatchNo."', '".$params->ExpDate."', 'Baik', 'BPB', '".$params->NoBPB."', '-')");


        // $stok = 0;
        // $UnitStok = $params->Qty;
        // $cogs = $params->HPC;
        // $ValueStok = $cogs * $UnitStok;
        //======= insert ke gudang Baik detail Cabang =====================
        $this->db->set("Tahun", date('Y'));
        $this->db->set("KodePrinsipal", $Kode_Counter[2]);
        $this->db->set("NamaPrinsipal", $params->Prinsipal);
        $this->db->set("Pabrik", $params->Pabrik);
        $this->db->set("UnitStok", $jml_qty);
        $this->db->set("ValueStok", $valuestok);
        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
        $this->db->set("ModifiedUser", $this->session->userdata('username'));
        $this->db->set("KodeProduk", $params->Produk);
        $this->db->set("NamaProduk", $params->NamaProduk);
        $this->db->set("Cabang", $params->Cabang);
        $this->db->set("Gudang", 'Baik');
        $this->db->set("NoDokumen", $params->NoBPB);
        $this->db->set("TanggalDokumen", date('Y-m-d'));
        $this->db->set("BatchNo", $params->BatchNo);
        $this->db->set("ExpDate", $params->ExpDate);
        $this->db->set("UnitCOGS",$params->HPC);
        $this->db->insert('trs_invdet');

        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$params->Produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
        if($invdet->num_rows() <= 0){
            $this->db->set("ValueStok", 0);
            $this->db->where("KodeProduk", $params->Produk);
            $this->db->where("Gudang", 'Baik');
            $this->db->where("Tahun", date('Y'));
            $this->db->where("Cabang",$this->cabang);
            $valid = $this->db->update('trs_invsum');
        }else{
            $invdet = $invdet->row();
            $this->db->set("ValueStok", $invdet->sumval);
            // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
            $this->db->where("KodeProduk", $params->Produk);
            $this->db->where("Gudang", 'Baik');
            $this->db->where("Tahun", date('Y'));
            $this->db->where("Cabang",$this->cabang);
            $valid = $this->db->update('trs_invsum');
        }
        $this->db->set("statusGIT", "Closed");
        $this->db->where("No_PO", $params->NoPO);
        $this->db->where("Produk", $params->Produk);
        $this->db->update('trs_po_detail');
    }

    public function viewusulanbelipusat($no=null){
      $this->db2 = $this->load->database('pusat', TRUE);
        $header = $this->db2->query("SELECT trs_usulan_beli_header.`Prinsipal`,
                                           trs_usulan_beli_header.`No_Usulan`,
                                           trs_usulan_beli_header.`App_BM_Status`,
                                           trs_usulan_beli_header.`App_RBM_Status`,
                                           trs_usulan_beli_header.`App_APJ_Pst_status`,
                                           pr.No_PR,
                                           pr.Tgl_PR,
                                           po.No_PO,
                                           po.Tgl_PO
                                    FROM trs_usulan_beli_header LEFT JOIN
                                         (SELECT trs_pembelian_header.No_PR,
                                           trs_pembelian_header.`Tgl_PR`,
                                           trs_pembelian_header.`No_Usulan`
                                           FROM trs_pembelian_header) AS pr
                                         ON pr.No_Usulan =  trs_usulan_beli_header.`No_Usulan` 
                                    LEFT JOIN (SELECT trs_po_header.`No_PO`,
                                                      trs_po_header.`Tgl_PO`,
                                                      trs_po_header.`No_Usulan`
                                                FROM trs_po_header ) AS po 
                                           ON po.No_Usulan =  trs_usulan_beli_header.`No_Usulan` 
                                    WHERE trs_usulan_beli_header.no_usulan ='".$no."';")->result();
        return $header;
    }

    public function listOustandinglimit($prinsipal = NULL)
    {
        $query = $this->db->query("SELECT No_Usulan AS 'NoDokumen',
                                           Tgl_Usulan AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'Usulan Beli' as 'Tipe',
                                           CONCAT(Produk,'~',Nama_Produk) as 'Produk',
                                           (Qty+Bonus) as 'Qty',
                                           IFNULL(Value_Usulan,0) AS 'valbeli'
                                    FROM `trs_usulan_beli_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND 
                                          Prinsipal IN ('".$prinsipal."') AND 
                                          (status_usulan NOT IN ('Tolak','Closed','Batal') and 
                                          trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header)) AND 
                                          MONTH(Tgl_Usulan) IN ('".date('m')."') AND 
                                          YEAR(Tgl_Usulan) = '".date('Y')."' 
                                    
                                    UNION ALL                                               
                                    SELECT No_PR AS 'NoDokumen',
                                           Tgl_PR AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'PR' as 'Tipe',
                                           CONCAT(Produk,'~',Nama_Produk) as 'Produk',
                                           (Qty+Bonus) as 'Qty',
                                           IFNULL(Value_PR,0) AS 'valbeli'
                                    FROM `trs_pembelian_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                          Prinsipal IN ('".$prinsipal."') AND 
                                         (status_pr NOT IN ('Tolak','Closed','Batal') and 
                                          trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header)) AND 
                                          MONTH(Tgl_PR) IN ('".date('m')."') AND 
                                          YEAR(Tgl_PR) = '".date('Y')."' 
                                    
                                    UNION ALL                                        
                                    SELECT No_PO AS 'NoDokumen',
                                           Tgl_PO AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'PO' as 'Tipe',
                                           CONCAT(Produk,'~',Nama_Produk) as 'Produk',
                                           (Qty+Bonus) as 'Qty',
                                           IFNULL(Value_PO,0) AS 'valbeli'
                                    FROM `trs_po_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                         Prinsipal IN ('".$prinsipal."') AND 
                                         ifnull(statusGIT,'') = 'Open' AND 
                                         MONTH(Tgl_PO) IN ('".date('m')."') AND 
                                         YEAR(Tgl_PO) = '".date('Y')."';")->result();
        $query1 = $this->db->query("select sum(ifnull(valbeli,0)) as 'totalbeli' from (SELECT No_Usulan AS 'NoDokumen',
                                           Tgl_Usulan AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'Usulan Beli' as 'Tipe',
                                           SUM(IFNULL(Value_Usulan,0)) AS 'valbeli'
                                    FROM `trs_usulan_beli_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND 
                                          Prinsipal IN ('".$prinsipal."') AND 
                                          (status_usulan NOT IN ('Tolak','Closed','Batal') and 
                                          trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header)) AND 
                                          MONTH(Tgl_Usulan) IN ('".date('m')."') AND 
                                          YEAR(Tgl_Usulan) = '".date('Y')."' 
                                    GROUP BY No_Usulan,
                                           Tgl_Usulan,tipe,
                                           Prinsipal
                                    UNION ALL                                               
                                    SELECT No_PR AS 'NoDokumen',
                                           Tgl_PR AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'PR' as 'Tipe',
                                           SUM(IFNULL(Value_PR,0)) AS 'valbeli'
                                    FROM `trs_pembelian_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                          Prinsipal IN ('".$prinsipal."') AND 
                                         (status_pr NOT IN ('Tolak','Closed','Batal') and 
                                          trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header)) AND 
                                          MONTH(Tgl_PR) IN ('".date('m')."') AND 
                                          YEAR(Tgl_PR) = '".date('Y')."' 
                                    GROUP BY No_PR,Tgl_PR,tipe,Prinsipal
                                    UNION ALL                                        
                                    SELECT No_PO AS 'NoDokumen',
                                           Tgl_PO AS 'Tgl',
                                           Prinsipal AS 'Prinsipal',
                                           'PO' as 'Tipe',
                                           SUM(IFNULL(Value_PO,0)) AS 'valbeli'
                                    FROM `trs_po_detail` 
                                    WHERE cabang='".$this->session->userdata('cabang')."' AND  
                                         Prinsipal IN ('".$prinsipal."') AND 
                                         ifnull(statusGIT,'') = 'Open' AND 
                                         MONTH(Tgl_PO) IN ('".date('m')."') AND 
                                         YEAR(Tgl_PO) = '".date('Y')."'
                                    GROUP BY No_PO,Tgl_PO,tipe,Prinsipal)a;")->result();

        $output = array(
              "list" => $query,
              "num" => $query1
           );
        return $output;
    }  
}