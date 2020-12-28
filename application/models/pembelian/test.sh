 mysql -usapta -pSapta254*x -e"
 use sst;


DELETE FROM sst_fed_temp.trs_delivery_order_sales_detail_month_fed_mkr;
INSERT INTO sst_fed_temp.trs_delivery_order_sales_detail_month_fed_mkr 
SELECT * FROM sst.trs_delivery_order_sales_detail WHERE YEAR(TglDO) = YEAR(CURDATE()) AND MONTH(TglDO)= MONTH(CURDATE()) -1 ;


DELETE FROM sst_fed_temp.trs_faktur_detail_month_fed_mkr;
INSERT INTO sst_fed_temp.trs_faktur_detail_month_fed_mkr 
SELECT * FROM sst.trs_faktur_detail WHERE YEAR(TglFaktur) = YEAR(CURDATE()) AND MONTH(TglFaktur)= MONTH(CURDATE()) -1 ;


 "