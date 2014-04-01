<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 08/02/13
 * Time: 6:43 PM
 */

namespace MegaCurl;


class MegaCurl {

    const CONNECTION_AUTO_CLOSE = 1;

    public $response;

    private $cookie_file;

    private $ch = null;

    private $options = array();

    private $headers = array();

    public $errCode;

    public $errString;

    public $info;

    /**
     * @param null $url
     */
    public function __construct($url = null)
    {
        if (!empty($url)) {
            $this->setRequestUrl($url);
        }
        return $this;
    }

    /**
     * @param $url
     * @return $this
     * @throws \Exception
     */
    public function setRequestUrl($url) {
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->close();
            throw new \Exception('It\'s joke? \''.$url.'\' not URL.');
        }
        $this->ch = curl_init($url);
        return $this;
    }

    /**
     * @param $cookieFile
     * @return bool
     */
    private function createCookieFile($cookieFile) {
        try {
            $file = new \SplFileObject($cookieFile, "w");
            $file->fwrite("");
            chmod($cookieFile, 0777);
            return true;
        }catch (\Exception $e) {
            echo $e->getCode();
            echo $e->getMessage();
        }
        return false;
    }

    /**
     * Call this method before Execute() for save session. e.g login, shopping cart or somewhere else.
     * If you need start session for each users separate, put file name for every user. File name must
     * be unique;
     *
     * @param null $c_file
     * @return $this
     */
    public function oneSession($c_file = null) {
        if($c_file !== null) {$this->cookie_file = $c_file;}
        if(!file_exists($this->cookie_file)) {
            $this->createCookieFile($this->cookie_file);
        }
        $this->SetOptions(array(
                'COOKIEFILE' => $this->cookie_file,
                'COOKIEJAR' => $this->cookie_file,
                'USERAGENT' => $_SERVER['HTTP_USER_AGENT']
            ));
        return $this;
    }

    /**
     * If you need start session for each users separate, put file name for every user. File name must
     * be unique;
     *
     * @param null $c_file
     * @return bool
     */
    public function renewSession($c_file = null) {
        if($c_file !== null) {$this->cookie_file = $c_file;}
        unlink($this->cookie_file);
        return $this->createCookieFile($this->cookie_file);
    }

    /**
     * @param $method
     * @return $this
     * @throws \Exception
     */
    public function setHttpMethod($method) {
        if(!in_array($method, array('post', 'get', 'put', 'delete', 'head', 'options', 'connect'))) {
            $this->close();
            throw new \Exception('Are you kidding me? The are no HTTP method like - \''.$method.'\'');
        }
        $this->options[CURLOPT_CUSTOMREQUEST] = strtoupper((string) $method);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach($options as $option_code => $option_value)
        {
            if (is_string($option_code) && !is_numeric($option_code)) {
                $option_code = constant('CURLOPT_' . strtoupper($option_code));
            }
            $this->options[$option_code] = $option_value;
        }
        return $this;
    }

    /**
     * @param $header
     * @param null $content
     * @return $this
     */
    public function setHttpHeader($header, $content = null)
    {
        $this->headers[] = $content ? (string) $header.': '.(string) $content : (string) $header;
        return $this;
    }

    /**
     * @param int $autoClose
     * @return bool|mixed
     */
    public function execute($autoClose = self::CONNECTION_AUTO_CLOSE)
    {
        // Set default options if not exist
        if (!isset($this->options[CURLOPT_TIMEOUT])) $this->options[CURLOPT_TIMEOUT] = 60;
        if (!isset($this->options[CURLOPT_RETURNTRANSFER])) $this->options[CURLOPT_RETURNTRANSFER] = TRUE;
        if (!isset($this->options[CURLOPT_FAILONERROR])) $this->options[CURLOPT_FAILONERROR] = TRUE;

        if (!empty($this->headers)) $this->options[CURLOPT_HTTPHEADER] = $this->headers;

        // set options
        curl_setopt_array($this->ch, $this->options);

        // execute
        $this->response = curl_exec($this->ch);

        // fail
        if ($this->response === false) {
            $this->errCode = curl_errno($this->ch);
            $this->errString = curl_error($this->ch);
            $this->close();
            return false;
        }
        // successful
        else {
            $this->info = curl_getinfo($this->ch);
            if ($autoClose === self::CONNECTION_AUTO_CLOSE) {
                $this->close();
            }
            $this->resetAllParams();
            return $this->response;
        }
    }

    /**
     * @param array $data
     * @return bool|mixed
     */
    public function executePost(array $data) {
        $this->setOptions(array(
                'POST' => true,
                'POSTFIELDS' => http_build_query($data)
            ));
        $this->setHttpMethod('post');
        return $this->execute();
    }

    /**
     * @return bool
     */
    public function close() {
        curl_close($this->ch);
        $this->ch = null;
        return true;
    }

    /**
    * @return bool
    */
    public function resetAllParams() {
        $this->info = array();
        $this->options = array();
        $this->headers = array();
        $this->errCode = 0;
        $this->errString = '';
        return true;
    }

} 