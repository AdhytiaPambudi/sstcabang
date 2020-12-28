<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Over_top extends CI_Controller {

	var $content;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();

		$this->load->model('pembelian/Model_over_top');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->userGroup = $this->session->userdata('userGroup');
		$this->user = $this->session->userdata('username');
		$this->cabang = $this->session->userdata('cabang');
		$this->content = array(
			"base_url" => base_url(),
			"tgl" => date("Y-m-d"),
			"y" => date("y"),
			"logs" => $this->session->all_userdata(),
			"logged" => $this->session->userdata('userLogged'),
		);

		
	}

	public function Over_top()
	{	
		if ( $this->logged ) 
		{
			$this->twig->display('pembelian/over_top.html', $this->content);
            
		}
		else 
		{
			redirect("auth/logout");
		}
	}


    function listData_overTop(){
    	$data = array();
    	$params = (object)$this->input->post();

         $where = "";
         $cek = $_POST['cek'];
         $keyword = $params->keyword;
         $bySearch ="";

         if($keyword['s_faktur'] != "" or $keyword['s_tglfaktur'] != "" or $keyword['s_tgljto'] != "" or $keyword['s_pelanggan'] != "" or $keyword['s_namapelanggan'] != "" or $keyword['s_tipepelanggan'] != "" or $keyword['s_umur'] != "" or $keyword['s_top'] != "" or $keyword['s_carabayar'] != "" or $keyword['s_acu'] != "" or $keyword['s_acu2'] != "" or $keyword['s_total'] or $keyword['s_salesman'] != "" or $keyword['s_namasalesman'] ){
				// log_message('error','xxxx');
				$bySearch = " where (NoFaktur like '%".$keyword['s_faktur']."%' and Tglfaktur like '%".$keyword['s_tglfaktur']."%' and TglJtoFakturHit like '%".$keyword['s_tgljto']."%' and Pelanggan like '%".$keyword['s_pelanggan']."%' and NamaPelanggan like '%".$keyword['s_namapelanggan']."%' and TipePelanggan like '%".$keyword['s_tipepelanggan']."%' and umurJT0 like '%".$keyword['s_umur']."%' and TOP like '%".$keyword['s_top']."%' and CaraBayar like '%".$keyword['s_carabayar']."%' and Acu like '%".$keyword['s_acu']."%' and Acu2 like '%".$keyword['s_acu2']."%' and Total like '%".$keyword['s_total']."%' and saldo like '%".$keyword['s_saldo']."%' and Salesman like '%".$keyword['s_salesman']."%' and NamaSalesman like '%".$keyword['s_namasalesman']."%') ";

		}


         $draw=$_REQUEST['draw'];

        $length=$_REQUEST['length'];

        $start=$_REQUEST['start'];

        // $search=$_REQUEST['search']["value"];
        $query1= $this->Model_over_top->listData_overTop($bySearch,$cek);


        $total=$query1->num_rows();

        $output=array();

        $output['draw']=$draw;

        $output['recordsTotal']=$output['recordsFiltered']=$total;

        $output['data']=array();


       

        $query= $this->Model_over_top->listData_overTop2($bySearch,$cek,$length,$start);
  
        if($bySearch!=""){
        $jum=$query1;
        $output['recordsTotal']=$output['recordsFiltered']=$jum->num_rows();
        }


        $i=$start+1;
        foreach ($query->result_array() as $r) {

            $NoFaktur = str_replace("/", "-", $r['NoFaktur']);
        	if ($r['alasan_jto'] == '') {
            	$update = '<button class="btn btn-success" id="btn-update-'.$NoFaktur.'" onclick=update_one('."'".$NoFaktur."'".')>Simpan</button>';
            }else if ($this->userGroup != 'CabangInkaso' AND $r['alasan_jto'] != '') {
                $update = '<button class="btn btn-warning" id="btn-update-'.$NoFaktur.'" onclick=update_one('."'".$NoFaktur."'".')>Update</button>';
            }else{ 
            	$update = ''; 
            }

            $background = "";

            if ($r['umurJT0'] >= 37 AND $r['alasan_jto'] == '') {
            	$background = '<span style="color:red;">';
            }

            $checked = '';
            if ($this->userGroup != 'CabangInkaso') {
                $checked = '<input type="checkbox" class="aa form-control" onchange="ceklis(this)"  name="cekfaktur" id="checkbox-'.$i.'" value="'.$NoFaktur.'">';
                $list_alasan = $this->list_alsanJTO(substr($r['alasan_jto'], 0,-10),$NoFaktur);
                $tgl_alasan = '<input type="date" id="txt-tgl-'.$NoFaktur.'" value="'.substr($r['alasan_jto'], -10).'">';

               // $checked = "1";
            }else if($this->userGroup == 'CabangInkaso' AND $r['alasan_jto'] == ''){
                $checked = '<input type="checkbox" class="aa form-control" onchange="ceklis(this)"  name="cekfaktur" id="checkbox-'.$i.'" value="'.$NoFaktur.'">';
                $list_alasan = $this->list_alsanJTO(substr($r['alasan_jto'], 0,-10),$NoFaktur);
                $tgl_alasan = '<input type="date" id="txt-tgl-'.$NoFaktur.'" value="'.substr($r['alasan_jto'], -10).'">';
                // $checked = "2";
            }else{
                $list_alasan = substr($r['alasan_jto'], 0,-10);
                $tgl_alasan = substr($r['alasan_jto'], -10);
            }

            $output['data'][]=array(
                    $i,
                    $checked
                    ,
                    $background.$r['NoFaktur'],
                    $r['Tglfaktur'],
                    $r['TglJtoFakturHit'],
                    $r['Pelanggan'],
                    $r['NamaPelanggan'],
                    $r['TipePelanggan'],
                    $r['Salesman'],
                    $r['NamaSalesman'],
                    $r['umurJT0'],
                    $r['TOP'],
                    $r['CaraBayar'],
                    $r['Acu'],
                    $r['Acu2'],
                    number_format($r['Total'],2),
                    number_format($r['saldo'],2),
                    $list_alasan,
                    // $tgl_alasan,
                    $update,
                );
        $i++;
        }

        echo json_encode($output);
    }

    function list_alsanJTO($alasan,$NoFaktur){
    	$data = $this->Model_over_top->listData_alasan(); 
        $NoFaktur = str_replace("/", "-", $NoFaktur);
        $a ='<select class="form-control selectpicker" id="txt-alasan-'.$NoFaktur.'"  data-live-search="true"  style="width:150px"> ';

        if ($alasan != '') {
	        $a .='<option value="'.$alasan.'">'.$alasan.'</option>';
        }else{
        	$a .='<option value="">Pilih</option>';
        }
            foreach ($data as $r) {
                $a .='<option value="'.$r->keterangan.'">'.$r->keterangan.'</option>';
            }
            $a .='
            </select>';
        return $a;
    }

    public function update_over_one(){
		if ($this->logged) 
		{
			$no = str_replace("-", "/", $_POST['no']);
			$alasan = $_POST['alasan'];
			$result = $this->Model_over_top->update_one($no,$alasan);
			echo json_encode(array("status" => $result));
		}
		else
		{
			redirect("auth/");
		}
	}

	public function update_over_all(){
		if ($this->logged) 
		{
			$data = $this->input->post();
			// print_r($data);
			// exit();
			$result = $this->Model_over_top->update_all($data);
			echo json_encode(array("status" => $result));
		}
		else
		{
			redirect("auth/");
		}
	}

}