
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Main';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Start Pembelian
$route['^(usulanbeli|usulanbeliprinsipal|listProdukUsulanBeli|getProdukUsulanBeli|saveDataUsulanBeli|getLimit|datausulanbeli|listDataUsulanBeli|dataDetailUsulan|prosesDataUsulanBeli|updateDataPusat|saveDataUsulanBeliPrinsipal|getprinsipallimit|datadopusat|load_datadopusat|detaildopusat|updatedataDOBelipusat|usulanBeliBonusPrinsipal|saveDataUsulanBeliBonusPrinsipal|listProdukOrderMonitor|viewusulanbelipusat|listOustandinglimit|updateAms3|closedataDO)(/:any)?$'] = "pembelian/usulanbeli/$0";

$route['^(approval|listDataReleasePR|releaseDataPR|dataUsulan|rejectDataPR|dataprpo|datapr|listDataPRPO|listDataPR|dataDetailPRPO|dataDetailPR|updateDataPOPusat|updateDataPRPusat|dataprpoclosed|listDataPRPOClosed|closedataPO)(/:any)?$'] = "pembelian/releasepr/$0";

$route['^(buatbpb|buatbpb_cabang|getprpo|getprpo_cabang|getCounterBPB|saveDataBPB|saveDataBPBCabang|getbpbpr|databpb|listDataBPB|dataDetailBPB|prosesDataBPB|updateDataBPBPusat|prosesDataBPBCabang|listNoBPB|viewbpppusat|revisi_bpb|get_bpb_revisi|update_bpb|datacndnbelicabang|list_data_cndnBelicabang|detail_cndnbeli_cabang|cetakcndnbelicabang|updateDatacndnbeliPusat)(/:any)?$'] = "pembelian/bpb/$0";

$route['^(buatbpb_retur|dataDetailBKB|saveDataTerimaRetur)(/:any)?$'] = "pembelian/bpb/$0";

$route['^(buatorder|getCounterOrder|saveDataOrder|dataOrder|listDataOrder|dataDetailOrder|prosesDataOrder|listPelanggan|dataPelanggan|listSales|listProduk|getBatch|getProdukBuatOrder|dataso|listDataSO|prosesDataSO|listDataDetailSO|updateDataSOPusat|rejectDataSO|prosesDO|listprodukorder|buatorderulang|listsoulang|getdataorder|saveUlangDataOrder|buatusulandiskonprinsipal|saveDataUsulanDiskonPrins|dataapprovaldiscprins|listDataApprovalDP|prosesDataApprovalDP|updateDataDiscPrinsPusat|datausulandiskonprinsipal|prosesDataUsulanDiscPrins|prosesUlangDataSO|viewapprovalpusat|UnlockTrans|updateDataSOapprovalbyno|listPelanggan2|dataretail|listretail|updateDataretail|cekEDSIPA|saveUsulanEDSIPA|dataUsulanEDSIPA|listDataUsulanEDSIPA|prosesDataApprovalEDSIPA|updateDataUsulanEDSIPA|prosesDataUsulanEDSIPA|RestartTrans|cekstokorder|dataPelanggan_detail|dataPelanggan_acu2|prosesulangtop|listPelangganSP|buatorderSPSalesman|getOrderSalesman|listPelanggan_all|gethargaproduk)(/:any)?$'] = "pembelian/order/$0";

$route['^(buatkiriman|listDataDO|saveKiriman|terimakiriman|listTerimaKiriman|formTerimaKiriman|saveTerimaKiriman|getDataDO|updateDataDO|datado|listDO|prosesDataDO|datakiriman|listDataKiriman|prosesDataKiriman|listDataDetailDO|updateDataDOPusat|updateDataKirimanPusat|listPengirim|listBatch|buatpickinglist|listDataPicking|updatepickinglist|datado_lama|prosesbatalDataDo|dataeditdo|listDataEditDO|dataapprovaleditdo|listApprovalEditDO|listDataDetailEditDO|prosesDataApprovalEditDO|listDO2|terimaKirimanparsial|listTerimaKirimanparsial|saveTerimaKirimanparsial|prosesReqReturDO|listReturDO|listDataDetailReturDO|cekPelangganReturDO|saveDataReturDO|validasiDO|listvalidasiDO|prosesValidasiDO|prosesReturDO|approvalreturDO|listdatareturDO|approvereturDO|prosesValidasiBanyakDO|prosesTerimaDO||datakirimandetail|listDataKirimandetail|datacekkiriman|listcekDataKiriman|prosesUlangKiriman|validasiDOFaktur|listvalidasiDOFaktur|prosesfixfaktur|RegisterFaktur|listRegisterFaktur|ProsesRegisterFaktur|DataRegisterFaktur|listDataRegisterFaktur|PresentaseRegisterFaktur)(/:any)?$'] = "pembelian/kiriman/$0";


$route['^(datafaktur|datafaktur2|listDataFaktur|listfakturexcel|getDataFaktur|prosesDataFaktur|listDataDetailFaktur|listDataDetailFakturCek|updateDataFakturPusat|buatInkaso|listDataInkaso|buatdih|listPenagih|saveDataDIH|buatretur|saveDataRetur|listFaktur|listPelangganRetur|cekPelangganRetur|listProdukAll|prosesReqRetur|buatcndn|saveDataCNDN|updateDataPelangganPusat|listReqPelanggan|dataretur|datacndn|listDataCNDN|prosesDataDIH|buatdih2|listDataInkaso2|getListFakturDIH|saveDataDIH2|listprintfaktur2|listPrintDataFaktur|listDetailFaktur|buatalasantelatpiutang|listalasanjto|saveDataAlasanjto|dataalasanpiutangterlambat|listDataAlasanJto)(/:any)?$'] = "pembelian/faktur/$0";

$route['^(approvalReturPelanggan|listDataReturPelanggan|approveDataReturPelanggan|rejectDataReturPelanggan)(/:any)?$'] = "pembelian/retur/$0";

$route['^(datainvsum|listDataInvSum|datainvdet|listDataInvDet|datainvhis|listDataInvHis|settlement|listDataSettlement|setSettlement|settlementKas|listDataSettlementKas|setSettlementKas|kartustok|getkartustok|getstokdetail|mutasistok|getmutasistok|kartustokdaily|getkartustokdaily|sobh|getsobh|setstock||uploadsobh|importsobh|datasobhperiode|getdatasobh|sobb|getsobb|uploadsobb|importsobb|datasobbperiode|getdatasobb|setSettlement_bulan|kartustokbatch|getkartustokbatch|getstokdetailbatch|liststoksummary|updatestoksummary|liststokdetail|updatestokdetail|getclosingstokdaily|generatestok|getclosingstokbulanan|generatestokbulanan|getclosingdoheader|getclosingdodetail|getclosingdokirim|getclosingcndn|getclosingvallunas|getclosingbpbDO|getclosingbpbDODetail|getclosingdofaktur|reproses_kartustok|reproses_stokdetail|dataFixSAwal|getSAwalstokdetail|listQtySAwalDetail|updateSAwalDetail|reproses_kartustok_sawal|saveMutasiKoreksi_sawal|kirim_inv_pusat|sobhdetail|getsobhdetail|uploadsobhdetail|importsobhdetail|datasobhdetailperiode|getdatasobhdetail|sobbdetail|getsobbdetail|uploadsobbdetail|importsobbdetail|datasobbdetailperiode|getdatasobbdetail)(/:any)?$'] = "pembelian/inventori/$0";

// =================================== Approval =======================================
$route['^(datalimit|datatop|datadc|datadp|datalimitbeli|listDataApproval|prosesDataApproval|datacn|listDataUsulanCN|prosesDataUsulanCN|prosesDataRejectUsulanCN|viewapproval|listDatalimitbeli|prosesDatalimitbeli|viewapprovallimitbeli|detailDataUsulanCN|datakirimrelokasi|listDatakirimrelokasi|prosesDatakirimrelokasi|dataDetailRelokasiKirim|updateDataapprovePusat|dataoutstandingdo|listDoOutstanding|prosesDataDoOutstanding|updateDataApproveDO|dataup|listDataApproval_UP|prosesApprovalUP|updateDataLimitBeliPusat|updateDataSODOPusat|dataapprovalusulanbeli|listApprovalUsulanBeli|prosesApprovalUsulanBeli|updateDataApprovalusulanbeli|updateDataSOapprovalPusat|viewpiutangpelanggan|datalimitTOP|updateDataCNDNPusat|prosesDataFakturCNDN|listDataApprovalAll|prosesDataApprovalAll|fix_cogs|datakirimrelokasiPusat)(/:any)?$'] = "pembelian/approval/$0";
// add new routes - @DnA
$route['^(approvalOrderKhusus|listDataApprovalOrderKhusus|listDataDetailPelanggan|prosesDataApprovalOrderKhusus|)(/:any)?$'] = "pembelian/approval/$0";

$route['^(cetak_top_limit)(/:any)?$'] = "pembelian/approval/$0";
// =================================== end Approval ====================================

$route['^(buatpelunasan|buatpelunasanparsial|getDataDIH|saveDataPelunasan|datadih|listDataDIH|datapelunasan|listDataPelunasan|prosesDataPelunasan|updateDataPelunasanPusat|listGiro|saveDataGiro|getFakturDIH|datagiro|listDataGiro|prosesGiro|listFakturDIH|prosesFakturDIH|listDataDetailDIH|get_gl_bank|saveDataSSP|listSSP||listTitipan|dataregpenerimaangiro|listregisterpenerimaangiro|datadihdetail|listDataDIHdetail)(/:any)?$'] = "pembelian/pelunasan/$0";

$route['^(buatKas|listGLTransaksi|detailGLTransaksi|saveGLTransaksi|listGLBank|listGLKaryawan|dataKas|dataKasPeriode|dataBank|listDataTransaksi|prosesDataTransaksi|updateDataTransaksiPusat|buatBank|dataBank|dataBank2|listGLKatagori|getNoDIH|mutasikasbank|listGLGiro|getNoDIHByBank|getmutasikasbank|setMutasiKas||getsaldoawalbank|getsaldoawalkas|getNoTitipan|buatbukugiro|saveDataBukuGiro|databukugiro|listDataBukuGiro|getNokontra|getlistNokontra|databukukasbon|listDataBukuKasbon|buatkasopname|saveopnamekas|dataOpnameKas|listDataOpnameKas|buatclosingkas|getclosingkas|listdetailOpnameKas|getsaldoawalopnamekas|databukutitipan|listDataBukuTitipan|dataTransaksiKasBank|listDataTransaksiKasBank|datamutasikas|getdatamutasikas|datamutasibank|getdatamutasibank|SaldoAwalMutasiBank|SaldoAwalMutasiKas)(/:any)?$'] = "pembelian/gl/$0";

$route['^(noDokumen|listCabang|produkAll|produkInStok|batchInStok|getSatuan|getPrinsipal|allbatchInStok|ProdukInkoreksi|batchInKoreksi|ProdukInMutasi|batchInStokRelokasi|getHargaBeli)(/:any)?$'] = "main/$0";

// START HERE

	$route['^(buatrelokasi|getDetailUsulanRelokasi|saveUsulanRelokasi|relokasiusulan|listDataRelokasiUsulan|dataDetailRelokasiUsulan|prosesDataRelokasiUsulan|updateDataRelokasiUsulanPusat|buatkirimanrelokasi|cabangRelokasi|produkRelokasi|getDetailProdukRelokasi|datarelokasikiriman|dataterimarelokasi|listDataTerimaRelokasi|updateDataRelokasiDOPusat|dataDetailRelokasiTerima|EditDataRelokasiterima|approvalData|datarelokasikiriman|load_datarelokasikiriman|updatedataRelokasipusat|prosesDataRelokasi|datarelokasikirimanPusat|load_datarelokasikirimanPusat|updatedataRelokasiKirimanPusat)(/:any)?$'] = "pembelian/relokasi/$0";
	
	// ============= retur Pembelian ====================
	$route['^(usulanreturbeli|saveusulanretur|getstok_retur|datareturbeli|listdatareturbeli|detail_retur_beli|approval_retur_beli|approve_retur_beli|cetakreturbeli|upload_retur_ke_pusat|download_retur_beli_pusat|load_prinsipal|databkb|listDataBKB|listDataBKB2|updateDataBKBPusat)(/:any)?$'] = "pembelian/usulanreturbeli/$0";

	$route['^(saveusulanreturnew|updateDataReturBeliFromPusat)(/:any)?$'] = "pembelian/usulanreturbeli/$0";
	
	// $route['^(usulanreturbeli|usulanbeliprinsipal|listProdukUsulanBeli|getProdukUsulanBeli|saveDataUsulanBeli|getLimit|datausulanbeli|listDataUsulanBeli|dataDetailUsulan|prosesDataUsulanBeli|updateDataPusat|saveDataUsulanBeliPrinsipal|getstok_retur)(/:any)?$'] = "pembelian/usulanreturbeli/$0";

	$route['^(mutasikoreksi|saveMutasiKoreksi|saveMutasiBatch|saveMutasiGudang|approvalMutasiKoreksi|listDataApprvMutasiKoreksi|apprvMutasiKoreksi|rejectMutasiKoreksi|mutasibatch|mutasigudang|datamutasikoreksi|datamutasibatch|datamutasigudang|updateMutasiKoreksi|batchInGudang|batchInNewGudang|getHargaProduk)(/:any)?$'] = "pembelian/mutasi/$0";

	$route['^(dataDOPending|listDataDOPending|prosesDataDOPending)(/:any)?$'] = "pembelian/proses/$0";

	//$route['^(insentif|getSalesman|dataLaporanInsentifSalesman)(/:any)?$'] = "pembelian/insentif/$0";
	$route['^(insentif|getSalesman|dataLaporanInsentifSalesman|addInsentifData|cekDataLapIns|detailInsentifData|dataSalesmanCabang|dataSalesmanCabangPdf|dataSalesmanInsPdf)(/:any)?$'] = "insentif/insentif/$0";
	
	$route['^(printdataUsulan|printdataPO|printdataPR|printdataBPB|printdataSO|printdataDO|printdataFaktur|printdataDIH|printdatamutasikoreksi|printdatamutasibatch|printdatamutasigudang|printdatakiriman|cetakrelokasikiriman|cetakrelokasiterima|cetak_kas|printdataDOBeli)(/:any)?$'] = "pembelian/CetakTransaksi/$0";

	$route['^(print_do_penjualan|print_do_penjualan_mpdf|print_faktur_penjualan)(/:any)?$'] = "pembelian/CetakTransaksi/$0";

	$route['^(usulanlimitbeliprinsipal|datalimitbeliprinsipal|getprinsipalLimit|listCabangPrinsipalLimit|listdataPrinsipalLimitbeli|listDataPrinsipalLimit|addDataPrinsipalLimit|updateDataPrinsipalLimit|getDataPrinsipalLimit|deleteDataPrinsipalLimit|usulanlimitbelicabang|listDataCabangLimit|getCabangLimit|addDataCabangLimit|getDataCabangLimit|updateDataCabangLimit|datalimitcabangbeli|listDatalimitbelicabang|prosesDatalimitbelicabang|updateDataLimitBeliCabangPusat|datalimitbelicabang|listdataCabangLimitbeli|deleteDataCabangLimit|prosesKirimDataLimitCabang)(/:any)?$'] = "pembelian/prinsipal_limit/$0";
	//$route['^(insentif|getSalesman|dataLaporanInsenti
	//$route['^(insentif|getSalesman|dataLaporanInsentifSalesman)(/:any)?$'] = "insentif/insentif/$0";

// ++++++++++++++++ Pelanggan ++++++++++++++++++++
	$route['^(pelanggan|editpelanggan|getalldataPelanggan|saveeditpelanggan|addDataPelanggan|datausulanpelanggan|list_data_usulan_pelanggan)(/:any)?$'] = "pembelian/Usulan_Pelanggan/$0";

// ----------------- Inventaris ---------------
	$route['^(entry_data_inventaris|data_inventaris|save_data_inventaris|load_datainventaris|cetak_inventaris|remove_data_inventaris)(/:any)?$'] = "pembelian/inventaris/$0";

	$route['^(closingharian|closingbulanan|prosesclosingharian|prosesclosingbulanan|setClosing|setClosingBulan|db_download|getdownload|uploaddatacabang|getuploadcabang|getimportdata|setClosingdaily|FixDOFakturClosing|listFixDOFaktur|prosesfixDoClosing|BypassClosing|setKirimdatadaily|setcreatetablepu)(/:any)?$'] = "pembelian/Closing/$0";

	$route['^(laporanpiutang|laporanpiutangx|laporanpdufaktur|laporanpdufakturX|laporanpdudo|laporanpdudoX|laporanbpbdetail|laporanbpbdetailX|laporanUsulanBelidetail|laporanUsulanBelidetailX|laporancndnH|listDatacndnHx|laporanpelanggan|lplg|laporanpelangganX|datastoksalesman|getstoksalesman|laporanpdufakturdo|laporanpdufakturdoX|agingpiut|getagingpiut|getagingpiutF|getagingpiutL|lappu|getlappu|laporanPT|listlaporanPT|laporanpdufakturdo2|laporanpdufakturdoX2|laporandobelidetail|laporandobelidetailX|getexport|getexportPT|laporanpodetail|laporanpodetailX|listlaphna|listHargaCabang|lapenapza|getenapza|laptriwulan|gettriwulan|laporansalesbypelangganbyprinsipal|datasalesbypelangganbyprinsipal|gettelePT|gettelePU|laporanPTNew|listlaporanPTNew|laporanPTByPrinsipal|listlaporanPTByPrinsipal|getexportPTNew|updateHarga|cek_harga_pusat||kirim_sales_pusat|updateProduk|datastok_day|listdatastok_day||datafakturreginkaso|laporanfakturreginkaso|laporanrelokasidetail|datarelokasidetail|laporanrelokasiheader|datarelokasiheader||laporanrelokasiheader1|datarelokasiheader1|laporanbpbheader|databpbheader)(/:any)?$'] = "laporan/CLaporan/$0";

	$route['^(laporanpiutangBM|getdatapiutangBM|laporansalesharian|getdatasalesharian|laporanpiutangpelanggan|listpelangganAll|getdatapiutangpelanggan|laporansalesbyprinsipal|listprinsipalAll|listprinsipalbycode|getdatasalesbyprinsipal|laporanjumlahfaktur|getjumlahfaktur|laporansalesotharian|getdatasalesot|laporanDOharian|getdataDOharian|datapelunasanperiode|getpelunasanperiode|laporanefaktur|getefaktur|laporanEC|getEC|laporanDOTracking|listfakturAll|getkartupiutang|laporanlistDoTracking|getmutasipiutang|laporanmutasiprpo|getmutasiprpoperiode|listDataDetailtransaksi|laporanomsetsalesharian|getomsetsalesharian|laporanomsetsalesharianprinsipal|getomsetsalesharianprinsipal|laporanpl|getnilaipl|listFakturRetur|laporanefaktur2|getefakturbyNo|databpbfaktur|getbpbfaktur|FixCOGS|listdatcogsfaktur|ProsesFixDataCOGS|ProsesFixDataCOGSAll|MutasiPiutang|listlaporanRekapPiutang|kartuPiutang|getkartupiutangPelanggan|LapAgingPiutang|listlaporanAgingPiutang)(/:any)?$'] = "laporan/CLaporan_all/$0";

	$route['^(g_stok|g_pelanggan|g_rute_salesman|g_piutang)(/:any)?$'] = "laporan/GPSCall/$0";
	$route['^(limittop_buatusulan|listFakturPelanggan|limittop_saveusulan|datausulanlimittop|limittop_getdata|limittop_approve|limittop_kirimulang|limittop_getdatapusat|limittop_reject|limittop_riwayatpelanggan)(/:any)?$'] = "pembelian/limittop/$0";
// FINISH HERE
	$route['^(buatordersalesman|getOrderSalesman|saveDataOrder_salesman|datasoSalesman|listDataSO_Salesman|rejectDataSO_Salesman|listDataDetail_Salesman|rejectData_Salesman)(/:any)?$'] = "pembelian/salesman/$0";
// End Pembelian
	
	// rian

	$route['^(buatkirimanKhusus|listDataDOKhusus|saveKirimanKhusus|terimakirimanKhusus|listTerimaKirimanKhusus|formTerimaKirimanKhusus|saveTerimaKirimanKhusus)(/:any)?$'] = "pembelian/kiriman_khusus/$0";

	$route['^(testKhusus|tampilKhusus|buatorderKhusus|getOrderKhusus|saveDataOrder_Khusus|datasoKhusus|listDataSO_Khusus|rejectDataSO_Khusus|listDataDetail_Khusus|rejectData_Mitra|cek_prins1)(/:any)?$'] = "pembelian/khusus/$0";

	$route['^(buatordermitra|cek_prins1|getOrdermitra|saveDataOrder_mitra|datasoMitra|listDataSO_Mitra|listDataDetail_Mitra|Cek_acu2)(/:any)?$'] = "pembelian/mitra/$0";


	$route['^(TargetSalesman|listDataTargetSalesman|listDataKodeSalesman|saveDataTargetSalesman|deleteDataTargetSalesman|getDataTargetSalesman|updateDataTargetSalesman|listDataTargetSalesman_count|uploadTargetSalesman|uploadTargetSalesman_all|tanggal_akhir|LapTargetSalesman|listDataLapTargetSalesman|listDataLapTargetSalesman_count|getExportTargetSalesman|LapTargetSalesmanPivot|listDataLapTargetSalesmanPivot|listDataLapTargetSalesmanPivot_count|LapTargetSalesman_Pivot|getdatasalesman|loadPrinsipal|cek_targetSalesman)(/:any)?$'] = "master/TargetSalesman/$0";

	$route['^(Karyawan|listDataKaryawan|listDataKodeSalesman|saveDataKaryawan|deleteDataKaryawan|getDataKaryawan|updateDataKaryawan|listDataKaryawan_count|uploadKaryawan|uploadKaryawan_all)(/:any)?$'] = "master/Karyawan/$0";

	$route['^(Register_SP_Pelanggan|listSP_pelanggan|detailsp_pelanggan|prosesSP_pelanggan|prosesSP_pelanggan_one)(/:any)?$'] = "master/SP_Pelanggan/$0";

	$route['^(Over_top|listData_overTop|update_over_one|update_over_all)(/:any)?$'] = "pembelian/over_top/$0";

	$route['^(datarelokasireject|load_datarelokasireject|updatedataRelokasipusatReject|ApprovedataRelokasipusatReject|RejectPenerima)(/:any)?$'] = "pembelian/relokasi/$0";

	$route['^(test|test1|test2)(/:any)?$'] = "pembelian/test/$0";
	// End rian

// Start Main

