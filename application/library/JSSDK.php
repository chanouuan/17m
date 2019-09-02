<?php
class JSSDK {

    private $appId;

    private $appSecret;

    public function __construct ($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 上传多媒体文件
     */
    public function uploadMedia ($file, $type = 'voice')
    {
        $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($file)) {
            return error('file error');
        }
        $_access_token = $this->getAccessToken();
        if ($_access_token['errorcode'] !== 0) {
            return $_access_token;
        }
        $accessToken = $_access_token['data']['access_token'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $accessToken . '&type=' . $type);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'media' => new \CURLFile($file)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return error(curl_errno($ch));
        }
        curl_close($ch);
        $result = json_decode($result, true);
        if ($result['errcode']) {
            return error($result['errmsg']);
        }
        return success($result['media_id']);
    }

    /**
     * 下载多媒体文件
     */
    public function getMedia ($media_id, $ext = 'amr')
    {
        $_access_token = $this->getAccessToken();
        if ($_access_token['errorcode'] !== 0) {
            return $_access_token;
        }
        $accessToken = $_access_token['data']['access_token'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=' . $accessToken . '&media_id=' . $media_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $media_file = curl_exec($ch);
        if (curl_errno($ch)) {
            return error(curl_errno($ch));
        }
        curl_close($ch);
        if (!$media_file) {
            return error('data error');
        }
        if ($ret = json_decode($media_file, true)) {
            return error($ret['errmsg']);
        }
        $name = date('Ymd') . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10) . '.' . $ext;
        $dir = date('Ym');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $dir;
        mkdirm($path);
        @file_put_contents($path . DIRECTORY_SEPARATOR . $name, $media_file);
        unset($media_file);
        return success('upload/' . $dir . '/' . $name);
    }
    
    /**
     * 生成网页授权State
     */
    private function getState ()
    {
        return str_replace('%', '0123456789', encode_formhash());
    }
    
    /**
     * 解析网页授权State
     */
    private function parseState ($state)
    {
        return str_replace('0123456789', '%', $state);
    }

    /**
     * 网页授权
     */
    public function connectAuth ($redirect_url)
    {
        session_start();
        $state = $_GET['state'];
        $code = $_GET['code'];
        $session_state = $_SESSION['state'];
        if (!$code || !$state) {
            $_SESSION['state'] = md5(uniqid(rand(), TRUE));
            $parse_url = parse_url($redirect_url);
            if ($parse_url['query']) {
                parse_str($parse_url['query'], $output_query);
                unset($output_query['state'], $output_query['code']);
                if (empty($output_query)) {
                    $redirect_url = $parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'];
                } else {
                    $redirect_url = $parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'] . '?' . http_build_query($output_query);
                }
            }
            $authorize_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . rawurlencode($redirect_url) . '&response_type=code&scope=snsapi_base&state=' . $_SESSION['state'] . '#wechat_redirect';
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Location: ' . $authorize_url, true, 301);
            exit();
        }
        unset($_GET['state'], $_GET['code']);
        $parse_url = parse_url($_SERVER['REQUEST_URI']);
        if ($parse_url['query']) {
            parse_str($parse_url['query'], $output_query);
            unset($output_query['state'], $output_query['code']);
            if (empty($output_query)) {
                $_SERVER['REQUEST_URI'] = $parse_url['path'];
            } else {
                $_SERVER['REQUEST_URI'] = $parse_url['path'] . '?' . http_build_query($output_query);
            }
        }
        if ($state != $session_state) {
            error_log('[' . date('Y-m-d H:i:s') . '] ' . $_SERVER['REQUEST_URI'] . "\r\n", 3, APPLICATION_PATH . '/log/jssdk.log');
            return error('正在跳转！');
        }
        $_SESSION['state'] = null;
        unset($_SESSION['state']);
        $userToken = $this->getSnsapiBase($code);
        if ($userToken['errorcode'] !== 0) {
            return $userToken;
        }
        $userInfo = $this->getUserInfo($userToken['data']['access_token'], $userToken['data']['openid']);
        if ($userInfo['errorcode'] !== 0) {
            return $userInfo;
        }
        return success(array_merge($userToken['data'], $userInfo['data']));
    }

    /**
     * 通过code换取网页授权access_token
     */
    public function getSnsapiBase ($code)
    {
        try {
            $reponse = $this->httpGet('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appId . '&secret=' . $this->appSecret . '&code=' . $code . '&grant_type=authorization_code');
        }
        catch (Exception $e) {
            return error($e->getMessage());
        }
        if ($reponse['errcode']) {
            return error($reponse['errmsg']);
        }
        return success($reponse);
    }

    /**
     * 获取微信用户信息
     */
    public function getUserInfo ($accessToken, $openid)
    {
        // 获取用户基本信息(UnionID机制)，所以accessToken用接口凭证
        $_access_token = $this->getAccessToken();
        if ($_access_token['errorcode'] !== 0) {
            return $_access_token;
        }
        $accessToken = $_access_token['data']['access_token'];
        try {
            $reponse = $this->httpGet('https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN');
        }
        catch (Exception $e) {
            return error($e->getMessage());
        }
        if ($reponse['errcode']) {
            return error($reponse['errmsg']);
        }
        return success($reponse);
    }

    public function getSignPackage ()
    {
        $_jsapiTicket = $this->getJsApiTicket();
        if ($_jsapiTicket['errorcode'] !== 0) {
            return $_jsapiTicket;
        }
        $jsapiTicket = $_jsapiTicket['data']['jsapi_ticket'];
        
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        
        $signature = sha1($string);
        
        $signPackage = array(
                "appId" => $this->appId, 
                "nonceStr" => $nonceStr, 
                "timestamp" => $timestamp, 
                "url" => $url, 
                "signature" => $signature, 
                "rawString" => $string
        );
        return success($signPackage);
    }

    public function getJsApiTicket ()
    {
        $res = DB::getInstance()->table('~wx_access~')->field('token')->where('type = "jsapi_ticket" and appid = "' . $this->appId . '"')->find();
        if (empty($res)) {
            DB::getInstance()->insert('~wx_access~', array(
                    'type' => 'jsapi_ticket', 
                    'appid' => $this->appId, 
                    'expire' => TIMESTAMP - 1
            ));
        }
        if (DB::getInstance()->update('~wx_access~', array(
                'expire' => '~expire+30'
        ), 'type = "jsapi_ticket" and appid = "' . $this->appId . '" and expire < ' . TIMESTAMP)) {
            $_access_token = $this->getAccessToken();
            if ($_access_token['errorcode'] !== 0) {
                return $_access_token;
            }
            $accessToken = $_access_token['data']['access_token'];
            try {
                $reponse = $this->httpGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken");
            }
            catch (Exception $e) {
                return error($e->getMessage());
            }
            DB::getInstance()->update('~wx_access~', array(
                    'expire' => TIMESTAMP + $reponse['expires_in'] - 100, 
                    'token' => $reponse['ticket']
            ), 'type = "jsapi_ticket" and appid = "' . $this->appId . '"');
            return success(array(
                    'jsapi_ticket' => $reponse['ticket']
            ));
        }
        return success(array(
                'jsapi_ticket' => $res['token']
        ));
    }

    public function getAccessToken ()
    {
        $res = DB::getInstance()->table('~wx_access~')->field('token')->where('type = "access_token" and appid = "' . $this->appId . '"')->find();
        if (empty($res)) {
            DB::getInstance()->insert('~wx_access~', array(
                    'type' => 'access_token', 
                    'appid' => $this->appId, 
                    'expire' => TIMESTAMP - 1
            ));
        }
        if (DB::getInstance()->update('~wx_access~', array(
                'expire' => '~expire+30'
        ), 'type = "access_token" and appid = "' . $this->appId . '" and expire < ' . TIMESTAMP)) {
            try {
                $reponse = $this->httpGet("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret");
            }
            catch (Exception $e) {
                return error($e->getMessage());
            }
            DB::getInstance()->update('~wx_access~', array(
                    'expire' => TIMESTAMP + $reponse['expires_in'] - 100, 
                    'token' => $reponse['access_token']
            ), 'type = "access_token" and appid = "' . $this->appId . '"');
            return success(array(
                    'access_token' => $reponse['access_token']
            ));
        }
        return success(array(
                'access_token' => $res['token']
        ));
    }

    private function createNonceStr ($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function httpGet ($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $reponse = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl), 0);
        }
        curl_close($curl);
        if (!$_reponse = json_decode($reponse, true)) {
            throw new Exception($reponse, 0);
        }
        return $_reponse;
    }

}