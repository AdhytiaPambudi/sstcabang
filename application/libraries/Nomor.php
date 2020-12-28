<?php
class nomor{
	
	var $CI;	
	public $validFile;
	public $fileName;
	
	public function __construct(){
	    $this->CI =& get_instance();
	 	$this->CI->load->library('session');
	}

	
		
}
?>