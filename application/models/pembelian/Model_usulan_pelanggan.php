<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_usulan_pelanggan extends CI_Model {

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

    public function listArea()
    {   
        $query = $this->db->query("select Kode, Area from mst_area order by Kode asc")->result();

        return $query;
    }

    public function listKota()
    {
        $query = $this->db->query("select Kota from mst_kota order by Kota asc")->result();

        return $query;
    }

    public function listRayon()
    {   
        $query = $this->db->query("select Kode , Rayon from mst_rayon order by Kode asc")->result();

        return $query;
    }

    public function listGrupPelanggan()
    {   
        $query = $this->db->query("select Nama_group from mst_group_pelanggan order by Kode asc")->result();

        return $query;
    }

    public function listTipePajak()
    {   
        $query = $this->db->query("select Tipe_pajak from mst_tipe_pajak order by Kode asc")->result();

        return $query;
    }

    public function listWilayah()
    {   
        $query = $this->db->query("select Nama_wilayah, Kode from mst_wilayah order by Kode asc")->result();

        return $query;
    }

    public function listTipePelanggan()
    {   
        $query = $this->db->query("select Tipe from mst_tipepelanggan order by Tipe asc")->result();

        return $query;
    }

    public function listSalesman()
    {   
        $query = $this->db->query("select Nama, Kode from mst_karyawan order by Kode asc")->result();

        return $query;
    }

    public function listSalesman2()
    {   
        $query = $this->db->query("select Nama, Kode from mst_karyawan order by Kode asc")->result();

        return $query;
    }

    private function _get_datatables_query()
    {
        
        $this->db->from('mst_pelanggan as r');
        // $this->db->join('mst_cabang as c', 'r.Cabang = c.cabang');
        // $this->db->join('mst_area as a', 'r.Area = a.Area');
        // $this->db->join('mst_pelanggan as p', 'r.Pelanggan = p.Pelanggan');
        // $this->db->join('mst_tipepelanggan as tp', 'r.Tipe_2 = tp.Tipe');

        $i = 0;
    
        foreach ($this->column as $item) // loop column 
    {
     if(isset($_POST['search']['value'])) // if datatable send POST for search
     {

        if($i===0) // first loop
        {
           $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND. 
           $this->db->like($item, $_POST['search']['value']);
        }
        else
        {
           $this->db->or_like($item, $_POST['search']['value']);
        }

        if(count($this->column) - 1 == $i) //last loop
           $this->db->group_end(); //close bracket
     }
     $column[$i] = $item; // set column array variable to order processing
     $i++;
    }

        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        // if($_POST['length'] != -1)
        // $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
    
    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function save($data)
    {
        $this->db->insert("mst_usulan_pelanggan", $data);
        return $this->db->insert_id();
    }

    public function update($where, $data)
    {
        $this->db->update($this->table, $data, $where);
        return $this->db->affected_rows();
    }

    public function getByID($id = NULL)
    {
        $this->db->from($this->table);
        $this->db->where('id',$id);
        $query = $this->db->get();
 
        return $query->row();  
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('mst_pelanggan');
    }

    public function dataarea(){
        $query = $this->db->query("select * from mst_area where Area_Cabang = '".$this->cabang."' order by Area")->result();
        return $query;
    }

    public function datakota(){
         $query = $this->db->query("select * from mst_kota order by Provinsi,Kota asc")->result();
         return $query;
    }

    public function datarayon(){
        // log_message('error',print_r($query,true));
         $query = $this->db->query("select * from mst_rayon  where Cabang = '".$this->cabang."' order by Cabang,Rayon asc")->result();
         return $query;
    }

    public function datawilayah(){
         $query = $this->db->query("select * from mst_wilayah  where Cabang = '".$this->cabang."' order by Cabang,Nama_Wilayah asc")->result();
         return $query;
    }

    public function datatipepelanggan(){
         $query = $this->db->query("select * from mst_tipepelanggan order by Tipe,Nama_Tipe asc")->result();
         return $query;
    }

    public function datasalesman(){
         $query = $this->db->query("select * from mst_karyawan where Jabatan = 'Salesman' order by Nama")->result();
         return $query;
    }

    public function get_data_usulan_pelanggan($search=null, $limit=null, $status=null){
         $query = $this->db->query("select * FROM mst_usulan_pelanggan where Cabang = '".$this->cabang."' $search $limit");
         // $query = $this->db->query("select * FROM mst_pelanggan where Cabang = '".$this->cabang."'");
        return $query;
    }

    public function getalldataPelanggan($kode=null){
         $query = $this->db->query("select * FROM mst_pelanggan where  Kode ='".$kode."' LIMIT 1")->row(); 
        return $query;
    }


    public function saveeditpelanggan($params = null)
    {

        $xplkode = explode("-", $params->pelangganMask);
        $kodepelanggan = $xplkode[0];

        $updatePelanggan = array(
                // 'Pelanggan'=> $params->nama_pelanggan,
                // 'Nama_Faktur'=> $params->nama_faktur,
                'No_Ijin_Usaha'=> $params->no_ijin_usaha,
                'No_Ijin_Apoteker'=> $params->no_ijin_apoteker,
                'Tipe_Pajak'=> $params->tipe_pajak,
                'No_KTP'=> $params->no_ktp,
                'Nama_Pajak'=> $params->nama_pajak,
                'Alamat_Pajak'=> $params->alamat_pajak,
                'NPWP'=> $params->npwp,
                'Alamat'=> $params->alamat,
                'Telp'=> $params->telepon,
                'Kota'=> $params->kota,
                'Fax'=> $params->no_fax,
                'Email_Pelanggan'=> $params->alamat_email,
                'Alamat_Kirim'=> $params->alamat_kirim,
                'Class'=> $params->class_pelanggan,
                'Tipe_Pelanggan'=> $params->tipe_pelanggan,
                'Grup_Pelanggan'=> $params->group_pelanggan,
                'Apoteker'=> $params->nama_apoteker,
                'Apoteker'=> $params->nama_apoteker,
                'SIPA'=> $params->no_sipa,
                'EDSIPA'=> $params->ed_sipa,
                'Asst_Apoteker'=> $params->nama_asisten_apoteker,
                'TTK'=> $params->no_ijin_asisten_apoteker,
                'EDTTK'=> $params->ed_ttk,
                // 'Limit_Kredit'=> $params->limit_kredit,
                // 'TOP'=> $params->top,
                // 'Cara_Bayar'=> $params->cara_bayar,
                'Area'=> $params->area,
                'Rayon_1'=> $params->rayon,
                'Wilayah'=> $params->wilayah,
                'Salesman'=> $params->salesman,
                'Updated_by'=>$this->user,
                'Updated_at'=>date("Y-m-d H:i:s")
            );


            $this->db->trans_start(); 
            $this->db->where("Kode", $kodepelanggan);
            $this->db->where("id", $params->idpelanggan);
            $this->db->where("Cabang", $this->cabang);
            $this->db->update('mst_pelanggan',$updatePelanggan); 
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return "gagal";
            } 
            else {
                $this->db->trans_commit();
                return "berhasil";
                 
            }

//         $xplkode = explode("-", $params->pelangganMask);
//         $kodepelanggan = $xplkode[0];
//         $this->db->trans_start(); # Starting Transaction
//         $this->db->trans_strict(FALSE);

//         $this->db->set("Salesman", $params->salesman);
//         $this->db->where("Kode", $kodepelanggan);
//         $this->db->where("id", $params->idpelanggan);
//         $this->db->where("Cabang", $this->cabang);
//         $this->db->update("mst_pelanggan");
//         $this->db->trans_complete();

//         if ($this->db->trans_status() === FALSE)
//         {
//             $this->db->trans_rollback();
//             return "gagal";
//         }else{
//             $this->db->trans_commit();
//             return "berhasil";
//         }
//         var_dump($kodepelanggan);
// die();

        
    }

}