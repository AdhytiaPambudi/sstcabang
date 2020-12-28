<?php 

    // include('../lib/full/qrlib.php'); 
	$text = $_GET['text'];
    include('phpqrcode/qrlib.php');
     
    // outputs image directly into browser, as PNG stream 
    QRcode::png($text);