<?php

class getSessToken {
    
    public $sess_token;
    
    public function createSessToken($user_data){
        $rand_string = $this->generateRandomString(20);
          
        $hex_string = $this->strtohex($user_data);
        $our_str = $hex_string.$rand_string;
        $our_str_len = strlen($our_str);
        $rand_num = rand(1,$our_str_len);
        $this->sess_token = substr_replace($our_str,$rand_string,$rand_num,0);
     }
       
     function generateRandomString($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $randomString;
        }
        function strtohex($x) {
            $s = '';
            foreach (str_split($x) as $c)
                $s.=sprintf("%02X", ord($c));
            
            return $s;
        }
}

class getOptions{
    public $rand_string;
    public $hex_string;
            
function setString($randomString){
    $this->rand_string = $randomString;
}
    
        function setHex($s){
            $this->hex_string =  $s;
        }
}

?>
