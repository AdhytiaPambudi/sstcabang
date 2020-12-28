<?php
class Model_auth extends CI_Model {
    
    function __construct(){
        parent::__construct();
    }
    
    public function loginAuth($email, $password)
    {
        $valid = false;         
        $password = md5($password);
        
        $check = $this->db->get_where("mst_user", array("username" => $email,"password" => $password));
            
        if ($check->num_rows() > 0) {            
            $data = $check->row();
                
                $cabang = $this->db->get_where("mst_closing", array("cabang" => $data->Cabang));
                if ($cabang->num_rows() > 0) {   
                    $port = $cabang->row('port');
                }else{
                    $port = 0000;
                }

                $session = array(
                    'id' => $data->id,
                    'username' => $data->username,
                    'emailx' => $data->email,
                    'password' => $data->password,
                    'userGroup' => $data->user_group,
                    'cabang' => $data->Cabang,
                    'region' => $data->Region,
                    'status' => $data->status,
                    'port' => $port,
                    'total_userlogged' => $data->total_userlogged,
                    'userLogged' => TRUE
                );
                $valid = TRUE;
                $this->session->set_userdata($session);
                $this->db->set("total_userlogged",$this->session->userdata("total_userlogged") + 1);
                $this->db->set("userLogged",$this->session->userdata("userLogged"));
                $this->db->where("username",$this->session->userdata("username"));
                $this->db->update("mst_user");
        }

        return $valid;      
    }   

    public function getcabuser (){
        $query = $this->db->query("SELECT Cabang FROM mst_user WHERE Cabang NOT IN('Pusat','') AND Cabang IS NOT NULL")->result();

        return $query;
    }

    

    // public function gantiPassword($password)
    // {
    //     $log = $this->session->all_userdata();
    //     $userId = $this->session->userdata('userId');
    //     $valid = false; 
    
    //     $this->db->set("password", $password);
    //     $this->db->where("id_user", $userId);
    //     $valid = $this->db->update("mst_user");
            
    //     $session = array(
    //           'userPassword' => $password,
    //     );
            
    //     $this->session->set_userdata($session);
        
    //     return $valid;
    // }
}
?>