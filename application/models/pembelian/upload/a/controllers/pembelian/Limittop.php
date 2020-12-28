<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Limittop extends CI_Controller {

    var $content;
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *      http://example.com/index.php/welcome
     *  - or -
     *      http://example.com/index.php/welcome/index
     *  - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model_main');
        $this->load->model('pembelian/Model_limittop');
        $this->load->library('Owner');
        $this->load->library('Format');
        $this->logs = $this->session->all_userdata();
        $this->grupuser = $this->session->userdata('userGroup');
        $this->logged = $this->session->userdata('userLogged');
        $this->userGroup = $this->session->userdata('user_group');
        $this->content = array(
            "base_url" => base_url(),
            "logs" => $this->session->all_userdata(),
            "logged" => $this->session->userdata('userLogged'),
        );
    }

    public function limittop_buatusulan()
    {   
        
        if ( $this->logged ) 
        {   
            $this->twig->display('pembelian/limittop_buatusulan.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }


    public function listFakturPelanggan($kode = null)
    {
        if ($this->logged) 
        {
            $data = $this->Model_limittop->listFakturPelanggan($kode);
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function limittop_saveusulan()
    {          
            $params = (object)$this->input->post();
            $valid = $this->Model_limittop->limittop_saveusulan($params);
            echo json_encode(array("status" => TRUE,"data" => $valid));
    }

    public function datausulanlimittop()
    {   
        
        if ( $this->logged ) 
        {   
            $this->twig->display('pembelian/limittop_data.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }


    public function limittop_getdata()
    {
            $query=$this->Model_limittop->listDataApproval($this->grupuser)->result();
            $x = 0;
            $i = 0;
            $countCetak = 1;
            $data = array();

            foreach ($query as $baris) {
                $x++;
                $row = array();
                
                $row[] = $i+1;
                $listapp = '';
                $buttonapp = '';
                $statpusat = '';
                if($this->grupuser == "KSA"){
                    if($baris->AppKSA != "Y"){
                        $buttonapp = $buttonapp . '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approve('."'".$baris->id."'".','."'AppKSA'".')"><i class="fa fa-eye"></i> Approve</button>    <button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Riject" onclick="riject('."'".$baris->id."'".','."'AppKSA'".')"><i class="fa fa-eye"></i> Riject</button>';
                    }else{
                        if($baris->AppBM == ''){
                            $buttonapp = $buttonapp . 'Pending BM';
                        }else if($baris->Jenis == 'Limit' && $baris->AppROM == ''){
                            $buttonapp = $buttonapp . 'Pending ROM';
                        }else{
                            $buttonapp = $buttonapp . 'Pending Pusat';
                        }
                        //$listapp = $listapp .' KSA Approve';
                    }
                }else if($this->grupuser == "BM"){    
                    if($baris->AppBM != "Y" && $baris->AppKSA != ''){
                        $buttonapp = $buttonapp . '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approve('."'".$baris->id."'".','."'AppBM'".')"><i class="fa fa-eye"></i> Approve</button>    <button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Riject" onclick="riject('."'".$baris->id."'".','."'AppBM'".')"><i class="fa fa-eye"></i> Riject</button>';
                    }else{
                        if($baris->AppKSA == ''){
                            $buttonapp = $buttonapp . 'Pending KSA';
                        }else if($baris->Jenis == 'Limit' && $baris->AppROM == ''){
                            $buttonapp = $buttonapp . 'Pending ROM';
                        }else{
                            $buttonapp = $buttonapp . 'Pending Pusat';
                        }
                        //$listapp = $listapp .' - BM Approve';
                    }
                }else{
                    /*if(($baris->AppKSA != "Y") OR ($baris->AppBM != "BM")){

                    $buttonapp = $buttonapp . '<button type="button" class="btn btn-sm btn-success" href="javascript:void(0)" title="Approve" onclick="approve('."'".$baris->id."'".','."'AppBM'".')"><i class="fa fa-eye"></i> Approve</button>    <button type="button" class="btn btn-sm btn-danger" href="javascript:void(0)" title="Riject" onclick="riject('."'".$baris->id."'".','."'AppBM'".')"><i class="fa fa-eye"></i> Riject</button>';
                    }else{
                        $buttonapp = '-';
                    }*/
                        $buttonapp = '-';

                }
                if($baris->StatusPusat != 'Berhasil'){
                    $statpusat = '<button type="button" class="btn btn-sm btn-info" href="javascript:void(0)" title="Kirim" onclick="kirimulang('."'".$baris->id."'".')"><i class="fa fa-eye"></i> Kirim</button>';
                }else{
                    $statpusat = 'Berhasil';
                }

                    if($baris->AppKSA == "Y"){
                        $listapp = $listapp .' KSA Approve ';
                    }
                    if($baris->AppBM == "Y"){
                        $listapp = $listapp .'-- BM Approve';
                    }
                    if($baris->AppROM == "Y"){
                        $listapp = $listapp .'-- ROM Approve';
                    }

                $row[] = $buttonapp;
                $row[] = $listapp;
                $row[] = (!empty($baris->NoUsulan) ? $baris->NoUsulan : "");
                $row[] = (!empty($baris->Jenis) ? $baris->Jenis : "");
                $row[] = (!empty($baris->Tanggal) ? $baris->Tanggal : "");
                $row[] = (!empty($baris->KodePelanggan) ? $baris->KodePelanggan : "");
                $row[] = (!empty($baris->NamaPelanggan) ? $baris->NamaPelanggan : "");
                // $row[] = (!empty($baris->Rayon) ? $baris->Rayon : "");
                $row[] = (!empty($baris->PerbaikanLimit) ? $baris->PerbaikanLimit : "");
                $row[] = (!empty($baris->PerbaikanTop) ? $baris->PerbaikanTop : "");
                $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="Top" onclick="toppiutang('."'".$baris->KodePelanggan."'".')"><i class="fa fa-eye"></i> TOP Piutang</button>';
                $row[] = '<button type="button" class="btn btn-sm btn-default" href="javascript:void(0)" title="pelanggan" onclick="datapelanggan('."'".$baris->NoUsulan."'".')"><i class="fa fa-eye"></i> Riwayat</button>';
                $row[] = (!empty($baris->Status) ? $baris->Status : "");
                $row[] = $statpusat;
                $data[] = $row;
                $i++;
            }
            $output['data'] = $data;
            //output to json format
            echo json_encode($output);

    }

    public function limittop_approve()
    {
        if ($this->logged) 
        {
            $no = $_POST['id'];
            $user = $_POST['user'];
            $data = $this->Model_limittop->limittop_approve($no,$user);
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function limittop_reject()
    {
        if ($this->logged) 
        {
            $no = $_POST['id'];
            $user = $_POST['user'];
            $data = $this->Model_limittop->limittop_reject($no,$user);
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function limittop_kirimulang()
    {
        if ($this->logged) 
        {
            $no = $_POST['id'];
            $data = $this->Model_limittop->limittop_kirimulang($no);
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function limittop_getdatapusat()
    {
        if ($this->logged) 
        {
            $no = $_POST['NoUsulan'];
            $data = $this->Model_limittop->limittop_getdatapusat($no);
            echo json_encode($data);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function limittop_riwayatpelanggan()
    {
        if ($this->logged) 
        {
            $usulan = $_POST['usulan'];
            $data = $this->Model_limittop->limittop_riwayatpelanggan($usulan);
            echo json_encode($data);
        }
        else 
        {   
            redirect("main/auth/logout");
        }
    }
    
}
