<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// ini_set('memory_limit', '910M');
class Model_closing extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->model('Model_main');
            $this->load->model('pembelian/Model_inventori');
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

    // public function closingClosingBulan()
    // {   
    //     $month = date('m');
    //     $year = date('Y');
    //     $date = date('Y-m-d');
    //     $nextmonth = date('Y-m-d', strtotime('+1 month'));
    //     $getmonth  = date('m', strtotime($nextmonth));
    //     $last_day = date("Y-m-t", strtotime($date));
    //     $valid = false;
    //     if($last_day != $date){
    //         log_message("error","bukan akhir bulan");
    //     }else{
    //         $valid = $this->db->query("truncate mst_closing");
    //         $valid = $this->db->query("insert into mst_closing values('".$this->cabang."',DATE_ADD(curdate(), INTERVAL 1 DAY),DATE_ADD(NOW(), INTERVAL 1 DAY))");
    //         if($month != '12'){
    //             $valid = $this->db->query("update trs_invsum set SAwal".$getmonth." = UnitStok
    //                                    where Tahun = '".date('Y')."'
    //                                 ");
    //         }else{
    //             $nextyear = date('Y-m-d', strtotime('+1 year'));
    //             $year  = date('Y', strtotime($nextyear));
    //             $query = $db->query("select * from trs_invsum where Tahun= '".date('Y')."'")->row();
    //             foreach ($query as $stok) {
    //                 $this->db->set("Tahun", $year);
    //                 $this->db->set("Cabang", $stok->Cabang);
    //                 $this->db->set("KodePrinsipal", $stok->KodePrinsipal);
    //                 $this->db->set("NamaPrinsipal", $stok->NamaPrinsipal);
    //                 $this->db->set("Pabrik", $stok->Pabrik);
    //                 $this->db->set("KodeProduk", $stok->KodeProduk);
    //                 $this->db->set("NamaProduk", $stok->NamaProduk);
    //                 $this->db->set("UnitStok", $UnitStok);
    //                 $this->db->set("ValueStok", $ValueStok);
    //                 $this->db->set("Gudang", $Gudang);
    //                 $this->db->set("Indeks", $Indeks);
    //                 $this->db->set("UnitCOGS", $UnitCOGS);
    //                 $this->db->set("HNA", $HNA);
    //                 $this->db->set("SAwal01", $SAwal12);
    //                 $this->db->set("VAwal01", $VAwal12);
    //                 $this->db->set("AddedUser", $this->user);
    //                 $this->db->set("AddedTime", date("Y-m-d H:i:s"));
    //                 $valid = $this->db->insert('trs_invsum');
    //             }               

    //         }
    //         $cek = false;
    //         $cek = $this->db->query("insert into trs_settlement_stok_history select * from trs_settlement_stok_day");
    //         $cek = $this->db->query("insert into trs_settlement_kasbank_history select * from trs_settlement_kasbank_day");
    //         if($cek){
    //             $valid = $this->db->query('truncate trs_settlement_stok_day');
    //             $valid = $this->db->query('truncate trs_settlement_kasbank_day');
    //         }
            
    //     }

    //     return $valid;
    // }


    public function setClosing()
    {   
        $valid = false;
        $valid = $this->db->query("truncate mst_closing");
        $valid = $this->db->query("insert into mst_closing values('".$this->cabang."',DATE_ADD(curdate(), INTERVAL 1 DAY),DATE_ADD(NOW(), INTERVAL 1 DAY))");
        $this->getbackupdaily();
        $this->getupload();
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

    public function getDOopen(){
        // if(date('t') != '31'){
        //     $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
        //     $satubulan = date('Y-m-t', strtotime('-1 months'));
        // }else{
        //     $satu = date('Y-m-d',strtotime("-31 days"));
        //     $satubulan_awal = date('Y-m-01', strtotime($satu));
        //     $satubulan = date('Y-m-t', strtotime($satu));
        // }
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Open' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
         $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Open' and tipedokumen='DO' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        return $query;
    }

    public function getDOKirim(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TipeDokumen='DO' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        return $query;
    }

    public function getDOTerima(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        // $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Kirim' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();
        $query = $this->db->query("select count(ifnull(NoDO,'')) as NoDO from trs_delivery_order_sales where ifnull(Status,'') = 'Terima' and tipedokumen='DO' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();

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

    public function getbackupdaily()
    {   
       set_time_limit(0);
        $valid=false;
        $connection = NULL;
        // $mysql_host = "bandungsst.ddns.net";
        $mysql_host = "localhost";
        $mysql_user = "sapta";
        $mysql_pass = "Sapta254*x";
        $username = "serversst";
        $password = "Sapta777*x";
        $mysql_database = "sst";
        $cabang = $this->cabang;
        $currdate = date("Y-m-d"); 
        $cab = "\"$cabang\"";
        // $cab = "'".$cabang."'";
        $transdate = "\"$currdate\"";
        // $transdate = "'".$currdate."'";
        $backup_folder = "/var/www/html/database_download/database_$cabang";
        $real_path = "/var/www/html/database_download/backup_$cabang.zip";

        // === import Struktur Database ======
        //     // exec("C:/xampp/mysql/bin/mysqldump.exe -d -h $mysql_host -u $mysql_user -p$mysql_pass  $mysql_database > $backup_folder/// ===== Create Backup Transaksi ==========
        // ======================= buat linux ========================================================
              // 1. trs_approval 
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_approval --where='Cabang=$cab' --where=date(TimeUsulan)='$transdate' > $backup_folder/trs_approval_$cabang.sql");
                //2. trs_buku_giro
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_giro --where='Cabang=$cab' --where=Tanggal='$transdate' > $backup_folder/trs_buku_giro_$cabang.sql");
                //2. trs_buku_kasbon
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_kasbon --where='Cabang=$cab' --where='Tanggal=$transdate' > $backup_folder/trs_buku_kasbon_$cabang.sql");
                //2. trs_buku_titipan
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_titipan --where='Cabang=$cab' --where='Tanggal=$transdate' > $backup_folder/trs_buku_titipan_$cabang.sql");
                //2. trs_buku_transaksi
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_transaksi --where='Cabang=$cab' --where='Tanggal=$transdate' > $backup_folder/trs_buku_transaksi_$cabang.sql");
               
                //trs_delivery_order_sales
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales --where='Cabang=$cab' --where='TglDO=$transdate' > $backup_folder/trs_delivery_order_sales_$cabang.sql");

                //trs_delivery_order_sales_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales_detail --where='Cabang=$cab' --where='TglDO=$transdate' > $backup_folder/trs_delivery_order_sales_detail_$cabang.sql");
                //trs_dih
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_dih --where='Cabang=$cab' --where='TglDIH=$transdate' > $backup_folder/trs_dih_$cabang.sql");
                //trs_faktur
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur --where='Cabang=$cab' --where='TglFaktur=$transdate' > $backup_folder/trs_faktur_$cabang.sql");

                //trs_faktur_cndn
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_cndn --where='Cabang=$cab' --where='TanggalCNDN=$transdate' > $backup_folder/trs_faktur_cndn_$cabang.sql");

                //trs_faktur_csv
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_csv --where='Cabang=$cab' --where='TglFaktur=$transdate' > $backup_folder/trs_faktur_csv_$cabang.sql");

                //trs_faktur_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_detail --where='Cabang=$cab' --where='TglFaktur=$transdate' > $backup_folder/trs_faktur_detail_$cabang.sql");
                
                //trs_giro
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_giro --where='Cabang=$cab' > $backup_folder/trs_giro_$cabang.sql");

                // //trs_insentif
                // $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_insentif --where=Cabang=$cab> $backup_folder/trs_insentif_$cabang.sql");

                // trs_invsum
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database trs_invsum --where='Cabang=$cab' > $backup_folder/trs_invsum_$cabang.sql");

                //trs_invdet
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invdet --where='Cabang=$cab' > $backup_folder/trs_invdet_$cabang.sql");

                // //trs_invhis
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invhis --where='Cabang=$cab' > $backup_folder/trs_invhis_$cabang.sql");

                // //trs_kiriman
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_kiriman --where='Cabang=$cab' --where='TglKirim=$transdate' > $backup_folder/trs_kiriman_$cabang.sql");

                // //trs_pelunasan_detail
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_detail --where='Cabang=$cab' --where='TglPelunasan=$transdate' > $backup_folder/trs_pelunasan_detail_$cabang.sql");

                //  //trs_pelunasan_giro_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_giro_detail --where='Cabang=$cab' --where='TglPelunasan=$transdate' > $backup_folder/trs_pelunasan_giro_detail_$cabang.sql");

                 // //trs_pelunasan_detail_ssp
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_detail_ssp --where='Cabang=$cab' --where='TglPelunasan=$transdate' > $backup_folder/trs_pelunasan_detail_ssp_$cabang.sql");
                
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_kirim_detail --where='Cabang=$cab' --where='Tgl_kirim=$transdate' > $backup_folder/trs_relokasi_kirim_detail_$cabang.sql");

                // //trs_relokasi_kirim_header
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_kirim_header --where='Cabang=$cab' --where='Tgl_kirim=$transdate' > $backup_folder/trs_relokasi_kirim_header_$cabang.sql");

                 // //trs_relokasi_terima_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_terima_detail --where='Cabang=$cab' --where='Tgl_terima=$transdate' > $backup_folder/trs_relokasi_terima_detail_$cabang.sql");

                 // //trs_relokasi_terima_header
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_terima_header --where='Cabang=$cab' --where='Tgl_terima=$transdate' > $backup_folder/trs_relokasi_terima_header_$cabang.sql");

                // //trs_sales_order
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order --where='Cabang=$cab' --where='TglSO=$transdate' > $backup_folder/trs_sales_order_$cabang.sql");

                // //trs_sales_order_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order_detail --where='Cabang=$cab' --where='TglSO=$transdate' > $backup_folder/trs_sales_order_detail_$cabang.sql");

                //  //trs_terima_barang_detail
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_detail --where='Cabang=$cab' --where='TglDokumen=$transdate' > $backup_folder/trs_terima_barang_detail_$cabang.sql");

                //  //trs_terima_barang_header
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_header --where='Cabang=$cab' --where='TglDokumen=$transdate' > $backup_folder/trs_terima_barang_header_$cabang.sql");

                // //trs_usulan_retur_beli_detail
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_retur_beli_detail --where='Cabang=$cab' --where='Tanggal=$transdate' > $backup_folder/trs_usulan_retur_beli_detail_$cabang.sql");

                // //trs_usulan_retur_beli_header
                $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_retur_beli_header --where='Cabang=$cab' --where='Tanggal=$transdate' > $backup_folder/trs_usulan_retur_beli_header_$cabang.sql");

                // //trs_sobh
               $valid = exec("/usr/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sobh --where='Cabang=$cab' --where='TglSOBH=$transdate' > $backup_folder/trs_sobh_$cabang.sql");
            // ===================== End backup versi linux =================================================================

             // ======================= buat Windows ========================================================
               //  // 1. trs_approval 
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_approval --where=Cabang=$cab --where=date(TimeUsulan)=$transdate > $backup_folder/trs_approval_$cabang.sql");
               //  //2. trs_buku_giro
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_giro --where=Cabang=$cab --where=Tanggal=$transdate > $backup_folder/trs_buku_giro_$cabang.sql");
               //  //2. trs_buku_kasbon
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_kasbon --where=Cabang=$cab --where=$transdate > $backup_folder/trs_buku_kasbon_$cabang.sql");
               //  //2. trs_buku_titipan
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_titipan --where=Cabang=$cab --where=$transdate > $backup_folder/trs_buku_titipan_$cabang.sql");
               //  //2. trs_buku_transaksi
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_buku_transaksi --where=Cabang=$cab --where=Tanggal=$transdate > $backup_folder/trs_buku_transaksi_$cabang.sql");
               
               //  //trs_delivery_order_sales
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales --where=Cabang=$cab --where=TglDO=$transdate > $backup_folder/trs_delivery_order_sales_$cabang.sql");

               //  //trs_delivery_order_sales_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_delivery_order_sales_detail --where=Cabang=$cab --where=TglDO=$transdate > $backup_folder/trs_delivery_order_sales_detail_$cabang.sql");
               //  //trs_dih
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_dih --where=Cabang=$cab --where=TglDIH=$transdate > $backup_folder/trs_dih_$cabang.sql");
               //  //trs_faktur
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur --where=Cabang=$cab --where=TglFaktur=$transdate > $backup_folder/trs_faktur_$cabang.sql");

               //  //trs_faktur_cndn
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_cndn --where=Cabang=$cab --where=TanggalCNDN=$transdate > $backup_folder/trs_faktur_cndn_$cabang.sql");

               //  //trs_faktur_csv
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_csv --where=Cabang=$cab --where=TglFaktur=$transdate > $backup_folder/trs_faktur_csv_$cabang.sql");

               //  //trs_faktur_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_faktur_detail --where=Cabang=$cab --where=TglFaktur=$transdate> $backup_folder/trs_faktur_detail_$cabang.sql");
                
               //  //trs_giro
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_giro --where=Cabang=$cab> $backup_folder/trs_giro_$cabang.sql");

               //  // //trs_insentif
               //  // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_insentif --where=Cabang=$cab> $backup_folder/trs_insentif_$cabang.sql");

               //  // trs_invsum
               //  exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database trs_invsum --where=Cabang=$cab > $backup_folder/trs_invsum_$cabang.sql");

               //  //trs_invdet
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invdet --where=Cabang=$cab > $backup_folder/trs_invdet_$cabang.sql");

               //  // //trs_invhis
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_invhis --where=Cabang=$cab > $backup_folder/trs_invhis_$cabang.sql");

               //  // //trs_kiriman
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_kiriman --where=Cabang=$cab --where=TglKirim=$transdate > $backup_folder/trs_pelunasan_detail_$cabang.sql");

               //  // //trs_pelunasan_detail
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_detail --where=Cabang=$cab --where=TglPelunasan=$transdate > $backup_folder/trs_pelunasan_detail_$cabang.sql");

               //  //  //trs_pelunasan_giro_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_giro_detail --where=Cabang=$cab --where=TglPelunasan=$transdate > $backup_folder/trs_pelunasan_giro_detail_$cabang.sql");

               //   // //trs_pelunasan_detail_ssp
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_pelunasan_detail_ssp --where=Cabang=$cab --where=TglPelunasan=$transdate > $backup_folder/trs_pelunasan_detail_ssp_$cabang.sql");
                
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_kirim_detail --where=Cabang=$cab --where=Tgl_kirim=$transdate > $backup_folder/trs_relokasi_kirim_detail_$cabang.sql");

               //  // //trs_relokasi_kirim_header
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_kirim_header --where=Cabang=$cab --where=Tgl_kirim=$transdate > $backup_folder/trs_relokasi_kirim_header_$cabang.sql");

               //   // //trs_relokasi_terima_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_terima_detail --where=Cabang=$cab --where=Tgl_terima=$transdate > $backup_folder/trs_relokasi_terima_detail_$cabang.sql");

               //   // //trs_relokasi_terima_header
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_relokasi_terima_header --where=Cabang=$cab --where=Tgl_terima=$transdate > $backup_folder/trs_relokasi_terima_header_$cabang.sql");

               //  // //trs_sales_order
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order --where=Cabang=$cab --where=TglSO=$transdate > $backup_folder/trs_sales_order_$cabang.sql");

               //  // //trs_sales_order_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sales_order_detail --where=Cabang=$cab --where=TglSO=$transdate > $backup_folder/trs_sales_order_detail_$cabang.sql");

               //  //  //trs_terima_barang_detail
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_detail --where=Cabang=$cab --where=TglDokumen=$transdate > $backup_folder/trs_terima_barang_detail_$cabang.sql");

               //  //  //trs_terima_barang_header
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_terima_barang_header --where=Cabang=$cab --where=TglDokumen=$transdate > $backup_folder/trs_terima_barang_header_$cabang.sql");

               //  // //trs_usulan_retur_beli_detail
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_retur_beli_detail --where=Cabang=$cab --where=Tanggal=$transdate > $backup_folder/trs_usulan_retur_beli_detail_$cabang.sql");

               //  // //trs_usulan_retur_beli_header
               //  $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_usulan_retur_beli_header --where=Cabang=$cab --where=Tanggal=$transdate > $backup_folder/trs_usulan_retur_beli_header_$cabang.sql");

               //  // //trs_sobh
               // $valid = exec("C:/xampp/mysql/bin/mysqldump -h $mysql_host -u $mysql_user -p$mysql_pass --no-create-info $mysql_database  trs_sobh --where=Cabang=$cab --where=TglSOBH=$transdate > $backup_folder/trs_sobh_$cabang.sql");

               // Get real path for our folder
                $rootPath = realpath($backup_folder);
                // Initialize archive object
                $zip = new ZipArchive();
                $zip->open($real_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                // Create recursive directory iterator
                /** @var SplFileInfo[] $files */
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($rootPath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file)
                {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir())
                    {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($rootPath) + 1);

                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                // Zip archive will be created only after closing object
                $zip->close();

                // $stream = exec("cd /var/www/html/database_download; zip -r database_$cabang.zip $backup_folder");
                // stream_set_blocking($stream, true);
                // $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                // $valid=$stream;
                $connection = null;
                // $valid=true;
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
    //     // $backup_folder = "D:/php/database_$cabang/var/www/html/database_download/database_bandung";
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
     public function getupload()
    {   
        $valid = false;
        $host = "119.235.19.138";
        $port = 22;
        $username = "serversst";
        $password = "Sapta777*x";
        $connection = NULL;
        $cabang = $this->cabang;
        $remote_file_path = "/var/www/html/database_download/backup_bandung/backup_$cabang.zip";
        $local_file_path  = "/var/www/html/database_download/backup_$cabang.zip";
        $local_file       = "/var/www/html/database_download/database_$cabang";
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
            if($valid=ssh2_scp_send($connection, $local_file_path,$remote_file_path )) {
                log_message("error","File Upload Success");
            } else {
                log_message("error","File Upload Failed");
            }
            $connection = NULL;

        return $valid;
    }

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
        $backup_folder = "D:/php/database_$cabang/var/www/html/database_download/database_bandung";
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

    public function getuploadcabang($tgl1=null,$tgl2=null)
    {  

        $cabang = $this->cabang;
        $path = "/var/www/html/database_download/database_$cabang";
        if(!is_dir($path))
        {
          mkdir($path,0755,TRUE);
        } 
        $delimiter = ",";
        $newline = "\r\n";
        $enclosure = '"';
        $this->load->dbutil();
        //=== DO ===============
        $querydoheader = $this->db->query("SELECT * FROM trs_delivery_order_sales 
                                            WHERE (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."' 
                                                    ELSE tgldo BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        $querydodetail = $this->db->query("SELECT * FROM trs_delivery_order_sales_detail 
                                            WHERE (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."' 
                                                    ELSE tgldo BETWEEN '".$tgl1."' and '".$tgl2."' END )");

        //=== Kiriman ==========
        $querykiriman = $this->db->query("SELECT * 
                                           FROM trs_kiriman 
                                           WHERE (CASE WHEN IFNULL(updated_by,'') != '' THEN DATE(updated_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                                ELSE TglKirim BETWEEN '".$tgl1."' and '".$tgl2."' END )");

        //====== faktur ==============
        $queryfakturheader = $this->db->query("SELECT * 
                                                FROM trs_faktur 
                                                WHERE (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                                    ELSE TglFaktur BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        $queryfakturdetail = $this->db->query("SELECT * FROM trs_faktur_detail where TglFaktur between '".$tgl1."' and '".$tgl2."'");

        $queryfakturcndn = $this->db->query("SELECT * 
                                                FROM trs_faktur_cndn 
                                                WHERE (CASE WHEN IFNULL(updated_by,'') != '' THEN DATE(updated_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                                    ELSE date(created_at) BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        //======= Pelunasan =========
        $querydih = $this->db->query("SELECT * 
                                      FROM trs_dih 
                                      where (CASE WHEN IFNULL(updated_by,'') != '' THEN DATE(updated_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                            ELSE TglDIH BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        $querylunascash = $this->db->query("SELECT * FROM trs_pelunasan_detail where TglPelunasan between '".$tgl1."' and '".$tgl2."'");
        $querylunasgiro = $this->db->query("SELECT * 
                                            FROM trs_pelunasan_giro_detail 
                                            where (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                                ELSE TglPelunasan BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        $querygiro = $this->db->query("SELECT * 
                                       FROM trs_giro 
                                       where (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                            ELSE DATE(create_at) BETWEEN '".$tgl1."' and '".$tgl2."' END )");

        //========= Kas & bank ===============

        $querybukugiro = $this->db->query("SELECT * 
                                            FROM trs_buku_giro 
                                            where (CASE WHEN IFNULL(modified_by,'') != '' THEN DATE(modified_at) BETWEEN '".$tgl1."' and '".$tgl2."'
                                            ELSE Tanggal BETWEEN '".$tgl1."' and '".$tgl2."' END )");
        $querybukukasbon = $this->db->query("SELECT * FROM trs_buku_kasbon where Tanggal between '".$tgl1."' and '".$tgl2."'");
        $querybukutitipan = $this->db->query("SELECT * FROM trs_buku_titipan where Tanggal between '".$tgl1."' and '".$tgl2."'");
        $querybukutransaksi = $this->db->query("SELECT * FROM trs_buku_transaksi where date(Tanggal) between '".$tgl1."' and '".$tgl2."'");

        //================ Stok ======================
        $queryinvsum = $this->db->query("select * from trs_invsum where tahun = '".date('Y')."' ");
        $queryinvdet = $this->db->query("select * from trs_invdet where tahun = '".date('Y')."' ");
       
        $doheader     = $this->dbutil->csv_from_result($querydoheader, $delimiter, $newline, $enclosure);
        $dodetail     = $this->dbutil->csv_from_result($querydodetail, $delimiter, $newline, $enclosure);
        $kiriman      = $this->dbutil->csv_from_result($querykiriman, $delimiter, $newline, $enclosure);
        $fakturheader = $this->dbutil->csv_from_result($queryfakturheader, $delimiter, $newline, $enclosure);
        $fakturdetail = $this->dbutil->csv_from_result($queryfakturdetail, $delimiter, $newline, $enclosure);
        $fakturcndn = $this->dbutil->csv_from_result($queryfakturcndn, $delimiter, $newline, $enclosure);
        $dih = $this->dbutil->csv_from_result($querydih, $delimiter, $newline, $enclosure);
        $giro = $this->dbutil->csv_from_result($querygiro, $delimiter, $newline, $enclosure);
        $lunascash = $this->dbutil->csv_from_result($querylunascash, $delimiter, $newline, $enclosure);
        $lunasgiro = $this->dbutil->csv_from_result($querylunasgiro, $delimiter, $newline, $enclosure);
        $bukugiro = $this->dbutil->csv_from_result($querybukugiro, $delimiter, $newline, $enclosure);
        $bukukasbon = $this->dbutil->csv_from_result($querybukukasbon, $delimiter, $newline, $enclosure);
        $bukutitipan = $this->dbutil->csv_from_result($querybukutitipan, $delimiter, $newline, $enclosure);
        $bukutransaksi = $this->dbutil->csv_from_result($querybukutransaksi, $delimiter, $newline, $enclosure);
        $invsum = $this->dbutil->csv_from_result($queryinvsum, $delimiter, $newline, $enclosure);
        $invdet = $this->dbutil->csv_from_result($queryinvdet, $delimiter, $newline, $enclosure);

        $this->load->helper('file');
        write_file($path.'/doheader.txt', $doheader);
        write_file($path.'/dodetail.txt', $dodetail);
        write_file($path.'/kiriman.txt', $kiriman);
        write_file($path.'/fakturheader.txt', $fakturheader);
        write_file($path.'/fakturdetail.txt', $fakturdetail);
        write_file($path.'/fakturcndn.txt', $fakturcndn);
        write_file($path.'/dih.txt', $dih);
        write_file($path.'/giro.txt', $giro);
        write_file($path.'/lunascash.txt', $lunascash);
        write_file($path.'/lunasgiro.txt', $lunasgiro);
        write_file($path.'/bukugiro.txt', $bukugiro);
        write_file($path.'/bukukasbon.txt', $bukukasbon);
        write_file($path.'/bukutitipan.txt', $bukutitipan);
        write_file($path.'/bukutransaksi.txt', $bukutransaksi);
        write_file($path.'/invsum.txt', $invsum);
        write_file($path.'/invdet.txt', $invdet);

        // $real_path = "/var/www/html/database_download/backup_$cabang.zip";
        $real_path = "/var/www/html/database_download/backup_$cabang.zip";
        //Get real path for our folder
        $rootPath = realpath($path);
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($real_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file)
        {
                    // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
                // Zip archive will be created only after closing object
        $zip->close();

        //=========== Upload =======================
        $valid = false;
        $host = "119.235.19.138";
        $port = 22;
        $username = "serversst";
        $password = "Sapta777*x";
        $connection = NULL;
        $cabang = $this->cabang;
        $remote_file_path = "/var/www/html/database_download/backup_$cabang/backup_$cabang.zip";
        // $local_file_path  = "/var/www/html/database_download/backup_$cabang.zip";
        $local_file       = "/var/www/html/database_download/database_$cabang";
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
            if($valid=ssh2_scp_send($connection, $real_path,$remote_file_path )) {
                log_message("error","File Upload Success");
            } else {
                $valid=false;
                log_message("error","File Upload Failed");
            }
            $connection = NULL;
            return $valid;

    }


    public function setClosingBulan()
    {   
        $month = date('m');
        $year = date('Y');
        $date = date('Y-m-d');
        $satubulan = date('Y-m-d',strtotime("-10 days"));
        $satubulan = date('Y-m-t',strtotime($satubulan));
        if($month != '01'){
            $valid = $this->db->query("update trs_invsum
                                    set trs_invsum.SAwal".$month." = trs_invsum.UnitStok,
                                        trs_invsum.VAwal".$month." = trs_invsum.ValueStok
                                    where trs_invsum.Cabang ='".$this->cabang."' and 
                                          trs_invsum.Tahun = '".$year."'");
            
            //cek sawal , unitstok, dan saldo akhir
            /*$cek = $this->cek_sawal_closing_bulanan();
            if ($cek > 0 ) {
                return false;
            }*/

            $valid = $this->db->query("update trs_invdet
                                    set trs_invdet.SAwa".$month." = trs_invdet.UnitStok,
                                        trs_invdet.VAwa".$month." = trs_invdet.ValueStok
                                    where trs_invdet.Cabang ='".$this->cabang."' and 
                                          trs_invdet.Tahun = '".$year."'");

        }else{
            $valid = $this->db->query("INSERT INTO `trs_invsum`
                                    (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,`Pabrik`,`KodeProduk`,
                                     `NamaProduk`,`UnitStok`,`ValueStok`,`Gudang`,`Indeks`,`UnitCOGS`,
                                     `HNA`,`SAwal01`,`VAwal01`,`SAwal02`,`VAwal02`,`SAwal03`,`VAwal03`,
                                     `SAwal04`,`VAwal04`,`SAwal05`,`VAwal05`,`SAwal06`,`VAwal06`,
                                     `SAwal07`,`VAwal07`,`SAwal08`,`VAwal08`,`SAwal09`,`VAwal09`,
                                     `SAwal10`,`VAwal10`,`SAwal11`,`VAwal11`,`SAwal12`,`VAwal12`,
                                     `LastBuy`,`LastSales`,`AddedUser`,`AddedTime`,`ModifiedUser`,
                                     `ModifiedDate`)
                                    SELECT '".$year."',`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                                     `Pabrik`,`KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,
                                     `Gudang`,`Indeks`,`UnitCOGS`,`HNA`,`UnitStok`,`ValueStok`,
                                     '0','0','0','0','0','0','0','0','0','0','0','0','0',
                                     '0','0','0','0','0','0','0','0','0',`LastBuy`,`LastSales`,
                                     `AddedUser`,`AddedTime`,`ModifiedUser`,`ModifiedDate`
                                    FROM trs_invsum where Tahun = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR));");
            //cek sawal , unitstok, dan saldo akhir
            $cek = $this->cek_sawal_closing_bulanan();
            if ($cek > 0 ) {
                return false;
            }
            $valid = $this->db->query("INSERT INTO `trs_invdet`
                                        (`Tahun`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,`Pabrik`,
                                         `KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,`UnitCOGS`,
                                         `BatchNo`,`ExpDate`,`NoDokumen`,`Gudang`,`TanggalDokumen`,
                                         `SAwa01`,`VAwa01`,`SAwa02`,`VAwa02`,`SAwa03`,`VAwa03`,
                                         `SAwa04`,`VAwa04`,`SAwa05`,`VAwa05`,`SAwa06`,`VAwa06`,
                                         `SAwa07`,`VAwa07`,`SAwa08`,`VAwa08`,`SAwa09`,`VAwa09`,
                                         `SAwa10`,`VAwa10`,`SAwa11`,`VAwa11`,`SAWa12`,`VAwa12`,
                                         `Keterangan`,`LastBuy`,`LastSales`,`AddedUser`,
                                         `AddedTime`,`ModifiedUser`,`ModifiedTime`)
                            SELECT '".$year."',`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,`Pabrik`,
                                         `KodeProduk`,`NamaProduk`,`UnitStok`,`ValueStok`,
                                         `UnitCOGS`,`BatchNo`,`ExpDate`,`NoDokumen`,`Gudang`, `TanggalDokumen`,
                                         `UnitStok`,`ValueStok`,'0','0','0','0','0','0','0','0',
                                         '0','0','0','0','0','0','0','0','0','0','0', '0','0','0',
                                         `Keterangan`,`LastBuy`,`LastSales`,`AddedUser`,
                                         `AddedTime`,`ModifiedUser`,`ModifiedTime`
                                     FROM trs_invdet where Tahun = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR));");
        }
        $cekstokawal = $this->db->query("select * 
                            from trs_invsum
                            where UnitStok != SAwal".$month." and 
                                  Cabang ='".$this->cabang."' and 
                                  Tahun = '".$year."'")->num_rows();
		
        if($cekstokawal < 1){
            $valid = $this->db->query("update mst_closing
                                    set mst_closing.tgl_stok_closing = '".$satubulan."',mst_closing.flag_stok ='N',
                                        mst_closing.tgl_daily_closing = '".date('Y-m-d')."'
                                       ");
            $this->db->query("INSERT INTO `mst_limit_pembelian`
                                (`id`,`Kat`,`Bulan`,`Cabang`,`Prinsipal`,`Limit_Beli`,
                                 `Limit_Outstanding`, `Pesanan`,`Sisa_Limit`,`Pcg`,
                                 `Beli1`,`L1`,`Beli2`,`Pcg1`,`Ekat`,`Created_by`,`Updated_by`,
                                 `Created_at`,`Updated_at`, `limit_awal`)
                            SELECT `id`,`Kat`,'".date('Y-m-t')."',`Cabang`,`Prinsipal`,`Limit_Beli`,`Limit_Outstanding`,
                              `Pesanan`,`Sisa_Limit`,`Pcg`,`Beli1`,`L1`,`Beli2`,`Pcg1`,`Ekat`,
                              `Created_by`,`Updated_by`,`Created_at`, `Updated_at`,`limit_awal`
                            FROM `mst_limit_pembelian` where Bulan ='".$satubulan."'");
            $this->db->query("INSERT INTO `mst_limit_pembelian_cabang`
                                (`id`,`Kat`,`Bulan`,`Cabang`,`Limit_Beli`,
                                 `Limit_Outstanding`, `Pesanan`,`Sisa_Limit`,`Pcg`,
                                 `Beli1`,`L1`,`Beli2`,`Pcg1`,`Ekat`,`Created_by`,`Updated_by`,
                                 `Created_at`,`Updated_at`, `limit_awal`)
                            SELECT `id`,`Kat`,'".date('Y-m-t')."',`Cabang`,`Limit_Beli`,`Limit_Outstanding`,
                              `Pesanan`,`Sisa_Limit`,`Pcg`,`Beli1`,`L1`,`Beli2`,`Pcg1`,`Ekat`,
                              `Created_by`,`Updated_by`,`Created_at`, `Updated_at`,`limit_awal`
                            FROM `mst_limit_pembelian_cabang` where Bulan ='".$satubulan."'");
            $this->Data_AMS3();
        }else{
            $valid =false;
        }
        return $valid;
		// $dbhost = 'localhost:3306';
		// $dbuser = 'sapta';
		// $dbpass = 'Sapta254*x';
		// $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
		// $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode,LOWER(Cabang) AS kCab from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
		// while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];$kkcab=$row['kCab'];}
		
  //       $this->Model_inventori->send_message_tele("SH KIRIM DATA CLOSING BULANAN ".$this->cabang." ");
  //       shell_exec("/var/www/html/federateday.sh");
  //       $this->Model_inventori->send_message_tele("SH PROSES DATA CLSOING BULANAN ".$this->cabang." ");
  //       $connsh = ssh2_connect('119.235.19.138', 22);
  //       ssh2_auth_password($connsh, 'serversst', 'Sapta777*x');
  //       ssh2_exec($connsh, '/var/www/html/fedcab/'.$kkcab.'.sh');
  //       //ssh2_exec($connsh, 'exit');
  //       //unset($connsh);
  //       $this->Model_inventori->send_message_tele("SH SELESAI PROSES DATA CLSOING BULANAN ".$this->cabang." ");		
		// mysqli_close($conn);

  //       $this->Model_inventori->send_message_tele("SEND PT, CABANG ".$this->cabang." ");
  //       $this->Model_inventori->getTelePT();
  //       $this->Model_inventori->send_message_tele("SEND PU, CABANG ".$this->cabang." ");
  //       $this->Model_inventori->getTelePU();
  //       $this->Model_inventori->send_message_tele("SEND STOK, CABANG ".$this->cabang." ");
  //       $this->Model_inventori->getTeleStok();
  //       $this->Model_inventori->send_message_tele("SEND KAS/BANK, CABANG ".$this->cabang." ");
  //       $this->Model_inventori->getTeleKasBank();

  //       $this->Model_inventori->send_message_tele("CREATE AMS3, CABANG ".$this->cabang." ");
        
  //       $this->Model_inventori->send_message_tele("FINISH CREATE AMS3, CABANG ".$this->cabang." ");
		
		// $this->Model_inventori->send_message_tele("CLOSING BULANAN TANGGAL : ".date('Y-m-d',strtotime("-1 days")).", BERHASIL, CABANG ".$this->cabang." ".$this->tele_sales($valid)." ",-171688895);
        // return $valid;
    }

    
    public function setClosingdaily($tipe=null,$status=null)
    {   
        $datetime = date('Y-m-d H:i:s');
        if($tipe =='daily'){
            $date  = date('Y-m-d',strtotime("+1 days"));
        }else{
            $date = date('Y-m-d');
        }
            if($status =='ok'){
                $valid = $this->db->query("update mst_closing set mst_closing.tgl_daily_closing = '".$date."', mst_closing.time_daily_closing = '".$datetime."', mst_closing.flag_trans = 'Y' where Cabang ='".$this->cabang."'");
                $this->set_invday($tipe);
            }else{
                $valid = $this->db->query("update mst_closing set mst_closing.flag_trans = 'N' where Cabang ='".$this->cabang."'");
            }
        $this->setcreatetablepu($tipe,"ok");
        return $valid;
    }
	
	public function tele_sales($kapan = null)
	{
		if(empty($kapan))
		{
			$tglData = " DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())";
		}else{
			$tglData = " DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) AND LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH))";		
		}



		// echo ('awal '.date('Y-m-d h:i:sa'));
		$dbhost = 'localhost:3306';
		// $dbhost = 'bandungsst.ddns.net:3306';
		$dbuser = 'sapta';
		$dbpass = 'Sapta254*x';
		$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
		$qData = mysqli_query($conn,"
				SELECT LOWER(b.Cabang) kCab,LOWER(b.Kode) as Kode FROM sst.mst_user a, sst.mst_cabang b
                  WHERE a.username='Cabang' AND a.Cabang = b.Cabang
                  LIMIT 1");
		while($row = mysqli_fetch_assoc($qData))
			{
				$kkdcab=$row['Kode'];
				$kkcab=$row['kCab'];
			}
			
			
		$qLunas = mysqli_query($conn,"
				SELECT Cabang,`Status`,SUM(ValuePelunasan) AS ValuePelunasan,Tipe FROM (
							SELECT
							  `Cabang`,`NomorFaktur`,`KodePelanggan`,`TglFaktur`,`TglPelunasan`,`NoDIH`,`KodeSalesman`, '' AS TipeSalesman,
								`KodePenagih`, '' AS TipePenagih, `ValueFaktur`,`Cicilan`,`SaldoFaktur`,
								CASE WHEN TipeDokumen IN ('Retur','CN') THEN ABS(IFNULL(`ValuePelunasan`,0))*-1 ELSE ABS(IFNULL(`ValuePelunasan`,0)) END AS ValuePelunasan,
								`SaldoAkhir`, `UmurLunas`,
								`ValueGiro`,`Giro`,`TglGiroCair`,`Status`, 'C' AS Tipe
							FROM `sst`.`trs_pelunasan_detail` WHERE `status` NOT LIKE '%batal%'
							AND 
							CASE WHEN IFNULL(`Status`,'') IN ('GiroCair','Giro Cair') 
								THEN  DATE(TglGiroCair) BETWEEN $tglData 
								ELSE DATE(TglPelunasan) BETWEEN $tglData  
							END

							UNION ALL

							SELECT
							  `Cabang`,`NomorFaktur`,`KodePelanggan`,`TglFaktur`,`TglPelunasan`,`NoDIH`,`KodeSalesman`, '' AS TipeSalesman,
								`KodePenagih`, '' AS TipePenagih, `ValueFaktur`,`Cicilan`,`SaldoFaktur`,
								CASE WHEN TipeDokumen IN ('Retur','CN') THEN ABS(IFNULL(`ValuePelunasan`,0))*-1 ELSE ABS(IFNULL(`ValuePelunasan`,0)) END AS ValuePelunasan,
								`SaldoAkhir`, `UmurLunas`,
								`ValueGiro`,`Giro`,`TglGiroCair`,`Status`, 'G' AS tipe
							FROM `sst`.`trs_pelunasan_giro_detail` WHERE `status` LIKE '%Open%' 
							AND TglPelunasan BETWEEN DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)), INTERVAL 1 DAY) AND DATE(NOW())

					)p GROUP BY Cabang
				");
				
		$mClosingL = "";
		while($rowLun = mysqli_fetch_assoc($qLunas))
			{
				$mClosingL = "Total Tagihan : ".number_format($rowLun['ValuePelunasan'],2);
				// echo '<br>'.$mClosingL;
			}

				
		$qPiut = mysqli_query($conn,"SELECT Cabang,SUM(Saldo) AS piut FROM sst.trs_faktur WHERE IFNULL(Saldo,0) != 0");
		$mClosingP = "";
		while($rowPiut = mysqli_fetch_assoc($qPiut))
			{

				$mClosingP = "Total Piutang : ".number_format($rowPiut['piut'],2);
				// echo '<br>'.$mClosingP;
				// echo "mClosingP";

			}

		// $qStok = mysqli_query($conn,"
			// SELECT Cabang,'TOTAL STOK' AS NamaPrinsipal,SUM(ValueStok) AS stk FROM sst.trs_invsum 
					// WHERE IFNULL(ValueStok,0) != 0
					// GROUP BY Cabang
			// UNION ALL
			// SELECT Cabang,NamaPrinsipal,SUM(ValueStok) AS stk FROM sst.trs_invsum 
					// WHERE IFNULL(ValueStok,0) != 0
					// GROUP BY Cabang,NamaPrinsipal 
			// ");

		$qStok = mysqli_query($conn,"
			SELECT Cabang,'TOTAL STOK' AS NamaPrinsipal,SUM(ValueStok) AS stk FROM sst.trs_invsum 
					WHERE IFNULL(ValueStok,0) != 0 AND Tahun=YEAR(NOW())
					GROUP BY Cabang
			");

		$mClosingS = "";
		$mClosingSp = "";
		while($rowStok = mysqli_fetch_assoc($qStok))
			{
				$mClosingSp = "Total Stok Prinsipal ".$rowStok['NamaPrinsipal'].": ".number_format($rowStok['stk'],2);
				$mClosingS = $mClosingS.$mClosingSp;
				// echo '<br>'.$mClosingS;
			}



		$qSales = mysqli_query($conn,"

				SELECT NM_CABANG,
					DAY(LAST_DAY(NOW())) AS JUMLAHHARIBULAN, 
					DAY(NOW()) AS JUMLAHHARI, 
					DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)), INTERVAL 1 DAY) AS TGLAWAL,
					DATE(NOW()) AS TGLAKHIR,
					YEAR(TGLDOK) AS Tahun,
					MONTH(TGLDOK) AS Bulan,
					MONTHNAME(TGLDOK) AS NamaBulan,
					(YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH)))) AS mingguke,
					SUM(jumTot) AS jumTot,	
					ROUND(SUM(jumTot)/DAY(NOW()),2) AS Rata2PerHari,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( W1) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( W1) ELSE 0 END) AS WK1,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( W2) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( W2) ELSE 0 END) AS WK2,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( W3) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( W3) ELSE 0 END) AS WK3,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( W4) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( W4) ELSE 0 END) AS WK4,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( W5) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( W5) ELSE 0 END) AS WK5,	
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day1) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day1) ELSE 0 END) AS day1,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day2) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day2) ELSE 0 END) AS day2,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day3) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day3) ELSE 0 END) AS day3,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day4) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day4) ELSE 0 END) AS day4,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day5) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day5) ELSE 0 END) AS day5,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day6) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day6) ELSE 0 END) AS day6,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day7) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day7) ELSE 0 END) AS day7,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day8) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day8) ELSE 0 END) AS day8,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day9) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day9) ELSE 0 END) AS day9,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day10) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day10) ELSE 0 END) AS day10,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day11) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day11) ELSE 0 END) AS day11,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day12) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day12) ELSE 0 END) AS day12,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day13) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day13) ELSE 0 END) AS day13,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day14) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day14) ELSE 0 END) AS day14,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day15) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day15) ELSE 0 END) AS day15,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day16) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day16) ELSE 0 END) AS day16,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day17) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day17) ELSE 0 END) AS day17,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day18) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day18) ELSE 0 END) AS day18,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day19) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day19) ELSE 0 END) AS day19,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day20) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day20) ELSE 0 END) AS day20,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day21) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day21) ELSE 0 END) AS day21,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day22) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day22) ELSE 0 END) AS day22,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day23) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day23) ELSE 0 END) AS day23,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day24) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day24) ELSE 0 END) AS day24,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day25) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day25) ELSE 0 END) AS day25,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day26) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day26) ELSE 0 END) AS day26,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day27) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day27) ELSE 0 END) AS day27,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day28) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day28) ELSE 0 END) AS day28,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day29) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day29) ELSE 0 END) AS day29,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day30) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day30) ELSE 0 END) AS day30,
					(CASE WHEN TipeDokumen IN ('Faktur','DO','CD') THEN SUM( day31) ELSE 0 END - CASE WHEN TipeDokumen IN ('Retur') THEN SUM( day31) ELSE 0 END) AS day31
				FROM(
				SELECT NM_CABANG,TGLDOK,NILJU AS jumTot, TipeDokumen,
					CASE WHEN DAY(TGLDOK)=1 THEN NILJU END AS day1,
					CASE WHEN DAY(TGLDOK)=2 THEN NILJU END AS day2,
					CASE WHEN DAY(TGLDOK)=3 THEN NILJU END AS day3,
					CASE WHEN DAY(TGLDOK)=4 THEN NILJU END AS day4,
					CASE WHEN DAY(TGLDOK)=5 THEN NILJU END AS day5,
					CASE WHEN DAY(TGLDOK)=6 THEN NILJU END AS day6,
					CASE WHEN DAY(TGLDOK)=7 THEN NILJU END AS day7,
					CASE WHEN DAY(TGLDOK)=8 THEN NILJU END AS day8,
					CASE WHEN DAY(TGLDOK)=9 THEN NILJU END AS day9,
					CASE WHEN DAY(TGLDOK)=10 THEN NILJU END AS day10,
					CASE WHEN DAY(TGLDOK)=11 THEN NILJU END AS day11,
					CASE WHEN DAY(TGLDOK)=12 THEN NILJU END AS day12,
					CASE WHEN DAY(TGLDOK)=13 THEN NILJU END AS day13,
					CASE WHEN DAY(TGLDOK)=14 THEN NILJU END AS day14,
					CASE WHEN DAY(TGLDOK)=15 THEN NILJU END AS day15,
					CASE WHEN DAY(TGLDOK)=16 THEN NILJU END AS day16,
					CASE WHEN DAY(TGLDOK)=17 THEN NILJU END AS day17,
					CASE WHEN DAY(TGLDOK)=18 THEN NILJU END AS day18,
					CASE WHEN DAY(TGLDOK)=19 THEN NILJU END AS day19,
					CASE WHEN DAY(TGLDOK)=20 THEN NILJU END AS day20,
					CASE WHEN DAY(TGLDOK)=21 THEN NILJU END AS day21,
					CASE WHEN DAY(TGLDOK)=22 THEN NILJU END AS day22,
					CASE WHEN DAY(TGLDOK)=23 THEN NILJU END AS day23,
					CASE WHEN DAY(TGLDOK)=24 THEN NILJU END AS day24,
					CASE WHEN DAY(TGLDOK)=25 THEN NILJU END AS day25,
					CASE WHEN DAY(TGLDOK)=26 THEN NILJU END AS day26,
					CASE WHEN DAY(TGLDOK)=27 THEN NILJU END AS day27,
					CASE WHEN DAY(TGLDOK)=28 THEN NILJU END AS day28,
					CASE WHEN DAY(TGLDOK)=29 THEN NILJU END AS day29,
					CASE WHEN DAY(TGLDOK)=30 THEN NILJU END AS day30,
					CASE WHEN DAY(TGLDOK)=31 THEN NILJU END AS day31,
					CASE WHEN (YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH))))=1 THEN NILJU END AS W1,
					CASE WHEN (YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH))))=2 THEN NILJU END AS W2,
					CASE WHEN (YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH))))=3 THEN NILJU END AS W3,
					CASE WHEN (YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH))))=4 THEN NILJU END AS W4,
					CASE WHEN (YEARWEEK(TGLDOK,1) - YEARWEEK(LAST_DAY(DATE_SUB(TGLDOK, INTERVAL 1 MONTH))))=5 THEN NILJU END AS W5
					
				FROM 
					(
					SELECT 
						trs_faktur_detail.Cabang AS 'NM_CABANG',
						DATE(trs_faktur_detail.TglFaktur) AS 'TGLDOK',
						trs_faktur_detail.Value AS 'NILJU',
						trs_faktur_detail.TipeDokumen
					FROM sst.trs_faktur_detail 
					WHERE trs_faktur_detail.TglFaktur BETWEEN $tglData												  
					UNION ALL								
					SELECT 
						   a.Cabang AS 'NM_CABANG',
						   DATE(trs_faktur.TglFaktur) AS 'TGLDOK',
						   IFNULL(a.Jumlah,0) AS 'NILJU',
						   'CD' AS TipeDokumen
					FROM sst.trs_faktur_cndn a,sst.trs_faktur
							WHERE  a.Status IN ('CNOK','DNOK')
							AND a.NoDokumen=trs_faktur.NoFaktur
							AND a.Cabang=trs_faktur.Cabang
							AND TanggalCNDN BETWEEN $tglData	   
					UNION ALL															
					SELECT 
						   trs_delivery_order_sales_detail.Cabang AS 'NM_CABANG',
						   DATE(TglDO) AS 'TGLDOK',
						   trs_delivery_order_sales_detail.Value AS 'NILJU',
						   trs_delivery_order_sales_detail.TipeDokumen
					   FROM sst.trs_delivery_order_sales_detail
					 WHERE  ((STATUS IN ('Open','Kirim','Terima','Retur')) OR (STATUS ='Closed' AND IFNULL(status_retur,'') ='Y'))
						   AND TglDO BETWEEN $tglData
						   ) AS LapPT
				WHERE LapPT.NM_CABANG LIKE '%%'  
				)DAT GROUP BY NM_CABANG
		");

				$mClosing = "";
		while($rowSales = mysqli_fetch_assoc($qSales))
			{
				$mClosing = "Total Sales ".$rowSales['NamaBulan']." : ".number_format($rowSales['jumTot'],2);
				$mClosing2 = "Total Sales ".$rowSales['NamaBulan'].", DAY ".date('j')." : ".number_format($rowSales['day'.date('j')],2);
				$mClosing3 = "Total Sales ".$rowSales['NamaBulan'].", Week 1 : ".number_format($rowSales['WK1'],2);
				$mClosing4 = "Total Sales ".$rowSales['NamaBulan'].", Week 2 : ".number_format($rowSales['WK2'],2);
				$mClosing5 = "Total Sales ".$rowSales['NamaBulan'].", Week 3 : ".number_format($rowSales['WK3'],2);
				$mClosing6 = "Total Sales ".$rowSales['NamaBulan'].", Week 4 : ".number_format($rowSales['WK4'],2);
				$mClosing7 = "Total Sales ".$rowSales['NamaBulan'].", Week 5 : ".number_format($rowSales['WK5'],2);
			}
			
			
        $bDatax = 'Cabang '.$rowSales['NM_CABANG'].'
        '.$mClosingP.'
        '.$mClosingL.'
        '.$mClosing.'
        '.$mClosing2.'
        '.$mClosingS.'
		';
		
		// $this->Model_inventori->send_message_tele($mClosing." ".$this->cabang." ",-171688895);
		mysqli_close($conn);
		// mysqli_close($conn);
		
		return $bDatax;
	}

    public function Data_AMS3()
    { 
        if(date('t') != '31'){
          $tigabulan = date('Y-m-01', strtotime('-3 months'));
          $tigabulan_akhir = date('Y-m-t', strtotime('-3 months'));
          $duabulan = date('Y-m-01', strtotime('-2 months'));
          $duabulan_akhir = date('Y-m-t', strtotime('-2 months'));
          $satubulan_awal = date('Y-m-01', strtotime('-1 months'));
          $satubulan = date('Y-m-t', strtotime('-1 months'));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }else{
            $daynumber = date('d');
            if($daynumber <= 5){
                $satu  = date('Y-m-01',strtotime("-10 days"));
                $dua  = date('Y-m-01',strtotime("-36 days"));
                $tiga  = date('Y-m-01',strtotime("-70 days"));
            }else if($daynumber > 5 and $daynumber < 15){
                $satu  = date('Y-m-01',strtotime("-15 days"));
                $dua  = date('Y-m-01',strtotime("-46 days"));
                $tiga  = date('Y-m-01',strtotime("-77 days"));
            }else if($daynumber >= 15 and $daynumber < 20){
                $satu  = date('Y-m-01',strtotime("-21 days"));
                $dua  = date('Y-m-01',strtotime("-52 days"));
                $tiga  = date('Y-m-01',strtotime("-83 days"));
                // $curclose  = date('Y-m-d',strtotime("-21 days"));
            }else if($daynumber >= 20 and $daynumber < 25){
                $satu  = date('Y-m-01',strtotime("-26 days"));
                $dua  = date('Y-m-01',strtotime("-57 days"));
                $tiga  = date('Y-m-01',strtotime("-88 days"));
                // $curclose  = date('Y-m-d',strtotime("-26 days"));
            }else if($daynumber >= 25){
                $satu  = date('Y-m-01',strtotime("-32 days"));
                $dua  = date('Y-m-01',strtotime("-63 days"));
                $tiga  = date('Y-m-01',strtotime("-94 days"));
                // $curclose  = date('Y-m-d',strtotime("-32 days"));
            }
          $tigabulan = date('Y-m-01', strtotime($tiga));
          $tigabulan_akhir = date('Y-m-t', strtotime($tiga));

          $duabulan = date('Y-m-01', strtotime($dua));
          $duabulan_akhir = date('Y-m-t', strtotime($dua));

          $satubulan_awal = date('Y-m-01', strtotime($satu));
          $satubulan = date('Y-m-t', strtotime($satu));
          $bulanini = date('Y-m-01');
          $bulanini_akhir = date('Y-m-d',strtotime("-1 days"));
        }
        $valid = $this->db->query("TRUNCATE TABLE `sst`.`mst_data_ams3`");
        $valid = $this->db->query("INSERT INTO `sst`.`mst_data_ams3` (
                          `Cabang`,`Produk`,`Prinsipal`,
                          `Qtyjualtigabulan`,`Qtyjualduabulan`,
                          `Qtyjualsatubulan`,`QtyAvgjual`,`COGS`,
                          `Created_by`,`Created_at`
                        )
                        SELECT DISTINCT trs_invsum.`Cabang`,
                               trs_invsum.`KodeProduk`,
                               trs_invsum.`NamaPrinsipal`,
                               IFNULL(tigabulan.`qtyfaktur`,0) AS 'qtyfakturtigabulan',
                               IFNULL(duabulan.`qtyfaktur`,0) AS 'qtyfakturduabulan',
                               IFNULL(satubulan.`qtyfaktur`,0) AS 'qtyfaktursatubulan',
                               (CASE WHEN IFNULL(tigabulan.`qtyfaktur`,0) = 0 AND IFNULL(duabulan.`qtyfaktur`,0) = 0 THEN IFNULL(satubulan.`qtyfaktur`,0)
                                WHEN IFNULL(tigabulan.`qtyfaktur`,0) = 0 AND IFNULL(duabulan.`qtyfaktur`,0) > 0 THEN (IFNULL(satubulan.`qtyfaktur`,0) + IFNULL(duabulan.`qtyfaktur`,0))/2
                                WHEN IFNULL(tigabulan.`qtyfaktur`,0) > 0 THEN (IFNULL(satubulan.`qtyfaktur`,0) + IFNULL(duabulan.`qtyfaktur`,0) + IFNULL(tigabulan.`qtyfaktur`,0))/3
                                ELSE 0 END ) AS 'avgjual',
                                trs_invsum.`UnitCOGS`,
                                'Cabang',
                                NOW()
                           FROM trs_invsum 
                           LEFT JOIN  
                               (SELECT trs_faktur_detail.`KodeProduk`,
                                      SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
                                      ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
                                 FROM trs_faktur_detail
                                WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$tigabulan."' AND '".$tigabulan_akhir."'
                                GROUP BY trs_faktur_detail.`KodeProduk`) AS tigabulan ON tigabulan.KodeProduk = trs_invsum.`KodeProduk` 
                           LEFT JOIN
                               (SELECT trs_faktur_detail.`KodeProduk`,
                                       SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
                                       ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
                                  FROM trs_faktur_detail
                                WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$duabulan."' AND '".$duabulan_akhir."'
                                GROUP BY trs_faktur_detail.`KodeProduk`) AS duabulan ON duabulan.KodeProduk = trs_invsum.`KodeProduk`
                           LEFT JOIN
                              (SELECT trs_faktur_detail.`KodeProduk`,
                                     SUM(CASE WHEN trs_faktur_detail.`TipeDokumen`='Faktur' THEN (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) 
                                     ELSE (trs_faktur_detail.`QtyFaktur` + trs_faktur_detail.`BonusFaktur`) * -1 END) AS 'qtyfaktur'
                               FROM trs_faktur_detail
                               WHERE trs_faktur_detail.`TglFaktur` BETWEEN '".$satubulan_awal."' AND '".$satubulan."'
                              GROUP BY trs_faktur_detail.`KodeProduk`) AS satubulan ON satubulan.KodeProduk = trs_invsum.`KodeProduk`
                          WHERE trs_invsum.tahun ='".date('Y')."' AND Gudang ='Baik';");

    }
    public function listFixDOFaktur($tipe=null,$jenis=null){
        if($jenis == 'monthly'){
            $tgl  = date('Y-m-d',strtotime("-7 days"));
            $tgl1  = date('Y-m-01',strtotime($tgl));
            $tgl2  = date('Y-m-t',strtotime($tgl));
            $tgl11  = date('Y-m-t',strtotime($tgl));
            $tgl22  = date('Y-m-d',strtotime("-3 days",strtotime($tgl11)));
        }else{
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-d');
            $daynumber = DATE('d');
            if($daynumber <= 3){
                $tgl11  = DATE('Y-m-01');
                $tgl22  = DATE('Y-m-d');
            }else{
                $dayofweek = date('w');
                if($dayofweek == 1){
                    $tgl11  = date('Y-m-d',strtotime("-5 days"));
                }else{
                    $tgl11  = date('Y-m-d',strtotime("-3 days"));
                }
                // $tgl11  = DATE('Y-m-d',strtotime("-3 days"));
                $tgl22  = DATE('Y-m-d');
            }
        }
        
        if($tipe=='doheader'){
            $query = $this->db->query("SELECT a.`Cabang`,a.NoDO,a.TglDO,
                                    SUM(a.`Total`) AS 'Total',
                                        b.`NoFaktur`,
                                        b.TglFaktur,
                                        IFNULL(b.`Total`,0) AS 'TotalB'
                                     FROM `trs_delivery_order_sales` a
                                     LEFT JOIN
                                     (
                                       SELECT a.`Cabang`,
                                              a.`NoFaktur`,
                                              a.`TglFaktur`,
                                              SUM(a.`Total`) AS 'Total'
                                        FROM `trs_faktur` a
                                       WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                             a.`TipeDokumen` IN ('Faktur')
                                        GROUP BY a.`Cabang`,
                                              a.`NoFaktur`,a.`TglFaktur`)b 
                                    ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                                    WHERE a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                        IFNULL(a.status_retur,'N')='N' AND 
                                         a.`Status` ='Closed' 
                                    GROUP BY a.`Cabang`,a.NoDO,a.TglDO
                                    HAVING Total != TotalB;");
        }
        elseif($tipe=='dodetail'){
            $query = $this->db->query("SELECT a.`Cabang`,a.NoDO,a.TglDO,
                                        SUM(a.`Total`) AS 'Total',
                                        b.`NoFaktur`,
                                        IFNULL(b.`Total`,0) AS 'TotalB'
                                     FROM `trs_delivery_order_sales_detail` a
                                     LEFT JOIN
                                     (
                                       SELECT a.`Cabang`,
                                              a.`NoFaktur`,
                                              a.`TglFaktur`,
                                              SUM(a.`Total`) AS 'Total'
                                        FROM `trs_faktur_detail` a
                                       WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                             a.`TipeDokumen` IN ('Faktur')
                                        GROUP BY a.`Cabang`,
                                              a.`NoFaktur`,a.`TglFaktur`)b 
                                    ON a.`NoDO`=b.NoFaktur AND a.`Cabang`=b.Cabang
                                    WHERE a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."' AND 
                                        IFNULL(a.status_retur,'N')='N' AND 
                                         a.`Status` ='Closed' 
                                    GROUP BY a.`Cabang`,a.NoDO,a.TglDO
                                    HAVING Total != TotalB;");
        }
        elseif($tipe=='cndn'){
            $query = $this->db->query("SELECT a.`Cabang`,
                                           a.NoFaktur,
                                           a.TglFaktur,
                                           a.`Total`,
                                           dd.`Total` as 'Total_detail'
                                     FROM `trs_faktur` a
                                     LEFT JOIN
                                     (
                                        SELECT a.`Cabang`,a.NoDokumen,
                                               SUM(a.`Jumlah`) AS Total
                                        FROM `trs_faktur_cndn` a
                                        WHERE a.TanggalCNDN BETWEEN '".$tgl1."' AND '".$tgl2."' 
                                        GROUP BY a.`Cabang`,a.`NoDokumen`) dd 
                                     ON a.`Cabang`=dd.`Cabang` AND 
                                        a.`NoFaktur`= dd.`NoDokumen`
                                    WHERE a.TglFaktur BETWEEN '".$tgl1."' AND '".$tgl2."'  AND 
                                          a.`TipeDokumen` IN ('CN','DN') AND 
                                         (IFNULL(a.`Total`,0) - IFNULL(dd.`Total`,0)) > 100;");
        }
        elseif($tipe=='dokirim'){
            $query = $this->db->query("SELECT Cabang,NoDO,
                                    TglDO,'header' AS 'status' 
                                    FROM trs_delivery_order_sales
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                          IFNULL(status_retur,'N')='N' AND  
                                          nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim' and TglTerima between '".$tgl1."' AND '".$tgl2."') AND 
                                          tgldo BETWEEN '".$tgl1."' AND '".$tgl2."' 
                                    UNION ALL
                                    SELECT DISTINCT Cabang,NoDO,TglDO,'detail' AS 'status' 
                                    FROM trs_delivery_order_sales_detail
                                    WHERE STATUS IN ('Kirim','Open') AND 
                                      IFNULL(status_retur,'N')='N' AND  
                                      nodo IN (SELECT NoDO FROM trs_kiriman WHERE StatusKiriman ='Closed' AND statusDO ='Terkirim' and TglTerima between '".$tgl1."' AND '".$tgl2."') AND 
                                      tgldo BETWEEN '".$tgl1."' AND '".$tgl2."' ;");
        }
        
        elseif($tipe=='vallunas'){
            $query = $this->db->query("SELECT a.Cabang,a.NoFaktur,a.Value AS  valuefaktur,a.TglFaktur,a.TipeDokumen ,
                a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,
                (CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                FROM trs_faktur a
                LEFT JOIN (
                  SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                    SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                    CASE 
                      WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                      WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      ELSE (IFNULL(ValuePelunasan,0) )
                      END AS ValuePelunasanx
                     
                    FROM trs_pelunasan_detail 
                       WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                    UNION ALL
                    SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                    CASE 
                      WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                      WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                      ELSE (IFNULL(ValuePelunasan,0) )
                      END AS ValuePelunasanx
                    FROM trs_pelunasan_giro_detail 
                       WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                    )xdat GROUP BY NomorFaktur
                )pel ON a.NoFaktur=pel.NomorFaktur
                 WHERE a.NoFaktur IN (
                  SELECT DISTINCT NomorFaktur FROM(
                  SELECT NomorFaktur
                  FROM trs_pelunasan_detail 
                     WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                     AND TglPelunasan BETWEEN '".$tgl11."' AND '".$tgl22."'
                  UNION ALL
                  SELECT NomorFaktur
                  FROM trs_pelunasan_giro_detail 
                     WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                     AND TglPelunasan BETWEEN '".$tgl11."' AND '".$tgl22."'
                     )xx
                ) 
                HAVING selisih != 0;");
        }elseif($tipe=='doopen'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`NoDO`,a.`TglDO`,
                                   a.`Status`,a.`status_retur`,
                                   b.NoKiriman,b.StatusKiriman,b.StatusDO
                            FROM trs_delivery_order_sales a 
                            LEFT JOIN 
                            ( SELECT b.`NoKiriman`,b.`NoDO`,b.`TglKirim`,b.`TglTerima`,b.`StatusKiriman`,b.`StatusDO` 
                            FROM trs_kiriman b
                            where b.TglKirim BETWEEN '".$tgl1."' AND '".$tgl2."' ) b
                            ON a.`NoDO` = b.NoDO
                            WHERE IFNULL(a.Status,'') = 'Open' AND a.tipedokumen='DO' 
                            AND a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."'");
        }elseif($tipe=='doclosed'){
            $query = $this->db->query("SELECT a.`Cabang`,a.`NoDO`,a.`TglDO`,
                                   a.`Status`,a.`status_retur`,
                                   b.NoKiriman,b.StatusKiriman,b.StatusDO
                            FROM trs_delivery_order_sales a 
                            LEFT JOIN 
                            ( SELECT b.`NoKiriman`,b.`NoDO`,b.`TglKirim`,b.`TglTerima`,b.`StatusKiriman`,b.`StatusDO` 
                            FROM trs_kiriman b
                            where b.TglKirim BETWEEN '".$tgl1."' AND '".$tgl2."' ) b
                            ON a.`NoDO` = b.NoDO
                            WHERE IFNULL(a.Status,'') = 'Kirim' AND a.tipedokumen='DO' 
                            AND a.TglDO BETWEEN '".$tgl1."' AND '".$tgl2."';");
        }elseif($tipe=='bpb'){
            $query = $this->db->query("SELECT a.`Cabang`,
                                           a.`NoDokumen`,
                                           a.`NoDO`,
                                           a.`flag_suratjalan`,
                                           a.`TglDokumen`,
                                           SUM(a.`Value`) AS 'Value',
                                           SUM(a.`Total`) AS 'Total',
                                           DObeli.NoDokumen AS 'DOBeli',
                                           DObeli.Value AS 'ValueDO',
                                           DObeli.Total AS 'TotalDO',
                                           (CASE WHEN SUM(a.`Value`) != DObeli.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                           (CASE WHEN SUM(a.`Total`) != DObeli.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                    FROM trs_terima_barang_header a
                                    LEFT JOIN (
                                        SELECT b.`Cabang`,
                                            b.`NoDokumen`,
                                            b.`TglDokumen`,
                                            SUM(b.`Value`) AS 'Value',
                                            SUM(b.`Total`) AS 'Total'
                                        FROM trs_delivery_order_header b
                                        WHERE b.`Status` NOT IN ('Batal','Reject')
                                            GROUP BY b.`Cabang`,b.`NoDokumen`,b.`TglDokumen`) AS DObeli ON 
                                    a.`Cabang` = DObeli.Cabang AND 
                                    a.`NoDO` =  DObeli.NoDokumen
                                    WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                           a.`Status` NOT IN ('Batal','Reject') AND 
                                          a.`TglDokumen` BETWEEN '".$tgl1."' AND '".$tgl2."'
                                    GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`, a.`flag_suratjalan`,a.`TglDokumen` 
                                    HAVING statusTotal = 'selisih';");
        }elseif($tipe=='bpbdetail'){
            $query = $this->db->query("SELECT a.`Cabang`,
                                               a.`NoDokumen`,
                                               a.`NoDO`,
                                               a.`TglDokumen`,
                                               SUM(a.`Value`) AS 'Value',
                                               SUM(a.`Total`) AS 'Total',
                                               DObeli.NoDokumen AS 'DOBeli',
                                               DObeli.Value AS 'ValueDO',
                                               DObeli.Total AS 'TotalDO',
                                               (CASE WHEN SUM(a.`Value`) != DObeli.Value THEN 'selisih' ELSE 'ok' END) AS 'statusValue',
                                               (CASE WHEN SUM(a.`Total`) != DObeli.Total THEN 'selisih' ELSE 'ok' END) AS 'statusTotal'
                                        FROM trs_terima_barang_detail a
                                        LEFT JOIN (
                                            SELECT b.`Cabang`,
                                                b.`NoDokumen`,
                                                b.`TglDokumen`,
                                                SUM(b.`Value`) AS 'Value',
                                                SUM(b.`Total`) AS 'Total'
                                            FROM trs_delivery_order_detail b
                                            WHERE b.`Status` NOT IN ('Batal','Reject')
                                                GROUP BY b.`Cabang`,b.`NoDokumen`,b.`TglDokumen`) AS DObeli ON 
                                        a.`Cabang` = DObeli.Cabang AND 
                                        a.`NoDO` =  DObeli.NoDokumen
                                        WHERE LEFT(a.`NoDokumen`,3) = 'BPB' AND
                                               a.`Status` NOT IN ('Batal','Reject') AND 
                                              a.`TglDokumen` BETWEEN '".$tgl1."' AND '".$tgl2."'
                                        GROUP BY a.`Cabang`,a.`NoDokumen`,a.`NoDO`,a.`TglDokumen`
                                        HAVING statusTotal = 'selisih';");
        }
        return $query;
    }

    public function prosesfixfakturheder($no = null)
    {   
        $cek =$this->db->query("select * from trs_faktur where nofaktur ='".$no."' and ifnull(saldo,0) = ifnull(Total,0)")->num_rows();
        $query ="";
        if($cek > 0){
            $query = $this->db->query("UPDATE trs_faktur faktur
                                            left join ( SELECT *
                                                FROM trs_delivery_order_sales ) AS do
                                            ON faktur.`nofaktur` = do.NoDO
                                            SET faktur.`Gross` = do.Gross,
                                                faktur.Potongan = do.Potongan,
                                                faktur.VALUE = do.VALUE,
                                                faktur.Ppn = do.Ppn,
                                                faktur.LainLain = do.LainLain,
                                                faktur.Total = do.Total,
                                                faktur.Saldo = do.Total
                                            WHERE faktur.`nofaktur` = '".$no."';");
        }else{
            $query = $this->db->query("INSERT INTO `trs_faktur` (
                                          `Cabang`,`NoFaktur`,
                                          `TglFaktur`,`TimeFaktur`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatFaktur`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          `TipePajak`,`NoPajak`,
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,
                                          `Saldo`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`NoSP`,
                                          `statusPusat`,`TipeFaktur`,
                                          `NoIDPaket`,`KeteranganTender`,
                                          `ppn_pelanggan`,
                                          `created_at`,`created_by`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `TglDO`,`TimeDO`,
                                          `Pelanggan`,`NamaPelanggan`,
                                          `AlamatKirim`,`TipePelanggan`,
                                          `NamaTipePelanggan`,`NPWPPelanggan`,
                                          '','',
                                          `KategoriPelanggan`,`Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoOrder`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `Gross`,`Potongan`,
                                          `Value`,`Ppn`,
                                          `LainLain`,`Materai`,
                                          `OngkosKirim`,`Total`,`Total`,
                                          `Keterangan1`,`Keterangan2`,
                                          `Barcode`,`QrCode`,
                                          `NoSO`,`NoDO`,
                                          `NoSP`,`statusPusat`,
                                          `TipeFaktur`,`NoIDPaket`,
                                          `KeteranganTender`,`ppn_pelanggan`,
                                          '".date('Y-m-d H:i:s')."','".$this->user."',`acu2`
                                        FROM `trs_delivery_order_sales`
                                        where NoDo = '".$no."' and IFNULL(trs_delivery_order_sales.status_retur,'N')='N' AND trs_delivery_order_sales.`Status` ='Closed'  ;
                                        ");
        $this->prosesfixfakturdetail($no);
        }
        return $query;
    }

    public function prosesfixfakturdetail($no = null)
    {   
        $query =false;
        $cekheader =$this->db->query("select * from trs_faktur where nofaktur ='".$no."'")->num_rows();
        if($cekheader > 0){
            $cek =$this->db->query("select * from trs_faktur_detail where nofaktur ='".$no."'")->num_rows();
            if($cek > 0){
                $del= $this->db->query("delete from trs_faktur_detail where nofaktur ='".$no."'");
            }
            $query = $this->db->query("INSERT INTO `trs_faktur_detail` (
                                          `Cabang`,`NoFaktur`,
                                          `noline`,`TglFaktur`,
                                          `TimeFaktur`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatFaktur`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoFaktur`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          `Status`,`TipeDokumen`,
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyFaktur`,`BonusFaktur`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,
                                          `sisa_retur`,`created_at`,
                                          `created_by`,`kota`,
                                          `telp`,`tipe_2`,
                                          `region`,`acu2`
                                        )
                                        SELECT
                                          `Cabang`,`NoDO`,
                                          `noline`,`TglDO`,
                                          `TimeDO`,`Pelanggan`,
                                          `NamaPelanggan`,`AlamatKirim`,
                                          `TipePelanggan`,`NamaTipePelanggan`,
                                          `NPWPPelanggan`,`KategoriPelanggan`,
                                          `Acu`,`CaraBayar`,
                                          `CashDiskon`,`ValueCashDiskon`,
                                          `TOP`,`TglJtoOrder`,
                                          `Salesman`,`NamaSalesman`,
                                          `Rayon`,`NamaRayon`,
                                          'Open','Faktur',
                                          `KodeProduk`,`NamaProduk`,
                                          `UOM`,`Harga`,
                                          `QtyDO`,`BonusDO`,
                                          `QtyDO`,`BonusDO`,
                                          `ValueBonus`,`DiscCab`,
                                          `ValueDiscCab`,`DiscCabTot`,
                                          `ValueDiscCabTotal`,`DiscPrins1`,
                                          `ValueDiscPrins1`,`DiscPrins2`,
                                          `ValueDiscPrins2`,`DiscPrinsTot`,
                                          `ValueDiscPrinsTotal`,`DiscTotal`,
                                          `ValueDiscTotal`,`Gross`,
                                          `Potongan`,`Value`,
                                          `Ppn`,`LainLain`,
                                          `Total`,`Keterangan1`,
                                          `Keterangan2`,`Barcode`,
                                          `QrCode`,`NoSO`,
                                          `NoDO`,`DiscCabMax`,
                                          `KetDiscCabMax`,`DiscPrinsMax`,
                                          `KetDiscPrinsMax`,`COGS`,
                                          `TotalCOGS`,`BatchNo`,
                                          `ExpDate`,`NoBPB`,
                                          `Prinsipal`,`Prinsipal2`,
                                          `Supplier`,`Supplier2`,
                                          `Pabrik`,`Farmalkes`,0,
                                          '".date('Y-m-d H:i:s')."',
                                          '".$this->user."',`kota`,
                                          `telp`,`tipe_2`,`region`,`acu2`
                                        FROM `trs_delivery_order_sales_detail`
                                        where NoDo = '".$no."' and IFNULL(trs_delivery_order_sales_detail.status_retur,'N')='N' AND trs_delivery_order_sales_detail.`Status` ='Closed'");
        }
        
        return $query;
    }
    public function prosesfixcndn($no = null)
    {   
        $query = $this->db->query("UPDATE trs_faktur header
                                    left join ( SELECT NoDokumen,
                                                SUM(`Jumlah`) as 'jumlah'
                                            FROM trs_faktur_cndn
                                            GROUP BY NoDokumen ) AS cn
                                    ON header.`nofaktur` = cn.NoDokumen
                                    SET header.Potongan = cn.jumlah,
                                        header.VALUE = cn.jumlah,
                                        header.Total = cn.jumlah,
                                        header.Saldo = cn.jumlah
                                    WHERE header.`nofaktur` = '".$no."';");
        return $query;
    }

    public function prosesfixsaldo($no = null)
    {   
        $query = $this->db->query("SELECT a.Cabang,a.NoFaktur,
                                    a.Value AS 'valuefaktur',a.TglFaktur,a.TipeDokumen ,a.Total,a.Saldo,a.saldo_giro,ValuePelunasanx,(a.Total - ValuePelunasanx) AS saldoHt,(CASE WHEN a.tipedokumen IN ('Faktur','DN') AND a.Saldo < 0 THEN a.Saldo
                                     WHEN a.tipedokumen IN ('Retur','CN') AND a.Saldo > 0 THEN a.saldo
                                    ELSE (a.Saldo - (a.Total - ValuePelunasanx)) END ) AS selisih
                                    FROM trs_faktur a
                                    LEFT JOIN (
                                      SELECT NomorFaktur,SUM(ValuePelunasanx) AS ValuePelunasanx FROM (
                                        SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'b' AS ket,
                                        CASE 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          ELSE (IFNULL(ValuePelunasan,0) )
                                          END AS ValuePelunasanx
                                         
                                        FROM trs_pelunasan_detail 
                                           WHERE  IFNULL(trs_pelunasan_detail.Status,'') IN ('Cicilan','Bayar Full','GiroCair','BayarFull','Cicil','Giro Cair')
                                        UNION ALL
                                        SELECT NomorFaktur,TglFaktur,ValuePelunasan,value_transfer,value_pembulatan,materai,'g' AS ket, 
                                        CASE 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Faktur','DN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  ) 
                                          WHEN IFNULL(TipeDokumen,'') IN ('Retur','CN') THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'INV%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'DN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  )
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'RTF%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          WHEN IFNULL(TipeDokumen,'') IN ('') AND IFNULL(NomorFaktur,'') LIKE 'CN%' THEN ((IFNULL(ValuePelunasan,0) + IFNULL(value_transfer,0) + IFNULL(value_pembulatan,0) + IFNULL(materai,0))  * -1)
                                          ELSE (IFNULL(ValuePelunasan,0) )
                                          END AS ValuePelunasanx
                                        FROM trs_pelunasan_giro_detail 
                                           WHERE IFNULL(trs_pelunasan_giro_detail.Status,'') IN ('Open')
                                        )xdat GROUP BY NomorFaktur
                                    )pel ON a.NoFaktur=pel.NomorFaktur
                                     WHERE a.NoFaktur = '".$no."'; ")->row();
        $total = $query->Total;
        $saldo = $query->Saldo;
        $saldogiro = $query->saldo_giro;
        $valuepelunasan = $query->ValuePelunasanx;
        $saldoakhir = $query->saldoHt;
        $selisih = $query->selisih; 

        if(($total == $valuepelunasan) and ($selisih != 0)){
            $cekdih = $this->db->query("SELECT * 
                                    FROM trs_pelunasan_detail 
                                    WHERE nomorfaktur ='".$no."' AND SaldoAkhir = 0")->row();
            if($cekdih->TipePelunasan == 'Cash'){
                $status = "LunasCash";
            }elseif($cekdih->TipePelunasan == 'Transfer'){
                $status = "LunasTransfer";
            }elseif($cekdih->TipePelunasan == 'Giro'){
                $status = "LunasGiro";
            }
            $output = $this->db->query("update trs_faktur 
                                        set status ='".$status."',
                                            Saldo = 0 
                                        where nofaktur = '".$no."'");
        }
        if($total != $valuepelunasan){
            $output = $this->db->query("update trs_faktur 
                                        set Saldo = '".$saldoakhir."',
                                            Status = 'Open' 
                                        where nofaktur = '".$no."'");
        }
        return $output;
    }

    public function prosesfixdokirim($no = null,$status = null)
    {   
        $query =false;
        if($status == "header"){
            $query = $this->db->query("UPDATE trs_delivery_order_sales a
                set Status ='Closed'
                where nodo ='".$no."' and IFNULL(a.status_retur,'N')='N';");
        }elseif($status == "detail"){
            $query = $this->db->query("UPDATE trs_delivery_order_sales_detail a
                set Status ='Closed'
                where nodo ='".$no."' and IFNULL(a.status_retur,'N')='N';");
        }
        // $this->prosesfixfakturheder($no);
        return $query;
    }

    public function prosesfixbpbheader($no = null,$NoDO = null,$status = null)
    {   
        $query =false;
        $query = $this->db->query("UPDATE trs_terima_barang_header
                            JOIN 
                            ( SELECT Cabang,NoDokumen,
                                SUM(a.Gross) AS 'Gross',
                                SUM(a.Potongan) AS 'Potongan',
                                SUM(a.VALUE) AS 'VALUE',
                                SUM(a.PPN) AS 'PPN',
                                SUM(a.Total) AS 'Total'
                              FROM trs_terima_barang_detail a
                              WHERE a.NoDokumen = '".$no."'
                              GROUP BY Cabang,NoDokumen ) AS detail 
                              ON detail.Cabang = trs_terima_barang_header.`Cabang` AND 
                                 detail.NoDokumen = trs_terima_barang_header.`NoDokumen`
                            SET trs_terima_barang_header.`Gross` = detail.Gross,
                            trs_terima_barang_header.`VALUE` = detail.VALUE,
                            trs_terima_barang_header.`Potongan` = detail.Potongan,
                            trs_terima_barang_header.`PPN` = detail.PPN,
                            trs_terima_barang_header.`Total` = detail.Total
                            WHERE trs_terima_barang_header.NoDokumen = '".$no."'
                             ;");
        $query = $this->db->query("UPDATE trs_delivery_order_header
                                JOIN 
                                ( SELECT a.Cabang,a.NoDokumen,
                                    SUM(a.Gross) AS 'Gross',
                                    SUM(a.Potongan) AS 'Potongan',
                                    SUM(a.VALUE) AS 'VALUE',
                                    SUM(a.PPN) AS 'PPN',
                                    SUM(a.Total) AS 'Total'
                                  FROM trs_delivery_order_detail a
                                  WHERE a.NoDokumen = '".$NoDO."'
                                  GROUP BY a.Cabang,a.NoDokumen ) AS detail 
                                  ON detail.Cabang = trs_delivery_order_header.`Cabang` AND 
                                     detail.NoDokumen = trs_delivery_order_header.`NoDokumen`
                                SET trs_delivery_order_header.`Gross` = detail.Gross,
                                trs_delivery_order_header.`VALUE` = detail.VALUE,
                                trs_delivery_order_header.`Potongan` = detail.Potongan,
                                trs_delivery_order_header.`PPN` = detail.PPN,
                                trs_delivery_order_header.`Total` = detail.Total
                                WHERE trs_delivery_order_header.NoDokumen = '".$NoDO."'");
        // $this->prosesfixfakturheder($no);
        return $query;
    }
    public function prosesfixbpbdetail($no = null,$NoDO = null,$status = null)
    {   
        $query =false;
        //update detail bpb
        $query = $this->db->query("UPDATE trs_terima_barang_detail a
                                SET Potongan = ((a.qty * a.`HrgBeli`) * (a.`Disc` /100)) + (a.`Bonus` * a.`HrgBeli`)
                                WHERE a.NoDokumen = '".$no."'");
        $query = $this->db->query("UPDATE trs_terima_barang_detail a
                                SET VALUE = a.Gross - a.Potongan
                                WHERE a.NoDokumen = '".$no."'");
        $query = $this->db->query("UPDATE trs_terima_barang_detail a
                                SET a.`PPN` = (a.`Value` * 10) / 100
                                WHERE a.NoDokumen = '".$no."'");
        $query = $this->db->query("UPDATE trs_terima_barang_detail a
                                SET a.`Total` = a.`Value` + a.`PPN`
                                WHERE a.NoDokumen = '".$no."'");

        //update detail DO Beli
        $query = $this->db->query("UPDATE trs_delivery_order_detail a
                                SET Potongan = ((a.qty * a.`HrgBeli`) * (a.`Disc` /100)) + (a.`Bonus` * a.`HrgBeli`)
                                WHERE a.NoDokumen = '".$NoDO."'");
        $query = $this->db->query("UPDATE trs_delivery_order_detail a
                                SET VALUE = a.Gross - a.Potongan
                                WHERE a.NoDokumen = '".$NoDO."'");
        $query = $this->db->query("UPDATE trs_delivery_order_detail a
                                SET a.`PPN` = (a.`Value` * 10) / 100
                                WHERE a.NoDokumen = '".$NoDO."'");
        $query = $this->db->query("UPDATE trs_delivery_order_detail a
                                SET a.`Total` = a.`Value` + a.`PPN`
                                WHERE a.NoDokumen = '".$NoDO."'");
        $this->prosesfixbpbheader($no,$NoDO);
        return $query;
    }
    public function prosesfixdofaktur($NoDO = null,$status = null)
    {   
        $cek =$this->db->query("select * from trs_delivery_order_sales where NoDO ='".$NoDO."' limit 1")->row();
        $status = $cek->status;
        $TipeDokumen = $cek->TipeDokumen;
        $Acu = $cek->Acu;
        $query ="";
        if($TipeDokumen == 'Retur'){
            $cekdetail = $this->db->query("select * from trs_delivery_order_sales_detail where NoDO ='".$NoDO."' and TipeDokumen = 'Retur'")->num_rows();
            if($cekdetail < 1){
                $query = $this->db->query("INSERT INTO `trs_delivery_order_sales_detail`
                            (`Cabang`,`NoDO`,`TglDO`,`TimeDO`,`noline`,`Pengirim`,`NamaPengirim`,
                                `Pelanggan`,`NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,`NamaTipePelanggan`,
                                `NPWPPelanggan`,`KategoriPelanggan`,Acu,`CaraBayar`,`CashDiskon`,
                                `ValueCashDiskon`,`TOP`,`TglJtoOrder`,`Salesman`,`NamaSalesman`,`Rayon`,
                                `NamaRayon`,`Status`,`TipeDokumen`,`KodeProduk`,`NamaProduk`,`UOM`,`Harga`,
                                `QtySO`,`BonusSO`,`QtyDO`,`BonusDO`,`ValueBonus`,`DiscCab`,`ValueDiscCab`,
                                `DiscCabTot`,`ValueDiscCabTotal`,`DiscPrins1`,`ValueDiscPrins1`,`DiscPrins2`,
                                `ValueDiscPrins2`,`DiscPrinsTot`,`ValueDiscPrinsTotal`,`DiscTotal`,
                                `ValueDiscTotal`,`Gross`,`Potongan`,`Value`,`Ppn`,`LainLain`,`Total`,
                                `Keterangan1`,`Keterangan2`,`Barcode`,`QrCode`,`NoSO`,`NoFaktur`,`DiscCabMax`,
                                `KetDiscCabMax`,`DiscPrinsMax`,`KetDiscPrinsMax`,`COGS`,`TotalCOGS`,
                                `BatchNo`,`ExpDate`,`TipeFaktur`,`NoIDPaket`,`KeteranganTender`,`NoBPB`,
                                `flag_closing`,`picking_list`,`BatchNoDok`,`BatchTglDok`,`Prinsipal`,
                                `Prinsipal2`,`Supplier`,`Supplier2`,`Pabrik`,`Farmalkes`,`QtyDO_awal`,
                                `BonusDO_awal`,`created_at`,`created_by`,`status_retur`)
                                SELECT `Cabang`,'".$NoDO."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."',`noline`,`Pengirim`,`NamaPengirim`,
                                    `Pelanggan`,`NamaPelanggan`,`AlamatKirim`,`TipePelanggan`,`NamaTipePelanggan`,
                                    `NPWPPelanggan`,`KategoriPelanggan`,'".$Acu."',`CaraBayar`,`CashDiskon`,
                                    `ValueCashDiskon`,`TOP`,`TglJtoOrder`,`Salesman`,`NamaSalesman`,`Rayon`,
                                    `NamaRayon`,'".$status."','Retur',`KodeProduk`,`NamaProduk`,`UOM`,
                                    `Harga`,`QtySO`,`BonusSO`,`QtyDO`,`BonusDO`,(`ValueBonus` * -1),`DiscCab`,
                                    (`ValueDiscCab` * -1),`DiscCabTot`,(`ValueDiscCabTotal` * -1),`DiscPrins1`,
                                    (`ValueDiscPrins1` * -1),`DiscPrins2`,(`ValueDiscPrins2` * -1),`DiscPrinsTot`,
                                    (`ValueDiscPrinsTotal` * -1),`DiscTotal`,(`ValueDiscTotal` * -1),(`Gross` * -1),(`Potongan` * -1),
                                    (`Value` * -1),(`Ppn` * -1),(`LainLain` * -1),(`Total` * -1),`Keterangan1`,`Keterangan2`,`Barcode`,
                                    `QrCode`,`NoSO`,`NoFaktur`,`DiscCabMax`,`KetDiscCabMax`,`DiscPrinsMax`,
                                    `KetDiscPrinsMax`,(`COGS` * -1),(`TotalCOGS` * -1),`BatchNo`,`ExpDate`,`TipeFaktur`,
                                    `NoIDPaket`,`KeteranganTender`,`NoBPB`,`flag_closing`,`picking_list`,
                                    `BatchNoDok`,`BatchTglDok`,`Prinsipal`,`Prinsipal2`,`Supplier`,`Supplier2`,
                                    `Pabrik`,`Farmalkes`,`QtyDO_awal`,`BonusDO_awal`,'".date('Y-m-d H:i:s')."','".$this->session->userdata('username')."','Y'
                            FROM `trs_delivery_order_sales_detail`
                                 where NODO ='".$Acu."'"); 
                if($status == 'Retur'){
                    $this->backStok($NoDO,$Acu);
                    $this->db->query("update trs_delivery_order_sales 
                                            set Status = 'Closed', 
                                                status_retur = 'Y'
                                            where NoDO ='".$Acu."'");
                    $this->db->query("update trs_delivery_order_sales_detail 
                                            set Status = 'Closed',
                                                status_retur = 'Y'
                                            where NoDO ='".$Acu."'");
                }
            }   
        }else{
            if($status != 'Batal'){
                $query = $this->db->query("update trs_delivery_order_sales a
                                        join ( select NoDO,
                                                  sum(Gross) as 'Gross',
                                                  sum(Potongan) as 'Potongan',
                                                  sum(Value) as 'Value',
                                                  sum(Ppn) as 'ppn',
                                                  sum(Total) as 'Total'
                                               from trs_delivery_order_sales_detail where NoDO = '".$NoDO."'
                                               group by NoDO) b on 
                                               b.NoDO = a.NoDO
                                        set a.Gross = b.Gross,
                                            a.Potongan = b.Potongan,
                                            a.Value = b.Value,
                                            a.Ppn = b.Ppn,
                                            a.Total = b.Total,       
                                        where a.NoDO ='".$NoDO."' and a.Status != 'Batal'");
            }
        }
        return $query;
    }
    public function setKirimdatadaily($tipe=null,$status=null)
    {   
        /*$datetime = date('Y-m-d H:i:s');
        $dbhost = 'localhost:3306';
        $dbuser = 'sapta';
        $dbpass = 'Sapta254*x';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
        $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode,LOWER(Cabang) AS kCab from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
        while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];$kkcab=$row['kCab'];}
        
        mysqli_close($conn);*/
        
        //ssh2_exec($connsh, 'exit');
        //unset($connsh);
        $this->Model_inventori->send_message_tele("SH SELESAI PROSES DATA CLSOING HARIAN ".$this->cabang." ");      
        if($tipe =='daily'){
            $date  = date('Y-m-d',strtotime("+1 days"));
        }else{
            $date = date('Y-m-d');
        }
            if($status =='ok'){
                // $valid = $this->db->query("update mst_closing set mst_closing.tgl_daily_closing = '".$date."', mst_closing.time_daily_closing = '".$datetime."', mst_closing.flag_trans = 'Y' where Cabang ='".$this->cabang."'");
                
                if($tipe =='daily'){
                    $this->Model_inventori->send_message_tele("CLOSING HARIAN TANGGAL : ".date('Y-m-d').", BERHASIL, CABANG ".$this->cabang." ".$this->tele_sales()." ",-171688895);
                }else{
                    $this->Model_inventori->send_message_tele("CLOSING H+1 TANGGAL : ".date('Y-m-d',strtotime("-1 days")).", BERHASIL, CABANG ".$this->cabang." ".$this->tele_sales()." ",-171688895);
                }

                $this->Model_inventori->send_message_tele("SEND PT, CABANG ".$this->cabang." ");
                $this->Model_inventori->getTelePT();
                $this->Model_inventori->send_message_tele("SEND PU, CABANG ".$this->cabang." ");
                $this->Model_inventori->getTelePU();
                $this->Model_inventori->send_message_tele("SEND STOK, CABANG ".$this->cabang." ");
                $this->Model_inventori->getTeleStok();
                $this->Model_inventori->send_message_tele("SEND KAS/BANK, CABANG ".$this->cabang." ");
                $this->Model_inventori->getTeleKasBank();
            }else{
                // $valid = $this->db->query("update mst_closing set mst_closing.flag_trans = 'N' where Cabang ='".$this->cabang."'");
                $this->Model_inventori->send_message_tele("CLOSING CABANG  : ".$this->cabang.", TGL : ".date('Y-m-d',strtotime("-1 days"))." => GAGAL 
                STOK SELISIH, HARAP CEK STOK..!!!
                HARAP LAKUKAN RE-PROSES STOK DAN CLOSING ULANG
                TRANSAKSI LOCK !!!!!!!!
                JIKA GAGAL KEMBALI SETELAH RE_PROSES STOK DAN CLOSING 
                HUBUNGI IT PUSAT",-171688895);
            }

        $this->Model_inventori->send_message_tele("SH KIRIM DATA CLOSING HARIAN ".$this->cabang." ");
        $this->Model_inventori->send_message_tele("SH PROSES DATA CLSOING HARIAN ".$this->cabang." ");
        
        /*shell_exec("/var/www/html/federateday.sh");
        $connsh = ssh2_connect('119.235.19.138', 22);
        ssh2_auth_password($connsh, 'serversst', 'Sapta777*x');
        ssh2_exec($connsh, '/var/www/html/fedcab/'.$kkcab.'.sh');*/

        // $this->Model_inventori->send_message_tele("CREATE AMS3, CABANG ".$this->cabang." ");
        // $this->Data_AMS3();
        // $this->Model_inventori->send_message_tele("FINISH CREATE AMS3, CABANG ".$this->cabang." ");
        return true;
    }
    public function setKirimdatamonthly($tipe=null,$status=null)
    {   
        $dbhost = 'localhost:3306';
        $dbuser = 'sapta';
        $dbpass = 'Sapta254*x';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
        $qData = mysqli_query($conn,"select LOWER(Kode) AS Kode,LOWER(Cabang) AS kCab from sst.mst_cabang where Cabang='".$this->cabang."' limit 1");
        while($row = mysqli_fetch_assoc($qData)){$kkdcab=$row['Kode'];$kkcab=$row['kCab'];}
        
       
        //ssh2_exec($connsh, 'exit');
        //unset($connsh);
        $this->Model_inventori->send_message_tele("SH SELESAI PROSES DATA CLSOING BULANAN ".$this->cabang." ");     
        mysqli_close($conn);

        $this->Model_inventori->send_message_tele("SEND PT, CABANG ".$this->cabang." ");
        $this->Model_inventori->getTelePT();
        $this->Model_inventori->send_message_tele("SEND PU, CABANG ".$this->cabang." ");
        $this->Model_inventori->getTelePU();
        $this->Model_inventori->send_message_tele("SEND STOK, CABANG ".$this->cabang." ");
        $this->Model_inventori->getTeleStok();
        $this->Model_inventori->send_message_tele("SEND KAS/BANK, CABANG ".$this->cabang." ");
        $this->Model_inventori->getTeleKasBank();

        $this->Model_inventori->send_message_tele("CREATE AMS3, CABANG ".$this->cabang." ");
        
        $this->Model_inventori->send_message_tele("FINISH CREATE AMS3, CABANG ".$this->cabang." ");
        
        $this->Model_inventori->send_message_tele("CLOSING BULANAN TANGGAL : ".date('Y-m-d',strtotime("-1 days")).", BERHASIL, CABANG ".$this->cabang." ".$this->tele_sales('month')." ",-171688895);

        $this->Model_inventori->send_message_tele("SH KIRIM DATA CLOSING BULANAN ".$this->cabang." ");
        shell_exec("/var/www/html/federateday.sh");
        $this->Model_inventori->send_message_tele("SH PROSES DATA CLSOING BULANAN ".$this->cabang." ");
        $connsh = ssh2_connect('119.235.19.138', 22);
        ssh2_auth_password($connsh, 'serversst', 'Sapta777*x');
        ssh2_exec($connsh, '/var/www/html/fedcab/'.$kkcab.'.sh');

        return true;
    }
    public function backStok($NoDO = null, $Acu = NULL)
    {              
        $produk = $this->db->query("select KodeProduk, Harga, QtyDO  from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."' ")->result();
        $detail = $this->db->query("select KodeProduk, QtyDO, BonusDO, Harga, Gross, BatchNo,Value, ExpDate,NoBPB,COGS,TotalCOGS from trs_delivery_order_sales_detail where NoDO = '".$NoDO."' and Acu = '".$Acu."'")->result();
        foreach ($detail as $kunci => $nilai) {
            $summary = 0;
            $product1 = $detail[$kunci]->KodeProduk;
            $harga = $detail[$kunci]->Harga;
            if (!empty($product1)) {
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$product1."'")->row();
                // foreach ($detail as $key => $value) {
                //     $product2 = $detail[$key]->KodeProduk;
                //     if ($product1 == $product2) {
                        $summary = $summary + $detail[$kunci]->QtyDO + $detail[$kunci]->BonusDO;
                        $valuestok = $detail[$kunci]->Value;
                //     }
                // }
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                if ($invsum->num_rows() > 0) {
                    $invsum = $invsum->row();  
                    $UnitStok = $invsum->UnitStok + $summary;
                    if($detail[$kunci]->TotalCOGS < 0){
                        $valuestok = $invsum->ValueStok - ($detail[$kunci]->TotalCOGS);
                    }else{
                        $valuestok = $invsum->ValueStok + ($detail[$kunci]->TotalCOGS);
                    }
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }
                    $UnitCOGS = $valuestok / $UnitStok;
                    $this->db->query("update trs_invsum set UnitStok = ".$UnitStok.", ValueStok = ".$valuestok.",UnitCOGS ='".$UnitCOGS."' where KodeProduk='".$product1."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1");
                }
                // else{
                //     $this->db->query("insert into trs_invsum (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, Gudang, indeks, UnitCOGS, HNA) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$product1."', '".addslashes($prod->Produk)."', '".$summary."', '".$valuestok."', 'Baik', '0.000', '".$harga."', '0.000')");   
                // }
            }
        }

        // save inventori history
        foreach ($detail as $key => $value) {   
            $produk = $detail[$key]->KodeProduk;        
            if (!empty($produk)) {
                $prod = $this->db->query("select Pabrik, Produk from mst_produk where Kode_Produk = '".$produk."'")->row();
                $valuestok = $detail[$key]->TotalCOGS;
                $UnitStok = $detail[$key]->QtyDO + $detail[$key]->BonusDO;

                $this->db->query("insert into trs_invhis (Tahun, Cabang, Pabrik, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, Tipe, NoDokumen, Keterangan) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', 'DO', '".$NoDO."', 'update')");
            }
        }

        // save inventori detail
        foreach ($detail as $key => $value) { 
            $produk = $detail[$key]->KodeProduk;  
            if (!empty($produk)) {
                $invsum = $this->db->query("select * from trs_invsum where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and Gudang='Baik' and Tahun = '".date('Y')."' limit 1")->row();
                $invdet = $this->db->query("select * from trs_invdet where KodeProduk='".$produk."' and Cabang='".$this->cabang."' and BatchNo='".$detail[$key]->BatchNo."' and Tahun = '".date('Y')."' and Gudang='Baik' and NoDokumen = '".$NoDO."' limit 1");
                $prod = $this->db->query("select * from mst_produk where Kode_Produk = '".$produk."'")->row();
                $dt = $invdet->row();
                if ($invdet->num_rows() > 0) {
                    $UnitStok  = $dt->UnitStok + $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->set("UnitStok", $UnitStok);
                    $this->db->set("ValueStok", $valuestok);
                    $this->db->set("ModifiedTime", date('Y-m-d H:i:s'));            
                    $this->db->set("ModifiedUser", $this->session->userdata('username'));
                    $this->db->where("KodeProduk", $produk);
                    $this->db->where("Tahun", date('Y'));
                    $this->db->where("Cabang", $this->cabang);
                    $this->db->where("NoDokumen", $NoDO);
                    $this->db->where("BatchNo", $detail[$key]->BatchNo);
                    $this->db->where("Gudang", 'Baik');
                    $valid = $this->db->update('trs_invdet');
                }else{
                    $UnitStok  = $detail[$key]->QtyDO + $detail[$key]->BonusDO;
                    if($detail[$key]->COGS < 0){
                        $valuestok = ($detail[$key]->TotalCOGS *-1);
                        $UnitCOGS =  $detail[$key]->COGS * -1; 
                    }else{
                        $valuestok = $detail[$key]->TotalCOGS;
                        $UnitCOGS =  $detail[$key]->COGS; 
                    }  
                    if($UnitStok == 0){
                        $valuestok = 0;
                    }    
                    $this->db->query("insert into trs_invdet (Tahun, Cabang, Pabrik,KodePrinsipal,NamaPrinsipal, KodeProduk, NamaProduk, UnitStok, ValueStok, BatchNo, ExpDate, Gudang, UnitCOGS,NoDokumen) values ('".date('Y')."', '".$this->cabang."', '".$prod->Pabrik."','".$prod->Prinsipal."','".$prod->Prinsipal."', '".$produk."', '".addslashes($prod->Produk)."', '".$UnitStok."', '".$valuestok."', '".$detail[$key]->BatchNo."', '".$detail[$key]->ExpDate."', 'Baik', '".$UnitCOGS."','".$NoDO."')");
                }
                    

                $invdet = $this->db->query("select KodeProduk,sum(ifnull(ValueStok,0)) as 'sumval',sum(ifnull(UnitStok,0)) as 'sumunit'
                                            from trs_invdet where KodeProduk = '".$produk."' and  Gudang = 'Baik' and Tahun = '".date('Y')."' and UnitStok != 0 and Cabang='".$this->cabang."'
                                            group by KodeProduk limit 1");
                    if($invdet->num_rows() <= 0){
                        $this->db->set("ValueStok", 0);
                        $this->db->where("KodeProduk", $produk);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }else{
                        $invdet = $invdet->row();
                        $this->db->set("ValueStok", $invdet->sumval);
                        // $this->db->set("UnitCOGS", ($invdet->sumval/$invdet->sumunit));
                        $this->db->where("KodeProduk", $produk);
                        $this->db->where("Gudang", 'Baik');
                        $this->db->where("Tahun", date('Y'));
                        $this->db->where("Cabang",$this->cabang);
                        $valid = $this->db->update('trs_invsum');
                    }
            }
        }
    }
    public function setcreatetablepu($tipe=null,$status=null)
    {   
        if($tipe == 'monthly'){
            log_message("error","bulanan");
            $date  = date('Y-m-d',strtotime("-10 days"));
        }else{
            log_message("error","harian");
            $date  = date('Y-m-d');
        }
        // $date  = date('Y-m-d');
        $bulan = date('Ym',strtotime($date));
        if($status == 'ok'){
            $valid = $this->db->query("CREATE TABLE 
            IF NOT EXISTS temp_pu_$bulan(  
                  `Cabang` VARCHAR(50) NOT NULL,
                  `Area` VARCHAR(100),
                  `Index` VARCHAR(100),
                  `Combo` VARCHAR(100),
                  `KdDokjdi` VARCHAR(100),
                  `NoDokjdi` VARCHAR(100) NOT NULL,
                  `TglFaktur` DATE,
                  `Top` VARCHAR(50),
                  `NoDO` VARCHAR(100),
                  `NoDIH` VARCHAR(100),
                  `TglDIH` DATE ,
                  `StatusInkaso` VARCHAR(50),
                  `Umur` VARCHAR(50),
                  `Kategori` VARCHAR(100),
                  `Kategori2` VARCHAR(100),
                  `KodeSales` VARCHAR(100),
                  `Nama_Lang` VARCHAR(100),
                  `Alamat_Lang` VARCHAR(100),
                  `NilDok` DECIMAL(15,2),
                  `Saldo` DECIMAL(15,2),
                  `Kategori3` VARCHAR(100),
                  `Judul` VARCHAR(100),
                  `Spesial` VARCHAR(100),
                  `Status` VARCHAR(50),
                  `CaraBayar` VARCHAR(100),
                  `Keterangan_Jatuh_Tempo` VARCHAR(100),
                  `Acu2` VARCHAR(100),
                  `Alasan` VARCHAR(100),
                  `region` VARCHAR(50),
                  `TglClosing` DATETIME,
                  PRIMARY KEY (`Cabang`, `NoDokjdi`)
                ) ENGINE=INNODB CHARSET=latin1 COLLATE=latin1_swedish_ci;
                "); 
            if($valid){
                $cek =$this->db->query("select * from temp_pu_$bulan limit 10")->num_rows();
                if($cek > 0){
                    $valid = $this->db->query("TRUNCATE TABLE temp_pu_$bulan");
                }
            } 
            if($valid){
                $valid = $this->db->query("INSERT INTO temp_pu_$bulan (
                      `Cabang`,`Area`,`Index`,`Combo`,
                      `KdDokjdi`,`NoDokjdi`,`TglFaktur`,
                      `Top`,`NoDO`,`NoDIH`,`TglDIH`,
                      `StatusInkaso`,`Umur`,`Kategori`,
                      `Kategori2`,`KodeSales`,`Nama_Lang`,
                      `Alamat_Lang`,`NilDok`,`Saldo`,`Kategori3`,
                      `Judul`,`Spesial`,`Status`,`CaraBayar`,
                      `Keterangan_Jatuh_Tempo`,`Acu2`,
                      `Alasan`,`region`,`TglClosing`
                    )
                    SELECT `CABANG`,`AREA`,`INDEX`,
                           `COMBO`,`KDDOKJDI`,`NODOKJDI`,
                           `TGLFAK`,`TOP`,`NODO`,
                           `nodih`,`tgldih`,`StatusInkaso`,
                           `UMUR`,`KATEGORI`,`KATEGORI2`,
                           `KODESALES`,`NAMA_LANG`,`ALAMAT_LANG`,
                           `NILDOK`,`SALDO`,`KATEGORI3`,`JUDUL`,
                           `SPESIAL`,`STATUS`,`CARA_BAYAR`,
                           `KETERANGAN_JATUH_TEMPO`,
                           `ACU2`,`ALASAN`,`REGION`,'".date('Y-m-d H:i:s')."'
                    FROM (
                       SELECT '' AS REGION,
                              Cabang AS CABANG,
                              Cabang AS 'AREA',
                              Pelanggan AS 'INDEX',
                              CONCAT(Pelanggan,NoFaktur,TglFaktur) AS COMBO,
                              TipeDokumen AS KDDOKJDI,
                              NoFaktur AS NODOKJDI,
                              TglFaktur AS TGLFAK,
                              TOP,
                              NODO,
                              nodih,
                              tgldih,
                              StatusInkaso,
                              CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END AS UMUR,
                              CASE 
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 0 AND 30 THEN '0-30'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 31 AND 45 THEN '31-45'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 46 AND 60 THEN '46-60'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 61 AND 90 THEN '61-90'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 91 AND 150 THEN '91-150'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                              > 150 THEN '>150'
                                  ELSE DATEDIFF(DATE(NOW()),TglFaktur)
                              END AS KATEGORI,
                              CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN
                                 CASE WHEN Salesman='RG2' THEN 'RAGU-RAGU' 
                                      WHEN CaraBayar LIKE '%RPO%' THEN 
                                      CASE 
                                          WHEN (CaraBayar = 'RPO' OR CaraBayar LIKE '%60%') THEN 
                                            CASE 
                                    WHEN 
                                    (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                     >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                WHEN CaraBayar LIKE '%75%' THEN 
                                                   CASE WHEN 
                                                      (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                    >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%90%' THEN 
                                                    CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                    >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%120%' THEN 
                                                    CASE WHEN 
                                                    (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%150%' THEN 
                                                    CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                     WHEN CaraBayar LIKE '%180%' THEN 
                                                     CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                     ELSE 'CEK CARA BAYAR' 
                                                     END
                                      WHEN CaraBayar LIKE '%Cash%' THEN 
                                             CASE WHEN 
                                             (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                              >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                      WHEN CaraBayar LIKE '%Kredit%' THEN 
                                             CASE WHEN 
                                               (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                               >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                      ELSE IF( (IFNULL(saldo,0) + IFNULL(saldo_giro,0))= 0 , 'LUNAS' , UCASE(TipeDokumen)) 
                                      END 
                          ELSE 'LUNAS' END  AS KATEGORI2,
                              Salesman AS KODESALES,
                              NamaPelanggan AS NAMA_LANG,
                              AlamatFaktur AS ALAMAT_LANG,
                              Total AS NILDOK,
                              (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) AS SALDO,
                              CASE 
                              WHEN TipePelanggan IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                              WHEN TipePelanggan IN ('DK','DPKM') THEN 'DINKES'
                              WHEN TipePelanggan IN ('IN') THEN 'INSTANSI'
                              WHEN TipePelanggan IN ('RSSA') THEN 'RS SWASTA'
                              WHEN TipePelanggan IN ('RSUD') THEN 'RSUD'
                              ELSE 'CEK TIPE PELANGGAN'
                              END AS 'KATEGORI3',
                              '' AS JUDUL,
                              '' AS SPESIAL,
                              STATUS AS 'STATUS',  
                              IFNULL(CaraBayar,'') AS CARA_BAYAR,
                              CASE WHEN Saldo !=0 
                              THEN
                                CASE WHEN
                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                              ELSE 'LUNAS' END AS KETERANGAN_JATUH_TEMPO,
                              IFNULL(trs_faktur.acu2,'') AS ACU2,
                              alasan_jto AS ALASAN
                         FROM trs_faktur 
                        WHERE IFNULL(STATUS,'') IN ('Giro','Open','OpenDIH')  )a   
                        ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC;");
            }
            $this->db3 = $this->load->database('mitra', TRUE);
            if ($this->db3->conn_id == TRUE) {
                $pusat = $this->db3->query("CREATE TABLE 
                IF NOT EXISTS sstinsentif.temp_pu_$bulan(  
                      `Cabang` VARCHAR(50) NOT NULL,
                      `Area` VARCHAR(100),
                      `Index` VARCHAR(100),
                      `Combo` VARCHAR(100),
                      `KdDokjdi` VARCHAR(100),
                      `NoDokjdi` VARCHAR(100) NOT NULL,
                      `TglFaktur` DATE,
                      `Top` VARCHAR(50),
                      `NoDO` VARCHAR(100),
                      `NoDIH` VARCHAR(100),
                      `TglDIH` DATE ,
                      `StatusInkaso` VARCHAR(50),
                      `Umur` VARCHAR(50),
                      `Kategori` VARCHAR(100),
                      `Kategori2` VARCHAR(100),
                      `KodeSales` VARCHAR(100),
                      `Nama_Lang` VARCHAR(100),
                      `Alamat_Lang` VARCHAR(100),
                      `NilDok` DECIMAL(15,2),
                      `Saldo` DECIMAL(15,2),
                      `Kategori3` VARCHAR(100),
                      `Judul` VARCHAR(100),
                      `Spesial` VARCHAR(100),
                      `Status` VARCHAR(50),
                      `CaraBayar` VARCHAR(100),
                      `Keterangan_Jatuh_Tempo` VARCHAR(100),
                      `Acu2` VARCHAR(100),
                      `Alasan` VARCHAR(100),
                      `region` VARCHAR(50),
                      `TglClosing` DATETIME,
                      PRIMARY KEY (`Cabang`, `NoDokjdi`)
                    ) ENGINE=INNODB CHARSET=latin1 COLLATE=latin1_swedish_ci;
                    ");
                if($pusat){
                    $cek =$this->db3->query("select * from sstinsentif.temp_pu_$bulan where Cabang ='".$this->cabang."' limit 10")->num_rows();
                    if($cek > 0){
                        $pusat = $this->db3->query("delete from  sstinsentif.temp_pu_$bulan where Cabang ='".$this->cabang."'");
                    }
                    $getpu =$this->db->query("SELECT `CABANG`,`AREA`,`INDEX`,
                           `COMBO`,`KDDOKJDI`,`NODOKJDI`,
                           `TGLFAK`,`TOP`,`NODO`,
                           `nodih`,`tgldih`,`StatusInkaso`,
                           `UMUR`,`KATEGORI`,`KATEGORI2`,
                           `KODESALES`,`NAMA_LANG`,`ALAMAT_LANG`,
                           `NILDOK`,`SALDO`,`KATEGORI3`,`JUDUL`,
                           `SPESIAL`,`STATUS`,`CARA_BAYAR`,
                           `KETERANGAN_JATUH_TEMPO`,
                           `ACU2`,`ALASAN`,`REGION`

                    FROM (
                       SELECT '' AS REGION,
                              Cabang AS CABANG,
                              Cabang AS 'AREA',
                              Pelanggan AS 'INDEX',
                              CONCAT(Pelanggan,NoFaktur,TglFaktur) AS COMBO,
                              TipeDokumen AS KDDOKJDI,
                              NoFaktur AS NODOKJDI,
                              TglFaktur AS TGLFAK,
                              TOP,
                              NODO,
                              nodih,
                              tgldih,
                              StatusInkaso,
                              CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END AS UMUR,
                              CASE 
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 0 AND 30 THEN '0-30'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 31 AND 45 THEN '31-45'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 46 AND 60 THEN '46-60'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 61 AND 90 THEN '61-90'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  BETWEEN 91 AND 150 THEN '91-150'
                                  WHEN (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                              > 150 THEN '>150'
                                  ELSE DATEDIFF(DATE(NOW()),TglFaktur)
                              END AS KATEGORI,
                              CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN
                                 CASE WHEN Salesman='RG2' THEN 'RAGU-RAGU' 
                                      WHEN CaraBayar LIKE '%RPO%' THEN 
                                      CASE 
                                          WHEN (CaraBayar = 'RPO' OR CaraBayar LIKE '%60%') THEN 
                                            CASE 
                                    WHEN 
                                    (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                     >= 60 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                WHEN CaraBayar LIKE '%75%' THEN 
                                                   CASE WHEN 
                                                      (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                    >= 75 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%90%' THEN 
                                                    CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                    >= 90 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%120%' THEN 
                                                    CASE WHEN 
                                                    (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 120 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                    WHEN CaraBayar LIKE '%150%' THEN 
                                                    CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 150 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END
                                                     WHEN CaraBayar LIKE '%180%' THEN 
                                                     CASE WHEN 
                                                       (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                                       >= 180 THEN 'RPO JATUH TEMPO' ELSE 'RPO BELUM JATUH TEMPO' END          
                                                     ELSE 'CEK CARA BAYAR' 
                                                     END
                                      WHEN CaraBayar LIKE '%Cash%' THEN 
                                             CASE WHEN 
                                             (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                              >= 7 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                      WHEN CaraBayar LIKE '%Kredit%' THEN 
                                             CASE WHEN 
                                               (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                               >= 21 THEN 'JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                                      ELSE IF( (IFNULL(saldo,0) + IFNULL(saldo_giro,0))= 0 , 'LUNAS' , UCASE(TipeDokumen)) 
                                      END 
                          ELSE 'LUNAS' END  AS KATEGORI2,
                              Salesman AS KODESALES,
                              NamaPelanggan AS NAMA_LANG,
                              AlamatFaktur AS ALAMAT_LANG,
                              Total AS NILDOK,
                              (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) AS SALDO,
                              CASE 
                              WHEN TipePelanggan IN ('AP','APGR','APMT','APSG','HOR','KL','KL1','KLAL','KLAD','KLAB','PBF','PBAK','MNAS','MMKT','MLOK','TK','TJ','XTO','TKOS') THEN 'REGULER'
                              WHEN TipePelanggan IN ('DK','DPKM') THEN 'DINKES'
                              WHEN TipePelanggan IN ('IN') THEN 'INSTANSI'
                              WHEN TipePelanggan IN ('RSSA') THEN 'RS SWASTA'
                              WHEN TipePelanggan IN ('RSUD') THEN 'RSUD'
                              ELSE 'CEK TIPE PELANGGAN'
                              END AS 'KATEGORI3',
                              '' AS JUDUL,
                              '' AS SPESIAL,
                              STATUS AS 'STATUS',  
                              IFNULL(CaraBayar,'') AS CARA_BAYAR,
                              CASE WHEN Saldo !=0 
                              THEN
                                CASE WHEN
                                (CASE WHEN (IFNULL(saldo,0) + IFNULL(saldo_giro,0)) != 0 THEN DATEDIFF(DATE(NOW()),TglFaktur) ELSE DATEDIFF(IFNULL(TglPelunasan,DATE(NOW())),TglFaktur) END)
                                  > TOP THEN 'SUDAH JATUH TEMPO' ELSE 'BELUM JATUH TEMPO' END
                              ELSE 'LUNAS' END AS KETERANGAN_JATUH_TEMPO,
                              IFNULL(trs_faktur.acu2,'') AS ACU2,
                              alasan_jto AS ALASAN
                         FROM trs_faktur 
                        WHERE IFNULL(STATUS,'') IN ('Giro','Open','OpenDIH')  )a   
                        ORDER BY UMUR DESC,TGLFAK,NODOKJDI ASC;")->result();
                    $vgetpu="";
                    foreach ($getpu as $key ) {
                        $vgetpu = $vgetpu."('".$key->CABANG."','".$key->AREA."','".$key->INDEX."','".$key->COMBO."','".$key->KDDOKJDI."','".$key->NODOKJDI."','".$key->TGLFAK."','".$key->TOP."','".$key->NODO."','".$key->nodih."','".$key->tgldih."','".$key->StatusInkaso."','".$key->UMUR."','".$key->KATEGORI."','".$key->KATEGORI2."','".$key->KODESALES."','".preg_replace('/[^A-Za-z0-9\-]/', '', $key->NAMA_LANG)."','".preg_replace('/[^A-Za-z0-9\-]/', '',$key->ALAMAT_LANG)."','".$key->NILDOK."','".$key->SALDO."','".$key->KATEGORI3."','".$key->JUDUL."','".$key->SPESIAL."','".$key->STATUS."','".$key->CARA_BAYAR."','".$key->KETERANGAN_JATUH_TEMPO."','".$key->ACU2."','".$key->ALASAN."','".$key->REGION."','".date('Y-m-d H:i:s')."'),";
                    }
                    $var_dump=rtrim($vgetpu,",");
                    // log_message("error",print_r($var_dump,true));
                    $querypu = "INSERT INTO sstinsentif.temp_pu_$bulan (
                      `Cabang`,`Area`,`Index`,`Combo`,
                      `KdDokjdi`,`NoDokjdi`,`TglFaktur`,
                      `Top`,`NoDO`,`NoDIH`,`TglDIH`,
                      `StatusInkaso`,`Umur`,`Kategori`,
                      `Kategori2`,`KodeSales`,`Nama_Lang`,
                      `Alamat_Lang`,`NilDok`,`Saldo`,`Kategori3`,
                      `Judul`,`Spesial`,`Status`,`CaraBayar`,
                      `Keterangan_Jatuh_Tempo`,`Acu2`,
                      `Alasan`,`region`,`TglClosing`
                    )VALUES ".$var_dump;
                    $pusat = $this->db3->query($querypu);
                    
                } 
            }
        }else{
            $this->db3 = $this->load->database('mitra', TRUE);
            if ($this->db3->conn_id == TRUE) {
                $pusat = $this->db3->query("CREATE TABLE 
                IF NOT EXISTS sstinsentif.temp_pu_$bulan(  
                      `Cabang` VARCHAR(50) NOT NULL,
                      `Area` VARCHAR(100),
                      `Index` VARCHAR(100),
                      `Combo` VARCHAR(100),
                      `KdDokjdi` VARCHAR(100),
                      `NoDokjdi` VARCHAR(100) NOT NULL,
                      `TglFaktur` DATE,
                      `Top` VARCHAR(50),
                      `NoDO` VARCHAR(100),
                      `NoDIH` VARCHAR(100),
                      `TglDIH` DATE ,
                      `StatusInkaso` VARCHAR(50),
                      `Umur` VARCHAR(50),
                      `Kategori` VARCHAR(100),
                      `Kategori2` VARCHAR(100),
                      `KodeSales` VARCHAR(100),
                      `Nama_Lang` VARCHAR(100),
                      `Alamat_Lang` VARCHAR(100),
                      `NilDok` DECIMAL(15,2),
                      `Saldo` DECIMAL(15,2),
                      `Kategori3` VARCHAR(100),
                      `Judul` VARCHAR(100),
                      `Spesial` VARCHAR(100),
                      `Status` VARCHAR(50),
                      `CaraBayar` VARCHAR(100),
                      `Keterangan_Jatuh_Tempo` VARCHAR(100),
                      `Acu2` VARCHAR(100),
                      `Alasan` VARCHAR(100),
                      `region` VARCHAR(50),
                      `TglClosing` DATETIME,
                      PRIMARY KEY (`Cabang`, `NoDokjdi`)
                    ) ENGINE=INNODB CHARSET=latin1 COLLATE=latin1_swedish_ci;
                    ");
                if($pusat){
                    $cek =$this->db3->query("select * from sstinsentif.temp_pu_$bulan where Cabang ='".$this->cabang."' limit 10")->num_rows();
                    if($cek > 0){
                        $pusat = $this->db3->query("delete from  sstinsentif.temp_pu_$bulan where Cabang ='".$this->cabang."'");
                    }
                    $getpu =$this->db->query("SELECT `Cabang`,`Area`,`Index`,
                                   `Combo`,`KdDokjdi`,`NoDokjdi`,
                                   `TglFaktur`,`Top`,`NoDO`,
                                   `NoDIH`,`TglDIH`,`StatusInkaso`,
                                   `Umur`,`Kategori`,`Kategori2`,
                                   `KodeSales`,`Nama_Lang`,`Alamat_Lang`,
                                   `NilDok`,`Saldo`,`Kategori3`,`Judul`,
                                   `Spesial`,`Status`,`CaraBayar`,
                                   `Keterangan_Jatuh_Tempo`,
                                   `Acu2`,`Alasan`,`region`,`TglClosing`
                            FROM temp_pu_$bulan")->result();
                    $vgetpu="";
                    foreach ($getpu as $key ) {
                        $vgetpu = $vgetpu."('".$key->Cabang."','".$key->Area."','".$key->Index."','".$key->Combo."','".$key->KdDokjdi."','".$key->NoDokjdi."','".$key->TglFaktur."','".$key->Top."','".$key->NoDO."','".$key->NoDIH."','".$key->TglDIH."','".$key->StatusInkaso."','".$key->Umur."','".$key->Kategori."','".$key->Kategori2."','".$key->KodeSales."','".preg_replace('/[^A-Za-z0-9\-]/', '', $key->Nama_Lang)."','".preg_replace('/[^A-Za-z0-9\-]/', '',$key->Alamat_Lang)."','".$key->NilDok."','".$key->Saldo."','".$key->Kategori3."','".$key->Judul."','".$key->Spesial."','".$key->Status."','".$key->CaraBayar."','".$key->Keterangan_Jatuh_Tempo."','".$key->Acu2."','".$key->Alasan."','".$key->region."','".$key->TglClosing."'),";
                    }
                    $var_dump=rtrim($vgetpu,",");
                    // log_message("error",print_r($var_dump,true));
                    $querypu = "INSERT INTO sstinsentif.temp_pu_$bulan (
                      `Cabang`,`Area`,`Index`,`Combo`,
                      `KdDokjdi`,`NoDokjdi`,`TglFaktur`,
                      `Top`,`NoDO`,`NoDIH`,`TglDIH`,
                      `StatusInkaso`,`Umur`,`Kategori`,
                      `Kategori2`,`KodeSales`,`Nama_Lang`,
                      `Alamat_Lang`,`NilDok`,`Saldo`,`Kategori3`,
                      `Judul`,`Spesial`,`Status`,`CaraBayar`,
                      `Keterangan_Jatuh_Tempo`,`Acu2`,
                      `Alasan`,`region`,`TglClosing`
                    )VALUES ".$var_dump;
                    $pusat = $this->db3->query($querypu);
                    
                } 
            }
            $valid = true;
        }
        return $valid;
    }

    public function AppAdjustment(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("select count(ifnull(no_koreksi,'')) as 'NoKoreksi' from trs_mutasi_koreksi where ifnull(Status,'') = 'open' and tanggal between '".$satubulan_awal."' and '".$satubulan."' ")->result();

        return $query;
    }
    public function AppRelokasi(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("select count(ifnull(No_Relokasi,'')) as 'NoRelokasi' from trs_relokasi_kirim_header where ifnull(Status_kiriman,'') = 'Open' and Tgl_kirim between '".$satubulan_awal."' and '".$satubulan."' ")->result();

        return $query;
    }
    public function AppReturBeli(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("select count(ifnull(No_Usulan,'')) as 'UsulanRetur' from trs_usulan_retur_beli_header where (ifnull(App_BM_Status,'') = '' or ifnull(App_BM_Status,'') = 'N') and Tanggal between '".$satubulan_awal."' and '".$satubulan."' ")->result();

        return $query;
    }
    public function AppRDO(){
        $cektglstok = $this->Model_main->cek_tglstoktrans();
        if($cektglstok == 1){
            $satubulan_awal = date('Y-m-01');
            $satubulan = date('Y-m-t');
        }else if($cektglstok == 0){
            $satu = date('Y-m-d',strtotime("-10 days"));
            $satubulan_awal = date('Y-m-01', strtotime($satu));
            $satubulan = date('Y-m-t', strtotime($satu));
        }
        $query = $this->db->query("select count(ifnull(NoDO,'')) as 'NoDO' from trs_delivery_order_sales where ifnull(Status,'') = 'Usulan Retur' and tipedokumen='Retur' and TglDO between '".$satubulan_awal."' and '".$satubulan."' ")->result();

        return $query;
    }

    function cek_sawal_closing_bulanan(){
        $cabang = $this->cabang; 

        $hari = date('Y-m-d',strtotime("-10 days"));
        $first_date = date('Y-m-01', strtotime($hari));
        $end_date = date('Y-m-t', strtotime($hari));

        $getyear = date('Y',strtotime($hari));
        $getmonth = date('m',strtotime($hari));

        if ($getmonth == '01') {
            $nextmonth = '01'; 
        }else{
            $nextmonth = $getmonth + 1; 
        }

        if (strlen($nextmonth) == 1) {
            $nextmonth = "0".$nextmonth;
        }

        $data = $this->db->query("SELECT * FROM (
                                    SELECT  DISTINCT trs_invsum.KodeProduk AS Kode_Produk,
                                         trs_invsum.NamaProduk AS Produk,
                                         IFNULL(s_awal.UnitStok,0)UnitStok,
                                         IFNULL(s_awalnext.saldo_awal,0) AS 'SaldoAwal',
                                        (IFNULL(s_awal.saldo_awal,0) + IFNULL(terima.qty,0) + IFNULL(relokasi_terima.qty,0)+ IFNULL(koreksi_plus.qty,0) + IFNULL(retur.qty,0) + IFNULL(returDelivery.qty,0) - IFNULL(Delivery.qty,0) - IFNULL(relokasi_kirim.qty,0) - IFNULL(koreksi_min.qty,0) - IFNULL(returbeli.qty,0)) AS 'saldo_akhir'
                                   FROM trs_invsum LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '$nextmonth' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal' 
                                      FROM trs_invsum 
                                     WHERE Cabang = '$cabang' AND Tahun ='$getyear'
                                     GROUP BY KodeProduk ) AS s_awalnext ON s_awalnext.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk,
                                          (CASE '$getmonth' 
                                           WHEN '01' THEN IFNULL(SUM(SAwal01),0)
                                           WHEN '02' THEN IFNULL(SUM(SAwal02),0)
                                           WHEN '03' THEN IFNULL(SUM(SAwal03),0)
                                           WHEN '04' THEN IFNULL(SUM(SAwal04),0)
                                           WHEN '05' THEN IFNULL(SUM(SAwal05),0)
                                           WHEN '06' THEN IFNULL(SUM(SAwal06),0)
                                           WHEN '07' THEN IFNULL(SUM(SAwal07),0)
                                           WHEN '08' THEN IFNULL(SUM(SAwal08),0)
                                           WHEN '09' THEN IFNULL(SUM(SAwal09),0)
                                           WHEN '10' THEN IFNULL(SUM(SAwal10),0)
                                           WHEN '11' THEN IFNULL(SUM(SAwal11),0)
                                           WHEN '12' THEN IFNULL(SUM(SAwal12),0)
                                           ELSE 0 END) AS 'saldo_awal',
                                           SUM(UnitStok) UnitStok 
                                      FROM trs_invsum 
                                     WHERE Cabang = '$cabang' AND Tahun ='$getyear'
                                     GROUP BY KodeProduk ) AS s_awal ON s_awal.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          SUM(IFNULL(Qty,'') + IFNULL(Bonus,'')) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '$cabang' AND
                                          TglDokumen BETWEEN '$first_date' AND '$end_date' AND 
                                          STATUS NOT IN ('pending','Batal') AND 
                                          IFNULL(tipe,'') NOT IN  ('BKB','Tolakan')
                                    GROUP BY Produk) AS terima ON terima.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          ((SUM(IFNULL(Qty,'') + IFNULL(Bonus,'')))*-1) AS 'qty'      
                                    FROM trs_terima_barang_detail
                                   WHERE  Cabang = '$cabang' AND
                                          TglDokumen BETWEEN '$first_date' AND '$end_date' AND 
                                          IFNULL(Tipe,'') ='BKB'
                                    GROUP BY Produk) AS returbeli ON returbeli.Produk =trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                  (SELECT  Produk,
                                          IFNULL(SUM(qty),0) AS 'qty' FROM (
                                      SELECT  Produk,
                                            IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_terima_detail
                                     WHERE  Cabang = '$cabang' AND 
                                            STATUS = 'Terima' AND
                                            Tgl_terima BETWEEN '$first_date' AND '$end_date'
                                      GROUP BY Produk

                                      UNION ALL

                                      SELECT  Produk,
                                            IFNULL(SUM(Qty+Bonus),0) AS 'qty'      
                                      FROM trs_relokasi_kirim_detail
                                     WHERE  Cabang = '$cabang' AND 
                                            STATUS = 'Reject' AND
                                            tgl_reject BETWEEN '$first_date' AND '$end_date'
                                      GROUP BY Produk
                                    )zz GROUP BY zz.Produk

                                    ) AS relokasi_terima ON relokasi_terima.Produk = trs_invsum.KodeProduk 
                                  LEFT OUTER JOIN
                                   ( SELECT produk,
                                           IFNULL(SUM(Qty),0) AS 'qty'    
                                     FROM  trs_mutasi_koreksi 
                                   WHERE   qty > 0 AND Cabang = '$cabang' AND
                                   CASE WHEN `Status` = 'Open' THEN
                                      tanggal
                                   ELSE
                                      IFNULL(DATE(tgl_approve),tanggal)
                                   END BETWEEN '$first_date' AND '$end_date' AND STATUS IN ('Approve','Open')
                                   GROUP BY produk ) AS koreksi_plus ON koreksi_plus.Produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                   (SELECT KodeProduk, 
                                          IFNULL(SUM(QtyFaktur+BonusFaktur),0) AS 'qty'    
                                     FROM trs_faktur_detail 
                                    WHERE TipeDokumen ='Retur' AND 
                                          IFNULL(STATUS,'') != 'Batal' AND
                                          Cabang = '$cabang' AND
                                          TglFaktur BETWEEN '$first_date' AND '$end_date' 
                                    GROUP BY KodeProduk) AS retur ON retur.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '$cabang' AND STATUS != 'Batal' AND tipedokumen ='DO' 
                                    AND TglDO BETWEEN '$first_date' AND '$end_date'
                                    GROUP BY KodeProduk
                                  ) AS Delivery ON Delivery.KodeProduk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                    (
                                   SELECT  KodeProduk,
                                           IFNULL(SUM(QtyDO+BonusDO),0) AS 'qty'
                                  FROM trs_delivery_order_sales_detail 
                                  WHERE Cabang = '$cabang' AND STATUS = 'Retur' AND tipedokumen ='Retur' 
                                    AND TglDO BETWEEN '$first_date' AND '$end_date'
                                    GROUP BY KodeProduk
                                  ) AS returDelivery ON returDelivery.KodeProduk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                  (
                                    SELECT Produk,
                                          IFNULL(SUM(Qty+Bonus),0) AS 'qty'
                                  FROM  trs_relokasi_kirim_detail
                                  WHERE trs_relokasi_kirim_detail.Status IN ('Kirim','Reject','RejectPusat','RejectCabPenerima') AND
                                        trs_relokasi_kirim_detail.Cabang_Pengirim = '$cabang' AND 
                                        CASE WHEN tgl_app IS NULL THEN trs_relokasi_kirim_detail.Tgl_kirim ELSE trs_relokasi_kirim_detail.tgl_app END
                                         BETWEEN '$first_date' AND '$end_date'
                                        GROUP BY Produk

                                

                                  ) AS relokasi_kirim ON relokasi_kirim.Produk = trs_invsum.KodeProduk
                                   LEFT OUTER JOIN
                                    (SELECT produk,
                                            IFNULL(SUM(Qty * -1),0) AS 'qty' 
                                      FROM  trs_mutasi_koreksi
                                    WHERE   qty < 0 AND  Cabang = '$cabang' AND
                                        STATUS = 'Approve' AND 
                                        IFNULL(DATE(tgl_approve),tanggal) BETWEEN '$first_date' AND '$end_date'
                                  GROUP BY produk ) AS koreksi_min ON koreksi_min.produk = trs_invsum.KodeProduk
                                  LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         SUM(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invsum
                                   WHERE Tahun ='$getyear'
                                   GROUP BY KodeProduk) AS stok ON stok.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         SUM(IFNULL(UnitStok,0)) AS 'qty'
                                   FROM trs_invdet
                                   WHERE Tahun ='$getyear'
                                   GROUP BY KodeProduk) AS stokdetail ON stokdetail.KodeProduk = trs_invsum.KodeProduk 
                                   LEFT OUTER JOIN
                                  (SELECT KodeProduk,
                                         (CASE COUNT(UnitStok)
                                          WHEN 0 THEN 'Ok'
                                          ELSE 'Minus' END ) AS 'Status_stok_detail'
                                  FROM trs_invdet
                                  WHERE IFNULL(UnitStok,0) < 0 AND Tahun ='$getyear'
                                  GROUP BY KodeProduk) AS stok_detail ON stok_detail.KodeProduk = trs_invsum.KodeProduk
                                ORDER BY trs_invsum.KodeProduk ASC
                            )z WHERE (UnitStok <> SaldoAwal) OR (SaldoAwal <> saldo_akhir)")->num_rows();
        return $data;
    }

    public function set_invday($tipe=null)
    {   
        $datetime = date('Y-m-d H:i:s');
        if($tipe =='daily'){
            $date  = date('Y-m-d',strtotime("+1 days"));
            $day  = date('d',strtotime($date));
        }else{
            $date = date('Y-m-d');
            $day  = date('d');
        }
        
        // delete transaksi bulan lalu
        $this->db->query("DELETE FROM trs_invday_sum WHERE Bulan = MONTH(CURDATE()) - 1"); // sum
        $this->db->query("DELETE FROM trs_invday_det WHERE Bulan = MONTH(CURDATE()) - 1"); // det

        // ===== UPDATE trs_invday_sum
               $valid = $this->db->query("INSERT INTO `trs_invday_sum`
                    (`Tahun`,`Bulan`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,`Pabrik`,`KodeProduk`,
                    `NamaProduk`,`Gudang`,`Indeks`,`UnitCOGS`,`HNA`,`SAwal$day`,`VAwal$day`)
                    SELECT YEAR(CURDATE()),MONTH(CURDATE()),`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                    `Pabrik`,`KodeProduk`,`NamaProduk`,`Gudang`,`Indeks`,`UnitCOGS`,`HNA`,`UnitStok`,`ValueStok` FROM trs_invsum WHERE Tahun = YEAR(CURDATE())
                    ON DUPLICATE KEY UPDATE trs_invday_sum.SAwal$day = UnitStok , trs_invday_sum.VAwal$day = ValueStok");
            
           
        // ===== END trs_invday_sum

        // ===== UPDATE trs_invday_det
           
               $valid = $this->db->query("INSERT INTO `trs_invday_det`
                    (`Tahun`,`Bulan`,`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,`Pabrik`,`KodeProduk`,
                    `NamaProduk`,`Gudang`,`UnitCOGS`,`BatchNo`,`ExpDate`,`NoDokumen`,`TanggalDokumen`,`SAwa$day`,`VAwa$day`)
                    SELECT YEAR(CURDATE()),MONTH(CURDATE()),`Cabang`,`KodePrinsipal`,`NamaPrinsipal`,
                    `Pabrik`,`KodeProduk`,`NamaProduk`,`Gudang`,`UnitCOGS`,`BatchNo`,`ExpDate`,`NoDokumen`,`TanggalDokumen`,`UnitStok`,`ValueStok` FROM trs_invdet WHERE Tahun = YEAR(CURDATE())
                    ON DUPLICATE KEY UPDATE trs_invday_det.SAwa$day = UnitStok , trs_invday_det.VAwa$day = ValueStok");
            
           
        // ===== END trs_invday_det


        return $valid;
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

