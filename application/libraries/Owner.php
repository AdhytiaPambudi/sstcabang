<?php
class owner{
	
	var $CI;	
	public $validFile;
	public $fileName;
	
	public function __construct(){
	    $this->CI =& get_instance();
	 	$this->CI->load->library('session');
	}
		
	public function alert($confirm, $url = NULL){
		$out = "<script>";
		$out.= "alert('$confirm');";
		
		if(!empty($url))
			$out.= "window.location='".base_url()."$url';";
		
		$out.="</script>";
		echo $out;
	}
	
	public function localDate($date){
		if(!empty($date)){
			$t=explode("-",$date);
			$tanggal=$t[1];
			switch($tanggal){
				case"01";
				 $bulan="Januari";
				break;
				case"02";
				 $bulan="Februari";
				break;
				case"03";
				 $bulan="Maret";
				break;
				case"04";
				 $bulan="April";
				break;
				case"05";
				 $bulan="Mei";
				break;
				case"06";
				 $bulan="Juni";
				break;
				case"07";
				 $bulan="Juli";
				break;
				case"08";
				 $bulan="Agustus";
				break;
				case"09";
				 $bulan="September";
				break;
				case"10";
				 $bulan="Oktober";
				break;
				case"11";
				 $bulan="Nopember";
				break;
				case"12";
				 $bulan="Desember";
				break;
			}
			if($date=="0000-00-00"){
			return '';
			}
			else{
				$output = $t[2]." ".$bulan." ".$t[0];
				$sr = explode(" ",$t[2]);
				
				if(count($sr) > 1)
					$output = $sr[0]." ".$bulan." ".$t[0].", Jam : ".str_replace(":",".",substr($sr[1],0,5));
				
				return $output;	
			}
		}
		else{
			return '';
		}	
	}
	
	public function getFK()
	{
		$c = uniqid(rand(), true);
		$md5c = md5($c);
		return $md5c;		
	}
	
	public function processFile($fileName, $definedName = NULL)
	{
		$arr = explode('.', $fileName);
		$allow = $this->CI->load->config->item("filetypes");
		$this->validFile = false;
		
		$lsc = count($arr) - 1;
		for($c=0; $c < count($allow); $c++)
		{
			if(strtolower($arr[$lsc]) == strtolower($allow[$c]))
				$this->validFile = true;
		}
		
		if ($this->validFile) {
			if( !empty($definedName))
				$this->fileName = $definedName . "." . $arr[$lsc];
			else {
				$fk = $this->getFK();		
				$this->fileName = $fk . "." . $arr[$lsc];
			}
		}
	}
	
	public function listInformasi(){	
		$this->CI->db->order_by("id", "desc");
		$query = $this->CI->db->get('informasi_online');
		$result = $query->result();
		return $result;		   
	}
	
	public function terbilangEn($n) {
	  if ($n < 0) return 'minus ' . $this->terbilangEn(-$n);
	  else if ($n < 10) {
		switch ($n) {
		  case 0: return 'zero';
		  case 1: return 'one';
		  case 2: return 'two';
		  case 3: return 'three';
		  case 4: return 'four';
		  case 5: return 'five';
		  case 6: return 'six';
		  case 7: return 'seven';
		  case 8: return 'eight';
		  case 9: return 'nine';
		}
	  }
	  else if ($n < 100) {
		$kepala = floor($n/10);
		$sisa = $n % 10;
		if ($kepala == 1) {
		  if ($sisa == 0) return 'ten';
		  else if ($sisa == 1) return 'eleven';
		  else if ($sisa == 2) return 'twelve';
		  else if ($sisa == 3) return 'thirteen';
		  else if ($sisa == 5) return 'fifteen';
		  else if ($sisa == 8) return 'eighteen';
		  else return $this->terbilangEn($sisa) . 'teen';
		}
		else if ($kepala == 2) $hasil = 'twenty';
		else if ($kepala == 3) $hasil = 'thirty';
		else if ($kepala == 5) $hasil = 'fifty';
		else if ($kepala == 8) $hasil = 'eighty';
		else $hasil = $this->terbilangEn($kepala) . 'ty';
	  }
	  else if ($n < 1000) {
		$kepala = floor($n/100);
		$sisa = $n % 100;
		$hasil = $this->terbilangEn($kepala) . ' hundred';
	  }
	  else if ($n < 1000000) {
		$kepala = floor($n/1000);
		$sisa = $n % 1000;
		$hasil = $this->terbilangEn($kepala) . ' thousand';
	  }
	  else if ($n < 1000000000) {
		$kepala = floor($n/1000000);
		$sisa = $n % 1000000;
		$hasil = $this->terbilangEn($kepala) . ' million';
	  }
	  else return false;
	
	  if ($sisa > 0) $hasil .= ' ' . $this->terbilangEn($sisa);
	  
	  return $hasil;
	}

	public function terbilang($x)
	{
	  $abil = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
	  if ($x < 12)
	    $hasil = " " . $abil[$x];
	  elseif ($x < 20)
	    $hasil = $this->terbilang($x - 10) . " Belas";
	  elseif ($x < 100)
	    $hasil = $this->terbilang($x / 10) . " Puluh" . $this->terbilang($x % 10);
	  elseif ($x < 200)
	    $hasil = " Seratus" . $this->terbilang($x - 100);
	  elseif ($x < 1000)
	    $hasil = $this->terbilang($x / 100) . " Ratus" . $this->terbilang($x % 100);
	  elseif ($x < 2000)
	    $hasil = " Seribu" . $this->terbilang($x - 1000);
	  elseif ($x < 1000000)
	    $hasil = $this->terbilang($x / 1000) . " Ribu" . $this->terbilang($x % 1000);
	  elseif ($x < 1000000000)
	    $hasil = $this->terbilang($x / 1000000) . " Juta" . $this->terbilang($x % 1000000);

		return $hasil;
	}
	
	public function deletDir($dir) {
	   if (is_dir($dir)) 
	   {
			$objects = scandir($dir);
	
			foreach ($objects as $object) 
			{
				if ($object != "." && $object != "..") 
				{
					if (filetype($dir . "/" . $object) == "dir")
					{
						deleteNonEmptyDir($dir . "/" . $object); 
					}
					else
					{
						unlink($dir . "/" . $object);
					}
				}
			}
	
			reset($objects);
			rmdir($dir);
		}
	}

	public function getUniqID()
	{
		$angka = substr(str_shuffle("1234567890"), 5);
		$huruf = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWQYZabcdefghijklmnopqrstuvwxyz"), 21);
		$shuffle = $angka.$huruf;
		$id = str_shuffle($shuffle);
		return $id;
	}

	public function encrypt($key, $payload) {
	  $iv = mcrypt_create_iv(IV_SIZE, MCRYPT_DEV_URANDOM);
	  $crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $payload, MCRYPT_MODE_CBC, $iv);
	  $combo = $iv . $crypt;
	  $garble = base64_encode($iv . $crypt);
	  return $garble;
	}

	public function decrypt($key, $garble) {
	  $combo = base64_decode($garble);
	  $iv = substr($combo, 0, IV_SIZE);
	  $crypt = substr($combo, IV_SIZE, strlen($combo));
	  $payload = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypt, MCRYPT_MODE_CBC, $iv);
	  return $payload;
	}
}
?>