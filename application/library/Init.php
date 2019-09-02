<?php

class Controller {

    public function run ()
    {
        $module = getgpc('c');
        $action = getgpc('a');
        $module = empty($module) ? 'Index' : ucwords($module);
        $action = empty($action) ? 'index' : $action;
        $_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $module . '.php';
        if (!file_exists($_dir)) {
            exit($module . ' 404.c');
        }
        include ($_dir);
        $class = $module . '_' . 'Action';
        if (class_exists($class)) {
            $_referer = new $class();
            $_referer->_module = $module;
            $_referer->_action = $action;
            $_referer->__init();
            if (method_exists($class, $action)) {
                $result = $_referer->$action();
            } else {
                $result = $_referer->__notfund();
            }
            if (null !== $result) {
                $_referer->render($action . '.html', is_array($result) ? $result : []);
            }
        } else {
            exit($class . ' 404.a');
        }
    }

}

abstract class ActionPDO {

    public $_module = null;

    public $_action = null;

    public $_G = [];

    public $_jssdk = null;

    public function __construct ()
    {
        // 检查客服端类型
        define('CLIENT_TYPE', check_client());
        
        // 定义视图样式
        define('APPLICATION_STYLE', 'mobile');
        
        if (CLIENT_TYPE == 'wx') {
            session_start();
            if (empty(getgpc('token', 'C')) && !empty($_SESSION['token'])) {
                set_cookie('token', $_SESSION['token']);
            }
        }
        
        // 登录用户
        $this->_G['user'] = $this->loginCheck();
        
        // 微信登录验证
        if (CLIENT_TYPE == 'wx') {
            $this->_jssdk = new JSSDK('wxf3b8281f2a822121', 'a8de747eeb0c0554bb518a7f5cb5b21d');
            if (empty($this->_G['user'])) {
                // 微信入口
                $userInfo = $this->_jssdk->connectAuth(APPLICATION_URL . $_SERVER['REQUEST_URI']);
                if ($userInfo['errorcode'] !== 0) {
                    $this->error($userInfo['data'], APPLICATION_URL . '?c=' . $_GET['c'] . '&a=' . $_GET['a']);
                }
                $userInfo = $this->wxlogin($userInfo['data']);
                if ($userInfo['errorcode'] !== 0) {
                    $this->error($userInfo['data'], APPLICATION_URL . '?c=' . $_GET['c'] . '&a=' . $_GET['a']);
                }
                $this->_G['user'] = json_decode($userInfo['data'], true);
            }
        }
        
        // 过滤不安全的post数据
        safepost($_GET);
        safepost($_POST);
    }

    public function __init ()
    {}

    public function __notfund ()
    {
        $this->error($this->_module . $this->_action . ' 404.');
    }

    public function render ($tplName, $params = array(), $style = null)
    {
        $style = !empty($style) ? $style : APPLICATION_STYLE;
        $tpl_dir = APPLICATION_URL . '/application/views/' . $style;
        is_array($params) && extract($params);
        include APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $tplName;
        exit();
    }

    public function success ($message = '', $url = '', $wait = 3, $ajax = null)
    {
        $this->show_message('success', $message, $url, $wait, $ajax);
    }

    public function error ($message = '', $url = '', $wait = 3, $ajax = null)
    {
        $this->show_message('error', $message, $url, $wait, $ajax);
    }

    private function show_message ($type, $message = '', $url = '', $wait = 3, $ajax = null)
    {
        $ajax = isset($ajax) ? $ajax : isset($_GET['ajax']);
        if ($ajax) {
            if ($type == 'success')
                echo json_unicode_encode(success($message));
            else if ($type == 'error')
                echo json_unicode_encode(error($message));
            else
                echo $message;
            exit();
        }
        if ($url) {
            $url = $url{0} == '/' ? (APPLICATION_URL . $url) : $url;
        } else {
            $url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : APPLICATION_URL;
        }
        if ($wait > 0) {
            $this->render('redirect.html', [
                    'type' => $type, 
                    'message' => $message, 
                    'url' => $url, 
                    'wait' => $wait
            ]);
        } else {
            header('Location: ' . $url);
        }
        exit();
    }

    private function loginCheck ($token = '', $clienttype = '')
    {
        if (empty($token)) {
            if (!empty($_POST['token']))
                $token = $_POST['token'];
            elseif (!empty($_GET['token']))
                $token = $_GET['token'];
            elseif (!empty($_COOKIE['token']))
                $token = $_COOKIE['token'];
        }
        if (empty($token))
            return false;
        list ($uid, $scode, $client) = explode("\t", authcode(rawurldecode($token), 'DECODE'));
        $clienttype = $clienttype ? $clienttype : ($client ? $client : (defined('CLIENT_TYPE') ? CLIENT_TYPE : ''));
        if (!$uid || !$scode)
            return false;
        return DB::getInstance()->field('u.id,u.nickname,u.realname,u.idcard,u.address,u.telephone,u.gender,u.email,u.qq,u.description,u.birthday,u.area,u.state,u.status,u.updatetime,s.clienttype,s.clientapp,s.stoken')->join('~user~ u inner join ~session~ s on s.userid=u.id')->where('u.id=' . $uid . ' and s.scode="' . $scode . '" and s.clienttype="' . $clienttype . '"')->find();
    }

    private function wxlogin ($data)
    {
        $user = new UserModel();
        $ret = $user->extend_login('wx', safe_subject($data['nickname']), $data['sex'], $data['headimgurl'], $data['openid'], $data['unionid'], array(
                'clienttype' => CLIENT_TYPE, 
                'clientapp' => 'web'
        ), $data['subscribe']);
        return $ret;
    }

}

class ComposerAutoloader {

    public static function loadClassLoader ($class)
    {
        $class_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $class . '.php';
        if (!file_exists($class_dir)) {
            $class_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class . '.php';
        }
        require_once $class_dir;
    }

    public static function getLoader ()
    {
        spl_autoload_register(array(
                'ComposerAutoloader', 
                'loadClassLoader'
        ), true, true);
    }

}

ComposerAutoloader::getLoader();

$controller = new Controller();
