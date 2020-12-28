<?php
class Model_insentif extends CI_Model {
    
    function __construct(){
        parent::__construct();
        $this->usergroup = $this->session->userdata('usergroup');
        $this->cabang = $this->session->userdata('cabang');
    }
    
    public function dataLaporanInsentifSalesman($cabang = null) //parameter tanggal
    {


        if ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM" OR $this->session->userdata('usergroup') == "CabangSPV") 
        {     

            //('BM','Supervisor','SPV','Salesman')
            $cab = $this->cabang;

            $byCabang = "";
            $byCabang = " where Cabang = '".$cab."' and `Status`='Aktif' and Jabatan in ('Salesman') and kode is not null";

        }
        elseif ($this->session->userdata('usergroup') == "Admin" OR $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit" ) 
        {   
            //('BM','Supervisor','SPV','Salesman')
            $cab = $cabang;            
            $byCabang = "";
            $byCabang = " where Cabang = '".$cab."' and `Status`='Aktif' and Jabatan in ('Salesman') and kode is not null";
        }
            $query = $this->db->query("SELECT  Nama AS NamaSalesman, Kode AS KodeSalesman, Tipe_Salesman AS TipeSalesman, Jabatan  FROM mst_karyawan $byCabang ORDER BY Jabatan,Tipe_Salesman,Kode,Nama")->result(); 

        return $query;
    }


    public function dataSalesmanCabang($params = null) //parameter cabang
    {

        if(!empty($params->tgl))
        {
            $tgl2 = $params->tgl;            
        }
        else
        {
            $tgl2 = DATE("Y-m-01");            
        }

        if ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM" OR $this->session->userdata('usergroup') == "CabangSPV") 
        {     

            //('BM','Supervisor','SPV','Salesman')
            $cab = $this->cabang;

        }
        elseif ($this->session->userdata('usergroup') == "Admin" OR $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit" ) 
        {   
            //('BM','Supervisor','SPV','Salesman')
            $cab = $params->cabang;            
        }

        if($params->cabx == "Pusat")
        {
            $query = $this->db->query("
                                SELECT  b.Bulan,a.Cabang,a.Nama AS NamaSalesman, a.Kode AS KodeSalesman, a.Tipe_Salesman AS TipeSalesman, a.Jabatan, FORMAT(ifnull(b.TotalInsentif,0),0) as TotalInsentif,b.Created_at,b.Updated_at  FROM mst_karyawan a
                                LEFT JOIN (
                                    SELECT Cabang,KodeSalesman,TipeSalesman,Bulan,
                                    case when SUM(`TotalInsentif`) > 4000000 then 4000000 else SUM(`TotalInsentif`) end
                                    AS TotalInsentif, 
                                    Created_by,Created_at,Updated_by,Updated_at FROM `trs_insentif` WHERE bulan='".$tgl2."' GROUP BY Cabang,KodeSalesman,TipeSalesman,Bulan
                                            ) b ON a.Kode = b.KodeSalesman AND a.`Cabang`=b.Cabang
                                WHERE  `Status`='Aktif' AND Jabatan IN ('Salesman') AND kode IS NOT NULL ORDER BY a.Cabang,Created_at, Updated_at
                        ")->result(); 
        }else
        {
            $query = $this->db->query("
                                SELECT  b.Bulan,a.Cabang,a.Nama AS NamaSalesman, a.Kode AS KodeSalesman, a.Tipe_Salesman AS TipeSalesman, a.Jabatan, FORMAT(ifnull(b.TotalInsentif,0),0) as TotalInsentif,b.Created_at,b.Updated_at  FROM mst_karyawan a
                                LEFT JOIN (
                                    SELECT Cabang,KodeSalesman,TipeSalesman,Bulan,
                                    case when SUM(`TotalInsentif`) > 4000000 then 4000000 else SUM(`TotalInsentif`) end
                                    AS TotalInsentif, 
                                    Created_by,Created_at,Updated_by,Updated_at FROM `trs_insentif` WHERE cabang='".$cab."' AND bulan='".$tgl2."' GROUP BY Cabang,KodeSalesman,TipeSalesman,Bulan
                                            ) b ON a.Kode = b.KodeSalesman AND a.`Cabang`=b.Cabang
                                WHERE a.`Cabang`='".$cab."' AND `Status`='Aktif' AND Jabatan IN ('Salesman') AND kode IS NOT NULL ORDER BY a.Cabang, Created_at, Updated_at
                        ")->result();             
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
                                    AND Cabang='$cabang' AND Kode='$kode' AND STATUS='Aktif'  Limit 1
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
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Alkes-lain') {
                $datTarget = 'TargetAlkesLain';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Altamed') {
                $datTarget = 'TargetAltamed';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Andalan') {
                $datTarget = 'TargetAndalan';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Armoxindo') {
                $datTarget = 'TargetArmox';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Axion') {
                $datTarget = 'TargetAxion';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Bintang KK') {
                $datTarget = 'TargetBKK';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Calumika') {
                $datTarget = 'TargetCalumika';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Cendo') {
                $datTarget = 'TargetCendo';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Coronet') {
                $datTarget = 'TargetCoronet';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Corsa') {
                $datTarget = 'TargetCorsa';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Dipa') {
                $datTarget = 'TargetDipaReg';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Dipa.OTC') {
                $datTarget = 'TargetDipaOTC';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'E-KAT') {
                $datTarget = 'TargetEKAT';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Erella') {
                $datTarget = 'TargetErela';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Erlimpex N.Reg') {
                $datTarget = 'TargetErlimpex';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Erlimpex Reg') {
                $datTarget = 'TargetErlimpexReg';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Escolab') {
                $datTarget = 'TargetEscolab';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Fahrenheit.OTC') {
                $datTarget = 'TargetFhOTC';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Firstmed') {
                $datTarget = 'TargetFirstmed';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Global') {
                $datTarget = 'TargetGMP';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Hermed') {
                $datTarget = 'TargetHermed';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Holi') {
                $datTarget = 'TargetHoli';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Hufa') {
                $datTarget = 'TargetHufa';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Itrasal') {
                $datTarget = 'TargetItrasal';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Karindo') {
                $datTarget = 'TargetKarindo';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Lain-lain') {
                $datTarget = 'TargetLain2';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Laserin') {
                $datTarget = 'TargetLaserin';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Mecosin N.Reg') {
                $datTarget = 'TargetMecNR';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Mecosin OTC') {
                $datTarget = 'TargetMecOTC';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Mecosin Reg.') {
                $datTarget = 'TargetMecReg';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Mersi') {
                $datTarget = 'TargetMersi';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Nova') {
                 $datTarget = 'TargetNova';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Nutrindo') {
                $datTarget = 'TargetNutrindo';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Pyridam') {
                $datTarget = 'TargetPyridam';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'SDM') {
                $datTarget = 'TargetSDM';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'SDM ETHICAL') {
                $datTarget = 'TargetSDMEthical';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'SDM OTC') {
                $datTarget = 'TargetSDMOTC';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'SDM OTHERS') {
                $datTarget = 'TargetSDMOthers';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'SDM PEACOCK') {
                $datTarget = 'TargetSDMPeacock';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Seles') {
                $datTarget = 'TargetSeles';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Solas N.Reg') {
                $datTarget = 'TargetSolasNR';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Solas Reg') {
                $datTarget = 'TargetSolasReg';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
         elseif ($prins ==  'Sparta-X') {
                 $datTarget = 'TargetSpartaX';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Sunthi') {
                $datTarget = 'TargetSunthi';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Sutra Fiesta') {
                $datTarget = 'TargetSutraFiesta';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
         elseif ($prins ==  'Tp.NREG') {
                 $datTarget = 'TargetTpNREG';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Tp.OBH') {
                $datTarget = 'TargetTpOBH';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Tp.Reg') {
                $datTarget = 'TargetTpReg';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Trifa') {
                $datTarget = 'TargetTrifa';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        elseif ($prins ==  'Zenith') {
                 $datTarget = 'TargetZenith';
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }
        else
        {
            $datTarget = 'TargetNull';   
        $query = $this->db->query("SELECT IFNULL($datTarget,0) AS TargetP, `Cabang`,`Kode`,DATE(`Tanggal`) AS Tanggal  FROM `mst_target_salesman` WHERE  `Cabang` = '".$cabang."' AND Kode = '".$salesman."' and year(Tanggal) = '".$year."' and month(Tanggal) = '".$month."' limit 1")->row();
        }



        return $query;
    }

    public function salesPrisipal2($salesman = null, $cabang = null, $tgl = null, $prins = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $query = $this->db->query("
                            SELECT Cabang, KodeSalesman, a.Prinsipal,
                                    d.`Prinsipal2`,
                                    SUM(VALUE) AS valSalesP, 
                                    IFNULL(c.`Diskon Jual`,0) AS tdj,  
                                    SUM(IF((IFNULL(a.DscCab,0)+IFNULL(a.DscPri,0)) <= IFNULL(c.`Diskon Jual`,0),VALUE,0)) AS valSalesDisk, 
                                    FLOOR(SUM(IF((IFNULL(a.DscCab,0)+IFNULL(a.DscPri,0)) <= IFNULL(c.`Diskon Jual`,0),VALUE,0))* 0.0075) AS KomSalesDisk 
                            FROM `temp_sd` a 
                                LEFT JOIN `mst_produk` d ON a.KodeProduk=d.Kode_Produk AND d.`Prinsipal2` = '$prins'
                                LEFT JOIN `mst_target_diskon_jual` c ON a.`Prinsipal`=c.`Prinsipal` AND c.Tahun='2018' 
                            WHERE  `Cabang` IN ('".$cabang."') AND KodeSalesman IN ('".$salesman."') 
                                and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' 
                                and d.`Prinsipal2` = '".$prins."' GROUP BY Cabang, KodeSalesman,d.`Prinsipal2` limit 1
                    ")->row();

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
        if ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM") 
        {
            $query = $this->db->query("select count(*) as total  from temp_ss a, mst_pelanggan b where a.`KodeSalesman` = '".$kode."' and a.`Cabang` = '".$this->cabang."' and year(a.TglFaktur) = '".$year."' and month(a.TglFaktur) = '".$month."' and a.`KodePelanggan` = b.`Kode` and year(b.Created_at) = '".$year."' and month(b.Created_at) = '".$month."'")->row();
        }
        elseif ($this->session->userdata('usergroup') == "Admin" || $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit") 
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



        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime( $tgl ))); //kurang tanggal sebanyak 1 bulan

        $datemin = explode("-", $enddatemin);
        $yearmin = $datemin[0];
        $monthmin = $datemin[1];

        $jHari = cal_days_in_month(CAL_GREGORIAN, $monthmin, $yearmin) + 60;

        if ($tgl  >  $enddate)
        {
            $tgl = $enddate;
        }
            if($tgl >= date('Y-m-d', strtotime('2018-01-01')))
            {
                $query = $this->db->query("
                    SELECT Cabang,KodeSalesman, 
                        SUM(lunTot) AS lunTot, 
                        SUM(vp30sx) AS vp30s, SUM(vp30tx) AS vp30t, 
                        SUM(vp45sx) AS vp45s, SUM(vp45tx) AS vp45t, 
                        SUM(vp60sx) AS vp60s, SUM(vp60tx) AS vp60t, 
                        SUM(vpb60sx) AS vpb60s, SUM(vpb60tx) AS vpb60t , 
                        ( SUM(vp30sx) + SUM(vp30tx) + SUM(vp45sx) + SUM(vp45tx) + SUM(vp60sx) + SUM(vp60tx) + SUM(vpb60sx) + SUM(vpb60tx)) AS tothit, 'IF' AS ket 
                    FROM (
                        SELECT Cabang,KodeSalesman,(ValuePelunasan) AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND DATEDIFF(TglPelunasan, TglFaktur) <= $jHari AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND DATEDIFF(TglPelunasan, TglFaktur) <= $jHari AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Cicilan','Bayar Full') and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang,KodeSalesman,ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Giro Cair') AND TglTransaksi <= '$enddate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang, KodeSalesman, ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE STATUS IN ('Giro Cair') AND MONTH(TglTransaksi)='$month' AND YEAR(TglTransaksi)='$year' 
                                AND TglPelunasan < '$startdate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                    )tpel 
                        WHERE Cabang='$cabang' AND KodeSalesman='$kode' GROUP BY Cabang,KodeSalesman
                ")->row(); 



            }else
            {
                $query = $this->db->query("
                    SELECT Cabang,KodeSalesman, 
                        SUM(lunTot) AS lunTot, 
                        SUM(vp30sx) AS vp30s, SUM(vp30tx) AS vp30t, 
                        SUM(vp45sx) AS vp45s, SUM(vp45tx) AS vp45t, 
                        SUM(vp60sx) AS vp60s, SUM(vp60tx) AS vp60t, 
                        SUM(vpb60sx) AS vpb60s, SUM(vpb60tx) AS vpb60t , 
                        ( SUM(vp30sx) + SUM(vp30tx) + SUM(vp45sx) + SUM(vp45tx) + SUM(vp60sx) + SUM(vp60tx) + SUM(vpb60sx) + SUM(vpb60tx)) AS tothit, 'IF' AS ket 
                    FROM (
                        SELECT Cabang,KodeSalesman,(ValuePelunasan) AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND DATEDIFF(TglPelunasan, TglFaktur) <= 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND DATEDIFF(TglPelunasan, TglFaktur) <= 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Cicilan','Bayar Full') and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang,KodeSalesman,ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND DATEDIFF(TglTransaksi, TglFaktur) <= 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND DATEDIFF(TglTransaksi, TglFaktur) <= 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Giro Cair') AND TglTransaksi <= '$enddate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang, KodeSalesman, ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND DATEDIFF(TglTransaksi, TglFaktur) <= 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND DATEDIFF(TglTransaksi, TglFaktur) <= 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE STATUS IN ('Giro Cair') AND MONTH(TglTransaksi)='$month' AND YEAR(TglTransaksi)='$year' 
                                AND TglPelunasan < '$startdate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                    )tpel 
                        WHERE Cabang='$cabang' AND KodeSalesman='$kode' GROUP BY Cabang,KodeSalesman
                ")->row(); 

            }

        return $query;        

    }


    public function cekDataLapIns($tgl =  null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $query = $this->db->query("SELECT Status FROM mst_insentif_status WHERE Tanggal='".$year."-".$month."-01' AND Status='OK'")->row();
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

        $enddatemin = date('Y-m-t', strtotime('-1 month', strtotime($tgl))); //kurang tanggal sebanyak 1 bulan

        $datemin = explode("-", $enddatemin);
        $yearmin = $datemin[0];
        $monthmin = $datemin[1];

        $namatabel = 'temp_pu_'.$yearmin.''.$monthmin;


        $jHari = cal_days_in_month(CAL_GREGORIAN, $monthmin, $yearmin) + 60;


        $query = $this->db->query("
                SELECT Cabang,KodeSalesman, 
                    CASE WHEN SUM(saldo) > 0 THEN SUM(saldo) ELSE 0 END AS penalti, 
                    CASE WHEN SUM(saldo) > 0 THEN (SUM(saldo)*0.0015) ELSE 0 END AS vpenalti 
                    FROM ( 
                    SELECT ss.Cabang,ss.KodeSalesman,ss.TglFaktur,ss.NoFaktur, 
                        VALUE,TotalValue, saldo, 
                        DATEDIFF('$enddatemin',ss.TglFaktur) AS umurF 
                    FROM $namatabel ss WHERE ss.Cabang='$cabang' 
                        AND ss.KodeSalesman='$kode' 
                        AND DATEDIFF('$enddatemin',ss.TglFaktur) > 60 
                        AND DATEDIFF('$enddatemin',ss.TglFaktur) <= 90 
                        AND TipeFaktur IN ('Faktur','Retur') AND TglFaktur < '$startdate' 
                        )pen 
                GROUP BY Cabang,KodeSalesman LIMIT 1 
        ")->row(); 



        return $query;

    }

        public function komisiTaghanPenaltiData($tgl = null, $kode = null, $cabang = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $startdate = date('Y-m-01', strtotime($tgl));
        $enddate = date('Y-m-t', strtotime($tgl));
        $tgl_today=date('Y-m-d');

        $enddatemin = date('Y-m-t', strtotime('-1 month', strtotime( $tgl ))); //kurang tanggal sebanyak 1 bulan

        $datemin = explode("-", $enddatemin);
        $yearmin = $datemin[0];
        $monthmin = $datemin[1];

        $namatabel = 'temp_pu_'.$yearmin.''.$monthmin;


        $jHari = cal_days_in_month(CAL_GREGORIAN, $monthmin, $yearmin) + 60;


        $query = $this->db->query("
                    SELECT ss.Cabang,ss.KodeSalesman,ss.TglFaktur,ss.NoFaktur, 
                        Value,TotalValue, saldo, 
                        (IFNULL(saldo,0)*0.0015) AS vpenalti,
                        DATEDIFF('$enddatemin',ss.TglFaktur) AS umurF 
                    FROM $namatabel ss WHERE ss.Cabang='$cabang' 
                        AND ss.KodeSalesman='$kode' 
                        AND DATEDIFF('$enddatemin',ss.TglFaktur) > 60 
                        AND DATEDIFF('$enddatemin',ss.TglFaktur) <= 90 
                        AND TipeFaktur IN ('Faktur','Retur') AND TglFaktur < '$startdate' 
        ")->result(); 



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
        if ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from temp_ss where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$this->cabang."' order by TglFaktur ")->result();
        }
        elseif ($this->session->userdata('usergroup') == "Admin" OR $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit") 
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
        if ($this->session->userdata('usergroup') == "Apotik") 
        { 
            $query = $this->db->query("select distinct(TglFaktur) as TglFaktur from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and KodeApotik = '".$this->session->userdata('kode')."' order by TglFaktur ")->row();
        }
        elseif ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$this->session->userdata('cabang2')."' order by TglFaktur ")->result();
        }
        elseif ($this->session->userdata('usergroup') == "Admin" OR $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit") 
        {
            $query = $this->db->query("select distinct(TglFaktur) from tsalesretailtotal where year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' and KodeSalesman = '".$kode."' and Cabang = '".$cabang."' order by TglFaktur ")->result();
        }

        return $query;
    }

    public function salesCountPelanggan($tgl = null, $kode = null, $cabang = null)
    {
        if ($this->session->userdata('usergroup') == "Cabang" OR $this->session->userdata('usergroup') == "BM") 
        {
            $faktur = $this->db->query("select * from temp_ss a where a.Cabang = '".$this->cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Faktur'")->num_rows();
            $retur = $this->db->query("select * from temp_ss a where a.Cabang = '".$this->cabang."' and a.KodeSalesman = '".$kode."' and TglFaktur = '".$tgl."' and TipeFaktur = 'Retur'")->num_rows();
        }
        elseif ($this->session->userdata('usergroup') == "Admin" OR $this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('usergroup') == "Audit") 
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
        $call = (!empty($query->Call)? $query->Call : 15); 
        return $call;
    }

    public function salesInsentifCall($val = null, $kondisi = null)
    {   

        $query = $this->db->query("select insentif from mst_target_ipt where `Batas bawah` <= ".$val." and `Batas atas` >= ".$val." and Tahun = '18'")->row();
        // and kondisi = '".$kondisi."'
        $insentif = (!empty($query->Insentif)) ? $query->Insentif : 0;
        return $insentif;
    }


    public function komisiEC($batas = null, $kondisi = null, $tahun = null)
    {
        $query = $this->db->query("SELECT * FROM `mst_target_ins_ec` WHERE `Batas bawah` <= $batas AND `Batas atas` >= $batas AND Kondisi='$kondisi' AND Tahun='$tahun' limit 1")->row();
        return $query;
    }

    public function listTargetSalesman()
    {   
        if ($this->session->userdata('usergroup') == "Cabang" || $this->session->userdata('usergroup') == "Apotik") 
        {
            $query = $this->db->query("select * from mtargetsalesman where cabang = '".$this->session->userdata('cabang2')."' order by tanggal desc")->result();
        }
        elseif ($this->session->userdata('usergroup') == "Admin" || $this->session->userdata('usergroup') == "Pusat" || $this->session->userdata('usergroup') == "BM") 
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


        $query = $this->db->query("
                        SELECT Cabang,SUM(JumH2) AS JumH,SUM(JumD2) AS JumD 
                        FROM ( 
                            SELECT a.Cabang, a.KodeSalesman,a.`TipeFaktur`, 
                                CASE  a.TipeFaktur 
                                    WHEN 'Faktur' THEN IFNULL(COUNT(DISTINCT (a.NoFaktur)),0) 
                                    WHEN 'Retur' THEN IFNULL(COUNT(DISTINCT (a.NoFaktur)),0) * -1 
                                        ELSE 0 END AS JumH2, 
                                CASE  a.TipeFaktur 
                                    WHEN 'Faktur' THEN IFNULL(jumD,0) 
                                    WHEN 'Retur' THEN IFNULL(jumD,0) * -1 
                                    ELSE 0 END AS JumD2 
                            FROM temp_sd a 
                            LEFT JOIN  (
                                    SELECT Cabang,KodeSalesman,COUNT(NoFaktur) AS jumD, TipeFaktur 
                                    FROM temp_sd 
                                    WHERE MONTH(TglFaktur)=$month AND YEAR(TglFaktur)=$year 
                                    GROUP BY Cabang, KodeSalesman, MONTH(TglFaktur), YEAR(TglFaktur), `TipeFaktur`
                                        ) b ON a.`Cabang`=b.Cabang AND a.`KodeSalesman`=b.`KodeSalesman` AND a.`TipeFaktur`=b.`TipeFaktur` 
                            WHERE a.Cabang = '$cabang' AND a.KodeSalesman = '$kode' 
                                AND MONTH(a.TglFaktur)=$month AND YEAR(a.TglFaktur)=$year 
                                AND a.`TipeFaktur` IN ('Faktur','Retur') 
                                GROUP BY a.Cabang, a.KodeSalesman, MONTH(a.TglFaktur), YEAR(a.TglFaktur), a.`TipeFaktur`)cekIPT 
                        GROUP BY Cabang, KodeSalesman limit 1")->row();

        return $query;
    }


    public function targetIPT($val = null, $kondisi = null)
    {   

        $query = $this->db->query("select insentif from mst_target_ipt where `Batas bawah` <= '".$val."' and `Batas atas` >= '".$val."' and Tahun = '$kondisi' limit 1")->row();
        $insentif = (!empty($query->insentif)) ? $query->insentif : 0;
        return $insentif;
    }

    public  function insentifROT($tgl = null, $kode = null, $cabang = null, $kondisi = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];


        $query = $this->db->query("
                        SELECT jPFin.*, 
                        CASE WHEN MCL > 0 THEN IFNULL(Insentif,0) ELSE 0 END AS Insentif FROM
                        (
                        SELECT ot.Cabang,ot.KodeSalesman,SUM(ot.cekHitOT) AS JumPel, 
                            b.`Tipe_Salesman`,
                            IFNULL(b.MCL,IFNULL(c.MCL,120)) AS MCL,
                            IFNULL(c.`TargetOT`,72) AS TargetOT, 
                            ((SUM(ot.cekHitOT) / IFNULL(b.MCL,IFNULL(c.MCL,120)))) AS pciOT
                        FROM (                                
                            SELECT Cabang,KodeSalesman,KodePelanggan,SUM(JumFak) AS JumFak,SUM(JumRet) AS JumRet, 
                                (SUM(JumFak)-SUM(JumRet)) AS selFak,
                                CASE 
                                    WHEN (SUM(JumFak)-SUM(JumRet)) > 0 THEN 1
                                    WHEN (SUM(JumFak)-SUM(JumRet)) < 0 THEN -1
                                    ELSE 0 END AS cekHitOT
                            FROM (
                                    (SELECT Cabang,KodeSalesman,KodePelanggan,COUNT(NoFaktur) AS JumFak, 0 AS JumRet 
                                        FROM temp_ss 
                                        WHERE Cabang='$cabang'
                                                AND KodeSalesman='$kode' 
                                                AND MONTH(TglFaktur)=$month AND YEAR(TglFaktur)=$year 
                                                AND TipeFaktur = 'Faktur'
                                                GROUP BY Cabang, KodeSalesman,KodePelanggan)
                                    UNION ALL
                                    (SELECT Cabang,KodeSalesman,KodePelanggan,0 AS JumFak, COUNT(NoFaktur) AS JumRet 
                                        FROM temp_ss 
                                        WHERE Cabang='$cabang'
                                                AND KodeSalesman='$kode' 
                                                AND MONTH(TglFaktur)=$month AND YEAR(TglFaktur)=$year 
                                                AND TipeFaktur = 'Retur'
                                                GROUP BY Cabang, KodeSalesman,KodePelanggan)
                                )aa
                            GROUP BY Cabang, KodeSalesman,KodePelanggan
                            )ot
                                LEFT JOIN `mst_karyawan` b ON ot.KodeSalesman = b.`Kode` AND ot.Cabang = b.`Cabang` 
                                LEFT JOIN `mst_target_ot_salesman` c ON b.Tipe_Salesman = c.`TipeSalesman` 
                            WHERE ot.Cabang='$cabang' 
                            AND ot.KodeSalesman='$kode'
                            GROUP BY ot.Cabang, ot.KodeSalesman
                        )jPFin 
                    LEFT JOIN `mst_target_rot` d ON d.`Tahun`='$kondisi' AND (pciOT) >= d.`Batas Bawah` 
                    AND (pciOT) <= d.`Batas Atas`                         
                ")->row();

        return $query;        
    }


    public  function addInsentifData($params = NULL)
    {
            $valid = false;
     
        if ($this->session->userdata('usergroup') == "Pusat" OR $this->session->userdata('Audit') == "BM" OR $this->session->userdata('usergroup') == "Admin") 
        {
            $cab = $params->cabang;
        }else
        {
            $cab = $this->cabang;
        }

            // Komisi Sales Total
            $kst = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiSalesTotal' and Bulan='".$params->tgl."' and Keterangan=''")->num_rows();

            $this->db->set("Cabang", $cab);
            $this->db->set("KodeSalesman", $params->salesman);
            $this->db->set("Bulan", $params->tgl);
            $this->db->set("TipeInsentif", 'KomisiSalesTotal');
            $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
            $this->db->set("Keterangan", "");
            //----
            $this->db->set('Target1', $params->target1Dat);
            $this->db->set('Target2', 0);
            $this->db->set('Target3', 0);
            $this->db->set('Target4', 0);
            $this->db->set('Kriteria1', $params->persen1Dat);
            $this->db->set("Pencapaian1", $params->sales1Dat);
            $this->db->set("Pencapaian2", $params->persen1Dat);
            $this->db->set("Pencapaian3", 0);
            $this->db->set("Pencapaian4", 0);
            $this->db->set('Insentif1', $params->val1Dat);
            $this->db->set('Insentif2', 0);
            $this->db->set('Insentif3', 0);
            $this->db->set('Insentif4', 0);
            $this->db->set('TotalInsentif', $params->val1Dat);
            if($kst<=0)
            {
                $this->db->set("Created_by", $this->session->userdata('username'));
                $this->db->set("Created_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert('trs_insentif'); 
            }else
            {
                $this->db->set("Updated_by", $this->session->userdata('username'));
                $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                $this->db->where("Cabang", $cab);
                $this->db->where("KodeSalesman", $params->salesman);
                $this->db->where("Bulan", $params->tgl);
                $this->db->where("TipeInsentif", 'KomisiSalesTotal');
                $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                $this->db->where("Keterangan", "");
                $valid = $this->db->update('trs_insentif'); 
            }

            // Komisi EC
           $kec = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiEC' and Bulan='".$params->tgl."' and Keterangan=''")->num_rows();
            $this->db->set("Cabang", $cab);
            $this->db->set("KodeSalesman", $params->salesman);
            $this->db->set("Bulan", $params->tgl);
            $this->db->set("TipeInsentif", 'KomisiEC');
            $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
            $this->db->set("Keterangan", "");
            //----
            $this->db->set('Target1', $params->target2ECtargetDat);
            $this->db->set('Target2', 0);
            $this->db->set('Target3', 0);
            $this->db->set('Target4', 0);
            $this->db->set('Kriteria1', $params->target2ECcallDat);
            $this->db->set('Kriteria2', $params->kerja1Dat);
            $this->db->set('Kriteria3', 0);
            $this->db->set('Kriteria4', 0);
            $this->db->set("Pencapaian1", $params->target2ECtotDat);
            $this->db->set("Pencapaian2", $params->ec1Dat);
            $this->db->set("Pencapaian3", 0);
            $this->db->set("Pencapaian4", 0);
            $this->db->set('Insentif1', $params->val2Dat);
            $this->db->set('TotalInsentif', $params->val2Dat);
            if($kec<=0)
            {
                $this->db->set("Created_by", $this->session->userdata('username'));
                $this->db->set("Created_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert('trs_insentif'); 
            }else
            {
                $this->db->set("Updated_by", $this->session->userdata('username'));
                $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                $this->db->where("Cabang", $cab);
                $this->db->where("KodeSalesman", $params->salesman);
                $this->db->where("Bulan", $params->tgl);
                $this->db->where("TipeInsentif", 'KomisiEC');
                $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                $this->db->where("Keterangan", "");
                $valid = $this->db->update('trs_insentif'); 
            }


            // Komisi IPT
           $kipt = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiIPT' and Bulan='".$params->tgl."' and Keterangan=''")->num_rows();
            $this->db->set("Cabang", $cab);
            $this->db->set("KodeSalesman", $params->salesman);
            $this->db->set("Bulan", $params->tgl);
            $this->db->set("TipeInsentif", 'KomisiIPT');
            $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
            $this->db->set("Keterangan", "");
            //----
            $this->db->set('Target1', 0);
            $this->db->set('Target2', 0);
            $this->db->set('Target3', 0);
            $this->db->set('Target4', 0);
            $this->db->set('Kriteria1', 0);
            $this->db->set('Kriteria2', 0);
            $this->db->set('Kriteria3', 0);
            $this->db->set('Kriteria4', 0);
            $this->db->set("Pencapaian1", $params->kIPTDat);
            $this->db->set("Pencapaian2", $params->targetIPTDat);
            $this->db->set("Pencapaian3", $params->kerjaIPTDat);
            $this->db->set("Pencapaian4", 0);
            $this->db->set('Insentif1', $params->valIPTDat);
            $this->db->set('TotalInsentif', $params->valIPTDat);
            if($kipt<=0)
            {
                $this->db->set("Created_by", $this->session->userdata('username'));
                $this->db->set("Created_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert('trs_insentif'); 
            }else
            {
                $this->db->set("Updated_by", $this->session->userdata('username'));
                $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                $this->db->where("Cabang", $cab);
                $this->db->where("KodeSalesman", $params->salesman);
                $this->db->where("Bulan", $params->tgl);
                $this->db->where("TipeInsentif", 'KomisiIPT');
                $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                $this->db->where("Keterangan", "");
                $valid = $this->db->update('trs_insentif'); 
            }

            // Komisi OT
           $kot = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiOT' and Bulan='".$params->tgl."' and Keterangan=''")->num_rows();
            $this->db->set("Cabang", $cab);
            $this->db->set("KodeSalesman", $params->salesman);
            $this->db->set("Bulan", $params->tgl);
            $this->db->set("TipeInsentif", 'KomisiOT');
            $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
            $this->db->set("Keterangan", "");
            //----
            $this->db->set('Target1', $params->targetOTDat);
            $this->db->set('Target2', 0);
            $this->db->set('Target3', 0);
            $this->db->set('Target4', 0);
            $this->db->set("Pencapaian1", $params->kOTDat);
            $this->db->set("Pencapaian2", 0);
            $this->db->set("Pencapaian3", 0);
            $this->db->set("Pencapaian4", 0);
            $this->db->set('Insentif1', $params->valOTDat);
            $this->db->set('Insentif2', 0);
            $this->db->set('Insentif3', 0);
            $this->db->set('Insentif4', 0);
            $this->db->set('TotalInsentif', $params->valOTDat);
            if($kot<=0)
            {
                $this->db->set("Created_by", $this->session->userdata('username'));
                $this->db->set("Created_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert('trs_insentif'); 
            }else
            {
                $this->db->set("Updated_by", $this->session->userdata('username'));
                $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                $this->db->where("Cabang", $cab);
                $this->db->where("KodeSalesman", $params->salesman);
                $this->db->where("Bulan", $params->tgl);
                $this->db->where("TipeInsentif", 'KomisiOT');
                $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                $this->db->where("Keterangan", "");
                $valid = $this->db->update('trs_insentif'); 
            }

            // Komisi Tagihan
           $ktag = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiTagihan' and Bulan='".$params->tgl."' and Keterangan=''")->num_rows();

            // $ktag1a = !empty($params->tagihanADat) ? $params->$tagihanADat : 0 );
            // $ktag1b = !empty($params->tagihanBDat) ? $params->tagihanBDat : 0 );
            // $ktag1c = !empty($params->tagihanCDat) ? $params->tagihanCDat : 0 );
            // $ktag1d = !empty($params->tagihanDDat) ? $params->tagihanDDat : 0 );
            // $ktag2a = !empty($params->val5Dat) ? $params->val5Dat : 0 );
            // $ktag2b = !empty($params->val6Dat) ? $params->val6Dat : 0 );
            // $ktag2c = !empty($params->val7Dat) ? $params->val7Dat : 0 );
            // $ktag2d = !empty($params->val8Dat) ? $params->val8Dat : 0 );

            $this->db->set("Cabang", $cab);
            $this->db->set("KodeSalesman", $params->salesman);
            $this->db->set("Bulan", $params->tgl);
            $this->db->set("TipeInsentif", 'KomisiTagihan');
            $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
            $this->db->set("Keterangan", "");
            //----
            $this->db->set('Target1', 0);
            $this->db->set('Target2', 0);
            $this->db->set('Target3', 0);
            $this->db->set('Target4', 0); //!empty($params->$tgtP) ? $params->$tgtP : 0
            $this->db->set("Pencapaian1",  $params->tagihanADat  );
            $this->db->set("Pencapaian2",  $params->tagihanBDat  );
            $this->db->set("Pencapaian3",  $params->tagihanCDat  );
            $this->db->set("Pencapaian4",  $params->tagihanDDat  );
            $this->db->set('Insentif1',  $params->val5Dat );
            $this->db->set('Insentif2',  $params->val6Dat );
            $this->db->set('Insentif3',  $params->val7Dat );
            $this->db->set('Insentif4',  $params->val8Dat );
            $this->db->set('TotalInsentif', $params->val5Dat + $params->val6Dat + $params->val7Dat + $params->val8Dat);
            if($ktag<=0)
            {
                $this->db->set("Created_by", $this->session->userdata('username'));
                $this->db->set("Created_at", date("Y-m-d H:i:s"));
                $valid = $this->db->insert('trs_insentif'); 
            }else
            {
                $this->db->set("Updated_by", $this->session->userdata('username'));
                $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                $this->db->where("Cabang", $cab);
                $this->db->where("KodeSalesman", $params->salesman);
                $this->db->where("Bulan", $params->tgl);
                $this->db->where("TipeInsentif", 'KomisiTagihan');
                $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                $this->db->where("Keterangan", "");
                $valid = $this->db->update('trs_insentif'); 
            }

            // Komisi Sales Diskon Prinsipal
            // 'Firstmed','MecNR','sdm_', 'SdmEth'
            $arrPrinsx =  array('Adi', 'Alkes', 'Altamed', 'Andalan', 'Armoxindo', 'Axion', 'BintangKK', 'Calumika', 'Cendo', 'Coronet', 'Corsa', 'Dipa', 'DipaOTC', 'EKAT', 'Erella', 'ErlimpexNReg', 'ErlimpexReg', 'Escolab', 'FhOTC', 'Firstmed', 'Gmp', 'Hermed', 'Holi', 'Hufa', 'Itra', 'Karindo', 'Lainlain', 'Las', 'MecNR', 'MecOTC', 'MecReg', 'Mersi', 'Nova', 'Nutrindo', 'Pyridam', 'sdm_', 'SdmEth', 'SdmOTC', 'SdmOth', 'SdmPck', 'Seles', 'SolasNR', 'SolasReg', 'SpartaX', 'Sunthi', 'SutraFiesta', 'TpNREG', 'TpOBH', 'TpReg', 'Trifa', 'Zenith');

            foreach ($arrPrinsx as $key => $value) {
                    // //Adi
                    $tgtP = "Target".$value."Dat";
                    $slsP = "Sales".$value."Dat";
                    $slsDP = "Sales".$value."DiscDat";
                    $prsP = "Persen".$value."Dat";
                    $kmsP = "Komisi".$value."Dat";
                    $kspri = $this->db->query("Select * from trs_insentif where Cabang='".$cab."' and KodeSalesman='".$params->salesman."' and TipeInsentif='KomisiSalesPrinsipal' and Bulan='".$params->tgl."' and Keterangan='".$value."'")->num_rows();
                    $this->db->set("Cabang", $cab);
                    $this->db->set("KodeSalesman", $params->salesman);
                    $this->db->set("Bulan", $params->tgl);
                    $this->db->set("TipeInsentif", 'KomisiSalesPrinsipal');
                    $this->db->set("TipeSalesman", $params->dataTipeSalesmanDetail);
                    $this->db->set("Keterangan", $value); 
                    //----
                    $this->db->set('Target1', !empty($params->$tgtP) ? $params->$tgtP : 0 );
                    $this->db->set('Target2', 0);
                    $this->db->set('Target3', 0);
                    $this->db->set('Target4', 0);
                    $this->db->set("Pencapaian1", !empty($params->$slsP) ? $params->$slsP : 0 );
                    $this->db->set("Pencapaian2", !empty($params->slsDP) ? $params->$slsDP : 0 );
                    $this->db->set("Pencapaian3", !empty($params->$prsP) ? $params->$prsP : 0 );
                    $this->db->set("Pencapaian4", 0);
                    $this->db->set('Insentif1', !empty($params->$kmsP) ? $params->$kmsP : 0 );
                    $this->db->set('Insentif2', 0);
                    $this->db->set('Insentif3', 0);
                    $this->db->set('Insentif4', 0);
                    $this->db->set('TotalInsentif', !empty($params->$kmsP) ? $params->$kmsP : 0 );
                    //----
                    if($kspri<=0)
                    {
                        $this->db->set("Created_by", $this->session->userdata('username'));
                        $this->db->set("Created_at", date("Y-m-d H:i:s"));
                        $valid = $this->db->insert('trs_insentif'); 
                    }else
                    {
                        $this->db->set("Updated_by", $this->session->userdata('username'));
                        $this->db->set("Updated_at", date("Y-m-d H:i:s"));
                        $this->db->where("Cabang", $cab);
                        $this->db->where("KodeSalesman", $params->salesman);
                        $this->db->where("Bulan", $params->tgl);
                        $this->db->where("TipeInsentif", 'KomisiSalesPrinsipal');
                        $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                        $this->db->where("Keterangan", $value);
                        $valid = $this->db->update('trs_insentif'); 
                    }
            }

                        $this->db->where("Cabang", null);
                        // $this->db->where("KodeSalesman", $params->salesman);
                        // $this->db->where("Bulan", $params->tgl);
                        // $this->db->where("TipeInsentif", 'KomisiSalesPrinsipal');
                        // $this->db->where("TipeSalesman", $params->dataTipeSalesmanDetail);
                        // $this->db->where("Keterangan", $value);
                        $valid = $this->db->delete('trs_insentif'); 
 
                
            return $valid;

    }

        public function salesPrisipal2Data($salesman = null, $cabang = null, $tgl = null, $prins = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $query = $this->db->query("
                            SELECT 
                                Cabang, KodeSalesman,NoFaktur,KodeProduk,Produk, a.Prinsipal,
                                d.`Prinsipal2`,IFNULL(a.DiskonFaktur,0) AS DiskonFakturx,
                                (IFNULL(VALUE,0)) AS valSalesP, 
                                IFNULL(c.`Diskon Jual`,0) AS tdj,  
                                ((IF((IFNULL(a.DiskonFaktur,0)) <= IFNULL(c.`Diskon Jual`,0),VALUE,0))) AS valSalesDisk 
                            FROM `temp_sd` a 
                                LEFT JOIN `mst_produk` d ON a.KodeProduk=d.Kode_Produk AND d.`Prinsipal2` = '$prins'
                                LEFT JOIN `mst_target_diskon_jual` c ON a.`Prinsipal`=c.`Prinsipal` AND c.Tahun='2018' 
                            WHERE  `Cabang` IN ('".$cabang."') AND KodeSalesman IN ('".$salesman."') 
                                and year(TglFaktur) = '".$year."' and month(TglFaktur) = '".$month."' 
                                and d.`Prinsipal2` = '$prins' ORDER BY  a.NoFaktur,d.Produk
                    ")->result();

        return $query;
    }



    public function komisiTaghanData($tgl = null, $kode = null, $cabang = null, $salesman = null, $umr = null)
    {
        $date = explode("-", $tgl);
        $year = $date[0];
        $month = $date[1];

        $startdate = date('Y-m-01', strtotime($tgl));
        $enddate = date('Y-m-t', strtotime($tgl));
        $tgl_today=date('Y-m-d');

        if($umr==30)
        {
            $datq = "and umurP < 30";
        }elseif ($umr==45) {
            $datq = "and umurP >= 30 and umurP < 46";
        }elseif ($umr==46) {
            $datq = "and umurP > 45";
        }else
        {
            $datq = "";
        }

        $enddatemin = date('Y-m-d', strtotime('-1 month', strtotime( $tgl ))); //kurang tanggal sebanyak 1 bulan

        $datemin = explode("-", $enddatemin);
        $yearmin = $datemin[0];
        $monthmin = $datemin[1];

        $jHari = cal_days_in_month(CAL_GREGORIAN, $monthmin, $yearmin) + 60;

        if ($tgl  >  $enddate)
        {
            $tgl = $enddate;
        }
                $query = $this->db->query("
                    SELECT Cabang,NoFaktur,KodeSalesman,KodePenagih,UmurP, TglFaktur,TglPelunasan,TglTransaksi, STATUS,  
                        (lunTot) AS lunTot, 
                        (vp30sx) AS vp30s, (vp30tx) AS vp30t, 
                        (vp45sx) AS vp45s, (vp45tx) AS vp45t, 
                        (vp60sx) AS vp60s, (vp60tx) AS vp60t, 
                        (vpb60sx) AS vpb60s, (vpb60tx) AS vpb60t 
                    FROM (
                        SELECT Cabang,NoFaktur,KodeSalesman,KodePenagih,TglPelunasan, TglFaktur,TglTransaksi, STATUS,DATEDIFF(TglPelunasan, TglFaktur) AS UmurP,(ValuePelunasan) AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) < 30 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) >= 30 AND DATEDIFF(TglPelunasan, TglFaktur) < 46 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 45 AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND DATEDIFF(TglPelunasan, TglFaktur) <= $jHari AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglPelunasan, TglFaktur) > 60 AND DATEDIFF(TglPelunasan, TglFaktur) <= $jHari AND STATUS IN ('Cicilan','Bayar Full') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Cicilan','Bayar Full') and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang,NoFaktur,KodeSalesman,KodePenagih ,TglPelunasan, TglFaktur,TglTransaksi,STATUS,DATEDIFF(TglTransaksi, TglFaktur) AS UmurP,ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE MONTH(TglPelunasan)='$month' AND YEAR(TglPelunasan)='$year' 
                                AND STATUS IN ('Giro Cair') AND TglTransaksi <= '$enddate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                        UNION ALL 
                        SELECT Cabang,NoFaktur,KodeSalesman,KodePenagih ,TglPelunasan, TglFaktur,TglTransaksi,STATUS,DATEDIFF(TglTransaksi, TglFaktur) AS UmurP,ValuePelunasan AS lunTot, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) < 30 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp30tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) >= 30 AND DATEDIFF(TglTransaksi, TglFaktur) < 46 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp45tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 45 AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vp60tx, 
                            (IF( KodeSalesman = KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60sx, 
                            (IF( KodeSalesman <> KodePenagih AND DATEDIFF(TglTransaksi, TglFaktur) > 60 AND DATEDIFF(TglTransaksi, TglFaktur) <= $jHari AND STATUS IN ('Giro Cair') , ValuePelunasan , 0 )) AS vpb60tx 
                            FROM temp_pd 
                            WHERE STATUS IN ('Giro Cair') AND MONTH(TglTransaksi)='$month' AND YEAR(TglTransaksi)='$year' 
                                AND TglPelunasan < '$startdate' and Cabang='$cabang' AND KodeSalesman='$kode' 
                    )tpel 
                        WHERE Cabang='$cabang' AND KodeSalesman='$kode' $datq ORDER BY umurP
                ")->result(); 


        return $query;        

    }




}
?>