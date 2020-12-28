<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class mpdf {


	function pdf()

	{

		$CI = & get_instance();

		log_message('Debug', 'mPDF class is loaded.');

	}


	function load($param=NULL)

	{

		include_once dirname(__DIR__).'/third_party/vendor/autoload.php';
		// include_once APPPATH.'third_party\vendor\autoload.php';

			if ($param == NULL){

				// $param = '"utf-8","A4","","",10,10,10,10,6,3';
				// $param = "'utf-8','A4','','','0','0','0','0'";

			}

		return new \Mpdf\Mpdf([	
					'mode' => 'utf-8',
					'format' => [215, 141],
					'margin_top' => 47.5,
					'margin_bottom' => 45,
					'margin_header' => 3,
        			'margin_footer' => 4,2,
        			'margin_left' => 4,
        			'margin_right' => 4
					]);

	}

}