<?php

namespace Etu\Module\BuckUTTBundle\Soap;

class SoapManager
{
    const cookie_name = 'buckutt_soap_cookie';
    private $client;
    private $wsdlName;
    private $firstUse = false;
    private $login = '';
    private $pin = 0;
    private static $cookies = array();// our session cookie for buckutt server
    private static $session = null;//our session from symfony

    public function __construct($wsdlName, $session)
    {
        $this->wsdlName = $wsdlName;
        self::$session = $session;

        $wsdl = array(
            'SBUY' => 'http://10.10.10.1:8080/SBUY.class.php?wsdl',
            'SADMIN' => 'http://10.10.10.1:8080/SADMIN.class.php?wsdl'
        );

        self::$cookies = $session->get(self::cookie_name);
        if (!self::$cookies) {
            $this->client = new \SoapClient($wsdl[$wsdlName], array("cache_wsdl" => WSDL_CACHE_NONE));
            $this->firstUse = true;
        }
        else {
            $this->client = @new \SoapClient($wsdl[$wsdlName], array("cache_wsdl" => WSDL_CACHE_BOTH));
            $this->client->__setCookie("PHPSESSID", self::$cookies[$wsdlName]);
        }
    }

    public function _login($login, $pin){
        $this->login = $login;
        $this->pin = (int)$pin;
        return $this->login($this->login, 1, $this->pin, 'etu.utt.fr');
    }

    public function __call($name, $arguments)
    {
        xdebug_disable();
        try{
            $rtn = call_user_func_array(array($this->client, $name), $arguments);
        }
        catch(\SoapFault $e){
            // We are disconnected
            //*
            if($this->_login($this->login, $this->pin) != 1)
                return false;

            $rtn = call_user_func_array(array($this->client, $name), $arguments);
            //*/
        }
        xdebug_enable();

        if($this->firstUse){
            self::$cookies[$this->wsdlName] = $this->client->_cookies["PHPSESSID"][0];
            self::$session->set(self::cookie_name, self::$cookies);
            $this->firstUse = false;
        }

        if (strpos($rtn, '","') != false) {
            $pattern = ',';
            if (!empty($rtn) && is_string($rtn)) {
                $array0 = explode(";\n", $rtn, -1);
                foreach ($array0 as $i=>$array1) {
                    $array2 = substr($array1, 1, -1);
                    $array0[$i] = explode('"'.$pattern.'"', $array2);
                    foreach ($array0[$i] as $j=>$array3) {
                        $array0[$i][$j] = $array3;
                    }
                }
                return $array0;
            }
        }
        return $rtn;
    }
}
