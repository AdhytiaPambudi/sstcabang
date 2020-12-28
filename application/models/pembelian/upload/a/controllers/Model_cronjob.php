<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set("memory_limit","1024M");
class Model_cronjob extends CI_Model {

    public function __construct()
    {
            parent::__construct();

            $data = $this->db->query("SELECT b.cabang,LOWER(b.`Kode`) Kode FROM mst_closing a JOIN mst_cabang b
            			ON a.cabang = b.cabang");

            if ($data->num_rows() > 0) {
            	echo "<br>".$this->cabang = $data->row("cabang");
	            echo "<br>".$this->kd_cab = $data->row("Kode");
	            echo "<br>";
            }else{
            	echo "gagal";
            	exit();
            }
            
    }

    function clear_history(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	// clear histori
            $query = $this->db2->query("UPDATE histori_cronjob SET Tanggal = NOW() , ket_gagal = '' , ket_berhasil = '', count_table = 0 WHERE Cabang = '".$this->cabang."' ");


        }
    }

    function update_trs_buku_giro(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	// 1 get data
        	$querytrs_buku_giro = $this->db->query("SELECT * FROM trs_buku_giro WHERE date(Tanggal) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");

        	if ($querytrs_buku_giro->num_rows() > 0) {
				  $string_trs_buku_giro="";
				  $jumlahbaru = 0;
				  $var_dump="";
				  foreach ($querytrs_buku_giro->result_array() as $row) {

				    $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
				    $Tanggal          = ($row['Tanggal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal'])."'";
				    $TimeGiro         = ($row['TimeGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeGiro'])."'";
				    $TglJTOGiro       = ($row['TglJTOGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJTOGiro'])."'";
				    $Bank             = ($row['Bank'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bank'])."'";
				    $KodePelanggan    = ($row['KodePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePelanggan'])."'";
				    $NamaPelanggan    = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
				    $KodeKaryawan     = ($row['KodeKaryawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeKaryawan'])."'";
				    $NamaKaryawan     = ($row['NamaKaryawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaKaryawan'])."'";
				    $NoGiro           = ($row['NoGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoGiro'])."'";
				    $ValueGiro        = ($row['ValueGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueGiro'])."'";
				    $StatusGiro       = ($row['StatusGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusGiro'])."'";
				    $TglCairGiro      = ($row['TglCairGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglCairGiro'])."'";
				    $NoVoucher        = ($row['NoVoucher'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoVoucher'])."'";
				    $JurnalID         = ($row['JurnalID'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['JurnalID'])."'";
				    $Transaksi        = ($row['Transaksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Transaksi'])."'";
				    $create_at        = ($row['create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_at'])."'";
				    $create_by        = ($row['create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_by'])."'";
				    $modified_at      = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
				    $modified_by      = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
				    $TglTolakGiro     = ($row['TglTolakGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglTolakGiro'])."'";
				    $max_tolak        = ($row['max_tolak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['max_tolak'])."'";

				    $string_trs_buku_giro .=  "(
				    ".$Cabang.",".$Tanggal.",".$TimeGiro.",".$TglJTOGiro.",".$Bank.",".$KodePelanggan.",".$NamaPelanggan.",".$KodeKaryawan.",".$NamaKaryawan.",".$NoGiro.",".$ValueGiro.",".$StatusGiro.",".$TglCairGiro.",".$NoVoucher.",".$JurnalID.",".$Transaksi.",".$create_at.",".$create_by.",".$modified_at.",".$modified_by.",".$TglTolakGiro.",".$max_tolak."),";

				    $jumlahbaru++;
				  }

				  $var_dump=rtrim($string_trs_buku_giro,",");

				  /*$this->db2->query("DELETE FROM  trs_buku_giro_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(Tanggal,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

				  $querytrs_buku_giroinsert = "REPLACE INTO `trs_buku_giro_".$this->kd_cab."` VALUES ".$var_dump;

				  if(empty($string_trs_buku_giro))  {
				   echo "No insert found <br>";
				   $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_giro') WHERE Cabang = '".$this->cabang."' ");
				 }
				 else{
				  if ($this->db2->query($querytrs_buku_giroinsert) === TRUE) {
				    echo "Berhasil insert ".$jumlahbaru ." trs_buku_giro <br>";

				    $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_giro : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");

				  } else {
				    echo  "gagal insert trs_buku_giro ".$this->kd_cab->error ."<br>";

				    $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_giro') WHERE Cabang = '".$this->cabang."' ");
				  }

				}


			}else{
        		$jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_buku_giro <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_giro : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
        	}
        }
    }

    function update_trs_buku_kasbon(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	$string_trs_buku_kasbon="";
		    $jumlahbaru = 0;
		    $var_dump="";
		    $querytrs_buku_kasbon = $this->db->query("SELECT * FROM trs_buku_kasbon WHERE date(Tanggal) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");

		    if ($querytrs_buku_kasbon->num_rows() > 0) {
		      foreach ($querytrs_buku_kasbon->result_array() as $row) {
		                $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $NoKasbon         = ($row['NoKasbon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoKasbon'])."'";
		                $Tanggal          = ($row['Tanggal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal'])."'";
		                $TimeTransaksi    = ($row['TimeTransaksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeTransaksi'])."'";
		                $KodeKaryawan     = ($row['KodeKaryawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeKaryawan'])."'";
		                $NamaKaryawan     = ($row['NamaKaryawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaKaryawan'])."'";
		                $Keterangan       = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Status           = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $NoVoucher        = ($row['NoVoucher'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoVoucher'])."'";
		                $JurnalID         = ($row['JurnalID'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['JurnalID'])."'";
		                $Transaksi        = ($row['Transaksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Transaksi'])."'";
		                $create_at        = ($row['create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_at'])."'";
		                $create_by        = ($row['create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_by'])."'";
		                $modified_at      = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
		                $modified_by      = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
		                $ValueKasbon      = ($row['ValueKasbon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueKasbon'])."'";
		                $ValueRealisasi   = ($row['ValueRealisasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueRealisasi'])."'";
		                $NoKasbon_Asal    = ($row['NoKasbon_Asal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoKasbon_Asal'])."'";
		                $TanggalClosed    = ($row['TanggalClosed'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TanggalClosed'])."'";
		                $TimeClosed       = ($row['TimeClosed'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeClosed'])."'";

		          $string_trs_buku_kasbon .=  "(
		                            ".$Cabang.",".$NoKasbon.",".$Tanggal.",".$TimeTransaksi.",".$KodeKaryawan.",".$NamaKaryawan.",".$Keterangan.",".$Status.",".$NoVoucher.",".$JurnalID.",".$Transaksi.",".$create_at.",".$create_by.",".$modified_at.",".$modified_by.",".$ValueKasbon.",".$ValueRealisasi.",".$NoKasbon_Asal.",".$TanggalClosed.",".$TimeClosed."),";

		          $jumlahbaru++;
		      }
		      $var_dump=rtrim($string_trs_buku_kasbon,",");
		      /*$this->db2->query("DELETE FROM  trs_buku_kasbon_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(Tanggal,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

		      $querytrs_buku_kasboninsert = "REPLACE INTO `trs_buku_kasbon_".$this->kd_cab."` VALUES ".$var_dump;

		      if(empty($string_trs_buku_kasbon))  {
	      			echo "No insert found <br>";
	      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_kasbon') WHERE Cabang = '".$this->cabang."' ");
		      }
		       else{
		        if ($this->db2->query($querytrs_buku_kasboninsert) === TRUE) {
		            echo "Berhasil insert ".$jumlahbaru ." trs_buku_kasbon <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_kasbon : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
		        } else {
		            echo  "gagal insert trs_buku_kasbon ".$this->db2->error ."<br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_gagal=CONCAT(ket_gagal, ' , trs_buku_kasbon') WHERE Cabang = '".$this->cabang."' ");
		        }

		      }
        	}else{
        		$jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_buku_kasbon <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_kasbon : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
        	}
    	}
    }

    function update_trs_buku_titipan(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_buku_titipan = $this->db->query("SELECT * FROM trs_buku_titipan WHERE date(Tanggal) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_buku_titipan->num_rows() > 0) {
			    $string_trs_buku_titipan="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_buku_titipan->result_array() as $row) {
			                $Cabang          = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
			                $Tanggal         = ($row['Tanggal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal'])."'";
			                $Time            = ($row['Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time'])."'";
			                $NoTitipan       = ($row['NoTitipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoTitipan'])."'";
			                $ValueTitipan    = ($row['ValueTitipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueTitipan'])."'";
			                $Saldo           = ($row['Saldo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Saldo'])."'";
			                $Status          = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
			                $Create_by       = ($row['Create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Create_by'])."'";
			                $Create_at       = ($row['Create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Create_at'])."'";
			                $Modified_by     = ($row['Modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_by'])."'";
			                $Modified_at     = ($row['Modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_at'])."'";

			          $string_trs_buku_titipan .=  "(
			                             ".$Cabang.",".$Tanggal.",".$Time.",".$NoTitipan.",".$ValueTitipan.",".$Saldo.",".$Status.",".$Create_by.",".$Create_at.",".$Modified_by.",".$Modified_at."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_buku_titipan,",");
			      /*$this->db2->query("DELETE FROM  trs_buku_titipan_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(Tanggal,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_buku_titipaninsert = "REPLACE INTO `trs_buku_titipan_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_buku_titipan))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_titipan') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_buku_titipaninsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_buku_titipan<br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_titipan : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_buku_titipan".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_titipan') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			    
			} else {
        		$jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_buku_titipan <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_titipan : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
        	
			}
    	}
    }

    function update_trs_buku_transaksi(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	$querytrs_buku_transaksi = $this->db->query("SELECT * FROM trs_buku_transaksi WHERE date(Tanggal) >= CURDATE() - INTERVAL 3 DAY or date(modified_Time) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_buku_transaksi->num_rows() > 0) {
			    $string_trs_buku_transaksi="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_buku_transaksi->result_array() as $row) {
		                $Buku             = ($row['Buku'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Buku'])."'";
		                $Bank             = ($row['Bank'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bank'])."'";
		                $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Area             = ($row['Area'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Area'])."'";
		                $Jurnal_ID        = ($row['Jurnal_ID'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jurnal_ID'])."'";
		                $Tanggal          = ($row['Tanggal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal'])."'";
		                $Kategori         = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Kategori_2       = ($row['Kategori_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori_2'])."'";
		                $Transaksi        = ($row['Transaksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Transaksi'])."'";
		                $Kode_Transaksi   = ($row['Kode_Transaksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kode_Transaksi'])."'";
		                $Keterangan       = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Ket_2            = ($row['Ket_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ket_2'])."'";
		                $Ket_3            = ($row['Ket_3'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ket_3'])."'";
		                $Ket_4            = ($row['Ket_4'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ket_4'])."'";
		                $Tgl_1            = ($row['Tgl_1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_1'])."'";
		                $Jumlah           = ($row['Jumlah'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jumlah'])."'";
		                $Saldo_Awal       = ($row['Saldo_Awal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Saldo_Awal'])."'";
		                $Debit            = ($row['Debit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Debit'])."'";
		                $Kredit           = ($row['Kredit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kredit'])."'";
		                $Saldo_Akhir      = ($row['Saldo_Akhir'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Saldo_Akhir'])."'";
		                $DR               = ($row['DR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DR'])."'";
		                $CR               = ($row['CR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CR'])."'";
		                $Add              = ($row['Add'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Add'])."'";
		                $Bulan            = ($row['Bulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bulan'])."'";
		                $Counter          = ($row['Counter'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Counter'])."'";
		                $Jurnal_Acu       = ($row['Jurnal_Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jurnal_Acu'])."'";
		                $Jumlah_Acu       = ($row['Jumlah_Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jumlah_Acu'])."'";
		                $Bank_Ref         = ($row['Bank_Ref'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bank_Ref'])."'";
		                $NoGiro           = ($row['NoGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoGiro'])."'";
		                $Validasi_BM      = ($row['Validasi_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Validasi_BM'])."'";
		                $Tanggal_ValBM    = ($row['Tanggal_ValBM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal_ValBM'])."'";
		                $Validasi_Akt_PST = ($row['Validasi_Akt_PST'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Validasi_Akt_PST'])."'";
		                $Tanggal_ValAktP  = ($row['Tanggal_ValAktP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tanggal_ValAktP'])."'";
		                $Dokumen          = ($row['Dokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen'])."'";
		                $Modified_User    = ($row['Modified_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_User'])."'";
		                $Modified_Time    = ($row['Modified_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_Time'])."'";
		                $Added_User       = ($row['Added_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_User'])."'";
		                $Added_Time       = ($row['Added_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_Time'])."'";
		                $Jumlah_dalam_huruf = ($row['Jumlah_dalam_huruf'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jumlah_dalam_huruf'])."'";
		                $TtdBM            = ($row['TtdBM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TtdBM'])."'";
		                $Ket_Cabang       = ($row['Ket_Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ket_Cabang'])."'";
		                $No_Voucher       = ($row['No_Voucher'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Voucher'])."'";
		                $Karyawan         = ($row['Karyawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Karyawan'])."'";
		                $Kode_Karyawan    = ($row['Kode_Karyawan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kode_Karyawan'])."'";
		                $Status           = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $NoDIH            = ($row['NoDIH'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDIH'])."'";
		                $bank_trans       = ($row['bank_trans'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['bank_trans'])."'";
		                $Tipe_Kas         = ($row['Tipe_Kas'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe_Kas'])."'";
		                $jenis_trans      = ($row['jenis_trans'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['jenis_trans'])."'";
		                $Kategori3        = ($row['Kategori3'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori3'])."'";
		                $NoTitipan        = ($row['NoTitipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoTitipan'])."'";
		                $NoKontrabon      = ($row['NoKontrabon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoKontrabon'])."'";
		                $tipe_pph         = ($row['tipe_pph'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tipe_pph'])."'";
		                $nourut           = ($row['nourut'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['nourut'])."'";

			          $string_trs_buku_transaksi .=  "(
			                          ".$Buku.",".$Bank.",".$Cabang.",".$Area.",".$Jurnal_ID.",".$Tanggal.",".$Kategori.",".$Kategori_2.",".$Transaksi.",".$Kode_Transaksi.",".$Keterangan.",".$Ket_2.",".$Ket_3.",".$Ket_4.",".$Tgl_1.",".$Jumlah.",".$Saldo_Awal.",".$Debit.",".$Kredit.",".$Saldo_Akhir.",".$DR.",".$CR.",".$Add.",".$Bulan.",".$Counter.",".$Jurnal_Acu.",".$Jumlah_Acu.",".$Bank_Ref.",".$NoGiro.",".$Validasi_BM.",".$Tanggal_ValBM.",".$Validasi_Akt_PST.",".$Tanggal_ValAktP.",".$Dokumen.",".$Modified_User.",".$Modified_Time.",".$Added_User.",".$Added_Time.",".$Jumlah_dalam_huruf.",".$TtdBM.",".$Ket_Cabang.",".$No_Voucher.",".$Karyawan.",".$Kode_Karyawan.",".$Status.",".$NoDIH.",".$bank_trans.",".$Tipe_Kas.",".$jenis_trans.",".$Kategori3.",".$NoTitipan.",".$NoKontrabon.",".$tipe_pph.",".$nourut."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_buku_transaksi,",");
			      /*$this->db2->query("DELETE FROM  trs_buku_transaksi_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(Tanggal,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_buku_transaksiinsert = "REPLACE INTO `trs_buku_transaksi_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_buku_transaksi))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_transaksi') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_buku_transaksiinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_buku_transaksi <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_transaksi : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_buku_transaksi ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_buku_transaksi') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_buku_transaksi <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_buku_transaksi : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
    	}
    }

    function update_trs_giro(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_giro = $this->db->query("SELECT * FROM trs_giro WHERE date(create_at) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_giro->num_rows() > 0) {
			    $string_trs_giro="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_giro->result_array() as $row) {
		                $Cabang          = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $KodePelanggan   = ($row['KodePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePelanggan'])."'";
		                $NoGiro          = ($row['NoGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoGiro'])."'";
		                $ValueGiro       = ($row['ValueGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueGiro'])."'";
		                $StatusGiro      = ($row['StatusGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusGiro'])."'";
		                $Tolak           = ($row['Tolak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tolak'])."'";
		                $NamaPelanggan   = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
		                $Bank            = ($row['Bank'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bank'])."'";
		                $TglJTO          = ($row['TglJTO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJTO'])."'";
		                $TglCair         = ($row['TglCair'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglCair'])."'";
		                $SisaGiro        = ($row['SisaGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SisaGiro'])."'";
		                $History         = ($row['History'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['History'])."'";
		                $create_at       = ($row['create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_at'])."'";
		                $create_by       = ($row['create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_by'])."'";
		                $modified_at     = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
		                $modified_by     = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";

			          $string_trs_giro .=  "(
			                              ".$Cabang.",".$KodePelanggan.",".$NoGiro.",".$ValueGiro.",".$StatusGiro.",".$Tolak.",".$NamaPelanggan.",".$Bank.",".$TglJTO.",".$TglCair.",".$SisaGiro.",".$History.",".$create_at.",".$create_by.",".$modified_at.",".$modified_by."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_giro,",");
			      /*$this->db2->query("DELETE FROM  trs_giro_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(create_at),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_giroinsert = "REPLACE INTO `trs_giro_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_giro))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , update_trs_giro') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_giroinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_giro <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , update_trs_giro : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_giro ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , update_trs_giro') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." update_trs_giro <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , update_trs_giro : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
    	}
    }

    function update_trs_mutasi_koreksi(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_mutasi_koreksi = $this->db->query("SELECT * FROM trs_mutasi_koreksi WHERE date(tanggal) >= CURDATE() - INTERVAL 3 DAY or date(updated_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_mutasi_koreksi->num_rows() > 0) {
			    $string_trs_mutasi_koreksi="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_mutasi_koreksi->result_array() as $row) {
		                $id             = ($row['id'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['id'])."'";
		                $cabang         = ($row['cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['cabang'])."'";
		                $no_koreksi     = ($row['no_koreksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['no_koreksi'])."'";
		                $tanggal        = ($row['tanggal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tanggal'])."'";
		                $catatan        = ($row['catatan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['catatan'])."'";
		                $dokumen        = ($row['dokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['dokumen'])."'";
		                $noline         = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $produk         = ($row['produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['produk'])."'";
		                $nama_produk    = ($row['nama_produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['nama_produk'])."'";
		                $qty            = ($row['qty'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['qty'])."'";
		                $harga          = ($row['harga'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['harga'])."'";
		                $value          = ($row['value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['value'])."'";
		                $batch_detail   = ($row['batch_detail'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['batch_detail'])."'";
		                $batch          = ($row['batch'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['batch'])."'";
		                $exp_date       = ($row['exp_date'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['exp_date'])."'";
		                $stok           = ($row['stok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['stok'])."'";
		                $status         = ($row['status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status'])."'";
		                $tipe_koreksi   = ($row['tipe_koreksi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tipe_koreksi'])."'";
		                $tgl_approve    = ($row['tgl_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgl_approve'])."'";
		                $created_by     = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
		                $created_at     = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
		                $updated_by     = ($row['updated_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_by'])."'";
		                $updated_at     = ($row['updated_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_at'])."'";
		                $Approve_BM     = ($row['Approve_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Approve_BM'])."'";
		                $date_BM        = ($row['date_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['date_BM'])."'";
		                $user_BM        = ($row['user_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_BM'])."'";
		                $Approve_pusat  = ($row['Approve_pusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Approve_pusat'])."'";
		                $date_pusat     = ($row['date_pusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['date_pusat'])."'";
		                $user_pusat     = ($row['user_pusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_pusat'])."'";
		                $NoDocStok      = ($row['NoDocStok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDocStok'])."'";
		                $NoDokumen      = ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
		                $gudang         = ($row['gudang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['gudang'])."'";

			          $string_trs_mutasi_koreksi .=  "(
			                              ".$id.",".$cabang.",".$no_koreksi.",".$tanggal.",".$catatan.",".$dokumen.",".$noline.",".$produk.",".$nama_produk.",".$qty.",".$harga.",".$value.",".$batch_detail.",".$batch.",".$exp_date.",".$stok.",".$status.",".$tipe_koreksi.",".$tgl_approve.",".$created_by.",".$created_at.",".$updated_by.",".$updated_at.",".$Approve_BM.",".$date_BM.",".$user_BM.",".$Approve_pusat.",".$date_pusat.",".$user_pusat.",".$NoDocStok.",".$NoDokumen.",".$gudang."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_mutasi_koreksi,",");
			      /*$this->db2->query("DELETE FROM  trs_mutasi_koreksi_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(tanggal),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_mutasi_koreksiinsert = "REPLACE INTO `trs_mutasi_koreksi_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_mutasi_koreksi))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_mutasi_koreksi') WHERE Cabang = '".$this->cabang."' ");
			      }else{
			        if ($this->db2->query($querytrs_mutasi_koreksiinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_mutasi_koreksi<br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_mutasi_koreksi : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_mutasi_koreksi".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_mutasi_koreksi') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
    		}else{
        		$jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_mutasi_koreksi <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_mutasi_koreksi : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
        	}
   	 	}
   	}

    function update_trs_pelunasan_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_pelunasan_detail = $this->db->query("SELECT * FROM trs_pelunasan_detail WHERE date(TglPelunasan) >= CURDATE() - INTERVAL 3 DAY  or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert  =============================
			if ($querytrs_pelunasan_detail->num_rows() > 0) {
			    $string_trs_pelunasan_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_pelunasan_detail->result_array() as $row) {
		                $Cabang        = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $NoDIH         = ($row['NoDIH'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDIH'])."'";
		                $NomorFaktur   = ($row['NomorFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NomorFaktur'])."'";
		                $noline        = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $Area          = ($row['Area'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Area'])."'";
		                $UmurFaktur    = ($row['UmurFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UmurFaktur'])."'";
		                $UmurLunas     = ($row['UmurLunas'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UmurLunas'])."'";
		                $TglFaktur     = ($row['TglFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglFaktur'])."'";
		                $TglPelunasan  = ($row['TglPelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglPelunasan'])."'";
		                $KodePenagih   = ($row['KodePenagih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePenagih'])."'";
		                $NamaPenagih   = ($row['NamaPenagih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPenagih'])."'";
		                $KodePelanggan = ($row['KodePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePelanggan'])."'";
		                $NamaPelanggan = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
		                $KodeSalesman  = ($row['KodeSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeSalesman'])."'";
		                $NamaSalesman  = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
		                $ValueFaktur   = ($row['ValueFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueFaktur'])."'";
		                $Cicilan       = ($row['Cicilan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cicilan'])."'";
		                $SaldoFaktur   = ($row['SaldoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SaldoFaktur'])."'";
		                $TipePelunasan = ($row['TipePelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelunasan'])."'";
		                $Status        = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $ValuePelunasan= ($row['ValuePelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValuePelunasan'])."'";
		                $SaldoAkhir    = ($row['SaldoAkhir'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SaldoAkhir'])."'";
		                $Giro          = ($row['Giro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Giro'])."'";
		                $TglGiroCair   = ($row['TglGiroCair'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglGiroCair'])."'";
		                $ValueGiro     = ($row['ValueGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueGiro'])."'";
		                $create_at     = ($row['create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_at'])."'";
		                $create_by     = ($row['create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_by'])."'";
		                $modified_at   = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
		                $modified_by   = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
		                $bank          = ($row['bank'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['bank'])."'";
		                $status_titipan= ($row['status_titipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_titipan'])."'";
		                $No_Titipan    = ($row['No_Titipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Titipan'])."'";
		                $status_tambahan= ($row['status_tambahan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_tambahan'])."'";
		                $value_pembulatan= ($row['value_pembulatan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['value_pembulatan'])."'";
		                $value_transfer  = ($row['value_transfer'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['value_transfer'])."'";
		                $materai         = ($row['materai'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['materai'])."'";
		                $TipeDokumen     = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
		                $NoNTPN          = ($row['NoNTPN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoNTPN'])."'";
		                $status_DIH      = ($row['status_DIH'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_DIH'])."'";
		                $keterangan      = ($row['keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['keterangan'])."'";
		                $acu2            = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";

			          $string_trs_pelunasan_detail .=  "(
			                              ".$Cabang.",".$NoDIH.",".$NomorFaktur.",".$noline.",".$Area.",".$UmurFaktur.",".$UmurLunas.",".$TglFaktur.",".$TglPelunasan.",".$KodePenagih.",".$NamaPenagih.",".$KodePelanggan.",".$NamaPelanggan.",".$KodeSalesman.",".$NamaSalesman.",".$ValueFaktur.",".$Cicilan.",".$SaldoFaktur.",".$TipePelunasan.",".$Status.",".$ValuePelunasan.",".$SaldoAkhir.",".$Giro.",".$TglGiroCair.",".$ValueGiro.",".$create_at.",".$create_by.",".$modified_at.",".$modified_by.",".$bank.",".$status_titipan.",".$No_Titipan.",".$status_tambahan.",".$value_pembulatan.",".$value_transfer.",".$materai.",".$TipeDokumen.",".$NoNTPN.",".$status_DIH.",".$keterangan.",".$acu2."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_pelunasan_detail,",");
			      /*$this->db2->query("DELETE FROM  trs_pelunasan_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(TglPelunasan),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_pelunasan_detailinsert = "REPLACE INTO `trs_pelunasan_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_pelunasan_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_pelunasan_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_pelunasan_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_pelunasan_detail<br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_pelunasan_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_pelunasan_detail".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_pelunasan_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_pelunasan_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_pelunasan_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_pelunasan_giro_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_pelunasan_giro_detail = $this->db->query("SELECT * FROM trs_pelunasan_giro_detail WHERE date(TglPelunasan) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert  =============================
			if ($querytrs_pelunasan_giro_detail->num_rows() > 0) {
			    $string_trs_pelunasan_giro_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_pelunasan_giro_detail->result_array() as $row) {
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $NoDIH          = ($row['NoDIH'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDIH'])."'";
		                $NomorFaktur    = ($row['NomorFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NomorFaktur'])."'";
		                $noline         = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $Area           = ($row['Area'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Area'])."'";
		                $UmurFaktur     = ($row['UmurFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UmurFaktur'])."'";
		                $UmurLunas      = ($row['UmurLunas'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UmurLunas'])."'";
		                $TglFaktur      = ($row['TglFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglFaktur'])."'";
		                $TglPelunasan   = ($row['TglPelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglPelunasan'])."'";
		                $KodePenagih    = ($row['KodePenagih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePenagih'])."'";
		                $NamaPenagih    = ($row['NamaPenagih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPenagih'])."'";
		                $KodePelanggan  = ($row['KodePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePelanggan'])."'";
		                $NamaPelanggan  = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
		                $KodeSalesman   = ($row['KodeSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeSalesman'])."'";
		                $NamaSalesman   = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
		                $ValueFaktur    = ($row['ValueFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueFaktur'])."'";
		                $Cicilan        = ($row['Cicilan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cicilan'])."'";
		                $SaldoFaktur    = ($row['SaldoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SaldoFaktur'])."'";
		                $TipePelunasan  = ($row['TipePelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelunasan'])."'";
		                $Status         = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $ValuePelunasan = ($row['ValuePelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValuePelunasan'])."'";
		                $SaldoAkhir     = ($row['SaldoAkhir'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SaldoAkhir'])."'";
		                $Giro           = ($row['Giro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Giro'])."'";
		                $TglGiroCair    = ($row['TglGiroCair'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglGiroCair'])."'";
		                $ValueGiro      = ($row['ValueGiro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueGiro'])."'";
		                $create_at      = ($row['create_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_at'])."'";
		                $create_by      = ($row['create_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['create_by'])."'";
		                $modified_at    = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
		                $modified_by    = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
		                $bank           = ($row['bank'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['bank'])."'";
		                $status_titipan = ($row['status_titipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_titipan'])."'";
		                $No_Titipan     = ($row['No_Titipan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Titipan'])."'";
		                $status_tambahan= ($row['status_tambahan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_tambahan'])."'";
		                $value_pembulatan= ($row['value_pembulatan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['value_pembulatan'])."'";
		                $value_transfer = ($row['value_transfer'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['value_transfer'])."'";
		                $materai        = ($row['materai'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['materai'])."'";
		                $TipeDokumen    = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
		                $NoNTPN         = ($row['NoNTPN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoNTPN'])."'";
		                $status_DIH     = ($row['status_DIH'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_DIH'])."'";
		                $keterangan     = ($row['keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['keterangan'])."'";
		                $acu2           = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";

			          $string_trs_pelunasan_giro_detail .=  "(
			                              ".$Cabang.",".$NoDIH.",".$NomorFaktur.",".$noline.",".$Area.",".$UmurFaktur.",".$UmurLunas.",".$TglFaktur.",".$TglPelunasan.",".$KodePenagih.",".$NamaPenagih.",".$KodePelanggan.",".$NamaPelanggan.",".$KodeSalesman.",".$NamaSalesman.",".$ValueFaktur.",".$Cicilan.",".$SaldoFaktur.",".$TipePelunasan.",".$Status.",".$ValuePelunasan.",".$SaldoAkhir.",".$Giro.",".$TglGiroCair.",".$ValueGiro.",".$create_at.",".$create_by.",".$modified_at.",".$modified_by.",".$bank.",".$status_titipan.",".$No_Titipan.",".$status_tambahan.",".$value_pembulatan.",".$value_transfer.",".$materai.",".$TipeDokumen.",".$NoNTPN.",".$status_DIH.",".$keterangan.",".$acu2."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_pelunasan_giro_detail,",");
			      /*$this->db2->query("DELETE FROM  trs_pelunasan_giro_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(TglPelunasan),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_pelunasan_giro_detailinsert = "REPLACE INTO `trs_pelunasan_giro_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_pelunasan_giro_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_pelunasan_giro_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_pelunasan_giro_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_pelunasan_giro_detail<br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_pelunasan_giro_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_pelunasan_giro_detail".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_pelunasan_giro_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_pelunasan_giro_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_pelunasan_giro_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_relokasi_kirim_header(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_relokasi_kirim_header = $this->db->query("SELECT * FROM trs_relokasi_kirim_header WHERE date(Tgl_kirim) >= CURDATE() - INTERVAL 3 DAY  or date(modified_time) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_relokasi_kirim_header->num_rows() > 0) {
			    $string_trs_relokasi_kirim_header="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_relokasi_kirim_header->result_array() as $row) {
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Kategori       = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Cabang_Pengirim= ($row['Cabang_Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Pengirim'])."'";
		                $Cabang_Penerima= ($row['Cabang_Penerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Penerima'])."'";
		                $Prinsipal      = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $Supplier       = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $No_Relokasi    = ($row['No_Relokasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Relokasi'])."'";
		                $Tgl_kirim      = ($row['Tgl_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_kirim'])."'";
		                $Time_kirim     = ($row['Time_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_kirim'])."'";
		                $Gross          = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan       = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Disc           = ($row['Disc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc'])."'";
		                $Value          = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $Ppn            = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
		                $Total          = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $Status_kiriman = ($row['Status_kiriman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status_kiriman'])."'";
		                $Dokumen        = ($row['Dokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen'])."'";
		                $Dokumen_2      = ($row['Dokumen_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen_2'])."'";
		                $Keterangan     = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $tgl_reject     = ($row['tgl_reject'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgl_reject'])."'";
		                $tgl_app        = ($row['tgl_app'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgl_app'])."'";
		                $user_BM        = ($row['user_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_BM'])."'";
		                $App_BM_Status  = ($row['App_BM_Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Status'])."'";
		                $App_BM_Time    = ($row['App_BM_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Time'])."'";
		                $App_BM_Alasan  = ($row['App_BM_Alasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Alasan'])."'";
		                $added_time     = ($row['added_time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['added_time'])."'";
		                $added_user     = ($row['added_user'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['added_user'])."'";
		                $modified_time  = ($row['modified_time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_time'])."'";
		                $modified_user  = ($row['modified_user'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_user'])."'";
		                $GudangPusat    = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";
		                $Biayakirim     = ($row['Biayakirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Biayakirim'])."'";
		                $ValueCR        = ($row['ValueCR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCR'])."'";

			          $string_trs_relokasi_kirim_header .=  "(
			                              ".$Cabang.",".$Kategori.",".$Cabang_Pengirim.",".$Cabang_Penerima.",".$Prinsipal.",".$Supplier.",".$No_Relokasi.",".$Tgl_kirim.",".$Time_kirim.",".$Gross.",".$Potongan.",".$Disc.",".$Value.",".$Ppn.",".$Total.",".$Status_kiriman.",".$Dokumen.",".$Dokumen_2.",".$Keterangan.",".$tgl_reject.",".$tgl_app.",".$user_BM.",".$App_BM_Status.",".$App_BM_Time.",".$App_BM_Alasan.",".$added_time.",".$added_user.",".$modified_time.",".$modified_user.",".$GudangPusat.",".$Biayakirim.",".$ValueCR."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_relokasi_kirim_header,",");
			      /*$this->db2->query("DELETE FROM  trs_relokasi_kirim_header_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(Tgl_kirim),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_relokasi_kirim_headerinsert = "REPLACE INTO `trs_relokasi_kirim_header_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_relokasi_kirim_header))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_kirim_header') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_relokasi_kirim_headerinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_kirim_header <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_kirim_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_relokasi_kirim_header ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_kirim_header') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_kirim_header <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_kirim_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_relokasi_kirim_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_relokasi_kirim_detail = $this->db->query("SELECT * FROM trs_relokasi_kirim_detail WHERE date(Tgl_kirim) >= CURDATE() - INTERVAL 3 DAY or date(modified_time) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_relokasi_kirim_detail->num_rows() > 0) {
			    $string_trs_relokasi_kirim_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_relokasi_kirim_detail->result_array() as $row) {
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Cabang_Penerima= ($row['Cabang_Penerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Penerima'])."'";
		                $Cabang_Pengirim= ($row['Cabang_Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Pengirim'])."'";
		                $Prinsipal      = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $Supplier       = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $noline         = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $Kategori       = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Produk         = ($row['Produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Produk'])."'";
		                $NamaProduk     = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
		                $Satuan         = ($row['Satuan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Satuan'])."'";
		                $Keterangan     = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Penjelasan     = ($row['Penjelasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Penjelasan'])."'";
		                $No_Relokasi    = ($row['No_Relokasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Relokasi'])."'";
		                $Counter        = ($row['Counter'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Counter'])."'";
		                $Tgl_kirim      = ($row['Tgl_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_kirim'])."'";
		                $Time_kirim     = ($row['Time_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_kirim'])."'";
		                $Status         = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $Qty            = ($row['Qty'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Qty'])."'";
		                $Stok           = ($row['Stok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Stok'])."'";
		                $Bonus          = ($row['Bonus'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bonus'])."'";
		                $Disc           = ($row['Disc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc'])."'";
		                $Harga          = ($row['Harga'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Harga'])."'";
		                $Gross          = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan       = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Value          = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $Ppn            = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
		                $Total          = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $HPC            = ($row['HPC'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HPC'])."'";
		                $BatchNo        = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
		                $ExpDate        = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
		                $Keterangan_reject = ($row['Keterangan_reject'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan_reject'])."'";
		                $tgl_reject     = ($row['tgl_reject'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgl_reject'])."'";
		                $tgl_app        = ($row['tgl_app'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgl_app'])."'";
		                $user_BM        = ($row['user_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_BM'])."'";
		                $App_BM_Status  = ($row['App_BM_Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Status'])."'";
		                $App_BM_Time    = ($row['App_BM_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Time'])."'";
		                $Added_Time     = ($row['Added_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_Time'])."'";
		                $Modified_Time  = ($row['Modified_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_Time'])."'";
		                $Added_User     = ($row['Added_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_User'])."'";
		                $Modified_User  = ($row['Modified_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_User'])."'";
		                $flag_closing   = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $BatchDoc       = ($row['BatchDoc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchDoc'])."'";
		                $GudangPusat    = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";

			          $string_trs_relokasi_kirim_detail .=  "(
			                              ".$Cabang.",".$Cabang_Penerima.",".$Cabang_Pengirim.",".$Prinsipal.",".$Supplier.",".$noline.",".$Kategori.",".$Produk.",".$NamaProduk.",".$Satuan.",".$Keterangan.",".$Penjelasan.",".$No_Relokasi.",".$Counter.",".$Tgl_kirim.",".$Time_kirim.",".$Status.",".$Qty.",".$Stok.",".$Bonus.",".$Disc.",".$Harga.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$Total.",".$HPC.",".$BatchNo.",".$ExpDate.",".$Keterangan_reject.",".$tgl_reject.",".$tgl_app.",".$user_BM.",".$App_BM_Status.",".$App_BM_Time.",".$Added_Time.",".$Modified_Time.",".$Added_User.",".$Modified_User.",".$flag_closing.",".$BatchDoc.",".$GudangPusat."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_relokasi_kirim_detail,",");
			      /*$this->db2->query("DELETE FROM  trs_relokasi_kirim_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(Tgl_kirim),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_relokasi_kirim_detailinsert = "REPLACE INTO `trs_relokasi_kirim_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_relokasi_kirim_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_kirim_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_relokasi_kirim_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_kirim_detail <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_kirim_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_relokasi_kirim_detail ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_kirim_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_kirim_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_kirim_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_relokasi_terima_header(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_relokasi_terima_header = $this->db->query("SELECT * FROM trs_relokasi_terima_header WHERE date(Tgl_kirim) >= CURDATE() - INTERVAL 3 DAY or date(modified_time) >= CURDATE() - INTERVAL 3 DAY ");


			// =========== insert   =============================
			if ($querytrs_relokasi_terima_header->num_rows() > 0) {
			    $string_trs_relokasi_terima_header="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_relokasi_terima_header->result_array() as $row) {
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Kategori       = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Cabang_Pengirim= ($row['Cabang_Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Pengirim'])."'";
		                $Cabang_Penerima= ($row['Cabang_Penerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Penerima'])."'";
		                $Prinsipal      = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $Supplier       = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $No_Terima      = ($row['No_Terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Terima'])."'";
		                $No_Relokasi    = ($row['No_Relokasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Relokasi'])."'";
		                $No_DO          = ($row['No_DO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_DO'])."'";
		                $Tgl_kirim      = ($row['Tgl_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_kirim'])."'";
		                $Time_kirim     = ($row['Time_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_kirim'])."'";
		                $Tgl_terima     = ($row['Tgl_terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_terima'])."'";
		                $Time_terima    = ($row['Time_terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_terima'])."'";
		                $Gross          = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan       = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Disc           = ($row['Disc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc'])."'";
		                $Value          = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $Ppn            = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
		                $Total          = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $Status_kiriman = ($row['Status_kiriman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status_kiriman'])."'";
		                $Keterangan     = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Dokumen        = ($row['Dokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen'])."'";
		                $Dokumen_2      = ($row['Dokumen_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen_2'])."'";
		                $App_BM_Status  = ($row['App_BM_Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Status'])."'";
		                $App_BM_Time    = ($row['App_BM_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Time'])."'";
		                $App_BM_Alasan  = ($row['App_BM_Alasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['App_BM_Alasan'])."'";
		                $added_time     = ($row['added_time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['added_time'])."'";
		                $added_user     = ($row['added_user'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['added_user'])."'";
		                $modified_time  = ($row['modified_time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_time'])."'";
		                $modified_user  = ($row['modified_user'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_user'])."'";
		                $flag_closing   = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $GudangPusat    = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";
		                $Biayakirim     = ($row['Biayakirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Biayakirim'])."'";
		                $ValueCR        = ($row['ValueCR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCR'])."'";

			          $string_trs_relokasi_terima_header .=  "(
			                              ".$Cabang.",".$Kategori.",".$Cabang_Pengirim.",".$Cabang_Penerima.",".$Prinsipal.",".$Supplier.",".$No_Terima.",".$No_Relokasi.",".$No_DO.",".$Tgl_kirim.",".$Time_kirim.",".$Tgl_terima.",".$Time_terima.",".$Gross.",".$Potongan.",".$Disc.",".$Value.",".$Ppn.",".$Total.",".$Status_kiriman.",".$Keterangan.",".$Dokumen.",".$Dokumen_2.",".$App_BM_Status.",".$App_BM_Time.",".$App_BM_Alasan.",".$added_time.",".$added_user.",".$modified_time.",".$modified_user.",".$flag_closing.",".$GudangPusat.",".$Biayakirim.",".$ValueCR."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_relokasi_terima_header,",");
			      /*$this->db2->query("DELETE FROM  trs_relokasi_terima_header_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(Tgl_kirim),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_relokasi_terima_headerinsert = "REPLACE INTO `trs_relokasi_terima_header_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_relokasi_terima_header))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_terima_header') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_relokasi_terima_headerinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_terima_header <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_terima_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_relokasi_terima_header ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_terima_header') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_terima_header <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_terima_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_relokasi_terima_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_relokasi_terima_detail = $this->db->query("SELECT * FROM trs_relokasi_terima_detail WHERE date(Tgl_terima) >= CURDATE() - INTERVAL 3 DAY or date(modified_time) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_relokasi_terima_detail->num_rows() > 0) {
			    $string_trs_relokasi_terima_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_relokasi_terima_detail->result_array() as $row) {
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Cabang_Penerima= ($row['Cabang_Penerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Penerima'])."'";
		                $Cabang_Pengirim= ($row['Cabang_Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang_Pengirim'])."'";
		                $No_Terima      = ($row['No_Terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Terima'])."'";
		                $Tgl_terima     = ($row['Tgl_terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_terima'])."'";
		                $Time_terima    = ($row['Time_terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_terima'])."'";
		                $Prinsipal      = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $Supplier       = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $noline         = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $Kategori       = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Produk         = ($row['Produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Produk'])."'";
		                $NamaProduk     = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
		                $Satuan         = ($row['Satuan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Satuan'])."'";
		                $Keterangan     = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Penjelasan     = ($row['Penjelasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Penjelasan'])."'";
		                $No_Relokasi    = ($row['No_Relokasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['No_Relokasi'])."'";
		                $Counter        = ($row['Counter'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Counter'])."'";
		                $Tgl_kirim      = ($row['Tgl_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tgl_kirim'])."'";
		                $Time_kirim     = ($row['Time_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Time_kirim'])."'";
		                $Status         = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $Qty_kirim      = ($row['Qty_kirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Qty_kirim'])."'";
		                $Qty_terima     = ($row['Qty_terima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Qty_terima'])."'";
		                $Qty            = ($row['Qty'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Qty'])."'";
		                $Stok           = ($row['Stok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Stok'])."'";
		                $Bonus          = ($row['Bonus'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bonus'])."'";
		                $Disc           = ($row['Disc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc'])."'";
		                $Harga          = ($row['Harga'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Harga'])."'";
		                $Gross          = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan       = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Value          = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $Ppn            = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
		                $Total          = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $HPC            = ($row['HPC'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HPC'])."'";
		                $BatchNo        = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
		                $ExpDate        = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
		                $Added_Time     = ($row['Added_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_Time'])."'";
		                $Modified_Time  = ($row['Modified_Time'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_Time'])."'";
		                $Added_User     = ($row['Added_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Added_User'])."'";
		                $Modified_User  = ($row['Modified_User'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Modified_User'])."'";
		                $flag_closing   = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $BatchDoc       = ($row['BatchDoc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchDoc'])."'";
		                $GudangPusat    = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";

			          $string_trs_relokasi_terima_detail .=  "(
			                              ".$Cabang.",".$Cabang_Penerima.",".$Cabang_Pengirim.",".$No_Terima.",".$Tgl_terima.",".$Time_terima.",".$Prinsipal.",".$Supplier.",".$noline.",".$Kategori.",".$Produk.",".$NamaProduk.",".$Satuan.",".$Keterangan.",".$Penjelasan.",".$No_Relokasi.",".$Counter.",".$Tgl_kirim.",".$Time_kirim.",".$Status.",".$Qty_kirim.",".$Qty_terima.",".$Qty.",".$Stok.",".$Bonus.",".$Disc.",".$Harga.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$Total.",".$HPC.",".$BatchNo.",".$ExpDate.",".$Added_Time.",".$Modified_Time.",".$Added_User.",".$Modified_User.",".$flag_closing.",".$BatchDoc.",".$GudangPusat."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_relokasi_terima_detail,",");
			      /*$this->db2->query("DELETE FROM  trs_relokasi_terima_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(Tgl_terima),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_relokasi_terima_detailinsert = "REPLACE INTO `trs_relokasi_terima_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_relokasi_terima_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_terima_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_relokasi_terima_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_terima_detail <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_terima_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_relokasi_terima_detail ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_relokasi_terima_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_relokasi_terima_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_relokasi_terima_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_terima_barang_header(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_terima_barang_header = $this->db->query("SELECT * FROM trs_terima_barang_header WHERE date(TglDokumen) >= CURDATE() - INTERVAL 3 DAY or date(TimeEdit) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_terima_barang_header->num_rows() > 0) {
			    $string_trs_terima_barang_header="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_terima_barang_header->result_array() as $row) {
		                $Cabang        = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $Prinsipal     = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $NamaPrinsipal = ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
		                $Supplier      = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $NamaSupplier  = ($row['NamaSupplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSupplier'])."'";
		                $NoUsulan      = ($row['NoUsulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoUsulan'])."'";
		                $NoPR          = ($row['NoPR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoPR'])."'";
		                $NoPO          = ($row['NoPO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoPO'])."'";
		                $Tipe          = ($row['Tipe'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe'])."'";
		                $Tipe_BKB      = ($row['Tipe_BKB'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe_BKB'])."'";
		                $NoDokumen     = ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
		                $NoAcuDokumen  = ($row['NoAcuDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoAcuDokumen'])."'";
		                $TglDokumen    = ($row['TglDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglDokumen'])."'";
		                $TimeDokumen   = ($row['TimeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeDokumen'])."'";
		                $Status        = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $Attach1       = ($row['Attach1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Attach1'])."'";
		                $Attach2       = ($row['Attach2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Attach2'])."'";
		                $NoSJ          = ($row['NoSJ'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSJ'])."'";
		                $NoBEX         = ($row['NoBEX'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoBEX'])."'";
		                $NoInv         = ($row['NoInv'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoInv'])."'";
		                $Keterangan    = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Penjelasan    = ($row['Penjelasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Penjelasan'])."'";
		                $Gross         = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan      = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Value         = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $PPN           = ($row['PPN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['PPN'])."'";
		                $pph22         = ($row['pph22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['pph22'])."'";
		                $Total         = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $TotalBiaya    = ($row['TotalBiaya'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TotalBiaya'])."'";
		                $Counter       = ($row['Counter'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Counter'])."'";
		                $UserAdd       = ($row['UserAdd'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UserAdd'])."'";
		                $TimeAdd       = ($row['TimeAdd'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeAdd'])."'";
		                $UserEdit      = ($row['UserEdit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UserEdit'])."'";
		                $TimeEdit      = ($row['TimeEdit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeEdit'])."'";
		                $statusPusat   = ($row['statusPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['statusPusat'])."'";
		                $NoIDPaket     = ($row['NoIDPaket'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoIDPaket'])."'";
		                $Pelanggan     = ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
		                $flag_closing  = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $NoDO          = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
		               $flag_suratjalan= ($row['flag_suratjalan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_suratjalan'])."'";
		                $no_suratjalan = ($row['no_suratjalan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['no_suratjalan'])."'";
		                $GudangPusat   = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";
		                $flag_cndn     = ($row['flag_cndn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_cndn'])."'";
		                $Tipe_PO       = ($row['Tipe_PO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe_PO'])."'";
		            $status_pelunasan= ($row['status_pelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_pelunasan'])."'";
		                $saldo         = ($row['saldo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['saldo'])."'";
		                $flag_mapping  = ($row['flag_mapping'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_mapping'])."'";
		                $NoFakturPajak = ($row['NoFakturPajak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoFakturPajak'])."'";


			          $string_trs_terima_barang_header .=  "(
			                              ".$Cabang.",".$Prinsipal.",".$NamaPrinsipal.",".$Supplier.",".$NamaSupplier.",".$NoUsulan.",".$NoPR.",".$NoPO.",".$Tipe.",".$Tipe_BKB.",".$NoDokumen.",".$NoAcuDokumen.",".$TglDokumen.",".$TimeDokumen.",".$Status.",".$Attach1.",".$Attach2.",".$NoSJ.",".$NoBEX.",".$NoInv.",".$Keterangan.",".$Penjelasan.",".$Gross.",".$Potongan.",".$Value.",".$PPN.",".$pph22.",".$Total.",".$TotalBiaya.",".$Counter.",".$UserAdd.",".$TimeAdd.",".$UserEdit.",".$TimeEdit.",".$statusPusat.",".$NoIDPaket.",".$Pelanggan.",".$flag_closing.",".$NoDO.",".$flag_suratjalan.",".$no_suratjalan.",".$GudangPusat.",".$flag_cndn.",".$Tipe_PO.",".$status_pelunasan.",".$saldo.",".$flag_mapping.",".$NoFakturPajak."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_terima_barang_header,",");
			      /*$this->db2->query("DELETE FROM  trs_terima_barang_header_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(TglDokumen),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_terima_barang_headerinsert = "REPLACE INTO `trs_terima_barang_header_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_terima_barang_header))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_terima_barang_header') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_terima_barang_headerinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_terima_barang_header <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_terima_barang_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_terima_barang_header ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_terima_barang_header') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_terima_barang_header <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_terima_barang_header : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_terima_barang_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_terima_barang_detail = $this->db->query("SELECT * FROM trs_terima_barang_detail WHERE date(TglDokumen) >= CURDATE() - INTERVAL 3 DAY or date(TimeEdit) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert   =============================
			if ($querytrs_terima_barang_detail->num_rows() > 0) {
			    $string_trs_terima_barang_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_terima_barang_detail->result_array() as $row) {
		                $Cabang        = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $NoDokumen     = ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
		                $noline        = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
		                $Prinsipal     = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
		                $NamaPrinsipal = ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
		                $Supplier      = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
		                $NamaSupplier  = ($row['NamaSupplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSupplier'])."'";
		                $Pabrik        = ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
		                $NoUsulan      = ($row['NoUsulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoUsulan'])."'";
		                $NoPR          = ($row['NoPR'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoPR'])."'";
		                $NoPO          = ($row['NoPO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoPO'])."'";
		                $NoDO          = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
		                $Tipe          = ($row['Tipe'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe'])."'";
		                $Tipe_BKB      = ($row['Tipe_BKB'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe_BKB'])."'";
		                $NoAcuDokumen  = ($row['NoAcuDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoAcuDokumen'])."'";
		                $TglDokumen    = ($row['TglDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglDokumen'])."'";
		                $TimeDokumen   = ($row['TimeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeDokumen'])."'";
		                $Status        = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $NoSJ          = ($row['NoSJ'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSJ'])."'";
		                $NoBEX         = ($row['NoBEX'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoBEX'])."'";
		                $NoInv         = ($row['NoInv'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoInv'])."'";
		                $Produk        = ($row['Produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Produk'])."'";
		                $NamaProduk    = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
		                $Satuan        = ($row['Satuan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Satuan'])."'";
		                $Keterangan    = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $Penjelasan    = ($row['Penjelasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Penjelasan'])."'";
		                $QtyPO         = ($row['QtyPO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyPO'])."'";
		                $Qty           = ($row['Qty'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Qty'])."'";
		                $Bonus         = ($row['Bonus'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bonus'])."'";
		                $Banyak        = ($row['Banyak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Banyak'])."'";
		                $Disc          = ($row['Disc'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc'])."'";
		                $DiscT         = ($row['DiscT'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscT'])."'";
		                $HrgBeli       = ($row['HrgBeli'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HrgBeli'])."'";
		                $BatchNo       = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
		                $ExpDate       = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
		                $HPC           = ($row['HPC'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HPC'])."'";
		                $HPC1          = ($row['HPC1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HPC1'])."'";
		                $Gross         = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan      = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Value         = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $PPN           = ($row['PPN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['PPN'])."'";
		                $Total         = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $Disc_Pst      = ($row['Disc_Pst'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Disc_Pst'])."'";
		                $Harga_Beli_Pst= ($row['Harga_Beli_Pst'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Harga_Beli_Pst'])."'";
		                $HPP           = ($row['HPP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HPP'])."'";
		                $Counter       = ($row['Counter'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Counter'])."'";
		                $UserAdd       = ($row['UserAdd'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UserAdd'])."'";
		                $TimeAdd       = ($row['TimeAdd'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeAdd'])."'";
		                $UserEdit      = ($row['UserEdit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UserEdit'])."'";
		                $TimeEdit      = ($row['TimeEdit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeEdit'])."'";
		                $QtyAwal       = ($row['QtyAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyAwal'])."'";
		                $BonusAwal     = ($row['BonusAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusAwal'])."'";
		                $HrgBeliAwal   = ($row['HrgBeliAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HrgBeliAwal'])."'";
		                $DiscAwal      = ($row['DiscAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscAwal'])."'";
		                $DiscTAwal     = ($row['DiscTAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscTAwal'])."'";
		                $GrossAwal     = ($row['GrossAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GrossAwal'])."'";
		                $PotonganAwal  = ($row['PotonganAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['PotonganAwal'])."'";
		                $ValueAwal     = ($row['ValueAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueAwal'])."'";
		                $PPNAwal       = ($row['PPNAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['PPNAwal'])."'";
		                $TotalAwal     = ($row['TotalAwal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TotalAwal'])."'";
		                $flag_closing  = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $kategori      = ($row['kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['kategori'])."'";
		                $pph22         = ($row['pph22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['pph22'])."'";
		                $qty_sj        = ($row['qty_sj'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['qty_sj'])."'";
		                $GudangPusat   = ($row['GudangPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['GudangPusat'])."'";
		                $flag_cndn     = ($row['flag_cndn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_cndn'])."'";
		                $Tipe_PO       = ($row['Tipe_PO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tipe_PO'])."'";

			          $string_trs_terima_barang_detail .=  "(
			                              ".$Cabang.",".$NoDokumen.",".$noline.",".$Prinsipal.",".$NamaPrinsipal.",".$Supplier.",".$NamaSupplier.",".$Pabrik.",".$NoUsulan.",".$NoPR.",".$NoPO.",".$NoDO.",".$Tipe.",".$Tipe_BKB.",".$NoAcuDokumen.",".$TglDokumen.",".$TimeDokumen.",".$Status.",".$NoSJ.",".$NoBEX.",".$NoInv.",".$Produk.",".$NamaProduk.",".$Satuan.",".$Keterangan.",".$Penjelasan.",".$QtyPO.",".$Qty.",".$Bonus.",".$Banyak.",".$Disc.",".$DiscT.",".$HrgBeli.",".$BatchNo.",".$ExpDate.",".$HPC.",".$HPC1.",".$Gross.",".$Potongan.",".$Value.",".$PPN.",".$Total.",".$Disc_Pst.",".$Harga_Beli_Pst.",".$HPP.",".$Counter.",".$UserAdd.",".$TimeAdd.",".$UserEdit.",".$TimeEdit.",".$QtyAwal.",".$BonusAwal.",".$HrgBeliAwal.",".$DiscAwal.",".$DiscTAwal.",".$GrossAwal.",".$PotonganAwal.",".$ValueAwal.",".$PPNAwal.",".$TotalAwal.",".$flag_closing.",".$kategori.",".$pph22.",".$qty_sj.",".$GudangPusat.",".$flag_cndn.",".$Tipe_PO."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_terima_barang_detail,",");
			      /*$this->db2->query("DELETE FROM  trs_terima_barang_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(TglDokumen),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_terima_barang_detailinsert = "REPLACE INTO `trs_terima_barang_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_terima_barang_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_terima_barang_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_terima_barang_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_terima_barang_detail <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_terima_barang_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_terima_barang_detail ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_terima_barang_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_terima_barang_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_terima_barang_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_kiriman(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_kiriman = $this->db->query("SELECT * FROM trs_kiriman WHERE date(TglKirim) >= CURDATE() - INTERVAL 3 DAY or date(updated_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert  =============================
			if ($querytrs_kiriman->num_rows() > 0) {
			    $string_trs_kiriman="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_kiriman->result_array() as $row) {
		                $id             = ($row['id'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['id'])."'";
		                $NoKiriman      = ($row['NoKiriman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoKiriman'])."'";
		                $Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $KodePelanggan  = ($row['KodePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePelanggan'])."'";
		                $Pengirim       = ($row['Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pengirim'])."'";
		                $NamaPengirim   = ($row['NamaPengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPengirim'])."'";
		                $StatusKiriman  = ($row['StatusKiriman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusKiriman'])."'";
		                $StatusDO       = ($row['StatusDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusDO'])."'";
		                $NoDO           = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
		                $TglKirim       = ($row['TglKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglKirim'])."'";
		                $TimeKirim      = ($row['TimeKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeKirim'])."'";
		                $TglTerima      = ($row['TglTerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglTerima'])."'";
		                $TimeTerima     = ($row['TimeTerima'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeTerima'])."'";
		                $Alasan         = ($row['Alasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Alasan'])."'";
		                $created_by     = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
		                $updated_by     = ($row['updated_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_by'])."'";
		                $created_at     = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
		                $updated_at     = ($row['updated_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_at'])."'";
		                $statusPusat    = ($row['statusPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['statusPusat'])."'";
			            $Keterangan 	= ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";

			          $string_trs_kiriman .=  "(
			                              ".$id.",".$NoKiriman.",".$Cabang.",".$KodePelanggan.",".$Pengirim.",".$NamaPengirim.",".$StatusKiriman.",".$StatusDO.",".$NoDO.",".$TglKirim.",".$TimeKirim.",".$TglTerima.",".$TimeTerima.",".$Alasan.",".$created_by.",".$updated_by.",".$created_at.",".$updated_at.",".$statusPusat.",".$Keterangan."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_kiriman,",");
			      /*$this->db2->query("DELETE FROM  trs_kiriman_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(DATE(TglKirim),'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			      $querytrs_kirimaninsert = "REPLACE INTO `trs_kiriman_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_kiriman))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_kiriman') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_kirimaninsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_kiriman <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_kiriman : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_kiriman ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_kiriman') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_kiriman <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_kiriman : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_invsum(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_invsum = $this->db->query("SELECT * FROM trs_invsum WHERE YEAR(ModifiedDate) = YEAR(CURDATE()) AND MONTH(ModifiedDate)= MONTH(CURDATE()) AND Tahun = YEAR(CURDATE()) ");


			// =========== insert  =============================
			if ($querytrs_invsum->num_rows() > 0) {
			    $string_trs_invsum="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_invsum->result_array() as $row) {
		                $Tahun            = ($row['Tahun'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tahun'])."'";
		                $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $KodePrinsipal    = ($row['KodePrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePrinsipal'])."'";
		                $NamaPrinsipal    = ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
		                $Pabrik           = ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
		                $KodeProduk       = ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
		                $NamaProduk       = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
		                $UnitStok         = ($row['UnitStok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitStok'])."'";
		                $ValueStok        = ($row['ValueStok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueStok'])."'";
		                $Gudang           = ($row['Gudang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gudang'])."'";
		                $Indeks           = ($row['Indeks'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Indeks'])."'";
		                $UnitCOGS         = ($row['UnitCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitCOGS'])."'";
		                $HNA              = ($row['HNA'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HNA'])."'";
		                $SAwal01          = ($row['SAwal01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal01'])."'";
		                $VAwal01          = ($row['VAwal01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal01'])."'";
		                $SAwal02          = ($row['SAwal02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal02'])."'";
		                $VAwal02          = ($row['VAwal02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal02'])."'";
		                $SAwal03          = ($row['SAwal03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal03'])."'";
		                $VAwal03          = ($row['VAwal03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal03'])."'";
		                $SAwal04          = ($row['SAwal04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal04'])."'";
		                $VAwal04          = ($row['VAwal04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal04'])."'";
		                $SAwal05          = ($row['SAwal05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal05'])."'";
		                $VAwal05          = ($row['VAwal05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal05'])."'";
		                $SAwal06          = ($row['SAwal06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal06'])."'";
		                $VAwal06          = ($row['VAwal06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal06'])."'";
		                $SAwal07          = ($row['SAwal07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal07'])."'";
		                $VAwal07          = ($row['VAwal07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal07'])."'";
		                $SAwal08          = ($row['SAwal08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal08'])."'";
		                $VAwal08          = ($row['VAwal08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal08'])."'";
		                $SAwal09          = ($row['SAwal09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal09'])."'";
		                $VAwal09          = ($row['VAwal09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal09'])."'";
		                $SAwal10          = ($row['SAwal10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal10'])."'";
		                $VAwal10          = ($row['VAwal10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal10'])."'";
		                $SAwal11          = ($row['SAwal11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal11'])."'";
		                $VAwal11          = ($row['VAwal11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal11'])."'";
		                $SAwal12          = ($row['SAwal12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal12'])."'";
		                $VAwal12          = ($row['VAwal12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal12'])."'";
		                $LastBuy          = ($row['LastBuy'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LastBuy'])."'";
		                $LastSales        = ($row['LastSales'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LastSales'])."'";
		                $AddedUser        = ($row['AddedUser'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AddedUser'])."'";
		                $AddedTime        = ($row['AddedTime'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AddedTime'])."'";
		                $ModifiedUser     = ($row['ModifiedUser'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ModifiedUser'])."'";
		                $ModifiedDate     = ($row['ModifiedDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ModifiedDate'])."'";

			          $string_trs_invsum .=  "(
			                              ".$Tahun.",".$Cabang.",".$KodePrinsipal.",".$NamaPrinsipal.",".$Pabrik.",".$KodeProduk.",".$NamaProduk.",".$UnitStok.",".$ValueStok.",".$Gudang.",".$Indeks.",".$UnitCOGS.",".$HNA.",".$SAwal01.",".$VAwal01.",".$SAwal02.",".$VAwal02.",".$SAwal03.",".$VAwal03.",".$SAwal04.",".$VAwal04.",".$SAwal05.",".$VAwal05.",".$SAwal06.",".$VAwal06.",".$SAwal07.",".$VAwal07.",".$SAwal08.",".$VAwal08.",".$SAwal09.",".$VAwal09.",".$SAwal10.",".$VAwal10.",".$SAwal11.",".$VAwal11.",".$SAwal12.",".$VAwal12.",".$LastBuy.",".$LastSales.",".$AddedUser.",".$AddedTime.",".$ModifiedUser.",".$ModifiedDate."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_invsum,",");
			      /*$this->db2->query("DELETE FROM  trs_invsum_".$this->kd_cab." WHERE cabang='".$this->cabang."' AND DATE_FORMAT(AddedUser,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m') ");*/
			      $querytrs_invsuminsert = "REPLACE INTO `trs_invsum_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_invsum))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invsum') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_invsuminsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_invsum <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_invsum : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_invsum ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invsum') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_invsum <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_invsum : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_invdet(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_invdet = $this->db->query("SELECT * FROM trs_invdet WHERE YEAR(ModifiedTime) = YEAR(CURDATE()) AND MONTH(ModifiedTime)= MONTH(CURDATE()) AND Tahun = YEAR(CURDATE())");


			// =========== insert   =============================
			if ($querytrs_invdet->num_rows() > 0) {
			    $string_trs_invdet=$string_trs_invdet2="";
			    $jumlahbaru = 0;
			    $var_dump=$var_dump2="";

			      foreach ($querytrs_invdet->result_array() as $row) {
		                $Tahun           = ($row['Tahun'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tahun'])."'";
		                $Cabang          = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $KodePrinsipal   = ($row['KodePrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePrinsipal'])."'";
		                $NamaPrinsipal   = ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
		                $Pabrik          = ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
		                $KodeProduk      = ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
		                $NamaProduk      = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
		                $UnitStok        = ($row['UnitStok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitStok'])."'";
		                $ValueStok       = ($row['ValueStok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueStok'])."'";
		                $UnitCOGS        = ($row['UnitCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitCOGS'])."'";
		                $BatchNo         = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
		                $ExpDate         = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
		                $NoDokumen       = ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
		                $Gudang          = ($row['Gudang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gudang'])."'";
		                $TanggalDokumen  = ($row['TanggalDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TanggalDokumen'])."'";
		                $SAwa01          = ($row['SAwa01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa01'])."'";
		                $VAwa01          = ($row['VAwa01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa01'])."'";
		                $SAwa02          = ($row['SAwa02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa02'])."'";
		                $VAwa02          = ($row['VAwa02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa02'])."'";
		                $SAwa03          = ($row['SAwa03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa03'])."'";
		                $VAwa03          = ($row['VAwa03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa03'])."'";
		                $SAwa04          = ($row['SAwa04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa04'])."'";
		                $VAwa04          = ($row['VAwa04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa04'])."'";
		                $SAwa05          = ($row['SAwa05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa05'])."'";
		                $VAwa05          = ($row['VAwa05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa05'])."'";
		                $SAwa06          = ($row['SAwa06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa06'])."'";
		                $VAwa06          = ($row['VAwa06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa06'])."'";
		                $SAwa07          = ($row['SAwa07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa07'])."'";
		                $VAwa07          = ($row['VAwa07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa07'])."'";
		                $SAwa08          = ($row['SAwa08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa08'])."'";
		                $VAwa08          = ($row['VAwa08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa08'])."'";
		                $SAwa09          = ($row['SAwa09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa09'])."'";
		                $VAwa09          = ($row['VAwa09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa09'])."'";
		                $SAwa10          = ($row['SAwa10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa10'])."'";
		                $VAwa10          = ($row['VAwa10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa10'])."'";
		                $SAwa11          = ($row['SAwa11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa11'])."'";
		                $VAwa11          = ($row['VAwa11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa11'])."'";
		                $SAWa12          = ($row['SAWa12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAWa12'])."'";
		                $VAwa12          = ($row['VAwa12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa12'])."'";
		                $Keterangan      = ($row['Keterangan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan'])."'";
		                $LastBuy         = ($row['LastBuy'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LastBuy'])."'";
		                $LastSales       = ($row['LastSales'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LastSales'])."'";
		                $AddedUser       = ($row['AddedUser'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AddedUser'])."'";
		                $AddedTime       = ($row['AddedTime'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AddedTime'])."'";
		                $ModifiedUser    = ($row['ModifiedUser'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ModifiedUser'])."'";
		                $ModifiedTime    = ($row['ModifiedTime'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ModifiedTime'])."'";
		                $nourut          = ($row['nourut'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['nourut'])."'";

		              if ($jumlahbaru <= 20000) {
				          $string_trs_invdet .=  "(
				                              ".$Tahun.",".$Cabang.",".$KodePrinsipal.",".$NamaPrinsipal.",".$Pabrik.",".$KodeProduk.",".$NamaProduk.",".$UnitStok.",".$ValueStok.",".$UnitCOGS.",".$BatchNo.",".$ExpDate.",".$NoDokumen.",".$Gudang.",".$TanggalDokumen.",".$SAwa01.",".$VAwa01.",".$SAwa02.",".$VAwa02.",".$SAwa03.",".$VAwa03.",".$SAwa04.",".$VAwa04.",".$SAwa05.",".$VAwa05.",".$SAwa06.",".$VAwa06.",".$SAwa07.",".$VAwa07.",".$SAwa08.",".$VAwa08.",".$SAwa09.",".$VAwa09.",".$SAwa10.",".$VAwa10.",".$SAwa11.",".$VAwa11.",".$SAWa12.",".$VAwa12.",".$Keterangan.",".$LastBuy.",".$LastSales.",".$AddedUser.",".$AddedTime.",".$ModifiedUser.",".$ModifiedTime.",".$nourut."),";
		              }else{
		              	$string_trs_invdet2 .=  "(
				                              ".$Tahun.",".$Cabang.",".$KodePrinsipal.",".$NamaPrinsipal.",".$Pabrik.",".$KodeProduk.",".$NamaProduk.",".$UnitStok.",".$ValueStok.",".$UnitCOGS.",".$BatchNo.",".$ExpDate.",".$NoDokumen.",".$Gudang.",".$TanggalDokumen.",".$SAwa01.",".$VAwa01.",".$SAwa02.",".$VAwa02.",".$SAwa03.",".$VAwa03.",".$SAwa04.",".$VAwa04.",".$SAwa05.",".$VAwa05.",".$SAwa06.",".$VAwa06.",".$SAwa07.",".$VAwa07.",".$SAwa08.",".$VAwa08.",".$SAwa09.",".$VAwa09.",".$SAwa10.",".$VAwa10.",".$SAwa11.",".$VAwa11.",".$SAWa12.",".$VAwa12.",".$Keterangan.",".$LastBuy.",".$LastSales.",".$AddedUser.",".$AddedTime.",".$ModifiedUser.",".$ModifiedTime.",".$nourut."),";
		              }

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_invdet,",");
			      // $this->db2->query("DELETE FROM  trs_invdet_".$this->kd_cab." WHERE cabang='".$this->cabang."'");

			      $querytrs_invdetinsert = "REPLACE INTO `trs_invdet_".$this->kd_cab."` VALUES ".$var_dump;

			      if ($jumlahbaru >= 20000) {
			      	$var_dump2=rtrim($string_trs_invdet2,",");

			      	$this->db2->query("REPLACE INTO `trs_invdet_".$this->kd_cab."` VALUES ".$var_dump2);
			      }

			      if(empty($string_trs_invdet))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invdet') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_invdetinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_invdet <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_invdet : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_invdet ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invdet') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_invdet <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_invdet : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_delivery_order_sales(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_delivery_order_sales = $this->db->query("SELECT * FROM trs_delivery_order_sales WHERE date(TglDO) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert  =============================
			if ($querytrs_delivery_order_sales->num_rows() > 0) {
			    $string_trs_delivery_order_sales="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_delivery_order_sales->result_array() as $row) {
		                $Cabang       = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
		                $NoDO         = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
		                $TglDO        = ($row['TglDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglDO'])."'";
		                $TimeDO       = ($row['TimeDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeDO'])."'";
		                $Pengirim     = ($row['Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pengirim'])."'";
		                $NamaPengirim = ($row['NamaPengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPengirim'])."'";
		                $Pelanggan    = ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
		                $NamaPelanggan= ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
		                $AlamatKirim  = ($row['AlamatKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AlamatKirim'])."'";
		                $TipePelanggan= ($row['TipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelanggan'])."'";
		         $NamaTipePelanggan= ($row['NamaTipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaTipePelanggan'])."'";
		                $NPWPPelanggan= ($row['NPWPPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NPWPPelanggan'])."'";
		         $KategoriPelanggan= ($row['KategoriPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KategoriPelanggan'])."'";
		                $Acu          = ($row['Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Acu'])."'";
		                $CaraBayar    = ($row['CaraBayar'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CaraBayar'])."'";
		                $CashDiskon   = ($row['CashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CashDiskon'])."'";
		              $ValueCashDiskon= ($row['ValueCashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCashDiskon'])."'";
		                $TOP          = ($row['TOP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TOP'])."'";
		                $TglJtoOrder  = ($row['TglJtoOrder'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJtoOrder'])."'";
		                $Salesman     = ($row['Salesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Salesman'])."'";
		                $NamaSalesman = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
		                $Rayon        = ($row['Rayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Rayon'])."'";
		                $NamaRayon    = ($row['NamaRayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaRayon'])."'";
		                $Status       = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
		                $TipeDokumen  = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
		                $Gross        = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
		                $Potongan     = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
		                $Value        = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
		                $Ppn          = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
		                $LainLain     = ($row['LainLain'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LainLain'])."'";
		                $Materai      = ($row['Materai'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Materai'])."'";
		                $OngkosKirim  = ($row['OngkosKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['OngkosKirim'])."'";
		                $Total        = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
		                $Keterangan1  = ($row['Keterangan1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan1'])."'";
		                $Keterangan2  = ($row['Keterangan2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan2'])."'";
		                $Barcode      = ($row['Barcode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Barcode'])."'";
		                $QrCode       = ($row['QrCode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QrCode'])."'";
		                $NoSO         = ($row['NoSO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSO'])."'";
		                $NoFaktur     = ($row['NoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoFaktur'])."'";
		                $NoSP         = ($row['NoSP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSP'])."'";
		                $statusPusat  = ($row['statusPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['statusPusat'])."'";
		                $TipeFaktur   = ($row['TipeFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeFaktur'])."'";
		                $NoIDPaket    = ($row['NoIDPaket'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoIDPaket'])."'";
		            $KeteranganTender= ($row['KeteranganTender'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KeteranganTender'])."'";
		                $flag_closing = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
		                $ppn_pelanggan= ($row['ppn_pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ppn_pelanggan'])."'";
		                $user_approve = ($row['user_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_approve'])."'";
		                $time_approve = ($row['time_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_approve'])."'";
		                $status_approve= ($row['status_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_approve'])."'";
		                $user_edit    = ($row['user_edit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_edit'])."'";
		                $time_edit    = ($row['time_edit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_edit'])."'";
		                $time_batal   = ($row['time_batal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_batal'])."'";
		                $user_batal   = ($row['user_batal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_batal'])."'";
		                $created_at   = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
		                $created_by   = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
		                $modified_at  = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
		                $modified_by  = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
		                $status_retur = ($row['status_retur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_retur'])."'";
		                $user_retur   = ($row['user_retur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_retur'])."'";
		                $time_retur   = ($row['time_retur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_retur'])."'";
		               $status_validasi= ($row['status_validasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_validasi'])."'";
		                $user_validasi = ($row['user_validasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_validasi'])."'";
		                $time_validasi = ($row['time_validasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_validasi'])."'";
		                $noretur      = ($row['noretur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noretur'])."'";
		                $acu2         = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";
		                $region       = ($row['region'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['region'])."'";
		                $ket_jto      = ($row['ket_jto'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ket_jto'])."'";
		                $Kategori    = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
		                $Kategori2   = ($row['Kategori2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori2'])."'";
		                $Kategori3   = ($row['Kategori3'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori3'])."'";
		                $judul       = ($row['judul'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['judul'])."'";
		                $spesial     = ($row['spesial'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['spesial'])."'";
		                $alasan_jto   = ($row['alasan_jto'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['alasan_jto'])."'";

			          $string_trs_delivery_order_sales .=  "(
			                              ".$Cabang.",".$NoDO.",".$TglDO.",".$TimeDO.",".$Pengirim.",".$NamaPengirim.",".$Pelanggan.",".$NamaPelanggan.",".$AlamatKirim.",".$TipePelanggan.",".$NamaTipePelanggan.",".$NPWPPelanggan.",".$KategoriPelanggan.",".$Acu.",".$CaraBayar.",".$CashDiskon.",".$ValueCashDiskon.",".$TOP.",".$TglJtoOrder.",".$Salesman.",".$NamaSalesman.",".$Rayon.",".$NamaRayon.",".$Status.",".$TipeDokumen.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$LainLain.",".$Materai.",".$OngkosKirim.",".$Total.",".$Keterangan1.",".$Keterangan2.",".$Barcode.",".$QrCode.",".$NoSO.",".$NoFaktur.",".$NoSP.",".$statusPusat.",".$TipeFaktur.",".$NoIDPaket.",".$KeteranganTender.",".$flag_closing.",".$ppn_pelanggan.",".$user_approve.",".$time_approve.",".$status_approve.",".$user_edit.",".$time_edit.",".$time_batal.",".$user_batal.",".$created_at.",".$created_by.",".$modified_at.",".$modified_by.",".$status_retur.",".$user_retur.",".$time_retur.",".$status_validasi.",".$user_validasi.",".$time_validasi.",".$noretur.",".$acu2.",".$region.",".$ket_jto.",".$Kategori.",".$Kategori2.",".$Kategori3.",".$judul.",".$spesial.",".$alasan_jto."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_delivery_order_sales,",");
			      // $this->db2->query("DELETE FROM  trs_delivery_order_sales_".$this->kd_cab." WHERE cabang='".$this->cabang."'");
			      $querytrs_delivery_order_salesinsert = "REPLACE INTO `trs_delivery_order_sales_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_delivery_order_sales))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_delivery_order_sales') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_delivery_order_salesinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_delivery_order_sales <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_delivery_order_sales : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_delivery_order_sales ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_delivery_order_sales') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_delivery_order_sales <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_delivery_order_sales : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_delivery_order_sales_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_delivery_order_sales_detail = $this->db->query("SELECT * FROM trs_delivery_order_sales_detail WHERE date(TglDO) >= CURDATE() - INTERVAL 3 DAY or date(modified_at) >= CURDATE() - INTERVAL 3 DAY");


			// =========== insert  =============================
			if ($querytrs_delivery_order_sales_detail->num_rows() > 0) {
			    $string_trs_delivery_order_sales_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_delivery_order_sales_detail->result_array() as $row) {
	                $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
	                $NoDO             = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
	                $TglDO            = ($row['TglDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglDO'])."'";
	                $TimeDO           = ($row['TimeDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeDO'])."'";
	                $noline           = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
	                $Pengirim         = ($row['Pengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pengirim'])."'";
	                $NamaPengirim     = ($row['NamaPengirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPengirim'])."'";
	                $Pelanggan        = ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
	                $NamaPelanggan    = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
	                $AlamatKirim      = ($row['AlamatKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AlamatKirim'])."'";
	                $TipePelanggan    = ($row['TipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelanggan'])."'";
	                $NamaTipePelanggan= ($row['NamaTipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaTipePelanggan'])."'";
	                $NPWPPelanggan    = ($row['NPWPPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NPWPPelanggan'])."'";
	                $KategoriPelanggan= ($row['KategoriPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KategoriPelanggan'])."'";
	                $Acu              = ($row['Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Acu'])."'";
	                $CaraBayar        = ($row['CaraBayar'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CaraBayar'])."'";
	                $CashDiskon       = ($row['CashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CashDiskon'])."'";
	                $ValueCashDiskon  = ($row['ValueCashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCashDiskon'])."'";
	                $TOP              = ($row['TOP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TOP'])."'";
	                $TglJtoOrder      = ($row['TglJtoOrder'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJtoOrder'])."'";
	                $Salesman         = ($row['Salesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Salesman'])."'";
	                $NamaSalesman     = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
	                $Rayon            = ($row['Rayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Rayon'])."'";
	                $NamaRayon        = ($row['NamaRayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaRayon'])."'";
	                $Status           = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
	                $TipeDokumen      = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
	                $KodeProduk       = ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
	                $NamaProduk       = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
	                $UOM              = ($row['UOM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UOM'])."'";
	                $Harga            = ($row['Harga'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Harga'])."'";
	                $QtySO            = ($row['QtySO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtySO'])."'";
	                $BonusSO          = ($row['BonusSO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusSO'])."'";
	                $QtyDO            = ($row['QtyDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyDO'])."'";
	                $BonusDO          = ($row['BonusDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusDO'])."'";
	                $ValueBonus       = ($row['ValueBonus'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueBonus'])."'";
	                $DiscCab_onf      = ($row['DiscCab_onf'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCab_onf'])."'";
	                $ValueDiscCab_onf= ($row['ValueDiscCab_onf'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCab_onf'])."'";
	                $DiscCab          = ($row['DiscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCab'])."'";
	                $ValueDiscCab     = ($row['ValueDiscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCab'])."'";
	                $DiscCabTot       = ($row['DiscCabTot'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCabTot'])."'";
	             $ValueDiscCabTotal= ($row['ValueDiscCabTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCabTotal'])."'";
	                $DiscPrins1       = ($row['DiscPrins1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrins1'])."'";
	                $ValueDiscPrins1  = ($row['ValueDiscPrins1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrins1'])."'";
	                $DiscPrins2       = ($row['DiscPrins2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrins2'])."'";
	                $ValueDiscPrins2  = ($row['ValueDiscPrins2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrins2'])."'";
	                $DiscPrinsTot     = ($row['DiscPrinsTot'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrinsTot'])."'";
	       $ValueDiscPrinsTotal= ($row['ValueDiscPrinsTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrinsTotal'])."'";
	                $DiscTotal       = ($row['DiscTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscTotal'])."'";
	                $ValueDiscTotal   = ($row['ValueDiscTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscTotal'])."'";
	                $Gross            = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
	                $Potongan         = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
	                $Value            = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
	                $Ppn              = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
	                $LainLain         = ($row['LainLain'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LainLain'])."'";
	                $Total            = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
	                $Keterangan1      = ($row['Keterangan1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan1'])."'";
	                $Keterangan2      = ($row['Keterangan2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan2'])."'";
	                $Barcode          = ($row['Barcode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Barcode'])."'";
	                $QrCode           = ($row['QrCode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QrCode'])."'";
	                $NoSO             = ($row['NoSO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSO'])."'";
	                $NoFaktur         = ($row['NoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoFaktur'])."'";
	                $DiscCabMax       = ($row['DiscCabMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCabMax'])."'";
	                $KetDiscCabMax    = ($row['KetDiscCabMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KetDiscCabMax'])."'";
	                $DiscPrinsMax     = ($row['DiscPrinsMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrinsMax'])."'";
	                $KetDiscPrinsMax  = ($row['KetDiscPrinsMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KetDiscPrinsMax'])."'";
	                $COGS             = ($row['COGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['COGS'])."'";
	                $TotalCOGS        = ($row['TotalCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TotalCOGS'])."'";
	                $BatchNo          = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
	                $ExpDate          = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
	                $TipeFaktur       = ($row['TipeFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeFaktur'])."'";
	                $NoIDPaket        = ($row['NoIDPaket'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoIDPaket'])."'";
	                $KeteranganTender= ($row['KeteranganTender'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KeteranganTender'])."'";
	                $NoBPB            = ($row['NoBPB'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoBPB'])."'";
	                $flag_closing     = ($row['flag_closing'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['flag_closing'])."'";
	                $picking_list     = ($row['picking_list'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['picking_list'])."'";
	                $BatchNoDok       = ($row['BatchNoDok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNoDok'])."'";
	                $BatchTglDok      = ($row['BatchTglDok'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchTglDok'])."'";
	                $Prinsipal        = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
	                $Prinsipal2       = ($row['Prinsipal2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal2'])."'";
	                $Supplier         = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
	                $Supplier2        = ($row['Supplier2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier2'])."'";
	                $Pabrik           = ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
	                $Farmalkes        = ($row['Farmalkes'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Farmalkes'])."'";
	                $QtyDO_awal       = ($row['QtyDO_awal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyDO_awal'])."'";
	                $BonusDO_awal     = ($row['BonusDO_awal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusDO_awal'])."'";
	                $user_approve     = ($row['user_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_approve'])."'";
	                $time_approve     = ($row['time_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_approve'])."'";
	                $status_approve   = ($row['status_approve'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_approve'])."'";
	                $user_edit        = ($row['user_edit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_edit'])."'";
	                $time_edit        = ($row['time_edit'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_edit'])."'";
	                $time_batal       = ($row['time_batal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['time_batal'])."'";
	                $user_batal       = ($row['user_batal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_batal'])."'";
	                $created_at       = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
	                $created_by       = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
	                $modified_at      = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
	                $modified_by      = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
	                $retur_qtyDO      = ($row['retur_qtyDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['retur_qtyDO'])."'";
	                $retur_bonusDO    = ($row['retur_bonusDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['retur_bonusDO'])."'";
	                $status_retur     = ($row['status_retur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_retur'])."'";
	                $status_validasi  = ($row['status_validasi'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['status_validasi'])."'";
	                $noretur          = ($row['noretur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noretur'])."'";
	                $region           = ($row['region'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['region'])."'";
	                $kota             = ($row['kota'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['kota'])."'";
	                $telp             = ($row['telp'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['telp'])."'";
	                $tipe_2           = ($row['tipe_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tipe_2'])."'";
	                $acu2             = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";

			          $string_trs_delivery_order_sales_detail .=  "(
			                              ".$Cabang.",".$NoDO.",".$TglDO.",".$TimeDO.",".$noline.",".$Pengirim.",".$NamaPengirim.",".$Pelanggan.",".$NamaPelanggan.",".$AlamatKirim.",".$TipePelanggan.",".$NamaTipePelanggan.",".$NPWPPelanggan.",".$KategoriPelanggan.",".$Acu.",".$CaraBayar.",".$CashDiskon.",".$ValueCashDiskon.",".$TOP.",".$TglJtoOrder.",".$Salesman.",".$NamaSalesman.",".$Rayon.",".$NamaRayon.",".$Status.",".$TipeDokumen.",".$KodeProduk.",".$NamaProduk.",".$UOM.",".$Harga.",".$QtySO.",".$BonusSO.",".$QtyDO.",".$BonusDO.",".$ValueBonus.",".$DiscCab_onf.",".$ValueDiscCab_onf.",".$DiscCab.",".$ValueDiscCab.",".$DiscCabTot.",".$ValueDiscCabTotal.",".$DiscPrins1.",".$ValueDiscPrins1.",".$DiscPrins2.",".$ValueDiscPrins2.",".$DiscPrinsTot.",".$ValueDiscPrinsTotal.",".$DiscTotal.",".$ValueDiscTotal.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$LainLain.",".$Total.",".$Keterangan1.",".$Keterangan2.",".$Barcode.",".$QrCode.",".$NoSO.",".$NoFaktur.",".$DiscCabMax.",".$KetDiscCabMax.",".$DiscPrinsMax.",".$KetDiscPrinsMax.",".$COGS.",".$TotalCOGS.",".$BatchNo.",".$ExpDate.",".$TipeFaktur.",".$NoIDPaket.",".$KeteranganTender.",".$NoBPB.",".$flag_closing.",".$picking_list.",".$BatchNoDok.",".$BatchTglDok.",".$Prinsipal.",".$Prinsipal2.",".$Supplier.",".$Supplier2.",".$Pabrik.",".$Farmalkes.",".$QtyDO_awal.",".$BonusDO_awal.",".$user_approve.",".$time_approve.",".$status_approve.",".$user_edit.",".$time_edit.",".$time_batal.",".$user_batal.",".$created_at.",".$created_by.",".$modified_at.",".$modified_by.",".$retur_qtyDO.",".$retur_bonusDO.",".$status_retur.",".$status_validasi.",".$noretur.",".$region.",".$kota.",".$telp.",".$tipe_2.",".$acu2."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_delivery_order_sales_detail,",");
			      // $this->db2->query("DELETE FROM  trs_delivery_order_sales_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."'");
			      $querytrs_delivery_order_sales_detailinsert = "REPLACE INTO `trs_delivery_order_sales_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_delivery_order_sales_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_delivery_order_sales_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_delivery_order_sales_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_delivery_order_sales_detail <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_delivery_order_sales_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_delivery_order_sales_detail ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_delivery_order_sales_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_delivery_order_sales_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_delivery_order_sales_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_faktur(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_faktur = $this->db->query("SELECT * FROM trs_faktur WHERE date(created_at) >= '2020-10-01'");


			// =========== insert   =============================
			if ($querytrs_faktur->num_rows() > 0) {
			    $string_trs_faktur="";
			    $jumlahbaru = 0;
			    $var_dump="";
			    foreach ($querytrs_faktur->result_array() as $row) {
			        $Cabang             = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
			        $NoFaktur           = ($row['NoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoFaktur'])."'";
			        $TglFaktur          = ($row['TglFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglFaktur'])."'";
			        $TimeFaktur         = ($row['TimeFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeFaktur'])."'";
			        $Pelanggan          = ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
			        $NamaPelanggan      = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
			        $AlamatFaktur       = ($row['AlamatFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AlamatFaktur'])."'";
			        $TipePelanggan      = ($row['TipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelanggan'])."'";
			        $NamaTipePelanggan  = ($row['NamaTipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaTipePelanggan'])."'";
			        $NPWPPelanggan      = ($row['NPWPPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NPWPPelanggan'])."'";
			        $TipePajak          = ($row['TipePajak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePajak'])."'";
			        $NoPajak            = ($row['NoPajak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoPajak'])."'";
			        $KategoriPelanggan  = ($row['KategoriPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KategoriPelanggan'])."'";
			        $Acu                = ($row['Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Acu'])."'";
			        $CaraBayar          = ($row['CaraBayar'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CaraBayar'])."'";
			        $CashDiskon         = ($row['CashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CashDiskon'])."'";
			        $ValueCashDiskon    = ($row['ValueCashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCashDiskon'])."'";
			        $TOP                = ($row['TOP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TOP'])."'";
			        $TglJtoFaktur       = ($row['TglJtoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJtoFaktur'])."'";
			        $Salesman           = ($row['Salesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Salesman'])."'";
			        $NamaSalesman       = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
			        $Rayon              = ($row['Rayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Rayon'])."'";
			        $NamaRayon          = ($row['NamaRayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaRayon'])."'";
			        $Status             = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
			        $TipeDokumen        = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
			        $Gross              = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
			        $Potongan           = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
			        $Value              = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
			        $Ppn                = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
			        $LainLain           = ($row['LainLain'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LainLain'])."'";
			        $Materai            = ($row['Materai'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Materai'])."'";
			        $OngkosKirim        = ($row['OngkosKirim'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['OngkosKirim'])."'";
			        $Total              = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
			        $Saldo              = ($row['Saldo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Saldo'])."'";
			        $Keterangan1        = ($row['Keterangan1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan1'])."'";
			        $Keterangan2        = ($row['Keterangan2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan2'])."'";
			        $Barcode            = ($row['Barcode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Barcode'])."'";
			        $QrCode             = ($row['QrCode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QrCode'])."'";
			        $NoSO               = ($row['NoSO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSO'])."'";
			        $NoDO               = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
			        $NoSP               = ($row['NoSP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSP'])."'";
			        $statusPusat        = ($row['statusPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['statusPusat'])."'";
			        $StatusInkaso       = ($row['StatusInkaso'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusInkaso'])."'";
			        $TimeInkaso         = ($row['TimeInkaso'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeInkaso'])."'";
			        $TipeFaktur         = ($row['TipeFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeFaktur'])."'";
			        $NoIDPaket          = ($row['NoIDPaket'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoIDPaket'])."'";
			        $KeteranganTender   = ($row['KeteranganTender'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KeteranganTender'])."'";
			        $Dokumen_lampiran   = ($row['Dokumen_lampiran'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Dokumen_lampiran'])."'";
			        $umur_faktur        = ($row['umur_faktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['umur_faktur'])."'";
			        $umur_pelunasan     = ($row['umur_pelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['umur_pelunasan'])."'";
			        $TglPelunasan       = ($row['TglPelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglPelunasan'])."'";
			        $ppn_pelanggan      = ($row['ppn_pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ppn_pelanggan'])."'";
			        $saldo_giro         = ($row['saldo_giro'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['saldo_giro'])."'";
			        $nodih              = ($row['nodih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['nodih'])."'";
			        $tgldih             = ($row['tgldih'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tgldih'])."'";
			        $created_at         = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
			        $created_by         = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
			        $modified_at        = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
			        $modified_by        = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
			        $counter_print      = ($row['counter_print'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['counter_print'])."'";
			        $acu2               = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";
			        $region             = ($row['region'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['region'])."'";
			        $ket_jto            = ($row['ket_jto'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ket_jto'])."'";
			        $Kategori           = ($row['Kategori'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori'])."'";
			        $Kategori2          = ($row['Kategori2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori2'])."'";
			        $Kategori3          = ($row['Kategori3'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Kategori3'])."'";
			        $judul              = ($row['judul'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['judul'])."'";
			        $spesial            = ($row['spesial'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['spesial'])."'";
			        $alasan_jto         = ($row['alasan_jto'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['alasan_jto'])."'";

			        $string_trs_faktur .=  "(
			        ".$Cabang.",".$NoFaktur.",".$TglFaktur.",".$TimeFaktur.",".$Pelanggan.",".$NamaPelanggan.",".$AlamatFaktur.",".$TipePelanggan.",".$NamaTipePelanggan.",".$NPWPPelanggan.",".$TipePajak.",".$NoPajak.",".$KategoriPelanggan.",".$Acu.",".$CaraBayar.",".$CashDiskon.",".$ValueCashDiskon.",".$TOP.",".$TglJtoFaktur.",".$Salesman.",".$NamaSalesman.",".$Rayon.",".$NamaRayon.",".$Status.",".$TipeDokumen.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$LainLain.",".$Materai.",".$OngkosKirim.",".$Total.",".$Saldo.",".$Keterangan1.",".$Keterangan2.",".$Barcode.",".$QrCode.",".$NoSO.",".$NoDO.",".$NoSP.",".$statusPusat.",".$StatusInkaso.",".$TimeInkaso.",".$TipeFaktur.",".$NoIDPaket.",".$KeteranganTender.",".$Dokumen_lampiran.",".$umur_faktur.",".$umur_pelunasan.",".$TglPelunasan.",".$ppn_pelanggan.",".$saldo_giro.",".$nodih.",".$tgldih.",".$created_at.",".$created_by.",".$modified_at.",".$modified_by.",".$counter_print.",".$acu2.",".$region.",".$ket_jto.",".$Kategori.",".$Kategori2.",".$Kategori3.",".$judul.",".$spesial.",".$alasan_jto."),";

			        $jumlahbaru++;
			    }
			    $var_dump=rtrim($string_trs_faktur,",");
			    /*$this->db2->query("DELETE FROM  trs_faktur_".$this->kd_cab."" WHERE cabang='$cabang' AND DATE_FORMAT(TglFaktur,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");*/

			    $querytrs_fakturinsert = "REPLACE INTO `trs_faktur_".$this->kd_cab."` VALUES ".$var_dump;

			    if(empty($string_trs_faktur))  {
				     echo "No insert found <br>";
				     $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur') WHERE Cabang = '".$this->cabang."' ");
				 }
				 else{
				    if ($this->db2->query($querytrs_fakturinsert) === TRUE) {
				        echo "Berhasil insert ".$jumlahbaru ." trs_faktur <br>";
				        $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
				    } else {
				        echo  "gagal insert trs_faktur ".$this->db2->error ."<br>";
				        $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur') WHERE Cabang = '".$this->cabang."' ");
				    }

				}
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_faktur <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_faktur_detail(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_faktur_detail = $this->db->query("SELECT * FROM trs_faktur_detail WHERE date(created_at) >= '2020-10-01' ");


			// =========== insert   =============================
			if ($querytrs_faktur_detail->num_rows() > 0) {
			    $string_trs_faktur_detail="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_faktur_detail->result_array() as $row) {
	                $Cabang           = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
	                $NoFaktur         = ($row['NoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoFaktur'])."'";
	                $noline           = ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
	                $TglFaktur        = ($row['TglFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglFaktur'])."'";
	                $TimeFaktur       = ($row['TimeFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TimeFaktur'])."'";
	                $Pelanggan        = ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
	                $NamaPelanggan    = ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
	                $AlamatFaktur     = ($row['AlamatFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AlamatFaktur'])."'";
	                $TipePelanggan    = ($row['TipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipePelanggan'])."'";
	             $NamaTipePelanggan= ($row['NamaTipePelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaTipePelanggan'])."'";
	                $NPWPPelanggan    = ($row['NPWPPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NPWPPelanggan'])."'";
	            $KategoriPelanggan= ($row['KategoriPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KategoriPelanggan'])."'";
	                $Acu             = ($row['Acu'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Acu'])."'";
	                $CaraBayar       = ($row['CaraBayar'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CaraBayar'])."'";
	                $CashDiskon      = ($row['CashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['CashDiskon'])."'";
	                $ValueCashDiskon = ($row['ValueCashDiskon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueCashDiskon'])."'";
	                $TOP             = ($row['TOP'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TOP'])."'";
	                $TglJtoFaktur    = ($row['TglJtoFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TglJtoFaktur'])."'";
	                $Salesman        = ($row['Salesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Salesman'])."'";
	                $NamaSalesman    = ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
	                $Rayon           = ($row['Rayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Rayon'])."'";
	                $NamaRayon       = ($row['NamaRayon'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaRayon'])."'";
	                $Status          = ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
	                $TipeDokumen     = ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
	                $KodeProduk      = ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
	                $NamaProduk      = ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
	                $UOM             = ($row['UOM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UOM'])."'";
	                $Harga           = ($row['Harga'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Harga'])."'";
	                $QtyDO           = ($row['QtyDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyDO'])."'";
	                $BonusDO         = ($row['BonusDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusDO'])."'";
	                $QtyFaktur       = ($row['QtyFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QtyFaktur'])."'";
	                $BonusFaktur     = ($row['BonusFaktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BonusFaktur'])."'";
	                $ValueBonus      = ($row['ValueBonus'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueBonus'])."'";
	                $DiscCab_onf     = ($row['DiscCab_onf'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCab_onf'])."'";
	                $ValueDiscCab_onf= ($row['ValueDiscCab_onf'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCab_onf'])."'";
	                $DiscCab         = ($row['DiscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCab'])."'";
	                $ValueDiscCab    = ($row['ValueDiscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCab'])."'";
	                $DiscCabTot      = ($row['DiscCabTot'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCabTot'])."'";
	             $ValueDiscCabTotal= ($row['ValueDiscCabTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscCabTotal'])."'";
	                $DiscPrins1      = ($row['DiscPrins1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrins1'])."'";
	                $ValueDiscPrins1 = ($row['ValueDiscPrins1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrins1'])."'";
	                $DiscPrins2      = ($row['DiscPrins2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrins2'])."'";
	                $ValueDiscPrins2 = ($row['ValueDiscPrins2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrins2'])."'";
	                $DiscPrinsTot    = ($row['DiscPrinsTot'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrinsTot'])."'";
	       $ValueDiscPrinsTotal= ($row['ValueDiscPrinsTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscPrinsTotal'])."'";
	                $DiscTotal       = ($row['DiscTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscTotal'])."'";
	                $ValueDiscTotal  = ($row['ValueDiscTotal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDiscTotal'])."'";
	                $Gross           = ($row['Gross'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gross'])."'";
	                $Potongan        = ($row['Potongan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Potongan'])."'";
	                $Value           = ($row['Value'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Value'])."'";
	                $Ppn             = ($row['Ppn'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Ppn'])."'";
	                $LainLain        = ($row['LainLain'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['LainLain'])."'";
	                $Total           = ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
	                $Keterangan1     = ($row['Keterangan1'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan1'])."'";
	                $Keterangan2     = ($row['Keterangan2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Keterangan2'])."'";
	                $Barcode         = ($row['Barcode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Barcode'])."'";
	                $QrCode          = ($row['QrCode'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['QrCode'])."'";
	                $NoSO            = ($row['NoSO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoSO'])."'";
	                $NoDO            = ($row['NoDO'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDO'])."'";
	                $DiscCabMax      = ($row['DiscCabMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscCabMax'])."'";
	                $KetDiscCabMax   = ($row['KetDiscCabMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KetDiscCabMax'])."'";
	                $DiscPrinsMax    = ($row['DiscPrinsMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DiscPrinsMax'])."'";
	                $KetDiscPrinsMax = ($row['KetDiscPrinsMax'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KetDiscPrinsMax'])."'";
	                $COGS            = ($row['COGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['COGS'])."'";
	                $TotalCOGS       = ($row['TotalCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TotalCOGS'])."'";
	                $BatchNo         = ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
	                $ExpDate         = ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
	                $NoBPB           = ($row['NoBPB'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoBPB'])."'";
	                $Prinsipal       = ($row['Prinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal'])."'";
	                $Prinsipal2      = ($row['Prinsipal2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Prinsipal2'])."'";
	                $Supplier        = ($row['Supplier'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier'])."'";
	                $Supplier2       = ($row['Supplier2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Supplier2'])."'";
	                $Pabrik          = ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
	                $Farmalkes       = ($row['Farmalkes'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Farmalkes'])."'";
	                $sisa_retur      = ($row['sisa_retur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['sisa_retur'])."'";
	                $created_at      = ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
	                $created_by      = ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
	                $modified_at     = ($row['modified_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_at'])."'";
	                $modified_by     = ($row['modified_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['modified_by'])."'";
	                $kota            = ($row['kota'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['kota'])."'";
	                $telp            = ($row['telp'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['telp'])."'";
	                $tipe_2          = ($row['tipe_2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['tipe_2'])."'";
	                $region          = ($row['region'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['region'])."'";
	                $acu2            = ($row['acu2'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['acu2'])."'";

			          $string_trs_faktur_detail .=  "(
			                              ".$Cabang.",".$NoFaktur.",".$noline.",".$TglFaktur.",".$TimeFaktur.",".$Pelanggan.",".$NamaPelanggan.",".$AlamatFaktur.",".$TipePelanggan.",".$NamaTipePelanggan.",".$NPWPPelanggan.",".$KategoriPelanggan.",".$Acu.",".$CaraBayar.",".$CashDiskon.",".$ValueCashDiskon.",".$TOP.",".$TglJtoFaktur.",".$Salesman.",".$NamaSalesman.",".$Rayon.",".$NamaRayon.",".$Status.",".$TipeDokumen.",".$KodeProduk.",".$NamaProduk.",".$UOM.",".$Harga.",".$QtyDO.",".$BonusDO.",".$QtyFaktur.",".$BonusFaktur.",".$ValueBonus.",".$DiscCab_onf.",".$ValueDiscCab_onf.",".$DiscCab.",".$ValueDiscCab.",".$DiscCabTot.",".$ValueDiscCabTotal.",".$DiscPrins1.",".$ValueDiscPrins1.",".$DiscPrins2.",".$ValueDiscPrins2.",".$DiscPrinsTot.",".$ValueDiscPrinsTotal.",".$DiscTotal.",".$ValueDiscTotal.",".$Gross.",".$Potongan.",".$Value.",".$Ppn.",".$LainLain.",".$Total.",".$Keterangan1.",".$Keterangan2.",".$Barcode.",".$QrCode.",".$NoSO.",".$NoDO.",".$DiscCabMax.",".$KetDiscCabMax.",".$DiscPrinsMax.",".$KetDiscPrinsMax.",".$COGS.",".$TotalCOGS.",".$BatchNo.",".$ExpDate.",".$NoBPB.",".$Prinsipal.",".$Prinsipal2.",".$Supplier.",".$Supplier2.",".$Pabrik.",".$Farmalkes.",".$sisa_retur.",".$created_at.",".$created_by.",".$modified_at.",".$modified_by.",".$kota.",".$telp.",".$tipe_2.",".$region.",".$acu2."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_faktur_detail,",");
			      // $this->db2->query("DELETE FROM  trs_faktur_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."'");

			      $querytrs_faktur_detailinsert = "REPLACE INTO `trs_faktur_detail_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_faktur_detail))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur_detail') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_faktur_detailinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_faktur_detail <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_faktur_detail ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur_detail') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_faktur_detail <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur_detail : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_faktur_cndn(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
			$querytrs_faktur_cndn = $this->db->query("SELECT * FROM trs_faktur_cndn WHERE date(TanggalCNDN) >= CURDATE() - INTERVAL 3 DAY OR DATE(updated_at) >= CURDATE() - INTERVAL 3 DAY ");


			// =========== insert   =============================
			if ($querytrs_faktur_cndn->num_rows() > 0) {
			    $string_trs_faktur_dcndn="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_faktur_cndn->result_array() as $row) {
	                $Cabang           	= ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
					$NoDokumen			= ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
					$Faktur				= ($row['Faktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Faktur'])."'";
					$noline				= ($row['noline'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['noline'])."'";
					$Approval			= ($row['Approval'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Approval'])."'";
					$TipeDokumen		= ($row['TipeDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TipeDokumen'])."'";
					$Pelanggan			= ($row['Pelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pelanggan'])."'";
					$NamaPelanggan		= ($row['NamaPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPelanggan'])."'";
					$AlamatPelanggan	= ($row['AlamatPelanggan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['AlamatPelanggan'])."'";
					$Perhitungan		= ($row['Perhitungan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Perhitungan'])."'";
					$TanggalCNDN		= ($row['TanggalCNDN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TanggalCNDN'])."'";
					$KodeProduk			= ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
					$Produk				= ($row['Produk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Produk'])."'";
					$DasarPerhitungan	= ($row['DasarPerhitungan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DasarPerhitungan'])."'";
					$Persen				= ($row['Persen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Persen'])."'";
					$Rupiah				= ($row['Rupiah'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Rupiah'])."'";
					$Jumlah				= ($row['Jumlah'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Jumlah'])."'";
					$DscCab				= ($row['DscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['DscCab'])."'";
					$Status				= ($row['Status'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Status'])."'";
					$Banyak				= ($row['Banyak'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Banyak'])."'";
					$ValueDscCab		= ($row['ValueDscCab'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ValueDscCab'])."'";
					$Total				= ($row['Total'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Total'])."'";
					$StatusCNDN			= ($row['StatusCNDN'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['StatusCNDN'])."'";
					$NoUsulan			= ($row['NoUsulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoUsulan'])."'";
					$created_at			= ($row['created_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_at'])."'";
					$created_by			= ($row['created_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['created_by'])."'";
					$updated_at			= ($row['updated_at'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_at'])."'";
					$updated_by			= ($row['updated_by'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['updated_by'])."'";
					$Approve_BM			= ($row['Approve_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Approve_BM'])."'";
					$date_BM			= ($row['date_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['date_BM'])."'";
					$user_BM			= ($row['user_BM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_BM'])."'";
					$Approve_RBM		= ($row['Approve_RBM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Approve_RBM'])."'";
					$date_RBM			= ($row['date_RBM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['date_RBM'])."'";
					$user_RBM			= ($row['user_RBM'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['user_RBM'])."'";
					$umur_faktur		= ($row['umur_faktur'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['umur_faktur'])."'";
					$umur_pelunasan		= ($row['umur_pelunasan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['umur_pelunasan'])."'";
					$statusPusat		= ($row['statusPusat'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['statusPusat'])."'";
					$Salesman			= ($row['Salesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Salesman'])."'";
					$NamaSalesman		= ($row['NamaSalesman'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaSalesman'])."'";
					$counter_print		= ($row['counter_print'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['counter_print'])."'";

			          $string_trs_faktur_dcndn .=  "(
			                              ".$Cabang.",".$NoDokumen.", ".$Faktur.", ".$noline.", ".$Approval.", ".$TipeDokumen.", ".$Pelanggan.", ".$NamaPelanggan.", ".$AlamatPelanggan.", ".$Perhitungan.", ".$TanggalCNDN.", ".$KodeProduk.", ".$Produk.", ".$DasarPerhitungan.", ".$Persen.", ".$Rupiah.", ".$Jumlah.", ".$DscCab.", ".$Status.", ".$Banyak.", ".$ValueDscCab.", ".$Total.", ".$StatusCNDN.", ".$NoUsulan.", ".$created_at.", ".$created_by.", ".$updated_at.", ".$updated_by.", ".$Approve_BM.", ".$date_BM.", ".$user_BM.", ".$Approve_RBM.", ".$date_RBM.", ".$user_RBM.", ".$umur_faktur.", ".$umur_pelunasan.", ".$statusPusat.", ".$Salesman.", ".$NamaSalesman.", ".$counter_print."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_faktur_dcndn,",");
			      // $this->db2->query("DELETE FROM  trs_faktur_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."'");

			      $querytrs_faktur_cndninsert = "REPLACE INTO `trs_faktur_cndn_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_faktur_dcndn))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur_cndn') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_faktur_cndninsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_faktur_cndn <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur_cndn : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_faktur_cndn ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_faktur_cndn') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_faktur_cndn <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_faktur_cndn : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_invday_sum(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
        	$day = date('d');
			$querytrs_invday_sum = $this->db->query("SELECT * FROM trs_invday_sum WHERE IFNULL(SAwal$day,'') <> 0");


			// =========== insert   =============================
			if ($querytrs_invday_sum->num_rows() > 0) {
			    $string_trs_invday="";
			    $jumlahbaru = 0;
			    $var_dump="";
			      foreach ($querytrs_invday_sum->result_array() as $row) {
					$Tahun			= ($row['Tahun'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tahun'])."'";
					$Bulan			= ($row['Bulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bulan'])."'";
					$Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
					$KodePrinsipal	= ($row['KodePrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePrinsipal'])."'";
					$NamaPrinsipal	= ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
					$Pabrik			= ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
					$KodeProduk		= ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
					$NamaProduk		= ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
					$Gudang			= ($row['Gudang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gudang'])."'";
					$Indeks			= ($row['Indeks'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Indeks'])."'";
					$UnitCOGS		= ($row['UnitCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitCOGS'])."'";
					$HNA			= ($row['HNA'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['HNA'])."'";
					$SAwal01		= ($row['SAwal01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal01'])."'";
					$VAwal01		= ($row['VAwal01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal01'])."'";
					$SAwal02		= ($row['SAwal02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal02'])."'";
					$VAwal02		= ($row['VAwal02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal02'])."'";
					$SAwal03		= ($row['SAwal03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal03'])."'";
					$VAwal03		= ($row['VAwal03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal03'])."'";
					$SAwal04		= ($row['SAwal04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal04'])."'";
					$VAwal04		= ($row['VAwal04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal04'])."'";
					$SAwal05		= ($row['SAwal05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal05'])."'";
					$VAwal05		= ($row['VAwal05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal05'])."'";
					$SAwal06		= ($row['SAwal06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal06'])."'";
					$VAwal06		= ($row['VAwal06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal06'])."'";
					$SAwal07		= ($row['SAwal07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal07'])."'";
					$VAwal07		= ($row['VAwal07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal07'])."'";
					$SAwal08		= ($row['SAwal08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal08'])."'";
					$VAwal08		= ($row['VAwal08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal08'])."'";
					$SAwal09		= ($row['SAwal09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal09'])."'";
					$VAwal09		= ($row['VAwal09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal09'])."'";
					$SAwal10		= ($row['SAwal10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal10'])."'";
					$VAwal10		= ($row['VAwal10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal10'])."'";
					$SAwal11		= ($row['SAwal11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal11'])."'";
					$VAwal11		= ($row['VAwal11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal11'])."'";
					$SAwal12		= ($row['SAwal12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal12'])."'";
					$VAwal12		= ($row['VAwal12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal12'])."'";
					$SAwal13		= ($row['SAwal13'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal13'])."'";
					$VAwal13		= ($row['VAwal13'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal13'])."'";
					$SAwal14		= ($row['SAwal14'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal14'])."'";
					$VAwal14		= ($row['VAwal14'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal14'])."'";
					$SAwal15		= ($row['SAwal15'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal15'])."'";
					$VAwal15		= ($row['VAwal15'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal15'])."'";
					$SAwal16		= ($row['SAwal16'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal16'])."'";
					$VAwal16		= ($row['VAwal16'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal16'])."'";
					$SAwal17		= ($row['SAwal17'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal17'])."'";
					$VAwal17		= ($row['VAwal17'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal17'])."'";
					$SAwal18		= ($row['SAwal18'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal18'])."'";
					$VAwal18		= ($row['VAwal18'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal18'])."'";
					$SAwal19		= ($row['SAwal19'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal19'])."'";
					$VAwal19		= ($row['VAwal19'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal19'])."'";
					$SAwal20		= ($row['SAwal20'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal20'])."'";
					$VAwal20		= ($row['VAwal20'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal20'])."'";
					$SAwal21		= ($row['SAwal21'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal21'])."'";
					$VAwal21		= ($row['VAwal21'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal21'])."'";
					$SAwal22		= ($row['SAwal22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal22'])."'";
					$VAwal22		= ($row['VAwal22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal22'])."'";
					$SAwal23		= ($row['SAwal23'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal23'])."'";
					$VAwal23		= ($row['VAwal23'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal23'])."'";
					$SAwal24		= ($row['SAwal24'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal24'])."'";
					$VAwal24		= ($row['VAwal24'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal24'])."'";
					$SAwal25		= ($row['SAwal25'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal25'])."'";
					$VAwal25		= ($row['VAwal25'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal25'])."'";
					$SAwal26		= ($row['SAwal26'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal26'])."'";
					$VAwal26		= ($row['VAwal26'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal26'])."'";
					$SAwal27		= ($row['SAwal27'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal27'])."'";
					$VAwal27		= ($row['VAwal27'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal27'])."'";
					$SAwal28		= ($row['SAwal28'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal28'])."'";
					$VAwal28		= ($row['VAwal28'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal28'])."'";
					$SAwal29		= ($row['SAwal29'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal29'])."'";
					$VAwal29		= ($row['VAwal29'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal29'])."'";
					$SAwal30		= ($row['SAwal30'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal30'])."'";
					$VAwal30		= ($row['VAwal30'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal30'])."'";
					$SAwal31		= ($row['SAwal31'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwal31'])."'";
					$VAwal31		= ($row['VAwal31'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwal31'])."'";


			          $string_trs_invday .=  "(
			                              ".$Tahun.",".$Bulan.", ".$Cabang.", ".$KodePrinsipal.", ".$NamaPrinsipal.", ".$Pabrik.", ".$KodeProduk.", ".$NamaProduk.", ".$Gudang.", ".$Indeks.", ".$UnitCOGS.", ".$HNA.", ".$SAwal01.", ".$VAwal01.", ".$SAwal02.", ".$VAwal02.", ".$SAwal03.", ".$VAwal03.", ".$SAwal04.", ".$VAwal04.", ".$SAwal05.", ".$VAwal05.", ".$SAwal06.", ".$VAwal06.", ".$SAwal07.", ".$VAwal07.", ".$SAwal08.", ".$VAwal08.", ".$SAwal09.", ".$VAwal09.", ".$SAwal10.", ".$VAwal10.", ".$SAwal11.", ".$VAwal11.", ".$SAwal12.", ".$VAwal12.", ".$SAwal13.", ".$VAwal13.", ".$SAwal14.", ".$VAwal14.", ".$SAwal15.", ".$VAwal15.", ".$SAwal16.", ".$VAwal16.", ".$SAwal17.", ".$VAwal17.", ".$SAwal18.", ".$VAwal18.", ".$SAwal19.", ".$VAwal19.", ".$SAwal20.", ".$VAwal20.", ".$SAwal21.", ".$VAwal21.", ".$SAwal22.", ".$VAwal22.", ".$SAwal23.", ".$VAwal23.", ".$SAwal24.", ".$VAwal24.", ".$SAwal25.", ".$VAwal25.", ".$SAwal26.", ".$VAwal26.", ".$SAwal27.", ".$VAwal27.", ".$SAwal28.", ".$VAwal28.", ".$SAwal29.", ".$VAwal29.", ".$SAwal30.", ".$VAwal30.", ".$SAwal31.", ".$VAwal31."),";

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_invday,",");
			      // $this->db2->query("DELETE FROM  trs_faktur_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."'");

			      $querytrs_invday_suminsert = "REPLACE INTO `trs_invday_sum_".$this->kd_cab."` VALUES ".$var_dump;

			      if(empty($string_trs_invday))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invday_sum') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_invday_suminsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_invday_sum <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_invday_sum : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_invday_sum ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invday_sum') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_invday_sum <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_invday_sum : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}

    function update_trs_invday_det(){
    	$this->db2 = $this->load->database('sstpusat', TRUE);   

        if ($this->db2->conn_id == TRUE) {
        	
        	//=== cabang ===============
        	$day = date('d');
			$querytrs_invday_det = $this->db->query("SELECT * FROM trs_invday_det WHERE IFNULL(SAwa$day,'') <> 0");


			// =========== insert   =============================
			if ($querytrs_invday_det->num_rows() > 0) {
			    $string_trs_invday=$string_trs_invday2="";
			    $jumlahbaru = 0;
			    $var_dump=$var_dump2="";
			      foreach ($querytrs_invday_det->result_array() as $row) {
	                $Tahun			= ($row['Tahun'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Tahun'])."'";
					$Bulan			= ($row['Bulan'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Bulan'])."'";
					$Cabang         = ($row['Cabang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Cabang'])."'";
					$KodePrinsipal	= ($row['KodePrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodePrinsipal'])."'";
					$NamaPrinsipal	= ($row['NamaPrinsipal'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaPrinsipal'])."'";
					$Pabrik			= ($row['Pabrik'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Pabrik'])."'";
					$KodeProduk		= ($row['KodeProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['KodeProduk'])."'";
					$NamaProduk		= ($row['NamaProduk'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NamaProduk'])."'";
					$UnitCOGS		= ($row['UnitCOGS'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['UnitCOGS'])."'";
					$BatchNo		= ($row['BatchNo'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['BatchNo'])."'";
					$ExpDate		= ($row['ExpDate'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['ExpDate'])."'";
					$NoDokumen		= ($row['NoDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['NoDokumen'])."'";
					$Gudang			= ($row['Gudang'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['Gudang'])."'";
					$TanggalDokumen	= ($row['TanggalDokumen'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['TanggalDokumen'])."'";
					$SAwa01		= ($row['SAwa01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa01'])."'";
					$VAwa01		= ($row['VAwa01'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa01'])."'";
					$SAwa02		= ($row['SAwa02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa02'])."'";
					$VAwa02		= ($row['VAwa02'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa02'])."'";
					$SAwa03		= ($row['SAwa03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa03'])."'";
					$VAwa03		= ($row['VAwa03'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa03'])."'";
					$SAwa04		= ($row['SAwa04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa04'])."'";
					$VAwa04		= ($row['VAwa04'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa04'])."'";
					$SAwa05		= ($row['SAwa05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa05'])."'";
					$VAwa05		= ($row['VAwa05'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa05'])."'";
					$SAwa06		= ($row['SAwa06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa06'])."'";
					$VAwa06		= ($row['VAwa06'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa06'])."'";
					$SAwa07		= ($row['SAwa07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa07'])."'";
					$VAwa07		= ($row['VAwa07'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa07'])."'";
					$SAwa08		= ($row['SAwa08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa08'])."'";
					$VAwa08		= ($row['VAwa08'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa08'])."'";
					$SAwa09		= ($row['SAwa09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa09'])."'";
					$VAwa09		= ($row['VAwa09'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa09'])."'";
					$SAwa10		= ($row['SAwa10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa10'])."'";
					$VAwa10		= ($row['VAwa10'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa10'])."'";
					$SAwa11		= ($row['SAwa11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa11'])."'";
					$VAwa11		= ($row['VAwa11'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa11'])."'";
					$SAwa12		= ($row['SAWa12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAWa12'])."'";
					$VAwa12		= ($row['VAwa12'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa12'])."'";
					$SAwa13		= ($row['SAwa13'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa13'])."'";
					$VAwa13		= ($row['VAwa13'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa13'])."'";
					$SAwa14		= ($row['SAwa14'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa14'])."'";
					$VAwa14		= ($row['VAwa14'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa14'])."'";
					$SAwa15		= ($row['SAwa15'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa15'])."'";
					$VAwa15		= ($row['VAwa15'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa15'])."'";
					$SAwa16		= ($row['SAwa16'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa16'])."'";
					$VAwa16		= ($row['VAwa16'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa16'])."'";
					$SAwa17		= ($row['SAwa17'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa17'])."'";
					$VAwa17		= ($row['VAwa17'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa17'])."'";
					$SAwa18		= ($row['SAwa18'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa18'])."'";
					$VAwa18		= ($row['VAwa18'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa18'])."'";
					$SAwa19		= ($row['SAwa19'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa19'])."'";
					$VAwa19		= ($row['VAwa19'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa19'])."'";
					$SAwa20		= ($row['SAwa20'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa20'])."'";
					$VAwa20		= ($row['VAwa20'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa20'])."'";
					$SAwa21		= ($row['SAwa21'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa21'])."'";
					$VAwa21		= ($row['VAwa21'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa21'])."'";
					$SAwa22		= ($row['SAwa22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa22'])."'";
					$VAwa22		= ($row['VAwa22'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa22'])."'";
					$SAwa23		= ($row['SAwa23'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa23'])."'";
					$VAwa23		= ($row['VAwa23'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa23'])."'";
					$SAwa24		= ($row['SAwa24'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa24'])."'";
					$VAwa24		= ($row['VAwa24'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa24'])."'";
					$SAwa25		= ($row['SAwa25'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa25'])."'";
					$VAwa25		= ($row['VAwa25'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa25'])."'";
					$SAwa26		= ($row['SAwa26'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa26'])."'";
					$VAwa26		= ($row['VAwa26'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa26'])."'";
					$SAwa27		= ($row['SAwa27'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa27'])."'";
					$VAwa27		= ($row['VAwa27'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa27'])."'";
					$SAwa28		= ($row['SAwa28'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa28'])."'";
					$VAwa28		= ($row['VAwa28'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa28'])."'";
					$SAwa29		= ($row['SAwa29'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa29'])."'";
					$VAwa29		= ($row['VAwa29'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa29'])."'";
					$SAwa30		= ($row['SAwa30'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa30'])."'";
					$VAwa30		= ($row['VAwa30'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa30'])."'";
					$SAwa31		= ($row['SAwa31'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['SAwa31'])."'";
					$VAwa31		= ($row['VAwa31'] == null) ? "null" : "'".str_replace(["'",","], " ", $row['VAwa31'])."'";

			          if ($jumlahbaru <= 20000) {
				          $string_trs_invday .=  "(
			                              ".$Tahun.",".$Bulan.", ".$Cabang.", ".$KodePrinsipal.", ".$NamaPrinsipal.", ".$Pabrik.", ".$KodeProduk.", ".$NamaProduk.", ".$UnitCOGS.", ".$BatchNo.", ".$ExpDate.", ".$NoDokumen.", ".$Gudang.", ".$TanggalDokumen.",".$SAwa01.", ".$VAwa01.", ".$SAwa02.", ".$VAwa02.", ".$SAwa03.", ".$VAwa03.", ".$SAwa04.", ".$VAwa04.", ".$SAwa05.", ".$VAwa05.", ".$SAwa06.", ".$VAwa06.", ".$SAwa07.", ".$VAwa07.", ".$SAwa08.", ".$VAwa08.", ".$SAwa09.", ".$VAwa09.", ".$SAwa10.", ".$VAwa10.", ".$SAwa11.", ".$VAwa11.", ".$SAwa12.", ".$VAwa12.", ".$SAwa13.", ".$VAwa13.", ".$SAwa14.", ".$VAwa14.", ".$SAwa15.", ".$VAwa15.", ".$SAwa16.", ".$VAwa16.", ".$SAwa17.", ".$VAwa17.", ".$SAwa18.", ".$VAwa18.", ".$SAwa19.", ".$VAwa19.", ".$SAwa20.", ".$VAwa20.", ".$SAwa21.", ".$VAwa21.", ".$SAwa22.", ".$VAwa22.", ".$SAwa23.", ".$VAwa23.", ".$SAwa24.", ".$VAwa24.", ".$SAwa25.", ".$VAwa25.", ".$SAwa26.", ".$VAwa26.", ".$SAwa27.", ".$VAwa27.", ".$SAwa28.", ".$VAwa28.", ".$SAwa29.", ".$VAwa29.", ".$SAwa30.", ".$VAwa30.", ".$SAwa31.", ".$VAwa31."),";
		              }else{
		              	$string_trs_invday2 .=  "(
			                              ".$Tahun.",".$Bulan.", ".$Cabang.", ".$KodePrinsipal.", ".$NamaPrinsipal.", ".$Pabrik.", ".$KodeProduk.", ".$NamaProduk.", ".$UnitCOGS.", ".$BatchNo.", ".$ExpDate.", ".$NoDokumen.", ".$Gudang.", ".$TanggalDokumen.",".$SAwa01.", ".$VAwa01.", ".$SAwa02.", ".$VAwa02.", ".$SAwa03.", ".$VAwa03.", ".$SAwa04.", ".$VAwa04.", ".$SAwa05.", ".$VAwa05.", ".$SAwa06.", ".$VAwa06.", ".$SAwa07.", ".$VAwa07.", ".$SAwa08.", ".$VAwa08.", ".$SAwa09.", ".$VAwa09.", ".$SAwa10.", ".$VAwa10.", ".$SAwa11.", ".$VAwa11.", ".$SAwa12.", ".$VAwa12.", ".$SAwa13.", ".$VAwa13.", ".$SAwa14.", ".$VAwa14.", ".$SAwa15.", ".$VAwa15.", ".$SAwa16.", ".$VAwa16.", ".$SAwa17.", ".$VAwa17.", ".$SAwa18.", ".$VAwa18.", ".$SAwa19.", ".$VAwa19.", ".$SAwa20.", ".$VAwa20.", ".$SAwa21.", ".$VAwa21.", ".$SAwa22.", ".$VAwa22.", ".$SAwa23.", ".$VAwa23.", ".$SAwa24.", ".$VAwa24.", ".$SAwa25.", ".$VAwa25.", ".$SAwa26.", ".$VAwa26.", ".$SAwa27.", ".$VAwa27.", ".$SAwa28.", ".$VAwa28.", ".$SAwa29.", ".$VAwa29.", ".$SAwa30.", ".$VAwa30.", ".$SAwa31.", ".$VAwa31."),";
		              }

			          $jumlahbaru++;
			      }
			      $var_dump=rtrim($string_trs_invday,",");
			      // $this->db2->query("DELETE FROM  trs_faktur_detail_".$this->kd_cab." WHERE cabang='".$this->cabang."'");

			      $querytrs_invday_detinsert = "REPLACE INTO `trs_invday_det_".$this->kd_cab."` VALUES ".$var_dump;

			      if ($jumlahbaru >= 20000) {
			      	$var_dump2=rtrim($string_trs_invday2,",");

			      	$this->db2->query("REPLACE INTO `trs_invday_det_".$this->kd_cab."` VALUES ".$var_dump2);
			      }

			      if(empty($string_trs_invday))  {
			      			echo "No insert found <br>";
			      			$this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invday_det') WHERE Cabang = '".$this->cabang."' ");
			      }
			       else{
			        if ($this->db2->query($querytrs_invday_detinsert) === TRUE) {
			            echo "Berhasil insert ".$jumlahbaru ." trs_invday_det <br>";
			            $this->db2->query("UPDATE histori_cronjob SET count_table =count_table + 1, ket_berhasil =CONCAT(ket_berhasil, ' , trs_invday_det : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			        } else {
			            echo  "gagal insert trs_invday_det ".$this->db2->error ."<br>";
			            $this->db2->query("UPDATE histori_cronjob SET ket_gagal=CONCAT(ket_gagal, ' , trs_invday_det') WHERE Cabang = '".$this->cabang."' ");
			        }

			      }
			    
			} else {
			    $jumlahbaru = 0;
        		echo "Berhasil insert ".$jumlahbaru ." trs_invday_det <br>";
		            $this->db2->query("UPDATE histori_cronjob SET count_table = count_table + 1,ket_berhasil =CONCAT(ket_berhasil, ' , trs_invday_det : ".$jumlahbaru."') WHERE Cabang = '".$this->cabang."' ");
			}
   	 	}
   	}


}