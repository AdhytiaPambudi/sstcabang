<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_faktur extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->model('pembelian/Model_order');
            $this->load->model('Model_main');
            $this->load->library('owner');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function listData($search = null, $limit = null, $status = null, $tipe = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and Status = '".$status."'";
        }
        $query = $this->db->query("select * from trs_faktur where Cabang = '".$this->cabang."' and TipeDokumen = '".$tipe."' $byStatus $search $limit");

        return $query;
    }

    public function listDataAll($search = null, $limit = null, $status = null, $tipe = null, $byperiode = null)
    {   
        $byStatus = "";
        // if (!empty($status)) {
        //     $byStatus = " and Status = '".$status."'";
        // }
        $query = $this->db->query("select * from trs_faktur where Cabang = '".$this->cabang."' and TipeDokumen in ('Faktur','Retur','CN','DN') and Status not in ('Usulan','Batal') $byStatus $search $byperiode order by TimeFaktur DESC, NoFaktur ASC $limit");

        return $query;
    }

    public function prosesDataFaktur($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_faktur set statusPusat = 'Berhasil' where NoFaktur = '".$no."'");

            $query = $this->db->query("select * from trs_faktur_detail where NoFaktur = '".$no."'")->result();
            foreach($query as $r) { // loop over results                
                $cek = $this->db2->query("select * from trs_faktur_detail where NoFaktur = '".$no."' and NoDO = '".$r->NoDO."' and KodeProduk = '".$r->KodeProduk."' and BatchNo = '".$r->BatchNo."'")->num_rows();
                if ($cek == 0) {
                    $this->db2->insert('trs_faktur_detail', $r); // insert each row to another table
                }
                else{
                    $this->db2->where('BatchNo', $r->BatchNo);
                    $this->db2->where('KodeProduk', $r->KodeProduk);
                    $this->db2->where('NoDO', $r->NoDO);
                    $this->db2->where('NoFaktur', $no);
                    $this->db2->update('trs_faktur_detail', $r);
                }
            }

            $query2 = $this->db->query("select * from trs_faktur where NoFaktur = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_faktur where NoFaktur = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_faktur', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('NoFaktur', $no);
                $this->db2->update('trs_faktur', $query2);
            }

            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function dataFaktur($no = NULL,$TipeDokumen = NULL)
    {
        if($TipeDokumen == 'Faktur' || $TipeDokumen == 'Retur' ){
            $query = $this->db->query("select a.*,IFNULL(b.qty,0)qty, IFNULL(b.bns,0)bns from (select * from trs_faktur_detail where Cabang = '".$this->cabang."' and NoFaktur = '".$no."' AND 
        ((IFNULL(trs_faktur_detail.`QtyFaktur`,0) + IFNULL(trs_faktur_detail.`BonusFaktur`,0)) - IFNULL(trs_faktur_detail.`sisa_retur`,0)) > 0) a left outer join (select sum(QtyFaktur) as qty, sum(BonusFaktur) as bns, KodeProduk as kode, Acu,BatchNo,nobpb from trs_faktur_detail where Cabang = '".$this->cabang."' and TipeDokumen = 'Retur' and Acu = '".$no."' group by Acu,KodeProduk,BatchNo) b on a.NoFaktur =  b.Acu and a.KodeProduk = b.kode and a.BatchNo = b.BatchNo and a.nobpb = b.nobpb where IFNULL(a.QtyFaktur,0) - IFNULL(b.qty,0) >  0 or IFNULL(a.BonusFaktur,0) - IFNULL(b.bns,0) > 0")->result();
        }else{
            $query = $this->db->query("SELECT trs_faktur.*,trs_faktur_cndn.* 
                                       FROM trs_faktur,trs_faktur_cndn 
                                       WHERE trs_faktur.NoFaktur = trs_faktur_cndn.NoDokumen and 
                                        trs_faktur.Cabang ='".$this->cabang."'
                                        AND trs_faktur.NoFaktur = '".$no."'")->result();
        }
        return $query;
    }

    public function dataFakturCN($no = NULL,$TipeDokumen = NULL)
    {
        if($TipeDokumen == 'Faktur' || $TipeDokumen == 'Retur' ){
            $query = $this->db->query("select a.*,b.qty, b.bns from (select * from trs_faktur_detail where Cabang = '".$this->cabang."' and NoFaktur = '".$no."' and ifnull(Prinsipal,'') != 'ANDALAN') a left outer join (select sum(QtyFaktur) as qty, sum(BonusFaktur) as bns, KodeProduk as kode, Acu,BatchNo from trs_faktur_detail where Cabang = '".$this->cabang."' and TipeDokumen = 'Retur' and Acu = '".$no."' and ifnull(Prinsipal,'') != 'ANDALAN' group by Acu,KodeProduk,BatchNo) b on a.NoFaktur =  b.Acu and a.KodeProduk = b.kode and a.BatchNo = b.BatchNo")->result();
        }else{
            $query = $this->db->query("SELECT trs_faktur.*,trs_faktur_cndn.* 
                                       FROM trs_faktur,trs_faktur_cndn 
                                       WHERE trs_faktur.NoFaktur = trs_faktur_cndn.NoDokumen and 
                                        trs_faktur.Cabang ='".$this->cabang."'
                                        AND trs_faktur.NoFaktur = '".$no."'")->result();
        }
        return $query;
    }

    public function updateDataFakturPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select NoFaktur from trs_faktur where Cabang = '".$this->cabang."'")->result();
                foreach ($nomor as $no) {
                    $query = $this->db2->query("select * from trs_faktur_detail where NoFaktur = '".$no->NoFaktur."'")->result();
                    foreach($query as $r) { // loop over results
                        $this->db->where('BatchNo', $r->BatchNo);
                        $this->db->where('KodeProduk', $r->KodeProduk);
                        $this->db->where('NoFaktur', $no->NoFaktur);
                        $this->db->update('trs_faktur_detail', $r); // insert each row to another table
                    }

                    $query2 = $this->db2->query("select * from trs_faktur where NoFaktur = '".$no->NoFaktur."'")->row();
                    $this->db->where('NoFaktur', $no->NoFaktur);
                    $this->db->where('statusPusat', 'Berhasil');
                    $this->db->update('trs_faktur', $query2); // insert each row to another table
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function buatInkaso($NoFaktur = NULL)
    {   
        $this->db->set("StatusInkaso", "Ok");
        $this->db->set("TimeInkaso", date("Y-m-d H:i:s"));
        $this->db->where("NoFaktur", $NoFaktur);
        $valid = $this->db->update('trs_faktur');        

        return $valid;
    }

    public function listDataInkaso($search = "", $limit = "", $date1 = "", $date2 = "", $pelanggan = "", $salesman = "")
    {   
        $byDate="";
        $byPelanggan="";
        $bySalesman="";
        if (!empty($date1) and !empty($date2)) {
            $byDate = " and TglFaktur between '".$date1."' and '".$date2."'";
        }
        elseif(!empty($date1)){
            $byDate = " and TglFaktur >= '".$date1."'";   
        }
        elseif(!empty($date2)){
            $byDate = " and TglFaktur <= '".$date2."'";   
        }

        if (!empty($pelanggan)) {
            $byPelanggan = " and Pelanggan like '%".$pelanggan."%'";
        }

        if (!empty($salesman)) {
            $bySalesman = " and Salesman like '%".$salesman."%'";
        }
        // $query = $this->db->query("SELECT distinct xx.* 
        //                         FROM (SELECT aa.`Cabang`,aa.`NoFaktur`,aa.`TglFaktur`,aa.`TimeFaktur`,aa.`Pelanggan`,aa.`NamaPelanggan`,aa.`Salesman`,aa.`Status`,aa.`TipeDokumen`,
        //                                  aa.`Acu`,aa.`CaraBayar`, aa.`top`, aa.`TglJtoFaktur`,aa.`Total`,aa.`Saldo`, 'dok' 
        //                               FROM trs_faktur aa
        //                               WHERE Cabang = '".$this->cabang."' AND 
        //                                     StatusInkaso = 'Ok' AND STATUS = 'Open' and TipeDokumen in ('Faktur','Retur') $byPelanggan $bySalesman $byDate
        //                                     UNION ALL 
        //                                     (SELECT a.Cabang,a.NoDokumen AS NoFaktur,
        //                                             a.TanggalCNDN AS TglFaktur,
        //                                             b.`TimeFaktur`,
        //                                             b.Pelanggan,
        //                                             b.NamaPelanggan,
        //                                             b.Salesman,
        //                                             a.Status,
        //                                             a.TipeDokumen,
        //                                             b.Acu,
        //                                             b.CaraBayar,
        //                                             b.TOP,
        //                                             b.TglJTOFaktur,
        //                                             b.Total,b.Saldo,
        //                                             'CN' 
        //                                        FROM trs_faktur_cndn a,trs_faktur b 
        //                                        WHERE a.`NoDokumen` = b.`NoFaktur`  AND 
        //                                              a.Cabang = '".$this->cabang."' AND 
        //                                              StatusCNDN = 'Disetujui' $byPelanggan $bySalesman $byDate 
        //                                     GROUP BY a.NoDokumen) )xx $search
        //                                 ORDER BY TimeFaktur DESC, TglFaktur DESC, RIGHT(NoFaktur,7) DESC $limit");
        $query = $this->db->query("SELECT distinct xx.* 
                                FROM (SELECT aa.`Cabang`,aa.`NoFaktur`,aa.`TglFaktur`,aa.`TimeFaktur`,aa.`Pelanggan`,aa.`NamaPelanggan`,aa.`Salesman`,aa.`Status`,aa.`TipeDokumen`,
                                         aa.`Acu`,aa.`Acu2`,aa.`CaraBayar`, aa.`top`, aa.`TglJtoFaktur`,aa.`Total`,aa.`Saldo`, 'dok' 
                                      FROM trs_faktur aa
                                      WHERE Cabang = '".$this->cabang."' AND 
                                            StatusInkaso = 'Ok' AND STATUS = 'Open' and TipeDokumen in ('Faktur','Retur','CN','DN') $byPelanggan $bySalesman $byDate )xx $search
                                        ORDER BY TimeFaktur DESC, TglFaktur DESC, RIGHT(NoFaktur,7) DESC $limit");
        //$search $limit

        return $query;
    }

    public function listGiroGantung($NoFaktur = NULL)
    {
        $query = $this->db->query("select NomorFaktur as NoFaktur, sum(ValuePelunasan) as Total from trs_pelunasan_giro_detail where NomorFaktur = '".$NoFaktur."' and status = 'Open' group by NomorFaktur")->row();
        return $query;
    }

    public function saveDataDIH($list = NULL, $penagih = NULL)
    {   
        $kodeLamaCabang = $this->Model_order->kodeLamaCabang();
        $kodeDokumen = $this->Model_order->kodeDokumen('DIH');
        $counter = $this->Model_order->counter('DIH');
        $expld = explode("-", $penagih);
        $Kode = $expld[0];
        $Nama = $expld[1];
        $noDIH = "";
        $dih = "";
            // $data = $this->db->query("select max(right(NoDIH,7)) as 'no' from trs_dih where  length(NoDIH)= 13  and Cabang = '".$this->cabang."'")->result();
            // if(empty($data[0]->no) || $data[0]->no == ""){
            //     $lastNumber = 1000001;
            // }else {
            //     $lastNumber = $data[0]->no + 1;
            // }
            // $noDIH = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;

            // $noDIH = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($no,7,"0",STR_PAD_LEFT);
            foreach ($list as $key => $value) {
                $num_data = 0;
                $expld = explode('~', $list[$key]);
                $No = $expld[0];
                $tipe = $expld[1];
                // if ($tipe == 'CN' || $tipe == 'DN'){
                //     $query = $this->db->query("select * from trs_faktur_cndn where NoDokumen = '".$No."' and StatusCNDN in ('Open') limit 1");
                //     $data = $query->row();
                //     $num_data = $query->num_rows();
                //     if($num_data > 0){
                //         $salesman = '';
                //         $saldo = $data->Total;
                //         $tgl = $data->TanggalCNDN;
                //     }   
                // }
                // else{
                    $query = $this->db->query("select * from trs_faktur where NoFaktur = '".$No."' and Status in ('Open') limit 1");
                    $data = $query->row();
                    $num_data = $query->num_rows();
                    if($num_data > 0){
                        $salesman = $data->Salesman;
                        $saldo = $data->Saldo;
                        $tgl = $data->TglFaktur; 
                    }  
                // }
                if($num_data > 0){
                    $query2 = $this->db->query("select NoFaktur from trs_dih where NoFaktur = '".$No."' and Status in ('Open') limit 1");
                    // $listdih = $query->row();
                    $num_dih = $query2->num_rows();
                    if($num_dih < 1){
                        if($noDIH == ""){
                            $year = date('Y');
                            $doc = $this->db->query("select max(right(NoDIH,7)) as 'no' from trs_dih where  length(NoDIH)= 13  and Cabang = '".$this->cabang."' and YEAR(TglDIH) ='".$year."'")->result();
                            if(empty($doc[0]->no) || $doc[0]->no == ""){
                                    $lastNumber = 1000001;
                            }else {
                                $lastNumber = $doc[0]->no + 1;
                            }
                            $noDIH = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                        }
                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("NoDIH", $noDIH);
                        $this->db->set("Acu2", $data->acu2);
                        $this->db->set("NoFaktur", $No);
                        $this->db->set("TipeDokumen", $data->TipeDokumen);
                        $this->db->set("Penagih", $Kode);
                        $this->db->set("Pelanggan", $data->Pelanggan);
                        $this->db->set("Salesman", $salesman);
                        $this->db->set("Status", "Open");
                        $this->db->set("Total", $data->Total);
                        $this->db->set("Saldo", $saldo);
                        $this->db->set("Saldo_faktur", $saldo);
                        $this->db->set("TglDIH", date("Y-m-d H:i:s"));
                        $this->db->set("TglFaktur", $tgl);
                        $this->db->set("created_by", $this->user);
                        $this->db->set("created_at", date("Y-m-d H:i:s"));
                        $valid = $this->db->insert('trs_dih');
                        if($valid){
                            if ($tipe == 'CN' || $tipe == 'DN'){
                                $this->db->set("Status", "OpenDIH");
                                $this->db->set("nodih", $noDIH);
                                $this->db->set("tgldih", date("Y-m-d"));
                                $this->db->where("NoFaktur", $No);
                                $valid = $this->db->update('trs_faktur'); 
                                
                                $this->db->set("StatusCNDN", "OpenDIH");    
                                $this->db->where("NoDokumen", $No);
                                $valid = $this->db->update('trs_faktur_cndn');   
                            }else{
                                $this->db->set("Status", "OpenDIH");
                                $this->db->set("nodih", $noDIH);
                                $this->db->set("tgldih", date("Y-m-d"));
                                $this->db->where("NoFaktur", $No);
                                $valid = $this->db->update('trs_faktur');  
                                // $this->Model_faktur->prosesDataFaktur($No);
                            }   
                        }
                    }  
                    // $this->db->trans_commit();
                }
            }
            return $valid;
    }

    public function prosesDataDIH($noDIH = NULL)
    {
        $valid = false;
        $query = $this->db->query("select * from trs_dih where NoDIH = '".$noDIH."'")->result();

        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            foreach ($query as $dt) {
                $cek = $this->db2->query("select * from trs_dih where NoDIH = '".$noDIH."' and NoFaktur = '".$dt->NoFaktur."' and created_at = '".$dt->created_at."'")->num_rows();
                $data = $this->db->query("select * from trs_dih where NoDIH = '".$noDIH."' and NoFaktur = '".$dt->NoFaktur."' and created_at = '".$dt->created_at."'")->row();
                if ($cek == 0) {
                    $valid = $this->db2->insert("trs_dih", $data);
                }
                else{                    
                    $this->db2->where("NoDIH", $noDIH);
                    $this->db2->where("NoFaktur", $dt->NoFaktur);
                    $this->db2->where("created_at", $dt->created_at);
                    $valid = $this->db2->update("trs_dih", $data);
                }
            }
        }

        return $valid;
    }

    public function Penagih()
    {   
        $query = $this->db->query("select Kode, Nama, Jabatan from mst_karyawan where Cabang = '".$this->cabang."' and Status = 'Aktif' and Menagih = 'Ya' group by Kode")->result();

        return $query;
    }

    public function listFaktur($kode = null)
    {   
        // $query = $this->db->query("select NoFaktur from trs_faktur where Cabang = '".$this->cabang."' and Pelanggan = '".$kode."' and TipeDokumen = 'Faktur' and NoFaktur Not IN (select ifnull(Acu,'') as 'Acu' from trs_faktur where TipeDokumen = 'Retur')")->result();
        $query = $this->db->query("SELECT DISTINCT trs_faktur_detail.NoFaktur FROM trs_faktur_detail LEFT JOIN
                                    (SELECT NoFaktur,(SUM(IFNULL(qtyfaktur,0)+IFNULL(BonusFaktur,0))- SUM(IFNULL(sisa_retur,0))) AS 'qty' 
                                    FROM trs_faktur_detail 
                                    WHERE Cabang = '".$this->cabang."' AND Pelanggan = '".$kode."' AND TipeDokumen = 'Faktur' and Status not in ('Batal','Usulan') 
                                    GROUP BY NoFaktur) sum_faktur ON sum_faktur.NoFaktur = trs_faktur_detail.NoFaktur 
                                    WHERE sum_faktur.qty != 0 and 
                                        trs_faktur_detail.status not in ('Batal','Usulan') ;")->result();
        return $query;
    }

     public function jual_cndn($kode = null)
    {   
        $query = $this->db->query("select flag_jual_cndn from mst_prinsipal where Prinsipal = '".$kode."'")->result();

        return $query;
    }

    // Pelanggan
    public function listPelangganRetur()
    {   
        //$query = $this->db->query("select a.Kode,a.Nama_Faktur,a.Alamat,a.`Tipe_Pajak` from mst_pelanggan a where a.Cabang = '".$this->cabang."' and (a.Nama_Faktur != '' or a.Nama_Faktur is not null or a.Nama_Faktur != '-') and (a.`NPWP` is not null or a.`NPWP` != 0) group by a.Kode order by a.Nama_Faktur,a.Kode")->result();
        $query = $this->db->query("select a.Kode,a.Nama_Faktur,a.Alamat,a.`Tipe_Pajak` from mst_pelanggan a where a.Cabang = '".$this->cabang."' and (a.Nama_Faktur != '' or a.Nama_Faktur is not null or a.Nama_Faktur != '-') group by a.Kode order by a.Nama_Faktur,a.Kode")->result();

        return $query;
    }

    public function cekPelangganRetur($kode = null, $act = null)
    {   
        $query = $this->db->query("select a.Kode,a.Nama_Faktur,a.Alamat,a.`Tipe_Pajak` from mst_pelanggan a where a.Cabang = '".$this->cabang."' and (a.Nama_Faktur != '' or a.Nama_Faktur is not null or a.Nama_Faktur != '-') and (a.`Status_Retur` = 'ya') and a.Kode = '".$kode."' group by a.Kode order by a.Nama_Faktur,a.Kode")->num_rows();
        if ($act == 'update') {
            $this->db->query("update mst_pelanggan set Status_Retur = null where Kode = '".$kode."'");
        }

        return $query;
    }

    public function cekFaktur($no = null)
    {   
        $query = $this->db->query("select * from trs_faktur where NoFaktur = '".$no."'")->num_rows();

        return $query;
    }

  public function saveDataRetur($params = NULL, $name = NULL)
    {  
        $valid =false; 
        if (empty($params->manual))
        $this->cekPelangganRetur($params->pelanggan, 'update');
            $h = $this->db->query("select * from trs_faktur where NoFaktur = '".$params->faktur."' limit 1")->row();
        $dataPelanggan = $this->Model_main->dataPelanggan($params->pelanggan);
        $dataSalesman = $this->Model_main->dataSalesman($params->salesman); 
        //================ Running Number ======================================//
        $year = date('Y');
        $thn = substr($year,2,2);
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        $kodeDokumen = $this->Model_main->kodeDokumen('Retur');
        $data = $this->db->query("select max(right(NoFaktur,7)) as 'no' from trs_faktur where TipeDokumen='Retur'  and length(NoFaktur)= 13  and Cabang = '".$this->cabang."' and YEAR(TglFaktur) ='".$year."' and left(NoFaktur,2) = '".$thn."'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        $i=0;
        // log_message("error",print_r($params,true));
        foreach ($params->produk as $key => $value) {
            if (!empty($params->qtyretur[$key]) or !empty($params->bnsretur[$key])) {
                $i++;
                $expld = explode("~", $params->produk[$key]);
                $KodeProduk = $expld[0];
                $NamaProduk = $expld[1];
                if(empty($params->NoBPB[$key])){
                    $params->NoBPB[$key] = "";
                }
                $produk = $this->db->query("select Prinsipal,   Prinsipal2,Supplier1,Supplier2,Pabrik,Farmalkes 
                    FROM mst_produk WHERE Kode_Produk = '".$KodeProduk."' limit 1
                                             ")->row();
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("NoFaktur", $noDokumen);
                $this->db->set("noline", $i);
                $this->db->set("TglFaktur", date('Y-m-d'));
                $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
                $this->db->set("Pelanggan", $params->pelanggan);
                $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                $this->db->set("AlamatFaktur", $dataPelanggan->Alamat);
                $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
                $this->db->set("NamaTipePelanggan", "");
                $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
                $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
                $this->db->set("Acu", $params->faktur);
                $this->db->set("TOP", $dataPelanggan->TOP);
                $this->db->set("TglJtoFaktur", date('Y-m-d'));
                $this->db->set("Salesman", $dataSalesman->Kode);
                $this->db->set("NamaSalesman", $dataSalesman->Nama);
                $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
                $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
                $this->db->set("Status", "Open");
                $this->db->set("TipeDokumen", "Retur");
                $this->db->set("Gross", round($params->gross[$key],0));
                $this->db->set("Potongan", round($params->potongan[$key],0));
                $this->db->set("Value", round($params->value[$key],0));
                $this->db->set("Ppn", round($params->ppn[$key],0));
                $this->db->set("Total", round($params->total[$key],0));
                if (empty($params->manual)) {
                    $this->db->set("NoSO", $h->NoSO);
                    $this->db->set("NoDO", $h->NoDO);
                }
                $this->db->set("KodeProduk", $KodeProduk);
                $this->db->set("NamaProduk", $NamaProduk);
                $this->db->set("Harga", $params->harga[$key]);  
                $this->db->set("QtyDO", $params->qtyfaktur[$key]);
                $this->db->set("BonusDO", $params->bnsfaktur[$key]);
                $this->db->set("QtyFaktur", $params->qtyretur[$key]);
                $this->db->set("BonusFaktur", $params->bnsretur[$key]);
                $this->db->set("CashDiskon", $params->cashdisc[$key]);
                $this->db->set("DiscCab", $params->dsccab[$key]);
                $this->db->set("DiscPrins1", $params->dscprins[$key]);
                $this->db->set("DiscTotal", $params->dsctotal[$key]);
                $this->db->set("BatchNo", $params->batch[$key]);
                $this->db->set("ExpDate", $params->expdate[$key]);
                $this->db->set("NoBPB", $params->NoBPB[$key]);
                $this->db->set("COGS", $params->COGS[$key]);
                $this->db->set("TotalCOGS", $params->TotalCOGS[$key]);
                $this->db->set("created_at", date('Y-m-d H:i:s'));
                $this->db->set("created_by", $this->user);
                $this->db->set("Prinsipal", $produk->Prinsipal);
                $this->db->set("Prinsipal2", $produk->Prinsipal2);
                $this->db->set("Supplier", $produk->Supplier1);
                $this->db->set("Supplier2", $produk->Supplier2);
                $this->db->set("Pabrik", $produk->Pabrik);
                $this->db->set("Farmalkes", $produk->Farmalkes);
                $valid =  $this->db->insert("trs_faktur_detail");
                if($valid){
                    $data = $this->db->query("select sisa_retur from trs_faktur_detail where NoFaktur ='".$params->faktur."' and KodeProduk = '".$KodeProduk."' and BatchNo ='".$params->batch[$key]."'  and NoBPB = '".$params->NoBPB[$key]."' limit 1")->result();
                    $sisa = $data[0]->sisa_retur;
                    $sisa_retur = $sisa + ($params->qtyretur[$key]+$params->bnsretur[$key]);
                    $this->db->set("sisa_retur", $sisa_retur);
                    $this->db->where("NoFaktur", $params->faktur);
                    $this->db->where("KodeProduk", $KodeProduk);
                    $this->db->where("BatchNo", $params->batch[$key]);
                    // $this->db->where("ExpDate", $params->expdate[$key]);
                    $this->db->where("NoBPB", $params->NoBPB[$key]); 
                    $this->db->update("trs_faktur_detail");  

                    // $this->db->query("update trs_faktur_detail a, mst_produk b 
                    //                     SET a.Prinsipal=b.Prinsipal,
                    //                         a.Prinsipal2=b.Prinsipal2,
                    //                         a.Supplier=b.Supplier1,
                    //                         a.Supplier2=b.Supplier2,
                    //                         a.Pabrik =b.Pabrik,
                    //                         a.Farmalkes=b.Farmalkes
                    //                 WHERE a.KodeProduk=b.Kode_Produk and 
                    //                     a.KodeProduk = '".$KodeProduk."' and 
                    //                     a.NoFaktur ='".$noDokumen."'");                    

                }
            }
        }
        $det = $this->db->query("select * from trs_faktur_detail where NoFaktur = '".$noDokumen."'")->num_rows();
        if($det > 0){
            // Start Header
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("NoFaktur", $noDokumen);
            $this->db->set("TglFaktur", date('Y-m-d'));
            $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
            $this->db->set("Pelanggan", $params->pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("AlamatFaktur", $dataPelanggan->Alamat);
            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
            $this->db->set("NamaTipePelanggan", "");
            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
            $this->db->set("Acu", $params->faktur);
            $this->db->set("CaraBayar", $params->carabayar);
            $this->db->set("ppn_pelanggan", $params->flag_ppn);
            $this->db->set("TOP", $dataPelanggan->TOP);
            $this->db->set("Salesman", $dataSalesman->Kode);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("Rayon", $dataSalesman->Rayon_Salesman);
            $this->db->set("NamaRayon", $dataSalesman->Rayon_Salesman2);
            $this->db->set("Status", "Open");
            $this->db->set("TipeDokumen", "Retur");
            $this->db->set("Gross", round($params->totgross,0));
            $this->db->set("Potongan", round($params->totpotongan,0));
            $this->db->set("Value", round($params->totvalue,0));
            $this->db->set("Ppn", round($params->totppn,0));
            $this->db->set("Total", round($params->tottotal,0));
            $this->db->set("Saldo", round($params->tottotal,0));
            $this->db->set("Dokumen_lampiran", $name);
            if (empty($params->manual)) {
                $this->db->set("NoSO", $h->NoSO);
                $this->db->set("NoDO", $h->NoDO);
                $this->db->set("NoSP", $h->NoSP);
            }
            $this->db->set("statusPusat", "Gagal"); 
            $this->db->set("created_at", date('Y-m-d H:i:s'));
            $this->db->set("created_by", $this->user);       
            $valid =  $this->db->insert("trs_faktur");
            if($valid){
                $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'Retur' and Cabang = '".$this->cabang."'");
            }

            $cek_data_double = $this->db->query("SELECT KodeProduk,COUNT(KodeProduk)jumlah from trs_faktur_detail where NoFaktur = '".$noDokumen."' AND (QtyDO = QtyFaktur AND BonusDO = BonusFaktur) AND Keterangan2 = 'Bidan'
                GROUP BY KodeProduk
                            HAVING COUNT(KodeProduk) > 1")->num_rows();

            if ($cek_data_double > 0) {
                $this->backStok_double($noDokumen, $params->faktur);
            }else{
                $this->backStok($noDokumen, $params->faktur);
            }
        }
        // End Header     
        return $valid;
    }

    public function backStok($No = null, $NoFaktur = NULL)
    {              
        $produk = $this->db->query("select noline,KodeProduk, Harga, QtyFaktur  from trs_faktur_detail where NoFaktur = '".$No."' and Acu = '".$NoFaktur."'")->result();
        $detail = $this->db->query("select KodeProduk, QtyFaktur, BonusFaktur, Harga, Gross, BatchNo,Value, ExpDate,NoBPB,COGS,TotalCOGS from trs_faktur_detail where NoFaktur = '".$No."' and Acu = '".$NoFaktur."'")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->KodeProduk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$product1."'")->row();
                // foreach ($detail as $key => $value) {
                //     $product2 = $detail[$key]->KodeProduk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $detail[$kunci]->QtyFaktur + $detail[$kunci]->BonusFaktur;
                        $valuestok = $detail[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    if($detail[$kunci]->TotalCOGS < 0){
                        $valuestok = $invsum->ValueStok - ($detail[$kunci]->TotalCOGS);
                    }else{
                        $valuestok = $invsum->ValueStok + ($detail[$kunci]->TotalCOGS);
                    }
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }
                    $UnitCOGS = $valuestok / $UnitStok;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok.",UnitCOGS ='".$UnitCOGS."' where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
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
                $valuestok = $detail[$key]->TotalCOGS;
                $UnitStok = $detail[$key]->QtyFaktur;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoFaktur."', 'update')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and Tahun = '".date('Y')."' and Gudang='Baik' and NoDokumen = '".$No."' limit 1");
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$produk."'")->row();
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $stok = $dt->UnitStok;
                    $UnitStok = $stok + $detail[$key]->QtyFaktur + $detail[$key]->BonusFaktur;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    

                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", ($detail[$key]->COGS * -1));
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("BatchNo", $detail[$key]->BatchNo);
                    $this->db->where("NoDokumen", $No);
                    $valid = $this->db->update('trs_invdet');
                }
                else{
                    $UnitStok  = $detail[$key]->QtyFaktur + $detail[$key]->BonusFaktur;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik,KodePrinsipal,NamaPrinsipal, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."','".$prod->Prinsipal."','".$prod->Prinsipal."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$UnitCOGS."','".$No."')");
                }
                // }

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

    public function backStok_double($No = null, $NoFaktur = NULL)
    {              
        $produk = $this->db->query("select noline,KodeProduk, Harga, QtyFaktur  from trs_faktur_detail where NoFaktur = '".$No."' and Acu = '".$NoFaktur."'")->result();
        $detail = $this->db->query("select KodeProduk, QtyFaktur, BonusFaktur, Harga, Gross, BatchNo,Value, ExpDate,NoBPB,COGS,TotalCOGS from trs_faktur_detail where NoFaktur = '".$No."' and Acu = '".$NoFaktur."'")->result();
        

        // save inventori history
        foreach ($detail as $key => $value) {   
            $produk = $detail[$key]->KodeProduk;        
            if (!empty($produk)) {
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $detail[$key]->TotalCOGS;
                $UnitStok = $detail[$key]->QtyFaktur;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoFaktur."', 'update')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and Tahun = '".date('Y')."' and Gudang='Baik' and NoDokumen = '".$No."' limit 1");
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$produk."'")->row();
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $stok = $dt->UnitStok;
                    $UnitStok = $stok + $detail[$key]->QtyFaktur + $detail[$key]->BonusFaktur;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    

                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("UnitCOGS", ($detail[$key]->COGS * -1));
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("BatchNo", $detail[$key]->BatchNo);
                    $this->db->where("NoDokumen", $No);
                    $valid = $this->db->update('trs_invdet');
                }
                else{
                    $UnitStok  = $detail[$key]->QtyFaktur + $detail[$key]->BonusFaktur;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik,KodePrinsipal,NamaPrinsipal, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."','".$prod->Prinsipal."','".$prod->Prinsipal."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$UnitCOGS."','".$No."')");
                }
                // }

                /*$invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
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
                    }*/
            }
        }

        // ===== untuk summary ===== //
            /*$produk_sum = $this->db->query("select KodeProduk, Harga, QtySO from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' GROUP BY KodeProduk")->result();*/
            $data_sum = $this->db->query("SELECT KodeProduk, sum(QtyFaktur)QtyFaktur, sum(BonusFaktur)BonusFaktur, Harga,Value,COGS,TotalCOGS from trs_faktur_detail where NoFaktur = '".$No."' and Acu = '".$NoFaktur."' GROUP BY KodeProduk");

            $numdata_sum = $data_sum->num_rows();
            $detail_sum = $data_sum->result();

            foreach ($detail_sum as $kunci => $nilai) {
                $summary = 0;
                $product1 = $detail_sum[$kunci]->KodeProduk;
                $harga = $detail_sum[$kunci]->Harga;

                if (!empty($product1)) {
                    $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$product1."'")->row();
                            $summary = $summary + $detail_sum[$kunci]->QtyFaktur + $detail_sum[$kunci]->BonusFaktur;
                            $valuestok = $detail_sum[$kunci]->Value;
                    $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok + $summary;
                        if($detail_sum[$kunci]->TotalCOGS < 0){
                            $valuestok = $invsum->ValueStok - ($detail_sum[$kunci]->TotalCOGS);
                        }else{
                            $valuestok = $invsum->ValueStok + ($detail_sum[$kunci]->TotalCOGS);
                        }
                        if($UnitStok == 0){
                            $valuestok = 0;
                        }
                        $UnitCOGS = $valuestok / $UnitStok;
                        $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok.",UnitCOGS ='".$UnitCOGS."' where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                    }
                }

                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$product1."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                if($invdet->num_rows() <= 0){
                    $this->db->set("ValueStok", 0);
                    $this->db->where("KodeProduk", $product1);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }else{
                    $invdet = $invdet->row();
                    $this->db->set("ValueStok", $invdet->sumval);
                    // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                    $this->db->where("KodeProduk", $product1);
                    $this->db->where("Gudang", 'Baik');
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang",$this->cabang);
                    $valid = $this->db->update('trs_invsum');
                }
            }

    }


    public function prosesReqRetur($kode = NULL)
    {
        $this->db->set("Status_Retur", "request");
        $this->db->where("Cabang", $this->cabang);
        $this->db->where("Kode", $kode);        
        $valid = $this->db->update("mst_pelanggan");
            $this->db2 = $this->load->database('pusat', TRUE);      
            if ($this->db2->conn_id == TRUE) {
                $this->db2->set("Status_Retur", "request");
                $this->db2->where("Cabang", $this->cabang);
                $this->db2->where("Kode", $kode);        
                $valid = $this->db2->update("mst_pelanggan");
            }
        return $valid;
    }

    public function cekDataCNDN($no='')
    {
        $cek = $this->db->query("select * from trs_faktur_cndn where Faktur = '".$no."' and Status !='Reject'")->num_rows();
        return $cek;
    }

   public function saveDataCNDN($params='')
    {
        $NoUsulanCN = '';
        $dataPelanggan = $this->Model_main->dataPelanggan($params->pelanggan);
        //================ Running Number ======================================//
        $year = date('Y');
        $thn = substr($year,2,2);
        $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
        $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
        if($params->cndn == 'CN'){
            $kodeDokumen = $this->Model_main->kodeDokumen('CN');
            $data = $this->db->query("select max(right(NoFaktur,7)) as 'no' from trs_faktur where TipeDokumen='".$params->cndn."' and substr(NoFaktur,5,2)= '15' and LENGTH(NoFaktur)= 13 and Cabang = '".$this->cabang."' and YEAR(TglFaktur) ='".$year."' and left(NoFaktur,2) = '".$thn."'")->result();
        }else if($params->cndn == 'DN'){
            $kodeDokumen = $this->Model_main->kodeDokumen('DN');
            $data = $this->db->query("select max(right(NoFaktur,7)) as 'no' from trs_faktur where TipeDokumen='".$params->cndn."' and substr(NoFaktur,5,2)= '16' and LENGTH(NoFaktur)= 13 and Cabang = '".$this->cabang."' and YEAR(TglFaktur) ='".$year."' and left(NoFaktur,2) = '".$thn."'")->result();
        }

        $sales = $this->db->query("select * from trs_faktur where nofaktur = '".$params->faktur."'")->row();
        
        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = $data[0]->no + 1;
        }
        $noDokumen = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
        //================= end of running number ========================================//
        try{
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("NoFaktur", $noDokumen);
            $this->db->set("TglFaktur", date('Y-m-d'));
            $this->db->set("TimeFaktur", date('Y-m-d H:i:s'));
            $this->db->set("Pelanggan", $params->pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("AlamatFaktur", $dataPelanggan->Alamat);
            $this->db->set("TipePelanggan", $dataPelanggan->Tipe_Pelanggan);
            $this->db->set("NamaTipePelanggan", "");
            $this->db->set("NPWPPelanggan", $dataPelanggan->NPWP);
            $this->db->set("KategoriPelanggan", $dataPelanggan->Kat);
            $this->db->set("Acu", $params->faktur);
            $this->db->set("CaraBayar", "Cash");
            $this->db->set("TOP", $dataPelanggan->TOP);
            $this->db->set("Salesman", $sales->Salesman);
            $this->db->set("NamaSalesman", $sales->NamaSalesman);
            $this->db->set("Rayon", '');
            $this->db->set("NamaRayon", '');
            $this->db->set("Status", "Usulan");
            $this->db->set("TipeDokumen", $params->cndn);
            $this->db->set("Gross", '');
            $this->db->set("Potongan", $params->totalvalue);
            $this->db->set("Value", $params->totalvalue);
            $this->db->set("Ppn", '');
            $this->db->set("Total", $params->totalvalue);
            $this->db->set("Saldo", $params->totalvalue);
            // if (empty($params->manual)) {
            //     $this->db->set("NoSO", $h->NoSO);
            //     $this->db->set("NoDO", $h->NoDO);
            //     $this->db->set("NoSP", $h->NoSP);
            // }
            $this->db->set("statusPusat", "Gagal");        
            $valid =  $this->db->insert("trs_faktur");
            if($valid){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = '".$params->cndn."' and Cabang = '".$this->cabang."'");
             $i=0;
            foreach ($params->produk as $key => $value) {
                $expld = explode("~", $params->produk[$key]);
                $KodeProduk = $expld[0];
                $NamaProduk = $expld[1];
                if ($params->statuscn[$key] == 'Usulan') {
                    $kodeLamaCabang = $this->Model_main->kodeLamaCabang();
                    $kodeLamaCabang = $kodeLamaCabang->Kode_Lama;
                    if($params->cndn == 'CN'){
                        $kodeDokumen = $this->Model_main->kodeDokumen('CNB');
                        $data = $this->db->query("select max(NoDokumen) as 'no' from trs_faktur_cndn where TipeDokumen='".$params->cndn."'  and Cabang = '".$this->cabang."'")->result();
                    }else if($params->cndn == 'DN'){
                        $kodeDokumen = $this->Model_main->kodeDokumen('DNB');
                        $data = $this->db->query("select max(NoDokumen) as 'no' from trs_faktur_cndn where TipeDokumen='".$params->cndn."' and  Cabang = '".$this->cabang."'")->result();
                    }
                    
                    if(empty($data[0]->no) || $data[0]->no == ""){
                        $lastNumber = 1000001;
                    }else {
                        $lastNumber = substr($data[0]->no,6) + 1;
                    }
                    $noCNDN = date("y").$kodeLamaCabang.$kodeDokumen.str_pad($lastNumber,5,"0",STR_PAD_LEFT);
                    $i++;
                    $this->db->set("Cabang", $this->cabang);
                    // $this->db->set("Approval", '$params->approval');
                    $this->db->set("TipeDokumen", $params->cndn);
                    $this->db->set("NoDokumen", $noDokumen);
                    $this->db->set("noline", $i);
                    $this->db->set("Pelanggan", $params->pelanggan);
                    $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
                    $this->db->set("AlamatPelanggan", $dataPelanggan->Alamat);
                    $this->db->set("Salesman", $sales->Salesman);
                    $this->db->set("NamaSalesman", $sales->NamaSalesman);
                    $this->db->set("Faktur", $params->faktur);
                    $this->db->set("Perhitungan", $params->net);
                    $this->db->set("TanggalCNDN", $params->tgl);
                    $this->db->set("KodeProduk", $KodeProduk);
                    $this->db->set("Produk", $NamaProduk);
                    $this->db->set("DasarPerhitungan", $params->perhitungan[$key]);
                    $this->db->set("Persen", $params->persen[$key]);
                    $this->db->set("Rupiah", $params->rupiah[$key]);
                    $this->db->set("Jumlah", $params->jumlah[$key]);
                    $this->db->set("DscCab", $params->dsccab[$key]);
                    $this->db->set("Status", $params->statuscn[$key]);
                    $this->db->set("Banyak", $params->banyak[$key]);
                    $this->db->set("ValueDscCab", $params->vdsccab[$key]);
                    $this->db->set("Total", $params->totalvalue);
                    $this->db->set("StatusCNDN", $params->statuscndn);
                    $this->db->set("NoUsulan", $NoUsulanCN);
                    $this->db->set("created_at", date("Y-m-d H:i:s"));
                    $this->db->set("created_by", $this->session->userdata('username'));
                    $valid =  $this->db->insert("trs_faktur_cndn");
                }
                if($valid){
                    $this->Model_faktur->prosesDataFakturCNDN($noDokumen);
                }
                if($params->cndn == 'CN'){
                    $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'CNB' and Cabang = '".$this->cabang."'");
                }else{
                    $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'DNB' and Cabang = '".$this->cabang."'");
                }

            }
        }
        }catch (Exception $e) {
            log_message('error',$e);
        }

        return $valid;
    }

    

    public function listReqPelanggan()
    {   
        $query = $this->db->query("select Kode, Pelanggan, Status_Retur from mst_pelanggan where Cabang = '".$this->cabang."' and Status_Retur is not null")->result();

        return $query;
    }

    public function listDataCNDN($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " and trs_faktur_cndn.Status = '".$status."'";
        }
        $query = $this->db->query("select trs_faktur_cndn.* ,mst_produk.`Prinsipal`,mst_produk.`Supplier1` from trs_faktur_cndn LEFT JOIN mst_produk
                            ON trs_faktur_cndn.`KodeProduk` = mst_produk.`Kode_Produk` where Cabang = '".$this->cabang."' $byStatus $search ORDER BY TanggalCNDN  DESC,NoDokumen DESC $limit ");

        return $query;
    }

    public function cekjualcndn($kode = null)
    {   
        $query = $this->db->query("select flag_jual_cndn from mst_produk_info where Cabang = '".$this->cabang."' and Kode_Produk = '". $kode ."'")->result();
        return $query;
    }

    public function listDataInkaso2()
    {   
        $query = $this->db->query("SELECT DISTINCT aa.`Cabang`,
                                          aa.`NoFaktur`,
                                          aa.`Pelanggan`,
                                          aa.`NamaPelanggan`,
                                          aa.`Salesman`,
                                          aa.`acu2`,
                                          aa.`TglFaktur`,
                                          aa.`TimeFaktur`,
                                          DATEDIFF(DATE(NOW()),aa.`TglFaktur`) AS umur
                                      FROM trs_faktur aa
                                      WHERE Cabang = '".$this->cabang."' AND 
                                            StatusInkaso = 'Ok' AND STATUS = 'Open'
                                    UNION ALL 
                                    SELECT a.Cabang,a.NoDokumen AS NoFaktur,
                                            b.Pelanggan,
                                            b.NamaPelanggan,
                                            b.Salesman,
                                            '' as acu2,
                                            a.TanggalCNDN AS TglFaktur,b.TimeFaktur,
                                            DATEDIFF(DATE(NOW()),a.`TanggalCNDN`) AS umur
                                       FROM trs_faktur_cndn a,trs_faktur b 
                                       WHERE a.`NoDokumen` = b.`NoFaktur`  AND 
                                             a.Cabang = '".$this->cabang."' AND 
                                             StatusCNDN = 'Disetujui' AND 
                                             b.StatusInkaso = 'Ok'
                                    GROUP BY a.NoDokumen
                                    ORDER BY umur DESC,TimeFaktur DESC, TglFaktur DESC, RIGHT(NoFaktur,7) DESC;
                                        ")->result();

        return $query;
    }

    public function getListFakturDIH($nofaktur=null)
    {   
        $query = $this->db->query("SELECT *,DATEDIFF(DATE(NOW()),`TglFaktur`) AS umur
                                      FROM trs_faktur
                                      WHERE Cabang = '".$this->cabang."' AND 
                                            StatusInkaso = 'Ok' AND STATUS IN ('Open') and 
                                            nofaktur = '".$nofaktur."' limit 1
                                        ")->result();
        return $query;
    }

    public function saveDataDIH2($params = NULL)
    {   
        $kodeLamaCabang = $this->Model_order->kodeLamaCabang();
        $kodeDokumen = $this->Model_order->kodeDokumen('DIH');
        $counter = $this->Model_order->counter('DIH');
        $expld = explode("-", $params->penagih);
        $Kode = $expld[0];
        $Nama = $expld[1];
        $noDIH = "";
        $dih = "";
        
            foreach ($params->nomorfaktur as $key => $value) {
                $num_data = 0;
                // $expld = explode('~', $list[$key]);
                $No = $params->nomorfaktur[$key];
                $tipe = $params->tipe[$key];
                // if ($tipe == 'CN' || $tipe == 'DN'){
                //     $query = $this->db->query("select * from trs_faktur_cndn where NoDokumen = '".$No."' and StatusCNDN in ('Open') limit 1");
                //     $data = $query->row();
                //     $num_data = $query->num_rows();
                //     if($num_data > 0){
                //         $salesman = '';
                //         $saldo = $data->Total;
                //         $tgl = $data->TanggalCNDN;
                //     }   
                // }
                // else{
                    $query = $this->db->query("select * from trs_faktur where NoFaktur = '".$No."' and Status in ('Open') limit 1");
                    $data = $query->row();
                    $num_data = $query->num_rows();
                    if($num_data > 0){
                          

                        $salesman = $data->Salesman;
                        $saldo = $data->Saldo;
                        $tgl = $data->TglFaktur; 
                    }  
                // }
                if($num_data > 0){
                    $query2 = $this->db->query("select NoFaktur from trs_dih where NoFaktur = '".$No."' and Status in ('Open') limit 1");
                    // $listdih = $query->row();
                    $num_dih = $query2->num_rows();
                    if($num_dih < 1){
                        if($noDIH == ""){
                            $year = date('Y');
                            $doc = $this->db->query("select max(right(NoDIH,7)) as 'no' from trs_dih where  length(NoDIH)= 13  and Cabang = '".$this->cabang."' and YEAR(TglDIH) ='".$year."'")->result();
                            if(empty($doc[0]->no) || $doc[0]->no == ""){
                                    $lastNumber = 1000001;
                            }else {
                                $lastNumber = $doc[0]->no + 1;
                            }
                            $noDIH = date("y").$kodeLamaCabang.$kodeDokumen.$lastNumber;
                        }
                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("NoDIH", $noDIH);
                        $this->db->set("NoFaktur", $No);
                        $this->db->set("acu2", $data->acu2);
                        $this->db->set("TipeDokumen", $data->TipeDokumen);
                        $this->db->set("Penagih", $Kode);
                        $this->db->set("Pelanggan", $data->Pelanggan);
                        $this->db->set("Salesman", $salesman);
                        $this->db->set("Status", "Open");
                        $this->db->set("Total", $data->Total);
                        $this->db->set("Saldo", $saldo);
                        $this->db->set("Saldo_faktur", $saldo);
                        $this->db->set("TglDIH", date("Y-m-d H:i:s"));
                        $this->db->set("TglFaktur", $tgl);
                        $this->db->set("created_by", $this->user);
                        $this->db->set("created_at", date("Y-m-d H:i:s"));
                        $valid = $this->db->insert('trs_dih');
                        if($valid){
                            if ($tipe == 'CN' || $tipe == 'DN'){
                                $this->db->set("Status", "OpenDIH");
                                $this->db->set("nodih", $noDIH);
                                $this->db->set("tgldih", date("Y-m-d"));
                                $this->db->where("NoFaktur", $No);
                                $valid = $this->db->update('trs_faktur'); 
                                
                                $this->db->set("StatusCNDN", "OpenDIH");    
                                $this->db->where("NoDokumen", $No);
                                $valid = $this->db->update('trs_faktur_cndn');   
                            }else{
                                $this->db->set("Status", "OpenDIH");
                                $this->db->set("nodih", $noDIH);
                                $this->db->set("tgldih", date("Y-m-d"));
                                $this->db->where("NoFaktur", $No);
                                $valid = $this->db->update('trs_faktur');  
                                // $this->Model_faktur->prosesDataFaktur($No);
                            }   
                        }
                    }  
                    // $this->db->trans_commit();
                }
            }
            return $noDIH;
    }

   public function prosesDataFakturCNDN($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_faktur set statusPusat = 'Berhasil' where NoFaktur = '".$no."'");

            $query = $this->db->query("select * from trs_faktur_cndn where NoDokumen = '".$no."'")->result();
            foreach($query as $r) { // loop over results                
                $cek = $this->db2->query("select * from trs_faktur_cndn where NoDokumen = '".$no."'  and KodeProduk = '".$r->KodeProduk."' and noline = '".$r->noline."'")->num_rows();
                if ($cek == 0) {
                    $this->db2->insert('trs_faktur_cndn', $r); // insert each row to another table
                }
                else{
                    $this->db2->set('Approve_BM', $r->Approve_BM);
                    $this->db2->set('date_BM', $r->date_BM);
                    $this->db2->set('user_BM', $r->user_BM);
                    $this->db2->where('noline', $r->noline);
                    $this->db2->where('KodeProduk', $r->KodeProduk);
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_faktur_cndn');
                }
            }

            $query2 = $this->db->query("select * from trs_faktur where NoFaktur = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_faktur where NoFaktur = '".$no."'")->num_rows();
            if ($cek2 == 0) {
                $this->db2->insert('trs_faktur', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('NoFaktur', $no);
                $this->db2->update('trs_faktur', $query2);
            }

            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_faktur set statusPusat = 'Gagal' where NoFaktur = '".$no."'");
            return FALSE;
        }
    }
    public function listPrintDataAll($search = null, $limit = null, $status = null, $tipe = null, $byperiode = null)
    {   
        $byStatus = "";
        // if (!empty($status)) {
        //     $byStatus = " and Status = '".$status."'";
        // }
        $query = $this->db->query("select * from trs_faktur where Cabang = '".$this->cabang."' and TipeDokumen in ('Faktur','Retur','CN','DN') and Status not in ('Usulan','Batal') and ifnull(counter_print,0) < 1 $byStatus $search $byperiode order by TimeFaktur DESC $limit");

        return $query;
    }

    public function listDetailFaktur($no = NULL,$TipeDokumen = NULL)
    {
        if($TipeDokumen == 'Faktur' || $TipeDokumen == 'Retur' ){
            $query = $this->db->query("select * from trs_faktur_detail where Cabang = '".$this->cabang."' and NoFaktur = '".$no."' and 
                (ifnull(qtyfaktur,0) > 0 or ifnull(bonusfaktur,0) > 0)")->result();
        }else{
            $query = $this->db->query("SELECT trs_faktur.*,trs_faktur_cndn.* 
                                       FROM trs_faktur,trs_faktur_cndn 
                                       WHERE trs_faktur.NoFaktur = trs_faktur_cndn.NoDokumen and 
                                        trs_faktur.Cabang ='".$this->cabang."'
                                        AND trs_faktur.NoFaktur = '".$no."'")->result();
        }
        return $query;
    }
    public function listalasanjto()
    {   
        $query = $this->db->query("select keterangan from mst_alasan_jto")->result();
        return $query;
    }
    public function saveDataAlasanjto($params = NULL)
    {   
        foreach ($params->nomorfaktur as $key => $value) {
            $num_data = 0;
            $No = $params->nomorfaktur[$key];
            $this->db->set("modified_by", $this->user);
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("alasan_jto", $params->alasan[$key]);
            $this->db->where("Cabang", $this->cabang);
            $this->db->where("NoFaktur", $No);
            $valid = $this->db->update('trs_faktur');
        }
        return $valid;
    }
    public function listDataAlasanJto($search = null, $limit = null)
    {   
        $byStatus = "";
        $query = $this->db->query("SELECT * FROM trs_faktur  where ifnull(alasan_jto,'') != '' and status in ('Open','OpenDIH') $search  order by TglFaktur desc, NoFaktur ASC $limit");
        return $query;
    }

}