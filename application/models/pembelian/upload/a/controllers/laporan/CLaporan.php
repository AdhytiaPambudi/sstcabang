<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CLaporan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model_main');
        $this->load->library('excel');
        $this->load->helper('download');
        $this->load->model('Model_main');
        $this->load->model('laporan/Model_laporan');
        $this->load->model('pembelian/Model_inventori');
        $this->logs = $this->session->all_userdata();
        $this->logged = $this->session->userdata('userLogged');
        $this->userGroup = $this->session->userdata('user_group');
        $this->cabang = $this->session->userdata('cabang');
        $this->content = array(
            "base_url" => base_url(),
            "tgl" => date("Y-m-d"),
            "logs" => $this->session->all_userdata(),
            "logged" => $this->session->userdata('userLogged'),
        );

    }

    public function laporanpiutang()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanpiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    

    public function laporanpiutangx($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = [];

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            //if($cek=='all')
            //  $total = $this->Model_faktur->listData('','','',$tipe)->num_rows();
            //else
            //  $total = $this->Model_faktur->listData('','','Open',$tipe)->num_rows();

            if($cek=='all')
                $total = $this->Model_laporan->listDataPiutangAll('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->listDataPiutangAll('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = "and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or Status like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or StatusInkaso like '%".$search."%' or TipeDokumen like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            //if($cek=='all')
            //  $query=$this->Model_faktur->listData($bySearch,$byLimit,'',$tipe)->result();
            //else
            //  $query=$this->Model_faktur->listData($bySearch, $byLimit, "Open", $tipe)->result();

            if($cek=='all')
                $query=$this->Model_laporan->listDataPiutangAll($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->listDataPiutangAll($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            //if($search!=""){          
            //  if($cek=='all')
            //      $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'','',$tipe)->num_rows();
            //  else
            //      $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'', "Open", $tipe)->num_rows();
            //}

            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPiutangAll($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPiutangAll($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                if(($baris->Saldo + $baris->saldo_giro) == 0){
                    $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
                }else{
                    $row[] = "";
                }
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "");
                if($baris->Saldo == 0){
                    $umur = floor((strtotime($baris->TglPelunasan) - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                }else{
                    $umur = floor((time() - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                }
                $row[] = $umur;
                $row[] = (!empty($baris->TOP) ? $baris->TOP : "");
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $top = $baris->TOP;
                $tgljto = date('Y-m-d',strtotime("+".$top." day", strtotime($baris->TglFaktur)));
                // $row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
                $row[] = (!empty($tgljto) ? $tgljto : "");
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Saldo + $baris->saldo_giro) ? number_format($baris->Saldo + $baris->saldo_giro) : 0)."</p>";
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->alasan_jto) ? $baris->alasan_jto : "");
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function laporanpdudo()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanpdu_do.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanpdudoX($cek = null)
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tipe   = $params['tipe'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];

            if($length == -1){
                $bylimit    = "";
            }else{
                $bylimit    = " LIMIT ".$start.", ".$length;
            }

            if($tgl1 && $tgl2){
                $bydate = " and trs_faktur_detail.TglFaktur between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_faktur_detail.TglFaktur = '".date('Y-m-d')."'";
            }

            $bysearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or Gross like '%".$search."%' or Salesman like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%')";

            if($cek=='all'){
                $query=$this->Model_laporan->listDataPDUDOAll($bysearch, $bylimit,'',$tipe, $bydate)->result();
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPDUDOAll($bysearch,'','',$tipe, $bydate)->num_rows();
            }else{
                $query=$this->Model_laporan->listDataPDUDOAll($bysearch, $bylimit, "Open", $tipe, $bydate)->result();
                $output['recordsTotal']=$output['recordsFiltered']=$this->Model_laporan->listDataPDUDOAll($bysearch,'', "Open", $tipe, $bydate)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
                $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglDO) ? $baris->TglDO : "");
                // $row[] = floor((time() - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->AlamatKirim) ? $baris->AlamatKirim : "");
                if($baris->TipeDokumen == 'Retur'){
                    $row[] = "<p align='right'>".(!empty($baris->Banyak * -1) ? number_format($baris->Banyak * -1) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus * -1) ? number_format($baris->Bonus * -1) : 0)."</p>";
                }else{
                    $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                }
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = "<p align='right'>".(!empty($baris->DiscCab) ? number_format($baris->DiscCab) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->DiscPri) ? number_format($baris->DiscPri) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }


    public function laporanpdufaktur()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanpdu_faktur.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanpdufakturX($cek = null)
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tipe   = $params['tipe'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];

            if($length == -1){
                $bylimit    = "";
            }else{
                $bylimit    = " LIMIT ".$start.", ".$length;
            }

            if($tgl1 && $tgl2){
                $bydate = " and trs_faktur_detail.TglFaktur between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_faktur_detail.TglFaktur = '".date('Y-m-d')."'";
            }

            $bysearch   = " and (NoFaktur like '%".$search."%' or Prinsipal2 like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or Gross like '%".$search."%' or Salesman like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%')";

            if($cek=='all'){
                $query=$this->Model_laporan->listDataPDUAll($bysearch, $bylimit,'',$tipe, $bydate)->result();
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPDUAll($bysearch,'','',$tipe, $bydate)->num_rows();
                // log_message('error',print_r($output,true));
            }else{
                $query=$this->Model_laporan->listDataPDUAll($bysearch, $bylimit, "Open", $tipe, $bydate)->result();
                $output['recordsTotal']=$output['recordsFiltered']=$this->Model_laporan->listDataPDUAll($bysearch,'', "Open", $tipe, $bydate)->num_rows();
                // log_message('error',print_r($output,true));
            }
            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            // if($search!=""){            
            //     if($cek=='all')
            //     else
            // }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
                $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                // $row[] = floor((time() - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->AlamatFaktur) ? $baris->AlamatFaktur : "");
                if($baris->TipeDokumen == 'Retur'){
                    $row[] = "<p align='right'>".(!empty($baris->Banyak * -1) ? number_format($baris->Banyak * -1) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus * -1) ? number_format($baris->Bonus * -1) : 0)."</p>";
                }else{
                    $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                }
                
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = "<p align='right'>".(!empty($baris->DiscCab) ? number_format($baris->DiscCab,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->DiscPri) ? number_format($baris->DiscPri,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total,2) : 0)."</p>";
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
                $row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }



    public function laporanpdufakturdo()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporandetail_do_faktur.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanpdufakturdoX($cek = null)
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $keyword = $params["keyword"];
            $data       = [];
            $bysearch   = "";
            $bysearch1   = "";
            $bylimit    = "";
            $bydate     = "";
            $bydateD     = "";
            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tipe   = $params["tipe"];
            $tgl1   = $params["tgl1"];
            $tgl2   = $params["tgl2"];

            if($tgl1 && $tgl2){
                $bydate = " and trs_faktur_detail.TglFaktur between '".$tgl1."' and '".$tgl2."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_faktur_detail.TglFaktur = '".date('Y-m-d')."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO = '".date('Y-m-d')."'";
            }
            if($keyword['s_cabang'] != "" or $keyword['s_prinsipal'] != "" or $keyword['s_prinsipal2'] != "" or $keyword['s_pabrik'] != "" or $keyword['s_produk'] != "" or $keyword['s_nama'] != "" or $keyword['s_nodo'] != "" or $keyword['s_nofaktur'] != "" or $keyword['s_tanggal'] != "" or $keyword['s_acu'] != "" or $keyword['s_pelanggan'] != "" or $keyword['s_namapel'] != "" or $keyword['s_unit'] != "" or $keyword['s_bns'] != "" or $keyword['s_salesman'] != "" or $keyword['s_disccab'] != "" or $keyword['s_discprins'] != "" or $keyword['s_gross'] != "" or $keyword['s_potongan'] != "" or $keyword['s_value'] != "" or $keyword['s_ppn'] != "" or $keyword['s_total'] != "" or $keyword['s_bayar'] != "" or $keyword['s_tipe'] != ""){
                $bysearch   = " and (Cabang like '%".$keyword['s_cabang']."%' and Prinsipal like '%".$keyword['s_prinsipal']."%' and Pabrik like '%".$keyword['s_pabrik']."%' and NoFaktur like '%".$keyword['s_nofaktur']."%' and NoDO like '%".$keyword['s_nodo']."%' and Pelanggan like '%".$keyword['s_pelanggan'] ."%' and TglFaktur like '%".$keyword['s_tanggal'] ."%'  and NamaPelanggan like '%".$keyword['s_namapel']."%' and Acu like '%".$keyword['s_acu']."%' and ifnull(CaraBayar,'') like '%".$keyword['s_bayar']."%' and Gross like '%".$keyword['s_gross']."%' and Salesman like '%".$keyword['s_salesman']."%' and KodeProduk like '%".$keyword['s_produk']."%' and NamaProduk like '%".$keyword['s_nama']."%' and Prinsipal2 like '%".$keyword['s_prinsipal2']."%' and QtyFaktur like '%".$keyword['s_unit']."%' and BonusFaktur like '%".$keyword['s_bns']."%' and DiscCab like '%".$keyword['s_disccab']."%' and ifnull(DiscPrins2,0) like '%".$keyword['s_discprins']."%' and ifnull(DiscPrins1,0) like '%".$keyword['s_discprins']."%' and Potongan like '%".$keyword['s_potongan']."%' and Value like '%".$keyword['s_value']."%' and Total like '%".$keyword['s_total']."%' and Ppn like '%".$keyword['s_ppn']."%'  and TipeDokumen like '%".$keyword['s_tipe']."%')";
                $bysearch1   = " and (Cabang like '%".$keyword['s_cabang']."%' and Prinsipal like '%".$keyword['s_prinsipal']."%' and Pabrik like '%".$keyword['s_pabrik']."%' and NoFaktur like '%".$keyword['s_nofaktur']."%' and NoDO like '%".$keyword['s_nodo']."%' and Pelanggan like '%".$keyword['s_pelanggan'] ."%' and TglDO like '%".$keyword['s_tanggal'] ."%' and NamaPelanggan like '%".$keyword['s_namapel']."%' and Acu like '%".$keyword['s_acu']."%' and ifnull(CaraBayar,'') like '%".$keyword['s_bayar']."%' and Gross like '%".$keyword['s_gross']."%' and Salesman like '%".$keyword['s_salesman']."%' and KodeProduk like '%".$keyword['s_produk']."%' and NamaProduk like '%".$keyword['s_nama']."%' and Prinsipal2 like '%".$keyword['s_prinsipal2']."%' and QtyDO like '%".$keyword['s_unit']."%' and BonusDO like '%".$keyword['s_bns']."%' and DiscCab like '%".$keyword['s_disccab']."%' and ifnull(DiscPrins2,0) like '%".$keyword['s_discprins']."%' and ifnull(DiscPrins1,0) like '%".$keyword['s_discprins']."%' and Potongan like '%".$keyword['s_potongan']."%' and Value like '%".$keyword['s_value']."%' and Total like '%".$keyword['s_total']."%' and Ppn like '%".$keyword['s_ppn']."%'  and TipeDokumen like '%".$keyword['s_tipe']."%')";
            }
            // $bysearch   = " and (NoFaktur like '%".$search."%' or NoDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or Gross like '%".$search."%' or Salesman like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%' or Prinsipal2 like '%".$search."%')";
            if($length == -1){
                $bylimit    = "";
            }else{
                $bylimit    = " LIMIT ".$start.", ".$length;
            }

            // if($cek=='all'){
                $query=$this->Model_laporan->listDataPDUFDOAll($bysearch, $bylimit,'',$tipe, $bydate, $bydateD,$bysearch1)->result();
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPDUFDOAll($bysearch,'','',$tipe, $bydate, $bydateD,$bysearch1)->num_rows();
                // log_message('error',print_r($output,true));
            // }else{
            //     $query=$this->Model_laporan->listDataPDUFDOAll($bysearch, $bylimit, "Open", $tipe, $bydate, $bydateD,$bysearch1)->result();
            //     $output['recordsTotal']=$output['recordsFiltered']=$this->Model_laporan->listDataPDUFDOAll($bysearch,'', "Open", $tipe, $bydate, $bydateD,$bysearch1)->num_rows();
            //     // log_message('error',print_r($output,true));
            // }
            // log_message("error","kesini");
            // log_message("error",print_r($query,true));
            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
                $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                // $row[] = floor((time() - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                if($baris->TipeDokumen == 'Retur'){
                    $row[] = "<p align='right'>".(!empty($baris->Banyak * -1) ? number_format($baris->Banyak * -1) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus * -1) ? number_format($baris->Bonus * -1) : 0)."</p>";
                }else{
                   $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                   $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>"; 
                }
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = "<p align='right'>".(!empty($baris->DiscCab) ? number_format($baris->DiscCab,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->DiscPri) ? number_format($baris->DiscPri,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn,2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total,2) : 0)."</p>";
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    
    public function laporanbpbdetail()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanbpbH.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanbpbdetailX($cek = null)
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $tipe = $this->input->post('tipe');      
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";
            $draw=$_REQUEST['draw'];

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];
            if($tgl1 && $tgl2){
                $bydate = " and trs_terima_barang_detail.TglDokumen between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_terima_barang_detail.TglDokumen = '".date('Y-m-d')."'";
            }
            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];
            $length = "";
            if(isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            $start=$_REQUEST['start'];
            $search=$_REQUEST['search']["value"];
            $output=array();
            $output['draw']=$draw;
            $total = $this->Model_laporan->listDataBPBDetailAll($bySearch,$byLimit,'',$tipe,$bydate)->num_rows();
            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();
            if($search!=""){
            $bySearch = " and (NoDokumen like '%".$search."%' or TglDokumen like '%".$search."%' or NoPO like '%".$search."%' or NoPR like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Produk like '%".$search."%' or NamaProduk like '%".$search."%' or Status like '%".$search."%' or NoSJ like '%".$search."%' or NoBex like '%".$search."%' or NoInv like '%".$search."%' or Qty like '%".$search."%' or Bonus like '%".$search."%' or HrgBeli like '%".$search."%' or Disc like '%".$search."%' or DiscT like '%".$search."%' or BatchNo like '%".$search."%' or ExpDate like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or VALUE like '%".$search."%' or PPN like '%".$search."%' or Total like '%".$search."%' or NoDO like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($_REQUEST['length'] != -1){
                $byLimit = " LIMIT ".$start.", ".$length;
            }
            $query=$this->Model_laporan->listDataBPBDetailAll($bySearch, $byLimit, "", $tipe,$bydate)->result();
            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataBPBDetailAll($bySearch,'','',$tipe,$bydate)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Produk) ? $baris->Produk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->Satuan) ? $baris->Satuan : "");
                $row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
                $row[] = (!empty($baris->TglDokumen) ? $baris->TglDokumen : "");
                $row[] = (!empty($baris->NoSJ) ? $baris->NoSJ : "");
                $row[] = (!empty($baris->NoBEX) ? $baris->NoBEX : "");
                $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->HrgBeli) ? number_format($baris->HrgBeli) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc) ? number_format($baris->Disc) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->DiscT) ? number_format($baris->DiscT) : 0)."</p>";
                $row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
                $row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->VALUE) ? number_format($baris->VALUE) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
                $row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
                $row[] = (!empty($baris->NoPR) ? $baris->NoPR : "");
                $row[] = (!empty($baris->NoPO) ? $baris->NoPO : "");
                $row[] = (!empty($baris->Keterangan) ? $baris->Keterangan : "");
                $row[] = (!empty($baris->Penjelasan) ? $baris->Penjelasan : "");
                $row[] = (!empty($baris->NoInv) ? $baris->NoInv : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function laporanUsulanBelidetail()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanusulanbelidetail.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanUsulanBelidetailX($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length=$_REQUEST['length'];

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            if($cek=='all')
                $total = $this->Model_laporan->listDataUsulanBeliDetailAll('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->listDataUsulanBeliDetailAll('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or Gross like '%".$search."%' or Salesman like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            if($cek=='all')
                $query=$this->Model_laporan->listDataUsulanBeliDetailAll($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->listDataUsulanBeliDetailAll($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataUsulanBeliDetailAll($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataUsulanBeliDetailAll($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Kategori) ? $baris->Kategori : "");
                $row[] = (!empty($baris->Produk) ? $baris->Produk : "");
                $row[] = (!empty($baris->Nama_Produk) ? $baris->Nama_Produk : "");
                $row[] = (!empty($baris->Satuan) ? $baris->Satuan : "");
                $row[] = (!empty($baris->No_Usulan) ? $baris->No_Usulan : "");
                $row[] = (!empty($baris->Tgl_Usulan) ? $baris->Tgl_Usulan : "");
                $row[] = (!empty($baris->Status_Usulan) ? $baris->Status_Usulan : "");
                $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Harga_Beli_Cab) ? number_format($baris->Harga_Beli_Cab) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Harga_Deal) ? number_format($baris->Harga_Deal) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc2) ? number_format($baris->Disc2) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc_Deal) ? number_format($baris->Disc_Deal) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value_Usulan) ? number_format($baris->Value_Usulan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->PPN) ? number_format($baris->PPN) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = (!empty($baris->Keterangan) ? $baris->Keterangan : "");
                $row[] = (!empty($baris->Penjelasan) ? $baris->Penjelasan : "");
                $row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }



    public function laporancndnH()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporancndndetail.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function listDatacndnHx($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            if($cek=='all')
                $total = $this->Model_laporan->listDatacndnHq('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->listDatacndnHq('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (Faktur like '%".$search."%' or TanggalCNDN like '%".$search."%' or NoDokumen like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' )";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            if($cek=='all')
                $query=$this->Model_laporan->listDatacndnHq($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->listDatacndnHq($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDatacndnHq($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDatacndnHq($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
                $row[] = (!empty($baris->Faktur) ? $baris->Faktur : "");
                $row[] = (!empty($baris->TanggalCNDN) ? $baris->TanggalCNDN : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }


    public function lplg()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanpelanggan.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function laporanpelangganX($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = [];

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            if($cek=='all')
                $total = $this->Model_laporan->listDataPelangganM('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->listDataPelangganM('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " where (a.Kode like '%".$search."%' or a.Pelanggan like '%".$search."%' or a.Tipe_Pelanggan like '%".$search."%' or a.Jenis_Pelanggan like '%".$search."%' or a.Alamat like '%".$search."%' or a.Alamat2 like '%".$search."%' or a.Alamat_Kirim like '%".$search."%' or a.Kota like '%".$search."%' or a.Nama_Pajak like '%".$search."%' or a.Alamat_Pajak like '%".$search."%' or NPWP like '%".$search."%' or Telp like '%".$search."%' or Fax like '%".$search."%' or a.Limit_Kredit like '%".$search."%' or a.TOP like '%".$search."%' or a.Cara_Bayar like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            if($cek=='all')
                $query=$this->Model_laporan->listDataPelangganM($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->listDataPelangganM($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPelangganM($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPelangganM($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;
            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Created_at) ? $baris->Created_at : "");
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Kode) ? $baris->Kode : "");
                $row[] = (!empty($baris->Kode2) ? $baris->Kode2 : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->No_Ijin_Usaha) ? $baris->No_Ijin_Usaha : "");
                $row[] = (!empty($baris->No_Ijin_Apoteker) ? $baris->No_Ijin_Apoteker : "");
                $row[] = "<p align='right'>".(!empty($baris->SaldoP) ? number_format($baris->SaldoP) : 0)."</p>";
                $row[] = (!empty($baris->Nama_Faktur) ? $baris->Nama_Faktur : "");
                $row[] = (!empty($baris->Alamat) ? $baris->Alamat : "");
                $row[] = (!empty($baris->Alamat2) ? $baris->Alamat2 : "");
                $row[] = (!empty($baris->Alamat_Kirim) ? $baris->Alamat_Kirim : "");
                $row[] = (!empty($baris->Nama_Pajak) ? $baris->Nama_Pajak : "");
                $row[] = (!empty($baris->Alamat_Pajak) ? $baris->Alamat_Pajak : "");
                $row[] = (!empty($baris->NPWP) ? $baris->NPWP : "");
                $row[] = (!empty($baris->Tipe_Pajak) ? $baris->Tipe_Pajak : "");
                $row[] = (!empty($baris->Kota) ? $baris->Kota : "");
                $row[] = (!empty($baris->Group_Pelanggan) ? $baris->Group_Pelanggan : "");
                $row[] = (!empty($baris->Group_Pelanggan2) ? $baris->Group_Pelanggan2 : "");
                $row[] = (!empty($baris->Area) ? $baris->Area : "");
                $row[] = (!empty($baris->Class) ? $baris->Class : "");
                $row[] = (!empty($baris->Telp) ? $baris->Telp : "");
                $row[] = (!empty($baris->Fax) ? $baris->Fax : "");
                $row[] = "<p align='right'>".(!empty($baris->Limit_Kredit) ? number_format($baris->Limit_Kredit) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->TOP) ? number_format($baris->TOP) : 0)."</p>";
                $row[] = (!empty($baris->Cara_Bayar) ? $baris->Cara_Bayar : "");
                $row[] = (!empty($baris->Aktif) ? $baris->Aktif : "");
                $row[] = (!empty($baris->Kat) ? $baris->Kat : "");
                $row[] = (!empty($baris->Tipe_2) ? $baris->Tipe_2 : "");
                $row[] = (!empty($baris->Tipe_Harga) ? $baris->Tipe_Harga : "");
                $row[] = (!empty($baris->Rayon_1) ? $baris->Rayon_1 : "");
                $row[] = (!empty($baris->Wilayah) ? $baris->Wilayah : "");
                $row[] = (!empty($baris->Kategori_2) ? $baris->Kategori_2 : "");
                $row[] = (!empty($baris->Prioritas) ? $baris->Prioritas : "");
                $row[] = "<p align='right'>".(!empty($baris->Prins_Onf) ? number_format($baris->Prins_Onf) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Cab_Onf) ? number_format($baris->Cab_Onf) : 0)."</p>";
                $row[] = (!empty($baris->Kode_Prov) ? $baris->Kode_Prov : "");
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = (!empty($baris->Salesman2) ? $baris->Salesman2 : "");
                $row[] = (!empty($baris->SIPA) ? $baris->SIPA : "");
                $row[] = (!empty($baris->EDSIPA) ? $baris->EDSIPA : "");
                $row[] = (!empty($baris->Apoteker) ? $baris->Apoteker : "");
                $row[] = (!empty($baris->Asst_Apoteker) ? $baris->Asst_Apoteker : "");
                $row[] = (!empty($baris->TTK) ? $baris->TTK : "");
                $row[] = (!empty($baris->EDTTK) ? $baris->EDTTK : "");
                $row[] = (!empty($baris->Email_Pelanggan) ? $baris->Email_Pelanggan : "");
                $row[] = (!empty($baris->Email_Apoteker) ? $baris->Email_Apoteker : "");
                $row[] = (!empty($baris->Email_Asst_Apoteker) ? $baris->Email_Asst_Apoteker : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }


    public function datastoksalesman()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content['gprins']= $this->Model_laporan->getstokprisipal();
            $this->twig->display('laporan/laporanstoksalesman.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 

    public function getstoksalesman()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";
            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            // if(isset($params['tgl1']) && isset($params['tgl2'])){
            //     $tgl1 = $this->input->post('tgl1');
            //     $tgl2 = $this->input->post('tgl2');
                // $byperiode = " and TglFaktur between '".$tgl1."' and '".$tgl2."' ";
            //}
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            // $length = (isset($_REQUEST['length']) != -1) ? $_REQUEST['length'] : "";

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }


            /*Menghitung total data didalam database*/
           
            $total = $this->Model_laporan->getstoksalesman('','',$tipe)->num_rows();
            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (NamaPrinsipal like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
        
            $query=$this->Model_laporan->getstoksalesman($bySearch,$byLimit,$tipe)->result();
            // log_message("error",print_r($query,true));
           
            if($search!=""){            
               
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getstoksalesman($bySearch,'',$tipe)->num_rows();
              
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                 $row[] = (!empty($baris->NamaPrinsipal) ? $baris->NamaPrinsipal : "");
                 $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                 $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                 $row[] = "<p align='right'>".(!empty(($baris->UnitStok)) ? ($baris->UnitStok) : 0)."</p>";
                 $row[] = "<p align='right'>".(!empty(($baris->HNA)) ? ($baris->HNA) : 0)."</p>";
                 
                $data[] = $row;
                $i++;
              }
              $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function agingpiut()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            // $this->content['gprins']= $this->Model_laporan->getstokprisipal();
            $this->twig->display('laporan/laporanumurpiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 


    public function getagingpiut()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            if(isset($params['jenis'])){
                $jenis = $this->input->post('jenis');
            }

            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if(isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if(isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            // $length = (isset($_REQUEST['length']) != -1) ? $_REQUEST['length'] : "";

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }


            /*Menghitung total data didalam database*/           
            if($jenis == "UF") {
                $total = $this->Model_laporan->getagingpiutF('','',$tipe)->num_rows();
            }elseif ($jenis == "UL") {
                $total = $this->Model_laporan->getagingpiutL('','',$tipe)->num_rows();
            }else
            {
                $total = $this->Model_laporan->getagingpiut('','',$tipe)->num_rows();
            }
            log_message('error',$jenis);
            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/

            if($search!=""){
            $bySearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or Status like '%".$search."%' or NoDO like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%' or ValueCashDiskon like '%".$search."%' or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or Ppn like '%".$search."%' or Total like '%".$search."%' or Saldo like '%".$search."%' or StatusInkaso like '%".$search."%' or TipeDokumen like '%".$search."%')";
            }

            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
        
            if ($jenis=="UF") {
                $query=$this->Model_laporan->getagingpiutF($bySearch,$byLimit,$tipe)->result();
            }elseif ($jenis=="UL") {
                $query=$this->Model_laporan->getagingpiutL($bySearch,$byLimit,$tipe)->result();
            }else
            {
                $query=$this->Model_laporan->getagingpiut($bySearch,$byLimit,$tipe)->result();
            }
            // log_message("error",print_r($query,true));
           
            if($search!=""){            
                if ($jenis=="UF") {
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getagingpiutF($bySearch,'',$tipe)->num_rows(); 
                }elseif ($jenis=="UL") {
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getagingpiutL($bySearch,'',$tipe)->num_rows(); 
                }else
                {
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getagingpiut($bySearch,'',$tipe)->num_rows(); 
                }
            }


            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->umurL) ? $baris->umurL : "");
                $row[] = (!empty($baris->umurF) ? $baris->umurF : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->TOP) ? $baris->TOP : "");
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $top = $baris->TOP;
                $tgljto = date('Y-m-d',strtotime("+".$top." day", strtotime($baris->TglFaktur)));
                // $row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
                $row[] = (!empty($tgljto) ? $tgljto : "");
                // $row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Saldo+$baris->saldo_giro) ? number_format($baris->Saldo+$baris->saldo_giro) : 0)."</p>";
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->alasan_jto) ? $baris->alasan_jto : "");
                $data[] = $row;
                $i++;              }
              $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }



    public function lappu()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            // $this->content['gprins']= $this->Model_laporan->getstokprisipal();
            $this->twig->display('laporan/laporanPU.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 


    public function getlappu()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            if(isset($params['jenis'])){
                $jenis = $this->input->post('jenis');
            }
            if(isset($params['kat1'])){
                $kat1 = $this->input->post('kat1');
            }


            // log_message('error',$jenis);
            // log_message('error',$kat1);

            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if(isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if(isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            // $length = (isset($_REQUEST['length']) != -1) ? $_REQUEST['length'] : "";

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }


            /*Menghitung total data didalam database*/           
            if($jenis == "All") {
                if ($kat1 == "All") {
                    $total = $this->Model_laporan->getpudat('','',$tipe,'All','All')->num_rows();
                }else
                {
                    $total = $this->Model_laporan->getpudat('','',$tipe,'All',$kat1)->num_rows();
                }
            }else
            {
                if ($kat1 == "All") {
                    $total = $this->Model_laporan->getpudat('','',$tipe,$jenis,'All')->num_rows();
                }else
                {
                    $total = $this->Model_laporan->getpudat('','',$tipe,$jenis,$kat1)->num_rows();
                }
            }

            // log_message('error',$jenis);
            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/

            if($search!=""){
            $bySearch = " AND (CABANG like '%".$search."%' or AREA like '%".$search."%' or a.INDEX like '%".$search."%' or KDDOKJDI like '%".$search."%' or NODOKJDI like '%".$search."%' or TGLFAK like '%".$search."%' or TOP like '%".$search."%' or UMUR like '%".$search."%' or KATEGORI like '%".$search."%' or KATEGORI2 like '%".$search."%' or KODESALES like '%".$search."%' or NAMA_LANG like '%".$search."%' or ALAMAT_LANG like '%".$search."%' or NILDOK like '%".$search."%' or SALDO like '%".$search."%' or KATEGORI3 like '%".$search."%' or JUDUL like '%".$search."%' or SPESIAL like '%".$search."%' or STATUS like '%".$search."%' or CARA_BAYAR like '%".$search."%' or KETERANGAN_JATUH_TEMPO like '%".$search."%' or COMBO like '%".$search."%')";
            }

            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
        

            if($jenis == "All") {
                if ($kat1 == "All") {
                    $query=$this->Model_laporan->getpudat($bySearch,$byLimit,$tipe,'All','All')->result();
                }else
                {
                    $query=$this->Model_laporan->getpudat($bySearch,$byLimit,$tipe,'All',$kat1)->result();
                }
            }else
            {
                if ($kat1 == "All") {
                    $query=$this->Model_laporan->getpudat($bySearch,$byLimit,$tipe,$jenis,'All')->result();
                }else
                {
                    $query=$this->Model_laporan->getpudat($bySearch,$byLimit,$tipe,$jenis,$kat1)->result();
                }
            }

            // log_message("error",print_r($query,true));
           

               if($jenis == "All") {
                    if ($kat1 == "All") {
                        $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getpudat($bySearch,'',$tipe,'All','All')->num_rows();
                    }else
                    {
                        $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getpudat($bySearch,'',$tipe,'All',$kat1)->num_rows();
                    }
                }else
                {
                    if ($kat1 == "All") {
                        $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getpudat($bySearch,'',$tipe,$jenis,'All')->num_rows();     
                    }else
                    {
                        $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->getpudat($bySearch,'',$tipe,$jenis,$kat1)->num_rows();
                    }
                }

            


            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                
                $row[] = (!empty($baris->CABANG) ? $baris->CABANG : "");
                $row[] = (!empty($baris->AREA) ? $baris->AREA : "");
                $row[] = (!empty($baris->INDEX) ? $baris->INDEX : "");
                $row[] = (!empty($baris->KDDOKJDI) ? $baris->KDDOKJDI : "");
                $row[] = (!empty($baris->NODOKJDI) ? $baris->NODOKJDI : "");
                $row[] = (!empty($baris->TGLFAK) ? $baris->TGLFAK : "");
                $row[] = (!empty($baris->NODO) ? $baris->NODO : "");
                $row[] = (!empty($baris->TOP) ? $baris->TOP : "");
                $row[] = (!empty($baris->UMUR) ? $baris->UMUR : "");
                $row[] = (!empty($baris->KATEGORI) ? $baris->KATEGORI : "");
                $row[] = (!empty($baris->KATEGORI2) ? $baris->KATEGORI2 : "");
                $row[] = (!empty($baris->KODESALES) ? $baris->KODESALES : "");
                $row[] = (!empty($baris->NAMA_LANG) ? $baris->NAMA_LANG : "");
                $row[] = (!empty($baris->ALAMAT_LANG) ? $baris->ALAMAT_LANG : "");
                $row[] = (!empty($baris->NILDOK) ? $baris->NILDOK : "");
                $row[] = (!empty($baris->SALDO) ? $baris->SALDO : "");
                $row[] = (!empty($baris->KATEGORI3) ? $baris->KATEGORI3 : "");
                $row[] = (!empty($baris->JUDUL) ? $baris->JUDUL : "");
                $row[] = (!empty($baris->SPESIAL) ? $baris->SPESIAL : "");
                $row[] = (!empty($baris->STATUS) ? $baris->STATUS : "");
                $row[] = (!empty($baris->CARA_BAYAR) ? $baris->CARA_BAYAR : "");
                $row[] = (!empty($baris->KETERANGAN_JATUH_TEMPO) ? $baris->KETERANGAN_JATUH_TEMPO : "");
                $row[] = (!empty($baris->COMBO) ? $baris->COMBO : "");
                $row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "");
                $row[] = (!empty($baris->nodih) ? $baris->nodih : "");
                $row[] = (!empty($baris->tgldih) ? $baris->tgldih : "");
                $row[] = (!empty($baris->alasan_jto) ? $baris->alasan_jto : "");
                $data[] = $row;
                $i++;              }
              $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function laporanPT()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanPT.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }   

    public function listlaporanPT()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            /*Menagkap semua data yang dikirimkan oleh client*/
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }

            $total = $this->Model_laporan->listlaporanPT('','',$tgl1)->num_rows();

            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            if($search!=""){
            $bySearch = " AND (WIL like '%".$search."%' or NM_CABANG like '%".$search."%' or KODE_JUAL like '%".$search."%' or NODOKJDI like '%".$search."%' or NO_ACU like '%".$search."%' or KODE_PELANGGAN like '%".$search."%' or KODE_KOTA like '%".$search."%' or KODE_TYPE like '%".$search."%' or KODE_LANG like '%".$search."%' or NAMA_LANG like '%".$search."%' or ALAMAT like '%".$search."%' or JUDUL like '%".$search."%' or KODEPROD like '%".$search."%' or NAMAPROD like '%".$search."%' or UNIT like '%".$search."%' or PRINS like '%".$search."%' or BANYAK like '%".$search."%' or HARGA like '%".$search."%' or PRSNXTRA like '%".$search."%' or PRINPXTRA like '%".$search."%' or TOT1 like '%".$search."%' or NILJU like '%".$search."%' or PPN like '%".$search."%' or COGS like '%".$search."%' or KODESALES like '%".$search."%' or TGLDOK like '%".$search."%' or TGLEXP like '%".$search."%' or BATCH like '%".$search."%'  or Area like '%".$search."%' or TELP like '%".$search."%' or RAYON like '%".$search."%' or PANEL like '%".$search."%' or DiscPrins1 like '%".$search."%' or DiscPrins2 like '%".$search."%' or CashDiskon like '%".$search."%' or DiscCabMax like '%".$search."%' or KetDiscCabMax like '%".$search."%' or DiscPrinsMax like '%".$search."%' or KetDiscPrinsMax like '%".$search."%' or NoDO like '%".$search."%' or Status like '%".$search."%' or Tipe like '%".$search."%' or acu2 like '%".$search."%' or NoBPB like '%".$search."%' )";
            }

            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/

            $query=$this->Model_laporan->listlaporanPT($bySearch, $byLimit,$tgl1)->result();

            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listlaporanPT($bySearch,'',$tgl1)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris->WIL) ? $baris->WIL : "");
                $row[] = (!empty($baris->NM_CABANG) ? $baris->NM_CABANG : "");
                $row[] = (!empty($baris->KODE_JUAL) ? $baris->KODE_JUAL : "");
                $row[] = (!empty($baris->NODOKJDI) ? $baris->NODOKJDI : "");
                $row[] = (!empty($baris->NO_ACU) ? $baris->NO_ACU : "");
                $row[] = (!empty($baris->KODE_PELANGGAN) ? $baris->KODE_PELANGGAN : "");
                $row[] = (!empty($baris->KODE_KOTA) ? $baris->KODE_KOTA : "");
                $row[] = (!empty($baris->KODE_TYPE) ? $baris->KODE_TYPE : "");
                $row[] = (!empty($baris->KODE_LANG) ? $baris->KODE_LANG : "");
                $row[] = (!empty($baris->NAMA_LANG) ? $baris->NAMA_LANG : "");
                $row[] = (!empty($baris->ALAMAT) ? $baris->ALAMAT : "");
                $row[] = (!empty($baris->JUDUL) ? $baris->JUDUL : "");
                $row[] = (!empty($baris->KODEPROD) ? $baris->KODEPROD : "");
                $row[] = (!empty($baris->NAMAPROD) ? $baris->NAMAPROD : "");
                // if($baris->TipeDokumen == 'Returx' or $baris->TipeDokumen == 'CN'){
                //     $row[] = (!empty($baris->UNIT * -1) ? number_format($baris->UNIT * -1): "");
                //     $row[] = (!empty($baris->PRINS * -1) ? number_format($baris->PRINS * -1) : 0);
                //     $row[] = (!empty($baris->BANYAK * -1) ? number_format($baris->BANYAK) * -1 : 0);
                // }else{
                    $row[] = (!empty($baris->UNIT) ? $baris->UNIT : "");
                    $row[] = (!empty($baris->PRINS) ? number_format($baris->PRINS) : 0);
                    $row[] = (!empty($baris->BANYAK) ? number_format($baris->BANYAK) : 0);
                // }
                $row[] = (!empty($baris->HARGA) ? number_format($baris->HARGA) : 0);
                $row[] = (!empty($baris->PRSNXTRA) ? number_format($baris->PRSNXTRA,2) : 0);
                $row[] = (!empty($baris->PRINPXTRA) ? number_format($baris->PRINPXTRA,2) : 0);
                $row[] = (!empty($baris->TOT1) ? number_format($baris->TOT1,2) : 0);
                $row[] = (!empty($baris->PPN) ? number_format($baris->PPN,2) : 0);
                $row[] = (!empty($baris->NILJU) ? number_format($baris->NILJU,2) : 0);
                $row[] = (!empty($baris->COGS) ? number_format($baris->COGS,2) : 0);
                $row[] = (!empty($baris->KODESALES) ? $baris->KODESALES :"");
                $row[] = (!empty($baris->TGLDOK) ? $baris->TGLDOK : "");
                $row[] = (!empty($baris->TGLEXP) ? $baris->TGLEXP : "");
                $row[] = (!empty($baris->BATCH) ? $baris->BATCH : "");
                $row[] = (!empty($baris->Area) ? $baris->Area : "");
                $row[] = (!empty($baris->TELP) ? $baris->TELP : "");
                $row[] = (!empty($baris->RAYON) ? $baris->RAYON : "");
                $row[] = (!empty($baris->PANEL) ? $baris->PANEL : "");
                $row[] = (!empty($baris->DiscPrins1) ? number_format($baris->DiscPrins1,2) : 0);
                $row[] = (!empty($baris->DiscPrins2) ? number_format($baris->DiscPrins2,2) : 0);
                $row[] = (!empty($baris->CashDiskon) ? $baris->CashDiskon : 0);
                $row[] = (!empty($baris->DiscCabMax) ? number_format($baris->DiscCabMax,2) : 0);
                $row[] = (!empty($baris->KetDiscCabMax) ? $baris->KetDiscCabMax : "");
                $row[] = (!empty($baris->DiscPrinsMax) ? number_format($baris->DiscPrinsMax,2) : 0);
                $row[] = (!empty($baris->KetDiscPrinsMax) ? $baris->KetDiscPrinsMax : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->Tipe) ? $baris->Tipe : "");
                $row[] = (!empty($baris->NO_ACU2) ? $baris->NO_ACU2 : "");
                $row[] = (!empty($baris->NoBPB) ? $baris->NoBPB : "");
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporanpdufakturdo2()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporandetail_do_faktur2.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    

    public function laporanpdufakturdoX2($cek = null)
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];
            if($tgl1 && $tgl2){
                $bydate = " and trs_faktur_detail.TglFaktur between '".$tgl1."' and '".$tgl2."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_faktur_detail.TglFaktur = '".date('Y-m-d')."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO = '".date('Y-m-d')."'";
            }

            $query=$this->Model_laporan->listDataPDUFDOAll2($bydate, $bydateD)->result();
            $return['Result'] = $query;
            echo json_encode($return);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function laporandobelidetail()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporandobelidetail.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 

    public function laporandobelidetailX($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length=$_REQUEST['length'];

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            if($cek=='all')
                $total = $this->Model_laporan->listDataDobeliDetailAll('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->listDataDobeliDetailAll('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (NoDokumen like '%".$search."%' or TglDokumen like '%".$search."%' or NoBPB like '%".$search."%' or NoAcuDokumen like '%".$search."%' or NoPO like '%".$search."%' or NoPR like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Produk like '%".$search."%' or NamaProduk like '%".$search."%' or Status like '%".$search."%' or NoSJ like '%".$search."%' or NoBex like '%".$search."%' or NoInv like '%".$search."%' or Qty like '%".$search."%' or Bonus like '%".$search."%' or HrgBeli like '%".$search."%' or Disc like '%".$search."%' or DiscT like '%".$search."%' or BatchNo like '%".$search."%' or ExpDate like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or VALUE like '%".$search."%' or PPN like '%".$search."%' or Total like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');

            if($cek=='all')
                $query=$this->Model_laporan->listDataDobeliDetailAll($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->listDataDobeliDetailAll($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataDobeliDetailAll($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataDobeliDetailAll($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Produk) ? $baris->Produk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->Satuan) ? $baris->Satuan : "");
                $row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
                $row[] = (!empty($baris->TglDokumen) ? $baris->TglDokumen : "");
                $row[] = (!empty($baris->NoBPB) ? $baris->NoBPB : "");
                $row[] = (!empty($baris->NoSJ) ? $baris->NoSJ : "");
                $row[] = (!empty($baris->NoBEX) ? $baris->NoBEX : "");
                $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->HrgBeli) ? number_format($baris->HrgBeli) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc) ? number_format($baris->Disc) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->DiscT) ? number_format($baris->DiscT) : 0)."</p>";
                $row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
                $row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->VALUE) ? number_format($baris->VALUE) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
                $row[] = (!empty($baris->Pabrik) ? $baris->Pabrik : "");
                $row[] = (!empty($baris->NoPR) ? $baris->NoPR : "");
                $row[] = (!empty($baris->NoPO) ? $baris->NoPO : "");
                $row[] = (!empty($baris->Keterangan) ? $baris->Keterangan : "");
                $row[] = (!empty($baris->Penjelasan) ? $baris->Penjelasan : "");
                $row[] = (!empty($baris->NoInv) ? $baris->NoInv : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->NoAcuDokumen) ? $baris->NoAcuDokumen : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    } 

    function getexport(){
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            if(isset($params['jenis'])){
                $jenis = $this->input->post('jenis');
            }
            if(isset($params['kat1'])){
                $kat1 = $this->input->post('kat1');
            }
            $query=$this->Model_laporan->getexport($tipe,$jenis,$kat1)->result();
            if(count($query)>0){
            
                $objPHPExcel = new PHPExcel();
                // Set properties
                $objPHPExcel->getProperties()
                      ->setCreator("Wahyu Purnomo") //creator
                        ->setTitle("Programmer - PT. Sapta Saritama");  //file title
     
                $objset = $objPHPExcel->setActiveSheetIndex(0); //inisiasi set object
                $objget = $objPHPExcel->getActiveSheet();  //inisiasi get object
     
                $objget->setTitle('Sample Sheet'); //sheet title
                //Warna header tabel
                $objget->getStyle("A1:Z1")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '92d050')
                        ),
                        'font' => array(
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                //table header
                $cols = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y"); 
                $val = array("CABANG","AREA","INDEX","KDDOKJDI","NODOKJDI","TGLFAK","NODO","TOP","UMUR","KATEGORI","KATEGORI2","KODESALES","NAMA_LANG","ALAMAT_LANG","NILDOK","SALDO","KATEGORI3","JUDUL","SPESIAL","STATUS","CARA_BAYAR","KETERANGAN_JATUH_TEMPO","COMBO","NODIH","TGLDIH","STATUS_INKASO");

                for ($a=0;$a<25; $a++) 
                {
                    $objset->setCellValue($cols[$a].'1', $val[$a]);
                     
                    // //Setting lebar cell
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25); // REGION
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25); // CABANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30); // AREA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30); // INDEX
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25); // COMBO
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25); // KDDOKJDI
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25); // NODOKJDI
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25); // TGLFAK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25); // TOP
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25); // UMUR
                    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25); // KATEGORI
                    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50); // KATEGORI2
                    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25); // KODESALES
                    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(75); // NAMA_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(100); // ALAMAT_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(35); // NILDOK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(35); // SALDO
                    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(35); // KATEGORI3
                    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(25); // JUDUL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(25); // SPESIAL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(35); // STATUS
                    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25); // CARA_BAYAR
                    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(50); // 
                    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(25); //
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(25); //
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(25); //KETERANGAN_JATUH_TEMPO
                 
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cols[$a].'1')->applyFromArray($style);
                }
                $baris  = 2;
                foreach ($query as $frow){
                    
                   //pemanggilan sesuaikan dengan nama kolom tabel
                    // $objset->setCellValue("A".$baris, $frow->REGION); //membaca data nama
                    $objset->setCellValue("A".$baris, $frow->CABANG); //membaca data alamat
                    $objset->setCellValue("B".$baris, $frow->AREA); //membaca data alamat
                    $objset->setCellValue("C".$baris, $frow->INDEX); //membaca data alamat
                    $objset->setCellValue("D".$baris, $frow->KDDOKJDI); //membaca data alamat
                    $objset->setCellValue("E".$baris, $frow->NODOKJDI); //membaca data alamat
                    $objset->setCellValue("F".$baris, $frow->TGLFAK); //membaca data alamat
                    $objset->setCellValue("G".$baris, $frow->NODO); //membaca data alamat
                    $objset->setCellValue("H".$baris, $frow->TOP); //membaca data alamat
                    $objset->setCellValue("I".$baris, $frow->UMUR); //membaca data alamat
                    $objset->setCellValue("J".$baris, $frow->KATEGORI); //membaca data alamat
                    $objset->setCellValue("K".$baris, $frow->KATEGORI2); //membaca data alamat
                    $objset->setCellValue("L".$baris, $frow->KODESALES); //membaca data alamat
                    $objset->setCellValue("M".$baris, $frow->NAMA_LANG); //membaca data alamat
                    $objset->setCellValue("N".$baris, $frow->ALAMAT_LANG); //membaca data alamat
                    $objset->setCellValue("O".$baris, $frow->NILDOK); //membaca data alamat
                    $objset->setCellValue("P".$baris, $frow->SALDO); //membaca data alamat
                    $objset->setCellValue("Q".$baris, $frow->KATEGORI3); //membaca data alamat
                    $objset->setCellValue("R".$baris, $frow->JUDUL); //membaca data alamat
                    $objset->setCellValue("S".$baris, $frow->SPESIAL); //membaca data alamat
                    $objset->setCellValue("T".$baris, $frow->STATUS); //membaca data alamat
                    $objset->setCellValue("U".$baris, $frow->CARA_BAYAR); //membaca data alamat
                    $objset->setCellValue("V".$baris, $frow->KETERANGAN_JATUH_TEMPO); //membaca data alamat
                    $objset->setCellValue("W".$baris, $frow->COMBO); //membaca data alamat
                    $objset->setCellValue("X".$baris, $frow->nodih); //membaca data alamat
                    $objset->setCellValue("Y".$baris, $frow->tgldih); //membaca data alamat
                    $objset->setCellValue("Z".$baris, $frow->StatusInkaso); //membaca data alamat
                     
                    //Set number value
                    // $objPHPExcel->getActiveSheet()->getStyle('C1:C'.$baris)->getNumberFormat()->setFormatCode('0');
                     
                    $baris++;
                }
                $objPHPExcel->getActiveSheet()->setTitle('Laporan PU');
                $objPHPExcel->setActiveSheetIndex(0);  
                $filename = urlencode("lappu".date("Y-m-d").".xlsx");  
                $temp_filename = 'd:/php/'. $filename;   
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
                header('Cache-Control: max-age=0'); //no cache
                ob_start();
                $objWriter->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();

                $response =  array(
                    'op' => 'ok',
                    'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
                    'filename' => $filename
                );
                echo json_encode($response);
            }else{
                redirect('Excel');
            }
            
        }else{   
            redirect("auth/logout");
        }

    }

    function getexportPT(){
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            // if(isset($params['cabang'])){
            //     $cabang = $this->input->post('cabang');
            // }
            // if(isset($params['optpusat'])){
            //     $dpusat = $this->input->post('optpusat');
            // }
            // if(isset($params['optcabang'])){
            //     $dcabang = $this->input->post('optcabang');
            // }
            $query=$this->Model_laporan->getexportPT($tgl1)->result();
            if(count($query)>0){
            
                $objPHPExcel = new PHPExcel();
                // Set properties
                $objPHPExcel->getProperties()
                      ->setCreator("Wahyu Purnomo") //creator
                        ->setTitle("Programmer - PT. Sapta Saritama");  //file title
     
                $objset = $objPHPExcel->setActiveSheetIndex(0); //inisiasi set object
                $objget = $objPHPExcel->getActiveSheet();  //inisiasi get object
     
                $objget->setTitle('Sample Sheet'); //sheet title
                //Warna header tabel
                $objget->getStyle("A1:AS1")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '92d050')
                        ),
                        'font' => array(
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                //table header
                $cols = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS"); 
                $val = array("WIL","NM_CABANG","KODE_JUAL","NODOKJDI","NO_ACU","KODE_PELANGGAN","KODE_KOTA","KODE_TYPE","KODE_LANG","NAMA_LANG","ALAMAT","JUDUL","KODEPROD","NAMAPROD","UNIT","PRINS","BANYAK","HARGA","PRSNXTRA","PRINPXTRA","TOT1","PPN","NILJU","COGS","KODESALES","TGLDOK","TGLEXP","BATCH","Area","TELP","RAYON","TIPE2","DiscPrins1","DiscPrins2","CashDiskon","DiscCabMax","KetDiscCabMax","DiscPrinsMax","KetDiscPrinsMax","NoDO","Status","TipeDokumen","Tipe","Acu2","NoBPB");

                for ($a=0;$a<45; $a++) 
                {
                    $objset->setCellValue($cols[$a].'1', $val[$a]);
                     
                    // //Setting lebar cell
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25); // WIL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25); // NM_CABANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25); // KODE_JUAL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25); // NODOKJDI
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25); // NO_ACU
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25); // KODE_PELANGGAN
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25); // KODE_KOTA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25); // KODE_TYPE
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25); // KODE_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50); // NAMA_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(100); // ALAMAT
                    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25); // JUDUL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25); // KODEPROD
                    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50); // NAMAPROD
                    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // UNIT
                    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(35); // PRINS
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(25); // BANYAK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25); // HARGA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(25); // PRSNXTRA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(25); // PRINPXTRA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(25); // TOT1  //TOT1
                    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25); // NILJU  //PPN
                    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(25); // PPN  //NILJU
                    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(25); // COGS
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(25); // KODESALES
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(25); // TGLDOK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(25); // TGLEXP
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(25); // BATCH
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(25); // Area
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(25); // TELP
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(25); // RAYON
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(25); // PANEL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(25); // DiscPrins1
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(25); // DiscPrins2
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(25); // CashDiskon
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(25); // DiscCabMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(25); // KetDiscCabMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(25); // DiscPrinsMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(25); // KetDiscPrinsMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(25); // NoDO
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(25); // Status
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(25); // TipeDokumen
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(25); // Tipe
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(25); // ACU2
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AS')->setWidth(25); // BPB
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cols[$a].'1')->applyFromArray($style);
                }
                $baris  = 2;
                foreach ($query as $frow){
                    // $objPHPExcel->getActiveSheet()
                    //             ->getStyle('D1:D'.$baris)
                    //             ->getNumberFormat()
                    //             ->setFormatCode('0');
                   //pemanggilan sesuaikan dengan nama kolom tabel
                    $objset->setCellValue("A".$baris, $frow->WIL); //membaca data nama
                    $objset->setCellValue("B".$baris, $frow->NM_CABANG); //membaca data alamat
                    $objset->setCellValue("C".$baris, $frow->KODE_JUAL); //membaca data alamat
                    $objset->setCellValue("D".$baris, $frow->NODOKJDI); //membaca data alamat
                    $objset->setCellValue("E".$baris, $frow->NO_ACU); //membaca data alamat
                    $objset->setCellValue("F".$baris, $frow->KODE_PELANGGAN); //membaca data alamat
                    $objset->setCellValue("G".$baris, $frow->KODE_KOTA); //membaca data alamat
                    $objset->setCellValue("H".$baris, $frow->KODE_TYPE); //membaca data alamat
                    $objset->setCellValue("I".$baris, $frow->KODE_LANG); //membaca data alamat
                    $objset->setCellValue("J".$baris, $frow->NAMA_LANG); //membaca data alamat
                    $objset->setCellValue("K".$baris, $frow->ALAMAT); //membaca data alamat
                    $objset->setCellValue("L".$baris, $frow->JUDUL); //membaca data alamat
                    $objset->setCellValue("M".$baris, $frow->KODEPROD); //membaca data alamat
                    $objset->setCellValue("N".$baris, $frow->NAMAPROD); //membaca data alamat
                    $objset->setCellValue("O".$baris, $frow->UNIT); //membaca data alamat
                    $objset->setCellValue("P".$baris, $frow->PRINS); //membaca data alamat
                    $objset->setCellValue("Q".$baris, $frow->BANYAK); //membaca data alamat
                    $objset->setCellValue("R".$baris, $frow->HARGA); //membaca data alamat
                    $objset->setCellValue("S".$baris, $frow->PRSNXTRA); //membaca data alamat
                    $objset->setCellValue("T".$baris, $frow->PRINPXTRA); //membaca data alamat
                    $objset->setCellValue("U".$baris, $frow->TOT1); //membaca data alamat
                    $objset->setCellValue("V".$baris, $frow->PPN); //membaca data alamat
                    $objset->setCellValue("W".$baris, $frow->NILJU); //membaca data alamat
                    $objset->setCellValue("X".$baris, $frow->COGS); //membaca data alamat
                    $objset->setCellValue("Y".$baris, $frow->KODESALES); //membaca data alamat
                    $objset->setCellValue("Z".$baris, $frow->TGLDOK); //membaca data alamat
                    $objset->setCellValue("AA".$baris, $frow->TGLEXP); //membaca data alamat
                    $objset->setCellValue("AB".$baris, $frow->BATCH); //membaca data alamat
                    $objset->setCellValue("AC".$baris, $frow->Area); //membaca data alamat
                    $objset->setCellValue("AD".$baris, $frow->TELP); //membaca data alamat
                    $objset->setCellValue("AE".$baris, $frow->RAYON); //membaca data alamat
                    $objset->setCellValue("AF".$baris, $frow->PANEL); //membaca data alamat
                    $objset->setCellValue("AG".$baris, $frow->DiscPrins1); //membaca data alamat
                    $objset->setCellValue("AH".$baris, $frow->DiscPrins2); //membaca data alamat
                    $objset->setCellValue("AI".$baris, $frow->CashDiskon); //membaca data alamat
                    $objset->setCellValue("AJ".$baris, $frow->DiscCabMax); //membaca data alamat
                    $objset->setCellValue("AK".$baris, $frow->KetDiscCabMax); //membaca data alamat
                    $objset->setCellValue("AL".$baris, $frow->DiscPrinsMax); //membaca data alamat
                    $objset->setCellValue("AM".$baris, $frow->KetDiscPrinsMax); //membaca data alamat
                    $objset->setCellValue("AN".$baris, $frow->NoDO); //membaca data alamat
                    $objset->setCellValue("AO".$baris, $frow->Status); //membaca data alamat
                    $objset->setCellValue("AP".$baris, $frow->TipeDokumen); //membaca data alamat
                    $objset->setCellValue("AQ".$baris, $frow->Tipe); //membaca data alamat
                    $objset->setCellValue("AR".$baris, $frow->NO_ACU2); //membaca data alamat
                    $objset->setCellValue("As".$baris, $frow->NoBPB); //membaca data alamat
                    //Set number value                    
                    $baris++;
                }
                $objPHPExcel->getActiveSheet()->getStyle('D2:D'.$baris)->getNumberFormat()->setFormatCode('0');
                $objPHPExcel->getActiveSheet()->getStyle('AN2:AN'.$baris)->getNumberFormat()->setFormatCode('0');
                $objPHPExcel->getActiveSheet()->setTitle('Laporan PT');
                $objPHPExcel->setActiveSheetIndex(0);  
                $filename = urlencode("lappt".date("Y-m-d").".xlsx");  
                // $temp_filename = 'd:/php/'. $filename;   
                // $size = filesize($filename);
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                // header("Content-length: $size");;
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
                header('Cache-Control: max-age=0'); //no cache
                ob_start();
                $objWriter->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();

                $response =  array(
                    'op' => 'ok',
                    'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
                    'filename' => $filename
                );
                echo json_encode($response);
            }else{
                redirect('Excel');
            }
            // log_message("error",print_r($query,true));
            // if($query){
            //     $this->Model_laporan->getexcel($query);
            // }
            
        }else{   
            redirect("auth/logout");
        }

    } 


    function gettelePT(){
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
              $tgl1 = $this->input->post('tgl1');
            }else
            {
                $tgl1 = null;
            }

            $response = $this->Model_inventori->getTelePT($tgl1);
            echo json_encode($response);
            
        }else{   
            redirect("auth/logout");
        }

    } 

    function gettelePU(){
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
              $tgl1 = $this->input->post('tgl1');
            }else
            {
                $tgl1 = null;
            }

            $response = $this->Model_inventori->getTelePU();
            echo json_encode($response);
            
        }else{   
            redirect("auth/logout");
        }

    } 

    public function laporanpodetail()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanpodetail.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 

    public function laporanpodetailX($cek = null)
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $tipe = $this->input->post('tipe');      
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";
            $draw=$_REQUEST['draw'];

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];
            if($tgl1 && $tgl2){
                $bydate = " trs_po_detail.Tgl_PO between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " trs_po_detail.Tgl_PO = '".date('Y-m-d')."'";
            }
            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];
            $length = "";
            if(isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $total=$this->Model_laporan->listDataPODetailAll($bySearch,$byLimit,'',$tipe)->num_rows();
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();
            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = " and (No_PO like '%".$search."%' or Tgl_PO like '%".$search."%' or No_PR like '%".$search."%' or No_Usulan like '%".$search."%' or Prinsipal like '%".$search."%' or Supplier like '%".$search."%' or Produk like '%".$search."%' or Nama_Produk like '%".$search."%' or Satuan like '%".$search."%' or Status_PO like '%".$search."%' or Qty like '%".$search."%' or Qty_PO like '%".$search."%' or Sisa_PO like '%".$search."%' or Bonus like '%".$search."%' or Harga_Beli_Cab like '%".$search."%' or Disc2 like '%".$search."%' or Disc_Deal like '%".$search."%' or Potongan_Cab like '%".$search."%' or Value_PO like '%".$search."%')";
            }
            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            $query=$this->Model_laporan->listDataPODetailAll($bySearch,$byLimit,'',$tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listDataPODetailAll($bySearch,'','',$tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Produk) ? $baris->Produk : "");
                $row[] = (!empty($baris->Nama_Produk) ? $baris->Nama_Produk : "");
                $row[] = (!empty($baris->Satuan) ? $baris->Satuan : "");
                $row[] = (!empty($baris->No_PO) ? $baris->No_PO : "");
                $row[] = (!empty($baris->Tgl_PO) ? $baris->Tgl_PO : "");
                $row[] = (!empty($baris->No_PR) ? $baris->No_PR : "");
                $row[] = (!empty($baris->Status_PO) ? $baris->Status_PO : "");
                $row[] = (!empty($baris->statusGIT) ? $baris->statusGIT : "");
                // $row[] = "<p align='right'>".(!empty($baris->Banyak) ? number_format($baris->Banyak) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Sisa_PO) ? number_format($baris->Sisa_PO) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Harga_Beli_Cab) ? number_format($baris->Harga_Beli_Cab) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc_Deal) ? number_format($baris->Disc_Deal) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Qty * $baris->Harga_Beli_Cab) ? number_format($baris->Qty * $baris->Harga_Beli_Cab) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->val_disc) ? number_format($baris->val_disc) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->val_bonus) ? number_format($baris->val_bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value_PO) ? number_format($baris->Value_PO) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan_Cab) ? number_format($baris->Potongan_Cab) : 0)."</p>";
                $row[] = (!empty($baris->Prinsipal2) ? $baris->Prinsipal2 : "");
                $row[] = (!empty($baris->Supplier) ? $baris->Supplier : "");
                $row[] = (!empty($baris->No_Usulan) ? $baris->No_Usulan : "");
                $row[] = "<p align='right'>".(!empty($baris->GIT) ? number_format($baris->GIT) : 0)."</p>";
                $row[] = (!empty($baris->Keterangan) ? $baris->Keterangan : "");
                $row[] = (!empty($baris->Penjelasan) ? $baris->Penjelasan : "");                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    } 


    public function listlaphna()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content['harga_cabang']= $this->Model_main->cek_harga_cabang();
            $this->twig->display('laporan/laporanharga.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 

    public function listHargaCabang()
    {   

        $params = (object)$this->input->post();
        $keyword = $params->keyword;
        
        $data = "";

        $bySearch = "";
        $bySearch1 = "";
        $byLimit = "";
        $draw=$_REQUEST['draw'];

        // $length=$_REQUEST['length'];
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length = "";
        if(isset($_REQUEST['length'])) {
            if($_REQUEST['length'] != -1){
                $length=$_REQUEST['length'];
            }
        }


        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']['value'];
        $output=array();
        $output['draw']=$draw;
        $total = $this->Model_laporan->listHargaCabang($bySearch,$byLimit)->num_rows();    
        $output['recordsTotal']=$output['recordsFiltered']=$total;
        $output['data']=array();



        $bySearch = " and (Prinsipal like '%".$keyword['s_prinsipal']."%' and Produk like '%".$keyword['s_produk']."%' and Produk_String like '%".$keyword['s_produk_string']."%' and ifnull(Cabang,'') like '%".$keyword['s_cabang']."%')";
            /*Lanjutkan pencarian ke database*/

        if($length != 0 || $length != "")
            $byLimit = " LIMIT ".$start.", ".$length;
        /*Urutkan dari alphabet paling terkahir*/

        $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listHargaCabang($bySearch,'')->num_rows();

        $list = $this->Model_laporan->listHargaCabang($bySearch,$byLimit)->result();
        $data = array();
        $no = 1;
        foreach ($list as $key) {
            $row = array();
            $row[] = $no;
            $row[] = (!empty($key->Prinsipal) ? $key->Prinsipal : "");
            $row[] = (!empty($key->Produk) ? $key->Produk : "");
            $row[] = (!empty($key->Produk_String) ? $key->Produk_String : "");
            $row[] = (!empty($key->Cabang) ? $key->Cabang : "");
            $row[] = (!empty($key->HNA) ? number_format($key->HNA) : "");
            $row[] = (!empty($key->HNA2) ? number_format($key->HNA2) : "");
            $row[] = (!empty($key->Dsc_Cab) ? number_format($key->Dsc_Cab,2) : "");
            $row[] = (!empty($key->Dsc_Pri) ? number_format($key->Dsc_Pri,2) : "");
            $row[] = (!empty($key->Hrg_Beli_Cab) ? number_format($key->Hrg_Beli_Cab) : "");
            $row[] = (!empty($key->Dsc_Beli_Cab) ? number_format($key->Dsc_Beli_Cab,2) : "");
            $data[] = $row;
            $no++;
        }

        $output['data'] = $data;
        echo json_encode($output);
    }


        public function lapenapza()
    {   
        if ( $this->logged ) 
        {
            // $this->content['cabang']= $this->Model_main->Cabang();
            // $this->content['gprins']= $this->Model_laporan->getstokprisipal();
            $this->twig->display('laporan/lapenapza.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 


    public function getenapza()
    {   
        if ($this->logged) 
        {

             log_message('error','masuk iff :');

            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            /*Menagkap semua data yang dikirimkan oleh client*/
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }

            $total = $this->Model_laporan->listenapza('','',$tgl1)->num_rows();

            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            if($search!=""){
            // $bySearch = " AND (WIL like '%".$search."%' or NM_CABANG like '%".$search."%' or KODE_JUAL like '%".$search."%')";
            $bySearch = " ";
            }

            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/

            $query=$this->Model_laporan->listenapza($bySearch, $byLimit,$tgl1)->result();

            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listenapza($bySearch,'',$tgl1)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;


            // log_message('error','tanggal :'.$tgl1);

            $kodeakhir = "";
            foreach ($query as $baris) {

                $queryA=$this->Model_laporan->listestokawals($tgl1,(!empty($baris->Kode) ? $baris->Kode : ""))->result();
                foreach ($queryA as $barisA) {
                    $awal = 0;
                    $awal = (!empty($barisA->unitA) ? $barisA->unitA : 0);
                }

                    if ((!empty($baris->Kode) ? $baris->Kode : "") == $kodeakhir) {
                        $awal = $akhirx;
                    }


                    $akhir= (int)$awal + (int)(!empty($baris->JumlahMasuk) ? $baris->JumlahMasuk : 0) - (int)(!empty($baris->JumlahKeluar) ? $baris->JumlahKeluar : 0);


                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->Tanggal) ? $baris->Tanggal : "");
                // $row[] = (!empty($baris->SaldoAwal) ? $baris->SaldoAwal : "");
                $row[] = $awal;
                $row[] = (!empty($baris->BatchAwal) ? $baris->BatchAwal : "");
                $row[] = (!empty($baris->DokumenMasuk) ? $baris->DokumenMasuk : "");
                $row[] = (!empty($baris->Sumber) ? $baris->Sumber : "");
                $row[] = (!empty($baris->JumlahMasuk) ? $baris->JumlahMasuk : "");
                $row[] = (!empty($baris->BatchMasuk) ? $baris->BatchMasuk : "");
                $row[] = (!empty($baris->DokumenKeluar) ? $baris->DokumenKeluar : "");
                $row[] = (!empty($baris->Tujuan) ? $baris->Tujuan : "");
                $row[] = (!empty($baris->JumlahKeluar) ? $baris->JumlahKeluar : "");
                $row[] = (!empty($baris->BatchKeluar) ? $baris->BatchKeluar : "");
                $row[] = $akhir;
                $row[] = (!empty($baris->BatchAkhir) ? $baris->BatchAkhir : "");
                $row[] = (!empty($baris->expD) ? $baris->expD : "");
                $row[] = (!empty($baris->Kategori) ? $baris->Kategori : "");
                $row[] = (!empty($baris->Kode) ? $baris->Kode : "");

                $kodeakhir = (!empty($baris->Kode) ? $baris->Kode : "");
                $akhirx = $akhir;

                // $row[] = (!empty($baris->WIL) ? $baris->WIL : "");
                // $row[] = (!empty($baris->NM_CABANG) ? $baris->NM_CABANG : "");
                // $row[] = (!empty($baris->KODE_JUAL) ? $baris->KODE_JUAL : "");
                // $row[] = (!empty($baris->NODOKJDI) ? $baris->NODOKJDI : "");
                // $row[] = (!empty($baris->NO_ACU) ? $baris->NO_ACU : "");
                // $row[] = (!empty($baris->KODE_PELANGGAN) ? $baris->KODE_PELANGGAN : "");
                // $row[] = (!empty($baris->KODE_KOTA) ? $baris->KODE_KOTA : "");
                // $row[] = (!empty($baris->KODE_TYPE) ? $baris->KODE_TYPE : "");
                // $row[] = (!empty($baris->KODE_LANG) ? $baris->KODE_LANG : "");
                // $row[] = (!empty($baris->NAMA_LANG) ? $baris->NAMA_LANG : "");
                // $row[] = (!empty($baris->ALAMAT) ? $baris->ALAMAT : "");
                // $row[] = (!empty($baris->JUDUL) ? $baris->JUDUL : "");
                // $row[] = (!empty($baris->KODEPROD) ? $baris->KODEPROD : "");
                // $row[] = (!empty($baris->NAMAPROD) ? $baris->NAMAPROD : "");
                // if($baris->TipeDokumen == 'Retur' or $baris->TipeDokumen == 'CN'){
                //     $row[] = (!empty($baris->UNIT * -1) ? number_format($baris->UNIT * -1): "");
                //     $row[] = (!empty($baris->PRINS * -1) ? number_format($baris->PRINS * -1) : 0);
                //     $row[] = (!empty($baris->BANYAK * -1) ? number_format($baris->BANYAK) * -1 : 0);
                // }else{
                //     $row[] = (!empty($baris->UNIT) ? $baris->UNIT : "");
                //     $row[] = (!empty($baris->PRINS) ? number_format($baris->PRINS) : 0);
                //     $row[] = (!empty($baris->BANYAK) ? number_format($baris->BANYAK) : 0);
                // }
                // $row[] = (!empty($baris->HARGA) ? number_format($baris->HARGA) : 0);
                // $row[] = (!empty($baris->PRSNXTRA) ? number_format($baris->PRSNXTRA,2) : 0);
                // $row[] = (!empty($baris->PRINPXTRA) ? number_format($baris->PRINPXTRA,2) : 0);
                // $row[] = (!empty($baris->TOT1) ? number_format($baris->TOT1,2) : 0);
                // $row[] = (!empty($baris->PPN) ? number_format($baris->PPN,2) : 0);
                // $row[] = (!empty($baris->NILJU) ? number_format($baris->NILJU,2) : 0);
                // $row[] = (!empty($baris->COGS) ? number_format($baris->COGS,2) : 0);
                // $row[] = (!empty($baris->KODESALES) ? $baris->KODESALES :"");
                // $row[] = (!empty($baris->TGLDOK) ? $baris->TGLDOK : "");
                // $row[] = (!empty($baris->TGLEXP) ? $baris->TGLEXP : "");
                // $row[] = (!empty($baris->BATCH) ? $baris->BATCH : "");
                // $row[] = (!empty($baris->Area) ? $baris->Area : "");
                // $row[] = (!empty($baris->TELP) ? $baris->TELP : "");
                // $row[] = (!empty($baris->RAYON) ? $baris->RAYON : "");
                // $row[] = (!empty($baris->PANEL) ? $baris->PANEL : "");
                // $row[] = (!empty($baris->DiscPrins1) ? number_format($baris->DiscPrins1,2) : 0);
                // $row[] = (!empty($baris->DiscPrins2) ? number_format($baris->DiscPrins2,2) : 0);
                // $row[] = (!empty($baris->CashDiskon) ? $baris->CashDiskon : 0);
                // $row[] = (!empty($baris->DiscCabMax) ? number_format($baris->DiscCabMax,2) : 0);
                // $row[] = (!empty($baris->KetDiscCabMax) ? $baris->KetDiscCabMax : "");
                // $row[] = (!empty($baris->DiscPrinsMax) ? number_format($baris->DiscPrinsMax,2) : 0);
                // $row[] = (!empty($baris->KetDiscPrinsMax) ? $baris->KetDiscPrinsMax : "");
                // $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                // $row[] = (!empty($baris->Status) ? $baris->Status : "");
                // $row[] = (!empty($baris->Tipe) ? $baris->Tipe : "");
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function laptriwulan()
    {   
        if ( $this->logged ) 
        {
            // $this->content['cabang']= $this->Model_main->Cabang();
            // $this->content['gprins']= $this->Model_laporan->getstokprisipal();
            $this->twig->display('laporan/laptriwulan.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 


    public function gettriwulan()
    {   
        if ($this->logged) 
        {

             log_message('error','masuk iff :');

            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            /*Menagkap semua data yang dikirimkan oleh client*/
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }

            $total = $this->Model_laporan->listtriwulan('','',$tgl1)->num_rows();

            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            if($search!=""){
            // $bySearch = " AND (WIL like '%".$search."%' or NM_CABANG like '%".$search."%' or KODE_JUAL like '%".$search."%')";
            $bySearch = " ";
            }

            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/

            $query=$this->Model_laporan->listtriwulan($bySearch, $byLimit,$tgl1)->result();

            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listtriwulan($bySearch,'',$tgl1)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;


            // log_message('error','tanggal :'.$tgl1);

            $kodeakhir = "";
            $akhirx = 0;
            $awal = 0;
            foreach ($query as $baris) {

                $queryA=$this->Model_laporan->listestokawals($tgl1,(!empty($baris->Kode) ? $baris->Kode : ""))->result();
                foreach ($queryA as $barisA) {
                    $awal = 0;
                    $awal = (!empty($barisA->unitA) ? $barisA->unitA : 0);
                }

                //     if ((!empty($baris->Kode) ? $baris->Kode : "") == $kodeakhir) {
                //         $awal = $akhirx;
                //     }


                //     $akhir= (int)$awal + (int)(!empty($baris->JumlahMasuk) ? $baris->JumlahMasuk : 0) - (int)(!empty($baris->JumlahKeluar) ? $baris->JumlahKeluar : 0);


                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris->NIE) ? $baris->NIE : (!empty($baris->Kode) ? $baris->Kode : ""));
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->Kemasan) ? $baris->Kemasan : "");
                // $row[] = (!empty($baris->StokAwal) ? $baris->StokAwal : "");
                $row[] = $awal;
                $row[] = 0;
                $row[] = "";
                $row[] = (!empty($baris->MasukPBF) ? $baris->MasukPBF : 0);
                $row[] = (!empty($baris->SumberPemasukan) ? $baris->SumberPemasukan : "");
                $row[] = (!empty($baris->keluarRet) ? $baris->keluarRet : 0);
                $row[] = (!empty($baris->PBF) ? $baris->PBF : 0);
                $row[] = "";
                $row[] = (!empty($baris->RS) ? $baris->RS : 0);
                $row[] = (!empty($baris->AP) ? $baris->AP : 0);
                $row[] = (!empty($baris->PEM) ? $baris->PEM : 0);
                $row[] = (!empty($baris->PKM) ? $baris->PKM : 0);
                $row[] = (!empty($baris->KL) ? $baris->KL : 0);
                $row[] = (!empty($baris->PE) ? $baris->PE : 0);
                $row[] = (!empty($baris->RRET) ? $baris->RRET : 0);
                $row[] = (!empty($baris->EP) ? $baris->EP : 0);
                $row[] = (!empty($baris->HNA) ? $baris->HNA : 0);
                $row[] = (!empty($baris->Kode) ? $baris->Kode : "");
                $row[] = (!empty($baris->Kategori) ? $baris->Kategori : "");
                // $row[] = $awal;
                // $row[] = (!empty($baris->BatchAwal) ? $baris->BatchAwal : "");
                // $row[] = (!empty($baris->DokumenMasuk) ? $baris->DokumenMasuk : "");
                // $row[] = (!empty($baris->Sumber) ? $baris->Sumber : "");
                // $row[] = (!empty($baris->JumlahMasuk) ? $baris->JumlahMasuk : "");
                // $row[] = (!empty($baris->BatchMasuk) ? $baris->BatchMasuk : "");
                // $row[] = (!empty($baris->DokumenKeluar) ? $baris->DokumenKeluar : "");
                // $row[] = (!empty($baris->Tujuan) ? $baris->Tujuan : "");
                // $row[] = (!empty($baris->JumlahKeluar) ? $baris->JumlahKeluar : "");
                // $row[] = (!empty($baris->BatchKeluar) ? $baris->BatchKeluar : "");
                // $row[] = (!empty($akhir) ? $akhir : 0);
                // $row[] = (!empty($baris->BatchAkhir) ? $baris->BatchAkhir : "");
                // $row[] = (!empty($baris->expD) ? $baris->expD : "");
                // $row[] = (!empty($baris->Kategori) ? $baris->Kategori : "");
                // $row[] = (!empty($baris->Kode) ? $baris->Kode : "");

                // $kodeakhir = (!empty($baris->Kode) ? $baris->Kode : "");
                // $akhirx = $akhir;

                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporansalesbypelangganbyprinsipal()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporansalesbypelangganbyprinsipal.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    


    public function datasalesbypelangganbyprinsipal($cek = null)
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";
            $bydateD     = "";

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tipe   = $params['tipe'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];

            if($length == -1){
                $bylimit    = "";
            }else{
                $bylimit    = " LIMIT ".$start.", ".$length;
            }

            if($tgl1 && $tgl2){
                $bydate = " and trs_faktur_detail.TglFaktur between '".$tgl1."' and '".$tgl2."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = " and trs_faktur_detail.TglFaktur = '".date('Y-m-d')."'";
                $bydateD = " and trs_delivery_order_sales_detail.TglDO = '".date('Y-m-d')."'";
            }

            $bysearch   = " and (NoFaktur like '%".$search."%' or NoDO like '%".$search."%' or Pelanggan like '%".$search."%' or NamaPelanggan like '%".$search."%' or Gross like '%".$search."%' or Salesman like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%' or Prinsipal like '%".$search."%')";

            if($cek=='all'){
                $query=$this->Model_laporan->listsalesbypelangganbyprinsipal($bysearch, $bylimit,'',$tipe, $bydate, $bydateD)->result();
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listsalesbypelangganbyprinsipal($bysearch,'','',$tipe, $bydate, $bydateD)->num_rows();
                // log_message('error',print_r($output,true));
            }else{
                $query=$this->Model_laporan->listsalesbypelangganbyprinsipal($bysearch, $bylimit, "Open", $tipe, $bydate, $bydateD)->result();
                $output['recordsTotal']=$output['recordsFiltered']=$this->Model_laporan->listsalesbypelangganbyprinsipal($bysearch,'', "Open", $tipe, $bydate, $bydateD)->num_rows();
                // log_message('error',print_r($output,true));
            }
            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            // if($search!=""){            
            //     if($cek=='all')
            //     else
            // }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->NoDokumen) ? $baris->NoDokumen : "");
                $row[] = (!empty($baris->Tgl) ? $baris->Tgl : "");
                $row[] = (!empty($baris->Prins) ? $baris->Prins : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                if($baris->TipeDokumen == 'Retur'){
                    $row[] = "<p align='right'>".(!empty($baris->Qty * -1) ? number_format($baris->Qty * -1) : 0)."</p>";
                    $row[] = "<p align='right'>".(!empty($baris->Bonus * -1) ? number_format($baris->Bonus * -1) : 0)."</p>";
                }else{
                   $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                   $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>"; 
                }
                $row[] = (!empty($baris->Gross) ? number_format($baris->Gross) : 0);
                $row[] = (!empty($baris->Potongan) ? number_format($baris->Potongan) : 0);
                $row[] = (!empty($baris->Value) ? number_format($baris->Value) : 0);
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporanPTNew()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanPTNew.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }   

    public function listlaporanPTNew()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            // if(isset($params['tgl1'])){
            //     $tgl1 = $this->input->post('tgl1');
            // }
            $tgl1   = $params['tgl1'];
            /*Menagkap semua data yang dikirimkan oleh client*/
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            // $search = "";
            // if (isset($_REQUEST['search']['value'])) {
            //     $search=$_REQUEST['search']['value'];
            // }

            // $total = $this->Model_laporan->listlaporanPTNew('','',$tgl1)->num_rows();

            $output=array();
            $output['draw']=$draw;
            // $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            // if($search!=""){
            // $bySearch = " AND (WIL like '%".$search."%' or NM_CABANG like '%".$search."%' or KODE_JUAL like '%".$search."%' or NODOKJDI like '%".$search."%' or NO_ACU like '%".$search."%' or KODE_PELANGGAN like '%".$search."%' or KODE_KOTA like '%".$search."%' or KODE_TYPE like '%".$search."%' or KODE_LANG like '%".$search."%' or NAMA_LANG like '%".$search."%' or ALAMAT like '%".$search."%' or JUDUL like '%".$search."%' or KODEPROD like '%".$search."%' or NAMAPROD like '%".$search."%' or UNIT like '%".$search."%' or PRINS like '%".$search."%' or BANYAK like '%".$search."%' or HARGA like '%".$search."%' or PRSNXTRA like '%".$search."%' or PRINPXTRA like '%".$search."%' or TOT1 like '%".$search."%' or NILJU like '%".$search."%' or PPN like '%".$search."%' or COGS like '%".$search."%' or KODESALES like '%".$search."%' or TGLDOK like '%".$search."%' or TGLEXP like '%".$search."%' or BATCH like '%".$search."%'  or Area like '%".$search."%' or TELP like '%".$search."%' or RAYON like '%".$search."%' or PANEL like '%".$search."%' or DiscPrins1 like '%".$search."%' or DiscPrins2 like '%".$search."%' or CashDiskon like '%".$search."%' or DiscCabMax like '%".$search."%' or KetDiscCabMax like '%".$search."%' or DiscPrinsMax like '%".$search."%' or KetDiscPrinsMax like '%".$search."%' or NoDO like '%".$search."%' or Status like '%".$search."%' or Tipe like '%".$search."%' or acu2 like '%".$search."%' or NoBPB like '%".$search."%' )";
            // }

            // if($length != 0 || $length != "")
            //     $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            $query=$this->Model_laporan->listlaporanPTNew($bySearch, $byLimit,$tgl1);

            // if($search!=""){            
            //     $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listlaporanPTNew($bySearch,'',$tgl1)->num_rows();
            // }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris['WIL']) ? $baris['WIL'] : "");
                $row[] = (!empty($baris['NM_CABANG']) ? $baris['NM_CABANG'] : "");
                $row[] = (!empty($baris['KODE_JUAL']) ? $baris['KODE_JUAL'] : "");
                $row[] = (!empty($baris['NODOKJDI']) ? $baris['NODOKJDI'] : "");
                $row[] = (!empty($baris['NO_ACU']) ? $baris['NO_ACU'] : "");
                $row[] = (!empty($baris['KODE_PELANGGAN']) ? $baris['KODE_PELANGGAN'] : "");
                $row[] = (!empty($baris['KODE_KOTA']) ? $baris['KODE_KOTA'] : "");
                $row[] = (!empty($baris['KODE_TYPE']) ? $baris['KODE_TYPE'] : "");
                $row[] = (!empty($baris['KODE_LANG']) ? $baris['KODE_LANG'] : "");
                $row[] = (!empty($baris['NAMA_LANG']) ? $baris['NAMA_LANG'] : "");
                $row[] = (!empty($baris['ALAMAT']) ? $baris['ALAMAT'] : "");
                $row[] = (!empty($baris['JUDUL']) ? $baris['JUDUL'] : "");
                $row[] = (!empty($baris['KODEPROD']) ? $baris['KODEPROD'] : "");
                $row[] = (!empty($baris['NAMAPROD']) ? $baris['NAMAPROD'] : "");
                $row[] = (!empty($baris['UNIT']) ? $baris['UNIT'] : "");
                $row[] = (!empty($baris['PRINS']) ? number_format($baris['PRINS']) : 0);
                $row[] = (!empty($baris['BANYAK']) ? number_format($baris['BANYAK']) : 0);
                $row[] = (!empty($baris['HARGA']) ? number_format($baris['HARGA']) : 0);
                $row[] = (!empty($baris['PRSNXTRA']) ? number_format($baris['PRSNXTRA'],2) : 0);
                $row[] = (!empty($baris['PRINPXTRA']) ? number_format($baris['PRINPXTRA'],2) : 0);
                $row[] = (!empty($baris['NILAI_PRINPXTRA']) ? number_format($baris['NILAI_PRINPXTRA'],2) : 0);
                $row[] = (!empty($baris['TOT1']) ? number_format($baris['TOT1'],2) : 0);
                $row[] = (!empty($baris['PPN']) ? number_format($baris['PPN'],2) : 0);
                $row[] = (!empty($baris['NILJU']) ? number_format($baris['NILJU'],2) : 0);
                $row[] = (!empty($baris['COGS']) ? number_format($baris['COGS'],2) : 0);
                $row[] = (!empty($baris['KODESALES']) ? $baris['KODESALES'] :"");
                $row[] = (!empty($baris['TGLDOK']) ? $baris['TGLDOK'] : "");
                $row[] = (!empty($baris['TGLEXP']) ? $baris['TGLEXP'] : "");
                $row[] = (!empty($baris['BATCH']) ? $baris['BATCH'] : "");
                $row[] = (!empty($baris['Area']) ? $baris['Area'] : "");
                $row[] = (!empty($baris['TELP']) ? $baris['TELP'] : "");
                $row[] = (!empty($baris['RAYON']) ? $baris['RAYON'] : "");
                $row[] = (!empty($baris['PANEL']) ? $baris['PANEL'] : "");
                $row[] = (!empty($baris['DiscCab1']) ? number_format($baris['DiscCab1'],2) : 0);
                $row[] = (!empty($baris['DiscCab2']) ? number_format($baris['DiscCab2'],2) : 0);
                $row[] = (!empty($baris['DiscPrins1']) ? number_format($baris['DiscPrins1'],2) : 0);
                $row[] = (!empty($baris['DiscPrins2']) ? number_format($baris['DiscPrins2'],2) : 0);
                $row[] = (!empty($baris['CashDiskon']) ? $baris['CashDiskon'] : 0);
                $row[] = (!empty($baris['DiscCabMax']) ? number_format($baris['DiscCabMax'],2) : 0);
                $row[] = (!empty($baris['KetDiscCabMax']) ? $baris['KetDiscCabMax'] : "");
                $row[] = (!empty($baris['DiscPrinsMax']) ? number_format($baris['DiscPrinsMax'],2) : 0);
                $row[] = (!empty($baris['KetDiscPrinsMax']) ? $baris['KetDiscPrinsMax'] : "");
                $row[] = (!empty($baris['NoDO']) ? $baris['NoDO'] : "");
                $row[] = (!empty($baris['Status']) ? $baris['Status'] : "");
                $row[] = (!empty($baris['Tipe']) ? $baris['Tipe'] : "");
                $row[] = (!empty($baris['acu2']) ? $baris['acu2'] : "");
                $row[] = (!empty($baris['NoBPB']) ? $baris['NoBPB'] : "");
                $row[] = (!empty($baris['NoDocStok']) ? $baris['NoDocStok'] : "");
                $row[] = (!empty($baris['NilaiKlaimDiscPrins']) ? number_format($baris['NilaiKlaimDiscPrins'],2) : 0);
                $row[] = (!empty($baris['UnitCNDN']) ? number_format($baris['UnitCNDN']) : 0);
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporanPTByPrinsipal()
    {   
        if ( $this->logged ) 
        {
            $this->content['prinsipal']= $this->Model_main->Prinsipal();
            $this->twig->display('laporan/laporanPTByPrinsipal.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }   

    public function listlaporanPTByPrinsipal()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            if(isset($params['prinsipal'])){
                $prins = $this->input->post('prinsipal');
            }
            $prinsipal = explode('~',$prins)[0];
            /*Menagkap semua data yang dikirimkan oleh client*/
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            
            $start = "";
            if (isset($_REQUEST['start'])) {
                $start=$_REQUEST['start'];
            }

            /*Keyword yang diketikan oleh user pada field pencarian*/
            // $search = "";
            // if (isset($_REQUEST['search']['value'])) {
            //     $search=$_REQUEST['search']['value'];
            // }

            // $total = $this->Model_laporan->listlaporanPTNew('','',$tgl1)->num_rows();

            $output=array();
            $output['draw']=$draw;
            // $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            // if($search!=""){
            // $bySearch = " AND (WIL like '%".$search."%' or NM_CABANG like '%".$search."%' or KODE_JUAL like '%".$search."%' or NODOKJDI like '%".$search."%' or NO_ACU like '%".$search."%' or KODE_PELANGGAN like '%".$search."%' or KODE_KOTA like '%".$search."%' or KODE_TYPE like '%".$search."%' or KODE_LANG like '%".$search."%' or NAMA_LANG like '%".$search."%' or ALAMAT like '%".$search."%' or JUDUL like '%".$search."%' or KODEPROD like '%".$search."%' or NAMAPROD like '%".$search."%' or UNIT like '%".$search."%' or PRINS like '%".$search."%' or BANYAK like '%".$search."%' or HARGA like '%".$search."%' or PRSNXTRA like '%".$search."%' or PRINPXTRA like '%".$search."%' or TOT1 like '%".$search."%' or NILJU like '%".$search."%' or PPN like '%".$search."%' or COGS like '%".$search."%' or KODESALES like '%".$search."%' or TGLDOK like '%".$search."%' or TGLEXP like '%".$search."%' or BATCH like '%".$search."%'  or Area like '%".$search."%' or TELP like '%".$search."%' or RAYON like '%".$search."%' or PANEL like '%".$search."%' or DiscPrins1 like '%".$search."%' or DiscPrins2 like '%".$search."%' or CashDiskon like '%".$search."%' or DiscCabMax like '%".$search."%' or KetDiscCabMax like '%".$search."%' or DiscPrinsMax like '%".$search."%' or KetDiscPrinsMax like '%".$search."%' or NoDO like '%".$search."%' or Status like '%".$search."%' or Tipe like '%".$search."%' or acu2 like '%".$search."%' or NoBPB like '%".$search."%' )";
            // }

            // if($length != 0 || $length != "")
            //     $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/

            $query=$this->Model_laporan->listlaporanPTByPrins($bySearch, $byLimit,$tgl1,$prinsipal);

            // if($search!=""){            
            //     $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listlaporanPTNew($bySearch,'',$tgl1)->num_rows();
            // }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris['WIL']) ? $baris['WIL'] : "");
                $row[] = (!empty($baris['NM_CABANG']) ? $baris['NM_CABANG'] : "");
                $row[] = (!empty($baris['KODE_JUAL']) ? $baris['KODE_JUAL'] : "");
                $row[] = (!empty($baris['NODOKJDI']) ? $baris['NODOKJDI'] : "");
                $row[] = (!empty($baris['NO_ACU']) ? $baris['NO_ACU'] : "");
                $row[] = (!empty($baris['KODE_PELANGGAN']) ? $baris['KODE_PELANGGAN'] : "");
                $row[] = (!empty($baris['KODE_KOTA']) ? $baris['KODE_KOTA'] : "");
                $row[] = (!empty($baris['KODE_TYPE']) ? $baris['KODE_TYPE'] : "");
                $row[] = (!empty($baris['KODE_LANG']) ? $baris['KODE_LANG'] : "");
                $row[] = (!empty($baris['NAMA_LANG']) ? $baris['NAMA_LANG'] : "");
                $row[] = (!empty($baris['ALAMAT']) ? $baris['ALAMAT'] : "");
                $row[] = (!empty($baris['JUDUL']) ? $baris['JUDUL'] : "");
                $row[] = (!empty($baris['KODEPROD']) ? $baris['KODEPROD'] : "");
                $row[] = (!empty($baris['NAMAPROD']) ? $baris['NAMAPROD'] : "");
                $row[] = (!empty($baris['UNIT']) ? $baris['UNIT'] : "");
                $row[] = (!empty($baris['PRINS']) ? number_format($baris['PRINS']) : 0);
                $row[] = (!empty($baris['BANYAK']) ? number_format($baris['BANYAK']) : 0);
                $row[] = (!empty($baris['HARGA']) ? number_format($baris['HARGA']) : 0);
                $row[] = (!empty($baris['PRSNXTRA']) ? number_format($baris['PRSNXTRA'],2) : 0);
                $row[] = (!empty($baris['PRINPXTRA']) ? number_format($baris['PRINPXTRA'],2) : 0);
                $row[] = (!empty($baris['TOT1']) ? number_format($baris['TOT1'],2) : 0);
                $row[] = (!empty($baris['PPN']) ? number_format($baris['PPN'],2) : 0);
                $row[] = (!empty($baris['NILJU']) ? number_format($baris['NILJU'],2) : 0);
                $row[] = (!empty($baris['COGS']) ? number_format($baris['COGS'],2) : 0);
                $row[] = (!empty($baris['KODESALES']) ? $baris['KODESALES'] :"");
                $row[] = (!empty($baris['TGLDOK']) ? $baris['TGLDOK'] : "");
                $row[] = (!empty($baris['TGLEXP']) ? $baris['TGLEXP'] : "");
                $row[] = (!empty($baris['BATCH']) ? $baris['BATCH'] : "");
                $row[] = (!empty($baris['Area']) ? $baris['Area'] : "");
                $row[] = (!empty($baris['TELP']) ? $baris['TELP'] : "");
                $row[] = (!empty($baris['RAYON']) ? $baris['RAYON'] : "");
                $row[] = (!empty($baris['PANEL']) ? $baris['PANEL'] : "");
                $row[] = (!empty($baris['DiscPrins1']) ? number_format($baris['DiscPrins1'],2) : 0);
                $row[] = (!empty($baris['DiscPrins2']) ? number_format($baris['DiscPrins2'],2) : 0);
                $row[] = (!empty($baris['CashDiskon']) ? $baris['CashDiskon'] : 0);
                $row[] = (!empty($baris['DiscCabMax']) ? number_format($baris['DiscCabMax'],2) : 0);
                $row[] = (!empty($baris['KetDiscCabMax']) ? $baris['KetDiscCabMax'] : "");
                $row[] = (!empty($baris['DiscPrinsMax']) ? number_format($baris['DiscPrinsMax'],2) : 0);
                $row[] = (!empty($baris['KetDiscPrinsMax']) ? $baris['KetDiscPrinsMax'] : "");
                $row[] = (!empty($baris['NoDO']) ? $baris['NoDO'] : "");
                $row[] = (!empty($baris['Status']) ? $baris['Status'] : "");
                $row[] = (!empty($baris['Tipe']) ? $baris['Tipe'] : "");
                $row[] = (!empty($baris['acu2']) ? $baris['acu2'] : "");
                $row[] = (!empty($baris['NoBPB']) ? $baris['NoBPB'] : "");
                $row[] = (!empty($baris['NoDocStok']) ? $baris['NoDocStok'] : "");
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    function getexportPTNew(){
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            if(isset($params['tgl1'])){
                $tgl1 = $this->input->post('tgl1');
            }
            // if(isset($params['cabang'])){
            //     $cabang = $this->input->post('cabang');
            // }
            // if(isset($params['optpusat'])){
            //     $dpusat = $this->input->post('optpusat');
            // }
            // if(isset($params['optcabang'])){
            //     $dcabang = $this->input->post('optcabang');
            // }
            $query=$this->Model_laporan->getexportPTNew($tgl1);
            if(count($query)>0){
            
                $objPHPExcel = new PHPExcel();
                // Set properties
                $objPHPExcel->getProperties()
                      ->setCreator("Wahyu Purnomo") //creator
                        ->setTitle("Programmer - PT. Sapta Saritama");  //file title
     
                $objset = $objPHPExcel->setActiveSheetIndex(0); //inisiasi set object
                $objget = $objPHPExcel->getActiveSheet();  //inisiasi get object
     
                $objget->setTitle('Sample Sheet'); //sheet title
                //Warna header tabel
                $objget->getStyle("A1:AU1")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '92d050')
                        ),
                        'font' => array(
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                //table header
                $cols = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU"); 
                $val = array("WIL","NM_CABANG","KODE_JUAL","NODOKJDI","NO_ACU","KODE_PELANGGAN","KODE_KOTA","KODE_TYPE","KODE_LANG","NAMA_LANG","ALAMAT","JUDUL","KODEPROD","NAMAPROD","UNIT","PRINS","BANYAK","HARGA","PRSNXTRA","PRINPXTRA","NILAI_PRINPXTRA","TOT1","PPN","NILJU","COGS","KODESALES","TGLDOK","TGLEXP","BATCH","Area","TELP","RAYON","TIPE2","DiscPrins1","DiscPrins2","CashDiskon","DiscCabMax","KetDiscCabMax","DiscPrinsMax","KetDiscPrinsMax","NoDO","Status","TipeDokumen","Tipe","Acu2","NoBPB","NoDocStok");

                for ($a=0;$a<47; $a++) 
                {
                    $objset->setCellValue($cols[$a].'1', $val[$a]);
                     
                    // //Setting lebar cell
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25); // WIL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25); // NM_CABANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25); // KODE_JUAL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25); // NODOKJDI
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25); // NO_ACU
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25); // KODE_PELANGGAN
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25); // KODE_KOTA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25); // KODE_TYPE
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25); // KODE_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50); // NAMA_LANG
                    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(100); // ALAMAT
                    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25); // JUDUL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25); // KODEPROD
                    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50); // NAMAPROD
                    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // UNIT
                    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(35); // PRINS
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(25); // BANYAK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25); // HARGA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(25); // PRSNXTRA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(25); // PRINPXTRA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(25); // NILAI_PRINPXTRA
                    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25); // TOT1  //TOT1
                    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(25); // NILJU  //PPN
                    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(25); // PPN  //NILJU
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(25); // COGS
                    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(25); // KODESALES
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(25); // TGLDOK
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(25); // TGLEXP
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(25); // BATCH
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(25); // Area
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(25); // TELP
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(25); // RAYON
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(25); // PANEL
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(25); // DiscPrins1
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(25); // DiscPrins2
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(25); // CashDiskon
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(25); // DiscCabMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(25); // KetDiscCabMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(25); // DiscPrinsMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(25); // KetDiscPrinsMax
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(25); // NoDO
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(25); // Status
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(25); // TipeDokumen
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(25); // Tipe
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AS')->setWidth(25); // ACU2
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AT')->setWidth(25); // BPB
                    $objPHPExcel->getActiveSheet()->getColumnDimension('AU')->setWidth(25); // NODocBatch
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cols[$a].'1')->applyFromArray($style);
                }
                $baris  = 2;
                foreach ($query as $frow){
                    // $objPHPExcel->getActiveSheet()
                    //             ->getStyle('D1:D'.$baris)
                    //             ->getNumberFormat()
                    //             ->setFormatCode('0');
                   //pemanggilan sesuaikan dengan nama kolom tabel
                    $objset->setCellValue("A".$baris, $frow["WIL"]); //membaca data nama
                    $objset->setCellValue("B".$baris, $frow["NM_CABANG"]); //membaca data alamat
                    $objset->setCellValue("C".$baris, $frow["KODE_JUAL"]); //membaca data alamat
                    $objset->setCellValue("D".$baris, $frow["NODOKJDI"]); //membaca data alamat
                    $objset->setCellValue("E".$baris, $frow["NO_ACU"]); //membaca data alamat
                    $objset->setCellValue("F".$baris, $frow["KODE_PELANGGAN"]); //membaca data alamat
                    $objset->setCellValue("G".$baris, $frow["KODE_KOTA"]); //membaca data alamat
                    $objset->setCellValue("H".$baris, $frow["KODE_TYPE"]); //membaca data alamat
                    $objset->setCellValue("I".$baris, $frow["KODE_LANG"]); //membaca data alamat
                    $objset->setCellValue("J".$baris, $frow["NAMA_LANG"]); //membaca data alamat
                    $objset->setCellValue("K".$baris, $frow["ALAMAT"]); //membaca data alamat
                    $objset->setCellValue("L".$baris, $frow["JUDUL"]); //membaca data alamat
                    $objset->setCellValue("M".$baris, $frow["KODEPROD"]); //membaca data alamat
                    $objset->setCellValue("N".$baris, $frow["NAMAPROD"]); //membaca data alamat
                    $objset->setCellValue("O".$baris, $frow["UNIT"]); //membaca data alamat
                    $objset->setCellValue("P".$baris, $frow["PRINS"]); //membaca data alamat
                    $objset->setCellValue("Q".$baris, $frow["BANYAK"]); //membaca data alamat
                    $objset->setCellValue("R".$baris, $frow["HARGA"]); //membaca data alamat
                    $objset->setCellValue("S".$baris, $frow["PRSNXTRA"]); //membaca data alamat
                    $objset->setCellValue("T".$baris, $frow["PRINPXTRA"]); //membaca data alamat
                    $objset->setCellValue("U".$baris, $frow["NILAI_PRINPXTRA"]); //membaca data alamat
                    $objset->setCellValue("V".$baris, $frow["TOT1"]); //membaca data alamat
                    $objset->setCellValue("W".$baris, $frow["PPN"]); //membaca data alamat
                    $objset->setCellValue("X".$baris, $frow["NILJU"]); //membaca data alamat
                    $objset->setCellValue("Y".$baris, $frow["COGS"]); //membaca data alamat
                    $objset->setCellValue("Z".$baris, $frow["KODESALES"]); //membaca data alamat
                    $objset->setCellValue("AA".$baris, $frow["TGLDOK"]); //membaca data alamat
                    $objset->setCellValue("AB".$baris, $frow["TGLEXP"]); //membaca data alamat
                    $objset->setCellValue("AC".$baris, $frow["BATCH"]); //membaca data alamat
                    $objset->setCellValue("AD".$baris, $frow["Area"]); //membaca data alamat
                    $objset->setCellValue("AE".$baris, $frow["TELP"]); //membaca data alamat
                    $objset->setCellValue("AF".$baris, $frow["RAYON"]); //membaca data alamat
                    $objset->setCellValue("AG".$baris, $frow["PANEL"]); //membaca data alamat
                    $objset->setCellValue("AH".$baris, $frow["DiscPrins1"]); //membaca data alamat
                    $objset->setCellValue("AI".$baris, $frow["DiscPrins2"]); //membaca data alamat
                    $objset->setCellValue("AJ".$baris, $frow["CashDiskon"]); //membaca data alamat
                    $objset->setCellValue("AK".$baris, $frow["DiscCabMax"]); //membaca data alamat
                    $objset->setCellValue("AL".$baris, $frow["KetDiscCabMax"]); //membaca data alamat
                    $objset->setCellValue("AM".$baris, $frow["DiscPrinsMax"]); //membaca data alamat
                    $objset->setCellValue("AN".$baris, $frow["KetDiscPrinsMax"]); //membaca data alamat
                    $objset->setCellValue("AO".$baris, $frow["NoDO"]); //membaca data alamat
                    $objset->setCellValue("AP".$baris, $frow["Status"]); //membaca data alamat
                    $objset->setCellValue("AQ".$baris, $frow["TipeDokumen"]); //membaca data alamat
                    $objset->setCellValue("AR".$baris, $frow["Tipe"]); //membaca data alamat
                    $objset->setCellValue("As".$baris, $frow["acu2"]); //membaca data alamat
                    $objset->setCellValue("AT".$baris, $frow["NoBPB"]); //membaca data alamat
                    $objset->setCellValue("AU".$baris, $frow["NoDocStok"]); //membaca data alamat
                    //Set number value                    
                    $baris++;
                }
                $objPHPExcel->getActiveSheet()->getStyle('D2:D'.$baris)->getNumberFormat()->setFormatCode('0');
                $objPHPExcel->getActiveSheet()->getStyle('AN2:AN'.$baris)->getNumberFormat()->setFormatCode('0');
                $objPHPExcel->getActiveSheet()->setTitle('Laporan PT');
                $objPHPExcel->setActiveSheetIndex(0);  
                $filename = urlencode("lappt".date("Y-m-d").".xlsx");  
                // $temp_filename = 'd:/php/'. $filename;   
                // $size = filesize($filename);
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                // header("Content-length: $size");;
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
                header('Cache-Control: max-age=0'); //no cache
                ob_start();
                $objWriter->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();

                $response =  array(
                    'op' => 'ok',
                    'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
                    'filename' => $filename
                );
                echo json_encode($response);
            }else{
                redirect('Excel');
            }
            // log_message("error",print_r($query,true));
            // if($query){
            //     $this->Model_laporan->getexcel($query);
            // }
            
        }else{   
            redirect("auth/logout");
        }

    }

    function updateHarga(){
        $valid = $this->Model_main->updateHarga();

        echo json_encode($valid);

    }

    function cek_harga_pusat(){
        $valid = $this->Model_main->cek_harga_pusat();

        echo json_encode($valid);

    } 

    function kirim_sales_pusat(){
        $valid = $this->Model_main->kirim_sales_pusat();

        echo json_encode($valid);

    }


    function updateProduk(){
        $valid = $this->Model_main->updateProduk();

        echo json_encode($valid);

    }

    public function datastok_day()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/datastok_day.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function listdatastok_day($cek = null)
    {   
        if ($this->logged) 
        {
            $tgl = $this->input->post('tgl');
            $cek = $this->input->post('cek');

            $data = [];

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }

           
            $start=$_REQUEST['start'];

            $search=$_REQUEST['search']["value"];


            $total = $this->Model_laporan->list_invday('','',$cek,$tgl)->num_rows();

           
            $output=array();

           
            $output['draw']=$draw;

            $output['recordsTotal']=$output['recordsFiltered']=$total;

            
            $output['data']=array();

            if($search!=""){
            $bySearch = "AND (KodeProduk like '%".$search."%' or Gudang like '%".$search."%' or NamaPrinsipal like '%".$search."%' or Pabrik like '%".$search."%' or NamaProduk like '%".$search."%' or BatchNo like '%".$search."%' or ExpDate like '%".$search."%' )";
            }

            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            
           
            $query=$this->Model_laporan->list_invday($bySearch, $byLimit,$cek,$tgl)->result_array();

         

            if($search!=""){     
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->list_invday($bySearch,'',$cek,$tgl)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
               
                
                $row[] = $baris['Cabang'];
                $row[] = $baris['Tahun'];
                $row[] = $baris['Bulan'];
                $row[] = $baris['NamaPrinsipal'];
                $row[] = $baris['Pabrik'];
                $row[] = $baris['Gudang'];
                $row[] = $baris['KodeProduk'];
                $row[] = $baris['NamaProduk'];
                $row[] = $baris['BatchNo'];
                $row[] = $baris['ExpDate'];
                $row[] = $baris['NoDokumen'];
                $row[] = date('Y-m-').$tgl;
                $row[] = number_format($baris['UnitCOGS'],2);
                $row[] = "<p align='right'>".(!empty($baris['SAwa'.$tgl]) ? number_format($baris['SAwa'.$tgl]) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris['VAwa'.$tgl]) ? number_format($baris['VAwa'.$tgl]) : 0)."</p>";
               
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    } 
    public function datafakturreginkaso()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/datafakturreginkaso.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    

    public function laporanfakturreginkaso($cek = null)
    {   
        if ($this->logged) 
        {
            $tipe = $this->input->post('tipe');     
            /*Menagkap semua data yang dikirimkan oleh client*/

            /*Sebagai token yang yang dikrimkan oleh client, dan nantinya akan
            server kirimkan balik. Gunanya untuk memastikan bahwa user mengklik paging
            sesuai dengan urutan yang sebenarnya */
            $data = [];

            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];

            /*Jumlah baris yang akan ditampilkan pada setiap page*/
            $length = "";
            if (isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }

            /*Offset yang akan digunakan untuk memberitahu database
            dari baris mana data yang harus ditampilkan untuk masing masing page
            */
            $start=$_REQUEST['start'];

            /*Keyword yang diketikan oleh user pada field pencarian*/
            $search=$_REQUEST['search']["value"];


            /*Menghitung total data didalam database*/
            //if($cek=='all')
            //  $total = $this->Model_faktur->listData('','','',$tipe)->num_rows();
            //else
            //  $total = $this->Model_faktur->listData('','','Open',$tipe)->num_rows();

            if($cek=='all')
                $total = $this->Model_laporan->lapfakturreginkaso('','','',$tipe)->num_rows();
            else
                $total = $this->Model_laporan->lapfakturreginkaso('','','Open',$tipe)->num_rows();

            /*Mempersiapkan array tempat kita akan menampung semua data
            yang nantinya akan server kirimkan ke client*/
            $output=array();

            /*Token yang dikrimkan client, akan dikirim balik ke client*/
            $output['draw']=$draw;

            /*
            $output['recordsTotal'] adalah total data sebelum difilter
            $output['recordsFiltered'] adalah total data ketika difilter
            Biasanya kedua duanya bernilai sama, maka kita assignment 
            keduaduanya dengan nilai dari $total
            */
            $output['recordsTotal']=$output['recordsFiltered']=$total;

            /*disini nantinya akan memuat data yang akan kita tampilkan 
            pada table client*/
            $output['data']=array();


            /*Jika $search mengandung nilai, berarti user sedang telah 
            memasukan keyword didalam filed pencarian*/
            if($search!=""){
            $bySearch = "and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or NoDO like '%".$search."%' or Status like '%".$search."%' or NoSO like '%".$search."%' or Pelanggan like '%".$search."%' or Salesman like '%".$search."%' or Rayon like '%".$search."%' or Status like '%".$search."%' or Acu like '%".$search."%' or CaraBayar like '%".$search."%'  or TOP like '%".$search."%' or TglJtoFaktur like '%".$search."%' or StatusInkaso like '%".$search."%' or TipeDokumen like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
            // $this->db->order_by('name','asc');
            //if($cek=='all')
            //  $query=$this->Model_faktur->listData($bySearch,$byLimit,'',$tipe)->result();
            //else
            //  $query=$this->Model_faktur->listData($bySearch, $byLimit, "Open", $tipe)->result();

            if($cek=='all')
                $query=$this->Model_laporan->lapfakturreginkaso($bySearch,$byLimit,'',$tipe)->result();
            else
                $query=$this->Model_laporan->lapfakturreginkaso($bySearch, $byLimit, "Open", $tipe)->result();

            /*Ketika dalam mode pencarian, berarti kita harus
            'recordsTotal' dan 'recordsFiltered' sesuai dengan jumlah baris
            yang mengandung keyword tertentu
            */
            //if($search!=""){          
            //  if($cek=='all')
            //      $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'','',$tipe)->num_rows();
            //  else
            //      $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_faktur->listData($bySearch,'', "Open", $tipe)->num_rows();
            //}

            if($search!=""){            
                if($cek=='all')
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->lapfakturreginkaso($bySearch,'','',$tipe)->num_rows();
                else
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->lapfakturreginkaso($bySearch,'', "Open", $tipe)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                // $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->StatusInkaso) ? $baris->StatusInkaso : "");
                // $row[] = (!empty($baris->TOP) ? $baris->TOP : "");
                if($baris->Saldo == 0){
                    $umur = floor((strtotime($baris->TglPelunasan) - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                }else{
                    $umur = floor((time() - strtotime($baris->TglFaktur))/(60 * 60 * 24));
                }
                $row[] = $umur;
                $top = $baris->TOP;
                $row[] = (!empty($baris->CaraBayar) ? $baris->CaraBayar : "");
                $tgljto = date('Y-m-d',strtotime("+".$top." day", strtotime($baris->TglFaktur)));
                // $row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
                $row[] = (!empty($tgljto) ? $tgljto : "");
                $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                $row[] = (!empty($baris->NoDO) ? $baris->NoDO : "");
                $row[] = (!empty($baris->alasan_jto) ? $baris->alasan_jto : "");
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporanrelokasidetail()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanrelokasidetail.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 
    public function datarelokasidetail()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();   
            $data       = [];
            $bysearch   = "";
            $bylimit    = "";
            $bydate     = "";
            $draw=$_REQUEST['draw'];

            $draw   = $params['draw']; // draw adalah halaman
            $start  = $params['start'];
            $length = $params['length'];
            $search = $params['search']['value'];
            $tgl1   = $params['tgl1'];
            $tgl2   = $params['tgl2'];
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $bySearch = "";
            $byLimit = "";
            $draw=$_REQUEST['draw'];
            $length = "";
            if(isset($_REQUEST['length'])) {
                if($_REQUEST['length'] != -1){
                    $length=$_REQUEST['length'];
                }
            }
            $start=$_REQUEST['start'];
            $search=$_REQUEST['search']["value"];
            $output=array();
            $output['draw']=$draw;
            $total = $this->Model_laporan->listdatarelokasidetail($bySearch,$byLimit,$bydate)->num_rows();
            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();
            if($search!=""){
            $bySearch = " and (Cabang_Pengirim like '%".$search."%' or Cabang_Penerima like '%".$search."%' or Tgl_kirim like '%".$search."%' or Tgl_terima like '%".$search."%' or Jenis like '%".$search."%' or No_Relokasi like '%".$search."%' or No_Terima like '%".$search."%' or Prinsipal like '%".$search."%' or Status like '%".$search."%' or Produk like '%".$search."%' or NamaProduk like '%".$search."%' or Satuan like '%".$search."%' or Qty like '%".$search."%' or Bonus like '%".$search."%' or Harga like '%".$search."%' or Disc like '%".$search."%' or  BatchNo like '%".$search."%' or ExpDate like '%".$search."%' or Gross like '%".$search."%' or Potongan like '%".$search."%' or Value like '%".$search."%' or PPN like '%".$search."%' or Total like '%".$search."%' or BatchDoc like '%".$search."%' or tgl_reject like '%".$search."%' or Keterangan_reject like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($_REQUEST['length'] != -1){
                $byLimit = " LIMIT ".$start.", ".$length;
            }
            $query=$this->Model_laporan->listdatarelokasidetail($bySearch, $byLimit,$bydate)->result();
            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listdatarelokasidetail($bySearch,'',$bydate)->num_rows();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;
            $tot_Gross = $tot_Potongan = $tot_Value = $tot_Ppn = $tot_Total = 0;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris->Cabang_Pengirim) ? $baris->Cabang_Pengirim : "");
                $row[] = (!empty($baris->Cabang_Penerima) ? $baris->Cabang_Penerima : "");
                $row[] = (!empty($baris->Tgl_kirim) ? $baris->Tgl_kirim : "");
                $row[] = (!empty($baris->Tgl_terima) ? $baris->Tgl_terima : "");
                $row[] = (!empty($baris->Jenis) ? $baris->Jenis : "");
                $row[] = (!empty($baris->No_Relokasi) ? $baris->No_Relokasi : "");
                $row[] = (!empty($baris->No_Terima) ? $baris->No_Terima : "");
                $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = (!empty($baris->Produk) ? $baris->Produk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->Satuan) ? $baris->Satuan : "");
                $row[] = "<p align='right'>".(!empty($baris->Qty) ? number_format($baris->Qty) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Bonus) ? number_format($baris->Bonus) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Harga) ? number_format($baris->Harga) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Disc) ? number_format($baris->Disc) : 0)."</p>";
                $row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
                $row[] = (!empty($baris->ExpDate) ? $baris->ExpDate : "");
                $row[] = (!empty($baris->BatchDoc) ? $baris->BatchDoc : "");
                $row[] = "<p align='right'>".(!empty($baris->Gross) ? number_format($baris->Gross) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Potongan) ? number_format($baris->Potongan) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Value) ? number_format($baris->Value) : 0)."</p>";
                /*$row[] = "<p align='right'>".(!empty($baris->Ppn) ? number_format($baris->Ppn) : 0)."</p>";
                $row[] = "<p align='right'>".(!empty($baris->Total) ? number_format($baris->Total) : 0)."</p>";*/
                $row[] = (!empty($baris->tgl_reject) ? $baris->tgl_reject : "");
                $row[] = (!empty($baris->Keterangan_reject) ? $baris->Keterangan_reject : "");                
                $data[] = $row;
                $i++;

                $tot_Gross += $baris->Gross;
                $tot_Potongan += $baris->Potongan;
                $tot_Value += $baris->Value;
                $tot_Ppn += $baris->Ppn;
                $tot_Total += $baris->Total;
            }
             $data[] = array("","","","","","","","","","","","","","","","","","","","Total",number_format($tot_Gross),number_format($tot_Potongan),number_format($tot_Value)/*,number_format($tot_Ppn),number_format($tot_Total)*/,"","");
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function laporanrelokasiheader1()
    {   
        if ( $this->logged ) 
        {
            $this->twig->display('laporan/laporanrelokasiheader.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function datarelokasiheader()
    {   
        $params = (object)$this->input->post();
        $keyword = $params->keyword;
        $tgl1   = $params->tgl1;
        $tgl2   = $params->tgl2;
        $tipe   = $params->tipe;
        if($tipe == ""){
            $tipe = 'all';
        }
        $data = "";
        $bySearch = "";
        $bySearch1 = "";
        $byLimit = "";
        $draw=$_REQUEST['draw'];
        // $length=$_REQUEST['length'];
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        if($tgl1 && $tgl2){
            $bydate = "between '".$tgl1."' and '".$tgl2."'";
        }else{
            $bydate = "= '".date('Y-m-d')."'";
        }

        $length = "";
        if (isset($_REQUEST['length'])) {
            if($_REQUEST['length'] != -1){
                $length=$_REQUEST['length'];
            }
        }

        $output=array();
        $output['draw']=$draw;
        $total = $this->Model_laporan->listdatarelokasiheader($bySearch,$tipe,'',$bydate)->num_rows();
        $output=array();
        $output['draw']=$draw;
        $output['recordsTotal']=$output['recordsFiltered']=$total;
        $output['data']=array();
        if($keyword['s_cabangkirim'] != "" or $keyword['s_cabangterima'] != "" or $keyword['s_tglkirim'] != "" or $keyword['s_tglterima'] != "" or $keyword['s_tipe'] != "" or $keyword['s_nokirim'] != "" or $keyword['s_noterima'] != "" or $keyword['s_status'] != "" or $keyword['s_keterangan'] != ""){
                $bySearch = " and (ifnull(Cabang_Pengirim,'') like '%".$keyword['s_cabangkirim']."%' and ifnull(Cabang_Penerima,'') like '%".$keyword['s_cabangterima']."%' and ifnull(Tgl_kirim,'') like '%".$keyword['s_tglkirim']."%' and ifnull(Tgl_terima,'') like '%".$keyword['s_tglterima']."%' and ifnull(Jenis,'') like '%".$keyword['s_tipe']."%' and  ifnull(No_Relokasi,'') like '%".$keyword['s_nokirim']."%' and IFNULL(No_Terima,'') like '%".$keyword['s_noterima']."%' and  ifnull(Status_kiriman,'') like '%".$keyword['s_status']."%' and ifnull(Keterangan,'') like '%".$keyword['s_keterangan']."%')";
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporan->listdatarelokasiheader($bySearch,$tipe,'',$bydate)->num_rows();
            }
        
        if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
        $list = $this->Model_laporan->listdatarelokasiheader($bySearch,$tipe,$byLimit,$bydate)->result();
        $data = array();
        $x = 0;
        $tot_Gross = $tot_Potongan = $tot_Value = $tot_Ppn = $tot_Total = 0;
        foreach ($list as $key) {
            $x++;
            $row = array();
            $row[] = $x;
            $row[] = (!empty($key->Cabang_Pengirim) ? $key->Cabang_Pengirim : "");
            $row[] = (!empty($key->Cabang_Penerima) ? $key->Cabang_Penerima : "");
            $row[] = (!empty($key->Tgl_kirim) ? $key->Tgl_kirim : "");
            $row[] = (!empty($key->Tgl_terima) ? $key->Tgl_terima : "");
            $row[] = (!empty($key->Jenis) ? $key->Jenis : "");
            $row[] = (!empty($key->No_Relokasi) ? $key->No_Relokasi : "");
            $row[] = (!empty($key->No_Terima) ? $key->No_Terima : "");
            $row[] = (!empty($key->Status_kiriman) ? $key->Status_kiriman : "");
            $row[] = (!empty($key->Gross) ? number_format($key->Gross) : "");
            $row[] = (!empty($key->Potongan) ? number_format($key->Potongan) : "");
            $row[] = (!empty($key->Value) ? number_format($key->Value) : "");     
            /*$row[] = (!empty($key->Ppn) ? number_format($key->Ppn)  : "");
            $row[] = (!empty($key->Total) ? number_format($key->Total)  : "");*/
            $row[] = (!empty($key->Keterangan) ? $key->Keterangan  : "");             
            $data[] = $row;

            $tot_Gross += $key->Gross;
            $tot_Potongan += $key->Potongan;
            $tot_Value += $key->Value;
            $tot_Ppn += $key->Ppn;
            $tot_Total += $key->Total;
        }
         /*$data[] = array("","","","","","","","","Total",number_format($tot_Gross),number_format($tot_Potongan),number_format($tot_Value),number_format($tot_Ppn),number_format($tot_Total),"");*/

        $output = array(
                        "data" => $data,
                );
        //output to json format
        echo json_encode($output);
    }  
    public function laporanrelokasiheader()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanrelokasiheader1.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function datarelokasiheader1()
    {
        if ($this->logged) 
        {
            $tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            $tipe = $_POST['tipe'];
            if($tipe == ""){
                $tipe = 'all';
            }
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $list = $this->Model_laporan->listdatarelokasiheader1($tipe,$bydate);
            $return['Result'] = $list;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }
    public function laporanbpbheader()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanbpbheader.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function databpbheader()
    {
        if ($this->logged) 
        {
            $tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $t1    = strtotime($tgl1);
            $t2    = strtotime($tgl2);
            $tgl1 = date('Y-m-d', $t1);
            $tgl2 = date('Y-m-d', $t2);
            if($tgl1 && $tgl2){
                $bydate = "between '".$tgl1."' and '".$tgl2."'";
            }else{
                $bydate = "= '".date('Y-m-d')."'";
            }
            $list = $this->Model_laporan->listdatabpbheader($bydate);
            $return['Result'] = $list;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }
}