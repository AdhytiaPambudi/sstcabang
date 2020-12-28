 mysql -usapta -pSapta254*x -e"
 use sst;


DROP DATABASE sst_fed_temp; CREATE DATABASE sst_fed_temp;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_karyawan_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_karyawan_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.mst_karyawan WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.mst_pelanggan_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/mst_pelanggan_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.mst_pelanggan WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_giro_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_giro_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_buku_giro WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_kasbon_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_kasbon_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_buku_kasbon WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_buku_transaksi_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_buku_transaksi_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_buku_transaksi WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_cndn_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_cndn_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_faktur_cndn WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_faktur WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_giro_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_giro_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_giro WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invdet_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invdet_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_invdet WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_invsum_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_invsum_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_invsum WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_kiriman_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_kiriman_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_kiriman WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_detail_ssp_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_detail_ssp_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_pelunasan_detail_ssp WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_pelunasan_giro_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_pelunasan_giro_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_pelunasan_giro_detail WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_po_detail WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_po_header_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_po_header_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_po_header WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_sales_order_detail WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_sales_order_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_sales_order_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_sales_order WHERE 1 = 0 ;  
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_detail_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_detail_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_terima_barang_detail WHERE 1 = 0 ;   
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_terima_barang_header_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_terima_barang_header_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_terima_barang_header WHERE 1 = 0 ; 
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_delivery_order_sales_detail_month_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_delivery_order_sales_detail_month_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_delivery_order_sales_detail WHERE 1 = 0 ; 
CREATE TABLE IF NOT EXISTS sst_fed_temp.trs_faktur_detail_month_fed_mkr 
  ENGINE=FEDERATED COLLATE = latin1_swedish_ci 
  CONNECTION='mysql://sapta:Sapta254*x@119.235.19.138:3306/sst_federate_temp/trs_faktur_detail_month_fed_mkr' 
  COMMENT = '' SELECT *  FROM sst.trs_faktur_detail WHERE 1 = 0 ; 
  
  
DELETE FROM sst_fed_temp.trs_delivery_order_sales_fed_mkr; 
INSERT INTO sst_fed_temp.trs_delivery_order_sales_fed_mkr 
SELECT * FROM sst.trs_delivery_order_sales 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);

DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_fed_mkr; 
INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_fed_mkr 
SELECT trs_delivery_order_sales_detail.* FROM sst.trs_delivery_order_sales_detail 
	JOIN (SELECT Cabang,NoDO,modified_at,TimeDO FROM sst.trs_delivery_order_sales) AS trs_delivery_order_sales 
	ON trs_delivery_order_sales_detail.NoDO = trs_delivery_order_sales.NoDO 
		AND trs_delivery_order_sales_detail.Cabang = trs_delivery_order_sales.Cabang
	WHERE
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' 
				THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' 
				THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(trs_delivery_order_sales.modified_at,'') != '' 
				THEN DATE(trs_delivery_order_sales.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_delivery_order_sales.TimeDO) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);
		

DELETE FROM sst_fed_temp.trs_kiriman_fed_mkr; 
INSERT INTO sst_fed_temp.trs_kiriman_fed_mkr 
SELECT * FROM sst.trs_kiriman 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeKirim) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             


DELETE FROM sst_fed_temp.trs_faktur_fed_mkr; 
INSERT INTO sst_fed_temp.trs_faktur_fed_mkr 
SELECT * FROM sst.trs_faktur 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             


DELETE FROM sst_fed_temp.trs_faktur_detail_fed_mkr; 
INSERT INTO sst_fed_temp.trs_faktur_detail_fed_mkr 
SELECT trs_faktur_detail.* FROM sst.trs_faktur_detail 
	JOIN (SELECT Cabang,NoFaktur,modified_at,TimeFaktur FROM sst.trs_faktur) AS trs_faktur 
		ON  trs_faktur_detail.NoFaktur = trs_faktur.NoFaktur AND trs_faktur_detail.Cabang = trs_faktur.Cabang
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' 
				THEN DATE(trs_faktur.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_faktur.TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' 
				THEN DATE(trs_faktur.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_faktur.TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(trs_faktur.modified_at,'') != '' 
				THEN DATE(trs_faktur.modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(trs_faktur.TimeFaktur) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             

                              
DELETE FROM sst_fed_temp.trs_faktur_cndn_fed_mkr; 
INSERT INTO sst_fed_temp.trs_faktur_cndn_fed_mkr 
SELECT * FROM sst.trs_faktur_cndn 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(updated_at,'') != '' 
				THEN DATE(updated_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(created_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             

DELETE FROM sst_fed_temp.trs_pelunasan_detail_fed_mkr; 
INSERT INTO sst_fed_temp.trs_pelunasan_detail_fed_mkr 
SELECT * FROM sst.trs_pelunasan_detail 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             


DELETE FROM sst_fed_temp.trs_pelunasan_giro_detail_fed_mkr; 
INSERT INTO sst_fed_temp.trs_pelunasan_giro_detail_fed_mkr 
SELECT * FROM sst.trs_pelunasan_giro_detail 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             



DELETE FROM sst_fed_temp.trs_giro_fed_mkr; 
INSERT INTO sst_fed_temp.trs_giro_fed_mkr 
SELECT * FROM sst.trs_giro 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(modified_at,'') != '' 
				THEN DATE(modified_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(create_at) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             


                          
DELETE FROM sst_fed_temp.trs_buku_transaksi_fed_mkr; 
INSERT INTO sst_fed_temp.trs_buku_transaksi_fed_mkr
SELECT * FROM sst.trs_buku_transaksi 
	WHERE 
	(CASE 
		WHEN DATE_FORMAT(NOW(),'%w') IN (0) 
		THEN 
			CASE WHEN IFNULL(Modified_Time,'') != '' 
				THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
				ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND DATE(NOW()) 
			END 
		WHEN DATE_FORMAT(NOW(),'%w') IN (1) 
		THEN 
			CASE WHEN IFNULL(Modified_Time,'') != '' 
				THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
				ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 3 DAY)) AND DATE(NOW()) 
			END 
		ELSE 
			CASE WHEN IFNULL(Modified_Time,'') != '' 
				THEN DATE(Modified_Time) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
				ELSE DATE(Tanggal) BETWEEN DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND DATE(NOW()) 
			END 
		END);                             

DELETE FROM sst_fed_temp.trs_invsum_fed_mkr;INSERT INTO sst_fed_temp.trs_invsum_fed_mkr SELECT * FROM sst.trs_invsum;
DELETE FROM sst_fed_temp.trs_invdet_fed_mkr;INSERT INTO sst_fed_temp.trs_invdet_fed_mkr SELECT * FROM sst.trs_invdet;

DELETE FROM sst_fed_temp.mst_pelanggan_fed_mkr;INSERT INTO sst_fed_temp.mst_pelanggan_fed_mkr SELECT * FROM sst.mst_pelanggan;
DELETE FROM sst_fed_temp.mst_karyawan_fed_mkr;INSERT INTO sst_fed_temp.mst_karyawan_fed_mkr SELECT * FROM sst.mst_karyawan;


DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_month_fed_mkr;
INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_month_fed_mkr 
SELECT * FROM sst.trs_delivery_order_sales_detail WHERE YEAR(TglDO) = YEAR(CURDATE()) AND MONTH(TglDO)= MONTH(CURDATE()) -1 ;


DELETE FROM sst_fed_temp.trs_faktur_detail_month_fed_mkr;
INSERT INTO sst_fed_temp.trs_faktur_detail_month_fed_mkr 
SELECT * FROM sst.trs_faktur_detail WHERE YEAR(TglFaktur) = YEAR(CURDATE()) AND MONTH(TglFaktur)= MONTH(CURDATE()) -1 ;


 "