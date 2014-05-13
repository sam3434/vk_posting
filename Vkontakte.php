<?php

if (!class_exists('Zend_Http_Client')){
    require_once(dirname(__FILE__).'/Zend/Http/Client.php');
    require_once(dirname(__FILE__).'/Zend/Http/Cookie.php');
    require_once(dirname(__FILE__).'/Zend/Http/CookieJar.php');
    require_once(dirname(__FILE__).'/Zend/Http/Exception.php');
    require_once(dirname(__FILE__).'/Zend/Http/Response.php');
    require_once(dirname(__FILE__).'/Zend/Http/UserAgent.php');
}

class Vkontakte
{
    protected $groupId, $appId, $secretKey, $accessToken, $accessSecret, $photo_files;


    /**
     * @param int $groupId
     * @param int $appId
     * @param string $secretKey
     */
    public function __construct($appId, $secretKey, $userId, $userPassword)
    {
        $this->photo_files = array();
        //$this->groupId = $groupId;
        $this->appId = $appId;
        $this->secretKey = $secretKey;

        $this->userId = $userId;
        $this->userPassword = $userPassword;
    }

    /**
     *
     * @param string $accessToken
     * @param string $accessSecret
     */
    public function setAccessData($accessToken, $accessSecret)
    {
        $this->accessToken = $accessToken;
        $this->accessSecret = $accessSecret;
    }

    //http-авторизация на сайте
    function login(){
        $data = array(
            '_origin' => 'http://vk.com',
            'act' => 'login',
            'captcha_key' => '',
            'captcha_sid' => '',
            'email' => $this->userId,
            'pass' => $this->userPassword,
            'expire' => 0,
            'ip_h' => '82dac14382eb851e12',
            'role' => 'al_frame');

        $uri = "https://login.vk.com/?act=login";
        $client = $this->getHttpClient($uri);
        $client->resetParameters();
        $client->setCookie('remixlang', 0);
        $client->setCookie('remixflash', '11.4.402');
        $client->setCookie('remixdt', 0);
        $client->setParameterPost($data);
        try {
            $res = $client->request($client::POST);
        } catch (Exception $exc) {
            echo "We catch some exception";
            print_r($client->getCookieJar());
            //debug($client->getCookieJar());
            echo $exc->getTraceAsString();
        }
    }

    //авторизация oauth, получение токена
    function auth($callback){
        $callback = 'http://api.vk.com/blank.html';
        //$callback = 'http://oauth.vk.com/blank.html';
        //$uri = "http://api.vkontakte.com/oauth/authorize?client_id={$this->appId}&scope=notify,friends,photos,audio,video,docs,notes,pages,wall,groups,ads&redirect_uri={$callback}&response_type=code";
        $uri = "https://oauth.vk.com/authorize?client_id={$this->appId}&scope=notify,friends,photos,audio,video,docs,notes,pages,wall,groups,ads&redirect_uri={$callback}&response_type=code";

        $client = $this->getHttpClient($uri);
        $res = $client->request();
        $body = $res->getBody();

        //grant access
        preg_match('/location.href = "(.*?)"/i', $body, $matches);
        $grantHref = $matches[1];
        $client = $this->getHttpClient($grantHref);
        $res = $client->request($client::POST);
        $code = str_replace('code=', '', $client->getUri()->getFragment());

        return $code;
    }

    function getSecret($callback, $code){

        //$uri = "https://api.vkontakte.com/oauth/access_token?client_id={$this->appId}&redirect_uri={$callback}&client_secret={$this->secretKey}&code=$code";
        $uri = "https://oauth.vk.com/access_token?client_id={$this->appId}&redirect_uri={$callback}&client_secret={$this->secretKey}&code=$code";
        $client = $this->getHttpClient($uri);
        $res = $client->request();

        return json_decode($res->getBody(), true);
    }

    public function getSslPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param string $method
     * @param mixed $parameters
     * @return mixed
     */
    public function callMethod($method, $parameters)
    {
        if (!$this->accessToken) return false;
        if (is_array($parameters)) $parameters = http_build_query($parameters);
        $queryString = "/method/$method?$parameters&access_token={$this->accessToken}";
        $querySig = md5($queryString . $this->accessSecret);
        return json_decode($this->getSslPage(
            "https://api.vk.com{$queryString}&sig=$querySig"
        ));
        // return json_decode(file_get_contents(
        //     "https://api.vk.com{$queryString}"
        // ));
    }

    /**
     * @param string $message
     * @param bool $fromGroup
     * @param bool $signed
     * @return mixed
     */
    public function wallPostMsg($message, $fromGroup = true, $signed = false)
    {
        return $this->callMethod('wall.post', array(
            'owner_id' => -1 * $this->groupId,
            'message' => $message,
            'from_group' => $fromGroup ? 1 : 0,
            'signed' => $signed ? 1 : 0,
        ));
    }

    /**
     * @param string $attachment
     * @param null|string $message
     * @param bool $fromGroup
     * @param bool $signed
     * @return mixed
     */
    public function wallPostAttachment($attachment, $message = null, $fromGroup = true, $signed = false)
    {
        $result = $this->callMethod('wall.post', array(
            'owner_id' => -1 * $this->groupId,
            'attachments' => strval($attachment),
            'message' => $message,
            'from_group' => 0,
            'signed' => $signed ? 1 : 0,
        ));
        foreach ($this->photo_files as $photo) {
            unlink($photo);
        }
        $this->photo_files = array();
        return $result;
    }

    /**
     * @param string $file relative file path
     * @return mixed
     */
    public function createPhotoAttachment($file, $b_files = false)
    {
        $result = $this->callMethod('photos.getWallUploadServer', array(
            'gid' => $this->groupId
        ));

        $ch = curl_init($result->response->upload_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        //     'photo' => '@' . getcwd() . '/' . $file
        // ));
        if (!$b_files)
        {
            $imgdata = file_get_contents($file);
            $imgformat = end(explode('.', $file));
            $imgfilename = __DIR__ . '/' . rand(199122, 1992314) . '.' . $imgformat;
            $this->photo_files[] = $imgfilename;
            $imghandle = fopen($imgfilename, 'w');
            fwrite($imghandle, $imgdata);
            fclose($imghandle);
        }
        else
        {
            $imgfilename = $file;
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'photo' => '@' . $imgfilename . ';type=image/' . $imgformat
        ));

        // curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        //     'photo' => '@' . $file
        // ));
        
        //debug('@' . getcwd() . '/' . $file);

        if (($upload = curl_exec($ch)) === false) {
            throw new Exception(curl_error($ch));
        }

        
        curl_close($ch);
        $upload = json_decode($upload);
        $result = $this->callMethod('photos.saveWallPhoto', array(
            'server' => $upload->server,
            'photo' => $upload->photo,
            'hash' => $upload->hash,
            'gid' => $this->groupId,
        ));

        
        return $result->response[0]->id;
    }

    public function combineAttachments()
    {
        $result = '';
        if (func_num_args() == 0) return '';
        foreach (func_get_args() as $arg) {
            $result .= strval($arg) . ',';
        }
        return substr($result, 0, strlen($result) - 1);
    }

    protected static $client;

    /**
     *
     * @param type $uri
     * @return Zend_Http_Client
     */
    function getHttpClient($uri){
        return self::_getHttpClient($uri);
    }

    function _getHttpClient($uri){
        if (empty(self::$client)){
            $client = new Zend_Http_Client(null, $config = array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => array(
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => 0,
                    CURLOPT_HEADER => 1),
            ));
            $jar = new Zend_Http_CookieJar;
            $client->setCookieJar($jar);
            self::$client = $client;
        }
        $client = self::$client;

        $client->resetParameters();
        $client->setUri($uri);

        $host = parse_url($uri);
        $host = $host['host'];
        $client->setHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Connection' => 'keep-alive',
            'Host' => $host,
            'Referer' => 'http://vk.com/login?act=mobile',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0 FirePHP/0.7.1',
            'x-insight' => 'activate'
        ));

        return $client;
    }

    function vkrepost($groups, $message, $images = null, $files = null){
                
        $this->login();

        $callback = 'http://api.vk.com/blank.html';
        $code = $this->auth($callback);
        $secret = $this->getSecret($callback, $code);
        
        $this->setAccessData($secret['access_token'], $secret['secret']);

        foreach ($groups as $group) {
            $this->groupId = $group;
            if (!empty($images) || !empty($files)){
                $uploads = array();
                if (!empty($images)){
                    foreach ($images as $image){
                        preg_match('/photo.*?$/i', $image, $matches);
                        if (isset($matches[0]) && strpos($image, "vk.com/")!==false)
                        {   
                            $uploads[] = $matches[0];
                        }
                        else
                        {
                            $uploads[] = $this->createPhotoAttachment($image);
                        }                        
                    }
                }
                if (!empty($files))
                {
                    foreach ($files as $file){
                        $uploads[] = $this->createPhotoAttachment($file, true);
                    }    
                }
                
                $chunks = array_chunk($uploads, 10);//разбиваем картинки по 10 штук - ограничение VK
                foreach ($chunks as $chunk){
                    $attachString = join(',', $chunk);
                    $this->wallPostAttachment($attachString, $message);
                }
            }
            else{
                $this->wallPostMsg($message);
            }
        }
    }
}