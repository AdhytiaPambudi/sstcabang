<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usulan_Pelanggan extends CI_Controller {

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
        $this->load->model('pembelian/Model_usulan_pelanggan');
        $this->load->library('Owner');
        $this->load->library('Format');
        $this->logs = $this->session->all_userdata();
        $this->logged = $this->session->userdata('userLogged');
        $this->userGroup = $this->session->userdata('user_group');
        $this->content = array(
            "base_url" => base_url(),
            "logs" => $this->session->all_userdata(),
            "logged" => $this->session->userdata('userLogged'),
        );
    }

    public function pelanggan()
    {   
        
        if ( $this->logged ) 
        {   
            $this->content['areas'] = $this->Model_usulan_pelanggan->dataarea();
            $this->content['rayons'] = $this->Model_usulan_pelanggan->datarayon();
            $this->content['wilayahs'] = $this->Model_usulan_pelanggan->datawilayah();
            $this->content['salesmans'] = $this->Model_usulan_pelanggan->datasalesman();
            $this->content['kotas'] = $this->Model_usulan_pelanggan->datakota();
            $this->content['tipe_pelanggans'] = $this->Model_usulan_pelanggan->datatipepelanggan();
            $this->twig->display('pembelian/buatusulanpelanggan.html', $this->content);
        }
        else 
        {
            redirect("auth/logout");
        }
    }

    public function listCabangPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listCabang();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listAreaPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listArea();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listGrupPelangganx()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listGrupPelanggan();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listKotaPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listKota();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listTipePajakPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listTipePajak();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listTipePelangganx()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listTipePelanggan();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listWilayahPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listWilayah();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listRayonPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listRayon();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listSalesmanPelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listSalesman();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listSalesman2Pelanggan()
    {
        if ($this->logged) 
        {
            $data = $this->Model_usulan_pelanggan->listsalesman2();
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function listDataPelanggan()
    {

         $list = $this->Model_usulan_pelanggan->get_datatables();
         $data = array();
         $no = $_POST['start'];
         foreach ($list as $pelanggan) {
             $no++;
             $row = array();
             $row[] = $no;
             $row[] = (!empty($pelanggan->Pelanggan) ? $pelanggan->Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Kode) ? $pelanggan->Kode : "NULL");
             $row[] = (!empty($pelanggan->Kode2) ? $pelanggan->Kode2 : "NULL");
             $row[] = (!empty($pelanggan->Nama_Faktur) ? $pelanggan->Nama_Faktur : "NULL");
             $row[] = (!empty($pelanggan->Alamat) ? $pelanggan->Alamat : "NULL");
             $row[] = (!empty($pelanggan->Alamat2) ? $pelanggan->Alamat2 : "NULL");
             $row[] = (!empty($pelanggan->Kota) ? $pelanggan->Kota : "NULL");
             $row[] = (!empty($pelanggan->Kota2) ? $pelanggan->Kota2 : "NULL");
             $row[] = (!empty($pelanggan->Grup_Pelanggan) ? $pelanggan->Grup_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Grup_Pelanggan2) ? $pelanggan->Grup_Pelanggan2 : "NULL");
             $row[] = (!empty($pelanggan->Nama_Pajak) ? $pelanggan->Nama_Pajak : "NULL");
             $row[] = (!empty($pelanggan->Alamat_Pajak) ? $pelanggan->Alamat_Pajak : "NULL");
             $row[] = (!empty($pelanggan->NPWP) ? $pelanggan->NPWP : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pajak) ? $pelanggan->Tipe_Pajak : "NULL");
             $row[] = (!empty($pelanggan->Cabang) ? $pelanggan->Cabang : "NULL");
             $row[] = (!empty($pelanggan->Cabang_String) ? $pelanggan->Cabang_String : "NULL");
             $row[] = (!empty($pelanggan->Area) ? $pelanggan->Area : "NULL");
             $row[] = (!empty($pelanggan->Area_String) ? $pelanggan->Area_String : "NULL");
             $row[] = (!empty($pelanggan->Class) ? $pelanggan->Class : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pelanggan) ? $pelanggan->Tipe_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Telp) ? $pelanggan->Telp : "NULL");
             $row[] = (!empty($pelanggan->Limit_Kredit) ? $pelanggan->Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->TOP) ? $pelanggan->TOP : "NULL");
             $row[] = (!empty($pelanggan->Cara_Bayar) ? $pelanggan->Cara_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Saldo_Piutang) ? $pelanggan->Saldo_Piutang : "NULL");
             $row[] = (!empty($pelanggan->Kat) ? $pelanggan->Kat : "NULL");
             $row[] = (!empty($pelanggan->Tipe_2) ? $pelanggan->Tipe_2 : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Harga) ? $pelanggan->Tipe_Harga : "NULL");
             $row[] = (!empty($pelanggan->Aktif) ? $pelanggan->Aktif : "NULL");
             $row[] = (!empty($pelanggan->Rayon_1) ? $pelanggan->Rayon_1 : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Limit) ? $pelanggan->Status_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Usulan_Limit_Kredit) ? $pelanggan->Usulan_Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->History_Update) ? $pelanggan->History_Update : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_Limit) ? $pelanggan->Tgl_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_BM) ? $pelanggan->Approval_Limit_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_Pusat) ? $pelanggan->Approval_Limit_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Top) ? $pelanggan->Status_Usulan_Top : "NULL");
             $row[] = (!empty($pelanggan->Buka_TOP) ? $pelanggan->Buka_TOP : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_TOP) ? $pelanggan->Tgl_Usulan_TOP : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_BM) ? $pelanggan->Approval_TOP_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_Pusat) ? $pelanggan->Approval_TOP_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Riwayat_Bayar) ? $pelanggan->Riwayat_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Dokumen_Limit_TOP) ? $pelanggan->Dokumen_Limit_TOP : "NULL");
             $row[] = (!empty($pelanggan->Kategori_2) ? $pelanggan->Kategori_2 : "NULL");
             $row[] = (!empty($pelanggan->Wilayah) ? $pelanggan->Wilayah : "NULL");
             $row[] = (!empty($pelanggan->DOW) ? $pelanggan->DOW : "NULL");
             $row[] = (!empty($pelanggan->Week) ? $pelanggan->Week : "NULL");
             $row[] = (!empty($pelanggan->Date) ? $pelanggan->Date : "NULL");
             $row[] = (!empty($pelanggan->Prioritas) ? $pelanggan->Prioritas : "NULL");
             $row[] = (!empty($pelanggan->Prins_Onf) ? $pelanggan->Prins_Onf : "NULL");
             $row[] = (!empty($pelanggan->Cab_Onf) ? $pelanggan->Cab_Onf : "NULL");
             $row[] = (!empty($pelanggan->smsP) ? $pelanggan->smsP : "NULL");
             $row[] = (!empty($pelanggan->Kode_Prov) ? $pelanggan->Kode_Prov : "NULL");
             $row[] = (!empty($pelanggan->Status_Update) ? $pelanggan->Status_Update : "NULL");
             $row[] = (!empty($pelanggan->Salesman) ? $pelanggan->Salesman : "NULL");
             $row[] = (!empty($pelanggan->Salesman2) ? $pelanggan->Salesman2 : "NULL");
             $row[] = (!empty($pelanggan->No_SIPA) ? $pelanggan->No_SIPA : "NULL");
             $row[] = (!empty($pelanggan->ED_SIPA) ? $pelanggan->ED_SIPA : "NULL");

            //add html for action
            $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$pelanggan->id."'".')"><i class="fa fa-pencil"></i> Edit</a>
                   <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$pelanggan->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
        
             $data[] = $row;
         }

         $output = array(
                        "draw" => $_POST['draw'],
                        "recordsTotal" => $this->Model_usulan_pelanggan->count_all(),
                        "recordsFiltered" => $this->Model_usulan_pelanggan->count_filtered(),
                        "data" => $data,
                );
        //output to json format
        echo json_encode($output);
    }

    public function addDataPelanggan()
    {
        if ( $this->logged )
        {   
            $request = $this->input->post();
            $cek_kode = $this->Model_main->dataPelanggan($request['kode']);
            if(count($cek_kode) > 0 ){
                $data = [
                    "status" => FALSE,
                    "message" => "kode ".$request['kode']." sudah pernah di entry"
                ];
                echo json_encode($data);
                return;
            }

            $data = array(
                'Pelanggan' => $this->input->post('nama_pelanggan'),  
                'Kode' => $this->input->post('kode'),          
                'Kode2' => $this->input->post('kode2'),
                'Nama_Faktur' => $this->input->post('nama_faktur'),
                'Alamat' => $this->input->post('alamat'),
                'Alamat2' => $this->input->post('alamat2'),
                'Alamat_Kirim' => $this->input->post('alamat_kirim'),
                'Kota' => $this->input->post('kota'),
                'Kota2' => $this->input->post('kota2'),
                'Fax' => $this->input->post('no_fax'),
                'Grup_Pelanggan' => $this->input->post('group_pelanggan'),
                'Grup_Pelanggan2' => $this->input->post('group_pelanggan2'),
                'Tipe_Pajak' => $this->input->post('tipe_pajak'),
                'Nama_Pajak' => $this->input->post('nama_pajak'),  
                'Alamat_Pajak' => $this->input->post('alamat_pajak'),          
                'NPWP' => $this->input->post('npwp'),
                'Tipe_Pajak' => $this->input->post('tipe_pajak'),
                'Cabang' => $this->input->post('cabang'),
                'Area' => $this->input->post('area'),
                'Class' => $this->input->post('class_pelanggan'),
                'Telp' => $this->input->post('telepon'),
                'Email_Pelanggan' => $this->input->post('alamat_email'),
                'Tipe_Pelanggan' => $this->input->post('tipe_pelanggan'),
                'Jenis_Pelanggan' => $this->input->post('jenis_pelanggan'),
                'Limit_Kredit' => str_replace( ',', '', $this->input->post('limit_kredit')),
                'TOP' => $this->input->post('top'),  
                'Cara_Bayar' => $this->input->post('cara_bayar'),          
                'Saldo_Piutang' => $this->input->post('saldo_piutang'),
                'Kat' => $this->input->post('kat'),
                'Tipe_2' => $this->input->post('tipe2'),   
                'Tipe_Harga' => $this->input->post('tipe_harga'),           
                'Aktif' => "0",
                'Rayon_1' => $this->input->post('rayon'),
                // 'Status_Usulan_Limit' => $this->input->post('status_usulan_limit'),
                // 'Usulan_Limit_Kredit' => $this->input->post('usulan_limit_kredit'),
                // 'History_Update' => $this->input->post('histori_update'),
                // 'Tgl_Usulan_Limit' => $this->input->post('tgl_usulan_limit'),
                // 'Approval_Limit_BM' => $this->input->post('approval_limit_bm'),
                // 'Approval_Limit_Pusat' => $this->input->post('approval_limit_pusat'),
                // 'Status_Usulan_TOP' => $this->input->post('status_usulan_top'),  
                // 'Buka_TOP' => $this->input->post('Buka_top'),          
                // 'Tgl_Usulan_TOP' => $this->input->post('tgl_usulan_top'),
                // 'Approval_TOP_BM' => $this->input->post('approval_top_bm'),
                // 'Approval_TOP_Pusat' => $this->input->post('approval_top_pusat'),
                // 'Riwayat_Bayar' => $this->input->post('riwayat_bayar'),
                // 'Kategori_2' => $this->input->post('kategori2'),
                'Wilayah' => $this->input->post('wilayah'),
                // 'DOW' => $this->input->post('dow'),
                // 'Week' => $this->input->post('week'),  
                'Date' => $this->input->post('date'),          
                'Prioritas' => $this->input->post('priorotas'),
                'smsP' => $this->input->post('smsp'),
                'Prins_Onf' => $this->input->post('prins_onf'),
                'Cab_Onf' => $this->input->post('cab_onf'),
                'Kode_Prov' => $this->input->post('kode_prov'),
                // 'Status_Update' => $this->input->post('status_update'),
                'Salesman' => $this->input->post('salesman'),
                'Salesman2' => $this->input->post('salesman2'),
                'SIPA' => $this->input->post('no_sipa'),
                'EDSIPA' => $this->input->post('ed_sipa'),
                'Apoteker' => $this->input->post('ed_sipa'),
                'Asst_Apoteker' => $this->input->post('ed_sipa'),
                'TTK' => $this->input->post('ed_sipa'),
                'EDTTK' => $this->input->post('ed_sipa'),
                'Status_Usulan' => "Usulan",
                'Created_by' => $this->session->userdata('username'),
                'Created_at' => date("Y-m-d H:i:s")
            );

            // log_message('error',print_r($data,true));
            // echo json_encode(array("status" => FALSE));
            // return;

            // ++++++++ Untuk upload dokumen +++++++
        // $name = array();
        //     if(!empty($_FILES['Dokumen_Limit_TOP']['name']))
        //     {
        //         $targetPath3 = getcwd()."/assets/img/pelanggan/";
        //         $uniqID3 = $this->owner->getUniqID();
        //         $tempFile3= $_FILES['Dokumen_Limit_TOP']['tmp_name'];
        //         $ext3 = explode(".", $_FILES['Dokumen_Limit_TOP']['name']);
        //         $name3 = $uniqID3.".".$ext3[1];
        //         $targetFile3 = $targetPath3.$name3 ;
        //         move_uploaded_file($tempFile3, $targetFile3);
        //         $data['Dokumen_Limit_TOP'] = $name3;
        //     }  

            $valid = $this->Model_usulan_pelanggan->save($data);
            echo json_encode(array("status" => TRUE));
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function updateDataPelanggan()
    {
        if ( $this->logged ) 
        {   
            //$this->_validate();
            $data = array(
                'Pelanggan' => $this->input->post('Pelanggan'),  
                'Kode' => $this->input->post('Kode'),          
                'Kode2' => $this->input->post('Kode2'),
                'Nama_Faktur' => $this->input->post('Nama_Faktur'),
                'Alamat' => $this->input->post('Alamat'),
                'Alamat2' => $this->input->post('Alamat2'),
                'Kota' => $this->input->post('Kota'),
                'Kota2' => $this->input->post('Kota2'),
                'Grup_Pelanggan' => $this->input->post('Grup_Pelanggan'),
                'Grup_Pelanggan2' => $this->input->post('Grup_Pelanggan2'),
                'Nama_Pajak' => $this->input->post('Nama_Pajak'),  
                'Alamat_Pajak' => $this->input->post('Alamat_Pajak'),          
                'NPWP' => $this->input->post('NPWP'),
                'Tipe_Pajak' => $this->input->post('Tipe_Pajak'),
                'Cabang' => $this->input->post('Cabang'),
                'Cabang_String' => $this->input->post('Cabang_String'),
                'Area' => $this->input->post('Area'),
                'Area_String' => $this->input->post('Area_String'),
                'Class' => $this->input->post('Class'),
                'Tipe_Pelanggan' => $this->input->post('Tipe_Pelanggan'),
                'Telp' => $this->input->post('Telp'),
                'Limit_Kredit' => $this->input->post('Limit_Kredit'),
                'TOP' => $this->input->post('TOP'),  
                'Cara_Bayar' => $this->input->post('Cara_Bayar'),          
                'Saldo_Piutang' => $this->input->post('Saldo_Piutang'),
                'Kat' => $this->input->post('Kat'),
                'Tipe_2' => $this->input->post('Tipe_2'),   
                'Tipe_Harga' => $this->input->post('Tipe_Harga'),           
                'Aktif' => $this->input->post('Aktif'),
                'Rayon_1' => $this->input->post('Rayon_1'),
                'Status_Usulan_Limit' => $this->input->post('Status_Usulan_Limit'),
                'Usulan_Limit_Kredit' => $this->input->post('Usulan_Limit_Kredit'),
                'History_Update' => $this->input->post('History_Update'),
                'Tgl_Usulan_Limit' => $this->input->post('Tgl_Usulan_Limit'),
                'Approval_Limit_BM' => $this->input->post('Approval_Limit_BM'),
                'Approval_Limit_Pusat' => $this->input->post('Approval_Limit_Pusat'),
                'Status_Usulan_TOP' => $this->input->post('Status_Usulan_TOP'),  
                'Buka_TOP' => $this->input->post('Buka_TOP'),          
                'Tgl_Usulan_TOP' => $this->input->post('Tgl_Usulan_TOP'),
                'Approval_TOP_BM' => $this->input->post('Approval_TOP_BM'),
                'Approval_TOP_Pusat' => $this->input->post('Approval_TOP_Pusat'),
                'Riwayat_Bayar' => $this->input->post('Riwayat_Bayar'),
                'Kategori_2' => $this->input->post('Kategori_2'),
                'Wilayah' => $this->input->post('Wilayah'),
                'DOW' => $this->input->post('DOW'),
                'Week' => $this->input->post('Week'),  
                'Date' => $this->input->post('Date'),          
                'Prioritas' => $this->input->post('Prioritas'),
                'smsP' => $this->input->post('smsP'),
                'Prins_Onf' => $this->input->post('Prins_Onf'),
                'Cab_Onf' => $this->input->post('Cab_Onf'),
                'Kode_Prov' => $this->input->post('Kode_Prov'),
                'Status_Update' => $this->input->post('Status_Update'),
                'Salesman' => $this->input->post('Salesman'),
                'Salesman2' => $this->input->post('Salesman2'),
                'No_SIPA' => $this->input->post('No_SIPA'),
                'ED_SIPA' => $this->input->post('ED_SIPA'),
                'Created_by' => $this->session->userdata('username'),
                'Created_by' => date("Y-m-d H:i:s"),
            );

            $name = array();
            if(!empty($_FILES['Dokumen_Limit_TOP']['name']))
            {
                $targetPath3 = getcwd()."/assets/img/pelanggan/";
                $uniqID3 = $this->owner->getUniqID();
                $tempFile3= $_FILES['Dokumen_Limit_TOP']['tmp_name'];
                $ext3 = explode(".", $_FILES['Dokumen_Limit_TOP']['name']);
                $name3 = $uniqID3.".".$ext3[1];
                $targetFile3 = $targetPath3.$name3 ;
                move_uploaded_file($tempFile3, $targetFile3);
                $data['Dokumen_Limit_TOP'] = $name3;
            } 
            
            $valid = $this->Model_usulan_pelanggan->update(array('id' => $this->input->post('id')), $data);
            echo json_encode(array("status" => TRUE));
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function deleteDataPelanggan($id = NULL)
    {
        $data = $this->Model_usulan_pelanggan->getByID($id);
        if(file_exists('assets/img/pelanggan/'.$data->Dokumen_Limit_TOP) && $data->Dokumen_Limit_TOP)
                    unlink('assets/img/pelanggan/'.$data->Dokumen_Limit_TOP);
        
        $this->Model_usulan_pelanggan->delete($id);
        echo json_encode(array("status" => TRUE));
    }

    public function getDataPelanggan($id = NULL)
    {    
        if ( $this->logged ) 
        {
            $data = $this->Model_usulan_pelanggan->getByID($id);
            echo json_encode($data);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function datausulanpelanggan()
    {
        if ( $this->logged )
        {
            $this->content['datapelanggan'] = $this->Model_usulan_pelanggan->get_datatables();
            $this->twig->display('pembelian/datausulanpelanggan.html', $this->content);
        }
        else 
        {   
            redirect("auth/logout");
        }
    }

    public function list_data_usulan_pelanggan($cek = null){
        $data = array();
        $bySearch = "";
        /*Jumlah baris yang akan ditampilkan pada setiap page*/
        $length=$_REQUEST['length'];
        $start=$_REQUEST['start'];
        $search=$_REQUEST['search']["value"];
        // if($cek=='all')
        $total = $this->Model_usulan_pelanggan->get_data_usulan_pelanggan()->num_rows();
        $output=array();
        if($search!=""){
            $bySearch = " and (Kode like '%".$search."%' or Pelanggan like '%".$search."%' or Nama_Faktur like '%".$search."%' or Tipe_Pelanggan like '%".$search."%' or Jenis_Pelanggan like '%".$search."%' or Alamat like '%".$search."%' or Alamat2 like '%".$search."%' or Alamat_Kirim like '%".$search."%' or Alamat_Kirim like '%".$search."%'  or Kota like '%".$search."%')";
        }
        $byLimit = " LIMIT ".$start.", ".$length;
        $no = $_POST['start'];
        $list=$this->Model_usulan_pelanggan->get_data_usulan_pelanggan($bySearch, $byLimit)->result();
        foreach ($list as $pelanggan) {
             $no++;
             $row = array();
             $row[] = $no;
             $row[] = (!empty($pelanggan->Pelanggan) ? $pelanggan->Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Kode) ? $pelanggan->Kode : "NULL");
             $row[] = (!empty($pelanggan->Nama_Faktur) ? $pelanggan->Nama_Faktur : "NULL");
             $row[] = (!empty($pelanggan->Alamat) ? $pelanggan->Alamat : "NULL");
             $row[] = (!empty($pelanggan->Kota) ? $pelanggan->Kota : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pelanggan) ? $pelanggan->Tipe_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Grup_Pelanggan) ? $pelanggan->Grup_Pelanggan : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Pajak) ? $pelanggan->Tipe_Pajak : "NULL");
             $row[] = (!empty($pelanggan->NPWP) ? $pelanggan->NPWP : "NULL");
             $row[] = (!empty($pelanggan->Limit_Kredit) ? $pelanggan->Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->TOP) ? $pelanggan->TOP : "NULL");
             $row[] = (!empty($pelanggan->Salesman) ? $pelanggan->Salesman : "NULL");
             $row[] = (!empty($pelanggan->Area) ? $pelanggan->Area : "NULL");
             $row[] = (!empty($pelanggan->Status_Update) ? $pelanggan->Status_Update : "NULL");
             $row[] = (!empty($pelanggan->Alamat_Pajak) ? $pelanggan->Alamat_Pajak : "NULL");
             $row[] = (!empty($pelanggan->Class) ? $pelanggan->Class : "NULL");
             $row[] = (!empty($pelanggan->Nama_Pajak) ? $pelanggan->Nama_Pajak : "NULL");
             $row[] = ($pelanggan->Status_Usulan == 'Closed' ? "Aktif" : "Non Aktiv");
             $row[] = (!empty($pelanggan->Telp) ? $pelanggan->Telp : "NULL");
             $row[] = (!empty($pelanggan->Rayon_1) ? $pelanggan->Rayon_1 : "NULL");
             $row[] = (!empty($pelanggan->Cara_Bayar) ? $pelanggan->Cara_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Kategori_2) ? $pelanggan->Kategori_2 : "NULL");
             $row[] = (!empty($pelanggan->Kode_Prov) ? $pelanggan->Kode_Prov : "NULL");
             $row[] = (!empty($pelanggan->No_SIPA) ? $pelanggan->No_SIPA : "NULL");
             $row[] = (!empty($pelanggan->ED_SIPA) ? $pelanggan->ED_SIPA : "NULL");

             $row[] = (!empty($pelanggan->Kat) ? $pelanggan->Kat : "NULL");
             $row[] = (!empty($pelanggan->Cabang) ? $pelanggan->Cabang : "NULL");
             $row[] = (!empty($pelanggan->Cabang_String) ? $pelanggan->Cabang_String : "NULL");
             $row[] = (!empty($pelanggan->Area_String) ? $pelanggan->Area_String : "NULL");
             $row[] = (!empty($pelanggan->Saldo_Piutang) ? $pelanggan->Saldo_Piutang : "NULL");
             $row[] = (!empty($pelanggan->Tipe_2) ? $pelanggan->Tipe_2 : "NULL");
             $row[] = (!empty($pelanggan->Tipe_Harga) ? $pelanggan->Tipe_Harga : "NULL");
             $row[] = (!empty($pelanggan->Aktif) ? $pelanggan->Aktif : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Limit) ? $pelanggan->Status_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Usulan_Limit_Kredit) ? $pelanggan->Usulan_Limit_Kredit : "NULL");
             $row[] = (!empty($pelanggan->History_Update) ? $pelanggan->History_Update : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_Limit) ? $pelanggan->Tgl_Usulan_Limit : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_BM) ? $pelanggan->Approval_Limit_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_Limit_Pusat) ? $pelanggan->Approval_Limit_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Status_Usulan_Top) ? $pelanggan->Status_Usulan_Top : "NULL");
             $row[] = (!empty($pelanggan->Buka_TOP) ? $pelanggan->Buka_TOP : "NULL");
             $row[] = (!empty($pelanggan->Tgl_Usulan_TOP) ? $pelanggan->Tgl_Usulan_TOP : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_BM) ? $pelanggan->Approval_TOP_BM : "NULL");
             $row[] = (!empty($pelanggan->Approval_TOP_Pusat) ? $pelanggan->Approval_TOP_Pusat : "NULL");
             $row[] = (!empty($pelanggan->Riwayat_Bayar) ? $pelanggan->Riwayat_Bayar : "NULL");
             $row[] = (!empty($pelanggan->Dokumen_Limit_TOP) ? $pelanggan->Dokumen_Limit_TOP : "NULL");
             $row[] = (!empty($pelanggan->Wilayah) ? $pelanggan->Wilayah : "NULL");
             $row[] = (!empty($pelanggan->DOW) ? $pelanggan->DOW : "NULL");
             $row[] = (!empty($pelanggan->Week) ? $pelanggan->Week : "NULL");
             $row[] = (!empty($pelanggan->Date) ? $pelanggan->Date : "NULL");
             $row[] = (!empty($pelanggan->Prioritas) ? $pelanggan->Prioritas : "NULL");
             $row[] = (!empty($pelanggan->Prins_Onf) ? $pelanggan->Prins_Onf : "NULL");
             $row[] = (!empty($pelanggan->Cab_Onf) ? $pelanggan->Cab_Onf : "NULL");
             $row[] = (!empty($pelanggan->smsP) ? $pelanggan->smsP : "NULL");
             $row[] = (!empty($pelanggan->Salesman2) ? $pelanggan->Salesman2 : "NULL");

            //add html for action
            $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="updateData('."'".$pelanggan->id."'".')"><i class="fa fa-pencil"></i> Edit</a>
                   <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="deleteData('."'".$pelanggan->id."'".')"><i class="fa fa-trash"></i> Delete</a>';
        
             $data[] = $row;
         }
         $output = array(
                        "draw" => $_POST['draw'],
                        "recordsTotal" => $this->Model_usulan_pelanggan->count_all(),
                        "recordsFiltered" => $this->Model_usulan_pelanggan->count_filtered(),
                        "data" => $data,
                );
        //output to json format
        echo json_encode($output);
    }

    
}
