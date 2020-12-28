<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body style="font-family: tahoma;">
<table width="30%">
        <tr>
            <td width="20%">No SO</td>
            <td width="2%">:</td>
            <td> <?= $data[0]->NoSO ?></td>
        </tr>
        <tr>
            <td width="20%">Pelanggan</td>
            <td width="2%">:</td>
            <td> <?= $data[0]->Pelanggan ?></td>
        </tr>
    </table>
    <br>
    <table width="100%" border="1" cellpadding="1" cellspacing="0" class="table table-bordered">
        <thead>
            <tr>
                <th width="2%">No</th>
                <th width="10%">Kode Produk</th>
                <th width="10%">Nama Produk</th>
                <th width="5%">Qty</th>
                <th width="5%">Bonus</th>
                <th width="5%">UOM</th>
            </tr>
        </thead>
        <tbody >	
        	<?php $no= 1; foreach ($data as $r): ?>
        		<tr>
	                <td width="2%"><?= $no ?></td>
	                <td width="10%"><?= $r->KodeProduk ?></td>
	                <td width="10%"><?= $r->NamaProduk ?></td>
	                <td width="5%"><?= $r->QtySO ?></td>
	                <td width="5%"><?= $r->Bonus ?></td>
	                <td width="5%"><?= $r->UOM ?></td>
	            </tr>
        	<?php $no++; endforeach ?>
            
        </tbody>
    </table>
</body>
</html>
