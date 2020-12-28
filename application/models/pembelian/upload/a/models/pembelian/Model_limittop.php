<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_limittop extends CI_Model {

    var $table = 'mst_pelanggan';
    var $column = array('r.Pelanggan','r.Kode','r.Kode2','r.Nama_Faktur','r.Nama_Pajak','r.Alamat_Pajak','r.NPWP','r.Alamat','r.Kode','r.Kota'); //set column field database for datatable searchable just firstname , lastname , address are searchable
    var $order = array('r.id' => 'desc'); // default order 
    public function __construct()
    {
            parent::__construct();
            $this->cabang = $this->session->userdata('cabang');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
            // Your own constructor code
    }

// MODULES LAPORAN PELANGGAN CABANG

    public function listCabang()
    {   

        $query = $this->db->query("select Kode,Cabang from mst_cabang order by Kode asc")->result();

        return $query;
    }



    public function listFakturPelanggan($kode = null)
    {   
        $queryFaktur = $this->db->query("select * from trs_faktur where Cabang = '".$this->cabang."' AND Pelanggan = '".$kode."' AND TipeDokumen = 'Faktur' and Status not like '%Lunas%' ")->result();
        $queryPelunasan = $this->db->query("select * from trs_pelunasan_detail where Cabang = '".$this->cabang."' AND KodePelanggan = '".$kode."' AND ValuePelunasan != 0 limit 10 ")->result();
        $data = array(
            'faktur' => $queryFaktur,
            'pelunasan' => $queryPelunasan );
        return $data;
    }


    public function limittop_saveusulan($params = NULL)
        {   
            $valid = TRUE;            
            $data = array(
                'Jenis' => $params->jenisusulan,
                'NoUsulan' => $this->cabang."-".$params->jenisusulan."-".date('Y-m-d H:i:s'),
                'Cabang' => $this->cabang,
                'Tanggal' => date('Y-m-d H:i:s'),
                'KodePelanggan' => $params->pelangganori,
                'NamaPelanggan' => $params->namapelangganori,
                'DataPelanggan' => $params->datapelanggan,
                'RiwayatBayar' => $params->datariwayat,
                'TanggalJanji' =>  $params->tgljanji,
                'PerbaikanLimit' =>  $params->usulanlimit,
                'PerbaikanTop' =>  $params->usulantop,
                'Alasan' =>  $params->dataalasan,
                'Keterangan' => $params->dataketerangan,
                'Status' => 'Usulan',
                'AddTime' => date('Y-m-d H:i:s')
            );

            $valid = $this->db->insert('trs_usulan_top',$data);
            
            return $valid;
        }


    public function listDataApproval($user = '')
    {
         $query = $this->db->query("select * from trs_usulan_top where Status = 'Usulan'");
        // log_message("error",print_r($query,true));    
        return $query;
    }

    public function limittop_approve($id,$user)
    {
        $valid = true;


        $query1 = $this->db->select('Jenis,PerbaikanLimit')->get('trs_usulan_top',array('id' => $id ));

        $data = array(
                $user => 'Y',
                'Time'.$user => date('Y-m-d H:i:s') 
            );

        if($query1->row()->Jenis == "Limit" and $user == "AppBM"){
            
            $query = $this->db->select('*')->get_where('mst_cabang',
                array(
                    'Cabang' => $this->cabang 
                )
            );
            
            if($query1->row()->PerbaikanLimit <= $query->row()->limit_BM2){
            
                $data = array(
                    $user => 'Y',
                    'Time'.$user => date('Y-m-d H:i:s'),
                    'Status' => 'Approve' 
                );
            }
        }

        $where = array(
            'id' => $id
        );
        
        $out = $this->db->update('trs_usulan_top', $data, $where);

        if(!$out){
            $valid = false;
        }
            $this->db->where('id', $id);
            $query = $this->db->get('trs_usulan_top')->row();
            //log_message("error",print_r($query,true));    

            if($query->AppBM == "Y" and $query->AppKSA != "" and $query->Status == "Usulan"){

                log_message("error",print_r("Berhasil",true));
                $this->db2 = $this->load->database('pusat', TRUE);
                $datareturn = $this->db2->insert('trs_usulan_top', $query);  
                
                if($datareturn > 0){
                    $out = $this->db->update('trs_usulan_top', array('StatusPusat' => 'Berhasil' ), $where);
                }

            }
        return $valid;
    }

    public function limittop_reject($id,$user)
    {
        $valid = true;
        if($user == "AppBM"){
            $data = array(
                $user => 'R',
                'Status' => 'Reject',
                'TimeAppBM' => date('Y-m-d H:i:s') 
            );
        }else{
         $data = array(
                $user => 'R',
                'TimeAppKSA' => date('Y-m-d H:i:s') 
            );   
        }
        $where = array(
            'id' => $id
        );
        $out = $this->db->update('trs_usulan_top', $data, $where);
        if(!$out){
            $valid = false;
        }
        return $valid;
    }

    public function limittop_kirimulang($id)
    {

            $valid = true;
            $this->db->where('id', $id);
            $query = $this->db->get('trs_usulan_top')->row(); 

            if($query->AppBM == "Y" and $query->AppKSA != ""){

                $this->db2 = $this->load->database('pusat', TRUE);
                $datareturn = $this->db2->insert('trs_usulan_top', $query);  
                
                if($datareturn > 0){
                    $out = $this->db->update('trs_usulan_top', array('StatusPusat' => 'Berhasil' ), array('id' => $id ));
                }else{
                     $valid = false;
                }

            }else{
                $valid = false;
            }
        return $valid;
    }



    public function limittop_getdatapusat($nousulan)
    {
        $valid = true;

        $where = array(
            'NoUsulan' => $nousulan
        );
            
            $this->db2 = $this->load->database('pusat', TRUE);
            $this->db2->select('AppPusat, TimePusat');
            $this->db2->where('NoUsulan', $nousulan);
            
            $query = $this->db2->get('trs_usulan_top')->row();
            //log_message("error",print_r($query,true));    

            if($query->AppPusat == "Y"){

                $out = $this->db->update('trs_usulan_top', array(
                    'Status' => 'Approve',
                    'AppPusat' => 'Y',
                    'TimePusat' => $query->TimePusat ), $where);

            }
        return $valid;
    }

    public function limittop_riwayatpelanggan($nousulan)
    {
        $valid = true;
            
            $this->db->select('DataPelanggan, RiwayatBayar');
            $this->db->where('NoUsulan', $nousulan);
            
            $query = $this->db->get('trs_usulan_top')->row();
            //log_message("error",print_r($query,true));    

        return $query;
    }




}