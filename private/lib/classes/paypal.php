<?php
class paypal {
    var $postdata=array();
    var $response=array();
    var $username;
    var $password;
    var $signature=NULL;
    var $certfile=NULL;
    var $proxy=NULL;
    var $sandbox;
    var $sandbox_url="https://api.sandbox.paypal.com/nvp";
    var $live_url="https://api.paypal.com/nvp";
    
    //initialize 
    function __construct($user, $pw, $cert, $sandbox=false, $proxy=NULL) {
        $this->username=$user;
        $this->password=$pw;
        if(is_file($cert)) $this->certfile=$cert;
        else $this->signature=$cert;
    
        if($proxy) $this->proxy=$proxy;
        $this->sandbox=$sandbox;
    }
    
    //add values to the array
    function addvalue($key, $val, $limit=NULL) {
        $v=$val;
        if(is_numeric($limit)) $v=substr($v,0,$limit);
        $this->postdata[$key]=urlencode($v);
    }
    
    //clear the array for a new call
    function resetdata() {
        $this->postdata=array();
    }

    function call_paypal($showurl=false) {
        $this->postdata['USER']=urlencode($this->username);
        $this->postdata['PWD']=urlencode($this->password);
        if($this->signature) $this->postdata['SIGNATURE']=urlencode($this->signature);
        if(!isset($this->postdata['VERSION'])) $this->postdata['VERSION']=urlencode(50.0);

        $url=($this->sandbox) ? $this->sandbox_url : $this->live_url;
        $nvp=NULL;
        foreach($this->postdata as $k => $v):
            $nvp.="$k=$v&";
        endforeach;
    
        if(!$nvp) return false;
        
        //strip out the last character, which is a &
        $nvp=substr($nvp, 0, -1);
        if($showurl) echo $nvp;
        
        //curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($this->certfile) curl_setopt($ch, CURLOPT_SSLCERT, $this->certfile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if($this->proxy) {
            curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt ($ch, CURLOPT_PROXY,$this->proxy);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvp);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $mydata = curl_exec($ch);
        if(curl_error($ch)) {
            //$this->save_error(curl_error($ch));
            return false;
        }
        curl_close ($ch);

        return $this->process_response($mydata);
    }

    function process_response($str) {
        $data=array();
        $x=explode("&", $str);
        foreach($x as $val):
            $y=explode("=", $val);
            $data[$y[0]]=urldecode($y[1]);
        endforeach;
        return $data;
    }

    function save_error($msg) {
        //do something here, like save it to a database
    }
}
?>
