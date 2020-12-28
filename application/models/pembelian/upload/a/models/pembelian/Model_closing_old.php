<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// ini_set('memory_limit', '910M');
class Model_closing extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->model('Model_main');
            $this->user = $this->session->userdata('username');
            $this->cabang = $this->session->userdata('cabang');
            $this->userLogged = $this->session->userdata('userLogged');
            $this->totaluserLogged = $this->session->userdata('total_userlogged');
    }

   
    public function listDataInvHis()
    {   
        $query = $this->db->query("select * from trs_invhis where Cabang = '".$this->cabang."'")->result();

        return $query;
    }

    public function setClosingBulan()
    {   
        $month = date('m');
        $year = date('Y');
        $date = date('Y-m-d');
        $nextmonth = date('Y-m-d', strtotime('+1 month'));
        $getmonth  = date('m', strtotime($nextmonth));
        $last_day = date("Y-m-t", strtotime($date));
        $valid = false;
        if($last_day != $date){
            log_message("error","bukan akhir bulan");
        }else{
            $valid = $this->db->query("truncate mst_closing");
            $valid = $this->db->query("insert into mst_closing values('".$this->cabang."',DATE_ADD(curdate(), INTERVAL 1 DAY),DATE_ADD(NOW(), INTERVAL 1 DAY))");
            if($month != '12'){
                $valid = $this->db->query("update trs_invsum set SAwal".$getmonth." = UnitStok
                                       where Tahun = '".date('Y')."'
                                    ");
            }else{
                $nextyear = date('Y-m-d', strtotime('+1 year'));
                $year  = date('Y', strtotime($nextyear));
                $query = $db->query("select * from trs_invsum where Tahun= '".date('Y')."'")->row();
                foreach ($query as $stok) {
                    $this->db->set("Tahun", $year);
                    $this->db->set("Cabang", $stok->Cabang);
                    $this->db->set("KodePrinsipal", $stok->KodePrinsipal);
                    $this->db->set("NamaPrinsipal", $stok->NamaPrinsipal);
                    $this->db->set("Pabrik", $stok->Pabrik);
                    $this->db->set("KodeProduk", $stok->KodeProduk);
                    $this->db->set("NamaProduk", $stok->NamaProduk);
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $ValueStok);
                    $this->db->set("Gudang", $Gudang);
                    $this->db->set("Indeks", $Indeks);
                    $this->db->set("UnitCOGS", $UnitCOGS);
                    $this->db->set("HNA", $HNA);
                    $this->db->set("SAwal01", $SAwal12);
                    $this->db->set("VAwal01", $VAwal12);
                    $this->db->set("AddedUser", $this->user);
                    $this->db->set("AddedTime", date("Y-m-d H:i:s"));
                    $valid = $this->db->insert('trs_invsum');
                }               

            }
            $cek = false;
            $cek = $this->db->query("insert into trs_settlement_stok_history select * from trs_settlement_stok_day");
            $cek = $this->db->query("insert into trs_settlement_kasbank_history select * from trs_settlement_kasbank_day");
            if($cek){
                $valid = $this->db->query('truncate trs_settlement_stok_day');
                $valid = $this->db->query('truncate trs_settlement_kasbank_day');
            }
            
        }

        return $valid;
    }

    public function setClosing()
    {   
        $valid = false;
        $valid = $this->db->query("truncate mst_closing");
            $valid = $this->db->query("insert into mst_closing values('".$this->cabang."',DATE_ADD(curdate(), INTERVAL 1 DAY),DATE_ADD(NOW(), INTERVAL 1 DAY))");
        return $valid;
    }


    public function getsettlementstokday(){
        $query = $this->db->query("select ifnull(count(KodeProduk),0) as count from trs_settlement_stok_day where tanggal = CURDATE()")->result();
        return $query;
    } 

    public function getsettlementkasbankday(){
        $query = $this->db->query("select ifnull(count(Transaksi),0) as count from trs_settlement_kasbank_day where tanggal = CURDATE()")->result();
        return $query;
    } 

    public function getsettlementstokmonth(){
        $query = $this->db->query("select ifnull(count(KodeProduk),0) as count from trs_settlement_stok_month where bulan = '".date('m')."' and tahun ='".date('Y')."'")->result();
        return $query;
    } 

    public function getsettlementkasbankmonth(){
        $query = $this->db->query("select ifnull(count(Transaksi),0) as count from trs_settlement_kasbank_month where bulan = '".date('M')."' and tahun ='".date('Y')."' ")->result();
        return $query;
    } 

     public function getbackup()
    {   
        set_time_limit(0);
        $valid=false;
        $connection = NULL;
        $mysql_host = "119.235.19.138";
        // $mysql_host = "192.168.1.200";
        $mysql_user = "sapta";
        $mysql_pass = "Sapta254*x";
        $username = "serversst";
        $password = "Sapta777*x";
        $mysql_database = "sst";
        $cabang = $this->cabang;
        $backup_folder = "/var/www/html/database_download/database_$cabang";
        $port = 22;
        $cab = "\"$cabang\"";

        //================ end cek ======================
        // if($valid==true){
            $connection = ssh2_connect($mysql_host, $port);
            $auth  = ssh2_auth_password($connection, $username, $password);
            if(!$auth){
                // throw new \Exception("Could not authenticate with username $username and password ");  
                log_message("error","Could not authenticate with username $username and password ");
                $valid = false;
            }else{
                    // // Execute a command on the connected server and capture the response
                $stream = ssh2_exec($connection,"mkdir /var/www/html/database_download/database_$cabang");
                $stream = ssh2_exec($connection,"chmod -R 777 /var/www/html/database_download");
                $stream = ssh2_exec($connection,"chmod -R 777 /var/www/html/database_download/database_$cabang");
                
                // === Create Backup Struktur Database ======
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -d -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database > $backup_folder/sst_struktur.sql"); 
                
                // exec("C:/xampp/mysql/bin/mysqldump.exe -d -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database > $backup_folder/sst_struktur.sql",$result,$result_value);

                // ===== Create Backup Master ==========
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database mst_absen mst_area mst_berita_salesman mst_bot mst_cabang mst_cara_bayar mst_channel_prinx mst_class mst_closing mst_counter mst_data_ams3 mst_diskon_beli mst_diskon_jual mst_ekspedisi mst_gl_bank mst_gl_coa mst_gl_transaksi mst_gl_transaksi_kat mst_group_pelanggan mst_gudang mst_harga mst_harga_beli_deal mst_harga_jual_deal mst_insentif_status mst_karyawan_dummy mst_karyawan_lama mst_kode_acu mst_kode_dokumen mst_kota mst_limit_pembelian mst_logo mst_mcl mst_npwp mst_pabrik mst_piutang_temp mst_prinsipal mst_prinsipal_limit mst_prinsipal_supplier mst_produk mst_produk_info mst_produk_priority mst_produk_status mst_program mst_rayon mst_retail mst_rute_salesman mst_seri_pajak mst_supervisor mst_supervisor_cabang mst_supplier mst_target_cabang mst_target_diskon_jual mst_target_ec mst_target_ins_ec mst_target_ipt mst_target_ot mst_target_ot_salesman mst_target_rot mst_target_sales_prinsipal mst_target_sales_total mst_target_salesman mst_target_tagihan mst_tipe2_prinx mst_tipe_pajak mst_tipepelanggan mst_usulan_pelanggan mst_usulan_produk mst_usulan_produk2 mst_usulanpelanggan mst_version mst_wilayah msupplier_voucher mutasi_gudang trs_efaktur_auto trs_efaktur_detail trs_efaktur_detail_temp trs_efaktur_header trs_efektif_call trs_eretur_auto > $backup_folder/sst_master_$cabang.sql");
                // ===== Create backup 3 master ===========
                // mst_user
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  mst_user --where='Cabang=$cab' > $backup_folder/mst_user_$cabang.sql");

                // mst_karyawan
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  mst_karyawan --where='Cabang=$cab' > $backup_folder/mst_karyawan_$cabang.sql");

                // mst_pelanggan
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  mst_pelanggan --where='Cabang=$cab' > $backup_folder/mst_pelanggan_$cabang.sql");
                //mst_inventaris_kantor
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  mst_inventaris_kantor --where='Cabang=$cab' > $backup_folder/mst_inventaris_kantor_$cabang.sql");

                // ===== Create Backup Transaksi ==========
                //1. trs_approval 
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_approval --where='Cabang=$cab' > $backup_folder/trs_approval_$cabang.sql");
                //2. trs_buku_transaksi
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_transaksi --where='Cabang=$cab' > $backup_folder/trs_buku_transaksi_$cabang.sql");
                //3.trs_call_salesman
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_call_salesman --where='Cabang=$cab' > $backup_folder/trs_call_salesman_$cabang.sql");
                //4.trs_delivery_order_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_detail --where='Cabang=$cab' > $backup_folder/trs_delivery_order_detail_$cabang.sql");
                //trs_delivery_order_header
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_header --where='Cabang=$cab' > $backup_folder/trs_delivery_order_header_$cabang.sql");
                //trs_delivery_order_sales
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales --where='Cabang=$cab' > $backup_folder/trs_delivery_order_sales_$cabang.sql");

                //trs_delivery_order_sales_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales_detail --where='Cabang=$cab' > $backup_folder/trs_delivery_order_sales_detail_$cabang.sql");
                //trs_dih
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_dih --where='Cabang=$cab' > $backup_folder/trs_dih_$cabang.sql");
                //trs_delivery_order_sales
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales --where='Cabang=$cab' > $backup_folder/trs_delivery_order_sales_$cabang.sql");
                //trs_faktur
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur --where='Cabang=$cab' > $backup_folder/trs_faktur_$cabang.sql");

                //trs_faktur_cndn
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_cndn --where='Cabang=$cab' > $backup_folder/trs_faktur_cndn_$cabang.sql");

                //trs_faktur_csv
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_csv --where='Cabang=$cab' > $backup_folder/trs_faktur_csv_$cabang.sql");

                //trs_faktur_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_detail --where='Cabang=$cab'> $backup_folder/trs_faktur_detail_$cabang.sql");
                
                //trs_giro
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_giro --where='Cabang=$cab'> $backup_folder/trs_giro_$cabang.sql");

                //trs_insentif
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_insentif --where='Cabang=$cab'> $backup_folder/trs_insentif_$cabang.sql");

                //trs_invsum
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invsum --where='Cabang=$cab' > $backup_folder/trs_invsum_$cabang.sql");

                //trs_invdet
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invdet --where='Cabang=$cab' > $backup_folder/trs_invdet_$cabang.sql");

                // //trs_invhis
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invhis --where='Cabang=$cab' > $backup_folder/trs_invhis_$cabang.sql");

                // //trs_pelunasan_detail
               $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_detail --where='Cabang=$cab' > $backup_folder/trs_pelunasan_detail_$cabang.sql");

                //  //trs_pelunasan_giro_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_giro_detail --where='Cabang=$cab' > $backup_folder/trs_pelunasan_giro_detail_$cabang.sql");
                // //trs_pembelian_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pembelian_detail --where='Cabang=$cab' > $backup_folder/trs_pembelian_detail_$cabang.sql");

                // //trs_pembelian_header
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pembelian_header --where='Cabang=$cab' > $backup_folder/trs_pembelian_header_$cabang.sql");

                // //trs_po_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_po_detail --where='Cabang=$cab' > $backup_folder/trs_po_detail_$cabang.sql");

                // //trs_po_header
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_po_header --where='Cabang=$cab' > $backup_folder/trs_po_header_$cabang.sql");

                // //trs_sales_order
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order --where='Cabang=$cab' > $backup_folder/trs_sales_order_$cabang.sql");

                // //trs_sales_order_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order_detail --where='Cabang=$cab' > $backup_folder/trs_sales_order_detail_$cabang.sql");

                //  //trs_terima_barang_detail
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_detail --where='Cabang=$cab' > $backup_folder/trs_terima_barang_detail_$cabang.sql");

                //  //trs_terima_barang_header
               $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_header --where='Cabang=$cab' > $backup_folder/trs_terima_barang_header_$cabang.sql");

                // //trs_usulan_beli_detail
               $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_beli_detail --where='Cabang=$cab' > $backup_folder/trs_usulan_beli_detail_$cabang.sql");

                // //trs_usulan_beli_header
                $stream = ssh2_exec($connection,"/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_beli_header --where='Cabang=$cab' > $backup_folder/trs_usulan_beli_header_$cabang.sql");
                $stream = ssh2_exec($connection,"cd /var/www/html/database_download; zip -r database_$cabang.zip $backup_folder");
                stream_set_blocking($stream, true);
                $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                $valid=stream_get_contents($stream_out);
                $connection = null;
                $valid=true;
            }
        // }
        return $valid;
       
    }
    //// ==== Versi Linux =======
    // public function getdownload()
    // {   
    //     $valid = false;
    //     $host = "119.235.19.138";
    //     $port = 22;
    //     $username = "serversst";
    //     $password = "Sapta777*x";
    //     $connection = NULL;
    //     $cabang = $this->cabang;
    //     $remote_file_path = "/var/www/html/database_download/database_$cabang.zip";
    //     $local_file_path  = "/var/www/html/database_download/database_$cabang.zip";
    //     $local_file       = "/var/www/html/database_download/database_$cabang";
    //         $connection = ssh2_connect($host, $port);
    //         if(!$connection){
    //             throw new \Exception("Could not connect to $host on port $port");
    //         }
    //         $auth  = ssh2_auth_password($connection, $username, $password);
    //         if(!$auth){
    //             throw new \Exception("Could not authenticate with username $username and password ");  
    //         }
    //         $sftp = ssh2_sftp($connection);
    //         if(!$sftp){
    //             throw new \Exception("Could not initialize SFTP subsystem.");  
    //         }
    //         if($valid=ssh2_scp_recv($connection, $remote_file_path, $local_file_path)) {
    //             log_message("error","File Download Success");
    //             $zip = new ZipArchive;
    //             $res = $zip->open($local_file_path);
    //             log_message("error",print_r($res,true));
    //             if ($res === TRUE) {
    //               $zip->extractTo($local_file);
    //               $zip->close();
    //             // if($archive = RarArchive::open('D:\php\sst.rar')){
    //             //     $entries = $archive->getEntries();
    //             //     foreach ($entries as $entry) {
    //             //         $entry->extract('D:\php');
    //             //     }                
    //             //     $archive->close();
    //                 log_message("error","extract File Success");
    //                  $dbhost = 'localhost:3306';
    //                  $dbuser = 'sapta';
    //                  $dbpass = 'Sapta254*x';
    //                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
                     
    //                  if(!$conn ){
    //                     log_message("error","Connection failure");
    //                  }
    //                  $buatdb = mysqli_query($conn,"drop database if exists sst");
    //                  $buatdb = mysqli_query($conn,"create database sst");
    //                  if($buatdb){
    //                     log_message("error","Create database success");
    //                     $valid=$this->dbimport();
    //                     // $valid=$this->dbimport_master();
    //                     // $valid=$this->dbimport_transaksi();
    //                     if($valid){
    //                         log_message("error","Import database success");
    //                     }else{
    //                         $valid=FALSE;
    //                     }
    //                 }else{
    //                     log_message("error","Create Failed");
    //                     $valid=FALSE;
    //                 }
    //                 mysqli_close($conn);
    //             } else {
    //               log_message("error","Cannot extract File");
    //               $valid=FALSE;
    //             }
    //         } else {
    //             log_message("error","File Download Failed");
    //             $valid=FALSE;
    //         }
    //         $connection = NULL;

    //     return $valid;
    // }

    // public function dbimport(){
    //     set_time_limit(0);
    //     $valid=false;
    //     $connection = NULL;
    //     $mysql_host = "localhost";
    //     $mysql_user = "sapta";
    //     $mysql_pass = "Sapta254*x";
    //     $username = "serversst";
    //     $password = "Sapta777*x";
    //     $mysql_database = "sst";
    //     $cabang = $this->cabang;
    //     $backup_folder = "/var/www/html/database_download/database_$cabang/var/www/html/database_download/database_$cabang";
    //     // $backup_folder = "D:/php/database_$cabang/var/www/html/database_download/database_Bandung";
    //     $port = 22;
    //     $cab = "\"$cabang\"";

    //     // // Execute a command on the connected server and capture the response
    //     exec("mkdir /var/www/html/database_download/database_$cabang",$result,$result_value);
    //     exec("chmod -R 777 /var/www/html/database_download",$result,$result_value);
    //     exec("chmod -R 777 /var/www/html/database_download/database_$cabang",$result,$result_value);
    //     // === import Struktur Database ======
    //     //     // exec("C:/xampp/mysql/bin/mysqldump.exe -d -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database > $backup_folder/sst_struktur.sql",$result,$result_value);
    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/sst_struktur.sql",$result,$result_value); 

    //     //==== Import Master Data =======
    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/sst_master_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_user_$cabang.sql",$result,$result_value); 
    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_pelanggan_$cabang.sql",$result,$result_value); 
    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_karyawan_$cabang.sql",$result,$result_value); 
    //     // ==== Import  Data Transaksi ============

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_approval_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_buku_transaksi_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_call_salesman_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_header_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_sales_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_sales_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_cndn_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_csv_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_giro_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_insentif_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invsum_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invdet_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invhis_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pelunasan_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pelunasan_giro_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pembelian_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pembelian_header_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_po_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_po_header_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_sales_order_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_sales_order_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_terima_barang_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_terima_barang_header_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_usulan_beli_detail_$cabang.sql",$result,$result_value); 

    //     exec("/usr/bin/mysql -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_usulan_beli_header_$cabang.sql",$result,$result_value); 

    //     $valid=true;
    //     return $valid;
    // }
    ////==== Versi Windows =======
    // public function getdownload_versiwindows()
    public function getdownload()
    {   
        $valid = false;
        $host = "119.235.19.138";
        $port = 22;
        $username = "serversst";
        $password = "Sapta777*x";
        $connection = NULL;
        $cabang = $this->cabang;
        $remote_file_path = "/var/www/html/database_download/database_$cabang.zip";
        $local_file_path  = "D:\php\database_$cabang.zip";
        $local_file       = "D:\php\database_$cabang";
            $connection = ssh2_connect($host, $port);
            if(!$connection){
                throw new \Exception("Could not connect to $host on port $port");
            }
            $auth  = ssh2_auth_password($connection, $username, $password);
            if(!$auth){
                throw new \Exception("Could not authenticate with username $username and password ");  
            }
            $sftp = ssh2_sftp($connection);
            if(!$sftp){
                throw new \Exception("Could not initialize SFTP subsystem.");  
            }
            if($valid=ssh2_scp_recv($connection, $remote_file_path, $local_file_path)) {
                log_message("error","File Download Success");
                $zip = new ZipArchive;
                $res = $zip->open($local_file_path);
                log_message("error",print_r($res,true));
                if ($res === TRUE) {
                  $zip->extractTo($local_file);
                  $zip->close();
                // if($archive = RarArchive::open('D:\php\sst.rar')){
                //     $entries = $archive->getEntries();
                //     foreach ($entries as $entry) {
                //         $entry->extract('D:\php');
                //     }                
                //     $archive->close();
                    log_message("error","extract File Success");
                     $dbhost = 'localhost:3306';
                     $dbuser = 'sapta';
                     $dbpass = 'Sapta254*x';
                     $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
                     
                     if(!$conn ){
                        log_message("error","Connection failure");
                     }
                     $buatdb = mysqli_query($conn,"drop database if exists sst");
                     $buatdb = mysqli_query($conn,"create database sst");
                     if($buatdb){
                        log_message("error","Create database success");
                        $valid=$this->dbimport();
                        // $valid=$this->dbimport_master();
                        // $valid=$this->dbimport_transaksi();
                        if($valid){
                            log_message("error","Import database success");
                        }
                    }else{
                        log_message("error","Create Failed");
                    }
                    mysqli_close($conn);
                } else {
                  log_message("error","Cannot extract File");
                }
            } else {
                log_message("error","File Download Failed");
            }
            $connection = NULL;

        return $valid;
    }

    // public function dbimport_versiwindows(){
    public function dbimport(){
        set_time_limit(0);
        $valid=false;
        $connection = NULL;
        $mysql_host = "localhost";
        $mysql_user = "sapta";
        $mysql_pass = "Sapta254*x";
        $username = "serversst";
        $password = "Sapta777*x";
        $mysql_database = "sst";
        $cabang = $this->cabang;
        // $backup_folder = "/var/www/html/database_download/database_$cabang";
        $backup_folder = "D:/php/database_$cabang/var/www/html/database_download/database_Bandung";
        // === import Struktur Database ======
        //     // exec("C:/xampp/mysql/bin/mysqldump.exe -d -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database > $backup_folder/sst_struktur.sql",$result,$result_value);
        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/sst_struktur.sql",$result,$result_value); 

        //==== Import Master Data =======
        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/sst_master_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_user_$cabang.sql",$result,$result_value); 
        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_pelanggan_$cabang.sql",$result,$result_value); 
        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_karyawan_$cabang.sql",$result,$result_value); 
        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/mst_inventaris_kantor_$cabang.sql",$result,$result_value); 

        // ==== Import  Data Transaksi ============

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_approval_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_buku_transaksi_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_call_salesman_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_header_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_sales_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_delivery_order_sales_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_cndn_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_csv_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_faktur_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_giro_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_insentif_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invsum_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invdet_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_invhis_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pelunasan_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pelunasan_giro_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pembelian_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_pembelian_header_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_po_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_po_header_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_sales_order_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_sales_order_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_terima_barang_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_terima_barang_header_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_usulan_beli_detail_$cabang.sql",$result,$result_value); 

        exec("C:/xampp/mysql/bin/mysql.exe -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database < $backup_folder/trs_usulan_beli_header_$cabang.sql",$result,$result_value); 
        $valid=true;
        return $valid;
    }

    public function table_num(){
        $this->db3 = $this->load->database('real', TRUE);  
        $query = $this->db3->query("select count(Produk) as 'produk' from mst_produk")->row();
        $mst_produk = $query->produk;

        $query = $this->db3->query("select count(Pelanggan) as 'Pelanggan' from mst_pelanggan")->row();
        $mst_pelanggan = $query->Pelanggan;

        $query = $this->db3->query("select count(Nama) as 'Nama' from mst_karyawan")->row();
        $mst_karyawan = $query->Nama;

        $query = $this->db3->query("select max(id) as 'harga' from mst_harga")->row();
        $mst_harga = $query->harga;

        $query = $this->db3->query("select max(id) as 'prinsipal' from mst_prinsipal")->row();
        $mst_prinsipal = $query->prinsipal;

        $query = $this->db3->query("select max(id) as 'cabang' from mst_cabang")->row();
        $mst_cabang = $query->cabang;

        $query = $this->db3->query("select max(id) as 'ams3' from mst_data_ams3")->row();
        $mst_data_ams3 = $query->ams3;

        $query = $this->db3->query("select max(id) as 'disc_jual' from mst_diskon_jual")->row();
        $mst_diskon_jual = $query->disc_jual;

        $query = $this->db3->query("select count(bank) as 'bank' from mst_gl_bank")->row();
        $mst_gl_bank = $query->bank;

        $query = $this->db3->query("select count(`Kode Perkiraan`) as 'Perkiraan' from mst_gl_coa")->row();
        $mst_gl_coa = $query->Perkiraan;

        $query = $this->db3->query("select max(id) as 'NamaTransaksi' from mst_gl_transaksi_kat")->row();
        $mst_gl_transaksi_kat = $query->NamaTransaksi;

        $query = $this->db3->query("select count(NoFaktur) as 'NoFaktur' from trs_faktur")->row();
        $trs_faktur = $query->NoFaktur;

        $query = $this->db3->query("select count(NoDokumen) as 'NoDokumen' from trs_faktur_cndn")->row();
        $cndn = $query->NoDokumen;

        $query = $this->db3->query("select count(NoDO) as 'NoDO' from trs_delivery_order_sales")->row();
        $trs_do = $query->NoDO;

        $query = $this->db3->query("select count(*) as 'invsum' from trs_invsum")->row();
        $trs_invsum = $query->invsum;

        $query = $this->db3->query("select count(*) as 'invdet' from trs_invdet")->row();
        $trs_invdet = $query->invdet;

        $query = $this->db3->query("select count(*) as 'invhis' from trs_invhis")->row();
        $trs_invhis = $query->invhis;

        $query = $this->db3->query("select count(*) as 'dih' from trs_dih")->row();
        $trs_dih = $query->dih;

        $query = $this->db3->query("select count(*) as 'lunas' from trs_pelunasan_detail")->row();
        $trs_pelunasan_detail = $query->lunas;

        $query = $this->db3->query("select count(*) as 'giro' from trs_pelunasan_giro_detail")->row();
        $trs_pelunasan_giro_detail = $query->giro;

        $query = $this->db3->query("select count(*) as 'po' from trs_po_header")->row();
        $trs_po_header = $query->po;

        $query = $this->db3->query("select count(*) as 'terima' from trs_terima_barang_header")->row();
        $trs_terima_barang_header = $query->terima;


        $data = array(
            "mst_produk" => $mst_produk,
            "mst_pelanggan" => $mst_pelanggan,
            "mst_karyawan" => $mst_karyawan,
            "mst_harga" => $mst_harga,
            "mst_cabang" => $mst_cabang,
            "mst_prinsipal" => $mst_prinsipal,
            "mst_data_ams3" => $mst_data_ams3,
            "mst_diskon_jual" => $mst_diskon_jual,
            "mst_gl_bank" => $mst_gl_bank,
            "mst_gl_coa" => $mst_gl_coa,
            "mst_gl_transaksi_kat" => $mst_gl_transaksi_kat,
            "trs_faktur" => $trs_faktur,
            "cndn" => $cndn,
            "trs_do" => $trs_do,
            "trs_invsum" => $trs_invsum,
            "trs_invdet" => $trs_invdet,
            "trs_invhis" => $trs_invhis,
            "trs_dih" => $trs_dih,
            "trs_pelunasan_detail" => $trs_pelunasan_detail,
            "trs_pelunasan_giro_detail" => $trs_pelunasan_giro_detail,
            "trs_po_header" => $trs_po_header,
            "trs_terima_barang_header" => $trs_terima_barang_header,
        );

        return $data;

    }
}
    // public function dbimport(){
    //     set_time_limit(0);
    //     // database file path
    //     $filename = 'd:\php\sst_struktur.sql';
    //     // MySQL host
    //     $mysql_host = 'localhost:3306';
    //     // MySQL username
    //     $mysql_username = 'sapta';
    //     // MySQL password
    //     $mysql_password = 'Sapta254*x';
    //     // Database name
    //     $mysql_database = 'sst';
    //     // Connect to MySQL server
    //     $connection = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database);
    //     if (mysqli_connect_errno())
    //         echo "Failed to connect to MySQL: " . mysqli_connect_error();
    //     // Temporary variable, used to store current query
    //     $templine = '';
    //     // Read in entire file
    //     $fp = fopen($filename, 'r');
    //     // Loop through each line
    //     while (($line = fgets($fp)) !== false) {
    //         // Skip it if it's a comment
    //         if (substr($line, 0, 2) == '--' || $line == '')
    //             continue;
    //         // Add this line to the current segment
    //         $templine .= $line;
    //         // If it has a semicolon at the end, it's the end of the query
    //         if (substr(trim($line), -1, 1) == ';') {
    //             // Perform the query
    //             if(!mysqli_query($connection, $templine)){
    //                 print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($connection) . '<br /><br />');
    //             }
    //             // Reset temp variable to empty
    //             $templine = '';
    //         }
    //     }
    //     $valid = mysqli_close($connection);
    //     $valid = fclose($fp);
    //     log_message("error","Database struktur imported successfully");
    //     return $valid;
        
    // }

    // public function dbimport_master(){
    //     set_time_limit(0);
    //     // database file path
    //     $filename = 'd:\php\sst_master.sql';
    //     // MySQL host
    //     $mysql_host = 'localhost:3306';
    //     // MySQL username
    //     $mysql_username = 'sapta';
    //     // MySQL password
    //     $mysql_password = 'Sapta254*x';
    //     // Database name
    //     $mysql_database = 'sst';
    //     // Connect to MySQL server
    //     $connection = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database);
    //     if (mysqli_connect_errno())
    //         echo "Failed to connect to MySQL: " . mysqli_connect_error();
    //     // Temporary variable, used to store current query
    //     $templine = '';
    //     // Read in entire file
    //     $fp = fopen($filename, 'r');
    //     // Loop through each line
    //     while (($line = fgets($fp)) !== false) {
    //         // Skip it if it's a comment
    //         if (substr($line, 0, 2) == '--' || $line == '')
    //             continue;
    //         // Add this line to the current segment
    //         $templine .= $line;
    //         // If it has a semicolon at the end, it's the end of the query
    //         if (substr(trim($line), -1, 1) == ';') {
    //             // Perform the query
    //             if(!mysqli_query($connection, $templine)){
    //                 print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($connection) . '<br /><br />');
    //             }
    //             // Reset temp variable to empty
    //             $templine = '';
    //         }
    //     }
    //     $valid = mysqli_close($connection);
    //     $valid = fclose($fp);
    //     log_message("error","Database master imported successfully");
    //     return $valid;
    // }

    // public function dbimport_transaksi(){
    //     set_time_limit(0);
    //     // database file path
    //     $filename = 'd:\php\sst_transaksi.sql';
    //     // MySQL host
    //     $mysql_host = 'localhost:3306';
    //     // MySQL username
    //     $mysql_username = 'sapta';
    //     // MySQL password
    //     $mysql_password = 'Sapta254*x';
    //     // Database name
    //     $mysql_database = 'sst';
    //     // Connect to MySQL server
    //     $connection = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database);
    //     if (mysqli_connect_errno())
    //         echo "Failed to connect to MySQL: " . mysqli_connect_error();
    //     // Temporary variable, used to store current query
    //     $templine = '';
    //     // Read in entire file
    //     $fp = fopen($filename, 'r');
    //     // Loop through each line
    //     while (($line = fgets($fp)) !== false) {
    //         // Skip it if it's a comment
    //         if (substr($line, 0, 2) == '--' || $line == '')
    //             continue;
    //         // Add this line to the current segment
    //         $templine .= $line;
    //         // If it has a semicolon at the end, it's the end of the query
    //         if (substr(trim($line), -1, 1) == ';') {
    //             // Perform the query
    //             if(!mysqli_query($connection, $templine)){
    //                 print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($connection) . '<br /><br />');
    //             }
    //             // Reset temp variable to empty
    //             $templine = '';
    //         }
    //     }
    //     $valid = mysqli_close($connection);
    //     $valid = fclose($fp);
    //     log_message("error","Database transaksi imported successfully");
    //     return $valid;
    // }

     // stream_set_blocking($valid, true);
        // if($result_value == 0){
        //     #Now zip that file
        //     $zip = new ZipArchive();
        //     $file_struktur = "d:\database\sst_struktur.zip";
        //     $file_master = "d:\database\sst_master_$cabang.zip";
        //     if ($zip->open($file_struktur, ZIPARCHIVE::CREATE) !== TRUE) {
        //        log_message("error","cannot open <$filename>n");
        //     }
        //     $zip->addFile("d:\database\sst_struktur.sql" , "sst_struktur.sql");
        //      if ($zip->open($file_master, ZIPARCHIVE::CREATE) !== TRUE) {
        //        log_message("error","cannot open <$filename>n");
        //     }
        //     $zip->addFile("d:\database\sst_master_$cabang.sql" , "sst_master_$cabang.sql");
        //     $valid = $zip->close();
        // }

