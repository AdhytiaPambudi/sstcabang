<?php 
 
class Upload_file{
 
 	public function ftp($source=null,$target=null){
		echo "Nama saya adalah malasngoding !";
	}
 
	public function sftp($source=null,$target=null){
		$server = "119.235.19.138";
        $serverPort = "22";
        $serverUser = "serversst";
        $serverPassword = "Sapta777*x";
        $connection = ssh2_connect($server, $serverPort);
        if(ssh2_auth_password($connection, $serverUser, $serverPassword)){
            ssh2_scp_send($connection, $source, $target, 0777);
            ssh2_exec($connection, 'exit');
            // return true;
        }
        return "asdasdasd";
	}
}