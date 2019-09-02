<?php
class DbMysql extends Db {

    private $_db = null;

    private $_parseSql = 'SELECT %FIELD% FROM %TABLE% %JOIN% %WHERE% %GROUP% %ORDER% %LIMIT%';

    private $_options = array();

    private $_tablePre = '';

    public function connect ($config)
    {
        try {
            $this->_db = new PDO('mysql:dbname=' . $config['database'] . ';host=' . $config['server'] . ';charset=utf8', $config['user'], $config['pwd'], array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ));
        }
        catch (Exception $e) {
            throw $e;
        }
        $this->_db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false); // 使用缓冲查询
        $this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // 返回一个索引为结果集列名的数组
        $this->_tablePre = $config['tablepre'];
    }

    public function close ()
    {
        $this->_db = null;
    }

    public function __call ($method, $args)
    {
        // 连贯操作的实现
        $args[0] && $this->_options[strtolower($method)] = $args[0];
        return $this;
    }

    private function putLastSql ($sql)
    {
        $this->_fetchSql[] = $sql;
    }

    private function parseSql ()
    {
        $_sql = str_replace(array(
                '%TABLE%', 
                '%FIELD%', 
                '%JOIN%', 
                '%WHERE%', 
                '%GROUP%', 
                '%ORDER%', 
                '%LIMIT%'
        ), array(
                !empty($this->_options['table']) ? $this->_options['table'] : '', 
                !empty($this->_options['field']) ? $this->_options['field'] : '', 
                !empty($this->_options['join']) ? $this->_options['join'] : '', 
                !empty($this->_options['where']) ? ('WHERE ' . $this->_options['where']) : '', 
                !empty($this->_options['group']) ? ('GROUP BY ' . $this->_options['group']) : '', 
                !empty($this->_options['order']) ? ('ORDER BY ' . $this->_options['order']) : '', 
                !empty($this->_options['limit']) ? ('LIMIT ' . $this->_options['limit']) : ''
        ), $this->_parseSql);
        $this->_options = array();
        return $_sql;
    }

    /**
     * 解析__TableName__为TablePre_TableName
     * @param $sql sql语句
     */
    private function parseTableName ($sql)
    {
        return preg_replace('/~(\S+)~/Us', $this->_tablePre . '\\1', $sql);
    }

    public function norepeat ($tablename, $fieldlist)
    {
        $fielddata = array();
        foreach ($fieldlist as $k => $v) {
            $fielddata['`' . $k . '`'] = '\'' . $v . '\'';
        }
        unset($fieldlist);
        $key = implode(',', array_keys($fielddata));
        $value = implode(',', array_values($fielddata));
        $fielddata = urldecode(http_build_query($fielddata, '', ','));
        $sql = 'insert into `' . $this->parseTableName($tablename) . '` (' . $key . ') values (' . $value . ') ON DUPLICATE KEY UPDATE ' . $fielddata . ';';
        $this->putLastSql($sql);
        $result = $this->_db->exec($sql);
        false === $result && $this->error();
        return $result;
    }

    public function insert ($tablename, $fieldlist, $replace = false)
    {
        $key = array();
        $value = array();
        foreach ($fieldlist as $k => $v) {
            $key[] = $k;
            if (!is_array($v)) {
                $value[0][] = $v;
            } else {
                $i = 0;
                foreach ($v as $kk => $vv) {
                    $value[$i++][] = $vv;
                }
            }
        }
        unset($fieldlist);
        $key = '`' . implode('`,`', $key) . '`';
        foreach ($value as $k => $v) {
            $value[$k] = '(\'' . implode('\',\'', $v) . '\')';
        }
        $value = implode(',', $value);
        $sql = ($replace ? 'replace' : 'insert') . ' into ' . $this->parseTableName($tablename) . ' (' . $key . ') values ' . $value . ';';
        $this->putLastSql($sql);
        $result = $this->_db->exec($sql);
        false === $result && $this->error();
        return $result;
    }

    public function update ($tablename, $fieldlist, $where)
    {
        $value = array();
        foreach ($fieldlist as $k => $v) {
            switch ($v{0}) {
                case '~':
                    // 表达式
                    $value[] = '`' . $k . '`=' . substr($v, 1);
                    break;
                default:
                    $value[] = '`' . $k . '`=\'' . $v . '\'';
            }
        }
        unset($fieldlist);
        $value = implode(',', $value);
        $sql = 'update ' . $this->parseTableName($tablename) . ' set ' . $value . ' where ' . $where;
        $this->putLastSql($sql);
        $result = $this->_db->exec($sql);
        false === $result && $this->error();
        return $result;
    }

    public function delete ($tablename, $where)
    {
        $sql = 'delete from ' . $this->parseTableName($tablename) . ' where ' . $where;
        $this->putLastSql($sql);
        $result = $this->_db->exec($sql);
        false === $result && $this->error();
        return $result;
    }

    public function query ($sql)
    {
        $sql = $this->parseTableName($sql);
        $this->putLastSql($sql);
        $result = $this->_db->exec($sql);
        false === $result && $this->error();
        return $result;
    }

    public function find ($sql = '', $first = false)
    {
        $sql || $sql = $this->parseSql();
        $sql = $this->parseTableName($sql);
        $this->putLastSql($sql);
        $rs = $this->_db->query($sql);
        if (false === $rs) {
            $this->error();
            return false;
        }
        $row = $rs->fetch();
        return !empty($row) ? ($first ? current($row) : $row) : false;
    }

    public function select ($sql = '')
    {
        $sql || $sql = $this->parseSql();
        $sql = $this->parseTableName($sql);
        $this->putLastSql($sql);
        $rs = $this->_db->query($sql);
        if (false === $rs) {
            $this->error();
            return false;
        }
        $result = $rs->fetchAll();
        return empty($result) ? false : $result;
    }

    public function transaction ($callback)
    {
        if (!isset($callback)) return false;
        $this->_db->beginTransaction();
        $ret = $callback($this);
        false !== $ret ? $this->_db->commit() : $this->_db->rollBack();
        return $ret;
    }

    public function getlastid ()
    {
        return $this->_db->lastInsertId();
    }

    public function error ()
    {
        $errorcode = $this->_db->errorCode();
        if ($errorcode != '00000') {
            $errorinfo = implode(',', $this->_db->errorInfo());
            $this->_errorInfo[] = end($this->_fetchSql);
            $this->_errorInfo[] = $errorinfo;
            return $errorinfo;
        }
        return false;
    }

}