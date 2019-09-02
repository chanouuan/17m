<?php
/**
 * 数据库管理
 * @author Administrator
 *
 */
abstract class Db {

    /* 数据库实例 */
    private static $_instance = array();

    /* SQL命令 */
    protected $_fetchSql = array();

    /* 错误命令 */
    protected $_errorInfo = array();

    /* 执行时间 */
    private $_runStart = array();

    /* Debug模式 */
    private $_debug = true;

    final function __construct ()
    {
        $this->_debug && $this->_runStart[get_class($this)] = microtime_float();
    }

    final function __destruct ()
    {
        // 关闭连接
        $this->close();
        // 记录日志
        if ($this->_debug === true && $this->_fetchSql) {
            $this->error_log($this->_fetchSql, get_class($this) . '_debug.log');
        }
        if ($this->_errorInfo) {
            $this->error_log($this->_errorInfo, get_class($this) . '_errors.log');
        }
    }

    private function error_log ($message, $logfile)
    {
        $data = array(
                '[' . date('Y-m-d H:i:s') . ']', 
                '运行地址: ' . $_SERVER['REQUEST_URI']
        );
        if ($_POST) {
            $data[] = urldecode(http_build_query($_POST));
        }
        $data[] = is_array($message) ? implode("\r\n", $message) : $message;
        if ($this->_debug === true) {
            $data[] = '运行时间: ' . round(microtime_float() - $this->_runStart[get_class($this)], 3) . ' 秒';
        }
        $data[] = "\r\n";
        error_log(implode("\r\n", $data), 3, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $logfile);
    }

    final static function getInstance ($link = 'mysql')
    {
        if (isset(self::$_instance[$link])) {return self::$_instance[$link];}
        $link = strtolower($link);
        $dbconfig = include APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'config.php';
        $dbconfig = $dbconfig[$link];
        if ($dbconfig) {
            $dbclass = 'Db' . ucfirst($link);
            $dbclass = new $dbclass();
            try {
                $dbclass->connect($dbconfig);
            } catch (Exception $e) {
                error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\r\n", 3, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $link . '_connect_err.log');
                try {
                    $dbclass->connect($dbconfig);
                } catch (Exception $e) {
                    error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\r\n", 3, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $link . '_connect_err.log');
                    return null;
                }
            }
            self::$_instance[$link] = & $dbclass;
        }
        return self::$_instance[$link];
    }

    abstract protected function connect ($config);

    abstract protected function close ();

}