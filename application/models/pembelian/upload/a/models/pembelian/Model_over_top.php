<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","512M");

class Model_over_top extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            // Your own constructor code
    }

    public function listData_overTop($search,$cek)
    {
    	/*$tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];*/
        $cabang = $this->session->userdata('cabang');
        $where = "";
        if ($cek != "semua") {
          $where = " AND (alasan_jto = '' OR alasan_jto is null)";
        }

        $query = $this->db->query("SELECT * FROM (
                    SELECT Cabang,NoFaktur,Tglfaktur,
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END AS
                          TglJtoFakturHit,
                          DATE(NOW()) AS tgl,
                          Pelanggan,
                          NamaPelanggan,
                          TipePelanggan,
                          Salesman,
                          NamaSalesman,
                          ( DATEDIFF(CURRENT_DATE(), Tglfaktur) ) AS umurJT0,
                          TOP,CaraBayar,Acu,Acu2,
                          Total,
                          (IFNULL(saldo,0)+IFNULL(saldo_giro,0)) AS saldo,
                          IFNULL(alasan_jto,'') AS alasan_jto
                         FROM trs_faktur 
                          WHERE cabang = '$cabang' $where  AND (IFNULL(saldo,0)+IFNULL(saldo_giro,0)) > 0 
                          AND 
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END <=DATE(NOW()) 
                          ORDER BY 
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END DESC, 
                          TglFaktur ASC ) z $search ORDER BY umurJT0 DESC"); 

        return $query;
    }

    public function listData_overTop2($search,$cek,$length,$start)
    {
      
        $cabang = $this->session->userdata('cabang');

        $where = $where2 = "";
        if ($cek != "semua") {
          $where = " AND (alasan_jto = '' OR alasan_jto is null)";
        }

        if ($length != -1) {
          $where2 = " LIMIT $start , $length";
        }



        $query = $this->db->query("SELECT * FROM (
                      SELECT Cabang,NoFaktur,Tglfaktur,
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END AS
                          TglJtoFakturHit,
                          DATE(NOW()) AS tgl,
                          Pelanggan,
                          NamaPelanggan,
                          TipePelanggan,
                          Salesman,
                          NamaSalesman,
                          ( DATEDIFF(CURRENT_DATE(), Tglfaktur) ) AS umurJT0,
                          TOP,CaraBayar,Acu,Acu2,
                          Total,
                          (IFNULL(saldo,0)+IFNULL(saldo_giro,0)) AS saldo,
                          IFNULL(alasan_jto,'') AS alasan_jto
                         FROM trs_faktur 
                          WHERE cabang = '$cabang' $where  AND (IFNULL(saldo,0)+IFNULL(saldo_giro,0)) > 0 
                          AND 
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END <=DATE(NOW()) 
                          ORDER BY 
                          CASE WHEN TglJtoFaktur='0000-00-00'
                            THEN Tglfaktur + INTERVAL TOP DAY
                            ELSE TglJtoFaktur
                          END DESC, 
                          TglFaktur ASC $where2 ) z $search"); 

        return $query;
    }

    public function listData_alasan()
    {
        $query = $this->db->query("SELECT * FROM mst_alasan_jto")->result(); 

        return $query;
    }

    function update_all($data){
        $cabang = $this->session->userdata('cabang');
        $alasan = explode("||", str_replace("undefined||", "",  substr($data['alasan'], 0,-2)));
        $no_do = explode("||", str_replace("undefined||", "",  substr(str_replace("-", "/",  $data['no']), 0,-2)));
        
        $no = 0;
        
         foreach($alasan as $i) {
            $this->db->set("modified_by", $this->user);
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("alasan_jto",$i);
            $this->db->where("Cabang",$cabang);
            $this->db->where("NoFaktur",$no_do[$no]);
            $result = $this->db->update("trs_faktur");
            $no++;
        }
        return $result;

    }

    function update_one($no,$alasan){
        $cabang = $this->session->userdata('cabang');
        
            $this->db->set("modified_by", $this->user);
            $this->db->set("modified_at", date("Y-m-d H:i:s"));
            $this->db->set("alasan_jto",$alasan);
            $this->db->where("Cabang",$cabang);
            $this->db->where("NoFaktur",$no);
            $result = $this->db->update("trs_faktur");
            
        return $result;

    }

}