<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* 
*/
class Auth extends MX_Controller
{
	
	var $content;
	var $logged;
	var $logs;	

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Model_auth');
		$this->logs = $this->session->all_userdata();
		$this->logged = $this->session->userdata('userLogged');
		$this->username = $this->session->userdata('username');
		$this->userGroup = $this->session->userdata('user_group');
		$this->Emailx = $this->session->userdata('email');
		// $this->userLogged = $this->session->userdata('userLogged');
		$this->content = array(
			"base_url" => base_url(),
			"logs" => $this->session->all_userdata(),
		);
	}

	public function index()
	{	
		if ( $this->logged && $this->kategori) 
		{
			redirect("main/");
		}
		else 
		{
			$cek = $this->Model_auth->getcabuser();
			$this->content['cekCabang'] = $cek[0]->Cabang;
			$this->twig->display("login.html", $this->content);
		}
	}

	public function login()
	{	
		if ( $this->logged ) 
		{
			redirect("main/");
		}
		else 
		{
			if($_POST)
			{
				$params = (object)$this->input->post();
				$valid = $this->Model_auth->loginAuth($params->email, $params->password);
				if ($valid)
				{
					echo json_encode(array("status" => TRUE));
				}
				else
					echo json_encode(array("status" => FALSE));
			}		
		}
	}

	public function logout()
	{
		$this->db->set("total_userlogged",$this->session->userdata("total_userlogged") - 1);
		$this->db->set("userLogged",false);
        $this->db->where("username",$this->session->userdata("username"));
        $this->db->update("mst_user");
		$valid = $this->session->sess_destroy();
		session_destroy();
		redirect("/");			
	}
}
?>