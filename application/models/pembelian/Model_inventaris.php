<?php
class Model_inventaris extends CI_Model {
	
	function __construct(){
        parent::__construct();
        $this->userGroup = $this->session->userdata('userGroup');
        $this->cabang = $this->session->userdata('cabang');
    }

    public function saveData($params = null){
    	$valid = false;
    	foreach ($params as $key => $value) {
	    	$data = $this->db->query("select max(id) as no from mst_inventaris_kantor where Cabang = '".$value['cabang']."'")->result();
	    	if(empty($data[0]->no)){
	            $lastNumber = 1;
	        }else{
	    		$lastNumber = (int)$data[0]->no + 1;
	        }
            $scabang = $this->get_kode_cabang($value['cabang']);
    		$barcode = str_pad($lastNumber,5,"0",STR_PAD_LEFT).$scabang.strtoupper($value['ruang']).$value['kode'];
    		# code...
	    	$this->db->trans_begin();
	    	$this->db->set("id", $lastNumber);
	    	$this->db->set("scabang", $scabang);
	    	$this->db->set("cabang", $value['cabang']);
	    	$this->db->set("pemegang", $value['pemegang']);
	    	$this->db->set("kode", $value['kode']);
	    	$this->db->set("jenis", $value['jenis']);
	    	$this->db->set("barang", $value['barang']);
	    	$this->db->set("merk", $value['merk']);
	    	$this->db->set("lokasi", $value['lokasi']);
	    	$this->db->set("ruang", strtoupper($value['ruang']));
	    	$this->db->set("kondisi", $value['kondisi']);
	    	$this->db->set("keterangan", $value['keterangan']);
	    	$this->db->set("tipe", $value['tipe']);
            $this->db->set("barcode", $barcode);
	    	$this->db->set("tgl_beli", $value['tgl_beli']);
	    	$this->db->insert("mst_inventaris_kantor");
    	}
    	if ($this->db->trans_status() === FALSE)
        {
                $this->db->trans_rollback();
                $valis=false;
        }
        else
        {
                $this->db->trans_commit();
                $valid = true;
        }

        return $valid;

    }

    public function load_datainventaris($search=null, $limit=null, $status=null){
    	// $data = $this->db->query("select * from mst_inventaris_kantor where Cabang = '".$value['cabang']."'")->result();
    	$header = $this->db->query("select * from mst_inventaris_kantor $search $limit");
        return $header;
    }

    // Hapus data
    public function remove_data_inventaris($no){
    	$hasil = $this->db->query("delete from mst_inventaris_kantor where barcode = '".$no."'");
    	return; 
    }

    // Print Barcode inventaris
	public function cetak_inventaris($asd)
	{
		$last_names = array_column($asd, 'value');
		$hasil = $this->db->query("select * from mst_inventaris_kantor where barcode IN ('" . implode("','", $last_names) . "')")->result();
	}
    //get kode cabang
    public function get_kode_cabang($cabang){
        $hasil = $this->db->query("select * from mst_cabang where Cabang ='".$cabang."'")->row('Kode');
        return $hasil;
    }
}
?>
