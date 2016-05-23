<?php
namespace StoneLon\PaypalIpn;

use Exception;

class PaypalIpn
{

    private $data = array();

    private $response_status = '';

    private $response = '';

    public $use_ssl = true;

    public $use_sandbox = false;

    public $timeout = 30;

    const PAYPAL_HOST = 'www.paypal.com';

    const SANDBOX_HOST = 'www.sandbox.paypal.com';


    public function validateIpn($data = null)
    {
        $encoded_data = 'cmd=_notify-validate';

        if ($data === null) {
            // use raw POST data
            if (!empty($_POST)) {
                $this->data = $_POST;
                $encoded_data .= '&'.file_get_contents('php://input');
            } else {
                throw new Exception("No POST data found.");
            }
        } else {
            $this->data = $data;
            foreach ($this->data as $key => $value) {
                $value = urlencode(stripslashes($value));
                $encoded_data .= "&$key=$value";
            }
        }
        $this->fsockPost($encoded_data);

        if (strpos($this->response_status, '200') === false) {
            throw new Exception("Invalid response status: ".$this->response_status);
        }

        if (strpos($this->response, "VERIFIED") !== false) {
            return true;
        } elseif (strpos($this->response, "INVALID") !== false) {
            return false;
        } else {
            throw new Exception("Unexpected response from PayPal.");
        }
    }

    public function fsockPost($encoded_data)
    {
        if ($this->use_ssl) {
            $uri = 'ssl://'.$this->getPaypalHost();
            $port = '443';
            $this->post_uri = $uri.'/cgi-bin/webscr';
        } else {
            $uri = $this->getPaypalHost();
            $port = '80';
            $this->post_uri = 'http://'.$uri.'/cgi-bin/webscr';
        }
        $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);

        if (!$fp) {
            // HTTP ERROR
            throw new Exception("fsockopen error: [$errno] $errstr");
        }
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: ".$this->getPaypalHost()."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($encoded_data)."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        fputs($fp, $header.$encoded_data."\r\n\r\n");

        while(!feof($fp)) {
            if (empty($this->response)) {
                // extract HTTP status from first line
                $this->response .= $status = fgets($fp, 1024);
                $this->response_status = trim(substr($status, 9, 4));
            } else {
                $this->response .= fgets($fp, 1024);
            }
        }
        fclose($fp);
    }

    private function getPaypalHost() {
        if ($this->use_sandbox) return self::SANDBOX_HOST;
        else return self::PAYPAL_HOST;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getResponseStatus() {
        return $this->response_status;
    }

}