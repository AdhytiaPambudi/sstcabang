<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CLaporan_all extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model_main');
        $this->load->model('laporan/Model_laporanAll');
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

    public function laporanpiutangBM()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanpiutangBM.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }    

    public function getdatapiutangBM()
    {
        if ($this->logged) 
        {
            $tglDoc = $_POST['tgldoc'];
            $cabang = $_POST['cabang'];
            $timestamp    = strtotime($tglDoc);
            $start_date  = date('Y-m-01', $timestamp);
            $end_date  = date('Y-m-d', $timestamp);
            $list = $this->Model_laporanAll->getdatapiutangBM($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }

    public function laporansalesharian()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporansalesharian.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getdatasalesharian()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tglmulai'];
            $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getdatasalesharian($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }   


    public function laporanpiutangpelanggan()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanpiutangpelanggan.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function laporanpl()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanpl.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    
    public function getnilaipl()
    {
        if ($this->logged) 
        {
            $tanggal = $_POST['tanggal'];
            $cabang = $_POST['cabang'];
            $mon  = date('m', strtotime($tanggal));
            $yer  = date('Y', strtotime($tanggal));
            $list = $this->Model_laporanAll->getnilaipl($mon,$yer,$cabang);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }  

    public function getdatapiutangpelanggan()
    {
        if ($this->logged) 
        {
            // $tglmulai = $_POST['tglmulai'];
            // $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $pelanggan = $_POST['pelanggan'];
            // $start_date  = date('Y-m-d', strtotime($tglmulai));
            // $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getdatapiutangpelanggan($cabang,$pelanggan);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }   
    public function listpelangganAll()
    {
        if ($this->logged) 
        {
            $list = $this->Model_main->Pelanggan();
            echo json_encode($list);
        }

    }

    public function laporansalesbyprinsipal()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporansalesbyprinsipal.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function listprinsipalAll()
    {
        if ($this->logged) 
        {
            $list = $this->Model_laporanAll->Prinsipal2();
            echo json_encode($list);
        }

    }

    public function listprinsipalbycode()
    {
        if ($this->logged) 
        {
            $prinsipal = $_POST['prinsipal'];
            $list = $this->Model_laporanAll->Prinsipal2bycode($prinsipal);
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }  

    public function getdatasalesbyprinsipal()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tglmulai'];
            $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $prinsipal = $_POST['prinsipal'];
            $prinsipal2 = $_POST['prinsipal2'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getdatasalesbyprinsipal($start_date,$end_date,$cabang,$prinsipal,$prinsipal2);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }    

    public function laporanjumlahfaktur()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanjumlahfaktur.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }   

    public function getjumlahfaktur()
    {
        if ($this->logged) 
        {
            $tglDoc = $_POST['tgldoc'];
            $cabang = $_POST['cabang'];
            $timestamp    = strtotime($tglDoc);
            $start_date  = date('Y-m-01', $timestamp);
            $end_date  = date('Y-m-d', $timestamp);
            $list = $this->Model_laporanAll->getjumlahfaktur($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }

    public function laporansalesotharian()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporansalesotharian.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getdatasalesot()
    {
        if ($this->logged) 
        {
            $tglDoc = $_POST['tgldoc'];
            $cabang = $_POST['cabang'];
            $timestamp    = strtotime($tglDoc);
            $start_date  = date('Y-m-01', $timestamp);
            $end_date  = date('Y-m-d', $timestamp);
            $list = $this->Model_laporanAll->getdatasalesot($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }

    public function laporanDOharian()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanDOharian.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getdataDOharian()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tglmulai'];
            $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getdataDOharian($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }  

    public function datapelunasanperiode()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/datapelunasanperiode.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    } 

    public function getpelunasanperiode()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";
            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            if(isset($params['tgl1']) && isset($params['tgl2'])){
                $tgl1 = $this->input->post('tgl1');
                $tgl2 = $this->input->post('tgl2');
                // $byperiode = " and TglFaktur between '".$tgl1."' and '".$tgl2."' ";
            }
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
           
                $total = $this->Model_laporanAll->getpelunasanperiode('','',$tipe, $tgl1,$tgl2)->num_rows();
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
            $bySearch = " and (xx.NomorFaktur like '%".$search."%' or xx.NoDIH like '%".$search."%' or xx.Area like '%".$search."%' or xx.KodePelanggan like '%".$search."%' or xx.NamaPelanggan like '%".$search."%' or xx.KodePenagih like '%".$search."%' or xx.NamaPenagih like '%".$search."%' or xx.KodeSalesman like '%".$search."%' or xx.NamaSalesman like '%".$search."%' or xx.TglFaktur like '%".$search."%' or xx.TglPelunasan like '%".$search."%' or xx.Status like '%".$search."%' or xx.ValueFaktur like '%".$search."%' or xx.Cicilan like '%".$search."%' or xx.SaldoFaktur like '%".$search."%' or xx.Giro like '%".$search."%' or xx.TglPelunasan like '%".$search."%' or xx.TglFaktur like '%".$search."%' or xx.TglGiroCair like '%".$search."%' or xx.bank like '%".$search."%' or xx.status_titipan like '%".$search."%' or xx.no_titipan like '%".$search."%' or xx.status_tambahan like '%".$search."%' or b.acu2 like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
        
            $query=$this->Model_laporanAll->getpelunasanperiode($bySearch,$byLimit,$tipe,$tgl1,$tgl2)->result();
            // log_message("error",print_r($query,true));
           
            if($search!=""){            
               
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporanAll->getpelunasanperiode($bySearch,'',$tipe,$tgl1,$tgl2)->num_rows();
              
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NomorFaktur."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                // $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                 $row[] = (!empty($baris->TglPelunasan) ? $baris->TglPelunasan : "");
                 // $row[] = (!empty($baris->UmurLunas) ? $baris->UmurLunas : "");
                 $row[] = (!empty($baris->NoDIH) ? $baris->NoDIH : "");
                 $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                 $row[] = (!empty($baris->NomorFaktur) ? $baris->NomorFaktur : "");
                 $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                 $f = new DateTime($baris->TglFaktur);
                 $p = new DateTime($baris->TglPelunasan);
                 $s = $f->diff($p);
                 $row[] = (!empty($baris->UmurFaktur) ? $baris->UmurFaktur : $s->days);
                 $row[] = (!empty($baris->KodePenagih) ? $baris->KodePenagih : "");
                 $row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
                 $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                 $row[] = (!empty($baris->KodeSalesman) ? $baris->KodeSalesman : "");
                 $row[] = (!empty($baris->acu2) ? $baris->acu2 : "");
                 $row[] = "<p align='right'>".(!empty(number_format($baris->ValueFaktur)) ? number_format($baris->ValueFaktur) : 0)."</p>";
                 // $row[] = (!empty($baris->Cicilan) ? $baris->Cicilan : "");
                 $row[] = "<p align='right'>".(!empty(number_format($baris->SaldoFaktur)) ? number_format($baris->SaldoFaktur) : 0)."</p>";
                 $row[] = (!empty($baris->TipePelunasan) ? $baris->TipePelunasan : "");
                 $row[] = (!empty($baris->Status) ? $baris->Status : "");
                if($baris->TipeDokumen == 'Retur' or $baris->TipeDokumen == 'CN'){
                    if($baris->Status != 'Batal'){
                        if($baris->ValuePelunasan < 0){
                            $row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
                            $row[] =(!empty(number_format($baris->value_pembulatan)) ? number_format($baris->value_pembulatan) : 0);
                            $row[] = (!empty(number_format($baris->materai)) ? number_format($baris->materai) : 0);
                            $row[] = (!empty(number_format($baris->value_transfer)) ? number_format($baris->value_transfer) : 0);
                            $row[]=number_format($baris->ValuePelunasan+$baris->value_pembulatan+$baris->materai+$baris->value_transfer);
                        }else{
                            $row[] = (!empty(number_format($baris->ValuePelunasan*-1)) ? number_format($baris->ValuePelunasan*-1) : 0);
                            $row[] =(!empty(number_format($baris->value_pembulatan*-1)) ? number_format($baris->value_pembulatan*-1) : 0);
                            $row[] = (!empty(number_format($baris->materai*-1)) ? number_format($baris->materai*-1) : 0);
                            $row[] = (!empty(number_format($baris->value_transfer*-1)) ? number_format($baris->value_transfer*-1) : 0);
                            $row[]=number_format(($baris->ValuePelunasan+$baris->value_pembulatan+$baris->materai+$baris->value_transfer) * -1);
                        }   
                     }else{
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                     }
                    
                 }else{
                    if($baris->Status != 'Batal'){
                       $row[] = (!empty(number_format($baris->ValuePelunasan)) ? number_format($baris->ValuePelunasan) : 0);
                       $row[] =(!empty(number_format($baris->value_pembulatan)) ? number_format($baris->value_pembulatan) : 0);
                        $row[] = (!empty(number_format($baris->materai)) ? number_format($baris->materai) : 0);
                        $row[] = (!empty(number_format($baris->value_transfer)) ? number_format($baris->value_transfer) : 0);
                        $row[]=number_format($baris->ValuePelunasan+$baris->value_pembulatan+$baris->materai+$baris->value_transfer);
                    }else{
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                        $row[] = 0;
                    }
                    
                 }
                $row[] = (!empty($baris->Giro) ? $baris->Giro : "");
                 $row[] = (!empty($baris->TglGiroCair) ? $baris->TglGiroCair : "");
                $row[] = (!empty($baris->bank) ? $baris->bank : "");
                $row[] = (!empty($baris->keterangan) ? $baris->keterangan : "");

                if ($baris->keterangan == "Khusus") {
                    $tipe = "Khusus";
                }else{
                    $tipe = "Reguler";
                }

                $row[] = $tipe;
                 
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

    public function laporanefaktur()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanefaktur.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getefaktur()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tgldoc'];
            $tglselesai = $_POST['tgldoc1'];
            $cabang = $_POST['cabang'];
            $tipe = $_POST['tipe'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            if($tipe == 'Faktur'){
                $list = $this->Model_laporanAll->getefaktur($start_date,$end_date,$cabang);
            }else{
                $list = $this->Model_laporanAll->geteretur($start_date,$end_date,$cabang);
            }
            
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }  

    public function laporanEC()
    {   
        if ( $this->logged ) 
        {
            log_message("error","kesini");
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanEC.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getEC()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tgldoc'];
            $tglselesai = $_POST['tgldoc1'];
            $cabang = $_POST['cabang'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getEC($start_date,$end_date,$cabang);
            // $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }  

    public function laporanDOTracking()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/datakartupiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function listfakturAll()
    {
        if ($this->logged) 
        {
            $cabang = $_POST['cabang'];
            $pelanggan = $_POST['pelanggan'];
            $list = $this->Model_laporanAll->listfakturAll($cabang,$pelanggan);
            echo json_encode($list);
        }

    }

    public function getkartupiutang()
    {   
        if ( $this->logged ) 
        {
            $cabang = $_POST['cabang'];
            $pelanggan = $_POST['pelanggan'];
            $NoDO = $_POST['faktur'];
            $list = $this->Model_laporanAll->getkartupiutang($cabang,$pelanggan,$NoDO);
            // $return['Result'] = $list;
            echo json_encode($list);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function laporanlistDoTracking()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanmutasipiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getmutasipiutang()
    {   
        if ( $this->logged ) 
        {
            $cabang = $_POST['cabang'];
            $tglDoc = $_POST['tgldoc'];
            $timestamp    = strtotime($tglDoc);
            $tgl = date('Y-m-d',$timestamp);
            $xpld = explode("-",$cabang);
            $cabang = $xpld[0];
            $list = $this->Model_laporanAll->getmutasipiutang($cabang,$tgl);
            // $return['Result'] = $list;
            echo json_encode($list);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function laporanmutasiprpo()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanmutasiprpo.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getmutasiprpoperiode()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";
            // if(isset($params['tipe'])){
            //     $tipe = $this->input->post('tipe');
            // }
            if(isset($params['tgl1']) && isset($params['tgl2'])){
                $tgl1 = $this->input->post('tgl1');
                $tgl2 = $this->input->post('tgl2');
                // $byperiode = " and TglFaktur between '".$tgl1."' and '".$tgl2."' ";
            }
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
           
                $total = $this->Model_laporanAll->getmutasiprpoperiode('','', $tgl1,$tgl2)->num_rows();
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
            $bySearch = " and (Cabang like '%".$search."%' or trs_usulan_beli_header.No_Usulan like '%".$search."%' or Prinsipal like '%".$search."%' or Status_Usulan like '%".$search."%' or No_PR like '%".$search."%' or Status_PR like '%".$search."%' or No_PO like '%".$search."%' or Status_PO like '%".$search."%' or NoBPB like '%".$search."%' or StatusBPB like '%".$search."%' or NoDOBeli like '%".$search."%' or StatusDOBeli like '%".$search."%')";
            }


            /*Lanjutkan pencarian ke database*/
            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;
            /*Urutkan dari alphabet paling terkahir*/
        
            $query=$this->Model_laporanAll->getmutasiprpoperiode($bySearch,$byLimit,$tgl1,$tgl2)->result();
            // log_message("error",print_r($query,true));
           
            if($search!=""){            
               
                    $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporanAll->getmutasiprpoperiode($bySearch,'',$tgl1,$tgl2)->num_rows();
              
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;

            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                // $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NomorFaktur."'".')"><i class="fa fa-eye"></i> Lihat</button>';
                 // $row[] = (!empty($baris->Cabang) ? $baris->Cabang : "");
                 $row[] = (!empty($baris->Prinsipal) ? $baris->Prinsipal : "");
                 $row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->No_Usulan."'".','."'Usulan'".')"><i class="fa fa-eye"></i> View</button>';
                 $row[] = (!empty($baris->No_Usulan) ? $baris->No_Usulan : "");
                 $row[] = (!empty($baris->added_time) ? $baris->added_time : "");
                 $row[] = (!empty($baris->Status_Usulan) ? $baris->Status_Usulan : "");
                 $row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->No_PR."'".','."'pr'".')"><i class="fa fa-eye"></i> View</button>';
                 $row[] = (!empty($baris->No_PR) ? $baris->No_PR : "");
                 $row[] = (!empty($baris->Time_PR) ? $baris->Time_PR : "");
                 $row[] = (!empty($baris->Status_PR) ? $baris->Status_PR : ""/*$s->days*/);
                 $row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->No_PO."'".','."'po'".')"><i class="fa fa-eye"></i> View</button>';
                 $row[] = (!empty($baris->No_PO) ? $baris->No_PO : "");
                 $row[] = (!empty($baris->Time_PO) ? $baris->Time_PO : "");
                 $row[] = (!empty($baris->Status_PO) ? $baris->Status_PO : "");
                 $row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoBPB."'".','."'bpb'".')"><i class="fa fa-eye"></i> View</button>';
                 $row[] = (!empty($baris->NoBPB) ? $baris->NoBPB : "");
                 $row[] = (!empty($baris->TglBPB) ? $baris->TglBPB : "");
                 $row[] = (!empty($baris->StatusBPB) ? $baris->StatusBPB : "");
                 $row[] = '<button type="button" class="btn-sm btn-info" href="javascript:void(0)" title="Lihat" onclick="view('."'".$baris->NoDOBeli."'".','."'dobeli'".')"><i class="fa fa-eye"></i> View</button>';
                 $row[] = (!empty($baris->NoDOBeli) ? $baris->NoDOBeli : "");
                 $row[] = (!empty($baris->TglDOBeli) ? $baris->TglDOBeli : "");
                 $row[] = (!empty($baris->StatusDOBeli) ? $baris->StatusDOBeli : "");
                 
                 
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
    public function listDataDetailtransaksi()
    {   
        if ( $this->logged ) 
        {
            $no = $_POST['no'];
            $jenis = $_POST['jenis'];
            $list = $this->Model_laporanAll->listDataDetailtransaksi($no,$jenis);
            echo json_encode($list);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function laporanomsetsalesharian()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanomsetsalesharian.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function laporanomsetsalesharianprinsipal()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanomsetsalesharianprinsipal.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getomsetsalesharian()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tglmulai'];
            $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getomsetsalesharian($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }   

    public function getomsetsalesharianprinsipal()
    {
        if ($this->logged) 
        {
            $tglmulai = $_POST['tglmulai'];
            $tglselesai = $_POST['tglselesai'];
            $cabang = $_POST['cabang'];
            $start_date  = date('Y-m-d', strtotime($tglmulai));
            $end_date  = date('Y-m-d', strtotime($tglselesai));
            $list = $this->Model_laporanAll->getomsetsalesharianprinsipal($start_date,$end_date,$cabang);
            $return['Result'] = $list;
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }   
    public function laporanefaktur2()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/laporanefaktur2.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function listFakturRetur()
    {
        if ($this->logged) 
        {
            $cabang = $_POST['cabang'];
            $tipe = $_POST['tipe'];
            $list = $this->Model_laporanAll->getlistfaktur($cabang,$tipe);
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    }    

    public function getefakturbyNo()
    {
        if ($this->logged) 
        {
            $cabang = $_POST['cabang'];
            $tipe = $_POST['tipe'];
            $NoFaktur = $_POST['NoFaktur'];
            if($tipe == 'Faktur'){
                $list = $this->Model_laporanAll->getefakturbyNo($cabang,$NoFaktur);
            }else{
                $list = $this->Model_laporanAll->getereturbyNO($cabang,$NoFaktur);
            }
            
            // $return['Result'] = $list;
            
            echo json_encode($list);
        
        }
        else{
            redirect("auth/logout");
        }   
    } 
    public function databpbfaktur()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('laporan/databpbfaktur.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function getbpbfaktur()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";
            if(isset($params['tipe'])){
                $tipe = $this->input->post('tipe');
            }
            if(isset($params['tgl1']) && isset($params['tgl2'])){
                $tgl1 = $this->input->post('tgl1');
                $tgl2 = $this->input->post('tgl2');
            }
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }
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
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }
        
            $query=$this->Model_laporanAll->getbpbfaktur($bySearch,$byLimit,$tipe,$tgl1,$tgl2);

            $x = 0;
            $i = 0;
            $countCetak = 1;
            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = (!empty($baris['NoDokumen']) ? $baris['NoDokumen'] : "");
                $row[] = (!empty($baris['NoPO']) ? $baris['NoPO'] : "");
                $row[] = (!empty($baris['TglDokumen']) ? $baris['TglDokumen'] : "");
                $row[] = (!empty($baris['Prinsipal']) ? $baris['Prinsipal'] : "");
                $row[] = (!empty($baris['Produk']) ? $baris['Produk'] : "");
                $row[] = (!empty($baris['NamaProduk']) ? $baris['NamaProduk'] : "");
                $row[] = (!empty($baris['Bonus']) ? $baris['Bonus'] : "");
                $row[] = (!empty($baris['BatchNo']) ? $baris['BatchNo'] : "");
                $row[] = (!empty($baris['NoDO']) ? $baris['NoDO'] : "");
                $row[] = (!empty($baris['TglDO']) ? $baris['TglDO'] : "");
                $row[] = (!empty($baris['TipeDokumen']) ? $baris['TipeDokumen'] : "");
                $row[] = (!empty($baris['Acu']) ? $baris['Acu'] : "");
                $row[] = (!empty($baris['QtyDO']) ? $baris['QtyDO'] : "");
                $row[] = (!empty($baris['BonusDO']) ? $baris['BonusDO'] : "");
                $row[] = (!empty($baris['BatchDO']) ? $baris['BatchDO'] : "");
                $row[] = (!empty($baris['BatchDoc']) ? $baris['BatchDoc'] : "");
                $row[] = (!empty($baris['Tipe']) ? $baris['Tipe'] : "");
                $data[] = $row;
                $i++;
              }
              $output['data'] = $data;
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function FixCOGS()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->twig->display('pembelian/FixCOGS.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }
    public function listdatcogsfaktur()
    {
        if ($this->logged) 
        {
            $params = $this->input->post();
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }
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
            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }
            if($search!=""){
            $bySearch = " and (NoFaktur like '%".$search."%' or TglFaktur like '%".$search."%' or TipeDokumen like '%".$search."%' or Acu like '%".$search."%' or KodeProduk like '%".$search."%' or NamaProduk like '%".$search."%' or  BatchNo like '%".$search."%' or NoBPB like '%".$search."%')";
            }
        
            $query=$this->Model_laporanAll->listdatcogsfaktur($bySearch);

            $x = 0;
            $i = 0;
            $countCetak = 1;
            foreach ($query as $baris) {
                $x++;
                $row = array();
                $row[] = $x;
                $row[] = '<a class="btn btn-sm btn-success" title="Fix COGS" onclick="fix('."'".$baris->NoFaktur."'".','."'".$baris->TipeDokumen."'".','."'".$baris->Acu."'".','."'".$baris->KodeProduk."'".','."'".$baris->BatchNo."'".','."'".$baris->NoBPB."'".')"><i class="fa fa-eye"></i>Fix COGS</a>';
                $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                $row[] = (!empty($baris->TipeDokumen) ? $baris->TipeDokumen : "");
                $row[] = (!empty($baris->Acu) ? $baris->Acu : "");
                $row[] = (!empty($baris->KodeProduk) ? $baris->KodeProduk : "");
                $row[] = (!empty($baris->NamaProduk) ? $baris->NamaProduk : "");
                $row[] = (!empty($baris->BatchNo) ? $baris->BatchNo : "");
                $row[] = (!empty($baris->NoBPB) ? $baris->NoBPB : "");
                $row[] = (!empty($baris->COGS) ? $baris->COGS : "");
                $row[] = (!empty($baris->TotalCOGS) ? $baris->TotalCOGS : "");
                $data[] = $row;
                $i++;
              }
              $output['data'] = $data;
            echo json_encode($output);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }    
    public function ProsesFixDataCOGS()
    {          
        if ($this->logged) 
        {  
            $No      = $_POST['No'];
            $tipeDok = $_POST['tipeDok'];
            $Acu     = $_POST['Acu'];
            $Kode    = $_POST['Kode'];
            $BatchNo = $_POST['BatchNo'];
            $BatchDoc= $_POST['BatchDoc']; 
            $valid   = $this->Model_laporanAll->ProsesFixDataCOGS($No,$tipeDok,$Acu,$Kode,$BatchNo,$BatchDoc);
            echo json_encode($valid);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }
    public function ProsesFixDataCOGSAll()
    {          
        if ($this->logged) 
        {  
            $query=$this->Model_laporanAll->listdatcogsfakturAll();
            $valid=true;
            foreach ($query as $list) {
                $No      = $list->NoFaktur;
                $tipeDok = $list->TipeDokumen;
                $Acu     = $list->Acu;
                $Kode    = $list->KodeProduk;
                $BatchNo = $list->BatchNo;
                $BatchDoc= $list->NoBPB; 
                log_message("error",print_r($No,true));
                $valid   = $this->Model_laporanAll->ProsesFixDataCOGS($No,$tipeDok,$Acu,$Kode,$BatchNo,$BatchDoc);

            }
            echo json_encode($valid);
        }
        else 
        {   
            redirect("auth/logout");
        }
    } 

    public function MutasiPiutang()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content['pelanggan']= $this->Model_main->Pelanggan();
            $this->twig->display('laporan/MutasiPiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function listlaporanRekapPiutang()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            $tgl1 = $this->input->post('tgl1');
            $tgl2 = $this->input->post('tgl2');
            $pelanggan = $this->input->post('pelanggan');
            /*
            $data = "";

            $bySearch = "";
            $byLimit = "";
            $draw = "";
            if (isset($_REQUEST['draw'])) {
                $draw=$_REQUEST['draw'];
            }

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

            $search = "";
            if (isset($_REQUEST['search']['value'])) {
                $search=$_REQUEST['search']['value'];
            }

            $total = $this->Model_laporanAll->listlaporanRekapPiutang('','',$tgl1,$tgl2,$pelanggan)->num_rows();

            $output=array();
            $output['draw']=$draw;
            $output['recordsTotal']=$output['recordsFiltered']=$total;
            $output['data']=array();

            if($search!=""){
            $bySearch = " AND (Kode like '%".$search."%' )";
            }

            if($length != 0 || $length != "")
                $byLimit = " LIMIT ".$start.", ".$length;

            $query=$this->Model_laporanAll->listlaporanRekapPiutang($bySearch, $byLimit,$tgl1,$tgl2,$pelanggan)->result();

            if($search!=""){            
                $output['recordsTotal']=$output['recordsFiltered'] = $this->Model_laporanAll->listlaporanRekapPiutang($bySearch,'',$tgl1,$tgl2,$pelanggan)->num_rows();
            }*/

            $query=$this->Model_laporanAll->listlaporanRekapPiutang($tgl1,$tgl2,$pelanggan)->result();

            $x = 0;
            $i = 0;
            $countCetak = 1;
            
          
            foreach ($query as $baris) {
                $row = array();
                $x++;
                $row[] = $x;
                $row[] = (!empty($baris->Kode) ? $baris->Kode : "");
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->Saldo_awal) ? number_format($baris->Saldo_awal,2) : 0); 
                $row[] = (!empty($baris->debet) ? number_format($baris->debet,2) : 0); 
                $row[] = (!empty($baris->cash) ? number_format($baris->cash,2) : 0); 
                $row[] = (!empty($baris->transfer) ? number_format($baris->transfer,2) : 0); 
                $row[] = (!empty($baris->giro) ? number_format($baris->giro,2) : 0); 
                $row[] = (!empty($baris->retur) ? number_format($baris->retur,2) : 0); 
                $row[] = (!empty($baris->CN) ? number_format($baris->CN,2) : 0); 
                $row[] = number_format($baris->Saldo_awal + ($baris->debet  - ($baris->cash + $baris->transfer + $baris->giro + ($baris->retur * -1) + $baris->CN)),2); 
                $data[] = $row;

              
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

    public function kartuPiutang()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content['pelanggan']= $this->Model_main->Pelanggan();
            $this->twig->display('laporan/kartuPiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function getkartupiutangPelanggan()
    {
        if ($this->logged) 
        {
            $tgl1 = $_POST['tgl1'];
            $tgl2 = $_POST['tgl2'];
            $pelanggan = $_POST['pelanggan'];
            $expld = explode("~", $pelanggan);
            $Kode = $expld[0];
            $Nama = $expld[1];

            $sawal = $this->Model_laporanAll->getkartupiutangPelanggan_sawal($Kode,$tgl1,$tgl2);
            $list = $this->Model_laporanAll->getkartupiutangPelanggan($Kode,$tgl1,$tgl2);
            $return['Result'] = $list;
            $return['s_awal'] = $sawal;
            echo json_encode($return);
        
        }
        else{
            redirect("auth/logout");
        }   
    }

    public function LapAgingPiutang()
    {   
        if ( $this->logged ) 
        {
            $this->content['cabang']= $this->Model_main->Cabang();
            $this->content['pelanggan']= $this->Model_main->Pelanggan();
            $this->content['salesman']= $this->Model_main->Salesman();
            $this->twig->display('laporan/LapAgingPiutang.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    

    public function listlaporanAgingPiutang()
    {   
        if ($this->logged) 
        {
            $params = $this->input->post();
            $byperiode = "";

            $tgl = $this->input->post('tgl');
            $jenis = $this->input->post('jenis');
            $pelanggan = $this->input->post('pelanggan');
            $salesman = $this->input->post('salesman');

            if ($jenis == "Rekap") {
                $query=$this->Model_laporanAll->listlaporanAgingPiutang($tgl,$jenis,$pelanggan)->result();
            }else if ($jenis == "Detail") {
                $query=$this->Model_laporanAll->listlaporanAgingPiutangDetail($tgl,$jenis,$pelanggan)->result();
            }else {
                $query=$this->Model_laporanAll->listlaporanAgingPiutangSales($tgl,$jenis,$salesman)->result();
            }

            $x = 0;
            $i = 0;
            $countCetak = 1;
            
          
            foreach ($query as $baris) {
                $row = array();
                $x++;
                $row[] = $x;
                $row[] = (!empty($baris->Pelanggan) ? $baris->Pelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");

                if ($jenis != "Rekap") {
                    $row[] = (!empty($baris->NoFaktur) ? $baris->NoFaktur : "");
                    $row[] = (!empty($baris->Salesman) ? $baris->Salesman : "");
                    $row[] = (!empty($baris->NamaSalesman) ? $baris->NamaSalesman : "");
                    $row[] = (!empty($baris->TglFaktur) ? $baris->TglFaktur : "");
                    $row[] = (!empty($baris->TglJtoFaktur) ? $baris->TglJtoFaktur : "");
                }

                $row[] = (!empty($baris->saldo_akhir) ? number_format($baris->saldo_akhir,2) : 0); 
                $row[] = (!empty($baris->piutang_bjt) ? number_format($baris->piutang_bjt,2) : 0); 
                $row[] = (!empty($baris->piutang_30) ? number_format($baris->piutang_30,2) : 0); 
                $row[] = (!empty($baris->piutang_45) ? number_format($baris->piutang_45,2) : 0); 
                $row[] = (!empty($baris->piutang_60) ? number_format($baris->piutang_60,2) : 0); 
                $row[] = (!empty($baris->piutang_90) ? number_format($baris->piutang_90,2) : 0); 
                $row[] = (!empty($baris->piutang_90_) ? number_format($baris->piutang_90_,2) : 0); 
                $data[] = $row;

              
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
}