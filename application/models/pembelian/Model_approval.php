<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_approval extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->load->model('pembelian/Model_approval');
            $this->load->model('pembelian/Model_order');
            $this->load->model('pembelian/Model_orderKhusus');
            $this->load->model('pembelian/Model_kiriman');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
    }



    function data_api($params) {

            $ch     = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL             => "http://119.235.19.137:8222/panel/apipusat/grobak?".$params,
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

    function getDataApprovalOrderKhusus(){
        $query = $this->data_api("method=databmplafon&cabang=".$this->cabang);
        return $query;
    }

    function getDataDetailPelanggan($kode){
        $query = $this->data_api("method=analisisplafon&kode=".$kode);
        return $query;
    }

    function prosesDataApprovalOrderKhusus($id,$tipe){
        if ($tipe == "Approve") {
          $query = $this->data_api("method=approvebm&id=".$id);
          $result = $query['response_code'];
           // $message = "sukses";
        }else{
          $query = $this->data_api("method=rejectfinance&id=".$id);
          $result = $query['response_code'];
            // $message = "sukses";
        }

        return $result;
    }


    public function listDataApproval($search = NULL, $length = NULL, $start = NULL, $status = NULL, $byfilter=null, $byLimit=null)
    {
        $byStatus = "";
        $statusLimit = "Limit";
        $statusTOP = "TOP";
        $statusDC = "DC";
        $statusDP = "DP";
        $approve_KSA = "N";
        $approve_BM = "N";
        if ($status == "Limit") {
            if ($this->group == "KSA") {
                $byStatus = "and StatusLimit <> 'Ok' and ifnull(Approve_KSA,'N') = '".$approve_KSA."' and Status not in ('Closed','Reject')";
            }elseif($this->group == "BM"){
                 $byStatus = "and StatusLimit <> 'Ok' and ifnull(Approve_BM,'N') = '".$approve_BM."' and ifnull(Approve_KSA,'N') in ('Y','R') and Status not in ('Closed','Reject')";
            }elseif($this->group == "RBM"){
                $byStatus = "and StatusLimit <> 'Ok' and  ifnull(Approve_RBM,'N') = 'N' and Status <> 'Closed'";
            }else{
                 $byStatus = " and Status in ('Usulan','Closed','Reject')";
                  // $byStatus = " and Status in ('Usulan')";
            }
        }
        elseif ($status == "TOP") {
            if ($this->group == "KSA") {
                $byStatus = "and StatusTOP <> 'Ok' and ifnull(Approve_TOP_KSA,'N') = '".$approve_KSA."' and Status not in ('Closed','Reject') ";
            }elseif($this->group == "BM"){
                $byStatus = "and StatusTOP <> 'Ok' and ifnull(Approve_TOP_BM,'N') = '".$approve_BM."' and ifnull(Approve_TOP_KSA,'N') in ('Y','R') and Status not in ('Closed','Reject')";
            }elseif($this->group == "RBM"){
                $byStatus = "and StatusTOP <> 'Ok' and ifnull(Approve_TOP_RBM,'N') = '".$approve_BM."' and Status not in ('Closed','Reject')";
            }else{
                 $byStatus = " and Status in ('Usulan','Closed','Reject')";
                  // $byStatus = " and Status in ('Usulan')";
            }
        }
        elseif ($status == "DC") {

            if($this->group == "BM"){
                 $byStatus = "and StatusDiscCab <> 'Ok' and ifnull(Approve_DiscCab_BM,'N') = '".$approve_BM."' and Status not in ('Closed','Reject')";
            }elseif( $this->group == "RBM" ){
                $byStatus = "and StatusDiscCab <> 'Ok' and ifnull(Approve_DiscCab_RBM,'N') = 'N' and Status not in ('Closed','Reject')";
            }else{
                 $byStatus = " and Status in ('Usulan','Closed','Reject')";
                  // $byStatus = " and Status in ('Usulan')";
            }

        }
        elseif ($status == "DP") {
            if($this->group == "BM"){
                 $byStatus = "and StatusDiscPrins <> 'Ok' and ifnull(Approve_DiscPrins_BM,'N') = '".$approve_BM."' and Status not in ('Closed','Reject')";
            }elseif($this->group == "BM"){
                $byStatus = "and StatusDiscPrins <> 'Ok' and ifnull(Approve_DiscPrins_RBM,'N') = '".$approve_BM."' and Status not in ('Closed','Reject')";
            }elseif($this->group == "Pusat" ){
                $byStatus = "and StatusDiscPrins <> 'Ok' and ifnull(Approve_DiscPrins_pusat,'N') = 'N' and Status not in ('Closed','Reject')";
            }
            else{
                 $byStatus = " and Status in ('Usulan','Closed','Reject')";
                  // $byStatus = " and Status in ('Usulan')";
            }
        }else{
            $byStatus = " and Status in ('Usulan','Closed','Reject')";
        } 
         $query = $this->db->query("select * from trs_sales_order where (NoSo like '%".$search."%' or TglSO like '%".$search."%' or Pelanggan like '%".$search."%') $byStatus $byLimit");
        // log_message("error",print_r($query,true));    
        return $query;
    }



    public function listDataDoOutstanling($search = NULL)
    {
        $byStatus = "and StatusLimit = 'Ok' and StatusTOP = 'Ok' and StatusDiscCab = 'Ok' and StatusDiscPrins = 'Ok' and Status not in  ('Reject','Closed')";
         $query = $this->db->query("select * from trs_sales_order where (NoSo like '%".$search."%' or TglSO like '%".$search."%' or Pelanggan like '%".$search."%')  $byStatus and IFNULL(NoDO,'') = '' AND DATE_FORMAT(TglSO,'%Y-%m') = '".date('Y-m')."' order by NoSo ASC");    
        return $query;
    }

    public function cekpiutangpelanggan($pelanggan = null)
    {
        $query = $this->db->query("select mst_pelanggan.limit_kredit as 'Limit_Pelanggan',
                                           mst_pelanggan.top as 'toppel',
                                           faktur_pelanggan.saldo as 'Limit_Kredit',
                                           faktur_pelanggan.Umur_faktur as 'TOP'
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(trs_pelunasan_giro_detail.`ValuePelunasan`) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.`Pelanggan`
                                      where ((trs_faktur.Status IN ( 'Open','OpenDIH','Giro') AND IFNULL(trs_faktur.Status,'') NOT LIKE '%je%') OR trs_faktur.Saldo!= 0)
                                       AND (trs_faktur.TipeDokumen IN ('Faktur','Retur') OR trs_faktur.NoFaktur LIKE 'INV%' OR trs_faktur.NoFaktur LIKE 'RTF%')
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$pelanggan."'")->row();

        return $query;
    }

    public function cekpiutangpelanggan_acu2($pelanggan = null)
    {
        $query = $this->db->query("SELECT
                                      mretail.limit_kredit AS 'Limit_Pelanggan', mretail.top AS 'toppel', IFNULL(faktur_pelanggan.saldo,0) AS 'Limit_Kredit', IFNULL(faktur_pelanggan.Umur_faktur,0) AS 'TOP'FROM mretail LEFT JOIN (SELECT trs_faktur.acu2, (SUM(IFNULL (saldo, 0)) + IFNULL (sumgiro.totalgiro, 0) ) AS 'saldo', MAX(DATEDIFF (CURDATE(), trs_faktur.`TglFaktur`) ) AS 'Umur_faktur'FROM trs_faktur LEFT JOIN (SELECT trs_pelunasan_giro_detail.`KodePelanggan`, SUM(trs_pelunasan_giro_detail.`ValuePelunasan` ) AS 'totalgiro'FROM trs_pelunasan_giro_detail WHERE STATUS = 'Open'GROUP BY trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON sumgiro.KodePelanggan = trs_faktur.`Pelanggan` WHERE (LEFT (trs_faktur.Status, 5) != 'Lunas'OR trs_faktur.`Saldo` != 0 ) GROUP BY acu2) AS faktur_pelanggan ON faktur_pelanggan.acu2 = mretail.Kode WHERE mretail.Kode = '$pelanggan'")->row();

        return $query;
    }


    public function prosesData($No = NULL, $status = NULL,$tipe=NULL)
    {
       $this->db2 = $this->load->database('pusat', TRUE); 
       $cek2 = $this->db->query("select trs_sales_order.* ,
                                            mst_pelanggan.Limit_Kredit
                                     from trs_sales_order,mst_pelanggan
                                     where trs_sales_order.Pelanggan = mst_pelanggan.Kode and 
                                     trs_sales_order.NoSO = '".$No."'")->row(); 
       // $cek = $this->cekpiutangpelanggan($cek2->Pelanggan);

       if ($cek2->acu2 == "") {
            $cek = $this->cekpiutangpelanggan($cek2->Pelanggan);
       }else{
            $cek = $this->cekpiutangpelanggan_acu2($cek2->acu2);
       }
       
        $limit_BM       = (int)$this->Model_main->CabangDev()[0]->limit_jual_BM;
        $limit_RMB      = $this->Model_main->CabangDev()[0]->limit_jual_RBM;  
        $limit_TOP_KSA  = $this->Model_main->CabangDev()[0]->top_KSA;
        $limit_TOP_BM   = $this->Model_main->CabangDev()[0]->top_BM;  
        $total_limit    = (int)$cek->Limit_Kredit + (int)$cek2->Total;
        if(!$limit_BM){
            $limit_BM = 0;
        }
        if(!$limit_RMB){
            $limit_RMB = 0;
        }
        $byStatus = "";
        $statusLimit = "";
        $statusTOP = "";
        $statusDC = "";
        $statusDP = "";
        $statusApprove = "";
        $message = "";
        log_message('error',print_r($No,true));
        //====== Approval Limit Jual ===================
        if($status == "Limit") {
            if ($this->group == "BM") {
                if($tipe =='Approve'){
                    $statusLimit = "Ok";
                    $approve_bm  = "Y";
                }else if($tipe =='Reject'){
                    $statusLimit = "Limit";
                    $approve_bm  = "R";
                }
                
                $date_approve_bm = date('Y-m-d H:i:s');
                $user_bm = $this->session->userdata('username');
                $statusApprove = "Limit Approval BM";   
                $byStatus = ", Status = 'Usulan'";  
                if($total_limit <= $limit_BM){
                    log_message('error','limit 1');
                    if($tipe =='Approve'){
                        log_message('error','approve');
                        $update = $this->db->query("update trs_sales_order 
                                set StatusLimit = 'Ok', 
                                    statusPusat = 'Gagal',
                                    limit_piutang_bm = '".$total_limit."',
                                    Approve_BM = '".$approve_bm."', 
                                    date_approve_BM = '".$date_approve_bm."',
                                    modified_at = '".$date_approve_bm."',
                                    modified_by = '".$user_bm."',
                                    user_BM = '".$user_bm."' $byStatus where NoSO = '".$No."'");
                    }else{
                        log_message('error','reject');
                        $update = $this->db->query("update trs_sales_order 
                            set StatusLimit = 'Limit', 
                                statusPusat = 'Gagal',
                                limit_piutang_bm = '".$total_limit."',
                                Approve_BM = '".$approve_bm."', 
                                date_approve_BM = '".$date_approve_bm."',
                                modified_at = '".$date_approve_bm."',
                                modified_by = '".$user_bm."',
                                user_BM = '".$user_bm."' $byStatus 
                                where NoSO = '".$No."'");
                    }
                    $cekulang = $this->db->query("select * from trs_sales_order where noso ='".$No."' limit 1")->row();
                    log_message('error',print_r($cekulang->StatusLimit,true));
                    log_message('error',print_r($cekulang->Approve_BM,true));
                    if(($cekulang->StatusLimit == 'Limit') and $cekulang->Approve_BM == 'Y'){
                        log_message('error','Update top Ulang');
                        $this->db->query("update trs_sales_order
                                set StatusLimit = 'Ok' 
                                where noso ='".$No."'");
                    }
                    $status_update = "ok";
                }else{
                    log_message('error','limit 2');
                    $update = $this->db->query("update trs_sales_order 
                              set StatusLimit = 'Limit', 
                                  statusPusat = 'Gagal',
                                  limit_piutang_bm = '".$total_limit."',
                                  Approve_BM = '".$approve_bm."', 
                                  date_approve_BM = '".$date_approve_bm."',
                                  modified_at = '".$date_approve_bm."',
                                  modified_by = '".$user_bm."',
                                  user_BM = '".$user_bm."' $byStatus 
                              where NoSO = '".$No."'");
                    $status_update = "ok";
                    $message = "Limited";   
                    if($tipe =='Approve'){
                          $this->Model_order->prosesDataSO($No);
                    }             
                } 
                               
            }elseif ($this->group == "KSA") {

                if($tipe =='Approve'){
                    $statusLimit = "Limit";
                    $approve_ksa  = "Y";
                }else if($tipe =='Reject'){
                    $statusLimit = "Limit";
                    $approve_ksa  = "R";
                }
                // $statusLimit = "Limit";
                // $approve_ksa  = "Y";
                $date_approve_ksa = date('Y-m-d');
                $user_ksa = $this->session->userdata('username');
                $statusApprove = "Limit Approval BM"; 
                $byStatus = ", Status = 'Usulan'";            
                // if($total_limit <= $limit_BM){
                    $update = $this->db->query("update trs_sales_order 
                              set StatusLimit = '".$statusLimit."', 
                                  statusPusat = 'Gagal',
                                  limit_piutang_ksa = '".$total_limit."',
                                  Approve_KSA = '".$approve_ksa."', 
                                  date_approve_KSA = '".$date_approve_ksa."',
                                  modified_at = '".$date_approve_ksa."',
                                  modified_by = '".$user_ksa."',
                                  user_KSA = '".$user_ksa."' $byStatus  
                              where NoSO = '".$No."'");
                    $status_update = "ok";
                    $message = "sukses";
            }elseif ($this->group == "RBM") {
                 if($tipe =='Approve'){
                    $statusLimit = "Ok";
                    $approve_rbm  = "Y";
                }else if($tipe =='Reject'){
                    $statusLimit = "Limit";
                    $approve_rbm  = "R";
                }
                $date_approve_rbm = date('Y-m-d H:i:s');
                $user_rbm = $this->session->userdata('username');
                $statusApprove = "Limit Approval RBM"; 
                $byStatus = ", Status = 'Usulan'";                
                if($total_limit <= $limit_RMB){
                    $update = $this->db->query("update trs_sales_order 
                              set StatusLimit = '".$statusLimit."', 
                                  statusPusat = 'Gagal',
                                  Approve_RBM = '".$approve_rbm."', 
                                  date_approve_RBM = '".$date_approve_rbm."',
                                  modified_at = '".$date_approve_rbm."',
                                  modified_by = '".$user_rbm."',
                                  user_RBM = '".$user_rbm."' $byStatus 
                              where NoSO = '".$No."'");
                    $status_update = "ok";
                }else{
                    $update = $this->db->query("update trs_sales_order 
                              set StatusLimit = 'Limit', 
                                  statusPusat = 'Gagal',
                                  Approve_RBM = '".$approve_rbm."', 
                                  date_approve_RBM = '".$date_approve_rbm."',
                                  modified_at = '".$date_approve_rbm."',
                                  modified_by = '".$user_rbm."',
                                  user_RBM = '".$user_rbm."' $byStatus 
                              where NoSO = '".$No."'");
                    $status_update = "gagal";
                    $message = "Limited";               
                }                 
            }
        }
        //============= Approval Limit TOP Jual ==========================
        else if ($status == "TOP") {
            if ($this->group == "BM") {
                if($tipe =='Approve'){
                    $statusTOP = "Ok";
                    $approve_top_bm  = "Y";
                }else if($tipe =='Reject'){
                    $statusTOP = "TOP";
                    $approve_top_bm  = "R";
                }
    
                $date_approve_top_bm = date('Y-m-d H:i:s');
                $user_top_bm = $this->session->userdata('username');
                $statusApprove = "TOP Approval KSA";  
                log_message('error',print_r($cek->TOP,true));  
                log_message('error',print_r($limit_TOP_BM,true));
                log_message('error',print_r($statusTOP,true));      
                if((int)$cek->TOP <= (int)$limit_TOP_BM)  {
                    log_message('error','TOP 1');
                    if($tipe =='Approve'){
                        log_message('error','Approve');
                        $update = $this->db->query("update trs_sales_order 
                                    set StatusTOP = 'Ok', 
                                        statusPusat = 'Berhasil',
                                        Approve_TOP_BM = '".$approve_top_bm."', 
                                        date_approve_TOP_BM = '".$date_approve_top_bm."',
                                        modified_at = '".date('Y-m-d H:i:s')."',
                                        modified_by = '".$this->session->userdata('username')."',
                                        user_TOP_BM = '".$user_top_bm."',
                                        umur_piutang_bm ='".$cek->TOP."',
                                        Barcode = 'lokal'
                                    where NoSO = '".$No."'");
                    }else{
                        log_message('error','Reject');
                        $update = $this->db->query("update trs_sales_order 
                                        set StatusTOP = 'Top', 
                                            statusPusat = 'Berhasil',
                                            Approve_TOP_BM = '".$approve_top_bm."', 
                                            date_approve_TOP_BM = '".$date_approve_top_bm."',
                                            modified_at = '".date('Y-m-d H:i:s')."',
                                            modified_by = '".$this->session->userdata('username')."',
                                            user_TOP_BM = '".$user_top_bm."',
                                            umur_piutang_bm ='".$cek->TOP."',
                                            Barcode = 'lokal'
                                            where NoSO = '".$No."'");
                    }
                    $cekulang = $this->db->query("select * from trs_sales_order where noso ='".$No."' limit 1")->row();
                    log_message('error',print_r($cekulang->StatusTOP,true));
                    log_message('error',print_r($cekulang->Approve_TOP_BM,true));
                    if(($cekulang->StatusTOP == 'TOP' or $cekulang->StatusTOP == 'Top') and $cekulang->Approve_TOP_BM == 'Y'){
                        log_message('error','Update top Ulang');
                        $this->db->query("update trs_sales_order
                                set StatusTOP = 'Ok' 
                                where noso ='".$No."'");
                    }
                    $status_update = "ok";
                    
                }else{
                    log_message('error','TOP 2');
                    $update = $this->db->query("update trs_sales_order 
                                        set StatusTOP = 'TOP', 
                                            statusPusat = 'Berhasil',
                                            Approve_TOP_BM = '".$approve_top_bm."', 
                                            date_approve_TOP_BM = '".$date_approve_top_bm."',
                                            modified_at = '".date('Y-m-d H:i:s')."',
                                            modified_by = '".$this->session->userdata('username')."',
                                            user_TOP_BM = '".$user_top_bm."',
                                            umur_piutang_bm ='".$cek->TOP."'
                                            where NoSO = '".$No."'");
                    $status_update = "ok";
                    $message = "limit_top";
                    if($tipe =='Approve'){
                        $this->Model_order->prosesDataSO($No);
                    }  
                }     
                        

            }elseif ($this->group == "KSA") {
                if($tipe =='Approve'){
                    $statusTOP = "Ok";
                    $approve_top_ksa  = "Y";
                }else if($tipe =='Reject'){
                    $statusTOP = "TOP";
                    $approve_top_ksa  = "R";
                }
                $date_approve_top_ksa = date('Y-m-d H:i:s');
                $user_top_ksa = $this->session->userdata('username');
                $statusApprove = "TOP Approval KSA";       

                if($cek->TOP <= $limit_TOP_KSA)  {
                    $update = $this->db->query("update trs_sales_order 
                                        set StatusTOP = '".$statusTOP."', 
                                            statusPusat = 'Berhasil',
                                            Approve_TOP_KSA = '".$approve_top_ksa."', 
                                            date_approve_TOP_KSA = '".$date_approve_top_ksa."',
                                            modified_at = '".date('Y-m-d H:i:s')."',
                                            modified_by = '".$this->session->userdata('username')."',
                                            user_TOP_KSA = '".$user_top_ksa."',
                                            umur_piutang_ksa ='".$cek->TOP."'
                                            where NoSO = '".$No."'");
                    $status_update = "ok";
                }else{
                    $update = $this->db->query("update trs_sales_order 
                                        set StatusTOP = 'TOP', 
                                            statusPusat = 'Berhasil',
                                            Approve_TOP_KSA = '".$approve_top_ksa."', 
                                            date_approve_TOP_KSA = '".$date_approve_top_ksa."',
                                            modified_at = '".date('Y-m-d H:i:s')."',
                                            modified_by = '".$this->session->userdata('username')."',
                                            user_TOP_KSA = '".$user_top_ksa."',
                                            umur_piutang_ksa ='".$cek->TOP."'
                                             where NoSO = '".$No."'");
                    $status_update = "gagal";
                    $message = "limit_top";
                }   
            }            
        }
         //============= Approval Limit Discount Cabang ==========================
        else if ($status == "DC") {
            if ($this->group == "BM") {
                if($tipe =='Approve'){
                    $statusDC = "DC";
                    $approve_discCab_bm  = "Y";
                }else if($tipe =='Reject'){
                    $statusDC = "DC";
                    $approve_discCab_bm  = "R";
                }
                $date_approve_discCab_bm = date('Y-m-d H:i:s');
                $user_discCab_bm = $this->session->userdata('username');
                $statusApprove = "DC Approval BM";
                $update = $this->db->query("update trs_sales_order 
                          set StatusDiscCab = '".$statusDC."', 
                              statusPusat = 'Berhasil',
                              Approve_DiscCab_BM = '".$approve_discCab_bm."', 
                              date_approve_DiscCab_BM = '".$date_approve_discCab_bm."',
                              modified_at = '".date('Y-m-d H:i:s')."',
                              modified_by = '".$this->session->userdata('username')."',
                              user_DiscCab_BM = '".$user_discCab_bm."' 
                          where NoSO = '".$No."'"); 
                $status_update = "ok";              
                if($tipe =='Approve'){
                  $this->Model_order->prosesDataSO($No);
                }
            }
            else if ($this->group == "RBM") {
                 if($tipe =='Approve'){
                    $statusDC = "Ok";
                    $approve_discCab_rbm  = "Y";
                }else if($tipe =='Reject'){
                    $statusDC = "DC";
                    $approve_discCab_rbm  = "R";
                }
                $date_approve_discCab_rbm = date('Y-m-d H:i:s');
                $user_discCab_rbm = $this->session->userdata('username');
                $statusApprove = "DC Approval BM";
                $update = $this->db->query("update trs_sales_order 
                          set StatusDiscCab = '".$statusDC."', 
                              statusPusat = 'Berhasil',
                              Approve_DiscCab_RBM = '".$approve_discCab_rbm."', 
                              date_approve_DiscCab_RBM = '".$date_approve_discCab_rbm."',
                              modified_at = '".date('Y-m-d H:i:s')."',
                              modified_by = '".$this->session->userdata('username')."',
                              user_DiscCab_RBM = '".$user_discCab_rbm."'
                          where NoSO = '".$No."'"); 
                
            }
            else
            {
                $message = "no_Approve";
            }      
        }
         //============= Approval Discount Prinsipal ==========================
        elseif ($status == "DP") {
            if ($this->group == "BM") {
                if($tipe =='Approve'){
                    $statusDP = "DP";
                    $approve_DiscPrins_bm  = "Y";
                }else if($tipe =='Reject'){
                    $statusDP = "DP";
                    $approve_DiscPrins_bm  = "R";
                }
                $date_approve_DiscPrins_bm = date('Y-m-d H:i:s');
                $user_DiscPrins_bm = $this->session->userdata('username');
                $statusApprove = "DC Approval BM";
                $update = $this->db->query("update trs_sales_order 
                          set StatusDiscPrins = '".$statusDP."', 
                              statusPusat = 'Berhasil',
                              Approve_DiscPrins_BM = '".$approve_DiscPrins_bm."', 
                              date_approve_DiscPrins_BM = '".$date_approve_DiscPrins_bm."',
                              modified_at = '".date('Y-m-d H:i:s')."',
                              modified_by = '".$this->session->userdata('username')."',
                              user_DiscPrins_BM = '".$user_DiscPrins_bm."' 
                          where NoSO = '".$No."'"); 
                $status_update = "ok";              
                if($tipe =='Approve'){
                  $this->Model_order->prosesDataSO($No);
                }
            }else if ($this->group == "Pusat") {
                 if($tipe =='Approve'){
                    $statusDP = "Ok";
                    $approve_DiscPrins_pusat  = "Y";
                }else if($tipe =='Reject'){
                    $statusDP = "DP";
                    $approve_DiscPrins_pusat  = "R";
                }
                $date_approve_DiscPrins_pusat = date('Y-m-d H:i:s');
                $user_DiscPrins_pusat = $this->session->userdata('username');
                $statusApprove = "DC Approval Pusat";
                $update = $this->db->query("update trs_sales_order 
                          set StatusDiscPrins = '".$statusDP."', 
                              statusPusat = 'Berhasil',
                              Approve_DiscPrins_pusat = '".$approve_DiscPrins_pusat."', 
                              date_approve_DiscPrins_pusat = '".$date_approve_DiscPrins_pusat."',
                              modified_at = '".date('Y-m-d H:i:s')."',
                              modified_by = '".$this->session->userdata('username')."',
                              user_DiscPrins_pusat = '".$user_DiscPrins_pusat."' 
                          where NoSO = '".$No."'"); 
                 
            }else{
                $message = "no_Approve";
            }      
            
        }

        $this->db->set("Cabang", $this->cabang);
        $this->db->set("Dokumen", "Sales Order");
        $this->db->set("NoDokumen", $No);
        $this->db->set("Status", $statusApprove);
        $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        $valid =  $this->db->insert("trs_approval");
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    


     public function updateDataApproveDO()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $query = $this->db2->query("select NoSO,Approve_RBM,date_approve_RBM,user_RBM,Approve_pusat,date_approve_pusat,user_pusat from trs_sales_order where Cabang = '".$this->cabang."' and (IFNULL(Approve_RBM,'') = 'Y' or IFNULL(Approve_pusat,'') = 'Y')");
                $num_query = $query->num_rows();
                if($num_query > 0){
                    $result = $query->result();
                    foreach ($result as $result) {
                        $this->db->set("StatusLimit",'Ok');
                        $this->db->set("Approve_RBM", $result->Approve_RBM);
                        $this->db->set("date_approve_RBM", $result->date_approve_RBM);
                        $this->db->set("user_RBM", $result->user_RBM);
                        $this->db->set("Approve_pusat", $result->Approve_pusat);
                        $this->db->set("date_approve_pusat", $result->date_approve_pusat);
                        $this->db->set("user_pusat", $result->user_pusat);
                        $this->db->where('NoSO', $result->NoSO);
                        $this->db->where('Cabang', $this->cabang);
                        $this->db->update('trs_sales_order'); // insert each row to another table
                        return 'BERHASIL';
                    }
                }
        }
        else{
            return 'GAGAL';
        }
    }
    public function listDataUsulanCN($status='')
    {
        $byStatus = '';
        
         if ($this->userGroup == 'BM') {
             if (!empty($status)) {
                $byStatus = "and StatusCNDN = '".$status."'";
                 $query = $this->db->query("select distinct  `Cabang`,
                                                `TipeDokumen`,
                                                `NoDokumen`,
                                                `Pelanggan`,
                                                `NamaPelanggan`,
                                                `TanggalCNDN`,
                                                ifnull(`StatusCNDN`,'') as 'StatusCNDN',
                                                date(created_at) as 'tglbuat',
                                                ifnull(Approve_BM,'') as 'Approve_BM' 
                                        from trs_faktur_cndn 
                                        where Cabang = '".$this->cabang."' $byStatus and 
                                          (ifnull(Approve_BM,'') = '' )")->result();
            }else{
               $query = $this->db->query("select distinct  `Cabang`,
                                                `TipeDokumen`,
                                                `NoDokumen`,
                                                `Pelanggan`,
                                                `NamaPelanggan`,
                                                `TanggalCNDN`,
                                                ifnull(`StatusCNDN`,'') as 'StatusCNDN',
                                                date(created_at) as 'tglbuat',
                                                ifnull(Approve_BM,'') as 'Approve_BM' 
                                        from trs_faktur_cndn 
                                        where Cabang = '".$this->cabang."'")->result();
            }
            
         }else{
            $query = $this->db->query("select distinct `Cabang`,
                                                `TipeDokumen`,
                                                `NoDokumen`,
                                                `Pelanggan`,
                                                `NamaPelanggan`,
                                                `TanggalCNDN`,
                                                ifnull(`StatusCNDN`,'') as 'StatusCNDN',
                                                date(created_at) as 'tglbuat',
                                                ifnull(Approve_BM,'') as 'Approve_BM' 
                                        from trs_faktur_cndn 
                                        where Cabang = '".$this->cabang."' order by TanggalCNDN DESC")->result();
         }
        
        return $query;
    }

     public function detailDataUsulanCN($no='')
    {
        $query = $this->db->query("select * from trs_faktur_cndn where Cabang = '".$this->cabang."' and NoDokumen = '". $no ."'")->result();      
        return $query;
    }

    public function prosesDataRejectUsulanCN($No = NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE); 
        $list = $this->db->query("
                        SELECT trs_faktur_cndn.`NoDokumen`,
                               trs_faktur_cndn.`Faktur`,
                               (trs_faktur_cndn.`Jumlah` * -1) AS 'valdisc_cndn',
                               trs_faktur_cndn.`ValueDscCab`,
                               trs_faktur_cndn.`KodeProduk`,
                               t_faktur.QtyFaktur,
                               t_faktur.Harga,
                               t_faktur.ValueDiscCab AS 'discfaktur',
                               t_harga.Dsc_Cab,
                               (t_harga.Dsc_Cab * t_faktur.QtyFaktur * t_faktur.Harga ) AS 'value_maksimal'
                         FROM trs_faktur_cndn
                         INNER JOIN ( SELECT trs_faktur_detail.`NoFaktur`,
                                     trs_faktur_detail.`KodeProduk`,
                                     trs_faktur_detail.`QtyFaktur`,
                                     trs_faktur_detail.`Harga`,
                                     trs_faktur_detail.`ValueDiscCab` 
                                  FROM   trs_faktur_detail ) AS t_faktur ON
                                  t_faktur.NoFaktur = trs_faktur_cndn.`Faktur` AND 
                                  t_faktur.KodeProduk = trs_faktur_cndn.KodeProduk
                        INNER JOIN ( SELECT mst_harga.`Produk`, mst_harga.`Dsc_Cab` FROM mst_harga ) AS t_harga ON
                                trs_faktur_cndn.KodeProduk = t_harga.Produk
                         WHERE trs_faktur_cndn.`NoDokumen` = '". $No ."';
            ")->result();
        if ($this->group == "BM") {
            $this->db->set("TglFaktur", date("Y-m-d"));
                    $this->db->set("TimeFaktur", date("Y-m-d H:i:s"));
                    $this->db->set("Status", 'Reject');
                    $this->db->set("Saldo", 0);
                    $this->db->set("modified_by", $this->user);
                    $this->db->set("modified_at", date("Y-m-d H:i:s"));
                    $this->db->where("NoFaktur", $No);
                    $valid =  $this->db->update("trs_faktur");

                    $this->db->set("TanggalCNDN", date("Y-m-d"));
                    $this->db->set("Approve_BM", 'Reject');
                    $this->db->set("date_BM", date("Y-m-d"));
                    $this->db->set("user_BM", $this->session->userdata('username'));
                    $this->db->set("Status", 'CNReject');
                    $this->db->set("StatusCNDN", 'Reject');
                    $this->db->set("updated_by", $this->user);
                    $this->db->set("updated_at", date("Y-m-d H:i:s"));
                    $this->db->where("NoDokumen", $No);
                    // $this->db->where("KodeProduk", $cndn->KodeProduk);
                    $valid =  $this->db->update("trs_faktur_cndn");
                    if ($this->db2->conn_id == TRUE) {
                        $this->db2->set("TglFaktur", date("Y-m-d"));
                        $this->db2->set("TimeFaktur", date("Y-m-d H:i:s"));
                        $this->db2->set("Status", 'Reject');
                        $this->db2->set("modified_by", $this->user);
                        $this->db2->set("modified_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoFaktur", $No);
                        $valid =  $this->db2->update("trs_faktur");

                        $this->db2->set("TanggalCNDN", date("Y-m-d"));
                        $this->db2->set("Approve_BM", 'Reject');
                        $this->db2->set("date_BM", date("Y-m-d"));
                        $this->db2->set("user_BM", $this->session->userdata('username'));
                        $this->db2->set("Status", 'CNReject');
                        $this->db2->set("StatusCNDN", 'Reject');
                        $this->db2->set("updated_by", $this->user);
                        $this->db2->set("updated_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoDokumen", $No);
                        // $this->db2->where("KodeProduk", $cndn->KodeProduk);
                        $valid =  $this->db2->update("trs_faktur_cndn");
                    }

        }

        return $valid;
    }

     public function prosesDataUsulanCN($No = NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE); 
        $list = $this->db->query("
                        SELECT trs_faktur_cndn.`NoDokumen`,
                               trs_faktur_cndn.`Faktur`,
                               (trs_faktur_cndn.`Jumlah` * -1) AS 'valdisc_cndn',
                               trs_faktur_cndn.`ValueDscCab`,
                               trs_faktur_cndn.`KodeProduk`,
                               t_faktur.QtyFaktur,
                               t_faktur.Harga,
                               t_faktur.ValueDiscCab AS 'discfaktur',
                               t_harga.Dsc_Cab,
                               ((t_faktur.DiscCabMax * t_faktur.QtyFaktur * t_faktur.Harga) / 100 ) AS 'value_maksimal'
                         FROM trs_faktur_cndn
                         INNER JOIN ( SELECT trs_faktur_detail.`NoFaktur`,
                                     trs_faktur_detail.`KodeProduk`,
                                     trs_faktur_detail.`QtyFaktur`,
                                     trs_faktur_detail.`Harga`,
                                      trs_faktur_detail.`ValueDiscCab`,
                                     trs_faktur_detail.`DiscCabMax`
                                  FROM   trs_faktur_detail ) AS t_faktur ON
                                  t_faktur.NoFaktur = trs_faktur_cndn.`Faktur` AND 
                                  t_faktur.KodeProduk = trs_faktur_cndn.KodeProduk
                        INNER JOIN ( SELECT mst_harga.`Produk`, mst_harga.`Dsc_Cab` FROM mst_harga ) AS t_harga ON
                                trs_faktur_cndn.KodeProduk = t_harga.Produk
                         WHERE trs_faktur_cndn.`NoDokumen` = '". $No ."';
            ")->result();
        if ($this->group == "BM") {
            foreach ($list as $cndn){
                if(($cndn->valdisc_cndn + $cndn->discfaktur) > ($cndn->value_maksimal)){
                    // $this->db->set("TanggalCNDN", date("Y-m-d H:i:s"));
                    $this->db->set("Approve_BM", 'Approve');
                    $this->db->set("date_BM", date("Y-m-d"));
                    $this->db->set("user_BM", $this->session->userdata('username'));
                    // $this->db->set("Status", 'CNOK');
                    // $this->db->set("StatusCNDN", 'Disetujui');
                    $this->db->set("updated_by", $this->user);
                    $this->db->set("updated_at", date("Y-m-d H:i:s"));
                    $this->db->where("NoDokumen", $No);
                    // $this->db->where("KodeProduk", $cndn->KodeProduk);
                    $valid =  $this->db->update("trs_faktur_cndn");
                    if ($this->db2->conn_id == TRUE) {
                        // $this->db2->set("TanggalCNDN", date("Y-m-d H:i:s"));
                        $this->db2->set("Approve_BM", 'Approve');
                        $this->db2->set("date_BM", date("Y-m-d"));
                        $this->db2->set("user_BM", $this->session->userdata('username'));
                        // $this->db2->set("Status", 'CNOK');
                        // $this->db2->set("StatusCNDN", 'Disetujui');
                        $this->db2->set("updated_by", $this->user);
                        $this->db2->set("updated_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoDokumen", $No);
                        // $this->db2->where("KodeProduk", $cndn->KodeProduk);
                        $valid =  $this->db2->update("trs_faktur_cndn");
                    }
                    $valid = "dsc_limit";
                    break;
                }else{
                    $this->db->set("TglFaktur", date("Y-m-d"));
                    $this->db->set("TimeFaktur", date("Y-m-d H:i:s"));
                    $this->db->set("Status", 'Open');
                    $this->db->set("modified_by", $this->user);
                    $this->db->set("modified_at", date("Y-m-d H:i:s"));
                    $this->db->where("NoFaktur", $No);
                    $valid =  $this->db->update("trs_faktur");

                    $this->db->set("TanggalCNDN", date("Y-m-d"));
                    $this->db->set("Approve_BM", 'Approve');
                    $this->db->set("date_BM", date("Y-m-d"));
                    $this->db->set("user_BM", $this->session->userdata('username'));
                    $this->db->set("Status", 'CNOK');
                    $this->db->set("StatusCNDN", 'Disetujui');
                    $this->db->set("updated_by", $this->user);
                    $this->db->set("updated_at", date("Y-m-d H:i:s"));
                    $this->db->where("NoDokumen", $No);
                    // $this->db->where("KodeProduk", $cndn->KodeProduk);
                    $valid =  $this->db->update("trs_faktur_cndn");
                    if ($this->db2->conn_id == TRUE) {
                        $this->db2->set("TglFaktur", date("Y-m-d"));
                        $this->db2->set("TimeFaktur", date("Y-m-d H:i:s"));
                        $this->db2->set("Status", 'Open');
                        $this->db2->set("modified_by", $this->user);
                        $this->db2->set("modified_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoFaktur", $No);
                        $valid =  $this->db2->update("trs_faktur");

                        $this->db2->set("TanggalCNDN", date("Y-m-d"));
                        $this->db2->set("Approve_BM", 'Approve');
                        $this->db2->set("date_BM", date("Y-m-d"));
                        $this->db2->set("user_BM", $this->session->userdata('username'));
                        $this->db2->set("Status", 'CNOK');
                        $this->db2->set("StatusCNDN", 'Disetujui');
                        $this->db2->set("updated_by", $this->user);
                        $this->db2->set("updated_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoDokumen", $No);
                        // $this->db2->where("KodeProduk", $cndn->KodeProduk);
                        $valid =  $this->db2->update("trs_faktur_cndn");
                    }
                }
            }

        }else if($this->group == "RBM"){
            $this->db->set("Approve_RBM", 'Approve');
            $this->db->set("date_RBM", date("Y-m-d"));
            $this->db->set("TanggalCNDN", date("Y-m-d"));
            $this->db->set("user_RBM", $this->session->userdata('username'));
            $this->db->set("Status", 'CNOK');
            $this->db->set("StatusCNDN", 'Disetujui');
            $this->db->set("updated_by", $this->user);
            $this->db->set("updated_at", date("Y-m-d H:i:s"));
            $this->db->where("NoDokumen", $No);
            $valid =  $this->db->update("trs_faktur_cndn");
            if ($this->db2->conn_id == TRUE) {
                        $this->db2->set("Approve_RBM", 'Approve');
                        $this->db2->set("date_RBM", date("Y-m-d"));
                        $this->db2->set("TanggalCNDN", date("Y-m-d"));
                        $this->db2->set("user_RBM", $this->session->userdata('username'));
                        $this->db2->set("Status", 'CNOK');
                        $this->db2->set("StatusCNDN", 'Disetujui');
                        $this->db2->set("updated_by", $this->user);
                        $this->db2->set("updated_at", date("Y-m-d H:i:s"));
                        $this->db2->where("NoDokumen", $No);
                        $valid =  $this->db2->update("trs_faktur_cndn");
            }
        }
       
        return $valid;
    }

    public function listDatalimitbeli($no = null)
    {   
       
        $query = $this->db->query("select * 
                                from mst_prinsipal_limit 
                                where Cabang = '".$this->session->userdata('cabang')."' and 
                                      (ifnull(Approve_BM,'')= ''  or ifnull(Approve_RBM,'')= ''  or
                                       ifnull(Approve_NSM,'')= ''  or ifnull(Approve_pusat,'')= '')
                                order by Waktu_Usulan Desc, Prinsipal Asc");
        return $query;
    }

    public function listDatakirimrelokasi($no = null)
    {   
       
        $query = $this->db->query("select * 
                                from trs_relokasi_kirim_header 
                                where Cabang = '".$this->session->userdata('cabang')."' and 
                                      (ifnull(Status_kiriman,'') = 'Open')
                                order by Tgl_kirim DESC, No_Relokasi ASC");
        return $query;
    }


    public function viewapproval($no = NULL)
    {
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and NoSO = '".$no."'";
        }
        $query = $this->db->query("select Cabang,NoSO,
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

    public function prosesDatalimitbeli($No = NULL, $status = NULL, $tgl = NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE);
        if($status == 'Approve'){
            $approve  = "Approve";
            $message = "Approve";
        }else{
            $approve  = "Reject";
            $message = "Reject";
        }
         if ($this->group == "BM") {
                // $approve_bm  = "Y";
                $date_approve_bm = date('Y-m-d H:i:s');
                $user_bm = $this->session->userdata('username');
                $update = $this->db->query("update mst_prinsipal_limit 
                          set status = 'Usulan',
                              Approve_BM = '".$approve."', 
                              date_BM = '".$date_approve_bm."',
                              user_BM = '".$user_bm."' 
                          where Prinsipal = '".$No."' and Waktu_Usulan ='".$tgl."' and ifnull(Approve_BM,'') =''"); 
                 if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update mst_prinsipal_limit 
                          set status = 'Usulan',
                              Approve_BM = '".$approve."', 
                              date_BM = '".$date_approve_bm."',
                              user_BM = '".$user_bm."' 
                          where Prinsipal = '".$No."' and Cabang = '".$this->cabang."' and Waktu_Usulan ='".$tgl."' and ifnull(Approve_BM,'') =''");
                    } 
                if($update){
                    $message = $message." BM Sukses";
                }else{
                    $message = $message." BM Gagal";
                }
                
         }else if ($this->group == "RBM") {
                // $approve_bm  = "Y";
                $date_approve_bm = date('Y-m-d H:i:s');
                $user_bm = $this->session->userdata('username');
                $update = $this->db->query("update mst_prinsipal_limit 
                          set status = 'Usulan',
                              Approve_RBM = '".$approve."', 
                              date_RBM = '".$date_approve_bm."',
                              user_RBM = '".$user_bm."' 
                          where Prinsipal = '".$No."' and Waktu_Usulan ='".$tgl."' and ifnull(Approve_BM,'') ='Approve'"); 
                 if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update mst_prinsipal_limit 
                          set status = 'Usulan',
                              Approve_RBM = '".$approve."', 
                              date_RBM = '".$date_approve_bm."',
                              user_RBM = '".$user_bm."' 
                          where Prinsipal = '".$No."' and Cabang = '".$this->cabang."' and and ifnull(Approve_BM,'') ='Approve' Waktu_Usulan ='".$tgl."'");
                    } 
                if($update){
                    $message = $message." RBM Sukses";
                }else{
                    $message = $message." RBM Gagal";
                }
         }
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function viewapprovallimitbeli($no = NULL,$tgl = NULL)
    {
        // $exp = explode('~',$no);
        // $no  = $exp[0];
        $byNo = "";
        if (!empty($no)) {
            $byNo = " and Prinsipal = '".$no."' and Waktu_Usulan = '".$tgl."'";
        }
        $query = $this->db->query("select Cabang,CONCAT_WS('-',KodePrinsipal,Prinsipal) as 'Prinsipal',
                                          IFNULL(Approve_BM,'N') AS 'Approve_BM',
                                          IFNULL(user_BM,'N') AS 'user_BM',
                                          IFNULL(Approve_RBM,'N') AS 'Approve_RBM',
                                          IFNULL(user_RBM,'N') AS 'user_RBM',
                                          IFNULL(Approve_NSM,'N') AS 'Approve_NSM',
                                          IFNULL(user_NSM,'N') AS 'user_NSM',
                                          IFNULL(Approve_pusat,'N') AS 'Approve_pusat',
                                          IFNULL(user_pusat,'N') AS 'user_pusat'
                                    from mst_prinsipal_limit where Cabang = '".$this->cabang."' $byNo ")->result();
        return $query;
    }

    public function prosesDatakirimrelokasi($No = NULL, $status = NULL, $jenis = NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE);
        if($status == 'Approve'){
            $approve  = "Approve";
            $message = "suksesapprove";
            $status_kirim = 'Kirim';
        }else{
            $approve  = "Reject";
            $message = "suksesreject";
            $status_kirim = 'Batal';
        }
         if ($this->group == "BM" || $this->group == "Cabang") {
                $cektglstok = $this->Model_main->cek_tglstoktrans();
                if($cektglstok == 1){
                    $tgl = date('Y-m-d');
                    $date_approve_bm = date('Y-m-d H:i:s');
                }else if($cektglstok == 0){
                    $date = date('Y-m-d',strtotime("-10 days"));
                    $tgl = date('Y-m-t', strtotime($date));
                    $date_approve_bm = date('Y-m-t H:i:s', strtotime($date));
                }
                
                $user_bm = $this->session->userdata('username');
                $update = $this->db->query("update trs_relokasi_kirim_header 
                          set Status_kiriman = '".$status_kirim."',
                              App_BM_Status = '".$approve."', 
                              tgl_app = '".$tgl."',
                              App_BM_Time = '".$date_approve_bm."',
                              user_BM = '".$user_bm."' 
                          where No_Relokasi = '".$No."'");
                $update = $this->db->query("update trs_relokasi_kirim_detail 
                          set Status = '".$status_kirim."',
                          tgl_app = '".$tgl."'
                          where No_Relokasi = '".$No."'"); 
                if($status == 'Approve'){
                    if ($this->db2->conn_id == TRUE) {

                    if (empty($jenis)) {
                      $kirim_header = $this->db->query("select * from trs_relokasi_kirim_header where No_Relokasi = '".$No."'")->result();
                      foreach ($kirim_header as $header) {
                         $this->db2->insert('trs_relokasi_kirim_header', $header);
                      }
                      $kirim_detail = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."'")->result();
                      foreach ($kirim_detail as $detail) {
                         $this->db2->insert('trs_relokasi_kirim_detail', $detail);
                      }
                    }else{
                        
                        $this->db2->set('Status_kiriman', 'Kirim');
                        $this->db2->set("Modified_Time", date('Y-m-d H:i:s'));
                        $this->db2->set("tgl_app", $tgl);
                        $this->db2->set("Modified_User", $this->user);
                        $this->db2->set('App_BM_Status', 'Kirim');
                        $this->db2->set("App_BM_Time", date('Y-m-d H:i:s'));
                        $this->db2->set("user_BM", $this->user);
                        $this->db2->where('No_Relokasi', $No);
                        $this->db2->where('Cabang_Pengirim', $this->session->userdata('cabang'));
                        $this->db2->update("trs_relokasi_kirim_header");

                        $this->db2->set('Status', 'Kirim');
                        $this->db2->set("Modified_Time", date('Y-m-d H:i:s'));
                        $this->db2->set("tgl_app", $tgl);
                        $this->db2->set("Modified_User", $this->user);
                        $this->db2->where('No_Relokasi', $No);
                        $this->db2->where('Cabang_Pengirim', $this->session->userdata('cabang'));
                        $this->db2->update("trs_relokasi_kirim_detail");
                    }
                  } 
                  $this->setStokOut($No,'Relokasi');
                }else{
                   $this->setStokOut($No,'Relokasi');
                   $this->setStokreject($No,'Baik');

                   if ($jenis == 'Pusat') {
                      $this->db2->set('Status_kiriman', 'Reject');
                      $this->db2->set("Modified_Time", date('Y-m-d H:i:s'));
                      $this->db2->set("tgl_app", $tgl);
                      $this->db2->set("Modified_User", $this->user);
                      $this->db2->set('App_BM_Status', 'Reject');
                      $this->db2->set("App_BM_Time", date('Y-m-d H:i:s'));
                      $this->db2->set("user_BM", $this->user);
                      $this->db2->where('No_Relokasi', $No);
                      $this->db2->where('Cabang_Pengirim', $this->session->userdata('cabang'));
                      $this->db2->update("trs_relokasi_kirim_header");

                      $this->db2->set('Status', 'Reject');
                        $this->db2->set("Modified_Time", date('Y-m-d H:i:s'));
                        $this->db2->set("tgl_app", $tgl);
                        $this->db2->set("Modified_User", $this->user);
                        $this->db2->where('No_Relokasi', $No);
                        $this->db2->where('Cabang_Pengirim', $this->session->userdata('cabang'));
                        $this->db2->update("trs_relokasi_kirim_detail");
                   }
                   
                }
                $message = $message."BM";
         }else{
                $message = 'no_Approve';
         }
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function setStokreject($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                        $valuestok = $produk[$kunci]->Gross;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $inv = $invsum->row();  
                    $UnitStok = $inv->UnitStok + $summary;
                    $valuestok = $UnitStok * $inv->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");    
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
           foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk;  
                if (!empty($kodeproduk)) {
                    $prod_det = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();  
                     $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen ='".$No."' limit 1"); 
                     if($det->num_rows() > 0){
                        $detrelo = $det->row();
                        $unitcogs = $detrelo->UnitCOGS;    
                        $qtystok = $detrelo->UnitStok + ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs;
                        $this->db->set("UnitStok", $qtystok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("NoDokumen", $No);
                        $this->db->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db->where("Gudang", $gudang);
                        $valid = $this->db->update('trs_invdet');
                    }else{
                        $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Relokasi' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen ='".$No."' limit 1");
                        $detrelo = $det->row();
                        $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;
                        $valuestok = $UnitStok * $detrelo->UnitCOGS;  
                        $unitcogs = $detrelo->UnitCOGS;  
                        $this->db->set("Tahun", date('Y'));
                        $this->db->set("Cabang", $this->cabang);
                        $this->db->set("KodePrinsipal", $prod_det->Kode);
                        $this->db->set("NamaPrinsipal",$prod_det->Prinsipal);
                        $this->db->set("Pabrik", $prod_det->Pabrik);
                        $this->db->set("KodeProduk", $kodeproduk);
                        $this->db->set("NamaProduk", $produk[$key]->NamaProduk);
                        $this->db->set("UnitStok", $UnitStok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("UnitCOGS", $unitcogs);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->set("NoDokumen", $No);
                        $this->db->set("TanggalDokumen", date('Y-m-d H:i:s'));
                        $this->db->set("BatchNo", $produk[$key]->BatchNo);
                        $this->db->set("ExpDate", $produk[$key]->ExpDate);
                        $this->db->set("Gudang", $gudang);
                        $valid = $this->db->insert('trs_invdet');
                    }
                        
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db->set("ValueStok", 0);
                            $this->db->where("KodeProduk", $kodeproduk);
                            $this->db->where("Gudang", $gudang);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }else{
                            $invdet = $invdet->row();
                            $this->db->set("ValueStok", $invdet->sumval);
                            $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db->where("KodeProduk", $kodeproduk);
                            $this->db->where("Gudang", $gudang);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }
                    // }
                }
            }

    }



//================Ngurangin  stok relokasi cabang============================
    public function setStokOut($No = NULL,$gudang = NULL)
    {   
        $produk = $this->db->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
        foreach ($produk as $kunci => $nilai) {
            $summary = 0;
            $product1 = $produk[$kunci]->Produk;
            $harga = $produk[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$product1."'")->row();
                // foreach ($produk as $key => $value) {
                //     $product2 = $produk[$key]->Produk;
                //     if ($product1 == $product2) {
                  $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                  $valuestok = $produk[$kunci]->Gross;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                if ($invsum->num_rows() > 0) {
                    $inv = $invsum->row();  
                    $UnitStok = $inv->UnitStok - $summary;
                    $valuestok = $UnitStok * $inv->UnitCOGS;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                }
                else{
                     $this->db->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");    
                }
            }
        }

        // save inventori history
        foreach ($produk as $key => $value) {   
            $kodeproduk = $produk[$key]->Produk;      
            if (!empty($kodeproduk)) {
                $prod = $this->db->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join                        mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal 
                                          where Kode_Produk = '".$kodeproduk."'")->row();
                $valuestok = $produk[$key]->Gross;
                $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
            }
        }

        // save inventori detail
            foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk;  
                if (!empty($kodeproduk)) {
                     $det = $this->db->query("select * from trs_invdet where cabang = '".$this->cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen ='".$No."' limit 1")->row(); 
                        $unitcogs = $det->UnitCOGS;    
                        $qtystok = $det->UnitStok - ($produk[$key]->Qty + $produk[$key]->Bonus);
                        $valuestok = $qtystok * $unitcogs;
                        $this->db->set("UnitStok", $qtystok);
                        $this->db->set("ValueStok", $valuestok);
                        $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang", $this->cabang);
                        $this->db->where("KodeProduk", $kodeproduk);
                        $this->db->where("NoDokumen", $No);
                        $this->db->where("BatchNo", $produk[$key]->BatchNo);
                        $this->db->where("Gudang", $gudang);
                        $valid = $this->db->update('trs_invdet');
                        $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                        if($invdet->num_rows() <= 0){
                            $this->db->set("ValueStok", 0);
                            $this->db->where("KodeProduk", $kodeproduk);
                            $this->db->where("Gudang", $gudang);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }else{
                            $invdet = $invdet->row();
                            $this->db->set("ValueStok", $invdet->sumval);
                            $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                            $this->db->where("KodeProduk", $kodeproduk);
                            $this->db->where("Gudang", $gudang);
                            $this->db->where("Tahun", date('Y'));
                            $this->db->where("Cabang",$this->cabang);
                            $valid = $this->db->update('trs_invsum');
                        }
                    // }
                }
            } 

    }

    //==== Nambahin  stok ke gudang relokasi pusat =======////
     public function setStokIn($No = NULL,$gudang = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $produk = $this->db2->query("select * from trs_relokasi_kirim_detail where No_Relokasi = '".$No."' ")->result();
            foreach ($produk as $kunci => $nilai) {
                $summary = 0;
                $product1 = $produk[$kunci]->Produk;
                $harga = $produk[$kunci]->Harga;
                if (!empty($product1)) {
                     $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join  mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$product1."'")->row();
                    // foreach ($produk as $key => $value) {
                    //     $product2 = $produk[$key]->Produk;
                    //     if ($product1 == $product2) {
                    $summary = $summary + $produk[$kunci]->Qty + $produk[$kunci]->Bonus;
                    $valuestok = $produk[$kunci]->Gross;
                    //     }
                    // }
                    $invsum = $this->db2->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");

                    if ($invsum->num_rows() > 0) {
                        $invsum = $invsum->row();  
                        $UnitStok = $invsum->UnitStok + $summary;
                        $valuestok = $UnitStok * $invsum->UnitCOGS;
                        $this->db2->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok." where KodeProduk='".$product1."' and Cabang='Pusat' and Gudang='".$gudang."' and Tahun = '".date('Y')."' limit 1");
                    }
                    else{
                         $this->db2->query("insert into trs_invsum (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', 'Pusat', '".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', '".$gudang."', '0.000', '".$harga."', '0.000')");     
                    }
                }
            }

            // save inventori history
            foreach ($produk as $key => $value) {   
                $kodeproduk = $produk[$key]->Produk;      
                if (!empty($kodeproduk)) {
                   $prod = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$kodeproduk."'")->row();
                    $valuestok = $produk[$key]->Gross;
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;

                    $this->db2->query("insert into trs_invhis (Tahun, Cabang, KodePrinsipal,NamaPrinsipal, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', 'Pusat','".$prod->Kode."','".$prod->Prinsipal."','".$prod->Pabrik."', '".$kodeproduk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$produk[$key]->BatchNo."', '".$produk[$key]->ExpDate."', '".$gudang."', 'Relokasi', '".$No."', '-')");
                }
            }

            // save inventori detail
             foreach ($produk as $key => $value) { 
                $kodeproduk = $produk[$key]->Produk;  
                $BatchDoc = $produk[$key]->BatchDoc;
                if (!empty($kodeproduk)) {
                    $prod_det = $this->db2->query("SELECT Pabrik, Produk,mst_prinsipal.Kode,mst_produk.Prinsipal FROM mst_produk left join  mst_prinsipal ON mst_prinsipal.Prinsipal = mst_produk.Prinsipal where Kode_Produk = '".$kodeproduk."'")->row();
                    $det = $this->db->query("select * from trs_invdet where cabang = '".$produk[$key]->Cabang."' and KodeProduk = '".$kodeproduk."' and  Gudang = 'Baik' and BatchNo = '".$produk[$key]->BatchNo."' and Tahun = '".date('Y')."' and NoDokumen = '".$BatchDoc."' limit 1")->row(); 
                    $UnitStok = $produk[$key]->Qty + $produk[$key]->Bonus;
                    $valuestok = ($produk[$key]->Qty + $produk[$key]->Bonus) * $det->UnitCOGS;  
                    $unitcogs = $det->UnitCOGS;      
                        
                        $this->db2->set("Tahun", date('Y'));
                        $this->db2->set("Cabang", 'Pusat');
                        $this->db2->set("KodePrinsipal", $prod_det->Kode);
                        $this->db2->set("NamaPrinsipal",$prod_det->Prinsipal);
                        $this->db2->set("Pabrik", $prod_det->Pabrik);
                        $this->db2->set("KodeProduk", $kodeproduk);
                        $this->db2->set("NamaProduk", $produk[$key]->NamaProduk);
                        $this->db2->set("UnitStok", $UnitStok);
                        $this->db2->set("UnitCOGS", $unitcogs);
                        $this->db2->set("ValueStok", $valuestok);
                        $this->db2->set("ModifiedTime", date('Y-m-d H:i:s'));            
                        $this->db2->set("ModifiedUser", $this->session->userdata('username'));
                        $this->db2->set("NoDokumen", $No);
                        $this->db2->set("TanggalDokumen", date('Y-m-d H:i:s'));
                        $this->db2->set("BatchNo", $produk[$key]->BatchNo);
                        $this->db2->set("ExpDate", $produk[$key]->ExpDate);
                        $this->db2->set("Gudang", $gudang);
                        $valid = $this->db2->insert('trs_invdet');

                    $invdet = $this->db2->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$kodeproduk."' and  Gudang = '".$gudang."' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang ='Pusat'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db2->set("ValueStok", 0);
                        $this->db2->where("KodeProduk", $kodeproduk);
                        $this->db2->where("Gudang", $gudang);
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("Cabang",'Pusat');
                        $valid = $this->db2->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db2->set("ValueStok", $invdet->sumval);
                        $this->db2->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db2->where("KodeProduk", $kodeproduk);
                        $this->db2->where("Gudang", $gudang);
                        $this->db2->where("Tahun", date('Y'));
                        $this->db2->where("Cabang",'Pusat');
                        $valid = $this->db2->update('trs_invsum');
                    }
                    
                }
            }

        }
    }

    public function listDataApproval_UP($search=null, $limit=null){
        $byStatus = "";
        switch ($this->group) {
            case 'CabangSPV':
                $byStatus = "and ifnull(Status_Usulan,'') <> 'Closed'";
                break;
            case 'KSA':
                $byStatus = "and ifnull(Status_Usulan,'') <> 'Closed'";
                break;
            case 'CabangApoteker':
                $byStatus = "and ifnull(Status_Usulan,'') <> 'Closed'";
                break;
            case 'BM':
                $byStatus = "and ifnull(Approve_SPV,'')='Y' and ifnull(Approve_KSA,'')='Y' and ifnull(Approve_APJ,'')='Y' and ifnull(Status_Usulan,'') <> 'Closed'";
                break;
            
            default:
                # code...
                break;
        }

        $query = $this->db->query("select * FROM mst_usulan_pelanggan where Cabang = '".$this->cabang."'  $byStatus $search $limit");
         // $query = $this->db->query("select * FROM mst_pelanggan where Cabang = '".$this->cabang."'");
        return $query;
    }

    function prosesApprovalUP($no,$fieldname1,$fieldname2,$status){
        $time = $now = date('H:i:s');

        $this->db->trans_begin();

        $this->db->set($fieldname1, $status);
        $this->db->set($fieldname2, $time);
        if($this->group == 'BM'){
          $this->db->set("Aktif", "Y");
          $this->db->set("Status_Usulan", "Closed");
        }
        $this->db->where('Kode', $no);
        $this->db->update('mst_usulan_pelanggan');
        
        if($this->group == 'BM' && $status == 'Y'){
          // $this->db->select('*');
          // $this->db->where('Kode',$no);
          // $data = $this->db->get('mst_usulan_pelanggan');
          $data = $this->db->query("select * from mst_usulan_pelanggan where kode = '".$no."'")->row();
          // log_message('error',print_r($data,true));
          $this->db->insert("mst_pelanggan", $data);
          $this->db->insert_id();
        }

        if ($this->db->trans_status() === FALSE)
        {
                $this->db->trans_rollback();
        }
        else
        {
                $this->db->trans_commit();
        }
            // ============ Ke Pusat -============
            // $this->db2 = $this->load->database('pusat', TRUE);
            // if ($this->db2->conn_id == TRUE) {
            //     $this->db2->set($fieldname1, 'Y');
            //     $this->db2->set($fieldname2, $time);
            //     $this->db2->where('Kode', $no);
            //     $this->db2->update('mst_pelanggan');
            // }
    }

    public function updateDataLimitBeliPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select * from mst_prinsipal_limit where Cabang = '".$this->cabang."'")->result();
                foreach ($nomor as $no) {
                    $this->db->set('Approve_BM', $no->Approve_BM);
                    $this->db->set('user_BM', $no->user_BM);
                    $this->db->set('date_BM', $no->date_BM);
                    $this->db->set('Approve_RBM', $no->Approve_RBM);
                    $this->db->set('user_RBM', $no->user_RBM);
                    $this->db->set('date_RBM', $no->date_RBM);
                    $this->db->set('Approve_NSM', $no->Approve_NSM);
                    $this->db->set('user_NSM', $no->user_NSM);
                    $this->db->set('date_NSM', $no->date_NSM);
                    $this->db->set('Approve_pusat', $no->Approve_pusat);
                    $this->db->set('user_pusat', $no->user_pusat);
                    $this->db->set('date_pusat', $no->date_pusat);
                    $this->db->where('Prinsipal', $no->Prinsipal);
                    $this->db->where('Cabang', $no->Cabang);
                    $this->db->where('Waktu_Usulan', $no->Waktu_Usulan);
                    $this->db->update('mst_prinsipal_limit'); // insert each row to another table

                }

                $data = $this->db->query("select * from mst_prinsipal_limit where Cabang = '".$this->cabang."'")->result();
                foreach ($data as $list) {
                    if($list->Approve_pusat =="Approve"){
                        $enddate = date("Y-m-t", strtotime($list->Waktu_Usulan));
                        $x = $this->db->query("select * from mst_limit_pembelian where Bulan ='".$enddate."'  and Cabang = '".$list->Cabang."' and Prinsipal = '".$list->Prinsipal."'")->num_rows();
                        if($x < 1){
                            $this->db->set("Bulan", $enddate);
                            $this->db->set("Cabang", $list->Cabang);         
                            $this->db->set("Prinsipal", $list->Prinsipal);
                            $this->db->set("Limit_Beli", $list->Limit_Usulan);  
                            $this->db->set("Created_by", $this->session->userdata('username'));
                            $this->db->set("Created_at", date("Y-m-d H:i:s"));
                            $valid = $this->db->insert('mst_limit_pembelian'); 
                        }else{
                            $this->db->set("Limit_Beli", $list->Limit_Usulan);  
                            $this->db->set("Updated_At", $this->session->userdata('username'));
                            $this->db->set("Created_at", date("Y-m-d H:i:s"));
                            $this->db->where("Bulan", $enddate);
                            $this->db->where("Cabang", $list->Cabang);         
                            $this->db->where("Prinsipal", $list->Prinsipal);
                            $valid = $this->db->update('mst_limit_pembelian'); 
                        }
                        

                        $this->db2 = $this->load->database('pusat', TRUE); 
                        $y = $this->db2->query("select * from mst_limit_pembelian where Bulan ='".$enddate."'  and Cabang = '".$list->Cabang."' and Prinsipal = '".$list->Prinsipal."'")->num_rows();
                         if($y < 1){
                            $this->db2->set("Bulan", $enddate);
                            $this->db2->set("Cabang", $list->Cabang);         
                            $this->db2->set("Prinsipal", $list->Prinsipal);
                            $this->db2->set("Limit_Beli", $list->Limit_Usulan);  
                            $this->db2->set("Created_by", $this->session->userdata('username'));
                            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
                            $valid = $this->db2->insert('mst_limit_pembelian'); 
                        }else{
                            $this->db2->set("Limit_Beli", $list->Limit_Usulan);  
                            $this->db2->set("Updated_At", $this->session->userdata('username'));
                            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
                            $this->db2->where("Bulan", $enddate);
                            $this->db2->where("Cabang", $list->Cabang);         
                            $this->db2->where("Prinsipal", $list->Prinsipal);
                            $valid = $this->db2->update('mst_limit_pembelian'); 
                        }

                    }
                }

                return 'BERHASIL';
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }
    }

    
    public function updateDataSOapprovalPusat($status=NULL){

        $this->db2 = $this->load->database('pusat', TRUE);   
    
        $so = $this->db->query("select * from trs_sales_order where status not in ('Closed','Reject') and ifnull(NoDO,'') = '' ")->result();
        $No = "";
        if ($this->db2->conn_id == TRUE) {
            foreach ($so as $list) {

               $No = $list->NoSO;
               $so_pusat = $this->db2->query("select * from trs_sales_order where NoSO = '".$list->NoSO."'");
               $num_so    = $so_pusat->num_rows();
               $data_so = $so_pusat->result();
               if($num_so == 0){
                    $query = $this->db->query("select * from trs_sales_order_detail where NoSO = '".$list->NoSO."'")->result();
                            foreach($query as $r) { // loop over results
                                $this->db2->insert('trs_sales_order_detail', $r); // insert each row to another table
                            }

                    $query2 = $this->db->query("select * from trs_sales_order where NoSO = '".$list->NoSO."'")->row();
                            $this->db2->insert('trs_sales_order', $query2); 
               }else{
                 $datax = $this->db->query("select * from trs_sales_order where NoSO = '".$data_so[0]->NoSO."' limit 1")->row();
                 if($datax->StatusLimit != 'Oke'){
                    $this->db->set('StatusLimit', $data_so[0]->StatusLimit);
                 }
                 if($datax->StatusTOP != 'Oke'){
                    $this->db->set('StatusTOP', $data_so[0]->StatusTOP);
                 }
                 if($datax->StatusDiscCab != 'Oke'){
                    $this->db->set('StatusDiscCab', $data_so[0]->StatusDiscCab);
                 }
                 if($datax->StatusDiscPrins != 'Oke'){
                     $this->db->set('StatusDiscPrins', $data_so[0]->StatusDiscPrins);
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
                    if($data_so[0]->Approve_TOP_RBM == 'Y' and (empty($list->date_approve_TOP_RBM_cab) or$list->date_approve_TOP_RBM_cab  == "")){
                        $this->db->set('date_approve_TOP_RBM_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_TOP_RBM', $data_so[0]->user_TOP_RBM);
                    $this->db->set('Approve_TOP_pusat', $data_so[0]->Approve_TOP_pusat);
                    if($data_so[0]->Approve_TOP_pusat == 'Y' and (empty($list->date_approve_TOP_pusat_cab) or $list->date_approve_TOP_pusat_cab == "")){
                        $this->db->set('date_approve_TOP_pusat_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_TOP_pusat', $data_so[0]->user_TOP_pusat);
                    $this->db->set('Approve_DiscCab_RBM', $data_so[0]->Approve_DiscCab_RBM);
                    if($data_so[0]->Approve_DiscCab_RBM == 'Y' and (empty($list->date_approve_DiscCab_RBM_cab) or  $list->date_approve_DiscCab_RBM_cab  == "")){
                        $this->db->set('date_approve_DiscCab_RBM_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_DiscCab_RBM', $data_so[0]->user_DiscCab_RBM);
                    $this->db->set('Approve_DiscCab_pusat', $data_so[0]->Approve_DiscCab_pusat);
                    $this->db->set('user_DiscCab_pusat', $data_so[0]->user_DiscCab_pusat);
                    $this->db->set('Approve_DiscPrins_RBM', $data_so[0]->Approve_DiscPrins_RBM);
                    $this->db->set('user_DiscPrins_RBM', $data_so[0]->user_DiscPrins_RBM);
                    $this->db->set('Approve_DiscPrins_pusat', $data_so[0]->Approve_DiscPrins_pusat);
                    if($data_so[0]->Approve_DiscPrins_pusat == 'Y' and (empty($list->date_approve_DiscPrins_pusat_cab) or $list->date_approve_DiscPrins_pusat_cab == "")){
                        $this->db->set('date_approve_DiscPrins_pusat_cab',date('Y-m-d H:i:s'));
                    }
                    $this->db->set('user_DiscPrins_pusat', $data_so[0]->user_DiscPrins_pusat);
                    $this->db->where('NoSO',$data_so[0]->NoSO);
                    $this->db->where('Cabang',$data_so[0]->Cabang);
                    $this->db->where('NoDO','');
                    $this->db->where('status','Usulan');
                    $this->db->update('trs_sales_order');
               }
            }
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }

    }

    public function updateDataSODOPusat()
    {   
        $do_cetak = [];
        $valid = false;
            $query2 = $this->db->query("select * from trs_sales_order where Status ='Usulan' and StatusTOP = 'Ok' and StatusLimit = 'Ok' and StatusDiscCab = 'Ok' and StatusDiscPrins = 'Ok' and ifnull(NoDO,'') = '' and ifnull(flag_nostok,'N') = 'N' and Keterangan2 <> 'Bidan' limit 30");
            // and NoSO not in (select ifnull(NoSO,'') as 'NoSO' from trs_delivery_order_sales where NoSO = '".$No."')
            $query3 = $this->db->query("select * from trs_sales_order where StatusTOP = 'Ok' and StatusLimit = 'Ok' and StatusDiscCab = 'Ok' and StatusDiscPrins = 'Ok' and ifnull(NoDO,'') <> ''  limit 30");
            $num3  = $query3->num_rows();
            // $data1 = $query3->result();
            //     foreach ($data1 as $datado) {
            //         $this->db->query("update trs_sales_order set Status = 'Closed' where NoSO = '".$datado->NoSO."'");
            //     }
            $num2  = $query2->num_rows();
            $data = $query2->result();
            if($num2 < 1){
                $valid = false;
            }else{
                foreach ($data as $datado) {
                    $cekdo = $this->db->query("select NoDO from trs_delivery_order_sales where NoSO ='".$datado->NoSO."'")->num_rows();
                    if($cekdo < 1){
                        $NoDO = $this->Model_order->saveDataDeliveryOrder($datado->NoSO);
                        if($NoDO == ""){
                            $valid = false;
                        }else{
                            // array_push($do_cetak,$NoDO);
                            $this->db->query("update trs_sales_order set Status = 'Closed' where NoSO = '".$datado->NoSO."'");
                            $this->Model_order->cekDataDO($NoDO);
                            $valid = true;
                        }
                    }else{
                        $valid = false;
                    }
                    
                }
            }
         $data = [
          "valid"     => $valid
        ];
        return $data;
    }

   public function prosesDataDoOutstanding($No = NULL)
    {
        $query = $this->db->query("select * from trs_sales_order where Status ='Usulan' and StatusTOP = 'Ok' and StatusLimit = 'Ok' and StatusDiscCab = 'Ok' and StatusDiscPrins = 'Ok' and ifnull(NoDO,'') = '' and NoSO = '".$No."' and NoSO not in (select ifnull(NoSO,'') as 'NoSO' from trs_delivery_order_sales where NoSO = '".$No."') ");
        $num = $query->num_rows();
        if($num > 0){
          
          if ($query->row()->Keterangan2 == 'Bidan') {
              $NoDO = $this->Model_orderKhusus->saveDataDeliveryOrder($No);
          }else{

            $NoDO = $this->Model_order->saveDataDeliveryOrder($No);
          }

          if($NoDO == ""){
              $update = false;
              $message = "No_DO";
          }else{
              $update = $this->db->query("update trs_sales_order set Status = 'Closed' where NoSO = '".$No."'");
              $this->Model_order->cekDataDO($NoDO);
              $message = "sukses";

              // // ambil data do nya untuk ngeprint
              // $this->db->where("NoDO",'1811061000359');
              // $do_header = $this->db->get('trs_delivery_order_sales');

              // $this->db->where("NoDO",'1811061000359');
              // $do_detail = $this->db->get('trs_delivery_order_sales_detail');

              // $data_cetak = [
              //   'header' => $do_header->result(),
              //   'detail' => $do_detail->result()
              // ];

              $data = ["update" =>$update,"message"=>$message];
              return $data;
          }
          $data = ["update" =>$update,"message"=>$message];
        }else{
          $data = ["update" =>false,"message"=>"gagal"];
        }
        
        return $data;
    }

    public function listApprovalUsulanBeli($search = NULL, $length = NULL, $start = NULL,$status=NULL)
    {
        $byStatus = "";
        if ($status != "") {
            $status = "and Status_Usulan = '$status'";
        }
        // if($status != 'all'){
            if ($status == "") {
                $byStatus = " and No_Usulan not in (select trs_pembelian_header.`No_Usulan` from trs_pembelian_header)";
            }elseif ($this->group == "Apotik" || $this->userGroup == 'CabangLogistik') {
                $byStatus = "$status and ifnull(App_APJ_Cabang,'') = ''  and tipe != 'Rutin'";
            }elseif($this->group == "BM"){
                  $byStatus = "$status and 
                 (case when ifnull(tipe,'') != 'Rutin' then (ifnull(App_APJ_Cabang,'') = 'Approve' and ifnull(App_BM_Status,'') = '') else ifnull(App_BM_Status,'') = '' end )";
            }elseif($this->group == "RBM"){
                $byStatus = "$status and  ifnull(App_RBM_Status,'') = '' and  ifnull(App_BM_Status,'') = 'Approve'";
            }else{
                 $byStatus = "$status ";
            }
       /* }else{
            $byStatus = " and No_Usulan not in (select trs_pembelian_header.`No_Usulan` from trs_pembelian_header)";
        }*/
         $query = $this->db->query("select * from trs_usulan_beli_header where (No_Usulan like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Tipe like '%".$search."%')  $byStatus order by added_time ASC ");    

        return $query;
    }

      public function prosesApprovalUsulanBeli($No = NULL,$status=NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE);
        if($status == 'Approve'){
            $approve  = "Approve";
            $message = "suksesapprove";
        }else{
            $approve  = "Reject";
            $message = "reject";
        }
        if ($this->group == "Apotik" || $this->userGroup == 'CabangApoteker') {
                // $approve_bm  = "Y";
                $date_approve_apj = date('Y-m-d H:i:s');
                $user_apj = $this->session->userdata('username');
                $update = $this->db->query("update trs_usulan_beli_header 
                      set Status_Usulan = 'Usulan',
                          App_APJ_Cabang = '".$approve."', 
                          App_APJC_Time = '".$date_approve_apj."',
                          user_apj_cabang = '".$user_apj."',
                          App_APJC_Alasan = '".$message."' 
                      where No_Usulan = '".$No."' and Cabang ='".$this->cabang."'"); 
                 if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update trs_usulan_beli_header 
                            set Status_Usulan = 'Usulan',
                                App_APJ_Cabang = '".$approve."', 
                                App_APJC_Time = '".$date_approve_apj."',
                                user_apj_cabang = '".$user_apj."',
                                App_APJC_Alasan = '".$message."'  
                            where No_Usulan = '".$No."' and Cabang ='".$this->cabang."'");
                    } 
                if($update){
                    $result = $this->db->query("select App_APJ_Cabang from trs_usulan_beli_header where No_Usulan = '".$No."' and Cabang ='".$this->cabang."' and App_APJ_Cabang ='Approve'")->num_rows();
                    if($result == 0){
                        $update =false;
                    }
                }
                if($update){
                   $message = $message."Apoteker Berhasil"; 
                }else{
                   $message = "Approve  Apoteker Gagal"; 
                }
         }elseif ($this->group == "BM") {
                // $approve_bm  = "Y";
                $date_approve_bm = date('Y-m-d H:i:s');
                $user_bm = $this->session->userdata('username');
                $update = $this->db->query("update trs_usulan_beli_header 
                      set Status_Usulan = 'Usulan',
                          App_BM_Status = '".$approve."', 
                          App_BM_Time = '".$date_approve_bm."',
                          user_bm = '".$user_bm."',
                          App_BM_Alasan = '".$message."' 
                      where No_Usulan = '".$No."' and 
                            (case when tipe != 'Rutin' then App_APJ_Cabang = 'Approve' else ifnull(App_BM_Status,'') = '' end) and 
                            Cabang ='".$this->cabang."'"); 
                 if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update trs_usulan_beli_header 
                            set Status_Usulan = 'Usulan',
                                App_BM_Status = '".$approve."', 
                                App_BM_Time = '".$date_approve_bm."',
                                user_bm = '".$user_bm."',
                                App_BM_Alasan = '".$message."' 
                            where No_Usulan = '".$No."' 
                                  and (case when tipe != 'Rutin' then App_APJ_Cabang = 'Approve' else ifnull(App_BM_Status,'') = '' end) and Cabang ='".$this->cabang."'");
                    } 
                if($update){
                    $result = $this->db->query("select App_BM_Status from trs_usulan_beli_header where No_Usulan = '".$No."' and Cabang ='".$this->cabang."' and App_BM_Status ='Approve'")->num_rows();
                    if($result == 0){
                        $update =false;
                    }
                }
                if($update){
                   $message = $message."BM Berhasil"; 
                }else{
                    $message = "Approve BM Gagal";
                }
         }
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function updateDataApprovalusulanbeli($no=NULL)
    {   
        $valid = false;
        $this->db2 = $this->load->database('pusat', TRUE); 
        $query2 = $this->db->query("select * from trs_usulan_beli_header where Status_Usulan = 'Usulan' and (ifnull(App_RBM_Status,'')='' or ifnull(App_APJ_Pst_status,'')='' or  ifnull(App_pusat_status,'')='')");
        $num2  = $query2->num_rows();
        $data = $query2->result();
        if($num2 == 0){
            $valid = false;
        }else{
            if ($this->db2->conn_id == TRUE) {
                foreach ($data as $usulan) {
                    $dt = $this->db2->query("select * from trs_usulan_beli_header where No_Usulan = '".$usulan->No_Usulan."' limit 1")->result();
                    if(empty($dt[0]->App_RBM_Status)){
                        $App_RBM_Status = '';
                        $user_rbm = '';
                        $App_RBM_Time = '';
                        $App_RBM_Alasan = '';
                    }else{
                        $App_RBM_Status = $dt[0]->App_RBM_Status;
                        $user_rbm       = $dt[0]->user_rbm;
                        $App_RBM_Time   = $dt[0]->App_RBM_Time;
                        $App_RBM_Alasan = $dt[0]->App_RBM_Alasan;
                    }

                    if(empty($dt[0]->App_APJ_Pst_status)){
                        $App_APJ_Pst_status = '';
                        $user_APJ_pusat     = '';
                        $App_APJ_Pst_Time   = '';
                        $App_APJ_Pst_Alasan = '';
                    }else{
                        $App_APJ_Pst_status   = $dt[0]->App_APJ_Pst_status;
                        $user_APJ_pusat       = $dt[0]->user_APJ_pusat;
                        $App_APJ_Pst_Time     = $dt[0]->App_APJ_Pst_Time;
                        $App_APJ_Pst_Alasan   = $dt[0]->App_APJ_Pst_Alasan;
                    }

                    if(empty($dt[0]->App_pusat_status)){
                        $App_pusat_status   = '';
                        $user_App_pusat     = '';
                        $App_pusat_Time     = '';
                        $App_pusat_Alasan   = '';
                    }else{
                        $App_pusat_status     = $dt[0]->App_pusat_status;
                        $user_App_pusat       = $dt[0]->user_App_pusat;
                        $App_pusat_Time       = $dt[0]->App_pusat_Time;
                        $App_pusat_Alasan     = $dt[0]->App_pusat_Alasan;
                    }

                    $this->db->set('App_RBM_Status',$App_RBM_Status);
                    $this->db->set('user_rbm',$user_rbm );
                    $this->db->set('App_RBM_Time', $App_RBM_Time);
                    $this->db->set('App_RBM_Alasan', $App_RBM_Alasan);
                    $this->db->set('App_APJ_Pst_status', $App_APJ_Pst_status);
                    $this->db->set('user_APJ_pusat', $user_APJ_pusat);
                    $this->db->set('App_APJ_Pst_Time', $App_APJ_Pst_Time);
                    $this->db->set('App_APJ_Pst_Alasan', $App_APJ_Pst_Alasan);
                    $this->db->set('App_pusat_status', $App_pusat_status);
                    $this->db->set('user_App_pusat', $user_App_pusat);
                    $this->db->set('App_pusat_Time', $App_pusat_Time);
                    $this->db->set('App_pusat_Alasan', $App_pusat_Alasan);
                    $this->db->where('No_Usulan', $usulan->No_Usulan);
                    $valid = $this->db->update('trs_usulan_beli_header');
                }
            }
        }
        return $valid;
            
    }
    public function viewpiutangpelanggan($pelanggan = NULL)
    {
        $query = $this->db->query("select mst_pelanggan.limit_kredit,
                                           mst_pelanggan.top,
                                           faktur_pelanggan.saldo,
                                           faktur_pelanggan.Umur_faktur
                                    from mst_pelanggan left join 
                                    ( select Pelanggan,
                                             (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                                             max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                                        from trs_faktur LEFT JOIN 
                                           ( SELECT trs_pelunasan_giro_detail.`KodePelanggan`,
                                            sum(trs_pelunasan_giro_detail.`ValuePelunasan`) AS 'totalgiro'
                                         FROM trs_pelunasan_giro_detail
                                         where Status = 'Open' 
                                         group by trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.`Pelanggan`
                                      where (LEFT(trs_faktur.Status,5) != 'Lunas' OR trs_faktur.`Saldo`!= 0) and trs_faktur.Status != 'Batal'
                                      group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                                    where mst_pelanggan.Kode ='".$pelanggan."'")->result();

        $detail = $this->db->query("SELECT DISTINCT trs_faktur.`NoFaktur`,
                                           trs_faktur.`TglFaktur`,
                                           trs_faktur.Acu,
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
                                           CONCAT(trs_faktur.`Salesman`,' - ',trs_faktur.`NamaSalesman`) AS 'Salesman',
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
                                    WHERE (LEFT(trs_faktur.Status,5) != 'Lunas' OR trs_faktur.`Saldo`!= 0)  AND           trs_faktur.`Pelanggan` = '".$pelanggan."' and trs_faktur.Status != 'Batal'
                                    order by Umur_faktur Desc")->result();
        $data = array("detail" =>$detail, "header" =>$query);
        return $data;
    }

    public function cetak($no_so = NULL)
    {
      $data_order = $this->db->query("
                          select 
                          a.NoSO, a.TglSO, a.Pelanggan,a. NamaPelanggan, a.AlamatPelanggan, a.TOP, a.Acu,
                          b.Value, b.Total
                          from trs_sales_order a
                          left join trs_sales_order_detail b on b.NoSO = a.NoSO
                          where a.NoSO = '".$no_so."'
                      ")->row();

      $piutang = $this->db->query("select mst_pelanggan.limit_kredit,
                         mst_pelanggan.top,
                         faktur_pelanggan.saldo,
                         faktur_pelanggan.Umur_faktur
                  from mst_pelanggan left join 
                  ( select Pelanggan,
                           (SUM(IFNULL(saldo,0)) + IFNULL(sumgiro.totalgiro,0)) AS 'saldo',
                           max(DATEDIFF(curdate(),trs_faktur.`TglFaktur`)) as 'Umur_faktur'
                      from trs_faktur LEFT JOIN 
                         ( SELECT trs_pelunasan_giro_detail.`KodePelanggan`,
                          sum(trs_pelunasan_giro_detail.`ValuePelunasan`) AS 'totalgiro'
                       FROM trs_pelunasan_giro_detail
                       where Status = 'Open' 
                       group by trs_pelunasan_giro_detail.`KodePelanggan`) AS sumgiro ON  sumgiro.KodePelanggan = trs_faktur.`Pelanggan`
                    where trs_faktur.`Saldo`!= 0
                    group by Pelanggan) as faktur_pelanggan on faktur_pelanggan.Pelanggan = mst_pelanggan.Kode
                  where mst_pelanggan.Kode ='".$data_order->Pelanggan."'")->row();
      
      if(count($piutang)>0){
        $data_order->Limit_Kredit = $piutang->limit_kredit;
        $data_order->Piutang = $piutang->saldo;
        $data_order->Umur_faktur = $piutang->Umur_faktur;
      }
        

      // log_message('error',print_r($piutang,true));
      // log_message('error',print_r($data_order,true));

      return $data_order;
    }

     public function updateDataCNDNPusat($status=NULL){

        $this->db2 = $this->load->database('pusat', TRUE);   
    
        $cndn = $this->db->query("select distinct NoDokumen from trs_faktur_cndn where ifnull(status,'') ='Usulan' and ifnull(StatusCNDN,'') = 'Usulan' ")->result();
        $No = "";
        if ($this->db2->conn_id == TRUE) {
            foreach ($cndn as $list) {
               $No = $list->NoDokumen;
               $cndn_pusat = $this->db2->query("select * from trs_faktur_cndn where NoDokumen = '".$list->NoDokumen."' and Cabang ='".$this->cabang."'");
               $num_cndn   = $cndn_pusat->num_rows();
               $data_cndn = $cndn_pusat->result();
               if($num_cndn > 0){
                    $this->db->set('Status', $data_cndn[0]->Status);
                    $this->db->set("TanggalCNDN", date("Y-m-d"));
                    $this->db->set('StatusCNDN', $data_cndn[0]->StatusCNDN);
                    $this->db->set('Approve_RBM', $data_cndn[0]->Approve_RBM);
                    $this->db->set('date_RBM', $data_cndn[0]->date_RBM);
                    $this->db->set('user_RBM', $data_cndn[0]->user_RBM);
                    $this->db->where('NoDokumen',$list->NoDokumen);
                    $this->db->where('Faktur',$data_cndn[0]->Faktur);
                    $this->db->where('Cabang',$this->cabang);
                    // $this->db->where('Status','Usulan');
                    $this->db->update('trs_faktur_cndn'); 
                    if($data_cndn[0]->StatusCNDN == 'Disetujui'){
                      $this->db->set("TglFaktur", date("Y-m-d"));
                      $this->db->set("TimeFaktur", date("Y-m-d H:i:s"));
                      $this->db->set("Status", 'Open');
                      $this->db->set("modified_by", $this->user);
                      $this->db->set("modified_at", date("Y-m-d H:i:s"));
                      $this->db->where("Cabang", $this->cabang);
                      $this->db->where("NoFaktur", $list->NoDokumen);
                      $valid =  $this->db->update("trs_faktur");
                    }
                    if($data_cndn[0]->StatusCNDN == 'Reject'){
                      $this->db->set("Status", 'Reject');
                      $this->db->set("Saldo", 0);
                      $this->db->set("modified_by", $this->user);
                      $this->db->set("modified_at", date("Y-m-d H:i:s"));
                      $this->db->where("Cabang", $this->cabang);
                      $this->db->where("NoFaktur", $list->NoDokumen);
                      $valid =  $this->db->update("trs_faktur");
                    }
                    
               }
            }
            // } JIKA SEBELUM UPDATE CEK STATUS DATA MASIH ADA YANG GAGAL APA TIDAK
        }
        else{
            return 'GAGAL';
        }

    }
    public function listDataApprovalAll($search = NULL, $length = NULL, $start = NULL, $status = NULL, $byfilter=null, $byLimit=null)
    {
        $byStatus = "";
        $statusLimit = "Limit";
        $statusTOP = "TOP";
        $statusDC = "DC";
        $statusDP = "DP";
        $approve_KSA = "N";
        $approve_BM = "N";
        
        if ($status == "LimitTOP") {
            if ($this->group == "KSA") {
                $byStatus = "and ((StatusLimit <> 'Ok' and ifnull(Approve_KSA,'N') = '".$approve_KSA."') or (StatusTOP <> 'Ok' and ifnull(Approve_TOP_KSA,'N') = '".$approve_KSA."')) and Status = 'Usulan' ";
            }elseif($this->group == "BM"){
                $byStatus = "and ((StatusLimit <> 'Ok' and ifnull(Approve_BM,'N') not in ('Y','R')   and ifnull(Approve_KSA,'N') in ('Y','R')) or (StatusTOP <> 'Ok' and ifnull(Approve_TOP_BM,'N') not in ('Y','R') and ifnull(Approve_TOP_KSA,'N') in ('Y','R')) or (StatusDiscCab <> 'Ok' and ifnull(Approve_DiscCab_BM,'N') not in ('Y','R')) or (StatusDiscPrins <> 'Ok' and ifnull(Approve_DiscPrins_BM,'N') not in ('Y','R'))) and Status = 'Usulan'";
            }elseif($this->group == "RBM"){
                $byStatus = "and StatusLimit <> 'Ok' and  ifnull(Approve_RBM,'N') = 'N' and StatusTOP <> 'Ok' and ifnull(Approve_TOP_RBM,'N') = '".$approve_BM."' and Status = 'Usulan'";
            }else{
                 $byStatus = " and Status in ('Usulan','Closed','Reject')";
            }        
        }else{
            $byStatus = " and Status in ('Usulan','Closed','Reject')";
        }  
        $query = $this->db->query("select * from trs_sales_order where (NoSo like '%".$search."%' or TglSO like '%".$search."%' or Pelanggan like '%".$search."%') $byStatus $byLimit");    
        return $query;
    }

    public function prosesDataApprovalAll($No = NULL,$Radiolimit=NULL,$RadioTop=NULL,$RadioDP=NULL,$RadioDC=NULL)
    {
       $this->db2 = $this->load->database('pusat', TRUE); 
       $cek2 = $this->db->query("select trs_sales_order.* ,
                                            mst_pelanggan.Limit_Kredit
                                     from trs_sales_order,mst_pelanggan
                                     where trs_sales_order.Pelanggan = mst_pelanggan.Kode and 
                                     trs_sales_order.NoSO = '".$No."' limit 1")->row(); 
       $cek = $this->cekpiutangpelanggan($cek2->Pelanggan);
        $limit_BM       = (int)$this->Model_main->CabangDev()[0]->limit_jual_BM;
        $limit_RMB      = $this->Model_main->CabangDev()[0]->limit_jual_RBM;  
        $limit_TOP_KSA  = $this->Model_main->CabangDev()[0]->top_KSA;
        $limit_TOP_BM   = $this->Model_main->CabangDev()[0]->top_BM;  
        $total_limit    = (int)$cek->Limit_Kredit + (int)$cek2->Total;
        if(!$limit_BM){
            $limit_BM = 0;
        }
        if(!$limit_RMB){
            $limit_RMB = 0;
        }
        $byStatus = "";
        $statusLimit = "";
        $statusTOP = "";
        $statusDC = "";
        $statusDP = "";
        $statusApprove = "";
        $message = "";
        //=== Approval Limit & TOP Jual =======
        if ($this->group == "BM") {
        //====== Limit ==============
          if($cek2->StatusLimit == 'Ok'){
            $statusLimit = "Ok";
            $app_limit = 'Y';
            $user_limit = $cek2->user_BM;
            $date_limit = $cek2->date_approve_BM;
          }else{
            if($cek2->Approve_BM == 'N'){
              if($Radiolimit == 'Y'){
                if($total_limit <= $limit_BM){
                  $statusLimit = "Ok";
                }else{
                  $statusLimit = "Limit";
                }
                $app_limit = 'Y';
                $user_limit = $this->session->userdata('username');
                $date_limit = date('Y-m-d H:i:s');
              }elseif($Radiolimit == 'R'){
                $statusLimit = "Limit";
                $app_limit = 'R';
                $user_limit = $this->session->userdata('username');
                $date_limit = date('Y-m-d H:i:s');
              }else{
                $statusLimit = "Limit";
                $app_limit = 'N';
                $user_limit = '';
                $date_limit = '';
              }
            }else{
              $statusLimit = $cek2->StatusLimit;
              $app_limit = $cek2->Approve_BM;
              $user_limit = $cek2->user_BM;
              $date_limit = $cek2->date_approve_BM;
            }     
          }
            //===========================
            //====== TOP ==============
          if($cek2->StatusTOP == 'Ok'){
            $statusTop = "Ok";
            $app_top = 'Y';
            $user_top = $cek2->user_TOP_BM;
            $date_top = $cek2->date_approve_TOP_BM;
          }else{
            if($cek2->Approve_TOP_BM == 'N'){
              if($RadioTop == 'Y'){
                if((int)$cek->TOP <= (int)$limit_TOP_BM){
                  $statusTop = "Ok";
                }else{
                  $statusTop = "Top";
                }
                $app_top = 'Y';
                $user_top = $this->session->userdata('username');
                $date_top = date('Y-m-d H:i:s');
              }elseif($RadioTop == 'R'){
                $statusTop = "Top";
                $app_top = 'R';
                $user_top = $this->session->userdata('username');
                $date_top = date('Y-m-d H:i:s');
              }else{
                $statusTop = "Top";
                $app_top = 'N';
                $user_top = '';
                $date_top = '';
              }
            }else{
              $statusTop = $cek2->StatusTOP;
              $app_top = $cek2->Approve_TOP_BM;
              $user_top = $cek2->user_TOP_BM;
              $date_top = $cek2->date_approve_TOP_BM;
            }    
          }
            //===========================
            //====== DC ==============
          if($cek2->StatusDiscCab == 'Ok'){
            $statusDC = "Ok";
            $app_dc = 'Y';
            $user_dc = $cek2->user_DiscCab_BM;
            $date_dc = $cek2->date_approve_DiscCab_BM;
          }else{
            if($cek2->Approve_DiscCab_BM == 'N'){
              if($RadioDC == 'Y'){
                $statusDC = "DC";
                $app_dc = 'Y';
                $user_dc = $this->session->userdata('username');
                $date_dc = date('Y-m-d H:i:s');
              }elseif($RadioDC == 'R'){
                $statusDC = "DC";
                $app_dc = 'R';
                $user_dc = $this->session->userdata('username');
                $date_dc = date('Y-m-d H:i:s');
              }else{
                $statusDC = "DC";
                $app_dc = 'N';
                $user_dc = '';
                $date_dc = '';
              } 
            }else{
              $statusDC = $cek2->StatusDiscCab;
              $app_dc = $cek2->Approve_DiscCab_BM;
              $user_dc = $cek2->user_DiscCab_BM;
              $date_dc = $cek2->date_approve_DiscCab_BM;
            }    
          }
            //===========================
            //====== DP ==============
          if($cek2->StatusDiscPrins == 'Ok'){
            $statusDP = "Ok";
            $app_dp = 'Y';
            $user_dp = $cek2->user_DiscPrins_BM;
            $date_dp = $cek2->date_approve_DiscPrins_BM;
          }else{
            if($cek2->Approve_DiscPrins_BM == 'N'){
              if($RadioDP == 'Y'){
                $statusDP = "DP";
                $app_dp = 'Y';
                $user_dp = $this->session->userdata('username');
                $date_dp = date('Y-m-d H:i:s');
              }elseif($RadioDP == 'R'){
                $statusDP = "DP";
                $app_dp = 'R';
                $user_dp = $this->session->userdata('username');
                $date_dp = date('Y-m-d H:i:s');
              }else{
                $statusDP = "DP";
                $app_dp = 'N';
                $user_dp = '';
                $date_dp = '';
              }
            }else{
              $statusDP = $cek2->StatusDiscPrins;
              $app_dp = $cek2->Approve_DiscPrins_BM;
              $user_dp = $cek2->user_DiscPrins_BM;
              $date_dp = $cek2->date_approve_DiscPrins_BM;
            }
          }
          //===========================
          $byStatus = ", Status = 'Usulan'";  
          $update = $this->db->query("update trs_sales_order 
                set StatusLimit = '".$statusLimit."',
                    StatusTOP= '".$statusTop."',
                    StatusDiscCab= '".$statusDC."',
                    StatusDiscPrins= '".$statusDP."', 
                    statusPusat = 'Gagal',
                    limit_piutang_bm = '".$total_limit."',
                    umur_piutang_bm ='".$cek->TOP."',
                    Approve_BM = '".$app_limit."', 
                    date_approve_BM = '".$date_limit."',
                    user_BM = '".$user_limit."',
                    Approve_TOP_BM = '".$app_top."', 
                    date_approve_TOP_BM = '".$date_top."',
                    user_TOP_BM = '".$user_top."',
                    Approve_DiscCab_BM = '".$app_dc."', 
                    date_approve_DiscCab_BM = '".$date_dc."',
                    user_DiscCab_BM = '".$user_dc."',
                    Approve_DiscPrins_BM = '".$app_dp."', 
                    date_approve_DiscPrins_BM = '".$date_dp."',
                    user_DiscPrins_BM = '".$user_dp."',
                    modified_at = '".date('Y-m-d H:i:s')."',
                    modified_by = '".$this->session->userdata('username')."' $byStatus 
                where NoSO = '".$No."'");
            //==== Cek Ulang hasil update status top & limit ====
          $cekulang = $this->db->query("select * from trs_sales_order where noso ='".$No."' limit 1")->row();
          if(($cekulang->StatusTOP == 'TOP' or $cekulang->StatusTOP == 'Top') and $cekulang->Approve_TOP_BM == 'Y'){
            if((int)$cek->TOP <= (int)$limit_TOP_BM){
              log_message('error','Update top Ulang');
              $this->db->query("update trs_sales_order
                                set StatusTOP = 'Ok' 
                                where noso ='".$No."'");
            }
          }
          if($cekulang->StatusLimit == 'Limit'  and $cekulang->Approve_BM == 'Y'){
            if($total_limit <= $limit_BM){
              log_message('error','Update limit Ulang');
              $this->db->query("update trs_sales_order
                                set StatusLimit = 'Ok' 
                                where noso ='".$No."'");
            }
          }
          //================ end of cek ====================
          if(($total_limit > $limit_BM) or ((int)$cek->TOP > (int)$limit_TOP_BM) or ($cek2->StatusDiscCab == 'DC') or ($cek2->StatusDiscPrins == 'DP')){
              $this->Model_order->prosesDataSO($No);
              log_message('error','kirim pusat');
          }
          $status_update = "ok";
          $message = "sukses";                                
        }elseif ($this->group == "KSA") {
            //====== Limit ==============
          if($cek2->StatusLimit == 'Ok'){
            $statusLimit = "Ok";
            $app_limit = 'Y';
            $user_limit = $cek2->user_KSA;
            $date_limit = $cek2->date_approve_KSA;
          }else{
            if($cek2->Approve_KSA == 'N'){
              if($Radiolimit == 'Y'){
                $statusLimit = "Limit";
                $app_limit = 'Y';
                $user_limit = $this->session->userdata('username');
                $date_limit = date('Y-m-d H:i:s');
              }elseif($Radiolimit == 'R'){
                $statusLimit = "Limit";
                $app_limit = 'R';
                $user_limit = $this->session->userdata('username');
                $date_limit = date('Y-m-d H:i:s');
              }else{
                $statusLimit = "Limit";
                $app_limit = 'N';
                $user_limit = '';
                $date_limit = '';
              }
            }else{
              $statusLimit = $cek2->StatusLimit;
              $app_limit = $cek2->Approve_KSA;
              $user_limit = $cek2->user_KSA;
              $date_limit = $cek2->date_approve_KSA;
            } 
          }
            //===========================
            //====== TOP ==============
          if($cek2->StatusTOP == 'Ok'){
            $statusTop = "Ok";
            $app_top = 'Y';
            $user_top = $cek2->user_TOP_KSA;
            $date_top = $cek2->date_approve_TOP_KSA;
          }else{
            if($cek2->Approve_TOP_KSA == 'N'){
              if($RadioTop == 'Y'){
                if((int)$cek->TOP <= (int)$limit_TOP_KSA)  {
                  $statusTop = "Ok";
                }else{
                  $statusTop = "Top";
                }
                $app_top = 'Y';
                $user_top = $this->session->userdata('username');
                $date_top = date('Y-m-d H:i:s');
              }elseif($RadioTop == 'R'){
                $statusTop = "Top";
                $app_top = 'R';
                $user_top = $this->session->userdata('username');
                $date_top = date('Y-m-d H:i:s');
              }else{
                $statusTop = "Top";
                $app_top = 'N';
                $user_top = '';
                $date_top = '';
              }
            }else{
              $statusTop = $cek2->StatusTOP;
              $app_top = $cek2->Approve_TOP_KSA;
              $user_top = $cek2->user_TOP_KSA;
              $date_top = $cek2->date_approve_TOP_KSA;
            }    
          }
            //===========================
          $byStatus = ", Status = 'Usulan'";  
          $update = $this->db->query("update trs_sales_order 
                set StatusLimit = '".$statusLimit."',
                    StatusTOP= '".$statusTop."', 
                    limit_piutang_ksa = '".$total_limit."',
                    umur_piutang_ksa ='".$cek->TOP."',
                    statusPusat = 'Gagal',
                    Approve_KSA = '".$app_limit."', 
                    date_approve_KSA = '".$date_limit."',
                    user_KSA = '".$user_limit."',
                    Approve_TOP_KSA = '".$app_top."', 
                    date_approve_TOP_KSA = '".$date_top."',
                    user_TOP_KSA = '".$user_top."',
                    modified_at = '".date('Y-m-d H:i:s')."',
                    modified_by = '".$this->session->userdata('username')."' $byStatus 
                where NoSO = '".$No."'");
          $status_update = "ok";
          $message = "sukses";
        }          
        // $this->db->set("Cabang", $this->cabang);
        // $this->db->set("Dokumen", "Sales Order");
        // $this->db->set("NoDokumen", $No);
        // $this->db->set("Status", $statusApprove);
        // $this->db->set("TimeUsulan", date("Y-m-d H:i:s"));
        // $valid =  $this->db->insert("trs_approval");
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    function fix_cogs($NoSO,$Keterangan2){
      if ($Keterangan2 == 'Bidan') {
        $table = "trs_sales_order_detail_bidan";
      }else{
        $table = "trs_sales_order_detail";
      }

      $data = $this->db->query("SELECT KodeProduk from $table where NoSO='$NoSO' GROUP BY KodeProduk")->result();

      foreach ($data as $r) {

        $data1 = $this->db->query("SELECT acu,KodeProduk,BatchNo,NoFaktur,NoBPB FROM `trs_faktur_detail` WHERE TipeDokumen ='Retur' AND KodeProduk = '".$r->KodeProduk."' AND COGS=0 ")->result();

        foreach ($data1 as $r1) {
          $Kode = $r1->KodeProduk;
          $BatchDoc = $r1->NoBPB;
          $BatchNo = $r1->BatchNo;
          $No = $r1->NoFaktur;
          $Acu = $r1->acu;
          //cek cogs invdet
            $cekstokdet =$this->db->query("select * from trs_invdet where NoDokumen = '".$BatchDoc."' and kodeproduk = '".$Kode."' and BatchNo ='".$BatchNo."' and Gudang ='Baik' limit 1" )->row();

            //cogs invdet ada
            if($cekstokdet->UnitCOGS > 0){
              $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();

              $this->db->set("COGS", ($cekstokdet->UnitCOGS) * -1);
              $this->db->set("TotalCOGS", (($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur)) * -1 );
              $this->db->where("Nofaktur",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_faktur_detail'); 

              $this->db->set("COGS", $cekstokdet->UnitCOGS);
              $this->db->set("TotalCOGS", ($cekstokdet->UnitCOGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
              $this->db->where("NoDO",$No);
              $this->db->where("KodeProduk", $Kode);
              $this->db->where("BatchNo", $BatchNo);
              $this->db->where("NoBPB", $BatchDoc);
              $valid = $this->db->update('trs_delivery_order_sales_detail'); 
            }else{
              //cogs invdet tidak ada
              // mengecek data retur
              $cekretur = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$BatchDoc."' and cogs = 0")->result();
              foreach ($cekretur as $key) {
                //mengecek acu faktur dari retur
                $cekfaktur1 = $this->db->query("select * from trs_faktur_detail where nofaktur ='".$key->Acu."' and KodeProduk='".$key->KodeProduk."' and BatchNo ='".$key->BatchNo."' and NoBPB = '".$key->NoBPB."' limit 1 ")->row();
                // jika cogs acu retur ( faktur ) 0
                if($cekfaktur1->COGS == 0){
                  //cek cogs invdet
                  $cekinvdet = $this->db->query("select * from trs_invdet where KodeProduk ='".$key->KodeProduk."' and NoDokumen ='".$key->NoBPB."'")->row();
                  if($cekinvdet->UnitCOGS > 0){
                    $this->db->set("COGS", $cekinvdet->UnitCOGS);
                    $this->db->set("TotalCOGS", ($cekinvdet->UnitCOGS) * ($key->QtyFaktur + $key->BonusFaktur));
                    $this->db->where("Nofaktur",$key->NoFaktur);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("NoBPB", $key->NoBPB);
                    $valid = $this->db->update('trs_faktur_detail'); 
                  }
                }else{
                  // jika cogs acu retur ( faktur ) ada value nya update cogs retur
                  $this->db->set("COGS", $cekfaktur1->COGS);
                  $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($key->QtyFaktur + $key->BonusFaktur));
                  $this->db->where("Nofaktur",$key->NoFaktur);
                  $this->db->where("KodeProduk", $key->KodeProduk);
                  $this->db->where("BatchNo", $key->BatchNo);
                  $this->db->where("NoBPB", $key->NoBPB);
                  $valid = $this->db->update('trs_faktur_detail'); 
                  if($valid){
                    //update cogs invdet
                    $this->db->set("UnitCOGS", $cekfaktur1->COGS);
                    $this->db->where("Nodokumen",$key->NoFaktur);
                    $this->db->where("KodeProduk", $key->KodeProduk);
                    $this->db->where("BatchNo", $key->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                    //update cogs faktur
                    $fak= $this->db->query("select * from trs_faktur_detail where nofaktur ='".$No."' and KodeProduk='".$Kode."' and BatchNo ='".$BatchNo."' and NoBPB = '".$BatchDoc."' limit 1 ")->row();
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                    $this->db->where("Nofaktur",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    //update cogs do faktur
                    $valid = $this->db->update('trs_faktur_detail'); 
                    $this->db->set("COGS", $cekfaktur1->COGS);
                    $this->db->set("TotalCOGS", ($cekfaktur1->COGS) * ($fak->QtyFaktur + $fak->BonusFaktur));
                    $this->db->where("NoDO",$No);
                    $this->db->where("KodeProduk", $Kode);
                    $this->db->where("BatchNo", $BatchNo);
                    $this->db->where("NoBPB", $BatchDoc);
                    $valid = $this->db->update('trs_delivery_order_sales_detail'); 


                    $this->db->query("UPDATE trs_sales_order SET alasan_status = ''  where  NoSO ='".$NoSO."'");
                  }
                }
              }
            }

        }


          
      }

      return ["status" =>$valid,"message"=>" Berhasil"];
    }

    public function listDatakirimrelokasiPusat($no = null)
    {   
       
        $query = $this->db->query("SELECT * 
                                from trs_relokasi_kirim_header 
                                where Cabang_Pengirim = '".$this->session->userdata('cabang')."' and 
                                      (ifnull(Status_kiriman,'') = 'ApproveLog') and ifnull(GudangPusat,'') <> ''
                                order by Tgl_kirim DESC, No_Relokasi ASC");
        return $query;
    }

}