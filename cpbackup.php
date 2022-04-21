<?php

class cPbackup{
    /*
     * define all variable that use for backup process
     */

    public $proxy=FALSE;
    public $baseurl;
    public $hostname;
    public $port="2083";
    public $ssl=TRUE;
    public $cpuser;
    public $cppasswd;
    public $database=TRUE;
    public $max_number_of_file=10;
    public $bk_dest='homedir';
    public $bk_email;
    public $bk_sendemail=FALSE;
    public $bk_server;
    public $bk_user;
    public $bk_pass;
    public $bk_port;
    public $bk_rdir;
    public $messages;
    private $curl;
    private $result;
    private $get;
    private $token;
    private $themes;
    private $bk_url;
    private $data;
    private $fields;
    public $useragent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36";

    /*
     * Function for login into cPanel Account
     */
    private function cPlogin(){

        if($this->ssl){
            $this->base_url ="https://".$this->hostname;
        }
        else{
            $this->base_url ="http://".$this->hostname;
        }

        $url = $this->base_url.":".$this->port."/login/";

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($this->curl, CURLOPT_HEADER,0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        if($this->proxy)
            curl_setopt($this->curl, CURLOPT_PROXY, '127.0.0.1:8888');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, "user=".$this->cpuser."&pass=".$this->cppasswd);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        $this->result = curl_exec($this->curl);

        /*
         * get cPanel Security Token Access and themes that used from after login process above
         */
        $this->get=  explode("/", $this->result);
        $this->token = $this->get[2];
        $this->themes = $this->get[4];
    }

    /*
     * Function backup
     */
    private function fullBackupProcess($token,$themes){

        $this->bk_url = $this->base_url.":".$this->port."/".$token."/frontend/".$themes."/backup/wizard-dofullbackup.html";

        /*
         * check if want to send email notification when backup process is complete
         */
        if($this->bk_sendemail){
            $emailr=1;
            $emailr0=0;
        }
        else{
            $emailr=0;
            $emailr0=1;
        }

        /*
         * set the post backup data for sent into backup url
         */
        $this->data=array("dest"=>$this->bk_dest,
        "email_radio"=>$emailr,
        "email_radio_0"=>$emailr0,
        "email"=>$this->bk_email,
        "server"=>$this->bk_server,
        "user"=>$this->bk_user,
        "pass"=>$this->bk_pass,
        "port"=>$this->bk_port,
        "rdir"=>$this->bk_rdir);

        /*
         * set data to use '&' delimiter between parameter and it value
         */
        $fields_string="";
        foreach($this->data as $key=>$value){
            $fields_string .= $key.'='.$value.'&';
        }
        $this->fields = rtrim($fields_string,'&');

        /*
         * process backup account
         */
        curl_setopt($this->curl,CURLOPT_URL,$this->bk_url);
        curl_setopt($this->curl,CURLOPT_POST,count($this->data));
        curl_setopt($this->curl,CURLOPT_POSTFIELDS,$this->fields);
        curl_setopt($this->curl,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl,  CURLOPT_USERAGENT , $this->useragent);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        $this->result = curl_exec($this->curl);
        curl_close($this->curl);
    }

    /*
     * Function docPbackup for start processing backup
     */
    public function fullBackup(){

        $this->cPlogin();
        $this->fullBackupProcess($this->token, $this->themes);
    }

    /*
     * Function docPbackup for start processing backup
     */
    private function databaseBackupProcess($token,$themes){

        $this->messages = [];

        $customer_dir = 'G:/My Drive/PERSONAL/Backups/'.$this->hostname;
        if (!file_exists($customer_dir)) {
            mkdir($customer_dir, 0777, true);
        }

        if(is_array($this->database)) {
            foreach ($this->database as $key => $dbname) {

                $database_dir = $customer_dir.'/'.$dbname;
                if (!file_exists($database_dir)) {
                    mkdir($database_dir, 0777, true);
                }

                $filename = date('Y-m-d_His').'.sql.gz';
                $this->bk_url = $this->base_url.":".$this->port."/".$token."/getsqlbackup/".$dbname.".sql.gz";
                $this->getGzipFile($this->bk_url, $filename, $database_dir);

                // start get all files and order by time desc, and delete file old
                $filetime = [];
                $files = glob($database_dir."/*");
                foreach ($files as $file) {
                    $filetime[$file] = filemtime($file);
                }
                arsort($filetime);
                $fileold = array_slice($filetime, $this->max_number_of_file);
                foreach ($fileold as $key_ => $value) {
                    unlink($key_);
                }// end delete file

                $filesize = filesize($database_dir.'/'.$filename);
                $this->messages[$key]['result'] = sprintf("%s/%s (%s)", $dbname, $filename, $this->formatSizeUnits($filesize));
                $this->messages[$key]['size'] = $filesize;
            }
        } elseif($this->database === true) {
            $this->messages[0]['result'] = 'the feature is still under construction, please specify database name';
            $this->messages[0]['size'] = 0;
        }

        curl_close($this->curl);
    }

    /*
     * Function docPbackup for start processing backup
     */
    private function getGzipFile($url_download, $filename, $path) {

        $fp = fopen ($path.'/'.$filename, 'wb');

        curl_setopt($this->curl, CURLOPT_URL, $url_download);
        curl_setopt($this->curl, CURLOPT_USERAGENT , $this->useragent);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        curl_exec($this->curl);

        fclose($fp);
    }

    public function formatSizeUnits($bytes) {

        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /*
     * Function docPbackup for start processing backup
     */
    public function databaseBackup(){

        $this->cPlogin();
        $this->databaseBackupProcess($this->token,$this->themes);

        return $this->messages;
    }
}
