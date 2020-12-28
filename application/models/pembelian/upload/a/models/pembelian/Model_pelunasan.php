 <?php
defined('BASEPATH') OR exit('No direct script access allowed');
// ini_set('max_execution_time', 9999);
ini_set('memory_limit','2048M');
class Model_pelunasan extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function listNoDIH()
    {   
        $query = $this->db->query("select NoDIH from trs_dih where Status = 'Open' group by NoDIH")->result();

        return $query;
    }

    public function getDataDIH($nodih = NULL)
    {   
        $query = $this->db->query("select trs_dih.*,mst_pelanggan.Pelanggan as 'nama'
                                   from trs_dih left join mst_pelanggan on 
                                        trs_dih.Pelanggan = mst_pelanggan.kode 
                                   where trs_dih.NoDIH = '".$nodih."' and trs_dih.Status = 'Open ' order by date(trs_dih.created_at),No ASC")->result();

        return $query;
    }

    public function dataPelanggan($kode = NULL)
    {   
        //$query = $this->db->query("select * from mst_pelanggan a , (select sum(saldo) as saldopiutang from trs_faktur where Pelanggan = '".$kode."' and Cabang = '".$this->cabang."' group by cabang,pelanggan) b where a.Kode = '".$kode."' and a.Cabang = '".$this->cabang."' limit 1")->row();
        $query = $this->db->query("SELECT * FROM mst_pelanggan a LEFT JOIN (SELECT SUM(saldo) AS saldopiutang,Pelanggan,Cabang FROM trs_faktur WHERE Pelanggan = '".$kode."' AND Cabang = '".$this->cabang."' GROUP BY cabang,pelanggan) b ON a.Kode=b.Pelanggan AND a.`Cabang`=b.Cabang LEFT JOIN (SELECT SUM(total) AS saldoorder,Pelanggan,Cabang FROM trs_sales_order WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS NOT LIKE '%close%' GROUP BY cabang,pelanggan) c ON a.Kode=c.Pelanggan AND a.`Cabang`=c.Cabang LEFT JOIN (SELECT SUM(total) AS saldoDlvorder,Pelanggan,Cabang FROM trs_delivery_order_sales WHERE Pelanggan = '".$kode."'AND Cabang = '".$this->cabang."' AND STATUS NOT LIKE '%close%' GROUP BY cabang,pelanggan) d ON a.Kode=d.Pelanggan AND a.`Cabang`=d.Cabang WHERE a.Kode = '".$kode."' AND a.Cabang = '".$this->cabang."' LIMIT 1")->row(); 
        return $query;
    }

     public function dataSalesman($kode = NULL)
    {   
        $query = $this->db->query("select * from mst_karyawan where Kode = '".$kode."' and Cabang = '".$this->cabang."' and Status = 'Aktif' limit 1")->row();

        return $query;
    }

   
    public function saveDataPelunasan($params = NULL, $key = NULL,$i = NULL)
    {   
        

        $xpld1 = explode("~", $params['pelanggan']);
        $kode_pelanggan = $xpld1[0];
        $dataPelanggan = $this->dataPelanggan($kode_pelanggan);
        $dataSalesman = $this->dataSalesman($params['salesman']); 
        $dataPenagih = $this->dataSalesman($params['penagih']); 
        $expld = explode(" | ", $params['giro']);
        $giro = $expld[0];
        $saldogiro = 0;
        $dtFaktur = $this->db->query("select * from trs_faktur where NoFaktur = '".$params['nofaktur']."'")->row();
        $acu2 = $dtFaktur->acu2;
        $saldo = $dtFaktur->Saldo;
        $saldogiro = $dtFaktur->saldo_giro;
        $sisaGiro = 0;
        $UmurPelunasan = 0;
        $UmurFaktur = 0;
        $tgl = date("Y-m-d");
        if($params['status_tambahan'] == 'potongan'){
            $pembulatan = $params['pembulatan'];
            $biaya_transfer = $params['biaya_transfer'] ;
            $materai = $params['materai'];
        }else if($params['status_tambahan'] == 'kelebihan'){
            $pembulatan = $params['pembulatan'];
            $biaya_transfer = $params['biaya_transfer'];
            $materai = $params['materai'];
        }else if($params['status_tambahan'] == 'ssp'){
            $pembulatan = $params['pembulatan'];
            $biaya_transfer = $params['biaya_transfer'];
            $materai = $params['materai'];
            $expld1 = explode(" | ", $params['ssp']);
            $NoNTPN = $expld1[0];
        }else{
            $pembulatan = 0;
            $biaya_transfer = 0;
            $materai = 0;
        }

        $val_tambahan = $pembulatan + $biaya_transfer + $materai;
        if ($params['tipe'] == "Giro") {
            $dtGiro = $this->db->query("select * from trs_giro where KodePelanggan = '".$kode_pelanggan."' and NoGiro = '".$giro."' and StatusGiro = 'Aktif'")->row();
            $total = $dtGiro->SisaGiro;
            if ($total >= $saldo) {
                $sisaGiro = $total - ($saldo + $val_tambahan);
                $sisaSaldo = 0;
                // $valuepelunasan = $saldo + $val_tambahan;  
                $valuepelunasan = $saldo;
                $saldogiroFaktur = $dtFaktur->saldo_giro + $saldo;                  
            }
            else{
                $sisaGiro = 0;
                $sisaSaldo = $saldo - ($total + $val_tambahan);  
                $valuepelunasan = $total; 
                $saldogiroFaktur = $dtFaktur->saldo_giro + $total + $val_tambahan; 
                // $valuepelunasan = $total + $val_tambahan;
            }
            $this->db->set("SisaGiro", $sisaGiro);
            $this->db->where("KodePelanggan", $kode_pelanggan);
            $this->db->where("NoGiro", $dtGiro->NoGiro);
            $this->db->update("trs_giro");          
            $cek = $this->db->query("select * from trs_pelunasan_detail where NomorFaktur = '".$params['nofaktur']."' order by TglPelunasan desc limit 1")->row();         
            if (!empty($cek)) {
                $this->db->set("Cicilan", $cek->ValuePelunasan);
            }
             $UmurPelunasan = floor((strtotime(date("Y-m-d"))-strtotime($params['tglfaktur']))/(60 * 60 * 24));
            $this->db->set("UmurLunas", $UmurPelunasan);
            $UmurFaktur = floor((strtotime($tgl)-strtotime($params['tglfaktur']))/(60 * 60 * 24)); 
            if($params['status_tambahan'] == 'ssp'){
                $this->db->set("NoNTPN", $NoNTPN);
            }
            $this->db->set("Giro", $dtGiro->NoGiro);
            $this->db->set("ValueGiro", $dtGiro->SisaGiro);
            $this->db->set("Status", 'Open'); 
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("TglPelunasan", date("Y-m-d"));
            $this->db->set("NoDIH", $params['nodih']);
            $this->db->set("acu2", $acu2);
            $this->db->set("noline", $i);
            $this->db->set("KodePenagih", $params['penagih']);
            $this->db->set("NamaPenagih", $dataPenagih->Nama);
            $this->db->set("NomorFaktur", $params['nofaktur']);
            $this->db->set("TglFaktur", $params['tglfaktur']);
            $this->db->set("UmurFaktur", $UmurFaktur);
            $this->db->set("KodePelanggan", $kode_pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("KodeSalesman", $params['salesman']);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("ValueFaktur", $params['total']);
            $this->db->set("SaldoFaktur", $params['saldo']);
            $this->db->set("ValuePelunasan", $valuepelunasan);
            $this->db->set("TipePelunasan", $params['tipe']);
            $this->db->set("TipeDokumen", $params['dokumen']);
            $this->db->set("bank", $params['bank_transfer']);
            $this->db->set("status_titipan", $params['titipan']);
            $this->db->set("No_Titipan", $params['notitipan']);
            $this->db->set("status_tambahan", $params['status_tambahan']);
            $this->db->set("value_pembulatan", $params['pembulatan']);
            $this->db->set("value_transfer", $params['biaya_transfer']);
            $this->db->set("materai", $params['materai']);
            $this->db->set("SaldoAkhir", $sisaSaldo);
            $this->db->set("status_DIH", 'Full');
            $this->db->set("create_by", $this->user);
            $this->db->set("create_at", date("Y-m-d H:i:s"));
            $this->db->set("keterangan", $params['keterangan']);
            if ($dtGiro->SisaGiro >= 0)
                $valid = $this->db->insert("trs_pelunasan_giro_detail");  
        }
        else{
            $valtitipan =0;
            $sisatitipan=0;
            $total = $params['value'];
            if($params['titipan'] == "titipan"){
                $split = explode(" | ", $params['notitipan']);
                $notitipan = $split[0];
                // $valtitipan = $split[1];
                $dttitipan = $this->db->query("select * from trs_buku_titipan where Status = 'Aktif'")->row();
                $valtitipan  = $dttitipan->Saldo;
                if ($valtitipan >= ($saldo + $val_tambahan)) {
                    $sisatitipan = $valtitipan - ($saldo + $val_tambahan);          
                }
                else{
                    $sisatitipan = 0;
                }
                if($sisatitipan == 0){
                   $this->db->set("Status","Closed"); 
                }
                $this->db->set("Saldo", $sisatitipan);
                $this->db->where("NoTitipan", $notitipan);
                $this->db->update("trs_buku_titipan");  

            }
            if ($params['dokumen'] == "Faktur" || $params['dokumen'] == "DN")
                {$sisaSaldo = $saldo - ($total + $val_tambahan);}
            else
                {$sisaSaldo = $saldo + ($total + $val_tambahan);}

            $valuepelunasan = $total;
            // $valuepelunasan = $total + $val_tambahan;   

            if ($sisaSaldo == 0 && $saldogiro == 0)
                {$this->db->set("Status", 'Bayar Full');}
            else
                {$this->db->set("Status", 'Cicilan'); }

            $cek = $this->db->query("select * from trs_pelunasan_detail where NomorFaktur = '".$params['nofaktur']."' order by TglPelunasan desc limit 1")->row();         
            if (!empty($cek)) {
                $this->db->set("Cicilan", $cek->ValuePelunasan);
            }
            $UmurPelunasan = floor((strtotime(date("Y-m-d"))-strtotime($params['tglfaktur']))/(60 * 60 * 24));
            $this->db->set("UmurLunas", $UmurPelunasan);
            $UmurFaktur = floor((strtotime($tgl)-strtotime($params['tglfaktur']))/(60 * 60 * 24)); 
                        // floor((strtotime($tgl)-strtotime($cek->TglPelunasan))/(60 * 60 * 24));
             if($params['status_tambahan'] == 'ssp'){
                $this->db->set("NoNTPN", $NoNTPN);
            }
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("TglPelunasan", date("Y-m-d"));
            $this->db->set("NoDIH", $params['nodih']);
            $this->db->set("acu2", $acu2);
            $this->db->set("noline", $i);
            $this->db->set("KodePenagih", $params['penagih']);
            $this->db->set("NamaPenagih", $dataPenagih->Nama);
            $this->db->set("NomorFaktur", $params['nofaktur']);
            $this->db->set("TglFaktur", $params['tglfaktur']);
            $this->db->set("UmurFaktur", $UmurFaktur);
            $this->db->set("KodePelanggan", $kode_pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("KodeSalesman", $params['salesman']);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("ValueFaktur", $params['total']);
            $this->db->set("SaldoFaktur", $params['saldo']);
            $this->db->set("ValuePelunasan", $valuepelunasan);
            $this->db->set("TipePelunasan", $params['tipe']);
            $this->db->set("TipeDokumen", $params['dokumen']);
            $this->db->set("bank", $params['bank_transfer']);
            $this->db->set("status_titipan", $params['titipan']);
            $this->db->set("No_Titipan", $params['notitipan']);
            $this->db->set("status_tambahan", $params['status_tambahan']);
            $this->db->set("value_pembulatan", $params['pembulatan']);
            $this->db->set("value_transfer", $params['biaya_transfer']);
            $this->db->set("materai", $params['materai']);
            $this->db->set("SaldoAkhir", $sisaSaldo);
            $this->db->set("status_DIH", 'Full');
            $this->db->set("create_by", $this->user);
            $this->db->set("create_at", date("Y-m-d H:i:s"));
            $this->db->set("keterangan", $params['keterangan']);
            $valid = $this->db->insert("trs_pelunasan_detail"); 
        }     
         $tdih = $this->db->query("select TglDIH from trs_dih where nodih = '".$params['nodih']."' limit 1")->row();  
         $tgldih = $tdih->TglDIH;  
        if ($params['dokumen'] == "Faktur" || $params['dokumen'] == "Retur") {
            if ($params['tipe'] != "Giro") {
                if ($sisaSaldo == 0 && $saldogiro == 0)
                    {$this->db->set("Status", 'Lunas'.$params['tipe']);}
                else
                    {$this->db->set("Status", 'Open');}

                $this->db->set("Saldo", $sisaSaldo);  
                $this->db->set("saldo_giro", 0);           
            }
            else{
                if($sisaSaldo==0){
                    $this->db->set("Status", 'Giro');
                }else{
                    $this->db->set("Status", 'Open');
                }
                $this->db->set("Saldo", $sisaSaldo);
                $this->db->set("saldo_giro", $saldogiroFaktur);
            }
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("nodih", $params['nodih']);
            $this->db->set("tgldih", $tgldih);
            $this->db->set("TglPelunasan", date('Y-m-d'));
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoFaktur", $params['nofaktur']);
            $this->db->update("trs_faktur");
        }
        else{
            if ($params['tipe'] != "Giro") {
                if ($sisaSaldo == 0)
                    {$this->db->set("Status", 'Lunas'.$params['tipe']);}
                else
                    {$this->db->set("Status", 'Open');}

                $this->db->set("Saldo", $sisaSaldo);  
                $this->db->set("saldo_giro", 0);               
            }
            else{
                if($sisaSaldo==0){
                    $this->db->set("Status", 'Giro');
                }else{
                    $this->db->set("Status", 'Open');
                }
                $this->db->set("Saldo", $sisaSaldo);
                $this->db->set("saldo_giro", $saldogiroFaktur);
            }
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("nodih", $params['nodih']);
            $this->db->set("tgldih", $tgldih);
            $this->db->set("TglPelunasan", date('Y-m-d'));
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoFaktur", $params['nofaktur']);
            $this->db->update("trs_faktur");


            $this->db->set("StatusCNDN", "Closed");
            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("updated_at", date('Y-m-d H:i:s'));
            $this->db->set("updated_by", $this->user);
            $this->db->where("NoDokumen", $params['nofaktur']);
            $this->db->update("trs_faktur_cndn");
        }

        if ($params['tipe'] != "Giro") {
            $this->db->set("Saldo", $sisaSaldo);            
        }
        $this->db->set("Status", 'Closed');
        $this->db->set("updated_by", $this->user);
        $this->db->set("updated_at", date("Y-m-d H:i:s"));
        $this->db->where("NoFaktur", $params['nofaktur']);
        $this->db->where("NoDIH", $params['nodih']);
        $this->db->update("trs_dih");

    }
    public function listDataDIH($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status) && $search == '') {
            $byStatus = "  where Status = '".$status."' ";
        }else if (!empty($status) && $search != '') {
            $byStatus = " where Status = '".$status."' and ";
        }else if($status == null && $search != ''){
            $byStatus = " where ";
        }
        $query = $this->db->query("SELECT Cabang,NoDIH,Penagih,TglDIH,Status,SUM(Total) AS 'Total',SUM(Saldo) AS 'Saldo', Sum(ifnull(Total - Saldo,0)) as Sisa,statusPusat FROM trs_dih  $byStatus $search GROUP BY Cabang,NoDIH,Penagih,TglDIH,Status order by TglDIH desc, NoDIH ASC $limit");
        return $query;
    }

    public function dataDetailDIH($no = NULL,$status = NULL)
    {
         // $query = $this->db->query("SELECT trs_dih.*,ifnull(Total - Saldo,0) as 'Sisa' FROM trs_dih where NoDIH = '".$no."' and status = '".$status."' ")->result();
         $query = $this->db->query("
            select distinct trs_dih.*,ifnull(trs_dih.Total - trs_dih.Saldo,0) as 'Sisa',
              mst_pelanggan.Pelanggan as nama_pelanggan, 
              mst_karyawan.Nama as nama_penagih,
              IFNULL(trs_pelunasan_detail.UmurFaktur,DATEDIFF(trs_dih.TglDIH,trs_dih.TglFaktur)) as UmurFaktur,
              trs_pelunasan_detail.SaldoFaktur as SaldoFaktur,
              trs_pelunasan_detail.ValuePelunasan as ValuePelunasan,
              trs_pelunasan_detail.TipeDokumen as TipeDokumen,
              trs_pelunasan_giro_detail.Giro as Giro
            from trs_dih
             left join mst_pelanggan on mst_pelanggan.Kode = trs_dih.Pelanggan 
             left join mst_karyawan on mst_karyawan.Kode = trs_dih.Penagih
             left join trs_pelunasan_detail on trs_pelunasan_detail.NoDIH = trs_dih.NoDIH 
              and trs_pelunasan_detail.NomorFaktur = trs_dih.NoFaktur
             left join trs_pelunasan_giro_detail on trs_pelunasan_giro_detail.NoDIH = trs_dih.NoDIH 
              and trs_pelunasan_giro_detail.NomorFaktur = trs_dih.NoFaktur
            where trs_dih.NoDIH = '".$no."' and trs_dih.status = '".$status."' order by date(trs_dih.created_at),No ASC
          ")->result();
         return $query;
    }
    
    public function listGiro($pelanggan = NULL)
    {   
        $query = $this->db->query("select NoGiro, SisaGiro from trs_giro where KodePelanggan = '".$pelanggan."' and StatusGiro = 'Aktif' and SisaGiro > 0")->result();

        return $query;
    }

    public function listSSP($noDIH = NULL,$nofaktur = NULL,$nontpn = NULL)
    {   
        $query = $this->db->query("select * from trs_pelunasan_detail_ssp where noDIH = '".$noDIH."' and NoFaktur = '".$nofaktur."' and Cabang ='".$this->cabang."'")->result();

        return $query;
    }

    public function listTitipan()
    {   
        $query = $this->db->query("select * from trs_buku_titipan where Status ='Aktif' and Cabang ='".$this->cabang."'")->result();
        return $query;
    }

    public function saveDataSSP($params = NULL)
    {
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("NoDIH", $params->ssp_dih);
        $this->db->set("TglDIH", $params->ssp_tgldih);
        $this->db->set("NoFaktur", $params->ssp_nofaktur);
        $this->db->set("TglFaktur", $params->ssp_tglfaktur);
        $this->db->set("NoNTPN", $params->nontpn);
        $this->db->set("TglBayar", $params->tglbayar);
        $this->db->set("MataAnggaran", $params->mata_anggaran);
        $this->db->set("JenisSetoran", $params->jenis_setoran);
        $this->db->set("MasaPajak", $params->masa_pajak);
        $this->db->set("JumlahSetoran", $params->jumlah_setoran);
        $this->db->set("create_at", date("Y-m-d H:i:s"));
        $this->db->set("create_by", $this->user);
        $valid = $this->db->insert("trs_pelunasan_detail_ssp");
        return $valid;
    }

    public function saveDataGiro($params = NULL)
    {
        //16092020
        $cek = $this->db->query("select ifnull(count(NoGiro),0) as jml from trs_giro where NoGiro='".$params->giro."'")->row();
        if($cek->jml > 0){
            $valid ='SudahAda';
        }else{
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("StatusGiro", "Aktif");
        $this->db->set("KodePelanggan", $params->pelanggan);
        $this->db->set("Bank", $params->bank);
        $this->db->set("NoGiro", $params->giro);
        $this->db->set("TglJTO", $params->tgljto);
        $this->db->set("ValueGiro", $params->valuegiro);
        $this->db->set("SisaGiro", $params->valuegiro);
        $this->db->set("create_at", date("Y-m-d H:i:s"));
        $this->db->set("create_by", $this->user);
        $valid = $this->db->insert("trs_giro");
    }
    // log_message("error",print_r($valid,true));
        return $valid;
    }

    public function getFakturDIH($nodih = null, $nofaktur = null)
    {   
        $query = $this->db->query("select * from trs_dih where NoDIH = '".$nodih."' and nofaktur = '".$nofaktur."'")->row();

        return $query;
    }

    // public function listDataPelunasan($tipe = null)
    public function listDataPelunasan($search = null, $limit = null, $status = null, $tipe = null)
    {   
        // $byStatus = "";
        // if ($tipe != "All") {
        //     $byStatus = " where TipePelunasan = '".$tipe."'";
        // }
        // $query = $this->db->query("select * from trs_pelunasan_detail $byStatus order by TglPelunasan desc")->result();

        // return $query;
        $byStatus = "";

        //log_message('error','tipePelunasan='.print_r($tipe,true));

        if ($tipe == "All") {
                //log_message("error","All");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang
                //                             UNION ALL
                //                             SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' $search
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' $search
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                                        ");
        }elseif ($tipe == "GiroOut") {
                //log_message("error","GiroOut");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang and  trs_pelunasan_detail.STATUS='Open'  AND trs_pelunasan_detail.TipePelunasan = 'Giro'
                //                             UNION ALL
                //                             SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang and  trs_pelunasan_giro_detail.STATUS='Open'  AND trs_pelunasan_giro_detail.TipePelunasan = 'Giro'
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Giro' $search
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and Status='Open' $search
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit 
                                        ");
        }elseif ($tipe == "Giro") {
                //log_message("error","Giro");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang and  trs_pelunasan_detail.Status='Giro Cair' and trs_pelunasan_detail.TipePelunasan = 'Giro'
                //                             UNION ALL
                //                             SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang and  trs_pelunasan_giro_detail.Status='Giro Cair'  and trs_pelunasan_giro_detail.TipePelunasan = 'Giro'
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Giro' and Status='GiroCair' $search
                //                             UNION ALL
                //                             SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Giro' and Status='GiroCair' $search
                //                             )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                //                         ");
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Giro' $search
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and Status='Open' $search
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                                        ");
        }elseif ($tipe == "Cash") {
                //log_message("error","Cash");
                // $query = $this->db->query("SELECT * FROM (
                //                              SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang and  trs_pelunasan_detail.Status in ('Cash','Bayar Full', 'Cicilan') and trs_pelunasan_detail.TipePelunasan = 'Cash'
                //                             UNION ALL
                //                              SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang and  trs_pelunasan_giro_detail.Status in ('Cash','Bayar Full', 'Cicilan')  and trs_pelunasan_giro_detail.TipePelunasan = 'Cash'
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Cash' $search
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Cash' $search
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                                        ");
        }elseif ($tipe == "Transfer") {
                //log_message("error","Transfer");
                // $query = $this->db->query("SELECT * FROM (
                //                             SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang and  trs_pelunasan_detail.Status in ('Cash','Bayar Full', 'Cicilan')  and trs_pelunasan_detail.TipePelunasan = 'Transfer'
                //                             UNION ALL
                //                             SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang and  trs_pelunasan_giro_detail.Status in ('Cash','Bayar Full', 'Cicilan')  and trs_pelunasan_giro_detail.TipePelunasan = 'Transfer'
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Transfer' $search
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Transfer' $search
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                                        ");
        }else {
                //log_message("error","Else");
                // $query = $this->db->query("SELECT * FROM (
                //                              SELECT trs_pelunasan_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_detail,trs_dih 
                //                             where trs_dih.NoDIH = trs_pelunasan_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_detail.Cabang 
                //                             UNION ALL
                //                             SELECT trs_pelunasan_giro_detail.*,trs_dih.TipeDokumen FROM trs_pelunasan_giro_detail,trs_dih
                //                              where trs_dih.NoDIH = trs_pelunasan_giro_detail.NoDIH and
                //                                   trs_dih.NoFaktur = trs_pelunasan_giro_detail.NomorFaktur and 
                //                                   trs_dih.Cabang = trs_pelunasan_giro_detail.Cabang 
                //                             )xx ORDER BY NoDIH DESC, KodePelanggan DESC, NomorFaktur ASC;
                //                         ")->result();            
                $query = $this->db->query("SELECT * FROM (
                                            SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."'
                                            UNION ALL
                                            SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."'
                                            )xx ORDER BY TglPelunasan DESC, NoDIH DESC, KodePelanggan ASC, `NomorFaktur` ASC $limit
                                        ");
        }
        //log_message("error","XX");
        return $query;
        
    }



    public function listregisterpenerimaangiro($search = null, $limit = null, $status = null, $tipe = null)
    {
       $query = $this->db->query("SELECT xx.*,yy.TglJTO FROM (
                                    SELECT * FROM trs_pelunasan_detail where Cabang='".$this->cabang."' and TipePelunasan = 'Giro' $search
                                    UNION ALL
                                    SELECT * FROM trs_pelunasan_giro_detail where Cabang='".$this->cabang."' and Status='Open' $search
                                    )xx 
                                        LEFT JOIN (SELECT Cabang,KodePelanggan,NoGiro,ValueGiro,Bank,TglJTO FROM `trs_giro`) yy    
                                            ON xx.Cabang=yy.Cabang AND xx.KodePelanggan=xx.KodePelanggan AND xx.Giro=yy.NoGiro AND xx.Bank=yy.Bank
                                        -- WHERE xx.`TglPelunasan`='2018-09-01'
                                        ORDER BY xx.TglPelunasan DESC, xx.NoDIH DESC, xx.KodePelanggan ASC, xx.`NomorFaktur` ASC
                                    $limit");
        //log_message("error","XX");
        return $query;
    }



    public function listDataGiro()
    {   
        //$query = $this->db->query("select * from trs_giro where Cabang = '".$this->cabang."' and NoGiro in (select Giro from trs_pelunasan_detail where status in ('Giro', 'GiroCair'))")->result(); 
         
        $query = $this->db->query("SELECT *,a.Cabang AS cabangG FROM trs_giro a LEFT JOIN (SELECT DISTINCT Giro,Cabang FROM trs_pelunasan_giro_detail WHERE Status = 'Open' and Cabang='".$this->cabang."') b ON a.Cabang=b.Cabang AND a.NoGiro=b.giro WHERE a.Cabang = '".$this->cabang."' Order By a.StatusGiro ASC,date(a.create_at) DESC")->result();

        return $query;
    }

    public function prosesGiroKliring($nogiro = null)
    {   
        $valid=false;
        $sumgiro = $this->db->query("select sum(ifnull(ValuePelunasan,'')+ifnull(value_pembulatan,'')+ifnull(value_transfer,'')+ifnull(materai,'')) as 'valgiro' from trs_pelunasan_giro_detail where Giro = '".$nogiro."' and Status= 'Open'")->row();
        $giro = $this->db->query("select sisaGiro,ValueGiro from trs_giro where NoGiro = '".$nogiro."' and StatusGiro='Aktif'")->row();
        $sumgiro = $sumgiro->valgiro;
        $sisagiro = $giro->sisaGiro;
        $valgiro = $giro->ValueGiro;
        $status = false;
        if($sisagiro == 0 and $valgiro == $sumgiro){
            $status = true;
        }
        if($status == true){
            $dt = $this->db->query("select a.*, b.Saldo as sisa,b.saldo_giro, b.Total from trs_pelunasan_giro_detail a, trs_faktur b where a.`Giro` = '".$nogiro."' and a.Status = 'Open' and a.`NomorFaktur` = b.`NoFaktur`")->result();
            // log_message("error",print_r($dt,true));
            $i=0;
            $this->db->set("StatusGiro", "Cair");
            $this->db->set("TglCair", date("Y-m-d"));
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoGiro", $nogiro);
            $valid = $this->db->update("trs_giro");

            foreach ($dt as $row) {
                $i++;
                $valLunas = $this->db->query("SELECT SUM(ValuePelunasan) as vlunas FROM trs_pelunasan_detail WHERE STATUS NOT LIKE '%batal%' AND STATUS NOT LIKE '%tolak%' AND  NomorFaktur ='".$row->NomorFaktur."'")->row();
                $saldo = $row->Total - ($row->ValuePelunasan+$valLunas->vlunas);
                $saldogiroFaktur = $row->saldo_giro - $row->ValuePelunasan;
                if($saldogiroFaktur < 0){
                  $saldogiroFaktur = 0; 
                }
                $UmurPelunasan = floor((strtotime(date("Y-m-d"))-strtotime($row->TglFaktur))/(60 * 60 * 24));
                if ($saldo == 0){
                    $this->db->set("Status", 'LunasGiro');
                }
                else{
                    $this->db->set("Status", 'Open');
                }
                $tdih = $this->db->query("select TglDIH from trs_dih where nodih = '".$row->NoDIH."' limit 1")->row();  
                $tgldih = $tdih->TglDIH; 
                // log_message("error",print_r($dt,true));
                $this->db->set("Saldo", $saldo);
                $this->db->set("saldo_giro", $saldogiroFaktur);
                $this->db->set("TglPelunasan", date('Y-m-d'));
                $this->db->set("statusPusat", "Gagal");
                $this->db->set("nodih", $row->NoDIH);
                $this->db->set("tgldih", $tgldih);
                $this->db->set("modified_at", date('Y-m-d H:i:s'));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoFaktur", $row->NomorFaktur);
                $valid = $this->db->update("trs_faktur");

                $this->db->set("Saldo", $saldo); 
                $this->db->where("NoFaktur", $row->NomorFaktur);           
                $this->db->where("NoDIH", $row->NoDIH);
                $this->db->update("trs_dih");

                $this->db->set("Status", "Closed");
                $this->db->set("TglGiroCair", date("Y-m-d"));
                $this->db->set("modified_at", date("Y-m-d H:i:s"));
                $this->db->set("modified_by", $this->user);
                $this->db->where("NoDIH", $row->NoDIH);
                $this->db->where("NomorFaktur", $row->NomorFaktur);
                $valid = $this->db->update("trs_pelunasan_giro_detail");

                $this->db->set("Giro", $row->Giro);
                $this->db->set("ValueGiro", $row->ValueGiro);
                $this->db->set("TglGiroCair", date("Y-m-d"));
                $this->db->set("Status", "GiroCair");
                $this->db->set("UmurLunas", $UmurPelunasan);
                $this->db->set("Cicilan", $row->Cicilan);
                $this->db->set("Cabang", $row->Cabang);
                $this->db->set("TglPelunasan", $row->TglPelunasan);
                $this->db->set("NoDIH", $row->NoDIH);
                $this->db->set("noline", $i);
                $this->db->set("KodePenagih", $row->KodePenagih);
                $this->db->set("NomorFaktur", $row->NomorFaktur);
                $this->db->set("TglFaktur", $row->TglFaktur);
                $this->db->set("UmurFaktur", $UmurPelunasan);
                $this->db->set("KodePelanggan", $row->KodePelanggan);
                $this->db->set("KodeSalesman", $row->KodeSalesman);
                $this->db->set("ValueFaktur", $row->ValueFaktur);
                $this->db->set("SaldoFaktur", $row->SaldoFaktur);
                $this->db->set("TipeDokumen", $row->TipeDokumen);
                $this->db->set("status_DIH", $row->status_DIH);
                $this->db->set("ValuePelunasan", $row->ValuePelunasan);
                $this->db->set("TipePelunasan", $row->TipePelunasan);
                $this->db->set("SaldoAkhir", $row->SaldoAkhir);
                $this->db->set("create_by", $this->user);
                $this->db->set("create_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert("trs_pelunasan_detail");  
                if($saldo == 0){
                    $this->db->set("Status", 'Closed');        
                }
                // else{
                //     $this->db->set("Status", 'Open');
                // }
                $this->db->set("updated_by", $this->user);
                $this->db->set("updated_at", date("Y-m-d H:i:s"));
                $this->db->where("NoFaktur", $row->NomorFaktur);
                $this->db->where("NoDIH", $row->NoDIH);
                $this->db->update("trs_dih");

                // $this->updateDIHparsial($row->NoDIH,$row->NomorFaktur);
            }
        
        }else{
            $valid = false;
        }
        return $valid;
    }

    public function prosesGiroBatal($nogiro = null)
    {   
        $data = $this->db->query("select * from trs_pelunasan_giro_detail where Giro = '".$nogiro."' and Status = 'Open'")->result();
        $i=0;
        foreach ($data as $dt) {
            $giro = $this->db->query("select * from trs_giro where KodePelanggan = '".$dt->KodePelanggan."' and NoGiro = '".$dt->Giro."' and StatusGiro='Aktif'")->row();
            $i++;
            $sisa = $giro->SisaGiro + $dt->ValuePelunasan;
            $this->db->set("StatusGiro", 'Batal');
            $this->db->set("SisaGiro", $sisa);
            $this->db->where("KodePelanggan", $dt->KodePelanggan);
            $this->db->where("NoGiro", $dt->Giro);
            $valid = $this->db->update("trs_giro");  

            $dtFaktur = $this->db->query("select * from trs_faktur where NoFaktur = '".$dt->NomorFaktur."'")->row();
            $addsaldo = $dtFaktur->Saldo + $dt->ValuePelunasan;
            $kurangisaldogiro = $dtFaktur->saldo_giro - $dt->ValuePelunasan;
            if($kurangisaldogiro < 0){
               $kurangisaldogiro = 0; 
            }
            $this->db->set("Saldo", $addsaldo);    
            $this->db->set("saldo_giro", $kurangisaldogiro);         
            $this->db->set("Status", "Open");
            $this->db->where("NoFaktur", $dt->NomorFaktur);
            $this->db->update("trs_faktur");      

            $this->db->set("Status", "Closed");
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDIH", $dt->NoDIH);
            $this->db->where("NomorFaktur", $dt->NomorFaktur);
            $valid = $this->db->update("trs_pelunasan_giro_detail"); 

            $this->db->set("Giro", $dt->Giro);
            $this->db->set("ValueGiro", $dt->ValueGiro);
            $this->db->set("TglGiroCair", date("Y-m-d"));
            $this->db->set("Status", "GiroBatal");
            $this->db->set("UmurLunas", $dt->UmurLunas);
            $this->db->set("Cicilan", $dt->Cicilan);
            $this->db->set("Cabang", $dt->Cabang);
            $this->db->set("TglPelunasan", $dt->TglPelunasan);
            $this->db->set("NoDIH", $dt->NoDIH);
            $this->db->set("noline", $i);
            $this->db->set("KodePenagih", $dt->KodePenagih);
            $this->db->set("NomorFaktur", $dt->NomorFaktur);
            $this->db->set("TglFaktur", $dt->TglFaktur);
            $this->db->set("UmurFaktur", $dt->UmurFaktur);
            $this->db->set("KodePelanggan", $dt->KodePelanggan);
            $this->db->set("KodeSalesman", $dt->KodeSalesman);
            $this->db->set("ValueFaktur", $dt->ValueFaktur);
            $this->db->set("SaldoFaktur", $dt->SaldoFaktur);
            $this->db->set("ValuePelunasan", $dt->ValuePelunasan);
            $this->db->set("TipePelunasan", $dt->TipePelunasan);
            $this->db->set("SaldoAkhir", $dt->SaldoAkhir);
            $this->db->set("TipeDokumen", $dt->TipeDokumen);
            $this->db->set("status_DIH", $dt->status_DIH);
            $this->db->set("create_by", $this->user);
            $this->db->set("create_at", date("Y-m-d H:i:s"));
            $valid = $this->db->insert("trs_pelunasan_detail");  
        }

        return $valid;
    }

    public function prosesGiroTolak($nogiro = null)
    {   
        $value = 0;
        $data = $this->db->query("select * from trs_pelunasan_detail where Giro = '".$nogiro."' and Status = 'GiroCair'")->result();
        foreach ($data as $dt) {
            $fak = $this->db->query("select * from trs_faktur where NoFaktur = '".$dt->NomorFaktur."'")->row();            

            $saldo = $fak->Saldo + $dt->ValuePelunasan;
            $saldogiroFaktur = $fak->saldo_giro - $dt->ValuePelunasan;
            if($saldogiroFaktur < 0){
                $saldogiroFaktur = 0;
            }
            $this->db->set("Status", 'Open');
            $this->db->set("Saldo", $saldo);
            $this->db->set("saldo_giro", $saldogiroFaktur);
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoFaktur", $dt->NomorFaktur);
            $valid = $this->db->update("trs_faktur");            

            $this->db->set("Status", "GiroTolak");
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoDIH", $dt->NoDIH);
            $this->db->where("NomorFaktur", $dt->NomorFaktur);
            //16092020
            $this->db->where("Giro", $nogiro);
            $valid = $this->db->update("trs_pelunasan_detail");  

            $value = $value + $dt->ValuePelunasan;
        }

        $giro = $this->db->query("select * from trs_giro where NoGiro = '".$nogiro."'")->row();

        $sisa = $giro->SisaGiro + $value;
        $tolak = $giro->Tolak + 1;
        if ($tolak >= 3)
            {$this->db->set("StatusGiro", 'Tidak Aktif');}
        else
            {$this->db->set("StatusGiro", 'Aktif');}
        
        $this->db->set("Tolak", $tolak);
        $this->db->set("SisaGiro", $sisa);
        $this->db->where("NoGiro", $nogiro);
        $valid = $this->db->update("trs_giro");        

        return $valid;
    }   

    public function listFakturDIH()
    {   
        $query = $this->db->query("select trs_dih.*,mst_pelanggan.Pelanggan as 'nama'
                                   from trs_dih left join mst_pelanggan on 
                                        trs_dih.Pelanggan = mst_pelanggan.kode  where Status = 'Open'")->result();

        return $query;
    } 

    public function prosesFakturDIH($params = NULL, $key = NULL, $i = NULL)
    {  
        $xpld1 = explode("~", $params['Pelanggan']); 
        $Kode_pelanggan = $xpld1[0];
        $dataPelanggan = $this->dataPelanggan($Kode_pelanggan);
        $dataSalesman = $this->dataSalesman($params['Salesman']); 
        $dataPenagih = $this->dataSalesman($params['Penagih']); 
        $expld = explode(" | ", $params['giro']); 
        $giro = $expld[0];
        $saldogiro = 0;
        $dtFaktur = $this->db->query("select * from trs_faktur where NoFaktur = '".$params['nofaktur']."'")->row();
        $saldo = $dtFaktur->Saldo;
        $saldogiro = $dtFaktur->saldo_giro;
        $acu2 = $dtFaktur->acu2;
        $sisaGiro = 0;
        $tgl = date("Y-m-d");
        // $dt = $this->db->query("select * from trs_dih where nofaktur = '".$no."' and status = 'Open' limit 1")->row();
        $UmurFaktur = floor((strtotime($tgl)-strtotime($params['TglFaktur']))/(60 * 60 * 24));  
        $UmurPelunasan = floor((strtotime(date("Y-m-d"))-strtotime($params['TglFaktur']))/(60 * 60 * 24));

        if($params["tipe"] == 'Cash'){
            $bank_transfer = "";
        }else{
            $bank_transfer = $params["bank_transfer"];
        }

        if(!$params["titipan"]){
            $titipan = "";
            $notitipan = "";
        }else{
            $titipan = $params["titipan"];
            $notitipan = $params["titipan"];
        }
        if(empty($params["status_tambahan"])){
            $status_tambahan = "";
            $pembulatan = 0;
            $biaya_transfer = 0;
            $materai = 0;
        }else{
           $status_tambahan = $params["status_tambahan"]; 
        }
        if($status_tambahan == 'potongan'){
            $pembulatan = $params["pembulatan"];
            $biaya_transfer = $params["biaya_transfer"];
            $materai = $params["materai"];
        }elseif($status_tambahan == 'kelebihan'){
            $pembulatan = $params["pembulatan"];
            $biaya_transfer = 0;
            $materai = 0;
        }else if($status_tambahan == 'ssp'){
            $pembulatan = $params['pembulatan'];
            $biaya_transfer = $params['biaya_transfer'];
            $materai = $params['materai'];
            $expld1 = explode(" | ", $params['ssp']);
            $NoNTPN = $expld1[0];
        }elseif($status_tambahan == ""){
            $pembulatan = 0;
            $biaya_transfer = 0;
            $materai = 0;
        }
        // $saldo_akhir = $params["Saldo"]-$params["value"] + $pembulatan + $biaya_transfer + $materai;
        $val_tambahan = $pembulatan + $biaya_transfer + $materai;
        if ($params['tipe'] == "Giro") {
            $dtGiro = $this->db->query("select * from trs_giro where KodePelanggan = '".$Kode_pelanggan."' and NoGiro = '".$giro."' and StatusGiro='Aktif' ")->row();
            $total = $dtGiro->SisaGiro;
            if ($total > $saldo) {
                $sisaGiro = $total - ($saldo + $val_tambahan);
                $sisaSaldo = 0;
                $valuepelunasan = $saldo; 
                $saldogiroFaktur = $dtFaktur->saldo_giro + $saldo;
                // $valuepelunasan = $saldo + $val_tambahan;                 
            }
            else{
                $sisaGiro = 0;
                $sisaSaldo = $saldo - ($total + $val_tambahan);
                $valuepelunasan = $total; 
                $saldogiroFaktur =  $dtFaktur->saldo_giro + $total + $val_tambahan;    
                // $valuepelunasan = $total + $val_tambahan;
            }

            $this->db->set("SisaGiro", $sisaGiro);
            $this->db->where("KodePelanggan", $Kode_pelanggan);
            $this->db->where("NoGiro", $dtGiro->NoGiro);
            $this->db->update("trs_giro");          

            $cek = $this->db->query("select * from trs_pelunasan_detail where NomorFaktur = '".$params['nofaktur']."' order by TglPelunasan desc limit 1")->row();         
            if (!empty($cek)) {
                $UmurPelunasan = floor((strtotime($tgl)-strtotime($cek->TglPelunasan))/(60 * 60 * 24)); 
                $this->db->set("UmurLunas", $UmurPelunasan);
                $this->db->set("Cicilan", $cek->ValuePelunasan);
            }
            
            $UmurFaktur = floor((strtotime($tgl)-strtotime($params['TglFaktur']))/(60 * 60 * 24)); 
            if($status_tambahan == 'ssp'){
                $this->db->set("NoNTPN", $NoNTPN);
            }

            $this->db->set("Giro", $dtGiro->NoGiro);
            $this->db->set("ValueGiro", $dtGiro->SisaGiro);
            $this->db->set("Status", 'Open'); 
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("TglPelunasan", date("Y-m-d"));
            $this->db->set("NoDIH", $params['NoDIH']);
            $this->db->set("acu2", $acu2);
            $this->db->set("noline", $i);
            $this->db->set("KodePenagih", $params['Penagih']);
            $this->db->set("NamaPenagih", $dataPenagih->Nama);
            $this->db->set("NomorFaktur", $params['nofaktur']);
            $this->db->set("TglFaktur", $params['TglFaktur']);
            $this->db->set("UmurFaktur", $UmurFaktur);
            $this->db->set("KodePelanggan", $Kode_pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("KodeSalesman", $params['Salesman']);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("ValueFaktur", $params['Total']);
            $this->db->set("SaldoFaktur", $params['Saldo']);
            $this->db->set("ValuePelunasan", $valuepelunasan);
            $this->db->set("TipePelunasan", $params['tipe']);
            $this->db->set("TipeDokumen", $params['dokumen']);
            $this->db->set("bank", $params['bank_transfer']);
            $this->db->set("status_titipan", $params['titipan']);
            $this->db->set("No_Titipan", $params['notitipan']);
            $this->db->set("status_tambahan", $params['status_tambahan']);
            $this->db->set("value_pembulatan", $params['pembulatan']);
            $this->db->set("value_transfer", $params['biaya_transfer']);
            $this->db->set("materai", $params['materai']);
            $this->db->set("SaldoAkhir", $sisaSaldo);
            $this->db->set("status_DIH", 'Parsial');
            $this->db->set("keterangan", $params['keterangan']);
            $this->db->set("create_by", $this->user);
            $this->db->set("create_at", date("Y-m-d H:i:s"));
            if ($dtGiro->SisaGiro >= 0)
                $valid = $this->db->insert("trs_pelunasan_giro_detail");  
        }else{
            $valtitipan =0;
            $sisatitipan=0;
            $total = $params['value'];
            if($params['titipan'] == "titipan"){
                $split = explode(" | ", $params['notitipan']);
                $notitipan = $split[0];
                // $valtitipan = $split[1];
                $dttitipan = $this->db->query("select * from trs_buku_titipan where Status = 'Aktif' and NoTitipan= '".$notitipan."'")->row();
                $valtitipan  = $dttitipan->Saldo;
                if ($valtitipan >= ($saldo + $val_tambahan)) {
                    $sisatitipan = $valtitipan - ($saldo + $val_tambahan);          
                }
                else{
                    $sisatitipan = 0;
                }
                if($sisatitipan == 0){
                   $this->db->set("Status","Closed"); 
                }
                $this->db->set("Saldo", $sisatitipan);
                $this->db->where("NoTitipan", $notitipan);
                $this->db->update("trs_buku_titipan");  

            }
            $sisaSaldo = 0;
            if ($params['dokumen'] == "Faktur" || $params['dokumen'] == "DN"){
               $sisaSaldo = $saldo - ($total + $val_tambahan);
            }else{
               $sisaSaldo = $saldo + ($total + $val_tambahan); 
            }    
                // $valuepelunasan = $total;
                
            $valuepelunasan = $total; 
            // $valuepelunasan = $total + $val_tambahan;   

            if ($sisaSaldo == 0 && $saldogiro == 0)
                {$this->db->set("Status", 'Bayar Full');}
            else
                {$this->db->set("Status", 'Cicilan');}  

            $cek = $this->db->query("select * from trs_pelunasan_detail where NomorFaktur = '".$params['nofaktur']."' order by TglPelunasan desc limit 1")->row();         
            if (!empty($cek)) {
                $UmurPelunasan = (strtotime($tgl)-strtotime($cek->TglPelunasan))/86400;
                $this->db->set("UmurLunas", $UmurPelunasan);
                $this->db->set("Cicilan", $cek->ValuePelunasan);
            }
            
            $UmurFaktur = (strtotime($tgl)-strtotime($params['TglFaktur']))/86400; 
            // $this->db->set("Status", "Cicilan");
            if($status_tambahan == 'ssp'){
                $this->db->set("NoNTPN", $NoNTPN);
            }
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("TglPelunasan", date("Y-m-d"));
            $this->db->set("NoDIH", $params["NoDIH"]);
            $this->db->set("acu2", $acu2);
            $this->db->set("noline", $i);
            $this->db->set("KodePenagih", $params["Penagih"]);
            $this->db->set("NamaPenagih", $dataPenagih->Nama);
            $this->db->set("NomorFaktur", $params["nofaktur"]);
            $this->db->set("TglFaktur", $params["TglFaktur"]);
            $this->db->set("UmurFaktur", $UmurFaktur);
            $this->db->set("KodePelanggan", $Kode_pelanggan);
            $this->db->set("NamaPelanggan", $dataPelanggan->Nama_Faktur);
            $this->db->set("KodeSalesman", $params["Salesman"]);
            $this->db->set("NamaSalesman", $dataSalesman->Nama);
            $this->db->set("ValueFaktur", $params["Total"]);
            $this->db->set("SaldoFaktur", $params["Saldo"]);
            $this->db->set("ValuePelunasan", $valuepelunasan);
            $this->db->set("TipePelunasan", $params["tipe"]);
            $this->db->set("TipeDokumen", $params['dokumen']);
            $this->db->set("SaldoAkhir",$sisaSaldo);
            $this->db->set("create_by", $this->user);
            $this->db->set("create_at", date("Y-m-d H:i:s"));
            $this->db->set("bank", $bank_transfer);
            $this->db->set("status_titipan", $titipan);
            $this->db->set("No_Titipan", $notitipan);
            $this->db->set("status_tambahan", $status_tambahan);
            $this->db->set("value_pembulatan", $pembulatan);
            $this->db->set("value_transfer", $biaya_transfer);
            $this->db->set("materai", $materai);
            $this->db->set("status_DIH", 'Parsial');
            $this->db->set("keterangan", $params['keterangan']);
            $valid = $this->db->insert("trs_pelunasan_detail");  
        }
        $tdih = $this->db->query("select TglDIH from trs_dih where nodih = '".$params["NoDIH"]."' limit 1")->row();  
        $tgldih = $tdih->TglDIH;
        if ($params['dokumen'] == "Faktur" || $params['dokumen'] == "Retur") {
            if ($params['tipe'] != "Giro") {
                if ($sisaSaldo == 0 && $saldogiro == 0)
                   { $this->db->set("Status", 'Lunas'.$params['tipe']);}
                else
                    {$this->db->set("Status", 'Open');}

                $this->db->set("Saldo", $sisaSaldo);  
                $this->db->set("saldo_giro", 0);            
            }
            else{
                if($sisaSaldo == 0){
                   $this->db->set("Status", 'Giro'); 
               }else{
                   $this->db->set("Status", 'Open'); 
               }
                $this->db->set("Saldo", $sisaSaldo);
                $this->db->set("saldo_giro", $saldogiroFaktur);
            }
            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("nodih", $params["NoDIH"]);
            $this->db->set("tgldih", $tgldih);
            $this->db->set("TglPelunasan", date('Y-m-d'));
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoFaktur", $params['nofaktur']);
            $this->db->update("trs_faktur");
        }else{
            if ($params['tipe'] != "Giro") {
                if ($sisaSaldo == 0)
                    {$this->db->set("Status", 'Lunas'.$params['tipe']);}
                else
                    {$this->db->set("Status", 'Open');}

                $this->db->set("Saldo", $sisaSaldo); 
                $this->db->set("saldo_giro", 0);            
            }
            else{
                if($sisaSaldo==0){
                    $this->db->set("Status", 'Giro');
                }else{
                    $this->db->set("Status", 'Open');
                }
                $this->db->set("Saldo", $sisaSaldo);
                $this->db->set("saldo_giro", $saldogiroFaktur);
            }
            $this->db->set("statusPusat", "Gagal");
            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("nodih", $params["NoDIH"]);
            $this->db->set("tgldih", $tgldih);
            $this->db->set("TglPelunasan", date('Y-m-d'));
            $this->db->set("modified_at", date('Y-m-d H:i:s'));
            $this->db->set("modified_by", $this->user);
            $this->db->where("NoFaktur", $params['nofaktur']);
            $this->db->update("trs_faktur");

            $this->db->set("umur_pelunasan", $UmurPelunasan);
            $this->db->set("umur_faktur", $UmurFaktur);
            $this->db->set("StatusCNDN", "Closed");
            $this->db->set("updated_at", date('Y-m-d H:i:s'));
            $this->db->set("updated_by", $this->user);
            $this->db->where("NoDokumen", $params['nofaktur']);
            $this->db->update("trs_faktur_cndn");
        }

        if ($params['tipe'] != "Giro") {
            $this->db->set("Saldo", $sisaSaldo);  
            if($sisaSaldo == 0 ){
                $this->db->set("Status", 'Closed');
            }        
        }
        else{
            $this->db->set("Status", 'Open');
        }
        $this->db->set("updated_by", $this->user);
        $this->db->set("updated_at", date("Y-m-d H:i:s"));
        $this->db->where("NoFaktur", $params['nofaktur']);
        $this->db->where("NoDIH", $params['NoDIH']);
        $this->db->update("trs_dih");
        // $this->updateDIHparsial($params["NoDIH"],$params['nofaktur']);
        return $valid;
    }  

    public function prosesDataPelunasan($noDIH = NULL, $NomorFaktur = NULL, $i = NULL)
    {
        $valid = false;
        $query = $this->db->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' and NomorFaktur = '".$NomorFaktur."'")->result();

        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            foreach ($query as $dt) {
                $cek = $this->db2->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' and NomorFaktur = '".$dt->NomorFaktur."' and create_at = '".$dt->create_at."'")->num_rows();
                $data = $this->db->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' and NomorFaktur = '".$dt->NomorFaktur."' and create_at = '".$dt->create_at."'")->row();
                if ($cek == 0) {
                    $valid = $this->db2->insert("trs_pelunasan_detail", $data);
                }
                else{                    
                    $this->db2->where("NoDIH", $noDIH);
                    $this->db2->where("NomorFaktur", $dt->NomorFaktur);
                    $this->db2->where("create_at", $dt->create_at);        
                    $valid = $this->db2->update("trs_pelunasan_detail");
                }
            }
        }

        return $valid;
    }

    public function updateDIHparsial($noDIH = NULL,$nofaktur=NULL){
    	$valid = false;
    	$ext = true;
        $query = $this->db->query("select * from trs_dih where NoDIH = '".$noDIH."' ")->result();
        foreach ($query as $dih) {
        	
            $nofaktur = $dih->NomorFaktur;
            $query2 = $this->db->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' and NomorFaktur ='".$nofaktur."' order by create_at Desc Limit 1 ")->result();
            foreach ($query2 as $faktur) {
                $status = $faktur->Status;
                $SaldoAkhir = $dih->SaldoAkhir;
                if(($status == "Bayar Full" || $status == "GiroCair")  && $SaldoAkhir == 0){
                    $ext = true;
                }else{
                    $ext = false;
                    break;
                }
            }    
        }
        if($ext == true){
        	$this->db->set("Status", "Closed");
        	$this->db->where("NoDIH", $noDIH);
        	$this->db->update("trs_dih"); 
        }
    }
    public function listDataDIHdetail($search = null, $limit = null, $status = null)
    {   
        $byStatus = "";
        if (!empty($status)) {
            $byStatus = " where Status = '".$status."'";
            $query = $this->db->query("SELECT * FROM trs_dih $byStatus $search  order by TglDIH desc, NoDIH ASC $limit");
        }else{
            $query = $this->db->query("SELECT * FROM trs_dih where Status in ('Open','Closed') $search  order by TglDIH desc, NoDIH ASC $limit");
        }
        return $query;
    }
    // public function updateDIH($noDIH = NULL,$nofaktur=NULL){
    //     $valid = false;
    //     $ext = true;
    //     $query = $this->db->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' ")->result();
    //     foreach ($query as $dih) {
            
    //         $nofaktur = $dih->NomorFaktur;
    //         $query2 = $this->db->query("select * from trs_pelunasan_detail where NoDIH = '".$noDIH."' and NomorFaktur ='".$nofaktur."' order by create_at Desc Limit 1 ")->result();
    //         foreach ($query2 as $faktur) {
    //             $status = $faktur->Status;
    //             $SaldoAkhir = $dih->SaldoAkhir;
    //             if(($status == "Bayar Full" || $status == "GiroCair")  && $SaldoAkhir == 0){
    //                 $ext = true;
    //             }else{
    //                 $ext = false;
    //             }
    //         }    
    //     }
    //     if($ext == true){
    //         $this->db->set("Status", "Closed");
    //         $this->db->where("NoDIH", $noDIH);
    //         $this->db->update("trs_dih"); 
    //     }
    // }
}