<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_bpb extends CI_Model {
    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->cabang = $this->session->userdata('cabang');
    }

    public function PR()
    {   
        // $query = $this->db->query("select No_PR, Cabang from(select No_PR, Cabang from trs_po_header where Status_PO='open' union all select NoPR as No_PR, Cabang` from trs_terima_barang_detail where QtyPO > Qty and Cabang ='".$this->session->userdata('cabang')."') t group by No_PR")->result();

        // $query = $this->db->query("select NoPR as No_PR, Cabang from trs_delivery_order_header where Status='Open' group by No_PR")->result();
        $query = $this->db->query("select NoDokumen,NoPR as No_PR, Cabang from trs_delivery_order_detail where Status in ('Open','',null) and ifnull(Tipe,'') != 'Relokasi' group by NoDokumen")->result();

        return $query;
    }

    public function PRPO_cabang()
    {   
        $query = $this->db->query("select distinct trs_po_detail.No_PO, trs_po_detail.No_PR, trs_po_detail.Cabang 
            from trs_po_header left join trs_po_detail on 
                 trs_po_header.No_PO = trs_po_detail.No_PO and 
                 trs_po_header.Cabang = trs_po_detail.Cabang 
            where trs_po_header.flag_suratjalan = 'N' and trs_po_detail.Status_PO = 'Open'
            union all
            select NOPO as 'No_PO',NOPR as 'No_PR',Cabang from trs_delivery_order_detail where left(NOPO,2) = 'SP' and status ='Open'")->result();
        // $query = $this->db->query("select No_PO, No_PR, Cabang from trs_po_header where flag_cabang = 'Y'")->result();
        return $query;
    }

    public function getPRPO($no = NULL)
    {   
        // $query = $this->db->query("select a.*, b.No_Usulan, b.Tipe from trs_po_detail a, trs_po_header b where a.No_PR = b.No_PR and b.No_PR = '".$no."' and a.Status_PO = 'Open'")->result();

        $byNo = "";
        $query = "";
        if (!empty($no)) {
            // $byNo = "where a.NoDokumen = '".$no."' AND a.Banyak <= a.QtyPO ";        
            // $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();
            // $query = $this->db->query("select a.* from trs_delivery_order_detail a " .$byNo)->result();

//             $query = $this->db->query("select trs_delivery_order_header.*,trs_delivery_order_detail.* 
// from trs_delivery_order_header join trs_delivery_order_detail
// on trs_delivery_order_header.NoDokumen = trs_delivery_order_detail.NoDokumen
// where trs_delivery_order_header.NoPR = '".$no."'")->result();
 
$query = $this->db->query("SELECT trs_delivery_order_header.*,trs_delivery_order_detail.*,IFNULL(terima.diterima,0) AS 'diterima'
FROM trs_delivery_order_header
left join trs_delivery_order_detail
ON trs_delivery_order_header.NoDokumen = trs_delivery_order_detail.NoDokumen
LEFT JOIN 
(SELECT trs_terima_barang_detail.`NoPO`,
       trs_terima_barang_detail.Produk,
       trs_terima_barang_detail.`BatchNo`,
       SUM(trs_terima_barang_detail.`Qty`) AS 'diterima'
FROM trs_terima_barang_detail 
GROUP BY trs_terima_barang_detail.`NoPO`,
         trs_terima_barang_detail.Produk,
         trs_terima_barang_detail.`BatchNo`) AS terima ON 
         terima.NoPO = trs_delivery_order_detail.NoPO
    AND terima.Produk = trs_delivery_order_detail.Produk
    AND terima.BatchNo = trs_delivery_order_detail.BatchNo
WHERE trs_delivery_order_detail.NoDokumen = '".$no."' AND trs_delivery_order_detail.Status = 'Open'
ORDER BY trs_delivery_order_detail.noline;
")->result();

            // $byNo = "LEFT JOIN (SELECT Cabang,NoPO,NoPR,NoUsulan,NoDokumen,Produk,SUM(Banyak) AS banyakx FROM trs_delivery_order_detail WHERE NoPR = '".$no."' GROUP BY Cabang,NoPO,NoPR,NoUsulan,Produk) b ON a.Cabang=b.Cabang AND a.NoPO=b.NoPO AND a.NoPR=b.NoPR AND a.NoUsulan=b.NoUsulan AND a.`Produk`=b.`Produk` where NoPR = '".$no."'";        

            // $query = $this->db->query("select a.* from trs_delivery_order_detail a LEFT JOIN (SELECT Cabang,NoPO,NoPR,NoUsulan,NoDokumen,Produk,SUM(Banyak) AS banyakx FROM trs_delivery_order_detail WHERE NoPR = '".$no."' GROUP BY Cabang,NoPO,NoPR,NoUsulan,Produk) b ON a.Cabang=b.Cabang AND a.NoPO=b.NoPO AND a.NoPR=b.NoPR AND a.NoUsulan=b.NoUsulan AND a.`Produk`=b.`Produk` ".$byNo)->result();
        }

        return $query;
    } 

    public function getPRPO_cabang($no = NULL)
    {   
        if (!empty($no)) {
            $query = $this->db->query("
                SELECT trs_po_detail.*,0 as 'Hrg_Beli_Cab',0 as 'Hrg_Beli_Pst'
                from trs_po_detail left join trs_po_header on trs_po_detail.No_PO = trs_po_header.No_PO and 
                     trs_po_detail.Cabang = trs_po_header.Cabang
                where trs_po_detail.No_PO = '".$no."' and trs_po_header.flag_suratjalan ='N' and trs_po_detail.Status_PO ='Open'
                ")->result();
            $i = 0;
            foreach ($query as $data) {
                $kode = $data->Produk;
                $list = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA, Dsc_Cab,Dsc_Pri,Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and Cabang ='".$this->session->userdata('cabang')."'"); 
                $num_query = $list->num_rows();
                if ($num_query <= 0) {
                    $list = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  ifnull(Cabang,'')='' limit 1")->row(); 
                }else{
                    $list = $this->db->query("select Hrg_Beli_Pst, Hrg_Beli_Cab, HNA,Dsc_Cab,Dsc_Pri, Dsc_Beli_Pst, Dsc_Beli_Cab from mst_harga where Produk ='".$kode."' and  Cabang ='".$this->session->userdata('cabang')."' limit 1")->row(); 
                } 
                $query[$i]->Hrg_Beli_Cab= $list->Hrg_Beli_Cab;
                $query[$i]->Hrg_Beli_Pst= $list->Hrg_Beli_Pst;

                $jml_terima = $this->db->query('select sum(Qty) as jml_po from trs_terima_barang_detail where NoPO="'.$no.'" and Produk="'.$kode.'" AND Status <> "Batal"')->row();
                
                if($jml_terima){
                    $query[$i]->Sisa_PO = $query[$i]->Qty_PO - $jml_terima->jml_po;
                }
                
                $i++;
                // $sisapo = $this->db->query('select sum(Qty) from trs_terima_barang_detail where NoPO="'.$no.'" and Produk="'.$kode.'"');
                // log_message('error',print_r($sisapo,true));
            }
        }
        return $query;
    } 

    public function getDO($no = NULL)
    {   
        // $query = $this->db->query("select a.*, b.No_Usulan, b.Tipe from trs_po_detail a, trs_po_header b where a.No_PR = b.No_PR and b.No_PR = '".$no."' and a.Status_PO = 'Open'")->result();

        $byNo = "";
        $query = "";
        if (!empty($no)) {
            // $byNo = "where a.NoPR = '".$no."' AND a.Banyak <= a.QtyPO ";        
            // $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();


            // $byNo = "LEFT JOIN (SELECT Cabang,NoPO,NoPR,NoUsulan,NoDokumen,Produk,SUM(Banyak) AS banyakx FROM trs_delivery_order_detail WHERE NoPR = '".$no."' GROUP BY Cabang,NoPO,NoPR,NoUsulan,Produk) b ON a.Cabang=b.Cabang AND a.NoPO=b.NoPO AND a.NoPR=b.NoPR AND a.NoUsulan=b.NoUsulan AND a.`Produk`=b.`Produk` where NoPR = '".$no."'";        

           $query = $this->db->query("select DISTINCT trs_delivery_order_detail.NoDokumen from trs_delivery_order_detail where Status = 'Open' and NoPR = '".$no."'" )->result();
        }
        return $query;
    } 

    public function getBPBPR($no = NULL)
    {
        $byNo = "";
        $query = "";
        if (!empty($no)) {
            $byNo = "where NoPR = '".$no."'";            
            $query = $this->db->query("select * from trs_terima_barang_detail ".$byNo)->result();
            // $query = $this->db->query("select * from trs_delivery_order_detail ".$byNo)->result();
        }

        return $query;
    } 
    // public function dataBPB($no = NULL)
    // {
    //     if (!empty($no)) {         
    //         $query = $this->db->query("
    //             select trs_terima_barang_detail.*,
    //             (
    //                 CASE 
    //                     WHEN trs_invdet.UnitStok IS NULL
    //                     THEN '0'
    //                     ELSE trs_invdet.UnitStok
    //                 END
    //             ) AS UnitStok
    //             from trs_terima_barang_detail
    //             left join trs_invdet on trs_invdet.BatchNo = trs_terima_barang_detail.BatchNo
    //             where trs_terima_barang_detail.NoDokumen = '".$no."' and Gudang = 'Baik'")->result();
    //     }
    //     return $query;
    // }

    public function dataBPB($no = NULL)
    {

        if (!empty($no)) {

        //     $query = $this->db->query("
        //         select distinct trs_terima_barang_detail.*,
        //         (
        //             CASE 
        //                 WHEN trs_invdet.UnitStok IS NULL
        //                 THEN '0'
        //                 ELSE trs_invdet.UnitStok
        //             END
        //         ) AS UnitStok
        //         from trs_terima_barang_detail
        //         left join trs_invdet on trs_invdet.BatchNo = trs_terima_barang_detail.BatchNo
        //         where trs_terima_barang_detail.NoDokumen = '".$no."'")->result();
        // }

         // $query = $this->db->query("
         //        select distinct trs_terima_barang_detail.*
         //        from trs_terima_barang_detail
         //        where trs_terima_barang_detail.NoDokumen = '".$no."'")->result();
            $query = $this->db->query(" SELECT trs_terima_barang_detail.*, IFNULL(trs_invdet.UnitStok,0) AS UnitStok
                FROM trs_terima_barang_detail LEFT JOIN trs_invdet ON trs_terima_barang_detail.Produk = trs_invdet.KodeProduk
                AND trs_terima_barang_detail.BatchNo = trs_invdet.BatchNo AND trs_terima_barang_detail.NoDokumen = trs_invdet.NoDokumen
                WHERE trs_terima_barang_detail.NoDokumen = '".$no."' and trs_invdet.Tahun='".date('Y')."' and Gudang= 'Baik'")->result();
           
        }
        return $query;
    }

    public function getCounterBPB($cab = NULL)
    {
        $c = $this->db->query("select Counter from mst_counter where Aplikasi = 'BPB' and Cabang = '".$cab."' limit 1");
        if ($c->num_rows() == 0) {
            $query = 1000001;
        }
        else{
            $t = $c->row();
            $query = intval($t->Counter) + 1;
        }

        return $query;
    } 

    public function saveData($params = null, $name1 = null, $name2 = null)
    {
                // log_message('error',print_r($params,true));
        $valid          = false;
        $x              = 1;
        $piutang        = 0;
        $totGross       = 0;
        $totPotongan    = 0;
        $totValue       = 0;
        $totPPN         = 0;
        $totTotal       = 0;
        $tgl            = date('d');
        $bulan          = date('m');
        $tahun          = date('y');
        $expld          = explode("/", $params->nousulan);
        $statuspo_hdr   = "Open";
        foreach ($params->produk as $key => $val){
            $diskon = 0;
            $gross = ($params->qtyterima[$key] + $params->bonus[$key])  * $params->hargabeli[$key];
            if($params->diskon[$key] > 0){
                $diskon = ($params->qtyterima[$key] * $params->hargabeli[$key]) * ( $params->diskon[$key] / 100 );
            }
            $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
            $value = $gross - $potongan;
            $ppn = $value * ( 10 / 100 );
            $total = $value + $ppn;

            $totGross = $totGross + $gross;
            $totPotongan = $totPotongan + $potongan;
            $totValue = $totValue + $value;
            $totPPN = $totPPN + $ppn;
            $totTotal = $totTotal + $total;
        }
        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."' limit 1")->row();
        
        $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$params->cabang."' and length(NoDokumen) > 23 and YEAR(TglDokumen) ='".date('Y')."' and left(NoDokumen,3) ='BPB'")->result();
        $kdCab = $this->db->query("select Kode from mst_cabang where Cabang = '".$params->cabang."' limit 1")->row();
        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = ($data[0]->no) + 1;
        }
        
        $nomorbpb       = 'BPB/'.$kdCab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;
        $ID_Paket = $this->db->query("select NoIDPaket, Pelanggan from trs_delivery_order_header where NoDokumen = '".$params->nopo."' limit 1")->row();
        if(count($ID_Paket)<=0){
            $NoIDPaket = "";
            $Pelanggan = "";
        }else{
            $NoIDPaket = $ID_Paket->NoIDPaket;
            $Pelanggan = $ID_Paket->Pelanggan;
        }

        // $jumlah_qty_sisa    = array_sum($params->qty_sisa);
        // $jumlah_qty_terima  = array_sum($params->qtyterima) + array_sum($params->bonus);
        // if($jumlah_qty_terima >= $jumlah_qty_sisa){
        //     $statuspo_hdr = "Closed";
        // }

        $expld_pr          = explode("~", $params->pr);
        $nopr =  $expld_pr[1];
        // $this->db->trans_begin();
        $this->db->set("Cabang", $params->cabang);
        $this->db->set("Prinsipal", $params->prinsipal);
        $this->db->set("Supplier", $params->supplier);
        $this->db->set("NoUsulan", $params->nousulan);
        $this->db->set("NoPR", $nopr);
        $this->db->set("NoPO", $params->nopo);
        $this->db->set("NoDO", $params->nodo);
        $this->db->set("flag_suratjalan", "Y");
        $this->db->set("Tipe", $params->tipe);
        $this->db->set("NoDokumen", $nomorbpb);
        $this->db->set("TglDokumen", date('Y-m-d'));
        $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
        $this->db->set("Attach1", $name1);
        $this->db->set("Attach2", $name2);
        $this->db->set("NoSJ", $params->nosj);
        $this->db->set("NoBEX", $params->nobex);
        $this->db->set("NoInv", $params->noinvoice);
        $this->db->set("Keterangan", $params->keterangan);
        $this->db->set("Gross", $totGross);
        $this->db->set("Potongan", $totPotongan);
        $this->db->set("Value", $totValue);
        $this->db->set("PPN", $totPPN);
        $this->db->set("Total", $totTotal);
        $this->db->set("NoIDPaket", $NoIDPaket);
        $this->db->set("Pelanggan", $Pelanggan);
        $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
        $this->db->set("UserAdd", $this->session->userdata('username'));
        $result = substr($params->nopo, 0, 2);
        $this->db->set("Status", "Closed");
        // }
        $this->db->insert('trs_terima_barang_header');

        foreach ($params->produk as $key => $value)
            {
                // if ($params->Kategori[$key] == $distinct[$kunci]) {  JIKA SAVE USULAN BELI DI PARSING BERDASARKAN KATEGORI
                if ((!empty($params->produk[$key]) and $params->qtyterima[$key] > 0) || (!empty($params->produk[$key]) and $params->bonus[$key] > 0)) {
                    $expld = explode("~", $params->produk[$key]);
                    $Produk = $expld[0];
                    $NamaProduk = $expld[1];

                    $qty_sisa   = $params->qty_sisa[$key];
                    $qty_terima = $params->qtyterima[$key] + $params->bonus[$key];
                    if($qty_terima >= $qty_sisa){
                        $status_po_detail = "Closed";
                    }else{
                        $status_po_detail = "Open";
                    }

                    $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$Produk."' limit 1")->row(); 
                    $banyak = $params->qtyterima[$key] + $params->bonus[$key];

                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Prinsipal", $params->prinsipal);
                    // $this->db->set("NamaPrinsipal", $);
                    $this->db->set("Supplier", $params->supplier);
                    // $this->db->set("NamaSupplier", $);
                    $this->db->set("Pabrik", $qproduk->Pabrik);
                    $this->db->set("NoUsulan", $params->nousulan);
                    $this->db->set("NoPR", $nopr);
                    $this->db->set("NoPO", $params->nopo);
                    $this->db->set("NoDO", $params->nodo);
                    $this->db->set("Tipe", $params->tipe);
                    $this->db->set("NoDokumen", $nomorbpb);
                    $this->db->set("NoAcuDokumen", $params->nobpb);
                    $this->db->set("TglDokumen", date('Y-m-d'));
                    $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("Status", $params->status);
                    $this->db->set("NoSJ", $params->nosj);
                    $this->db->set("NOBEX", $params->nobex);
                    $this->db->set("NoInv", $params->noinvoice);
                    $this->db->set("Produk", $Produk);
                    $this->db->set("NamaProduk", $NamaProduk);
                    $this->db->set("Satuan", $params->satuan[$key]);
                    $this->db->set("Keterangan", $params->keterangan);
                    // $this->db->set("Penjelasan", $);
                    $this->db->set("QtyPO", $params->qtypesan[$key]);
                    $this->db->set("Qty", $params->qtyterima[$key]);
                    $this->db->set("Bonus", $params->bonus[$key]);
                    $this->db->set("Banyak", $banyak);
                    $this->db->set("Disc", $params->diskon[$key]);
                    // $this->db->set("DiscT", $);
                    $this->db->set("HrgBeli", $params->hargabeli[$key]);
                    $this->db->set("BatchNo", $params->batchno[$key]);
                    $this->db->set("ExpDate", $params->expdate[$key]);
                    $this->db->set("HPC", $params->hpc[$key]);
                    $this->db->set("HPC1", $params->hpcawal[$key]);
                    $this->db->set("Harga_Beli_Pst", $params->hargabelipusat[$key]);
                    $this->db->set("HPP", $params->hpp[$key]);
                    $this->db->set("Disc_Pst", $params->diskonpusat[$key]);

                    $gross = ($params->qtyterima[$key] + $params->bonus[$key])  * $params->hargabeli[$key];
                    $diskon =  ($params->qtyterima[$key] * $params->hargabeli[$key]) * ( $params->diskon[$key] / 100 );
                    $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
                    $value = $gross - $potongan;
                    $ppn = $value * ( 10 / 100 );
                    $total = $value + $ppn;

                    $this->db->set("Gross", $gross);
                    $this->db->set("Potongan", $potongan);
                    $this->db->set("Value", $value);
                    $this->db->set("PPN", $ppn);
                    $this->db->set("Total", $total);
                    $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                    $this->db->set("UserAdd", $this->session->userdata('username'));
                    $this->db->set("noline", $key+1);
                    $this->db->set("Status", "Closed");
                    $this->db->set("QtyAwal", $params->qtyterima[$key]);
                    $this->db->set("BonusAwal", $params->bonus[$key]);
                    $this->db->set("DiscAwal", $params->diskon[$key]);
                    $this->db->set("HrgBeliAwal", $params->hargabeli[$key]);
                    $this->db->set("GrossAwal", $gross);
                    $this->db->set("PotonganAwal", $potongan);
                    $this->db->set("ValueAwal", $value);
                    $this->db->set("PPNAwal", $ppn);
                    $this->db->set("TotalAwal", $total);
                    $this->db->insert('trs_terima_barang_detail');

                    
                    //update data po detail dan do detail
                $cekQtyBPB = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_terima_barang_detail where ifnull(NODO,'') ='".$params->nodo."' and Produk ='".$Produk."' and BatchNo ='".$params->batchno[$key]."'");
                $cekQtyAllBPB = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_terima_barang_detail where ifnull(NODO,'') ='".$params->nodo."' and Produk ='".$Produk."'");
                $cekQtyDO = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_delivery_order_detail where ifnull(NoDokumen,'') ='".$params->nodo."' and Produk ='".$Produk."' and BatchNo ='".$params->batchno[$key]."'");
                $cekQtyPO = $this->db->query("select (ifnull(Qty,0)+ifnull(Bonus,0)) as 'banyak' from trs_po_detail where ifnull(No_PO,'') ='".$params->nopo."' and Produk ='".$Produk."'");
                if($cekQtyBPB->num_rows() > 0 and $cekQtyDO->num_rows() > 0){
                    $qtyBPB = $cekQtyBPB->row();
                    $qtyDO = $cekQtyDO->row();
                    if(($qtyBPB->banyak) >= ($qtyDO->banyak)){
                        $this->db->set("NoBPB", $nomorbpb);
                        $this->db->set("Status", "Closed");
                        $this->db->where("Produk", $Produk);
                        $this->db->where("BatchNo", $params->batchno[$key]);
                        $this->db->where("NoDokumen", $params->nodo);
                        $this->db->update('trs_delivery_order_detail');
                    }else{
                        $this->db->set("NoBPB", $nomorbpb);
                        $this->db->set("Status", "Open");
                        $this->db->where("Produk", $Produk);
                        $this->db->where("BatchNo", $params->batchno[$key]);
                        $this->db->where("NoDokumen", $params->nodo);
                        $this->db->update('trs_delivery_order_detail');
                    }
                }

                if($cekQtyAllBPB->num_rows() > 0 and $cekQtyPO->num_rows() > 0){
                    $qtyAllBPB = $cekQtyAllBPB->row();
                    $qtyPO = $cekQtyPO->row();
                    if(($qtyAllBPB->banyak) >= ($qtyPO->banyak)){
                        $this->db->set("StatusGIT", "Closed");
                        $this->db->set("Status_PO", "Closed");
                        $this->db->set("Modified_Time", date('Y-m-d H:i:s'));       
                        $this->db->where("Produk", $Produk);
                        $this->db->where("No_PO", $params->nopo);
                        $this->db->update('trs_po_detail');
                    }else{
                        $this->db->set("StatusGIT", "Open");
                        $this->db->set("Status_PO", "Open");
                        $this->db->set("Modified_Time", date('Y-m-d H:i:s'));       
                        $this->db->where("Produk", $Produk);
                        $this->db->where("No_PO", $params->nopo);
                        $this->db->update('trs_po_detail');
                    }
                }
            }
        }

        // if ($this->db->trans_status() === FALSE)
        // {
        //         $this->db->trans_rollback();
        //         return "gagal save bpb";
        // }
        // else
        // {
        //         $this->db->trans_commit();
        //update data po header dan do header
        $cekBPB = $this->db->query("select ifnull(NoDokumen,'') as 'NoDokumen' from trs_terima_barang_detail where ifnull(NODO,'') ='".$params->nodo."' and ifnull(status,'') Not in ('Closed','Close','')")->num_rows();
        $cekDO = $this->db->query("select ifnull(NoDokumen,'') as 'NoDokumen' from trs_delivery_order_detail where ifnull(NoDokumen,'') ='".$params->nodo."' and ifnull(status,'') Not in ('Closed','Close','')")->num_rows();
        
        if($cekBPB > 0){
            $this->db->set("Status_PO", "Open");
            $this->db->set("Modified_Time", date('Y-m-d H:i:s'));            
            $this->db->set("Modified_User", $this->session->userdata('username'));
            $this->db->where("No_PO", $params->nopo);
            $this->db->update('trs_po_header');
        }else{
            $this->db->set("Status_PO", "Closed");
            $this->db->set("Modified_Time", date('Y-m-d H:i:s'));            
            $this->db->set("Modified_User", $this->session->userdata('username'));
            $this->db->where("No_PO", $params->nopo);
            $this->db->update('trs_po_header');
        }
        if($cekDO > 0){
            $this->db->set("Status", "Open");
            $this->db->set("NoBPB", $nomorbpb);
            $this->db->where("NoDokumen", $params->nodo);
            $this->db->update('trs_delivery_order_header');
        }else{
            $this->db->set("Status", "Closed");
            $this->db->set("NoBPB", $nomorbpb);
            $this->db->where("NoDokumen", $params->nodo);
            $this->db->update('trs_delivery_order_header');
        }
        $counter = $this->db->query("select Counter from mst_counter where Aplikasi = 'BPB' and Cabang = '".$params->cabang."' limit 1")->num_rows(); 
        if ($counter > 0){
            $this->db->query("update mst_counter set Counter = '".$lastNumber."' where Aplikasi = 'BPB' and Cabang = '".$params->cabang."' limit 1");
        }else{
            $this->db->query("insert into mst_counter (`Aplikasi`, `Cabang`, `Counter`) values ('BPB', '".$params->cabang."', 1000000)");
        }
        return $nomorbpb;
        // }

    }

    public function saveDataBPBCabang($params = null, $name1 = null, $name2 = null)
    {
        $this->db2 = $this->load->database('pusat', TRUE);
        $valid = false;
        $x = 1;
        $piutang = 0;
        $totGross = 0;
        $totPotongan = 0;
        $totValue = 0;
        $totPPN = 0;
        $totTotal = 0;
        $value = 0;
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $expld = explode("/", $params->nousulan);
        $statusHeader = "Open";
        $diskon = 0;

        $terima = array_sum($params->qtyterima);
        $sisa = $params->total_sisa;

        if($terima >= $sisa){
            $statusHeader = "Closed";
        }

        foreach ($params->produk as $key => $val){
            $gross = ($params->qtyterima[$key] + $params->bonus[$key])  * $params->hargabeli[$key];
            if($params->diskon[$key] > 0){
                $diskon = ($params->qtyterima[$key] * $params->hargabeli[$key]) * ( $params->diskon[$key] / 100 );
            }
            $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
            $value = $gross - $potongan;
            $ppn = $value * ( 10 / 100 );
            $total = $value + $ppn;

            $totGross = $totGross + $gross;
            $totPotongan = $totPotongan + $potongan;
            $totValue = $totValue + $value;
            $totPPN = $totPPN + $ppn;
            $totTotal = $totTotal + $total;
        }

        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."' limit 1")->row();
        $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$params->cabang."' and length(NoDokumen) > 23 and YEAR(TglDokumen) ='".date('Y')."' and left(NoDokumen,3) ='BPB'")->result();

        $kdCab = $this->db->query("select Kode from mst_cabang where Cabang = '".$params->cabang."' limit 1")->row();

        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = ($data[0]->no) + 1;
        }
        $nomorbpb = 'BPB/'.$kdCab->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;

        $this->db->trans_begin();
        $this->db->set("Cabang", $params->cabang);
        $this->db->set("Prinsipal", $params->prinsipal);
        $this->db->set("NamaPrinsipal", $params->prinsipal);
        $this->db->set("Supplier", $params->supplier);
        $this->db->set("NamaSupplier", $params->supplier);
        $this->db->set("NoUsulan", $params->nousulan);
        $this->db->set("NoPR", $params->nopr);
        $this->db->set("NoPO", $params->pr);
        $this->db->set("Tipe", $params->tipe);
        $this->db->set("NoDokumen", $nomorbpb);
        // $this->db->set("NoAcuDokumen", $);
        $this->db->set("TglDokumen", date('Y-m-d'));
        $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
        $this->db->set("Attach1", $name1);
        $this->db->set("Attach2", $name2);
        $this->db->set("NoSJ", $params->nosj);
        $this->db->set("NoBEX", $params->nobex);
        $this->db->set("NoInv", $params->noinvoice);
        $this->db->set("Keterangan", $params->keterangan);
        // $this->db->set("Penjelasan", $);
        $this->db->set("Gross", $params->total1);
        $this->db->set("Potongan", $params->potongan);
        $this->db->set("Value", $params->total2);
        $this->db->set("PPN", $params->ppn);
        $this->db->set("Total", $params->totalvalue);
        // $this->db->set("NoIDPaket", $NoIDPaket);
        // $this->db->set("Pelanggan", $Pelanggan);

        $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
        $this->db->set("UserAdd", $this->session->userdata('username'));
        $this->db->set("Status", "pending");
        $this->db->set("flag_suratjalan", "N");
        $this->db->insert('trs_terima_barang_header');

        $this->db->set("Status_PO", $statusHeader);
        $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
        $this->db->where("No_PO", $params->pr);
        $valid = $this->db->update('trs_po_header');


        foreach ($params->produk as $key => $value){
            if (!empty($params->produk[$key]) and ($params->qtyterima[$key]+$params->bonus[$key]) > 0) {
                    $status_item = 'Open';
                    if($params->qtyterima[$key] >= $params->qty_sisa[$key] ){
                        $status_item = 'Closed';
                    }
                
                    $expld = explode("~", $params->produk[$key]);
                    $Produk = $expld[0];
                    $NamaProduk = $expld[1];

                    $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$Produk."' limit 1")->row(); 
                    $banyak = $params->qtyterima[$key] + $params->bonus[$key];

                    $this->db->set("Cabang", $params->cabang);
                    $this->db->set("Prinsipal", $params->prinsipal);
                    // $this->db->set("NamaPrinsipal", $);
                    $this->db->set("Supplier", $params->supplier);
                    // $this->db->set("NamaSupplier", $);
                    $this->db->set("Pabrik", $qproduk->Pabrik);
                    $this->db->set("NoUsulan", $params->nousulan);
                    $this->db->set("NoPR", $params->nopr);
                    $this->db->set("NoPO", $params->pr);
                    $this->db->set("Tipe", $params->tipe);
                    $this->db->set("NoDokumen", $nomorbpb);
                    $this->db->set("NoAcuDokumen", $params->nobpb);
                    $this->db->set("TglDokumen", date('Y-m-d'));
                    $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                    $this->db->set("Status", $params->status);
                    $this->db->set("NoSJ", $params->nosj);
                    $this->db->set("NOBEX", $params->nobex);
                    $this->db->set("NoInv", $params->noinvoice);
                    $this->db->set("Produk", $Produk);
                    $this->db->set("NamaProduk", $NamaProduk);
                    $this->db->set("Satuan", $params->satuan[$key]);
                    $this->db->set("Keterangan", $params->keterangan);
                    // $this->db->set("Penjelasan", $);
                    $this->db->set("QtyPO", $params->qtypesan[$key]);
                    $this->db->set("Qty", $params->qtyterima[$key]);
                    $this->db->set("Bonus", $params->bonus[$key]);
                    $this->db->set("Banyak", $banyak);
                    $this->db->set("Disc", $params->diskon[$key]);
                    // $this->db->set("DiscT", $);
                    $this->db->set("HrgBeli", $params->hargabeli[$key]);
                    $this->db->set("BatchNo", $params->batchno[$key]);
                    $this->db->set("ExpDate", $params->expdate[$key]);
                    $this->db->set("HPC", $params->hpc[$key]);
                    $this->db->set("HPC1", $params->hpcawal[$key]);
                    $this->db->set("Harga_Beli_Pst", floatval($params->hargabelipusat[$key]));
                    $this->db->set("HPP", floatval($params->hpp[$key]));
                    // $this->db->set("Disc_Pst", $params->diskonpusat[$key]);
                    
                    $gross = ($params->qtyterima[$key] + $params->bonus[$key]) * $params->hargabeli[$key];
                    if($params->diskon[$key] > 0){
                        $diskon = ($params->qtyterima[$key] * $params->hargabeli[$key]) * ( $params->diskon[$key] / 100 );
                    }
                    else{
                        $diskon = 0;   
                    }
                    $potongan = ( $params->bonus[$key] * $params->hargabeli[$key] ) + $diskon;
                    $value = $gross - $potongan;
                    $ppn = $value * ( 10 / 100 );
                    $total = $value + $ppn;

                    $this->db->set("Gross", $gross);
                    $this->db->set("Potongan", $potongan);
                    $this->db->set("Value", $value);
                    $this->db->set("PPN", $ppn);
                    $this->db->set("Total", $total);

                    $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                    $this->db->set("UserAdd", $this->session->userdata('username'));
                    $this->db->set("noline", $key+1);
                    $this->db->set("Status", "pending");
                    //data value awal, jika nanti di update sama pusat
                    $this->db->set("QtyAwal", $params->qtyterima[$key]);
                    $this->db->set("BonusAwal", $params->bonus[$key]);
                    $this->db->set("DiscAwal", $params->diskon[$key]);
                    $this->db->set("HrgBeliAwal", $params->hargabeli[$key]);
                    $this->db->set("GrossAwal", $gross);
                    $this->db->set("PotonganAwal", $potongan);
                    $this->db->set("ValueAwal", $value);
                    $this->db->set("PPNAwal", $ppn);
                    $this->db->set("TotalAwal", $total);
                    $this->db->insert('trs_terima_barang_detail');
                    // UPDATE PO GIT PO
                    $this->db->set("GIT", $params->qty_sisa[$key] - $params->qtyterima[$key]);
                    $this->db->set("StatusGIT", "Open");
                    $this->db->set("Status_PO", $status_item);
                    $this->db->set("Modified_Time", date('Y-m-d H:i:s'));
                    $this->db->where("No_PO", $params->pr);
                    $this->db->where("Produk", $Produk);
                    $this->db->update('trs_po_detail');
                    
                }
        }
        if ($this->db->trans_status() === FALSE)
        {
                $this->db->trans_rollback();
                return "gagal save bpb";
        }
        else
        {
                $this->db->trans_commit();
                $this->updatePOpusat($params->pr);
                return $nomorbpb;
        }

    }

    public function updatePOpusat($no){
        $header = $this->db->query("
                select * from trs_po_header where no_po = '".$no."'
            ")->row();
        $details = $this->db->query("
                select * from trs_po_detail where no_po = '".$no."'
            ")->result();

        // Closing PO nya karena sudah dipake disini
        $this->db2 = $this->load->database('pusat', TRUE);
        if ($this->db2->conn_id == TRUE) {  //update pusat dulu          
            $this->db2->where("No_PO", $header->No_PO);
            $this->db2->update('trs_po_header',$header);

            foreach ($details as $key => $detail) {
                $this->db2->where("No_PO", $detail->No_PO);
                $this->db2->where("noline", $detail->noline);
                $this->db2->where("Produk", $detail->Produk);
                $this->db2->update('trs_po_detail',$detail);
            }
        }

    }

   public function setStok($params = NULL, $no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  
        // $prdct = array_unique($params->produk);
        $prins = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."'")->row();        
        foreach ($params->produk as $kunci => $nilai) {
            $product1 = $params->produk[$kunci];
            $summary = 0;
            if (!empty($product1)) {
                $split = explode("~", $product1);
                $product = $split[0];
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$product."'")->row();
                $cogsx = 0;
                $summary = 0;
                $qtybyk = $params->qtyterima[$kunci] + $params->bonus[$kunci];
                $cogsx = ($params->valuebpp[$kunci]) / ($qtybyk);                        
                
                if ($cogsx == 0) {
                    $cogsx = 1;
                }
                
                $summary = $qtybyk;
                $valuestok = $summary * $cogsx;
                $UnitCOGS = $cogsx;
                // foreach ($params->produk as $key => $value) {
                //     $expld = explode("~", $params->produk[$key]);
                //     $produk = $expld[0];
                //     if ($produk == $product) {
                //         $summary = $params->qtyterima[$key] + $params->bonus[$key];
                //         $valuestok = $params->valuebpb[$key];
                //         $UnitCOGS = $params->hpc[$key];
                //     }
                // }

                // }else{                    
                //     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$product."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Git', '0.000', '".$UnitCOGS."', '0.000')");   
                // }

                // Update Gudang Baik
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();
                    $UnitStok = $invsum->UnitStok + $summary;
                    $valuestok2 = $UnitStok * $invsum->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.",  ValueStok = ".$valuestok2." where KodeProduk='".$product."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                    $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$product."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$UnitCOGS."', '0.000')");   
                }
                 // Update Gudang Git Pusat
                if ($this->db2->conn_id == TRUE) {
                    $gdgpusat = $this->db->query("select ifnull(GudangPusat,'') as 'GudangPusat' from trs_delivery_order_header where nosj ='".$params->nosj."' limit 1")->row();
                    $cabpst = $gdgpusat->GudangPusat;
                    $invsum = $this->db2->query("select * from trs_invsum where KodeProduk='".$product."' and Cabang='".$cabpst."' and Gudang='Git' and Tahun = '".date('Y')."' limit 1");
                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();
                        $UnitStok = $invsum->UnitStok - $summary;
                        $valuestok2 = $UnitStok * $invsum->UnitCOGS;
                        // update pusat
                        $this->db2->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok2." where KodeProduk='".$product."' and Cabang='".$cabpst."' and Gudang='Git' and Tahun = '".date('Y')."' limit 1");
                    }
                }

            }
        }

        // save inventori history
        foreach ($params->produk as $key => $value) {            
            if (!empty($params->produk[$key])) {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $params->qtyterima[$key] * $params->hpc[$key];
                $UnitStok = $params->qtyterima[$key] + $params->bonus[$key];
                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal, NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prins->Kode_Counter."', '".$params->prinsipal."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$params->qtyterima[$key]."', '".$valuestok."', '".$params->batchno[$key]."', '".$params->expdate[$key]."', 'Baik', 'BPB', '".$no."', '-')");
            }
        }

        // save inventori detail
        foreach ($params->produk as $key => $value) {        
            if (!empty($params->produk[$key])) {
                $expld = explode("~", $params->produk[$key]);
                $produk = $expld[0];
                $NamaProduk =$expld[1];
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$params->batchno[$key]."' and NoDokumen='".$no."' and Gudang = 'Baik' and Tahun = '".date('Y')."' limit 1");
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $valid = false;
                }else{
                    $stok = 0;
                    $UnitStok = $params->qtyterima[$key] + $params->bonus[$key];
                    $cogs = $params->hpc[$key];
                    $ValueStok = $cogs * $UnitStok;
                    //======= insert ke gudang Baik detail Cabang =====================
                    $expDD= date("Y-m-d", strtotime($params->expdate[$key]));
                    $this->db->set("Tahun", date('Y'));
                    $this->db->set("KodePrinsipal", $prins->Kode_Counter);
                    $this->db->set("NamaPrinsipal", $params->prinsipal);
                    $this->db->set("Pabrik", $prod->Pabrik);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $ValueStok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->set("KodeProduk", $produk);
                    $this->db->set("NamaProduk", $NamaProduk);
                    $this->db->set("Cabang", $this->cabang);
                    $this->db->set("Gudang", 'Baik');
                    $this->db->set("NoDokumen", $no);
                    $this->db->set("TanggalDokumen", date('Y-m-d'));
                    $this->db->set("BatchNo", $params->batchno[$key]);
                    $this->db->set("ExpDate", $params->expdate[$key]);
                    $this->db->set("UnitCOGS",$params->hpc[$key]);
                    $valid = $this->db->insert('trs_invdet');

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
                    //======= update ke gudang GIT detail pusat =====================
                    if ($this->db2->conn_id == TRUE) {
                        $noDo = $params->nosj;
                        $gudangpusat = $this->db->query("select ifnull(GudangPusat,'') as 'GudangPusat' from trs_delivery_order_header where nosj ='".$params->nosj."' limit 1")->row();
                        $cabpusat = $gudangpusat->GudangPusat;
                        $invdet2 = $this->db2->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$cabpusat."' and BatchNo='".$params->batchno[$key]."' and Gudang = 'Git' and Tahun = '".date('Y')."' and NoDokumen ='".$noDo."' limit 1");
                        $invsum2 = $invdet2->row();
                        $UnitStok2 = $invsum2->UnitStok - ($params->qtyterima[$key]+$params->bonus[$key]);
                        $cogs2 = $params->hpc[$key];
                        $ValueStok2 = $cogs2 * $UnitStok2;
                        $this->db2->set("UnitStok", $UnitStok2);
                        $this->db2->set("ValueStok", $ValueStok2);
                        $this->db2->set("UnitCOGS",$params->hpc[$key]);
                        $this->db2->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db2->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db2->where("KodeProduk", $produk);
                        $this->db2->where("Cabang", $cabpusat);
                        $this->db2->where("Gudang", 'Git');
                        $this->db2->where("NoDokumen", $noDo);
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("BatchNo", $params->batchno[$key]);
                        $valid = $this->db2->update('trs_invdet');

                        $invdet = $this->db2->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Git' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$cabpusat."'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db2->set("ValueStok", 0);;
                            $this->db2->where("KodeProduk", $produk);
                            $this->db2->where("Gudang", 'Git');
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",$cabpusat);
                            $valid = $this->db2->update('trs_invsum');

                        }else{
                            $invdet = $invdet->row();
                            $this->db2->set("ValueStok", $invdet->sumval);
                            // $this->db2->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db2->where("KodeProduk", $produk);
                            $this->db2->where("Gudang", 'Git');
                            $this->db2->where("Tahun", date('Y'));
                            $this->db2->where("Cabang",$cabpusat);
                            $valid = $this->db2->update('trs_invsum');
                        }
                    }
                }
               
            }
        }

    }

   public function prosesData($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Berhasil' where NoDokumen = '".$no."'");

            $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();
            $cek = $this->db2->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->num_rows();
            $query2 = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->row();
            $cek2 = $this->db2->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->num_rows();
            if ($cek == 0) {
                foreach($query as $r) { // loop over results
                    $this->db2->insert('trs_terima_barang_detail', $r); 
                    $cekQtyBPB = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_terima_barang_detail where ifnull(NODO,'') ='".$r->NoDO."' and Produk ='".$r->Produk."' and BatchNo ='".$r->BatchNo."'");
                    $cekQtyDO = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_delivery_order_detail where ifnull(NoDokumen,'') ='".$r->NoDO."' and Produk ='".$r->Produk."' and BatchNo ='".$r->BatchNo."'");
                    if($cekQtyBPB->num_rows() > 0 and $cekQtyDO->num_rows() > 0){
                        $qtyBPB = $cekQtyBPB->row();
                        $qtyDO = $cekQtyDO->row();
                        if(($qtyBPB->banyak) >= ($qtyDO->banyak)){
                            $this->db2->set('Status', 'Closed');
                            $this->db2->set('NoBPBCab', $no);
                            $this->db2->set('TglBPB', date('Y-m-d H:i:s'));
                            $this->db2->where('NoDokumen', $query2->NoSJ);
                            $this->db2->where('Produk', $r->Produk);
                            $this->db2->where('BatchNo', $r->BatchNo);
                            $this->db2->update('trs_surat_jalan');
                        }else{
                            $this->db2->set('Status', 'Kirim');
                            $this->db2->set('NoBPBCab', $no);
                            $this->db2->set('TglBPB', date('Y-m-d H:i:s'));
                            $this->db2->where('NoDokumen', $query2->NoSJ);
                            $this->db2->where('Produk', $r->Produk);
                            $this->db2->where('BatchNo', $r->BatchNo);
                            $this->db2->update('trs_surat_jalan');
                        }
                    }
                }
            }
            else{
                foreach($query as $r) { // loop over results
                    $this->db2->where('Produk', $r->Produk);
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_detail', $r);
                    $cekQtyBPB = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_terima_barang_detail where ifnull(NODO,'') ='".$r->NoDO."' and Produk ='".$r->Produk."' and BatchNo ='".$r->BatchNo."'");
                    $cekQtyDO = $this->db->query("select sum(ifnull(banyak,0)) as 'banyak' from trs_delivery_order_detail where ifnull(NoDokumen,'') ='".$r->NoDO."' and Produk ='".$r->Produk."' and BatchNo ='".$r->BatchNo."'");
                    if($cekQtyBPB->num_rows() > 0 and $cekQtyDO->num_rows() > 0){
                        $qtyBPB = $cekQtyBPB->row();
                        $qtyDO = $cekQtyDO->row();
                        if(($qtyBPB->banyak) >= ($qtyDO->banyak)){
                            $this->db2->set('Status', 'Closed');
                            $this->db2->set('NoBPBCab', $no);
                            $this->db2->set('TglBPB', date('Y-m-d H:i:s'));
                            $this->db2->where('NoDokumen', $query2->NoSJ);
                            $this->db2->where('Produk', $r->Produk);
                            $this->db2->where('BatchNo', $r->BatchNo);
                            $this->db2->update('trs_surat_jalan');
                        }else{
                            $this->db2->set('Status', 'Kirim');
                            $this->db2->set('NoBPBCab', $no);
                            $this->db2->set('TglBPB', date('Y-m-d H:i:s'));
                            $this->db2->where('NoDokumen', $query2->NoSJ);
                            $this->db2->where('Produk', $r->Produk);
                            $this->db2->where('BatchNo', $r->BatchNo);
                            $this->db2->update('trs_surat_jalan');
                        }
                    }
                }
            }
// return;
            
            if ($cek2 == 0) {
                $this->db2->insert('trs_terima_barang_header', $query2); // insert each row to another table
            }
            else{
                $this->db2->where('NoDokumen', $no);
                $this->db2->update('trs_terima_barang_header', $query2);
            }

            // Update Status PO
            $this->db->set('Status_PO', 'Closed');
            $this->db->where('No_PO', $query2->NoPO);
            $this->db->update('trs_po_header');

            $this->db->set('Status_PO', 'Closed');
            $this->db->where('No_PO', $query2->NoPO);
            $this->db->update('trs_po_detail');

            $this->db->set('Status', 'Closed');
            $this->db->where('NoSJ', $query2->NoSJ);
            $this->db->update('trs_delivery_order_header');

            $this->db->set('Status', 'Closed');
            $this->db->where('NoSJ', $query2->NoSJ);
            $this->db->update('trs_delivery_order_detail');

            // $this->db2->set('Status_PO', 'Closed');
            // $this->db2->where('No_PO', $query2->NoPO);
            // $this->db2->update('trs_po_header');

            // $this->db2->set('Status_PO', 'Closed');
            // $this->db2->where('No_PO', $query2->NoPO);
            // $this->db2->update('trs_po_detail');

            // Update Status DO

            $this->db2->set('Status', 'Closed');
            $this->db2->where('NoSJ', $query2->NoSJ);
            $this->db2->update('trs_delivery_order_header');

            $this->db2->set('Status', 'Closed');
            $this->db2->where('NoSJ', $query2->NoSJ);
            $this->db2->update('trs_delivery_order_detail');

            // $this->db2->set('status', 'Closed');
            // $this->db2->set('NoBPBCab', $no);
            // $this->db2->set('TglBPB', date('Y-m-d H:i:s'));
            // $this->db2->where('NoDokumen', $query2->NoSJ);
            // $this->db2->update('trs_surat_jalan');
            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Gagal' where NoDokumen = '".$no."'");
            return FALSE;
        }
    }

    public function prosesDataCabang($no = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Berhasil' where NoDokumen = '".$no."'");
            $status = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."' limit 1")->row();
            if($status->flag_suratjalan == 'N'){
                $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();
                $cek = $this->db2->query("select * from trs_terima_barang_cabang_detail where NoDokumen = '".$no."'")->num_rows();
                if ($cek == 0) {
                    foreach($query as $r) { // loop over results
                        $this->db2->insert('trs_terima_barang_cabang_detail', $r); // insert each row to another table
                    }
                }
                else{
                    foreach($query as $r) { // loop over results
                        $this->db2->where('BatchNo', $r->BatchNo);
                        $this->db2->where('Produk', $r->Produk);
                        $this->db2->where('Status', 'Pending');
                        $this->db2->where('NoDokumen', $no);
                        $this->db2->update('trs_terima_barang_cabang_detail', $r);
                    }
                }
                $query2 = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->row();
                $cek2 = $this->db2->query("select * from trs_terima_barang_cabang_header where NoDokumen = '".$no."'")->num_rows();
                if ($cek2 == 0) {
                    $this->db2->insert('trs_terima_barang_cabang_header', $query2); // insert each row to another table
                }
                else{
                    $this->db2->where('Status', 'Pending');
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_cabang_header', $query2);
                }
            }else{
                $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();
                $cek = $this->db2->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->num_rows();
                if ($cek == 0) {
                    foreach($query as $r) { // loop over results
                        $this->db2->insert('trs_terima_barang_detail', $r); // insert each row to another table
                    }
                    $this->db2->set('TglDokumen',date('Y-m-d'));
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_detail');
                }
                else{
                    foreach($query as $r) { // loop over results
                        $this->db2->where('BatchNo', $r->BatchNo);
                        $this->db2->where('Produk', $r->Produk);
                        $this->db2->where('NoDokumen', $no);
                        $this->db2->update('trs_terima_barang_detail', $r);
                    }
                }
                $query2 = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->row();
                $cek2 = $this->db2->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->num_rows();
                if ($cek2 == 0) {
                    $this->db2->insert('trs_terima_barang_header', $query2); // insert each row to another table
                    $this->db2->set('TglDokumen',date('Y-m-d'));
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_header');
                }
                else{
                    $this->db2->where('NoDokumen', $no);
                    $this->db2->update('trs_terima_barang_header', $query2);
                }
            }
            

            return TRUE;
        }
        else{
            $update = $this->db->query("update trs_terima_barang_header set statusPusat = 'Gagal' where NoDokumen = '".$no."'");
            return FALSE;
        }
    }

    public function listData($no = null)
    {   
        $byID = "";
        if(!empty($no)){
            $byID = "and NoDokumen = '".$no."'";
        }
        $query = $this->db->query("select * from trs_terima_barang_header where Cabang = '".$this->session->userdata('cabang')."' and Tipe <> 'BKB' $byID order by TglDokumen DESC");

        return $query;
    }

    public function updateDataBPBPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  

        if ($this->db2->conn_id == TRUE) {
                $data = $this->db2->query("select * from trs_terima_barang_cabang_header where Cabang = '".$this->cabang."'")->result();
                foreach ($data as $dt) {
                    $query = $this->db2->query("select * from trs_terima_barang_cabang_detail where NoDokumen = '".$dt->NoDokumen."'")->result();
                    foreach($query as $r) { // loop over results
                            $cek = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$r->NoDokumen."' and Produk = '".$r->Produk."'")->num_rows();
                            if ($cek == 0) {
                                $test = $this->db->insert('trs_terima_barang_detail', $r);
                            }
                            else{
                                $this->db->where('Produk', $r->Produk);
                                $this->db->where('NoDokumen', $r->NoDokumen);
                                $this->db->update('trs_terima_barang_detail', $r);
                            }
                    }

                    $cek = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$r->NoDokumen."'")->num_rows();
                    if ($cek == 0) {
                        $this->db->insert('trs_terima_barang_header', $dt); // insert each row to another table
                    }
                    else{
                        $this->db->where('NoDokumen', $dt->NoDokumen);
                        $this->db->update('trs_terima_barang_header', $dt);
                    }
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    public function listNoBPB(){
        $prinsipal = $this->input->post('Prinsipal');

        /*$query = $this->db->query(" SELECT NoDokumen AS nobpb FROM trs_terima_barang_header WHERE Cabang = '".$this->cabang."' AND STATUS <> 'Pending' AND NoDokumen NOT LIKE '%BKB%' AND tglDokumen >= CURDATE() - INTERVAL '1' MONTH")->result();*/

        $query = $this->db->query(" SELECT NoDokumen AS nobpb FROM trs_terima_barang_header WHERE Cabang = '".$this->cabang."' AND Prinsipal = '".$prinsipal."' AND STATUS <> 'Pending' AND NoDokumen NOT LIKE '%BKB%' ")->result();
        return $query;
    }

    public function dataDetailBKB($no){
        $query = $this->db->query("select * from trs_terima_barang_detail where NoDokumen ='".$no."'")->result();
        return $query;
    }

    public function saveDataTerimaRetur($params = null, $name1 = null, $name2 = null){
        $tgl = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $cabang = $this->db->query("select Kode from mst_cabang where Cabang ='".$this->cabang."'")->row();

        $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$params->prinsipal."' limit 1")->row();
        
        $data = $this->db->query("select max(RIGHT(NoDokumen,7)) as 'no' from trs_terima_barang_header where Cabang = '".$this->cabang."' and length(NoDokumen) > 23 and YEAR(TglDokumen) ='".date('Y')."' and left(NoDokumen,3) ='BPB'")->result();
        if(empty($data[0]->no) || $data[0]->no == ""){
            $lastNumber = 1000001;
        }else {
            $lastNumber = ($data[0]->no) + 1;
        }
        $nomorbpb = 'BPB/'.$cabang->Kode.'/'.$kd->Kode_Counter.'/'.$tgl.$bulan.$tahun.'/'.$lastNumber;

        $this->db->trans_begin();
        $this->db->set("Cabang", $this->cabang);
        $this->db->set("Prinsipal", $params->prinsipal);
        $this->db->set("NamaPrinsipal", $params->prinsipal);
        $this->db->set("Supplier", $params->supplier);
        $this->db->set("NamaSupplier", $params->supplier);
        $this->db->set("NoUsulan", "");
        $this->db->set("NoPR", "");
        $this->db->set("NoPO", "");
        $this->db->set("Tipe", "BPB Retur");
        $this->db->set("NoDokumen", $nomorbpb);
        $this->db->set("NoAcuDokumen", $params->no_bkb);
        $this->db->set("TglDokumen", date('Y-m-d'));
        $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
        $this->db->set("Attach1", $name1);
        $this->db->set("Attach2", $name2);
        $this->db->set("NoSJ", "");
        $this->db->set("NoBEX", "");
        $this->db->set("NoInv", "");
        $this->db->set("Keterangan", "");
        $this->db->set("Gross", str_replace( ',', '', $params->total_gross));
        $this->db->set("Potongan", str_replace( ',', '', $params->total_potongan));
        $this->db->set("Value", str_replace( ',', '', $params->total_value));
        $this->db->set("PPN", str_replace( ',', '', $params->total_ppn));
        $this->db->set("Total", str_replace( ',', '', $params->total_net));
        $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
        $this->db->set("UserAdd", $this->session->userdata('username'));
        $this->db->set("Status", "Close");
        $this->db->set("flag_suratjalan", "N");
        $this->db->insert('trs_terima_barang_header');

        foreach ($params->produk as $key => $value){
            if (!empty($params->produk[$key]) and $params->qtyterima[$key] > 0) {
                $expld = explode("~", $params->produk[$key]);
                $Produk = $expld[0];
                $NamaProduk = $expld[1];

                $qproduk = $this->db->query("select Pabrik from mst_produk where Kode_Produk = '".$Produk."' limit 1")->row(); 
                $banyak = $params->qtyterima[$key] + $params->bonus[$key];

                $this->db->set("Cabang", $this->cabang);
                $this->db->set("Prinsipal", $params->prinsipal);
                $this->db->set("NamaPrinsipal", $params->prinsipal);
                $this->db->set("Supplier", $params->supplier);
                $this->db->set("NamaSupplier", $params->supplier);
                $this->db->set("Pabrik", $qproduk->Pabrik);
                $this->db->set("NoUsulan", "");
                $this->db->set("NoPR", "");
                $this->db->set("NoPO", "");
                $this->db->set("Tipe", "BPB Retur");
                $this->db->set("NoDokumen", $nomorbpb);
                $this->db->set("NoAcuDokumen", $params->no_bkb);
                $this->db->set("TglDokumen", date('Y-m-d'));
                $this->db->set("TimeDokumen", date('Y-m-d H:i:s'));
                $this->db->set("Status", "");
                $this->db->set("NoSJ", "");
                $this->db->set("NOBEX", "");
                $this->db->set("NoInv", "");
                $this->db->set("Produk", $Produk);
                $this->db->set("NamaProduk", $NamaProduk);
                $this->db->set("Satuan", $params->satuan[$key]);
                $this->db->set("Keterangan", "");
                // $this->db->set("Penjelasan", $);
                $this->db->set("QtyPO", $params->qtypesan[$key]);
                $this->db->set("Qty", $params->qtyterima[$key]);
                $this->db->set("Bonus", $params->bonus[$key]);
                $this->db->set("Banyak", $banyak);
                $this->db->set("Disc", $params->diskon[$key]);
                // $this->db->set("DiscT", $);
                $this->db->set("HrgBeli", str_replace( ',', '', $params->hargabeli[$key]));
                $this->db->set("BatchNo", $params->batchno[$key]);
                $this->db->set("ExpDate", $params->expdate[$key]);
                $this->db->set("HPC", str_replace( ',', '', $params->hpc[$key]));
                $this->db->set("HPC1", "");
                $this->db->set("Harga_Beli_Pst", str_replace( ',', '', $params->hargabelipusat[$key]));
                $this->db->set("HPP", str_replace( ',', '', $params->hargabelipusat[$key]));
                // $this->db->set("HPP", str_replace( ',', '', $params->hpp[$key]));

                $this->db->set("Gross", str_replace( ',', '', $params->gross[$key]));
                $this->db->set("Potongan", str_replace( ',', '', $params->potcabang[$key]));
                $this->db->set("Value", str_replace( ',', '', $params->valuebpb[$key]));
                $this->db->set("PPN", str_replace( ',', '', $params->ppncabang[$key]));
                $this->db->set("Total", str_replace( ',', '', $params->total[$key]));

                $this->db->set("TimeAdd", date('Y-m-d H:i:s'));
                $this->db->set("UserAdd", $this->session->userdata('username'));
                $this->db->set("noline", $key+1);
                $this->db->set("Status", "Closed");

                $this->db->insert('trs_terima_barang_detail');
            }
            else{
                $statusHeader = "Open";
            }
        }

        $this->db->set("Status", "Close");
        $this->db->set("UserEdit", $this->session->userdata('username'));
        $this->db->set("TimeEdit", date('Y-m-d H:i:s'));
        $this->db->where("NoDokumen", $params->no_bkb);
        $valid = $this->db->update('trs_terima_barang_header');

        // commit transaction
        if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $respon = [
                        "status" => "Gagal",
                        "pesan" => "Gagal simpan ke database local"
                ];
        }else{
                $this->db->trans_commit();
                $this->setStokBPBretur($params,$nomorbpb);

                $respon = [
                        "status" => "sukses",
                        "pesan" => "Berhasil Disimpan ".$nomorbpb
                ];
        }



        return $respon;
    }

    public function setStokBPBretur($params = NULL,$No_Usulan=null)
    {
        // return;
        foreach ($params->produk as $key => $value) {
            $prinsipal = $params->prinsipal;
            $nama_prinsipal = $params->prinsipal;
            $kd = $this->db->query("select Kode_Counter from mst_prinsipal where Prinsipal = '".$prinsipal."' limit 1")->row();
            $qty = str_replace( ',', '', $params->qtyterima[$key]);
            $bonus = str_replace( ',', '', $params->bonus[$key]);
            $hpc = str_replace( ',', '', $params->hpc[$key]);
            $unitcogs = str_replace( ',', '', $params->unitcogs[$key]);
            $banyak = ($qty + $bonus);
            $valuestok = $banyak * $unitcogs;
            // $sisa_qty = str_replace( ',', '', $params->QtyRec[$key]) - $banyak;
            // $value_sisa_qty = $sisa_qty * $unitcogs;

            // ==================== save inventori detail ==============
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", $kd->Kode_Counter);
                $this->db->set("NamaPrinsipal", $prinsipal);
                $this->db->set("Pabrik", $params->supplier);
                $this->db->set("KodeProduk", explode("~", $value)[0]);
                $this->db->set("NamaProduk", explode("~", $value)[1]);
                $this->db->set("BatchNo", $params->batchno[$key]);
                $this->db->set("ExpDate", $params->expdate[$key]);
                $this->db->set("UnitStok", $banyak);
                $this->db->set("Gudang", 'Baik');
                $this->db->set("TanggalDokumen", date('Y-m-d'));
                $this->db->set("UnitCOGS", $unitcogs);
                $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                $this->db->set("NoDokumen", $No_Usulan);
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->insert("trs_invdet");
                // kurangi gudang baik
                // $this->db->set("UnitStok", "UnitStok-".$banyak, FALSE);
                // $this->db->set("ValueStok", "UnitStok*UnitCOGS", FALSE);
                // $this->db->set("ModifiedUser", $this->session->userdata('username'));
                // $this->db->set("ModifiedTime", date('Y-m-d h:i:s'));
                // $this->db->where("KodeProduk", explode("~", $value)[0]);
                // $this->db->where("BatchNo", $params->batchno[$key]);
                // $this->db->where("Gudang", 'Baik');
                // $this->db->update("trs_invdet");
            // }
            // ==================== save inventori Sumary ==============
            // $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".explode("~", $value)[0]."' and Cabang='".$this->cabang."' and Gudang='Retur Supplier' and Tahun = '".date('Y')."' limit 1");
            $invsum_baik = $this->db->query("select * from trs_invsum where KodeProduk='".explode("~", $value)[0]."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");

            // $invsum_baik = $invsum_baik->row();
            // $sisa_stok_baik = $invsum_baik->UnitStok - $banyak;
            // $sisa_value_stok_baik = $sisa_stok_baik * $unitcogs;

            if ($invsum_baik->num_rows() > 0) {
                // $invsum_baik = $invsum_baik->row();
                // kurangi stok baik
                $this->db->set("UnitStok", "UnitStok+".$banyak, FALSE);
                $this->db->set("ValueStok", "UnitStok*unitCOGS", FALSE);
                $this->db->set("UnitCOGS", "ValueStok/".$banyak, FALSE);
                $this->db->set("ModifiedUser", $this->session->userdata('username'));
                $this->db->set("ModifiedDate", date('Y-m-d h:i:s'));
                $this->db->where("KodeProduk", explode("~", $value)[0]);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("Tahun", date('Y'));
                $this->db->update("trs_invsum");
            }
            else{
                $this->db->set("Tahun", date('Y'));
                $this->db->set("Cabang", $this->cabang);
                $this->db->set("KodePrinsipal", $kd->Kode_Counter);
                $this->db->set("NamaPrinsipal", $prinsipal);
                $this->db->set("Pabrik", $params->supplier);
                $this->db->set("KodeProduk", explode("~", $value)[0]);
                $this->db->set("NamaProduk", explode("~", $value)[1]);
                $this->db->set("UnitStok", $banyak);
                $this->db->set("Gudang", 'Baik');
                $this->db->set("indeks", '0.000');
                $this->db->set("ValueStok", $valuestok);
                $this->db->set("UnitCOGS", $unitcogs);
                $this->db->set("HNA", '0.000');
                $this->db->set("AddedUser", $this->session->userdata('username'));
                $this->db->set("AddedTime", date('Y-m-d h:i:s'));
                $this->db->insert("trs_invsum");
            }

            // ==================== save inventori history ==============
            $this->db->set("Tahun", date('Y'));
            $this->db->set("Cabang", $this->cabang);
            $this->db->set("KodePrinsipal", $kd->Kode_Counter);
            $this->db->set("NamaPrinsipal", $prinsipal);
            $this->db->set("Pabrik", $params->supplier);
            $this->db->set("KodeProduk", explode("~", $value)[0]);
            $this->db->set("NamaProduk", explode("~", $value)[1]);
            $this->db->set("BatchNo", $params->batchno[$key]);
            $this->db->set("ExpDate", $params->expdate[$key]);
            $this->db->set("Tipe", "BPB");
            $this->db->set("UnitStok", $banyak);
            $this->db->set("Gudang", 'Retur Supplier');
            $this->db->set("ValueStok", $valuestok);
            $this->db->set("NoDokumen", $No_Usulan);
            $this->db->set("Keterangan", "-");
            $this->db->set("AddedUser", $this->session->userdata('username'));
            $this->db->set("AddedTime", date('Y-m-d h:i:s'));
            $this->db->insert("trs_invhis");
        }
    return;
    }
    public function viewbpppusat($no=null){
      $this->db2 = $this->load->database('pusat', TRUE);
        $header = $this->db2->query("SELECT bpb.NoDokumen as 'NoBPB',
                                            bpp.NoDokumen as 'NoBPP',
                                            bpp.TimeDokumen as 'TimeBPP',
                                            do.NoDokumen as 'NoDO',
                                            do.TimeDokumen as 'TimeDO'
                                    FROM trs_terima_barang_cabang_header bpb LEFT JOIN
                                         (SELECT trs_terima_barang_header.NoDokumen,
                                              trs_terima_barang_header.NoAcuDokumen,
                                              trs_terima_barang_header.TimeDokumen
                                           FROM trs_terima_barang_header) AS bpp
                                         ON bpp.NoAcuDokumen =  bpb.NoDokumen 
                                    LEFT JOIN (SELECT trs_delivery_order_header.NoDokumen,
                                                    trs_delivery_order_header.NoBPB,
                                                    trs_delivery_order_header.TimeDokumen
                                                FROM trs_delivery_order_header ) AS do 
                                           ON do.NoBPB =  bpb.NoDokumen 
                                    WHERE bpb.NoDokumen ='".$no."' and 
                                          bpb.Cabang='".$this->cabang."';")->result();
        return $header;
    }
    public function GetBPBNumber()
    {   
        $query = $this->db->query("select NoDokumen from trs_terima_barang_header where month(tgldokumen) = '".date('m')."' and year(tgldokumen) ='".date('Y')."' and tipe != 'BKB' and status != 'Batal'")->result();
        // $query = $this->db->query("select No_PO, No_PR, Cabang from trs_po_header where flag_cabang = 'Y'")->result();
        return $query;
    }

    public function load_bpb_revisi($no = null)
    {   
        $header = $this->db->query("select * from trs_terima_barang_header where NoDokumen = '".$no."'")->row();

        $detail = $this->db->query("select * from trs_terima_barang_detail where NoDokumen = '".$no."'")->result();

        $query = [
            "header" => $header,
            "detail" => $detail
        ];
        return $query;
    }

    public function update_bpb($params){
        $this->db->trans_begin();
        $cek = $this->db->query("select * from trs_terima_barang_header where nodokumen ='".$params->nobpp."' limit 1")->row();
        $this->db->set("Gross", str_replace( ',', '', $params->gross));
        $this->db->set("Potongan", str_replace( ',', '', $params->potongan));
        $this->db->set("Value", str_replace( ',', '', $params->value));
        $this->db->set("PPN", str_replace( ',', '', $params->ppn));
        $this->db->set("pph22", str_replace( ',', '', $params->pph));
        $this->db->set("Total", str_replace( ',', '', $params->total));
        $this->db->where('NoDokumen', $params->nobpp);
        $this->db->update('trs_terima_barang_header');

        foreach ($params->Produk as $key => $value){
            // $this->db->set("Qty", $params->Qty[$key]);
            // $this->db->set("Bonus", $params->Bonus[$key]);
            // $this->db->set("Banyak", $params->Qty[$key] + $params->Bonus[$key]);
            $this->db->set("Disc", $params->Disc[$key]);
            $this->db->set("HrgBeli", str_replace( ',', '', $params->Harga_Beli[$key]));
            // $this->db->set("Harga_Beli", str_replace( ',', '', $params->Harga_Beli_Pst[$key]));
            $this->db->set("pph22", str_replace( ',', '', $params->PPH22val[$key]));

            $this->db->set("Gross", str_replace( ',', '', $params->Gross[$key]));
            $this->db->set("Potongan", str_replace( ',', '', $params->Potongan[$key]));
            $this->db->set("Value", str_replace( ',', '', $params->Value[$key]));
            $this->db->set("PPN", str_replace( ',', '', $params->PPN[$key]));
            $this->db->set("Total", str_replace( ',', '', $params->Total[$key]));
            $this->db->where('NoDokumen', $params->nobpp);
            $this->db->where('Produk', $value);
            $this->db->where('BatchNo', $params->Batch[$key]);
            $this->db->update('trs_terima_barang_detail');
            if($cek->Status != 'pending' or $cek->Status != 'Batal' or $cek->Status != 'Pending'){
                $cogs = ($params->Value[$key]) / ($params->Qty[$key] + $params->Bonus[$key]);
                $this->db->update("update trs_invdet set UnitCOGS ='".$cogs."' where nodokumen ='".$params->nobpp."' and KodeProduk ='".$value."' and BatchNo ='".$params->Batch[$key]."' and Tahun ='".date('Y')."'");
            }
            
        }

        if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $respon = [
                        "status" => "gagal",
                        "pesan" => "Gagal simpan ke database local"
                ];
        }else{
                $this->db->trans_commit();
                $this->prosesDataCabang($params->nobpp);
                $respon = [
                        "status" => "sukses",
                        // "status" => $update->status,
                        "pesan" => "Data sudah diupdate"
                ];
        }
        return $respon;
    }

    function list_data_cndnBelicabang($search = null, $limit = null, $status = null){
        $byStatus = "";
        // if (!empty($status)) {
        //     $byStatus = " where TipeDokumen in ('CN','DN')";
        // }
        $query = $this->db->query("SELECT *, DATE_FORMAT(TglFaktur, '%Y-%m-%d') as tanggal FROM trs_faktur_beli_cabang where TipeDokumen in ('CN','DN') $byStatus $search order by NoFaktur desc $limit");
        return $query;
    }

    function detail_cndnbeli_cabang($no = null){
        $query = $this->db->query("SELECT * FROM trs_faktur_beli_detail_cabang where NoFaktur='".$no."'");
        return $query;
    }

    function cetakcndnbelicabang($no = null){
        $header = $this->db->query("SELECT trs_faktur_beli_cabang.*, DATE_FORMAT(TglFaktur, '%Y-%m-%d') as tanggal, mst_cabang.Alamat, mst_cabang.Kode, mst_cabang.Ijin_PBF, mst_cabang.NPWP,mst_cabang.Ijin_Alkes
                                    FROM trs_faktur_beli_cabang
                                    left join mst_cabang on mst_cabang.Cabang = trs_faktur_beli_cabang.Cabang
                                    where NoFaktur='".$no."'")->row();
        $detail = $this->db->query("SELECT * FROM trs_faktur_beli_detail_cabang where NoFaktur='".$no."'")->result();

        $output = [
            "header" => $header,
            "detail" => $detail,
        ];
        return $output;
    }

    public function updateDatacndnbeliPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  

        if ($this->db2->conn_id == TRUE) {
           $daynumber = date('d');
            if($daynumber <= 5){
                $curclose  = date('Y-m-d',strtotime("-10 days"));
            }else if($daynumber > 5 and $daynumber < 15){
                $curclose  = date('Y-m-d',strtotime("-15 days"));
            }else if($daynumber >= 15 and $daynumber < 20){
                $curclose  = date('Y-m-d',strtotime("-21 days"));
            }else if($daynumber >= 20 and $daynumber < 25){
                $curclose  = date('Y-m-d',strtotime("-26 days"));
            }else if($daynumber >= 25){
                $curclose  = date('Y-m-d',strtotime("-32 days"));
            }
            $satubulan_awal = date('Y-m-01',strtotime($curclose));
            $pr2 = $this->db2->query("select * from trs_faktur_beli_cabang where Cabang='".$this->session->userdata('cabang')."' and TglFaktur between '".$satubulan_awal."' and '".date('Y-m-d')."'")->result();
            foreach($pr2 as $r2) {
                $cekpr2 = $this->db->query("select * from trs_faktur_beli_cabang where NoFaktur = '".$r2->NoFaktur."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr2 == 0) {
                    $this->db->insert('trs_faktur_beli_cabang', $r2);
                    $this->db->set("TglFakturCabang", date('Y-m-d'));
                    $this->db->set("TimeFakturCabang", date('Y-m-d h:i:s'));
                    $this->db->where("NoFaktur", $r2->NoFaktur);
                    $this->db->where("Cabang", $this->session->userdata('cabang'));
                    $this->db->Update('trs_faktur_beli_cabang');
                    //update data pusat
                    $this->db2->set("TglFakturCabang", date('Y-m-d'));
                    $this->db2->set("TimeFakturCabang", date('Y-m-d h:i:s'));
                    $this->db2->where("NoFaktur", $r2->NoFaktur);
                    $this->db2->where("Cabang", $this->session->userdata('cabang'));
                    $this->db2->Update('trs_faktur_beli_cabang');
                    

                }
            }
            $pr = $this->db2->query("select * from trs_faktur_beli_detail_cabang where Cabang='".$this->session->userdata('cabang')."' and TglFaktur between '".$satubulan_awal."' and '".date('Y-m-d')."' ")->result();          
            foreach($pr as $r) {
                $cekpr = $this->db->query("select * from trs_faktur_beli_detail_cabang where NoFaktur = '".$r->NoFaktur."' and Produk = '".$r->Produk."' and noline = '".$r->noline."' and Cabang='".$this->session->userdata('cabang')."'")->num_rows();
                if ($cekpr == 0) {
                    $this->db->insert('trs_faktur_beli_detail_cabang', $r); // insert each row to another table
                    $this->db->set("TglFakturCabang", date('Y-m-d'));
                    $this->db->set("TimeFakturCabang", date('Y-m-d h:i:s'));
                    $this->db->where("NoFaktur", $r2->NoFaktur);
                    $this->db->where("Cabang", $this->session->userdata('cabang'));
                    $this->db->Update('trs_faktur_beli_detail_cabang');
                    if($r->TipeFaktur == 'Barang'){
                        $this->setstokvalue($r->NoFaktur,$r->NoAcuDokumen,$r->KodeProduk,$r->noline,$r->JumlahCab,$r->Banyak,$r->TipeDokumen,$r->BatchNo);
                    }

                    //update data pusat
                    $this->db2->set("TglFakturCabang", date('Y-m-d'));
                    $this->db2->set("TimeFakturCabang", date('Y-m-d h:i:s'));
                    $this->db2->where("NoFaktur", $r2->NoFaktur);
                    $this->db2->where("Cabang", $this->session->userdata('cabang'));
                    $this->db2->Update('trs_faktur_beli_detail_cabang');

                }
            }
          return 'BERHASIL';
        }
        else{
            return 'GAGAL';
        }
    }

    public function setstokvalue($nofaktur = NULL,$Nobpb = NULL, $produk = NULL,$noline = NULL,$jumlahCab = NULL, $banyak = NULL, $TipeDokumen = NULL,$BatchNo=NULL)
    {     
        $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$BatchNo."' and NoDokumen='".$Nobpb."' and Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok > 0 limit 1");
        $dt = $invdet->row();
        if ($invdet->num_rows() > 0) {
            $ValueStok = $dt->ValueStok + $jumlahCab;
            $cogs = $ValueStok / $dt->UnitStok;
            $this->db->set("ValueStok", $ValueStok);
            $this->db->set("UnitCOGS",  $cogs);
            if($dt->Keterangan == ""){
                $this->db->set("Keterangan",  $dt->UnitCOGS);
            }
            $this->db->where("KodeProduk", $produk);
            $this->db->where("Cabang", $this->cabang);
            $this->db->where("Gudang", 'Baik');
            $this->db->where("NoDokumen", $Nobpb);
            $this->db->where("BatchNo", $BatchNo);
            $this->db->where("Tahun", date('Y'));
            $valid = $this->db->update('trs_invdet');
            $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                        from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."' group by KodeProduk limit 1");
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
                $this->db->where("KodeProduk", $produk);
                $this->db->where("Gudang", 'Baik');
                $this->db->where("Tahun", date('Y'));
                $this->db->where("Cabang",$this->cabang);
                $valid = $this->db->update('trs_invsum');
            }
        }  
    }
    public function PRPO_Pusat()
    {   
        $query="";
        $this->db2 = $this->load->database('pusat', TRUE);
        if ($this->db2->conn_id == TRUE) { 
            $query = $this->db2->query("select distinct trs_po_detail.No_PO, trs_po_detail.No_PR, trs_po_detail.Cabang,trs_po_header.flag_suratjalan
                from trs_po_header left join trs_po_detail on 
                     trs_po_header.No_PO = trs_po_detail.No_PO and 
                     trs_po_header.Cabang = trs_po_detail.Cabang 
                where trs_po_header.flag_suratjalan = 'N' and trs_po_detail.Status_PO = 'Open' and trs_po_detail.Cabang = '".$this->cabang."'
                union all
                select NOPO as 'No_PO',NOPR as 'No_PR',Cabang,'Y' as flag_suratjalan from trs_delivery_order_detail where left(NOPO,2) = 'SP' and status ='Open' and Cabang = '".$this->cabang."'")->result();
        }
        // $query = $this->db->query("select No_PO, No_PR, Cabang from trs_po_header where flag_cabang = 'Y'")->result();
        return $query;
    }
}