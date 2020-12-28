<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","2048M");

class Model_GPSCall extends CI_Model
{
	public function __construct()
    {
        parent::__construct();
    }

    public function get_stok(){
    	$query = $this->db->query("select Cabang, NamaPrinsipal,KodeProduk,NamaProduk,UnitStok,ValueStok,HNA from trs_invsum")->result();

    	return $query;
    }

    public function get_pelanggan(){
    	$query = $this->db->query("select Cabang,Kode,Pelanggan,Tipe_Pelanggan,Alamat,Limit_Kredit,TOP from mst_pelanggan")->result();

    	return $query;
    }

    public function get_rute_salesman($kode = null){
        $query = $this->db->query("select * from mst_rute_salesman where KodeSalesman = '".$kode."'")->result();

        return $query;
    }

    public function get_piutang(){
        $query = $this->db->query("SELECT b.Cabang AS cabang,aa.Pelanggan AS kodePelanggan,b.Pelanggan AS namaPelanggan,b.Alamat AS alamatPelanggan,
            aa.Piu AS saldoPiutang,b.Limit_Kredit AS limitKredit,b.TOP AS top
            FROM (
            SELECT Pelanggan,ROUND(SUM(Saldo) , 0) AS Piu FROM trs_faktur GROUP BY Pelanggan
        )aa LEFT JOIN mst_pelanggan b ON aa.Pelanggan = b.Kode
        ORDER BY Piu DESC, NamaPelanggan ASC
        ;")->result();

        return $query;
    }
}