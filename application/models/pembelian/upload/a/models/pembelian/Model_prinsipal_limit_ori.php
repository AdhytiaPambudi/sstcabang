<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");
class Model_prinsipal_limit extends CI_Model {

    var $table = 'mst_prinsipal_limit';
    var $column = array('r.Cabang','r.Prinsipal','r.Limit','r.Limit_Outstanding','r.Limit_Usulan','r.Keterangan_Usulan','r.Waktu_Usulan','r.Waktu_Approv'); //set column field database for datatable searchable just firstname , lastname , address are searchable
    var $order = array('r.Cabang' => 'asc'); // default order

    public function __construct()
    {
            parent::__construct();
            $this->cabang = $this->session->userdata('cabang');
            // Your own constructor code
    }

// MODULES PRINSIPAL LIMIT
    public function listCabang()
    {   
        $query = $this->db->query("select Kode,Cabang from mst_cabang where id order by id asc")->result();

        return $query;
    }

     public function listPrinsipal()
    {   
        $query = $this->db->query("select Kode,Prinsipal from mst_prinsipal where id order by id asc")->result();

        return $query;
    }

     public function getLimit($prinsipal = NULL,$cabang = NULL)
    {   
        $query = $this->db->query("select mst_limit_pembelian.Bulan,mst_limit_pembelian.Prinsipal, Supplier1 as Supplier,Limit_Beli, Sisa_Limit 
                                    from mst_limit_pembelian left join mst_prinsipal on 
                                         mst_limit_pembelian.Prinsipal = mst_prinsipal.Prinsipal
                                    where mst_limit_pembelian.Cabang = '".$cabang."' and 
                                          mst_limit_pembelian.Prinsipal =  '".$prinsipal."' 
                                          order by mst_limit_pembelian.Bulan Desc LIMIT 1")->result();
        return $query;
    }

    public function getLimitOutstanding($prinsipal = NULL,$cabang = NULL)
    {
        // $query = $this->db->query("select sum(Value_PO) as po from trs_po_detail where Cabang = '".$cabang."' and Status_PO !='Batal' and Status_PO ='Open'and Prinsipal = '".$prinsipal."'")->result(); 
         $query = $this->db->query("SELECT SUM(vBeli) AS po 
                                    FROM (SELECT (SUM(Value_Usulan)) AS vBeli 
                                          FROM `trs_usulan_beli_detail` 
                                          WHERE cabang='".$cabang."' AND 
                                                Prinsipal IN ('".$prinsipal."') AND 
                                                (status_usulan NOT IN ('Tolak','Closed') OR 
                                             trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header)) AND 
                                                MONTH(Tgl_Usulan) IN (".date('m').") AND 
                                                YEAR(Tgl_Usulan) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                          UNION 
                                          SELECT (SUM(Value_PR)) AS vBeli 
                                           FROM `trs_pembelian_detail` 
                                           WHERE cabang='".$cabang."' AND  
                                                 Prinsipal IN ('".$prinsipal."') AND
                                                 (status_pr NOT IN ('Tolak','Closed') OR 
                                                  trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header)) AND 
                                                 MONTH(Tgl_PR) IN (".date('m').") AND 
                                                 YEAR(Tgl_PR) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                         UNION 
                                         SELECT (SUM(Value_PO)) AS vBeli 
                                         FROM `trs_po_detail` 
                                         WHERE cabang='".$cabang."' AND  
                                           Prinsipal IN ('".$prinsipal."') AND 
                                           status_po NOT IN ('Tolak') AND 
                                           MONTH(Tgl_PO) IN (".date('m').") AND 
                                           YEAR(Tgl_PO) = ".date('Y')." 
                                         GROUP BY cabang,Prinsipal)a")->result(); 
        return $query;
    }


    public function listPrinsipalLimit()
    {
        $query = $this->db->query("select mst_prinsipal_limit.*, mst_cabang.Cabang, mst_prinsipal.Prinsipal from mst_cabang, mst_prinsipal_limit, mst_prinsipal where mst_prinsipal_limit.Id_cabang=mst_cabang.id and mst_prinsipal_limit.Id_prinsipal=mst_prinsipal.id order by mst_prinsipal_limit.id asc")->result();

        return $query;
    }

    public function listPrinsipalLimitbeli($search=NULL)
    {
        // $byID = "";
        // if(!empty($prinsipal)){
        //     $byID = "and Prinsipal = '".$prinsipal."'";
        // }
        $query = $this->db->query("select * from mst_limit_pembelian where Cabang = '".$this->cabang."' $search order by Bulan Desc")->result();
        return $query;
    }

     public function getlistdatalimit($search=NULL)
    {
        
        $query = $this->db->query("select * from mst_prinsipal_limit where status = 'Usulan'  and Cabang = '".$this->cabang."' $search order by Waktu_Usulan Desc, mst_prinsipal_limit.id asc")->result();
        return $query;
    }
    public function savePrinsipalLimit($params = NULL)
    {
        $Limit = floatval(str_replace(',', '', $params->Limit));
        $Limit_Outstanding = floatval(str_replace(',', '', $params->Limit_Outstanding));
        $Limit_Usulan = floatval(str_replace(',', '', $params->Limit_Usulan));
        $ext = $params->Prinsipal;
        $exp = explode("~",$ext);
        $prinsipal =$exp[0];
        $valid = false;
            $this->db->set("id", $params->id);
            $this->db->set("Cabang", $this->cabang);         
            $this->db->set("Prinsipal", $prinsipal);
            $this->db->set("Limit", $Limit);  
            $this->db->set("Limit_Outstanding", $Limit_Outstanding); 
            $this->db->set("Limit_Usulan", $Limit_Usulan);   
            $this->db->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db->set("Status", "Usulan");    
            $this->db->set("Created_by", $this->session->userdata('username'));
            $this->db->set("Created_at", date("Y-m-d H:i:s"));
            $valid = $this->db->insert('mst_prinsipal_limit'); 
        
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $this->db2->set("id", $params->id);
            $this->db2->set("Cabang", $this->cabang);         
            $this->db2->set("Prinsipal", $prinsipal);
            $this->db2->set("Limit", $Limit);  
            $this->db2->set("Limit_Outstanding", $Limit_Outstanding); 
            $this->db2->set("Limit_Usulan", $Limit_Usulan);   
            $this->db2->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db2->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db2->set("Status", "Usulan");    
            $this->db2->set("Created_by", $this->session->userdata('username'));
            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
            $valid = $this->db2->insert('mst_prinsipal_limit'); 
        }
    return $valid;
    }


    public function updatePrinsipalLimit($params = NULL)
    {
        $valid = false; 
            $this->db->set("Cabang", $params->Cabang);         
            $this->db->set("Prinsipal", $params->Prinsipal);
            $this->db->set("Limit", $params->Limit);  
            $this->db->set("Limit_Outstanding", $params->Limit_Outstanding); 
            $this->db->set("Limit_Usulan", $params->Limit_Usulan);   
            $this->db->set("Keterangan_Usulan", $params->Keterangan_Usulan);      
            $this->db->set("Waktu_Usulan", date("Y-m-d"));
            $this->db->set("Status", "Usulan");
            $this->db->set("Updated_by", $this->session->userdata('username'));
            $this->db->set("Updated_at", date("Y-m-d H:i:s"));
            $this->db->where("id", $params->id);
            $valid = $this->db->update('mst_prinsipal_limit'); 
            $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $this->db2->set("id", $params->id);
            $this->db2->set("Cabang", $this->cabang);         
            $this->db2->set("Prinsipal", $params->Prinsipal);
            $this->db2->set("Limit", $params->Limit);  
            $this->db2->set("Limit_Outstanding", $params->Limit_Outstanding); 
            $this->db2->set("Limit_Usulan", $params->Limit_Usulan);   
            $this->db2->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db2->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db2->set("Status", "Usulan");    
            $this->db2->set("Updated_by", $this->session->userdata('username'));
            $this->db2->set("Updated_at", date("Y-m-d H:i:s"));
            $this->db->where("id", $params->id);
            $valid = $this->db2->update('mst_prinsipal_limit'); 
        }
        return $valid;

    }

    public function getById($id = NULL)
    {
        $query = $this->db->query("select * from mst_limit_pembelian where MONTH(Bulan) ='".date('m')."' and Cabang = '".$this->cabang."'");  
        $num = $query->num_rows();
        if($num == 0){
            $this->db2 = $this->load->database('pusat', TRUE); 
            $query2 = $this->db2->query("select * from mst_limit_pembelian where MONTH(Bulan) ='".date('m')."' and Cabang = '".$this->cabang."'")->result();
            foreach($query2 as $r) { // loop over results
                $valid = $this->db->insert('mst_limit_pembelian', $r); // insert each row to another table
            }
        }else{
            $this->db2 = $this->load->database('pusat', TRUE); 
            $bln = date('m');
            $query2 = $this->db2->query("select * from mst_limit_pembelian where MONTH(Bulan) ='".$bln."' and Cabang = '".$this->cabang."'")->result();
            foreach($query2 as $r) { // loop over results
                $this->db->set('Limit_Beli', $r->Limit_Beli); 
                $this->db->where('Prinsipal', $r->Prinsipal); 
                $this->db->where('Cabang', $this->cabang); 
                $this->db->where('Bulan', $r->Bulan); 
                $valid= $this->db->update("mst_limit_pembelian");
                // insert each row to another table
            }

        }
        return $valid;     
    }

     public function getdataById($id)
    {
        $query = $this->db->query("select * from mst_prinsipal_limit where Cabang = '".$this->cabang."' and id ='".$id."'")->row();
        return $query; 
    }

    public function deletePrinsipalLimit($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('mst_prinsipal_limit');
    }
}