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
            $this->load->library('owner');
            $this->load->model('Model_main');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->group = $this->session->userdata('userGroup');
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

    public function getlistdatalimitcabang($search=NULL)
    {
        
        $query = $this->db->query("select * from mst_cabang_limit where  Cabang = '".$this->cabang."' $search order by Waktu_Usulan Desc")->result();
        return $query;
    }

    public function getLimitCabang()
    {   
        $query = $this->db->query("select mst_limit_pembelian_cabang.Bulan,Limit_Beli, Sisa_Limit 
                                    from mst_limit_pembelian_cabang
                                    where mst_limit_pembelian_cabang.Cabang = '".$this->cabang."'
                                          order by mst_limit_pembelian_cabang.Bulan Desc LIMIT 1")->row();
        return $query;
    }

    public function getLimitCabangOutstanding()
    {
         $query = $this->db->query("SELECT SUM(vBeli) AS vBeli
                                    FROM (SELECT (SUM(Value_Usulan)) AS vBeli 
                                          FROM `trs_usulan_beli_detail` 
                                          WHERE cabang='".$this->cabang."' AND  
                                              Prinsipal NOT IN ('ANDALAN','SOLAS REG','SOLAS N.REG') AND
                                                status_usulan NOT IN ('Tolak','Closed') and
                                             trs_usulan_beli_detail.`No_Usulan` NOT IN (SELECT IFNULL(trs_po_header.`No_Usulan`,'') AS 'No_Usulan' FROM trs_po_header where Prinsipal NOT IN ('ANDALAN','SOLAS REG','SOLAS N.REG')) AND 
                                                MONTH(Tgl_Usulan) IN (".date('m').") AND 
                                                YEAR(Tgl_Usulan) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                          UNION 
                                          SELECT (SUM(Value_PR)) AS vBeli 
                                           FROM `trs_pembelian_detail` 
                                           WHERE cabang='".$this->cabang."' AND  
                                                Prinsipal NOT IN ('ANDALAN','SOLAS REG','SOLAS N.REG') AND
                                                 status_pr NOT IN ('Tolak','Closed','Batal') and 
                                                  trs_pembelian_detail.`No_PR` NOT IN (SELECT IFNULL(trs_po_header.`No_PR`,'') AS 'No_PR' FROM trs_po_header where Prinsipal NOT IN ('ANDALAN','SOLAS REG','SOLAS N.REG')) AND 
                                                 MONTH(Tgl_PR) IN (".date('m').") AND 
                                                 YEAR(Tgl_PR) = ".date('Y')." 
                                          GROUP BY cabang,Prinsipal 
                                         UNION 
                                         SELECT (SUM(Value_PO)) AS vBeli 
                                         FROM `trs_po_detail` 
                                         WHERE cabang='".$this->cabang."' AND  
                                         Prinsipal NOT IN ('ANDALAN','SOLAS REG','SOLAS N.REG') AND
                                           status_po NOT IN ('Tolak','Batal','Closed') AND 
                                           MONTH(Tgl_PO) IN (".date('m').") AND 
                                           YEAR(Tgl_PO) = ".date('Y')." 
                                         GROUP BY cabang,Prinsipal)a 
                                    ")->row(); 
        return $query;
    }

    public function saveCabangLimit($params = NULL)
    {
        $Limit = floatval(str_replace(',', '', $params->Limit));
        $Limit_Outstanding = floatval(str_replace(',', '', $params->Limit_Outstanding));
        $Limit_Usulan = floatval(str_replace(',', '', $params->Limit_Usulan));
        $valid = false;
            // $this->db->set("id", $params->id);
            $this->db->set("Cabang", $this->cabang);         
            $this->db->set("Limit", $Limit);  
            $this->db->set("Limit_Outstanding", $Limit_Outstanding); 
            $this->db->set("Limit_Usulan", $Limit_Usulan);   
            $this->db->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db->set("Status", "Usulan");    
            $this->db->set("Created_by", $this->session->userdata('username'));
            $this->db->set("Created_at", date("Y-m-d H:i:s"));
            $valid = $this->db->insert('mst_cabang_limit'); 
        
        $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            // $this->db2->set("id", $params->id);
            $this->db2->set("Cabang", $this->cabang);         
            $this->db2->set("Limit", $Limit);  
            $this->db2->set("Limit_Outstanding", $Limit_Outstanding); 
            $this->db2->set("Limit_Usulan", $Limit_Usulan);   
            $this->db2->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db2->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db2->set("Status", "Usulan");    
            $this->db2->set("Created_by", $this->session->userdata('username'));
            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
            $valid = $this->db2->insert('mst_cabang_limit'); 
        }
    return $valid;
    }
    public function getdataCabangById($id)
    {
        $query = $this->db->query("select * from mst_cabang_limit where Cabang = '".$this->cabang."' and id ='".$id."'")->row();
        return $query; 
    }
    public function updateDataCabangLimit($params = NULL)
    {
        $valid = false; 
            $this->db->set("Cabang", $params->Cabang);         
            $this->db->set("Limit", $params->Limit);  
            $this->db->set("Limit_Outstanding", $params->Limit_Outstanding); 
            $this->db->set("Limit_Usulan", $params->Limit_Usulan);   
            $this->db->set("Keterangan_Usulan", $params->Keterangan_Usulan);      
            $this->db->set("Waktu_Usulan", date("Y-m-d"));
            $this->db->set("Status", "Usulan");
            $this->db->set("Updated_by", $this->session->userdata('username'));
            $this->db->set("Updated_at", date("Y-m-d H:i:s"));
            $this->db->where("id", $params->id);
            $valid = $this->db->update('mst_cabang_limit'); 
            $this->db2 = $this->load->database('pusat', TRUE);  
        if ($this->db2->conn_id == TRUE) {
            $this->db2->set("id", $params->id);
            $this->db2->set("Cabang", $this->cabang);         
            $this->db2->set("Limit", $params->Limit);  
            $this->db2->set("Limit_Outstanding", $params->Limit_Outstanding); 
            $this->db2->set("Limit_Usulan", $params->Limit_Usulan);   
            $this->db2->set("Keterangan_Usulan", $params->Keterangan_Usulan); 
            $this->db2->set("Waktu_Usulan", date("Y-m-d"));  
            $this->db2->set("Status", "Usulan");    
            $this->db2->set("Updated_by", $this->session->userdata('username'));
            $this->db2->set("Updated_at", date("Y-m-d H:i:s"));
            $this->db->where("id", $params->id);
            $valid = $this->db2->update('mst_cabang_limit'); 
        }
        return $valid;

    }
    public function listDatalimitbelicabang($no = null)
    {   
       if($no == 'all'){
            $query = $this->db->query("select * 
                                from mst_cabang_limit 
                                where Cabang = '".$this->session->userdata('cabang')."' 
                                order by Waktu_Usulan Desc");
       }else{
            $query = $this->db->query("select * 
                                from mst_cabang_limit 
                                where Cabang = '".$this->session->userdata('cabang')."' and 
                                      (ifnull(Approve_BM,'')= '')
                                order by Waktu_Usulan Desc");
       }
        
        return $query;
    }
    public function prosesDatalimitbelicabang($No = NULL, $status = NULL)
    {
        $this->db2 = $this->load->database('pusat', TRUE);
        $message = "";
        $update = "";
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
                $update = $this->db->query("update mst_cabang_limit 
                                            set    status = 'Usulan',
                                                   Approve_BM = '".$approve."', 
                                                   date_BM = '".$date_approve_bm."',
                                                   user_BM = '".$user_bm."' where id = '".$No."'  and ifnull(Approve_BM,'') =''"); 
                 if ($this->db2->conn_id == TRUE) {
                        $update = $this->db2->query("update mst_cabang_limit 
                                                    set    status = 'Usulan',
                                                           Approve_BM = '".$approve."', 
                                                           date_BM = '".$date_approve_bm."',
                                                           user_BM = '".$user_bm."' where id = '".$No."' and Cabang = '".$this->cabang."'  and ifnull(Approve_BM,'') =''");
                    } 
                if($update){
                    $message = $message." BM Sukses";
                }else{
                    $message = $message." BM Gagal";
                }
                
         }
        $data = ["update" =>$update,"message"=>$message];
        return $data;
    }

    public function updateDataLimitBeliCabangPusat()
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
                $nomor = $this->db2->query("select * from mst_cabang_limit where Cabang = '".$this->cabang."'")->result();
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
                    $this->db->where('id', $no->id);
                    $this->db->where('Cabang', $no->Cabang);
                    $this->db->update('mst_cabang_limit'); // insert each row to another table

                }

                $data = $this->db->query("select * from mst_cabang_limit where Cabang = '".$this->cabang."' and status ='Usulan'")->result();
                foreach ($data as $list) {
                    if($list->Approve_pusat =="Approve"){
                        $x = $this->db->query("select * from mst_limit_pembelian_cabang where Bulan ='".date('Y-m-t')."'  and Cabang = '".$list->Cabang."' limit 1");
                        $limitbeli = $x->row()->Limit_Beli;
                        $totallimit = $limitbeli + $list->Limit_Usulan;
                        if($x->num_rows() < 1){
                            $this->db->set("Bulan", $enddate);
                            $this->db->set("Cabang", $list->Cabang);         
                            // $this->db->set("Prinsipal", $list->Prinsipal);
                            $this->db->set("Limit_Beli",$list->Limit_Usulan);  
                            $this->db->set("Created_by", $this->session->userdata('username'));
                            $this->db->set("Created_at", date("Y-m-d H:i:s"));
                            $valid = $this->db->insert('mst_limit_pembelian_cabang'); 
                        }else{
                            $this->db->set("Limit_Beli", $totallimit);  
                            $this->db->set("Updated_At", $this->session->userdata('username'));
                            $this->db->set("Created_at", date("Y-m-d H:i:s"));
                            $this->db->where("Bulan", date('Y-m-t'));
                            $this->db->where("Cabang", $list->Cabang);         
                            $valid = $this->db->update('mst_limit_pembelian_cabang'); 
                        }
                        if($valid){
                            $this->db->query("update mst_cabang_limit set status ='Closed' where id = '".$list->id."'");
                        }
                        
                        // $this->db2 = $this->load->database('pusat', TRUE); 
                        $y = $this->db2->query("select * from mst_limit_pembelian_cabang where Bulan ='".date('Y-m-t')."'  and Cabang = '".$list->Cabang."'");
                        $limitbeli = $y->row()->Limit_Beli;
                        $totallimit = $limitbeli + $list->Limit_Usulan;
                         if($y->num_rows() < 1){
                            $this->db2->set("Bulan", $enddate);
                            $this->db2->set("Cabang", $list->Cabang);         
                            $this->db2->set("Limit_Beli", $list->Limit_Usulan);  
                            $this->db2->set("Created_by", $this->session->userdata('username'));
                            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
                            $valid = $this->db2->insert('mst_limit_pembelian_cabang'); 
                        }else{
                            $this->db2->set("Limit_Beli", $totallimit);  
                            $this->db2->set("Updated_At", $this->session->userdata('username'));
                            $this->db2->set("Created_at", date("Y-m-d H:i:s"));
                            $this->db2->where("Bulan", date('Y-m-t'));
                            $this->db2->where("Cabang", $list->Cabang);         
                            $valid = $this->db2->update('mst_limit_pembelian_cabang'); 
                        }
                        if($valid){
                            $this->db2->query("update mst_cabang_limit set status ='Closed' where id = '".$list->id."'");
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

    public function listCabangLimitbeli($search=NULL)
    {
        $query = $this->db->query("select * from mst_limit_pembelian_cabang where Cabang = '".$this->cabang."' $search order by Bulan Desc")->result();
        return $query;
    }

    public function deleteCabangLimit($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('mst_cabang_limit');
    }

    public function prosesKirimDataLimitCabang($id = NULL)
    {   
        $this->db2 = $this->load->database('pusat', TRUE);      
        if ($this->db2->conn_id == TRUE) {
            $update = $this->db->query("update mst_cabang_limit set statuspusat = 'Berhasil' where id = '".$id."'");
            $query = $this->db->query("select * from mst_cabang_limit where id = '".$id."'")->row();
            $cek = $this->db2->query("select * from mst_cabang_limit where id = '".$id."'")->num_rows();
            if ($cek == 0) {
                $this->db2->insert('mst_cabang_limit', $query); // insert each row to another table
            }
            else{
                $this->db2->where('id', $id);
                $this->db2->update('mst_cabang_limit', $query);
            }


            return TRUE;
        }
        else{
            $update = $this->db->query("update mst_cabang_limit set statuspusat = 'Gagal' where id = '".$id."'");
            return FALSE;
        }
    }
}