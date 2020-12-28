<?php
class Model_insentif extends CI_Model {
    
    function __construct(){
        parent::__construct();
        $this->userGroup = $this->session->userdata('userGroup');
        $this->cabang = $this->session->userdata('cabang');
    }
    
    public function dataLaporanInsentifSalesman($cabang = null) //parameter tanggal
    {
        if ($this->session->userdata('userGroup') == "Cabang" OR $this->session->userdata('userGroup') == "BM") 
        {            
            //('BM','Supervisor','SPV','Salesman')
            $byCabang = "";
            $byCabang = " where Cabang = '".$this->cabang."' and `Status`='Aktif' and Jabatan in ('Salesman') and kode is not null";
            $query = $this->db->query("SELECT  Nama AS NamaSalesman, Kode AS KodeSalesman, Tipe_Salesman AS TipeSalesman, Jabatan  FROM mst_karyawan $byCabang ORDER BY Jabatan,Tipe_Salesman,Kode,Nama")->result(); 
        }
        elseif ($this->session->userdata('userGroup') == "Admin" OR $this->session->userdata('userGroup') == "Pusat" OR $this->session->userdata('userGroup') == "Audit" ) 
        {   
            //('BM','Supervisor','SPV','Salesman')
            $byCabang = "";
            $byCabang = " where Cabang = '".$cabang."' and `Status`='Aktif' and Jabatan in ('Salesman') and kode is not null";
            $query = $this->db->query("SELECT  Nama AS NamaSalesman, Kode AS KodeSalesman, Tipe_Salesman AS TipeSalesman, Jabatan  FROM mst_karyawan $byCabang ORDER BY Jabatan,Tipe_Salesman,Kode,Nama")->result(); 
        }

        return $query;
    }

    public function salesInsentif($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $query = $this->db->query("
                        SELECT a.Cabang, a.KodeSalesman,c.NamaSalesman,c.Tipe_Salesman,c.`Status`, 
                            (SUM(a.`value`)) AS PencapaianSales, 
                            (IFNULL(TargetTotal,0)) AS Target, 
                            ROUND(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0),2) AS Persen,
                            CASE 
                                WHEN c.Tipe_Salesman IN ('Reg','Alkes','Institusi','OTC','RS','Ekat','Apotik','Mix', 'PBF', 'COMBO', 'ALK', 'Alkes')
                                THEN CASE 
                                    WHEN  FLOOR(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0))>=100 THEN FLOOR(SUM(a.`value`) * 0.0025) 
                                    WHEN  FLOOR(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0))>=90 THEN FLOOR(SUM(a.`value`) * 0.0015) 
                                    ELSE 0 END 
                                ELSE CASE 
                                    WHEN  FLOOR(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0))>=110 THEN FLOOR(750000) 
                                    WHEN  FLOOR(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0))>=100 THEN FLOOR(500000) 
                                    WHEN  FLOOR(IFNULL((SUM(a.`value`)  / IFNULL(TargetTotal,0))*100,0))>=90 THEN FLOOR(300000) 
                                    ELSE 0 END 
                                END AS KomisiSalesTotal         
                        FROM temp_sd a 
                        LEFT JOIN (
                                SELECT `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal, 
                                SUM(IFNULL(`TargetAditama`,0)+IFNULL(`TargetAltamed`,0)+IFNULL(`TargetAndalan`,0)+
                                    IFNULL(`TargetArmox`,0)+IFNULL(`TargetAxion`,0) + IFNULL(`TargetAlkesLain`,0)+IFNULL(`TargetBKK`,0)+
                                    IFNULL(`TargetCorsa`,0)+IFNULL(`TargetCendo`,0)+IFNULL(`TargetCoronet`,0) + IFNULL(`TargetDipaReg`,0)+
                                    IFNULL(`TargetDipaOTC`,0)+IFNULL(`TargetErela`,0)+IFNULL(`TargetEKat`,0)+IFNULL(`TargetErlimpex`,0) + 
                                    IFNULL(`TargetErlimpexReg`,0)+IFNULL(`TargetEscolab`,0)+IFNULL(`TargetFhOTC`,0)+IFNULL(`TargetGMP`,0)+
                                    IFNULL(`TargetHoli`,0) + IFNULL(`TargetHermed`,0)+IFNULL(`TargetHufa`,0)+IFNULL(`TargetItrasal`,0)+
                                    IFNULL(`TargetKarindo`,0)+IFNULL(`TargetLaserin`,0) + IFNULL(`TargetLain2`,0)+IFNULL(`TargetMecReg`,0)+
                                    IFNULL(`TargetMecNR`,0)+IFNULL(`TargetMecOTC`,0)+IFNULL(`TargetSolasNR`,0) + IFNULL(`TargetSolasReg`,0)+
                                    IFNULL(`TargetSutraFiesta`,0)+IFNULL(`TargetSDM`,0)+IFNULL(`TargetSDMEthical`,0)+IFNULL(`TargetSDMOTC`,0) + 
                                    IFNULL(`TargetSDMOthers`,0)+IFNULL(`TargetSDMPeacock`,0)+IFNULL(`TargetSunthi`,0)+IFNULL(`TargetSeles`,0) + 
                                    IFNULL(`TargetTrifa`,0) + IFNULL(`TargetTpOBH`,0)+IFNULL(`TargetTpReg`,0)+IFNULL(`TargetMersi`,0)+
                                    IFNULL(`TargetPyridam`,0) + IFNULL(`TargetCalumika`,0)+IFNULL(`TargetNutrindo`,0)) AS TargetTotal 
                                FROM mst_target_salesman WHERE `Cabang` IN ('$cabang') AND Kode IN ('$kode')
                                AND MONTH(Tanggal)='$month' AND YEAR(Tanggal)='$year' 
                                GROUP BY `Cabang`,`Kode`,MONTH(Tanggal)AND YEAR(Tanggal)
                            ) b 
                            ON a.`Cabang`=b.Cabang AND a.`KodeSalesman`=b.Kode 
                        LEFT JOIN (
                                    SELECT Cabang,Kode,Nama AS NamaSalesman, Tipe_Salesman,`Status` FROM mst_karyawan WHERE Jabatan='Salesman'
                                ) c 
                            ON a.Cabang=c.Cabang AND a.KodeSalesman=c.Kode 
                        WHERE MONTH(a.TglFaktur)='$month' AND YEAR(a.TglFaktur)='$year' AND a.Cabang IN ('".$cabang."') AND a.KodeSalesman IN ('".$kode."') GROUP BY a.Cabang, a.KodeSalesman limit 1"
        )->row();


        return $query;
    }

    public function salesTarget($salesman = null, $cabang = null, $tgl = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        $query = $this->db->query("select total from mst_target_salesman where Kode = '".$salesman."' and Cabang = '".$cabang."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();

        return $query;
    }

    public function salesTargetPrisipal($salesman = null, $cabang = null, $tgl = null, $prins = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        $datTarget = 'TargetNull';

        if($prins ==  'Aditama Raya') {
                $datTarget = 'TargetAditama';
        }
        elseif ($prins ==  'Alkes-lain') {
                $datTarget = 'TargetAlkesLain';
        }
        elseif ($prins ==  'Altamed') {
                $datTarget = 'TargetAltamed';
        }
        elseif ($prins ==  'Andalan') {
                $datTarget = 'TargetAndalan';
        }
        elseif ($prins ==  'Armoxindo') {
                $datTarget = 'TargetArmox';
        }
        elseif ($prins ==  'Axion') {
                $datTarget = 'TargetAxion';
        }
        elseif ($prins ==  'Bintang KK') {
                $datTarget = 'TargetBKK';
        }
        elseif ($prins ==  'Calumika') {
                $datTarget = 'TargetCalumika';
        }
        elseif ($prins ==  'Cendo') {
                $datTarget = 'TargetCendo';
        }
        elseif ($prins ==  'Coronet') {
                $datTarget = 'TargetCoronet';
        }
        elseif ($prins ==  'Corsa') {
                $datTarget = 'TargetCorsa';
        }
        elseif ($prins ==  'Dipa') {
                $datTarget = 'TargetDipaReg';
        }
        elseif ($prins ==  'Dipa.OTC') {
                $datTarget = 'TargetDipaOTC';
        }
        elseif ($prins ==  'E-KAT') {
                $datTarget = 'TargetEKAT';
        }
        elseif ($prins ==  'Erella') {
                $datTarget = 'TargetErela';
        }
        elseif ($prins ==  'Erlimpex N.Reg') {
                $datTarget = 'TargetErlimpex';
        }
        elseif ($prins ==  'Erlimpex Reg') {
                $datTarget = 'TargetErlimpexReg';
        }
        elseif ($prins ==  'Escolab') {
                $datTarget = 'TargetEscolab';
        }
        elseif ($prins ==  'Fahrenheit.OTC') {
                $datTarget = 'TargetFhOTC';
        }
        elseif ($prins ==  'Firstmed') {
                $datTarget = 'TargetFirstmed';
        }
        elseif ($prins ==  'Global') {
                $datTarget = 'TargetGMP';
        }
        elseif ($prins ==  'Hermed') {
                $datTarget = 'TargetHermed';
        }
        elseif ($prins ==  'Holi') {
                $datTarget = 'TargetHoli';
        }
        elseif ($prins ==  'Hufa') {
                $datTarget = 'TargetHufa';
        }
        elseif ($prins ==  'Itrasal') {
                $datTarget = 'TargetItrasal';
        }
        elseif ($prins ==  'Karindo') {
                $datTarget = 'TargetKarindo';
        }
        elseif ($prins ==  'Lain-lain') {
                $datTarget = 'TargetLain2';
        }
        elseif ($prins ==  'Laserin') {
                $datTarget = 'TargetLaserin';
        }
        elseif ($prins ==  'Mecosin N.Reg') {
                $datTarget = 'TargetMecNR';
        }
        elseif ($prins ==  'Mecosin OTC') {
                $datTarget = 'TargetMecOTC';
        }
        elseif ($prins ==  'Mecosin Reg.') {
                $datTarget = 'TargetMecReg';
        }
        elseif ($prins ==  'Mersi') {
                $datTarget = 'TargetMersi';
        }
        elseif ($prins ==  'Nova') {
                 $datTarget = 'TargetNova';
        }
        elseif ($prins ==  'Nutrindo') {
                $datTarget = 'TargetNutrindo';
        }
        elseif ($prins ==  'Pyridam') {
                $datTarget = 'TargetPyridam';
        }
        elseif ($prins ==  'SDM') {
                $datTarget = 'TargetSDM';
        }
        elseif ($prins ==  'SDM ETHICAL') {
                $datTarget = 'TargetSDMEthical';
        }
        elseif ($prins ==  'SDM OTC') {
                $datTarget = 'TargetSDMOTC';
        }
        elseif ($prins ==  'SDM OTHERS') {
                $datTarget = 'TargetSDMOthers';
        }
        elseif ($prins ==  'SDM PEACOCK') {
                $datTarget = 'TargetSDMPeacock';
        }
        elseif ($prins ==  'Seles') {
                $datTarget = 'TargetSeles';
        }
        elseif ($prins ==  'Solas N.Reg') {
                $datTarget = 'TargetSolasNR';
        }
        elseif ($prins ==  'Solas Reg') {
                $datTarget = 'TargetSolasReg';
        }
         elseif ($prins ==  'Sparta-X') {
                 $datTarget = 'TargetSpartaX';
        }
        elseif ($prins ==  'Sunthi') {
                $datTarget = 'TargetSunthi';
        }
        elseif ($prins ==  'Sutra Fiesta') {
                $datTarget = 'TargetSutraFiesta';
        }
         elseif ($prins ==  'Tp.NREG') {
                 $datTarget = 'TargetTpNREG';
        }
        elseif ($prins ==  'Tp.OBH') {
                $datTarget = 'TargetTpOBH';
        }
        elseif ($prins ==  'Tp.Reg') {
                $datTarget = 'TargetTpReg';
        }
        elseif ($prins ==  'Trifa') {
                $datTarget = 'TargetTrifa';
        }
        elseif ($prins ==  'Zenith') {
                 $datTarget = 'TargetZenith';
        }
        else
        {
            $datTarget = 'TargetNull';   
        }


        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` IN ('".$cabang."') AND Kode IN ('".$salesman."') and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();

        return $query;
    }
    public function salesPrisipal2($salesman = null, $cabang = null, $tgl = null, $prins = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        //$query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` IN ('".$cabang."') AND Kode IN ('".$salesman."') and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        $query = $this->db->query("SELECT Cabang, KodeSalesman, a.Prinsipal,b.`Prinslns`,d.`Prinsipal2`,SUM(VALUE) AS valSalesP, IFNULL(c.`Diskon Jual`,0) AS tdj,  SUM(IF((IFNULL(a.DiskonFaktur,0)) <= IFNULL(c.`Diskon Jual`,0),VALUE,0)) AS valSalesDisk, FLOOR(SUM(IF((IFNULL(a.DiskonFaktur,0)) <= IFNULL(c.`Diskon Jual`,0),VALUE,0))* 0.0075) AS KomSalesDisk FROM `temp_sd` a LEFT JOIN `mst_prinsipal` b ON a.`Prinsipal`=b.`Prinsipal` LEFT JOIN `mst_target_diskon_jual` c ON a.`Prinsipal`=c.`Prinsipal` AND c.Tahun='2018' LEFT JOIN `mst_produk` d ON a.KodeProduk=d.Kode_Produk WHERE  `Cabang` IN ('".$cabang."') AND KodeSalesman IN ('".$salesman."') and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and d.`Prinsipal2` = '".$prins."' GROUP BY Cabang, KodeSalesman,d.`Prinsipal2` limit 1")->row();

        return $query;
    }

    public function mPrinsipal()
    {

        $query = $this->db->query("SELECT DISTINCT `Prinslns` FROM `mst_prinsipal`")->result();

        return $query;
    }



    public function salesNOO($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        if ($this->session->userdata('userGroup') == "Cabang" OR $this->session->userdata('userGroup') == "BM") 
        {
            $query = $this->db->query("select count(*) as total  from temp_ss a, mst_pelanggan b where a.`KodeSalesman` = '".$kode."' and a.`Cabang` = '".$this->cabang."' and year(a.TglFaktur) = '".$year."' and month(a.TglFaktur) = '".$month."' and a.`KodePelanggan` = b.`Kode` and year(b.Created_at) = '".$year."' and month(b.Created_at) = '".$month."'")->row();
        }
        elseif ($this->session->userdata('userGroup') == "Admin" || $this->session->userdata('userGroup') == "Pusat" OR $this->session->userdata('userGroup') == "Audit") 
        {
            $query = $this->db->query("select count(*) as total  from temp_ss a, mst_pelanggan b where a.`KodeSalesman` = '".$kode."' and a.`Cabang` = '".$cabang."' and year(a.TglFaktur) = '".$year."' and month(a.TglFaktur) = '".$month."' and a.`KodePelanggan` = b.`Kode` and year(b.Created_at) = '".$year."' and month(b.Created_at) = '".$month."'")->row();
        }

        return $query;
    }

    public function salesTagihan($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        $tgl_terakhir = date('Y-m-t', strtotime($tgl));
        $tgl_today=date('Y-m-d');
        
            $query = $this->db->query("select (select case when sum(TotalValue) is not null then sum(TotalValue) else 0 end from temp_ss where datediff('".$tgl_terakhir."', TglFaktur) < 30 and KodeSalesman = '".$kode."' and `Cabang` = '".$this->cabang."' and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."') as A, (select case when sum(TotalValue) is not null then sum(TotalValue) else 0 end from temp_ss where datediff('".$tgl_terakhir."', TglFaktur) >= 30 and datediff('".$tgl_terakhir."', TglFaktur) <= 45 and KodeSalesman = '".$kode."' and `Cabang` = '".$this->cabang."' and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."') as B, (select case when sum(TotalValue) is not null then sum(TotalValue) else 0 end from temp_ss where datediff('".$tgl_terakhir."', TglFaktur) > 45 and datediff('".$tgl_terakhir."', TglFaktur) < 60 and KodeSalesman = '".$kode."' and `Cabang` = '".$this->cabang."' and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."') as C, (select case when sum(TotalValue) is not null then sum(TotalValue) else 0 end from temp_ss where datediff('".$tgl_terakhir."', TglFaktur) >= 60 and KodeSalesman = '".$kode."' and `Cabang` = '".$this->cabang."' and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."') as D ")->row();

        return $query;        
    }

    public function komisiTaghan($tgl = null, $kode = null, $cabang = null, $salesman = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $startdate = date('Y-m-01', strtotime($tgl));
        $enddate = date('Y-m-t', strtotime($tgl));
        $tgl_today=date('Y-m-d');

        if ($tgl  >  $enddate)
        {
            $tgl = $enddate;
        }
            $query = $this->db->query("
                SELECT Cabang,KodeSalesman, 
                    SUM(lunTot) AS lunTot, 
                    SUM(vp30sx) AS vp30s, SUM(vp30tx) AS vp30t, 
                    SUM(vp45sx) AS vp45s, SUM(vp45tx) AS vp45t, 
                    SUM(vp60sx) AS vp60s, SUM(vp60tx) AS vp60t, 
                    SUM(vpb60sx) AS vpb60s, SUM(vpb60tx) AS vpb60t , 
                    ( SUM(vp30sx) + SUM(vp30tx) + SUM(vp45sx) + SUM(vp45tx) + SUM(vp60sx) + SUM(vp60tx) + SUM(vpb60sx) + SUM(vpb60tx)) AS tothit, 'IF' AS ket 
                FROM (
                    SELECT Cabang,KodeSalesman,SUM(ValuePelunasan) AS lunTot, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60tx 
                        FROM temp_pd 
                        WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                            AND STATUS IN ('Cicilan','Bayar Full') and Cabang='$cabang' AND KodeSalesman='$kode' 
                            GROUP BY Cabang, KodeSalesman 
                    UNION ALL 
                    SELECT Cabang,KodeSalesman,ValuePelunasan AS lunTot, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                        FROM temp_pd 
                        WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                            AND STATUS IN ('Giro Cair') AND TglTransaksi <= '$enddate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                            GROUP BY Cabang, KodeSalesman 
                    UNION ALL 
                    SELECT Cabang, KodeSalesman, ValuePelunasan AS lunTot, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                        SUM(IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                        SUM(IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                        FROM temp_pd 
                        WHERE STATUS IN ('Giro Cair') AND MONTH(TglTransaksi)='$month' AND YEAR(TglTransaksi)='$year' 
                            AND TglPelunasan < '$startdate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                            GROUP BY Cabang,KodeSalesman 
                )tpel 
                    WHERE Cabang='$cabang' AND KodeSalesman='$kode' GROUP BY Cabang,KodeSalesman 
            ")->row(); 

        return $query;        

    }

    public function komisiTaghanPenalti($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $startdate = date('Y-m-01', strtotime($tgl));
        $enddate = date('Y-m-t', strtotime($tgl));
        $tgl_today=date('Y-m-d');

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime( $tgl ))); //kurang tanggal sebanyak 6 bulan

        $datemin = explode("-", $enddatemin);
        $yearmin = $datemin[0];
        $monthmin = $datemin[1];


        $jHari = cal_days_in_month(CAL_GREGORIAN, $monthmin, $yearmin) + 60;

        $query = $this->db->query("
                   SELECT *,
                        CASE WHEN sels <=0 THEN 0 ELSE sels END AS penalti,
                        CASE WHEN sels <=0 THEN 0 ELSE sels * 0.0015 END AS vpenalti
                            FROM (
                                SELECT ss.Cabang,ss.KodeSalesman,SUM(ss.TotalValue) AS jumSales, jumLunas, 
                                (ABS(SUM(ss.TotalValue)) - ABS(jumLunas)) AS sels
                                FROM temp_ss ss
                            LEFT JOIN
                            (
                                SELECT Cabang,KodeSalesman,SUM(ValuePelunasan) AS jumLunas FROM temp_pd 
                                WHERE STATUS IN ('Cicilan','Bayar Full','Giro Cair') AND Cabang='$cabang' AND KodeSalesman='$kode'
                                AND DATEDIFF('$enddate' - INTERVAL '1' MONTH,TglFaktur) < $jHari AND DATEDIFF('$enddate' - INTERVAL '1' MONTH,TglFaktur) > 60
                                GROUP BY Cabang,KodeSalesman
                            ) pd ON ss.`Cabang`=pd.`Cabang` AND ss.`KodeSalesman`=pd.`KodeSalesman`
                            WHERE ss.Cabang='$cabang' AND ss.KodeSalesman='$kode' AND DATEDIFF('$enddate' - INTERVAL '1' MONTH,TglFaktur) > 60 
                            AND DATEDIFF('$enddate' - INTERVAL '1' MONTH,TglFaktur) < $jHari AND TipeFaktur IN ('Faktur','Retur') 
                            GROUP BY ss.Cabang,ss.KodeSalesman
                        )xx Limit 1
                ")->row(); 

        return $query;

    }

    public function listSalesman($cabang = null)
    {
        $query = $this->db->query("select Kode, Nama from mkaryawan where Jabatan ='Salesman' and cabang ='".$cabang."' and Status='Aktif' order by Nama asc")->result(); 

        return $query;
    }

    public function salesTipeSalesman($salesman = null, $cabang = null)
    {
        $query = $this->db->query("select Tipe_Salesman as tipeSalesman, Tipe_Salesman2 as tipeSalesman2 from mst_karyawan where Jabatan ='Salesman' and Cabang ='".$cabang."' and Kode='".$salesman."' limit 1")->row(); 

        return $query;
    }

    public function salesECReport($tgl = null, $kode = null)
    {        
        $query = $this->db->query("select hariDK, hariLK from mecsalesmanreport where bulan = month('".$tgl."')")->row();

        return $query;
    }

    public function salesTanggalEfektifCall($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        if ($this->session->userdata('userGroup') == "Cabang" OR $this->session->userdata('userGroup') == "BM") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from temp_ss where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$this->cabang."' order by TglFaktur ")->result();
        }
        elseif ($this->session->userdata('userGroup') == "Admin" OR $this->session->userdata('userGroup') == "Pusat" OR $this->session->userdata('userGroup') == "Audit") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from temp_ss where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$cabang."' order by TglFaktur ")->result();
        }

        return $query;
    }

    public function salesPelangganEfektifCall($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];
        if ($this->session->userdata('userGroup') == "Apotik") 
        { 
            $query = $this->db->query("select distinct(TglFaktur) as TglFaktur from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and KodeApotik = '".$this->session->userdata('kode')."' order by TglFaktur ")->row();
        }
        elseif ($this->session->userdata('userGroup') == "Cabang" OR $this->session->userdata('userGroup') == "BM") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$this->session->userdata('cabang2')."' order by TglFaktur ")->result();
        }
        elseif ($this->session->userdata('userGroup') == "Admin" OR $this->session->userdata('userGroup') == "Pusat" OR $this->session->userdata('userGroup') == "Audit") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$cabang."' order by TglFaktur ")->result();
        }

        return $query;
    }

    public function salesCountPelanggan($tgl = null, $kode = null, $cabang = null)
    {
        if ($this->session->userdata('userGroup') == "Cabang" OR $this->session->userdata('userGroup') == "BM") 
        {
            $faktur = $this->db->query("select * from temp_ss a where a.Cabang = '".$this->cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Faktur'")->num_rows();
            $retur = $this->db->query("select * from temp_ss a where a.Cabang = '".$this->cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Retur'")->num_rows();
        }
        elseif ($this->session->userdata('userGroup') == "Admin" OR $this->session->userdata('userGroup') == "Pusat" OR $this->session->userdata('userGroup') == "Audit") 
        {
            $faktur = $this->db->query("select * from temp_ss a where a.Cabang = '".$cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Faktur'")->num_rows();
            $retur = $this->db->query("select * from temp_ss a where a.Cabang = '".$cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Retur'")->num_rows();
        }

        $ec = $faktur - $retur;
        return $ec;
    }

    public function salesCall($tipe = null)
    {
        if($tipe==null) $tipe='';

        $query = $this->db->query("select * from mst_target_ec where `Tipe Salesman` = '".$tipe."'")->row();
        $call = $query->Call; 
        return $call;
    }

    public function salesInsentifCall($val = null, $kondisi = null)
    {   

        $query = $this->db->query("select insentif from mst_target_ipt where `Batas bawah` <= '".$val."' and `Batas atas` >= '".$val."' and Tahun like '18'")->row();
        // and kondisi = '".$kondisi."'
        $insentif = (!empty($query->Insentif)) ? $query->Insentif : 0;
        return $insentif;
    }


    public function komisiEC($batas = null, $kondisi = null, $tahun = null)
    {
        $query = $this->db->query("SELECT * FROM `mst_target_ins_ec` WHERE `Batas bawah` <= '$batas' AND `Batas atas` >= '$batas' AND Kondisi='$kondisi' AND Tahun='$tahun' limit 1")->row();
        return $query;
    }

    public function listTargetSalesman()
    {   
        if ($this->session->userdata('userGroup') == "Cabang" || $this->session->userdata('userGroup') == "Apotik") 
        {
            $query = $this->db->query("select * from mtargetsalesman where cabang = '".$this->session->userdata('cabang2')."' order by tanggal desc")->result();
        }
        elseif ($this->session->userdata('userGroup') == "Admin" || $this->session->userdata('userGroup') == "Pusat" || $this->session->userdata('userGroup') == "BM") 
        {
            $query = $this->db->query("select * from mtargetsalesman order by tanggal desc")->result();
        }
        return $query;
    }

    public function getTargetSalesman($id = null)
    {
        $query = $this->db->query("select * from mtargetsalesman where id = '".$id."'")->row();
        return $query;
    }

    public function getCabangLok($cabang = null)
    {
        $query = $this->db->query("select * from mst_cabang where Cabang = '".$cabang."'")->row();
        return $query;
    }

    public function jumlahIPT($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];


        $query = $this->db->query("SELECT Cabang,SUM(JumH2) AS JumH,SUM(JumD2) AS JumD FROM ( SELECT a.Cabang, a.KodeSalesman,a.`TipeFaktur`, CASE  a.TipeFaktur WHEN 'Faktur' THEN IFNULL(COUNT(DISTINCT (a.NoFaktur)),0) WHEN 'Retur' THEN IFNULL(COUNT(DISTINCT (a.NoFaktur)),0) * -1 ELSE 0 END AS JumH2, CASE  a.TipeFaktur WHEN 'Faktur' THEN IFNULL(jumD,0) WHEN 'Retur' THEN IFNULL(jumD,0) * -1 ELSE 0 END AS JumD2 FROM temp_sd a LEFT JOIN  (SELECT Cabang,KodeSalesman,COUNT(NoFaktur) AS jumD, TipeFaktur FROM temp_sd WHERE MONTH(TglFaktur)=$month AND YEAR(TglFaktur)=$year GROUP BY Cabang, KodeSalesman, MONTH(TglFaktur), YEAR(TglFaktur), `TipeFaktur`) b ON a.`Cabang`=b.Cabang AND a.`KodeSalesman`=b.`KodeSalesman` AND a.`TipeFaktur`=b.`TipeFaktur` WHERE a.Cabang = '$cabang' AND a.KodeSalesman = '$kode' AND MONTH(a.TglFaktur)=$month AND YEAR(a.TglFaktur)=$year AND a.`TipeFaktur` IN ('Faktur','Retur') GROUP BY a.Cabang, a.KodeSalesman, MONTH(a.TglFaktur), YEAR(a.TglFaktur), a.`TipeFaktur`)cekIPT GROUP BY Cabang, KodeSalesman limit 1")->row();

        return $query;
    }


    public function targetIPT($val = null, $kondisi = null)
    {   

        $query = $this->db->query("select insentif from mst_target_ipt where `Batas bawah` <= '".$val."' and `Batas atas` >= '".$val."' and Tahun = '$kondisi' limit 1")->row();
        $insentif = (!empty($query->Insentif)) ? $query->Insentif : 0;
        return $insentif;
    }

    public  function insentifROT($tgl = null, $kode = null, $cabang = null, $kondisi = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $query = $this->db->query("
                    SELECT jPFin.*, 
                        CASE WHEN JumPel >= MCL THEN IFNULL(Insentif,0) ELSE 0 END AS Insentif 
                    FROM(
                            SELECT jP.Cabang,jP.KodeSalesman,b.`Tipe_Salesman`,
                                IFNULL(b.MCL,IFNULL(c.MCL,120)) AS MCL,
                                SUM(JumPel) AS JumPel, 
                                IFNULL(c.`TargetOT`,72) AS TargetOT, 
                                ((SUM(JumPel) / IFNULL(c.`TargetOT`,72))) AS pciOT 
                            FROM( 
                                SELECT Cabang, KodeSalesman,TipeFaktur, 
                                    CASE TipeFaktur 
                                            WHEN 'Faktur' THEN IFNULL(COUNT(DISTINCT (KodePelanggan)),0) 
                                            WHEN 'DN' THEN IFNULL(COUNT(DISTINCT (KodePelanggan)),0) 
                                            WHEN 'Retur' THEN IFNULL(COUNT(DISTINCT (KodePelanggan)),0) * -1 
                                            ELSE 0 
                                    END AS JumPel 
                                FROM `temp_ss` 
                                WHERE MONTH(TglFaktur)=$month AND YEAR(TglFaktur)=$year 
                                    AND `TipeFaktur` IN ('Faktur','Retur','DN') 
                                GROUP BY Cabang, KodeSalesman , TipeFaktur )jP 
                            LEFT JOIN `mst_karyawan` b ON jP.KodeSalesman = b.`Kode` AND jP.Cabang = b.`Cabang` 
                            LEFT JOIN `mst_target_ot_salesman` c ON b.Tipe_Salesman = c.`TipeSalesman` 
                            WHERE jP.Cabang='$cabang' AND jP.KodeSalesman = '$kode' 
                            GROUP BY jP.Cabang,jP.KodeSalesman)jPFin 
                    LEFT JOIN `mst_target_rot` d ON d.`Tahun`='$kondisi' AND (pciOT) >= d.`Batas Bawah` 
                        AND (pciOT) <= d.`Batas Atas` limit 1")->row();

        return $query;        
    }


}
?>