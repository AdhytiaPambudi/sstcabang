<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");
class Model_order extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }

    // Pelanggan
    public function Pelanggan()
    {   
        $query = $this->db->query("select * from mst_pelanggan where Cabang = '".$this->cabang."' AND ifnull(Aktif,'') in ('Y','') AND (Nama_Faktur != '' AND Nama_Faktur IS NOT NULL AND Nama_Faktur != '-') AND (IFNULL(Tipe_2,'') = 'Y' AND IFNULL(Tipe_Pelanggan,'') = 'AP'  ) OR (IFNULL(Tipe_2,'') <> 'Y' AND IFNULL(Tipe_Pelanggan,'') <> 'APMT'  AND ifnull(Aktif,'') in ('Y',''))  ORDER BY Nama_Faktur, Kode")->result();

        return $query;
    }

    // Pelanggan ALL
    public function Pelanggan_all()
    {   
        $query = $this->db->query("select * from mst_pelanggan where Cabang = '".$this->cabang."' AND ifnull(Aktif,'') in ('Y','') AND (Nama_Faktur != '' AND Nama_Faktur IS NOT NULL AND Nama_Faktur != '-')   ORDER BY Nama_Faktur, Kode")->result();

        return $query;
    }

    // Pelanggan
    public function PelangganSP()
    {   
        $query = $this->db->query("select * from mst_pelanggan where Cabang = '".$this->cabang."' AND ifnull(Aktif,'') in ('Y','') AND (Nama_Faktur != '' AND Nama_Faktur IS NOT NULL AND Nama_Faktur != '-')  ORDER BY Nama_Faktur, Kode")->result();

        return $query;
    }

    // Salesman
    public function Salesman()
    {   
        $query = $this->db->query("select Kode, Nama, Jabatan from mst_karyawan where Cabang = '".$this->cabang."'  and Status = 'Aktif' and jabatan='Salesman' group by Kode order by Nama asc")->result();

        return $query;
    }

    // Kode Lama Cabang
    public function kodeLamaCabang()
    {   
        $query = $this->db->query("select Kode_Lama from mst_cabang where Cabang = '".$this->cabang."' limit 1")->row();
        $query = $query->Kode_Lama;
        return $query;
    }

    // Kode Dokumen
    public function kodeDokumen($dok = NULL)
    {   
        $query = $this->db->query("select Kode from mst_kode_dokumen where Dokumen = '".$dok."' limit 1")->row();
        $query = (!empty($query->Kode))?$query->Kode:'';
        return $query;
    }

    // Counter
    public function counter($apl = NULL)
    {   
        $query = $this->db->query("select Counter from mst_counter where Aplikasi = '".$apl."' and Cabang = '".$this->cabang."' limit 1")->row();
        return $query;
    }

    //Cara Bayar
    public function caraBayar()
    {   
        $query = $this->db->query("select * from mst_cara_bayar")->result();
        return $query;
    }

    // Produk
    public function Produk()
    {   
        // INVSUM
        $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk from trs_invsum where Cabang = '".$this->cabang."' and Gudang = 'Baik' order by NamaProduk asc")->result();
        // MST PRODUK
        // $query = $this->db->query("select Kode_Produk, Produk from mst_produk order by Produk asc")->result();

        return $query;
    }

    // Produk
    public function listProdukorder($tipe=NULL)
    {   
        $tipe = $this->db->query("select distinct Dot from mst_tipepelanggan where Tipe='".$tipe."' LIMIT 1")->result();
        $tipe = $tipe[0]->Dot;
        // INVSUM
        if($tipe == "Merah"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum left join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2','3','4')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Biru"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum left join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2','3')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Hijau"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum left join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1','2')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Jamu"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum left join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0','1')
                 order by NamaProduk asc")->result();
        }elseif($tipe == "Non Dot"){
            $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk,flag_dot from trs_invsum left join mst_produk on mst_produk.Kode_Produk = trs_invsum.KodeProduk
                 where Cabang = '".$this->cabang."' and Gudang = 'Baik' and trs_invsum.tahun ='".date('Y')."' and
                       flag_dot in ('0')
                 order by NamaProduk asc")->result();
        }
        // $query = $this->db->query("select KodeProduk as Kode_Produk, NamaProduk as Produk from trs_invsum where Cabang = '".$this->cabang."' and Gudang = 'Baik' order by NamaProduk asc")->result();
        // MST PRODUK
        // $query = $this->db->query("select Kode_Produk, Produk from mst_produk order by Produk asc")->result();
        // log_message("error",print_r($query,true));
        return $query;
    }

    // Data Pelanggan
    public function dataPelangganNew($kode = NULL)
    {   
        $query = $this->db->query("SELECT * FROM mst_pelanggan  WHERE Kode = '".$kode."' AND Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }
    
    public function dataPelanggan($kode = NULL)
    {   
        //$query = $this->db->query("select * from mst_pelanggan a , (select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan) b where a.Kode = '".$kode."' and a.Cabang = '".$this->cabang."' limit 1")->row();
        $query = $this->db->query("SELECT * FROM mst_pelanggan a LEFT JOIN (SELECT SUM(saldo) AS saldopiutang,Pelanggan,Cabang FROM trs_faktur WHERE Pelanggan = '".$kode."' AND Cabang = '".$this->cabang."' GROUP BY cabang,pelanggan) b ON a.Kode=b.Pelanggan AND a.`Cabang`=b.Cabang LEFT JOIN (SELECT SUM(total) AS saldoorder,Pelanggan,Cabang FROM trs_sales_order WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS ='Usulan' GROUP BY cabang,pelanggan) c ON a.Kode=c.Pelanggan AND a.`Cabang`=c.Cabang LEFT JOIN (SELECT SUM(total) AS saldoDlvorder,Pelanggan,Cabang FROM trs_delivery_order_sales WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS IN ('Open','Kirim') GROUP BY cabang,pelanggan) d ON a.Kode=d.Pelanggan AND a.`Cabang`=d.Cabang WHERE a.Kode = '".$kode."' AND a.Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

    // Data Piutang
    public function dataPelanggangPiutang($kode = NULL)
    {   
        $query = $this->db->query("select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan limit 1")->row();

        return $query;
    }

    // Data Salesman
    public function dataSalesman($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_karyawan where Kode = '".$kode."' and Cabang = '".$this->cabang."' and Jabatan = 'Salesman' and Status = 'Aktif' limit 1")->row();

        return $query;
    }

    public function AllBatch($kode = NULL)
    {   
        // $query = $this->db->query("select sum(ifnull(UnitStok,0)) as 'UnitStok' from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and Gudang = 'Baik' and Tahun ='".date('Y')."' limit 1")->result();
        $query = $this->db->query("SELECT (CASE WHEN SUM(IFNULL(trs_invdet.UnitStok,0)) > IFNULL(invsum.UnitStok,0) THEN 0 ELSE SUM(IFNULL(trs_invdet.UnitStok,0)) END ) AS 'UnitStok'
                                    FROM trs_invdet left join (SELECT KodeProduk,IFNULL(UnitStok,0) AS 'UnitStok',Cabang,Gudang,Tahun
                                     FROM trs_invsum ) AS invsum 
                                     ON invsum.KodeProduk = trs_invdet.`KodeProduk` AND 
                                        invsum.Cabang = trs_invdet.`Cabang` AND
                                        invsum.Gudang = trs_invdet.`Gudang` AND
                                        invsum.Tahun = trs_invdet.`Tahun` 
                                    WHERE trs_invdet.KodeProduk = '".$kode."' AND 
                                    trs_invdet.Cabang = '".$this->cabang."' AND 
                                    trs_invdet.Gudang = 'Baik' AND 
                                    trs_invdet.Tahun ='".date('Y')."' LIMIT 1;")->result();

        return $query;
    }

    // Batch No
    public function Batch($kode = NULL)
    {   
        $query = $this->db->query("select BatchNo, UnitStok, ExpDate, NoDokumen as NoBPB,UnitCOGS as 'COGS' from trs_invdet where KodeProduk = '".$kode."' and Cabang = '".$this->cabang."' and UnitStok > 0 and Gudang = 'Baik' and Tahun ='".date('Y')."' AND NoDokumen NOT LIKE '%BKB%' order by ExpDate,BatchNo,NoDokumen  asc")->result();

        return $query;
    }

    // Harga Produk
    public function getProdukBuatOrder($kode = NULL)
    {   
        // $query = $this->db->query("select HNA, 
        //                                   Dsc_Cab, 
        //                                   Dsc_Pri 
        //                            from mst_harga 
        //                            where Produk = '".$kode."' and 
        //                                 Cabang = (case when ifnull(Cabang,'') != '' then '".$this->cabang."' else '' end) limit 1")->row();
        $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Prinsipal, Dsc_Cab,Dsc_Pri,Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'"); 
        $num_query = $query->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab,Prinsipal, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  ifnull(Cabang,'')='' limit 1")->row(); 
        }else{
            $query = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab,Prinsipal, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
        } 
        return $query;
    }

    // Satuan Produk
    public function getprodukorder($kode = NULL,$NODO=NULL)
    {   
        $query = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$NODO."' and KodeProduk='".$kode."'")->row();
        return $query;
    }

    // Satuan Produk
    public function getSatuanBuatOrder($kode = NULL)
    {   
        /*$query = $this->db->query("select ifnull(a.Satuan,'') as 'Satuan', b.UnitStok, b.UnitCOGS from mst_produk a, trs_invsum b where a.Kode_Produk = '".$kode."' and a.`Kode_Produk` = b.`KodeProduk` and b.Gudang = 'Baik' and b.tahun='".date('Y')."' limit 1")->row();*/

        $query = $this->db->query("select ifnull(a.Satuan,'') as 'Satuan', b.UnitStok, b.UnitCOGS,IFNULL(c.flag_prins_onf,'N') flag_prins_onf from mst_produk a LEFT JOIN trs_invsum b
        ON  a.`Kode_Produk` = b.`KodeProduk` 
        LEFT JOIN mst_prinsipal c ON c.Prinsipal = a.`Prinsipal`
        where a.Kode_Produk = '".$kode."'  and b.Gudang = 'Baik' and b.tahun='".date('Y')."' limit 1")->row();
        
        return $query;
    }

    
    public function listapproval($pelanggan = null)
    {   
        // tambahan kriteria, hanyaFaktur dan Retur Saja untuk perhitungan TOP Limit
        // ACC Pa Kuntoro
        // UJ 07Agustus2019
        $query = $this->db->query("SELECT Pelanggan,
                                              (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                                             ifnull(DATEDIFF(CURDATE(),trs_faktur.TglFaktur),'') AS 'Umur_faktur'
                                        FROM trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.KodePelanggan,
                                            SUM(trs_pelunasan_giro_detail.ValuePelunasan) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         WHERE STATUS = 'Open' 
                                         GROUP BY trs_pelunasan_giro_detail.KodePelanggan) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.Pelanggan
                                        WHERE ((trs_faktur.Status in ( 'Open','OpenDIH','Giro') AND IFNULL(trs_faktur.Status,'') NOT LIKE '%je%') OR trs_faktur.Saldo!= 0)
                                        AND Pelanggan = '$pelanggan' 
                                            AND (trs_faktur.TipeDokumen IN ('Faktur','Retur') OR trs_faktur.NoFaktur LIKE 'INV%' OR trs_faktur.NoFaktur LIKE 'RTF%')
                                         GROUP BY Pelanggan;")->row();

        return $query;
    }
    public function saveDataSalesOrder($params = null)
    {
        $dataPelanggan = $this->dataPelangganNew($params->pelanggan);
        $dataSalesman = $this->dataSalesman($params->salesman);   
        $dataTOPLimit = $this->listapproval($params->pelanggan);
        $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

        $status = "Usulan";
        $statusTOP = "Ok";
        $statusLimit = "Ok";
        $statusDiscCab = "Ok";
        $statusDiscPrins = "Ok";
        $do = true;
        $totalPiutang = $params->total + $params->piutang;
        $xPelanggan = explode("-", $params->pelanggan);
        $Pelanggan = $xPelanggan[0];
        $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
        $datax = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status in ('Open','OpenDIH') and Saldo != 0 order by TglFaktur desc")->result();

        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('SO');
        $year = date('Y');
        $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13 and YEAR(tglso) ='".$year."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //==== end of running number ============//

        //Di Ganti Dengan Usulan Limit TOP Pelanggan
        if($dataTOPLimit != ""){
            if ($dataTOPLimit->Umur_faktur > $params->top) {
                $status = "Usulan";
                $statusTOP = "TOP";
                $do = false;

                if ($params->tipeso != 'Salesman') {
                    
                    
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Dokumen", "Sales Order");
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("Status", "TOP");
                    $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_approval");
                }
                // break;
            }
        }

        if ($params->limit < $totalPiutang) {
            $status = "Usulan";
            $statusLimit = "Limit";
            $do = false;
            if ($params->tipeso != 'Salesman') {
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "Limit");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
            }
        } 

        foreach ($params->produk as $key => $value)
        {
            $dscprins = $params->dscprins1[$key] + $params->dscprins2[$key];
            if ($params->maksdsccab[$key] < $params->dsccab[$key]) {
                $status = "Usulan";
                $statusDiscCab = "DC";
                $do = false;
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "DC");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
                break;
            }

            if($params->Prinsipal[$key] == 'NUTRINDO' || substr($params->Prinsipal[$key],0,5) == 'CORSA' || $params->Prinsipal[$key] == 'MERSI' || $params->Prinsipal[$key] == 'ALTA WRAP'){
                    // $status = "Usulan";
                    // $statusDiscPrins = "Ok";
                    // $do = false;
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Dokumen", "Sales Order");
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("Status", "Ok");
                    $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_approval");
            }else{
                if ($params->maksdscprins[$key] < $dscprins) {
                    $status = "Usulan";
                    $statusDiscPrins = "DP";
                    $do = false;
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Dokumen", "Sales Order");
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("Status", "DP");
                    $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                    $valid =  $this->db->insert("trs_approval");
                    break;
                }
            }
        } 

        $carabayar = $params->carabayar;
        if($carabayar == "Cash"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+7 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "Kredit"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+21 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO60"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+60 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO75"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+75 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO90"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+90 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO120"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+120 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO150"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+150 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO180"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+180 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "B2B"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+1000 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO240"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+240 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO300"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+300 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "konsinyasi"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+60 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "COD"){
            $tgljto = date('Y-m-d H:i:s');
            // $curr_date = strtotime($curr_date);
            // $tgl_jto   = strtotime("+180 day", $curr_date);
            // $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }
        $xbid = explode("-", $params->acu2);
        $bid = $xbid[0];
        //save header  
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("NoSO", $noDokumen);
        $this->db->set("TglSO", $params->tgl);
        $this->db->set("TimeSO", date('Y-m-d H:i:s'));
        $this->db->set("Pelanggan", $params->pelanggan);
        $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
        $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
        $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
        $this->db->set("NamaTipePelanggan", "");
        $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
        $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
        $this->db->set("Acu", $params->acu);
        $this->db->set("acu2", $bid);
        $this->db->set("CaraBayar", $params->carabayar);
        $this->db->set("ppn_pelanggan", $params->flag_ppn);
        $this->db->set("CashDiskon", $params->cashdiskon);
        $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
        $this->db->set("TOP", $dataPelanggan->TOP);
        $this->db->set("TglJtoOrder", $tgljto);
        $this->db->set("Salesman", $dataSalesman->Kode);
        $this->db->set("NamaSalesman", $dataSalesman->Nama);
        $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
        $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
        $this->db->set("Status", $status);
        if ($params->tipeso == 'Salesman') {
            $this->db->set("StatusTOP", 'Ok');
            $this->db->set("StatusLimit", 'Ok');
        }else{
            $this->db->set("StatusTOP", $statusTOP);
            $this->db->set("StatusLimit", $statusLimit);
        }
        $this->db->set("StatusDiscCab", $statusDiscCab);
        $this->db->set("StatusDiscPrins", $statusDiscPrins);
        $this->db->set("TipeDokumen", "SO");
        $this->db->set("Gross", round($params->grosir,0));
        $this->db->set("Potongan", round($params->potongan,0));
        $this->db->set("Value", round($params->value,0));
        $this->db->set("Ppn", round($params->ppn,0));
        $this->db->set("LainLain", "");
        $this->db->set("Materai", $params->materai);
        $this->db->set("OngkosKirim", $params->ongkir);
        $this->db->set("Total", round($params->total,0));
        $this->db->set("Keterangan1", "");
        $this->db->set("Keterangan2", $params->tipeso);
        $this->db->set("Barcode", "");
        $this->db->set("QrCode", "");
        $this->db->set("NoDo", "");
        $this->db->set("NoFaktur", "");
        $this->db->set("NoSP", $params->nosp);
        $this->db->set("TipeFaktur", $params->tipefaktur);
        $this->db->set("NoIDPaket", $params->idpaket);
        $this->db->set("KeteranganTender", $params->kettender);
        $this->db->set("statusPusat", "Gagal");
        $this->db->set("created_at", date('Y-m-d H:i:s'));
        $this->db->set("created_by", $this->session->userdata('username'));
        $valid =  $this->db->insert("trs_sales_order"); 
        $i=0;
        $xx = $this->db->query("select Pelanggan from trs_sales_order where NoSO = '".$noDokumen."' limit 1")->row();
        $headerPelanggan = $xx->Pelanggan;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                if($headerPelanggan == $params->pelanggan){
                  $i++;
                  $expld = explode("~", $params->produk[$key]);
                  $KodeProduk = $expld[0];
                  $NamaProduk = $expld[1];

                  $jml = $params->jumlah[$key];
                  $harga = $params->harga[$key];
                  $dsccab = $params->dsccab[$key];
                  $dscprins1 = $params->dscprins1[$key];
                  $dscprins2 = $params->dscprins2[$key];
                  $bnscab = $params->bnscab[$key];
                  $bnsprins = $params->bnsprins[$key];
                  $statusppn = $params->flag_ppn;

                  // tambahan
                  $unCogs = $params->cogsval[$key];

                  $gross = round(($jml  + ($bnscab + $bnsprins)) * $harga,0);
                  $stotCogs = round(($jml  + ($bnscab + $bnsprins)) * $unCogs,0);
                  $diskoncab = ($dsccab)/100;

                  $dsccab = ($harga * ($jml)) * (($diskoncab)); 
                  $boncab = ($bnscab * $harga);

                  $diskonprins1 = ($dscprins1)/100;
                  $diskonprins2 = ($dscprins2)/100;
                  $diskonprins = $diskonprins1 + $diskonprins2;
                  $dscprins1 = ($harga * ($jml)) * (($diskonprins1));
                  $dscprins2 = ($harga * ($jml)) * (($diskonprins2));
                  $dscprins = ($dscprins1) + ($dscprins2);
                  $bonprins = (($bnsprins) * ($harga));

                  $dscTot = $dsccab + $dscprins;
                  $bnsTot = $boncab + $bonprins;
                  
                  $potongan = round($dscTot + $bnsTot,0);     
                  $value = round($gross - $potongan,0);
                  if($statusppn == 'Y'){
                      $ppn = round($value * 0.1,0);
                  }else{
                      $ppn = 0;
                  }
                  
                  $TotValue = round($value + $ppn,0); 

                  $totgross = $totgross + $gross;
                  $totpotongan = $totpotongan + $potongan;
                  $totvalue = $totvalue + $value;
                  $totppn = $totppn + $ppn;
                  $summary = $summary + $totppn;
                  $totCogs = $totCogs + $stotCogs;


                  // end tambahan

                  //$gross = $jml * $harga;     
                  //$diskoncab = $dsccab/100;
                  //$diskonprins1 = $dscprins1/100;
                  //$diskonprins2 = $dscprins2/100;
                  //$diskonprins = $diskonprins1 + $diskonprins2;
                  //$dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                  //$dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                  //$dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                  //$dscprins = $dscprins1 + $dscprins2;
                  //$boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
                  //$bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
                  //$potongan = $bonprins + $boncab + $dsccab + $dscprins;     
                  //$value = $gross - $potongan;
                  //$ppn = ($value) * ( 10 / 100 );
                  //$TotValue = $value + $ppn; 

                if ($params->jumlah[$key] > 0 || ($params->bnscab[$key] + $params->bnsprins[$key]) > 0) {

                      $getproduk = $this->db->query("select * from mst_produk where Kode_Produk ='".$KodeProduk."' limit 1")->row();

                      $this->db->set("Cabang", $this->cabang);
                      $this->db->set("NoSO", $noDokumen);
                      $this->db->set("noline", $i);
                      $this->db->set("TglSO", $params->tgl);
                      $this->db->set("TimeSO", date('Y-m-d H:i:s'));
                      $this->db->set("Pelanggan", $params->pelanggan);
                      $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                      $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
                      $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                      $this->db->set("NamaTipePelanggan", "");
                      $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                      $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                      $this->db->set("Acu", $params->acu);
                      $this->db->set("acu2", $bid);
                      $this->db->set("CaraBayar", $params->carabayar);
                      $this->db->set("CashDiskon", $params->cashdiskon);
                      $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
                      $this->db->set("TOP", $dataPelanggan->TOP);
                      $this->db->set("TglJtoOrder", $tgljto );
                      $this->db->set("Salesman", $dataSalesman->Kode);
                      $this->db->set("NamaSalesman", $dataSalesman->Nama);
                      $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                      $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                      $this->db->set("Status", $status);
                      $this->db->set("TipeDokumen", "SO");
                      $this->db->set("Gross", $gross);
                      $this->db->set("Potongan", $potongan);
                      $this->db->set("Value", $value);
                      $this->db->set("Ppn", $ppn);
                      $this->db->set("LainLain", "");
                      $this->db->set("Total", $TotValue);
                      $this->db->set("Keterangan1", "");
                      $this->db->set("Keterangan2", $params->tipeso);
                      $this->db->set("Barcode", "");
                      $this->db->set("QrCode", "");
                      $this->db->set("NoDO", "");
                      $this->db->set("NoFaktur", "");
                      // DETAIL PRODUK
                      $this->db->set("KodeProduk", $KodeProduk);
                      $this->db->set("NamaProduk", $NamaProduk);
                      $this->db->set("UOM", $params->uom[$key]);
                      $this->db->set("Harga", $params->harga[$key]);
                      $this->db->set("QtySO", $params->jumlah[$key]);
                      $this->db->set("Bonus", $params->bnscab[$key] + $params->bnsprins[$key]);
                      $this->db->set("ValueBonus", $params->valuebnscab[$key] + $params->valuebnsprins[$key]);
                      $this->db->set("DiscCab_onf", $params->dsccab_onf[$key]);
                      $this->db->set("ValueDiscCab_onf", $params->valuedsccab_onf[$key]);
                      $this->db->set("DiscCab", $params->dsccab[$key]);
                      $this->db->set("ValueDiscCab", $params->valuedsccab[$key]);
                      // $this->db->set("DiscCab2", $params->dsccab2[$key]);
                      // $this->db->set("ValueDiscCab2", $params->valuedsccab2[$key]);
                      $this->db->set("DiscCabTot", "");
                      $this->db->set("ValueDiscCabTotal", "");
                      $this->db->set("DiscPrins1", $params->dscprins1[$key]);
                      $this->db->set("ValueDiscPrins1", $params->valuedscprins1[$key]);
                      $this->db->set("DiscPrins2", $params->dscprins2[$key]);
                      $this->db->set("ValueDiscPrins2", $params->valuedscprins2[$key]);
                      $this->db->set("DiscPrinsTot", "");
                      $this->db->set("ValueDiscPrinsTotal", "");
                      $this->db->set("DiscTotal", "");
                      $this->db->set("ValueDiscTotal", "");
                      $this->db->set("DiscCabMax", $params->maksdsccab[$key]);
                      // $this->db->set("KetDiscCabMax", $params->KetDiscCabMax[$key]);
                      $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
                      // $this->db->set("KetDiscPrinsMax", $params->KetDiscPrinsMax[$key]);
                      $this->db->set("COGS", $params->cogsval[$key]);
                      $this->db->set("TotalCOGS", $stotCogs);
                      $this->db->set("created_at", date('Y-m-d H:i:s'));
                      $this->db->set("created_by", $this->session->userdata('username'));
                      $this->db->set("NoUsulanDisc", $params->NoUsulanDisc[$key]);
                      $this->db->set("TipeUsulanDisc", $params->disprins_trans[$key]);

                      $this->db->set("Prinsipal", $getproduk->Prinsipal);
                      $this->db->set("Prinsipal2", $getproduk->Prinsipal2);
                      $this->db->set("Supplier", $getproduk->Supplier1);
                      $this->db->set("Supplier2", $getproduk->Supplier2);
                      $this->db->set("Pabrik", $getproduk->Pabrik);
                      $this->db->set("Farmalkes", $getproduk->Farmalkes);

                      $valid =  $this->db->insert("trs_sales_order_detail");
                }
                  if($valid){
                       if(!empty($params->NoUsulanDisc[$key])){
                            $this->db->query("update mst_usulan_disc_prinsipal  
                                          SET status ='Terpakai',
                                              NoSO = '".$noDokumen."'
                                          WHERE No_Usulan ='".$params->NoUsulanDisc[$key]."' and 
                                        KodeProduk = '".$KodeProduk."'");
                       }
                      /*$this->db->query("update trs_sales_order_detail a, mst_produk b 
                                  SET a.Prinsipal=b.Prinsipal,
                                    a.Prinsipal2=b.Prinsipal2,
                                    a.Supplier=b.Supplier1,
                                    a.Supplier2=b.Supplier2,
                                    a.Pabrik =b.Pabrik,
                                    a.Farmalkes=b.Farmalkes
                                  WHERE a.KodeProduk=b.Kode_Produk and 
                                        a.KodeProduk = '".$KodeProduk."' and a.NoSO = '".$noDokumen."'");*/
                  }

                }
                
            }
        }
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'SO' and Cabang = '".$this->cabang."'");
            $this->db->query("update mst_usulan_edsipa set Status_Usulan = 'Terpakai',NoSO ='".$noDokumen."' where Status_Usulan = 'Disetujui' and Kode_Pelanggan = '".$params->pelanggan."' and Cabang = '".$this->cabang."'");  
        }
        $callback['no'] = $noDokumen;
        $callback['do'] = $do;

        return $callback;
    }

    public function saveUlangDataSalesOrder_old($params = null)
    {
        // // Set no dokumen
        // $NoSO = $this->Model_main->noDokumen('SO');
        // // Update no dokumen
        // $this->Model_main->saveNoDokumen('SO', substr($NoSO, -7));
        
        $xpldsales = explode("~", $params->salesman);
        $xpldpelanggan = explode("~", $params->pelanggan);
        $kodesales = $xpldsales[0];
        $kodepelanggan = $xpldpelanggan[0];

        $dataPelanggan = $this->dataPelangganNew($kodepelanggan);
        $dataSalesman = $this->dataSalesman($kodesales);   
        $dataTOPLimit = $this->listapproval($kodepelanggan);
        $totgross = $totpotongan = $totvalue = $totppn = $summary = $totCogs = 0;

        $status = "Usulan";
        $statusTOP = "Ok";
        $statusLimit = "Ok";
        $statusDiscCab = "Ok";
        $statusDiscPrins = "Ok";
        $do = true;
        $totalPiutang = $params->total + $params->piutang;
        $xPelanggan = explode("~", $params->pelanggan);
        $Pelanggan = $xPelanggan[0];
        $tglTOP = date('Y-m-d', strtotime('-'.$params->top.' days', strtotime(date('Y-m-d'))));
        $datax = $this->db->query("select TglFaktur from trs_faktur where Pelanggan ='".$Pelanggan."' and Status in ('Open','OpenDIH') and Saldo != 0 order by TglFaktur desc")->result();

        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('SO');
        $year = date('Y');
        $data = $this->db->query("select max(right(NoSO,7)) as 'no' from trs_sales_order where substr(NoSO,5,2) = '14' and Cabang = '".$this->cabang."' and length(NoSO) = 13 and YEAR(tglSO) ='".$year."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//

        // foreach ($datax as $key) {
        //     if (strtotime($key->TglFaktur) < strtotime($tglTOP)) {
        //         $status = "Usulan";
        //         $statusTOP = "TOP";
        //         $do = false;
        //         $this->db->set("Cabang", $this->cabang);
        //         $this->db->set("Dokumen", "Sales Order");
        //         $this->db->set("NoDokumen", $noDokumen);
        //         $this->db->set("Status", "TOP");
        //         $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        //         $valid =  $this->db->insert("trs_approval");
        //         break;
        //     }
        // }


        //Di Ganti Dengan Usulan Limit TOP Pelanggan
        /*
        if ($dataTOPLimit->Umur_faktur > $params->top) {
            $status = "Usulan";
            $statusTOP = "TOP";
            $do = false;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("Dokumen", "Sales Order");
            $this->db->set("NoDokumen", $noDokumen);
            $this->db->set("Status", "TOP");
            $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
            $valid =  $this->db->insert("trs_approval");
            // break;
        }

        if ($params->limit < $totalPiutang) {
            $status = "Usulan";
            $statusLimit = "Limit";
            $do = false;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("Dokumen", "Sales Order");
            $this->db->set("NoDokumen", $noDokumen);
            $this->db->set("Status", "Limit");
            $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
            $valid =  $this->db->insert("trs_approval");
        } */

        if($params->limit < $totalPiutang || $dataTOPLimit->Umur_faktur > $params->top){
            if($dataPelanggan->Buka_Top != 0){
                        $datatop = $dataPelanggan->Buka_Top - 1;
                        $out = $this->db->update('mst_pelanggan', array('Buka_Top' => $datatop ), array('Kode' => $query->Pelanggan ));  
                }
        }

        foreach ($params->produk as $key => $value)
        {
            $dscprins = $params->dscprins1[$key] + $params->dscprins2[$key];
            if ($params->maksdsccab[$key] < $params->dsccab[$key]) {
                $status = "Usulan";
                $statusDiscCab = "DC";
                $do = false;
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "DC");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
                break;
            }

            if ($params->maksdscprins[$key] < $dscprins) {
                $status = "Usulan";
                $statusDiscPrins = "DP";
                $do = false;
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Dokumen", "Sales Order");
                $this->db->set("NoDokumen", $noDokumen);
                $this->db->set("Status", "DP");
                $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("trs_approval");
                break;
            }
        } 

        $carabayar = $params->carabayar;
        if($carabayar == "Cash"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+7 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "Kredit"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+21 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO60"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+60 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
        }elseif($carabayar == "RPO75"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+75 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO90"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+90 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO120"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+120 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO150"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+150 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }elseif($carabayar == "RPO180"){
            $curr_date = date('Y-m-d H:i:s');
            $curr_date = strtotime($curr_date);
            $tgl_jto   = strtotime("+180 day", $curr_date);
            $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            
        }
        //save header  
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("NoSO", $noDokumen);
        $this->db->set("TglSO", $params->tgl);
        $this->db->set("TimeSO", date('Y-m-d H:i:s'));
        $this->db->set("Pelanggan", $kodepelanggan);
        $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
        $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
        $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
        $this->db->set("NamaTipePelanggan", "");
        $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
        $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
        $this->db->set("Acu", $params->acu);
        $this->db->set("CaraBayar", $params->carabayar);
        $this->db->set("ppn_pelanggan", $params->flag_ppn);
        $this->db->set("CashDiskon", $params->cashdiskon);
        $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
        $this->db->set("TOP", $dataPelanggan->TOP);
        $this->db->set("TglJtoOrder", $tgljto);
        $this->db->set("Salesman", $dataSalesman->Kode);
        $this->db->set("NamaSalesman", $dataSalesman->Nama);
        $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
        $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
        $this->db->set("Status", $status);
        $this->db->set("StatusTOP", $statusTOP);
        $this->db->set("StatusLimit", $statusLimit);
        $this->db->set("StatusDiscCab", $statusDiscCab);
        $this->db->set("StatusDiscPrins", $statusDiscPrins);
        $this->db->set("TipeDokumen", "SO");
        $this->db->set("Gross", round($params->grosir,0));
        $this->db->set("Potongan", round($params->potongan,0));
        $this->db->set("Value", round($params->value,0));
        $this->db->set("Ppn", round($params->ppn,0));
        $this->db->set("LainLain", "");
        $this->db->set("Materai", $params->materai);
        $this->db->set("OngkosKirim", $params->ongkir);
        $this->db->set("Total", round($params->total,0));
        $this->db->set("Keterangan1", "");
        $this->db->set("Keterangan2", "");
        $this->db->set("Barcode", "");
        $this->db->set("QrCode", "");
        $this->db->set("NoDo", "");
        $this->db->set("NoFaktur", "");
        $this->db->set("NoSP", $params->nosp);
        $this->db->set("TipeFaktur", $params->tipefaktur);
        $this->db->set("NoIDPaket", $params->idpaket);
        $this->db->set("KeteranganTender", $params->kettender);
        $this->db->set("statusPusat", "Gagal");
        $this->db->set("created_at", date('Y-m-d H:i:s'));
        $this->db->set("created_by", $this->session->userdata('username'));
        $valid =  $this->db->insert("trs_sales_order"); 
        $i=0;
        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key]) and ($params->jumlah[$key] > 0 or ($params->bnscab[$key] + $params->bnsprins[$key]) > 0)) 
            {
                $i++;
                $expld = explode("~", $params->produk[$key]);
                $KodeProduk = $expld[0];
                $NamaProduk = $expld[1];

                $jml = $params->jumlah[$key];
                $harga = $params->harga[$key];
                $dsccab = $params->dsccab[$key];
                $dscprins1 = $params->dscprins1[$key];
                $dscprins2 = $params->dscprins2[$key];
                $bnscab = $params->bnscab[$key];
                $bnsprins = $params->bnsprins[$key];
                $statusppn = $params->flag_ppn;

                // tambahan
                $unCogs = $params->cogsval[$key];

                $gross = round(($jml  + ($bnscab + $bnsprins)) * $harga,0);
                $stotCogs = round(($jml  + ($bnscab + $bnsprins)) * $unCogs,0);
                $diskoncab = ($dsccab)/100;

                $dsccab = ($harga * ($jml)) * (($diskoncab)); 
                $boncab = ($bnscab * $harga);

                $diskonprins1 = ($dscprins1)/100;
                $diskonprins2 = ($dscprins2)/100;
                $diskonprins = $diskonprins1 + $diskonprins2;
                $dscprins1 = ($harga * ($jml)) * (($diskonprins1));
                $dscprins2 = ($harga * ($jml)) * (($diskonprins2));
                $dscprins = ($dscprins1) + ($dscprins2);
                $bonprins = (($bnsprins) * ($harga));

                $dscTot = $dsccab + $dscprins;
                $bnsTot = $boncab + $bonprins;
                
                $potongan = round($dscTot + $bnsTot,0);     
                $value = round($gross - $potongan,0);
                if($statusppn == 'Y'){
                    $ppn = round($value * 0.1,0);
                }else{
                    $ppn = 0;
                }
                
                $TotValue = round($value + $ppn,0); 

                $totgross = $totgross + $gross;
                $totpotongan = $totpotongan + $potongan;
                $totvalue = $totvalue + $value;
                $totppn = $totppn + $ppn;
                $summary = $summary + $totppn;
                $totCogs = $totCogs + $stotCogs;


                // end tambahan

                //$gross = $jml * $harga;     
                //$diskoncab = $dsccab/100;
                //$diskonprins1 = $dscprins1/100;
                //$diskonprins2 = $dscprins2/100;
                //$diskonprins = $diskonprins1 + $diskonprins2;
                //$dsccab = ( $harga * $jml ) * ( $diskoncab ); 
                //$dscprins1 = ( $harga * $jml ) * ( $diskonprins1 );
                //$dscprins2 = ( $harga * $jml ) * ( $diskonprins2 );
                //$dscprins = $dscprins1 + $dscprins2;
                //$boncab = ( $bnscab * $harga) - ( $bnscab * $harga * $diskoncab);
                //$bonprins = ( $bnsprins * $harga) - ( $bnsprins * $harga * $diskonprins);
                //$potongan = $bonprins + $boncab + $dsccab + $dscprins;     
                //$value = $gross - $potongan;
                //$ppn = ($value) * ( 10 / 100 );
                //$TotValue = $value + $ppn; 

                $this->db->set("Cabang", $this->cabang);
                $this->db->set("NoSO", $noDokumen);
                $this->db->set("noline", $i);
                $this->db->set("TglSO", $params->tgl);
                $this->db->set("TimeSO", date('Y-m-d H:i:s'));
                $this->db->set("Pelanggan", $kodepelanggan);
                $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
                $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                $this->db->set("NamaTipePelanggan", "");
                $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                $this->db->set("Acu", $params->acu);
                $this->db->set("CaraBayar", $params->carabayar);
                $this->db->set("CashDiskon", $params->cashdiskon);
                $this->db->set("ValueCashDiskon", $params->cashdiskontotal);
                $this->db->set("TOP", $dataPelanggan->TOP);
                $this->db->set("TglJtoOrder", $tgljto );
                $this->db->set("Salesman", $dataSalesman->Kode);
                $this->db->set("NamaSalesman", $dataSalesman->Nama);
                $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                $this->db->set("Status", $status);
                $this->db->set("TipeDokumen", "SO");
                $this->db->set("Gross", $gross);
                $this->db->set("Potongan", $potongan);
                $this->db->set("Value", $value);
                $this->db->set("Ppn", $ppn);
                $this->db->set("LainLain", "");
                $this->db->set("Total", $TotValue);
                $this->db->set("Keterangan1", "");
                $this->db->set("Keterangan2", "");
                $this->db->set("Barcode", "");
                $this->db->set("QrCode", "");
                $this->db->set("NoDO", "");
                $this->db->set("NoFaktur", "");
                // DETAIL PRODUK
                $this->db->set("KodeProduk", $KodeProduk);
                $this->db->set("NamaProduk", $NamaProduk);
                $this->db->set("UOM", $params->uom[$key]);
                $this->db->set("Harga", $params->harga[$key]);
                $this->db->set("QtySO", $params->jumlah[$key]);
                $this->db->set("Bonus", $params->bnscab[$key] + $params->bnsprins[$key]);
                $this->db->set("ValueBonus", $params->valuebnscab[$key] + $params->valuebnsprins[$key]);
                $this->db->set("DiscCab", $params->dsccab[$key]);
                $this->db->set("ValueDiscCab", $params->valuedsccab[$key]);
                // $this->db->set("DiscCab2", $params->dsccab2[$key]);
                // $this->db->set("ValueDiscCab2", $params->valuedsccab2[$key]);
                $this->db->set("DiscCabTot", "");
                $this->db->set("ValueDiscCabTotal", "");
                $this->db->set("DiscPrins1", $params->dscprins1[$key]);
                $this->db->set("ValueDiscPrins1", $params->valuedscprins1[$key]);
                $this->db->set("DiscPrins2", $params->dscprins2[$key]);
                $this->db->set("ValueDiscPrins2", $params->valuedscprins2[$key]);
                $this->db->set("DiscPrinsTot", "");
                $this->db->set("ValueDiscPrinsTotal", "");
                $this->db->set("DiscTotal", "");
                $this->db->set("ValueDiscTotal", "");
                $this->db->set("DiscCabMax", $params->maksdsccab[$key]);
                $this->db->set("KetDiscCabMax", $params->KetDiscCabMax[$key]);
                $this->db->set("DiscPrinsMax", $params->maksdscprins[$key]);
                $this->db->set("KetDiscPrinsMax", $params->KetDiscPrinsMax[$key]);
                $this->db->set("COGS", $params->cogsval[$key]);
                $this->db->set("TotalCOGS", $stotCogs);
                $this->db->set("created_at", date('Y-m-d H:i:s'));
                $this->db->set("created_by", $this->session->userdata('username'));
                $valid =  $this->db->insert("trs_sales_order_detail");
                if($valid){
                    $this->db->query("update trs_sales_order_detail a, mst_produk b 
                                SET a.Prinsipal=b.Prinsipal,
                                  a.Prinsipal2=b.Prinsipal2,
                                  a.Supplier=b.Supplier1,
                                  a.Supplier2=b.Supplier2,
                                  a.Pabrik =b.Pabrik,
                                  a.Farmalkes=b.Farmalkes
                                WHERE a.KodeProduk=b.Kode_Produk and 
                                      a.KodeProduk = '".$KodeProduk."' and a.NoSO = '".$noDokumen."'");
                }
            }
        }
        if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'SO' and Cabang = '".$this->cabang."'");
        }
        $callback['no'] = $noDokumen;
        $callback['do'] = $do;

        return $callback;
    }

     public function saveDataDeliveryOrder($NoSO = null, $NoDO = NULL)
   {    
        $header = $this->db->query("select * from trs_sales_order where NoSO = '".$NoSO."'")->row();
        $table_detail = "trs_sales_order_detail";
        if ($header->Keterangan2 == 'Bidan') {
            $table_detail = "trs_sales_order_detail_bidan";
        }else{
            $table_detail = "trs_sales_order_detail";
        }
        $detail = $this->db->query("select * from $table_detail where NoSO = '".$NoSO."'")->result();
        $noDokumen ='';
        $NoDO = "";
        $val = true;
        foreach ($detail as $key => $value) 
        {
            $AllBatch = $this->AllBatch($detail[$key]->KodeProduk);
            $qty_so     = $detail[$key]->QtySO;
            $bonus_so   = $detail[$key]->Bonus;
            if(($qty_so + $bonus_so) >  $AllBatch[0]->UnitStok){
                $this->db->set('Status', "Usulan");
                $alasan = "Stok Produk ".$detail[$key]->KodeProduk."~".$detail[$key]->NamaProduk." Tidak Terpenuhi Atau Selisih Stok Summary dan Detail";
                $this->db->set('alasan_status', $alasan);
                $this->db->set('flag_nostok', 'Y');
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');
                log_message("error","stok tidak terpenuhi");
                $val = false;
                $noDokumen='';
                break;
            }

            // /*// cek COGS
            $batch = $this->Batch($detail[$key]->KodeProduk);        
            foreach ($batch as $kunci => $nilai) {
                if($batch[$kunci]->COGS == 0){
                   $unCogs =  -1 * $batch[$kunci]->COGS;

                   $this->db->set('Status', "Usulan");
                    $alasan = "Produk ".$detail[$key]->KodeProduk."~".$detail[$key]->NamaProduk." COGS 0";
                    $this->db->set('alasan_status', $alasan);
                    $this->db->set('flag_nostok', 'Y');
                    $this->db->where('NoSO', $NoSO);
                    $this->db->update('trs_sales_order');
                    log_message("error", $detail[$key]->KodeProduk." COGS 0");
                    $val = false;
                    $noDokumen='';
                    break;
                }
            }

            //cek nama produk
            $getproduk = $this->db->query("select * from mst_produk where Kode_Produk ='".$detail[$key]->KodeProduk."' limit 1")->num_rows();
            if ($getproduk == 0 ) {
                $alasan = "Tidak Ada Nama Produk Pada Kode ".$detail[$key]->KodeProduk;
                $this->db->set('alasan_status', $alasan);
                $this->db->set('flag_nostok', 'Y');
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');
                log_message("error", "Tidak Ada Nama Produk Pada Kode ".$detail[$key]->KodeProduk);
                $val = false;
                $noDokumen='';
                break;
            }
        }
        if ($val == true) {
            $year = date('Y');
            $dataPelanggan = $this->dataPelangganNew($header->Pelanggan);
            $dataSalesman = $this->dataSalesman($header->Salesman);
            if (empty($NoDO)) {
                //================ Running Number ======================================//
                $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                $kodeDokumen = $this->Model_main->kodeDokumen('DO');
                $data = $this->db->query("select max(right(NoDO,7)) as 'no' from trs_delivery_order_sales where substr(NoDO,5,2) = '06' and Cabang = '".$this->cabang."' and length(NoDO) = 13 and YEAR(TglDO) ='".$year."'")->result();
                if(empty($data[0]->no) || $data[0]->no == ""){
                  $lastNumber = 1000001;
                }else {
                  $lastNumber = $data[0]->no + 1;
                }
                $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                //================= end of running number ========================================//
            }
            $carabayar = $header->CaraBayar;
            if($carabayar == "Cash"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+7 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            }elseif($carabayar == "Kredit"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+21 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            }elseif($carabayar == "RPO60"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+60 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
            }elseif($carabayar == "RPO75"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+75 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "RPO90"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+90 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "RPO120"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+120 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "RPO150"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+150 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "RPO180"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+180 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "RPO300"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+300 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "B2B"){
                $curr_date = date('Y-m-d H:i:s');
                $curr_date = strtotime($curr_date);
                $tgl_jto   = strtotime("+1000 day", $curr_date);
                $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }elseif($carabayar == "COD"){
                $tgljto = date('Y-m-d H:i:s');
                // $curr_date = strtotime($curr_date);
                // $tgl_jto   = strtotime("+7 day", $curr_date);
                // $tgljto    = date('Y-m-d H:i:s', $tgl_jto);
                
            }

            $totgross = 0;
            $totpotongan = 0;
            $totalvalue = 0;
            $totcashdiskon = 0;
            $totppn = 0;
            $summary = 0;
            $totCogs = 0;
            $i=0;
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("NoDO", $noDokumen);
            $this->db->set("TglDO", date('Y-m-d'));
            $this->db->set("TimeDO", date('Y-m-d H:i:s'));
            $this->db->set("Pengirim", '');
            $this->db->set("NamaPengirim", '');
            $this->db->set("Pelanggan", $header->Pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("AlamatKirim", $dataPelanggan->Alamat);
            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
            $this->db->set("NamaTipePelanggan", "");
            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
            $this->db->set("Acu", $header->Acu);
            $this->db->set("acu2", $header->acu2);
            $this->db->set("CaraBayar", $header->CaraBayar);
            $this->db->set("CashDiskon", $header->CashDiskon);
            $this->db->set("ValueCashDiskon", $header->ValueCashDiskon);
            $this->db->set("ppn_pelanggan", $header->ppn_pelanggan);
            $this->db->set("TOP", $dataPelanggan->TOP);
            $this->db->set("TglJtoOrder", $tgljto);
            $this->db->set("Salesman", $dataSalesman->Kode);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
            $this->db->set("Status", 'Open');
            $this->db->set("TipeDokumen", "DO");
            $this->db->set("Gross", 0);
            $this->db->set("Potongan", 0);
            $this->db->set("Value",0);
            $this->db->set("Ppn", 0);
            $this->db->set("LainLain",0);
            $this->db->set("Materai", 0);
            $this->db->set("OngkosKirim", 0);
            $this->db->set("Total", 0);
            $this->db->set("Keterangan1", "");
            
            if ($header->Keterangan2 == 'Bidan') {
                $this->db->set("Keterangan2", "Bidan");
            }else{
                $this->db->set("Keterangan2", $header->Keterangan2);
            }
            $this->db->set("Barcode", "");
            $this->db->set("QrCode", "");
            $this->db->set("NoSO", $NoSO);
            $this->db->set("NoFaktur", "");
            $this->db->set("NoSP", $header->NoSP);
            $this->db->set("TipeFaktur", $header->TipeFaktur);
            $this->db->set("NoIDPaket", $header->NoIDPaket);
            $this->db->set("KeteranganTender", $header->KeteranganTender);
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("created_at", date('Y-m-d H:i:s'));
            $this->db->set("created_by", $this->session->userdata('username'));
            $valid =  $this->db->insert("trs_delivery_order_sales");
            $xx = $this->db->query("select Pelanggan from trs_delivery_order_sales where NoDO = '".$noDokumen."' limit 1")->row();
            $headerPelanggan = $xx->Pelanggan; 
            foreach ($detail as $key => $value) 
            {  
                if($headerPelanggan == $header->Pelanggan){ 
                    if (!empty($detail[$key]->KodeProduk)) 
                    {
                        $batch = $this->Batch($detail[$key]->KodeProduk);        
                        $QtySO = $detail[$key]->QtySO;
                        $bonusSOawal = $detail[$key]->Bonus;
                        foreach ($batch as $kunci => $nilai) {
                            $i++; 
                            $jumlah = $detail[$key]->QtySO + $detail[$key]->Bonus;
                            if($jumlah <= 0){
                                log_message("error","kosong");
                                break;
                            }
                            $getproduk = $this->db->query("select * from mst_produk where Kode_Produk ='".$detail[$key]->KodeProduk."' limit 1")->row();
                            $this->db->set("Cabang", $this->cabang);
                            $this->db->set("NoDO", $noDokumen);
                            $this->db->set("noline", $i);
                            $this->db->set("TglDO", date('Y-m-d'));
                            $this->db->set("TimeDO", date('Y-m-d H:i:s'));
                            $this->db->set("Pengirim", '');
                            $this->db->set("NamaPengirim", '');
                            $this->db->set("Pelanggan", $header->Pelanggan);
                            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                            $this->db->set("AlamatKirim", $dataPelanggan->Alamat);
                            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                            $this->db->set("NamaTipePelanggan", "");
                            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                            $this->db->set("Acu", $header->Acu);
                            $this->db->set("acu2", $header->acu2);
                            $this->db->set("CaraBayar", $header->CaraBayar);
                            $this->db->set("CashDiskon", $header->CashDiskon);
                            $this->db->set("TOP", $dataPelanggan->TOP);
                            $this->db->set("TglJtoOrder", $tgljto);
                            $this->db->set("Salesman", $dataSalesman->Kode);
                            $this->db->set("NamaSalesman", $dataSalesman->Nama);
                            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                            $this->db->set("Status", "Open");
                            $this->db->set("TipeDokumen", "DO");
                            $this->db->set("Keterangan1", "");

                            if ($header->Keterangan2 == 'Bidan') {
                                $this->db->set("Keterangan2", "Bidan");
                            }else{
                                $this->db->set("Keterangan2", $header->Keterangan2);
                            }

                            $this->db->set("Barcode", "");
                            $this->db->set("QrCode", "");
                            $this->db->set("NoSO", $NoSO);
                            $this->db->set("NoFaktur", "");
                            $this->db->set("KodeProduk", $detail[$key]->KodeProduk);
                            $this->db->set("NamaProduk", $getproduk->Produk/*$detail[$key]->NamaProduk*/);
                            $this->db->set("UOM", $detail[$key]->UOM);
                            $this->db->set("Harga", $detail[$key]->Harga);
                            $this->db->set("QtySO", $QtySO);
                            $this->db->set("BonusSO", $bonusSOawal);
                            $this->db->set("DiscCab", $detail[$key]->DiscCab);   
                            $this->db->set("DiscCab_onf", $detail[$key]->DiscCab_onf);                  
                            $this->db->set("DiscCabTot", $detail[$key]->DiscCab + $detail[$key]->DiscCab_onf);
                            $this->db->set("DiscPrins1", $detail[$key]->DiscPrins1);
                            $this->db->set("DiscPrins2", $detail[$key]->DiscPrins2);
                            $this->db->set("DiscPrinsTot", $detail[$key]->DiscPrins1 + $detail[$key]->DiscPrins2);
                            $this->db->set("DiscCabMax", $detail[$key]->DiscCabMax);
                            $this->db->set("KetDiscCabMax", $detail[$key]->KetDiscCabMax);
                            $this->db->set("DiscPrinsMax", $detail[$key]->DiscPrinsMax);
                            $this->db->set("KetDiscPrinsMax", $detail[$key]->KetDiscPrinsMax);
                            $this->db->set("created_at", date('Y-m-d H:i:s'));
                            $this->db->set("created_by", $this->session->userdata('username'));
                            $this->db->set("Prinsipal", $getproduk->Prinsipal);
                            $this->db->set("Prinsipal2", $getproduk->Prinsipal2);
                            $this->db->set("Supplier", $getproduk->Supplier1);
                            $this->db->set("Supplier2", $getproduk->Supplier2);
                            $this->db->set("Pabrik", $getproduk->Pabrik);
                            $this->db->set("Farmalkes", $getproduk->Farmalkes);
                            if (($detail[$key]->QtySO + $detail[$key]->Bonus ) <= $batch[$kunci]->UnitStok) {     
                                $jml = $detail[$key]->QtySO;
                                $harga = $detail[$key]->Harga;
                                $dsccab = $detail[$key]->DiscCab;
                                $dsccab_onf = $detail[$key]->DiscCab_onf;
                                $dscprins1 = $detail[$key]->DiscPrins1;
                                $dscprins2 = $detail[$key]->DiscPrins2;
                                $bns = $detail[$key]->Bonus;
                                $CashDiskon = $header->CashDiskon;
                                $statusppn = $header->ppn_pelanggan;

                                // tambahan
                                if($batch[$kunci]->COGS < 0){
                                   $unCogs =  -1 * $batch[$kunci]->COGS;
                                }else{
                                   $unCogs = $batch[$kunci]->COGS; 
                                }

                                $gross = round((($jml  + $bns) * $harga),0);
                                $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                $diskoncab = ($dsccab)/100;
                                $diskoncab_onf = ($dsccab_onf)/100;

                                $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                $dsccab_onfval = ($harga * ($jml)) * (($diskoncab_onf)); 
                                $boncabval = ($bns * $harga);

                                $diskonprins1 = ($dscprins1)/100;
                                $diskonprins2 = ($dscprins2)/100;
                                $diskonprins = $diskonprins1 + $diskonprins2;
                                $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                $dscprins = ($dscprins1val) + ($dscprins2val);
                                // $bonprinsval = (($bns) * ($harga));

                                $dscTot = $dsccabval + $dsccab_onfval + $dscprins;
                                $bnsTot = $boncabval;

                                $valdisctotal = $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val;
                                $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                
                                $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);     
                                $value = round(($gross - $potongan),0);
                                if($statusppn=='Y'){
                                    $ppn = round(($value * 0.1),0);
                                }else{
                                    $ppn = 0;
                                }
                                
                                $TotValue = round(($value + $ppn),0); 
                                // $TotAll = 

                                $totgross = $totgross + $gross;
                                $totpotongan = $totpotongan + $potongan;
                                $totalvalue = $totalvalue + $value;
                                $totppn = $totppn + $ppn;
                                $summary = $summary + $TotValue;
                                $totCogs = $totCogs + $stotCogs;

                                $this->db->set("ValueDiscCab", $dsccabval);
                                $this->db->set("ValueDiscCab_onf", $dsccab_onfval);
                                $this->db->set("ValueDiscCabTotal", $dsccabval+$dsccab_onfval);
                                $this->db->set("ValueDiscPrins1", $dscprins1val);
                                $this->db->set("ValueDiscPrins2", $dscprins2val);
                                $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                $this->db->set("DiscTotal", $dsccab+$dsccab_onf+$dscprins1+$dscprins2);
                                $this->db->set("ValueDiscTotal", $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val);
                                $this->db->set("ValueCashDiskon", $valcashdiskon);
                                $this->db->set("QtyDO", $jml);
                                $this->db->set("BonusDO", $detail[$key]->Bonus);
                                $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                                $this->db->set("Gross", $gross);
                                $this->db->set("Potongan", $potongan);
                                $this->db->set("Value", $value);
                                $this->db->set("Ppn", $ppn);
                                $this->db->set("LainLain", "");
                                $this->db->set("Total", $TotValue);
                                $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                $this->db->set("COGS", $unCogs);
                                $this->db->set("TotalCOGS", $stotCogs);
                                $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                if($valid){
                                    $jumlah = $detail[$key]->QtySO + $detail[$key]->Bonus;
                                    $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                    // $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                    //                     SET a.Prinsipal=b.Prinsipal,
                                    //                       a.Prinsipal2=b.Prinsipal2,
                                    //                       a.Supplier=b.Supplier1,
                                    //                       a.Supplier2=b.Supplier2,
                                    //                       a.Pabrik =b.Pabrik,
                                    //                       a.Farmalkes=b.Farmalkes
                                    //                     WHERE 
                                    //                       a.KodeProduk=b.Kode_Produk and 
                                    //                       a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                }
                                
                                break;
                            }
                            if((($detail[$key]->QtySO + $detail[$key]->Bonus ) > $batch[$kunci]->UnitStok) and ($batch[$kunci]->UnitStok < $detail[$key]->Bonus)){
                                log_message("error","kesini");
                                log_message("error",print_r($batch[$kunci]->BatchNo,true));
                                log_message("error",print_r($batch[$kunci]->UnitStok,true));
                                if($detail[$key]->QtySO > 0){
                                    log_message("error","ga nol");
                                    if($detail[$key]->QtySO > $batch[$kunci]->UnitStok){
                                        log_message("error","1");
                                        $jml = $batch[$kunci]->UnitStok;
                                    }elseif($detail[$key]->QtySO == $batch[$kunci]->UnitStok){
                                        log_message("error","2");
                                        $jml = $detail[$key]->QtySO;
                                    }elseif($detail[$key]->QtySO < $batch[$kunci]->UnitStok){
                                        log_message("error","4");
                                        $jml = $detail[$key]->QtySO;
                                    }elseif($detail[$key]->QtySO = 0){
                                        log_message("error","5");
                                        $jml = 0;
                                    }
                                }else{
                                   $jml = 0; 
                                }
                                
                                if($detail[$key]->Bonus > 0){
                                    if($detail[$key]->QtySO > $batch[$kunci]->UnitStok){
                                        log_message("error","1");
                                        $bns = 0;
                                    }elseif($detail[$key]->QtySO == $batch[$kunci]->UnitStok){
                                        log_message("error","2");
                                        $bns = 0;
                                    }elseif($detail[$key]->QtySO < $batch[$kunci]->UnitStok){
                                        log_message("error","4");
                                        $bns = $batch[$kunci]->UnitStok - $detail[$key]->QtySO ;
                                    }elseif($detail[$key]->QtySO = 0){
                                        log_message("error","5");
                                        $bns = $batch[$kunci]->UnitStok;
                                    }
                                    log_message("error","Bonus Masuk ksini");
                                   // $bns = $detail[$key]->Bonus - ($batch[$kunci]->UnitStok + $jml);
                                }else{
                                   $bns = 0;
                                }
                                log_message("error",print_r($jml,true));
                                log_message("error",print_r($bns,true));
                                if($jml != 0 or $bns != 0){
                                    $harga = $detail[$key]->Harga;
                                    $dsccab = $detail[$key]->DiscCab;
                                    $dsccab_onf = $detail[$key]->DiscCab_onf;
                                    $dscprins1 = $detail[$key]->DiscPrins1;
                                    $dscprins2 = $detail[$key]->DiscPrins2;
                                    // $bns = 0;
                                    $CashDiskon = $header->CashDiskon;
                                    $statusppn = $header->ppn_pelanggan;

                                    // tambahan
                                    if($batch[$kunci]->COGS < 0){
                                       $unCogs =  -1 * $batch[$kunci]->COGS;
                                    }else{
                                       $unCogs = $batch[$kunci]->COGS; 
                                    }

                                    $gross = round((($jml  + $bns) * $harga),0);
                                    $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                    $diskoncab = ($dsccab)/100;
                                    $diskoncab_onf = ($dsccab_onf)/100;

                                    $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                    $dsccab_onfval = ($harga * ($jml)) * (($diskoncab_onf)); 
                                    $boncabval = ($bns * $harga);

                                    $diskonprins1 = ($dscprins1)/100;
                                    $diskonprins2 = ($dscprins2)/100;
                                    $diskonprins = $diskonprins1 + $diskonprins2;
                                    $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                    $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                    $dscprins = ($dscprins1val) + ($dscprins2val);
                                    // $bonprinsval = (($bns) * ($harga));

                                    $dscTot = $dsccabval + $dsccab_onfval + $dscprins;
                                    $bnsTot = $boncabval;

                                    $valdisctotal = $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val;
                                    $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                    
                                    $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);      
                                    $value = round(($gross - $potongan),0);
                                    if($statusppn=='Y'){
                                        $ppn = round(($value * 0.1),0);
                                    }else{
                                        $ppn = 0;
                                    }
                                    $TotValue = round(($value + $ppn),0); 

                                    $totgross = $totgross + $gross;
                                    $totpotongan = $totpotongan + $potongan ;
                                    $totalvalue = $totalvalue + $value;
                                    $totppn = $totppn + $ppn;
                                    $summary = $summary + $TotValue;
                                    $totCogs = $totCogs + $stotCogs;

                                    $this->db->set("ValueDiscCab", $dsccabval);
                                    $this->db->set("ValueDiscCab_onf", $dsccab_onfval);
                                    $this->db->set("ValueDiscCabTotal", $dsccabval+$dsccab_onfval);
                                    $this->db->set("ValueDiscPrins1", $dscprins1val);
                                    $this->db->set("ValueDiscPrins2", $dscprins2val);
                                    $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                    $this->db->set("DiscTotal", $dsccab+$dsccab_onf+$dscprins1+$dscprins2);
                                    $this->db->set("ValueDiscTotal", $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val);
                                    $this->db->set("ValueCashDiskon", $valcashdiskon);
                                    $this->db->set("QtyDO", $jml);
                                    $this->db->set("Gross", $gross);
                                    $this->db->set("BonusDO", $bns);
                                    $this->db->set("ValueBonus", $boncabval);
                                    $this->db->set("Potongan", $potongan);
                                    $this->db->set("Value", $value);
                                    $this->db->set("Ppn", $ppn);
                                    $this->db->set("LainLain", "");
                                    $this->db->set("Total", $TotValue);
                                    $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                    $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                    $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                    $this->db->set("COGS", $unCogs);
                                    $this->db->set("TotalCOGS", $stotCogs);
                                    $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                    $detail[$key]->QtySO = ($detail[$key]->QtySO) - $jml;
                                    $detail[$key]->Bonus = $detail[$key]->Bonus - $bns;
                                    if($valid){
                                        $jumlah = $jml + $bns;
                                        $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                        // $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                        //                     SET a.Prinsipal=b.Prinsipal,
                                        //                       a.Prinsipal2=b.Prinsipal2,
                                        //                       a.Supplier=b.Supplier1,
                                        //                       a.Supplier2=b.Supplier2,
                                        //                       a.Pabrik =b.Pabrik,
                                        //                       a.Farmalkes=b.Farmalkes
                                        //                     WHERE 
                                        //                       a.KodeProduk=b.Kode_Produk and 
                                        //                       a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                    }
                                }
                            }
                            else{
                                $jml = $batch[$kunci]->UnitStok - $detail[$key]->Bonus;
                                $harga = $detail[$key]->Harga;
                                $dsccab = $detail[$key]->DiscCab;
                                $dsccab_onf = $detail[$key]->DiscCab_onf;
                                $dscprins1 = $detail[$key]->DiscPrins1;
                                $dscprins2 = $detail[$key]->DiscPrins2;
                                $bns = $detail[$key]->Bonus;
                                $CashDiskon = $header->CashDiskon;
                                $statusppn = $header->ppn_pelanggan;
                                // tambahan
                                
                                if($batch[$kunci]->COGS < 0){
                                   $unCogs =  -1 * $batch[$kunci]->COGS;
                                }else{
                                   $unCogs = $batch[$kunci]->COGS; 
                                }

                                $gross = round((($jml  + $bns) * $harga),0);
                                $stotCogs = round((($jml  + $bns) * $unCogs),0);
                                $diskoncab = ($dsccab)/100;
                                $diskoncab_onf = ($dsccab_onf)/100;

                                $dsccabval = ($harga * ($jml)) * (($diskoncab)); 
                                $dsccab_onfval = ($harga * ($jml)) * (($diskoncab_onf)); 
                                $boncabval = ($bns * $harga);

                                $diskonprins1 = ($dscprins1)/100;
                                $diskonprins2 = ($dscprins2)/100;
                                $diskonprins = $diskonprins1 + $diskonprins2;
                                $dscprins1val = ($harga * ($jml)) * (($diskonprins1));
                                $dscprins2val = ($harga * ($jml)) * (($diskonprins2));
                                $dscprins = ($dscprins1val) + ($dscprins2val);
                                // $bonprinsval = (($bns) * ($harga));

                                $dscTot = $dsccabval +$dsccab_onfval + $dscprins;
                                $bnsTot = $boncabval;

                                $valdisctotal = $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val;
                                $valcashdiskon = ($gross - ($valdisctotal + $bnsTot)) * ($CashDiskon/100);
                                
                                $potongan = round(($dscTot + $bnsTot + $valcashdiskon),0);      
                                $value = round(($gross - $potongan),0);
                                if($statusppn=='Y'){
                                    $ppn = round(($value * 0.1),0);
                                }else{
                                    $ppn = 0;
                                }
                                $TotValue = round(($value + $ppn),0);   

                                $totgross = $totgross + $gross;
                                $totpotongan = $totpotongan + $potongan;
                                $totalvalue = $totalvalue + $value;
                                $totppn = $totppn + $ppn;
                                $summary = $summary + $TotValue;
                                $totCogs = $totCogs + $stotCogs;

                                $this->db->set("ValueDiscCab", $dsccabval);
                                $this->db->set("ValueDiscCab_onf", $dsccab_onfval);
                                $this->db->set("ValueDiscCabTotal", $dsccabval+$dsccab_onfval);
                                $this->db->set("ValueDiscPrins1", $dscprins1val);
                                $this->db->set("ValueDiscPrins2", $dscprins2val);
                                $this->db->set("ValueDiscPrinsTotal", $dscprins1val+$dscprins2val);
                                $this->db->set("DiscTotal", $dsccab+$dsccab_onf+$dscprins1+$dscprins2);
                                $this->db->set("ValueDiscTotal", $dsccabval+$dsccab_onfval+$dscprins1val+$dscprins2val);
                                $this->db->set("ValueCashDiskon", $valcashdiskon);
                                $this->db->set("QtyDO", $jml);
                                $this->db->set("BonusDO", $detail[$key]->Bonus);
                                $this->db->set("ValueBonus", $detail[$key]->ValueBonus);
                                $this->db->set("Gross", $gross);
                                $this->db->set("Potongan", $potongan);
                                $this->db->set("Value", $value);
                                $this->db->set("Ppn", $ppn);
                                $this->db->set("LainLain", "");
                                $this->db->set("Total", $TotValue);
                                $this->db->set("BatchNo", $batch[$kunci]->BatchNo);
                                $this->db->set("ExpDate", $batch[$kunci]->ExpDate);
                                $this->db->set("COGS", $unCogs);
                                $this->db->set("TotalCOGS", $stotCogs);
                                $this->db->set("NoBPB", $batch[$kunci]->NoBPB);
                                $valid =  $this->db->insert("trs_delivery_order_sales_detail");
                                $detail[$key]->QtySO = ($detail[$key]->QtySO + $detail[$key]->Bonus ) - $batch[$kunci]->UnitStok;
                                $detail[$key]->Bonus = 0;
                                $detail[$key]->ValueBonus = 0;
                                if($valid){
                                    $jumlah = ($batch[$kunci]->UnitStok - $detail[$key]->Bonus) + $detail[$key]->Bonus;
                                    $this->updateStok($noDokumen,$detail[$key]->KodeProduk,$batch[$kunci]->BatchNo,$batch[$kunci]->NoBPB,$jumlah,$detail[$key]->Harga,$stotCogs,$batch[$kunci]->ExpDate);
                                    // $this->db->query("update trs_delivery_order_sales_detail a, mst_produk b 
                                    //                     SET a.Prinsipal=b.Prinsipal,
                                    //                       a.Prinsipal2=b.Prinsipal2,
                                    //                       a.Supplier=b.Supplier1,
                                    //                       a.Supplier2=b.Supplier2,
                                    //                       a.Pabrik =b.Pabrik,
                                    //                       a.Farmalkes=b.Farmalkes
                                    //                     WHERE 
                                    //                       a.KodeProduk=b.Kode_Produk and 
                                    //                       a.KodeProduk = '".$detail[$key]->KodeProduk."' and a.NoDO='".$noDokumen."'");

                                }
                            }
                        }
                    }
                }
            }

            $valid = $this->db->query("update trs_delivery_order_sales 
                                        set  Gross = '".$totgross."', 
                                            Potongan = '".$totpotongan."',
                                            Value = '".$totalvalue."',
                                            Ppn = '".$totppn."',
                                            LainLain = '".$header->LainLain."',
                                            Materai = '".$header->Materai."',
                                            OngkosKirim = '".$header->OngkosKirim."',
                                            Total = '".$summary."'
                                        WHERE Cabang = '".$this->cabang."' and 
                                              NoDO = '".$noDokumen."'");
            
            // $this->db->set("Gross", $totgross);
            // $this->db->set("Potongan", $totpotongan);
            // $this->db->set("Value", $totalvalue);
            // $this->db->set("Ppn", $totppn);
            // $this->db->set("LainLain", $header->LainLain);
            // $this->db->set("Materai", $header->Materai);
            // $this->db->set("OngkosKirim", $header->OngkosKirim);
            // $this->db->set("Total", $summary);
            // $this->db->where("Cabang", $this->cabang);
            // $this->db->where("NoDO", $noDokumen);
            // $valid =  $this->db->update("trs_delivery_order_sales");
            if($valid){
                // $this->setStok($noDokumen);
                $this->db->set('NoDO', $noDokumen);
                $this->db->set('Status', "Closed");
                $this->db->set('alasan_status', "DO Sudah Di Proses");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->session->userdata('username'));
                $this->db->where('NoSO', $NoSO);
                $this->db->update('trs_sales_order');

                $this->db->set('NoDO', $noDokumen);
                $this->db->set('Status', "Closed");
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->session->userdata('username'));
                $this->db->where('NoSO', $NoSO);
                $this->db->update($table_detail);
                if($valid){
                    $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'DO' and Cabang = '".$this->cabang."'");
                   
                }
            }
        }
        return $noDokumen;
    }

    public function cekDataDO($noDokumen=NULL){
        $rollback = false;
                
                    
                $query = $this->db->query("SELECT DISTINCT trs_delivery_order_sales_detail.KodeProduk,
                                            QtySO,BonusSO,sumdo.QtyDO,sumdo.BonusDO,Keterangan2
                                            FROM trs_delivery_order_sales_detail LEFT JOIN
                                            (SELECT KodeProduk,SUM(QtyDO) AS 'QtyDO', SUM(BonusDO) AS 'BonusDO'  
                                             from trs_delivery_order_sales_detail where NoDO ='".$noDokumen."'
                                             group by KodeProduk) as sumdo on sumdo.KodeProduk = trs_delivery_order_sales_detail.KodeProduk
                                            where trs_delivery_order_sales_detail.NoDO ='".$noDokumen."'");
                

                $Keterangan2 = "";
                $table_detail = "";

                if ($query->row()->Keterangan2 == "Bidan") {
                        $Keterangan2 = "Bidan";
                        $table_detail = "trs_sales_order_detail_bidan";
                    }else{
                        $table_detail = "trs_sales_order_detail";
                    }

                foreach ($query->result() as $cek) {
                    $cekprod = $cek->KodeProduk;
                    $cekqtyso = $cek->QtySO;
                    $cekbonusso = $cek->BonusSO;
                    $cekqtydo = $cek->QtyDO;
                    $cekbonusdo = $cek->BonusDO;
                    
                    if ($Keterangan2 != "Bidan") {
                        if(($cekqtyso + $cekbonusso) != ($cekqtydo + $cekbonusdo)){
                            $rollback = true;
                            break;
                        }
                        # code...
                    }
                }

                $query2 =$this->db->query("select NODO from trs_delivery_order_sales where NODO = '".$noDokumen."' and Gross=0 limit 1")->num_rows();
                $query3 =$this->db->query("select NODO from trs_delivery_order_sales where NODO = '".$noDokumen."' and NODO in (select NODO from trs_delivery_order_sales_detail where NODO = '".$noDokumen."') ")->num_rows();

                if($query2 > 0 or $query3 <= 0){
                    $rollback = true;
                }
                if($rollback == true){
                    $this->db->set("Status", "Batal");
                    $this->db->set('Keterangan1', "DO Batal By System");
                    
                    $this->db->where("NoDO", $noDokumen);
                    $valid = $this->db->update('trs_delivery_order_sales'); 

                    
                    $this->db->set("Status", "Batal");
                    $this->db->set('time_batal', date('Y-m-d H:i:s'));
                    $this->db->where("NoDO", $noDokumen);
                    $valid = $this->db->update('trs_delivery_order_sales_detail');

                    $this->db->set('NoDO', '');
                    $this->db->set('Status', "Usulan");
                    $this->db->set('alasan_status', "DO Batal By System");
                    
                    $this->db->where('NoDO', $noDokumen);
                    $valid = $this->db->update('trs_sales_order');

                    $this->db->set('NoDO', '');
                    $this->db->set('Status', "Usulan");
                    $this->db->where('NoDO', $noDokumen);
                    $valid = $this->db->update($table_detail);

                    if($valid){
                        $cek_data_double = $this->db->query("select KodeProduk,COUNT(KodeProduk)jumlah from trs_delivery_order_sales_detail where NoDO = '".$noDokumen."' AND (QtySO = QtyDO AND BonusSO = BonusDO) AND Keterangan2 = 'Bidan' 
                            GROUP BY KodeProduk
                            HAVING COUNT(KodeProduk) > 1")->num_rows();  

                        if ($cek_data_double > 0) {
                            $this->backStok_double($noDokumen);
                            log_message("error","backStok Double ".$noDokumen);
                        }else{
                            $this->backStok($noDokumen);
                            log_message("error","backStok ".$noDokumen);
                        }
                    }
                }
    }

    // public function listDataSO($search = null, $limit = null, $status = null)
    // {   
    //     $byStatus = "";
    //     if (!empty($status)) {
    //         $byStatus = " and Status = '".$status."'";
    //     }

    //     // $query = $this->db->query("select * from trs_sales_order where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TimeSO DESC,NoSO ASC $limit ");
    //     $query = $this->db->query(        
    //             "SELECT a.NoDO, 
    //                     trs_sales_order.NoSO,
    //                     TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
    //                     Acu,Salesman,`CaraBayar`,`Status`,
    //                     TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,
    //                     -- Monitoring
    //                     CONCAT(
    //                         CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
    //                         CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
    //                         CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
    //                         CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
    //                         ) AS MonitoringApproval,
    //                     CONCAT(
    //                             -- Limit
    //                             CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
    //                             CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
    //                             -- TOP
    //                             CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Diskon Cabang
    //                             CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Diskon Prinsipal
    //                             CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
    //                             CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
    //                             -- Status Stok
    //                             IFNULL(alasan_status,'')  
    //                         ) AS MonitoringStatus                           
    //                 FROM trs_sales_order 
    //                 LEFT JOIN (SELECT NoDO,NoSO FROM `trs_delivery_order_sales` WHERE Cabang='".$this->cabang."') a ON trs_sales_order.`NoSO`=a.NoSO 
    //                 WHERE Cabang = '".$this->cabang."' $byStatus $search
    //                 ORDER BY TimeSO DESC,NoSO ASC $limit ");


    //     return $query;
    // }

     public function listDataSO($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and Status = '".$status."'";
        }

        // $query = $this->db->query("select * from trs_sales_order where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TimeSO DESC,NoSO ASC $limit ");
        if($status != 'Usulan'){
          $query = $this->db->query(        
                "SELECT trs_sales_order.NoDO, 
                        trs_sales_order.NoSO,
                        TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
                        Acu,Salesman,`CaraBayar`,`Status`,
                        TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,umur_piutang_bm,limit_piutang_bm,
                        -- Monitoring
                        CONCAT(
                            CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
                            CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
                            CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
                            CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
                            ) AS MonitoringApproval,
                        CONCAT(
                                -- Limit
                                CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
                                CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
                                CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
                                -- TOP
                                CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Cabang
                                CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Prinsipal
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
                                -- Status Stok
                                IFNULL(alasan_status,'')  
                            ) AS MonitoringStatus                           
                    FROM trs_sales_order 
                    WHERE Cabang = '".$this->cabang."' $search
                    ORDER BY TimeSO DESC,NoSO ASC $limit ");
        }else{
          $query = $this->db->query(        
                "SELECT a.NoDO, 
                        trs_sales_order.NoSO,
                        TglSO,TimeSO,TipePelanggan,Pelanggan,NamaPelanggan,AlamatPelanggan,
                        Acu,Salesman,`CaraBayar`,`Status`,
                        TipeDokumen,Total,TipeFaktur,trs_sales_order.statusPusat,alasan_status,umur_piutang_bm,limit_piutang_bm,
                        -- Monitoring
                        CONCAT(
                            CASE StatusLimit WHEN 'Limit' THEN CONCAT('Limit',',') ELSE '' END,
                            CASE StatusTOP WHEN 'TOP' THEN CONCAT('TOP',',') ELSE '' END,
                            CASE StatusDiscCab WHEN 'DC' THEN CONCAT('DC',',') ELSE '' END,
                            CASE StatusDiscPrins WHEN 'DP' THEN CONCAT('DP',',') ELSE '' END
                            ) AS MonitoringApproval,
                        CONCAT(
                                -- Limit
                                CASE Approve_KSA WHEN 'Y' THEN 'LMT KSA OK | ' WHEN 'R' THEN 'LMT KSA TOLAK | ' ELSE '' END ,
                                CASE Approve_BM WHEN 'Y' THEN 'LMT BM OK | ' WHEN 'R' THEN 'LMT BM TOLAK | '  ELSE '' END ,
                                CASE Approve_RBM WHEN 'Y' THEN 'LMT RBM OK | ' WHEN 'R' THEN 'LMT RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_Pusat WHEN 'Y' THEN 'LMT PUSAT OK | ' WHEN 'R' THEN 'LMT PUSAT TOLAK | '  ELSE '' END ,
                                -- TOP
                                CASE Approve_TOP_KSA WHEN 'Y' THEN 'TOP KSA OK | ' WHEN 'R' THEN 'TOP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_BM WHEN 'Y' THEN 'TOP BM OK | ' WHEN 'R' THEN 'TOP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_RBM WHEN 'Y' THEN 'TOP RBM OK | ' WHEN 'R' THEN 'TOP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_TOP_Pusat WHEN 'Y' THEN 'TOP PUSAT OK | ' WHEN 'R' THEN 'TOP PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Cabang
                                CASE Approve_DiscCab_KSA WHEN 'Y' THEN 'DC KSA OK | ' WHEN 'R' THEN 'DC KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_BM WHEN 'Y' THEN 'DC BM OK | ' WHEN 'R' THEN 'DC BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_RBM WHEN 'Y' THEN 'DC RBM OK | ' WHEN 'R' THEN 'DC RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscCab_Pusat WHEN 'Y' THEN 'DC PUSAT OK | ' WHEN 'R' THEN 'DC PUSAT TOLAK | '  ELSE '' END ,
                                -- Diskon Prinsipal
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP KSA OK | ' WHEN 'R' THEN 'DP KSA TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_BM WHEN 'Y' THEN 'DP BM OK | ' WHEN 'R' THEN 'DP BM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_RBM WHEN 'Y' THEN 'DP RBM OK | ' WHEN 'R' THEN 'DP RBM TOLAK | '  ELSE '' END ,
                                CASE Approve_DiscPrins_Pusat WHEN 'Y' THEN 'DP PUSAT OK | ' WHEN 'R' THEN 'DP PUSAT TOLAK | '  ELSE '' END ,
                                -- Status Stok
                                IFNULL(alasan_status,'')  
                            ) AS MonitoringStatus                           
                    FROM trs_sales_order 
                    LEFT JOIN (SELECT NoDO,NoSO FROM `trs_delivery_order_sales` WHERE Cabang='".$this->cabang."') a ON trs_sales_order.`NoSO`=a.NoSO 
                    WHERE Cabang = '".$this->cabang."' $byStatus $search
                    ORDER BY TimeSO DESC,NoSO ASC $limit ");
        }
        return $query;
    }

    public function prosesDataSO($no = NULL,$do=null)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        
        $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row(); 

        $table = "trs_sales_order_detail";

        if ($query2->Keterangan2 == "Bidan") {
            $table = "trs_sales_order_detail_bidan";
        }else{
            $table = "trs_sales_order_detail";
        }

        $query = $this->db->query("select * from $table where NoSO = '".$no."'")->result(); 
        $status = $query2->Status;  

        if ($this->db2->conn_id == TRUE) {
            $cek = $this->db2->query("select * from $table where NoSO = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert($table, $r); // insert each row to another table
                }
            }
            // else{
            //     // // foreach($query as $r) { // loop over results
            //     //    if($status == 'Reject'){
            //     //     $this->db2->set('Status', "Reject");
            //     //   }elseif($status == 'Closed') {
            //     //     $this->db2->set('Status', "Closed");
            //     //     $this->db2->set('NoDO', $do);
            //     //   }else{
            //     //     $this->db2->set('Status', $status);
            //     //   }
            //     //     $this->db2->where('NoSO', $no);
            //     //     $this->db2->update('trs_sales_order_detail');
            //     // // }
            //     foreach($query as $r) { // loop over results
            //         $this->db2->where('KodeProduk', $r->KodeProduk);
            //         $this->db2->where('NoSO', $no);
            //         $this->db2->update($table, $r);
            //     }
            // }
            $cek2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_sales_order', $query2); // insert each row to another table
            }
            else{
                // if($status == 'Reject'){
                //     $this->db2->set('Status', "Reject");
                //   }elseif($status == 'Closed') {
                //     $this->db2->set('Status', "Closed");
                //     $this->db2->set('NoDO', $do);
                //   }else{
                //     $this->db2->set('Status', $status);
                //   }
                // $this->db2->where('NoSO', $no);
                // $this->db2->update('trs_sales_order');
                $this->db2->where('NoSO', $no);
                $this->db2->update('trs_sales_order', $query2);
            }
            $update = $this->db->query("update trs_sales_order set statusPusat = 'Berhasil' where NoSO = '".$no."'");
            return TRUE;
        }
        else{
            $this->db->query("update trs_sales_order set statusPusat = 'Gagal' where NoSO = '".$no."'");
            return FALSE;
        }
    }

    public function prosesDataLT($noso)
    {
        $valid = true;

        //$this->db->select('AppKSA, AppBM');
        log_message("error",print_r($noso,true));    
        $this->db->where('NoSO', $noso);
        $query = $this->db->get('trs_sales_order')->row();
        
        $dataPelanggan = $this->dataPelangganNew($query->Pelanggan);
        //$dataSalesman = $this->dataSalesman($query->salesman);   
        $dataTOPLimit = $this->listapproval($query->Pelanggan);

        $dtop = true;
        if ($dataTOPLimit->Umur_faktur < $query->TOP) {

            $out = $this->db->update('trs_sales_order', array('StatusTOP' => 'Ok' ), array('NoSO' => $noso ));
                       
        }else{
            $dtop = false; 
        }   

        if(!$dtop and $query->StatusTOP == "TOP" ){
            if($dataPelanggan->Buka_Top != 0){

            $out = $this->db->update('trs_sales_order', array('StatusTOP' => 'Ok' ), array('NoSO' => $noso ));
                if($out > 0){
                    $datatop = $dataPelanggan->Buka_Top - 1;
                    $out = $this->db->update('mst_pelanggan', array('Buka_Top' => $datatop ), array('Kode' => $query->Pelanggan ));        
                }
            }
        }


        if ($dataPelanggan->Limit_Kredit > $dataTOPLimit->saldo) {
            $out = $this->db->update('trs_sales_order', array('StatusLimit' => 'Ok' ), array('NoSO' => $noso ));
        } 
        return $valid;

    }

    public function dataSO($no = NULL)
    {
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and trs_sales_order.NoSO = '".$no."'";
        }
        $query = $this->db->query("select trs_sales_order.StatusTOP,trs_sales_order.StatusLimit,trs_sales_order.StatusDiscCab,trs_sales_order.StatusDiscPrins,trs_sales_order_detail.* ,'' NoOrder,'' Kota from trs_sales_order_detail left join trs_sales_order on  trs_sales_order.NoSO = trs_sales_order_detail.NoSO and  trs_sales_order.Cabang = trs_sales_order_detail.Cabang where trs_sales_order.Cabang = '".$this->cabang."' $byNo 
            UNION ALL
            SELECT trs_sales_order.StatusTOP,trs_sales_order.StatusLimit,trs_sales_order.StatusDiscCab,trs_sales_order.StatusDiscPrins,
            trs_sales_order_detail_bidan.* 
            FROM trs_sales_order_detail_bidan 
            left join trs_sales_order ON trs_sales_order.NoSO = trs_sales_order_detail_bidan.NoSO AND 
            trs_sales_order.Cabang = trs_sales_order_detail_bidan.Cabang WHERE trs_sales_order.Cabang = '".$this->cabang."' $byNo 
            ")->result();

        return $query;
    }

    public function setStok($NoDO = NULL)
    {  
        $status=true;            
        $produk = $this->db->query("select KodeProduk, Harga from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' group by KodeProduk")->result();
        $data = $this->db->query("select KodeProduk, QtyDO , BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,TotalCOGS,COGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'");
        $numdata=$data->num_rows();
        $detail=$data->result();
        if($numdata <=0){
            $status=false;
        }
        if($status==true){
                foreach ($produk as $kunci => $nilai) {
                    $summary = 0;
                    $product1 = $produk[$kunci]->KodeProduk;
                    $harga = $produk[$kunci]->Harga;
                    if (!empty($product1)) {
                        $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                        foreach ($detail as $key => $value) {
                            $product2 = $detail[$key]->KodeProduk;
                            if ($product1 == $product2) {
                                $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                                $valuestok = $detail[$key]->TotalCOGS;
                            }
                        }
                        $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                        if ($invsum->num_rows() > 0) {
                            $invsum = $invsum->row();  
                            $UnitStok = $invsum->UnitStok - $summary;
                            $valuestok = $UnitStok * $invsum->UnitCOGS;
                            $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                        }
                        // else{
                        //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                        // }
                    }
                }

                // save inventori history
                foreach ($detail as $key => $value) {   
                    $produk = $detail[$key]->KodeProduk;        
                    if (!empty($produk)) {
                        $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                        $valuestok = $detail[$key]->Gross;
                        $UnitStok = $detail[$key]->QtyDO + $detail[$key]->BonusDO;

                        $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', '-')");
                    }
                }

                // save inventori detail
                foreach ($detail as $key => $value) { 
                    $produk = $detail[$key]->KodeProduk;  
                    if (!empty($produk)) {
                        $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                        $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                        $dt = $invdet->row();
                        if ($invdet->num_rows() > 0) {
                            $stok = $dt->UnitStok;
                            $UnitStok = $stok - ($detail[$key]->QtyDO + $detail[$key]->BonusDO );
                            $cogs = $dt->UnitCOGS;
                            $ValueStok = $invsum->UnitCOGS * $UnitStok;
                            $this->db->set("UnitStok", $UnitStok);
                            $this->db->set("ValueStok", $ValueStok);
                            $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                            $this->db->set("ModifiedUser", $this->session->userdata('username'));
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang", $this->cabang);
                            $this->db->where("NoDokumen", $detail[$key]->NoBPB);
                            $this->db->where("BatchNo", $detail[$key]->BatchNo);
                            $this->db->where("Gudang", 'Baik');
                            $valid = $this->db->update('trs_invdet');
                            $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                            if($invdet->num_rows <= 0){
                                $this->db->set("ValueStok", 0);
                                $this->db->where("KodeProduk", $produk);
                                $this->db->where("Gudang", 'Baik');
                                $this->db->where("Tahun", date('Y'));
                                $this->db->where("Cabang",$this->cabang);
                                $valid = $this->db->update('trs_invsum');
                            }else{
                                $invdet = $invdet->row();
                                $this->db->set("ValueStok", $invdet->sumval);
                                // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                                $this->db->where("KodeProduk", $produk);
                                $this->db->where("Gudang", 'Baik');
                                $this->db->where("Tahun", date('Y'));
                                $this->db->where("Cabang",$this->cabang);
                                $valid = $this->db->update('trs_invsum');
                            }
                        }
                    }
                }

        }
        

    }

     public function updateDataSOPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoSO from trs_sales_order where Cabang = '".$this->cabang."' and Status ='Usulan'")->result();
                foreach ($nomor as $no) {
                    $query = $this->db2->query("select * from trs_sales_order_detail where NoSO = '".$no->NoSO."' and Status ='Usulan' ")->result();
                    foreach($query as $r) { // loop over results
                        $this->db->where('KodeProduk', $r->KodeProduk);
                        $this->db->where('Status', 'Usulan');
                        $this->db->where('NoSO', $no->NoSO);
                        $this->db->update('trs_sales_order_detail', $r); // insert each row to another table
                    }

                    $query2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no->NoSO."' and Status ='Usulan'")->row();
                    $this->db->where('NoSO', $no->NoSO);
                    $this->db->where('Status', 'Usulan');
                    $this->db->where('statusPusat', 'Berhasil');
                    $this->db->update('trs_sales_order', $query2); // insert each row to another table
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function rejectDataSO($NoSO = NULL,$status=NULL)
    {
        $query = $this->db->get_where('trs_sales_order', array('NoSO' => $NoSO))->row();
        $table = "trs_sales_order_detail";
        if ($query->Keterangan2 == "Bidan") {
            $table = "trs_sales_order_detail_bidan";
        }else{
            $table = "trs_sales_order_detail";
        }

        if($status == 'Reject'){
            $this->db->set("Status", "Reject");
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("alasan_status", "SO Batal");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->session->userdata('username'));
            $this->db->where('NoSO', $NoSO);
            $valid = $this->db->update('trs_sales_order');

            $this->db->set("Status", "Reject");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->session->userdata('username'));
            $this->db->where('NoSO', $NoSO);
        }else{
            $this->db->set("Status", "Closed");
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("alasan_status", "Closed By User");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->session->userdata('username'));
            $this->db->where('NoSO', $NoSO);
            $valid = $this->db->update('trs_sales_order');

            $this->db->set("Status", "Closed");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->session->userdata('username'));
            $this->db->where('NoSO', $NoSO);
        }
        
        $valid = $this->db->update($table);

        return $valid;
    }

    public function backStok($NoDO = NULL)
    {    
        $status=true;          
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
        $data = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,COGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'");
        $numdata = $data->num_rows();
        $detail = $data->result();
        if($numdata <= 0){
            $status=false;
        }
        if($status==true){
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->KodeProduk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    foreach ($detail as $key => $value) {
                        $product2 = $detail[$key]->KodeProduk;
                        if ($product1 == $product2) {
                            $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                            $valuestok = $detail[$key]->Gross;
                        }
                    }
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok + $summary;
                        $valuestok = $UnitStok * $invsum->UnitStok;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    }
                    // else{
                    //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                    // }
                }
            }

            // save inventori history
            foreach ($detail as $key => $value) {   
                $produk = $detail[$key]->KodeProduk;        
                if (!empty($produk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                    $valuestok = $detail[$key]->COGS;
                    $UnitStok = $detail[$key]->QtyDO+$detail[$key]->BonusDO;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
                }
            }

            // save inventori detail
            foreach ($detail as $key => $value) { 
                $produk = $detail[$key]->KodeProduk;  
                if (!empty($produk)) {
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                    $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                    $dt = $invdet->row();
                    if ($invdet->num_rows() > 0) {
                        $stok = $dt->UnitStok;
                        $UnitStok = $stok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                        $ValueStok = $UnitStok * $detail[$key]->COGS;

                        $this->db->set("UnitStok", $UnitStok);
                        $this->db->set("ValueStok", $ValueStok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("KodeProduk", $produk);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Gudang", "Baik");
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("NoDokumen", $detail[$key]->NoBPB);
                        $this->db->where("BatchNo", $detail[$key]->BatchNo);
                        $valid = $this->db->update('trs_invdet');
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db->set("ValueStok", 0);
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Gudang", 'Baik');
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }else{
                            $invdet = $invdet->row();
                            $this->db->set("ValueStok", $invdet->sumval);
                            $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db->where("KodeProduk", $produk);
                            $this->db->where("Gudang", 'Baik');
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }
                    }
                    // else{
                    //     $valuestok = ($detail[$key]->QtyDO  + $detail[$key]->BonusDO) * $detail[$key]->Harga;                
                    //     $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$detail[$key]->QtyDO + $detail[$key]->BonusDO."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$detail[$key]->Harga."','".$detail[$key]->NoBPB."')");
                    // }
                }
            }
        }
    }

    public function backStok_double($NoDO = NULL)
    {    
        $status=true;  

        // ====== untuk detail ======= //
        $produk = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'")->result();
        $data = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo, ExpDate,NoBPB,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'");

        $numdata = $data->num_rows();
        $detail = $data->result();
        if($numdata <= 0){
            $status=false;
        }
        if($status==true){
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->KodeProduk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product1."'")->row();
                    foreach ($detail as $key => $value) {
                        $product2 = $detail[$key]->KodeProduk;
                        if ($product1 == $product2) {
                            $summary = $summary + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                            $valuestok = $detail[$key]->Gross;
                        }
                    }

                }
            }


            // save inventori history
            foreach ($detail as $key => $value) {   
                $produk = $detail[$key]->KodeProduk;        
                if (!empty($produk)) {
                    $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                    $valuestok = $detail[$key]->TotalCOGS;
                    $UnitStok = $detail[$key]->QtyDO+$detail[$key]->BonusDO;

                    $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
                }
            }

            // save inventori detail
            foreach ($detail as $key => $value) { 
                $produk = $detail[$key]->KodeProduk;  
                if (!empty($produk)) {
                    /*$invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();*/
                    $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."'  and Tahun = '".date('Y')."' and NoDokumen='".$detail[$key]->NoBPB."' and Gudang='Baik' limit 1");
                    $dt = $invdet->row();
                    if ($invdet->num_rows() > 0) {
                        $stok = $dt->UnitStok;
                        $UnitStok = $stok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                        $ValueStok = $dt->ValueStok + $detail[$key]->TotalCOGS;

                        $this->db->set("UnitStok", $UnitStok);
                        $this->db->set("ValueStok", $ValueStok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("KodeProduk", $produk);
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Gudang", "Baik");
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("NoDokumen", $detail[$key]->NoBPB);
                        $this->db->where("BatchNo", $detail[$key]->BatchNo);
                        $valid = $this->db->update('trs_invdet');
                        
                    }
                }
            }

             // ===== untuk summary ===== //
            /*$produk_sum = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' GROUP BY KodeProduk")->result();*/
            $data_sum = $this->db->query("select KodeProduk, sum(QtyDO) QtyDO, sum(BonusDO) BonusDO,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' GROUP BY KodeProduk");

            $numdata_sum = $data_sum->num_rows();
            $detail_sum = $data_sum->result();

            foreach ($detail_sum as $r) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$r->KodeProduk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");

                $summary = $r->QtyDO + $r->BonusDO;

                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok = $invsum->ValueStok + $r->TotalCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$r->KodeProduk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }
            }

            // ==== untuk summary ==== //
            foreach ($detail_sum as $r) {
                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$r->KodeProduk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");

                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $r->KodeProduk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $this->db->set("ValueStok", $invdet->sumval);
                    $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                    $this->db->where("KodeProduk", $r->KodeProduk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }
            }
        }
    }

    public function cekStok($produk=null,$Batch=null,$batchDoc=null,$qty=null)
    {
        $result = TRUE;
        $s = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$Batch."' and NoDokumen='".$batchDoc."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
        $stok = $s->UnitStok;
        // if ($qty > $stok) {
        //     $result = FALSE;
        // }
        return $stok;
    }
    public function updateStok($nodo=null,$produk=null,$Batch=null,$batchDoc=null,$qty=null,$harga=null,$gross=null,$expdate=null)
    {  
        $summary = 0;
        if (!empty($produk)) {
            $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
            $summary = $summary + $qty;
            $valuestok = $gross;
            $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
            if ($invsum->num_rows() > 0) {
                $invsum = $invsum->row();  
                $UnitStok = $invsum->UnitStok - $summary;
                if($UnitStok == 0){
                    $valuestok = 0;
                }else{
                    $valuestok = $invsum->ValueStok - $gross;
                }
                $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
            }
                        
        }      
        if(!empty($produk)) {
            $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
            $valuestok = $gross;
            $UnitStok = $qty;

            $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$Batch."', '".$expdate."', 'Baik', 'DO', '".$nodo."', '-')");
        }
        // save inventori detail
        if (!empty($produk)) {
            $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
            $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$Batch."'  and Tahun = '".date('Y')."' and NoDokumen='".$batchDoc."' and Gudang='Baik' limit 1");
            $dt = $invdet->row();
            if ($invdet->num_rows() > 0) {
                $stok = $dt->UnitStok;
                $UnitStok = $stok - $qty;
                $cogs = $dt->UnitCOGS;
                if($UnitStok == 0){
                    $ValueStok = 0;
                }else{
                    $ValueStok = $invsum->ValueStok - $gross;
                }
                $this->db->set("UnitStok", $UnitStok);
                $this->db->set("ValueStok", $ValueStok);
                $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->where("KodeProduk", $produk);
                $this->db->where("Tahun", date('Y'));
                $this->db->where("Cabang", $this->cabang);
                $this->db->where("NoDokumen", $batchDoc);
                $this->db->where("BatchNo", $Batch);
                $this->db->where("Gudang", 'Baik');
                $valid = $this->db->update('trs_invdet');
                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $this->db->set("ValueStok", $invdet->sumval);
                    // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }
            }
        }            
    }

    public function listsoulang()
    {   
        $query = $this->db->query("select NoSO,TglSO,Pelanggan from trs_sales_order where Cabang = '".$this->cabang."'  and alasan_status like '%Terpenuhi%' and NoSO NOT IN (select Acu from trs_sales_order where Cabang = '".$this->cabang."') order by NoSO asc")->result();

        return $query;
    }
    public function getdataorder($noso=null)
    {   
        $query = $this->db->query("select trs_sales_order.ppn_pelanggan,trs_sales_order_detail.* from trs_sales_order 
                                    left join trs_sales_order_detail
                                    on trs_sales_order.Cabang = trs_sales_order_detail.Cabang and 
                                       trs_sales_order.NoSO = trs_sales_order_detail.NoSO
                                     where trs_sales_order.Cabang = '".$this->cabang."' and 
                                           trs_sales_order.NoSO = '".$noso."'  and 
                                           (trs_sales_order.alasan_status like '%Terpenuhi%' or ifnull(Approve_BM,'N') = 'R' or  ifnull(Approve_TOP_BM,'N') = 'R' or ifnull(Approve_DiscCab_BM,'N') = 'R' or ifnull(Approve_DiscPrins_BM,'N') = 'R')
                                    order by trs_sales_order.NoSO asc")->result();
        return $query;
    }

    public function datapiutangPelanggan($kode = NULL)
    {   
        $header = $this->db->query("select mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.Umur_faktur
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur 
                                      where (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0) and Status not in ('Usulan','Batal','Reject') 
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$kode."'")->result();

        /*$query = $this->db->query("SELECT DISTINCT trs_faktur.`NoFaktur`,
                                           trs_faktur.`TglFaktur`,
                                           (case when trs_faktur.TipeDokumen ='Faktur' then 'F'
                                                 when trs_faktur.TipeDokumen ='Retur' then 'R'
                                                 when trs_faktur.TipeDokumen ='CN' then 'CN'
                                                 when trs_faktur.TipeDokumen ='DN' then 'DN' 
                                                 else '' end ) as 'TipeDokumen',
                                           trs_faktur.`TglJtoFaktur`,
                                           CONCAT(trs_faktur.`Pelanggan`,' - ',trs_faktur.`NamaPelanggan`) AS 'Pelanggan',
                                           trs_faktur.`AlamatFaktur`,
                                           trs_faktur.`CaraBayar`,
                                           DATEDIFF(curdate(),trs_faktur.`TglFaktur`) AS 'Umur_faktur',
                                           trs_faktur.`Total`,
                                          (case when trs_faktur.`Saldo` = trs_faktur.`Total` then trs_faktur.`Saldo` 
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) != ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + trs_faktur.saldo_giro
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) = ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + ifnull(giro.bayargiro,0) else trs_faktur.`Saldo`
                                                end ) as 'Saldo',
                                           IFNULL(pelunasan.bayar,0) AS 'pelunasan',
                                           giro.giro AS 'Nogiro',
                                           giro.TglGiro AS 'TglGiro',
                                           giro.tglJTO AS 'tglJTO',
                                           ifnull(giro.bayargiro,0) as 'bayargiro'
                                     FROM  trs_faktur LEFT JOIN 
                                        ( SELECT trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`,
                                             SUM(trs_pelunasan_detail.`ValuePelunasan`) AS 'bayar'
                                          FROM trs_pelunasan_detail
                                          where  trs_pelunasan_detail.status != 'Batal'
                                          GROUP BY trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`) AS pelunasan ON pelunasan.NomorFaktur = trs_faktur.`NoFaktur` AND pelunasan.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                                    trs_pelunasan_giro_detail.Status,
                                           trs_pelunasan_giro_detail.`TglPelunasan` as 'TglGiro',
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            trs_pelunasan_giro_detail.`Giro`,
                                            trs_pelunasan_giro_detail.`ValuePelunasan` AS 'bayargiro',
                                            trs_giro.tglJTO as 'tglJTO'
                                         FROM trs_pelunasan_giro_detail left join 
                                              trs_giro on trs_pelunasan_giro_detail.`Giro` = trs_giro.Nogiro
                                         where trs_pelunasan_giro_detail.Status != 'Closed'
                                          ) AS giro ON giro.NomorFaktur = trs_faktur.`NoFaktur` AND giro.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(ifnull(trs_pelunasan_giro_detail.`ValuePelunasan`,0)) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON sumgiro.NomorFaktur = trs_faktur.`NoFaktur` AND sumgiro.KodePelanggan = trs_faktur.`Pelanggan` 
                                    WHERE (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0)  AND 
                                       trs_faktur.Status != 'Usulan' and
                                       trs_faktur.`Pelanggan` = '".$kode."'
                                    order by Umur_faktur Desc")->result();*/
        $data = array("query" =>"", "header" =>$header);

        return $data;
    }

    public function datapiutangPelanggan_detail($kode = NULL)
    {   
       /* $header = $this->db->query("select mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.Umur_faktur
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur 
                                      where (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0) and 
                                        trs_faktur.Status != 'Usulan'
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$kode."'")->result();*/

        $query = $this->db->query("SELECT DISTINCT trs_faktur.`NoFaktur`,
                                           trs_faktur.`TglFaktur`,
                                           (case when trs_faktur.TipeDokumen ='Faktur' then 'F'
                                                 when trs_faktur.TipeDokumen ='Retur' then 'R'
                                                 when trs_faktur.TipeDokumen ='CN' then 'CN'
                                                 when trs_faktur.TipeDokumen ='DN' then 'DN' 
                                                 else '' end ) as 'TipeDokumen',
                                           trs_faktur.`TglJtoFaktur`,
                                           CONCAT(trs_faktur.`Pelanggan`,' - ',trs_faktur.`NamaPelanggan`) AS 'Pelanggan',
                                           trs_faktur.`AlamatFaktur`,
                                           trs_faktur.`CaraBayar`,
                                           DATEDIFF(curdate(),trs_faktur.`TglFaktur`) AS 'Umur_faktur',
                                           trs_faktur.`Total`,
                                          (case when trs_faktur.`Saldo` = trs_faktur.`Total` then trs_faktur.`Saldo` 
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) != ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + trs_faktur.saldo_giro
                                                when (giro.status != 'Closed' and ifnull(giro.bayargiro,0) > 0 and (trs_faktur.`Total` - IFNULL(pelunasan.bayar,0) = ifnull(giro.bayargiro,0))) then trs_faktur.`Saldo` + ifnull(giro.bayargiro,0) else trs_faktur.`Saldo`
                                                end ) as 'Saldo',
                                           IFNULL(pelunasan.bayar,0) AS 'pelunasan',
                                           giro.giro AS 'Nogiro',
                                           giro.TglGiro AS 'TglGiro',
                                           giro.tglJTO AS 'tglJTO',
                                           ifnull(giro.bayargiro,0) as 'bayargiro'
                                     FROM  trs_faktur LEFT JOIN 
                                        ( SELECT trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`,
                                             SUM(trs_pelunasan_detail.`ValuePelunasan`) AS 'bayar'
                                          FROM trs_pelunasan_detail
                                          where  trs_pelunasan_detail.status != 'Batal'
                                          GROUP BY trs_pelunasan_detail.`NomorFaktur`,
                                             trs_pelunasan_detail.`KodePelanggan`) AS pelunasan ON pelunasan.NomorFaktur = trs_faktur.`NoFaktur` AND pelunasan.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                                    trs_pelunasan_giro_detail.Status,
                                           trs_pelunasan_giro_detail.`TglPelunasan` as 'TglGiro',
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            trs_pelunasan_giro_detail.`Giro`,
                                            trs_pelunasan_giro_detail.`ValuePelunasan` AS 'bayargiro',
                                            trs_giro.tglJTO as 'tglJTO'
                                         FROM trs_pelunasan_giro_detail left join 
                                              trs_giro on trs_pelunasan_giro_detail.`Giro` = trs_giro.Nogiro
                                         where trs_pelunasan_giro_detail.Status != 'Closed'
                                          ) AS giro ON giro.NomorFaktur = trs_faktur.`NoFaktur` AND giro.KodePelanggan = trs_faktur.`Pelanggan`
                                        LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(ifnull(trs_pelunasan_giro_detail.`ValuePelunasan`,0)) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`NomorFaktur`,
                                            trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON sumgiro.NomorFaktur = trs_faktur.`NoFaktur` AND sumgiro.KodePelanggan = trs_faktur.`Pelanggan` 
                                    WHERE (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0)  and trs_faktur.Status not in ('Usulan','Batal','Reject') and
                                       trs_faktur.`Pelanggan` = '".$kode."'
                                    order by Umur_faktur Desc")->result();
        $data = array("query" =>$query);

        return $data;
    }
    public function saveDataUsulanDiskonPrins($params = null)
    {
        $valid = false;
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Usulan Diskon Prins');
        $data = $this->db->query("select max(right(No_Usulan,7)) as 'no' from mst_usulan_disc_prinsipal where Cabang = '".$this->cabang."' and YEAR(tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == "" ){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        $xpldprins = explode("~", $params->prinsipal);
        $kodePrins = $xpldprins[0];
        $namaPrins = $xpldprins[1];

        if($params->jmlpelanggan == 'All'){
            $kodePelanggan = '';
            $namaPelanggan = '';
        }else{
            $xpldPelanggan = explode("-", $params->pelangganMask);
            $kodePelanggan = $xpldPelanggan[0];
            $namaPelanggan = $xpldPelanggan[1];
        }

        foreach ($params->produk as $key => $value) 
        {
            if (!empty($params->produk[$key])) 
            {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $namaProduk = $expld[1]; 

                $this->db->set("cabang", $this->cabang);
                $this->db->set("tanggal", date("Y-m-d"));
                $this->db->set("time_trans", date("Y-m-d H:i:s"));
                $this->db->set("No_Usulan", $noDokumen);
                $this->db->set("Tipe_trans", $params->tipe_trans);
                $this->db->set("Prinsipal", $kodePrins);
                $this->db->set("Prinsipal2", $namaPrins);
                $this->db->set("Supplier", $params->Supplier);
                $this->db->set("KodeProduk", $produk);
                $this->db->set("NamaProduk", $namaProduk);
                $this->db->set("tglmulai", $params->tgl1);
                $this->db->set("tglakhir",$params->tgl2);
                $this->db->set("qty1", $params->qty1[$key]);
                $this->db->set("qty2", $params->qty2[$key]);
                $this->db->set("disc_prins1", $params->dscprins1[$key]);
                $this->db->set("disc_prins2", $params->dscprins2[$key]);
                $this->db->set("disc_cab", $params->dsccab1[$key]);
                $this->db->set("KodePelanggan", $kodePelanggan);
                $this->db->set("NamaPelanggan", $namaPelanggan);
                $this->db->set("status", "Open");
                $this->db->set("status_pelanggan", $params->jmlpelanggan);
                $this->db->set("created_by", $this->session->userdata('username'));
                $this->db->set("created_at", date("Y-m-d H:i:s"));
                $valid =  $this->db->insert("mst_usulan_disc_prinsipal");
                if($valid){
                    $this->prosesDataUsulanDiscPrins($noDokumen,$produk);
                }
            }
        } 
        return $valid;
    }
    public function prosesDataUsulanDiscPrins($no = NULL,$produk=NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        
        $query = $this->db->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->result(); 
        $query2 = $this->db->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->row(); 
        // $status = $query2->Status;    
        if ($this->db2->conn_id == TRUE) {
            $cek = $this->db2->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$no."' and KodeProduk = '".$produk."'")->num_rows();
            if ($cek == 0) {
                $this->db2->insert('mst_usulan_disc_prinsipal', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('No_Usulan', $no);
                $this->db2->update('mst_usulan_disc_prinsipal', $query2);
            }
            $update = $this->db->query("update mst_usulan_disc_prinsipal set statusPusat = 'Berhasil' where No_Usulan = '".$no."'");
            return TRUE;
        }
        else{
            $this->db->query("update mst_usulan_disc_prinsipal set statusPusat = 'Gagal' where No_Usulan = '".$no."'");
            return FALSE;
        }
    }

    public function listDataApprovalDP($search=null,$byLimit=null)
    {
        $byStatus = "";
        
        if($this->group == "BM"){
            $byStatus = "ifnull(Approved_BM,'') = '' and ifnull(Status,'') != 'Terpakai'";
        }else{
            $byStatus = "ifnull(Status,'') in ('Open','Terpakai')";
        }
         $query = $this->db->query("select * from mst_usulan_disc_prinsipal where $byStatus $search order by tanggal DESC,No_Usulan ASC $byLimit");

        return $query;
    }
    public function prosesDataApprovalDP($NoUsulan=null,$KodeProduk=null,$status=null)
    {
       $this->db2 = $this->load->database('pusat', TRUE); 
        if ($this->group == "BM") {
            if($status =='Approve'){
                $approve_DiscPrins  = "Y";
            }else if($status =='Reject'){
                $approve_DiscPrins  = "R";
            }
            $date_approve_DiscPrins = date('Y-m-d H:i:s');
            $user_DiscPrins = $this->session->userdata('username');
            $update = $this->db->query("update mst_usulan_disc_prinsipal 
                                            set status = 'Open', 
                                                statusPusat = 'Berhasil',
                                                Approved_BM = '".$approve_DiscPrins."', 
                                                DateApprove_BM = '".$date_approve_DiscPrins."',
                                                modified_at = '".date('Y-m-d H:i:s')."',
                                                modified_by = '".$user_DiscPrins."',
                                                user_BM = '".$user_DiscPrins."' 
                                        where No_Usulan = '".$NoUsulan."' and 
                                              KodeProduk = '".$KodeProduk."' and 
                                              Cabang ='".$this->cabang."'"); 
                if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update mst_usulan_disc_prinsipal 
                                            set status = 'Open', 
                                                statusPusat = 'Berhasil',
                                                Approved_BM = '".$approve_DiscPrins."', 
                                                DateApprove_BM = '".$date_approve_DiscPrins."',
                                                modified_at = '".date('Y-m-d H:i:s')."',
                                                modified_by = '".$user_DiscPrins."',
                                                user_BM = '".$user_DiscPrins."' 
                                        where No_Usulan = '".$NoUsulan."' and 
                                              KodeProduk = '".$KodeProduk."' and 
                                              Cabang ='".$this->cabang."'");
                    } 
                $message = "Sukses";                 
            }else{
                $update = false;
                $message = "no_Approve";
            }      
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function getProdukDiscPrins($kode = NULL,$kodepel = NULL)
    {   
        $num = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPF' and ifnull(status,'')='Open' and Cabang ='".$this->session->userdata('cabang')."' and ifnull(Approved_prins,'')='Y' and KodePelanggan ='".$kodepel."' "); 
        $num_query = $num->num_rows();
        if ($num_query <= 0) {
            $query = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPL' and curdate() >= tglmulai and curdate() <= tglakhir and Cabang ='".$this->session->userdata('cabang')."' and ifnull(Approved_prins,'')='Y' and KodePelanggan ='".$kodepel."'  limit 1")->row(); 
        }else{
            $query = $this->db->query("select * from mst_usulan_disc_prinsipal where KodeProduk ='".$kode."' and ifnull(Tipe_trans,'') = 'DPF' and ifnull(status,'')='Open' and curdate() >= tglmulai and curdate() <= tglakhir and Cabang ='".$this->session->userdata('cabang')."' and ifnull(Approved_prins,'')='Y' and KodePelanggan ='".$kodepel."'  limit 1")->row(); 
        } 
        return $query;
    }

    //diskon cabang khusus >> UJ 20190716
    public function getProdukDiscCabs($kode = NULL,$kodepel = NULL)
    {   

            $query = $this->db->query("SELECT * FROM `mst_diskon_jual_cabang_khusus` WHERE Cabang='".$this->session->userdata('cabang')."' AND KodePelanggan = '".$kodepel."' AND KodeProduk='".$kode."' AND App1='Y' AND APP2='Y' AND (Periode1 <= curdate()  AND Periode2 >= curdate()) limit 1")->row(); 
        return $query;
    }

    public function updateDataDiscPrinsPusat(){

        $this->db2 = $this->load->database('pusat', TRUE);   
    
        $disc = $this->db->query("select * from mst_usulan_disc_prinsipal where ifnull(Approved_prins,'') ='' and 
            (case when Tipe_trans = 'DPF' then ifnull(status,'') ='Open' else ifnull(status,'') in ('Open','Terpakai') end) ")->result();
        $No = "";
        if ($this->db2->conn_id == TRUE) {
            foreach ($disc as $list) {
               $No = $list->No_Usulan;
               $KodeProduk = $list->KodeProduk;
               $disprins_pusat = $this->db2->query("select * from mst_usulan_disc_prinsipal where No_Usulan = '".$list->No_Usulan."' and Cabang ='".$this->cabang."' and KodeProduk ='".$KodeProduk."'");
               $num_prins  = $disprins_pusat->num_rows();
               $data_prins = $disprins_pusat->result();
               if($num_prins > 0){
                    $this->db->set('Status', 'Open');
                    $this->db->set('status_pelanggan', $data_prins[0]->status_pelanggan);
                    $this->db->set('Approved_prins', $data_prins[0]->Approved_prins);
                    $this->db->set('DateApprove_prins', $data_prins[0]->DateApprove_prins);
                    $this->db->set('user_prins', $data_prins[0]->user_prins);
                    $this->db->where('No_Usulan',$list->No_Usulan);
                    $this->db->where('KodeProduk',$list->KodeProduk);
                    $this->db->where('Cabang',$this->cabang);
                    $this->db->where('Status','Open');
                    $this->db->update('mst_usulan_disc_prinsipal'); 
               }
            }
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }

    }
    public function prosesUlangDataSO($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row(); 

        $table = "trs_sales_order_detail";
        
        if ($query2->Keterangan2 == 'Bidan') {
            $table = "trs_sales_order_detail_bidan";
        }else{
            $table = "trs_sales_order_detail";
        }

        $query = $this->db->query("select * from $table where NoSO = '".$no."'")->result(); 
        $status = $query2->Status; 

        if ($this->db2->conn_id == TRUE) {
            $cek = $this->db2->query("select * from $table where NoSO = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert($table, $r); // insert each row to another table
                }
            }
            else{
                foreach($query as $r) { // loop over results
                    $this->db2->where('KodeProduk', $r->KodeProduk);
                    $this->db2->where('NoSO', $no);
                    $this->db2->update($table, $r);
                }
            }
            $cek2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_sales_order', $query2); // insert each row to another table
            }
            else{
                // foreach($query2 as $r) {
                    $this->db2->set('StatusTOP', $query2->StatusTOP);
                    $this->db2->set('StatusLimit', $query2->StatusLimit);
                    $this->db2->set('StatusDiscCab', $query2->StatusDiscCab);
                    $this->db2->set('StatusDiscPrins', $query2->StatusDiscPrins);
                    $this->db2->set('umur_piutang_ksa', $query2->umur_piutang_ksa);
                    $this->db2->set('umur_piutang_bm', $query2->umur_piutang_bm);
                    $this->db2->set('limit_piutang_ksa', $query2->limit_piutang_ksa);
                    $this->db2->set('limit_piutang_bm', $query2->limit_piutang_bm);
                    $this->db2->set('Approve_KSA', $query2->Approve_KSA);
                    $this->db2->set('Approve_BM', $query2->Approve_BM);
                    $this->db2->set('Approve_TOP_KSA', $query2->Approve_TOP_KSA);
                    $this->db2->set('Approve_TOP_BM', $query2->Approve_TOP_BM);
                    $this->db2->set('Approve_DiscCab_KSA',$query2->Approve_DiscCab_KSA);
                    $this->db2->set('Approve_DiscCab_BM', $query2->Approve_DiscCab_BM);
                    $this->db2->set('Approve_DiscPrins_KSA',$query2->Approve_DiscPrins_KSA);
                    $this->db2->set('Approve_DiscPrins_BM', $query2->Approve_DiscPrins_BM);
                    $this->db2->where('NoSO', $no);
                    $this->db2->where('Status', 'Usulan');
                    $this->db2->update('trs_sales_order');
                // }
            }
            $update = $this->db->query("update trs_sales_order set statusPusat = 'Berhasil' where NoSO = '".$no."'");
            return TRUE;
        }
        else{
            $this->db->query("update trs_sales_order set statusPusat = 'Gagal' where NoSO = '".$no."'");
            return FALSE;
        }
    }

    public function viewapproval($no = NULL)
    {
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and NoSO = '".$no."'";
        }
        log_message("error",print_r($no,true));
        $this->db2 = $this->load->database('pusat', TRUE); 
        if ($this->db2->conn_id == TRUE) {
            $query = $this->db2->query("select Cabang,NoSO,
                                          IFNULL(Approve_KSA,'N') AS 'Approve_KSA',
                                          IFNULL(user_KSA,'N') AS 'user_KSA', 
                                          IFNULL(Approve_BM,'N') AS 'Approve_BM',
                                          IFNULL(user_BM,'N') AS 'user_BM',
                                          IFNULL(Approve_RBM,'N') AS 'Approve_RBM',
                                          IFNULL(user_RBM,'N') AS 'user_RBM',
                                          IFNULL(Approve_pusat,'N') AS 'Approve_pusat',
                                          IFNULL(user_pusat,'N') AS 'user_pusat',
                                          IFNULL(Approve_TOP_KSA,'N') AS 'Approve_TOP_KSA',
                                          IFNULL(user_TOP_KSA,'N') AS 'user_TOP_KSA',
                                          IFNULL(Approve_TOP_BM,'N') AS 'Approve_TOP_BM',
                                          IFNULL(user_TOP_BM,'N') AS 'user_TOP_BM',
                                          IFNULL(Approve_TOP_RBM,'N') AS 'Approve_TOP_RBM',
                                          IFNULL(user_TOP_RBM,'N') AS 'user_TOP_RBM',
                                          IFNULL(Approve_TOP_pusat,'N') AS 'Approve_TOP_pusat',
                                          IFNULL(user_TOP_pusat,'N') AS 'user_TOP_pusat',
                                          IFNULL(Approve_DiscCab_KSA,'N') AS 'Approve_DiscCab_KSA',
                                          IFNULL(user_DiscCab_KSA,'N') AS 'user_DiscCab_KSA',
                                          IFNULL(Approve_DiscCab_BM,'N') AS 'Approve_DiscCab_BM',
                                          IFNULL(date_approve_DiscCab_BM,'N') AS 'date_approve_DiscCab_BM',
                                          IFNULL(user_DiscCab_BM,'N') AS 'user_DiscCab_BM',
                                          IFNULL(Approve_DiscCab_RBM,'N') AS 'Approve_DiscCab_RBM',
                                          IFNULL(user_DiscCab_RBM,'N') AS 'user_DiscCab_RBM',
                                          IFNULL(Approve_DiscCab_pusat,'N') AS 'Approve_DiscCab_pusat',
                                          IFNULL(user_DiscCab_pusat,'N') AS 'user_DiscCab_pusat',
                                          IFNULL(Approve_DiscPrins_KSA,'N') AS 'Approve_DiscPrins_KSA',
                                          IFNULL(user_DiscPrins_KSA,'N') AS 'user_DiscPrins_KSA',
                                          IFNULL(Approve_DiscPrins_BM,'N') AS 'Approve_DiscPrins_BM',
                                          IFNULL(user_DiscPrins_BM,'N') AS 'user_DiscPrins_BM',
                                          IFNULL(Approve_DiscPrins_RBM,'N') AS 'Approve_DiscPrins_RBM',
                                          IFNULL(user_DiscPrins_RBM,'N') AS 'user_DiscPrins_RBM',
                                          IFNULL(Approve_DiscPrins_pusat,'N') AS 'Approve_DiscPrins_pusat',
                                          IFNULL(user_DiscPrins_pusat,'N') AS 'user_DiscPrins_pusat' 
                                    from trs_sales_order where Cabang = '".$this->cabang."' $byNo ")->result();
            return $query;
        }
        
    }

    public function updateDataSOapprovalbyno($no=NULL){

        $this->db2 = $this->load->database('pusat', TRUE);   
        // $so = $this->db->query("select * from trs_sales_order where status not in ('Closed','Reject') and ifnull(NoDO,'') = '' ")->result();
        // $No = "";
        if ($this->db2->conn_id == TRUE) {
             // foreach ($so as $list) {

               // $No = $list->NoSO;
               $so_pusat = $this->db2->query("select * from trs_sales_order where NoSO = '".$no."'");
               $num_so    = $so_pusat->num_rows();
               $data_so = $so_pusat->result();
                if($num_so == 0){
                    $query = $this->db->query("select * from trs_sales_order_detail where NoSO = '".$no."'")->result();
                            foreach($query as $r) { // loop over results
                                $this->db2->insert('trs_sales_order_detail', $r); // insert each row to another table
                            }

                    $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row();
                            $this->db2->insert('trs_sales_order', $query2); 
                }else{
                    $list = $this->db->query("select * from trs_sales_order where NoSO = '".$no."' limit 1")->row();
                    if($list != ""){
                        if($list->StatusLimit != 'Ok'){
                            $this->db->set('StatusLimit', $data_so[0]->StatusLimit);
                        }
                        if($list->StatusTOP != 'Ok'){
                            $this->db->set('StatusTOP', $data_so[0]->StatusTOP);
                        }
                        if($list->StatusDiscCab != 'Ok'){
                           $this->db->set('StatusDiscCab', $data_so[0]->StatusDiscCab);
                        }
                        if($list->StatusDiscPrins != 'Ok'){
                           $this->db->set('StatusDiscPrins', $data_so[0]->StatusDiscPrins);
                        }
                    }
                    
                    $this->db->set('Status', $data_so[0]->Status);
                    $this->db->set('Approve_RBM', $data_so[0]->Approve_RBM);
                    if($data_so[0]->Approve_RBM == 'Y' and (empty($list->date_approve_RBM_cab) or  $list->date_approve_RBM_cab =="")){
                        $this->db->set('date_approve_RBM_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_RBM', $data_so[0]->user_RBM);
                    $this->db->set('Approve_pusat', $data_so[0]->Approve_pusat);
                    if($data_so[0]->Approve_pusat == 'Y' and (empty($list->date_approve_pusat_cab) or  $list->date_approve_pusat_cab == "")){
                        $this->db->set('date_approve_pusat_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_pusat', $data_so[0]->user_pusat);
                    $this->db->set('Approve_TOP_RBM', $data_so[0]->Approve_TOP_RBM);
                    $this->db->set('user_TOP_RBM', $data_so[0]->user_TOP_RBM);
                    if($data_so[0]->Approve_TOP_RBM == 'Y' and (empty($list->date_approve_TOP_RBM_cab) or$list->date_approve_TOP_RBM_cab  == "")){
                        $this->db->set('date_approve_TOP_RBM_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('Approve_TOP_pusat', $data_so[0]->Approve_TOP_pusat);
                    if($data_so[0]->Approve_TOP_pusat == 'Y' and (empty($list->date_approve_TOP_pusat_cab) or $list->date_approve_TOP_pusat_cab == "")){
                        $this->db->set('date_approve_TOP_pusat_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_TOP_pusat', $data_so[0]->user_TOP_pusat);
                    $this->db->set('Approve_DiscCab_RBM', $data_so[0]->Approve_DiscCab_RBM);
                    $this->db->set('user_DiscCab_RBM', $data_so[0]->user_DiscCab_RBM);
                    if($data_so[0]->Approve_DiscCab_RBM == 'Y' and (empty($list->date_approve_DiscCab_RBM_cab) or  $list->date_approve_DiscCab_RBM_cab  == "")){
                        $this->db->set('date_approve_DiscCab_RBM_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('Approve_DiscCab_pusat', $data_so[0]->Approve_DiscCab_pusat);
                    $this->db->set('user_DiscCab_pusat', $data_so[0]->user_DiscCab_pusat);
                    $this->db->set('Approve_DiscPrins_RBM', $data_so[0]->Approve_DiscPrins_RBM);
                    if($data_so[0]->Approve_DiscPrins_pusat == 'Y' and (empty($list->date_approve_DiscPrins_pusat_cab) or $list->date_approve_DiscPrins_pusat_cab == "")){
                        $this->db->set('date_approve_DiscPrins_pusat_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_DiscPrins_RBM', $data_so[0]->user_DiscPrins_RBM);
                    $this->db->set('Approve_DiscPrins_pusat', $data_so[0]->Approve_DiscPrins_pusat);
                    $this->db->set('user_DiscPrins_pusat', $data_so[0]->user_DiscPrins_pusat);
                    $this->db->where('NoSO',$no);
                    $this->db->where('Status',"Usulan");
                    $this->db->where('Cabang',$data_so[0]->Cabang);
                    $this->db->where('NoDO','');
                    $this->db->update('trs_sales_order');       
                }

               //  $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$no."'")->row();

               //   $cek2 = $this->db2->query("select * from trs_sales_order where NoSO = '".$no."'")->num_rows();
               // if ($cek2 == 0) {
               //     $this->db2->insert('trs_sales_order', $query2); // insert each row to another table
               // }
               // else{
               //     // foreach($query2 as $r) {
               //         $this->db2->set('Approve_KSA', $query2->Approve_KSA);
               //         $this->db2->set('Approve_BM', $query2->Approve_BM);
               //         $this->db2->set('Approve_TOP_KSA', $query2->Approve_TOP_KSA);
               //         $this->db2->set('Approve_TOP_BM', $query2->Approve_TOP_BM);
               //         $this->db2->set('Approve_DiscCab_KSA',$query2->Approve_DiscCab_KSA);
               //         $this->db2->set('Approve_DiscCab_BM', $query2->Approve_DiscCab_BM);
               //         $this->db2->set('Approve_DiscPrins_KSA',$query2->Approve_DiscPrins_KSA);
               //         $this->db2->set('Approve_DiscPrins_BM', $query2->Approve_DiscPrins_BM);
               //         $this->db2->where('NoSO', $no);
               //         $this->db2->update('trs_sales_order');
               //     // }
               // }
               return true;


            // }
        }//
        else{
            return 'GAGAL';
        }

    }
    public function Pelanggan2()
    {   
        // $this->db3 = $this->load->database('mitra', TRUE); 
        $query = $this->db->query("select Cabang,Kode from mretail where Cabang LIKE '%".$this->cabang."%' ORDER BY Kode")->result();
        return $query;
    }

    public function dataretail()
    {   
        // $this->db3 = $this->load->database('mitra', TRUE); 
        $query = $this->db->query("select Cabang,Kode,`AddedTime` as 'addedTime',`ModifiedTime` as 'ModifiedTime' from mretail where Cabang like '%".$this->cabang."%' ORDER BY Kode");
        return $query;
    }

    public function updateDataretail()
    {   
        $this->db3 = $this->load->database('mitra', TRUE);      
        if ($this->db3->conn_id == TRUE) {
                $nomor = $this->db3->query("select * from mretail where Cabang LIKE '".$this->cabang."%' ")->result();
                $string = '';
                $this->db->truncate("mretail");
                foreach ($nomor as $r) {
                    /*$query = $this->db->query("select * from mretail where Cabang like '%".$this->cabang."%' and kode ='".$no->Kode."' ")->num_rows();
                    $data = $this->db->query("select * from mretail where Cabang like '%".$this->cabang."%' and kode ='".$no->Kode."' ")->row();
                    if($query == 0){
                        $this->db->insert('mretail', $no);
                    }else{
                        $this->db->where('Cabang', $this->cabang);
                        $this->db->where('kode', $no->Kode);
                        $this->db->update('mretail', $no);
                    }
                */
                    $AddedTime              = ($r->AddedTime == null) ? "null" : "'".$r->AddedTime."'";
                    $ModifiedTime           = ($r->ModifiedTime == null) ? "null" : "'".$r->ModifiedTime."'";
                    $Cabang                 = ($r->Cabang == null) ? "null" : "'".$r->Cabang."'";
                    $Area                   = ($r->Area == null) ? "null" : "'".$r->Area."'";
                    $Pelanggan              = ($r->Pelanggan == null) ? "null" : "'".str_replace(["'",","], " ", $r->Pelanggan)."'";
                    $Nama_Pelanggan         = ($r->Nama_Pelanggan == null) ? "null" : "'".str_replace(["'",","], " ", $r->Nama_Pelanggan)."'";
                    $Kode_Reps              = ($r->Kode_Reps == null) ? "null" : "'".$r->Kode_Reps."'";
                    $Kode                   = ($r->Kode == null) ? "null" : "'".$r->Kode."'";
                    $Nama_Faktur            = ($r->Nama_Faktur == null) ? "null" : "'".str_replace(["'",","], " ", $r->Nama_Faktur)."'";
                    $Alamat                 = ($r->Alamat == null) ? "null" : "'".str_replace(["'",",","/"], " ", $r->Alamat)."'" ;
                    $Kota                   = ($r->Kota == null) ? "null" : "'".str_replace(["'",","], " ", $r->Kota)."'";
                    $Contact_Person         = ($r->Contact_Person == null) ? "null" : "'".str_replace(["'",","], " ", $r->Contact_Person)."'";
                    $Telp                   = ($r->Telp == null) ? "null" : "'".str_replace(["'",","], " ", $r->Telp)."'";
                    $Limit_Kredit           = ($r->Limit_Kredit == null) ? "null" : "'".$r->Limit_Kredit."'";
                    $TOP                    = ($r->TOP == null) ? "null" : "'".$r->TOP."'";
                    $Cara_Bayar             = ($r->Cara_Bayar == null) ? "null" : "'".$r->Cara_Bayar."'";
                    $Saldo_Piutang          = ($r->Saldo_Piutang == null) ? "null" : "'".$r->Saldo_Piutang."'";
                    $Class_2                = ($r->Class_2 == null) ? "null" : "'".$r->Class_2."'";
                    $Class                  = ($r->Class == null) ? "null" : "'".$r->Class."'";
                    $Tipe_2                 = ($r->Tipe_2 == null) ? "null" : "'".$r->Tipe_2."'";
                    $Tipe                   = ($r->Tipe == null) ? "null" : "'".$r->Tipe."'";
                    $Channel_2              = ($r->Channel_2 == null) ? "null" : "'".$r->Channel_2."'";
                    $Channel                = ($r->Channel == null) ? "null" : "'".$r->Channel."'";
                    $Rayon_1                = ($r->Rayon_1 == null) ? "null" : "'".$r->Rayon_1."'";
                    $Status_Usulan_Limit    = ($r->Status_Usulan_Limit == null) ? "null" : "'".$r->Status_Usulan_Limit."'";
                    $Usulan_Limit_Kredit    = ($r->Usulan_Limit_Kredit == null) ? "null" : "'".$r->Usulan_Limit_Kredit."'";
                    $History_Update         = ($r->History_Update == null) ? "null" : "'".$r->History_Update."'";
                    $Tgl_Usulan_Limit       = ($r->Tgl_Usulan_Limit == null) ? "null" : "'".$r->Tgl_Usulan_Limit."'";
                    $Approval_Limit_BM      = ($r->Approval_Limit_BM == null) ? "null" : "'".$r->Approval_Limit_BM."'";
                    $Approval_Limit_Pusat   = ($r->Approval_Limit_Pusat == null) ? "null" : "'".$r->Approval_Limit_Pusat."'";
                    $Status_Usulan_TOP      = ($r->Status_Usulan_TOP == null) ? "null" : "'".$r->Status_Usulan_TOP."'";
                    $Buka_TOP               = ($r->Buka_TOP == null) ? "null" : "'".$r->Buka_TOP."'";
                    $Tgl_Usulan_TOP         = ($r->Tgl_Usulan_TOP == null) ? "null" : "'".$r->Tgl_Usulan_TOP."'";
                    $Approval_TOP_BM        = ($r->Approval_TOP_BM == null) ? "null" : "'".$r->Approval_TOP_BM."'";
                    $Approval_TOP_Pusat     = ($r->Approval_TOP_Pusat == null) ? "null" : "'".$r->Approval_TOP_Pusat."'";
                    $Riwayat_Bayar          = ($r->Riwayat_Bayar == null) ? "null" : "'".$r->Riwayat_Bayar."'";
                    $Dokumen_Limit_TOP      = ($r->Dokumen_Limit_TOP == null) ? "null" : "'".$r->Dokumen_Limit_TOP."'";
                    $Kategori_2             = ($r->Kategori_2 == null) ? "null" : "'".$r->Kategori_2."'";
                    $Status                 = ($r->Status == null) ? "null" : "'".$r->Status."'";
                    $Stat_No                = ($r->Stat_No == null) ? "null" : "null";
                    $Cabang_String          = ($r->Cabang_String == null) ? "null" : "'".$r->Cabang_String."'";
                    $Area_String            = ($r->Area_String == null) ? "null" : "'".str_replace(["'",","], " ", $r->Area_String)."'";
                    $Pelanggan_String       = ($r->Pelanggan_String == null) ? "null" : "'".str_replace(["'",","], " ", $r->Pelanggan_String)."'";
                    $smsP                   = ($r->smsP == null) ? "null" : "'".str_replace(["'",","], " ", $r->smsP)."'";
                    $Nama_Reg               = ($r->Nama_Reg == null) ? "null" : "'".str_replace(["'",","], " ", $r->Nama_Reg)."'";
                    $Alamat_Reg             = ($r->Alamat_Reg == null) ? "null" : "'".str_replace(["'",",","/"], " ", $r->Alamat_Reg)."'";
                    $userid                 = ($r->userid == null) ? "null" : "'".$r->userid."'";
                    $Password               = ($r->Password == null) ? "null" : "'".$r->Password."'";
                    $Jadwal_Kirim           = ($r->Jadwal_Kirim == null) ? "null" : "'".$r->Jadwal_Kirim."'";
                    $Jadwal_Call            = ($r->Jadwal_Call == null) ? "null" : "'".$r->Jadwal_Call."'";
                    $KTP                    = ($r->KTP == null) ? "null" : "'".$r->KTP."'";
                    $Ijin_Bidan             = ($r->Ijin_Bidan == null) ? "null" : "'".$r->Ijin_Bidan."'";
                    $Bank                   = ($r->Bank == null) ? "null" : "'".$r->Bank."'";
                    $Rekening               = ($r->Rekening == null) ? "null" : "'".$r->Rekening."'";
                    $Area_Kirim             = ($r->Area_Kirim == null) ? "null" : "'".$r->Area_Kirim."'";
                    $cek                    = ($r->cek == null) ? "null" : "'".$r->cek."'";
                    $Status_Retail          = ($r->Status_Retail == null) ? "null" : "'".$r->Status_Retail."'";
                    $id                     = ($r->id == null) ? "null" : "'".$r->id."'";

                    $string .= "(".$AddedTime.", ".$ModifiedTime.", ".$Cabang.", ".$Area.", ".$Pelanggan.", ".$Nama_Pelanggan.", ".$Kode_Reps.", ".$Kode.", ".$Nama_Faktur.", ".$Alamat.", ".$Kota.", ".$Contact_Person.", ".$Telp.", ".$Limit_Kredit.", ".$TOP.", ".$Cara_Bayar.", ".$Saldo_Piutang.", ".$Class_2.", ".$Class.", ".$Tipe_2.", ".$Tipe.", ".$Channel_2.", ".$Channel.", ".$Rayon_1.", ".$Status_Usulan_Limit.", ".$Usulan_Limit_Kredit.", ".$History_Update.", ".$Tgl_Usulan_Limit.", ".$Approval_Limit_BM.", ".$Approval_Limit_Pusat.", ".$Status_Usulan_TOP.", ".$Buka_TOP.", ".$Tgl_Usulan_TOP.", ".$Approval_TOP_BM.", ".$Approval_TOP_Pusat.", ".$Riwayat_Bayar.", ".$Dokumen_Limit_TOP.", ".$Kategori_2.", ".$Status.", ".$Stat_No.", ".$Cabang_String.", ".$Area_String.", ".$Pelanggan_String.", ".$smsP.", ".$Nama_Reg.", ".$Alamat_Reg.", ".$userid.", ".$Password.", ".$Jadwal_Kirim.", ".$Jadwal_Call.", ".$KTP.", ".$Ijin_Bidan.", ".$Bank.", ".$Rekening.", ".$Area_Kirim.", ".$cek.", ".$Status_Retail.", ".$id."),";

                }

                $var_dump=rtrim($string,",");

                $valid = $this->db->query("INSERT INTO `mretail` VALUES ".$var_dump);


                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

              public function cekEDSIPA($kode = NULL)
    {   
        $query = $this->db->query("SELECT 
                                  mst_pelanggan.Cabang,
                                  mst_pelanggan.Kode,
                                  mst_pelanggan.Tipe_Pelanggan,
                                  IF( mst_pelanggan.EDSIPA = '0000-00-00' , DATE(NOW()),IFNULL(mst_pelanggan.EDSIPA, DATE(NOW())) )AS EDSIPA,
                                  DATE(NOW()) AS DAYSERVER,
                                  IFNULL(mst_tipepelanggan.CekEDSIPA,'N') AS CekSIPA,
                                  mst_tipepelanggan.HariEDKunci,
                                  IFNULL(mst_tipepelanggan.KunciTransEDSIPA1,'N') AS KunciTrans1,
                                  IFNULL(mst_tipepelanggan.KunciTransEDSIPA2,'N') AS KunciTrans2
                                FROM
                                  mst_pelanggan
                                  LEFT JOIN mst_tipepelanggan ON mst_tipepelanggan.Tipe = mst_pelanggan.Tipe_Pelanggan
                                  WHERE mst_pelanggan.Kode = '".$kode."' AND mst_pelanggan.Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

    public function cekAproveEDSIPA($kode = NULL)
    {   
        $query = $this->db->query("SELECT count(*) as buka FROM mst_usulan_edsipa WHERE Status_Usulan='Disetujui' and Kode_Pelanggan = '".$kode."' AND Cabang = '".$this->cabang."' ")->result(); 
        return $query;
    }

    public function saveDataUsulanEDSIAPA($params = null)
    {
        $dataPelanggan = $this->dataPelangganNew($params->pelanggan);
        
        //================ Running Number ======================================//
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Usulan Buka ED SIPA');
        $year = date('Y');
        $data = $this->db->query("select max(right(No_Usulan,7)) as 'no' from mst_usulan_edsipa where Cabang = '".$this->cabang."' and YEAR(Tanggal) ='".date('Y')."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
          $lastNumber = 1000001;
        }else {
          $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//

        
        $this->db->set("No_Usulan", $noDokumen);
        $this->db->set("Tanggal",$params->tgl );
        $this->db->set("Cabang",$this->cabang );
        $this->db->set("Kode_Pelanggan", $params->pelanggan);
        $this->db->set("Pelanggan", $dataPelanggan->Nama_Faktur);
        $this->db->set("Status_Usulan", 'Usulan');
        $this->db->set("Created_at", date('Y-m-d H:i:s'));
        $this->db->set("Created_by", $this->session->userdata('username'));
        $this->db->set("Updated_at", date('Y-m-d H:i:s'));
        $this->db->set("Updated_by", $this->session->userdata('username'));
        $valid =  $this->db->insert("mst_usulan_edsipa"); 

        $this->db2 = $this->load->database('pusat', TRUE);
         if ($this->db2->conn_id == TRUE) {

        $this->db2->set("No_Usulan", $noDokumen);
        $this->db2->set("Tanggal",$params->tgl );
        $this->db2->set("Cabang",$this->cabang );
        $this->db2->set("Kode_Pelanggan", $params->pelanggan);
        $this->db2->set("Pelanggan", $dataPelanggan->Nama_Faktur);
        $this->db2->set("Status_Usulan", 'Usulan');
        $this->db2->set("Created_at", date('Y-m-d H:i:s'));
        $this->db2->set("Created_by", $this->session->userdata('username'));
        $this->db2->set("Updated_at", date('Y-m-d H:i:s'));
        $this->db2->set("Updated_by", $this->session->userdata('username'));
        $valid =  $this->db2->insert("mst_usulan_edsipa");
        $this->db->set("Status_Pusat", "Berhasil");
        $this->db->where("No_Usulan",$noDokumen);
        $this->db->update("mst_usulan_edsipa");
    }

        return $noDokumen;
    }

    public function prosesDataApprovalEDSIPA($NoUsulan=null,$status=null)
    {
       $this->db2 = $this->load->database('pusat', TRUE); 

        if ($this->group == "CabangApoteker") {
            if($status =='Approve'){
                $approveusulan  = "Y";
            }else if($status =='Reject'){
                $approveusulan  = "R";
                $this->db->set("Status_Usulan", 'Ditolak');
            }

            $this->db->set("Approved_APTCAB", $approveusulan);
            $this->db->set("DateApprove_APTCAB",  date('Y-m-d H:i:s'));
            $this->db->set("Updated_at", date('Y-m-d H:i:s'));
            $this->db->set("Updated_by", $this->session->userdata('username'));
            $this->db->where("No_Usulan",$NoUsulan);
            $this->db->where("Cabang",$this->cabang);
            $this->db->update("mst_usulan_edsipa");


                if ($this->db2->conn_id == TRUE) {


                    $cekdata =  $this->db2->query ("Select * from mst_usulan_edsipa where No_Usulan = '".$NoUsulan."' and Cabang = '".$this->cabang."'")->num_rows();

                    // log_message('error', 'diluar '.$cekdata);
                            if($cekdata == 0){
                                $datacab =  $this->db->query ("Select * from mst_usulan_edsipa where No_Usulan = '".$NoUsulan."' and Cabang = '".$this->cabang."'")->row();
                                   log_message('error', 'didalam ');
                                    
                                    $valid =  $this->db2->insert("mst_usulan_edsipa",$datacab);
                                    if($valid){
                                        $this->db->set("Status_Pusat", "Berhasil");
                                        $this->db->where("No_Usulan",$NoUsulan);
                                        $this->db->update("mst_usulan_edsipa");
                                    }
                                
                            }else{
                                // log_message('error', 'elseee ');

                                    if($status =='Reject'){
                                        $this->db2->set("Status_Usulan", 'Ditolak');
                                    }
                                $this->db2->set("Approved_APTCAB", $approveusulan);
                                $this->db2->set("DateApprove_APTCAB",  date('Y-m-d H:i:s'));
                                $this->db2->set("Updated_at", date('Y-m-d H:i:s'));
                                $this->db2->set("Updated_by", $this->session->userdata('username'));
                                $this->db2->where("No_Usulan",$NoUsulan);
                                $this->db2->where("Cabang",$this->cabang);
                                $this->db2->update("mst_usulan_edsipa");
                            }

                            // log_message('error', 'dibawah ');
                    } 
                $update = true;
                $message = "Sukses";                 
            }else if ($this->group == "PusatApotik") {
            
                            if($status =='Approve'){
                                $approveusulan  = "Y";
                                $this->db->set("Status_Usulan", 'Disetujui');
                            }else if($status =='Reject'){
                                $approveusulan  = "R";
                                $this->db->set("Status_Usulan", 'Ditolak');
                            }

                            $this->db->set("Approved_APTPST", $approveusulan);
                            $this->db->set("DateApprove_APTPST",  date('Y-m-d H:i:s'));
                            $this->db->set("Updated_at", date('Y-m-d H:i:s'));
                            $this->db->set("Updated_by", $this->session->userdata('username'));
                            $this->db->where("No_Usulan",$NoUsulan);
                            $this->db->where("Cabang",$this->cabang);
                            $this->db->update("mst_usulan_edsipa");

                                $update = true;
                                $message = "Sukses";                 
            }else{
                $update = false;
                $message = "no_Approve";
            }      

        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

     public function listDataUsulanEDSIPA($search=null,$byLimit=null,$cek=null)
    {
        $byStatus = "";
        
        
        if($this->group == "CabangApoteker"){
            if($cek == 'allx'){
                $byStatus = "";
                }else{
            $byStatus = " and ifnull(Approved_APTCAB,'') = '' and ifnull(Status_Usulan,'') != 'Terpakai'";
                }

            }else if($this->group == "PusatApotik"){
                if($cek == 'allx'){
                $byStatus = "";
                }
            $byStatus = " and ifnull(Approved_APTCAB,'') = '' and ifnull(Status_Usulan,'') != 'Terpakai'";
            }
              
        
         $query = $this->db->query("select * from mst_usulan_edsipa where (No_Usulan like '%".$search."%' or Tanggal like '%".$search."%' or Kode_Pelanggan like '%".$search."%') $byStatus $byLimit");

        return $query;
    }


        public function updateDataUsulanEDSIPA(){

        $this->db2 = $this->load->database('pusat', TRUE);   
    
        $disc = $this->db->query("SELECT * FROM mst_usulan_edsipa WHERE Status_Usulan ='Usulan' ")->result();

        if ($this->db2->conn_id == TRUE) {
            foreach ($disc as $list) {

               $UsulanEDSIPA_PST = $this->db2->query("select * from mst_usulan_edsipa where No_Usulan = '".$list->No_Usulan."' and Cabang ='".$this->cabang."' ");

               $data_usulan = $UsulanEDSIPA_PST->result();
               if($UsulanEDSIPA_PST->num_rows() > 0){

                            $this->db->set("Status_Usulan", $data_usulan[0]->Status_Usulan);
                            $this->db->set("Approved_APTPST", $data_usulan[0]->Approved_APTPST);
                            $this->db->set("DateApprove_APTPST",  $data_usulan[0]->DateApprove_APTPST);
                            $this->db->set("Updated_at", date('Y-m-d H:i:s'));
                            $this->db->set("Updated_by", $this->session->userdata('username'));
                            $this->db->where("No_Usulan",$list->No_Usulan);
                            $this->db->where("Cabang",$this->cabang);
                            $this->db->update("mst_usulan_edsipa");
               }
            }
  
        }
        else{
            return 'GAGAL';
        }

    }


    public function prosesDataUsulanEDSIPA($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE); 
        
        $datacab =  $this->db->query ("Select * from mst_usulan_edsipa where No_Usulan = '".$no."' and Cabang = '".$this->cabang."'")->row();

        if ($this->db2->conn_id == TRUE) {
             $cekdata =  $this->db2->query ("Select * from mst_usulan_edsipa where No_Usulan = '".$no."' and Cabang = '".$this->cabang."'")->num_rows();
                            if($cekdata == 0){
                                
                                    
                                    $valid =  $this->db2->insert("mst_usulan_edsipa",$datacab);
                                    if($valid){
                                        $this->db->set("Status_Pusat", "Berhasil");
                                        $this->db->where("No_Usulan",$no);
                                        $this->db->update("mst_usulan_edsipa");
                                    }
                                
                            }else{

                                $this->db2->set("Approved_APTCAB", $datacab->Approved_APTCAB);
                                $this->db2->set("DateApprove_APTCAB",  $datacab->DateApprove_APTCAB);
                                $this->db2->set("Updated_at", $datacab->Updated_at);
                                $this->db2->set("Updated_by", $this->session->userdata('username'));
                                $this->db2->where("No_Usulan",$datacab->No_Usulan);
                                $this->db2->where("Cabang",$this->cabang);
                                $valid = $this->db2->update("mst_usulan_edsipa");
                                if($valid){
                                        $this->db->set("Status_Pusat", "Berhasil");
                                        $this->db->where("No_Usulan",$no);
                                        $this->db->update("mst_usulan_edsipa");
                                    }
                            }
            return TRUE;
        }
        else{
            $this->db->query("update mst_usulan_disc_prinsipal set statusPusat = 'Gagal' where No_Usulan = '".$no."'");
            return FALSE;
        }
    }

    public function RestartTrans()
    {   
        $DO = $this->db->query("select * from mst_trans 
                                  where Nama_transaksi='DO' and counter=1")->num_rows();
        $Kiriman = $this->db->query("select * from mst_trans 
                                    where Nama_transaksi='Kiriman' and counter=1")->num_rows();
        
        // log_message("error",print_r($DO,true));
        // log_message("error",print_r($Kiriman,true));
        // if($DO == 0 and $Kiriman == 0){
            // log_message("error","kesini");
            $valid = exec("/var/www/html/./restartmysql.sh",$result,$result_value);
            $valid = true;
        // }else{
        //     $valid=false;
        // }
        return $valid;
    }
    public function cekstokorder($produk = NULL)
    {   
        $data = $this->db->query("select * from trs_invdet
                                    where KodeProduk ='".$produk."' and tahun ='".date('Y')."' and unitstok > 0 and gudang ='Baik' order by ExpDate,NoDokumen Desc,BatchNo ASC,KodeProduk ASC")->result();
        return $data;
    }
    public function prosesulangtop($noso = NULL,$status = NULL)
    {   
        $valid = false;
        $limit_TOP_BM    = $this->Model_main->CabangDev()[0]->top_BM;
        $limit_BM       = (int)$this->Model_main->CabangDev()[0]->limit_jual_BM;
        $cekso = $this->db->query("select * from trs_sales_order where noso ='".$noso."' limit 1")->row();
        $umur_piutang_bm    = $cekso->umur_piutang_bm;
        $limit_piutang_bm   = $cekso->limit_piutang_bm;
        $app_bm = $cekso->Approve_BM;
        $app_top_bm = $cekso->Approve_TOP_BM;
        if($status == 'TOP'){
            if($app_top_bm == 'Y'){
                if($umur_piutang_bm <= $limit_TOP_BM){
                    $valid =  $this->db->query("update trs_sales_order
                        set StatusTOP = 'Ok' 
                        where noso ='".$noso."'");
                    $this->db2 = $this->load->database('pusat', TRUE);
                    if ($this->db2->conn_id == TRUE) {
                        $valid =  $this->db2->query("update trs_sales_order
                        set StatusTOP = 'Ok' 
                        where noso ='".$noso."'");
                    } 
                }
            }
        }elseif($status == 'Limit'){
            if($app_bm == 'Y'){
                if($limit_piutang_bm <= $limit_BM){
                    $valid =  $this->db->query("update trs_sales_order
                        set StatusLimit = 'Ok' 
                        where noso ='".$noso."'");
                    $this->db2 = $this->load->database('pusat', TRUE);
                    if ($this->db2->conn_id == TRUE) {
                        $valid =  $this->db2->query("update trs_sales_order
                        set StatusLimit = 'Ok' 
                        where noso ='".$noso."'");
                    } 
                }
            }
        }
        return $valid;
    }

    public function dataPelanggan_acu2($kode = NULL)
    {   
       
        $query = $this->db->query("
                    SELECT Kode, TOP,limit_kredit,IFNULL(saldopiutang,0)saldopiutang,IFNULL(saldoorder,0)saldoorder,IFNULL(saldoDlvorder,0) saldoDlvorder,
                    IFNULL(Buka_Top,0)Buka_Top FROM mretail a LEFT JOIN (SELECT SUM(saldo) AS saldopiutang,acu2,Cabang 
                    FROM trs_faktur WHERE acu2 = '$kode' AND Cabang = '".$this->cabang."' GROUP BY cabang,acu2) b 
                    ON a.Kode=b.acu2 AND a.`Cabang`=b.Cabang 
                    LEFT JOIN 
                    (SELECT SUM(total) AS saldoorder,acu2,Cabang FROM trs_sales_order WHERE acu2 = '$kode'
                    AND Cabang = '".$this->cabang."' AND STATUS ='Usulan' GROUP BY cabang,acu2
                    ) c ON a.Kode=c.acu2 AND a.`Cabang`=c.Cabang 
                    LEFT JOIN (
                    SELECT SUM(total) AS saldoDlvorder,acu2,Cabang FROM trs_delivery_order_sales WHERE acu2 = '$kode' AND 
                    Cabang = '".$this->cabang."' AND STATUS IN ('Open','Kirim') GROUP BY cabang,acu2
                    ) d ON a.Kode=d.acu2 AND a.`Cabang`=d.Cabang WHERE a.Kode = '$kode' AND a.Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

    public function datapiutangPelanggan_acu2($kode = NULL)
    {   
        $header = $this->db->query("SELECT mretail.limit_kredit,
                                           mretail.top,
                                           ifnull(faktur_pelanggan.Umur_faktur,0)Umur_faktur
                                    from mretail left join 
                                    ( select acu2,
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur 
                                      where (trs_faktur.Status in ( 'Open','OpenDIH','Giro') OR (trs_faktur.`Saldo` + trs_faktur.`saldo_giro`) != 0) and 
                                        trs_faktur.Status != 'Usulan'
                                      group by acu2) as faktur_pelanggan on faktur_pelanggan.acu2 = mretail.Kode
                                    where mretail.Kode ='".$kode."'")->result();

        $data = array("query" =>"", "header" =>$header);

        return $data;
    }

    public function get_onf($kodepel = NULL)
    {   
        $data = $this->db->query("SELECT Kode,Tipe_2,IFNULL(Cab_Onf,0) Cab_Onf,IFNULL(Prins_Onf,0) Prins_Onf FROM `mst_pelanggan` WHERE Tipe_2 = 'Y' AND Kode ='$kodepel'"); 
        return $data;
    }

    function data_api($params) {

            $ch     = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL             => "http://119.235.19.141/mitraapi/apimitra/grobak/index?".$params,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 30,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                                            "cache-control: no-cache",
                                            "content-type: application/x-www-form-urlencoded",
                                            "Accept: application/json"
                )
            ));


            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
            // return $api_url."?api_auth_key=".$api_auth_key. ",".$postField;
    }

    function NoOrder(){
         $query = $this->data_api("method=getfororder&cabang=".$this->cabang);
        return $query;
    }

    function getOrderSalesman($no){
         $query = $this->data_api("method=getsalesdetail&nosp=".$no."&cabang=".$this->cabang);
        return $query;
    }

    public function gethargaproduk()
    {   
        $data = $this->db->query("SELECT (SELECT COUNT(id) AS tot FROM mst_harga) harga, (SELECT COUNT(id) AS tot FROM mst_produk) produk")->result(); 
        return $data;
    }
}